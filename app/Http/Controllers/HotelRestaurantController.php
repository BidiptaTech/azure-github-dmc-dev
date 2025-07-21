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

class HotelRestaurantController extends Controller
{
    /*
    * Display a listing of the restaurant.
    * Date 06-11-2024
    */
    public function index(Request $request)
    {
        // if (!hasPermission('view restaurant')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        $user = auth()->user();
        $restaurants = [];
        if ($user->role_id == 4) {
            $dmc_ids = User::with('hotel')->where('assistant_manager_id', $user->userId)->pluck('userId')->toArray();
            $restaurants = Restaurant::whereIn('status', [4, 5, 1])
                // ->whereIn('dmc_id', $dmc_ids)
                ->orderBy('id', 'DESC')
                ->get();
        } elseif ($user->role_id == 3) {
            $restaurants = Restaurant::with('hotel')->orderBy('updated_at', 'desc')->whereIn('status', [5, 1])->get();
        } elseif (in_array($user->role_id, [1, 2, 23])) {
            $restaurants = Restaurant::with('hotel')->orderBy('updated_at', 'desc')->whereIn('status', [1, 3])->get();
        }
        elseif($user->role_id == 10 || $user->role_id == 19){
            $dmc_ids = User::where('master_dmc_id', $user->userId)->get()->pluck('userId')->toArray();
            $restaurants = Restaurant::orderBy('updated_at', 'desc')->whereIn('dmc_id', $dmc_ids)->get();
        }
        elseif ($user->role_id == 11 || $user->role_id == 20) {
            $restaurants = Restaurant::with('hotel')->orderBy('updated_at', 'desc')->where('dmc_id', $user->userId)->get();
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
            $restaurants = Restaurant::orderBy('updated_at', 'desc')->whereIn('dmc_id', $dmc_ids)->get();
        }
        
        elseif($user->role_id == 35 || $user->role_id == 130 || $user->role_id == 132 || $user->role_id == 133 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138){
            $restaurants = Restaurant::orderBy('updated_at', 'desc')->where('dmc_id', $user->created_by)->get();
        }
        elseif($user->role_id == 78){
            $assistant_product_manager_ids = User::where('created_by', $user->userId)->get()->pluck('userId')->toArray();
            if($assistant_product_manager_ids){
                $restaurants = Restaurant::orderBy('updated_at', 'desc')->whereIn('created_by', $assistant_product_manager_ids)->orWhere('created_by', $user->userId)->get();
            }else{
                $restaurants = Restaurant::orderBy('updated_at', 'desc')->where('created_by', $user->userId)->get();
            }
        }
        elseif($user->role_id == 120){
            $restaurants = Restaurant::orderBy('updated_at', 'desc')->where('created_by', $user->userId)->get();
        }
        // $restaurants = Restaurant::with('hotel')->get();
        return view('restaurants.restaurant', compact('restaurants'));
    }

