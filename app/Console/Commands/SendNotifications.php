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
use Masso\Services\LegacySerializedData;
use Masso\Services\EnrollmentDataResolver;

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
                    $data = LegacySerializedData::safeUnserialize($payment->data);

                    // event_id es columna real en payments (ya usada en el
                    // WHERE de arriba) -- desde que payments.data dejo de
                    // escribirse (Bloque 3), leerlo del blob siempre daba []
                    // y tiraba "undefined array key", silenciado por el
                    // catch de este bucle, asi que nunca se creaba el
                    // EventEnroll para ningun pago nuevo.
                    $event = Event::find($payment->event_id);

                    if (!$event) {
                        throw new \Exception("Event not found for ID {$payment->event_id} (Payment ID {$payment->id})");
                    }

                    $passport = isset($data['passport']) ? $data['passport'] : '';
                    // $payment->data ya viene serializado una vez (PublicController lo
                    // guarda con serialize()): antes este código volvía a serializarlo
                    // aquí, produciendo el bug de doble serialización en events_enroll.data.
                    $paymentData = $payment->data;

                    $details = $payment->details;
                    if (count($details) === 0) {
                        throw new \Exception("No payment detail found for Payment ID {$payment->id}");
                    }
            
                    foreach ($details as $detail) {
                        // name/lastname/rut/email/city_id/country_id/custom_city/
                        // nationality_country_id ya NO se copian acá: al setear
                        // payment_detail_id, el modelo EventEnroll las resuelve
                        // a través de paymentDetail->payment (ver EventEnroll.php).
                        $enroll = new EventEnroll();
                        $enroll->event_id          = $event->id;
                        $enroll->passport          = $passport;
                        $enroll->phone             = '';
                        $enroll->profession        = '';
                        $enroll->speciality        = '';
                        $enroll->workplace         = '';
                        $enroll->city              = '';
                        $enroll->country           = '';
                        $enroll->ticket_id         = $detail->ticket_id;
                        $enroll->created_at        = Carbon::now();
                        $enroll->updated_at        = Carbon::now();
                        $enroll->deleted_at        = null;
                        $enroll->data              = $paymentData;
                        $enroll->data_json         = EnrollmentDataResolver::extraFields($data);
                        $enroll->payment_id        = $payment->id;
                        $enroll->payment_detail_id = $detail->id;

                        $enroll->save();

                        $detail->status = \Masso\PaymentDetail::STATUS_CONFIRMED;
                        $detail->save();
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
