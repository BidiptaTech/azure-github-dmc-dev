<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Package;
use App\Models\Hotel;
use App\Models\Attraction;
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
                'inclusions' => 'nullable|string',
                'exclusions' => 'nullable|string',
                'terms_conditions' => 'nullable|string',
                'main_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
                'status' => 'required|in:draft,active',
                'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
                'selected_hotels' => 'nullable',
                'selected_attractions' => 'nullable',
                'hotel-select-count' => 'nullable|integer|min:1|max:5',
                'attraction-select-count' => 'nullable|integer|min:1|max:5',
            ]);

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

            // Handle hotels and attractions
            $selectedHotels = json_decode($request->input('selected_hotels', '[]'), true) ?: [];
            $selectedAttractions = json_decode($request->input('selected_attractions', '[]'), true) ?: [];

            // Create package
            $package = Package::create([
                'title' => $validated['title'],
                'destination' => $validated['destination'],
                'city' => $validated['city'],
                'category' => $validated['category'],
                'duration_days' => $validated['duration_days'],
                'description' => $validated['description'],
                'price_adult' => $validated['price_adult'],
                'price_senior' => $validated['price_senior'],
                'price_child' => $validated['price_child'],
                'max_pax' => $validated['max_pax'],
                'selected_hotels' => $selectedHotels,
                'selected_attractions' => $selectedAttractions,
                'max_hotels' => $request->input('hotel-select-count'),
                'max_attractions' => $request->input('attraction-select-count'),
                'main_image' => $mainImagePath,
                'gallery_images' => $galleryImages,
                'start_date' => $request->input('start_date'),
                'expire_date' => $request->input('expiry_date'),
                'inclusions' => $validated['inclusions'],
                'exclusions' => $validated['exclusions'],
                'terms_conditions' => $validated['terms_conditions'],
                'status' => $validated['status'],
                'created_by' => Auth::id(),
                'updated_by' => Auth::id()
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
        $package = Package::with(['creator', 'updater'])->findOrFail($id);
        
        // Increment views
        $package->incrementViews();
        
        return view('package.show-predefined', compact('package'));
    }

    /**
     * Show the form for editing the specified package
     */
    public function edit($id)
    {
        $package = Package::findOrFail($id);
        $countries = Country::where('is_active', 1)->orderBy('name')->get();
        $categories = Package::CATEGORIES;

        return view('package.edit-predefined', compact('package', 'countries', 'categories'));
    }

    /**
     * Update the specified package
     */
    public function update(Request $request, $id)
    {
        $package = Package::findOrFail($id);

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
            'selected_hotels' => 'nullable|array',
            'selected_attractions' => 'nullable|array',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'available_dates' => 'nullable|array',
            'inclusions' => 'nullable|string',
            'exclusions' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'status' => 'required|in:draft,active,inactive'
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
                'selected_hotels' => $this->processSelectedItems($request->input('selected_hotels', [])),
                'selected_attractions' => $this->processSelectedItems($request->input('selected_attractions', [])),
                'max_hotels' => $request->input('hotel-select-count'),
                'max_attractions' => $request->input('attraction-select-count'),
                'available_dates' => array_filter($request->input('available_dates', [])),
                'inclusions' => $validated['inclusions'],
                'exclusions' => $validated['exclusions'],
                'terms_conditions' => $validated['terms_conditions'],
                'status' => $validated['status'],
                'updated_by' => Auth::id()
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
            $package = Package::findOrFail($id);
            
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
        $hotels = \App\Models\Hotel::where('city', $city)->get(['hotel_unique_id', 'name']);
        return response()->json($hotels);
    }

    /**
     * Get attractions by city (AJAX)
     */
    public function getAttractionsByCity($city)
    {
        $attractions = \App\Models\Attraction::where('location', $city)->get(['attraction_id', 'name']);
        return response()->json($attractions);
    }
}
