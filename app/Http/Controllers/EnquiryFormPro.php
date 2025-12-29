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
        
        $ports = Port::where('status', 1)->with('country')->orderBy('port_name')->get();
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
            
            $attractionsQuery = Attraction::whereJsonContains('dmc_id', (int) $dmc_id)
                ->where('status', 1)
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
            
            \Log::info('EnquiryFormPro create() - Attractions loaded', [
                'dmc_id' => $dmc_id,
                'count' => $attractions->count(),
                'attraction_ids' => $attractions->pluck('attraction_id')->toArray(),
                'attraction_names' => $attractions->pluck('name')->toArray()
            ]);
            
            // Get restaurants for this DMC with city info (restaurants have 'city' field)
            $restaurants = Restaurant::whereJsonContains('dmc_id', (int) $dmc_id)
                ->where('status', 1)
                ->where('is_active', 1)
                ->select('restaurant_id', 'name', 'city', 'country', 'breakfast_available', 'lunch_available', 
                         'dinner_available', 'opening_time_bf', 'closing_time_bf', 'opening_time_lunch', 
                         'closing_time_lunch', 'opening_time_dinner', 'closing_time_dinner')
                ->orderBy('name')
                ->get();
            
            \Log::info('EnquiryFormPro create() - Restaurants loaded', [
                'dmc_id' => $dmc_id,
                'count' => $restaurants->count(),
                'restaurant_ids' => $restaurants->pluck('restaurant_id')->toArray(),
                'restaurant_names' => $restaurants->pluck('name')->toArray()
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
            
            // Get guides for this DMC
            $guides = Guide::where('dmc_id', $dmc_id)
                ->where('status', 1)
                ->with('languages')
                ->select('guide_id', 'name', 'city')
                ->orderBy('name')
                ->get();
            
            // Get hotels for this DMC
            $hotels = Hotel::where('status', 1)
                ->where('is_active', 1)
                ->where('is_complete', 1)
                ->whereJsonContains('dmc_id', (int) $dmc_id)
                ->select('id', 'name', 'city', 'country', 'address')
                ->orderBy('name')
                ->get();
            
            // Get vehicles for this DMC
            $vehicles = Vehicle::where('dmc_id', $dmc_id)
                ->where('is_available', 1)
                ->select('vehicle_id', 'vehicle_type', 'vehicle_name', 'seating_capacity', 'base_price', 'sharable_base_price')
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
            
            $guides = Guide::where('status', 1)
                ->with('languages')
                ->select('guide_id', 'name', 'city')
                ->orderBy('name')
                ->get();
            
            // Get all hotels (fallback)
            $hotels = Hotel::where('status', 1)
                ->where('is_active', 1)
                ->where('is_complete', 1)
                ->select('id', 'name', 'city', 'country', 'address')
                ->orderBy('name')
                ->get();
            
            // Get all vehicles (fallback)
            $vehicles = Vehicle::where('is_available', 1)
                ->select('vehicle_id', 'vehicle_type', 'vehicle_name', 'seating_capacity', 'base_price', 'sharable_base_price')
                ->orderBy('vehicle_type')
                ->get();
        }
        
        // Create city to country mapping for filtering
        $cityCountryMap = \App\Models\City::with('country')
            ->get()
            ->pluck('country.name', 'name')
            ->toArray();
        
        return view('enquiryform_pro.create', compact('destination', 'agents', 'agencies', 'user', 'countries', 'ports', 'destinations', 'attractions', 'restaurants', 'initialData', 'meals', 'guides', 'dmc_id', 'hotels', 'vehicles', 'master_dmc_destinations', 'cityCountryMap'));
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
        
        // Transform the data to include bed information properly
        $hotels->each(function($hotel) {
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
                     'adult_price', 'child_price', 'senior_adult_price')
            ->orderBy('name')
            ->get();
        
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
            
            // Get guides for this DMC and destination
            $guidesQuery = Guide::where('status', 1)
                ->where('city', $destination);
            
            if ($dmc_id) {
                $guidesQuery->where('dmc_id', $dmc_id);
            }
            
            $guides = $guidesQuery
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
            ]);
            
            DB::beginTransaction();
            
            // Parse the dates
            $checkInTime = Carbon::createFromFormat('Y-m-d', $request->start_date);
            $checkOutTime = Carbon::createFromFormat('Y-m-d', $request->end_date);
            
            // Generate tour ID
            $max_tour_id = Tour::max('tour_id') ?? 0;
            $tourId = CommonHelper::createId($max_tour_id);
            $display_id = 'DMC-ORD' . $tourId;
            
            // Get DMC ID
            $user = Auth::user();
            $dmcId = $user->created_by;
            
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
                foreach ($entryPorts as $entryPort) {
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
                    
                    $order = Order::create([
                        'booking_id' => $bookingId,
                        'agent_id' => $request->agent_id,
                        'tour_id' => $tourId,
                        'data' => [$entryPort],
                        'type' => 'entry_port',
                        'bookingType' => $bookingType,
                        'discount' => 0,
                        'markup_percentage' => 0,
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
                foreach ($exitPorts as $exitPort) {
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
                    
                    $order = Order::create([
                        'booking_id' => $bookingId,
                        'agent_id' => $request->agent_id,
                        'tour_id' => $tourId,
                        'data' => [$exitPort],
                        'type' => 'exit_port',
                        'bookingType' => $bookingType,
                        'discount' => 0,
                        'markup_percentage' => 0,
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
                    
                    $order = Order::create([
                        'booking_id' => $bookingId,
                        'agent_id' => $request->agent_id,
                        'tour_id' => $tourId,
                        'data' => [$accommodation],
                        'type' => 'hotel',
                        'bookingType' => $bookingType,
                        'discount' => 0,
                        'markup_percentage' => 0,
                        'status' => 1,
                    ]);
                    
                    $createdOrders[] = [
                        'type' => 'hotel',
                        'booking_id' => $bookingId,
                        'service_date' => $accommodation['checkIn'] ?? null
                    ];
                    
                    // AUTO-CREATE LOCAL TRANSPORT IF TRANSFER IS OPTED
                    if (isset($accommodation['transfer_options']) && !empty($accommodation['transfer_options']) && ($accommodation['transfer_options']['transfer_required'] ?? false)) {
                        $transferOptions = $accommodation['transfer_options'];
                        $bookingDates = $accommodation['bookingDate'] ?? [];
                        $transferDate = is_array($bookingDates) && count($bookingDates) > 0 ? $bookingDates[0] : date('Y-m-d');
                        
                        // Create unique transfer identifier
                        $transferIdentifier = md5(
                            ($transferOptions['pickup_location_name'] ?? '') . 
                            ($accommodation['hotelDetails']['hotel_name'] ?? '') . 
                            $transferDate . 
                            '11:00 AM'
                        );
                        
                        // Only create transfer if not already created
                        if (!in_array($transferIdentifier, $createdTransferIds)) {
                            $createdTransferIds[] = $transferIdentifier;
                            
                            \Log::info('Auto-creating local_transport for hotel', ['hotel_booking_id' => $bookingId]);
                            
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
                            'entrydropoff' => $transferOptions['destination_name'] ?? $accommodation['hotelDetails']['hotel_name'] ?? '',
                            'PickupPlaceid' => '',
                            'DropoffPlaceid' => '',
                            'pickupdate' => $transferDate,
                            'entrytime' => '11:00 AM',
                            'adults' => (string) ($accommodation['rooms'][0]['beds'][0]['head_count'] ?? 2),
                            'children' => '0',
                            'totalPrice' => (string) ($transferOptions['cost'] ?? 0),
                            'to_zone_id' => '',
                            'from_zone_id' => '',
                            'city' => $accommodation['hotelDetails']['location'] ?? 'Singapore',
                            'country' => $accommodation['hotelDetails']['location'] ?? 'Singapore',
                            'fullName' => $accommodation['fullName'] ?? 'Guest User',
                            'email' => $accommodation['email'] ?? 'guest@example.com',
                            'phone' => $accommodation['phone'] ?? '0000000000',
                            'countryCode' => $accommodation['countryCode'] ?? '65',
                            'address1' => $accommodation['address1'] ?? '',
                            'address2' => $accommodation['address2'] ?? '',
                            'state' => $accommodation['state'] ?? '',
                            'zip' => $accommodation['zip'] ?? '',
                            'specialRequests' => $accommodation['specialRequests'] ?? '',
                            'userInfo' => [
                                'fullName' => $accommodation['fullName'] ?? 'Guest User',
                                'email' => $accommodation['email'] ?? 'guest@example.com',
                                'phone' => $accommodation['phone'] ?? '0000000000',
                                'countryCode' => $accommodation['countryCode'] ?? '65',
                                'address1' => $accommodation['address1'] ?? ''
                            ],
                            'bookingType' => 'enquiry',
                            'linked_to_hotel' => $bookingId
                        ];
                        
                        $transportBookingId = $this->generateBookingId();
                        Order::create([
                            'booking_id' => $transportBookingId,
                            'agent_id' => $request->agent_id,
                            'tour_id' => $tourId,
                            'data' => [$localTransportData],
                            'type' => 'local_transport',
                            'bookingType' => $bookingType,
                            'discount' => 0,
                            'markup_percentage' => 0,
                            'status' => 1,
                        ]);
                        
                            $createdOrders[] = ['type' => 'local_transport', 'booking_id' => $transportBookingId, 'linked_to' => 'hotel'];
                            \Log::info('Local transport created for hotel', ['transport_booking_id' => $transportBookingId, 'linked_to_hotel' => $bookingId]);
                        } else {
                            \Log::info('Skipped duplicate local_transport for hotel', ['transfer_identifier' => $transferIdentifier]);
                        }
                    }
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
                    
                    $order = Order::create([
                        'booking_id' => $bookingId,
                        'agent_id' => $request->agent_id,
                        'tour_id' => $tourId,
                        'data' => [$tourItem],
                        'type' => 'attraction',
                        'bookingType' => $bookingType,
                        'discount' => 0,
                        'markup_percentage' => 0,
                        'status' => 1,
                    ]);
                    
                    $createdOrders[] = [
                        'type' => 'attraction',
                        'booking_id' => $bookingId,
                        'service_date' => $tourItem['dateTime'] ?? null
                    ];
                    
                    // AUTO-CREATE LOCAL TRANSPORT IF TRANSFER IS OPTED
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
                            'discount' => 0,
                            'markup_percentage' => 0,
                            'status' => 1,
                        ]);
                        
                            $createdOrders[] = ['type' => 'local_transport', 'booking_id' => $transportBookingId, 'linked_to' => 'attraction'];
                        } else {
                            \Log::info('Skipped duplicate local_transport for attraction', ['transfer_identifier' => $transferIdentifier]);
                        }
                    }
                    
                    // AUTO-CREATE GUIDE IF GUIDE IS OPTED
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
                            'discount' => 0,
                            'markup_percentage' => 0,
                            'status' => 1,
                        ]);
                        
                            $createdOrders[] = ['type' => 'guide', 'booking_id' => $guideBookingId, 'linked_to' => 'attraction'];
                        } else {
                            \Log::info('Skipped duplicate guide for attraction', ['guide_identifier' => $guideIdentifier]);
                        }
                    }
                }
            }
            
            // 3. Meal/Restaurant Orders
            if ($request->has('meals') && !empty($request->meals)) {
                $meals = json_decode($request->meals, true);
                \Log::info('Processing meals/restaurants', ['count' => count($meals)]);
                
                foreach ($meals as $meal) {
                    \Log::info('Restaurant data', [
                        'has_transfer_options' => isset($meal['transfer_options']),
                        'transfer_options' => $meal['transfer_options'] ?? null
                    ]);
                    $bookingId = $this->generateBookingId();
                    
                    $order = Order::create([
                        'booking_id' => $bookingId,
                        'agent_id' => $request->agent_id,
                        'tour_id' => $tourId,
                        'data' => [$meal],
                        'type' => 'restaurant',
                        'bookingType' => $bookingType,
                        'discount' => 0,
                        'markup_percentage' => 0,
                        'status' => 1,
                    ]);
                    
                    $createdOrders[] = [
                        'type' => 'restaurant',
                        'booking_id' => $bookingId,
                        'service_date' => $meal['bookingDate'] ?? null
                    ];
                    
                    // AUTO-CREATE LOCAL TRANSPORT IF TRANSFER IS OPTED
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
                                'discount' => 0,
                                'markup_percentage' => 0,
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
                }
            }
            
            // 4. Transfer Orders (Local Transport)
            if ($request->has('transfers') && !empty($request->transfers)) {
                $transfers = json_decode($request->transfers, true);
                foreach ($transfers as $transfer) {
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
                    
                    $bookingId = $this->generateBookingId();
                    
                    $order = Order::create([
                        'booking_id' => $bookingId,
                        'agent_id' => $request->agent_id,
                        'tour_id' => $tourId,
                        'data' => [$transfer],
                        'type' => 'local_transport',
                        'bookingType' => $bookingType,
                        'discount' => 0,
                        'markup_percentage' => 0,
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
                    $bookingId = $this->generateBookingId();
                    
                    $order = Order::create([
                        'booking_id' => $bookingId,
                        'agent_id' => $request->agent_id,
                        'tour_id' => $tourId,
                        'data' => [$guide],
                        'type' => 'guide',
                        'bookingType' => $bookingType,
                        'discount' => 0,
                        'markup_percentage' => 0,
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
                    $bookingId = $this->generateBookingId();
                    
                    $order = Order::create([
                        'booking_id' => $bookingId,
                        'agent_id' => $request->agent_id,
                        'tour_id' => $tourId,
                        'data' => [$miscItem],
                        'type' => 'miscellaneous',
                        'bookingType' => $bookingType,
                        'discount' => 0,
                        'markup_percentage' => 0,
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
   
}
