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

if (!function_exists('resolveBookingZoneLabel')) {
    /**
     * Resolve a zone label for booking transport display.
     * Only queries zones when zone_id is a pure integer — location keys
     * (hotel_unique_id, attraction_id, etc.) must not hit the zones table.
     *
     * @param  mixed  $zoneId
     * @param  string|null  $fallback
     * @return string|null
     */
    function resolveBookingZoneLabel($zoneId, $fallback = null)
    {
        if (is_array($zoneId) || is_object($zoneId)) {
            return $fallback;
        }
        $raw = trim((string) $zoneId);
        if ($raw === '' || $raw === '0' || strtolower($raw) === 'null' || strtolower($raw) === 'undefined') {
            return $fallback;
        }
        // Postgres integer column — reject hotel unique ids like "97b49d6.67226352"
        if (!preg_match('/^\d+$/', $raw)) {
            return $fallback;
        }
        try {
            $zone = DB::table('zones')->where('zone_id', (int) $raw)->first();
            if ($zone) {
                $label = trim((string) ($zone->zone_type ?? ''));
                if ($label === '') {
                    $label = trim((string) ($zone->zone_name ?? ''));
                }
                return $label !== '' ? $label : ('Zone ' . $raw);
            }
        } catch (\Throwable $e) {
            return $fallback;
        }

        return $fallback !== null ? $fallback : ('Zone ' . $raw);
    }
}
    