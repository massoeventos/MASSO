<?php

namespace Masso\Services\Checkout;

use Illuminate\Support\Facades\DB;
use Masso\Event;
use Masso\PaymentDetail;

/**
 * Tickets que una persona ya compró en un evento: [ticket_id => unidades],
 * para las reglas "por compra" cuando el evento las activa
 * (events.rules_consider_previous_purchases).
 *
 * Cuenta las unidades confirmadas de pagos del evento hechos por la cuenta
 * (customer_id), por cualquier cuenta con ese correo, o -- en pagos
 * anteriores a las cuentas -- con ese correo o RUT escritos en el pago.
 */
class PreviousPurchases
{
    public static function forEvent(Event $event, ?string $email, ?int $customerId = null, ?string $rut = null): array
    {
        $email = $email ? mb_strtolower(trim($email)) : null;
        $rut = $rut ? strtoupper(preg_replace('/[^0-9Kk]/', '', $rut)) : null;
        if (!$email && !$customerId && !$rut) {
            return [];
        }

        return DB::table('payments_detail')
            ->join('payments', 'payments.id', '=', 'payments_detail.payment_id')
            ->leftJoin('customers', 'customers.id', '=', 'payments.customer_id')
            ->where('payments.event_id', $event->id)
            ->whereNull('payments.deleted_at')
            ->whereNull('payments_detail.deleted_at')
            ->where('payments_detail.status', PaymentDetail::STATUS_CONFIRMED)
            ->where(function ($q) use ($email, $customerId, $rut) {
                if ($customerId) {
                    $q->orWhere('payments.customer_id', $customerId);
                }
                if ($email) {
                    $q->orWhereRaw('LOWER(customers.email) = ?', [$email])
                      ->orWhereRaw('LOWER(payments.email) = ?', [$email]);
                }
                if ($rut) {
                    $q->orWhere('customers.rut', $rut)->orWhere('payments.rut', $rut);
                }
            })
            ->groupBy('payments_detail.ticket_id')
            ->pluck(DB::raw('COUNT(*)'), 'payments_detail.ticket_id')
            ->map(fn ($n) => (int) $n)
            ->all();
    }
}
