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
        ]);

        try {
            DB::beginTransaction();

            $checkIn = Carbon::createFromFormat('Y-m-d', $validated['start_date']);
            $checkOut = Carbon::createFromFormat('Y-m-d', $validated['end_date']);

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
        ]);

        try {
            DB::beginTransaction();

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
            if (!array_key_exists('totalPrice', $currentPayload)) {
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
            
            // Update hotelDetails fields only if provided in request, otherwise preserve existing
            if (!empty($validated['hotel_name'])) {
                $currentPayload['hotelDetails']['hotel_name'] = $validated['hotel_name'];
            }
            
            if (isset($validated['hotel_location'])) {
                $currentPayload['hotelDetails']['location'] = $validated['hotel_location'] ?? null;
            }
            
            if (!empty($validated['check_in_time'])) {
                $currentPayload['hotelDetails']['checkInTime'] = $validated['check_in_time'];
            }
            
            if (!empty($validated['check_out_time'])) {
                $currentPayload['hotelDetails']['checkOutTime'] = $validated['check_out_time'];
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

            // Step 7: Update rooms only if provided, otherwise preserve existing structure
            if (!empty($validated['rooms_json'])) {
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
                    
                    $currentPayload['rooms'] = !empty($uniqueRooms) ? $uniqueRooms : $rooms;
                }
            }

            // Step 8: Update specialRequests (mapped from notes field) only if provided
            if (!empty($validated['notes'])) {
                $currentPayload['specialRequests'] = $validated['notes'];
            }

            // Step 9: Update days_display only if provided, otherwise preserve existing
            if (!empty($validated['days_display'])) {
                $currentPayload['days_display'] = $validated['days_display'];
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
}
