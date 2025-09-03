<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\City;
use App\Models\Agent;
use App\Models\SingleTourPackage;
use App\Models\Tour;
use App\Models\Hotel;
use App\Models\Attraction;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Guide;
use App\Models\Restaurant;
use App\Models\Meal;
use App\Models\Zone;
use App\Models\Port;
use App\Models\Order;
use App\Models\Vehicle;
use App\Models\VehicleZoneMapping;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\CommonHelper;
use Carbon\Carbon;
use App\Models\EnquiryForm;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class SingleTourPackageController extends Controller
{
    /**
     * Display a listing of single tour packages.
     */
    public function index()
    {
        // Query tours table since that's where the data is actually stored
        $packages = Tour::with(['agent'])
            ->whereHas('agent', function($query) {
                $query->where('sales_manager_dmc', Auth::id());
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('single-tour-package.index', compact('packages'));
    }

    /**
     * Show the form for creating a new single tour package.
     */
    public function create(Request $request, $enquiry_id = null)
    {

        $user = Auth::user();

        if($user->role_id == 11){
            $dmc_id = $user->userId;
        }
        elseif(in_array($user->role_id, [33,34, 128, 129, 130,131,132, 134, 135, 136,137, 138])){
            $dmc_id = $user->created_by;
        }
        elseif(in_array($user->role_id, [37,64,65,66,67,68])){
            $sales_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }
        elseif(in_array($user->role_id, [38,81,90,108,117,124,125,126,127])){
            $sales_manager = User::where('userId', $user->created_by)->first();
            $sales_head = User::where('userId', $sales_manager->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }

        $countries = Country::where('is_active', 1)->orderBy('name')->get();
        $portsQuery = Port::query();
        if ($request->has('country') && $request->country) {
            $country = Country::find($request->country);
            if ($country) {
                $portsQuery->where('country', $country->name);
            }
        }
        $ports = $portsQuery->orderBy('port_name')->get();        
        // Get agents for the current DMC
        $agents = Agent::whereJsonContains('dmc_id', (int) $dmc_id)
            ->orderBy('name')
            ->get();
        $selectedCountry = $request->country;

        $enquiry = null;
        $hotels = collect();
        $attractions = collect();
        $guides = collect();
        $vehicles = collect();
        $meals = collect();
        $tickets = collect();
        $zones = collect();
        
        if($enquiry_id){
            $enquiry_id = Crypt::decrypt($enquiry_id);
            if($enquiry_id){
                $enquiry = EnquiryForm::where('enquiry_id', $enquiry_id)->first();
                $selectedCountry = $enquiry->country;
                if($enquiry){
                    // Get hotels
                    if($enquiry->hotel_ids){
                        $hotel_ids = json_decode($enquiry->hotel_ids, true);
                        $hotels = Hotel::whereIn('hotel_unique_id', $hotel_ids)->get();
                    }
                    
                    // Get attractions
                    if($enquiry->attraction_ids){
                        $attraction_ids = json_decode($enquiry->attraction_ids, true);
                        $attractions = Attraction::whereIn('attraction_id', $attraction_ids)->get();
                    }
                    
                    // Get guides
                    if($enquiry->guide_ids){
                        $guide_ids = json_decode($enquiry->guide_ids, true);
                        $guides = Guide::whereIn('guide_id', $guide_ids)->get();
                    }
                    
                    // Get vehicles
                    if($enquiry->local_transport_vehicle_ids){
                        $vehicle_ids = json_decode($enquiry->local_transport_vehicle_ids, true);
                        $vehicles = Vehicle::whereIn('vehicle_id', $vehicle_ids)->get();
                    }
                    
                    // Get restaurants as meals
                    if($enquiry->restaurant_ids){
                        $restaurant_ids = json_decode($enquiry->restaurant_ids, true);
                        $meals = Restaurant::whereIn('restaurant_id', $restaurant_ids)->get();
                    }
                }
            }
        }
        return view('single-tour-package.create', compact('countries', 'agents', 'ports', 'selectedCountry', 'enquiry', 'hotels', 'attractions', 'guides', 'vehicles', 'meals', 'tickets', 'zones'));
    }
    
    /**
     * Add more services to an existing tour package.
     */
    public function addServices($tour_id)
    {
        // Get countries for dropdown
        $user = Auth::user();

        if($user->role_id == 11){
            $dmc_id = $user->userId;
        }
        elseif(in_array($user->role_id, [33,34, 128, 129, 130,131,132, 134, 135, 136,137, 138])){
            $dmc_id = $user->created_by;
        }
        elseif(in_array($user->role_id, [37,64,65,66,67,68])){
            $sales_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }
        elseif(in_array($user->role_id, [38,81,90,108,117,124,125,126,127])){
            $sales_manager = User::where('userId', $user->created_by)->first();
            $sales_head = User::where('userId', $sales_manager->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }
        
        $countries = Country::where('is_active', 1)->orderBy('name')->get();
        
        // Get agents for the current DMC
        $agents = Agent::whereJsonContains('dmc_id', (int) $dmc_id)
            ->orderBy('name')
            ->get();
        
        // Find the existing tour
        $existingTour = Tour::with(['agent'])->where('tour_id', $tour_id)->first();
        if (!$existingTour) {
            return redirect()->route('single-tour-package.index')->with('error', 'Tour not found');
        }
        
        // Prepare tour data for the view
        $tourData = [
            'id' => $existingTour->tour_id,
            'display_id' => $existingTour->display_id,
            'country' => $existingTour->destination,
            'city' => $existingTour->city,
            'agent_id' => $existingTour->agent_id,
            'adults' => $existingTour->adult,
            'children' => $existingTour->child,
            'infants' => $existingTour->infant,
            'male_count' => $existingTour->male_count,
            'female_count' => $existingTour->female_count,
            'check_in_time' => $existingTour->check_in_time,
            'check_out_time' => $existingTour->check_out_time
        ];
            
        $enquiry = null;
        
        // Get hotels for this DMC and city
        $cityName = $existingTour->city;
        $hotels = Hotel::whereJsonContains('dmc_id', (int) $dmc_id)
            ->where('status', 1)
            ->where('is_active', 1)
            ->where(function ($q) use ($cityName) {
                $q->whereRaw('LOWER(city) = ?', [strtolower($cityName)]);
            })
            ->select('hotel_unique_id', 'name', 'city', 'main_image', 'hotel_star_rating', 
                     'latitude', 'longitude', 'check_in_time', 'check_out_time')
            ->orderBy('name')
            ->get();
            
        // Get attractions for this DMC
        $attractions = Attraction::whereJsonContains('dmc_id', (int) $dmc_id)
            ->select('attraction_id', 'name', 'open_time', 'close_time', 'location', 
                     'adult_price', 'child_price', 'senior_adult_price')
            ->get();
            
        // Get attraction tickets
        $attractionIds = $attractions->pluck('attraction_id')->toArray();
        $tickets = Ticket::whereIn('attraction_id', $attractionIds)
            ->select('ticket_id', 'attraction_id', 'name', 'child_price', 'adult_price', 
                     'senior_adult_price', 'description')
            ->get();
        
        // Get guides for this DMC
        $guides = Guide::where('dmc_id', $dmc_id)
            ->where('status', 1)
            ->select('guide_id', 'name', 'night_start_time', 'night_end_time', 
                    'day_rate', 'night_surcharge', 'hourly_price', 
                    'two_hour_price', 'four_hour_price', 'six_hour_price', 
                    'eight_hour_price', 'ten_hour_price', 'twelve_hour_price')
            ->get();
            
        // Get restaurants and meals
        $restaurants = Restaurant::whereJsonContains('dmc_id', (int) $dmc_id)
            ->select('restaurant_id', 'name', 'breakfast_available', 'lunch_available', 'dinner_available',
                     'opening_time_bf', 'closing_time_bf', 'opening_time_lunch', 'closing_time_lunch',
                     'opening_time_dinner', 'closing_time_dinner')
            ->get();
            
        $restaurantIds = $restaurants->pluck('restaurant_id')->toArray();
        $meals = Meal::whereIn('restaurant_id', $restaurantIds)
            ->select('meal_id', 'restaurant_id', 'name', 'type', 'price', 'adult_price', 'child_price', 'meal_period')
            ->get();
            
        // Get zones for transport
        $cityId = City::where('name', $cityName)->first()->city_id ?? null;
        $zones = Zone::where('dmc_id', $dmc_id)
            ->where('status', 1)
            ->where('city', $cityId)
            ->select('zone_id', 'zone_name', 'zone_type', 'city', 'description')
            ->orderBy('zone_name')
            ->get();
            
        // Get vehicles based on zones
        $zoneIds = $zones->pluck('zone_id')->toArray();
        $vehicleMappings = VehicleZoneMapping::whereIn('from_zone_id', $zoneIds)
            ->whereIn('to_zone_id', $zoneIds)
            ->get();
            
        $vehicleIds = $vehicleMappings->pluck('vehicle_id')->unique()->toArray();
        $vehicles = Vehicle::whereIn('vehicle_id', $vehicleIds)
            ->select('vehicle_id', 'vehicle_name', 'vehicle_type', 'seating_capacity', 
                     'vehicle_model', 'image', 'base_price', 'sharable_base_price', 'service_type')
            ->get();
            
        // Additional data for the view
        $serviceData = [
            'city_name' => $cityName,
            'dmc_id' => $dmc_id,
            'start_date' => $existingTour->check_in_time,
            'end_date' => $existingTour->check_out_time,
            'tour_id' => $existingTour->tour_id
        ];

        return view('single-tour-package.create', compact('countries', 'agents', 'enquiry', 'hotels', 
            'attractions', 'guides', 'vehicles', 'meals', 'tickets', 'zones', 'existingTour', 'tourData',
            'restaurants', 'serviceData'));
    }


    public function editpackage(Request $request, $tour_id)
    {
        if (!$tour_id) {
            \Log::error('No tour_id provided');
            return redirect()->back()->with('error', 'Tour ID is required to edit tour services.');
        }
        $tour = Tour::where('tour_id', $tour_id)->first();
        $agent_name = Agent::where('agent_id', $tour->agent_id)->first()->name;
        if (!$tour) {
            \Log::error('Tour not found with tour_id: ' . $tour_id);
            return redirect()->back()->with('error', 'Tour not found.');
        }
        
        // Get hotels based on user's DMC ID
        $userDmcId = Auth::user()->created_by;
        if ($userDmcId) {
            $hotels = Hotel::with(['rooms.bed'])->where('country', $tour->destination)
                        //   ->whereJsonContains('dmc_id', $userDmcId)
                          ->get();
        } else {
            $hotels = collect(); // Empty collection if no DMC ID
        }
        $guide = Guide::where('dmc_id', Auth::user()->created_by)->get();
        $countries = Country::where('is_active', 1)->orderBy('name')->get();
        $portsQuery = Port::query();
        if ($request->has('country') && $request->country) {
            $country = Country::find($request->country);
            if ($country) {
                $portsQuery->where('country', $country->name);
            }
        }
        $ports = $portsQuery->orderBy('port_name')->get();
        $agents = Agent::Where('sales_manager_dmc', Auth::id())
            ->orderBy('name')
            ->get();
        $selectedCountry = $request->country;
        // Fetch all orders for this tour
        $orders = Order::where('tour_id', $tour_id)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Group orders by type and process the data
        $ordersByType = [];
        foreach ($orders as $order) {
            $type = $order->type;
            if (!isset($ordersByType[$type])) {
                $ordersByType[$type] = [];
            }
            
            // Parse the JSON data
            $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
            
            // Add processed data to the order
            $order->processed_data = $orderData;
            $ordersByType[$type][] = $order;
        }
        
        return view('single-tour-package.edit', compact('tour', 'countries', 'agents', 'ports', 'selectedCountry', 'ordersByType','agent_name','hotels','guide'));
    }

    /**
     * Get order details for editing
     */
    public function getOrder($id)
    {
        try {
            $order = Order::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'order' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
    }

    /**
     * Cancel an order
     */
    public function cancelOrder(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
            
            // Soft delete the order
            $order->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show thank you page after successful tour package creation
     */
    public function thankYou(Request $request)
    {
        // Handle POST data from JavaScript form submission
        if ($request->isMethod('post')) {
            $tourDetails = $request->input('tour_details');
            $createdOrders = $request->input('created_orders');
            
            if ($tourDetails) {
                session(['tour_details' => json_decode($tourDetails, true)]);
            }
            if ($createdOrders) {
                session(['created_orders' => json_decode($createdOrders, true)]);
            }
            
            return redirect()->route('single-tour-package.thank-you');
        }
        
        return view('single-tour-package.thank-you');
    }

    /**
     * Store a newly created single tour package in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_country' => 'required|string', // Country name
            'city' => 'required|string', // City name  
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'adults' => 'required|integer|min:1',
            'male' => 'required|integer|min:0',
            'female' => 'required|integer|min:0',
            'children' => 'required|integer|min:0',
            'infants' => 'required|integer|min:0',
            'agent_id' => 'required|exists:agents,agent_id',
            'package_name' => 'nullable|string|max:255',
            'estimated_budget' => 'nullable|numeric|min:0',
            'package_description' => 'nullable|string',
            'is_premium' => 'nullable|boolean',
        ]);

        // Additional validation: male + female should equal adults
        if (($request->male + $request->female) != $request->adults) {
            return back()->withErrors(['adults' => 'Total male and female count must equal total adults.'])->withInput();
        }

        try {
            DB::beginTransaction();

            // Parse the dates
            $checkInTime = Carbon::createFromFormat('Y-m-d', $request->start_date);
            $checkOutTime = Carbon::createFromFormat('Y-m-d', $request->end_date);

            // Generate tour ID and save the tour
            $max_tour_id = Tour::max('tour_id') ?? 0;
            $tourId = CommonHelper::createId($max_tour_id);

            $display_id = 'DMC-ORD' . $tourId;

            // Create new tour record following TourController pattern
            $tour = new Tour();
            $tour->destination = $request->user_country;
            $tour->adult = $request->adults;
            $tour->child = $request->children ?? 0;
            $tour->infant = $request->infants ?? 0;
            $tour->agent_id = $request->agent_id;
            $tour->tour_id = $tourId;
            $tour->male_count = $request->male;
            $tour->female_count = $request->female;
            $tour->check_in_time = $checkInTime;
            $tour->check_out_time = $checkOutTime;
            $tour->display_id = $display_id;
            $tour->tour_status = "New Enquiry";
            $tour->city = $request->city;
            $tour->dmc_id = Auth::user()->created_by;
            $tour->child_ages = $request->child_ages ?? null;
            $tour->save();

            DB::commit();

            // Return JSON response for AJAX
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tour package created successfully!',
                    'tour_id' => $tourId,
                    'display_id' => $display_id,
                    'tour' => $tour
                ]);
            }

            return redirect()->route('single-tour-package.create')
                ->with('success', 'Tour package created successfully! Tour ID: ' . $display_id);

        } catch (\Exception $e) {
            DB::rollback();
            
            // Return JSON error response for AJAX
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create tour package. Error: ' . $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create tour package. Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified single tour package.
     * Note: Not used in current workflow - user stays on create page
     */
    public function show($id)
    {
        // Not implemented - user workflow doesn't need this
        return redirect()->route('single-tour-package.create')
            ->with('info', 'Redirected to create page - show functionality not implemented');
    }

    /**
     * Show the form for editing the specified single tour package.
     */
    public function edit($id)
    {
        $package = Tour::findOrFail($id);
        $countries = Country::where('is_active', 1)->orderBy('name')->get();
        $cities = City::orderBy('name')->get();
        
        $agents = Agent::where('sales_manager_dmc', Auth::id())
            ->orderBy('name')
            ->get();

        return view('single-tour-package.edit', compact('package', 'countries', 'cities', 'agents'));
    }

    /**
     * Update the specified single tour package in storage.
     */
    public function update(Request $request, $id)
    {
        $package = Tour::findOrFail($id);

        $request->validate([
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0',
            'infants' => 'required|integer|min:0',
            'agent_id' => 'required|exists:agents,agent_id',
            'package_name' => 'required|string|max:255',
            'estimated_budget' => 'nullable|numeric|min:0',
            'package_description' => 'nullable|string',
            'is_premium' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $package->update([
                'country_id' => $request->country_id,
                'city_id' => $request->city_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'adults' => $request->adults,
                'children' => $request->children,
                'infants' => $request->infants,
                'agent_id' => $request->agent_id,
                'package_name' => $request->package_name,
                'estimated_budget' => $request->estimated_budget,
                'package_description' => $request->package_description,
                'is_premium' => $request->has('is_premium'),
                'updated_by' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('single-tour-package.index')
                ->with('success', 'Single tour package updated successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update tour package. Please try again.');
        }
    }

    /**
     * Remove the specified single tour package from storage.
     */
    public function destroy($id)
    {
        try {
            $package = Tour::findOrFail($id);
            $package->delete();

            return redirect()->route('single-tour-package.index')
                ->with('success', 'Single tour package deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete tour package. Please try again.');
        }
    }

    /**
     * Fetch cities by country for auto-population (following agent controller pattern)
     */
    public function fetchCitiesByCountry(Request $request) 
    {
        $countryName = $request->input('country');
        
        $cities = City::where('country', $countryName)
                ->select('name', 'city_id', 'id')
                ->get();
                 
        return response()->json(['cities' => $cities]);
    }

    /**
     * Fetch ports by country for dynamic filtering
     */
    public function fetchPortsByCountry(Request $request) 
    {
        $countryId = $request->input('country_id');
        
        if (!$countryId) {
            return response()->json(['ports' => []]);
        }
        
        $country = Country::where('name', $countryId)->first();
        if (!$country) {
            return response()->json(['ports' => []]);
        }
        
        $ports = Port::where('country', $country->name)
                ->select('id', 'port_id', 'port_name', 'country')
                ->orderBy('port_name')
                ->get();
                 
        return response()->json(['ports' => $ports]);
    }

    /**
     * Fetch zone-assigned locations (attractions, hotels, restaurants) by DMC ID
     */
    public function fetchZoneAssignedLocations(Request $request) 
    {
        try {
            $user = User::where('userId', Auth::user()->userId)->first();
            $dmcId = $user->created_by;
            
            // Debug logging
            \Log::info('FetchZoneAssignedLocations Debug', [
                'user_id' => Auth::user()->userId,
                'user_created_by' => $user->created_by,
                'dmc_id' => $dmcId
            ]);
            
            if (!$dmcId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine DMC ID'
                ], 403);
            }

            $locations = [];

            // Fetch attractions that have zone assignments for this DMC
            $attractions = Attraction::where('status', 1)
                ->where('is_active', 1)
                ->get()
                ->filter(function ($attraction) use ($dmcId) {
                    // Check if this attraction has zone assignments for the current DMC
                    return $attraction->isAssignedToZoneByDmc($dmcId);
                })
                ->map(function ($attraction) {
                    return [
                        'id' => $attraction->attraction_id,
                        'name' => $attraction->name,
                        'location' => $attraction->location,
                        'type' => 'attraction',
                        'latitude' => $attraction->latitude,
                        'longitude' => $attraction->longitude
                    ];
                });

            // Fetch hotels that have zone assignments for this DMC
            $hotels = \App\Models\Hotel::where('status', 1)
                ->where('is_active', 1)
                ->get()
                ->filter(function ($hotel) use ($dmcId) {
                    // Check if this hotel has zone assignments for the current DMC
                    return $hotel->isAssignedToZoneByDmc($dmcId);
                })
                ->map(function ($hotel) {
                    return [
                        'id' => $hotel->hotel_unique_id,
                        'name' => $hotel->name,
                        'location' => $hotel->city,
                        'type' => 'hotel',
                        'latitude' => $hotel->latitude,
                        'longitude' => $hotel->longitude
                    ];
                });

            // Fetch restaurants that have zone assignments for this DMC
            $restaurants = Restaurant::where('status', 1)
                ->where('is_active', 1)
                ->get()
                ->filter(function ($restaurant) use ($dmcId) {
                    // Check if this restaurant has zone assignments for the current DMC
                    return $restaurant->isAssignedToZoneByDmc($dmcId);
                })
                ->map(function ($restaurant) {
                    return [
                        'id' => $restaurant->restaurant_id,
                        'name' => $restaurant->name,
                        'location' => $restaurant->location,
                        'type' => 'restaurant',
                        'latitude' => $restaurant->latitude,
                        'longitude' => $restaurant->longitude
                    ];
                });

            // If no zone-assigned locations found, fallback to DMC-selected locations
            if ($attractions->count() == 0 && $hotels->count() == 0 && $restaurants->count() == 0) {
                \Log::info('No zone-assigned locations found, falling back to DMC-selected locations');
                
                // Fallback: Get attractions selected by this DMC
                $attractions = Attraction::where('status', 1)
                    ->where('is_active', 1)
                    ->get()
                    ->filter(function ($attraction) use ($dmcId) {
                        return $attraction->hasSelectedByDmc($dmcId);
                    })
                    ->map(function ($attraction) {
                        return [
                            'id' => $attraction->attraction_id,
                            'name' => $attraction->name,
                            'location' => $attraction->location,
                            'type' => 'attraction',
                            'latitude' => $attraction->latitude,
                            'longitude' => $attraction->longitude
                        ];
                    });

                // Fallback: Get hotels selected by this DMC
                $hotels = \App\Models\Hotel::where('status', 1)
                    ->where('is_active', 1)
                    ->get()
                    ->filter(function ($hotel) use ($dmcId) {
                        return $hotel->hasSelectedByDmc($dmcId);
                    })
                    ->map(function ($hotel) {
                        return [
                            'id' => $hotel->hotel_unique_id,
                            'name' => $hotel->name,
                            'location' => $hotel->city,
                            'type' => 'hotel',
                            'latitude' => $hotel->latitude,
                            'longitude' => $hotel->longitude
                        ];
                    });

                // Fallback: Get restaurants selected by this DMC
                $restaurants = Restaurant::where('status', 1)
                    ->where('is_active', 1)
                    ->get()
                    ->filter(function ($restaurant) use ($dmcId) {
                        return $restaurant->hasSelectedByDmc($dmcId);
                    })
                    ->map(function ($restaurant) {
                        return [
                            'id' => $restaurant->restaurant_id,
                            'name' => $restaurant->name,
                            'location' => $restaurant->location,
                            'type' => 'restaurant',
                            'latitude' => $restaurant->latitude,
                            'longitude' => $restaurant->longitude
                        ];
                    });
            }

            // Combine all locations
            $locations = $attractions->concat($hotels)->concat($restaurants)->sortBy('name');

            \Log::info('Final locations result', [
                'total_locations' => count($locations),
                'attractions_count' => $attractions->count(),
                'hotels_count' => $hotels->count(),
                'restaurants_count' => $restaurants->count(),
                'dmc_id' => $dmcId
            ]);

            return response()->json([
                'success' => true,
                'locations' => $locations->values()->toArray(), // Convert collection to array
                'total_locations' => count($locations),
                'dmc_id' => $dmcId,
                'breakdown' => [
                    'attractions' => $attractions->count(),
                    'hotels' => $hotels->count(),
                    'restaurants' => $restaurants->count()
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in fetchZoneAssignedLocations', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching zone-assigned locations: ' . $e->getMessage(),
                'debug' => [
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile()
                ]
            ], 500);
        }
    }

    /**
     * Get DMC ID based on current user hierarchy
     */
    private function getDmcId()
    {
        try {
            $currentUser = Auth::user();
            $dmcId = null;

            // If user has agent_id, get DMC from agent hierarchy
            if (isset($currentUser->agent_id) && $currentUser->agent_id) {
                $agent = Agent::where('agent_id', $currentUser->agent_id)->first();
                if ($agent) {
                    $sales_manager_dmc = $agent->sales_manager_dmc;
                    
                    // Based on role hierarchy, find the DMC
                    if ($agent->role_id == 11) {
                        $dmcId = $sales_manager_dmc;
                    } elseif (in_array($agent->role_id, [33, 128, 129, 130, 134, 135, 136, 138])) {
                        $sales_head = User::where('userId', $sales_manager_dmc)->first();
                        $dmcId = $sales_head ? $sales_head->created_by : null;
                    } elseif ($agent->role_id == 37) {
                        $sales_manager = User::where('userId', $sales_manager_dmc)->first();
                        if ($sales_manager) {
                            $sales_head = User::where('userId', $sales_manager->created_by)->first();
                            $dmcId = $sales_head ? $sales_head->created_by : null;
                        }
                    } elseif ($agent->role_id == 38) {
                        $assistant_sales_manager = User::where('userId', $sales_manager_dmc)->first();
                        if ($assistant_sales_manager) {
                            $sales_manager = User::where('userId', $assistant_sales_manager->created_by)->first();
                            if ($sales_manager) {
                                $sales_head = User::where('userId', $sales_manager->created_by)->first();
                                $dmcId = $sales_head ? $sales_head->created_by : null;
                            }
                        }
                    }
                }
            } elseif (isset($currentUser->userId) && $currentUser->userId) {
                // Direct user - check if they're a DMC or sales head
                $dmcId = $currentUser->userId;
            }

            return $dmcId;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Fetch attractions for the current DMC
     */
    public function fetchAttractions(Request $request)
    {
        try {
            $user = User::where('userId', Auth::user()->userId)->first();
            $dmcId = $user->created_by;
            
            if (!$dmcId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine DMC ID'
                ], 403);
            }

            // Fetch attractions where dmc_id JSON contains current DMC ID
            // Using LIKE instead of JSON_CONTAINS for better compatibility
            $attractions = Attraction::whereJsonContains('dmc_id', (int) $dmcId)
                        ->select('attraction_id', 'name', 'open_time', 'close_time', 'location', 'adult_price', 'child_price', 'senior_adult_price')
                        ->get();

            $attractionsData = $attractions->map(function ($attraction) {
                // Parse time slots from JSON
                $openTimes = [];
                $closeTimes = [];
                
                // Handle open_time
                if ($attraction->open_time) {
                    $decoded = json_decode($attraction->open_time, true);
                    if (is_array($decoded)) {
                        $openTimes = $decoded;
                    } elseif (is_string($attraction->open_time)) {
                        // If it's just a string time, wrap it in array
                        $openTimes = [$attraction->open_time];
                    }
                }
                
                // Handle close_time
                if ($attraction->close_time) {
                    $decoded = json_decode($attraction->close_time, true);
                    if (is_array($decoded)) {
                        $closeTimes = $decoded;
                    } elseif (is_string($attraction->close_time)) {
                        // If it's just a string time, wrap it in array
                        $closeTimes = [$attraction->close_time];
                    }
                }
                
                // Generate time slots
                $timeSlots = [];
                if (!empty($openTimes) && !empty($closeTimes) && is_array($openTimes) && is_array($closeTimes)) {
                    foreach ($openTimes as $index => $openTime) {
                        $closeTime = $closeTimes[$index] ?? $closeTimes[0];
                        $timeSlots[] = [
                            'open' => $openTime,
                            'close' => $closeTime,
                            'slot' => $openTime . ' - ' . $closeTime
                        ];
                    }
                }

                return [
                    'attraction_id' => $attraction->attraction_id,
                    'name' => $attraction->name,
                    'location' => $attraction->location,
                    'time_slots' => $timeSlots,
                    'adult_price' => $attraction->adult_price,
                    'child_price' => $attraction->child_price,
                    'senior_price' => $attraction->senior_adult_price
                ];
            });

            return response()->json([
                'success' => true,
                'attractions' => $attractionsData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching attractions: ' . $e->getMessage(),
                'debug' => [
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile()
                ]
            ], 500);
        }
    }

    /**
     * Fetch tickets for a specific attraction
     */
    public function fetchTickets(Request $request)
    {
        try {
            $attractionId = $request->input('attraction_id');
            
            if (!$attractionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attraction ID is required'
                ], 400);
            }

            $tickets = Ticket::where('attraction_id', $attractionId)
                ->select('ticket_id', 'name', 'child_price', 'adult_price', 'senior_adult_price', 'description')
                ->get();

            return response()->json([
                'success' => true,
                'tickets' => $tickets
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching tickets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch hotels for the current DMC by city
     */
    public function fetchHotels(Request $request)
    {
        try {
            $user = User::where('userId', Auth::user()->userId)->first();
            $dmcId = $user->created_by;
            $city = $request->input('city');
            
            if (!$dmcId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine DMC ID'
                ], 403);
            }

            if (!$city) {
                return response()->json([
                    'success' => false,
                    'message' => 'City is required'
                ], 400);
            }

            // Fetch hotels where dmc_id JSON contains current DMC ID and city matches
            $hotels = \App\Models\Hotel::whereJsonContains('dmc_id', (int) $dmcId)
                ->where('status', 1)
                ->where('is_active', 1)
                ->where(function ($q) use ($city) {
                    $q->whereRaw('LOWER(city) = ?', [strtolower($city)]);
                })
                ->select('hotel_unique_id', 'name', 'city', 'main_image', 'hotel_star_rating', 'latitude', 'longitude', 'check_in_time', 'check_out_time')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'hotels' => $hotels,
                'total_hotels' => count($hotels),
                'city' => $city
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching hotels: ' . $e->getMessage(),
                'debug' => [
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile()
                ]
            ], 500);
        }
    }

    /**
     * Fetch rooms for a specific hotel
     */
    public function fetchRooms(Request $request)
    {
        try {
            $hotelId = $request->input('hotel_id');
            $dmcId = $request->input('dmc_id');
            
            if (!$hotelId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hotel ID is required'
                ], 400);
            }

            if (!$dmcId) {
                return response()->json([
                    'success' => false,
                    'message' => 'DMC ID is required'
                ], 400);
            }

            // Log the query parameters for debugging
            \Log::info('Fetching rooms for hotel', [
                'hotel_id' => $hotelId,
                'dmc_id' => $dmcId,
                'user_id' => Auth::id()
            ]);

            // Fetch rooms for the selected hotel filtered by DMC ID (created_by)
            $rooms = \App\Models\Room::where('hotel_id', $hotelId)
                ->where('status', 1)
                ->where('created_by', $dmcId) // Filter by DMC ID
                ->select('room_id', 'room_type', 'weekday_price', 'weekend_price', 'double_weekday_price', 'double_weekend_price', 
                        'breakfast', 'breakfast_type', 'lunch', 'lunch_type', 'dinner', 'dinner_type',
                        'breakfast_included', 'dimension', 'features', 'master_image', 'created_by')
                ->orderBy('room_type')
                ->get();

            // If no rooms found with created_by, try alternative field names
            if ($rooms->count() == 0) {
                \Log::info('No rooms found with created_by, trying alternative fields');
                
                // Try alternative field names for DMC ID
                $rooms = \App\Models\Room::where('hotel_id', $hotelId)
                    ->where('status', 1)
                    ->where(function($query) use ($dmcId) {
                        $query->where('created_by', $dmcId)
                              ->orWhere('dmc_id', $dmcId)
                              ->orWhere('company_id', $dmcId)
                              ->orWhere('user_id', $dmcId);
                    })
                    ->select('room_id', 'room_type', 'weekday_price', 'weekend_price', 'double_weekday_price', 'double_weekend_price', 
                            'breakfast', 'breakfast_type', 'lunch', 'lunch_type', 'dinner', 'dinner_type',
                            'breakfast_included', 'dimension', 'features', 'master_image', 'created_by', 'dmc_id', 'company_id', 'user_id')
                    ->orderBy('room_type')
                    ->get();
                
                \Log::info('Alternative query results', [
                    'rooms_found' => $rooms->count(),
                    'dmc_id' => $dmcId
                ]);
            }

            // Log the results for debugging
            \Log::info('Rooms fetched', [
                'hotel_id' => $hotelId,
                'dmc_id' => $dmcId,
                'total_rooms_found' => count($rooms),
                'room_ids' => $rooms->pluck('room_id')->toArray()
            ]);

            // Debug: Check what fields are actually available in the first room
            if ($rooms->count() > 0) {
                $firstRoom = $rooms->first();
                \Log::info('First room structure', [
                    'room_id' => $firstRoom->room_id,
                    'hotel_id' => $firstRoom->hotel_id,
                    'created_by' => $firstRoom->created_by ?? 'NOT_FOUND',
                    'all_fields' => $firstRoom->toArray()
                ]);
            }

            // Debug: Check total rooms for this hotel without DMC filtering
            $totalRoomsForHotel = \App\Models\Room::where('hotel_id', $hotelId)
                ->where('status', 1)
                ->count();
            
            \Log::info('Total rooms for hotel (without DMC filtering)', [
                'hotel_id' => $hotelId,
                'total_rooms' => $totalRoomsForHotel,
                'rooms_with_dmc_filter' => count($rooms)
            ]);

            return response()->json([
                'success' => true,
                'rooms' => $rooms,
                'total_rooms' => count($rooms),
                'hotel_id' => $hotelId,
                'dmc_id' => $dmcId,
                'filtered_by_dmc' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching rooms: ' . $e->getMessage(),
                'debug' => [
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile()
                ]
            ], 500);
        }
    }

    /**
     * Fetch beds for a specific room
     */
    public function fetchBeds(Request $request)
    {
        try {
            $roomId = $request->input('room_id');
            if (!$roomId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room ID is required'
                ], 400);
            }

            // Fetch beds for the selected room
            $beds = \App\Models\Bed::where('room_id', $roomId)
                ->where('is_active', 1)
                ->select('bed_id', 'room_type', 'no_of_rooms', 'max_occupancy', 'adult_count', 'child_count', 
                        'extra_bed', 'extra_bed_price', 'extra_bed_type', 'baby_cot', 'baby_cot_price')
                ->orderBy('room_type')
                ->get();
            return response()->json([
                'success' => true,
                'beds' => $beds,
                'total_beds' => count($beds),
                'room_id' => $roomId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching beds: ' . $e->getMessage(),
                'debug' => [
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile()
                ]
            ], 500);
        }
    }

    /**
     * Fetch guides for the current DMC
     */
    public function fetchGuides(Request $request)
    {
        try {
            $user = User::where('userId', Auth::user()->userId)->first();
            $dmcId = $user->created_by;
            
            if (!$dmcId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine DMC ID'
                ], 403);
            }

            // Fetch guides where dmc_id matches current DMC ID (bigint type)
            $guides = Guide::where('dmc_id', $dmcId)
                ->where('status', 1)
                ->select('guide_id', 'name', 'night_start_time', 'night_end_time', 
                        'day_rate', 'night_surcharge', 'hourly_price', 
                        'two_hour_price', 'four_hour_price', 'six_hour_price', 
                        'eight_hour_price', 'ten_hour_price', 'twelve_hour_price')
                ->get();

            $guidesData = $guides->map(function ($guide) {
                return [
                    'guide_id' => $guide->guide_id,
                    'name' => $guide->name,
                    'night_start_time' => $guide->night_start_time,
                    'night_end_time' => $guide->night_end_time,
                    'day_rate' => $guide->day_rate,
                    'night_surcharge' => $guide->night_surcharge,
                    'hourly_price' => $guide->hourly_price,
                    'two_hour_price' => $guide->two_hour_price,
                    'four_hour_price' => $guide->four_hour_price,
                    'six_hour_price' => $guide->six_hour_price,
                    'eight_hour_price' => $guide->eight_hour_price,
                    'ten_hour_price' => $guide->ten_hour_price,
                    'twelve_hour_price' => $guide->twelve_hour_price,
                ];
            });

            return response()->json([
                'success' => true,
                'guides' => $guidesData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching guides: ' . $e->getMessage(),
                'debug' => [
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile()
                ]
            ], 500);
        }
    }

    /**
     * Fetch restaurants for the current DMC
     */
    public function fetchRestaurants(Request $request)
    {
        try {
            $user = User::where('userId', Auth::user()->userId)->first();
            $dmcId = $user->created_by;
            
            if (!$dmcId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine DMC ID'
                ], 403);
            }
            
            // Fetch restaurants where dmc_id JSON contains current DMC ID AND have meals in meals table
            $restaurants = Restaurant::whereJsonContains('dmc_id', (int) $dmcId)
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('meals')
                          ->whereRaw('meals.restaurant_id = restaurants.restaurant_id');
                })
                ->select('restaurant_id', 'name', 'breakfast_available', 'lunch_available', 'dinner_available',
                         'opening_time_bf', 'closing_time_bf', 'opening_time_lunch', 'closing_time_lunch',
                         'opening_time_dinner', 'closing_time_dinner')
                ->get();

            $restaurantsData = $restaurants->map(function ($restaurant) {
                $mealTypes = [];
                
                // Add breakfast if available
                if ($restaurant->breakfast_available == 1) {
                    $mealTypes[] = [
                        'type' => 'breakfast',
                        'label' => 'Breakfast',
                        'open_time' => $this->formatTimeTo12Hour($restaurant->opening_time_bf),
                        'close_time' => $this->formatTimeTo12Hour($restaurant->closing_time_bf)
                    ];
                }
                
                // Add lunch if available
                if ($restaurant->lunch_available == 1) {
                    $mealTypes[] = [
                        'type' => 'lunch',
                        'label' => 'Lunch',
                        'open_time' => $this->formatTimeTo12Hour($restaurant->opening_time_lunch),
                        'close_time' => $this->formatTimeTo12Hour($restaurant->closing_time_lunch)
                    ];
                }
                
                // Add dinner if available
                if ($restaurant->dinner_available == 1) {
                    $mealTypes[] = [
                        'type' => 'dinner',
                        'label' => 'Dinner',
                        'open_time' => $this->formatTimeTo12Hour($restaurant->opening_time_dinner),
                        'close_time' => $this->formatTimeTo12Hour($restaurant->closing_time_dinner)
                    ];
                }

                return [
                    'restaurant_id' => $restaurant->restaurant_id,
                    'name' => $restaurant->name,
                    'meal_types' => $mealTypes
                ];
            });

            return response()->json([
                'success' => true,
                'restaurants' => $restaurantsData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching restaurants: ' . $e->getMessage(),
                'debug' => [
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile()
                ]
            ], 500);
        }
    }

    /**
     * Fetch meals/dishes for a specific restaurant
     */
    public function fetchMealsByRestaurant(Request $request)
    {
        try {
            $restaurantId = $request->input('restaurant_id');
            $mealPeriod = $request->input('meal_period'); // 1=Breakfast, 2=Lunch, 3=Dinner
            
            if (!$restaurantId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Restaurant ID is required'
                ], 400);
            }

            $query = Meal::where('restaurant_id', $restaurantId);
            
            // Filter by meal period if provided
            if ($mealPeriod) {
                $query->where('meal_period', $mealPeriod);
            }

            $meals = $query->select('meal_id', 'name', 'type', 'price', 'adult_price', 'child_price', 'meal_period')
                ->get();

            $mealsData = $meals->map(function ($meal) {
                $dishType = '';
                switch ($meal->type) {
                    case 1:
                        $dishType = 'Buffet';
                        break;
                    case 2:
                        $dishType = 'Set Menu';
                        break;
                    default:
                        $dishType = 'Other';
                }

                $mealPeriodLabel = '';
                switch ($meal->meal_period) {
                    case 1:
                        $mealPeriodLabel = 'Breakfast';
                        break;
                    case 2:
                        $mealPeriodLabel = 'Lunch';
                        break;
                    case 3:
                        $mealPeriodLabel = 'Dinner';
                        break;
                }

                return [
                    'meal_id' => $meal->meal_id,
                    'name' => $meal->name,
                    'type' => $meal->type,
                    'type_label' => $dishType,
                    'meal_period' => $meal->meal_period,
                    'meal_period_label' => $mealPeriodLabel,
                    'price' => $meal->price,
                    'adult_price' => $meal->adult_price,
                    'child_price' => $meal->child_price,
                    'display_name' => $dishType
                ];
            });

            return response()->json([
                'success' => true,
                'meals' => $mealsData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching meals: ' . $e->getMessage(),
                'debug' => [
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile()
                ]
            ], 500);
        }
    }

    /**
     * Format time from 24-hour to 12-hour format
     */
    private function formatTimeTo12Hour($time)
    {
        if (!$time) return '';
        
        try {
            // Parse time (handle both HH:MM:SS and HH:MM formats)
            $carbonTime = \Carbon\Carbon::createFromFormat('H:i:s', $time);
            return $carbonTime->format('g:i A');
        } catch (\Exception $e) {
            try {
                // Try HH:MM format if HH:MM:SS fails
                $carbonTime = \Carbon\Carbon::createFromFormat('H:i', $time);
                return $carbonTime->format('g:i A');
            } catch (\Exception $e2) {
                return $time; // Return original if parsing fails
            }
        }
    }

    /**
     * Fetch zones for transportation dropdowns
     */
    public function fetchZones(Request $request)
    {
        try {
            $user = User::where('userId', Auth::user()->userId)->first();
            $dmcId = $user->created_by;
            $city = $request->input('city');
            $city_id = City::where('name', $city)->first()->city_id;
            if (!$dmcId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine DMC ID'
                ], 403);
            }
            // Fetch zones for the current DMC and city
            $zones = Zone::where('dmc_id', $dmcId)  
                ->where('status', 1)
                ->where('city', $city_id)
                ->select('zone_id', 'zone_name', 'zone_type', 'city', 'description')
                ->orderBy('zone_name')
                ->get();

            return response()->json([
                'success' => true,
                'zones' => $zones,
                'total_zones' => count($zones)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching zones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch vehicles based on zone mapping with proper ID handling
     */
    public function fetchVehiclesByZones(Request $request)
    {
        try {
            $user = User::where('userId', Auth::user()->userId)->first();
            $dmcId = $user->created_by;
            $fromZoneId = $request->from_zone_id;
            $toZoneId = $request->to_zone_id;
            $fromZoneType = $request->from_zone_type;
            $toZoneType = $request->to_zone_type;
            
            if (!$dmcId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine DMC ID'
                ], 403);
            }

            if (!$fromZoneId || !$toZoneId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Both pickup and dropoff zones are required'
                ], 400);
            }

            // Determine the correct IDs based on location types
            $actualFromZoneId = $this->getActualZoneId($fromZoneId, $fromZoneType);
            $actualToZoneId = $this->getActualZoneId($toZoneId, $toZoneType);

            // Debug logging for zone ID resolution
            \Log::info('Zone ID Resolution Debug', [
                'original_from_zone_id' => $fromZoneId,
                'original_to_zone_id' => $toZoneId,
                'from_zone_type' => $fromZoneType,
                'to_zone_type' => $toZoneType,
                'actual_from_zone_id' => $actualFromZoneId,
                'actual_to_zone_id' => $actualToZoneId,
                'dmc_id' => $dmcId
            ]);

            // Fetch vehicles that have zone mappings between the actual zone IDs
            // Try both directions to ensure bidirectional route coverage
            $vehicleMappings = VehicleZoneMapping::where(function($query) use ($actualFromZoneId, $actualToZoneId) {
                $query->where(function($subQuery) use ($actualFromZoneId, $actualToZoneId) {
                    // Original direction
                    $subQuery->where('from_zone_id', $actualFromZoneId)
                             ->where('to_zone_id', $actualToZoneId);
                })->orWhere(function($subQuery) use ($actualFromZoneId, $actualToZoneId) {
                    // Reverse direction for bidirectional routes
                    $subQuery->where('from_zone_id', $actualToZoneId)
                             ->where('to_zone_id', $actualFromZoneId);
                });
            })->get();
                
            // Debug logging for zone mapping query
            \Log::info('Vehicle Zone Mapping Query Debug (Bidirectional)', [
                'from_zone_id' => $actualFromZoneId,
                'to_zone_id' => $actualToZoneId,
                'mappings_found' => $vehicleMappings->count(),
                'original_direction_count' => VehicleZoneMapping::where('from_zone_id', $actualFromZoneId)->where('to_zone_id', $actualToZoneId)->count(),
                'reverse_direction_count' => VehicleZoneMapping::where('from_zone_id', $actualToZoneId)->where('to_zone_id', $actualFromZoneId)->count(),
                'mappings_data' => $vehicleMappings->toArray()
            ]);
                
            // Format the response with vehicle details and pricing
            $vehicles = $vehicleMappings->map(function ($mapping) {
                $vehicle = Vehicle::select('vehicle_id', 'vehicle_name', 'vehicle_type', 'seating_capacity', 'vehicle_model', 'image', 'base_price', 'sharable_base_price', 'service_type')
                    ->where('vehicle_id', $mapping->vehicle_id)
                    ->first();
                    
                if (!$vehicle) {
                    return null;
                }
                
                return [
                    'vehicle_id' => $vehicle->vehicle_id,
                    'vehicle_name' => $vehicle->vehicle_name,
                    'vehicle_type' => $vehicle->vehicle_type,
                    'seating_capacity' => $vehicle->seating_capacity,
                    'vehicle_model' => $vehicle->vehicle_model,
                    'image' => $vehicle->image,
                    'base_price' => $vehicle->base_price,
                    'sharable_base_price' => $vehicle->sharable_base_price,
                    'service_type' => $vehicle->service_type,
                    'from_zone' => $mapping->fromZone->zone_name ?? '',
                    'to_zone' => $mapping->toZone->zone_name ?? '',
                    'mapping_id' => $mapping->mapping_id,
                    // Use the zone mapping prices instead of vehicle base prices
                    'private_price' => $mapping->private_price,
                    'shared_price' => $mapping->shared_price
                ];
            })->filter()->values(); // Remove null values and reindex

            // Debug logging for final response
            \Log::info('Final Vehicle Response', [
                'from_zone_id' => $actualFromZoneId,
                'to_zone_id' => $actualToZoneId,
                'total_vehicles' => count($vehicles),
                'vehicles_data' => $vehicles->toArray()
            ]);
            
            return response()->json([
                'success' => true,
                'vehicles' => $vehicles,
                'total_vehicles' => count($vehicles),
                'from_zone_id' => $actualFromZoneId,
                'to_zone_id' => $actualToZoneId,
                'original_from_zone_id' => $fromZoneId,
                'original_to_zone_id' => $toZoneId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching vehicles: ' . $e->getMessage(),
                'debug' => [
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile()
                ]
            ], 500);
        }
    }

    /**
     * Get the actual zone ID based on location type and ID
     */
    private function getActualZoneId($locationId, $locationType = null)
    {
        // If no type specified, assume it's already the correct ID
        if (!$locationType) {
            return $locationId;
        }

        switch ($locationType) {
            case 'port':
                // For ports, get the port_id
                $port = Port::where('id', $locationId)->first();
                return $port ? $port->port_id : $locationId;
                
            case 'attraction':
                // For attractions, use attraction_id directly
                return $locationId;
                
            case 'hotel':
                // For hotels, use hotel_unique_id directly
                return $locationId;
                
            case 'restaurant':
                // For restaurants, use restaurant_id directly
                return $locationId;
                
            default:
                // For unknown types, return the original ID
                return $locationId;
        }
    }

    /**
     * Fetch vehicles based on city and dmc_id for point to point and hourly services
     */
    public function fetchVehiclesByCityAndDmc(Request $request)
    {
        try {
            $user = User::where('userId', Auth::user()->userId)->first();
            $dmcId = $user->created_by;
            $city = $request->input('city');
            
            if (!$dmcId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine DMC ID'
                ], 403);
            }

            if (!$city) {
                return response()->json([
                    'success' => false,
                    'message' => 'City is required'
                ], 400);
            }

            // Fetch vehicles for the current DMC and city
            $vehicles = Vehicle::where('dmc_id', $dmcId)
                ->where('city', $city)
                ->where('is_available', 1)
                ->select('vehicle_id', 'vehicle_name', 'vehicle_type', 'seating_capacity', 'vehicle_model', 'image', 'base_price', 'sharable_base_price', 'service_type', 'cost_per_hour', 'sharable_cost_per_hour')
                ->orderBy('vehicle_name')
                ->get();

            $vehiclesData = $vehicles->map(function ($vehicle) {
                return [
                    'vehicle_id' => $vehicle->vehicle_id,
                    'vehicle_name' => $vehicle->vehicle_name,
                    'vehicle_type' => $vehicle->vehicle_type,
                    'seating_capacity' => $vehicle->seating_capacity,
                    'vehicle_model' => $vehicle->vehicle_model,
                    'image' => $vehicle->image,
                    'base_price' => $vehicle->base_price,
                    'sharable_base_price' => $vehicle->sharable_base_price,
                    'service_type' => $vehicle->service_type,
                    'cost_per_hour' => $vehicle->cost_per_hour,
                    'sharable_cost_per_hour' => $vehicle->sharable_cost_per_hour,
                    'private_price' => $vehicle->base_price,
                    'shared_price' => $vehicle->sharable_base_price
                ];
            });

            return response()->json([
                'success' => true,
                'vehicles' => $vehiclesData,
                'total_vehicles' => count($vehiclesData),
                'city' => $city,
                'dmc_id' => $dmcId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching vehicles: ' . $e->getMessage(),
                'debug' => [
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile()
                ]
            ], 500);
        }
    }

    /**
     * Get service name based on service type and data using exact field names from your JSON
     */
    private function getServiceName($serviceType, $data)
    {
        switch ($serviceType) {
            case 'hotel':
                return $data['hotelDetails']['hotel_name'] ?? 'Hotel Booking';
            case 'attraction':
                return $data['AttractionName'] ?? 'Attraction Visit'; // Using AttractionName from your JSON
            case 'restaurant':
                return $data['name'] ?? 'Restaurant Booking';
            case 'guide':
                return $data['guide_name'] ?? 'Guide Service'; // Using guide_name from your JSON
            case 'transport':
                return $data['vehicles_name'] ?? 'Transport Service'; // Using vehicles_name from your JSON
            case 'entry_port':
                return $data['name'] ?? 'Entry Port Service';
            case 'exit_port':
                return $data['name'] ?? 'Exit Port Service';
            default:
                return 'Service';
        }
    }

    /**
     * Extract booking date based on service type using exact field names from your JSON
     */
    private function extractBookingDate($serviceType, $data)
    {
        switch ($serviceType) {
            case 'hotel':
                return $data['bookingDate'][0] ?? null; // First date from array
            case 'attraction':
                return $data['bookingDate'] ?? null; // Single date string
            case 'restaurant':
                return $data['bookingDate'] ?? null; // Single date string
            case 'guide':
                return $data['pickupdate'] ?? null; // Using pickupdate from guide data
            case 'transport':
                return $data['pickupdate'] ?? null; // Using pickupdate from transport data
            case 'entry_port':
                return $data['pickupdate'] ?? null; // Using pickupdate from entry port data
            case 'exit_port':
                return $data['exitpickupdate'] ?? null; // Using exitpickupdate from exit port data
            default:
                return null;
        }
    }

    /**
     * Store service orders in orders table (called separately after tour creation)
     */
    public function storeServiceOrders(Request $request)
    {
        $request->validate([
            'tour_id' => 'required|integer',
            'agent_id' => 'required|integer',
            'hotel_data' => 'nullable|string',
            'attraction_data' => 'nullable|string',
            'restaurant_data' => 'nullable|string',
            'guide_data' => 'nullable|string',
            'transport_data' => 'nullable|string',
            'entry_port_data' => 'nullable|string',
            'exit_port_data' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $tourId = $request->tour_id;
            $agentId = $request->agent_id;

            // Debug: Log incoming data
            \Log::info('Store Service Orders Request Data:', [
                'tour_id' => $tourId,
                'agent_id' => $agentId,
                'hotel_data' => $request->hotel_data,
                'attraction_data' => $request->attraction_data,
                'guide_data' => $request->guide_data,
                'restaurant_data' => $request->restaurant_data,
                'transport_data' => $request->transport_data,
                'entry_port_data' => $request->entry_port_data,
                'exit_port_data' => $request->exit_port_data
            ]);
            
            // Debug: Log transport data specifically
            $transportData = $request->transport_data;
            if ($transportData) {
                try {
                    $decodedTransport = json_decode($transportData, true);
                    \Log::info("Transport data decoded successfully", [
                        'count' => count($decodedTransport),
                        'data' => $decodedTransport
                    ]);
                    
                    // Log each transport item
                    foreach ($decodedTransport as $index => $transport) {
                        \Log::info("Transport item {$index} details:", [
                            'id' => $transport['id'] ?? 'no_id',
                            'travel_type' => $transport['travel_type'] ?? 'no_travel_type',
                            'type' => $transport['type'] ?? 'no_type',
                            'vehicles_name' => $transport['vehicles_name'] ?? 'no_vehicle_name',
                            'bookingType' => $transport['bookingType'] ?? 'no_booking_type'
                        ]);
                    }
                } catch (Exception $e) {
                    \Log::error("Error decoding transport data: " . $e->getMessage());
                }
            } else {
                \Log::info("No transport data received");
            }
            
            // Also log to file for debugging
            file_put_contents(storage_path('logs/transport_debug.log'), 
                "=== TRANSPORT DEBUG " . date('Y-m-d H:i:s') . " ===\n" .
                "Tour ID: $tourId\n" .
                "Agent ID: $agentId\n" .
                "Transport Data: " . ($request->transport_data ?: 'EMPTY') . "\n" .
                "Entry Port Data: " . ($request->entry_port_data ?: 'EMPTY') . "\n" .
                "Exit Port Data: " . ($request->exit_port_data ?: 'EMPTY') . "\n" .
                "=====================================\n\n", 
                FILE_APPEND
            );
            
            // Additional debugging for hotel data
            if ($request->hotel_data) {
                $hotelDataDecoded = json_decode($request->hotel_data, true);
                \Log::info('Hotel Data Analysis:', [
                    'raw_hotel_data' => $request->hotel_data,
                    'decoded_hotel_data' => $hotelDataDecoded,
                    'is_array' => is_array($hotelDataDecoded),
                    'count' => is_array($hotelDataDecoded) ? count($hotelDataDecoded) : 0,
                    'first_item_keys' => is_array($hotelDataDecoded) && count($hotelDataDecoded) > 0 ? array_keys($hotelDataDecoded[0]) : []
                ]);
            }

            $serviceTypes = [
                'hotel' => 'hotel_data',
                'attraction' => 'attraction_data', 
                'restaurant' => 'restaurant_data',
                'guide' => 'guide_data',
                'transport' => 'transport_data',
                'entry_port' => 'entry_port_data',
                'exit_port' => 'exit_port_data'
            ];

            $createdOrders = [];

            foreach ($serviceTypes as $type => $dataField) {
                $serviceData = $request->input($dataField);
                
                \Log::info("Processing service type: {$type}", [
                    'dataField' => $dataField,
                    'serviceData' => $serviceData,
                    'isEmpty' => empty($serviceData),
                    'isEmptyArray' => $serviceData === '[]'
                ]);
                
                if ($serviceData && $serviceData !== '[]' && $serviceData !== '') {
                    // Decode JSON data
                    $decodedData = json_decode($serviceData, true);
                    
                    \Log::info("Decoded data for {$type}:", [
                        'decodedData' => $decodedData,
                        'isArray' => is_array($decodedData),
                        'count' => is_array($decodedData) ? count($decodedData) : 0
                    ]);
                    
                    if (is_array($decodedData) && count($decodedData) > 0) {
                        if ($type === 'hotel') {
                            // For hotels, store each hotel booking as a separate order
                            foreach ($decodedData as $hotelBooking) {
                                try {
                                    // Debug: Log the raw hotel data to see what's coming through
                                    \Log::info("Raw hotel data received:", [
                                        'hotel_booking' => $hotelBooking,
                                        'has_fullName' => isset($hotelBooking['fullName']),
                                        'has_hotelDetails' => isset($hotelBooking['hotelDetails']),
                                        'hotel_name_value' => $hotelBooking['hotelDetails']['hotel_name'] ?? 'NOT_SET',
                                        'available_keys' => array_keys($hotelBooking)
                                    ]);
                                    
                                    // Get tour details to extract customer information if hotel data is empty
                                    $tour = Tour::where('tour_id', $tourId)->first();
                                    
                                    // Check if we have valid hotel data or if it's empty
                                    $hasValidHotelData = !empty($hotelBooking['hotelDetails']['hotel_name'] ?? $hotelBooking['hotel_name'] ?? '') && 
                                                       !empty($hotelBooking['fullName'] ?? '');
                                    
                                    if (!$hasValidHotelData) {
                                        \Log::warning("Hotel data is incomplete or empty. Raw data:", [
                                            'hotel_booking' => $hotelBooking,
                                            'tour_id' => $tourId,
                                            'available_keys' => array_keys($hotelBooking)
                                        ]);
                                    }
                                    
                                    // Ensure hotel data includes all required customer details and maintains exact format
                                    $enhancedHotelData = [
                                        // Customer details - use hotel data if available, otherwise use defaults
                                        'fullName' => $hotelBooking['fullName'] ?? 'Guest User',
                                        'email' => $hotelBooking['email'] ?? 'guest@example.com',
                                        'phone' => $hotelBooking['phone'] ?? '0000000000',
                                        'countryCode' => $hotelBooking['countryCode'] ?? '65',
                                        'address1' => $hotelBooking['address1'] ?? 'Address not provided',
                                        'address2' => $hotelBooking['address2'] ?? null,
                                        'state' => $hotelBooking['state'] ?? 'State not provided',
                                        'zip' => $hotelBooking['zip'] ?? '000000',
                                        'specialRequests' => $hotelBooking['specialRequests'] ?? null,
                                        'id' => $hotelBooking['id'] ?? null,
                                        'bookingType' => $hotelBooking['bookingType'] ?? 'enquiry',
                                        
                                        // Hotel booking dates
                                        'bookingDate' => $hotelBooking['bookingDate'] ?? [],
                                        
                                        // Hotel details - ensure we have valid data with proper null checks
                                        'hotelDetails' => [
                                            'hotel_id' => $hotelBooking['hotelDetails']['hotel_id'] ?? $hotelBooking['hotel_id'] ?? 'hotel_' . time(),
                                            'hotel_name' => $hotelBooking['hotelDetails']['hotel_name'] ?? $hotelBooking['hotel_name'] ?? 'Hotel Booking',
                                            'image' => $hotelBooking['hotelDetails']['image'] ?? $hotelBooking['hotel_image'] ?? '',
                                            'location' => $hotelBooking['hotelDetails']['location'] ?? $hotelBooking['hotel_location'] ?? 'Location not specified',
                                            'checkInTime' => $hotelBooking['hotelDetails']['checkInTime'] ?? $hotelBooking['check_in_time'] ?? '15:00:00',
                                            'checkOutTime' => $hotelBooking['hotelDetails']['checkOutTime'] ?? $hotelBooking['check_out_time'] ?? '12:00:00',
                                            'cancellation_charge' => $hotelBooking['hotelDetails']['cancellation_charge'] ?? null
                                        ],
                                        
                                        // Price and mode
                                        'priceMode' => $hotelBooking['priceMode'] ?? 'dmc',
                                        'priceModeId' => $hotelBooking['priceModeId'] ?? 0,
                                        
                                        // Rooms data (exact structure)
                                        'rooms' => $hotelBooking['rooms'] ?? [],
                                        
                                        // Total price
                                        'totalPrice' => $hotelBooking['totalPrice'] ?? 0,
                                        // Price field for database storage
                                        'price' => $hotelBooking['totalPrice'] ?? 0,
                                        
                                        // Tour ID
                                        'tour_id' => $tourId
                                    ];
                                    
                                    // Create separate order for each hotel booking
                                    $lastBooking = Order::orderBy('booking_id', 'desc')->first();
                                    $lastBookingId = $lastBooking ? $lastBooking->booking_id : 0;
                                    $newBookingId = CommonHelper::createId($lastBookingId);
                                    
                                    $order = Order::create([
                                        'booking_id' => $newBookingId,
                                        'agent_id' => $agentId,
                                        'tour_id' => $tourId,
                                        'data' => [$enhancedHotelData], // Store hotel data as array
                                        'type' => $type,
                                        'status' => 1,
                                        'bookingType' => 'enquiry',
                                    ]);

                                    \Log::info("Hotel order created successfully", [
                                        'order_id' => $order->booking_id,
                                        'hotel_name' => $enhancedHotelData['hotelDetails']['hotel_name'],
                                        'tour_id' => $tourId
                                    ]);

                                    $createdOrders[] = [
                                        'type' => $type,
                                        'order_id' => $order->booking_id,
                                        'hotel_name' => $enhancedHotelData['hotelDetails']['hotel_name'],
                                        'data_count' => 1
                                    ];
                                    
                                } catch (\Exception $e) {
                                    \Log::error("Error processing hotel booking:", [
                                        'error' => $e->getMessage(),
                                        'hotel_booking' => $hotelBooking,
                                        'tour_id' => $tourId,
                                        'line' => $e->getLine(),
                                        'file' => $e->getFile()
                                    ]);
                                    
                                    // Continue with next hotel booking instead of failing completely
                                    continue;
                                }
                            }
                            
                        } elseif ($type === 'attraction') {
                            // For attractions, store each attraction as a separate order
                            foreach ($decodedData as $attraction) {
                                // Ensure attraction has proper price field (use totalPrice from frontend calculation)
                                $attraction['price'] = $attraction['totalPrice'] ?? $attraction['price'] ?? 0;
                                
                                // Create separate order for each attraction
                                $lastBooking = Order::orderBy('booking_id', 'desc')->first();
                                $lastBookingId = $lastBooking ? $lastBooking->booking_id : 0;
                                $newBookingId = CommonHelper::createId($lastBookingId);
                                
                                $order = Order::create([
                                    'booking_id' => $newBookingId,
                                    'agent_id' => $agentId,
                                    'tour_id' => $tourId,
                                    'data' => [$attraction], // Store attraction data as array
                                    'type' => $type,
                                    'status' => 1,
                                    'bookingType' => 'enquiry',
                                ]);

                                \Log::info("Attraction order created successfully", [
                                    'order_id' => $order->booking_id,
                                    'attraction_name' => $attraction['attraction_name'] ?? 'Unknown Attraction',
                                    'tour_id' => $tourId
                                ]);

                                $createdOrders[] = [
                                    'type' => $type,
                                    'order_id' => $order->booking_id,
                                    'attraction_name' => $attraction['attraction_name'] ?? 'Unknown Attraction',
                                    'data_count' => 1
                                ];
                            }
                            
                        } elseif ($type === 'restaurant') {
                            // For restaurants, store each restaurant as a separate order
                            foreach ($decodedData as $restaurant) {
                                // Create separate order for each restaurant
                                $lastBooking = Order::orderBy('booking_id', 'desc')->first();
                                $lastBookingId = $lastBooking ? $lastBooking->booking_id : 0;
                                $newBookingId = CommonHelper::createId($lastBookingId);
                                
                                $order = Order::create([
                                    'booking_id' => $newBookingId,
                                    'agent_id' => $agentId,
                                    'tour_id' => $tourId,
                                    'data' => [$restaurant], // Store restaurant data as array
                                    'type' => $type,
                                    'status' => 1,
                                    'bookingType' => 'enquiry',
                                ]);

                                \Log::info("Restaurant order created successfully", [
                                    'order_id' => $order->booking_id,
                                    'restaurant_name' => $restaurant['restaurant_name'] ?? 'Unknown Restaurant',
                                    'tour_id' => $tourId
                                ]);

                                $createdOrders[] = [
                                    'type' => $type,
                                    'order_id' => $order->booking_id,
                                    'restaurant_name' => $restaurant['restaurant_name'] ?? 'Unknown Restaurant',
                                    'data_count' => 1
                                ];
                            }
                            
                        } elseif ($type === 'guide') {
                            // For guides, store each guide as a separate order
                            foreach ($decodedData as $guide) {
                                // Ensure guide has proper price field (use totalPrice from frontend calculation)
                                $guide['price'] = $guide['totalPrice'] ?? 0;
                                
                                // Log guide pricing for debugging
                                \Log::info("Guide pricing data:", [
                                    'guide_name' => $guide['guide_name'] ?? 'Unknown',
                                    'totalPrice' => $guide['totalPrice'] ?? 0,
                                    'basePrice' => $guide['basePrice'] ?? 0,
                                    'hours' => $guide['hours'] ?? 0,
                                    'surcharge' => $guide['surcharge'] ?? 0,
                                    'final_price' => $guide['price'] ?? 0
                                ]);
                                
                                // Create separate order for each guide
                                $lastBooking = Order::orderBy('booking_id', 'desc')->first();
                                $lastBookingId = $lastBooking ? $lastBooking->booking_id : 0;
                                $newBookingId = CommonHelper::createId($lastBookingId);
                                
                                $order = Order::create([
                                    'booking_id' => $newBookingId,
                                    'agent_id' => $agentId,
                                    'tour_id' => $tourId,
                                    'data' => [$guide], // Store guide data as array
                                    'type' => $type,
                                    'status' => 1,
                                    'bookingType' => 'enquiry',
                                ]);

                                \Log::info("Guide order created successfully", [
                                    'order_id' => $order->booking_id,
                                    'guide_name' => $guide['guide_name'] ?? 'Unknown Guide',
                                    'tour_id' => $tourId
                                ]);

                                $createdOrders[] = [
                                    'type' => $type,
                                    'order_id' => $order->booking_id,
                                    'guide_name' => $guide['guide_name'] ?? 'Unknown Guide',
                                    'data_count' => 1
                                ];
                            }
                            
                        } elseif ($type === 'transport') {
                            // For transport, store each transport as a separate order
                            \Log::info("Processing transport data", [
                                'total_transports' => count($decodedData),
                                'transport_data' => $decodedData
                            ]);
                            
                            // Debug: Log each transport item individually
                            foreach ($decodedData as $index => $transport) {
                                \Log::info("Transport item {$index}", [
                                    'id' => $transport['id'] ?? 'no_id',
                                    'travel_type' => $transport['travel_type'] ?? 'no_travel_type',
                                    'type' => $transport['type'] ?? 'no_type',
                                    'vehicles_name' => $transport['vehicles_name'] ?? 'no_vehicle_name',
                                    'bookingType' => $transport['bookingType'] ?? 'no_booking_type',
                                    'pickup_coords' => $transport['PickupPlaceid'] ?? 'no_pickup_coords',
                                    'dropoff_coords' => $transport['DropoffPlaceid'] ?? 'no_dropoff_coords'
                                ]);
                            }
                            
                            foreach ($decodedData as $transport) {
                                // Determine the correct type based on travel_type
                                $orderType = $transport['travel_type'] ?? 'travel_point'; // Default to travel_point if not specified
                                
                                \Log::info("Processing individual transport", [
                                    'transport_id' => $transport['id'] ?? 'no_id',
                                    'vehicles_name' => $transport['vehicles_name'] ?? 'unknown',
                                    'travel_type' => $orderType,
                                    'service_type' => $transport['type'] ?? 'unknown',
                                    'pickup_coords' => $transport['PickupPlaceid'] ?? 'no_coords',
                                    'dropoff_coords' => $transport['DropoffPlaceid'] ?? 'no_coords',
                                    'booking_type' => $transport['bookingType'] ?? 'no_booking_type',
                                    'full_transport_data' => $transport
                                ]);
                                
                                // Create separate order for each transport
                                $lastBooking = Order::orderBy('booking_id', 'desc')->first();
                                $lastBookingId = $lastBooking ? $lastBooking->booking_id : 0;
                                $newBookingId = CommonHelper::createId($lastBookingId);
                                
                                $order = Order::create([
                                    'booking_id' => $newBookingId,
                                    'agent_id' => $agentId,
                                    'tour_id' => $tourId,
                                    'data' => [$transport], // Store transport data as array
                                    'type' => $orderType, // Use the specific travel type
                                    'status' => 1,
                                    'bookingType' => $transport['bookingType'] ?? 'enquiry', // Use bookingType from transport data
                                ]);

                                \Log::info("Transport order created successfully", [
                                    'order_id' => $order->booking_id,
                                    'transport_name' => $transport['vehicles_name'] ?? 'Unknown Transport',
                                    'travel_type' => $orderType,
                                    'tour_id' => $tourId
                                ]);

                                $createdOrders[] = [
                                    'type' => $orderType,
                                    'order_id' => $order->booking_id,
                                    'transport_name' => $transport['vehicles_name'] ?? 'Unknown Transport',
                                    'data_count' => 1
                                ];
                            }
                            
                        } elseif ($type === 'entry_port' || $type === 'exit_port') {
                            // For entry_port and exit_port, treat them as transport data
                            \Log::info("Processing {$type} data as transport", [
                                'total_transports' => count($decodedData),
                                'transport_data' => $decodedData
                            ]);
                            
                            foreach ($decodedData as $transport) {
                                // Determine the correct type based on travel_type
                                $orderType = $transport['travel_type'] ?? 'travel_point'; // Default to travel_point if not specified
                                
                                \Log::info("Processing individual {$type} transport", [
                                    'transport_id' => $transport['id'] ?? 'no_id',
                                    'vehicles_name' => $transport['vehicles_name'] ?? 'unknown',
                                    'travel_type' => $orderType,
                                    'service_type' => $transport['type'] ?? 'unknown',
                                    'pickup_coords' => $transport['PickupPlaceid'] ?? 'no_coords',
                                    'dropoff_coords' => $transport['DropoffPlaceid'] ?? 'no_coords',
                                    'booking_type' => $transport['bookingType'] ?? 'no_booking_type',
                                    'full_transport_data' => $transport
                                ]);
                                
                                // Create separate order for each transport
                                $lastBooking = Order::orderBy('booking_id', 'desc')->first();
                                $lastBookingId = $lastBooking ? $lastBooking->booking_id : 0;
                                $newBookingId = CommonHelper::createId($lastBookingId);
                                
                                $order = Order::create([
                                    'booking_id' => $newBookingId,
                                    'agent_id' => $agentId,
                                    'tour_id' => $tourId,
                                    'data' => [$transport], // Store transport data as array
                                    'type' => $orderType, // Use the specific travel type
                                    'status' => 1,
                                    'bookingType' => $transport['bookingType'] ?? 'enquiry', // Use bookingType from transport data
                                ]);

                                \Log::info("{$type} transport order created successfully", [
                                    'order_id' => $order->booking_id,
                                    'transport_name' => $transport['vehicles_name'] ?? 'Unknown Transport',
                                    'travel_type' => $orderType,
                                    'tour_id' => $tourId
                                ]);

                                $createdOrders[] = [
                                    'type' => $orderType,
                                    'order_id' => $order->booking_id,
                                    'transport_name' => $transport['vehicles_name'] ?? 'Unknown Transport',
                                    'data_count' => 1
                                ];
                            }
                            
                        } else {
                            // For other services, store each as a separate order
                            foreach ($decodedData as $service) {
                                // Create separate order for each service
                                $lastBooking = Order::orderBy('booking_id', 'desc')->first();
                                $lastBookingId = $lastBooking ? $lastBooking->booking_id : 0;
                                $newBookingId = CommonHelper::createId($lastBookingId);
                                
                                $order = Order::create([
                                    'booking_id' => $newBookingId,
                                    'agent_id' => $agentId,
                                    'tour_id' => $tourId,
                                    'data' => [$service], // Store service data as array
                                    'type' => $type,
                                    'status' => 1,
                                    'bookingType' => 'enquiry',
                                ]);

                                \Log::info("{$type} order created successfully", [
                                    'order_id' => $order->booking_id,
                                    'service_name' => $service['port_name'] ?? 'Unknown Service',
                                    'tour_id' => $tourId
                                ]);

                                $createdOrders[] = [
                                    'type' => $type,
                                    'order_id' => $order->booking_id,
                                    'service_name' => $service['port_name'] ?? 'Unknown Service',
                                    'data_count' => 1
                                ];
                            }
                        }
                    }
                }
            }

            DB::commit();

            // Get tour details for thank you page
            $tour = Tour::where('tour_id', $tourId)->first();
            
            // Get all service orders with booking dates for thank you page
            $serviceOrders = Order::where('tour_id', $tourId)
                ->orderBy('created_at', 'asc')
                ->get();
            
            $servicesByDate = [];
            foreach ($serviceOrders as $order) {
                $orderData = $order->data;
                
                // Each order contains an array with one service item
                if (is_array($orderData) && count($orderData) > 0) {
                    $serviceData = $orderData[0]; // Get the first (and only) service item
                    $bookingDate = $this->extractBookingDate($order->type, $serviceData);
                    
                    if ($bookingDate) {
                        $formattedDate = \Carbon\Carbon::parse($bookingDate)->format('M d, Y');
                        if (!isset($servicesByDate[$formattedDate])) {
                            $servicesByDate[$formattedDate] = [];
                        }
                        
                        $servicesByDate[$formattedDate][] = [
                            'type' => $order->type,
                            'name' => $this->getServiceName($order->type, $serviceData),
                            'order_id' => $order->booking_id,
                            'data' => $serviceData // Include service data for synchronization
                        ];
                    }
                }
            }
            
            $tourDetails = [
                'tour_id' => $tourId,
                'display_id' => $tour->display_id ?? 'N/A',
                'destination' => $tour->destination ?? 'N/A',
                'city' => $tour->city ?? 'N/A',
                'check_in_date' => $tour->check_in_time ? \Carbon\Carbon::parse($tour->check_in_time)->format('M d, Y') : 'N/A',
                'check_out_date' => $tour->check_out_time ? \Carbon\Carbon::parse($tour->check_out_time)->format('M d, Y') : 'N/A',
                'total_guests' => ($tour->adult ?? 0) + ($tour->child ?? 0) + ($tour->infant ?? 0),
                'services_by_date' => $servicesByDate
            ];

            return response()->json([
                'success' => true,
                'message' => 'All service orders saved successfully!',
                'created_orders' => $createdOrders,
                'tour_id' => $tourId,
                'tour_details' => $tourDetails,
                'redirect_url' => route('single-tour-package.thank-you')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save service orders. Error: ' . $e->getMessage()
            ], 422);
        }
    }
    
} 