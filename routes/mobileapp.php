<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MobileApp\LoginController;
use App\Http\Controllers\UserController;

// Public Routes - No authentication required
Route::post('/login', [LoginController::class, 'login']);
// Protected Routes - Using custom mobileapp auth middleware
Route::middleware('mobileapp')->group(function () {
    Route::post('/profile', function (Request $request) {
        return response()->json([
            'user' => $request->user(),
            'authenticated_user' => $request->input('authenticated_user')
        ]);
    });
    
    Route::get('/dashboard', function (Request $request) {
        return response()->json([
            'message' => 'Welcome to mobile dashboard',
            'user' => $request->user()
        ]);
    });
        
    Route::post('/logout', function (Request $request) {
        return response()->json([
            'message' => 'Successfully logged out from mobile app'
        ]);
    });
});
