<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\City;
use App\Helpers\CommonHelper;
use App\Models\Hotel;
use App\Models\Attraction;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class ZoneController extends Controller
{
    /**
     * Resolve the DMC ID for the given user based on role hierarchy
     * This mirrors the conditions used in index() for filtering zones.
     */
    private function resolveDmcIdForUser(User $user)
    {
        // Direct DMC roles
        if ($user->role_id == 11 || $user->role_id == 20) {
            return $user->userId;
        }

        // Team roles directly under a DMC
        if ($user->role_id == 35 || in_array($user->role_id, [130, 132, 133, 135, 136, 137, 138])) {
            return $user->created_by;
        }

        // Roles under Product Head
        if ($user->role_id == 76 || $user->role_id == 139) {
            $productHead = User::where('userId', $user->created_by)->first();
            return $productHead ? $productHead->created_by : null;
        }

        // Roles under Product Manager → Product Head
        if ($user->role_id == 111 || $user->role_id == 140) {
            $productManager = User::where('userId', $user->created_by)->first();
            $productHead = $productManager ? User::where('userId', $productManager->created_by)->first() : null;
            return $productHead ? $productHead->created_by : null;
        }

        return null;
    }
    /**
     * Display a listing of zones.
     */
    public function index()
    {
        $user = Auth::user();
        if ($user->role_id == 4) {
            $dmc_ids = User::where('assistant_manager_id', $user->userId)->pluck('userId')->toArray();
            $zones = Zone::orderBy('updated_at', 'desc')->whereIn('dmc_id', $dmc_ids)->get();
        } elseif ($user->role_id == 3) {
            $zones = Zone::orderBy('updated_at', 'desc')->get();
        } elseif (in_array($user->role_id, [1, 2, 23])) {
            $zones = Zone::orderBy('updated_at', 'desc')->get();
        }
        elseif ($user->role_id == 10) {
            $dmc_ids = User::where('master_dmc_id', $user->userId)->get()->pluck('userId')->toArray();
            $zones = Zone::orderBy('updated_at', 'desc')->whereIn('dmc_id', $dmc_ids)->get();
        }
         elseif ($user->role_id == 11 || $user->role_id == 20) {
            $zones = Zone::orderBy('updated_at', 'desc')->where('dmc_id', $user->userId)->get();
        }
        elseif(in_array($user->role_id, [25, 62, 110])){
            if($user->role_id == 25){
                $master_dmc_id = $user->created_by;
            }
            elseif($user->role_id == 62){
                $product_head = User::where('userId', $user->created_by)->first();
                $master_dmc_id = $product_head->created_by;
            }
            elseif($user->role_id == 110){
                $product_manager = User::where('userId', $user->created_by)->first();
                $product_head = User::where('userId', $product_manager->created_by)->first();
                $master_dmc_id = $product_head->created_by;
            }
            
            $dmc_ids = User::where('master_dmc_id', $master_dmc_id)->get()->pluck('userId')->toArray();
            $zones = Zone::orderBy('updated_at', 'desc')->whereIn('dmc_id', $dmc_ids)->get();
        } 
        elseif($user->role_id == 35 || $user->role_id == 130 || $user->role_id == 132 || $user->role_id == 133 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138){
            $zones = Zone::orderBy('updated_at', 'desc')->where('dmc_id', $user->created_by)->get();
        }
        elseif($user->role_id == 76 || $user->role_id == 139){
            $product_head = User::where('userId', $user->created_by)->first();
            $zones = Zone::orderBy('updated_at', 'desc')->where('dmc_id', $product_head->created_by)->get();
        }
        elseif($user->role_id == 111 || $user->role_id == 140){
            $product_manager = User::where('userId', $user->created_by)->first();
            $product_head = User::where('userId', $product_manager->created_by)->first();
            $zones = Zone::orderBy('updated_at', 'desc')->where('dmc_id', $product_head->created_by)->get();
        }

        $hotels = Hotel::all();
        $attractions = Attraction::all();
        $restaurants = Restaurant::all();
        return view('zones.index', compact('zones', 'hotels', 'attractions', 'restaurants'));
    }

    /**
     * Show the form for creating a new zone.
     */
    public function create()
    {
        $city = City::where('country', Auth::user()->country)->get();
        return view('zones.create', compact('city'));
    }
    

    /**
     * Store a newly created zone in storage.
     */
    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'zone_name' => 'required|string|max:255',
            'zone_type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'city' => 'required',
            'status' => 'required|integer',
        ]);

        $zone_max_id = Zone::max('zone_id') ?? 0;
        $zoneId = CommonHelper::createId($zone_max_id);
        if ($validator->fails()) {
            return redirect()->back()
            ->withErrors($validator)
            ->withInput();
        }
        
        // Create zone with validated data
        $data = $request->all();
        // Only set the zone_id if it doesn't already exist in the request
        if (!isset($data['zone_id'])) {
            $data['zone_id'] = $zoneId;
        }
        // Determine DMC ID using the same conditions as index()
        $data['dmc_id'] = $this->resolveDmcIdForUser(Auth::user());
        
        if(!$data['dmc_id']){
            return redirect()->back()
            ->withErrors(['dmc_id' => 'DMC ID not found'])
            ->withInput();
        }
        
        $zone = Zone::create($data);
        return redirect()->route('zones.index')
            ->with('success', 'Zone created successfully');
    }

    /**
     * Display the specified zone.
     */
    public function show($id)
    {
        $zoneId = Crypt::decrypt($id);
        $zone = Zone::where('zone_id', $zoneId)->first();
        // Get the city name using the city_id
        $cityName = City::where('city_id', $zone->city)->value('name') ?? $zone->city;
        return view('zones.show', compact('zone', 'cityName'));
    }

    /**
     * Show the form for editing the specified zone.
     */
    public function edit($id)
    {
        $zoneId = Crypt::decrypt($id);
        $zone = Zone::where('zone_id', $zoneId)->first();
        $city = City::where('country', Auth::user()->country)->get();
        return view('zones.edit', compact('zone', 'city'));
    }

    /**
     * Update the specified zone in storage.
     */
    public function update(Request $request, Zone $zone)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'zone_name' => 'required|string|max:255',
            'zone_type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'city' => 'required',
            'status' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update zone
        $data = $request->all();
        $data['dmc_id'] = $this->resolveDmcIdForUser(Auth::user());
        
        if(!$data['dmc_id']){
            return redirect()->back()
            ->withErrors(['dmc_id' => 'DMC ID not found'])
            ->withInput();
        }
        
        $zone->update($data);

        return redirect()->route('zones.index')
            ->with('success', 'Zone updated successfully');
    }

    /**
     * Remove the specified zone from storage.
     */
    public function destroy($id)
    {
        $zoneId = Crypt::decrypt($id);
        $zone = Zone::where('zone_id', $zoneId)->first();
        
        // Remove zone assignments from associated hotels, attractions, and restaurants
        if ($zone->zone_type == 'Hotel') {
            $hotels = Hotel::whereJsonContains('zone_assignments', ['zone_id' => $zoneId])->get();
            foreach ($hotels as $hotel) {
                $hotel->removeZoneAssignment($zoneId);
            }
        } elseif ($zone->zone_type == 'Attraction') {
            $attractions = Attraction::whereJsonContains('zone_assignments', ['zone_id' => $zoneId])->get();
            foreach ($attractions as $attraction) {
                $attraction->removeZoneAssignment($zoneId);
            }
        } elseif ($zone->zone_type == 'Restaurant') {
            $restaurants = Restaurant::whereJsonContains('zone_assignments', ['zone_id' => $zoneId])->get();
            foreach ($restaurants as $restaurant) {
                $restaurant->removeZoneAssignment($zoneId);
            }
        }
        
        $zone->delete();

        return redirect()->route('zones.index')
            ->with('success', 'Zone deleted successfully');
    }
    
    /**
     * Save settings for a zone
     */
    public function saveSettings(Request $request, $zone_id)
    {
        $zone = Zone::where('zone_id', $zone_id)->first();
        
        if (!$zone) {
            return redirect()->route('zones.index')->with('error', 'Zone not found');
        }
        
        // Determine DMC ID using the same conditions as index()
        $currentDmcId = $this->resolveDmcIdForUser(Auth::user());
        
        if (!$currentDmcId) {
            return redirect()->route('zones.index')->with('error', 'Unable to determine your DMC association');
        }
        
        // Handle DMC-specific zone assignments
        if ($zone->zone_type == 'Hotel') {
            // Clear previous assignments for this DMC and zone
            $allHotels = Hotel::whereJsonContains('dmc_id', $currentDmcId)->get();
            foreach ($allHotels as $hotel) {
                if ($hotel->getZoneForDmc($currentDmcId) == $zone_id) {
                    $hotel->setZoneForDmc($currentDmcId, null); // Remove assignment
                }
            }
            
            // Set new assignments for selected hotels
            if ($request->has('hotels')) {
                $selectedHotels = Hotel::whereIn('hotel_unique_id', $request->hotels)->get();
                foreach ($selectedHotels as $hotel) {
                    $hotel->setZoneForDmc($currentDmcId, $zone_id);
                }
            }
        } elseif ($zone->zone_type == 'Attraction') {
            // Clear previous assignments for this DMC and zone
            $allAttractions = Attraction::whereJsonContains('dmc_id', $currentDmcId)->get();
            foreach ($allAttractions as $attraction) {
                if ($attraction->getZoneForDmc($currentDmcId) == $zone_id) {
                    $attraction->setZoneForDmc($currentDmcId, null); // Remove assignment
                }
            }
            
            // Set new assignments for selected attractions
            if ($request->has('attractions')) {
                $selectedAttractions = Attraction::whereIn('attraction_id', $request->attractions)->get();
                foreach ($selectedAttractions as $attraction) {
                    $attraction->setZoneForDmc($currentDmcId, $zone_id);
                }
            }
        } elseif ($zone->zone_type == 'Restaurant') {
            // Clear previous assignments for this DMC and zone
            $allRestaurants = Restaurant::whereJsonContains('dmc_id', $currentDmcId)->get();
            foreach ($allRestaurants as $restaurant) {
                if ($restaurant->getZoneForDmc($currentDmcId) == $zone_id) {
                    $restaurant->setZoneForDmc($currentDmcId, null); // Remove assignment
                }
            }
            
            // Set new assignments for selected restaurants
            if ($request->has('restaurants')) {
                $selectedRestaurants = Restaurant::whereIn('restaurant_id', $request->restaurants)->get();
                foreach ($selectedRestaurants as $restaurant) {
                    $restaurant->setZoneForDmc($currentDmcId, $zone_id);
                }
            }
        }
        
        return redirect()->route('zones.index')->with('success', 'Zone settings updated successfully for your DMC');
    }
}
