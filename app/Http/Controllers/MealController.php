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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Crypt;

class MealController extends Controller
{
    /*
    * Display a listing of the Category.
    * Date 02-01-2025
    */
    public function index(Request $request)
    {
        if (!hasPermission('view meal')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $meals = Meal::with('restaurant')->get();
        return view('meals.meals', compact('meals'));
    }

    /*
    * Show the form for creating a new category.
    * Date 02-01-2025
    */
    public function create(){
        if (!hasPermission('create meal')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $meals = Meal::with('restaurant')->get();
        $auth_user = Auth::user();
        $dmcUsers = collect();
        if ($auth_user->role_id == 1) {
            $dmcUsers = User::where('role_id', 11)
            ->where('user_type', 2)
            ->select('userId', 'name', 'company_name')
            ->orderBy('company_name', 'asc')
            ->get();
        }
        $restaurants = Restaurant::where('is_active', 1)->get();
        return view('meals.add-meals', compact('restaurants', 'meals', 'auth_user', 'dmcUsers'));
    }

    /*
    * Store a newly created role.
    * Date 02-01-2025
    */
    public function store(Request $request)
    {
        // Validate the incoming request data
        try {
            $request->validate([
                'meal_category' => 'required',
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

        $auth_user = Auth::user();
        if($auth_user->role_id == 1 || $auth_user->role_id == 20){
            $dmc_id = $request->input('dmc_id');
        }else if($auth_user->role_id == 11){
            $dmc_id = $auth_user->userId;
        }else if($auth_user->role_id == 35 || in_array($auth_user->role_id, [130, 132, 133, 135, 136, 137, 138])){
            $dmc_id = $auth_user->created_by;
        }else if($auth_user->role_id == 78 || $auth_user->role_id == 139){
            $sales_head = User::where('userId', $auth_user->created_by)->first();
            $dmc_id = $sales_head->created_by;
        }else if($auth_user->role_id == 120 || $auth_user->role_id == 140){
            $sales_manager = User::where('userId', $auth_user->created_by)->first();
            $sales_head = User::where('userId', $sales_manager->created_by)->first();
            $dmc_id = $sales_head->created_by;
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

        $image = '';
        if ($request->hasFile('item_file')) {
            
            $pathData = CommonHelper::image_path('file_storage', $request->file('item_file'));
            if (!empty($pathData['master_value'])) {
                $image = $pathData['master_value'];
            }
        }
        $auth_user = Auth::user();
        //Create a new restaurant record
        $meal = new Meal();
        $meal->meal_id = $mealId;
        $meal->name = $request->input('name');
        $meal->restaurant_id = $request->restaurant_id;
        $meal->item_description = $request->item_description;
        $meal->type = $request->input('meal_type');
        $meal->meal_period = $request->input('meal_period');
        $meal->price = $request->input('price');
        $meal->item_cost_price = $request->input('item_cost_price');
        $meal->adult_price = $request->input('adult_price');
        $meal->adult_cost_price = $request->input('adult_cost_price');
        $meal->child_price = $request->input('child_price');
        $meal->child_cost_price = $request->input('child_cost_price');
        $meal->category = $request->input('meal_category');
        $meal->files = $image;
        $meal->item_type = $request->input('item_type');
        $meal->is_active = $request->input('meal_status') == 1 ? 1 : 0;
        $meal->created_by = $auth_user->userId;
        $meal->dmc_id = $dmc_id;

        $meal->save();

        return redirect()->route('meals.restaurant_create', Crypt::encrypt($request->restaurant_id))->with('success', 'Meal added successfully!');
    }

    /*
    * Show the form fors editing the specified role.
    * Date 02-01-2025
    */
    public function edit($id)
    {
        if (!hasPermission('edit meal')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $id = Crypt::decrypt($id);
        $restaurants = Restaurant::where('is_active', 1)->get();

        $meals = Meal::where('meal_id',$id)->first();
        return view('meals.edit-meal', compact('meals', 'restaurants'));
    }
    /*
    * Update the specified role.
    * Date 07-10-2024
    */
    public function update(Request $request, $id)
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
        $id = Crypt::decrypt($id);
        
        // $image = $request->file('files');
        // if($image){
        // $storage_file = CommonHelper::image_path('meals', $image);
        // }

        // $image = $meal->files ?? '';

        // if ($request->hasFile('item_file')) {
        //     $masterImagePath = CommonHelper::image_path('file_storage', $request->file('item_file'));
        //     if (!empty($masterImagePath['master_value'])) {
        //         $image = $masterImagePath['master_value'];
        //     }
        // }

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
        $meal->restaurant_id = $request->restaurant_id;
        $meal->item_description = $request->item_description;
        $meal->name = $request->input('name');
        $meal->type = $request->input('meal_type');
        $meal->price = $request->input('price');
        $meal->item_cost_price = $request->input('item_cost_price');
        $meal->adult_price = $request->input('adult_price');
        $meal->adult_cost_price = $request->input('adult_cost_price');
        $meal->child_price = $request->input('child_price');
        $meal->child_cost_price = $request->input('child_cost_price');
        $meal->category = $request->input('category');
        $meal->item_type = $request->input('item_type');
        $meal->files = $image;
        $meal->meal_period = $request->input('meal_period');
        $meal->is_active = $request->input('meal_status') == 1 ? 1 : 0;
        
        $meal->save();

        return redirect()->route('meals.restaurant_create', Crypt::encrypt($request->restaurant_id))->with('success', 'Meal details updated successfully.');
    }

    /*
    * Soft Delete Roles.
    * Date 07-10-2024
    */
    public function destroy($id)
    {
        if (!hasPermission('delete meal')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $id = Crypt::decrypt($id);
        $meal = Meal::where('meal_id',$id)->first();
        $delete = $meal->delete();
        return redirect()->route('meals.restaurant_create', Crypt::encrypt($meal->restaurant_id))->with('success', 'Meal details deleted successfully.');
        
    
    }

}
