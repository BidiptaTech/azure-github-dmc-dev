<?php
namespace App\Http\Controllers;

use App\Helpers\CountryHelper;
use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
class CityController extends Controller
{
    /**
    * Display a listing of the resource.
    */
    public function index()
    {
        $countries = [];
        $countries = Country::all();
        $cities = [];
        $cities = City::all();
        return view('cities.index', compact('countries', 'cities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $countries = [];
        $countries = Country::where('is_active', 1)->get();
        return view('cities.add-cities', compact('countries'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //dd($request->all());
        // Validate the request
        $request->validate([
            'name' => 'required|string',
            'country_name' => 'required|string',
        ]);

        // Check if city already exists with the same name and country
        $existingCity = City::where('name', $request->city_name)
        ->where('country', $request->country)
        ->first();

        if (!$existingCity) {
        // Only add if the city doesn't already exist
            $city = new City();
            $city->name = $request->city_name;
            $city->country = $request->country;
            $city->save();

            return redirect()->back()->with('success', 'City added successfully.');
        } else {
            return redirect()->back()->with('info', 'City already exists.');
        }

        // Redirect with success message
        return redirect()->route('countries.index')->with('success', 'Country added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // $country = Country::find($id);

        // if (!$country) {
        //     return redirect()->route('countries.index')->with('error', 'Country not found.');
        // }

        // $countries = Country::all(); // Get all countries for dropdown

        // return view('countries.edit-country', compact('country', 'countries'));
        $id = Crypt::decrypt($id);
        $city = City::where('city_id', $id)->first();
        $countries = Country::where('is_active', 1)->get();

        if (!$city) {
            return redirect()->route('ccities.index')->with('error', 'Country not found.');
        }

        // Get the list of 195 countries from CountryHelper
        $countries = CountryHelper::getAllCountries();

        return view('cities.edit-cities', compact('city', 'countries'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // dd($id, $request->all());
        // Validate the request
        $request->validate([
            'name' => 'required|string|unique:countries,name,' . $id,
            // 'country_code' => 'required|string|unique:countries,country_code,' . $id,
            'tax_percentage' => 'required|numeric|min:0',
            'gateway_percentage' => 'required|numeric|min:0',
            'commission_percentage' => 'required|numeric|min:0',
        ]);

        // Find the country by ID
        $country = Country::findOrFail($id);

        // Update the country data
        $country->update([
            'name' => $request->name,
            'country_code' => $request->country_code,
            'tax_percentage' => $request->tax_percentage,
            'currency' => $request->currency,
            'gateway_percentage' => $request->gateway_percentage,
            'commission_percentage' => $request->commission_percentage,
        ]);

        // Redirect with success message
        return redirect()->route('countries.index')->with('success', 'Country updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
