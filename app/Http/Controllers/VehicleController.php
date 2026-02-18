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
use App\Models\Room;
use App\Models\City;
use App\Models\Vehicle;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;
use App\Helpers\CommonHelper;
use App\Models\Port;
use App\Models\VehicleZoneMapping;
use App\Models\Zone;
use App\Services\LogActivityService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Crypt;

class VehicleController extends Controller
{
    /*
    * Display a listing of the Category.
    * Date 06-11-2024
    */
    public function index(Request $request)
    {
        if (!hasPermission('view vehicle')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $user = auth()->user();
        if ($user->role_id == 4) {
            $dmc_ids = User::where('assistant_manager_id', $user->userId)->pluck('userId')->toArray();
            $vehicles = Vehicle::with(['dmc'])->orderBy('created_at', 'desc')->whereIn('dmc_id', $dmc_ids)->get();
        } elseif ($user->role_id == 3) {
            $vehicles = Vehicle::with(['dmc'])->orderBy('created_at', 'desc')->get();
        } elseif (in_array($user->role_id, [1, 2, 23, 20])) {
            $vehicles = Vehicle::with(['dmc'])->orderBy('created_at', 'desc')->get();
        }
        elseif ($user->role_id == 10) {
            $dmc_ids = User::where('master_dmc_id', $user->userId)->get()->pluck('userId')->toArray();
            $vehicles = Vehicle::with(['dmc'])->orderBy('created_at', 'desc')->whereIn('dmc_id', $dmc_ids)->get();
        }
         elseif ($user->role_id == 11 || $user->role_id == 20) {
            $vehicles = Vehicle::with(['dmc'])->orderBy('created_at', 'desc')->where('dmc_id', $user->userId)->get();
        }
         elseif ($user->role_id == 20) {
            $vehicles = Vehicle::with(['dmc'])->orderBy('created_at', 'desc')->where('dmc_id', $user->userId)->get();
        }
        elseif(in_array($user->role_id, [25, 62, 110])){
            if($user->role_id == 25){
                $master_dmc_id = $user->created_by;
            }
            elseif($user->role_id == 62){
                $product_head = User::where('userId', $user->created_by)->first();
                $master_dmc_id = $product_head->created_by;
            }
            elseif($user->role_id == 110){
                $product_manager = User::where('userId', $user->created_by)->first();
                $product_head = User::where('userId', $product_manager->created_by)->first();
                $master_dmc_id = $product_head->created_by;
            }
            
            $dmc_ids = User::where('master_dmc_id', $master_dmc_id)->get()->pluck('userId')->toArray();
            $vehicles = Vehicle::with(['dmc'])->orderBy('created_at', 'desc')->whereIn('dmc_id', $dmc_ids)->get();
        } 
        elseif($user->role_id == 35 || $user->role_id == 130 || $user->role_id == 132 || $user->role_id == 133 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138){
            $vehicles = Vehicle::with(['dmc'])->orderBy('created_at', 'desc')->where('dmc_id', $user->created_by)->get();
        }
        elseif($user->role_id == 76 || $user->role_id == 139){
            $product_head = User::where('userId', $user->created_by)->first();
            $vehicles = Vehicle::with(['dmc'])->orderBy('created_at', 'desc')->where('dmc_id', $product_head->created_by)->get();

        }
        elseif($user->role_id == 111 || $user->role_id == 140){
            $product_manager = User::where('userId', $user->created_by)->first();
            $product_head = User::where('userId', $product_manager->created_by)->first();
            $vehicles = Vehicle::with(['dmc'])->orderBy('created_at', 'desc')->where('dmc_id', $product_head->created_by)->get();
        }

        return view('vehicles.vehicle', compact('vehicles'));
    }

    /*
    * Show the form for creating a new category.
    * Date 06-11-2024
    */
    public function create()
    {
        if (!hasPermission('create vehicle')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $authuser = auth()->user();
        $cities = City::where('country', $authuser->country)->get();

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
                $master_dmc_id = $authuser->userId;
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
        if($authuser->role_id == 11 || $authuser->role_id == 35 || $authuser->role_id == 76 || $authuser->role_id == 111 || $authuser->role_id == 139 || $authuser->role_id == 140){
            
        }

        // Check if we're in the zone mapping tab
        if (request()->has('zone_mapping') && request()->has('vehicle_id')) {
            $vehicle = Vehicle::where('vehicle_id', request()->get('vehicle_id'))->first();
            if ($vehicle) {
                $dmc_country = User::where('userId', $vehicle->dmc_id)->first()->country;
                $zones = Zone::where('dmc_id', $vehicle->dmc_id)->get();
                $ports = Port::where('country', $dmc_country)->get();
                
                return view('vehicles.add-vehicle', compact('dmcs', 'cities', 'zones', 'ports'));
            }
        }
        
        return view('vehicles.add-vehicle', compact('dmcs', 'cities'));
        // return view('vehicles.add-vehicle', compact('dmcs', 'cities'));
    }

    public function fetchDrivers(Request $request)
    {
        $user = auth()->user();
        $dmc_id = $request->country_name;
        $drivers = collect(); // Default empty

        if ($user->role_id == 11 || $user->role_id == 20) {
            // DMC sees their own drivers
            $drivers = Driver::where('status', 1)
                ->where('dmc_id', $user->userId)
                ->orderByDesc('updated_at')
                ->get();

        } elseif ($user->role_id == 35 || $user->role_id == 130 || $user->role_id == 132 || $user->role_id == 133 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138) {
            // Product Head sees own and APMs they created
            $createdByIds = User::where('created_by', $user->userId)->pluck('userId')->toArray();
            $createdByIds[] = $user->userId;

            $drivers = Driver::where('status', 1)
                ->where('dmc_id', $dmc_id)
                ->whereIn('created_by', $createdByIds)
                ->orderByDesc('updated_at')
                ->get();

        } elseif ($user->role_id == 76 || $user->role_id == 139) {
            // Product Manager sees APMs they created and self
            $apmIds = User::where('created_by', $user->userId)->pluck('userId')->toArray();
            $createdByIds = array_merge($apmIds, [$user->userId]);

            $drivers = Driver::where('status', 1)
                ->where('dmc_id', $dmc_id)
                ->whereIn('created_by', $createdByIds)
                ->orderByDesc('updated_at')
                ->get();

        } elseif ($user->role_id == 111 || $user->role_id == 140) {
            // APM sees only own drivers
            $drivers = Driver::where('status', 1)
                ->where('dmc_id', $dmc_id)
                ->where('created_by', $user->userId)
                ->orderByDesc('updated_at')
                ->get();

        } else {
            // Admins or others
            $drivers = Driver::where('status', 1)
                ->where('dmc_id', $dmc_id)
                ->orderByDesc('updated_at')
                ->get();
        }

        return response()->json($drivers);
    }


    public function fetchCities(Request $request)
    {
        $country = User::where('userId', $request->country_name)->first()->country;
        $cities = City::where('country', $country)->get();
        return response()->json($cities);
    }
    /*
    * Store a newly created role.
    * Date 07-10-2024
    */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'vehicle_name' => 'required|string|max:255',
            'vehicle_type' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            'vehicle_color' => 'required|string|max:255',
            'model_year' => 'required|integer|digits:4',
            'vehicle_plate_no' => 'required|string|max:255',
            'description' => 'nullable|string',
            'seating_capacity' => 'required|integer|min:1',
            'vehicle_status' => 'nullable|integer',
            'city_tour_seating_capacity' => 'required|integer|min:1',
            'city_tour_guides' => 'required|integer|min:1',
            // Add validation for sharable prices when sharable is checked
        ]);
        $lastVehicle = Vehicle::withTrashed()->orderBy('created_at', 'desc')->first();
        $vehicle_max_id = $lastVehicle->vehicle_id ?? 0;
        $vehicleId = CommonHelper::createId($vehicle_max_id);
        while (Vehicle::where('vehicle_id', $vehicleId)->exists()) {
            $vehicleId = CommonHelper::createId($vehicleId);
        }

