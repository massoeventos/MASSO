<?php

namespace Masso\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if(env('APP_ENV') !== 'local') {
            \URL::forceScheme('https');
        }
        
        Validator::extend('valid_rut', function ($attribute, $value, $parameters, $validator) {
            $rut = strtoupper(preg_replace('/[^0-9Kk]/', '', $value));
            if (strlen($rut) < 2) return false;
    
            $body = substr($rut, 0, -1);
            $dv = substr($rut, -1);
    
            $sum = 0;
            $multiplier = 2;
    
            for ($i = strlen($body) - 1; $i >= 0; $i--) {
                $sum += $body[$i] * $multiplier;
                $multiplier = $multiplier == 7 ? 2 : $multiplier + 1;
            }
    
            $rest = $sum % 11;
            $checkDigit = 11 - $rest;
    
            if ($checkDigit == 11) $checkDigit = '0';
            elseif ($checkDigit == 10) $checkDigit = 'K';
            else $checkDigit = (string) $checkDigit;
    
            return $dv === $checkDigit;
        }, 'El RUT ingresado no es válido.');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
