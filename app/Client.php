<?php
namespace Masso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{

	use SoftDeletes;
	
    protected $table = 'clients';
    protected $fillable = ['rut', 'name', 'email', 'comments', 'country'];
    protected $primaryKey = 'id';

    public function user()    {
        return $this->belongsTo('Masso\User', 'user_id', 'id')->withTrashed();
    }
}
