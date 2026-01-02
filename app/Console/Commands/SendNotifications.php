<?php

namespace Masso\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Masso\Mail\SendTicket;
use Masso\Payment;
use Masso\Event;
use Masso\EventEnroll;
use Masso\EventTicket;

class SendNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'masso:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Notifications';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        
        // \Log::info('Ejecutando masso:send');

        Payment::notifyPayments();

        $payments = Payment::where('notified', 1)->where('dte', '!=', '')->get();

        if( !empty($payments) ):

            foreach( $payments as $payment ):

                try {

                    if( \Storage::exists( $payment->document ) ):

                        $path = storage_path('app/'.$payment->document);


                        if( filter_var($payment->email, FILTER_VALIDATE_EMAIL) )
                            \Mail::to($payment->email)->send(new SendTicket($payment, $path));

                        if(App::environment() === 'production')
                            \Mail::to('pagos@massoeventos.cl')->send(new SendTicket($payment, $path));

                        $payment->notified = 2;
                        $payment->save();

                    endif;


                } catch (\Exception $e) {
                    \Log::error('Excepción al notificar pago  ' . $payment->id . ': ' . $e->getMessage());
                    \Log::error($e->getTraceAsString());
                    continue;
                }


            endforeach;

        endif;

        $payments = Payment::where('status', 'pagado')
            ->whereIn('type', ['inscription', 'custom'])
            ->where('has_inscription', 0)
            ->where('event_id', '!=', 0)
            ->get();

        if (!empty($payments)):
            foreach ($payments as $payment):
                try {
                    $data = unserialize($payment->data);

                    $event = Event::find($data['event_id']);

                    if (!$event) {
                        throw new \Exception("Event not found for ID {$data['event_id']} (Payment ID {$payment->id})");
                    }

                    $passport = isset($data['passport']) ? $data['passport'] : '';
                    $paymentData = serialize($payment->data); //TODO: validar si puede removerse el serializar nuevamente
            
                    $details = $payment->details;
                    if (count($details) === 0) {
                        throw new \Exception("No payment detail found for Payment ID {$payment->id}");
                    }
            
                    foreach ($details as $detail) {
                        $enroll = new EventEnroll();
                        $enroll->event_id    = $event->id;
                        $enroll->city_id     = $payment->city_id;
                        $enroll->country_id  = $payment->country_id;
                        $enroll->custom_city = $payment->custom_city;
                        $enroll->name        = $payment->name;
                        $enroll->lastname    = $payment->lastname;
                        $enroll->rut         = $payment->rut;
                        $enroll->passport    = $passport;
                        $enroll->email       = $data['email'];
                        $enroll->phone       = '';
                        $enroll->profession  = '';
                        $enroll->speciality  = '';
                        $enroll->workplace   = '';
                        $enroll->city        = '';
                        $enroll->country     = '';
                        $enroll->ticket_id   = $detail->ticket_id;
                        $enroll->created_at  = Carbon::now();
                        $enroll->updated_at  = Carbon::now();
                        $enroll->deleted_at  = null;
                        $enroll->data        = $paymentData;
                        $enroll->payment_id  = $payment->id;
                        $enroll->nationality_country_id  = $payment->nationality_country_id;

                        $enroll->save();
                    }

                    $payment->has_inscription = 1;
                    $payment->save();

                } catch (\Exception $e) {
                    Log::error("Error processing inscription for Payment ID {$payment->id}: " . $e->getMessage(), [
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Continúa con el siguiente payment
                }
            endforeach;
        endif;

        return 0;
    }
}
