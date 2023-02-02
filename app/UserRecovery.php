<?php

namespace Masso;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class UserRecovery extends Model {
    use SoftDeletes;

    protected $table = 'users_recovery';
    protected $fillable = ['user_id', 'token', 'used'];


    public function user()
    {
        return $this->belongsTo('Masso\User', 'user_id', 'id');
    }

    public static function genToken($length = 20) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


}