    public function restaurantApproval(Request $request)
    {
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

        if ($request->hasFile('master_image')) {
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
    * Show the form for creating a new restaurant.
    * Date 06-11-2024
    */
    public function create($id, Request $request)
    {
        $hotel = Hotel::where('hotel_unique_id', $id)->first();
        if (!$hotel) {
            abort(404, 'Hotel not found.');
        }
        if (!hasPermission('create restaurant')) {
            abort(403, 'You do not have permission to access this page.');
        }
        
        $auth_user = Auth::user();
        if($auth_user->user_type == 1){
            $hotels = Hotel::get();
        }elseif($auth_user->user_type == 2){
            // Use JSON query for PostgreSQL to check if dmc_id array contains the user ID
            $hotels = Hotel::whereJsonContains('dmc_id', $auth_user->userId)->get();
        }else {
            // Use JSON query for PostgreSQL to check if dmc_id array contains the user ID
            $hotels = Hotel::whereJsonContains('dmc_id', $auth_user->userId)->get();
        }
        
        // Handle dmc_id as array since it's cast as array in Hotel model
        $dmcIds = $hotel->getSelectedDmcIds();
        $currentUserDmcId = null;
        
        // For DMC users, get their own DMC ID
        if ($auth_user->role_id == 11 || $auth_user->role_id == 20) {
            $currentUserDmcId = $auth_user->userId;
        } else {
            // For admin users (role_id 1 and 20), handle DMC selection
            if ($auth_user->role_id == 1 || $auth_user->role_id == 20) {
                // Check if DMC is selected via query parameter
                if ($request->has('dmc_id') && !empty($request->dmc_id)) {
                    $selectedDMC = User::where('userId', $request->dmc_id)->where('role_id', 11)->first();
                    if ($selectedDMC) {
                        $currentUserDmcId = $request->dmc_id;
                    }
                }
                
                // If no valid DMC selected, try hotel's associated DMCs
                if (!$currentUserDmcId && !empty($dmcIds)) {
                    $currentUserDmcId = $dmcIds[0];
                }
                
                // If still no DMC, get any available DMC
                if (!$currentUserDmcId) {
                    $anyDMC = User::where('role_id', 11)->first();
                    if ($anyDMC) {
                        $currentUserDmcId = $anyDMC->userId;
                    }
                }
            } else {
                // For other users, use the first available DMC ID
                $currentUserDmcId = !empty($dmcIds) ? $dmcIds[0] : null;
            }
        }
        
        if (!$currentUserDmcId) {
            abort(404, 'No DMC found for this hotel.');
        }
        
        $userDMC = User::where('userId', $currentUserDmcId)->first();
        if (!$userDMC) {
            abort(404, 'DMC user not found.');
        }
        
        if($auth_user->role_id == 1 || $auth_user->role_id == 20){
            $restaurants = Restaurant::where('owned_by', $hotel->hotel_unique_id)->get();
        }else{
            $restaurants = Restaurant::where('owned_by', $hotel->hotel_unique_id)->get();
        }
        
        // Get available DMCs for admin users to choose from
        $availableDMCs = [];
        if ($auth_user->role_id == 1 || $auth_user->role_id == 20) {
            $availableDMCs = User::where('role_id', 11)->get();
        }
        
        $authuser = auth()->user();
        $userCountry = $userDMC->country;
        $cities = City::where('country', $userCountry)->get();

        return view('hotel.add-hotel-restaurant', compact('hotels', 'auth_user', 'cities', 'userCountry', 'hotel', 'userDMC', 'restaurants', 'availableDMCs'));
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
            if(auth()->user()->role_id ==35){
                $userdmc = User::where('userId', auth()->user()->created_by)->first();
                $dmc_id = $userdmc->userId;
                $status = 1;
            }
            elseif(auth()->user()->role_id == 77){
                $user_product_head = User::where('userId', auth()->user()->created_by)->first();
                $user_product_head_dmc = User::where('userId', $user_product_head->created_by)->first();
                $dmc_id = $user_product_head_dmc->userId;
                $status = 1;
            }
            elseif(auth()->user()->role_id == 84){
                $user_product_manager = User::where('userId', auth()->user()->created_by)->first();
                $user_product_head = User::where('userId', $user_product_manager->created_by)->first();
                $user_product_head_dmc = User::where('userId', $user_product_head->created_by)->first();
                $dmc_id = $user_product_head_dmc->userId;
                $status = 1;
            }
            elseif(auth()->user()->role_id == 11) {
                $dmc_id = auth()->user()->userId;
                $status = 1;
            }
            else{
                $dmc_id = $request->dmc;
                $status = 1;
            }
            // 🔍 Check for existing restaurant at same lat/lng for this DMC
            $existingRestaurant = Restaurant::where('latitude', $request->latitude)
                ->where('longitude', $request->longitude)
                ->whereJsonContains('dmc_id', $dmc_id)
                ->where('owned_by', $request->hotel_id)
                ->first();

            if ($existingRestaurant) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'A Restaurant already exists at this location for the selected DMC.');
            }
            $hotel_id = $request->hotel_id;
           
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

        $restaurant->owned_by = $request->input('hotel_id');
        $restaurant->restaurant_id = $restaurantId;

        $restaurant->property = $request->input('property');
        //$restaurant->is_active = $restaurant->restaurant_status;
        $restaurant->images = $imagePathsJson;
        $restaurant->master_image = $masterImage;
        $restaurant->is_active = $request->input('restaurant_status');
        $restaurant->status = $status;
        $restaurant->dmc_id = $dmc_id;
        $restaurant->description = $request->input('description');
        $restaurant->remarks = $request->input('remarks');
        $restaurant->terms_conditions = $request->input('terms_conditions');
        $restaurant->created_by = $auth_user->userId;
        $restaurant->save();

        $restaurant_id = $restaurant->restaurant_id;
        return redirect()->route('hotel-meals-create', [
            'dmc_id' => $dmc_id,
            'hotel_id' => $hotel_id,
        ])->with('success', 'Restaurant added successfully!');
    }

