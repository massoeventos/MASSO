<?php
namespace Masso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventInputValue extends Model
{
    use SoftDeletes;

    protected $table = 'event_input_values';
    protected $fillable = ['payment_id', 'event_input_id', 'value'];
    protected $primaryKey = 'id';

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'id')->withTrashed();
    }

    public function eventInput()
    {
        return $this->belongsTo(EventInput::class, 'event_input_id', 'id')->withTrashed();
    }
}
