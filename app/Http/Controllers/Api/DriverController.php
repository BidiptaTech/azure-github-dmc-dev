<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Hotel;
use App\Models\Attraction;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\Agent;
use App\Models\Country;
use App\Models\OperationalCountry;
use App\Models\VehicleZoneMapping;
use Illuminate\Support\Facades\Http;
use App\Helpers\CommonHelper;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

use App\Services\CurrencyService;
use Carbon\Carbon;



class DriverController extends Controller
{     

   /*
    * Show Driver Listings.
    * Date 16-01-2025
    */
    public function driverListing(Request $request)
    {
        $location = $request->city;
        $parts = explode(',', $location);
        $city = trim($parts[0]); // it gives City Name
        $country = trim($parts[1] ?? ''); 
        $country = trim($country, '()'); //it gives Country Name
        if (!$city || !$country) {
            return response()->json(['message' => 'City or Country is missing'], 400);
        }
        $drivers = Driver::where('is_active', 1)
            ->where('country', $country)
            ->where('location', $city)
            ->get();

        if ($drivers->isEmpty()) {
            return response()->json(['message' => 'No drivers found for the selected city'], 404);
        }
        $closeDates = explode(',', $driver->close_dates);
        $dateRange = json_decode($request->query('date'), true);

        $driverList = $drivers->map(function ($driver) use ($country) {
            $driverImages = json_decode($driver->additional_image, true) ?? [];
            $closeDates = explode(',', $driver->close_dates); // If comma-separated
            foreach ($dateRange as $date) {
                if (in_array($date, $closeDates)) {
                    $responseData[$date][] = [
                        'status' => 'Not Available',
                    ];
                }
            }
            return [
                'id' => $driver->driver_id,
                'name' => $driver->name,
                'email' => $driver->email,
                'phone' => $driver->phone,
                'license no' => $driver->license_no,
                'license_exp_date' => $driver->license_exp_date,
                'city' => $driver->city,
                'country' => $driver->country,
                'image' => $driver->image,
                'responseData' =>$responseData
            ];
        });
        return response()->json($driverList);
    }

    //Vehicles Listing
    public function vehicleListing(Request $request)
    {
        $agent_id = $request->header('agent-id');
        if(!$agent_id){
            return response()->json(['error' => 'Agent Id not found.'], 404);
        }
        $apiKey = "AIzaSyCLzISM9kkNCKKmQs7BcpSll4emFw1yicw";
        $pickup = json_decode($request->query('pickup'), true);
        $dropoff = json_decode($request->query('dropoff'), true);
        $date = $request->query('date');
        $rawTime = trim($request->input('time'), " \t\n\r\0\x0B\"'"); // removes extra whitespace and quotes
        $time = Carbon::createFromFormat('h:i A', $rawTime)->format('H:i:s');

        $agentId = auth()->user()->agent_id;
            

            $agent = Agent::where('agent_id', $agentId)->first();
            
            $dmc_id = null;
            if ($agent) {
                $salesManagerId = $agent->sales_manager_dmc;
                switch ($agent->role_id) {
                    case 11: // Agent is a DMC
                        $dmc_id = $agent->sales_manager_dmc; // Assuming `userId` in agent or fallback to agent_id
                        break;
                        case 33: 
                        case 128: 
                        case 129: 
                        case 130: 
                        case 134: 
                        case 135: 
                        case 136: 
                        case 138: 
                        $salesManagerId = $agent->sales_manager_dmc;
                             $saleshead_dmc = User::where('userId', $agent->sales_manager_dmc)->first(); // SH
                            if ( $saleshead_dmc) {
                                $dmc_users = User::where('userId',  $saleshead_dmc->created_by)->first(); // DMC
                                if ($dmc_users && $dmc_users->role_id == 11) {
                                    $dmc_id = $dmc_users->userId;
                                }
                            }
                        break;
                    case 12:
                    case 37: // Sales Manager
                        $salesManagerId = $agent->sales_manager_dmc;
                        $salesmng_dmc= User::where('userId', $agent->sales_manager_dmc)->first(); // SM
                        
                        if ($salesmng_dmc) {
                             $saleshead_dmc = User::where('userId', $salesmng_dmc->created_by)->first(); // SH
                            if ( $saleshead_dmc) {
                                $dmc_users = User::where('userId',  $saleshead_dmc->created_by)->first(); // DMC
                                if ($dmc_users && $dmc_users->role_id == 11) {
                                    $dmc_id = $dmc_users->userId;
                                }
                            }
                        }
                        break;
                    case 38: // Assistant Manager
                        $salesManagerId = $agent->sales_manager_dmc;
                        $asmng_dmc = User::where('userId', $agent->sales_manager_dmc)->first(); // SM
                        if($asmng_dmc){
                            $salesmng_dmc = User::where('userId', $asmng_dmc->created_by)->first(); // SH
                        }
                        if ($salesmng_dmc) {
                             $saleshead_dmc = User::where('userId', $salesmng_dmc->created_by)->first(); // SH
                            if ( $saleshead_dmc) {
                                $dmc_users = User::where('userId',  $saleshead_dmc->created_by)->first(); // DMC
                                if ($dmc_users && $dmc_users->role_id == 11) {
                                    $dmc_id = $dmc_users->userId;
                                }
                            }
                        }
                        break;
                }
            }
            elseif(Auth::user()->userId){
                $currentUser = Auth::user();
                
                if($currentUser->role_id == 33 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138)
                {
                    $dmc_id = $currentUser->created_by;
                }
                elseif($currentUser->role_id == 37){
                    $sales_head_id = $currentUser->created_by;
                    $sales_head = User::where('userId', $sales_head_id)->first();
                    $dmc_id = $sales_head->created_by;
                }
                elseif($currentUser->role_id == 38){
                    $sales_manager_id = $currentUser->created_by;
                    $sales_manager = User::where('userId', $sales_manager_id)->first();
                    $sales_head_id = $sales_manager->created_by;
                    $sales_head = User::where('userId', $sales_head_id)->first();
                    $dmc_id = $sales_head->created_by;
                }
            }
            if (!$dmc_id) {
                return response()->json(['message' => 'DMC Not Found!'], 400);
            }
            $start = $request->input('start', 0);
            $limit = $request->input('limit', 9);

            if($pickup && $dropoff){
                $url = "https://maps.googleapis.com/maps/api/directions/json?origin={$pickup['lat']},{$pickup['lng']}&destination={$dropoff['lat']},{$dropoff['lng']}&key=" . $apiKey;
                    $curl = curl_init($url);
                    curl_setopt($curl, CURLOPT_HEADER, false);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_HTTPGET, true);
                    curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
                    $json_response = curl_exec($curl);
                    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    curl_close($curl);
                    $response = json_decode($json_response, true);
                    if(json_last_error()){
                        $error = array("error"=>"Error computing distance. Please retry.");
                        echo json_encode($error); //database error
                        exit;
                    }
                    $startAddress = $response['routes'][0]['legs'][0]['start_address'];
                    $distance = (($response['routes'][0]['legs'][0]['distance']['value']));
                    $distanceInKM = $distance/1000;

                    //geocode api for getting city
                    $pickupLat = $pickup['lat'];
                    $pickupLng = $pickup['lng'];
                    $geocodeUrl = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$pickupLat},{$pickupLng}&key={$apiKey}";

