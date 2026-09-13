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
use App\Models\Agency;
use App\Models\VehicleZoneMapping;
class ZoneController extends Controller
{
    /**
     * The DMCs the caller may browse.
     *
     * An agent inherits them from their agency (agencies.dmc_id), which is the same
     * list the app renders from /get-dmcs and picks dmc_id from. Returns an empty
     * array for internal sales users, who are not agency scoped.
     */
    private function allowedDmcIds($account): array
    {
        $ids = [];

        if (!empty($account->agency_id)) {
            $agency = Agency::where('agency_id', $account->agency_id)->first();
            if ($agency) {
                $ids = $agency->getSelectedDmcIds();
            }
        }

        // Agents created before agencies existed carry their DMCs on the agent row.
        if (empty($ids) && !empty($account->dmc_id)) {
            $ids = is_array($account->dmc_id)
                ? $account->dmc_id
                : (json_decode((string) $account->dmc_id, true) ?: []);
        }

        return array_values(array_unique(array_map('intval', array_filter((array) $ids))));
    }

    /**
     * Every port in the country, so a transfer can start or end at any port
     * nationwide rather than only the ones in the anchor record's own city.
     * Falls back to the city's country when the anchor record has none stored.
     */
    private function portsForCountry(?string $country, ?string $cityName)
    {
        if (empty($country) && !empty($cityName)) {
            $country = City::where('name', $cityName)->value('country');
        }

        if (empty($country)) {
            return collect();
        }

        return Port::orderBy('port_id', 'desc')->where('country', $country)->get();
    }

    public function zone_lists(Request $request)
    {
        $id = $request->id;
        $type = $request->type;
        $dmc_id = (int) $request->dmc_id;

        if (!$id || !$type) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter both id and type.',
            ]);
        }

        if (!$dmc_id) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide dmc_id.',
            ]);
        }

        $allowedDmcIds = $this->allowedDmcIds(auth()->user());
        if ($allowedDmcIds && !in_array($dmc_id, $allowedDmcIds, true)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this DMC.',
            ], 403);
        }

        switch ($type) {
            case 'hotel':
                $hotel = Hotel::where('hotel_unique_id', $id)->first();
                $city = $hotel->city ?? null;
        
                $hotels = Hotel::orderBy('name', 'asc')->whereJsonContains('dmc_id', $dmc_id)->where('city', $city)->get();
                $attractions = Attraction::orderBy('attraction_id', 'desc')->whereJsonContains('dmc_id', $dmc_id)->where('location', $city)->get();
                $restaurants = Restaurant::orderBy('restaurant_id', 'desc')->whereJsonContains('dmc_id', $dmc_id)->where('city', $city)->get();
                $ports = $this->portsForCountry($hotel->country ?? null, $city);
                $items = [
                    'hotels' => $hotels,
                    'attractions' => $attractions,
                    'restaurants' => $restaurants,
                    'ports' => $ports,
                ];
                break;
        
            case 'attraction':
                $attraction = Attraction::where('attraction_id', $id)->first();
                $city = $attraction->location ?? null;
        
                $attractions = Attraction::orderBy('attraction_id', 'desc')->whereJsonContains('dmc_id', $dmc_id)->where('location', $city)->get();
                $hotels = Hotel::orderBy('name', 'asc')->whereJsonContains('dmc_id', $dmc_id)->where('city', $city)->get();
                $restaurants = Restaurant::orderBy('restaurant_id', 'desc')->whereJsonContains('dmc_id', $dmc_id)->where('city', $city)->get();
                $ports = $this->portsForCountry($attraction->country ?? null, $city);
        
                $items = [
                    'attractions' => $attractions,
                    'hotels' => $hotels,
                    'restaurants' => $restaurants,
                    'ports' => $ports,
                ];
                break;
        
            case 'restaurant':
                $restaurant = Restaurant::where('restaurant_id', $id)->first();
                $city = $restaurant->city ?? null;
        
                $hotels = Hotel::orderBy('name', 'asc')->whereJsonContains('dmc_id', $dmc_id)->where('city', $city)->get();
                $attractions = Attraction::orderBy('attraction_id', 'desc')->whereJsonContains('dmc_id', $dmc_id)->where('location', $city)->get();
                $restaurants = Restaurant::orderBy('restaurant_id', 'desc')->whereJsonContains('dmc_id', $dmc_id)->where('city', $city)->get();
                $ports = $this->portsForCountry($restaurant->country ?? null, $city);
        
                $items = [
                    'hotels' => $hotels,
                    'attractions' => $attractions,
                    'restaurants' => $restaurants,
                    'ports' => $ports,
                ];
                break;
        
            case 'port':
                $port = Port::where('port_id', $id)->first();
                $city = $port ? City::where('city_id', $port->city_id)->value('name') : null;
        
                $ports = $this->portsForCountry($port->country ?? null, $city);
                $hotels = Hotel::orderBy('name', 'asc')->whereJsonContains('dmc_id', $dmc_id)->where('city', $city)->get();
                $attractions = Attraction::orderBy('attraction_id', 'desc')->whereJsonContains('dmc_id', $dmc_id)->where('location', $city)->get();
                $restaurants = Restaurant::orderBy('restaurant_id', 'desc')->whereJsonContains('dmc_id', $dmc_id)->where('city', $city)->get();
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
