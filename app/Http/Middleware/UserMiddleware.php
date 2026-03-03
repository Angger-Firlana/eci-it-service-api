<?php

namespace App\Http\Middleware;

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
        if(!auth()->check()){
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = auth()->user();
        
        foreach($roles as $role){
            if($user->roles->contains('name', $role)){
                return $next($request);
            }
        }
        
        return response()->json(['message' => 'User is not authorized'], 403);
    }
}
