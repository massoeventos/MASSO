<?php

namespace Masso;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Masso\Traits\Rbac;
use Masso\Traits\People;


class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Rbac, Authenticatable, Authorizable, CanResetPassword, People, SoftDeletes;

    protected $table = 'users';
    protected $fillable = ['id', 'rut', 'email', 'name', 'class', 'password','role_id'];
    protected $hidden = ['password', 'remember_token'];


    public function revisorAssigned()
    {
        return $this->belongsTo('Masso\Member', 'revisor', 'id');
    }



}
