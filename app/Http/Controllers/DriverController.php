<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\Role;
use App\Models\User;
use App\Models\Hotel;
use App\Models\Meal;
use Illuminate\Support\Facades\Auth;
use App\Models\Room;
use App\Models\Vehicle;
use App\Models\OperationalCountry;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;
use App\Helpers\CommonHelper;
use App\Models\Country;
use App\Models\City;
use App\Services\LogActivityService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use App\Mail\DmcMail;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class DriverController extends Controller
{
    /*
    * Display a listing of the Category.
    * Date 06-11-2024
    */
    public function index(Request $request)
    {
        if (!hasPermission('view driver')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $user = auth()->user();
        $drivers = [];
        if ($user->role_id == 4) {
            $dmc_ids = User::where('assistant_manager_id', $user->userId)->pluck('userId')->toArray();
            $drivers = Driver::whereIn('status', [4, 5, 1])
                // ->whereIn('dmc_id', $dmc_ids)
                ->orderBy('created_at', 'DESC')
                ->get();
        } elseif ($user->role_id == 3) {
            $drivers = Driver::orderBy('created_at', 'desc')->whereIn('status', [5, 1])->get();
        } elseif (in_array($user->role_id, [1, 2, 23])) {
            $drivers = Driver::orderBy('created_at', 'desc')->whereIn('status', [1, 3])->get();
        }
        elseif ($user->role_id == 10) {
            $dmc_ids = User::where('master_dmc_id', $user->userId)->get()->pluck('userId')->toArray();
            $drivers = Driver::orderBy('created_at', 'desc')->whereIn('dmc_id', $dmc_ids)->get();
        }
        elseif ($user->role_id == 11) {
            $drivers = Driver::orderBy('created_at', 'desc')->where('dmc_id', $user->userId)->get();
        }
        elseif ($user->role_id == 20) {
            $drivers = Driver::orderBy('created_at', 'desc')->where('dmc_id', $user->userId)->get();
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
            $drivers = Driver::orderBy('created_at', 'desc')->whereIn('dmc_id', $dmc_ids)->get();
        } 
        elseif($user->role_id == 35 || $user->role_id == 130 || $user->role_id == 132 || $user->role_id == 133 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138){
            $drivers = Driver::orderBy('created_at', 'desc')->where('dmc_id', $user->created_by)->get();
        }
        elseif($user->role_id == 76 || $user->role_id == 139){
            $product_head = User::where('userId', $user->created_by)->first();
            $drivers = Driver::orderBy('created_at', 'desc')->where('dmc_id', $product_head->created_by)->get();
        }
        elseif($user->role_id == 111 || $user->role_id == 140){
            $product_manager = User::where('userId', $user->created_by)->first();
            $product_head = User::where('userId', $product_manager->created_by)->first();
            $drivers = Driver::orderBy('created_at', 'desc')->where('dmc_id', $product_head->created_by)->get();
        }
        return view('drivers.index', compact('drivers', 'user'));
    }

    public function driverApproval(Request $request)
    {
        if (!hasPermission('view driverapproval')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $auth_user = auth()->user();
        $pendingDrivers = [];
        if($auth_user->role_id == 4){
        $pendingDrivers = Driver::with('user')
        ->orderBy('updated_at', 'desc')
        ->where('status', 2)
        ->get();
        }elseif($auth_user->role_id == 3){
        $pendingDrivers = Driver::with('user')
        ->orderBy('updated_at', 'desc')
        ->where('status', 4)
        ->get();
        }elseif($auth_user->role_id == 2 || $auth_user->role_id == 1 || $auth_user->role_id == 23){
        $pendingDrivers = Driver::with('user')
        ->orderBy('updated_at', 'desc')
        ->where('status', 5)
        ->get();
        }
        return view('drivers.driver-approval',compact('pendingDrivers'));
    }

    public function getDmcCities($dmcId)
    {
        try {
            // Get DMC's country from users table
            $dmc = User::where('userId', $dmcId)->first();
            
            if (!$dmc) {
                return response()->json([
                    'success' => false,
                    'message' => 'DMC not found',
                    'cities' => []
                ], 404);
            }

            // Get cities for DMC's country
            $cities = City::where('country', $dmc->country)
                        ->orderBy('name')
                        ->get(['id', 'name']);

            return response()->json([
                'success' => true,
                'cities' => $cities
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching cities',
                'cities' => []
            ], 500);
        }
    }

    public function editdriverApproval($id)
    {
        // if (!hasPermission('edit driver')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        $driverUsers = User::select('phone','name')->where('user_type', 3)->get();
        $vehicles = Vehicle::all();
        $countries = OperationalCountry::all();
        $driver = Driver::where('driver_id',$id)->first();
        $country = Country::where('is_active', 1)->get();
        $city = City::where('country', $driver->country)->get();

        return view('drivers.edit-driver-approval', compact('driver', 'countries', 'driverUsers','vehicles', 'city'));
    }

    public function updateDriverApproval(Request $request, $id)
    {
        // Find the driver
        $driver = Driver::where('driver_id', $id)->first();
        if (!$driver) {
            return redirect()->route('driver.approval')->with('error', 'Driver not found');
        }
        // Validate the incoming request data
        $validated = $request->validate([
            'address' => 'required|string',
            'salutation' => 'required|in:Mr,Mrs,Miss,Dear',
            'driver_gender' => 'required|in:Male,Female,Other',
            'country' => 'required|string|max:255',
            // 'state' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'name' => 'required|string',
            'email' => 'required|string|email|max:255',
            'phone' => 'required|string|min:8|max:15',
            'license_no' => [
                                'required',
                                Rule::unique('drivers', 'license_no')->ignore($driver->driver_id, 'driver_id'),
                            ],
            'license_exp_date' => 'required|date', // Added missing validation
            'driver_age' => 'required',
            'bank_account_holder_name' => 'nullable|string',
            'account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'bank_code' => 'nullable|string|max:50',
            'swift_code' => 'nullable|string|max:50',
            ],[
            'license_no.unique' => 'This license number is already taken by another driver.',
        ]);

        // Process master image
        $master_image = $driver->image ?? '';
        if ($request->hasFile('master_image')) {
            $masterImagePath = CommonHelper::image_path('file_storage', $request->file('master_image'));
            if (!empty($masterImagePath['master_value'])) {
                $master_image = $masterImagePath['master_value'];
            }
        }

        // Determine approval status based on role
        $auth_user = auth()->user();
        if($auth_user->role_id == 2 || $auth_user->role_id == 1 || $auth_user->role_id == 23){
            $status = 1;
        }else{
            $status = 5;
        }
        $status = $request->has('decline_status') ? 3 : $status;

        // If the driver is declined, set status to 3
        // if ($request->has('decline_status')) {
        //     $status = 3;
        // }

        // Update driver details
        $driver->name = $validated['name'];
        $driver->salutation = $validated['salutation'];
        $driver->driver_gender = $validated['driver_gender'];
        $driver->email = $validated['email'];
        $driver->phone = $validated['phone'];
        $driver->address = $validated['address'];
        $driver->country = $validated['country'];
        // $driver->state = $validated['state'];
        $driver->state = $request->input('state');
        $driver->city = $validated['city'];
        $driver->license_no = $validated['license_no'];
        $driver->license_exp_date = $validated['license_exp_date'];
        $driver->driver_age = $validated['driver_age'];
        $driver->operational_country_id = $request->input('operational_country_id');
        $driver->is_active = $request->input('driver_status') == 1 ? 1 : 0;
        // $driver->bank_account_holder_name = $validated['bank_account_holder_name'];
        // $driver->account_number = $validated['account_number'];
        // $driver->bank_name = $validated['bank_name'];
        // $driver->bank_code = $validated['bank_code'];
        // $driver->swift_code = $validated['swift_code'];
        $driver->bank_account_holder_name = $request->input('bank_account_holder_name');
        $driver->account_number = $request->input('account_number');
        $driver->bank_name = $request->input('bank_name');
        $driver->bank_code = $request->input('bank_code');
        $driver->swift_code = $request->input('swift_code');
        $driver->image = $master_image;
        $driver->status = $status;

        // Save changes
        $isSaved = $driver->save();

        // Logging activity and redirecting with appropriate message
        if ($request->has('decline_status')) {
            return redirect()->route('driver.approval', ['driver' => $driver->driver_id])
                ->with('error', 'Driver Declined successfully');
        } elseif ($isSaved) {
            // LogActivityService::log('edit_driver', 'App\Models\Driver', $driver->driver_id, $driver);
            return redirect()->route('driver.approval', ['driver' => $driver->driver_id])
                ->with('success', 'Driver Approved successfully');
        } else {
            // LogActivityService::log('edit_driver_failed', 'App\Models\Driver', $driver->driver_id, $driver);
            return redirect()->route('driver.approval')->with('error', 'Failed to approve driver');
        }
    }

    /*
    * Show the form for creating a new category.
    * Date 06-11-2024
    */
    public function create()
    {
        if (!hasPermission('create driver')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $driverUsers = User::select('phone','name')->where('user_type', 3)->get();
        $vehicles = Vehicle::all();
        $countries = OperationalCountry::all();
        $country = Country::where('is_active', 1)->get();
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

        if (in_array($authuser->role_id, [11, 35, 76, 111, 130, 132, 133, 135, 136, 137, 138, 139, 140])) {
            // For product/multi-product roles, the user's own country may not be set;
            // use the parent DMC's country when available.
            $countryOwnerUserId = $authuser->userId;
            if (in_array($authuser->role_id, [35, 130, 132, 133, 135, 136, 137, 138, 76, 111, 139, 140]) && !empty($authuser->created_by)) {
                $countryOwnerUserId = $authuser->created_by;
            }

            $userCountry = optional(User::where('userId', $countryOwnerUserId)->first())->country ?? '';
            $cities = $userCountry ? City::where('country', $userCountry)->get() : [];
        } else {
            $userCountry = '';
            $cities = [];
        }
        return view('drivers.add-drivers', compact('countries', 'vehicles', 'driverUsers', 'dmcs', 'country', 'userCountry', 'cities'));
    }

    public function getUserDetails($id)
    {
        $user = User::where('phone', $id)->get(); // Replace 'User' with your model name
        
        if ($user) {
            return response()->json([
                'success' => true,
                'user' => $user,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ]);
        }
    }


    /*
    * Store a newly created Driver details.
    * Date 07-10-2024
    */
    public function store(Request $request)
    {
        
        // Validate the incoming request data
        $validated = $request->validate([
            'salutation' => 'required|in:Mr,Mrs,Miss,Dear',
            'driver_gender' => 'required|in:Male,Female,Other',
            'address' => 'required|string',
            'country' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'name' => 'required|string',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|min:8|max:15',
            'license_no' => 'required',
            'license_exp_date' => 'required',
            'driver_age' => 'required',
            'wp_number' => 'required|numeric',
            // Bank details
            'bank_account_holder_name' => 'nullable|string',
            'account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'bank_code' => 'nullable|string|max:50',
            'swift_code' => 'nullable|string|max:50',
            'master_image' => 'required|nullable|mimes:jpg,jpeg,png,bmp,gif,svg,webp,avif',
        ]);

        // Generate unique driver ID
        // $lastDriver = Driver::withTrashed()->orderBy('created_at', 'desc')->first();
        // $driver_max_id = $lastDriver->driver_id ?? 0;
        // $driverId = CommonHelper::createId($driver_max_id);
        // while (Driver::where('driver_id', $driverId)->exists()) {
        //     $driverId = CommonHelper::createId($driverId);
        // }

        $master_image = '';
        if ($request->hasFile('master_image')) {
            $masterImagePath = CommonHelper::image_path('file_storage', $request->file('master_image'));
            if (!empty($masterImagePath['master_value'])) {
                $master_image = $masterImagePath['master_value'];
            }
        }

        $auth_user = Auth::user();
            if ($auth_user->role_id == 11) {
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
            }elseif(auth()->user()->role_id ==35 || auth()->user()->role_id == 130 || auth()->user()->role_id == 132 || auth()->user()->role_id == 133 || auth()->user()->role_id == 135 || auth()->user()->role_id == 136 || auth()->user()->role_id == 137 || auth()->user()->role_id == 138){
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
            else{
                $dmc_id = $request->dmc;
                $status = 1;
            }

            // 🔍 Check for existing hotel at same lat/lng for this DMC
            $existingDriver = Driver::where([
                ['license_no', $request->license_no],
                ['dmc_id', $dmc_id]
            ])->first();

            if ($existingDriver) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'A Driver already exists with this License Number for the selected DMC.');
            }

            $deletedDriver = Driver::withTrashed()->where([
                ['license_no', $request->license_no],
                ['dmc_id', $dmc_id]
            ])->first();

            //dd($dmc_id);
        
            if ($deletedDriver && $deletedDriver->trashed()) {
                $deletedDriver->restore();
                
                // Prepare update data
                $updateData = [
                    'salutation' => $request->salutation,
                    'driver_gender' => $request->driver_gender,
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'address' => $request->address,
                    'country' => $request->country,
                    'state' => $request->state,
                    'city' => $request->city,
                    'license_no' => $request->license_no,
                    'license_exp_date' => $request->license_exp_date,
                    'driver_age' => $request->driver_age,
                    'wp_number' => $request->wp_number,
                    'operational_city' => $request->operational_city,
                    'bank_account_holder_name' => $request->bank_account_holder_name,
                    'account_number' => $request->account_number,
                    'bank_name' => $request->bank_name,
                    'bank_code' => $request->bank_code,
                    'swift_code' => $request->swift_code,
                    'userId' => $request->user_id,
                    'image' => $master_image,
                    'is_active' => $request->driver_status == 1 ? 1 : 0,
                    'status' => $status,
                    'created_by' => $auth_user->userId,
                    'dmc_id' => $dmc_id ?? 0,
                ];
                
                // Hash password if provided
                if ($request->app_password) {
                    $updateData['app_password'] = Hash::make($request->app_password);
                }
                
                $deletedDriver->update($updateData);
        
                // LogActivityService::log('restore_driver', 'App\Models\Driver', $deletedDriver->id, $deletedDriver);
        
                // if (in_array($auth_user->role_id, [11, 4, 3, 35, 76, 111])) {
                //     return view('drivers.thankyou');
                // }
        
                return redirect()->route('driver.index')->with('success', 'Driver restored successfully!');
            }
        
            // Create new driver
            // Store plain password for email before hashing
            $plainPassword = $request->app_password;
            
            $driver = new Driver();
                // $driver->driver_id = $driverId;
            $driver->salutation = $request->salutation;
            $driver->driver_gender = $request->driver_gender;
            $driver->name = $request->name;
            $driver->phone = $request->phone;
            $driver->email = $request->email;
            $driver->address = $request->address;
            $driver->country = $request->country;
            $driver->state = $request->state;
            $driver->city = $request->city;
            $driver->license_no = $request->license_no;
            $driver->license_exp_date = $request->license_exp_date;
            $driver->driver_age = $request->driver_age;
            $driver->wp_number = $request->wp_number;
            $driver->operational_city = $request->operational_city;
            $driver->bank_account_holder_name = $request->bank_account_holder_name;
            $driver->account_number = $request->account_number;
            $driver->bank_name = $request->bank_name;
            $driver->bank_code = $request->bank_code;
            $driver->swift_code = $request->swift_code;
            $driver->userId = $request->user_id;
            $driver->image = $master_image;
            $driver->is_active = $request->driver_status == 1 ? 1 : 0;
            $driver->status = $status;
            $driver->created_by = $auth_user->userId;
            $driver->dmc_id = $dmc_id ?? 0;
            $driver->app_password = $plainPassword ? Hash::make($plainPassword) : null;
            
            if ($driver->save()) {
                $driver->refresh();
                $driverId = $driver->driver_id;
                // LogActivityService::log('create_driver', 'App\Models\Driver', $driver->driver_id, $driver);
        
                // Send credentials email if email is provided
                if ($driver->email && $plainPassword) {
                    try {
                        $this->sendDriverCredentialsEmail($driver, $plainPassword);
                    } catch (\Exception $e) {
                        Log::warning('Failed to send driver credentials email: ' . $e->getMessage());
                        // Don't fail the request if email sending fails
                    }
                }
        
                // if (in_array($auth_user->role_id, [11, 4, 3, 35, 76, 111])) {
                //     return view('drivers.thankyou');
                // }
        
                return redirect()->route('driver.index')->with('success', 'Driver added successfully!');
            } else {
                // LogActivityService::log('create_driver_failed', 'App\Models\Driver', $driverId, 'An error occurred while saving the driver details.');
                return redirect()->back()->with('error', 'An error occurred while saving the driver details.');
            }
    }

    /*
    * Show the form fors editing the specified role.
    * Date 07-10-2024
    */
    public function edit($id)
    {
        if (!hasPermission('edit driver')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $driverId = Crypt::decrypt($id);
        $driverUsers = User::select('phone','name')->where('user_type', 3)->get();
        $vehicles = Vehicle::all();
        $driver = Driver::where('driver_id',$driverId)->first();
        $countries = OperationalCountry::all();
        $country = Country::where('is_active', 1)->get();
        $city = City::where('country', $driver->country)->get();
        $dmc = User::where('userId', $driver->dmc_id)->first();

        $authuser = auth()->user();
        if($authuser->role_id == 4){
            $dmcs = User::where('role_id', 11)->where('country', $authuser->country)->get();
        }elseif($authuser->role_id == 3){
            $dmcs = User::where('role_id', 11)->where('country', $authuser->country)->get();
        }else{
            $dmcs = User::where('role_id', 11)->get();
        }

        return view('drivers.edit-drivers', compact('driver', 'countries', 'driverUsers', 'vehicles', 'country', 'city','dmcs', 'dmc'));
    }
    /*
    * Update the specified role.
    * Date 07-10-2024
    */
    public function update(Request $request, $id)
    {
        $driverId = Crypt::decrypt($id);
        $driver = Driver::where('driver_id', $driverId)->first();

        $user = auth()->user();
        $userRoleId = $user->role_id;
        $selectedDmcId = $request->input('dmc_id'); // Correct field (numeric)

        $licenseRule = ['required'];

        if (in_array($userRoleId, [1, 2]) && $selectedDmcId) {
            $licenseRule[] = Rule::unique('drivers', 'license_no')
                ->where(function ($query) use ($selectedDmcId) {
                    $query->where('dmc_id', $selectedDmcId);
                })
                ->ignore($driver->driver_id, 'driver_id');
        } else {
            // Global uniqueness for DMC roles
            $licenseRule[] = Rule::unique('drivers', 'license_no')
                ->ignore($driver->driver_id, 'driver_id');
        }

        // Validate the incoming request data
        $validated = $request->validate([
            'address' => 'required|string',
            'salutation' => 'required|in:Mr,Mrs,Miss,Dear',
            'driver_gender' => 'required|in:Male,Female,Other',
            'country' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'name' => 'required|string',
            'email' => 'required|string',
            'phone' => 'required|string|min:8|max:15',
            // 'license_no' => [
            //                     'required',
            //                     Rule::unique('drivers', 'license_no')->ignore($driver->driver_id, 'driver_id'),
            //                 ],
            'license_no' => $licenseRule,
            'driver_age' => 'required',
            'wp_number' => 'required|numeric',
            'bank_account_holder_name' => 'nullable|string',
            'account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'bank_code' => 'nullable|string|max:50',
            'swift_code' => 'nullable|string|max:50',
        ],[
        'license_no.unique' => 'This license number is already taken by another driver.',
        ]);

        $lastDriver = Driver::withTrashed()->orderBy('created_at', 'desc')->first();
        $driver_max_id = $lastDriver->driver_id ?? 0;
        // Process master image
        $master_image = $driver->image ?? '';
        if ($request->hasFile('master_image')) {
            $masterImagePath = CommonHelper::image_path('file_storage', $request->file('master_image'));
            if (!empty($masterImagePath['master_value'])) {
                $master_image = $masterImagePath['master_value'];
            }
        }

        $plainPassword = $request->app_password;

        $driver->salutation = $validated['salutation'];
        $driver->driver_gender = $validated['driver_gender'];
        $driver->name = $request->input('name');
        $driver->email = $request->input('email');
        $driver->phone = $request->input('phone');
        $driver->address = $request->input('address');
        $driver->country = $request->input('country');
        $driver->state = $request->input('state');
        $driver->city = $request->input('city');
        
        $driver->license_no = $request->license_no;
        $driver->license_exp_date = $request->license_exp_date;
        $driver->driver_age = $request->driver_age;
        $driver->wp_number = $request->wp_number;
        $driver->is_active = $request->input('driver_status') == 1 ? 1 : 0;
        $driver->bank_account_holder_name = $request->input('bank_account_holder_name');
        $driver->account_number = $request->input('account_number');
        $driver->bank_name = $request->input('bank_name');
        $driver->bank_code = $request->input('bank_code');
        $driver->swift_code = $request->input('swift_code');
        $driver->image = $master_image;
        $driver->app_password = $plainPassword ? Hash::make($plainPassword) : null;

        if ($driver->save()) {
            // LogActivityService::log('edit_driver', 'App\Models\Driver', $driver->driver_id, $driver);

            // Send credentials email if email is provided
            if ($driver->email && $plainPassword) {
                try {
                    $this->sendDriverCredentialsEmail($driver, $plainPassword);
                } catch (\Exception $e) {
                    Log::warning('Failed to send driver credentials email: ' . $e->getMessage());
                    // Don't fail the request if email sending fails
                }
            }
            return redirect()->route('driver.index')->with('success', 'Driver updated successfully!');
        } else {
            // LogActivityService::log('edit_driver_failed', 'App\Models\driver', $driver_max_id,'An error occurred while saving the driver details.');
            return redirect()->back()
                ->with('error', 'An error occurred while saving the driver details.');
        }
    }

    /*
    * Soft Delete Roles.
    * Date 07-10-2024
    */
    public function destroy($id)
    {
        // if (!hasPermission('delete driver')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        $driverId = Crypt::decrypt($id);
        // Get driver and delete images from Azure
        $driver = Driver::where('driver_id', $driverId)->first();
        if($driver) {
            // Delete driver image
            if($driver->image) {
                CommonHelper::deleteAzureImage($driver->image);
            }
            
            // Delete license image
            if($driver->license_image) {
                CommonHelper::deleteAzureImage($driver->license_image);
            }
        }
        
        Driver::where('driver_id', $driverId)->delete();
        return redirect()->route('driver.index')
        ->with('success', 'Driver deleted successfully');
    
    }

    public function approveOrDecline($driverId, Request $request)
    {
        $driver = Driver::where('driver_id', $driverId)->first();
        if ($request->action == 'approve') {
            // Handle approval logic
            $driver->driver_approval = 1; // Example logic for approval
            $driver->save();
        } elseif ($request->action == 'decline') {
            // Handle decline logic
            $driver->driver_approval = 0; // Example logic for decline
            $driver->save();
        }
        return response()->json(['success' => true]);
    }

    public function driverCalendar($driver_id)
    {
        $driver = Driver::where('driver_id', Crypt::decrypt($driver_id))->first();
        $close_days = $driver->close_days;
        $close_dates = $driver->close_dates;
        return view('drivers.calendar', compact('driver_id', 'driver', 'close_days', 'close_dates'));
    }

    public function driverCloseDate(Request $request) {
        $stringDates = $request->driver_holiday_dates;
        $datesArray = array_map('trim', explode(',', $stringDates));
        $datesJson = json_encode($datesArray, JSON_PRETTY_PRINT);
        $driver = Driver::where('driver_id', $request->driver_id)->first();
        $driver->close_days = $request->driver_closed_days;
        $driver->close_dates = $request->driver_holiday_dates;
        $driver->save();
        return redirect()->back()
        ->with('success', 'Close dates and holidays saved successfully');
    }

    /**
     * Send driver credentials email
     * This method sends a welcome email with login credentials to the newly created driver
     * 
     * @param Driver $driver
     * @param string $plainPassword
     * @return bool
     */
    private function sendDriverCredentialsEmail(Driver $driver, string $plainPassword)
    {
        try {
            // Get company settings for branding
            $logoSetting = Setting::where('name', 'logo')->where('status', 1)->first();
            $nameSetting = Setting::where('name', 'name')->where('status', 1)->first();
            $supportEmailSetting = Setting::where('name', 'support_email')->first();
            $supportPhoneSetting = Setting::where('name', 'support_phone')->first();
            
            $companyLogo = $logoSetting ? $logoSetting->value : null;
            $companyName = $nameSetting ? $nameSetting->value : config('app.name');
            $supportEmail = $supportEmailSetting ? $supportEmailSetting->value : null;
            $supportPhone = $supportPhoneSetting ? $supportPhoneSetting->value : null;
            
            // Prepare email data (use plain password for email, not the hashed one)
            $emailData = [
                'driver_name' => $driver->name,
                'driver_id' => $driver->driver_id,
                'email' => $driver->email,
                'app_password' => $plainPassword,
                'phone' => $driver->phone,
                'license_no' => $driver->license_no,
                'operational_city' => $driver->operational_city,
                'company_name' => $companyName,
                'company_logo' => $companyLogo,
                'support_email' => $supportEmail,
                'support_phone' => $supportPhone,
            ];
            
            // Render the email template
            $html = view('mails.driver_credentials', $emailData)->render();
            
            // Extract styles and email container
            preg_match('/<style>(.*?)<\/style>/s', $html, $styleMatches);
            $styles = !empty($styleMatches[0]) ? $styleMatches[0] : '';
            
            // Extract the email-container div
            preg_match('/<div class="email-container">(.*?)<\/div>\s*<\/body>/s', $html, $matches);
            
            if (!empty($matches[0])) {
                $extractedHtml = $matches[0];
                
                // Build complete email HTML
                $subject = 'Welcome! Your Driver App Credentials';
                $emailHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>' . $subject . '</title>' . $styles . '</head><body>' . $extractedHtml . '</body></html>';
                
                // Send the email
                Mail::to($driver->email)->send(new DmcMail($emailHtml, $subject));
                
                Log::info("Driver credentials email sent successfully to: {$driver->email}", [
                    'driver_id' => $driver->driver_id,
                    'driver_name' => $driver->name,
                ]);
                
                return true;
            } else {
                Log::error("Email container div not found in driver credentials template");
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Failed to send driver credentials email', [
                'error' => $e->getMessage(),
                'driver_id' => $driver->driver_id ?? null,
                'email' => $driver->email ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
