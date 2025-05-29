<?php

namespace Masso;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
	use SoftDeletes;

    protected $fillable = [
        'event_id',
        'code',
        'discount_percentage',
        'usage_limit',
        'used_count',
        'starts_at',
        'ends_at',
    ];

    public function setStartsAtAttribute($value)
    {
        $this->attributes['starts_at'] = $value ?: null;
    }

    public function setEndsAtAttribute($value)
    {
        $this->attributes['ends_at'] = $value ?: null;
    }

    public function validateForTickets(array $ticketIds): array
    {
        $result = [
            'valid' => false,
            'discount_percentage' => null,
            'message' => null,
            'invalid_ticket_names' => [],
        ];

        // Validar existencia y fechas
        $today = Carbon::now()->toDateString();

        if (
            ($this->starts_at && $this->starts_at > $today) ||
            ($this->ends_at && $this->ends_at < $today)
        ) {
            $result['message'] = 'El cupón no está disponible actualmente.';
            return $result;
        }

        // Verificar tickets válidos para el cupón
        $validTicketIds = $this->tickets()->pluck('event_ticket_id')->toArray();
        $invalidTicketIds = array_diff($ticketIds, $validTicketIds);

        if (!empty($invalidTicketIds)) {
            $invalidTicketNames = EventTicket::whereIn('id', $invalidTicketIds)->pluck('name')->toArray();
            $result['message'] = 'El cupón no es válido para los siguientes tickets';
            $result['invalid_ticket_names'] = $invalidTicketNames;
            return $result;
        }

        // Todo bien
        $result['valid'] = true;
        $result['discount_percentage'] = $this->discount_percentage;
        $result['message'] = 'Cupón válido.';
        return $result;
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function tickets()
    {
        return $this->belongsToMany(EventTicket::class, 'coupon_ticket');
    }
}