<?php

namespace App\Http\Controllers\APi;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Port;
use App\Models\Tour;
use App\Models\Hotel;
use App\Models\Attraction;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class PortController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function port_list(Request $request)
    {
        // $tour_id = $request->query('tour_id');
        $cityName = $request->query('city');
        $country = $request->query('country');
        $type = $request->query('type');
        if (!$country) {
            return response()->json([
                'status' => false,
                'message' => 'Country is required.',
            ], 422);
        }
        if (!$cityName) {
            return response()->json([
                'status' => false,
                'message' => 'City is required.',
            ], 422);
        }
        // $country = Tour::where('tour_id', $tour_id)->value('destination');
        $countryExists = Port::where('country', $country)->exists();
        if (!$countryExists) {
            return response()->json([
                'status' => false,
                'message' => 'Country not found.',
            ], 404);
        }
        $city = City::where('name', $cityName)->first();
        if (!$city) {
            return response()->json([
                'status' => false,
                'message' => 'City not found.',
            ], 404);
        }
        if($type == 'port'){
            $data = Port::where('country', $country)
                ->where('city_id', $city->city_id)
                ->select('port_id', 'port_name', 'type')
                ->get();
        }else{
            $cityName = City::where('city_id',$city->city_id)->value('name');
            // Get hotels
            $hotels = Hotel::where('country', $country)
                ->where('city', $cityName)
                ->get()
                ->map(function ($hotel) {
                    return [
                        'id' => (string) $hotel->hotel_unique_id, // force string cast
                        'name' => $hotel->name,
                        'type' => 'hotel',
                    ];
                });
            // Get attractions
            $attractions = Attraction::where('country', $country)
                ->where('location', $cityName)
                ->select('attraction_id as id', 'name', \DB::raw("'attraction' as type"))
                ->get();
            // Get restaurants
            $restaurants = Restaurant::where('country', $country)
                ->where('city', $cityName)
                ->select('restaurant_id as id', 'name', \DB::raw("'restaurant' as type"))
                ->get();
            // Combine all data
            $data = [
                'hotels' => $hotels,
                'attractions' => $attractions,
                'restaurants' => $restaurants,
            ];
        }
        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
