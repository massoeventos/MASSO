<?php

namespace Masso\Services\Otp;

use Illuminate\Support\Facades\Mail;
use Masso\Customer;
use Masso\Mail\CustomerLoginCodeMail;

class MailOtpChannel implements OtpChannel
{
    public function send(Customer $customer, string $code): bool
    {
        if (!filter_var($customer->email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        Mail::to($customer->email)->send(new CustomerLoginCodeMail($code));

        return true;
    }
}
