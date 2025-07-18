<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomPackageController extends Controller
{
    public function create(){
        // Sample hotels data
        $hotels = [
            (object)[
                'id' => 1, 
                'name' => 'Hotel Boss',
                'location' => 'Little India',
                'star_rating' => 3,
                'description' => 'Little India, 3 Star'
            ],
            (object)[
                'id' => 2, 
                'name' => 'Hotel Marina Bay',
                'location' => 'Marina Bay',
                'star_rating' => 5,
                'description' => 'Marina Bay, 5 Star'
            ],
            (object)[
                'id' => 3, 
                'name' => 'Grand Plaza Hotel',
                'location' => 'Orchard Road',
                'star_rating' => 4,
                'description' => 'Orchard Road, 4 Star'
            ],
            (object)[
                'id' => 4, 
                'name' => 'Raffles Hotel',
                'location' => 'Colonial District',
                'star_rating' => 5,
                'description' => 'Colonial District, 5 Star Luxury'
            ],
        ];

        // Sample meal plans
        $mealPlans = [
            (object)['id' => 1, 'name' => 'BB', 'description' => 'Bed & Breakfast'],
            (object)['id' => 2, 'name' => 'HB', 'description' => 'Half Board'],
            (object)['id' => 3, 'name' => 'FB', 'description' => 'Full Board'],
            (object)['id' => 4, 'name' => 'AI', 'description' => 'All Inclusive'],
        ];

        // Sample room types
        $roomTypes = [
            (object)['id' => 1, 'name' => 'Self Booked', 'description' => 'Customer arranged'],
            (object)['id' => 2, 'name' => 'Single', 'description' => 'Single occupancy'],
            (object)['id' => 3, 'name' => 'Double', 'description' => 'Double occupancy'],
            (object)['id' => 4, 'name' => 'Triple', 'description' => 'Triple occupancy'],
            (object)['id' => 5, 'name' => 'Quad', 'description' => 'Quad occupancy'],
        ];

        // Sample vehicle types
        $vehicleTypes = [
            (object)['id' => 1, 'name' => 'Vehicle - 7/12 Seater', 'capacity' => 12],
            (object)['id' => 2, 'name' => 'Vehicle - 15 Seater', 'capacity' => 15],
            (object)['id' => 3, 'name' => 'Vehicle - 25 Seater', 'capacity' => 25],
            (object)['id' => 4, 'name' => 'Vehicle - 35 Seater', 'capacity' => 35],
            (object)['id' => 5, 'name' => 'Vehicle - 45 Seater', 'capacity' => 45],
        ];

        // Sample service types
        $serviceTypes = [
            (object)['id' => 1, 'name' => 'Arrival - PVT Transfer'],
            (object)['id' => 2, 'name' => 'Departure - PVT Transfer'],
            (object)['id' => 3, 'name' => 'PVT Return Transfer'],
            (object)['id' => 4, 'name' => 'City Tour'],
            (object)['id' => 5, 'name' => 'Half Day Tour'],
            (object)['id' => 6, 'name' => 'Full Day Tour'],
        ];

        // Sample activities
        $activities = [
            (object)[
                'id' => 1,
                'name' => 'Night Safari Admission + Tram Ride',
                'type' => 'Tickets',
                'duration' => '3 Hours',
                'adult_price' => 43,
                'child_price' => 28
            ],
            (object)[
                'id' => 2,
                'name' => 'Singapore Zoo Admission',
                'type' => 'Tickets',
                'duration' => '4 Hours',
                'adult_price' => 39,
                'child_price' => 26
            ],
            (object)[
                'id' => 3,
                'name' => 'Gardens by the Bay + Sky Walk',
                'type' => 'Tickets',
                'duration' => '2 Hours',
                'adult_price' => 35,
                'child_price' => 20
            ],
            (object)[
                'id' => 4,
                'name' => 'Universal Studios Singapore',
                'type' => 'Tickets',
                'duration' => 'Full Day',
                'adult_price' => 81,
                'child_price' => 61
            ],
            (object)[
                'id' => 5,
                'name' => 'Singapore City Tour',
                'type' => 'Package',
                'duration' => '5 Hours',
                'adult_price' => 65,
                'child_price' => 45
            ],
        ];

        // Sample currencies
        $currencies = [
            (object)['id' => 1, 'code' => 'SGD', 'name' => 'Singapore Dollar', 'symbol' => 'S$'],
            (object)['id' => 2, 'code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$'],
            (object)['id' => 3, 'code' => 'EUR', 'name' => 'Euro', 'symbol' => '€'],
            (object)['id' => 4, 'code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹'],
            (object)['id' => 5, 'code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£'],
        ];

        // Sample locations
        $locations = [
            'Changi Airport',
            'Marina Bay Sands',
            'Sentosa Island',
            'Orchard Road',
            'Little India',
            'Chinatown',
            'Clarke Quay',
            'Singapore Zoo',
            'Night Safari',
            'Gardens by the Bay',
            'Universal Studios',
            'Merlion Park',
            'Singapore Flyer',
            'Boat Quay',
            'Bugis Street'
        ];

        // Sample package data for demonstration
        $packageData = (object)[
            'destination' => 'Singapore',
            'start_date' => '2025-10-01',
            'duration' => '3 Nights, 4 Days',
            'nights' => 3,
            'days' => 4,
            'adults' => 16,
            'children' => 0,
            'infants' => 0,
            'rooms' => 8,
            'pax_per_room' => 2,
            'trip_code' => '42143',
            'customer_name' => 'PRIYA',
            'source' => 'SG',
            'agent' => 'TRIPSTARS'
        ];

        return view('custom-packages.create', compact(
            'hotels', 
            'mealPlans', 
            'roomTypes', 
            'vehicleTypes', 
            'serviceTypes', 
            'activities', 
            'currencies', 
            'locations', 
            'packageData'
        ));
    }

    // AJAX endpoint for real-time calculations (no database operations)
    public function calculatePricing(Request $request)
    {
        $data = $request->all();
        
        // Sample calculation logic
        $cabsTotal = 0;
        $activitiesTotal = 0;
        
        // Calculate based on submitted data
        if (isset($data['services'])) {
            foreach ($data['services'] as $service) {
                if ($service['type'] === 'transport') {
                    $cabsTotal += ($service['rate'] ?? 0) * ($service['quantity'] ?? 0);
                } else if ($service['type'] === 'activity') {
                    $activitiesTotal += ($service['rate'] ?? 0) * ($service['quantity'] ?? 0);
                }
            }
        }
        
        $totalCost = $cabsTotal + $activitiesTotal;
        $perPersonCost = $totalCost / ($data['adults'] ?? 16);
        
        return response()->json([
            'success' => true,
            'calculations' => [
                'cabs_total' => $cabsTotal,
                'activities_total' => $activitiesTotal,
                'total_cost' => $totalCost,
                'per_person_cost' => round($perPersonCost, 2),
                'currency' => $data['currency'] ?? 'SGD'
            ]
        ]);
    }

    // Get hotel pricing (sample data)
    public function getHotelPricing(Request $request)
    {
        $hotelId = $request->input('hotel_id');
        $date = $request->input('date');
        
        // Sample pricing data
        $pricing = [
            1 => ['rate' => 120, 'available' => true], // Hotel Boss
            2 => ['rate' => 280, 'available' => true], // Marina Bay
            3 => ['rate' => 200, 'available' => true], // Grand Plaza
            4 => ['rate' => 450, 'available' => false], // Raffles (unavailable)
        ];
        
        return response()->json([
            'success' => true,
            'hotel_id' => $hotelId,
            'date' => $date,
            'pricing' => $pricing[$hotelId] ?? ['rate' => 0, 'available' => false]
        ]);
    }

    // Get activity availability (sample data)
    public function getActivityAvailability(Request $request)
    {
        $activityId = $request->input('activity_id');
        $date = $request->input('date');
        $slot = $request->input('slot');
        
        return response()->json([
            'success' => true,
            'activity_id' => $activityId,
            'date' => $date,
            'slot' => $slot,
            'available' => true,
            'remaining_slots' => rand(5, 50)
        ]);
    }

    // Validate quote data (no saving)
    public function validateQuote(Request $request)
    {
        $validation = [
            'hotels' => [],
            'transport' => [],
            'activities' => [],
            'errors' => [],
            'warnings' => []
        ];
        
        // Sample validation logic
        $data = $request->all();
        
        if (empty($data['hotels'])) {
            $validation['warnings'][] = 'No hotels selected';
        }
        
        if (empty($data['transport_services'])) {
            $validation['warnings'][] = 'No transportation services added';
        }
        
        return response()->json([
            'success' => true,
            'validation' => $validation,
            'total_cost' => rand(2500, 5000),
            'message' => 'Quote validation completed (demo mode - no data saved)'
        ]);
    }

    // Add special hotel service
    public function addHotelService(Request $request)
    {
        return response()->json([
            'success' => true,
            'service' => [
                'id' => rand(1000, 9999),
                'name' => $request->input('service_name', 'Special Service'),
                'price' => $request->input('price', 0),
                'quantity' => $request->input('quantity', 1)
            ]
        ]);
    }

    // Get vehicle pricing
    public function getVehiclePricing(Request $request)
    {
        $vehicleId = $request->input('vehicle_id');
        $date = $request->input('date');
        $serviceType = $request->input('service_type');
        
        // Sample vehicle pricing
        $pricing = [
            1 => ['rate' => 35, 'available' => true], // 7/12 Seater
            2 => ['rate' => 45, 'available' => true], // 15 Seater
            3 => ['rate' => 65, 'available' => true], // 25 Seater
            4 => ['rate' => 85, 'available' => true], // 35 Seater
            5 => ['rate' => 105, 'available' => true], // 45 Seater
        ];
        
        return response()->json([
            'success' => true,
            'vehicle_id' => $vehicleId,
            'pricing' => $pricing[$vehicleId] ?? ['rate' => 50, 'available' => true]
        ]);
    }

    // Update service location suggestions
    public function getLocationSuggestions(Request $request)
    {
        $query = $request->input('query');
        
        $locations = [
            'Changi Airport',
            'Marina Bay Sands',
            'Sentosa Island',
            'Orchard Road',
            'Little India',
            'Chinatown',
            'Clarke Quay',
            'Singapore Zoo',
            'Night Safari',
            'Gardens by the Bay',
            'Universal Studios',
            'Merlion Park',
            'Singapore Flyer',
            'Boat Quay',
            'Bugis Street',
            'Jurong Bird Park',
            'Science Centre',
            'ArtScience Museum',
            'Haw Par Villa',
            'East Coast Park'
        ];
        
        $suggestions = array_filter($locations, function($location) use ($query) {
            return stripos($location, $query) !== false;
        });
        
        return response()->json([
            'success' => true,
            'suggestions' => array_values($suggestions)
        ]);
    }

    // Calculate real-time totals
    public function calculateRealTimeTotals(Request $request)
    {
        $data = $request->all();
        
        $hotelTotal = 0;
        $cabsTotal = 0;
        $activitiesTotal = 0;
        $specialServicesTotal = 0;
        
        // Calculate hotel costs
        if (isset($data['hotels'])) {
            foreach ($data['hotels'] as $hotel) {
                if ($hotel['selected'] ?? false) {
                    $hotelTotal += ($hotel['rate'] ?? 0) * ($hotel['rooms'] ?? 1) * ($hotel['nights'] ?? 1);
                }
            }
        }
        
        // Calculate transport costs
        if (isset($data['transport_services'])) {
            foreach ($data['transport_services'] as $service) {
                $cabsTotal += ($service['rate'] ?? 0) * ($service['quantity'] ?? 1);
            }
        }
        
        // Calculate activity costs
        if (isset($data['activities'])) {
            foreach ($data['activities'] as $activity) {
                $activitiesTotal += ($activity['rate'] ?? 0) * ($activity['quantity'] ?? 1);
            }
        }
        
        // Calculate special services
        if (isset($data['special_services'])) {
            foreach ($data['special_services'] as $service) {
                $specialServicesTotal += ($service['rate'] ?? 0) * ($service['quantity'] ?? 1);
            }
        }
        
        $totalCost = $hotelTotal + $cabsTotal + $activitiesTotal + $specialServicesTotal;
        $pax = $data['pax'] ?? 16;
        $perPersonCost = $pax > 0 ? $totalCost / $pax : 0;
        
        return response()->json([
            'success' => true,
            'totals' => [
                'hotel_total' => $hotelTotal,
                'cabs_total' => $cabsTotal,
                'activities_total' => $activitiesTotal,
                'special_services_total' => $specialServicesTotal,
                'grand_total' => $totalCost,
                'per_person_cost' => round($perPersonCost, 2),
                'currency' => $data['currency'] ?? 'SGD'
            ]
        ]);
    }

    // Save quote as draft (demo - no actual saving)
    public function saveDraft(Request $request)
    {
        return response()->json([
            'success' => true,
            'draft_id' => 'DRAFT_' . rand(10000, 99999),
            'message' => 'Quote saved as draft successfully (demo mode)',
            'saved_at' => now()->format('Y-m-d H:i:s')
        ]);
    }

    // Save final quote (demo - no actual saving)
    public function saveQuote(Request $request)
    {
        $validation = $this->validateQuoteData($request->all());
        
        if (!$validation['is_valid']) {
            return response()->json([
                'success' => false,
                'errors' => $validation['errors'],
                'message' => 'Please fix the validation errors before saving'
            ], 422);
        }
        
        return response()->json([
            'success' => true,
            'quote_id' => 'QT_' . rand(10000, 99999),
            'message' => 'Quote saved successfully (demo mode)',
            'redirect_url' => '/custom-packages',
            'saved_at' => now()->format('Y-m-d H:i:s')
        ]);
    }

    // Get markup suggestions based on service type
    public function getMarkupSuggestions(Request $request)
    {
        $serviceType = $request->input('service_type');
        $totalCost = $request->input('total_cost', 0);
        
        $suggestions = [
            'budget' => ['markup' => 8, 'description' => 'Budget Package'],
            'standard' => ['markup' => 12, 'description' => 'Standard Package'],
            'premium' => ['markup' => 18, 'description' => 'Premium Package'],
            'luxury' => ['markup' => 25, 'description' => 'Luxury Package']
        ];
        
        return response()->json([
            'success' => true,
            'suggestions' => $suggestions,
            'recommended' => 'standard'
        ]);
    }

    // Check service availability
    public function checkServiceAvailability(Request $request)
    {
        $serviceId = $request->input('service_id');
        $date = $request->input('date');
        $quantity = $request->input('quantity', 1);
        
        // Random availability simulation
        $available = rand(1, 10) > 2; // 80% chance of availability
        
        return response()->json([
            'success' => true,
            'service_id' => $serviceId,
            'date' => $date,
            'available' => $available,
            'available_quantity' => $available ? rand($quantity, $quantity + 10) : 0,
            'message' => $available ? 'Service available' : 'Service not available for selected date'
        ]);
    }

    // Get currency exchange rates
    public function getCurrencyRates(Request $request)
    {
        $baseCurrency = $request->input('base', 'SGD');
        
        $rates = [
            'SGD' => 1.0,
            'USD' => 0.74,
            'EUR' => 0.68,
            'GBP' => 0.58,
            'INR' => 61.5,
            'AUD' => 1.02,
            'JPY' => 107.8
        ];
        
        return response()->json([
            'success' => true,
            'base_currency' => $baseCurrency,
            'rates' => $rates,
            'last_updated' => now()->format('Y-m-d H:i:s')
        ]);
    }

    // Export quote to PDF (demo)
    public function exportToPDF(Request $request)
    {
        return response()->json([
            'success' => true,
            'pdf_url' => '/downloads/quote_' . rand(10000, 99999) . '.pdf',
            'message' => 'PDF generated successfully (demo mode)'
        ]);
    }

    // Send quote via email (demo)
    public function sendQuoteEmail(Request $request)
    {
        $email = $request->input('email');
        
        return response()->json([
            'success' => true,
            'message' => "Quote sent to {$email} successfully (demo mode)",
            'sent_at' => now()->format('Y-m-d H:i:s')
        ]);
    }

    // Private helper method for validation
    private function validateQuoteData($data)
    {
        $errors = [];
        $warnings = [];
        
        // Basic validation
        if (empty($data['destination'])) {
            $errors[] = 'Destination is required';
        }
        
        if (empty($data['start_date'])) {
            $errors[] = 'Start date is required';
        }
        
        if (empty($data['pax']) || $data['pax'] <= 0) {
            $errors[] = 'Number of passengers must be greater than 0';
        }
        
        // Hotel validation
        if (empty($data['hotels'])) {
            $warnings[] = 'No hotels selected';
        }
        
        // Transport validation
        if (empty($data['transport_services'])) {
            $warnings[] = 'No transportation services added';
        }
        
        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }
}
