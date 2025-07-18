<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use app\Models\User;
use App\Models\Wallet;
use App\Models\Country;
use App\Models\Transaction;
use App\Helpers\CommonHelper;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Session;
use DB;
use Auth;

class UserController extends Controller
{
    /*  
    *construct function
    */
    protected $auth_user;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->auth_user = Auth::user();
            return $next($request);
        });
    }

    /* 
    * Display a listing of the Users.
    * Date: 04-10-2024 
    */
    public function index()
    { 
        if (!hasPermission('view users')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $masterDmcId =User::where('userId',$this->auth_user->userId)->value('master_dmc_id');
        if($this->auth_user->user_type == 1 && ($this->auth_user->role_id == 1 || $this->auth_user->role_id == 2)){
            $users = User::with('roles')->orderBy('userId', 'asc')->get();
        }elseif($this->auth_user->user_type == 1 && $this->auth_user->role_id == 3){
            $countriesArray = explode(',', $this->auth_user->country); // Convert "India,Singapore" into an array
            $users = User::with('roles')
            ->where(function ($query) use ($countriesArray) {
                $query->whereIn('role_id', [4,11,24, 25, 26, 27, 28, 29, 30, 31, 32,])
                    ->whereIn('country', $countriesArray) // Match country for these role IDs
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('role_id', 10); // Allow role_id = 10 without country restriction
                    });
            })
            ->where('sales_manager_admin',$this->auth_user->userId)
            ->orderBy('userId', 'asc')
            ->get();
        }elseif($this->auth_user->user_type == 1 && $this->auth_user->role_id == 4){
            $countriesArray = explode(',', $this->auth_user->country); // Convert "India,Singapore" into an array
            $users = User::with('roles')
                ->where(function ($query) use ($countriesArray) {
                    $query->whereIn('role_id', [11, 24, 25, 26, 27, 28, 29, 30, 31, 32])
                        ->whereIn('country', $countriesArray) // Match country for these role IDs
                        ->orWhere(function ($subQuery) {
                            $subQuery->where('role_id', 10); // Allow role_id = 10 without country restriction
                        });
                })
                ->orderBy('userId', 'asc')
                ->get();
        }elseif($this->auth_user->role_id == 10){
            $users = User::with('roles')
                ->where('master_dmc_id', $this->auth_user->userId)
                ->whereIn('role_id', [11,24,25,26,27,28,29,30,31,32])
                ->orderBy('userId', 'asc')
                ->get();
        }elseif($this->auth_user->role_id == 11 || $this->auth_user->role_id == 20){
            $users = User::with('roles')
                // ->where('master_dmc_id', $this->auth_user->userId)
                ->whereIn('role_id', [33,34,35,36,128,129,130,131,132,133,134,135,136,137,138])
                ->orderBy('userId', 'asc')
                ->get();
        }elseif($this->auth_user->role_id == 24){
            $users = User::with('roles')->whereIn('role_id',[28,30,11])->where('master_dmc_id',$masterDmcId)->orderBy('userId', 'asc')->get();
        }elseif($this->auth_user->role_id == 28){
            $users = User::with('roles')->whereIn('role_id',[11,30])->where('master_dmc_id', $masterDmcId)->orderBy('userId', 'asc')->get();
        }elseif($this->auth_user->role_id == 30){
            $users = User::with('roles')->whereIn('role_id',[11])->orderBy('userId', 'asc')->get();
        }elseif($this->auth_user->role_id == 11 || $this->auth_user->role_id == 20){
            $users = User::with('roles')->whereIn('role_id',[10,33,34,35,36,128,129,130,131,132,133,134,135,136,137,138])->orderBy('userId', 'asc')->get();
        }elseif($this->auth_user->role_id == 33 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
            $users = User::with('roles')->whereIn('role_id',[37,38])->orderBy('userId', 'asc')->get();
        }elseif($this->auth_user->role_id == 37){
            $users = User::with('roles')->whereIn('role_id',[38])->orderBy('userId', 'asc')->get();
        }elseif($this->auth_user->role_id == 21){
            $users = User::with('roles')->whereIn('role_id',[13,14,15,16,17,88,97,106,79,115])->orderBy('userId', 'asc')->get();
        }elseif($this->auth_user->role_id == 22){
            $users = User::with('roles')->whereIn('role_id',[39,40,41,42,43,94,103,112,85,121])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 23){
            $users = User::with('roles')->whereIn('role_id',[44,45,46,47,48,91,100,109,82,118])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 25){
            $users = User::with('roles')->whereIn('role_id',[59,60,61,62,63,83,92,101,110,119])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 26){
            $users = User::with('roles')->whereIn('role_id',[49,50,51,52,53,89,98,107,80,116])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 27){
            $users = User::with('roles')->whereIn('role_id',[54,55,56,57,58,95,104,113,86,122])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 34 || $user->role_id == 128 || $user->role_id == 131 || $user->role_id == 132 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 137 || $user->role_id == 138){
            $users = User::with('roles')->whereIn('role_id',[64,65,66,67,68,90,99,108,81,117])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 35 || $user->role_id == 130 || $user->role_id == 132 || $user->role_id == 133 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138){
            $users = User::with('roles')->whereIn('role_id',[74,75,76,77,78,93,102,111,84,120])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 36 || $user->role_id == 129 || $user->role_id == 131 || $user->role_id == 133 || $user->role_id == 134 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138){
            $users = User::with('roles')->whereIn('role_id',[69,70,71,72,73,96,105,114,87,123])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 13){
            $users = User::with('roles')->whereIn('role_id',[88])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 12 || $this->auth_user->role_id == 37 ){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[38])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 16){
            $users = User::with('roles')->whereIn('role_id',[79])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 14){
            $users = User::with('roles')->whereIn('role_id',[97])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 15){
            $users = User::with('roles')->whereIn('role_id',[106])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 17){
            $users = User::with('roles')->whereIn('role_id',[115])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 39){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[94])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 40){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[103])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 41){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[112])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 42){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[85])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 43){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[121])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 44){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[91])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 45){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[100])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 46){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[109])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 47){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[82])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 48){
            $users = User::with('roles')->whereIn('role_id',[118])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 49){
            $users = User::with('roles')->whereIn('role_id',[89])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 50){
            $users = User::with('roles')->whereIn('role_id',[98])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 51){
            $users = User::with('roles')->whereIn('role_id',[107])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 52){
            $users = User::with('roles')->whereIn('role_id',[180])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 53){
            $users = User::with('roles')->whereIn('role_id',[116])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 54){
            $users = User::with('roles')->whereIn('role_id',[95])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 55){
            $users = User::with('roles')->whereIn('role_id',[104])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 56){
            $users = User::with('roles')->whereIn('role_id',[113])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 57){
            $users = User::with('roles')->whereIn('role_id',[86])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 58){
            $users = User::with('roles')->whereIn('role_id',[122])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 59){
            $users = User::with('roles')->whereIn('role_id',[83])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 60){
            $users = User::with('roles')->whereIn('role_id',[92])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 61){
            $users = User::with('roles')->whereIn('role_id',[101])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 62){
            $users = User::with('roles')->whereIn('role_id',[110])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 63){
            $users = User::with('roles')->whereIn('role_id',[119])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 64){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[90])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 65){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[99])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 66){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[108])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 67){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[81])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 68){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[117])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 69){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[96])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 70){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[105])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 71){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[114])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 72){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[87])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 73){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[123])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 74){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[93])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 75){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[102])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 76){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[111])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 77){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[84])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 78){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[120])->orderBy('userId', 'asc')->get();
        }
        
        else{
            $users = User::with('roles')->where('user_type', '>', $this->auth_user->user_type)->orderBy('userId', 'asc')->get();
        }
        return view('users.users',compact('users'));
    }

    /*
    * Show the form for creating a new User.
    * Date 08-10-2024
    */
    public function create()
    {
        if (!hasPermission('create users')) {
            abort(403, 'You do not have permission to access this page.');
        }
        if(!$this->auth_user->role_id >= 37){
            abort(403, 'You do not have permission to access this page.');
        }
        $ipAddress = request()->ip();
        $usercountryCode = CommonHelper::getCountryInfo($ipAddress);
        $user_countryCode = $usercountryCode['country_code'];
        $countryCodes = User::countryCodes();
        $authUserType =  $this->auth_user->user_type; 

        $userTypes = array_filter(User::getUserTypes(), function($key) use ($authUserType) {
            if($authUserType == 1){
                return $key >= $authUserType;
            }else{
                return $key > $authUserType;
            }
        }, ARRAY_FILTER_USE_KEY);

        $accountManager = User::where('role_id', 3)->get();
        $dmcs = User::where('user_type', 2)->where('role_id',11)->get();
        $master_dmc = User::where('role_id', 10)->get();
        if ($this->auth_user->role_id == 3) {
            $authUserCountry = $this->auth_user->country; // Authenticated user's single country
            $master_dmcs = User::where('role_id', 10)
                ->whereRaw("? = ANY(string_to_array(country, ','))", [$authUserCountry]) // PostgreSQL check
                ->pluck('userId');
            $master_dmc = User::whereIn('userId', $master_dmcs)->get();
        }
        
        $salesManager = User::where('role_id', 12)->get();
        $adminSalesManager = User::where('role_id',3)->get();
        if ($this->auth_user->role_id == 10) {
            $assignedCountries = explode(',', $this->auth_user->country); 
            $country = Country::whereIn('name', $assignedCountries)->get(); 
        } else {
            $country = Country::get(); 
        }
        $countriesArray = [];
        if($this->auth_user->role_id == 1){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [2,21,22,23])
            ->orderBy('role_id', 'asc')
            ->get();
        }elseif($this->auth_user->role_id == 2){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [3,10])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 3){
            $roles = Role::where('is_active', 1)
                 ->whereIn('role_id', [4,11])
                 ->orderBy('role_id', 'asc')
                 ->get();
        }elseif($this->auth_user->role_id == 4){
            $roles = Role::where('is_active', 1)
                 ->where('role_id', 11)
                 ->orderBy('role_id', 'asc')
                 ->get();
        }
        elseif($this->auth_user->role_id == 10){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [24,25,26,27])
            ->orderBy('role_id', 'asc')
            ->get();
        }elseif($this->auth_user->role_id == 11 || $this->auth_user->role_id == 20){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [33,34,35,36,128,129,130,131,132,133,134,135,136,137,138])
            ->orderBy('role_id', 'asc')
            ->get();
        }elseif($this->auth_user->role_id == 12 || $this->auth_user->role_id == 37){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [38])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 24){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [28])
            ->orderBy('role_id', 'asc')
            ->get();
            $master_dmc = User::where('userId',$this->auth_user->userId)->value('master_dmc_id');
            $countrys = User::where('userId',$master_dmc)->value('country');
            $countriesArray = explode(',', $countrys);
        }elseif($this->auth_user->role_id == 28){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [11,30])
            ->orderBy('role_id', 'asc')
            ->get();
        }elseif($this->auth_user->role_id == 30){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [11])
            ->orderBy('role_id', 'asc')
            ->get();
        }elseif($this->auth_user->role_id == 33 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
            $roles = Role::where('is_active', 1)
            ->where('role_id', 37)
            ->orderBy('role_id', 'asc')
            ->get();
        }elseif($this->auth_user->role_id == 21){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [13,14,15,16,17])
            ->orderBy('role_id', 'asc')
            ->get();
        }elseif($this->auth_user->role_id == 22){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [39,40,41,42,43])
            ->orderBy('role_id', 'asc')
            ->get();
        }elseif($this->auth_user->role_id == 23){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [44,45,46,47,48])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 25){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [59,60,61,62,63])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 26){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [49,50,51,52,53])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 27){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [54,55,56,57,58])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 34 || $user->role_id == 128 || $user->role_id == 131 || $user->role_id == 132 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 137 || $user->role_id == 138){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [64,65,66,67,68])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 35 || $user->role_id == 130 || $user->role_id == 132 || $user->role_id == 133 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [74,75,76,77,78])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 36 || $user->role_id == 129 || $user->role_id == 131 || $user->role_id == 133 || $user->role_id == 134 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [69,70,71,72,73])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 13){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [88])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 16){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [79])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 14){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [97])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 15){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [106])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 17){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [115])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 39){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [94])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 40){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [103])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 41){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [112])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 42){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [85])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 43){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [121])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 44){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [91])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 45){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [100])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 46){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [109])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 47){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [82])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 48){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [118])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 49){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [89])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 50){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [98])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 51){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [107])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 52){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [180])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 53){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [116])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 54){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [95])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 55){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [104])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 56){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [113])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 57){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [86])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 58){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [122])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 59){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [83])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 60){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [92])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 61){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [101])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 62){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [110])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 63){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [119])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 64){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [90])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 65){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [99])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 66){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [108])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 67){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [81])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 68){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [117])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 69){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [96])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 70){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [105])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 71){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [114])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 72){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [87])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 73){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [123])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 74){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [93])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 75){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [102])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 76){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [111])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 77){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [84])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 78){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [120])
            ->orderBy('role_id', 'asc')
            ->get();
        }

        else{
            $roles = Role::where('is_active', 1)
            ->where('role_id', '>', $this->auth_user->role_id)
            ->orderBy('role_id', 'asc')
            ->get();
        }
        return view('users.add-user',compact('adminSalesManager','countriesArray','country','countryCodes', 'accountManager', 'salesManager', 'userTypes', 'roles', 'user_countryCode', 'master_dmc', 'dmcs'));
    }

    /*
    * Store a newly created User.
    * Date 08-10-2024
    */
    public function store(Request $request)
    {
        $this->validate($request, [
            'salutation' => 'required|in:Mr,Mrs,Miss,Dear',
            'yourname' => 'required|max:255',
            'role' => 'required', 
            'phone' => 'required|unique:users,phone',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8', 
        ]);
        $authUserType =  $this->auth_user->user_type;
        $admin_id = $this->auth_user->userId;
        $get_country_name = $request->country_name;
        //login user role insert directly
        $auth_role_id = $this->auth_user->role_id;
        
        $user_max_id = User::withTrashed()->max('userId') ?? 1;
        $usersId = CommonHelper::createId($user_max_id);
        while (User::where('userId', $usersId)->exists()) {
            $usersId = CommonHelper::createId($usersId);
        }
        $role = $request->input('role');
        if ($role <= 9 || ($role >= 13 && $role <= 17) || $role == 21 || $role == 22 || $role == 23 || ($role >= 39 && $role <= 48) || $role == 79 || $role == 82 || $role == 85 || $role == 88 || $role == 91 || $role == 94 
        ||$role == 97 || $role == 100 || $role == 103 || $role == 106 || $role == 109 || $role == 112 || $role == 115 || $role == 118 || $role == 121) {
            $user_type = 1;
        } elseif($role == 10 || ($role >= 24 && $role <= 31) || ($role >= 49 && $role <= 63) || $role == 80 || $role == 83 || $role == 86 || $role == 89 || $role == 92 || $role == 95 
        ||$role == 98 || $role == 101 || $role == 104 || $role == 107 || $role == 110 || $role == 113 || $role == 116 || $role == 119 || $role == 122) {
            $user_type = 3;
        }else{
            $user_type = 2;
        }
        if ($request->input('role') == 4) {
            $get_country_name = User::where('userId', $request->input('salemg_admin'))->value('country') ?? $get_country_name;
        }

        if ($request->input('role') == 11 || $request->input('role') == 20) {
            $country_name = Country::where('name', $request->country_name)->first();
            $get_country_name = $country_name ? $country_name->name : $request->country_name;
        }
        if ($request->input('role') >= 12 && $request->input('role') <= 17) {
            $get_country_name = User::where('userId', $request->input('dmc'))->value('country') ?? $get_country_name;
        }

        if ($request->input('role') == 38) {
            $dmc_sales_manager = $this->auth_user->userId;
        }
        
        if($this->auth_user->role_id == 4 || $this->auth_user->role_id == 30 ||$this->auth_user->role_id == 11 
        ||$this->auth_user->role_id == 12 ||$this->auth_user->role_id == 33 || $this->auth_user->role_id == 37 ||$this->auth_user->role_id == 38 || $this->auth_user->role_id == 128 || $this->auth_user->role_id == 129 || $this->auth_user->role_id == 130 || $this->auth_user->role_id == 134 || $this->auth_user->role_id == 135 || $this->auth_user->role_id == 136 || $this->auth_user->role_id == 138){
            $get_country_name = $this->auth_user->country;
        }
        if($this->auth_user->role_id == 10){
            $masterDmcId = $this->auth_user->userId;
            $country_name = Country::where('name', $request->country_name)->first();
            $get_country_name = $country_name ? $country_name->name : $request->country_name;
        }elseif($this->auth_user->role_id == 24 || $this->auth_user->role_id == 28){
            $masterDmcId =User::where('userId',$this->auth_user->userId)->value('master_dmc_id');
            $get_country_name = $this->auth_user->country;
        }
        if($auth_role_id == 3){
            $salemg_admin = $admin_id;
            $get_country_name = $this->auth_user->country;
        }else{
            $salemg_admin = $request->salemg_admin;
        }
        $masterImage = '';
        if ($request->hasFile('master_logo')) {
            $pathData = CommonHelper::image_path('file_storage', $request->file('master_logo'));
            if (!empty($pathData['master_value'])) {
                $masterImage = $pathData['master_value'];
            }
        }
        
        $user = User::create([
            'salutation' => $request->input('salutation'),
            'name' => $request->input('yourname'),
            'role_id' => (int) $request->input('role'), // Ensure integer
            'master_dmc_id' => isset($masterDmcId) ? (int) $masterDmcId : (int) ($request->master_dmc ?? 0), // Convert to integer
            'country' => is_array($request->country_names) ? implode(',', $request->country_names) : ($get_country_name ?? null),
            'dmcId' => (int) ($request->dmc ?? 0), // Ensure integer
            'country_code' => (string) ($request->input('code') ?? ''), // Ensure string
            'phone' => (string) $request->input('phone'),
            'markup_type' => 0, 
            'markup_price' => 0, // Ensure float
            'userId' => (int) $usersId, // Ensure integer
            'email' => $request->input('email'),
            'created_by' => (int) ($admin_id ?? 0), // Ensure integer
            'user_type' => (int) $user_type, // Ensure integer
            'logo' => $masterImage ?? null,
            'dmc_sales_manager' => (int) ($dmc_sales_manager ?? 0), // Ensure integer
            'assistant_manager_id' => (int) ($request->assistant_manager ?? 0), // Ensure integer
            'password' => bcrypt($request->input('password')),
            'sales_manager_admin' => (int) ($salemg_admin ?? 0), // Ensure integer
        ]);
        
        $role = Role::where('role_id', $request->input('role'))->first();
        if ($role) {
            $user->assignRole($role->name);
        } else {
            return redirect()->back()->withErrors(['role' => 'The selected role does not exist.']);
        }
        $users = User::all();
        return redirect()->route('users.index',compact('users'))
            ->with('success', 'User created successfully');
    }

    /*
    * Show the form for editing the specified User.
    * Date 08-10-2024
    */
    public function edit($id)
    {
        if (!hasPermission('edit users')) {
            abort(403, 'You do not have permission to access this page.');
        }
        
        $users = User::where('userId', $id)->first();
        $authUserType = $this->auth_user->user_type;
        
        // Get appropriate roles based on auth user type
        if($this->auth_user->role_id == 1) {
            $roles = Role::where('is_active', 1)
                ->whereIn('role_id', [2,21,22,23])
                ->orderBy('role_id', 'asc')
                ->get();
        } elseif($this->auth_user->role_id == 2) {
            $roles = Role::where('is_active', 1)
                ->whereIn('role_id', [3,10])
                ->orderBy('role_id', 'asc')
                ->get();
        } elseif($this->auth_user->role_id == 3) {
            $roles = Role::where('is_active', 1)
                ->whereIn('role_id', [4,11])
                ->orderBy('role_id', 'asc')
                ->get();
        }
        // ... continue with other role conditions as in your create method ...

        $countryCodes = User::countryCodes();
        $master_dmc = User::whereIn('role_id', [10,19])->get();
        $dmcs = User::where('user_type', 2)->whereIn('role_id', [11,20])->get();
        $adminSalesManager = User::where('role_id', 3)->get();
        $country = Country::get();

        // Get user's existing data for dependent fields
        $userCountries = explode(',', $users->country);
        $users->country_names = $userCountries;

        return view('users.edit-user', compact(
            'users',
            'roles',
            'countryCodes',
            'master_dmc',
            'dmcs',
            'adminSalesManager',
            'country'
        ));
    }

    /*
    * Update the specified user.
    * Date 08-10-2024
    */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'salutation' => 'required|in:Mr,Mrs,Miss,Dear',
            'yourname' => 'required|max:255',
            'role' => 'required|exists:roles,role_id', 
            'phone' => 'required|max:15|unique:users,phone,' . $id . ',userId', 
            'email' => 'required|email|unique:users,email,' . $id . ',userId',
            'password' => 'nullable|min:8',
        ]);

        $user = User::where('userId', $id)->first();
        
        // Determine user type based on role
        $role = $request->input('role');
        if ($role <= 9 || ($role >= 13 && $role <= 17) || $role == 21 || $role == 22 || $role == 23) {
            $user_type = 1;
        } elseif($role == 10 || ($role >= 24 && $role <= 31)) {
            $user_type = 3;
        } else {
            $user_type = 2;
        }

        // Handle country name based on role
        if ($role == 11) {
            $country_name = Country::where('name', $request->country_name)->first();
            $get_country_name = $country_name ? $country_name->name : $request->country_name;
        } elseif ($role == 4) {
            $get_country_name = User::where('userId', $request->salemg_admin)->value('country');
        } elseif ($role >= 12 && $role <= 17) {
            $get_country_name = User::where('userId', $request->dmc)->value('country');
        }

        // Handle master logo upload
        $masterImage = $user->logo;
        if ($request->hasFile('master_logo')) {
            $pathData = CommonHelper::image_path('file_storage', $request->file('master_logo'));
            if (!empty($pathData['master_value'])) {
                $masterImage = $pathData['master_value'];
            }
        }

        // Update user
        $user->update([
            'salutation' => $request->salutation,
            'name' => $request->yourname,
            'role_id' => $role,
            'user_type' => $user_type,
            'country' => is_array($request->country_names) ? implode(',', $request->country_names) : ($get_country_name ?? $user->country),
            'master_dmc_id' => $request->master_dmc ?? $user->master_dmc_id,
            'dmcId' => $request->dmc ?? $user->dmcId,
            'country_code' => $request->code,
            'phone' => $request->phone,
            'email' => $request->email,
            'logo' => $masterImage,
            'assistant_manager_id' => $request->assistant_manager,
            'sales_manager_admin' => $request->salemg_admin ?? 0,
            'password' => $request->filled('password') ? bcrypt($request->password) : $user->password,
        ]);

        // Update role
        $role = Role::where('role_id', $request->role)->first();
        if ($role) {
            $user->syncRoles([$role->name]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully');
    }

    /*
    * Soft Delete User.
    * Date 14-10-2024
    */
    public function destroy($id)
    {
        if (!hasPermission('delete users')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $delete = User::where('id', $id)->delete();
        if($delete){
            return redirect()->route('users.index')->with('success','User deleted successfully');
        }else{
            return redirect()->route('users.index')->with('error','Something went wrong');
        }
    }

    /*
    * Dependent roles respect of usertype.
    * Date 07-10-2024
    */
    public function getRolesByUserType($userType)
    {
        $master_dmc = [];
        if ($userType == 2) {
            $master_dmc = User::where('master_dmc_id', 1)->get();
        } elseif ($userType == 3) {
            $master_dmc = User::where('user_type', 2)
                            ->where('master_dmc_id', '!=', 1)
                            ->get();
        }
        $roles = Role::where('user_type', $userType)->get();
        return response()->json([
            'roles' => $roles,
            'master_dmc' => $master_dmc,
            'user_type' => $userType
        ]);
    }

    
    /*
    * Add wallet money.
    * Date 28-10-2024
    */
    public function add_money(Request $request, $id)
    {
        $validatedData = $request->validate([
            'userId' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
        ]);
        $walletBalance = \App\Models\Transaction::where('user_id', Auth::id())->sum('amount');
        if($walletBalance < $validatedData['amount'] && $this->auth_user->id != 1){
            return redirect()->back()->with('error', 'You dont have much balance, please add balance.');
        }else{
            $user = User::where('id', $validatedData['userId'])->first();
            DB::transaction(function () use ($validatedData, $user) {
                $wallet = Wallet::create([
                    'user_id' => $user->userId, 
                    'balance' => $validatedData['amount'], 
                ]);
                if($wallet){
                    $transaction = Transaction::create([
                        'user_id' => $user->userId, 
                        'type' => 'transaction', 
                        'amount' => $validatedData['amount'], 
                        'credited_from' => $this->auth_user->id, 
                    ]);
                }
            });
        }
        return redirect()->back()->with('success', 'Amount added in wallet');
    }

    /*
    * All Transaction Record.
    * Date 28-10-2024
    */
    public function transaction()
    {
        if (!hasPermission('transaction')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $transaction = Transaction::where('user_id', $this->auth_user->userId)
        ->orWhere('credited_from', $this->auth_user->userId)
        ->get();
        return view('users.transaction',compact('transaction'));
    }

    /*
    * Admin access to Another User login.
    * Date 30-10-2024
    */
    public function loginAsUser($userId)
    {
        $currentUser = Auth::user();
        $targetUser = User::where('userId', $userId)->first();

        // Role hierarchy: lower value = higher privilege
        $roleHierarchy = [
            1 => 1,  // Admin
            3 => 2,  // Sales Head
            2 => 3,  // Sales Manager
            4 => 4,  // Assistant Manager
            5 => 5,  // DMC
        ];

        $currentRoleRank = $roleHierarchy[$currentUser->user_type] ?? 999;
        $targetRoleRank = $roleHierarchy[$targetUser->user_type] ?? 999;

        // Ensure user can only impersonate lower-ranked users
        if ($currentRoleRank > $targetRoleRank) {
            abort(403, 'Unauthorized action.');
        }

        // Get or initialize login stack
        $loginStack = Session::get('login_stack', []);

        // Save current user if not already in the stack
        if (empty($loginStack) || end($loginStack) !== $currentUser->userId) {
            $loginStack[] = $currentUser->userId; // Append user ID
            Session::put('login_stack', $loginStack);
        }
    
        // Set impersonation
        Session::put('impersonate', $targetUser->userId);
        Auth::login($targetUser);

        return redirect()->route('dashboard');
    }

    /*
    * Revert Back To Previous User.
    * Date 21-03-2025
    */
    public function revertToPreviousUser()
    {
        $loginStack = Session::get('login_stack', []);

        if (empty($loginStack)) {
            return redirect()->route('dashboard')->with('error', 'No previous user found.');
        }

        // Pop the last user ID from the stack
        $previousUserId = array_pop($loginStack);
        Session::put('login_stack', $loginStack);

        // Remove impersonation if stack is empty
        if (empty($loginStack)) {
            Session::forget('impersonate');
        }

        // Log in as the previous user
        $previousUser = User::where('userId', $previousUserId)->first();
        Auth::login($previousUser);

        return redirect()->route('dashboard')->with('success', 'Reverted to previous user.');
    }

    /*
    * Logout .
    * Date 30-10-2024
    */
    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        // Redirect to the login page
        return redirect()->route('login')->with('message', 'You have been logged out successfully.');
    }

    /*
    * Get country respect of master dmc.
    * Ajax Call Function 
    * Date 27-02-2024
    */
    public function getCountries($masterDmcId) {
        $masterDmc = User::where('userId',$masterDmcId)->first();
        $countries = $masterDmc ? explode(',', $masterDmc->country) : []; // Assuming countries are stored as CSV
        return response()->json(['countries' => $countries]);
    }

    /*
    * country Selected master dmc.
    * Date 21-03-2024
    */
    public function selectedCountry($master) {
        $user = User::where('userId', $master)->value('country'); // Get the country string
        if ($user) {
            $countriesArray = explode(',', $user); // Convert to an array
            $country = Country::whereIn('name', $countriesArray)->first(); // Fetch first matching country
        }
        $commission = $country->commission_percentage;
        $gateway_percentage = $country->gateway_percentage;
        return response()->json(['markup_percentage' => $commission,'gateway_percentage' => $gateway_percentage]);
    }
    
    /*
    * get Assistant manager dmc.
    * Date 21-03-2024
    */
    public function getAssistantManagers($country)
    {
        $assistantManagers = User::where('country', $country)
                                ->where('role_id', 4) // Adjust this based on your role system
                                ->get(['id', 'name']);
        return response()->json(['assistant_managers' => $assistantManagers]);
    }
}
