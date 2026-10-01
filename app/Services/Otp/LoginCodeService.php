<?php

namespace Masso\Services\Otp;

use Illuminate\Support\Facades\Hash;
use Masso\Customer;
use Masso\CustomerLoginCode;

/**
 * Genera y valida los códigos de acceso (OTP) de los clientes, por email o
 * SMS. El límite de reenvío (1 código nuevo cada X segundos) es lo que
 * protege del riesgo de costo de SMS en eventos grandes (hasta 800
 * personas) — se revisa acá, a nivel de aplicación, no en la BD.
 */
class LoginCodeService
{
    private const EXPIRES_IN_MINUTES = 10;
    private const RESEND_THROTTLE_SECONDS = 60;

    public function __construct(
        private MailOtpChannel $mailChannel,
        private TwilioSmsChannel $smsChannel
    ) {
    }

    public function canSendSms(Customer $customer): bool
    {
        return !empty($customer->phone);
    }

    public function canResend(Customer $customer): bool
    {
        $lastCode = $customer->loginCodes()->latest('created_at')->first();

        if (!$lastCode) {
            return true;
        }

        return $lastCode->created_at->diffInSeconds(now()) >= self::RESEND_THROTTLE_SECONDS;
    }

    public function sendCode(Customer $customer, string $channel = CustomerLoginCode::CHANNEL_EMAIL): bool
    {
        if (!$this->canResend($customer)) {
            return false;
        }

        if ($channel === CustomerLoginCode::CHANNEL_SMS && !$this->canSendSms($customer)) {
            return false;
        }

        $code = (string) random_int(100000, 999999);

        $loginCode = $customer->loginCodes()->create([
            'code_hash' => Hash::make($code),
            'channel' => $channel,
            'attempts' => 0,
            'expires_at' => now()->addMinutes(self::EXPIRES_IN_MINUTES),
        ]);

        $sent = $channel === CustomerLoginCode::CHANNEL_SMS
            ? $this->smsChannel->send($customer, $code)
            : $this->mailChannel->send($customer, $code);

        if (!$sent) {
            $loginCode->delete();
            return false;
        }

        return true;
    }

    public function verifyCode(Customer $customer, string $code): bool
    {
        $loginCode = $customer->loginCodes()
            ->whereNull('consumed_at')
            ->latest('created_at')
            ->first();

        if (!$loginCode || $loginCode->isExpired() || !$loginCode->hasAttemptsLeft()) {
            return false;
        }

        $loginCode->increment('attempts');

        if (!Hash::check($code, $loginCode->code_hash)) {
            return false;
        }

        $loginCode->consumed_at = now();
        $loginCode->save();

        return true;
    }
}
