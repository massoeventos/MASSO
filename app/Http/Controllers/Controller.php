<?php
namespace Masso\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct( Request $request ){

        $route = $this->route = '';
        if(!is_null($request->route()) )
            $route = $this->route = $request->route()->getName();

        \View::share('authUser', $authUser = $this->user = \Auth::user());
        \View::share('currentRoute', $route);

    }
}
