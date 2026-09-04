<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tour;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\DB;
use Throwable;

class ChatController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Build Tour_Details for Firebase: selected tour columns + all orders (booking_id, type, data).
     */
    private function buildTourDetailsPayload(Tour $tour): array
    {
        $orderRows = DB::table('orders')
            ->where('tour_id', $tour->tour_id)
            ->whereNull('deleted_at')
            ->orderBy('booking_id')
            ->get(['booking_id', 'type', 'data', 'status']);

        $orders = $orderRows->map(static function ($row) {
            $data = $row->data;
            if (is_string($data)) {
                $decoded = json_decode($data, true);
                $data = json_last_error() === JSON_ERROR_NONE ? $decoded : $data;
            }

            return [
                'booking_id' => $row->booking_id,
                'type' => $row->type,
                'data' => $data,
                'status' => $row->status,
            ];
        })->values()->all();

        return [
            'destination' => $tour->destination,
            'adult' => $tour->adult,
            'child' => $tour->child,
            'infant' => $tour->infant ?? null,
            'check_in_time' => $tour->check_in_time?->toIso8601String(),
            'check_out_time' => $tour->check_out_time?->toIso8601String(),
            'male_count' => $tour->male_count ?? null,
            'female_count' => $tour->female_count ?? null,
            'child_ages' => $tour->child_ages ?? null,
            'hotel' => $tour->hotel ?? null,
            'attraction' => $tour->attraction ?? null,
            'travel' => $tour->travel ?? null,
            'restaurant' => $tour->restaurant ?? null,
            'guide' => $tour->guide ?? null,
            'port' => $tour->port ?? null,
            'tour_status' => $tour->tour_status ?? null,
            'mainguest' => $tour->mainguest ?? null,
            'orders' => $orders,
        ];
    }

    public function createChat(Request $request)
    {
        $request->validate([
            'tour_id' => 'required|integer',
        ]);

        try {
            $tour = Tour::query()
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

            $tourDetails = $this->buildTourDetailsPayload($tour);

            $result = $this->firebaseService->createChatRoom(
                $tour->tour_id,
                (int) $tour->dmc_id,
                $tourDetails
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