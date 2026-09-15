<?php

namespace Masso\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Masso\Services\LegacySerializedData;
use Masso\Services\EnrollmentDataResolver;
use Masso\Services\CorruptRowLogger;

/**
 * Rellena payments.data_json / events_enroll.data_json a partir de la
 * columna legada `data` (PHP serialize(), a veces doblemente serializada,
 * y en algunos casos con el largo declarado de algún string desalineado
 * — ver LegacySerializedData::repairLengths()). No modifica ni borra
 * `data`: es puramente aditivo.
 *
 * data_json guarda SOLO los campos que no tienen columna real dedicada
 * (ver EnrollmentDataResolver::KNOWN_KEYS) — no repite name/lastname/
 * email/rut/etc., que ya viven en sus propias columnas.
 */
class BackfillDataJson extends Command
{
    protected $signature = 'masso:backfill-data-json
        {table=all : payments|events_enroll|all}
        {--event= : Filtrar por event_id}
        {--chunk=500 : Tamaño del lote}
        {--force : Reprocesa incluso filas que ya tienen data_json (lo sobrescribe)}
        {--dry-run : No escribe en la BD, solo reporta lo que haría}';

    protected $description = 'Rellena data_json (solo campos extra) a partir de data (serialize() legado) sin tocar la columna data';

    private const SUPPORTED_TABLES = ['payments', 'events_enroll'];

    public function handle()
    {
        $tableArg = $this->argument('table');
        $eventFilter = $this->option('event');
        $chunkSize = max(1, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $tables = $tableArg === 'all' ? self::SUPPORTED_TABLES : [$tableArg];
        $logger = new CorruptRowLogger();

        foreach ($tables as $table) {
            if (!in_array($table, self::SUPPORTED_TABLES, true)) {
                $this->error("Tabla no soportada: {$table}. Use payments, events_enroll o all.");
                continue;
            }

            $this->backfillTable($table, $eventFilter, $chunkSize, $dryRun, $force, $logger);
        }

        $path = $logger->write('backfill-data-json');
        if ($path !== null) {
            $this->newLine();
            $this->warn("Se guardaron {$logger->count()} filas corruptas/no codificables (con su data completa) en: {$path}");
        }

        return 0;
    }

    private function backfillTable(string $table, ?string $eventFilter, int $chunkSize, bool $dryRun, bool $force, CorruptRowLogger $logger): void
    {
        $this->info(($dryRun ? '[DRY-RUN] ' : '')."== Backfill de {$table}.data_json ==");

        $query = DB::table($table)
            ->select('id', 'event_id', 'data')
            ->whereNotNull('data')
            ->where('data', '!=', '');

        if (!$force) {
            $query->whereNull('data_json');
        }

        if ($eventFilter !== null && $eventFilter !== '') {
            $query->where('event_id', $eventFilter);
        }

        $updated = 0;
        $repaired = 0;
        $skippedEmpty = 0;
        $skippedCorrupt = 0;

        $query->orderBy('id')->chunkById($chunkSize, function ($rows) use ($table, $dryRun, &$updated, &$repaired, &$skippedEmpty, &$skippedCorrupt, $logger) {
            foreach ($rows as $row) {
                $result = LegacySerializedData::inspect($row->data);

                if ($result['status'] === LegacySerializedData::STATUS_EMPTY) {
                    $skippedEmpty++;
                    continue;
                }

                if ($result['status'] === LegacySerializedData::STATUS_CORRUPT) {
                    $skippedCorrupt++;
                    $logger->add($table, $row->id, $row->event_id, $row->data, 'unserialize_failed');
                    continue;
                }

                $extra = EnrollmentDataResolver::extraFields($result['data']);
                $encoded = json_encode($extra, JSON_UNESCAPED_UNICODE);

                if ($encoded === false) {
                    $skippedCorrupt++;
                    $logger->add($table, $row->id, $row->event_id, $row->data, 'json_encode_failed: ' . json_last_error_msg());
                    continue;
                }

                if (!$dryRun) {
                    DB::table($table)->where('id', $row->id)->update([
                        'data_json' => $encoded,
                    ]);
                }

                if ($result['repaired']) {
                    $repaired++;
                }

                $updated++;
            }
        }, 'id');

        $this->line("{$table}: actualizadas {$updated} (de las cuales {$repaired} recuperadas por reparación de largo), vacías omitidas {$skippedEmpty}, corruptas omitidas {$skippedCorrupt}");
    }
}
