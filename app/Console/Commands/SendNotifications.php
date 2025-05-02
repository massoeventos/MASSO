<?php

namespace Masso\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Masso\Mail\SendTicket;
use Masso\Payment;
use Masso\Event;
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

        if( !empty($payments) ):
            foreach( $payments as $payment ):
                $data = unserialize($payment->data);

                $payment->data = serialize( str_replace("'", "''", $payment->data) );
                $event = Event::where('id', $data['event_id'])->first();

                $passport = array_key_exists('passport', $data) ? $data["passport"] : "";
                $payment_data = addslashes($payment->data);
                $payment_name = addslashes($data['name']);
                $payment_lastname = addslashes($data['lastname']);
                $query = "INSERT INTO events_enroll(event_id, name, lastname, passport,  email, phone, profession, speciality, workplace, city, country, ticket_id, created_at, updated_at, deleted_at, data, payment_id) 
                            SELECT 
                                '{$event->id}', '{$payment_name}', '{$payment_lastname}',
                                '{$passport}', '{$data['email']}', '', '', '', '', '', '',
                                p.ticket_id, now(), now(), null,  '{$payment_data}', '{$payment->id}'
                            FROM payments_detail p WHERE p.payment_id={$payment->id}";
                DB::insert($query);
                $payment->has_inscription = 1;
                $payment->save();

            endforeach;
        endif;
    }
}
