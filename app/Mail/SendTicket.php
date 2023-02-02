<?php

namespace Masso\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Masso\Payment;

class SendTicket extends Mailable
{
    use Queueable, SerializesModels;
    public $payment;
    public $path;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Payment $payment, $path)
    {
        $this->payment = $payment;
        $this->path = $path;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $file = $this->payment->dte;
        $num = explode('-', $this->payment->dte)[1];
        return $this->subject('Boleta electrónica N° '.$num.' | Masso Eventos')->view('emails.ticket')->attach($this->path,['as' => $file]);
    }
}
