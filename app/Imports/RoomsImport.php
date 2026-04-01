<?php

namespace App\Imports;

use App\Models\Room;
use App\Models\Hotel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RoomsImport
{
    protected $errors = [];
    protected $successCount = 0;
    protected $errorCount = 0;
    protected $authUser;

    public function __construct()
    {
        $this->authUser = Auth::user();
    }

    /**
     * @param  string  $filePath
     * @param  int|null  $roomCreatedByDmcUserId  Parent DMC userId for created_by (delegated uploads)
     */
    public function import($filePath, ?int $roomCreatedByDmcUserId = null)
    {
        $dmcOwnerUserId = $roomCreatedByDmcUserId ?? (int) $this->authUser->userId;

        $file = fopen($filePath, 'r');
        
        if (!$file) {
            $this->errors[] = "Unable to open file.";
            $this->errorCount++;
            return $this->getResults();
        }

        $header = fgetcsv($file); // Read header row
        
        if (!$header) {
            $this->errors[] = "CSV file is empty or invalid.";
            $this->errorCount++;
            fclose($file);
            return $this->getResults();
        }
        
        // Convert header to lowercase for case-insensitive matching
        $header = array_map('strtolower', $header);
        $header = array_map('trim', $header);
        
        $rowNumber = 2; // Start from 2 (1 is header)
        
        while (($data = fgetcsv($file)) !== false) {
            // Skip empty rows
            if (empty(array_filter($data))) {
                $rowNumber++;
                continue;
            }

            // Combine header with data to create associative array
            $row = array_combine($header, $data);
            
            if (!$row) {
                $this->errors[] = "Row {$rowNumber}: Invalid data format.";
                $this->errorCount++;
                $rowNumber++;
                continue;
            }

            try {
                // Validate required fields
                $validator = Validator::make($row, $this->rules(), $this->validationMessages());

                if ($validator->fails()) {
                    foreach ($validator->errors()->all() as $error) {
                        $this->errors[] = "Row {$rowNumber}: " . $error;
                    }
                    $this->errorCount++;
                    $rowNumber++;
                    continue;
                }

                // Get hotel_id from request (passed from controller)
                $hotelId = request()->get('hotel_id');
                $roomType = trim($row['room_type'] ?? '');
                $dimensionInCsv = trim($row['dimension'] ?? '');
                $hotelNameInCsv = trim($row['hotel_name'] ?? '');

                // Find the original room (admin base room) by hotel_id and room_type
                $room = Room::where('hotel_id', $hotelId)
                          ->where('room_type', $roomType)
                          ->where('created_by', 1) // Only admin base rooms
                          ->first();

                if (!$room) {
                    $this->errors[] = "Row {$rowNumber}: Admin base room with type '{$roomType}' not found for this hotel.";
                    $this->errorCount++;
                    $rowNumber++;
                    continue;
                }

                // Get hotel information for validation
                $hotel = Hotel::where('hotel_unique_id', $hotelId)->first();
                $expectedHotelName = $hotel ? $hotel->name : '';

                // VALIDATION: Check if DMC user is trying to change protected fields
                if (!in_array($this->authUser->role_id, [1, 20])) {
                    // For DMC users - validate they haven't changed protected fields
                    
                    // Validate hotel_name
                    if ($hotelNameInCsv !== $expectedHotelName) {
                        $this->errors[] = "Row {$rowNumber}: Hotel Name cannot be changed. Expected: '{$expectedHotelName}', Found: '{$hotelNameInCsv}'";
                        $this->errorCount++;
                        $rowNumber++;
                        continue;
                    }
                    
                    // Validate room_type
                    if ($roomType !== $room->room_type) {
                        $this->errors[] = "Row {$rowNumber}: Room Type cannot be changed. Expected: '{$room->room_type}', Found: '{$roomType}'";
                        $this->errorCount++;
                        $rowNumber++;
                        continue;
                    }

                    // Validate dimension
                    if ($dimensionInCsv !== (string)$room->dimension) {
                        $this->errors[] = "Row {$rowNumber}: Room Dimension cannot be changed. Expected: '{$room->dimension}', Found: '{$dimensionInCsv}'";
                        $this->errorCount++;
                        $rowNumber++;
                        continue;
                    }
                    
                    // Note: no_of_room CAN be changed by DMC users
                }

                // Validate meal options
                $mealValidation = $this->validateMealOptions($row, $rowNumber);
                if ($mealValidation !== true) {
                    $this->errors[] = $mealValidation;
                    $this->errorCount++;
                    $rowNumber++;
                    continue;
                }

                // For Admin: Update the original room
                if (in_array($this->authUser->role_id, [1, 20])) {
                    // Admin updates their own room
                    $room->no_of_room = $row['no_of_room'] ?? $room->no_of_room;
                    $room->weekday_price = $row['weekday_price'];
                    $room->weekend_price = $row['weekend_price'];
                    $room->double_weekday_price = $row['double_weekday_price'];
                    $room->double_weekend_price = $row['double_weekend_price'];

                    // Update meal options
                    $room->breakfast = isset($row['breakfast']) && $row['breakfast'] == 1 ? 1 : 0;
                    $room->breakfast_type = $room->breakfast ? ($row['breakfast_type'] ?? null) : null;
                    $room->breakfast_price = $room->breakfast ? ($row['breakfast_price'] ?? null) : null;

                    $room->lunch = isset($row['lunch']) && $row['lunch'] == 1 ? 1 : 0;
                    $room->lunch_type = $room->lunch ? ($row['lunch_type'] ?? null) : null;
                    $room->lunch_price = $room->lunch ? ($row['lunch_price'] ?? null) : null;

                    $room->dinner = isset($row['dinner']) && $row['dinner'] == 1 ? 1 : 0;
                    $room->dinner_type = $room->dinner ? ($row['dinner_type'] ?? null) : null;
                    $room->dinner_price = $room->dinner ? ($row['dinner_price'] ?? null) : null;

                    $room->breakfast_included = isset($row['breakfast_included']) && $row['breakfast_included'] == 1 ? 1 : 0;

                    $room->save();
                } else {
                    // For DMC: Create NEW row (don't update admin room)
                    // Check if DMC already has their own room for this hotel/room_type
                    $existingDmcRoom = Room::where('hotel_id', $hotelId)
                                          ->where('room_type', $room->room_type)
                                          ->where('created_by', $dmcOwnerUserId)
                                          ->where('dmc_base_room', 0)
                                          ->first();

                    if ($existingDmcRoom) {
                        // DMC already has their own room, update it
                        $existingDmcRoom->no_of_room = $row['no_of_room'] ?? $existingDmcRoom->no_of_room;
                        $existingDmcRoom->weekday_price = $row['weekday_price'];
                        $existingDmcRoom->weekend_price = $row['weekend_price'];
                        $existingDmcRoom->double_weekday_price = $row['double_weekday_price'];
                        $existingDmcRoom->double_weekend_price = $row['double_weekend_price'];

                        $existingDmcRoom->breakfast = isset($row['breakfast']) && $row['breakfast'] == 1 ? 1 : 0;
                        $existingDmcRoom->breakfast_type = $existingDmcRoom->breakfast ? ($row['breakfast_type'] ?? null) : null;
                        $existingDmcRoom->breakfast_price = $existingDmcRoom->breakfast ? ($row['breakfast_price'] ?? null) : null;

                        $existingDmcRoom->lunch = isset($row['lunch']) && $row['lunch'] == 1 ? 1 : 0;
                        $existingDmcRoom->lunch_type = $existingDmcRoom->lunch ? ($row['lunch_type'] ?? null) : null;
                        $existingDmcRoom->lunch_price = $existingDmcRoom->lunch ? ($row['lunch_price'] ?? null) : null;

                        $existingDmcRoom->dinner = isset($row['dinner']) && $row['dinner'] == 1 ? 1 : 0;
                        $existingDmcRoom->dinner_type = $existingDmcRoom->dinner ? ($row['dinner_type'] ?? null) : null;
                        $existingDmcRoom->dinner_price = $existingDmcRoom->dinner ? ($row['dinner_price'] ?? null) : null;

                        $existingDmcRoom->breakfast_included = isset($row['breakfast_included']) && $row['breakfast_included'] == 1 ? 1 : 0;

                        $existingDmcRoom->save();
                    } else {
                        // DMC doesn't have their own room yet - CREATE NEW ROW
                        $this->createDmcRoomCopy($room, $row, $dmcOwnerUserId);
                    }
                }

                $this->successCount++;

            } catch (\Exception $e) {
                $this->errors[] = "Row {$rowNumber}: " . $e->getMessage();
                $this->errorCount++;
                Log::error("Room import error at row {$rowNumber}: " . $e->getMessage());
            }
            
            $rowNumber++;
        }
        
        fclose($file);
        
        return $this->getResults();
    }

    private function validateMealOptions($row, $rowNumber)
    {
        // Breakfast validation
        if (isset($row['breakfast']) && $row['breakfast'] == 1) {
            if (empty($row['breakfast_type'])) {
                return "Row {$rowNumber}: Breakfast type is required when breakfast is included.";
            }
            if (!in_array($row['breakfast_type'], ['Buffet', 'Set Menu'])) {
                return "Row {$rowNumber}: Breakfast type must be 'Buffet' or 'Set Menu'.";
            }
            if (empty($row['breakfast_price']) || !is_numeric($row['breakfast_price']) || $row['breakfast_price'] < 0) {
                return "Row {$rowNumber}: Valid breakfast price is required when breakfast is included.";
            }
        }

        // Lunch validation
        if (isset($row['lunch']) && $row['lunch'] == 1) {
            if (empty($row['lunch_type'])) {
                return "Row {$rowNumber}: Lunch type is required when lunch is included.";
            }
            if (!in_array($row['lunch_type'], ['Buffet', 'Set Menu'])) {
                return "Row {$rowNumber}: Lunch type must be 'Buffet' or 'Set Menu'.";
            }
            if (empty($row['lunch_price']) || !is_numeric($row['lunch_price']) || $row['lunch_price'] < 0) {
                return "Row {$rowNumber}: Valid lunch price is required when lunch is included.";
            }
        }

        // Dinner validation
        if (isset($row['dinner']) && $row['dinner'] == 1) {
            if (empty($row['dinner_type'])) {
                return "Row {$rowNumber}: Dinner type is required when dinner is included.";
            }
            if (!in_array($row['dinner_type'], ['Buffet', 'Set Menu'])) {
                return "Row {$rowNumber}: Dinner type must be 'Buffet' or 'Set Menu'.";
            }
            if (empty($row['dinner_price']) || !is_numeric($row['dinner_price']) || $row['dinner_price'] < 0) {
                return "Row {$rowNumber}: Valid dinner price is required when dinner is included.";
            }
        }

        return true;
    }


    public function rules(): array
    {
        return [
            'hotel_name' => 'required|string',
            'room_type' => 'required|string',
            'no_of_room' => 'required|numeric|min:1',
            'dimension' => 'required',
            'weekday_price' => 'required|numeric|min:0',
            'weekend_price' => 'required|numeric|min:0',
            'double_weekday_price' => 'required|numeric|min:0',
            'double_weekend_price' => 'required|numeric|min:0',
            'breakfast' => 'nullable|in:0,1',
            'lunch' => 'nullable|in:0,1',
            'dinner' => 'nullable|in:0,1',
            'breakfast_included' => 'nullable|in:0,1',
        ];
    }

    public function validationMessages()
    {
        return [
            'hotel_name.required' => 'Hotel Name is required.',
            'hotel_name.string' => 'Hotel Name must be a string.',
            'room_type.required' => 'Room Type is required.',
            'room_type.string' => 'Room Type must be a string.',
            'no_of_room.required' => 'Number of Rooms is required.',
            'dimension.required' => 'Room Dimension is required.',
            'weekday_price.required' => 'Weekday price is required.',
            'weekday_price.numeric' => 'Weekday price must be a number.',
            'weekday_price.min' => 'Weekday price must be at least 0.',
            'weekend_price.required' => 'Weekend price is required.',
            'weekend_price.numeric' => 'Weekend price must be a number.',
            'weekend_price.min' => 'Weekend price must be at least 0.',
            'double_weekday_price.required' => 'Double weekday price is required.',
            'double_weekday_price.numeric' => 'Double weekday price must be a number.',
            'double_weekday_price.min' => 'Double weekday price must be at least 0.',
            'double_weekend_price.required' => 'Double weekend price is required.',
            'double_weekend_price.numeric' => 'Double weekend price must be a number.',
            'double_weekend_price.min' => 'Double weekend price must be at least 0.',
        ];
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    private function getResults(): array
    {
        return [
            'success' => $this->successCount,
            'errors' => $this->errorCount,
            'error_messages' => $this->errors,
        ];
    }

    /**
     * Create a new DMC room copy from admin base room
     */
    private function createDmcRoomCopy($originalRoom, $row, int $dmcOwnerUserId)
    {
        // Generate new unique room_id
        $newRoomId = $this->generateNewRoomId();

        // Create new room record (copy of admin room with DMC's custom data)
        $newRoom = new Room();
        
        // Copy structure from original room
        $newRoom->room_id = $newRoomId;
        $newRoom->hotel_id = $originalRoom->hotel_id;
        $newRoom->room_type = $originalRoom->room_type;
        $newRoom->no_of_room = $row['no_of_room'] ?? $originalRoom->no_of_room; // DMC can set their own no_of_room
        $newRoom->dimension = $originalRoom->dimension;
        $newRoom->children_price = $originalRoom->children_price;
        
        // Set DMC's custom prices from CSV
        $newRoom->weekday_price = $row['weekday_price'];
        $newRoom->weekend_price = $row['weekend_price'];
        $newRoom->double_weekday_price = $row['double_weekday_price'];
        $newRoom->double_weekend_price = $row['double_weekend_price'];

        // Set DMC's meal options from CSV
        $newRoom->breakfast = isset($row['breakfast']) && $row['breakfast'] == 1 ? 1 : 0;
        $newRoom->breakfast_type = $newRoom->breakfast ? ($row['breakfast_type'] ?? null) : null;
        $newRoom->breakfast_price = $newRoom->breakfast ? ($row['breakfast_price'] ?? null) : null;

        $newRoom->lunch = isset($row['lunch']) && $row['lunch'] == 1 ? 1 : 0;
        $newRoom->lunch_type = $newRoom->lunch ? ($row['lunch_type'] ?? null) : null;
        $newRoom->lunch_price = $newRoom->lunch ? ($row['lunch_price'] ?? null) : null;

        $newRoom->dinner = isset($row['dinner']) && $row['dinner'] == 1 ? 1 : 0;
        $newRoom->dinner_type = $newRoom->dinner ? ($row['dinner_type'] ?? null) : null;
        $newRoom->dinner_price = $newRoom->dinner ? ($row['dinner_price'] ?? null) : null;

        $newRoom->breakfast_included = isset($row['breakfast_included']) && $row['breakfast_included'] == 1 ? 1 : 0;

        // Set ownership to parent DMC (same when upload is done by product head / PM / assistant)
        $newRoom->created_by = $dmcOwnerUserId;
        $newRoom->dmc_id = $dmcOwnerUserId;
        $newRoom->dmc_base_room = 0; // This is DMC's custom room, not base room
        
        // Copy other fields
        $newRoom->base_room = 0; // DMC rooms are not base rooms
        $newRoom->rooms_only = $originalRoom->rooms_only ?? 0;
        $newRoom->master_image = $originalRoom->master_image;
        $newRoom->images = $originalRoom->images;
        $newRoom->status = $originalRoom->status;
        
        $newRoom->save();
        
        Log::info("Created new DMC room copy: {$newRoomId} for DMC user: {$dmcOwnerUserId}");
    }

    /**
     * Generate new unique room_id using CommonHelper
     */
    private function generateNewRoomId()
    {
        // Get the last room ID (including soft deleted) - same logic as HotelController
        $lastRoom = Room::withTrashed()->orderBy('id', 'desc')->first();
        $room_max_id = $lastRoom->room_id ?? 0;
        
        // Use CommonHelper to create ID (returns an integer)
        $roomId = \App\Helpers\CommonHelper::createId($room_max_id);
        
        // Ensure uniqueness (in case of concurrent requests)
        while (Room::where('room_id', $roomId)->exists()) {
            $roomId = \App\Helpers\CommonHelper::createId($roomId);
        }
        
        return $roomId; // Returns integer, not string
    }
}
