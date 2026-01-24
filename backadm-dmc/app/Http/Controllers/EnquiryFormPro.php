<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Models\User;
use App\Models\Agency;
use App\Models\Country;
use App\Models\Agent;
use App\Models\Port;
use App\Models\Attraction;
use App\Models\Restaurant;
use App\Models\Meal;
use App\Models\Guide;
use App\Models\Vehicle;
use App\Models\Tour;
use App\Models\Order;
use App\Models\Tax;
use App\Models\Rate;
use App\Models\VehicleZoneMapping;
use App\Helpers\CommonHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EnquiryFormPro extends Controller
{
    /**
     * Get vehicle details by vehicle_id
     */
    private function getVehicleDetails($vehicleId)
    {
        if (empty($vehicleId)) {
            return null;
        }
        
        $vehicle = Vehicle::where('vehicle_id', $vehicleId)->first();
        
        if (!$vehicle) {
            \Log::warning('Vehicle not found', ['vehicle_id' => $vehicleId]);
            return null;
        }
        
        return [
            'vehicle_id' => (string) $vehicle->vehicle_id,
            'vehicles_name' => $vehicle->vehicle_name ?? '',
            'vehicle_type' => $vehicle->vehicle_type ?? '',
            'vehicle_model' => $vehicle->vehicle_model ?? '',
            'model_year' => $vehicle->model_year ?? '',
            'seating_capacity' => $vehicle->seating_capacity ?? 0,
            'image' => $vehicle->image ?? ''
        ];
    }
    
    /**
     * Normalize transfer type to proper case
     */
    private function normalizeTransferType($type)
    {
        $type = trim($type);
        // Handle new uppercase single letter format
        if ($type === 'P') {
            return 'Private';
        } elseif ($type === 'S') {
            return 'Shared';
        }
        // Handle legacy formats
        if (stripos($type, 'private') !== false) {
            return 'Private';
        } elseif (stripos($type, 'shared') !== false || stripos($type, 'sic') !== false) {
            return 'Shared';
        }
        return ucfirst(strtolower($type));
    }
    
    public function create(Request $request)
    {
        $user = auth()->user();
        $destination = $user->country ?? 'Singapore';
        
        // Get initial data from session if available
        $initialData = $request->session()->get('tour_pro_initial_data', null);
        
        // Get DMC ID based on user role
        $dmc_id = null;
        if ($user->role_id == 11) {
            $dmc_id = $user->userId;
        } elseif (in_array($user->role_id, [33, 34, 35, 77, 78, 84, 120, 128, 129, 130, 131, 132, 133, 134, 135, 136, 137, 138, 139, 140])) {
            $dmc_id = $user->created_by;
        } elseif (in_array($user->role_id, [37, 64, 65, 66, 67, 68])) {
            $sales_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $sales_head ? $sales_head->created_by : null;
        } elseif (in_array($user->role_id, [38, 81, 90, 108, 117, 124, 125, 126, 127])) {
            $sales_manager = User::where('userId', $user->created_by)->first();
            if ($sales_manager) {
                $sales_head = User::where('userId', $sales_manager->created_by)->first();
                $dmc_id = $sales_head ? $sales_head->created_by : null;
            }
        }
        
        // Log DMC ID for debugging
        \Log::info('EnquiryFormPro create() - DMC ID determined', [
            'dmc_id' => $dmc_id,
            'user_id' => $user->userId,
            'role_id' => $user->role_id,
            'created_by' => $user->created_by ?? null
        ]);
        
        // Get master_dmc_id from logged-in user
        $master_dmc_id = $user->master_dmc_id;
        
        // Get countries based on master_dmc_id
        $countries = collect();
        if ($master_dmc_id) {
            // Find all users with this master_dmc_id
            $usersWithMasterDmc = User::where('master_dmc_id', $master_dmc_id)
                ->whereNotNull('country')
                ->get();
            
            // Extract and merge all countries (comma-separated)
            $countryNames = [];
            foreach ($usersWithMasterDmc as $userItem) {
                if ($userItem->country) {
                    $userCountries = array_map('trim', explode(',', $userItem->country));
                    $countryNames = array_merge($countryNames, $userCountries);
                }
            }
            
            // Remove duplicates and get unique country names
            $countryNames = array_unique($countryNames);
            
            // Get Country objects matching these names
            if (!empty($countryNames)) {
                $countries = Country::whereIn('name', $countryNames)
                    ->where('is_active', 1)
                    ->orderBy('name')
                    ->get();
            }
            
            \Log::info('EnquiryFormPro create() - Countries filtered by master_dmc_id', [
                'master_dmc_id' => $master_dmc_id,
                'country_count' => $countries->count(),
                'countries' => $countries->pluck('name')->toArray()
            ]);
        } else {
            // Fallback: Get all active countries if no master_dmc_id
            $countries = Country::where('is_active', 1)->orderBy('name')->get();
        }
        
        // Load agencies based on destination from popup or user country
        $agencyQuery = Agency::where('status', 1);
        
        // Filter by DMC ID if available
        if ($dmc_id) {
            $agencyQuery->whereJsonContains('dmc_id', (int) $dmc_id);
        }
        
        // If we have initial data, get agencies from that destination, otherwise use user's country
        if ($initialData && isset($initialData['destination_display'])) {
            // Get destination(s) from initial data
            if (isset($initialData['destinations_array'])) {
                $agencyQuery->whereIn('country', $initialData['destinations_array']);
            } else {
                $agencyQuery->where('country', $initialData['destination_display']);
            }
        } else {
            $agencyQuery->where('country', $destination);
        }
        
        $agencies = $agencyQuery->orderBy('agency_name', 'asc')->get();
        
        // Log agency filtering results for debugging
        \Log::info('EnquiryFormPro create() - Agencies loaded', [
            'dmc_id' => $dmc_id,
            'destination' => $initialData['destination_display'] ?? $destination,
            'total_agencies' => $agencies->count(),
            'agency_names' => $agencies->pluck('agency_name')->toArray(),
            'agency_ids' => $agencies->pluck('agency_id')->toArray()
        ]);
        
        // Get country names for port filtering
        $countryNames = $countries->pluck('name')->toArray();
        
        // Filter ports by accessible countries
        $portsQuery = Port::where('status', 1)->with('country')->orderBy('port_name');
        if (!empty($countryNames)) {
            $portsQuery->whereIn('country', $countryNames);
        }
        $ports = $portsQuery->select('port_id', 'port_name', 'type', 'country', 'city_id')->get();
        
        \Log::info('EnquiryFormPro create() - Ports loaded', [
            'filtered_by_countries' => $countryNames,
            'count' => $ports->count(),
            'port_names' => $ports->pluck('port_name')->toArray()
        ]);
        
        $destinations = $countries; // Use the filtered countries as destinations
        
        // Get master DMC destinations for miscellaneous items
        // Master DMC is the created_by user (the parent DMC)
        $master_dmc_id = null;
        if ($dmc_id) {
            // Get the master DMC (the user who created this DMC)
            $currentDmc = User::find($dmc_id);
            if ($currentDmc && $currentDmc->created_by) {
                $master_dmc_id = $currentDmc->created_by;
            } else {
                // If no parent, this IS the master DMC
                $master_dmc_id = $dmc_id;
            }
        }
        
        // Get destinations for master DMC (use the same filtered countries as header)
        $master_dmc_destinations = $countries;
        
        $agents = []; // Start with empty agents, will be populated via AJAX
        
        // Get dynamic data filtered by DMC ID (like SingleTourPackageController)
        if ($dmc_id) {
            // Get attractions for this DMC (attractions use 'location' field, not 'city')
            // Filter by destination if we have initial data, otherwise use user's country
            $attractionDestination = $destination;
            if ($initialData && isset($initialData['destination_display'])) {
                // For multiple destinations, we'll load attractions for all of them
                if (isset($initialData['destinations_array'])) {
                    $attractionDestination = $initialData['destinations_array'];
                } else {
                    $attractionDestination = $initialData['destination_display'];
                }
            }
            
            // Get user's accessible countries based on master_dmc_id
            $userCountries = [];
            if ($master_dmc_id) {
                $usersWithMasterDmc = User::where('master_dmc_id', $master_dmc_id)
                    ->whereNotNull('country')
                    ->get();
                
                foreach ($usersWithMasterDmc as $userItem) {
                    if ($userItem->country) {
                        $userCountries = array_merge($userCountries, array_map('trim', explode(',', $userItem->country)));
                    }
                }
                $userCountries = array_unique($userCountries);
            }
            
            // Filter attractions by DMC ID and accessible countries
            $attractionsQuery = Attraction::whereJsonContains('dmc_id', (int) $dmc_id)
                ->where('status', 1)
                ->where('is_active', 1);
            
            // Apply country filter if we have user countries
            if (!empty($userCountries)) {
                $attractionsQuery->whereIn('country', $userCountries);
            }
            
            $attractions = $attractionsQuery
                ->select('attraction_id', 'name', 'location', 'country', 'open_time', 'close_time', 
                         'adult_price', 'child_price', 'senior_adult_price', 'zone_assignments')
                ->orderBy('name')
                ->get();
            
            // Add zone_id to each attraction
            $attractions->each(function($attraction) use ($dmc_id) {
                $attraction->zone_id = $dmc_id ? $attraction->getZoneForDmc($dmc_id) : null;
            });
            
            \Log::info('EnquiryFormPro create() - Attractions loaded', [
                'dmc_id' => $dmc_id,
                'filtered_by_countries' => $userCountries,
                'count' => $attractions->count(),
                'attraction_ids' => $attractions->pluck('attraction_id')->toArray(),
                'attraction_names' => $attractions->pluck('name')->toArray(),
                'attraction_countries' => $attractions->pluck('country')->unique()->toArray()
            ]);
            
            // Get restaurants for this DMC with city info (restaurants have 'city' field)
            $restaurantsQuery = Restaurant::whereJsonContains('dmc_id', (int) $dmc_id)
                ->where('status', 1)
                ->where('is_active', 1);
            
            // Apply country filter if we have user countries
            if (!empty($userCountries)) {
                $restaurantsQuery->whereIn('country', $userCountries);
            }
            
            $restaurants = $restaurantsQuery
                ->select('restaurant_id', 'name', 'city', 'country', 'breakfast_available', 'lunch_available', 
                         'dinner_available', 'opening_time_bf', 'closing_time_bf', 'opening_time_lunch', 
                         'closing_time_lunch', 'opening_time_dinner', 'closing_time_dinner', 'zone_assignments')
                ->orderBy('name')
                ->get();
            
            // Add zone_id to each restaurant
            $restaurants->each(function($restaurant) use ($dmc_id) {
                $restaurant->zone_id = $dmc_id ? $restaurant->getZoneForDmc($dmc_id) : null;
            });
            
            \Log::info('EnquiryFormPro create() - Restaurants loaded', [
                'dmc_id' => $dmc_id,
                'filtered_by_countries' => $userCountries,
                'count' => $restaurants->count(),
                'restaurant_ids' => $restaurants->pluck('restaurant_id')->toArray(),
                'restaurant_names' => $restaurants->pluck('name')->toArray(),
                'restaurant_countries' => $restaurants->pluck('country')->unique()->toArray()
            ]);
            
            // Get all meals for these restaurants
            $restaurantIds = $restaurants->pluck('restaurant_id')->toArray();
            $meals = Meal::whereIn('restaurant_id', $restaurantIds)
                ->select('meal_id', 'restaurant_id', 'name', 'type', 'price', 'adult_price', 'child_price', 'meal_period')
                ->get();
            
            \Log::info('EnquiryFormPro create() - Meals loaded', [
                'dmc_id' => $dmc_id,
                'meal_count' => $meals->count(),
                'restaurant_count' => count($restaurantIds)
            ]);
            
            // Get guides for this DMC only (include pricing for linked guides)
            // Status 1 and 3 are both considered active guides
            if ($dmc_id) {
                $guides = Guide::where('dmc_id', $dmc_id)
                    ->whereIn('status', [1, 3])
                    ->with('languages')
                    ->select('guide_id', 'name', 'city', 'twelve_hour_price', 'day_rate')
                    ->orderBy('name')
                    ->get();
                
                \Log::info('EnquiryFormPro create() - Guides loaded for arrival/departure', [
                    'dmc_id' => $dmc_id,
                    'guide_count' => $guides->count(),
                    'guide_ids' => $guides->pluck('guide_id')->toArray(),
                    'guide_names' => $guides->pluck('name')->toArray()
                ]);
            } else {
                $guides = collect([]); // Empty collection if no DMC ID
            }
            
            // Get hotels for this DMC
            $hotelsQuery = Hotel::where('status', 1)
                ->where('is_active', 1)
                ->where('is_complete', 1)
                ->whereJsonContains('dmc_id', (int) $dmc_id)
                ->whereNotNull('hotel_unique_id')
                ->where('hotel_unique_id', '!=', '')
                ->where('hotel_unique_id', '!=', '0');
            
            // Apply country filter if we have user countries
            if (!empty($userCountries)) {
                $hotelsQuery->whereIn('country', $userCountries);
            }
            
            $hotels = $hotelsQuery
                ->select('id', 'hotel_unique_id', 'name', 'city', 'country', 'address', 'zone_assignments')
                ->orderBy('name')
                ->get();
            
            // Add zone_id to each hotel
            $hotels->each(function($hotel) use ($dmc_id) {
                $hotel->zone_id = $dmc_id ? $hotel->getZoneForDmc($dmc_id) : null;
            });
            
            \Log::info('EnquiryFormPro create() - Hotels loaded', [
                'dmc_id' => $dmc_id,
                'filtered_by_countries' => $userCountries,
                'count' => $hotels->count(),
                'hotel_names' => $hotels->pluck('name')->toArray(),
                'hotel_countries' => $hotels->pluck('country')->unique()->toArray()
            ]);
            
            // Get vehicles for this DMC
            $vehicles = Vehicle::where('dmc_id', $dmc_id)
                ->where('is_available', 1)
                ->select('vehicle_id', 'vehicle_type', 'vehicle_name', 'seating_capacity', 'base_price', 'sharable_base_price', 'sharable')
                ->orderBy('vehicle_type')
                ->get();
            
            \Log::info('EnquiryFormPro create() - Hotels and Vehicles loaded', [
                'dmc_id' => $dmc_id,
                'hotel_count' => $hotels->count(),
                'vehicle_count' => $vehicles->count()
            ]);
        } else {
            // Fallback: Get all active attractions and restaurants if no DMC ID
            // Filter by destination if we have initial data, otherwise use user's country
            $attractionDestination = $destination;
            if ($initialData && isset($initialData['destination_display'])) {
                // For multiple destinations, we'll load attractions for all of them
                if (isset($initialData['destinations_array'])) {
                    $attractionDestination = $initialData['destinations_array'];
                } else {
                    $attractionDestination = $initialData['destination_display'];
                }
            }
            
            $attractionsQuery = Attraction::where('status', 1)
                ->where('is_active', 1);
            
            // Apply destination filter
            if (is_array($attractionDestination)) {
                $attractionsQuery->whereIn('location', $attractionDestination);
            } else {
                $attractionsQuery->where('location', $attractionDestination);
            }
            
            $attractions = $attractionsQuery
                ->select('attraction_id', 'name', 'location', 'country', 'open_time', 'close_time', 
                         'adult_price', 'child_price', 'senior_adult_price')
                ->orderBy('name')
                ->get();
            
            $restaurants = Restaurant::where('status', 1)
                ->where('is_active', 1)
                ->select('restaurant_id', 'name', 'city', 'country', 'breakfast_available', 'lunch_available', 
                         'dinner_available', 'opening_time_bf', 'closing_time_bf', 'opening_time_lunch', 
                         'closing_time_lunch', 'opening_time_dinner', 'closing_time_dinner')
                ->orderBy('name')
                ->get();
            
            $restaurantIds = $restaurants->pluck('restaurant_id')->toArray();
            $meals = Meal::whereIn('restaurant_id', $restaurantIds)
                ->select('meal_id', 'restaurant_id', 'name', 'type', 'price', 'adult_price', 'child_price', 'meal_period')
                ->get();
            
            // Get guides for this DMC only
            // Status 1 and 3 are both considered active guides
            if ($dmc_id) {
                $guides = Guide::where('dmc_id', $dmc_id)
                    ->whereIn('status', [1, 3])
                    ->with('languages')
                    ->select('guide_id', 'name', 'city')
                    ->orderBy('name')
                    ->get();
            } else {
                $guides = collect([]); // Empty collection if no DMC ID
            }
            
            // Get all hotels (fallback)
            $hotels = Hotel::where('status', 1)
                ->where('is_active', 1)
                ->where('is_complete', 1)
                ->whereNotNull('hotel_unique_id')
                ->where('hotel_unique_id', '!=', '')
                ->where('hotel_unique_id', '!=', '0')
                ->select('id', 'hotel_unique_id', 'name', 'city', 'country', 'address')
                ->orderBy('name')
                ->get();
            
            // Get all vehicles (fallback)
            $vehicles = Vehicle::where('is_available', 1)
                ->select('vehicle_id', 'vehicle_type', 'vehicle_name', 'seating_capacity', 'base_price', 'sharable_base_price', 'sharable')
                ->orderBy('vehicle_type')
                ->get();
        }
        
        // Create city to country mapping for filtering
        $cityCountryMap = \App\Models\City::with('country')
            ->get()
            ->pluck('country.name', 'name')
            ->toArray();
        
        // Fetch default values for this DMC (6 types: hotel, restaurant, attraction, car_private, car_shared, port)
        $defaultValues = [];
        if ($dmc_id) {
            $defaults = \App\Models\DefaultValue::where('dmc_id', $dmc_id)
                ->where('status', 1)
                ->get();
            
            foreach ($defaults as $default) {
                $defaultValues[$default->name] = $default->service_id;
            }
        }
        
        return view('enquiryform_pro.create', compact('destination', 'agents', 'agencies', 'user', 'countries', 'ports', 'destinations', 'attractions', 'restaurants', 'initialData', 'meals', 'guides', 'dmc_id', 'hotels', 'vehicles', 'master_dmc_destinations', 'cityCountryMap', 'defaultValues'));
    }
    
    /**
     * Initialize tour with popup data and redirect to create page
     */
    public function initialize(Request $request)
    {
        $validated = $request->validate([
            'tour_type' => 'required|in:Group,FIT',
            'tour_start_date' => 'required|date|after_or_equal:today',
            'tour_end_date' => 'required|date|after:tour_start_date',
            'adult_count' => 'required|integer|min:0',
            'child_count' => 'nullable|integer|min:0',
            'infant_count' => 'nullable|integer|min:0',
            'agency_id' => 'required|exists:agencies,agency_id',
            'agent_id' => 'required|exists:agents,agent_id',
            'salutation' => 'required|in:Mr,Mrs,Ms,Dr',
            'customer_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
            'multiple_destination' => 'nullable|boolean',
            'destination_single' => 'nullable|string',
            'destinations' => 'nullable|json',
        ]);
        
        // Get agency and agent details
        $agency = Agency::find($validated['agency_id']);
        $agent = Agent::find($validated['agent_id']);
        
        // Prepare destination data
        if ($request->has('multiple_destination') && $request->multiple_destination) {
            // Decode JSON destinations
            $destinations = json_decode($request->destinations, true);
            if (is_array($destinations) && count($destinations) > 0) {
                $validated['destinations_array'] = $destinations;
                $validated['destination_display'] = implode(', ', $destinations);
            } else {
                // Fallback if JSON decode fails
                $validated['destination_display'] = $request->destination_single ?? '';
            }
        } else {
            // Single destination mode
            $validated['destination_display'] = $request->destination_single;
            // Also set destinations_array for single destination to maintain consistency
            if ($request->destination_single) {
                $validated['destinations_array'] = [$request->destination_single];
            }
        }
        
        // Add agency and agent names
        $validated['agency_name'] = $agency->agency_name ?? '';
        $validated['agent_name'] = $agent->name ?? '';
        
        // Store in session
        $request->session()->put('tour_pro_initial_data', $validated);
        
        return redirect()->route('enquiry-form-pro.create');
    }
    
    /**
     * Get agencies for popup (AJAX) - filtered by destination and DMC
     */
    public function getAgencies(Request $request)
    {
        $user = auth()->user();
        
        // Get DMC ID based on user role (following existing pattern)
        $dmc_id = null;
        if ($user->role_id == 11) {
            $dmc_id = $user->userId;
        } elseif (in_array($user->role_id, [33, 34, 128, 129, 130, 131, 132, 134, 135, 136, 137, 138])) {
            $dmc_id = $user->created_by;
        } elseif (in_array($user->role_id, [37, 64, 65, 66, 67, 68])) {
            $sales_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $sales_head ? $sales_head->created_by : null;
        } elseif (in_array($user->role_id, [38, 81, 90, 108, 117, 124, 125, 126, 127])) {
            $sales_manager = User::where('userId', $user->created_by)->first();
            if ($sales_manager) {
                $sales_head = User::where('userId', $sales_manager->created_by)->first();
                $dmc_id = $sales_head ? $sales_head->created_by : null;
            }
        }
        
        // Get destination(s) from request
        $destination = $request->input('destination');
        $destinations = $request->input('destinations'); // comma-separated
        
        if (!$destination && !$destinations) {
            return response()->json([
                'success' => false,
                'message' => 'Destination is required',
                'agencies' => []
            ]);
        }
        
        // Parse destinations
        $countryArray = [];
        if ($destinations) {
            $countryArray = array_map('trim', explode(',', $destinations));
        } else {
            $countryArray = [$destination];
        }
        
        // Build query for agencies with two-step filtering:
        // Step 1: Get agencies that are in the selected destination(s)
        // Step 2: From those, filter only agencies connected to this DMC
        
        $agencies = Agency::where('status', 1)
            ->whereIn('country', $countryArray); // Step 1: Filter by destination country
        
        // Step 2: Filter by DMC ID - only agencies that have this DMC in their dmc_id JSON array
        if ($dmc_id) {
            $agencies = $agencies->whereJsonContains('dmc_id', (int) $dmc_id);
        }
        
        $agencies = $agencies->orderBy('agency_name', 'asc')
                           ->get(['agency_id', 'agency_name', 'country', 'dmc_id']);
        
        // Log agency filtering results for debugging
        \Log::info('EnquiryFormPro getAgencies() - AJAX request', [
            'dmc_id' => $dmc_id,
            'user_id' => $user->userId,
            'role_id' => $user->role_id,
            'destinations' => $countryArray,
            'total_agencies' => $agencies->count(),
            'agency_names' => $agencies->pluck('agency_name')->toArray()
        ]);
        
        return response()->json([
            'success' => true,
            'agencies' => $agencies,
            'dmc_id' => $dmc_id,
            'count' => $agencies->count()
        ]);
    }
    
    /**
     * Get destinations for popup (AJAX)
     */
    public function getDestinations(Request $request)
    {
        $user = auth()->user();
        $master_dmc_id = $user->master_dmc_id;
        
        // Get countries based on master_dmc_id
        $destinations = collect();
        if ($master_dmc_id) {
            // Find all users with this master_dmc_id
            $usersWithMasterDmc = User::where('master_dmc_id', $master_dmc_id)
                ->whereNotNull('country')
                ->get();
            
            // Extract and merge all countries (comma-separated)
            $countryNames = [];
            foreach ($usersWithMasterDmc as $userItem) {
                if ($userItem->country) {
                    $userCountries = array_map('trim', explode(',', $userItem->country));
                    $countryNames = array_merge($countryNames, $userCountries);
                }
            }
            
            // Remove duplicates and get unique country names
            $countryNames = array_unique($countryNames);
            
            // Get Country objects matching these names
            if (!empty($countryNames)) {
                $destinations = Country::whereIn('name', $countryNames)
                    ->where('is_active', 1)
                    ->orderBy('name', 'asc')
                    ->get(['id', 'name']);
            }
        } else {
            // Fallback: Get all active countries if no master_dmc_id
            $destinations = Country::where('is_active', 1)
                                  ->orderBy('name', 'asc')
                                  ->get(['id', 'name']);
        }
        
        return response()->json([
            'success' => true,
            'destinations' => $destinations,
            'master_dmc_id' => $master_dmc_id
        ]);
    }
    
    // Get agents by agency ID
    public function getAgentsByAgency(Request $request)
    {
        $agencyId = $request->input('agency_id');
        if (!$agencyId) {
            return response()->json([
                'success' => false,
                'message' => 'Agency ID is required'
            ], 400);
        }
        
        // Fetch agents for the selected agency
        $agents = Agent::where('status', 1)
                      ->where('agency_id', $agencyId)
                      ->orderBy('name', 'asc')
                      ->get(['agent_id', 'name', 'email']);
        
        return response()->json([
            'success' => true,
            'agents' => $agents
        ]);
    }
    
    /**
     * Get hotels by destination (AJAX)
     */
    public function getHotelsByDestination(Request $request)
    {
        $user = auth()->user();
        $destination = $request->input('destination');
        
        if (!$destination) {
            return response()->json([
                'success' => false,
                'message' => 'Destination is required'
            ], 400);
        }
        
        // Get DMC ID based on user role
        $dmc_id = null;
        if ($user->role_id == 11) {
            $dmc_id = $user->userId;
        } elseif (in_array($user->role_id, [33, 34, 35, 77, 78, 84, 120, 128, 129, 130, 131, 132, 133, 134, 135, 136, 137, 138, 139, 140])) {
            $dmc_id = $user->created_by;
        } elseif (in_array($user->role_id, [37, 64, 65, 66, 67, 68])) {
            $sales_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $sales_head ? $sales_head->created_by : null;
        } elseif (in_array($user->role_id, [38, 81, 90, 108, 117, 124, 125, 126, 127])) {
            $sales_manager = User::where('userId', $user->created_by)->first();
            if ($sales_manager) {
                $sales_head = User::where('userId', $sales_manager->created_by)->first();
                $dmc_id = $sales_head ? $sales_head->created_by : null;
            }
        }
        
        \Log::info('getHotelsByDestination - DMC ID determined', [
            'dmc_id' => $dmc_id,
            'user_id' => $user->userId,
            'role_id' => $user->role_id,
            'destination' => $destination
        ]);
        
        // Get hotels selected by this DMC with rooms for the destination
        $hotelsQuery = Hotel::where('status', 1)
                      ->where('is_active', 1)
                      ->where('is_complete', 1)
                      ->where('city', $destination)
                      ->with(['rooms' => function($query) {
                          $query->where('status', 1)
                                ->with(['beds' => function($bedQuery) {
                                    $bedQuery->where('is_active', 1);
                                }])
                                ->orderBy('room_type', 'asc');
                      }]);
        
        // Filter by DMC if available
        if ($dmc_id) {
            $hotelsQuery->whereJsonContains('dmc_id', (int) $dmc_id);
        }
        
        $hotels = $hotelsQuery->orderBy('name', 'asc')->get();
        
        \Log::info('getHotelsByDestination - Hotels found', [
            'count' => $hotels->count(),
            'hotel_ids' => $hotels->pluck('id')->toArray(),
            'hotel_names' => $hotels->pluck('name')->toArray()
        ]);
        
        // Transform the data to include bed information properly and fetch rates for each hotel
        $hotels->each(function($hotel) use ($dmc_id, $user) {
            // Extract zone_id from hotel's zone_assignments for this DMC
            $zone_id = null;
            if ($dmc_id) {
                $zone_id = $hotel->getZoneForDmc($dmc_id);
            }
            $hotel->zone_id = $zone_id;
            
            // Fetch rates for this hotel with DMC access control
            // Use hotel_unique_id if available, otherwise use id
            $hotelIdForRates = $hotel->hotel_unique_id ?? $hotel->id;
            $ratesQuery = Rate::where('hotel_id', $hotelIdForRates)->where('is_active', 1);
            
            // Apply DMC filtering for rates for non-admin users
            if ($user->role_id != 1) {
                if ($dmc_id) {
                    $ratesQuery->where('dmc_id', $dmc_id);
                }
            }
            
            $rates = $ratesQuery->orderByRaw("
                CASE 
                    WHEN event_type = 'Blackout Date' THEN 1
                    WHEN event_type = 'Fair Date' THEN 2
                    WHEN event_type = 'Season' THEN 3
                    ELSE 4
                END
            ")->orderBy('start_date')->get();
            
            // Attach rates to hotel
            $hotel->rates = $rates->map(function($rate) {
                return [
                    'rate_id' => $rate->rate_id,
                    'event' => $rate->event,
                    'event_type' => $rate->event_type,
                    'price' => $rate->price ?? 0,
                    'weekday_price' => $rate->weekday_price ?? 0,
                    'weekend_price' => $rate->weekend_price ?? 0,
                    'start_date' => $rate->start_date,
                    'end_date' => $rate->end_date,
                ];
            })->toArray();
            
            $hotel->rooms->each(function($room) {
                // Attach bed types to each room
                if ($room->beds && $room->beds->isNotEmpty()) {
                    $room->bed_types = $room->beds->map(function($bed) {
                        return [
                            'bed_type_id' => $bed->bed_id,
                            'bed_type' => $bed->room_type ?? 'Standard Bed',
                            'max_occupancy' => $bed->max_occupancy ?? 2,
                            'extra_bed_price' => $bed->extra_bed_price ?? 0,
                            'has_extra_bed' => $bed->extra_bed ? true : false,
                        ];
                    })->toArray();
                } else {
                    // Default bed type if no beds defined
                    $room->bed_types = [
                        [
                            'bed_type_id' => $room->room_id,
                            'bed_type' => 'Standard Bed',
                            'max_occupancy' => 2,
                            'extra_bed_price' => 0,
                            'has_extra_bed' => false,
                        ]
                    ];
                }
                // Remove the beds relation to keep response clean
                unset($room->beds);
            });
        });
        
        return response()->json([
            'success' => true,
            'hotels' => $hotels,
            'dmc_id' => $dmc_id,
            'destination' => $destination
        ]);
    }
    
    /**
     * Get attractions by destination (AJAX)
     */
    public function getAttractionsByDestination(Request $request)
    {
        $user = auth()->user();
        $destination = $request->input('destination');
        
        if (!$destination) {
            return response()->json([
                'success' => false,
                'message' => 'Destination is required',
                'attractions' => []
            ], 400);
        }
        
        // Get DMC ID based on user role
        $dmc_id = null;
        if ($user->role_id == 11) {
            $dmc_id = $user->userId;
        } elseif (in_array($user->role_id, [33, 34, 35, 77, 78, 84, 120, 128, 129, 130, 131, 132, 133, 134, 135, 136, 137, 138, 139, 140])) {
            $dmc_id = $user->created_by;
        } elseif (in_array($user->role_id, [37, 64, 65, 66, 67, 68])) {
            $sales_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $sales_head ? $sales_head->created_by : null;
        } elseif (in_array($user->role_id, [38, 81, 90, 108, 117, 124, 125, 126, 127])) {
            $sales_manager = User::where('userId', $user->created_by)->first();
            if ($sales_manager) {
                $sales_head = User::where('userId', $sales_manager->created_by)->first();
                $dmc_id = $sales_head ? $sales_head->created_by : null;
            }
        }
        
        \Log::info('getAttractionsByDestination - DMC ID determined', [
            'dmc_id' => $dmc_id,
            'user_id' => $user->userId,
            'role_id' => $user->role_id,
            'destination' => $destination
        ]);
        
        // Get attractions for this DMC and destination
        $attractionsQuery = Attraction::where('status', 1)
            ->where('is_active', 1)
            ->where('location', $destination);
        
        if ($dmc_id) {
            $attractionsQuery->whereJsonContains('dmc_id', (int) $dmc_id);
        }
        
        $attractions = $attractionsQuery
            ->select('attraction_id as id', 'name', 'location', 'country', 'open_time', 'close_time', 
                     'adult_price', 'child_price', 'senior_adult_price', 'zone_assignments')
            ->orderBy('name')
            ->get();
        
        // Add zone_id to each attraction
        $attractions->each(function($attraction) use ($dmc_id) {
            $attraction->zone_id = $dmc_id ? $attraction->getZoneForDmc($dmc_id) : null;
        });
        
        \Log::info('getAttractionsByDestination - Attractions found', [
            'count' => $attractions->count(),
            'attraction_ids' => $attractions->pluck('id')->toArray(),
            'attraction_names' => $attractions->pluck('name')->toArray(),
            'prices' => $attractions->map(function($a) {
                return [
                    'id' => $a->id,
                    'name' => $a->name,
                    'adult_price' => $a->adult_price,
                    'child_price' => $a->child_price
                ];
            })->toArray()
        ]);
        
        return response()->json([
            'success' => true,
            'attractions' => $attractions,
            'count' => $attractions->count(),
            'dmc_id' => $dmc_id,
            'destination' => $destination
        ]);
    }
    
    /**
     * Get guides by destination (AJAX)
     */
    public function getGuidesByDestination(Request $request)
    {
        try {
            $user = auth()->user();
            $destination = $request->input('destination');
            
            \Log::info('Guide request received', [
                'destination' => $destination,
                'user_id' => $user->userId,
                'role_id' => $user->role_id
            ]);
            
            if (!$destination) {
                return response()->json([
                    'success' => false,
                    'message' => 'Destination is required',
                    'guides' => []
                ], 400);
            }
            
            // Get DMC ID based on user role
            $dmc_id = null;
            if ($user->role_id == 11) {
                $dmc_id = $user->userId;
            } elseif (in_array($user->role_id, [33, 34, 35, 77, 78, 84, 120, 128, 129, 130, 131, 132, 133, 134, 135, 136, 137, 138, 139, 140])) {
                $dmc_id = $user->created_by;
            } elseif (in_array($user->role_id, [37, 64, 65, 66, 67, 68])) {
                $sales_head = User::where('userId', $user->created_by)->first();
                $dmc_id = $sales_head ? $sales_head->created_by : null;
            } elseif (in_array($user->role_id, [38, 81, 90, 108, 117, 124, 125, 126, 127])) {
                $sales_manager = User::where('userId', $user->created_by)->first();
                if ($sales_manager) {
                    $sales_head = User::where('userId', $sales_manager->created_by)->first();
                    $dmc_id = $sales_head ? $sales_head->created_by : null;
                }
            }
            
            \Log::info('getGuidesByDestination - DMC ID determined', [
                'dmc_id' => $dmc_id,
                'user_id' => $user->userId,
                'role_id' => $user->role_id,
                'created_by' => $user->created_by ?? null
            ]);
            
            // Get guides for this DMC and destination only
            if (!$dmc_id) {
                // If no DMC ID, return empty collection
                return response()->json([
                    'success' => true,
                    'guides' => [],
                    'count' => 0,
                    'message' => 'No DMC ID found for this user'
                ]);
            }
            
            // Status 1 and 3 are both considered active guides
            $guides = Guide::where('dmc_id', $dmc_id)
                ->whereIn('status', [1, 3])
                ->where('city', $destination)
                ->with('languages')
                ->select('guide_id', 'name', 'city', 'country', 'day_rate', 
                         'hourly_price', 'two_hour_price', 'four_hour_price', 
                         'six_hour_price', 'eight_hour_price', 'ten_hour_price', 'twelve_hour_price')
                ->orderBy('name')
                ->get();
            
            \Log::info('Guides found', ['count' => $guides->count()]);
            
            // Format guides with languages
            $guidesData = $guides->map(function($guide) {
                return [
                    'guide_id' => $guide->guide_id,
                    'name' => $guide->name,
                    'city' => $guide->city,
                    'country' => $guide->country,
                    'day_rate' => $guide->day_rate ?? $guide->twelve_hour_price ?? 0,
                    'hourly_price' => $guide->hourly_price ?? 0,
                    'two_hour_price' => $guide->two_hour_price ?? 0,
                    'four_hour_price' => $guide->four_hour_price ?? 0,
                    'six_hour_price' => $guide->six_hour_price ?? 0,
                    'eight_hour_price' => $guide->eight_hour_price ?? 0,
                    'ten_hour_price' => $guide->ten_hour_price ?? 0,
                    'twelve_hour_price' => $guide->twelve_hour_price ?? 0,
                    'languages' => $guide->languages->map(function($lang) {
                        return [
                            'language' => $lang->language,
                            'proficiency' => $lang->proficiency
                        ];
                    })
                ];
            });
            
            return response()->json([
                'success' => true,
                'guides' => $guidesData,
                'count' => $guides->count()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching guides', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading guides: ' . $e->getMessage(),
                'guides' => []
            ], 500);
        }
    }
    
    /**
     * Store the pro form data - create tour and orders
     */
    public function store(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'destination' => 'required|string',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after:start_date',
                'adults' => 'required|integer|min:0',
                'children' => 'required|integer|min:0',
                'infants' => 'required|integer|min:0',
                'agent_id' => 'required|exists:agents,agent_id',
                'agency_id' => 'required|exists:agencies,agency_id',
                'markup_value' => 'nullable|numeric|min:0',
                'markup_type' => 'nullable|string|in:percentage,flat',
                'discount_value' => 'nullable|numeric|min:0',
                'discount_type' => 'nullable|string|in:percentage,flat,',
            ]);
            
            // Get markup and discount values
            $markupValue = $request->input('markup_value', 0);
            $markupType = $request->input('markup_type', 'percentage');
            $discountValue = $request->input('discount_value', 0);
            $discountType = $request->input('discount_type', '');
            
            DB::beginTransaction();
            
            // Parse the dates
            $checkInTime = Carbon::createFromFormat('Y-m-d', $request->start_date);
            $checkOutTime = Carbon::createFromFormat('Y-m-d', $request->end_date);
            
            // Generate tour ID
            $max_tour_id = Tour::max('tour_id') ?? 0;
            $tourId = CommonHelper::createId($max_tour_id);
            $display_id = 'DMC-ORD' . $tourId;
            
            // Get DMC ID based on user role (same logic as create method)
            $user = Auth::user();
            $dmcId = null;
            if ($user->role_id == 11) {
                $dmcId = $user->userId;
            } elseif (in_array($user->role_id, [33, 34, 35, 77, 78, 84, 120, 128, 129, 130, 131, 132, 133, 134, 135, 136, 137, 138, 139, 140])) {
                $dmcId = $user->created_by;
            } elseif (in_array($user->role_id, [37, 64, 65, 66, 67, 68])) {
                $sales_head = User::where('userId', $user->created_by)->first();
                $dmcId = $sales_head ? $sales_head->created_by : null;
            } elseif (in_array($user->role_id, [38, 81, 90, 108, 117, 124, 125, 126, 127])) {
                $sales_manager = User::where('userId', $user->created_by)->first();
                if ($sales_manager) {
                    $sales_head = User::where('userId', $sales_manager->created_by)->first();
                    $dmcId = $sales_head ? $sales_head->created_by : null;
                }
            }
            
            // Fallback to created_by if no DMC ID determined
            if (!$dmcId) {
                $dmcId = $user->created_by;
            }
            
            // Get DMC taxes
            $taxArray = [];
            if ($dmcId) {
                $taxes = Tax::where('dmc_id', $dmcId)
                    ->where('is_active', 1)
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                foreach ($taxes as $tax) {
                    $taxArray[] = [
                        'tax_id' => $tax->tax_id,
                        'tax_name' => $tax->tax_name,
                        'tax_type' => $tax->tax_type,
                        'tax_value' => $tax->tax_value,
                        'calculate_on' => $tax->calculate_on,
                        'description' => $tax->description ?? '',
                        'if_fixed' => $tax->if_fixed ?? null,
                    ];
                }
            }
            
            // Calculate auto cancel date
            $userDMC = \App\Models\User::where('userId', $dmcId)->first();
            $auto_cancel_day = (int) ($userDMC->auto_cancel_date ?? 1);
            $auto_cancel_date = $checkInTime->copy()->subDays($auto_cancel_day)->toDateString();
            
            // Create tour record
            $tour = new Tour();
            $tour->destination = $request->destination;
            $tour->adult = $request->adults;
            $tour->child = $request->children;
            $tour->infant = $request->infants;
            $tour->agent_id = $request->agent_id;
            $tour->tour_id = $tourId;
            $tour->male_count = $request->male ?? 0;
            $tour->female_count = $request->female ?? 0;
            $tour->check_in_time = $checkInTime;
            $tour->check_out_time = $checkOutTime;
            $tour->display_id = $display_id;
            $tour->tour_status = "New Enquiry";
            $tour->city = $request->city ?? null;
            $tour->dmc_id = $dmcId;
            $tour->child_ages = $request->child_ages ?? null;
            $tour->auto_cancel_date = $auto_cancel_date;
            $tour->taxes = !empty($taxArray) ? json_encode($taxArray) : null;
            $tour->is_pro = 1; // Set to 1 for Pro Enquiry Form
            $tour->save();
            
            \Log::info('Tour created', [
                'tour_id' => $tourId,
                'display_id' => $display_id,
                'agent_id' => $request->agent_id
            ]);
            
            // Determine booking type
            $bookingType = 'enquiry'; // Pro form always creates enquiries
            
            // Create orders for each service type
            $createdOrders = [];
            
            // Track created transfers and guides to avoid duplicates
            $createdTransferIds = [];
            $createdGuideIds = [];
            
            // 0. Entry Port Orders (Arrival)
            if ($request->has('entry_port') && !empty($request->entry_port)) {
                $entryPorts = json_decode($request->entry_port, true);
                
                // Track unique entries to prevent duplicates within the same request
                $seenEntries = [];
                
                foreach ($entryPorts as $entryPort) {
                    // Create a unique identifier for this entry (based on key data fields)
                    $uniqueKey = md5(json_encode([
                        'port_id' => $entryPort['port_id'] ?? '',
                        'port_name' => $entryPort['port_name'] ?? '',
                        'bookingDate' => $entryPort['bookingDate'] ?? '',
                        'type' => $entryPort['type'] ?? ''
                    ]));
                    
                    // Skip if we've already processed this exact entry
                    if (in_array($uniqueKey, $seenEntries)) {
                        \Log::info('Skipping duplicate entry_port within create request', ['unique_key' => $uniqueKey]);
                        continue;
                    }
                    $seenEntries[] = $uniqueKey;
                    
                    // Get vehicle details from database if vehicle_id exists
                    if (!empty($entryPort['vehicle_id'])) {
                        $vehicleDetails = $this->getVehicleDetails($entryPort['vehicle_id']);
                        if ($vehicleDetails) {
                            $entryPort['vehicle_id'] = $vehicleDetails['vehicle_id'];
                            $entryPort['vehicles_name'] = $vehicleDetails['vehicles_name'];
                            $entryPort['vehicle_type'] = $vehicleDetails['vehicle_type'];
                            $entryPort['vehicle_model'] = $vehicleDetails['vehicle_model'];
                            $entryPort['model_year'] = $vehicleDetails['model_year'];
                            $entryPort['seating_capacity'] = $vehicleDetails['seating_capacity'];
                            $entryPort['image'] = $vehicleDetails['image'];
                        }
                    }
                    
                    // Normalize transfer type
                    if (!empty($entryPort['type'])) {
                        $entryPort['type'] = $this->normalizeTransferType($entryPort['type']);
                    }
                    
                    $bookingId = $this->generateBookingId();
                    
                    // Add tour_id to the JSON data
                    $entryPort['tour_id'] = $tourId;
                    
                    $order = Order::create([
                        'booking_id' => $bookingId,
                        'agent_id' => $request->agent_id,
                        'tour_id' => $tourId,
                        'data' => [$entryPort],
                        'type' => 'entry_port',
                        'bookingType' => $bookingType,
                        'discount' => $discountValue,
                        'discount_type' => $discountType,
                        'markup_percentage' => $markupValue,
                        'markup_type' => $markupType,
                        'status' => 1,
                    ]);
                    
                    $createdOrders[] = [
                        'type' => 'entry_port',
                        'booking_id' => $bookingId,
                        'service_date' => $entryPort['bookingDate'] ?? null
                    ];
                }
            }
            
            // 0b. Exit Port Orders (Departure)
            if ($request->has('exit_port') && !empty($request->exit_port)) {
                $exitPorts = json_decode($request->exit_port, true);
                
                // Track unique exits to prevent duplicates within the same request
                $seenExits = [];
                
                foreach ($exitPorts as $exitPort) {
                    // Create a unique identifier for this exit (based on key data fields)
                    $uniqueKey = md5(json_encode([
                        'port_id' => $exitPort['port_id'] ?? '',
                        'port_name' => $exitPort['port_name'] ?? '',
                        'bookingDate' => $exitPort['bookingDate'] ?? '',
                        'type' => $exitPort['type'] ?? ''
                    ]));
                    
                    // Skip if we've already processed this exact exit
                    if (in_array($uniqueKey, $seenExits)) {
                        \Log::info('Skipping duplicate exit_port within create request', ['unique_key' => $uniqueKey]);
                        continue;
                    }
                    $seenExits[] = $uniqueKey;
                    
                    // Get vehicle details from database if vehicle_id exists
                    if (!empty($exitPort['vehicle_id'])) {
                        $vehicleDetails = $this->getVehicleDetails($exitPort['vehicle_id']);
                        if ($vehicleDetails) {
                            $exitPort['vehicle_id'] = $vehicleDetails['vehicle_id'];
                            $exitPort['vehicles_name'] = $vehicleDetails['vehicles_name'];
                            $exitPort['vehicle_type'] = $vehicleDetails['vehicle_type'];
                            $exitPort['vehicle_model'] = $vehicleDetails['vehicle_model'];
                            $exitPort['model_year'] = $vehicleDetails['model_year'];
                            $exitPort['seating_capacity'] = $vehicleDetails['seating_capacity'];
                            $exitPort['image'] = $vehicleDetails['image'];
                        }
                    }
                    
                    // Normalize transfer type
                    if (!empty($exitPort['type'])) {
                        $exitPort['type'] = $this->normalizeTransferType($exitPort['type']);
                    }
                    
                    $bookingId = $this->generateBookingId();
                    
                    // Add tour_id to the JSON data
                    $exitPort['tour_id'] = $tourId;
                    
                    $order = Order::create([
                        'booking_id' => $bookingId,
                        'agent_id' => $request->agent_id,
                        'tour_id' => $tourId,
                        'data' => [$exitPort],
                        'type' => 'exit_port',
                        'bookingType' => $bookingType,
                        'discount' => $discountValue,
                        'discount_type' => $discountType,
                        'markup_percentage' => $markupValue,
                        'markup_type' => $markupType,
                        'status' => 1,
                    ]);
                    
                    $createdOrders[] = [
                        'type' => 'exit_port',
                        'booking_id' => $bookingId,
                        'service_date' => $exitPort['bookingDate'] ?? null
                    ];
                }
            }
            
            // 1. Accommodation Orders
            if ($request->has('accommodations') && !empty($request->accommodations)) {
                $accommodations = json_decode($request->accommodations, true);
                \Log::info('Processing accommodations', ['count' => count($accommodations)]);
                
                foreach ($accommodations as $accommodation) {
                    \Log::info('Accommodation data', [
                        'has_transfer_options' => isset($accommodation['transfer_options']),
                        'transfer_options' => $accommodation['transfer_options'] ?? null
                    ]);
                    $bookingId = $this->generateBookingId();
                    
                    // Add tour_id to the JSON data
                    $accommodation['tour_id'] = $tourId;
                    
                    $order = Order::create([
                        'booking_id' => $bookingId,
                        'agent_id' => $request->agent_id,
                        'tour_id' => $tourId,
                        'data' => [$accommodation],
                        'type' => 'hotel',
                        'bookingType' => $bookingType,
                        'discount' => $discountValue,
                        'discount_type' => $discountType,
                        'markup_percentage' => $markupValue,
                        'markup_type' => $markupType,
                        'status' => 1,
                    ]);
                    
                    $createdOrders[] = [
                        'type' => 'hotel',
                        'booking_id' => $bookingId,
                        'service_date' => $accommodation['checkIn'] ?? null
                    ];
                    
                    // DISABLED: AUTO-CREATE LOCAL TRANSPORT IF TRANSFER IS OPTED
                    // Note: Hotel-linked transfers are now handled through the transfers array (section 4)
                    // This prevents duplicates - the transfers array contains the correct data
                    // The auto-created transfer was the first (wrong) one, the transfers array has the second (correct) one
                    /*
                    if (isset($accommodation['transfer_options']) && !empty($accommodation['transfer_options']) && ($accommodation['transfer_options']['transfer_required'] ?? false)) {
                        // Auto-create logic disabled - transfers are handled via transfers array
                    }
                    */
                }
            }
            
            // 2. Tour/Attraction Orders
            if ($request->has('tours') && !empty($request->tours)) {
                $tours = json_decode($request->tours, true);
                \Log::info('Processing attractions', ['count' => count($tours)]);
                
                foreach ($tours as $tourItem) {
                    \Log::info('Attraction data', [
                        'has_transfer_options' => isset($tourItem['transfer_options']),
                        'has_guide_options' => isset($tourItem['guide_options']),
                        'transfer_options' => $tourItem['transfer_options'] ?? null,
                        'guide_options' => $tourItem['guide_options'] ?? null
                    ]);
                    $bookingId = $this->generateBookingId();
                    
                    // Add tour_id to the JSON data
                    $tourItem['tour_id'] = $tourId;
                    
                    $order = Order::create([
                        'booking_id' => $bookingId,
                        'agent_id' => $request->agent_id,
                        'tour_id' => $tourId,
                        'data' => [$tourItem],
                        'type' => 'attraction',
                        'bookingType' => $bookingType,
                        'discount' => $discountValue,
                        'discount_type' => $discountType,
                        'markup_percentage' => $markupValue,
                        'markup_type' => $markupType,
                        'status' => 1,
                    ]);
                    
                    $createdOrders[] = [
                        'type' => 'attraction',
                        'booking_id' => $bookingId,
                        'service_date' => $tourItem['dateTime'] ?? null
                    ];
                    
                    // DISABLED: AUTO-CREATE LOCAL TRANSPORT IF TRANSFER IS OPTED
                    // Note: Transfer options are still saved within attraction data for proforma display
                    // This code is commented out to prevent creating separate local_transport orders
                    // Uncomment if you want to create separate local_transport orders for attractions in the future
                    /*
                    if (isset($tourItem['transfer_options']) && !empty($tourItem['transfer_options']) && ($tourItem['transfer_options']['transfer_required'] ?? false)) {
                        $transferOptions = $tourItem['transfer_options'];
                        $transferDate = $tourItem['bookingDate'] ?? date('Y-m-d');
                        
                        // Create unique transfer identifier
                        $transferIdentifier = md5(
                            ($transferOptions['pickup_location_name'] ?? '') . 
                            ($tourItem['AttractionName'] ?? '') . 
                            $transferDate . 
                            ($tourItem['visitTime'] ?? '10:00 AM')
                        );
                        
                        // Only create transfer if not already created
                        if (!in_array($transferIdentifier, $createdTransferIds)) {
                            $createdTransferIds[] = $transferIdentifier;
                            
                            // Get vehicle details from database
                            $vehicleDetails = $this->getVehicleDetails($transferOptions['vehicle_id'] ?? null);
                            
                            // Normalize transfer type
                            $transferType = $this->normalizeTransferType($transferOptions['type'] ?? 'Private');
                        
                        $localTransportData = [
                            'bookingDate' => $transferDate,
                            'vehicle_id' => $vehicleDetails['vehicle_id'] ?? ($transferOptions['vehicle_id'] ?? ''),
                            'vehicles_name' => $vehicleDetails['vehicles_name'] ?? '',
                            'vehicle_type' => $vehicleDetails['vehicle_type'] ?? '',
                            'vehicle_model' => $vehicleDetails['vehicle_model'] ?? '',
                            'model_year' => $vehicleDetails['model_year'] ?? '',
                            'seating_capacity' => $vehicleDetails['seating_capacity'] ?? 0,
                            'dmc_id' => (string) ($request->user()->created_by ?? $dmcId ?? ''),
                            'image' => $vehicleDetails['image'] ?? '',
                            'Mode' => 'dmc',
                            'type' => $transferType,
                            'entrypickup' => $transferOptions['pickup_location_name'] ?? '',
                            'entrydropoff' => $tourItem['AttractionName'] ?? '',
                            'PickupPlaceid' => '',
                            'DropoffPlaceid' => '',
                            'pickupdate' => $transferDate,
                            'entrytime' => $tourItem['visitTime'] ?? '10:00 AM',
                            'adults' => (string) ($tourItem['adultCount'] ?? 2),
                            'children' => (string) ($tourItem['childCount'] ?? 0),
                            'totalPrice' => (string) ($transferOptions['cost'] ?? 0),
                            'to_zone_id' => '',
                            'from_zone_id' => '',
                            'city' => 'Singapore',
                            'country' => 'Singapore',
                            'fullName' => $tourItem['fullName'] ?? 'Guest User',
                            'email' => $tourItem['email'] ?? 'guest@example.com',
                            'phone' => $tourItem['phone'] ?? '0000000000',
                            'countryCode' => $tourItem['countryCode'] ?? '65',
                            'address1' => $tourItem['address1'] ?? '',
                            'address2' => $tourItem['address2'] ?? '',
                            'state' => $tourItem['state'] ?? '',
                            'zip' => $tourItem['zip'] ?? '',
                            'specialRequests' => $tourItem['specialRequests'] ?? '',
                            'userInfo' => [
                                'fullName' => $tourItem['fullName'] ?? 'Guest User',
                                'email' => $tourItem['email'] ?? 'guest@example.com',
                                'phone' => $tourItem['phone'] ?? '0000000000',
                                'countryCode' => $tourItem['countryCode'] ?? '65',
                                'address1' => $tourItem['address1'] ?? ''
                            ],
                            'bookingType' => 'enquiry',
                            'linked_to_attraction' => $bookingId
                        ];
                        
                        $transportBookingId = $this->generateBookingId();
                        Order::create([
                            'booking_id' => $transportBookingId,
                            'agent_id' => $request->agent_id,
                            'tour_id' => $tourId,
                            'data' => [$localTransportData],
                            'type' => 'local_transport',
                            'bookingType' => $bookingType,
                            'discount' => $discountValue,
                            'discount_type' => $discountType,
                            'markup_percentage' => $markupValue,
                            'markup_type' => $markupType,
                            'status' => 1,
                        ]);
                        
                            $createdOrders[] = ['type' => 'local_transport', 'booking_id' => $transportBookingId, 'linked_to' => 'attraction'];
                        } else {
                            \Log::info('Skipped duplicate local_transport for attraction', ['transfer_identifier' => $transferIdentifier]);
                        }
                    }
                    */
                    
                    // DISABLED: AUTO-CREATE GUIDE IF GUIDE IS OPTED
                    // Note: Guide options are still saved within attraction data for proforma display
                    // This code is commented out to prevent creating separate guide orders
                    // Uncomment if you want to create separate guide orders for attractions in the future
                    /*
                    if (isset($tourItem['guide_options']) && !empty($tourItem['guide_options']) && ($tourItem['guide_options']['guide_required'] ?? false)) {
                        $guideOptions = $tourItem['guide_options'];
                        $guideDate = $tourItem['bookingDate'] ?? date('Y-m-d');
                        
                        // Create unique guide identifier
                        $guideIdentifier = md5(
                            ($guideOptions['guide_id'] ?? '') . 
                            ($tourItem['AttractionName'] ?? '') . 
                            $guideDate . 
                            ($guideOptions['hours'] ?? 2)
                        );
                        
                        // Only create guide if not already created
                        if (!in_array($guideIdentifier, $createdGuideIds)) {
                            $createdGuideIds[] = $guideIdentifier;
                        
                        $guideData = [
                            'Mode' => 'dmc',
                            'dmc_Id' => (string) ($request->user()->created_by ?? $dmcId ?? ''),
                            'fullName' => $tourItem['fullName'] ?? 'Guest User',
                            'email' => $tourItem['email'] ?? 'guest@example.com',
                            'phone' => $tourItem['phone'] ?? '0000000000',
                            'countryCode' => $tourItem['countryCode'] ?? '65',
                            'address1' => $tourItem['address1'] ?? '',
                            'address2' => $tourItem['address2'] ?? '',
                            'state' => $tourItem['state'] ?? '',
                            'zip' => $tourItem['zip'] ?? '',
                            'specialRequests' => $tourItem['specialRequests'] ?? '',
                            'guide_id' => intval($guideOptions['guide_id'] ?? 0),
                            'guide_name' => $guideOptions['guide_name'] ?? 'Guide',
                            'image' => '',
                            'entrytime' => intval($guideOptions['hours'] ?? 2),
                            'adults' => intval($tourItem['adultCount'] ?? 0),
                            'children' => intval($tourItem['childCount'] ?? 0),
                            'hours' => intval($guideOptions['hours'] ?? 2),
                            'basePrice' => floatval($guideOptions['base_price'] ?? 0),
                            'surcharge' => 0,
                            'totalPrice' => floatval($guideOptions['total_price'] ?? 0),
                            'pickupdate' => $guideDate,
                            'bookingDate' => $guideDate,
                            'dayIndex' => 1,
                            'Tax' => '7.00',
                            'city' => 'Singapore',
                            'country' => 'Singapore',
                            'languages' => [],
                            'experience' => 0,
                            'price' => floatval($guideOptions['total_price'] ?? 0),
                            'booking_id' => 0,
                            'linked_to_attraction' => $bookingId
                        ];
                        
                        $guideBookingId = $this->generateBookingId();
                        Order::create([
                            'booking_id' => $guideBookingId,
                            'agent_id' => $request->agent_id,
                            'tour_id' => $tourId,
                            'data' => [$guideData],
                            'type' => 'guide',
                            'bookingType' => $bookingType,
                            'discount' => $discountValue,
                            'discount_type' => $discountType,
                            'markup_percentage' => $markupValue,
                            'markup_type' => $markupType,
                            'status' => 1,
                        ]);
                        
                            $createdOrders[] = ['type' => 'guide', 'booking_id' => $guideBookingId, 'linked_to' => 'attraction'];
                        } else {
                            \Log::info('Skipped duplicate guide for attraction', ['guide_identifier' => $guideIdentifier]);
                        }
                    }
                    */
                }
            }
            
            // 3. Meal/Restaurant Orders
            if ($request->has('meals') && !empty($request->meals)) {
                $meals = json_decode($request->meals, true);
                \Log::info('Processing meals/restaurants', ['count' => count($meals)]);
                
                foreach ($meals as $meal) {
                    \Log::info('Restaurant data', [
                        'has_transfer_options' => isset($meal['transfer_options']),
                        'transfer_options' => $meal['transfer_options'] ?? null,
                        'has_guide_info' => isset($meal['guideInfo']),
                        'guide_info' => $meal['guideInfo'] ?? null
                    ]);
                    $bookingId = $this->generateBookingId();
                    
                    // Add tour_id to the JSON data
                    $meal['tour_id'] = $tourId;
                    
                    $order = Order::create([
                        'booking_id' => $bookingId,
                        'agent_id' => $request->agent_id,
                        'tour_id' => $tourId,
                        'data' => [$meal],
                        'type' => 'restaurant',
                        'bookingType' => $bookingType,
                        'discount' => $discountValue,
                        'discount_type' => $discountType,
                        'markup_percentage' => $markupValue,
                        'markup_type' => $markupType,
                        'status' => 1,
                    ]);
                    
                    $createdOrders[] = [
                        'type' => 'restaurant',
                        'booking_id' => $bookingId,
                        'service_date' => $meal['bookingDate'] ?? null
                    ];
                    
                    // AUTO-CREATE GUIDE IF GUIDE IS OPTED FOR RESTAURANT
                    if (isset($meal['guideInfo']) && !empty($meal['guideInfo']) && isset($meal['guideId'])) {
                        $guideInfo = $meal['guideInfo'];
                        $guideDate = $meal['bookingDate'] ?? date('Y-m-d');
                        
                        // Create unique guide identifier to prevent duplicates
                        $guideIdentifier = md5(
                            ($meal['guideId'] ?? '') . 
                            ($meal['restaurantName'] ?? '') . 
                            $guideDate . 
                            'restaurant'
                        );
                        
                        // Only create guide if not already created
                        if (!in_array($guideIdentifier, $createdGuideIds)) {
                            $createdGuideIds[] = $guideIdentifier;
                        
                            $guideData = [
                                'tour_id' => $tourId,
                                'Mode' => 'dmc',
                                'dmc_Id' => (string) ($request->user()->created_by ?? $dmcId ?? ''),
                                'fullName' => $meal['fullName'] ?? 'Guest User',
                                'email' => $meal['email'] ?? 'guest@example.com',
                                'phone' => $meal['phone'] ?? '0000000000',
                                'countryCode' => $meal['countryCode'] ?? '65',
                                'address1' => $meal['address1'] ?? '',
                                'address2' => $meal['address2'] ?? '',
                                'state' => $meal['state'] ?? '',
                                'zip' => $meal['zip'] ?? '',
                                'specialRequests' => $meal['specialRequests'] ?? '',
                                'guide_id' => intval($meal['guideId'] ?? 0),
                                'guide_name' => $guideInfo['guideName'] ?? 'Guide',
                                'image' => '',
                                'entrytime' => 2, // Default 2 hours for restaurant guide
                                'adults' => intval($meal['adultCount'] ?? 0),
                                'children' => intval($meal['childCount'] ?? 0),
                                'hours' => 2, // Default 2 hours
                                'basePrice' => 0,
                                'surcharge' => 0,
                                'totalPrice' => 0,
                                'pickupdate' => $guideDate,
                                'bookingDate' => $guideDate,
                                'dayIndex' => 1,
                                'Tax' => '7.00',
                                'city' => 'Singapore',
                                'country' => 'Singapore',
                                'languages' => $guideInfo['languages'] ? explode(', ', $guideInfo['languages']) : [],
                                'experience' => 0,
                                'price' => 0,
                                'booking_id' => 0,
                                'linked_to_restaurant' => $bookingId
                            ];
                            
                            $guideBookingId = $this->generateBookingId();
                            Order::create([
                                'booking_id' => $guideBookingId,
                                'agent_id' => $request->agent_id,
                                'tour_id' => $tourId,
                                'data' => [$guideData],
                                'type' => 'guide',
                                'bookingType' => $bookingType,
                                'discount' => $discountValue,
                                'discount_type' => $discountType,
                                'markup_percentage' => $markupValue,
                                'markup_type' => $markupType,
                                'status' => 1,
                            ]);
                            
                            $createdOrders[] = ['type' => 'guide', 'booking_id' => $guideBookingId, 'linked_to' => 'restaurant'];
                            
                            \Log::info('Created guide order for restaurant', [
                                'guide_identifier' => $guideIdentifier,
                                'guide_name' => $guideInfo['guideName'] ?? 'Guide',
                                'restaurant_name' => $meal['restaurantName'] ?? ''
                            ]);
                        } else {
                            \Log::info('Skipped duplicate guide for restaurant', ['guide_identifier' => $guideIdentifier]);
                        }
                    }
                    
                    // DISABLED: AUTO-CREATE LOCAL TRANSPORT IF TRANSFER IS OPTED
                    // Note: Transfer options are still saved within restaurant data for proforma display
                    // This code is commented out to prevent creating separate local_transport orders
                    // Uncomment if you want to create separate local_transport orders for restaurants in the future
                    /*
                    if (isset($meal['transfer_options']) && !empty($meal['transfer_options']) && ($meal['transfer_options']['transfer_required'] ?? false)) {
                        $transferOptions = $meal['transfer_options'];
                        
                        // Create unique transfer identifier based on pickup, dropoff, date, and time
                        $transferIdentifier = md5(
                            ($transferOptions['pickup_location_name'] ?? '') . 
                            ($meal['restaurantName'] ?? '') . 
                            ($meal['bookingDate'] ?? '') . 
                            ($meal['visitTime'] ?? '')
                        );
                        
                        // Only create transfer if not already created
                        if (!in_array($transferIdentifier, $createdTransferIds)) {
                            $createdTransferIds[] = $transferIdentifier;
                            
                            $transferDate = $meal['bookingDate'] ?? date('Y-m-d');
                            
                            // Get vehicle details from database
                            $vehicleDetails = $this->getVehicleDetails($transferOptions['vehicle_id'] ?? null);
                            
                            // Normalize transfer type
                            $transferType = $this->normalizeTransferType($transferOptions['type'] ?? 'Private');
                            
                            $localTransportData = [
                                'bookingDate' => $transferDate,
                                'vehicle_id' => $vehicleDetails['vehicle_id'] ?? ($transferOptions['vehicle_id'] ?? ''),
                                'vehicles_name' => $vehicleDetails['vehicles_name'] ?? '',
                                'vehicle_type' => $vehicleDetails['vehicle_type'] ?? '',
                                'vehicle_model' => $vehicleDetails['vehicle_model'] ?? '',
                                'model_year' => $vehicleDetails['model_year'] ?? '',
                                'seating_capacity' => $vehicleDetails['seating_capacity'] ?? 0,
                                'dmc_id' => (string) ($request->user()->created_by ?? $dmcId ?? ''),
                                'image' => $vehicleDetails['image'] ?? '',
                                'Mode' => 'dmc',
                                'type' => $transferType,
                                'entrypickup' => $transferOptions['pickup_location_name'] ?? '',
                                'entrydropoff' => $meal['restaurantName'] ?? '',
                                'PickupPlaceid' => '',
                                'DropoffPlaceid' => '',
                                'pickupdate' => $transferDate,
                                'entrytime' => $meal['visitTime'] ?? '12:00 PM',
                                'adults' => (string) ($meal['adultCount'] ?? 2),
                                'children' => (string) ($meal['childCount'] ?? 0),
                                'totalPrice' => (string) ($transferOptions['cost'] ?? 0),
                                'to_zone_id' => '',
                                'from_zone_id' => '',
                                'city' => 'Singapore',
                                'country' => 'Singapore',
                                'fullName' => $meal['fullName'] ?? 'Guest User',
                                'email' => $meal['email'] ?? 'guest@example.com',
                                'phone' => $meal['phone'] ?? '0000000000',
                                'countryCode' => $meal['countryCode'] ?? '65',
                                'address1' => $meal['address1'] ?? '',
                                'address2' => $meal['address2'] ?? '',
                                'state' => $meal['state'] ?? '',
                                'zip' => $meal['zip'] ?? '',
                                'specialRequests' => $meal['specialRequests'] ?? '',
                                'userInfo' => [
                                    'fullName' => $meal['fullName'] ?? 'Guest User',
                                    'email' => $meal['email'] ?? 'guest@example.com',
                                    'phone' => $meal['phone'] ?? '0000000000',
                                    'countryCode' => $meal['countryCode'] ?? '65',
                                    'address1' => $meal['address1'] ?? ''
                                ],
                                'bookingType' => 'enquiry',
                                'linked_to_restaurant' => $bookingId
                            ];
                            
                            $transportBookingId = $this->generateBookingId();
                            Order::create([
                                'booking_id' => $transportBookingId,
                                'agent_id' => $request->agent_id,
                                'tour_id' => $tourId,
                                'data' => [$localTransportData],
                                'type' => 'local_transport',
                                'bookingType' => $bookingType,
                                'discount' => $discountValue,
                                'discount_type' => $discountType,
                                'markup_percentage' => $markupValue,
                                'markup_type' => $markupType,
                                'status' => 1,
                            ]);
                            
                            $createdOrders[] = ['type' => 'local_transport', 'booking_id' => $transportBookingId, 'linked_to' => 'restaurant'];
                            
                            \Log::info('Created unique local_transport for restaurant', [
                                'transfer_identifier' => $transferIdentifier,
                                'pickup' => $transferOptions['pickup_location_name'] ?? '',
                                'dropoff' => $meal['restaurantName'] ?? ''
                            ]);
                        } else {
                            \Log::info('Skipped duplicate local_transport for restaurant', [
                                'transfer_identifier' => $transferIdentifier
                            ]);
                        }
                    }
                    */
                }
            }
            
            // 4. Transfer Orders (Local Transport)
            if ($request->has('transfers') && !empty($request->transfers)) {
                $transfers = json_decode($request->transfers, true);
                foreach ($transfers as $transfer) {
                    // Add tour_id to the JSON data
                    $transfer['tour_id'] = $tourId;
                    
                    // Get vehicle details from database if vehicle_id exists
                    if (!empty($transfer['vehicle_id'])) {
                        $vehicleDetails = $this->getVehicleDetails($transfer['vehicle_id']);
                        if ($vehicleDetails) {
                            $transfer['vehicle_id'] = $vehicleDetails['vehicle_id'];
                            $transfer['vehicles_name'] = $vehicleDetails['vehicles_name'];
                            $transfer['vehicle_type'] = $vehicleDetails['vehicle_type'];
                            $transfer['vehicle_model'] = $vehicleDetails['vehicle_model'];
                            $transfer['model_year'] = $vehicleDetails['model_year'];
                            $transfer['seating_capacity'] = $vehicleDetails['seating_capacity'];
                            $transfer['image'] = $vehicleDetails['image'];
                        }
                    }
                    
                    // Normalize transfer type
                    if (!empty($transfer['type'])) {
                        $transfer['type'] = $this->normalizeTransferType($transfer['type']);
                    }
                    
                    // CRITICAL FIX: Validate and sanitize zone IDs - only use integer zone IDs, not Place IDs
                    // Helper function to check if a value is a valid zone ID (integer) or a Place ID (contains dot)
                    $isValidZoneId = function($value) {
                        if (empty($value) || $value === '') return false;
                        // If it contains a dot, it's likely a Google Place ID, not a zone ID
                        if (strpos((string)$value, '.') !== false) return false;
                        // Check if it's a valid integer
                        return ctype_digit((string)$value) || (is_numeric($value) && (int)$value == $value);
                    };
                    
                    // Sanitize from_zone_id - only keep if it's a valid integer zone ID
                    if (isset($transfer['from_zone_id']) && !empty($transfer['from_zone_id'])) {
                        if (!$isValidZoneId($transfer['from_zone_id'])) {
                            // If it's a Place ID, clear from_zone_id but keep PickupPlaceid
                            if (empty($transfer['PickupPlaceid'])) {
                                $transfer['PickupPlaceid'] = $transfer['from_zone_id'];
                            }
                            $transfer['from_zone_id'] = '';
                        }
                    }
                    
                    // Sanitize to_zone_id - only keep if it's a valid integer zone ID
                    if (isset($transfer['to_zone_id']) && !empty($transfer['to_zone_id'])) {
                        if (!$isValidZoneId($transfer['to_zone_id'])) {
                            // If it's a Place ID, clear to_zone_id but keep DropoffPlaceid
                            if (empty($transfer['DropoffPlaceid'])) {
                                $transfer['DropoffPlaceid'] = $transfer['to_zone_id'];
                            }
                            $transfer['to_zone_id'] = '';
                        }
                    }
                    
                    $bookingId = $this->generateBookingId();
                    
                    $order = Order::create([
                        'booking_id' => $bookingId,
                        'agent_id' => $request->agent_id,
                        'tour_id' => $tourId,
                        'data' => [$transfer],
                        'type' => 'local_transport',
                        'bookingType' => $bookingType,
                        'discount' => $discountValue,
                        'discount_type' => $discountType,
                        'markup_percentage' => $markupValue,
                        'markup_type' => $markupType,
                        'status' => 1,
                    ]);
                    
                    $createdOrders[] = [
                        'type' => 'local_transport',
                        'booking_id' => $bookingId,
                        'service_date' => $transfer['bookingDate'] ?? null
                    ];
                }
            }
            
            // 5. Guide Orders
            if ($request->has('guides') && !empty($request->guides)) {
                $guides = json_decode($request->guides, true);
                foreach ($guides as $guide) {
                    // Add tour_id to the JSON data
                    $guide['tour_id'] = $tourId;
                    
                    $bookingId = $this->generateBookingId();
                    
                    $order = Order::create([
                        'booking_id' => $bookingId,
                        'agent_id' => $request->agent_id,
                        'tour_id' => $tourId,
                        'data' => [$guide],
                        'type' => 'guide',
                        'bookingType' => $bookingType,
                        'discount' => $discountValue,
                        'discount_type' => $discountType,
                        'markup_percentage' => $markupValue,
                        'markup_type' => $markupType,
                        'status' => 1,
                    ]);
                    
                    $createdOrders[] = [
                        'type' => 'guide',
                        'booking_id' => $bookingId,
                        'service_date' => $guide['bookingDate'] ?? null
                    ];
                }
            }
            
            // 6. Miscellaneous Orders
            if ($request->has('miscellaneous') && !empty($request->miscellaneous)) {
                $miscItems = json_decode($request->miscellaneous, true);
                foreach ($miscItems as $miscItem) {
                    // Add tour_id to the JSON data
                    $miscItem['tour_id'] = $tourId;
                    
                    $bookingId = $this->generateBookingId();
                    
                    $order = Order::create([
                        'booking_id' => $bookingId,
                        'agent_id' => $request->agent_id,
                        'tour_id' => $tourId,
                        'data' => [$miscItem],
                        'type' => 'miscellaneous',
                        'bookingType' => $bookingType,
                        'discount' => $discountValue,
                        'discount_type' => $discountType,
                        'markup_percentage' => $markupValue,
                        'markup_type' => $markupType,
                        'status' => 1,
                    ]);
                    
                    $createdOrders[] = [
                        'type' => 'miscellaneous',
                        'booking_id' => $bookingId,
                        'service_date' => $miscItem['bookingDate'] ?? null
                    ];
                }
            }
            
            DB::commit();
            
            // Send tour proposal email
            try {
                $tourData = [
                    'destination' => $tour->destination,
                    'city' => $tour->city,
                    'check_in_time' => $tour->check_in_time,
                    'check_out_time' => $tour->check_out_time,
                    'adult' => $tour->adult,
                    'child' => $tour->child,
                    'infant' => $tour->infant,
                ];
                
                CommonHelper::sendTourProposalEmail(
                    $tour->agent_id,
                    $tour->tour_id,
                    $tour->display_id,
                    $tourData
                );
            } catch (\Exception $e) {
                \Log::error("Exception while sending tour proposal email", [
                    'tour_id' => $tour->tour_id,
                    'agent_id' => $tour->agent_id,
                    'error' => $e->getMessage()
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Tour and services saved successfully!',
                'tour_id' => $tourId,
                'display_id' => $display_id,
                'created_orders' => $createdOrders,
                'total_orders' => count($createdOrders)
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            \Log::error('Error saving pro form', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save tour: ' . $e->getMessage()
            ], 422);
        }
    }
    
    /**
     * Generate unique booking ID
     */
    private function generateBookingId()
    {
        $max_book_id = Order::max('booking_id') ?? 0;
        $bookingId = CommonHelper::createId($max_book_id);
        while (Order::where('booking_id', $bookingId)->exists()) {
            $bookingId = CommonHelper::createId($bookingId);
        }
        return $bookingId;
    }
    
    /**
     * Get zone prices from vehicle_zone_mappings
     * Query: vehicle_id, (from_zone_id = pickupid AND to_zone_id = dropid) OR (from_zone_id = dropid AND to_zone_id = pickupid)
     * Also checks from_zone_type and to_zone_type to match SingleTourPackageController logic
     */
    public function getZonePrices(Request $request)
    {
        try {
            $vehicleId = $request->input('vehicle_id');
            $pickupId = $request->input('pickup_id');
            $dropId = $request->input('drop_id');
            $pickupType = $request->input('pickup_type'); // hotel, attraction, restaurant, port
            $dropType = $request->input('drop_type'); // hotel, attraction, restaurant, port
            
            if (!$vehicleId || !$pickupId || !$dropId) {
                return response()->json([
                    'success' => false,
                    'message' => 'vehicle_id, pickup_id, and drop_id are required'
                ], 400);
            }
            
            // Map pickup and drop types to zone types (Hotel, Attraction, Restaurant, Port)
            $fromZoneType = null;
            if ($pickupType) {
                $pickupTypeLower = strtolower($pickupType);
                if ($pickupTypeLower === 'hotel') {
                    $fromZoneType = 'Hotel';
                } elseif ($pickupTypeLower === 'attraction') {
                    $fromZoneType = 'Attraction';
                } elseif ($pickupTypeLower === 'restaurant') {
                    $fromZoneType = 'Restaurant';
                } elseif ($pickupTypeLower === 'port') {
                    $fromZoneType = 'Port';
                }
            }
            
            $toZoneType = null;
            if ($dropType) {
                $dropTypeLower = strtolower($dropType);
                if ($dropTypeLower === 'hotel') {
                    $toZoneType = 'Hotel';
                } elseif ($dropTypeLower === 'attraction') {
                    $toZoneType = 'Attraction';
                } elseif ($dropTypeLower === 'restaurant') {
                    $toZoneType = 'Restaurant';
                } elseif ($dropTypeLower === 'port') {
                    $toZoneType = 'Port';
                }
            }
            
            // Query vehicle_zone_mappings with bidirectional check
            // Match SingleTourPackageController logic: check zone_id AND zone_type, also check deleted_at
            $mapping = VehicleZoneMapping::where('vehicle_id', $vehicleId)
                ->whereNull('deleted_at')
                ->where(function($query) use ($pickupId, $dropId, $fromZoneType, $toZoneType) {
                    // Case 1: from = pickup, to = drop
                    $query->where(function($q) use ($pickupId, $dropId, $fromZoneType, $toZoneType) {
                        $q->where('from_zone_id', $pickupId)
                          ->where('to_zone_id', $dropId);
                        // Add zone type checks if provided
                        if ($fromZoneType) {
                            $q->where('from_zone_type', $fromZoneType);
                        }
                        if ($toZoneType) {
                            $q->where('to_zone_type', $toZoneType);
                        }
                    })
                    // Case 2: from = drop, to = pickup (bidirectional)
                    ->orWhere(function($q) use ($pickupId, $dropId, $fromZoneType, $toZoneType) {
                        $q->where('from_zone_id', $dropId)
                          ->where('to_zone_id', $pickupId);
                        // Swap zone types for bidirectional check
                        if ($fromZoneType && $toZoneType) {
                            $q->where('from_zone_type', $toZoneType)
                              ->where('to_zone_type', $fromZoneType);
                        } elseif ($toZoneType) {
                            $q->where('from_zone_type', $toZoneType);
                        } elseif ($fromZoneType) {
                            $q->where('to_zone_type', $fromZoneType);
                        }
                    });
                })
                ->first();
            
            if (!$mapping) {
                return response()->json([
                    'success' => false,
                    'message' => 'No zone mapping found for the given parameters',
                    'data' => [
                        'private_price' => 0,
                        'shared_price' => 0
                    ]
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Zone prices retrieved successfully',
                'data' => [
                    'private_price' => $mapping->private_price ?? 0,
                    'shared_price' => $mapping->shared_price ?? 0
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error getting zone prices', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving zone prices: ' . $e->getMessage(),
                'data' => [
                    'private_price' => 0,
                    'shared_price' => 0
                ]
            ], 500);
        }
    }
    
    /**
     * Edit an existing tour enquiry
     */
    public function edit($tour_id)
    {
        // Try to decrypt tour_id if it's encrypted
        try {
            $decryptedTourId = \Crypt::decrypt($tour_id);
            $tour_id = $decryptedTourId;
        } catch (\Exception $e) {
            // If decryption fails, use tour_id as-is
        }
        
        // Clean up any duplicate orders for this tour before loading edit page
        $this->cleanupDuplicateOrders($tour_id);
        
        // Get the tour with agent relationship
        $tour = Tour::with('agent')->where('tour_id', $tour_id)->firstOrFail();
        
        // Get all orders for this tour
        $orders = Order::where('tour_id', $tour_id)->get();
        
        // Get the first order to extract markup/discount values
        $firstOrder = $orders->first();
        $markupValue = $firstOrder->markup_percentage ?? 0;
        $markupType = $firstOrder->markup_type ?? 'percentage';
        $discountValue = $firstOrder->discount ?? 0;
        $discountType = $firstOrder->discount_type ?? '';
        
        // Get DMC ID
        $user = Auth::user();
        $dmc_id = null;
        if ($user->role_id == 11) {
            $dmc_id = $user->userId;
        } elseif (in_array($user->role_id, [33, 34, 35, 77, 78, 84, 120, 128, 129, 130, 131, 132, 133, 134, 135, 136, 137, 138, 139, 140])) {
            $dmc_id = $user->created_by;
        } elseif (in_array($user->role_id, [37, 64, 65, 66, 67, 68])) {
            $sales_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $sales_head ? $sales_head->created_by : null;
        } elseif (in_array($user->role_id, [38, 81, 90, 108, 117, 124, 125, 126, 127])) {
            $sales_manager = User::where('userId', $user->created_by)->first();
            if ($sales_manager) {
                $sales_head = User::where('userId', $sales_manager->created_by)->first();
                $dmc_id = $sales_head ? $sales_head->created_by : null;
            }
        }
        
        if (!$dmc_id) {
            $dmc_id = $user->created_by;
        }
        
        // Get all required data (same as create method)
        // Note: dmc_id is a JSON array column, so we use whereJsonContains
        $hotels = Hotel::whereJsonContains('dmc_id', (int) $dmc_id)
            ->where('status', 1)
            ->where('is_active', 1)
            ->where('hotel_unique_id', '!=', '0')
            ->select('id', 'hotel_unique_id', 'name', 'city', 'country', 'address', 'zone_assignments')
            ->orderBy('name')
            ->get();
        
        // Add zone_id to each hotel
        $hotels->each(function($hotel) use ($dmc_id) {
            $hotel->zone_id = $dmc_id ? $hotel->getZoneForDmc($dmc_id) : null;
        });
        
        $attractions = Attraction::whereJsonContains('dmc_id', (int) $dmc_id)
            ->where('is_active', 1)
            ->select('attraction_id', 'name', 'location', 'country', 'open_time', 'close_time', 
                     'adult_price', 'child_price', 'senior_adult_price', 'zone_assignments')
            ->orderBy('name')
            ->get();
        
        // Add zone_id to each attraction
        $attractions->each(function($attraction) use ($dmc_id) {
            $attraction->zone_id = $dmc_id ? $attraction->getZoneForDmc($dmc_id) : null;
        });
        
        $restaurants = Restaurant::whereJsonContains('dmc_id', (int) $dmc_id)
            ->where('is_active', 1)
            ->select('restaurant_id', 'name', 'city', 'country', 'breakfast_available', 'lunch_available', 
                     'dinner_available', 'opening_time_bf', 'closing_time_bf', 'opening_time_lunch', 
                     'closing_time_lunch', 'opening_time_dinner', 'closing_time_dinner', 'zone_assignments')
            ->orderBy('name')
            ->get();
        
        // Add zone_id to each restaurant
        $restaurants->each(function($restaurant) use ($dmc_id) {
            $restaurant->zone_id = $dmc_id ? $restaurant->getZoneForDmc($dmc_id) : null;
        });
        
        // Get guides for this DMC only
        // Status 1 and 3 are both considered active guides
        if ($dmc_id) {
            $guides = Guide::where('dmc_id', $dmc_id)
                ->whereIn('status', [1, 3])
                ->with('languages')
                ->select('guide_id', 'name', 'city', 'twelve_hour_price', 'day_rate')
                ->orderBy('name')
                ->get();
        } else {
            $guides = collect([]); // Empty collection if no DMC ID
        }
        
        $vehicles = Vehicle::where('dmc_id', $dmc_id)
            ->where('is_available', 1)
            ->select('vehicle_id', 'vehicle_type', 'vehicle_name', 'seating_capacity', 'base_price', 'sharable_base_price', 'sharable')
            ->orderBy('vehicle_type')
            ->get();
        
        $ports = Port::where('status', 1)
            ->with('country')
            ->select('port_id', 'port_name', 'type', 'country', 'city_id')
            ->orderBy('port_name')
            ->get();
        
        $destinations = Country::where('is_active', 1)
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);
        
        // Get agency and agent
        $agency = Agency::find($tour->agent->agency_id ?? null);
        $agent = Agent::find($tour->agent_id);
        
        // Prepare initialData from tour data
        $initialData = [
            'tour_type' => $tour->tour_type ?? 'FIT',
            'salutation' => $tour->salutation ?? 'Mr',
            'customer_name' => $tour->customer_name ?? 'To Be Advised',
            'contact_number' => $tour->contact_number ?? '',
            'agency_id' => $tour->agent->agency_id ?? null,
            'agent_id' => $tour->agent_id ?? null,
            'agent_name' => $agent->name ?? '',
            'destination' => $tour->destination ?? '',
            'destination_display' => $tour->destination ?? '',
            'tour_start_date' => $tour->check_in_time ? $tour->check_in_time->format('Y-m-d') : '',
            'tour_end_date' => $tour->check_out_time ? $tour->check_out_time->format('Y-m-d') : '',
            'adult_count' => $tour->adult ?? 1,
            'child_count' => $tour->child ?? 0,
            'infant_count' => $tour->infant ?? 0,
        ];
        
        // Load agencies filtered by DMC ID
        $agencyQuery = Agency::where('status', 1);
        if ($dmc_id) {
            $agencyQuery->whereJsonContains('dmc_id', (int) $dmc_id);
        }
        // Filter by tour destination
        if ($tour->destination) {
            $agencyQuery->where('country', $tour->destination);
        }
        $agencies = $agencyQuery->orderBy('agency_name', 'asc')->get();
        
        // Get agents for the selected agency (if any)
        $agents = [];
        if ($tour->agent && $tour->agent->agency_id) {
            $agents = Agent::where('status', 1)
                ->where('agency_id', $tour->agent->agency_id)
                ->orderBy('name', 'asc')
                ->get(['agent_id', 'name', 'email']);
        }
        
        // Get master_dmc_id from logged-in user
        $master_dmc_id = $user->master_dmc_id;
        
        // Get countries based on master_dmc_id
        $countries = collect();
        if ($master_dmc_id) {
            // Find all users with this master_dmc_id
            $usersWithMasterDmc = User::where('master_dmc_id', $master_dmc_id)
                ->whereNotNull('country')
                ->get();
            
            // Extract and merge all countries (comma-separated)
            $countryNames = [];
            foreach ($usersWithMasterDmc as $userItem) {
                if ($userItem->country) {
                    $userCountries = array_map('trim', explode(',', $userItem->country));
                    $countryNames = array_merge($countryNames, $userCountries);
                }
            }
            
            // Remove duplicates and get unique country names
            $countryNames = array_unique($countryNames);
            
            // Get Country objects matching these names
            if (!empty($countryNames)) {
                $countries = Country::whereIn('name', $countryNames)
                    ->where('is_active', 1)
                    ->orderBy('name')
                    ->get();
            }
        } else {
            // Fallback: Get all active countries if no master_dmc_id
            $countries = Country::where('is_active', 1)->orderBy('name')->get();
        }
        
        // Get destinations for master DMC (use the same filtered countries)
        $master_dmc_destinations = $countries;
        
        // Create city to country mapping for filtering
        $cityCountryMap = \App\Models\City::with('country')
            ->get()
            ->pluck('country.name', 'name')
            ->toArray();
        
        // Fetch default values for this DMC (6 types: hotel, restaurant, attraction, car_private, car_shared, port)
        $defaultValues = [];
        if ($dmc_id) {
            $defaults = \App\Models\DefaultValue::where('dmc_id', $dmc_id)
                ->where('status', 1)
                ->get();
            
            foreach ($defaults as $default) {
                $defaultValues[$default->name] = $default->service_id;
            }
        }
        
        return view('enquiryform_pro.edit', compact(
            'tour',
            'orders',
            'hotels',
            'attractions',
            'restaurants',
            'guides',
            'vehicles',
            'ports',
            'destinations',
            'agency',
            'agent',
            'agencies',
            'agents',
            'initialData',
            'countries',
            'master_dmc_destinations',
            'cityCountryMap',
            'dmc_id',
            'user',
            'defaultValues'
        ))->with([
            'markupValue' => $markupValue,
            'markupType' => $markupType,
            'discountValue' => $discountValue,
            'discountType' => $discountType
        ]);
    }
    
    /**
     * Update an existing tour enquiry
     */
    public function update(Request $request, $tour_id)
    {
        try {
            // Try to decrypt tour_id if it's encrypted
            try {
                $decryptedTourId = \Crypt::decrypt($tour_id);
                $tour_id = $decryptedTourId;
            } catch (\Exception $e) {
                // If decryption fails, use tour_id as-is
            }
            
            // Validate the request
            $request->validate([
                'destination' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'adults' => 'required|integer|min:0',
                'children' => 'required|integer|min:0',
                'infants' => 'required|integer|min:0',
                'agent_id' => 'required|exists:agents,agent_id',
                'agency_id' => 'required|exists:agencies,agency_id',
                'markup_value' => 'nullable|numeric|min:0',
                'markup_type' => 'nullable|string|in:percentage,flat',
                'discount_value' => 'nullable|numeric|min:0',
                'discount_type' => 'nullable|string|in:percentage,flat,',
            ]);
            
            // Get markup and discount values
            $markupValue = $request->input('markup_value', 0);
            $markupType = $request->input('markup_type', 'percentage');
            $discountValue = $request->input('discount_value', 0);
            $discountType = $request->input('discount_type', '');
            
            DB::beginTransaction();
            
            // Get the tour
            $tour = Tour::where('tour_id', $tour_id)->firstOrFail();
            
            // Update tour record
            $checkInTime = Carbon::createFromFormat('Y-m-d', $request->start_date);
            $checkOutTime = Carbon::createFromFormat('Y-m-d', $request->end_date);
            
            $tour->destination = $request->destination;
            $tour->adult = $request->adults;
            $tour->child = $request->children;
            $tour->infant = $request->infants;
            $tour->agent_id = $request->agent_id;
            $tour->male_count = $request->male ?? 0;
            $tour->female_count = $request->female ?? 0;
            $tour->check_in_time = $checkInTime;
            $tour->check_out_time = $checkOutTime;
            $tour->city = $request->city ?? null;
            $tour->child_ages = $request->child_ages ?? null;
            
            // Save tour with updated dates
            $saved = $tour->save();
            
            \Log::info('Tour dates updated', [
                'saved' => $saved,
                'tour_id' => $tour_id,
                'check_in_time' => $tour->check_in_time->format('Y-m-d H:i:s'),
                'check_out_time' => $tour->check_out_time->format('Y-m-d H:i:s')
            ]);
            
            \Log::info('Tour updated', [
                'tour_id' => $tour_id,
                'display_id' => $tour->display_id,
                'agent_id' => $request->agent_id
            ]);
            
            // Handle orders marked for deletion
            if ($request->has('orders_to_delete') && !empty($request->orders_to_delete)) {
                $ordersToDelete = json_decode($request->orders_to_delete, true);
                if (is_array($ordersToDelete) && count($ordersToDelete) > 0) {
                    \Log::info('Deleting orders', ['booking_ids' => $ordersToDelete]);
                    Order::where('tour_id', $tour_id)
                        ->whereIn('booking_id', $ordersToDelete)
                        ->delete();
                }
            }
            
            // Collect all existing booking_ids to track what we're updating
            $existingBookingIds = Order::where('tour_id', $tour_id)->pluck('booking_id')->toArray();
            \Log::info('Existing booking IDs before update', ['booking_ids' => $existingBookingIds]);
            
            // Determine booking type
            $bookingType = 'enquiry';
            
            // Update or create orders for each service type
            // Note: We'll update existing orders by booking_id or create new ones
            $createdOrders = [];
            $updatedOrders = [];
            $processedBookingIds = []; // Track processed booking IDs to prevent duplicates
            
            // 1. Entry Port Orders (Arrival)
            if ($request->has('entry_port') && !empty($request->entry_port)) {
                // First, delete ALL existing entry_port orders for this tour to prevent duplicates
                $deletedEntryPorts = Order::where('tour_id', $tour_id)
                    ->where('type', 'entry_port')
                    ->delete();
                \Log::info('Deleted existing entry_port orders', ['count' => $deletedEntryPorts, 'tour_id' => $tour_id]);
                
                $entryPorts = json_decode($request->entry_port, true);
                
                // Use a set to track unique entries to prevent duplicates within the same request
                $seenEntries = [];
                
                foreach ($entryPorts as $entryPort) {
                    // Create a unique identifier for this entry (based on key data fields)
                    $uniqueKey = md5(json_encode([
                        'port_id' => $entryPort['port_id'] ?? '',
                        'port_name' => $entryPort['port_name'] ?? '',
                        'bookingDate' => $entryPort['bookingDate'] ?? '',
                        'type' => $entryPort['type'] ?? ''
                    ]));
                    
                    // Skip if we've already processed this exact entry
                    if (in_array($uniqueKey, $seenEntries)) {
                        \Log::info('Skipping duplicate entry_port within request', ['unique_key' => $uniqueKey]);
                        continue;
                    }
                    $seenEntries[] = $uniqueKey;
                    
                    // Get vehicle details from database if vehicle_id exists
                    if (!empty($entryPort['vehicle_id'])) {
                        $vehicleDetails = $this->getVehicleDetails($entryPort['vehicle_id']);
                        if ($vehicleDetails) {
                            $entryPort['vehicle_id'] = $vehicleDetails['vehicle_id'];
                            $entryPort['vehicles_name'] = $vehicleDetails['vehicles_name'];
                            $entryPort['vehicle_type'] = $vehicleDetails['vehicle_type'];
                            $entryPort['vehicle_model'] = $vehicleDetails['vehicle_model'];
                            $entryPort['model_year'] = $vehicleDetails['model_year'];
                            $entryPort['seating_capacity'] = $vehicleDetails['seating_capacity'];
                            $entryPort['image'] = $vehicleDetails['image'];
                        }
                    }
                    
                    // Normalize transfer type
                    if (!empty($entryPort['type'])) {
                        $entryPort['type'] = $this->normalizeTransferType($entryPort['type']);
                    }
                    
                    // Add tour_id to the JSON data
                    $entryPort['tour_id'] = $tour_id;
                    
                    // Create new order (always create new since we deleted all existing ones)
                    $bookingId = $this->generateBookingId();
                    Order::create([
                        'booking_id' => $bookingId,
                        'agent_id' => $request->agent_id,
                        'tour_id' => $tour_id,
                        'data' => [$entryPort],
                        'type' => 'entry_port',
                        'bookingType' => $bookingType,
                        'discount' => $discountValue,
                        'discount_type' => $discountType,
                        'markup_percentage' => $markupValue,
                        'markup_type' => $markupType,
                        'status' => 1,
                    ]);
                    
                    $createdOrders[] = ['type' => 'entry_port', 'booking_id' => $bookingId];
                }
            }
            
            // 2. Exit Port Orders (Departure)
            if ($request->has('exit_port') && !empty($request->exit_port)) {
                // First, delete ALL existing exit_port orders for this tour to prevent duplicates
                $deletedExitPorts = Order::where('tour_id', $tour_id)
                    ->where('type', 'exit_port')
                    ->delete();
                \Log::info('Deleted existing exit_port orders', ['count' => $deletedExitPorts, 'tour_id' => $tour_id]);
                
                $exitPorts = json_decode($request->exit_port, true);
                
                // Use a set to track unique entries to prevent duplicates within the same request
                $seenExits = [];
                
                foreach ($exitPorts as $exitPort) {
                    // Create a unique identifier for this exit (based on key data fields)
                    $uniqueKey = md5(json_encode([
                        'port_id' => $exitPort['port_id'] ?? '',
                        'port_name' => $exitPort['port_name'] ?? '',
                        'bookingDate' => $exitPort['bookingDate'] ?? '',
                        'type' => $exitPort['type'] ?? ''
                    ]));
                    
                    // Skip if we've already processed this exact exit
                    if (in_array($uniqueKey, $seenExits)) {
                        \Log::info('Skipping duplicate exit_port within request', ['unique_key' => $uniqueKey]);
                        continue;
                    }
                    $seenExits[] = $uniqueKey;
                    
                    // Get vehicle details from database if vehicle_id exists
                    if (!empty($exitPort['vehicle_id'])) {
                        $vehicleDetails = $this->getVehicleDetails($exitPort['vehicle_id']);
                        if ($vehicleDetails) {
                            $exitPort['vehicle_id'] = $vehicleDetails['vehicle_id'];
                            $exitPort['vehicles_name'] = $vehicleDetails['vehicles_name'];
                            $exitPort['vehicle_type'] = $vehicleDetails['vehicle_type'];
                            $exitPort['vehicle_model'] = $vehicleDetails['vehicle_model'];
                            $exitPort['model_year'] = $vehicleDetails['model_year'];
                            $exitPort['seating_capacity'] = $vehicleDetails['seating_capacity'];
                            $exitPort['image'] = $vehicleDetails['image'];
                        }
                    }
                    
                    // Normalize transfer type
                    if (!empty($exitPort['type'])) {
                        $exitPort['type'] = $this->normalizeTransferType($exitPort['type']);
                    }
                    
                    // Add tour_id to the JSON data
                    $exitPort['tour_id'] = $tour_id;
                    
                    // Create new order (always create new since we deleted all existing ones)
                    $bookingId = $this->generateBookingId();
                    Order::create([
                        'booking_id' => $bookingId,
                        'agent_id' => $request->agent_id,
                        'tour_id' => $tour_id,
                        'data' => [$exitPort],
                        'type' => 'exit_port',
                        'bookingType' => $bookingType,
                        'discount' => $discountValue,
                        'discount_type' => $discountType,
                        'markup_percentage' => $markupValue,
                        'markup_type' => $markupType,
                        'status' => 1,
                    ]);
                    
                    $createdOrders[] = ['type' => 'exit_port', 'booking_id' => $bookingId];
                }
            }
            
            // 3. Accommodation Orders
            if ($request->has('accommodations') && !empty($request->accommodations)) {
                // Delete ALL existing hotel orders for this tour to prevent duplicates
                $deletedHotels = Order::where('tour_id', $tour_id)
                    ->where('type', 'hotel')
                    ->delete();
                \Log::info('Deleted existing hotel orders', ['count' => $deletedHotels, 'tour_id' => $tour_id]);
                
                $accommodations = json_decode($request->accommodations, true);
                $seenHotels = [];
                
                foreach ($accommodations as $accommodation) {
                    // Create unique identifier
                    $uniqueKey = md5(json_encode([
                        'hotel_id' => $accommodation['hotel_unique_id'] ?? $accommodation['hotelDetails']['hotel_id'] ?? '',
                        'checkIn' => $accommodation['checkIn'] ?? '',
                        'checkOut' => $accommodation['checkOut'] ?? ''
                    ]));
                    
                    if (in_array($uniqueKey, $seenHotels)) {
                        \Log::info('Skipping duplicate hotel within request', ['unique_key' => $uniqueKey]);
                        continue;
                    }
                    $seenHotels[] = $uniqueKey;
                    
                    // Add tour_id to the JSON data
                    $accommodation['tour_id'] = $tour_id;
                    
                    // Create new order
                    $bookingId = $this->generateBookingId();
                    Order::create([
                        'booking_id' => $bookingId,
                        'agent_id' => $request->agent_id,
                        'tour_id' => $tour_id,
                        'data' => [$accommodation],
                        'type' => 'hotel',
                        'bookingType' => $bookingType,
                        'discount' => $discountValue,
                        'discount_type' => $discountType,
                        'markup_percentage' => $markupValue,
                        'markup_type' => $markupType,
                        'status' => 1,
                    ]);
                    
                    $createdOrders[] = ['type' => 'hotel', 'booking_id' => $bookingId];
                }
            }
            
            // 4. Tour/Attraction Orders
            if ($request->has('tours') && !empty($request->tours)) {
                // Delete ALL existing attraction orders
                Order::where('tour_id', $tour_id)->where('type', 'attraction')->delete();
                
                $tours = json_decode($request->tours, true);
                $seenTours = [];
                
                foreach ($tours as $tourItem) {
                    $uniqueKey = md5(json_encode([
                        'attraction_id' => $tourItem['attraction_id'] ?? '',
                        'AttractionName' => $tourItem['AttractionName'] ?? '',
                        'bookingDate' => $tourItem['bookingDate'] ?? ''
                    ]));
                    
                    if (in_array($uniqueKey, $seenTours)) continue;
                    $seenTours[] = $uniqueKey;
                    
                    $tourItem['tour_id'] = $tour_id;
                    
                    $bookingId = $this->generateBookingId();
                    Order::create([
                        'booking_id' => $bookingId,
                        'agent_id' => $request->agent_id,
                        'tour_id' => $tour_id,
                        'data' => [$tourItem],
                        'type' => 'attraction',
                        'bookingType' => $bookingType,
                        'discount' => $discountValue,
                        'discount_type' => $discountType,
                        'markup_percentage' => $markupValue,
                        'markup_type' => $markupType,
                        'status' => 1,
                    ]);
                    
                    $createdOrders[] = ['type' => 'attraction', 'booking_id' => $bookingId];
                }
            }
            
            // 5. Meal/Restaurant Orders
            if ($request->has('meals') && !empty($request->meals)) {
                // Delete ALL existing restaurant orders
                Order::where('tour_id', $tour_id)->where('type', 'restaurant')->delete();
                
                $meals = json_decode($request->meals, true);
                $seenMeals = [];
                
                foreach ($meals as $meal) {
                    $uniqueKey = md5(json_encode([
                        'restaurant_id' => $meal['restaurant_id'] ?? '',
                        'restaurantName' => $meal['restaurantName'] ?? '',
                        'bookingDate' => $meal['bookingDate'] ?? ''
                    ]));
                    
                    if (in_array($uniqueKey, $seenMeals)) continue;
                    $seenMeals[] = $uniqueKey;
                    
                    $meal['tour_id'] = $tour_id;
                    
                    $bookingId = $this->generateBookingId();
                    Order::create([
                        'booking_id' => $bookingId,
                        'agent_id' => $request->agent_id,
                        'tour_id' => $tour_id,
                        'data' => [$meal],
                        'type' => 'restaurant',
                        'bookingType' => $bookingType,
                        'discount' => $discountValue,
                        'discount_type' => $discountType,
                        'markup_percentage' => $markupValue,
                        'markup_type' => $markupType,
                        'status' => 1,
                    ]);
                    
                    $createdOrders[] = ['type' => 'restaurant', 'booking_id' => $bookingId];
                }
            }
            
            // 6. Transfer Orders (Local Transport)
            if ($request->has('transfers') && !empty($request->transfers)) {
                // Delete ALL existing local_transport orders
                Order::where('tour_id', $tour_id)->where('type', 'local_transport')->delete();
                
                $transfers = json_decode($request->transfers, true);
                $seenTransfers = [];
                
                foreach ($transfers as $transfer) {
                    $uniqueKey = md5(json_encode([
                        'vehicle_id' => $transfer['vehicle_id'] ?? '',
                        'entrypickup' => $transfer['entrypickup'] ?? '',
                        'entrydropoff' => $transfer['entrydropoff'] ?? '',
                        'bookingDate' => $transfer['bookingDate'] ?? ''
                    ]));
                    
                    if (in_array($uniqueKey, $seenTransfers)) continue;
                    $seenTransfers[] = $uniqueKey;
                    
                    // Get vehicle details from database if vehicle_id exists
                    if (!empty($transfer['vehicle_id'])) {
                        $vehicleDetails = $this->getVehicleDetails($transfer['vehicle_id']);
                        if ($vehicleDetails) {
                            $transfer['vehicle_id'] = $vehicleDetails['vehicle_id'];
                            $transfer['vehicles_name'] = $vehicleDetails['vehicles_name'];
                            $transfer['vehicle_type'] = $vehicleDetails['vehicle_type'];
                            $transfer['vehicle_model'] = $vehicleDetails['vehicle_model'];
                            $transfer['model_year'] = $vehicleDetails['model_year'];
                            $transfer['seating_capacity'] = $vehicleDetails['seating_capacity'];
                            $transfer['image'] = $vehicleDetails['image'];
                        }
                    }
                    
                    // Normalize transfer type
                    if (!empty($transfer['type'])) {
                        $transfer['type'] = $this->normalizeTransferType($transfer['type']);
                    }
                    
                    // CRITICAL FIX: Validate and sanitize zone IDs - only use integer zone IDs, not Place IDs
                    // Helper function to check if a value is a valid zone ID (integer) or a Place ID (contains dot)
                    $isValidZoneId = function($value) {
                        if (empty($value) || $value === '') return false;
                        // If it contains a dot, it's likely a Google Place ID, not a zone ID
                        if (strpos((string)$value, '.') !== false) return false;
                        // Check if it's a valid integer
                        return ctype_digit((string)$value) || (is_numeric($value) && (int)$value == $value);
                    };
                    
                    // Sanitize from_zone_id - only keep if it's a valid integer zone ID
                    if (isset($transfer['from_zone_id']) && !empty($transfer['from_zone_id'])) {
                        if (!$isValidZoneId($transfer['from_zone_id'])) {
                            // If it's a Place ID, clear from_zone_id but keep PickupPlaceid
                            if (empty($transfer['PickupPlaceid'])) {
                                $transfer['PickupPlaceid'] = $transfer['from_zone_id'];
                            }
                            $transfer['from_zone_id'] = '';
                        }
                    }
                    
                    // Sanitize to_zone_id - only keep if it's a valid integer zone ID
                    if (isset($transfer['to_zone_id']) && !empty($transfer['to_zone_id'])) {
                        if (!$isValidZoneId($transfer['to_zone_id'])) {
                            // If it's a Place ID, clear to_zone_id but keep DropoffPlaceid
                            if (empty($transfer['DropoffPlaceid'])) {
                                $transfer['DropoffPlaceid'] = $transfer['to_zone_id'];
                            }
                            $transfer['to_zone_id'] = '';
                        }
                    }
                    
                    $transfer['tour_id'] = $tour_id;
                    
                    $bookingId = $this->generateBookingId();
                    Order::create([
                        'booking_id' => $bookingId,
                        'agent_id' => $request->agent_id,
                        'tour_id' => $tour_id,
                        'data' => [$transfer],
                        'type' => 'local_transport',
                        'bookingType' => $bookingType,
                        'discount' => $discountValue,
                        'discount_type' => $discountType,
                        'markup_percentage' => $markupValue,
                        'markup_type' => $markupType,
                        'status' => 1,
                    ]);
                    
                    $createdOrders[] = ['type' => 'local_transport', 'booking_id' => $bookingId];
                }
            }
            
            // 7. Guide Orders
            if ($request->has('guides') && !empty($request->guides)) {
                // Delete ALL existing guide orders
                Order::where('tour_id', $tour_id)->where('type', 'guide')->delete();
                
                $guides = json_decode($request->guides, true);
                $seenGuides = [];
                
                foreach ($guides as $guide) {
                    $uniqueKey = md5(json_encode([
                        'guide_id' => $guide['guide_id'] ?? '',
                        'guide_name' => $guide['guide_name'] ?? '',
                        'bookingDate' => $guide['bookingDate'] ?? ''
                    ]));
                    
                    if (in_array($uniqueKey, $seenGuides)) continue;
                    $seenGuides[] = $uniqueKey;
                    
                    $guide['tour_id'] = $tour_id;
                    
                    $bookingId = $this->generateBookingId();
                    Order::create([
                        'booking_id' => $bookingId,
                        'agent_id' => $request->agent_id,
                        'tour_id' => $tour_id,
                        'data' => [$guide],
                        'type' => 'guide',
                        'bookingType' => $bookingType,
                        'discount' => $discountValue,
                        'discount_type' => $discountType,
                        'markup_percentage' => $markupValue,
                        'markup_type' => $markupType,
                        'status' => 1,
                    ]);
                    
                    $createdOrders[] = ['type' => 'guide', 'booking_id' => $bookingId];
                }
            }
            
            // 8. Miscellaneous Orders
            if ($request->has('miscellaneous') && !empty($request->miscellaneous)) {
                // Delete ALL existing miscellaneous orders
                Order::where('tour_id', $tour_id)->where('type', 'miscellaneous')->delete();
                
                $miscItems = json_decode($request->miscellaneous, true);
                $seenMisc = [];
                
                foreach ($miscItems as $miscItem) {
                    $uniqueKey = md5(json_encode([
                        'item_id' => $miscItem['item_id'] ?? '',
                        'item_name' => $miscItem['item_name'] ?? '',
                        'bookingDate' => $miscItem['bookingDate'] ?? ''
                    ]));
                    
                    if (in_array($uniqueKey, $seenMisc)) continue;
                    $seenMisc[] = $uniqueKey;
                    
                    $miscItem['tour_id'] = $tour_id;
                    
                    $bookingId = $this->generateBookingId();
                    Order::create([
                        'booking_id' => $bookingId,
                        'agent_id' => $request->agent_id,
                        'tour_id' => $tour_id,
                        'data' => [$miscItem],
                        'type' => 'miscellaneous',
                        'bookingType' => $bookingType,
                        'discount' => $discountValue,
                        'discount_type' => $discountType,
                        'markup_percentage' => $markupValue,
                        'markup_type' => $markupType,
                        'status' => 1,
                    ]);
                    
                    $createdOrders[] = ['type' => 'miscellaneous', 'booking_id' => $bookingId];
                }
            }
            
            DB::commit();
            
            \Log::info('Orders recreated using delete-and-create approach', [
                'tour_id' => $tour_id,
                'total_created' => count($createdOrders)
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Tour enquiry updated successfully',
                'display_id' => $tour->display_id,
                'tour_id' => $tour_id,
                'total_orders' => count($createdOrders)
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating tour enquiry', [
                'tour_id' => $tour_id,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tour enquiry: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Clean up duplicate orders for a tour
     * Keeps only the first unique order of each type based on key data fields
     */
    private function cleanupDuplicateOrders($tour_id)
    {
        try {
            $orders = Order::where('tour_id', $tour_id)->get();
            
            $seenOrders = [];
            $duplicateIds = [];
            
            foreach ($orders as $order) {
                $orderData = is_array($order->data) ? $order->data : json_decode($order->data, true);
                $firstItem = $orderData[0] ?? [];
                
                // Create unique key based on order type and key fields
                $uniqueKey = '';
                switch ($order->type) {
                    case 'entry_port':
                    case 'exit_port':
                        $uniqueKey = md5(json_encode([
                            'type' => $order->type,
                            'port_id' => $firstItem['port_id'] ?? '',
                            'port_name' => $firstItem['port_name'] ?? '',
                            'bookingDate' => $firstItem['bookingDate'] ?? '',
                            'transfer_type' => $firstItem['type'] ?? ''
                        ]));
                        break;
                    case 'hotel':
                        $uniqueKey = md5(json_encode([
                            'type' => $order->type,
                            'hotel_id' => $firstItem['hotel_unique_id'] ?? $firstItem['hotelDetails']['hotel_id'] ?? '',
                            'checkIn' => $firstItem['checkIn'] ?? '',
                            'checkOut' => $firstItem['checkOut'] ?? ''
                        ]));
                        break;
                    case 'attraction':
                        $uniqueKey = md5(json_encode([
                            'type' => $order->type,
                            'attraction_id' => $firstItem['attraction_id'] ?? '',
                            'AttractionName' => $firstItem['AttractionName'] ?? '',
                            'bookingDate' => $firstItem['bookingDate'] ?? ''
                        ]));
                        break;
                    case 'restaurant':
                        $uniqueKey = md5(json_encode([
                            'type' => $order->type,
                            'restaurant_id' => $firstItem['restaurant_id'] ?? '',
                            'restaurantName' => $firstItem['restaurantName'] ?? '',
                            'bookingDate' => $firstItem['bookingDate'] ?? ''
                        ]));
                        break;
                    case 'local_transport':
                        $uniqueKey = md5(json_encode([
                            'type' => $order->type,
                            'vehicle_id' => $firstItem['vehicle_id'] ?? '',
                            'entrypickup' => $firstItem['entrypickup'] ?? '',
                            'entrydropoff' => $firstItem['entrydropoff'] ?? '',
                            'bookingDate' => $firstItem['bookingDate'] ?? ''
                        ]));
                        break;
                    case 'guide':
                        $uniqueKey = md5(json_encode([
                            'type' => $order->type,
                            'guide_id' => $firstItem['guide_id'] ?? '',
                            'guide_name' => $firstItem['guide_name'] ?? '',
                            'bookingDate' => $firstItem['bookingDate'] ?? ''
                        ]));
                        break;
                    case 'miscellaneous':
                        $uniqueKey = md5(json_encode([
                            'type' => $order->type,
                            'item_id' => $firstItem['item_id'] ?? '',
                            'item_name' => $firstItem['item_name'] ?? '',
                            'bookingDate' => $firstItem['bookingDate'] ?? ''
                        ]));
                        break;
                    default:
                        $uniqueKey = md5(json_encode([
                            'type' => $order->type,
                            'booking_id' => $order->booking_id
                        ]));
                        break;
                }
                
                // Check if we've seen this order before
                if (in_array($uniqueKey, $seenOrders)) {
                    // This is a duplicate, mark for deletion
                    $duplicateIds[] = $order->id;
                    \Log::info('Found duplicate order', [
                        'tour_id' => $tour_id,
                        'order_id' => $order->id,
                        'booking_id' => $order->booking_id,
                        'type' => $order->type
                    ]);
                } else {
                    // First occurrence, keep it
                    $seenOrders[] = $uniqueKey;
                }
            }
            
            // Delete duplicates
            if (count($duplicateIds) > 0) {
                $deletedCount = Order::whereIn('id', $duplicateIds)->delete();
                \Log::info('Cleaned up duplicate orders', [
                    'tour_id' => $tour_id,
                    'deleted_count' => $deletedCount
                ]);
            }
            
        } catch (\Exception $e) {
            \Log::error('Error cleaning up duplicate orders', [
                'tour_id' => $tour_id,
                'error' => $e->getMessage()
            ]);
            // Don't fail the edit page load if cleanup fails
        }
    }
   
}
