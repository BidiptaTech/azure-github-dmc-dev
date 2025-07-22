<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BedMaster;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Facility;
use App\Models\Role;
use App\Models\Room;
use App\Models\Bed;
use App\Models\User;
use App\Models\Hotel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\CommonHelper;

class BedsController extends Controller
{
    public function index()
    {
        if (!hasPermission('view bed')) {
            abort(403, 'You do not have permission to access this page.');
        }
        $beds = BedMaster::with('hotel')->orderBy('created_at', 'desc')->get();
        return view('beds.index', compact('beds'));
    }

    /*
    * Show the form for creating a new category.
    * Date 06-11-2024
    */
    public function create($id)
    {
        // if (!hasPermission('create bed')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        $auth_user = Auth::user();
        
        // Get the specific hotel by ID
        $hotel = Hotel::where('hotel_unique_id', $id)->first();
        
        if (!$hotel) {
            abort(404, 'Hotel not found.');
        }

        $beds = BedMaster::where('hotel_id', $id)->get();
        return view('beds.add-beds', compact('beds', 'hotel', 'id'));
    }

    /*
    * Store a newly created bed.
    * Date 31-12-2024
    */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'bed_type' => 'required|string|max:255',
            'king_beds' => 'required|integer|min:0|max:4',
            'queen_beds' => 'required|integer|min:0|max:4',
            'twin_beds' => 'required|integer|min:0|max:4', 
            'single_bed' => 'required|integer|min:0|max:4', 
            'bunk_beds' => 'required|integer|min:0|max:4', 
            'bed_status' => 'nullable|integer',
            // 'room_category_id' => 'required', 
        ]);
        $hotel_id = $request->hotel_id;
        $bedId = BedMaster::max('bedId') ?? 1;
        $categoryId = CommonHelper::createId($bedId);
        while (BedMaster::where('bedId', $bedId)->exists()) {
            $bedId = CommonHelper::createId($bedId);
        }
        BedMaster::create([
            'name' => $validatedData['bed_type'],
            'no_of_king_bed' => $validatedData['king_beds'],
            'no_of_queen_bed' => $validatedData['queen_beds'],
            'no_of_twin_bed' => $validatedData['twin_beds'],
            'no_of_bunk_bed' => $validatedData['bunk_beds'],
            'no_of_single_bed' => $validatedData['single_bed'],
            'bedId' => $bedId,
            'hotel_id' => $hotel_id,
            'is_active' => $request->bed_status
            // 'room_id' =>  $validatedData['room_category_id'],
        ]);
        return redirect()->route('beds.create', $hotel_id)->with('success', 'Bed information added successfully.');
    }

    /*
    * Show the form fors editing the specified bed.
    * Date 07-10-2024
    */
    public function edit($id)
    {
        // if (!hasPermission('edit bed')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        $auth_user = Auth::user();
        if($auth_user->user_type == 1){
            $hotel = Hotel::get();
        }elseif($auth_user->user_type == 2){
            $hotel = Hotel::whereJsonContains('dmc_id', $auth_user->userId)->get();
        }else {
            $hotel = Hotel::whereJsonContains('dmc_id', $auth_user->userId)->get();
        }
        $bed = BedMaster::where('bedId',$id)->first();
        $room = Room::where('room_id', $bed->room_id)->first();
        return view('beds.edit-beds', compact('bed','hotel','room'));
    }
    /*
    * Update the specified bed.
    * Date 07-10-2024
    */
    public function update(Request $request)
    {
        $bedId = $request->bed_id;
        $bed = BedMaster::where('bedId', $bedId)->first();
        $validatedData = $request->validate([
            'bed_type' => 'required|string|max:255', 
        ]);

        // Get the old name before updating
        $oldBedName = $bed->name;
        $newBedName = $validatedData['bed_type'];
        // Update the bed master record
        $bed->update([
            'name' => $newBedName,
            'no_of_king_bed' => $request->king_beds ?? 0,
            'no_of_queen_bed' => $request->queen_beds ?? 0,
            'no_of_twin_bed' => $request->twin_beds ?? 0,
            'no_of_bunk_bed' => $request->bunk_beds ?? 0,
            'hotel_id' => $request->hotel_id,
            'no_of_single_bed' => $request->single_bed ?? 0,
            'is_active' => $request->bed_status == 1 ? 1 : 0,
        ]);

        // Update room_type in beds table if the name has changed
        if ($oldBedName !== $newBedName) {
            \App\Models\Bed::where('bed_master_id', $bedId)
                ->update(['room_type' => $newBedName]);
        }

        return redirect()->route('beds.create', $bed->hotel_id)->with('success', 'Bed information updated successfully.');
    }

    /*
    * Soft Delete Bed.
    * Date 07-10-2024
    */
    public function destroy($id)
    {
        // if (!hasPermission('delete bed')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        $bedmaster = BedMaster::where('bedId', $id)->first();
        if (!$bedmaster) {
            return redirect()->route('beds.create', $bedmaster->hotel_id)->with('error', 'Bed Type not found!');
        }
        $bedCount = Bed::where('bed_id', $id)->where('is_active', 1)->count();
        if ($bedCount > 0) {
            return redirect()->route('beds.create', $bedmaster->hotel_id)
                            ->with('denied', 'Either cannot delete or bed type not found!');
        } else {
            $bedmaster->delete();
            return redirect()->route('beds.create', $bedmaster->hotel_id)
                            ->with('success', 'Bed Type deleted successfully');
        }
    }

}