                    $curl = curl_init($geocodeUrl);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                    $geocodeResponse = curl_exec($curl);
                    curl_close($curl);

                    $geocodeData = json_decode($geocodeResponse, true);
                    $city = null;

                    if (!json_last_error() && !empty($geocodeData['results'][0]['address_components'])) {
                        $matchedCity = null;
                        $country = null;
                    
                        foreach ($geocodeData['results'][0]['address_components'] as $component) {
                            if (in_array('country', $component['types'])) {
                                $country = $component['long_name'] ?? null;
                            }
                            if($country == 'Singapore'){
                                $matchedCity = 'Singapore';
                            }
                            else{
                                // Primary: locality
                                if (in_array('locality', $component['types']) && !empty($component['long_name'])) {
                                    $matchedCity = $component['long_name'];
                                }
                        
                                // Fallback 1: administrative_area_level_2
                                if (!$matchedCity && in_array('administrative_area_level_2', $component['types']) && !empty($component['long_name'])) {
                                    $matchedCity = $component['long_name'];
                                }
                        
                                // Fallback 2: postal_town
                                if (!$matchedCity && in_array('postal_town', $component['types']) && !empty($component['long_name'])) {
                                    $matchedCity = $component['long_name'];
                                }
                            }
                            
                        }
                    }
                    
                    if (!$country) {
                        $country = "Unknown Country";
                    }
                    if (!$matchedCity) {
                        $matchedCity = "Unknown City";
                    }
        
                    if ($distanceInKM <= 10) {
                        $dayColumn = 'cost_per_km_below_10';
                        $nightColumn = 'night_cost_per_km_below_10';
                        $sharableDayColumn = 'sharable_cost_per_km_below_10';
                        $sharableNightColumn = 'sharable_night_cost_per_km_below_10';
                    } elseif ($distanceInKM > 10 && $distanceInKM <= 25) {
                        $dayColumn = 'cost_per_km_10_to_25';
                        $nightColumn = 'night_cost_per_km_10_to_25';
                        $sharableDayColumn = 'sharable_cost_per_km_10_to_25';
                        $sharableNightColumn = 'sharable_night_cost_per_km_10_to_25';
                    } else {
                        $dayColumn = 'cost_per_km_above_25';
                        $nightColumn = 'night_cost_per_km_above_25';
                        $sharableDayColumn = 'sharable_cost_per_km_above_25';
                        $sharableNightColumn = 'sharable_night_cost_per_km_above_25';
                    }

