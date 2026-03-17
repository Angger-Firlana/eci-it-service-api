<?php

namespace App\Http\Middleware;

use App\Helpers\APIResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $guard = auth('sanctum');

        if(!$guard->check()){
            return APIResponse::error(null, 401, 'Unauthorized');
        }

        $user = $guard->user();
        
        foreach($roles as $role){
            if($user->roles->contains('name', $role)){
                return $next($request);
            }
        }
        
        return APIResponse::error(null, 403, 'User is not authorized');
    }
}
