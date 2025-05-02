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
        'description',
        'dte',
        'document',
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
        'has_inscription',
        'user_observation',
    ];
    protected $primaryKey = 'id';

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


    public function getCanal()
    {
        return $this->managment == 'webpay' ? 'WebPay' : 'Transferencia';
    }

    public function getEvent()
    {
        $events = array();
        foreach ($this->detail as $item) {
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
        foreach($this->detail as $item) {
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
