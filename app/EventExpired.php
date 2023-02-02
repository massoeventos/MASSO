<?php
namespace Masso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventExpired extends Model
{

    use SoftDeletes;

    protected $table = 'events_expired';
    protected $fillable = ['name','date_init','date_finish','description','photo','location'];
    protected $primaryKey = 'id';

    public static $months = ['Sep'=>'Septiembre', 'Oct'=>'Octubre', 'Nov'=>'Noviembre', 'Dec'=>'Diciembre', 'Jan'=>'Enero', 'Feb'=>'Febrero', 'Mar'=>'Marzo', 'May'=>'Mayo', 'Apr'=>'Abril', 'Jun'=>'Junio', 'Jul'=>'Julio', 'Aug'=>'Agosto'];

    public function getDateString(){

    	$tinit = strtotime($this->date_init);
    	$tfinish = strtotime($this->date_finish);
    	$datediff = $tfinish - $tinit;
    	$day = '';

    	if( $tinit == $tfinish )
    		$day = date('d', $tinit);
    	elseif( $datediff == 86400 )
    		$day = date('d', $tinit).' y '.date('d', $tfinish);
    	else
    		$day = date('d', $tinit).' - '.date('d', $tfinish);

    	if( date('M', $tinit) == date('M', $tfinish) )
    		return $day.' de '.EventExpired::$months[date('M', $tinit)].' de '.date('Y', $tinit);
    	else
    		return date('d', $tinit).' de '.EventExpired::$months[date('M', $tinit)].' al '.date('d', $tfinish).' de '.EventExpired::$months[date('M', $tfinish)].' de '.date('Y', $tfinish);
    }

    public function getFinishString(){
        $tfinish = strtotime($this->date_finish);

        return date('d', $tfinish).' de '.EventExpired::$months[date('M', $tfinish)].' de '.date('Y', $tfinish);
    }
}
