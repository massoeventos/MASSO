<?php

namespace Masso\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Colecciona filas que el pipeline de datos serializados no pudo procesar
 * (corruptas, o que fallaron al codificarse a JSON) y las vuelca a un
 * archivo en storage/app/audits/ para revisión manual. Antes de esta
 * clase, lo único que quedaba de una fila corrupta era su ID en
 * storage/logs/laravel.log — insuficiente para entender qué pasó sin
 * volver a consultar la BD fila por fila.
 */
class CorruptRowLogger
{
    /** @var array<int, array<string, mixed>> */
    private array $rows = [];

    public function add(string $table, $id, $eventId, $rawData, string $reason): void
    {
        $this->rows[] = [
            'table' => $table,
            'id' => $id,
            'event_id' => $eventId,
            'reason' => $reason,
            'data' => $rawData,
        ];
    }

    public function isEmpty(): bool
    {
        return empty($this->rows);
    }

    public function count(): int
    {
        return count($this->rows);
    }

    /**
     * Escribe todas las filas acumuladas a un archivo JSON en
     * storage/app/audits/ (disco `local`) y devuelve la ruta absoluta, o
     * null si no había nada que escribir.
     */
    public function write(string $commandName): ?string
    {
        if ($this->isEmpty()) {
            return null;
        }

        $filename = sprintf('audits/%s-%s.json', $commandName, now()->format('Y_m_d_His'));

        Storage::disk('local')->put(
            $filename,
            json_encode($this->rows, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );

        return Storage::disk('local')->path($filename);
    }
}
