<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LoginTrace
{
    private static ?string $traceId = null;

    private static float $startedAt = 0.0;

    public static function start(?Request $request = null): string
    {
        if (self::$traceId !== null) {
            return self::$traceId;
        }

        self::$traceId = (string) Str::uuid();
        self::$startedAt = microtime(true);

        if ($request !== null) {
            $request->attributes->set('login_trace_id', self::$traceId);
        }

        return self::$traceId;
    }

    public static function traceId(Request $request): string
    {
        $existing = $request->attributes->get('login_trace_id');

        if (is_string($existing) && $existing !== '') {
            self::$traceId = $existing;

            return $existing;
        }

        return self::start($request);
    }

    public static function reset(): void
    {
        self::$traceId = null;
        self::$startedAt = 0.0;
    }

    public static function isLoginPost(Request $request): bool
    {
        if (! $request->isMethod('POST')) {
            return false;
        }

        if ($request->routeIs('login')) {
            return true;
        }

        return str_ends_with(trim($request->path(), '/'), 'login');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public static function info(string $step, array $context = [], ?Request $request = null): void
    {
        if ($request !== null) {
            self::traceId($request);
        }

        $payload = array_merge([
            'trace_id' => self::$traceId,
            'step' => $step,
            'elapsed_ms' => self::$startedAt > 0
                ? round((microtime(true) - self::$startedAt) * 1000, 2)
                : null,
        ], $context);

        Log::channel('login_trace')->info('[LoginTrace] '.$step, $payload);
    }

    /**
     * @return array<string, mixed>
     */
    public static function requestContext(Request $request): array
    {
        return [
            'method' => $request->method(),
            'path' => $request->path(),
            'full_url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'has_session' => $request->hasSession(),
            'session_id' => $request->hasSession() ? $request->session()->getId() : null,
            'has_csrf_field' => $request->has('_token'),
            'has_remember' => $request->boolean('remember'),
            'email' => self::maskEmail($request->input('email')),
        ];
    }

    public static function maskEmail(mixed $email): ?string
    {
        $email = strtolower(trim((string) $email));

        if ($email === '' || ! str_contains($email, '@')) {
            return $email !== '' ? '[invalid-email]' : null;
        }

        [$local, $domain] = explode('@', $email, 2);
        $visible = substr($local, 0, min(2, strlen($local)));

        return $visible.'***@'.$domain;
    }
}
