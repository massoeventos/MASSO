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

    // Mensajes del cupón en español (esp) e inglés (eng).
    public const MESSAGES = [
        'invalid' => ['Cupón inválido.', 'Invalid coupon.'],
        'unavailable' => ['El cupón no está disponible actualmente', 'The coupon is not currently available'],
        'limit' => ['El cupón ha alcanzado su límite de uso', 'The coupon has reached its usage limit'],
        'tickets' => ['El cupón no es válido para los siguientes tickets', 'The coupon is not valid for the following tickets'],
        'valid' => ['Cupón válido.', 'Valid coupon.'],
    ];

    public static function message(string $key, string $lang = 'esp'): string
    {
        return self::MESSAGES[$key][$lang === 'eng' ? 1 : 0];
    }

    public function validateForTickets(array $ticketIds, string $lang = 'esp'): array
    {
        $result = [
            'valid' => false,
            'discount_percentage' => null,
            'message' => null,
            'invalid_ticket_names' => [],
        ];

        $today = Carbon::now()->toDateString();

        // Validar fechas de validez del cupón
        if (
            ($this->starts_at && $this->starts_at > $today) ||
            ($this->ends_at && $this->ends_at < $today)
        ) {
            $result['message'] = self::message('unavailable', $lang);
            return $result;
        }

        // Validar si se alcanzó el límite de uso
        if ($this->usage_limit !== null) {
            $usedCount = $this->payments()->where('status', 'pagado')->count();
            if ($usedCount >= $this->usage_limit) {
                $result['message'] = self::message('limit', $lang);
                return $result;
            }
        }

        // Validar tickets permitidos por el cupón
        $validTicketIds = $this->tickets()->pluck('event_ticket_id')->toArray();
        $invalidTicketIds = array_diff($ticketIds, $validTicketIds);

        if (!empty($invalidTicketIds)) {
            $invalidTicketNames = EventTicket::whereIn('id', $invalidTicketIds)->get()
                ->map(fn ($t) => $lang === 'eng' ? ($t->name_eng ?: $t->name) : $t->name)->all();
            $result['message'] = self::message('tickets', $lang);
            $result['invalid_ticket_names'] = $invalidTicketNames;
            return $result;
        }

        // Cupón válido
        $result['valid'] = true;
        $result['discount_percentage'] = $this->discount_percentage;
        $result['message'] = self::message('valid', $lang);
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

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}