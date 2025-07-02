<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\Facility;
use App\Models\Role;
use App\Models\User;
use App\Models\Hotel;
use App\Models\Meal;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\CommonHelper;
use App\Models\Country;
use App\Models\City;

class RestaurantController extends Controller
{
    /*
    * Display a listing of the Category.
    * Date 06-11-2024
    */
    public function index(Request $request)
    {
        if (!hasPermission('view restaurant')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $user = auth()->user();
        $restaurants = [];
        if ($user->role_id == 4) {
            $dmc_ids = User::with('hotel')->where('assistant_manager_id', $user->userId)->pluck('userId')->toArray();
            $restaurants = Restaurant::whereIn('status', [4, 5, 1])
                // ->whereIn('dmc_id', $dmc_ids)
                ->orderBy('restaurant_id', 'DESC')
                ->get();
        } elseif ($user->role_id == 3) {
            $restaurants = Restaurant::with('hotel')->orderBy('restaurant_id', 'desc')->whereIn('status', [5, 1])->get();
        } elseif (in_array($user->role_id, [1, 2, 23])) {
            $restaurants = Restaurant::with('hotel')->orderBy('restaurant_id', 'desc')->whereIn('status', [1, 3])->get();
        }
        elseif($user->role_id == 10){
            $dmc_ids = User::where('master_dmc_id', $user->userId)->get()->pluck('userId')->toArray();
            $restaurants = Restaurant::orderBy('restaurant_id', 'desc')->whereIn('dmc_id', $dmc_ids)->get();
        }
        elseif ($user->role_id == 11) {
            $restaurants = Restaurant::with('hotel')->orderBy('restaurant_id', 'desc')->where('dmc_id', $user->userId)->get();
        }

        elseif(in_array($user->role_id, [25, 63, 119])){
            if($user->role_id == 25){
                $master_dmc_id = $user->created_by;
            }
            elseif($user->role_id == 63){
                $product_head = User::where('userId', $user->created_by)->first();
                $master_dmc_id = $product_head->created_by;
            }
            elseif($user->role_id == 119){
                $product_manager = User::where('userId', $user->created_by)->first();
                $product_head = User::where('userId', $product_manager->created_by)->first();
                $master_dmc_id = $product_head->created_by;
            }
            
            $dmc_ids = User::where('master_dmc_id', $master_dmc_id)->get()->pluck('userId')->toArray();
            $restaurants = Restaurant::orderBy('restaurant_id', 'desc')->whereIn('dmc_id', $dmc_ids)->get();
        } 
        
        elseif($user->role_id == 35){
            $restaurants = Restaurant::orderBy('restaurant_id', 'desc')->where('dmc_id', $user->created_by)->get();
        }
        elseif($user->role_id == 78){
            $assistant_product_manager_ids = User::where('created_by', $user->userId)->get()->pluck('userId')->toArray();
            if($assistant_product_manager_ids){
                $restaurants = Restaurant::orderBy('restaurant_id', 'desc')->whereIn('created_by', $assistant_product_manager_ids)->orWhere('created_by', $user->userId)->get();
            }else{
                $restaurants = Restaurant::orderBy('restaurant_id', 'desc')->where('created_by', $user->userId)->get();
            }
        }
        elseif($user->role_id == 120){
            $restaurants = Restaurant::orderBy('restaurant_id', 'desc')->where('created_by', $user->userId)->get();
        }
        // $restaurants = Restaurant::with('hotel')->get();
        return view('restaurants.restaurant', compact('restaurants'));
    }

    public function restaurantApproval(Request $request)
    {
        if (!hasPermission('view restaurantapproval')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $auth_user = auth()->user();

        $pendingrestaurants = [];
        
       if($auth_user->role_id == 2 || $auth_user->role_id == 1 || $auth_user->role_id == 23){
        $pendingrestaurants = Restaurant::with('user')
        ->where('status', 5)
        ->get();
        }
        
        return view('restaurants.restaurant-approval',compact('pendingrestaurants'));
    }

    public function editRestaurantApproval($id)
    {
        // if (!hasPermission('edit restaurant')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }

        $hotels = Hotel::where('status', 1)->get();
        $mealTypes = Meal::whereIn('type', ['Breakfast', 'Lunch', 'Dinner'])
                        ->get()
                        ->groupBy('type');
        $restaurant = Restaurant::where('restaurant_id',$id)->first();
        $country = Country::where('is_active', 1)->get();
        $city = City::where('country', $restaurant->country)->get();

        $same_restaurant = null;
        if (!$restaurant) {
            // Handle the case where the restaurant is not found
            return redirect()->back()->with('error', 'Restaurant not found.');
        }
        $same_restaurant = Restaurant::where('latitude', $restaurant->latitude)
            ->where('longitude', $restaurant->longitude)
            ->where('restaurant_id', '!=', $id) // Ignore the current restaurant
            ->where('status',1)
            ->first(); // Only check existence instead of fetching the first record

        return view('restaurants.edit-restaurant-approval', compact('restaurant', 'hotels', 'mealTypes', 'same_restaurant', 'city'));
    }

    public function updateRestaurantApproval(Request $request, $id)
    {
        /// dd($request->all());
        // Reset fields for Breakfast if not available
        if ($request->breakfast_available != 1) {
            $request->merge([
                'opening_time_bf' => null,
                'closing_time_bf' => null,
                'breakfast_type' => null,
                'bf_price' => null,
            ]);
        }
        // Reset fields for Lunch if not available
        if ($request->lunch_available != 1) {
            $request->merge([
                'lunch_type' => null,
                'lunch_price' => null,
                'opening_time_lunch' => null,
                'closing_time_lunch' => null,
            ]);
        }

        // Reset fields for Dinner if not available
        if ($request->dinner_available != 1) {
            $request->merge([
                'dinner_type' => null,
                'dinner_price' => null,
                'opening_time_dinner' => null,
                'closing_time_dinner' => null,
            ]);
        }

        $allImages = $request->all_images;
        $existingImages = $request->input('existing_images', []);
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

        $restaurant = Restaurant::where('restaurant_id',$id)->first();
        // Process master image
        $master_image = $restaurant->master_image ?? '';

        // Unlink removed additional images
        $currentImages = $restaurant->images ? json_decode($restaurant->images, true) : [];
        if(is_array($currentImages) && is_array($existingImages)) {
            $removedImages = array_diff($currentImages, $existingImages);
            foreach($removedImages as $removedImage) {
                CommonHelper::deleteAzureImage($removedImage);
            }
        }

        if ($request->hasFile('master_image')) {
            // Delete old master image from Azure before uploading new one
            if ($restaurant->master_image) {
                CommonHelper::deleteAzureImage($restaurant->master_image);
            }
            $masterImagePath = CommonHelper::image_path('file_storage', $request->file('master_image'));
            if (!empty($masterImagePath['master_value'])) {
                $master_image = $masterImagePath['master_value'];
            }
        }

        $auth_user = auth()->user();
        if ($auth_user->role_id == 2 || $auth_user->role_id == 1 || $auth_user->role_id == 23) {
            $status = 1;
        }else{
            $status = 5;
        }

        $status = $request->has('decline_status') ? 3 : $status;

        $restaurant->name = $request->input('name');
        $restaurant->city = $request->input('city');
        $restaurant->country = $request->input('country');
        $restaurant->latitude = $request->input('latitude');
        $restaurant->longitude = $request->input('longitude');
        $restaurant->cuisine = $request->input('cuisine');
        $restaurant->breakfast_available = $request->input('breakfast_available', 0);
        $restaurant->opening_time_bf = $request->input('opening_time_bf');
        $restaurant->closing_time_bf = $request->input('closing_time_bf');
        $restaurant->breakfast_type = $request->input('breakfast_type');
        $restaurant->lunch_available = $request->input('lunch_available', 0);
        $restaurant->opening_time_lunch = $request->input('opening_time_lunch');
        $restaurant->closing_time_lunch = $request->input('closing_time_lunch');
        $restaurant->lunch_type = $request->input('lunch_type');
        $restaurant->dinner_available = $request->input('dinner_available', 0);
        $restaurant->opening_time_dinner = $request->input('opening_time_dinner');
        $restaurant->closing_time_dinner = $request->input('closing_time_dinner');
        $restaurant->dinner_type = $request->input('dinner_type');
        $restaurant->owned_by = $request->input('owned_by');
        $restaurant->bf_price = $request->input('breakfast_price');
        $restaurant->lunch_price = $request->input('lunch_price'); // Fixed typo
        $restaurant->dinner_price = $request->input('dinner_price');
        $restaurant->property = $request->input('property');
        $restaurant->is_active = $request->input('restaurant_status') == 1 ? 1 : 0;
        $restaurant->images = $img_path ?? null;
        $restaurant->master_image = $master_image ?? null;
        $restaurant->description = $request->input('description');
        $restaurant->remarks = $request->input('remarks');
        $restaurant->terms_conditions = $request->input('terms_conditions');
        $restaurant->status = $status; // Fixed status assignment

        $restaurant->save();

        if ($request->has('decline_status')) {
            return redirect()->route('restaurants.approval', ['restaurant' => $restaurant->restaurant_id])
                ->with('error', 'Restaurant Declined successfully');
        }

        return redirect()->route('restaurants.approval', ['restaurant' => $restaurant->restaurant_id])
            ->with('success', 'Restaurant Approved successfully');
    }

    public function restaurant_create($restaurant_id)
    {
        $restaurants = Restaurant::all();
        $current_restaurant = Restaurant::where('restaurant_id', $restaurant_id)->first();
        $meals = Meal::where('restaurant_id', $restaurant_id)->get();
        return view('meals.create-meals', compact('restaurants', 'meals', 'current_restaurant'));
    }

    /*
    * Show the form for creating a new category.
    * Date 06-11-2024
    */
    public function create()
    {
        if (!hasPermission('create restaurant')) {
            abort(403, 'You do not have permission to access this page.');
        }
        // $currentUser = Auth::user();
        $mealTypes = Meal::where('is_active', 1)->get();
        $country = Country::where('is_active', 1)->get();

        $auth_user = Auth::user();
        if($auth_user->user_type == 1){
            $hotels = Hotel::where('status', 1)->get();
        }elseif($auth_user->user_type == 2){
            $hotels = Hotel::where('dmc_id', $auth_user->userId)->where('status', 1)->get();
        }else {
            $hotels = Hotel::where('dmc_id', $auth_user->userId)->where('status', 1)->get();
        }

        $authuser = auth()->user();
        if($authuser->role_id == 4){
            $dmcs = User::where('role_id', 11)->where('country', $authuser->country)->get();
        }elseif($authuser->role_id == 3){
            $dmcs = User::where('role_id', 11)->where('country', $authuser->country)->get();
        }
        elseif(in_array($authuser->role_id, [10,25, 63, 119])){
            if($authuser->role_id == 10){
                $dmc_ids = User::where('master_dmc_id', $authuser->userId)->get()->pluck('userId')->toArray();
                $master_dmc_id = Auth::user()->userId;
            }
            elseif($authuser->role_id == 25){
                $master_dmc_id = $authuser->created_by;
            }
            elseif($authuser->role_id == 63){
                $product_head = User::where('userId', $authuser->created_by)->first();
                $master_dmc_id = $product_head->created_by;
            }
            elseif($authuser->role_id == 119){
                $product_manager = User::where('userId', $authuser->created_by)->first();
                $product_head = User::where('userId', $product_manager->created_by)->first();
                $master_dmc_id = $product_head->created_by;
            }
            
            $dmcs = User::where('master_dmc_id', $master_dmc_id)->get();
        } 
        else{
            $dmcs = User::where('role_id', 11)->get();
        }

        if(in_array($authuser->role_id, [11, 35, 78, 120])){
            $userCountry = User::where('userId', $authuser->userId)->first()->country;
            $cities = City::where('country', $userCountry)->get();
        }
        else{
            $userCountry = '';
            $cities = [];
        }

        return view('restaurants.add-restaurant', compact('hotels', 'mealTypes', 'auth_user', 'dmcs', 'country', 'cities', 'userCountry'));
    }

    /*
    * Store a newly created role.
    * Date 07-10-2024
    */
    public function store(Request $request)
    {
        // dd($request->all());
        // Validate the incoming request data
        $request->validate([
            'name' => 'required|string|max:255',
            'cuisine' => 'required|string|max:255',
            'breakfast_available' => 'nullable|integer|in:0,1',
            'opening_time_bf' => 'nullable|date_format:H:i',
            'closing_time_bf' => 'nullable|date_format:H:i',
            'breakfast_type' => 'nullable|string|max:255',
            'lunch_available' => 'nullable|integer|in:0,1',
            'opening_time_lunch' => 'nullable|date_format:H:i',
            'closing_time_lunch' => 'nullable|date_format:H:i',
            'lunch_type' => 'nullable|string|max:255',
            'dinner_available' => 'nullable|integer|in:0,1',
            'opening_time_dinner' => 'nullable|date_format:H:i',
            'closing_time_dinner' => 'nullable|date_format:H:i',
            'dinner_type' => 'nullable|string|max:255',
            'owned_by' => 'nullable|string',
            'restaurant_status' => 'nullable|integer',
            'latitude' => 'required|numeric|min:0',
            'longitude' => 'required|numeric|min:0',
            'description' => 'required',
            'terms_conditions' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        

        // Reset fields for Breakfast if not available
        if ($request->breakfast_available != 1) {
            $request->merge([
                'opening_time_bf' => null,
                'closing_time_bf' => null,
                'breakfast_type' => null,
                'bf_price' => null,
            ]);
        }
        
        // Reset fields for Lunch if not available
        if ($request->lunch_available != 1) {
            $request->merge([
                'lunch_type' => null,
                'lunch_price' => null,
                'opening_time_lunch' => null,
                'closing_time_lunch' => null,
            ]);
        }

        // Reset fields for Dinner if not available
        if ($request->dinner_available != 1) {
            $request->merge([
                'dinner_type' => null,
                'dinner_price' => null,
                'opening_time_dinner' => null,
                'closing_time_dinner' => null,
            ]);
        }
        $lastRestaurant = Restaurant::withTrashed()->orderBy('created_at', 'desc')->first();
        $restaurant_max_id = $lastRestaurant->restaurant_id ?? 0;
        $restaurantId = CommonHelper::createId($restaurant_max_id);
        while (Restaurant::where('restaurant_id', $restaurantId)->exists()) {
            $restaurantId = CommonHelper::createId($restaurantId);
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
            // if ($auth_user->role_id == 1 || $auth_user->role_id == 2 || $auth_user->role_id == 23) {
            //     $dmc_id = $request->dmc;
            //     $status = 1;
            // } elseif ($auth_user->role_id == 11) {
            //     $dmc_id = $auth_user->userId;
            //     $status = 1;
            // } elseif(auth()->user()->role_id ==35){
            //     $userdmc = User::where('userId', auth()->user()->created_by)->first();
            //     $dmc_id = $userdmc->userId;
            //     $status = 1;
            // }
            // elseif(auth()->user()->role_id == 78){
            //     $user_product_head = User::where('userId', auth()->user()->created_by)->first();
            //     $user_product_head_dmc = User::where('userId', $user_product_head->created_by)->first();
            //     $dmc_id = $user_product_head_dmc->userId;
            //     $status = 1;
            // }
            // elseif(auth()->user()->role_id == 120){
            //     $user_product_manager = User::where('userId', auth()->user()->created_by)->first();
            //     $user_product_head = User::where('userId', $user_product_manager->created_by)->first();
            //     $user_product_head_dmc = User::where('userId', $user_product_head->created_by)->first();
            //     $dmc_id = $user_product_head_dmc->userId;
            //     $status = 1;
            // }
            // else{
            //     $dmc_id = $request->dmc;
            // }
            $dmc_id = 1;
            $status = 1;
            // 🔍 Check for existing hotel at same lat/lng for this DMC
            $existingRestaurant = Restaurant::where([
                ['latitude', $request->latitude],
                ['longitude', $request->longitude],
                ['dmc_id', $dmc_id]
            ])->first();

            if ($existingRestaurant) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'A Restaurant already exists at this location for the selected DMC.');
            }

        //Create a new restaurant record
        $restaurant = new Restaurant();
        $restaurant->name = $request->input('name');
        $restaurant->city = ucfirst($request->input('city'));
        $restaurant->country = ucfirst($request->input('country'));
        $restaurant->latitude = $request->input('latitude');
        $restaurant->longitude = $request->input('longitude');
        $restaurant->cuisine = $request->input('cuisine');
        // $restaurant->hotel_id = $request->input('hotel_id');
        $restaurant->breakfast_available = $request->input('breakfast_available', 0);
        $restaurant->opening_time_bf = $request->input('opening_time_bf');
        $restaurant->closing_time_bf = $request->input('closing_time_bf');
        $restaurant->bf_price = $request->input('breakfast_price');
        $restaurant->lunch_available = $request->input('lunch_available', 0);
        $restaurant->opening_time_lunch = $request->input('opening_time_lunch');
        $restaurant->closing_time_lunch = $request->input('closing_time_lunch');
        $restaurant->lunch_price = $request->input('lunch_price');

        $restaurant->dinner_available = $request->input('dinner_available', 0);
        $restaurant->opening_time_dinner = $request->input('opening_time_dinner');
        $restaurant->closing_time_dinner = $request->input('closing_time_dinner');
        $restaurant->dinner_price = $request->input('dinner_price');

        $restaurant->owned_by = $request->input('owned_by');
        $restaurant->restaurant_id = $restaurantId;

        $restaurant->property = $request->input('property');
        //$restaurant->is_active = $restaurant->restaurant_status;
        $restaurant->images = $imagePathsJson;
        $restaurant->master_image = $masterImage;
        $restaurant->is_active = $request->input('restaurant_status');
        $restaurant->status = $status;
        $restaurant->dmc_id = $dmc_id ?? 0;
        $restaurant->description = $request->input('description');
        $restaurant->remarks = $request->input('remarks');
        $restaurant->terms_conditions = $request->input('terms_conditions');
        $restaurant->created_by = $auth_user->userId;
        $restaurant->save();

        // if (in_array($auth_user->role_id, [11, 4, 3, 35, 78, 120])) {
        //     return view('restaurants.thankyou');
        // }
        $restaurant_id = $restaurant->restaurant_id;
        return redirect()->route('meals.restaurant_create', $restaurant_id)->with('success', 'Restaurant added successfully!');
    }

    /*
    * Show the form fors editing the specified role.
    * Date 07-10-2024
    */
    public function edit($id)
    {
        if (!hasPermission('edit restaurant')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $hotels = Hotel::where('status', 1)->get();
        $mealTypes = Meal::whereIn('type', ['Breakfast', 'Lunch', 'Dinner'])
                        ->get()
                        ->groupBy('type');
        $country = Country::where('is_active', 1)->get();
        $restaurant = Restaurant::where('restaurant_id',$id)->first();
        $city = City::where('country', $restaurant->country)->get();

        $authuser = auth()->user();
        if($authuser->role_id == 4){
            $dmcs = User::where('role_id', 11)->where('country', $authuser->country)->get();
        }elseif($authuser->role_id == 3){
            $dmcs = User::where('role_id', 11)->where('country', $authuser->country)->get();
        }else{
            $dmcs = User::where('role_id', 11)->get();
        }

        return view('restaurants.edit-restaurant', compact('restaurant', 'hotels', 'mealTypes', 'country', 'city','dmcs'));
    }
    /*
    * Update the specified role.
    * Date 07-10-2024
    */
    public function update(Request $request, $id)
    {
        // Reset fields for Breakfast if not available
        if ($request->breakfast_available != 1) {
            $request->merge([
                'opening_time_bf' => null,
                'closing_time_bf' => null,
                'breakfast_type' => null,
                'bf_price' => null,
            ]);
        }
        // Reset fields for Lunch if not available
        if ($request->lunch_available != 1) {
            $request->merge([
                'lunch_type' => null,
                'lunch_price' => null,
                'opening_time_lunch' => null,
                'closing_time_lunch' => null,
            ]);
        }

        // Reset fields for Dinner if not available
        if ($request->dinner_available != 1) {
            $request->merge([
                'dinner_type' => null,
                'dinner_price' => null,
                'opening_time_dinner' => null,
                'closing_time_dinner' => null,
            ]);
        }

        $allImages = $request->all_images;
        $existingImages = $request->input('existing_images', []);
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

        $restaurant = Restaurant::where('restaurant_id',$id)->first();
        // Process master image
        $master_image = $restaurant->master_image ?? '';

        // Unlink removed additional images
        $currentImages = $restaurant->images ? json_decode($restaurant->images, true) : [];
        if(is_array($currentImages) && is_array($existingImages)) {
            $removedImages = array_diff($currentImages, $existingImages);
            foreach($removedImages as $removedImage) {
                CommonHelper::deleteAzureImage($removedImage);
            }
        }

        if ($request->hasFile('master_image')) {
            // Delete old master image from Azure before uploading new one
            if ($restaurant->master_image) {
                CommonHelper::deleteAzureImage($restaurant->master_image);
            }
            $masterImagePath = CommonHelper::image_path('file_storage', $request->file('master_image'));
            if (!empty($masterImagePath['master_value'])) {
                $master_image = $masterImagePath['master_value'];
            }
        }

        $restaurant->name = $request->input('name');
        $restaurant->city = $request->input('city');
        $restaurant->latitude = $request->input('latitude');
        $restaurant->longitude = $request->input('longitude');
        $restaurant->cuisine = $request->input('cuisine');
        $restaurant->breakfast_available = $request->input('breakfast_available', 0);
        $restaurant->opening_time_bf = $request->input('opening_time_bf');
        $restaurant->closing_time_bf = $request->input('closing_time_bf');
        $restaurant->breakfast_type = $request->input('breakfast_type');
        $restaurant->lunch_available = $request->input('lunch_available', 0);
        $restaurant->opening_time_lunch = $request->input('opening_time_lunch');
        $restaurant->closing_time_lunch = $request->input('closing_time_lunch');
        $restaurant->lunch_type = $request->input('lunch_type');
        $restaurant->dinner_available = $request->input('dinner_available', 0);
        $restaurant->opening_time_dinner = $request->input('opening_time_dinner');
        $restaurant->closing_time_dinner = $request->input('closing_time_dinner');
        $restaurant->dinner_type = $request->input('dinner_type');
        $restaurant->owned_by = $request->input('owned_by');
        $restaurant->bf_price = $request->input('breakfast_price');
        $restaurant->lunch_price = $request->input('lunch_price');
        $restaurant->dinner_price = $request->input('dinner_price');
        $restaurant->property = $request->input('property');
        $restaurant->is_active = $request->input('restaurant_status') == 1 ? 1 : 0;
        $restaurant->description = $request->input('description');
        $restaurant->remarks = $request->input('remarks');
        $restaurant->terms_conditions = $request->input('terms_conditions');
        $restaurant->images = $img_path;
        $restaurant->master_image = $master_image;
        $restaurant->save();

        return redirect()->route('restaurant.index')->with('success', 'Restaurant details updated successfully.');
    }

    /*
    * Soft Delete Roles.
    * Date 07-10-2024
    */
    public function destroy($id)
    {
        if (!hasPermission('delete restaurant')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $isUsedInRooms = Room::where('breakfast_restaurant', $id)
        ->orWhere('lunch_restaurant', $id)
        ->orWhere('dinner_restaurant', $id)
        ->exists();
        if ($isUsedInRooms) {
        // The restaurant is being used in the rooms table, so do not delete it
        return redirect()->route('restaurant.index')
        ->with('error', 'This Restaurant is in use, cannot be deleted!');
        }

        // Get restaurant and delete images from Azure
        $restaurant = Restaurant::where('restaurant_id', $id)->first();
        if($restaurant) {
            // Delete master image
            if($restaurant->master_image) {
                CommonHelper::deleteAzureImage($restaurant->master_image);
            }
            
            // Delete additional images
            if($restaurant->images) {
                $images = json_decode($restaurant->images, true);
                if(is_array($images)) {
                    foreach($images as $image) {
                        CommonHelper::deleteAzureImage($image);
                    }
                }
            }
        }

        Restaurant::where('restaurant_id', $id)->delete();
        return redirect()->route('restaurant.index')
        ->with('success', 'Restaurant deleted successfully');
    
    }

    public function restaurantCalendar($restaurant_id)
    {
        $restaurant = Restaurant::where('restaurant_id', $restaurant_id)->first();
        $close_days = $restaurant->close_days;
        $close_dates = $restaurant->close_dates;
        return view('restaurants.calendar', compact('restaurant_id', 'restaurant', 'close_days', 'close_dates'));
    }

    public function restaurantCloseDate(Request $request) 
    {
        $stringDates = $request->restaurant_holiday_dates;
        $datesArray = array_map('trim', explode(',', $stringDates));
        $datesJson = json_encode($datesArray, JSON_PRETTY_PRINT);
        $restaurant = Restaurant::where('restaurant_id', $request->restaurant_id)->first();
        $restaurant->close_days = $request->restaurant_closed_days;
        $restaurant->close_dates = $request->restaurant_holiday_dates;
        $restaurant->save();
        return redirect()->back()
        ->with('success', 'Close dates and holidays saved successfully');
    }
}
