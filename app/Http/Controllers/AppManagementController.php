<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AppManagementController extends Controller
{
    public function appManagementSettings()
    {
        return json_encode(['success' => true, 'message' => 'App management settings fetched successfully']);
    }
}
