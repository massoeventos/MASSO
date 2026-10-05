<?php

namespace Masso\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Masso\PaymentDetail;

/**
 * payments_detail.status es una columna nueva (ver migración
 * 2026_09_14_000005): las filas que ya existían nacieron todas con el
 * default 'reservado'. Este comando las pone al día según el estado real
 * de su payment — sin tocar nada más.
 */
class BackfillPaymentDetailStatus extends Command
{
    protected $signature = 'masso:backfill-payment-detail-status
        {--dry-run : No escribe en la BD, solo reporta lo que haría}';

    protected $description = 'Pone al día payments_detail.status según el status real de su payment (pagado -> confirmado)';

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');

        $query = DB::table('payments_detail')
            ->join('payments', 'payments.id', '=', 'payments_detail.payment_id')
            ->where('payments.status', 'pagado')
            ->where('payments_detail.status', '!=', PaymentDetail::STATUS_CONFIRMED);

        $count = $query->count();

        if (!$dryRun) {
            DB::table('payments_detail')
                ->join('payments', 'payments.id', '=', 'payments_detail.payment_id')
                ->where('payments.status', 'pagado')
                ->where('payments_detail.status', '!=', PaymentDetail::STATUS_CONFIRMED)
                ->update(['payments_detail.status' => PaymentDetail::STATUS_CONFIRMED]);
        }

        $this->line(($dryRun ? '[DRY-RUN] ' : '') . "payments_detail actualizadas a 'confirmado': {$count}");

        return 0;
    }
}
