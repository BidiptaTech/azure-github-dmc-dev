<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use App\Models\Hotel;
use App\Models\Driver;
use App\Models\Guide;
use App\Models\Restaurant;
use App\Models\Meal;
use App\Models\Vehicle;
use App\Models\Attraction;
use App\Models\Ticket;
use App\Models\Country;
use App\Models\City;
use App\Models\UploadHistory;
use Carbon\Carbon;

// PhpSpreadsheet classes are referenced via fully-qualified names within methods to avoid IDE autoload issues.

class BulkUploadController extends Controller
{
    // Allowed option lists for validation
    private array $allowedAccommodationTypes = ['Hotel', 'Motel', 'Resort', 'Bed & Breakfast (BB)', 'Hostels', 'Service Appartments/ Aparthotels', 'Guest House', 'Vacation Rentals', 'Boutique Hotels', 'Lodges', 'Homestays', 'Camping & Glamping', 'Host Home / Couchsurfing', 'Farm Stays / Argo-tourism'];
    private array $allowedOwnerships = ['Chain Hotels', 'Independent Hotels', 'Franchise Hotels'];
    private array $allowedSegments = ['Budget / Economy Hotels', 'Mid-Range Hotels', 'Luxury Hotels', 'Boutique Hotels', 'Resort Hotels', 'Business Hotels', 'Airport Hotels', 'Extended Stay Hotels', 'Family Hotels', 'Romantic / Gateway Hotels', 'Adventure Hotels', 'Wellness / Spa Hotels', 'Eco-Friendly / Sustainable Hotels', 'Extend Stay/ Serviced Appartments', 'Conference & Convention Hotels', 'Casino Hotels', 'Cultural / Heritage Hotels', 'Religious or Piligrimage Hotels', 'Medical or Wellness Tourism Hotels'];
    private array $allowedStarRatings = ['1-Star', '2-Star', '3-Star', '4-Star', '5-Star', '7-Star'];
    private array $allowedPortTypes = ['Airport', 'Seaport', 'Land Border Crossing', 'Railway', 'Bus Stand'];
    private array $allowedYesNo = ['No', 'Yes'];
    private array $allowedMealType = ['Buffet', 'Set Menu'];
    private array $allowedMealChildPrice = ['Free', 'Half Price', 'Full Price'];
    private array $allowedBaseRoom = ['0', '1'];
    private array $allowedExtraBedType = ['Sofa Bed', 'Wall Bed', 'Futon', 'Rollaway Bed', 'Bunk Bed'];
    private array $allowedEventType = ['Fair Date', 'Blackout Date'];

    private function validateOption(string $value, array $allowed, string $fieldName): void
    {
        if ($value === '') {
            return; // Empty means not set; handled elsewhere if required
        }
        if (!in_array($value, $allowed, true)) {
            throw new \Exception("Invalid value '{$value}' for {$fieldName}. Allowed: " . implode(', ', $allowed));
        }
    }

    public function hotels()
    {
        return view('bulk-upload.hotels');
    }

    public function drivers()
    {
        $auth_user = Auth::user();
        
        // Define role groups for driver bulk upload access
        $dmcFullAccessRoles = [11, 35]; // DMC, Product Head (DMC)
        $dmcDriverRoles = [81, 124]; // Product Manager Driver (DMC), Assistant PM Driver (DMC)
        $travclicksFullAccessRoles = [1, 23, 20, 29]; // Travclicks, Product Head (Travclicks), Virtual DMC, Assistant Manager(PROD HEAD)
        $travclicksDriverRoles = [51, 125]; // Product Manager Driver (Travclicks), Assistant PM Driver (Travclicks)
        
        // Check if user has access to driver bulk upload
        $hasAccess = in_array($auth_user->role_id, array_merge($dmcFullAccessRoles, $dmcDriverRoles, $travclicksFullAccessRoles, $travclicksDriverRoles));
        
        if (!$hasAccess) {
            abort(403, 'You do not have permission to access driver bulk upload.');
        }
        
        return view('bulk-upload.drivers');
    }

    public function guides()
    {
        $auth_user = Auth::user();
        
        // Define role groups for guide bulk upload access
        $dmcFullAccessRoles = [11, 35]; // DMC, Product Head (DMC)
        $dmcGuideRoles = [79, 121]; // Product Manager Guide (DMC), Assistant PM Guide (DMC)
        $travclicksFullAccessRoles = [1, 23, 20, 29]; // Travclicks, Product Head (Travclicks), Virtual DMC, Assistant Manager(PROD HEAD)
        $travclicksGuideRoles = [49, 119]; // Product Manager Guide (Travclicks), Assistant PM Guide (Travclicks)
        
        // Check if user has access to guide bulk upload
        $hasAccess = in_array($auth_user->role_id, array_merge($dmcFullAccessRoles, $dmcGuideRoles, $travclicksFullAccessRoles, $travclicksGuideRoles));
        
        if (!$hasAccess) {
            abort(403, 'You do not have permission to access guide bulk upload.');
        }
        
        return view('bulk-upload.guides');
    }

    public function restaurants()
    {
        $auth_user = Auth::user();
        
        // Only Virtual DMC (role_id = 20) can access restaurant bulk upload
        if ($auth_user->role_id != 20) {
            abort(403, 'Only Virtual DMC users can access restaurant bulk upload.');
        }
        
        // Get upload history (simple approach to avoid UploadHistory model issues)
        $uploadHistory = collect();
        
        return view('bulk-upload.restaurants', compact('uploadHistory'));
    }

    public function vehicles()
    {
        return view('bulk-upload.vehicles');
    }

    public function attractions()
    {
        $auth_user = Auth::user();
        
        // Define role groups for attraction bulk upload access
        $dmcFullAccessRoles = [11, 35]; // DMC, Product Head (DMC)
        $dmcAttractionRoles = [80, 122]; // Product Manager Attraction (DMC), Assistant PM Attraction (DMC)
        $travclicksFullAccessRoles = [1, 23, 20, 29]; // Travclicks, Product Head (Travclicks), Virtual DMC, Assistant Manager(PROD HEAD)
        $travclicksAttractionRoles = [50, 123]; // Product Manager Attraction (Travclicks), Assistant PM Attraction (Travclicks)
        
        // Check if user has access to attraction bulk upload
        $hasAccess = in_array($auth_user->role_id, array_merge($dmcFullAccessRoles, $dmcAttractionRoles, $travclicksFullAccessRoles, $travclicksAttractionRoles));
        
        if (!$hasAccess) {
            abort(403, 'You do not have permission to access attraction bulk upload.');
        }
        
        // Get upload history
        $uploadHistory = $this->getUploadHistory('attractions');
        
        return view('bulk-upload.attractions', compact('uploadHistory'));
    }

    public function tickets()
    {
        $auth_user = Auth::user();
        
        // Check for DMC role - role_id is stored as string "11"
        if (!$auth_user || $auth_user->role_id !== '11') {
            abort(403, 'Access denied. Only DMC users can access ticket bulk upload.');
        }
        
        // Get attractions that belong to this DMC - same approach as meals
        try {
            // First, let's see what columns exist
            $testAttraction = Attraction::first();
            if ($testAttraction) {
                Log::info('Attraction columns available: ' . json_encode(array_keys($testAttraction->getAttributes())));
            }
            
            // Try to get attractions for this DMC
            $attractions = Attraction::withCount('tickets')
                                   ->where('dmc_id', $auth_user->userId)
                                   ->where('status', 1)
                                   ->get();
            
            // If no attractions found, try getting all attractions for testing
            if ($attractions->isEmpty()) {
                Log::info('No attractions found for DMC user: ' . $auth_user->userId);
                $attractions = Attraction::withCount('tickets')
                                       ->where('status', 1)
                                       ->take(3)
                                       ->get(); // Get first 3 for testing
            }
            
        } catch (\Exception $e) {
            Log::error('Error in tickets method: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Database error: ' . $e->getMessage());
        }
        
        // Get upload history for tickets
        $uploadHistory = $this->getUploadHistory('tickets');
        
        return view('bulk-upload.tickets', compact('attractions', 'uploadHistory'));
    }

