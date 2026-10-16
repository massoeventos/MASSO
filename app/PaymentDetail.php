<?php

namespace Masso;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PaymentDetail extends Model
{
    use SoftDeletes;

    protected $table = 'payments_detail';
    protected $primaryKey = 'id';

    public const STATUS_RESERVED = 'reservado';
    public const STATUS_CONFIRMED = 'confirmado';
    public const STATUS_VOIDED = 'anulado';
    public const STATUS_REPLACED = 'reemplazado';

    protected $fillable = [
        'type',
        'payment_id',
        'ticket_id',
        'price',
        'status',
        'replaces_id',
        'parent_id',
        'required_document_file'
    ];

    public function ticket()
    {
        return $this->hasOne('Masso\EventTicket', 'id', 'ticket_id')->withTrashed();
    }

    public function payment()
    {
        return $this->belongsTo('Masso\Payment', 'payment_id', 'id')->withTrashed();
    }

    // Entrada (unidad principal) de la que cuelga este complementario.
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id')->withTrashed();
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    public function replaces()
    {
        return $this->belongsTo(self::class, 'replaces_id', 'id')->withTrashed();
    }

    public function replacedBy()
    {
        return $this->hasOne(self::class, 'replaces_id', 'id')->withTrashed();
    }

     /**
      * Una fila por unidad: 2 × Cena son 2 filas (y luego 2 inscritos).
      * $quantities = [ticket_id => cantidad]; sin cantidad, 1.
      * Con $entries (ver EventTicket::entriesFromInput) cada complementario
      * queda colgando (parent_id) de la fila de su entrada.
      */
     public function addDetails($payment, $object, $ids, $quantities = [], $entries = null)
     {
        switch ($object) {
            case 'EventTicket':
                // El ticket nace confirmado si el pago ya está aprobado
                // (ej. entradas gratuitas, que se marcan 'pagado' al instante);
                // si no, queda reservado hasta que el pago se confirme.
                $status = $payment->status === 'pagado' ? self::STATUS_CONFIRMED : self::STATUS_RESERVED;
                if (!empty($entries)) {
                    $this->addEntryDetails($payment, $entries, $status);
                    break;
                }

                $cart = EventTicket::cartFromInput($ids, $quantities);

                if (empty($cart)) {
                    return;
                }

                $now = now();
                $rows = [];
                foreach (EventTicket::whereIn('id', array_keys($cart))->get(['id', 'price']) as $ticket) {
                    for ($i = 0; $i < $cart[$ticket->id]; $i++) {
                        $rows[] = [
                            'type' => 1,
                            'payment_id' => $payment->id,
                            'ticket_id' => $ticket->id,
                            'price' => $ticket->price,
                            'status' => $status,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                DB::table('payments_detail')->insert($rows);
                break;
        }
     }

    private function addEntryDetails($payment, array $entries, string $status): void
    {
        $cart = EventTicket::cartFromEntries($entries);
        $prices = EventTicket::whereIn('id', array_keys($cart))->pluck('price', 'id');
        $row = fn ($ticketId, $parentId = null) => [
            'type' => 1,
            'payment_id' => $payment->id,
            'ticket_id' => $ticketId,
            'parent_id' => $parentId,
            'price' => $prices[$ticketId] ?? 0,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::transaction(function () use ($entries, $row) {
            foreach ($entries as $entry) {
                $parentId = DB::table('payments_detail')->insertGetId($row($entry['ticket']));
                $children = [];
                foreach ($entry['items'] as $ticketId => $qty) {
                    for ($i = 0; $i < $qty; $i++) {
                        $children[] = $row($ticketId, $parentId);
                    }
                }
                if ($children) {
                    DB::table('payments_detail')->insert($children);
                }
            }
        });
    }
}
