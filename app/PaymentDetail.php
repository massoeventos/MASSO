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

    public function replaces()
    {
        return $this->belongsTo(self::class, 'replaces_id', 'id')->withTrashed();
    }

    public function replacedBy()
    {
        return $this->hasOne(self::class, 'replaces_id', 'id')->withTrashed();
    }

     public function addDetails($payment, $object, $ids)
     {
        switch ($object) {
            case 'EventTicket':
                // El ticket nace confirmado si el pago ya está aprobado
                // (ej. entradas gratuitas, que se marcan 'pagado' al instante);
                // si no, queda reservado hasta que el pago se confirme.
                $status = $payment->status === 'pagado' ? self::STATUS_CONFIRMED : self::STATUS_RESERVED;
                $ticketIds = array_values(array_filter(array_map('intval', $ids)));

                if (empty($ticketIds)) {
                    return;
                }

                $placeholders = implode(',', array_fill(0, count($ticketIds), '?'));

                DB::insert(
                    "INSERT INTO payments_detail (type, payment_id, ticket_id, price, status, created_at, updated_at)
                     SELECT 1, ?, id, price, ?, now(), now() FROM events_tickets WHERE id IN ({$placeholders})",
                    array_merge([$payment->id, $status], $ticketIds)
                );
                break;
        }
     }
}
