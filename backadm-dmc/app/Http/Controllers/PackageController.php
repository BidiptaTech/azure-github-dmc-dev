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

class PackageController extends Controller
{
    /**
     * Display the predefined packages admin interface
     */
    public function index(Request $request)
    {
        $query = Package::query();

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
        try {
            // Validation with specific exception handling
            try {
                $validated = $request->validate([
                    'title' => 'required|string|max:255',
                    'destination' => 'required|string|max:255',
                    'city' => 'required|string|max:255',
                    'category' => 'required|string|max:255',
                    'duration_days' => 'required|integer|min:1',
                    'package_type' => 'required|string|max:255',
                    'description' => 'nullable|string',
                    'price_adult' => 'required|numeric|min:0',
                    'price_senior' => 'nullable|numeric|min:0',
                    'price_child' => 'nullable|numeric|min:0',
                    'max_pax' => 'required|integer|min:1',
                    'start_date' => 'required|date',
                    'expiry_date' => 'required|date|after:start_date',
                    'inclusions' => 'nullable|string',
                    'exclusions' => 'nullable|string',
                    'terms_conditions' => 'nullable|string',
                    'main_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
                    'status' => 'required',
                    'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
                    'selected_hotels' => 'nullable',
                    'selected_attractions' => 'nullable',
                    'selected_guide' => 'nullable',
                    'selected_restaurants' => 'nullable',
                    'hotel-select-count' => 'nullable|integer|min:1|max:5',
                    'attraction-select-count' => 'nullable|integer|min:1|max:5',
                    'restaurant-select-count' => 'nullable|integer|min:1|max:5',
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                dd($e->validator->getMessageBag());
                return redirect()->back()->withErrors($e->validator->getMessageBag())->withInput();
            } catch (\Exception $e) {
                dd($e->getMessage());
                return redirect()->back()->with('error', 'Validation error: ' . $e->getMessage())->withInput();
            }


            DB::beginTransaction();

            // Handle main image upload
            $mainImagePath = null;
            if ($request->hasFile('main_image')) {
                $imageData = CommonHelper::image_path('file_storage', $request->file('main_image'));
                $mainImagePath = $imageData['master_value'] ?? null;
            }

            // Handle gallery images
            $galleryImages = [];
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $image) {
                    $imageData = CommonHelper::image_path('file_storage', $image);
                    if (!empty($imageData['master_value'])) {
                        $galleryImages[] = $imageData['master_value'];
                    }
                }
            }

            // Handle hotels, attractions, guide and restaurants
            $selectedHotels = json_decode($request->input('selected_hotels', '[]'), true) ?: [];
            $selectedAttractions = json_decode($request->input('selected_attractions', '[]'), true) ?: [];
            $selectedGuide = json_decode($request->input('selected_guide', '{}'), true) ?: null;
            $selectedRestaurants = json_decode($request->input('selected_restaurants', '[]'), true) ?: [];

            $lastPackage = Package::withTrashed()->orderBy('created_at', 'desc')->first();
            $package_max_id = $lastPackage->package_id ?? 0;
            $packageId = CommonHelper::createId($package_max_id);
            while (Package::where('package_id', $packageId)->exists()) {
                $packageId = CommonHelper::createId($packageId);
            }

            // Create package
            $package = Package::create([
                'package_id' => $packageId,
                'title' => $validated['title'],
                'destination' => $validated['destination'],
                'city' => $validated['city'],
                'category' => $validated['category'],
                'duration_days' => $validated['duration_days'],
                'package_type' => $validated['package_type'],
                'description' => $validated['description'],
                'price_adult' => $validated['price_adult'],
                'price_senior' => $validated['price_senior'],
                'price_child' => $validated['price_child'],
                'max_pax' => $validated['max_pax'],
                'selected_hotels' => $selectedHotels,
                'selected_attractions' => $selectedAttractions,
                'selected_guide' => $selectedGuide,
                'selected_restaurants' => $selectedRestaurants,
                'max_hotels' => $request->input('hotel-select-count'),
                'max_attractions' => $request->input('attraction-select-count'),
                'max_restaurants' => $request->input('restaurant-select-count'),
                'main_image' => $mainImagePath,
                'gallery_images' => $galleryImages,
                'start_date' => $request->input('start_date'),
                'expire_date' => $request->input('expiry_date'),
                'inclusions' => $validated['inclusions'],
                'exclusions' => $validated['exclusions'],
                'terms_conditions' => $validated['terms_conditions'],
                'status' => $validated['status'],
                'created_by' => auth()->user()->userId,
                'updated_by' => auth()->user()->userId
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
    public function show($id)
    {
        $package = Package::with(['creator', 'updater'])->where('package_id', $id)->firstOrFail();
        
        // Increment views
        $package->incrementViews();
        
        return view('package.show-predefined', compact('package'));
    }

    /**
     * Show the form for editing the specified package
     */
    public function edit($id)
    {
        $package = Package::where('package_id', $id)->first();
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
    public function update(Request $request, $id)
    {
        $package = Package::where('package_id', $id)->first();

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

                'max_hotels' => $request->input('hotel-select-count'),
                'max_attractions' => $request->input('attraction-select-count'),
                'max_restaurants' => $request->input('restaurant-select-count'),
                'available_dates' => array_filter($request->input('available_dates', [])),
                'inclusions' => $validated['inclusions'],
                'exclusions' => $validated['exclusions'],
                'terms_conditions' => $validated['terms_conditions'],
                'status' => $validated['status'],
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
    public function destroy($id)
    {
        try {
            $package = Package::where('package_id', $id)->first();
            
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
        $hotels = \App\Models\Hotel::where('city', $city)->get(['hotel_unique_id', 'name', 'city']);
        return response()->json($hotels);
    }

    /**
     * Get attractions by city (AJAX)
     */
    public function getAttractionsByCity($city)
    {
        $attractions = \App\Models\Attraction::where('location', $city)->get(['attraction_id', 'name', 'location']);
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
}
