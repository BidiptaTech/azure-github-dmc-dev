<?php
namespace App\Http\Controllers;

use App\Helpers\CountryHelper;
use App\Helpers\CommonHelper;
use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
class CityController extends Controller
{
    /**
    * Display a listing of the resource.
    */
    public function index(Request $request)
    {
        $countries = Country::orderBy('name', 'asc')->where('is_active', 1)->get();
        
        // Get all cities for DataTable pagination
        $cities = City::orderBy('name', 'asc')->get();
        
        return view('cities.index', compact('countries', 'cities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $countries = Country::orderBy('name', 'asc')->where('is_active', 1)->get();
        return view('cities.add-cities', compact('countries'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|exists:countries,id',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
        ]);

        // Get country name from country ID
        $country = Country::find($request->country);
        if (!$country) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Selected country not found'], 400);
            }
            return redirect()->back()
                ->withErrors(['country' => 'Selected country not found'])
                ->withInput();
        }

        $countryName = $country->name;
        $cityName = trim($request->name);

        // Check if city already exists (case-insensitive) - active cities
        $existingCity = City::whereRaw('LOWER(name) = ?', [strtolower($cityName)])
            ->where('country', $countryName)
            ->first();

        if ($existingCity) {
            if ($request->ajax()) {
                return response()->json(['error' => "City '{$cityName}' already exists in {$countryName}"], 400);
            }
            return redirect()->back()
                ->withErrors(['name' => "City '{$cityName}' already exists in {$countryName}"])
                ->withInput();
        }

        // Check if city exists in soft-deleted records
        $deletedCity = City::onlyTrashed()
            ->whereRaw('LOWER(name) = ?', [strtolower($cityName)])
            ->where('country', $countryName)
            ->first();

        if ($deletedCity) {
            // Restore the soft-deleted city
            $deletedCity->restore();
            
            // Handle image upload for restored city
            if ($request->hasFile('image')) {
                // Delete old image if exists using CommonHelper
                if ($deletedCity->image) {
                    CommonHelper::deleteAzureImage($deletedCity->image);
                }
                
                // Store new image using CommonHelper
                $uploadResult = CommonHelper::image_path('file_storage', $request->file('image'), 'uploads');
                $imagePath = $uploadResult['master_value'] ?? null;
                $deletedCity->update(['image' => $imagePath]);
            }
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "City '{$cityName}' restored successfully in {$countryName}",
                    'city_id' => $deletedCity->city_id,
                    'city_name' => $deletedCity->name
                ]);
            }
            
            return redirect()->route('cities.index')
                ->with('success', "City '{$cityName}' restored successfully in {$countryName}");
        }

        // Generate new city_id
        $lastCity = City::withTrashed()->orderBy('city_id', 'desc')->first();
        $lastCityId = $lastCity->city_id ?? 0;
        $newCityId = \App\Helpers\CommonHelper::createId($lastCityId);

        // Ensure uniqueness of city_id
        while (City::where('city_id', $newCityId)->exists()) {
            $newCityId = \App\Helpers\CommonHelper::createId($newCityId);
        }

        // Generate new database ID
        $lastDbId = City::withTrashed()->orderBy('id', 'desc')->value('id') ?? 0;
        $newId = $lastDbId + 1;

        // Handle image upload using CommonHelper
        $imagePath = null;
        if ($request->hasFile('image')) {
            $uploadResult = CommonHelper::image_path('file_storage', $request->file('image'), 'uploads');
            $imagePath = $uploadResult['master_value'] ?? null;
        }

        // Create city
        $city = City::create([
            'id' => $newId,
            'name' => $cityName,
            'country' => $countryName,
            'city_id' => $newCityId,
            'image' => $imagePath,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "City '{$cityName}' added successfully to {$countryName}",
                'city_id' => $city->city_id,
                'city_name' => $city->name
            ]);
        }

        return redirect()->route('cities.index')
            ->with('success', "City '{$cityName}' added successfully to {$countryName}");
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
        $city = City::where('city_id', Crypt::decrypt($id))->first();
        $countries = Country::orderBy('name', 'asc')->where('is_active', 1)->get();

        if (!$city) {
            return redirect()->route('cities.index')->with('error', 'City not found.');
        }

        return view('cities.edit-cities', compact('city', 'countries'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|exists:countries,id',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:2048',
        ]);

        // Find the city by city_id
        $city = City::where('city_id', $id)->first();
        if (!$city) {
            return redirect()->route('cities.index')->with('error', 'City not found.');
        }
        
        // Get country name from country ID
        $country = Country::find($request->country);
        if (!$country) {
            return redirect()->back()
                ->withErrors(['country' => 'Selected country not found'])
                ->withInput();
        }

        $countryName = $country->name;
        $cityName = trim($request->name);

        // Check if city already exists (case-insensitive) excluding current city
        $existingCity = City::whereRaw('LOWER(name) = ?', [strtolower($cityName)])
            ->where('country', $countryName)
            ->where('city_id', '!=', $id)
            ->first();

        if ($existingCity) {
            return redirect()->back()
                ->withErrors(['name' => "City '{$cityName}' already exists in {$countryName}"])
                ->withInput();
        }

        // Handle image upload using CommonHelper
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($city->image) {
                CommonHelper::deleteAzureImage($city->image);
            }
            
            // Store new image using CommonHelper
            $uploadResult = CommonHelper::image_path('file_storage', $request->file('image'), 'uploads');
            $city->image = $uploadResult['master_value'] ?? null;
        }
        
        // Handle image removal
        if ($request->has('remove_existing_image') && $request->remove_existing_image == '1') {
            if ($city->image) {
                CommonHelper::deleteAzureImage($city->image);
            }
            $city->image = null;
        }

        // Update city
        $city->update([
            'name' => $cityName,
            'country' => $countryName,
            'image' => $city->image,
        ]);

        return redirect()->route('cities.index')
            ->with('success', "City '{$cityName}' updated successfully in {$countryName}");
    }

    /**
    * Remove the specified resource from storage.
    */
    public function destroy(string $id)
    {
        try {
            $city = City::where('city_id', Crypt::decrypt($id))->first();
            if (!$city) {
                return redirect()->route('cities.index')->with('error', 'City not found.');
            }
            
            $cityName = $city->name;
            $countryName = $city->country;
            
            // Delete image if exists (only when permanently deleting)
            // Note: With soft delete, we keep the image for potential restoration
            // Uncomment below if you want to delete image on soft delete
            // if ($city->image) {
            //     CommonHelper::deleteAzureImage($city->image);
            // }
            
            $city->delete();
            
            return redirect()->route('cities.index')
                ->with('success', "City '{$cityName}' from {$countryName} deleted successfully");
        } catch (\Exception $e) {
            return redirect()->route('cities.index')
                ->with('error', 'Failed to delete city: ' . $e->getMessage());
        }
    }

    /**
     * Get cities by country (AJAX)
     */
    public function getCitiesByCountry(Request $request)
    {
        $countryId = $request->country;
        
        if (!$countryId) {
            return response()->json([]);
        }

        // Get country name from country ID
        $country = Country::find($countryId);
        if (!$country) {
            return response()->json([]);
        }

        $cities = City::where('country', $country->name)
            ->orderBy('name', 'asc')
            ->get(['city_id', 'name']);

        return response()->json($cities);
    }
}