        $masterImage = '';
        if ($request->hasFile('master_image')) {
            $pathData = CommonHelper::image_path('file_storage', $request->file('master_image'));
            if (!empty($pathData['master_value'])) {
                $masterImage = $pathData['master_value'];
            }
        }

        $auth_user = Auth::user();
        if ($auth_user->role_id == 11 || $auth_user->role_id == 20) {
            $dmc_id = $auth_user->userId;
                $status = 1;
        }elseif($auth_user->role_id == 4){
            $dmc_id = $request->dmc;
            $status = 1;
        }elseif($auth_user->role_id == 23){
            $dmc_id = $request->dmc;
            $status = 1;
        }elseif($auth_user->role_id == 1 || $auth_user->role_id == 2){
            $dmc_id = $request->dmc;
            $status = 1;
        } elseif(auth()->user()->role_id ==35 || auth()->user()->role_id == 130 || auth()->user()->role_id == 132 || auth()->user()->role_id == 133 || auth()->user()->role_id == 135 || auth()->user()->role_id == 136 || auth()->user()->role_id == 137 || auth()->user()->role_id == 138){
            $userdmc = User::where('userId', auth()->user()->created_by)->first();
            $dmc_id = $userdmc->userId;
            $status = 1;
        }
        elseif(auth()->user()->role_id == 76 || auth()->user()->role_id == 139){
            $user_product_head = User::where('userId', auth()->user()->created_by)->first();
            $user_product_head_dmc = User::where('userId', $user_product_head->created_by)->first();
            $dmc_id = $user_product_head_dmc->userId;
            $status = 1;
        }
        elseif(auth()->user()->role_id == 111 || auth()->user()->role_id == 140){
            $user_product_manager = User::where('userId', auth()->user()->created_by)->first();
            $user_product_head = User::where('userId', $user_product_manager->created_by)->first();
            $user_product_head_dmc = User::where('userId', $user_product_head->created_by)->first();
            $dmc_id = $user_product_head_dmc->userId;
            $status = 1;
        }
        elseif($auth_user->role_id == 11 || $auth_user->role_id == 20) {
            $dmc_id = $auth_user->userId;
            $status = 1;
        }
        else{
            $dmc_id = $request->dmc;
            $status = 1;
        }

