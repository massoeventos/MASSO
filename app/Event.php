<?php
namespace Masso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{

    use SoftDeletes;

    protected $table = 'events';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
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
        'terms_and_conditions_eng'
    ];
    public static $months = ['Sep' => 'Septiembre', 'Oct' => 'Octubre', 'Nov' => 'Noviembre', 'Dec' => 'Diciembre', 'Jan' => 'Enero', 'Feb' => 'Febrero', 'Mar' => 'Marzo', 'May' => 'Mayo', 'Apr' => 'Abril', 'Jun' => 'Junio', 'Jul' => 'Julio', 'Aug' => 'Agosto'];


    public function files()
    {
        return $this->hasMany('Masso\EventFile', 'event_id', 'id');
    }

    public function tickets()
    {
        return $this->hasMany('Masso\EventTicket', 'event_id', 'id');
    }

    public function inputs()
    {
        return $this->hasMany('Masso\EventInput', 'event_id', 'id');
    }

    public function assistants()
    {
        return $this->hasMany('Masso\EventEnroll', 'event_id', 'id');
    }

    public function hasTicketsAvailables()
    {

        if ($this->tickets()->count() > 0)
            foreach ($this->tickets as $ticket)
                if ($ticket->isAvailable())
                    return true;

        return false;
    }


    public function checkSlug()
    {

        if (empty($this->slug)):
            $this->slug = str_slug($this->name);
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
            return $day . ' de ' . EventExpired::$months[date('M', $tinit)] . ' de ' . date('Y', $tinit);
        else
            return date('d', $tinit) . ' de ' . EventExpired::$months[date('M', $tinit)] . ' al ' . date('d', $tfinish) . ' de ' . EventExpired::$months[date('M', $tfinish)] . ' de ' . date('Y', $tfinish);
    }


    public function getFinishString($type = '')
    {
        $tfinish = strtotime($this->date_finish);

        if ($type == 'short')
            return date('d', $tfinish) . ' ' . EventExpired::$months[date('M', $tfinish)] . ', ' . date('Y', $tfinish);

        return date('d', $tfinish) . ' de ' . EventExpired::$months[date('M', $tfinish)] . ' de ' . date('Y', $tfinish);
    }


    public function getInitString($type = '')
    {
        $tinit = strtotime($this->date_init);

        if ($type == 'short')
            return date('d', $tinit) . ' ' . EventExpired::$months[date('M', $tinit)] . ', ' . date('Y', $tinit);

        return date('d', $tinit) . ' de ' . EventExpired::$months[date('M', $tinit)] . ' de ' . date('Y', $tinit);
    }


    public function getInitInscriptionString($type = '')
    {
        $inscription_init = strtotime($this->inscription_init);

        if ($type == 'short')
            return date('d', $inscription_init) . ' ' . EventExpired::$months[date('M', $inscription_init)] . ', ' . date('Y', $inscription_init);

        return date('d', $inscription_init) . ' de ' . EventExpired::$months[date('M', $inscription_init)] . ' de ' . date('Y', $inscription_init);
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
}
