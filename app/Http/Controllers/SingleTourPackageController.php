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
use App\Models\Agency;

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
        $agency = Agency::whereJsonContains('dmc_id', (int) $dmc_id)->orderBy('created_at', 'desc')->get();
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
                        $meals = Restaurant::with('meals')->whereIn('restaurant_id', $restaurant_ids)->get();
                    }
                }
            }
        }

        $userDmcId = CommonHelper::getDmcId(Auth::user());

        $UserDmc = User::select('userId','zone_on')->where('userId', $userDmcId)->first();
        $restaurants = Restaurant::with(['meals'])->whereJsonContains('dmc_id', $userDmcId)->get();
        return view('single-tour-package.create', compact('countries', 'agents', 'ports', 'selectedCountry', 'enquiry', 'hotels', 'attractions', 'guides', 'vehicles', 'meals', 'tickets', 'zones', 'agency', 'restaurants', 'UserDmc'));
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
                    'night_surcharge', 'hourly_price', 
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
        $tour_id = Crypt::decrypt($tour_id);
        $tour = Tour::where('tour_id', $tour_id)->first();
        $tour_agent = Agent::select('name', 'agent_id')->where('agent_id', $tour->agent_id)->first();
        $agent_name = $tour_agent->name;
        $agent_id = $tour_agent->agent_id;
        if (!$tour) {
            \Log::error('Tour not found with tour_id: ' . $tour_id);
            return redirect()->back()->with('error', 'Tour not found.');
        }
        
        // Get hotels based on user's DMC ID
        $userDmcId = CommonHelper::getDmcId(Auth::user());

        $UserDmc = User::select('userId','zone_on')->where('userId', $userDmcId)->first();
        if ($userDmcId) {
            $hotels = Hotel::with(['rooms.bed'])
                ->where('country', $tour->destination)
                ->whereJsonContains('dmc_id', (int)$userDmcId)
                ->get();
            
         } else {
            $hotels = collect(); // Empty collection if no DMC ID
        }
        $guides = Guide::with(['languages'])->where('dmc_id', $userDmcId)->get();

        $restaurants = Restaurant::with(['meals' => function($query) use ($userDmcId) {
            $query->where('dmc_id', $userDmcId);
        }])
        ->whereJsonContains('dmc_id', $userDmcId)
        ->get();

        $attractions = Attraction::with(['tickets' => function($query) use ($userDmcId) {
            $query->where('dmc_id', $userDmcId);
        }])
        ->whereJsonContains('dmc_id', $userDmcId)
        ->get();

        $vehicles = Vehicle::where('dmc_id', $userDmcId)->get();
        $dmc_id = CommonHelper::getDmcId(Auth::user());

        $countries = Country::where('is_active', 1)->orderBy('name')->get();
        $portsQuery = Port::query();
        if ($tour->destination) {
            $country = Country::where('name', $tour->destination)->first();
            if ($country) {
                $portsQuery->where('country', $country->name);
            }
        }
        $cities = City::where('country', $tour->destination)->get();
        $ports = $portsQuery->orderBy('port_name')->get();
        
        $agencies = Agency::whereJsonContains('dmc_id', $userDmcId)->get();
        $agents = Agent::WhereIn('agency_id', $agencies->pluck('agency_id'))
            ->orderBy('name')
            ->get();
        $selectedCountry = $request->country;
        // Fetch all orders for this tour
        $orders = Order::where('tour_id', $tour_id)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();
        $firstOrder = $orders->first();
        $customer_info = [];
        if($firstOrder && $firstOrder->data){
            $customer_info['fullName'] = $firstOrder->data[0]['fullName'] ?? '';
            $customer_info['email'] = $firstOrder->data[0]['email'] ?? '';
            $customer_info['phone'] = $firstOrder->data[0]['phone'] ?? '';
            $customer_info['countryCode'] = $firstOrder->data[0]['countryCode'] ?? '';
            $customer_info['address1'] = $firstOrder->data[0]['address1'] ?? '';
            $customer_info['address2'] = $firstOrder->data[0]['address2'] ?? '';
            $customer_info['state'] = $firstOrder->data[0]['state'] ?? '';
            $customer_info['zip'] = $firstOrder->data[0]['zip'] ?? '';
            $customer_info['specialRequests'] = $firstOrder->data[0]['specialRequests'] ?? '';
        }
        // Group orders by type and process the data
        $ordersByType = [];
        $hotelOrders = [];
        $ordersByDay = [];
        
        // Calculate tour days
        $checkInDate = \Carbon\Carbon::parse($tour->check_in_time);
        $checkOutDate = \Carbon\Carbon::parse($tour->check_out_time);
        $tourDays = [];
        
        // Generate array of tour days
        $currentDate = $checkInDate->copy();
        $dayNumber = 1;
        while ($currentDate <= $checkOutDate) {
            $tourDays[$currentDate->format('Y-m-d')] = [
                'day_number' => $dayNumber,
                'date' => $currentDate->copy(),
                'orders' => []
            ];
            $currentDate->addDay();
            $dayNumber++;
        }
        
        foreach ($orders as $order) {
            $type = $order->type;
            
            // Parse the JSON data
            $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
            
            // Add processed data to the order
            $order->processed_data = $orderData;
            
            // Separate hotel orders
            if ($type === 'hotel') {
                $hotelOrders[] = $order;
            } else {
                // Group by type for compatibility
                if (!isset($ordersByType[$type])) {
                    $ordersByType[$type] = [];
                }
                $ordersByType[$type][] = $order;
                
                // Group by day based on booking date
                $bookingDate = null;
                
                // Extract booking date from order data
                if (isset($orderData[0]['bookingDate'])) {
                    $bookingDate = $orderData[0]['bookingDate'];
                } elseif (isset($orderData['bookingDate'])) {
                    $bookingDate = $orderData['bookingDate'];
                } elseif (isset($orderData[0]['pickupdate'])) {
                    $bookingDate = $orderData[0]['pickupdate'];
                } elseif (isset($orderData['pickupdate'])) {
                    $bookingDate = $orderData['pickupdate'];
                } elseif (isset($orderData[0]['exitpickupdate'])) {
                    $bookingDate = $orderData[0]['exitpickupdate'];
                } elseif (isset($orderData['exitpickupdate'])) {
                    $bookingDate = $orderData['exitpickupdate'];
                }
                
                // Handle special cases for entry/exit ports
                if ($type === 'entry_port') {
                    $bookingDate = $checkInDate->format('Y-m-d');
                } elseif ($type === 'exit_port') {
                    $bookingDate = $checkOutDate->format('Y-m-d');
                }
                
                // Add order to the appropriate day
                if ($bookingDate) {
                    // Handle array of dates (shouldn't happen for non-hotel bookings, but just in case)
                    if (is_array($bookingDate)) {
                        $bookingDate = $bookingDate[0];
                    }
                    
                    // Normalize date format
                    $bookingDate = \Carbon\Carbon::parse($bookingDate)->format('Y-m-d');
                    
                    // Add to the appropriate day if it's within tour dates
                    if (isset($tourDays[$bookingDate])) {
                        $tourDays[$bookingDate]['orders'][] = $order;
                    }
                }
            }
        }

        

        return view('single-tour-package.edit', compact('tour', 'countries', 'agents', 'ports', 'selectedCountry', 'ordersByType','agent_name','hotels','guides','restaurants','attractions','customer_info','agent_id','vehicles', 'hotelOrders', 'tourDays', 'cities', 'UserDmc'));
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
            $order = Order::where('booking_id', $id);
            
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
        try{
        $request->validate([
            'user_country' => 'required|string', // Country name
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
        }catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }


        // Parse the dates
        $checkInTime = Carbon::createFromFormat('Y-m-d', $request->start_date);
        $checkOutTime = Carbon::createFromFormat('Y-m-d', $request->end_date);
        $today = Carbon::today();
        if($today > $checkInTime){
            
            $error = 'Start date cannot be in the past';
            return back()->withErrors(['start_date' => $error])->withInput();
        }

        try {

            // Generate tour ID and save the tour
            $max_tour_id = Tour::max('tour_id') ?? 0;
            $tourId = CommonHelper::createId($max_tour_id);

            $display_id = 'DMC-ORD' . $tourId;

            $userDmcId = CommonHelper::getDmcId(Auth::user());
            $userDMC = User::where('userId', $userDmcId)->first();
            $auto_cancel_day = (int) $userDMC->auto_cancel_date; // e.g. 1
            $auto_cancel_date = $checkInTime->copy()->subDays($auto_cancel_day)->toDateString();

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
            $tour->auto_cancel_date = $auto_cancel_date;
            $tour->save();

            $thisTour = Tour::where('tour_id', $tour->tour_id)->first();
            if($request->enquiry_id){
                EnquiryForm::where('enquiry_id', $request->enquiry_id)->update(['unique_tour_id' => $thisTour->unique_tour_id]);
            }
            $cities = City::where('country', $request->user_country)->get();
            // Return JSON response for AJAX
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tour package created successfully!',
                    'tour_id' => $tourId,
                    'display_id' => $display_id,
                    'tour' => $tour,
                    'cities' => $cities
                ]);
            }

            return redirect()->route('single-tour-package.create')
                ->with('success', 'Tour package created successfully! Tour ID: ' . $display_id)
                ->with('cities', $cities);

        } catch (\Exception $e) {
            
            
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
            $city = $request->input('city');
            
            if (!$dmcId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine DMC ID'
                ], 403);
            }

            // Fetch attractions where dmc_id JSON contains current DMC ID
            $query = Attraction::whereJsonContains('dmc_id', (int) $dmcId)
                        ->select('attraction_id', 'name', 'open_time', 'close_time', 'location', 'adult_price', 'child_price', 'senior_adult_price');
            
            // Filter by city if provided
            if ($city) {
                $query->where(function($q) use ($city) {
                    $q->where('location', $city);
                });
            }
            
            $attractions = $query->get();

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
                    'city' => $attraction->location,
                    'time_slots' => $timeSlots,
                    'adult_price' => $attraction->adult_price,
                    'child_price' => $attraction->child_price,
                    'senior_price' => $attraction->senior_adult_price
                ];
            });

            return response()->json([
                'success' => true,
                'attractions' => $attractionsData,
                'filtered_by_city' => !empty($city),
                'city' => $city
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
     * Fetch attractions by DMC and city
     */
    public function fetchAttractionsByDmc(Request $request)
    {
        try {
            $city = $request->input('city');
            $dmcId = $request->input('dmc_id') ?? Auth::user()->created_by;
            
            if (!$dmcId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine DMC ID'
                ], 403);
            }
            
            if (!$city) {
                return response()->json([
                    'success' => false,
                    'message' => 'City parameter is required'
                ], 400);
            }

            // Fetch attractions for the specific city and DMC
            // Note: Attractions table uses 'location' field instead of 'city'
            $query = Attraction::whereJsonContains('dmc_id', (int) $dmcId)
                ->where(function($q) use ($city) {
                    $q->where('location', $city);
                })
                ->select('attraction_id', 'name', 'open_time', 'close_time', 'location', 'adult_price', 'child_price', 'senior_adult_price');
            
            $attractions = $query->get();
            // Transform the attractions data to include city (mapped from location) and parse time slots
            $attractionsData = $attractions->map(function($attraction) {
                // Parse time slots from JSON
                $openTimes = [];
                $closeTimes = [];
                
                // Handle open_time JSON - check if it's already an array or needs parsing
                if ($attraction->open_time) {
                    \Log::info("Processing open_time for attraction {$attraction->name}", [
                        'raw_open_time' => $attraction->open_time,
                        'type' => gettype($attraction->open_time)
                    ]);
                    
                    if (is_array($attraction->open_time)) {
                        // Already an array from database
                        $openTimes = $attraction->open_time;
                    } elseif (is_string($attraction->open_time)) {
                        // Try to decode JSON string
                        $decoded = json_decode($attraction->open_time, true);
                        if (is_array($decoded)) {
                            $openTimes = $decoded;
                        } else {
                            // If it's just a string time, wrap it in array
                            $openTimes = [$attraction->open_time];
                        }
                    }
                    
                    \Log::info("Parsed open_times", ['open_times' => $openTimes]);
                }
                
                // Handle close_time JSON - check if it's already an array or needs parsing
                if ($attraction->close_time) {
                    \Log::info("Processing close_time for attraction {$attraction->name}", [
                        'raw_close_time' => $attraction->close_time,
                        'type' => gettype($attraction->close_time)
                    ]);
                    
                    if (is_array($attraction->close_time)) {
                        // Already an array from database
                        $closeTimes = $attraction->close_time;
                    } elseif (is_string($attraction->close_time)) {
                        // Try to decode JSON string
                        $decoded = json_decode($attraction->close_time, true);
                        if (is_array($decoded)) {
                            $closeTimes = $decoded;
                        } else {
                            // If it's just a string time, wrap it in array
                            $closeTimes = [$attraction->close_time];
                        }
                    }
                    
                    \Log::info("Parsed close_times", ['close_times' => $closeTimes]);
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
                
                \Log::info("Generated time slots for attraction {$attraction->name}", [
                    'time_slots' => $timeSlots,
                    'open_times_count' => count($openTimes),
                    'close_times_count' => count($closeTimes)
                ]);
                
                return [
                    'attraction_id' => $attraction->attraction_id,
                    'attraction_unique_id' => $attraction->attraction_id, // For compatibility
                    'name' => $attraction->name,
                    'location' => $attraction->location,
                    'city' => $attraction->location, // Map location to city for frontend compatibility
                    'time_slots' => $timeSlots,
                    'adult_price' => $attraction->adult_price,
                    'child_price' => $attraction->child_price,
                    'senior_adult_price' => $attraction->senior_adult_price
                ];
            });
            
            \Log::info("Fetching attractions for city: {$city}", [
                'dmc_id' => $dmcId,
                'city' => $city,
                'attractions_found' => $attractions->count()
            ]);

            return response()->json([
                'success' => true,
                'attractions' => $attractionsData,
                'city' => $city,
                'count' => $attractions->count()
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
            $dmcId = $request->input('dmc_id') ?? Auth::user()->created_by;
            
            if (!$attractionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attraction ID is required'
                ], 400);
            }

            if (!$dmcId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine DMC ID'
                ], 403);
            }

            // First verify that the attraction belongs to the current DMC
            $attraction = Attraction::where('attraction_id', $attractionId)
                ->whereJsonContains('dmc_id', (int) $dmcId)
                ->first();

            if (!$attraction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attraction not found or does not belong to your DMC'
                ], 404);
            }

            // Fetch tickets for the attraction
            $tickets = Ticket::where('attraction_id', $attractionId)->where('dmc_id', $dmcId)
                ->select('ticket_id', 'name', 'child_price', 'adult_price', 'senior_adult_price', 'description')
                ->get();

            \Log::info("Fetching tickets for attraction: {$attractionId}", [
                'dmc_id' => $dmcId,
                'attraction_id' => $attractionId,
                'tickets_found' => $tickets->count()
            ]);

            return response()->json([
                'success' => true,
                'tickets' => $tickets,
                'attraction_id' => $attractionId,
                'count' => $tickets->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching tickets: ' . $e->getMessage(),
                'debug' => [
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile()
                ]
            ], 500);
        }
    }

    /**
     * Fetch hotels for the current DMC by city
     */
    public function fetchHotels(Request $request)
    {
        try {
            if(Auth::user()->role_id == 11){
                $dmcId = Auth::user()->userId;
            }elseif(in_array(Auth::user()->role_id, [33, 34, 128, 129, 130, 131, 132, 134, 135, 136, 137, 138])){
                $user = User::where('userId', Auth::user()->userId)->first();
                $dmcId = $user->created_by;
            }elseif(in_array(Auth::user()->role_id, [37, 124])){
                $dmcIds = Auth::user()->created_by;
                $user = User::where('userId', $dmcIds)->first();
                $dmcId = $user->created_by;
            }elseif(in_array(Auth::user()->role_id, [38, 125])){
                $dmcIds = Auth::user()->created_by;
                $user = User::where('userId', $dmcIds)->first();
                $dmcIdss = $user->created_by;
                $user = User::where('userId', $dmcIdss)->first();
                $dmcId = $user->created_by;
            }else{
                $dmcId = null;
            }
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
            if(Auth::user()->role_id == 11){
                $dmcId = Auth::user()->userId;
            }elseif(in_array(Auth::user()->role_id, [33, 34, 128, 129, 130, 131, 132, 134, 135, 136, 137, 138])){
                $user = User::where('userId', Auth::user()->userId)->first();
                $dmcId = $user->created_by;
            }elseif(in_array(Auth::user()->role_id, [37, 124])){
                $dmcIds = Auth::user()->created_by;
                $user = User::where('userId', $dmcIds)->first();
                $dmcId = $user->created_by;
            }elseif(in_array(Auth::user()->role_id, [38, 125])){
                $dmcIds = Auth::user()->created_by;
                $user = User::where('userId', $dmcIds)->first();
                $dmcIdss = $user->created_by;
                $user = User::where('userId', $dmcIdss)->first();
                $dmcId = $user->created_by;
            }else{
                $dmcId = null;
            }
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
                        'breakfast', 'breakfast_type', 'breakfast_price', 'lunch', 'lunch_type', 'lunch_price', 'dinner', 'dinner_type', 'dinner_price',
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
                              ->orWhere('dmc_id', $dmcId);
                    })
                    ->select('room_id', 'room_type', 'weekday_price', 'weekend_price', 'double_weekday_price', 'double_weekend_price', 
                            'breakfast', 'breakfast_type','breakfast_price','lunch', 'lunch_type', 'lunch_price', 'dinner', 'dinner_type', 'dinner_price',
                            'breakfast_included', 'dimension', 'features', 'master_image', 'created_by', 'dmc_id',)
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
            $city = $request->input('city');
            
            if (!$dmcId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine DMC ID'
                ], 403);
            }

            // Fetch guides where dmc_id matches current DMC ID (bigint type)
            $query = Guide::where('dmc_id', $dmcId)->where('dmc_id', $dmcId)
                ->where('status', 1);
                
            // Filter by city if provided
            if ($city) {
                $query->where('city', $city);
            }
            
            $guides = $query->select('guide_id', 'name', 'city', 'night_start_time', 'night_end_time', 
                        'night_surcharge', 'hourly_price', 
                        'two_hour_price', 'four_hour_price', 'six_hour_price', 
                        'eight_hour_price', 'ten_hour_price', 'twelve_hour_price')
                ->get();

            $guidesData = $guides->map(function ($guide) {
                return [
                    'guide_id' => $guide->guide_id,
                    'name' => $guide->name,
                    'city' => $guide->city,
                    'night_start_time' => $guide->night_start_time,
                    'night_end_time' => $guide->night_end_time,
                    'day_rate' => 0,
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
                'guides' => $guidesData,
                'filtered_by_city' => !empty($city),
                'city' => $city
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
     * Fetch guides by DMC and city
     */
    public function fetchGuidesByDmc(Request $request)
    {
        try {
            $city = $request->input('city');
            $dmcId = $request->input('dmc_id') ?? Auth::user()->created_by;
            
            if (!$dmcId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine DMC ID'
                ], 403);
            }
            
            if (!$city) {
                return response()->json([
                    'success' => false,
                    'message' => 'City parameter is required'
                ], 400);
            }

            // Fetch guides for the specific city and DMC with languages
            $guides = Guide::where('dmc_id', $dmcId)
                ->where('status', 1)
                ->where('city', $city)
                ->with('languages')
                ->select('guide_id', 'name', 'city', 'night_start_time', 'night_end_time', 
                        'night_surcharge', 'hourly_price', 
                        'two_hour_price', 'four_hour_price', 'six_hour_price', 
                        'eight_hour_price', 'ten_hour_price', 'twelve_hour_price')
                ->get();
            
            \Log::info("Fetching guides for city: {$city}", [
                'dmc_id' => $dmcId,
                'city' => $city,
                'guides_found' => $guides->count()
            ]);

            return response()->json([
                'success' => true,
                'guides' => $guides,
                'city' => $city,
                'count' => $guides->count()
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
            $city = $request->input('city');
            
            if (!$dmcId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine DMC ID'
                ], 403);
            }
            
            // Fetch restaurants where dmc_id JSON contains current DMC ID AND have meals in meals table
            $query = Restaurant::whereJsonContains('dmc_id', (int) $dmcId)
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('meals')
                          ->whereRaw('meals.restaurant_id = restaurants.restaurant_id');
                });
                
            // Filter by city if provided
            if ($city) {
                $query->where('city', $city);
            }
            
            $restaurants = $query->select('restaurant_id', 'name', 'city', 'breakfast_available', 'lunch_available', 'dinner_available',
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
                    'city' => $restaurant->city,
                    'meal_types' => $mealTypes
                ];
            });

            return response()->json([
                'success' => true,
                'restaurants' => $restaurantsData,
                'filtered_by_city' => !empty($city),
                'city' => $city
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
     * Fetch restaurants by DMC and city
     */
    public function fetchRestaurantsByDmc(Request $request)
    {
        try {
            $city = $request->input('city');
            $dmcId = $request->input('dmc_id') ?? Auth::user()->created_by;
            
            if (!$dmcId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine DMC ID'
                ], 403);
            }
            
            if (!$city) {
                return response()->json([
                    'success' => false,
                    'message' => 'City parameter is required'
                ], 400);
            }

            // Fetch restaurants for the specific city and DMC
            $restaurants = Restaurant::whereJsonContains('dmc_id', (int) $dmcId)
                ->where('city', $city)
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('meals')
                          ->whereRaw('meals.restaurant_id = restaurants.restaurant_id');
                })
                ->select('restaurant_id', 'name', 'city', 'breakfast_available', 'lunch_available', 'dinner_available',
                         'opening_time_bf', 'closing_time_bf', 'opening_time_lunch', 'closing_time_lunch',
                         'opening_time_dinner', 'closing_time_dinner')
                ->get();
            
            \Log::info("Fetching restaurants for city: {$city}", [
                'dmc_id' => $dmcId,
                'city' => $city,
                'restaurants_found' => $restaurants->count()
            ]);
            
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
                    'city' => $restaurant->city,
                    'meal_types' => $mealTypes
                ];
            });

            return response()->json([
                'success' => true,
                'restaurants' => $restaurantsData,
                'city' => $city,
                'count' => $restaurants->count()
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
            
            // Get DMC ID from authenticated user
            $user = User::where('userId', Auth::user()->userId)->first();
            $dmcId = $user->created_by;
            
            if (!$dmcId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine DMC ID'
                ], 403);
            }
            
            // Check if restaurant ID is valid (not empty, null, or 'undefined')
            if (!$restaurantId || $restaurantId === 'undefined' || $restaurantId === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Restaurant ID is required'
                ], 400);
            }
            
            // Validate that restaurant ID is numeric
            if (!is_numeric($restaurantId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid restaurant ID format'
                ], 400);
            }

            $query = Meal::where('restaurant_id', $restaurantId)->where('dmc_id', $dmcId);
            
            // Filter by meal period if provided
            if ($mealPeriod) {
                $query->where('meal_period', $mealPeriod);
            }

            $meals = $query->select('meal_id', 'name', 'type', 'price', 'adult_price', 'child_price', 'meal_period')
                ->get();

            // Debug logging
            \Log::info('Meals query result:', [
                'restaurant_id' => $restaurantId,
                'dmc_id' => $dmcId,
                'meal_period' => $mealPeriod,
                'meals_count' => $meals->count(),
                'meals_data' => $meals->toArray()
            ]);

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

            $response = [
                'success' => true,
                'meals' => $mealsData
            ];
            
            // Debug logging
            \Log::info('Meals API response:', $response);
            
            return response()->json($response);

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
            $dmcId = CommonHelper::getDmcId($user);
            $fromZoneId = $request->from_zone_id;
            $toZoneId = $request->to_zone_id;
            $fromZoneType = $request->from_zone_type;
            $toZoneType = $request->to_zone_type;
            $city = $request->city;
            $zone_status = $request->zone_status;

            
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
            $actualFromZoneId = null;
            $actualToZoneId = null;
            $vehicleMappings = collect();

            if($zone_status == 1){
                
                $actualFromZoneId = intval($this->getActualZoneId($fromZoneId, $fromZoneType, $dmcId));
                $actualToZoneId = intval($this->getActualZoneId($toZoneId, $toZoneType, $dmcId));

                $vehicleMappings = VehicleZoneMapping::whereIn('from_zone_id', [$actualFromZoneId, $actualToZoneId])
                ->whereIn('to_zone_id', [$actualToZoneId, $actualFromZoneId])
                ->get();
                $vehicles = $vehicleMappings->load(['vehicle', 'fromZone', 'toZone'])
                    ->map(function ($mapping) {
                        $vehicle = $mapping->vehicle;
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
                            'sharable' => $vehicle->sharable,
                            'from_zone' => $mapping->fromZone->zone_name ?? '',
                            'to_zone' => $mapping->toZone->zone_name ?? '',
                            'mapping_id' => $mapping->mapping_id,
                            // ✅ Zone mapping prices
                            'private_price' => $mapping->private_price,
                            'shared_price' => $mapping->shared_price,
                        ];
                    })
                ->filter()
                ->values();
            }
            else{
                $vehicles = Vehicle::select('vehicle_id', 'vehicle_name', 'vehicle_type', 'seating_capacity', 'vehicle_model', 'image', 'base_price', 'sharable_base_price', 'service_type', 'sharable')
                    ->where('dmc_id', $dmcId)
                    ->where('city', $city)
                    ->where('is_available', 1)
                    ->get();
                $vehicles = $vehicles->map(function ($vehicle) {
                    return [
                        'vehicle_id' => $vehicle->vehicle_id,
                        'vehicle_name' => $vehicle->vehicle_name,
                        'vehicle_type' => $vehicle->vehicle_type,
                        'seating_capacity' => $vehicle->seating_capacity,
                        'base_price' => $vehicle->base_price,
                        'sharable_base_price' => $vehicle->sharable_base_price,
                        'service_type' => $vehicle->service_type,
                        'private_price' => $vehicle->base_price,
                        'shared_price' => $vehicle->sharable_base_price,
                        'sharable' => $vehicle->sharable,
                    ];
                });
            }

            // Debug logging for zone ID resolution
            \Log::info('Zone ID Resolution Debug', [
                'original_from_zone_id' => $fromZoneId,
                'original_to_zone_id' => $toZoneId,
                'from_zone_type' => $fromZoneType,
                'to_zone_type' => $toZoneType,
                'actual_from_zone_id' => $actualFromZoneId,
                'actual_to_zone_id' => $actualToZoneId,
                'dmc_id' => $dmcId,
                'city' => $city,
                'from_zone_type' => $fromZoneType,
                'to_zone_type' => $toZoneType,
                'zone_status' => $zone_status
            ]);

            // Fetch vehicles that have zone mappings between the actual zone IDs
            // Try both directions to ensure bidirectional route coverage

           
            
                
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
    private function getActualZoneId($locationId, $locationType = null, $dmcId = null)
    {
        // If no type specified, assume it's already the correct ID
        if (!$locationType) {
            return $locationId;
        }

        switch ($locationType) {
            case 'port':
                case 'Port':
                // For ports, get the port_id
                $port = Port::where('id', $locationId)->first();
                return $port ? $port->port_id : $locationId;
                
            case 'attraction':
                case 'Attraction':
                // For attractions, get the zone_id for the specific DMC
                $attraction = Attraction::where('attraction_id', $locationId)->first();
                if ($attraction && $dmcId) {
                    return $attraction->getZoneForDmc($dmcId);
                }
                return $locationId;
                
            case 'hotel':
                case 'Hotel':
                // For hotels, use hotel_unique_id directly
                $hotel = Hotel::where('hotel_unique_id', $locationId)->first();
                return $hotel->getZoneForDmc($dmcId);
                
            case 'restaurant':
                case 'Restaurant':
                // For restaurants, use restaurant_id directly
                $restaurant = Restaurant::where('restaurant_id', $locationId)->first();
                return $restaurant->getZoneForDmc($dmcId);
                
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
            $showAllVehicles = $request->input('show_all', false); // New parameter for point-to-point and hourly services
            
            if (!$dmcId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to determine DMC ID'
                ], 403);
            }

            // For point-to-point and hourly services, city is not required
            if (!$showAllVehicles && !$city) {
                return response()->json([
                    'success' => false,
                    'message' => 'City is required'
                ], 400);
            }

            // Build query for vehicles
            $query = Vehicle::where('dmc_id', $dmcId)
                ->where('is_available', 1)
                ->select('vehicle_id', 'vehicle_name', 'vehicle_type', 'seating_capacity', 'vehicle_model', 'image', 'base_price', 'sharable_base_price', 'service_type', 'cost_per_hour', 'sharable_cost_per_hour', 'sharable');
            
            // Only filter by city if not showing all vehicles
            if (!$showAllVehicles && $city) {
                $query->where('city', $city);
            }
            
            $vehicles = $query->orderBy('vehicle_name')->get();

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
                    'shared_price' => $vehicle->sharable_base_price,
                    'sharable' => $vehicle->sharable
                ];
            });

            return response()->json([
                'success' => true,
                'vehicles' => $vehiclesData,
                'total_vehicles' => count($vehiclesData),
                'city' => $city,
                'dmc_id' => $dmcId,
                'show_all_vehicles' => $showAllVehicles
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
     * Fetch agents by agency ID
     */
    public function fetchAgentsByAgency(Request $request)
    {
        try {
            $agencyId = $request->input('agency_id');
            
            if (!$agencyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Agency ID is required'
                ], 400);
            }
            $agents = Agent::where('agency_id', $agencyId)
                ->select('agent_id', 'name', 'email', 'phone')
                ->orderBy('name')
                ->get();

            $agentsData = $agents->map(function ($agent) {
                return [
                    'agent_id' => $agent->agent_id,
                    'name' => $agent->name,
                    'email' => $agent->email,
                    'phone' => $agent->phone
                ];
            });

            return response()->json([
                'success' => true,
                'agents' => $agentsData,
                'total_agents' => count($agentsData),
                'agency_id' => $agencyId
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching agents: ' . $e->getMessage(),
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
    private function getNextBookingId()
    {
        // Use a more robust approach to get the next booking ID
        try {
            // Try to get by booking_id first (if column exists)
            $lastBooking = Order::lockForUpdate()->orderBy('booking_id', 'desc')->first();
            if ($lastBooking && isset($lastBooking->booking_id) && $lastBooking->booking_id > 0) {
                return CommonHelper::createId($lastBooking->booking_id);
            }
        } catch (\Exception $e) {
            // Column might not exist, fall back to using id
            \Log::info("booking_id column not found, using id column instead");
        }
        
        // Fallback: use the id column 
        $lastBooking = Order::lockForUpdate()->orderBy('id', 'desc')->first();
        $lastId = $lastBooking ? $lastBooking->id : 0;
        return CommonHelper::createId($lastId);
    }

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
                                    
            // This initial booking ID is not used since we generate unique IDs for each service
            // But we keep it for compatibility with existing logging

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
                                    
                                    // Generate new booking ID for each hotel
                                    $newHotelBookingId = $this->getNextBookingId();
                                    
                                    $order = Order::create([
                                        'booking_id' => $newHotelBookingId,
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
                                
                                // Generate new booking ID for each attraction
                                $newAttractionBookingId = $this->getNextBookingId();
                                
                                $order = Order::create([
                                    'booking_id' => $newAttractionBookingId,
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
                                // Generate new booking ID for each restaurant
                                $newRestaurantBookingId = $this->getNextBookingId();
                                
                                $order = Order::create([
                                    'booking_id' => $newRestaurantBookingId,
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
                                // Generate new booking ID for each guide
                                $newGuideBookingId = $this->getNextBookingId();
                                
                                $order = Order::create([
                                    'booking_id' => $newGuideBookingId,
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
                                
                                if ($orderType === 'travel_point') {
                                    \Log::info("✅ PROCESSING POINT-TO-POINT TRANSPORT", [
                                        'transport_id' => $transport['id'] ?? 'no_id',
                                        'vehicles_name' => $transport['vehicles_name'] ?? 'unknown',
                                        'pickup_location' => $transport['entrypickup'] ?? 'no_pickup',
                                        'dropoff_location' => $transport['entrydropoff'] ?? 'no_dropoff',
                                        'travel_type' => $orderType
                                    ]);
                                }
                                
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
                                
                                // Generate new booking ID for each transport
                                $newTransportBookingId = $this->getNextBookingId();
                                
                                $order = Order::create([
                                    'booking_id' => $newTransportBookingId,
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
                                
                                // Handle date formatting based on port type
                                if ($type === 'entry_port' && isset($transport['bookingDate'])) {
                                    // For entry_port, use bookingDate and extract first date
                                    $dateRange = $transport['bookingDate'];
                                    if (preg_match('/(\w{3} \d{1,2}), (\d{4})/', $dateRange, $matches)) {
                                        $firstDate = $matches[1] . ', ' . $matches[2];
                                        $transport['bookingDate'] = date('Y-m-d', strtotime($firstDate));
                                        $transport['pickupdate'] = $transport['bookingDate'];
                                    }
                                } elseif ($type === 'exit_port' && isset($transport['exitpickupdate'])) {
                                    // For exit_port, use exitpickupdate and extract second date
                                    $dateRange = $transport['exitpickupdate'];
                                    if (preg_match('/- (\w{3} \d{1,2}), (\d{4})/', $dateRange, $matches)) {
                                        $secondDate = $matches[1] . ', ' . $matches[2];
                                        $transport['bookingDate'] = date('Y-m-d', strtotime($secondDate));
                                        $transport['exitpickupdate'] = $transport['bookingDate'];
                                    }
                                }
                                
                                \Log::info("Processing individual {$type} transport", [
                                    'transport_id' => $transport['id'] ?? 'no_id',
                                    'vehicles_name' => $transport['vehicles_name'] ?? 'unknown',
                                    'travel_type' => $orderType,
                                    'service_type' => $transport['type'] ?? 'unknown',
                                    'pickup_coords' => $transport['PickupPlaceid'] ?? 'no_coords',
                                    'dropoff_coords' => $transport['DropoffPlaceid'] ?? 'no_coords',
                                    'booking_type' => $transport['bookingType'] ?? 'no_booking_type',
                                    'booking_date' => $transport['bookingDate'] ?? 'no_date',
                                    'full_transport_data' => $transport
                                ]);
                                
                                // Generate new booking ID for each port transport
                                $newPortBookingId = $this->getNextBookingId();
                                
                                $order = Order::create([
                                    'booking_id' => $newPortBookingId,
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
                                // Generate new booking ID for each other service
                                $newServiceBookingId = $this->getNextBookingId();
                                
                                $order = Order::create([
                                    'booking_id' => $newServiceBookingId,
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
                        try {
                            // Handle range case like "Oct 06 - Oct 10, 2025"
                            if (strpos($bookingDate, '-') !== false) {
                                $parts = explode('-', $bookingDate);
                                $start = trim($parts[0]);
                                $end   = trim($parts[1]);

                                // Append year from end part if missing
                                if (!preg_match('/\d{4}$/', $start) && preg_match('/\d{4}$/', $end)) {
                                    $year = substr($end, -4);
                                    $start .= " " . $year;
                                }

                                $formattedDate = \Carbon\Carbon::parse($start)->format('M d, Y');
                            } else {
                                // Normal single date
                                $formattedDate = \Carbon\Carbon::parse($bookingDate)->format('M d, Y');
                            }
                        } catch (\Exception $e) {
                            // Fallback → today's date if parsing fails
                            $formattedDate = now()->format('M d, Y');
                        }

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

    public function orderSelectGuide(Request $request)
    {
        $request->validate([
            'booking_data' => 'required|json',
            'customer_info' => 'required|array',
        ]);
        $bookingData = json_decode($request->input('booking_data'), true);
        $customerInfo = $request->input('customer_info');
        $guideId = $request->input('guide_id');
        $duration = $request->input('duration');
        $customHours = $request->input('custom_hours');
        $pickupTime = $request->input('pickup_time');
        $tourId = $request->input('tour_id');
        $agentId = $request->input('agent_id');
        $dmcId = $request->input('dmc_id');
        $commission = $request->input('commission');
        $markup_percentage = $request->input('markup_percentage');


        $max_book_id = Order::max('booking_id') ?? 0;
        $bookingId = CommonHelper::createId($max_book_id);
        while (Order::where('booking_id', $bookingId)->exists()) {
            $bookingId = CommonHelper::createId($bookingId);
        }
        
        $order =  Order::create([
            'booking_id' => $bookingId,
            'agent_id' => $agentId,
            'tour_id' => $tourId,
            'data' => $bookingData,
            'type' => 'guide',
            'bookingType' => 'booking',
            'discount' => $commission,
            'markup_percentage' => $markup_percentage,
            'status' => 1,
        ]);
        return back()->with('success', 'Guide selected successfully');
    }
    
    public function orderSelectRestaurant(Request $request)
    {
        $request->validate([
            'booking_data' => 'required|json',
            'agent_id' => 'required',
            'tour_id' => 'required',
            'restaurant_id' => 'required',
            'meal_type' => 'required',
            'dish_id' => 'required',
            'time_slot' => 'required',
            'adults' => 'required',
            'children' => 'required',
            'infants' => 'required',
            'male_count' => 'required',
            'female_count' => 'required',
            'country' => 'required',
            'start_date' => 'required',
            'end_date' => 'required'
        ]);

        $bookingData = json_decode($request->input('booking_data'), true);
        $agentId = $request->input('agent_id');
        $tourId = $request->input('tour_id');
       
        // Generate unique booking ID
        $max_book_id = Order::max('booking_id') ?? 0;
        $bookingId = CommonHelper::createId($max_book_id);
        while (Order::where('booking_id', $bookingId)->exists()) {
            $bookingId = CommonHelper::createId($bookingId);
        }

        $order = Order::create([
            'booking_id' => $bookingId,
            'agent_id' => $agentId,
            'tour_id' => $tourId,
            'data' => $bookingData,
            'type' => 'restaurant',
            'bookingType' => 'booking',
            'discount' => 0,
            'markup_percentage' => 0,
            'status' => 1,
        ]);

        return back()->with('success', 'Restaurant selected successfully');
    }

    public function orderSelectAttraction(Request $request)
    {
        $request->validate([
            'booking_data' => 'required|json',
            'agent_id' => 'required',
            'tour_id' => 'required',
            'attraction_id' => 'required',
            'time_slot' => 'required',
        ]);

        $bookingData = json_decode($request->input('booking_data'), true);
        $agentId = $request->input('agent_id');
        $tourId = $request->input('tour_id');
        
        // Generate a unique booking ID
        $max_book_id = \App\Models\Order::max('booking_id') ?? 0;
        $bookingId = \App\Helpers\CommonHelper::createId($max_book_id);
        while (\App\Models\Order::where('booking_id', $bookingId)->exists()) {
            $bookingId = \App\Helpers\CommonHelper::createId($bookingId);
        }
        
        // Create order
        $order = \App\Models\Order::create([
            'booking_id' => $bookingId,
            'agent_id' => $agentId,
            'tour_id' => $tourId,
            'data' => $bookingData,
            'type' => 'attraction',
            'bookingType' => 'booking',
            'discount' => 0,
            'markup_percentage' => 0,
            'status' => 1,
        ]);
        
        return back()->with('success', 'Attraction selected successfully');
    }
    
    public function orderSelectTransport(Request $request)
    {
        $request->validate([
            'transport_data' => 'required|json',
            'agent_id' => 'required',
            'tour_id' => 'required',
            'pickup_zone_id' => 'required',
            'dropoff_zone_id' => 'required',
            'pickup_time' => 'required',
            'vehicle_id' => 'required',
        ]);

        $transportData = json_decode($request->input('transport_data'), true);
        $agentId = $request->input('agent_id');
        $tourId = $request->input('tour_id');
        
        // Generate a unique booking ID
        $max_book_id = \App\Models\Order::max('booking_id') ?? 0;
        $bookingId = \App\Helpers\CommonHelper::createId($max_book_id);
        while (\App\Models\Order::where('booking_id', $bookingId)->exists()) {
            $bookingId = \App\Helpers\CommonHelper::createId($bookingId);
        }
        
        // Log the transport data for debugging
        \Log::info("Processing transport order", [
            'transport_data' => $transportData,
            'booking_id' => $bookingId,
            'agent_id' => $agentId,
            'tour_id' => $tourId
        ]);
        
        // Create order
        $order = \App\Models\Order::create([
            'booking_id' => $bookingId,
            'agent_id' => $agentId,
            'tour_id' => $tourId,
            'data' => $transportData,
            'type' => $request->input('type'),
            'bookingType' => 'booking',
            'discount' => 0,
            'markup_percentage' => 0,
            'status' => 1,
        ]);
        
        return back()->with('success', 'Transport service booked successfully');
    }

    public function orderSelectLocalTransfer(Request $request)
    {
       
        $request->validate([
            'booking_data' => 'required|json',
            'type' => 'required|string|in:travel_hourly,travel_point,local_transport,local_transport_dropoff'
        ]);

        $transportData = json_decode($request->input('booking_data'), true);
        $serviceType = $request->input('type');
        $tourId = $transportData[0]['tour_id'];
        $tour = Tour::where('tour_id', $tourId)->first();
        $agent_id = $tour->agent_id;

        $max_book_id = Order::max('booking_id') ?? 0;
        $bookingId = CommonHelper::createId($max_book_id);
        while (Order::where('booking_id', $bookingId)->exists()) {
            $bookingId = CommonHelper::createId($bookingId);
        }

        // Map service type to appropriate order type
        $orderType = match($serviceType) {
            'travel_hourly' => 'travel_hourly',
            'travel_point' => 'travel_point',
            'local_transport' => 'local_transport',
            default => 'local_transport'
        };

        $order = Order::create([
            'booking_id' => $bookingId,
            'agent_id' => $agent_id,
            'tour_id' => $tourId,
            'data' => $transportData,
            'type' => $orderType,
            'bookingType' => 'booking',
            'discount' => 0,
            'markup_percentage' => 0,
            'status' => 1,
        ]);

        // Get success message based on service type
        $successMessage = match($serviceType) {
            'travel_hourly' => 'Hourly travel service booked successfully',
            'travel_point' => 'Point to point travel service booked successfully',
            'local_transport' => 'Local transfer service booked successfully',
            default => 'Transfer service booked successfully'
        };

        return response()->json([
            'success' => true,
            'message' => $successMessage,
            'booking_id' => $bookingId,
            'service_type' => $serviceType,
            'order_type' => $orderType
        ]);
    }
} 