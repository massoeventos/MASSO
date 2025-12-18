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
use Masso\DeviceProfile;
use Masso\City;
use Masso\Region;
use Masso\Coupon;
use Masso\Log;

class PublicController extends Controller
{

    /**
     * Obtener el último pago realizado desde este dispositivo (si existe)
     */
    protected function getLastPaymentFromDevice(Request $request, $preferInscription = false)
    {
        $deviceToken = $request->cookie('device_token');
        if (empty($deviceToken)) {
            return null;
        }

        try {
            $profile = DeviceProfile::where('device_token', $deviceToken)->first();
            if (empty($profile)) {
                return null;
            }

            $paymentId = null;

            if ($preferInscription && !empty($profile->last_inscription_payment_id)) {
                $paymentId = $profile->last_inscription_payment_id;
            } elseif (!empty($profile->last_payment_id)) {
                $paymentId = $profile->last_payment_id;
            }

            if (empty($paymentId)) {
                return null;
            }

            $payment = Payment::find($paymentId);

            // If we prefer an inscription but the pointed payment isn't one anymore, fallback to last_payment_id.
            if ($preferInscription && !empty($payment) && isset($payment->type) && $payment->type !== 'inscription') {
                if (!empty($profile->last_payment_id) && (int) $profile->last_payment_id !== (int) $paymentId) {
                    $fallback = Payment::find($profile->last_payment_id);
                    if (!empty($fallback)) {
                        return $fallback;
                    }
                }
            }

            return $payment;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Guardar el ID del último pago realizado en el perfil del dispositivo (si existe)
     */
    protected function persistLastPaymentForDevice(Request $request, $paymentId, $isInscription = false)
    {
        if (empty($paymentId)) {
            return;
        }

        $deviceToken = $request->cookie('device_token');
        if (empty($deviceToken)) {
            return;
        }

        try {
            $updates = ['last_payment_id' => $paymentId];

            if ($isInscription) {
                $updates['last_inscription_payment_id'] = $paymentId;
            }

            DeviceProfile::updateOrCreate(
                ['device_token' => $deviceToken],
                $updates
            );
        } catch (\Exception $e) {
            // Non-critical
        }
    }

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
    public function register(Request $request, $slug)
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

        $autofill = [];
        $lastPayment = $this->getLastPaymentFromDevice($request, true);
        

        if (!empty($lastPayment)) {
            $autofill = [
                'name' => $lastPayment->name,
                'lastname' => $lastPayment->lastname,
                'email' => $lastPayment->email,
                'nationality_country_id' => $lastPayment->nationality_country_id,
                'rut' => $lastPayment->rut,
                'billing_method' => $lastPayment->billing_method,
                'invoice_data' => $lastPayment->invoice_data,
            ];

            // Passport is stored inside serialized payment->data
            $processed = $lastPayment->processData();
            if (is_array($processed) && isset($processed['passport'])) {
                $autofill['passport'] = $processed['passport'];
            }

            // Location autofill
            // If we have a city_id (Chile flow), derive region and country from it.
            if (!empty($lastPayment->city_id)) {
                $autofill['city_id'] = $lastPayment->city_id;

                $city = City::find($lastPayment->city_id);
                if (!empty($city) && !empty($city->region_id)) {
                    $autofill['region_id'] = $city->region_id;

                    $region = Region::find($city->region_id);
                    if (!empty($region) && !empty($region->country_id)) {
                        $autofill['country_id'] = $region->country_id;
                    }
                }
            } else {
                // If we don't have a city, only then use stored country/custom city
                if (!empty($lastPayment->country_id)) {
                    $autofill['country_id'] = $lastPayment->country_id;
                }
                if (!empty($lastPayment->custom_city)) {
                    $autofill['custom_city'] = $lastPayment->custom_city;
                }
            }
        }

        return view('guest.register', compact('title','event', 'bodyClass', 'lang', 'countries', 'chile', 'autofill'));
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

        if ($managment === 'transfer' && !$event->allow_bank_transfer) {
            \Session::flash('error_alert', 'El pago por transferencia no está disponible para este evento.');
            return redirect()->route('public.register', ['id' => $slug])->withInput();
        }

        $ticket = new EventTicket();
        $tickets = $ticket->getTicketToBuy($event->id, $data['ticket']);

        // validate tickets
        if (!$tickets->available) {
            \Session::flash('error_alert', 'Ocurrió un error al procesar la reserva de tickets, intentalo nuevamente');
            return redirect()->route('public.register', ['id' => $slug])->withInput();
        }

        // Validate files
        foreach ($data as $key => $_data) {
            // ticket_document is an array of files keyed by ticket id; validated/uploaded separately below
            if ($key === 'ticket_document') {
                continue;
            }
            if ($request->hasFile($key)) {
                $file = $request->file($key);

                // In case a field contains multiple files, validate each
                $files = is_array($file) ? $file : [$file];
                foreach ($files as $singleFile) {
                    if (empty($singleFile)) {
                        continue;
                    }

                    $original_name = explode('.', $singleFile->getClientOriginalName());
                    $extension = strtolower(end($original_name));
                    if (!in_array($extension, ['png', 'jpg', 'jpeg', 'pdf'])) {
                        \Session::flash('error_alert', 'Formato de archivo no permitido');
                        return redirect()->route('public.register', ['id' => $slug])->withInput();
                    }
                }
            }
        }

        foreach ($data as $key => $_data) {
            if ($key === 'ticket_document') {
                continue;
            }
            if ($request->hasFile($key)) {
                $data[$key] = FileBehavior::upload($key, 'files/events/', $request);
            }
        }

        // Upload ticket-specific required documents
        $ticketDocumentPaths = [];
        try {
            $selectedTickets = isset($data['ticket']) ? $data['ticket'] : [];
            if (!is_array($selectedTickets)) {
                $selectedTickets = [$selectedTickets];
            }

            $requiredTicketIds = EventTicket::where('event_id', $event->id)
                ->whereIn('id', $selectedTickets)
                ->where('requires_document', 1)
                ->pluck('id')
                ->toArray();

            foreach ($requiredTicketIds as $ticketId) {
                $key = 'ticket_document.' . $ticketId;
                if (!$request->hasFile($key)) {
                    \Session::flash('error_alert', 'Debe adjuntar el documento requerido para el ticket seleccionado.');
                    return redirect()->route('public.register', ['id' => $slug])->withInput();
                }

                $file = $request->file('ticket_document')[$ticketId];
                if (empty($file)) {
                    continue;
                }

                $originalName = $file->getClientOriginalName();
                $originalParts = explode('.', $originalName);
                $extension = strtolower(end($originalParts));

                if (!in_array($extension, ['png', 'jpg', 'jpeg', 'pdf'])) {
                    \Session::flash('error_alert', 'Formato de archivo no permitido');
                    return redirect()->route('public.register', ['id' => $slug])->withInput();
                }

                $dir = public_path('files/events/ticket_documents');
                if (!file_exists($dir)) {
                    @mkdir($dir, 0775, true);
                }

                $safeName = date('YmdHis') . '-' . $ticketId . '-' . preg_replace('/[^a-zA-Z0-9._-]/', '_', strtolower($originalName));
                $file->move($dir, $safeName);

                $ticketDocumentPaths[$ticketId] = '/files/events/ticket_documents/' . $safeName;
            }
        } catch (\Exception $e) {
            // Non-critical
        }

        // Never serialize UploadedFile instances
        if (isset($data['ticket_document'])) {
            unset($data['ticket_document']);
        }

        $coupon = null;
        $discountPercentage = null;
        $discountAmount = null;


        // Validar y aplicar cupón
        if (!empty($data['coupon_code'])) {
            $coupon = Coupon::where([
                'code' => $data['coupon_code'],
                'event_id' => $event->id
            ])->first();

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
            Arr::except($data, ['coupon_code', 'ticket_document']),
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

        $this->persistLastPaymentForDevice($request, $payment->id, true);

        // save ticket relations
        $paymentDetail = new PaymentDetail();
        $paymentDetail->addDetails($payment, 'EventTicket', $tickets->ids);

        // Persist required documents per ticket in payments_detail
        if (!empty($ticketDocumentPaths)) {
            foreach ($ticketDocumentPaths as $ticketId => $path) {
                try {
                    PaymentDetail::where('payment_id', $payment->id)
                        ->where('ticket_id', $ticketId)
                        ->update(['required_document_file' => $path]);
                } catch (\Exception $e) {
                    // Non-critical
                }
            }
        }

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


    /**
     * Listado de eventos anteriores (acceso público) con soporte para scroll infinito (JSON).
     */
    public function previously(Request $request){
	    $title = 'Eventos Anteriores';
        $events = Event::expired()
            ->orderBy('date_finish', 'desc')
            ->paginate(9);

        if ($request->ajax() || $request->wantsJson()) {
            $html = view('guest.previous._items', ['events' => $events])->render();
            return response()->json([
                'html' => $html,
                'next_page_url' => $events->nextPageUrl(),
            ]);
        }

        return view('guest.previous.index', compact('title', 'events'));
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


    /**
     * Mostrar formulario de pagos
     */
    public function payment(Request $request)
    {
        $title = 'Pagos';
        $events = Event::where('status', 1)->get(); // Solo eventos activos
        $lang = isset($_GET['english']) ? 'eng' : 'esp';

        $autofill = [];
        $lastPayment = $this->getLastPaymentFromDevice($request);
        if (!empty($lastPayment)) {
            $autofill = [
                'name' => $lastPayment->name,
                'lastname' => $lastPayment->lastname,
                'email' => $lastPayment->email,
            ];
        }

        return view('guest.payment', compact('title', 'events', 'lang', 'autofill'));
    }

    public function getTicketsByEvent($eventId)
    {
        $tickets = EventTicket::select('id', 'name')
            ->where('event_id', $eventId)
            ->get();

        return response()->json($tickets);
    }


    /**
     * Post del formulario de pagos grupales
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
            'po_input_mode' => 'required|in:number,file',
            'purchase_order_number' => 'required_if:po_input_mode,number|max:255',
            'purchase_order_file' => 'required_if:po_input_mode,file|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        $ticket_id = $data['ticket_id'];
        $payment_type = $data['payment'];

        // Purchase order / associated document
        $poInputMode = isset($data['po_input_mode']) ? $data['po_input_mode'] : null;
        $data['purchase_order_type'] = $poInputMode;

        if ($poInputMode === 'file') {
            $data['purchase_order_number'] = null;

            if ($request->hasFile('purchase_order_file')) {
                $dir = public_path('files/purchase_orders');
                if (!file_exists($dir)) {
                    @mkdir($dir, 0775, true);
                }

                $file = $request->file('purchase_order_file');
                $originalName = $file->getClientOriginalName();
                $safeName = date('YmdHis') . '-' . preg_replace('/[^a-zA-Z0-9._-]/', '_', strtolower($originalName));

                $file->move($dir, $safeName);
                $data['purchase_order_file'] = '/files/purchase_orders/' . $safeName;
            }
        } else {
            $data['purchase_order_file'] = null;
        }

        unset($data['po_input_mode']);

        try {
            $event_ticket = EventTicket::where('id', $ticket_id)->firstOrFail();
            $event = Event::where('id', $event_ticket->event_id)->firstOrFail();
        } catch (\Exception $e) {
            \Session::flash('error_alert', 'Ocurrió un error el procesar el pago, intentalo nuevamente');
            return redirect()->route('public.payment')->withInput();
        }

        if ($payment_type === 'transfer' && !$event->allow_bank_transfer) {
            \Session::flash('error_alert', 'El pago por transferencia no está disponible para este evento.');
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

        $this->persistLastPaymentForDevice($request, $payment->id, false);

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