        $normalizedPlateNumber = $this->normalizePlateNumber($request->vehicle_plate_no);

        // Check for existing vehicle with same normalized plate number for this DMC
        $existingVehicle = Vehicle::withTrashed()
            ->where('dmc_id', $dmc_id)
            ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(UPPER(vehicle_plate_no), ' ', ''), '-', ''), '/', ''), '&', '') = ?", [$normalizedPlateNumber])
            ->first();

        if ($existingVehicle) {
            // If vehicle exists but is trashed, restore it
            if ($existingVehicle->trashed()) {
                $existingVehicle->restore();
                $existingVehicle->update([
                    'vehicle_name' => $request->input('vehicle_name'),
                    'vehicle_type' => $request->input('vehicle_type'),
                    'vehicle_model' => $request->input('vehicle_model'),
                    'vehicle_color' => $request->input('vehicle_color'),
                    'model_year' => $request->input('model_year'),
                    'vehicle_plate_no' => $request->input('vehicle_plate_no'),
                    'description' => $request->input('description'),
                    'sharable' => $request->input('sharable') ?? 0,
                    'seating_capacity' => $request->input('seating_capacity'),
                    'image' => $masterImage,
                    'is_available' => $request->vehicle_status == 1 ? 1 : 0,
                    'driver_id' => $request->driver_id,
                    'created_by' => $auth_user->userId,
                    'city' => $request->city_name,
                    'base_price' => $request->input('base_price') ?? 0,
                    'cost_per_km_below_10' => $request->input('cost_per_km_below_10') ?? 0,
                    'cost_per_km_10_to_25' => $request->input('cost_per_km_10_to_25') ?? 0,
                    'cost_per_km_above_25' => $request->input('cost_per_km_above_25') ?? 0,
                    'cost_per_hour' => $request->input('cost_per_hour') ?? 0,
                    'cancel_cost' => $request->input('cancel_cost') ?? 0,
                    'night_base_price' => $request->input('night_base_price') ?? 0,
                    'night_cost_per_km_below_10' => $request->input('night_cost_per_km_below_10') ?? 0,
                    'night_cost_per_km_10_to_25' => $request->input('night_cost_per_km_10_to_25') ?? 0,
                    'night_cost_per_km_above_25' => $request->input('night_cost_per_km_above_25') ?? 0,
                    'night_cost_per_hour' => $request->input('night_cost_per_hour') ?? 0,
                    'night_cancel_cost' => $request->input('night_cancel_cost') ?? 0,
                    'sharable_base_price' => $request->input('sharable_base_price') ?? 0,
                    'sharable_cost_per_km_below_10' => $request->input('sharable_cost_per_km_below_10') ?? 0,
                    'sharable_cost_per_km_10_to_25' => $request->input('sharable_cost_per_km_10_to_25') ?? 0,
                    'sharable_cost_per_km_above_25' => $request->input('sharable_cost_per_km_above_25') ?? 0,
                    'sharable_cost_per_hour' => $request->input('sharable_cost_per_hour') ?? 0,
                    'sharable_cancel_cost' => $request->input('sharable_cancel_cost') ?? 0,
                    'sharable_night_base_price' => $request->input('sharable_night_base_price') ?? 0,
                    'sharable_night_cost_per_km_below_10' => $request->input('sharable_night_cost_per_km_below_10') ?? 0,
                    'sharable_night_cost_per_km_10_to_25' => $request->input('sharable_night_cost_per_km_10_to_25') ?? 0,
                    'sharable_night_cost_per_km_above_25' => $request->input('sharable_night_cost_per_km_above_25') ?? 0,
                    'sharable_night_cost_per_hour' => $request->input('sharable_night_cost_per_hour') ?? 0,
                    'sharable_night_cancel_cost' => $request->input('sharable_night_cancel_cost') ?? 0,
                    'attraction_private_transport_price' => $request->input('attraction_private_transport_price') ?? 0,
                    'attraction_shared_transport_price' => $request->input('attraction_shared_transport_price') ?? 0,
                    'restaurant_private_transport_price' => $request->input('restaurant_private_transport_price') ?? 0,
                    'restaurant_shared_transport_price' => $request->input('restaurant_shared_transport_price') ?? 0,
                    // 'status' => $status,
                ]);

                LogActivityService::log('restore_vehicle', 'App\Models\Vehicle', $existingVehicle->id, $existingVehicle);

                return redirect()->route('vehicle.edit', [
                    'vehicle' => Crypt::encrypt($existingVehicle->vehicle_id),
                    'zone_mapping' => true,
                    'mapping_type' => 'port_port'
                ])->with('success', 'Vehicle restored successfully! Now you can map zones.');
            } else {
                // If vehicle exists and is active, return error
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'A Vehicle already exists with this Plate Number for the selected DMC.');
            }
        }

        // Create a new vehicle if none exists
        $vehicle = new Vehicle();
        $vehicle->vehicle_name = $request->input('vehicle_name');
        $vehicle->vehicle_type = $request->input('vehicle_type');
        $vehicle->vehicle_model = $request->input('vehicle_model');
        $vehicle->vehicle_color = $request->input('vehicle_color');
        $vehicle->model_year = $request->input('model_year');
        $vehicle->vehicle_plate_no = $request->input('vehicle_plate_no');
        $vehicle->description = $request->input('description');
        $vehicle->sharable = $request->input('sharable') ?? 0;
        $vehicle->seating_capacity = $request->input('seating_capacity');
        $vehicle->city_tour_seating_capacity = $request->input('city_tour_seating_capacity');
        $vehicle->city_tour_guides = $request->input('city_tour_guides');
        $vehicle->vehicle_id = $vehicleId;
        $vehicle->image = $masterImage;
        $vehicle->is_available = $request->vehicle_status == 1 ? 1 : 0;
        $vehicle->driver_id = $request->driver_id;
        $vehicle->created_by = $auth_user->userId;
        $vehicle->dmc_id = $dmc_id;
        $vehicle->city = $request->city_name;
        // $vehicle->status = $status;

        $vehicle->base_price = $request->input('base_price')?? 0;
        $vehicle->cost_per_km_below_10 = $request->input('cost_per_km_below_10')?? 0;
        $vehicle->cost_per_km_10_to_25 = $request->input('cost_per_km_10_to_25')?? 0;
        $vehicle->cost_per_km_above_25 = $request->input('cost_per_km_above_25')?? 0;
        $vehicle->cost_per_hour = $request->input('cost_per_hour')?? 0;
        $vehicle->cancel_cost = $request->input('cancel_cost')?? 0;
            
        // Night charges for sharable
        $vehicle->night_base_price = $request->input('night_base_price');
        $vehicle->night_cost_per_km_below_10 = $request->input('night_cost_per_km_below_10')?? 0;
        $vehicle->night_cost_per_km_10_to_25 = $request->input('night_cost_per_km_10_to_25')?? 0;
        $vehicle->night_cost_per_km_above_25 = $request->input('night_cost_per_km_above_25')?? 0;
        $vehicle->night_cost_per_hour = $request->input('night_cost_per_hour')?? 0;
        $vehicle->night_cancel_cost = $request->input('night_cancel_cost')?? 0;

        // Add sharable prices if sharable is checked
        $vehicle->sharable_base_price = $request->input('sharable_base_price')?? 0;
        $vehicle->sharable_cost_per_km_below_10 = $request->input('sharable_cost_per_km_below_10')?? 0;
        $vehicle->sharable_cost_per_km_10_to_25 = $request->input('sharable_cost_per_km_10_to_25')?? 0;
        $vehicle->sharable_cost_per_km_above_25 = $request->input('sharable_cost_per_km_above_25')?? 0;
        $vehicle->sharable_cost_per_hour = $request->input('sharable_cost_per_hour')?? 0;
        $vehicle->sharable_cancel_cost = $request->input('sharable_cancel_cost')?? 0;
            
        // Night charges for sharable
        $vehicle->sharable_night_base_price = $request->input('sharable_night_base_price')?? 0;
        $vehicle->sharable_night_cost_per_km_below_10 = $request->input('sharable_night_cost_per_km_below_10')?? 0;
        $vehicle->sharable_night_cost_per_km_10_to_25 = $request->input('sharable_night_cost_per_km_10_to_25')?? 0;
        $vehicle->sharable_night_cost_per_km_above_25 = $request->input('sharable_night_cost_per_km_above_25')?? 0;
        $vehicle->sharable_night_cost_per_hour = $request->input('sharable_night_cost_per_hour')?? 0;
        $vehicle->sharable_night_cancel_cost = $request->input('sharable_night_cancel_cost')?? 0;

        $vehicle->attraction_private_transport_price = $request->input('attraction_private_transport_price')?? 0;
        $vehicle->attraction_shared_transport_price = $request->input('attraction_shared_transport_price')?? 0;
        $vehicle->restaurant_private_transport_price = $request->input('restaurant_private_transport_price')?? 0;
        $vehicle->restaurant_shared_transport_price = $request->input('restaurant_shared_transport_price')?? 0;

        $vehicle->city_tour_seating_capacity = $request->input('city_tour_seating_capacity')?? 0;

        if ($vehicle->save()) {
            LogActivityService::log('create_vehicle', 'App\Models\Vehicle', $vehicle->vehicle_id, $vehicle);
            
            // Redirect to edit page with zone mapping tab active
            return redirect()->route('vehicle.edit', [
                'vehicle' => Crypt::encrypt($vehicle->vehicle_id),
                'zone_mapping' => true,
                'mapping_type' => 'port_port'
            ])->with('success', 'Vehicle added successfully! Now you can map zones.');
        } else {
            LogActivityService::log('create_vehicle_failed', 'App\Models\Vehicle', $vehicle_max_id, 'An error occurred while saving the vehicle details.');
            return redirect()->back()
                ->with('error', 'An error occurred while saving the vehicle details.');
        }
    }
        //         ->with('error', 'An error occurred while saving the vehicle details.');
        // }

    //     if ($vehicle->save()) {
    //         LogActivityService::log('create_vehicle', 'App\Models\Vehicle', $vehicle->vehicle_id, $vehicle);
            
    //         // Redirect to edit page with zone mapping tab active
    //         return redirect()->route('vehicle.edit', [
    //             'vehicle' => $vehicle->vehicle_id,
    //             'zone_mapping' => true,
    //             'mapping_type' => 'port_hotel'
    //         ])->with('success', 'Vehicle added successfully! Now you can map zones.');
    //     } else {
    //         LogActivityService::log('create_vehicle_failed', 'App\Models\Vehicle', $vehicle_max_id, 'An error occurred while saving the vehicle details.');
    //         return redirect()->back()
    //             ->with('error', 'An error occurred while saving the vehicle details.');
    //     }
    // }

    /*
    * Show the form fors editing the specified role.
    * Date 07-10-2024
    */
    public function edit($id)
    {
        if (!hasPermission('edit vehicle')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $id = Crypt::decrypt($id);
        $vehicle = Vehicle::where('vehicle_id',$id)->first();
        $drivers = Driver::where('is_active', 1)->where('dmc_id', $vehicle->dmc_id)->get();
        $dmc_country = User::where('userId', $vehicle->dmc_id)->first()->country;
        $city = City::where('country', $dmc_country)->get();
        $authuser = auth()->user();
        if($authuser->role_id == 4){
            $dmcs = User::where('role_id', 11)->where('country', $authuser->country)->get();
        }elseif($authuser->role_id == 3){
            $dmcs = User::where('role_id', 11)->where('country', $authuser->country)->get();
        }else{
            $dmcs = User::where('role_id', 11)->get();
        }

        // Check if we're in the zone mapping tab
        if (request()->has('zone_mapping')) {
            // Get zones based on the vehicle's DMC
            $zones = Zone::where('dmc_id', $vehicle->dmc_id)->get();
            
            // Get ports for the DMC country
            $ports = Port::where('country', $dmc_country)->get();
            
            // Get existing mappings
            $mappings = VehicleZoneMapping::with(['fromZone', 'toZone'])
                ->where('vehicle_id', $vehicle->vehicle_id)
                ->get();
            
            return view('vehicles.edit-vehicle', compact('vehicle', 'drivers', 'dmcs', 'city', 'zones', 'ports', 'mappings'));
        }
        
        return view('vehicles.edit-vehicle', compact('vehicle', 'drivers', 'dmcs', 'city'));

        // return view('vehicles.edit-vehicle', compact('vehicle', 'drivers', 'dmcs', 'city'));
    }
    /*
    * Update the specified role.
    * Date 07-10-2024
    */
    public function update(Request $request, $id)
    {
        // Validate the incoming request data
        $id = Crypt::decrypt($id);
        $vehicle_id = $id;
        $vehicle = Vehicle::where('vehicle_id', $vehicle_id)->first();

        // For plate number, we only need basic validation since it's readonly in the form
        $vehiclePlateRules = ['required', 'string'];
        
        try {
            $validatedData = $request->validate([
                'vehicle_name' => 'required|string|max:255',
                'vehicle_type' => 'required|string|max:255',
                'vehicle_model' => 'required|string|max:255',
                'vehicle_color' => 'required|string|max:255',
                'model_year' => 'required|integer',
                'description' => 'nullable|string',
                'seating_capacity' => 'required|integer',
                'city_tour_seating_capacity' => 'required|integer',
                'city_tour_guides' => 'required|integer',
                'vehicle_status' => 'nullable|integer',
                'vehicle_plate_no' => $vehiclePlateRules,
                // Regular Day Pricing
                'base_price' => 'required|numeric',
                'cost_per_km_below_10' => 'required|numeric',
                'cost_per_km_10_to_25' => 'required|numeric',
                'cost_per_km_above_25' => 'required|numeric',
                'cost_per_hour' => 'required|numeric',
                'cancel_cost' => 'required|numeric',
                // Regular Night Pricing
                'night_base_price' => 'required|numeric',
                'night_cost_per_km_below_10' => 'required|numeric',
                'night_cost_per_km_10_to_25' => 'required|numeric',
                'night_cost_per_km_above_25' => 'required|numeric',
                'night_cost_per_hour' => 'required|numeric',
                'night_cancel_cost' => 'required|numeric',
            ],[
                'vehicle_plate_no.required' => 'Vehicle plate number is required.',
            ]);
        } catch (ValidationException $e) {
            // Get all errors
            $errors = $e->errors();
            // Example: log the errors or return them
            foreach ($errors as $field => $messages) {
                // $field is the field name like 'vehicle_name', 'base_price', etc.
                // $messages is an array of messages for that field
                foreach ($messages as $message) {
                    logger("Validation error in field '$field': $message");
                }
            }
        
            // Optionally return a custom response
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $errors
            ], 422);
        }
        
        $lastVehicle = Vehicle::withTrashed()->orderBy('created_at', 'desc')->first();
        $vehicle_max_id = $lastVehicle->vehicle_id ?? 0;

        // Process master image
        $master_image = $vehicle->image ?? '';
        if ($request->hasFile('master_image')) {
            $masterImagePath = CommonHelper::image_path('file_storage', $request->file('master_image'));
            if (!empty($masterImagePath['master_value'])) {
                $master_image = $masterImagePath['master_value'];
            }
        }

        $vehicle->vehicle_name = $request->input('vehicle_name');
        $vehicle->vehicle_type = $request->input('vehicle_type');
        $vehicle->vehicle_model = $request->input('vehicle_model');
        $vehicle->vehicle_color = $request->input('vehicle_color');
        $vehicle->vehicle_plate_no = $request->input('vehicle_plate_no');
        $vehicle->model_year = $request->input('model_year');
        $vehicle->sharable = $request->input('sharable') ?? 0;
        $vehicle->description = $request->input('description');
        $vehicle->seating_capacity = $request->input('seating_capacity');
        $vehicle->is_available = $request->input('vehicle_status') == 1 ? 1 : 0;
        $vehicle->image = $master_image;
        $vehicle->driver_id = $request->driver_id;
        $vehicle->city = $request->city_name;
        $vehicle->city_tour_seating_capacity = $request->input('city_tour_seating_capacity')?? 0;
        $vehicle->city_tour_guides = $request->input('city_tour_guides')?? 0;
        // Regular Day Pricing
        $vehicle->base_price = $request->input('base_price')?? 0;
        $vehicle->cost_per_km_below_10 = $request->input('cost_per_km_below_10')?? 0;
        $vehicle->cost_per_km_10_to_25 = $request->input('cost_per_km_10_to_25')?? 0;
        $vehicle->cost_per_km_above_25 = $request->input('cost_per_km_above_25')?? 0;
        $vehicle->cost_per_hour = $request->input('cost_per_hour')?? 0;
        $vehicle->cancel_cost = $request->input('cancel_cost')?? 0;

        // Regular Night Pricing
        $vehicle->night_base_price = $request->input('night_base_price')?? 0;
        $vehicle->night_cost_per_km_below_10 = $request->input('night_cost_per_km_below_10')?? 0;
        $vehicle->night_cost_per_km_10_to_25 = $request->input('night_cost_per_km_10_to_25')?? 0;
        $vehicle->night_cost_per_km_above_25 = $request->input('night_cost_per_km_above_25')?? 0;
        $vehicle->night_cost_per_hour = $request->input('night_cost_per_hour')?? 0;
        $vehicle->night_cancel_cost = $request->input('night_cancel_cost')?? 0;

            $vehicle->sharable_base_price = $request->input('sharable_base_price') ?? 0;
            $vehicle->sharable_cost_per_km_below_10 = $request->input('sharable_cost_per_km_below_10')?? 0;
            $vehicle->sharable_cost_per_km_10_to_25 = $request->input('sharable_cost_per_km_10_to_25')?? 0;
            $vehicle->sharable_cost_per_km_above_25 = $request->input('sharable_cost_per_km_above_25')?? 0;
            $vehicle->sharable_cost_per_hour = $request->input('sharable_cost_per_hour')?? 0;
            $vehicle->sharable_cancel_cost = $request->input('sharable_cancel_cost')?? 0;
            
            // Night charges for sharable
            $vehicle->sharable_night_base_price = $request->input('sharable_night_base_price')?? 0;
            $vehicle->sharable_night_cost_per_km_below_10 = $request->input('sharable_night_cost_per_km_below_10')?? 0;
            $vehicle->sharable_night_cost_per_km_10_to_25 = $request->input('sharable_night_cost_per_km_10_to_25')?? 0;
            $vehicle->sharable_night_cost_per_km_above_25 = $request->input('sharable_night_cost_per_km_above_25')?? 0;
            $vehicle->sharable_night_cost_per_hour = $request->input('sharable_night_cost_per_hour')?? 0;
            $vehicle->sharable_night_cancel_cost = $request->input('sharable_night_cancel_cost')?? 0;

            $vehicle->attraction_private_transport_price = $request->input('attraction_private_transport_price')?? 0;
            $vehicle->attraction_shared_transport_price = $request->input('attraction_shared_transport_price')?? 0;
            $vehicle->restaurant_private_transport_price = $request->input('restaurant_private_transport_price')?? 0;
            $vehicle->restaurant_shared_transport_price = $request->input('restaurant_shared_transport_price')?? 0;

            

        if ($vehicle->save()) {
            LogActivityService::log('edit_vehicle', 'App\Models\Vehicle', $vehicle->vehicle_id, $vehicle);
            return redirect()->route('vehicle.index')->with('success', 'Vehicle updated successfully!');
        } else {
            LogActivityService::log('edit_vehicle_failed', 'App\Models\vehicle', $vehicle_max_id,'An error occurred while saving the vehicle details.');
            return redirect()->back()
                ->with('error', 'An error occurred while saving the vehicle details.');
        }
    }

    public function mapZones(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,vehicle_id',
            'private_prices' => 'required|array',
            'shared_prices' => 'required|array',
            'mapping_type' => 'required|string',
        ]);

        $vehicleId = $request->vehicle_id;
        $privatePrices = $request->private_prices;
        $sharedPrices = $request->shared_prices;
        $mappingType = $request->mapping_type;
        
        // Set zone types based on mapping type
        $fromZoneType = '';
        $toZoneType = '';
        
        switch ($mappingType) {
            case 'port_port':
                $fromZoneType = 'Port';
                $toZoneType = 'Port';
                break;
            case 'port_attraction':
                $fromZoneType = 'Port';
                $toZoneType = 'Attraction';
                break;
            case 'port_restaurant':
                $fromZoneType = 'Port';
                $toZoneType = 'Restaurant';
                break;
            case 'port_hotel':
                $fromZoneType = 'Port';
                $toZoneType = 'Hotel';
                break;
            case 'hotel_attraction':
                $fromZoneType = 'Hotel';
                $toZoneType = 'Attraction';
                break;
            case 'hotel_restaurant':
                $fromZoneType = 'Hotel';
                $toZoneType = 'Restaurant';
                break;
            case 'attraction_restaurant':
                $fromZoneType = 'Attraction';
                $toZoneType = 'Restaurant';
                break;
            default:
                $fromZoneType = 'Unknown';
                $toZoneType = 'Unknown';
        }
        
        // Process each mapping
        foreach ($privatePrices as $fromZoneId => $toZones) {
            foreach ($toZones as $toZoneId => $privatePrice) {
                $sharedPrice = $sharedPrices[$fromZoneId][$toZoneId] ?? 0;

                // Find existing mapping (including soft deleted)
                $mapping = VehicleZoneMapping::withTrashed()
                    ->where('vehicle_id', $vehicleId)
                    ->where('from_zone_id', $fromZoneId)
                    ->where('to_zone_id', $toZoneId)
                    ->first();

                if ($mapping) {
                    // If soft deleted, restore it
                    if ($mapping->trashed()) {
                        $mapping->restore();
                    }
                    // Update prices and types
                    $mapping->update([
                        'private_price' => $privatePrice,
                        'shared_price' => $sharedPrice,
                        'from_zone_type' => $fromZoneType,
                        'to_zone_type' => $toZoneType,
                    ]);
                } else {
                    // Generate a new mapping_id
                    $lastMapping = VehicleZoneMapping::withTrashed()->orderBy('created_at', 'desc')->first();
                    $mapping_max_id = $lastMapping->mapping_id ?? 0;
                    $mappingId = \App\Helpers\CommonHelper::createId($mapping_max_id);
                    while (VehicleZoneMapping::where('mapping_id', $mappingId)->exists()) {
                        $mappingId = \App\Helpers\CommonHelper::createId($mappingId);
                    }
                    // Create new mapping
                    VehicleZoneMapping::create([
                        'mapping_id' => $mappingId,
                        'vehicle_id' => $vehicleId,
                        'from_zone_id' => $fromZoneId,
                        'to_zone_id' => $toZoneId,
                        'from_zone_type' => $fromZoneType,
                        'to_zone_type' => $toZoneType,
                        'private_price' => $privatePrice,
                        'shared_price' => $sharedPrice,
                    ]);
                }
            }
        }
        
        return redirect()->route('vehicle.edit', [
            'vehicle' => Crypt::encrypt($vehicleId),
            'zone_mapping' => true,
            'mapping_type' => $mappingType
        ])->with('success', 'Zone mappings saved successfully!');
    }

