<?php

namespace App\Http\Middleware;

use App\Support\LoginTrace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs POST /login before StartSession (detect session lock / early stalls).
 */
class LogLoginPostEntry
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! LoginTrace::isLoginPost($request)) {
            return $next($request);
        }

        LoginTrace::start($request);
        LoginTrace::info('request:received_before_session', LoginTrace::requestContext($request), $request);

        return $next($request);
    }
}
