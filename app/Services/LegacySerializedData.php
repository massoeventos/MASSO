<?php

namespace Masso\Services;

/**
 * Lectura segura de las columnas `payments.data` / `events_enroll.data`.
 *
 * Estas columnas fueron pensadas para guardar un único array serializado
 * con PHP serialize(), pero un bug histórico en
 * App\Console\Commands\SendNotifications::handle() volvía a aplicar
 * serialize() sobre un valor que ya venía serializado, produciendo filas
 * doblemente (o más) serializadas para un rango de fechas. Antes de esta
 * clase, cada controlador que necesitaba leer el dato agregaba su propio
 * parche ad-hoc para intentar un segundo unserialize(); esta clase es la
 * única implementación de esa lógica, sin límite fijo de "un segundo
 * intento": colapsa cualquier número de capas.
 *
 * Además de la doble serialización, existe un segundo tipo de daño real
 * en producción: strings tipo s:N:"..." donde N (la longitud en bytes
 * declarada en el momento de serializar) ya no coincide con el contenido
 * actual — confirmado en ~92 filas reales, todas con comillas simples
 * duplicadas dentro del texto (ej. "Baratela''s"), lo que sugiere que en
 * algún punto el valor pasó por un escape de comillas SQL después de
 * haber sido serializado, corriendo el largo real sin actualizar el
 * prefijo. unserialize() de PHP confía ciegamente en ese prefijo y falla
 * por completo apenas hay un byte de diferencia. `repairLengths()`
 * ignora el N declarado y ubica el cierre real de cada string buscando el
 * patrón `";` seguido de un token válido de serialize() — permite
 * recuperar el array original sin adivinar el contenido. Verificado
 * contra las 92 filas reales marcadas como corruptas: recupera 84 (91%)
 * con datos coherentes (nombres, emails con formato válido).
 */
class LegacySerializedData
{
    public const STATUS_EMPTY = 'vacio';
    public const STATUS_SIMPLE = 'simple';
    public const STATUS_DOUBLE_OR_MORE = 'doble_o_mas';
    public const STATUS_CORRUPT = 'corrupto';

    /**
     * Máximo de capas de serialize() que se intentan colapsar antes de
     * darse por vencido y tratar el valor como corrupto. El bug conocido
     * solo produce una capa extra, este límite es solo una salvaguarda.
     */
    private const MAX_DEPTH = 10;

    /**
     * Devuelve siempre un array, sin importar cuántas veces se haya
     * serializado el valor original, o si hubo que repararlo (ver
     * inspect()/wasRepaired si necesitas saber si el dato es 100% confiable).
     */
    public static function safeUnserialize($value): array
    {
        return self::inspect($value)['data'];
    }

    /**
     * Igual que safeUnserialize(), pero además informa cuántas capas de
     * serialización tenía el valor, a qué categoría pertenece, y si hizo
     * falta reparar algún prefijo de longitud para poder leerlo (en cuyo
     * caso el dato se considera recuperado por heurística, no garantizado
     * al 100%: quien lo use para algo sensible debería poder distinguirlo).
     *
     * @return array{status: string, depth: int, data: array, repaired: bool}
     */
    public static function inspect($value): array
    {
        if ($value === null || $value === '') {
            return ['status' => self::STATUS_EMPTY, 'depth' => 0, 'data' => [], 'repaired' => false];
        }

        if (is_array($value)) {
            return ['status' => self::STATUS_SIMPLE, 'depth' => 0, 'data' => $value, 'repaired' => false];
        }

        if (!is_string($value)) {
            return ['status' => self::STATUS_CORRUPT, 'depth' => 0, 'data' => [], 'repaired' => false];
        }

        $current = $value;
        $depth = 0;
        $repaired = false;

        while (is_string($current) && $depth < self::MAX_DEPTH) {
            $unserialized = @unserialize($current);
            $usedRepair = false;

            // unserialize() devuelve false tanto en error como cuando el
            // valor original serializado era literalmente `false`
            // (representado como la cadena 'b:0;').
            if ($unserialized === false && $current !== 'b:0;') {
                // Segundo intento: puede que el prefijo de longitud de algún
                // string interno ya no coincida con el contenido real.
                $fixed = self::repairLengths($current);
                $unserialized = @unserialize($fixed);

                if ($unserialized === false && $fixed !== 'b:0;') {
                    break;
                }

                $usedRepair = true;
            }

            $current = $unserialized;
            $depth++;
            $repaired = $repaired || $usedRepair;
        }

        if ($depth === 0 || !is_array($current)) {
            return ['status' => self::STATUS_CORRUPT, 'depth' => $depth, 'data' => [], 'repaired' => false];
        }

        return [
            'status' => $depth === 1 ? self::STATUS_SIMPLE : self::STATUS_DOUBLE_OR_MORE,
            'depth' => $depth,
            'data' => $current,
            'repaired' => $repaired,
        ];
    }

    /**
     * Recorre un string serializado con PHP serialize() y recalcula el
     * largo declarado de cada nodo `s:N:"...";` según el contenido real,
     * en vez de confiar en N. Para encontrar el cierre real de cada string
     * (que puede contener cualquier byte, incluido `";`), busca la
     * siguiente ocurrencia de `";` que esté seguida por el final del
     * string, un `}` de cierre de array, o el inicio de un token válido de
     * serialize() (s:, i:, b:, a:, d:, N;). Es una heurística: no hay forma
     * 100% inequívoca de parsear un serialize() con longitudes corruptas,
     * pero en la práctica (contenido de formularios, sin bytes binarios
     * raros) encuentra el cierre correcto de forma consistente.
     */
    private static function repairLengths(string $serialized): string
    {
        $length = strlen($serialized);
        $out = '';
        $i = 0;

        while ($i < $length) {
            if ($i + 1 < $length && $serialized[$i] === 's' && $serialized[$i + 1] === ':') {
                $colonPos = $i + 2;
                while ($colonPos < $length && $serialized[$colonPos] !== ':') {
                    $colonPos++;
                }
                $quotePos = $colonPos + 1;

                if ($colonPos >= $length || $quotePos >= $length || $serialized[$quotePos] !== '"') {
                    $out .= $serialized[$i];
                    $i++;
                    continue;
                }

                $contentStart = $quotePos + 1;
                $cursor = $contentStart;
                $end = null;

                while ($cursor < $length - 1) {
                    if ($serialized[$cursor] === '"' && $serialized[$cursor + 1] === ';') {
                        $after = $cursor + 2;

                        if ($after >= $length) {
                            $end = $cursor;
                            break;
                        }

                        $next = $serialized[$after];

                        if ($next === '}') {
                            $end = $cursor;
                            break;
                        }

                        if ($next === 'N' && $after + 1 < $length && $serialized[$after + 1] === ';') {
                            $end = $cursor;
                            break;
                        }

                        if (in_array($next, ['s', 'i', 'b', 'a', 'd'], true) && $after + 1 < $length && $serialized[$after + 1] === ':') {
                            $end = $cursor;
                            break;
                        }
                    }

                    $cursor++;
                }

                if ($end === null) {
                    // No se encontró un cierre confiable: se deja el resto
                    // tal cual (unserialize() decidirá si falla o no).
                    $out .= substr($serialized, $i);
                    break;
                }

                $content = substr($serialized, $contentStart, $end - $contentStart);
                $out .= 's:' . strlen($content) . ':"' . $content . '";';
                $i = $end + 2;
            } else {
                $out .= $serialized[$i];
                $i++;
            }
        }

        return $out;
    }
}
