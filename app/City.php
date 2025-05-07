<?php

namespace Masso;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $table = 'cities';

    protected $fillable = [
        'name',
        'region_id',
        'is_other',
    ];

    protected $casts = [
        'is_other' => 'boolean',
    ];

    public function getTranslatedName($lang){
        return $this->attributes['is_other'] ? ($lang == 'esp' ? 'Otra' : 'Other') : $this->attributes['name'];
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
