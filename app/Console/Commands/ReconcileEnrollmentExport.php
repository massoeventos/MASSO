<?php

namespace Masso\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Masso\Services\LegacySerializedData;
use Masso\Services\EnrollmentDataResolver;

/**
 * Reconciliación de solo lectura: compara, fila por fila y sobre datos
 * reales, la lógica VIEJA de armado de "campos extra" que tenía
 * EnrollController::index() (antes de introducir EnrollmentDataResolver
 * en el ítem 1.4) contra la lógica NUEVA. Pensado para correr en staging
 * con una copia de PROD antes de confiar en el export refactorizado, y
 * para volver a correrse cada vez que se toque EnrollmentDataResolver.
 *
 * No escribe nada en la BD. Sale con código 1 si encuentra diferencias,
 * para poder usarse como gate antes de un deploy.
 */
class ReconcileEnrollmentExport extends Command
{
    protected $signature = 'masso:reconcile-enrollment-export
        {--event= : Filtrar por event_id}
        {--chunk=500 : Tamaño del lote}
        {--sample=5 : Cantidad de diferencias a mostrar en detalle}';

    protected $description = 'Compara la lógica vieja vs. la nueva (EnrollmentDataResolver) de los "campos extra" del export de inscritos, sobre datos reales';

    // Copia exacta de la lógica que tenía EnrollController::index() antes
    // del ítem 1.4 (ver historial de git de ese método).
    private const OLD_EXCLUDED = [
        'status', 'type', 'managment', 'has_inscription', 'ticket_id',
        'billing_method', 'invoice_data', 'rut', 'city_id',
        'nationality_country_id', 'country_id', 'region_id',
        'custom_city', 'description',
    ];

    private const OLD_FIELD_PRIVATE = [
        '_token', 'name', 'lastname', 'passport', 'email', 'ticket',
        'payment', 'check', 'ids', 'amount', 'available', 'event_id',
    ];

    public function handle()
    {
        $eventFilter = $this->option('event');
        $chunkSize = max(1, (int) $this->option('chunk'));
        $sampleSize = (int) $this->option('sample');

        $query = DB::table('events_enroll')
            ->join('payments', 'payments.id', '=', 'events_enroll.payment_id')
            ->select('events_enroll.id', 'events_enroll.event_id', 'events_enroll.data', 'payments.gender as payment_gender')
            ->whereNotNull('events_enroll.data')
            ->where('events_enroll.data', '!=', '');

        if ($eventFilter !== null && $eventFilter !== '') {
            $query->where('events_enroll.event_id', $eventFilter);
        }

        $checked = 0;
        $diffs = 0;
        $samples = [];

        $query->orderBy('events_enroll.id')->chunkById($chunkSize, function ($rows) use (&$checked, &$diffs, &$samples, $sampleSize) {
            foreach ($rows as $row) {
                $raw = LegacySerializedData::safeUnserialize($row->data);

                // 'gender' es la única KNOWN_KEY que la lógica vieja no excluía
                // de los "extra": igual quedaba duplicada por payments.gender
                // al armar la fila final del export (array_merge con la columna
                // real al final). Se incluye acá para comparar el resultado
                // final real, no un paso intermedio que difiere sin impacto.
                $columnFields = ['gender' => $row->payment_gender];

                $old = array_merge($columnFields, $this->oldExtraFields($raw));
                $new = EnrollmentDataResolver::mergeWithColumns($columnFields, $raw);

                $checked++;

                if ($old != $new) {
                    $diffs++;

                    if (count($samples) < $sampleSize) {
                        $samples[] = [
                            $row->id,
                            $row->event_id,
                            json_encode($old, JSON_UNESCAPED_UNICODE),
                            json_encode($new, JSON_UNESCAPED_UNICODE),
                        ];
                    }
                }
            }
        }, 'events_enroll.id', 'id');

        $this->line("Filas comparadas: {$checked}, con diferencias: {$diffs}");

        if (!empty($samples)) {
            $this->warn('Ejemplos de diferencias (fila final vieja vs. nueva):');
            $this->table(['ID', 'Evento', 'Fila vieja', 'Fila nueva'], $samples);
        }

        return $diffs === 0 ? 0 : 1;
    }

    private function oldExtraFields(array $additional): array
    {
        $add = [];

        foreach ($additional as $key => $value) {
            if (in_array($key, self::OLD_EXCLUDED, true)) {
                continue;
            }
            if (in_array($key, self::OLD_FIELD_PRIVATE, true)) {
                continue;
            }
            $add[$key] = $value;
        }

        return $add;
    }
}
