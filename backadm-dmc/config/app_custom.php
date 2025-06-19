<?php
// Custom configuration for subdirectory deployment

// Set the correct asset URL for subdirectory
if (!function_exists('custom_asset')) {
    function custom_asset($path) {
        return config('app.asset_url', config('app.url')) . '/' . ltrim($path, '/');
    }
}

// Override Laravel's asset helper
if (!function_exists('asset_override')) {
    function asset_override($path) {
        // For subdirectory deployment, ensure assets include the subdirectory path
        $baseUrl = config('app.asset_url') ?: config('app.url');
        
        // If we're in a subdirectory, make sure the path includes it
        if (strpos($baseUrl, '/backadm-dmc') !== false) {
            return $baseUrl . '/' . ltrim($path, '/');
        }
        
        return $baseUrl . '/' . ltrim($path, '/');
    }
}
