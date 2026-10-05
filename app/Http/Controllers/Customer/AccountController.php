<?php

namespace Masso\Http\Controllers\Customer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Masso\Country;
use Masso\Customer;
use Masso\CustomerLoginCode;
use Masso\Http\Controllers\Controller;
use Masso\Mail\OrderTransferPayment;
use Masso\Payment;
use Masso\Services\Otp\LoginCodeService;
use Masso\Transaction;
use Masso\WebPay\WebPayTransaction;

/**
 * Cuenta del cliente (Bloque 3, ítem 3.3): historial de compras y las dos
 * únicas configuraciones opcionales de identidad -- password y teléfono --
 * que nunca se piden en el checkout, solo acá.
 */
class AccountController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('customer')->check()) {
            // Ruta relativa a propósito: el grupo de rutas fija el dominio
            // (ROUTE_WEB) sin puerto, y route() por defecto genera una URL
            // absoluta que pierde el puerto si se corre con `artisan serve`
            // en uno distinto al de producción (causaba 404 al redirigir).
            return redirect(route('customer.account', [], false));
        }

        return view('customer.login');
    }

    /**
     * Primer paso: solo el email. Las cuentas nacen siempre de una compra
     * (ver PublicController::linkCustomerToPurchase) -- acá no se crea
     * ninguna, solo se decide si mostrar password o mandar un código.
     */
    public function identify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'invalid'], 422);
        }

        $email = mb_strtolower(trim($request->input('email')));
        $customer = Customer::where('email', $email)->first();

        if (!$customer) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'No encontramos una cuenta con ese correo. Se crea automáticamente después de tu primera compra.',
            ], 404);
        }

        if ($customer->hasPassword()) {
            return response()->json(['status' => 'has_password']);
        }

        app(LoginCodeService::class)->sendCode($customer, CustomerLoginCode::CHANNEL_EMAIL);

        return response()->json([
            'status' => 'code_sent',
            'can_sms' => app(LoginCodeService::class)->canSendSms($customer),
        ]);
    }

    public function attemptPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Datos inválidos.'], 422);
        }

        $email = mb_strtolower(trim($request->input('email')));

        if (!Auth::guard('customer')->attempt(['email' => $email, 'password' => $request->input('password')])) {
            return response()->json(['success' => false, 'message' => 'Contraseña incorrecta.'], 422);
        }

        $request->session()->regenerate();

        return response()->json(['success' => true, 'redirect' => route('customer.account', [], false)]);
    }

    public function sendLoginCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'channel' => 'nullable|in:email,sms',
        ]);

        if ($validator->fails()) {
            return response()->json(['sent' => false, 'message' => 'Datos inválidos.'], 422);
        }

        $email = mb_strtolower(trim($request->input('email')));
        $customer = Customer::where('email', $email)->first();

        if (!$customer) {
            return response()->json(['sent' => false, 'message' => 'No encontramos una cuenta con ese correo.'], 404);
        }

        $channel = $request->input('channel', CustomerLoginCode::CHANNEL_EMAIL);
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
            'message' => $sent ? 'Código enviado.' : 'No pudimos enviar el código, intenta nuevamente.',
        ]);
    }

    public function verifyLoginCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Datos inválidos.'], 422);
        }

        $email = mb_strtolower(trim($request->input('email')));
        $customer = Customer::where('email', $email)->first();

        if (!$customer || !app(LoginCodeService::class)->verifyCode($customer, $request->input('code'))) {
            return response()->json(['success' => false, 'message' => 'Código inválido o vencido.'], 422);
        }

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return response()->json(['success' => true, 'redirect' => route('customer.account', [], false)]);
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('customer.login', [], false));
    }

    /**
     * Historial de compras del cliente autenticado. Solo pagos hechos
     * después del Bloque 3 tienen customer_id (sin backfill histórico a
     * propósito, ver plan) -- un cliente antiguo simplemente no ve compras
     * previas a este cambio.
     */
    public function account()
    {
        $customer = Auth::guard('customer')->user();

        $payments = Payment::where('customer_id', $customer->id)
            ->with('details.ticket', 'event')
            ->orderByDesc('created_at')
            ->get();

        return view('customer.account', compact('customer', 'payments'));
    }

    /**
     * "Retomar" una compra pendiente desde el historial. Para transferencia
     * no hay nada que reintentar contra un gateway -- solo se reenvían los
     * datos bancarios por correo. Para webpay se genera una sesión nueva
     * (el token viejo, si nunca se completó, ya no sirve de todas formas).
     */
    public function retryPayment($paymentId)
    {
        $customer = Auth::guard('customer')->user();

        $payment = Payment::where('id', $paymentId)
            ->where('customer_id', $customer->id)
            ->where('status', 'pending')
            ->first();

        if (!$payment) {
            abort(404);
        }

        // Una compra pendiente no reserva stock (solo se descuenta al
        // confirmarse) -- entre que se creó y que el cliente vuelve a
        // reintentar puede haberse agotado el ticket o cerrado su fecha
        // de venta. Sin este chequeo, pagar igual dejaría el stock en
        // negativo al confirmarse (ver EventTicket::isAvailable()).
        $noLongerAvailable = $payment->details->first(function ($detail) {
            return !$detail->ticket || !$detail->ticket->isAvailable();
        });

        if ($noLongerAvailable) {
            \Session::flash('error_alert', 'Uno de los tickets de esta compra ya no está disponible (agotado o fuera de la fecha de venta). Contáctanos si necesitas ayuda.');
            return redirect(route('customer.account', [], false));
        }

        if (in_array($payment->managment, ['transfer', 'transfer2'])) {
            Mail::to($payment->email)->send(new OrderTransferPayment($payment));
            \Session::flash('success_alert', 'Te reenviamos los datos de la transferencia a tu correo.');
            return redirect(route('customer.account', [], false));
        }

        // Si la anula en WebPay ("Anular compra y volver"), CartController
        // debe devolverla a Mi Cuenta, no al formulario público de pago
        // donde nunca estuvo -- ver check()/verify().
        session(['webpay_retry_return_to' => route('customer.account', [], false)]);

        $transaction = new WebPayTransaction;
        $transaction = $transaction->initTransaction(
            $payment->amount,
            $payment->id,
            route('cart.validate'),
            route('cart.verify')
        );

        if (get_class($transaction) != 'Transbank\Webpay\WebpayPlus\Responses\TransactionCreateResponse') {
            \Session::flash('error_alert', 'Ocurrió un error al iniciar el pago, intenta nuevamente.');
            return redirect(route('customer.account', [], false));
        }

        Transaction::create([
            'response_code' => 9,
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
            'token' => $transaction->token,
        ]);

        return view('guest.webpay', ['url' => $transaction->url, 'token' => $transaction->token]);
    }

    /**
     * Pantalla separada para configurar password/teléfono (3.3b) y los
     * datos "default" del comprador (3.3c) -- el historial de compras
     * (account()) ya no mezcla nada de esto.
     */
    public function profile()
    {
        $customer = Auth::guard('customer')->user();
        $lang = 'esp';

        $countries = Country::orderBy('is_other')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function ($country) use ($lang) {
                return [$country->id => $country->getTranslatedName($lang)];
            });

        $chile = Country::where('name', Country::$CHILE_NAME)->firstOrFail();

        return view('customer.profile', compact('customer', 'lang', 'countries', 'chile'));
    }

    /**
     * Guarda los datos "default" del comprador (ítem 3.3c) -- nombre/
     * apellido/género/nacionalidad/rut-o-pasaporte son obligatorios; la
     * ubicación queda opcional acá porque su necesidad depende del evento,
     * no del perfil (si falta, el checkout la vuelve a pedir esa vez).
     */
    public function updateProfile(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $chile = Country::where('name', Country::$CHILE_NAME)->firstOrFail();
        $isChile = (string) $request->input('nationality_country_id') === (string) $chile->id;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'gender' => 'required|in:female,male,non_binary,other',
            'nationality_country_id' => 'required|exists:countries,id',
            'rut' => $isChile ? 'required|string|max:20' : 'nullable|string|max:20',
            'passport' => !$isChile ? 'required|string|max:50' : 'nullable|string|max:50',
            'country_id' => 'nullable|exists:countries,id',
            'city_id' => 'nullable|exists:cities,id',
            'custom_city' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $customer->name = $request->input('name');
        $customer->lastname = $request->input('lastname');
        $customer->gender = $request->input('gender');
        $customer->nationality_country_id = $request->input('nationality_country_id');
        $customer->rut = $isChile ? $request->input('rut') : null;
        $customer->passport = !$isChile ? $request->input('passport') : null;

        // Bug corregido: esto debe decidirse por el país de RESIDENCIA
        // (country_id) que se envió, no por $isChile (que es la
        // nacionalidad) -- alguien con nacionalidad extranjera puede
        // residir en Chile, y usaba el branch equivocado, guardando
        // country_id=Chile junto con un custom_city viejo y sin city_id.
        $residesInChile = (string) $request->input('country_id') === (string) $chile->id;

        if ($residesInChile && $request->filled('city_id')) {
            $customer->city_id = $request->input('city_id');
            $customer->country_id = null;
            $customer->custom_city = null;
        } elseif (!$residesInChile && $request->filled('custom_city')) {
            $customer->country_id = $request->input('country_id');
            $customer->custom_city = $request->input('custom_city');
            $customer->city_id = null;
        }

        $customer->save();

        return response()->json(['success' => true, 'message' => 'Datos actualizados.']);
    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $customer = Auth::guard('customer')->user();
        $customer->password = Hash::make($request->input('password'));
        $customer->save();

        return response()->json(['success' => true, 'message' => 'Contraseña actualizada.']);
    }

    /**
     * Antes de guardar un teléfono nuevo se confirma por SMS que el cliente
     * realmente lo controla -- si no, cualquiera podría dejar guardado el
     * número de otra persona como vía de recuperación. El teléfono no se
     * persiste hasta que el código se verifica (ver verifyPhoneCode).
     */
    public function requestPhoneCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
        ]);

        if ($validator->fails()) {
            return response()->json(['sent' => false, 'message' => 'Ingresa un teléfono válido, con código de país (ej: +56912345678).'], 422);
        }

        $customer = Auth::guard('customer')->user();
        $phone = $request->input('phone');

        $service = app(LoginCodeService::class);
        if (!$service->canResend($customer)) {
            return response()->json(['sent' => false, 'message' => 'Espera un momento antes de solicitar otro código.'], 429);
        }

        // Se envía al número nuevo (todavía no guardado) sin persistirlo.
        $customer->phone = $phone;
        $sent = $service->sendCode($customer, CustomerLoginCode::CHANNEL_SMS);

        if ($sent) {
            $request->session()->put('pending_phone', $phone);
        }

        return response()->json([
            'sent' => $sent,
            'message' => $sent ? 'Código enviado por SMS.' : 'No pudimos enviar el código, revisa el número.',
        ]);
    }

    public function verifyPhoneCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
        ]);

        $pendingPhone = $request->session()->get('pending_phone');

        if ($validator->fails() || empty($pendingPhone)) {
            return response()->json(['success' => false, 'message' => 'No hay un teléfono pendiente de confirmar.'], 422);
        }

        $customer = Auth::guard('customer')->user();

        if (!app(LoginCodeService::class)->verifyCode($customer, $request->input('code'))) {
            return response()->json(['success' => false, 'message' => 'Código inválido o vencido.'], 422);
        }

        $customer->phone = $pendingPhone;
        $customer->save();
        $request->session()->forget('pending_phone');

        return response()->json(['success' => true, 'message' => 'Teléfono confirmado.', 'phone' => $customer->phone]);
    }
}
