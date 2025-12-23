<?php
namespace Masso\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class AdminController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    protected $user;

    public function __construct(Request $request)
    {
        $this->middleware(function ($req, $next) {
            $route = $this->route = $req->route() ? $req->route()->getName() : '';

            $authUser = $this->user = Auth::user();

            \View::share('authUser', $authUser);
            \View::share('title', trans('titles.' . str_replace('.', '_', $route)));
            \View::share('croute', $route);

            return $next($req);
        });
    }
}
