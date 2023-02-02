<?php
namespace Masso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Transaction extends Model
{

	use SoftDeletes;
	
    protected $table = 'transactions';
    protected $fillable = ['payment_id','amount','token','payment_type','response_code','quotes','auth_code','card_number'];
    protected $primaryKey = 'id';

    public function client()    {
        return $this->belongsTo('Masso\Client', 'client_id', 'id')->withTrashed();
    }

    public function typePayment(){
        return ($this->payment_type == 'VN' ) ? 'Crédito' : 'Débito';
    }

    public function getStatus(){

        if( $this->response_code == 0 )
            return 'Exitoso';

        if( $this->response_code == 9 )
            return 'Abortado';

    }
}
