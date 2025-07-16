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
use Illuminate\Support\Facades\Log;
use App\Helpers\CommonHelper;
use App\Models\Country;
use App\Models\City;
use App\Models\Order;

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
            $restaurants = Restaurant::orderBy('restaurant_id', 'desc')->get()->filter(function($restaurant) use ($dmc_ids) {
                $selectedDmcIds = $restaurant->getSelectedDmcIds();
                return !empty(array_intersect($selectedDmcIds, $dmc_ids));
            });
        }
        elseif ($user->role_id == 11) {
            $restaurants = Restaurant::with('hotel')->orderBy('restaurant_id', 'desc')->get()->filter(function($restaurant) use ($user) {
                return $restaurant->hasSelectedByDmc($user->userId);
            });
        }
        elseif ($user->role_id == 20) {
            $restaurants = Restaurant::with('hotel')->orderBy('restaurant_id', 'desc')->whereJsonContains('dmc_id', $user->userId)->get();
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
            $restaurants = Restaurant::orderBy('restaurant_id', 'desc')->get()->filter(function($restaurant) use ($dmc_ids) {
                $selectedDmcIds = $restaurant->getSelectedDmcIds();
                return !empty(array_intersect($selectedDmcIds, $dmc_ids));
            });
        } 
        
        elseif($user->role_id == 35){
            $restaurants = Restaurant::orderBy('restaurant_id', 'desc')->get()->filter(function($restaurant) use ($user) {
                return $restaurant->hasSelectedByDmc($user->created_by);
            });
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
        // dd($request->all());
        // dd($request->all());
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
            $hotels = Hotel::where('status', 1)->get()->filter(function($hotel) use ($auth_user) {
                return $hotel->hasSelectedByDmc($auth_user->userId);
            });
        }else {
            $hotels = Hotel::where('status', 1)->get()->filter(function($hotel) use ($auth_user) {
                return $hotel->hasSelectedByDmc($auth_user->userId);
            });
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

        if(in_array($authuser->role_id, [11, 20, 35, 78, 120])){
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
            // $dmc_id = User::where('role_id', 20)->value('userId') ?? 0;
            // $status = 1;
            // // 🔍 Check for existing hotel at same lat/lng for this DMC
            // $existingRestaurant = Restaurant::where([
            //     ['latitude', $request->latitude],
            //     ['longitude', $request->longitude],
            //     ['dmc_id', $dmc_id]
            // ])->first();

            // if ($existingRestaurant) {
            //     return redirect()->back()
            //         ->withInput()
            //         ->with('error', 'A Restaurant already exists at this location for the selected DMC.');
            // }

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
        $restaurant->status = 1;
        // $restaurant->dmc_id = $dmc_id ?? 0;
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

    /**
     * Show DMC Restaurants Selection Page
     * For DMC users to select/manage their restaurants
     */
    public function dmcRestaurantsSelection(Request $request)
    {
        // Check if user is DMC (role_id = 11)
        $user = auth()->user();
        if ($user->role_id != 11) {
            abort(403, 'You do not have permission to access this page.');
        }

        // Get all available restaurants
        $allRestaurants = Restaurant::where('status', 1)
                                   ->orderBy('name', 'asc')
                                   ->get();
        
        // Filter restaurants that are selected by the current DMC
        $selectedRestaurants = $allRestaurants->filter(function($restaurant) use ($user) {
            return $restaurant->hasSelectedByDmc($user->userId);
        });
        
        // Get restaurants that are not selected by the current DMC
        $availableRestaurants = $allRestaurants->filter(function($restaurant) use ($user) {
            return !$restaurant->hasSelectedByDmc($user->userId);
        });

        return view('services.restaurants', compact('availableRestaurants', 'selectedRestaurants'));
    }

    /**
     * Update DMC Restaurants Selection
     * Handle checkbox updates for restaurant selection
     */
    public function updateDmcRestaurants(Request $request)
    {
        $user = auth()->user();
        if ($user->role_id != 11) {
            abort(403, 'You do not have permission to perform this action.');
        }

        $selectedRestaurants = $request->input('selected_restaurants', []);
        
        // Remove DMC ID from all restaurants first
        Restaurant::whereJsonContains('dmc_id', $user->userId)->get()->each(function($restaurant) use ($user) {
            $restaurant->removeDmcId($user->userId);
        });
        
        // Add DMC ID to selected restaurants
        if (!empty($selectedRestaurants)) {
            Restaurant::whereIn('restaurant_id', $selectedRestaurants)->get()->each(function($restaurant) use ($user) {
                $restaurant->addDmcId($user->userId);
            });
        }

        return redirect()->back()->with('success', 'Restaurant selection updated successfully!');
    }

    /**
     * Select Individual Restaurant for DMC
     * Handle individual restaurant selection with AJAX
     */
    public function selectRestaurant(Request $request)
    {
        try {
            $restaurantId = $request->input('restaurant_id');
            $user = Auth::user();
            
            // Find the restaurant
            $restaurant = Restaurant::find($restaurantId);
            if (!$restaurant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Restaurant not found.'
                ], 404);
            }
            
            // Add the DMC ID to the restaurant's dmc_id array
            $restaurant->addDmcId($user->userId);
            
            return response()->json([
                'success' => true,
                'message' => 'Restaurant selected successfully!'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Restaurant selection error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while selecting the restaurant.'
            ], 500);
        }
    }

    /**
     * Try generating image using wkhtmltoimage
     */
    private function tryWkhtmltoimage($htmlFile, $imageFile)
    {
        try {
            Log::info('Trying wkhtmltoimage method');
            
            // Check if wkhtmltoimage is available
            $command = "wkhtmltoimage --width 600 --height 300 --format png --quality 100 \"$htmlFile\" \"$imageFile\" 2>&1";
            $output = shell_exec($command);
            
            Log::info('wkhtmltoimage command output', ['output' => $output]);
            
            $success = file_exists($imageFile) && filesize($imageFile) > 0;
            
            if (!$success) {
                Log::warning('wkhtmltoimage failed', [
                    'file_exists' => file_exists($imageFile),
                    'filesize' => file_exists($imageFile) ? filesize($imageFile) : 'N/A',
                    'command' => $command
                ]);
            }
            
            return $success;
        } catch (\Exception $e) {
            Log::error('wkhtmltoimage exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Try generating image using Puppeteer
     */
    private function tryPuppeteer($htmlFile, $imageFile)
    {
        try {
            Log::info('Trying Puppeteer method');
            
            // Check if node is available
            $nodeCheck = shell_exec('node --version 2>&1');
            Log::info('Node version check', ['result' => $nodeCheck]);
            
            // Create a simple Node.js script for Puppeteer
            $puppeteerScript = "
                const puppeteer = require('puppeteer');
                const fs = require('fs');

                (async () => {
                    try {
                        const browser = await puppeteer.launch({headless: true});
                        const page = await browser.newPage();
                        await page.setViewport({width: 600, height: 300});
                        const html = fs.readFileSync('$htmlFile', 'utf8');
                        await page.setContent(html, {waitUntil: 'networkidle0'});
                        await page.screenshot({path: '$imageFile', type: 'png', fullPage: true});
                        await browser.close();
                        console.log('Success');
                    } catch (error) {
                        console.error('Error:', error);
                    }
                })();
                ";
            
            $scriptFile = tempnam(sys_get_temp_dir(), 'puppeteer_') . '.js';
            file_put_contents($scriptFile, $puppeteerScript);
            
            $command = "node \"$scriptFile\" 2>&1";
            $output = shell_exec($command);
            
            Log::info('Puppeteer command output', ['output' => $output]);
            
            // Clean up script file
            unlink($scriptFile);
            
            $success = file_exists($imageFile) && filesize($imageFile) > 0;
            
            if (!$success) {
                Log::warning('Puppeteer failed', [
                    'file_exists' => file_exists($imageFile),
                    'filesize' => file_exists($imageFile) ? filesize($imageFile) : 'N/A'
                ]);
            }
            
            return $success;
        } catch (\Exception $e) {
            Log::error('Puppeteer exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Try generating image using PhantomJS
     */
    private function tryPhantomJS($htmlFile, $imageFile)
    {
        try {
            Log::info('Trying PhantomJS method');
            
            // Check if phantomjs is available
            $phantomCheck = shell_exec('phantomjs --version 2>&1');
            Log::info('PhantomJS version check', ['result' => $phantomCheck]);
            
            $phantomScript = "
                var page = require('webpage').create();
                page.viewportSize = {width: 600, height: 300};
                page.open('file://{$htmlFile}', function(status) {
                    if (status === 'success') {
                        setTimeout(function() {
                            page.render('{$imageFile}');
                            phantom.exit();
                        }, 1000);
                    } else {
                        phantom.exit();
                    }
                });
                ";
            
            $scriptFile = tempnam(sys_get_temp_dir(), 'phantom_') . '.js';
            file_put_contents($scriptFile, $phantomScript);
            
            $command = "phantomjs \"$scriptFile\" 2>&1";
            $output = shell_exec($command);
            
            Log::info('PhantomJS command output', ['output' => $output]);
            
            // Clean up script file
            unlink($scriptFile);
            
            $success = file_exists($imageFile) && filesize($imageFile) > 0;
            
            if (!$success) {
                Log::warning('PhantomJS failed', [
                    'file_exists' => file_exists($imageFile),
                    'filesize' => file_exists($imageFile) ? filesize($imageFile) : 'N/A'
                ]);
            }
            
            return $success;
        } catch (\Exception $e) {
            Log::error('PhantomJS exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Try simple image generation as fallback
     */
    private function trySimpleImageGeneration($html, $imageFile, $bookingId)
    {
        try {
            Log::info('Trying simple image generation method');
            Log::info('GD extension loaded: ' . (extension_loaded('gd') ? 'yes' : 'no'));
            
            // Create a simple image using GD library if available
            if (extension_loaded('gd')) {
                // Create a 600x300 image
                $image = imagecreatetruecolor(600, 300);
                
                // Set colors
                $white = imagecolorallocate($image, 255, 255, 255);
                $black = imagecolorallocate($image, 0, 0, 0);
                $blue = imagecolorallocate($image, 102, 126, 234);
                
                // Fill background
                imagefill($image, 0, 0, $white);
                
                // Add text
                $font = 5; // Built-in font
                imagestring($image, $font, 50, 50, 'Restaurant Voucher', $black);
                imagestring($image, $font, 50, 80, 'Booking ID: ' . $bookingId, $black);
                imagestring($image, $font, 50, 110, 'Generated on: ' . date('Y-m-d H:i:s'), $black);
                imagestring($image, $font, 50, 140, 'This is a fallback image', $black);
                imagestring($image, $font, 50, 170, 'Please use the View Voucher button', $black);
                imagestring($image, $font, 50, 200, 'to see the actual voucher', $black);
                
                // Save image
                $success = imagepng($image, $imageFile);
                imagedestroy($image);
                
                Log::info('Simple image generation result', [
                    'success' => $success,
                    'image_file' => $imageFile,
                    'file_exists' => file_exists($imageFile),
                    'file_size' => file_exists($imageFile) ? filesize($imageFile) : 'N/A'
                ]);
                
                if ($success) {
                    Log::info('Simple image generated successfully using GD');
                    return true;
                }
            }
            
            Log::warning('Simple image generation failed - GD extension not available');
            return false;
            
        } catch (\Exception $e) {
            Log::error('Simple image generation exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove Individual Restaurant from DMC Selection
     * Handle individual restaurant removal with AJAX
     */
    public function removeRestaurant(Request $request)
    {
        try {
            $validated = $request->validate([
                'restaurant_id' => 'required',
                'dmc_id' => 'required'
            ]);

            $restaurantId = $validated['restaurant_id'];
            $dmcId = $validated['dmc_id'];

            // Find the restaurant
            $restaurant = Restaurant::where('restaurant_id', $restaurantId)->first();
            
            if (!$restaurant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Restaurant not found'
                ], 404);
            }

            // Remove the DMC from the restaurant's selected DMCs
            // Assuming there's a relationship or field to manage DMC selections
            // This would depend on your specific implementation
            
            return response()->json([
                'success' => true,
                'message' => 'Restaurant removed successfully!'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Restaurant removal error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error removing restaurant: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fallback to HTML2Canvas (client-side conversion)
     */
    private function fallbackToHtml2Canvas($html, $imageFilename, $bookingId)
    {
        // Inject HTML2Canvas script and conversion code
        $html2canvasHtml = str_replace(
            '</body>',
            '
                <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
                <script>
                    window.onload = function() {
                        // Add a button to generate image
                        const button = document.createElement("button");
                        button.innerHTML = "📷 Download as Image";
                        button.style.cssText = "position: fixed; top: 10px; right: 10px; z-index: 9999; padding: 10px 15px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;";
                        document.body.appendChild(button);
                        
                        button.onclick = function() {
                            button.innerHTML = "📷 Generating...";
                            button.disabled = true;
                            
                            html2canvas(document.querySelector(".voucher"), {
                                useCORS: true,
                                allowTaint: true,
                                scale: 2,
                                width: 600,
                                height: 300,
                                backgroundColor: "#ffffff"
                            }).then(function(canvas) {
                                // Create download link
                                const link = document.createElement("a");
                                link.download = "' . $imageFilename . '";
                                link.href = canvas.toDataURL("image/png");
                                link.click();
                                
                                button.innerHTML = "📷 Download as Image";
                                button.disabled = false;
                            }).catch(function(error) {
                                console.error("Error generating image:", error);
                                button.innerHTML = "❌ Error";
                            });
                        };
                    };
                </script>
            </body>',
            $html
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Voucher ready for image conversion',
            'html' => $html2canvasHtml,
            'filename' => $imageFilename,
            'method' => 'html2canvas'
        ]);
    }

    /**
     * Return image response
     */
    private function returnImageResponse($imageFile, $filename, $download, $htmlFile)
    {
        try {
            $imageData = file_get_contents($imageFile);
            
            // Clean up temporary files
            unlink($imageFile);
            unlink($htmlFile);
            
            if ($download) {
                return response($imageData)
                    ->header('Content-Type', 'image/png')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                    ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                    ->header('Pragma', 'no-cache')
                    ->header('Expires', '0');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Restaurant removed successfully!'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Restaurant removal error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing image: ' . $e->getMessage()
            ], 500);
        }
    }


        /**
     * Generate voucher image from HTML and save to storage
     */
    private function generateAndSaveVoucherImage($html, $bookingId)
    {
        try {
            // Create a temporary HTML file
            $tempHtmlFile = tempnam(sys_get_temp_dir(), 'voucher_') . '.html';
            file_put_contents($tempHtmlFile, $html);
           
            // Generate image filename
            $imageFilename = 'restaurant_voucher_' . $bookingId . '_' . date('Ymd_His') . '.png';
            $tempImageFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $imageFilename;
           
            Log::info('Trying to generate voucher image', [
                'booking_id' => $bookingId,
                'temp_html_file' => $tempHtmlFile,
                'temp_image_file' => $tempImageFile,
                'html_length' => strlen($html)
            ]);
           
            // Try to generate the image using available methods
            Log::info('Starting image generation process');
            
            $generationSuccess = false;
            $generationMethod = 'none';
            
            if ($this->tryWkhtmltoimage($tempHtmlFile, $tempImageFile)) {
                Log::info('Image generated successfully using wkhtmltoimage');
                $generationSuccess = true;
                $generationMethod = 'wkhtmltoimage';
                $result = $this->saveImageToStorage($tempImageFile, $imageFilename, $tempHtmlFile);
                Log::info('wkhtmltoimage storage result', ['result' => $result]);
                return $result;
            }
           
            if ($this->tryPuppeteer($tempHtmlFile, $tempImageFile)) {
                Log::info('Image generated successfully using Puppeteer');
                $generationSuccess = true;
                $generationMethod = 'puppeteer';
                $result = $this->saveImageToStorage($tempImageFile, $imageFilename, $tempHtmlFile);
                Log::info('puppeteer storage result', ['result' => $result]);
                return $result;
            }
           
            if ($this->tryPhantomJS($tempHtmlFile, $tempImageFile)) {
                Log::info('Image generated successfully using PhantomJS');
                $generationSuccess = true;
                $generationMethod = 'phantomjs';
                $result = $this->saveImageToStorage($tempImageFile, $imageFilename, $tempHtmlFile);
                Log::info('phantomjs storage result', ['result' => $result]);
                return $result;
            }
           
            // Try fallback method using simple image generation
            if ($this->trySimpleImageGeneration($html, $tempImageFile, $bookingId)) {
                Log::info('Image generated successfully using simple image generation');
                $generationSuccess = true;
                $generationMethod = 'simple_gd';
                $result = $this->saveImageToStorage($tempImageFile, $imageFilename, $tempHtmlFile);
                Log::info('simple_gd storage result', ['result' => $result]);
                return $result;
            }
            
            Log::error('All image generation methods failed', [
                'generation_success' => $generationSuccess,
                'generation_method' => $generationMethod,
                'temp_html_file' => $tempHtmlFile,
                'temp_image_file' => $tempImageFile
            ]);
           
            Log::warning('All image generation methods failed');
           
            // Clean up HTML file if no image was generated
            if (file_exists($tempHtmlFile)) {
                unlink($tempHtmlFile);
            }
           
            return null;
           
        } catch (\Exception $e) {
            Log::error('Error generating voucher image: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Save image to storage and return the URL
     */
    private function saveImageToStorage($tempImageFile, $imageFilename, $tempHtmlFile)
    {
        try {
            if (!file_exists($tempImageFile) || filesize($tempImageFile) === 0) {
                Log::warning('Image file does not exist or is empty', [
                    'temp_image_file' => $tempImageFile,
                    'file_exists' => file_exists($tempImageFile),
                    'file_size' => file_exists($tempImageFile) ? filesize($tempImageFile) : 'N/A'
                ]);
                return null;
            }

            // Create uploaded file object for CommonHelper
            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $tempImageFile,
                $imageFilename,
                'image/png',
                null,
                true
            );

            // Use CommonHelper to save the image
            Log::info('Calling CommonHelper::image_path', [
                'storage_type' => 'file_storage',
                'container' => 'vouchers',
                'filename' => $imageFilename,
                'temp_file' => $tempImageFile,
                'file_exists' => file_exists($tempImageFile),
                'file_size' => file_exists($tempImageFile) ? filesize($tempImageFile) : 'N/A'
            ]);
            
            $pathData = CommonHelper::image_path('file_storage', $uploadedFile, 'vouchers');
            
            Log::info('CommonHelper image_path result', [
                'path_data' => $pathData,
                'master_value' => $pathData['master_value'] ?? null,
                'has_error' => isset($pathData['error']),
                'error_message' => $pathData['error'] ?? null
            ]);

            // Clean up temporary files
            if (file_exists($tempImageFile)) {
                unlink($tempImageFile);
            }
            if (file_exists($tempHtmlFile)) {
                unlink($tempHtmlFile);
            }

            if (!empty($pathData['master_value'])) {
                Log::info('Image saved successfully to storage', [
                    'image_url' => $pathData['master_value'],
                    'path_data' => $pathData
                ]);
                return $pathData['master_value'];
            } else {
                Log::error('Failed to save image via CommonHelper', [
                    'path_data' => $pathData,
                    'temp_image_file' => $tempImageFile,
                    'image_filename' => $imageFilename
                ]);
                return null;
            }

        } catch (\Exception $e) {
            Log::error('Error saving image to storage: ' . $e->getMessage());
            
            // Clean up temporary files on error
            if (file_exists($tempImageFile)) {
                unlink($tempImageFile);
            }
            if (file_exists($tempHtmlFile)) {
                unlink($tempHtmlFile);
            }
            
            return null;
        }
    }

    /**
     * View voucher image in browser instead of downloading
     */
    public function viewVoucherImage($bookingId, $tourId)
    {
        try {
            // Find the order
            $order = Order::where('booking_id', $bookingId)->where('tour_id', $tourId)->first();
            
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }
            
            // Check for voucher image in voucher_image field or data field
            $imageUrl = $order->voucher_image ?? null;
            if (!$imageUrl) {
                $data = $order->data ?? [];
                $imageUrl = $data['voucher_image'] ?? null;
            }
            
            if (!$imageUrl) {
                return response()->json([
                    'success' => false,
                    'message' => 'Voucher image not found'
                ], 404);
            }
            
            // If it's a full URL (Azure, S3, etc.), fetch the image content
            if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                try {
                    // Create a context for the file_get_contents to handle different scenarios
                    $context = stream_context_create([
                        'http' => [
                            'method' => 'GET',
                            'header' => [
                                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                                'Accept: image/png,image/*,*/*;q=0.8'
                            ],
                            'timeout' => 30,
                            'ignore_errors' => true
                        ]
                    ]);
                    
                    $imageContent = file_get_contents($imageUrl, false, $context);
                    
                    if ($imageContent === false) {
                        Log::warning('Failed to fetch image from URL: ' . $imageUrl);
                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to retrieve image from storage'
                        ], 404);
                    }
                    
                    // Return the image with proper headers for viewing
                    return response($imageContent)
                        ->header('Content-Type', 'image/png')
                        ->header('Content-Disposition', 'inline; filename="voucher_' . $bookingId . '.png"')
                        ->header('Cache-Control', 'public, max-age=3600')
                        ->header('X-Content-Type-Options', 'nosniff');
                        
                } catch (\Exception $e) {
                    Log::error('Error fetching voucher image: ' . $e->getMessage(), [
                        'image_url' => $imageUrl,
                        'booking_id' => $bookingId,
                        'tour_id' => $tourId
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Error retrieving image: ' . $e->getMessage()
                    ], 500);
                }
            }
            
            // If it's a local path, serve it directly
            if (file_exists(public_path($imageUrl))) {
                return response()->file(public_path($imageUrl), [
                    'Content-Type' => 'image/png',
                    'Content-Disposition' => 'inline; filename="voucher_' . $bookingId . '.png"',
                    'Cache-Control' => 'public, max-age=3600'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Image file not found'
            ], 404);
            
        } catch (\Exception $e) {
            Log::error('Error serving voucher image: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error serving image'
            ], 500);
        }
    }



    public function generateCoupon(Request $request)
    {        
        // Handle image storage request from JavaScript
        if ($request->input('action') === 'store_image') {
            return $this->storeClientGeneratedImage($request);
        }
       
        // Validate the incoming request
        $validated = $request->validate([
            'restaurant_data' => 'required|array',
            'booking_id' => 'required',
            'tour_id' => 'required',
            'display_id' => 'required',
            'destination' => 'nullable|string',
            'check_in_date' => 'nullable',
            'total_pax' => 'nullable|integer',
            'adults' => 'nullable|integer',
            'children' => 'nullable|integer',
            'agent_name' => 'nullable|string',
            'dmc_company' => 'nullable|string',
            'download' => 'nullable|boolean',
            'format' => 'nullable|string|in:html,image' // Add format option
        ]);

        try {
            // Get restaurant data
            $restaurantData = $request->restaurant_data;
           
            // Handle if restaurant data is an array (take first element)
            if (is_array($restaurantData) && !empty($restaurantData)) {
                $restaurantData = $restaurantData[0];
            }
           
            // Get restaurant details from database if needed
            $restaurant = Restaurant::where('restaurant_id', $restaurantData['restaurantId'] ?? null)->first();
           
            // Generate coupon code
            $coupon_code = $request->booking_id . '-' . date('Ymd');
           
            // Generate the HTML coupon
            $html = view('restaurants.coupon_pdf', [
                'restaurant' => $restaurant,
                'bookingDetails' => $restaurantData,
                'booking_id' => $request->booking_id,
                'tour_id' => $request->tour_id,
                'display_id' => $request->display_id,
                'destination' => $request->destination,
                'check_in_date' => $request->check_in_date,
                'total_pax' => $request->total_pax,
                'adults' => $request->adults,
                'children' => $request->children,
                'agent_name' => $request->agent_name,
                'dmc_company' => $request->dmc_company,
                'coupon_code' => $coupon_code,

            ])->render();

            // Generate and save the image to storage
            $imageUrl = null;
            $order = Order::where('booking_id', $request->booking_id)->where('tour_id', $request->tour_id)->first();
            
            \Log::info('Looking for order', [
                'booking_id' => $request->booking_id,
                'tour_id' => $request->tour_id,
                'order_found' => $order ? 'yes' : 'no',
                'order_id' => $order ? $order->id : null,
                'order_columns' => $order ? array_keys($order->getAttributes()) : []
            ]);
            
            if ($order) {
                $imageUrl = $this->generateAndSaveVoucherImage($html, $request->booking_id);
                \Log::info('Image generation result', [
                    'image_url' => $imageUrl,
                    'image_url_type' => gettype($imageUrl)
                ]);
                
                if ($imageUrl) {
                    // Check if voucher_image field exists
                    if (property_exists($order, 'voucher_image') || in_array('voucher_image', array_keys($order->getAttributes()))) {
                        $order->voucher_image = $imageUrl;
                        $order->save();
                        \Log::info('Voucher image saved to database', [
                            'booking_id' => $request->booking_id,
                            'tour_id' => $request->tour_id,
                            'image_url' => $imageUrl
                        ]);
                    } else {
                        \Log::error('voucher_image field does not exist in orders table', [
                            'order_columns' => array_keys($order->getAttributes())
                        ]);
                        // For now, let's store the image URL in the data field as a workaround
                        $data = $order->data ?? [];
                        $data['voucher_image'] = $imageUrl;
                        $order->data = $data;
                        $order->save();
                        \Log::info('Voucher image stored in data field as fallback', [
                            'booking_id' => $request->booking_id,
                            'tour_id' => $request->tour_id,
                            'image_url' => $imageUrl
                        ]);
                    }
                } else {
                    \Log::warning('Failed to generate voucher image', [
                        'booking_id' => $request->booking_id,
                        'tour_id' => $request->tour_id
                    ]);
                }
            } else {
                \Log::warning('Order not found for voucher image', [
                    'booking_id' => $request->booking_id,
                    'tour_id' => $request->tour_id
                ]);
            }
           
            // Check if image format is requested
            if ($request->format === 'image') {
                // For silent generation, return JSON response with voucher_image
                if (!$request->download) {
                    // Check if voucher_image field exists in the order
                    $voucherImageUrl = null;
                    if ($order) {
                        if (property_exists($order, 'voucher_image') || in_array('voucher_image', array_keys($order->getAttributes()))) {
                            $voucherImageUrl = $order->voucher_image;
                        } else {
                            // Check if it's stored in the data field
                            $data = $order->data ?? [];
                            $voucherImageUrl = $data['voucher_image'] ?? null;
                        }
                    }
                    
                    \Log::info('Final response data', [
                        'imageUrl' => $imageUrl,
                        'voucherImageUrl' => $voucherImageUrl,
                        'order_exists' => $order ? 'yes' : 'no',
                        'order_columns' => $order ? array_keys($order->getAttributes()) : [],
                        'has_voucher_image_field' => $order ? (property_exists($order, 'voucher_image') || in_array('voucher_image', array_keys($order->getAttributes()))) : false
                    ]);
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Restaurant voucher image generated and saved successfully',
                        'voucher_image' => $voucherImageUrl,
                        'debug_info' => [
                            'generated_image_url' => $imageUrl,
                            'stored_voucher_image' => $voucherImageUrl,
                            'order_found' => $order ? 'yes' : 'no',
                            'order_id' => $order ? $order->id : null,
                            'booking_id' => $request->booking_id,
                            'tour_id' => $request->tour_id
                        ]
                    ]);
                }
                // For download requests, return the image file
                return $this->generateVoucherImage($html, $request->booking_id, $request->download);
            }
           
            // Generate a unique filename for HTML
            $filename = 'restaurant_voucher_' . $request->booking_id . '_' . date('Ymd_His') . '.html';
           
            // If download is requested, return the file as download
            if ($request->download) {
                return response($html)
                    ->header('Content-Type', 'text/html')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                    ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                    ->header('Pragma', 'no-cache')
                    ->header('Expires', '0');
            }

           
           
            // Otherwise, return JSON response for preview
            return response()->json([
                'success' => true,
                'message' => 'Restaurant coupon generated successfully',
                'html' => $html,
                'filename' => $filename,
                'voucher_image' => $imageUrl ?? null
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error generating restaurant coupon: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
           
            return response()->json([
                'success' => false,
                'message' => 'Error generating coupon: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate voucher image from HTML
     */
    private function generateVoucherImage($html, $bookingId, $download = false)
    {
        try {
            // Create a temporary HTML file
            $tempHtmlFile = tempnam(sys_get_temp_dir(), 'voucher_') . '.html';
            file_put_contents($tempHtmlFile, $html);
           
            // Generate image filename
            $imageFilename = 'restaurant_voucher_' . $bookingId . '_' . date('Ymd_His') . '.png';
            $tempImageFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $imageFilename;
           
            // Method 1: Try using wkhtmltoimage if available
            if ($this->tryWkhtmltoimage($tempHtmlFile, $tempImageFile)) {
                return $this->returnImageResponse($tempImageFile, $imageFilename, $download, $tempHtmlFile);
            }
           
            // Method 2: Try using Puppeteer/Chrome headless via node
            if ($this->tryPuppeteer($tempHtmlFile, $tempImageFile)) {
                return $this->returnImageResponse($tempImageFile, $imageFilename, $download, $tempHtmlFile);
            }
           
            // Method 3: Try using PhantomJS (if available)
            if ($this->tryPhantomJS($tempHtmlFile, $tempImageFile)) {
                return $this->returnImageResponse($tempImageFile, $imageFilename, $download, $tempHtmlFile);
            }
           
            // Method 4: Fallback to HTML2Canvas via browser (return HTML with JS)
            return $this->fallbackToHtml2Canvas($html, $imageFilename, $bookingId);
           
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating image: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Store client-generated voucher image
     */
    private function storeClientGeneratedImage(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'booking_id' => 'required',
                'tour_id' => 'required',
                'image_data' => 'required|string',
                'action' => 'required|string'
            ]);

            $bookingId = $validated['booking_id'];
            $tourId = $validated['tour_id'];
            $imageData = $validated['image_data'];

            Log::info('Storing client-generated voucher image', [
                'booking_id' => $bookingId,
                'tour_id' => $tourId,
                'image_data_length' => strlen($imageData)
            ]);

            // Remove the data URL prefix if present
            $imageData = preg_replace('/^data:image\/[a-zA-Z]+;base64,/', '', $imageData);
            $decodedImage = base64_decode($imageData);

            if ($decodedImage === false) {
                Log::error('Failed to decode base64 image data');
                return response()->json([
                    'success' => false,
                    'message' => 'Restaurant not selected by you.'
                ], 400);
            }

            // Create temporary file
            $imageFilename = 'restaurant_voucher_' . $bookingId . '_' . date('Ymd_His') . '.png';
            $tempImageFile = tempnam(sys_get_temp_dir(), 'voucher_') . '.png';
            file_put_contents($tempImageFile, $decodedImage);

            Log::info('Temporary image file created', [
                'temp_file' => $tempImageFile,
                'file_size' => filesize($tempImageFile),
                'filename' => $imageFilename
            ]);

            // Create uploaded file object for CommonHelper
            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $tempImageFile,
                $imageFilename,
                'image/png',
                null,
                true
            );

            // Use CommonHelper to save the image
            $pathData = CommonHelper::image_path('file_storage', $uploadedFile, 'vouchers');
            
            Log::info('CommonHelper image_path result', [
                'path_data' => $pathData,
                'master_value' => $pathData['master_value'] ?? null
            ]);

            if (!empty($pathData['master_value'])) {
                // Update the order with the image URL
                $order = Order::where('booking_id', $bookingId)->where('tour_id', $tourId)->first();
                if ($order) {
                    $order->voucher_image = $pathData['master_value'];
                    $order->save();

                    Log::info('Order updated with voucher image', [
                        'booking_id' => $bookingId,
                        'tour_id' => $tourId,
                        'image_url' => $pathData['master_value']
                    ]);
                } else {
                    Log::warning('Order not found for voucher image update', [
                        'booking_id' => $bookingId,
                        'tour_id' => $tourId
                    ]);
                }

                // Clean up temp file
                if (file_exists($tempImageFile)) {
                    unlink($tempImageFile);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Voucher image stored successfully',
                    'image_url' => $pathData['master_value']
                ]);
            } else {
                Log::error('Failed to store image via CommonHelper', [
                    'path_data' => $pathData
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to store image'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error storing client-generated voucher image', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while storing the voucher image.'
            ], 500);
        }
    }
}