/**
 * Check if a mapping exists (including soft deleted records)
 */
    public function checkMappingExists(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|string',
            'from_zone_id' => 'required|string',
            'to_zone_id' => 'required|string',
            'from_zone_type' => 'required|string',
            'to_zone_type' => 'required|string',
        ]);
        
        // Check both active and trashed records
        $mapping = VehicleZoneMapping::where('vehicle_id', $validated['vehicle_id'])
            ->where('from_zone_id', $validated['from_zone_id'])
            ->where('to_zone_id', $validated['to_zone_id'])
            ->where('from_zone_type', $validated['from_zone_type'])
            ->where('to_zone_type', $validated['to_zone_type'])
            ->first();
        if ($mapping) {
            return response()->json([
                'exists' => true,
                'was_deleted' => $mapping->trashed(),
                'mapping_id' => $mapping->mapping_id
            ]);
        }
        
        return response()->json(['exists' => false]);
    }

/**
 * Add a mapping via AJAX
 */
    public function addMappingAjax(Request $request)
    {
        try {
            $vehicleId = $request->input('vehicle_id');
            $fromZoneId = $request->input('from_zone_id');
            $toZoneId = $request->input('to_zone_id');
            $fromZoneType = $request->input('from_zone_type');
            $toZoneType = $request->input('to_zone_type');

            // For port to port mapping, explicitly set the types
            if ($request->input('mapping_type') === 'port_port') {
                $fromZoneType = 'Port';
                $toZoneType = 'Port';
            }

            // Check if mapping already exists (including soft deleted)
            $existingMapping = VehicleZoneMapping::withTrashed()
                ->where('vehicle_id', $vehicleId)
                ->where('from_zone_id', $fromZoneId)
                ->where('to_zone_id', $toZoneId)
                ->where('from_zone_type', $fromZoneType)
                ->where('to_zone_type', $toZoneType)
                ->first();

            if ($existingMapping) {
                if ($existingMapping->trashed()) {
                    // Restore the soft-deleted mapping
                    $existingMapping->restore();
                    $existingMapping->update([
                        'from_zone_type' => $fromZoneType,
                        'to_zone_type' => $toZoneType,
                    ]);
                    
                    return response()->json([
                        'success' => true,
                        'mapping_id' => $existingMapping->mapping_id,
                        'message' => 'Mapping restored successfully'
                    ]);
                } else {
                    // Mapping already exists and is active
                    return response()->json([
                        'success' => false,
                        'message' => 'This mapping already exists'
                    ], 409);
                }
            }

            // Generate mapping_id
            $lastMapping = VehicleZoneMapping::withTrashed()->orderBy('created_at', 'desc')->first();
            $mapping_max_id = $lastMapping->mapping_id ?? 0;
            $mappingId = \App\Helpers\CommonHelper::createId($mapping_max_id);
            while (VehicleZoneMapping::where('mapping_id', $mappingId)->exists()) {
                $mappingId = \App\Helpers\CommonHelper::createId($mappingId);
            }

            // Create new mapping
            $mapping = VehicleZoneMapping::create([
                'mapping_id' => $mappingId,
                'vehicle_id' => $vehicleId,
                'from_zone_id' => $fromZoneId,
                'to_zone_id' => $toZoneId,
                'from_zone_type' => $fromZoneType,
                'to_zone_type' => $toZoneType,
                'private_price' => 0,
                'shared_price' => 0
            ]);

            return response()->json([
                'success' => true,
                'mapping_id' => $mapping->mapping_id,
                'message' => 'Mapping added successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding mapping: ' . $e->getMessage()
            ], 500);
        }
    }

