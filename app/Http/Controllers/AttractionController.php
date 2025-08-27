<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\Facility;
use App\Models\Role;
use App\Models\User;
use App\Models\Hotel;
use App\Models\Attraction;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\CommonHelper;
use App\Models\Country;
use App\Models\City;
use Illuminate\Support\Facades\Crypt;

class AttractionController extends Controller
{
    /*
    * Display a listing of the Category.
    * Date 17-01-2025
    */
    public function index(Request $request)
    {
        if (!hasPermission('view attraction')) {
            abort(403, 'You do not have permission to access this page.');
        }

        $user = auth()->user();
        $attractions = [];
        if ($user->role_id == 4) {
            $dmc_ids = User::where('role_id', 11)->pluck('userId')->toArray();
            $attractions = Attraction::orderBy('created_at', 'desc')->whereIn('status', [4, 5, 1])
                // ->whereIn('dmc_id', $dmc_ids)
                ->get();
        } elseif ($user->role_id == 3) {
            $attractions = Attraction::orderBy('created_at', 'desc')->whereIn('status', [5, 1])->get();
        } elseif (in_array($user->role_id, [1, 2, 23])) {
            $attractions = Attraction::orderBy('created_at', 'desc')->whereIn('status', [1, 3])->get();
        }
        elseif($user->role_id == 10){
            $dmc_ids = User::where('master_dmc_id', $user->userId)->get()->pluck('userId')->toArray();
            $attractions = Attraction::orderBy('created_at', 'desc')->get()->filter(function($attraction) use ($dmc_ids) {
                $selectedDmcIds = $attraction->getSelectedDmcIds();
                return !empty(array_intersect($selectedDmcIds, $dmc_ids));
            });
        }
        elseif ($user->role_id == 11) {
            $attractions = Attraction::orderBy('created_at', 'desc')->get()->filter(function($attraction) use ($user) {
                return $attraction->hasSelectedByDmc($user->userId);
            });
        }
        elseif ($user->role_id == 20) {
            $attractions = Attraction::orderBy('created_at', 'desc')->get();
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
            $attractions = Attraction::orderBy('created_at', 'desc')->get()->filter(function($attraction) use ($dmc_ids) {
                $selectedDmcIds = $attraction->getSelectedDmcIds();
                return !empty(array_intersect($selectedDmcIds, $dmc_ids));
            });
        }
        elseif($user->role_id == 35 || $user->role_id == 130 || $user->role_id == 132 || $user->role_id == 133 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138){
            $attractions = Attraction::orderBy('created_at', 'desc')->get()->filter(function($attraction) use ($user) {
                return $attraction->hasSelectedByDmc($user->created_by);
            });
        }
        elseif($user->role_id == 74){
            $product_head = User::where('userId', $user->created_by)->first();
            $this_dmc_id = $product_head->created_by;

            if($this_dmc_id){
                $attractions = Attraction::orderBy('created_at', 'desc')->get()->filter(function($attraction) use ($this_dmc_id) {
                    return $attraction->hasSelectedByDmc($this_dmc_id);
                });
            }
        }
        elseif($user->role_id == 93 || $user->role_id == 90){
            $product_manager = User::where('userId', $user->created_by)->first();
            $product_head = User::where('userId', $product_manager->created_by)->first();
            $this_dmc_id = $product_head->created_by;

            if($this_dmc_id){
                $attractions = Attraction::orderBy('created_at', 'desc')->get()->filter(function($attraction) use ($this_dmc_id) {
                    return $attraction->hasSelectedByDmc($this_dmc_id);
                });
            }
        }
        return view('attractions.attraction', compact('attractions', 'user'));
    }

