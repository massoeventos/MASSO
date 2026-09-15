<?php

namespace Masso\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Masso\Services\LegacySerializedData;
use Masso\Services\CorruptRowLogger;

/**
 * Auditoría de solo lectura de las columnas `payments.data` /
 * `events_enroll.data`: clasifica cada fila (vacío / serializado simple /
 * doblemente serializado o más / corrupto) para dimensionar el problema
 * antes de migrar a data_json (ver masso:backfill-data-json).
 */
class AuditSerializedData extends Command
{
    protected $signature = 'masso:audit-serialized-data
        {table=all : payments|events_enroll|all}
        {--event= : Filtrar por event_id}
        {--sample=5 : Cantidad de filas corruptas a mostrar como ejemplo}';

    protected $description = 'Audita payments.data / events_enroll.data y clasifica cada fila por nivel de serialización';

    private const SUPPORTED_TABLES = ['payments', 'events_enroll'];

    public function handle()
    {
        $tableArg = $this->argument('table');
        $eventFilter = $this->option('event');
        $sampleSize = (int) $this->option('sample');

        $tables = $tableArg === 'all' ? self::SUPPORTED_TABLES : [$tableArg];
        $logger = new CorruptRowLogger();

        foreach ($tables as $table) {
            if (!in_array($table, self::SUPPORTED_TABLES, true)) {
                $this->error("Tabla no soportada: {$table}. Use payments, events_enroll o all.");
                continue;
            }

            $this->auditTable($table, $eventFilter, $sampleSize, $logger);
        }

        $path = $logger->write('audit-serialized-data');
        if ($path !== null) {
            $this->newLine();
            $this->warn("Se guardaron {$logger->count()} filas corruptas (con su data completa) en: {$path}");
        }

        return 0;
    }

    private function auditTable(string $table, ?string $eventFilter, int $sampleSize, CorruptRowLogger $logger): void
    {
        $this->info("== Auditando {$table} ==");

        $counts = [];
        $samples = [];
        $repairedSamples = [];
        $repairedTotal = 0;

        $query = DB::table($table)->select('id', 'event_id', 'data')->orderBy('id');

        if ($eventFilter !== null && $eventFilter !== '') {
            $query->where('event_id', $eventFilter);
        }

        $query->chunk(500, function ($rows) use (&$counts, &$samples, &$repairedSamples, &$repairedTotal, $sampleSize, $table, $logger) {
            foreach ($rows as $row) {
                $result = LegacySerializedData::inspect($row->data);
                $eventId = $row->event_id ?? 'null';

                $counts[$eventId][$result['status']] = ($counts[$eventId][$result['status']] ?? 0) + 1;

                if ($result['repaired']) {
                    $repairedTotal++;
                    if (count($repairedSamples) < $sampleSize) {
                        $repairedSamples[] = [$table, $row->id, $eventId];
                    }
                }

                if ($result['status'] === LegacySerializedData::STATUS_CORRUPT
                    && $row->data !== null
                    && $row->data !== ''
                ) {
                    // La data completa (sin truncar) queda en el archivo que
                    // escribe CorruptRowLogger; en consola solo se muestra
                    // una muestra corta para no inundar la terminal.
                    $logger->add($table, $row->id, $eventId, $row->data, 'unserialize_failed');

                    if (count($samples) < $sampleSize) {
                        $samples[] = [
                            $table,
                            $row->id,
                            $eventId,
                            mb_strimwidth((string) $row->data, 0, 120, '...'),
                        ];
                    }
                }
            }
        });

        if (empty($counts)) {
            $this->comment("Sin filas para {$table}.");
            return;
        }

        $rowsOut = [];
        $totals = [
            LegacySerializedData::STATUS_EMPTY => 0,
            LegacySerializedData::STATUS_SIMPLE => 0,
            LegacySerializedData::STATUS_DOUBLE_OR_MORE => 0,
            LegacySerializedData::STATUS_CORRUPT => 0,
        ];

        foreach ($counts as $eventId => $statuses) {
            $rowsOut[] = [
                $eventId,
                $statuses[LegacySerializedData::STATUS_EMPTY] ?? 0,
                $statuses[LegacySerializedData::STATUS_SIMPLE] ?? 0,
                $statuses[LegacySerializedData::STATUS_DOUBLE_OR_MORE] ?? 0,
                $statuses[LegacySerializedData::STATUS_CORRUPT] ?? 0,
                array_sum($statuses),
            ];

            foreach ($totals as $status => $total) {
                $totals[$status] += $statuses[$status] ?? 0;
            }
        }

        $this->table(['Evento', 'Vacío', 'Simple', 'Doble+', 'Corrupto', 'Total'], $rowsOut);
        $this->line(sprintf(
            'TOTAL %s -> vacío: %d, simple: %d, doble+: %d, corrupto: %d, filas: %d (de las cuales %d recuperadas por reparación de largo)',
            $table,
            $totals[LegacySerializedData::STATUS_EMPTY],
            $totals[LegacySerializedData::STATUS_SIMPLE],
            $totals[LegacySerializedData::STATUS_DOUBLE_OR_MORE],
            $totals[LegacySerializedData::STATUS_CORRUPT],
            array_sum($totals),
            $repairedTotal
        ));

        if (!empty($samples)) {
            $this->warn('Ejemplos de filas corruptas (irrecuperables):');
            $this->table(['Tabla', 'ID', 'Evento', 'Muestra'], $samples);
        }

        if (!empty($repairedSamples)) {
            $this->comment('Ejemplos de filas recuperadas por reparación de largo (para verificar manualmente):');
            $this->table(['Tabla', 'ID', 'Evento'], $repairedSamples);
        }

        $this->newLine();
    }
}
