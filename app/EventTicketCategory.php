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
        'position'
    ];
    protected $primaryKey = 'id';

    public function event(){
        return $this->belongsTo('Masso\Event', 'event_id', 'id');
    }

    public function tickets(){
        return $this->hasMany('Masso\EventTicket', 'category_id', 'id')->orderBy('position')->orderBy('id');
    }
}
