<?php
namespace Masso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class TeamMember extends Model
{
	use SoftDeletes;
	
    protected $table = 'team_members';
    protected $fillable = ['name','image','description'];
    protected $primaryKey = 'id';

}
