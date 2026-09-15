<?php

namespace Masso\Console\Commands;

use Illuminate\Console\Command;
use Masso\EventEnroll;
use Masso\PaymentDetail;
use Masso\Services\CorruptRowLogger;

/**
 * Enlaza cada events_enroll histórico (que vino de un pago real) con el
 * payments_detail que lo originó, completando payment_detail_id. No borra
 * ni modifica las columnas propias de name/lastname/rut/email/etc. — a
 * partir de este enlace, el modelo simplemente prioriza el dato del pago
 * al leerlas (ver EventEnroll::linkedPayment()).
 *
 * El match es por (payment_id, ticket_id): dentro de cada grupo se
 * empareja en orden de id. Si el conteo no coincide de ambos lados (caso
 * ambiguo, poco común) no se fuerza — se loguea para revisión manual.
 */
class BackfillEventEnrollPaymentDetail extends Command
{
    protected $signature = 'masso:backfill-event-enroll-payment-detail
        {--chunk=500 : Cantidad de payment_id a procesar por lote}
        {--dry-run : No escribe en la BD, solo reporta lo que haría}';

    protected $description = 'Enlaza events_enroll con su payments_detail de origen (completa payment_detail_id)';

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunkSize = max(1, (int) $this->option('chunk'));
        $logger = new CorruptRowLogger();

        $linked = 0;
        $ambiguous = 0;
        $noDetail = 0;

        EventEnroll::whereNotNull('payment_id')
            ->whereNull('payment_detail_id')
            ->pluck('payment_id')
            ->unique()
            ->values()
            ->chunk($chunkSize)
            ->each(function ($paymentIds) use (&$linked, &$ambiguous, &$noDetail, $dryRun, $logger) {
                $paymentIds = $paymentIds->values()->all();

                $enrolls = EventEnroll::whereIn('payment_id', $paymentIds)
                    ->whereNull('payment_detail_id')
                    ->orderBy('id')
                    ->get(['id', 'payment_id', 'ticket_id'])
                    ->groupBy(['payment_id', 'ticket_id']);

                $details = PaymentDetail::whereIn('payment_id', $paymentIds)
                    ->orderBy('id')
                    ->get(['id', 'payment_id', 'ticket_id'])
                    ->groupBy(['payment_id', 'ticket_id']);

                foreach ($enrolls as $paymentId => $ticketGroups) {
                    foreach ($ticketGroups as $ticketId => $enrollGroup) {
                        $detailGroup = $details->get($paymentId, collect())->get($ticketId, collect());

                        if ($detailGroup->count() === 0) {
                            $noDetail += $enrollGroup->count();
                            foreach ($enrollGroup as $e) {
                                $logger->add('events_enroll', $e->id, null, ['payment_id' => $paymentId, 'ticket_id' => $ticketId], 'sin_payment_detail_correspondiente');
                            }
                            continue;
                        }

                        if ($detailGroup->count() !== $enrollGroup->count()) {
                            $ambiguous += $enrollGroup->count();
                            foreach ($enrollGroup as $e) {
                                $logger->add('events_enroll', $e->id, null, [
                                    'payment_id' => $paymentId,
                                    'ticket_id' => $ticketId,
                                    'enrolls_en_grupo' => $enrollGroup->count(),
                                    'details_en_grupo' => $detailGroup->count(),
                                ], 'conteo_ambiguo_payment_ticket');
                            }
                            continue;
                        }

                        $enrollValues = $enrollGroup->values();
                        $detailValues = $detailGroup->values();

                        foreach ($enrollValues as $i => $enroll) {
                            if (!$dryRun) {
                                EventEnroll::where('id', $enroll->id)->update([
                                    'payment_detail_id' => $detailValues[$i]->id,
                                ]);
                            }
                            $linked++;
                        }
                    }
                }
            });

        $this->line(($dryRun ? '[DRY-RUN] ' : '') . "Enlazados: {$linked}, sin payments_detail correspondiente: {$noDetail}, ambiguos (conteo no coincide): {$ambiguous}");

        $path = $logger->write('backfill-event-enroll-payment-detail');
        if ($path !== null) {
            $this->warn("Detalle de filas no enlazadas guardado en: {$path}");
        }

        return 0;
    }
}
