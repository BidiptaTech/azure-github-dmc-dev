<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Models\Category;
use App\Models\Facility;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomtypeController extends Controller
{
    //Render Index page of Room Type (roomType/roomsType.blade.php)
    public function index(Request $request)
    {
        $rooms = RoomType::orderBy('created_at', 'desc')->get();

        return view('roomType.roomsType',compact('rooms'));
    }

    //Render Add room type page (roomType/add-roomsType.blade.php)
    public function create(){

        $facilities = Facility::all();
        return view('roomType.add-roomsType', ['facilities'=>$facilities]);
    }

    //Handle Store Function Of New Room Type Details
    public function store(Request $request)
    {
        
        
        $validatedData = $this->validate($request, [
            'roomType' => 'required|string',
            'facilities' => 'required|array',
            'room_status' => 'required',
            'description' => 'required|string',
            'room_status' => 'nullable|integer',
        ]);
        
        $userId = Auth::id(); 
        $roomType = RoomType::create([
            'name' => $request->roomType, // Save the room type name
            'facilities' => json_encode($request->facilities),
            'inserted_by_user' => $userId,
            'description' => $request->description,
            'status' => $request->room_status == 1 ? 1 : 0,
        ]);
        return redirect()->route('roomType.index')
            ->with('success', 'Room Type created successfully');
    }

    public function edit($id)
    {
        $roomType = RoomType::with('hotel')->find($id); // Eager load the hotel relationship


        if(!$roomType->hotel){
            return response()->json(['error'=> 'Hotel not found']) ;
        }

        $facilities = []; // Initialize an empty array

        $facilityIds = json_decode($roomType->hotel->facilities, true); // Decode JSON string into array
        if($facilityIds){
            foreach ($facilityIds as $facilityId) {
                $facility = Facility::find($facilityId); // Use find for cleaner code
                if ($facility) {
                    $facilities[] = $facility->name; // Add the facility name to the array
                }
            }
        }
        return view('roomType.edit-roomsType', compact('roomType','facilities'));
    }

    public function update(Request $request, $id){
        $userId = Auth::id();
        
        $roomType = RoomType::where('id',$id)->first();
        $roomType->name = $request->roomType;
        $roomType->facilities = json_encode($request->facilities);
        $roomType->status = $request->room_status;
        $roomType->description = $request->description;
        $roomType->save();

        return redirect()->route('roomType.index')->with('success', 'Room Type updated successfully.');
    }

    public function destroy($id){
        $roomType = RoomType::find($id);
        $roomType->delete();
        return redirect()->route('roomType.index')->with('success','Room Type deleted successfully');
    }

    //Send Selected hotel facilities through AJAX Call
    public function getHotelFacilities($hotelId)
    {
        $hotel = Hotel::findOrFail($hotelId);
        if(!$hotel){
            return response()->json(['error'=> 'Hotel not found']) ;
        }

        if (empty(json_decode($hotel->facilities, true))) {
            return response()->json(['facilities' => []]);
        }

        $facilityNames = []; // Initialize an empty array

        $facilityIds = json_decode($hotel->facilities, true); // Decode JSON string into array

        foreach ($facilityIds as $facilityId) {
            $facility = Facility::find($facilityId); // Use find for cleaner code
            if ($facility) {
                $facilityNames[] = $facility->name; // Add the facility name to the array
            }
        }

        return response()->json([
            'success' => true,
            'facilities' => $facilityNames,
        ]);
    }

    //Show the Hotels List Based on User Input Key
    public function search(Request $request)
    {
        $query = $request->get('query');

        // Fetch hotels with matching names
        $hotels = Hotel::where('name', 'ILIKE', '%' . $query . '%')
            ->select('id', 'name', 'city')
            ->limit(10)
            ->get();
       
        return response()->json($hotels);
    }

    public function toggle(Request $request)
    {
        $roomType = RoomType::findOrFail($request->id);

        // Update the specified field with the new value
        $field = $request->field;
        if (in_array($field, ['breakfast', 'lunch', 'dinner', 'extra_bed'])) {
            $roomType->$field = $request->value;
            $roomType->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid field']);
    }

// Add this method to handle base room toggle
public function updateBaseRoom(Request $request)
{
    try {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,room_id',
            'base_room' => 'required|boolean',
        ]);
        $auth_user = Auth::user();
        $room = Room::where('room_id', $request->room_id)->first();
        
        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Room not found'], 404);
        }
        
        // If setting as base room, unset all other rooms for this hotel
        if ($request->base_room) {
            // Find all rooms in the same hotel and set base_room to false
            Room::where('hotel_id', $room->hotel_id)
                ->where('room_id', '!=', $room->room_id)
                ->where('created_by', $auth_user->userId)
                ->update(['base_room' => false]);
        }
        
        // Update the current room
        $room->base_room = $request->base_room;
        $room->save();
        
        return response()->json([
            'success' => true, 
            'message' => 'Base room status updated successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false, 
            'message' => 'Failed to update base room status: ' . $e->getMessage()
        ], 500);
    }
}

// Add this method to handle rooms only toggle
public function updateRoomsOnly(Request $request)
{
    try {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,room_id',
            'rooms_only' => 'required|boolean',
        ]);
        
        $room = Room::where('room_id', $request->room_id)->first();
        
        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Room not found'], 404);
        }
        
        // Update the rooms_only status
        $room->rooms_only = $request->rooms_only;
        $room->save();
        
        return response()->json([
            'success' => true, 
            'message' => 'Rooms only status updated successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false, 
            'message' => 'Failed to update rooms only status: ' . $e->getMessage()
        ], 500);
    }
}
}
