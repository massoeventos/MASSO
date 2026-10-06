<?php
namespace Masso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Masso\Mail\OrderPayment;
use Masso\Mail\OrderTransferPayment;
use Masso\Services\LegacySerializedData;

class Payment extends Model
{

	use SoftDeletes;

    /**
     * Correo de contacto para problemas de pago. Se muestra al inscrito en
     * lugar de los datos bancarios: las transferencias se coordinan a mano
     * y el equipo de Masso envía los datos por su cuenta.
     */
    const PAYMENT_CONTACT_EMAIL = 'paolamasso@massoeventos.cl';

    protected $table = 'payments';
    protected $fillable = [
        'id',
        'name',
        'lastname',
        'email',
        'rut',
        'passport',
        'description',
        'dte',
        'document',
        'purchase_order_type',
        'purchase_order_number',
        'purchase_order_file',
        'amount',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
        'notified',
        'managment',
        'data',
        'data_json',
        'type',
        'event_id',
        'city_id',
        'country_id',
        'custom_city',
        'has_inscription',
        'user_observation',
        'nationality_country_id',
        'billing_method',
        'invoice_data',
        'coupon_id',
        'discount_percentage',
        'discount_amount',
        'gender',
        'participants_excel_file',
        'participants_count',
        'customer_id',
    ];
    protected $primaryKey = 'id';

    protected $casts = [
        'data_json' => 'array',
    ];

    public static $BILLING_METHOD_RECEIPT = 'receipt';
    public static $BILLING_METHOD_INVOICE = 'invoice';

    public static function getRutPrint($rut){
        if (strlen($rut) >= 8) {
            return substr($rut, 0, -1) . '-' . substr($rut, -1);
        }
        return $rut;
    }
   
    public function getRutPrintAttribute()
    {
        return self::getRutPrint($this->rut);
    }

    /**
     * Ítem 3.3c: los datos "default" del comprador (nombre/apellido/email/
     * rut/pasaporte/género/nacionalidad/ubicación) ya no se repiten en cada
     * pago -- se resuelven a través del Customer vinculado, igual patrón
     * que EventEnroll usa con payment_detail_id desde el Bloque 2. Los
     * pagos históricos (sin customer_id) siguen leyendo su propia columna.
     * billing_method/invoice_data quedan fuera a propósito: son por compra.
     */
    private function resolvedCustomer()
    {
        return $this->customer_id ? $this->customer : null;
    }

    public function getNameAttribute($value)
    {
        $customer = $this->resolvedCustomer();
        return ($customer && !empty($customer->name)) ? $customer->name : $value;
    }

    public function getLastnameAttribute($value)
    {
        $customer = $this->resolvedCustomer();
        return ($customer && !empty($customer->lastname)) ? $customer->lastname : $value;
    }

    public function getEmailAttribute($value)
    {
        $customer = $this->resolvedCustomer();
        return ($customer && !empty($customer->email)) ? $customer->email : $value;
    }

    public function getRutAttribute($value)
    {
        $customer = $this->resolvedCustomer();
        return ($customer && !empty($customer->rut)) ? $customer->rut : $value;
    }

    public function getPassportAttribute($value)
    {
        $customer = $this->resolvedCustomer();
        return ($customer && !empty($customer->passport)) ? $customer->passport : $value;
    }

    public function getGenderAttribute($value)
    {
        $customer = $this->resolvedCustomer();
        return ($customer && !empty($customer->gender)) ? $customer->gender : $value;
    }

    public function getNationalityCountryIdAttribute($value)
    {
        $customer = $this->resolvedCustomer();
        return ($customer && !empty($customer->nationality_country_id)) ? $customer->nationality_country_id : $value;
    }

    public function getCityIdAttribute($value)
    {
        $customer = $this->resolvedCustomer();
        return ($customer && !empty($customer->city_id)) ? $customer->city_id : $value;
    }

    public function getCountryIdAttribute($value)
    {
        $customer = $this->resolvedCustomer();
        return ($customer && !empty($customer->country_id)) ? $customer->country_id : $value;
    }

    public function getCustomCityAttribute($value)
    {
        $customer = $this->resolvedCustomer();
        return ($customer && !empty($customer->custom_city)) ? $customer->custom_city : $value;
    }

