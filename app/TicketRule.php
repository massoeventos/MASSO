<?php

namespace Masso;

use Illuminate\Database\Eloquent\Model;

/**
 * Regla de compra de un evento. Sujeto y objetivo son un ticket, una
 * categoría o un tipo de ticket (principal / complementario):
 *   requires: si hay sujeto, debe haber al menos `quantity` (1) del objetivo.
 *   excludes: si hay sujeto, no puede haber objetivo.
 *   max:      el sujeto no supera `quantity` unidades.
 *   min:      debe haber al menos `quantity` unidades del sujeto.
 * Ámbito: `entrada` se evalúa dentro de cada entrada (la entrada principal
 * con sus complementarios); `compra`, sobre todo el carrito. Se evalúa con
 * Services\Checkout\TicketRuleEvaluator.
 */
class TicketRule extends Model
{
    protected $table = 'ticket_rules';

    protected $fillable = [
        'event_id', 'scope', 'subject_type', 'subject_ref', 'rule',
        'target_type', 'target_ref', 'quantity', 'position',
    ];

    public const SCOPE_ENTRY = 'entrada';
    public const SCOPE_PURCHASE = 'compra';
    public const SCOPES = [self::SCOPE_ENTRY, self::SCOPE_PURCHASE];

    public const TYPE_TICKET = 'ticket';
    public const TYPE_CATEGORY = 'category';
    public const TYPE_KIND = 'kind';
    public const TYPES = [self::TYPE_TICKET, self::TYPE_CATEGORY, self::TYPE_KIND];

    public const REQUIRES = 'requires';
    public const EXCLUDES = 'excludes';
    public const MAX = 'max';
    public const MIN = 'min';
    public const RULES = [self::REQUIRES, self::EXCLUDES, self::MAX, self::MIN];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }

    public function hasTarget(): bool
    {
        return in_array($this->rule, [self::REQUIRES, self::EXCLUDES], true);
    }

    // Unidades que exige o permite la regla (requires sin cantidad = 1).
    public function amount(): int
    {
        return $this->quantity === null ? 1 : (int) $this->quantity;
    }

    public function mentions(string $type, $ref): bool
    {
        return ($this->subject_type === $type && (string) $this->subject_ref === (string) $ref)
            || ($this->target_type === $type && (string) $this->target_ref === (string) $ref);
    }

    /**
     * La regla en una frase, para el admin y los mensajes del checkout.
     * $name: fn(string $type, string $ref) => nombre del ticket o categoría.
     */
    public function sentence(callable $name, string $lang = 'esp'): string
    {
        $esp = $lang === 'esp';
        $n = $this->amount();
        $s = fn ($form) => self::phrase($this->subject_type, $this->subject_ref, $name, $form, $esp);
        $t = fn ($form) => self::phrase($this->target_type, $this->target_ref, $name, $form, $esp);

        switch ($this->rule) {
            case self::REQUIRES:
                return $esp
                    ? "Si eliges {$s('one')}, debes agregar al menos {$n} de {$t('set')}."
                    : "If you choose {$s('one')}, you must add at least {$n} from {$t('set')}.";
            case self::EXCLUDES:
                return $esp
                    ? "No puedes combinar {$s('plain')} con {$t('plain')}."
                    : "You cannot combine {$s('plain')} with {$t('plain')}.";
            case self::MAX:
                return $esp
                    ? "Puedes elegir como máximo {$n} de {$s('set')}."
                    : "You can choose at most {$n} from {$s('set')}.";
            default:
                return $esp
                    ? "Debes elegir al menos {$n} de {$s('set')}."
                    : "You must choose at least {$n} from {$s('set')}.";
        }
    }

    // one: "un ticket de «X»"; set: "la categoría «X»"; plain: "tickets de «X»".
    private static function phrase(?string $type, $ref, callable $name, string $form, bool $esp): string
    {
        if ($type === self::TYPE_KIND) {
            $complementary = $ref === EventTicket::KIND_COMPLEMENTARY;
            $forms = $esp
                ? ($complementary
                    ? ['one' => 'un complementario', 'set' => 'los complementarios', 'plain' => 'complementarios']
                    : ['one' => 'una entrada principal', 'set' => 'las entradas principales', 'plain' => 'entradas principales'])
                : ($complementary
                    ? ['one' => 'an add-on', 'set' => 'the add-ons', 'plain' => 'add-ons']
                    : ['one' => 'a main ticket', 'set' => 'the main tickets', 'plain' => 'main tickets']);
            return $forms[$form];
        }

        $label = '«' . $name($type, $ref) . '»';
        if ($type === self::TYPE_CATEGORY) {
            $forms = $esp
                ? ['one' => "un ticket de {$label}", 'set' => "la categoría {$label}", 'plain' => "tickets de {$label}"]
                : ['one' => "a ticket from {$label}", 'set' => "the {$label} category", 'plain' => "tickets from {$label}"];
            return $forms[$form];
        }
        return $label;
    }
}
