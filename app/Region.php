<?php

namespace Masso;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $table = 'regions';

    protected $fillable = [
        'name',
        'country_id',
        'is_other',
    ];

    protected $casts = [
        'is_other' => 'boolean',
    ];

    public function getTranslatedName($lang){
        return $this->attributes['is_other'] ? ($lang == 'esp' ? 'Otra' : 'Other') : $this->attributes['name'];
    }
    
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
