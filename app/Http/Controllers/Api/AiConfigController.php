<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiKeyword;
use Illuminate\Http\Request;

class AiConfigController extends Controller
{
    /**
     * GET /api/v1/ai-keywords
     * Optional: ?category=hotel&include_inactive=1
     */
    public function keywords(Request $request)
    {
        
        $rows = AiKeyword::all();
        return response()->json($rows);
    }
}
