<?php
namespace Masso\Http\Controllers\Guest;
use Masso\EventTicket;
use Masso\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Masso\Http\Requests\EnrollRequest;
use Masso\PaymentDetail;
use Masso\WebPay\WebPayTransaction;
use Masso\Mail\OrderPayment;
use Masso\Behaviors\FileBehavior;
use Masso\Client;
use Masso\Payment;
use Masso\Transaction;
use Masso\EventExpired;
use Masso\Event;
use Masso\EventIntent;
use Masso\EventFile;
use Masso\TeamMember;
use Masso\Log;

class PublicController extends Controller
{

    public function index()
    {

        $events = Event::where('status', 1)
            ->where('isUC', 0)
            ->orderBy('date_init')
            ->get();
        $eventsUC = Event::where('status', 1)
            ->where('isUC', 1)
            ->orderBy('date_init')
            ->get();
        return view('guest.index', compact('events', 'eventsUC'));
    }


    public function event( $slug )
    {
        $lang = isset($_GET['english']) ? 'eng' : 'esp';
        $event = Event::where('slug', $slug)->where('status', 1)->first();

        if( empty($event) )
            abort(404);

        $debug = false;

        $title = $event->name;
        $bodyClass = 'event-page';
        $location_to_map = str_replace(' ', '%20', $event->location);

        return view('guest.event', compact('title','event', 'bodyClass', 'lang', 'location_to_map'));
    }


    public function register( $slug )
    {
        $lang = isset($_GET['english']) ? 'eng' : 'esp';
        $event = Event::where('slug', $slug)->where('status', 1)->first();

        if( empty($event) )
            abort(404);

        if( !$event->hasTicketsAvailables() )
            return redirect()->route('public.event', $slug);

        $title = 'Registro '.$event->name;
        $bodyClass = 'register-page';
        return view('guest.register', compact('title','event', 'bodyClass', 'lang'));
    }


    public function process( EnrollRequest $request, $slug )
    {
        $data = $request->all();
        $managment = $data['payment'];
        $status = 'pending';

        $event = Event::where('slug', $slug)->where('status', 1)->firstOrFail();

        $ticket = new EventTicket();
        $tickets = $ticket->getTicketToBuy($event->id, $data['ticket']);

        // validate tickets
        if (!$tickets->available) {
            \Session::flash('error_alert', 'Ocurrió un error al procesar la reserva de tickets, intentalo nuevamente');
        }

        // Validate files
        foreach( $data as $key => $_data ):
            if( $request->hasFile($key) ):
                $original_name = explode('.', $data[$key]->getClientOriginalName());
                $extension = end($original_name);
                if (!in_array($extension, ['png', 'jpg', 'pdf'])) {
                    \Session::flash('error_alert', 'Formato de archivo no permitido');
                    return redirect()->route('public.register', ['id'=>$slug])->withInput();
                }

            endif;
        endforeach;

        foreach( $data as $key => $_data ):
            if( $request->hasFile($key) ):
                $data[$key] = FileBehavior::upload( $key, 'files/events/', $request );
            endif;
        endforeach;

        if($tickets->amount === 0) {
            $status = 'pagado';
        }

        $dataPayment = array_merge(
            $data,
            (array) $tickets,
            array('event_id' => $event->id)
        );

        $payment = [
            'name'      => $data['name'],
            'lastname'  => $data['lastname'],
            'email'     => $data['email'],
            'description' => $event->name,
            'amount'    => $tickets->amount,
            'status'    => $status,
            'dte'       => '',
            'document'  => '',
            'managment' => $data['payment'],
            'type'      => 'inscription',
            'data'      => serialize($dataPayment),
            'notified'  => 0,
            'event_id'  => $event->id,
            'has_inscription' => 0
        ];

        if(!$payment = Payment::create($payment)) {
            \Session::flash('error_alert', 'Ocurrió un error el procesar el pago, intentalo nuevamente');
            return redirect()->route('public.payment')->withInput();
        }

        //save ticket relations
        $paymentDetail = new PaymentDetail();
        $paymentDetail->addDetail($payment,'EventTicket', $tickets->ids);

        // view free ticket
        if( $managment == 'free' ):
            $payment->updateTicketStock();
            session(['payment'=>$payment, 'events' => $payment->getEvent()]);
            return redirect()->route('cart.webpayexito');
        endif;

        // view data transfer
        if( $managment == 'transfer' ):
            session(['payment'=>$payment, 'events' => $payment->getEvent()]);
            return redirect()->route('cart.webpayexito');
        endif;

        // init process webpay
        $transaction = new WebPayTransaction;
        $transaction = $transaction->initTransaction(
            $payment->amount,
            $payment->id,
            route('cart.validate'),
            route('cart.verify')
        );

        if( get_class($transaction) != 'Transbank\Webpay\WebpayPlus\Responses\TransactionCreateResponse' ):
            \Session::flash('error_alert', 'Ocurrió un error el procesar el pago, intentalo nuevamente');
            return redirect()->route('public.payment')->withInput();
        endif;

        Transaction::create(['response_code' => 9, 'payment_id'=>$payment->id, 'amount'=>$payment->amount, 'token' => $transaction->token ]);

        return view('guest.webpay', ['url'=>$transaction->url, 'token'=>$transaction->token]);

    }


