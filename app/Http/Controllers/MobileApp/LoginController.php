<?php

namespace App\Http\Controllers\MobileApp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Middleware\AppAuthMiddleware;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'error' => 'User not found',
                'message' => 'No account found with this email address'
            ], 404);
        }
        
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'Invalid credentials',
                'message' => 'The provided password is incorrect'
            ], 401);
        }
        
        // Generate custom token using our middleware
        $token = AppAuthMiddleware::generateToken($user);
        
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 86400, // 24 hours in seconds
            'user' => [
                'id' => $user->userId,
                'name' => $user->name,
                'email' => $user->email,
                'role_id' => $user->role_id ?? null
            ]
        ]);
    }

    public function test(Request $request){
        \Log::info('TEST ROUTE HIT - This route was accessed successfully');
        return response()->json([
            'success' => true,
            'message' => 'Mobile app test route working',
            'time' => now(),
            'route_hit' => true,
            'method' => $request->method(),
            'middleware_applied' => 'none - public route'
        ]);
    }
}
