<?php

namespace Masso\Services\Checkout;

use Masso\Event;
use Masso\EventTicket;
use Masso\TicketRule;

/**
 * Evalúa las reglas de compra de un evento (Masso\TicketRule) sobre un
 * carrito. Las reglas de ámbito "entrada" se revisan dentro de cada entrada
 * (su ticket principal con sus complementarios); en un evento sin entradas,
 * sobre toda la compra, igual que las de ámbito "compra".
 *
 * Compras anteriores (F6): se suman a las de ámbito "compra"; una regla solo
 * se reporta si la compra actual incluye su sujeto u objetivo (o es "min").
 */
class TicketRuleEvaluator
{
    private Event $event;
    private $rules;
    private $tickets;
    private $categories;

    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->rules = $event->ticketRules()->get();
        $this->tickets = $event->tickets()->with('categories:event_ticket_categories.id')->get()->keyBy('id');
        $this->categories = $event->ticketCategories()->get()->keyBy('id');
    }

    public function hasRules(): bool
    {
        return $this->rules->isNotEmpty();
    }

    /**
     * $cart [ticket_id => unidades], $entries como EventTicket::entriesFromInput
     * (null sin entradas), $previous [ticket_id => unidades] ya compradas.
     * Devuelve [['rule_id', 'entry' (número o null), 'message_es',
     * 'message_en', 'text_es', 'text_en' (sin el prefijo de la entrada),
     * 'add_ticket_id' (ticket que resolvería, o null)], ...].
     */
    public function evaluate(array $cart, ?array $entries = null, array $previous = []): array
    {
        $violations = [];

        foreach ($this->rules as $rule) {
            if ($rule->scope === TicketRule::SCOPE_ENTRY && $entries) {
                foreach ($entries as $n => $entry) {
                    $entryCart = [$entry['ticket'] => 1];
                    foreach ($entry['items'] as $id => $qty) {
                        $entryCart[$id] = ($entryCart[$id] ?? 0) + $qty;
                    }
                    if ($this->breaks($rule, $entryCart, $entryCart)) {
                        $violations[] = $this->violation($rule, $n + 1, $entry['ticket']);
                    }
                }
                continue;
            }

            $total = $cart;
            if ($rule->scope === TicketRule::SCOPE_PURCHASE) {
                foreach ($previous as $id => $qty) {
                    $total[$id] = ($total[$id] ?? 0) + $qty;
                }
            }
            if ($this->breaks($rule, $total, $cart)) {
                // falla solo por lo que ya compró: se aclara en el mensaje
                $byPrevious = $previous && !$this->breaks($rule, $cart, $cart);
                $violations[] = $this->violation($rule, null, null, $byPrevious);
            }
        }

        return $violations;
    }

    // $counted: lo que cuenta para la regla; $current: lo de esta compra.
    private function breaks(TicketRule $rule, array $counted, array $current): bool
    {
        $subject = $this->units($counted, $rule->subject_type, $rule->subject_ref);

        if ($rule->rule === TicketRule::MIN) {
            return $subject < $rule->amount();
        }

        $touchesCurrent = $this->units($current, $rule->subject_type, $rule->subject_ref) > 0
            || ($rule->hasTarget() && $this->units($current, $rule->target_type, $rule->target_ref) > 0);
        if (!$touchesCurrent) {
            return false;
        }

        switch ($rule->rule) {
            case TicketRule::MAX:
                return $subject > $rule->amount();
            case TicketRule::REQUIRES:
                return $subject > 0 && $this->units($counted, $rule->target_type, $rule->target_ref) < $rule->amount();
            case TicketRule::EXCLUDES:
                return $subject > 0 && $this->units($counted, $rule->target_type, $rule->target_ref) > 0;
        }
        return false;
    }

    public function rule(int $id): ?TicketRule
    {
        return $this->rules->firstWhere('id', $id);
    }

    // Tickets de la lista que son sujeto de la regla.
    public function subjectTicketIds(TicketRule $rule, array $ticketIds): array
    {
        return array_values(array_filter($ticketIds, function ($id) use ($rule) {
            $ticket = $this->tickets[$id] ?? null;
            return $ticket && $this->matches($ticket, $rule->subject_type, $rule->subject_ref);
        }));
    }

    // Unidades del carrito que pertenecen al ticket, categoría o tipo.
    private function units(array $cart, ?string $type, $ref): int
    {
        $sum = 0;
        foreach ($cart as $id => $qty) {
            $ticket = $this->tickets[$id] ?? null;
            if ($ticket && $this->matches($ticket, $type, $ref)) {
                $sum += $qty;
            }
        }
        return $sum;
    }

    private function matches(EventTicket $ticket, ?string $type, $ref): bool
    {
        switch ($type) {
            case TicketRule::TYPE_TICKET:
                return (string) $ticket->id === (string) $ref;
            case TicketRule::TYPE_CATEGORY:
                return $ticket->categories->contains('id', (int) $ref);
            case TicketRule::TYPE_KIND:
                return ($ticket->kind ?: EventTicket::KIND_PRINCIPAL) === $ref;
        }
        return false;
    }

    private function violation(TicketRule $rule, ?int $entry = null, ?int $entryTicketId = null, bool $byPrevious = false): array
    {
        $text = function ($lang) use ($rule, $byPrevious) {
            $sentence = $rule->sentence($this->namer($lang), $lang);
            if (!$byPrevious) {
                return $sentence;
            }
            return rtrim($sentence, '.') . ($lang === 'esp'
                ? ', contando lo que ya compraste en este evento.'
                : ', counting what you already bought for this event.');
        };

        $prefix = function ($lang) use ($entry, $entryTicketId) {
            if ($entry === null) {
                return '';
            }
            $ticket = $this->tickets[$entryTicketId] ?? null;
            $name = $ticket ? ($lang === 'esp' ? $ticket->name : ($ticket->name_eng ?: $ticket->name)) : '';
            return ($lang === 'esp' ? "Entrada {$entry}" : "Ticket {$entry}") . ($name ? " ({$name})" : '') . ': ';
        };

        $addTicket = null;
        if ($rule->rule === TicketRule::REQUIRES && $rule->target_type === TicketRule::TYPE_TICKET) {
            $addTicket = (int) $rule->target_ref;
        } elseif ($rule->rule === TicketRule::MIN && $rule->subject_type === TicketRule::TYPE_TICKET) {
            $addTicket = (int) $rule->subject_ref;
        }

        return [
            'rule_id' => $rule->id,
            'entry' => $entry,
            'message_es' => $prefix('esp') . $text('esp'),
            'message_en' => $prefix('eng') . $text('eng'),
            // sin "Entrada n (...): ", para mostrarlo dentro de la entrada
            'text_es' => $text('esp'),
            'text_en' => $text('eng'),
            'add_ticket_id' => $addTicket,
        ];
    }

    /**
     * Nombre de un ticket, categoría o tipo, para las frases de las reglas.
     */
    public function namer(string $lang = 'esp'): callable
    {
        $esp = $lang === 'esp';
        $categories = $this->categories;

        return function ($type, $ref) use ($esp, $categories) {
            switch ($type) {
                case TicketRule::TYPE_TICKET:
                    $ticket = $this->tickets[(int) $ref] ?? null;
                    return $ticket ? ($esp ? $ticket->name : ($ticket->name_eng ?: $ticket->name)) : '(ticket eliminado)';
                case TicketRule::TYPE_CATEGORY:
                    $category = $categories[(int) $ref] ?? null;
                    return $category ? ($esp ? $category->name : ($category->name_eng ?: $category->name)) : '(categoría eliminada)';
                case TicketRule::TYPE_KIND:
                    if ($ref === EventTicket::KIND_COMPLEMENTARY) {
                        return $esp ? 'Complementarios' : 'Add-ons';
                    }
                    return $esp ? 'Entradas principales' : 'Main tickets';
            }
            return '';
        };
    }
}
