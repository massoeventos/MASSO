<?php
namespace Masso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Event extends Model
{

    use SoftDeletes;

    protected $table = 'events';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'name_eng',
        'slug',
        'location',
        'date_init',
        'date_finish',
        'description',
        'photo',
        'status',
        'description_eng',
        'isUC',
        'organize',
        'is_multiple_selection_ticket',
        'max_selection_ticket',
        'terms_and_conditions',
        'terms_and_conditions_eng',
        'show_location_fields',
        'allow_bank_transfer',
    ];

    protected $casts = [
        'allow_bank_transfer' => 'boolean',
    ];
    public static $months = ['Sep' => 'Septiembre', 'Oct' => 'Octubre', 'Nov' => 'Noviembre', 'Dec' => 'Diciembre', 'Jan' => 'Enero', 'Feb' => 'Febrero', 'Mar' => 'Marzo', 'May' => 'Mayo', 'Apr' => 'Abril', 'Jun' => 'Junio', 'Jul' => 'Julio', 'Aug' => 'Agosto'];


    /**
     * Nombre según idioma de la vista pública ('esp' | 'eng').
     * Si no hay nombre en inglés, se usa el nombre en español.
     */
    public function displayName($lang = 'esp')
    {
        return ($lang == 'eng' && !empty($this->name_eng)) ? $this->name_eng : $this->name;
    }

    public function files()
    {
        return $this->hasMany('Masso\EventFile', 'event_id', 'id');
    }

    public function tickets()
    {
        return $this->hasMany('Masso\EventTicket', 'event_id', 'id')->orderBy('position')->orderBy('id');
    }

    public function ticketCategories()
    {
        return $this->hasMany('Masso\EventTicketCategory', 'event_id', 'id')->orderBy('position')->orderBy('id');
    }

    public function inputs()
    {
        return $this->hasMany('Masso\EventInput', 'event_id', 'id');
    }

    public function assistants()
    {
        return $this->hasMany('Masso\EventEnroll', 'event_id', 'id');
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }

    public function images()
    {
        return $this->hasMany(EventImage::class);
    }

    public function bannerImage()
    {
        return $this->images()->where('type', 'banner')->orderBy('position')->first();
    }

    public function footerImages()
    {
        return $this->images()->where('type', 'footer')->orderBy('position')->get();
    }

    public function hasTicketsAvailables()
    {
        foreach ($this->tickets()->withCount(EventTicket::soldCountQuery())->get() as $ticket)
            if ($ticket->isAvailable())
                return true;

        return false;
    }

    /**
     * Tickets agrupados como se muestran al público (ficha del evento y
     * checkout): un grupo por categoría activa con tickets, más un grupo sin
     * categoría ('category' => null) para los tickets que no tienen ninguna.
     * Un ticket en varias categorías aparece en cada una; uno cuyas
     * categorías están todas apagadas no aparece.
     *
     * $onlyAvailable: true = solo los que están a la venta (checkout);
     * false = todos los activos, incluidos agotados o fuera de fecha (ficha).
     */
    public function ticketGroups(bool $onlyAvailable = true, bool $withSoldOut = false): array
    {
        $tickets = $this->tickets()
            ->with('categories', 'allowedWith:events_tickets.id')
            ->withCount(EventTicket::soldCountQuery())
            ->get()
            ->filter(fn ($t) => $onlyAvailable ? ($t->isAvailable() || ($withSoldOut && $t->isSoldOut())) : (bool) $t->is_active);

        $groups = [];
        foreach ($this->ticketCategories()->where('is_active', true)->get() as $category) {
            $categoryTickets = $tickets->filter(fn ($t) => $t->categories->contains('id', $category->id))->values();
            if ($categoryTickets->isNotEmpty()) {
                $groups[] = ['category' => $category, 'tickets' => $categoryTickets];
            }
        }

        $uncategorized = $tickets->filter(fn ($t) => $t->categories->isEmpty())->values();
        if ($uncategorized->isNotEmpty()) {
            $groups[] = ['category' => null, 'tickets' => $uncategorized];
        }

        return $groups;
    }


    /**
     * Compra por entradas: el evento vende entradas principales y
     * complementarios, y cada complementario se elige dentro de una entrada
     * (2 × Congreso, cada uno con sus talleres). Sin alguno de los dos tipos
     * a la venta, el checkout es la lista simple de siempre.
     */
    public function usesEntries(): bool
    {
        $kinds = $this->tickets()->get()->filter(fn ($t) => $t->isOnSaleWindow())->pluck('kind')->unique();
        return $kinds->contains(EventTicket::KIND_COMPLEMENTARY) && $kinds->contains(fn ($kind) => $kind !== EventTicket::KIND_COMPLEMENTARY);
    }


    public function checkSlug()
    {

        if (empty($this->slug)):
            $baseSlug = Str::slug($this->name);
            $this->slug = $baseSlug . '-' . $this->id;
            $this->save();
        endif;

    }

    public function getDateString()
    {

        $tinit = strtotime($this->date_init);
        $tfinish = strtotime($this->date_finish);
        $datediff = $tfinish - $tinit;
        $day = '';

        if ($tinit == $tfinish)
            $day = date('d', $tinit);
        elseif ($datediff == 86400)
            $day = date('d', $tinit) . ' y ' . date('d', $tfinish);
        else
            $day = date('d', $tinit) . ' - ' . date('d', $tfinish);

        if (date('M', $tinit) == date('M', $tfinish))
            return $day . ' de ' . self::$months[date('M', $tinit)] . ' de ' . date('Y', $tinit);
        else
            return date('d', $tinit) . ' de ' . self::$months[date('M', $tinit)] . ' al ' . date('d', $tfinish) . ' de ' . self::$months[date('M', $tfinish)] . ' de ' . date('Y', $tfinish);
    }


    public function getFinishString($type = '')
    {
        $tfinish = strtotime($this->date_finish);

        if ($type == 'short')
            return date('d', $tfinish) . ' ' . self::$months[date('M', $tfinish)] . ', ' . date('Y', $tfinish);

        return date('d', $tfinish) . ' de ' . self::$months[date('M', $tfinish)] . ' de ' . date('Y', $tfinish);
    }


    public function getInitString($type = '')
    {
        $tinit = strtotime($this->date_init);

        if ($type == 'short')
            return date('d', $tinit) . ' ' . self::$months[date('M', $tinit)] . ', ' . date('Y', $tinit);

        return date('d', $tinit) . ' de ' . self::$months[date('M', $tinit)] . ' de ' . date('Y', $tinit);
    }


    public function getInitInscriptionString($type = '')
    {
        $inscription_init = strtotime($this->inscription_init);

        if ($type == 'short')
            return date('d', $inscription_init) . ' ' . Event::$months[date('M', $inscription_init)] . ', ' . date('Y', $inscription_init);

        return date('d', $inscription_init) . ' de ' . Event::$months[date('M', $inscription_init)] . ' de ' . date('Y', $inscription_init);
    }

    public function hasEarlyBird()
    {
        return $this->early_bird == 0 ? '<label class="label label-xs label-danger"><i class="fa fa-times"></i></label>' : '<label class="label label-xs label-success"><i class="fa fa-check"></i></label>';
    }


    public function isVisible()
    {
        return $this->status == 0 ? '<label class="label label-xs label-danger"><i class="fa fa-times"></i></label>' : '<label class="label label-xs label-success"><i class="fa fa-check"></i></label>';
    }

    public function setMaxSelectionTicketAttribute($value)
    {
        $this->attributes['max_selection_ticket'] = is_null($value) ? 1 : $value;
    }

    /**
     * Alcance para eventos ya expirados (fecha de término en el pasado).
     */
    public function scopeExpired($query)
    {
        return $query->whereDate('date_finish', '<', Carbon::now());
    }
}
