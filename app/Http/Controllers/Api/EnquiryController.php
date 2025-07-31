<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EnquiryForm;
use Carbon\Carbon;
use App\Helpers\CommonHelper;
use App\Models\User;
use App\Models\Hotel;
use App\Models\Attraction;
use App\Models\PackagedAttraction;
use App\Models\Restaurant;
use App\Models\Port;
use App\Models\Country;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Guide;
use App\Models\City;
use App\Models\Agent;
use App\Models\Tour;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

class EnquiryController extends Controller
{
    public function createEnquiry(Request $request)
    {
        $country = $request->input('country');
        $city = $request->input('city');
        $dmc_ids = $request->input('dmc_ids');
        
        // Convert to array if string
        if (is_string($dmc_ids)) {
            $dmc_ids = json_decode($dmc_ids, true) ?? [];
        } elseif (!is_array($dmc_ids)) {
            $dmc_ids = [];
        }

        if (empty($dmc_ids)) {
            return response()->json([
                'message' => 'DMC IDs are required',
                'success' => false
            ], 400);
        }

        $user = auth()->user();
        $agent_id = $user->agent_id;

        try {
            // Parse the dates
            $checkInTime = Carbon::createFromFormat('d/m/Y', $request->check_in);
            $checkOutTime = Carbon::createFromFormat('d/m/Y', $request->check_out);

            // Generate multi enquiry ID
            $randomDigits = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $multi_enq_id = 'MULTI-ENQ-' . $randomDigits;

            $created_enquiries = [];

            // Create an enquiry for each DMC ID
            foreach ($dmc_ids as $dmc_id) {
                // Generate unique enquiry ID for each DMC
                $max_tour_id = EnquiryForm::max('enquiry_id') ?? 0;
                $enquiryId = CommonHelper::createId($max_tour_id);
                $display_id = 'DMC-ENQ' . $enquiryId;

                $enquiry = new EnquiryForm();
                $enquiry->adult = $request->adult ?? 0;
                $enquiry->child = $request->child ?? 0;
                $enquiry->infant = $request->infant ?? 0;
                $enquiry->agent_id = $agent_id;
                $enquiry->enquiry_id = $enquiryId;
                $enquiry->male_count = $request->male ?? 0;
                $enquiry->female_count = $request->female ?? 0;
                $enquiry->check_in_time = $checkInTime;
                $enquiry->check_out_time = $checkOutTime;
                $enquiry->display_id = $display_id;
                $enquiry->country = $country;
                $enquiry->city = $city;
                $enquiry->child_ages = $request->children_ages ?? null;
                $enquiry->dmc_id = $dmc_id;  // Set individual DMC ID
                $enquiry->multi_enq_id = $multi_enq_id;  // Set common multi enquiry ID
                $enquiry->save();
                $enquiry->refresh();

                $created_enquiries[] = [
                    'enquiry_id' => $enquiry->enquiry_id,
                    'dmc_id' => $dmc_id,
                    'display_id' => $display_id,
                    'country' => $enquiry->country,
                    'city' => $enquiry->city,
                    'child' => $enquiry->child,
                    'infant' => $enquiry->infant,
                    'male' => $enquiry->male_count,
                    'female' => $enquiry->female_count,
                    'CheckInTime' => CommonHelper::DateFormat($checkInTime),
                    'CheckOutTime' => CommonHelper::DateFormat($checkOutTime),
                    'adult' => $enquiry->adult,
                    'total_pax' => $enquiry->adult + $enquiry->child,
                ];
            }

            return response()->json([
                'message' => 'Multiple enquiries created successfully',
                'multi_enq_id' => $multi_enq_id,
                'data' => $created_enquiries
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the enquiries',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function enquiry_lists(Request $request)
    {
        $country = $request->country;
        $city = $request->city;
        
        // Handle DMC IDs from request
        $request_dmc_ids = $request->input('dmc_ids');
        if (is_string($request_dmc_ids)) {
            $request_dmc_ids = json_decode($request_dmc_ids, true) ?? [];
        } elseif (!is_array($request_dmc_ids)) {
            $request_dmc_ids = [];
        }

        if (!$country || !$city) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter both country and city.',
            ]);
        }

        if (empty($request_dmc_ids)) {
            return response()->json([
                'success' => false,
                'message' => 'DMC IDs are required.',
            ]);
        }
        $country_id = Country::where('name', $country)->first();
        // Fetch all services for the given city and DMCs
        // For tables with JSON dmc_id field, we'll use a more complex query
        $hotels = Hotel::with(['rooms' => function($query) {
            $query->select('hotel_id', 'double_weekday_price', 'room_type', 'room_id')
                  ->selectRaw('(double_weekday_price / 2) as single_base_price');
        }])->where('city', $city)
          ->where('country', $country_id->country_id)
          ->where('status', 1)
          ->where('is_active', 1)
          ->where('is_complete', 1)
          ->get();

        // Create custom arrays with only necessary data
        $hotel_list = [];
        
        foreach ($hotels as $hotel) {
            // Get hotel's DMC IDs from JSON field
            $hotel_dmc_ids = $hotel->dmc_id;
            if (is_string($hotel_dmc_ids)) {
                $hotel_dmc_ids = json_decode($hotel_dmc_ids, true) ?? [];
            } elseif (!is_array($hotel_dmc_ids)) {
                $hotel_dmc_ids = [];
            }

            // Find matching DMC IDs between request and hotel
            $matching_dmc_ids = array_intersect($request_dmc_ids, $hotel_dmc_ids);

            if (empty($matching_dmc_ids)) {
                continue; // Skip if no matching DMCs
            }

            // Get minimum price from rooms
            $min_price = $hotel->rooms->min('double_weekday_price') ?? 0;
            
            // Add hotel for each matching DMC ID
            foreach ($matching_dmc_ids as $current_dmc_id) {
                $hotel_list[] = [
                    'hotel_unique_id' => $hotel->hotel_unique_id,
                    'name' => $hotel->name,
                    'main_image' => $hotel->main_image,
                    'city' => $hotel->city,
                    'address' => $hotel->address,
                    'country' => $hotel->country,
                    'hotel_star_rating' => $hotel->hotel_star_rating,
                    'single_base_price' => $min_price > 0 ? $min_price/2 : 0,
                    'status' => $hotel->status,
                    'is_active' => $hotel->is_active,
                    'is_complete' => $hotel->is_complete,
                    'dmc_id' => $current_dmc_id
                ];
            }
        }
        
        $attractions = Attraction::where(function($query) use ($request_dmc_ids) {
            foreach ($request_dmc_ids as $dmc_id) {
                $query->orWhereRaw("dmc_id::text LIKE '%'||?||'%'", [$dmc_id]);
            }
        })->where('location', $city)->where('country', $country)->get();
        
        // Fetch packaged attractions for all DMCs
        $packagedAttractions = PackagedAttraction::whereIn('dmc_id', $request_dmc_ids)
            ->where('status', 1)
            ->get()
            ->filter(function($package) use ($city, $country) {
                $attractionIds = json_decode($package->attractions, true) ?? [];
                if (empty($attractionIds)) {
                    return false;
                }
                
                $matchingAttractions = Attraction::whereIn('attraction_id', $attractionIds)
                    ->where('location', $city)
                    ->where('country', $country)
                    ->count();
                
                return $matchingAttractions > 0;
            });
        
        $restaurants = Restaurant::where(function($query) use ($request_dmc_ids) {
            foreach ($request_dmc_ids as $dmc_id) {
                $query->orWhereRaw("dmc_id::text LIKE '%'||?||'%'", [$dmc_id]);
            }
        })->where('city', $city)->where('country', $country)->get();
        
        // For tables with integer dmc_id field
        $vehicles = Vehicle::whereIn('dmc_id', $request_dmc_ids)
            ->where('city', $city)->get();
        
        $city_id = City::where('name', $city)->first();
        $ports = Port::where('city_id', $city_id->city_id)
            ->where('country', $country)->get();
        
        $guides = Guide::with('languages')
            ->whereIn('dmc_id', $request_dmc_ids)
            ->where('city', $city)
            ->where('country', $country)
            ->get();
        
        // Create list for regular attractions
        $attraction_list = $attractions->map(function($attraction) {
            return [
                'id' => $attraction->id,
                'attraction_id' => $attraction->attraction_id,
                'name' => $attraction->name,
                'location' => $attraction->location,
                'country' => $attraction->country,
                'master_image' => $attraction->master_image,
                'base_price' => $attraction->adult_price,
                'type' => 'attraction',
                'child_price' => $attraction->child_price,
                'description' => $attraction->description,
            ];
        });
        
        // Create list for packaged attractions
        $packaged_attractions = $packagedAttractions->map(function($package) {
            $attractionIds = json_decode($package->attractions, true) ?? [];
            $attractionDetails = [];
            
            if (!empty($attractionIds)) {
                $attractionDetails = Attraction::whereIn('attraction_id', $attractionIds)
                    ->select('attraction_id', 'name', 'location', 'country', 'master_image')
                    ->get()
                    ->map(function($attraction) {
                        return [
                            'attraction_id' => $attraction->attraction_id,
                            'name' => $attraction->name,
                            'location' => $attraction->location,
                            'country' => $attraction->country,
                            'master_image' => $attraction->master_image,
                        ];
                    });
            }
            
            return [
                'id' => $package->id,
                'attraction_id' => $package->package_attraction_id,
                'name' => $package->name . ' (Package)',
                'master_image' => $package->image ? json_decode($package->image, true)[0] ?? null : null,
                'base_price' => $package->adult_price,
                'type' => 'package',
                'child_price' => $package->child_price,
                'senior_citizen_price' => $package->senior_citizen_price,
                'description' => $package->description,
                'attractions' => $attractionDetails,
            ];
        });
        
        $restaurant_list = $restaurants->map(function($restaurant) {
            return [
                'restaurant_id' => $restaurant->restaurant_id,
                'name' => $restaurant->name,
                'master_image' => $restaurant->master_image,
                'city' => $restaurant->city,
                'country' => $restaurant->country,
                'base-price' => $restaurant->bf_price,
            ];
        });
        
        $vehicle_list = $vehicles->map(function($vehicle) {
            return [
                'id' => $vehicle->id,
                'vehicle_id' => $vehicle->vehicle_id,
                'vehicle_type' => $vehicle->vehicle_type,
                'vehicle_name' => $vehicle->vehicle_name,
                'image' => $vehicle->image,
                'city' => $vehicle->city,
                'base_price' => $vehicle->base_price,
                'seating_capacity' => $vehicle->seating_capacity,
            ];
        });
        
        $port_list = $ports->map(function($port) {
            return [
                'port_id' => $port->port_id,
                'port_name' => $port->port_name,
                'type' => $port->type,
                'country' => $port->country,
                'distance' => $port->distance,
            ];
        });
        
        $guide_list = $guides->map(function($guide) {
            return [
                'guide_id' => $guide->guide_id,
                'name' => $guide->name,
                'guide_gender' => $guide->guide_gender,
                'guide_age' => $guide->guide_age,
                'image' => $guide->image,
                'rating' => $guide->rating,
                'city' => $guide->city,
                'country' => $guide->country,
                'base_price' => $guide->hourly_price,
                'languages' => $guide->languages->pluck('language'),
            ];
        });
        
        $items = [
            'hotels' => $hotel_list,
            'attractions' => $attraction_list,
            'restaurants' => $restaurant_list,
            'guides' => $guide_list,
            'vehicles' => $vehicle_list,
            'ports' => $port_list,
            'packaged_attractions' => $packaged_attractions,
        ];

        return response()->json([
            'success' => true,
            'message' => 'Successful',
            'data' => $items,
        ]);
    }

    public function UpdateEnquiryForm(Request $request)
    {
        try {
            $validated = $request->validate([
                'enquiry_id' => 'required',
                'hotel' => 'required|boolean',
                'hotel_ids' => 'nullable|array',
                'hotel_categories' => 'nullable|array',
                'hotel_remarks' => 'nullable|string',

                'port' => 'nullable|boolean',
                'port_remarks' => 'nullable|string',
                'local_transfer' => 'required|boolean',

                'attraction' => 'required|boolean',
                'attraction_ids' => 'nullable|array',
                'attraction_remarks' => 'nullable|string',
                'attraction_transport' => 'nullable|boolean',
                'attraction_transport_type' => 'nullable|string',

                'restaurant' => 'required|boolean',
                'restaurant_ids' => 'nullable|array',
                'restaurant_remarks' => 'nullable|string',
                'restaurant_transport' => 'nullable|boolean',
                'restaurant_transport_type' => 'nullable|string',

                'guide' => 'required|boolean',
                'guide_ids' => 'nullable|array',
                'guide_remarks' => 'nullable|string',

                // Additional fields
                'local_transport_type' => 'nullable|string',
                'port_transport_type' => 'nullable|string',
                'local_transport_vehicle_ids' => 'nullable|array',
                'port_vehicle_ids' => 'nullable|array',
                'compare_hotel' => 'nullable|boolean',
                'port_type' => 'nullable|string',

                'entry_port' => 'nullable',
                'entry_port_address' => 'nullable|string',
                'entry_dropoff_type' => 'nullable|string',
                'entry_dropoff_location_id' => 'nullable',

                'exit_port' => 'nullable',
                'exit_port_address' => 'nullable|string',
                'exit_pickup_type' => 'nullable|string',
                'exit_pickup_location_id' => 'nullable',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }

        // Fetch all enquiries with the same multi_enq_id
        $enquiries = EnquiryForm::where('multi_enq_id', $validated['enquiry_id'])->get();
        if($enquiries->isEmpty()){
            return response()->json(['error' => true, 'message' => 'Data not found for given enquiry id.']);
        }

        // Update each enquiry
        foreach ($enquiries as $enquiry) {
            // Update fields
            $enquiry->hotel = $validated['hotel'];
            $enquiry->hotel_ids = json_encode($validated['hotel_ids'] ?? []);
            $enquiry->hotel_categories = json_encode($validated['hotel_categories'] ?? []);
            $enquiry->hotel_remarks = $validated['hotel_remarks'] ?? null;
            $enquiry->pickup = $validated['port'];
            $enquiry->pickup_remarks = $validated['port_remarks'] ?? null;

            $enquiry->local_transfer = $validated['local_transfer'];

            $enquiry->attraction = $validated['attraction'];
            $enquiry->attraction_ids = json_encode($validated['attraction_ids'] ?? []);
            $enquiry->attraction_remarks = $validated['attraction_remarks'] ?? null;
            $enquiry->attraction_transport = $validated['attraction_transport'] ?? null;
            $enquiry->attraction_transport_type = $validated['attraction_transport_type'] ?? null;

            $enquiry->restaurant = $validated['restaurant'];
            $enquiry->restaurant_ids = json_encode($validated['restaurant_ids'] ?? []);
            $enquiry->restaurant_remarks = $validated['restaurant_remarks'] ?? null;
            $enquiry->restaurant_transport = $validated['restaurant_transport'] ?? null;
            $enquiry->restaurant_transport_type = $validated['restaurant_transport_type'] ?? null;

            $enquiry->guide = $validated['guide'];
            $enquiry->guide_ids = json_encode($validated['guide_ids'] ?? []);
            $enquiry->guide_remarks = $validated['guide_remarks'] ?? null;

            // Additional transport and vehicle fields
            $enquiry->local_transport_type = $validated['local_transport_type'] ?? null;
            $enquiry->port_transport_type = $validated['port_transport_type'] ?? null;
            $enquiry->local_transport_vehicle_ids = json_encode($validated['local_transport_vehicle_ids'] ?? []);
            $enquiry->port_vehicle_ids = json_encode($validated['port_vehicle_ids'] ?? []);
            $enquiry->compare_hotel = $validated['compare_hotel'] ?? null;
            $enquiry->port_type = $validated['port_type'] ?? null;

            // Additional fields
            $enquiry->entry_port = $validated['entry_port'] ?? null;
            $enquiry->entry_port_address = $validated['entry_port_address'] ?? null;
            $enquiry->entry_dropoff_type = $validated['entry_dropoff_type'] ?? null;
            $enquiry->entry_dropoff_location_id = $validated['entry_dropoff_location_id'] ?? null;

            $enquiry->exit_port = $validated['exit_port'] ?? null;
            $enquiry->exit_port_address = $validated['exit_port_address'] ?? null;
            $enquiry->exit_pickup_type = $validated['exit_pickup_type'] ?? null;
            $enquiry->exit_pickup_location_id = $validated['exit_pickup_location_id'] ?? null;
            $enquiry->approx_price = $request->approx_price ?? null;
            $enquiry->packaged_attractions = $request->packaged_attractions ?? null;
            $enquiry->packaged_attraction_ids = json_encode($request->packaged_attraction_ids ?? []);

            // Save the updated enquiry
            $enquiry->save();
        }

        return response()->json([
            'success' => true, 
            'message' => 'All enquiries updated successfully.',
            'updated_count' => $enquiries->count()
        ]);
    }

    public function listofenquiry(Request $request)
    {
        $user = auth()->user();
        $agent_id = $request->agent_id;
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 401);
        }
        $enquiries = collect();
        $tour_enquiries_list = collect();

        if ($agent_id) {
            // If user is DMC role (33, 37, 38), verify they have access to this agent
            if (in_array($user->role_id, [33, 37, 38])) {
                $hasAccess = false;
                $dmc_id = null;
                
                if ($user->role_id == 33) { // Sales Head
                    $dmc_id = $user->created_by;
                } elseif ($user->role_id == 37) { // Sales Manager
                    // Get parent DMC ID by traversing up the hierarchy
                    $parentUser = User::where('userId', $user->created_by)->first();
                    while ($parentUser && !in_array($parentUser->role_id, [11])) {
                        $parentUser = User::where('userId', $parentUser->created_by)->first();
                    }
                    if ($parentUser && $parentUser->role_id == 11) {
                        $dmc_id = $parentUser->userId;
                    }
                } elseif ($user->role_id == 38) { // Assistant Sales Manager
                    // Get parent DMC ID by traversing up the hierarchy
                    $parentUser = User::where('userId', $user->created_by)->first();
                    while ($parentUser && !in_array($parentUser->role_id, [11])) {
                        $parentUser = User::where('userId', $parentUser->created_by)->first();
                    }
                    if ($parentUser && $parentUser->role_id == 11) {
                        $dmc_id = $parentUser->userId;
                    }
                }
                
                if ($dmc_id) {
                    // Check if the agent belongs to this DMC using the dmc_id field
                    $agent = Agent::where('agent_id', $agent_id)->first();
                    if ($agent) {
                        // Check if agent's dmc_id field contains this DMC ID
                        $agent_dmc_ids = $agent->dmc_id;
                        if (is_string($agent_dmc_ids)) {
                            $agent_dmc_ids = json_decode($agent_dmc_ids, true) ?? [];
                        } elseif (!is_array($agent_dmc_ids)) {
                            $agent_dmc_ids = [$agent_dmc_ids];
                        }
                        
                        $hasAccess = in_array($dmc_id, $agent_dmc_ids);
                    }
                }
                
                if (!$hasAccess) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have access to this agent\'s enquiries.',
                    ], 403);
                }
                
                // For DMC roles, filter enquiries by both agent_id AND dmc_id
                $enquiries = EnquiryForm::where('agent_id', $agent_id)
                    ->where('dmc_id', $dmc_id)
                    ->whereNull('unique_tour_id')
                    ->where('status', null)
                    ->get();
                $tour_enquiries_list = EnquiryForm::where('agent_id', $agent_id)
                    ->where('dmc_id', $dmc_id)
                    ->whereNotNull('unique_tour_id')
                    ->where('status', null)
                    ->get();
            } else {
                // For agents, show all their enquiries (no DMC filtering)
                $enquiries = EnquiryForm::where('agent_id', $agent_id)
                    ->whereNull('unique_tour_id')
                    ->where('status', null)
                    ->get();
                $tour_enquiries_list = EnquiryForm::where('agent_id', $agent_id)
                    ->whereNotNull('unique_tour_id')
                    ->where('status', null)
                    ->get();
            }
            