    public function about(){
    	$title = 'Quiénes Somos';
        $members = TeamMember::all();
        return view('guest.about', compact('title','members'));
    }


    public function previously(){
    	$title = 'Eventos Anteriores';
        $events = EventExpired::orderBy('date_finish', 'desc')->get();
        return view('guest.previously', compact('title', 'events'));
    }


    public function contact(){
    	$title = 'Contacto';
        return view('guest.contact', compact('title'));
    }


    public function certificates( Request $request ){

    	if( $request->isMethod('post') ):
    		\Session::flash('error_alert', 'No se encontraron certificados asociados al documento: '.$request->get('run'));
    	endif;

    	$title = 'Certificados';
        return view('guest.certificates', compact('title'));
    }


    public function payment(){
        $title = 'Pagos';
        $events = EventExpired::all();
        $tickets = EventTicket::select(
            'events_tickets.id',
            \DB::raw("CONCAT(events.name, ' | ', events_tickets.name) as name")
        )
        ->join('events', 'events_tickets.event_id', '=', 'events.id')
        ->where('events.status', 1)
        ->where('events_tickets.deleted_at', NULL)
        ->get();

        $select_options = [];
        foreach($tickets as $event) {
            $select_options[$event->id] = $event->name;
        }
        $lang = isset($_GET['english']) ? 'eng' : 'esp';

        return view('guest.payment', compact('title', 'events', 'select_options', 'lang'));
    }


    public function processPay( Request $request ){
        $data = $request->all();
        $event_id = $data['description'];
        $payment_type = $data['payment'];

        try {
            $event_ticket = EventTicket::where('id', $event_id)->firstOrFail();
            $event = Event::where('id', $event_ticket->event_id)->firstOrFail();
        } catch (\Exception $e) {
            $event = null;
        }

        if (!$event) {
            \Session::flash('error_alert', 'Ocurrió un error el procesar el pago, intentalo nuevamente');
            return redirect()->route('public.payment')->withInput();
        }

        $ticket = new EventTicket();
        $tickets = $ticket->getTicketToBuy($event->id, [$event_id]);

        // validate tickets
        if (!$tickets->available) {
            \Session::flash('error_alert', 'Ocurrió un error al procesar la reserva de tickets, intentalo nuevamente');
        }
        $amount = intval(str_replace(['.',',','$','-','e'],['','','','',''],$data['amount']));

        $data['description'] = $event->name;
        $data['event_id'] = $event_ticket->event_id;
        $data['amount'] = $amount;
        $data['status'] = 'pending';
        $data['type'] = 'custom';
        $data['managment'] = $payment_type == 'webpay' ? $payment_type : 'transfer';
        $data['has_inscription'] = 0;
        $data['ticket_id'] = $event_ticket->id;

        $data['data'] = serialize($data);

        if( $data['amount'] < 10 || !$payment = Payment::create($data) ):
            \Session::flash('error_alert', 'Ocurrió un error el procesar el pago, intentalo nuevamente');
            return redirect()->route('public.payment')->withInput();
        endif;

        $payment_detail = new PaymentDetail();
        $payment_detail->type = 1;
        $payment_detail->payment_id = $payment->id;
        $payment_detail->ticket_id = $event_ticket->id;
        $payment_detail->price = $amount;
        $payment_detail->save();


        if ($payment_type === "webpay") {
            $transaction = new WebPayTransaction;
            $transaction = $transaction->initTransaction(
                $data['amount'],
                $payment->id,
                route('cart.validate'),
                route('cart.verify')
            );

            if( get_class($transaction) != 'Transbank\Webpay\WebpayPlus\Responses\TransactionCreateResponse' ):
                \Session::flash('error_alert', 'Ocurrió un error el procesar el pago, intentalo nuevamente');
                return redirect()->route('public.payment')->withInput();
            endif;

            Transaction::create([
                'response_code' => 9,
                'payment_id'=>$payment->id,
                'amount'=>$payment->amount,
                'token' => $transaction->token
            ]);
    
            return view('guest.webpay', ['url'=>$transaction->url, 'token'=>$transaction->token]);
        } else {
            return redirect()->route('cart.webpayexito')->with(['payment' => $payment]);
        }

    }


    public function download( $download ){
        $file = EventFile::where('uuid', $download)->first();
        if( empty($file) )
            abort(404);

        return response()->download(public_path().$file->file, $file->name.'.'.$file->extension);
    }


}
