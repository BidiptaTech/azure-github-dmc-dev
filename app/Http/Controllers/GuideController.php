<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\Role;
use App\Models\GuideLanguage;
use App\Models\User;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Guide;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\CommonHelper;
use App\Models\Country;
use App\Models\Languages;
use App\Services\LogActivityService;
use App\Models\City;
use Illuminate\Validation\Rule;

class GuideController extends Controller
{
    /*
    * Display a listing of the Category.
    * Date 18-01-2025
    */
    public function index(Request $request)
    {
        if (!hasPermission('view guide')) {
            abort(403, 'You do not have permission to access this page.');
        }

        $user = auth()->user();
        $guides = [];
        if ($user->role_id == 4) {
            $dmc_ids = User::where('assistant_manager_id', $user->userId)->pluck('userId')->toArray();
            $guides = Guide::with('languages')->whereIn('status', [4, 5, 1])
                // ->whereIn('dmc_id', $dmc_ids)
                ->orderBy('id', 'DESC')
                ->get();
        } elseif ($user->role_id == 3) {
            $guides = Guide::orderBy('updated_at', 'desc')->whereIn('status', [5, 1])->get();
        } elseif (in_array($user->role_id, [1, 2, 23])) {
            $guides = Guide::orderBy('updated_at', 'desc')->whereIn('status', [1, 3])->get();
        }
        elseif($user->role_id == 10){
            $dmc_ids = User::where('master_dmc_id', $user->userId)->get()->pluck('userId')->toArray();
            $guides = Guide::orderBy('updated_at', 'desc')->whereIn('dmc_id', $dmc_ids)->get();

        }
        elseif ($user->role_id == 11) {
            $guides = Guide::orderBy('updated_at', 'desc')->where('dmc_id', $user->userId)->get();
        }
        elseif ($user->role_id == 20) {
            $guides = Guide::orderBy('updated_at', 'desc')->where('dmc_id', $user->userId)->get();
        }
        elseif(in_array($user->role_id, [25, 61, 101])){
            if($user->role_id == 25){
                $master_dmc_id = $user->created_by;
            }
            elseif($user->role_id == 61){
                $product_head = User::where('userId', $user->created_by)->first();
                $master_dmc_id = $product_head->created_by;
            }
            elseif($user->role_id == 101){
                $product_manager = User::where('userId', $user->created_by)->first();
                $product_head = User::where('userId', $product_manager->created_by)->first();
                $master_dmc_id = $product_head->created_by;
            }
            
            $dmc_ids = User::where('master_dmc_id', $master_dmc_id)->get()->pluck('userId')->toArray();
            $guides = Guide::orderBy('updated_at', 'desc')->whereIn('dmc_id', $dmc_ids)->get();
        } 
        elseif($user->role_id == 35){
            $guides = Guide::orderBy('updated_at', 'desc')->where('dmc_id', $user->created_by)->get();
        }
        elseif($user->role_id == 75){
            $assistant_product_manager_ids = User::where('created_by', $user->userId)->get()->pluck('userId')->toArray();
            if($assistant_product_manager_ids){
                $guides = Guide::orderBy('updated_at', 'desc')->whereIn('created_by', $assistant_product_manager_ids)->orWhere('created_by', $user->userId)->get();
            }else{
                $guides = Guide::orderBy('updated_at', 'desc')->where('created_by', $user->userId)->get();
            }
        }
        elseif($user->role_id == 102){
            $guides = Guide::orderBy('updated_at', 'desc')->where('created_by', $user->userId)->get();
        }
        // $guides = Guide::where('guide_id', 40)->get();
        return view('guides.guide', compact('guides', 'user'));
    }

