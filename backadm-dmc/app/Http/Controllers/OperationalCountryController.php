<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\Facility;
use App\Models\Role;
use App\Models\User;
use App\Models\Hotel;
use App\Models\Country;
use App\Models\Meal;
use App\Models\City;
use Illuminate\Support\Facades\Auth;
use App\Models\Room;
use App\Models\Vehicle;
use App\Models\OperationalCountry;
use Illuminate\Support\Facades\DB;
use App\Helpers\CommonHelper;
use App\Services\LogActivityService;
use Illuminate\Support\Facades\Crypt;
class OperationalCountryController extends Controller
{
    /*
    * Display a listing of the Category.
    * Date 06-11-2024
    */
    public function index(Request $request)
    {
        if (!hasPermission('view country')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $countries = OperationalCountry::all();
        return view('operational_countries.countries', compact('countries'));
    }

    /*
    * Show the form for creating a new category.
    * Date 06-11-2024
    */
    public function create()
    {
        if (!hasPermission('create country')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $countries = Country::where('is_active', 1)->get();
        $authuser = auth()->user();
        if($authuser->role_id == 4){
            $dmcs = User::whereIn('role_id', [11,20])->where('country', $authuser->country)->get();
        }elseif($authuser->role_id == 3){
            $dmcs = User::whereIn('role_id', [11,20])->where('country', $authuser->country)->get();
        }else{
            $dmcs = User::where('role_id', 11)->get();
        }
        return view('operational_countries.add-country', compact('countries', 'dmcs'));
    }

    public function fetchCities(Request $request)
{
    $cities = City::where('country', $request->country_name)->get();
    return response()->json($cities);
}

public function getExistingCities(Request $request)
{
    $countryName = $request->input('country_name');
    
    // Fetch cities that already exist in your database for this country
    $existingCities = OperationalCountry::where('name', $countryName)
                        ->pluck('city')
                        ->toArray();
    
    return response()->json($existingCities);
}
// public function fetchCities(Request $request)
//     {
//         $cities = City::where('country', $request->country)->orderBy('name')->get();
//         return response()->json($cities);
//     }


    /*
    * Store a newly created role.
    * Date 07-10-2024
    */
    public function store(Request $request)
    {
        //dd($request->all());
        //Validate the incoming request data
        $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'holiday_charges' => ['required', 'numeric'],
                'holiday_dates' => ['required', 'string'],
        ]);

        $lastCountry = OperationalCountry::withTrashed()->orderBy('created_at', 'desc')->first();
        $country_max_id = $lastCountry->operational_country_id ?? 0;
        $countryId = CommonHelper::createId($country_max_id);
        while (OperationalCountry::where('operational_country_id', $countryId)->exists()) {
            $countryId = CommonHelper::createId($countryId);
        }
        $auth_user = Auth::user();
            if ($auth_user->role_id == 11 || $auth_user->role_id == 20) {
                $dmc_id = $auth_user->userId;
                $status = 2;
            }elseif($auth_user->role_id == 4){
                $dmc_id = $request->dmc;
                $status = 4;
            }elseif($auth_user->role_id == 3){
                $dmc_id = $request->dmc;
                $status = 5;
            }elseif($auth_user->role_id == 1 || $auth_user->role_id == 2){
                $dmc_id = $request->dmc;
                $status = 1;
            }

            $existingCity = OperationalCountry::where('city', $request->city_name)
            ->where('name', $request->name)
            ->first();
            if ($existingCity) {
                // Only add if the city doesn't already exist
                return redirect()->back()->with('error', 'City already exists.');
            }

        // Create a new City model instance
        $country = new OperationalCountry(); // Changed City to Country
        $country->name = $request->input('name');
        $country->city = $request->input('city_name');
        $country->operational_country_id = $countryId;
        
        $country->holiday_charges = $request->input('holiday_charges');
        $country->night_start_time = $request->input('night_start_time');
        $country->night_end_time = $request->input('night_end_time');
        $country->holiday_dates = $request->input('holiday_dates');
        $country->dmc_id = $dmc_id ?? 0;

        // Night charges fields
        $country->peakPeriod_start_time = $request->input('peakPeriod_start_time');
        $country->peakPeriod_end_time = $request->input('peakPeriod_end_time');
        $country->peak_period_charge = $request->input('peak_period_charge');
        $country->created_by = $auth_user->userId;

        // Save the city data
        if ($country->save()) {
            // Log activity for successful creation
            LogActivityService::log('create_country', 'App\Models\OperationalCountry', $country->operational_country_id, $country);
            return redirect()->route('country.index')->with('success', 'Country added successfully!');
        } else {
            // Log activity for failure
            LogActivityService::log('create_country_failed', 'App\Models\OperationalCountry', $country->operational_country_id, 'An error occurred while saving the country details.');
            return redirect()->back()->with('error', 'An error occurred while saving the country details.');
        }
    }
    
