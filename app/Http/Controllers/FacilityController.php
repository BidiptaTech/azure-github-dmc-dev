<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Facility;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\CommonHelper;

class FacilityController extends Controller
{
    /*
    * Display a listing of the Category.
    * Date 06-11-2024
    */
    public function index(Request $request)
    {
        if (!hasPermission('view facility')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $facilities = Facility::with('categories')->get(); 
        return view('facility.facility',compact('facilities'));
    }

    /*
    * Show the form for creating a new category.
    * Date 06-11-2024
    */
    public function create()
    {
        if (!hasPermission('create facility')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $categories = Category::get();
        return view('facility.add-facility', compact('categories'));
    }

    /*
    * Store a newly created role.
    * Date 07-10-2024
    */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'category_type' => 'required',
            'facility_status' => 'nullable|integer',
        ]);
        
        $existingFacility = Facility::where('name', $request->input('name'))
            ->where('category_id', $request->input('category_type'))
            ->first();
    
        if ($existingFacility) {
            return redirect()->back()
                ->with('error', 'Facility with this name and category already exists.');
        }
        
        $image = $request->file('icon');
        
        $storage_file = CommonHelper::image_path('file_storage', $image);

        // $facility_max_id = Facility::max('facilityId') ?? 0;
        // $facilityId = CommonHelper::createId($facility_max_id);
        // while (Facility::where('facilityId', $facilityId)->exists()) {
        //     $facilityId = CommonHelper::createId($facilityId);
        // }
        
        $newFacility = Facility::create([
            'name' => $request->input('name'),
            'category_id' => $request->input('category_type'),
            'icon' => $storage_file['master_value'],
            'status' => $request->input('facility_status'),
            'is_chargeable' => $request->input('chargeable'),
            'chargable_comment' => $request->input('comment'),
            'inserted_by_user' => Auth::user()->userId,
            // 'facilityId' => $facilityId,
        ]);
        $newFacility->refresh();
        if ($newFacility) {
            return redirect()->route('facility.index')
                ->with('success', 'Facility created successfully');
        } else {
            return redirect()->route('facility.index')
                ->with('error', 'An error occurred while saving the facility details.');
        }
    }
    

    /*
    * Show the form for editing the specified role.
    * Date 07-10-2024
    */
    public function edit($id)
    {
        if (!hasPermission('edit facility')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $facility = Facility::where('id',$id)->first();
        $categories = Category::get();
        $currentCategory = Category::where('id',$facility->category_id)->first();
        return view('facility.edit-facility', compact('facility', 'categories', 'currentCategory'));
    }
    /*
    * Update the specified role.
    * Date 07-10-2024
    */
    public function update(Request $request, $id)
    {
        $image = $request->file('icon');
        if($image){
        $storage_file = CommonHelper::image_path('file_storage', $image);
        }
        
        $facility = Facility::where('id',$id)->first();
        $facility->name = $request->input('name');
        $facility->category_id = $request->input('category_type');
        $facility->status = $request->input('facility_status') == 1 ? 1 : 0;
        $facility->is_chargeable = $request->input('chargeable');
        $facility->chargable_comment = $request->input('comment');
        $facility->inserted_by_user = Auth::user()->userId;
        $facility->icon = $storage_file['master_value'] ?? $facility->icon;
        $facility->save();
        return redirect()->route('facility.index')->with('success', 'Facility updated successfully.');
    }

    /*
    * Soft Delete Roles.
    * Date 07-10-2024
    */
    public function destroy($id)
    { 
        if (!hasPermission('delete facility')) {
            abort(403, 'You do not have permission to access this page.');
        }
        
        // Get facility and delete image from Azure
        $facility = Facility::where('id', $id)->first();
        if($facility && $facility->icon) {
            CommonHelper::deleteAzureImage($facility->icon);
        }
        
        $delete = Facility::where('id', $id)->delete();
        return redirect()->route('facility.index')
        ->with('success','Facility deleted successfully');
    
    }
}
