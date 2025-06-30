<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Models\Room; 
use App\Models\Rate; 
use App\Models\Setting;
use App\Models\HotelCategory;
use App\Models\Category;
use App\Models\RoomType;
use App\Models\Bed;
use App\Models\BedMaster;
use App\Models\User;
use App\Models\HotelPolicy;
use App\Models\Country;
use App\Models\Meal;
use Carbon\Carbon;
use App\Helpers\CommonHelper;
use App\Models\City;
use Illuminate\Support\Facades\Storage;
use Auth; 
use App\Models\Restaurant;
use App\Services\LogActivityService;
use Illuminate\Support\Facades\Log;

class HotelController extends Controller
{
    /*
    * Display a listing of the Hotels.
    * Date 04-11-2024
    */
    public function index(Request $request)
    {
        if (!hasPermission('view hotel')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $user = auth()->user();
        $hotels = [];
        if ($user->role_id == 4) {
            // $dmc_ids = User::where('assistant_manager_id', $user->userId)->pluck('userId')->toArray();
            $hotels = Hotel::whereIn('status', [4, 5, 1])
                // ->whereIn('dmc_id', $dmc_ids)
                ->orderBy('updated_at', 'DESC')
                ->get();
        } elseif ($user->role_id == 3) {
            $hotels = Hotel::whereIn('status', [5, 1])->orderBy('updated_at', 'DESC')->get();
        } elseif (in_array($user->role_id, [1, 2, 23])) {
            $hotels = Hotel::whereIn('status', [1, 3])->orderBy('updated_at', 'DESC')->get();
        }
        elseif($user->role_id == 10){
            $dmc_ids = User::where('master_dmc_id', $user->userId)->get()->pluck('userId')->toArray();
            $hotels = Hotel::orderBy('updated_at', 'desc')->whereIn('dmc_id', $dmc_ids)->get();
        }
        elseif ($user->role_id == 11) {
            $hotels = Hotel::where('dmc_id', $user->userId)->orderBy('updated_at', 'DESC')->get();
        }
        elseif(in_array($user->role_id, [25, 59, 83])){
            if($user->role_id == 25){
                $master_dmc_id = $user->created_by;
            }
            elseif($user->role_id == 59){
                $product_head = User::where('userId', $user->created_by)->first();
                $master_dmc_id = $product_head->created_by;
            }
            elseif($user->role_id == 83){
                $product_manager = User::where('userId', $user->created_by)->first();
                $product_head = User::where('userId', $product_manager->created_by)->first();
                $master_dmc_id = $product_head->created_by;
            }
            
            $dmc_ids = User::where('master_dmc_id', $master_dmc_id)->get()->pluck('userId')->toArray();
            $hotels = Hotel::whereIn('dmc_id', $dmc_ids)->orderBy('updated_at', 'DESC')->get();
        }
        elseif($user->role_id == 77){
            $assistant_product_manager_ids = User::where('created_by', $user->userId)->get()->pluck('userId')->toArray();
            if($assistant_product_manager_ids){
                $hotels = Hotel::whereIn('userId', $assistant_product_manager_ids)->orWhere('userId', $user->userId)->orderBy('hotel_unique_id', 'DESC')->get();
            }else{
                $hotels = Hotel::where('userId', $user->userId)->orderBy('updated_at', 'DESC')->get();
            }
        }
        elseif($user->role_id == 35){
            $hotels = Hotel::where('dmc_id', $user->created_by)->orderBy('updated_at', 'DESC')->get();
        }
        elseif($user->role_id == 84){
            $hotels = Hotel::where('userId', $user->userId)->orderBy('updated_at', 'DESC')->get();
        }
        return view('hotel.hotels', compact('hotels', 'user'));
    }

    public function hotelApproval(Request $request)
    {
        if (!hasPermission('view hotelapproval')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $auth_user = auth()->user();
        if($auth_user->role_id == 2 || $auth_user->role_id == 1 || $auth_user->role_id == 23){
            $pendinghotels = Hotel::with('user')
            ->where('status', 5)
            ->get();
            }
        
        return view('hotel.hotel-approval',compact('pendinghotels'));
    }

    public function editHotelApproval($id) {
        $facilities = Facility::all();
        $hotel_categories = HotelCategory::all();
        $hotel = Hotel::where('hotel_unique_id', $id)->first();
        
        $same_hotel = null;
        if (!$hotel) {
            // Handle the case where the hotel is not found
            return redirect()->back()->with('error', 'Hotel not found.');
        }
        $same_hotel = Hotel::where('latitude', $hotel->latitude)
            ->where('longitude', $hotel->longitude)
            ->where('hotel_unique_id', '!=', $id) // Ignore the current hotel
            ->where('status',1)
            ->first(); // Only check existence instead of fetching the first record
        $entry_data = json_decode($hotel->port_of_entry, true) ?? [];
        $exit_data = json_decode($hotel->port_of_exit, true) ?? [];
        $others = json_decode($hotel->others, true) ?? [];
        $enable_port_of_entry = !empty($entry_data);
        $enable_port_of_exit = !empty($exit_data);
        $others_data = !empty($others);
        $hotel_images = $hotel->images ? json_decode($hotel->images, true) : [];
        $country_code = User::countryCodes();
        $city = City::where('country', $hotel->country)->get();
        $country = Country::get();
        $selectedDays = json_decode($hotel->weekend_days, true) ?? [];
        return view('hotel.edit-hotel-approval', compact('hotel_categories', 'same_hotel', 'hotel','country','facilities','entry_data','exit_data','enable_port_of_entry','enable_port_of_exit','others','others_data','hotel_images','country_code','selectedDays', 'city'));
    }

    public function updateHotelApproval(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            // 'category_type' => 'required|integer',
            'phone' => 'required|string',
            'email' => 'required|email',
            'address' => 'required|string',
            'city' => 'required|string',
            // 'state' => 'required|string',
            'country' => 'required|string',
            'pincode' => 'required',
            'latitude' => 'required',
            'time_range' => 'required',
            'longitude' => 'required',
            'master_image' => 'nullable|image',
            'images.*' => 'nullable|image',
        ]);

        $hotel = Hotel::where('hotel_unique_id', $id)->first();
        $storage_file = $hotel->main_image;
        if ($request->hasFile('master_image')) {
            $image = $request->file('master_image');
            $storage_file = CommonHelper::image_path('file_storage', $image);
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
        $existingImages = $hotel->images ? json_decode($hotel->images, true) : [];
        $img_path = array_merge($existingImages, $imagePaths);
        $auth_user = auth()->user();

        if($auth_user->role_id == 2 || $auth_user->role_id == 1 || $auth_user->role_id == 23){
            $status = 1;
        }else{
            $status = 5;
        }
        ## status 1 approve, 5 pending, 3 decline.
        $status = $request->has('decline_status') ? 3 : $status;
        $hotel->update([
            'name' => $request->input('name'),
            'hotel_unique_id' => $hotel->hotel_unique_id,
            'address' => $request->input('address'),
            'infant_age_limit' => $request->input('infant_age_limit'),
            'child_age_limit' => $request->input('child_age_limit'),
            'weekend_days' => json_encode($request->weekend_days),
            '12_hour_book' => $request->input('time_range'),
            'day_usage_type' => $request->input('day_usage_type'),
            'twelve_hours_charge' => $request->input('percentPrice'),
            'city' => $request->input('city'),
            'cat_id' => $request->input('category_type'),
            'state' => $request->input('state'),
            'country' => $request->input('country'),
            'zipcode' => $request->input('pincode'),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'main_image' => $storage_file['master_value'] ?? $storage_file,
            'check_in_time' => $request->input('check_in_time'),
            'check_out_time' => $request->input('check_out_time'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'description' => $request->input('description'),
            'extra_bed_age_limit' => $request->input('extra_bed_age_limit'),
            'is_active' => $request->input('hotel_status') == 1 ? 1 : 0,
            'status' =>$request->decline_status ?? $status,
            'images' => json_encode($img_path),
            'duration' => $request->input('duration'),
            'chain_hotel_name' => $request->input('chain_name'),
        ]);
        if($request->decline_status){
        return redirect()->route('hotels.approval', ['hotel' => $hotel->hotel_unique_id])->with('error', 'Hotel Declined successfully');
        }
        elseif ($status) {
        return redirect()->route('hotels.approval', ['hotel' => $hotel->hotel_unique_id])->with('success', 'Hotel Approve successfully');
        } else {
            return redirect()->back()->withInput()->with('error', 'Something went wrong, please try again');
        }
    }

    /*
    * Show the form for creating a new hotel.
    * Date 04-11-2024
    */
    public function create()
    {
        $categories = HotelCategory::get();
        $facilities = Facility::all();
        $country_code = User::countryCodes();
        $country = Country::where('is_active', 1)->get();
        $authuser = auth()->user();
        if($authuser->role_id == 25){
            $master_dmc = User::where('created_by', $authuser->userId)->first();
            $dmcs = User::where('master_dmc_id', $master_dmc->userId)->get();
        }elseif($authuser->role_id == 59){
            $master_dmc_ph = User::where('created_by', $authuser->userId)->first();
            $master_dmc = User::where('created_by', $master_dmc_ph->userId)->first();
            $dmcs = User::where('master_dmc_id', $master_dmc->userId)->get();
        }elseif($authuser->role_id == 83){
            $master_dmc_pm = User::where('created_by', $authuser->userId)->first();
            $master_dmc_ph = User::where('created_by', $master_dmc_pm->userId)->first();
            $master_dmc = User::where('created_by', $master_dmc_ph->userId)->first();
            $dmcs = User::where('master_dmc_id', $master_dmc->userId)->get();
        }
        elseif(in_array($authuser->role_id, [10,25, 63, 119])){
            if($authuser->role_id == 10){
                $dmc_ids = User::where('master_dmc_id', $authuser->userId)->get()->pluck('userId')->toArray();
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
        return view('hotel.add-hotel', compact('categories', 'dmcs','facilities','country_code', 'country'));
    }

    /*
    * Store new hotel.
    * Date 05-11-2024
    */
    public function store(Request $request)
    {
        try {
            $dialCode = $request->country_code;
            $country_code = User::dialCodeToCountryCode($dialCode);
            $countryCode = strtoupper(substr($country_code, 0, 2)); 
            $hotelName = strtoupper(substr($request->input('name'), 0, 3)); 
            $randomDigits = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT); 
            $display_id = $countryCode . $hotelName . $randomDigits;
            $uniqueId = uniqid('', true);
            $unique_id = substr($uniqueId, -16);
            // 🔒 Validation with Try-Catch
            $validatedData = $request->validate([
                'name' => 'required|string',
                'phone' => 'required|string',
                'email' => 'required|email',
                'address' => 'required|string',
                // 'state' => 'required|string',
                'country' => 'required|string',
                'pincode' => 'required',
                'latitude' => 'required',
                'longitude' => 'required',
                'infant_age_limit' => 'required',
                'child_age_limit' => 'required',
                'extra_bed_age_limit' => 'required',
                'description' => 'required',
                'master_image' => 'nullable|image',
                'images.*' => 'nullable|image',
            ]);
    
            // ✅ Master Image
            $mainImagePath = ['master_value' => null];
            if ($request->hasFile('master_image')) {
                $image = $request->file('master_image');
                $mainImagePath = CommonHelper::image_path('file_storage', $image);
            }
    
            // ✅ Gallery Images
            $imagePaths = [];
            if ($request->hasFile('all_images')) {
                foreach ($request->file('all_images') as $image) {
                    $pathData = CommonHelper::image_path('file_storage', $image);
                    if (!empty($pathData['master_value'])) {
                        $imagePaths[] = $pathData['master_value'];
                    }
                }
            }
            $auth_user = Auth::user();
            if ($auth_user->role_id == 1 || $auth_user->role_id == 2 || $auth_user->role_id == 23) {
                $dmc_id = $request->dmc;
                $status = 1;
            } elseif ($auth_user->role_id == 11) {
                $dmc_id = $auth_user->userId;
                $status = 1;
            } elseif(auth()->user()->role_id ==35){
                $userdmc = User::where('userId', auth()->user()->created_by)->first();
                $dmc_id = $userdmc->userId;
                $status = 1;
            }
            elseif(auth()->user()->role_id == 77){
                $user_product_head = User::where('userId', auth()->user()->created_by)->first();
                $user_product_head_dmc = User::where('userId', $user_product_head->created_by)->first();
                $status = 1;
                $dmc_id = $user_product_head_dmc->userId;
            }
            elseif(auth()->user()->role_id == 84){
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
            $existingHotel = Hotel::where([
                ['latitude', $request->latitude],
                ['longitude', $request->longitude],
                ['dmc_id', $dmc_id]
            ])->first();
    
            if ($existingHotel) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'A hotel already exists at this location for the selected DMC.');
            }

            // if(auth()->user()->role_id ==35){
            //     $userdmc = User::where('userId', auth()->user()->created_by)->first();
            //     $dmc_id = $userdmc->userId;
            // }
            // elseif(auth()->user()->role_id == 77){
            //     $user_product_head = User::where('userId', auth()->user()->created_by)->first();
            //     $user_product_head_dmc = User::where('userId', $user_product_head->created_by)->first();

            //     $dmc_id = $user_product_head_dmc->userId;
            // }
            // elseif(auth()->user()->role_id == 84){
            //     $user_product_manager = User::where('userId', auth()->user()->created_by)->first();

            //     $user_product_head = User::where('userId', $user_product_manager->created_by)->first();

            //     $user_product_head_dmc = User::where('userId', $user_product_head->created_by)->first();

            //     $dmc_id = $user_product_head_dmc->userId;
            // }
            // elseif($auth_user->role_id == 11) {
            //     $dmc_id = $auth_user->userId;
            // }
            // else{
            //     $dmc_id = $request->dmc;
            // }
    
            // ✅ Create Hotel
            $hotel = Hotel::create([
                'user_type' => $auth_user->user_type,
                'userId' => $auth_user->userId,
                'name' => $request->input('name'),
                'hotel_unique_id' => $unique_id,
                'address' => $request->input('address'),
                'infant_age_limit' => $request->input('infant_age_limit'),
                'child_age_limit' => $request->input('child_age_limit'),
                'check_in_time' => $request->input('check_in_time') ?? '',
                'check_out_time' => $request->input('check_out_time') ?? '',
                '12_hour_book' => $request->input('start_time') ?? '',
                'city' => $request->input('location'),
                'state' => $request->input('state'),
                'country' => $request->input('country'),
                'zipcode' => $request->input('pincode'),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'main_image' => $mainImagePath['master_value'],
                'phone' => $request->input('phone'),
                'email' => $request->input('email'),
                'description' => $request->input('description'),
                'status' => $status,
                'is_active' => $request->input('hotel_status'),
                'weekend_days' => json_encode($request->input('weekend_days')),
                'images' => json_encode($imagePaths),
                'display_id' => $display_id,
                'dmc_id' => $dmc_id ?? 0,
                'extra_bed_age_limit' => $request->extra_bed_age_limit,
                'country_code' => $request->country_code,
                'twelve_hours_charge' => $request->input('charge') ?? 0,
                'day_usage_type' => $request->input('day_usage_type'),
                'duration' => $request->input('duration') ?? 0,
                'accomodation_type' => $request->input('hotel_category'),
                'ownership_type' => $request->input('hotel_ownership'),
                'hotel_segment' => $request->input('hotel_segment'),
                'hotel_star_rating' => $request->input('hotel_star_rating'),
                'chain_hotel_name' => $request->input('chain_name'),
                'is_complete' => 0,
            ]);
    
            // if (in_array($auth_user->role_id, [11, 35, 77 , 84])) {
            //     return view('hotel.thankyou');
            // }
    
            return redirect()->route('hotels.contact', ['hotel' => $hotel->hotel_unique_id])
                ->with('success', 'Hotel created successfully');
    
        } catch (\Exception $e) {
            Log::error('Hotel Creation Failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An unexpected error occurred. Please try again.');
        }
    }
    

    /**
     * Helper function to process port data.
     */
    private function processPortData( $types, $names, $latitudes, $longitudes, $distances)
    {
        $data = [];
        // Get the maximum array length to ensure we process all entries
        $maxLength = max(
            count($types),
            count($names),
            count($latitudes),
            count($longitudes),
            count($distances)
        );
        
        for ($i = 0; $i < $maxLength; $i++) {
            // Only add entry if it has latitude, longitude and distance
            if (
                isset($latitudes[$i]) && !empty($latitudes[$i]) && 
                isset($longitudes[$i]) && !empty($longitudes[$i]) && 
                isset($distances[$i]) && !empty($distances[$i])
            ) {
                // Make sure type is not null
                $type = isset($types[$i]) ? $types[$i] : null;
                // If type is empty but we have coordinates, use a default value
                if (empty($type) && $i % 2 == 0) {
                    // Check previous and next elements to see if this is part of a pattern
                    $prevType = $i > 0 ? ($types[$i-1] ?? null) : null;
                    $nextType = isset($types[$i+1]) ? $types[$i+1] : null;
                    
                    if (!empty($prevType)) {
                        $type = $prevType; // Use previous type if available
                    } elseif (!empty($nextType)) {
                        $type = $nextType; // Use next type if available
                    } else {
                        $type = 'Unknown'; // Default type if no context available
                    }
                }
                
                $data[] = [
                    'type' => $type, 
                    'port_name' => $names[$i] ?? '', 
                    'latitude' => $latitudes[$i],
                    'longitude' => $longitudes[$i],
                    'distance' => $distances[$i],
                ];
            }
        }
        return $data;
    }

    // Ajax call for port name
    public function getPorts(Request $request)
    {
        $hotelId = $request->input('hotel_id');
        $query = $request->input('q');

        $hotel = Hotel::where('hotel_unique_id', $hotelId)->first();

        if (!$hotel) {
            return response()->json([], 404);
        }

        $hotelsInCountry = Hotel::where('country', $hotel->country)->get();

        $allPorts = [];

        foreach ($hotelsInCountry as $h) {
            $entryPorts = json_decode($h->port_of_entry, true) ?? [];
            $exitPorts = json_decode($h->port_of_exit, true) ?? [];

            $combined = array_merge($entryPorts, $exitPorts);

            foreach ($combined as $port) {
                if (!empty($port['port_name'])) {
                    $allPorts[] = [
                        'port_name' => $port['port_name'],
                        'latitude' => $port['latitude'] ?? null,
                        'longitude' => $port['longitude'] ?? null,
                    ];
                }
            }
        }

        $ports = collect($allPorts)
            ->filter(function ($port) use ($query) {
                return stripos($port['port_name'], $query) !== false;
            })
            ->unique('port_name')
            ->values()
            ->take(10);
        return response()->json($ports);
    }

    /*
    * Editing Hotel details.
    * Date 15-11-2024
    */
    public function edit($id)
    {
        if (!hasPermission('edit hotel')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $facilities = Facility::all();
        $hotel_categories = [];
        $hotel_categories = HotelCategory::all();
        $hotel = Hotel::where('hotel_unique_id',$id)->first();
        $country = Country::where('is_active', 1)->get();
        $city = City::where('country', $hotel->country)->get();
        $entry_data = json_decode($hotel->port_of_entry, true) ?? [];
        $exit_data = json_decode($hotel->port_of_exit, true) ?? [];
        $others = json_decode($hotel->others, true) ?? [];
        $enable_port_of_entry = !empty($entry_data);
        $enable_port_of_exit = !empty($exit_data);
        $others_data = !empty($others);
        $hotel_images = $hotel->images ? json_decode($hotel->images, true) : [];
        $country_code = User::countryCodes();
        $selectedDays = json_decode($hotel->weekend_days, true) ?? [];

        $authuser = auth()->user();
        if($authuser->role_id == 4){
            $dmcs = User::where('role_id', 11)->where('country', $authuser->country)->get();
        }elseif($authuser->role_id == 3){
            $dmcs = User::where('role_id', 11)->where('country', $authuser->country)->get();
        }else{
            $dmcs = User::where('role_id', 11)->get();
        }

        return view('hotel.edit-hotel', compact('hotel_categories', 'hotel','facilities','country', 'city', 'entry_data','exit_data','enable_port_of_entry','enable_port_of_exit','others','others_data','hotel_images','country_code','selectedDays','dmcs'));
    }

    /*
    * Update Hotel details.
    * Date 15-11-2024
    */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'phone' => 'required|string',
                'email' => 'required|email',
                'address' => 'required|string',
                'city' => 'required|string',
                // 'state' => 'required|string',
                'country' => 'required|string',
                'pincode' => 'required',
                'latitude' => 'required',
                'time_range' => 'required',
                'longitude' => 'required',
                // 'is_active' => 'required|integer',
                'master_image' => 'nullable|image',
                'images.*' => 'nullable|image',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Catch Validation Errors
            dd($e->errors());
        }
        // $validatedData = $request->validate([
        //     'name' => 'required|string',
        //     'category_type' => 'required|integer',
        //     'phone' => 'required|string',
        //     'email' => 'required|email',
        //     'address' => 'required|string',
        //     'city' => 'required|string',
        //     'state' => 'required|string',
        //     'country' => 'required|string',
        //     'pincode' => 'required|integer',
        //     'latitude' => 'required',
        //     'time_range' => 'required',
        //     'longitude' => 'required',
        //     'status' => 'required|integer',
        //     'master_image' => 'nullable|image',
        //     'images.*' => 'nullable|image',
        // ]);

        $hotel = Hotel::where('hotel_unique_id', $id)->first();
        
        // Handle master image
        $storage_file = $hotel->main_image;
        
        // Check if master image is removed
        if ($request->filled('removed_master_image')) {
            $removedMasterImage = $request->input('removed_master_image');
            // Delete the physical file if it exists
            if ($storage_file && file_exists(public_path($storage_file))) {
                unlink(public_path($storage_file));
            }
            $storage_file = null; // Set to null when removed
        }
        
        // Handle new master image upload
        if ($request->hasFile('master_image')) {
            $image = $request->file('master_image');
            $storage_file = CommonHelper::image_path('file_storage', $image);
        }

        // Handle additional images
        $imagePaths = []; 
        if ($request->hasFile('all_images')) {
            foreach ($request->file('all_images') as $image) {
                $pathData = CommonHelper::image_path('file_storage', $image);
                if (!empty($pathData['master_value'])) {
                    $imagePaths[] = $pathData['master_value']; 
                }
            }
        }
        
        // Get existing images and filter out removed ones
        $existingImages = $hotel->images ? json_decode($hotel->images, true) : [];
        
        // Handle removed images
        if ($request->filled('removed_images')) {
            $removedImages = explode(',', $request->input('removed_images'));
            $removedImages = array_filter($removedImages); // Remove empty values
            
            // Delete physical files and filter out from existing images
            foreach ($removedImages as $removedImage) {
                // Remove from existing images array
                $existingImages = array_filter($existingImages, function($img) use ($removedImage) {
                    return $img !== $removedImage;
                });
                
                // Delete the physical file if it exists
                if (file_exists(public_path($removedImage))) {
                    unlink(public_path($removedImage));
                }
            }
            
            // Re-index the array to avoid gaps
            $existingImages = array_values($existingImages);
        }
        
        // Merge existing images (after removal) with new images
        $img_path = array_merge($existingImages, $imagePaths);
        $hotel->update([
            'name' => $request->input('name'),
            'hotel_unique_id' => $hotel->hotel_unique_id,
            'address' => $request->input('address'),
            'infant_age_limit' => $request->input('infant_age_limit'),
            'child_age_limit' => $request->input('child_age_limit'),
            'weekend_days' => json_encode($request->weekend_days),
            '12_hour_book' => $request->input('time_range'),
            'day_usage_type' => $request->input('day_usage_type'),
            'twelve_hours_charge' => $request->input('percentPrice'),
            'city' => $request->input('city'),
            'state' => $request->input('state'),
            'country' => $request->input('country'),
            'zipcode' => $request->input('pincode'),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'main_image' => $storage_file['master_value'] ?? $storage_file,
            'check_in_time' => $request->input('check_in_time'),
            'check_out_time' => $request->input('check_out_time'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'description' => $request->input('description'),
            'extra_bed_age_limit' => $request->input('extra_bed_age_limit'),
            'is_active' => $request->input('hotel_status') == 1 ? 1 : 0,
            'images' => json_encode($img_path),
            'duration' => $request->input('duration'),
            'chain_hotel_name' => $request->input('chain_name'),

            'accomodation_type' => $request->input('hotel_category'),
            'ownership_type' => $request->input('hotel_ownership'),
            'hotel_segment' => $request->input('hotel_segment'),
            'hotel_star_rating' => $request->input('hotel_star_rating'),
        ]);

        if ($hotel) {
            return redirect()->route('hotels.contact', ['hotel' => $hotel->hotel_unique_id])->with('success', 'Hotel updated successfully');
        } else {
            return redirect()->back()->withInput()->with('error', 'Something went wrong, please try again');
        }
    }

    /*
    * Soft Delete Hotels.
    * Date 05-11-2024
    */
    public function destroy($id)
    {
        if (!hasPermission('delete hotel')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $delete =Hotel::where('hotel_unique_id', $id)->delete();
        $delete =Room::where('hotel_id', $id)->delete();
        return redirect()->route('hotels.index')
        ->with('success','Hotel deleted successfully');
    }
    
    /*
    * Contact Details of Hotel.
    * Date 15-11-2024
    */
    public function hotelcontacts($hotelId){
        $hotel = Hotel::where('hotel_unique_id', $hotelId)->first();
        return view('hotel.contactdetails', compact('hotel'));
    }

    /*
    * Edit Contact Details .
    * Date 15-11-2024
    */
    public function editcontacts($hotelId){
        $hotel = Hotel::where('hotel_unique_id', $hotelId)->first();
        return view('hotel.contactdetails', compact('hotel'));
    }

    /*
    * Update Contact Details of Hotel.
    * Date 15-11-2024
    */
    public function updatecontacts(Request $request)
    {
        $validatedData = $request->validate([
            'hotel_owner_company_name' => 'required|string|max:255',
            'management_comp_name' => 'nullable|string|max:255',
            'hotel_reservation_cont_no' => 'nullable|string|max:13|min:6',
            'hotel_reservation_email' => 'nullable|email|max:255',
            'revenue_director_cont_no' => 'nullable|string|max:13|min:6',
            'revenue_director_email' => 'nullable|email|max:255',
            'sales_director_cont_no' => 'nullable|string|max:13|min:6',
            'sales_director_email' => 'nullable|email|max:255',
            'finance_director_cont_no' => 'nullable|string|max:13|min:6',
            'finance_director_email' => 'nullable|email|max:255',
            'beverage_director_cont_no' => 'nullable|string|max:13|min:6',
            'beverage_director_email' => 'nullable|email|max:255',
            'marketing_manager_cont_no' => 'nullable|string|max:13|min:6',
            'marketing_manager_email' => 'nullable|email|max:255',
            'account_manager_cont_no' => 'nullable|string|max:13|min:6',
            'account_manager_email' => 'nullable|email|max:255',
            'general_manager_cont_no' => 'nullable|string|max:13|min:6',
            'general_manager_email' => 'nullable|email|max:255',
            'whatsapp' => 'nullable|string|max:13|min:6',
        ]);

        try {
            $hotel = Hotel::where('hotel_unique_id', $request->id)->first();
    
            if (!$hotel) {
                return redirect()->back()->withInput()->with('error', 'Hotel not found.');
            }
    
            // Update hotel with validated data
            $hotel->update([
                'hotel_owner_company_name' => $request->input('hotel_owner_company_name'),
                'management_comp_name' => $request->input('management_comp_name'),
                'hotel_reservation_cont_no' => $request->input('hotel_reservation_cont_no'),
                'hotel_reservation_email' => $request->input('hotel_reservation_email'),
                'revenue_director_cont_no' => $request->input('revenue_director_cont_no'),
                'revenue_director_email' => $request->input('revenue_director_email'),
                'sales_director_cont_no' => $request->input('sales_director_cont_no'),
                'sales_director_email' => $request->input('sales_director_email'),
                'finance_director_cont_no' => $request->input('finance_director_cont_no'),
                'finance_director_email' => $request->input('finance_director_email'),
                'food_beverage_director_cont_no' => $request->input('beverage_director_cont_no'), // Make sure this key exists in the request
                'food_beverage_director_email' => $request->input('beverage_director_email'),
                'marketing_manager_cont_no' => $request->input('marketing_manager_cont_no'),
                'marketing_manager_email' => $request->input('marketing_manager_email'),
                'account_manager_cont_no' => $request->input('account_manager_cont_no'),
                'account_manager_email' => $request->input('account_manager_email'),
                'general_manager_cont_no' => $request->input('general_manager_cont_no'),
                'general_manager_email' => $request->input('general_manager_email'),
                'whatsapp' => $request->input('whatsapp'),
            ]);            
    
            return redirect()->route('ports', ['id' => $hotel->hotel_unique_id])
            ->with('success', 'Hotel contacts updated successfully.');
        } catch (\Exception $e) {
            // Log the exception for debugging purposes
            Log::error('Error updating hotel contacts: ' . $e->getMessage());
    
            return redirect()->back()->withInput()->with('error', 'Something went wrong. Please try again.');
        }
    }
    
    /*
    * Room Details of Hotel.
    * Date 15-11-2024
    */
    // public function hotelrooms()
    // {
    //     if (!hasPermission('view room')) {
    //         abort(403, 'You do not have permission to access this page.');
    //     }
    //     $auth_user = Auth::user();

    //     $roomQuery = Room::with('hotel');
    //     if ($auth_user->user_type == 1) {
    //         $rooms = $roomQuery->get(); 
    //     } else {
    //         $rooms = $roomQuery->whereHas('hotel', function ($query) use ($auth_user) {
    //             $query->where('dmc_id', $auth_user->userId)->groupBy('name'); 
    //         })->get();
    //     }

    //     $currentRooms = Room::where('room_type', 'Standard')->first();
    //     $restaurants = Restaurant::all();
    //     $mealTypes = Meal::all();
    //     return view('hotel.rooms', compact('rooms', 'restaurants', 'currentRooms', 'mealTypes'));
    // }

    public function hotelrooms()
    {
        // if (!hasPermission('view room')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
    
        $auth_user = Auth::user();
    
        $roomQuery = Room::with('hotel')
            ->selectRaw('hotel_id, MAX(room_id) as room_id, MAX(room_type) as room_type, MAX(no_of_room) as no_of_room, MAX(status) as status')
            ->groupBy('hotel_id');
    
        if ($auth_user->user_type == 1) {
            $rooms = $roomQuery->get();
        } else {
            $rooms = $roomQuery->whereHas('hotel', function ($query) use ($auth_user) {
                $query->where('dmc_id', $auth_user->userId);
            })->get();
        }
    
        $restaurants = Restaurant::all();
        $mealTypes = Meal::all();
    
        return view('hotel.rooms', compact('rooms', 'restaurants', 'mealTypes'));
    }


    /*
    * Create Room of Hotel.
    * Date 06-01-2024
    */
    public function createHotelRooms($hotel_unique_id){
        // if (!hasPermission('create room')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        
        $commission_type = 0;
        $commission_price = 0;
        $auth_user = Auth::user();
        
        // Get the specific hotel by hotel_unique_id
        $hotel = Hotel::where('hotel_unique_id', $hotel_unique_id)->firstOrFail();
        
        if($auth_user->user_type == 1){
            $hotels = Hotel::get();
        }elseif($auth_user->user_type == 2){
            $hotels = Hotel::where('dmc_id', $auth_user->userId)->get();
            if($auth_user->is_master_dmc == 1){
                $commission_type = $auth_user->markup_type;
                $commission_price = $auth_user->markup_price;
            }else{
                $master_id = User::where('master_dmc_id',$auth_user->master_dmc_id)->first();
                $commission_type = $master_id->markup_type;
                $commission_price = $master_id->markup_price;
            }
        }else {
            $hotels = Hotel::where('dmc_id', $auth_user->userId)->get();
        }
        
        $roomtypes = RoomType::where('status', 1)->get();
        $rooms = Room::with('beds')->where('hotel_id', $hotel_unique_id)->get();
        $currentRooms = Room::where('room_type', 'Standard')->first();
        $restaurants = Restaurant::all();
        $mealTypes = Meal::whereIn('type', ['Breakfast', 'Lunch', 'Dinner'])
                        ->get()
                        ->groupBy('type');
                        
        return view('hotel.create-room', compact(
            'hotel',
            'hotels',
            'commission_price',
            'commission_type',
            'rooms',
            'roomtypes',
            'restaurants',
            'currentRooms',
            'mealTypes',
            'auth_user'
        ));
    }
    /*
    * Create Room of Hotel.
    * Date 15-01-2025
    */
    public function getHotelRooms(Request $request)
    {
        $hotelId = $request->input('hotel_id');
        $rooms = Room::where('hotel_id', $hotelId)->get();
        return response()->json([
            'success' => true,
            'rooms' => $rooms,
        ]);
    }

    public function hotelrates($hotelId){
        $hotel = Hotel::where('hotel_unique_id', $hotelId)->first();
        $rates = Rate::where('event_type', '!=', 'Season')->where('hotel_id', $hotelId)->get();
        return view('hotel.rates', compact('hotel','rates'));
    }

    public function hotelseason($hotelId){
        $hotel = Hotel::where('hotel_unique_id', $hotelId)->first();
        $room = Room::where('hotel_id', $hotelId)->get()->first();
        $rates = Rate::where('event_type', "Season")->where('hotel_id', $hotelId)->get();
        return view('hotel.season', compact('hotel','room','rates'));
    }

    /*
    * Store new Rooms.
    * Date 15-11-2024
    */
    public function storeroom(Request $request)
    {
        $request->validate([
            'room_type' => 'nullable',
            'total_no_of_room' => 'nullable|integer',
            'singleWeekdayPrice' => 'nullable|numeric',
            'singleWeekendPrice' => 'nullable|numeric',
            'doubleWeekdayPrice' => 'nullable|numeric',
            'doubleWeekendPrice' => 'nullable|numeric',
            'children_price' => 'nullable|numeric|min:0',
            'master_image' => 'required|nullable'
        ]);

        $lastRoom = Room::withTrashed()->orderBy('id', 'desc')->first();
        $room_max_id = $lastRoom->room_id ?? 0;
        $roomId = CommonHelper::createId($room_max_id);
        while (Room::where('room_id', $roomId)->exists()) {
            $roomId = CommonHelper::createId($roomId);
        }
        // Handle image paths
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

        //master image
        $master_image = '';
        if($request->hasFile('master_image')){
            $masterImagePath = CommonHelper::image_path('file_storage', $request->file('master_image'));
            if (!empty($pathData['master_value'])) {
                $master_image = $pathData['master_value'];
            }
        }
        
        // Check if this is the first room for this hotel
        $isFirstRoom = Room::where('hotel_id', $request->hotel_id)->count() === 0;
        
        // Check if this is a Standard room
        $isStandardRoom = ($request->room_type === 'Standard' || $request->base_room_type === 'Standard');
        
        // Create and save the room
        $room = new Room();
        $room->hotel_id = $request->hotel_id;
        $room->room_type = $request->room_type ? $request->room_type : $request->base_room_type;
        $room->no_of_room = $request->total_no_of_room;

        $room->weekday_price = $request->baseSingleWeekdayPrice ? $request->baseSingleWeekdayPrice : $request->singleWeekdayPrice;
        $room->weekend_price = $request->baseSingleWeekendPrice ? $request->baseSingleWeekendPrice : $request->singleWeekendPrice;

        $room->double_weekday_price = $request->baseDoubleWeekdayPrice ? $request->baseDoubleWeekdayPrice : $request->doubleWeekdayPrice;
        $room->double_weekend_price = $request->baseDoubleWeekendPrice ? $request->baseDoubleWeekendPrice : $request->doubleWeekendPrice;

        $room->varient_price = $request->varient_price ?? 0;

        $room->dimension = $request->dimension;
        $room->children_price = $request->children_price;

        $room->status = $request->room_status == 1 ? 1 : 0;
        $room->room_id = $roomId;
        $room->images = $imagePathsJson;
        $room->master_image = $master_image;
        $room->breakfast_restaurant = $request->breakfast_restaurant;
        
        // Set base_room to true if this is first room or Standard room
        $room->base_room = ($isFirstRoom || $isStandardRoom) ? true : false;
        
        // If this is a base room, make sure no other rooms for this hotel are base rooms
        if ($room->base_room) {
            Room::where('hotel_id', $request->hotel_id)->update(['base_room' => false]);
        }
        $room->breakfast = $request->breakfast_included;
        $room->lunch = $request->lunch_included;
        $room->dinner = $request->dinner_included;
        $room->dinner_type=$request->dinner_type;
        $room->lunch_type=$request->lunch_type;
        $room->breakfast_type=$request->breakfast_type;
        $room->breakfast_price=$request->breakfast_price;
        $room->lunch_price=$request->lunch_price;
        $room->dinner_price=$request->dinner_price;
        $room->breakfast_included=$request->supplementary_breakfast;
        $room->save();
        
        if($request->no_of_rooms){
            $lastBed = Bed::withTrashed()->orderBy('bed_id', 'desc')->first();
            $bed_max_id = $lastBed->bed_id ?? 0;
            $bedId = CommonHelper::createId($bed_max_id);
            while (Bed::where('bed_id', $bedId)->exists()) {
                $bedId = CommonHelper::createId($bedId);
            }
        }

        $lastRoomId = Room::latest()->value('room_id');
        // Return response based on room save result
        if ($room->save()) {
            $auth_user = Auth::user();
            $roomQuery = Room::with('hotel');
            if ($auth_user->user_type == 1) {
                $rooms = $roomQuery->get(); // Fetch all rooms for user_type 1
            } else {
                $rooms = $roomQuery->whereHas('hotel', function ($query) use ($auth_user) {
                    $query->where('dmc_id', $auth_user->userId); // Filter by dmcId for other user types
                })->get();
            }
            $currentRooms = Room::where('room_type', 'Standard')->first();
            return redirect()->route('hotels.createroom', ['id' => $request->hotel_id])->with('success', 'Room details saved successfully!');
        } else {
            return redirect()->back()->with('error', 'An error occurred while saving the room details.');
        }
    }

    /**
     * store rates
     */
    public function storerates(Request $request){
        $request->validate([
            'event' => 'required|string',
            'hotel_id' => 'required',
            'event_type' => 'required|string',
            'price' => 'nullable|numeric',
            'surcharge' => 'nullable|numeric',
            'date_range' => 'required|string',
            'rate_status' => 'nullable|integer',
        ]);
        list($firstDate, $lastDate) = explode(' - ', $request->date_range);
        $firstDate = Carbon::createFromFormat('m/d/Y', $firstDate);
        $lastDate = Carbon::createFromFormat('m/d/Y', $lastDate);
       
        // Generate rate ID
        $lastRate = Rate::withTrashed()->orderBy('created_at', 'desc')->first();
        $rate_max_id = $lastRate->rate_id ?? 0;
        $rateId = CommonHelper::createId($rate_max_id);
        while (Rate::where('rate_id', $rateId)->exists()) {
            $rateId = CommonHelper::createId($rateId);
        }

        $rate = Rate::create([
            'event' => $request->event,
            'hotel_id' => $request->hotel_id,
            'rate_id' => $rateId,
            'event_type' => $request->event_type,
            'price' => $request->price ? $request->price : $request->surcharge,
            'weekday_price' => 0.00,
            'weekend_price' => 0.00,
            'start_date' => $firstDate,
            'end_date' => $lastDate,
            'is_active' => $request->rate_status
        ]);

        if ($rate->save()) {
            return redirect()->back()
                ->with('success', 'Rates details saved successfully!');
        } else {
            return redirect()->back()
                ->with('error', 'An error occurred while saving the room details.');
        }
    }

    /** store season details */

    public function storeseason(Request $request){
        $request->validate([
            'event' => 'required|string',
            'event_type' => 'required|string',
            'weekday_price' => 'required|numeric',
            'weekend_price' => 'required|numeric',
            'double_weekday_price' => 'required|numeric',
            'double_weekend_price' => 'required|numeric',
            'season_status' => 'nullable|integer',
        ]);
        // dd($request->all());
        $lastRate = Rate::orderBy('created_at', 'desc')->first();

        $rate_max_id = $lastRate->rate_id ?? 0;
        $rateId = CommonHelper::createId($rate_max_id);
        while (Rate::where('rate_id', $rateId)->exists()) {
            $rateId = CommonHelper::createId($rateId);
        }

        list($firstDate, $lastDate) = explode(' - ', $request->date_range);
        // Convert the string dates to the format 'Y-m-d' for database compatibility

        $firstDate = Carbon::createFromFormat('m/d/Y', $firstDate);
        $lastDate = Carbon::createFromFormat('m/d/Y', $lastDate);

        // Check for overlapping dates
        $overlappingRates = Rate::where('hotel_id', $request->hotel_id)
            ->where('event_type', 'Season')
            ->where(function ($query) use ($firstDate, $lastDate) {
                $query->whereBetween('start_date', [$firstDate, $lastDate])
                    ->orWhereBetween('end_date', [$firstDate, $lastDate])
                    ->orWhere(function ($query) use ($firstDate, $lastDate) {
                        $query->where('start_date', '<=', $firstDate)
                            ->where('end_date', '>=', $lastDate);
                    });
            })
            ->exists();

        if ($overlappingRates) {
            return redirect()->back()
                ->with('error', 'The date range overlaps with an existing season.');
        }

        $rate = Rate::create([
            'event' => $request->event, 
            'hotel_id' => $request->hotel_id,
            'rate_id' => $rateId,
            'event_type' => $request->event_type,
            'price' => 0,
            'weekday_price' => $request->weekday_price,
            'weekend_price' => $request->weekend_price,
            'double_weekday_price' => $request->double_weekday_price,
            'double_weekend_price' => $request->double_weekend_price,
            'start_date' => $firstDate,
            'end_date' => $lastDate,
            'is_active' => $request->season_status
        ]);

        if ($rate->save()) {
            LogActivityService::log('create_rate', 'App\Models\Rate', $rate->rate_id, $rate);
            return redirect()->back()
                ->with('success', 'Rates details saved successfully!');
        } else {
            LogActivityService::log('create_rate_failed', 'App\Models\Rate', $rate_max_id,'An error occurred while saving the room details.');
            return redirect()->back()
                ->with('error', 'An error occurred while saving the room details.');
        }
    }

    /*
    * Edit Rate Details .
    * Date 15-12-2024
    */
    public function editrate($id, $hotelId){
        $rate = Rate::where('rate_id', $id)->first();
        $hotel = Hotel::where('hotel_unique_id', $hotelId)->first();
        return view('hotel.edit-rate', compact('rate','hotel'));
    }

    /*
    * Edit Rate Details .
    * Date 15-12-2024
    */
    public function editseason($id, $hotelId){
        $rate = Rate::where('rate_id', $id)->first();
        $hotel = Hotel::where('hotel_unique_id', $hotelId)->first();
        return view('hotel.edit-season', compact('rate','hotel'));
    }

    /*
    * Edit Rates .
    * Date 18-11-2024
    */
    public function updaterates(Request $request){
        
        $rate_id = $request->rate_id;
        $hotel = Hotel::where('hotel_unique_id', $request->hotel_id)->first();
        $rate = Rate::where('rate_id', $rate_id)->where('hotel_id', $request->hotel_id)->first();
        
        // Parse date range
        list($firstDate, $lastDate) = explode(' - ', $request->date_range);
        $firstDate = Carbon::createFromFormat('m/d/Y', $firstDate);
        $lastDate = Carbon::createFromFormat('m/d/Y', $lastDate);
        
        $rate->event = $request->event;
        $rate->event_type = $request->event_type;
        $rate->price = $request->price;
        $rate->weekday_price = $request->weekday_price ? $request->weekday_price : 0.00;
        $rate->weekend_price = $request->weekend_price ? $request->weekend_price : 0.00;
        $rate->start_date = $firstDate;
        $rate->end_date = $lastDate;
        $rate->is_active = $request->rate_status == 1 ? 1 : 0;

        if ($rate->save()) {
            $rates = Rate::where('event_type', '!=', 'Season')->where('hotel_id', $request->hotel_id)->get();
            return view('hotel.rates', compact('hotel', 'rates'))
                ->with('success', 'Rates details saved successfully!');
        } else {
            return redirect()->back()
                ->with('error', 'An error occurred while saving the rate details.');
        }
    }

    /*
    * Edit Seasons .
    * Date 18-11-2024
    */
    public function updateseason(Request $request)
    {
        // Validate the request data
        $request->validate([
            'rate_id' => 'required|exists:rates,rate_id',
            'hotel_id' => 'required|exists:hotels,hotel_unique_id',
            'event' => 'nullable|string|max:255',
            'event_type' => 'nullable|string|max:255',
            'weekday_price' => 'required|numeric|min:0',
            'weekend_price' => 'required|numeric|min:0',
            'double_weekday_price' => 'required|numeric',
            'double_weekend_price' => 'required|numeric',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'season_status' => 'nullable|integer',
        ]);

        // Fetch hotel and rate
        $hotel = Hotel::where('hotel_unique_id', $request->hotel_id)->first();
        $rate = Rate::where('rate_id', $request->rate_id)
                    ->where('hotel_id', $request->hotel_id)
                    ->first();

        if (!$rate) {
            return redirect()->back()->with('error', 'Rate not found for the specified hotel.');
        }
        $rate->event = $request->event;
        $rate->event_type = $request->event_type;
        $rate->price = 0; // Why is this always 0? Confirm if intentional.
        $rate->weekday_price = $request->weekday_price;
        $rate->weekend_price = $request->weekend_price;
        $rate->double_weekday_price = $request->double_weekday_price;
        $rate->double_weekend_price = $request->double_weekend_price;
        $rate->start_date = $request->start_date;
        $rate->end_date = $request->end_date;
        $rate->is_active = $request->season_status == 1 ? 1 : 0;
        if ($rate->save()) {
            return redirect()->route('hotels.season', $request->hotel_id)
                            ->with('success', 'Rate details saved successfully!');
        } else {
            return redirect()->back()
                            ->with('error', 'An error occurred while saving the rate details.');
        }
    }

    public function deleteSeason($hotelId, $id){
        $delete = Rate::where('rate_id', $id)->delete();
        if ($delete){
            return redirect()->route('hotels.season', ['hotel' => $hotelId])
                ->with('error', 'Season details deleted successfully!');
        } else {
            return redirect()->back()
                ->with('error', 'An error occurred while updating the room details.');
        }
    }

    /*
    * Edit Room Details .
    * Date 18-11-2024
    */
    public function editroom(Request $request, $id)
    {
    $auth_user = Auth::user();
    $room = Room::where('room_id', $id)->first();

    // Define default values to prevent "undefined variable" error
    $single_weekday_price = 0;
    $double_weekday_price = 0;
    $single_weekend_price = 0;
    $double_weekend_price = 0;
    $commission_type = null;
    $commission_price = null;

    if ($auth_user->user_type == 1) {
        $hotel = Hotel::get();
        $baseRoom = Room::where('hotel_id', $room->hotel_id)->where('varient_price', '0')->first();
    } elseif ($auth_user->user_type == 2) {
        $hotel = Hotel::where('dmc_id', $auth_user->userId)->get();
        $baseRoom = Room::where('hotel_id', $room->hotel_id)->where('varient_price', '0')->first();

        if ($auth_user->is_master_dmc == 1) {
            $commission_type = $auth_user->markup_type;
            $commission_price = $auth_user->markup_price;
        } else {
            $master_id = User::where('master_dmc_id', $auth_user->master_dmc_id)->first();
            $commission_type = $master_id->markup_type ?? 0;
            $commission_price = $master_id->markup_price ?? 0;
        }

        if ($commission_type == 0) {
            $single_weekday_price = $auth_user->markup_price + $room->weekday_price;
            $double_weekday_price = $auth_user->markup_price + $room->double_weekday_price;
            $single_weekend_price = $auth_user->markup_price + $room->weekend_price;
            $double_weekend_price = $auth_user->markup_price + $room->double_weekend_price;
        } else {
            $single_weekday_price = $room->weekday_price + ($auth_user->markup_price * $room->weekday_price) / 100;
            $double_weekday_price = $room->double_weekday_price + ($auth_user->markup_price * $room->double_weekday_price) / 100;
            $single_weekend_price = $room->weekend_price + ($auth_user->markup_price * $room->weekend_price) / 100;
            $double_weekend_price = $room->double_weekend_price + ($auth_user->markup_price * $room->double_weekend_price) / 100;
        }
    } else {
        $hotel = Hotel::where('dmc_id', $auth_user->userId)->get();
        $baseRoom = Room::where('hotel_id', $room->hotel_id)->where('varient_price', '0')->first();
    }

    // $mealTypes = Meal::whereIn('type', ['Breakfast', 'Lunch', 'Dinner'])
    //     ->get()
    //     ->groupBy('type');

    // $restaurants = Restaurant::where('is_active', 1)->get();

    return view('hotel.editroom', compact(
        'hotel',
        'single_weekday_price',
        'double_weekday_price',
        'single_weekend_price',
        'double_weekend_price',
        'room',
        'baseRoom',
        // 'mealTypes',
        // 'restaurants',
        'auth_user',
        'commission_type',
        'commission_price'
    ));
   }

    /*
    * Update Room Details .
    * Date 18-11-2024
    */
    public function updateroom(Request $request)
    {
        $request->validate([
            'no_of_room' => 'nullable|integer',
            'single_weekday_price' => 'nullable|numeric',
            'single_weekend_price' => 'nullable|numeric',
            'double_weekday_price' => 'nullable|numeric',
            'double_weekend_price' => 'nullable|numeric',
            'children_price' => 'nullable|numeric|min:0',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Find the room
        $room = Room::where('room_id', $request->room_id)->first();

        if (!$room) {
            return redirect()->back()->withErrors('Room not found or invalid room ID.');
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

        // Update room data
        $room->update([
            'no_of_room' => $request->total_no_of_room,
            'weekday_price' => $request->singleWeekdayPrice ?? $request->baseSingleWeekdayPrice ?? 0,
            'weekend_price' => $request->singleWeekendPrice ?? $request->baseSingleWeekendPrice ?? 0,
            'dimension' => $request->dimension,
            'double_weekday_price' => $request->doubleWeekdayPrice ?? $request->baseDoubleWeekdayPrice ?? 0,
            'double_weekend_price' => $request->doubleWeekendPrice ?? $request->baseDoubleWeekendPrice ?? 0,
            'children_price' => $request->children_price,
            'breakfast' => $request->breakfast_included,
            'breakfast_type' => $request->breakfast_included ? $request->breakfast_type : null,
            'breakfast_price' => $request->breakfast_included ? $request->breakfast_price : null,
            'lunch' => $request->lunch_included,
            'lunch_type' => $request->lunch_included ? $request->lunch_type : null,
            'lunch_price' => $request->lunch_included ? $request->lunch_price : null,
            'dinner' => $request->dinner_included,
            'dinner_type' => $request->dinner_included ? $request->dinner_type : null,
            'dinner_price' => $request->dinner_included ? $request->dinner_price : null,
            'breakfast_included' => $request->supplementary_breakfast ?? false,
            'images' => json_encode($img_path)
        ]);

        return redirect()->route('hotels.createroom', ['id' => $request->hotel_id])->with('success', 'Room updated successfully.');
    }

    /*
    * Delete Room Details .
    * Date 18-11-2024
    */
    public function deleteroom($id){
        // if (!hasPermission('delete room')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        $room = Room::where('room_id', $id)->first();
        $usedRooms = Bed::where('room_id', $id)
        ->exists();

        if ($usedRooms) {
        // The restaurant is being used in the rooms table, so do not delete it
        return redirect()->route('hotels.createroom', ['id' => $room->hotel_id])
        ->with('error', 'This Room is in use, cannot be deleted!');
        }
        
        $delete =Room::where('room_id', $id)->delete();
        if ($delete){
            return redirect()->route('hotels.createroom', ['id' => $room->hotel_id])
                ->with('success', 'Room details deleted successfully!');
        } else {
            return redirect()->back()
                ->with('error', 'An error occurred while updating the room details.');
        }
    }
    
    /*
    * Beds .
    * Date 31-12-2024
    */
    public function hotelbeds($id){
        // if (!hasPermission('view bed')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        $bedsData = [];
        $hotel = Hotel::where('hotel_unique_id', $id)->first();
        $rooms = Room::where('hotel_id', $id)->get();
        $bedsData = Bed::with('room')
        ->whereHas('room', function ($query) use ($id) {
            $query->where('hotel_id', $id);
        })
        ->get();
        $beds = BedMaster::where('hotel_id', $id)->get();
        return view('hotel.beds', compact('hotel','rooms','beds','bedsData'));
    }

    public function getBedTypeData(Request $request)
    {
        $bedType = $request->input('bed_type');
        $hotel_id = $request->input('hotel_id');
        $hotels_bed = BedMaster::where('bedId', $bedType)->where('hotel_id', $hotel_id)->first();
        $max_occupancy = 0;
        if ($hotels_bed) {
            $kingBedCount = $hotels_bed->no_of_king_bed ?? 0;
            $queenBedCount = $hotels_bed->no_of_queen_bed ?? 0;
            $twinBedCount = $hotels_bed->no_of_twin_bed ?? 0;
            $singleBedCount = $hotels_bed->no_of_single_bed ?? 0;
            $bunkBedCount = $hotels_bed->no_of_bunk_bed ?? 0;
            $max_occupancy = ($kingBedCount * 2)
                        + ($queenBedCount * 2) 
                        + ($twinBedCount * 2) 
                        + ($singleBedCount) 
                        + ($bunkBedCount * 2);
        }
        return response()->json([
            'total_count' => $max_occupancy,
        ]);
    }

    public function storebeds(Request $request){
        $request->validate([
            'no_of_rooms' => 'required|integer|min:1',
            'max_occupancy' => 'required|integer|min:1',
            'adult_count' => 'nullable|integer|min:0',
            'child_count' => 'nullable|integer|min:0',
            'extra_bed' => 'nullable|boolean',
            'extra_bed_type' => 'nullable|string',
            'extra_bed_price' => 'nullable|numeric|min:0',
            'baby_cot' => 'nullable|boolean',
            'baby_cot_price' => 'nullable|numeric|min:0',
        ]);
        //If extra bed and baby cot is not available
        if ($request->extra_bed != 1) {
            $request->merge([
                'extra_bed_type' => null,
                'extra_bed_price' => 0,
            ]);
        }
        if ($request->baby_cot != 1) {
            $request->merge([
                'baby_cot_price' => 0,
            ]);
        }

        // Check if a bed of the specified type is available in the given room
        $room_data = Room::where('room_id', $request->room_id)->first();
        $no_of_room = $room_data->no_of_room;
        $bedAvailable = Bed::where('room_id', $request->room_id)
        ->sum('no_of_rooms');
        
        if($no_of_room < $bedAvailable + $request->input('no_of_rooms')){
            return redirect()->route('hotels.beds', $request->hotel_id)->with('error', 'You have already filled.');
        }

        $lastBed = Bed::withTrashed()->orderBy('bed_id', 'desc')->first();
        $bed_max_id = $lastBed->bed_id ?? 0;
        $bedId = CommonHelper::createId($bed_max_id);
        while (Bed::where('bed_id', $bedId)->exists()) {
            $bedId = CommonHelper::createId($bedId);
        }
        
        $nameOfBedType = 'Unknown';

        if($request->input('bed_type')){
            $bedmaster_det = BedMaster::where('bedId',$request->input('bed_type'))->first();
            if ($bedmaster_det) {
                $nameOfBedType = $bedmaster_det->name;
            }
        }
        $bed = new Bed();
        $bed->room_type = $nameOfBedType;
        $bed->bed_master_id = $request->input('bed_type');
        $bed->no_of_rooms = $request->input('no_of_rooms');
        $bed->max_occupancy = $request->input('max_occupancy');
        $bed->adult_count = $request->input('adult_count');
        $bed->child_count = $request->input('child_count');
        $bed->extra_bed = $request->input('extra_bed');
        $bed->extra_bed_type = $request->input('extra_bed_type');
        $bed->extra_bed_price = $request->input('extra_bed_price') ?? 0;
        $bed->baby_cot = $request->input('baby_cot') ?? null;
        $bed->baby_cot_price = $request->input('baby_cot_price') ?? 0;
        $bed->bed_id = $bedId;
        $bed->room_id = $request->input('room_id');
        $bed->is_active = $request->input('bed_status');
        $bed->force_child = $request->input('force_child');
        $bed->force_child_count = $request->input('force_child_count');
        $bed->save();
        if ($request->hotel_id) {
            Hotel::where('hotel_unique_id', $request->hotel_id)->update(['is_complete' => 1]);
        }
        // Redirect or return response
        return redirect()->route('hotels.beds', $request->hotel_id)->with('success', 'Bed saved successfully.');
    }

    //edit bed
    public function editbed($id, $hotelId){
        // if (!hasPermission('edit bed')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        $hotel = Hotel::where('hotel_unique_id', $hotelId)->first();
        $beds = BedMaster::where('hotel_id', $hotelId)->get();
        $rooms = Room::where('hotel_id',$hotelId)->get();
        $hotelBed = Bed::with('room')->where('bed_id', $id)->first();
        $room = Room::where('room_id', $hotelBed->room_id)->first();
        return view('hotel.edit-beds', compact('hotel','rooms','beds','hotelBed','room'));
    }

    /** update bed */

    public function updatebed(Request $request){
        $request->validate([
            'no_of_rooms' => 'required|integer|min:1',
            'max_occupancy' => 'required|integer|min:1',
            'adult_count' => 'nullable|integer|min:0',
            'child_count' => 'nullable|integer|min:0',
            'extra_bed' => 'nullable|boolean',
            'extra_bed_type' => 'nullable|string',
            'extra_bed_price' => 'nullable|numeric|min:0',
            'baby_cot' => 'nullable|boolean',
            'baby_cot_price' => 'nullable|numeric|min:0',
        ]);

        //If extra bed and baby cot is not available
        if ($request->extra_bed != 1) {
            $request->merge([
                'extra_bed_type' => null,
                'extra_bed_price' => 0,
            ]);
        }
        if ($request->baby_cot != 1) {
            $request->merge([
                'baby_cot_price' => 0,
            ]);
        }

        $bed = Bed::where('bed_id', $request->bed_id)->first();
        $room_data = Room::where('room_id', $request->room_type)->first();
        $no_of_room = $room_data->no_of_room;
        $bedAvailable = Bed::where('room_id', $request->room_type)
        ->sum('no_of_rooms');

        if($no_of_room <= ($bedAvailable - $bed->no_of_rooms) + $request->input('no_of_rooms')){
            return redirect()->route('hotels.beds', $request->hotel_id)->with('error', 'You have already filled.');
        }
        if($request->input('bed_type')){
            $bedmaster_det = BedMaster::where('bedId',$request->input('bed_type'))->first();
           $nameOfBedType =  $bedmaster_det->name;
        }

        // $nameOfBedType = 'Unknown';

        // if($request->input('bed_type')){
        //     $bedmaster_det = BedMaster::where('bedId',$request->input('bed_type'))->first();
        //     if ($bedmaster_det) {
        //         $nameOfBedType = $bedmaster_det->name;
        //     }
        // }
        $bed->room_type = $nameOfBedType;
        $bed->bed_master_id = $request->input('bed_type');
        $bed->no_of_rooms = $request->input('no_of_rooms');
        $bed->max_occupancy = $request->input('max_occupancy');
        $bed->adult_count = $request->input('adult_count');
        $bed->child_count = $request->input('child_count');
        $bed->extra_bed = $request->input('extra_bed');
        $bed->extra_bed_type = $request->input('extra_bed_type');
        $bed->extra_bed_price = $request->input('extra_bed_price') ?? 0;
        $bed->baby_cot = $request->input('baby_cot') ?? null;
        $bed->baby_cot_price = $request->input('baby_cot_price') ?? 0;
        $bed->is_active = $request->beds_status == 1 ? 1 : 0;
        $bed->save();

        return redirect()->route('hotels.beds', $request->hotel_id)->with('success', 'Bed Details Updated Successfully.');
    }
    /*
    * Delete Bed Details .
    * Date 18-11-2024
    */
    public function deletebed($hotelId, $bedId){
        // if (!hasPermission('delete bed')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        $bed = Bed::where('bed_id', $bedId)->first();
        $delete = Bed::where('bed_id', $bedId)->delete();
        if ($delete){
            return redirect()->route('hotels.beds', ['hotel' => $hotelId])
                ->with('success', 'Bed details deleted successfully!');
        } else {
            return redirect()->back()
                ->with('error', 'An error occurred while updating the room details.');
        }
    }

    public function getNoOfRooms(Request $request){
        $roomTypeId = $request->input('room_type_id');
        $rooms = Room::where('room_id', $roomTypeId)->get(['id', 'no_of_room']); 
        return response()->json($rooms);
    }
    
    /*
    * Hotel Calender Monthly Details.
    * Date 16-12-2024
    */
    public function calender($id, $year = null)
    {
        $hotel = Hotel::with('category', 'rooms.beds')
            ->where('status', 1)
            ->where('hotel_unique_id', $id)
            ->first();

        if (!$hotel) {
            return redirect()->back()->with('error', 'Hotel not found!');
        }

        $year = $year ?? now()->year;
        $weekend_days = json_decode($hotel->weekend_days) ?? []; // Weekend days
        $room = $hotel->rooms->first();

        $weekday_base_price = $room->weekday_price ?? 0;
        $weekend_base_price = $room->weekend_price ?? 0;

        // Get all rates, ordered by priority
        $rates = Rate::where('hotel_id', $id)
            ->orderByRaw("
                CASE 
                    WHEN event_type = 'Blackout Date' THEN 1
                    WHEN event_type = 'Fair Date' THEN 2
                    WHEN event_type = 'Season' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('start_date')
            ->get();
        $rate_dates = [];
        $base_prices = [];

        // Generate base prices for all days of the year
        $startOfYear = Carbon::create($year, 1, 1);
        $endOfYear = Carbon::create($year, 12, 31);
        while ($startOfYear->lte($endOfYear)) {
            $dayName = $startOfYear->format('l');
            $base_prices[$startOfYear->toDateString()] = in_array($dayName, $weekend_days)
                ? $weekend_base_price
                : $weekday_base_price;

            $startOfYear->addDay();
        }

        foreach ($rates as $rate) {
            $startDate = Carbon::parse($rate->start_date);
            $endDate = Carbon::parse($rate->end_date);

            while ($startDate->lte($endDate)) {
                $currentDate = $startDate->toDateString();
                $price = $base_prices[$currentDate] ?? 0; 
                if (!isset($rate_dates[$currentDate])) {
                    if ($rate->event_type == "Blackout Date") {
                        $price = $rate->price;
                        $shiftedDate = Carbon::parse($currentDate)->toDateString(); 
                        $rate_dates[$shiftedDate] = [
                            'id' => $rate->rate_id,
                            'event' => $rate->event,
                            'event_type' => $rate->event_type,
                            'price' => $price,
                            'date' => $shiftedDate,
                        ];
                    } elseif ($rate->event_type == "Fair Date" && !isset($rate_dates[$currentDate])) {
                        $price += $rate->price;
                        $shiftedDate = Carbon::parse($currentDate)->toDateString();  
                        $rate_dates[$shiftedDate] = [
                            'id' => $rate->rate_id,
                            'event' => $rate->event,
                            'event_type' => $rate->event_type,
                            'price' => $price,
                            'date' => $shiftedDate,
                        ];
                    } elseif ($rate->event_type == "Season" && !isset($rate_dates[$currentDate])) {
                        $price = in_array($startDate->format('l'), $weekend_days)
                            ? $rate->weekend_price
                            : $rate->weekday_price;
                        $shiftedDate = Carbon::parse($currentDate)->toDateString();  
                        $rate_dates[$shiftedDate] = [
                            'id' => $rate->rate_id,
                            'event' => $rate->event,
                            'event_type' => $rate->event_type,
                            'price' => $price,
                            'date' => $shiftedDate,
                        ];
                    }
                }

                $startDate->addDay(1);  // Keep the original loop for date iteration
            }
        }
        return view('hotel.calender', compact('hotel', 'rate_dates', 'year', 'weekend_days', 'weekday_base_price', 'weekend_base_price'));
    }

    /*
    * Hotel Yearly Calender Details.
    * Date 18-12-2024
    */
    public function yearlycalender(){
        return view('hotel.fullcalender');
    }

    public function hotelCalendar($hotel_unique_id)
    {
        $hotel = Hotel::where('hotel_unique_id', $hotel_unique_id)->first();
        $close_days = $hotel->close_days;
        $close_dates = $hotel->close_dates;
        // $seasons = Season::select('event', 'event_type', 'price', 'start_date', 'end_date', 'weekday_price', 'weekend_price', 'double_weekday_price', 'double_weekend_price')->where('hotel_id', $hotel_unique_id)->get():
        return view('hotel.viewCalender', compact('hotel_unique_id', 'hotel', 'close_days', 'close_dates'));
    }

    public function hotelCloseDate(Request $request) {
        $stringDates = $request->hotel_holiday_dates;
        $datesArray = array_map('trim', explode(',', $stringDates));
        $datesJson = json_encode($datesArray, JSON_PRETTY_PRINT);
        $hotel = Hotel::where('hotel_unique_id', $request->hotel_unique_id)->first();
        $hotel->close_days = $request->hotel_closed_days;
        $hotel->close_dates = $datesJson;
        $hotel->save();
        return redirect()->back()
        ->with('success', 'Close dates and holidays saved successfully');
    }

    /*
    * Hotel Policy Details.
    * Date 24-12-2024
    */
    public function policy(Request $request, $id)
    {
        // $hotelPolicy = HotelPolicy::where('hotel_id', $id)->first();
        // $hotel = Hotel::where('hotel_unique_id', $id)->first();

        // if ($hotelPolicy) {
        // $hotelPolicy->check_in_time = Carbon::parse($hotelPolicy->check_in_time)->format('H:i');
        // $hotelPolicy->check_in_until = Carbon::parse($hotelPolicy->check_in_until)->format('H:i');
        // $hotelPolicy->check_out_time = Carbon::parse($hotelPolicy->check_out_time)->format('H:i');
        // $hotelPolicy->check_out_until = Carbon::parse($hotelPolicy->check_out_until)->format('H:i');
        // }

        // return view('hotel.policy', compact('hotelPolicy', 'hotel'));

        $hotel = Hotel::where('hotel_unique_id', $id)->first();
        if (!$hotel) {
            return redirect()->route('hotels.index')->with('error', 'Hotel not found.');
        }
        
        // Get hotel policy data
        $hotelPolicy = HotelPolicy::where('hotel_id', $id)->first();
        
        // Get cancellation policy data
        $cancellation_data = [];
        if ($hotel->cancellation_json) {
            $cancellation_data = json_decode($hotel->cancellation_json, true) ?? [];
        }
        
        return view('hotel.policy', compact('hotel', 'hotelPolicy', 'cancellation_data'));
    }


    /*
    * Hotel Policy Update.
    * Date 26-12-2024
    */
    public function updatepolicy(Request $request)
    {

        // $policy_file = '';
        // if($request->file('file')){
        //     $policyFilePath = CommonHelper::image_path('file_storage', $request->file('file'));
        //     if (!empty($policyFilePath['master_value'])) {
        //         $policy_file = $policyFilePath['master_value'];
        //     }
        // }
        // $hotel = Hotel::where('hotel_unique_id',$request->hotel_id)->first();
        // $hotelPolicy = HotelPolicy::updateOrCreate(
        //     ['hotel_id' => $request->hotel_id],
        //     [
        //         'name' => $request->name,
        //         'policy' => $request->policy,
        //         'check_in_time' => Carbon::parse($request->check_in_time)->format('H:i'),
        //         'check_in_until' => Carbon::parse($request->check_in_until)->format('H:i'),
        //         'check_out_time' => Carbon::parse($request->check_out_time)->format('H:i'),
        //         'check_out_until' => Carbon::parse($request->check_out_until)->format('H:i'),
        //         'extras' => $request->extras,
        //         'property' => $request->property,
        //         'file' => $policy_file
        //     ]
        // );
        // return redirect()->route('cancellation.policy', ['id' => $hotel->hotel_unique_id])
        // ->with('success', 'Policy updated successfully.');

        // $policy_file = '';
        // if($request->file('file')){
        //     $policyFilePath = CommonHelper::image_path('file_storage', $request->file('file'));
        //     if (!empty($policyFilePath['master_value'])) {
        //         $policy_file = $policyFilePath['master_value'];
        //     }
        // }

        $hotelPolicy = HotelPolicy::where('hotel_id', $request->hotel_id)->first();

        $policy_file = $hotelPolicy->file ?? ''; // Default to existing PDF if no new file
        if ($request->hasFile('file')) {
            $policyFilePath = CommonHelper::image_path('file_storage', $request->file('file'));
            if (!empty($policyFilePath['master_value'])) {
                $policy_file = $policyFilePath['master_value'];
            }
        }
        
        $hotel = Hotel::where('hotel_unique_id',$request->hotel_id)->first();
        $hotelPolicy = HotelPolicy::updateOrCreate(
            ['hotel_id' => $request->hotel_id],
            [
                'name' => $request->name,
                'policy' => $request->policy,
                // 'check_in_time' => Carbon::parse($request->check_in_time)->format('H:i'),
                // 'check_in_until' => Carbon::parse($request->check_in_until)->format('H:i'),
                // 'check_out_time' => Carbon::parse($request->check_out_time)->format('H:i'),
                // 'check_out_until' => Carbon::parse($request->check_out_until)->format('H:i'),
                'extras' => $request->extras,
                'property' => $request->property,
                'file' => $policy_file
            ]
        );
        
        // Redirect to the unified policy page with cancellation tab active
        return redirect()
            ->route('policy', ['id' => $hotel->hotel_unique_id, 'tab' => 'cancellation'])
            ->with('success', 'Property policy updated successfully.');
    }

    /*
    * Cancellation Policy
    * Date 26-12-2024
    */
    public function cancellationPolicy(Request $request, $id){
        $hotel = Hotel::where('hotel_unique_id', $id)->first();
        $cancellation_data = json_decode($hotel->cancellation_data, true) ?? [];
        if (!$hotel) {
            return redirect()->back()->with('error', 'Hotel not found.');
        }
        return view('hotel.cancelpolicy', compact('hotel','cancellation_data'));
    }
    
    /*
    * Refund Policy
    * Date 19-02-2025
    */
    public function refundPolicy(Request $request, $id){
        $hotel = Hotel::where('hotel_unique_id', $id)->first();
        $refund_data = json_decode($hotel->refund_data, true) ?? [];
        if (!$hotel) {
            return redirect()->back()->with('error', 'Hotel not found.');
        }
        return view('hotel.refundpolicy', compact('hotel','refund_data'));
    }

    /*
    * Update Refund Policy
    * Date 19-02-2025
    */
    public function updateRefundPolicy(Request $request)
    {
        $request->validate([
            'refundpolicy' => 'required|string|max:1000', 
        ]);

        $hotel = Hotel::where('hotel_unique_id', $request->hotel_id)->first();
        if (!$hotel) {
            return redirect()
                ->back()
                ->with('error', 'Hotel not found.');
        }
        $refund_policy = $hotel->refundpolicy_pdf ?? ''; // Default to existing PDF if no new file
        if ($request->hasFile('file')) {
            $refundPolicyPath = CommonHelper::image_path('file_storage', $request->file('file'));
            if (!empty($refundPolicyPath['master_value'])) {
                $refund_policy = $refundPolicyPath['master_value'];
            }
        }
        $hotel->update([
            'refundpolicy_pdf' => $refund_policy,
            'refundpolicy' => $request->refundpolicy ?? '',
        ]);

        // return redirect()
        //     ->route('hotel.conference',['id' => $hotel->hotel_unique_id])
        //     ->with('success', 'Refund Policy updated successfully.');

        return redirect()
            ->route('policy', ['id' => $hotel->hotel_unique_id, 'tab' => 'child'])
            ->with('success', 'Refund policy updated successfully.');
    }

    /*
    * Update Cancellation Policy
    * Date 26-12-2024
    */
    public function updatecancellationPolicy(Request $request)
    {
        $hotel = Hotel::where('hotel_unique_id', $request->hotel_id)->first();
        if (!$hotel) {
            return redirect()
            ->back()
            ->with('error', 'Hotel not found.');
        }
        $cancellationDataJson = null;
        if ($request->cancellation_type == 1) {
            $cancellationData = [];
            
            if ($request->has('cancellation_duration') && is_array($request->cancellation_duration)) {
                foreach ($request->cancellation_duration as $index => $duration) {
                    $cancellationData[] = [
                        'duration' => $duration,
                        'price' => $request->cancellation_price[$index] ?? null,
                        'type' => $request->type[$index] ?? null, // Fixed the key for 'type'
                    ];
                }
            }
            $cancellationDataJson = !empty($cancellationData) ? json_encode($cancellationData) : null;
        }
        $cancellation_policy = $hotel->cancellation_pdf ?? ''; // Default to existing PDF if no new file
        if ($request->hasFile('file')) {
            $cancellationPolicyPath = CommonHelper::image_path('file_storage', $request->file('file'));
            if (!empty($cancellationPolicyPath['master_value'])) {
                $cancellation_policy = $cancellationPolicyPath['master_value'];
            }
        }
        // Update the hotel with the new cancellation policy
        $hotel->update([
            'cancellation_type' => $request->input('cancellation_type'),
            'cancellation_data' => $cancellationDataJson,
            'cancellation_pdf' => $cancellation_policy,
            'policy' => $request->policy ?? '',
        ]);
        return redirect()
            ->route('policy', ['id' => $hotel->hotel_unique_id, 'tab' => 'refund'])
            ->with('success', 'Cancellation policy updated successfully.');
    }

    /**
     * Update child policy
     */
    public function updateChildPolicy(Request $request)
    {
        $policy_file = '';
        if($request->file('file')){
            $policyFilePath = CommonHelper::image_path('file_storage', $request->file('file'));
            if (!empty($policyFilePath['master_value'])) {
                $policy_file = $policyFilePath['master_value'];
            }
        }
        
        $hotel = Hotel::where('hotel_unique_id', $request->hotel_id)->first();
        $hotel->update([
            'childpolicy' => $request->childpolicy,
            'childpolicy_pdf' => $policy_file ?: $hotel->childpolicy_pdf
        ]);
        
        // Redirect to the next policy tab
        return redirect()->route('policy', ['id' => $hotel->hotel_unique_id, 'tab' => 'pet'])
            ->with('success', 'Child policy updated successfully.');
    }

    /**
     * Update pet policy
     */
    public function updatePetPolicy(Request $request)
    {
        $policy_file = '';
        if($request->file('file')){
            $policyFilePath = CommonHelper::image_path('file_storage', $request->file('file'));
            if (!empty($policyFilePath['master_value'])) {
                $policy_file = $policyFilePath['master_value'];
            }
        }
        
        $hotel = Hotel::where('hotel_unique_id', $request->hotel_id)->first();
        $hotel->update([
            'pet_allowed' => $request->pet_allowed,
            'petpolicy' => $request->petpolicy,
            'petpolicy_pdf' => $policy_file ?: $hotel->petpolicy_pdf
        ]);
        
        // Redirect to the next policy tab
        return redirect()->route('policy', ['id' => $hotel->hotel_unique_id, 'tab' => 'terms'])
            ->with('success', 'Pet policy updated successfully.');
    }

    /**
     * Update terms and conditions
     */
    public function updateTermsPolicy(Request $request)
    {
        $policy_file = '';
        if($request->file('file')){
            $policyFilePath = CommonHelper::image_path('file_storage', $request->file('file'));
            if (!empty($policyFilePath['master_value'])) {
                $policy_file = $policyFilePath['master_value'];
            }
        }
        
        $hotel = Hotel::where('hotel_unique_id', $request->hotel_id)->first();
        $hotel->update([
            'termspolicy' => $request->termspolicy,
            'termspolicy_pdf' => $policy_file ?: $hotel->termspolicy_pdf
        ]);
        
        // Redirect back to the property policy tab
        return redirect()->route('policy', ['id' => $hotel->hotel_unique_id, 'tab' => 'property'])
            ->with('success', 'Terms and conditions updated successfully.');
    }

    /*
    * Show Ports Data
    * Date 27-12-2024
    */
    public function showPorts(Request $request, $id){
        $hotel = Hotel::where('hotel_unique_id', $id)->first();
        if (!$hotel) {
            return redirect()->back()->with('error', 'Hotel not found.');
        }
        $entry_data = json_decode($hotel->port_of_entry, true) ?? [];
        $exit_data = json_decode($hotel->port_of_exit, true) ?? [];
        $others = json_decode($hotel->others, true) ?? [];
        $enable_port_of_entry = !empty($entry_data);
        $enable_port_of_exit = !empty($exit_data);
        $others_data = !empty($others);
        return view('hotel.ports', compact('hotel','entry_data','exit_data','others','enable_port_of_entry','enable_port_of_exit','others_data'));
    }

    /*
    * Update Ports Data
    * Date 27-12-2024
    */
    public function updateports(Request $request)
    {
        //dd($request->all());
        $hotel_id = $request->hotel_id;
        $hotel = Hotel::where('hotel_unique_id', $hotel_id)->first();
        if (!$hotel) {
            return redirect()->back()->with('error', 'Hotel not found.');
        }
        // Handle port of entry, exit, and others data
        $portOfEntryData = $this->processPortData(
            $request->input('port_name', []),
            $request->input('port_specific_name', []),
            $request->input('latitudentry', []),
            $request->input('longitudeentry', []),
            $request->input('distanceentry', []),
        );
        $portOfExitData = $this->processPortData(
            $request->input('exit_port_name', []),
            // Add the 2nd argument (it seems to be empty here, make sure it's necessary)
            $request->input('exit_port_specific_name', []),
            $request->input('exit_latitude', []),
            $request->input('exit_longitude', []),
            $request->input('exit_distance', []),
        );
    
        $portOfOtherData = $this->processPortData(
            $request->input('others_port_name', []),
            $request->input('others_type', []),  // Assuming the 2nd argument here is the 'type'
            $request->input('others_latitude', []),
            $request->input('others_longitude', []),
            $request->input('others_distance', []),
        );
    
        // Prepare update data for the hotel
        $updateData = [];
        
        if (!empty($portOfEntryData)) {
            $updateData['port_of_entry'] = json_encode($portOfEntryData);
        }
        
        if (!empty($portOfExitData)) {
            $updateData['port_of_exit'] = json_encode($portOfExitData);
        }
        
        if (!empty($portOfOtherData)) {
            $updateData['others'] = json_encode($portOfOtherData);
        }

    
        // Only update fields if there is new data
        if (!empty($updateData)) {
            $hotel->update($updateData);
        }
    
        return redirect()
            ->route('hotels.facility', ['id' => $hotel->hotel_unique_id])
            ->with('success', 'Ports updated successfully.');
    }    

    /*
    * Shows Facility Data
    * Date 27-12-2024
    */
    public function hotelfacility(Request $request, $id)
    {
        $hotel = Hotel::where('hotel_unique_id', $id)->first();

        if (!$hotel) {
            return redirect()->back()->with('error', 'Hotel not found.');
        }

        $decode_data = json_decode($hotel->facilities, true); // Decode JSON
        if (!is_array($decode_data) || empty($decode_data)) {
            $facilities = collect(); 
        } else {
            $facilities = Facility::with('categories')->whereIn('facilityId', $decode_data)->get();
        }
        $facility = Facility::with('categories')->get();
        $category = Category::where('status',1)->get();
        $facilityImages = json_decode($hotel->facilities_images);

        return view('hotel.facility', compact('hotel','category', 'facilities', 'facilityImages','facility'));
    }

    /*
    * Store Facility Data
    * Date 14-01-2025
    */
    public function storeFacility(Request $request){
        
        $request->validate([
            'hotel_id' => 'required|exists:hotels,hotel_unique_id',
            'selected_facilities' => 'required',
            'images.*' => 'image|max:2048', // Validate images (max 2MB each)
        ]);
        
        // Get the hotel
        $hotel = Hotel::where('hotel_unique_id', $request->hotel_id)->firstOrFail();
        
        // Get the selected facilities from the form
        $selectedFacilities = json_decode($request->selected_facilities, true);
        
        if (!is_array($selectedFacilities)) {
            return back()->withErrors(['error' => 'Invalid facilities data']);
        }
        
        // Update the hotel's facilities directly with the new selection
        $hotel->facilities = json_encode($selectedFacilities, JSON_UNESCAPED_UNICODE);
        
        // Process existing images
        $existingImages = json_decode($hotel->facilities_images, true) ?? [];

        // Update or append images if files are uploaded
        if ($request->hasFile('all_images')) {
            // Get the last selected facility to associate images with
            $facilityId = end($selectedFacilities);
            
            // If facilityId exists in the images array, update the images
            if (!isset($existingImages[$facilityId])) {
                $existingImages[$facilityId] = [];
            }
            
            foreach ($request->file('all_images') as $image) {
                try {
                    $imagePath = CommonHelper::image_path('file_storage', $image);
                    
                    if (!empty($imagePath['master_value'])) {
                        // Add the new image path to the existing images array
                        $existingImages[$facilityId][] = $imagePath['master_value'];
                    }
                } catch (\Exception $e) {
                    return back()->withErrors(['images' => 'Failed to upload one or more images: ' . $e->getMessage()]);
                }
            }
        }

        // Save the updated facilities and images back to the hotel
        $hotel->facilities_images = json_encode($existingImages, JSON_UNESCAPED_UNICODE);
        $hotel->save();

        return back()->with('success', 'Facilities and images updated successfully!');
    }

    /*
    * Store Facility Data
    * Date 13-01-2025
    */
    public function updateFacility(Request $request)
    {
        $request->validate([
            'hotel_id' => 'required|exists:hotels,hotel_unique_id',
            'name' => 'required|string',
            'images.*' => 'image|max:2048', // Validate images (max 2MB each)
        ]);
        $allImages = $request->all_images;

        $facilityId = $request->input('selected_facility_id');
        

        //Get the hotel and decode the facilities and images
        $hotel = Hotel::where('hotel_unique_id', $request->hotel_id)->firstOrFail();

        $facilitiesImages = json_decode($hotel->facilities_images, true) ?? [];
        
        // Unset the images for the given facilityId
        if (isset($facilitiesImages[$facilityId])) {
            unset($facilitiesImages[$facilityId]);
        }

        $existingImages = $request->input('existing_images', []); // Existing images paths from the form

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
        $facilitiesImages[$facilityId] = $img_path;

        // Save the updated facilities and images back to the hotel
        $hotel->facilities_images = json_encode($facilitiesImages);
        $hotel->save();

        //return back()->with('success', 'Facility and images updated successfully!');
        return redirect()->route('hotels.facility', ['id' => $hotel->hotel_unique_id])->with('success', 'Facility and images updated successfully!');
    }

    /*
    * Edit Facility Data
    * Date 06-03-2025
    */
    public function editfacility($facilityId, $hotelId)
    {
        $selectedFacilityId = $facilityId;
        $facility = Facility::with('categories')->get();
        $hotel = Hotel::where('hotel_unique_id', $hotelId)->first();
        if (!$hotel) {
            return redirect()->back()->withErrors(['error' => 'Hotel not found.']);
        }
        $facilityImages = [];
        if (!empty($hotel->facilities_images)) {
            $facilityImages = json_decode($hotel->facilities_images, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()->back()->withErrors(['error' => 'Error decoding facility images.']);
            }
        }
        $imagesForFacility = $facilityImages[$facilityId] ?? [];
        return view('hotel.editfacility', compact('facility', 'hotel', 'selectedFacilityId', 'imagesForFacility'));
    }

    /*
    * Delete Facility Data
    * Date 06-03-2025
    */
    public function destroyfacility($hotelId, $facilityId)
    {
        $hotel = Hotel::where('hotel_unique_id', $hotelId)->first();
        $existingFacilities = json_decode($hotel->facilities, true) ?? [];
        $existingImages = json_decode($hotel->facilities_images, true) ?? [];
        if (($key = array_search($facilityId, $existingFacilities)) !== false) {
            unset($existingFacilities[$key]);
        }
        if (isset($existingImages[$facilityId])) {
            foreach ($existingImages[$facilityId] as $imagePath) {
                Storage::delete($imagePath); // Delete the image
            }
            unset($existingImages[$facilityId]);
        }
        $hotel->facilities = json_encode(array_values($existingFacilities), JSON_UNESCAPED_UNICODE);
        $hotel->facilities_images = json_encode($existingImages, JSON_UNESCAPED_UNICODE);
        $hotel->save();

        return back()->with('success', 'Facility and its images deleted successfully!');
    }

    /*
    * Conference Show
    * Date 06-03-2025
    */
    public function conference(Request $request,$id){
        $hotel = Hotel::where('hotel_unique_id', $id)->first();
        $conference = $hotel->conference_room;
        return view('hotel.conference', compact('hotel','conference'));
    }

    /*
    * Conference Update
    * Date 06-03-2025
    */
    public function updateConference(Request $request)
    {
        $hotel = Hotel::where('hotel_unique_id', $request->hotel_id)->firstOrFail();
        $hotel->conference_room = $request->conference;
        $hotel->save();
        return back()->with('success', 'Conference Room Updated Successfully!');
    }

    /*
    * Hotel Approve Or Decline
    * Date 06-03-2025
    */
    public function approveOrDecline($hotelId, Request $request)
    {
        $hotel = Hotel::where('hotel_unique_id', $hotelId)->first();
        if ($request->action == 'approve') {
            $hotel->status = 1; // Example logic for status
            $hotel->save();
        } elseif ($request->action == 'decline') {
            $hotel->status = 0; // Example logic for decline
            $hotel->save();
        }
        return response()->json(['success' => true]);
    }

    /*
    * Hotel Details
    * Date 06-03-2025
    */
    public function hotelDetails($hotel, Request $request)
    {
        // if (!hasPermission('view room')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        $hotel = Hotel::where('hotel_unique_id', $hotel)->first();
        $auth_user = Auth::user();
        $roomQuery = Room::with('hotel')->where('hotel_id', $hotel->hotel_unique_id);
        if ($auth_user->user_type == 1) {
            $rooms = $roomQuery->get(); // Fetch all rooms for user_type 1
        } else {
            $rooms = $roomQuery->whereHas('hotel', function ($query) use ($auth_user) {
                $query->where('dmc_id', $auth_user->userId); // Corrected column name
            })->get();
        }
        $currentRooms = Room::where('room_type', 'Standard')->first();
        return view('hotel.hoteldetails', compact('rooms', 'currentRooms', 'hotel'));
    }

    /*
    * Hotel Brand Details
    * Date 06-03-2025
    */
    public function hotelBrandDetails($brand, Request $request)
    {
        // if (!hasPermission('view room')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        $brand = Hotel::where('hotel_unique_id', $brand)->first();
        // dd($brand->hotel_owner_company_name);
        $auth_user = Auth::user();
        $roomQuery = Room::with('hotel')->where('hotel_id', $brand->hotel_unique_id);
        if ($auth_user->user_type == 1) {
            $rooms = $roomQuery->get(); // Fetch all rooms for user_type 1
        } else {
            $rooms = $roomQuery->whereHas('hotel', function ($query) use ($auth_user) {
                $query->where('dmc_id', $auth_user->userId); // Corrected column name
            })->get();
        }
        $currentRooms = Room::where('room_type', 'Standard')->first();
        return view('hotel.hotel-brand-details', compact('rooms', 'currentRooms', 'brand'));
    }

    
}