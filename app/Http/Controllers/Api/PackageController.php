<?php

namespace App\Http\Controllers\Api;
use App\Models\Package;
use App\Models\Agent;
use App\Models\User;
use App\Models\Hotel;
use App\Models\Attraction;
use App\Models\Restaurant;
use App\Models\Guide;
use App\Models\Tour;
use App\Models\Order;
use App\Models\PackageBooking;
use App\Models\GuideLanguage;
use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $city = $request->query('city');
        $country = $request->query('country');
        $today = Carbon::today();
        $date = $request->query('date');
        $pax = $request->query('adults');
        $dmcId = $request->query('dmc_id');

        if(!$dmcId){
            return response()->json(['message' => 'Dmc ID is required'], 400);
        }
        $dmc = User::select('userId', 'name', 'company_name', 'logo')->where('userId', $dmcId)->first();
        if(!$dmc){
            return response()->json(['message' => 'Dmc not found'], 404);
        }
        $dmc_data = [
            'dmc_id' => $dmc->userId,
            'dmc_name' => $dmc->name,
            'dmc_company_name' => $dmc->company_name,
            'dmc_logo' => $dmc->logo,
        ];

        // Format the date properly for comparison with database date fields
        if (empty($date)) {
            $date = $today->format('Y-m-d');
        } else {
            try {
                // Trim whitespace and remove quotes
                $date = trim($date, " \t\n\r\0\x0B\"'");
                
                // Handle the specific case of d-m-Y format like "25-06-2025"
                if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $date, $matches)) {
                    $day = (int)$matches[1];
                    $month = (int)$matches[2];
                    $year = (int)$matches[3];
                    
                    // Validate date components
                    if ($day >= 1 && $day <= 31 && $month >= 1 && $month <= 12) {
                        // Create a valid Y-m-d format
                        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    } else {
                        return response()->json(['message' => 'Invalid date components. Day must be 1-31 and month must be 1-12.'], 400);
                    }
                }
                // If already in Y-m-d format
                else if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    // Already in the right format, just validate it
                    $date = Carbon::createFromFormat('Y-m-d', $date)->format('Y-m-d');
                }
                // For any other format, try Carbon's parsing
                else {
                    $date = Carbon::parse($date)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Invalid date format. Please use d-m-Y (like 25-06-2025) or Y-m-d (like 2025-06-25) format.',
                    'error' => $e->getMessage()
                ], 400);
            }
        }

        $query = Package::where('status', 1)->where('max_pax', '>=', $pax)->where('dmc_id', $dmcId)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('expire_date', '>=', $date);
        if (!empty($city)) {
            $query->where('city', $city);
        }

        if (!empty($country)) {
            $query->where('destination', $country);
        }

        $packages = $query->select('package_id', 'title', 'destination', 'category', 'duration_days', 'description', 'price_adult', 'max_pax', 'main_image', 'city', 'start_date', 'expire_date', 'package_type', 'itinerary')->get();
        
        // Format the response
        return response()->json([
            'packages' => $packages,
            'dmc_data' => $dmc_data,
        ]);
    }

    private function getDmcIdForCurrentUser()
    {
        $user = Auth::user();

        if ($user->agent_id) {
            $agent = Agent::where('agent_id', $user->agent_id)->first();

            if (!$agent) {
                return null;
            }

            switch ($agent->role_id) {
                case 11: // DMC
                    return $agent->sales_manager_dmc;

                case 33: 
                case 128: 
                case 129: 
                case 130: 
                case 134: 
                case 135: 
                case 136: 
                case 138: // Sales Head
                    return optional(User::where('userId', $agent->sales_manager_dmc)->first())->created_by;

                case 12:  
                case 37: // Sales Manager
                    $sm = User::where('userId', $agent->sales_manager_dmc)->first();
                    return optional($sm && $sm->created_by ? User::where('userId', $sm->created_by)->first() : null)->created_by;

                case 38: // Assistant Manager
                    $am = User::where('userId', $agent->sales_manager_dmc)->first();
                    $sm = $am && $am->created_by ? User::where('userId', $am->created_by)->first() : null;
                    $sh = $sm && $sm->created_by ? User::where('userId', $sm->created_by)->first() : null;
                    return optional($sh)->created_by;
            }
        }

        // If the user is not an agent (e.g., directly SH, SM, AM)
        switch ($user->role_id) {
            case 33: 
            case 128: 
            case 129: 
            case 130: 
            case 134: 
            case 135: 
            case 136: 
            case 138: // SH
                return $user->created_by;

            case 37: // SM
                return optional(User::where('userId', $user->created_by)->first())->created_by;

            case 38: // AM
                $sm = User::where('userId', $user->created_by)->first();
                $sh = $sm && $sm->created_by ? User::where('userId', $sm->created_by)->first() : null;
                return optional($sh)->created_by;
        }

        return null;
    }

    public function package_details(Request $request)
    {
        $package_id = $request->input('package_id');
        
        $package = Package::where('package_id', $package_id)
            ->select(
                'package_id', 'title', 'destination', 'category', 'duration_days', 
                'description', 'price_adult', 'price_senior', 'price_child', 
                'max_pax', 'selected_hotels', 'selected_attractions', 'max_hotels', 
                'max_attractions', 'main_image', 'gallery_images', 'inclusions', 
                'exclusions', 'terms_conditions', 'views_count', 
                'rating', 'reviews_count', 'city', 'expire_date', 'start_date', 
                'selected_guide', 'selected_restaurants', 'max_restaurants','package_type','attraction_with_transfer','entry_port', 'exit_port', 'status', 'itinerary'
            )
            ->first();
        if (!$package) {
            return response()->json(['message' => 'Package not found'], 404);
        }
        
        // Enhance selected_hotels with image data
        if (!empty($package->selected_hotels)) {
            $hotelIds = collect($package->selected_hotels)->pluck('id')->toArray();
            $hotelNames = collect($package->selected_hotels)->pluck('name')->toArray();
            
            $hotelsData = Hotel::where(function($query) use ($hotelIds) {
                $query->whereIn('hotel_unique_id', $hotelIds);
            })->select('hotel_unique_id', 'name', 'main_image', 'images')->get();
            
            $package->selected_hotels = collect($package->selected_hotels)->map(function($hotel) use ($hotelsData) {
                // Use first with a callback for matching complex conditions
                $hotelData = $hotelsData->first(function($item) use ($hotel) {
                    return $item->hotel_unique_id == $hotel['id'] || 
                           $item->name == $hotel['name'];
                });
                
                if ($hotelData) {
                    $hotel['image'] = $hotelData->main_image 
                        ? (str_starts_with($hotelData->main_image, 'http') 
                            ? $hotelData->main_image 
                            : asset('storage/' . $hotelData->main_image))
                        : null;
                    $hotel['images'] = $hotelData->images 
                        ? collect(json_decode($hotelData->images, true))->map(function($img) {
                            return str_starts_with($img, 'http') ? $img : asset('storage/' . $img);
                        })->toArray()
                        : [];
                }
                
                return $hotel;
            })->toArray();
        }
        
        // Enhance selected_attractions with image data
        if (!empty($package->selected_attractions)) {
            $attractionIds = collect($package->selected_attractions)->pluck('id')->toArray();
            $attractionNames = collect($package->selected_attractions)->pluck('name')->toArray();
            
            $attractionsData = Attraction::where(function($query) use ($attractionIds) {
                $query->whereIn('attraction_id', $attractionIds);
            })->select('attraction_id', 'name', 'master_image', 'additional_image')->get();
            
            $package->selected_attractions = collect($package->selected_attractions)->map(function($attraction) use ($attractionsData) {
                // Use first with a callback for matching complex conditions
                $attractionData = $attractionsData->first(function($item) use ($attraction) {
                    return $item->attraction_id == $attraction['id'] || 
                           $item->name == $attraction['name'];
                });
                
                if ($attractionData) {
                    $attraction['image'] = $attractionData->master_image 
                        ? (str_starts_with($attractionData->master_image, 'http') 
                            ? $attractionData->master_image 
                            : asset('storage/' . $attractionData->master_image))
                        : null;
                    $attraction['images'] = $attractionData->additional_image 
                        ? collect(json_decode($attractionData->additional_image, true))->map(function($img) {
                            return str_starts_with($img, 'http') ? $img : asset('storage/' . $img);
                        })->toArray()
                        : [];
                }
                
                return $attraction;
            })->toArray();
        }
        
        // Enhance selected_guide with image data
        if (!empty($package->selected_guide)) {
            $guideIds = [];
            $guideNames = [];
            
            foreach ($package->selected_guide as $guide) {
                if (isset($guide['id'])) $guideIds[] = $guide['id'];
                if (isset($guide['name'])) $guideNames[] = $guide['name'];
            }
            
            $guidesData = Guide::where(function($query) use ($guideIds) {
                $query->whereIn('guide_id', $guideIds);
            })->select('guide_id', 'name', 'image')->get();
            
            $package->selected_guide = collect($package->selected_guide)->map(function($guide) use ($guidesData) {
                // Use first with a callback for matching complex conditions
                $guideData = $guidesData->first(function($item) use ($guide) {
                    return $item->guide_id == $guide['id'] || 
                           $item->name == $guide['name'];
                });
                
                if ($guideData) {
                    $guide['image'] = $guideData->image 
                        ? (str_starts_with($guideData->image, 'http') 
                            ? $guideData->image 
                            : asset('storage/' . $guideData->image))
                        : null;
                }
                
                return $guide;
            })->toArray();
        }
        
        // Enhance selected_restaurants with image data
        if (!empty($package->selected_restaurants)) {
            $restaurantIds = collect($package->selected_restaurants)->pluck('id')->toArray();
            $restaurantNames = collect($package->selected_restaurants)->pluck('name')->toArray();
            
            $restaurantsData = Restaurant::where(function($query) use ($restaurantIds) {
                $query->whereIn('restaurant_id', $restaurantIds);
            })->select('restaurant_id', 'name', 'master_image', 'images')->get();
            
            $package->selected_restaurants = collect($package->selected_restaurants)->map(function($restaurant) use ($restaurantsData) {
                // Use first with a callback for matching complex conditions
                $restaurantData = $restaurantsData->first(function($item) use ($restaurant) {
                    return $item->restaurant_id == $restaurant['id'] || 
                           $item->name == $restaurant['name'];
                });
                
                if ($restaurantData) {
                    $restaurant['image'] = $restaurantData->master_image 
                        ? (str_starts_with($restaurantData->master_image, 'http') 
                            ? $restaurantData->master_image 
                            : asset('storage/' . $restaurantData->master_image))
                        : null;
                    $restaurant['images'] = $restaurantData->images 
                        ? collect(json_decode($restaurantData->images, true))->map(function($img) {
                            return str_starts_with($img, 'http') ? $img : asset('storage/' . $img);
                        })->toArray()
                        : [];
                }
                
                return $restaurant;
            })->toArray();
        }
        
        return response()->json($package);
    }
        
    public function storeMultipleOrders(Request $request)
    {
        $payload = $request->all(); // this is the outer array

        foreach ($payload as $entry) {
            // Validate each entry
            validator($entry, [
                'type' => 'required|string',
                'tour_id' => 'required|integer',
                'agent_id' => 'required|integer',
                'data' => 'required|array',
            ])->validate();

            $type = $entry['type'];
            $tourId = $entry['tour_id'];
            $agentId = $entry['agent_id'];

            foreach ($entry['data'] as $item) {
                $max_book_id = Order::max('booking_id') ?? 0;
                $bookId = CommonHelper::createId($max_book_id);

                Order::create([
                    'agent_id' => $agentId,
                    'tour_id' => $tourId,
                    'data' => [$item],
                    'type' => $type,
                    'bookingType' => 'enquiry',
                    'booking_id' => $bookId,
                    'status' => 1,
                ]);
            }
        }

        return response()->json(['message' => 'All orders saved successfully.']);
    }

    public function booking(Request $request){
        // Extract booking data from request
        $user = Auth::user();
        if(!$user){
            return response()->json(['message' => 'User not found'], 404);
        }
        
        // Get type from request
        $type = $request->input('user_role', 'user'); // Default to 'user' if not provided
        
        if($type == 'agent' || $type == 'Agent'){
            $user_id = $user->agent_id; 
            $sales_manager_dmc_id = $user->sales_manager_dmc;
            $role_id = $user->role_id;

            if($role_id == 11){
                $dmc_id = $sales_manager_dmc_id;
            }
            elseif($role_id == 33 || $role_id == 128 || $role_id == 129 || $role_id == 130 || $role_id == 134 || $role_id == 135 || $role_id == 136 || $role_id == 138){
                $sales_head_id = $user->sales_manager_dmc;
                $sales_head = User::where('userId', $sales_head_id)->first();
                $dmc_id = $sales_head->created_by;
            }
            elseif($role_id == 37){
                $sales_manager_id = $user->sales_manager_dmc;
                $sales_manager = User::where('userId', $sales_manager_id)->first();
                $sales_head_id = $sales_manager->created_by;
                $sales_head = User::where('userId', $sales_head_id)->first();
                $dmc_id = $sales_head->created_by;
            }
            elseif($role_id == 38){
                $assistant_sales_manager_id = $user->sales_manager_dmc;
                $assistant_sales_manager = User::where('userId', $assistant_sales_manager_id)->first();
                $sales_manager_id = $assistant_sales_manager->created_by;
                $sales_manager = User::where('userId', $sales_manager_id)->first();
                $sales_head_id = $sales_manager->created_by;
                $sales_head = User::where('userId', $sales_head_id)->first();
                $dmc_id = $sales_head->created_by;
            }
            else{
                $dmc_id = null;
            }
        }else{
            $user_id = $user->userId;
            //DMC (role_id 11)
            if($user->role_id == 11){
                $dmc_id = $user_id;
            }
            //Sales Head (role_id 33)
            elseif($user->role_id == 33 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
                $dmc_id = $user->created_by;
            }
            //Sales Manager (role_id 37)
            elseif($user->role_id == 37){
                $sales_manager_id = $user->userId;
                $sales_head_id = $user->created_by;
                $sales_head = User::where('userId', $sales_head_id)->first();
                $dmc_id = $sales_head->created_by;
            }
            //Assistant Sales Manager (role_id 38)
            elseif($user->role_id == 38){
                $assistant_sales_manager_id = $user->userId;
                $sales_manager_id = $user->created_by;
                $sales_manager = User::where('userId', $sales_manager_id)->first();
                $sales_head_id = $sales_manager->created_by;
                $sales_head = User::where('userId', $sales_head_id)->first();
                $dmc_id = $sales_head->created_by;
            }
            else {
                $dmc_id = null; // Default for other roles
            }
        }
        
        $data = $request->json()->all();
        $package_id = $data['package']['package_id'];
        $totalPrice = $data['booking_details']['total_price'];
        
        
        // Extract passenger counts
        $adult_count = $data['booking_details']['adult_count'];
        $child_count = $data['booking_details']['child_count'] ?? 0;
        $senior_count = 0; // Not specified in the provided data
        
        $totalPax = $adult_count + $child_count + $senior_count;
        
        // Validate package exists
        $package = Package::select('package_id', 'title', 'destination', 'category', 'duration_days', 'description', 'price_adult', 'price_senior', 'price_child', 'max_pax', 'main_image', 'city')->where('package_id', $package_id)->first();
        if (!$package) {
            return response()->json(['message' => 'Package not found'], 404);
        }
        $start_date = $request->input('date');
        $end_date = Carbon::parse($start_date)->addDays($package->duration_days - 1);
        
        // Format dates to Y-m-d
        $check_in = Carbon::parse($start_date)->format('Y-m-d');
        $check_out = Carbon::parse($end_date)->format('Y-m-d');


        // Verify price calculation
        $package_price = $package->price_adult * $adult_count + $package->price_senior * $senior_count + $package->price_child * $child_count;

        if($package_price != $totalPrice){
            return response()->json(['message' => 'Total price is not correct', 'package_price' => $package_price, 'totalPrice' => $totalPrice, 'adult_count' => $adult_count, 'child_count' => $child_count, 'senior_count' => $senior_count], 400);
        }

        $lastBooking = PackageBooking::withTrashed()->orderBy('created_at', 'desc')->first();
        $booking_max_id = $lastBooking->booking_id ?? 0;
        $bookingId = CommonHelper::createId($booking_max_id);
        while (PackageBooking::where('booking_id', $bookingId)->exists()) {
            $bookingId = CommonHelper::createId($bookingId);
        }

                
        // Extract only IDs from selected services
        $hotelIds = collect($data['selected']['hotels'])->pluck('id')->toArray();
        $attractionIds = collect($data['selected']['attractions'])->pluck('id')->toArray();
        $guideIds = collect($data['selected']['guides'])->pluck('id')->toArray();
        $booking = new PackageBooking();
        $booking->booking_id = $bookingId;
        $booking->package_id = $package_id;
        $booking->type = $type; // Save the type (agent/user)
        $booking->dmc_id = $dmc_id; // Save the DMC ID
        $booking->booking_details = $data['booking_details'];
        $booking->package = $data['package'];
        $booking->user_info = $data['user_info'];
        $booking->travel_dates = ["check_in" => $check_in, "check_out" => $check_out];

        $booking->selected_hotels = $hotelIds;
        $booking->selected_attractions = $attractionIds;
        $booking->selected_guides = $guideIds;

        $booking->status = '1';
        $booking->booked_by = $user->userId ?? $user->agent_id;
        $booking->agent_id = $request->input('agent_id');
        // Add other required fields and save the booking
        $booking->save();
        
        return response()->json([
            'message' => 'Booking created successfully', 
            'booking_id' => $booking->booking_id,
            'type' => $type,
            'dmc_id' => $dmc_id
        ], 201);
    }

    public function editCustomPackage(Request $request){
        $tour_id = $request->tour_id;
        if(!$tour_id){
            return response()->json(['message' => 'Please add tour_id'], 400);
        }
        $tour = Tour::with('booking')->where('tour_id', $tour_id)->first();
        if(!$tour){
            return response()->json(['message' => 'Tour not found'], 404);
        }
        $order = Order::where('tour_id', $tour_id)->first();
        $customer_info = [];
        
        if($order){
            $customer_info['fullName'] = $order->data[0]['fullName'];
            $customer_info['email'] = $order->data[0]['email'];
            $customer_info['phone'] = $order->data[0]['phone'];
            $customer_info['countryCode'] = $order->data[0]['countryCode'];
            $customer_info['address1'] = $order->data[0]['address1'];
            $customer_info['address2'] = $order->data[0]['address2'];
            $customer_info['state'] = $order->data[0]['state'];
            $customer_info['zip'] = $order->data[0]['zip'];
        }
        
        $agent_id = $tour->agent_id;
        $agent = Agent::where('agent_id', $agent_id)->first();
        $agent_name = $agent->name;
        $tour->agent_name = $agent_name;
        return response()->json([
            'tour' => $tour,
            'customer_info' => $customer_info
        ]);
    }

    public function getBookingLists(Request $request){
        $user = Auth::user();
        $booking = [];
        $agent_id = request()->header('agent-id');
        // Convert string "null" to actual null value
        if ($agent_id === 'null') {
            $agent_id = null;
        }
        
        try {
            
            if(!$agent_id || $agent_id === 'null'){
                if($user->userId){
                    $dmc_id = null;
                    $agent_creator_id = $user->userId;
                    $agent_ids = [];
                    // Check user role and determine DMC ID based on role hierarchy
                    if($user->role_id == 11){
                        $dmc_id = $user->userId;
                    }
                    elseif ($user->role_id == 33 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138) { // Sales Head
                        $sales_head = User::where('userId', $user->userId)->first();
                        $dmc_id = $sales_head->created_by;
                    } elseif ($user->role_id == 37) { // Sales Manager
                        $product_head = User::where('userId', $user->userId)->first();
                        $sales_head_id = $product_head->created_by;
                        $sales_head = User::where('userId', $sales_head_id)->first();
                        $dmc_id = $sales_head->created_by;
                    } elseif ($user->role_id == 38) { // Assistant Sales Manager
                        $assistant_sales_manager = User::where('userId', $user->userId)->first();
                        $sales_manager_id = $assistant_sales_manager->created_by;
                        $sales_manager = User::where('userId', $sales_manager_id)->first();
                        $sales_head_id = $sales_manager->created_by;
                        $sales_head = User::where('userId', $sales_head_id)->first();
                        $dmc_id = $sales_head->created_by;
                    }
                    
                    // If DMC ID is found, filter bookings by DMC
                    if ($dmc_id) {
                        
                        $booking = PackageBooking::select('booking_id', 'package_id', 'booking_details', 'travel_dates', 'selected_hotels', 'selected_attractions', 'selected_guides', 'selected_restaurants', 'status', 'booked_by', 'package', 'user_info')
                            ->where('dmc_id', $dmc_id)
                            ->get();
                    } else {
                        $agents = Agent::where('sales_manager_dmc', $agent_creator_id)->get();
                        $agent_ids = $agents->pluck('agent_id')->toArray();

                        // Fallback to user's own bookings if no DMC ID found
                        $booking = PackageBooking::select('booking_id', 'package_id', 'booking_details', 'travel_dates', 'selected_hotels', 'selected_attractions', 'selected_guides', 'selected_restaurants', 'status', 'booked_by', 'package', 'user_info')
                            ->whereIn('booked_by', $agent_ids)
                            ->get();
                    }
                }
                else{
                    $booking = PackageBooking::select('booking_id', 'package_id', 'booking_details', 'travel_dates', 'selected_hotels', 'selected_attractions', 'selected_guides', 'selected_restaurants', 'status', 'booked_by', 'package', 'user_info', 'dmc_id')
                        ->where('booked_by', $user->agent_id)
                        ->get();
                }
            }
            else{
                $booking = PackageBooking::select('booking_id', 'package_id', 'booking_details', 'travel_dates', 'selected_hotels', 'selected_attractions', 'selected_guides', 'selected_restaurants', 'status', 'booked_by', 'package', 'user_info', 'dmc_id');
                // Only add the where clause if agent_id is not null
                if ($agent_id !== null) {
                    $booking = $booking->where('agent_id', $agent_id)->get();
                }
                else{
                    $booking = [];
                }
            }
            if(count($booking) == 0){
                return response()->json([
                    'booking_lists' => [],
                    'total_bookings' => 0
                ]);
            }
            
            $data = [];
            $dmc_data = [];
            foreach ($booking as $b) {
                // Ensure the values are arrays, even if they come as strings
                if($user->agent_id && $b->dmc_id){
                    $dmc_id = $b->dmc_id;
                    $dmc = User::select('userId', 'name', 'company_name', 'logo')->where('userId', $dmc_id)->first();
                    $dmc_data = [
                        'dmc_id' => $dmc->userId,
                        'dmc_name' => $dmc->name,
                        'dmc_company_name' => $dmc->company_name,
                        'dmc_logo' => $dmc->logo,
                    ];
                }
                $hotelIds = is_array($b->selected_hotels) ? $b->selected_hotels : (is_string($b->selected_hotels) ? json_decode($b->selected_hotels, true) : []);
                $attractionIds = is_array($b->selected_attractions) ? $b->selected_attractions : (is_string($b->selected_attractions) ? json_decode($b->selected_attractions, true) : []);
                $guideIds = is_array($b->selected_guides) ? $b->selected_guides : (is_string($b->selected_guides) ? json_decode($b->selected_guides, true) : []);

                // Fallback to empty arrays if null or invalid
                $hotelIds = $hotelIds ?? [];
                $attractionIds = $attractionIds ?? [];
                $guideIds = $guideIds ?? [];

                // Only fetch related data if IDs exist
                $hotels = !empty($hotelIds) && is_array($hotelIds) ? Hotel::select(
                    'hotel_unique_id', 'name', 'main_image', 'images', 'address',
                    'phone', 'email', 'latitude', 'longitude'
                )->whereIn('hotel_unique_id', $hotelIds)->get() : [];
                
                $attractions = !empty($attractionIds) && is_array($attractionIds) ? Attraction::select(
                    'attraction_id', 'name', 'master_image', 'additional_image',
                    'location', 'latitude', 'longitude'
                )->whereIn('attraction_id', $attractionIds)->get() : [];
                
                $guides = [];
                if (!empty($guideIds) && is_array($guideIds)) {
                    $selected_guides = Guide::select(
                        'guide_id', 'name', 'image', 'contact_no', 'email'
                    )->whereIn('guide_id', $guideIds)->get();
                    
                    $guides = $selected_guides->map(function ($guide) {
                        $languages = GuideLanguage::where('guide_id', $guide->guide_id)->pluck('language');
                        return [
                            'guide_id' => $guide->guide_id,
                            'name' => $guide->name,
                            'image' => $guide->image,
                            'contact_no' => $guide->contact_no,
                            'email' => $guide->email,
                            'languages' => $languages,
                        ];
                    });
                }
                
                

                // Ensure JSON fields are properly decoded
                $bookingDetails = is_array($b->booking_details) ? $b->booking_details : (is_string($b->booking_details) ? json_decode($b->booking_details, true) : []);
                $travelDates = is_array($b->travel_dates) ? $b->travel_dates : (is_string($b->travel_dates) ? json_decode($b->travel_dates, true) : []);
                $package = is_array($b->package) ? $b->package : (is_string($b->package) ? json_decode($b->package, true) : []);
                $userInfo = is_array($b->user_info) ? $b->user_info : (is_string($b->user_info) ? json_decode($b->user_info, true) : []);

                $data[] = [
                    'dmc_data' => $dmc_data,
                    'booking_id' => $b->booking_id,
                    'package_id' => $b->package_id,
                    'booking_details' => $bookingDetails,
                    'travel_dates' => $travelDates,
                    'hotels' => $hotels,
                    'attractions' => $attractions,
                    'guides' => $guides,
                    'package' => $package,
                    'user_info' => $userInfo,
                    'status' => $b->status,
                    
                ];
            }
            
            return response()->json([
                'booking_lists' => $data,
                'total_bookings' => count($data)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'package_bookings table does not exist. Please create the table first.',
                'error' => $e->getMessage(),
                'booking_lists' => [],
                'total_bookings' => 0
            ], 500);
        }
    }
    
    /**
     * Convert date from various formats to YYYY-MM-DD for PostgreSQL
     *
     * @param string $date Date string in various formats
     * @return string Date in YYYY-MM-DD format
     */
    private function formatDateForDatabase($date)
    {
        if (empty($date)) {
            return null;
        }
        
        // If it's already in YYYY-MM-DD format, return it
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }
        
        // Try to parse DD/MM/YYYY format
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date, $matches)) {
            return sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
        }
        
        // Try to parse using DateTime
        try {
            $dateObj = new \DateTime($date);
            return $dateObj->format('Y-m-d');
        } catch (\Exception $e) {
            // If all else fails, return the original string
            return $date;
        }
    }
    
    public function updateCustomPackage(Request $request){
        $payload = $request->all(); // this is the outer array
        $tourId = null;
        $processedBookingIds = [];
        
        // First, extract the tour_id from the first object
        if (!empty($payload) && isset($payload[0]) && isset($payload[0]['tour_id'])) {
            $tourId = $payload[0]['tour_id'];
        }
        
        if (!$tourId) {
            return response()->json([
                'status' => false,
                'message' => 'Tour ID is required in the first object'
            ], 400);
        }

        $tour = Tour::where('tour_id', $tourId)->first();
        if(!$tour){
            return response()->json([
                'status' => false,
                'message' => 'Tour not found'
            ], 404);
        }
        
        // Convert date format from DD/MM/YYYY to YYYY-MM-DD for PostgreSQL
        $checkInDate = $this->formatDateForDatabase($payload[0]['check_in_time']);
        $checkOutDate = $this->formatDateForDatabase($payload[0]['check_out_time']);
        
        $tour->check_in_time = $checkInDate;
        $tour->check_out_time = $checkOutDate;
        $tour->save();
        
        // Get all existing orders for this tour_id
        $existingOrders = Order::where('tour_id', $tourId)->get();
        $existingBookingIds = $existingOrders->pluck('booking_id')->toArray();
        
        // Process all booking objects (second object onwards)
        foreach ($payload as $index => $entry) {
            // Skip the first object (tour details)
            if ($index === 0) {
                continue;
            }
            
            // Validate booking data objects
             if (!isset($entry['agent_id']) || !isset($entry['type']) || !isset($entry['data'])) {
                 continue;
             }
             
             // Set booking_id to 1 if not present
             $max_book_id = Order::max('booking_id') ?? 0;
             $bookId = CommonHelper::createId($max_book_id);
             while (Order::where('booking_id', $bookId)->exists()) {
                $bookId = CommonHelper::createId($bookId);
            }

             $bookingId = isset($entry['booking_id']) ? $entry['booking_id'] : $bookId;
             $agentId = $entry['agent_id'];
             $type = $entry['type'];
             $data = $entry['data'];
            
            // Track processed booking IDs
            $processedBookingIds[] = $bookingId;
            
            // Check if this booking_id exists in the orders table
            $existingOrder = $existingOrders->where('booking_id', $bookingId)
                                           ->where('type', $type)
                                           ->first();
            
            if ($existingOrder) {
                // Update existing order
                $existingOrder->update([
                    'data' => $data,
                    
                ]);
            } else {
                // Create new order
                Order::create([
                    'booking_id' => $bookingId,
                    'agent_id' => $agentId,
                    'tour_id' => $tourId,
                    'data' => $data,
                    'type' => $type,
                    'bookingType' => $data[0]['bookingType'] ?? 'booking',
                    'status' => 1,
                ]);
            }
        }
        
        // Delete orders that are no longer in the incoming data
        $bookingIdsToDelete = array_diff($existingBookingIds, $processedBookingIds);
        if (!empty($bookingIdsToDelete)) {
            Order::where('tour_id', $tourId)
                ->whereIn('booking_id', $bookingIdsToDelete)
                ->delete();
        }
        
        return response()->json([
            'status' => true,
            'message' => 'Custom package updated successfully',
            'tour_id' => $tourId,
            'processed_booking_ids' => $processedBookingIds,
            'deleted_booking_ids' => $bookingIdsToDelete
        ]);
    }

    public function cancelPackageBooking(Request $request)
    {
        $package_id = $request->input('booking_id');
        if (empty($package_id)) {
            return response()->json([
                'status' => false,
                'message' => 'Package ID is required.',
            ], 400);
        }
        $updated = PackageBooking::where('booking_id', $package_id)
            ->update(['status' => 4]);
        if ($updated) {
            return response()->json([
                'status' => true,
                'message' => 'Booking successfully cancelled.',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Booking not found or already cancelled.',
            ], 404);
        }
    }

}
