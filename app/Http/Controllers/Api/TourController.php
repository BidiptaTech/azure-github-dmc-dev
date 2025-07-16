<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Tour;
use App\Models\Hotel;
use App\Models\Enquiry;
use App\Models\Country;
use App\Models\City;
use App\Models\User;
use App\Models\Agent;
use Illuminate\Support\Facades\Auth;
use App\Models\Facility;
use App\Models\Order;
use App\Models\Meal;
use App\Models\EnquiryForm;
use App\Models\Attraction;
use App\Models\Restaurant;
use App\Models\VehicleZoneMapping;
use App\Helpers\CommonHelper;
use App\Models\Guide;
use App\Models\Transaction;
use App\Models\Vehicle;
use App\Models\Ticket;
use App\Models\OperationalCountry;
use App\Models\PackagedAttraction;
use App\Services\LogActivityService;
use Illuminate\Support\Facades\Validator;
use DB;
use Illuminate\Support\Facades\Log;


class TourController extends Controller
{
    /*
    * Create Tour unique Id.
    * Date 28-11-2024
    */
    public function createTour(Request $request)
    {
        $validatedData = $request->validate([
            'destination' => 'required|string|max:255',
            'adult' => 'required|integer|min:0',
            'child' => 'nullable|integer|min:0',
            'infant' => 'nullable|integer|min:0',
            'check_in' => 'required|date_format:d/m/Y',
            'check_out' => 'required|date_format:d/m/Y|after_or_equal:check_in',
            'male' => 'required|integer|min:0',
            'female' => 'required|integer|min:0',
            'children_ages' => 'nullable|string',
        ]);
        $countryNames = request()->input('destination');
        $agent_id = request()->header('agent-id') ?? request()->header('agent_id');
        $enquiryId = $request->enquiry_id;
        $countryArray = array_map('trim', explode(',', $countryNames));
        $cities = City::whereIn('country', $countryArray)
              ->select('name', 'country')
              ->get()
              ->map(fn($city) => "{$city->name}, ({$city->country})")
              ->toArray();
        // $user = Agent::where('agent_id',);
        // $agent_id = $user->agent_id;

        try {
            // Parse the dates
            $checkInTime = Carbon::createFromFormat('d/m/Y', $request->check_in);
            $checkOutTime = Carbon::createFromFormat('d/m/Y', $request->check_out);

            // Generate tour ID and save the tour
            $max_tour_id = Tour::max('tour_id') ?? 0;
            $tourId = CommonHelper::createId($max_tour_id);

            $randomDigits = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT); 
            $display_id = 'DMC-ORD'. $tourId;
            $formEnquiry = null;
            if($enquiryId){
            $formEnquiry = EnquiryForm::where('enquiry_id', $enquiryId)
                                      ->where('agent_id', $agent_id)
                                      ->whereNull('unique_tour_id')
                                      ->first();
            }
            $tour = new Tour();
            $tour->destination = $validatedData['destination'];
            $tour->adult = $validatedData['adult'];
            $tour->child = $validatedData['child'] ?? 0;
            $tour->infant = $validatedData['infant'] ?? 0;
            $tour->agent_id = $agent_id;
            $tour->tour_id = $tourId;
            $tour->male_count = $validatedData['male'];
            $tour->female_count = $validatedData['female'];
            $tour->check_in_time = $checkInTime;
            $tour->check_out_time = $checkOutTime;
            $tour->display_id = $display_id;
            $tour->tour_status = "Pending";
            $tour->city = $request->city;
            $tour->child_ages = $validatedData['children_ages'] ?? null;
            $tour->save();
            $tour->refresh();
            if($formEnquiry){
                $formEnquiry->unique_tour_id = $tour->unique_tour_id;
                $formEnquiry->save();
            }

            $service = CommonHelper::CommonResponse($agent_id, $tour->tour_id);
            // LogActivityService::log('create_tour', 'App\Models\Tour', $tourId, $tour);

            return response()->json([
                'message' => 'Tour created successfully',
                'tour_id' => $tour->unique_tour_id,
                'data' => [
                    'tour_id' => $tour->tour_id,
                    'agent_id' => $agent_id,
                    'destination' => $tour->destination,
                    'child' => $tour->child,
                    'infant' => $tour->infant,
                    'male' => $tour->male_count,
                    'female' => $tour->female_count,
                    'CheckInTime' => CommonHelper::DateFormat($checkInTime),
                    'CheckOutTime' => CommonHelper::DateFormat($checkOutTime),
                    'adult' => $tour->adult,
                    'total_pax' => $tour->adult + $tour->child,
                    'service' => $service,
                    'city' => $cities,
                    'EnquiryDetails' => $formEnquiry ?? '',
                ],
            ], 201);
        } catch (\Exception $e) {
            // LogActivityService::log('create_tour_failed', 'App\Models\Tour', $tourId ?? null, json_encode($e->getMessage()));
            return response()->json([
                'message' => 'An error occurred while creating the tour',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /*
    * Edit Tour.
    * Date 23-01-2024
    */
    public function editTour(Request $request)
    {
        $user = auth()->user();
        $agent_id = $user->agent_id;
        $tour_id = $request->header('tour-id') ?? $request->header('tour_id');
        if (empty($tour_id)) {
            return response()->json([
                'message' => 'Invalid request headers',
                'errors' => ['tour_id' => ['The tour_id header is required.']],
            ], 422);
        }
        
        $tour = Tour::where('tour_id', $tour_id)->first();
        if (!$tour) {
            return response()->json([
                'message' => 'Tour not found.',
            ], 404);
        }

        $countryNames = $tour->destination;
        $countryArray = array_map('trim', explode(',', $countryNames));
        
        $cities = City::whereIn('country', $countryArray)
              ->select('name', 'country')
              ->get()
              ->map(fn($city) => "{$city->name}, ({$city->country})")
              ->toArray();

        try {
            $hotel_status = Tour::where('tour_id', $tour->tour_id)->first();
            if ($hotel_status) {
                switch (true) {
                    case ($hotel_status->restaurent == 1 || $hotel_status->restaurent == 3):
                        $position = 'complete';
                        break;
                    case ($hotel_status->guide == 1 || $hotel_status->guide == 3):
                        $position = 'restaurent';
                        break;
                    case ($hotel_status->travel == 1 || $hotel_status->travel == 3):
                        $position = 'guide';
                        break;
                    case ($hotel_status->attraction == 1 || $hotel_status->attraction == 3):
                        $position = 'travel';
                        break;
                    case ($hotel_status->port == 1 || $hotel_status->port == 3):
                        $position = 'attraction';
                        break;
                    case ($hotel_status->hotel == 1 || $hotel_status->hotel == 3):
                        $position = 'port';
                        break;
                    default:
                        $position = 'hotel';
                }
            }
            $columns = ['hotel', 'port', 'attraction', 'travel', 'guide', 'restaurent'];
            $status_position = Tour::where('tour_id',$tour_id)->where(function ($query) use ($columns) {
                foreach ($columns as $column) {
                    $query->orWhere($column, 2);
                }
            })->first();
            $min_position = null;
            if ($status_position) {
                foreach ($columns as $column) {
                    if ($status_position->$column == 2) {
                        $position = $column;
                        break; // Stop at the first found position
                    }
                }
            }

            $order = Order::where('tour_id', $tour_id)->get();
            if(!$order || $order->isEmpty()){
                $booking_type = '';
            }
            else{
                $booking_type = $order->where('bookingType', 'booking')->count() > 0 ? 'booking' : 'enquiry';
            }

            // Get the customer info from the order
            $customerInfo = [];
            if($order->first() && $order->first()->data){
                $data = $order->first()->data;
                // Check if data is already an array or a JSON string
                if (is_string($data)) {
                $decodedData = json_decode($data, true);
                } else {
                    $decodedData = $data; // Already an array
                }
                // Get the customer info (first item of the array)
                $orderInfo = $decodedData[0];

                $customerInfo = [
                    'fullName'        => $orderInfo['fullName']        ?? null,
                    'email'           => $orderInfo['email']           ?? null,
                    'phone'           => $orderInfo['phone']           ?? null,
                    'address1'        => $orderInfo['address1']        ?? null,
                    'address2'        => $orderInfo['address2']        ?? null,
                    'state'           => $orderInfo['state']           ?? null,
                    'zip'             => $orderInfo['zip']             ?? null,
                    'specialRequest'  => $orderInfo['specialRequests'] ?? null,
                ];
            }

            $service = CommonHelper::CommonResponse($agent_id, $tour_id);
            // LogActivityService::log('fetch_tour', 'App\Models\Tour', $tour_id, $tour);
            return response()->json([
                'message' => 'Tour fetched successfully',
                'tour_display_id' => $tour->unique_tour_id,
                'data' => [
                    'tour_id' => $tour->tour_id,
                    'agent_id' => $tour->agent_id,
                    'destination' => $tour->destination,
                    'child' => $tour->child,
                    'infant' => $tour->infant,
                    'male' => $tour->male_count,
                    'female' => $tour->female_count,
                    'children_ages' => $tour->child_ages,
                    'CheckInTime' => CommonHelper::DateFormat($tour->check_in_time),
                    'CheckOutTime' => CommonHelper::DateFormat($tour->check_out_time),
                    'adult' => $tour->adult,
                    'total_pax' => $tour->adult + $tour->child,
                    'step' => $position,
                    'customerInfo' => $customerInfo,
                    'service' => $service,
                    'cities' => $cities,
                    'bookingType' => $booking_type,
                ],
            ], 200);
        } catch (\Exception $e) {
            // LogActivityService::log('fetch_tour_failed', 'App\Models\Tour', $tour_id, json_encode($e->getMessage()));
            return response()->json([
                'message' => 'An error occurred while fetching the tour',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /*
    * Tour listing.
    * Date 24-12-2024
    */
    public function tourlists(Request $request)
    {
        $user = auth()->user();

        $agent_id = $request->agent_id ?? $user->agent_id;

        if (!$agent_id) {
            return response()->json(['message' => 'Agent ID not found'], 400);
        }

        $currentAgent = Agent::where('agent_id', $agent_id)->first();
        if (!$currentAgent) {
            return response()->json(['message' => 'Agent not found'], 404);
        }

        // Update expired tours to 'Closed'
        $tours = Tour::where('agent_id', $agent_id)->get();
        foreach ($tours as $tour) {
            if (
                $tour->check_out_time &&
                Carbon::parse($tour->check_out_time)->lt(Carbon::today()) &&
                $tour->tour_status !== 'Closed'
            ) {
                $tour->tour_status = 'Closed';
                $tour->save();
            }
        }

        // Fetch active (not Closed) tours
        $activeTours = Tour::with(['booking']) // eager load to prevent N+1
            ->where('agent_id', $agent_id)
            // ->where('tour_status', '!=', 'Closed')
            ->get();

        $tour_list = [];

        foreach ($activeTours as $tour) {
            $orders = Order::where('tour_id', $tour->tour_id)->get();

            $edit_off = $orders->isNotEmpty() ? 1 : 0;

            // Calculate total_price from all orders
            $total_price = $orders->sum(function ($order) {
                $data = is_string($order->data) ? json_decode($order->data, true) : $order->data;
                return is_array($data) ? collect($data)->sum(fn($item) => $item['totalPrice'] ?? 0) : 0;
            });

            // Enquiry details
            $enquiry = Enquiry::where('tour_id', $tour->tour_id)->first();
                $enquiry_status = '';
                $edit_off = 0;

                if ($enquiry) {
                    $enquiry_status = $enquiry->status;
                    $edit_off = 1;
                }

            // Latest order for booking type
            $latestOrder = $orders->sortByDesc('created_at')->first();
            $booking_type = $latestOrder->bookingType ?? '';

            // Get first customer name
            $customer_name = '';
            foreach ($orders as $order) {
                $data = is_string($order->data) ? json_decode($order->data, true) : $order->data;
                if (is_array($data)) {
                    foreach ($data as $item) {
                        if (!empty($item['fullName'])) {
                            $customer_name = $item['fullName'];
                            break 2;
                        }
                    }
                }
            }

            // Total tour price from bookings
            $tourTotalPrice = 0;
            foreach ($tour->booking as $booking) {
                if (in_array($booking->status, [1, 3])) {
                    $data = is_string($booking->data) ? json_decode($booking->data, true) : $booking->data;
                    if (is_array($data)) {
                        foreach ($data as $item) {
                            if (isset($item['totalPrice'])) {
                                $tourTotalPrice += (float) $item['totalPrice'];
                            }
                        }
                    }
                }
            }

            // Calculate final amounts
            $settlementAmount = $enquiry->amount ?? $tourTotalPrice;
            $discount = ceil($tourTotalPrice) - $settlementAmount;

            $paymentData = is_string($tour->payment_details) ? json_decode($tour->payment_details, true) : $tour->payment_details;
            $totalPaid = 0;
            if (is_array($paymentData)) {
                foreach ($paymentData as $payment) {
                    if (($payment['status'] ?? 0) == 1) {
                        $totalPaid += (float) ($payment['amount'] ?? 0);
                    }
                }
            }

            $remainingAmount = $settlementAmount - $totalPaid;

            // Determine payment status
            if (!$settlementAmount) {
                $payment_status = "Not Started Yet";
            } elseif ($remainingAmount <= 0) {
                $payment_status = "Completely Paid";
            } elseif ($remainingAmount < $settlementAmount) {
                $payment_status = "Partially Paid";
            } else {
                $payment_status = "Not Paid";
            }

            $order_from = "";
            
            $enquiry_form = EnquiryForm::where('agent_id', $agent_id)->where('unique_tour_id', $tour->unique_tour_id)->exists();
            if($enquiry_form){
                $order_from = "By Sales Person";
            }

            $tour_list[] = [
                'id' => $tour->tour_id,
                'unique_tour_id' => $tour->unique_tour_id,
                'display_id' => $tour->display_id ?? '',
                'destination' => $tour->destination,
                'total_pax' => $tour->adult + $tour->child,
                'check_in_time' => CommonHelper::DateFormat($tour->check_in_time),
                'check_out_time' => CommonHelper::DateFormat($tour->check_out_time),
                'child' => $tour->child,
                'infant' => $tour->infant,
                'adult' => $tour->adult,
                'status' => $tour->status,
                'editOff' => $edit_off,
                'enquiry_status' => $enquiry_status,
                'booking_type' => $booking_type,
                'customer_name' => $customer_name,
                'tour_total_price' => $tourTotalPrice,
                'dueAmount' => $remainingAmount,
                'discountAmount' => $discount,
                'finalAmount' => $settlementAmount,
                'payment_status' => $payment_status,
                'tour_status' => $tour->tour_status,
                'order_from' => $order_from,
            ];
        }

        return response()->json([
            'message' => 'Tour list retrieved successfully',
            'data' => $tour_list,
        ]);
    }

    /*
    * Tour Status.
    * Date 17-01-2025
    */
    public function TourStatus(Request $request)
    {
        $status = $request->status;
        $type = $request->type;
        if (!$request->tour_id) {
            return response()->json(['message' => 'Tour ID is required'], 400);
        }
        $tour = Tour::where('tour_id', $request->tour_id)->first();
        if (!$tour) {
            return response()->json(['message' => 'Tour ID not found'], 404);
        }
        // Ordered task statuses
        $tasks = [
            'hotel' => $tour->hotel,
            'port' => $tour->port,
            'attraction' => $tour->attraction,
            'guide' => $tour->guide,
            'restaurent' => $tour->restaurent,
            'travel' => $tour->travel,
        ];
        /**
         * Helper function to get previous task
         */
        $getPreviousTask = function ($task) use ($tasks) {
            $keys = array_keys($tasks);
            $index = array_search($task, $keys);
            return $index > 0 ? $keys[$index - 1] : null;
        };

        /**
         * Handle Backward Status Reversion
         */
        if ($type === 'back') {
            foreach (array_reverse($tasks, true) as $task => $task_status) {
                if (in_array($task_status, [1, 3])) {
                    $tour->update([$task => 2]); // Revert to pending
                    $tasks[$task] = 2;

                    return response()->json([
                        'message' => ucfirst($task) . ' status reverted to pending',
                        'data' => $tasks,
                        'active_task' => $task,
                        'active_task_status' => 2,
                    ]);
                }
            }

            return response()->json([
                'message' => 'No previous task to revert',
                'data' => $tasks,
            ]);
        }

        /**
         * Handle Forward Status Update
         */
        $updateTaskStatus = function ($taskName) use ($tour, &$tasks, $status, $getPreviousTask) {
            $previousTask = $getPreviousTask($taskName);

            if ($previousTask && !in_array($tasks[$previousTask], [1, 3])) {
                // Auto-skip the previous task if not completed/skipped
                $tour->update([$previousTask => 1]);
                $tasks[$previousTask] = 1;
            }

            $tour->update([$taskName => $status]);
            $tasks[$taskName] = $status;
        };

        // Map request fields to tasks
        $taskMap = [
            'hotel' => 'hotel',
            'port' => 'port',
            'attraction' => 'attraction',
            'guide' => 'guide',
            'restaurent' => 'restaurent',
            'travel' => 'travel',
        ];

        foreach ($taskMap as $requestField => $taskName) {
            if (isset($request->$requestField) && in_array($request->$requestField, [1, 3])) {
                $updateTaskStatus($taskName);
                break; // Update only one task per request
            }
        }

        // Auto-update final status after restaurent completion
        if (in_array($tour->restaurent, [1, 3])) {
            $tour->update(['status' => 1]);
        }

        // Determine next active task
        $activeTask = collect($tasks)->filter(fn($status) => !in_array($status, [1, 3]))->keys()->first();
        $activeTaskStatus = $activeTask ? $tasks[$activeTask] : null;

        $isCompleted = collect($tasks)->every(fn($status) => in_array($status, [1, 3]));
        $tour_type = $request->tour_type;
        if ($isCompleted && $tour_type) {
            $tour->update([
                'hotel'      => 2,
                'port'       => 2,
                'guide'      => 2,
                'attraction' => 2,
                'restaurent' => 2,
                'travel'     => 2,
                'status'     => null,
            ]);

            return response()->json([
                'message' => 'All statuses updated to pending',
                'data' => [
                    'hotel' => 2,
                    'port' => 2,
                    'guide' => 2,
                    'attraction' => 2,
                    'restaurent' => 2,
                    'travel' => 2,
                    'status' => null
                ],
                'active_task' => 'hotel',
                'active_task_status' => 2,
            ]);
        } elseif($isCompleted) {
            return response()->json([
                'message' => 'All tasks are completed or skipped',
                'data' => $tasks,
                'status' => 'completed',
            ]);
        }

        return response()->json([
            'message' => 'Status Updated Successfully',
            'data' => $tasks,
            'active_task' => $activeTask,
            'active_task_status' => $activeTaskStatus ?? 0,
        ]);
    }

    // Helper function to check if the previous step's status allows progression
    private function canProceedToNextStep($stepStatus)
    {
        return in_array($stepStatus, [1, 3]);
    }
    /*
    * Create Booking.
    * Date 24-12-2024
    */
    public function createBooking(Request $request)
    {
        $validatedData = $request->validate([
            'tour_id' => 'required',
            'data' => 'required', 
            'type' => 'required|string|max:255', 
            'bookingType' => 'required|string|max:255', 
        ], [
            'tour_id.required' => 'The tour ID is required.',
            'data.required' => 'The data field is required.',
            'type.required' => 'The type field is required.',
            'bookingType.required' => 'The type field is required.',
        ]);

        if($request->bookingType == 'enquiry'){
            $bookingType = 'enquiry';
        }else{
            $bookingType = 'booking';
        }

        $commission = $request->commission ?? 0;
        $markup_percentage = $request->markup_percentage ?? 0;

        $user = Agent::where('agent_id', $request->header('agent-id'))->first();
        $userId = $request->header('agent-id');
        
        $salesmanager = User::where('userId', $user->sales_manager_dmc)->first();
        $salesmanagerId = $salesmanager->userId;
        $agent_id = $userId;

        $tour_id = $validatedData['tour_id'];
        $tourStatus = Tour::where('tour_id', $tour_id)->value('tour_status');
        $type = $validatedData['type'];
        $max_book_id = Order::max('booking_id') ?? 0;
        $bookId = CommonHelper::createId($max_book_id);
        $flag = 0;
        $decodedData = $validatedData['data']; //getting array data from frontend

        // Convert to JSON if needed and decode to get object format
        if (is_array($validatedData['data'])) {
            $jsonData = json_decode(json_encode($validatedData['data'])); //converting it to json then decoding to get it in object format
        } else if (is_string($validatedData['data'])) {
            return response()->json(['message' => 'Invalid data type'], 409);
        } else {
            $jsonData = null; // Invalid data type
        }
            $order = $jsonData[0];  // Object data

        if (in_array($validatedData['type'], ['entry_port', 'exit_port', 'travel_point','local_transport'])) {

            //travel point
            $vehicle_id = $order->vehicles_id ?? null;
            $country = $order->country ?? null;
            $city = $order->city ?? null;
            $totalPrice = $order->totalPrice ?? null;
            $entry_time = $order->entrytime ?? null;
            $dmcId = $order->dmc_id ?? null;
            $mode = $order->Mode ?? null;
            $distance = $order->distance ?? null;
            $price_type = $order->type ?? null;
            $adults = $order->adults ?? 0;
            $children = $order->children ?? 0;

            $vehicle = Vehicle::where('city', $city)->where('vehicle_id', $vehicle_id)->first();
            if(!$vehicle){
                return response()->json(['message' => 'This vehicle is not found!'], 404);
            }
            $dmc = User::where('userId', $dmcId)->first();
            if(!$dmc){
                return response()->json(['message' => 'DMC not found!'], 409);
            }
            $zone_on = $dmc->zone_on;
            if(in_array($validatedData['type'], ['entry_port', 'exit_port', 'local_transport']) && $zone_on == 1){
                $to_zone_id = $order->to_zone_id;
                $from_zone_id = $order->from_zone_id;
                $existingOrder = Order::where('tour_id', $validatedData['tour_id'])
                ->where('type', $validatedData['type'])
                ->first();

                $zone_price = VehicleZoneMapping::where('vehicle_id', $vehicle_id)->where('to_zone_id', $to_zone_id)->where('from_zone_id', $from_zone_id)->first();
                if(!$zone_price){
                    $zone_price = VehicleZoneMapping::where('vehicle_id', $vehicle_id)->where('to_zone_id', $from_zone_id)->where('from_zone_id', $to_zone_id)->first();
                }
                if($price_type == 'Sharable'){
                    $price = $zone_price->shared_price;
                    $price = $price * ($adults + $children);
                }
                else{
                    $price = $zone_price->private_price;
                    $price = $price;
                }
                $price = ceil($price);
                if($price == $totalPrice){
                    if ($existingOrder) {
                        $existingOrder->data = $validatedData['data'];
                        $existingOrder->agent_id = $agent_id;
                        $existingOrder->status = 1;  // Assuming status 1 means active or confirmed
                        $existingOrder->bookingType = $bookingType;
                        $existingOrder->discount = $commission;
                        $existingOrder->markup_percentage = $markup_percentage;
                        $existingOrder->save();
                        $service = CommonHelper::CommonBookingResponse($agent_id,$tour_id,$type);
                        if($tourStatus == "Tentative"){
                            $tour = Tour::where('tour_id', $tour_id)->update([
                                'tour_status' => "On Hold",
                            ]);
                        }
                        if($bookingType == 'enquiry'){
                            $tour = Tour::where('tour_id', $tour_id)->update([
                                'tour_status' => "New Enquiry",
                            ]);
                        }
                        return response()->json([
                            'message' => ucfirst($validatedData['type']) . ' order updated successfully.',
                            'order' => $existingOrder,
                            'service' => $service
                        ], 200);
                    }
                    else {
                        $order = new Order();
                        $order->agent_id = $agent_id;
                        $order->tour_id = $validatedData['tour_id'];
                        $order->data = $validatedData['data'];
                        $order->type = $validatedData['type'];
                        $order->booking_id = $bookId;
                        $order->status = 1; // Assuming status 1 means active or confirmed
                        $order->bookingType = $bookingType;
                        $order->discount = $commission;
                        $order->markup_percentage = $markup_percentage;
                        $order->save();
                        $service = CommonHelper::CommonBookingResponse($agent_id,$tour_id,$type);
                        if($tourStatus == "Tentative"){
                            $tour = Tour::where('tour_id', $tour_id)->update([
                                'tour_status' => "On Hold",
                            ]);
                        }
                        if($bookingType == 'enquiry'){
                            $tour = Tour::where('tour_id', $tour_id)->update([
                                'tour_status' => "New Enquiry",
                            ]);
                        }
                        return response()->json([
                            'message' => ucfirst($validatedData['type']) . ' order created successfully.',
                            'order' => $order,
                            'service' => $service
                        ], 201);
                    }
                }
                else{
                    return response()->json(['message' => 'Zone Price missmatch occur!', 'actual price='=>$price, 'incoming price='=>$totalPrice], 409);
                }
            }

            $existingOrder = Order::where('tour_id', $validatedData['tour_id'])
            ->where('type', $validatedData['type'])
            ->first();
            

            if ($distance <= 10) {
                $dayColumn = 'cost_per_km_below_10';
                $nightColumn = 'night_cost_per_km_below_10';
                $sharableDayColumn = 'sharable_cost_per_km_below_10';
                $sharableNightColumn = 'sharable_cost_per_km_below_10';

            } elseif ($distance > 10 && $distance <= 25) {
                $dayColumn = 'cost_per_km_10_to_25';
                $nightColumn = 'night_cost_per_km_10_to_25';
                $sharableDayColumn = 'sharable_cost_per_km_10_to_25';
                $sharableNightColumn = 'sharable_night_cost_per_km_10_to_25';
            } else {
                $dayColumn = 'cost_per_km_above_25';
                $nightColumn = 'night_cost_per_km_above_25';
                $sharableDayColumn = 'sharable_cost_per_km_above_25';
                $sharableNightColumn = 'sharable_night_cost_per_km_above_25';
            }
            $operationalCity = OperationalCountry::where('name', $country)->where('city', $city)->first();
            if(!$operationalCity){
                return response()->json(['message' => 'It seems service is not available in  this city!', 'country'=>$country, 'city'=>$city], 409);
            }

            $isNight = false;
                // Parse entry time with improved handling for time formats
                try {
                    // Handle various time formats properly
                    // First, standardize the time format by removing any trailing "am" or "pm" if it also has 24-hour format
                    $entry_time_clean = preg_replace('/(\d{2}:\d{2})(am|pm|AM|PM)$/', '$1', $entry_time);
                    
                    // Try different parsing formats
                    if (preg_match('/^\d{1,2}:\d{2}$/', $entry_time_clean)) {
                        // Simple HH:MM format
                        $parsedEntryTime = Carbon::createFromFormat('H:i', $entry_time_clean)->format('H:i:s');
                    } else if (preg_match('/^\d{1,2}:\d{2} (AM|PM|am|pm)$/', $entry_time)) {
                        // 12-hour format with AM/PM
                        $parsedEntryTime = Carbon::createFromFormat('h:i A', $entry_time)->format('H:i:s');
                    } else if (preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $entry_time_clean)) {
                        // HH:MM:SS format
                        $parsedEntryTime = Carbon::createFromFormat('H:i:s', $entry_time_clean)->format('H:i:s');
                    } else {
                        // Default fallback attempt
                        $parsedEntryTime = Carbon::parse($entry_time)->format('H:i:s');
                    }
                    
                    \Log::info('Time parsing successful for travel_point', [
                        'original' => $entry_time,
                        'parsed' => $parsedEntryTime
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Time parsing failed for travel_point', [
                        'entry_time' => $entry_time,
                        'error' => $e->getMessage()
                    ]);
                    return response()->json([
                        'message' => 'Invalid time format', 
                        'entry_time' => $entry_time
                    ], 400);
                }
                
                if ($operationalCity->night_start_time < $operationalCity->night_end_time) {
                    // Range does not cross midnight
                    $isNight = ($parsedEntryTime >= $operationalCity->night_start_time && $parsedEntryTime <= $operationalCity->night_end_time);
                } else {
                    // Range crosses midnight (e.g., 22:00 to 06:00)
                    $isNight = ($parsedEntryTime >= $operationalCity->night_start_time || $parsedEntryTime <= $operationalCity->night_end_time);
                }
                if($isNight){
                    if($price_type == 'Sharable'){
                        $price = $vehicle->$sharableNightColumn;
                    }
                    else{
                        $price = $vehicle->$nightColumn;
                    }
                }
                else{
                    if($price_type == 'Sharable'){
                        $price = $vehicle->$sharableDayColumn;
                    }
                    else{
                        $price = $vehicle->$dayColumn;
                    }
                }

            if($mode == "dmc"){
                list($finalPrice, $dmc_dmc_id) = CommonHelper::calculateDmcModePricehotel(
                    $price, $dmcId, $vehicle->vehicle_name, 'vehicle',$city);
                $finalPrice = round((float)$finalPrice * $distance, 2) + $vehicle->base_price;
            }
            elseif($mode == "travclicks"){
                $dmc = User::where('userId', $dmcId)->first();
                $dmc_markup = 0;
                if (!$dmc) {
                    return response()->json(['message' => 'DMC not found!'], 409);
                }
                $markup_value = is_numeric($dmc->markup_price) ? $dmc->markup_price : 0;
                $dmc_markup = ($dmc->markup_type == 0) ? $markup_value : ($price * $markup_value / 100);
                
                $finalPrice = ($price + $dmc_markup) ?? 0;
                $finalPrice = round((float)$finalPrice * $distance, 2)>0 ? (round((float)$finalPrice * $distance, 2)+$vehicle->base_price) : 0;
                
            }
            $finalPrice = ceil($finalPrice);
            if($totalPrice == $finalPrice){
                $flag = 1;
            }
            else{
                return response()->json(['message' => 'Travel Point Price missmatch occur!', 'actual price'=>$finalPrice, 'incoming price'=>$totalPrice], 409);
            }
            if($flag == 1){
                if ($existingOrder) {
                    $existingOrder->data = $validatedData['data'];
                    $existingOrder->agent_id = $agent_id;
                    $existingOrder->status = 1;  // Assuming status 1 means active or confirmed
                    $existingOrder->bookingType = $bookingType;
                    $existingOrder->discount = $commission;
                    $existingOrder->markup_percentage = $markup_percentage;
                    $existingOrder->save();
                    $service = CommonHelper::CommonBookingResponse($agent_id,$tour_id,$type);
                    if($tourStatus == "Tentative"){
                        $tour = Tour::where('tour_id', $tour_id)->update([
                            'tour_status' => "On Hold",
                        ]);
                    }   
                    if($bookingType == 'enquiry'){
                        $tour = Tour::where('tour_id', $tour_id)->update([
                            'tour_status' => "New Enquiry",
                        ]);
                    }

                    return response()->json([
                        'message' => ucfirst($validatedData['type']) . ' order updated successfully.',
                        'order' => $existingOrder,
                        'service' => $service
                    ], 200);
                }
                else {
                    $order = new Order();
                    $order->agent_id = $agent_id;
                    $order->tour_id = $validatedData['tour_id'];
                    $order->data = $validatedData['data'];
                    $order->type = $validatedData['type'];
                    $order->booking_id = $bookId;
                    $order->status = 1; // Assuming status 1 means active or confirmed
                    $order->bookingType = $bookingType;
                    $order->discount = $commission;
                    $order->markup_percentage = $markup_percentage;
                    $order->save();
                    $service = CommonHelper::CommonBookingResponse($agent_id,$tour_id,$type);
                    if($tourStatus == "Tentative"){
                        $tour = Tour::where('tour_id', $tour_id)->update([
                            'tour_status' => "On Hold",
                        ]);
                    } 
                    if($bookingType == 'enquiry'){
                        $tour = Tour::where('tour_id', $tour_id)->update([
                            'tour_status' => "New Enquiry",
                        ]);
                    }
                    $tourStatus = Tour::where('tour_id', $tour_id)->value('tour_status');
                    return response()->json([
                        'message' => ucfirst($validatedData['type']) . ' order created successfully.',
                        'order' => $order,
                        'service' => $service
                    ], 201);
                }
            }
            else{
                return response()->json(['message' => 'Travel Point Price missmatch occur!', 'actual price='=>$price, 'incoming price='=>$totalPrice], 409);
            }
        }
        else {
            //hotel
            $type = $validatedData['type'];
            if ($type == 'hotel') {
                $flag = 1;
                // $hotelData = $order;
                // $totalPrice = $hotelData->totalPrice;
                // $priceMode = $hotelData->priceMode ?? 'travclick';
                // $bookingDates = $hotelData->bookingDate;
                // $rooms = $hotelData->rooms;
                // $dmcId = null;
                
                // // Calculate number of nights from booking dates
                // $startDate = Carbon::parse($bookingDates[0]);
                // $endDate = Carbon::parse($bookingDates[1]);
                // $numberOfNights = $endDate->diffInDays($startDate);
                
                // // If dates are same or end date is before start date, set to 1 night
                // $numberOfNights = max(1, $numberOfNights);
                
                // // Calculate total price from all rooms and validate
                // $calculatedPrice = 0;
                
                // foreach ($rooms as $room) {
                //     $beds = $room->beds;
                    
                //     foreach ($beds as $bed) {
                //         $bedPrice = (float)$bed->price;
                //         $headCount = (int)$bed->head_count;
                //         $selectedMeals = (object)$bed->selectedMeals;
                //         $babyCot = isset($bed->baby_cot) ? (int)$bed->baby_cot : 0;
                        
                //         // For each night, get the correct meal price
                //         for ($i = 1; $i <= $numberOfNights; $i++) {
                //             $mealKey = "meal_" . $i;
                            
                //             // If there's a specific meal price for this day
                //             if (isset($selectedMeals->$mealKey) && isset($selectedMeals->$mealKey->price)) {
                //                 $mealPrice = (float)$selectedMeals->$mealKey->price;
                //                 $calculatedPrice += $mealPrice * $headCount;
                //             } else {
                //                 // Fallback to base bed price
                //                 $calculatedPrice += $bedPrice * $headCount;
                //             }
                //         }
                        
                //         // Add baby cot charges if applicable
                //         if ($babyCot > 0) {
                //             // Add baby cot pricing logic if needed
                //             // For now, we're assuming it's included in the room price
                //         }
                //     }
                // }
                
                // $calculatedPrice = round($calculatedPrice, 2);
                
                // // Apply markup based on priceMode if needed
                // // Currently we're just comparing calculated price with total price
                // if ($priceMode == 'dmc' || $priceMode == 'travclick') {
                //     // Allow a small tolerance for floating point comparison (1 cent)
                //     $flag = (abs($calculatedPrice - $totalPrice) < 0.01) ? 1 : 0;
                // }
                
                // if ($flag == 0) {
                //     return response()->json([
                //         'message' => 'Hotel price mismatch!', 
                //         'calculated_price' => $calculatedPrice, 
                //         'submitted_price' => $totalPrice,
                //         'number_of_nights' => $numberOfNights
                //     ], 409);
                // }

                if($flag == 1){
                    // Properly format order data for the email
                   
                    $hotel_agent_id = $request->agent_id;
                    if(!$hotel_agent_id){
                        $hotel_agent_id = auth()->user()->agent_id;
                    }
                    $agent = Agent::where('agent_id', $hotel_agent_id)->first();
                    $sales_manager_dmc = $agent->sales_manager_dmc;
                    $dmcId = null;
                    
                    if($agent->role_id == 11){
                        $dmcId = $sales_manager_dmc;
                    }
                    elseif($agent->role_id == 33){
                        $sales_head = User::where('userId', $sales_manager_dmc)->first();
                        $dmcId = $sales_head->created_by;
                        
                    }
                    elseif($agent->role_id == 37){
                        $sales_manager = User::where('userId', $sales_manager_dmc)->first();
                        $sales_head = User::where('userId', $sales_manager->created_by)->first();
                        $dmcId = $sales_head->created_by;
                    }
                    elseif($agent->role_id == 38){
                        $assistant_sales_manager = User::where('userId', $sales_manager_dmc)->first();
                        $sales_manager = User::where('userId', $assistant_sales_manager->created_by)->first();
                        $sales_head = User::where('userId', $sales_manager->created_by)->first();
                        $dmcId = $sales_head->created_by;
                    }

                    $dmc_email = null;
                    $dmc_phone = null;
                    $dmc_company = null;
                    $dmc_logo = null;
                    // $hotel = Hotel::where('hotel_unique_id', $hotel_id)->select('email')->first();
                    if($dmcId){
                        $dmc = User::where('userId', $dmcId)->first();
                        $master_dmc_id = $dmc->master_dmc_id;
                        $master_dmc = User::where('userId', $master_dmc_id)->first();
                        $dmc_email = $dmc->email;
                        $dmc_phone = $dmc->phone;
                        $dmc_company = $master_dmc->company_name;
                        $dmc_logo = $master_dmc->logo;
                    }
                    $totalGuests = 0;
                    $data = $validatedData['data'][0];

                    $roomInfo = [];
                    $roomTypes = [];
                    $bedInfo = [];
                    $bedTypes = [];
                    $mealInfo = [];
                    $No_of_rooms = 0;
                    $No_of_beds = 0;
                    $checkInTime = $data['hotelDetails']['checkInTime'];
                    $checkOutTime = $data['hotelDetails']['checkOutTime'];
                    $hotel_id = $data['hotelDetails']['hotel_id'];

                    $hotel = Hotel::where('hotel_unique_id', $hotel_id)->first();
                    $hotel_email = $hotel->email;

                    foreach ($validatedData['data'] as $entry) {
                        if (isset($entry['rooms']) && is_array($entry['rooms'])) {
                            foreach ($entry['rooms'] as $room) {
                                // Get room information
                                $roomInfo = $room;
                                $roomTypes[] = $room['room_type'];
                                $No_of_rooms++;
                                if (isset($room['beds']) && is_array($room['beds'])) {
                                    foreach ($room['beds'] as $bed) {
                                        $totalGuests += $bed['head_count'] ?? 0;
                                        // Get bed information
                                        $bedInfo = $bed;
                                        $No_of_beds++;
                                        $bedTypes[] = $bed['bed_type'];
                                        // Get meal information if available
                                            $mealInfo[] = $bed['mealTypes'][0];
                                    }
                                }
                            }
                        }
                    }
                                       
                    $orderData = [
                        "booking_id" => 'BK-' . rand(10000, 99999), // fallback ID
                        "customer_name" => $data['fullName'] ?? "Guest",
                        "type" => "Hotel Booking",
                        "booking_date" => date('Y-m-d'),
                        "check_in_date" => $data['bookingDate'][0] ?? date('Y-m-d', strtotime('+7 days')),
                        "check_out_date" => $data['bookingDate'][1] ?? date('Y-m-d', strtotime('+10 days')),
                        "location" => $data['hotelDetails']['location'] ?? "Unknown",
                        "guests" => $totalGuests . " Guests",
                        "reference_number" => 'REF-' . rand(1000, 9999),
                        "total_price" => $data['totalPrice'] ?? 0,
                        "payment_status" => "Confirmed", // or fetch from elsewhere if needed
                        // Room information
                        "room_type" => implode(', ', $roomTypes) ?? "Standard",
                        "bed_type" => implode(', ', $bedTypes) ?? "Queen Size",
                        "max_occupancy" => $bedInfo['max_occupancy'] ?? 1,
                        "head_count" => $bedInfo['head_count'] ?? 1,
                        "baby_cot" => $bedInfo['baby_cot'] ?? 0,
                        "meal_plan" => implode(', ', $mealInfo) ?? "Room Only",
                        "hotel_name" => $data['hotelDetails']['hotel_name'] ?? "Hotel",
                        "check_in_time" => $checkInTime ?? "00:00",
                        "check_out_time" => $checkOutTime ?? "00:00",
                        "mealTypes" => $bedInfo['mealTypes'] ?? [],
                        "selectedMeals" => isset($bedInfo['selectedMeals']) ? json_encode($bedInfo['selectedMeals']) : "",
                        // Customer information fields
                        "fullName" => $data['fullName'] ?? null,
                        "email" => $data['email'] ?? null,
                        "phone" => $data['phone'] ?? null,
                        "countryCode" => $data['countryCode'] ?? null,
                        "address1" => $data['address1'] ?? null,
                        "address2" => $data['address2'] ?? null,
                        "state" => $data['state'] ?? null,
                        "zip" => $data['zip'] ?? null,
                        "specialRequests" => $data['specialRequests'] ?? null,
                        "dmc_email" => $dmc_email,
                        "dmc_phone" => $dmc_phone,
                        "dmc_company" => $dmc_company,
                        "No_of_rooms" => $No_of_rooms,
                        "No_of_beds" => $No_of_beds,
                        "dmc_logo" => $dmc_logo
                    ];
                    
                    try {
                        $sendEmail = CommonHelper::sendEmail(
                            $hotel_email,
                            'hotel',
                            'Hotel Booking Confirmation',
                            'Your hotel booking has been confirmed',
                            $orderData
                        );
                        
                        // The sendEmail function now returns:
                        // - true on success
                        // - string with error message on failure
                        
                        if ($sendEmail === true) {
                            // Email sent successfully
                            Log::info('Email sent successfully in hotel booking', [
                                'tour_id' => $tour_id,
                                'hotel_email' => $hotel_email,
                                'order_data' => $orderData
                            ]);
                        } else {
                            // Any non-true response is an error message
                            Log::error('Email sending failed in hotel booking', [
                                'error' => $sendEmail,
                                'tour_id' => $tour_id,
                                'hotel_email' => $hotel_email,
                                'order_data' => $orderData
                            ]);
                        }
                                                                                  
                    } catch (\Exception $e) {
                        // Catch any exceptions from sendEmail and log the error
                        Log::error('Email sending failed in hotel booking', [
                            'error' => $e->getMessage(),
                            'tour_id' => $tour_id,
                            'hotel_email' => $hotel_email,
                            'order_data' => $orderData
                        ]);
                    }
                }
            }
            else if ($type == 'zone') {
                $flag = 1;
            }
            //attraction
            else if($type == 'attraction'){
                $attractionId = $order->AttractionId;
                $selection = $order->Selection;
                $mode = $order->mode;
                $dmcId = $order->dmc_id;
                $adultCount = $order->adultCount;
                $childCount = $order->childCount;
                $seniorCount = $order->seniorCount;
                $totalPrice = $order->totalPrice;
                $nri = $order->nri;
                $attraction = Attraction::where('attraction_id', $attractionId)->first();
                
                $name = $attraction->name;
                $city = $attraction->location;
                $dmc = User::where('userId', $dmcId)->first();
                if($order->transport && $order->transport->vehicle_id){
                    $vehicle = Vehicle::where('vehicle_id', $order->transport->vehicle_id)->first();
                }
                else{
                    $vehicle = null;
                }

                $ticket = Ticket::where('attraction_id', $attractionId)->where('ticket_id', $order->ticketId)->first();
                if(!$ticket){
                    return response()->json(['message' => 'Ticket not found!'], 409);
                }
                if($nri == 'residential'){
                    $adultPrice = $ticket->adult_price;
                    $childPrice = $ticket->child_price;
                    $seniorPrice = $ticket->senior_adult_price;
                }
                else{
                    $adultPrice = $ticket->adult_price_nri;
                    $childPrice = $ticket->child_price_nri;
                    $seniorPrice = $ticket->senior_adult_price_nri;
                }

                if($selection == "withoutTransport"){
                    if($mode == "dmc"){
                        $dmc_adult_price = $adultPrice;
                        $dmc_child_price = $childPrice;
                        $dmc_senior_price = $seniorPrice;
                        
                        $price = ($dmc_adult_price*$adultCount)+($dmc_child_price*$childCount)+($dmc_senior_price*$seniorCount);

                        $priceWithoutCommission = ($adultPrice*$adultCount) + ($childPrice*$childCount) + ($seniorPrice*$seniorCount);
                    }
                    elseif($mode == "travclicks"){
                        $dmc = User::where('userId', $dmcId)->first();
                        if(!$dmc){
                            return response()->json(['message' => 'DMC not found!'], 409);
                        }
                        $markup = $dmc->markup_price;
                        $markup_type = $dmc->markup_type;
                        
                        //child price
                        if($markup_type == 1){
                            $markupAdultPrice = $adultPrice; //+ ($adultPrice*($markup/100));
                            $markupChildPrice = $childPrice; //+ ($childPrice*($markup/100));
                            $markupSeniorPrice = $seniorPrice; //+ ($seniorPrice*($markup/100));
                        }
                        elseif($markup_type == 0){
                            $markupAdultPrice = $adultPrice;
                            $markupChildPrice = $childPrice;
                            $markupSeniorPrice = $seniorPrice;
                        }
                        $price = ($adultCount*$markupAdultPrice) + ($childCount*$markupChildPrice) + ($seniorCount*$markupSeniorPrice);
                        
                        $priceWithoutCommission = ($adultPrice*$adultCount) + ($childPrice*$childCount) + ($seniorPrice*$seniorCount);
                    }
                }
                // else if($selection == "withShare"){
                //     if($mode == "dmc"){
                //         list($dmc_adult_price, $dmc_id) = CommonHelper::calculateDmcModePricehotel($adultPrice, $dmcId, $name ,$type = 'attraction', $city);

                //         list($dmc_child_price, $dmc_id) = CommonHelper::calculateDmcModePricehotel($childPrice, $dmcId ,$name ,$type = 'attraction', $city);
                //         list($dmc_senior_price, $dmc_id) = CommonHelper::calculateDmcModePricehotel($seniorPrice, $dmcId ,$name ,$type = 'attraction', $city);

                //         $dmc_shared_price = $vehicle->attraction_shared_transport_price;
                        
                //         $price = $dmc_shared_price*($adultCount + $childCount + $seniorCount)+($dmc_adult_price*$adultCount)+($dmc_child_price*$childCount) + ($dmc_senior_price*$seniorCount);

                //         $priceWithoutCommission = $attraction->price_shared*($adultCount + $childCount + $seniorCount) + ($adultPrice*$adultCount) + ($childPrice*$childCount) + ($seniorPrice*$seniorCount);
                //     }
                //     elseif($mode == "travclicks"){
                //         $sharedPrice = $vehicle->attraction_shared_transport_price;
                //         if(!$dmc){
                //             return response()->json(['message' => 'DMC not found!'], 409);
                //         }
                //         $markup = $dmc->markup_price;
                //         $markup_type = $dmc->markup_type;
                //         if($markup_type == 1){
                            
                //             $adultPrice = $adultPrice + ($adultPrice*($markup/100));
                //             $childPrice = $childPrice + ($childPrice*($markup/100));
                //             $seniorPrice = $seniorPrice + ($seniorPrice*($markup/100));
                //         }
                //         elseif($markup_type == 0){
                            
                //             $adultPrice = $adultPrice + $markup;
                //             $childPrice = $childPrice + $markup;
                //             $seniorPrice = $seniorPrice + $markup;
                //         }
                //         $price = ($sharedPrice*($adultCount + $childCount + $seniorCount))+($adultPrice*$adultCount)+($childPrice*$childCount)+($seniorPrice*$seniorCount);

                //         $priceWithoutCommission = $sharedPrice*($adultCount + $childCount + $seniorCount) + ($adultPrice*$adultCount) + ($childPrice*$childCount) + ($seniorPrice*$seniorCount);
                //     }
                // }
                // else if($selection == "withPrivate"){
                    
                //     if($mode == "dmc"){
                //         $dmc_adult_price = $adultPrice;
                //         $dmc_child_price = $childPrice;
                //         $dmc_senior_price = $seniorPrice;

                //         $dmc_private_price = $vehicle->attraction_private_transport_price;

                //         $price = $dmc_private_price+($dmc_adult_price*$adultCount)+($dmc_child_price*$childCount)+($dmc_senior_price*$seniorCount);

                //         $priceWithoutCommission = $attraction->price_private + ($adultPrice*$adultCount) + ($childPrice*$childCount) + ($seniorPrice*$seniorCount);
                        
                //     }
                //     elseif($mode == "travclicks"){
                //         $privatePrice = $vehicle->attraction_private_transport_price;
                //         if(!$dmc){
                //             return response()->json(['message' => 'DMC not found!'], 404);
                //         }
                //         $markup = $dmc->markup_price;
                //         $markup_type = $dmc->markup_type;
                //         if($markup_type == 1){
                //             $markupPrice = $privatePrice + ($privatePrice*($markup/100));
                //             $adultPrice = $adultPrice + ($adultPrice*($markup/100));
                //             $childPrice = $childPrice + ($childPrice*($markup/100));
                //             $seniorPrice = $seniorPrice + ($seniorPrice*($markup/100));
                //         }
                //         elseif($markup_type == 0){
                //             $markupPrice = $privatePrice + $markup;
                //             $adultPrice = $adultPrice + $markup;
                //             $childPrice = $childPrice + $markup;
                //             $seniorPrice = $seniorPrice + $markup;
                //         }
                //         $price = $markupPrice+($adultPrice*$adultCount)+($childPrice*$childCount)+($seniorPrice*$seniorCount);

                //         $priceWithoutCommission = $attraction->price_private + ($ticket->adult_price*$adultCount) + ($ticket->child_price*$childCount) + ($ticket->senior_adult_price*$seniorCount);
                //     }
                // }
                if($totalPrice == $price){
                    $adminProfit = $totalPrice - $priceWithoutCommission;
                    $flag = 1;
                }
                else{
                    return response()->json(['message' => 'Attraction Price missmatch occur!', 'actual price='=>$price, 'incoming price='=>$totalPrice, 'vehicle'=>$vehicle, 'ticket'=>$ticket], 409);
                }
            }

            else if($type == 'attraction_package'){
                $packageId = $order->package_attraction_id;
                $adultCount = $order->adultCount;
                $childCount = $order->childCount;
                $seniorCount = $order->seniorCount;
                $package = PackagedAttraction::where('package_attraction_id', $packageId)->first();
                if(!$package){
                    return response()->json(['message' => 'Package not found!'], 404);
                }
                $adultPrice = $package->adult_price;
                $childPrice = $package->child_price;
                $seniorPrice = $package->senior_citizen_price;
                $totalPrice = $order->totalPrice;
                $price = ($adultPrice*$adultCount)+($childPrice*$childCount)+($seniorPrice*$seniorCount);
                $priceWithoutCommission = $price;
                
                if($totalPrice == $price){
                    $flag = 1;
                }
                else{
                    return response()->json(['message' => 'Attraction Package Price missmatch occur!', 'actual price='=>$price, 'incoming price='=>$totalPrice, 'package'=>$package], 409);
                }
            }
            
            //guide
            else if($type == 'guide'){
                $guideId = $order->guide_id;
                $entry_time = $order->entrytime;
                $hour = $order->hours;
                $mode = $order->Mode;
                $dmcId = $order->dmc_Id;
                $totalPrice = $order->totalPrice;
                $dmc = User::where('userId', $dmcId)->first();

                $hourMapping = [
                    1 => 'one',
                    2 => 'two',
                    4 => 'four',
                    6 => 'six',
                    8 => 'eight',
                    10 => 'ten',
                    12 => 'twelve'
                ];

                if (isset($hourMapping[$hour])) {
                    $columnName = "{$hourMapping[$hour]}_hour_price";
                } else {
                    return response()->json(['message' => 'Invalid hour input.'], 400);
                }
                
                $guide = Guide::where('guide_id', $guideId)->first();
                
                if(!$guide){
                    return response()->json(['message' => 'Guide not found, book another guide!'], 404);
                }
                
                // Try to parse entry time in different formats
                try {
                    // Standardize time format by removing any trailing "am" or "pm" if format is ambiguous
                    $entry_time_clean = preg_replace('/(\d{2}:\d{2})(am|pm|AM|PM)$/', '$1', $entry_time);
                    
                    // Parse entry time based on format
                    if (preg_match('/^\d{1,2}:\d{2}$/', $entry_time_clean)) {
                        // Simple HH:MM format
                        $entryTimeObj = Carbon::createFromFormat('H:i', $entry_time_clean);
                    } else if (preg_match('/^\d{1,2}:\d{2} (AM|PM|am|pm)$/', $entry_time)) {
                        // 12-hour format with AM/PM
                        $entryTimeObj = Carbon::createFromFormat('h:i A', $entry_time);
                    } else if (preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $entry_time_clean)) {
                        // HH:MM:SS format
                        $entryTimeObj = Carbon::createFromFormat('H:i:s', $entry_time_clean);
                    } else {
                        // Default fallback attempt
                        $entryTimeObj = Carbon::parse($entry_time);
                    }
                    
                    \Log::info('Guide time parsing successful', [
                        'original' => $entry_time,
                        'parsed' => $entryTimeObj->format('H:i:s')
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Guide time parsing failed', [
                        'entry_time' => $entry_time,
                        'error' => $e->getMessage()
                    ]);
                    return response()->json([
                        'message' => 'Invalid time format', 
                        'entry_time' => $entry_time
                    ], 400);
                }

                // Parse night start and end times
                $nightStartObj = Carbon::parse($guide->night_start_time);
                $nightEndObj = Carbon::parse($guide->night_end_time);
                
                // Calculate night hours using the hour-by-hour approach
                $nightHours = 0;
                $currentTime = clone $entryTimeObj;
                $endTime = (clone $entryTimeObj)->addHours((int)$hour);

                while ($currentTime < $endTime) {
                    // Create copies for comparison that are on the same day
                    $compareTime = clone $currentTime;
                    $compareNightStart = clone $nightStartObj;
                    $compareNightEnd = clone $nightEndObj;

                    // Adjust dates to match the current day being checked
                    $compareNightStart->setDate(
                        $compareTime->year, 
                        $compareTime->month, 
                        $compareTime->day
                    );
                    $compareNightEnd->setDate(
                        $compareTime->year, 
                        $compareTime->month, 
                        $compareTime->day
                    );

                    // If night end is before night start, it means it crosses midnight
                    if ($compareNightEnd < $compareNightStart) {
                        if ($compareTime >= $compareNightStart || $compareTime < $compareNightEnd) {
                            $nightHours++;
                        }
                    } else {
                        // Regular night time check
                        if ($compareTime >= $compareNightStart && $compareTime < $compareNightEnd) {
                            $nightHours++;
                        }
                    }

                    // Move to next hour
                    $currentTime->addHour();
                }
                
                \Log::info('Guide night hours calculation', [
                    'nightHours' => $nightHours,
                    'entry_time' => $entryTimeObj->format('H:i:s'),
                    'night_start' => $nightStartObj->format('H:i:s'),
                    'night_end' => $nightEndObj->format('H:i:s'),
                    'duration' => (int)$hour
                ]);

                // Get base guide price based on selected hours
                if($hour == 1){
                    $guidePrice = $guide->hourly_price;
                }
                else{
                $guidePrice = $guide->$columnName;
                }
                $priceWithoutCommission = $guidePrice;
                $night_surcharge = $guide->night_surcharge ?? 0;
                
                if($mode == "dmc"){
                    list($finalPrice, $dmc_id) = CommonHelper::calculateDmcModePricehotel($guidePrice, $dmcId, $guide->name, 'guide', $guide->city);
                    
                    // Apply night surcharge for each night hour
                    if ($nightHours > 0 && $night_surcharge > 0) {
                        list($surchargeAmount, $surchargeDmcId) = CommonHelper::calculateDmcModePricehotel($night_surcharge, $dmcId, $guide->name, 'guide', $guide->city);
                        $finalPrice += ($surchargeAmount * $nightHours);
                    }
                }
                elseif($mode=="travclicks"){
                    if(!$dmc){
                        return response()->json(['message' => 'DMC not found!'], 409);
                    }
                    $markup = $dmc->markup_price;
                    $markup_type = $dmc->markup_type;
                    
                    if($markup_type == 0){
                        // Fixed markup
                        $finalPrice = $guidePrice + $markup;
                        
                        if ($nightHours > 0) {
                            $surcharge = ($night_surcharge + $markup) * $nightHours;
                            $finalPrice += $surcharge;
                        }
                    }
                    elseif($markup_type == 1){
                        // Percentage markup
                        $finalPrice = $guidePrice + ($guidePrice * ($markup/100));
                        
                        if ($nightHours > 0) {
                            $surcharge = ($night_surcharge + ($night_surcharge * ($markup/100))) * $nightHours;
                            $finalPrice += $surcharge;
                        }
                    }
                }
                
                $finalPrice = round($finalPrice, 2);
                
                if($finalPrice == $totalPrice){
                    $adminProfit = $finalPrice - $priceWithoutCommission;
                    $flag = 1;
                }
                else{
                    return response()->json([
                        'message' => 'Guide price mismatch!', 
                        'calculated_price' => $finalPrice, 
                        'received_price' => $totalPrice,
                        'night_hours' => $nightHours,
                        'base_price' => $guidePrice,
                        'night_surcharge' => $night_surcharge
                    ], 409);
                }
            }
            //travel hourly
            else if($type == 'travel_hourly'){
                $vehicle_id = $order->vehicles_id;
                $country = $order->country;
                $city = $order->city;
                $totalHourlyPrice = $order->totalPrice;
                $entry_time = $order->entrytime;
                $selectedHours = $order->selectedHours;
                $dmcId = $order->dmc_id;
                $mode = $order->Mode;
                $dmc = User::where('userId', $dmcId)->first();
                $price_type = $order->type;

                $vehicle = Vehicle::where('city', $city)->where('vehicle_id', $vehicle_id)->first();
                if(!$vehicle){
                    return response()->json(['message' => 'This vehicle is not found!'], 404);
                }
                $operationalCity = OperationalCountry::where('name', $country)->where('city', $city)->first();
                if(!$operationalCity){
                    return response()->json(['message' => 'It seems service is not available in  this city!'], 409);
                }
                $night_start_time = $operationalCity->night_start_time;
                $night_end_time = $operationalCity->night_end_time;
                
                // Parse booking time with improved handling for time formats
                try {
                    // Handle various time formats properly
                    // First, standardize the time format by removing any trailing "am" or "pm" if it also has 24-hour format
                    $entry_time_clean = preg_replace('/(\d{2}:\d{2})(am|pm|AM|PM)$/', '$1', $entry_time);
                    
                    // Try different parsing formats
                    if (preg_match('/^\d{1,2}:\d{2}$/', $entry_time_clean)) {
                        // Simple HH:MM format
                        $bookingTime = Carbon::createFromFormat('H:i', $entry_time_clean);
                    } else if (preg_match('/^\d{1,2}:\d{2} (AM|PM|am|pm)$/', $entry_time)) {
                        // 12-hour format with AM/PM
                        $bookingTime = Carbon::createFromFormat('h:i A', $entry_time);
                    } else if (preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $entry_time_clean)) {
                        // HH:MM:SS format
                        $bookingTime = Carbon::createFromFormat('H:i:s', $entry_time_clean);
                    } else {
                        // Default fallback attempt
                        $bookingTime = Carbon::parse($entry_time);
                    }
                    
                    \Log::info('Time parsing successful', [
                        'original' => $entry_time,
                        'parsed' => $bookingTime->format('H:i:s')
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Time parsing failed', [
                        'entry_time' => $entry_time,
                        'error' => $e->getMessage()
                    ]);
                    return response()->json([
                        'message' => 'Invalid time format', 
                        'entry_time' => $entry_time
                    ], 400);
                }
                
                // Get day and night prices based on price type
                if($price_type == 'Sharable'){
                    $dayPrice = $vehicle->sharable_cost_per_hour;
                    $nightPrice = $vehicle->sharable_night_cost_per_hour;
                    $dayBasePrice = $vehicle->sharable_base_price ?: 0;
                    $nightBasePrice = $vehicle->sharable_night_base_price ?: 0;
                } else {
                    $dayPrice = $vehicle->cost_per_hour;
                    $nightPrice = $vehicle->night_cost_per_hour;
                    $dayBasePrice = $vehicle->base_price ?: 0;
                    $nightBasePrice = $vehicle->night_base_price ?: 0;
                }
                
                // Hour-by-hour calculation approach (matching frontend)
                $totalPrice = 0;
                $hoursInDay = 0;
                $hoursInNight = 0;
                $currentTime = clone $bookingTime;
                
                // Format night start and end times (without date) for comparison
                $startNightTime = substr($night_start_time, 0, 5); // Format: HH:MM
                $endNightTime = substr($night_end_time, 0, 5);     // Format: HH:MM
                
                \Log::info('Night time settings', [
                    'night_start' => $startNightTime,
                    'night_end' => $endNightTime
                ]);
                
                // Helper function to check if a time is within night hours
                $isNightTime = function($timeObj) use ($startNightTime, $endNightTime) {
                    $timeFormat = $timeObj->format('H:i');
                    
                    // If night period crosses midnight
                    if ($startNightTime > $endNightTime) {
                        return $timeFormat >= $startNightTime || $timeFormat < $endNightTime;
                    } else {
                        return $timeFormat >= $startNightTime && $timeFormat < $endNightTime;
                    }
                };
                
                \Log::info('Hour-by-hour calculation start', [
                    'selectedHours' => $selectedHours,
                    'startTime' => $currentTime->format('H:i')
                ]);
                
                // Loop through each hour and calculate price
                for ($i = 0; $i < $selectedHours; $i++) {
                    $isNight = $isNightTime($currentTime);
                    
                    if ($isNight) {
                        $totalPrice += $nightPrice + $nightBasePrice;
                        $hoursInNight++;
                        
                        \Log::info("Hour {$i} is night time", [
                            'time' => $currentTime->format('H:i'),
                            'price' => $nightPrice + $nightBasePrice
                        ]);
                    } else {
                        $totalPrice += $dayPrice + $dayBasePrice;
                        $hoursInDay++;
                        
                        \Log::info("Hour {$i} is day time", [
                            'time' => $currentTime->format('H:i'),
                            'price' => $dayPrice + $dayBasePrice
                        ]);
                    }
                    
                    // Add 1 hour for next iteration
                    $currentTime->addHour();
                }
                
                // Apply DMC or TravClicks markup
                if($mode == "dmc"){
                    // If journey has both day and night hours
                    if ($hoursInDay > 0 && $hoursInNight > 0) {
                        // Calculate markup for each rate separately
                        list($day_price_with_markup, $day_dmc_id) = CommonHelper::calculateDmcModePricehotel(
                            $dayPrice, $dmcId, $vehicle->vehicle_name, 'vehicle', $city);
                        list($night_price_with_markup, $night_dmc_id) = CommonHelper::calculateDmcModePricehotel(
                            $nightPrice, $dmcId, $vehicle->vehicle_name, 'vehicle', $city);
                        
                        // Calculate base prices with markup if needed
                        list($day_base_price_with_markup, $_) = $dayBasePrice > 0 ? 
                            CommonHelper::calculateDmcModePricehotel($dayBasePrice, $dmcId, $vehicle->vehicle_name, 'vehicle', $city) : 
                            [$dayBasePrice, null];
                            
                        list($night_base_price_with_markup, $_) = $nightBasePrice > 0 ? 
                            CommonHelper::calculateDmcModePricehotel($nightBasePrice, $dmcId, $vehicle->vehicle_name, 'vehicle', $city) : 
                            [$nightBasePrice, null];
                            
                        // Calculate final price based on hours in each period
                        $finalPrice = (($day_price_with_markup + $dayBasePrice) * $hoursInDay) + (($night_price_with_markup + $nightBasePrice) * $hoursInNight);
                    } else {
                        // Journey entirely in day or night
                        $price = $hoursInNight > 0 ? $nightPrice : $dayPrice;
                        $basePrice = $hoursInNight > 0 ? $nightBasePrice : $dayBasePrice;
                        
                        list($hourly_price_with_markup, $dmc_dmc_id) = CommonHelper::calculateDmcModePricehotel(
                            $price, $dmcId, $vehicle->vehicle_name, 'vehicle', $city);
                            
                        list($base_price_with_markup, $_) = $basePrice > 0 ? 
                            CommonHelper::calculateDmcModePricehotel($basePrice, $dmcId, $vehicle->vehicle_name, 'vehicle', $city) : 
                            [$basePrice, null];
                            
                        $finalPrice = ($hourly_price_with_markup + $basePrice) * $selectedHours;
                    }
                }
                elseif($mode == "travclicks"){
                    if ($dmc) {
                        $markup_value = is_numeric($dmc->markup_price) ? $dmc->markup_price : 0;
                        
                        if ($hoursInDay > 0 && $hoursInNight > 0) {
                            // Mixed day/night journey
                            if ($dmc->markup_type == 0) {
                                // Fixed markup
                                $day_price_with_markup = $dayPrice + $markup_value;
                                $night_price_with_markup = $nightPrice + $markup_value;
                                // Apply markup to base prices if they exist
                                $day_base_with_markup = $dayBasePrice + $markup_value;
                                $night_base_with_markup = $nightBasePrice + $markup_value;
                            } else {
                                // Percentage markup
                                $day_price_with_markup = $dayPrice + ($dayPrice * $markup_value / 100);
                                $night_price_with_markup = $nightPrice + ($nightPrice * $markup_value / 100);
                                // Apply markup to base prices if they exist
                                $day_base_with_markup = $dayBasePrice + ($dayBasePrice * $markup_value / 100);
                                $night_base_with_markup = $nightBasePrice + ($nightBasePrice * $markup_value / 100);
                            }
                            
                            // Calculate pro-rated price with hour-by-hour precision
                            $finalPrice = (($day_price_with_markup + $dayBasePrice) * $hoursInDay) + 
                                         (($night_price_with_markup + $nightBasePrice) * $hoursInNight);
                        } else {
                            // Journey entirely in day or night
                            $price = $hoursInNight > 0 ? $nightPrice : $dayPrice;
                            $basePrice = $hoursInNight > 0 ? $nightBasePrice : $dayBasePrice;
                            
                            if ($dmc->markup_type == 0) {
                                // Fixed markup
                                $hourly_price_with_markup = $price + $markup_value;
                                $base_price_with_markup = $basePrice + $markup_value;
                            } else {
                                // Percentage markup
                                $hourly_price_with_markup = $price + ($price * $markup_value / 100);
                                $base_price_with_markup = $basePrice + ($basePrice * $markup_value / 100);
                            }
                            
                            $finalPrice = ($hourly_price_with_markup + $basePrice) * $selectedHours;
                        }
                    } else {
                        return response()->json(['message' => 'DMC not found!'], 409);
                    }
                }

                $finalPrice = ceil($finalPrice);
                
                // Calculate the price without commission for admin profit
                $dayPriceNoMarkup = ($dayPrice + $dayBasePrice) * $hoursInDay;
                $nightPriceNoMarkup = ($nightPrice + $nightBasePrice) * $hoursInNight;
                $travelHourlyPriceWithoutCommission = $dayPriceNoMarkup + $nightPriceNoMarkup;
                
                \Log::info('Price calculation complete', [
                    'calculated_price' => $finalPrice,
                    'frontend_price' => $totalHourlyPrice,
                    'hours_in_day' => $hoursInDay,
                    'hours_in_night' => $hoursInNight,
                    'total_hours' => $selectedHours
                ]);
                
                if($finalPrice == $totalHourlyPrice){
                    $flag = 1;
                    $adminProfit = $finalPrice - $travelHourlyPriceWithoutCommission;
                }
                else{
                    return response()->json([
                        'message' => 'Price missmatch occur!', 
                        'calculated' => $finalPrice,
                        'received' => $totalHourlyPrice,
                        'hours_in_day' => $hoursInDay,
                        'hours_in_night' => $hoursInNight,
                        'entry_time_original' => $entry_time,
                        'total_hours' => $selectedHours
                    ], 409);
                }
            }
            //restaurant
            else if($type == 'restaurant'){
                $restaurant_id = $order->restaurantId;
                $totalPrice = $order->totalPrice;
                $dmcId = $order->dmc_id;
                $mealSpecificType = $order->mealSpecificType;
                $mode = $order->priceTypes[0];
                $finalPrice = 0;
                $finalPriceWithoutMarkup = 0;
                $mealDescription = $order->MealDescription;
                $mealsData = $mealDescription;
                $childCount = $order->childCount;
                $adultCount = $order->adultCount;

                $restaurant = Restaurant::where('is_active', 1)
                    ->where('restaurant_id', $restaurant_id)
                    ->first();
                if (!$restaurant) {
                    return response()->json(['message' => 'No restaurants found for the selected city'], 404);
                }
                
                $vehicle = null;
                
                $salesManagerId = $user->sales_manager_dmc;
                $dmc_id = User::where('userId', $salesManagerId)->value('dmcId');
                $dmc = User::where('userId', $dmcId)->first();
                

                if($mealSpecificType == "A la carte"){
                    $markupPrice = 0;
                    
                    foreach ($mealsData as $mealData) {
                        $quantity = (float) $mealData->quantity; // Type cast to float
                        $meal_id = (int) $mealData->meal_id; // Type cast to integer
                        
                        $meal = Meal::where('meal_id', $meal_id)->first();
                        if (!$meal) {
                            return response()->json(['message' => 'Meal not found!'], 404);
                        }
                        $price = (float) $meal->price; // Type cast price to float
                        $markupPrice = 0;
                        // Price without markup
                        $finalPriceWithoutMarkup += ($price * $quantity);
                        
                        if ($mode == "dmc") {
                            list($markupPrice, $dmc_id) = CommonHelper::calculateDmcModePricehotel($price, $dmcId, $restaurant->name ,$type = 'restaurant', $restaurant->city);

                            $markupPrice = (float) $markupPrice; // Type cast markup price
                        } elseif ($mode == "travclicks") {
                            if (!$dmc) {
                                return response()->json(['message' => 'Dmc not found!'], 409);
                            }
                            
                            $markup = (float) $dmc->markup_price;
                            $markup_type = (int) $dmc->markup_type;
                    
                            if ($markup_type == 0) {
                                $markupPrice = $price + $markup;
                            } elseif ($markup_type == 1) {
                                $markupPrice = $price + ($price * ($markup / 100));
                            }
                        }
                    
                        $finalPrice = (float) $finalPrice; // Type cast finalPrice
                        $finalPrice += ($markupPrice * $quantity);
                    } 
                }
                else if($mealSpecificType == "Set Menu"){
                    $quantity = (float) $mealsData[0]->quantity;
                    $meal_id = $mealsData[0]->meal_id;
                    $mealPrice = Meal::where('meal_id', $meal_id)->value('price');                    if (!$mealPrice) {
                        return response()->json(['message' => 'Meal not found!'], 404);
                    }
                    $finalPriceWithoutMarkup = $mealPrice * $quantity; 
                    if($mode == 'dmc'){
                        list($markupPrice, $dmc_id) = CommonHelper::calculateDmcModePricehotel($mealPrice, $dmcId, $restaurant->name ,$type = 'restaurant', $restaurant->city);
                    }
                    elseif($mode == 'travclicks'){
                        $dmc = User::where('userId', $dmcId)->first();
                        if(!$dmc){
                            return response()->json(['message' => 'Dmc not found!'], 409);
                        }
                        $markup = $dmc->markup_price;
                        $markup_type = $dmc->markup_type;
                        if($markup_type == 0){
                            $markupPrice = $mealPrice + $markup;
                            
                        }
                        elseif($markup_type == 1){
                            $markupPrice = $mealPrice + ($mealPrice*($markup/100));
                        }
                    }
                    $finalPrice = $markupPrice * $quantity;
                }
                else if($mealSpecificType == "Buffet"){
                    $adult_count = $order->adultCount;
                    $child_count = $order->childCount;
                    $meal_id = $mealsData[0]->meal_id;
                    $meal = Meal::where('meal_id', $meal_id)->first();
                    if (!$meal) {
                        return response()->json(['message' => 'Meal not found!'], 404);
                    }
                    $finalPriceWithoutMarkup = ($meal->adult_price * $adult_count) + ($meal->child_price * $child_count);

                    if($mode == 'dmc'){
                        list($markupAdultPrice, $dmc_id) = CommonHelper::calculateDmcModePricehotel($meal->adult_price, $dmcId, $restaurant->name ,$type = 'restaurant', $restaurant->city);
                        list($markupChildPrice, $dmc_id) = CommonHelper::calculateDmcModePricehotel($meal->child_price, $dmcId, $restaurant->name ,$type = 'restaurant', $restaurant->city);
                        // list($markupAdultPrice, $dmc_id) = CommonHelper::calculateDmcModePrice($meal->adult_price, $agent_id);
                        // list($markupChildPrice, $dmc_id) = CommonHelper::calculateDmcModePrice($meal->child_price, $agent_id);
                    }
                    elseif($mode == 'travClicks' || $mode == 'travclicks'){
                        $dmc = User::where('userId', $dmcId)->first();
                        if(!$dmc){
                            return response()->json(['message' => 'Dmc not found!'], 409);
                        }
                        $markup = $dmc->markup_price;
                        $markup_type = $dmc->markup_type;
                        if($markup_type == 0){
                            $markupAdultPrice = $meal->adult_price + $markup;
                            $markupChildPrice = $meal->child_price + $markup;
                        }
                        elseif($markup_type == 1){
                            $markupAdultPrice = $meal->adult_price + ($meal->adult_price*($markup/100));
                            $markupChildPrice = $meal->child_price + ($meal->child_price*($markup/100));
                        }
                    }
                    $finalPrice = ($markupAdultPrice * $adult_count) + ($markupChildPrice * $child_count);
                }

                // if (!empty($order->transport) && $order->transport->transport_type == 'private') {
                //     $vehicle = Vehicle::where('vehicle_id', $order->transport->vehicle_id)->first();
                //     if ($vehicle) {
                //         $finalPrice = $finalPrice + ($vehicle->restaurant_private_transport_price);
                //     }
                // } 
                // elseif (!empty($order->transport) && $order->transport->transport_type == 'shared') {
                //     $vehicle = Vehicle::where('vehicle_id', $order->transport->vehicle_id)->first();
                //     if ($vehicle) {
                //         $finalPrice = $finalPrice + ($vehicle->restaurant_shared_transport_price * ($childCount + $adultCount));
                //     }
                // }
                
                if($finalPrice == $totalPrice){
                    $flag = 1;
                    $adminProfit = $finalPrice - $finalPriceWithoutMarkup;
                }
                else{
                    $flag = 0;
                    return response()->json(['message' => 'Price missmatch occur!', 'actual price'=>$finalPrice, 'incoming Price'=>$totalPrice, '$markupAdultPrice'=>$markupAdultPrice, '$markupChildPrice'=>$markupChildPrice], 409);
                }
            }

            // if($flag == 2){
            //     $order = new Order();
            //     $order->agent_id = $agent_id;
            //     $order->tour_id = $validatedData['tour_id'];
            //     $order->data = $validatedData['data'];
            //     $order->type = $validatedData['type'];
            //     $order->booking_id = $bookId;
            //     $order->status = 2;
            //     $order->bookingType = $bookingType;
            //     $order->discount = $commission;
            //     $order->markup_percentage = $markup_percentage;
            //     $order->save();
            //     $service = CommonHelper::CommonBookingResponse($agent_id,$tour_id,$type = 'hotel');
            //     return response()->json([
            //         'message' =>' Booking created successfully.',
            //         'order' => $order,
            //         'service' => $service
            //     ], 201);
            // }
            if($flag == 1){
                $stattus = 1;
                $order = new Order();
                $order->agent_id = $agent_id;
                $order->tour_id = $validatedData['tour_id'];
                $order->data = $validatedData['data'];
                $order->type = $validatedData['type'];
                $order->booking_id = $bookId;
                $order->status = $stattus;
                $order->bookingType = $bookingType;
                $order->discount = $commission;
                $order->markup_percentage = $markup_percentage;
                $order->save();
                if($tourStatus == "Tentative"){
                    $tour = Tour::where('tour_id', $tour_id)->update([
                        'tour_status' => "On Hold",
                    ]);
                } 
                if($bookingType == 'enquiry'){
                    $tour = Tour::where('tour_id', $tour_id)->update([
                        'tour_status' => "New Enquiry",
                    ]);
                }
                // if($order->save()){
                //     // if($dmc){
                //     //     try {
                //     //         DB::beginTransaction(); // Start Transaction
                //     //         // Deduct agent balance from agent wallet

                //     //         $agentBalance = $user->wallet_balance;
                //     //         // if($agentBalance < $totalPrice){
                //     //         //     DB::rollback();
                //     //         //     $order->delete(); // Rollback the order
                //     //         //     return response()->json(['message' => 'Low Balance!! Add money to complete order.'], 404);
                //     //         // }

                //     //         $agentBalance = $user->wallet_balance;
                //     //         $newAgentBalance = $balance-$totalPrice;
                //     //         $agent->wallet_balance = $newAgentBalance;
                //     //         $agent->save();

                //     //         // Deduct admin profit from DMC wallet
                //     //         $balance = $dmc->wallet_balance;
                //     //         $newBalance = $balance + ($totalPrice-$adminProfit);
                //     //         $dmc->wallet_balance = $newBalance;
                //     //         $dmc->save();

                //     //         //Add profit to Admin balance
                //     //         $admin = User::where('userId', 1)->first();
                //     //         // if(!$admin){
                //     //         //     DB::rollback();
                //     //         //     $order->delete(); // Rollback the order
                //     //         //     return response()->json(['message' => 'Admin not found!'], 404);
                //     //         // }
                //     //         $admin->wallet_balance += $adminProfit;
                //     //         $admin->save();

                //     //         // Insert transaction history
                //     //         $transaction = new Transaction();
                //     //         $transaction->user_id = $admin->userId;
                //     //         $transaction->credited_from = $dmc->userId;
                //     //         $transaction->credited_to = $admin->userId;
                //     //         $transaction->commission_type = $dmc->markup_type;
                //     //         $transaction->commission = $dmc->markup_price;
                //     //         $transaction->commission_price = $adminProfit; 
                //     //         $transaction->tour_id = $order->tourId; 
                //     //         $transaction->transaction_id = uniqid('TXN-', true);
                //     //         $transaction->tour_id = $validatedData['tour_id'];
                //     //         $transaction->save();
                //     //         DB::commit();

                //     //     } catch (\Exception $e) {
                //     //         DB::rollback();
                //     //         $order->delete(); // Rollback the order
                //     //         return response()->json([
                //     //             'message' => 'Transaction failed',
                //     //             'error' => $e->getMessage()
                //     //         ], 500);
                //     //     }
                //     // }
                // }

            if($request->bookingType == 'enquiry'){
                $service = CommonHelper::CommonBookingResponse($agent_id,$tour_id,$type);
                Tour::where('tour_id', $tour_id)->update([
                        'tour_status' => "New Enquiry",
                    ]);
                return response()->json([
                    'message' => ucfirst($validatedData['type']) . ' Enquiry has been sent successfully..',
                    'order' => $order,
                    'service' => $service,
                    'comment' => $request->comment,
                    'enqueryPrice' => $request->enquiryPrice,
                ], 201);
            }else{
                $service = CommonHelper::CommonBookingResponse($agent_id,$tour_id,$type);
                if($tourStatus == "Tentative"){
                    $tour = Tour::where('tour_id', $tour_id)->update([
                        'tour_status' => "On Hold",
                    ]);
                } 
                if($bookingType == 'enquiry'){
                    $tour = Tour::where('tour_id', $tour_id)->update([
                        'tour_status' => "New Enquiry",
                    ]);
                }
                return response()->json([
                    'message' => ucfirst($validatedData['type']) . ' Booking created successfully.',
                    'order' => $order,
                    'service' => $service,
                ], 201);
            }
            }
            else{
                return response()->json([
                    'message' => 'Something went wrong! contact to admin if error persists.'
                ], 201);
            }
        }
    }

    /* 
    *Update Enquiry 
    * Date 24-03-2025
    */
    public function updateEnquiry(Request $request)
    {
        $tour_id = $request->tour_id;
        $totalPrice = $request->total_price;
        $type = $request->type;
        if (!$totalPrice) {
            return response()->json([
                'success' => false,
                'message' => 'Total price not found.'
            ], 404);
        }
        if (!$tour_id || !in_array($type, ['enquiry', 'cancel', 'accept'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request parameters.'
            ], 400);
        }
        $currentEnquiry = Enquiry::where('status', 1)->where('tour_id', $tour_id)->first();
        $user = auth()->user();
        $salesManagerId = $user->sales_manager_dmc;


        if ($user) {
            switch ($user->role_id) {
                case 11: // Agent is a DMC
                    $dmc_id = $user->sales_manager_dmc; // Assuming `userId` in agent or fallback to agent_id
                    $dmc_users = User::where('userId', $dmc_id)->first();
                    break;
                case 33: // Sales Head
                    $salesManagerId = $user->sales_manager_dmc;
                        $saleshead_dmc = User::where('userId', $user->sales_manager_dmc)->first(); // SH
                        if ( $saleshead_dmc) {
                            $dmc_users = User::where('userId',  $saleshead_dmc->created_by)->first(); // DMC
                            if ($dmc_users && $dmc_users->role_id == 11) {
                                $dmc_id = $dmc_users->userId;
                            }
                        }
                    break;
                case 12:
                case 37: // Sales Manager
                    $salesManagerId = $user->sales_manager_dmc;
                    $salesmng_dmc= User::where('userId', $user->sales_manager_dmc)->first(); // SM
                    
                    if ($salesmng_dmc) {
                        $saleshead_dmc = User::where('userId', $salesmng_dmc->created_by)->first(); // SH
                        if ( $saleshead_dmc) {
                            $dmc_users = User::where('userId',  $saleshead_dmc->created_by)->first(); // DMC
                            if ($dmc_users && $dmc_users->role_id == 11) {
                                $dmc_id = $dmc_users->userId;
                            }
                        }
                    }
                    break;
                case 38: // Assistant Manager
                    $salesManagerId = $user->sales_manager_dmc;
                    $asmng_dmc = User::where('userId', $user->sales_manager_dmc)->first(); // SM
                    if($asmng_dmc){
                        $salesmng_dmc = User::where('userId', $asmng_dmc->created_by)->first(); // SH
                    }
                    if ($salesmng_dmc) {
                        $saleshead_dmc = User::where('userId', $salesmng_dmc->created_by)->first(); // SH
                        if ( $saleshead_dmc) {
                            $dmc_users = User::where('userId',  $saleshead_dmc->created_by)->first(); // DMC
                            if ($dmc_users && $dmc_users->role_id == 11) {
                                $dmc_id = $dmc_users->userId;
                            }
                        }
                    }
                    break;
            }
        }
        
        $userId = $user->agent_id;
        switch ($type) {
            case 'enquiry':
                // Generate a unique enquiry ID
                $enquiryId = CommonHelper::createId(Enquiry::latest('created_at')->value('enquiry_id') ?? 1);
                while (Enquiry::where('enquiry_id', $enquiryId)->exists()) {
                    $enquiryId = CommonHelper::createId($enquiryId);
                }
                
                // Create a new enquiry (always insert, never update)
                $enquiry = Enquiry::create([
                    'tour_id' => $tour_id, 
                    'status' => 1,
                    'dmcId' => $dmc_id,
                    'enquiry_id' => $enquiryId,
                    'sender_id' => $userId,
                    'sender_type' => 'agent',
                    'receiver_id' => $currentEnquiry->sender_id ?? 0,
                    'receiver_type' => 'OM',
                    'current_position' => 'OM',
                    'amount' => $request->enquiry_price,
                    'actual_amount' => $totalPrice,
                    'comment' => $request->comment,
                    'status' => 1,
                ]);
                
                if ($enquiry) {
                    // Mark previous enquiry as inactive if it exists
                    if ($currentEnquiry && $currentEnquiry->id !== $enquiry->id) {
                        $currentEnquiry->update(['status' => 0]);
                    }
                    
                    // Check if this is the first enquiry for this tour
                    $enquiryCount = Enquiry::where('tour_id', $tour_id)->count();
                    
                    if ($enquiryCount === 1) {
                        // Only update to "New Enquiry" if this is the first enquiry
                        Tour::where('tour_id', $tour_id)->update([
                            'tour_status' => "New Enquiry",
                        ]);
                    }
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Enquiry created successfully.'
                    ], 201);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Unfortunately, enquiry not sent!'
                ], 500);

            case 'cancel':
                if ($currentEnquiry) {
                    $currentEnquiry->update(['status' => 3]);

                    $tour = Tour::where('tour_id', $tour_id)->update([
                        'tour_status' => "Cancelled",
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Enquiry cancelled successfully.'
                    ], 200);
                }
                return response()->json([
                    'success' => false,
                    'message' => 'No active enquiry found to cancel.'
                ], 404);

            case 'accept':
                if ($currentEnquiry) {
                    $currentEnquiry->update(['status' => 2]);
                    Order::where('tour_id', $tour_id)->update(['bookingType' => 'booking']);
                    $tourStatus = Tour::where('tour_id',$tour_id)->first();
                    if($tourStatus->tour_status == "Pending" || $tourStatus->tour_status == "Prospect" || $tourStatus->tour_status == "Tentative"){
                        $tour = Tour::where('tour_id', $tour_id)->update([
                            'tour_status' => "On Hold",
                        ]);
                    }

                    return response()->json([
                        
                        'success' => true,
                        'message' => 'Booking accepted successfully.'
                    ], 200);
                }
                return response()->json([
                    'success' => false,
                    'message' => 'No active enquiry found to accept.'
                ], 404);
        }
    }

    /* 
    *Update Enquiry 
    * Date 24-03-2025
    */
    public function enquiryStatus(Request $request){
        $tour_id = $request->tour_id;
        if(!$tour_id){
            return response()->json([
                'success' => false,
                'message' => 'Tour id required.'
            ], 404); 
        }
        $tour = Tour::where('tour_id', $tour_id)->first();
        if(!$tour){
            return response()->json([
                'success' => false,
                'message' => 'Tour not found.'
            ], 404); 
        }else{
            $enquiry = Enquiry::where('tour_id', $tour->tour_id)->latest()->first();
            $rem = '';
            
            if ($enquiry) {
            if($enquiry->current_position == "OM"){
                    $rem = 'Waiting for AM approval';
            }elseif($enquiry->current_position == "AM"){
                    $rem = 'Waiting for Offer';
            }elseif($enquiry->current_position == "agent"){
                    $rem = 'Offered';
            }
            }
            
            $data = [
                'tour_id' => $tour->tour_id,
                'actual_price' => $enquiry ? ($enquiry->actual_amount ?? '') : '',
                'current_price' => $enquiry ? ($enquiry->amount ?? '') : '',
                'comment' => $enquiry ? ($enquiry->comment ?? '') : '',
                'remarks' => $rem,
                'assigned' => $enquiry ? ($enquiry->current_position ?? '') : '',
                'status' => $enquiry ? ($enquiry->status ?? '') : '',
                'created' => $enquiry ? CommonHelper::DateFormatAdmin($enquiry->created_at) : '',
                'updated' => $enquiry ? CommonHelper::DateFormatAdmin($enquiry->updated_at) : '',
                'pending_days' => $enquiry && $enquiry->created_at 
                    ? max(1, now()->diffInDays($enquiry->created_at)) . ' days' 
                    : '0 days',
            ];
            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);
        }
    }

