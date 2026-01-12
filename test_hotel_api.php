<?php
// Quick test script to check hotel API
// Run this from: http://localhost/azure_new_files/public/test_hotel_api.php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Simulate being logged in as user 8 (role 35)
$user = \App\Models\User::find(8);
if (!$user) {
    die("User 8 not found");
}

// Determine DMC ID
$dmc_id = null;
if ($user->role_id == 11) {
    $dmc_id = $user->userId;
} elseif (in_array($user->role_id, [33, 34, 35, 77, 78, 84, 120, 128, 129, 130, 131, 132, 133, 134, 135, 136, 137, 138, 139, 140])) {
    $dmc_id = $user->created_by;
}

echo "<h2>Hotel API Test</h2>";
echo "<p><strong>User ID:</strong> {$user->userId}</p>";
echo "<p><strong>Role ID:</strong> {$user->role_id}</p>";
echo "<p><strong>DMC ID:</strong> {$dmc_id}</p>";
echo "<hr>";

// Test query for Singapore
$destination = 'Singapore';

echo "<h3>Query for: {$destination}</h3>";

$hotelsQuery = \App\Models\Hotel::where('status', 1)
    ->where('is_active', 1)
    ->where('is_complete', 1)
    ->where('city', $destination);

if ($dmc_id) {
    $hotelsQuery->whereJsonContains('dmc_id', (int) $dmc_id);
}

$hotels = $hotelsQuery->orderBy('name', 'asc')->get();

echo "<p><strong>Hotels Found:</strong> {$hotels->count()}</p>";

if ($hotels->count() > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>City</th><th>DMC IDs</th><th>Status</th><th>Active</th><th>Complete</th></tr>";
    foreach ($hotels as $hotel) {
        echo "<tr>";
        echo "<td>{$hotel->id}</td>";
        echo "<td>{$hotel->name}</td>";
        echo "<td>{$hotel->city}</td>";
        echo "<td>" . json_encode($hotel->dmc_id) . "</td>";
        echo "<td>{$hotel->status}</td>";
        echo "<td>" . ($hotel->is_active ? 'Yes' : 'No') . "</td>";
        echo "<td>" . ($hotel->is_complete ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'><strong>No hotels found!</strong></p>";
    
    // Check without DMC filter
    echo "<h4>Checking ALL hotels in Singapore (no DMC filter):</h4>";
    $allHotels = \App\Models\Hotel::where('status', 1)
        ->where('is_active', 1)
        ->where('is_complete', 1)
        ->where('city', $destination)
        ->get();
    
    echo "<p>Total hotels in Singapore: {$allHotels->count()}</p>";
    
    if ($allHotels->count() > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Name</th><th>DMC IDs</th></tr>";
        foreach ($allHotels as $hotel) {
            $hasDmc = false;
            if (is_array($hotel->dmc_id)) {
                $hasDmc = in_array($dmc_id, $hotel->dmc_id);
            }
            $color = $hasDmc ? 'green' : 'red';
            echo "<tr style='color: {$color}'>";
            echo "<td>{$hotel->id}</td>";
            echo "<td>{$hotel->name}</td>";
            echo "<td>" . json_encode($hotel->dmc_id) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p><em>Green = Has DMC {$dmc_id}, Red = Does NOT have DMC {$dmc_id}</em></p>";
    }
}

