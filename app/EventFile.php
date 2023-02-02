<?php
namespace Masso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventFile extends Model
{

	use SoftDeletes;
	
    protected $table = 'events_file';
    protected $fillable = ['name','file','event_id', 'uuid', 'extension'];
    public static $months = ['Sep'=>'Septiembre', 'Oct'=>'Octubre', 'Nov'=>'Noviembre', 'Dec'=>'Diciembre', 'Jan'=>'Enero', 'Feb'=>'Febrero', 'Mar'=>'Marzo', 'May'=>'Mayo', 'Apr'=>'Abril', 'Jun'=>'Junio', 'Jul'=>'Julio', 'Aug'=>'Agosto'];
    protected $primaryKey = 'id';


    public function checkUUID(){

    	if( empty($this->uuid) ):
    		$this->uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
	        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
	        mt_rand(0, 0xffff),
	        mt_rand(0, 0x0fff) | 0x4000,
	        mt_rand(0, 0x3fff) | 0x8000,
	        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
    		$this->save();
    	endif;

    }


    public function getPath(){
    	return route('public.download', $this->uuid);
    }

    public function getCreatedString( $type = '' ){
        $tfinish = strtotime($this->created_at);

        if( $type == 'short' )
            return date('d', $tfinish).' '.EventExpired::$months[date('M', $tfinish)].', '.date('Y', $tfinish);

        return date('d', $tfinish).' de '.EventExpired::$months[date('M', $tfinish)].' de '.date('Y', $tfinish);
    }

    
}
