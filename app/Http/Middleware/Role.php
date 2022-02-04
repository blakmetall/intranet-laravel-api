<?php

namespace App\Http\Middleware;

use Auth;
use Closure;

class Role
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        $rol_id = '';
        switch($role){
            case 'super': $rol_id = 1; break;
            case 'admin': $rol_id = 2; break;
            case 'corporative': $rol_id = 3; break;
            case 'regular': $rol_id = 4; break;
        }

        if($rol_id){
            if ( Auth::user()->rol_id == $rol_id ) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized action.');
    }
}
