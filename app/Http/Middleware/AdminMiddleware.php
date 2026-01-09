<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
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
        $user = Auth::user();
        
        // Check if the user is authenticated and has one of the allowed user types
        if (!$user || !in_array($user->user_type, [1, 2, 3, 4])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        return $next($request);
    }
    
}