    public function getInvoiceRutPrintAttribute()
    {
        if($this->invoice_data && $this->invoice_data['rut']){
            return self::getRutPrint($this->invoice_data['rut']);
        }
        return null;
    }

    public function setInvoiceDataAttribute($value)
    {
        $this->attributes['invoice_data'] = json_encode($value);
    }

    public function getInvoiceDataAttribute($value)
    {
        return $value ? json_decode($value, true) : null;
    }
    
    public function getBillingMethodPrintAttribute()
    {
        switch ($this->billing_method) {
            case self::$BILLING_METHOD_RECEIPT:
                return 'recibo';
                break;
            case self::$BILLING_METHOD_INVOICE:
                return 'factura';
                break;
        }
        return $this->billing_method;
    }

    public function getInvoiceDataField($field){
        if($this->invoice_data){
            if(isset($this->invoice_data[$field])){
                return $this->invoice_data[$field];
            }
        }
        return null;
    }

    public function success()
    {
        return $this->hasOne(
            'Masso\Transaction',
            'payment_id',
            'id')
            ->where('response_code', 0)
            ->withTrashed();
    }

    public function transactions()
    {
        return $this->hasMany('Masso\Transaction', 'payment_id', 'id')->withTrashed();
    }

    public function detail()
    {
        return $this->hasMany('Masso\PaymentDetail', 'payment_id', 'id')->withTrashed();
    }

    public function details()
    {
        return $this->hasMany('Masso\PaymentDetail', 'payment_id', 'id')->withTrashed();
    }

    public function inputValues()
    {
        return $this->hasMany('Masso\EventInputValue', 'payment_id', 'id')->withTrashed();
    }

    public function customer()
    {
        return $this->belongsTo('Masso\Customer', 'customer_id', 'id')->withTrashed();
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function nationalityCountry()
    {
        return $this->belongsTo(Country::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class)->withTrashed();
    }

    public function getCanal()
    {
        return $this->managment == 'webpay' ? 'WebPay' : 'Transferencia';
    }

    public function getGenderLabel()
    {
        switch ($this->gender) {
            case 'female':
                return 'FEMENINO';
            case 'male':
                return 'MASCULINO';
            case 'non_binary':
                return 'NO BINARIO';
            case 'other':
                return 'OTRO / PREFIERE NO RESPONDER';
        }

        return '-';
    }

    public function getEvent()
    {
        $events = array();
        foreach ($this->details as $item) {
            array_push($events, $item->ticket->name);
        }
        return count($events) > 0 ? $events : '-';
    }


    public static function notifyPayments()
    {
        $payments = Payment::where('notified', 0)->where('status', 'pagado')->get();

        foreach ($payments as $payment) {
            try {
                if (filter_var($payment->email, FILTER_VALIDATE_EMAIL)) {
                    \Mail::to($payment->email)->send(new OrderPayment($payment));
                }

                if (App::environment() === 'production') {
                    \Mail::to('pagos@massoeventos.cl')->send(new OrderPayment($payment));
                }

                $payment->notified = 1;
                $payment->save();
            } catch (\Exception $e) {
                Log::error('Error enviando correo de pago para Payment ID ' . $payment->id, [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $payments = Payment::where('notified', 0)->where('status', 'pending')->where('managment', 'transfer')->get();

        foreach ($payments as $payment) {
            try {
                if (filter_var($payment->email, FILTER_VALIDATE_EMAIL)) {
                    \Mail::to($payment->email)->send(new OrderTransferPayment($payment));
                }

                $payment->managment = 'transfer2';
                $payment->save();
            } catch (\Exception $e) {
                Log::error('Error enviando correo de transferencia para Payment ID ' . $payment->id, [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }

    public function updateTicketStock()
    {
        foreach($this->details as $item) {
            $item->ticket()->decrement('stock');
        }
    }

    public function processData(){

        $_data = LegacySerializedData::safeUnserialize($this->data);
        $data = [];

        foreach( $_data as $key => $value ):
            $key = str_replace(['_'], [' '], $key);
            $data[strtolower($key)] = $value;
        endforeach;

        return $data;
    }

    public function getPropertyData($data, $key)
    {
        return array_key_exists($key, $data) ? $data[$key] : '';
    }
}
