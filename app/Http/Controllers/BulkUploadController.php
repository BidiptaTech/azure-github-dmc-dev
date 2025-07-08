<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Models\Hotel;
use App\Models\Driver;
use App\Models\Guide;
use App\Models\Restaurant;
use App\Models\Meal;
use App\Models\Vehicle;
use App\Models\Attraction;
use App\Models\Country;
use App\Models\City;
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
        
        // Define role groups for restaurant bulk upload access
        $dmcFullAccessRoles = [11, 35]; // DMC, Product Head (DMC)
        $dmcRestaurantRoles = [78, 120]; // Product Manager Restaurant (DMC), Assistant PM Restaurant (DMC)
        $travclicksFullAccessRoles = [1, 23, 20, 29]; // Travclicks, Product Head (Travclicks), Virtual DMC, Assistant Manager(PROD HEAD)
        $travclicksRestaurantRoles = [48, 118]; // Product Manager Restaurant (Travclicks), Assistant PM Restaurant (Travclicks)
        
        // Check if user has access to restaurant bulk upload
        $hasAccess = in_array($auth_user->role_id, array_merge($dmcFullAccessRoles, $dmcRestaurantRoles, $travclicksFullAccessRoles, $travclicksRestaurantRoles));
        
        if (!$hasAccess) {
            abort(403, 'You do not have permission to access restaurant bulk upload.');
        }
        
        return view('bulk-upload.restaurants');
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
        
        return view('bulk-upload.attractions');
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

        // Define role groups
        $dmcFullAccessRoles = [11, 35]; // DMC, Product Head (DMC)
        $dmcRestaurantRoles = [78, 120]; // Product Manager Restaurant (DMC), Assistant PM Restaurant (DMC)
        $travclicksFullAccessRoles = [1, 23, 20, 29]; // Travclicks, Product Head (Travclicks), Virtual DMC, Assistant Manager(PROD HEAD)
        $travclicksRestaurantRoles = [48, 118]; // Product Manager Restaurant (Travclicks), Assistant PM Restaurant (Travclicks)

        // Check if user is DMC or Travclicks
        $isDmcUser = in_array($auth_user->role_id, array_merge($dmcFullAccessRoles, $dmcRestaurantRoles));
        $isTravclicksUser = in_array($auth_user->role_id, array_merge($travclicksFullAccessRoles, $travclicksRestaurantRoles));

        if ($isDmcUser || ($auth_user->role_id == 20)) { // DMC users or Virtual DMC
            return $this->downloadDmcRestaurantTemplate($auth_user);
        } elseif ($isTravclicksUser) { // Travclicks users
            return $this->downloadTravclicksRestaurantTemplate($auth_user);
        } else {
            abort(403, 'You do not have permission to download restaurant templates.');
        }
    }

    private function downloadDmcRestaurantTemplate($auth_user)
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
            'Item Description*',
            'Meal Status (1=Active, 0=Inactive)'
        ];

        $data = [$headers];

        // Get restaurants based on user role
        $restaurants = Restaurant::where('dmc_id', $auth_user->userId)
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
                    // Restaurant without meals - add restaurant row with sample meal
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
                        '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
                    ];
                    $data[] = $row;
                }
            }
        } else {
            // No existing restaurants, add sample data for DMC format
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

            $data[] = $sampleData1;
            $data[] = $sampleData2;
        }

        $content = $this->generateCsvContent($data);
        $filename = 'dmc_restaurant_bulk_upload_template.csv';

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
            
            // Define role groups for access control
            $dmcFullAccessRoles = [11, 35]; // DMC, Product Head (DMC)
            $dmcRestaurantRoles = [78, 120]; // Product Manager Restaurant (DMC), Assistant PM Restaurant (DMC)
            $travclicksFullAccessRoles = [1, 23, 20, 29]; // Travclicks, Product Head (Travclicks), Virtual DMC, Assistant Manager(PROD HEAD)
            $travclicksRestaurantRoles = [48, 118]; // Product Manager Restaurant (Travclicks), Assistant PM Restaurant (Travclicks)
            
            // Check if user has access and determine format
            $isDmcUser = in_array($auth_user->role_id, array_merge($dmcFullAccessRoles, $dmcRestaurantRoles));
            $isTravclicksUser = in_array($auth_user->role_id, array_merge($travclicksFullAccessRoles, $travclicksRestaurantRoles));
            
            if (!$isDmcUser && !$isTravclicksUser) {
                return redirect()->back()->with('error', 'You do not have permission to upload restaurants.');
            }

            if ($isDmcUser || ($auth_user->role_id == 20)) { // DMC users or Virtual DMC
                return $this->uploadDmcRestaurants($csvData, $auth_user);
            } else { // Travclicks users
                return $this->uploadTravclicksRestaurants($csvData, $auth_user);
            }
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Restaurant bulk upload failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    private function uploadDmcRestaurants($csvData, $auth_user)
    {
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        DB::beginTransaction();
        
        $currentRestaurant = null;
        
        foreach ($csvData as $rowIndex => $row) {
            $rowNumber = $rowIndex + 2; // +2 because we removed header and rows start at 1
            
            try {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                // Map CSV columns to variables
                $restaurantName = trim($row[0] ?? '');
                $country = trim($row[1] ?? '');
                $city = trim($row[2] ?? '');
                $latitude = trim($row[3] ?? '');
                $longitude = trim($row[4] ?? '');
                $cuisine = trim($row[5] ?? '');
                $ownership = trim($row[6] ?? '');
                $property = trim($row[7] ?? '');
                $breakfastAvailability = trim($row[8] ?? '0');
                $lunchAvailability = trim($row[9] ?? '0');
                $dinnerAvailability = trim($row[10] ?? '0');
                $breakfastOpenTime = trim($row[11] ?? '');
                $breakfastCloseTime = trim($row[12] ?? '');
                $lunchOpenTime = trim($row[13] ?? '');
                $lunchCloseTime = trim($row[14] ?? '');
                $dinnerOpenTime = trim($row[15] ?? '');
                $dinnerCloseTime = trim($row[16] ?? '');
                $masterImage = trim($row[17] ?? '');
                $additionalImage = trim($row[18] ?? '');
                $description = trim($row[19] ?? '');
                $termsCondition = trim($row[20] ?? '');
                
                // Meal data starts from column 21
                $mealType = trim($row[21] ?? '');
                $beverage = trim($row[22] ?? '');
                $mealsType = trim($row[23] ?? '');
                $itemName = trim($row[24] ?? '');
                $itemPrice = trim($row[25] ?? '');
                $itemType = trim($row[26] ?? '');
                $adultPrice = trim($row[27] ?? '');
                $childPrice = trim($row[28] ?? '');
                $itemDescription = trim($row[29] ?? '');
                
                // Validate required meal fields
                if (empty($mealType) || empty($beverage) || empty($mealsType) || empty($itemDescription)) {
                    $errors[] = "Row {$rowNumber}: Missing required meal fields (Meal Type, Beverage, Meals, or Item Description)";
                    $errorCount++;
                    continue;
                }
                
                // Process restaurant (only if restaurant name is provided)
                if (!empty($restaurantName)) {
                    // Validate required restaurant fields
                    if (empty($country) || empty($city) || empty($cuisine)) {
                        $errors[] = "Row {$rowNumber}: Missing required restaurant fields (Country, City, or Cuisine)";
                        $errorCount++;
                        continue;
                    }
                    
                    // Create new restaurant
                    $lastRestaurant = Restaurant::withTrashed()->orderBy('created_at', 'desc')->first();
                    $restaurant_max_id = $lastRestaurant->restaurant_id ?? 0;
                    $restaurantId = \App\Helpers\CommonHelper::createId($restaurant_max_id);
                    while (Restaurant::where('restaurant_id', $restaurantId)->exists()) {
                        $restaurantId = \App\Helpers\CommonHelper::createId($restaurantId);
                    }
                    
                    $restaurant = new Restaurant();
                    $restaurant->restaurant_id = $restaurantId;
                    $restaurant->name = $restaurantName;
                    $restaurant->country = $country;
                    $restaurant->city = $city;
                    $restaurant->latitude = is_numeric($latitude) ? floatval($latitude) : null;
                    $restaurant->longitude = is_numeric($longitude) ? floatval($longitude) : null;
                    $restaurant->cuisine = $cuisine;
                    $restaurant->ownership = $ownership;
                    $restaurant->property = $property;
                    $restaurant->breakfast_availability = ($breakfastAvailability == '1') ? 1 : 0;
                    $restaurant->lunch_availability = ($lunchAvailability == '1') ? 1 : 0;
                    $restaurant->dinner_availability = ($dinnerAvailability == '1') ? 1 : 0;
                    
                    // Set time fields based on availability
                    if ($restaurant->breakfast_availability && !empty($breakfastOpenTime) && !empty($breakfastCloseTime)) {
                        $restaurant->breakfast_open_time = $breakfastOpenTime;
                        $restaurant->breakfast_close_time = $breakfastCloseTime;
                    }
                    if ($restaurant->lunch_availability && !empty($lunchOpenTime) && !empty($lunchCloseTime)) {
                        $restaurant->lunch_open_time = $lunchOpenTime;
                        $restaurant->lunch_close_time = $lunchCloseTime;
                    }
                    if ($restaurant->dinner_availability && !empty($dinnerOpenTime) && !empty($dinnerCloseTime)) {
                        $restaurant->dinner_open_time = $dinnerOpenTime;
                        $restaurant->dinner_close_time = $dinnerCloseTime;
                    }
                    
                    $restaurant->master_image = $masterImage;
                    $restaurant->additional_image = $additionalImage;
                    $restaurant->description = $description;
                    $restaurant->terms_condition = $termsCondition;
                    $restaurant->is_active = 1;
                    $restaurant->status = 1; // Default approved status
                    $restaurant->dmc_id = $auth_user->userId; // DMC users assign to their own DMC
                    $restaurant->created_by = $auth_user->userId;
                    
                    $restaurant->save();
                    $currentRestaurant = $restaurant;
                }
                
                // Ensure we have a current restaurant to add meals to
                if (!$currentRestaurant) {
                    $errors[] = "Row {$rowNumber}: No restaurant context for meal. Ensure restaurant details are provided first.";
                    $errorCount++;
                    continue;
                }
                
                // Create meal
                $this->createMeal($currentRestaurant, $mealType, $beverage, $mealsType, $itemName, $itemPrice, $itemType, $adultPrice, $childPrice, $itemDescription, '1', $auth_user->userId);
                
                $successCount++;
                
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                $errorCount++;
                Log::error("Restaurant bulk upload error on row {$rowNumber}: " . $e->getMessage());
            }
        }
        
        DB::commit();
        
        $message = "Upload completed. {$successCount} meals processed successfully.";
        if ($errorCount > 0) {
            $message .= " {$errorCount} errors occurred.";
        }
        
        return redirect()->back()
            ->with('success', $message)
            ->with('errors', $errors);
    }

    private function uploadTravclicksRestaurants($csvData, $auth_user)
    {
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        DB::beginTransaction();
        
        $currentRestaurant = null;
        
        foreach ($csvData as $rowIndex => $row) {
            $rowNumber = $rowIndex + 2; // +2 because we removed header and rows start at 1
            
            try {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                // Map CSV columns to variables - same as DMC format for Travclicks
                $restaurantName = trim($row[0] ?? '');
                $country = trim($row[1] ?? '');
                $city = trim($row[2] ?? '');
                $latitude = trim($row[3] ?? '');
                $longitude = trim($row[4] ?? '');
                $cuisine = trim($row[5] ?? '');
                $ownership = trim($row[6] ?? '');
                $property = trim($row[7] ?? '');
                $breakfastAvailability = trim($row[8] ?? '0');
                $lunchAvailability = trim($row[9] ?? '0');
                $dinnerAvailability = trim($row[10] ?? '0');
                $breakfastOpenTime = trim($row[11] ?? '');
                $breakfastCloseTime = trim($row[12] ?? '');
                $lunchOpenTime = trim($row[13] ?? '');
                $lunchCloseTime = trim($row[14] ?? '');
                $dinnerOpenTime = trim($row[15] ?? '');
                $dinnerCloseTime = trim($row[16] ?? '');
                $masterImage = trim($row[17] ?? '');
                $additionalImage = trim($row[18] ?? '');
                $description = trim($row[19] ?? '');
                $termsCondition = trim($row[20] ?? '');
                
                // Meal data starts from column 21
                $mealType = trim($row[21] ?? '');
                $beverage = trim($row[22] ?? '');
                $mealsType = trim($row[23] ?? '');
                $itemName = trim($row[24] ?? '');
                $itemPrice = trim($row[25] ?? '');
                $itemType = trim($row[26] ?? '');
                $adultPrice = trim($row[27] ?? '');
                $childPrice = trim($row[28] ?? '');
                $itemDescription = trim($row[29] ?? '');
                
                // Validate required meal fields
                if (empty($mealType) || empty($beverage) || empty($mealsType) || empty($itemDescription)) {
                    $errors[] = "Row {$rowNumber}: Missing required meal fields (Meal Type, Beverage, Meals, or Item Description)";
                    $errorCount++;
                    continue;
                }
                
                // Process restaurant (only if restaurant name is provided)
                if (!empty($restaurantName)) {
                    // Validate required restaurant fields
                    if (empty($country) || empty($city) || empty($cuisine)) {
                        $errors[] = "Row {$rowNumber}: Missing required restaurant fields (Country, City, or Cuisine)";
                    $errorCount++;
                    continue;
                }
                
                // Create new restaurant
                $lastRestaurant = Restaurant::withTrashed()->orderBy('created_at', 'desc')->first();
                $restaurant_max_id = $lastRestaurant->restaurant_id ?? 0;
                $restaurantId = \App\Helpers\CommonHelper::createId($restaurant_max_id);
                while (Restaurant::where('restaurant_id', $restaurantId)->exists()) {
                    $restaurantId = \App\Helpers\CommonHelper::createId($restaurantId);
                }
                
                $restaurant = new Restaurant();
                $restaurant->restaurant_id = $restaurantId;
                $restaurant->name = $restaurantName;
                    $restaurant->country = $country;
                    $restaurant->city = $city;
                    $restaurant->latitude = is_numeric($latitude) ? floatval($latitude) : null;
                    $restaurant->longitude = is_numeric($longitude) ? floatval($longitude) : null;
                    $restaurant->cuisine = $cuisine;
                    $restaurant->ownership = $ownership;
                    $restaurant->property = $property;
                    $restaurant->breakfast_availability = ($breakfastAvailability == '1') ? 1 : 0;
                    $restaurant->lunch_availability = ($lunchAvailability == '1') ? 1 : 0;
                    $restaurant->dinner_availability = ($dinnerAvailability == '1') ? 1 : 0;
                    
                    // Set time fields based on availability
                    if ($restaurant->breakfast_availability && !empty($breakfastOpenTime) && !empty($breakfastCloseTime)) {
                        $restaurant->breakfast_open_time = $breakfastOpenTime;
                        $restaurant->breakfast_close_time = $breakfastCloseTime;
                    }
                    if ($restaurant->lunch_availability && !empty($lunchOpenTime) && !empty($lunchCloseTime)) {
                        $restaurant->lunch_open_time = $lunchOpenTime;
                        $restaurant->lunch_close_time = $lunchCloseTime;
                    }
                    if ($restaurant->dinner_availability && !empty($dinnerOpenTime) && !empty($dinnerCloseTime)) {
                        $restaurant->dinner_open_time = $dinnerOpenTime;
                        $restaurant->dinner_close_time = $dinnerCloseTime;
                    }
                    
                    $restaurant->master_image = $masterImage;
                    $restaurant->additional_image = $additionalImage;
                    $restaurant->description = $description;
                    $restaurant->terms_condition = $termsCondition;
                    $restaurant->is_active = 1;
                $restaurant->status = 1; // Default approved status
                
                // Set dmc_id based on user role
                if ($auth_user->role_id == 20) {
                    // Virtual DMC - assign to their own DMC
                    $restaurant->dmc_id = $auth_user->userId;
                } else {
                    // Other Travclicks users - assign to their userId
                    $restaurant->dmc_id = $auth_user->userId;
                }
                
                $restaurant->created_by = $auth_user->userId;
                $restaurant->save();
                    $currentRestaurant = $restaurant;
                }
                
                // Ensure we have a current restaurant to add meals to
                if (!$currentRestaurant) {
                    $errors[] = "Row {$rowNumber}: No restaurant context for meal. Ensure restaurant details are provided first.";
                    $errorCount++;
                    continue;
                }
                
                // Create meal
                $this->createMeal($currentRestaurant, $mealType, $beverage, $mealsType, $itemName, $itemPrice, $itemType, $adultPrice, $childPrice, $itemDescription, '1', $auth_user->userId);
                
                $successCount++;
                
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
                $errorCount++;
                Log::error("Restaurant bulk upload error on row {$rowNumber}: " . $e->getMessage());
            }
        }
        
        DB::commit();
        
        $message = "Upload completed. {$successCount} meals processed successfully.";
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
        $meal->item_name = $itemName ?: 'Menu Item';
        $meal->item_description = $itemDescription;
        
        // Map meal type (breakfast/lunch/dinner)
        $meal->meal_type = match(strtolower($mealType)) {
            'breakfast' => 1,
            'lunch' => 2,
            'dinner' => 3,
            default => 1
        };
        
        // Map beverage
        $meal->beverage = match(strtolower($beverage)) {
            'alcoholic' => 1,
            'non alcoholic' => 2,
            'no beverage' => 3,
            default => 2
        };
        
        // Map meals (buffet/set menu)
        $meal->meals = match(strtolower($mealsType)) {
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
            $meal->item_price = is_numeric($itemPrice) ? floatval($itemPrice) : 0;
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
            'Important Notes*',
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
                    $attraction->important_notes ?? '',
                    $attraction->terms_conditions ?? '',
                    $attraction->is_active ? '1' : '0'
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
                    $attraction->important_notes ?? '',
                    $attraction->terms_conditions ?? '',
                    $attraction->is_active ? '1' : '0'
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
                    
                    // Map to new column structure
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
                    
                    // Generate attraction ID
                    $lastAttraction = Attraction::withTrashed()->orderBy('created_at', 'desc')->first();
                    $attraction_max_id = $lastAttraction->attraction_id ?? 0;
                    $attractionId = \App\Helpers\CommonHelper::createId($attraction_max_id);
                    while (Attraction::where('attraction_id', $attractionId)->exists()) {
                        $attractionId = \App\Helpers\CommonHelper::createId($attractionId);
                    }
                    
                    // Process additional images (comma-separated to JSON array)
                    $additionalImagesArray = [];
                    if (!empty($additionalImages)) {
                        $additionalImagesArray = array_map('trim', explode(',', $additionalImages));
                    }
                    
                    // Create description with additional info
                    $description = $importantNotes;
                    if (!empty($termsConditions)) {
                        $description .= "\n\nTerms & Conditions: " . $termsConditions;
                    }
                    
                    // Add opening schedule info to description
                    $openingSchedule = [];
                    if ($morningOpening == '1') $openingSchedule[] = 'Morning';
                    if ($afternoonOpening == '1') $openingSchedule[] = 'Afternoon';
                    if ($eveningOpening == '1') $openingSchedule[] = 'Evening';
                    if ($nightOpening == '1') $openingSchedule[] = 'Night';
                    
                    if (!empty($openingSchedule)) {
                        $description .= "\n\nOpening Schedule: " . implode(', ', $openingSchedule);
                    }
                    
                    $description .= "\n\nSenior Age Threshold: " . $seniorAgeThreshold;
                    $description .= "\nMaximum Child Age: " . $maxChildAge;
                    $description .= "\nLatitude: " . $latitude;
                    $description .= "\nLongitude: " . $longitude;
                    
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
                    if (Schema::hasColumn('attractions', 'city')) {
                    $attraction->city = $city;
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
                    if (Schema::hasColumn('attractions', 'senior_age_threshold')) {
                        $attraction->senior_age_threshold = is_numeric($seniorAgeThreshold) ? intval($seniorAgeThreshold) : null;
                    }
                    if (Schema::hasColumn('attractions', 'max_child_age')) {
                        $attraction->max_child_age = is_numeric($maxChildAge) ? intval($maxChildAge) : null;
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
                    if (Schema::hasColumn('attractions', 'important_notes')) {
                        $attraction->important_notes = $importantNotes;
                    }
                    if (Schema::hasColumn('attractions', 'terms_conditions')) {
                        $attraction->terms_conditions = $termsConditions;
                    }
                    if (Schema::hasColumn('attractions', 'is_active')) {
                    $attraction->is_active = ($status == '1') ? 1 : 0;
                    }
                    if (Schema::hasColumn('attractions', 'status')) {
                        $attraction->status = ($status == '1') ? 1 : 0;
                    }
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
                }
            }
            
            DB::commit();
            
            $message = "Upload completed. {$successCount} attractions processed successfully.";
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

    // Additional upload methods for other entities would follow similar patterns
    // You can implement uploadDrivers, uploadGuides, etc. following the same CSV pattern
} 