                    $cityDetails = OperationalCountry::where('name', $country)->where('city', $matchedCity)->first();
                    if(!$cityDetails){
                        return response()->json(['error' => 'Please add this city details in operational cities.',
                        'message' => $matchedCity, 'Address for lat-long' => $startAddress, 'geocode' => $geocodeData, 'geocode address' => $geocodeData['results'][0]['address_components']], 404);
                    }
                    $dmcs = User::where('role_id', 11)
                        ->where('country', $country)
                        ->get();
                    
                    $dmcIds = $dmcs->pluck('userId'); // Get list of DMC user IDs
                    $vehicles_row = Vehicle::whereIn('dmc_id', $dmcIds)
                        ->where('city', $matchedCity)
                        ->whereNotNull('driver_id')
                        ->skip($start)
                        ->take($limit)
                        ->get();
                    if (!$vehicles_row) {
                        return response()->json(['error' => 'Not Getting vehicles for this city'], 404);
                    }
                    $dmcDayPrice=null;
                    $dmcNightPrice=null;
                    $dmc_dmc_id = 0;
                    $travclicks_id = 0;
                    
                $vehicleList = [];
                    foreach ($vehicles_row as $vehicle) {
                        $basePrice = $vehicle->base_price;
                        $night_start_time = $cityDetails->night_start_time;
                        $night_end_time = $cityDetails->night_end_time;
                        $isNight = false;

                        $check_country = Country::whereRaw('LOWER(name) = ?', [strtolower($country)])->first();
                        $country_tax = $check_country->tax_percentage ?? 0;

                        if ($night_start_time < $night_end_time) {
                            // Range does not cross midnight
                            $isNight = ($time >= $night_start_time && $time <= $night_end_time);
                        } else {
                            // Range crosses midnight (e.g., 22:00 to 06:00)
                            $isNight = ($time >= $night_start_time || $time <= $night_end_time);
                        }
                        if($isNight){
                            $private_price = $vehicle->$nightColumn;
                            $sharable_price = $vehicle->$sharableNightColumn;
                        }
                        else{
                            $private_price = $vehicle->$dayColumn;
                            $sharable_price = $vehicle->$sharableDayColumn;
                        }
        
                        // Fetch DMC Vehicle price
                        $dmcVehicles = $vehicle->where('dmc_id', $dmc_id);
                        $dmcVehicle = $dmcVehicles->first();
                        $dmc_price = 0;
                        $trav_privatePrice = 0;
                        $trav_sharablePrice = 0;
                        $travclicks_id = 0;
                        $privatePrice = 0;
                        $sharablePrice = 0;
                        $dmc_dmc_id = 0;
                        
                        if ($vehicle->dmc_id == $dmc_id) {
                            $dmc_result = CommonHelper::calculateDmcModePricehotel(
                            $private_price, $dmc_id, $vehicle->vehicle_name, 'vehicle',$vehicle->city);
                            $privatePrice = $dmc_result[0] ?? 0;

                            $sharable_price = CommonHelper::calculateDmcModePricehotel(
                            $sharable_price, $dmc_id, $vehicle->vehicle_name, 'vehicle',$matchedCity);
                            $sharablePrice = $sharable_price[0] ?? 0;
                            $dmc_dmc_id = $sharable_price[1] ?? 0;
                        }
                        elseif($agent){
                            $dmc = User::where('userId', $vehicle->dmc_id)->first();
                            if ($dmc) {
                                $sharable_price = (float) $sharable_price;
                                $markup_value = is_numeric($dmc->markup_price) ? $dmc->markup_price : 0;
                                $private_dmc_markup = ($dmc->markup_type == 0) ? $markup_value : ($private_price * $markup_value / 100);
                                $shared_dmc_markup = ($dmc->markup_type == 0) ? $markup_value : ($sharable_price * $markup_value / 100);
                                $travclicks_id = $vehicle->dmc_id;
                            }
                            $trav_privatePrice = ($private_price + $private_dmc_markup) ?? 0;
                            $trav_sharablePrice = ($sharable_price + $shared_dmc_markup) ?? 0;
                            
                        }
                        
                        $vehicleList[] = [
                            
                            'id' => $vehicle->vehicle_id,
                            'vehicle_name' => $vehicle->vehicle_name,
                            'vehicle_type' => $vehicle->vehicle_type,
                            'vehicle_model' => $vehicle->vehicle_model,
                            'model_year' => $vehicle->model_year,
                            'description' => $vehicle->description,
                            'seating_capacity' => $vehicle->seating_capacity,
                            'image' => $vehicle->image,
                            'sharable' => 1,
                            'dmc_private_price' => $privatePrice > 0 ? $basePrice + ($privatePrice*$distanceInKM) : 0,
                            'dmc_sharable_price' => $sharablePrice > 0 ? $basePrice + ($sharablePrice*$distanceInKM) : 0,
                            'dmc_id' => $dmc_dmc_id,
                            'trav_private_price' => 0,
                            'trav_sharable_price' => 0,
                            'travclicks_dmc_id' => 0,
                            'night_start_time' => $cityDetails->night_start_time,
                            'night_end_time' => $cityDetails->night_end_time,
                            'city' => $vehicle->city,
                            'country' => $country,
                            'tax_percentage' => $country_tax,
                        ];
                    }
                return response()->json($vehicleList);    
            }
        //If only pickup lat long is found
        else if($pickup && !$dropoff){
            $url = $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$pickup['lat']},{$pickup['lng']}&key={$apiKey}";
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $json_response = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            
            $response = json_decode($json_response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $error = array("error" => "Error fetching address. Please retry.");
                echo json_encode($error);
                exit;
            }

            //geocode api for getting city
            $pickupLat = $pickup['lat'];
            $pickupLng = $pickup['lng'];
            $geocodeUrl = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$pickupLat},{$pickupLng}&key={$apiKey}";

            $curl = curl_init($geocodeUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $geocodeResponse = curl_exec($curl);
            curl_close($curl);

            $geocodeData = json_decode($geocodeResponse, true);
            $city = null;
            if (!json_last_error() && !empty($geocodeData['results'][0]['address_components'])) {
                $matchedCity = null;
                $country = null;
            
                foreach ($geocodeData['results'][0]['address_components'] as $component) {
                    if (in_array('country', $component['types'])) {
                        $country = $component['long_name'] ?? null;
                    }
                    if($country == 'Singapore'){
                        $matchedCity = 'Singapore';
                    }
                    else{
                        // Primary: locality
                        if (in_array('locality', $component['types']) && !empty($component['long_name'])) {
                            $matchedCity = $component['long_name'];
                        }
                
                        // Fallback 1: administrative_area_level_2
                        if (!$matchedCity && in_array('administrative_area_level_2', $component['types']) && !empty($component['long_name'])) {
                            $matchedCity = $component['long_name'];
                        }
                
                        // Fallback 2: postal_town
                        if (!$matchedCity && in_array('postal_town', $component['types']) && !empty($component['long_name'])) {
                            $matchedCity = $component['long_name'];
                        }
                    }
                    
                }
            }
            if (!$country) {
                $country = "Unknown Country";
            }
            if (!$matchedCity) {
                $matchedCity = "Unknown City";
            }
            $cityDetails = OperationalCountry::where('name', $country)->where('city', $matchedCity)->first();
            if(!$cityDetails){
                return response()->json(['error' => 'Please add this city details in operational cities.',
                        'message' => $matchedCity , 'geocode' => $geocodeData, 'geocode address' => $geocodeData['results'][0]['address_components']], 404);
            }
            $dmcs = User::where('role_id', 11)
                ->where('country', $country)
                ->get();
            $dmcIds = $dmcs->pluck('userId'); // Get list of DMC user IDs
            $vehicles_row = Vehicle::whereIn('dmc_id', $dmcIds)
                ->where('city', $matchedCity)
                ->whereNotNull('driver_id')
                ->skip($start)
                ->take($limit)
                ->get();
            if (!$vehicles_row) {
                return response()->json(['error' => 'Not Getting vehicles for this city'], 404);
            }
                if (!$dmc_id || $dmc_id == 0) {
                    return response()->json(['error' => 'DMC not found for Sales Manager'], 404);
                }
                $vehicleList = [];
                if ($vehicles_row->isNotEmpty()) {
                    foreach ($vehicles_row as $vehicle) {
                        $basePrice = $vehicle->base_price;
                        $night_start_time = $cityDetails->night_start_time;
                        $night_end_time = $cityDetails->night_end_time;
                        $base_price = $vehicle->cost_per_hour;
                        $base_price_night = $vehicle->night_cost_per_hour;

                        $check_country = Country::whereRaw('LOWER(name) = ?', [strtolower($country)])->first();
                        $country_tax = $check_country->tax_percentage ?? 0;

                        $isNight = false;
                        if ($night_start_time < $night_end_time) {
                            // Range does not cross midnight
                            $isNight = ($time >= $night_start_time && $time <= $night_end_time);
                        } else {
                            // Range crosses midnight (e.g., 22:00 to 06:00)
                            $isNight = ($time >= $night_start_time || $time <= $night_end_time);
                        }
                        if($isNight){
                            $private_price = $vehicle->night_cost_per_hour;
                            $sharable_price = $vehicle->sharable_night_cost_per_hour;
                        }
                        else{
                            $private_price = $vehicle->cost_per_hour;
                            $sharable_price = $vehicle->sharable_cost_per_hour;
                        }
                        $trav_privatePrice = 0;
                        $trav_sharablePrice = 0;
                        $travclicks_id = 0;
                        $dmcPrivatePrice = 0;
                        $dmcSharablePrice = 0;
                        $dmc_dmc_id = 0;
                        // Fetch DMC Vehicle price
                        if ($vehicle->dmc_id == $dmc_id){
                            $dmc_result = CommonHelper::calculateDmcModePricehotel(
                                $private_price, $dmc_id, $vehicle->vehicle_name, 'vehicle',$vehicle->city);
                            $dmcPrivatePrice = $dmc_result[0] ?? 0;
                            $dmc_night_price = CommonHelper::calculateDmcModePricehotel(
                                $sharable_price, $dmc_id, $vehicle->vehicle_name, 'vehicle',$vehicle->city);
                            $dmcSharablePrice = $dmc_night_price[0] ?? 0;
                            $dmc_dmc_id = $dmc_night_price[1] ?? 0;
                        }
        
                        else{
                            list($trav_privatePrice) = CommonHelper::CalculatePriceDetails(
                                $private_price, $vehicle->dmc_id);
                            list($trav_sharablePrice) = CommonHelper::CalculatePriceDetails($sharable_price, $vehicle->dmc_id);
                            $travclicks_id = $vehicle->dmc_id;
                        }
                            $vehicleList[] = [
                            'id' => $vehicle->vehicle_id,
                            'vehicle_name' => $vehicle->vehicle_name,
                            'vehicle_type' => $vehicle->vehicle_type,
                            'vehicle_model' => $vehicle->vehicle_model,
                            'model_year' => $vehicle->model_year,
                            'description' => $vehicle->description,
                            'seating_capacity' => $vehicle->seating_capacity,
                            'image' => $vehicle->image,
                            'sharable' => 1,
                            'dmc_private_price' => $dmcPrivatePrice > 0 ? $basePrice + $dmcPrivatePrice : 0,
                            'dmc_sharable_price' => $dmcSharablePrice > 0 ? $basePrice + $dmcSharablePrice : 0,
                            'dmc_id' => $dmc_dmc_id,
                            'trav_private_price' => 0,
                            'trav_sharable_price' => 0,
                            'travclicks_dmc_id' => 0,
                            'night_start_time' => $cityDetails->night_start_time,
                            'night_end_time' => $cityDetails->night_end_time,
                            'city' => $vehicle->city,
                            'country' => $country,
                            'tax_percentage' => $country_tax,
                        ];
                    }
                }
                return response()->json($vehicleList);
        }
    }

    //Vehicles Details
    public function vehicleDetails(Request $request)
    {
        $mode = $request->mode;
        $get_dmc_id = $request->dmc_id;
        $agentId = $request->header('agent-id');
        $type = $request->type;
        $pickup_id = $request->from_zone_id;
        $drop_id = $request->to_zone_id;
        $vehicle_id = $request->vehicle_id;
        $pickup_type = $request->pickup_type;
        $drop_type = $request->drop_type;
                        
        if (!$vehicle_id) {
            return response()->json(['error' => 'Vehicle Id not found.'], 404);
        }
        $vehicle = Vehicle::where('vehicle_id', $vehicle_id)->where('dmc_id', $get_dmc_id)->first();
        if(!$vehicle){
            return response()->json(['message' => 'Vehicle Not Found!'], 400);
        }

        if($type && $type == 'zone'){
            $pickup = null;
            $drop = null;
            $from_zone_id = null;
            $to_zone_id = null;
            $vehicleList = [];
            // Determine from_zone_id (pickup)
            switch ($pickup_type) {
                case 'hotel':
                    $pickup = Hotel::where('hotel_unique_id', $pickup_id)->first();
                    $from_zone_id = $pickup->zone_id ?? null;
                    break;
                case 'attraction':
                    $pickup = Attraction::where('attraction_id', $pickup_id)->first();
                    $from_zone_id = $pickup->zone_id ?? null;
                    break;
                case 'restaurant':
                    $pickup = Restaurant::where('restaurant_id', $pickup_id)->first();
                    $from_zone_id = $pickup->zone_id ?? null;
                    break;
                case 'port':
                    // port pickup uses zone id directly from pickup_id (assuming it's already a zone_id)
                    $from_zone_id = $pickup_id;
                    break;
                default:
                    // return response()->json(['success' => false, 'message' => 'Invalid pickup type.']);
                    return response()->json($vehicleList);
            }

            // Determine to_zone_id (drop)
            switch ($drop_type) {
                case 'hotel':
                    $drop = Hotel::where('hotel_unique_id', $drop_id)->first();
                    $to_zone_id = $drop->zone_id ?? null;
                    break;
                case 'attraction':
                    $drop = Attraction::where('attraction_id', $drop_id)->first();
                    $to_zone_id = $drop->zone_id ?? null;
                    break;
                case 'restaurant':
                    $drop = Restaurant::where('restaurant_id', $drop_id)->first();
                    $to_zone_id = $drop->zone_id ?? null;
                    break;
                case 'port':
                    $to_zone_id = $drop_id;
                    break;
                default:
                    // return response()->json(['success' => false, 'message' => 'Invalid drop type.']);
                    return response()->json($vehicleList);
            }
            $zone_price = VehicleZoneMapping::where('vehicle_id', $vehicle->vehicle_id)
                ->where('from_zone_id', $from_zone_id)
                ->where('to_zone_id', $to_zone_id)
                ->first();
                
            if (!$zone_price) {
                $zone_price = VehicleZoneMapping::where('vehicle_id', $vehicle->vehicle_id)
                    ->where('to_zone_id', $from_zone_id)
                    ->where('from_zone_id', $to_zone_id)
                    ->first();
            }
            $private_price = 0;
            $shared_price = 0;
            if ($zone_price) {  
                    $private_price = $zone_price->private_price;
                    $shared_price = $zone_price->shared_price;
            }
            
            return [
                'id' => $vehicle->vehicle_id,
                'vehicle_name' => $vehicle->vehicle_name,
                'vehicle_type' => $vehicle->vehicle_type,
                'vehicle_model' => $vehicle->vehicle_model,
                'model_year' => $vehicle->model_year,
                'description' => $vehicle->description,
                'seating_capacity' => $vehicle->seating_capacity,
                'image' => $vehicle->image,
                'sharable' => $vehicle->sharable,
                'private_price' => $private_price,
                'shared_price' => $shared_price,
                'city' => $vehicle->city,
                'country' =>$request->country,
                'to_zone_id' => $to_zone_id,
                'from_zone_id' => $from_zone_id,
                'dmc_id' => $get_dmc_id,
            ];
        }

            if(!$agentId){
                return response()->json(['error' => 'Agent not found.'], 404);
            }
            $agent = Agent::where('agent_id', $agentId)->first();
            $salesManagerId = $agent->sales_manager_dmc;
            $dmc_id = null;
            if ($agent) {
                switch ($agent->role_id) {
                    case 11: // Agent is a DMC
                        $dmc_id = $agent->sales_manager_dmc; // Assuming `userId` in agent or fallback to agent_id
                        break;
                        case 33: 
                        case 128: 
                        case 129: 
                        case 130: 
                        case 134: 
                        case 135: 
                        case 136: 
                        case 138: // Sales Head
                        $salesManagerId = $agent->sales_manager_dmc;
                             $saleshead_dmc = User::where('userId', $agent->sales_manager_dmc)->first(); // SH
                            if ( $saleshead_dmc) {
                                $dmc_users = User::where('userId',  $saleshead_dmc->created_by)->first(); // DMC
                                if ($dmc_users && $dmc_users->role_id == 11) {
                                    $dmc_id = $dmc_users->userId;
                                }
                            }
                        break;
                    case 12:
                    case 37: // Sales Manager
                        $salesManagerId = $agent->sales_manager_dmc;
                        $salesmng_dmc= User::where('userId', $agent->sales_manager_dmc)->first(); // SM
                        
                        if ($salesmng_dmc) {
                             $saleshead_dmc = User::where('userId', $salesmng_dmc->created_by)->first(); // SH
                            if ( $saleshead_dmc) {
                                $dmc_users = User::where('userId',  $saleshead_dmc->created_by)->first(); // DMC
                                if ($dmc_users && $dmc_users->role_id == 11) {
                                    $dmc_id = $dmc_users->userId;
                                }
                            }
                        }
                        break;
                    case 38: // Assistant Manager
                        $salesManagerId = $agent->sales_manager_dmc;
                        $asmng_dmc = User::where('userId', $agent->sales_manager_dmc)->first(); // SM
                        if($asmng_dmc){
                            $salesmng_dmc = User::where('userId', $asmng_dmc->created_by)->first(); // SH
                        }
                        if ($salesmng_dmc) {
                             $saleshead_dmc = User::where('userId', $salesmng_dmc->created_by)->first(); // SH
                            if ( $saleshead_dmc) {
                                $dmc_users = User::where('userId',  $saleshead_dmc->created_by)->first(); // DMC
                                if ($dmc_users && $dmc_users->role_id == 11) {
                                    $dmc_id = $dmc_users->userId;
                                }
                            }
                        }
                        break;
                }
            }
            if (!$dmc_id) {
                return response()->json(['message' => 'DMC Not Found!'], 400);
            }



        $rawTime = trim($request->input('time'), " \t\n\r\0\x0B\"'"); // removes extra whitespace and quotes
        $time = Carbon::createFromFormat('h:i A', $rawTime)->format('H:i:s');

        $apiKey = "AIzaSyCLzISM9kkNCKKmQs7BcpSll4emFw1yicw";
        $pickup = json_decode($request->query('pickup'), true);
        $dropoff = json_decode($request->query('dropoff'), true);
        $date = $request->query('date');
        $city = $request->city;
        $country = $request->country;
        $base_price = $vehicle->base_price;
        if($pickup && $dropoff){
            $url = "https://maps.googleapis.com/maps/api/directions/json?origin={$pickup['lat']},{$pickup['lng']}&destination={$dropoff['lat']},{$dropoff['lng']}&key=" . $apiKey;
                $curl = curl_init($url);
                curl_setopt($curl, CURLOPT_HEADER, false);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HTTPGET, true);
                curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, false);
                $json_response = curl_exec($curl);
                $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);
                $response = json_decode($json_response, true);
                if(json_last_error()){
                    $error = array("error"=>"Error computing distance. Please retry.");
                    echo json_encode($error); //database error
                    exit;
                }
                $startAddress = $response['routes'][0]['legs'][0]['start_address'];
                $distance = (($response['routes'][0]['legs'][0]['distance']['value']));
                $distanceInKM = $distance/1000;
                
                if ($distanceInKM <= 10) {
                    $dayColumn = 'cost_per_km_below_10';
                    $nightColumn = 'night_cost_per_km_below_10';
                    $sharableDayColumn = 'sharable_cost_per_km_below_10';
                    $sharableNightColumn = 'sharable_cost_per_km_below_10';

                } elseif ($distanceInKM > 10 && $distanceInKM <= 25) {
                    $dayColumn = 'cost_per_km_10_to_25';
                    $nightColumn = 'night_cost_per_km_10_to_25';
                    $sharableDayColumn = 'sharable_cost_per_km_10_to_25';
                    $sharableNightColumn = 'sharable_night_cost_per_km_10_to_25';
                } else {
                    $dayColumn = 'cost_per_km_above_25';
                    $nightColumn = 'night_cost_per_km_above_25';
                    $sharableDayColumn = 'sharable_cost_per_km_above_25';
                    $sharableNightColumn = 'sharable_night_cost_per_km_above_25';
                }

                $cityDetails = OperationalCountry::where('name', $country)->where('city', $city)->first();

                if(!$cityDetails){
                    return response()->json(['error' => 'Please add this city details in operational cities.',
                        'city' => $city], 404);
                }
                else{
                    $night_start_time = $cityDetails->night_start_time;
                    $night_end_time = $cityDetails->night_end_time;
                }
                $isNight = false;

                if ($night_start_time < $night_end_time) {
                    // Range does not cross midnight
                    $isNight = ($time >= $night_start_time && $time <= $night_end_time);
                } else {
                    // Range crosses midnight (e.g., 22:00 to 06:00)
                    $isNight = ($time >= $night_start_time || $time <= $night_end_time);
                }
                if($isNight){
                    $private_price = $vehicle->$nightColumn;
                    $sharable_price = $vehicle->$sharableNightColumn;
                }
                else{
                    $private_price = $vehicle->$dayColumn;
                    $sharable_price = $vehicle->$sharableDayColumn;
                }

                $country_tax = 0;
                $check_country = Country::whereRaw('LOWER(name) = ?', [strtolower($country)])->first();
                $country_tax = $check_country->tax_percentage ?? 0;

                if($vehicle->dmc_id == $dmc_id){
                    list($private_price, $dmc_dmc_id) = CommonHelper::calculateDmcModePricehotel(
                        $private_price, $dmc_id, $vehicle->vehicle_name, 'vehicle',$vehicle->city);
                    list($sharable_price, $dmc_dmc_id) = CommonHelper::calculateDmcModePricehotel(
                        $sharable_price, $dmc_id, $vehicle->vehicle_name, 'vehicle',$vehicle->city);
                }
                
                else{
                    list($private_price) = CommonHelper::CalculatePriceDetails(
                        $private_price, $get_dmc_id);
                    list($sharable_price) = CommonHelper::CalculatePriceDetails(
                        $sharable_price, $get_dmc_id);
                }
                $prices = (object) [
                    'privatePrice' => round((float)$private_price * $distanceInKM, 2) > 0 
                        ? round((float)$private_price * $distanceInKM, 2) + $base_price 
                        : 0,
                    'sharablePrice' => round((float)$sharable_price * $distanceInKM, 2) > 0 
                        ? round((float)$sharable_price * $distanceInKM, 2) + $base_price 
                        : 0,
                    'dmc_id' => $get_dmc_id,
                ];
                return [
                    'id' => $vehicle->vehicle_id,
                    'vehicle_name' => $vehicle->vehicle_name,
                    'vehicle_type' => $vehicle->vehicle_type,
                    'vehicle_model' => $vehicle->vehicle_model,
                    'model_year' => $vehicle->model_year,
                    'description' => $vehicle->description,
                    'seating_capacity' => $vehicle->seating_capacity,
                    'image' => $vehicle->image,
                    '$distanceInKM' => $distanceInKM,
                    'base_price' => $base_price,
                    'prices' => $prices,
                    'tax_percentage' => $country_tax,
                    'sharable' => 1,
                    'night_start_time' =>$night_start_time,
                    'night_end_time' =>$night_end_time,
                    'city' => $vehicle->city,
                    'country' => $country,
                ];
        }
        //If only pickup lat long is found
        else if($pickup && !$dropoff){
            $url = $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$pickup['lat']},{$pickup['lng']}&key={$apiKey}";
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $json_response = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            
            $response = json_decode($json_response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $error = array("error" => "Error fetching address. Please retry.");
                echo json_encode($error);
                exit;
            }
            $completeAddress = null;
            $cityName = null;
            $countryName = null;

                // Search for country in `name` column
                $cityDetails = OperationalCountry::where('name', $country)->where('city', $city)->first();
                
                $dmcPrice=null;
                $night_start_time = null;
                $night_end_time = null;
                $base_price = null;
                $base_night_price = null;

                if(!$cityDetails){
                    return response()->json(['message' => 'It seems service is not available in  this city!', 'country'=>$country, 'city'=>$city], 409);
                }
                else{
                    $night_start_time = $cityDetails->night_start_time;
                    $night_end_time = $cityDetails->night_end_time;
                }
                $country_tax = 0;
                $check_country = Country::whereRaw('LOWER(name) = ?', [strtolower($country)])->first();
                $country_tax = $check_country->tax_percentage ?? 0;

                $day_private_price = $vehicle->cost_per_hour;
                $day_sharable_price = $vehicle->sharable_cost_per_hour;
                $night_private_price = $vehicle->night_cost_per_hour;
                $night_sharable_price = $vehicle->sharable_night_cost_per_hour;

            if($vehicle->dmc_id == $dmc_id){
                list($day_private_price, $dmc_dmc_id) = CommonHelper::calculateDmcModePricehotel(
                    $day_private_price, $dmc_id, $vehicle->vehicle_name, 'vehicle',$vehicle->city);
                list($day_sharable_price, $dmc_dmc_id) = CommonHelper::calculateDmcModePricehotel(
                    $day_sharable_price, $dmc_id, $vehicle->vehicle_name, 'vehicle',$vehicle->city);
                list($night_private_price, $dmc_dmc_id) = CommonHelper::calculateDmcModePricehotel(
                    $night_private_price, $dmc_id, $vehicle->vehicle_name, 'vehicle',$vehicle->city);
                list($night_sharable_price, $dmc_dmc_id) = CommonHelper::calculateDmcModePricehotel(
                    $night_sharable_price, $dmc_id, $vehicle->vehicle_name, 'vehicle',$vehicle->city);
            }
            else{
                $dmc = User::where('userId', $get_dmc_id)->first();
                if ($dmc) {
                    $markup_value = is_numeric($dmc->markup_price) ? $dmc->markup_price : 0;
                    $dmc_markup = ($dmc->markup_type == 0) ? $markup_value : ($base_price * $markup_value / 100);
                }
                else{
                    return response()->json(['message' => 'Travclicks dmc_id not found!'], 404);
                }
                $day_private_price = ($day_private_price + $dmc_markup) ?? 0;
                $day_sharable_price = ($day_sharable_price + $dmc_markup) ?? 0;
                $night_private_price = ($night_private_price + $dmc_markup) ?? 0;
                $night_sharable_price = ($night_sharable_price + $dmc_markup) ?? 0;
            }
            $prices = (object) [
                'day_private_price' => round((float)$day_private_price, 2),
                'day_sharable_price' => round((float)$day_sharable_price, 2),
                'night_private_price' => round((float)$night_private_price, 2),
                'night_sharable_price' => round((float)$night_sharable_price, 2),
                'private_day_base_price' => $vehicle->base_price,
                'private_night_base_price' => $vehicle->night_base_price,
                'sharable_day_base_price' => $vehicle->sharable_base_price,
                'sharable_night_base_price' => $vehicle->sharable_night_base_price,
                'dmc_id' => $get_dmc_id,
            ];
            return [
                'id' => $vehicle->vehicle_id,
                'vehicle_name' => $vehicle->vehicle_name,
                'vehicle_type' => $vehicle->vehicle_type,
                'vehicle_model' => $vehicle->vehicle_model,
                'model_year' => $vehicle->model_year,
                'description' => $vehicle->description,
                'seating_capacity' => $vehicle->seating_capacity,
                'image' => $vehicle->image,
                'prices' => $prices,
                'tax_percentage' => $country_tax,
                'sharable' => 1,
                'night_start_time' =>$night_start_time,
                'night_end_time' =>$night_end_time,
                'city' => $vehicle->city,
                'country' => $country,
            ];
        }
    }
}
