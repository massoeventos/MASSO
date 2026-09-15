<?php

namespace Masso\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Masso\Services\LegacySerializedData;

/**
 * Responde con evidencia la pregunta de si vale la pena seguir guardando,
 * dentro del blob serializado/JSON, las claves que YA existen como columna
 * real (name, email, rut, ...): compara columna vs. blob campo por campo
 * y clasifica cada fila en:
 *
 *   - coinciden:      la columna y el blob tienen el mismo valor (el caso
 *                      esperado si el blob es puro ruido redundante).
 *   - solo_columna:   la columna tiene valor y el blob no trae esa clave.
 *   - solo_blob:      la columna está vacía pero el blob SÍ tiene un valor
 *                      (señal de que el blob podría aportar algo que la
 *                      columna perdió).
 *   - difieren:       ambos tienen valor pero no coinciden (a revisar).
 *   - ambos_vacios:   ninguno tiene valor.
 *
 * Solo lee, no escribe nada.
 */
class AuditDuplicateFields extends Command
{
    protected $signature = 'masso:audit-duplicate-fields
        {table=payments : payments|events_enroll}
        {--event= : Filtrar por event_id}
        {--sample=5 : Cantidad de ejemplos por campo para "difieren"/"solo_blob"}';

    protected $description = 'Compara columna real vs. blob serializado/JSON campo por campo, para validar si esas claves duplicadas son redundantes';

    /**
     * Campos a comparar por tabla: el nombre debe ser tanto una columna
     * real de la tabla como una clave posible dentro del blob (algunos,
     * como los definidos dinámicamente en events_inputs, no aplican acá).
     */
    private const FIELD_MAP = [
        'payments' => ['name', 'lastname', 'email', 'rut', 'gender', 'city_id', 'country_id', 'nationality_country_id', 'custom_city', 'billing_method'],
        'events_enroll' => ['name', 'lastname', 'email', 'rut', 'passport', 'phone', 'profession', 'speciality', 'workplace', 'city', 'country', 'city_id', 'country_id', 'nationality_country_id', 'custom_city'],
    ];

    public function handle()
    {
        $table = $this->argument('table');

        if (!isset(self::FIELD_MAP[$table])) {
            $this->error('Tabla no soportada. Use payments o events_enroll.');
            return 1;
        }

        $fields = self::FIELD_MAP[$table];
        $eventFilter = $this->option('event');
        $sampleSize = (int) $this->option('sample');

        $columns = array_values(array_unique(array_merge(['id', 'event_id', 'data'], $fields)));

        $query = DB::table($table)->select($columns)
            ->whereNotNull('data')
            ->where('data', '!=', '');

        if ($eventFilter !== null && $eventFilter !== '') {
            $query->where('event_id', $eventFilter);
        }

        $stats = [];
        $samples = [];

        $query->orderBy('id')->chunkById(500, function ($rows) use ($fields, &$stats, &$samples, $sampleSize) {
            foreach ($rows as $row) {
                // Ojo: no se puede usar data_json acá. Este comando compara
                // la columna real contra el blob ORIGINAL completo (para
                // decidir si esas claves duplicadas valían la pena); desde
                // que data_json solo guarda campos extra, ya no sirve como
                // fuente para esta comparación específica.
                $raw = LegacySerializedData::safeUnserialize($row->data);

                foreach ($fields as $field) {
                    $columnValue = $row->{$field} ?? null;
                    $hasBlobKey = array_key_exists($field, $raw);
                    $blobValue = $hasBlobKey ? $raw[$field] : null;

                    $columnEmpty = $columnValue === null || $columnValue === '';
                    $blobEmpty = !$hasBlobKey || $blobValue === null || $blobValue === '';

                    if ($columnEmpty && $blobEmpty) {
                        $status = 'ambos_vacios';
                    } elseif (!$columnEmpty && $blobEmpty) {
                        $status = 'solo_columna';
                    } elseif ($columnEmpty && !$blobEmpty) {
                        $status = 'solo_blob';
                    } else {
                        $normColumn = mb_strtolower(trim((string) $columnValue));
                        $blobAsString = is_array($blobValue) ? json_encode($blobValue, JSON_UNESCAPED_UNICODE) : (string) $blobValue;
                        $normBlob = mb_strtolower(trim($blobAsString));
                        $status = ($normColumn === $normBlob) ? 'coinciden' : 'difieren';
                    }

                    $stats[$field][$status] = ($stats[$field][$status] ?? 0) + 1;

                    if (in_array($status, ['difieren', 'solo_blob'], true) && count($samples[$field] ?? []) < $sampleSize) {
                        $samples[$field][] = [
                            $row->id,
                            $row->event_id,
                            $status,
                            $columnValue,
                            is_array($blobValue) ? json_encode($blobValue, JSON_UNESCAPED_UNICODE) : $blobValue,
                        ];
                    }
                }
            }
        }, 'id');

        $summaryRows = [];
        foreach ($fields as $field) {
            $s = $stats[$field] ?? [];
            $summaryRows[] = [
                $field,
                $s['coinciden'] ?? 0,
                $s['solo_columna'] ?? 0,
                $s['solo_blob'] ?? 0,
                $s['difieren'] ?? 0,
                $s['ambos_vacios'] ?? 0,
            ];
        }

        $this->table(['Campo', 'Coinciden', 'Solo columna', 'Solo blob', 'Difieren', 'Ambos vacíos'], $summaryRows);

        foreach ($samples as $field => $rowsForField) {
            $this->warn("Ejemplos para '{$field}' (solo_blob / difieren):");
            $this->table(['ID', 'Evento', 'Estado', 'Columna', 'Blob'], $rowsForField);
        }

        return 0;
    }
}
