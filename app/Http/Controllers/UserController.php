<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use app\Models\User;
use App\Models\Wallet;
use App\Models\Hotel;
use App\Models\Attraction;
use App\Models\Restaurant;
use App\Models\Guide;
use App\Models\Agent;
use App\Models\Country;
use App\Models\Transaction;
use App\Helpers\CommonHelper;
use App\Models\City;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
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
        $this->middleware('auth');
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
        if($this->auth_user->user_type == 1 && ($this->auth_user->role_id == 1)){
            $users = User::with('roles')->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->user_type == 1 && ($this->auth_user->role_id == 2)){
            $users = User::with('roles')->wherenotIn('role_id', [1,2,21,22,23])->orderBy('userId', 'asc')->get();
        }elseif($this->auth_user->user_type == 1 && $this->auth_user->role_id == 3){
            // Recursive approach to get all users directly and indirectly created by this user
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
            ->get();
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }elseif($this->auth_user->user_type == 1 && $this->auth_user->role_id == 4){
            // Recursive approach to get all users directly and indirectly created by role_id 4 (AM)
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current AM user
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                ->get();
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 10 || $this->auth_user->role_id == 19){
            // Recursive approach to get all users directly and indirectly created by role_id 10
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                ->get();
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif ($this->auth_user->role_id == 11 || $this->auth_user->role_id == 20) {
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current DMC
            do {
                // Get users created by the current set of creator IDs (SH, SM, AM)
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->whereIn('role_id', [33, 34, 35, 36]) // SH, SM, AM
                    ->get();
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
                // Prepare the next level of creators (those who created these new users)
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
            // Final sorted result
            $rest_users = User::with('roles')
                    ->where('created_by', $this->auth_user->userId)
                    ->whereIn('role_id', [128, 129, 130, 131, 132, 133, 134, 135, 136, 137, 138])   
                    ->get();
            $allUsers = $allUsers->merge($rest_users);
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 24){
            // Recursive approach to get all users directly and indirectly created by role_id 24
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                        ->get();
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }elseif($this->auth_user->role_id == 28){
            // Recursive approach to get all users directly and indirectly created by role_id 28
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }elseif($this->auth_user->role_id == 30){
            // Recursive approach to get all users directly and indirectly created by role_id 30
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif ($this->auth_user->role_id == 33 || $this->auth_user->role_id == 128 || $this->auth_user->role_id == 129 || $this->auth_user->role_id == 130 || $this->auth_user->role_id == 134 || $this->auth_user->role_id == 135 || $this->auth_user->role_id == 136 || $this->auth_user->role_id == 138) { 
            // Recursive approach to get all users directly and indirectly created by role_id 33
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                ->get();
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 37){
            // Recursive approach to get all users directly and indirectly created by role_id 37
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 21){
            // Recursive approach to get all users directly and indirectly created by role_id 21
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }elseif($this->auth_user->role_id == 22){
            // Recursive approach to get all users directly and indirectly created by role_id 22
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 23){
            // Recursive approach to get all users directly and indirectly created by role_id 23
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 25){
            // Recursive approach to get all users directly and indirectly created by role_id 25
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 26){
            // Recursive approach to get all users directly and indirectly created by role_id 26
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
        
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
        
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 27){
            // Recursive approach to get all users directly and indirectly created by role_id 27
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
        
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
        
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
        
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
        
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 34 || $this->auth_user->role_id == 128 || $this->auth_user->role_id == 131 || $this->auth_user->role_id == 132 || $this->auth_user->role_id == 134 || $this->auth_user->role_id == 135 || $this->auth_user->role_id == 137 || $this->auth_user->role_id == 138){
            // Recursive approach to get all users directly and indirectly created by role_id 34
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
        
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
        
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
        
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
        
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 35 || $this->auth_user->role_id == 130 || $this->auth_user->role_id == 132 || $this->auth_user->role_id == 133 || $this->auth_user->role_id == 135 || $this->auth_user->role_id == 136 || $this->auth_user->role_id == 137 || $this->auth_user->role_id == 138){
            // Recursive approach to get all users directly and indirectly created by role_id 35
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
        
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
        
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
        
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
        
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 36 || $this->auth_user->role_id == 129 || $this->auth_user->role_id == 131 || $this->auth_user->role_id == 133 || $this->auth_user->role_id == 134 || $this->auth_user->role_id == 136 || $this->auth_user->role_id == 137 || $this->auth_user->role_id == 138){
            // Recursive approach to get all users directly and indirectly created by role_id 36
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
        
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
        
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
        
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
        
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 13){
            // Recursive approach to get all users directly and indirectly created by role_id 13
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
        
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
        
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
        
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
        
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 12 || $this->auth_user->role_id == 37 ){
            // Recursive approach to get all users directly and indirectly created by role_id 12 or 37
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
        
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
        
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
        
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
        
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 16){
            // Recursive approach to get all users directly and indirectly created by role_id 16
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
        
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
        
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
        
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
        
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 14){
            // Recursive approach to get all users directly and indirectly created by role_id 14
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
        
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
        
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
        
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
        
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 15){
            // Recursive approach to get all users directly and indirectly created by role_id 15
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
        
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
        
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
        
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
        
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 17){
            // Recursive approach to get all users directly and indirectly created by role_id 17
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
        
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
        
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
        
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
        
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 39){
            // Recursive approach to get all users directly and indirectly created by role_id 39
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
        
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
        
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
        
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
        
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 40){
            // Recursive approach to get all users directly and indirectly created by role_id 40
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
        
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
        
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
        
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
        
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 41){
            // Recursive approach to get all users directly and indirectly created by role_id 41
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
        
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
        
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
        
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
        
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 42){
            // Recursive approach to get all users directly and indirectly created by role_id 42
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
        
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
        
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
        
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
        
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 43){
            // Recursive approach to get all users directly and indirectly created by role_id 43
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
        
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
        
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
        
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
        
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 44){
            // Recursive approach to get all users directly and indirectly created by role_id 44
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
        
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
        
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
        
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
        
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 45){
            // Recursive approach to get all users directly and indirectly created by role_id 45
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
        
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
        
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
        
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
        
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 46){
            // Recursive approach to get all users directly and indirectly created by role_id 46
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
        
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
        
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
        
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
        
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }
        elseif($this->auth_user->role_id == 47){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[82])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 48){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[118])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 49){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[89])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 50){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[98])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 51){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[107])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 52){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[180])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 53){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[116])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 54){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[95])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 55){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[104])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 56){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[113])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 57){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[86])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 58){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[122])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 59){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[83])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 60){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[92])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 61){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[101])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 62){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[110])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 63){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[119])->orderBy('userId', 'asc')->get();
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
        elseif($this->auth_user->role_id == 139){
            $users = User::with('roles')->where('created_by',$this->auth_user->userId)->whereIn('role_id',[140])->orderBy('userId', 'asc')->get();
        }
        elseif($this->auth_user->role_id == 78){
            // Recursive approach to get all users directly and indirectly created by role_id 78
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
        
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
        
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
        
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
        
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
        }elseif($this->auth_user->role_id == 124 || $this->auth_user->role_id == 126){
            // Recursive approach to get all users directly and indirectly created by role_id 124 or 126
            $allUsers = collect(); // Final result
            $creatorIds = collect([$this->auth_user->userId]); // Start with current user
        
            do {
                // Get users created by the current set of creator IDs
                $newUsers = User::with('roles')
                    ->whereIn('created_by', $creatorIds)
                    ->get();
        
                // Merge the found users into the result collection
                $allUsers = $allUsers->merge($newUsers);
        
                // Prepare the next level of creators
                $creatorIds = $newUsers->pluck('userId');
            } while ($creatorIds->isNotEmpty()); // Continue if there are more creators
        
            // Final sorted result
            $users = $allUsers->sortBy('userId')->values();
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
            $country = Country::where('is_active', 1)->whereIn('name', $assignedCountries)->get(); 
        } else {
            $country = Country::where('is_active', 1)->get(); 
        }
        $countriesArray = [];
        if($this->auth_user->role_id == 1){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [2,19,21,22,23,10])
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
            ->whereIn('role_id', [24,25,26,27,11])
            ->orderBy('role_id', 'asc')
            ->get();
        }elseif($this->auth_user->role_id == 11){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [33,34,35,36, 128, 129, 130, 131, 132, 133, 134, 135, 136, 137, 138])
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
            ->whereIn('role_id', [28,11])
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
        }elseif($this->auth_user->role_id == 33){
            $roles = Role::where('is_active', 1)
            ->where('role_id', 37)
            ->orderBy('role_id', 'asc')
            ->get();
        }elseif($this->auth_user->role_id == 21){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [13,14,15,16,17,10])
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
            ->whereIn('role_id', [49,50,51,52,53,11])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 27){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [54,55,56,57,58])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 34){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [64,65,66,67,68,124])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 35){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [74,75,76,77,78,139])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 36){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [69,70,71,72,73,126])
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
        elseif($this->auth_user->role_id == 124){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [125])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 126){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [127])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 19){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [20])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 128){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [37,64,65,66,67,68])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 129){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [37,69,70,71,72,73])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 130){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [37,74,75,76,77,78,139])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 131){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [64,65,66,67,68,69,70,71,72,73])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 132){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [64,65,66,67,68,74,75,76,77,78,139])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 133){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [69,70,71,72,73,74,75,76,77,78,139])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 134){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [37,38,64,65,66,67,68,69,70,71,72,73])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 135){   
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [37,38,64,65,66,67,68,74,75,76,77,78,139])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 136){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [37,38,69,70,71,72,73,74,75,76,77,78,139])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 137){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,139])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 138){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [37,38,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,139])
            ->orderBy('role_id', 'asc')
            ->get();
            
        }
        elseif($this->auth_user->role_id == 139){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [140])
            ->orderBy('role_id', 'asc')
            ->get();
            
        }
        else{
            $roles = Role::where('is_active', 1)
            ->where('role_id', '>', $this->auth_user->role_id)
            ->orderBy('role_id', 'asc')
            ->get();
        }

        // Setup dependent data based on auth user role
        $accountManager = User::where('role_id', 3)->get();
        $dmcs = User::where('user_type', 2)->where('role_id', 11)->get();
        $master_dmc = User::where('role_id', 10)->get();
        if ($this->auth_user->role_id == 3) {
            $authUserCountry = $this->auth_user->country;
            $master_dmcs = User::where('role_id', 10)
                ->whereRaw("? = ANY(string_to_array(country, ','))", [$authUserCountry])
                ->pluck('userId');
            $master_dmc = User::whereIn('userId', $master_dmcs)->get();
        }
        
        $salesManager = User::where('role_id', 12)->get();
        $adminSalesManager = User::where('role_id', 3)->get();
        
        // Handle country access based on role
        if ($this->auth_user->role_id == 10) {
            $assignedCountries = explode(',', $this->auth_user->country);
            $country = Country::where('is_active', 1)->whereIn('name', $assignedCountries)->get();
        } else {
            $country = Country::where('is_active', 1)->get();
        }
        
        // If we're coming from role 24 setup, prepare countries array
        if(isset($countriesArray) && $this->auth_user->role_id == 24) {
            // countriesArray is already set above for role_id 24
        } else {
            $users = User::where('userId',$this->auth_user->userId)->first();
            // $countriesArray = [];
            $countriesArray = explode(',', $users->country);
        }
        
        // Format user's data for form display
        $userTypes = array_filter(User::getUserTypes(), function($key) use ($authUserType) {
            if($authUserType == 1){
                return $key >= $authUserType;
            }else{
                return $key > $authUserType;
            }
        }, ARRAY_FILTER_USE_KEY);
        // if($countriesArray){
        //     $users->country_names = $countriesArray;
        // }

        return view('users.add-user', compact(
            'adminSalesManager',
            'countriesArray',
            'country',
            'countryCodes',
            'accountManager',
            'salesManager',
            'userTypes',
            'roles',
            'user_countryCode',
            'master_dmc',
            'dmcs'
        ));
    }

    /*
    * Store a newly created User.
    * Date 08-10-2024
    */
    public function store(Request $request)
    {
            // Step 1: Validate input except for email/phone (we'll handle those manually)
            $validator = Validator::make($request->all(), [
                'salutation' => 'required|in:Mr,Mrs,Miss,Dear',
                'yourname' => 'required|max:255',
                'role' => 'required',
                'phone' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:8',
            ]);
        
            // Step 2: Check for validation errors first
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
        
            // Step 3: Custom unique check (convert email to lowercase)
            $email = strtolower(trim($request->email));
            $existingUser = User::where('email', $email)->first();
            if ($existingUser) {
                return redirect()->back()->withErrors(['email' => 'The email has already been taken.'])->withInput();
            }
        
            $deletedUser = User::withTrashed()->where('email', $email)->first();
            if ($deletedUser && $deletedUser->trashed()) {
                $deletedUser->restore();
                // Optional: Update user details after restore
                $deletedUser->update([
                    'name' => $request->yourname,
                    'phone' => $request->phone,
                    'role_id' => $request->role,
                    'password' => bcrypt($request->password),
                ]);
                return redirect()->route('users.index')->with('success', 'User restored successfully.');
            }
        
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

        
        if ($request->input('role') >= 12 && $request->input('role') <= 17) {
            $get_country_name = User::where('userId', $request->input('dmc'))->value('country') ?? $get_country_name;
        }

        if ($request->input('role') == 38) {
            $dmc_sales_manager = $this->auth_user->userId;
        }
        
        if($this->auth_user->role_id == 4 || $this->auth_user->role_id == 30 ||$this->auth_user->role_id == 11 
        ||$this->auth_user->role_id == 12 ||$this->auth_user->role_id == 33 || $this->auth_user->role_id == 37 ||$this->auth_user->role_id == 38 || $this->auth_user->role_id == 34 || $this->auth_user->role_id == 64 || $this->auth_user->role_id == 65 || $this->auth_user->role_id == 66 || $this->auth_user->role_id == 67 || $this->auth_user->role_id == 68 || $this->auth_user->role_id == 81 || $this->auth_user->role_id == 90 || $this->auth_user->role_id == 99 || $this->auth_user->role_id == 108 || $this->auth_user->role_id == 117 || $this->auth_user->role_id == 35 || $this->auth_user->role_id == 74 || $this->auth_user->role_id == 75 || $this->auth_user->role_id == 76 || $this->auth_user->role_id == 77 || $this->auth_user->role_id == 78 || $this->auth_user->role_id == 84 || $this->auth_user->role_id == 93 || $this->auth_user->role_id == 102 || $this->auth_user->role_id == 111 || $this->auth_user->role_id == 120 || $this->auth_user->role_id == 36 || $this->auth_user->role_id == 69 || $this->auth_user->role_id == 70 || $this->auth_user->role_id == 71 || $this->auth_user->role_id == 72 || $this->auth_user->role_id == 73 || $this->auth_user->role_id == 128 || $this->auth_user->role_id == 129 || $this->auth_user->role_id == 130 || $this->auth_user->role_id == 131 || $this->auth_user->role_id == 132 || $this->auth_user->role_id == 133 || $this->auth_user->role_id == 134 || $this->auth_user->role_id == 135 || $this->auth_user->role_id == 136 || $this->auth_user->role_id == 137 || $this->auth_user->role_id == 138 || $this->auth_user->role_id == 139 || $this->auth_user->role_id == 140){
            $get_country_name = $this->auth_user->country;
        }
        if($this->auth_user->role_id == 10 || $this->auth_user->role_id == 19){
            $masterDmcId = $this->auth_user->userId;
            $country_name = Country::where('name', $request->country_name)->first();
            $get_country_name = $country_name ? $country_name->name : $request->country_name;
        }
        if($this->auth_user->role_id == 24 || $this->auth_user->role_id == 28 || $this->auth_user->role_id == 26 || $this->auth_user->role_id == 49 || $this->auth_user->role_id == 50 || $this->auth_user->role_id == 51 || $this->auth_user->role_id == 52 || $this->auth_user->role_id == 53 || $this->auth_user->role_id == 27 || $this->auth_user->role_id == 54 || $this->auth_user->role_id == 55 || $this->auth_user->role_id == 56 || $this->auth_user->role_id == 57 || $this->auth_user->role_id == 58 || $this->auth_user->role_id == 25 || $this->auth_user->role_id == 59 || $this->auth_user->role_id == 60 || $this->auth_user->role_id == 61 || $this->auth_user->role_id == 62 || $this->auth_user->role_id == 63){
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

        $dmc_id = CommonHelper::getDmcId($this->auth_user);
        
        $user = User::create([
            'salutation' => $request->input('salutation'),
            'name' => $request->input('yourname'),
            'role_id' => (int) $request->input('role'), // Ensure integer
            'master_dmc_id' => isset($masterDmcId) ? (int) $masterDmcId : (int) ($request->master_dmc ?? 0), // Convert to integer
            'country' => is_array($request->country_names) ? implode(',', $request->country_names) : ($get_country_name ?? null),
            'dmcId' => $request->input('role') == 11 ? (int) $usersId : (int) ($dmc_id ?? 0), // Ensure integer
            'country_code' => (string) ($request->input('code') ?? ''), // Ensure string
            'phone' => (string) $request->input('phone'),
            'city' => $request->input('city'),
            'user_country' => $request->input('user_country'),
            'address' => $request->input('address'),
            'markup_type' => 0, 
            'guide_pax' => (int) ($request->guide_pax ?? 0), // Ensure integer
            'markup_price' => 0, // Ensure float
            'userId' => (int) $usersId, // Ensure integer
            'email' => $email, // Store email in lowercase
            'created_by' => (int) ($admin_id ?? 0), // Ensure integer
            'user_type' => (int) $user_type, // Ensure integer
            'logo' => $masterImage ?? null,
            'dmc_sales_manager' => (int) ($dmc_sales_manager ?? 0), // Ensure integer
            'assistant_manager_id' => (int) ($request->assistant_manager ?? 0), // Ensure integer
            'password' => bcrypt($request->input('password')),
            'sales_manager_admin' => (int) ($salemg_admin ?? 0), // Ensure integer
            'company_name' => $request->company_name ?? Auth::user()->company_name ?? 'Travclicks',
            'timezone' => $request->timezone ?? 'UTC',
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
    public function edit($encryptedId)
    {
        if (!hasPermission('edit users')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $id = Crypt::decrypt($encryptedId); 
        $users = User::where('userId', $id)->first();
        if(!$this->auth_user->role_id >= 37){
            abort(403, 'You do not have permission to access this page.');
        }
        
        $ipAddress = request()->ip();
        $usercountryCode = CommonHelper::getCountryInfo($ipAddress);
        $user_countryCode = $usercountryCode['country_code'];
        $countryCodes = User::countryCodes();
        $authUserType = $this->auth_user->user_type;

        // Get appropriate roles based on auth user type and role_id - matching create function logic
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
        }elseif($this->auth_user->role_id == 11){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [33,34,35,36, 128, 129, 130, 131, 132, 133, 134, 135, 136, 137, 138])
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
        }elseif($this->auth_user->role_id == 33 || $this->auth_user->role_id == 128 || $this->auth_user->role_id == 129 || $this->auth_user->role_id == 130 || $this->auth_user->role_id == 134 || $this->auth_user->role_id == 135 || $this->auth_user->role_id == 136 || $this->auth_user->role_id == 138){
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
        elseif($this->auth_user->role_id == 34 || $this->auth_user->role_id == 128 || $this->auth_user->role_id == 131 || $this->auth_user->role_id == 132 || $this->auth_user->role_id == 134 || $this->auth_user->role_id == 135 || $this->auth_user->role_id == 137 || $this->auth_user->role_id == 138){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [64,65,66,67,68,124])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 35 || $this->auth_user->role_id == 130 || $this->auth_user->role_id == 132 || $this->auth_user->role_id == 133 || $this->auth_user->role_id == 135 || $this->auth_user->role_id == 136 || $this->auth_user->role_id == 137 || $this->auth_user->role_id == 138 || $this->auth_user->role_id == 139){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [74,75,76,77,78])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        elseif($this->auth_user->role_id == 36 || $this->auth_user->role_id == 129 || $this->auth_user->role_id == 131 || $this->auth_user->role_id == 133 || $this->auth_user->role_id == 134 || $this->auth_user->role_id == 136 || $this->auth_user->role_id == 137 || $this->auth_user->role_id == 138){
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
        elseif($this->auth_user->role_id == 124){
            $roles = Role::where('is_active', 1)
            ->whereIn('role_id', [125])
            ->orderBy('role_id', 'asc')
            ->get();
        }
        else{
            $roles = Role::where('is_active', 1)
            ->where('role_id', '>', $this->auth_user->role_id)
            ->orderBy('role_id', 'asc')
            ->get();
        }

        // Setup dependent data based on auth user role
        $accountManager = User::where('role_id', 3)->get();
        $dmcs = User::where('user_type', 2)->where('role_id', 11)->get();
        $master_dmc = User::where('role_id', 10)->get();
        if ($this->auth_user->role_id == 3) {
            $authUserCountry = $this->auth_user->country;
            $master_dmcs = User::where('role_id', 10)
                ->whereRaw("? = ANY(string_to_array(country, ','))", [$authUserCountry])
                ->pluck('userId');
            $master_dmc = User::whereIn('userId', $master_dmcs)->get();
        }
        
        $salesManager = User::where('role_id', 12)->get();
        $adminSalesManager = User::where('role_id', 3)->get();
        
        // Handle country access based on role
        if ($this->auth_user->role_id == 10) {
            $assignedCountries = explode(',', $this->auth_user->country);
            $country = Country::whereIn('name', $assignedCountries)->get();
        } else {
            $country = Country::where('is_active', 1)->get();
        }
        
        // If we're coming from role 24 setup, prepare countries array
        if(isset($countriesArray) && $this->auth_user->role_id == 24) {
            // countriesArray is already set above for role_id 24
        } else {
            $countriesArray = explode(',', $users->country);
        }
        
        // Format user's data for form display
        $userTypes = array_filter(User::getUserTypes(), function($key) use ($authUserType) {
            if($authUserType == 1){
                return $key >= $authUserType;
            }else{
                return $key > $authUserType;
            }
        }, ARRAY_FILTER_USE_KEY);
        
        $users->country_names = $countriesArray;

        return view('users.edit-user', compact(
            'users',
            'countriesArray',
            'country',
            'countryCodes',
            'accountManager',
            'salesManager',
            'userTypes',
            'roles',
            'user_countryCode',
            'master_dmc',
            'dmcs',
            'adminSalesManager'
        ));
    }

    /*
    * Update the specified user.
    * Date 08-10-2024
    */
    public function update(Request $request, $id)
    {
        // Get current user role for conditional validation
        $currentUser = User::where('userId', $id)->first();
        $userRole = $currentUser->role_id;
        
        // Base validation rules
        $validationRules = [
            'salutation' => 'required|in:Mr,Mrs,Miss,Dear',
            'yourname' => 'required|max:255',
            'phone' => 'required', 
            'email' => 'required|email', // Remove unique validation since email is read-only
            'password' => 'nullable|min:8',
            'user_country' => 'required',
            'city' => 'required',
            'address' => 'required',
            'code' => 'required', // country_code
        ];
        
        // Add conditional validation rules based on role
        if ($userRole == 10 || $userRole == 19) {
            // Master DMC - require country_names array and company_name
            $validationRules['country_names'] = 'required|array|min:1';
            $validationRules['company_name'] = 'required|string|max:255';
            // Optional logo validation
            if ($request->hasFile('master_logo')) {
                $validationRules['master_logo'] = 'image|mimes:jpeg,png,jpg,gif|max:2048';
            }
        } elseif ($userRole == 11 || $userRole == 20) {
            // DMC - require master_dmc and company_name
            // Only require master_dmc if user doesn't already have one (for new DMC users)
            if (!$currentUser->master_dmc_id) {
                $validationRules['master_dmc'] = 'required|exists:users,userId';
            }
            $validationRules['company_name'] = 'required|string|max:255';
            // Only require country_name if it's being sent (created by Master DMC)
            if ($request->has('country_name')) {
                $validationRules['country_name'] = 'required|string';
            }
            // Optional logo validation
            if ($request->hasFile('master_logo')) {
                $validationRules['master_logo'] = 'image|mimes:jpeg,png,jpg,gif|max:2048';
            }
        }
        
        $this->validate($request, $validationRules);

        $user = User::where('userId', $id)->first();
        $authUserType = $this->auth_user->user_type;
        $admin_id = $this->auth_user->userId;
        $role = $user->role_id;
        
        // Determine user_type based on role - same as store function
        if ($role <= 9 || ($role >= 13 && $role <= 17) || $role == 21 || $role == 22 || $role == 23 || ($role >= 39 && $role <= 48) || $role == 79 || $role == 82 || $role == 85 || $role == 88 || $role == 91 || $role == 94 
        ||$role == 97 || $role == 100 || $role == 103 || $role == 106 || $role == 109 || $role == 112 || $role == 115 || $role == 118 || $role == 121) {
            $user_type = 1;
        } elseif($role == 10 || ($role >= 24 && $role <= 31) || ($role >= 49 && $role <= 63) || $role == 80 || $role == 83 || $role == 86 || $role == 89 || $role == 92 || $role == 95 
        ||$role == 98 || $role == 101 || $role == 104 || $role == 107 || $role == 110 || $role == 113 || $role == 116 || $role == 119 || $role == 122) {
            $user_type = 3;
        } else {
            $user_type = 2;
        }
        
        // Handle country name based on role
        $get_country_name = $request->country_name;
        if ($role == 4) {
            $get_country_name = User::where('userId', $request->input('salemg_admin'))->value('country') ?? $get_country_name;
        } else if ($role == 11) {
            $country_name = Country::where('name', $request->country_name)->first();
            $get_country_name = $country_name ? $country_name->name : $request->country_name;
        } else if ($role >= 12 && $role <= 17) {
            $get_country_name = User::where('userId', $request->dmc)->value('country') ?? $get_country_name;
        }

        // Logic for masterDmcId
        if($this->auth_user->role_id == 10){
            $masterDmcId = $this->auth_user->userId;
        } elseif($this->auth_user->role_id == 24 || $this->auth_user->role_id == 28){
            $masterDmcId = User::where('userId', $this->auth_user->userId)->value('master_dmc_id');
        } else {
            $masterDmcId = $request->master_dmc ?? $user->master_dmc_id;
        }
        
        // Handle special role conditions
        if($this->auth_user->role_id == 4 || $this->auth_user->role_id == 30 ||$this->auth_user->role_id == 11 
        ||$this->auth_user->role_id == 12 ||$this->auth_user->role_id == 33 || $this->auth_user->role_id == 37 ||$this->auth_user->role_id == 38 || $this->auth_user->role_id == 128 || $this->auth_user->role_id == 129 || $this->auth_user->role_id == 130 || $this->auth_user->role_id == 131 || $this->auth_user->role_id == 132 || $this->auth_user->role_id == 133 || $this->auth_user->role_id == 134 || $this->auth_user->role_id == 135 || $this->auth_user->role_id == 136 || $this->auth_user->role_id == 137 || $this->auth_user->role_id == 138){
            $get_country_name = $this->auth_user->country;
        }
        
        // Setup for salemg_admin based on auth role
        if($this->auth_user->role_id == 3){
            $salemg_admin = $admin_id;
        } else{
            $salemg_admin = $request->salemg_admin ?? $user->sales_manager_admin;
        }
        
        // Handle master logo upload
        $masterImage = $user->logo;
        if ($request->hasFile('master_logo')) {
            $pathData = CommonHelper::image_path('file_storage', $request->file('master_logo'));
            if (!empty($pathData['master_value'])) {
                $masterImage = $pathData['master_value'];
            }
        }
        
        // Handle dmc_sales_manager for role 38
        $dmc_sales_manager = $user->dmc_sales_manager;
        if ($request->input('role') == 38) {
            $dmc_sales_manager = $this->auth_user->userId;
        }

        // Update user with all the properly determined values
        $user->update([
            'salutation' => $request->salutation,
            'name' => $request->yourname,
            'user_type' => (int) $user_type,
            'country' => is_array($request->country_names) ? implode(',', $request->country_names) : ($get_country_name ?? $user->country),
            'master_dmc_id' => (int) $masterDmcId,
            'dmcId' => (int) ($request->dmc ?? $user->dmcId),
            'country_code' => (string) $request->code,
            'phone' => (string) $request->phone,
            'city' => $request->city,
            'user_country' => $request->user_country,
            'address' => $request->address,
            'email' => $request->email,
            'logo' => $masterImage,
            'company_name' => $request->company_name ?? $user->company_name,
            'dmc_sales_manager' => (int) $dmc_sales_manager,
            'assistant_manager_id' => (int) ($request->assistant_manager ?? $user->assistant_manager_id),
            'sales_manager_admin' => (int) $salemg_admin,
            'password' => $request->filled('password') ? bcrypt($request->password) : $user->password,
            // DMC-level settings (for DMC roles: 10, 11, 19, 20)
            'group_pax' => $request->filled('group_pax') ? (int) $request->group_pax : $user->group_pax,
            'markup_service' => $request->filled('markup_service') ? $request->markup_service : $user->markup_service,
            'markup_type' => $request->has('markup_type') && $request->markup_type !== '' ? (int) $request->markup_type : $user->markup_type,
            'markup_price' => $request->filled('markup_price') ? (int) $request->markup_price : $user->markup_price,
        ]);

        // Update role
        $role = Role::where('role_id', $request->role)->first();
        if ($role) {
            $user->syncRoles([$role->name]);
        }

        // Add debug information to session for testing
        session()->flash('debug', [
            'user_id' => $user->userId,
            'role_id' => $user->role_id,
            'updated_fields' => array_keys($user->getDirty())
        ]);
        
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

        $user = User::where('userId', $id)->first();
        if (!$user) {
            return redirect()->route('users.index')->with('error', 'User not found');
        }

        if ($user) { // Super Head
            $salesManagers = User::where('created_by', $id)->count();
            if ($salesManagers > 0) {
                return redirect()->route('users.index')->with('error', 'Cannot delete Users - has dependent please delete dependent users first');
            }
        }

        $dependencies = [
            ['model' => Hotel::class, 'field' => 'dmc_id', 'message' => 'hotels'],
            ['model' => Attraction::class, 'field' => 'dmc_id', 'message' => 'attractions'],
            ['model' => Restaurant::class, 'field' => 'dmc_id', 'message' => 'restaurants'],
            ['model' => Guide::class, 'field' => 'dmc_id', 'message' => 'guides'],
            ['model' => Agent::class, 'field' => 'sales_manager_dmc', 'message' => 'agents']
        ];
        
        foreach ($dependencies as $dependency) {
            $count = $dependency['model']::whereRaw("{$dependency['field']}::text = ?", [$id])->count();
        
            if ($count > 0) {
                return redirect()->route('users.index')
                    ->with('error', "User has dependent {$dependency['message']}, please delete dependent {$dependency['message']} first");
            }
        }        

        // If no dependencies found, proceed with deletion
        try {
            $user->delete();
            return redirect()->route('users.index')->with('success', 'User deleted successfully');
        } catch (\Exception $e) {
            return redirect()->route('users.index')->with('error', 'Something went wrong while deleting the user');
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

    /*
    * Get countries by master DMC.
    * Date 2024
    */
    public function getCountriesByMasterDmc(Request $request)
    {
        $masterDmcId = $request->master_dmc_id;
        $masterDmc = User::where('userId', $masterDmcId)->first();
        
        if ($masterDmc && $masterDmc->country) {
            $countries = explode(',', $masterDmc->country);
            $countryObjects = Country::whereIn('name', $countries)->get(['name']);
            return response()->json(['countries' => $countryObjects]);
        }
        
        return response()->json(['countries' => []]);
    }

    /*
    * Get sales managers by master DMC.
    * Date 2024
    */
    public function getSalesManagersByMasterDmc(Request $request)
    {
        $masterDmcId = $request->master_dmc_id;
        $salesManagers = User::where('role_id', 3)
                            ->where('master_dmc_id', $masterDmcId)
                            ->get(['userId', 'name']);
        
        return response()->json(['sales_managers' => $salesManagers]);
    }

    public function updateTravclicks(Request $request)
    {            
        try {
            $user = User::where('userId', $request->user_id)->first();
            if(!$user){
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            $currentUser = Auth::user();
            
            // Check permissions - only admins can update this
            if ($currentUser->role_id != 1 && $currentUser->role_id != 2 && $currentUser->role_id != 3 && $currentUser->role_id != 4 && $currentUser->role_id != 5 && $currentUser->role_id != 6 && $currentUser->role_id != 7 && $currentUser->role_id != 8 && $currentUser->role_id != 9 && $currentUser->role_id != 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update TravClicks status'
                ], 403);
            }
            
            $user->travclicks_on = $request->travclicks_on;
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'TravClicks status updated successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating travclicks status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating TravClicks status',
                'user_id' => $request->user_id,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updatePriceHide(Request $request){
        $user = User::where('userId', $request->user_id)->first();
        $user->price_hide = $request->price_hide;
        $user->save();
        return response()->json(['success' => true, 'message' => 'Price Hide status updated successfully', 'user_id' => $request->user_id, 'price_hide' => $request->price_hide]);
    }

    public function updateZone(Request $request){
        $user = User::where('userId', $request->user_id)->first();
        $user->zone_on = $request->zone_on;
        $user->save();
        return response()->json(['success' => true, 'message' => 'Zone status updated successfully', 'user_id' => $request->user_id, 'zone_on' => $request->zone_on]);
    }

    public function updateAutoCancel(Request $request){
        $user = User::where('userId', $request->user_id)->first();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found']);
        }

        // Store previous value for potential rollback
        $previousValue = $user->auto_cancel_date;

        // Update auto_cancel_date and auto_cancel_status (1 = on, 0 = off)
        $user->auto_cancel_date = $request->auto_cancel_date ?: null;
        $user->auto_cancel_status = $request->auto_cancel_date ? 1 : 0;
        $user->save();

        $message = $request->auto_cancel_date ? 
            "Auto cancel date updated to D-{$request->auto_cancel_date} successfully" : 
            "Auto cancel date cleared successfully";
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'user_id' => $request->user_id,
            'auto_cancel_date' => $request->auto_cancel_date,
            'auto_cancel_status' => $user->auto_cancel_status,
            'previous_value' => $previousValue
        ]);
    }

    public function updateGuidePax(Request $request)
    {
        $user = User::where('userId', $request->user_id)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found']);
        }

        $previousValue = $user->guide_pax;
        $guidePax = (int) ($request->guide_pax ?? 0);
        $guidePax = max(0, min(99, $guidePax)); // clamp 0-99

        $user->guide_pax = $guidePax;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Guide pax updated successfully',
            'user_id' => $request->user_id,
            'guide_pax' => $guidePax,
            'previous_value' => $previousValue,
        ]);
    }

    public function updateEmail(Request $request)
    {            
        try {
            $user = User::where('userId', $request->user_id)->first();
            if(!$user){
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            $currentUser = Auth::user();
            
            // Check permissions - only admins can update this
            if ($currentUser->role_id != 1 && $currentUser->role_id != 2 && $currentUser->role_id != 3 && $currentUser->role_id != 4 && $currentUser->role_id != 5 && $currentUser->role_id != 6 && $currentUser->role_id != 7 && $currentUser->role_id != 8 && $currentUser->role_id != 9 && $currentUser->role_id != 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update Email status'
                ], 403);
            }
            
            $user->email_on = $request->email_on;
            $user->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Email status updated successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating email status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating Email status',
                'user_id' => $request->user_id,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /*
    * Get cities by country name
    * Date: 03-06-2024
    */
    public function getCitiesByCountry(Request $request) {
        $countryName = $request->input('country');
        
        $cities = City::where('country', $countryName)
                ->select('name', 'city_id')
                ->get();
                 
        return response()->json(['cities' => $cities]);
    }

    /*
    * Get country code by country name
    * Date: 03-06-2024
    */
    public function getCountryCode(Request $request) {
        $countryName = $request->input('country');
        
        $country = Country::where('name', $countryName)->first();
        
        if ($country) {
            return response()->json([
                'success' => true,
                'country_code' => $country->country_code
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Country not found'
        ], 404);
    }

    public function profile()
    {
        $user = Auth::user();
        return view('users.profile', compact('user'));
    }

    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        $data = [
            'name' => $request->name,
            'phone' => $request->phone ?? $user->phone,
            'country' => $request->country ?? $user->country,
            'city' => $request->city ?? $user->city,
            'address' => $request->address ?? $user->address,
        ];

        if ($request->hasFile('profile_image')) {
            $pathData = CommonHelper::image_path('file_storage', $request->file('profile_image'));
            if (!empty($pathData['master_value'])) {
                $data['profile_image'] = $pathData['master_value'];
            }
        }

        $user->update($data);
        return redirect()->route('user.profile')->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Current password is required.',
            'password.required' => 'New password is required.',
            'password.min' => 'New password must be at least 8 characters.',
            'password.confirmed' => 'New password confirmation does not match.',
        ]);

        $user = Auth::user();
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->route('user.profile')
                ->withErrors(['current_password' => 'The current password is incorrect.'])
                ->withInput($request->only('current_password'))
                ->with('open_password_modal', true);
        }

        $user->update([
            'password' => bcrypt($request->password),
        ]);

        return redirect()->route('user.profile')
            ->with('success', 'Password changed successfully.');
    }
}
