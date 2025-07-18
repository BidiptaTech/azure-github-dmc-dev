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
        if (!hasPermission('view attraction')) {
            abort(403, 'You do not have permission to access this page.');
        }

        $user = auth()->user();
        $packagedAttractions = [];
        
        if ($user->role_id == 4) {
            $dmc_ids = User::where('role_id', 11)->pluck('userId')->toArray();
            $packagedAttractions = PackagedAttraction::orderBy('updated_at', 'desc')->where('status', 1)
                ->orderBy('id', 'DESC')
                ->get();
        } elseif ($user->role_id == 3) {
            $packagedAttractions = PackagedAttraction::orderBy('updated_at', 'desc')->where('status', 1)->get();
        } elseif (in_array($user->role_id, [1, 2, 23])) {
            $packagedAttractions = PackagedAttraction::orderBy('updated_at', 'desc')->where('status', 1)->get();
        }
        elseif($user->role_id == 10 || $user->role_id == 19){
            $dmc_ids = User::where('master_dmc_id', $user->userId)->get()->pluck('userId')->toArray();
            $packagedAttractions = PackagedAttraction::orderBy('updated_at', 'desc')->whereIn('dmc_id', $dmc_ids)->get();
        }
        elseif ($user->role_id == 11 || $user->role_id == 20) {
            $packagedAttractions = PackagedAttraction::orderBy('updated_at', 'desc')->where('dmc_id', $user->userId)->get();
        }
        elseif(in_array($user->role_id, [25,26, 60,49, 92,89])){
            if($user->role_id == 25 || $user->role_id == 26){
                $master_dmc_id = $user->created_by;
            }
            elseif($user->role_id == 60 || $user->role_id == 49){
                $product_head = User::where('userId', $user->created_by)->first();
                $master_dmc_id = $product_head->created_by;
            }
            elseif($user->role_id == 92 || $user->role_id == 89){
                $product_manager = User::where('userId', $user->created_by)->first();
                $product_head = User::where('userId', $product_manager->created_by)->first();
                $master_dmc_id = $product_head->created_by;
            }
            $dmc_ids = User::where('master_dmc_id', $master_dmc_id)->get()->pluck('userId')->toArray();
            $packagedAttractions = PackagedAttraction::orderBy('updated_at', 'desc')->whereIn('dmc_id', $dmc_ids)->get();
        } 
        elseif($user->role_id == 35 || $user->role_id == 130 || $user->role_id == 132 || $user->role_id == 133 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138){
            $userdmc = User::where('userId', $user->created_by)->first();
            $dmc_id = $userdmc->userId;
            $packagedAttractions = PackagedAttraction::orderBy('updated_at', 'desc')->where('dmc_id', $dmc_id)->get();
        }
        elseif($user->role_id == 74){
            $assistant_product_manager_ids = User::where('created_by', $user->userId)->get()->pluck('userId')->toArray();
            if($assistant_product_manager_ids){
                $packagedAttractions = PackagedAttraction::orderBy('updated_at', 'desc')->whereIn('created_by', $assistant_product_manager_ids)->orWhere('created_by', $user->userId)->get();
            }else{
                $packagedAttractions = PackagedAttraction::orderBy('updated_at', 'desc')->where('created_by', $user->userId)->get();
            }
        }
        elseif($user->role_id == 93 || $user->role_id == 90){
            if($user->role_id != 111){
                $assistant_product_manager = User::where('created_by', $user->userId)->get()->pluck('userId')->toArray();
            }
            
            $packagedAttractions = PackagedAttraction::orderBy('updated_at', 'desc')->where('created_by', $user->userId)->get();
        }
        
        return view('packaged_attractions.list', compact('packagedAttractions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!hasPermission('create attraction')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $user = auth()->user();
        $dmc_id = null;
        if($user->role_id == 11 || $user->role_id == 20){
            $dmc_id = $user->userId;
        }
        elseif($user->role_id == 35 || $user->role_id == 130 || $user->role_id == 132 || $user->role_id == 133 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138){
            $userdmc = User::where('userId', $user->created_by)->first();
            $dmc_id = $userdmc->userId;
        }
        elseif($user->role_id == 74){
            $user_product_head = User::where('userId', $user->created_by)->first();
            $user_product_head_dmc = User::where('userId', $user_product_head->created_by)->first();
            $dmc_id = $user_product_head_dmc->userId;
        }
        elseif($user->role_id == 93){
            $user_product_manager = User::where('userId', $user->created_by)->first();
            $user_product_head = User::where('userId', $user_product_manager->created_by)->first();
            $user_product_head_dmc = User::where('userId', $user_product_head->created_by)->first();
            $dmc_id = $user_product_head_dmc->userId;
        }
        if($dmc_id){
            $attractions = Attraction::where('is_active', 1)
            ->whereRaw("dmc_id::jsonb @> ?", [json_encode([$dmc_id])])
            ->get();
        }
        else{
            $attractions = Attraction::where('is_active', 1)->get();
        }
        return view('packaged_attractions.create', compact('attractions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!hasPermission('create attraction')) {
            abort(403, 'You do not have permission to perform this action.');
        }
        
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
            $dmc_id = null;
            
            if ($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 23) {
                $dmc_id = $request->dmc ?? null;
            } elseif ($user->role_id == 11 || $user->role_id == 20) {
                $dmc_id = $user->userId;
            } elseif($user->role_id == 35 || $user->role_id == 130 || $user->role_id == 132 || $user->role_id == 133 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138){
                $userdmc = User::where('userId', $user->created_by)->first();
                $dmc_id = $userdmc->userId;
            }
            elseif($user->role_id == 74){
                $user_product_head = User::where('userId', $user->created_by)->first();
                $user_product_head_dmc = User::where('userId', $user_product_head->created_by)->first();
                $dmc_id = $user_product_head_dmc->userId;
            }
            elseif($user->role_id == 93){
                $user_product_manager = User::where('userId', $user->created_by)->first();
                $user_product_head = User::where('userId', $user_product_manager->created_by)->first();
                $user_product_head_dmc = User::where('userId', $user_product_head->created_by)->first();
                $dmc_id = $user_product_head_dmc->userId;
            }
            // Generate unique package ID
            $lastPackage = PackagedAttraction::withTrashed()->orderBy('created_at', 'desc')->first();
            $package_max_id = $lastPackage->package_attraction_id ?? 0;
            $packageId = CommonHelper::createId($package_max_id);
            while (PackagedAttraction::where('package_attraction_id', $packageId)->exists()) {
                $packageId = CommonHelper::createId($packageId);
            }
            
            
            // Process images - keep existing images and add new ones
            $allImages = [];

            // Add existing images if they exist in the request
            if ($request->has('existing_images')) {
                $allImages = array_merge($allImages, $request->existing_images);
            }

            // Add new uploaded images if any
            if ($request->hasFile('image')) {
                foreach ($request->file('image') as $image) {
                    $imageData = CommonHelper::image_path('file_storage', $image);
                    if (!empty($imageData['master_value'])) {
                        $allImages[] = $imageData['master_value'];
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
                'image' => !empty($allImages) ? json_encode($allImages) : null,
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
        if (!hasPermission('view attraction')) {
            abort(403, 'You do not have permission to access this page.');
        }
        
        $packagedAttraction = PackagedAttraction::findOrFail($id);
        return view('packaged_attractions.show', compact('packagedAttraction'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if (!hasPermission('edit attraction')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $packagedAttraction = PackagedAttraction::findOrFail($id);
        $attractions = Attraction::where('status', 1)->get();
        return view('packaged_attractions.edit', compact('packagedAttraction', 'attractions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!hasPermission('edit attraction')) {
            abort(403, 'You do not have permission to perform this action.');
        }
        try {
            $validator = Validator::make($request->all(), [
                'package_attraction_name' => 'required|string|max:255',
                'attractions' => 'required|array',
                'senior_citizen_price' => 'required|numeric|min:0',
                'adult_price' => 'required|numeric|min:0',
                'child_price' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'image' => 'nullable|array',
                
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $packagedAttraction = PackagedAttraction::where('package_attraction_id', $id)->first();
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

            // Process images - keep existing images and add new ones
            $allImages = [];

            // Add existing images if they exist in the request
            if ($request->has('existing_images')) {
                $allImages = array_merge($allImages, $request->existing_images);
            }

            // Add new uploaded images if any
            if ($request->hasFile('image')) {
                foreach ($request->file('image') as $image) {
                    $imageData = CommonHelper::image_path('file_storage', $image);
                    if (!empty($imageData['master_value'])) {
                        $allImages[] = $imageData['master_value'];
                    }
                }
            }

            // Update image field if we have images
            if (!empty($allImages)) {
                $updateData['image'] = json_encode($allImages);
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
        if (!hasPermission('delete attraction')) {
            abort(403, 'You do not have permission to perform this action.');
        }
        try {
            $packagedAttraction = PackagedAttraction::where('package_attraction_id', $id)->first();
            
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
        if (!hasPermission('view attraction')) {
            abort(403, 'You do not have permission to access this data.');
        }
        
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
        if (!hasPermission('create attraction')) {
            abort(403, 'You do not have permission to perform this action.');
        }
        
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
        if (!hasPermission('edit attraction')) {
            abort(403, 'You do not have permission to perform this action.');
        }
        
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
