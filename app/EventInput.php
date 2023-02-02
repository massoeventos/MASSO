<?php
namespace Masso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventInput extends Model
{

	use SoftDeletes; 
	
    protected $table = 'events_inputs';
    protected $fillable = ['event_id','name', 'name_eng','type','required'];
    protected $primaryKey = 'id';

    
}
