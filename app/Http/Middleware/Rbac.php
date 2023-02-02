<?php

namespace Masso\Http\Middleware;

use Closure;
use Masso\Society;
use Illuminate\Routing\Route;

class Rbac
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $route = $request->route()->getName();

        if( $request->user()->canDo($route) || $request->user()->role_id == 1 ) 
            return $next($request);

        if( $route != 'dashboard.index' )
            $request->session()->flash('error_alert', trans('text.forbidden_route',['route'=>$route]));

        if( \Auth::user() && ( $request->user()->canDo('dashboard.index') || $request->user()->canDo('dashboard.client') ) ):
            return redirect()->route('dashboard.index');
        elseif( \Auth::user() && ( \Auth::user()->role_id == 10) ):
             return redirect()->route('b.pr.index');
        elseif( \Auth::user() && ( \Auth::user()->role_id == 11) ):
             return redirect()->route('b.bo.index');
        elseif( \Auth::user() && ( \Auth::user()->role_id == 5) ):
             return redirect()->route('b.picking.assign.index');
        elseif( \Auth::user() && ( \Auth::user()->role_id == 6 ) ):
             return redirect()->route('b.picking.process.index');
        elseif( \Auth::user() && (\Auth::user()->role_id == 7) ):
             return redirect()->route('b.picking.process.index');
        elseif( \Auth::user() && \Auth::user()->role_id == 4 ):
             return redirect()->route('h.bc.index');                
        endif;

        abort(403);


    }
}
