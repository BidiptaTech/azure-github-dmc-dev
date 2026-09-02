<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request)
    {
        $this->logLogin('start', $request);

        $this->validateLogin($request);
        $this->logLogin('validated', $request);

        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->logLogin('lockout', $request);
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            $this->logLogin('success', $request);

            if ($request->hasSession()) {
                $request->session()->put('auth.password_confirmed_at', time());
            }

            return $this->sendLoginResponse($request);
        }

        $this->logLogin('failed', $request);
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    protected function attemptLogin(Request $request)
    {
        $email = strtolower(trim((string) $request->input($this->username())));

        $this->logLogin('db_lookup', $request, ['email' => $this->maskEmail($email)]);

        $user = User::where('email', $email)->first();

        if ($user && ! $user->isAccountActive()) {
            $this->logLogin('inactive_account', $request, [
                'user_id' => $user->userId ?? $user->id,
            ]);

            return false;
        }

        $result = $this->guard()->attempt(
            $this->credentials($request),
            $request->boolean('remember')
        );

        $this->logLogin('password_check', $request, [
            'user_found' => $user !== null,
            'success' => (bool) $result,
        ]);

        return $result;
    }

    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();
        $this->clearLoginAttempts($request);

        $this->logLogin('redirect', $request, [
            'redirect_to' => $this->redirectPath(),
            'user_id' => $this->guard()->id(),
        ]);

        if ($response = $this->authenticated($request, $this->guard()->user())) {
            return $response;
        }

        return redirect()->intended($this->redirectPath());
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $email = strtolower(trim((string) $request->input($this->username())));
        $user = User::where('email', $email)->first();

        if ($user && ! $user->isAccountActive()) {
            $this->logLogin('inactive_account_response', $request);

            throw ValidationException::withMessages([
                $this->username() => ['This user account is not active. Please contact your administrator.'],
            ]);
        }

        $this->logLogin('invalid_credentials', $request);

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function logLogin(string $step, Request $request, array $extra = []): void
    {
        Log::info('[Login] '.$step, array_merge([
            'ip' => $request->ip(),
            'path' => $request->path(),
            'email' => $this->maskEmail($request->input('email')),
        ], $extra));
    }

    private function maskEmail(mixed $email): ?string
    {
        $email = strtolower(trim((string) $email));

        if ($email === '' || ! str_contains($email, '@')) {
            return null;
        }

        [$local, $domain] = explode('@', $email, 2);

        return substr($local, 0, min(2, strlen($local))).'***@'.$domain;
    }
}
