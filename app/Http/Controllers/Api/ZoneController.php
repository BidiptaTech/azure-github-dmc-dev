<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use App\Models\Hotel;
use App\Models\Attraction;
use App\Models\Restaurant;
use App\Models\Port;
use App\Models\City;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\VehicleZoneMapping;
class ZoneController extends Controller
{
    public function zone_lists(Request $request)
    {
        $agent = auth()->user()->sales_manager_dmc;
        if(!$agent){
        $agent = auth()->user()->userId;
        }
        $user = User::where('userId', $agent)->first();
        $id = $request->id;
        $type = $request->type;
        if (!$id || !$type) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter both id and type.',
            ]);
        }
        if ($user) {
            switch ($user->role_id) {
                case 11: // Agent is a DMC
                    $dmc_id = $user->userId; // Assuming `userId` in agent or fallback to agent_id
                    $dmc_users = User::where('userId', $dmc_id)->first();
                    break;
                case 33: 
                case 128: 
                case 129: 
                case 130: 
                case 134: 
                case 135: 
                case 136: 
                case 138: // Sales Head
                    $salesManagerId = $user->userId;
                        $saleshead_dmc = User::where('userId', $user->userId)->first(); // SH
                        if ( $saleshead_dmc) {
                            $dmc_users = User::where('userId',  $saleshead_dmc->created_by)->first(); // DMC
                            if ($dmc_users && $dmc_users->role_id == 11) {
                                $dmc_id = $dmc_users->userId;
                            }
                        }else{
                            $dmc_users = User::where('userId',  $user->created_by)->first(); // DMC
                            $dmc_id = $dmc_users->userId;
                        }
                    break;
                case 12:
                case 37: // Sales Manager
                    $salesManagerId = $user->userId;
                    $salesmng_dmc= User::where('userId', $user->userId)->first(); // SM
                    
                    if ($salesmng_dmc) {
                        $saleshead_dmc = User::where('userId', $salesmng_dmc->created_by)->first(); // SH
                        if ( $saleshead_dmc) {
                            $dmc_users = User::where('userId',  $saleshead_dmc->created_by)->first(); // DMC
                            if ($dmc_users && $dmc_users->role_id == 11) {
                                $dmc_id = $dmc_users->userId;
                            }
                        }
                    }else{
                        $saleshead_dmc = User::where('userId', $user->created_by)->first(); // SH
                        if ( $saleshead_dmc) {
                            $dmc_users = User::where('userId',  $saleshead_dmc->created_by)->first(); // DMC
                            if ($dmc_users && $dmc_users->role_id == 11) {
                                $dmc_id = $dmc_users->userId;
                            }
                        }
                    }
                    break;
                case 38: // Assistant Manager
                    $salesManagerId = $user->userId;
                    $asmng_dmc = User::where('userId', $user->userId)->first(); // SM
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
        switch ($type) {
            case 'hotel':
                $city = Hotel::where('hotel_unique_id', $id)->value('city');
        
                $hotels = Hotel::orderBy('name', 'asc')->whereJsonContains('dmc_id', $dmc_id)->where('city', $city)->get();
                $attractions = Attraction::orderBy('attraction_id', 'desc')->whereJsonContains('dmc_id', $dmc_id)->where('location', $city)->get();
                $restaurants = Restaurant::orderBy('restaurant_id', 'desc')->whereJsonContains('dmc_id', $dmc_id)->where('city', $city)->get();
                $port_city = City::where('name', $city)->value('city_id');
                $ports = Port::orderBy('port_id', 'desc')->where('city_id', $port_city)->get();
                $items = [
                    'hotels' => $hotels,
                    'attractions' => $attractions,
                    'restaurants' => $restaurants,
                    'ports' => $ports,
                ];
                break;
        
            case 'attraction':
                $city = Attraction::where('attraction_id', $id)->value('location');
        
                $attractions = Attraction::orderBy('attraction_id', 'desc')->whereJsonContains('dmc_id', $dmc_id)->where('location', $city)->get();
                $hotels = Hotel::orderBy('name', 'asc')->whereJsonContains('dmc_id', $dmc_id)->where('city', $city)->get();
                $restaurants = Restaurant::orderBy('restaurant_id', 'desc')->whereJsonContains('dmc_id', $dmc_id)->where('city', $city)->get();
        
                $items = [
                    'attractions' => $attractions,
                    'hotels' => $hotels,
                    'restaurants' => $restaurants,
                ];
                break;
        
            case 'restaurant':
                $city = Restaurant::where('restaurant_id', $id)->value('city');
        
                $hotels = Hotel::orderBy('name', 'asc')->whereJsonContains('dmc_id', $dmc_id)->where('city', $city)->get();
                $attractions = Attraction::orderBy('attraction_id', 'desc')->whereJsonContains('dmc_id', $dmc_id)->where('location', $city)->get();
                $restaurants = Restaurant::orderBy('restaurant_id', 'desc')->whereJsonContains('dmc_id', $dmc_id)->where('city', $city)->get();
                $port_city = City::where('name', $city)->value('city_id');
                $ports = Port::orderBy('port_id', 'desc')->where('city_id', $port_city)->get();
        
                $items = [
                    'hotels' => $hotels,
                    'attractions' => $attractions,
                    'restaurants' => $restaurants,
                    'ports' => $ports,
                ];
                break;
        
            case 'port':
                $port_city = Port::where('port_id', $id)->value('city_id');
                $ports = Port::orderBy('port_id', 'desc')->where('city_id', $port_city)->get();
                
                $port_city = Port::where('port_id', $id)->value('city_id');
                $city = City::where('city_id', $port_city)->first();
                $hotels = Hotel::orderBy('name', 'asc')->whereJsonContains('dmc_id', $dmc_id)->where('city', $city->name)->get();
                $attractions = Attraction::orderBy('attraction_id', 'desc')->whereJsonContains('dmc_id', $dmc_id)->where('location', $city->name)->get();
                $restaurants = Restaurant::orderBy('restaurant_id', 'desc')->whereJsonContains('dmc_id', $dmc_id)->where('city', $city->name)->get();
                $items = [
                    'hotels' => $hotels,
                    'ports' => $ports,
                    'attractions' => $attractions,
                    'restaurants' => $restaurants,
                ];
                break;
        
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid type provided.',
                ]);
        }
        
        // Return response with structured data
        return response()->json([
            'success' => true,
            'message' => 'Successful',
            'data' => $items,
        ]);
        
    }

    public function zonewisePrice(Request $request)
    {
        $drop_type = $request->drop_type;
        $drop_id = $request->drop_id;
        $pickup_type = $request->pickup_type;
        $pickup_id = $request->pickup_id;
        $dmc_id = $request->dmc_id;
        if (!$drop_type || !$drop_id || !$pickup_type || !$pickup_id) {
            return response()->json([
                'success' => false,
                'message' => 'Both pickup and drop type/id are required.',
            ]);
        }

        $pickup = null;
        $drop = null;
        $from_zone_id = null;
        $to_zone_id = null;

        // Determine from_zone_id (pickup)
        switch ($pickup_type) {
            case 'hotel':
                $pickup = Hotel::where('hotel_unique_id', $pickup_id)->first();
                // Use zone_assignments to get zone for the specified DMC
                $from_zone_id = $pickup ? $pickup->getZoneForDmc($dmc_id) : null;
                break;
            case 'attraction':
                $pickup = Attraction::where('attraction_id', $pickup_id)->first();
                // Use zone_assignments to get zone for the specified DMC
                $from_zone_id = $pickup ? $pickup->getZoneForDmc($dmc_id) : null;
                break;
            case 'restaurant':
                $pickup = Restaurant::where('restaurant_id', $pickup_id)->first();
                // Use zone_assignments to get zone for the specified DMC
                $from_zone_id = $pickup ? $pickup->getZoneForDmc($dmc_id) : null;
                break;
            case 'port':
                // port pickup uses zone id directly from pickup_id (assuming it's already a zone_id)
                $from_zone_id = $pickup_id;
                break;
            default:
                return response()->json(['success' => false, 'message' => 'Invalid pickup type.']);
        }

        // Determine to_zone_id (drop)
        switch ($drop_type) {
            case 'hotel':
                $drop = Hotel::where('hotel_unique_id', $drop_id)->first();
                // Use zone_assignments to get zone for the specified DMC
                $to_zone_id = $drop ? $drop->getZoneForDmc($dmc_id) : null;
                break;
            case 'attraction':
                $drop = Attraction::where('attraction_id', $drop_id)->first();
                // Use zone_assignments to get zone for the specified DMC
                $to_zone_id = $drop ? $drop->getZoneForDmc($dmc_id) : null;
                break;
            case 'restaurant':
                $drop = Restaurant::where('restaurant_id', $drop_id)->first();
                // Use zone_assignments to get zone for the specified DMC
                $to_zone_id = $drop ? $drop->getZoneForDmc($dmc_id) : null;
                break;
            default:
                return response()->json(['success' => false, 'message' => 'Invalid drop type.']);
        }
        if (!$from_zone_id || !$to_zone_id) {
            return response()->json([
                'success' => false,
                'message' => 'Zone information not found for pickup or drop.',
            ]);
        }

        $zone = VehicleZoneMapping::where('from_zone_id', $from_zone_id)
                                ->where('to_zone_id', $to_zone_id)
                                ->first();

        if (!$zone) {
            return response()->json([
                'success' => false,
                'message' => 'No zone mapping found between selected pickup and drop.',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Zone pricing found',
            'data' => [
                'private_price' => $zone->private_price,
                'shared_price' => $zone->shared_price,
            ],
        ]);
    }

    public function vehicleLists(Request $request)
    {
        $drop_type = $request->drop_type;
        $drop_id = $request->dropoffid;
        $pickup_type = $request->pickup_type;
        $pickup_id = $request->pickupid;
        $agent      = auth()->user();
        $dmcId = $request->dmc_id;
        if($agent->sales_manager_dmc){
            if(!$dmcId){
                return response()->json(['message' => 'DMC Id Not Found!'], 400);
            }
            $user = User::where('userId', $dmcId)->first();
        }
        else{
            $user = User::where('userId', $agent->userId)->first();
        }

        if(in_array($user->role_id, [11,33, 128, 129, 130, 134, 135, 136, 138, 37, 38]) && $agent->userId){
            if($user->role_id == 11){
                $dmcId = $user->userId;
            }
            elseif($user->role_id == 33 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
                $sales_head = User::where('userId', $user->userId)->first();
                $dmcId = $sales_head->created_by;
            }
            elseif($user->role_id == 37){
                $sales_manager = User::where('userId', $user->userId)->first();
                $sales_head = User::where('userId', $sales_manager->created_by)->first();
                $dmcId = $sales_head->created_by;
            }
            elseif($user->role_id == 38){
                $sales_manager = User::where('userId', $agent->created_by)->first();
                $sales_head = User::where('userId', $sales_manager->created_by)->first();
                $dmcId = $sales_head->created_by;
            }

            else{
                $dmcId = null;
            }
        }
        if(!$dmcId){
            return response()->json(['message' => 'DMC Not Found!'], 400);
        }

        if (!$drop_type || !$drop_id || !$pickup_type || !$pickup_id) {
            return response()->json([
                'success' => false,
                'message' => 'Both pickup and drop type/id are required.',
            ]);
        }

        $pickup = null;
        $drop = null;
        $from_zone_id = null;
        $to_zone_id = null;
        $country = null;
        $vehicleList = [];
        // Determine from_zone_id (pickup)
        switch ($pickup_type) {
            case 'hotel':
                $pickup = Hotel::where('hotel_unique_id', $pickup_id)->first();
                if(!$pickup){
                    return response()->json(['message' => 'Pickup Hotel Not Found!'], 400);
                }
                $country = $pickup->country;
                // Use zone_assignments to get zone for the determined DMC
                $from_zone_id = $pickup->getZoneForDmc($dmcId);
                break;
            case 'attraction':
                $pickup = Attraction::where('attraction_id', $pickup_id)->first();
                if(!$pickup){
                    return response()->json(['message' => 'Pickup Attraction Not Found!'], 400);
                }
                $country = $pickup->country;
                // Use zone_assignments to get zone for the determined DMC
                $from_zone_id = $pickup->getZoneForDmc($dmcId);
                break;
            case 'restaurant':
                $pickup = Restaurant::where('restaurant_id', $pickup_id)->first();
                
                if(!$pickup){
                    return response()->json(['message' => 'Pickup Restaurant Not Found!'], 400);
                }
                $country = $pickup->country;
                // Use zone_assignments to get zone for the determined DMC
                $from_zone_id = $pickup->getZoneForDmc($dmcId);
                break;
            case 'port':
                // port pickup uses zone id directly from pickup_id (assuming it's already a zone_id)
                $pickup = Port::where('port_id', $pickup_id)->first();
                if(!$pickup){
                    return response()->json(['message' => 'Pickup Port Not Found!'], 400);
                }
                $country = $pickup->country;
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
                if(!$drop){
                    return response()->json(['message' => 'Drop-off Hotel Not Found!'], 400);
                }
                $country = $drop->country;
                // Use zone_assignments to get zone for the determined DMC
                $to_zone_id = $drop->getZoneForDmc($dmcId);
                break;
            case 'attraction':
                $drop = Attraction::where('attraction_id', $drop_id)->first();
                if(!$drop){
                    return response()->json(['message' => 'Drop-off Attraction Not Found!'], 400);
                }
                $to_zone_id = $drop->getZoneForDmc($dmcId);
                break;
            case 'restaurant':
                $drop = Restaurant::where('restaurant_id', $drop_id)->first();
                if(!$drop){
                    return response()->json(['message' => 'Drop-off Restaurant Not Found!'], 400);
                }
                $country = $drop->country;
                // Use zone_assignments to get zone for the determined DMC
                $to_zone_id = $drop->getZoneForDmc($dmcId);
                break;
            case 'port':
                $drop = Port::where('port_id', $drop_id)->first();
                if(!$drop){
                    return response()->json(['message' => 'Drop-off Port Not Found!'], 400);
                }
                $country = $drop->country;
                $to_zone_id = $drop_id;
                break;
            default:
                // return response()->json(['success' => false, 'message' => 'Invalid drop type.']);
                return response()->json($vehicleList);
        }
        
        if (!$from_zone_id || !$to_zone_id) {
            return response()->json([
                'success' => false,
                'message' => 'Zone information not found for pickup or drop.',
            ]);
        }

        $vehicleIds = VehicleZoneMapping::where('from_zone_id', $from_zone_id)
        ->where('to_zone_id', $to_zone_id)
        ->pluck('vehicle_id')
        ->toArray();

        if(!$vehicleIds){
            $vehicleIds = VehicleZoneMapping::where('from_zone_id', $to_zone_id)
                ->where('to_zone_id', $from_zone_id)
                ->pluck('vehicle_id')
                ->toArray();
        }
        if(!$vehicleIds){
            return response()->json(['message' => 'No vehicles found!'], 400);
        }
        $vehicles = Vehicle::whereIn('vehicle_id', $vehicleIds)->where('dmc_id', $dmcId)->get();
        $vehicleList = [];

        foreach ($vehicles as $vehicle) {
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
            
            // Add null check to prevent errors
            $privatePrice = $zone_price ? $zone_price->private_price : 0;
            $sharablePrice = $zone_price ? $zone_price->shared_price : 0;
            
            $vehicleList[] = [
                'id' => $vehicle->vehicle_id,
                'dmc_id' => $vehicle->dmc_id,
                'vehicle_name' => $vehicle->vehicle_name,
                'vehicle_type' => $vehicle->vehicle_type,
                'vehicle_model' => $vehicle->vehicle_model,
                'model_year' => $vehicle->model_year,
                'description' => $vehicle->description,
                'seating_capacity' => $vehicle->seating_capacity,
                'image' => $vehicle->image,
                'sharable' => $vehicle->sharable,
                'dmc_private_price' => $privatePrice,
                'dmc_sharable_price' => $sharablePrice,
                'city' => $vehicle->city,
                'country' => $country,
            ];
        }
        return response()->json($vehicleList);
    }

}
