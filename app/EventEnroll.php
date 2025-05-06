<?php
namespace Masso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventEnroll extends Model
{

	use SoftDeletes;

    protected $table = 'events_enroll';
    protected $fillable = ['event_id','name','lastname','passport','email','phone','profession','speciality','workplace','city','country','data','ticket_id'];
    protected $primaryKey = 'id';

    private $field_private = [
        '_token',
        'name',
        'lastname',
        'passport',
        'email',
        'ticket',
        'payment',
        'check',
        'ids',
        'amount',
        'available',
        'event_id'
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

    public function getName(){
    	return ucwords(strtolower($this->name.' '.$this->lastname));
    }
    
    public function getRutPrintAttribute()
    {
        $rut = $this->attributes['rut'];

        if (strlen($rut) == 9) { // Si tiene 9 caracteres, agrega el guion antes del dígito verificador
            return substr($rut, 0, 8) . '-' . substr($rut, 8, 1);
        }
    
        return $rut;
    }

    public function processData(){

        try {
            $_data = unserialize($this->data);
            $data = [];
            foreach( $_data as $key => $value ):

                if( in_array($key, $this->field_private) )
                    continue;

                $key = str_replace(['_'], [' '], $key);
                $data[$key] = $value;

            endforeach;

        } catch (\Exception $e) {
            $data = [];
        }

        $this->enrolldata = $data;
        return $data;
    }

}
