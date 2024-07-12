<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

class AdminMiddleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        if($user == null){
            return response(['status' => 'error', 'message' => 'unauthorized'], 401);
        }else if($user->guard != 'admin'){
            return response(['status' => 'error', 'message' => 'unauthorized'], 401);
        }
        return $next($request);
    }
}
