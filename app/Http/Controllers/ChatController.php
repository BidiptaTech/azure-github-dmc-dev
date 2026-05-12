<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tour;
use App\Services\FirebaseService;
use Throwable;

class ChatController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function createChat(Request $request)
    {
        $request->validate([
            'tour_id' => 'required|integer',
        ]);

        try {
            $tour = Tour::query()
                ->select(['tour_id', 'dmc_id'])
                ->where('tour_id', (int) $request->tour_id)
                ->first();

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found.',
                ], 404);
            }

            if (empty($tour->dmc_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'DMC ID is missing for this tour.',
                ], 422);
            }

            $result = $this->firebaseService->createChatRoom(
                $tour->tour_id,
                (int) $tour->dmc_id
            );

            return response()->json($result);
        } catch (Throwable $e) {
            report($e);

            $statusCode = str_contains(strtolower($e->getMessage()), 'invalid_grant') ? 422 : 500;

            return response()->json([
                'success' => false,
                'message' => 'Firebase authentication failed. Please verify service-account credentials and Firebase project settings.',
                'error' => $e->getMessage(),
            ], $statusCode);
        }
    }
}