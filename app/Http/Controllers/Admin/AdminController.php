<?php
namespace Masso\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class AdminController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    protected $user;

    public function __construct( Request $request ){

        $route = $this->route = '';
        if(!is_null($request->route()) )
            $route = $this->route = $request->route()->getName();

        \View::share('authUser', $authUser = $this->user = \Auth::user());
        \View::share('title', trans('titles.'.str_replace('.', '_', $route)));

		\View::share('croute', $route);


    }
}