    public function guideApproval(Request $request)
    {
        if (!hasPermission('view guideapproval')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $auth_user = auth()->user();
        $pendingGuides = [];
        if($auth_user->role_id == 4){
        $pendingGuides = Guide::with('user')
        ->where('status', 2)
        ->get();
        }elseif($auth_user->role_id == 3){
        $pendingGuides = Guide::with('user')
        ->where('status', 4)
        ->get();
        }elseif($auth_user->role_id == 2 || $auth_user->role_id == 1 || $auth_user->role_id == 23){
        $pendingGuides = Guide::with('user')
        ->where('status', 5)
        ->get();
        }
        return view('guides.guide-approval',compact('pendingGuides'));
    }

    public function editGuideApproval($id)
    {
        $guide = Guide::where('guide_id', $id)->first();
        $languages = GuideLanguage::where('guide_id', $id)->get();
        $languagesname = Languages::get();
        $city = City::where('country', $guide->country)->get();

        return view('guides.edit-guide-approval', compact('guide', 'languages', 'languagesname', 'city'));
    }

    public function updateGuideApproval(Request $request, $id)
{
     // Fetch the existing guide
    $guide = Guide::where('guide_id', $id)->firstOrFail();
    // Validate the request
    $validated = $request->validate([
        'salutation' => 'required|in:Mr,Mrs,Miss,Dear',
        'name' => 'required|string|max:255',
        'guide_gender' => 'required|in:Male,Female,Other',
        'contact_no' => 'required|string|min:8|max:15',
        'email' => 'required|email|max:255',
        'languages' => 'required|array',
        'about' => 'required',
        // 'license_no' => 'required|string',
        'license_no' => [
                                'required',
                                Rule::unique('guides', 'government_license_no')->ignore($guide->guide_id, 'guide_id'),
                            ],
        'license_exp_date' => 'required|date',
        'experience' => 'required|integer',
        'service_type' => 'required|integer',
        'guide_age' => 'required',
        'day_rate' => 'required|numeric',
        'night_surcharge' => 'required|numeric',
        'night_start_time' => 'required',
        'night_end_time' => 'required',
        'hourly_price' => 'required|numeric',
        'two_hour_price' => 'required|numeric',
        'four_hour_price' => 'required|numeric',
        'six_hour_price' => 'required|numeric',
        'eight_hour_price' => 'required|numeric',
        'ten_hour_price' => 'required|numeric',
        'twelve_hour_price' => 'required|numeric',
    ],[
            'license_no.unique' => 'This gov. license number is already taken by another guide.',
    ]);

    // Process license image
    $licenseImage = $guide->license_image ?? '';
    if ($request->hasFile('license_image')) {
        $pathData = CommonHelper::image_path('file_storage', $request->file('license_image'));
        if (!empty($pathData['master_value'])) {
            $licenseImage = $pathData['master_value'];
        }
    }

    // Process guide image
    $guide_image = $guide->image ?? '';
    if ($request->hasFile('master_image')) {
        $masterImagePath = CommonHelper::image_path('file_storage', $request->file('master_image'));
        if (!empty($masterImagePath['master_value'])) {
            $guide_image = $masterImagePath['master_value'];
        }
    }

    // Determine status based on user role
    $auth_user = auth()->user();
        if($auth_user->role_id == 2 || $auth_user->role_id == 1 || $auth_user->role_id == 23){
            $status = 1;
        }else{
            $status = 5;
        }
    $status = $request->has('decline_status') ? 3 : $status;

    // Update guide details
    $guide->fill([
        'salutation' => $validated['salutation'],
        'guide_gender' => $validated['guide_gender'],
        'name' => $validated['name'],
        'contact_no' => $validated['contact_no'],
        'email' => $validated['email'],
        'city' => $request->input('city'),
        'description' => $validated['about'],
        'image' => $guide_image,
        'is_active' => $request->input('guide_status') == 1 ? 1 : 0,
        'government_license_no' => $validated['license_no'],
        'license_exp_date' => $validated['license_exp_date'],
        'experience_years' => $validated['experience'],
        'license_image' => $licenseImage,
        'service_type' => $validated['service_type'],
        'guide_age' => $validated['guide_age'],
        'day_rate' => $validated['day_rate'],
        'night_surcharge' => $validated['night_surcharge'],
        'night_start_time' => $validated['night_start_time'],
        'night_end_time' => $validated['night_end_time'],
        'hourly_price' => $validated['hourly_price'],
        'two_hour_price' => $validated['two_hour_price'],
        'four_hour_price' => $validated['four_hour_price'],
        'six_hour_price' => $validated['six_hour_price'],
        'eight_hour_price' => $validated['eight_hour_price'],
        'ten_hour_price' => $validated['ten_hour_price'],
        'twelve_hour_price' => $validated['twelve_hour_price'],
        'status' => $status
    ]);

    $isSaved = $guide->save();

    // Get all existing languages for the guide
    $existingLanguages = GuideLanguage::where('guide_id', $id)->pluck('proficiency', 'language')->toArray();

    // 1. Iterate over existing languages to update or delete
    foreach ($existingLanguages as $language => $proficiency) {
        $index = array_search($language, $request->languages);
        if ($index !== false) {
            // Language exists in the new input, update proficiency
            GuideLanguage::where('guide_id', $id)
                ->where('language', $language)
                ->update(['proficiency' => $request->language_proficiency[$index]]);
        } else {
            // Language does not exist in the new input, delete it
            GuideLanguage::where('guide_id', $id)
                ->where('language', $language)
                ->delete();
        }
    }

    // 2. Insert new languages that don't exist in the database
    foreach ($request->languages as $index => $language) {
        if (!array_key_exists($language, $existingLanguages)) {
            // Generate new language_id
            $max_language_id = GuideLanguage::max('language_id') ?? 0;
            $language_id = CommonHelper::createId($max_language_id);

            // Insert new language
            if ($language && isset($request->language_proficiency[$index])) {
                GuideLanguage::create([
                    'guide_id' => $guide->guide_id,
                    'language' => $language,
                    'language_id' => $language_id,
                    'proficiency' => $request->language_proficiency[$index],
                ]);
            }
        }
    }

    // Handle response messages
    if ($request->has('decline_status')) {
        return redirect()->route('guide.approval', ['guide' => $guide->guide_id])
            ->with('error', 'Guide Declined successfully');
    } elseif ($isSaved) {
        LogActivityService::log('edit_guide', 'App\Models\Guide', $guide->guide_id, $guide);
        return redirect()->route('guide.approval', ['guide' => $guide->guide_id])
            ->with('success', 'Guide Approved successfully');
    } else {
        LogActivityService::log('edit_guide_failed', 'App\Models\Guide', $guide->guide_id, $guide);
        return redirect()->route('guide.approval')->with('error', 'Failed to approve Guide');
    }
}


    public function search(Request $request)
    {
        $search = $request->input('query');
        $guides = Guide::where('name', 'like', "%{$search}%")
                    ->select('id', 'name')  // Return only the id and name of the guides
                    ->limit(10)  // Limit the number of suggestions
                    ->get();

        return response()->json($guides);
    }


    /*
    * Show the form for creating a new category.
    * Date 18-01-2025
    */
    public function create()
    {
        if (!hasPermission('create guide')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $languages = Languages::get();
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

        if(in_array($authuser->role_id, [11, 35, 75, 102])){
            $userCountry = User::where('userId', $authuser->userId)->first()->country;
            $cities = City::where('country', $userCountry)->get();
        }
        else{
            $userCountry = '';
            $cities = [];
        }
    
        return view('guides.create-guide',compact('languages', 'dmcs', 'country', 'userCountry', 'cities'));
    }

    /*
    * Store a newly created role.
    * Date 18-01-2025
    */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $validated = $request->validate([
            'salutation' => 'required|in:Mr,Mrs,Miss,Dear',
            'guide_gender' => 'required|in:Male,Female,Other',
            'name' => 'required|string|max:255',
            'contact_no' => 'required|string|min:8|max:15',
            'email' => 'required|email|unique:guides,email',
            'license_no' => 'required|string',
            'license_exp_date' => 'required|date',
            'experience' => 'required|numeric',
            'service_type' => 'required|integer',
            'guide_age' => 'required',
            'about' => 'required|string',
            'license_image' => 'required|mimes:jpg,jpeg,png,bmp,gif,svg,webp,avif',
            'master_image' => 'required|mimes:jpg,jpeg,png,bmp,gif,svg,webp,avif',
            'day_rate' => 'nullable|numeric',
            'night_surcharge' => 'nullable|numeric',
            'night_start_time' => 'nullable|date_format:H:i',
            'night_end_time' => 'nullable|date_format:H:i',
            'hourly_price' => 'nullable|numeric',
            'two_hour_price' => 'nullable|numeric',
            'four_hour_price' => 'nullable|numeric',
            'six_hour_price' => 'nullable|numeric',
            'eight_hour_price' => 'nullable|numeric',
            'ten_hour_price' => 'nullable|numeric',
            'twelve_hour_price' => 'nullable|numeric',
            'languages' => 'required|array',
            'language_proficiency' => 'required|array',
        ]);
    
        // Generate unique guide ID
        $lastGuide = Guide::withTrashed()->orderBy('created_at', 'desc')->first();
        $guide_max_id = $lastGuide->guide_id ?? 0;
        $guideId = CommonHelper::createId($guide_max_id);
    
        while (Guide::where('guide_id', $guideId)->exists()) {
            $guideId = CommonHelper::createId($guideId);
        }
    
        // Process license image
        $licenseImage = '';
        if ($request->hasFile('license_image')) {
            $pathData = CommonHelper::image_path('file_storage', $request->file('license_image'));
            if (!empty($pathData['master_value'])) {
                $licenseImage = $pathData['master_value'];
            }
        }
    
        // Process guide image
        $guideImage = '';
        if ($request->hasFile('master_image')) {
            $pathData = CommonHelper::image_path('file_storage', $request->file('master_image'));
            if (!empty($pathData['master_value'])) {
                $guideImage = $pathData['master_value'];
            }
        }

        $auth_user = Auth::user();
        if ($auth_user->role_id == 11) {
            $dmc_id = $auth_user->userId;
            $status = 1;
        } elseif($auth_user->role_id == 4){
            $dmc_id = $request->dmc;
            $status = 1;
        } elseif($auth_user->role_id == 23){
            $dmc_id = $request->dmc;
            $status = 1;
        } elseif($auth_user->role_id == 1 || $auth_user->role_id == 2){
            $dmc_id = $request->dmc;
            $status = 1;
        } elseif(auth()->user()->role_id ==35){
            $userdmc = User::where('userId', auth()->user()->created_by)->first();
            $dmc_id = $userdmc->userId;
            $status = 1;
        } elseif(auth()->user()->role_id == 75){
            $user_product_head = User::where('userId', auth()->user()->created_by)->first();
            $user_product_head_dmc = User::where('userId', $user_product_head->created_by)->first();
            $dmc_id = $user_product_head_dmc->userId;
            $status = 1;
        } elseif(auth()->user()->role_id == 102){
            $user_product_manager = User::where('userId', auth()->user()->created_by)->first();
            $user_product_head = User::where('userId', $user_product_manager->created_by)->first();
            $user_product_head_dmc = User::where('userId', $user_product_head->created_by)->first();
            $dmc_id = $user_product_head_dmc->userId;
            $status = 1;
        } else{
            $dmc_id = $request->dmc;
            $status = 1;
        }

        // Check for existing active guide with same license for this DMC
        $existingGuide = Guide::where([
            ['government_license_no', $request->license_no],
            ['dmc_id', $dmc_id]
        ])->first();

        if ($existingGuide) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'A Guide already exists with this License for the selected DMC.');
        }

