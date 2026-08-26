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
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Models\Tour;

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
                // ->whereJsonContains('dmc_id', $dmc_ids)
                ->orderBy('created_at', 'DESC')
                ->get();
        } elseif ($user->role_id == 3) {
            $hotels = Hotel::whereIn('status', [5, 1])->orderBy('created_at', 'DESC')->get();
        } elseif (in_array($user->role_id, [1, 2, 23])) {
            $hotels = Hotel::whereIn('status', [1, 3])->orderBy('created_at', 'DESC')->get();
        }
        elseif($user->role_id == 10){
            $dmc_ids = User::where('master_dmc_id', $user->userId)->get()->pluck('userId')->toArray();
            $hotels = Hotel::orderBy('created_at', 'DESC')->where(function($query) use ($dmc_ids) {
                foreach($dmc_ids as $dmc_id) {
                    $query->orWhereJsonContains('dmc_id', $dmc_id);
                }
            })->get();
        }
        elseif ($user->role_id == 11) {
            $allHotels = Hotel::where('status', 1)
                          ->with(['category'])
                          ->orderBy('created_at', 'DESC')
                          ->get();
            $hotels = $allHotels->filter(function($hotel) use ($user) {
                return $hotel->hasSelectedByDmc($user->userId);
            });
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
            $hotels = Hotel::where(function($query) use ($dmc_ids) {
                foreach($dmc_ids as $dmc_id) {
                    $query->orWhereJsonContains('dmc_id', $dmc_id);
                }
            })->orderBy('created_at', 'DESC')->get();
        }
        else{
            $dmc_id = CommonHelper::getDmcId($user);
            $hotels = Hotel::whereJsonContains('dmc_id', $dmc_id)->orderBy('created_at', 'DESC')->get();       
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
        $country = Country::where('is_active', 1)->orderBy('name')->get();
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
    /**
     * True when Summernote/HTML description has real text content.
     */
    private function hotelDescriptionHasContent(?string $html): bool
    {
        $text = trim(html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $text = preg_replace('/\x{00A0}|\s+/u', '', $text ?? '');

        return $text !== '';
    }

    public function store(Request $request)
    {
        // Validate first — never catch ValidationException as a generic error.
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:500',
            'auto_cancel_date' => 'required',
            'country' => 'required|string',
            'location' => 'required|string',
            'pincode' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'infant_age_limit' => 'required',
            'child_age_limit' => 'required',
            'extra_bed_age_limit' => 'required',
            'hotel_category' => 'required',
            'hotel_ownership' => 'required',
            'hotel_segment' => 'required',
            'hotel_star_rating' => 'required',
            'weekend_days' => 'required|array|min:1',
            'description' => 'required',
            'master_image' => 'required|image|mimes:jpeg,jpg,png,webp,gif|max:5120', // 5MB
            'all_images.*' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:5120',
            'country_code' => 'required',
        ], [
            'description.required' => 'Hotel description is mandatory.',
            'location.required' => 'City is mandatory.',
            'master_image.required' => 'Please upload master image.',
            'master_image.image' => 'Master image must be a valid image file (JPEG, PNG, WEBP or GIF).',
            'master_image.max' => 'Master image must not exceed 5 MB. Please compress the image and try again.',
            'all_images.*.image' => 'Each additional image must be a valid image file.',
            'all_images.*.max' => 'Each additional image must not exceed 5 MB. Please compress oversized images and try again.',
            'weekend_days.required' => 'Please select at least one weekend day.',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (!$this->hotelDescriptionHasContent($request->input('description'))) {
                $validator->errors()->add('description', 'Hotel description is mandatory.');
            }
            if ($request->input('hotel_ownership') == '1' && trim((string) $request->input('chain_name')) === '') {
                $validator->errors()->add('chain_name', 'Chain hotel name is mandatory for chain hotels.');
            }
            // Reject when PHP dropped oversized uploads (upload_max_filesize / post_max_size)
            if ($request->hasFile('master_image') === false && $request->file('master_image') === null) {
                // already covered by required when missing; when too large PHP may leave empty
            }
            foreach ((array) ($request->file('all_images') ?? []) as $idx => $file) {
                if ($file && !$file->isValid() && $file->getError() === UPLOAD_ERR_INI_SIZE) {
                    $validator->errors()->add("all_images.$idx", 'An additional image exceeds the server upload limit. Please use a smaller file (max 5 MB).');
                }
            }
            if ($request->file('master_image') && !$request->file('master_image')->isValid()
                && $request->file('master_image')->getError() === UPLOAD_ERR_INI_SIZE) {
                $validator->errors()->add('master_image', 'Master image exceeds the server upload limit. Please use a file under 5 MB.');
            }
        });

        if ($validator->fails()) {
            $redirect = redirect()->back()->withErrors($validator)->withInput();
            if ($validator->errors()->has('description')) {
                $redirect->with('focus_field', 'description');
            } elseif ($validator->errors()->has('master_image')) {
                $redirect->with('focus_field', 'master_image');
            }

            return $redirect;
        }

        try {
            $dialCode = $request->country_code;
            $country_code = User::dialCodeToCountryCode($dialCode) ?: 'XX';
            $countryCode = strtoupper(substr((string) $country_code, 0, 2));
            $hotelName = strtoupper(substr((string) $request->input('name'), 0, 3));
            $randomDigits = str_pad((string) rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $display_id = $countryCode . $hotelName . $randomDigits;
            $uniqueId = uniqid('', true);
            $unique_id = substr($uniqueId, -16);

            // Master Image
            $mainImagePath = ['master_value' => null];
            if ($request->hasFile('master_image')) {
                $mainImagePath = CommonHelper::image_path('file_storage', $request->file('master_image'));
                if (empty($mainImagePath['master_value'])) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['master_image' => 'Unable to upload master image. Please try a smaller JPEG/PNG file and try again.'])
                        ->with('focus_field', 'master_image');
                }
            }

            // Gallery Images (all_images[] from create form; fallback to images[] if present)
            $imagePaths = [];
            $galleryFiles = [];
            if ($request->hasFile('all_images')) {
                $galleryFiles = array_values(array_filter((array) $request->file('all_images')));
            } elseif ($request->hasFile('images')) {
                $galleryFiles = array_values(array_filter((array) $request->file('images')));
            }

            $galleryFailures = [];
            foreach ($galleryFiles as $image) {
                if (!$image) {
                    continue;
                }
                if (!$image->isValid()) {
                    $galleryFailures[] = $image->getClientOriginalName() ?: 'image';
                    continue;
                }
                if ($image->getSize() > 5 * 1024 * 1024) {
                    $galleryFailures[] = ($image->getClientOriginalName() ?: 'image') . ' (over 5 MB)';
                    continue;
                }
                try {
                    $pathData = CommonHelper::image_path('file_storage', $image);
                    if (!empty($pathData['master_value'])) {
                        $imagePaths[] = $pathData['master_value'];
                    } else {
                        $galleryFailures[] = $image->getClientOriginalName() ?: 'image';
                    }
                } catch (\Throwable $uploadEx) {
                    Log::error('Hotel gallery upload failed: ' . $uploadEx->getMessage());
                    $galleryFailures[] = $image->getClientOriginalName() ?: 'image';
                }
            }

            if (!empty($galleryFiles) && empty($imagePaths)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'all_images' => 'Additional images could not be uploaded'
                            . (!empty($galleryFailures) ? ' (' . implode(', ', $galleryFailures) . ')' : '')
                            . '. Please use JPEG/PNG files under 5 MB and try again.',
                    ])
                    ->with('focus_field', 'all_images');
            }

            $auth_user = Auth::user();

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
                'status' => 1,
                'is_active' => $request->input('hotel_status'),
                'weekend_days' => json_encode($request->input('weekend_days')),
                'images' => json_encode($imagePaths),
                'display_id' => $display_id,
                'extra_bed_age_limit' => $request->extra_bed_age_limit,
                'country_code' => $request->country_code,
                'twelve_hours_charge' => $request->input('charge') ?? 0,
                'auto_cancel_date' => $request->input('auto_cancel_date') ?? 0,
                'day_usage_type' => $request->input('day_usage_type'),
                'duration' => $request->input('duration') ?? 0,
                'accomodation_type' => $request->input('hotel_category'),
                'ownership_type' => $request->input('hotel_ownership'),
                'hotel_segment' => $request->input('hotel_segment'),
                'hotel_star_rating' => $request->input('hotel_star_rating'),
                'chain_hotel_name' => $request->input('chain_name'),
                'is_complete' => 0,
            ]);

            return redirect()->route('hotels.contact', ['hotel' => $hotel->hotel_unique_id])
                ->with('success', 'Hotel created successfully');
        } catch (\Throwable $e) {
            Log::error('Hotel Creation Failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);

            $message = 'Unable to save the hotel. Please verify all mandatory fields and image sizes (max 5 MB each), then try again.';
            if (str_contains(strtolower($e->getMessage()), 'upload') || str_contains(strtolower($e->getMessage()), 'image')) {
                $message = 'Image upload failed. Please use JPEG/PNG files under 5 MB and try again.';
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $message);
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
        $country = Country::where('is_active', 1)->orderBy('name')->get();
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
            $dmcs = User::whereIn('role_id', [11,20])->where('country', $authuser->country)->get();
        }elseif($authuser->role_id == 3){
            $dmcs = User::whereIn('role_id', [11,20])->where('country', $authuser->country)->get();
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
        // Check total request size before processing
        $contentLength = $request->header('Content-Length');
        if ($contentLength && $contentLength > 100 * 1024 * 1024) { // 100MB limit
            return redirect()->back()->withInput()->with('error', 'Upload size too large. Please reduce image sizes or upload fewer images (max 5 MB each).');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string',
            'auto_cancel_date' => 'required',
            'country' => 'required|string',
            'pincode' => 'required',
            'latitude' => 'required',
            'time_range' => 'required',
            'longitude' => 'required',
            'description' => 'required',
            'master_image' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:5120',
            'all_images.*' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:5120',
        ], [
            'description.required' => 'Hotel description is mandatory.',
            'master_image.image' => 'Master image must be a valid image file (JPEG, PNG, WEBP or GIF).',
            'master_image.max' => 'Master image must not exceed 5 MB. Please compress the image and try again.',
            'all_images.*.image' => 'Each additional image must be a valid image file.',
            'all_images.*.max' => 'Each additional image must not exceed 5 MB. Please compress oversized images and try again.',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (!$this->hotelDescriptionHasContent($request->input('description'))) {
                $validator->errors()->add('description', 'Hotel description is mandatory.');
            }
        });

        if ($validator->fails()) {
            $redirect = redirect()->back()->withErrors($validator)->withInput();
            if ($validator->errors()->has('description')) {
                $redirect->with('focus_field', 'description');
            }

            return $redirect;
        }

        try {
        $hotel = Hotel::where('hotel_unique_id', $id)->first();
        if (!$hotel) {
            return redirect()->route('hotels.index')->with('error', 'Hotel not found.');
        }
        
        // Handle master image
        $storage_file = $hotel->main_image;
        
        // Check if master image is removed
        if ($request->filled('removed_master_image')) {
            $removedMasterImage = $request->input('removed_master_image');
            // Delete from Azure blob storage
            if ($storage_file) {
                CommonHelper::deleteAzureImage($storage_file);
            }
            $storage_file = null; // Set to null when removed
        }
        
        // Handle new master image upload
        if ($request->hasFile('master_image')) {
            // Delete old main image from Azure before uploading new one
            if ($hotel->main_image) {
                CommonHelper::deleteAzureImage($hotel->main_image);
            }
            
            $image = $request->file('master_image');
            $storage_file = CommonHelper::image_path('file_storage', $image);
            if (empty($storage_file['master_value'] ?? null) && empty($storage_file)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['master_image' => 'Unable to upload master image. Please try a smaller JPEG/PNG file under 5 MB.'])
                    ->with('focus_field', 'master_image');
            }
        }

        // Handle additional images with better error handling
        $imagePaths = []; 
        if ($request->hasFile('all_images')) {
            $uploadedCount = 0;
            $maxImages = 10; // Limit to prevent overwhelming the server
            
            foreach ($request->file('all_images') as $image) {
                if ($uploadedCount >= $maxImages) {
                    Log::warning("Maximum image limit reached, skipping remaining images");
                    break;
                }
                
                try {
                    // Validate image size
                    if ($image->getSize() > 5 * 1024 * 1024) { // 5MB limit per image
                        Log::warning("Image too large, skipping: " . $image->getClientOriginalName());
                        continue;
                    }
                    
                    $pathData = CommonHelper::image_path('file_storage', $image);
                    if (!empty($pathData['master_value'])) {
                        $imagePaths[] = $pathData['master_value'];
                        $uploadedCount++;
                    }
                } catch (\Exception $e) {
                    Log::error("Error uploading image: " . $e->getMessage());
                    // Continue with other images instead of failing completely
                    continue;
                }
            }
            
            Log::info("Successfully uploaded {$uploadedCount} additional images");
        }
        
        // Get existing images and filter out removed ones
        $existingImages = $hotel->images ? json_decode($hotel->images, true) : [];
        
        // Handle removed images
        if ($request->filled('removed_images')) {
            $removedImages = explode(',', $request->input('removed_images'));
            $removedImages = array_filter($removedImages); // Remove empty values
            
            // Delete from Azure and filter out from existing images
            foreach ($removedImages as $removedImage) {
                // Remove from existing images array
                $existingImages = array_filter($existingImages, function($img) use ($removedImage) {
                    return $img !== $removedImage;
                });
                
                // Delete from Azure blob storage
                CommonHelper::deleteAzureImage($removedImage);
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
            'main_image' => is_array($storage_file) ? ($storage_file['master_value'] ?? $hotel->main_image) : $storage_file,
            'check_in_time' => $request->input('check_in_time'),
            'check_out_time' => $request->input('check_out_time'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'description' => $request->input('description'),
            'extra_bed_age_limit' => $request->input('extra_bed_age_limit'),
            'auto_cancel_date' => $request->input('auto_cancel_date') ?? 0,
            'is_active' => $request->input('hotel_status') == 1 ? 1 : 0,
            'images' => json_encode($img_path),
            'duration' => $request->input('duration'),
            'chain_hotel_name' => $request->input('chain_name'),

            'accomodation_type' => $request->input('hotel_category'),
            'ownership_type' => $request->input('hotel_ownership'),
            'hotel_segment' => $request->input('hotel_segment'),
            'hotel_star_rating' => $request->input('hotel_star_rating'),
        ]);

        return redirect()->route('hotels.contact', ['hotel' => $hotel->hotel_unique_id])->with('success', 'Hotel updated successfully');
        } catch (\Throwable $e) {
            Log::error('Hotel Update Failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'hotel_id' => $id,
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Unable to update the hotel. Please verify all mandatory fields and image sizes (max 5 MB each), then try again.');
        }
    }

    /*
    * Soft Delete Hotels.
    * Date 05-11-2024
    */
    public function destroy($id)
    {
        // if (!hasPermission('delete hotel')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        
        // Get hotel and check DMC assignments
        $hotel = Hotel::where('hotel_unique_id', $id)->first();
        
        if(!$hotel) {
            return redirect()->route('hotels.index')
                ->with('error', 'Hotel not found.');
        }
        
        // Check if hotel has any DMC assignments
        $dmcIds = $hotel->getSelectedDmcIds();
        
        if(!empty($dmcIds) && count($dmcIds) > 0) {
            // Hotel has DMC assignments, prevent deletion
            return redirect()->route('hotels.index')
                ->with('error', 'Cannot delete this hotel! It is currently assigned to ' . count($dmcIds) . ' DMC(s). Please remove all DMC assignments before deleting.');
        }
        
        // Hotel has no DMC assignments (empty array), proceed with deletion
        // Delete main image
        if($hotel->main_image) {
            CommonHelper::deleteAzureImage($hotel->main_image);
        }
        
        // Delete additional images
        if($hotel->images) {
            $images = json_decode($hotel->images, true);
            if(is_array($images)) {
                foreach($images as $image) {
                    CommonHelper::deleteAzureImage($image);
                }
            }
        }
        
        // Delete hotel and related rooms
        Hotel::where('hotel_unique_id', $id)->delete();
        Room::where('hotel_id', $id)->delete();
        
        return redirect()->route('hotels.index')
            ->with('success', 'Hotel deleted successfully');
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
    
            return redirect()->route('hotelp', ['id' => $hotel->hotel_unique_id])
            ->with('success', 'Hotel contacts updated successfully.');
        }
        catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('HotelController::updatecontacts failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', $this->hotelUserFacingError($e, 'Unable to update the hotel contacts. Please check your details and try again.'));
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
    
        if (in_array($auth_user->role_id, [1, 20])) {
            // Admin: Show all rooms
            $roomQuery = Room::with('hotel')
                ->selectRaw('hotel_id, MAX(room_id) as room_id, MAX(room_type) as room_type, MAX(no_of_room) as no_of_room, MAX(status) as status')
                ->groupBy('hotel_id');
            $rooms = $roomQuery->get();
        } else {
            // DMC/Other users: Show rooms based on their hotel access
            $roomQuery = Room::with('hotel')
                ->selectRaw('hotel_id, MAX(room_id) as room_id, MAX(room_type) as room_type, MAX(no_of_room) as no_of_room, MAX(status) as status')
                ->whereHas('hotel', function ($query) use ($auth_user) {
                    $query->whereJsonContains('dmc_id', $auth_user->userId);
                })
                ->where(function ($query) use ($auth_user) {
                    // Show either admin base rooms OR DMC's own rooms
                    $query->where('dmc_base_room', 1)
                          ->orWhere('created_by', $auth_user->userId);
                })
                ->groupBy('hotel_id');
            $rooms = $roomQuery->get();
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
        
        if ($auth_user->user_type == 1) {
            $hotels = Hotel::get();
        } else {
            $dmcForHotels = $this->resolveRoomPricingDmcUserId($auth_user);
            $hotels = $dmcForHotels
                ? Hotel::whereJsonContains('dmc_id', $dmcForHotels)->get()
                : Hotel::whereJsonContains('dmc_id', $auth_user->userId)->get();
            if ($auth_user->user_type == 2 || $this->canManageRoomPricingImport($auth_user)) {
                [$commission_type, $commission_price] = $this->resolveDmcMarkupForPricing($auth_user);
            }
        }

        $effective_room_owner_id = $auth_user->userId;
        if (! in_array((int) $auth_user->role_id, [1, 20], true)) {
            $effective_room_owner_id = $this->resolveRoomPricingDmcUserId($auth_user) ?? $auth_user->userId;
        }
        $show_dmc_room_pricing_hints = (int) $auth_user->user_type === 2 || $this->canManageRoomPricingImport($auth_user);
        
        $roomtypes = RoomType::where('status', 1)->get();
        
        // Get DMC users for admin dropdown (only for admin users)
        $dmcUsers = collect();
        if ($auth_user->role_id == 1) {
            $dmcUsers = User::whereIn('role_id', [11,20])
                           ->where('user_type', 2)
                           ->select('userId', 'name', 'company_name', 'currency')
                           ->orderBy('company_name', 'asc')
                           ->get();
        }
        
        // Show different rooms based on user role
        if (in_array($auth_user->role_id, [1, 20])) {
            if ($auth_user->role_id == 1) {
                // Admin: Show all rooms for this hotel (both admin base rooms and DMC rooms)
                $rooms = Room::with(['beds', 'hotel'])
                            ->where('hotel_id', $hotel_unique_id)
                            ->orderBy('dmc_base_room', 'desc') // Admin base rooms first
                            ->orderBy('room_type', 'asc')
                            ->get();
                
                // Add DMC information to each room
                $rooms = $rooms->map(function ($room) {
                    if ($room->created_by && $room->dmc_base_room == 0) {
                        $dmcUser = User::where('userId', $room->created_by)->first();
                        if ($dmcUser) {
                            $room->dmc_name = $dmcUser->name;
                            $room->dmc_company = $dmcUser->company_name;
                            $room->dmc_id = $dmcUser->userId;
                        }
                    } else {
                        $room->dmc_name = 'Admin';
                        $room->dmc_company = 'Admin Base Room';
                        $room->dmc_id = 'admin';
                    }
                    return $room;
                });
            } else {
                // Virtual DMC: Show admin base rooms only
                $rooms = Room::with('beds')->where('dmc_base_room', 1)->where('hotel_id', $hotel_unique_id)->get();
            }
        } else {
            // DMC and delegated roles: show parent DMC's room copies + admin base rooms
            $dmcRooms = Room::with('beds')
                          ->where('hotel_id', $hotel_unique_id)
                          ->where('created_by', $effective_room_owner_id)
                          ->where('dmc_base_room', 0)
                          ->get();
            
            $baseRooms = Room::with('beds')
                           ->where('dmc_base_room', 1)
                           ->where('hotel_id', $hotel_unique_id)
                           ->get();
            
            // For each base room, check if DMC has their own version
            $rooms = collect();
            foreach ($baseRooms as $baseRoom) {
                $dmcRoom = $dmcRooms->firstWhere('room_type', $baseRoom->room_type);
                if ($dmcRoom) {
                    // Use DMC's version if exists
                    $rooms->push($dmcRoom);
                } else {
                    // Use base room if DMC doesn't have their own
                    $rooms->push($baseRoom);
                }
            }
        }
        
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
            'auth_user',
            'dmcUsers',
            'effective_room_owner_id',
            'show_dmc_room_pricing_hints'
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
        $auth_user = Auth::user();
        
        $dmcUsers = collect();
        if ($auth_user->role_id == 1) {
            $dmcUsers = User::whereIn('role_id', [11,20])
                           ->where('user_type', 2)
                           ->select('userId', 'name', 'company_name', 'currency')
                           ->orderBy('company_name', 'asc')
                           ->get();
        }
        
        $rates = $this->fetchHotelRatesForUser($auth_user, $hotelId, 'non_season');
        $canManageHotelRates = $this->userCanManageHotelRates($auth_user);
        
        return view('hotel.rates', compact('hotel','rates','auth_user','dmcUsers', 'canManageHotelRates'));
    }

    public function hotelseason($hotelId){
        $hotel = Hotel::where('hotel_unique_id', $hotelId)->first();
        $room = Room::where('hotel_id', $hotelId)->get()->first();
        $auth_user = Auth::user();
        
        $dmcUsers = collect();
        if ($auth_user->role_id == 1) {
            $dmcUsers = User::whereIn('role_id', [11,20])
                           ->where('user_type', 2)
                           ->select('userId', 'name', 'company_name', 'currency')
                           ->orderBy('company_name', 'asc')
                           ->get();
        }
        
        $rates = $this->fetchHotelRatesForUser($auth_user, $hotelId, 'Season');
        $canManageHotelRates = $this->userCanManageHotelRates($auth_user);
        
        return view('hotel.season', compact('hotel','room','rates','auth_user','dmcUsers', 'canManageHotelRates'));
    }


    /**
     * Map exceptions to a short user-facing message (never expose SQL/stack traces).
     */
    private function hotelUserFacingError(\Throwable $e, string $fallback): string
    {
        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return 'The requested record was not found.';
        }

        if ($e instanceof \Illuminate\Database\QueryException) {
            $sqlState = $e->errorInfo[0] ?? null;
            $driverCode = isset($e->errorInfo[1]) ? (int) $e->errorInfo[1] : null;
            if ($driverCode === 1062 || $sqlState === '23000') {
                return 'A duplicate record already exists. Please check your details.';
            }
            return $fallback;
        }

        $message = strtolower($e->getMessage());
        if (
            str_contains($message, 'upload')
            || str_contains($message, 'failed to open stream')
            || str_contains($message, 'unable to write')
            || str_contains($message, 'file could not be uploaded')
        ) {
            return 'File upload failed. Please try again with a different file.';
        }

        // Friendly messages rethrown from private helpers
        if ($e instanceof \RuntimeException && $e->getPrevious() !== null) {
            $friendly = $e->getMessage();
            if ($friendly !== '' && !str_contains($friendly, 'SQLSTATE') && !str_contains($friendly, 'Stack trace')) {
                return $friendly;
            }
        }

        return $fallback;
    }

    /*
    * Store new Rooms.
    * Date 15-11-2024
    */
    public function storeroom(Request $request)
    {
        try {
            $auth_user = Auth::user();
        
            // Check if user is admin or virtual DMC - only they can create rooms
            if (!in_array($auth_user->role_id, [1, 20, 10])) {
                return redirect()->back()->with('error', 'Only administrators and virtual DMCs can create rooms.');
            }
        
            $this->sanitizeRoomGalleryUploads($request);
            $this->sanitizeRoomMasterUpload($request);

            $request->validate([
                'room_type' => 'nullable',
                'total_no_of_room' => 'nullable|integer',
                'singleWeekdayPrice' => 'nullable|numeric',
                'singleWeekendPrice' => 'nullable|numeric',
                'doubleWeekdayPrice' => 'nullable|numeric',
                'doubleWeekendPrice' => 'nullable|numeric',
                'children_price' => 'nullable|numeric|min:0',
                'master_image' => $request->hasFile('master_image')
                    ? ['required', 'file', 'max:5120']
                    : ['required'],
                'all_images' => 'nullable|array',
                'all_images.*' => 'nullable|image|max:5120',
                'child_with_bed' => 'nullable|numeric|min:0',
                'child_without_bed' => 'nullable|numeric|min:0',
                'child_with_bed_cost' => 'nullable|numeric|min:0',
                'child_without_bed_cost' => 'nullable|numeric|min:0',
                'singleWeekdayCostPrice' => 'nullable|numeric|min:0',
                'singleWeekendCostPrice' => 'nullable|numeric|min:0',
                'doubleWeekdayCostPrice' => 'nullable|numeric|min:0',
                'doubleWeekendCostPrice' => 'nullable|numeric|min:0',
                'breakfast_cost_price' => 'nullable|numeric|min:0',
                'lunch_cost_price' => 'nullable|numeric|min:0',
                'dinner_cost_price' => 'nullable|numeric|min:0',
            ], [
                'master_image.required' => 'Please upload a master image.',
                'master_image.file' => 'Please upload a master image.',
                'master_image.max' => 'Master image must not exceed 5 MB.',
            ]);

            if ($request->hasFile('master_image') && !$this->isUploadedImageFile($request->file('master_image'))) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['master_image' => 'Master image must be a JPEG, PNG, WEBP or GIF.'])
                    ->with('error', 'Master image must be a JPEG, PNG, WEBP or GIF.');
            }
        
            $admin_base_room = 1; // Admin creates base rooms
            $lastRoom = Room::withTrashed()->orderBy('id', 'desc')->first();
            // $room_max_id = $lastRoom->room_id ?? 0;
            // $roomId = CommonHelper::createId($room_max_id);
            // while (Room::where('room_id', $roomId)->exists()) {
            //     $roomId = CommonHelper::createId($roomId);
            // }
            // Handle image paths. The create form has two file inputs: the visible drop
            // zone posts as images[] and JS copies selections onto hidden all_images[].
            $imagePaths = $this->storeUploadedRoomGallery($request);
            $imagePathsJson = json_encode($imagePaths);

            $master_image = '';
            if ($request->hasFile('master_image')) {
                $masterImagePath = CommonHelper::image_path('file_storage', $request->file('master_image'));
                if (!empty($masterImagePath['master_value'])) {
                    $master_image = $masterImagePath['master_value'];
                }
            }

            if ($master_image === '') {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['master_image' => 'Unable to upload master image. Please try a smaller JPEG/PNG file and try again.']);
            }
        
            // Check if admin has any base room for this hotel
            $adminBaseRoom = Room::where('hotel_id', $request->hotel_id)
                                ->where('dmc_base_room', 1)
                                ->where('base_room', true)
                                ->first();
        
            // Check if this is a Standard room or first room
            $isStandardRoom = ($request->room_type === 'Standard' || $request->base_room_type === 'Standard');
            $isFirstRoom = !$adminBaseRoom;
        
            // Determine if this should be the admin's base room
            $isBaseRoom = ($isFirstRoom || $isStandardRoom) && !$adminBaseRoom;
        
            // Calculate final prices
            $weekdayPrice = $request->baseSingleWeekdayPrice ?? $request->singleWeekdayPrice ?? 0;
            $weekendPrice = $request->baseSingleWeekendPrice ?? $request->singleWeekendPrice ?? 0;
            $doubleWeekdayPrice = $request->baseDoubleWeekdayPrice ?? $request->doubleWeekdayPrice ?? 0;
            $doubleWeekendPrice = $request->baseDoubleWeekendPrice ?? $request->doubleWeekendPrice ?? 0;
            $weekdayCostPrice = $request->baseSingleWeekdayCostPrice ?? $request->singleWeekdayCostPrice ?? null;
            $weekendCostPrice = $request->baseSingleWeekendCostPrice ?? $request->singleWeekendCostPrice ?? null;
            $doubleWeekdayCostPrice = $request->baseDoubleWeekdayCostPrice ?? $request->doubleWeekdayCostPrice ?? null;
            $doubleWeekendCostPrice = $request->baseDoubleWeekendCostPrice ?? $request->doubleWeekendCostPrice ?? null;
        
            // If this is not a base room and admin has a base room, add variant to admin base prices
            $varientPrice = $request->varient_price ?? 0;
            if (!$isBaseRoom && $adminBaseRoom && $varientPrice > 0) {
                $weekdayPrice = $adminBaseRoom->weekday_price + $varientPrice;
                $weekendPrice = $adminBaseRoom->weekend_price + $varientPrice;
                $doubleWeekdayPrice = $adminBaseRoom->double_weekday_price + $varientPrice;
                $doubleWeekendPrice = $adminBaseRoom->double_weekend_price + $varientPrice;
            }
        
            // Create and save the room
            $room = new Room();
            $room->hotel_id = $request->hotel_id;
            $room->room_type = $request->room_type ? $request->room_type : $request->base_room_type;
            $room->no_of_room = $request->total_no_of_room;
            $room->weekday_price = $weekdayPrice;
            $room->weekend_price = $weekendPrice;
            $room->double_weekday_price = $doubleWeekdayPrice;
            $room->double_weekend_price = $doubleWeekendPrice;
            $room->weekday_cost_price = $weekdayCostPrice;
            $room->weekend_cost_price = $weekendCostPrice;
            $room->double_weekday_cost_price = $doubleWeekdayCostPrice;
            $room->double_weekend_cost_price = $doubleWeekendCostPrice;
            $room->varient_price = $varientPrice;
            $room->dimension = $request->dimension;
            $room->children_price = $request->children_price;
            $room->status = $request->room_status == 1 ? 1 : 0;
            // $room->room_id = $roomId;
            $room->dmc_base_room = $admin_base_room;
            $room->created_by = $auth_user->userId;
            $room->images = $imagePathsJson;
            $room->master_image = $master_image;
            $room->breakfast_restaurant = $request->breakfast_restaurant;
            $room->base_room = $isBaseRoom;
            $room->breakfast = $request->breakfast_included;
            $room->lunch = $request->lunch_included;
            $room->dinner = $request->dinner_included;
            $room->dinner_type=$request->dinner_type;
            $room->lunch_type=$request->lunch_type;
            $room->breakfast_type=$request->breakfast_type;
            $room->breakfast_price=$request->breakfast_price;
            $room->lunch_price=$request->lunch_price;
            $room->dinner_price=$request->dinner_price;
            $room->breakfast_cost_price = $request->breakfast_included ? $request->breakfast_cost_price : null;
            $room->lunch_cost_price = $request->lunch_included ? $request->lunch_cost_price : null;
            $room->dinner_cost_price = $request->dinner_included ? $request->dinner_cost_price : null;
            $room->breakfast_included=$request->supplementary_breakfast;
            $room->child_with_bed = $request->child_with_bed;
            $room->child_without_bed = $request->child_without_bed;
            $room->child_with_bed_cost = $request->child_with_bed_cost;
            $room->child_without_bed_cost = $request->child_without_bed_cost;
            $is_save = $room->save();
            $room->refresh();
            // if($request->no_of_rooms){
            //     $lastBed = Bed::withTrashed()->orderBy('bed_id', 'desc')->first();
            //     $bed_max_id = $lastBed->bed_id ?? 0;
            //     // $bedId = CommonHelper::createId($bed_max_id);
            //     // while (Bed::where('bed_id', $bedId)->exists()) {
            //     //     $bedId = CommonHelper::createId($bedId);
            //     // }
            // }
            $lastRoomId = Room::latest()->value('room_id');
            // Return response based on room save result
            if ($is_save) {
                $auth_user = Auth::user();
                $roomQuery = Room::with('hotel');
                if ($auth_user->user_type == 1) {
                    $rooms = $roomQuery->get(); // Fetch all rooms for user_type 1
                } else {
                    $rooms = $roomQuery->whereHas('hotel', function ($query) use ($auth_user) {
                    $query->whereJsonContains('dmc_id', $auth_user->userId); // Filter by dmcId for other user types
                    })->get();
                }
                    $currentRooms = Room::where('room_type', 'Standard')->first();
                return redirect()->route('hotels.createroom', ['id' => $request->hotel_id])->with('success', 'Room details saved successfully!');
            } else {
                return redirect()->back()->with('error', 'An error occurred while saving the room details.');
            }
    

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', collect($e->errors())->flatten()->first() ?: 'Please check the room details and try again.');
        } catch (\Throwable $e) {
            \Log::error('HotelController::storeroom failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', $this->hotelUserFacingError($e, 'Unable to save the room. Please check your details and try again.'));
        }
    }

    /**
     * store rates
     */
    public function storerates(Request $request){
        try {
            $auth_user = Auth::user();
        
            // Base validation rules
            $rules = [
                'event' => 'required|string',
                'hotel_id' => 'required',
                'event_type' => 'required|string',
                'price' => 'nullable|numeric',
                'surcharge' => 'nullable|numeric',
                'breakfast_price' => 'nullable|numeric',
                'lunch_price' => 'nullable|numeric',
                'dinner_price' => 'nullable|numeric',
                'date_range' => 'required|string',
                'rate_status' => 'nullable|integer',
            ];
        
            // For admin and role_id 20, DMC selection is required
            if ($auth_user->role_id == 1 || $auth_user->role_id == 20) {
                $rules['dmc_id'] = 'required|exists:users,userId';
            }
        
            $request->validate($rules);
            list($firstDate, $lastDate) = explode(' - ', $request->date_range);
            $firstDate = Carbon::createFromFormat('m/d/Y', $firstDate);
            $lastDate = Carbon::createFromFormat('m/d/Y', $lastDate);
       
            // Generate rate ID
            // $lastRate = Rate::withTrashed()->orderBy('created_at', 'desc')->first();
            // $rate_max_id = $lastRate->rate_id ?? 0;
            // $rateId = CommonHelper::createId($rate_max_id);
            // while (Rate::where('rate_id', $rateId)->exists()) {
            //     $rateId = CommonHelper::createId($rateId);
            // }

            // Set DMC ID based on user role (DMC, product head, multi-role staff use parent DMC via created_by)
            $dmcId = $this->resolveHotelRateDmcIdForStore($auth_user, $request);
            if (!$dmcId) {
                return redirect()->back()->with('error', 'Unable to determine DMC for this rate.');
            }

            $rate = Rate::create([
                'event' => $request->event,
                'hotel_id' => $request->hotel_id,
                // 'rate_id' => $rateId,
                'event_type' => $request->event_type,
                'price' => $request->price ? $request->price : $request->surcharge,
                'weekday_price' => 0.00,
                'weekend_price' => 0.00,
                'breakfast_price' => $request->breakfast_price ?? 0.00,
                'lunch_price' => $request->lunch_price ?? 0.00,
                'dinner_price' => $request->dinner_price ?? 0.00,
                'start_date' => $firstDate,
                'end_date' => $lastDate,
                'dmc_id' => $dmcId, // Set DMC ID based on user role
                'is_active' => $request->rate_status == 1 ? 1 : 0
            ]);
            $is_save = $rate->save();
            $rate->refresh();
            if ($is_save) {
                return redirect()->back()
                    ->with('success', 'Rates details saved successfully!');
            } else {
                return redirect()->back()
                    ->with('error', 'An error occurred while saving the room details.');
            }
    

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::storerates failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', $this->hotelUserFacingError($e, 'Unable to save the rates. Please try again.'));
        }
    }

    /** store season details */

    public function storeseason(Request $request){
        try {
            $auth_user = Auth::user();
        
            // Base validation rules
            $rules = [
                'event' => 'required|string',
                'event_type' => 'required|string',
                'weekday_price' => 'required|numeric|min:0',
                'weekday_cost_price' => 'required|numeric|min:0',
                'weekend_price' => 'required|numeric|min:0',
                'weekend_cost_price' => 'required|numeric|min:0',
                'double_weekday_price' => 'required|numeric|min:0',
                'double_weekday_cost_price' => 'required|numeric|min:0',
                'double_weekend_price' => 'required|numeric|min:0',
                'double_weekend_cost_price' => 'required|numeric|min:0',
                'breakfast_price' => 'nullable|numeric|min:0',
                'breakfast_cost_price' => 'nullable|numeric|min:0',
                'lunch_price' => 'nullable|numeric|min:0',
                'lunch_cost_price' => 'nullable|numeric|min:0',
                'dinner_price' => 'nullable|numeric|min:0',
                'dinner_cost_price' => 'nullable|numeric|min:0',
                'season_status' => 'nullable|integer',
            ];
        
            // For admin and role_id 20, DMC selection is required
            if ($auth_user->role_id == 1 || $auth_user->role_id == 20) {
                $rules['dmc_id'] = 'required|exists:users,userId';
            }
        
            $request->validate($rules);
            // dd($request->all());
            // $lastRate = Rate::orderBy('created_at', 'desc')->first();

            // $rate_max_id = $lastRate->rate_id ?? 0;
            // $rateId = CommonHelper::createId($rate_max_id);
            // while (Rate::where('rate_id', $rateId)->exists()) {
            //     $rateId = CommonHelper::createId($rateId);
            // }

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

            // Set DMC ID based on user role (DMC, product head, multi-role staff use parent DMC via created_by)
            $dmcId = $this->resolveHotelRateDmcIdForStore($auth_user, $request);
            if (!$dmcId) {
                return redirect()->back()->with('error', 'Unable to determine DMC for this season.');
            }

            $rate = Rate::create([
                'event' => $request->event, 
                'hotel_id' => $request->hotel_id,
                // 'rate_id' => $rateId,
                'event_type' => $request->event_type,
                'price' => 0,
                'weekday_price' => $request->weekday_price,
                'weekday_cost_price' => $request->weekday_cost_price,
                'weekend_price' => $request->weekend_price,
                'weekend_cost_price' => $request->weekend_cost_price,
                'double_weekday_price' => $request->double_weekday_price,
                'double_weekday_cost_price' => $request->double_weekday_cost_price,
                'double_weekend_price' => $request->double_weekend_price,
                'double_weekend_cost_price' => $request->double_weekend_cost_price,
                'breakfast_price' => $request->breakfast_price ?? 0.00,
                'breakfast_cost_price' => $request->breakfast_cost_price,
                'lunch_price' => $request->lunch_price ?? 0.00,
                'lunch_cost_price' => $request->lunch_cost_price,
                'dinner_price' => $request->dinner_price ?? 0.00,
                'dinner_cost_price' => $request->dinner_cost_price,
                'start_date' => $firstDate,
                'end_date' => $lastDate,
                'dmc_id' => $dmcId, // Set DMC ID based on user role
                'is_active' => $request->season_status == 1 ? 1 : 0
            ]);

            $is_save = $rate->save();
            $rate->refresh();
            if ($is_save) {
                // LogActivityService::log('create_rate', 'App\Models\Rate', $rate->rate_id, $rate);
                return redirect()->back()
                    ->with('success', 'Rates details saved successfully!');
            } else {
                // LogActivityService::log('create_rate_failed', 'App\Models\Rate', $rate_max_id,'An error occurred while saving the room details.');
                return redirect()->back()
                    ->with('error', 'An error occurred while saving the room details.');
            }
    

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::storeseason failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', $this->hotelUserFacingError($e, 'Unable to save the season. Please try again.'));
        }
    }

    /*
    * Edit Rate Details .
    * Date 15-12-2024
    */
    public function editrate($id, $hotelId){
        try {
            $rate = Rate::where('rate_id', $id)->where('hotel_id', $hotelId)->first();
            $this->authorizeHotelRateForUser($rate, Auth::user());
            $hotel = Hotel::where('hotel_unique_id', $hotelId)->first();
            return view('hotel.edit-rate', compact('rate','hotel'));
    

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::editrate failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', $this->hotelUserFacingError($e, 'Unable to open this page right now.'));
        }
    }

    /*
    * Edit Rate Details .
    * Date 15-12-2024
    */
    public function editseason($id, $hotelId){
        try {
            $rate = Rate::where('rate_id', $id)->where('hotel_id', $hotelId)->first();
            $this->authorizeHotelRateForUser($rate, Auth::user());
            $hotel = Hotel::where('hotel_unique_id', $hotelId)->first();
            return view('hotel.edit-season', compact('rate','hotel'));
    

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::editseason failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', $this->hotelUserFacingError($e, 'Unable to open this page right now.'));
        }
    }

    /*
    * Edit Rates .
    * Date 18-11-2024
    */
    public function updaterates(Request $request){
        try {
            $auth_user = Auth::user();
            // Get DMC users for admin dropdown (only for admin users)
            $dmcUsers = collect();
            if ($auth_user->role_id == 1 || $auth_user->role_id == 20) {
                $dmcUsers = User::whereIn('role_id', [11,20])
                               ->where('user_type', 2)
                               ->select('userId', 'name', 'company_name', 'currency')
                               ->orderBy('company_name', 'asc')
                               ->get();
            }
            $rate_id = $request->rate_id;
            $hotel = Hotel::where('hotel_unique_id', $request->hotel_id)->first();
            $rate = Rate::where('rate_id', $rate_id)->where('hotel_id', $request->hotel_id)->first();
            if (!$rate) {
                return redirect()->back()->with('error', 'Rate not found for the specified hotel.');
            }
            $this->authorizeHotelRateForUser($rate, $auth_user);
        
            // Parse date range
            list($firstDate, $lastDate) = explode(' - ', $request->date_range);
            $firstDate = Carbon::createFromFormat('m/d/Y', $firstDate);
            $lastDate = Carbon::createFromFormat('m/d/Y', $lastDate);
        
            $rate->event = $request->event;
            $rate->event_type = $request->event_type;
            $rate->price = $request->price;
            $rate->weekday_price = $request->weekday_price ? $request->weekday_price : 0.00;
            $rate->weekend_price = $request->weekend_price ? $request->weekend_price : 0.00;
            $rate->breakfast_price = $request->breakfast_price ?? 0.00;
            $rate->lunch_price = $request->lunch_price ?? 0.00;
            $rate->dinner_price = $request->dinner_price ?? 0.00;
            $rate->start_date = $firstDate;
            $rate->end_date = $lastDate;
            $rate->is_active = $request->rate_status == 1 ? 1 : 0;

            if ($rate->save()) {
                $rates = $this->fetchHotelRatesForUser($auth_user, $request->hotel_id, 'non_season');
                $canManageHotelRates = $this->userCanManageHotelRates($auth_user);
                return view('hotel.rates', compact('hotel', 'rates', 'dmcUsers', 'auth_user', 'canManageHotelRates'))
                    ->with('success', 'Rates details saved successfully!');
            } else {
                return redirect()->back()
                    ->with('error', 'An error occurred while saving the rate details.');
            }
    

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::updaterates failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', $this->hotelUserFacingError($e, 'Unable to update the rates. Please try again.'));
        }
    }

    /*
    * Edit Seasons .
    * Date 18-11-2024
    */
    public function updateseason(Request $request)
    {
        try {
            $auth_user = Auth::user();
            // Validate the request data
            $request->validate([
                'rate_id' => 'required|exists:rates,rate_id',
                'hotel_id' => 'required|exists:hotels,hotel_unique_id',
                'event' => 'nullable|string|max:255',
                'event_type' => 'nullable|string|max:255',
                'weekday_price' => 'required|numeric|min:0',
                'weekday_cost_price' => 'required|numeric|min:0',
                'weekend_price' => 'required|numeric|min:0',
                'weekend_cost_price' => 'required|numeric|min:0',
                'double_weekday_price' => 'required|numeric|min:0',
                'double_weekday_cost_price' => 'required|numeric|min:0',
                'double_weekend_price' => 'required|numeric|min:0',
                'double_weekend_cost_price' => 'required|numeric|min:0',
                'breakfast_price' => 'nullable|numeric|min:0',
                'breakfast_cost_price' => 'nullable|numeric|min:0',
                'lunch_price' => 'nullable|numeric|min:0',
                'lunch_cost_price' => 'nullable|numeric|min:0',
                'dinner_price' => 'nullable|numeric|min:0',
                'dinner_cost_price' => 'nullable|numeric|min:0',
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
            $this->authorizeHotelRateForUser($rate, $auth_user);
            $rate->event = $request->event;
            $rate->event_type = $request->event_type;
            $rate->price = 0; // Why is this always 0? Confirm if intentional.
            $rate->weekday_price = $request->weekday_price;
            $rate->weekday_cost_price = $request->weekday_cost_price;
            $rate->weekend_price = $request->weekend_price;
            $rate->weekend_cost_price = $request->weekend_cost_price;
            $rate->double_weekday_price = $request->double_weekday_price;
            $rate->double_weekday_cost_price = $request->double_weekday_cost_price;
            $rate->double_weekend_price = $request->double_weekend_price;
            $rate->double_weekend_cost_price = $request->double_weekend_cost_price;
            $rate->breakfast_price = $request->breakfast_price ?? 0.00;
            $rate->breakfast_cost_price = $request->breakfast_cost_price;
            $rate->lunch_price = $request->lunch_price ?? 0.00;
            $rate->lunch_cost_price = $request->lunch_cost_price;
            $rate->dinner_price = $request->dinner_price ?? 0.00;
            $rate->dinner_cost_price = $request->dinner_cost_price;
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
    

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::updateseason failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', $this->hotelUserFacingError($e, 'Unable to update the season. Please try again.'));
        }
    }

    public function deleteSeason($hotelId, $id){
        try {
            $rate = Rate::where('rate_id', $id)->where('hotel_id', $hotelId)->first();
            $this->authorizeHotelRateForUser($rate, Auth::user());
            $delete = $rate ? $rate->delete() : false;
            if ($delete){
                return redirect()->route('hotels.season', ['hotel' => $hotelId])
                    ->with('error', 'Season details deleted successfully!');
            } else {
                return redirect()->back()
                    ->with('error', 'An error occurred while updating the room details.');
            }
    

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::deleteSeason failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', $this->hotelUserFacingError($e, 'Unable to delete the season. Please try again.'));
        }
    }

    /*
    * Edit Room Details .
    * Date 18-11-2024
    */
    public function editroom(Request $request, $id)
    {
        try {
            $auth_user = Auth::user();
            $originalRoom = Room::where('room_id', Crypt::decrypt($id))->first();

            if (!$originalRoom) {
                return redirect()->back()->with('error', 'Room not found.');
            }

            // Define default values to prevent "undefined variable" error
            $single_weekday_price = 0;
            $double_weekday_price = 0;
            $single_weekend_price = 0;
            $double_weekend_price = 0;
            $commission_type = null;
            $commission_price = null;

            // Determine which room to edit based on user role
            if (in_array($auth_user->role_id, [1, 20])) {
                // Admin: Edit the original room
                $room = $originalRoom;
                $hotel = Hotel::get();
                $baseRoom = Room::where('hotel_id', $room->hotel_id)
                               ->where('dmc_base_room', 1)
                               ->where('varient_price', '0')
                               ->first();
            } else {
                $dmcOwnerId = $this->resolveRoomPricingDmcUserId($auth_user) ?? $auth_user->userId;

                // DMC / delegated roles: rooms are owned by parent DMC (created_by)
                $dmcRoom = Room::where('hotel_id', $originalRoom->hotel_id)
                              ->where('room_type', $originalRoom->room_type)
                              ->where('created_by', $dmcOwnerId)
                              ->where('dmc_base_room', 0)
                              ->first();

                if ($dmcRoom) {
                    $room = $dmcRoom;
                } else {
                    $room = $originalRoom;
                }

                $baseRoom = Room::where('hotel_id', $originalRoom->hotel_id)
                               ->where('created_by', $dmcOwnerId)
                               ->where('base_room', true)
                               ->where('dmc_base_room', 0)
                               ->first();

                if (!$baseRoom) {
                    $baseRoom = Room::where('hotel_id', $originalRoom->hotel_id)
                                   ->where('dmc_base_room', 1)
                                   ->where('varient_price', '0')
                                   ->first();
                }

                $hotel = Hotel::whereJsonContains('dmc_id', $dmcOwnerId)->get();

                if ($auth_user->user_type == 2 || $this->canManageRoomPricingImport($auth_user)) {
                    $dmcPricingUser = User::where('userId', $dmcOwnerId)->first() ?? $auth_user;

                    if ((int) $dmcPricingUser->is_master_dmc === 1) {
                        $commission_type = $dmcPricingUser->markup_type;
                        $commission_price = $dmcPricingUser->markup_price;
                    } else {
                        $master_id = User::where('master_dmc_id', $dmcPricingUser->master_dmc_id)->first();
                        $commission_type = $master_id->markup_type ?? 0;
                        $commission_price = $master_id->markup_price ?? 0;
                    }

                    if ($commission_type == 0) {
                        $single_weekday_price = $dmcPricingUser->markup_price + $room->weekday_price;
                        $double_weekday_price = $dmcPricingUser->markup_price + $room->double_weekday_price;
                        $single_weekend_price = $dmcPricingUser->markup_price + $room->weekend_price;
                        $double_weekend_price = $dmcPricingUser->markup_price + $room->double_weekend_price;
                    } else {
                        $single_weekday_price = $room->weekday_price + ($dmcPricingUser->markup_price * $room->weekday_price) / 100;
                        $double_weekday_price = $room->double_weekday_price + ($dmcPricingUser->markup_price * $room->double_weekday_price) / 100;
                        $single_weekend_price = $room->weekend_price + ($dmcPricingUser->markup_price * $room->weekend_price) / 100;
                        $double_weekend_price = $room->double_weekend_price + ($dmcPricingUser->markup_price * $room->double_weekend_price) / 100;
                    }
                }
            }

            return view('hotel.editroom', compact(
                'hotel',
                'single_weekday_price',
                'double_weekday_price',
                'single_weekend_price',
                'double_weekend_price',
                'room',
                'baseRoom',
                'auth_user',
                'commission_type',
                'commission_price'
            ));
    

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::editroom failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', $this->hotelUserFacingError($e, 'Unable to open this page right now.'));
        }
    }

    /*
    * Update Room Details .
    * Date 18-11-2024
    */
    public function updateroom(Request $request)
    {
        try {

            $this->sanitizeRoomGalleryUploads($request);
            $this->sanitizeRoomMasterUpload($request);

            $request->validate([
                'no_of_room' => 'nullable|numeric',
                'total_no_of_room' => 'nullable|numeric',
                'single_weekday_price' => 'nullable|numeric',
                'single_weekend_price' => 'nullable|numeric',
                'double_weekday_price' => 'nullable|numeric',
                'double_weekend_price' => 'nullable|numeric',
                'children_price' => 'nullable|numeric|min:0',
                'master_image' => $request->hasFile('master_image')
                    ? ['nullable', 'file', 'max:5120']
                    : 'nullable',
                'all_images' => 'nullable|array',
                'all_images.*' => 'nullable|image|max:5120',
                'child_with_bed' => 'nullable|numeric|min:0',
                'child_without_bed' => 'nullable|numeric|min:0',
                'child_with_bed_cost' => 'nullable|numeric|min:0',
                'child_without_bed_cost' => 'nullable|numeric|min:0',
                'singleWeekdayCostPrice' => 'nullable|numeric|min:0',
                'singleWeekendCostPrice' => 'nullable|numeric|min:0',
                'doubleWeekdayCostPrice' => 'nullable|numeric|min:0',
                'doubleWeekendCostPrice' => 'nullable|numeric|min:0',
                'breakfast_cost_price' => 'nullable|numeric|min:0',
                'lunch_cost_price' => 'nullable|numeric|min:0',
                'dinner_cost_price' => 'nullable|numeric|min:0',
            ], [
                'master_image.file' => 'Please upload a master image.',
                'master_image.max' => 'Master image must not exceed 5 MB.',
            ]);

            if ($request->hasFile('master_image') && !$this->isUploadedImageFile($request->file('master_image'))) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['master_image' => 'Master image must be a JPEG, PNG, WEBP or GIF.'])
                    ->with('error', 'Master image must be a JPEG, PNG, WEBP or GIF.');
            }

            $auth_user = Auth::user();
            $originalRoom = Room::where('room_id', $request->room_id)->first();

            if (!$originalRoom) {
                return redirect()->back()->withErrors('Room not found or invalid room ID.');
            }

            \Log::info("Room update started", [
                'room_id' => $request->room_id,
                'user_role' => $auth_user->role_id,
                'hotel_id' => $request->hotel_id
            ]);

            // Check if user is admin (role_id 1 or 20)
            if (in_array($auth_user->role_id, [1, 20])) {
                // Admin: Update the original room
                \Log::info("Admin updating original room");
                $this->updateExistingRoom($request, $originalRoom);
            } else {
                $dmcOwnerId = $this->resolveRoomPricingDmcUserId($auth_user) ?? $auth_user->userId;

                $dmcRoom = Room::where('hotel_id', $request->hotel_id)
                              ->where('room_type', $originalRoom->room_type)
                              ->where('created_by', $dmcOwnerId)
                              ->where('dmc_base_room', 0)
                              ->first();

                if ($dmcRoom) {
                    \Log::info("DMC updating existing room", ['dmc_room_id' => $dmcRoom->room_id]);
                    $this->updateExistingRoom($request, $dmcRoom);
                } else {
                    \Log::info("DMC creating new room");
                    $this->createDmcRoom($request, $originalRoom, $auth_user, $dmcOwnerId);
                }
            }

            \Log::info("Room update completed successfully");
            return redirect()->route('hotels.createroom', ['id' => $request->hotel_id])->with('success', 'Room updated successfully.');
        
        

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::updateroom failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', $this->hotelUserFacingError($e, 'Unable to update the room. Please try again.'));
        }
    }

    /**
     * Drop empty/invalid gallery file slots before validation.
     * An unused all_images[] input still posts a blank UploadedFile, which fails the image rule.
     */
    private function sanitizeRoomGalleryUploads(Request $request): void
    {
        foreach (['all_images', 'images'] as $key) {
            $files = $request->file($key);
            if ($files === null) {
                $request->files->remove($key);
                continue;
            }

            $list = is_array($files) ? $files : [$files];
            $valid = array_values(array_filter($list, function ($file) {
                return $file instanceof \Illuminate\Http\UploadedFile
                    && $file->isValid()
                    && $file->getSize() > 0;
            }));

            if ($valid === []) {
                $request->files->remove($key);
            } else {
                $request->files->set($key, $valid);
            }
        }
    }

    private function sanitizeRoomMasterUpload(Request $request): void
    {
        $file = $request->file('master_image');
        if ($file === null) {
            return;
        }

        if (is_array($file)) {
            $valid = null;
            foreach ($file as $item) {
                if (
                    $item instanceof \Illuminate\Http\UploadedFile
                    && $item->isValid()
                    && $item->getSize() > 0
                ) {
                    $valid = $item;
                    break;
                }
            }
            if ($valid) {
                $request->files->set('master_image', $valid);
            } else {
                $request->files->remove('master_image');
            }
            return;
        }

        if (
            !($file instanceof \Illuminate\Http\UploadedFile)
            || !$file->isValid()
            || $file->getSize() <= 0
        ) {
            $request->files->remove('master_image');
        }
    }

    private function isUploadedImageFile(\Illuminate\Http\UploadedFile $file): bool
    {
        $mime = strtolower((string) $file->getMimeType());
        if (str_starts_with($mime, 'image/')) {
            return true;
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());

        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'], true);
    }

    /**
     * Persist additional room images from either all_images[] or images[].
     * The create/edit forms use a visible drop zone named images[] and a hidden
     * all_images[] that JS copies selections onto; we accept whichever arrives.
     *
     * @return array<int, string>
     */
    private function storeUploadedRoomGallery(Request $request): array
    {
        $galleryFiles = [];
        if ($request->hasFile('all_images')) {
            $galleryFiles = (array) $request->file('all_images');
        } elseif ($request->hasFile('images')) {
            $galleryFiles = (array) $request->file('images');
        }

        $imagePaths = [];
        foreach ($galleryFiles as $image) {
            if (!$image || !$image->isValid()) {
                continue;
            }

            $pathData = CommonHelper::image_path('file_storage', $image);
            if (!empty($pathData['master_value'])) {
                $imagePaths[] = $pathData['master_value'];
            }
        }

        return $imagePaths;
    }

    /**
     * Update existing room (for admin users or DMC updating their own room)
     */
    private function updateExistingRoom(Request $request, Room $room)
    {
        try {
            $auth_user = Auth::user();
            $dmcPricingOwnerId = in_array((int) $auth_user->role_id, [1, 20], true)
                ? null
                : ($this->resolveRoomPricingDmcUserId($auth_user) ?? $auth_user->userId);

            // Handle master image
            $master_image = $room->master_image ?? '';
        
            // Check if master image is removed
            if ($request->filled('removed_master_image')) {
                $removedMasterImage = $request->input('removed_master_image');
                // Delete from Azure blob storage
                if ($master_image) {
                    CommonHelper::deleteAzureImage($master_image);
                }
                $master_image = null; // Set to null when removed
            }
        
            // Handle new master image upload
            if ($request->hasFile('master_image')) {
                // Delete old master image from Azure before uploading new one
                if ($room->master_image) {
                    CommonHelper::deleteAzureImage($room->master_image);
                }
            
                $masterImagePath = CommonHelper::image_path('file_storage', $request->file('master_image'));
                if (!empty($masterImagePath['master_value'])) {
                    $master_image = $masterImagePath['master_value'];
                }
            }

            $existingImages = $request->input('existing_images', []);
            if (!is_array($existingImages)) {
                $existingImages = [];
            }

            // Get current images and find removed ones
            $currentImages = $room->images ? json_decode($room->images, true) : [];
            if (!is_array($currentImages)) {
                $currentImages = [];
            }
            $removedImages = array_diff($currentImages, $existingImages);
            foreach ($removedImages as $removedImage) {
                CommonHelper::deleteAzureImage($removedImage);
            }

            $imagePaths = $this->storeUploadedRoomGallery($request);
            $img_path = array_values(array_filter(array_merge($existingImages, $imagePaths)));

            // Calculate final prices based on user type and base room logic
            $finalWeekdayPrice = $request->singleWeekdayPrice ?? $request->baseSingleWeekdayPrice ?? 0;
            $finalWeekendPrice = $request->singleWeekendPrice ?? $request->baseSingleWeekendPrice ?? 0;
            $finalDoubleWeekdayPrice = $request->doubleWeekdayPrice ?? $request->baseDoubleWeekdayPrice ?? 0;
            $finalDoubleWeekendPrice = $request->doubleWeekendPrice ?? $request->baseDoubleWeekendPrice ?? 0;
            $finalWeekdayCostPrice = $request->singleWeekdayCostPrice ?? $request->baseSingleWeekdayCostPrice ?? null;
            $finalWeekendCostPrice = $request->singleWeekendCostPrice ?? $request->baseSingleWeekendCostPrice ?? null;
            $finalDoubleWeekdayCostPrice = $request->doubleWeekdayCostPrice ?? $request->baseDoubleWeekdayCostPrice ?? null;
            $finalDoubleWeekendCostPrice = $request->doubleWeekendCostPrice ?? $request->baseDoubleWeekendCostPrice ?? null;
        
            // If this is not a base room, calculate prices based on respective base room + variant
            if (!$room->base_room && $room->varient_price > 0) {
                if (in_array($auth_user->role_id, [1, 20])) {
                    // Admin: Use admin's base room
                    $adminBaseRoom = Room::where('hotel_id', $request->hotel_id)
                                       ->where('dmc_base_room', 1)
                                       ->where('base_room', true)
                                       ->first();
                
                    if ($adminBaseRoom) {
                        $finalWeekdayPrice = $adminBaseRoom->weekday_price + $room->varient_price;
                        $finalWeekendPrice = $adminBaseRoom->weekend_price + $room->varient_price;
                        $finalDoubleWeekdayPrice = $adminBaseRoom->double_weekday_price + $room->varient_price;
                        $finalDoubleWeekendPrice = $adminBaseRoom->double_weekend_price + $room->varient_price;
                    }
                } else {
                    // DMC / delegated roles: use parent DMC base room
                    $dmcBaseRoom = Room::where('hotel_id', $request->hotel_id)
                                     ->where('created_by', $dmcPricingOwnerId)
                                     ->where('base_room', true)
                                     ->where('dmc_base_room', 0)
                                     ->first();
                
                    if ($dmcBaseRoom) {
                        $finalWeekdayPrice = $dmcBaseRoom->weekday_price + $room->varient_price;
                        $finalWeekendPrice = $dmcBaseRoom->weekend_price + $room->varient_price;
                        $finalDoubleWeekdayPrice = $dmcBaseRoom->double_weekday_price + $room->varient_price;
                        $finalDoubleWeekendPrice = $dmcBaseRoom->double_weekend_price + $room->varient_price;
                    }
                }
            }

            // Debug the data being updated
            \Log::info("Updating room data", [
                'room_id' => $room->room_id,
                'no_of_room' => $request->total_no_of_room,
                'weekday_price' => $finalWeekdayPrice,
                'weekend_price' => $finalWeekendPrice,
                'double_weekday_price' => $finalDoubleWeekdayPrice,
                'double_weekend_price' => $finalDoubleWeekendPrice,
                'children_price' => $request->children_price,
                'dimension' => $request->dimension,
            ]);

            // Update room data
            $updateResult = $room->update([
                'room_type' => $request->room_type,
                'no_of_room' => $request->total_no_of_room,
                'varient_price' => $request->varient_price ?? 0,
                'weekday_price' => $finalWeekdayPrice,
                'weekend_price' => $finalWeekendPrice,
                'dimension' => $request->dimension,
                'double_weekday_price' => $finalDoubleWeekdayPrice,
                'double_weekend_price' => $finalDoubleWeekendPrice,
                'weekday_cost_price' => $finalWeekdayCostPrice,
                'weekend_cost_price' => $finalWeekendCostPrice,
                'double_weekday_cost_price' => $finalDoubleWeekdayCostPrice,
                'double_weekend_cost_price' => $finalDoubleWeekendCostPrice,
                'children_price' => $request->children_price,
                'breakfast' => $request->breakfast_included,
                'breakfast_type' => $request->breakfast_included ? $request->breakfast_type : null,
                'breakfast_price' => $request->breakfast_included ? $request->breakfast_price : null,
                'breakfast_cost_price' => $request->breakfast_included ? $request->breakfast_cost_price : null,
                'lunch' => $request->lunch_included,
                'lunch_type' => $request->lunch_included ? $request->lunch_type : null,
                'lunch_price' => $request->lunch_included ? $request->lunch_price : null,
                'lunch_cost_price' => $request->lunch_included ? $request->lunch_cost_price : null,
                'dinner' => $request->dinner_included,
                'dinner_type' => $request->dinner_included ? $request->dinner_type : null,
                'dinner_price' => $request->dinner_included ? $request->dinner_price : null,
                'dinner_cost_price' => $request->dinner_included ? $request->dinner_cost_price : null,
                'breakfast_included' => $request->supplementary_breakfast ?? false,
                'master_image' => $master_image,
                'images' => json_encode($img_path),
                'child_with_bed' => $request->child_with_bed,
                'child_without_bed' => $request->child_without_bed,
                'child_with_bed_cost' => $request->child_with_bed_cost,
                'child_without_bed_cost' => $request->child_without_bed_cost,
            ]);
        
            \Log::info("Room update result", ['success' => $updateResult]);
    

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::updateExistingRoom failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $friendly = $this->hotelUserFacingError($e, 'Unable to update the room. Please try again.');
            throw new \RuntimeException($friendly, 0, $e);
        }
    }

    /**
     * Create new DMC room based on original room (first time DMC edits)
     */
    private function createDmcRoom(Request $request, Room $originalRoom, $auth_user, ?int $roomCreatedByDmcUserId = null)
    {
        try {
            $roomCreatedByDmcUserId = $roomCreatedByDmcUserId ?? $this->resolveRoomPricingDmcUserId($auth_user) ?? $auth_user->userId;

            // Generate new room ID
            // $lastRoom = Room::withTrashed()->orderBy('id', 'desc')->first();
            // $room_max_id = $lastRoom->room_id ?? 0;
            // $roomId = CommonHelper::createId($room_max_id);
            // while (Room::where('room_id', $roomId)->exists()) {
            //     $roomId = CommonHelper::createId($roomId);
            // }

            // Handle master image
            $master_image = '';
            if ($request->hasFile('master_image')) {
                $masterImagePath = CommonHelper::image_path('file_storage', $request->file('master_image'));
                if (!empty($masterImagePath['master_value'])) {
                    $master_image = $masterImagePath['master_value'];
                }
            }

            // Handle additional images
            $imagePaths = $this->storeUploadedRoomGallery($request);

            // Check if DMC has a base room for this hotel
            $dmcBaseRoom = Room::where('hotel_id', $request->hotel_id)
                              ->where('created_by', $roomCreatedByDmcUserId)
                              ->where('base_room', true)
                              ->where('dmc_base_room', 0)
                              ->first();

            // Determine if this should be the DMC's base room
            $isBaseRoom = false;
            $varientPrice = 0;
        
            if (!$dmcBaseRoom) {
                // This is DMC's first room for this hotel - make it their base room
                $isBaseRoom = true;
            } else {
                // DMC already has a base room - calculate variant price based on their base room
                if ($originalRoom->varient_price > 0) {
                    $varientPrice = $originalRoom->varient_price;
                }
            }

            // Calculate final prices based on DMC's base room (if exists) + variant
            $finalWeekdayPrice = $request->singleWeekdayPrice ?? $request->baseSingleWeekdayPrice ?? 0;
            $finalWeekendPrice = $request->singleWeekendPrice ?? $request->baseSingleWeekendPrice ?? 0;
            $finalDoubleWeekdayPrice = $request->doubleWeekdayPrice ?? $request->baseDoubleWeekdayPrice ?? 0;
            $finalDoubleWeekendPrice = $request->doubleWeekendPrice ?? $request->baseDoubleWeekendPrice ?? 0;
            $finalWeekdayCostPrice = $request->singleWeekdayCostPrice ?? $request->baseSingleWeekdayCostPrice ?? null;
            $finalWeekendCostPrice = $request->singleWeekendCostPrice ?? $request->baseSingleWeekendCostPrice ?? null;
            $finalDoubleWeekdayCostPrice = $request->doubleWeekdayCostPrice ?? $request->baseDoubleWeekdayCostPrice ?? null;
            $finalDoubleWeekendCostPrice = $request->doubleWeekendCostPrice ?? $request->baseDoubleWeekendCostPrice ?? null;

            // If this is not a base room and DMC has a base room, add variant to DMC base prices
            if (!$isBaseRoom && $dmcBaseRoom && $varientPrice > 0) {
                $finalWeekdayPrice = $dmcBaseRoom->weekday_price + $varientPrice;
                $finalWeekendPrice = $dmcBaseRoom->weekend_price + $varientPrice;
                $finalDoubleWeekdayPrice = $dmcBaseRoom->double_weekday_price + $varientPrice;
                $finalDoubleWeekendPrice = $dmcBaseRoom->double_weekend_price + $varientPrice;
            }

            // Create new room for DMC
            $newRoom = Room::create([
                'hotel_id' => $request->hotel_id,
                'room_type' => $originalRoom->room_type,
                // 'room_id' => $roomId,
                'no_of_room' => $request->total_no_of_room,
                'weekday_price' => $finalWeekdayPrice,
                'weekend_price' => $finalWeekendPrice,
                'double_weekday_price' => $finalDoubleWeekdayPrice,
                'double_weekend_price' => $finalDoubleWeekendPrice,
                'weekday_cost_price' => $finalWeekdayCostPrice,
                'weekend_cost_price' => $finalWeekendCostPrice,
                'double_weekday_cost_price' => $finalDoubleWeekdayCostPrice,
                'double_weekend_cost_price' => $finalDoubleWeekendCostPrice,
                'dimension' => $request->dimension,
                'children_price' => $request->children_price,
                'breakfast' => $request->breakfast_included,
                'breakfast_type' => $request->breakfast_included ? $request->breakfast_type : null,
                'breakfast_price' => $request->breakfast_included ? $request->breakfast_price : null,
                'breakfast_cost_price' => $request->breakfast_included ? $request->breakfast_cost_price : null,
                'lunch' => $request->lunch_included,
                'lunch_type' => $request->lunch_included ? $request->lunch_type : null,
                'lunch_price' => $request->lunch_included ? $request->lunch_price : null,
                'lunch_cost_price' => $request->lunch_included ? $request->lunch_cost_price : null,
                'dinner' => $request->dinner_included,
                'dinner_type' => $request->dinner_included ? $request->dinner_type : null,
                'dinner_price' => $request->dinner_included ? $request->dinner_price : null,
                'dinner_cost_price' => $request->dinner_included ? $request->dinner_cost_price : null,
                'breakfast_included' => $request->supplementary_breakfast ?? false,
                'master_image' => $master_image,
                'images' => json_encode($imagePaths),
                'created_by' => $roomCreatedByDmcUserId,
                'dmc_id' => $roomCreatedByDmcUserId,
                'dmc_base_room' => 0, // This is DMC specific room, not admin base room
                'base_room' => $isBaseRoom, // True if this is DMC's first/base room
                'status' => $request->room_status == 1 ? 1 : 0,
                'varient_price' => $varientPrice, // Store the variant price for future calculations
                'breakfast_restaurant' => $originalRoom->breakfast_restaurant,
                'child_with_bed' => $request->child_with_bed,
                'child_without_bed' => $request->child_without_bed,
                'child_with_bed_cost' => $request->child_with_bed_cost,
                'child_without_bed_cost' => $request->child_without_bed_cost,
            ]);
            $is_save = $newRoom->save();
            $newRoom->refresh();
            if ($is_save) {
                return redirect()->back()
                    ->with('success', 'Room details saved successfully!');
            } else {
                return redirect()->back()
                    ->with('error', 'An error occurred while saving the room details.');
            }
    

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::createDmcRoom failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $friendly = $this->hotelUserFacingError($e, 'Unable to update the room. Please try again.');
            throw new \RuntimeException($friendly, 0, $e);
        }
    }

    /*
    * Delete Room Details .
    * Date 18-11-2024
    */
    public function deleteroom($id){
        try {
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
        
            // Delete room images from Azure before deleting the record
            if($room) {
                // Delete master image
                if($room->master_image) {
                    CommonHelper::deleteAzureImage($room->master_image);
                }
            
                // Delete additional images
                if($room->images) {
                    $images = json_decode($room->images, true);
                    if(is_array($images)) {
                        foreach($images as $image) {
                            CommonHelper::deleteAzureImage($image);
                        }
                    }
                }
            }
        
            $delete = Room::where('room_id', $id)->delete();
            if ($delete){
                return redirect()->route('hotels.createroom', ['id' => $room->hotel_id])
                    ->with('success', 'Room details deleted successfully!');
            } else {
                return redirect()->back()
                    ->with('error', 'An error occurred while updating the room details.');
            }
    

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::deleteroom failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', $this->hotelUserFacingError($e, 'Unable to delete the room. Please try again.'));
        }
    }
    
    /*
    * Beds .
    * Date 31-12-2024
    */
    public function hotelbeds($id){
        try {
            // if (!hasPermission('view bed')) {
            //     abort(403, 'You do not have permission to access this page.');
            // }
            $auth_user = Auth::user();
        
            $hotel = Hotel::where('hotel_unique_id', $id)->first();
            $dmcId = CommonHelper::getDmcId($auth_user);
            $rooms = Room::where('hotel_id', $id)->where('created_by', $dmcId)
            ->get();

        
            // Get DMC users for admin dropdown (only for admin users)
            $dmcUsers = collect();
            if ($auth_user->role_id == 1) {
                $dmcUsers = User::where('role_id', 11)
                ->where('user_type', 2)
                ->select('userId', 'name', 'company_name', 'currency')
                ->orderBy('company_name', 'asc')
                ->get();
            }
        
            // Fetch beds data based on user role
            if ($auth_user->role_id == 1) {
                // Admin: Show all beds for this hotel
                $bedsData = Bed::with(['room', 'user'])
                ->whereHas('room', function ($query) use ($id) {
                    $query->where('hotel_id', $id);
                })
                ->get();
            
                // Add DMC information to each bed
                $bedsData = $bedsData->map(function ($bed) {
                    if ($bed->dmc_id) {
                        $dmcUser = User::where('userId', $bed->dmc_id)->first();
                        if ($dmcUser) {
                            $bed->dmc_name = $dmcUser->name;
                            $bed->dmc_company = $dmcUser->company_name;
                            $bed->dmc_user_id = $dmcUser->userId;
                        }
                    } else {
                        $bed->dmc_name = 'Unknown';
                        $bed->dmc_company = 'Unknown DMC';
                        $bed->dmc_user_id = 'unknown';
                    }
                    return $bed;
                });
            } else {
                // DMC/Other users: Show only their own beds
                $bedsData = Bed::with('room')->where('dmc_id', $dmcId)
                              ->whereHas('room', function ($query) use ($id) {
                                  $query->where('hotel_id', $id);
                              })
                              ->get();
            }
        
            $beds = BedMaster::where('hotel_id', $id)->get();
        
            return view('hotel.beds', compact('hotel','rooms','beds','bedsData','auth_user','dmcUsers'));
    

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::hotelbeds failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', $this->hotelUserFacingError($e, 'Unable to open this page right now.'));
        }
    }

    public function getBedTypeData(Request $request)
    {
        try {
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
    

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::getBedTypeData failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => $this->hotelUserFacingError($e, 'Unable to load bed type details.')], 500);
        }
    }

    public function storebeds(Request $request){
        try {
            $auth_user = Auth::user();
        
            // Base validation rules
            $rules = [
                'no_of_rooms' => 'required|integer|min:1',
                'max_occupancy' => 'required|integer|min:1',
                'adult_count' => 'nullable|integer|min:0',
                'child_count' => 'nullable|integer|min:0',
                'extra_bed' => 'nullable|boolean',
                'extra_bed_type' => 'required_if:extra_bed,1|nullable|string',
                'extra_bed_price' => 'required_if:extra_bed,1|nullable|numeric|min:0',
                'extra_bed_cost_price' => 'required_if:extra_bed,1|nullable|numeric|min:0',
                'baby_cot' => 'nullable|boolean',
                'baby_cot_price' => 'required_if:baby_cot,1|nullable|numeric|min:0',
                'baby_cot_cost_price' => 'required_if:baby_cot,1|nullable|numeric|min:0',
            ];
        
            // For admin and role_id 20, DMC selection is required
            if ($auth_user->role_id == 1 || $auth_user->role_id == 20) {
                $rules['dmc_id'] = 'required|exists:users,userId';
            }
        
            $request->validate($rules);
            //If extra bed and baby cot is not available
            if ($request->extra_bed != 1) {
                $request->merge([
                    'extra_bed_type' => null,
                    'extra_bed_price' => 0,
                    'extra_bed_cost_price' => null,
                ]);
            }
            if ($request->baby_cot != 1) {
                $request->merge([
                    'baby_cot_price' => 0,
                    'baby_cot_cost_price' => null,
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

            // $lastBed = Bed::withTrashed()->orderBy('bed_id', 'desc')->first();
            // $bed_max_id = $lastBed->bed_id ?? 0;
            // $bedId = CommonHelper::createId($bed_max_id);
            // while (Bed::where('bed_id', $bedId)->exists()) {
            //     $bedId = CommonHelper::createId($bedId);
            // }
        
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
            $bed->extra_bed_cost_price = $request->input('extra_bed') == 1 ? $request->input('extra_bed_cost_price') : null;
            $bed->baby_cot = $request->input('baby_cot') ?? null;
            $bed->baby_cot_price = $request->input('baby_cot_price') ?? 0;
            $bed->baby_cot_cost_price = $request->input('baby_cot') == 1 ? $request->input('baby_cot_cost_price') : null;
            // $bed->bed_id = $bedId;
            $dmcId = CommonHelper::getDmcId($auth_user);
            // Set DMC ID based on user role
            if ($auth_user->role_id == 1 || $auth_user->role_id == 20) {
                // Admin/Manager users: use the selected DMC ID
                $bed->dmc_id = $request->input('dmc_id');
            } else {
                // Regular DMC users: use their own user ID
                $bed->dmc_id = $dmcId;
            }
        
            $bed->room_id = $request->input('room_id');
            $bed->is_active = $request->input('bed_status');
            $bed->force_child = $request->input('force_child');
            $bed->force_child_count = $request->input('force_child_count');
            $is_save = $bed->save();
            $bed->refresh();
            if ($request->hotel_id) {
                Hotel::where('hotel_unique_id', $request->hotel_id)->update(['is_complete' => 1]);
            }
            // Redirect or return response
            if ($is_save) {
                return redirect()->route('hotels.beds', $request->hotel_id)->with('success', 'Bed saved successfully.');
            } else {
                return redirect()->route('hotels.beds', $request->hotel_id)->with('error', 'An error occurred while saving the bed details.');
            }
    

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::storebeds failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', $this->hotelUserFacingError($e, 'Unable to save the bed. Please try again.'));
        }
    }

    //edit bed
    public function editbed($id, $hotelId){
        try {
            // if (!hasPermission('edit bed')) {
            //     abort(403, 'You do not have permission to access this page.');
            // }
            $auth_user = Auth::user();
            $bedId = Crypt::decrypt($id);
            $hotel = Hotel::where('hotel_unique_id', $hotelId)->first();
            $beds = BedMaster::where('hotel_id', $hotelId)->get();
            $dmcId = CommonHelper::getDmcId($auth_user);
            $rooms = Room::where('hotel_id',$hotelId)->where('created_by', $dmcId)->get();
            $hotelBed = Bed::with('room')->where('bed_id', $bedId)->first();
            $room = Room::where('room_id', $hotelBed->room_id)->first();
            // dd($rooms->toArray());
            return view('hotel.edit-beds', compact('hotel','rooms','beds','hotelBed','room'));
    

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::editbed failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', $this->hotelUserFacingError($e, 'Unable to open this page right now.'));
        }
    }

    /** update bed */

    public function updatebed(Request $request){
        try {
            $request->validate([
                'no_of_rooms' => 'required|integer|min:1',
                'max_occupancy' => 'required|integer|min:1',
                'adult_count' => 'nullable|integer|min:0',
                'child_count' => 'nullable|integer|min:0',
                'extra_bed' => 'nullable|boolean',
                'extra_bed_type' => 'required_if:extra_bed,1|nullable|string',
                'extra_bed_price' => 'required_if:extra_bed,1|nullable|numeric|min:0',
                'extra_bed_cost_price' => 'required_if:extra_bed,1|nullable|numeric|min:0',
                'baby_cot' => 'nullable|boolean',
                'baby_cot_price' => 'required_if:baby_cot,1|nullable|numeric|min:0',
                'baby_cot_cost_price' => 'required_if:baby_cot,1|nullable|numeric|min:0',
            ]);

            //If extra bed and baby cot is not available
            if ($request->extra_bed != 1) {
                $request->merge([
                    'extra_bed_type' => null,
                    'extra_bed_price' => 0,
                    'extra_bed_cost_price' => null,
                ]);
            }
            if ($request->baby_cot != 1) {
                $request->merge([
                    'baby_cot_price' => 0,
                    'baby_cot_cost_price' => null,
                ]);
            }

            // $bedId = Crypt::decrypt($request->bed_id);
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
            $bed->extra_bed_cost_price = $request->input('extra_bed') == 1 ? $request->input('extra_bed_cost_price') : null;
            $bed->baby_cot = $request->input('baby_cot') ?? null;
            $bed->baby_cot_price = $request->input('baby_cot_price') ?? 0;
            $bed->baby_cot_cost_price = $request->input('baby_cot') == 1 ? $request->input('baby_cot_cost_price') : null;
            $bed->is_active = $request->beds_status == 1 ? 1 : 0;
            $bed->save();

            return redirect()->route('hotels.beds', $request->hotel_id)->with('success', 'Bed Details Updated Successfully.');
    

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::updatebed failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', $this->hotelUserFacingError($e, 'Unable to update the bed. Please try again.'));
        }
    }
    /*
    * Delete Bed Details .
    * Date 18-11-2024
    */
    public function deletebed($hotelId, $bedId){
        try {
            // if (!hasPermission('delete bed')) {
            //     abort(403, 'You do not have permission to access this page.');
            // }
            $bedId = Crypt::decrypt($bedId);
            $bed = Bed::where('bed_id', $bedId)->first();
            $delete = Bed::where('bed_id', $bedId)->delete();
            if ($delete){
                return redirect()->route('hotels.beds', ['hotel' => $hotelId])
                    ->with('success', 'Bed details deleted successfully!');
            } else {
                return redirect()->back()
                    ->with('error', 'An error occurred while updating the room details.');
            }
    

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::deletebed failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', $this->hotelUserFacingError($e, 'Unable to delete the bed. Please try again.'));
        }
    }

    public function getNoOfRooms(Request $request){
        $roomTypeId = $request->input('room_type_id');
        $rooms = Room::where('room_id', $roomTypeId)->get(['id', 'no_of_room']); 
        return response()->json($rooms);
    }

    public function getRoomsByDmc(Request $request){
        $dmcId = $request->input('dmc_id');
        $hotelId = $request->input('hotel_id');
        
        if (!$dmcId || !$hotelId) {
            return response()->json([]);
        }
        
        $rooms = Room::where('hotel_id', $hotelId)
                    ->where('created_by', $dmcId)
                    ->where('status', 1)
                    ->get(['room_id', 'room_type']); 
        
        return response()->json($rooms);
    }
    
    /*
    * Hotel Calender Monthly Details with DMC Filtering.
    * 
    * DMC Filtering Logic:
    * - Admin users (role_id 1 or 20) can see all rates for the hotel
    * - DMC users can only see their own rates (rates they created)
    * - Each rate is associated with a dmc_id when created
    * - Calendar displays only rates belonging to the current user (unless admin)
    * 
    * Date 16-12-2024
    * Updated: 20-01-2025 (Added DMC filtering)
    */
    public function calender($id, $year = null)
    {
        $auth_user = Auth::user();
        
        // Build hotel query with DMC access control
        $hotelQuery = Hotel::with('category', 'rooms.beds')
            ->where('status', 1)
            ->where('hotel_unique_id', $id);

        // Apply DMC filtering for non-admin users
        if ($auth_user->role_id != 1) {
            $hotelQuery->whereJsonContains('dmc_id', $auth_user->userId);
        }

        $hotel = $hotelQuery->first();

        if (!$hotel) {
            return redirect()->back()->with('error', 'Hotel not found or you do not have access to this hotel!');
        }

        $year = $year ?? now()->year;
        $weekend_days = json_decode($hotel->weekend_days) ?? []; // Weekend days
        $room = $hotel->rooms->first();

        $weekday_base_price = $room->weekday_price ?? 0;
        $weekend_base_price = $room->weekend_price ?? 0;

        // Get DMC users for admin dropdown
        $dmcUsers = [];
        if ($auth_user->role_id == 1) {
            $dmcUsers = User::where('role_id', 11)
                ->where('user_type', 2)
                ->select('userId', 'name', 'company_name', 'currency')
                ->orderBy('company_name', 'asc')
                ->get();
        }

        // Get all rates with DMC access control, ordered by priority
        $ratesQuery = Rate::where('hotel_id', $id)->where('is_active', 1);
        
        // Apply DMC filtering for rates for non-admin users
        // Admin users (role_id 1) can see all rates
        // Other users (DMCs) can only see their own rates
        if ($auth_user->role_id != 1) {
            $ratesQuery->where('dmc_id', $auth_user->userId);
        }
        
        $rates = $ratesQuery->orderByRaw("
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
                            'dmc_id' => $rate->dmc_id, // Add DMC ID for reference
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
                            'dmc_id' => $rate->dmc_id, // Add DMC ID for reference
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
                            'dmc_id' => $rate->dmc_id, // Add DMC ID for reference
                        ];
                    }
                }

                $startDate->addDay(1);  // Keep the original loop for date iteration
            }
        }

        // Create DMC user mapping for admin users
        $dmcUserMap = [];
        if ($auth_user->role_id == 1) {
            foreach ($dmcUsers as $user) {
                $dmcUserMap[$user->userId] = [
                    'user_name' => $user->name,
                    'company_name' => $user->company_name
                ];
            }
        }
        
        return view('hotel.calender', compact('hotel', 'rate_dates', 'year', 'weekend_days', 'weekday_base_price', 'weekend_base_price', 'auth_user', 'dmcUsers', 'dmcUserMap'));
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
        try {
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

            $hotel = Hotel::where('hotel_unique_id', $request->hotel_id)->first();
            if (!$hotel) {
                return redirect()->back()->with('error', 'Hotel not found.');
            }

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
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::updatepolicy failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', $this->hotelUserFacingError($e, 'Unable to update the property policy. Please try again.'));
        }
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
        try {
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::updateRefundPolicy failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', $this->hotelUserFacingError($e, 'Unable to update the refund policy. Please try again.'));
        }
    }

    /*
    * Update Cancellation Policy
    * Date 26-12-2024
    */
    public function updatecancellationPolicy(Request $request)
    {
        try {
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::updatecancellationPolicy failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', $this->hotelUserFacingError($e, 'Unable to update the cancellation policy. Please try again.'));
        }
    }

    /**
     * Update child policy
     */
    public function updateChildPolicy(Request $request)
    {
        try {
            $policy_file = '';
            if ($request->file('file')) {
                $policyFilePath = CommonHelper::image_path('file_storage', $request->file('file'));
                if (!empty($policyFilePath['master_value'])) {
                    $policy_file = $policyFilePath['master_value'];
                }
            }

            $hotel = Hotel::where('hotel_unique_id', $request->hotel_id)->first();
            if (!$hotel) {
                return redirect()->back()->with('error', 'Hotel not found.');
            }
            $hotel->update([
                'childpolicy' => $request->childpolicy,
                'childpolicy_pdf' => $policy_file ?: $hotel->childpolicy_pdf
            ]);

            // Redirect to the next policy tab
            return redirect()->route('policy', ['id' => $hotel->hotel_unique_id, 'tab' => 'pet'])
                ->with('success', 'Child policy updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::updateChildPolicy failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', $this->hotelUserFacingError($e, 'Unable to update the child policy. Please try again.'));
        }
    }

    /**
     * Update pet policy
     */
    public function updatePetPolicy(Request $request)
    {
        try {
            $policy_file = '';
            if ($request->file('file')) {
                $policyFilePath = CommonHelper::image_path('file_storage', $request->file('file'));
                if (!empty($policyFilePath['master_value'])) {
                    $policy_file = $policyFilePath['master_value'];
                }
            }

            $hotel = Hotel::where('hotel_unique_id', $request->hotel_id)->first();
            if (!$hotel) {
                return redirect()->back()->with('error', 'Hotel not found.');
            }
            $hotel->update([
                'pet_allowed' => $request->pet_allowed,
                'petpolicy' => $request->petpolicy,
                'petpolicy_pdf' => $policy_file ?: $hotel->petpolicy_pdf
            ]);

            // Redirect to the next policy tab
            return redirect()->route('policy', ['id' => $hotel->hotel_unique_id, 'tab' => 'terms'])
                ->with('success', 'Pet policy updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::updatePetPolicy failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', $this->hotelUserFacingError($e, 'Unable to update the pet policy. Please try again.'));
        }
    }

    /**
     * Update terms and conditions
     */
    public function updateTermsPolicy(Request $request)
    {
        try {
            $policy_file = '';
            if ($request->file('file')) {
                $policyFilePath = CommonHelper::image_path('file_storage', $request->file('file'));
                if (!empty($policyFilePath['master_value'])) {
                    $policy_file = $policyFilePath['master_value'];
                }
            }

            $hotel = Hotel::where('hotel_unique_id', $request->hotel_id)->first();
            if (!$hotel) {
                return redirect()->back()->with('error', 'Hotel not found.');
            }
            $hotel->update([
                'termspolicy' => $request->termspolicy,
                'termspolicy_pdf' => $policy_file ?: $hotel->termspolicy_pdf
            ]);

            // Redirect back to the property policy tab
            return redirect()->route('policy', ['id' => $hotel->hotel_unique_id, 'tab' => 'property'])
                ->with('success', 'Terms and conditions updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::updateTermsPolicy failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', $this->hotelUserFacingError($e, 'Unable to update the terms and conditions. Please try again.'));
        }
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
        try {
            $hotel_id = $request->hotel_id;
            $hotel = Hotel::where('hotel_unique_id', $hotel_id)->first();
            if (!$hotel) {
                return redirect()->back()->with('error', 'Hotel not found.');
            }

            $exitEnabled = $request->boolean('enable_port_of_exit');
            $othersEnabled = $request->boolean('enable_others');

            $rules = [
                'hotel_id' => 'required',
            ];
            $messages = [];

            if ($exitEnabled) {
                $exitTypes = $request->input('exit_port_name', []);
                $exitNames = $request->input('exit_port_specific_name', []);
                $exitLats = $request->input('exit_latitude', []);
                $exitLngs = $request->input('exit_longitude', []);
                $exitDists = $request->input('exit_distance', []);
                $exitCount = max(count($exitTypes), count($exitLats), count($exitLngs), count($exitDists));

                if ($exitCount === 0) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['enable_port_of_exit' => 'Please add at least one Port of Entry/Exit row, or disable the section.']);
                }

                for ($i = 0; $i < $exitCount; $i++) {
                    $rules["exit_port_name.$i"] = 'required|string|max:100';
                    $rules["exit_port_specific_name.$i"] = 'required|string|max:255';
                    $rules["exit_latitude.$i"] = ['required', 'regex:/^-?([1-8]?[0-9]\.\d{1,9}|90\.0{1,9})$/'];
                    $rules["exit_longitude.$i"] = ['required', 'regex:/^-?([1-9]?[0-9]\.\d{1,9}|1[0-7][0-9]\.\d{1,9}|180\.0{1,9})$/'];
                    $rules["exit_distance.$i"] = ['required', 'regex:/^[0-9]+(\.[0-9]{1,2})?$/'];

                    $messages["exit_port_name.$i.required"] = 'Port Type is required for each Entry/Exit row.';
                    $messages["exit_port_specific_name.$i.required"] = 'Port Name is required for each Entry/Exit row.';
                    $messages["exit_latitude.$i.required"] = 'Latitude is required for each Entry/Exit row.';
                    $messages["exit_latitude.$i.regex"] = 'Latitude must be a valid decimal between -90 and 90.';
                    $messages["exit_longitude.$i.required"] = 'Longitude is required for each Entry/Exit row.';
                    $messages["exit_longitude.$i.regex"] = 'Longitude must be a valid decimal between -180 and 180.';
                    $messages["exit_distance.$i.required"] = 'Distance is required for each Entry/Exit row.';
                    $messages["exit_distance.$i.regex"] = 'Distance must be a positive number with up to 2 decimals.';
                }

                // Ensure arrays are present for indexed validation
                $request->merge([
                    'exit_port_name' => $exitTypes,
                    'exit_port_specific_name' => $exitNames,
                    'exit_latitude' => $exitLats,
                    'exit_longitude' => $exitLngs,
                    'exit_distance' => $exitDists,
                ]);
            }

            if ($othersEnabled) {
                $otherNames = $request->input('others_port_name', []);
                $otherTypes = $request->input('others_type', []);
                $otherLats = $request->input('others_latitude', []);
                $otherLngs = $request->input('others_longitude', []);
                $otherDists = $request->input('others_distance', []);
                $otherCount = max(count($otherNames), count($otherTypes), count($otherLats), count($otherLngs), count($otherDists));

                if ($otherCount === 0) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['enable_others' => 'Please add at least one Near By Attraction row, or disable the section.']);
                }

                for ($i = 0; $i < $otherCount; $i++) {
                    $rules["others_port_name.$i"] = 'required|string|max:255';
                    $rules["others_type.$i"] = 'required|string|max:100';
                    $rules["others_latitude.$i"] = ['required', 'regex:/^-?([1-8]?[0-9]\.\d{1,9}|90\.0{1,9})$/'];
                    $rules["others_longitude.$i"] = ['required', 'regex:/^-?([1-9]?[0-9]\.\d{1,9}|1[0-7][0-9]\.\d{1,9}|180\.0{1,9})$/'];
                    $rules["others_distance.$i"] = ['required', 'regex:/^[0-9]+(\.[0-9]{1,2})?$/'];

                    $messages["others_port_name.$i.required"] = 'Attraction Name is required for each row.';
                    $messages["others_type.$i.required"] = 'Attraction Type is required for each row.';
                    $messages["others_latitude.$i.required"] = 'Latitude is required for each Near By Attraction row.';
                    $messages["others_latitude.$i.regex"] = 'Latitude must be a valid decimal between -90 and 90.';
                    $messages["others_longitude.$i.required"] = 'Longitude is required for each Near By Attraction row.';
                    $messages["others_longitude.$i.regex"] = 'Longitude must be a valid decimal between -180 and 180.';
                    $messages["others_distance.$i.required"] = 'Distance is required for each Near By Attraction row.';
                    $messages["others_distance.$i.regex"] = 'Distance must be a positive number with up to 2 decimals.';
                }

                $request->merge([
                    'others_port_name' => $otherNames,
                    'others_type' => $otherTypes,
                    'others_latitude' => $otherLats,
                    'others_longitude' => $otherLngs,
                    'others_distance' => $otherDists,
                ]);
            }

            $request->validate($rules, $messages);

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
                $request->input('exit_port_specific_name', []),
                $request->input('exit_latitude', []),
                $request->input('exit_longitude', []),
                $request->input('exit_distance', []),
            );

            $portOfOtherData = $this->processPortData(
                $request->input('others_port_name', []),
                $request->input('others_type', []),
                $request->input('others_latitude', []),
                $request->input('others_longitude', []),
                $request->input('others_distance', []),
            );

            // Prepare update data for the hotel
            $updateData = [];

            if ($exitEnabled) {
                $updateData['port_of_exit'] = json_encode($portOfExitData);
            } else {
                $updateData['port_of_exit'] = json_encode([]);
            }

            if ($othersEnabled) {
                $updateData['others'] = json_encode($portOfOtherData);
            } else {
                $updateData['others'] = json_encode([]);
            }

            // Keep entry data behavior unchanged when present
            if (!empty($portOfEntryData)) {
                $updateData['port_of_entry'] = json_encode($portOfEntryData);
            }

            if (!empty($updateData)) {
                $hotel->update($updateData);
            }

            return redirect()
                ->route('hotels.facility', ['id' => $hotel->hotel_unique_id])
                ->with('success', 'Ports updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::updateports failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', $this->hotelUserFacingError($e, 'Unable to update the ports. Please try again.'));
        }
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
    public function storeFacility(Request $request)
    {
        try {
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
                    $imagePath = CommonHelper::image_path('file_storage', $image);

                    if (!empty($imagePath['master_value'])) {
                        // Add the new image path to the existing images array
                        $existingImages[$facilityId][] = $imagePath['master_value'];
                    }
                }
            }

            // Save the updated facilities and images back to the hotel
            $hotel->facilities_images = json_encode($existingImages, JSON_UNESCAPED_UNICODE);
            $hotel->save();

            return back()->with('success', 'Facilities and images updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::storeFacility failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', $this->hotelUserFacingError($e, 'Unable to save the facilities. Please try again.'));
        }
    }

    /*
    * Store Facility Data
    * Date 13-01-2025
    */
    public function updateFacility(Request $request)
    {
        try {
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::updateFacility failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', $this->hotelUserFacingError($e, 'Unable to update the facility. Please try again.'));
        }
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
        try {
            $hotel = Hotel::where('hotel_unique_id', $hotelId)->first();
            if (!$hotel) {
                return redirect()->back()->with('error', 'Hotel not found.');
            }
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::destroyfacility failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', $this->hotelUserFacingError($e, 'Unable to delete the facility. Please try again.'));
        }
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
        try {
            $hotel = Hotel::where('hotel_unique_id', $request->hotel_id)->firstOrFail();
            $hotel->conference_room = $request->conference;
            $hotel->save();
            return back()->with('success', 'Conference Room Updated Successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('HotelController::updateConference failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withInput()->with('error', $this->hotelUserFacingError($e, 'Unable to update the conference room. Please try again.'));
        }
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
                $query->whereJsonContains('dmc_id', $auth_user->userId); // Corrected column name
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
                $query->whereJsonContains('dmc_id', $auth_user->userId); // Corrected column name
            })->get();
        }
        $currentRooms = Room::where('room_type', 'Standard')->first();
        return view('hotel.hotel-brand-details', compact('rooms', 'currentRooms', 'brand'));
    }

    /**
     * Show DMC Hotels Selection Page
     * For DMC users to select/manage their hotels
     */
    public function dmcHotelsSelection(Request $request)
    {
        // Check if user is DMC (role_id = 11)
        $user = auth()->user();
        $allowedRoles = [11, 35, 77, 84, 130, 132, 133, 135, 136, 137, 138, 139, 140];

        if (!in_array($user->role_id, $allowedRoles)) {
            abort(403, 'You do not have permission to access this page.');
        }

        if($user->role_id == 11){
            $dmc_id = $user->userId;
        }else if($user->role_id == 35 || in_array($user->role_id, [130, 132, 133, 135, 136, 137, 138])){
            $dmc_id = $user->created_by;
        }else if($user->role_id == 77 || $user->role_id == 139){
            $product_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $product_head->created_by;
        }else if($user->role_id == 84 || $user->role_id == 140){
            $product_manager = User::where('userId', $user->created_by)->first();
            $product_head = User::where('userId', $product_manager->created_by)->first();
            $dmc_id = $product_head->created_by;
        }
        else{
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }

        // We want Master DMC countries' hotels that are added from Travclicks.
        // Important: for role_id=11 (DMC user), $dmc_id is the DMC id, not the Master DMC.
        // Resolve the Master DMC via master_dmc_id, or by walking created_by chain until role_id=3.
        $masterDmcId = $user->master_dmc_id ?? null;
        if (empty($masterDmcId)) {
            $dmcUser = User::where('userId', $dmc_id)->first();
            $masterDmcId = $dmcUser->master_dmc_id ?? null;
        }
        if (empty($masterDmcId)) {
            $visited = [];
            $candidateId = $user->created_by ?? null;
            $safety = 0;
            while (!empty($candidateId) && $safety < 8 && !in_array($candidateId, $visited, true)) {
                $visited[] = $candidateId;
                $candidate = User::where('userId', $candidateId)->first();
                if (! $candidate) {
                    break;
                }
                if ((int) ($candidate->role_id ?? 0) === 3) {
                    $masterDmcId = $candidate->userId;
                    break;
                }
                $candidateId = $candidate->created_by ?? null;
                $safety++;
            }
        }

        // Fall back to current DMC id if we cannot resolve a master
        $masterDmc = User::where('userId', $masterDmcId ?: $dmc_id)->first();
        $masterDmcCountries = [];
        if ($masterDmc && !empty($masterDmc->country)) {
            $masterDmcCountries = array_values(array_filter(array_map(
                static fn ($c) => trim($c),
                preg_split('/\s*,\s*/', (string) $masterDmc->country)
            )));
        }

        // Get all available hotels
        $allHotelsQuery = Hotel::where('status', 1)
            // Hotels added from Travclicks are treated as platform/admin hotels
            ->where('user_type', 1)
            ->with(['category'])
            ->orderBy('name', 'asc');

        if (!empty($masterDmcCountries)) {
            $allHotelsQuery->whereIn('country', $masterDmcCountries);
        }

        $allHotels = $allHotelsQuery->get();

        // Country dropdown should show only countries that actually have Travclicks hotels
        // within the Master DMC allowed countries.
        $allowedCountries = $allHotels
            ->pluck('country')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
        
        // Filter hotels that are selected by the current DMC
        $selectedHotels = $allHotels->filter(function($hotel) use ($dmc_id) {
            return $hotel->hasSelectedByDmc($dmc_id);
        });

        // Get hotels that are not selected by the current DMC
        $availableHotels = $allHotels->filter(function($hotel) use ($dmc_id) {
            return !$hotel->hasSelectedByDmc($dmc_id);
        });

        return view('services.hotels', compact('availableHotels', 'selectedHotels', 'allowedCountries'));
    }

    /**
     * Update DMC Hotels Selection
     * Handle checkbox updates for hotel selection
     */
    public function updateDmcHotels(Request $request)
    {
        $user = auth()->user();
        $allowedRoles = [11, 35, 77, 84, 130, 132, 133, 135, 136, 137, 138, 139, 140];

        if (!in_array($user->role_id, $allowedRoles)) {
            abort(403, 'You do not have permission to access this page.');
        }

        if($user->role_id == 11){
            $dmc_id = $user->userId;
        }else if($user->role_id == 35 || in_array($user->role_id, [130, 132, 133, 135, 136, 137, 138])){
            $dmc_id = $user->created_by;
        }else if($user->role_id == 77 || $user->role_id == 139){
            $product_head = User::where('userId', $user->created_by)->first();
            $dmc_id = $product_head->created_by;
        }else if($user->role_id == 84 || $user->role_id == 140){
            $product_manager = User::where('userId', $user->created_by)->first();
            $product_head = User::where('userId', $product_manager->created_by)->first();
            $dmc_id = $product_head->created_by;
        }
        else{
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }

        $selectedHotels = $request->input('selected_hotels', []);
        
        // Reset all hotels for this DMC (remove from dmc_ids)
        $allHotelsForUser = Hotel::whereJsonContains('dmc_id', $dmc_id)->get();
        foreach ($allHotelsForUser as $hotel) {
            $hotel->removeDmcId($dmc_id);
        }
        
        // Add dmc_id for selected hotels
        if (!empty($selectedHotels)) {
            $hotelsToSelect = Hotel::whereIn('hotel_unique_id', $selectedHotels)->get();
            foreach ($hotelsToSelect as $hotel) {
                $hotel->addDmcId($dmc_id);
            }
        }

        return redirect()->back()->with('success', 'Hotel selection updated successfully!');
    }

    /**
     * Select Individual Hotel for DMC
     * Handle individual hotel selection with AJAX
     */
        public function selectHotel(Request $request)
    {
        try {
            $hotelId = $request->input('hotel_id');
            $user = Auth::user();

            $allowedRoles = [11, 35, 77, 84, 130, 132, 133, 135, 136, 137, 138, 139, 140];

            if (!in_array($user->role_id, $allowedRoles)) {
                abort(403, 'You do not have permission to access this page.');
            }

            if($user->role_id == 11){
                $dmc_id = $user->userId;
            }else if($user->role_id == 35 || in_array($user->role_id, [130, 132, 133, 135, 136, 137, 138])){
                $dmc_id = $user->created_by;
            }else if($user->role_id == 77 || $user->role_id == 139){
                $product_head = User::where('userId', $user->created_by)->first();
                $dmc_id = $product_head->created_by;
            }else if($user->role_id == 84 || $user->role_id == 140){
                $product_manager = User::where('userId', $user->created_by)->first();
                $product_head = User::where('userId', $product_manager->created_by)->first();
                $dmc_id = $product_head->created_by;
            }
            else{
                return redirect()->back()->with('error', 'You do not have permission to access this page.');
            }
            
            // Find the hotel
            $hotel = Hotel::find($hotelId);
            if (!$hotel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hotel not found.'
                ], 404);
            }
            
            // Add the DMC ID to the hotel's dmc_id array
            $hotel->addDmcId($dmc_id);
            
            return response()->json([
                'success' => true,
                'message' => 'Hotel selected successfully!'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Hotel selection error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while selecting the hotel.'
            ], 500);
        }
    }

    /**
     * Remove Individual Hotel from DMC Selection
     * Handle individual hotel removal with AJAX
     */
    public function removeHotel(Request $request)
    {
        try {
            $hotelId = $request->input('hotel_id');
            $user = Auth::user();

            $allowedRoles = [11, 35, 77, 84, 130, 132, 133, 135, 136, 137, 138, 139, 140];

            if (!in_array($user->role_id, $allowedRoles)) {
                abort(403, 'You do not have permission to access this page.');
            }

            if($user->role_id == 11){
                $dmc_id = $user->userId;
            }else if($user->role_id == 35 || in_array($user->role_id, [130, 132, 133, 135, 136, 137, 138])){
                $dmc_id = $user->created_by;
            }else if($user->role_id == 77 || $user->role_id == 139){
                $product_head = User::where('userId', $user->created_by)->first();
                $dmc_id = $product_head->created_by;
            }else if($user->role_id == 84 || $user->role_id == 140){
                $product_manager = User::where('userId', $user->created_by)->first();
                $product_head = User::where('userId', $product_manager->created_by)->first();
                $dmc_id = $product_head->created_by;
            }
            else{
                return redirect()->back()->with('error', 'You do not have permission to access this page.');
            }
            
            // Find the hotel
            $hotel = Hotel::find($hotelId);
            if (!$hotel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hotel not found.'
                ], 404);
            }
            
            // Check if this DMC has selected this hotel
            if (!$hotel->hasSelectedByDmc($dmc_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hotel not selected by you.'
                ], 400);
            }
            
            // Remove the DMC ID from the hotel's dmc_id array
            $hotel->removeDmcId($dmc_id);
            
            return response()->json([
                'success' => true,
                'message' => 'Hotel removed successfully!'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Hotel removal error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while removing the hotel.'
            ], 500);
        }
    }

    public function deleterate($id)
    {
        try {
            $rate = \App\Models\Rate::where('rate_id', $id)->first();
            $this->authorizeHotelRateForUser($rate, Auth::user());
            $rate->delete();
            
            return redirect()->back()->with('success', 'Rate deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete rate');
        }
    }

    /**
     * Product-head / multi-role staff who manage hotel rates for their parent DMC (users.created_by).
     */
    private function hotelRateDelegateRoleIds(): array
    {
        return [35, 130, 132, 133, 135, 136, 137, 138];
    }

    /**
     * Parent DMC userId used to scope hotel season / blackout / fair rates for the current user.
     */
    private function resolveHotelRateDmcUserId(User $user): ?int
    {
        if ((int) $user->role_id === 11 || (int) $user->role_id === 20) {
            return (int) $user->userId;
        }
        if (in_array((int) $user->role_id, $this->hotelRateDelegateRoleIds(), true)) {
            return $user->created_by ? (int) $user->created_by : null;
        }
        if ((int) $user->user_type === 2) {
            return (int) $user->userId;
        }

        return null;
    }

    private function resolveHotelRateDmcIdForStore(User $authUser, Request $request): ?int
    {
        if ((int) $authUser->role_id === 1 || (int) $authUser->role_id === 20) {
            $selected = $request->input('dmc_id');

            return $selected ? (int) $selected : null;
        }

        return $this->resolveHotelRateDmcUserId($authUser);
    }

    private function userCanManageHotelRates(User $user): bool
    {
        if ((int) $user->role_id === 1 || (int) $user->role_id === 20) {
            return true;
        }
        if ((int) $user->role_id === 11) {
            return true;
        }
        if (in_array((int) $user->role_id, $this->hotelRateDelegateRoleIds(), true)) {
            return $this->resolveHotelRateDmcUserId($user) !== null;
        }

        return (int) $user->user_type === 2;
    }

    private function authorizeHotelRateForUser(?Rate $rate, User $authUser): void
    {
        if (!$rate) {
            abort(404, 'Rate not found.');
        }
        if ((int) $authUser->role_id === 1) {
            return;
        }
        $dmcUserId = $this->resolveHotelRateDmcUserId($authUser);
        if (!$dmcUserId || (int) $rate->dmc_id !== (int) $dmcUserId) {
            abort(403, 'You do not have permission to manage this rate.');
        }
    }

    private function appendDmcLabelsToRates($rates)
    {
        return $rates->map(function ($rate) {
            if ($rate->dmc_id) {
                $dmcUser = User::where('userId', $rate->dmc_id)->first();
                if ($dmcUser) {
                    $rate->dmc_name = $dmcUser->name;
                    $rate->dmc_company = $dmcUser->company_name;
                    $rate->dmc_user_id = $dmcUser->userId;
                }
            } else {
                $rate->dmc_name = 'Unknown';
                $rate->dmc_company = 'Unknown DMC';
                $rate->dmc_user_id = 'unknown';
            }

            return $rate;
        });
    }

    /**
     * @param  string|null  $eventType  'Season', 'non_season', or null for all
     */
    private function fetchHotelRatesForUser(User $authUser, string $hotelId, ?string $eventType = null)
    {
        $query = Rate::query()->where('hotel_id', $hotelId);
        if ($eventType === 'Season') {
            $query->where('event_type', 'Season');
        } elseif ($eventType === 'non_season') {
            $query->where('event_type', '!=', 'Season');
        }

        if ((int) $authUser->role_id === 1) {
            return $this->appendDmcLabelsToRates($query->with(['user'])->get());
        }

        $dmcUserId = $this->resolveHotelRateDmcUserId($authUser);
        if (!$dmcUserId) {
            return collect();
        }

        return $query->where('dmc_id', $dmcUserId)->get();
    }

    /**
     * Product-head / product-manager roles that manage room pricing for a parent DMC.
     */
    private function roomPricingDelegateRoleIds(): array
    {
        return array_values(array_unique(array_merge(
            $this->hotelRateDelegateRoleIds(),
            [77, 84, 139, 140]
        )));
    }

    /**
     * Whether the user may bulk-import room pricing and download templates for their DMC.
     */
    private function canManageRoomPricingImport(User $user): bool
    {
        if ((int) $user->user_type === 2) {
            return true;
        }
        if ((int) $user->role_id === 11) {
            return true;
        }

        return in_array((int) $user->role_id, $this->roomPricingDelegateRoleIds(), true);
    }

    /**
     * DMC userId used for room ownership (created_by), hotel access, and imports (delegated staff use parent DMC).
     */
    private function resolveRoomPricingDmcUserId(User $user): ?int
    {
        if ((int) $user->role_id === 11) {
            return (int) $user->userId;
        }
        if (in_array((int) $user->role_id, $this->hotelRateDelegateRoleIds(), true)) {
            return $user->created_by ? (int) $user->created_by : null;
        }
        if ((int) $user->role_id === 77 || (int) $user->role_id === 139) {
            $product_head = User::where('userId', $user->created_by)->first();

            return ($product_head && $product_head->created_by) ? (int) $product_head->created_by : null;
        }
        if ((int) $user->role_id === 84 || (int) $user->role_id === 140) {
            $product_manager = User::where('userId', $user->created_by)->first();
            if (! $product_manager) {
                return null;
            }
            $product_head = User::where('userId', $product_manager->created_by)->first();

            return ($product_head && $product_head->created_by) ? (int) $product_head->created_by : null;
        }
        if ((int) $user->user_type === 2) {
            return (int) $user->userId;
        }

        return null;
    }

    /**
     * Markup settings for room pricing UI (delegated users inherit their DMC's markup).
     *
     * @return array{0: int|float, 1: int|float} [commission_type, commission_price]
     */
    private function resolveDmcMarkupForPricing(User $authUser): array
    {
        $dmcId = $this->resolveRoomPricingDmcUserId($authUser);
        $subject = $dmcId ? User::where('userId', $dmcId)->first() : null;
        if (! $subject) {
            return [0, 0];
        }
        if ((int) $subject->is_master_dmc === 1) {
            return [$subject->markup_type ?? 0, $subject->markup_price ?? 0];
        }
        $master_id = User::where('master_dmc_id', $subject->master_dmc_id)->first();
        if (! $master_id) {
            return [0, 0];
        }

        return [$master_id->markup_type ?? 0, $master_id->markup_price ?? 0];
    }

    /**
     * Display rooms import view with upload history
     */
    public function roomsImportView($hotel_id)
    {
        $user = Auth::user();

        if (! $this->canManageRoomPricingImport($user)) {
            abort(403, 'You do not have permission to import rooms.');
        }

        $dmcOwnerId = $this->resolveRoomPricingDmcUserId($user) ?? (int) $user->userId;

        // Get the specific hotel
        $hotel = Hotel::where('hotel_unique_id', $hotel_id)->first();

        if (!$hotel) {
            return redirect()->back()->with('error', 'Hotel not found.');
        }

        // Check if user has access to this hotel
        $dmcIds = is_string($hotel->dmc_id) ? json_decode($hotel->dmc_id, true) : $hotel->dmc_id;
        if (!is_array($dmcIds)) {
            $dmcIds = [];
        }

        if (!in_array($dmcOwnerId, $dmcIds)) {
            return redirect()->back()->with('error', 'You do not have access to this hotel.');
        }

        // Get recent upload history for this specific hotel
        $uploadHistory = \App\Models\UploadHistory::where('upload_type', 'rooms')
            ->where('uploaded_by', $user->userId)
            ->where('hotel_id', $hotel_id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('hotel.rooms-import', compact('uploadHistory', 'hotel'));
    }

    /**
     * Process rooms bulk upload
     */
    public function roomsImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:10240',
            'hotel_id' => 'required|string',
        ]);

        $user = Auth::user();

        if (! $this->canManageRoomPricingImport($user)) {
            return redirect()->back()->with('error', 'You do not have permission to import rooms.');
        }

        $dmcOwnerId = $this->resolveRoomPricingDmcUserId($user);
        if (! $dmcOwnerId) {
            return redirect()->back()->with('error', 'Unable to resolve DMC account for room import.');
        }

        try {
            $file = $request->file('file');
            $hotelId = $request->input('hotel_id');
            $hotelForAccess = Hotel::where('hotel_unique_id', $hotelId)->first();
            if (! $hotelForAccess) {
                return redirect()->back()->with('error', 'Hotel not found.');
            }
            $dmcIdsAccess = is_string($hotelForAccess->dmc_id) ? json_decode($hotelForAccess->dmc_id, true) : $hotelForAccess->dmc_id;
            if (! is_array($dmcIdsAccess)) {
                $dmcIdsAccess = [];
            }
            if (! in_array($dmcOwnerId, $dmcIdsAccess)) {
                return redirect()->back()->with('error', 'You do not have access to this hotel.');
            }

            $originalFileName = $file->getClientOriginalName();
            
            // Check for duplicate uploads within 5 minutes
            $cacheKey = 'room_upload_' . $user->userId . '_' . $dmcOwnerId . '_' . $hotelId . '_' . md5($originalFileName . $file->getSize());
            
            if (\Cache::has($cacheKey)) {
                return redirect()->back()->with('error', 'This file has already been uploaded recently. Please wait a few minutes before trying again.');
            }

            // Store file temporarily
            $filePath = $file->getRealPath();

            // Count total records
            $csv = array_map('str_getcsv', file($filePath));
            $totalRecords = count($csv) - 1; // Subtract header row

            // Process import (rooms stored under parent DMC userId when uploaded by delegated roles)
            $importer = new \App\Imports\RoomsImport();
            $result = $importer->import($filePath, $dmcOwnerId);

            $successCount = $result['success'];
            $errorCount = $result['errors'];
            $errorMessages = $result['error_messages'];

            // Cache this upload to prevent duplicates
            \Cache::put($cacheKey, true, now()->addMinutes(5));

            // Record upload history with hotel_id
            \App\Models\UploadHistory::createRecord(
                'rooms',
                $originalFileName,
                $originalFileName,
                $totalRecords,
                $successCount,
                $errorCount,
                $errorMessages,
                $user->userId,
                $hotelId
            );

            // Return appropriate response
            if ($errorCount > 0) {
                // Format error summary with better structure
                $errorSummary = '<div class="error-summary-list mt-2">';
                $displayErrors = array_slice($errorMessages, 0, 10);
                
                foreach ($displayErrors as $index => $error) {
                    $errorSummary .= '<div class="error-summary-item mb-1">';
                    $errorSummary .= '<i class="ri-close-circle-line text-danger me-1"></i>';
                    $errorSummary .= '<strong>' . ($index + 1) . '.</strong> ' . $error;
                    $errorSummary .= '</div>';
                }
                
                if (count($errorMessages) > 10) {
                    $errorSummary .= '<div class="error-summary-item mt-2 text-muted">';
                    $errorSummary .= '<i class="ri-more-line me-1"></i>';
                    $errorSummary .= '<em>... and ' . (count($errorMessages) - 10) . ' more errors. View upload history for complete details.</em>';
                    $errorSummary .= '</div>';
                }
                
                $errorSummary .= '</div>';
                
                if ($successCount > 0) {
                    $message = '<div class="mb-2">';
                    $message .= '<i class="ri-information-line me-1"></i>';
                    $message .= '<strong>Import Summary:</strong><br>';
                    $message .= '<span class="text-success"><i class="ri-check-line me-1"></i>' . $successCount . ' rooms updated successfully</span><br>';
                    $message .= '<span class="text-danger"><i class="ri-close-line me-1"></i>' . $errorCount . ' rows failed</span>';
                    $message .= '</div>';
                    $message .= '<div class="mb-2"><strong>Failed Rows:</strong></div>';
                    $message .= $errorSummary;
                    
                    return redirect()->back()->with('warning', $message);
                } else {
                    $message = '<div class="mb-2">';
                    $message .= '<i class="ri-error-warning-line me-1"></i>';
                    $message .= '<strong>No rooms were updated.</strong> All ' . $errorCount . ' rows had errors.';
                    $message .= '</div>';
                    $message .= '<div class="mb-2"><strong>Error Details:</strong></div>';
                    $message .= $errorSummary;
                    
                    return redirect()->back()->with('error', $message);
                }
            }

            return redirect()->back()->with('success', '<div><i class="ri-check-circle-line me-1"></i><strong>Success!</strong> ' . $successCount . ' rooms updated successfully.</div>');

        } catch (\Exception $e) {
            \Log::error('Room import error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download template with existing room data
     */
    public function roomsDownloadTemplate($hotel_id)
    {
        $user = Auth::user();

        if (! $this->canManageRoomPricingImport($user)) {
            abort(403, 'You do not have permission to download this template.');
        }

        $dmcOwnerId = $this->resolveRoomPricingDmcUserId($user) ?? (int) $user->userId;

        // Use the hotel_id from route parameter
        $hotelId = $hotel_id;

        if (!$hotelId) {
            return redirect()->back()->with('error', 'Hotel ID is required.');
        }

        // Get rooms directly using the hotel_id from the parameter
        // This hotel_id matches the hotel_id column in rooms table
        $rooms = Room::where('hotel_id', $hotelId)
            ->where('created_by', 1) // Only admin base rooms
            ->orderBy('room_type')
            ->get();

        if ($rooms->isEmpty()) {
            return redirect()->back()->with('error', 'No admin base rooms found for this hotel. Please contact administrator to create base rooms first.');
        }

        // Get hotel info for CSV filename (optional, for better naming)
        $hotel = Hotel::where('hotel_unique_id', $hotelId)->first();
        $hotelName = $hotel ? $hotel->name : 'hotel';

        // Verify user has access to this hotel
        if ($hotel) {
            $dmcIds = is_string($hotel->dmc_id) ? json_decode($hotel->dmc_id, true) : $hotel->dmc_id;
            if (!is_array($dmcIds)) {
                $dmcIds = [];
            }

            if (!in_array($dmcOwnerId, $dmcIds)) {
                return redirect()->back()->with('error', 'You do not have access to this hotel.');
            }
        }

        // Create CSV content
        $csvData = [];
        
        // Header row (removed room_id, hotel_id, base_room, rooms_only)
        $csvData[] = [
            'hotel_name',
            'room_type',
            'no_of_room',
            'dimension',
            'weekday_price',
            'weekday_cost_price',
            'weekend_price',
            'weekend_cost_price',
            'double_weekday_price',
            'double_weekday_cost_price',
            'double_weekend_price',
            'double_weekend_cost_price',
            'child_with_bed',
            'child_with_bed_cost',
            'child_without_bed',
            'child_without_bed_cost',
            'breakfast',
            'breakfast_type',
            'breakfast_price',
            'breakfast_cost_price',
            'lunch',
            'lunch_type',
            'lunch_price',
            'lunch_cost_price',
            'dinner',
            'dinner_type',
            'dinner_price',
            'dinner_cost_price',
            'breakfast_included',
        ];

        // Data rows (removed room_id, hotel_id, base_room, rooms_only)
        foreach ($rooms as $room) {
            $csvData[] = [
                $hotelName,
                $room->room_type,
                $room->no_of_room ?? 0,
                $room->dimension ?? '',
                $room->weekday_price ?? 0,
                $room->weekday_cost_price ?? '',
                $room->weekend_price ?? 0,
                $room->weekend_cost_price ?? '',
                $room->double_weekday_price ?? 0,
                $room->double_weekday_cost_price ?? '',
                $room->double_weekend_price ?? 0,
                $room->double_weekend_cost_price ?? '',
                $room->child_with_bed ?? '',
                $room->child_with_bed_cost ?? '',
                $room->child_without_bed ?? '',
                $room->child_without_bed_cost ?? '',
                $room->breakfast ? 1 : 0,
                $room->breakfast_type ?? '',
                $room->breakfast_price ?? '',
                $room->breakfast_cost_price ?? '',
                $room->lunch ? 1 : 0,
                $room->lunch_type ?? '',
                $room->lunch_price ?? '',
                $room->lunch_cost_price ?? '',
                $room->dinner ? 1 : 0,
                $room->dinner_type ?? '',
                $room->dinner_price ?? '',
                $room->dinner_cost_price ?? '',
                $room->breakfast_included ? 1 : 0,
            ];
        }

        // Generate CSV file
        $hotelNameSlug = \Str::slug($hotelName);
        $fileName = 'rooms_' . $hotelNameSlug . '_' . date('Y-m-d_His') . '.csv';
        $filePath = storage_path('app/temp/' . $fileName);

        // Create temp directory if it doesn't exist
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        // Write CSV file
        $file = fopen($filePath, 'w');
        foreach ($csvData as $row) {
            fputcsv($file, $row);
        }
        fclose($file);

        // Download and delete file
        return response()->download($filePath, $fileName)->deleteFileAfterSend(true);
    }
    

    
}