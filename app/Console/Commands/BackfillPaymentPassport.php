<?php

namespace Masso\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Masso\Services\LegacySerializedData;

/**
 * payments.passport es una columna nueva (ver migración
 * 2026_09_15_000001): antes ese dato solo existía dentro del blob
 * serializado (data/data_json) y, copiado desde ahí, en
 * events_enroll.passport. Este comando la rellena para pagos existentes,
 * priorizando el propio blob del pago y, si no está ahí, el de un
 * events_enroll enlazado a ese pago.
 */
class BackfillPaymentPassport extends Command
{
    protected $signature = 'masso:backfill-payment-passport
        {--dry-run : No escribe en la BD, solo reporta lo que haría}';

    protected $description = 'Rellena payments.passport desde el blob legado o desde events_enroll enlazado';

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');

        $fromBlob = 0;
        $fromEnroll = 0;
        $notFound = 0;

        DB::table('payments')
            ->whereNull('passport')
            ->orderBy('id')
            ->chunkById(1000, function ($payments) use ($dryRun, &$fromBlob, &$fromEnroll, &$notFound) {
                foreach ($payments as $payment) {
                    $raw = LegacySerializedData::safeUnserialize($payment->data);
                    $passport = $raw['passport'] ?? null;

                    if (!empty($passport)) {
                        if (!$dryRun) {
                            DB::table('payments')->where('id', $payment->id)->update(['passport' => $passport]);
                        }
                        $fromBlob++;
                        continue;
                    }

                    // Fallback: un events_enroll ya enlazado a este pago que
                    // tenga su propio passport (por si el blob del pago no
                    // lo tenía pero sí llegó a copiarse en su momento).
                    $enrollPassport = DB::table('events_enroll')
                        ->where('payment_id', $payment->id)
                        ->whereNotNull('passport')
                        ->where('passport', '!=', '')
                        ->value('passport');

                    if (!empty($enrollPassport)) {
                        if (!$dryRun) {
                            DB::table('payments')->where('id', $payment->id)->update(['passport' => $enrollPassport]);
                        }
                        $fromEnroll++;
                        continue;
                    }

                    $notFound++;
                }
            });

        $this->line(($dryRun ? '[DRY-RUN] ' : '') . "Rellenados desde el blob del pago: {$fromBlob}, desde events_enroll: {$fromEnroll}, sin dato disponible: {$notFound}");

        return 0;
    }
}
