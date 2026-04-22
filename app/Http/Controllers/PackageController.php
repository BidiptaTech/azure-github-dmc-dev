<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Package;
use App\Models\Hotel;
use App\Models\Attraction;
use App\Models\Guide;
use App\Models\Restaurant;
use App\Models\Meal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\CommonHelper;
use Exception;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\PackageBooking;
use App\Models\Vehicle;
use App\Models\Agent;
use App\Models\Room;
use App\Models\Bed;
use App\Models\Ticket;
use App\Models\Port;
use Illuminate\Support\Facades\Crypt;

class PackageController extends Controller
{
    /**
     * Display the predefined packages admin interface
     */
    public function index(Request $request)
    {
        $dmc_id = null;
        $user = Auth::user();
        if($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 23){
            $dmc_id = $request->dmc ?? null;
        }
        elseif($user->role_id == 11){
            $dmc_id = $user->userId;
        }
        elseif($user->role_id == 33 || $user->role_id == 34|| $user->role_id == 35 || $user->role_id == 36 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 131 || $user->role_id == 132 || $user->role_id == 133 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138 || $user->role_id == 139){
            $userdmc = User::where('userId', $user->created_by)->first();
            $dmc_id = $userdmc->userId;
        }
        elseif(in_array($user->role_id, array_merge(range(64, 78), [37,139]))) {
            $user_product_head = User::where('userId', $user->created_by)->first();
            $user_product_head_dmc = User::where('userId', $user_product_head->created_by)->first();
            $dmc_id = $user_product_head_dmc->userId;
        }
        elseif($user->role_id == 84 || $user->role_id == 93 || $user->role_id == 102  || $user->role_id == 38 || $user->role_id == 81 || $user->role_id == 87 || $user->role_id == 90  || $user->role_id == 96|| $user->role_id == 99 || $user->role_id == 102 || $user->role_id == 105 || $user->role_id == 108 || $user->role_id == 111 || $user->role_id == 114 || $user->role_id == 117 || $user->role_id == 120 || $user->role_id == 123 ){
            $user_product_manager = User::where('userId', $user->created_by)->first();
            $user_product_head = User::where('userId', $user_product_manager->created_by)->first();
            $user_product_head_dmc = User::where('userId', $user_product_head->created_by)->first();
            $dmc_id = $user_product_head_dmc->userId;
        }
        $query = Package::where('dmc_id', $dmc_id);

        // Duration filter
        if ($request->filled('duration')) {
            $duration = explode('-', $request->duration);
            if (count($duration) == 2) {
                $query->whereBetween('duration_days', [$duration[0], $duration[1]]);
            } else {
                // For 10+ days
                $query->where('duration_days', '>', 10);
            }
        }

        // Price range filter
        if ($request->filled('price_range')) {
            $price = explode('-', $request->price_range);
            if (count($price) == 2) {
                $query->whereBetween('price_adult', [$price[0], $price[1]]);
            } else {
                // For 501+ price
                $query->where('price_adult', '>', 501);
            }
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Sorting
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'price_low':
                    $query->orderBy('price_adult', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('price_adult', 'desc');
                    break;
                case 'duration_low':
                    $query->orderBy('duration_days', 'asc');
                    break;
                case 'duration_high':
                    $query->orderBy('duration_days', 'desc');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $packages = $query->paginate(12);
        
        // Pre-process itinerary data for each package
        foreach ($packages as $package) {
            // We'll let the view handle JSON decoding to avoid double-decoding issues
            // Just ensure the itinerary field exists
            if (is_null($package->itinerary)) {
                $package->itinerary = '{}';
            } elseif (is_string($package->itinerary) && !empty($package->itinerary)) {
                // Validate JSON string to avoid errors in the view
                $decoded = json_decode($package->itinerary, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    // Invalid JSON, set to empty object
                    $package->itinerary = '{}';
                }
            }
        }

        return view('package.package', compact('packages'));
    }

    /**
     * Show the form for creating a new predefined package
     */
    public function create()
    {
        $countries = Country::where('is_active', 1)->orderBy('name')->get();
        return view('package.create-predefined', compact('countries'));
    }

    /**
     * Store a newly created package
     */
    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'duration_days' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'price_adult' => 'required|numeric|min:0',
            'price_senior' => 'nullable|numeric|min:0',
            'price_child' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'expiry_date' => 'required|date|after:start_date',
            'main_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'inclusions' => 'nullable|string',
            'exclusions' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'status' => 'required',
            'child_max_age' => 'nullable|integer',
        ]);

