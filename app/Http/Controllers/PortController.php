<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Port;
use App\Models\Country;
use App\Models\City;
use Illuminate\Support\Str;
use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;

class PortController extends Controller
{
    /**
     * Display a listing of the ports.
     */
    public function index()
    {
        $ports = Port::with(['country', 'city'])->orderby('created_at', 'desc')->get();
        return view('ports.index', compact('ports'));
    }

    /**
     * Show the form for creating a new port.
     */
    public function create()
    {
        $countries = Country::all();
        $cities = City::all();
        return view('ports.create', compact('countries', 'cities'));
    }

    /**
     * Store a newly created port in storage.
     */
    public function store(Request $request)
    {
        $cityInput = $request->input('city_id');
        $cityIdToUse = null;

        if (Str::startsWith($cityInput, 'new:')) {
            $cityName = trim(substr($cityInput, 4));

            // Check if the city already exists
            $existingCity = City::where('name', $cityName)
                ->where('country', $request->country)
                ->first();

            if ($existingCity) {
                $cityIdToUse = $existingCity->city_id;
            } else {
                // Generate new custom city_id
                // $lastCity = City::withTrashed()->orderBy('city_id', 'desc')->first();
                // $lastCity_max_city_id = $lastCity->city_id ?? 0;
                // $newCityId = CommonHelper::createId($lastCity_max_city_id);

                // Ensure uniqueness of city_id
                // while (City::where('city_id', $newCityId)->exists()) {
                //     $newCityId = CommonHelper::createId($newCityId);
                // }

                // Generate new custom ID
                $lastDbId = City::withTrashed()->orderBy('id', 'desc')->value('id') ?? 0;
                $newId = $lastDbId + 1;

                // Create city
                $newCity = City::create([
                    'id' => $newId,
                    'name' => $cityName,
                    'country' => $request->country,
                    // 'city_id' => $newCityId,
                ]);
                

                $cityIdToUse = $newCity->city_id;
            }
        } else {
            $cityIdToUse = $cityInput;
        }

        // Validate after resolving final city_id
        $validator = Validator::make(array_merge($request->all(), ['city_id' => $cityIdToUse]), [
            'port_name' => 'required|string|max:255',
            'type' => 'required|in:Airport,Seaport,LandPort,Railway,BusStand',
            'country' => 'required|string',
            'city_id' => 'required|exists:cities,city_id',
            'latitude' => ['required', 'regex:/^-?([1-8]?[0-9](\.\d+)?|90(\.0+)?)/'],
            'longitude' => ['required', 'regex:/^-?(1[0-7][0-9](\.\d+)?|[1-9]?[0-9](\.\d+)?|180(\.0+)?)/'],
            // 'distance' => 'required|numeric|between:0,9999.99',
            'status' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Create port
        // $port_max_id = Port::max('port_id') ?? 0;
        // $portId = CommonHelper::createId($port_max_id);

        $data = $validator->validated();
        // $data['port_id'] = $portId;
        $data['status'] = $request->has('status') ? true : false;
        $data['city_id'] = $cityIdToUse;

        $port = Port::create($data);
        $port->refresh();

        return redirect()->route('ports.index')->with('success', 'Port created successfully');
    }

    /**
     * Display the specified port.
     */
    public function show($port_id)
    {
        $port_id = Crypt::decrypt($port_id);
        $port = Port::where('port_id', $port_id)->firstOrFail();
        $port->load(['country', 'city']);
        return view('ports.show', compact('port'));
    }

    /**
     * Show the form for editing the specified port.
     */
    public function edit($port_id)
    {
        $port_id = Crypt::decrypt($port_id);
        $port = Port::where('port_id', $port_id)->firstOrFail();
        $countries = Country::all();
        $cities = City::all();
        return view('ports.edit', compact('port', 'countries', 'cities'));
    }

    /**
     * Update the specified port in storage.
     */
    public function update(Request $request, $port_id)
    {
        $port = Port::where('port_id', $port_id)->firstOrFail();
        
        $cityInput = $request->input('city_id');
        $cityIdToUse = null;

        if (Str::startsWith($cityInput, 'new:')) {
            $cityName = trim(substr($cityInput, 4));

            // Check if the city already exists
            $existingCity = City::where('name', $cityName)
                ->where('country', $request->country)
                ->first();

            if ($existingCity) {
                $cityIdToUse = $existingCity->city_id;
            } else {
                // Generate new custom city_id
                // $lastCity = City::withTrashed()->orderBy('city_id', 'desc')->first();
                // $lastCity_max_city_id = $lastCity->city_id ?? 0;
                // $newCityId = CommonHelper::createId($lastCity_max_city_id);

                // // Ensure uniqueness of city_id
                // while (City::where('city_id', $newCityId)->exists()) {
                //     $newCityId = CommonHelper::createId($newCityId);
                // }

                // Generate new custom ID
                $lastDbId = City::withTrashed()->orderBy('id', 'desc')->value('id') ?? 0;
                $newId = $lastDbId + 1;

                // Create city
                $newCity = City::create([
                    'id' => $newId,
                    'name' => $cityName,
                    'country' => $request->country,
                    // 'city_id' => $newCityId,
                ]);
                $newCity->refresh();
                $cityIdToUse = $newCity->city_id;
            }
        } else {
            $cityIdToUse = $cityInput;
        }

        // Validate after resolving final city_id
        $validator = Validator::make(array_merge($request->all(), ['city_id' => $cityIdToUse]), [
            'port_name' => 'required|string|max:255',
            'type' => 'required|in:Airport,Seaport,LandPort,Railway,BusStand',
            'country' => 'required',
            'city_id' => 'required|exists:cities,city_id',
            'latitude' => ['required', 'regex:/^-?([1-8]?[0-9]\.{1}\d{1,9}$|90\.{1}0{1,9}$)/'],
            'longitude' => ['required', 'regex:/^-?([1-9]?[0-9]\.{1}\d{1,9}$|1[0-7][0-9]\.{1}\d{1,9}$|180\.{1}0{1,9}$)/'],
            // 'distance' => 'required|numeric|between:0,9999.99',
            'status' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $data = $validator->validated();
        $data['status'] = $request->has('status') ? true : false;
        $data['city_id'] = $cityIdToUse;
        $port->update($data);
        return redirect()->route('ports.index')->with('success', 'Port updated successfully');
    }

    /**
     * Remove the specified port from storage.
     */
    public function destroy($port_id)
    {
        $port_id = Crypt::decrypt($port_id);
        $port = Port::where('port_id', $port_id)->firstOrFail();
        $port->delete();
        return redirect()->route('ports.index')->with('success', 'Port deleted successfully');
    }

    /**
     * Get cities for a country (for dynamic dropdown)
     */
    public function getCities(Request $request)
    {
        $countryId = $request->country;
        $cities = City::where('country', $countryId)->get();
        return response()->json($cities);
    }

    /**
     * Toggle port status (active/inactive)
     */
    public function toggleStatus($port_id)
    {
        $port_id = Crypt::decrypt($port_id);
        $port = Port::where('port_id', $port_id)->firstOrFail();
        $port->status = !$port->status;
        $port->save();
        
        return redirect()->route('ports.index')->with('success', 'Port status updated successfully');
    }
} 