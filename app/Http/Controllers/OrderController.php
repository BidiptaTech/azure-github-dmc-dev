<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Validation rules for different booking types
     */
    protected $validationRules = [
        'hotel' => [
            'fullName' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'address1' => 'required|string',
            'rooms' => 'required|array',
            'bookingDate' => 'required|array',
            'totalPrice' => 'required|numeric',
            'hotelDetails' => 'required|array',
        ],
        'guide' => [
            'fullName' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'address1' => 'required|string',
            'guide_id' => 'required',
            'bookingDate' => 'required',
            'totalPrice' => 'required|numeric',
        ],
        'vehicle' => [
            'fullName' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'address1' => 'required|string',
            'vehicles_id' => 'required',
            'bookingDate' => 'required',
            'totalPrice' => 'required|numeric',
        ],
        'attraction' => [
            'fullName' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'address1' => 'required|string',
            'AttractionId' => 'required',
            'bookingDate' => 'required',
            'totalPrice' => 'required|numeric',
        ],
    ];

    public function saveService(Request $request)
    {
        try {
            // Validate basic request structure
            $validated = $request->validate([
                'agent_id' => 'required|integer',
                'tour_id' => 'required|integer',
                'type' => 'required|string|in:hotel,guide,vehicle,attraction',
                'data' => 'required|array',
            ]);

            // Validate each item in the data array based on type
            foreach ($validated['data'] as $item) {
                $itemValidation = validator($item, $this->validationRules[$validated['type']]);
                if ($itemValidation->fails()) {
                    throw new ValidationException($itemValidation);
                }
            }

            // Create and save order
            $order = new Order(); 
            $order->agent_id = $validated['agent_id'];
            $order->tour_id = $validated['tour_id'];
            $order->type = $validated['type'];
            $order->data = json_encode($validated['data']);
            $order->status = 1; // Default status for new orders

            // Add additional metadata based on type
            $metadata = [
                'total_items' => count($validated['data']),
                'total_price' => collect($validated['data'])->sum('totalPrice'),
                'booking_dates' => collect($validated['data'])->pluck('bookingDate')->flatten()->unique()->values(),
            ];
            $order->metadata = json_encode($metadata);

            $order->save();

            // Log successful order creation
            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'type' => $order->type,
                'metadata' => $metadata
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst($validated['type']) . ' service saved successfully!',
                'order' => $order,
                'metadata' => $metadata
            ], 201);

        } catch (ValidationException $e) {
            Log::error('Validation error saving service: ' . $e->getMessage(), [
                'errors' => $e->errors(),
                'data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error saving service: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the service.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}