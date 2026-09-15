<?php

namespace Masso\Console\Commands;

use Illuminate\Console\Command;
use Masso\Payment;

/**
 * 'participants_excel_file' / 'participants_count' se promovieron de
 * "campo extra" dentro del blob a columna real de payments (ver
 * migración 2026_09_14_000004). Este comando rellena esas columnas para
 * los pagos ya existentes que las tenían solo en data_json.
 */
class BackfillParticipantsColumns extends Command
{
    protected $signature = 'masso:backfill-participants-columns
        {--dry-run : No escribe en la BD, solo reporta lo que haría}';

    protected $description = 'Rellena payments.participants_excel_file / participants_count desde data_json';

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');
        $updated = 0;

        Payment::whereNull('participants_excel_file')
            ->whereNotNull('data_json')
            ->chunkById(200, function ($payments) use (&$updated, $dryRun) {
                foreach ($payments as $payment) {
                    $raw = $payment->data_json ?? [];

                    if (!array_key_exists('participants_excel_file', $raw) && !array_key_exists('participants_count', $raw)) {
                        continue;
                    }

                    if (!$dryRun) {
                        $payment->participants_excel_file = $raw['participants_excel_file'] ?? null;
                        $payment->participants_count = $raw['participants_count'] ?? null;
                        $payment->save();
                    }

                    $updated++;
                }
            });

        $this->line(($dryRun ? '[DRY-RUN] ' : '') . "Pagos actualizados: {$updated}");

        return 0;
    }
}
