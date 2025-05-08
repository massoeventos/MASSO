<?php

namespace Masso;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';

    protected $fillable = [
        'name',
        'code',
        'is_other',
    ];

    protected $casts = [
        'is_other' => 'boolean',
    ];
    
    public static $CHILE_NAME = 'Chile';

    public function getTranslatedName($lang){
        return $this->attributes['is_other'] ? ($lang == 'esp' ? 'Otro' : 'Other') : $this->attributes['name'];
    }

    public function regions()
    {
        return $this->hasMany(Region::class);
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
