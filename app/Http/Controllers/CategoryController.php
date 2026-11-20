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
use Illuminate\Support\Facades\Mail;
use App\Mail\DmcMail;

class CategoryController extends Controller
{
    /*
    * Display a listing of the Category.
    * Date 06-11-2024
    */
    public function index(Request $request)
    {
        if (!hasPermission('view category')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $categories = Category::orderBy('created_at', 'desc')->get();
        return view('category.index', compact('categories'));
    }

    /*
    * Show the form for creating a new category.
    * Date 06-11-2024
    */
    public function create()
    {
        if (!hasPermission('create category')) {
            abort(403, 'You do not have permission to access this page.');
        }
        return view('category.create');
    }

    /*
    * Store a newly created role.
    * Date 07-10-2024
    */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles,name',
            'icon' => 'required',
        ]);
        $image = $request->file('icon');
        $storage_file = CommonHelper::image_path('file_storage', $image);

        // $category_max_id = Category::withTrashed()->max('category_id') ?? 0;
        // $categoryId = CommonHelper::createId($category_max_id);
        $newCategory = Category::create([
            'name' => $request->input('name'),
            'status' => $request->input('category_status'),
            'icon' => $storage_file['master_value'],
            // 'category_id' => $categoryId,
        ]);
        $newCategory->refresh();
        if ($newCategory) {
            return redirect()->route('category.index')
                ->with('success', 'Category created successfully');
        } else {
            return redirect()->route('category.index')
                ->with('error', 'An error occurred while saving the category details.');
        }
        // if($category){
        //     Mail::to('niawaj3@gmail.com')->send(new DmcMail($category->name));
        // }
    }

    /*
    * Show the form fors editing the specified role.
    * Date 07-10-2024
    */
    public function edit($id)
    {
        if (!hasPermission('edit category')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $category = Category::where('category_id',$id)->first();
        return view('category.edit', compact('category'));
    }
    /*
    * Update the specified role.
    * Date 07-10-2024
    */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|image',
        ]);

        $category = Category::where('category_id', $id)->firstOrFail();

        $category->name = $request->input('name');
        $category->status = $request->input('category_status') == 1 ? 1 : 0;

        if ($request->hasFile('icon')) {
            $storage_file = CommonHelper::image_path('file_storage', $request->file('icon'));
            $category->icon = $storage_file['master_value'];
        }

        $category->save();

        return redirect()->route('category.index')->with('success', 'Categories updated successfully.');
    }

    /*
    * Soft Delete Roles.
    * Date 07-10-2024
    */
    public function destroy($id)
    {
        if (!hasPermission('delete category')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $facility = Facility::where('category_id',$id)->get();
        
        if(count($facility) == 0){
            // Get category and delete image from Azure
            $category = Category::where('category_id', $id)->first();
            if($category && $category->icon) {
                CommonHelper::deleteAzureImage($category->icon);
            }
            
            $delete = Category::where('category_id', $id)->delete();
            return redirect()->route('category.index')
            ->with('success','Category deleted successfully');
        }
        else{
            return redirect()->route(route: 'category.index')
            ->with('denied','This Category is in use, Cannot be delete!');
        }
    }

}
