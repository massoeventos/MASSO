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
        'category_id',
        'position',
        'name',
        'name_eng',
        'description',
        'description_eng',
        'price',
        'stock',
        'capacity',
        'from',
        'to',
        'is_mandatory',
        'requires_document',
        'is_active',
        'kind',
    ];
    protected $primaryKey = 'id';

    // principal: inscripción al evento; complementario: taller, cena, curso...
    public const KIND_PRINCIPAL = 'principal';
    public const KIND_COMPLEMENTARY = 'complementario';
    public const KINDS = [self::KIND_PRINCIPAL, self::KIND_COMPLEMENTARY];

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

    // Columna legada: primera categoría del ticket. Para todas, categories().
    public function category(){
        return $this->belongsTo('Masso\EventTicketCategory', 'category_id', 'id');
    }

    public function categories(){
        return $this->belongsToMany('Masso\EventTicketCategory', 'event_ticket_category_ticket', 'ticket_id', 'category_id')
            ->orderBy('position')->orderBy('event_ticket_categories.id');
    }

    public function paymentDetails(){
        return $this->hasMany('Masso\PaymentDetail', 'ticket_id', 'id');
    }

    // Para cargar los vendidos de varios tickets en una consulta:
    // ->withCount(EventTicket::soldCountQuery())
    public static function soldCountQuery(): array
    {
        return ['paymentDetails as sold_count' => function ($q) {
            $q->where('status', PaymentDetail::STATUS_CONFIRMED);
        }];
    }

    // Vendidos = tickets de pagos confirmados (no se reserva cupo con
    // pagos pendientes, igual que antes).
    public function soldCount(): int
    {
        if (array_key_exists('sold_count', $this->attributes)) {
            return (int) $this->attributes['sold_count'];
        }
        return $this->paymentDetails()->where('status', PaymentDetail::STATUS_CONFIRMED)->count();
    }

    // Disponibles = cupo total - vendidos. Sin cupo cargado (no debería
    // pasar después de la migración) se usa el stock legado.
    public function availableCount(): int
    {
        if ($this->capacity === null) {
            return max(0, (int) $this->stock);
        }
        return max(0, (int) $this->capacity - $this->soldCount());
    }

    public function availableEngText(){

    	if( !$this->isAvailable() )
    		return false;

    	return 'Available on: '.$this->getDateStringEng().'.';

    }

    // A la venta: activo, con cupo disponible y dentro de su ventana de venta.
    public function isAvailable(){
    	if( !$this->is_active || $this->availableCount() < 1 )
    		return false;

    	if( !((bool)strtotime($this->to)) or !((bool)strtotime($this->from)) )
    		return false;

    	$to = strtotime($this->to.' 23:59:59');
    	$from = strtotime($this->from.' 00:00:00');

    	if( $to < time() or $from > time() )
    		return false;

    	return true;

    }

    /**
     * Carrito [ticket_id => cantidad] desde el formulario: `ticket[]` trae
     * los ids (sin repetir) y `ticket_quantity[id]` la cantidad de cada uno;
     * sin cantidad (formularios anteriores, pagos personalizados) es 1.
     */
    public static function cartFromInput($ids, $quantities = []): array
    {
        $quantities = is_array($quantities) ? $quantities : [];
        $cart = [];
        foreach ((array) $ids as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $cart[$id] = max(1, (int) ($quantities[$id] ?? 1));
            }
        }
        return $cart;
    }

    /**
     * Monto y disponibilidad de una compra. `ids` son los tickets distintos y
     * `quantities` la cantidad de cada uno. No disponible si algún ticket no
     * es del evento, no está a la venta o no le alcanzan los disponibles.
     */
    public function getTicketToBuy($event_id, $tickets, $quantities = [])
    {
        $cart = self::cartFromInput($tickets, $quantities);
        $amount = 0;

        $ticketsToBuy = new \stdClass();
        $ticketsToBuy->ids = array_keys($cart);
        $ticketsToBuy->quantities = $cart;

        $data = $this
            ->select('id', 'price', 'stock', 'capacity', 'is_active', 'to', 'from')
            ->withCount(self::soldCountQuery())
            ->where('event_id', $event_id)
            ->whereIn('id', array_keys($cart))->get();

        $available = !empty($cart) && $data->count() === count($cart);

        foreach ($data as $ticket) {
            if (!$ticket->isAvailable() || $ticket->availableCount() < $cart[$ticket->id]) {
                $available = false;
                break;
            }
            $amount += $ticket->price * $cart[$ticket->id];
        }

        $ticketsToBuy->amount = $amount;
        $ticketsToBuy->available = $available;

        return $ticketsToBuy;
    }

    public function getIsMandatoryBooleanAttribute()
    {
        return $this->is_mandatory === 1 ? 'true' :  'false';
    }

    public function getRequiresDocumentBooleanAttribute()
    {
        return $this->requires_document === 1 ? 'true' : 'false';
    }
}
