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

    /**
     * Códigos de Transbank (paymentTypeCode): VD = débito, VP = prepago,
     * VN/VC/SI/S2/NC = crédito (sin cuotas o en cuotas).
     */
    public function typePayment(){
        switch ($this->payment_type) {
            case 'VD':
                return 'Débito';
            case 'VP':
                return 'Prepago';
            case '':
            case null:
                return '';
            default:
                return 'Crédito';
        }
    }

    public function getStatus(){

        if( $this->response_code == 0 )
            return 'Exitoso';

        if( $this->response_code == 9 )
            return 'Abortado';

    }
}
