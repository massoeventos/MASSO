<?php
namespace Masso;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    
    protected $fillable = ['name', 'slug', 'description'];

    public function users()
    {
        return $this->hasMany(config('auth.providers.users.model'));
    }
    
    public function permissions()
    {
        return $this->belongsToMany('Masso\Permission')->withTimestamps();
    }

}