    public function attractionApproval(Request $request)
    {
        if (!hasPermission('view attractionapproval')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $auth_user = auth()->user();
        $pendingattractions = [];
        if($auth_user->role_id == 2 || $auth_user->role_id == 1 || $auth_user->role_id == 23){
            $pendingattractions = Attraction::with('user')
            ->where('status', 5)
            ->orderBy('created_at', 'desc')
            ->get();
        }
        
        return view('attractions.attraction-approval',compact('pendingattractions'));
    }

    public function editAttractionApproval($id)
    {

        $id = Crypt::decrypt($id);
        $attraction = Attraction::where('attraction_id',$id)->first();
        $country = [];
        $country = Country::where('is_active', 1)->get();
        $city = City::where('country', $attraction->country)->get();

        $same_attraction = null;
        if (!$attraction) {
            // Handle the case where the hotel is not found
            return redirect()->back()->with('error', 'Attraction not found.');
        }
        $same_attraction = Attraction::where('latitude', $attraction->latitude)
            ->where('longitude', $attraction->longitude)
            ->where('attraction_id', '!=', $id) // Ignore the current hotel
            ->where('status',1)
            ->first(); // Only check existence instead of fetching the first record
        
        return view('attractions.edit-attraction-approval', compact('attraction','country', 'same_attraction', 'city'));
    }

    public function updateAttractionApproval(Request $request, $id)
    {
        $id = Crypt::decrypt($id);
        //dd($id, $request->all());
        $request->validate([
            'name' => 'required|string|max:255',
            // 'adult_price' => 'required|numeric|min:0',
            // 'child_price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'city' => 'required|string|max:255',
            'all_images.*' => 'nullable|file|image|mimes:jpeg,png,jpg,gif',
            // 'price_shared' => 'required|numeric|min:0',
            // 'price_private' => 'required|numeric|min:0',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        $attraction = Attraction::where('attraction_id', $id)->firstOrFail();

        // Handling images
        $existingImages = $request->input('existing_images', []);
        
        // Get current additional images and find removed ones
        $currentImages = $attraction->additional_image ? json_decode($attraction->additional_image, true) : [];
        if(is_array($currentImages) && is_array($existingImages)) {
            $removedImages = array_diff($currentImages, $existingImages);
            // Delete removed images from Azure
            foreach($removedImages as $removedImage) {
                CommonHelper::deleteAzureImage($removedImage);
            }
        }
        
        $imagePaths = [];

        if ($request->hasFile('all_images')) {
            foreach ($request->file('all_images') as $image) {
                $pathData = CommonHelper::image_path('file_storage', $image);
                if (!empty($pathData['master_value'])) {
                    $imagePaths[] = $pathData['master_value'];
                }
            }
        }

        $attraction->additional_image = json_encode(array_merge($existingImages, $imagePaths));

        // Process master image
        if ($request->hasFile('master_image')) {
            // Delete old master image from Azure before uploading new one
            if ($attraction->master_image) {
                CommonHelper::deleteAzureImage($attraction->master_image);
            }
            
            $masterImagePath = CommonHelper::image_path('file_storage', $request->file('master_image'));
            if (!empty($masterImagePath['master_value'])) {
                $attraction->master_image = $masterImagePath['master_value'];
            }
        }

        // Determine status based on user role
        $auth_user = auth()->user();
        if($auth_user->role_id == 2 || $auth_user->role_id == 1 || $auth_user->role_id == 23){
            $status = 1;
        }else{
            $status = 5;
        }
        $status = $request->has('decline_status') ? 3 : $status;

        // Update attraction details
        $attraction->update([
            'name' => $request->input('name'),
            'adult_price' => $request->input('adult_price'),
            'child_price' => $request->input('child_price'),
            'price_shared' => $request->input('price_shared'),
            'price_private' => $request->input('price_private'),
            'country' => $request->input('country'),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'open_time' => json_encode($request->input('open_time')),
            'close_time' => json_encode($request->input('close_time')),
            'description' => $request->input('description'),
            'remarks' => $request->input('remarks'),
            'terms_conditions' => $request->input('terms_conditions'),
            'location' => $request->input('city'),
            'senior_min_age' => $request->input('senior_min_age'),
            'child_max_age' => $request->input('child_end_age'),
            'senior_adult_price' => $request->input('senior_adult_price'),
            'is_active' => $request->input('attraction_status') == 1 ? 1 : 0,
            'status' => $request->decline_status ?? $status,
            'night_opening' => $request->input('night_opening'),
            'morning_opening' => $request->input('morning_opening'),
            'afternoon_opening' => $request->input('afternoon_opening'),
            'evening_opening' => $request->input('evening_opening'),
            // 'is_complete' => 1,
        ]);

        // $attraction->night_opening = $request->night_opening;
        // $attraction->afternoon_opening = $request->afternoon_opening;
        // $attraction->morning_opening = $request->morning_opening;
        // $attraction->evening_opening = $request->evening_opening;

        // Redirect based on approval or decline status
        if ($request->has('decline_status')) {
            return redirect()->route('attractions.approval', ['attraction' => $attraction->attraction_id])
                ->with('error', 'Attraction Declined successfully');
        } else {
            return redirect()->route('attractions.approval', ['attraction' => $attraction->attraction_id])
                ->with('success', 'Attraction Approved successfully');
        }

        return redirect()->route('attraction.index')->with('success', 'Attraction details updated successfully.');
    }

    /*
    * Show the form for creating a new Attraction.
    * Date 17-01-2025
    */
    public function create()
    {
        if (!hasPermission('create attraction')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $country = Country::where('is_active', 1)->get();
        $authuser = auth()->user();
        if($authuser->role_id == 4){
            $dmcs = User::where('role_id', 11)->where('country', $authuser->country)->get();
        }elseif($authuser->role_id == 3){
            $dmcs = User::where('role_id', 11)->where('country', $authuser->country)->get();
        }else{
            $dmcs = User::where('role_id', 11)->get();
        }
        if(in_array($authuser->role_id, [11, 20, 35, 74, 93, 130, 132, 133, 135, 136, 137, 138])){
            $userCountry = User::where('userId', $authuser->userId)->first()->country;
            $cities = City::where('country', $userCountry)->get();
        }
        else{
            $userCountry = '';
            $cities = [];
        }
        $user__Id = $authuser->userId;
        //dd($dmcs);
        return view('attractions.create-attraction',compact('country', 'dmcs', 'userCountry', 'user__Id', 'cities'));
    }

    /*
    * Store a newly created Attraction.
    * Date 17-01-2025
    */
    public function store(Request $request)
    {
        // Validate the incoming request data
        // try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                // 'adult_price' => 'required|numeric|min:0',
                // 'child_price' => 'required|numeric|min:0',
                'description' => 'required|string',
                'location' => 'required|string|max:255',
                'master_image' => 'required|file|image',
                'all_images' => 'nullable|array',
                'all_images.*' => 'nullable|file|image',
                
                'latitude' => 'required|numeric|min:0',
                'longitude' => 'required|numeric|min:0',
                'morning_opening' => 'required',
                'afternoon_opening' => 'required',
                'night_opening' => 'required',
                'evening_opening' => 'required',
            ], [
                // Custom error messages for each field
                'name.required' => 'The attraction name is required',
                'name.max' => 'The attraction name cannot exceed 255 characters',
                // 'adult_price.required' => 'Adult price is required',
                // 'adult_price.numeric' => 'Adult price must be a number',
                // 'adult_price.min' => 'Adult price cannot be negative',
                // 'child_price.required' => 'Child price is required',
                // 'child_price.numeric' => 'Child price must be a number',
                // 'child_price.min' => 'Child price cannot be negative',
                'description.required' => 'Description is required',
                //'description.max' => 'Description cannot exceed 1000 characters',
                'location.required' => 'Location is required',
                'master_image.required' => 'Master image is required',
                'master_image.image' => 'Master image must be a valid image file',
                'all_images.*.image' => 'All additional images must be valid image files',
                
                'latitude.required' => 'Latitude is required',
                'latitude.numeric' => 'Latitude must be a number',
                //'latitude.min' => 'Latitude cannot be negative',
                'longitude.required' => 'Longitude is required',
                'longitude.numeric' => 'Longitude must be a number',
                //'longitude.min' => 'Longitude cannot be negative',
                'morning_opening.required' => 'Morning opening status is required',
                'afternoon_opening.required' => 'Afternoon opening status is required',
                'night_opening.required' => 'Night opening status is required',
                'evening_opening.required' => 'Evening opening status is required',
            ]);
        // } catch (\Illuminate\Validation\ValidationException $e) {
        //     return redirect()
        //         ->back()
        //         ->withErrors($e->errors())
        //         ->withInput();
        // }

        $auth_user = Auth::user();
        $lastAttraction = Attraction::withTrashed()->orderBy('created_at', 'desc')->first();
        $attraction_max_id = $lastAttraction->attraction_id ?? 0;
        $attractionId = CommonHelper::createId($attraction_max_id);
        while (Attraction::where('attraction_id', $attractionId)->exists()) {
            $attractionId = CommonHelper::createId($attractionId);
        }

        $imagePaths = [];
        if ($request->hasFile('all_images')) {
            foreach ($request->file('all_images') as $image) {
                $pathData = CommonHelper::image_path('file_storage', $image);
                if (!empty($pathData['master_value'])) {
                    $imagePaths[] = $pathData['master_value'];
                }
            }
        }
        $imagePathsJson = json_encode($imagePaths);
        $masterImage = '';
        if ($request->hasFile('master_image')) {
            $pathData = CommonHelper::image_path('file_storage', $request->file('master_image'));
            if (!empty($pathData['master_value'])) {
                $masterImage = $pathData['master_value'];
            }
        }

            $auth_user = Auth::user();
            if ($auth_user->role_id == 1 || $auth_user->role_id == 2 || $auth_user->role_id == 23) {
                $dmc_id = $request->dmc;
                $status = 1;
            } elseif ($auth_user->role_id == 11) {
                $dmc_id = $auth_user->userId;
                $status = 1;
            } elseif(auth()->user()->role_id ==35 || auth()->user()->role_id == 130 || auth()->user()->role_id == 132 || auth()->user()->role_id == 133 || auth()->user()->role_id == 135 || auth()->user()->role_id == 136 || auth()->user()->role_id == 137 || auth()->user()->role_id == 138){
                $userdmc = User::where('userId', auth()->user()->created_by)->first();
                $dmc_id = $userdmc->userId;
                $status = 1;
            }
            elseif(auth()->user()->role_id == 74){
                $user_product_head = User::where('userId', auth()->user()->created_by)->first();
                $user_product_head_dmc = User::where('userId', $user_product_head->created_by)->first();
                $dmc_id = $user_product_head_dmc->userId;
                $status = 1;
            }
            elseif(auth()->user()->role_id == 93){
                $user_product_manager = User::where('userId', auth()->user()->created_by)->first();
            }
            //     $user_product_head = User::where('userId', $user_product_manager->created_by)->first();

            //     $user_product_head_dmc = User::where('userId', $user_product_head->created_by)->first();

            //     $dmc_id = $user_product_head_dmc->userId;
            //     $status = 1;
            // }
            // else{
            //     $dmc_id = $request->dmc;
            // }
            // $dmc_id = User::where('role_id', 20)->value('userId') ?? 0;
            // $status = 1;
            // // 🔍 Check for existing hotel at same lat/lng for this DMC
            // $existingAttraction = Attraction::where([
            //     ['latitude', $request->latitude],
            //     ['longitude', $request->longitude],
            //     ['dmc_id', $dmc_id]
            // ])->first();

            // if ($existingAttraction) {
            //     return redirect()->back()
            //         ->withInput()
            //         ->with('error', 'A Attraction already exists at this location for the selected DMC.');
            //}

        //Create a new attraction record
        $attraction = new Attraction();
        $attraction->name = $request->input('name');
        $attraction->adult_price = 0;
        $attraction->child_price = 0;
        $attraction->price_shared = $request->input('price_shared');
        $attraction->price_private = $request->input('price_private');
        $attraction->latitude = $request->input('latitude');
        $attraction->longitude = $request->input('longitude');
        $attraction->location = ucfirst($request->input('location'));
        $attraction->country = $request->input('country');
        $attraction->open_time = json_encode($request->input('open_time'));
        $attraction->close_time = json_encode($request->input('close_time'));
        $attraction->description = $request->input('description');
        $attraction->remarks = $request->input('remarks');
        $attraction->terms_conditions = $request->input('terms_conditions');
        $attraction->attraction_id = $attractionId;
        $attraction->status = 1;
        // $attraction->dmc_id = $dmc_id ?? 0;
        $attraction->is_active = $request->input('attraction_status') == 1 ? 1 : 0;
        $attraction->additional_image = $imagePathsJson;
        $attraction->master_image = $masterImage;
        $attraction->created_by = $auth_user->userId;
        $attraction->night_opening = $request->night_opening;
        $attraction->afternoon_opening = $request->afternoon_opening;
        $attraction->morning_opening = $request->morning_opening;
        $attraction->evening_opening = $request->evening_opening;
        $attraction->senior_min_age = $request->senior_min_age;
        $attraction->child_max_age = $request->child_end_age;
        $attraction->senior_adult_price = 0;
        $attraction->save();

        // if (in_array($auth_user->role_id, [11, 4, 3, 35, 74, 93])) {
        //     return view('attractions.thankyou');
        // }
        return redirect()->route('tickets.add_ticket', Crypt::encrypt($attractionId))->with('success', 'Attraction added successfully!');
    }

    /*
    * Show the form fors editing the specified role.
    * Date 17-01-2025
    */
    public function edit($id)
    {
        if (!hasPermission('edit attraction')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $id = Crypt::decrypt($id);
        $country = Country::where('is_active', 1)->get();
        $attraction = Attraction::where('attraction_id',$id)->first();
        $city = City::where('country', $attraction->country)->get();

        $authuser = auth()->user();
        if($authuser->role_id == 4){
            $dmcs = User::where('role_id', 11)->where('country', $authuser->country)->get();
        }elseif($authuser->role_id == 3){
            $dmcs = User::where('role_id', 11)->where('country', $authuser->country)->get();
        }else{
            $dmcs = User::where('role_id', 11)->get();
        }

        return view('attractions.edit-attraction', compact('attraction','country','city','dmcs'));
    }
    /*
    * Update the specified role.
    * Date 17-01-2025
    */
    public function update(Request $request, $id)
    {
        $attraction = Attraction::where('attraction_id',$id)->first();

        // dd($request->all());
        $request->validate([
            'name' => 'required|string|max:255',
            // 'adult_price' => 'required|numeric|min:0',
            // 'child_price' => 'required|numeric|min:0',
            'description' => 'required|string',
            'terms_conditions' => 'required|string',
            'city' => 'required|string|max:255',
            'all_images.*' => 'nullable|file|image|mimes:jpeg,png,jpg,gif',
            'latitude' => 'required|numeric|min:0',
            'longitude' => 'required|numeric|min:0',
            'morning_opening' => 'required',
            'afternoon_opening' => 'required',
            'night_opening' => 'required',
            'evening_opening' => 'required',
        ]);

        $allImages = $request->all_images;
        $existingImages = $request->input('existing_images', []);
        
        // Get current additional images and find removed ones
        $currentImages = $attraction->additional_image ? json_decode($attraction->additional_image, true) : [];
        if(is_array($currentImages) && is_array($existingImages)) {
            $removedImages = array_diff($currentImages, $existingImages);
            // Delete removed images from Azure
            foreach($removedImages as $removedImage) {
                CommonHelper::deleteAzureImage($removedImage);
            }
        }
        
        $imagePaths = []; 
        if ($request->hasFile('all_images')) {    
            foreach ($request->file('all_images') as $image) {
                $pathData = CommonHelper::image_path('file_storage', $image);
                if (!empty($pathData['master_value'])) {
                    $imagePaths[] = $pathData['master_value']; 
                }
            }
        }
        $img_path = array_merge($existingImages, $imagePaths);
        // Process master image
        $master_image = $attraction->master_image ?? '';
        if ($request->hasFile('master_image')) {
            // Delete old master image from Azure before uploading new one
            if ($attraction->master_image) {
                CommonHelper::deleteAzureImage($attraction->master_image);
            }
            
            $masterImagePath = CommonHelper::image_path('file_storage', $request->file('master_image'));
            if (!empty($masterImagePath['master_value'])) {
                $master_image = $masterImagePath['master_value'];
            }
        }

        $attraction->name = $request->input('name');
        $attraction->adult_price = 0;
        $attraction->child_price = 0;
        $attraction->price_shared = 0;
        $attraction->price_private = 0;
        $attraction->latitude = $request->input('latitude');
        $attraction->longitude = $request->input('longitude');
        $attraction->open_time = json_encode($request->input('open_time'));
        $attraction->close_time = json_encode($request->input('close_time'));
        $attraction->description = $request->input('description');
        $attraction->remarks = $request->input('remarks');
        $attraction->terms_conditions = $request->input('terms_conditions');
        $attraction->location = $request->input('city');
        $attraction->is_active = $request->input('attraction_status') == 1 ? 1 : 0;
        $attraction->additional_image = json_encode($img_path);
        $attraction->master_image = $master_image;
        $attraction->night_opening = $request->night_opening;
        $attraction->afternoon_opening = $request->afternoon_opening;
        $attraction->morning_opening = $request->morning_opening;
        $attraction->evening_opening = $request->evening_opening;
        $attraction->senior_min_age = $request->senior_min_age;
        $attraction->child_max_age = $request->child_end_age;
        $attraction->senior_adult_price = 0;

        $attraction->save();

        return redirect()->route('attraction.index')->with('success', 'Attraction details updated successfully.');
    }

    /*
    * Soft Delete attraction.
    * Date 17-01-2025
    */
    public function destroy($id)
    {
        if (!hasPermission('delete attraction')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $id = Crypt::decrypt($id);
        // Get attraction and delete images from Azure
        $attraction = Attraction::where('attraction_id', $id)->first();
        if($attraction) {
            // Delete master image
            if($attraction->master_image) {
                CommonHelper::deleteAzureImage($attraction->master_image);
            }
            
            // Delete additional images
            if($attraction->additional_image) {
                $images = json_decode($attraction->additional_image, true);
                if(is_array($images)) {
                    foreach($images as $image) {
                        CommonHelper::deleteAzureImage($image);
                    }
                }
            }
        }
        
        Attraction::where('attraction_id', $id)->delete();
        return redirect()->route('attraction.index')
        ->with('success', 'Attraction deleted successfully');
    }

    /*
    * Calendar attraction.
    * Date 10-02-2025
    */
    public function attractionCalendar($attraction_id)
    {
        $attraction = Attraction::where('attraction_id', $attraction_id)->first();
        $close_days = $attraction->close_days;
        $close_dates = $attraction->close_dates;
        return view('attractions.calendar', compact('attraction_id', 'attraction', 'close_days', 'close_dates'));
    }

    public function attractionCloseDate(Request $request){
        $stringDates = $request->attraction_holiday_dates;
        $datesArray = array_map('trim', explode(',', $stringDates));
        $datesJson = json_encode($datesArray, JSON_PRETTY_PRINT);
        $attraction = Attraction::where('attraction_id', $request->attraction_id)->first();
        $attraction->close_days = $request->attraction_closed_days;
        $attraction->close_dates = $request->attraction_holiday_dates;
        $attraction->save();
        return redirect()->back()
        ->with('success', 'Attraction Calender deleted successfully');
    }

    /**
     * Show DMC Attractions Selection Page
     * For DMC users to select/manage their attractions
     */
    public function dmcAttractionsSelection(Request $request)
    {
        // Check if user is DMC (role_id = 11)
        $user = auth()->user();
        $allowedRoles = [11, 35,74, 93, 130, 132, 133, 135, 136, 137, 138];
        if (!in_array($user->role_id, $allowedRoles)) {
            abort(403, 'You do not have permission to access this page.');
        }

        if($user->role_id == 11){
            $dmc_id = $user->userId;
        }else if($user->role_id == 35 || in_array($user->role_id, [130, 132, 133, 135, 136, 137, 138])){
            $dmc_id = $user->created_by;
        }else if($user->role_id == 74){
            $user_product_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $user_product_head->created_by;
        }else if($user->role_id == 93){
            $user_product_manager = User::where('userId', $user->created_by)->first();
            $user_product_head = User::where('userId', $user_product_manager->created_by)->first();
            $dmc_id = $user_product_head->created_by;
        }
        else{
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }

        // Get all available attractions
        $allAttractions = Attraction::where('status', 1)
                                   ->orderBy('created_at', 'desc')
                                   ->get();
        
        // Filter attractions that are selected by the current DMC
        $selectedAttractions = $allAttractions->filter(function($attraction) use ($dmc_id) {
            return $attraction->hasSelectedByDmc($dmc_id);
        });
        
        // Get attractions that are not selected by the current DMC
        $availableAttractions = $allAttractions->filter(function($attraction) use ($dmc_id) {
            return !$attraction->hasSelectedByDmc($dmc_id);
        });

        return view('services.attractions', compact('availableAttractions', 'selectedAttractions'));
    }

    /**
     * Update DMC Attractions Selection
     * Handle checkbox updates for attraction selection
     */
    public function updateDmcAttractions(Request $request)
    {
        $user = auth()->user();
        $allowedRoles = [11, 35,74, 93, 130, 132, 133, 135, 136, 137, 138];
        if (!in_array($user->role_id, $allowedRoles)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        if($user->role_id == 11){
            $dmc_id = $user->userId;
        }else if($user->role_id == 35 || in_array($user->role_id, [130, 132, 133, 135, 136, 137, 138])){
            $dmc_id = $user->created_by;
        }else if($user->role_id == 74){
            $user_product_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $user_product_head->created_by;
        }else if($user->role_id == 93){
            $user_product_manager = User::where('userId', $user->created_by)->first();
            $user_product_head = User::where('userId', $user_product_manager->created_by)->first();
            $dmc_id = $user_product_head->created_by;
        }
        else{
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }

        $selectedAttractions = $request->input('selected_attractions', []);
        
        // Remove DMC ID from all attractions first
        Attraction::whereJsonContains('dmc_id', $dmc_id)->get()->each(function($attraction) use ($dmc_id) {
            $attraction->removeDmcId($dmc_id);
        });
        
        // Add DMC ID to selected attractions
        if (!empty($selectedAttractions)) {
            Attraction::whereIn('attraction_id', $selectedAttractions)->get()->each(function($attraction) use ($dmc_id) {
                $attraction->addDmcId($dmc_id);
            });
        }

        return redirect()->back()->with('success', 'Attraction selection updated successfully!');
    }

    /**
     * Select Individual Attraction for DMC
     * Handle individual attraction selection with AJAX
     */
    public function selectAttraction(Request $request)
    {
        try {
            $attractionId = Crypt::decrypt($request->input('attraction_id'));
            $user = Auth::user();

        $allowedRoles = [11, 35,74, 93, 130, 132, 133, 135, 136, 137, 138];
        if (!in_array($user->role_id, $allowedRoles)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        if($user->role_id == 11){
            $dmc_id = $user->userId;
        }else if($user->role_id == 35 || in_array($user->role_id, [130, 132, 133, 135, 136, 137, 138])){
            $dmc_id = $user->created_by;
        }else if($user->role_id == 74){
            $user_product_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $user_product_head->created_by;
        }else if($user->role_id == 93){
            $user_product_manager = User::where('userId', $user->created_by)->first();
            $user_product_head = User::where('userId', $user_product_manager->created_by)->first();
            $dmc_id = $user_product_head->created_by;
        }
        else{
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }
            
            // Find the attraction
            $attraction = Attraction::where('attraction_id', $attractionId)->first();
            if (!$attraction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attraction not found.'
                ], 404);
            }
            
            // Add the DMC ID to the attraction's dmc_id array
            $attraction->addDmcId($dmc_id);
            
            return response()->json([
                'success' => true,
                'message' => 'Attraction selected successfully!'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Attraction selection error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while selecting the attraction.'
            ], 500);
        }
    }

    /**
     * Remove Individual Attraction from DMC Selection
     * Handle individual attraction removal with AJAX
     */
    public function removeAttraction(Request $request)
    {
        try {
            $attractionId = $request->input('attraction_id');
            $user = Auth::user();

            $allowedRoles = [11, 35,74, 93, 130, 132, 133, 135, 136, 137, 138];
        if (!in_array($user->role_id, $allowedRoles)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        if($user->role_id == 11){
            $dmc_id = $user->userId;
        }else if($user->role_id == 35 || in_array($user->role_id, [130, 132, 133, 135, 136, 137, 138])){
            $dmc_id = $user->created_by;
        }else if($user->role_id == 74){
            $user_product_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $user_product_head->created_by;
        }else if($user->role_id == 93){
            $user_product_manager = User::where('userId', $user->created_by)->first();
            $user_product_head = User::where('userId', $user_product_manager->created_by)->first();
            $dmc_id = $user_product_head->created_by;
        }
        else{
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }
            
            // Find the attraction
            $attraction = Attraction::where('attraction_id', $attractionId)->first();
            if (!$attraction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attraction not found.'
                ], 404);
            }
            
            // Check if this DMC has selected this attraction
            if (!$attraction->hasSelectedByDmc($dmc_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attraction not selected by you.'
                ], 400);
            }
            
            // Remove the DMC ID from the attraction's dmc_id array
            $attraction->removeDmcId($dmc_id);
            
            return response()->json([
                'success' => true,
                'message' => 'Attraction removed successfully!'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Attraction removal error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while removing the attraction.'
            ], 500);
        }
    }
}
