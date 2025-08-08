<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function saveService(Request $request)
    {
        
        try {
            $validated = $request->validate([
                'agent_id' => 'required|integer',
                'tour_id' => 'required|integer',
                'type' => 'required|string',
                'data' => 'required|array',
            ]);


            $order = new Order();
            $order->agent_id = $validated['agent_id'];
            $order->tour_id = $validated['tour_id'];
            $order->type = $validated['type'];
            $order->data = json_encode($validated['data']); // Store as JSON string
            $order->status = 1; // Default status, adjust as needed
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Service saved successfully!',
                'order' => $order
            ], 201);

        } catch (ValidationException $e) {
            Log::error('Validation error saving service: ' . $e->getMessage(), ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error saving service: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the service.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}