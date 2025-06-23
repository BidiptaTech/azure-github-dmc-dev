<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;

use App\Models\HotelCategory;
use Illuminate\Http\Request;
use App\Helpers\CommonHelper;

class HotelCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $hotel_categories = [];
        $hotel_categories = HotelCategory::orderBy('id', 'desc')->get();
        
        return view('HotelCategory.index', compact('hotel_categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('HotelCategory.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles,name',
            'icon' => 'required',
        ]);
        $image = $request->file('icon');
        $storage_file = CommonHelper::image_path('file_storage', $image);

        $category_max_id = HotelCategory::max('category_id') ?? 0;
        $categoryId = CommonHelper::createId($category_max_id);
        $category = HotelCategory::create([
            'name' => $request->input('name'),
            'status' => $request->input('status'),
            'icon' => $storage_file['master_value'],
            'category_id' => $categoryId,
            
        ]);
        return redirect()->route('hotel-category.index')
            ->with('success', 'Hotel Category created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = HotelCategory::where('id',$id)->first();
        return view('HotelCategory.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = HotelCategory::where('id',$id)->first();
        //image upload using helper
        $image = $request->file('icon');
        if($image){
        $storage_file = CommonHelper::image_path('file_storage', $image);
        }
        $category->name = $request->input('name');
        $category->status = $request->input('status') == 1 ? 1 : 0;
        $category->icon = $storage_file['master_value'] ?? $category->icon;
        $category->save();

        return redirect()->route('hotel-category.index')->with('success', 'Hotel Categories updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        HotelCategory::where('category_id', $id)->delete();
        return redirect()->route('hotel-category.index')
        ->with('success', 'Hotel Category deleted successfully');
    }
}
