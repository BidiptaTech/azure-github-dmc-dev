<?php

namespace App\Http\Controllers\api;

use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelPolicy;
use Illuminate\Http\Request;

class HotelPolicyController extends Controller
{
    public function fetchHotelPolicies(Request $request)
    {
        $user = auth()->user();
        $hotel_id = $request->hotel_id;

        if (!$hotel_id) {
            return response()->json(['message' => 'Hotel ID is required'], 400);
        }

        // Fetch hotel policies based on hotel_id
        $policy = HotelPolicy::where('hotel_id', $hotel_id)->first();
        if (!$policy) {
            return response()->json(['message' => 'No policies found for this hotel'], 404);
        }
        $policy_list = [];

        $policy_list[] = [
            'id' => $policy->id,
            'name' => $policy->name,
            'policy' => strip_tags($policy->policy),
            'file' => $policy->file ? asset('storage/' . $policy->file) : '',
            'hotel_id' => $policy->hotel_id,
            'check_in_time' => CommonHelper::DateFormat($policy->check_in_time),
            'check_in_until' => CommonHelper::DateFormat($policy->check_in_until),
            'check_out_time' => CommonHelper::DateFormat($policy->check_out_time),
            'check_out_until' => CommonHelper::DateFormat($policy->check_out_until),
            'extras' => $policy->extras,
        ];

        return response()->json([
            'message' => 'Hotel policies retrieved successfully',
            'data' => $policy_list,
        ]);
    }

    public function fetchHotelCancellationPolicies(Request $request)
    {
        $user = auth()->user();
        $hotel_id = $request->hotel_unique_id;

        if (!$hotel_id) {
            return response()->json(['message' => 'Hotel ID is required'], 400);
        }

        // Fetch hotel policies based on hotel_id
        $policy = Hotel::where('hotel_unique_id', $hotel_id)->first();
        //dd($policy);
        if (!$policy) {
            return response()->json(['message' => 'No cancellation policies found for this hotel'], 404);
        }
        $policy_list = [];

        $policy_list[] = [
            'id' => $policy->id,
            'name' => $policy->name,
            'policy' => strip_tags($policy->policy),
            'cancellation_pdf' => $policy->cancellation_pdf ? asset('storage/' . $policy->cancellation_pdf) : '',
            'cancel_policy' => $policy->cancel_policy,
            'cancellation_type' => $policy->cancellation_type,
            'cancellation_data' => json_decode($policy->cancellation_data, true) ?? [],
        ];        

        return response()->json([
            'message' => 'Hotel cancellation policies retrieved successfully',
            'data' => $policy_list,
        ]);
    }

    public function fetchHotelRefundPolicies(Request $request)
    {
        $user = auth()->user();
        $hotel_id = $request->hotel_unique_id;

        if (!$hotel_id) {
            return response()->json(['message' => 'Hotel ID is required'], 400);
        }

        // Fetch hotel policies based on hotel_id
        $policy = Hotel::where('hotel_unique_id', $hotel_id)->first();
        //dd($policy);
        if (!$policy) {
            return response()->json(['message' => 'No refund policies found for this hotel'], 404);
        }
        $policy_list = [];

        $policy_list[] = [
            'id' => $policy->id,
            'name' => $policy->name,
            'refundpolicy' => strip_tags($policy->refundpolicy),
            'refundpolicy_pdf' => $policy->refundpolicy_pdf ? asset('storage/' . $policy->refundpolicy_pdf) : '',
        ];        

        return response()->json([
            'message' => 'Hotel refund policies retrieved successfully',
            'data' => $policy_list,
        ]);
    }
}