            if (!$enquiries) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Enquiry Found.',
                ], 404);
            }
        }

        elseif($user->userId){
            $currentUser = null;
            if(in_array($user->role_id, [33, 37, 38,])){
                $currentUser = User::where('userId', $user->userId)->first();

                if (!$currentUser) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User not found.',
                    ], 404);
                }
            }

            if($currentUser){
                // For DMC roles (33, 37, 38), they must select an agent first
                // No enquiries shown until an agent is selected
                $enquiries = collect();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Please select an agent to view enquiries.',
                    'enquiries' => [],
                    'note' => 'Use agent_id parameter to view specific agent enquiries'
                ]);
            }
            else{
                if (empty($user?->agent_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized or Agent ID missing.',
                    ], 401);
                }
                $enquiries = EnquiryForm::where('agent_id', $user->agent_id)
                    ->whereNull('unique_tour_id')
                    ->where('status', null)
                    ->get();
            }
        }
        elseif($user->agent_id){
            $enquiries = EnquiryForm::where('agent_id', $user->agent_id)
                ->whereNull('unique_tour_id')
                ->where('status', null)
                ->get();
        }
        $hotelIds = [];
        $restaurantIds = [];
        $attractionIds = [];
        $guideIds = [];
        $enquiry_lists = [];
        foreach($enquiries as $enquiry){
            // Decode and normalize all IDs to arrays of integers
            $hotelIds = is_array($decoded = json_decode($enquiry->hotel_ids, true)) ? $decoded : [];
            $restaurantIds = is_array($decoded = json_decode($enquiry->restaurant_ids, true)) ? array_map('intval', $decoded) : [];
            $attractionIds = is_array($decoded = json_decode($enquiry->attraction_ids, true)) ? array_map('intval', $decoded) : [];
            $guideIds = is_array($decoded = json_decode($enquiry->guide_ids, true)) ? array_map('intval', $decoded) : [];
            $localTransportVehicleIds = is_array($decoded = json_decode($enquiry->local_transport_vehicle_ids, true)) ? array_map('intval', $decoded) : [];
            $portVehicleIds = is_array($decoded = json_decode($enquiry->port_vehicle_ids, true)) ? array_map('intval', $decoded) : [];

            // Decode packaged attraction IDs
            $packagedAttractionIds = is_array($decoded = json_decode($enquiry->packaged_attraction_ids, true)) ? array_map('intval', $decoded) : [];

            // Fetch related models
            $restaurants = Restaurant::whereIn('restaurant_id', $restaurantIds)->get(); 
            $attractions = Attraction::whereIn('attraction_id', $attractionIds)->get();
            $hotels = Hotel::whereIn('hotel_unique_id', $hotelIds)->get();
            $guides = Guide::whereIn('guide_id', $guideIds)->get();

            $localTransports = Vehicle::whereIn('vehicle_id', $localTransportVehicleIds)->get();
            $portVehicles = Vehicle::whereIn('vehicle_id', $portVehicleIds)->get();

            // Fetch packaged attractions
            $packagedAttractions = PackagedAttraction::whereIn('package_attraction_id', $packagedAttractionIds)
                ->get()
                ->each(function($package) {
                    // Decode the attractions JSON array
                    $attractionIds = json_decode($package->attractions, true) ?? [];
                    
                    // Fetch the actual attraction details
                    $attractionDetails = [];
                    if (!empty($attractionIds)) {
                        $attractionDetails = Attraction::whereIn('attraction_id', $attractionIds)
                            ->select('attraction_id', 'name', 'master_image', 'location', 'country','open_time','close_time')
                            ->get();
                    }

                    // Add attraction details to the package model
                    $package->attraction_details = $attractionDetails;
                });

            $entry_dropoff_location = null;
            if($enquiry->entry_dropoff_type == 'hotel'){
                $entry_dropoff_location = Hotel::where('hotel_unique_id', $enquiry->entry_dropoff_location_id)->first();
            }
            elseif($enquiry->entry_dropoff_type == 'attraction'){
                $entry_dropoff_location = Attraction::where('attraction_id', $enquiry->entry_dropoff_location_id)->first();
            }
            elseif($enquiry->entry_dropoff_type == 'restaurant'){
                $entry_dropoff_location = Restaurant::where('restaurant_id', $enquiry->entry_dropoff_location_id)->first();
            }

            $exit_pickup_location = null;
            if($enquiry->exit_pickup_type == 'hotel'){
                $exit_pickup_location = Hotel::where('hotel_unique_id', $enquiry->exit_pickup_location_id)->first();
            }
            elseif($enquiry->exit_pickup_type == 'attraction'){

                $exit_pickup_location = Attraction::where('attraction_id',  intval($enquiry->exit_pickup_location_id))->first();
            }
            elseif($enquiry->exit_pickup_type == 'restaurant'){
                $exit_pickup_location = Restaurant::where('restaurant_id',  intval($enquiry->exit_pickup_location_id))->first();
            }

            $enquiry_lists[] = [
                'enquiry_id' => $enquiry->enquiry_id,
                'agent_id' => $enquiry->agent_id,
                'display_id' => $enquiry->display_id,
                'country' => $enquiry->country,
                'city' => $enquiry->city,
                'adult' => $enquiry->adult,
                'child' => $enquiry->child,
                'infant' => $enquiry->infant,
                'check_in_time' => $enquiry->check_in_time,
                'check_out_time' => $enquiry->check_out_time,
                'male_count' => $enquiry->male_count,
                'female_count' => $enquiry->female_count,
                'child_ages' => $enquiry->child_ages,
                'hotel' => $enquiry->hotel,
                'hotel_categories' => json_decode($enquiry->hotel_categories),
                'hotel_remarks' => $enquiry->hotel_remarks,
                'hotel_details' => $hotels,
                'port' => $enquiry->pickup,
                'port_type' => $enquiry->port_type,
                'port_details' => $portVehicles,
                'port_remarks' => $enquiry->pickup_remarks,
                'local_transfer' => $enquiry->local_transfer,
                'local_transfer_type' => $enquiry->local_transport_type,
                'local_transfer_details' => $localTransports,
                'attraction' => $enquiry->attraction,
                'attraction_remarks' => $enquiry->attraction_remarks,
                'attraction_details' => $attractions,
                'restaurant' => $enquiry->restaurant,
                'restaurant_remarks' => $enquiry->restaurant_remarks,
                'restaurant_details' => $restaurants,
                'guide' => $enquiry->guide,
                'guide_remarks' => $enquiry->guide_remarks,
                'guide_details' => $guides,

                'packaged_attractions' => $enquiry->packaged_attractions,
                'packaged_attraction_details' => $packagedAttractions,

                'entry_port' => $enquiry->entry_port,
                'entry_port_address' => $enquiry->entry_port_address,
                'entry_dropoff_type' => $enquiry->entry_dropoff_type,
                'entry_dropoff_location' => $entry_dropoff_location,

                'exit_port' => $enquiry->exit_port,
                'multi_enq_id' => $enquiry->multi_enq_id,
                'created_at' => $enquiry->created_at->format('Y-m-d H:i:s'),
                'approx_price' => $enquiry->approx_price,
                'exit_port_address' => $enquiry->exit_port_address,
                'exit_pickup_type' => $enquiry->exit_pickup_type,
                'exit_pickup_location' => $exit_pickup_location,
            ];
        }
            
        return response()->json([
            'success' => true,
            'message' => 'Successful',
            'enquiries' => $enquiry_lists,
            
        ]);
    }

    public function enquiryToTour(Request $request){
        try {
            // Validate request
            $validated = $request->validate([
                'enquiry_id' => 'required',
                'agent_id' => 'required',
            ]);

            $enquiryId = $request->enquiry_id;
            $agentId = $request->agent_id;

            // Fix variable name inconsistency
            $formEnquiry = EnquiryForm::where('enquiry_id', $enquiryId)
                                      ->where('agent_id', $agentId)
                                      ->whereNull('unique_tour_id')
                                      ->first();

            if (!$formEnquiry) {
                return response()->json([
                    'success' => false,
                    'message' => 'Enquiry not found for the given enquiry_id and agent_id.'
                ], 404);
            }

            // Generate tour ID and save the tour
            // $max_tour_id = Tour::max('tour_id') ?? 0;
            // $tourId = CommonHelper::createId($max_tour_id);

            // $randomDigits = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
            // $display_id = 'DMC-ORD'. $tourId;

            // $tour = new Tour();
            // // Map all common fields between inquiry_form and tours tables
            // $tour->destination = $formEnquiry->country;
            // $tour->adult = $formEnquiry->adult;
            // $tour->child = $formEnquiry->child;
            // $tour->infant = $formEnquiry->infant;
            // $tour->agent_id = $agentId;
            // $tour->tour_id = $tourId;
            // $tour->male_count = $formEnquiry->male_count;
            // $tour->female_count = $formEnquiry->female_count;
            // $tour->check_in_time = $formEnquiry->check_in_time;
            // $tour->check_out_time = $formEnquiry->check_out_time;
            // $tour->display_id = $display_id;
            // $tour->child_ages = $formEnquiry->child_ages;

            // // Map service-related fields if they exist in both tables
            // // Set default status (2 = pending)
            // $tour->tour_status = "Pending";
            // $tour->save();
            // $tour->refresh();
            // $formEnquiry->unique_tour_id = $tour->unique_tour_id;
            // $formEnquiry->save();
            $agent = Agent::where('agent_id', $formEnquiry->agent_id)->first();

            return response()->json([
                'data' => [
                    'country' => $formEnquiry->country,
                    'city' => $formEnquiry->city,
                    'enquiry_id' => $formEnquiry->enquiry_id,
                    'agent_id' => $formEnquiry->agent_id,
                    'agent_name' => $agent ? $agent->name : null,
                    'check_in_time' => $formEnquiry->check_in_time,
                    'check_out_time' => $formEnquiry->check_out_time,
                    'adult' => $formEnquiry->adult,
                    'child' => $formEnquiry->child,
                    'infant' => $formEnquiry->infant,
                    'male_count' => $formEnquiry->male_count,
                    'female_count' => $formEnquiry->female_count,
                ],
                'success' => true,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while converting enquiry to tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function agentLists(Request $request)
    {
        $user = auth()->user();
        
        // Check if user is a DMC role (Sales Head, Sales Manager, or Asst Sales Manager)
        if (!in_array($user?->role_id, [33, 37, 38])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access. Only DMC roles can access this endpoint.',
            ], 401);
        }

        $agents = collect();

        switch ($user->role_id) {
            case 33: // Sales Head
                // Get all Sales Managers under this Sales Head
                $sales_managers = User::where('role_id', 37)
                    ->where('created_by', $user->userId)
                    ->get();
                $sales_managers_ids = $sales_managers->pluck('userId')->toArray();

                // Get all Assistant Sales Managers under those Sales Managers
                $asst_managers = User::where('role_id', 38)
                    ->whereIn('created_by', $sales_managers_ids)
                    ->get();
                $asst_managers_ids = $asst_managers->pluck('userId')->toArray();

                // Get parent DMC's ID (the DMC who created this Sales Head)
                $dmc_id = User::where('userId', $user->created_by)
                             ->where('role_id', 11)
                             ->value('userId');

                if ($dmc_id) {
                    // Get all agents under Sales Head, Sales Managers and Assistant Managers
                    // Also include agents that have the DMC's ID in their dmc_id field
                    $agents = Agent::where(function($query) use ($user, $sales_managers_ids, $asst_managers_ids) {
                        $query->where('sales_manager_dmc', $user->userId)
                            ->orWhereIn('sales_manager_dmc', $sales_managers_ids)
                            ->orWhereIn('sales_manager_dmc', $asst_managers_ids);
                    })->orWhere(function($query) use ($dmc_id) {
                        $query->whereRaw("CASE 
                            WHEN dmc_id IS NOT NULL 
                            THEN (
                                CASE 
                                    WHEN dmc_id::text ~ '^\\[.*\\]$' 
                                    THEN dmc_id::jsonb @> ?::jsonb
                                    WHEN dmc_id::text ~ '^\\{.*\\}$'
                                    THEN dmc_id::jsonb @> ?::jsonb
                                    ELSE dmc_id::text LIKE ?
                                END
                            )
                            ELSE false
                        END", [
                            json_encode([$dmc_id]),
                            json_encode([$dmc_id]),
                            "%{$dmc_id}%"
                        ]);
                    })->get();
                }
                break;

            case 37: // Sales Manager
                // Get all Assistant Sales Managers under this Sales Manager
                $asst_managers = User::where('role_id', 38)
                    ->where('created_by', $user->userId)
                    ->get();
                $asst_managers_ids = $asst_managers->pluck('userId')->toArray();

                // Get parent DMC's ID by traversing up the hierarchy
                $parentUser = User::where('userId', $user->created_by)->first();
                while ($parentUser && !in_array($parentUser->role_id, [11])) {
                    $parentUser = User::where('userId', $parentUser->created_by)->first();
                }

                if ($parentUser && $parentUser->role_id == 11) {
                    $dmc_id = $parentUser->userId;
                    // Get all agents under Sales Manager and their Assistant Managers
                    // Also include agents that have the DMC's ID in their dmc_id field
                    $agents = Agent::where(function($query) use ($user, $asst_managers_ids) {
                        $query->where('sales_manager_dmc', $user->userId)->where('status', 1)
                            ->orWhereIn('sales_manager_dmc', $asst_managers_ids);
                    })->orWhere(function($query) use ($dmc_id) {
                        $query->whereRaw("CASE 
                            WHEN dmc_id IS NOT NULL 
                            THEN (
                                CASE 
                                    WHEN dmc_id::text ~ '^\\[.*\\]$' 
                                    THEN dmc_id::jsonb @> ?::jsonb
                                    WHEN dmc_id::text ~ '^\\{.*\\}$'
                                    THEN dmc_id::jsonb @> ?::jsonb
                                    ELSE dmc_id::text LIKE ?
                                END
                            )
                            ELSE false
                        END", [
                            json_encode([$dmc_id]),
                            json_encode([$dmc_id]),
                            "%{$dmc_id}%"
                        ]);
                    })->get();
                }
                break;

            case 38: // Assistant Sales Manager
                // Get parent DMC's ID by traversing up the hierarchy
                $parentUser = User::where('userId', $user->created_by)->first();
                while ($parentUser && !in_array($parentUser->role_id, [11])) {
                    $parentUser = User::where('userId', $parentUser->created_by)->first();
                }

                if ($parentUser && $parentUser->role_id == 11) {
                    $dmc_id = $parentUser->userId;
                    // Get agents directly under this Assistant Sales Manager
                    // Also include agents that have the DMC's ID in their dmc_id field
                    $agents = Agent::where(function($query) use ($user) {
                        $query->where('sales_manager_dmc', $user->userId);
                    })->orWhere(function($query) use ($dmc_id) {
                        $query->whereRaw("CASE 
                            WHEN dmc_id IS NOT NULL 
                            THEN (
                                CASE 
                                    WHEN dmc_id::text ~ '^\\[.*\\]$' 
                                    THEN dmc_id::jsonb @> ?::jsonb
                                    WHEN dmc_id::text ~ '^\\{.*\\}$'
                                    THEN dmc_id::jsonb @> ?::jsonb
                                    ELSE dmc_id::text LIKE ?
                                END
                            )
                            ELSE false
                        END", [
                            json_encode([$dmc_id]),
                            json_encode([$dmc_id]),
                            "%{$dmc_id}%"
                        ]);
                    })->get();
                }
                break;
        }

        // For debugging
        \Log::info('Agents Query', [
            'role_id' => $user->role_id,
            'user_id' => $user->userId,
            'agent_count' => $agents->count(),
            'agents' => $agents->pluck('dmc_id', 'agent_id')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'DMC agents retrieved successfully',
            'agents' => $agents,
        ]);
    }

}
