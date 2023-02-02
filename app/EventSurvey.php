<?php
namespace Masso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventSurvey extends Model
{
	use SoftDeletes;
	
    protected $table = 'events_survey';
    protected $fillable = ['name','date_init','date_finish','description','photo','location'];
    protected $primaryKey = 'id';

    
}
