<?php

namespace Masso;

use Illuminate\Database\Eloquent\Model;

class DeviceProfile extends Model
{
    protected $table = 'device_profiles';

    protected $fillable = [
        'device_token',
        'last_payment_id',
        'last_inscription_payment_id',
    ];
}
