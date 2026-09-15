<?php

namespace Tests\Unit;

use Masso\Services\EnrollmentDataResolver;
use PHPUnit\Framework\TestCase;

class EnrollmentDataResolverTest extends TestCase
{
    public function test_extra_fields_strips_known_keys(): void
    {
        $raw = [
            'name' => 'Ana',
            'rut' => '11111111-1',
            'ticket_id' => 5,
            'talla_polera' => 'M',
            'universidad' => 'UC',
        ];

        $extra = EnrollmentDataResolver::extraFields($raw);

        $this->assertSame(['talla_polera' => 'M', 'universidad' => 'UC'], $extra);
    }

    public function test_extra_fields_keeps_unknown_keys_untouched_when_nothing_matches(): void
    {
        $raw = ['talla_polera' => 'M'];

        $this->assertSame($raw, EnrollmentDataResolver::extraFields($raw));
    }

    public function test_merge_with_columns_lets_real_column_win_over_blob(): void
    {
        $columns = ['Nombre' => 'Ana', 'RUT' => '11111111-1'];
        $raw = ['name' => 'valor viejo del blob', 'talla_polera' => 'M'];

        $merged = EnrollmentDataResolver::mergeWithColumns($columns, $raw);

        $this->assertSame([
            'talla_polera' => 'M',
            'Nombre' => 'Ana',
            'RUT' => '11111111-1',
        ], $merged);
    }

    public function test_column_or_fallback_prefers_real_column_when_present(): void
    {
        $this->assertSame(
            'Ingeniera',
            EnrollmentDataResolver::columnOrFallback('Ingeniera', ['profession' => 'legado'], 'profession')
        );
    }

    public function test_column_or_fallback_uses_blob_only_when_column_is_empty(): void
    {
        $this->assertSame(
            'legado',
            EnrollmentDataResolver::columnOrFallback('', ['profession' => 'legado'], 'profession')
        );
        $this->assertSame(
            'legado',
            EnrollmentDataResolver::columnOrFallback(null, ['profession' => 'legado'], 'profession')
        );
    }

    public function test_column_or_fallback_returns_original_when_neither_has_value(): void
    {
        $this->assertSame('', EnrollmentDataResolver::columnOrFallback('', [], 'profession'));
    }
}
