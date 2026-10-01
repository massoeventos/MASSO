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
use Illuminate\Support\Facades\Mail;
use Masso\DeviceProfile;
use Masso\Coupon;
use Masso\Log;
use Masso\Mail\OrderTransferPayment;
use Masso\Customer;
use Masso\CustomerLoginCode;
use Masso\Services\Otp\LoginCodeService;
use Illuminate\Support\Facades\Auth;

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

    /**
     * Copia las respuestas a los campos dinámicos del evento (events_inputs)
     * a event_input_values, además de dejarlas (como siempre) dentro del
     * blob serializado/JSON de payments.data. Escritura dual mientras se
     * valida en staging; nunca debe romper el flujo de compra.
     */
    private function storeEventInputValues(Event $event, Payment $payment, array $dataPayment)
    {
        try {
            $rows = [];
            $now = now();

            foreach ($event->inputs as $input) {
                $key = str_replace(' ', '_', $input->name);

                if (!array_key_exists($key, $dataPayment)) {
                    continue;
                }

                $value = $dataPayment[$key];
                if (is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                }

                $rows[] = [
                    'payment_id' => $payment->id,
                    'event_input_id' => $input->id,
                    'value' => $value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($rows)) {
                \DB::table('event_input_values')->insertOrIgnore($rows);
            }
        } catch (\Throwable $e) {
            \Log::error('No se pudieron guardar event_input_values para el pago ' . $payment->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Encuentra o crea (de forma pasiva, sin verificar) el Customer para la
     * compra que se está procesando, y completa los campos de perfil que
     * todavía tuviera vacíos con los datos de esta compra — nunca sobreescribe
     * un dato ya guardado. Si la sesión tiene una identificación verificada
     * (ver verifyCustomerCode) para el mismo email, se usa ese Customer
     * directamente en vez de buscarlo de nuevo.
     */
    private function linkCustomerToPurchase(array $data): Customer
    {
        $email = mb_strtolower(trim($data['email']));

        $customer = null;
        $authCustomer = Auth::guard('customer')->user();
        if ($authCustomer && mb_strtolower(trim($authCustomer->email)) === $email) {
            $customer = $authCustomer;
        }

        if (empty($customer)) {
            $customer = Customer::firstOrCreate(['email' => $email]);
        }

        // Ítem 3.3c: estos son los datos "default" del comprador -- se
        // completan una sola vez (nunca se sobreescriben) y de ahí en
        // adelante Payment los resuelve vía el Customer, sin repetirlos.
        $profileFields = [
            'name' => $data['name'] ?? null,
            'lastname' => $data['lastname'] ?? null,
            'rut' => $data['rut'] ?? null,
            'passport' => $data['passport'] ?? null,
            'gender' => $data['gender'] ?? null,
            'nationality_country_id' => $data['nationality_country_id'] ?? null,
        ];

        $dirty = false;
        foreach ($profileFields as $field => $value) {
            if (empty($customer->$field) && !empty($value)) {
                $customer->$field = $value;
                $dirty = true;
            }
        }

        // La ubicación se completa como grupo, nunca campo por campo: si
        // se hiciera igual que arriba, una compra posterior con un país
        // de residencia distinto podía llenar solo `city_id` (porque
        // antes estaba vacío) dejando `country_id`/`custom_city` viejos
        // de una compra anterior -- la misma inconsistencia que causaba
        // el bug ya corregido en updateProfile()/toggleChileMode().
        $hasNoSavedLocation = empty($customer->city_id) && empty($customer->country_id);
        if ($hasNoSavedLocation) {
            if (!empty($data['city_id'] ?? null)) {
                $customer->city_id = $data['city_id'];
                $dirty = true;
            } elseif (!empty($data['custom_city'] ?? null)) {
                $customer->country_id = $data['country_id'] ?? null;
                $customer->custom_city = $data['custom_city'];
                $dirty = true;
            }
        }

        if ($dirty) {
            $customer->save();
        }

        return $customer;
    }


    /**
     * Primer paso de identificación en el checkout: solo con el email, sin
     * pedir nunca contraseña. Si el email es nuevo (o no tiene datos guardados
     * que proteger) se deja pasar directo. Si ya tiene datos, se envía un
     * código por correo y el front pide confirmarlo antes de autocompletar.
     */
    public function identifyCustomer(Request $request, $slug)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'invalid'], 422);
        }

        $email = mb_strtolower(trim($request->input('email')));
        $customer = Customer::where('email', $email)->first();

        $hasSavedData = $customer && (!empty($customer->name) || !empty($customer->lastname) || !empty($customer->rut));

        if (!$hasSavedData) {
            return response()->json(['status' => 'new']);
        }

        app(LoginCodeService::class)->sendCode($customer, CustomerLoginCode::CHANNEL_EMAIL);

        return response()->json([
            'status' => 'existing',
            'can_sms' => app(LoginCodeService::class)->canSendSms($customer),
        ]);
    }


    /**
     * Verifica el código OTP de un cliente existente. Solo si el código es
     * correcto se revelan sus datos guardados (nombre/apellido/rut) para
     * autocompletar el formulario.
     */
    public function verifyCustomerCode(Request $request, $slug)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['verified' => false, 'message' => 'Datos inválidos.'], 422);
        }

        $email = mb_strtolower(trim($request->input('email')));
        $customer = Customer::where('email', $email)->first();

        if (!$customer) {
            return response()->json(['verified' => false, 'message' => 'Código inválido o vencido.'], 404);
        }

        $verified = app(LoginCodeService::class)->verifyCode($customer, $request->input('code'));

        if (!$verified) {
            return response()->json(['verified' => false, 'message' => 'Código inválido o vencido.'], 422);
        }

        // Ítem 3.3c: además del flag liviano de sesión, logueamos al
        // cliente en el guard `customer` -- así el link "Editar en Mi
        // Perfil" que se muestra tras verificar no le vuelve a pedir login.
        Auth::guard('customer')->login($customer);

        session(['customer_identified' => [
            'email' => $email,
            'customer_id' => $customer->id,
        ]]);

        $regionId = null;
        if (!empty($customer->city_id)) {
            $city = \Masso\City::find($customer->city_id);
            $regionId = $city ? $city->region_id : null;
        }

        return response()->json([
            'verified' => true,
            'autofill' => [
                'name' => $customer->name,
                'lastname' => $customer->lastname,
                'email' => $customer->email,
                'rut' => $customer->rut,
                'passport' => $customer->passport,
                'gender' => $customer->gender,
                'nationality_country_id' => $customer->nationality_country_id,
                'city_id' => $customer->city_id,
                'region_id' => $regionId,
                'country_id' => $customer->country_id,
                'custom_city' => $customer->custom_city,
            ],
        ]);
    }


    /**
     * Reenvía el código OTP, ya sea por correo o (solo si el cliente tiene
     * teléfono guardado) por SMS vía Twilio.
     */
    public function resendCustomerCode(Request $request, $slug)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'channel' => 'required|in:email,sms',
        ]);

        if ($validator->fails()) {
            return response()->json(['sent' => false, 'message' => 'Datos inválidos.'], 422);
        }

        $email = mb_strtolower(trim($request->input('email')));
        $customer = Customer::where('email', $email)->first();

        if (!$customer) {
            return response()->json(['sent' => false, 'message' => 'No encontramos una cuenta con ese correo.'], 404);
        }

        $channel = $request->input('channel');
        $service = app(LoginCodeService::class);

        if ($channel === CustomerLoginCode::CHANNEL_SMS && !$service->canSendSms($customer)) {
            return response()->json(['sent' => false, 'message' => 'No tienes un teléfono guardado.'], 422);
        }

        if (!$service->canResend($customer)) {
            return response()->json(['sent' => false, 'message' => 'Espera un momento antes de solicitar otro código.'], 429);
        }

        $sent = $service->sendCode($customer, $channel);

        return response()->json([
            'sent' => $sent,
            'message' => $sent ? 'Código reenviado.' : 'No pudimos enviar el código, intenta nuevamente.',
        ]);
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

        // El autocompletado por dispositivo (device_token) quedó obsoleto:
        // el checkout ahora autocompleta vía el Customer verificado por
        // OTP (ver register.blade.php / verifyCustomerCode), que es más
        // confiable porque está ligado a la identidad real, no al
        // navegador. getLastPaymentFromDevice() sigue existiendo y se
        // sigue usando para el aviso de "posible compra duplicada"
        // (checkDuplicatePayment/resendLastPayment) -- eso no cambia.
        $autofill = [];
        $sessionIdentified = false;

        // Si ya inició sesión en su cuenta (guard `customer`, ej. venía de
        // /mi-cuenta o de una compra anterior en esta misma visita), no
        // tiene sentido volver a pedirle el email por el modal -- ya lo
        // sabemos. Se autocompleta directo y se avisa que fue por sesión
        // activa (no por el código OTP).
        $sessionCustomer = Auth::guard('customer')->user();
        if ($sessionCustomer) {
            $sessionIdentified = true;

            $regionId = null;
            if (!empty($sessionCustomer->city_id)) {
                $city = \Masso\City::find($sessionCustomer->city_id);
                $regionId = $city ? $city->region_id : null;
            }

            $autofill = [
                'name' => $sessionCustomer->name,
                'lastname' => $sessionCustomer->lastname,
                'email' => $sessionCustomer->email,
                'rut' => $sessionCustomer->rut,
                'passport' => $sessionCustomer->passport,
                'gender' => $sessionCustomer->gender,
                'nationality_country_id' => $sessionCustomer->nationality_country_id,
                'city_id' => $sessionCustomer->city_id,
                'region_id' => $regionId,
                'country_id' => $sessionCustomer->country_id,
                'custom_city' => $sessionCustomer->custom_city,
            ];
        }

        return view('guest.register', compact('title','event', 'bodyClass', 'lang', 'countries', 'chile', 'autofill', 'sessionIdentified'));
    }


    /**
     * Verifica si existe un pago previo similar (mismo dispositivo, email y tickets) para evitar duplicados.
     */
    public function checkDuplicatePayment(Request $request, $slug)
    {
        $event = Event::where('slug', $slug)->where('status', 1)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'tickets' => 'required|array|min:1',
            'tickets.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'duplicate' => false,
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $payload = $validator->getData();
        $data = [
            'email' => isset($payload['email']) ? $payload['email'] : null,
            'tickets' => isset($payload['tickets']) ? $payload['tickets'] : [],
        ];

        $lastPayment = $this->getLastPaymentFromDevice($request, true);

        if (empty($lastPayment) || (int) $lastPayment->event_id !== (int) $event->id) {
            return response()->json(['duplicate' => false]);
        }

        $emailMatches = strtolower(trim($lastPayment->email)) === strtolower(trim($data['email']));
        $selected = collect($data['tickets'])->map(fn($id) => (int) $id)->sort()->values();
        $lastTickets = $lastPayment->details()->pluck('ticket_id')->map(fn($id) => (int) $id)->sort()->values();
        $ticketsMatch = $selected->count() === $lastTickets->count() && $selected->values()->toJson() === $lastTickets->values()->toJson();

        if (!$emailMatches || !$ticketsMatch) {
            return response()->json(['duplicate' => false]);
        }

        $details = $lastPayment->details()->with('ticket')->get();
        $summary = [
            'id' => $lastPayment->id,
            'email' => $lastPayment->email,
            'amount' => $lastPayment->amount,
            'status' => $lastPayment->status,
            'managment' => $lastPayment->managment,
            'created_at' => $lastPayment->created_at ? $lastPayment->created_at->format('Y-m-d H:i') : null,
            'tickets' => $details->map(function ($detail) {
                return [
                    'id' => $detail->ticket_id,
                    'name' => $detail->ticket ? $detail->ticket->name : '',
                    'price' => $detail->price,
                ];
            })->values()->toArray(),
        ];

        return response()->json([
            'duplicate' => true,
            'payment' => $summary,
        ]);
    }


    /**
     * Reenviar por correo los datos del último pago asociado al dispositivo (para evitar duplicados).
     */
    public function resendLastPayment(Request $request, $slug)
    {
        $event = Event::where('slug', $slug)->where('status', 1)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Parámetros inválidos.',
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $payload = $validator->getData();
        $data = [
            'payment_id' => isset($payload['payment_id']) ? $payload['payment_id'] : null,
        ];

        $lastPayment = $this->getLastPaymentFromDevice($request, true);

        if (empty($lastPayment) || (int) $lastPayment->id !== (int) $data['payment_id'] || (int) $lastPayment->event_id !== (int) $event->id) {
            return response()->json([
                'message' => 'No encontramos un pago previo asociado a este dispositivo.',
            ], 404);
        }

        try {
            if (filter_var($lastPayment->email, FILTER_VALIDATE_EMAIL)) {
                if (in_array($lastPayment->managment, ['transfer', 'transfer2']) || $lastPayment->status === 'pending') {
                    Mail::to($lastPayment->email)->send(new OrderTransferPayment($lastPayment));
                } else {
                    Mail::to($lastPayment->email)->send(new OrderPayment($lastPayment));
                }
            }

            return response()->json([
                'message' => 'Correo reenviado correctamente.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error reenviando correo de pago', [
                'payment_id' => $lastPayment->id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'No pudimos reenviar el correo, intenta nuevamente.',
            ], 500);
        }
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

        // Ítem 3.3c: name/lastname/email/rut/passport/gender/nationality/
        // ubicación ya NO se escriben en payments -- son datos "default"
        // del comprador, resueltos vía el Customer vinculado (accessors en
        // Payment). linkCustomerToPurchase() los completa ahí (solo si
        // estaban vacíos) a partir de $data.
        //
        // data/data_json tampoco se escriben más: no los lee nadie en el
        // código actual (Payment::processData() no lo llama nada, y los
        // campos personalizados del evento ya se guardan aparte en
        // event_input_values vía storeEventInputValues() más abajo, que
        // sigue usando $dataPayment igual que antes). Las columnas y los
        // pagos históricos que ya las tenían no se tocan.
        $payment = [
            'description' => $event->name,
            'amount' => $tickets->amount,
            'status' => $status,
            'dte' => '',
            'document' => '',
            'managment' => $data['payment'],
            'type' => 'inscription',
            'notified' => 0,
            'event_id' => $event->id,
            'has_inscription' => 0,
            'billing_method' => $data['billing_method'],
            'coupon_id' => $coupon ? $coupon->id : null,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount,
        ];

        if ($payment['billing_method'] == Payment::$BILLING_METHOD_INVOICE) {
            $payment['invoice_data'] = $data['invoice_data'];
        }

        $payment['customer_id'] = $this->linkCustomerToPurchase($data)->id;

        if (!$payment = Payment::create($payment)) {
            \Session::flash('error_alert', 'Ocurrió un error el procesar el pago, intentalo nuevamente');
            return redirect()->route('public.register', ['id' => $slug])->withInput();
        }

        $this->storeEventInputValues($event, $payment, $dataPayment);

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

        // Si la anula en WebPay ("Anular compra y volver"), CartController
        // debe devolverla al formulario del evento del que vino, no al
        // formulario de pago grupal (/pagos) -- ver check()/verify().
        session(['webpay_retry_return_to' => route('public.register', ['id' => $slug], false)]);

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

        // Autocompletado por dispositivo removido (ver register(), mismo
        // motivo) -- este formulario de pagos grupales no tiene paso de
        // identificación por OTP, así que simplemente no autocompleta nada.
        $autofill = [];

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
            'purchase_order_number' => 'bail|sometimes|nullable|required_if:po_input_mode,number|max:255',
            'purchase_order_file' => 'bail|sometimes|nullable|required_if:po_input_mode,file|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'participants_excel' => 'nullable|file|mimes:xls,xlsx,csv|max:10240',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->all();
        $ticket_id = $data['ticket_id'];
        $payment_type = $data['payment'];

        // Optional participants excel
        if ($request->hasFile('participants_excel')) {
            $dir = public_path('files/group_participants');
            if (!file_exists($dir)) {
                @mkdir($dir, 0775, true);
            }

            $file = $request->file('participants_excel');
            $originalName = $file->getClientOriginalName();
            $safeName = date('YmdHis') . '-' . preg_replace('/[^a-zA-Z0-9._-]/', '_', strtolower($originalName));
            $file->move($dir, $safeName);

            $fullPath = $dir . DIRECTORY_SEPARATOR . $safeName;
            $data['participants_excel_file'] = '/files/group_participants/' . $safeName;

            try {
                $import = new \Masso\Excel\ParticipantsExcelImport();
                $result = $import->validateAndCount($fullPath);
                if (!isset($result['ok']) || $result['ok'] !== true) {
                    $bag = isset($result['bag']) ? $result['bag'] : null;
                    if ($bag) {
                        return redirect()->back()->withErrors($bag)->withInput();
                    }
                    return redirect()->back()->withInput();
                }

                $data['participants_count'] = (int) $result['participants_count'];
            } catch (\Exception $e) {
                // Fallback defensivo
                $bag = new \Illuminate\Support\MessageBag();
                $bag->add('participants_excel', 'No se pudo leer el Excel. Verifica que el archivo sea válido (.xlsx/.xls/.csv) y respete el formato.');
                return redirect()->back()->withErrors($bag)->withInput();
            }

            // Avoid serializing UploadedFile instances
            if (isset($data['participants_excel'])) {
                unset($data['participants_excel']);
            }
        }

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

        $data['customer_id'] = $this->linkCustomerToPurchase($data)->id;

        // Ítem 3.3c: igual que en process(), estos datos "default" del
        // comprador ya no se escriben en payments -- linkCustomerToPurchase()
        // (arriba) ya los completó en el Customer si estaban vacíos.
        $paymentData = Arr::except($data, [
            'name', 'lastname', 'email', 'rut', 'passport', 'gender',
            'nationality_country_id', 'city_id', 'country_id', 'custom_city',
        ]);

        if ($data['amount'] < 10 || !$payment = Payment::create($paymentData)) {
            \Session::flash('error_alert', 'Ocurrió un error el procesar el pago, intentalo nuevamente');
            return redirect()->route('public.payment')->withInput();
        }

        $this->persistLastPaymentForDevice($request, $payment->id, false);

        $payment_detail = new PaymentDetail();
        $payment_detail->type = 1;
        $payment_detail->payment_id = $payment->id;
        $payment_detail->ticket_id = $ticket_id;
        $payment_detail->price = $amount;
        // Este flujo siempre arranca con el pago en 'pending' (nunca pre-pagado).
        $payment_detail->status = PaymentDetail::STATUS_RESERVED;
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
