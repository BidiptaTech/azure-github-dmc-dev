<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AppAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for Authorization header
        $authHeader = $request->header('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'error' => 'Authorization token required',
                'message' => 'Please provide a valid Bearer token'
            ], 401);
        }

        // Extract token from Bearer token
        $token = substr($authHeader, 7);
        
        if (empty($token)) {
            return response()->json([
                'error' => 'Invalid token format',
                'message' => 'Token cannot be empty'
            ], 401);
        }

        // Custom token validation logic
        // You can modify this based on your token structure
        $user = $this->validateCustomToken($token);
        
        if (!$user) {
            return response()->json([
                'error' => 'Invalid or expired token',
                'message' => 'Please login again'
            ], 401);
        }

        // Add user to request
        $request->merge(['authenticated_user' => $user]);
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }

    /**
     * Validate custom token and return user
     */
    private function validateCustomToken($token)
    {
        try {
            // Step 1: Base64 decode
            $decoded = base64_decode($token);
            \Log::info('Token decoded', ['token' => $token, 'decoded' => $decoded]);
            
            if (!$decoded) {
                \Log::error('Failed to decode base64 token');
                return null;
            }

            // Step 2: JSON decode
            $userData = json_decode($decoded, true);
            \Log::info('JSON decoded', ['userData' => $userData]);
            
            if (!$userData) {
                \Log::error('Failed to decode JSON from token');
                return null;
            }

            // Step 3: Check required fields
            if (!isset($userData['user_id']) || !isset($userData['timestamp'])) {
                \Log::error('Missing required fields in token', ['userData' => $userData]);
                return null;
            }

            // Step 4: Check token expiration
            $tokenAge = time() - $userData['timestamp'];
            if ($tokenAge > 86400) { // 24 hours = 86400 seconds
                \Log::error('Token expired', ['tokenAge' => $tokenAge, 'timestamp' => $userData['timestamp']]);
                return null;
            }

            // Step 5: Find user
            $user = User::find($userData['user_id']);
            \Log::info('User lookup', ['user_id' => $userData['user_id'], 'found' => $user ? true : false]);
            
            if (!$user) {
                \Log::error('User not found', ['user_id' => $userData['user_id']]);
                return null;
            }

            // Step 6: Validate email
            $emailMatch = $user->email === $userData['email'];
            \Log::info('Email validation', ['user_email' => $user->email, 'token_email' => $userData['email'], 'match' => $emailMatch]);
            
            return $emailMatch ? $user : null;
            
        } catch (\Exception $e) {
            \Log::error('Token validation exception', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return null;
        }
    }

    /**
     * Generate custom token for user (to be used in login)
     */
    public static function generateToken($user)
    {
        $tokenData = [
            'user_id' => $user->userId,
            'email' => $user->email,
            'timestamp' => time()
        ];
        
        return base64_encode(json_encode($tokenData));
    }
} 