    /*
    * Show the form fors editing the specified role.
    * Date 07-10-2024
    */
    public function edit($id)
    {
        if (!hasPermission('edit country')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $id = Crypt::decrypt($id);
        $vehicles = Vehicle::where('is_available', 1)->get();
        $country = OperationalCountry::where('operational_country_id',$id)->first();
        return view('operational_countries.edit-country', compact('country', 'vehicles'));
    }
    /*
    * Update the specified role.
    * Date 07-10-2024
    */
    public function update(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // 'base_distance' => ['required', 'numeric', 'min:0'],
            // 'cost_per_km_below_10' => ['required', 'numeric', 'min:0'],
            // 'cost_per_km_10_to_25' => ['required', 'numeric', 'min:0'],
            // 'cost_per_km_above_25' => ['required', 'numeric', 'min:0'],
            // 'cost_per_hour' => ['required', 'numeric', 'min:0'],
            // 'cancel_cost' => ['required', 'numeric', 'min:0'],
            // 'night_cost_per_km_below_10' => ['required', 'numeric', 'min:0'],
            // 'night_cost_per_km_10_to_25' => ['required', 'numeric', 'min:0'],
            // 'night_cost_per_km_above_25' => ['required', 'numeric', 'min:0'],
            // 'night_cost_per_hour' => ['required', 'numeric', 'min:0'],
            // 'night_cancel_cost' => ['required', 'numeric', 'min:0'],
            'holiday_charges' => ['required', 'numeric'],
            // 'entry_port_pickup_charge' => ['nullable', 'numeric', 'min:0'],
            // 'exit_port_drop_charge' => ['nullable', 'numeric', 'min:0'],
        ]);
        $id = $request->operational_country_id;

        $country = OperationalCountry::where('operational_country_id', $id)->first();
        $country->name = $request->input('name');
        $country->city = $request->input('city_name');
        // $country->distance_unit = $request->input('distance_unit');
        // $country->country_currency = $request->input('country_currency');
        // $country->base_distance = $request->input('base_distance');
        // $country->cost_per_km_below_10 = $request->input('cost_per_km_below_10');
        // $country->cost_per_km_10_to_25 = $request->input('cost_per_km_10_to_25');
        // $country->cost_per_km_above_25 = $request->input('cost_per_km_above_25');
        // $country->cost_per_hour = $request->input('cost_per_hour');
        // $country->cancel_cost = $request->input('cancel_cost');
        // $country->charge_type = $request->input('night_charge_type');
        $country->holiday_charges = $request->input('holiday_charges');
        $country->holiday_dates = $request->input('holiday_dates');
        $country->night_start_time = $request->input('night_start_time');
        $country->night_end_time = $request->input('night_end_time');
        // $country->entry_port_pickup_charge = $request->input('entry_port_pickup_charge', 0);
        // $country->exit_port_drop_charge = $request->input('exit_port_drop_charge', 0);
        // $country->vehicle_id = $request->input('vehicle', 0);

        // Night charges fields
        $country->peakPeriod_start_time = $request->input('peakPeriod_start_time');
        $country->peakPeriod_end_time = $request->input('peakPeriod_end_time');
        // $country->night_cost_per_km_below_10 = $request->input('night_cost_per_km_below_10');
        // $country->night_cost_per_km_10_to_25 = $request->input('night_cost_per_km_10_to_25');
        // $country->night_cost_per_km_above_25 = $request->input('night_cost_per_km_above_25');
        // $country->night_cost_per_hour = $request->input('night_cost_per_hour');
        // $country->night_cancel_cost = $request->input('night_cancel_cost');
        $country->peak_period_charge = $request->input('peak_period_charge');

        // Save the country data
        if ($country->save()) {
            // Log activity for successful creation
            LogActivityService::log('edit_country', 'App\Models\OperationalCountry', $country->operational_country_id, $country);
            return redirect()->route('country.index')->with('success', 'Country updated successfully!');
        } else {
            // Log activity for failure
            LogActivityService::log('edit_country_failed', 'App\Models\OperationalCountry', $country->operational_country_id, 'An error occurred while saving the updated country details.');
            return redirect()->route('country.index')->with('error', 'Error while updating Country details!');
        } 
    }

    /*
    * Soft Delete Roles.
    * Date 07-10-2024
    */
    public function destroy($id)
    {
        if (!hasPermission('delete country')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $country = OperationalCountry::where('operational_country_id', $id)->first();
        $delete = $country->delete();
        if ($delete) {
            LogActivityService::log('deleted_country_details', 'App\Models\OperationalCountry', $country->operational_country_id, 'An error occurred while saving the updated country details.');
            return redirect()->route('country.index')
                ->with('success', 'Country details deleted successfully');
        } else {
            return redirect()->route('country.index')
                ->with('error', 'Country details could not be deleted!');
        }
    }

    
    public function getCities(Request $request)
    {
        $countryName = $request->query('country_name');
        
        $cities = City::where('country', $countryName)
                     ->select('name', 'country')
                     ->get();

        return response()->json(['cities' => $cities]);
    }
}
