<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Helpers\CommonHelper;

class BookingController extends Controller
{
    public function index()
    {
        // if (!hasPermission('view booking')) {
        //     abort(403, 'You do not have permission to access this page.');
        // }
        if(auth()->user()->role_id == 21||auth()->user()->role_id == 26 || auth()->user()->role_id == 34 || auth()->user()->role_id == 124 || auth()->user()->role_id == 125 || $user->role_id == 128 || $user->role_id == 131 || $user->role_id == 132 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 137 || $user->role_id == 138){
            $orderss = Order::with('tour')->where('status', 2)
            ->where('type', 'hotel')
            ->orderBy('id', 'desc') // Orders by ID in descending order
            ->get();
        }else{
            $orderss = Order::with('tour')
            ->where('type', 'hotel')
            ->orderBy('id', 'desc') // Orders by ID in descending order
            ->get();
        }

        $orders = [];

        foreach ($orderss as $order) {
            // Decode only if it's a string
            $data = is_string($order->data) ? json_decode($order->data, true) : $order->data;

            if (is_array($data)) {
                $orders[] = [
                    'id' => $order->id,
                    'booking_id' => $order->booking_id, // Ensure booking_id is included
                    'user_id' => $order->user_id,
                    'amount' => $order->amount,
                    'hotel_details' => $data,
                    'tour_id' => $order->tour_id,
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                ];
            }
        }
        return view('booking.index', compact('orders'));
    }

    public function approve(Request $request)
    {
        // For all roles, reference_id is required
        $request->validate([
            'reference_id' => 'required|string',
        ]);
    
        $order = Order::where('booking_id', $request->booking_id)->first();
    
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order Not Found!'], 404);
        }
    
        // Check if invoice PDF is uploaded before storing
        if ($request->hasFile('invoice_pdf')) {
            $invoicePathData = CommonHelper::image_path('file_storage', $request->file('invoice_pdf'));
            $order->invoice_pdf = $invoicePathData['master_value'] ?? null;
        }
    
        // Update order details with reference_id
        $order->reference_id = $request->reference_id;
        // Set status based on role - status 1 for specific roles, status 4 for others
        $order->status = 1; // Approved with special status
        $order->save();
        return response()->json(['success' => true, 'message' => 'Order Approved Successfully!']);
    }
    

    public function decline(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:orders,booking_id'
        ]);

        $order = Order::where('booking_id', $request->booking_id)->first();

        if ($order) {
            $order->status = 3; // Declined
            $order->save();

            return response()->json(['success' => true, 'message' => 'Order Declined Successfully!']);
        }

        return response()->json(['success' => false, 'message' => 'Order Not Found!'], 404);
    }


}
