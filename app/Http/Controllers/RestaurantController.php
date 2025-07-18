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
        elseif($user->role_id == 10 || $user->role_id == 19){
            $dmc_ids = User::where('master_dmc_id', $user->userId)->get()->pluck('userId')->toArray();
            $restaurants = Restaurant::orderBy('restaurant_id', 'desc')->get()->filter(function($restaurant) use ($dmc_ids) {
                $selectedDmcIds = $restaurant->getSelectedDmcIds();
                return !empty(array_intersect($selectedDmcIds, $dmc_ids));
            });
        }
        elseif ($user->role_id == 11 || $user->role_id == 20) {
            $restaurants = Restaurant::with('hotel')->orderBy('restaurant_id', 'desc')->get()->filter(function($restaurant) use ($user) {
                return $restaurant->hasSelectedByDmc($user->userId);
            });
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
        
        elseif($user->role_id == 35 || $user->role_id == 130 || $user->role_id == 132 || $user->role_id == 133 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138){
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
            $dmcs = User::whereIn('role_id', [11,20])->where('country', $authuser->country)->get();
        }elseif($authuser->role_id == 3){
            $dmcs = User::whereIn('role_id', [11,20])->where('country', $authuser->country)->get();
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

        if(in_array($authuser->role_id, [11, 20, 35, 78, 120, 130, 132, 133, 135, 136, 137, 138])){
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
            $dmcs = User::whereIn('role_id', [11,20])->where('country', $authuser->country)->get();
        }elseif($authuser->role_id == 3){
            $dmcs = User::whereIn('role_id', [11,20])->where('country', $authuser->country)->get();
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
        
        // Handle HTML-only rendering request (for client-side image generation)
        if ($request->input('action') === 'render_html_only') {
            try {
                // Validate the incoming request
                $validated = $request->validate([
                    'restaurant_data' => 'required|array',
                    'booking_id' => 'required',
                    'tour_id' => 'required',
                    'display_id' => 'required',
                ]);
                
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
                
                // Return just the HTML for client-side rendering
                return response()->json([
                    'success' => true,
                    'message' => 'Restaurant coupon HTML generated successfully',
                    'html' => $html
                ]);
            } catch (\Exception $e) {
                // Log the error for debugging
                \Log::error('Error generating restaurant coupon HTML: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'request_data' => $request->all()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Error generating coupon HTML: ' . $e->getMessage()
                ], 500);
            }
        }

        // If not a browser-generated image or HTML-only request, return error
        return response()->json([
            'success' => false,
            'message' => 'Direct server-side voucher image generation is not supported. Please use the browser to generate and upload the voucher image.'
        ], 400);
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