        try {
            DB::beginTransaction();

            // Log the JSON data for debugging
            Log::info('Package Creation JSON Data:', [
                'itinerary_json_data' => $request->input('itinerary_json_data'),
                'hotel_json_data' => $request->input('hotel_json_data'),
                'day_wise_itinerary' => $request->input('day_wise_itinerary')
            ]);

            // Handle main image upload
            $mainImagePath = null;
            if ($request->hasFile('main_image')) {
                $imageData = CommonHelper::image_path('file_storage', $request->file('main_image'));
                if (!empty($imageData['master_value'])) {
                    $mainImagePath = $imageData['master_value'];
                }
            }

            // Handle gallery images upload
            $galleryImages = [];
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $image) {
                    $imageData = CommonHelper::image_path('file_storage', $image);
                    if (!empty($imageData['master_value'])) {
                        $galleryImages[] = $imageData['master_value'];
                    }
                }
            }

            // Process the JSON data
            $itineraryData = $request->input('itinerary_json_data');
            $hotelJsonData = $request->input('hotel_json_data');
            
            // Debug the JSON data
            Log::debug('Raw itinerary data', ['data' => $itineraryData]);
            Log::debug('Raw hotel data', ['data' => $hotelJsonData]);
            
            // Process day-wise itinerary data for backward compatibility
            $dayWiseItineraryRaw = $request->input('day_wise_itinerary');
            Log::debug('Day-wise itinerary data', ['data' => $dayWiseItineraryRaw]);
            
            // Extract data from day_wise_itinerary if JSON data is empty
            if ((empty($itineraryData) || $itineraryData === 'null' || $itineraryData === '[]') && 
                !empty($dayWiseItineraryRaw)) {
                $dayWiseData = json_decode($dayWiseItineraryRaw, true);
                
                if (is_array($dayWiseData) && isset($dayWiseData['itinerary'])) {
                    // Create itinerary JSON from day_wise_itinerary
                    $extractedItinerary = [];
                    
                    foreach ($dayWiseData['itinerary'] as $dayData) {
                        $day = $dayData['day'];
                        $extractedItinerary[$day] = [
                            'attractions' => array_map(function($attraction) {
                                return [
                                    'id' => $attraction['attraction_id'],
                                    'name' => $attraction['name'],
                                    'city' => $attraction['location'],
                                    'transfer_available' => $attraction['transfer_available'] ?? 0,
                                    'transfer_type' => $attraction['transfer_type'] ?? 'none'
                                ];
                            }, $dayData['attractions'] ?? []),
                            'guide' => $dayData['guide'],
                            'arrival_pickup' => $dayData['arrival_pickup'],
                            'departure_service' => $dayData['departure_service']
                        ];
                    }
                    
                    $itineraryData = json_encode($extractedItinerary);
                    Log::debug('Extracted itinerary data', ['data' => $itineraryData]);
                }
                
                // Extract hotel data from day_wise_itinerary
                if (is_array($dayWiseData) && isset($dayWiseData['hotels'])) {
                    $extractedHotels = [];
                    
                    foreach ($dayWiseData['hotels'] as $hotel) {
                        $extractedHotels[$hotel['id']] = [
                            'name' => $hotel['name'],
                            'city' => $hotel['city'],
                            'selected_days' => $hotel['days']
                        ];
                    }
                    
                    $hotelJsonData = json_encode($extractedHotels);
                    Log::debug('Extracted hotel data', ['data' => $hotelJsonData]);
                }
            }
            
            // Ensure we have valid JSON data
            if (empty($itineraryData) || $itineraryData === 'null') {
                $itineraryData = json_encode([]);
            }
            
            if (empty($hotelJsonData) || $hotelJsonData === 'null') {
                $hotelJsonData = json_encode([]);
            }
            
            // Extract selected attractions, guides, etc. from day_wise_itinerary
            $selectedAttractions = [];
            $selectedGuides = [];
            
            if (!empty($dayWiseItineraryRaw)) {
                $dayWiseData = json_decode($dayWiseItineraryRaw, true);
                
                if (is_array($dayWiseData) && isset($dayWiseData['itinerary'])) {
                    foreach ($dayWiseData['itinerary'] as $dayData) {
                        // Extract attractions
                        if (!empty($dayData['attractions'])) {
                            foreach ($dayData['attractions'] as $attraction) {
                                $selectedAttractions[] = [
                                    'id' => $attraction['attraction_id'],
                                    'name' => $attraction['name'],
                                    'day' => $dayData['day']
                                ];
                            }
                        }
                        
                        // Extract guides
                        if (!empty($dayData['guide'])) {
                            $selectedGuides[] = [
                                'id' => $dayData['guide']['id'],
                                'name' => $dayData['guide']['name'],
                                'day' => $dayData['day']
                            ];
                        }
                    }
                }
            }

            $lastPackage = Package::withTrashed()->orderBy('created_at', 'desc')->first();
            $package_max_id = $lastPackage->package_id ?? 0;
            $packageId = CommonHelper::createId($package_max_id);
            while (Package::where('package_id', $packageId)->exists()) {
                $packageId = CommonHelper::createId($packageId);
            }

            $dmc_id = null;
            $user = Auth::user();
            if ($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 23) {
                $dmc_id = $request->dmc ?? null;
            } elseif ($user->role_id == 11 || $user->role_id == 20) {
                $dmc_id = $user->userId;
            } elseif($user->role_id == 35 || $user->role_id == 130 || $user->role_id == 132 || $user->role_id == 133 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138){
                $userdmc = User::where('userId', $user->created_by)->first();
                $dmc_id = $userdmc->userId;
            }
            elseif($user->role_id == 74 || $user->role_id == 75 || $user->role_id == 77){
                $user_product_head = User::where('userId', $user->created_by)->first();
                $user_product_head_dmc = User::where('userId', $user_product_head->created_by)->first();
                $dmc_id = $user_product_head_dmc->userId;
            }
            elseif($user->role_id == 84 || $user->role_id == 93 || $user->role_id == 102){
                $user_product_manager = User::where('userId', $user->created_by)->first();
                $user_product_head = User::where('userId', $user_product_manager->created_by)->first();
                $user_product_head_dmc = User::where('userId', $user_product_head->created_by)->first();
                $dmc_id = $user_product_head_dmc->userId;
            }


            // Create the package
            $package = Package::create([
                'package_id' => $packageId,
                'title' => $validated['title'],
                'destination' => $validated['destination'],
                'city' => $validated['city'],
                'category' => $validated['category'],
                'duration_days' => $validated['duration_days'],
                'package_type' => $request->input('package_type'),
                'description' => $validated['description'],
                'price_adult' => $validated['price_adult'],
                'price_senior' => $validated['price_senior'],
                'price_child' => $validated['price_child'],
                'max_pax' => $request->input('max_pax') ?? null,
                'selected_hotels' => $request->input('selected_hotels'),
                'selected_attractions' => json_encode($selectedAttractions),
                'selected_guide' => json_encode($selectedGuides),
                'selected_restaurants' => json_encode([]),
                'max_hotels' => null,
                'max_attractions' => null,
                'max_restaurants' => null,
                'attraction_with_transfer' => false,
                'transfer_notes' => null,
                'entry_port' => false,
                'exit_port' => false,
                'main_image' => $mainImagePath,
                'gallery_images' => json_encode($galleryImages),
                'start_date' => $validated['start_date'],
                'expire_date' => $validated['expiry_date'],
                'inclusions' => $validated['inclusions'],
                'exclusions' => $validated['exclusions'],
                'terms_conditions' => $validated['terms_conditions'],
                'status' => $validated['status'],
                'created_by' => Auth::user()->userId,
                'updated_by' => Auth::user()->userId,
                'itinerary' => $request->day_wise_itinerary,
                'dmc_id' => $dmc_id,
                'child_max_age' => $validated['child_max_age']
            ]);
            
            DB::commit();

            return redirect()->route('packages.index')
                ->with('success', 'Package created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Package Creation Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return back()->withInput()
                ->with('error', 'Failed to create package: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified package
     */
    public function show($package_id)
    {
        $package_id = Crypt::decrypt($package_id);
        $package = Package::with(['creator', 'updater'])->where('package_id', $package_id)->firstOrFail();
        
        // Increment views
        $package->incrementViews();
        
        return view('package.show-predefined', compact('package'));
    }

    /**
     * Show the form for editing the specified package
     */
    public function edit($package_id)
    {
        $package = Package::where('package_id', $package_id)->first();
        $city = $package->city;
        $countries = Country::where('is_active', 1)->orderBy('name')->get();

        $hotels = Hotel::where('city', $city)->get();
        $attractions = Attraction::where('location', $city)->get();
        $restaurants = Restaurant::where('city', $city)->get();
        $guides = Guide::where('city', $city)->get();

        $categories = Package::CATEGORIES;

        return view('package.edit-predefined', compact('package', 'countries', 'categories', 'hotels', 'attractions', 'restaurants', 'guides'));
    }

    /**
     * Update the specified package
     */
    public function update(Request $request, $package_id)
    {
        $package = Package::where('package_id', $package_id)->first();

        // Validation
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'duration_days' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'price_adult' => 'required|numeric|min:0',
            'price_senior' => 'nullable|numeric|min:0',
            'price_child' => 'nullable|numeric|min:0',
            'max_pax' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'expiry_date' => 'required|date|after:start_date',
            'selected_hotels' => 'nullable|array',
            'selected_attractions' => 'nullable|array',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'available_dates' => 'nullable|array',
            'inclusions' => 'nullable|string',
            'exclusions' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'status' => 'required'
        ]);

        try {
            DB::beginTransaction();
            
            // Log the JSON data for debugging
            Log::info('Package Update JSON Data:', [
                'itinerary_json_data' => $request->input('itinerary_json_data'),
                'hotel_json_data' => $request->input('hotel_json_data'),
                'day_wise_itinerary' => $request->input('day_wise_itinerary')
            ]);

            $updateData = [
                'title' => $validated['title'],
                'destination' => $validated['destination'],
                'category' => $validated['category'],
                'duration_days' => $validated['duration_days'],
                'description' => $validated['description'],
                'price_adult' => $validated['price_adult'],
                'price_senior' => $validated['price_senior'],
                'price_child' => $validated['price_child'],
                'max_pax' => $validated['max_pax'],
                'start_date' => $validated['start_date'],
                'expire_date' => $validated['expiry_date'],
                'selected_hotels' => $this->processSelectedItems($request->input('selected_hotels', [])),
                'selected_attractions' => $this->processSelectedItems($request->input('selected_attractions', [])),
                'selected_guide' => $this->processSelectedItems($request->input('selected_guide', [])),
                'selected_restaurants' => $this->processSelectedItems($request->input('selected_restaurants', [])),
                'hotel_json_data' => $request->input('hotel_json_data'),
                'max_hotels' => $request->input('hotel-select-count'),
                'max_attractions' => $request->input('attraction-select-count'),
                'max_restaurants' => $request->input('restaurant-select-count'),
                'available_dates' => array_filter($request->input('available_dates', [])),
                'inclusions' => $validated['inclusions'],
                'exclusions' => $validated['exclusions'],
                'terms_conditions' => $validated['terms_conditions'],
                'status' => $validated['status'],
                'itinerary' => $request->input('itinerary_json_data'),
                'hotel_json_data' => $request->input('hotel_json_data'),
                'updated_by' => Auth::user()->userId
            ];

            // Handle main image upload using CommonHelper
            if ($request->hasFile('main_image')) {
                $imageData = CommonHelper::image_path('file_storage', $request->file('main_image'));
                if (!empty($imageData['master_value'])) {
                    $updateData['main_image'] = $imageData['master_value'];
                }
            }

            // Handle gallery images upload using CommonHelper
            if ($request->hasFile('gallery_images')) {
                $galleryImages = [];
                foreach ($request->file('gallery_images') as $image) {
                    $imageData = CommonHelper::image_path('file_storage', $image);
                    if (!empty($imageData['master_value'])) {
                        $galleryImages[] = $imageData['master_value'];
                    }
                }
                $updateData['gallery_images'] = $galleryImages;
            }

            $package->update($updateData);
            DB::commit();
            return redirect()->route('packages.index')->with('success', 'Package updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update package: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified package
     */
    public function destroy($package_id)
    {
        try {
            $package_id = Crypt::decrypt($package_id);
            $package = Package::where('package_id', $package_id)->first();
            
            // Note: Image cleanup is handled by the storage system configured in CommonHelper
            // The actual files will be managed based on the file_storage setting (local/s3/azure)
            
            $package->delete();
            
            return redirect()->route('packages.index')->with('success', 'Package deleted successfully!');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete package: ' . $e->getMessage());
        }
    }

    /**
     * Get cities by country name (for AJAX)
     */
    public function getCitiesByCountry($country)
    {
        $cities = \App\Models\City::where('country', $country)->get(['city_id', 'name']);
        return response()->json($cities);
    }

    /**
     * Get hotels by city (AJAX)
     */
    public function getHotelsByCity($city)
    {
        $hotels = \App\Models\Hotel::where('city', $city)->get(['hotel_unique_id', 'name', 'city','main_image', 'weekend_days']);
        return response()->json($hotels);
    }

    /**
     * Get attractions by city (AJAX)
     */
    public function getAttractionsByCity($city)
    {
        $attractions = \App\Models\Attraction::where('location', $city)->get(['attraction_id', 'name', 'location', 'master_image', 'adult_price', 'child_price']);
        return response()->json($attractions);
    }

    /**
     * Get guides by city (AJAX)
     */
    public function getGuidesByCity($city)
    {
        $guides = \App\Models\Guide::where('city', $city)
            ->with(['languages' => function ($query) {
                $query->select('guide_id', 'language'); // columns in guide_language table
            }])
            ->get(['guide_id', 'name', 'contact_no', 'city', 'status', 'hourly_price', 'two_hour_price', 'four_hour_price', 'six_hour_price', 'eight_hour_price', 'ten_hour_price', 'twelve_hour_price', 'night_surcharge', 'night_start_time', 'night_end_time']);

        // Map to flatten language strings if needed
        $guides->transform(function ($guide) {
            return [
                'guide_id'   => $guide->guide_id,
                'name'       => $guide->name,
                'contact_no' => $guide->contact_no,
                'languages'  => $guide->languages->pluck('language')->toArray(),
                'hourly_price' => $guide->hourly_price ?? null,
                'two_hour_price' => $guide->two_hour_price ?? null,
                'four_hour_price' => $guide->four_hour_price ?? null,
                'six_hour_price' => $guide->six_hour_price ?? null,
                'eight_hour_price' => $guide->eight_hour_price ?? null,
                'ten_hour_price' => $guide->ten_hour_price ?? null,
                'twelve_hour_price' => $guide->twelve_hour_price ?? null,
                'night_surcharge' => $guide->night_surcharge ?? null,
                'night_start_time' => $guide->night_start_time ?? null,
                'night_end_time' => $guide->night_end_time ?? null,
            ];
        });

        return response()->json($guides);
    }

    /**
     * Get restaurants by city (AJAX) - through hotels in that city
     */
    public function getRestaurantsByCity($city)
    {
        $restaurants = Restaurant::where('city', $city)->get(['restaurant_id', 'name', 'city', 'cuisine', 'bf_price', 'lunch_price', 'dinner_price', 'breakfast_available', 'lunch_available', 'dinner_available']);
        return response()->json(['restaurants' => $restaurants]);
    }

    /**
     * Get meals by restaurant (AJAX) for package definition
     * meals.type: 1=Buffet, 2=Set Menu, 3=A la carte
     */
    public function getMealsByRestaurant($restaurantId)
    {
        $typeMap = [
            1 => 'Buffet',
            2 => 'Set Menu',
            3 => 'A la carte',
        ];
        try {
            $restaurantId = Crypt::decrypt($restaurantId);
        } catch (\Throwable $e) {
            // Support plain numeric ids from package definition UI.
            $restaurantId = is_numeric($restaurantId) ? (int) $restaurantId : 0;
        }

        $meals = Meal::where('restaurant_id', $restaurantId)
            ->orderBy('type')
            ->get(['id', 'meal_id', 'restaurant_id', 'type', 'item_type', 'adult_price', 'child_price']);

        $rows = $meals->map(function ($m) use ($typeMap) {
            $typeInt = (int) ($m->type ?? 0);
            return [
                'id' => $m->id,
                'meal_id' => $m->meal_id ?? $m->id,
                'restaurant_id' => $m->restaurant_id,
                'type' => $typeInt,
                'type_label' => $typeMap[$typeInt] ?? ('Type ' . $typeInt),
                'adult_price' => $m->adult_price != null ? (float) $m->adult_price : null,
                'child_price' => $m->child_price != null ? (float) $m->child_price : null,
            ];
        })->values();

        return response()->json(['meals' => $rows]);
    }

    /**
     * Get ports by country (AJAX) for package definition transfers
     */
    public function getPortsByCountry($country)
    {
        $ports = Port::where('country', $country)
            ->where('status', 1)
            ->orderBy('port_name')
            ->get(['port_id', 'port_name', 'country']);
        return response()->json($ports);
    }

    /**
     * Get transport by city (AJAX)
     */
    public function getTransportByCity($city)
    {
        $user = Auth::user();
        $dmc_id = CommonHelper::getDmcId($user);
        if($dmc_id){
            $transport = Vehicle::where('city', $city)->where('dmc_id', $dmc_id)->get(['vehicle_id', 'vehicle_name as name', 'city', 'vehicle_type', 'base_price']);
            return response()->json($transport);
        }else{
            return response()->json(['error' => 'You are not authorized to view this page.']);
        }
    }

    /**
     * Show the form for creating a package definition (no day-wise itinerary)
     */
    public function createDefinition()
    {
        $countries = Country::where('is_active', 1)->orderBy('name')->get();
        return view('package.package-definition', compact('countries'));
    }

    /**
     * Get room types by hotel (AJAX) for package definition
     */
    /**
     * Get rooms by hotel (AJAX) for package definition.
     * Uses the rooms table; Room.hotel_id = Hotel.hotel_unique_id.
     */
    public function getRoomTypesByHotel($hotelId)
    {
        
        $rooms = Room::where('hotel_id', $hotelId)
            ->get([
                'room_id',
                'id',
                'hotel_id',
                'room_type',
                'no_of_room',
                'weekday_price',
                'weekend_price',
                'double_weekday_price',
                'double_weekend_price',
                'dimension',
                'breakfast',
                'breakfast_included',
                'lunch',
                'dinner',
                'master_image',
                'features',
            ]);

        // Map to same shape as before for frontend (id, name) + extra room fields
        $roomTypes = $rooms->map(function ($room) {
            return [
                'id'           => $room->room_id ?? $room->id,
                'room_id'      => $room->room_id ?? $room->id,
                'name'         => $room->room_type ?: ('Room ' . ($room->room_id ?? $room->id)),
                'room_type'    => $room->room_type,
                'no_of_room'   => $room->no_of_room,
                'weekday_price'=> ceil(($room->double_weekday_price)/2),
                'weekend_price'=> ceil(($room->double_weekend_price)/2),
                'dimension'    => $room->dimension,
                'breakfast'    => $room->breakfast,
                'breakfast_included' => $room->breakfast_included,
                'lunch'        => $room->lunch,
                'lunch_included'=> $room->lunch_included,
                'dinner'       => $room->dinner,
                'dinner_included'=> $room->dinner_included,
                'master_image' => $room->master_image,
                'features'     => $room->features,
            ];
        });

        return response()->json(['room_types' => $roomTypes]);
    }

    /**
     * Get bed types by room (AJAX) for package definition.
     * Uses beds.room_id -> rooms.room_id
     */
    public function getBedsByRoom($roomId)
    {
        $beds = Bed::where('room_id', $roomId)->get();

        $bedTypes = $beds->map(function ($bed) {
            return [
                'room_type' => (string) ($bed->room_type ?? ''),
                'extra_bed' => (int) ($bed->extra_bed ?? 0) === 1,
                'bed_id' => (int) ($bed->bed_id ?? 0),
                'extra_bed_type' => (string) ($bed->extra_bed_type ?? ''),
            ];
        })
        ->filter(function ($bed) {
            return $bed['room_type'] !== '';
        })
        ->unique(function ($bed) {
            return strtolower($bed['room_type']) . '|' . ($bed['extra_bed'] ? '1' : '0');
        })
        ->values();

        return response()->json(['beds' => $bedTypes]);
    }

    /**
     * Get tickets by attraction (AJAX) for package definition.
     */
    public function getTicketsByAttraction($attractionId)
    {
        $tickets = Ticket::where('attraction_id', $attractionId)
            ->where('status', 1)
            ->orderBy('name')
            ->get([
                'id',
                'ticket_id',
                'name',
                'adult_price',
                'child_price',
                'senior_adult_price',
                'attraction_id',
            ]);

        return response()->json(['tickets' => $tickets]);
    }

    /**
     * Store a package definition (no day-wise itinerary)
     */
    public function storeDefinition(Request $request)
    {
        try {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'duration_days' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'price_adult' => 'nullable|numeric|min:0',
            'price_senior' => 'nullable|numeric|min:0',
            'price_child' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'expiry_date' => 'required|date|after:start_date',
            'main_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'inclusions' => 'nullable|string',
            'exclusions' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'status' => 'required',
            'child_max_age' => 'nullable|integer',
            'price_data' => 'nullable|json',
            'total_price' => 'nullable|numeric|min:0',
            'markup_type' => 'nullable|in:percentage,flat',
            'markup_amount' => 'nullable|numeric|min:0',
        ]);
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to validate package definition. ' . $e->getMessage()]);
        }

        try {
            DB::beginTransaction();
            $mainImagePath = null;
            if ($request->hasFile('main_image')) {
                $imageData = CommonHelper::image_path('file_storage', $request->file('main_image'));
                if (!empty($imageData['master_value'])) {
                    $mainImagePath = $imageData['master_value'];
                }
            }
            $galleryImages = [];
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $image) {
                    $imageData = CommonHelper::image_path('file_storage', $image);
                    if (!empty($imageData['master_value'])) {
                        $galleryImages[] = $imageData['master_value'];
                    }
                }
            }

            $selectedHotels = $request->input('selected_hotels', '[]');
            $selectedAttractions = $request->input('selected_attractions', '[]');
            $selectedRestaurants = $request->input('selected_restaurants', '[]');
            $localTransfers = $request->input('local_transfers', '[]');
            $priceData = $request->input('price_data', '[]');
            $totalPrice = $request->input('total_price');
            $markupType = $request->input('markup_type');
            $markupAmount = $request->input('markup_amount');

            // Decode main JSON payloads once so we can derive price_data server-side reliably.
            // This avoids depending on frontend JS to send price_data correctly.
            $decodedHotels = is_string($selectedHotels) ? (json_decode($selectedHotels, true) ?: []) : ($selectedHotels ?: []);
           
            foreach ($decodedHotels as &$hotel) {
                $hotel['city'] = $request->input('city') ?? '';
            }
            unset($hotel);
            
            $decodedAttractions = is_string($selectedAttractions) ? (json_decode($selectedAttractions, true) ?: []) : ($selectedAttractions ?: []);
            $decodedRestaurants = is_string($selectedRestaurants) ? (json_decode($selectedRestaurants, true) ?: []) : ($selectedRestaurants ?: []);
            $decodedLocalTransfers = is_string($localTransfers) ? (json_decode($localTransfers, true) ?: []) : ($localTransfers ?: []);
            $decodedIndependentGuides = json_decode($request->input('definition_independent_guide', '[]'), true) ?: [];
            $decodedArrivalVehicles = json_decode($request->input('arrival_vehicles', '[]'), true) ?: [];
            
            $decodedDepartureVehicles = json_decode($request->input('departure_vehicles', '[]'), true) ?: [];

            // Persisted direct columns (JSON) for transfer/arrival/departure
            // transfer_data: local transfer list
            // arrival_data / departure_data: dedicated arrival/departure section config
            $transferDataPayload = is_array($decodedLocalTransfers) ? $decodedLocalTransfers : [];
            $arrivalDataPayload = [
                'enabled' => (int) $request->input('arrival_pickup', 0) === 1,
                'pickup_port_id' => $request->input('arrival_pickup_port_id') ?: null,
                'dropoff_hotel_id' => $request->input('arrival_dropoff_hotel_id') ?: null,
                'vehicles' => is_array($decodedArrivalVehicles) ? $decodedArrivalVehicles : [],
            ];
            
            $departureDataPayload = [
                'enabled' => (int) $request->input('departure_service', 0) === 1,
                'pickup_hotel_id' => $request->input('departure_pickup_hotel_id') ?: null,
                'dropoff_port_id' => $request->input('departure_dropoff_port_id') ?: null,
                'vehicles' => is_array($decodedDepartureVehicles) ? $decodedDepartureVehicles : [],
            ];

            // price_data is built on the frontend and only carries four keys:
            //   total_price, markup_type, markup_amount, final_price
            // Accept it as-is and recompute final_price server-side so the stored
            // value is tamper-proof even if the hidden field is edited.
            $decodedPriceData = is_string($priceData) ? (json_decode($priceData, true) ?: []) : ($priceData ?: []);
            if (!is_array($decodedPriceData)) {
                $decodedPriceData = [];
            }

            $totalPriceNum = ($totalPrice !== null && $totalPrice !== '' && is_numeric($totalPrice))
                ? (float) $totalPrice
                : (isset($decodedPriceData['total_price']) && is_numeric($decodedPriceData['total_price']) ? (float) $decodedPriceData['total_price'] : 0.0);
            $markupAmountNum = ($markupAmount !== null && $markupAmount !== '' && is_numeric($markupAmount))
                ? (float) $markupAmount
                : (isset($decodedPriceData['markup_amount']) && is_numeric($decodedPriceData['markup_amount']) ? (float) $decodedPriceData['markup_amount'] : 0.0);
            $markupTypeClean = !empty($markupType) ? $markupType : ($decodedPriceData['markup_type'] ?? null);

            // Round UP to the nearest multiple of 5 for both total and final price.
            $ceilToFive = function ($n) {
                $num = (float) $n;
                if (!is_finite($num) || $num <= 0) return 0.0;
                return ceil($num / 5) * 5;
            };
            $totalPriceNum = $ceilToFive($totalPriceNum);

            // Final price rule (applied to the ceil-rounded total):
            //   flat       => final = total + markupAmount
            //   percentage => final = total + (total * markupAmount / 100)
            //   none       => final = total
            $finalPriceNum = $totalPriceNum;
            if ($markupTypeClean === 'flat') {
                $finalPriceNum = $totalPriceNum + $markupAmountNum;
            } elseif ($markupTypeClean === 'percentage') {
                $finalPriceNum = $totalPriceNum + ($totalPriceNum * $markupAmountNum / 100);
            }
            $finalPriceNum = $ceilToFive($finalPriceNum);

            $decodedPriceData = [
                'total_price' => round($totalPriceNum, 2),
                'markup_type' => $markupTypeClean ?: null,
                'markup_amount' => $markupTypeClean ? round($markupAmountNum, 2) : null,
                'final_price' => round($finalPriceNum, 2),
            ];

            $definitionData = [
                'hotels' => $decodedHotels,
                'attractions' => $decodedAttractions,
                'restaurants' => $decodedRestaurants,
                'arrival_pickup' => (int) $request->input('arrival_pickup', 0),
                'departure_service' => (int) $request->input('departure_service', 0),
                'independent_guide' => $decodedIndependentGuides,
                'local_transfers' => $decodedLocalTransfers,
                'price_data' => $decodedPriceData,
                'total_price' => ($totalPrice !== null && $totalPrice !== '' && is_numeric($totalPrice)) ? (float) $totalPrice : null,
                'markup_type' => $markupType ?: null,
                'markup_amount' => ($markupAmount !== null && $markupAmount !== '' && is_numeric($markupAmount)) ? (float) $markupAmount : null,
            ];

            $user = Auth::user();
            $dmc_id = $user->userId;
            if (in_array($user->role_id, [1, 2, 23])) {
                $dmc_id = $request->input('dmc_id', $user->userId);
            }

            $lastPackage = Package::withTrashed()->orderBy('created_at', 'desc')->first();
            $package_max_id = $lastPackage->package_id ?? 0;
            $packageId = CommonHelper::createId($package_max_id);
            while (Package::where('package_id', $packageId)->exists()) {
                $packageId = CommonHelper::createId($packageId);
            }

            $package = Package::create([
                'package_id' => $packageId,
                'title' => $validated['title'],
                'destination' => $validated['destination'],
                'city' => $validated['city'],
                'category' => $validated['category'],
                'duration_days' => $validated['duration_days'],
                'package_type' => 'definition',
                'description' => $validated['description'] ?? '',
                'price_adult' => $validated['price_adult'] ?? null,
                'price_senior' => $validated['price_senior'] ?? null,
                'price_child' => $validated['price_child'] ?? null,
                'child_max_age' => $validated['child_max_age'] ?? null,
                'start_date' => $validated['start_date'],
                'expire_date' => $validated['expiry_date'],
                'main_image' => $mainImagePath,
                'gallery_images' => json_encode($galleryImages),
                'inclusions' => $validated['inclusions'] ?? '',
                'exclusions' => $validated['exclusions'] ?? '',
                'terms_conditions' => $validated['terms_conditions'] ?? '',
                'status' => $validated['status'],
                'dmc_id' => $dmc_id,
                'created_by' => $user->userId,
                'updated_by' => $user->userId,
                'selected_hotels' => $decodedHotels,
                'selected_attractions' => $selectedAttractions,
                'selected_guide' => $request->input('definition_independent_guide', 'null'),
                'selected_restaurants' => $selectedRestaurants,
                // Store as JSON array (like selected_guide), letting Eloquent handle encoding via cast
                'price_data' => $decodedPriceData,
                'transfer_data' => $transferDataPayload,
                'arrival_data' => $arrivalDataPayload,
                'departure_data' => $departureDataPayload,
                'itinerary' => json_encode($definitionData),
            ]);

            DB::commit();
            return redirect()->route('packages.index')->with('success', 'Package definition created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Package definition store error: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to create package definition. '.$e->getMessage()]);
        }
    }

    /**
     * Process selected items array from request
     * 
     * @param array $items Array of selected items from request
     * @return array Processed items array
     */
    private function processSelectedItems($items)
    {
        if (empty($items)) {
            return [];
        }

        // If items is a JSON string, decode it
        if (is_string($items)) {
            $items = json_decode($items, true);
        }

        // If decoding failed or items is not an array, return empty array
        if (!is_array($items)) {
            return [];
        }

        // Process each item
        return array_map(function ($item) {
            // If item is a JSON string, decode it
            if (is_string($item)) {
                $decodedItem = json_decode($item, true);
                return is_array($decodedItem) ? $decodedItem : ['id' => $item];
            }
            return $item;
        }, $items);
    }


    public function predefinedPackageBookingList()
    {
        $user = Auth::user();
        if($user->role_id == 11 || $user->role_id == 33 || $user->role_id == 34 || $user->role_id == 36 || 
        $user->role_id == 37 || $user->role_id == 38 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 131 || $user->role_id == 132 ||
         $user->role_id == 133 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 ||
          $user->role_id == 137 || $user->role_id == 138 || $user->role_id == 126 || $user->role_id == 124 ||
           $user->role_id == 127 || $user->role_id == 125){
            if($user->role_id == 11 || $user->role_id == 20){
                $dmc_id = $user->userId;
            }
            //sales head
            elseif($user->role_id == 33 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
                $dmc_id = $user->created_by;
            }
            //operational head
            elseif($user->role_id == 34 || $user->role_id == 128 || $user->role_id == 131 || $user->role_id == 132 || $user->role_id == 133 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 137 || $user->role_id == 138){
                $dmc_id = $user->created_by;
            }
            //finance head
            elseif($user->role_id == 36 || $user->role_id == 129 || $user->role_id == 131 || $user->role_id == 133 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138){
                $dmc_id = $user->created_by;
            }
            //sales manager
            elseif($user->role_id == 37 || $user->role_id == 126 || $user->role_id == 124){
                $sales_manager_id = $user->userId;
                $sales_head_id = $user->created_by;
                $sales_head = User::where('userId', $sales_head_id)->first();
                $dmc_id = $sales_head->created_by;
            }
            //assistant sales manager
            elseif($user->role_id == 38 || $user->role_id == 127 || $user->role_id == 125){
                $assistant_sales_manager_id = $user->userId;
                $sales_manager_id = $user->created_by;
                $sales_manager = User::where('userId', $sales_manager_id)->first();
                $sales_head_id = $sales_manager->created_by;
                $sales_head = User::where('userId', $sales_head_id)->first();
                $dmc_id = $sales_head->created_by;
            }

            // Include the package and agent relationships to access package and agent details
            $query = PackageBooking::with(['package', 'agent'])
            ->where('dmc_id', $dmc_id);
            
            // For finance roles, only show bookings with payment_details
            if(in_array($user->role_id, [36, 129, 131, 133, 134, 136, 137, 138, 126, 127])) {
                $query->whereNotNull('payment_details');
            }
            
            $bookings = $query->orderBy('created_at', 'desc')->get();
        }else{
            $bookings = PackageBooking::with(['package', 'agent'])
            ->orderBy('created_at', 'desc')
            ->get();
        }
        
        return view('package.booking-list', compact('bookings'));
    }
    
    /**
     * Add payment for a package booking
     *
     * @param Request $request
     * @param string $package_id Package ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addPayment(Request $request, $booking_id)
    {
        try {
            // Validate the request
            $request->validate([
                'booking_id' => 'required',
                'payment_amount' => 'required|numeric|min:0.01',
                'payment_date' => 'required|date',
                'payment_type' => 'required|string',
                'transaction_id' => 'required|string',
            ]);
            
            // Find the booking by ID
            $booking = PackageBooking::where('booking_id', $booking_id)->first();
            
            if (!$booking) {
                return redirect()->route('predefined.package.booking.list')
                    ->with('error', 'Booking not found.');
            }
            
            // Prepare payment details for JSON storage
            $paymentDetails = [
                'payment_amount' => $request->payment_amount,
                'payment_date' => $request->payment_date,
                'payment_type' => $request->payment_type,
                'transaction_id' => $request->transaction_id,
                'status' => 0, // 0 = pending approval
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString()
            ];
            
            // Get existing payment details or initialize empty array
            $existingPaymentDetails = $booking->payment_details ? json_decode($booking->payment_details, true) : [];
            
            // Add new payment to existing payments (for multiple payments support)
            $existingPaymentDetails[] = $paymentDetails;
            
            // Update booking with payment details in JSON format
            $booking->payment_details = json_encode($existingPaymentDetails);
            $booking->save();
            
            return redirect()->route('predefined.package.booking.list')
                ->with('success', 'Payment details saved successfully.');
                
        } catch (\Exception $e) {
            return redirect()->route('predefined.package.booking.list')
                ->with('error', 'Failed to save payment details: ' . $e->getMessage());
        }
    }

    /**
     * Approve a payment for a package booking
     *
     * @param Request $request
     * @param string $booking_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function approvePayment(Request $request, $booking_id)
    {
        try {
            $request->validate([
                'payment_index' => 'required|integer|min:0',
            ]);

            $booking = PackageBooking::where('booking_id', $booking_id)->first();
            
            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found.'
                ], 404);
            }

            $paymentDetails = $booking->payment_details ? json_decode($booking->payment_details, true) : [];
            
            if (!isset($paymentDetails[$request->payment_index])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found.'
                ], 404);
            }

            // Update payment status to approved (1)
            $paymentDetails[$request->payment_index]['status'] = 1;
            $paymentDetails[$request->payment_index]['approved_at'] = now()->toDateTimeString();
            $paymentDetails[$request->payment_index]['approved_by'] = Auth::user()->userId;

            $booking->payment_details = json_encode($paymentDetails);
            $booking->status = 2;
            $booking->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment approved successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Decline a payment for a package booking
     *
     * @param Request $request
     * @param string $booking_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function declinePayment(Request $request, $booking_id)
    {
        try {
            $request->validate([
                'payment_index' => 'required|integer|min:0',
                'decline_reason' => 'required|string|max:500',
            ]);

            $booking = PackageBooking::where('booking_id', $booking_id)->first();
            
            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found.'
                ], 404);
            }

            $paymentDetails = $booking->payment_details ? json_decode($booking->payment_details, true) : [];
            
            if (!isset($paymentDetails[$request->payment_index])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found.'
                ], 404);
            }

            // Update payment status to declined (2)
            $paymentDetails[$request->payment_index]['status'] = 2;
            $paymentDetails[$request->payment_index]['declined_at'] = now()->toDateTimeString();
            $paymentDetails[$request->payment_index]['declined_by'] = Auth::user()->userId;
            $paymentDetails[$request->payment_index]['decline_reason'] = $request->decline_reason;

            $booking->payment_details = json_encode($paymentDetails);
            $booking->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment declined successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to decline payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm payment for a package booking
     *
     * @param Request $request
     * @param int $booking_id Booking ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmPayment(Request $request, $booking_id)
    {
        try {
            // Find the booking by ID
            $booking = PackageBooking::where('booking_id', $booking_id)->first();
            
            // Check if booking has payment details
            if (empty($booking->payment_amount) || empty($booking->payment_date) || empty($booking->transaction_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot confirm payment. Payment details are missing.'
                ], 400);
            }
            
            // Update booking status to confirmed (2)
            $booking->status = '2'; // Definite
            $booking->save();
        
            
            return response()->json([
                'success' => true,
                'message' => 'Payment confirmed successfully.'
            ]);
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a package booking
     *
     * @param Request $request
     * @param string $booking_id Booking ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancelBooking(Request $request, $booking_id)
    {
        try {
            // Validate the request
            $request->validate([
                'booking_id' => 'required',
                'cancel_reason' => 'nullable|string|max:1000',
            ]);

            // Find the booking by ID
            $booking = PackageBooking::where('booking_id', $booking_id)->first();
            
            if (!$booking) {
                return redirect()->route('predefined.package.booking.list')
                    ->with('error', 'Booking not found.');
            }

            // Check if user has permission to cancel (sales head roles)
            $user = Auth::user();
            if (!in_array($user->role_id, [33,34, 37, 38,124,125, 128, 129, 130,132,133, 134, 135, 136, 137,138])) {
                return redirect()->route('predefined.package.booking.list')
                    ->with('error', 'You do not have permission to cancel bookings.');
            }

            // Check if booking can be cancelled (only confirmed or definite status)
            if (!in_array($booking->status, ['1', '2'])) {
                return redirect()->route('predefined.package.booking.list')
                    ->with('error', 'This booking cannot be cancelled. Only confirmed or definite bookings can be cancelled.');
            }

            // Update booking status based on current status
            if ($booking->status == '1') {
                // Confirmed -> Cancel - Confirmed
                $booking->status = '7';
            } elseif ($booking->status == '2') {
                // Definite -> Refund - Pending
                $booking->status = '5';
            }

            // Add cancellation details
            $cancellationDetails = [
                'cancelled_by' => $user->userId,
                'cancelled_at' => now()->toDateTimeString(),
                'cancel_reason' => $request->cancel_reason ?: 'Cancelled by sales head',
                'previous_status' => $booking->status == '7' ? '1' : '2'
            ];

            // Store cancellation details in a separate field or extend existing data
            $existingData = [];
            if ($booking->booking_details) {
                if (is_string($booking->booking_details)) {
                    $existingData = json_decode($booking->booking_details, true) ?: [];
                } elseif (is_array($booking->booking_details)) {
                    $existingData = $booking->booking_details;
                }
            }
            $existingData['cancellation_details'] = $cancellationDetails;
            $booking->booking_details = json_encode($existingData);

            $booking->save();

            return redirect()->route('predefined.package.booking.list')
                ->with('success', 'Booking cancelled successfully.');
                
        } catch (\Exception $e) {
            return redirect()->route('predefined.package.booking.list')
                ->with('error', 'Failed to cancel booking: ' . $e->getMessage());
        }
    }

    /**
     * Process refund for a package booking
     *
     * @param Request $request
     * @param string $booking_id Booking ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processRefund(Request $request, $booking_id)
    {
        try {
            // Validate the request
            $request->validate([
                'booking_id' => 'required',
                'refund_reason' => 'nullable|string|max:1000',
            ]);

            // Find the booking by ID
            $booking = PackageBooking::where('booking_id', $booking_id)->first();
            
            if (!$booking) {
                return redirect()->route('predefined.package.booking.list')
                    ->with('error', 'Booking not found.');
            }

            // Check if user has permission to process refund (sales and finance roles)
            $user = Auth::user();
            if (!in_array($user->role_id, [36, 126, 127, 129, 131, 133, 134, 136, 137, 138])) {
                return redirect()->route('predefined.package.booking.list')
                    ->with('error', 'You do not have permission to process refunds.');
            }

            // Check if booking can be refunded (only refund-pending status)
            if ($booking->status != '5') {
                return redirect()->route('predefined.package.booking.list')
                    ->with('error', 'This booking cannot be refunded. Only refund-pending bookings can be processed.');
            }

            // Update booking status to refunded
            $booking->status = '6'; // Refunded

            // Add refund details
            $refundDetails = [
                'refunded_by' => $user->userId,
                'refunded_at' => now()->toDateTimeString(),
                'refund_reason' => $request->refund_reason ?: 'Refund processed',
                'previous_status' => '5'
            ];

            // Store refund details in booking_details
            $existingData = [];
            if ($booking->booking_details) {
                if (is_string($booking->booking_details)) {
                    $existingData = json_decode($booking->booking_details, true) ?: [];
                } elseif (is_array($booking->booking_details)) {
                    $existingData = $booking->booking_details;
                }
            }
            $existingData['refund_details'] = $refundDetails;
            $booking->booking_details = json_encode($existingData);

            $booking->save();

            return redirect()->route('predefined.package.booking.list')
                ->with('success', 'Refund processed successfully.');
                
        } catch (\Exception $e) {
            return redirect()->route('predefined.package.booking.list')
                ->with('error', 'Failed to process refund: ' . $e->getMessage());
        }
    }
}
