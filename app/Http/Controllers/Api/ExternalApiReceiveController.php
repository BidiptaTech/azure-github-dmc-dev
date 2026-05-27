<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExternalApiReceive;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExternalApiReceiveController extends Controller
{
    public function receive(Request $request): JsonResponse
    {
        $payload = $request->all();

        if ($payload === []) {
            $raw = trim((string) $request->getContent());
            if ($raw !== '') {
                $decoded = json_decode($raw, true);
                $payload = is_array($decoded) ? $decoded : ['raw_body' => $raw];
            }
        }

        $record = ExternalApiReceive::create([
            'source_ip' => $request->ip(),
            'source_server' => (string) ($request->header('X-Source-Server') ?? ''),
            'headers' => $request->headers->all(),
            'payload' => $payload,
        ]);


        $syncResult = [
            'success' => false,
            'message' => 'Enquiry sync handler is not configured.',
        ];

        $syncControllerClass = 'App\\Http\\Controllers\\Api\\ExternalReceivedEnquiryController';
        if (class_exists($syncControllerClass)) {
            $syncResult = app($syncControllerClass)
                ->createEnquiryFromReceivedPayload($record);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payload received successfully.',
            'received_id' => $record->id,
            'enquiry_sync' => $syncResult,
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(1, min(100, $perPage));

        $rows = ExternalApiReceive::query()
            ->latest('id')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Received payload list fetched.',
            'data' => $rows->items(),
            'pagination' => [
                'current_page' => $rows->currentPage(),
                'per_page' => $rows->perPage(),
                'total' => $rows->total(),
                'last_page' => $rows->lastPage(),
            ],
        ]);
    }
}
