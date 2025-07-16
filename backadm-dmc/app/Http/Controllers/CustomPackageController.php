<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomPackageController extends Controller
{
    public function create(){
        return view('custom-packages.create');
    }
}
