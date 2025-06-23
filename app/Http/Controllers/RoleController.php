<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use \Illuminate\Support\Facades\Auth;
use \Illuminate\Support\Facades\DB;
use App\Helpers\CommonHelper;

class RoleController extends Controller
{
    protected $auth_user;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->auth_user = Auth::user();
            return $next($request);
        });
    }

    /*
    * Display a listing of the Roles.
    * Date 07-10-2024
    */
    public function index(Request $request)
    {
        /*
        * Checked user have view roles access or not.
        */
        if (!hasPermission('view roles')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $roles = Role::where('user_type', '>=', $this->auth_user->user_type)->orderBy('id','ASC')->get();
        return view('roles.index',compact('roles'))
        ->with('i', ($request->input('page', 1) - 1) * 3);
    }

    /*
    * Show the form for creating a new role.
    * Date 07-10-2024
    */
    
    public function create()
    {
        // if (!Auth::user()->can('create roles')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }else{
            if (!hasPermission('create roles')) {
                abort(403, 'You do not have permission to access this page.');
            }
        $permission = Permission::where('status', 1)->get();
        $authUserType =  $this->auth_user->user_type; 
        $userTypes = array_filter(User::getUserTypes(), function($key) use ($authUserType) {
            return $key >= $authUserType;
        }, ARRAY_FILTER_USE_KEY);

        return view('roles.create',compact('permission','userTypes'));
        // }
    }

    /*
    * Store a newly created role.
    * Date 07-10-2024
    */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles,name',
            'user_type' => 'required',
            'role_status' => 'nullable|integer',
        ]);
        $max_role_id = Role::max('role_id') ?? 1;
        $roleId = CommonHelper::createId($max_role_id);
        while (Role::where('role_id', $roleId)->exists()) {
            $roleId = CommonHelper::createId($roleId);
        }
        $role = Role::create([
            'name' => $request->input('name'),
            'role_id' => $roleId,
            'user_type' => $request->input('user_type'),
            'is_active' => $request->input('role_status') == 1 ? 1 : 0,
        ]);
        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully');
    }

    /*
    * Show the form for editing the specified role.
    * Date 07-10-2024
    */
    public function edit($id)
    {
        // if (!Auth::user()->can('edit roles')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }else{
            if (!hasPermission('edit roles')) {
                abort(403, 'You do not have permission to access this page.');
            }
            $role = Role::find($id);
            if (!$role) {
                abort(404, 'Role not found.');
            }
            $permissions = Permission::where('status', 1)->get();
            $authUserType = $this->auth_user->user_type; 
            $userTypes = array_filter(User::getUserTypes(), function($key) use ($authUserType) {
                return $key >= $authUserType;
            }, ARRAY_FILTER_USE_KEY);
            $assignedRolePermissions = Permission::whereJsonContains('feature_roles', $id)->pluck('id')->toArray();
            
            $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id",$id)
                ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
                ->all();
        // }
        return view('roles.edit', compact('role', 'permissions', 'assignedRolePermissions', 'rolePermissions', 'userTypes'));
    }


    /*
    * Update the specified role.
    * Date 07-10-2024
    */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);
        $role = Role::findOrFail($id);
        $role->name = $request->input('name');
        $role->user_type = $request->input('user_type');
        $role->is_active = $request->input('role_status') == 1 ? 1 : 0;
        $role->save();
        // $permissions = Permission::whereIn('id', $request->input('permission'))->pluck('name')->toArray();
        // $role->syncPermissions($permissions);
        return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully');
    }

    /*
    * Soft Delete Roles.
    * Date 07-10-2024
    */
    public function destroy($id)
    {
        // if (!Auth::user()->can('delete roles')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }else{
            if (!hasPermission('delete roles')) {
                abort(403, 'You do not have permission to access this page.');
            }
            $delete =Role::where('id', $id)->delete();
            return redirect()->route('roles.index')
            ->with('success','Role deleted successfully');
        // }
    }

}
