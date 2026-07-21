<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Force logout when an authenticated user's account has been deactivated.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        // Never invalidate session on auth entry points; CSRF already passed
        // and regenerating here can strand the next form render.
        if ($request->routeIs('login', 'logout', 'register', 'password.*')) {
            return $next($request);
        }

        $authUser = Auth::user();
        $userId = $authUser->userId ?? $authUser->id ?? null;

        if (!$userId) {
            return $next($request);
        }

        $freshUser = User::where('userId', $userId)->first();

        if ($freshUser && $freshUser->isAccountActive()) {
            return $next($request);
        }

        Auth::logout();

        Session::forget('impersonate');
        Session::forget('login_stack');

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $message = 'Your account has been deactivated. Please contact your administrator.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'redirect' => route('login'),
            ], 401);
        }

        return redirect()->route('login')
            ->withErrors(['email' => $message]);
    }
}