        // Check for soft-deleted guide with same license for this DMC
        $deletedGuide = Guide::withTrashed()->where([
            ['government_license_no', $request->license_no],
            ['dmc_id', $dmc_id]
        ])->first();

        if ($deletedGuide && $deletedGuide->trashed()) {
            $deletedGuide->restore();
            $deletedGuide->update([
                'salutation' => $validated['salutation'],
                'guide_gender' => $validated['guide_gender'],
                'name' => $validated['name'],
                'contact_no' => $validated['contact_no'],
                'email' => $validated['email'],
                'government_license_no' => $validated['license_no'],
                'license_exp_date' => $validated['license_exp_date'],
                'experience_years' => $validated['experience'],
                'license_image' => $licenseImage,
                'service_type' => $validated['service_type'],
                'guide_age' => $validated['guide_age'],
                'description' => $validated['about'],
                'city' => $request->city,
                'country' => $request->country,
                'image' => $guideImage,
                'day_rate' => $validated['day_rate'],
                'night_surcharge' => $validated['night_surcharge'],
                'night_start_time' => $validated['night_start_time'],
                'night_end_time' => $validated['night_end_time'],
                'hourly_price' => $validated['hourly_price'],
                'two_hour_price' => $validated['two_hour_price'],
                'four_hour_price' => $validated['four_hour_price'],
                'six_hour_price' => $validated['six_hour_price'],
                'eight_hour_price' => $validated['eight_hour_price'],
                'ten_hour_price' => $validated['ten_hour_price'],
                'twelve_hour_price' => $validated['twelve_hour_price'],
                'status' => $status,
                'dmc_id' => $dmc_id ?? 0,
                'is_active' => $request->input('guide_status') == 1 ? 1 : 0,
                'created_by' => $auth_user->userId,
            ]);

            // Handle guide languages
            GuideLanguage::where('guide_id', $deletedGuide->guide_id)->delete();
            
            $max_language_id = GuideLanguage::max('language_id') ?? 0;
            $language_id = CommonHelper::createId($max_language_id);
            
            // Insert languages
            foreach ($validated['languages'] as $index => $language) {
                GuideLanguage::create([
                    'guide_id' => $deletedGuide->guide_id,
                    'language_id' => $language_id + $index,
                    'language' => $language,
                    'proficiency' => $validated['language_proficiency'][$index],
                ]);
            }

            LogActivityService::log('restore_guide', 'App\Models\Guide', $deletedGuide->id, $deletedGuide);

            // if (in_array($auth_user->role_id, [11, 4, 3, 35, 75, 102])) {
            //     return view('guides.thankyou');
            // }

            return redirect()->route('guide.index')->with('success', 'Guide restored successfully!');
        }

