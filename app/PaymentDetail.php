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

    protected $fillable = [
        'type',
        'payment_id',
        'ticket_id',
        'price'
    ];

    public function ticket()
    {
        return $this->hasOne('Masso\EventTicket', 'id', 'ticket_id')->withTrashed();
    }

     public function addDetail($payment, $object, $ids)
     {
        switch ($object) {
            case 'EventTicket':
                $idsString = implode(',', $ids);
                DB::insert("INSERT INTO payments_detail (type, payment_id, ticket_id, price, created_at, updated_at) SELECT 1, {$payment->id}, id, price, now(), now() FROM events_tickets WHERE id in ({$idsString})");
                break;
        }
     }
}
