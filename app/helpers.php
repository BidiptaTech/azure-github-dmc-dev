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
    