<?php

namespace Masso\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerLoginCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function build()
    {
        return $this->subject('Tu código de acceso — Massó Eventos')
            ->view('emails.customer-login-code');
    }
}