        // Create and save a new guide record
        $guide = new Guide();
        $guide->guide_id = $guideId;
        $guide->salutation = $validated['salutation'];
        $guide->guide_gender = $validated['guide_gender'];
        $guide->name = $validated['name'];
        $guide->contact_no = $validated['contact_no'];
        $guide->email = $validated['email'];
        $guide->government_license_no = $validated['license_no'];
        $guide->license_exp_date = $validated['license_exp_date'];
        $guide->experience_years = $validated['experience'];
        $guide->license_image = $licenseImage;
        $guide->service_type = $validated['service_type'];
        $guide->guide_age = $validated['guide_age'];
        $guide->description = $validated['about'];
        $guide->city = $request->city;
        $guide->country = $request->country;
        $guide->image = $guideImage;
        $guide->day_rate = $validated['day_rate'];
        $guide->night_surcharge = $validated['night_surcharge'];
        $guide->night_start_time = $validated['night_start_time'];
        $guide->night_end_time = $validated['night_end_time'];
        $guide->hourly_price = $validated['hourly_price'];
        $guide->two_hour_price = $validated['two_hour_price'];
        $guide->four_hour_price = $validated['four_hour_price'];
        $guide->six_hour_price = $validated['six_hour_price'];
        $guide->eight_hour_price = $validated['eight_hour_price'];
        $guide->ten_hour_price = $validated['ten_hour_price'];
        $guide->twelve_hour_price = $validated['twelve_hour_price'];
        $guide->status = $status;
        $guide->dmc_id = $dmc_id ?? 0;
        $guide->is_active = $request->input('guide_status') == 1 ? 1 : 0;
        $guide->created_by = $auth_user->userId;
        $save = $guide->save();
        
