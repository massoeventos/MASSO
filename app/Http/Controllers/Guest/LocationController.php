<?php

namespace Masso\Http\Controllers\Guest;

use Illuminate\Http\Request;
use Masso\Http\Controllers\Controller;
use Masso\Region;
use Masso\City;

class LocationController extends Controller
{
    public function getRegions($country_id) {
        $lang = request('lang', 'esp');

        return Region::where('country_id', $country_id)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function ($region) use ($lang) {
                return [$region->id => $region->getTranslatedName($lang)];
            });
    }
    
    public function getCities($region_id) {
        $lang = request('lang', 'esp');

        return City::where('region_id', $region_id)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function ($city) use ($lang) {
                return [$city->id => $city->getTranslatedName($lang)];
            });
    }
}
