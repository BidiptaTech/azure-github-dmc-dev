<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tour;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EditTourController extends Controller
{
    /**
     * Update tour information.
     */
    public function updateTour(Request $request, $tour)
    {
        // Manually resolve tour by tour_id since route model binding uses 'id' by default
        $tour = Tour::where('tour_id', $tour)->firstOrFail();

        $validated = $request->validate([
            'display_id' => 'nullable|string|max:255',
            'user_country' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0',
            'infants' => 'required|integer|min:0',
            'male' => 'required|integer|min:0',
            'female' => 'required|integer|min:0',
            'agent_id' => 'required|exists:agents,agent_id',
            'child_ages' => 'nullable|string|max:255',
            'delete_affected_services' => 'nullable|boolean', // Flag to delete services outside date range
        ]);

        try {
            DB::beginTransaction();

            $checkIn = Carbon::createFromFormat('Y-m-d', $validated['start_date']);
            $checkOut = Carbon::createFromFormat('Y-m-d', $validated['end_date']);
            
            // Check if dates are changing
            $oldCheckIn = $tour->check_in_time ? Carbon::parse($tour->check_in_time) : null;
            $oldCheckOut = $tour->check_out_time ? Carbon::parse($tour->check_out_time) : null;
            $datesChanged = false;
            
            if ($oldCheckIn && $oldCheckOut) {
                $datesChanged = !$checkIn->equalTo($oldCheckIn) || !$checkOut->equalTo($oldCheckOut);
            } else {
                $datesChanged = true;
            }
            
            // If dates changed, check for affected services
            $affectedServices = [];
            $deletedServicesCount = 0;
            if ($datesChanged) {
                $affectedServices = $this->getServicesOutsideDateRange($tour->tour_id, $checkIn, $checkOut);
                
                // If delete flag is set, delete affected services
                // Handle boolean values from form (can be '1', 'true', 'on', or actual boolean)
                $shouldDelete = $request->has('delete_affected_services') && 
                               ($request->delete_affected_services === true || 
                                $request->delete_affected_services === '1' || 
                                $request->delete_affected_services === 'true' || 
                                $request->delete_affected_services === 'on');
                
                if ($shouldDelete && !empty($affectedServices)) {
                    $deletedServicesCount = count($affectedServices);
                    $this->deleteServicesOutsideDateRange($affectedServices);
                    $affectedServices = []; // Clear after deletion
                } else if (!empty($affectedServices)) {
                    // Return affected services for frontend alert (don't update tour yet)
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'requires_confirmation' => true,
                        'message' => 'Changing tour dates will affect services outside the new date range.',
                        'affected_services' => $affectedServices,
                        'new_start_date' => $checkIn->format('Y-m-d'),
                        'new_end_date' => $checkOut->format('Y-m-d'),
                    ], 200);
                }
            }

            if (!empty($validated['display_id'])) {
                $tour->display_id = $validated['display_id'];
            }
            $tour->destination = $validated['user_country'];
            $tour->check_in_time = $checkIn;
            $tour->check_out_time = $checkOut;
            $tour->adult = $validated['adults'];
            $tour->child = $validated['children'];
            $tour->infant = $validated['infants'];
            $tour->male_count = $validated['male'];
            $tour->female_count = $validated['female'];
            $tour->agent_id = $validated['agent_id'];
            $tour->child_ages = !empty($validated['child_ages']) ? $validated['child_ages'] : null;
            
            $saved = $tour->save();
            
            if (!$saved) {
                throw new \Exception('Failed to save tour information.');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tour information updated successfully.',
                'data' => [
                    'display_id' => $tour->display_id,
                    'destination' => $tour->destination,
                    'check_in_time' => $tour->check_in_time?->toDateString(),
                    'check_out_time' => $tour->check_out_time?->toDateString(),
                    'adults' => $tour->adult,
                    'children' => $tour->child,
                    'infants' => $tour->infant,
                    'male' => $tour->male_count,
                    'female' => $tour->female_count,
                    'agent_id' => $tour->agent_id,
                ],
                'deleted_services_count' => $deletedServicesCount,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Failed to update tour information', [
                'tour_id' => $tour->tour_id ?? null,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to save tour information right now.',
                'error' => config('app.debug') ? $exception->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update hotel service order.
     */
    public function updateHotel(Request $request, $orderId)
    {
        $order = Order::where('booking_id', $orderId)->firstOrFail();
        $validated = $request->validate([
            'hotel_name' => 'nullable|string|max:255',
            'hotel_location' => 'nullable|string|max:255',
            'hotel_id' => 'nullable|string|max:255',
            'check_in_date' => 'nullable|date',
            'check_out_date' => 'nullable|date|after_or_equal:check_in_date',
            'check_in_time' => 'nullable|string|max:20',
            'check_out_time' => 'nullable|string|max:20',
            'rooms_json' => 'nullable|string',
            'notes' => 'nullable|string|max:1000',
            'days_display' => 'nullable|string|max:255',
            'number_of_rooms' => 'nullable|integer|min:1',
            'room_type' => 'nullable|string|max:255',
            'bed_type' => 'nullable|string|max:255',
            'meal_plan' => 'nullable|string|max:255',
            'number_of_persons' => 'nullable|integer|min:1',
            'total_price' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Log incoming request data for debugging
            Log::info('updateHotel called', [
                'order_id' => $orderId,
                'request_data' => $request->except(['_token']),
                'validated' => $validated,
            ]);

            // Step 1: Get existing data from database FIRST (before clearing) to preserve structure
            $existingDataRaw = $order->data;
            $hasExistingData = !empty($existingDataRaw) && 
                              !(is_array($existingDataRaw) && empty($existingDataRaw)) && 
                              !(is_string($existingDataRaw) && (trim($existingDataRaw) === '' || $existingDataRaw === '[]' || $existingDataRaw === 'null'));

            // Decode existing data to use as template
            $originalPayload = [];
            if ($hasExistingData) {
                $decodedExisting = is_array($existingDataRaw) ? $existingDataRaw : json_decode($existingDataRaw, true);
                if (isset($decodedExisting[0])) {
                    $originalPayload = $decodedExisting[0];
                } else {
                    $originalPayload = $decodedExisting;
                }
            }

            // Step 2: Don't clear data - we'll update it directly to preserve all fields

            // Step 3: Create deep copy of original payload to preserve ALL fields and nested structures
            // This matches the exact format from storeServiceOrders method
            if (!empty($originalPayload)) {
                $currentPayload = json_decode(json_encode($originalPayload), true);
                if (!is_array($currentPayload)) {
                    $currentPayload = [];
                }
            } else {
                $currentPayload = [];
            }

            // Step 4: Ensure all required fields exist - only add if missing, never overwrite existing values
            // This preserves the exact structure from the original data
            if (!array_key_exists('fullName', $currentPayload)) {
                $currentPayload['fullName'] = $originalPayload['fullName'] ?? '';
            }
            if (!array_key_exists('email', $currentPayload)) {
                $currentPayload['email'] = $originalPayload['email'] ?? '';
            }
            if (!array_key_exists('phone', $currentPayload)) {
                $currentPayload['phone'] = $originalPayload['phone'] ?? '';
            }
            if (!array_key_exists('countryCode', $currentPayload)) {
                $currentPayload['countryCode'] = $originalPayload['countryCode'] ?? '';
            }
            if (!array_key_exists('address1', $currentPayload)) {
                $currentPayload['address1'] = $originalPayload['address1'] ?? '';
            }
            if (!array_key_exists('address2', $currentPayload)) {
                $currentPayload['address2'] = $originalPayload['address2'] ?? null;
            }
            if (!array_key_exists('state', $currentPayload)) {
                $currentPayload['state'] = $originalPayload['state'] ?? '';
            }
            if (!array_key_exists('zip', $currentPayload)) {
                $currentPayload['zip'] = $originalPayload['zip'] ?? '';
            }
            if (!array_key_exists('specialRequests', $currentPayload)) {
                $currentPayload['specialRequests'] = $originalPayload['specialRequests'] ?? '';
            }
            if (!array_key_exists('id', $currentPayload)) {
                $currentPayload['id'] = $originalPayload['id'] ?? null;
            }
            if (!array_key_exists('bookingType', $currentPayload)) {
                $currentPayload['bookingType'] = $originalPayload['bookingType'] ?? 'booking';
            }
            if (!array_key_exists('priceMode', $currentPayload)) {
                $currentPayload['priceMode'] = $originalPayload['priceMode'] ?? 'dmc';
            }
            if (!array_key_exists('priceModeId', $currentPayload)) {
                $currentPayload['priceModeId'] = $originalPayload['priceModeId'] ?? 0;
            }
            // Update totalPrice if provided in request (always update if key exists, even if value is 0)
            if (array_key_exists('total_price', $validated)) {
                $currentPayload['totalPrice'] = (float) ($validated['total_price'] ?? 0);
            } elseif (!array_key_exists('totalPrice', $currentPayload)) {
                $currentPayload['totalPrice'] = $originalPayload['totalPrice'] ?? 0;
            }
            if (!array_key_exists('price', $currentPayload)) {
                $currentPayload['price'] = $originalPayload['price'] ?? ($currentPayload['totalPrice'] ?? 0);
            }
            if (!array_key_exists('tour_id', $currentPayload)) {
                $currentPayload['tour_id'] = $originalPayload['tour_id'] ?? $order->tour_id;
            }
            if (!array_key_exists('rooms', $currentPayload)) {
                $existingRooms = $originalPayload['rooms'] ?? [];
                // Deduplicate existing rooms if they have duplicates
                if (is_array($existingRooms) && count($existingRooms) > 0) {
                    $uniqueExistingRooms = [];
                    $seenExistingRooms = [];
                    foreach ($existingRooms as $room) {
                        if (!is_array($room)) {
                            continue;
                        }
                        $roomId = $room['room_id'] ?? null;
                        $bedId = null;
                        if (isset($room['beds']) && is_array($room['beds']) && count($room['beds']) > 0) {
                            $bedId = $room['beds'][0]['bed_id'] ?? null;
                        }
                        $uniqueKey = $roomId . '_' . $bedId;
                        if (!isset($seenExistingRooms[$uniqueKey])) {
                            $uniqueExistingRooms[] = $room;
                            $seenExistingRooms[$uniqueKey] = true;
                        }
                    }
                    $currentPayload['rooms'] = !empty($uniqueExistingRooms) ? $uniqueExistingRooms : [];
                } else {
                    $currentPayload['rooms'] = [];
                }
            }
            if (!array_key_exists('bookingDate', $currentPayload)) {
                $currentPayload['bookingDate'] = $originalPayload['bookingDate'] ?? [];
            }
            if (!array_key_exists('days_display', $currentPayload)) {
                $currentPayload['days_display'] = $originalPayload['days_display'] ?? '';
            }

            // Step 5: Ensure hotelDetails exists and preserve all fields
            if (!isset($currentPayload['hotelDetails']) || !is_array($currentPayload['hotelDetails'])) {
                $currentPayload['hotelDetails'] = $originalPayload['hotelDetails'] ?? [];
            }
            
            $existingHotelDetails = $originalPayload['hotelDetails'] ?? [];
            
            // Update hotelDetails fields if provided in request (even if empty to allow clearing)
            if (array_key_exists('hotel_name', $validated)) {
                $currentPayload['hotelDetails']['hotel_name'] = $validated['hotel_name'] ?? '';
            }
            
            if (array_key_exists('hotel_location', $validated)) {
                $currentPayload['hotelDetails']['location'] = $validated['hotel_location'] ?? null;
            }
            
            if (array_key_exists('check_in_time', $validated)) {
                $currentPayload['hotelDetails']['checkInTime'] = $validated['check_in_time'] ?? null;
            }
            
            if (array_key_exists('check_out_time', $validated)) {
                $currentPayload['hotelDetails']['checkOutTime'] = $validated['check_out_time'] ?? null;
            }
            
            // Update hotel_id if provided (from hidden input)
            if ($request->has('hotel_id')) {
                $currentPayload['hotelDetails']['hotel_id'] = $request->input('hotel_id', '');
            }
            
            // Ensure all hotelDetails fields exist - only add if missing
            if (!array_key_exists('hotel_id', $currentPayload['hotelDetails'])) {
                $currentPayload['hotelDetails']['hotel_id'] = $existingHotelDetails['hotel_id'] ?? '';
            }
            if (!array_key_exists('hotel_name', $currentPayload['hotelDetails'])) {
                $currentPayload['hotelDetails']['hotel_name'] = $existingHotelDetails['hotel_name'] ?? '';
            }
            if (!array_key_exists('location', $currentPayload['hotelDetails'])) {
                $currentPayload['hotelDetails']['location'] = $existingHotelDetails['location'] ?? null;
            }
            if (!array_key_exists('checkInTime', $currentPayload['hotelDetails'])) {
                $currentPayload['hotelDetails']['checkInTime'] = $existingHotelDetails['checkInTime'] ?? null;
            }
            if (!array_key_exists('checkOutTime', $currentPayload['hotelDetails'])) {
                $currentPayload['hotelDetails']['checkOutTime'] = $existingHotelDetails['checkOutTime'] ?? null;
            }
            if (!array_key_exists('image', $currentPayload['hotelDetails'])) {
                $currentPayload['hotelDetails']['image'] = $existingHotelDetails['image'] ?? '';
            }
            if (!array_key_exists('cancellation_charge', $currentPayload['hotelDetails'])) {
                $currentPayload['hotelDetails']['cancellation_charge'] = $existingHotelDetails['cancellation_charge'] ?? null;
            }

            // Step 6: Update bookingDate only if provided, otherwise preserve existing
            if (!empty($validated['check_in_date']) && !empty($validated['check_out_date'])) {
                $currentPayload['bookingDate'] = [
                    $validated['check_in_date'],
                    $validated['check_out_date'],
                ];
            }

            // Step 7: Update rooms - construct from individual fields if rooms_json not provided
            $roomsUpdated = false;
            
            if (!empty($validated['rooms_json'])) {
                // Use provided rooms_json
                $rooms = json_decode($validated['rooms_json'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    // If JSON decode fails, log error but preserve existing rooms
                    Log::warning('Rooms JSON decode failed', [
                        'order_id' => $orderId,
                        'rooms_json' => substr($validated['rooms_json'], 0, 200), // Log first 200 chars
                        'json_error' => json_last_error_msg(),
                    ]);
                } else {
                    // Ensure rooms is an array
                    if (!is_array($rooms)) {
                        $rooms = [];
                    }
                    
                    // Preserve structure: ensure each room has proper mealTypes array and selectedMeals object
                    foreach ($rooms as &$room) {
                        if (!is_array($room) || !isset($room['beds']) || !is_array($room['beds'])) {
                            continue;
                        }
                        
                        foreach ($room['beds'] as &$bed) {
                            if (!is_array($bed)) {
                                continue;
                            }
                            
                            // Ensure mealTypes is an array
                            if (!isset($bed['mealTypes']) || !is_array($bed['mealTypes'])) {
                                // Try to get from selectedMeals if mealTypes is missing
                                if (isset($bed['selectedMeals']) && is_array($bed['selectedMeals'])) {
                                    $firstMeal = reset($bed['selectedMeals']);
                                    if ($firstMeal && isset($firstMeal['type'])) {
                                        $bed['mealTypes'] = [$firstMeal['type']];
                                    } else {
                                        $bed['mealTypes'] = [];
                                    }
                                } else {
                                    $bed['mealTypes'] = [];
                                }
                            }
                            
                            // Ensure selectedMeals is an object (associative array)
                            if (!isset($bed['selectedMeals']) || !is_array($bed['selectedMeals'])) {
                                // If missing, try to construct from mealTypes
                                if (!empty($bed['mealTypes']) && is_array($bed['mealTypes'])) {
                                    $mealType = $bed['mealTypes'][0] ?? '';
                                    if (!empty($validated['check_in_date']) && !empty($validated['check_out_date'])) {
                                        $checkIn = \Carbon\Carbon::parse($validated['check_in_date']);
                                        $checkOut = \Carbon\Carbon::parse($validated['check_out_date']);
                                        $numberOfNights = $checkIn->diffInDays($checkOut);
                                        
                                        $selectedMeals = [];
                                        for ($i = 1; $i <= $numberOfNights; $i++) {
                                            $selectedMeals["meal_{$i}"] = [
                                                'type' => $mealType,
                                                'price' => 0
                                            ];
                                        }
                                        $bed['selectedMeals'] = $selectedMeals;
                                    } else {
                                        $bed['selectedMeals'] = [];
                                    }
                                } else {
                                    $bed['selectedMeals'] = [];
                                }
                            } else {
                                // Ensure selectedMeals has proper structure (meal_1, meal_2, etc.)
                                $selectedMeals = [];
                                foreach ($bed['selectedMeals'] as $key => $meal) {
                                    if (is_array($meal) && isset($meal['type'])) {
                                        $selectedMeals[$key] = [
                                            'type' => $meal['type'],
                                            'price' => isset($meal['price']) ? (float)$meal['price'] : 0
                                        ];
                                    }
                                }
                                $bed['selectedMeals'] = $selectedMeals;
                            }
                        }
                        unset($bed); // Break reference
                    }
                    unset($room); // Break reference
                    
                    // Remove duplicate rooms based on room_id and bed_id combination
                    $uniqueRooms = [];
                    $seenRooms = [];
                    
                    foreach ($rooms as $room) {
                        if (!is_array($room)) {
                            continue;
                        }
                        
                        $roomId = $room['room_id'] ?? null;
                        $bedId = null;
                        
                        if (isset($room['beds']) && is_array($room['beds']) && count($room['beds']) > 0) {
                            $bedId = $room['beds'][0]['bed_id'] ?? null;
                        }
                        
                        $uniqueKey = $roomId . '_' . $bedId;
                        
                        if (!isset($seenRooms[$uniqueKey])) {
                            $uniqueRooms[] = $room;
                            $seenRooms[$uniqueKey] = true;
                        }
                    }
                    
                    $currentPayload['rooms'] = $uniqueRooms;
                    $roomsUpdated = true;
                    
                    Log::info('Rooms updated from rooms_json', [
                        'order_id' => $orderId,
                        'rooms_count' => count($uniqueRooms),
                        'sample_room' => !empty($uniqueRooms) ? $uniqueRooms[0] : null,
                    ]);
                }
            } elseif ($request->has('room_type') || $request->has('bed_type') || $request->has('meal_plan')) {
                // Construct rooms from individual form fields
                $roomType = $validated['room_type'] ?? '';
                $bedType = $validated['bed_type'] ?? '';
                $mealPlan = $validated['meal_plan'] ?? '';
                $numberOfRooms = $validated['number_of_rooms'] ?? 1;
                $numberOfPersons = $validated['number_of_persons'] ?? 1;
                $hotelId = $request->input('hotel_id', '');
                
                // Get existing room structure to preserve bed_id and room_id
                $existingRooms = $currentPayload['rooms'] ?? [];
                $existingRoom = null;
                $existingBed = null;
                
                if (!empty($existingRooms) && is_array($existingRooms) && isset($existingRooms[0])) {
                    $existingRoom = $existingRooms[0];
                    if (isset($existingRoom['beds']) && is_array($existingRoom['beds']) && isset($existingRoom['beds'][0])) {
                        $existingBed = $existingRoom['beds'][0];
                    }
                }
                
                // Build new room structure
                $newRooms = [];
                if (!empty($roomType) && !empty($bedType)) {
                    // Extract room_id and bed_id from existing data if available
                    $roomId = $existingRoom['room_id'] ?? null;
                    $bedId = $existingBed['bed_id'] ?? null;
                    
                    // Build meal types array from meal plan
                    // Ensure it's an array format like ["Room Only"] or ["room with breakfast"]
                    $mealTypes = [];
                    if (!empty($mealPlan)) {
                        $mealTypes[] = $mealPlan;
                    }
                    
                    // Build selected meals structure (simplified - one meal per night)
                    // Try to preserve prices from original rooms if available
                    $selectedMeals = [];
                    if (!empty($validated['check_in_date']) && !empty($validated['check_out_date'])) {
                        $checkIn = \Carbon\Carbon::parse($validated['check_in_date']);
                        $checkOut = \Carbon\Carbon::parse($validated['check_out_date']);
                        $numberOfNights = $checkIn->diffInDays($checkOut);
                        
                        // Get original selectedMeals prices if available
                        $originalSelectedMeals = [];
                        if (!empty($existingRooms) && is_array($existingRooms) && isset($existingRooms[0]['beds'][0]['selectedMeals'])) {
                            $originalSelectedMeals = $existingRooms[0]['beds'][0]['selectedMeals'];
                        }
                        
                        for ($i = 1; $i <= $numberOfNights; $i++) {
                            $mealKey = "meal_{$i}";
                            $mealPrice = 0;
                            
                            // Try to preserve price from original if meal type matches
                            if (isset($originalSelectedMeals[$mealKey]) && 
                                is_array($originalSelectedMeals[$mealKey]) &&
                                isset($originalSelectedMeals[$mealKey]['type']) &&
                                $originalSelectedMeals[$mealKey]['type'] === $mealPlan) {
                                $mealPrice = $originalSelectedMeals[$mealKey]['price'] ?? 0;
                            }
                            
                            $selectedMeals[$mealKey] = [
                                'type' => $mealPlan ?: 'Room Only',
                                'price' => (float)$mealPrice
                            ];
                        }
                    }
                    
                    $newRooms[] = [
                        'room_id' => $roomId ?? 0,
                        'room_type' => $roomType,
                        'beds' => [
                            [
                                'bed_id' => $bedId ?? 0,
                                'bed_type' => $bedType,
                                'max_occupancy' => $numberOfPersons,
                                'mealTypes' => $mealTypes,
                                'selectedMeals' => $selectedMeals,
                                'head_count' => $numberOfPersons,
                                'price' => 0, // Price would need to be calculated
                                'room_type' => $roomType
                            ]
                        ]
                    ];
                    
                    // If multiple rooms, duplicate the structure
                    if ($numberOfRooms > 1) {
                        $baseRoom = $newRooms[0];
                        $newRooms = [];
                        for ($i = 0; $i < $numberOfRooms; $i++) {
                            $newRooms[] = $baseRoom;
                        }
                    }
                    
                    $currentPayload['rooms'] = $newRooms;
                    $roomsUpdated = true;
                    
                    Log::info('Rooms constructed from individual fields', [
                        'order_id' => $orderId,
                        'rooms' => $newRooms,
                        'room_type' => $roomType,
                        'bed_type' => $bedType,
                        'meal_plan' => $mealPlan,
                    ]);
                } else {
                    Log::warning('Cannot construct rooms - missing room_type or bed_type', [
                        'order_id' => $orderId,
                        'room_type' => $roomType,
                        'bed_type' => $bedType,
                    ]);
                }
            }
            
            if (!$roomsUpdated) {
                Log::info('Rooms not updated - preserving existing', [
                    'order_id' => $orderId,
                    'existing_rooms_count' => is_array($currentPayload['rooms'] ?? []) ? count($currentPayload['rooms']) : 0,
                ]);
            }

            // Step 8: Update specialRequests (mapped from notes field) if provided
            if (array_key_exists('notes', $validated)) {
                $currentPayload['specialRequests'] = $validated['notes'] ?? '';
            }

            // Step 9: Update days_display if provided
            if (array_key_exists('days_display', $validated)) {
                $currentPayload['days_display'] = $validated['days_display'] ?? '';
            }

            // Step 10: Restructure payload to match desired order (same as SingleTourPackageController)
            // Order: customer info, specialRequests, rooms, booking info, hotelDetails, bookingDate
            // Remove unwanted fields: id, price, tour_id, days_display
            $restructuredPayload = [
                // Customer information fields first
                'fullName' => $currentPayload['fullName'] ?? '',
                'email' => $currentPayload['email'] ?? '',
                'phone' => $currentPayload['phone'] ?? '',
                'countryCode' => $currentPayload['countryCode'] ?? '',
                'address1' => $currentPayload['address1'] ?? '',
                'address2' => $currentPayload['address2'] ?? null,
                'state' => $currentPayload['state'] ?? '',
                'zip' => $currentPayload['zip'] ?? '',
                'specialRequests' => $currentPayload['specialRequests'] ?? '',
                // Rooms array - ensure it's always an array
                'rooms' => is_array($currentPayload['rooms'] ?? []) ? $currentPayload['rooms'] : [],
                // Booking type and pricing (order: totalPrice, bookingType, priceMode, priceModeId)
                'totalPrice' => isset($currentPayload['totalPrice']) ? (float)$currentPayload['totalPrice'] : 0,
                'bookingType' => $currentPayload['bookingType'] ?? 'booking',
                'priceMode' => $currentPayload['priceMode'] ?? 'dmc',
                'priceModeId' => isset($currentPayload['priceModeId']) ? (int)$currentPayload['priceModeId'] : 0,
                // Hotel details at the end - ensure it's always an array
                'hotelDetails' => is_array($currentPayload['hotelDetails'] ?? []) ? $currentPayload['hotelDetails'] : [],
                // Booking date at the end - ensure it's always an array
                'bookingDate' => is_array($currentPayload['bookingDate'] ?? []) ? $currentPayload['bookingDate'] : [],
            ];
            
            // Ensure hotelDetails has all required fields
            if (empty($restructuredPayload['hotelDetails'])) {
                $restructuredPayload['hotelDetails'] = [
                    'hotel_id' => '',
                    'hotel_name' => '',
                    'checkInTime' => null,
                    'checkOutTime' => null,
                    'location' => null,
                    'image' => '',
                    'cancellation_charge' => null,
                ];
            }

            // Log the data before saving for debugging
            Log::info('Saving hotel booking data', [
                'order_id' => $orderId,
                'restructured_payload' => $restructuredPayload,
                'has_existing_data' => $hasExistingData,
            ]);

            // Set the data - Laravel will automatically encode to JSON due to the cast
            $order->data = [$restructuredPayload];
            $successMessage = $hasExistingData ? 'Hotel booking data replaced successfully.' : 'Hotel service updated successfully.';

            // Save the order
            $saved = $order->save();

            if (!$saved) {
                Log::error('Failed to save order', [
                    'order_id' => $orderId,
                    'order_data' => $order->data,
                ]);
                throw new \Exception('Failed to save hotel service information.');
            }

            // Verify data was saved correctly
            $order->refresh();
            $savedData = $order->data;
            
            if (empty($savedData) || !is_array($savedData)) {
                Log::error('Data was not saved correctly', [
                    'order_id' => $orderId,
                    'saved_data' => $savedData,
                ]);
                throw new \Exception('Data was not saved correctly. Please try again.');
            }
            
            Log::info('Hotel booking data saved successfully', [
                'order_id' => $orderId,
                'data_count' => is_array($savedData) ? count($savedData) : 0,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $successMessage,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Failed to update hotel service', [
                'order_id' => $orderId,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to save hotel service right now.',
                'error' => config('app.debug') ? $exception->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update attraction service order.
     */
    public function updateAttraction(Request $request, $orderId)
    {
        $order = Order::where('booking_id', $orderId)->firstOrFail();
        
        $validated = $request->validate([
            'booking_data' => 'nullable|string', // Complete JSON data to replace
            'attraction_name' => 'nullable|string|max:255', // Optional for backward compatibility
            'ticket_name' => 'nullable|string|max:255',
            'visit_time' => 'nullable|string|max:255',
            'adult_count' => 'nullable|integer|min:0',
            'child_count' => 'nullable|integer|min:0',
            'senior_count' => 'nullable|integer|min:0',
            'total_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Check if complete booking data is provided
            if (!empty($validated['booking_data'])) {
                // Step 1: First clear the data column (use empty array instead of null to satisfy NOT NULL constraint)
                $order->data = [];
                $order->save();

                // Step 2: Now update with new JSON data
                $newBookingData = json_decode($validated['booking_data'], true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Booking data JSON is invalid. Please provide a valid JSON structure.');
                }

                // Validate that the JSON structure contains required fields
                if (!is_array($newBookingData)) {
                    throw new \Exception('Booking data must be a valid JSON array.');
                }

                // If it's not already wrapped in an array, wrap it
                if (!isset($newBookingData[0]) && (isset($newBookingData['AttractionName']) || isset($newBookingData['fullName']) || isset($newBookingData['adultCount']))) {
                    $newBookingData = [$newBookingData];
                }

                // Completely replace the data
                $order->data = $newBookingData;
                $successMessage = 'Attraction booking data replaced successfully.';
            } else {
                // Backward compatibility: Legacy field-by-field update mode
                $existingData = is_array($order->data) ? $order->data : json_decode($order->data, true);
                $currentPayload = [];

                if (empty($existingData)) {
                    $currentPayload = [];
                } elseif (isset($existingData[0])) {
                    $currentPayload = $existingData[0];
                } else {
                    $currentPayload = $existingData;
                }

                if (!empty($validated['attraction_name'])) {
                    $currentPayload['AttractionName'] = $validated['attraction_name'];
                }
                if (array_key_exists('ticket_name', $validated)) {
                    $currentPayload['ticketName'] = $validated['ticket_name'];
                }
                if (array_key_exists('visit_time', $validated)) {
                    $currentPayload['visitTime'] = $validated['visit_time'];
                }

                if (!empty($validated['adult_count'])) {
                    $currentPayload['adultCount'] = (int) $validated['adult_count'];
                }
                if (!empty($validated['child_count'])) {
                    $currentPayload['childCount'] = (int) $validated['child_count'];
                }
                if (!empty($validated['senior_count'])) {
                    $currentPayload['seniorCount'] = (int) $validated['senior_count'];
                }
                if (!empty($validated['total_price'])) {
                    $currentPayload['totalPrice'] = (float) $validated['total_price'];
                }

                if (!empty($validated['notes'])) {
                    $currentPayload['notes'] = $validated['notes'];
                }

                $order->data = [$currentPayload];
                $successMessage = 'Attraction booking updated successfully.';
            }

            $saved = $order->save();

            if (!$saved) {
                throw new \Exception('Failed to save attraction service information.');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $successMessage,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Failed to update attraction service', [
                'order_id' => $orderId,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to save attraction service right now.',
                'error' => config('app.debug') ? $exception->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update guide service order.
     */
    public function updateGuide(Request $request, $orderId)
    {
        $order = Order::where('booking_id', $orderId)->firstOrFail();
        
        $validated = $request->validate([
            'booking_data' => 'nullable|string', // Complete JSON data to replace
            'guide_name' => 'nullable|string|max:255', // Optional for backward compatibility
            'package_hours' => 'nullable|string|max:255',
            'pickup_time' => 'nullable|string|max:255',
            'guest_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Check if complete booking data is provided
            if (!empty($validated['booking_data'])) {
                // Step 1: First clear the data column (use empty array instead of null to satisfy NOT NULL constraint)
                $order->data = [];
                $order->save();

                // Step 2: Now update with new JSON data
                $newBookingData = json_decode($validated['booking_data'], true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Booking data JSON is invalid. Please provide a valid JSON structure.');
                }

                // Validate that the JSON structure contains required fields
                if (!is_array($newBookingData)) {
                    throw new \Exception('Booking data must be a valid JSON array.');
                }

                // If it's not already wrapped in an array, wrap it
                if (!isset($newBookingData[0]) && (isset($newBookingData['guide_name']) || isset($newBookingData['fullName']) || isset($newBookingData['hours']))) {
                    $newBookingData = [$newBookingData];
                }

                // Completely replace the data
                $order->data = $newBookingData;
                $successMessage = 'Guide booking data replaced successfully.';
            } else {
                // Backward compatibility: Legacy field-by-field update mode
                $existingData = is_array($order->data) ? $order->data : json_decode($order->data, true);
                $currentPayload = [];

                if (empty($existingData)) {
                    $currentPayload = [];
                } elseif (isset($existingData[0])) {
                    $currentPayload = $existingData[0];
                } else {
                    $currentPayload = $existingData;
                }

                if (!empty($validated['guide_name'])) {
                    $currentPayload['guide_name'] = $validated['guide_name'];
                }

                if (array_key_exists('package_hours', $validated)) {
                    $currentPayload['hours'] = $validated['package_hours'];
                }
                if (array_key_exists('pickup_time', $validated)) {
                    $currentPayload['entrytime'] = $validated['pickup_time'];
                }
                if (array_key_exists('guest_name', $validated)) {
                    $currentPayload['fullName'] = $validated['guest_name'];
                }
                if (!empty($validated['notes'])) {
                    $currentPayload['notes'] = $validated['notes'];
                }

                $order->data = [$currentPayload];
                $successMessage = 'Guide booking updated successfully.';
            }

            $saved = $order->save();

            if (!$saved) {
                throw new \Exception('Failed to save guide service information.');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $successMessage,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Failed to update guide service', [
                'order_id' => $orderId,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to save guide service right now.',
                'error' => config('app.debug') ? $exception->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update restaurant service order.
     */
    public function updateRestaurant(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        
        $validated = $request->validate([
            'booking_data' => 'nullable|string', // Complete JSON data to replace
            'restaurant_name' => 'nullable|string|max:255', // Optional for backward compatibility
            'meal_type' => 'nullable|string|max:255',
            'meal_specific_type' => 'nullable|string|max:255',
            'time_slot' => 'nullable|string|max:255',
            'adult_count' => 'nullable|integer|min:0',
            'child_count' => 'nullable|integer|min:0',
            'total_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'meal_description_json' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Check if complete booking data is provided
            if (!empty($validated['booking_data'])) {
                // Step 1: First clear the data column (use empty array instead of null to satisfy NOT NULL constraint)
                $order->data = [];
                $order->save();

                // Step 2: Now update with new JSON data
                $newBookingData = json_decode($validated['booking_data'], true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Booking data JSON is invalid. Please provide a valid JSON structure.');
                }

                // Validate that the JSON structure contains required fields
                if (!is_array($newBookingData)) {
                    throw new \Exception('Booking data must be a valid JSON array.');
                }

                // If it's not already wrapped in an array, wrap it
                if (!isset($newBookingData[0]) && (isset($newBookingData['restaurantName']) || isset($newBookingData['fullName']) || isset($newBookingData['mealType']))) {
                    $newBookingData = [$newBookingData];
                }

                // Completely replace the data
                $order->data = $newBookingData;
                $successMessage = 'Restaurant booking data replaced successfully.';
            } else {
                // Backward compatibility: Legacy field-by-field update mode
                $existingData = is_array($order->data) ? $order->data : json_decode($order->data, true);
                $currentPayload = [];

                if (empty($existingData)) {
                    $currentPayload = [];
                } elseif (isset($existingData[0])) {
                    $currentPayload = $existingData[0];
                } else {
                    $currentPayload = $existingData;
                }

                if (!empty($validated['restaurant_name'])) {
                    $currentPayload['restaurantName'] = $validated['restaurant_name'];
                }
                $currentPayload['mealType'] = $validated['meal_type'] ?? ($currentPayload['mealType'] ?? null);
                $currentPayload['mealSpecificType'] = $validated['meal_specific_type'] ?? ($currentPayload['mealSpecificType'] ?? null);
                
                if (array_key_exists('time_slot', $validated)) {
                    $currentPayload['visitTime'] = $validated['time_slot'];
                }
                
                if (!empty($validated['adult_count'])) {
                    $currentPayload['adultCount'] = (int) $validated['adult_count'];
                }
                if (!empty($validated['child_count'])) {
                    $currentPayload['childCount'] = (int) $validated['child_count'];
                }

                if (array_key_exists('total_price', $validated) && $validated['total_price'] !== null) {
                    $currentPayload['totalPrice'] = (float) $validated['total_price'];
                }

                if (!empty($validated['notes'])) {
                    $currentPayload['notes'] = $validated['notes'];
                }

                if (!empty($validated['meal_description_json'])) {
                    $decodedMeals = json_decode($validated['meal_description_json'], true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \Exception('Meal details JSON is invalid.');
                    }
                    $currentPayload['MealDescription'] = $decodedMeals;
                }

                $order->data = [$currentPayload];
                $successMessage = 'Restaurant booking updated successfully.';
            }

            $saved = $order->save();

            if (!$saved) {
                throw new \Exception('Failed to save restaurant service information.');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $successMessage,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Failed to update restaurant service', [
                'order_id' => $orderId,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to save restaurant service right now.',
                'error' => config('app.debug') ? $exception->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update transport service order (handles entry_port, exit_port, travel_hourly, travel_point, local_transport).
     */
    public function updateTransport(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        
        // First check if it's complete JSON replacement mode
        if (!empty($request->booking_data)) {
            $validated = $request->validate([
                'booking_data' => 'nullable|string', // Complete JSON data to replace
            ]);

            try {
                DB::beginTransaction();

                // Step 1: First clear the data column (use empty array instead of null to satisfy NOT NULL constraint)
                $order->data = [];
                $order->save();

                // Step 2: Now update with new JSON data
                $newBookingData = json_decode($validated['booking_data'], true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Booking data JSON is invalid. Please provide a valid JSON structure.');
                }

                // Validate that the JSON structure contains required fields
                if (!is_array($newBookingData)) {
                    throw new \Exception('Booking data must be a valid JSON array.');
                }

                // If it's not already wrapped in an array, wrap it
                if (!isset($newBookingData[0]) && (isset($newBookingData['entrypickup']) || isset($newBookingData['fullName']) || isset($newBookingData['vehicles_name']))) {
                    $newBookingData = [$newBookingData];
                }

                // Completely replace the data
                $order->data = $newBookingData;
                $saved = $order->save();

                if (!$saved) {
                    throw new \Exception('Failed to save transport service information.');
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Transport booking data replaced successfully.',
                ]);

            } catch (\Illuminate\Validation\ValidationException $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422);
            } catch (\Throwable $exception) {
                DB::rollBack();
                Log::error('Failed to update transport service', [
                    'order_id' => $orderId,
                    'error' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Unable to save transport service right now.',
                    'error' => config('app.debug') ? $exception->getMessage() : null,
                ], 500);
            }
        }

        // Legacy mode: field-by-field update
        $request->validate([
            'type' => 'required|string|in:entry_port,exit_port,travel_hourly,travel_point,local_transport',
        ]);

        $type = $request->type;

        try {
            DB::beginTransaction();

            if (in_array($type, ['entry_port', 'exit_port'])) {
                $validated = $request->validate([
                    'city' => 'nullable|string|max:255',
                    'pickup_location' => 'required|string|max:255',
                    'dropoff_location' => 'required|string|max:255',
                    'pickup_time' => 'required|string|max:50',
                    'vehicle_name' => 'nullable|string|max:255',
                    'vehicle_type' => 'nullable|string|max:50',
                    'passenger_count' => 'nullable|integer|min:1',
                    'notes' => 'nullable|string|max:1000',
                ]);

                $existingData = is_array($order->data) ? $order->data : json_decode($order->data, true);
                $currentPayload = [];

                if (empty($existingData)) {
                    $currentPayload = [];
                } elseif (isset($existingData[0])) {
                    $currentPayload = $existingData[0];
                } else {
                    $currentPayload = $existingData;
                }

                $currentPayload['city'] = $validated['city'] ?? ($currentPayload['city'] ?? null);

                if ($type === 'entry_port') {
                    $currentPayload['entrypickup'] = $validated['pickup_location'];
                    $currentPayload['entrydropoff'] = $validated['dropoff_location'];
                    $currentPayload['entrytime'] = $validated['pickup_time'];
                } else {
                    $currentPayload['exitpickup'] = $validated['pickup_location'];
                    $currentPayload['exitdropoff'] = $validated['dropoff_location'];
                    $currentPayload['exitpickupdate'] = $validated['pickup_time'];
                }

                if (!empty($validated['vehicle_name'])) {
                    $currentPayload['vehicles_name'] = $validated['vehicle_name'];
                }

                if (!empty($validated['vehicle_type'])) {
                    $currentPayload['type'] = $validated['vehicle_type'];
                }

                if (!empty($validated['passenger_count'])) {
                    $currentPayload['passengers'] = (int)$validated['passenger_count'];
                }

                if (!empty($validated['notes'])) {
                    $currentPayload['notes'] = $validated['notes'];
                }

                $successMessage = 'Transport service updated successfully.';
            }

            if (in_array($type, ['travel_hourly', 'travel_point', 'local_transport'])) {
                $rules = [
                    'pickup_location' => 'required|string|max:255',
                    'pickup_time' => 'required|string|max:50',
                    'pickup_date' => 'nullable|date',
                    'vehicle_name' => 'nullable|string|max:255',
                    'vehicle_type' => 'nullable|string|max:50',
                    'total_price' => 'nullable|numeric|min:0',
                    'adult_count' => 'nullable|integer|min:0',
                    'child_count' => 'nullable|integer|min:0',
                    'notes' => 'nullable|string|max:1000',
                ];

                if ($type === 'travel_hourly') {
                    $rules['dropoff_location'] = 'nullable|string|max:255';
                    $rules['selected_hours'] = 'nullable|integer|min:1';
                } else {
                    $rules['dropoff_location'] = 'required|string|max:255';
                }

                if ($type === 'travel_point') {
                    $rules['distance'] = 'nullable|numeric|min:0';
                }

                $validated = $request->validate($rules);

                $existingData = is_array($order->data) ? $order->data : json_decode($order->data, true);
                if (empty($existingData)) {
                    $currentPayload = [];
                } elseif (isset($existingData[0])) {
                    $currentPayload = $existingData[0];
                } else {
                    $currentPayload = $existingData;
                }

                $currentPayload['entrypickup'] = $validated['pickup_location'];

                if (array_key_exists('dropoff_location', $validated)) {
                    $currentPayload['entrydropoff'] = $validated['dropoff_location'];
                }

                if (!empty($validated['pickup_date'])) {
                    $currentPayload['pickupdate'] = $validated['pickup_date'];
                    $currentPayload['bookingDate'] = $validated['pickup_date'];
                }

                $currentPayload['entrytime'] = $validated['pickup_time'];

                if (array_key_exists('vehicle_name', $validated)) {
                    $currentPayload['vehicles_name'] = $validated['vehicle_name'];
                }

                if (array_key_exists('vehicle_type', $validated)) {
                    $currentPayload['type'] = $validated['vehicle_type'];
                }

                if (array_key_exists('selected_hours', $validated)) {
                    $currentPayload['selectedHours'] = $validated['selected_hours'];
                }

                if (array_key_exists('total_price', $validated) && $validated['total_price'] !== null) {
                    $currentPayload['totalPrice'] = (float) $validated['total_price'];
                }

                if (array_key_exists('adult_count', $validated) && $validated['adult_count'] !== null) {
                    $currentPayload['adults'] = (int) $validated['adult_count'];
                    $currentPayload['adultCount'] = (int) $validated['adult_count'];
                }

                if (array_key_exists('child_count', $validated) && $validated['child_count'] !== null) {
                    $currentPayload['children'] = (int) $validated['child_count'];
                    $currentPayload['childCount'] = (int) $validated['child_count'];
                }

                if (array_key_exists('distance', $validated) && $validated['distance'] !== null) {
                    $currentPayload['distance'] = (float) $validated['distance'];
                }

                if (array_key_exists('notes', $validated)) {
                    $currentPayload['notes'] = $validated['notes'];
                }

                $currentPayload['travel_type'] = $type;

                $messages = [
                    'travel_hourly' => 'Hourly transport updated successfully.',
                    'travel_point' => 'Point-to-point transport updated successfully.',
                    'local_transport' => 'Local transfer updated successfully.',
                ];

                $successMessage = $messages[$type] ?? 'Transport service updated successfully.';
            }

            $order->data = [$currentPayload];
            $saved = $order->save();

            if (!$saved) {
                throw new \Exception('Failed to save transport service information.');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $successMessage,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Failed to update transport service', [
                'order_id' => $orderId,
                'type' => $type,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to save transport service right now.',
                'error' => config('app.debug') ? $exception->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get services that are outside the new tour date range
     */
    private function getServicesOutsideDateRange($tourId, Carbon $newStartDate, Carbon $newEndDate)
    {
        $affectedServices = [];
        
        // Get all orders for this tour (SoftDeletes automatically excludes soft-deleted records)
        $orders = Order::where('tour_id', $tourId)->get();
        
        foreach ($orders as $order) {
            $serviceData = $order->data;
            if (empty($serviceData) || !is_array($serviceData)) {
                continue;
            }
            
            // Handle array of service data
            $serviceArray = isset($serviceData[0]) ? $serviceData : [$serviceData];
            
            foreach ($serviceArray as $index => $service) {
                if (!is_array($service)) {
                    continue;
                }
                
                $rawServiceDates = $this->extractServiceDates($service, $order->type);
                if (empty($rawServiceDates)) {
                    continue;
                }
                
                // Check if any service date is outside the new tour date range
                $isOutsideRange = false;
                $validDates = [];
                
                foreach ($rawServiceDates as $dateStr) {
                    try {
                        // Parse date and normalize to date only (ignore time)
                        $serviceDate = Carbon::parse($dateStr)->startOfDay();
                        $dateStrFormatted = $serviceDate->format('Y-m-d');
                        $validDates[] = $dateStrFormatted;
                        
                        // Check if date is completely outside the range
                        // Date is outside if it's before start date or after end date
                        $newStart = $newStartDate->copy()->startOfDay();
                        $newEnd = $newEndDate->copy()->startOfDay();
                        
                        if ($serviceDate->lt($newStart) || $serviceDate->gt($newEnd)) {
                            $isOutsideRange = true;
                        }
                    } catch (\Exception $e) {
                        // Skip invalid dates
                        Log::warning('Invalid service date', [
                            'date' => $dateStr,
                            'service_type' => $order->type,
                            'error' => $e->getMessage(),
                        ]);
                        continue;
                    }
                }
                
                if ($isOutsideRange && !empty($validDates)) {
                    $serviceName = $this->getServiceName($order->type, $service);
                    $affectedServices[] = [
                        'order_id' => $order->booking_id,
                        'type' => $order->type,
                        'name' => $serviceName,
                        'dates' => array_unique($validDates),
                        'index' => $index,
                    ];
                }
            }
        }
        
        return $affectedServices;
    }

    /**
     * Extract dates from service data based on service type
     */
    private function extractServiceDates(array $service, string $serviceType)
    {
        $dates = [];
        
        switch ($serviceType) {
            case 'hotel':
                // Hotels have bookingDate array [check_in, check_out]
                if (isset($service['bookingDate']) && is_array($service['bookingDate'])) {
                    $dates = array_merge($dates, $service['bookingDate']);
                }
                break;
                
            case 'attraction':
                // Attractions have visitTime
                if (isset($service['visitTime'])) {
                    $dates[] = $service['visitTime'];
                }
                break;
                
            case 'guide':
                // Guides have pickupdate and entrytime
                if (isset($service['pickupdate'])) {
                    $dates[] = $service['pickupdate'];
                }
                if (isset($service['entrytime'])) {
                    // entrytime might be datetime, extract date
                    try {
                        $parsed = Carbon::parse($service['entrytime']);
                        $dates[] = $parsed->format('Y-m-d');
                    } catch (\Exception $e) {
                        $dates[] = $service['entrytime'];
                    }
                }
                if (isset($service['bookingDate'])) {
                    $dates[] = $service['bookingDate'];
                }
                break;
                
            case 'restaurant':
                // Restaurants have visitTime
                if (isset($service['visitTime'])) {
                    $dates[] = $service['visitTime'];
                }
                break;
                
            case 'entry_port':
            case 'exit_port':
            case 'travel_hourly':
            case 'travel_point':
            case 'local_transport':
                // Transport services have pickupdate and bookingDate
                if (isset($service['pickupdate'])) {
                    $dates[] = $service['pickupdate'];
                }
                if (isset($service['bookingDate'])) {
                    $dates[] = $service['bookingDate'];
                }
                break;
        }
        
        // Filter out empty dates and normalize format
        $dates = array_filter(array_map(function($date) {
            if (empty($date)) {
                return null;
            }
            try {
                // Try to parse and return in Y-m-d format
                $parsed = Carbon::parse($date);
                return $parsed->format('Y-m-d');
            } catch (\Exception $e) {
                return $date;
            }
        }, $dates));
        
        return array_unique(array_values($dates));
    }

    /**
     * Get service name from service data
     */
    private function getServiceName(string $serviceType, array $service)
    {
        switch ($serviceType) {
            case 'hotel':
                return $service['hotelDetails']['hotel_name'] ?? 'Hotel Booking';
            case 'attraction':
                return $service['AttractionName'] ?? 'Attraction';
            case 'guide':
                return $service['guide_name'] ?? 'Guide Service';
            case 'restaurant':
                return $service['restaurantName'] ?? 'Restaurant';
            case 'entry_port':
                return 'Entry Port Transfer';
            case 'exit_port':
                return 'Exit Port Transfer';
            case 'travel_hourly':
                return 'Hourly Transport';
            case 'travel_point':
                return 'Point-to-Point Transport';
            case 'local_transport':
                return 'Local Transport';
            default:
                return ucfirst($serviceType) . ' Service';
        }
    }

    /**
     * Soft delete services that are outside the date range
     * Uses SoftDeletes trait - sets deleted_at timestamp instead of hard deleting
     */
    private function deleteServicesOutsideDateRange(array $affectedServices)
    {
        // Group services by order_id to handle multiple services in the same order
        $servicesByOrder = [];
        foreach ($affectedServices as $service) {
            $orderId = $service['order_id'];
            if (!isset($servicesByOrder[$orderId])) {
                $servicesByOrder[$orderId] = [];
            }
            $servicesByOrder[$orderId][] = $service;
        }
        
        // Process each order
        foreach ($servicesByOrder as $orderId => $services) {
            try {
                $order = Order::where('booking_id', $orderId)->first();
                if (!$order) {
                    continue;
                }
                
                $serviceData = $order->data;
                if (empty($serviceData) || !is_array($serviceData)) {
                    continue;
                }
                
                // Check if it's an array with multiple services
                if (isset($serviceData[0])) {
                    // Multiple services in array - collect indexes to delete
                    $indexesToDelete = array_map(function($service) {
                        return $service['index'];
                    }, $services);
                    
                    // Sort indexes in descending order to avoid index shifting issues
                    rsort($indexesToDelete);
                    
                    // Remove services starting from highest index
                    foreach ($indexesToDelete as $index) {
                        if (isset($serviceData[$index])) {
                            unset($serviceData[$index]);
                        }
                    }
                    
                    // Re-index array
                    $serviceData = array_values($serviceData);
                    
                    // If no services left, soft delete the order (sets deleted_at timestamp), otherwise update it
                    if (empty($serviceData)) {
                        $order->delete(); // Soft delete - sets deleted_at timestamp automatically via SoftDeletes trait
                        Log::info('Soft deleted entire order - all services outside date range', [
                            'order_id' => $orderId,
                            'deleted_at' => $order->deleted_at ? $order->deleted_at->toDateTimeString() : 'N/A',
                        ]);
                    } else {
                        $order->data = $serviceData;
                        $order->save();
                        Log::info('Updated order - removed services outside date range', [
                            'order_id' => $orderId,
                            'remaining_services' => count($serviceData),
                            'deleted_count' => count($indexesToDelete),
                        ]);
                    }
                } else {
                    // Single service, soft delete the entire order (sets deleted_at timestamp)
                    $order->delete(); // Soft delete - sets deleted_at timestamp automatically via SoftDeletes trait
                    Log::info('Soft deleted order - single service outside date range', [
                        'order_id' => $orderId,
                        'service_type' => $services[0]['type'],
                        'service_name' => $services[0]['name'],
                        'deleted_at' => $order->deleted_at ? $order->deleted_at->toDateTimeString() : 'N/A',
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to delete service outside date range', [
                    'order_id' => $orderId ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
