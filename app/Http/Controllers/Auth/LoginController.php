<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\LoginTrace;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Handle a login request to the application.
     */
    public function login(Request $request)
    {
        LoginTrace::traceId($request);
        LoginTrace::info('controller:login:start', [], $request);

        $this->validateLogin($request);
        LoginTrace::info('controller:login:validated', [], $request);

        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            LoginTrace::info('controller:login:lockout', [], $request);
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            LoginTrace::info('controller:login:attempt_success', [], $request);

            if ($request->hasSession()) {
                $request->session()->put('auth.password_confirmed_at', time());
            }

            return $this->sendLoginResponse($request);
        }

        LoginTrace::info('controller:login:attempt_failed', [], $request);
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Block login for inactive accounts before password check.
     */
    protected function attemptLogin(Request $request)
    {
        LoginTrace::info('controller:attemptLogin:start', [], $request);

        $email = strtolower(trim((string) $request->input($this->username())));

        LoginTrace::info('controller:attemptLogin:db_lookup_start', [
            'email' => LoginTrace::maskEmail($email),
        ], $request);

        $user = User::where('email', $email)->first();

        LoginTrace::info('controller:attemptLogin:db_lookup_done', [
            'user_found' => $user !== null,
            'user_id' => $user?->userId ?? $user?->id,
            'is_active' => $user?->isAccountActive(),
        ], $request);

        if ($user && ! $user->isAccountActive()) {
            LoginTrace::info('controller:attemptLogin:inactive_account', [], $request);

            return false;
        }

        LoginTrace::info('controller:attemptLogin:guard_attempt_start', [], $request);

        $result = $this->guard()->attempt(
            $this->credentials($request),
            $request->boolean('remember')
        );

        LoginTrace::info('controller:attemptLogin:guard_attempt_done', [
            'success' => (bool) $result,
        ], $request);

        return $result;
    }

    /**
     * The user has been authenticated.
     */
    protected function authenticated(Request $request, $user)
    {
        LoginTrace::info('controller:authenticated', [
            'user_id' => $user->userId ?? $user->id ?? null,
            'email' => LoginTrace::maskEmail($user->email ?? null),
        ], $request);
    }

    /**
     * Send the response after the user was authenticated.
     */
    protected function sendLoginResponse(Request $request)
    {
        LoginTrace::info('controller:sendLoginResponse:start', [], $request);

        $request->session()->regenerate();

        LoginTrace::info('controller:sendLoginResponse:session_regenerated', [
            'new_session_id' => $request->session()->getId(),
        ], $request);

        $this->clearLoginAttempts($request);

        if ($response = $this->authenticated($request, $this->guard()->user())) {
            LoginTrace::info('controller:sendLoginResponse:custom_response', [], $request);

            return $response;
        }

        LoginTrace::info('controller:sendLoginResponse:redirect', [
            'redirect_to' => $this->redirectPath(),
        ], $request);

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Show a clear message when the account exists but is inactive.
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        LoginTrace::info('controller:sendFailedLoginResponse:start', [], $request);

        $email = strtolower(trim((string) $request->input($this->username())));
        $user = User::where('email', $email)->first();

        if ($user && ! $user->isAccountActive()) {
            LoginTrace::info('controller:sendFailedLoginResponse:inactive', [], $request);

            throw ValidationException::withMessages([
                $this->username() => ['This user account is not active. Please contact your administrator.'],
            ]);
        }

        LoginTrace::info('controller:sendFailedLoginResponse:invalid_credentials', [], $request);

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }
}
