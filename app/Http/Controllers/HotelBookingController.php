<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HotelBookingController extends Controller
{
    /**
     * Update hotel booking dates in the orders table
     * 
     * This method updates only the bookingDate array in the JSON data column
     * for the specific hotel booking in the orders table.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateHotelDates(Request $request): JsonResponse
    {
        try {
            // Log incoming request for debugging
            Log::info('Hotel booking update request received', [
                'request_data' => $request->all()
            ]);

            // Validate the incoming request
            $validator = Validator::make($request->all(), [
                'tour_id' => 'required|integer',
                'booking_id' => 'nullable|integer',
                'hotel_order_index' => 'required|integer|min:0',
                'booking_index' => 'required|integer|min:0',
                'check_in_date' => 'required|date',
                'check_out_date' => 'required|date|after:check_in_date',
                'booking_dates' => 'required|array|size:2',
                'booking_dates.0' => 'required|date',
                'booking_dates.1' => 'required|date|after:booking_dates.0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors(),
                    'request_data' => $request->all() // Include request data for debugging
                ], 422);
            }

            $tourId = $request->tour_id;
            $hotelOrderIndex = $request->hotel_order_index;
            $bookingIndex = $request->booking_index;
            $newBookingDates = $request->booking_dates;

            // Find the hotel booking in the orders table
            $hotelOrder = DB::table('orders')
                ->where('tour_id', $tourId)
                ->where('type', 'hotel')
                ->orderBy('id')
                ->skip($hotelOrderIndex)
                ->first();

            if (!$hotelOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hotel order not found'
                ], 404);
            }

            // Decode the JSON data
            $hotelData = json_decode($hotelOrder->data, true);
            
            if (!$hotelData || !is_array($hotelData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid hotel data format'
                ], 400);
            }

            // Check if the booking index exists
            if (!isset($hotelData[$bookingIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hotel booking not found at the specified index'
                ], 404);
            }

            // Get tour travel dates for validation
            $tour = DB::table('tours')->where('tour_id', $tourId)->first();
            if ($tour) {
                $tourStartDate = Carbon::parse($tour->check_in_time);
                $tourEndDate = Carbon::parse($tour->check_out_time);
                $newCheckIn = Carbon::parse($newBookingDates[0]);
                $newCheckOut = Carbon::parse($newBookingDates[1]);

                // Validate that hotel dates are within tour dates
                if ($newCheckIn->lt($tourStartDate) || $newCheckIn->gt($tourEndDate)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Check-in date must be within the tour travel period'
                    ], 400);
                }

                if ($newCheckOut->lt($tourStartDate) || $newCheckOut->gt($tourEndDate)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Check-out date must be within the tour travel period'
                    ], 400);
                }
            }

            // Update only the bookingDate array in the specific booking
            $hotelData[$bookingIndex]['bookingDate'] = $newBookingDates;

            // Update the orders table with the modified JSON data
            $updated = DB::table('orders')
                ->where('id', $hotelOrder->id)
                ->update([
                    'data' => json_encode($hotelData),
                    'updated_at' => now()
                ]);

            if ($updated) {
                // Log the successful update
                Log::info('Hotel booking dates updated', [
                    'tour_id' => $tourId,
                    'order_id' => $hotelOrder->id,
                    'hotel_order_index' => $hotelOrderIndex,
                    'booking_index' => $bookingIndex,
                    'old_dates' => $hotelData[$bookingIndex]['bookingDate'] ?? 'unknown',
                    'new_dates' => $newBookingDates,
                    'user_id' => Auth::id()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Hotel booking dates updated successfully',
                    'data' => [
                        'tour_id' => $tourId,
                        'order_id' => $hotelOrder->id,
                        'hotel_order_index' => $hotelOrderIndex,
                        'booking_index' => $bookingIndex,
                        'new_booking_dates' => $newBookingDates,
                        'hotel_name' => $hotelData[$bookingIndex]['hotelDetails']['hotel_name'] ?? 'Unknown Hotel'
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update hotel booking dates'
                ], 500);
            }

        } catch (\Exception $e) {
            // Log the error
            Log::error('Error updating hotel booking dates', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating hotel booking dates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update attraction booking date and visit time in the orders table.
     * This method specifically updates the 'bookingDate' and 'visitTime' fields within the JSON 'data' column.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAttractionBooking(Request $request): JsonResponse
    {
        try {
            // Log incoming request for debugging
            Log::info('Attraction booking update request received', [
                'request_data' => $request->all()
            ]);

            // Validate the incoming request
            $validator = Validator::make($request->all(), [
                'tour_id' => 'required|integer',
                'booking_id' => 'nullable|integer',
                'attraction_order_index' => 'required|integer|min:0',
                'booking_index' => 'required|integer|min:0',
                'booking_date' => 'required|date',
                'visit_time' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors(),
                    'request_data' => $request->all()
                ], 422);
            }

            $tourId = $request->tour_id;
            $attractionOrderIndex = $request->attraction_order_index;
            $bookingIndex = $request->booking_index;
            $newBookingDate = $request->booking_date;
            $newVisitTime = $request->visit_time;

            // Find the attraction booking in the orders table
            $attractionOrder = DB::table('orders')
                ->where('tour_id', $tourId)
                ->where('type', 'attraction')
                ->orderBy('id')
                ->skip($attractionOrderIndex)
                ->first();

            if (!$attractionOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attraction order not found'
                ], 404);
            }

            // Decode the JSON data
            $attractionData = json_decode($attractionOrder->data, true);
            
            if (!$attractionData || !is_array($attractionData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid attraction data format'
                ], 400);
            }

            // Check if the booking index exists
            if (!isset($attractionData[$bookingIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attraction booking not found at the specified index'
                ], 404);
            }

            // Get tour travel dates for validation
            $tour = DB::table('tours')->where('tour_id', $tourId)->first();
            if ($tour) {
                $tourStartDate = Carbon::parse($tour->check_in_time);
                $tourEndDate = Carbon::parse($tour->check_out_time);
                $selectedDate = Carbon::parse($newBookingDate);

                // Validate that attraction date is within tour dates
                if ($selectedDate->lt($tourStartDate) || $selectedDate->gt($tourEndDate)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Booking date must be within the tour travel period'
                    ], 400);
                }
            }

            // Update only the bookingDate and visitTime in the specific booking
            $attractionData[$bookingIndex]['bookingDate'] = $newBookingDate;
            $attractionData[$bookingIndex]['visitTime'] = $newVisitTime;

            // Update the orders table with the modified JSON data
            $updated = DB::table('orders')
                ->where('id', $attractionOrder->id)
                ->update([
                    'data' => json_encode($attractionData),
                    'updated_at' => now()
                ]);

            if ($updated) {
                // Log the successful update
                Log::info('Attraction booking updated', [
                    'tour_id' => $tourId,
                    'order_id' => $attractionOrder->id,
                    'attraction_order_index' => $attractionOrderIndex,
                    'booking_index' => $bookingIndex,
                    'old_date' => $attractionData[$bookingIndex]['bookingDate'] ?? 'unknown',
                    'old_time' => $attractionData[$bookingIndex]['visitTime'] ?? 'unknown',
                    'new_date' => $newBookingDate,
                    'new_time' => $newVisitTime,
                    'user_id' => Auth::id()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Attraction booking updated successfully',
                    'data' => [
                        'tour_id' => $tourId,
                        'order_id' => $attractionOrder->id,
                        'attraction_order_index' => $attractionOrderIndex,
                        'booking_index' => $bookingIndex,
                        'new_booking_date' => $newBookingDate,
                        'new_visit_time' => $newVisitTime,
                        'attraction_name' => $attractionData[$bookingIndex]['AttractionName'] ?? 'Unknown Attraction'
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update attraction booking'
                ], 500);
            }

        } catch (\Exception $e) {
            // Log the error
            Log::error('Error updating attraction booking', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating attraction booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attraction booking data for editing
     * 
     * This method retrieves the current attraction booking data for the edit modal.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAttractionBookingData(Request $request): JsonResponse
    {
        try {
            Log::info('Getting attraction booking data request', [
                'request_data' => $request->all()
            ]);
            
            $validator = Validator::make($request->all(), [
                'tour_id' => 'required|integer',
                'attraction_order_index' => 'required|integer|min:0',
                'booking_index' => 'required|integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $tourId = $request->tour_id;
            $attractionOrderIndex = $request->attraction_order_index;
            $bookingIndex = $request->booking_index;

            // Get tour data
            $tour = DB::table('tours')->where('tour_id', $tourId)->first();
            
            // Find the attraction booking
            $attractionOrder = DB::table('orders')
                ->where('tour_id', $tourId)
                ->where('type', 'attraction')
                ->orderBy('id')
                ->skip($attractionOrderIndex)
                ->first();

            if (!$attractionOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attraction order not found'
                ], 404);
            }

            $attractionData = json_decode($attractionOrder->data, true);
            
            if (!$attractionData || !isset($attractionData[$bookingIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attraction booking not found'
                ], 404);
            }

            $booking = $attractionData[$bookingIndex];
            
            Log::info('Attraction booking data found', [
                'tour_id' => $tourId,
                'attraction_order_index' => $attractionOrderIndex,
                'booking_index' => $bookingIndex,
                'attraction_name' => $booking['AttractionName'] ?? 'Unknown',
                'total_price' => $booking['totalPrice'] ?? 0,
                'booking_date' => $booking['bookingDate'] ?? null,
                'visit_time' => $booking['visitTime'] ?? null,
                'full_booking_keys' => array_keys($booking),
                'raw_booking_data' => $booking
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'tour' => [
                        'tour_id' => $tour->tour_id ?? $tourId,
                        'check_in_time' => $tour->check_in_time ?? null,
                        'check_out_time' => $tour->check_out_time ?? null
                    ],
                    'attraction_booking' => [
                        'booking_id' => $attractionOrder->id,
                        'attraction_name' => $booking['AttractionName'] ?? 'Unknown Attraction',
                        'attraction_id' => $booking['AttractionId'] ?? null,
                        'ticket_name' => $booking['ticketName'] ?? 'N/A',
                        'total_price' => $booking['totalPrice'] ?? 0,
                        'adult_count' => $booking['adultCount'] ?? 0,
                        'child_count' => $booking['childCount'] ?? 0,
                        'senior_count' => $booking['seniorCount'] ?? 0,
                        'booking_date' => $booking['bookingDate'] ?? null,
                        'visit_time' => $booking['visitTime'] ?? null,
                        // Customer information
                        'full_name' => $booking['fullName'] ?? 'N/A',
                        'email' => $booking['email'] ?? 'N/A',
                        'phone' => $booking['phone'] ?? 'N/A',
                        'address' => $booking['address1'] ?? 'N/A',
                        'special_requests' => $booking['specialRequests'] ?? null,
                        // Additional details  
                        'location' => $booking['location'] ?? $booking['city'] ?? $booking['country'] ?? 'N/A',
                        'image' => $booking['image'] ?? $booking['AttractionImage'] ?? null,
                        'transport' => $booking['transport'] ?? null,
                        'selection' => $booking['Selection'] ?? null,
                        'ticket_details' => $booking['ticket_details'] ?? [],
                        'attraction_details' => $booking
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting attraction booking data', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving attraction booking data'
            ], 500);
        }
    }

    /**
     * Update restaurant booking date and visit time in the orders table.
     * This method specifically updates the 'bookingDate' and 'visitTime' fields within the JSON 'data' column.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateRestaurantBooking(Request $request): JsonResponse
    {
        try {
            // Log incoming request for debugging
            Log::info('Restaurant booking update request received', [
                'request_data' => $request->all()
            ]);

            // Validate the incoming request
            $validator = Validator::make($request->all(), [
                'tour_id' => 'required|integer',
                'booking_id' => 'nullable|integer',
                'restaurant_order_index' => 'required|integer|min:0',
                'booking_index' => 'required|integer|min:0',
                'booking_date' => 'required|date',
                'visit_time' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors(),
                    'request_data' => $request->all()
                ], 422);
            }

            $tourId = $request->tour_id;
            $restaurantOrderIndex = $request->restaurant_order_index;
            $bookingIndex = $request->booking_index;
            $newBookingDate = $request->booking_date;
            $newVisitTime = $request->visit_time;

            // Find the restaurant booking in the orders table
            $restaurantOrder = DB::table('orders')
                ->where('tour_id', $tourId)
                ->where('type', 'restaurant')
                ->orderBy('id')
                ->skip($restaurantOrderIndex)
                ->first();

            if (!$restaurantOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Restaurant order not found'
                ], 404);
            }

            // Decode the JSON data
            $restaurantData = json_decode($restaurantOrder->data, true);
            
            if (!$restaurantData || !is_array($restaurantData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid restaurant data format'
                ], 400);
            }

            // Check if the booking index exists
            if (!isset($restaurantData[$bookingIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Restaurant booking not found at the specified index'
                ], 404);
            }

            // Get tour travel dates for validation
            $tour = DB::table('tours')->where('tour_id', $tourId)->first();
            if ($tour) {
                $tourStartDate = Carbon::parse($tour->check_in_time);
                $tourEndDate = Carbon::parse($tour->check_out_time);
                $selectedDate = Carbon::parse($newBookingDate);

                // Validate that restaurant date is within tour dates
                if ($selectedDate->lt($tourStartDate) || $selectedDate->gt($tourEndDate)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Booking date must be within the tour travel period'
                    ], 400);
                }
            }

            // Update only the bookingDate and visitTime in the specific booking
            $restaurantData[$bookingIndex]['bookingDate'] = $newBookingDate;
            $restaurantData[$bookingIndex]['visitTime'] = $newVisitTime;

            // Update the orders table with the modified JSON data
            $updated = DB::table('orders')
                ->where('id', $restaurantOrder->id)
                ->update([
                    'data' => json_encode($restaurantData),
                    'updated_at' => now()
                ]);

            if ($updated) {
                // Log the successful update
                Log::info('Restaurant booking updated', [
                    'tour_id' => $tourId,
                    'order_id' => $restaurantOrder->id,
                    'restaurant_order_index' => $restaurantOrderIndex,
                    'booking_index' => $bookingIndex,
                    'old_date' => $restaurantData[$bookingIndex]['bookingDate'] ?? 'unknown',
                    'old_time' => $restaurantData[$bookingIndex]['visitTime'] ?? 'unknown',
                    'new_date' => $newBookingDate,
                    'new_time' => $newVisitTime,
                    'user_id' => Auth::id()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Restaurant booking updated successfully',
                    'data' => [
                        'tour_id' => $tourId,
                        'order_id' => $restaurantOrder->id,
                        'restaurant_order_index' => $restaurantOrderIndex,
                        'booking_index' => $bookingIndex,
                        'new_booking_date' => $newBookingDate,
                        'new_visit_time' => $newVisitTime,
                        'restaurant_name' => $restaurantData[$bookingIndex]['restaurantName'] ?? 'Unknown Restaurant'
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update restaurant booking'
                ], 500);
            }

        } catch (\Exception $e) {
            // Log the error
            Log::error('Error updating restaurant booking', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating restaurant booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get restaurant booking data for editing
     * 
     * This method retrieves the current restaurant booking data for the edit modal.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getRestaurantBookingData(Request $request): JsonResponse
    {
        try {
            Log::info('🍽️ Restaurant data request received', [
                'request_data' => $request->all()
            ]);

            $validator = Validator::make($request->all(), [
                'tour_id' => 'required|integer',
                'restaurant_order_index' => 'required|integer|min:0',
                'booking_index' => 'required|integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $tourId = $request->tour_id;
            $restaurantOrderIndex = $request->restaurant_order_index;
            $bookingIndex = $request->booking_index;

            Log::info('🔍 Processing restaurant data request', [
                'tour_id' => $tourId,
                'restaurant_order_index' => $restaurantOrderIndex,
                'booking_index' => $bookingIndex
            ]);

            // Get tour data
            $tour = DB::table('tours')->where('tour_id', $tourId)->first();
            
            // Find the restaurant booking
            $allRestaurantOrders = DB::table('orders')
                ->where('tour_id', $tourId)
                ->where('type', 'restaurant')
                ->orderBy('id')
                ->get();

            Log::info('📋 All restaurant orders found', [
                'count' => $allRestaurantOrders->count(),
                'orders' => $allRestaurantOrders->map(function($order) {
                    $data = json_decode($order->data, true);
                    return [
                        'id' => $order->id,
                        'booking_count' => is_array($data) ? count($data) : 0,
                        'first_booking_name' => is_array($data) && count($data) > 0 ? ($data[0]['restaurantName'] ?? 'Unknown') : 'No bookings'
                    ];
                })
            ]);

            $restaurantOrder = $allRestaurantOrders->skip($restaurantOrderIndex)->first();

            if (!$restaurantOrder) {
                Log::error('❌ Restaurant order not found', [
                    'tour_id' => $tourId,
                    'restaurant_order_index' => $restaurantOrderIndex,
                    'total_orders_found' => $allRestaurantOrders->count()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Restaurant order not found'
                ], 404);
            }

            $restaurantData = json_decode($restaurantOrder->data, true);
            
            Log::info('📊 Restaurant order data decoded', [
                'order_id' => $restaurantOrder->id,
                'booking_count' => is_array($restaurantData) ? count($restaurantData) : 0,
                'requested_booking_index' => $bookingIndex,
                'all_restaurant_names' => is_array($restaurantData) ? array_map(function($booking) {
                    return $booking['restaurantName'] ?? 'Unknown';
                }, $restaurantData) : []
            ]);
            
            if (!$restaurantData || !isset($restaurantData[$bookingIndex])) {
                Log::error('❌ Restaurant booking not found at index', [
                    'booking_index' => $bookingIndex,
                    'available_indices' => is_array($restaurantData) ? array_keys($restaurantData) : [],
                    'booking_count' => is_array($restaurantData) ? count($restaurantData) : 0
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Restaurant booking not found'
                ], 404);
            }

            $booking = $restaurantData[$bookingIndex];
            
            Log::info('✅ Found specific restaurant booking', [
                'booking_index' => $bookingIndex,
                'restaurant_name' => $booking['restaurantName'] ?? 'Unknown',
                'booking_date' => $booking['bookingDate'] ?? 'Unknown',
                'visit_time' => $booking['visitTime'] ?? 'Unknown',
                'meal_type' => $booking['mealType'] ?? 'Unknown'
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'tour' => [
                        'tour_id' => $tour->tour_id ?? $tourId,
                        'check_in_time' => $tour->check_in_time ?? null,
                        'check_out_time' => $tour->check_out_time ?? null
                    ],
                    'restaurant_booking' => [
                        'booking_id' => $restaurantOrder->id,
                        'restaurant_name' => $booking['restaurantName'] ?? 'Unknown Restaurant',
                        'meal_type' => $booking['mealType'] ?? 'N/A',
                        'meal_specific_type' => $booking['mealSpecificType'] ?? 'N/A',
                        'total_price' => $booking['totalPrice'] ?? 0,
                        'adult_count' => $booking['adultCount'] ?? 0,
                        'child_count' => $booking['childCount'] ?? 0,
                        'booking_date' => $booking['bookingDate'] ?? null,
                        'visit_time' => $booking['visitTime'] ?? null,
                        'restaurant_details' => $booking // This contains the full JSON data
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting restaurant booking data', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving restaurant booking data'
            ], 500);
        }
    }

    /**
     * Get guide booking data for individual modal
     * 
     * This method retrieves the current guide booking data for the individual modal.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getGuideBookingData(Request $request): JsonResponse
    {
        try {
            Log::info('👨‍💼 Guide data request received', [
                'request_data' => $request->all()
            ]);

            $validator = Validator::make($request->all(), [
                'tour_id' => 'required|integer',
                'guide_order_index' => 'required|integer|min:0',
                'booking_index' => 'required|integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $tourId = $request->tour_id;
            $guideOrderIndex = $request->guide_order_index;
            $bookingIndex = $request->booking_index;

            Log::info('🔍 Processing guide data request', [
                'tour_id' => $tourId,
                'guide_order_index' => $guideOrderIndex,
                'booking_index' => $bookingIndex
            ]);

            // Get tour data
            $tour = DB::table('tours')->where('tour_id', $tourId)->first();
            
            // Find the guide booking
            $allGuideOrders = DB::table('orders')
                ->where('tour_id', $tourId)
                ->where('type', 'guide')
                ->orderBy('id')
                ->get();

            Log::info('📋 All guide orders found', [
                'count' => $allGuideOrders->count(),
                'orders' => $allGuideOrders->map(function($order) {
                    $data = json_decode($order->data, true);
                    return [
                        'id' => $order->id,
                        'booking_count' => is_array($data) ? count($data) : 0,
                        'first_booking_name' => is_array($data) && count($data) > 0 ? ($data[0]['guide_name'] ?? 'Unknown') : 'No bookings'
                    ];
                })
            ]);

            $guideOrder = $allGuideOrders->skip($guideOrderIndex)->first();

            if (!$guideOrder) {
                Log::error('❌ Guide order not found', [
                    'tour_id' => $tourId,
                    'guide_order_index' => $guideOrderIndex,
                    'total_orders_found' => $allGuideOrders->count()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Guide order not found'
                ], 404);
            }

            $guideData = json_decode($guideOrder->data, true);
            
            Log::info('📊 Guide order data decoded', [
                'order_id' => $guideOrder->id,
                'booking_count' => is_array($guideData) ? count($guideData) : 0,
                'requested_booking_index' => $bookingIndex,
                'all_guide_names' => is_array($guideData) ? array_map(function($booking) {
                    return $booking['guide_name'] ?? 'Unknown';
                }, $guideData) : []
            ]);
            
            if (!$guideData || !isset($guideData[$bookingIndex])) {
                Log::error('❌ Guide booking not found at index', [
                    'booking_index' => $bookingIndex,
                    'available_indices' => is_array($guideData) ? array_keys($guideData) : [],
                    'booking_count' => is_array($guideData) ? count($guideData) : 0
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Guide booking not found'
                ], 404);
            }

            $booking = $guideData[$bookingIndex];
            
            Log::info('✅ Found specific guide booking', [
                'booking_index' => $bookingIndex,
                'guide_name' => $booking['guide_name'] ?? 'Unknown',
                'booking_date' => $booking['bookingDate'] ?? 'Unknown',
                'pickup_time' => $booking['entrytime'] ?? 'Unknown',
                'hours' => $booking['hours'] ?? 'Unknown'
            ]);

            $response = [
                'success' => true,
                'data' => [
                    'tour_data' => [
                        'tour_id' => $tour->tour_id,
                        'check_in_time' => $tour->check_in_time,
                        'check_out_time' => $tour->check_out_time,
                    ],
                    'guide_name' => $booking['guide_name'] ?? 'Professional Guide',
                    'guide_id' => $booking['guide_id'] ?? null,
                    'image' => $booking['image'] ?? null,
                    'booking_date' => $booking['bookingDate'] ?? 'N/A',
                    'pickup_date' => $booking['pickupdate'] ?? $booking['bookingDate'] ?? 'N/A',
                    'entry_time' => $booking['entrytime'] ?? 'N/A',
                    'hours' => $booking['hours'] ?? 'N/A',
                    'adults' => $booking['adults'] ?? '0',
                    'children' => $booking['children'] ?? '0',
                    'total_price' => $booking['totalPrice'] ?? '0',
                    'base_price' => $booking['basePrice'] ?? '0',
                    'surcharge' => $booking['surcharge'] ?? '0',
                    'tax' => $booking['Tax'] ?? '0',
                    'pickup_location' => $booking['entrypickup'] ?? 'N/A',
                    'night_start_time' => $booking['Night_Start_Time'] ?? 'N/A',
                    'night_end_time' => $booking['Night_End_Time'] ?? 'N/A',
                    'full_name' => $booking['fullName'] ?? 'N/A',
                    'email' => $booking['email'] ?? 'N/A',
                    'phone' => $booking['phone'] ?? 'N/A',
                    'address' => $booking['address1'] ?? 'N/A',
                    'state' => $booking['state'] ?? 'N/A',
                    'zip' => $booking['zip'] ?? 'N/A',
                    'special_requests' => $booking['specialRequests'] ?? null,
                    'booking_type' => $booking['bookingType'] ?? 'Standard',
                    'mode' => $booking['Mode'] ?? 'dmc',
                    'dmc_id' => $booking['dmc_id'] ?? $booking['dmc_Id'] ?? null,
                    // Include full booking details for comprehensive display
                    'guide_details' => $booking
                ]
            ];

            Log::info('✅ Guide booking data response prepared', [
                'guide_name' => $response['data']['guide_name'],
                'booking_date' => $response['data']['booking_date'],
                'total_price' => $response['data']['total_price']
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('❌ Error retrieving guide booking data', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving guide booking data'
            ], 500);
        }
    }

    /**
     * Update guide booking data
     */
    public function updateGuideBooking(Request $request)
    {
        try {
            Log::info('Guide booking update request received', $request->all());

            // Validation
            $validator = Validator::make($request->all(), [
                'tour_id' => 'required|integer',
                'guide_order_index' => 'required|integer|min:0',
                'booking_index' => 'required|integer|min:0',
                'booking_date' => 'required|date',
                'pickup_date' => 'required|date',
                'entry_time' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors(),
                    'request_data' => $request->all()
                ], 400);
            }

            $tourId = $request->tour_id;
            $guideOrderIndex = $request->guide_order_index;
            $bookingIndex = $request->booking_index;
            $bookingDate = $request->booking_date;
            $pickupDate = $request->pickup_date;
            $entryTime = $request->entry_time;

            // Find the guide order
            $guideOrder = DB::table('orders')
                ->where('tour_id', $tourId)
                ->where('type', 'guide')
                ->skip($guideOrderIndex)
                ->first();

            if (!$guideOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guide order not found'
                ], 404);
            }

            // Decode the JSON data
            $data = json_decode($guideOrder->data, true);
            if (!is_array($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid guide data format'
                ], 400);
            }

            // Check if the booking index exists (same as restaurant method)
            if (!isset($data[$bookingIndex])) {
                Log::error('Guide booking index not found', [
                    'booking_index' => $bookingIndex,
                    'available_indices' => array_keys($data),
                    'data_count' => count($data)
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Guide booking not found at the specified index'
                ], 404);
            }

            Log::info('Guide booking found for update', [
                'booking_index' => $bookingIndex,
                'guide_name' => $data[$bookingIndex]['guide_name'] ?? 'Unknown',
                'current_booking_date' => $data[$bookingIndex]['bookingDate'] ?? 'Unknown',
                'new_booking_date' => $bookingDate
            ]);

            // Get tour travel dates for validation (same as restaurant method)
            $tour = DB::table('tours')->where('tour_id', $tourId)->first();
            if ($tour) {
                $tourStartDate = Carbon::parse($tour->check_in_time);
                $tourEndDate = Carbon::parse($tour->check_out_time);
                $selectedBookingDate = Carbon::parse($bookingDate);
                $selectedPickupDate = Carbon::parse($pickupDate);

                // Validate that booking date is within tour dates
                if ($selectedBookingDate->lt($tourStartDate) || $selectedBookingDate->gt($tourEndDate)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Booking date must be within the tour travel period'
                    ], 400);
                }

                // Validate that pickup date is within tour dates
                if ($selectedPickupDate->lt($tourStartDate) || $selectedPickupDate->gt($tourEndDate)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Pickup date must be within the tour travel period'
                    ], 400);
                }
            }

            // Update only the bookingDate, pickupdate, and entrytime in the specific booking (same as restaurant method)
            $data[$bookingIndex]['bookingDate'] = $bookingDate;
            $data[$bookingIndex]['pickupdate'] = $pickupDate;
            $data[$bookingIndex]['entrytime'] = $entryTime;

            // Update the database
            $updated = DB::table('orders')
                ->where('id', $guideOrder->id)
                ->update([
                    'data' => json_encode($data),
                    'updated_at' => now()
                ]);

            if ($updated) {
                Log::info('Guide booking updated successfully', [
                    'order_id' => $guideOrder->id,
                    'tour_id' => $tourId,
                    'booking_date' => $bookingDate,
                    'pickup_date' => $pickupDate,
                    'entry_time' => $entryTime
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Guide booking updated successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update guide booking'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error updating guide booking', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the guide booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get hotel booking data for editing
     * 
     * This method retrieves the current hotel booking data for the edit modal.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getHotelBookingData(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'tour_id' => 'required|integer',
                'hotel_order_index' => 'required|integer|min:0',
                'booking_index' => 'required|integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $tourId = $request->tour_id;
            $hotelOrderIndex = $request->hotel_order_index;
            $bookingIndex = $request->booking_index;

            // Get tour data
            $tour = DB::table('tours')->where('tour_id', $tourId)->first();
            
            // Find the hotel booking
            $hotelOrder = DB::table('orders')
                ->where('tour_id', $tourId)
                ->where('type', 'hotel')
                ->orderBy('id')
                ->skip($hotelOrderIndex)
                ->first();

            if (!$hotelOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hotel order not found'
                ], 404);
            }

            $hotelData = json_decode($hotelOrder->data, true);
            
            if (!$hotelData || !isset($hotelData[$bookingIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hotel booking not found'
                ], 404);
            }

            $booking = $hotelData[$bookingIndex];
            
            Log::info('Hotel booking data found', [
                'tour_id' => $tourId,
                'hotel_order_index' => $hotelOrderIndex,
                'booking_index' => $bookingIndex,
                'hotel_name' => $booking['hotelDetails']['hotel_name'] ?? 'Unknown',
                'total_price' => $booking['totalPrice'] ?? 0,
                'booking_dates' => $booking['bookingDate'] ?? [],
                'full_booking_keys' => array_keys($booking)
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'tour' => [
                        'tour_id' => $tour->tour_id ?? $tourId,
                        'check_in_time' => $tour->check_in_time ?? null,
                        'check_out_time' => $tour->check_out_time ?? null
                    ],
                    'hotel_booking' => [
                        'booking_id' => $hotelOrder->id,
                        'hotel_name' => $booking['hotelDetails']['hotel_name'] ?? 'Unknown Hotel',
                        'hotel_id' => $booking['hotelDetails']['hotel_id'] ?? null,
                        'location' => $booking['hotelDetails']['location'] ?? 'N/A',
                        'image' => $booking['hotelDetails']['image'] ?? null,
                        'total_price' => $booking['totalPrice'] ?? 0,
                        'room_count' => count($booking['rooms'] ?? []),
                        'booking_dates' => $booking['bookingDate'] ?? [],
                        'check_in_time' => $booking['hotelDetails']['checkInTime'] ?? '12:00 PM',
                        'check_out_time' => $booking['hotelDetails']['checkOutTime'] ?? '11:00 AM',
                        'cancellation_charge' => $booking['hotelDetails']['cancellation_charge'] ?? null,
                        // Customer information
                        'full_name' => $booking['fullName'] ?? 'N/A',
                        'email' => $booking['email'] ?? 'N/A',
                        'phone' => $booking['phone'] ?? 'N/A',
                        'address' => $booking['address1'] ?? 'N/A',
                        'special_requests' => $booking['specialRequests'] ?? null,
                        // Room details
                        'rooms' => $booking['rooms'] ?? [],
                        'hotel_details' => $booking['hotelDetails'] ?? []
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting hotel booking data', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving hotel booking data'
            ], 500);
        }
    }

    /**
     * Update arrival booking data
     */
    public function updateArrivalBooking(Request $request)
    {
        Log::info('Arrival booking update request received', $request->all());

        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'tour_id' => 'required|integer',
                'arrival_order_index' => 'required|integer',
                'booking_index' => 'required|integer',
                'booking_date' => 'required|date',
                'pickup_date' => 'required|date',
                'entry_time' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'request_data' => $request->all()
                ], 422);
            }

            $tourId = $request->tour_id;
            $arrivalOrderIndex = $request->arrival_order_index;
            $bookingIndex = $request->booking_index;
            $bookingDate = $request->booking_date;
            $pickupDate = $request->pickup_date;
            $entryTime = $request->entry_time;

            // Find the arrival order
            $arrivalOrder = DB::table('orders')
                ->where('tour_id', $tourId)
                ->where('type', 'entry_port')
                ->skip($arrivalOrderIndex)
                ->first();

            if (!$arrivalOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arrival order not found'
                ], 404);
            }

            // Decode the JSON data
            $data = json_decode($arrivalOrder->data, true);
            
            if (!is_array($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid arrival data format'
                ], 400);
            }

            // Validate booking index
            $bookingKeys = array_keys($data);
            if (!isset($bookingKeys[$bookingIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking index not found'
                ], 404);
            }

            $actualKey = $bookingKeys[$bookingIndex];

            // Get tour dates for validation
            $tour = DB::table('tours')->where('tour_id', $tourId)->first();
            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found'
                ], 404);
            }

            $tourStartDate = new \DateTime($tour->check_in_time);
            $tourEndDate = new \DateTime($tour->check_out_time);
            $bookingDateTime = new \DateTime($bookingDate);
            $pickupDateTime = new \DateTime($pickupDate);

            // Validate dates are within tour range
            if ($bookingDateTime < $tourStartDate || $bookingDateTime > $tourEndDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking date must be within tour travel dates'
                ], 400);
            }

            if ($pickupDateTime < $tourStartDate || $pickupDateTime > $tourEndDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pickup date must be within tour travel dates'
                ], 400);
            }

            // Update the specific booking data
            $data[$actualKey]['bookingDate'] = $bookingDate;
            $data[$actualKey]['pickupdate'] = $pickupDate;
            $data[$actualKey]['entrytime'] = $entryTime;

            // Update the database
            $updated = DB::table('orders')
                ->where('id', $arrivalOrder->id)
                ->update([
                    'data' => json_encode($data),
                    'updated_at' => now()
                ]);

            if ($updated) {
                Log::info('Arrival booking updated successfully', [
                    'order_id' => $arrivalOrder->id,
                    'tour_id' => $tourId,
                    'booking_date' => $bookingDate,
                    'pickup_date' => $pickupDate,
                    'entry_time' => $entryTime
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Arrival booking updated successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update arrival booking'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error updating arrival booking', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the arrival booking'
            ], 500);
        }
    }

    /**
     * Get arrival booking data
     */
    public function getArrivalBookingData(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'tour_id' => 'required|integer',
                'arrival_order_index' => 'required|integer',
                'booking_index' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $tourId = $request->tour_id;
            $arrivalOrderIndex = $request->arrival_order_index;
            $bookingIndex = $request->booking_index;

            // Get tour data
            $tour = DB::table('tours')->where('tour_id', $tourId)->first();
            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found'
                ], 404);
            }

            // Find the arrival order
            $arrivalOrder = DB::table('orders')
                ->where('tour_id', $tourId)
                ->where('type', 'entry_port')
                ->skip($arrivalOrderIndex)
                ->first();

            if (!$arrivalOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arrival order not found'
                ], 404);
            }

            // Decode the JSON data
            $data = json_decode($arrivalOrder->data, true);
            
            if (!is_array($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid arrival data format'
                ], 400);
            }

            // Validate booking index
            $bookingKeys = array_keys($data);
            if (!isset($bookingKeys[$bookingIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking index not found'
                ], 404);
            }

            $actualKey = $bookingKeys[$bookingIndex];
            $specificBooking = $data[$actualKey];

            // Extract arrival data
            $vehicleName = $specificBooking['vehicles_name'] ?? 'Unknown Vehicle';
            $bookingDate = $specificBooking['bookingDate'] ?? null;
            $pickupDate = $specificBooking['pickupdate'] ?? null;
            $entryTime = $specificBooking['entrytime'] ?? null;
            $totalPrice = $specificBooking['totalPrice'] ?? 0;
            $adults = $specificBooking['adults'] ?? 0;
            $children = $specificBooking['children'] ?? 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'vehicle_name' => $vehicleName,
                    'booking_date' => $bookingDate,
                    'pickup_date' => $pickupDate,
                    'entry_time' => $entryTime,
                    'total_price' => $totalPrice,
                    'adults' => $adults,
                    'children' => $children,
                    'arrival_details' => $specificBooking // Include full booking data for modal display
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting arrival booking data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving arrival booking data'
            ], 500);
        }
    }

    public function updateDepartureBooking(Request $request)
    {
        try {
            Log::info('Departure booking update request received', $request->all());

            // Validation
            $validator = Validator::make($request->all(), [
                'tour_id' => 'required|integer',
                'departure_order_index' => 'required|integer|min:0',
                'booking_index' => 'required|integer|min:0',
                'booking_date' => 'required|date',
                'exit_pickup_date' => 'required|date',
                'entry_time' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors(),
                    'request_data' => $request->all()
                ], 400);
            }

            $tourId = $request->tour_id;
            $departureOrderIndex = $request->departure_order_index;
            $bookingIndex = $request->booking_index;
            $bookingDate = $request->booking_date;
            $exitPickupDate = $request->exit_pickup_date;
            $entryTime = $request->entry_time;

            // Find the departure order
            $departureOrder = DB::table('orders')
                ->where('tour_id', $tourId)
                ->where('type', 'exit_port')
                ->skip($departureOrderIndex)
                ->first();

            if (!$departureOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Departure order not found'
                ], 404);
            }

            // Decode the JSON data
            $data = json_decode($departureOrder->data, true);
            if (!is_array($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid departure data format'
                ], 400);
            }

            // Validate booking index
            $keys = array_keys($data);
            if (!isset($keys[$bookingIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking index not found'
                ], 404);
            }

            $actualKey = $keys[$bookingIndex];

            // Get tour dates for validation
            $tour = DB::table('tours')->where('tour_id', $tourId)->first();
            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found'
                ], 404);
            }

            $tourStartDate = new \DateTime($tour->check_in_time);
            $tourEndDate = new \DateTime($tour->check_out_time);
            $bookingDateTime = new \DateTime($bookingDate);
            $exitPickupDateTime = new \DateTime($exitPickupDate);

            // Validate dates are within tour range
            if ($bookingDateTime < $tourStartDate || $bookingDateTime > $tourEndDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking date must be within tour travel dates'
                ], 400);
            }

            if ($exitPickupDateTime < $tourStartDate || $exitPickupDateTime > $tourEndDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Exit pickup date must be within tour travel dates'
                ], 400);
            }

            // Update the specific booking data
            $data[$actualKey]['bookingDate'] = $bookingDate;
            $data[$actualKey]['exitpickupdate'] = $exitPickupDate;
            $data[$actualKey]['entrytime'] = $entryTime;

            // Update the database
            $updated = DB::table('orders')
                ->where('id', $departureOrder->id)
                ->update([
                    'data' => json_encode($data),
                    'updated_at' => now()
                ]);

            if ($updated) {
                Log::info('Departure booking updated successfully', [
                    'order_id' => $departureOrder->id,
                    'tour_id' => $tourId,
                    'booking_date' => $bookingDate,
                    'exit_pickup_date' => $exitPickupDate,
                    'entry_time' => $entryTime
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Departure booking updated successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update departure booking'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error updating departure booking', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the departure booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getDepartureBookingData(Request $request)
    {
        try {
            // Validation
            $validator = Validator::make($request->all(), [
                'tour_id' => 'required|integer',
                'departure_order_index' => 'required|integer|min:0',
                'booking_index' => 'required|integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first()
                ], 400);
            }

            $tourId = $request->tour_id;
            $departureOrderIndex = $request->departure_order_index;
            $bookingIndex = $request->booking_index;

            // Get tour data
            $tour = DB::table('tours')->where('tour_id', $tourId)->first();
            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found'
                ], 404);
            }

            // Find the departure order
            $departureOrder = DB::table('orders')
                ->where('tour_id', $tourId)
                ->where('type', 'exit_port')
                ->skip($departureOrderIndex)
                ->first();

            if (!$departureOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Departure order not found'
                ], 404);
            }

            // Decode the JSON data
            $data = json_decode($departureOrder->data, true);
            if (!is_array($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid departure data format'
                ], 400);
            }

            // Validate booking index and get the specific booking
            $keys = array_keys($data);
            if (!isset($keys[$bookingIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking index not found'
                ], 404);
            }

            $specificBooking = $data[$keys[$bookingIndex]];

            // Extract relevant data
            $departureData = [
                'vehicles_name' => $specificBooking['vehicles_name'] ?? 'N/A',
                'booking_date' => $specificBooking['bookingDate'] ?? null,
                'exit_pickup_date' => $specificBooking['exitpickupdate'] ?? null,
                'entry_time' => $specificBooking['entrytime'] ?? null,
                'total_price' => $specificBooking['totalPrice'] ?? 0,
                'adults' => $specificBooking['adults'] ?? 0,
                'children' => $specificBooking['children'] ?? 0,
                'tour_start_date' => $tour->check_in_time,
                'tour_end_date' => $tour->check_out_time
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'tour_id' => $tourId,
                    'departure_order_index' => $departureOrderIndex,
                    'booking_index' => $bookingIndex,
                    'vehicles_name' => $departureData['vehicles_name'],
                    'booking_date' => $departureData['booking_date'],
                    'exit_pickup_date' => $departureData['exit_pickup_date'],
                    'entry_time' => $departureData['entry_time'],
                    'total_price' => $departureData['total_price'],
                    'adults' => $departureData['adults'],
                    'children' => $departureData['children'],
                    'tour_start_date' => $departureData['tour_start_date'],
                    'tour_end_date' => $departureData['tour_end_date'],
                    'departure_details' => $specificBooking // Include full booking data for detailed display
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching departure booking data', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching departure booking data'
            ], 500);
        }
    }

    public function updateTravelPointBooking(Request $request)
    {
        try {
            Log::info('Travel point booking update request received', $request->all());

            // Validation
            $validator = Validator::make($request->all(), [
                'tour_id' => 'required|integer',
                'travel_point_order_index' => 'required|integer|min:0',
                'booking_index' => 'required|integer|min:0',
                'booking_date' => 'required|date',
                'pickup_date' => 'required|date',
                'entry_time' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors(),
                    'request_data' => $request->all()
                ], 400);
            }

            $tourId = $request->tour_id;
            $travelPointOrderIndex = $request->travel_point_order_index;
            $bookingIndex = $request->booking_index;
            $bookingDate = $request->booking_date;
            $pickupDate = $request->pickup_date;
            $entryTime = $request->entry_time;

            // Find the travel point order
            $travelPointOrder = DB::table('orders')
                ->where('tour_id', $tourId)
                ->where('type', 'travel_point')
                ->skip($travelPointOrderIndex)
                ->first();

            if (!$travelPointOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Travel point order not found'
                ], 404);
            }

            // Decode the JSON data
            $data = json_decode($travelPointOrder->data, true);
            if (!is_array($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid travel point data format'
                ], 400);
            }

            // Validate booking index
            $keys = array_keys($data);
            if (!isset($keys[$bookingIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking index not found'
                ], 404);
            }

            $actualKey = $keys[$bookingIndex];

            // Get tour dates for validation
            $tour = DB::table('tours')->where('tour_id', $tourId)->first();
            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found'
                ], 404);
            }

            $tourStartDate = new \DateTime($tour->check_in_time);
            $tourEndDate = new \DateTime($tour->check_out_time);
            $bookingDateTime = new \DateTime($bookingDate);
            $pickupDateTime = new \DateTime($pickupDate);

            // Validate dates are within tour range
            if ($bookingDateTime < $tourStartDate || $bookingDateTime > $tourEndDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking date must be within tour travel dates'
                ], 400);
            }

            if ($pickupDateTime < $tourStartDate || $pickupDateTime > $tourEndDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pickup date must be within tour travel dates'
                ], 400);
            }

            // Update the specific booking data
            $data[$actualKey]['bookingDate'] = $bookingDate;
            $data[$actualKey]['pickupdate'] = $pickupDate;
            $data[$actualKey]['entrytime'] = $entryTime;

            // Update the database
            $updated = DB::table('orders')
                ->where('id', $travelPointOrder->id)
                ->update([
                    'data' => json_encode($data),
                    'updated_at' => now()
                ]);

            if ($updated) {
                Log::info('Travel point booking updated successfully', [
                    'order_id' => $travelPointOrder->id,
                    'tour_id' => $tourId,
                    'booking_date' => $bookingDate,
                    'pickup_date' => $pickupDate,
                    'entry_time' => $entryTime
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Travel point booking updated successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update travel point booking'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error updating travel point booking', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the travel point booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getTravelPointBookingData(Request $request)
    {
        try {
            // Validation
            $validator = Validator::make($request->all(), [
                'tour_id' => 'required|integer',
                'travel_point_order_index' => 'required|integer|min:0',
                'booking_index' => 'required|integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first()
                ], 400);
            }

            $tourId = $request->tour_id;
            $travelPointOrderIndex = $request->travel_point_order_index;
            $bookingIndex = $request->booking_index;

            // Get tour data
            $tour = DB::table('tours')->where('tour_id', $tourId)->first();
            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found'
                ], 404);
            }

            // Find the travel point order
            $travelPointOrder = DB::table('orders')
                ->where('tour_id', $tourId)
                ->where('type', 'travel_point')
                ->skip($travelPointOrderIndex)
                ->first();

            if (!$travelPointOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Travel point order not found'
                ], 404);
            }

            // Decode the JSON data
            $data = json_decode($travelPointOrder->data, true);
            if (!is_array($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid travel point data format'
                ], 400);
            }

            // Validate booking index and get the specific booking
            $keys = array_keys($data);
            if (!isset($keys[$bookingIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking index not found'
                ], 404);
            }

            $specificBooking = $data[$keys[$bookingIndex]];

            // Extract relevant data
            $travelPointData = [
                'vehicles_name' => $specificBooking['vehicles_name'] ?? 'N/A',
                'booking_date' => $specificBooking['bookingDate'] ?? null,
                'pickup_date' => $specificBooking['pickupdate'] ?? null,
                'entry_time' => $specificBooking['entrytime'] ?? null,
                'total_price' => $specificBooking['totalPrice'] ?? 0,
                'adults' => $specificBooking['adults'] ?? 0,
                'children' => $specificBooking['children'] ?? 0,
                'tour_start_date' => $tour->check_in_time,
                'tour_end_date' => $tour->check_out_time
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'tour_id' => $tourId,
                    'travel_point_order_index' => $travelPointOrderIndex,
                    'booking_index' => $bookingIndex,
                    'vehicles_name' => $travelPointData['vehicles_name'],
                    'booking_date' => $travelPointData['booking_date'],
                    'pickup_date' => $travelPointData['pickup_date'],
                    'entry_time' => $travelPointData['entry_time'],
                    'total_price' => $travelPointData['total_price'],
                    'adults' => $travelPointData['adults'],
                    'children' => $travelPointData['children'],
                    'tour_start_date' => $travelPointData['tour_start_date'],
                    'tour_end_date' => $travelPointData['tour_end_date'],
                    'travel_point_details' => $specificBooking // Include full booking data for detailed display
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching travel point booking data', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching travel point booking data'
            ], 500);
        }
    }

    public function updateTravelHourlyBooking(Request $request)
    {
        try {
            Log::info('Travel hourly booking update request received', $request->all());

            // Validation
            $validator = Validator::make($request->all(), [
                'tour_id' => 'required|integer',
                'travel_hourly_order_index' => 'required|integer|min:0',
                'booking_index' => 'required|integer|min:0',
                'booking_date' => 'required|date',
                'exit_pickup_date' => 'required|date',
                'entry_time' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors(),
                    'request_data' => $request->all()
                ], 400);
            }

            $tourId = $request->tour_id;
            $travelHourlyOrderIndex = $request->travel_hourly_order_index;
            $bookingIndex = $request->booking_index;
            $bookingDate = $request->booking_date;
            $exitPickupDate = $request->exit_pickup_date;
            $entryTime = $request->entry_time;

            // Find the travel hourly order
            $travelHourlyOrder = DB::table('orders')
                ->where('tour_id', $tourId)
                ->where('type', 'travel_hourly')
                ->skip($travelHourlyOrderIndex)
                ->first();

            if (!$travelHourlyOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Travel hourly order not found'
                ], 404);
            }

            // Decode the JSON data
            $data = json_decode($travelHourlyOrder->data, true);
            if (!is_array($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid travel hourly data format'
                ], 400);
            }

            // Validate booking index
            $keys = array_keys($data);
            if (!isset($keys[$bookingIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking index not found'
                ], 404);
            }

            $actualKey = $keys[$bookingIndex];

            // Get tour dates for validation
            $tour = DB::table('tours')->where('tour_id', $tourId)->first();
            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found'
                ], 404);
            }

            $tourStartDate = new \DateTime($tour->check_in_time);
            $tourEndDate = new \DateTime($tour->check_out_time);
            $bookingDateTime = new \DateTime($bookingDate);
            $exitPickupDateTime = new \DateTime($exitPickupDate);

            // Validate dates are within tour range
            if ($bookingDateTime < $tourStartDate || $bookingDateTime > $tourEndDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking date must be within tour travel dates'
                ], 400);
            }

            if ($exitPickupDateTime < $tourStartDate || $exitPickupDateTime > $tourEndDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Exit pickup date must be within tour travel dates'
                ], 400);
            }

            // Update the specific booking data
            $data[$actualKey]['bookingDate'] = $bookingDate;
            $data[$actualKey]['exitpickupdate'] = $exitPickupDate;
            $data[$actualKey]['entrytime'] = $entryTime;

            // Update the database
            $updated = DB::table('orders')
                ->where('id', $travelHourlyOrder->id)
                ->update([
                    'data' => json_encode($data),
                    'updated_at' => now()
                ]);

            if ($updated) {
                Log::info('Travel hourly booking updated successfully', [
                    'order_id' => $travelHourlyOrder->id,
                    'tour_id' => $tourId,
                    'booking_date' => $bookingDate,
                    'exit_pickup_date' => $exitPickupDate,
                    'entry_time' => $entryTime
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Travel hourly booking updated successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update travel hourly booking'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error updating travel hourly booking', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the travel hourly booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getTravelHourlyBookingData(Request $request)
    {
        try {
            // Validation
            $validator = Validator::make($request->all(), [
                'tour_id' => 'required|integer',
                'travel_hourly_order_index' => 'required|integer|min:0',
                'booking_index' => 'required|integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first()
                ], 400);
            }

            $tourId = $request->tour_id;
            $travelHourlyOrderIndex = $request->travel_hourly_order_index;
            $bookingIndex = $request->booking_index;

            // Get tour data
            $tour = DB::table('tours')->where('tour_id', $tourId)->first();
            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found'
                ], 404);
            }

            // Find the travel hourly order
            $travelHourlyOrder = DB::table('orders')
                ->where('tour_id', $tourId)
                ->where('type', 'travel_hourly')
                ->skip($travelHourlyOrderIndex)
                ->first();

            if (!$travelHourlyOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Travel hourly order not found'
                ], 404);
            }

            // Decode the JSON data
            $data = json_decode($travelHourlyOrder->data, true);
            if (!is_array($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid travel hourly data format'
                ], 400);
            }

            // Validate booking index and get the specific booking
            $keys = array_keys($data);
            if (!isset($keys[$bookingIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking index not found'
                ], 404);
            }

            $specificBooking = $data[$keys[$bookingIndex]];

            // Extract relevant data
            $travelHourlyData = [
                'vehicles_name' => $specificBooking['vehicles_name'] ?? 'N/A',
                'booking_date' => $specificBooking['bookingDate'] ?? null,
                'exit_pickup_date' => $specificBooking['exitpickupdate'] ?? null,
                'entry_time' => $specificBooking['entrytime'] ?? null,
                'total_price' => $specificBooking['totalPrice'] ?? 0,
                'adults' => $specificBooking['adults'] ?? 0,
                'children' => $specificBooking['children'] ?? 0,
                'selected_hours' => $specificBooking['selectedHours'] ?? 1,
                'tour_start_date' => $tour->check_in_time,
                'tour_end_date' => $tour->check_out_time
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'tour_id' => $tourId,
                    'travel_hourly_order_index' => $travelHourlyOrderIndex,
                    'booking_index' => $bookingIndex,
                    'vehicles_name' => $travelHourlyData['vehicles_name'],
                    'booking_date' => $travelHourlyData['booking_date'],
                    'exit_pickup_date' => $travelHourlyData['exit_pickup_date'],
                    'entry_time' => $travelHourlyData['entry_time'],
                    'total_price' => $travelHourlyData['total_price'],
                    'adults' => $travelHourlyData['adults'],
                    'children' => $travelHourlyData['children'],
                    'selected_hours' => $travelHourlyData['selected_hours'],
                    'tour_start_date' => $travelHourlyData['tour_start_date'],
                    'tour_end_date' => $travelHourlyData['tour_end_date'],
                    'travel_hourly_details' => $specificBooking // Include full booking data for detailed display
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching travel hourly booking data', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching travel hourly booking data'
            ], 500);
        }
    }

    public function updateLocalTransportBooking(Request $request)
    {
        try {
            Log::info('Local transport booking update request received', $request->all());

            // Validation
            $validator = Validator::make($request->all(), [
                'tour_id' => 'required|integer',
                'local_transport_order_index' => 'required|integer|min:0',
                'booking_index' => 'required|integer|min:0',
                'booking_date' => 'required|date',
                'pickup_date' => 'required|date',
                'entry_time' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors(),
                    'request_data' => $request->all()
                ], 400);
            }

            $tourId = $request->tour_id;
            $localTransportOrderIndex = $request->local_transport_order_index;
            $bookingIndex = $request->booking_index;
            $bookingDate = $request->booking_date;
            $pickupDate = $request->pickup_date;
            $entryTime = $request->entry_time;

            // Find the local transport order
            $localTransportOrder = DB::table('orders')
                ->where('tour_id', $tourId)
                ->where('type', 'local_transport')
                ->skip($localTransportOrderIndex)
                ->first();

            if (!$localTransportOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Local transport order not found'
                ], 404);
            }

            // Decode the JSON data
            $data = json_decode($localTransportOrder->data, true);
            if (!is_array($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid local transport data format'
                ], 400);
            }

            // Validate booking index
            $keys = array_keys($data);
            if (!isset($keys[$bookingIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking index not found'
                ], 404);
            }

            $actualKey = $keys[$bookingIndex];

            // Get tour dates for validation
            $tour = DB::table('tours')->where('tour_id', $tourId)->first();
            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found'
                ], 404);
            }

            $tourStartDate = new \DateTime($tour->check_in_time);
            $tourEndDate = new \DateTime($tour->check_out_time);
            $bookingDateTime = new \DateTime($bookingDate);
            $pickupDateTime = new \DateTime($pickupDate);

            // Validate dates are within tour range
            if ($bookingDateTime < $tourStartDate || $bookingDateTime > $tourEndDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking date must be within tour travel dates'
                ], 400);
            }

            if ($pickupDateTime < $tourStartDate || $pickupDateTime > $tourEndDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pickup date must be within tour travel dates'
                ], 400);
            }

            // Update the specific booking data
            $data[$actualKey]['bookingDate'] = $bookingDate;
            $data[$actualKey]['pickupdate'] = $pickupDate;
            $data[$actualKey]['entrytime'] = $entryTime;

            // Update the database
            $updated = DB::table('orders')
                ->where('id', $localTransportOrder->id)
                ->update([
                    'data' => json_encode($data),
                    'updated_at' => now()
                ]);

            if ($updated) {
                Log::info('Local transport booking updated successfully', [
                    'order_id' => $localTransportOrder->id,
                    'tour_id' => $tourId,
                    'booking_date' => $bookingDate,
                    'pickup_date' => $pickupDate,
                    'entry_time' => $entryTime
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Local transport booking updated successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update local transport booking'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error updating local transport booking', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the local transport booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getLocalTransportBookingData(Request $request)
    {
        try {
            // Validation
            $validator = Validator::make($request->all(), [
                'tour_id' => 'required|integer',
                'local_transport_order_index' => 'required|integer|min:0',
                'booking_index' => 'required|integer|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first()
                ], 400);
            }

            $tourId = $request->tour_id;
            $localTransportOrderIndex = $request->local_transport_order_index;
            $bookingIndex = $request->booking_index;

            // Get tour data
            $tour = DB::table('tours')->where('tour_id', $tourId)->first();
            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found'
                ], 404);
            }

            // Find the local transport order
            $localTransportOrder = DB::table('orders')
                ->where('tour_id', $tourId)
                ->where('type', 'local_transport')
                ->skip($localTransportOrderIndex)
                ->first();

            if (!$localTransportOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Local transport order not found'
                ], 404);
            }

            // Decode the JSON data
            $data = json_decode($localTransportOrder->data, true);
            if (!is_array($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid local transport data format'
                ], 400);
            }

            // Validate booking index and get the specific booking
            $keys = array_keys($data);
            if (!isset($keys[$bookingIndex])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking index not found'
                ], 404);
            }

            $specificBooking = $data[$keys[$bookingIndex]];

            // Extract relevant data
            $localTransportData = [
                'vehicles_name' => $specificBooking['vehicles_name'] ?? 'N/A',
                'booking_date' => $specificBooking['bookingDate'] ?? null,
                'pickup_date' => $specificBooking['pickupdate'] ?? null,
                'entry_time' => $specificBooking['entrytime'] ?? null,
                'total_price' => $specificBooking['totalPrice'] ?? 0,
                'adults' => $specificBooking['adults'] ?? 0,
                'children' => $specificBooking['children'] ?? 0,
                'tour_start_date' => $tour->check_in_time,
                'tour_end_date' => $tour->check_out_time
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'tour_id' => $tourId,
                    'local_transport_order_index' => $localTransportOrderIndex,
                    'booking_index' => $bookingIndex,
                    'vehicles_name' => $localTransportData['vehicles_name'],
                    'booking_date' => $localTransportData['booking_date'],
                    'pickup_date' => $localTransportData['pickup_date'],
                    'entry_time' => $localTransportData['entry_time'],
                    'total_price' => $localTransportData['total_price'],
                    'adults' => $localTransportData['adults'],
                    'children' => $localTransportData['children'],
                    'tour_start_date' => $localTransportData['tour_start_date'],
                    'tour_end_date' => $localTransportData['tour_end_date'],
                    'local_transport_details' => $specificBooking // Include full booking data for detailed display
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching local transport booking data', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching local transport booking data'
            ], 500);
        }
    }
}
