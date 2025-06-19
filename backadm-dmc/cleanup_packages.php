<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Package;

echo "Cleaning up corrupted package data...\n";

$packages = Package::all();

foreach ($packages as $package) {
    $updated = false;
    
    // Clean selected_hotels
    if ($package->selected_hotels) {
        $cleanHotels = [];
        foreach ($package->selected_hotels as $hotel) {
            if (isset($hotel['name']) && isset($hotel['country'])) {
                // Use hotel name as ID if the ID is corrupted
                $cleanHotels[] = [
                    'id' => isset($hotel['id']) ? preg_replace('/[^a-zA-Z0-9\-_]/', '', $hotel['id']) : $hotel['name'],
                    'name' => $hotel['name'],
                    'country' => $hotel['country']
                ];
            }
        }
        if ($cleanHotels !== $package->selected_hotels) {
            $package->selected_hotels = $cleanHotels;
            $updated = true;
        }
    }
    
    // Clean selected_attractions
    if ($package->selected_attractions) {
        $cleanAttractions = [];
        foreach ($package->selected_attractions as $attraction) {
            if (isset($attraction['name']) && isset($attraction['country'])) {
                // Use attraction name as ID if the ID is corrupted
                $cleanAttractions[] = [
                    'id' => isset($attraction['id']) ? preg_replace('/[^a-zA-Z0-9\-_]/', '', $attraction['id']) : $attraction['name'],
                    'name' => $attraction['name'],
                    'country' => $attraction['country']
                ];
            }
        }
        if ($cleanAttractions !== $package->selected_attractions) {
            $package->selected_attractions = $cleanAttractions;
            $updated = true;
        }
    }
    
    if ($updated) {
        $package->save();
        echo "Cleaned package ID: {$package->id} - {$package->title}\n";
    }
}

echo "Cleanup completed!\n"; 