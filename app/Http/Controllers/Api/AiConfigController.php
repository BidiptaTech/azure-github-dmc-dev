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
        $query = AiKeyword::query()->whereNull('deleted_at');

        if (!$request->boolean('include_inactive')) {
            $query->where('status', 1);
        }

        if ($request->filled('category')) {
            $category = $request->query('category');

            if (!array_key_exists($category, AiKeyword::CATEGORIES)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid category. Allowed values: ' . implode(', ', array_keys(AiKeyword::CATEGORIES)),
                ], 422);
            }

            $query->where('category', $category);
        }

        $rows = $query->orderBy('category')->get();
        $data = $rows->map(fn (AiKeyword $row) => $row->toApiArray())->values();

        return response()->json([
            'success' => true,
            'count' => $data->count(),
            'categories' => AiKeyword::CATEGORIES,
            'data' => $data,
        ]);
    }
}
