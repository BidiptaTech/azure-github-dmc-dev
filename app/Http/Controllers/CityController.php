<?php
namespace App\Http\Controllers;

use App\Helpers\CountryHelper;
use App\Helpers\CommonHelper;
use App\Models\City;
use App\Models\Country;
use App\Models\CityExploration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Attraction;
use App\Models\Hotel;
use App\Models\Restaurant;
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

    /**
     * Show the exploration form for a specific city
     */
    public function exploreCity(string $id)
    {
        try {
            $city = City::where('city_id', Crypt::decrypt($id))->first();
            
            if (!$city) {
                return redirect()->route('cities.index')->with('error', 'City not found.');
            }
            $attractions = Attraction::where('location', $city->name)->get();
            $hotels = Hotel::where('city', $city->name)->get();
            $restaurants = Restaurant::where('city', $city->name)->get();
            // Get existing exploration data if available
            $exploration = CityExploration::where('city_id', $city->city_id)->first();

            return view('cities.explore', compact('city', 'exploration', 'attractions', 'hotels', 'restaurants'));
        } catch (\Exception $e) {
            return redirect()->route('cities.index')->with('error', 'Invalid city ID.');
        }
    }

    /**
     * Store or update city exploration data
     */
    public function storeExploration(Request $request, string $id)
    {
        try {
            $cityId = Crypt::decrypt($id);
            $city = City::where('city_id', $cityId)->first();
            
            if (!$city) {
                return redirect()->route('cities.index')->with('error', 'City not found.');
            }

            DB::beginTransaction();

            // Process Overview section
            $overview = [
                'city_name' => $request->input('overview_city_name'),
                'image' => null,
                'short_description' => $request->input('overview_description'),
                'best_known_for' => $request->input('overview_known_for'),
                'local_language' => $request->input('overview_language'),
                'currency' => $request->input('overview_currency'),
                'time_zone' => $request->input('overview_timezone'),
                'population' => $request->input('overview_population'),
            ];

            // Handle overview image
            $existingExploration = CityExploration::where('city_id', $cityId)->first();
            
            // Handle image upload/removal
            if ($request->hasFile('overview_image')) {
                // User is uploading a new image
                // Delete old overview image if exists
                if ($existingExploration && !empty($existingExploration->overview['image'])) {
                    CommonHelper::deleteAzureImage($existingExploration->overview['image']);
                }
                
                $uploadResult = CommonHelper::image_path('file_storage', $request->file('overview_image'));
                if (!empty($uploadResult['master_value'])) {
                    $overview['image'] = $uploadResult['master_value'];
                }
            } elseif ($request->input('remove_overview_image') == '1') {
                // User is removing the image without uploading a new one
                if ($existingExploration && !empty($existingExploration->overview['image'])) {
                    CommonHelper::deleteAzureImage($existingExploration->overview['image']);
                }
                $overview['image'] = null;
            } elseif ($request->input('existing_overview_image')) {
                // Keep existing image
                $overview['image'] = $request->input('existing_overview_image');
            }

            // Process Attractions
            $attractions = [];
            if ($request->has('attraction_name')) {
                foreach ($request->input('attraction_name') as $index => $name) {
                    $attractionData = [
                        'name' => $name,
                        'image' => null,
                    ];

                    // Handle attraction image
                    if ($request->hasFile("attraction_image.{$index}")) {
                        // User is uploading a new image
                        // Delete old attraction image if exists and being replaced
                        if ($existingExploration && 
                            isset($existingExploration->attractions[$index]['image']) && 
                            !empty($existingExploration->attractions[$index]['image'])) {
                            CommonHelper::deleteAzureImage($existingExploration->attractions[$index]['image']);
                        }
                        
                        $uploadResult = CommonHelper::image_path('file_storage', $request->file("attraction_image.{$index}"));
                        if (!empty($uploadResult['master_value'])) {
                            $attractionData['image'] = $uploadResult['master_value'];
                        }
                    } elseif ($request->input("remove_attraction_image.{$index}") == '1') {
                        // User is removing the image without uploading a new one
                        if ($existingExploration && 
                            isset($existingExploration->attractions[$index]['image']) && 
                            !empty($existingExploration->attractions[$index]['image'])) {
                            CommonHelper::deleteAzureImage($existingExploration->attractions[$index]['image']);
                        }
                        $attractionData['image'] = null;
                    } elseif ($request->input("existing_attraction_image.{$index}")) {
                        // Keep existing image
                        $attractionData['image'] = $request->input("existing_attraction_image.{$index}");
                    }

                    $attractions[] = $attractionData;
                }
            }

            // Process Food and Cuisine
            $foodCuisine = [
                'famous_dishes' => $request->input('food_dish_name') ? array_combine(
                    $request->input('food_dish_name', []),
                    $request->input('food_dish_description', [])
                ) : [],
                'top_restaurants' => [],
                'street_spots' => [],
            ];

            // Process restaurants
            if ($request->has('restaurant_name')) {
                foreach ($request->input('restaurant_name') as $index => $name) {
                    $restaurantData = [
                        'name' => $name,
                        'image' => null,
                    ];

                    // Handle restaurant image
                    if ($request->hasFile("restaurant_image.{$index}")) {
                        // User is uploading a new image
                        // Delete old restaurant image if exists and being replaced
                        if ($existingExploration && 
                            isset($existingExploration->food_cuisine['top_restaurants'][$index]['image']) && 
                            !empty($existingExploration->food_cuisine['top_restaurants'][$index]['image'])) {
                            CommonHelper::deleteAzureImage($existingExploration->food_cuisine['top_restaurants'][$index]['image']);
                        }
                        
                        $uploadResult = CommonHelper::image_path('file_storage', $request->file("restaurant_image.{$index}"));
                        if (!empty($uploadResult['master_value'])) {
                            $restaurantData['image'] = $uploadResult['master_value'];
                        }
                    } elseif ($request->input("remove_restaurant_image.{$index}") == '1') {
                        // User is removing the image without uploading a new one
                        if ($existingExploration && 
                            isset($existingExploration->food_cuisine['top_restaurants'][$index]['image']) && 
                            !empty($existingExploration->food_cuisine['top_restaurants'][$index]['image'])) {
                            CommonHelper::deleteAzureImage($existingExploration->food_cuisine['top_restaurants'][$index]['image']);
                        }
                        $restaurantData['image'] = null;
                    } elseif ($request->input("existing_restaurant_image.{$index}")) {
                        // Keep existing image
                        $restaurantData['image'] = $request->input("existing_restaurant_image.{$index}");
                    }

                    $foodCuisine['top_restaurants'][] = $restaurantData;
                }
            }

            // Process street spots
            if ($request->has('street_spot_name')) {
                foreach ($request->input('street_spot_name') as $index => $name) {
                    $foodCuisine['street_spots'][] = [
                        'name' => $name,
                        'description' => $request->input('street_spot_description')[$index] ?? '',
                    ];
                }
            }

            // Handle food image
            if ($request->hasFile('food_image')) {
                // User is uploading a new image
                // Delete old food image if exists
                if ($existingExploration && !empty($existingExploration->food_cuisine['image'])) {
                    CommonHelper::deleteAzureImage($existingExploration->food_cuisine['image']);
                }
                
                $uploadResult = CommonHelper::image_path('file_storage', $request->file('food_image'));
                if (!empty($uploadResult['master_value'])) {
                    $foodCuisine['image'] = $uploadResult['master_value'];
                }
            } elseif ($request->input('remove_food_image') == '1') {
                // User is removing the image without uploading a new one
                if ($existingExploration && !empty($existingExploration->food_cuisine['image'])) {
                    CommonHelper::deleteAzureImage($existingExploration->food_cuisine['image']);
                }
                $foodCuisine['image'] = null;
            } elseif ($request->input('existing_food_image')) {
                // Keep existing image
                $foodCuisine['image'] = $request->input('existing_food_image');
            }

            // Process Accommodation
            $accommodation = [];
            if ($request->has('hotel_name')) {
                foreach ($request->input('hotel_name') as $index => $name) {
                    $hotelData = [
                        'name' => $name,
                        'image' => null,
                    ];

                    if ($request->hasFile("hotel_image.{$index}")) {
                        // User is uploading a new image
                        // Delete old hotel image if exists and being replaced
                        if ($existingExploration && 
                            isset($existingExploration->accommodation[$index]['image']) && 
                            !empty($existingExploration->accommodation[$index]['image'])) {
                            CommonHelper::deleteAzureImage($existingExploration->accommodation[$index]['image']);
                        }
                        
                        $uploadResult = CommonHelper::image_path('file_storage', $request->file("hotel_image.{$index}"));
                        if (!empty($uploadResult['master_value'])) {
                            $hotelData['image'] = $uploadResult['master_value'];
                        }
                    } elseif ($request->input("remove_hotel_image.{$index}") == '1') {
                        // User is removing the image without uploading a new one
                        if ($existingExploration && 
                            isset($existingExploration->accommodation[$index]['image']) && 
                            !empty($existingExploration->accommodation[$index]['image'])) {
                            CommonHelper::deleteAzureImage($existingExploration->accommodation[$index]['image']);
                        }
                        $hotelData['image'] = null;
                    } elseif ($request->input("existing_hotel_image.{$index}")) {
                        // Keep existing image
                        $hotelData['image'] = $request->input("existing_hotel_image.{$index}");
                    }

                    $accommodation[] = $hotelData;
                }
            }

            // Process Transportation
            $transportation = [
                'airports' => $this->processMultipleFields($request, 'airport_name', 'airport_distance', 'airport_code'),
                'railway_stations' => $this->processMultipleFields($request, 'railway_name', 'railway_distance'),
                'local_transport' => $request->input('transport_type') ? array_combine(
                    $request->input('transport_type', []),
                    $request->input('transport_description', [])
                ) : [],
            ];

            // Process Best Time to Visit
            $bestTimeVisit = [
                'seasonal_highlights' => $this->processMultipleFields($request, 'season_period', 'season_description'),
                'festival_periods' => $this->processMultipleFields($request, 'festival_name', 'festival_period', 'festival_description'),
            ];

            // Process Shopping
            $shopping = [];
            if ($request->has('shopping_name')) {
                foreach ($request->input('shopping_name') as $index => $name) {
                    $shoppingData = [
                        'name' => $name,
                        'type' => $request->input('shopping_type')[$index] ?? '',
                        'description' => $request->input('shopping_description')[$index] ?? '',
                        'image' => null,
                    ];

                    // Handle shopping image
                    if ($request->hasFile("shopping_image.{$index}")) {
                        // User is uploading a new image
                        // Delete old shopping image if exists and being replaced
                        if ($existingExploration && 
                            isset($existingExploration->shopping[$index]['image']) && 
                            !empty($existingExploration->shopping[$index]['image'])) {
                            CommonHelper::deleteAzureImage($existingExploration->shopping[$index]['image']);
                        }
                        
                        $uploadResult = CommonHelper::image_path('file_storage', $request->file("shopping_image.{$index}"));
                        if (!empty($uploadResult['master_value'])) {
                            $shoppingData['image'] = $uploadResult['master_value'];
                        }
                    } elseif ($request->input("remove_shopping_image.{$index}") == '1') {
                        // User is removing the image without uploading a new one
                        if ($existingExploration && 
                            isset($existingExploration->shopping[$index]['image']) && 
                            !empty($existingExploration->shopping[$index]['image'])) {
                            CommonHelper::deleteAzureImage($existingExploration->shopping[$index]['image']);
                        }
                        $shoppingData['image'] = null;
                    } elseif ($request->input("existing_shopping_image.{$index}")) {
                        // Keep existing image
                        $shoppingData['image'] = $request->input("existing_shopping_image.{$index}");
                    }

                    $shopping[] = $shoppingData;
                }
            }

            // Process Hospitals and Emergency
            $hospitalsEmergency = [
                'hospitals' => [],
                'pharmacies' => $this->processSimpleList($request, 'pharmacy_name'),
                'emergency_numbers' => $this->processMultipleFields($request, 'emergency_service', 'emergency_number'),
            ];

            if ($request->has('hospital_name')) {
                foreach ($request->input('hospital_name') as $index => $name) {
                    $hospitalsEmergency['hospitals'][] = [
                        'name' => $name,
                        'type' => $request->input('hospital_type')[$index] ?? '',
                        'address' => $request->input('hospital_address')[$index] ?? '',
                        'contact' => $request->input('hospital_contact')[$index] ?? '',
                    ];
                }
            }

            // Create or update exploration data
            CityExploration::updateOrCreate(
                ['city_id' => $cityId],
                [
                    'overview' => $overview,
                    'attractions' => $attractions,
                    'food_cuisine' => $foodCuisine,
                    'accommodation' => $accommodation,
                    'transportation' => $transportation,
                    'best_time_visit' => $bestTimeVisit,
                    'shopping' => $shopping,
                    'hospitals_emergency' => $hospitalsEmergency,
                ]
            );

            DB::commit();

            return redirect()->route('cities.index')
                ->with('success', "Exploration data for '{$city->name}' saved successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to save exploration data: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Helper function to process multiple fields
     */
    private function processMultipleFields(Request $request, ...$fields)
    {
        $result = [];
        $primaryField = $fields[0];
        
        if (!$request->has($primaryField)) {
            return $result;
        }

        foreach ($request->input($primaryField) as $index => $value) {
            $item = [];
            foreach ($fields as $fieldIndex => $field) {
                $fieldName = explode('_', $field);
                $key = end($fieldName);
                $item[$key] = $request->input($field)[$index] ?? '';
            }
            $result[] = $item;
        }

        return $result;
    }

    /**
     * Helper function to process simple list
     */
    private function processSimpleList(Request $request, $fieldName)
    {
        return $request->input($fieldName, []);
    }

    /**
     * Delete city exploration data and all associated images
     */
    public function destroyExploration(string $id)
    {
        try {
            $cityId = Crypt::decrypt($id);
            $exploration = CityExploration::where('city_id', $cityId)->first();
            
            if (!$exploration) {
                return redirect()->route('cities.index')->with('error', 'Exploration data not found.');
            }

            // Delete overview image
            if (!empty($exploration->overview['image'])) {
                CommonHelper::deleteAzureImage($exploration->overview['image']);
            }

            // Delete attraction images
            if (!empty($exploration->attractions)) {
                foreach ($exploration->attractions as $attraction) {
                    if (!empty($attraction['image'])) {
                        CommonHelper::deleteAzureImage($attraction['image']);
                    }
                }
            }

            // Delete food image
            if (!empty($exploration->food_cuisine['image'])) {
                CommonHelper::deleteAzureImage($exploration->food_cuisine['image']);
            }

            // Delete restaurant images
            if (!empty($exploration->food_cuisine['top_restaurants'])) {
                foreach ($exploration->food_cuisine['top_restaurants'] as $restaurant) {
                    if (!empty($restaurant['image'])) {
                        CommonHelper::deleteAzureImage($restaurant['image']);
                    }
                }
            }

            // Delete shopping images
            if (!empty($exploration->shopping)) {
                foreach ($exploration->shopping as $shopping) {
                    if (!empty($shopping['image'])) {
                        CommonHelper::deleteAzureImage($shopping['image']);
                    }
                }
            }

            // Delete hotel images
            if (!empty($exploration->accommodation)) {
                foreach ($exploration->accommodation as $hotel) {
                    if (!empty($hotel['image'])) {
                        CommonHelper::deleteAzureImage($hotel['image']);
                    }
                }
            }

            // Delete the exploration record
            $exploration->delete();

            return redirect()->route('cities.index')
                ->with('success', 'City exploration data deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->route('cities.index')
                ->with('error', 'Failed to delete exploration data: ' . $e->getMessage());
        }
    }
}
