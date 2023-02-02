<?php
namespace Masso\Http\Controllers\Admin;
use Illuminate\Http\Request;
use Masso\Log;
use Masso\User;

class LogController extends AdminController
{

    public function index(){
        $search = request()->get('search');
        $area = request()->get('areas');
        $module = request()->get('module');
        $user = request()->get('user');

        $logs = Log::orderBy('created_at', 'DESC');

        if( !empty($search) )
            $logs = $logs->orWhere('action', 'LIKE', '%'.$search.'%');
        if( !empty($area) )
            $logs = $logs->orWhere('area', '=', $area);
        if( !empty($module) )
            $logs = $logs->orWhere('action', '=', $module);
        if( !empty($user) )
            $logs = $logs->orWhere('user_id', '=', $user);

        $logs = $logs->paginate(50);

        $users = User::withTrashed()->pluck('name', 'id')->toArray();
        $areas = Log::groupBy('area')->pluck('area','area')->toArray();
        $modules = Log::groupBy('module')->pluck('module','module')->toArray();

        $title = 'Registro de Acciones';
        return view('admin.general.log.index', compact('logs', 'title', 'users', 'areas', 'modules'));

    }

    
}
