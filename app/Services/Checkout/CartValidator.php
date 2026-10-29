<?php

namespace Masso\Services\Checkout;

use Masso\Event;
use Masso\EventTicket;

/**
 * Validación final del carrito antes de crear el pago (PublicController::
 * process()). Repite en el servidor lo que el formulario revisa en pantalla:
 * que cada ticket sea del evento y esté a la venta, los disponibles, los
 * máximos por compra o por entrada, las entradas y sus complementarios
 * permitidos, la configuración del evento (selección única, máximo de la
 * compra, obligatorios) y las reglas de compra.
 *
 * Devuelve los mensajes de error (vacío = carrito válido).
 */
class CartValidator
{
    private Event $event;
    private bool $esp;

    public function __construct(Event $event, string $lang = 'esp')
    {
        $this->event = $event;
        $this->esp = $lang !== 'eng';
    }

    /**
     * $cart [ticket_id => unidades]; $entries como EventTicket::entriesFromInput
     * (null sin entradas); $previous: compras anteriores para las reglas.
     */
    public function validate(array $cart, ?array $entries = null, array $previous = []): array
    {
        if (empty($cart)) {
            return [$this->t('Debes seleccionar al menos un ticket.', 'You must select at least one ticket.')];
        }

        $tickets = $this->event->tickets()
            ->with('allowedWith:events_tickets.id')
            ->withCount(EventTicket::soldCountQuery())
            ->get()->keyBy('id');

        $errors = array_merge(
            $this->availability($cart, $entries, $tickets),
            $entries ? $this->entries($entries, $tickets) : $this->flatMaximums($cart, $tickets)
        );
        if ($errors) {
            return $errors; // con tickets inválidos, lo demás no aporta
        }

        $errors = $this->eventSettings($cart, $entries, $tickets);

        $lang = $this->esp ? 'es' : 'en';
        foreach ((new TicketRuleEvaluator($this->event))->evaluate($cart, $entries, $previous) as $violation) {
            $errors[] = $violation['message_' . $lang];
        }

        return $errors;
    }

    private function availability(array $cart, ?array $entries, $tickets): array
    {
        $errors = [];
        foreach ($cart as $id => $qty) {
            $ticket = $tickets[$id] ?? null;
            if (!$ticket) {
                $errors[] = $this->t('Uno de los tickets elegidos no pertenece a este evento.', 'One of the selected tickets does not belong to this event.');
                continue;
            }
            $name = $this->name($ticket);
            if (!$ticket->isOnSaleWindow()) {
                $errors[] = $this->t("«{$name}» ya no está a la venta.", "«{$name}» is no longer on sale.");
            } elseif ($ticket->availableCount() < 1) {
                $errors[] = $this->t("«{$name}» está agotado.", "«{$name}» is sold out.");
            } elseif ($ticket->availableCount() < $qty) {
                $n = $ticket->availableCount();
                $errors[] = $this->t("Solo quedan {$n} de «{$name}».", "Only {$n} of «{$name}» left.");
            }
        }

        if ($entries === null && $this->event->usesEntries()) {
            $complementary = collect(array_keys($cart))->first(fn ($id) => isset($tickets[$id]) && $tickets[$id]->isComplementary());
            if ($complementary) {
                $name = $this->name($tickets[$complementary]);
                $errors[] = $this->t("«{$name}» se elige dentro de una entrada.", "«{$name}» must be chosen within a ticket.");
            }
        }
        return $errors;
    }

    // Sin entradas: el máximo por compra de cada ticket.
    private function flatMaximums(array $cart, $tickets): array
    {
        $errors = [];
        foreach ($cart as $id => $qty) {
            $ticket = $tickets[$id] ?? null;
            if ($ticket && $qty > $ticket->maxPerPurchase()) {
                $n = $ticket->maxPerPurchase();
                $errors[] = $this->t("Puedes comprar como máximo {$n} de «{$this->name($ticket)}».", "You can buy at most {$n} of «{$this->name($ticket)}».");
            }
        }
        return $errors;
    }

    // Por entradas: cada entrada es un principal, con complementarios
    // permitidos para ella y dentro de su máximo por entrada.
    private function entries(array $entries, $tickets): array
    {
        $errors = [];
        $perPrincipal = [];
        foreach ($entries as $n => $entry) {
            $number = $n + 1;
            $principal = $tickets[$entry['ticket']] ?? null;
            if (!$principal || $principal->isComplementary()) {
                $errors[] = $this->t("La entrada {$number} no es una entrada principal.", "Ticket {$number} is not a main ticket.");
                continue;
            }
            $perPrincipal[$principal->id] = ($perPrincipal[$principal->id] ?? 0) + 1;

            foreach ($entry['items'] as $id => $qty) {
                $item = $tickets[$id] ?? null;
                if (!$item || !$item->isComplementary() || !$item->allowsPrincipal($principal->id)) {
                    $label = $item ? "«{$this->name($item)}»" : $this->t('Un ticket', 'A ticket');
                    $errors[] = $this->t("{$label} no está disponible con «{$this->name($principal)}» (entrada {$number}).", "{$label} is not available with «{$this->name($principal)}» (ticket {$number}).");
                } elseif ($qty > $item->maxPerPurchase()) {
                    $max = $item->maxPerPurchase();
                    $errors[] = $this->t("Entrada {$number}: como máximo {$max} de «{$this->name($item)}».", "Ticket {$number}: at most {$max} of «{$this->name($item)}».");
                }
            }
        }

        foreach ($perPrincipal as $id => $count) {
            if ($count > $tickets[$id]->maxPerPurchase()) {
                $max = $tickets[$id]->maxPerPurchase();
                $errors[] = $this->t("Puedes comprar como máximo {$max} de «{$this->name($tickets[$id])}».", "You can buy at most {$max} of «{$this->name($tickets[$id])}».");
            }
        }
        return $errors;
    }

    // Configuración del evento: selección única, máximo de la compra y
    // tickets obligatorios. Por entradas cuentan solo las entradas.
    private function eventSettings(array $cart, ?array $entries, $tickets): array
    {
        $errors = [];
        $counted = $entries
            ? array_count_values(array_column($entries, 'ticket'))
            : $cart;

        if ((int) $this->event->is_multiple_selection_ticket !== 1 && count($counted) > 1) {
            $errors[] = $this->t('Este evento permite elegir un solo tipo de ticket por compra.', 'This event allows only one ticket type per purchase.');
        }

        $max = (int) $this->event->max_selection_ticket;
        if ((int) $this->event->is_multiple_selection_ticket === 1 && $max > 0 && array_sum($counted) > $max) {
            $errors[] = $this->t("Puedes elegir como máximo {$max} tickets por compra.", "You can choose at most {$max} tickets per purchase.");
        }

        $mandatory = $tickets->filter(fn ($t) => (int) $t->is_mandatory === 1 && $t->isAvailable());
        if ($mandatory->isNotEmpty() && !$mandatory->contains(fn ($t) => isset($cart[$t->id]))) {
            $errors[] = $this->t('Debes seleccionar los tickets obligatorios.', 'You must select the mandatory tickets.');
        }

        return $errors;
    }

    private function name(EventTicket $ticket): string
    {
        return $this->esp ? $ticket->name : ($ticket->name_eng ?: $ticket->name);
    }

    private function t(string $es, string $en): string
    {
        return $this->esp ? $es : $en;
    }
}
