<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Enquiry;

class PaymentController extends Controller
{
    public function getPaymentDetails(Request $request)
    {
        $tour_id = $request->tour_id;
        $enquiry = Enquiry::select('id', 'tour_id', 'package_id', 'user_id', 'status', 'created_at', 'amount,', 'actual_amount', 'gross_amount', 'negotiation_details', 'payment_status')->where('tour_id', $tour_id)->first();

        if (!$enquiry) {
            return response()->json([
                'status' => false,
                'message' => 'Enquiry not found',
            ], 404);
        }
        return response()->json([
            'status' => true,
            'message' => 'Payment details fetched successfully',
            'data' => $enquiry,
        ]);
    }
}


