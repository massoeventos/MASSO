<?php

namespace Masso\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecoveryMailPassword extends Mailable
{
    use Queueable, SerializesModels;
    public $recovery;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($recovery)
    {
        $this->recovery = $recovery;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = 'Recuperación de Contraseña';
        return $this->subject($subject)->view('emails.recover-password');
    }
}
