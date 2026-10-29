<?php
namespace Masso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventTicketCategory extends Model
{

    use SoftDeletes;

    protected $table = 'event_ticket_categories';
    protected $fillable = [
        'event_id',
        'name',
        'name_eng',
        'position',
        'is_active',
    ];
    protected $primaryKey = 'id';

    public function event(){
        return $this->belongsTo('Masso\Event', 'event_id', 'id');
    }

    // Un ticket puede estar en varias categorías (pivote event_ticket_category_ticket).
    public function tickets(){
        return $this->belongsToMany('Masso\EventTicket', 'event_ticket_category_ticket', 'category_id', 'ticket_id')
            ->orderBy('events_tickets.position')->orderBy('events_tickets.id');
    }
}