/**
 * Delete a mapping via AJAX (soft delete)
 */
    public function deleteMappingAjax(Request $request)
    {
        try {
            $validated = $request->validate([
                'mapping_id' => 'required|string',
            ]);
            
            // Debug: Log the mapping_id being searched for
            \Log::info('Attempting to delete mapping_id: ' . $validated['mapping_id']);
            
            // Find the mapping by mapping_id (including soft deleted)
            $mapping = VehicleZoneMapping::withTrashed()
                ->where('mapping_id', $validated['mapping_id'])
                ->first();
            
            // Debug: Log what we found
            if ($mapping) {
                \Log::info('Found mapping: ', [
                    'mapping_id' => $mapping->mapping_id,
                    'vehicle_id' => $mapping->vehicle_id,
                    'from_zone_id' => $mapping->from_zone_id,
                    'to_zone_id' => $mapping->to_zone_id,
                    'deleted_at' => $mapping->deleted_at
                ]);
            } else {
                \Log::info('No mapping found for mapping_id: ' . $validated['mapping_id']);
                
                // Let's also check what mappings exist for debugging
                $allMappings = VehicleZoneMapping::withTrashed()->get(['mapping_id', 'vehicle_id', 'from_zone_id', 'to_zone_id']);
                \Log::info('All existing mappings: ', $allMappings->toArray());
            }
            
            if (!$mapping) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mapping not found for ID: ' . $validated['mapping_id']
                ], 404);
            }
            
            // Soft delete the mapping
            $mapping->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Mapping deleted successfully'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting mapping: ' . $e->getMessage()
            ], 500);
        }
    }

