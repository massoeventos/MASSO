<?php

namespace Masso\Http\Middleware;

use Closure;
use Illuminate\Routing\Route;

class IsAdmin
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

        $user = $request->user();

        if( $user->isAdmin() ) 
            return $next($request);

        return redirect()->route('home.index');
    

    }
}
