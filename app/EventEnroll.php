<?php
namespace Masso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventEnroll extends Model
{

	use SoftDeletes;

    protected $table = 'events_enroll';
    protected $fillable = ['event_id','name','lastname','passport','email','phone','profession','speciality','workplace','city','country','data','data_json','ticket_id','payment_id','payment_detail_id'];
    protected $primaryKey = 'id';

    protected $casts = [
        'data_json' => 'array',
    ];

    public $enrolldata;

    public function ticket()    {
        return $this->hasOne('Masso\EventTicket', 'id', 'ticket_id')->withTrashed();
    }

    public function event()    {
        return $this->belongsTo('Masso\Event', 'event_id', 'id')->withTrashed();
    }

    public function payment()    {
        return $this->belongsTo('Masso\Payment', 'payment_id', 'id')
            ->withTrashed();
    }

    public function paymentDetail()
    {
        return $this->belongsTo('Masso\PaymentDetail', 'payment_detail_id', 'id')->withTrashed();
    }

    public function cityRel()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    public function countryRel()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function nationalityCountry()
    {
        return $this->belongsTo(Country::class, 'nationality_country_id', 'id');
    }

    /**
     * Cuando este asistente viene de un pago real (payment_detail_id
     * seteado), sus datos personales se resuelven a través de ese pago en
     * vez de las columnas propias de events_enroll, para no duplicarlos.
     * Para invitados manuales (payment_detail_id nulo) devuelve null y
     * cada accessor cae de vuelta a la columna propia, sin cambios.
     */
    private function linkedPayment()
    {
        if (!$this->payment_detail_id) {
            return null;
        }

        return $this->paymentDetail ? $this->paymentDetail->payment : null;
    }

    public function getNameAttribute($value)
    {
        $payment = $this->linkedPayment();
        return $payment ? $payment->name : $value;
    }

    public function getLastnameAttribute($value)
    {
        $payment = $this->linkedPayment();
        return $payment ? $payment->lastname : $value;
    }

    public function getEmailAttribute($value)
    {
        $payment = $this->linkedPayment();
        return $payment ? $payment->email : $value;
    }

    public function getRutAttribute($value)
    {
        $payment = $this->linkedPayment();
        return $payment ? $payment->rut : $value;
    }

    public function getPassportAttribute($value)
    {
        // A diferencia de name/lastname/rut/email, payments.passport es una
        // columna nueva que todavía puede no estar poblada para pagos viejos
        // (ver masso:backfill-payment-passport): se prefiere el dato del
        // pago solo cuando ya lo tiene, para no perder el valor histórico
        // propio de events_enroll mientras se completa el backfill.
        $payment = $this->linkedPayment();

        if ($payment && !empty($payment->passport)) {
            return $payment->passport;
        }

        return $value;
    }

    public function getCityIdAttribute($value)
    {
        $payment = $this->linkedPayment();
        return $payment ? $payment->city_id : $value;
    }

    public function getCountryIdAttribute($value)
    {
        $payment = $this->linkedPayment();
        return $payment ? $payment->country_id : $value;
    }

    public function getNationalityCountryIdAttribute($value)
    {
        $payment = $this->linkedPayment();
        return $payment ? $payment->nationality_country_id : $value;
    }

    public function getCustomCityAttribute($value)
    {
        $payment = $this->linkedPayment();
        return $payment ? $payment->custom_city : $value;
    }

    public function getName(){
    	return ucwords(strtolower($this->name.' '.$this->lastname));
    }

    public function getDeepCountryAttribute()
    {
        if($this->cityRel){
            return $this->cityRel->region->country;
        }

        return $this->countryRel;
    }

    public function getRutPrintAttribute()
    {
        $rut = $this->rut;

        if (strlen((string) $rut) >= 8) {
            return substr($rut, 0, -1) . '-' . substr($rut, -1);
        }

        return $rut;
    }

    public function processData(){

        // Las respuestas a los campos dinámicos del evento (events_inputs)
        // ya están normalizadas en event_input_values, con el nombre
        // original de la pregunta (sin el str_replace(' ','_',...) que
        // hacía falta cuando vivían solo en el blob serializado).
        $data = [];

        if ($this->payment) {
            foreach ($this->payment->inputValues as $inputValue) {
                if ($inputValue->eventInput) {
                    $data[$inputValue->eventInput->name] = $inputValue->value;
                }
            }
        }

        $this->enrolldata = $data;
        return $data;
    }

}