        // Save guide before inserting into GuideLanguage
        if ($save) {
            $guideId = $guide->guide_id; // Ensure correct primary key usage
            $max_language_id = GuideLanguage::max('language_id') ?? 0;
            $language_id = CommonHelper::createId($max_language_id);
            
            // Insert languages
            foreach ($validated['languages'] as $index => $language) {
                GuideLanguage::create([
                    'guide_id' => $guideId,
                    'language_id' => $language_id + $index,
                    'language' => $language,
                    'proficiency' => $validated['language_proficiency'][$index],
                ]);
            }

            // if (in_array($auth_user->role_id, [11, 4, 3, 35, 75, 102])) {
            //     return view('guides.thankyou');
            // }

            return redirect()->route('guide.index')->with('success', 'Guide details added successfully!');
        } else {
            return redirect()->route('guide.index')->with('error', 'Failed to add guide details.');
        }
    }


    public function fetchCitiesCountries(Request $request)
    {
        $dmcId = $request->dmc_id;
        $dmc = User::where('userId', $dmcId)->first();
        $country = $dmc->country;

        $cities = City::where('country', $country)->get();
        return response()->json(['cities' => $cities, 'country' => $country]);
    }

    /*
    * Guide Edit form.
    * Date 18-01-2025
    */
    public function edit($id)
    {
        if (!hasPermission('edit guide')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $guide = Guide::where('guide_id', $id)->first();
        $city = City::where('country', $guide->country)->get();
        $languages = GuideLanguage::where('guide_id', $id)->get();
        $country = Country::where('is_active', 1)->get();
        $languagesname = Languages::get();

        $authuser = auth()->user();
        if($authuser->role_id == 4){
            $dmcs = User::where('role_id', 11)->where('country', $authuser->country)->get();
        }elseif($authuser->role_id == 3){
            $dmcs = User::where('role_id', 11)->where('country', $authuser->country)->get();
        }else{
            $dmcs = User::where('role_id', 11)->get();
        }

        return view('guides.edit-guide', compact('guide', 'languages', 'languagesname', 'country', 'city','dmcs'));
    }
    /*
    * Update the Guide details.
    * Date 18-01-2025
    */
    public function update(Request $request, $id)
    {
        $guide = Guide::where('guide_id',$id)->first();

        $user = auth()->user();
        $userRoleId = $user->role_id;
        $selectedDmcId = $request->input('dmc_id'); // Correct field (numeric)

        $licenseRule = ['required'];

        if (in_array($userRoleId, [1, 2]) && $selectedDmcId) {
            $licenseRule[] = Rule::unique('guides', 'government_license_no')
                ->where(function ($query) use ($selectedDmcId) {
                    $query->where('dmc_id', $selectedDmcId);
                })
                ->ignore($guide->guide_id, 'guide_id');
        } else {
            // Global uniqueness for DMC roles
            $licenseRule[] = Rule::unique('guides', 'government_license_no')
                ->ignore($guide->guide_id, 'guide_id');
        }

        $validated = $request->validate([
            'salutation' => 'required|in:Mr,Mrs,Miss,Dear',
            'guide_gender' => 'required|in:Male,Female,Other',
            'name' => 'required|string|max:255',
            'contact_no' => 'required|string|min:8|max:15',
            'email' => 'required|email|max:255',
            'languages' => 'array',
            'about' => 'required',
            // 'license_no' => 'required|string',
            // 'license_no' => [
            //                     'required',
            //                     Rule::unique('guides', 'government_license_no')->ignore($guide->guide_id, 'guide_id'),
            //                 ],
            'license_no' => $licenseRule,
            'license_exp_date' => 'required|date',
            'experience' => 'required|integer',
            'service_type' => 'required|integer',
            'guide_age' => 'required',
            'day_rate' => 'required|numeric',
            'night_surcharge' => 'required|numeric',
            'night_start_time' => 'required',
            'night_end_time' => 'required',
            'hourly_price' => 'required|numeric',
            'two_hour_price' => 'required|numeric',
            'four_hour_price' => 'required|numeric',
            'six_hour_price' => 'required|numeric',
            'eight_hour_price' => 'required|numeric',
            'ten_hour_price' => 'required|numeric',
            'twelve_hour_price' => 'required|numeric',
        ],[
            'license_no.unique' => 'This gov. license number is already taken by another guide.',
        ]);

        //process license image
        $licenseImage = $guide->license_image ?? '';
        if ($request->hasFile('license_image')) {
            $pathData = CommonHelper::image_path('file_storage', $request->file('license_image'));
            if (!empty($pathData['master_value'])) {
                $licenseImage = $pathData['master_value'];
            }
        }

        // Process guide image
        $guide_image = $guide->image ?? '';
        if ($request->hasFile('master_image')) {
            $masterImagePath = CommonHelper::image_path('file_storage', $request->file('master_image'));
            if (!empty($masterImagePath['master_value'])) {
                $guide_image = $masterImagePath['master_value'];
            }
        }

        $guide->salutation = $validated['salutation'];
        $guide->guide_gender = $validated['guide_gender'];
        $guide->name = $request->input('name');
        $guide->contact_no = $request->input('contact_no');
        $guide->email = $request->input('email');
        $guide->description = $request->input('about');
        $guide->city = $request->city;
        $guide->image = $guide_image;
        $guide->is_active = $request->input('guide_status') == 1 ? 1 : 0;

        $guide->government_license_no = $validated['license_no'];
        $guide->license_exp_date = $validated['license_exp_date'];
        $guide->experience_years = $validated['experience'];
        $guide->license_image = $licenseImage;
        $guide->service_type = $validated['service_type'];
        $guide->guide_age = $validated['guide_age'];

        $guide->day_rate = $validated['day_rate'];
        $guide->night_surcharge = $validated['night_surcharge'];
        $guide->night_start_time = $validated['night_start_time'];
        $guide->night_end_time = $validated['night_end_time'];
        $guide->hourly_price = $validated['hourly_price'];
        $guide->two_hour_price = $validated['two_hour_price'];
        $guide->four_hour_price = $validated['four_hour_price'];
        $guide->six_hour_price = $validated['six_hour_price'];
        $guide->eight_hour_price = $validated['eight_hour_price'];
        $guide->ten_hour_price = $validated['ten_hour_price'];
        $guide->twelve_hour_price = $validated['twelve_hour_price'];

        //Get all existing languages for the guide
        $existingLanguages = GuideLanguage::where('guide_id', $id)
        ->pluck('proficiency', 'language')->toArray();

        // 1. Iterate over existing languages to update or delete
        foreach ($existingLanguages as $language => $proficiency) {
            $index = array_search($language, $request->languages);
            if ($index !== false) {
                // Language exists in the new input, update proficiency
                GuideLanguage::where('guide_id', $id)
                    ->where('language', $language)
                    ->update(['proficiency' => $request->language_proficiency[$index]]);
            } else {
                // Language does not exist in the new input, delete it
                GuideLanguage::where('guide_id', $id)
                    ->where('language', $language)
                    ->delete();
            }
        }
        $isSaved = $guide->save();

        // 2. Insert new languages that don't exist in the database
        foreach ($request->languages as $index => $language) {
            if (!array_key_exists($language, $existingLanguages)) {
                // Generate new language_id
                $max_language_id = GuideLanguage::max('language_id') ?? 0;
                $language_id = CommonHelper::createId($max_language_id);

                // Insert new language
                if($language && $request->language_proficiency[$index])
                GuideLanguage::create([
                    'guide_id' => $guide->guide_id,
                    'language' => $language,
                    'language_id' => $language_id,
                    'proficiency' => $request->language_proficiency[$index],
                ]);
            }
        }


        if ($isSaved) {
            LogActivityService::log('edit_guide', 'App\Models\Guide', $guide->guide_id, $guide);
            return redirect()->route('guide.index')->with('success', 'Guide details updated successfully!');
        } else {
            LogActivityService::log('edit_guide_failed', 'App\Models\Guide', $guide->guide_id, $guide);
            return redirect()->route('guide.index')->with('error', 'Failed to update guide details.');
        }
    }

    /*
    * Soft Delete Guide.
    * Date 18-01-2025
    */
    public function destroy($id)
    {
        if (!hasPermission('delete guide')) {
            abort(403, 'You do not have permission to access this page.');
        }
        // Get guide and delete images from Azure
        $guide = Guide::where('guide_id', $id)->first();
        if($guide) {
            if($guide->image) {
                CommonHelper::deleteAzureImage($guide->image);
            }
            if($guide->license_image) {
                CommonHelper::deleteAzureImage($guide->license_image);
            }
        }
        $delete = Guide::where('guide_id', $id)->delete();
        return redirect()->route('guide.index')
            ->with('error', 'Guide deleted successfully');
    }

    public function approveOrDecline($guideId, Request $request)
    {

        $guide = Guide::where('guide_id', $guideId)->first();

        if ($request->action == 'approve') {
            // Handle approval logic
            $guide->approval = 1; // Example logic for approval
            $guide->save();
            
        } elseif ($request->action == 'decline') {
            // Handle decline logic
            $guide->approval = 0; // Example logic for decline
            $guide->save();
        }

        return response()->json(['success' => true]);
    }

    public function guideCalendar($guide_id)
    {
        $guide = Guide::where('guide_id', $guide_id)->first();
        $close_days = $guide->close_days;
        $close_dates = $guide->close_dates;
        return view('guides.calendar', compact('guide_id', 'guide', 'close_days', 'close_dates'));
    }

    public function guideCloseDate(Request $request) {
        $stringDates = $request->guide_holiday_dates;
        $datesArray = array_map('trim', explode(',', $stringDates));
        $datesJson = json_encode($datesArray, JSON_PRETTY_PRINT);
        $guide = Guide::where('guide_id', $request->guide_id)->first();
        $guide->close_days = $request->guide_closed_days;
        $guide->close_dates = $request->guide_holiday_dates;
        $guide->save();
        return redirect()->back()
        ->with('success', 'Close dates and holidays saved successfully');
    }
}
