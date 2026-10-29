<?php

namespace Tests\Unit;

use Masso\EventTicket;
use Masso\TicketRule;
use PHPUnit\Framework\TestCase;

class CheckoutCartTest extends TestCase
{
    public function test_cart_from_input_defaults_quantity_to_one_and_skips_invalid_ids(): void
    {
        $cart = EventTicket::cartFromInput(['10', '11', '0', 'x'], ['11' => '3']);

        $this->assertSame([10 => 1, 11 => 3], $cart);
    }

    public function test_cart_from_input_never_goes_below_one(): void
    {
        $this->assertSame([10 => 1], EventTicket::cartFromInput([10], [10 => -4]));
    }

    public function test_entries_from_input_normalizes_and_drops_empty_items(): void
    {
        $entries = EventTicket::entriesFromInput([
            ['ticket' => '5', 'items' => ['7' => '1', '8' => '0']],
            ['ticket' => '0', 'items' => ['7' => '1']],
            ['ticket' => '6'],
        ]);

        $this->assertSame([
            ['ticket' => 5, 'items' => [7 => 1]],
            ['ticket' => 6, 'items' => []],
        ], $entries);
    }

    public function test_cart_from_entries_adds_up_entries_and_their_items(): void
    {
        $cart = EventTicket::cartFromEntries([
            ['ticket' => 5, 'items' => [7 => 1, 9 => 2]],
            ['ticket' => 5, 'items' => [7 => 1]],
            ['ticket' => 6, 'items' => []],
        ]);

        $this->assertSame([5 => 2, 7 => 2, 9 => 2, 6 => 1], $cart);
    }

    public function test_rule_sentences_in_spanish_and_english(): void
    {
        $name = fn ($type, $ref) => $type === TicketRule::TYPE_CATEGORY ? 'Médicos' : 'Cena';

        $requires = new TicketRule(['subject_type' => 'ticket', 'subject_ref' => '1', 'rule' => 'requires', 'target_type' => 'category', 'target_ref' => '2', 'quantity' => 2]);
        $this->assertSame('Si eliges «Cena», debes agregar al menos 2 de la categoría «Médicos».', $requires->sentence($name));
        $this->assertSame('If you choose «Cena», you must add at least 2 from the «Médicos» category.', $requires->sentence($name, 'eng'));

        $excludes = new TicketRule(['subject_type' => 'category', 'subject_ref' => '2', 'rule' => 'excludes', 'target_type' => 'kind', 'target_ref' => 'complementario']);
        $this->assertSame('No puedes combinar tickets de «Médicos» con complementarios.', $excludes->sentence($name));

        $max = new TicketRule(['subject_type' => 'kind', 'subject_ref' => 'principal', 'rule' => 'max', 'quantity' => 0]);
        $this->assertSame('Puedes elegir como máximo 0 de las entradas principales.', $max->sentence($name));
    }

    public function test_requires_without_quantity_means_one(): void
    {
        $rule = new TicketRule(['rule' => 'requires', 'quantity' => null]);

        $this->assertSame(1, $rule->amount());
        $this->assertTrue($rule->hasTarget());
        $this->assertFalse((new TicketRule(['rule' => 'max']))->hasTarget());
    }
}
