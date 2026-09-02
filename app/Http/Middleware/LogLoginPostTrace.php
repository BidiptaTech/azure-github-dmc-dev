<?php

namespace App\Http\Middleware;

use App\Support\LoginTrace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Logs POST /login after session + CSRF middleware (full request lifecycle timing).
 */
class LogLoginPostTrace
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! LoginTrace::isLoginPost($request)) {
            return $next($request);
        }

        LoginTrace::traceId($request);
        LoginTrace::info('request:after_session_middleware', LoginTrace::requestContext($request), $request);

        try {
            $response = $next($request);

            LoginTrace::info('request:completed', [
                'status' => $response->getStatusCode(),
            ], $request);

            return $response;
        } catch (Throwable $exception) {
            LoginTrace::info('request:exception', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ], $request);

            throw $exception;
        } finally {
            LoginTrace::reset();
        }
    }
}