/**
 * Restore a soft-deleted mapping via AJAX
 */
    public function restoreMappingAjax(Request $request)
    {
        $validated = $request->validate([
            'mapping_id' => 'required|string',
        ]);
        
        $mapping = VehicleZoneMapping::withTrashed()->where('mapping_id', $validated['mapping_id'])->firstOrFail();
        $mapping->restore();
        
        return response()->json([
            'success' => true,
            'mapping_id' => $mapping->mapping_id,
            'private_price' => $mapping->private_price,
            'shared_price' => $mapping->shared_price
        ]);
    }

    /*
    * Soft Delete Roles.
    * Date 07-10-2024
    */
    public function destroy($id)
    {
        // if (!hasPermission('delete vehicle')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        $id = Crypt::decrypt($id);
        // Get vehicle and delete images from Azure
        $vehicle = Vehicle::where('vehicle_id', $id)->first();
        if($vehicle) {
            // Delete main image
            if($vehicle->image) {
                CommonHelper::deleteAzureImage($vehicle->image);
            }
            
            // Delete additional images
            if($vehicle->images) {
                $images = json_decode($vehicle->images, true);
                if(is_array($images)) {
                    foreach($images as $image) {
                        CommonHelper::deleteAzureImage($image);
                    }
                }
            }
        }
        
        Vehicle::where('vehicle_id', $id)->delete();
        return redirect()->route('vehicle.index')
        ->with('error', 'Vehicle deleted successfully');
    
    }

    /**
     * Normalizes vehicle plate numbers for consistent comparison
     * 
     * This function:
     * 1. Removes all non-alphanumeric characters (spaces, hyphens, slashes, etc.)
     * 2. Converts the result to uppercase for case-insensitive comparison
     * 
     * Examples:
     * - "WB 26" becomes "WB26"
     * - "wb-26" becomes "WB26"
     * - "WB/26" becomes "WB26"
     * - "wb&26" becomes "WB26"
     * 
     * @param string $plateNumber The original plate number
     * @return string The normalized plate number
     */
    private function normalizePlateNumber($plateNumber) {
        // Remove all non-alphanumeric characters (spaces, hyphens, slashes, etc.)
        // Convert to uppercase for case-insensitive comparison
        return preg_replace('/[^A-Za-z0-9]/', '', strtoupper($plateNumber));
    }
}
