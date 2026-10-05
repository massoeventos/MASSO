<?php

namespace Masso;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

/**
 * El comprador (Bloque 3) — identidad separada de `Masso\User` (que es
 * solo para staff/admin). Nunca se le exige password: `password` es
 * nullable y solo se completa si el cliente decide configurarlo desde
 * su historial de compras (ítem 3.3). El login normal es por código
 * temporal (ver LoginCodeService / OtpChannel).
 */
class Customer extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword, SoftDeletes;

    protected $table = 'customers';
    protected $primaryKey = 'id';

    protected $fillable = [
        'email',
        'name',
        'lastname',
        'rut',
        'phone',
        'password',
        'gender',
        'passport',
        'nationality_country_id',
        'city_id',
        'country_id',
        'custom_city',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = is_string($value) ? mb_strtolower(trim($value)) : $value;
    }

    public function payments()
    {
        return $this->hasMany('Masso\Payment', 'customer_id', 'id');
    }

    public function loginCodes()
    {
        return $this->hasMany('Masso\CustomerLoginCode', 'customer_id', 'id');
    }

    public function city()
    {
        return $this->belongsTo('Masso\City', 'city_id', 'id');
    }

    public function country()
    {
        return $this->belongsTo('Masso\Country', 'country_id', 'id');
    }

    public function nationalityCountry()
    {
        return $this->belongsTo('Masso\Country', 'nationality_country_id', 'id');
    }

    public function hasPassword(): bool
    {
        return !empty($this->password);
    }
}
