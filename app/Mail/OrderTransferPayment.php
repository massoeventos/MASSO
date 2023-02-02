<?php

namespace Masso\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Masso\Payment;

class OrderTransferPayment extends Mailable
{
    use Queueable, SerializesModels;
    public $payment;
    public $subject;
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject = $this->payment->description;
        return $this->subject('Nuevo Registro Orden N° '.$this->payment->id.' | Masso Eventos')->view('emails.order-pending');
    }
}
