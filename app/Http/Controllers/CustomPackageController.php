<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomPackageController extends Controller
{
    public function create(){
        // Replace this with: $hotels = Hotel::all(); if you have a Hotel model
        $hotels = [
            (object)['id' => 1, 'name' => 'Hotel A'],
            (object)['id' => 2, 'name' => 'Hotel B'],
            (object)['id' => 3, 'name' => 'Hotel C'],
        ];
        return view('custom-packages.create', compact('hotels'));
    }
}
