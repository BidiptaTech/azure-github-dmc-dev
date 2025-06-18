<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

if (!function_exists('hasPermission')) {
    /**
     * Check if the logged-in user has a specific permission.
     *
     * @param string $permissionName
     * @return bool
     */
    function hasPermission($permissionName)
    {
        $user = Auth::user();
        if (!$user) {
            return false; // No user is logged in
        }

        // Fetch permission from the database
        $permission = DB::table('permissions')->where('name', $permissionName)->first();
        if (!$permission) {
            return false; // Permission doesn't exist
        }

        // Decode the feature_roles JSON column
        $allowedRoles = json_decode($permission->feature_roles, true) ?? [];

        // Check if user's role_id exists in allowed roles
        return in_array($user->role_id, $allowedRoles);
    }
}

if (!function_exists('getUserRole')) {
    /**
     * Get the logged-in user's role ID.
     *
     * @return int|null
     */
    function getUserRole()
    {
        return Auth::check() ? Auth::user()->role_id : null;
    }
}

if (!function_exists('subdirectory_asset')) {
    /**
     * Generate asset URL with proper subdirectory handling for Azure deployment
     *
     * @param string $path
     * @return string
     */
    function subdirectory_asset($path)
    {
        // Remove leading slash if present
        $path = ltrim($path, '/');
        
        // In production, use the configured APP_URL which includes subdirectory
        if (config('app.env') === 'production') {
            $baseUrl = rtrim(config('app.url'), '/');
            return $baseUrl . '/' . $path;
        }
        
        // In development, use the standard asset helper
        return asset($path);
    }
}

if (!function_exists('subdirectory_url')) {
    /**
     * Generate URL with proper subdirectory handling for Azure deployment
     *
     * @param string $path
     * @param array $parameters
     * @return string
     */
    function subdirectory_url($path = '', $parameters = [])
    {
        // In production, ensure URLs are properly constructed with subdirectory
        if (config('app.env') === 'production') {
            $baseUrl = rtrim(config('app.url'), '/');
            $path = ltrim($path, '/');
            $url = $baseUrl . ($path ? '/' . $path : '');
            
            if (!empty($parameters)) {
                $url .= '?' . http_build_query($parameters);
            }
            
            return $url;
        }
        
        // In development, use the standard url helper
        return url($path, $parameters);
    }
}
    