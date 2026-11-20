<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts access to Super Admin only.
 *
 * Matches the established project convention (UserController):
 * user_type === 1 AND role_id === 1.
 */
class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || (int) ($user->user_type ?? 0) !== 1 || (int) ($user->role_id ?? 0) !== 1) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
