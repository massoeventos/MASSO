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
        'max_per_purchase',
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

    // Complementario: entradas principales con las que se puede comprar.
    // Sin filas = disponible con todas.
    public function allowedWith(){
        return $this->belongsToMany('Masso\EventTicket', 'event_ticket_complements', 'complement_ticket_id', 'principal_ticket_id');
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
    	return $this->availableCount() > 0 && $this->isOnSaleWindow();
    }

    // Agotado: se vendería (activo y en fecha), pero no quedan disponibles.
    public function isSoldOut(){
    	return $this->availableCount() < 1 && $this->isOnSaleWindow();
    }

    // Activo y dentro de su ventana de venta (sin mirar el cupo).
    public function isOnSaleWindow(){
    	if( !$this->is_active )
    		return false;

    	if( !((bool)strtotime($this->to)) or !((bool)strtotime($this->from)) )
    		return false;

    	$to = strtotime($this->to.' 23:59:59');
    	$from = strtotime($this->from.' 00:00:00');

    	return $to >= time() && $from <= time();
    }

    public function isComplementary(){
    	return $this->kind === self::KIND_COMPLEMENTARY;
    }

    // Unidades de este ticket por compra (configurable por ticket).
    public function maxPerPurchase(): int
    {
    	return max(1, (int) ($this->max_per_purchase ?? 1));
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
     * Entradas desde el formulario: `entries[n][ticket]` es la entrada
     * principal y `entries[n][items][id]` las unidades de cada complementario
     * de esa entrada. Devuelve [['ticket' => id, 'items' => [id => qty]], ...].
     */
    public static function entriesFromInput($entries): array
    {
        $result = [];
        foreach ((array) $entries as $entry) {
            $ticketId = (int) ($entry['ticket'] ?? 0);
            if ($ticketId < 1) {
                continue;
            }
            $items = [];
            foreach ((array) ($entry['items'] ?? []) as $id => $qty) {
                if ((int) $id > 0 && (int) $qty > 0) {
                    $items[(int) $id] = (int) $qty;
                }
            }
            $result[] = ['ticket' => $ticketId, 'items' => $items];
        }
        return $result;
    }

    // Carrito [ticket_id => cantidad] que suman las entradas.
    public static function cartFromEntries(array $entries): array
    {
        $cart = [];
        foreach ($entries as $entry) {
            $cart[$entry['ticket']] = ($cart[$entry['ticket']] ?? 0) + 1;
            foreach ($entry['items'] as $id => $qty) {
                $cart[$id] = ($cart[$id] ?? 0) + $qty;
            }
        }
        return $cart;
    }

    /**
     * Monto y disponibilidad de una compra. `ids` son los tickets distintos y
     * `quantities` la cantidad de cada uno. No disponible si algún ticket no
     * es del evento, no está a la venta o no le alcanzan los disponibles.
     *
     * Con `entries` (evento con complementarios por entrada) el carrito sale
     * de las entradas, y además cada entrada debe ser un ticket principal, sus
     * complementarios deben estar permitidos para ella, y el máximo por compra
     * cuenta entradas (principal) o unidades por entrada (complementario).
     */
    public function getTicketToBuy($event_id, $tickets, $quantities = [], $entries = null)
    {
        $entries = $entries === null ? null : self::entriesFromInput($entries);
        $cart = $entries ? self::cartFromEntries($entries) : self::cartFromInput($tickets, $quantities);
        $amount = 0;

        $ticketsToBuy = new \stdClass();
        $ticketsToBuy->ids = array_keys($cart);
        $ticketsToBuy->quantities = $cart;
        $ticketsToBuy->entries = $entries ?: null;

        $data = $this
            ->select('id', 'price', 'stock', 'capacity', 'max_per_purchase', 'is_active', 'kind', 'to', 'from')
            ->with('allowedWith:events_tickets.id')
            ->withCount(self::soldCountQuery())
            ->where('event_id', $event_id)
            ->whereIn('id', array_keys($cart))->get()->keyBy('id');

        $available = !empty($cart) && $data->count() === count($cart);

        foreach ($data as $ticket) {
            $exceedsMax = $entries ? false : $cart[$ticket->id] > $ticket->maxPerPurchase();
            if (!$ticket->isAvailable() || $ticket->availableCount() < $cart[$ticket->id] || $exceedsMax) {
                $available = false;
                break;
            }
            $amount += $ticket->price * $cart[$ticket->id];
        }

        if ($available && $entries && !self::entriesAreValid($entries, $data)) {
            $available = false;
        }

        $ticketsToBuy->amount = $amount;
        $ticketsToBuy->available = $available;

        return $ticketsToBuy;
    }

    private static function entriesAreValid(array $entries, $tickets): bool
    {
        $perPrincipal = [];
        foreach ($entries as $entry) {
            $principal = $tickets[$entry['ticket']] ?? null;
            if (!$principal || $principal->isComplementary()) {
                return false;
            }
            $perPrincipal[$principal->id] = ($perPrincipal[$principal->id] ?? 0) + 1;

            foreach ($entry['items'] as $id => $qty) {
                $item = $tickets[$id] ?? null;
                if (!$item || !$item->isComplementary() || $qty > $item->maxPerPurchase() || !$item->allowsPrincipal($principal->id)) {
                    return false;
                }
            }
        }

        foreach ($perPrincipal as $id => $count) {
            if ($count > $tickets[$id]->maxPerPurchase()) {
                return false;
            }
        }
        return true;
    }

    public function allowsPrincipal(int $principalId): bool
    {
        return $this->allowedWith->isEmpty() || $this->allowedWith->contains('id', $principalId);
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
