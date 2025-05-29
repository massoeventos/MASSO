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
use Masso\Country;
use Masso\Payment;
use Masso\Transaction;
use Masso\EventExpired;
use Masso\Event;
use Masso\EventIntent;
use Masso\EventFile;
use Masso\TeamMember;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Masso\Coupon;
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


    /**
     * Formulario para registrarse en un evento
     */
    public function register( $slug )
    {
        $lang = isset($_GET['english']) ? 'eng' : 'esp';
        $event = Event::where('slug', $slug)->where('status', 1)->first();
        if( empty($event) ){
            abort(404);
        }

        if( !$event->hasTicketsAvailables() ){
            return redirect()->route('public.event', $slug);
        }

        $title = 'Registro '.$event->name;
        $bodyClass = 'register-page';
        $countries = Country::orderBy('is_other')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function ($country) use ($lang) {
                return [$country->id => $country->getTranslatedName($lang)];
            });

        $chile = Country::where('name', Country::$CHILE_NAME)->firstOrFail();

        return view('guest.register', compact('title','event', 'bodyClass', 'lang', 'countries', 'chile'));
    }


    /**
     * Procesar POST de registrarse en evento
     */
    public function process(EnrollRequest $request, $slug)
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
            return redirect()->route('public.register', ['id' => $slug])->withInput();
        }

        // Validate files
        foreach ($data as $key => $_data) {
            if ($request->hasFile($key)) {
                $original_name = explode('.', $data[$key]->getClientOriginalName());
                $extension = end($original_name);
                if (!in_array($extension, ['png', 'jpg', 'pdf'])) {
                    \Session::flash('error_alert', 'Formato de archivo no permitido');
                    return redirect()->route('public.register', ['id' => $slug])->withInput();
                }
            }
        }

        foreach ($data as $key => $_data) {
            if ($request->hasFile($key)) {
                $data[$key] = FileBehavior::upload($key, 'files/events/', $request);
            }
        }

        $coupon = null;
        $discountPercentage = null;
        $discountAmount = null;


        // Validar y aplicar cupón
        if (!empty($data['coupon_code'])) {
            $coupon = Coupon::where('code', $data['coupon_code'])->first();

            if ($coupon) {
                $validation = $coupon->validateForTickets($tickets->ids);

                if (!$validation['valid']) {
                    \Session::flash('error_alert', $validation['message'] . ': ' . implode(', ', $validation['invalid_ticket_names']));
                    return redirect()->route('public.register', ['id' => $slug])->withInput();
                }

                $discountPercentage = $validation['discount_percentage'];
                $discountAmount = round($tickets->amount * ($discountPercentage / 100), 2);
                $tickets->amount -= $discountAmount;
            } else {
                \Session::flash('error_alert', 'Cupón inválido.');
                return redirect()->route('public.register', ['id' => $slug])->withInput();
            }
        }

        if ($tickets->amount === 0) {
            $status = 'pagado';
        }

        $dataPayment = array_merge(
            Arr::except($data, ['coupon_code']),
            (array) $tickets,
            ['event_id' => $event->id]
        );

        $payment = [
            'name' => $data['name'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'rut' => $data['rut'],
            'description' => $event->name,
            'amount' => $tickets->amount,
            'status' => $status,
            'dte' => '',
            'document' => '',
            'managment' => $data['payment'],
            'type' => 'inscription',
            'data' => serialize($dataPayment),
            'notified' => 0,
            'event_id' => $event->id,
            'has_inscription' => 0,
            'nationality_country_id' => $data['nationality_country_id'],
            'billing_method' => $data['billing_method'],
            'coupon_id' => $coupon ? $coupon->id : null,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount,
        ];

        if ($payment['billing_method'] == Payment::$BILLING_METHOD_INVOICE) {
            $payment['invoice_data'] = $data['invoice_data'];
        }

        if ($event->show_location_fields) {
            $chile = Country::where('name', Country::$CHILE_NAME)->firstOrFail();

            if ($data['country_id'] == $chile->id) {
                $payment['city_id'] = $data['city_id'];
            } else {
                $payment['country_id'] = $data['country_id'];
                $payment['custom_city'] = $data['custom_city'];
            }
        }

        if (!$payment = Payment::create($payment)) {
            \Session::flash('error_alert', 'Ocurrió un error el procesar el pago, intentalo nuevamente');
            return redirect()->route('public.register', ['id' => $slug])->withInput();
        }

        // save ticket relations
        $paymentDetail = new PaymentDetail();
        $paymentDetail->addDetail($payment, 'EventTicket', $tickets->ids);

        // view free ticket
        if ($managment == 'free') {
            $payment->updateTicketStock();
            session(['payment' => $payment, 'events' => $payment->getEvent()]);
            return redirect()->route('cart.webpayexito');
        }

        // view data transfer
        if ($managment == 'transfer') {
            session(['payment' => $payment, 'events' => $payment->getEvent()]);
            return redirect()->route('cart.webpayexito');
        }

        // init process webpay
        $transaction = new WebPayTransaction;
        $transaction = $transaction->initTransaction(
            $payment->amount,
            $payment->id,
            route('cart.validate'),
            route('cart.verify')
        );

        if (get_class($transaction) != 'Transbank\Webpay\WebpayPlus\Responses\TransactionCreateResponse') {
            \Session::flash('error_alert', 'Ocurrió un error el procesar el pago, intentalo nuevamente');
            return redirect()->route('public.register', ['id' => $slug])->withInput();
        }

        Transaction::create([
            'response_code' => 9,
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
            'token' => $transaction->token
        ]);

        return view('guest.webpay', ['url' => $transaction->url, 'token' => $transaction->token]);
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


    public function payment()
    {
        $title = 'Pagos';
        $events = Event::where('status', 1)->get(); // Solo eventos activos
        $lang = isset($_GET['english']) ? 'eng' : 'esp';

        return view('guest.payment', compact('title', 'events', 'lang'));
    }

    public function getTicketsByEvent($eventId)
    {
        $tickets = EventTicket::select('id', 'name')
            ->where('event_id', $eventId)
            ->get();

        return response()->json($tickets);
    }


    /**
     * Post del formulario de pagos
     */
    public function processPay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:100',
            'lastname'  => 'required|string|max:100',
            'email'     => 'required|email|max:255',
            'ticket_id' => 'required|exists:events_tickets,id',
            'payment'   => 'required|in:webpay,transfer',
            'amount'    => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        $ticket_id = $data['ticket_id'];
        $payment_type = $data['payment'];

        try {
            $event_ticket = EventTicket::where('id', $ticket_id)->firstOrFail();
            $event = Event::where('id', $event_ticket->event_id)->firstOrFail();
        } catch (\Exception $e) {
            \Session::flash('error_alert', 'Ocurrió un error el procesar el pago, intentalo nuevamente');
            return redirect()->route('public.payment')->withInput();
        }

        $ticket = new EventTicket();
        $tickets = $ticket->getTicketToBuy($event->id, [$ticket_id]);

        // validate tickets
        if (!$tickets->available) {
            \Session::flash('error_alert', 'Ocurrió un error al procesar la reserva de tickets, intentalo nuevamente');
            return redirect()->route('public.payment')->withInput();
        }

        $amount = intval(str_replace(['.',',','$','-','e'], ['','','','',''], $data['amount']));

        $data['description'] = $event->name; // guardar el nombre del evento como descripción
        $data['event_id'] = $event_ticket->event_id;
        $data['amount'] = $amount;
        $data['status'] = 'pending';
        $data['type'] = 'custom';
        $data['managment'] = $payment_type == 'webpay' ? $payment_type : 'transfer';
        $data['has_inscription'] = 0;
        $data['ticket_id'] = $ticket_id;

        if($data['user_observation'] === ''){
           $data['user_observation'] = null;
        }

        $data['data'] = serialize($data);

        if ($data['amount'] < 10 || !$payment = Payment::create($data)) {
            \Session::flash('error_alert', 'Ocurrió un error el procesar el pago, intentalo nuevamente');
            return redirect()->route('public.payment')->withInput();
        }

        $payment_detail = new PaymentDetail();
        $payment_detail->type = 1;
        $payment_detail->payment_id = $payment->id;
        $payment_detail->ticket_id = $ticket_id;
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

            if (get_class($transaction) != 'Transbank\Webpay\WebpayPlus\Responses\TransactionCreateResponse') {
                \Session::flash('error_alert', 'Ocurrió un error el procesar el pago, intentalo nuevamente');
                return redirect()->route('public.payment')->withInput();
            }

            Transaction::create([
                'response_code' => 9,
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'token' => $transaction->token
            ]);

            return view('guest.webpay', ['url' => $transaction->url, 'token' => $transaction->token]);
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
