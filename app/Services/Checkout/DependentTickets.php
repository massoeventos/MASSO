<?php

namespace Masso\Services\Checkout;

use Masso\Payment;
use Masso\PaymentDetail;
use Masso\TicketRule;

/**
 * Qué tickets de una compra dependen de cada uno: los complementarios de
 * una entrada, y los que dejarían de cumplir una regla "requiere" si ese
 * ticket se quitara. Se muestra como aviso en el detalle del pago del admin;
 * el bloque de reembolsos y cesión lo usará para ofrecer "incluir también".
 *
 * Devuelve [payment_detail_id => [nombres de tickets dependientes]].
 */
class DependentTickets
{
    public static function forPayment(Payment $payment): array
    {
        $details = $payment->details()->with('ticket')
            ->whereNotIn('status', [PaymentDetail::STATUS_VOIDED, PaymentDetail::STATUS_REPLACED])
            ->get();
        if ($details->isEmpty() || !$payment->event_id || !$payment->event) {
            return [];
        }

        $evaluator = new TicketRuleEvaluator($payment->event);
        $children = $details->whereNotNull('parent_id')->groupBy('parent_id');
        $result = [];

        foreach ($details as $detail) {
            $names = [];
            foreach ($children->get($detail->id, collect()) as $child) {
                $names[] = $child->ticket ? $child->ticket->name : 'Ticket';
            }

            if ($evaluator->hasRules()) {
                $before = self::requiresBroken($evaluator, $details);
                $after = self::requiresBroken($evaluator, $details->reject(fn ($d) => $d->id === $detail->id || $d->parent_id === $detail->id));
                foreach (array_diff_key($after, $before) as $ticketIds) {
                    foreach ($ticketIds as $ticketId) {
                        $row = $details->firstWhere('ticket_id', $ticketId);
                        $names[] = $row && $row->ticket ? $row->ticket->name : 'Ticket';
                    }
                }
            }

            $names = array_values(array_unique($names));
            if ($names) {
                $result[$detail->id] = $names;
            }
        }

        return $result;
    }

    // Reglas "requiere" que no se cumplen: ["regla|fila de la entrada" => [ids de tickets sujeto]].
    private static function requiresBroken(TicketRuleEvaluator $evaluator, $details): array
    {
        [$cart, $entries, $roots] = self::cartOf($details);
        $broken = [];
        foreach ($evaluator->evaluate($cart, $entries) as $violation) {
            $rule = $evaluator->rule($violation['rule_id']);
            if (!$rule || $rule->rule !== TicketRule::REQUIRES) {
                continue;
            }
            $ids = $violation['entry'] ? array_keys(self::entryCart($entries[$violation['entry'] - 1])) : array_keys($cart);
            // la entrada se identifica por su fila (no por su número, que
            // cambia al quitar otra entrada)
            $entryId = $violation['entry'] ? $roots[$violation['entry'] - 1] : '';
            $broken[$rule->id . '|' . $entryId] = $evaluator->subjectTicketIds($rule, $ids);
        }
        return $broken;
    }

    private static function cartOf($details): array
    {
        $cart = [];
        foreach ($details as $d) {
            $cart[$d->ticket_id] = ($cart[$d->ticket_id] ?? 0) + 1;
        }

        $byParent = $details->whereNotNull('parent_id')->groupBy('parent_id');
        $entries = null;
        $roots = [];
        if ($byParent->isNotEmpty()) {
            $entries = [];
            foreach ($details->whereNull('parent_id') as $root) {
                $roots[] = $root->id;
                $items = [];
                foreach ($byParent->get($root->id, collect()) as $child) {
                    $items[$child->ticket_id] = ($items[$child->ticket_id] ?? 0) + 1;
                }
                $entries[] = ['ticket' => $root->ticket_id, 'items' => $items];
            }
        }
        return [$cart, $entries, $roots];
    }

    private static function entryCart(array $entry): array
    {
        return [$entry['ticket'] => 1] + $entry['items'];
    }
}
