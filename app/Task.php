<?php

namespace Masso;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'tasks';

    protected $fillable = [
        'id',
        'task_name',
        'controller',
        'object_id',
    ];

    protected $primaryKey = 'id';
}
