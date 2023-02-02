<?php
namespace Masso;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'logs';
    protected $fillable = ['user_id', 'area', 'module', 'action'];
    protected $primaryKey = 'id';

    public function user()    {
        return $this->belongsTo('Masso\User', 'user_id', 'id')->withTrashed();
    }
}
