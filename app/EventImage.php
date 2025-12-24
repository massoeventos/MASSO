<?php
namespace Masso;

use Illuminate\Database\Eloquent\Model;

class EventImage extends Model
{
    protected $table = 'event_images';

    protected $fillable = [
        'event_id',
        'path',
        'type',
        'position',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
