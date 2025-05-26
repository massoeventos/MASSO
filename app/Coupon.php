<?php

namespace Masso;

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

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function tickets()
    {
        return $this->belongsToMany(EventTicket::class, 'coupon_ticket');
    }
}