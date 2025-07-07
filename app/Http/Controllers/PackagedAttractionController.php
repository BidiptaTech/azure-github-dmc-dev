<?php

namespace App\Http\Controllers;

use App\Models\Attraction;
use App\Models\PackagedAttraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Helpers\CommonHelper;
use App\Models\User;

class PackagedAttractionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $packagedAttractions = PackagedAttraction::latest()->get();
        return view('packaged_attractions.list', compact('packagedAttractions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $attractions = Attraction::where('status', 1)->get();
        return view('packaged_attractions.create', compact('attractions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // $validator = Validator::make($request->all(), [
            //     'package_attraction_name' => 'required|string|max:255',
            //     'attractions' => 'required|array',
            //     'senior_citizen_price' => 'required|numeric|min:0',
            //     'adult_price' => 'required|numeric|min:0',
            //     'child_price' => 'required|numeric|min:0',
            //     'description' => 'nullable|string',
            //     'images' => 'nullable|array',
            //     'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            // ]);

            // if ($validator->fails()) {
            //     return redirect()->back()
            //         ->withErrors($validator)
            //         ->withInput();
            // }

            $user = auth()->user();
            if($user->role_id == 11){
                $dmc_id = $user->userId;
            }
            elseif($user->role_id == 35){
                $product_head_id = $user->userId;
                $product_head = User::where('userId', $product_head_id)->first();
                $dmc_id = $product_head->created_by;
            }
            elseif($user->role_id == 74){
                $product_manager_id = $user->userId;
                $product_head = User::where('userId', $user->created_by)->first();
                $dmc_id = $product_head->created_by;
            }
            elseif($user->role_id == 93){
                $assistant_manager_id = $user->userId;
                $product_manager = User::where('userId', $user->created_by)->first();
                $product_head = User::where('userId', $product_manager->created_by)->first();
                $dmc_id = $product_head->created_by;
            }
            else{
                $dmc_id = null;
            }
            // Generate unique package ID
            $lastPackage = PackagedAttraction::withTrashed()->orderBy('created_at', 'desc')->first();
            $package_max_id = $lastPackage->package_attraction_id ?? 0;
            $packageId = CommonHelper::createId($package_max_id);
            while (PackagedAttraction::where('package_attraction_id', $packageId)->exists()) {
                $packageId = CommonHelper::createId($packageId);
            }
            
            
            // Process gallery images (all images including the first one)
            $galleryImages = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imageData = CommonHelper::image_path('file_storage', $image);
                    if (!empty($imageData['master_value'])) {
                        $galleryImages[] = $imageData['master_value'];
                    }
                }
            }



            // Create packaged attraction
            $packagedAttraction = PackagedAttraction::create([
                'name' => $request->package_attraction_name,
                'package_attraction_id' => $packageId,
                'attractions' => json_encode($request->attractions),
                'senior_citizen_price' => $request->senior_citizen_price,
                'adult_price' => $request->adult_price,
                'child_price' => $request->child_price,
                'description' => $request->description,
                'image' => !empty($galleryImages) ? json_encode($galleryImages) : null,
                'dmc_id' => $dmc_id,
                'status' => $request->status ?? 1,
                'created_by' => auth()->user()->userId,
            ]);

            return redirect()->route('packaged-attractions.index')
                ->with('success', 'Packaged attraction created successfully.');
        } catch (\Exception $e) {
            \Log::error('Error creating packaged attraction: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred while creating the packaged attraction: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $packagedAttraction = PackagedAttraction::findOrFail($id);
        return view('packaged_attractions.show', compact('packagedAttraction'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $packagedAttraction = PackagedAttraction::findOrFail($id);
        $attractions = Attraction::where('status', 1)->get();
        return view('packaged_attractions.edit', compact('packagedAttraction', 'attractions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'package_attraction_name' => 'required|string|max:255',
                'attractions' => 'required|array',
                'senior_citizen_price' => 'required|numeric|min:0',
                'adult_price' => 'required|numeric|min:0',
                'child_price' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'images' => 'nullable|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $packagedAttraction = PackagedAttraction::findOrFail($id);
            $updateData = [
                'name' => $request->package_attraction_name,
                'attractions' => json_encode($request->attractions),
                'senior_citizen_price' => $request->senior_citizen_price,
                'adult_price' => $request->adult_price,
                'child_price' => $request->child_price,
                'description' => $request->description,
                'status' => $request->status ?? $packagedAttraction->status,
                'updated_by' => auth()->user()->userId,
            ];

            // Process main image if provided
            if ($request->hasFile('images') && count($request->file('images')) > 0) {
                $image = $request->file('images')[0];
                $imageData = CommonHelper::image_path('file_storage', $image);
                if (!empty($imageData['master_value'])) {
                    $updateData['image'] = $imageData['master_value'];
                }
            }

            // Process gallery images if any
            $galleryImages = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imageData = CommonHelper::image_path('file_storage', $image);
                    if (!empty($imageData['master_value'])) {
                        $galleryImages[] = $imageData['master_value'];
                    }
                }
                
                if (!empty($galleryImages)) {
                    $updateData['gallery_images'] = json_encode($galleryImages);
                }
            }

            // Update packaged attraction
            $packagedAttraction->update($updateData);

            return redirect()->route('packaged-attractions.index')
                ->with('success', 'Packaged attraction updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Error updating packaged attraction: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred while updating the packaged attraction: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $packagedAttraction = PackagedAttraction::findOrFail($id);
            
            // We don't need to manually delete files when using CommonHelper
            // as it handles storage in a centralized way
            
            $packagedAttraction->delete();
            
            return redirect()->route('packaged-attractions.index')
                ->with('success', 'Packaged attraction deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Error deleting packaged attraction: ' . $e->getMessage());
            return redirect()->route('packaged-attractions.index')
                ->with('error', 'An error occurred while deleting the packaged attraction: ' . $e->getMessage());
        }
    }
    
    /**
     * Get attractions for select dropdown
     */
    public function getAttractions(Request $request)
    {
        $attractions = Attraction::where('status', 1);
        
        if ($request->has('search')) {
            $attractions->where('name', 'like', '%' . $request->search . '%');
        }
        
        $attractions = $attractions->get(['id', 'name']);
        
        return response()->json($attractions);
    }
    
    /**
     * Upload images via AJAX
     */
    public function uploadImages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $uploadedImages = [];
        foreach ($request->file('images') as $image) {
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('assets/images/packaged_attractions'), $imageName);
            $uploadedImages[] = [
                'path' => 'assets/images/packaged_attractions/' . $imageName,
                'name' => $imageName
            ];
        }

        return response()->json(['images' => $uploadedImages]);
    }
    
    /**
     * Remove image
     */
    public function removeImage($id)
    {
        $packagedAttraction = PackagedAttraction::findOrFail($id);
        
        // For simplicity, we'll just remove the main image
        if ($packagedAttraction->image && file_exists(public_path($packagedAttraction->image))) {
            unlink(public_path($packagedAttraction->image));
            $packagedAttraction->update(['image' => null]);
            return response()->json(['success' => true]);
        }
        
        return response()->json(['error' => 'Image not found'], 404);
    }
}
