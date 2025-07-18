<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;
use App\Models\City;
use App\Helpers\CommonHelper;
use App\Models\Hotel;
use App\Models\Attraction;
use App\Models\Restaurant;
use App\Models\User;

class ZoneController extends Controller
{
    /**
     * Display a listing of zones.
     */
    public function index()
    {
        $user = auth()->user();
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
        elseif($user->role_id == 76){
            $assistant_product_manager_ids = User::where('created_by', $user->userId)->get()->pluck('userId')->toArray();
            if($assistant_product_manager_ids){
                $zones = Zone::orderBy('updated_at', 'desc')->whereIn('created_by', $assistant_product_manager_ids)->orWhere('created_by', $user->userId)->get();
            }else{
                $zones = Zone::orderBy('updated_at', 'desc')->where('created_by', $user->userId)->get();
            }
        }
        elseif($user->role_id == 111){
            $zones = Zone::orderBy('updated_at', 'desc')->where('created_by', $user->userId)->get();
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
        $data['dmc_id'] = Auth::user()->userId;
        $zone = Zone::create($data);
        return redirect()->route('zones.index')
            ->with('success', 'Zone created successfully');
    }

    /**
     * Display the specified zone.
     */
    public function show(Zone $zone)
    {
        // Get the city name using the city_id
        $cityName = City::where('city_id', $zone->city)->value('name') ?? $zone->city;
        return view('zones.show', compact('zone', 'cityName'));
    }

    /**
     * Show the form for editing the specified zone.
     */
    public function edit(Zone $zone)
    {
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
        $zone->update($request->all());

        return redirect()->route('zones.index')
            ->with('success', 'Zone updated successfully');
    }

    /**
     * Remove the specified zone from storage.
     */
    public function destroy(Zone $zone)
    {
        // Remove zone_id from associated hotels, attractions, and restaurants
        if ($zone->zone_type == 'Hotel') {
            Hotel::where('zone_id', $zone->zone_id)->update(['zone_id' => null]);
        } elseif ($zone->zone_type == 'Attraction') {
            Attraction::where('zone_id', $zone->zone_id)->update(['zone_id' => null]);
        } elseif ($zone->zone_type == 'Restaurant') {
            Restaurant::where('zone_id', $zone->zone_id)->update(['zone_id' => null]);
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
        
        // Clear previous assignments for this zone
        if ($zone->zone_type == 'Hotel') {
            Hotel::where('zone_id', $zone_id)->update(['zone_id' => null]);
            
            // Update selected hotels
            if ($request->has('hotels')) {
                Hotel::whereIn('hotel_unique_id', $request->hotels)->update(['zone_id' => $zone_id]);
            }
        } elseif ($zone->zone_type == 'Attraction') {
            Attraction::where('zone_id', $zone_id)->update(['zone_id' => null]);
            
            // Update selected attractions
            if ($request->has('attractions')) {
                Attraction::whereIn('attraction_id', $request->attractions)->update(['zone_id' => $zone_id]);
            }
        } elseif ($zone->zone_type == 'Restaurant') {
            Restaurant::where('zone_id', $zone_id)->update(['zone_id' => null]);
            
            // Update selected restaurants
            if ($request->has('restaurants')) {
                Restaurant::whereIn('restaurant_id', $request->restaurants)->update(['zone_id' => $zone_id]);
            }
        }
        
        return redirect()->route('zones.index')->with('success', 'Zone settings updated successfully');
    }
}