    /*
    * Cancel Booking.
    * Date 23-01-2024
    */
    public function CancelBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agent_id' => 'required|integer',
            'tour_id' => 'required|integer|exists:tours,tour_id',
            'booking_id' => 'required|integer|exists:orders,booking_id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $validator->errors()
            ], 422);
        }
        $agent_id = $request->agent_id;
        $tour_id = $request->tour_id;
        $booking_id = $request->booking_id;
        $order = Order::where('agent_id', $agent_id)
            ->where('tour_id', $tour_id)
            ->where('booking_id', $booking_id)
            ->first();

        if ($order) {
            $order->status = 4; //cancel booking
            $order->save;
            $service = CommonHelper::CommonBookingResponse($agent_id,$tour_id,$order->type);
            return response()->json([
                'success' => true,
                'message' => 'Booking has been canceled successfully.',
                'service' => $service
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found.'
            ], 404);
        }
    }

    /*
    * Tour Details.
    * Date 25-02-2024
    */
  public function tourDetails(Request $request)
    {
        $unique_tour_id = $request->tour_id;
        $tour = Tour::where('tour_id', $unique_tour_id)->first();

        if (!$tour) {
            return response()->json([
                'message' => 'Tour not found',
            ], 404);
        }

        $orders = Order::where('tour_id', $tour->tour_id)->get();

        $processedOrders = $orders->map(function ($order) {
            $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;

            if (!is_array($orderData)) {
                $orderData = [];
            }

            foreach ($orderData as &$data) {
                if ($order->type === 'attraction' && isset($data['AttractionId'])) {
                    $data['service_details'] = Attraction::where('attraction_id', $data['AttractionId'])->first();
                } elseif ($order->type === 'restaurant' && isset($data['restaurantId'])) {
                    $data['service_details'] = Restaurant::where('restaurant_id', $data['restaurantId'])->first();
                } elseif ($order->type === 'hotel' && isset($data['hotelId'])) {
                    $data['service_details'] = Hotel::where('hotel_unique_id', $data['hotelId'])->first();
                } elseif ($order->type === 'guide' && isset($data['guide_id'])) {
                    $data['service_details'] = Guide::where('guide_id', $data['guide_id'])->first();
                } elseif (
                    in_array($order->type, ['entry_port', 'exit_port', 'travel_point', 'travel_hourly']) &&
                    isset($data['vehicles_id'])
                ) {
                    $data['service_details'] = Vehicle::where('vehicle_id', $data['vehicles_id'])->first();
                }

                $data['status'] = $order->status;
            }

            $order->data = $orderData;
            return $order;
        });

        $data = [
            'tour_id' => $tour->tour_id,
            'agent_id' => $tour->agent_id,
            'destination' => $tour->destination,
            'adult' => $tour->adult,
            'child' => $tour->child,
            'infant' => $tour->infant,
            'male' => $tour->male_count,
            'female' => $tour->female_count,
            'check_in' => Carbon::parse($tour->check_in_time)->format('d/m/Y'),
            'check_out' => Carbon::parse($tour->check_out_time)->format('d/m/Y'),
            'child_ages' => $tour->child_ages,
            'total_pax' => $tour->adult + $tour->child,
            'orders' => $processedOrders,
        ];

        $groupedOrders = [];

        foreach ($data['orders'] as $order) {
            $type = $order['type'];
            if (!isset($groupedOrders[$type])) {
                $groupedOrders[$type] = [];
            }
            $groupedOrders[$type][] = $order['data'];
        }

        $formattedData = [];
        foreach ($groupedOrders as $type => $orderData) {
            $formattedData[$type] = array_merge(...$orderData);
        }

        // ✅ Add tour block at the top level, without affecting existing keys
        $formattedData['tour'] = [
            'tour_id' => $tour->tour_id,
            'agent_id' => $tour->agent_id,
            'destination' => $tour->destination,
            'adult' => $tour->adult,
            'child' => $tour->child,
            'infant' => $tour->infant,
            'male' => $tour->male_count,
            'female' => $tour->female_count,
            'check_in' => Carbon::parse($tour->check_in_time)->format('d/m/Y'),
            'check_out' => Carbon::parse($tour->check_out_time)->format('d/m/Y'),
            'child_ages' => $tour->child_ages,
            'total_pax' => $tour->adult + $tour->child,
            'checkin_date' => $tour->check_in_time,
            'checkout_date' => $tour->check_out_time
        ];

        return response()->json($formattedData);
    }

    /*
    * Cancel Tour.
    * Date 25-02-2024
    */
    public function deleteTour(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tour_id' => 'required|integer|exists:tours,tour_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $validator->errors()
            ], 422);
        }

        $tour_id = $request->tour_id;
        $tour = Tour::where('tour_id', $tour_id)->first();

        if ($tour) {
            $tour->status = 4; //cancel tour
            $tour->save();
            // Soft delete associated orders if Order model also uses SoftDeletes
            // Order::where('tour_id', $tour_id)->update(['deleted_at' => now()]);
            return response()->json([
                'success' => true,
                'message' => 'Tour has been soft deleted successfully.',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Tour not found.',
            ], 404);
        }
    }
}