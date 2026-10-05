<?php

namespace Masso;

use Illuminate\Database\Eloquent\Model;

class CustomerLoginCode extends Model
{
    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_SMS = 'sms';

    public const MAX_ATTEMPTS = 5;

    protected $table = 'customer_login_codes';
    protected $primaryKey = 'id';

    protected $fillable = [
        'customer_id',
        'code_hash',
        'channel',
        'attempts',
        'expires_at',
        'consumed_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo('Masso\Customer', 'customer_id', 'id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isConsumed(): bool
    {
        return $this->consumed_at !== null;
    }

    public function hasAttemptsLeft(): bool
    {
        return $this->attempts < self::MAX_ATTEMPTS;
    }
}
