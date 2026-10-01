<?php

namespace Masso\Services\Otp;

use Illuminate\Support\Facades\Log;
use Masso\Customer;
use Twilio\Rest\Client;

class TwilioSmsChannel implements OtpChannel
{
    public function send(Customer $customer, string $code): bool
    {
        if (empty($customer->phone)) {
            return false;
        }

        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        if (empty($sid) || empty($token) || empty($from)) {
            Log::error('Twilio no está configurado (faltan TWILIO_SID/TWILIO_TOKEN/TWILIO_FROM).');
            return false;
        }

        try {
            (new Client($sid, $token))->messages->create($customer->phone, [
                'from' => $from,
                'body' => "Tu código de acceso Massó Eventos es: {$code}",
            ]);
        } catch (\Exception $e) {
            Log::error('Error enviando código OTP por SMS (Twilio) a Customer ID ' . $customer->id, [
                'message' => $e->getMessage(),
            ]);
            return false;
        }

        return true;
    }
}
