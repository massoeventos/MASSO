<?php

namespace Masso\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Masso\EventInput;
use Masso\Payment;
use Masso\Services\LegacySerializedData;
use Masso\Services\CorruptRowLogger;

/**
 * Rellena event_input_values a partir de payments.data_json (o data, si
 * data_json todavía no existe para esa fila), mapeando cada events_inputs
 * del evento por su nombre (la misma transformación que usa el formulario
 * público: str_replace(' ', '_', $input->name)). Es best-effort: si el
 * evento no tiene inputs definidos, o la clave no aparece en el blob, esa
 * fila simplemente no se crea (no se fuerza ni se inventa el dato).
 */
class BackfillEventInputValues extends Command
{
    protected $signature = 'masso:backfill-event-input-values
        {--event= : Filtrar por event_id}
        {--chunk=200 : Tamaño del lote}
        {--dry-run : No escribe en la BD, solo reporta lo que haría}';

    protected $description = 'Rellena event_input_values a partir de payments.data_json / data (best-effort, por nombre de events_inputs)';

    public function handle()
    {
        $eventFilter = $this->option('event');
        $chunkSize = max(1, (int) $this->option('chunk'));
        $dryRun = (bool) $this->option('dry-run');

        $inputsByEvent = EventInput::all()->groupBy('event_id');

        if ($inputsByEvent->isEmpty()) {
            $this->comment('No hay events_inputs definidos, nada que hacer.');
            return 0;
        }

        $query = Payment::query()
            ->whereNotNull('event_id')
            ->where('event_id', '!=', 0)
            ->whereIn('event_id', $inputsByEvent->keys());

        if ($eventFilter !== null && $eventFilter !== '') {
            $query->where('event_id', $eventFilter);
        }

        $created = 0;
        $skippedNoInputs = 0;
        $skippedEmptyData = 0;
        $skippedCorrupt = 0;
        $logger = new CorruptRowLogger();

        $query->orderBy('id')->chunkById($chunkSize, function ($payments) use ($inputsByEvent, $dryRun, &$created, &$skippedNoInputs, &$skippedEmptyData, &$skippedCorrupt, $logger) {
            $rows = [];
            $now = now();

            foreach ($payments as $payment) {
                $inputs = $inputsByEvent->get($payment->event_id);

                if (!$inputs || $inputs->isEmpty()) {
                    $skippedNoInputs++;
                    continue;
                }

                if ($payment->data_json !== null) {
                    $raw = $payment->data_json;
                } else {
                    // data_json todavía no existe para esta fila (corrió antes
                    // de masso:backfill-data-json, o quedó fuera por corrupta):
                    // se distingue "vacío" de "corrupto" para poder loguear
                    // esto último con su data completa.
                    $inspected = LegacySerializedData::inspect($payment->data);

                    if ($inspected['status'] === LegacySerializedData::STATUS_CORRUPT) {
                        $skippedCorrupt++;
                        $logger->add('payments', $payment->id, $payment->event_id, $payment->data, 'unserialize_failed');
                        continue;
                    }

                    $raw = $inspected['data'];
                }

                if (empty($raw)) {
                    $skippedEmptyData++;
                    continue;
                }

                foreach ($inputs as $input) {
                    $key = str_replace(' ', '_', $input->name);

                    if (!array_key_exists($key, $raw)) {
                        continue;
                    }

                    $value = $raw[$key];
                    if (is_array($value)) {
                        $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                    }

                    $rows[] = [
                        'payment_id' => $payment->id,
                        'event_input_id' => $input->id,
                        'value' => $value,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if (!empty($rows)) {
                if ($dryRun) {
                    $created += count($rows);
                } else {
                    $created += DB::table('event_input_values')->insertOrIgnore($rows);
                }
            }
        }, 'id');

        $this->line(($dryRun ? '[DRY-RUN] ' : '') . "Filas creadas: {$created}, pagos sin inputs definidos: {$skippedNoInputs}, sin datos: {$skippedEmptyData}, corruptos: {$skippedCorrupt}");

        $path = $logger->write('backfill-event-input-values');
        if ($path !== null) {
            $this->warn("Se guardaron {$logger->count()} pagos corruptos (con su data completa) en: {$path}");
        }

        return 0;
    }
}
