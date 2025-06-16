<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomsController extends Controller
{
    //
    public function index(Request $request)
    {
        // $rooms = Room::all();

        // return view('rooms.rooms',compact('rooms'));
    }

    public function create(){
        return view('roomType.addRoomsType');
    }
}
