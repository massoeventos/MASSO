<?php
namespace Masso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Masso\Mail\OrderPayment;
use Masso\Mail\OrderTransferPayment;

class Payment extends Model
{

	use SoftDeletes;

    protected $table = 'payments';
    protected $fillable = [
        'id',
        'name',
        'lastname',
        'email',
        'rut',
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
    ];
    protected $primaryKey = 'id';

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
        return self::getRutPrint($this->attributes['rut']);
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

        try {
            $_data = unserialize($this->data);
            $data = [];
            foreach( $_data as $key => $value ):

                $key = str_replace(['_'], [' '], $key);
                $data[strtolower($key)] = $value;

            endforeach;

        } catch (\Exception $e) {
            $data = [];
        }

        return $data;
    }

    public function getPropertyData($data, $key)
    {
        return array_key_exists($key, $data) ? $data[$key] : '';
    }
}
