<?php

namespace Tests\Unit;

use Masso\Services\LegacySerializedData;
use PHPUnit\Framework\TestCase;

class LegacySerializedDataTest extends TestCase
{
    public function test_null_is_empty(): void
    {
        $this->assertSame([], LegacySerializedData::safeUnserialize(null));
        $this->assertSame(
            ['status' => LegacySerializedData::STATUS_EMPTY, 'depth' => 0, 'data' => [], 'repaired' => false],
            LegacySerializedData::inspect(null)
        );
    }

    public function test_empty_string_is_empty(): void
    {
        $this->assertSame([], LegacySerializedData::safeUnserialize(''));
        $this->assertSame(LegacySerializedData::STATUS_EMPTY, LegacySerializedData::inspect('')['status']);
    }

    public function test_simple_serialized_array_is_collapsed_once(): void
    {
        $original = ['name' => 'Ana', 'lastname' => 'Pérez', 'ticket_id' => 42];

        $result = LegacySerializedData::inspect(serialize($original));

        $this->assertSame(LegacySerializedData::STATUS_SIMPLE, $result['status']);
        $this->assertSame(1, $result['depth']);
        $this->assertSame($original, $result['data']);
        $this->assertFalse($result['repaired']);
        $this->assertSame($original, LegacySerializedData::safeUnserialize(serialize($original)));
    }

    public function test_double_serialized_array_is_collapsed_to_original(): void
    {
        $original = ['name' => 'Juan', 'event_id' => 7];

        $doublySerialized = serialize(serialize($original));

        $result = LegacySerializedData::inspect($doublySerialized);

        $this->assertSame(LegacySerializedData::STATUS_DOUBLE_OR_MORE, $result['status']);
        $this->assertSame(2, $result['depth']);
        $this->assertSame($original, $result['data']);
    }

    public function test_triple_serialized_array_is_still_collapsed(): void
    {
        $original = ['email' => 'cliente@example.com'];

        $tripleSerialized = serialize(serialize(serialize($original)));

        $result = LegacySerializedData::inspect($tripleSerialized);

        $this->assertSame(3, $result['depth']);
        $this->assertSame($original, $result['data']);
    }

    public function test_corrupt_string_returns_empty_array(): void
    {
        $result = LegacySerializedData::inspect('esto no es un serialize valido {{{');

        $this->assertSame(LegacySerializedData::STATUS_CORRUPT, $result['status']);
        $this->assertSame([], $result['data']);
    }

    public function test_serialized_scalar_is_treated_as_corrupt_for_this_use_case(): void
    {
        // El dominio siempre espera un array (name/lastname/ticket_id/...),
        // así que un escalar válido serializado no cuenta como dato usable.
        $result = LegacySerializedData::inspect(serialize('solo-un-texto'));

        $this->assertSame(LegacySerializedData::STATUS_CORRUPT, $result['status']);
        $this->assertSame([], $result['data']);
    }

    public function test_serialized_false_edge_case_does_not_break_the_loop(): void
    {
        // 'b:0;' es el serialize() de `false`, que unserialize() también
        // devuelve como `false` en éxito: no debe confundirse con un error.
        $result = LegacySerializedData::inspect('b:0;');

        $this->assertSame(LegacySerializedData::STATUS_CORRUPT, $result['status']);
        $this->assertSame([], $result['data']);
    }

    public function test_array_input_is_passed_through(): void
    {
        $original = ['already' => 'decoded'];

        $this->assertSame($original, LegacySerializedData::safeUnserialize($original));
    }

    public function test_repairs_string_with_wrong_declared_length(): void
    {
        $original = ['name' => 'Andressa', 'lastname' => "Baratela's companion"];
        $serialized = serialize($original);

        // Simula la corrupción real observada en producción: comillas
        // simples duplicadas dentro de un valor, que alargan el contenido
        // real sin actualizar el prefijo s:N: que declara su longitud.
        $corrupted = str_replace("Baratela's", "Baratela''s", $serialized);

        // Confirma que, sin la reparación, PHP no puede leer el valor.
        $this->assertFalse(@unserialize($corrupted));

        $result = LegacySerializedData::inspect($corrupted);

        $this->assertSame(LegacySerializedData::STATUS_SIMPLE, $result['status']);
        $this->assertTrue($result['repaired']);
        $this->assertSame('Andressa', $result['data']['name']);
        $this->assertSame("Baratela''s companion", $result['data']['lastname']);
    }

    public function test_repairs_wrong_length_inside_a_doubly_serialized_value(): void
    {
        $original = ['email' => 'cliente@example.com', 'note' => "it's doubled"];
        $innerSerialized = serialize($original);

        // La corrupción real ocurre en events_enroll cuando SendNotifications
        // vuelve a serializar un payments.data que ya venía dañado: el largo
        // del wrapper exterior queda correcto (mide el string ya corrupto),
        // pero la capa interior sigue con su prefijo desalineado.
        $corruptedInner = str_replace("it's doubled", "it''s doubled", $innerSerialized);
        $doublySerialized = serialize($corruptedInner);

        $this->assertFalse(@unserialize($corruptedInner));

        $result = LegacySerializedData::inspect($doublySerialized);

        $this->assertSame(LegacySerializedData::STATUS_DOUBLE_OR_MORE, $result['status']);
        $this->assertTrue($result['repaired']);
        $this->assertSame('cliente@example.com', $result['data']['email']);
        $this->assertSame("it''s doubled", $result['data']['note']);
    }

    public function test_repair_gives_up_cleanly_on_truly_unparseable_garbage(): void
    {
        $result = LegacySerializedData::inspect('s:999:"esto no tiene ningun cierre valido');

        $this->assertSame(LegacySerializedData::STATUS_CORRUPT, $result['status']);
        $this->assertSame([], $result['data']);
        $this->assertFalse($result['repaired']);
    }
}