    // Hotel Template Download (CSV format)
    public function downloadHotelTemplate()
    {
        $data = $this->generateHotelCsvData();
        $content = $this->generateCsvContent($data);
        $filename = 'hotel_bulk_upload_template.csv';

        return Response::make($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }





    /**
     * Generate CSV template data for hotel bulk upload
     */
    private function generateHotelCsvData()
    {
        $header = [
            'hotel_name','accomodation_type','ownership','chain_hotel_name','general_phone_no','segment','star_rating','general_email','address','country','city','provision','postal_code','lat','lng','weekend','min_age_of_extra_bed','check_in_time','check_out_time','hotel_master_image','hotel_additional_image',
            'port_type','port_name','port_lat','port_lng','port_distance_km',
            'room_category','room_rate_variant','total_no_of_rooms','single_price','double_price','single_weekend_price','double_weekend_price','breakfast','breakfast_type','breakfast_price','lunch','lunch_type','lunch_price','dinner','dinner_type','dinner_price','meal_child_price','room_master_img','room_add_img','base_room',
            'bed_type_name','single_bed_count','king_bed_count','queen_bed_count','twin_bed_count','bunk_bed_count',
            'map_room_category','map_bed_type','no_of_rooms','extra_bed','extra_bed_type','extra_bed_price','max_adult_allowed','babycot','babycot_price',
            'season_name','season_single_price','season_double_price','season_single_weekend_price','season_double_weekend_price','season_start_date','season_end_date',
            'event_name','event_type','price_surcharge','event_start_date','event_end_date'
        ];

        $data = [$header];

        // Add section marker for Basic Info
        $sectionRow = array_fill(0, count($header), '');
        $sectionRow[0] = 'SECTION';
        $sectionRow[1] = 'BASIC_INFO';
        $data[] = $sectionRow;

        // Sample basic info
        $basicInfo = array_fill(0, count($header), '');
        $basicInfo[0] = 'Hotel Boss';
        $basicInfo[1] = 'Hotel';
        $basicInfo[2] = 'Independent Hotels';
        $basicInfo[3] = '';
        $basicInfo[4] = '9999999999';
        $basicInfo[5] = 'Luxury Hotels';
        $basicInfo[6] = '3-Star';
        $basicInfo[7] = 'hotel@boss.com';
        $basicInfo[8] = '123 Beach Road';
        $basicInfo[9] = 'Singapore';
        $basicInfo[10] = 'Singapore';
        $basicInfo[11] = '';
        $basicInfo[12] = '546664';
        $basicInfo[13] = '1.255654';
        $basicInfo[14] = '103.2545';
        $basicInfo[15] = 'Sat,Sun';
        $basicInfo[16] = '12';
        $basicInfo[17] = '15:00';
        $basicInfo[18] = '11:00';
        $basicInfo[19] = 'https://cdn.example.com/master.jpg';
        $basicInfo[20] = 'https://cdn.example.com/additional.jpg';
        $data[] = $basicInfo;

        // Add empty row
        $data[] = array_fill(0, count($header), '');

        // Add section marker for Port Info
        $portSectionRow = array_fill(0, count($header), '');
        $portSectionRow[0] = 'SECTION';
        $portSectionRow[1] = 'PORT_INFO';
        $data[] = $portSectionRow;

        // Sample ports
        $ports = [
            ['Seaport', 'Sea Port 1', '1.02224', '103.5145', '1.5'],
            ['Airport', 'Changi Airport', '1.3644', '103.9915', '18']
        ];
        foreach ($ports as $port) {
            $portRow = array_fill(0, count($header), '');
            $portRow[21] = $port[0]; // port_type
            $portRow[22] = $port[1]; // port_name
            $portRow[23] = $port[2]; // port_lat
            $portRow[24] = $port[3]; // port_lng
            $portRow[25] = $port[4]; // port_distance_km
            $data[] = $portRow;
        }

        // Add empty row
        $data[] = array_fill(0, count($header), '');

        // Add section marker for Room Categories
        $roomSectionRow = array_fill(0, count($header), '');
        $roomSectionRow[0] = 'SECTION';
        $roomSectionRow[1] = 'ROOM_CATEGORIES';
        $data[] = $roomSectionRow;

        // Sample room categories
        $roomCategories = [
            ['Standard', '0', '10', '150', '200', '175', '250', 'Yes', 'Buffet', '50', 'Yes', 'Set Menu', '150', 'Yes', 'Set Menu', '120', 'Half Price', 'https://example.com/standard.jpg', '', '1'],
            ['Deluxe', '0', '20', '200', '260', '225', '300', 'Yes', 'Buffet', '60', 'Yes', 'Set Menu', '180', 'Yes', 'Set Menu', '150', 'Full Price', 'https://example.com/deluxe.jpg', '', '1']
        ];
        foreach ($roomCategories as $room) {
            $roomRow = array_fill(0, count($header), '');
            for ($i = 0; $i < count($room); $i++) {
                $roomRow[26 + $i] = $room[$i]; // Starting from room_category column
            }
            $data[] = $roomRow;
        }

        // Add empty row
        $data[] = array_fill(0, count($header), '');

        // Add section marker for Bed Types
        $bedSectionRow = array_fill(0, count($header), '');
        $bedSectionRow[0] = 'SECTION';
        $bedSectionRow[1] = 'BED_TYPES';
        $data[] = $bedSectionRow;

        // Sample bed types
        $bedTypes = [
            ['King Bed', '0', '1', '0', '0', '0'],
            ['Twin Bed', '2', '0', '0', '2', '0']
        ];
        foreach ($bedTypes as $bed) {
            $bedRow = array_fill(0, count($header), '');
            for ($i = 0; $i < count($bed); $i++) {
                $bedRow[46 + $i] = $bed[$i]; // Starting from bed_type_name column
            }
            $data[] = $bedRow;
        }

        // Add empty row
        $data[] = array_fill(0, count($header), '');

        // Add section marker for Room Mappings
        $mappingSectionRow = array_fill(0, count($header), '');
        $mappingSectionRow[0] = 'SECTION';
        $mappingSectionRow[1] = 'ROOM_MAPPINGS';
        $data[] = $mappingSectionRow;

        // Sample room mappings
        $roomMappings = [
            ['Standard', 'King Bed', '5', 'Yes', 'Rollaway Bed', '50', '2', 'Yes', '25'],
            ['Deluxe', 'Twin Bed', '10', 'Yes', 'Sofa Bed', '75', '3', 'Yes', '30']
        ];
        foreach ($roomMappings as $mapping) {
            $mappingRow = array_fill(0, count($header), '');
            for ($i = 0; $i < count($mapping); $i++) {
                $mappingRow[52 + $i] = $mapping[$i]; // Starting from map_room_category column
            }
            $data[] = $mappingRow;
        }

        // Add empty row
        $data[] = array_fill(0, count($header), '');

        // Add section marker for Seasons
        $seasonSectionRow = array_fill(0, count($header), '');
        $seasonSectionRow[0] = 'SECTION';
        $seasonSectionRow[1] = 'SEASONS';
        $data[] = $seasonSectionRow;

        // Sample seasons
        $seasons = [
            ['Peak Season', '180', '240', '210', '280', '2024-12-20', '2025-01-05'],
            ['Low Season', '130', '180', '150', '210', '2024-06-01', '2024-08-31']
        ];
        foreach ($seasons as $season) {
            $seasonRow = array_fill(0, count($header), '');
            for ($i = 0; $i < count($season); $i++) {
                $seasonRow[61 + $i] = $season[$i]; // Starting from season_name column
            }
            $data[] = $seasonRow;
        }

        // Add empty row
        $data[] = array_fill(0, count($header), '');

        // Add section marker for Events
        $eventSectionRow = array_fill(0, count($header), '');
        $eventSectionRow[0] = 'SECTION';
        $eventSectionRow[1] = 'EVENTS';
        $data[] = $eventSectionRow;

        // Sample events
        $events = [
            ['New Year', 'Fair Date', '50', '2024-12-31', '2025-01-01'],
            ['Renovation', 'Blackout Date', '0', '2024-09-15', '2024-09-30']
        ];
        foreach ($events as $event) {
            $eventRow = array_fill(0, count($header), '');
            for ($i = 0; $i < count($event); $i++) {
                $eventRow[68 + $i] = $event[$i]; // Starting from event_name column
            }
            $data[] = $eventRow;
        }

        // Add empty row and end marker
        $data[] = array_fill(0, count($header), '');
        $endRow = array_fill(0, count($header), '');
        $endRow[0] = 'END_HOTEL';
        $data[] = $endRow;

        return $data;
    }



    // Hotel Upload Processing (Updated for complex structure)
    public function uploadHotels(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('file');
            $csvData = $this->readCsvFile($file->getPathname());
            
            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            DB::beginTransaction();

            // Process each hotel block in the CSV
            $hotelBlocks = $this->parseHotelBlocks($csvData);
            
            foreach ($hotelBlocks as $blockIndex => $hotelData) {
                try {
                    $hotelId = $this->processHotelBlock($hotelData, $blockIndex + 1);
                    if ($hotelId) {
                        $successCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Hotel Block " . ($blockIndex + 1) . ": " . $e->getMessage();
                    $errorCount++;
                    Log::error("Hotel bulk upload error for block " . ($blockIndex + 1) . ": " . $e->getMessage());
                }
            }

            DB::commit();

            $message = "Upload completed! {$successCount} hotels uploaded successfully.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} hotels had errors.";
            }

            return redirect()->back()->with([
                'success' => $message,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Hotel bulk upload failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    // Parse hotel blocks from CSV data
    private function parseHotelBlocks($csvData)
    {
        $hotelBlocks = [];
        $currentBlock = [];
        $currentSection = null;
        
        foreach ($csvData as $row) {
            // Skip empty rows and header explanations
            if (empty(array_filter($row)) || strpos($row[0] ?? '', 'HOTEL BULK UPLOAD') !== false) {
                continue;
            }
            
            // Check for section markers
            if (($row[0] ?? '') === 'SECTION') {
                $currentSection = $row[1] ?? '';
                $currentBlock[$currentSection] = [];
                continue;
            }
            
            // Check for end of hotel block
            if (($row[0] ?? '') === 'END_HOTEL') {
                if (!empty($currentBlock)) {
                    $hotelBlocks[] = $currentBlock;
                    $currentBlock = [];
                }
                continue;
            }
            
            // Add data to current section
            if ($currentSection && !empty(array_filter($row))) {
                $currentBlock[$currentSection][] = $row;
            }
        }
        
        // Add the last block if it exists
        if (!empty($currentBlock)) {
            $hotelBlocks[] = $currentBlock;
        }
        
        return $hotelBlocks;
    }

    // Process a single hotel block
    private function processHotelBlock($hotelData, $blockNumber)
    {
        // Validate required sections
        if (!isset($hotelData['BASIC_INFO']) || empty($hotelData['BASIC_INFO'])) {
            throw new \Exception("Missing basic hotel information");
        }
        
        // Process basic hotel info
        $basicInfo = $hotelData['BASIC_INFO'];
        $headers = array_shift($basicInfo); // Remove header row
        $hotelRow = $basicInfo[0] ?? [];
        
        if (count($hotelRow) < 13) {
            throw new \Exception("Incomplete basic hotel information");
        }
        
        // Map the basic hotel data
        $hotelName = trim($hotelRow[0]);
        $address = trim($hotelRow[8]);
        $countryName = trim($hotelRow[9]);
        $cityName = trim($hotelRow[10]);
        
        if (empty($hotelName) || empty($address) || empty($countryName) || empty($cityName)) {
            throw new \Exception("Missing required fields: hotel name, address, country, or city");
        }
        
        // Validate dropdown fields
        $this->validateOption(trim($hotelRow[1]), $this->allowedAccommodationTypes, 'accomodation_type');
        $this->validateOption(trim($hotelRow[2]), $this->allowedOwnerships, 'ownership');
        $this->validateOption(trim($hotelRow[5]), $this->allowedSegments, 'segment');
        $this->validateOption(trim($hotelRow[6]), $this->allowedStarRatings, 'star_rating');
        
        // Find or validate country and city
        $country = Country::where('country_name', $countryName)->first();
        if (!$country) {
            throw new \Exception("Country '{$countryName}' not found in database");
        }
        
        $city = City::where('city_name', $cityName)
                    ->where('country_id', $country->id)
                    ->first();
        if (!$city) {
            throw new \Exception("City '{$cityName}' not found in country '{$countryName}'");
        }
        
        // Create the hotel record
        $hotel = Hotel::create([
            'hotel_name' => $hotelName,
            'accommodation_type' => trim($hotelRow[1]) ?: 'Hotel',
            'ownership' => trim($hotelRow[2]) ?: '',
            'chain_hotel_name' => trim($hotelRow[3]) ?: '',
            'phone' => trim($hotelRow[4]) ?: '',
            'segment' => trim($hotelRow[5]) ?: '',
            'star_rating' => is_numeric($hotelRow[6]) ? (int)$hotelRow[6] : null,
            'email' => trim($hotelRow[7]) ?: '',
            'address' => $address,
            'country_id' => $country->id,
            'city_id' => $city->id,
            'postal_code' => trim($hotelRow[12]) ?: '',
            'status' => 1,
                         'created_by' => Auth::id(),
             'dmc_id' => Auth::user()->dmc_id ?? 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Process additional sections if they exist
        $this->processPortInfo($hotel->id, $hotelData['PORT_INFO'] ?? []);
        $this->processRoomCategories($hotel->id, $hotelData['ROOM_CATEGORIES'] ?? []);
        $this->processBedTypes($hotel->id, $hotelData['BED_TYPES'] ?? []);
        $this->processRoomAmenities($hotel->id, $hotelData['ROOM_AMENITIES'] ?? []);
        $this->processAdditionalServices($hotel->id, $hotelData['ADDITIONAL_SERVICES'] ?? []);
        $this->processSeasonalPricing($hotel->id, $hotelData['SEASONAL_PRICING'] ?? []);
        $this->processEvents($hotel->id, $hotelData['EVENTS'] ?? []);
        
        return $hotel->id;
    }

    // Helper methods for processing different sections
    private function processPortInfo($hotelId, $portData)
    {
        if (empty($portData)) return;
        
        $headers = array_shift($portData);
        foreach ($portData as $row) {
            if (count($row) >= 5) {
                // You would insert into your ports/locations table here
                // This is a placeholder for the actual implementation
                Log::info("Processing port data for hotel {$hotelId}: " . json_encode($row));
            }
        }
    }

    private function processRoomCategories($hotelId, $roomData)
    {
        if (empty($roomData)) return;
        
        $headers = array_shift($roomData);
        foreach ($roomData as $row) {
            if (count($row) >= 7) {
                // You would insert into your room categories table here
                Log::info("Processing room category for hotel {$hotelId}: " . json_encode($row));
            }
        }
    }

    private function processBedTypes($hotelId, $bedData)
    {
        if (empty($bedData)) return;
        
        $headers = array_shift($bedData);
        foreach ($bedData as $row) {
            if (count($row) >= 6) {
                // You would insert into your bed types table here
                Log::info("Processing bed type for hotel {$hotelId}: " . json_encode($row));
            }
        }
    }

    private function processRoomAmenities($hotelId, $amenityData)
    {
        if (empty($amenityData)) return;
        
        $headers = array_shift($amenityData);
        foreach ($amenityData as $row) {
            if (count($row) >= 6) {
                // You would insert into your room amenities table here
                Log::info("Processing room amenity for hotel {$hotelId}: " . json_encode($row));
            }
        }
    }

    private function processAdditionalServices($hotelId, $serviceData)
    {
        if (empty($serviceData)) return;
        
        $headers = array_shift($serviceData);
        foreach ($serviceData as $row) {
            if (count($row) >= 6) {
                // You would insert into your additional services table here
                Log::info("Processing additional service for hotel {$hotelId}: " . json_encode($row));
            }
        }
    }

    private function processSeasonalPricing($hotelId, $seasonData)
    {
        if (empty($seasonData)) return;
        
        $headers = array_shift($seasonData);
        foreach ($seasonData as $row) {
            if (count($row) >= 7) {
                // You would insert into your seasonal pricing table here
                Log::info("Processing seasonal pricing for hotel {$hotelId}: " . json_encode($row));
            }
        }
    }

    private function processEvents($hotelId, $eventData)
    {
        if (empty($eventData)) return;
        
        $headers = array_shift($eventData);
        foreach ($eventData as $row) {
            if (count($row) >= 5) {
                // You would insert into your events table here
                Log::info("Processing event for hotel {$hotelId}: " . json_encode($row));
            }
        }
    }

    // Driver Template Download
    public function downloadDriverTemplate()
    {
        $auth_user = Auth::user();

        // Define role groups
        $dmcFullAccessRoles = [11, 35]; // DMC, Product Head (DMC)
        $dmcDriverRoles = [81, 124]; // Product Manager Driver (DMC), Assistant PM Driver (DMC)
        $travclicksFullAccessRoles = [1, 23, 20, 29]; // Travclicks, Product Head (Travclicks), Virtual DMC, Assistant Manager(PROD HEAD)
        $travclicksDriverRoles = [51, 125]; // Product Manager Driver (Travclicks), Assistant PM Driver (Travclicks)

        // Check if user is DMC or Travclicks
        $isDmcUser = in_array($auth_user->role_id, array_merge($dmcFullAccessRoles, $dmcDriverRoles));
        $isTravclicksUser = in_array($auth_user->role_id, array_merge($travclicksFullAccessRoles, $travclicksDriverRoles));

        if ($isDmcUser || ($auth_user->role_id == 20)) { // DMC users or Virtual DMC
            return $this->downloadDmcDriverTemplate($auth_user);
        } elseif ($isTravclicksUser) { // Travclicks users
            return $this->downloadTravclicksDriverTemplate($auth_user);
        } else {
            abort(403, 'You do not have permission to download driver templates.');
        }
    }

    private function downloadDmcDriverTemplate($auth_user)
    {
        $headers = [
            'Salutation*',
            'Driver Gender*',
            'Name*',
            'Email*',
            'Phone No*',
            'Address*',
            'Country*',
            'City*',
            'License No*',
            'License Expiry Date*',
            'Driver Age*',
            'Profile Image*',
            'Status*'
        ];

        $data = [$headers];

        // Get drivers based on user role - DMC users only see their own drivers
        $drivers = Driver::where('dmc_id', $auth_user->userId)
                          ->where('is_active', 1)
                          ->get();

        if ($drivers->count() > 0) {
            foreach ($drivers as $driver) {
                $row = [
                    $driver->salutation ?? '',
                    $driver->driver_gender ?? '',
                    $driver->name ?? '',
                    $driver->email ?? '',
                    $driver->phone ?? '',
                    $driver->address ?? '',
                    $driver->country ?? '',
                    $driver->city ?? '',
                    $driver->license_no ?? '',
                    $driver->license_exp_date ?? '',
                    $driver->driver_age ?? '',
                    $driver->image ?? '',
                    $driver->is_active ? '1' : '0'
                ];
                
                $data[] = $row;
            }
        } else {
            // No existing drivers, add sample data for DMC format
        $sampleData = [
                'Mr',
                'Male',
            'John Driver',
            'john@example.com',
            '+1-555-123-4567',
                '123 Main Street, Apt 4B',
                'United States',
            'New York',
                'DL123456789',
                '2025-12-31',
                '35',
                'driver_profile.jpg',
                '1'
            ];

            $data[] = $sampleData;
        }

        $content = $this->generateCsvContent($data);
        $filename = 'dmc_driver_bulk_upload_template.csv';

        return Response::make($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function downloadTravclicksDriverTemplate($auth_user)
    {
        $headers = [
            'Salutation*',
            'Driver Gender*',
            'Name*',
            'Email*',
            'Phone No*',
            'Address*',
            'Country*',
            'City*',
            'License No*',
            'License Expiry Date*',
            'Driver Age*',
            'Profile Image*',
            'Status*'
        ];

        $data = [$headers];

        // Get all drivers - Travclicks users can see all data
        $drivers = Driver::where('is_active', 1)->get();

        if ($drivers->count() > 0) {
            foreach ($drivers as $driver) {
                $row = [
                    $driver->salutation ?? '',
                    $driver->driver_gender ?? '',
                    $driver->name ?? '',
                    $driver->email ?? '',
                    $driver->phone ?? '',
                    $driver->address ?? '',
                    $driver->country ?? '',
                    $driver->city ?? '',
                    $driver->license_no ?? '',
                    $driver->license_exp_date ?? '',
                    $driver->driver_age ?? '',
                    $driver->image ?? '',
                    $driver->is_active ? '1' : '0'
                ];
                
                $data[] = $row;
            }
        } else {
            // No existing drivers, add sample data for Travclicks format
            $sampleData1 = [
                'Mr',
                'Male',
                'John Driver',
                'john@example.com',
                '+1-555-123-4567',
                '123 Main Street, Apt 4B',
            'United States',
                'New York',
                'DL123456789',
                '2025-12-31',
                '35',
                'driver_profile.jpg',
            '1'
        ];

            $sampleData2 = [
                'Ms',
                'Female',
                'Jane Smith',
                'jane@example.com',
                '+65-9999-8888',
                '456 Ocean Drive',
                'Singapore',
                'Singapore',
                'DL987654321',
                '2026-06-30',
                '28',
                'jane_profile.jpg',
                '1'
            ];

            $data[] = $sampleData1;
            $data[] = $sampleData2;
        }

        $content = $this->generateCsvContent($data);
        $filename = 'travclicks_driver_bulk_upload_template.csv';

        return Response::make($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // Guide Template Download
    public function downloadGuideTemplate()
    {
        $auth_user = Auth::user();

        // Define role groups
        $dmcFullAccessRoles = [11, 35]; // DMC, Product Head (DMC)
        $dmcGuideRoles = [79, 121]; // Product Manager Guide (DMC), Assistant PM Guide (DMC)
        $travclicksFullAccessRoles = [1, 23, 20, 29]; // Travclicks, Product Head (Travclicks), Virtual DMC, Assistant Manager(PROD HEAD)
        $travclicksGuideRoles = [49, 119]; // Product Manager Guide (Travclicks), Assistant PM Guide (Travclicks)

        // Check if user is DMC or Travclicks
        $isDmcUser = in_array($auth_user->role_id, array_merge($dmcFullAccessRoles, $dmcGuideRoles));
        $isTravclicksUser = in_array($auth_user->role_id, array_merge($travclicksFullAccessRoles, $travclicksGuideRoles));

        if ($isDmcUser || ($auth_user->role_id == 20)) { // DMC users or Virtual DMC
            return $this->downloadDmcGuideTemplate($auth_user);
        } elseif ($isTravclicksUser) { // Travclicks users
            return $this->downloadTravclicksGuideTemplate($auth_user);
        } else {
            abort(403, 'You do not have permission to download guide templates.');
        }
    }

    private function downloadDmcGuideTemplate($auth_user)
    {
        $headers = [
            'Salutation',
            'Gender*',
            'Guide Name*',
            'Email*',
            'Contact No*',
            'Service Type*',
            'Age*',
            'Master Image*',
            'License Number*',
            'License Image*',
            'License Expiry Date*',
            'City*',
            'Country*',
            'Experience Years*',
            'Languages*',
            'Proficiency*',
            'Minimum Base Price*',
            'Night Surcharge*',
            'Night Start Time*',
            'Night End Time*',
            'Hourly Price*',
            'Two Hour Price*',
            'Four Hour Price*',
            'Six Hour Price*',
            'Eight Hour Price*',
            'Ten Hour Price*',
            'Twelve Hour Price*',
            'Adout*',
            // 'Specialization',
            // 'Daily Rate',
            // 'Guide Type',
            // 'Availability Status',
            'Status (1=Active, 0=Inactive)'
        ];

        $data = [$headers];

        // Get guides based on user role
        $guides = Guide::where('dmc_id', $auth_user->userId)
                      ->where('status', 1)
                      ->with('languages')
                      ->get();

            if ($guides->count() > 0) {
            foreach ($guides as $guide) {
                // Get guide languages
                $guideLanguages = \App\Models\GuideLanguage::where('guide_id', $guide->guide_id)->get();
                
                if ($guideLanguages->count() > 0) {
                    // Create a row for each language-proficiency pair
                    foreach ($guideLanguages as $index => $guideLanguage) {
                        $row = [
                            // Guide basic info only on first language row
                            $index === 0 ? ($guide->salutation ?? '') : '',
                            $index === 0 ? ($guide->guide_gender ?? '') : '',
                            $index === 0 ? ($guide->name ?? '') : '',
                            $index === 0 ? ($guide->email ?? '') : '',
                            $index === 0 ? ($guide->contact_no ?? '') : '',
                            $index === 0 ? ($guide->service_type ?? '') : '',
                            $index === 0 ? ($guide->guide_age ?? '') : '',
                            $index === 0 ? ($guide->image ?? '') : '',
                            $index === 0 ? ($guide->government_license_no ?? '') : '',
                            $index === 0 ? ($guide->license_image ?? '') : '',
                            $index === 0 ? ($guide->license_exp_date ?? '') : '',
                            $index === 0 ? ($guide->city ?? '') : '',
                            $index === 0 ? ($guide->country ?? '') : '',
                            $index === 0 ? ($guide->experience_years ?? '') : '',
                            
                            // Language and proficiency for each row
                            $guideLanguage->language ?? '',
                            $guideLanguage->proficiency ?? '',
                            $guide->day_rate ?? '',
                            $guide->night_surcharge ?? '',
                            $guide->night_start_time ?? '',
                            $guide->night_end_time ?? '',
                            $guide->hourly_price ?? '',
                            $guide->two_hour_price ?? '',
                            $guide->four_hour_price ?? '',
                            $guide->six_hour_price ?? '',
                            $guide->eight_hour_price ?? '',
                            $guide->ten_hour_price ?? '',
                            $guide->twelve_hour_price ?? '',
                            $guide->description ?? '',
                            
                            // Status only on first row
                            $index === 0 ? ($guide->is_active ? '1' : '0') : ''
                        ];
                        
                        $data[] = $row;
                    }
                } else {
                    // Guide without languages - add guide row with empty language fields
                    $row = [
                        $guide->salutation ?? '',
                        $guide->guide_gender ?? '',
                        $guide->name ?? '',
                        $guide->email ?? '',
                        $guide->contact_no ?? '',
                        $guide->service_type ?? '',
                        $guide->guide_age ?? '',
                        $guide->image ?? '',
                        $guide->government_license_no ?? '',
                        $guide->license_image ?? '',
                        $guide->license_exp_date ?? '',
                        $guide->city ?? '',
                        $guide->country ?? '',
                        $guide->experience_years ?? '',
                        '', // Language
                        '', // Proficiency
                        $guide->day_rate ?? '',
                        $guide->night_surcharge ?? '',
                        $guide->night_start_time ?? '',
                        $guide->night_end_time ?? '',
                        $guide->hourly_price ?? '',
                        $guide->two_hour_price ?? '',
                        $guide->four_hour_price ?? '',
                        $guide->six_hour_price ?? '',
                        $guide->eight_hour_price ?? '',
                        $guide->ten_hour_price ?? '',
                        $guide->twelve_hour_price ?? '',
                        $guide->description ?? '',
                        $guide->is_active ? '1' : '0'
                    ];
                    $data[] = $row;
                }
            }
        } else {
            // No existing guides, add sample data for DMC format
            $sampleData1 = [
                'Mr',
                'Male',
                'John Smith',
                'john@example.com',
                '+65-9999-1234',
                '1',
                '35',
                'https://example.com/john.jpg',
                'GL123456',
                'https://example.com/license.jpg',
                '2025-12-31',
                'Singapore',
                'Singapore',
                '5',
                'English',
                'Fluent',
                '180',
                '10',
                '18:00',
                '06:00',
                '30',
                '55',
                '100',
                '140',
                '180',
                '220',
                '250',
                'Professional tour guide with 5 years experience',
            '1'
        ];

            $sampleData2 = [
                '', // Empty guide info for additional language
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'Mandarin',
                'Intermediate',
                '180',
                '10',
                '18:00',
                '06:00',
                '30',
                '55',
                '100',
                '140',
                '180',
                '220',
                '250',
                'Professional tour guide with 5 years experience',
                ''
            ];

            $data[] = $sampleData1;
            $data[] = $sampleData2;
        }

        $content = $this->generateCsvContent($data);
        $filename = 'dmc_guide_bulk_upload_template.csv';

        return Response::make($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function downloadTravclicksGuideTemplate($auth_user)
    {
        $headers = [
            'Salutation',
            'Gender*',
            'Guide Name*',
            'Email*',
            'Contact No*',
            'Service Type*',
            'Age*',
            'Master Image*',
            'License Number*',
            'License Image*',
            'License Expiry Date*',
            'City*',
            'Country*',
            'Experience Years*',
            'Languages*',
            'Proficiency*',
            'Minimum Base Price*',
            'Night Surcharge*',
            'Night Start Time*',
            'Night End Time*',
            'Hourly Price*',
            'Two Hour Price*',
            'Four Hour Price*',
            'Six Hour Price*',
            'Eight Hour Price*',
            'Ten Hour Price*',
            'Twelve Hour Price*',
            'Adout*',
            // 'Specialization',
            // 'Daily Rate',
            // 'Guide Type',
            // 'Availability Status',
            'Status (1=Active, 0=Inactive)'
        ];

        $data = [$headers];

        // Get guides based on user role
        $guides = Guide::where('status', 1)
                      ->with('languages') // Load language relationships
                      ->get();

        if ($guides->count() > 0) {
            foreach ($guides as $guide) {
                // Get guide languages
                $guideLanguages = \App\Models\GuideLanguage::where('guide_id', $guide->guide_id)->get();
                
                if ($guideLanguages->count() > 0) {
                    // Create a row for each language-proficiency pair
                    foreach ($guideLanguages as $index => $guideLanguage) {
                        $row = [
                            // Guide basic info only on first language row
                            $index === 0 ? ($guide->salutation ?? '') : '',
                            $index === 0 ? ($guide->guide_gender ?? '') : '',
                            $index === 0 ? ($guide->name ?? '') : '',
                            $index === 0 ? ($guide->email ?? '') : '',
                            $index === 0 ? ($guide->contact_no ?? '') : '',
                            $index === 0 ? ($guide->service_type ?? '') : '',
                            $index === 0 ? ($guide->guide_age ?? '') : '',
                            $index === 0 ? ($guide->image ?? '') : '',
                            $index === 0 ? ($guide->government_license_no ?? '') : '',
                            $index === 0 ? ($guide->license_image ?? '') : '',
                            $index === 0 ? ($guide->license_exp_date ?? '') : '',
                            $index === 0 ? ($guide->city ?? '') : '',
                            $index === 0 ? ($guide->country ?? '') : '',
                            $index === 0 ? ($guide->experience_years ?? '') : '',
                            
                            // Language and proficiency for each row
                            $guideLanguage->language ?? '',
                            $guideLanguage->proficiency ?? '',
                            $guide->day_rate ?? '',
                            $guide->night_surcharge ?? '',
                            $guide->night_start_time ?? '',
                            $guide->night_end_time ?? '',
                            $guide->hourly_price ?? '',
                            $guide->two_hour_price ?? '',
                            $guide->four_hour_price ?? '',
                            $guide->six_hour_price ?? '',
                            $guide->eight_hour_price ?? '',
                            $guide->ten_hour_price ?? '',
                            $guide->twelve_hour_price ?? '',
                            $guide->description ?? '',
                            // Status only on first row
                            $index === 0 ? ($guide->is_active ? '1' : '0') : ''
                        ];
                        
                        $data[] = $row;
                    }
                } else {
                    // Guide without languages - add guide row with empty language fields
                    $row = [
                        $guide->salutation ?? '',
                        $guide->guide_gender ?? '',
                        $guide->name ?? '',
                        $guide->email ?? '',
                        $guide->contact_no ?? '',
                        $guide->service_type ?? '',
                        $guide->guide_age ?? '',
                        $guide->image ?? '',
                        $guide->government_license_no ?? '',
                        $guide->license_image ?? '',
                        $guide->license_exp_date ?? '',
                        $guide->city ?? '',
                        $guide->country ?? '',
                        $guide->experience_years ?? '',
                        '', // Language
                        '', // Proficiency
                        $guide->day_rate ?? '',
                        $guide->night_surcharge ?? '',
                        $guide->night_start_time ?? '',
                        $guide->night_end_time ?? '',
                        $guide->hourly_price ?? '',
                        $guide->two_hour_price ?? '',
                        $guide->four_hour_price ?? '',
                        $guide->six_hour_price ?? '',
                        $guide->eight_hour_price ?? '',
                        $guide->ten_hour_price ?? '',
                        $guide->twelve_hour_price ?? '',
                        $guide->description ?? '',
                        $guide->is_active ? '1' : '0'
                    ];
                    $data[] = $row;
                }
            }
        } else {
            // No existing guides, add sample data for Travclicks format
            $sampleData1 = [
                'Mr',
                'Male',
                'John Smith',
                'john@example.com',
                '+65-9999-1234',
                '1',
                '35',
                'https://example.com/john.jpg',
                'GL123456',
                'https://example.com/license.jpg',
                '2025-12-31',
                'Singapore',
                'Singapore',
                '5',
                'English',
                'Fluent',
                '180',
                '10',
                '18:00',
                '06:00',
                '30',
                '55',
                '100',
                '140',
                '180',
                '220',
                '250',
                'Professional tour guide with 5 years experience',
                '1'
            ];

            $sampleData2 = [
                '', // Empty guide info for additional language
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'Mandarin',
                'Intermediate',
                '180',
                '10',
                '18:00',
                '06:00',
                '30',
                '55',
                '100',
                '140',
                '180',
                '220',
                '250',
                'Professional tour guide with 5 years experience',
                ''
            ];

            $sampleData3 = [
                '', // Empty guide info for additional language
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'French',
                'Beginner',
                '180',
                '10',
                '18:00',
                '06:00',
                '30',
                '55',
                '100',  
                '140',
                '180',
                '220',
                '250',
                'Professional tour guide with 5 years experience',
                ''
            ];

            $data[] = $sampleData1;
            $data[] = $sampleData2;
            $data[] = $sampleData3;
        }

        $content = $this->generateCsvContent($data);
        $filename = 'travclicks_guide_bulk_upload_template.csv';

        return Response::make($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // Restaurant Template Download  
    public function downloadRestaurantTemplate()
    {
        $auth_user = Auth::user();

        // Only Virtual DMC (role_id = 20) can download restaurant templates
        if ($auth_user->role_id != 20) {
            abort(403, 'Only Virtual DMC users can download restaurant templates.');
        }

        return $this->downloadVirtualDmcRestaurantTemplate($auth_user);
    }

    private function downloadVirtualDmcRestaurantTemplate($auth_user)
    {
        $headers = [
            // Restaurant Basic Info matching actual table structure
            'Restaurant Name*',
            'Cuisine*',
            'Country*',
            'City*',
            'Latitude*',
            'Longitude*',
            'Breakfast Available (1=Yes, 0=No)',
            'Breakfast Open Time',
            'Breakfast Close Time',
            'Lunch Available (1=Yes, 0=No)',
            'Lunch Open Time', 
            'Lunch Close Time',
            'Dinner Available (1=Yes, 0=No)',
            'Dinner Open Time',
            'Dinner Close Time',
            'Owned By (0=Third Party, 1=Hotel Owned)',
            'Master Image',
            'Additional Images (comma-separated)',
            'Description',
            'Terms and Condition',
            'Restaurant Status (1=Active, 0=Inactive)'
        ];

        $data = [$headers];

        // Get restaurants created by Virtual DMC
        $restaurants = Restaurant::where('created_by', $auth_user->userId)
                                ->where('status', 1)
                                ->get();

        if ($restaurants->count() > 0) {
            foreach ($restaurants as $restaurant) {
                // Get meals for this restaurant
                $meals = Meal::where('restaurant_id', $restaurant->restaurant_id)->get();
                
                if ($meals->count() > 0) {
                    foreach ($meals as $mealIndex => $meal) {
                        $row = [
                            // Restaurant info only on first meal row
                            $mealIndex === 0 ? ($restaurant->name ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->cuisine ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->country ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->city ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->latitude ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->longitude ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->owned_by ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->property ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->breakfast_available ? '1' : '0') : '',
                            $mealIndex === 0 ? ($restaurant->lunch_available ? '1' : '0') : '',
                            $mealIndex === 0 ? ($restaurant->dinner_available ? '1' : '0') : '',
                            $mealIndex === 0 ? ($restaurant->opening_time_bf ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->closing_time_bf ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->opening_time_lunch ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->closing_time_lunch ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->opening_time_dinner ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->closing_time_dinner ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->master_image ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->images ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->description ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->terms_conditions ?? '') : '',
                            $restaurant->is_active ? '1' : '0',
                            // Meal Type
                            match($meal->meal_period ?? 1) {
                                1 => 'Breakfast',
                                2 => 'Lunch', 
                                3 => 'Dinner',
                                default => 'Breakfast'
                            },
                            
                            // Beverage
                            match($meal->category ?? 1) {
                                1 => 'Alcoholic',
                                2 => 'Non Alcoholic',
                                3 => 'No Beverage',
                                default => 'Non Alcoholic'
                            },
                            
                            // Meals Type
                            match($meal->type ?? '2') {
                                '1' => 'Buffet',
                                '2' => 'Set Menu',
                                1 => 'Buffet',
                                2 => 'Set Menu',
                                default => 'Set Menu'
                            },
                            
                            // Item Name (for Set Menu)
                            // $meal->meals == 2 ? ($meal->item_name ?? '') : '',
                            
                            // Item Price (for Set Menu)
                            ($meal->type == '2' || $meal->type == 2) ? ($meal->price ?? '') : '',
                            
                            // Item Type
                            match($meal->item_type ?? 1) {
                                1 => 'Vegetarian',
                                2 => 'Non Vegetarian',
                                default => 'Vegetarian'
                            },
                            
                            // Adult Price (for Buffet)
                            ($meal->type == '1' || $meal->type == 1) ? ($meal->adult_price ?? '') : '',
                            
                            // Child Price (for Buffet)
                            ($meal->type == '1' || $meal->type == 1) ? ($meal->child_price ?? '') : '',
                            
                            // Item Description
                            $meal->item_description ?? '',
                            $meal->is_active ? '1' : '0'
                        ];
                        
                        $data[] = $row;
                    }
                } else {
                    // Restaurant without meals - add restaurant row with sample meal
                    $row = [
                        $restaurant->name ?? '',
                        $restaurant->cuisine ?? '',
                        $restaurant->country ?? '',
                        $restaurant->city ?? '',
                        $restaurant->latitude ?? '',
                        $restaurant->longitude ?? '',
                        $restaurant->owned_by ?? '',
                        $restaurant->property ?? '',
                        $restaurant->breakfast_available ? '1' : '0',
                        $restaurant->lunch_available ? '1' : '0',
                        $restaurant->dinner_available ? '1' : '0',
                        $restaurant->opening_time_bf ?? '',
                        $restaurant->closing_time_bf ?? '',
                        $restaurant->opening_time_lunch ?? '',
                        $restaurant->closing_time_lunch ?? '',
                        $restaurant->opening_time_dinner ?? '',
                        $restaurant->closing_time_dinner ?? '',
                        $restaurant->master_image ?? '',
                        $restaurant->images ?? '',
                        $restaurant->description ?? '',
                        $restaurant->terms_conditions ?? '',
                        $restaurant->is_active ? '1' : '0',
                        '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
                    ];
                    $data[] = $row;
                }
            }
        } else {
            // No existing restaurants, add sample data for Virtual DMC format
            $sampleData1 = [
                'Sample Restaurant',
                'Italian',
                'United States',
                'New York',
                '40.7128',
                '-74.0060',
                '1',
                '07:00',
                '11:00',
                '1', 
                '12:00',
                '15:00',
                '1',
                '18:00',
                '23:00',
                '0',
                'restaurant_main.jpg',
                'rest1.jpg,rest2.jpg',
                'Authentic Italian restaurant with fresh ingredients',
                'No outside food allowed. Dress code applies.',
                '1'
            ];

            $sampleData2 = [
                'Cafe Delight',
                'French',
                'France',
                'Paris',
                '48.8566',
                '2.3522',
                '1',
                '06:30',
                '10:30',
                '0', 
                '',
                '',
                '1',
                '17:00',
                '22:00',
                '1',
                'cafe_main.jpg',
                'cafe1.jpg,cafe2.jpg,cafe3.jpg',
                'Modern French cafe with delicious pastries',
                'Reservation required for dinner. No cancellation within 2 hours.',
                '1'
            ];

            $data[] = $sampleData1;
            $data[] = $sampleData2;
        }

        $content = $this->generateCsvContent($data);
        $filename = 'virtual_dmc_restaurant_bulk_upload_template.csv';

        return Response::make($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function downloadTravclicksRestaurantTemplate($auth_user)
    {
        $headers = [
            // Restaurant Basic Info
            'Restaurant Name*',
            'Country*',
            'City*', 
            'Latitude*',
            'Longitude*',
            'Cuisine*',
            'Ownership*',
            'Property*',
            'Breakfast Availability',
            'Lunch Availability',
            'Dinner Availability',
            'Breakfast Open Time',
            'Breakfast Close Time',
            'Lunch Open Time', 
            'Lunch Close Time',
            'Dinner Open Time',
            'Dinner Close Time',
            'Master Image',
            'Additional Image',
            'Description',
            'Terms and Condition',
            'Restaurant Status (1=Active, 0=Inactive)',
            
            // Meal Info Headers
            'Meal Type*',
            'Beverage*',
            'Meals*',
            // 'Item Name',
            'Item Price', 
            'Item Type',
            'Adult Price',
            'Child Price',
            'Item Description*'
        ];

        $data = [$headers];

        // Get all restaurants - Travclicks users can see all data
        $restaurants = Restaurant::where('status', 1)->get();

        if ($restaurants->count() > 0) {
            foreach ($restaurants as $restaurant) {
                // Get meals for this restaurant
                $meals = Meal::where('restaurant_id', $restaurant->restaurant_id)->get();
                
                if ($meals->count() > 0) {
                    foreach ($meals as $mealIndex => $meal) {
                        $row = [
                            // Restaurant info only on first meal row
                            $mealIndex === 0 ? ($restaurant->name ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->country ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->city ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->latitude ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->longitude ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->cuisine ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->owned_by ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->property ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->breakfast_available ? '1' : '0') : '',
                            $mealIndex === 0 ? ($restaurant->lunch_available ? '1' : '0') : '',
                            $mealIndex === 0 ? ($restaurant->dinner_available ? '1' : '0') : '',
                            $mealIndex === 0 ? ($restaurant->opening_time_bf ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->closing_time_bf ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->opening_time_lunch ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->closing_time_lunch ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->opening_time_dinner ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->closing_time_dinner ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->master_image ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->images ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->description ?? '') : '',
                            $mealIndex === 0 ? ($restaurant->terms_conditions ?? '') : '',
                            $restaurant->is_active ? '1' : '0',
                            
                            // Meal Type
                            match($meal->meal_period ?? 1) {
                                1 => 'Breakfast',
                                2 => 'Lunch', 
                                3 => 'Dinner',
                                default => 'Breakfast'
                            },
                            
                            // Beverage
                            match($meal->category ?? 1) {
                                1 => 'Alcoholic',
                                2 => 'Non Alcoholic',
                                3 => 'No Beverage',
                                default => 'Non Alcoholic'
                            },
                            
                            // Meals Type
                            match($meal->type ?? '2') {
                                '1' => 'Buffet',
                                '2' => 'Set Menu',
                                1 => 'Buffet',
                                2 => 'Set Menu',
                                default => 'Set Menu'
                            },
                            
                            // Item Name (for Set Menu)
                            // $meal->meals == 2 ? ($meal->item_name ?? '') : '',
                            
                            // Item Price (for Set Menu)
                            ($meal->type == '2' || $meal->type == 2) ? ($meal->price ?? '') : '',
                            
                            // Item Type
                            match($meal->item_type ?? 1) {
                                1 => 'Vegetarian',
                                2 => 'Non Vegetarian',
                                default => 'Vegetarian'
                            },
                            
                            // Adult Price (for Buffet)
                            ($meal->type == '1' || $meal->type == 1) ? ($meal->adult_price ?? '') : '',
                            
                            // Child Price (for Buffet)
                            ($meal->type == '1' || $meal->type == 1) ? ($meal->child_price ?? '') : '',
                            
                            // Item Description
                            $meal->item_description ?? '',
                            $meal->is_active ? '1' : '0'
                        ];
                        
                        $data[] = $row;
                    }
                } else {
                    // Restaurant without meals - add restaurant row with empty meal fields
                $row = [
                    $restaurant->name ?? '',
                        $restaurant->country ?? '',
                        $restaurant->city ?? '',
                        $restaurant->latitude ?? '',
                        $restaurant->longitude ?? '',
                    $restaurant->cuisine ?? '',
                        $restaurant->owned_by ?? '',
                        $restaurant->property ?? '',
                        $restaurant->breakfast_available ? '1' : '0',
                        $restaurant->lunch_available ? '1' : '0',
                        $restaurant->dinner_available ? '1' : '0',
                        $restaurant->opening_time_bf ?? '',
                        $restaurant->closing_time_bf ?? '',
                        $restaurant->opening_time_lunch ?? '',
                        $restaurant->closing_time_lunch ?? '',
                        $restaurant->opening_time_dinner ?? '',
                        $restaurant->closing_time_dinner ?? '',
                        $restaurant->master_image ?? '',
                        $restaurant->images ?? '',
                        $restaurant->description ?? '',
                        $restaurant->terms_conditions ?? '',
                        $restaurant->is_active ? '1' : '0',
                        '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
                    ];
                $data[] = $row;
                }
            }
        } else {
            // No existing restaurants, add sample data for Travclicks format
            $sampleData1 = [
                'Sample Restaurant',
                'United States',
                'New York',
                '40.7128',
                '-74.0060',
                'Italian',
                'Independent',
                'Fine Dining',
                '1',
                '1', 
                '1',
                '07:00',
                '11:00',
                '12:00',
                '15:00',
                '18:00',
                '23:00',
                'restaurant_main.jpg',
                'rest1.jpg,rest2.jpg',
                'Authentic Italian restaurant with fresh ingredients',
                'No outside food allowed. Dress code applies.',
                'Breakfast',
                'Non Alcoholic',
                'Buffet',
                '',
                '',
                'Vegetarian',
                '25.00',
                '12.50',
                'Continental breakfast with fresh fruits and pastries'
            ];

            $sampleData2 = [
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'Lunch',
                'Alcoholic',
                'Set Menu',
                'Pasta Carbonara',
                '18.50',
                'Non Vegetarian',
                '',
                '',
                'Authentic Italian pasta with pancetta and parmesan'
            ];

            $sampleData3 = [
                'Asian Fusion Cafe',
                'Singapore',
                'Singapore',
                '1.3521',
                '103.8198',
                'Asian Fusion',
                'Franchise',
                'Casual Dining',
                '0',
                '1', 
                '1',
                '',
                '',
                '11:00',
                '16:00',
                '17:00',
                '22:00',
                'asian_main.jpg',
                'asian1.jpg,asian2.jpg',
                'Modern Asian fusion cuisine with a twist',
                'Reservation required for dinner. No cancellation within 2 hours.',
                'Dinner',
                'No Beverage',
                'Set Menu',
                'Ramen Bowl',
                '14.90',
                'Non Vegetarian',
                '',
                '',
                'Rich tonkotsu broth with chashu pork and soft-boiled egg'
            ];

            $data[] = $sampleData1;
            $data[] = $sampleData2;
            $data[] = $sampleData3;
        }

        $content = $this->generateCsvContent($data);
        $filename = 'travclicks_restaurant_bulk_upload_template.csv';

        return Response::make($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // Restaurant Upload Method
    public function uploadRestaurants(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB limit
            ]);

            $file = $request->file('file');
            $csvData = $this->readCsvFile($file->getPathname());
            
            if (empty($csvData)) {
                return redirect()->back()->with('error', 'The uploaded file is empty or invalid.');
            }

            // Remove header row
            array_shift($csvData);
            
            $auth_user = Auth::user();
            
            // Only Virtual DMC (role_id = 20) can upload restaurants
            if ($auth_user->role_id != 20) {
                return redirect()->back()->with('error', 'Only Virtual DMC users can upload restaurants.');
            }

            return $this->uploadVirtualDmcRestaurants($csvData, $auth_user);
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Restaurant bulk upload failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    private function uploadVirtualDmcRestaurants($csvData, $auth_user)
    {
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        DB::beginTransaction();
        
        foreach ($csvData as $rowIndex => $row) {
            $rowNumber = $rowIndex + 2; // +2 because we removed header and rows start at 1
            
            try {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                // Map CSV columns to match template structure 
                $restaurantName = trim($row[0] ?? '');
                $cuisine = trim($row[1] ?? '');
                $country = trim($row[2] ?? '');
                $city = trim($row[3] ?? '');
                $latitude = trim($row[4] ?? '');
                $longitude = trim($row[5] ?? '');
                $breakfastAvailability = trim($row[6] ?? '0');
                $breakfastOpenTime = trim($row[7] ?? '');
                $breakfastCloseTime = trim($row[8] ?? '');
                $lunchAvailability = trim($row[9] ?? '0');
                $lunchOpenTime = trim($row[10] ?? '');
                $lunchCloseTime = trim($row[11] ?? '');
                $dinnerAvailability = trim($row[12] ?? '0');
                $dinnerOpenTime = trim($row[13] ?? '');
                $dinnerCloseTime = trim($row[14] ?? '');
                $ownedBy = trim($row[15] ?? '0'); // 0 = Third party, 1 = Hotel owned
                $masterImage = trim($row[16] ?? '');
                $additionalImages = trim($row[17] ?? '');
                $description = trim($row[18] ?? '');
                $termsConditions = trim($row[19] ?? '');
                $status = trim($row[20] ?? '1');
                
                // Validate required restaurant fields
                if (empty($restaurantName) || empty($cuisine) || empty($country) || empty($city)) {
                    $errors[] = "Row {$rowNumber}: Missing required restaurant fields (Restaurant Name, Cuisine, Country, or City)";
                    $errorCount++;
                    continue;
                }
                
                // Create new restaurant with location and additional data
                $restaurant = new Restaurant();
                $restaurant->name = $restaurantName;
                $restaurant->cuisine = $cuisine;
                $restaurant->breakfast_available = ($breakfastAvailability == '1') ? 1 : 0;
                $restaurant->lunch_available = ($lunchAvailability == '1') ? 1 : 0;
                $restaurant->dinner_available = ($dinnerAvailability == '1') ? 1 : 0;
                $restaurant->owned_by = ($ownedBy == '1') ? 1 : 0;
                
                // Set country and city if columns exist
                if (Schema::hasColumn('restaurants', 'country')) {
                    $restaurant->country = $country;
                }
                if (Schema::hasColumn('restaurants', 'city')) {
                    $restaurant->city = $city;
                }
                if (Schema::hasColumn('restaurants', 'latitude')) {
                    $restaurant->latitude = is_numeric($latitude) ? floatval($latitude) : null;
                }
                if (Schema::hasColumn('restaurants', 'longitude')) {
                    $restaurant->longitude = is_numeric($longitude) ? floatval($longitude) : null;
                }
                if (Schema::hasColumn('restaurants', 'description')) {
                    $restaurant->description = $description;
                }
                if (Schema::hasColumn('restaurants', 'terms_conditions')) {
                    $restaurant->terms_conditions = $termsConditions;
                }
                if (Schema::hasColumn('restaurants', 'hotel_id')) {
                    $restaurant->hotel_id = 1; // Default hotel ID
                }
                if (Schema::hasColumn('restaurants', 'status')) {
                    $restaurant->status = ($status == '1') ? 1 : 0;
                }
                if (Schema::hasColumn('restaurants', 'is_active')) {
                    $restaurant->is_active = ($status == '1') ? 1 : 0;
                }
                if (Schema::hasColumn('restaurants', 'dmc_id')) {
                    $restaurant->dmc_id = $auth_user->userId;
                }
                if (Schema::hasColumn('restaurants', 'created_by')) {
                    $restaurant->created_by = $auth_user->userId;
                }
                
                // Set time fields based on availability
                if ($restaurant->breakfast_available && !empty($breakfastOpenTime) && !empty($breakfastCloseTime)) {
                    $restaurant->opening_time_bf = $breakfastOpenTime;
                    $restaurant->closing_time_bf = $breakfastCloseTime;
                }
                if ($restaurant->lunch_available && !empty($lunchOpenTime) && !empty($lunchCloseTime)) {
                    $restaurant->opening_time_lunch = $lunchOpenTime;
                    $restaurant->closing_time_lunch = $lunchCloseTime;
                }
                if ($restaurant->dinner_available && !empty($dinnerOpenTime) && !empty($dinnerCloseTime)) {
                    $restaurant->opening_time_dinner = $dinnerOpenTime;
                    $restaurant->closing_time_dinner = $dinnerCloseTime;
                }
                
                // Set image fields if they exist in the table
                if (!empty($masterImage)) {
                    $restaurant->master_image = $masterImage;
                }
                if (!empty($additionalImages)) {
                    // Convert comma-separated images to JSON array
                    $imagesArray = array_map('trim', explode(',', $additionalImages));
                    $restaurant->images = json_encode($imagesArray);
                }
                
                $restaurant->save();
                $successCount++;
                
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                $errorCount++;
                Log::error("Restaurant bulk upload error on row {$rowNumber}: " . $e->getMessage());
            }
        }
        
        DB::commit();
        
        $message = "Upload completed. {$successCount} restaurants processed successfully.";
        if ($errorCount > 0) {
            $message .= " {$errorCount} errors occurred.";
        }
        
        return redirect()->back()
            ->with('success', $message)
            ->with('errors', $errors);
    }

    private function getRestaurantOpeningTime($restaurant)
    {
        // Check what meal types are available and return the earliest opening time
        $openingTimes = [];
        
        if ($restaurant->breakfast_available && $restaurant->opening_time_bf) {
            $openingTimes[] = $restaurant->opening_time_bf;
        }
        if ($restaurant->lunch_available && $restaurant->opening_time_lunch) {
            $openingTimes[] = $restaurant->opening_time_lunch;
        }
        if ($restaurant->dinner_available && $restaurant->opening_time_dinner) {
            $openingTimes[] = $restaurant->opening_time_dinner;
        }
        
        return !empty($openingTimes) ? min($openingTimes) : '';
    }

    private function getRestaurantClosingTime($restaurant)
    {
        // Check what meal types are available and return the latest closing time
        $closingTimes = [];
        
        if ($restaurant->breakfast_available && $restaurant->closing_time_bf) {
            $closingTimes[] = $restaurant->closing_time_bf;
        }
        if ($restaurant->lunch_available && $restaurant->closing_time_lunch) {
            $closingTimes[] = $restaurant->closing_time_lunch;
        }
        if ($restaurant->dinner_available && $restaurant->closing_time_dinner) {
            $closingTimes[] = $restaurant->closing_time_dinner;
        }
        
        return !empty($closingTimes) ? max($closingTimes) : '';
    }

    private function createMeal($restaurant, $mealType, $beverage, $mealsType, $itemName, $itemPrice, $itemType, $adultPrice, $childPrice, $itemDescription, $mealStatus, $userId)
    {
        $lastMeal = Meal::withTrashed()->orderBy('created_at', 'desc')->first();
        $meal_max_id = $lastMeal->meal_id ?? 0;
        $mealId = \App\Helpers\CommonHelper::createId($meal_max_id);
        while (Meal::where('meal_id', $mealId)->exists()) {
            $mealId = \App\Helpers\CommonHelper::createId($mealId);
        }
        
        $meal = new Meal();
        $meal->meal_id = $mealId;
        $meal->restaurant_id = $restaurant->restaurant_id;
        $meal->name = $itemName ?: 'Menu Item';
        $meal->item_description = $itemDescription;
        $meal->dmc_id = $restaurant->dmc_id; // Set DMC ID from restaurant
        
        // Map meal period (breakfast/lunch/dinner)
        $meal->meal_period = match(strtolower($mealType)) {
            'breakfast' => 1,
            'lunch' => 2,
            'dinner' => 3,
            default => 1
        };
        
        // Map category (beverage type)
        $meal->category = match(strtolower($beverage)) {
            'alcoholic' => 1,
            'non alcoholic' => 2,
            'no beverage' => 3,
            default => 2
        };
        
        // Map type (buffet/set menu)
        $meal->type = match(strtolower($mealsType)) {
            'buffet' => 1,
            'set menu' => 2,
            default => 2
        };
        
        // Map item type
        if (!empty($itemType)) {
            $meal->item_type = match(strtolower($itemType)) {
                'vegetarian' => 1,
                'non vegetarian' => 2,
                default => 1
            };
        }
        
        // Set prices based on meal type
        if (strtolower($mealsType) === 'buffet') {
            $meal->adult_price = is_numeric($adultPrice) ? floatval($adultPrice) : 0;
            $meal->child_price = is_numeric($childPrice) ? floatval($childPrice) : 0;
        } else {
            $meal->price = is_numeric($itemPrice) ? floatval($itemPrice) : 0;
        }
        
        $meal->is_active = ($mealStatus == '1') ? 1 : 0;
        $meal->created_by = $userId;
        
        $meal->save();
        
        return $meal;
    }

    private function uploadDmcGuides($csvData, $auth_user)
    {
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        DB::beginTransaction();
        
        foreach ($csvData as $rowIndex => $row) {
            $rowNumber = $rowIndex + 2; // +2 because we removed header and rows start at 1
            
            try {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                // Map CSV columns to variables for DMC format
                $guideName = trim($row[0] ?? '');
                $email = trim($row[1] ?? '');
                $phone = trim($row[2] ?? '');
                $licenseNumber = trim($row[3] ?? '');
                $city = trim($row[4] ?? '');
                $country = trim($row[5] ?? '');
                $experience = trim($row[6] ?? '');
                $languages = trim($row[7] ?? '');
                $specialization = trim($row[8] ?? '');
                $dailyRate = trim($row[9] ?? '');
                $guideType = trim($row[10] ?? '');
                $availabilityStatus = trim($row[11] ?? '');
                $status = trim($row[12] ?? '1');
                
                // Validate required fields
                if (empty($guideName) || empty($email) || empty($phone) || empty($languages)) {
                    $errors[] = "Row {$rowNumber}: Missing required fields (Guide Name, Email, Phone, or Languages)";
                    $errorCount++;
                    continue;
                }
                
                // Create new guide
                $lastGuide = Guide::withTrashed()->orderBy('created_at', 'desc')->first();
                $guide_max_id = $lastGuide->guide_id ?? 0;
                $guideId = \App\Helpers\CommonHelper::createId($guide_max_id);
                while (Guide::where('guide_id', $guideId)->exists()) {
                    $guideId = \App\Helpers\CommonHelper::createId($guideId);
                }
                
                $guide = new Guide();
                $guide->guide_id = $guideId;
                $guide->name = $guideName;
                $guide->email = $email;
                $guide->phone = $phone;
                $guide->license_number = $licenseNumber;
                $guide->city = $city;
                $guide->country = $country;
                $guide->experience = is_numeric($experience) ? intval($experience) : 0;
                $guide->languages = $languages;
                $guide->specialization = $specialization;
                $guide->daily_rate = is_numeric($dailyRate) ? floatval($dailyRate) : 0;
                $guide->guide_type = $guideType;
                $guide->availability_status = $availabilityStatus;
                $guide->is_active = ($status == '1') ? 1 : 0;
                $guide->status = 1; // Default approved status
                $guide->dmc_id = $auth_user->userId; // DMC users assign to their own DMC
                $guide->created_by = $auth_user->userId;
                
                $guide->save();
                $successCount++;
                
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                $errorCount++;
                Log::error("Guide bulk upload error on row {$rowNumber}: " . $e->getMessage());
            }
        }
        
        DB::commit();
        
        $message = "Upload completed. {$successCount} guides processed successfully.";
        if ($errorCount > 0) {
            $message .= " {$errorCount} errors occurred.";
        }
        
        return redirect()->back()
            ->with('success', $message)
            ->with('errors', $errors);
    }

    private function uploadTravclicksGuides($csvData, $auth_user)
    {
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        DB::beginTransaction();
        
        foreach ($csvData as $rowIndex => $row) {
            $rowNumber = $rowIndex + 2; // +2 because we removed header and rows start at 1
            
            try {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                // Map CSV columns to variables for Travclicks format
                $guideName = trim($row[0] ?? '');
                $email = trim($row[1] ?? '');
                $phone = trim($row[2] ?? '');
                $address = trim($row[3] ?? '');
                $city = trim($row[4] ?? '');
                $country = trim($row[5] ?? '');
                $postalCode = trim($row[6] ?? '');
                $experience = trim($row[7] ?? '');
                $languages = trim($row[8] ?? '');
                $specialization = trim($row[9] ?? '');
                $certification = trim($row[10] ?? '');
                $dailyRate = trim($row[11] ?? '');
                $website = trim($row[12] ?? '');
                $socialMedia = trim($row[13] ?? '');
                $status = trim($row[14] ?? '1');
                
                // Validate required fields
                if (empty($guideName) || empty($email) || empty($phone) || empty($languages)) {
                    $errors[] = "Row {$rowNumber}: Missing required fields (Guide Name, Email, Phone, or Languages)";
                    $errorCount++;
                    continue;
                }
                
                // Create new guide
                $lastGuide = Guide::withTrashed()->orderBy('created_at', 'desc')->first();
                $guide_max_id = $lastGuide->guide_id ?? 0;
                $guideId = \App\Helpers\CommonHelper::createId($guide_max_id);
                while (Guide::where('guide_id', $guideId)->exists()) {
                    $guideId = \App\Helpers\CommonHelper::createId($guideId);
                }
                
                $guide = new Guide();
                $guide->guide_id = $guideId;
                $guide->name = $guideName;
                $guide->email = $email;
                $guide->phone = $phone;
                $guide->address = $address;
                $guide->city = $city;
                $guide->country = $country;
                $guide->postal_code = $postalCode;
                $guide->experience = is_numeric($experience) ? intval($experience) : 0;
                $guide->languages = $languages;
                $guide->specialization = $specialization;
                $guide->certification = $certification;
                $guide->daily_rate = is_numeric($dailyRate) ? floatval($dailyRate) : 0;
                $guide->website = $website;
                $guide->social_media = $socialMedia;
                $guide->is_active = ($status == '1') ? 1 : 0;
                $guide->status = 1; // Default approved status
                
                // Set dmc_id based on user role
                if ($auth_user->role_id == 20) {
                    // Virtual DMC - assign to their own DMC
                    $guide->dmc_id = $auth_user->userId;
                } else {
                    // Other Travclicks users - assign to their userId
                    $guide->dmc_id = $auth_user->userId;
                }
                
                $guide->created_by = $auth_user->userId;
                $guide->save();
                
                $successCount++;
                
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                $errorCount++;
                Log::error("Guide bulk upload error on row {$rowNumber}: " . $e->getMessage());
            }
        }
        
        DB::commit();
        
        $message = "Upload completed. {$successCount} guides processed successfully.";
        if ($errorCount > 0) {
            $message .= " {$errorCount} errors occurred.";
        }
        
        return redirect()->back()
            ->with('success', $message)
            ->with('errors', $errors);
    }

    // Vehicle Template Download
    public function downloadVehicleTemplate()
    {
        $headers = [
            'Vehicle Name*',
            'Vehicle Type*',
            'License Plate*',
            'Capacity*',
            'City*',
            'Country*',
            'Daily Rate',
            'Status (1=Active, 0=Inactive)'
        ];

        $sampleData = [
            'Toyota Camry 2023',
            'Sedan',
            'ABC123',
            '4',
            'New York',
            'United States',
            '80',
            '1'
        ];

        $content = $this->generateCsvContent([$headers, $sampleData]);
        $filename = 'vehicle_bulk_upload_template.csv';

        return Response::make($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // Attraction Template Download
    public function downloadAttractionTemplate()
    {
        $auth_user = Auth::user();

        // Define role groups
        $dmcFullAccessRoles = [11, 35]; // DMC, Product Head (DMC)
        $dmcAttractionRoles = [80, 122]; // Product Manager Attraction (DMC), Assistant PM Attraction (DMC)
        $travclicksFullAccessRoles = [1, 23, 20, 29]; // Travclicks, Product Head (Travclicks), Virtual DMC, Assistant Manager(PROD HEAD)
        $travclicksAttractionRoles = [50, 123]; // Product Manager Attraction (Travclicks), Assistant PM Attraction (Travclicks)

        // Check if user is DMC or Travclicks
        $isDmcUser = in_array($auth_user->role_id, array_merge($dmcFullAccessRoles, $dmcAttractionRoles));
        $isTravclicksUser = in_array($auth_user->role_id, array_merge($travclicksFullAccessRoles, $travclicksAttractionRoles));

        // Virtual DMC (role_id = 20) uses DMC template format but with their own data scope
        if ($isDmcUser || ($auth_user->role_id == 20)) { // DMC users or Virtual DMC
            return $this->downloadDmcAttractionTemplate($auth_user);
        } elseif ($isTravclicksUser) { // Travclicks users
            return $this->downloadTravclicksAttractionTemplate($auth_user);
        } else {
            abort(403, 'You do not have permission to download attraction templates.');
        }
    }

    private function downloadDmcAttractionTemplate($auth_user)
    {
        $headers = [
            'Attraction Name*',
            'Country*',
            'City*',
            'Senior Age Threshold*',
            'Maximum Child Age*',
            'Latitude*',
            'Longitude*',
            'Morning Opening*',
            'Afternoon Opening*',
            'Evening Opening*',
            'Night Opening*',
            'Open Time*',
            'Close Time*',
            'Master Image*',
            'Additional Images',
            'Description*',
            'Terms & Conditions*',
            'Status*'
        ];

        $data = [$headers];

        // Get attractions based on user role - DMC users only see their own attractions
        $attractions = Attraction::where('dmc_id', $auth_user->userId)
                                ->where('status', 1)
                                ->get();

        if ($attractions->count() > 0) {
            foreach ($attractions as $attraction) {
                $row = [
                    $attraction->name ?? '',
                    $attraction->country ?? '',
                    $attraction->location ?? '',
                    $attraction->senior_min_age ?? '',
                    $attraction->child_max_age ?? '',
                    $attraction->latitude ?? '',
                    $attraction->longitude ?? '',
                    $attraction->morning_opening ? '1' : '0',
                    $attraction->afternoon_opening ? '1' : '0',
                    $attraction->evening_opening ? '1' : '0',
                    $attraction->night_opening ? '1' : '0',
                    $attraction->open_time ?? '',
                    $attraction->close_time ?? '',
                    $attraction->master_image ?? '',
                    is_array($attraction->additional_image) ? implode(',', $attraction->additional_image) : 
                        (is_string($attraction->additional_image) && !empty($attraction->additional_image) ? 
                         implode(',', json_decode($attraction->additional_image, true) ?? []) : ''),
                    $attraction->description ?? '',
                    $attraction->terms_conditions ?? '',
                    $attraction->status ? '1' : '0'
                ];
                
                $data[] = $row;
            }
        } else {
            // No existing attractions, add sample data for DMC format
        $sampleData = [
            'Sample Museum',
                'United States',
            'New York',
                '65',
                '12',
                '40.7128',
                '-74.0060',
                '1',
                '1',
                '0',
                '0',
                '09:00',
                '17:00',
                'museum_main.jpg',
                'image1.jpg,image2.jpg,image3.jpg',
                'Please arrive 15 minutes before your scheduled time',
                'No refunds after booking confirmation',
                '1'
            ];

            $data[] = $sampleData;
        }

        $content = $this->generateCsvContent($data);
        $filename = 'dmc_attraction_bulk_upload_template.csv';

        return Response::make($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function downloadTravclicksAttractionTemplate($auth_user)
    {
        $headers = [
            'Attraction Name*',
            'Country*',
            'City*',
            'Senior Age Threshold*',
            'Maximum Child Age*',
            'Latitude*',
            'Longitude*',
            'Morning Opening*',
            'Afternoon Opening*',
            'Evening Opening*',
            'Night Opening*',
            'Open Time*',
            'Close Time*',
            'Master Image*',
            'Additional Images',
            'Important Notes*',
            'Terms & Conditions*',
            'Status*'
        ];

        $data = [$headers];

        // Get all attractions - Travclicks users can see all data
        $attractions = Attraction::where('status', 1)->get();

        if ($attractions->count() > 0) {
            foreach ($attractions as $attraction) {
                $row = [
                    $attraction->name ?? '',
                    $attraction->country ?? '',
                    $attraction->location ?? '',
                    $attraction->senior_min_age ?? '',
                    $attraction->child_max_age ?? '',
                    $attraction->latitude ?? '',
                    $attraction->longitude ?? '',
                    $attraction->morning_opening ? '1' : '0',
                    $attraction->afternoon_opening ? '1' : '0',
                    $attraction->evening_opening ? '1' : '0',
                    $attraction->night_opening ? '1' : '0',
                    $attraction->open_time ?? '',
                    $attraction->close_time ?? '',
                    $attraction->master_image ?? '',
                    is_array($attraction->additional_image) ? implode(',', $attraction->additional_image) : 
                        (is_string($attraction->additional_image) && !empty($attraction->additional_image) ? 
                         implode(',', json_decode($attraction->additional_image, true) ?? []) : ''),
                    $attraction->description ?? '',
                    $attraction->terms_conditions ?? '',
                    $attraction->status ? '1' : '0'
                ];
                
                $data[] = $row;
            }
        } else {
            // No existing attractions, add sample data for Travclicks format
            $sampleData1 = [
                'Sample Museum',
            'United States',
                'New York',
                '65',
                '12',
                '40.7128',
                '-74.0060',
                '1',
                '1',
                '0',
                '0',
            '09:00',
            '17:00',
                'museum_main.jpg',
                'image1.jpg,image2.jpg,image3.jpg',
                'Please arrive 15 minutes before your scheduled time',
                'No refunds after booking confirmation',
            '1'
        ];

            $sampleData2 = [
                'Adventure Park',
                'Singapore',
                'Singapore',
                '60',
                '15',
                '1.3521',
                '103.8198',
                '1',
                '1',
                '1',
                '0',
                '08:00',
                '22:00',
                'adventure_main.jpg',
                'adventure1.jpg,adventure2.jpg',
                'Safety equipment provided for all activities',
                'All bookings are non-refundable',
                '1'
            ];

            $data[] = $sampleData1;
            $data[] = $sampleData2;
        }

        $content = $this->generateCsvContent($data);
        $filename = 'travclicks_attraction_bulk_upload_template.csv';

        return Response::make($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // Ticket Template Download - Only for DMC users (role_id = 11)
    public function downloadTicketTemplate()
    {
        $auth_user = Auth::user();

        // Check for DMC role - role_id is stored as string "11"
        if (!$auth_user || $auth_user->role_id !== '11') {
            abort(403, 'Access denied. Only DMC users can access ticket bulk upload.');
        }

        return $this->downloadDmcTicketTemplate($auth_user);
    }

    private function downloadDmcTicketTemplate($auth_user)
    {
        try {
            // Get attractions with their existing tickets for this DMC user
            $attractions = Attraction::with(['tickets' => function($query) {
                $query->where('status', 1);
            }])
            ->where('dmc_id', $auth_user->userId)
            ->where('status', 1)
            ->get();
        } catch (\Exception $e) {
            // If there's an issue with the query, just return sample data
            $attractions = collect();
        }

        // Headers for attraction + ticket data
        $headers = [
            'Attraction Name*',
            'Country*',
            'City*',
            'Ticket Name*',
            'Child Price(local)*',
            'Adult Price(local)*',
            'Senior Citizen Price(local)*',
            'Child Price(foreigner)*',
            'Adult Price(foreigner)*',
            'Senior Citizen Price(foreigner)*',
            'Important Notes*',
            'Terms & Conditions*',
            'Status*'
        ];

        $data = [$headers];

        if ($attractions->count() > 0) {
            foreach ($attractions as $attraction) {
                $isFirstRowForAttraction = true;
                
                // First, show existing tickets for this attraction
                if (isset($attraction->tickets) && $attraction->tickets->count() > 0) {
                    foreach ($attraction->tickets as $ticket) {
                        $row = [
                            $isFirstRowForAttraction ? ($attraction->name ?? '') : '',
                            $isFirstRowForAttraction ? ($attraction->country ?? '') : '',
                            $isFirstRowForAttraction ? ($attraction->location ?? '') : '',
                            $ticket->name ?? '',
                            $ticket->child_price ?? '0',
                            $ticket->adult_price ?? '0',
                            $ticket->senior_adult_price ?? '0',
                            $ticket->child_price_nri ?? '0',
                            $ticket->adult_price_nri ?? '0',
                            $ticket->senior_adult_price_nri ?? '0',
                            $ticket->description ?? '',
                            $ticket->terms_conditions ?? '',
                            $ticket->status ? '1' : '0'
                        ];
                        $data[] = $row;
                        $isFirstRowForAttraction = false;
                    }
                }
                
                // Then add an empty row for this attraction so users can add new tickets
                $emptyRow = [
                    $isFirstRowForAttraction ? ($attraction->name ?? '') : '',
                    $isFirstRowForAttraction ? ($attraction->country ?? '') : '',
                    $isFirstRowForAttraction ? ($attraction->location ?? '') : '',
                    '', // Empty ticket name - user can add new ticket
                    '', '', '', '', '', '', // Empty prices
                    '', // Empty important notes
                    '', // Empty terms & conditions
                    '1' // Default active status
                ];
                $data[] = $emptyRow;
            }
        } else {
            // No attractions found, add sample data
            $sampleData = [
                'Sample Museum',
                'United States',
                'New York',
                'Adult Entry Ticket',
                '10.00',
                '25.00',
                '20.00',
                '15.00',
                '35.00',
                '30.00',
                'Please arrive 15 minutes before scheduled time',
                'No refunds after booking confirmation',
                '1'
            ];
            $data[] = $sampleData;

            // Add another sample ticket for the same attraction (empty attraction details)
            $sampleData2 = [
                '', // Empty attraction name
                '', // Empty country
                '', // Empty city
                'VIP Entry Ticket',
                '20.00',
                '50.00',
                '40.00',
                '25.00',
                '60.00',
                '50.00',
                'Includes guided tour and priority access',
                'No refunds after booking confirmation',
                '1'
            ];
            $data[] = $sampleData2;
            
            // Add empty row for users to add more tickets to the same attraction
            $emptyRow = [
                '', // Empty attraction name
                '', // Empty country
                '', // Empty city
                '', // Empty ticket name - user can add new ticket
                '', '', '', '', '', '', // Empty prices
                '', // Empty important notes
                '', // Empty terms & conditions
                '1' // Default active status
            ];
            $data[] = $emptyRow;
            
            // Add sample for second attraction to show multiple attraction format
            $sampleData3 = [
                'Adventure Park',
                'Singapore', 
                'Singapore',
                'Standard Entry',
                '5.00',
                '15.00',
                '10.00',
                '8.00',
                '20.00',
                '15.00',
                'All safety equipment included',
                'Weather dependent. No refunds for cancellations',
                '1'
            ];
            $data[] = $sampleData3;
        }

        $content = $this->generateCsvContent($data);
        $filename = 'dmc_ticket_bulk_upload_template.csv';

        return Response::make($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // Helper method to generate CSV content
    private function generateCsvContent($data)
    {
        // Use a temporary file to properly format CSV
        $temp = fopen('php://temp', 'r+');
        
        foreach ($data as $row) {
            // Clean data to prevent line breaks and properly escape
            $cleanRow = [];
            foreach ($row as $field) {
                // Convert to string, remove line breaks, and trim
                $cleanField = str_replace(["\r", "\n", "\r\n"], ' ', (string)$field);
                $cleanRow[] = trim($cleanField);
            }
            fputcsv($temp, $cleanRow);
        }
        
        rewind($temp);
        $output = stream_get_contents($temp);
        fclose($temp);
        
        return $output;
    }

    // Helper method to read CSV file
    private function readCsvFile($filePath)
    {
        $data = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $data[] = $row;
            }
            fclose($handle);
        }
      
        return $data;
    }

    // Driver Upload Method
    public function uploadDrivers(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:10240',
            ]);

            $file = $request->file('file');
            $csvData = $this->readCsvFile($file->getPathname());
            
            if (empty($csvData)) {
                return redirect()->back()->with('error', 'The uploaded file is empty or invalid.');
            }

            array_shift($csvData); // Remove header
            
            $auth_user = Auth::user();
            
            // Define role groups for access control
            $dmcFullAccessRoles = [11, 35]; // DMC, Product Head (DMC)
            $dmcDriverRoles = [81, 124]; // Product Manager Driver (DMC), Assistant PM Driver (DMC)
            $travclicksFullAccessRoles = [1, 23, 20, 29]; // Travclicks, Product Head (Travclicks), Virtual DMC, Assistant Manager(PROD HEAD)
            $travclicksDriverRoles = [51, 125]; // Product Manager Driver (Travclicks), Assistant PM Driver (Travclicks)
            
            // Check if user has access
            $isDmcUser = in_array($auth_user->role_id, array_merge($dmcFullAccessRoles, $dmcDriverRoles));
            $isTravclicksUser = in_array($auth_user->role_id, array_merge($travclicksFullAccessRoles, $travclicksDriverRoles));
            
            if (!$isDmcUser && !$isTravclicksUser) {
                return redirect()->back()->with('error', 'You do not have permission to upload drivers.');
            }
            
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            
            DB::beginTransaction();
            
            foreach ($csvData as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2;
                
                try {
                    if (empty(array_filter($row))) continue;
                    
                    // Map to new column structure: Salutation*, Driver Gender*, Name*, Email*, Phone No*, Address*, Country*, City*, License No*, License Expiry Date*, Driver Age*, Profile Image*, Status*
                    $salutation = trim($row[0] ?? '');
                    $driverGender = trim($row[1] ?? '');
                    $driverName = trim($row[2] ?? '');
                    $email = trim($row[3] ?? '');
                    $phoneNo = trim($row[4] ?? '');
                    $address = trim($row[5] ?? '');
                    $country = trim($row[6] ?? '');
                    $city = trim($row[7] ?? '');
                    $licenseNo = trim($row[8] ?? '');
                    $licenseExpiryDate = trim($row[9] ?? '');
                    $driverAge = trim($row[10] ?? '');
                    $profileImage = trim($row[11] ?? '');
                    $status = trim($row[12] ?? '1');
                    
                    // Validate required fields
                    if (empty($driverGender) || empty($driverName) || empty($email) || empty($phoneNo) || 
                        empty($address) || empty($country) || empty($city) || empty($licenseNo) || 
                        empty($licenseExpiryDate) || empty($driverAge) || empty($profileImage)) {
                        $errors[] = "Row {$rowNumber}: Missing required fields";
                        $errorCount++;
                        continue;
                    }
                    
                    // Check for duplicate email
                    if (Driver::where('email', $email)->exists()) {
                        $errors[] = "Row {$rowNumber}: Email already exists";
                        $errorCount++;
                        continue;
                    }
                    
                    // Check for duplicate license number if the column exists
                    if (Schema::hasColumn('drivers', 'license_no') && Driver::where('license_no', $licenseNo)->exists()) {
                        $errors[] = "Row {$rowNumber}: License number already exists";
                        $errorCount++;
                        continue;
                    }
                    
                    // Generate driver ID
                    $lastDriver = Driver::withTrashed()->orderBy('created_at', 'desc')->first();
                    $driver_max_id = $lastDriver->driver_id ?? 0;
                    $driverId = \App\Helpers\CommonHelper::createId($driver_max_id);
                    while (Driver::where('driver_id', $driverId)->exists()) {
                        $driverId = \App\Helpers\CommonHelper::createId($driverId);
                    }
                    
                    // Split name into first and last
                    $nameParts = explode(' ', $driverName, 2);
                    $firstName = $nameParts[0];
                    $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
                    
                    // Create driver record
                    $driver = new Driver();
                    $driver->driver_id = $driverId;
                    $driver->first_name = $firstName;
                    $driver->last_name = $lastName;
                    $driver->email = $email;
                    $driver->mobile_number = $phoneNo;
                    $driver->address = $address;
                    $driver->country = $country;
                    $driver->city = $city;
                    $driver->is_active = ($status == '1') ? 1 : 0;
                    
                    // Map additional fields if they exist in the database
                    if (Schema::hasColumn('drivers', 'salutation')) {
                        $driver->salutation = $salutation;
                    }
                    if (Schema::hasColumn('drivers', 'driver_gender')) {
                        $driver->driver_gender = $driverGender;
                    }
                    if (Schema::hasColumn('drivers', 'license_no')) {
                        $driver->license_no = $licenseNo;
                    }
                    if (Schema::hasColumn('drivers', 'license_expiry_date')) {
                        $driver->license_expiry_date = $licenseExpiryDate;
                    }
                    if (Schema::hasColumn('drivers', 'driver_age')) {
                        $driver->driver_age = is_numeric($driverAge) ? intval($driverAge) : null;
                    }
                    if (Schema::hasColumn('drivers', 'profile_image')) {
                        $driver->profile_image = $profileImage;
                    }
                    if (Schema::hasColumn('drivers', 'dmc_id')) {
                    $driver->dmc_id = $auth_user->userId;
                    }
                    if (Schema::hasColumn('drivers', 'created_by')) {
                    $driver->created_by = $auth_user->userId;
                    }
                    if (Schema::hasColumn('drivers', 'status')) {
                        $driver->status = ($status == '1') ? 1 : 0;
                    }
                    
                    // Handle required fields from migration that may not be in CSV
                    if (Schema::hasColumn('drivers', 'password')) {
                        $driver->password = bcrypt('default123'); // Default password
                    }
                    if (Schema::hasColumn('drivers', 'activation_in')) {
                        $driver->activation_in = now();
                    }
                    if (Schema::hasColumn('drivers', 'vehicle_plate_no')) {
                        $driver->vehicle_plate_no = 'TBD'; // To be determined
                    }
                    if (Schema::hasColumn('drivers', 'vehicle_model')) {
                        $driver->vehicle_model = 'TBD';
                    }
                    if (Schema::hasColumn('drivers', 'model_year')) {
                        $driver->model_year = date('Y');
                    }
                    if (Schema::hasColumn('drivers', 'vehicle_id')) {
                        $driver->vehicle_id = 1; // Default to first vehicle
                    }
                    if (Schema::hasColumn('drivers', 'sharable')) {
                        $driver->sharable = 0;
                    }
                    if (Schema::hasColumn('drivers', 'night_time')) {
                        $driver->night_time = '22:00:00';
                    }
                    if (Schema::hasColumn('drivers', 'operational_country_id')) {
                        $driver->operational_country_id = 1; // Default
                    }
                    if (Schema::hasColumn('drivers', 'bank_account_holder_name')) {
                        $driver->bank_account_holder_name = $driverName;
                    }
                    if (Schema::hasColumn('drivers', 'account_number')) {
                        $driver->account_number = 'TBD';
                    }
                    if (Schema::hasColumn('drivers', 'state')) {
                        $driver->state = $city; // Default state to city
                    }
                    
                    $driver->save();
                    $successCount++;
                    
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                    $errorCount++;
                }
            }
            
            DB::commit();
            
            $message = "Upload completed. {$successCount} drivers processed successfully.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} errors occurred.";
            }
            
            return redirect()->back()
                ->with('success', $message)
                ->with('errors', $errors);
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    // Guide Upload Method
    public function uploadGuides(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB limit
            ]);

            $file = $request->file('file');
            $csvData = $this->readCsvFile($file->getPathname());
            
            if (empty($csvData)) {
                return redirect()->back()->with('error', 'The uploaded file is empty or invalid.');
            }

            // Remove header row
            array_shift($csvData);
            
            $auth_user = Auth::user();
            
            // Define role groups for access control
            $dmcFullAccessRoles = [11, 35]; // DMC, Product Head (DMC)
            $dmcGuideRoles = [79, 121]; // Product Manager Guide (DMC), Assistant PM Guide (DMC)
            $travclicksFullAccessRoles = [1, 23, 20, 29]; // Travclicks, Product Head (Travclicks), Virtual DMC, Assistant Manager(PROD HEAD)
            $travclicksGuideRoles = [49, 119]; // Product Manager Guide (Travclicks), Assistant PM Guide (Travclicks)
            
            // Check if user has access and determine format
            $isDmcUser = in_array($auth_user->role_id, array_merge($dmcFullAccessRoles, $dmcGuideRoles));
            $isTravclicksUser = in_array($auth_user->role_id, array_merge($travclicksFullAccessRoles, $travclicksGuideRoles));
            
            if (!$isDmcUser && !$isTravclicksUser) {
                return redirect()->back()->with('error', 'You do not have permission to upload guides.');
            }

            if ($isDmcUser || ($auth_user->role_id == 20)) { // DMC users or Virtual DMC
                return $this->uploadDmcGuides($csvData, $auth_user);
            } else { // Travclicks users
                return $this->uploadTravclicksGuides($csvData, $auth_user);
            }
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Guide bulk upload failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    // Vehicle Upload Method
    public function uploadVehicles(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:10240',
            ]);

            $file = $request->file('file');
            $csvData = $this->readCsvFile($file->getPathname());
            
            if (empty($csvData)) {
                return redirect()->back()->with('error', 'The uploaded file is empty or invalid.');
            }

            array_shift($csvData); // Remove header
            
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            
            DB::beginTransaction();
            $auth_user = Auth::user();
            
            foreach ($csvData as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2;
                
                try {
                    if (empty(array_filter($row))) continue;
                    
                    $vehicleName = trim($row[0] ?? '');
                    $vehicleType = trim($row[1] ?? '');
                    $licensePlate = trim($row[2] ?? '');
                    $capacity = trim($row[3] ?? '');
                    $city = trim($row[4] ?? '');
                    $country = trim($row[5] ?? '');
                    $dailyRate = trim($row[6] ?? '');
                    $status = trim($row[7] ?? '1');
                    
                    if (empty($vehicleName) || empty($vehicleType) || empty($licensePlate) || empty($capacity)) {
                        $errors[] = "Row {$rowNumber}: Missing required fields";
                        $errorCount++;
                        continue;
                    }
                    
                    // Check for duplicate license plate
                    if (Vehicle::where('license_plate', $licensePlate)->exists()) {
                        $errors[] = "Row {$rowNumber}: License plate already exists";
                        $errorCount++;
                        continue;
                    }
                    
                    $lastVehicle = Vehicle::withTrashed()->orderBy('created_at', 'desc')->first();
                    $vehicle_max_id = $lastVehicle->vehicle_id ?? 0;
                    $vehicleId = \App\Helpers\CommonHelper::createId($vehicle_max_id);
                    while (Vehicle::where('vehicle_id', $vehicleId)->exists()) {
                        $vehicleId = \App\Helpers\CommonHelper::createId($vehicleId);
                    }
                    
                    $vehicle = new Vehicle();
                    $vehicle->vehicle_id = $vehicleId;
                    $vehicle->vehicle_name = $vehicleName;
                    $vehicle->vehicle_type = $vehicleType;
                    $vehicle->license_plate = $licensePlate;
                    $vehicle->seating_capacity = is_numeric($capacity) ? intval($capacity) : 0;
                    $vehicle->city = $city;
                    $vehicle->country = $country;
                    $vehicle->daily_rate = is_numeric($dailyRate) ? floatval($dailyRate) : 0;
                    $vehicle->is_available = ($status == '1') ? 1 : 0;
                    $vehicle->status = 1;
                    $vehicle->dmc_id = $auth_user->userId;
                    $vehicle->created_by = $auth_user->userId;
                    
                    $vehicle->save();
                    $successCount++;
                    
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                    $errorCount++;
                }
            }
            
            DB::commit();
            
            $message = "Upload completed. {$successCount} vehicles processed successfully.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} errors occurred.";
            }
            
            return redirect()->back()
                ->with('success', $message)
                ->with('errors', $errors);
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    // Attraction Upload Method
    public function uploadAttractions(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:10240',
            ]);

            $file = $request->file('file');
            $auth_user = Auth::user();
            
            // Generate file hash to prevent duplicate uploads
            $fileHash = hash_file('md5', $file->getPathname());
            $cacheKey = "attraction_upload_{$fileHash}_{$auth_user->userId}";
            
            // Check if this exact file was uploaded recently (within last 60 seconds)
            if (cache()->has($cacheKey)) {
                return redirect()->back()->with('error', 'This file was already uploaded recently. Please wait a moment before uploading again.');
            }
            
            // Mark this upload as in progress
            cache()->put($cacheKey, true, 60); // Cache for 60 seconds
            
            $csvData = $this->readCsvFile($file->getPathname());
            
            if (empty($csvData)) {
                return redirect()->back()->with('error', 'The uploaded file is empty or invalid.');
            }

            // Remove header row
            array_shift($csvData);
            
            // Filter out empty rows to prevent double processing
            $csvData = array_filter($csvData, function($row) {
                return !empty(array_filter($row, function($cell) {
                    return !empty(trim($cell));
                }));
            });
            
            // Re-index the array after filtering
            $csvData = array_values($csvData);
            

            
            // Define role groups for access control
            $dmcFullAccessRoles = [11, 35]; // DMC, Product Head (DMC)
            $dmcAttractionRoles = [80, 122]; // Product Manager Attraction (DMC), Assistant PM Attraction (DMC)
            $travclicksFullAccessRoles = [1, 23, 20, 29]; // Travclicks, Product Head (Travclicks), Virtual DMC, Assistant Manager(PROD HEAD)
            $travclicksAttractionRoles = [50, 123]; // Product Manager Attraction (Travclicks), Assistant PM Attraction (Travclicks)
            
            // Check if user has access
            $isDmcUser = in_array($auth_user->role_id, array_merge($dmcFullAccessRoles, $dmcAttractionRoles));
            $isTravclicksUser = in_array($auth_user->role_id, array_merge($travclicksFullAccessRoles, $travclicksAttractionRoles));
            
            if (!$isDmcUser && !$isTravclicksUser) {
                return redirect()->back()->with('error', 'You do not have permission to upload attractions.');
            }
            
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            
            // Track attractions being processed in this upload to prevent duplicates within the same CSV
            $processedAttractions = [];
            
            DB::beginTransaction();
            foreach ($csvData as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2; // +2 because we removed header and rows start at 1
                
                try {
                    // Double-check for empty rows (shouldn't be needed now, but just in case)
                    if (empty(array_filter($row, function($cell) { return !empty(trim($cell)); }))) {
                        continue;
                    }
                    
                    // Map CSV columns to variables
                    $attractionName = trim($row[0] ?? '');
                    $country = trim($row[1] ?? '');
                    $city = trim($row[2] ?? '');
                    $seniorAgeThreshold = trim($row[3] ?? '');
                    $maxChildAge = trim($row[4] ?? '');
                    $latitude = trim($row[5] ?? '');
                    $longitude = trim($row[6] ?? '');
                    $morningOpening = trim($row[7] ?? '');
                    $afternoonOpening = trim($row[8] ?? '');
                    $eveningOpening = trim($row[9] ?? '');
                    $nightOpening = trim($row[10] ?? '');
                    $openTime = trim($row[11] ?? '');
                    $closeTime = trim($row[12] ?? '');
                    $masterImage = trim($row[13] ?? '');
                    $additionalImages = trim($row[14] ?? '');
                    $importantNotes = trim($row[15] ?? '');
                    $termsConditions = trim($row[16] ?? '');
                    $status = trim($row[17] ?? '1');
                    
                    // Validate required fields
                    if (empty($attractionName) || empty($country) || empty($city) || empty($seniorAgeThreshold) || 
                        empty($maxChildAge) || empty($latitude) || empty($longitude) || empty($openTime) || 
                        empty($closeTime) || empty($masterImage) || empty($importantNotes) || empty($termsConditions)) {
                        $errors[] = "Row {$rowNumber}: Missing required fields";
                        $errorCount++;
                        continue;
                    }
                    
                    // Create unique key for this attraction
                    $attractionKey = strtolower($attractionName . '|' . $country . '|' . $city);
                    
                    // Check for duplicate within this CSV upload first
                    if (isset($processedAttractions[$attractionKey])) {
                        $errors[] = "Row {$rowNumber}: Duplicate attraction '{$attractionName}' in {$city}, {$country} found in this CSV (previously at row {$processedAttractions[$attractionKey]})";
                        $errorCount++;
                        continue;
                    }
                    
                    // Check for duplicate attraction in database
                    $existingAttraction = Attraction::where('name', $attractionName)
                                                  ->where('dmc_id', $auth_user->userId)
                                                  ->first();
                    
                    if ($existingAttraction) {
                        $errors[] = "Row {$rowNumber}: Attraction '{$attractionName}' already exists for your account";
                        $errorCount++;
                        continue;
                    }
                    
                    // Mark this attraction as being processed
                    $processedAttractions[$attractionKey] = $rowNumber;
                    
                    // Generate unique attraction ID
                    $lastAttraction = Attraction::withTrashed()->orderBy('attraction_id', 'desc')->first();
                    $attraction_max_id = $lastAttraction->attraction_id ?? 0;
                  
                    // Generate new ID with retry logic
                    $maxRetries = 10;
                    $retryCount = 0;
                    do {
                        $attractionId = \App\Helpers\CommonHelper::createId($attraction_max_id + $retryCount);
                        $retryCount++;
                    } while (Attraction::where('attraction_id', $attractionId)->exists() && $retryCount < $maxRetries);
                    
                    if ($retryCount >= $maxRetries) {
                        $errors[] = "Row {$rowNumber}: Could not generate unique attraction ID";
                        $errorCount++;
                        continue;
                    }
                    
                    // Process additional images (comma-separated to JSON array)
                    $additionalImagesArray = [];
                    if (!empty($additionalImages)) {
                        $additionalImagesArray = array_map('trim', explode(',', $additionalImages));
                    }
                    
                    // Use Important Notes as description (no additional data appended)
                    $description = $importantNotes;
                  
                    // Create attraction record
                    $attraction = new Attraction();
                    $attraction->attraction_id = $attractionId;
                    $attraction->name = $attractionName;
                    $attraction->description = $description;
                    $attraction->master_image = $masterImage;
                    $attraction->additional_image = json_encode($additionalImagesArray);
                    $attraction->open_time = $openTime;
                    $attraction->close_time = $closeTime;
                    
                    // Map additional fields if they exist in the database
                    if (Schema::hasColumn('attractions', 'location')) {
                        $attraction->location = $city;
                    }
                    if (Schema::hasColumn('attractions', 'country')) {
                        $attraction->country = $country;
                    }
                    if (Schema::hasColumn('attractions', 'latitude')) {
                        $attraction->latitude = is_numeric($latitude) ? floatval($latitude) : null;
                    }
                    if (Schema::hasColumn('attractions', 'longitude')) {
                        $attraction->longitude = is_numeric($longitude) ? floatval($longitude) : null;
                    }
                    if (Schema::hasColumn('attractions', 'senior_min_age')) {
                        $attraction->senior_min_age = is_numeric($seniorAgeThreshold) ? intval($seniorAgeThreshold) : null;
                    }
                    if (Schema::hasColumn('attractions', 'child_max_age')) {
                        $attraction->child_max_age = is_numeric($maxChildAge) ? intval($maxChildAge) : null;
                    }
                    if (Schema::hasColumn('attractions', 'morning_opening')) {
                        $attraction->morning_opening = ($morningOpening == '1') ? 1 : 0;
                    }
                    if (Schema::hasColumn('attractions', 'afternoon_opening')) {
                        $attraction->afternoon_opening = ($afternoonOpening == '1') ? 1 : 0;
                    }
                    if (Schema::hasColumn('attractions', 'evening_opening')) {
                        $attraction->evening_opening = ($eveningOpening == '1') ? 1 : 0;
                    }
                    if (Schema::hasColumn('attractions', 'night_opening')) {
                        $attraction->night_opening = ($nightOpening == '1') ? 1 : 0;
                    }
                    if (Schema::hasColumn('attractions', 'terms_conditions')) {
                        $attraction->terms_conditions = $termsConditions;
                    }
                    if (Schema::hasColumn('attractions', 'status')) {
                        $attraction->status = ($status == '1') ? 1 : 0;
                    }
                    
                    // Set dmc_id - Virtual DMC (role_id = 20) assigns to their own userId like DMC users
                    if (Schema::hasColumn('attractions', 'dmc_id')) {
                        $attraction->dmc_id = $auth_user->userId;
                    }
                    if (Schema::hasColumn('attractions', 'created_by')) {
                        $attraction->created_by = $auth_user->userId;
                    }
                    
                    $attraction->save();
                    $successCount++;
                    
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                    $errorCount++;
                    Log::error("Attraction bulk upload error on row {$rowNumber}: " . $e->getMessage());
                }
            }
            
                        if ($successCount > 0) {
                DB::commit();
            } else {
                DB::rollback();
            }
            
            // Clear the upload cache on completion
            cache()->forget($cacheKey);
            
            // Save upload history
            UploadHistory::createRecord(
                'attractions',
                $file->getClientOriginalName(),
                $file->getClientOriginalName(),
                count($csvData),
                $successCount,
                $errorCount,
                $errors,
                $auth_user->userId
            );
            
            $message = "Upload completed. {$successCount} attractions processed successfully.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} errors occurred.";
            }

            return redirect()->back()
                ->with('success', $message)
                ->with('errors', $errors);
                
        } catch (\Exception $e) {
            DB::rollback();
            // Clear the upload cache on error
            if (isset($cacheKey)) {
                cache()->forget($cacheKey);
            }
            Log::error('Attraction bulk upload failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    // Ticket Upload Method - Only for DMC users (role_id = 11)
    public function uploadTickets(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:10240',
            ]);

            $file = $request->file('file');
            $auth_user = Auth::user();
            
            // Check for DMC role - role_id is stored as string "11"
            if (!$auth_user || $auth_user->role_id !== '11') {
                return redirect()->back()->with('error', 'Access denied. Only DMC users can upload tickets.');
            }
            
            // Generate file hash to prevent duplicate uploads
            $fileHash = hash_file('md5', $file->getPathname());
            $cacheKey = "ticket_upload_{$fileHash}_{$auth_user->userId}";
            
            // Check if this exact file was uploaded recently (within last 30 seconds)
            if (cache()->has($cacheKey)) {
                return redirect()->back()->with('error', 'This file was already uploaded recently. Please wait a moment before uploading again.');
            }
            
            $csvData = $this->readCsvFile($file->getPathname());
            
            if (empty($csvData)) {
                return redirect()->back()->with('error', 'The uploaded file is empty or invalid.');
            }

            // Remove header row
            array_shift($csvData);
            
            // Filter out empty rows to prevent double processing
            $csvData = array_filter($csvData, function($row) {
                return !empty(array_filter($row, function($cell) {
                    return !empty(trim($cell));
                }));
            });
            
            // Re-index the array after filtering
            $csvData = array_values($csvData);
            
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            $currentAttraction = null; // Track current attraction context
            
            DB::beginTransaction();
            
            foreach ($csvData as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2; // +2 because we removed header and rows start at 1
                
                try {
                    // Map CSV columns to variables
                    $attractionName = trim($row[0] ?? '');
                    $country = trim($row[1] ?? '');
                    $city = trim($row[2] ?? '');
                    $ticketName = trim($row[3] ?? '');
                    $childPrice = trim($row[4] ?? '0');
                    $adultPrice = trim($row[5] ?? '0');
                    $seniorAdultPrice = trim($row[6] ?? '0');
                    $childPriceNri = trim($row[7] ?? '0');
                    $adultPriceNri = trim($row[8] ?? '0');
                    $seniorAdultPriceNri = trim($row[9] ?? '0');
                    $description = trim($row[10] ?? '');
                    $termsConditions = trim($row[11] ?? '');
                    $status = trim($row[12] ?? '1');
                    
                    // Check if this row defines a new attraction context
                    if (!empty($attractionName) && !empty($country) && !empty($city)) {
                        // Find the attraction for this DMC user
                        $attraction = Attraction::where('name', $attractionName)
                                              ->where('country', $country)
                                              ->where('location', $city)
                                              ->where('dmc_id', $auth_user->userId)
                                              ->first();
                        
                        if (!$attraction) {
                            $errors[] = "Row {$rowNumber}: Attraction '{$attractionName}' not found in {$city}, {$country} for your account";
                            $errorCount++;
                            continue;
                        }
                        
                        $currentAttraction = $attraction;
                    }
                    
                    // Validate that we have an attraction context
                    if (!$currentAttraction) {
                        $errors[] = "Row {$rowNumber}: No attraction context found. Please ensure attraction details are provided first.";
                        $errorCount++;
                        continue;
                    }
                    
                    // Skip rows that don't have ticket information (empty ticket name)
                    if (empty($ticketName)) {
                        continue;
                    }
                    
                    // Validate required ticket fields
                    $missingFields = [];
                    if (empty($ticketName)) $missingFields[] = 'Ticket Name';
                    if (empty($childPrice) || $childPrice === '0') $missingFields[] = 'Child Price';
                    if (empty($adultPrice) || $adultPrice === '0') $missingFields[] = 'Adult Price';
                    if (empty($seniorAdultPrice) || $seniorAdultPrice === '0') $missingFields[] = 'Senior Citizen Price';
                    if (empty($childPriceNri) || $childPriceNri === '0') $missingFields[] = 'Child Price NRI';
                    if (empty($adultPriceNri) || $adultPriceNri === '0') $missingFields[] = 'Adult Price NRI';
                    if (empty($seniorAdultPriceNri) || $seniorAdultPriceNri === '0') $missingFields[] = 'Senior Citizen Price NRI';
                    if (empty($description)) $missingFields[] = 'Important Notes';
                    if (empty($termsConditions)) $missingFields[] = 'Terms & Conditions';
                    
                    if (!empty($missingFields)) {
                        $errors[] = "Row {$rowNumber}: Missing required fields: " . implode(', ', $missingFields);
                        $errorCount++;
                        continue;
                    }

                    // Validate numeric fields and ensure they are greater than 0
                    $numericFields = [
                        'Child Price' => $childPrice,
                        'Adult Price' => $adultPrice,
                        'Senior Citizen Price' => $seniorAdultPrice,
                        'Child Price NRI' => $childPriceNri,
                        'Adult Price NRI' => $adultPriceNri,
                        'Senior Citizen Price NRI' => $seniorAdultPriceNri
                    ];

                    foreach ($numericFields as $fieldName => $value) {
                        if (!empty($value) && !is_numeric($value)) {
                            $errors[] = "Row {$rowNumber}: {$fieldName} must be a valid number. Found: '{$value}'";
                            $errorCount++;
                            continue 2; // Skip to next row
                        }
                        if (!empty($value) && is_numeric($value) && floatval($value) <= 0) {
                            $errors[] = "Row {$rowNumber}: {$fieldName} must be greater than 0. Found: '{$value}'";
                            $errorCount++;
                            continue 2; // Skip to next row
                        }
                    }

                    // Validate status field
                    if (!in_array($status, ['0', '1'])) {
                        $errors[] = "Row {$rowNumber}: Status must be 0 (inactive) or 1 (active). Found: '{$status}'";
                        $errorCount++;
                        continue;
                    }

                    // Validate ticket name length
                    if (strlen($ticketName) > 255) {
                        $errors[] = "Row {$rowNumber}: Ticket name is too long (maximum 255 characters)";
                        $errorCount++;
                        continue;
                    }
                    
                    // Check for duplicate ticket name for this attraction
                    $existingTicket = \App\Models\Ticket::where('name', $ticketName)
                                                       ->where('attraction_id', $currentAttraction->attraction_id)
                                                       ->first();
                    
                    if ($existingTicket) {
                        $errors[] = "Row {$rowNumber}: Ticket '{$ticketName}' already exists for attraction '{$currentAttraction->name}'";
                        $errorCount++;
                        continue;
                    }
                    
                    // Generate unique ticket ID - get the maximum ticket_id and increment
                    $maxTicketId = \App\Models\Ticket::withTrashed()->max('ticket_id');
                    
                    // Ensure it's at least 8 digits
                    if (!$maxTicketId || $maxTicketId < 10000000) {
                        $ticketMaxId = 10000000;
                    } else {
                        $ticketMaxId = $maxTicketId + 1;
                    }
                    
                    // Double-check for uniqueness (in case of concurrent uploads)
                    while (\App\Models\Ticket::withTrashed()->where('ticket_id', $ticketMaxId)->exists()) {
                        $ticketMaxId++;
                    }
                    
                    // Create ticket record
                    $ticket = new \App\Models\Ticket();
                    $ticket->ticket_id = $ticketMaxId;
                    $ticket->name = $ticketName;
                    $ticket->description = $description;
                    $ticket->terms_conditions = $termsConditions;
                    $ticket->child_price = is_numeric($childPrice) ? floatval($childPrice) : 0;
                    $ticket->adult_price = is_numeric($adultPrice) ? floatval($adultPrice) : 0;
                    $ticket->senior_adult_price = is_numeric($seniorAdultPrice) ? floatval($seniorAdultPrice) : 0;
                    $ticket->child_price_nri = is_numeric($childPriceNri) ? floatval($childPriceNri) : 0;
                    $ticket->adult_price_nri = is_numeric($adultPriceNri) ? floatval($adultPriceNri) : 0;
                    $ticket->senior_adult_price_nri = is_numeric($seniorAdultPriceNri) ? floatval($seniorAdultPriceNri) : 0;
                    $ticket->status = ($status == '1') ? 1 : 0;
                    $ticket->attraction_id = $currentAttraction->attraction_id;
                    $ticket->dmc_id = $auth_user->userId; // Store DMC's userId
                    $ticket->created_by = $auth_user->userId;
                    
                    $ticket->save();
                    $successCount++;
                    
                    Log::info("SUCCESS: Created ticket '{$ticketName}' for attraction '{$currentAttraction->name}' with ID {$ticket->ticket_id}");
                    
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                    $errorCount++;
                    Log::error("Ticket bulk upload error on row {$rowNumber}: " . $e->getMessage());
                }
            }
            
            if ($successCount > 0) {
                DB::commit();
                // Cache file hash only after successful processing
                cache()->put($cacheKey, true, 30); // Cache for 30 seconds
            } else {
                DB::rollback();
            }
            
            // Save upload history
            UploadHistory::createRecord(
                'tickets',
                $file->getClientOriginalName(),
                $file->getClientOriginalName(),
                count($csvData),
                $successCount,
                $errorCount,
                $errors,
                $auth_user->userId
            );
            
            $message = "Upload completed. {$successCount} tickets processed successfully.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} errors occurred.";
            }

            return redirect()->back()
                ->with('success', $message)
                ->with('errors', $errors);
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Ticket bulk upload failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    // Get upload history for display
    public function getUploadHistory($uploadType)
    {
        $auth_user = Auth::user();
        return UploadHistory::getRecentHistory($uploadType, $auth_user->userId, 10);
    }

    // Attraction-specific ticket bulk upload page
    public function attractionTickets($attraction_id)
    {
        $auth_user = Auth::user();
        
        // Check for DMC role - role_id is stored as string "11"
        if (!$auth_user || $auth_user->role_id !== '11') {
            abort(403, 'Access denied. Only DMC users can access ticket bulk upload.');
        }

        // Get the attraction
        $attraction = Attraction::where('attraction_id', $attraction_id)->first();
        if (!$attraction) {
            return redirect()->back()->with('error', 'Attraction not found.');
        }

        // Get upload history for this attraction
        $uploadHistory = UploadHistory::where('upload_type', 'attraction_tickets')
                                    ->where('uploaded_by', $auth_user->userId)
                                    ->orderBy('created_at', 'desc')
                                    ->limit(10)
                                    ->get();
        
        return view('bulk-upload.attraction-tickets', compact('attraction', 'uploadHistory'));
    }

    // Download template for specific attraction
    public function downloadAttractionTicketTemplate($attraction_id)
    {
        $auth_user = Auth::user();
        
        // Check for DMC role
        if (!$auth_user || $auth_user->role_id !== '11') {
            abort(403, 'Access denied. Only DMC users can download ticket templates.');
        }

        // Get the attraction
        $attraction = Attraction::where('attraction_id', $attraction_id)->first();
        if (!$attraction) {
            abort(404, 'Attraction not found.');
        }

        $data = $this->generateAttractionTicketCsvData($attraction);
        $content = $this->generateCsvContent($data);
        $filename = 'tickets_template_' . str_replace(' ', '_', strtolower($attraction->name)) . '.csv';

        return Response::make($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // Generate CSV template data for attraction-specific ticket upload
    private function generateAttractionTicketCsvData($attraction)
    {
        $header = [
            'Ticket Name (Required)',
            'Child Price(local) (Required)',
            'Adult Price(local) (Required)',
            'Senior Citizen Price(local) (Required)',
            'Child Price(foreigner) (Required)',
            'Adult Price(foreigner) (Required)',
            'Senior Citizen Price(foreigner) (Required)',
            'Important Notes (Required)',
            'Terms & Conditions (Required)',
            'Status (1=Active, 0=Inactive)'
        ];

        $data = [$header];

        // Add sample data with proper required field values
        $sampleData = [
            'Standard Entry Ticket',
            '15.00',  // Child Price (required)
            '25.00',  // Adult Price (required)
            '20.00',  // Senior Citizen Price (required)
            '20.00',  // Child Price NRI (required)
            '35.00',  // Adult Price NRI (required)
            '30.00',  // Senior Citizen Price NRI (required)
            'Valid for one day entry. Please bring valid ID.',
            'No refund after booking. Entry subject to availability.',
            '1'
        ];
        $data[] = $sampleData;

        // Add another sample
        $sampleData2 = [
            'VIP Entry Ticket',
            '25.00',  // Child Price (required)
            '50.00',  // Adult Price (required)
            '40.00',  // Senior Citizen Price (required)
            '30.00',  // Child Price NRI (required)
            '60.00',  // Adult Price NRI (required)
            '50.00',  // Senior Citizen Price NRI (required)
            'Includes priority access and guided tour.',
            'Advance booking required. No cancellation allowed.',
            '1'
        ];
        $data[] = $sampleData2;

        // Add empty row for user input (with notes for required fields)
        $emptyRow = ['[Required]', '[Required]', '[Required]', '[Required]', '[Required]', '[Required]', '[Required]', '[Required]', '[Required]', '1'];
        $data[] = $emptyRow;

        return $data;
    }

    // Upload tickets for specific attraction
    public function uploadAttractionTickets(Request $request, $attraction_id)
    {
        $auth_user = Auth::user();
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        try {
            // Enhanced validation
            $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:10240',
            ], [
                'file.required' => 'Please select a file to upload.',
                'file.mimes' => 'Only CSV and TXT files are allowed.',
                'file.max' => 'File size should not exceed 10MB.'
            ]);

            $file = $request->file('file');
            
            // Check for DMC role
            if (!$auth_user || $auth_user->role_id !== '11') {
                return redirect()->back()->with('error', 'Access denied. Only DMC users can upload tickets.');
            }

            // Get the attraction
            $attraction = Attraction::where('attraction_id', $attraction_id)->first();
            if (!$attraction) {
                return redirect()->back()->with('error', 'Attraction not found.');
            }

            // Check if attraction belongs to this DMC
            if ($attraction->dmc_id != $auth_user->userId) {
                return redirect()->back()->with('error', 'You can only upload tickets for your own attractions.');
            }

            // Check if file was uploaded successfully
            if (!$file->isValid()) {
                return redirect()->back()->with('error', 'File upload failed. Please try again.');
            }

            // Check file size
            if ($file->getSize() == 0) {
                return redirect()->back()->with('error', 'The uploaded file is empty.');
            }
            
            // Read CSV file with enhanced error handling
            try {
                $csvData = $this->readCsvFile($file->getPathname());
            } catch (\Exception $e) {
                Log::error('CSV file reading failed: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Failed to read CSV file. Please ensure the file is properly formatted.');
            }
            
            if (empty($csvData)) {
                return redirect()->back()->with('error', 'The uploaded file is empty or contains no valid data.');
            }

            // Check if CSV has header row
            if (count($csvData) < 2) {
                return redirect()->back()->with('error', 'The CSV file must contain at least a header row and one data row.');
            }

            // Validate CSV structure
            $expectedColumns = 10; // Based on template
            $headerRow = $csvData[0];
            if (count($headerRow) < $expectedColumns) {
                return redirect()->back()->with('error', "Invalid CSV format. Expected at least {$expectedColumns} columns, found " . count($headerRow) . ".");
            }

            // Remove header row
            array_shift($csvData);
            
            // Filter out empty rows more thoroughly
            $csvData = array_filter($csvData, function($row) {
                // Check if all cells are empty or whitespace
                $nonEmptyCells = array_filter($row, function($cell) {
                    return !empty(trim($cell ?? ''));
                });
                return count($nonEmptyCells) > 0;
            });
            
            $csvData = array_values($csvData);
            
            if (empty($csvData)) {
                return redirect()->back()->with('error', 'No valid data rows found in the CSV file.');
            }

            // Limit number of rows to prevent timeout
            if (count($csvData) > 1000) {
                return redirect()->back()->with('error', 'Maximum 1000 rows allowed per upload. Your file contains ' . count($csvData) . ' rows.');
            }
            
            DB::beginTransaction();
            
            foreach ($csvData as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2; // +2 because we removed header and array is 0-indexed
                
                try {
                    // Ensure row has enough columns
                    if (count($row) < $expectedColumns) {
                        $errors[] = "Row {$rowNumber}: Insufficient columns. Expected {$expectedColumns}, found " . count($row);
                        $errorCount++;
                        continue;
                    }

                    // Map CSV columns to variables with better null handling
                    $ticketName = trim($row[0] ?? '');
                    $childPrice = trim($row[1] ?? '0');
                    $adultPrice = trim($row[2] ?? '0');
                    $seniorAdultPrice = trim($row[3] ?? '0');
                    $childPriceNri = trim($row[4] ?? '0');
                    $adultPriceNri = trim($row[5] ?? '0');
                    $seniorAdultPriceNri = trim($row[6] ?? '0');
                    $description = trim($row[7] ?? '');
                    $termsConditions = trim($row[8] ?? '');
                    $status = trim($row[9] ?? '1');
                    
                    // Enhanced validation for required fields
                    $missingFields = [];
                    if (empty($ticketName)) $missingFields[] = 'Ticket Name';
                    if (empty($childPrice) || $childPrice === '0') $missingFields[] = 'Child Price';
                    if (empty($adultPrice) || $adultPrice === '0') $missingFields[] = 'Adult Price';
                    if (empty($seniorAdultPrice) || $seniorAdultPrice === '0') $missingFields[] = 'Senior Citizen Price';
                    if (empty($childPriceNri) || $childPriceNri === '0') $missingFields[] = 'Child Price NRI';
                    if (empty($adultPriceNri) || $adultPriceNri === '0') $missingFields[] = 'Adult Price NRI';
                    if (empty($seniorAdultPriceNri) || $seniorAdultPriceNri === '0') $missingFields[] = 'Senior Citizen Price NRI';
                    if (empty($description)) $missingFields[] = 'Important Notes';
                    if (empty($termsConditions)) $missingFields[] = 'Terms & Conditions';
                    
                    if (!empty($missingFields)) {
                        $errors[] = "Row {$rowNumber}: Missing required fields: " . implode(', ', $missingFields);
                        $errorCount++;
                        continue;
                    }

                    // Validate numeric fields and ensure they are greater than 0
                    $numericFields = [
                        'Child Price' => $childPrice,
                        'Adult Price' => $adultPrice,
                        'Senior Citizen Price' => $seniorAdultPrice,
                        'Child Price NRI' => $childPriceNri,
                        'Adult Price NRI' => $adultPriceNri,
                        'Senior Citizen Price NRI' => $seniorAdultPriceNri
                    ];

                    foreach ($numericFields as $fieldName => $value) {
                        if (!empty($value) && !is_numeric($value)) {
                            $errors[] = "Row {$rowNumber}: {$fieldName} must be a valid number. Found: '{$value}'";
                            $errorCount++;
                            continue 2; // Skip to next row
                        }
                        if (!empty($value) && is_numeric($value) && floatval($value) <= 0) {
                            $errors[] = "Row {$rowNumber}: {$fieldName} must be greater than 0. Found: '{$value}'";
                            $errorCount++;
                            continue 2; // Skip to next row
                        }
                    }

                    // Validate status field
                    if (!in_array($status, ['0', '1'])) {
                        $errors[] = "Row {$rowNumber}: Status must be 0 (inactive) or 1 (active). Found: '{$status}'";
                        $errorCount++;
                        continue;
                    }

                    // Validate ticket name length
                    if (strlen($ticketName) > 255) {
                        $errors[] = "Row {$rowNumber}: Ticket name is too long (maximum 255 characters)";
                        $errorCount++;
                        continue;
                    }
                    
                    // Check for duplicate ticket name for this attraction
                    try {
                        $existingTicket = \App\Models\Ticket::where('name', $ticketName)
                                                           ->where('attraction_id', $attraction->attraction_id)
                                                           ->first();
                        
                        if ($existingTicket) {
                            $errors[] = "Row {$rowNumber}: Ticket '{$ticketName}' already exists for this attraction";
                            $errorCount++;
                            continue;
                        }
                    } catch (\Exception $e) {
                        Log::error("Database check error for row {$rowNumber}: " . $e->getMessage());
                        $errors[] = "Row {$rowNumber}: Database error while checking for duplicates";
                        $errorCount++;
                        continue;
                    }
                    
                    // Generate unique ticket ID with better error handling
                    try {
                        $maxTicketId = \App\Models\Ticket::withTrashed()->max('ticket_id');
                        
                        if (!$maxTicketId || $maxTicketId < 10000000) {
                            $ticketMaxId = 10000000;
                        } else {
                            $ticketMaxId = $maxTicketId + 1;
                        }
                        
                        // Double-check for uniqueness with limit to prevent infinite loop
                        $attempts = 0;
                        while (\App\Models\Ticket::withTrashed()->where('ticket_id', $ticketMaxId)->exists() && $attempts < 100) {
                            $ticketMaxId++;
                            $attempts++;
                        }

                        if ($attempts >= 100) {
                            throw new \Exception("Unable to generate unique ticket ID after 100 attempts");
                        }
                    } catch (\Exception $e) {
                        Log::error("Ticket ID generation error for row {$rowNumber}: " . $e->getMessage());
                        $errors[] = "Row {$rowNumber}: Error generating ticket ID";
                        $errorCount++;
                        continue;
                    }
                    
                    // Create ticket record with enhanced error handling
                    try {
                        $ticket = new \App\Models\Ticket();
                        $ticket->ticket_id = $ticketMaxId;
                        $ticket->name = $ticketName;
                        $ticket->description = $description;
                        $ticket->terms_conditions = $termsConditions;
                        $ticket->child_price = is_numeric($childPrice) ? floatval($childPrice) : 0;
                        $ticket->adult_price = is_numeric($adultPrice) ? floatval($adultPrice) : 0;
                        $ticket->senior_adult_price = is_numeric($seniorAdultPrice) ? floatval($seniorAdultPrice) : 0;
                        $ticket->child_price_nri = is_numeric($childPriceNri) ? floatval($childPriceNri) : 0;
                        $ticket->adult_price_nri = is_numeric($adultPriceNri) ? floatval($adultPriceNri) : 0;
                        $ticket->senior_adult_price_nri = is_numeric($seniorAdultPriceNri) ? floatval($seniorAdultPriceNri) : 0;
                        $ticket->status = ($status == '1') ? 1 : 0;
                        $ticket->attraction_id = $attraction->attraction_id;
                        $ticket->dmc_id = $auth_user->userId;
                        $ticket->created_by = $auth_user->userId;
                        
                        $ticket->save();
                        $successCount++;
                    } catch (\Exception $e) {
                        Log::error("Ticket save error for row {$rowNumber}: " . $e->getMessage());
                        $errors[] = "Row {$rowNumber}: Error saving ticket - " . $e->getMessage();
                        $errorCount++;
                    }
                    
                } catch (\Exception $e) {
                    $errors[] = "Row {$rowNumber}: Unexpected error - " . $e->getMessage();
                    $errorCount++;
                    Log::error("Attraction ticket bulk upload error on row {$rowNumber}: " . $e->getMessage());
                }
            }
            
            // Commit transaction only if we have successes
            if ($successCount > 0) {
                DB::commit();
            } else {
                DB::rollback();
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            Log::error('Validation error in attraction ticket upload: ' . json_encode($e->errors()));
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Attraction ticket bulk upload failed with exception: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()->with('error', 'Upload failed due to an unexpected error. Please check your file format and try again.');
        }

        // Save upload history regardless of success/failure
        try {
            UploadHistory::createRecord(
                'attraction_tickets',
                $file->getClientOriginalName(),
                $file->getClientOriginalName(),
                count($csvData ?? []),
                $successCount,
                $errorCount,
                $errors,
                $auth_user->userId
            );
        } catch (\Exception $e) {
            Log::error('Failed to save upload history: ' . $e->getMessage());
        }
        
        // Generate user-friendly messages
        if ($successCount > 0 && $errorCount == 0) {
            $message = "Success! {$successCount} tickets uploaded successfully for {$attraction->name}.";
            return redirect()->back()->with('success', $message);
        } elseif ($successCount > 0 && $errorCount > 0) {
            $message = "Partial success: {$successCount} tickets uploaded successfully, {$errorCount} failed for {$attraction->name}.";
            
            // Create error bag for Laravel
            $validator = Validator::make([], []);
            foreach ($errors as $error) {
                $validator->errors()->add('upload', $error);
            }
            
            return redirect()->back()
                ->with('success', $message)
                ->withErrors($validator);
        } else {
            $message = "Upload failed: {$errorCount} errors occurred. No tickets were uploaded.";
            
            // Create error bag for Laravel  
            $validator = Validator::make([], []);
            foreach ($errors as $error) {
                $validator->errors()->add('upload', $error);
            }
            
            return redirect()->back()
                ->with('error', $message)
                ->withErrors($validator);
        }
    }

    // Meal Upload Methods for DMC Users
    public function meals()
    {
        //dd(1);
        $auth_user = Auth::user();

        if (!$auth_user || $auth_user->role_id !== '11') {
            abort(403, 'Only DMC users can access meal bulk upload.');
        }
        
        // Get restaurants that belong to this DMC - Simple approach for debugging
        try {
            // First, let's see what columns exist
            $testRestaurant = Restaurant::first();
            if ($testRestaurant) {
                Log::info('Restaurant columns available: ' . json_encode(array_keys($testRestaurant->getAttributes())));
            }
            
            // Try to get restaurants for this DMC
            $restaurants = Restaurant::where('dmc_id', $auth_user->userId)->where('status', 1)->get();
            // If no restaurants found, try getting all restaurants for testing
            if ($restaurants->isEmpty()) {
                Log::info('No restaurants found for DMC user: ' . $auth_user->userId);
                $restaurants = Restaurant::take(3)->get(); // Get first 3 for testing
            }
            
        } catch (\Exception $e) {
            Log::error('Error in meals method: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Database error: ' . $e->getMessage());
        }
        return view('bulk-upload.meals', compact('restaurants'));
    }

    public function downloadMealTemplate($restaurant_id)
    {
        $auth_user = Auth::user();
        
        // Only DMC (role_id = 11) can download meal templates
        if ($auth_user->role_id != '11') {
            abort(403, 'Only DMC users can download meal templates.');
        }
        
        // Verify restaurant belongs to this DMC
        $restaurant = Restaurant::where('restaurant_id', $restaurant_id)
                                ->where('dmc_id', $auth_user->userId)
                                ->where('status', 1)
                                ->first();
        
        if (!$restaurant) {
            abort(404, 'Restaurant not found or access denied.');
        }
        
        return $this->generateDmcMealTemplate($restaurant);
    }

    private function generateDmcMealTemplate($restaurant)
    {
        $headers = [
            'Meal Type*',
            'Beverage*',
            'Meals*',
            'Item Price',
            'Item Type',
            'Adult Price',
            'Child Price',
            'Item Description*'
        ];

        $data = [$headers];

        // Get existing meals for this restaurant
        $meals = Meal::where('restaurant_id', $restaurant->restaurant_id)->get();

        if ($meals->count() > 0) {
            foreach ($meals as $meal) {
                $row = [
                    // Meal Type (using meal_period column)
                    match($meal->meal_period ?? 1) {
                        1 => 'Breakfast',
                        2 => 'Lunch', 
                        3 => 'Dinner',
                        default => 'Breakfast'
                    },
                    
                    // Beverage (using category column)
                    match($meal->category ?? 2) {
                        1 => 'Alcoholic',
                        2 => 'Non Alcoholic',
                        3 => 'No Beverage',
                        default => 'Non Alcoholic'
                    },
                    
                    // Meals Type (using type column)
                    ($meal->type == 1) ? 'Buffet' : 'Set Menu',
                    
                    // Item Price (only for Set Menu - type = 2)
                    ($meal->type == 2) ? ($meal->price ?? '') : '',
                    
                    // Item Type (only for Set Menu - type = 2, empty for Buffet)
                    ($meal->type == 2) ? (match($meal->item_type ?? null) {
                        1 => 'Vegetarian',
                        2 => 'Non Vegetarian',
                        default => ''
                    }) : '',
                    
                    // Adult Price (only for Buffet - type = 1)
                    ($meal->type == 1) ? ($meal->adult_price ?? '') : '',
                    
                    // Child Price (only for Buffet - type = 1)
                    ($meal->type == 1) ? ($meal->child_price ?? '') : '',
                    
                    // Item Description
                    $meal->item_description ?? ''
                ];
                
                $data[] = $row;
            }
        } else {
            // No existing meals, add sample data based on restaurant's meal availability
            if (isset($restaurant->breakfast_available) && $restaurant->breakfast_available) {
                $sampleBreakfast = [
                    'Breakfast',
                    'Non Alcoholic',
                    'Buffet',
                    '', // No item price for Buffet
                    '', // No item type for Buffet
                    '25.00', // Adult price for Buffet
                    '12.50', // Child price for Buffet
                    'Continental breakfast with fresh fruits and pastries'
                ];
                $data[] = $sampleBreakfast;
            }
            
            if (isset($restaurant->lunch_available) && $restaurant->lunch_available) {
                $sampleLunch = [
                    'Lunch',
                    'Non Alcoholic',
                    'Set Menu',
                    '18.50', // Item price for Set Menu
                    'Non Vegetarian', // Item type for Set Menu
                    '', // No adult price for Set Menu
                    '', // No child price for Set Menu
                    'Authentic local cuisine lunch special'
                ];
                $data[] = $sampleLunch;
            }
            
            if (isset($restaurant->dinner_available) && $restaurant->dinner_available) {
                $sampleDinner = [
                    'Dinner',
                    'Alcoholic',
                    'Set Menu',
                    '35.00', // Item price for Set Menu
                    'Non Vegetarian', // Item type for Set Menu
                    '', // No adult price for Set Menu
                    '', // No child price for Set Menu
                    'Premium dinner with wine pairing'
                ];
                $data[] = $sampleDinner;
            }
            
            // If no meal types are available, add one example of each meal type
            if (!isset($restaurant->breakfast_available) && !isset($restaurant->lunch_available) && !isset($restaurant->dinner_available)) {
                // Add Buffet example
                $buffetExample = [
                    'Breakfast',
                    'Non Alcoholic',
                    'Buffet',
                    '', // No item price for Buffet
                    '', // No item type for Buffet  
                    '25.00', // Adult price required for Buffet
                    '12.50', // Child price required for Buffet
                    'Sample buffet breakfast description'
                ];
                $data[] = $buffetExample;
                
                // Add Set Menu example
                $setMenuExample = [
                    'Lunch',
                    'Non Alcoholic', 
                    'Set Menu',
                    '18.50', // Item price required for Set Menu
                    'Vegetarian', // Item type required for Set Menu
                    '', // No adult price for Set Menu
                    '', // No child price for Set Menu
                    'Sample set menu lunch description'
                ];
                $data[] = $setMenuExample;
            }
        }

        $content = $this->generateCsvContent($data);
        $filename = 'meal_bulk_upload_template_' . $restaurant->restaurant_id . '.csv';

        return Response::make($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }



    public function uploadMeals(Request $request, $restaurant_id)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB limit
            ]);

            $auth_user = Auth::user();
            
            // Only DMC (role_id = 11) can upload meals
            if ($auth_user->role_id != 11) {
                return redirect()->back()->with('error', 'Only DMC users can upload meals.');
            }
            
            // Verify restaurant belongs to this DMC
            $restaurant = Restaurant::where('restaurant_id', $restaurant_id)
                                    ->where('dmc_id', $auth_user->userId)
                                    ->where('status', 1)
                                    ->first();
            
            if (!$restaurant) {
                return redirect()->back()->with('error', 'Restaurant not found or access denied.');
            }

            $file = $request->file('file');
            $csvData = $this->readCsvFile($file->getPathname());
            
            if (empty($csvData)) {
                return redirect()->back()->with('error', 'The uploaded file is empty or invalid.');
            }

            // Remove header row
            array_shift($csvData);

            return $this->processMealUpload($csvData, $restaurant, $auth_user);
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Meal bulk upload failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    private function processMealUpload($csvData, $restaurant, $auth_user)
    {
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        DB::beginTransaction();
        
        foreach ($csvData as $rowIndex => $row) {
            $rowNumber = $rowIndex + 2; // +2 because we removed header and rows start at 1
            
            try {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                // Map CSV columns to variables
                $mealType = trim($row[0] ?? '');
                $beverage = trim($row[1] ?? '');
                $mealsType = trim($row[2] ?? '');
                $itemPrice = trim($row[3] ?? '');
                $itemType = trim($row[4] ?? '');
                $adultPrice = trim($row[5] ?? '');
                $childPrice = trim($row[6] ?? '');
                $itemDescription = trim($row[7] ?? '');
                
                // Validate required fields
                if (empty($mealType) || empty($beverage) || empty($mealsType) || empty($itemDescription)) {
                    $errors[] = "Row {$rowNumber}: Missing required fields (Meal Type, Beverage, Meals Type, or Item Description)";
                    $errorCount++;
                    continue;
                }
                
                // Validate meal type availability for restaurant
                $mealTypeNum = match(strtolower($mealType)) {
                    'breakfast' => 1,
                    'lunch' => 2,
                    'dinner' => 3,
                    default => null
                };
                
                if ($mealTypeNum === null) {
                    $errors[] = "Row {$rowNumber}: Invalid meal type. Must be Breakfast, Lunch, or Dinner";
                    $errorCount++;
                    continue;
                }
                
                // Check if meal type is available for this restaurant
                $availabilityField = match($mealTypeNum) {
                    1 => 'breakfast_available',
                    2 => 'lunch_available',
                    3 => 'dinner_available'
                };
                
                if (!$restaurant->$availabilityField) {
                    $errors[] = "Row {$rowNumber}: {$mealType} is not available for this restaurant";
                    $errorCount++;
                    continue;
                }
                
                // Validate beverage type
                if (!in_array(strtolower($beverage), ['alcoholic', 'non alcoholic', 'no beverage'])) {
                    $errors[] = "Row {$rowNumber}: Invalid beverage type. Must be Alcoholic, Non Alcoholic, or No Beverage";
                    $errorCount++;
                    continue;
                }
                
                // Validate meals type and required fields
                $mealsTypeNum = match(strtolower($mealsType)) {
                    'buffet' => 1,
                    'set menu' => 2,
                    default => null
                };
                
                if ($mealsTypeNum === null) {
                    $errors[] = "Row {$rowNumber}: Invalid meals type. Must be Buffet or Set Menu";
                    $errorCount++;
                    continue;
                }
                
                // Validate pricing and dependencies based on meal type
                if ($mealsTypeNum == 1) { // Buffet
                    // For Buffet: Adult Price and Child Price are required, Item Price and Item Type should be empty
                    if (empty($adultPrice) || empty($childPrice)) {
                        $errors[] = "Row {$rowNumber}: Buffet requires Adult Price and Child Price";
                        $errorCount++;
                        continue;
                    }
                    if (!is_numeric($adultPrice) || !is_numeric($childPrice)) {
                        $errors[] = "Row {$rowNumber}: Adult Price and Child Price must be numeric for Buffet";
                        $errorCount++;
                        continue;
                    }
                    // Check if unwanted fields are filled for Buffet
                    if (!empty($itemPrice)) {
                        $errors[] = "Row {$rowNumber}: Item Price should be empty for Buffet meals";
                        $errorCount++;
                        continue;
                    }
                    if (!empty($itemType)) {
                        $errors[] = "Row {$rowNumber}: Item Type should be empty for Buffet meals";
                        $errorCount++;
                        continue;
                    }
                } else { // Set Menu
                    // For Set Menu: Item Price and Item Type are required, Adult Price and Child Price should be empty
                    if (empty($itemPrice)) {
                        $errors[] = "Row {$rowNumber}: Set Menu requires Item Price";
                        $errorCount++;
                        continue;
                    }
                    if (!is_numeric($itemPrice)) {
                        $errors[] = "Row {$rowNumber}: Item Price must be numeric for Set Menu";
                        $errorCount++;
                        continue;
                    }
                    if (empty($itemType)) {
                        $errors[] = "Row {$rowNumber}: Set Menu requires Item Type (Vegetarian or Non Vegetarian)";
                        $errorCount++;
                        continue;
                    }
                    if (!in_array(strtolower($itemType), ['vegetarian', 'non vegetarian'])) {
                        $errors[] = "Row {$rowNumber}: Invalid item type. Must be Vegetarian or Non Vegetarian";
                        $errorCount++;
                        continue;
                    }
                    // Check if unwanted fields are filled for Set Menu
                    if (!empty($adultPrice)) {
                        $errors[] = "Row {$rowNumber}: Adult Price should be empty for Set Menu meals";
                        $errorCount++;
                        continue;
                    }
                    if (!empty($childPrice)) {
                        $errors[] = "Row {$rowNumber}: Child Price should be empty for Set Menu meals";
                        $errorCount++;
                        continue;
                    }
                }
                
                // Create meal using existing createMeal method
                $this->createMeal(
                    $restaurant, 
                    $mealType, 
                    $beverage, 
                    $mealsType, 
                    '', // itemName - not used in new format
                    $itemPrice, 
                    $itemType, 
                    $adultPrice, 
                    $childPrice, 
                    $itemDescription, 
                    '1', // mealStatus - active by default
                    $auth_user->userId
                );
                
                $successCount++;
                
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                $errorCount++;
                Log::error("Meal bulk upload error on row {$rowNumber}: " . $e->getMessage());
            }
        }
        
        DB::commit();
        
        // Store upload history using the createRecord method
        try {
            UploadHistory::createRecord(
                'meals',
                request()->file('file')->getClientOriginalName(),
                request()->file('file')->getClientOriginalName(),
                count($csvData),
                $successCount,
                $errorCount,
                $errors,
                $auth_user->userId
            );
        } catch (\Exception $e) {
            Log::error('Failed to save upload history: ' . $e->getMessage());
        }
        
        // Enhanced success message with restaurant name and meal details
        $restaurantName = $restaurant->name;
        $message = "🍽️ **Meal Upload Complete for {$restaurantName}!**\n\n";
        $message .= "✅ **{$successCount} meals** successfully added to your restaurant menu";
        
        if ($errorCount > 0) {
            $message .= "\n⚠️ **{$errorCount} records** failed to upload";
        }
        
        $message .= "\n📍 Restaurant: **{$restaurantName}**";
        $message .= "\n📊 Total processed: **" . ($successCount + $errorCount) . " records**";
        
        if ($successCount > 0) {
            // Add meal type breakdown
            $mealTypes = [];
            foreach ($csvData as $row) {
                if (!empty($row[0])) {
                    $mealType = trim($row[0]);
                    if (!isset($mealTypes[$mealType])) {
                        $mealTypes[$mealType] = 0;
                    }
                    $mealTypes[$mealType]++;
                }
            }
            
            if (!empty($mealTypes)) {
                $message .= "\n🍴 **Meal types added:**";
                foreach ($mealTypes as $type => $count) {
                    $message .= "\n   • {$type}: {$count} items";
                }
            }
        }
        
        return redirect()->back()
            ->with('success', $message)
            ->with('errors', $errors);
    }
}