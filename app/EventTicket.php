<?php
namespace Masso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventTicket extends Model
{

	use SoftDeletes;

    protected $table = 'events_tickets';
    protected $fillable = [
        'event_id',
        'name',
        'name_eng',
        'description',
        'description_eng',
        'price',
        'stock',
        'from',
        'to',
        'is_mandatory'
    ];
    protected $primaryKey = 'id';

    public static $months = ['Sep'=>'Septiembre', 'Oct'=>'Octubre', 'Nov'=>'Noviembre', 'Dec'=>'Diciembre', 'Jan'=>'Enero', 'Feb'=>'Febrero', 'Mar'=>'Marzo', 'May'=>'Mayo', 'Apr'=>'Abril', 'Jun'=>'Junio', 'Jul'=>'Julio', 'Aug'=>'Agosto'];

    public function getDateString(){

    	$tinit = strtotime($this->from);
    	$tfinish = strtotime($this->to);
    	$datediff = $tfinish - $tinit;
    	$day = '';

    	if( $tinit == $tfinish )
    		$day = date('d', $tinit);
    	elseif( $datediff == 86400 )
    		$day = date('d', $tinit).' al '.date('d', $tfinish);
    	else
    		$day = date('d', $tinit).' al '.date('d', $tfinish);

    	if( date('M', $tinit) == date('M', $tfinish) )
    		return $day.' de '.Event::$months[date('M', $tinit)].' de '.date('Y', $tinit);
    	else
    		return date('d', $tinit).' de '.Event::$months[date('M', $tinit)].' al '.date('d', $tfinish).' de '.Event::$months[date('M', $tfinish)].' de '.date('Y', $tfinish);
    }

    public function getDateStringEng(){

    	$tinit = strtotime($this->from);
    	$tfinish = strtotime($this->to);
    	$datediff = $tfinish - $tinit;
    	$day = '';

    	if( $tinit == $tfinish )
    		$day = date('d', $tinit);
    	elseif( $datediff == 86400 )
    		$day = date('d', $tinit).' to '.date('d', $tfinish);
    	else
    		$day = date('d', $tinit).' to '.date('d', $tfinish);

    	if( date('F', $tinit) == date('F', $tfinish) )
    		return $day.' '.date('F', $tinit).', '.date('Y', $tinit);
    	else
    		return date('d', $tinit).' '.date('F', $tinit).' to '.date('d', $tfinish).' '.date('F', $tfinish).', '.date('Y', $tfinish);
    }

    public function availableText(){

    	if( !$this->isAvailable() )
    		return false;

    	return 'Disponible desde el '.$this->getDateString().'.';

    }

    public function event(){
        return $this->belongsTo('Masso\Event', 'event_id', 'id');
    }

    public function availableEngText(){

    	if( !$this->isAvailable() )
    		return false;

    	return 'Available on: '.$this->getDateStringEng().'.';

    }

    public function isAvailable(){
    	if( $this->stock < 1 )
    		return false;

    	if( !((bool)strtotime($this->to)) or !((bool)strtotime($this->from)) )
    		return false;

    	$to = strtotime($this->to.' 23:59:59');
    	$from = strtotime($this->from. '00:00:00');

    	if( $to < time() or $from > time() )
    		return false;

    	return true;

    }

    public function getTicketToBuy($event_id, $tickets)
    {
        $amount = 0;
        $available = true;

        $ticketsToBuy = new \stdClass();
        $ticketsToBuy->ids = $tickets;

        $data = $this
            ->select('price', 'stock', 'to', 'from')
            ->where('event_id', $event_id)
            ->whereIn('id', $tickets)->get();


        foreach ($data as $ticket) {
            if (!$ticket->isAvailable()) {
                $available = false;
                break;
            }
            $amount+= $ticket->price;
        }

        $ticketsToBuy->amount = $amount;
        $ticketsToBuy->available = $available;

        return $ticketsToBuy;
    }

    public function getIsMandatoryBooleanAttribute()
    {
        return $this->is_mandatory === 1 ? 'true' :  'false';
    }
}
