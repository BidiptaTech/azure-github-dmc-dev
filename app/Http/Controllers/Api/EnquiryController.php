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
use App\Models\Restaurant;
use App\Models\Port;
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
        // $countryArray = array_map('trim', explode(',', $countryNames));

        $user = auth()->user();
        $agent_id = $user->agent_id;

        try {
            // Parse the dates
            $checkInTime = Carbon::createFromFormat('d/m/Y', $request->check_in);
            $checkOutTime = Carbon::createFromFormat('d/m/Y', $request->check_out);

            // Generate tour ID and save the tour
            $max_tour_id = EnquiryForm::max('enquiry_id') ?? 0;
            $tourId = CommonHelper::createId($max_tour_id);

            $randomDigits = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $display_id = 'DMC-ENQ' . $tourId;

            $enquiry = new EnquiryForm();
            $enquiry->adult = $request->adult ?? 0;
            $enquiry->child = $request->child ?? 0;
            $enquiry->infant = $request->infant ?? 0;
            $enquiry->agent_id = $agent_id;
            $enquiry->enquiry_id = $tourId;
            $enquiry->male_count = $request->male ?? 0;
            $enquiry->female_count = $request->female ?? 0;
            $enquiry->check_in_time = $checkInTime;
            $enquiry->check_out_time = $checkOutTime;
            $enquiry->display_id = $display_id;
            $enquiry->country = $country;
            $enquiry->city = $city;
            $enquiry->child_ages = $request->children_ages ?? null;
            $enquiry->save();
            $enquiry->refresh();
            return response()->json([
                'message' => 'EnquiryForm created successfully',
                // 'enquiry_id' => $enquiry->enquiry_id,
                'data' => [
                    'enquiry_id' => $enquiry->enquiry_id,
                    'agent_id' => $agent_id,
                    'country' => $enquiry->country,
                    'child' => $enquiry->child,
                    'infant' => $enquiry->infant,
                    'male' => $enquiry->male_count,
                    'female' => $enquiry->female_count,
                    'CheckInTime' => CommonHelper::DateFormat($checkInTime),
                    'CheckOutTime' => CommonHelper::DateFormat($checkOutTime),
                    'adult' => $enquiry->adult,
                    'total_pax' => $enquiry->adult + $enquiry->child,
                    'city' => $request->city,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the tour',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function enquiry_lists(Request $request)
    {
        $agent = auth()->user()->sales_manager_dmc;
        $user = User::where('userId', $agent)->first();
        $country = $request->country;
        $city = $request->city;

        if (!$country || !$city) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter both country and city.',
            ]);
        }

        $dmc_id = null;
        if ($user) {
            switch ($user->role_id) {
                case 11: // DMC
                    $dmc_id = $user->userId;
                    break;

                case 33: // Sales Head
                    $saleshead_dmc = User::where('userId', $user->userId)->first();
                    $dmc_users = $saleshead_dmc
                        ? User::where('userId', $saleshead_dmc->created_by)->first()
                        : User::where('userId', $user->created_by)->first();
                    $dmc_id = $dmc_users->userId ?? null;
                    break;

                case 12:
                case 37: // Sales Manager
                    $salesmng_dmc = User::where('userId', $user->userId)->first();
                    if ($salesmng_dmc) {
                        $saleshead_dmc = User::where('userId', $salesmng_dmc->created_by)->first();
                        $dmc_users = $saleshead_dmc
                            ? User::where('userId', $saleshead_dmc->created_by)->first()
                            : null;
                        $dmc_id = $dmc_users->userId ?? null;
                    }
                    break;

                case 38: // Assistant Manager
                    $asmng_dmc = User::where('userId', $user->userId)->first();
                    $salesmng_dmc = $asmng_dmc
                        ? User::where('userId', $asmng_dmc->created_by)->first()
                        : null;
                    $saleshead_dmc = $salesmng_dmc
                        ? User::where('userId', $salesmng_dmc->created_by)->first()
                        : null;
                    $dmc_users = $saleshead_dmc
                        ? User::where('userId', $saleshead_dmc->created_by)->first()
                        : null;
                    $dmc_id = $dmc_users->userId ?? null;
                    break;
            }
        }

        if (!$dmc_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to determine DMC ID.',
            ]);
        }

        // Fetch all services for the given city and DMC
        $hotels = Hotel::with(['rooms' => function($query) {
            $query->select('hotel_id', 'double_weekday_price', 'room_type', 'room_id')
                  ->selectRaw('(double_weekday_price / 2) as single_base_price');
        }])->where('dmc_id', $dmc_id)->where('city', $city)->where('country', $country)->get();
        
        $attractions = Attraction::where('dmc_id', $dmc_id)->where('location', $city)->where('country', $country)->get();
        $restaurants = Restaurant::where('dmc_id', $dmc_id)->where('city', $city)->where('country', $country)->get();
        $vehicles = Vehicle::where('dmc_id', $dmc_id)->where('city', $city)->get(); // Assuming Driver model exists
        $city_id = City::where('name', $city)->first();
        $ports = Port::where('city_id', $city_id->city_id)->where('country', $country)->get();
        $guides = Guide::with('languages')->where('dmc_id', $dmc_id)->where('city', $city)->where('country', $country)->get(); // Assuming Guide model exists
        
        // Create custom arrays with only necessary data
        $hotel_list = $hotels->map(function($hotel) {
            // Get minimum double_weekday_price from all rooms
            $min_price = $hotel->rooms->min('double_weekday_price');
            
            return [
                'hotel_unique_id' => $hotel->hotel_unique_id,
                'name' => $hotel->name,
                'main_image' => $hotel->main_image,
                'city' => $hotel->city,
                'address' => $hotel->address,
                'country' => $hotel->country,
                'hotel_star_rating' => $hotel->hotel_star_rating,
                'single_base_price' => $min_price/2,
            ];
        });
        
        $attraction_list = $attractions->map(function($attraction) {
            return [
                'attraction_id' => $attraction->attraction_id,
                'name' => $attraction->name,
                'location' => $attraction->location,
                'country' => $attraction->country,
                'master_image' => $attraction->master_image,
                'base_price' => $attraction->adult_price,
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
                'errors' => $e->errors(), // sends field-wise errors
            ], 422);
        }

        // Fetch the enquiry
        $enquiry = EnquiryForm::where('enquiry_id', $validated['enquiry_id'])->first();
        if(!$enquiry){
            return response()->json(['error' => true, 'message' => 'Data not found for given enquiry id.']);
        }

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

        // Save the updated enquiry
        $enquiry->save();

        return response()->json(['success' => true, 'message' => 'Enquiry updated successfully.']);
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
            $enquiries = EnquiryForm::where('agent_id', $agent_id)->whereNull('unique_tour_id')->get();
            $tour_enquiries_list = EnquiryForm::where('agent_id', $agent_id)->whereNotNull('unique_tour_id')->get();
            if (!$enquiries) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Enquiry Found.',
                ], 404);
            }
        }

        elseif($user->userId){
            $currentUser = null;
            if(in_array($user->role_id, [33, 37, 38])){
                $currentUser = User::where('userId', $user->userId)->first();

                if (!$currentUser) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User not found.',
                    ], 404);
                }
            }

            if($currentUser){
                $role_id = $currentUser->role_id;
                $agents = collect();

                if($currentUser->role_id == 33){
                    $sales_managers = User::where('role_id', 37)->where('created_by', $user->userId)->get();
                    $sales_managers_ids = $sales_managers->pluck('userId')->toArray();

                    // Ensure we have an array for whereIn
                    if (!empty($sales_managers_ids)) {
                        $assisstant_manager = User::where('role_id', 38)
                            ->whereIn('created_by', $sales_managers_ids)
                            ->get();
                    } else {
                        $assisstant_manager = collect();
                    }

                    $assisstant_manager_ids = $assisstant_manager->pluck('userId')->toArray();

                    $agents = Agent::where(function($query) use ($user, $sales_managers_ids, $assisstant_manager_ids) {
                        $query->where('sales_manager_dmc', $user->userId);

                        if (!empty($sales_managers_ids)) {
                            $query->orWhereIn('sales_manager_dmc', $sales_managers_ids);
                        }

                        if (!empty($assisstant_manager_ids)) {
                            $query->orWhereIn('sales_manager_dmc', $assisstant_manager_ids);
                        }
                    })->get();
                }
                elseif($user->role_id == 37){
                    $assisstant_manager = User::where('role_id', 38)
                        ->where('created_by', $user->userId)
                        ->get();
                    $assisstant_manager_ids = $assisstant_manager->pluck('userId')->toArray();

                    $agents = Agent::where(function($query) use ($user, $assisstant_manager_ids) {
                        $query->where('sales_manager_dmc', $user->userId);

                        if (!empty($assisstant_manager_ids)) {
                            $query->orWhereIn('sales_manager_dmc', $assisstant_manager_ids);
                        }
                    })->get();
                }
                else{
                    $agents = Agent::where('sales_manager_dmc', $currentUser->userId)->get();
                }

                $agentIds = $agents->pluck('agent_id')->toArray();

                // Check if we have any agent IDs before using whereIn
                if (empty($agentIds)) {
                    $enquiries = collect(); // Return empty collection if no agents found
                } else {
                    $enquiries = EnquiryForm::whereIn('agent_id', $agentIds)->whereNull('unique_tour_id')->get();
                }
            }
            else{
                if (empty($user?->agent_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized or Agent ID missing.',
                    ], 401);
                }
                $enquiries = EnquiryForm::where('agent_id', $user->agent_id)->whereNull('unique_tour_id')->get();
            }
        }
        elseif($user->agent_id){
            $enquiries = EnquiryForm::where('agent_id', $user->agent_id)->whereNull('unique_tour_id')->get();
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

            // Fetch related models
            $restaurants = Restaurant::whereIn('restaurant_id', $restaurantIds)->get(); 
            $attractions = Attraction::whereIn('attraction_id', $attractionIds)->get();
            $hotels = Hotel::whereIn('hotel_unique_id', $hotelIds)->get();
            $guides = Guide::whereIn('guide_id', $guideIds)->get();

            $localTransports = Vehicle::whereIn('vehicle_id', $localTransportVehicleIds)->get();
            $portVehicles = Vehicle::whereIn('vehicle_id', $portVehicleIds)->get();

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

                'entry_port' => $enquiry->entry_port,
                'entry_port_address' => $enquiry->entry_port_address,
                'entry_dropoff_type' => $enquiry->entry_dropoff_type,
                'entry_dropoff_location' => $entry_dropoff_location,

                'exit_port' => $enquiry->exit_port,
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
            $max_tour_id = Tour::max('tour_id') ?? 0;
            $tourId = CommonHelper::createId($max_tour_id);

            $randomDigits = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $display_id = 'DMC-ORD'. $tourId;

            $tour = new Tour();
            // Map all common fields between inquiry_form and tours tables
            $tour->destination = $formEnquiry->country;
            $tour->adult = $formEnquiry->adult;
            $tour->child = $formEnquiry->child;
            $tour->infant = $formEnquiry->infant;
            $tour->agent_id = $agentId;
            $tour->tour_id = $tourId;
            $tour->male_count = $formEnquiry->male_count;
            $tour->female_count = $formEnquiry->female_count;
            $tour->check_in_time = $formEnquiry->check_in_time;
            $tour->check_out_time = $formEnquiry->check_out_time;
            $tour->display_id = $display_id;
            $tour->child_ages = $formEnquiry->child_ages;

            // Map service-related fields if they exist in both tables
            // Set default status (2 = pending)
            $tour->tour_status = "Pending";
            $tour->save();
            $tour->refresh();
            $formEnquiry->unique_tour_id = $tour->unique_tour_id;
            $formEnquiry->save();

            return response()->json([
                'success' => true,
                'message' => 'Enquiry successfully converted to tour',
            ], 201);

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
        $currentUser = null;
        if(in_array($user?->role_id, [33, 37, 38])){
            $currentUser = User::where('userId', $user->userId)->first();
        }
        if (!$currentUser) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }
        $agents = collect();

        if($currentUser){
            $role_id = $currentUser->role_id;
            $agents = collect();

            if($user->role_id == 33){
                $sales_managers = User::where('role_id', 37)->where('created_by', $user->userId)->get();
                $sales_managers_ids = $sales_managers->pluck('userId')->toArray();

                // Ensure we have an array for whereIn
                if (!empty($sales_managers_ids)) {
                    $assisstant_manager = User::where('role_id', 38)
                        ->whereIn('created_by', $sales_managers_ids)
                        ->get();
                } else {
                    $assisstant_manager = collect();
                }

                $assisstant_manager_ids = $assisstant_manager->pluck('userId')->toArray();

                $agents = Agent::where(function($query) use ($user, $sales_managers_ids, $assisstant_manager_ids) {
                    $query->where('sales_manager_dmc', $user->userId);

                    if (!empty($sales_managers_ids)) {
                        $query->orWhereIn('sales_manager_dmc', $sales_managers_ids);
                    }

                    if (!empty($assisstant_manager_ids)) {
                        $query->orWhereIn('sales_manager_dmc', $assisstant_manager_ids);
                    }
                })->get();
            }
            elseif($user->role_id == 37){
                $assisstant_manager = User::where('role_id', 38)
                    ->where('created_by', $user->userId)
                    ->get();
                $assisstant_manager_ids = $assisstant_manager->pluck('userId')->toArray();

                $agents = Agent::where(function($query) use ($user, $assisstant_manager_ids) {
                    $query->where('sales_manager_dmc', $user->userId);

                    if (!empty($assisstant_manager_ids)) {
                        $query->orWhereIn('sales_manager_dmc', $assisstant_manager_ids);
                    }
                })->get();
            }
            else{
                $agents = Agent::where('sales_manager_dmc', $currentUser->userId)->get();
            }
        }
        else{
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized or Agent ID missing.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Successful',
            'agents' => $agents,
        ]);
    }

}
