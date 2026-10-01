<?php

namespace Masso\Services\Otp;

use Masso\Customer;

interface OtpChannel
{
    /**
     * Envía el código de acceso al cliente. Devuelve false cuando el canal
     * no está disponible para ese cliente (ej. sin teléfono, email inválido)
     * en vez de lanzar una excepción — quien llama decide qué hacer.
     */
    public function send(Customer $customer, string $code): bool;
}
