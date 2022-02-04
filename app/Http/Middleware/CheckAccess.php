<?php

namespace App\Http\Middleware;

use Auth;
use Closure;

class CheckAccess
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
        if($request->route()->getPrefix() == 'users'){
            return $next($request);
        }else{
            abort(403, 'Unauthorized action.');
        }
        /*
        if ( Auth::user()->rol_id == $rol_id ) {
        }

        */
    }
}
