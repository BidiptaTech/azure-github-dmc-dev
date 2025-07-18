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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\CommonHelper;
use Exception;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\PackageBooking;
use App\Models\Vehicles;

class PackageController extends Controller
{
    /**
     * Display the predefined packages admin interface
     */
    public function index(Request $request)
    {
        $dmc_id = null;
        $user = auth()->user();
        if($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 23){
            $dmc_id = $request->dmc ?? null;
        }
        elseif($user->role_id == 11){
            $dmc_id = $user->userId;
        }
        elseif($user->role_id == 33 || $user->role_id == 34|| $user->role_id == 35 || $user->role_id == 36 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 131 || $user->role_id == 132 || $user->role_id == 133 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138){
            $userdmc = User::where('userId', $user->created_by)->first();
            $dmc_id = $userdmc->userId;
        }
        elseif(in_array($user->role_id, array_merge(range(64, 78), [37]))) {
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
            'max_pax' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'expiry_date' => 'required|date|after:start_date',
            'main_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'inclusions' => 'nullable|string',
            'exclusions' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'status' => 'required',
            'package_type' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            // Log the JSON data for debugging
            \Log::info('Package Creation JSON Data:', [
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
            \Log::debug('Raw itinerary data', ['data' => $itineraryData]);
            \Log::debug('Raw hotel data', ['data' => $hotelJsonData]);
            
            // Process day-wise itinerary data for backward compatibility
            $dayWiseItineraryRaw = $request->input('day_wise_itinerary');
            \Log::debug('Day-wise itinerary data', ['data' => $dayWiseItineraryRaw]);
            
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
                    \Log::debug('Extracted itinerary data', ['data' => $itineraryData]);
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
                    \Log::debug('Extracted hotel data', ['data' => $hotelJsonData]);
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
            $user = auth()->user();
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
                'max_pax' => $validated['max_pax'],
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
                'created_by' => auth()->user()->userId,
                'updated_by' => auth()->user()->userId,
                'itinerary' => $request->day_wise_itinerary,
                'dmc_id' => $dmc_id
            ]);
            
            DB::commit();

            return redirect()->route('packages.index')
                ->with('success', 'Package created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Package Creation Error:', [
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
            \Log::info('Package Update JSON Data:', [
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
                'updated_by' => auth()->user()->userId
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
        $hotels = \App\Models\Hotel::where('city', $city)->get(['hotel_unique_id', 'name', 'city','main_image']);
        return response()->json($hotels);
    }

    /**
     * Get attractions by city (AJAX)
     */
    public function getAttractionsByCity($city)
    {
        $attractions = \App\Models\Attraction::where('location', $city)->get(['attraction_id', 'name', 'location','master_image']);
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
            ->get(['guide_id', 'name', 'contact_no', 'city', 'status']);

        // Map to flatten language strings if needed
        $guides->transform(function ($guide) {
            return [
                'guide_id'   => $guide->guide_id,
                'name'       => $guide->name,
                'contact_no' => $guide->contact_no,
                'languages'  => $guide->languages->pluck('language')->toArray(),
            ];
        });

        return response()->json($guides);
    }

    /**
     * Get restaurants by city (AJAX) - through hotels in that city
     */
    public function getRestaurantsByCity($city)
    {
        $restaurants = Restaurant::where('city', $city)->get(['restaurant_id', 'name', 'city', 'cuisine']);
        return response()->json(['restaurants' => $restaurants]);
    }

    /**
     * Get transport by city (AJAX)
     */
    public function getTransportByCity($city)
    {
        $transport = Vehicles::where('city', $city)->get(['vehicle_id', 'name', 'city','vehicle_type','vehicle_capacity','vehicle_image','vehicle_description','vehicle_price','vehicle_status']);
        return response()->json($transport);
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
        $user = auth()->user();

        if($user->role_id == 11 || $user->role_id == 33 || $user->role_id == 34 || $user->role_id == 36 || $user->role_id == 37 || $user->role_id == 38 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 131 || $user->role_id == 132 || $user->role_id == 133 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138){
            if($user->role_id == 11 || $user->role_id == 20){
                $dmc_id = $user->userId;
            }
            //sales head
            elseif($user->role_id == 33 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138
            ){
                $dmc_id = $user->created_by;
            }
            //operational head
            elseif($user->role_id == 34 || $user->role_id == 128 || $user->role_id == 131 || $user->role_id == 132 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 137 || $user->role_id == 138){
                $dmc_id = $user->created_by;
            }
            //finance head
            elseif($user->role_id == 36 || $user->role_id == 129 || $user->role_id == 131 || $user->role_id == 133 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138){
                $dmc_id = $user->created_by;
            }
            //sales manager
            elseif($user->role_id == 37){
                $sales_manager_id = $user->userId;
                $sales_head_id = $user->created_by;
                $sales_head = User::where('userId', $sales_head_id)->first();
                $dmc_id = $sales_head->created_by;
            }
            //assistant sales manager
            elseif($user->role_id == 38){
                $assistant_sales_manager_id = $user->userId;
                $sales_manager_id = $user->created_by;
                $sales_manager = User::where('userId', $sales_manager_id)->first();
                $sales_head_id = $sales_manager->created_by;
                $sales_head = User::where('userId', $sales_head_id)->first();
                $dmc_id = $sales_head->created_by;
            }

            // Include the package relationship to access package details
        $bookings = PackageBooking::with('package')
        
        ->orderBy('created_at', 'desc')
        ->get();
        }else{
            $bookings = PackageBooking::with('package')
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
            
            // Update booking with payment details
            $booking->status = '3'; // Mark as paid
            $booking->payment_amount = $request->payment_amount;
            $booking->payment_date = $request->payment_date;
            $booking->payment_mode = $request->payment_type; // Note: field name mismatch fixed
            $booking->transaction_id = $request->transaction_id;

            $booking->save();
            
            return redirect()->route('predefined.package.booking.list')
                ->with('success', 'Payment details saved successfully.');
                
        } catch (\Exception $e) {
            return redirect()->route('predefined.package.booking.list')
                ->with('error', 'Failed to save payment details: ' . $e->getMessage());
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
            $booking = PackageBooking::findOrFail($booking_id);
            
            // Check if booking has payment details
            if (empty($booking->payment_amount) || empty($booking->payment_date) || empty($booking->transaction_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot confirm payment. Payment details are missing.'
                ], 400);
            }
            
            // Update booking status to confirmed (2)
            $booking->status = '2'; // Confirmed
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
}
