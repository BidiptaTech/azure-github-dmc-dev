<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Driver;
use App\Models\Guide;
use App\Models\Jobsheet;
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

    /**
     * Build Firebase chat fields from jobsheets for this tour:
     * ID/{order_id}/{driverId|guideId}, shared dmc_id, and driver/guide emails.
     */
    private function buildJobsheetChatPayload(int $tourId): array
    {
        $jobsheets = Jobsheet::query()
            ->where('tour_id', $tourId)
            ->get(['order_id', 'driver_id', 'guide_id', 'dmc_id']);

        $idMap = [];
        $driverIds = [];
        $guideIds = [];
        $dmcId = null;

        foreach ($jobsheets as $row) {
            if ($dmcId === null && !empty($row->dmc_id)) {
                $dmcId = (int) $row->dmc_id;
            }

            $assignment = [];

            if (!empty($row->driver_id)) {
                $driverId = (int) $row->driver_id;
                $assignment['driverId'] = $driverId;
                $driverIds[] = $driverId;
            }

            if (!empty($row->guide_id)) {
                $guideId = (int) $row->guide_id;
                $assignment['guideId'] = $guideId;
                $guideIds[] = $guideId;
            }

            $orderKey = $row->order_id === null ? '' : trim((string) $row->order_id);
            if ($orderKey === '' || empty($assignment)) {
                continue;
            }

            $idMap[$orderKey] = array_merge($idMap[$orderKey] ?? [], $assignment);
        }

        $emails = [];

        if (!empty($driverIds)) {
            $emails = array_merge(
                $emails,
                Driver::query()
                    ->whereIn('driver_id', array_values(array_unique($driverIds)))
                    ->pluck('email')
                    ->all()
            );
        }

        if (!empty($guideIds)) {
            $emails = array_merge(
                $emails,
                Guide::query()
                    ->whereIn('guide_id', array_values(array_unique($guideIds)))
                    ->pluck('email')
                    ->all()
            );
        }

        $emails = array_values(array_unique(array_filter(array_map(
            static function ($email) {
                if (!is_string($email)) {
                    return null;
                }

                $email = trim($email);

                return $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)
                    ? strtolower($email)
                    : null;
            },
            $emails
        ))));

        return [
            'ID' => $idMap,
            'dmc_id' => $dmcId,
            'emails' => $emails,
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

            $jobsheetPayload = $this->buildJobsheetChatPayload((int) $tour->tour_id);
            $dmcId = $jobsheetPayload['dmc_id'] ?? (!empty($tour->dmc_id) ? (int) $tour->dmc_id : null);

            if (empty($dmcId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'DMC ID is missing for this tour.',
                ], 422);
            }

            $jobsheetPayload['dmc_id'] = (int) $dmcId;
            $tourDetails = $this->buildTourDetailsPayload($tour);

            $result = $this->firebaseService->createChatRoom(
                $tour->tour_id,
                (int) $dmcId,
                $tourDetails,
                $jobsheetPayload
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