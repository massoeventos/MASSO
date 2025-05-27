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
            // Limpiar el valor: quitar puntos y guiones
            $rut = strtoupper(preg_replace('/[^0-9Kk]/', '', $value));


            // Validar longitud mínima y máxima (7 a 8 dígitos + 1 verificador)
            if (strlen($rut) < 8 || strlen($rut) > 9) {
                return false;
            }

            // Separar cuerpo y dígito verificador
            $body = substr($rut, 0, -1);
            $dv = substr($rut, -1);

            // Validar que el cuerpo solo contenga números
            if (!ctype_digit($body)) {
                return false;
            }

            // Cálculo del dígito verificador
            $sum = 0;
            $multiplier = 2;

            for ($i = strlen($body) - 1; $i >= 0; $i--) {
                $sum += ((int) $body[$i]) * $multiplier;
                $multiplier = $multiplier == 7 ? 2 : $multiplier + 1;
            }

            $rest = $sum % 11;
            $checkDigit = 11 - $rest;

            if ($checkDigit == 11) {
                $checkDigit = '0';
            } elseif ($checkDigit == 10) {
                $checkDigit = 'K';
            } else {
                $checkDigit = (string) $checkDigit;
            }

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