    /*
    * Show the form fors editing the specified role.
    * Date 07-10-2024
    */
    public function edit($id)
    {
        // if (!hasPermission('edit restaurant')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
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

        return view('hotel.edit-hotel-restaurant', compact('restaurant', 'hotels', 'mealTypes', 'country', 'city','dmcs'));
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

        if ($request->hasFile('master_image')) {
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

        return redirect()->route('hotel-restaurant-create', $restaurant->owned_by)->with('success', 'Restaurant details updated successfully.');
    }

    /*
    * Soft Delete Roles.
    * Date 07-10-2024
    */
    public function destroy($id)
    {
        // if (!hasPermission('delete restaurant')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        $isUsedInRooms = Room::where('breakfast_restaurant', $id)
        ->orWhere('lunch_restaurant', $id)
        ->orWhere('dinner_restaurant', $id)
        ->exists();
        if ($isUsedInRooms) {
        // The restaurant is being used in the rooms table, so do not delete it
        return redirect()->route('restaurant.index')
        ->with('error', 'This Restaurant is in use, cannot be deleted!');
        }
        $restaurant = Restaurant::where('restaurant_id', $id)->first();

        $restaurant->delete();
        return redirect()->route('hotel-restaurant-create', $restaurant->owned_by)
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


    //hotel meals create
    public function mealsCreate($dmc_id, $hotel_id){
        if (!hasPermission('create meal')) {
            abort(403, 'You do not have permission to access this page.');
        }

        $hotel = Hotel::where('hotel_unique_id', $hotel_id)->first();
        $auth_user = Auth::user();
        
        // Get restaurant_id from URL parameter if provided
        $restaurant_id = request()->get('restaurant_id');
        $selectedRestaurant = null;
        
        // Handle different user roles
        if($auth_user->role_id == 1 || $auth_user->role_id == 20) {
            // Admin users - get all DMCs and selected DMC data
            $dmcs = User::whereIn('role_id', [11,20])->get();
            
            // Handle case when no DMC is selected (dmc_id = 0 or invalid)
            if($dmc_id == '0' || !$dmc_id) {
                $userDMC = null;
                $restaurants = collect();
            } else {
                $userDMC = User::where('userId', $dmc_id)->first();
            }
        } else {
            // Regular DMC users - only their own data
            $dmcs = collect(); // Empty for non-admin users
            $userDMC = User::where('userId', $dmc_id)->first();
            if (!$userDMC) {
                abort(404, 'DMC user not found.');
            }
        }
        
        // Determine the correct DMC ID for filtering meals
        $filterDmcId = $dmc_id;
        if($auth_user->role_id != 1 && $auth_user->role_id != 20) {
            // For non-admin users, use their own DMC ID
            $filterDmcId = $auth_user->userId;
        }
        
        // If restaurant_id is provided, filter for that specific restaurant
        if($restaurant_id) {
            $selectedRestaurant = Restaurant::where('restaurant_id', $restaurant_id)
                ->where('owned_by', $hotel_id)
                ->where('is_active', 1)
                ->first();
                
            if($selectedRestaurant) {
                // Verify the user has access to this restaurant
                $restaurantDmcIds = $selectedRestaurant->getSelectedDmcIds();
                if($auth_user->role_id == 1 || $auth_user->role_id == 20 || in_array($auth_user->userId, $restaurantDmcIds)) {
                    $restaurants = collect([$selectedRestaurant]);
                    $restaurantIds = [$restaurant_id];
                    // Filter meals for the specific restaurant and correct DMC
                    $meals = Meal::where('restaurant_id', $restaurant_id)
                        ->where('dmc_id', $filterDmcId)
                        ->get();
                } else {
                    // User doesn't have access to this restaurant
                    $restaurants = collect();
                    $meals = collect();
                }
            } else {
                $restaurants = collect();
                $meals = collect();
            }
        } else {
            // Default behavior - show restaurants the user has access to
            if($auth_user->role_id == 1 || $auth_user->role_id == 20) {
                // Admin users can see all restaurants for the hotel
                $restaurants = Restaurant::where('owned_by', $hotel_id)
                    ->where('is_active', 1)
                    ->get();
            } else {
                // Regular DMC users only see their restaurants
                $restaurants = Restaurant::where('owned_by', $hotel_id)
                    ->where('is_active', 1)
                    ->whereJsonContains('dmc_id', $auth_user->userId)
                    ->get();
            }
            
            $restaurantIds = $restaurants->pluck('restaurant_id');
            $meals = Meal::whereIn('restaurant_id', $restaurantIds)
                ->where('dmc_id', $filterDmcId)
                ->get();
        }
        
        return view('hotel.add-meals', compact('restaurants', 'meals', 'hotel', 'userDMC', 'dmcs', 'auth_user', 'selectedRestaurant'));
    }

    /**
     * Fetch meals for a specific DMC (AJAX endpoint for admin users)
     */
    public function fetchDmcMeals(Request $request, $hotel_id)
    {
        $dmc_id = $request->input('dmc_id');
        
        if (!$dmc_id) {
            return response()->json(['error' => 'DMC ID is required'], 400);
        }

        try {
            // Get restaurants for the selected DMC and hotel
            $restaurants = Restaurant::where('owned_by', $hotel_id)
                ->where('is_active', 1)
                ->whereJsonContains('dmc_id', $dmc_id)
                ->get();
                
            $restaurantIds = $restaurants->pluck('restaurant_id');
            $meals = Meal::whereIn('restaurant_id', $restaurantIds)->get();

            $mealsData = $meals->map(function ($meal) {
                $mealPeriod = '';
                switch($meal->meal_period) {
                    case 1: $mealPeriod = 'Breakfast'; break;
                    case 2: $mealPeriod = 'Lunch'; break;
                    case 3: $mealPeriod = 'Dinner'; break;
                    default: $mealPeriod = 'Unknown'; break;
                }

                $mealType = '';
                switch($meal->type) {
                    case 1: $mealType = 'Buffet'; break;
                    case 2: $mealType = 'Set Menu'; break;
                    case 3: $mealType = 'A-La-Carte'; break;
                    default: $mealType = 'Unknown'; break;
                }

                return [
                    'meal_id' => $meal->meal_id,
                    'name' => $meal->name ?? 'N/A',
                    'restaurant_name' => $meal->restaurant ? $meal->restaurant->name : 'Unknown',
                    'type' => $mealType,
                    'meal_period' => $mealPeriod,
                    'item_description' => $meal->item_description,
                    'is_active' => $meal->is_active
                ];
            });

            $restaurantsData = $restaurants->map(function ($restaurant) {
                return [
                    'restaurant_id' => $restaurant->restaurant_id,
                    'name' => $restaurant->name
                ];
            });

            return response()->json([
                'success' => true,
                'meals' => $mealsData,
                'restaurants' => $restaurantsData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch meals: ' . $e->getMessage()
            ], 500);
        }
    }


    public function mealStore(Request $request)
    {
        $auth_user = Auth::user();
        
        // Base validation rules
        $rules = [
            'meal_category' => 'required',
            'item_type' => 'nullable|integer',
            'meal_type' => 'required|string',
            'restaurant_id' => 'required|integer',
            'item_description' => 'required|string',
            'meal_status' => 'nullable|integer',
        ];
        
        // For admin and role_id 20, DMC selection is required
        if ($auth_user->role_id == 1 || $auth_user->role_id == 20) {
            $rules['dmc_id'] = 'required|exists:users,userId';
        }
        
        // Validate the incoming request data
        try {
            $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Catch Validation Errors
            dd($e->errors());
        }
        $lastMeal = Meal::withTrashed()->orderBy('created_at', 'desc')->first();
        $meal_max_id = $lastMeal->meal_id ?? 0;
        $mealId = CommonHelper::createId($meal_max_id);
        while (Meal::where('meal_id', $mealId)->exists()) {
            $mealId = CommonHelper::createId($mealId);
        }

        // $image = $request->file('item_file');
        // if($image){
        // $storage_file = CommonHelper::image_path('meals', $image);
        // }
        $dmc_id = $request->dmc_id;
        $hotel_id = $request->hotel_id;

        $image = '';
        if ($request->hasFile('item_file')) {
            
            $pathData = CommonHelper::image_path('file_storage', $request->file('item_file'));
            if (!empty($pathData['master_value'])) {
                $image = $pathData['master_value'];
            }
        }
        // Set DMC ID based on user role
        $dmcId = null;
        if ($auth_user->role_id == 1 || $auth_user->role_id == 20) {
            // Admin/Manager users: use the selected DMC ID
            $dmcId = $request->input('dmc_id');
        } else {
            // Regular DMC users: use their own user ID
            $dmcId = $auth_user->userId;
        }
        
        //Create a new restaurant record
        $meal = new Meal();
        $meal->meal_id = $mealId;
        $meal->name = $request->input('name');
        $meal->restaurant_id = $request->restaurant_id;
        $meal->item_description = $request->item_description;
        $meal->type = $request->input('meal_type');
        $meal->meal_period = $request->input('meal_period');
        $meal->price = $request->input('price');
        $meal->adult_price = $request->input('adult_price');
        $meal->child_price = $request->input('child_price');
        $meal->category = $request->input('meal_category');
        $meal->files = $image;
        $meal->item_type = $request->input('item_type');
        $meal->is_active = $request->input('meal_status') == 1 ? 1 : 0;
        $meal->created_by = $auth_user->userId;
        $meal->dmc_id = $dmcId; // Set DMC ID based on user role

        $meal->save();

        return redirect()->route('hotel-meals-create', [
            'dmc_id' => $dmc_id,
            'hotel_id' => $hotel_id,
        ])->with('success', 'Meal added successfully!');
    }

    public function mealEdit($id)
    {
        // if (!hasPermission('edit meal')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        
        $meal = Meal::where('meal_id',$id)->first();
        if (!$meal) {
            abort(404, 'Meal not found.');
        }

        $restaurant = Restaurant::where('restaurant_id', $meal->restaurant_id)->first();
        if (!$restaurant) {
            abort(404, 'Restaurant not found.');
        }

        $auth_user = Auth::user();
        
        // Check if user has permission to edit this meal
        if($auth_user->role_id != 1 && $auth_user->role_id != 20) {
            // For non-admin users, check if they own this meal's restaurant
            $dmcIds = $restaurant->getSelectedDmcIds();
            if(!in_array($auth_user->userId, $dmcIds)) {
                abort(403, 'You do not have permission to edit this meal.');
            }
        }

        $restaurants = Restaurant::where('restaurant_id', $meal->restaurant_id)->where('is_active', 1)->get();
        return view('hotel.edit-meals', compact('meal', 'restaurants'));
    }

    //update
    public function mealUpdate(Request $request, $id)
    {
        try {
            $request->validate([
                'category' => 'required',
                'item_type' => 'nullable|integer',
                'meal_type' => 'required|string',
                'restaurant_id' => 'required|integer',
                'item_description' => 'required|string',
                'meal_status' => 'nullable|integer',
            ]);
        
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Catch Validation Errors
            dd($e->errors());
        }

        // In the update method of your MealController, add this logic:
        $image = $meal->files ?? '';

        if ($request->has('remove_image')) {
            $image = ''; // Clear the image path if remove flag is set
        } elseif ($request->hasFile('item_file')) {
            $masterImagePath = CommonHelper::image_path('file_storage', $request->file('item_file'));
            if (!empty($masterImagePath['master_value'])) {
                $image = $masterImagePath['master_value'];
            }
        }

        $meal = Meal::where('meal_id',$id)->first();
        $restaurant = Restaurant::where('restaurant_id', $meal->restaurant_id)->first();
        
        // Handle restaurant dmc_id as array since it's cast as array in Restaurant model
        $dmcIds = $restaurant->getSelectedDmcIds();
        $dmc_id = !empty($dmcIds) ? $dmcIds[0] : null;
        $hotel_id = $restaurant->owned_by;
        $meal->restaurant_id = $request->restaurant_id;
        $meal->item_description = $request->item_description;
        $meal->name = $request->input('name');
        $meal->type = $request->input('meal_type');
        $meal->price = $request->input('price');
        $meal->adult_price = $request->input('adult_price');
        $meal->child_price = $request->input('child_price');
        $meal->category = $request->input('category');
        $meal->item_type = $request->input('item_type');
        $meal->files = $image;
        $meal->meal_period = $request->input('meal_period');
        $meal->is_active = $request->input('meal_status') == 1 ? 1 : 0;
        
        $meal->save();

        return redirect()->route('hotel-meals-create', [
            'dmc_id' => $dmc_id,
            'hotel_id' => $hotel_id,
        ])->with('success', 'Meal details updated successfully.');
    }

    public function mealDestroy($id)
    {
        // if (!hasPermission('delete meal')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        $meal = Meal::where('meal_id',$id)->first();
        if (!$meal) {
            abort(404, 'Meal not found.');
        }

        $restaurant = Restaurant::where('restaurant_id', $meal->restaurant_id)->first();
        if (!$restaurant) {
            abort(404, 'Restaurant not found.');
        }

        $auth_user = Auth::user();
        
        // Handle restaurant dmc_id as array since it's cast as array in Restaurant model
        $dmcIds = $restaurant->getSelectedDmcIds();
        $dmc_id = !empty($dmcIds) ? $dmcIds[0] : null;
        $hotel_id = $restaurant->owned_by;

        // Check if user has permission to delete this meal
        if($auth_user->role_id != 1 && $auth_user->role_id != 20) {
            // For non-admin users, check if they own this meal's restaurant
            if(!in_array($auth_user->userId, $dmcIds)) {
                abort(403, 'You do not have permission to delete this meal.');
            }
        }

        $delete = $meal->delete();
        return redirect()->route('hotel-meals-create', [
            'dmc_id' => $dmc_id,
            'hotel_id' => $hotel_id,
        ])->with('success', 'Meal details deleted successfully.');
        
    
    }


}



