<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
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
     * Block login for inactive accounts before password check.
     */
    protected function attemptLogin(Request $request)
    {
        $email = strtolower(trim((string) $request->input($this->username())));
        $user = User::where('email', $email)->first();

        if ($user && !$user->isAccountActive()) {
            return false;
        }

        return $this->guard()->attempt(
            $this->credentials($request),
            $request->boolean('remember')
        );
    }

    /**
     * Show a clear message when the account exists but is inactive.
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $email = strtolower(trim((string) $request->input($this->username())));
        $user = User::where('email', $email)->first();

        if ($user && !$user->isAccountActive()) {
            throw ValidationException::withMessages([
                $this->username() => ['This user account is not active. Please contact your administrator.'],
            ]);
        }

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }
}
