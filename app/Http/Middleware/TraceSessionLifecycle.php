<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Temporary session/CSRF lifecycle tracer.
 * Enable with SESSION_TRACE=true in .env.
 * Must be registered in bootstrap/app.php (Laravel 11), not Kernel.php groups.
 */
class TraceSessionLifecycle
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->enabled()) {
            return $next($request);
        }

        $cookieName = (string) config('session.cookie');
        $this->writeTrace('request_start', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'host' => $request->getHost(),
            'scheme' => $request->getScheme(),
            'configured_session_cookie' => $cookieName,
            'incoming_session_cookie_present' => $request->cookies->has($cookieName),
            'incoming_laravel_session_present' => $request->cookies->has('laravel_session'),
            'request_cookies' => array_keys($request->cookies->all()),
            'has_remember_cookie' => (bool) collect($request->cookies->all())
                ->keys()
                ->first(fn ($name) => str_starts_with((string) $name, 'remember_web_')),
            'posted_csrf_token' => $request->input('_token'),
            'session_driver' => config('session.driver'),
            'session_domain' => config('session.domain'),
            'session_secure' => config('session.secure'),
            'session_same_site' => config('session.same_site'),
            'app_url' => config('app.url'),
        ]);

        $response = null;
        $exception = null;

        try {
            $response = $next($request);
        } catch (Throwable $e) {
            $exception = $e;
            throw $e;
        } finally {
            $sessionId = null;
            $csrf = null;
            try {
                if ($request->hasSession()) {
                    $sessionId = $request->session()->getId();
                    $csrf = $request->session()->token();
                }
            } catch (Throwable $e) {
                // ignore
            }

            $setCookies = [];
            if ($response instanceof Response) {
                foreach ($response->headers->all('set-cookie') as $header) {
                    $setCookies[] = strtok((string) $header, ';');
                }
            }

            $payload = [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'status' => $response?->getStatusCode(),
                'exception' => $exception ? $exception::class : null,
                'session_id' => $sessionId,
                'csrf_token' => $csrf,
                'auth_user_id' => Auth::id(),
                'auth_check' => Auth::check(),
                'posted_csrf_token' => $request->input('_token'),
                'csrf_matches_session' => $request->input('_token')
                    ? hash_equals((string) $csrf, (string) $request->input('_token'))
                    : null,
                'set_cookie_headers' => $setCookies,
                'note' => 'Compare session_id across GET then POST. Encrypted cookie values change every response (new IV).',
            ];

            $this->writeTrace('request_end', $payload);
        }

        return $response;
    }

    private function enabled(): bool
    {
        return filter_var(env('SESSION_TRACE', false), FILTER_VALIDATE_BOOLEAN);
    }

    private function writeTrace(string $phase, array $payload): void
    {
        Log::info('SESSION_TRACE.'.$phase, $payload);
        $line = '['.date('Y-m-d H:i:s')."] SESSION_TRACE.{$phase} ".json_encode($payload).PHP_EOL;
        @file_put_contents(storage_path('logs/session_trace.log'), $line, FILE_APPEND | LOCK_EX);
    }
}
