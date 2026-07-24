<?php
namespace App\Helpers;
use Illuminate\Support\Facades\Http;
use App\Models\Setting;
use App\Models\Order;
use App\Models\Tour;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Guide;
use App\Models\Hotel;
use App\Models\Bed;
use App\Models\Room;
use App\Models\Agent;
use App\Models\Attraction;
use App\Models\Restaurant;
use App\Models\OperationalCountry;
use App\Models\Agency;
use App\Models\Rate;
use App\Models\Jobsheet;
use App\Models\BankDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\AutomatedMail;
use App\Mail\DmcMail;
use App\Models\EmailsSetup;
use Illuminate\Support\Facades\Auth;
use App\Models\Country;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Config;
use Barryvdh\DomPDF\Facade\Pdf;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use League\Flysystem\Filesystem;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;

class CommonHelper
{
    /**
     * Safely normalize a JSON field into an array.
     *
     * @param mixed $value
     * @return array<mixed>
     */
    public static function normalizeJsonArray($value): array
    {
        if ($value === null) {
            return [];
        }
        if (is_array($value)) {
            return $value;
        }
        if (is_object($value)) {
            return (array) $value;
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    /**
     * Load package booking services JSON by booking_id (+ optional dmc scope).
     *
     * Returns normalized arrays for:
     * selected_hotels, selected_attractions, selected_restaurants, arrival_data, departure_data, travel_dates
     *
     * @param string $bookingId e.g. PB00239
     * @param int|string|null $packageId
     * @param int|string|null $dmcId
     * @return array<string, mixed>
     */
    public static function getPackageBookingServices(string $bookingId, $packageId = null, $dmcId = null): array
    {
        $q = \App\Models\PackageBooking::query()->where('booking_id', $bookingId);
        if ($packageId !== null && $packageId !== '') {
            $q->where('package_id', $packageId);
        }
        if ($dmcId !== null && $dmcId !== '' && \Illuminate\Support\Facades\Schema::hasColumn('package_bookings', 'dmc_id')) {
            $q->where('dmc_id', (int) $dmcId);
        }
        $b = $q->first();
        if (!$b) {
            return [
                'travel_dates' => [],
                'selected_hotels' => [],
                'selected_attractions' => [],
                'selected_restaurants' => [],
                'arrival_data' => [],
                'departure_data' => [],
            ];
        }

        return [
            'travel_dates' => self::normalizeJsonArray($b->travel_dates),
            'selected_hotels' => self::normalizeJsonArray($b->selected_hotels),
            'selected_attractions' => self::normalizeJsonArray($b->selected_attractions),
            'selected_restaurants' => self::normalizeJsonArray($b->selected_restaurants),
            'arrival_data' => self::normalizeJsonArray($b->arrival_data),
            'departure_data' => self::normalizeJsonArray($b->departure_data),
        ];
    }

    /**
     * Calculate package booking "actual amount" for negotiation.
     *
     * Includes:
     * - Hotels: sum(base_price)
     * - Attractions: base_price + guide.price + (transfer ? transfer_price : 0)
     * - Restaurants: sum(base_price)
     * - Arrival vehicles: sum(selected_price) if present else unit_price*qty
     * - Departure vehicles: sum(selected_price) if present else unit_price*qty
     */
    public static function calculatePackageBookingActualAmount(string $bookingId, $packageId = null, $dmcId = null): float
    {
        $services = self::getPackageBookingServices($bookingId, $packageId, $dmcId);
        $sum = 0.0;

        foreach (($services['selected_hotels'] ?? []) as $h) {
            $sum += (float) ($h['base_price'] ?? 0);
        }

        foreach (($services['selected_attractions'] ?? []) as $a) {
            $sum += (float) ($a['base_price'] ?? 0);
            $sum += (float) (data_get($a, 'guide.price') ?? 0);
            if (!empty($a['transfer'])) {
                $sum += (float) ($a['transfer_price'] ?? 0);
            }
        }

        foreach (($services['selected_restaurants'] ?? []) as $r) {
            $sum += (float) ($r['base_price'] ?? 0);
            if (!empty($r['transfer'])) {
                $sum += (float) ($r['transfer_price'] ?? 0);
            }
        }

        $arrival = $services['arrival_data'] ?? [];
        if (!empty($arrival) && (bool) ($arrival['enabled'] ?? true)) {
            foreach (($arrival['vehicles'] ?? []) as $v) {
                $sum += (float) ($v['selected_price'] ?? ((float) ($v['unit_price'] ?? 0) * (int) ($v['qty'] ?? 1)));
            }
        }

        $departure = $services['departure_data'] ?? [];
        if (!empty($departure) && (bool) ($departure['enabled'] ?? true)) {
            foreach (($departure['vehicles'] ?? []) as $v) {
                $sum += (float) ($v['selected_price'] ?? ((float) ($v['unit_price'] ?? 0) * (int) ($v['qty'] ?? 1)));
            }
        }

        return round($sum, 2);
    }
    /*
    * Get User Data Based on IP Address .
    * Date 14-10-2024
    */
    public static function getCountryInfo($ipAddress)
    {
        $response = Http::get("https://ipapi.co/{$ipAddress}/json/");
        $data = $response->json();
        return [
            'country_code' => $data['country_calling_code'] ?? 'Unknown',
            'country_name' => $data['country_name'] ?? 'Unknown',
        ];
    }

    /*
    * Access Master Value .
    * Date 18-10-2024
    */
    public static function masterSettingsName($name) {
        $setting = Setting::where('name', $name)->first();
        if ($setting) {
            return [
                'master_value' => $setting->value,
            ];
        }
        return [
            'master_value' => null,
        ];
    }

    /*
    * Image path set.
    * Date 14-11-2024
    */
    public static function image_path($name, $logoFile, $container = 'uploads') {
        $get_filestorage = Setting::where('name', $name)->where('status', 1)->first();
        $logoName = 'logo_' . time() . '_' . Str::random(6) . '.' . $logoFile->getClientOriginalExtension();
        
        if ($get_filestorage) {
            try {
                
                if ($get_filestorage->value == 'local') {
                    $destinationPath = public_path('build/images');
                    $logoFile->move($destinationPath, $logoName);
                    $logoPath = asset('build/images/' . $logoName);
                } elseif ($get_filestorage->value == 's3') {
                    $path = Storage::disk('s3')->putFileAs($container, $logoFile, $logoName);
                    $logoPath = Storage::disk('s3')->url($path);
                } elseif ($get_filestorage->value == 'azure') {
                    // Try the direct blob client method first
                    try {
                        return self::uploadToAzure($logoFile, $logoName, $container);
                    } catch (\Exception $e) {
                        Log::warning('Direct Azure upload failed, trying Storage method', [
                            'error' => $e->getMessage()
                        ]);
                        // Fallback to Storage method
                        return self::uploadToAzureWithStorage($logoFile, $logoName, $container);
                    }
                } else {
                    $logoPath = null;
                }
               
                return [
                    'master_value' => $logoPath,
                ];
            } catch (\Exception $e) {
                Log::error("Image upload failed: " . $e->getMessage());
                return [
                    'master_value' => null,
                ];
            }
        }
        return [
            'master_value' => null,
        ];
    }

    /**
     * Upload JSON to Azure only (same file_storage setting as image_path).
     * Skips local and S3 â€” returns master_value null when storage is not azure.
     */
    public static function json_path(string $name, string $jsonContent, string $fileName, string $container = 'aiuploads'): array
    {
        $get_filestorage = Setting::where('name', $name)->where('status', 1)->first();

        if (!$get_filestorage || $get_filestorage->value !== 'azure' || $jsonContent === '') {
            return ['master_value' => null];
        }

        try {
            return self::uploadJsonToAzure($jsonContent, $fileName, $container);
        } catch (\Exception $e) {
            Log::error('JSON Azure upload failed: ' . $e->getMessage(), [
                'file_name' => $fileName,
                'container' => $container,
            ]);

            return [
                'master_value' => null,
                'error'      => $e->getMessage(),
            ];
        }
    }

    /**
     * Azure disk config by disk name (azure = app uploads, azure_ai = AI/JSON).
     */
    protected static function azureDiskConfig(string $disk = 'azure'): array
    {
        $config = config("filesystems.disks.{$disk}");

        return is_array($config) ? $config : [];
    }

    /**
     * AI JSON uploads: use azure_ai account when AZURE_AI_* is set; else main azure disk.
     */
    protected static function azureAiDiskConfig(): array
    {
        $ai = self::azureDiskConfig('azure_ai');
        if (!empty($ai['name']) && !empty($ai['key'])) {
            return $ai;
        }

        return self::azureDiskConfig('azure');
    }

    /**
     * Resolve blob container for AI uploads (env container wins when caller uses default).
     */
    protected static function azureAiContainer(string $container = 'aiuploads'): string
    {
        $config = self::azureAiDiskConfig();
        if ($container === 'aiuploads' && !empty($config['container'])) {
            return (string) $config['container'];
        }

        return $container;
    }

    /**
     * Azure blob URL for a JSON file in the given container.
     */
    public static function json_azure_url(string $fileName, string $container = 'aiuploads'): ?string
    {
        $storage = Setting::where('name', 'file_storage')->where('status', 1)->first();
        if (!$storage || $storage->value !== 'azure') {
            return null;
        }

        $config = self::azureAiDiskConfig();
        if (empty($config['name'])) {
            return null;
        }

        $blobContainer = self::azureAiContainer($container);

        return sprintf(
            'https://%s.blob.core.windows.net/%s/%s',
            $config['name'],
            $blobContainer,
            $fileName
        );
    }

    /**
     * Upload raw JSON string to Azure blob storage.
     */
    public static function uploadJsonToAzure(string $jsonContent, string $fileName, string $container = 'aiuploads'): array
    {
        $config = self::azureAiDiskConfig();
        $blobContainer = self::azureAiContainer($container);
        $connectionString = sprintf(
            'DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s;EndpointSuffix=core.windows.net',
            $config['name'],
            $config['key']
        );

        $blobClient = BlobRestProxy::createBlobService($connectionString);
        self::ensureAzureContainerExists($blobClient, $blobContainer);

        Log::info('Attempting Azure JSON upload', [
            'file_name' => $fileName,
            'container' => $blobContainer,
            'account' => $config['name'],
        ]);

        $blobClient->createBlockBlob($blobContainer, $fileName, $jsonContent);

        $url = sprintf(
            'https://%s.blob.core.windows.net/%s/%s',
            $config['name'],
            $blobContainer,
            $fileName
        );

        Log::info('Azure JSON upload successful', [
            'url' => $url,
            'container' => $blobContainer,
        ]);

        return ['master_value' => $url];
    }

    /*
    * Upload file to Azure with dynamic container support
    * Date 16-06-2025
    */
    public static function uploadToAzure($file, $fileName, $container = 'uploads')
    {
        try {
            // Get Azure configuration
            $config = config('filesystems.disks.azure');
            
            // Create connection string
            $connectionString = sprintf(
                'DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s;EndpointSuffix=core.windows.net',
                $config['name'],
                $config['key']
            );

            // Create blob client
            $blobClient = BlobRestProxy::createBlobService($connectionString);
            
            // Ensure container exists
            self::ensureAzureContainerExists($blobClient, $container);
            
            Log::info('Attempting Azure upload', [
                'file_name' => $fileName,
                'container' => $container
            ]);

            // Read file content
            $fileContent = file_get_contents($file->getRealPath());
            
            // Upload directly using blob client
            $blobClient->createBlockBlob($container, $fileName, $fileContent);
            
            // Generate URL
            $logoPath = sprintf(
                'https://%s.blob.core.windows.net/%s/%s',
                $config['name'],
                $container,
                $fileName
            );
            
            Log::info('Azure upload successful', [
                'path' => $fileName,
                'url' => $logoPath,
                'container' => $container
            ]);

            return [
                'master_value' => $logoPath,
            ];
        } catch (\Exception $e) {
            Log::error('Azure upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file_name' => $fileName,
                'container' => $container
            ]);
            
            return [
                'master_value' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    /*
    * Ensure Azure container exists
    * Date 16-06-2025
    */
    public static function ensureAzureContainerExists($blobClient, $container)
    {
        try {
            // Try to get container properties
            $blobClient->getContainerProperties($container);
            Log::info("Container '{$container}' already exists");
        } catch (\Exception $e) {
            try {
                // Container doesn't exist, create it
                $blobClient->createContainer($container);
                Log::info("Container '{$container}' created successfully");
            } catch (\Exception $createException) {
                Log::error("Failed to create container '{$container}'", [
                    'error' => $createException->getMessage()
                ]);
                throw $createException;
            }
        }
    }

    /*
    *Create Id for all table
    *Date 29-11-2024
    */
    public static function createId($previousId)
    {
        return $previousId ? $previousId + 1 : 1;
    }

    /**
     * Generate the next unique orders.booking_id (used by edit-tour service routes).
     */
    public static function nextOrderBookingId(): int
    {
        $bookingId = (int) self::createId((int) (Order::max('booking_id') ?? 0));
        while (Order::where('booking_id', $bookingId)->exists()) {
            $bookingId = (int) self::createId($bookingId);
        }

        return $bookingId;
    }

    /**
     * Backfill missing booking_id values so edit-tour update routes resolve correctly.
     */
    public static function ensureOrdersHaveBookingIds($orders): void
    {
        foreach ($orders as $order) {
            if (!empty($order->booking_id)) {
                continue;
            }

            $order->booking_id = self::nextOrderBookingId();
            $order->save();
        }
    }

    /**
     * Replace generated room/bed ids with numeric database ids (editform / storeServiceOrders format).
     *
     * @param  list<array<string, mixed>>  $rooms
     * @return list<array<string, mixed>>
     */
    public static function fixHotelOrderRoomIds(array $rooms, $hotelId): array
    {
        if ($rooms === [] || empty($hotelId)) {
            return $rooms;
        }

        $fixedRooms = [];

        foreach ($rooms as $room) {
            $roomId = $room['room_id'] ?? $room['roomId'] ?? null;
            $roomType = $room['room_type'] ?? $room['roomType'] ?? null;

            if ($roomId && ((is_string($roomId) && str_starts_with($roomId, 'room_')) || ! is_numeric($roomId))) {
                $foundRoomId = null;

                if (isset($room['beds']) && is_array($room['beds']) && $room['beds'] !== []) {
                    foreach ($room['beds'] as $index => $bed) {
                        $bedId = $bed['bed_id'] ?? null;

                        if ($bedId && is_string($bedId) && (str_starts_with($bedId, 'bed_') || ! is_numeric($bedId))) {
                            $numericBedId = filter_var($bedId, FILTER_SANITIZE_NUMBER_INT);
                            if ($numericBedId && is_numeric($numericBedId)) {
                                $bedId = (int) $numericBedId;
                                $room['beds'][$index]['bed_id'] = (string) $bedId;
                            }
                        }

                        if ($bedId && is_numeric($bedId)) {
                            $bedRecord = \App\Models\Bed::where('bed_id', (int) $bedId)
                                ->where(function ($q) {
                                    $q->where('is_active', 1)->orWhereNull('is_active');
                                })
                                ->first();

                            if ($bedRecord && $bedRecord->room_id) {
                                $foundRoomId = $bedRecord->room_id;
                                break;
                            }
                        }
                    }
                }

                if ($foundRoomId) {
                    $room['room_id'] = (int) $foundRoomId;
                } elseif ($roomType) {
                    $roomRecord = \App\Models\Room::where('hotel_id', $hotelId)
                        ->where('room_type', $roomType)
                        ->where(function ($q) {
                            $q->where('status', 1)->orWhereNull('status');
                        })
                        ->first();

                    if ($roomRecord && $roomRecord->room_id) {
                        $room['room_id'] = (int) $roomRecord->room_id;
                    }
                }
            } elseif ($roomId && is_numeric($roomId)) {
                $room['room_id'] = (int) $roomId;

                if (isset($room['beds']) && is_array($room['beds'])) {
                    foreach ($room['beds'] as $index => $bed) {
                        $bedId = $bed['bed_id'] ?? null;
                        if ($bedId && is_string($bedId) && (str_starts_with($bedId, 'bed_') || ! is_numeric($bedId))) {
                            $numericBedId = filter_var($bedId, FILTER_SANITIZE_NUMBER_INT);
                            if ($numericBedId && is_numeric($numericBedId)) {
                                $room['beds'][$index]['bed_id'] = (string) (int) $numericBedId;
                            }
                        } elseif ($bedId !== null && $bedId !== '' && is_numeric($bedId)) {
                            $room['beds'][$index]['bed_id'] = (string) (int) $bedId;
                        }
                    }
                }
            }

            $fixedRooms[] = $room;
        }

        return $fixedRooms;
    }

    /*
    *Date Format Maintain for Api
    *Date 14-01-2025
    */
    public static function DateFormat($date)
    {
        $get_date = new \DateTime($date);
        $date_format = $get_date->format('d/m/Y');
        return $date_format;
    }
      /*
    *Time Format Maintain for Api
    *Date 14-01-2025
    */

    public static function TimeFormat($time)
    {
        $get_time = new \DateTime($time);
        return $get_time->format('H:i');
    }

    /*
    *Date Format Maintain Admin
    *Date 10-02-2025
    */
    public static function DateFormatAdmin($date)
    {
        $date_format = Carbon::parse($date)->format('jS M Y') ;
        return $date_format;
    }

    //common response for edit tour
    public static function CommonResponse($agent_id, $tour_id)
    {
        if($agent_id){
            $booking_data = Order::where('tour_id', $tour_id)
                ->where('agent_id', $agent_id)
                ->get();
        }else{
             $booking_data = Order::where('tour_id', $tour_id)
                ->get();
        }
        
        $date_service = [];
        $hotel = [];
        $attraction = [];
        $entry_port = [];
        $exit_port = [];
        $travel_point = [];
        $travel_hourly = [];
        $guide = [];
        $restaurant = [];
        $local_transport = [];
    
        foreach ($booking_data as $booking) {
            $json_data = $booking->data;
    
            // Check if data is a JSON string and decode it
            if (!empty($json_data) && is_string($json_data)) {
                $array = json_decode($json_data, true);
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($array)) {
                    foreach ($array as $item) {
                        $bookingDates = $item['bookingDate'] ?? null;
                        $bookingType = $booking->type ?? null;
    
                        if (!$bookingDates || !$bookingType) {
                            Log::error("Missing bookingDate or type for booking ID: {$booking->booking_id}");
                            continue;
                        }
    
                        // Ensure bookingDates is always an array
                        if (!is_array($bookingDates)) {
                            $bookingDates = [$bookingDates];
                        }
    
                        // Expand date range if needed
                        $expandedDates = [];
                        if (count($bookingDates) == 2) {
                            try {
                                $startDate = \Carbon\Carbon::createFromFormat('Y-m-d', $bookingDates[0]);
                                $endDate = \Carbon\Carbon::createFromFormat('Y-m-d', $bookingDates[1]);
                                
                                // Include all dates in the range
                                while ($startDate->lte($endDate)) {
                                    $expandedDates[] = $startDate->format('Y-m-d');
                                    $startDate->addDay();
                                }
                            } catch (\Exception $e) {
                                // Use original dates as fallback
                                $expandedDates = $bookingDates;
                            }
                        } else {
                            $expandedDates = $bookingDates;
                        }
    
                        // Process each generated date
                        foreach ($expandedDates as $bookingDate) {
                            if (empty($bookingDate)) continue;
                            
                            // For hotel bookings, skip the last date in date_service
                            if ($bookingType === 'hotel') {
                                // Check if this is the last date in the range
                                $isLastDate = false;
                                if (count($bookingDates) == 2) {
                                    $isLastDate = $bookingDate === $bookingDates[1];
                                } else {
                                    $isLastDate = $bookingDate === end($bookingDates);
                                }
                                
                                if ($isLastDate) {
                                    continue; // Skip adding this date to date_service
                                }
                            }
                            
                            if (!isset($date_service[$bookingDate])) {
                                $date_service[$bookingDate] = ['services' => []];
                            }
                            if (!isset($date_service[$bookingDate]['services'][$bookingType])) {
                                $date_service[$bookingDate]['services'][$bookingType] = [
                                    'status' => $booking->status,
                                    'count' => 0
                                ];
                            }
                            $date_service[$bookingDate]['services'][$bookingType]['count']++;
                        }
    
                        // Organize data by type
                        $bookingArray = array_merge(
                            ['id' => $booking->booking_id, 'type' => $bookingType],
                            $item
                        );
    
                        switch ($bookingType) {
                            case 'hotel':
                                $hotel[] = $bookingArray;
                                break;
                            case 'attraction':
                                $attraction[] = $bookingArray;
                                break;
                            case 'attraction_package':
                                $attraction[] = $bookingArray;
                                break;
                            case 'entry_port':
                                $entry_port[] = $bookingArray;
                                break;
                            case 'exit_port':
                                $exit_port[] = $bookingArray;
                                break;
                            case 'travel_point':
                                $travel_point[] = $bookingArray;
                                break;
                            case 'travel_hourly':
                                $travel_hourly[] = $bookingArray;
                                break;
                            case 'guide':
                                $guide[] = $bookingArray;
                                break;
                            case 'restaurant':
                                $restaurant[] = $bookingArray;
                                break;
                            case 'local_transport':
                                $local_transport[] = $bookingArray;
                                break;
                            default:
                                Log::warning("Unknown booking type '{$bookingType}' for booking ID: {$booking->booking_id}");
                        }
                    }
                } else {
                    Log::error("JSON decoding error for booking ID: {$booking->booking_id} - " . json_last_error_msg());
                }
            } else if (is_array($json_data)) {
                // Handle case where data is already an array
                foreach ($json_data as $item) {
                    $bookingDates = $item['bookingDate'] ?? null;
                    $bookingType = $booking->type ?? null;
    
                    if (!$bookingDates || !$bookingType) {
                        Log::error("Missing bookingDate or type for booking ID: {$booking->booking_id}");
                        continue;
                    }
    
                    // Ensure bookingDates is always an array
                    if (!is_array($bookingDates)) {
                        $bookingDates = [$bookingDates];
                    }
    
                    // Convert date range into all dates
                    $expandedDates = [];
                    if (count($bookingDates) == 2) {
                        try {
                            $startDate = \Carbon\Carbon::createFromFormat('Y-m-d', $bookingDates[0]);
                            $endDate = \Carbon\Carbon::createFromFormat('Y-m-d', $bookingDates[1]);
                            
                            // Include all dates in the range
                            while ($startDate->lte($endDate)) {
                                $expandedDates[] = $startDate->format('Y-m-d');
                                $startDate->addDay();
                            }
                        } catch (\Exception $e) {
                            // Use original dates as fallback
                            $expandedDates = $bookingDates;
                        }
                    } else {
                        $expandedDates = $bookingDates;
                    }
    
                    // Process each date
                    foreach ($expandedDates as $bookingDate) {
                        if (empty($bookingDate)) continue;
                        
                        // For hotel bookings, skip the last date in date_service
                        if ($bookingType === 'hotel') {
                            // Check if this is the last date in the range
                            $isLastDate = false;
                            if (count($bookingDates) == 2) {
                                $isLastDate = $bookingDate === $bookingDates[1];
                            } else {
                                $isLastDate = $bookingDate === end($bookingDates);
                            }
                            
                            if ($isLastDate) {
                                continue; // Skip adding this date to date_service
                            }
                        }
                        
                        if (!isset($date_service[$bookingDate])) {
                            $date_service[$bookingDate] = ['services' => []];
                        }
                        if (!isset($date_service[$bookingDate]['services'][$bookingType])) {
                            $date_service[$bookingDate]['services'][$bookingType] = [
                                'status' => $booking->status,
                                'count' => 0
                            ];
                        }
                        $date_service[$bookingDate]['services'][$bookingType]['count']++;
                    }
    
                    // Organize data by type
                    $bookingArray = array_merge(
                        ['id' => $booking->booking_id, 'type' => $bookingType],
                        $item
                    );
    
                    switch ($bookingType) {
                        case 'hotel':
                            $hotel[] = $bookingArray;
                            break;
                        case 'attraction':
                            $attraction[] = $bookingArray;
                            break;
                        case 'attraction_package':
                            $attraction[] = $bookingArray;
                            break;
                        case 'entry_port':
                            $entry_port[] = $bookingArray;
                            break;
                        case 'exit_port':
                            $exit_port[] = $bookingArray;
                            break;
                        case 'travel_point':
                            $travel_point[] = $bookingArray;
                            break;
                        case 'travel_hourly':
                            $travel_hourly[] = $bookingArray;
                            break;
                        case 'guide':
                            $guide[] = $bookingArray;
                            break;
                        case 'restaurant':
                            $restaurant[] = $bookingArray;
                            break;
                        case 'local_transport':
                            $local_transport[] = $bookingArray;
                            break;
                        default:
                            Log::warning("Unknown booking type '{$bookingType}' for booking ID: {$booking->booking_id}");
                    }
                }
            } else {
                Log::error("Invalid or missing 'data' field for booking ID: {$booking->booking_id}");
            }
        }
    
        return [
            'date_service' => $date_service,
            'hotel' => $hotel,
            'hotel_count' => count($hotel),
            'attraction' => $attraction,
            'attraction_count' => count($attraction),
            'entry_port' => $entry_port,
            'entry_port_count' => count($entry_port),
            'exit_port' => $exit_port,
            'exit_port_count' => count($exit_port),
            'travel_point' => $travel_point,
            'travel_point_count' => count($travel_point),
            'travel_hourly' => $travel_hourly,
            'travel_hourly_count' => count($travel_hourly),
            'guide' => $guide,
            'guide_count' => count($guide),
            'restaurant' => $restaurant,
            'restaurant_count' => count($restaurant),
            'local_transport' => $local_transport,
            'local_transport_count' => count($local_transport),
        ];
    }

    /*
    *Common Response create tour and edit tour 
    *Date 29-01-2025
    */
    public static function CommonBookingResponse($agent_id, $tour_id, $type)
    {
        $booking_data = Order::where('tour_id', $tour_id)
            ->where('agent_id', $agent_id)
            ->where('status', '!=', 4)
            ->get();
        
        $date_service = [];
        $data = []; // Store only the requested type data
        $hotel_count = 0;
        $attraction_count = 0;
        $entry_port_count = 0;
        $exit_port_count = 0;
        $travel_hourly_count = 0;
        $travel_point_count = 0;
        $guide_count = 0;
        $restaurant_count = 0;
        $total_count = 0;

        foreach ($booking_data as $booking) {
            $json_data = $booking->data;
            
            // Check if data is a JSON string and decode it
            if (!empty($json_data) && is_string($json_data)) {
                $array = json_decode($json_data, true);
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($array)) {
                    foreach ($array as $item) {
                        $bookingDates = $item['bookingDate'] ?? null;

                        if ($bookingDates) {
                            // Ensure bookingDates is always an array
                            if (!is_array($bookingDates)) {
                                $bookingDates = [$bookingDates];
                            }

                            // Convert date range into all dates
                            $expandedDates = [];
                            if (count($bookingDates) == 2) {
                                try {
                                    $startDate = \Carbon\Carbon::createFromFormat('Y-m-d', $bookingDates[0]);
                                    $endDate = \Carbon\Carbon::createFromFormat('Y-m-d', $bookingDates[1]);
                                    
                                    // Include all dates in the range
                                    while ($startDate->lte($endDate)) {
                                        $expandedDates[] = $startDate->format('Y-m-d');
                                        $startDate->addDay();
                                    }
                                } catch (\Exception $e) {
                                    // Use original dates as fallback
                                    $expandedDates = $bookingDates;
                                }
                            } else {
                                $expandedDates = $bookingDates;
                            }

                            // Process each date
                            foreach ($expandedDates as $bookingDate) {
                                if (empty($bookingDate)) continue;
                                
                                // For hotel bookings, skip the last date in date_service
                                if ($booking->type === 'hotel') {
                                    // Check if this is the last date in the range
                                    $isLastDate = false;
                                    if (count($bookingDates) == 2) {
                                        $isLastDate = $bookingDate === $bookingDates[1];
                                    } else {
                                        $isLastDate = $bookingDate === end($bookingDates);
                                    }
                                    
                                    if ($isLastDate) {
                                        continue; // Skip adding this date to date_service
                                    }
                                }
                                
                                if (!isset($date_service[$bookingDate])) {
                                    $date_service[$bookingDate] = ['services' => []];
                                }
                                
                                if (!isset($date_service[$bookingDate]['services'][$booking->type])) {
                                    $date_service[$bookingDate]['services'][$booking->type] = [
                                        'status' => $booking->status,
                                        'count' => 0
                                    ];
                                }
                                
                                $date_service[$bookingDate]['services'][$booking->type]['count']++;
                            }
                        }
                        // Add to type-specific data if matching requested type
                        if(($type == 'attraction' || $type == 'attraction_package') && ($booking->type == 'attraction_package' || $booking->type == 'attraction')){
                            $data[] = array_merge(
                                ['id' => $booking->booking_id],
                                // ['type' => $booking->type],
                                ['type' => 'attraction'],
                                $item
                            );
                        }
                        else if($booking->type == $type) {
                            $data[] = array_merge(
                                ['id' => $booking->booking_id],
                                ['type' => $booking->type],
                                $item
                            );
                        }
                        // Count booking types
                        if ($booking->type == 'hotel') $hotel_count++;
                        if ($booking->type == 'attraction') 
                        $attraction_count++;
                        if ($booking->type == 'attraction_package') 
                        $attraction_count++;
                        if ($booking->type == 'entry_port') $entry_port_count++;
                        if ($booking->type == 'exit_port') $exit_port_count++;
                        if ($booking->type == 'travel_point') $travel_point_count++;
                        if ($booking->type == 'travel_hourly') $travel_hourly_count++;
                        if ($booking->type == 'guide') $guide_count++;
                        if ($booking->type == 'restaurant') $restaurant_count++;
                        $total_count++;
                    }
                } else {
                    Log::error("JSON decoding error for booking ID: {$booking->booking_id} - " . json_last_error_msg());
                }
            } else if (is_array($json_data)) {
                // Handle case where data is already an array
                foreach ($json_data as $item) {
                    $bookingDates = $item['bookingDate'] ?? null;

                    if ($bookingDates) {
                        // Process dates and update date_service...
                        if (!is_array($bookingDates)) {
                            $bookingDates = [$bookingDates];
                        }

                        $expandedDates = [];
                        if (count($bookingDates) == 2) {
                            try {
                                $startDate = \Carbon\Carbon::createFromFormat('Y-m-d', $bookingDates[0]);
                                $endDate = \Carbon\Carbon::createFromFormat('Y-m-d', $bookingDates[1]);
                                
                                while ($startDate->lte($endDate)) {
                                    $expandedDates[] = $startDate->format('Y-m-d');
                                    $startDate->addDay();
                                }
                            } catch (\Exception $e) {
                                $expandedDates = $bookingDates;
                            }
                        } else {
                            $expandedDates = $bookingDates;
                        }

                        foreach ($expandedDates as $bookingDate) {
                            if (empty($bookingDate)) continue;
                            // For hotel bookings, skip the last date in date_service
                            if ($booking->type === 'hotel') {
                                // Check if this is the last date in the range
                                $isLastDate = false;
                                if (count($bookingDates) == 2) {
                                    $isLastDate = $bookingDate === $bookingDates[1];
                                } else {
                                    $isLastDate = $bookingDate === end($bookingDates);
                                }
                                
                                if ($isLastDate) {
                                    continue; // Skip adding this date to date_service
                                }
                            }
                            
                            if (!isset($date_service[$bookingDate])) {
                                $date_service[$bookingDate] = ['services' => []];
                            }
                            
                            if (!isset($date_service[$bookingDate]['services'][$booking->type])) {
                                $date_service[$bookingDate]['services'][$booking->type] = [
                                    'status' => $booking->status,
                                    'count' => 0
                                ];
                            }
                            
                            $date_service[$bookingDate]['services'][$booking->type]['count']++;
                        }
                    }

                    // Add to type-specific data if matching requested type
                    if(($type == 'attraction' || $type == 'attraction_package') && ($booking->type == 'attraction_package' || $booking->type == 'attraction')){
                        $data[] = array_merge(
                            ['id' => $booking->booking_id],
                            // ['type' => $booking->type],
                            ['type' => 'attraction'],
                            $item
                        );
                    }
                    else if ($booking->type == $type) {
                        $data[] = array_merge(
                            ['id' => $booking->booking_id],
                            ['type' => $booking->type],
                            $item
                        );
                    }

                    // Count booking types
                    if ($booking->type == 'hotel') $hotel_count++;
                    if ($booking->type == 'attraction') $attraction_count++;
                    if ($booking->type == 'attraction_package') $attraction_count++;
                    if ($booking->type == 'entry_port') $entry_port_count++;
                    if ($booking->type == 'exit_port') $exit_port_count++;
                    if ($booking->type == 'travel_point') $travel_point_count++;
                    if ($booking->type == 'travel_hourly') $travel_hourly_count++;
                    if ($booking->type == 'guide') $guide_count++;
                    if ($booking->type == 'restaurant') $restaurant_count++;
                    $total_count++;
                }
            }
        }

        $service = [
            'date_service' => $date_service,
            'type' => $type,
            'data' => $data, 
            'hotel_count' => $hotel_count,
            'attraction_count' => $attraction_count,
            'entry_port_count' => $entry_port_count,
            'exit_port_count' => $exit_port_count,
            'travel_hourly_count' => $travel_hourly_count,
            'travel_point_count' => $travel_point_count,
            'guide_count' => $guide_count,
            'restaurant_count' => $restaurant_count,
            'total_count' => $total_count,
        ];

        return $service;
    }

    /*
    *Travclicks Mode Price Calculation
    * Date 14-02-2025
    */
    public static function calculateMinPricehotel($base_price, $dmc_id, $name, $type, $city)
    {
        $agent_dmc_id = $dmc_id;
        $dmc = User::where('userId', $dmc_id)->first(); // Use fallback DMC if agent's DMC is not found
        if (!$dmc) {
            return [0, null]; // No valid DMC found, return 0
        }
        // Determine the model based on type and get all DMC IDs from JSON arrays
        $hotel_dmc_ids = [];
        if ($type === 'hotel') {
            $records = Hotel::where('name', $name)
                ->where('city', $city)
                ->get(['dmc_id']);
        } elseif ($type === 'attraction') {
            $records = Attraction::where('name', $name)
                ->where('location', $city)
                ->get(['dmc_id']);
        } elseif ($type === 'restaurant') {
            $records = Restaurant::where('name', $name)
                ->where('city', $city)
                ->get(['dmc_id']);
        } elseif ($type === 'vehicle') {
            // $hotel_dmc_ids = OperationalCountry::where('name', $name)
            //     ->where('city', $city)
            //     ->pluck('dmc_id')
            //     ->toArray();

                $hotel_dmc_ids = Vehicle::with('operationalCountry')
                    ->whereHas('operationalCountry', function ($query) use ($city, $dmc) {
                        $query->where('city', $city)->where('dmc_id', $dmc->userId);
                    })
                    ->where('dmc_id', $dmc->userId) // Use the resolved DMC
                    ->pluck('dmc_id')
                    ->toArray();

        } elseif ($type === 'guide') {
            $hotel_dmc_ids = Guide::where('name', $name)
                ->where('city', $city)
                ->pluck('dmc_id')
                ->toArray();
        } else {
            return [0, null]; // Invalid type, return 0
        }

        // Extract all DMC IDs from JSON arrays (only if records exist)
        if ($type === 'hotel' || $type === 'attraction' || $type === 'restaurant') {
            foreach ($records as $record) {
                $dmc_id_data = $record->dmc_id;
                if (is_string($dmc_id_data)) {
                    // If it's a JSON string, decode it
                    $decoded = json_decode($dmc_id_data, true);
                    if (is_array($decoded)) {
                        $hotel_dmc_ids = array_merge($hotel_dmc_ids, $decoded);
                    } else {
                        // Single ID as string
                        $hotel_dmc_ids[] = (int)$dmc_id_data;
                    }
                } elseif (is_array($dmc_id_data)) {
                    // Already an array
                    $hotel_dmc_ids = array_merge($hotel_dmc_ids, $dmc_id_data);
                } elseif (is_numeric($dmc_id_data)) {
                    // Single numeric ID
                    $hotel_dmc_ids[] = (int)$dmc_id_data;
                }
            }
        }

        // Remove duplicates and ensure all values are integers
        $hotel_dmc_ids = array_unique(array_map('intval', array_filter($hotel_dmc_ids)));

        if (empty($hotel_dmc_ids)) {
            return [0, null]; // No linked DMC found, set price to 0
        }
        // Exclude the agent's own DMC ID
        $filtered_dmc_ids = array_values(array_filter($hotel_dmc_ids, fn($dmc_id) => $dmc_id && $dmc_id !== $agent_dmc_id));
        if (empty($filtered_dmc_ids)) {
            return [0, null]; // No valid DMCs remaining, set price to 0
        }
        // Fetch valid DMC users
        $dmc_users = User::whereIn('userId', $filtered_dmc_ids)->get();
        if ($dmc_users->isEmpty()) {
            return [0, null]; // No valid DMC users found, set price to 0
        }
        // Calculate min price
        $prices = $dmc_users->map(function ($dmc_user) use ($base_price) {
            $markup_value = is_numeric($dmc_user->markup_price) ? $dmc_user->markup_price : 0;
            $dmc_markup = ($dmc_user->markup_type == 0)
                ? $markup_value
                : ($base_price * $markup_value / 100);
            return [
                //'total_price' => $base_price + $dmc_markup,
                //'trav_dmc_id' => $dmc_user->userId
                'total_price' => 0,
                'trav_dmc_id' => null
            ];
        });

        if ($prices->isEmpty()) {
            return [0, null]; // No prices found, set price to 0
        }

        $min_price_data = $prices->sortBy('total_price')->first();
        return [0, null];
    }

    /*
    * Dmc Mode Price Calculation
    * Date 14-02-2025
    */
    public static function calculateDmcModePricehotel($base_price, $dmc_id, $name, $type, $city)
    {
        $dmc = User::where('userId', $dmc_id)->first();
        
        if (!$dmc) {
            return [0, null]; // No valid DMC found, return 0
        }
        if ($type === 'hotel') {
            $hotel = Hotel::where('name', $name)
            ->where('city', $city)
            ->whereJsonContains('dmc_id', $dmc->userId) // Check if DMC ID exists in JSON array
            ->first();
        } elseif ($type === 'attraction') {
            $hotel = Attraction::where('name', $name)
            ->where('location', $city)
            ->whereJsonContains('dmc_id', $dmc->userId) // Check if DMC ID exists in JSON array
            ->first();
        } elseif ($type === 'restaurant') {
            $hotel = Restaurant::where('name', $name)
            ->where('city', $city)
            ->whereJsonContains('dmc_id', $dmc->userId) // Check if DMC ID exists in JSON array
            ->first();
        } elseif ($type === 'vehicle') {
            $hotel = Vehicle::where('vehicle_name', $name)
            ->where('city', $city)
            ->where('dmc_id', $dmc->userId) // Use the resolved DMC
            ->first();
            
        }elseif ($type === 'guide') {
            $hotel = Guide::where('name', $name)
            ->where('city', $city)
            ->where('dmc_id', $dmc->userId) // Use the resolved DMC
            ->first();
        } else {
            return [0, null]; // Invalid type, return 0
        }
        
        if (!$hotel) {
            return [0, null]; // Hotel not linked to this DMC, return 0
        }
        $markup_value = is_numeric($dmc->markup_price) ? $dmc->markup_price : 0;
        $dmc_markup = ($dmc->markup_type == 0)
        ? $markup_value
        : ($base_price * $markup_value / 100);
        $price = $base_price + $dmc_markup;
        return [$price ?? 0, $dmc->userId ?? null]; // Ensure price is numeric, fallback to 0
    }
 
    //Vehicle dmc mode price 
    public static function calculateDmcModePriceVehicle($base_price, $salesManagerId, $name, $type)
    {
        $dmc = User::where('userId', $dmc_id)->first(); 
        if (!$dmc) {
            return [0, null]; // No valid DMC found, return 0
        }

         if ($type === 'vehicle') {
            $operationalCountry = OperationalCountry::where('name', $name)
                 ->where('city', $city)
                 ->where('vehicle_id', $vehicle_id)
                 ->where('dmc_id', $dmc->userId)
                 ->first();
         }else {
             return [0, null]; // Invalid type, return 0
         }
         if (!$operationalCountry) {

             return [0, null]; // operationalCountry not linked to this DMC, return 0
         }
         $markup_value = is_numeric($dmc->markup_price) ? $dmc->markup_price : 0;
         $dmc_markup = ($dmc->markup_type == 0)
             ? $markup_value
             : ($base_price * $markup_value / 100);

         $price = $base_price + $dmc_markup;
         return [$price ?? 0, $dmc->userId ?? null]; // Ensure price is numeric, fallback to 0
    }

     //Vehicle travclicks price
     public static function calculateMinPriceVehicle($base_price, $salesManagerId, $name, $type, $city, $vehicle_id, $vehicle_name)
    {
        $asmng_dmc = User::where('userId', $salesManagerId)->first();
        $salesmng_dmc = User::where('userId', $asmng_dmc->created_by)->first();
        $saleshead_dmc = User::where('userId', $salesmng_dmc->created_by)->first();
        $dmc_users = User::where('userId', $saleshead_dmc->created_by)->first();
        $dmc = $dmc_users ?: User::first();        

        if (!$dmc) {
            return [0, null]; // No valid DMC found, return 0
        }
         // Determine the model based on type
         if ($type === 'vehicle') {
             $vehicles = OperationalCountry::where('name', $name)
                 ->where('city', $city)
                 ->where('vehicle_id', $vehicle_id)
                 ->pluck('dmc_id')
                 ->toArray();
            $dmc_ids = Vehicle::where('vehicle_name', $vehicle_name)
            ->pluck('dmc_id')
            ->toArray();
         } else {
             return [0, null]; // Invalid type, return 0
         }
         if (empty($vehicles)) {
             return [0, null]; // No linked DMC found, set price to 0
         }
         // Exclude the agent's own DMC ID
         $filtered_dmc_ids = array_values(array_filter($vehicles, fn($dmc_id) => $dmc_id));
         if (empty($filtered_dmc_ids)) {
             return [0, null]; // No valid DMCs remaining, set price to 0
         }
         // Fetch valid DMC users
         $dmc_users = User::whereIn('userId', $filtered_dmc_ids)->get();
         if ($dmc_users->isEmpty()) {
             return [0, null]; // No valid DMC users found, set price to 0
         }
         // Calculate min price
         $prices = $dmc_users->map(function ($dmc_user) use ($base_price) {
             $markup_value = is_numeric($dmc_user->markup_price) ? $dmc_user->markup_price : 0;
             $dmc_markup = ($dmc_user->markup_type == 0)
                 ? $markup_value
                 : ($base_price * $markup_value / 100);
             return [
                 'total_price' => $base_price + $dmc_markup,
                 'dmc_id' => $dmc_user->userId
             ];
         });
 
         if ($prices->isEmpty()) {
             return [0, null]; // No prices found, set price to 0
         }
 
         $min_price_data = $prices->sortBy('total_price')->first();
         return [$min_price_data['total_price'] ?? 0, $min_price_data['dmc_id'] ?? null];
    }
   
    /*
    *Details Backup Minimum/TravClicks Price Calculation
    * Date 07-03-2025
    */
    public static function CalculatePriceDetails($base_price, $get_dmc_id){
        $dmc = User::where('userId', $get_dmc_id)->first();
        $dmc_markup = 0;
        if ($dmc) {
            $markup_value = is_numeric($dmc->markup_price) ? $dmc->markup_price : 0;
            $dmc_markup = ($dmc->markup_type == 0) ? $markup_value : ($base_price * $markup_value / 100);
        }
        $price = ($base_price + $dmc_markup) ?? 0;
        return [$price];
    }

    /*
    * Upload file to Azure using Laravel Storage (alternative method)
    * Date 16-06-2025
    */
    public static function uploadToAzureWithStorage($file, $fileName, $container = 'uploads')
    {
        try {
            // Create a temporary Azure disk configuration for this container
            config(['filesystems.disks.azure_temp' => [
                'driver' => 'azure',
                'name' => config('filesystems.disks.azure.name'),
                'key' => config('filesystems.disks.azure.key'),
                'endpoint' => config('filesystems.disks.azure.endpoint'),
            ]]);

            // Store the file in the temporary disk
            $path = Storage::disk('azure_temp')->putFileAs($container, $file, $fileName);
            
            // Generate the URL for the stored file
            $url = Storage::disk('azure_temp')->url($path);
            
            return [
                'master_value' => $url,
            ];
        } catch (\Exception $e) {
            Log::error('Azure upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file_name' => $fileName,
                'container' => $container
            ]);
            
            return [
                'master_value' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    /*
    * Delete image from Azure blob storage
    * Date [Current Date]
    */
    public static function deleteAzureImage($imageUrl)
    {
        try {
            $config = config('filesystems.disks.azure');
            $connectionString = sprintf(
                'DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s;EndpointSuffix=core.windows.net',
                $config['name'],
                $config['key']
            );
            
            $blobClient = BlobRestProxy::createBlobService($connectionString);
            
            // Extract filename from URL
            $fileName = basename(parse_url($imageUrl, PHP_URL_PATH));
            
            // Delete the blob
            $blobClient->deleteBlob('uploads', $fileName);
            
        } catch (\Exception $e) {
            // Ignore errors, just log
            Log::error('Azure image deletion failed: ' . $e->getMessage());
        }
    }

    public static function sendEmail($email, $type, $subject, $body, $orderData)
    {
        try {
            // Process order data to prepare template data
            $data = [];
            
            // Extract data from order object if it's an object
            if (is_object($orderData)) {
                // If it's an Order model object, extract what we need
                $data = [
                    "booking_id" => $orderData->booking_id ?? ('BK-' . ($orderData->id ?? rand(10000, 99999))),
                    "customer_name" => $orderData->customer_name ?? $orderData->name ?? "Guest",
                    "type" => $type,
                    "booking_date" => $orderData->booking_date ?? date('Y-m-d'),
                    "check_in_date" => $orderData->check_in_date ?? date('Y-m-d'),
                    "check_out_date" => $orderData->check_out_date ?? date('Y-m-d', strtotime('+1 day')),
                    "location" => $orderData->location ?? $orderData->city ?? "Unknown",
                    "guests" => $orderData->guests ?? "Not specified",
                    "reference_number" => $orderData->reference_number ?? ('REF-' . rand(1000, 9999)),
                    "total_price" => $orderData->total_price ?? 0,
                    "payment_status" => $orderData->payment_status ?? "Pending",
                    "room_type" => $orderData->room_type ?? [],
                    "bed_type" => $orderData->bed_type ?? "Queen Size",
                    "meal_plan" => $orderData->meal_plan ?? null,
                    "check_in_time" => $orderData->check_in_time ?? "00:00",
                    "check_out_time" => $orderData->check_out_time ?? "00:00",
                    "max_occupancy" => $orderData->max_occupancy ?? "Not specified",
                    "baby_cot" => $orderData->baby_cot ?? "Not specified",
                    "fullName" => $orderData->fullName ?? null,
                    "email" => $orderData->email ?? null,
                    "phone" => $orderData->phone ?? null,
                    "countryCode" => $orderData->countryCode ?? null,
                    "address1" => $orderData->address1 ?? null,
                    "address2" => $orderData->address2 ?? null,
                    "state" => $orderData->state ?? null,
                    "zip" => $orderData->zip ?? null,
                    "specialRequests" => $orderData->specialRequests ?? null,
                    "dmc_email" => $orderData->dmc_email ?? null,
                    "dmc_phone" => $orderData->dmc_phone ?? null,
                    "No_of_rooms" => $orderData->No_of_rooms ?? 0,
                    "No_of_beds" => $orderData->No_of_beds ?? 0,
                    "dmc_logo" => $orderData->dmc_logo ?? null,
                    "hotel_name" => $orderData->hotel_name ?? null,
                    "message_type" => $orderData->message_type ?? null,
                    "otp" => $orderData->otp ?? null,
                    "name" => $orderData->name ?? null,
                    "salutation" => $orderData->salutation ?? null,
                    "email" => $orderData->email ?? null,
                ];
            }

            
            
            // If it's already an array, use it directly
            else if (is_array($orderData)) {
                $data = $orderData;
            }
            
            // Get company settings for the email
            $logoSetting = self::masterSettingsName('logo');
            $nameSetting = self::masterSettingsName('name');
            
            // Add company info to the data array
            $companyData = [
                "company" => [
                    "companyName" => $orderData['dmc_company'] ?? config('app.name'),
                    "logo" => $orderData['dmc_logo'] ?? $logoSetting['master_value'] ?? asset('images/logo.png')
                ]
            ];
            
            // Add mail settings for the template
            $mailSettings = (object)[
                "support_email" => $orderData['dmc_email'] ?? null,
                "support_phone" => $orderData['dmc_phone'] ?? null,
                "facebook_url" => "https://facebook.com/yourcompany",
                "twitter_url" => "https://twitter.com/yourcompany",
                "instagram_url" => "https://instagram.com/yourcompany",
                "linkedin_url" => "https://linkedin.com/company/yourcompany"
            ];
            
            // Merge all data
            $viewData = array_merge($data, $companyData);
            $viewData['mail_settings'] = $mailSettings;

            // Use DMC-specific SMTP from emails_setup (not .env)
            self::applyEmailsSetupMailConfig();

            // Determine which template to use based on the type
            $template = 'mails.' . $type;
            if (!view()->exists($template)) {
                $template = 'mails.booking_confirmation'; // Default template
            }
            
            // Render the email template
            try {
                $html = view($template, $viewData)->render();
            } catch (\Exception $e) {
                return "Error rendering email template: " . $e->getMessage();
            }
            
            // Extract the entire style tag content
            preg_match('/<style>(.*?)<\/style>/s', $html, $styleMatches);
            $styles = !empty($styleMatches[0]) ? $styleMatches[0] : '';
            
            // Extract the email-container div with all its contents
            preg_match('/<div class="email-container">(.*?)<\/div>\s*$/s', $html, $matches);
            if (!empty($matches[0])) {
                $extractedHtml = $matches[0];
                
                // Add minimal HTML structure with the extracted styles
                $emailHtml = '<!DOCTYPE html><html><head><title>' . $subject . '</title>' . $styles . '</head><body>' . $extractedHtml . '</body></html>';
                
                // Send the email to the actual recipient
                try {
                    Mail::to($email)->send(new DmcMail($emailHtml, $subject));
                    // Log successful email sending
                    Log::info("Email sent successfully to: {$email}", ['type' => $type, 'subject' => $subject]);
                    
                    return true;
                } catch (\Exception $e) {
                    return "Failed to send email: " . $e->getMessage();
                }
            } else {
                // Handle case where the div is not found
                Log::error("Email container div not found in email template");
                return "Email container div not found in email template";
            }
        } catch (\Exception $e) {
            \Log::error('Email sending failed: ' . $e->getMessage(), [
                'recipient' => $email,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            return "Email sending failed: " . $e->getMessage();
        }
    }

    /**
     * Send a hotel booking confirmation email with a custom subject and body
     * (plain text body is wrapped in a minimal HTML email container).
     *
     * @param  string  $email    Hotel email from hotels.email
     * @param  string  $subject  Email subject
     * @param  string  $body     Plain-text email body (e.g. from mail preview content)
     * @return true|string       true on success, error message string on failure
     */
    public static function sendHotelApprovalEmail($email, $subject, $body)
    {
        try {
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Log::error('Hotel approval email skipped: invalid hotel email', [
                    'email' => $email,
                    'subject' => $subject,
                ]);
                return 'Hotel email is not set or invalid';
            }

            // Use DMC-specific SMTP from emails_setup (not .env)
            self::applyEmailsSetupMailConfig();

            $escapedBody = nl2br(e($body));
            $safeSubject = e($subject);

            $emailHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . $safeSubject . '</title>
<style>
body{font-family:Segoe UI,Tahoma,Geneva,Verdana,sans-serif;background:#f8f9fa;margin:0;padding:0;color:#333;line-height:1.6;}
.email-container{max-width:720px;margin:20px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 8px 24px rgba(0,0,0,.08);border:1px solid #e2e8f0;}
.email-header{background:linear-gradient(135deg,#28a745 0%,#20c997 100%);padding:24px 28px;color:#fff;}
.email-header h1{margin:0;font-size:20px;}
.email-body{padding:28px;white-space:pre-wrap;font-family:Consolas,Monaco,monospace;font-size:13px;color:#1f2937;}
.email-footer{padding:16px 28px;background:#f8fafc;border-top:1px solid #e2e8f0;font-size:12px;color:#64748b;}
</style>
</head><body>
<div class="email-container">
<div class="email-header"><h1>Hotel Booking Confirmation</h1></div>
<div class="email-body">' . $escapedBody . '</div>
<div class="email-footer">This email was sent automatically from the booking system.</div>
</div>
</body></html>';

            Mail::to($email)->send(new DmcMail($emailHtml, $subject));

            Log::info('Hotel approval email sent successfully', [
                'email' => $email,
                'subject' => $subject,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Hotel approval email sending failed', [
                'email' => $email,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);

            return 'Email sending failed: ' . $e->getMessage();
        }
    }

    /**
     * Apply SMTP/from settings from emails_setup for the given (or current) DMC.
     * Returns the setup row when found, otherwise null.
     */
    public static function applyEmailsSetupMailConfig($dmcId = null): ?EmailsSetup
    {
        try {
            if (empty($dmcId)) {
                $user = Auth::user();
                $dmcId = $user ? self::getDmcId($user) : null;
                if ($user && (int) $user->role_id === 1) {
                    $dmcId = 1;
                }
            }

            if (empty($dmcId)) {
                return null;
            }

            $setup = EmailsSetup::where('dmcId', $dmcId)->first();
            if (!$setup || empty($setup->SMTP_Host)) {
                return $setup;
            }

            self::applyRuntimeMailConfig([
                'host' => $setup->SMTP_Host,
                'port' => $setup->SMTP_Port,
                'encryption' => $setup->SMTP_Encrypt,
                'username' => $setup->SMTP_User,
                'password' => $setup->SMTP_Pass,
                'from_email' => $setup->From_Email,
                'from_name' => $setup->From_Name,
            ]);

            return $setup;
        } catch (\Exception $e) {
            Log::warning('Failed to apply emails_setup mail config', [
                'dmcId' => $dmcId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Override Laravel SMTP mailer config at runtime and purge cached mailer.
     */
    public static function applyRuntimeMailConfig(array $config): void
    {
        $encryption = strtolower((string) ($config['encryption'] ?? 'tls'));
        if ($encryption === 'none' || $encryption === '') {
            $encryption = null;
        }

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.transport', 'smtp');
        Config::set('mail.mailers.smtp.host', $config['host'] ?? null);
        Config::set('mail.mailers.smtp.port', (int) ($config['port'] ?? 587));
        Config::set('mail.mailers.smtp.encryption', $encryption);
        Config::set('mail.mailers.smtp.username', $config['username'] ?? null);
        Config::set('mail.mailers.smtp.password', $config['password'] ?? null);

        if (!empty($config['from_email'])) {
            Config::set('mail.from.address', $config['from_email']);
            Config::set('mail.from.name', $config['from_name'] ?? config('app.name'));
        }

        try {
            app('mail.manager')->purge('smtp');
        } catch (\Throwable $e) {
            // Mail manager may not be bound in some contexts.
        }
    }

    public static function getDmcId($auth_user){
        if($auth_user->agent_id){
            $agent = Agent::where('agent_id', $auth_user->agent_id)->first();
            $sales_manager_dmc = $agent->sales_manager_dmc;
            $dmcId = null;
            
            if($agent->role_id == 11){
                $dmcId = $sales_manager_dmc;
            }
            elseif($agent->role_id == 33 || $agent->role_id == 34 || $agent->role_id == 36 || $agent->role_id == 128 || $agent->role_id == 129 || $agent->role_id == 130 || $agent->role_id == 134 || $agent->role_id == 135 || $agent->role_id == 136 || $agent->role_id == 138){
                $sales_head = User::where('userId', $sales_manager_dmc)->first();
                $dmcId = $sales_head->created_by;
            }
            elseif($agent->role_id == 37 || $agent->role_id == 126 || $agent->role_id == 124){
                $sales_manager = User::where('userId', $sales_manager_dmc)->first();
                $sales_head = User::where('userId', $sales_manager->created_by)->first();
                $dmcId = $sales_head->created_by;
            }
            elseif($agent->role_id == 38 || $agent->role_id == 127 || $agent->role_id == 125){
                $assistant_sales_manager = User::where('userId', $sales_manager_dmc)->first();
                $sales_manager = User::where('userId', $assistant_sales_manager->created_by)->first();
                $sales_head = User::where('userId', $sales_manager->created_by)->first();
                $dmcId = $sales_head->created_by;
            }
            return $dmcId;
        }
        elseif($auth_user->userId){
            $user = $auth_user;
            if($user->role_id == 11 || $user->role_id == 20){
                return $user->userId;
            }
            elseif(in_array($user->role_id, [33, 34, 35, 36, 128, 129, 130, 131, 132, 133, 134, 135, 136, 137, 138])){
                $sales_head = User::where('userId', $user->userId)->first();
                return $sales_head->created_by;
            }
            elseif($user->role_id == 37 || in_array($user->role_id, [64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 139])){
                $sales_manager = User::where('userId', $user->userId)->first();
                $sales_head = User::where('userId', $sales_manager->created_by)->first();
                return $sales_head->created_by;
            }
            elseif(in_array($user->role_id, [38, 81, 84, 87, 90, 93, 96, 99, 102, 105, 108, 111, 114, 117, 120, 123, 124, 125, 126, 127, 140])){
                $assistant_sales_manager = User::where('userId', $user->userId)->first();
                $sales_manager = User::where('userId', $assistant_sales_manager->created_by)->first();
                $sales_head = User::where('userId', $sales_manager->created_by)->first();
                return $sales_head->created_by;
            }
        }
        return null;
    }


    /**
     * Country used for multi-country tour visibility.
     * Prefer user_country, else first CSV segment of country.
     * For sales staff, fall back to their parent DMC country.
     */
    public static function resolveUserOperatingCountry($user): ?string
    {
        if (!$user) {
            return null;
        }

        $pickFirst = static function (?string $raw): ?string {
            $raw = trim((string) $raw);
            if ($raw === '') {
                return null;
            }
            $parts = preg_split('/\s*,\s*/', $raw) ?: [];
            $first = trim((string) ($parts[0] ?? ''));
            return $first !== '' ? $first : null;
        };

        $country = $pickFirst($user->user_country ?? null)
            ?: $pickFirst($user->country ?? null);

        if ($country) {
            return $country;
        }

        // Sales / ops: inherit DMC country (e.g. India DMC â†’ India)
        $dmcId = self::getDmcId($user);
        if (!$dmcId && (int) ($user->role_id ?? 0) === 11) {
            $dmcId = $user->userId;
        }
        if ($dmcId && (int) $dmcId !== (int) ($user->userId ?? 0)) {
            $dmcUser = User::where('userId', $dmcId)->first();
            if ($dmcUser) {
                return $pickFirst($dmcUser->user_country ?? null)
                    ?: $pickFirst($dmcUser->country ?? null);
            }
        }

        return null;
    }

    /**
     * Child DMC ids under the same Master DMC as $dmcId (includes $dmcId).
     */
    public static function getSiblingDmcIds($dmcId): array
    {
        $dmcId = (int) $dmcId;
        if ($dmcId <= 0) {
            return [];
        }

        $dmcUser = User::where('userId', $dmcId)->first();
        if (!$dmcUser) {
            return [$dmcId];
        }

        $masterDmcId = (int) ($dmcUser->master_dmc_id ?? 0);
        if ($masterDmcId <= 0 && (int) ($dmcUser->role_id ?? 0) === 10) {
            $masterDmcId = $dmcId;
        }

        if ($masterDmcId <= 0) {
            $visited = [];
            $candidateId = (int) ($dmcUser->created_by ?? 0);
            $safety = 0;
            while ($candidateId > 0 && $safety < 8 && !in_array($candidateId, $visited, true)) {
                $visited[] = $candidateId;
                $candidate = User::where('userId', $candidateId)->first();
                if (!$candidate) {
                    break;
                }
                if ((int) ($candidate->role_id ?? 0) === 10) {
                    $masterDmcId = (int) $candidate->userId;
                    break;
                }
                $candidateId = (int) ($candidate->created_by ?? 0);
                $safety++;
            }
        }

        if ($masterDmcId <= 0) {
            return [$dmcId];
        }

        $ids = User::where('master_dmc_id', $masterDmcId)
            ->where('role_id', 11)
            ->pluck('userId')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (!in_array($dmcId, $ids, true)) {
            $ids[] = $dmcId;
        }

        return array_values(array_unique(array_filter($ids)));
    }

    /**
     * Match tours.destination CSV (e.g. "Singapore, Malaysia, India") to one country.
     */
    public static function whereDestinationContainsCountry($query, string $country, string $column = 'destination')
    {
        $country = trim($country);
        if ($country === '') {
            return $query;
        }

        $needle = mb_strtolower($country);

        return $query->whereRaw(
            "EXISTS (
                SELECT 1
                FROM unnest(
                    string_to_array(
                        regexp_replace(LOWER(COALESCE({$column}, '')), '\\s*,\\s*', ',', 'g'),
                        ','
                    )
                ) AS dest(name)
                WHERE TRIM(dest.name) = ?
            )",
            [$needle]
        );
    }

    /**
     * Master multi-country access:
     * - Always show tours for the user's own DMC
     * - Also show sibling-DMC tours when destination includes this DMC/user country
     *   Example: Singapore DMC sales creates "Singapore, Malaysia, India"
     *            â†’ India DMC (and India sales) can see that tour
     */
    public static function applyTourDmcCountryAccess($query, $dmcId, $user = null, string $dmcColumn = 'tours.dmc_id', string $destinationColumn = 'tours.destination')
    {
        $dmcId = (int) $dmcId;
        if ($dmcId <= 0) {
            return $query;
        }

        $user = $user ?: Auth::user();
        $siblingIds = self::getSiblingDmcIds($dmcId);
        $country = self::resolveUserOperatingCountry($user);

        // No siblings / no country â†’ classic own-DMC filter
        if (count($siblingIds) <= 1 || !$country) {
            return $query->where($dmcColumn, $dmcId);
        }

        return $query->where(function ($q) use ($dmcId, $siblingIds, $country, $dmcColumn, $destinationColumn) {
            $q->where($dmcColumn, $dmcId)
                ->orWhere(function ($q2) use ($siblingIds, $country, $dmcColumn, $destinationColumn) {
                    $q2->whereIn($dmcColumn, $siblingIds);
                    self::whereDestinationContainsCountry($q2, $country, $destinationColumn);
                });
        });
    }

    /**
     * Resolve the display currency for a DMC (users.currency of the DMC user).
     * Pass the packages.dmc_id (or any DMC userId). Returns null when not found.
     */
    public static function getDmcCurrency($dmcId = null): ?string
    {
        if (empty($dmcId)) {
            return null;
        }

        $currency = User::where('userId', $dmcId)->value('currency');

        return filled($currency) ? $currency : null;
    }

    /**
     * DMC booking form type from users.is_pro (set on users listing).
     * 1 = Lite only, 2 = Pro only, 3 = Both.
     */
    public static function getDmcBookingType($user = null): int
    {
        $user = $user ?? Auth::user();
        if (!$user) {
            return 1;
        }

        $dmcId = self::getDmcId($user);
        if (!$dmcId && (int) ($user->role_id ?? 0) === 11) {
            $dmcId = $user->userId;
        }

        if (!$dmcId) {
            $own = (int) ($user->is_pro ?? 1);
            return in_array($own, [1, 2, 3], true) ? $own : 1;
        }

        $dmc = User::where('userId', $dmcId)->first();
        $bookingType = (int) ($dmc->is_pro ?? 1);

        return in_array($bookingType, [1, 2, 3], true) ? $bookingType : 1;
    }

    public static function dmcCanAccessLiteForm($user = null): bool
    {
        return in_array(self::getDmcBookingType($user), [1, 3], true);
    }

    public static function dmcCanAccessProForm($user = null): bool
    {
        return in_array(self::getDmcBookingType($user), [2, 3], true);
    }

    /**
     * Redirect/JSON denial when the current user's DMC cannot access a booking form.
     * Returns null when access is allowed.
     */
    public static function bookingFormAccessDeniedResponse(string $formType, $user = null)
    {
        $formType = strtolower($formType);
        $allowed = $formType === 'pro'
            ? self::dmcCanAccessProForm($user)
            : self::dmcCanAccessLiteForm($user);

        if ($allowed) {
            return null;
        }

        $label = $formType === 'pro' ? 'Pro' : 'Lite';
        $message = "Your DMC account does not have access to the {$label} booking form.";

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json(['success' => false, 'message' => $message], 403);
        }

        return redirect()->route('dashboard')->with('error', $message);
    }

    /**
     * Resolve QTN (quotation) or ITN (itinerary) email type from DMC user settings.
     * Falls back to master DMC when the child DMC has no selection.
     */
    public static function resolveDmcAiResponse(?User $dmcUser): ?string
    {
        if ($dmcUser === null) {
            return null;
        }

        $type = strtoupper(trim((string) ($dmcUser->ai_response ?? '')));
        if (in_array($type, ['QTN', 'ITN'], true)) {
            return $type;
        }

        $masterId = (int) ($dmcUser->master_dmc_id ?? 0);
        if ($masterId > 0 && $masterId !== (int) $dmcUser->userId) {
            $master = User::where('userId', $masterId)->first();
            if ($master) {
                $masterType = strtoupper(trim((string) ($master->ai_response ?? '')));
                if (in_array($masterType, ['QTN', 'ITN'], true)) {
                    return $masterType;
                }
            }
        }

        return null;
    }

    public static function normalizeEmailMessageId(?string $messageId): ?string
    {
        $messageId = trim((string) $messageId);
        if ($messageId === '') {
            return null;
        }

        $messageId = preg_replace('/^message-id:\s*/i', '', $messageId) ?? $messageId;
        $messageId = trim($messageId, " \t\n\r\0\x0B\"'");

        if ($messageId === '') {
            return null;
        }

        if (! str_starts_with($messageId, '<')) {
            $messageId = '<'.$messageId;
        }
        if (! str_ends_with($messageId, '>')) {
            $messageId = rtrim($messageId, '>').'>';
        }

        return $messageId;
    }

    public static function isUsableEmailUuid(?string $emailUuid): bool
    {
        $emailUuid = trim((string) $emailUuid);
        if ($emailUuid === '') {
            return false;
        }

        $normalized = strtolower(trim($emailUuid, '<>'));

        return ! in_array($normalized, [
            'no-uuid-provided',
            'none',
            'null',
            'n/a',
            'na',
            'undefined',
        ], true);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public static function resolveEmailUuidFromContext(array $context): ?string
    {
        $raw = trim((string) ($context['email_uuid'] ?? ''));
        if (! self::isUsableEmailUuid($raw)) {
            return null;
        }

        return self::normalizeEmailMessageId($raw);
    }

    public static function resolveEmailSubjectFromContext(array $context): ?string
    {
        $candidates = [
            $context['subject'] ?? null,
            $context['mail_received'] ?? null,
        ];

        foreach ($candidates as $subject) {
            $subject = trim((string) $subject);
            if ($subject === '' || self::looksLikeEmailReceivedTimestamp($subject)) {
                continue;
            }

            return $subject;
        }

        return null;
    }

    public static function looksLikeEmailReceivedTimestamp(string $value): bool
    {
        return (bool) preg_match(
            '/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}/',
            $value
        );
    }

    /**
     * @return list<string>
     */
    public static function normalizeEmailList(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_string($value)) {
            $value = preg_split('/[,;]+/', $value) ?: [];
        }

        if (! is_array($value)) {
            return [];
        }

        $emails = [];
        foreach ($value as $item) {
            if (is_array($item)) {
                $email = trim((string) ($item['email'] ?? $item['address'] ?? ''));
            } else {
                $email = trim((string) $item);
            }

            $email = trim($email, " \t\n\r\0\x0B<>\"'");
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && ! in_array($email, $emails, true)) {
                $emails[] = $email;
            }
        }

        return $emails;
    }

    /**
     * Merge every present list field (cc, cc_list, etc.) â€” empty arrays are skipped.
     *
     * @param  array<string, mixed>  $context
     * @param  list<string>  $keys
     * @return list<string>
     */
    public static function resolveEmailListFromContext(array $context, array $keys): array
    {
        $emails = [];
        foreach ($keys as $key) {
            if (! array_key_exists($key, $context)) {
                continue;
            }
            $emails = array_merge($emails, self::normalizeEmailList($context[$key]));
        }

        return array_values(array_unique($emails));
    }

    public static function looksLikeEmailMessageId(string $value): bool
    {
        $bare = trim($value, '<>');
        if ($bare === '' || ! str_contains($bare, '@')) {
            return false;
        }

        $local = strstr($bare, '@', true) ?: '';
        if ($local === '') {
            return false;
        }

        if (preg_match('/[+=%]/', $local) || strlen($local) > 40) {
            return true;
        }

        return ! filter_var($bare, FILTER_VALIDATE_EMAIL);
    }

    /**
     * @return list<string>
     */
    public static function extractReferenceTokens(mixed $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        if (is_array($raw)) {
            $tokens = [];
            foreach ($raw as $item) {
                $tokens = array_merge($tokens, self::extractReferenceTokens($item));
            }

            return $tokens;
        }

        $raw = trim((string) $raw);
        if ($raw === '') {
            return [];
        }

        if (preg_match_all('/<[^>]+>/', $raw, $matches)) {
            return $matches[0];
        }

        $tokens = [];
        foreach (preg_split('/[\s,;]+/', $raw) ?: [] as $part) {
            $part = trim((string) $part);
            if ($part !== '') {
                $tokens[] = $part;
            }
        }

        return $tokens !== [] ? $tokens : [$raw];
    }

    /**
     * Split payload references into Message-IDs (threading) and CC mailbox addresses.
     *
     * @param  array<string, mixed>  $context
     * @return array{message_ids: list<string>, cc_emails: list<string>}
     */
    public static function partitionEmailReferencesContext(array $context): array
    {
        $raw = $context['references'] ?? $context['email_references'] ?? $context['References'] ?? null;
        $messageIds = [];
        $ccFromReferences = [];

        foreach (self::extractReferenceTokens($raw) as $token) {
            if (self::looksLikeEmailMessageId($token)) {
                $normalized = self::normalizeEmailMessageId($token);
                if ($normalized !== null) {
                    $messageIds[] = $normalized;
                }

                continue;
            }

            $email = trim($token, " \t\n\r\0\x0B<>\"'");
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $ccFromReferences[] = $email;
            }
        }

        $ccEmails = array_values(array_unique(array_merge(
            self::resolveEmailListFromContext($context, ['cc', 'cc_list', 'cc_emails', 'cc_email', 'CC']),
            $ccFromReferences
        )));

        return [
            'message_ids' => array_values(array_unique($messageIds)),
            'cc_emails' => $ccEmails,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return list<string>
     */
    public static function resolveBccEmailsFromContext(array $context, ?string $primaryRecipient = null): array
    {
        $bccEmails = self::resolveEmailListFromContext($context, [
            'bcc', 'bcc_list', 'bcc_emails', 'bcc_email', 'BCC',
        ]);
        $exclude = array_map(
            'strtolower',
            array_values(array_filter(array_unique(array_merge(
                [trim((string) $primaryRecipient)],
                self::resolveCcEmailsFromContext($context, $primaryRecipient)
            ))))
        );

        return array_values(array_filter(
            $bccEmails,
            static fn (string $email): bool => ! in_array(strtolower($email), $exclude, true)
        ));
    }

    /**
     * @param  array<string, mixed>  $context
     * @return list<string>
     */
    public static function resolveCcEmailsFromContext(array $context, ?string $primaryRecipient = null): array
    {
        $ccEmails = self::partitionEmailReferencesContext($context)['cc_emails'];
        $exclude = strtolower(trim((string) $primaryRecipient));

        if ($exclude === '') {
            return $ccEmails;
        }

        return array_values(array_filter(
            $ccEmails,
            static fn (string $email): bool => strtolower($email) !== $exclude
        ));
    }

    /**
     * @param  array<string, mixed>  $context
     * @return list<string>
     */
    public static function resolveEmailReferencesFromContext(array $context): array
    {
        return self::partitionEmailReferencesContext($context)['message_ids'];
    }

    /**
     * @param  list<string>  $additionalReferences
     * @return list<string>
     */
    public static function buildEmailReferenceChain(?string $parentMessageId, array $additionalReferences = []): array
    {
        $normalizedParent = self::normalizeEmailMessageId($parentMessageId);
        if ($normalizedParent === null) {
            return [];
        }

        $chain = [];
        foreach ($additionalReferences as $reference) {
            $normalized = self::normalizeEmailMessageId((string) $reference);
            if ($normalized === null) {
                continue;
            }
            $id = trim($normalized, '<>');
            if ($id !== '' && ! in_array($id, $chain, true)) {
                $chain[] = $id;
            }
        }

        $parentId = trim($normalizedParent, '<>');
        if ($parentId !== '' && ! in_array($parentId, $chain, true)) {
            $chain[] = $parentId;
        }

        return $chain;
    }

    public static function applyThreadReplySubject(string $subject, ?string $originalSubject = null): string
    {
        $originalSubject = trim((string) $originalSubject);
        if ($originalSubject === '') {
            return trim($subject);
        }

        if (preg_match('/^re:\s/i', $originalSubject)) {
            return $originalSubject;
        }

        return 'Re: '.$originalSubject;
    }

    public static function sendHtmlEmail(
        string $recipientEmail,
        string $html,
        string $subject,
        ?string $fromEmail = null,
        ?string $fromName = null,
        ?string $replyToEmail = null,
        ?string $emailUuid = null,
        ?string $threadSubject = null,
        array $referenceMessageIds = [],
        array $ccEmails = [],
        array $bccEmails = []
    ): void {
        // Use DMC-specific SMTP from emails_setup (not .env)
        self::applyEmailsSetupMailConfig();

        if ($emailUuid !== null && $emailUuid !== '') {
            $finalSubject = self::applyThreadReplySubject($subject, $threadSubject);
            $referenceChain = self::buildEmailReferenceChain($emailUuid, $referenceMessageIds);

            Mail::to($recipientEmail)->send(new AutomatedMail(
                $html,
                $finalSubject,
                $emailUuid,
                $fromEmail,
                $fromName,
                $replyToEmail,
                $referenceChain,
                $ccEmails,
                $bccEmails
            ));

            Log::info('Threaded email sent', [
                'to' => $recipientEmail,
                'cc' => $ccEmails,
                'bcc' => $bccEmails,
                'in_reply_to' => $emailUuid,
                'references' => $referenceChain,
                'subject' => $finalSubject,
                'thread_subject' => $threadSubject,
            ]);

            return;
        }

        Mail::to($recipientEmail)->send(new DmcMail(
            $html,
            $subject,
            $fromEmail,
            $fromName,
            $replyToEmail,
            $ccEmails,
            $bccEmails
        ));
    }

    /**
     * Send itinerary-style email using the DMC's ai_response setting (QTN or ITN).
     *
     * @param  array<string, mixed>  $tourData
     * @return bool|string
     */
    public static function sendTourItineraryEmailByAiResponse(string $recipientEmail, array $tourData = [], ?User $dmcUser = null)
    {
        $aiResponse = self::resolveDmcAiResponse($dmcUser);

        if ($aiResponse === null) {
            Log::info('Skipping itinerary email: DMC ai_response is not QTN or ITN', [
                'dmc_id' => $dmcUser?->userId,
                'email' => $recipientEmail,
            ]);

            return 'AI response type not configured (select QTN or ITN in user settings)';
        }

        if ($aiResponse === 'QTN') {
            return self::sendTourQuotationEmail($recipientEmail, $tourData);
        }

        return self::sendTourAutoBookedDmcEmail($recipientEmail, $tourData);
    }

    /**
     * Send tour proposal email to agent
     * Date: Current
     * 
     * @param int $agentId - Agent ID
     * @param int $tourId - Tour ID
     * @param string $tourDisplayId - Tour Display ID
     * @param array $tourData - Tour details (destination, dates, guests, etc.)
     * @return bool|string - true on success, error message on failure
     */
    public static function sendTourProposalEmail($agentId, $tourId, $tourDisplayId, $tourData = [], ?User $dmcUser = null)
    {
        try {
            // Get agent details
            $agent = Agent::where('agent_id', $agentId)->first();
            if (!$agent) {
                Log::error("Agent not found for tour proposal email", ['agent_id' => $agentId]);
                return "Agent not found";
            }

            // Get agent's agency details
            $agency = \App\Models\Agency::where('agency_id', $agent->agency_id)->first();
            $agencyName = $agency ? $agency->agency_name : 'Your Travel Agency';

            $tour = Tour::where('tour_id', $tourId)->first();

            if ($dmcUser === null && $tour && !empty($tour->dmc_id)) {
                $dmcUser = User::where('userId', $tour->dmc_id)->first();
            }
            if ($dmcUser === null) {
                $dmcId = self::getDmcId(\Illuminate\Support\Facades\Auth::user());
                if (!$dmcId) {
                    $dmcId = $agent->sales_manager_dmc;
                }
                if ($dmcId) {
                    $dmcUser = User::where('userId', $dmcId)->first();
                }
            }

            $aiResponse = self::resolveDmcAiResponse($dmcUser);
            if ($aiResponse === null) {
                return 'AI response type not configured (select QTN or ITN in user settings)';
            }

            $emailData = null;
            if ($tour) {
                $emailData = $aiResponse === 'QTN'
                    ? self::buildQuotationConfirmationEmailDataFromTour($tour)
                    : self::buildBookingConfirmationEmailDataFromTour($tour);
            }

            if (!$emailData) {
                $dmcName = $dmcUser
                    ? trim((string) ($dmcUser->company_name ?? $dmcUser->name ?? 'DMC'))
                    : 'DMC';

                $fallbackPayload = [
                    'dmc_name' => $dmcName,
                    'dmc_logo' => $dmcUser?->logo ?? null,
                    'dmc_label' => $dmcName,
                    'dmc_contact_email' => (string) ($dmcUser?->email ?? ''),
                    'tour_display_id' => $tourDisplayId,
                    'destination' => $tourData['destination'] ?? 'N/A',
                    'city' => $tourData['city'] ?? null,
                    'check_in_time' => $tourData['check_in_time'] ?? null,
                    'check_out_time' => $tourData['check_out_time'] ?? null,
                    'adult' => $tourData['adult'] ?? 0,
                    'child' => $tourData['child'] ?? 0,
                    'infant' => $tourData['infant'] ?? 0,
                    'agent_name' => $agent->name ?? 'Valued Partner',
                    'agency_name' => $agencyName,
                    'dashboard_link' => self::url(),
                    'booked_at' => now()->format('M d, Y H:i'),
                    'quoted_at' => now()->format('M d, Y H:i'),
                ];

                $emailData = $aiResponse === 'QTN'
                    ? self::normalizeQuotationEmailData($fallbackPayload)
                    : self::normalizeTourAutoBookedEmailData($fallbackPayload);
            }

            $emailData['agent_name'] = $agent->name ?? 'Valued Partner';
            $emailData['agency_name'] = $agencyName;
            $emailData['query_date'] = now()->format('M d, Y');

            $emailUuid = self::resolveEmailUuidFromContext($tourData);
            $threadSubject = self::resolveEmailSubjectFromContext($tourData);
            $referenceMessageIds = self::resolveEmailReferencesFromContext($tourData);
            $ccEmails = self::resolveCcEmailsFromContext($tourData, $agent->email);
            $bccEmails = self::resolveBccEmailsFromContext($tourData, $agent->email);
            if ($emailUuid !== null) {
                $emailData['email_uuid'] = $emailUuid;
            }
            if ($threadSubject !== null) {
                $emailData['subject'] = $threadSubject;
            }
            if ($referenceMessageIds !== []) {
                $emailData['references'] = $referenceMessageIds;
            }
            if ($ccEmails !== []) {
                $emailData['cc'] = $ccEmails;
            }
            if ($bccEmails !== []) {
                $emailData['bcc'] = $bccEmails;
            }

            return self::sendTourItineraryEmailByAiResponse($agent->email, $emailData, $dmcUser);

        } catch (\Exception $e) {
            Log::error('Tour proposal email sending failed', [
                'error' => $e->getMessage(),
                'agent_id' => $agentId,
                'tour_id' => $tourId,
                'trace' => $e->getTraceAsString()
            ]);
            return "Email sending failed: " . $e->getMessage();
        }
    }

    /**
     * Notify DMC when an external/day-level auto-booking creates a tour (uses DMC_email from package JSON).
     *
     * @param  string  $dmcEmail
     * @param  array<string, mixed>  $tourData
     * @return bool|string
     */
    public static function resolveEmailLogoUrl(?string $logo): ?string
    {
        $logo = trim((string) $logo);
        if ($logo === '') {
            return null;
        }

        if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://') || str_starts_with($logo, 'data:image')) {
            return $logo;
        }

        return url(ltrim($logo, '/'));
    }

    /**
     * Normalize tour auto-book email payload for Blade templates.
     * Shared by email/booking-confirmation (live auto-book emails).
     *
     * @param  array<string, mixed>  $tourData
     * @return array<string, mixed>
     */
    public static function normalizeTourAutoBookedEmailData(array $tourData = []): array
    {
        $cities = $tourData['cities'] ?? [];
        if (is_string($cities)) {
            $cities = array_filter(array_map('trim', explode(',', $cities)));
        }
        if (! is_array($cities)) {
            $cities = [];
        }
        $citiesLabel = implode(', ', array_values(array_filter(array_map('strval', $cities))));

        $emailData = [
            'dmc_name' => (string) ($tourData['dmc_name'] ?? 'DMC Partner'),
            'dmc_logo' => self::resolveEmailLogoUrl($tourData['dmc_logo'] ?? null),
            'dmc_label' => (string) ($tourData['dmc_label'] ?? ''),
            'dmc_contact_email' => (string) ($tourData['dmc_contact_email'] ?? ''),
            'tour_display_id' => (string) ($tourData['tour_display_id'] ?? 'N/A'),
            'diff' => (int) ($tourData['diff'] ?? 0),
            'requested_days' => (int) ($tourData['requested_days'] ?? 0),
            'available_days' => (int) ($tourData['available_days'] ?? 0),
            'requested_nights' => max(0, (int) ($tourData['requested_days'] ?? 0) - 1),
            'available_nights' => max(0, (int) ($tourData['available_days'] ?? 0) - 1),
            'is_partial_package' => (bool) ($tourData['is_partial_package'] ?? false),
            'partial_package_message' => (string) ($tourData['partial_package_message'] ?? ''),
            'country' => (string) ($tourData['country'] ?? ''),
            'destination' => (string) ($tourData['destination'] ?? 'N/A'),
            'cities_label' => $citiesLabel !== '' ? $citiesLabel : (string) ($tourData['city'] ?? ''),
            'check_in_date' => isset($tourData['check_in_time'])
                ? Carbon::parse($tourData['check_in_time'])->format('M d, Y')
                : ($tourData['check_in_date'] ?? 'N/A'),
            'check_out_date' => isset($tourData['check_out_time'])
                ? Carbon::parse($tourData['check_out_time'])->format('M d, Y')
                : ($tourData['check_out_date'] ?? 'N/A'),
            'adults' => (int) ($tourData['adults'] ?? $tourData['adult'] ?? 0),
            'children' => (int) ($tourData['children'] ?? $tourData['child'] ?? 0),
            'infants' => (int) ($tourData['infants'] ?? $tourData['infant'] ?? 0),
            'agent_name' => (string) ($tourData['agent_name'] ?? ''),
            'agency_name' => (string) ($tourData['agency_name'] ?? ''),
            'booked_at' => (string) ($tourData['booked_at'] ?? now()->format('M d, Y H:i')),
            'dashboard_link' => (string) ($tourData['dashboard_link'] ?? self::url()),
            'booked_services' => is_array($tourData['booked_services'] ?? null) ? $tourData['booked_services'] : [],
            'currency_code' => strtoupper(trim((string) ($tourData['currency_code'] ?? 'SGD'))) ?: 'SGD',
            'total_estimation' => round((float) ($tourData['total_estimation'] ?? 0), 2),
        ];
        $emailData['total_guests'] = $emailData['adults'] + $emailData['children'] + $emailData['infants'];
        $emailData['total_estimation_formatted'] = $emailData['currency_code'] . ' '
            . number_format($emailData['total_estimation'], 2);

        return $emailData;
    }

    public static function sendTourAutoBookedDmcEmail(string $dmcEmail, array $tourData = [])
    {
        $dmcEmail = trim($dmcEmail);
        if ($dmcEmail === '' || ! filter_var($dmcEmail, FILTER_VALIDATE_EMAIL)) {
            return 'Invalid DMC email address';
        }

        try {
            $emailUuid = self::resolveEmailUuidFromContext($tourData);
            $threadSubject = self::resolveEmailSubjectFromContext($tourData);
            $referenceMessageIds = self::resolveEmailReferencesFromContext($tourData);
            $ccEmails = self::resolveCcEmailsFromContext($tourData, $dmcEmail);
            $bccEmails = self::resolveBccEmailsFromContext($tourData, $dmcEmail);
            $emailData = self::normalizeTourAutoBookedEmailData($tourData);

            $subject = 'Booking #' . ($emailData['tour_display_id'] !== 'N/A' ? $emailData['tour_display_id'] : '') . ' â€” Travclicks';

            $html = view('email.booking-confirmation', $emailData)->render();
            $dmcContactEmail = trim((string) ($emailData['dmc_contact_email'] ?? ''));
            $fromEmail = (string) config('mail.from.address');
            $fromName = trim((string) config('mail.from.name', 'Travclicks'));
            $dmcLabel = trim((string) ($emailData['dmc_label'] ?? $emailData['dmc_name'] ?? ''));
            if ($dmcLabel !== '' && $dmcLabel !== 'DMC') {
                $fromName = $dmcLabel.' via '.$fromName;
            }
            $replyTo = ($dmcContactEmail !== '' && filter_var($dmcContactEmail, FILTER_VALIDATE_EMAIL))
                ? $dmcContactEmail
                : $fromEmail;

            self::sendHtmlEmail(
                $dmcEmail,
                $html,
                trim($subject),
                $fromEmail,
                $fromName,
                $replyTo,
                $emailUuid,
                $threadSubject,
                $referenceMessageIds,
                $ccEmails,
                $bccEmails
            );

            Log::info('Booking confirmation email sent', [
                'email' => $dmcEmail,
                'cc' => $ccEmails,
                'bcc' => $bccEmails,
                'tour_display_id' => $emailData['tour_display_id'],
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Tour auto-booked DMC email failed', [
                'dmc_email' => $dmcEmail,
                'error' => $e->getMessage(),
            ]);

            return 'Failed to send email: ' . $e->getMessage();
        }
    }

    /**
     * Send quotation email with full itinerary (email/quotation-confirmation.blade.php).
     *
     * @param  string  $recipientEmail
     * @param  array<string, mixed>  $tourData
     * @return bool|string
     */
    public static function sendTourQuotationEmail(string $recipientEmail, array $tourData = [])
    {
        $recipientEmail = trim($recipientEmail);
        if ($recipientEmail === '' || ! filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            return 'Invalid recipient email address';
        }

        try {
            $emailUuid = self::resolveEmailUuidFromContext($tourData);
            $threadSubject = self::resolveEmailSubjectFromContext($tourData);
            $referenceMessageIds = self::resolveEmailReferencesFromContext($tourData);
            $ccEmails = self::resolveCcEmailsFromContext($tourData, $recipientEmail);
            $bccEmails = self::resolveBccEmailsFromContext($tourData, $recipientEmail);
            $emailData = self::normalizeQuotationEmailData($tourData);

            $displayId = $emailData['tour_display_id'] !== 'N/A' ? $emailData['tour_display_id'] : '';
            $dmcName = (string) ($emailData['dmc_label'] ?? $emailData['dmc_name'] ?? 'DMC');
            $subject = 'Quotation #' . $displayId . ' from ' . $dmcName . ' â€” Travclicks';

            $html = view('email.quotation-confirmation', $emailData)->render();
            self::sendHtmlEmail(
                $recipientEmail,
                $html,
                trim($subject),
                null,
                null,
                null,
                $emailUuid,
                $threadSubject,
                $referenceMessageIds,
                $ccEmails,
                $bccEmails
            );

            Log::info('Quotation email sent', [
                'email' => $recipientEmail,
                'cc' => $ccEmails,
                'bcc' => $bccEmails,
                'tour_display_id' => $emailData['tour_display_id'],
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Tour quotation email failed', [
                'email' => $recipientEmail,
                'error' => $e->getMessage(),
            ]);

            return 'Failed to send email: ' . $e->getMessage();
        }
    }

    /** @var list<int> */
    public const MASTER_DMC_ROLE_IDS = [10, 19];

    /** @var list<int> */
    public const NORMAL_DMC_ROLE_IDS = [11, 20];

    /**
     * @return list<string>
     */
    public static function parseUserCountryList(?string $rawCountry): array
    {
        if (! is_string($rawCountry) || trim($rawCountry) === '') {
            return [];
        }

        $decoded = json_decode($rawCountry, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $parts = $decoded;
        } else {
            $parts = preg_split('/[,|]/', $rawCountry) ?: [];
        }

        $countries = [];
        foreach ($parts as $part) {
            $name = trim((string) $part);
            if ($name !== '' && ! in_array($name, $countries, true)) {
                $countries[] = $name;
            }
        }

        return $countries;
    }

    public static function normalizeCountryName(string $country): string
    {
        $trimmed = trim($country);
        if ($trimmed === '') {
            return '';
        }

        $match = Country::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($trimmed)])
            ->value('name');

        return is_string($match) && $match !== '' ? $match : $trimmed;
    }

    public static function countriesMatch(string $left, string $right): bool
    {
        $left = strtolower(trim(self::normalizeCountryName($left)));
        $right = strtolower(trim(self::normalizeCountryName($right)));

        return $left !== '' && $right !== '' && $left === $right;
    }

    public static function isMasterDmcUser(?User $user): bool
    {
        return $user !== null && in_array((int) $user->role_id, self::MASTER_DMC_ROLE_IDS, true);
    }

    /**
     * @return list<string>
     */
    public static function resolveSupportedCountriesForDmc(User $dmcUser): array
    {
        return array_map(
            static fn (string $country): string => self::normalizeCountryName($country),
            self::parseUserCountryList($dmcUser->country ?? null)
        );
    }

    public static function dmcSupportsDestinationCountry(User $dmcUser, string $requestedCountry): bool
    {
        $requestedCountry = trim($requestedCountry);
        if ($requestedCountry === '') {
            return true;
        }

        $supported = self::resolveSupportedCountriesForDmc($dmcUser);
        if ($supported === []) {
            return false;
        }

        foreach ($supported as $country) {
            if (self::countriesMatch($country, $requestedCountry)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<array{name: string, email: string, country: string}>
     */
    public static function findAlternateDmcsForCountry(User $selectedDmc, string $requestedCountry): array
    {
        $requestedCountry = trim($requestedCountry);
        if ($requestedCountry === '') {
            return [];
        }

        $masterId = (int) ($selectedDmc->master_dmc_id ?? 0);
        if ($masterId <= 0 && self::isMasterDmcUser($selectedDmc)) {
            $masterId = (int) $selectedDmc->userId;
        }

        if ($masterId <= 0) {
            return [];
        }

        $alternates = [];
        foreach (User::query()
            ->whereIn('role_id', self::NORMAL_DMC_ROLE_IDS)
            ->where('master_dmc_id', $masterId)
            ->where('userId', '!=', $selectedDmc->userId)
            ->get() as $dmc) {
            if (! self::dmcSupportsDestinationCountry($dmc, $requestedCountry)) {
                continue;
            }

            $alternates[] = [
                'name' => trim((string) ($dmc->company_name ?: $dmc->name ?: 'DMC')),
                'email' => trim((string) ($dmc->email ?? '')),
                'country' => self::normalizeCountryName(
                    self::resolveSupportedCountriesForDmc($dmc)[0] ?? $requestedCountry
                ),
            ];
        }

        return $alternates;
    }

    /**
     * @return array{
     *   supported: bool,
     *   requested_country: string,
     *   supported_countries: list<string>,
     *   alternate_dmcs: list<array{name: string, email: string, country: string}>
     * }
     */
    public static function validateDmcDestinationCountrySupport(User $dmcUser, string $requestedCountry): array
    {
        $requestedCountry = self::normalizeCountryName(trim($requestedCountry));

        return [
            'supported' => self::dmcSupportsDestinationCountry($dmcUser, $requestedCountry),
            'requested_country' => $requestedCountry,
            'supported_countries' => self::resolveSupportedCountriesForDmc($dmcUser),
            'alternate_dmcs' => self::findAlternateDmcsForCountry($dmcUser, $requestedCountry),
        ];
    }

    /**
     * @param  array<string, mixed>  $emailData
     * @return bool|string
     */
    public static function sendUnsupportedDestinationCountryEmail(
        string $recipientEmail,
        array $emailData = [],
        ?User $dmcUser = null
    ) {
        $recipientEmail = trim($recipientEmail);
        if ($recipientEmail === '' || ! filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            return 'Invalid recipient email address';
        }

        try {
            $emailUuid = self::resolveEmailUuidFromContext($emailData);
            $threadSubject = self::resolveEmailSubjectFromContext($emailData);
            $referenceMessageIds = self::resolveEmailReferencesFromContext($emailData);
            $ccEmails = self::resolveCcEmailsFromContext($emailData, $recipientEmail);
            $bccEmails = self::resolveBccEmailsFromContext($emailData, $recipientEmail);

            $selectedDmcName = (string) ($emailData['selected_dmc_name'] ?? $emailData['dmc_name'] ?? 'DMC');
            $requestedCountry = (string) ($emailData['requested_country'] ?? '');
            $alternateDmcs = is_array($emailData['alternate_dmcs'] ?? null) ? $emailData['alternate_dmcs'] : [];

            $viewData = [
                'recipient_name' => (string) ($emailData['recipient_name'] ?? 'Valued Partner'),
                'dmc_name' => (string) ($emailData['dmc_name'] ?? ''),
                'dmc_label' => (string) ($emailData['dmc_label'] ?? ''),
                'dmc_logo' => self::resolveEmailLogoUrl($emailData['dmc_logo'] ?? null),
                'dmc_contact_email' => (string) ($emailData['dmc_contact_email'] ?? ''),
                'selected_dmc_name' => $selectedDmcName,
                'requested_country' => $requestedCountry,
                'alternate_dmcs' => $alternateDmcs,
            ];

            $dmcName = $viewData['dmc_label'] ?: $viewData['dmc_name'] ?: $selectedDmcName;
            $subject = 'Destination not supported â€” '.$requestedCountry.' â€” '.$dmcName;
            $html = view('email.unsupported-destination-country', $viewData)->render();

            $dmcEmail = trim($viewData['dmc_contact_email']);
            if ($dmcEmail === '' && $dmcUser) {
                $dmcEmail = trim((string) ($dmcUser->email ?? ''));
            }

            $fromEmail = (string) config('mail.from.address');
            $fromName = trim((string) config('mail.from.name', 'Travclicks'));
            if ($dmcName !== '' && $dmcName !== 'DMC') {
                $fromName = $dmcName.' via '.$fromName;
            }
            $replyTo = ($dmcEmail !== '' && filter_var($dmcEmail, FILTER_VALIDATE_EMAIL))
                ? $dmcEmail
                : $fromEmail;

            self::sendHtmlEmail(
                $recipientEmail,
                $html,
                trim($subject),
                $fromEmail,
                $fromName,
                $replyTo,
                $emailUuid,
                $threadSubject,
                $referenceMessageIds,
                $ccEmails,
                $bccEmails
            );

            Log::info('Unsupported destination country email sent', [
                'email' => $recipientEmail,
                'selected_dmc' => $selectedDmcName,
                'requested_country' => $requestedCountry,
                'alternate_dmcs' => $alternateDmcs,
                'cc' => $ccEmails,
                'bcc' => $bccEmails,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Unsupported destination country email failed', [
                'email' => $recipientEmail,
                'error' => $e->getMessage(),
            ]);

            return 'Failed to send email: '.$e->getMessage();
        }
    }

    /**
     * Notify sender when external payload matching is 0 (incomplete travel details).
     *
     * @param  string  $recipientEmail
     * @param  array<string, mixed>  $emailData
     * @return bool|string
     */

    public static function sendIncompleteTravelDetailsEmail(string $recipientEmail, array $emailData = [], ?User $dmcUser = null)
    {
        $recipientEmail = trim($recipientEmail);
        if ($recipientEmail === '' || ! filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            return 'Invalid recipient email address';
        }

        try {
            $emailUuid = self::resolveEmailUuidFromContext($emailData);
            $threadSubject = self::resolveEmailSubjectFromContext($emailData);
            $referenceMessageIds = self::resolveEmailReferencesFromContext($emailData);
            $ccEmails = self::resolveCcEmailsFromContext($emailData, $recipientEmail);
            $bccEmails = self::resolveBccEmailsFromContext($emailData, $recipientEmail);
            $viewData = [
                'recipient_name' => (string) ($emailData['recipient_name'] ?? 'Valued Customer'),
                'dmc_name' => (string) ($emailData['dmc_name'] ?? ''),
                'dmc_label' => (string) ($emailData['dmc_label'] ?? ''),
                'dmc_logo' => self::resolveEmailLogoUrl($emailData['dmc_logo'] ?? null),
                'dmc_contact_email' => (string) ($emailData['dmc_contact_email'] ?? ''),
                'missing_items' => is_array($emailData['missing_items'] ?? null) ? $emailData['missing_items'] : [],
            ];

            $dmcName = $viewData['dmc_label'] ?: $viewData['dmc_name'] ?: 'DMC';
            $subject = 'Missing travel details â€” please check and resubmit â€” '.$dmcName;
            $html = view('email.incomplete-travel-details', $viewData)->render();

            $dmcEmail = trim($viewData['dmc_contact_email']);
            if ($dmcEmail === '' && $dmcUser) {
                $dmcEmail = trim((string) ($dmcUser->email ?? ''));
            }

            // SMTP auth is tied to MAIL_FROM; use DMC email only as Reply-To.
            $fromEmail = (string) config('mail.from.address');
            $fromName = trim((string) config('mail.from.name', 'Travclicks'));
            if ($dmcName !== '' && $dmcName !== 'DMC') {
                $fromName = $dmcName.' via '.$fromName;
            }
            $replyTo = ($dmcEmail !== '' && filter_var($dmcEmail, FILTER_VALIDATE_EMAIL))
                ? $dmcEmail
                : $fromEmail;

            self::sendHtmlEmail(
                $recipientEmail,
                $html,
                trim($subject),
                $fromEmail,
                $fromName,
                $replyTo,
                $emailUuid,
                $threadSubject,
                $referenceMessageIds,
                $ccEmails,
                $bccEmails
            );

            Log::info('Incomplete travel details email sent', [
                'email' => $recipientEmail,
                'cc' => $ccEmails,
                'bcc' => $bccEmails,
                'from' => $fromEmail,
                'reply_to' => $replyTo,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Incomplete travel details email failed', [
                'email' => $recipientEmail,
                'error' => $e->getMessage(),
            ]);

            return 'Failed to send email: '.$e->getMessage();
        }
    }

    /**
     * Build booking-confirmation email view data from a persisted tour + its orders.
     * Prices and itinerary lines come from order JSON (same source as live emails).
     *
     * @return array<string, mixed>|null
     */
    public static function buildBookingConfirmationEmailDataFromTour(Tour $tour): ?array
    {
        $orders = Order::where('tour_id', $tour->tour_id)->get();
        if ($orders->isEmpty()) {
            return null;
        }

        /** @var \App\Http\Controllers\Api\ExternalApiReceiveController $controller */
        $controller = app(\App\Http\Controllers\Api\ExternalApiReceiveController::class);
        $bookedServices = $controller->buildBookedServicesForEmailPublic($orders);

        $totalEstimation = round(array_sum(array_map(
            static fn (array $service): float => (float) ($service['price_value'] ?? 0),
            $bookedServices
        )), 2);

        $agent = $tour->agent_id
            ? Agent::where('agent_id', $tour->agent_id)->first()
            : null;

        $agency = $agent && $agent->agency_id
            ? Agency::where('agency_id', $agent->agency_id)->first()
            : null;

        $dmcUser = $tour->dmc_id
            ? User::where('userId', $tour->dmc_id)->first()
            : null;

        $dmcName = $dmcUser
            ? trim((string) ($dmcUser->company_name ?: $dmcUser->name ?: 'DMC'))
            : 'DMC';

        $currencyCode = self::resolveTourEmailCurrency($tour, $dmcUser);

        return self::normalizeTourAutoBookedEmailData([
            'dmc_name' => $dmcName,
            'dmc_logo' => $dmcUser->logo ?? null,
            'dmc_label' => $dmcName,
            'dmc_contact_email' => (string) ($dmcUser->email ?? ''),
            'tour_display_id' => (string) ($tour->display_id ?? $tour->tour_id),
            'country' => (string) ($tour->country ?? ''),
            'destination' => (string) ($tour->destination ?? 'N/A'),
            'city' => (string) ($tour->city ?? ''),
            'check_in_time' => $tour->check_in_time,
            'check_out_time' => $tour->check_out_time,
            'adult' => (int) ($tour->adult ?? 0),
            'child' => (int) ($tour->child ?? 0),
            'infant' => (int) ($tour->infant ?? 0),
            'agent_name' => (string) ($agent->name ?? ''),
            'agency_name' => (string) ($agency->agency_name ?? ''),
            'booked_at' => $tour->created_at
                ? Carbon::parse($tour->created_at)->format('M d, Y H:i')
                : now()->format('M d, Y H:i'),
            'booked_services' => $bookedServices,
            'total_estimation' => $totalEstimation,
            'currency_code' => $currencyCode,
            'dashboard_link' => self::url(),
            'is_partial_package' => false,
            'partial_package_message' => '',
            'requested_days' => 0,
            'available_days' => 0,
        ]);
    }

    /**
     * Normalize quotation email payload for email/quotation-confirmation.blade.php.
     *
     * @param  array<string, mixed>  $tourData
     * @return array<string, mixed>
     */
    public static function normalizeQuotationEmailData(array $tourData = []): array
    {
        // Full quotation layout (same data as single-tour-package/quotation.blade.php)
        if (isset($tourData['tour']) && is_object($tourData['tour'])) {
            $emailData = $tourData;
        } else {
            $emailData = self::normalizeTourAutoBookedEmailData($tourData);
        }

        $emailData['statusLabel'] = (string) ($tourData['statusLabel'] ?? 'TRAVEL QUOTATION');
        $emailData['heroText'] = (string) ($tourData['heroText']
            ?? "We've prepared a personalized travel quotation based on your request.");
        $emailData['quoted_at'] = (string) ($tourData['quoted_at'] ?? $emailData['booked_at'] ?? now()->format('M d, Y H:i'));

        return $emailData;
    }

    /**
     * Build quotation-confirmation email view data from a persisted tour + its orders.
     * Uses the same pricing / inclusions source as the PDF quotation blade.
     *
     * @return array<string, mixed>|null
     */
    public static function buildQuotationConfirmationEmailDataFromTour(Tour $tour): ?array
    {
        $pdfData = self::prepareEmailTemplateData($tour->tour_id);
        if (!$pdfData || empty($pdfData['tour'])) {
            return null;
        }

        $agent = $tour->agent_id
            ? Agent::where('agent_id', $tour->agent_id)->first()
            : null;

        $agency = $agent && $agent->agency_id
            ? Agency::where('agency_id', $agent->agency_id)->first()
            : null;

        $dmcUser = $tour->dmc_id
            ? User::where('userId', $tour->dmc_id)->first()
            : null;

        $dmcName = $dmcUser
            ? trim((string) ($dmcUser->company_name ?: $dmcUser->name ?: 'DMC'))
            : 'DMC';

        try {
            $encryptedTourId = Crypt::encrypt($tour->tour_id);
            $quotationPreviewUrl = route('tour.itinerary.preview', ['encryptedTourId' => $encryptedTourId]);
            $quotationDownloadUrl = route('tour.itinerary.pdf', [
                'tourId' => $tour->tour_id,
                'preview' => 0,
            ]);
        } catch (\Throwable $e) {
            $quotationPreviewUrl = self::url();
            $quotationDownloadUrl = self::url();
        }

        $baseCurrency = strtoupper((string) ($tour->currency ?? self::resolveTourEmailCurrency($tour, $dmcUser)));

        return self::normalizeQuotationEmailData(array_merge($pdfData, [
            'dmc_name' => $dmcName,
            'dmc_logo' => self::resolveEmailLogoUrl($dmcUser?->logo ?? null),
            'dmc_label' => $dmcName,
            'dmc_contact_email' => (string) ($dmcUser?->email ?? ''),
            'tour_display_id' => (string) ($tour->display_id ?? $tour->tour_id),
            'agent_name' => (string) ($agent->name ?? ''),
            'agency_name' => (string) ($agency->agency_name ?? ''),
            'quoted_at' => $tour->created_at
                ? Carbon::parse($tour->created_at)->format('M d, Y H:i')
                : now()->format('M d, Y H:i'),
            'dashboard_link' => $quotationPreviewUrl,
            'itineraryUrl' => $quotationPreviewUrl,
            'downloadUrl' => $quotationDownloadUrl,
            'detailsUrl' => $quotationPreviewUrl,
            'baseCurrency' => $baseCurrency,
            'selectedCurrency' => $baseCurrency,
            'exchangeRate' => 1.0,
            'logoType' => 'dmc',
            'quotationInformationHtml' => (string) ($pdfData['quotationInformationHtml'] ?? ''),
        ]));
    }

    /**
     * Resolve a tour for email preview: by tour_id / display_id, or the latest tour with orders.
     */
    public static function findTourForEmailPreview(?string $tourKey = null): ?Tour
    {
        $query = Tour::with(['agent', 'dmc']);

        if ($tourKey !== null && $tourKey !== '') {
            return $query->where(function ($q) use ($tourKey) {
                $q->where('tour_id', $tourKey)->orWhere('display_id', $tourKey);
            })->first();
        }

        return $query->whereHas('booking', function ($q) {
            $q->where('type', '!=', 'enquiry');
        })->latest()->first();
    }

    protected static function resolveTourEmailCurrency(Tour $tour, ?User $dmcUser = null): string
    {
        foreach ([$dmcUser->currency ?? null, $tour->currency ?? null] as $code) {
            $code = strtoupper(trim((string) $code));
            if ($code !== '') {
                return $code;
            }
        }

        $country = strtolower(trim((string) ($tour->country ?? $tour->destination ?? '')));

        return match (true) {
            str_contains($country, 'singapore') => 'SGD',
            str_contains($country, 'india') => 'INR',
            str_contains($country, 'thailand') => 'THB',
            str_contains($country, 'malaysia') => 'MYR',
            str_contains($country, 'indonesia') => 'IDR',
            str_contains($country, 'vietnam') || str_contains($country, 'viet nam') => 'VND',
            str_contains($country, 'philippines') => 'PHP',
            str_contains($country, 'united arab') || str_contains($country, 'dubai') => 'AED',
            default => 'SGD',
        };
    }

    /**
     * Send welcome email to agency when first DMC selects them
     * Date: Current
     * 
     * @param int $agencyId - Agency ID
     * @param int $dmcId - DMC ID that selected the agency
     * @return bool|string - true on success, error message on failure
     */
    public static function sendAgencyWelcomeEmail($agencyId, $dmcId)
    {
        try {
            // Get agency details
            $agency = \App\Models\Agency::where('agency_id', $agencyId)->first();
            if (!$agency) {
                Log::error("Agency not found for welcome email", ['agency_id' => $agencyId]);
                return "Agency not found";
            }

            // Get DMC details
            $dmc = User::where('userId', $dmcId)->first();
            $dmcName = $dmc ? ($dmc->company_name ?? $dmc->name ?? 'DMC') : 'DMC';
            $dmcLogo = $dmc ? ($dmc->logo ?? null) : null;

            // Prepare email data
            $emailData = [
                'agency_name' => $agency->agency_name ?? 'Valued Partner',
                'company_name' => $agency->agency_name ?? 'Travel Agency',
                'dmc_name' => $dmcName,
                'dmc_logo' => $dmcLogo,
            ];

            // Email subject
            $subject = "TRAVCLICKS - Super Charge Your Travel Business - Reimagine. Automate. Accelerate";

            // Render the email template
            try {
                $html = view('mails.agency_welcome', $emailData)->render();
            } catch (\Exception $e) {
                Log::error("Error rendering agency welcome email template", [
                    'error' => $e->getMessage(),
                    'agency_id' => $agencyId
                ]);
                return "Error rendering email template: " . $e->getMessage();
            }

            // Send the email
            try {
                Mail::to($agency->email)->send(new DmcMail($html, $subject));
                
                // Log successful email sending
                Log::info("Agency welcome email sent successfully", [
                    'agency_id' => $agencyId,
                    'agency_email' => $agency->email,
                    'dmc_id' => $dmcId
                ]);
                
                return true;
            } catch (\Exception $e) {
                Log::error("Failed to send agency welcome email", [
                    'error' => $e->getMessage(),
                    'agency_id' => $agencyId,
                    'agency_email' => $agency->email,
                    'dmc_id' => $dmcId
                ]);
                return "Failed to send email: " . $e->getMessage();
            }

        } catch (\Exception $e) {
            Log::error('Agency welcome email sending failed', [
                'error' => $e->getMessage(),
                'agency_id' => $agencyId,
                'dmc_id' => $dmcId,
                'trace' => $e->getTraceAsString()
            ]);
            return "Email sending failed: " . $e->getMessage();
        }
    }

    /**
     * Send partnership invitation email to agency when additional DMC selects them
     * Date: Current
     * 
     * @param int $agencyId - Agency ID
     * @param int $dmcId - DMC ID that selected the agency
     * @return bool|string - true on success, error message on failure
     */
    public static function sendAgencyPartnershipEmail($agencyId, $dmcId)
    {
        try {
            // Get agency details
            $agency = \App\Models\Agency::where('agency_id', $agencyId)->first();
            if (!$agency) {
                Log::error("Agency not found for partnership email", ['agency_id' => $agencyId]);
                return "Agency not found";
            }

            // Get DMC details
            $dmc = User::where('userId', $dmcId)->first();
            $dmcName = $dmc ? ($dmc->company_name ?? $dmc->name ?? 'DMC') : 'DMC';
            $dmcLogo = $dmc ? ($dmc->logo ?? null) : null;
            $dmcEmail = $dmc ? ($dmc->email ?? null) : null;
            $dmcPhone = $dmc ? ($dmc->phone_number ?? null) : null;

            // Prepare email data
            $emailData = [
                'agency_name' => $agency->agency_name ?? 'Valued Partner',
                'company_name' => $agency->agency_name ?? 'Travel Agency',
                'dmc_name' => $dmcName,
                'dmc_logo' => $dmcLogo,
                'dmc_email' => $dmcEmail,
                'dmc_phone' => $dmcPhone,
                'dashboard_link' => self::url(),
            ];

            // Email subject
            $subject = "ðŸŒ You've Been Invited to Partner with {$dmcName} on Travclicks";

            // Render the email template
            try {
                $html = view('mails.agency_partnership_invite', $emailData)->render();
            } catch (\Exception $e) {
                Log::error("Error rendering agency partnership email template", [
                    'error' => $e->getMessage(),
                    'agency_id' => $agencyId
                ]);
                return "Error rendering email template: " . $e->getMessage();
            }

            // Send the email
            try {
                Mail::to($agency->email)->send(new DmcMail($html, $subject));
                
                // Log successful email sending
                Log::info("Agency partnership email sent successfully", [
                    'agency_id' => $agencyId,
                    'agency_email' => $agency->email,
                    'dmc_id' => $dmcId,
                    'dmc_name' => $dmcName
                ]);
                
                return true;
            } catch (\Exception $e) {
                Log::error("Failed to send agency partnership email", [
                    'error' => $e->getMessage(),
                    'agency_id' => $agencyId,
                    'agency_email' => $agency->email,
                    'dmc_id' => $dmcId
                ]);
                return "Failed to send email: " . $e->getMessage();
            }

        } catch (\Exception $e) {
            Log::error('Agency partnership email sending failed', [
                'error' => $e->getMessage(),
                'agency_id' => $agencyId,
                'dmc_id' => $dmcId,
                'trace' => $e->getTraceAsString()
            ]);
            return "Email sending failed: " . $e->getMessage();
        }
    }

    /**
     * App root URL (strips /backadm-dmc from APP_URL). Defaults to login path.
     */
    public static function url(string $path = 'login'): string
    {
        $base = (string) config('app.url');
        $root = preg_replace('#/backadm-dmc/?$#', '', $base);

        return rtrim($root, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Send negotiation update email to agent
     * Date: Current
     * 
     * @param int $agentId - Agent ID
     * @param int $tourId - Tour ID
     * @param string $tourDisplayId - Tour Display ID
     * @param array $negotiationData - Negotiation details (prices, comment, status, etc.)
     * @return bool|string - true on success, error message on failure
     */
    public static function sendNegotiationEmail($agentId, $tourId, $tourDisplayId, $negotiationData = [])
    {
        try {
            // Get agent details
            $agent = Agent::where('agent_id', $agentId)->first();
            if (!$agent) {
                Log::error("Agent not found for negotiation email", ['agent_id' => $agentId]);
                return "Agent not found";
            }

            // Check if agent has valid email
            if (empty($agent->email)) {
                Log::error("Agent email not found", ['agent_id' => $agentId]);
                return "Agent email not found";
            }

            // Get agent's agency details
            $agency = Agency::where('agency_id', $agent->agency_id)->first();
            $agencyName = $agency ? $agency->agency_name : 'Your Travel Agency';

            // Get DMC details
            $dmcId = $negotiationData['dmc_id'] ?? null;
            if (!$dmcId && isset($negotiationData['tour'])) {
                $dmcId = $negotiationData['tour']->dmc_id ?? null;
            }
            
            $dmc = User::where('userId', $dmcId)->first();
            $dmcName = $dmc ? ($dmc->company_name ?? $dmc->name ?? 'DMC') : 'DMC';
            $dmcLogo = $dmc ? ($dmc->logo ?? null) : null;
            $dmcEmail = $dmc ? ($dmc->email ?? null) : null;
            $dmcPhone = $dmc ? ($dmc->phone_number ?? null) : null;

            // Get tour details if available
            $tour = $negotiationData['tour'] ?? Tour::where('tour_id', $tourId)->first();
            $tourStatus = $tour ? $tour->tour_status : 'N/A';
            $destination = $tour ? ($tour->tour_destination ?? $tour->destination ?? null) : null;
            $city = $tour ? ($tour->tour_city ?? $tour->city ?? null) : null;

            // Prepare email data
            $emailData = [
                'agent_name' => $agent->name ?? 'Valued Partner',
                'agency_name' => $agencyName,
                'dmc_name' => $dmcName,
                'dmc_logo' => $dmcLogo,
                'dmc_email' => $dmcEmail,
                'dmc_phone' => $dmcPhone,
                'tour_display_id' => $tourDisplayId,
                'tour_status' => $tourStatus,
                'destination' => $destination,
                'city' => $city,
                'actual_amount' => $negotiationData['actual_amount'] ?? 0,
                'negotiated_amount' => $negotiationData['negotiated_amount'] ?? 0,
                'previous_negotiated_amount' => $negotiationData['previous_negotiated_amount'] ?? null,
                'comment' => $negotiationData['comment'] ?? null,
                'currency' => $negotiationData['currency'] ?? '$',
                'dashboard_link' => self::url(),
                'submission_date' => now()->format('M d, Y'),
            ];

            // Email subject
            $subject = "ðŸ’° Price Negotiation Submitted - Tour {$tourDisplayId}";

            // Render the email template
            try {
                $html = view('mails.negotiation_update_agent', $emailData)->render();
            } catch (\Exception $e) {
                Log::error("Error rendering negotiation email template", [
                    'error' => $e->getMessage(),
                    'tour_id' => $tourId,
                    'agent_id' => $agentId
                ]);
                return "Error rendering email template: " . $e->getMessage();
            }

            // Send the email
            try {
                Mail::to($agent->email)->send(new DmcMail($html, $subject));
                
                // Log successful email sending
                Log::info("Negotiation email sent successfully", [
                    'agent_id' => $agentId,
                    'agent_email' => $agent->email,
                    'tour_id' => $tourId,
                    'tour_display_id' => $tourDisplayId,
                    'negotiated_amount' => $negotiationData['negotiated_amount'] ?? 0
                ]);
                
                return true;
            } catch (\Exception $e) {
                Log::error("Failed to send negotiation email", [
                    'error' => $e->getMessage(),
                    'agent_id' => $agentId,
                    'agent_email' => $agent->email,
                    'tour_id' => $tourId
                ]);
                return "Failed to send email: " . $e->getMessage();
            }

        } catch (\Exception $e) {
            Log::error('Negotiation email sending failed', [
                'error' => $e->getMessage(),
                'agent_id' => $agentId,
                'tour_id' => $tourId,
                'trace' => $e->getTraceAsString()
            ]);
            return "Email sending failed: " . $e->getMessage();
        }
    }

    public static function downloadTourPdf($tourId, $targetCurrency = null, $preview = false, $quotationInformationHtml = null, $viewName = 'single-tour-package.quotation', $logoType = 'dmc')
    {
        $tour = Tour::where('tour_id', $tourId)->first();
        if (!$tour) {
            return null;
        }

        // Currency handling
        // Base currency = tour DMC currency (e.g. VND for a Vietnam DMC), not a hardcoded SGD.
        $baseCurrency = self::resolveTourDisplayCurrency($tour);
        $selectedCurrency = $targetCurrency ? strtoupper($targetCurrency) : $baseCurrency;
        $exchangeRate = 1.0;

        if ($selectedCurrency !== $baseCurrency) {
            $rate = \App\Helpers\CurrencyHelper::getExchangeRate($baseCurrency, $selectedCurrency);
            if ($rate && is_numeric($rate) && $rate > 0) {
                $exchangeRate = (float) $rate;
            }
        }
        
        $orders = Order::where('tour_id', $tourId)
            ->where('status', 1)
            ->orderBy('booking_id')
            ->get();

        $servicesByDate = self::groupServicesByDate($orders);
        ksort($servicesByDate);

        $servicesByType = self::groupServicesByType($orders, $tour);

        // Fetch DMC user data for logo and company name
        $dmcUser = null;
        $dmcLogo = null;
        $dmcCompanyName = null;
        $dmcDetails = [
            'name' => 'N/A',
            'address' => 'N/A',
            'city' => 'N/A',
            'country' => 'N/A',
            'email' => 'N/A',
            'email2' => 'N/A',
            'phone' => 'N/A',
            'postal_pin' => 'N/A',
            'company_name' => 'N/A',
        ];
        
        if (!empty($tour->dmc_id)) {
            $dmcUser = User::where('userId', $tour->dmc_id)->first();
            if ($dmcUser) {
                $logoUrl = $dmcUser->logo ?? null;
                $dmcCompanyName = $dmcUser->company_name ?? null;
                $dmc_name = $dmcUser->name ?? null;
                // Populate DMC details
                $dmcDetails = [
                    'name' => $dmcUser->name ?? 'N/A',
                    'address' => $dmcUser->address ?? 'N/A',
                    'city' => $dmcUser->city ?? 'N/A',
                    'country' => $dmcUser->user_country ?? $dmcUser->country ?? 'N/A',
                    'email' => $dmcUser->email ?? 'N/A',
                    'email2' => 'N/A', // Second email field - can be extended if needed
                    'phone' => ($dmcUser->country_code ? '+' . $dmcUser->country_code . ' ' : '') . ($dmcUser->phone ?? 'N/A'),
                    'postal_pin' => 'N/A', // Postal/Pin - can be added to User model if needed
                    'company_name' => $dmcUser->company_name ?? 'N/A',
                ];
                
                // Convert logo URL to base64 for PDF display
                if (!empty($logoUrl)) {
                    try {
                        // Determine MIME type from URL extension
                        $mimeType = 'image/png'; // default
                        $urlPath = parse_url($logoUrl, PHP_URL_PATH);
                        if ($urlPath) {
                            $extension = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION));
                            $mimeMap = [
                                'jpg' => 'image/jpeg',
                                'jpeg' => 'image/jpeg',
                                'png' => 'image/png',
                                'gif' => 'image/gif',
                                'webp' => 'image/webp',
                                'svg' => 'image/svg+xml',
                            ];
                            $mimeType = $mimeMap[$extension] ?? 'image/png';
                        }
                        
                        // Try using Laravel HTTP client first
                        $response = Http::timeout(10)->get($logoUrl);
                        if ($response->successful()) {
                            $imageContent = $response->body();
                            if (!empty($imageContent) && strlen($imageContent) > 100) {
                                // Try to get MIME type from response headers if available
                                $contentType = $response->header('Content-Type');
                                if ($contentType && strpos($contentType, 'image/') !== false) {
                                    $mimeType = explode(';', $contentType)[0]; // Remove charset if present
                                }
                                $base64 = base64_encode($imageContent);
                                $dmcLogo = 'data:' . $mimeType . ';base64,' . $base64;
                                Log::info('DMC Logo converted to base64', [
                                    'url' => $logoUrl,
                                    'mime_type' => $mimeType,
                                    'base64_length' => strlen($base64)
                                ]);
                            }
                        } else {
                            // Fallback to file_get_contents
                            $imageContent = @file_get_contents($logoUrl);
                            if ($imageContent !== false && !empty($imageContent) && strlen($imageContent) > 100) {
                                $base64 = base64_encode($imageContent);
                                $dmcLogo = 'data:' . $mimeType . ';base64,' . $base64;
                                Log::info('DMC Logo converted to base64 (fallback method)', [
                                    'url' => $logoUrl,
                                    'mime_type' => $mimeType,
                                    'base64_length' => strlen($base64)
                                ]);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to fetch DMC logo for PDF', [
                            'logo_url' => $logoUrl,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }

        // Fetch Agent/Agency information
        $agentDetails = [
            'name' => 'N/A',
            'address' => 'N/A',
            'contact_person' => 'N/A',
            'phone' => 'N/A',
            'email' => 'N/A',
        ];
        $userAgencyForHeader = null;

        if (!empty($tour->agent_id)) {
            $agent = Agent::with('agency')->where('agent_id', $tour->agent_id)->first();
            if ($agent) {
                $agency = $agent->agency;
                if ($agency) {
                    $userAgencyForHeader = $agency;
                }

                // Use agency data if available, otherwise fall back to agent data
                $agentDetails = [
                    'name' => ($agency && $agency->agency_name) ? $agency->agency_name : ($agent->name ?? 'N/A'),
                    'address' => ($agency && $agency->address) ? $agency->address : 'N/A',
                    'contact_person' => ($agency && $agency->contact_person) ? $agency->contact_person : ($agent->name ?? 'N/A'),
                    'phone' => ($agency && $agency->phone) ? $agency->phone : ($agent->phone ?? 'N/A'),
                    'email' => ($agency && $agency->email) ? $agency->email : ($agent->email ?? 'N/A'),
                ];
            }
        }

        $logoType = strtolower((string) $logoType) === 'agency' ? 'agency' : 'dmc';
        if ($logoType === 'agency' && !$userAgencyForHeader) {
            $logoType = 'dmc';
        }

        // Proposal details
        $proposalDetails = [
            'proposal_date' => now()->format('d M Y'),
            'proposal_validity' => 'N/A',
            'proposal_sent_by' => $dmc_name ?? 'N/A',
        ];

        // Get booking and guest details from orders
        $bookingDetails = [
            'booking_id' => $tour->display_id ?? ('Tour #' . ($tour->tour_id ?? 'N/A')),
            'lead_guest_name' => 'N/A',
            'email' => 'N/A',
            'phone' => 'N/A',
            'address' => 'N/A',
            'city' => 'N/A',
            'postal_code' => 'N/A',
            'no_of_adults' => (int)($tour->adult ?? 0),
            'no_of_children' => (int)($tour->child ?? 0),
            'no_of_infants' => (int)($tour->infant ?? 0),
        ];

        // Try to get guest information from first order
        if ($orders->count() > 0) {
            $firstOrder = $orders->first();
            $orderData = $firstOrder->data;
            if (is_string($orderData)) {
                $orderData = json_decode($orderData, true);
            }
            
            if (is_array($orderData) && !empty($orderData)) {
                $firstItem = is_array($orderData[0]) ? $orderData[0] : $orderData;
                
                // Extract guest information
                $bookingDetails['lead_guest_name'] = $firstItem['fullName'] ?? $firstItem['name'] ?? 'N/A';
                $bookingDetails['email'] = $firstItem['email'] ?? 'N/A';
                $bookingDetails['gender'] = $firstItem['gender'] ?? 'N/A';
                $bookingDetails['passenger_type'] = $firstItem['passenger_type'] ?? 'N/A';
                $bookingDetails['salutation'] = $firstItem['salutation'] ?? 'N/A';
                
                // Format phone number with country code if available
                $phone = $firstItem['phone'] ?? 'N/A';
                $bookingDetails['phone'] = $phone;
                
                
                // Combine address1 and address2 for full address
                $address1 = $firstItem['address1'] ?? '';
                $address2 = $firstItem['address2'] ?? '';
                if (!empty($address1) || !empty($address2)) {
                    $bookingDetails['address'] = trim($address1 . ' ' . $address2);
                }
                
                // State
                $bookingDetails['city'] = $firstItem['state'] ?? 'N/A';
                
                // Postal/Zip code
                $bookingDetails['postal_code'] = $firstItem['zip'] ?? 'N/A';
            }
        }
        
        // Initialize passengers array
        $allPassengers = [];
        
        // Extract passengers from orders if available
        if ($orders->count() > 0) {
            foreach ($orders as $order) {
                $orderData = $order->data;
                if (is_string($orderData)) {
                    $orderData = json_decode($orderData, true);
                }
                
                if (is_array($orderData) && !empty($orderData)) {
                    $orderItem = is_array($orderData[0]) ? $orderData[0] : $orderData;
                    
                    // Check if this order has passengers array
                    if (isset($orderItem['passengers']) && is_array($orderItem['passengers'])) {
                        foreach ($orderItem['passengers'] as $passenger) {
                            if (is_array($passenger) && !empty($passenger)) {
                                $allPassengers[] = $passenger;
                            }
                        }
                    }
                }
            }
        }
        
        // Extract main guest from mainguest column
        if (!empty($tour->mainguest)) {
            try {
                $mainguestData = is_string($tour->mainguest) ? json_decode($tour->mainguest, true) : $tour->mainguest;
                if (is_array($mainguestData) && !empty($mainguestData)) {
                    // Map mainguest fields to bookingDetails (for lead guest info)
                    if (($bookingDetails['lead_guest_name'] === 'N/A' || empty($bookingDetails['lead_guest_name'])) && !empty($mainguestData['full_name'])) {
                        $bookingDetails['lead_guest_name'] = $mainguestData['full_name'];
                    }
                    if (empty($bookingDetails['email']) && !empty($mainguestData['email'])) {
                        $bookingDetails['email'] = $mainguestData['email'];
                    }
                    if (empty($bookingDetails['phone']) && !empty($mainguestData['phone'])) {
                        $phone = $mainguestData['phone'];
                        // Add country code if available
                        if (!empty($mainguestData['country_code'])) {
                            $phone = '+' . $mainguestData['country_code'] . ' ' . $phone;
                        }
                        $bookingDetails['phone'] = $phone;
                    }
                    // Combine address1 and address2
                    if (empty($bookingDetails['address'])) {
                        $address1 = $mainguestData['address1'] ?? '';
                        $address2 = $mainguestData['address2'] ?? '';
                        if (!empty($address1) || !empty($address2)) {
                            $bookingDetails['address'] = trim($address1 . ' ' . $address2);
                        }
                    }
                    if (empty($bookingDetails['city']) && !empty($mainguestData['state'])) {
                        $bookingDetails['city'] = $mainguestData['state'];
                    }
                    if (empty($bookingDetails['postal_code']) && !empty($mainguestData['zip'])) {
                        $bookingDetails['postal_code'] = $mainguestData['zip'];
                    }
                    
                    // Add main guest to passengers array
                    $mainGuest = [
                        'salutation' => $mainguestData['salutation'] ?? 'Mr',
                        'first_name' => $mainguestData['full_name'] ?? '',
                        'passenger_type' => $mainguestData['passenger_type'] ?? 'N/A',
                        'gender' => $mainguestData['gender'] ?? 'N/A',
                        'mobile_phone' => $mainguestData['phone'] ?? '',
                        'phone' => $mainguestData['phone'] ?? '',
                        'email' => $mainguestData['email'] ?? '',
                    ];
                    // Add country code to phone if available
                    if (!empty($mainguestData['country_code']) && !empty($mainGuest['phone'])) {
                        $formattedPhone = '+' . $mainguestData['country_code'] . ' ' . $mainGuest['phone'];
                        $mainGuest['mobile_phone'] = $formattedPhone;
                        $mainGuest['phone'] = $formattedPhone;
                    }
                    $allPassengers[] = $mainGuest;
                }
            } catch (\Exception $e) {
                // If parsing fails, keep existing values
                Log::warning('Failed to parse mainguest data from tour', [
                    'tour_id' => $tourId,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Extract additional guests from additionalguest column
        if (!empty($tour->additionalguest)) {
            try {
                $additionalGuestsData = is_string($tour->additionalguest) ? json_decode($tour->additionalguest, true) : $tour->additionalguest;
                if (is_array($additionalGuestsData)) {
                    // Check if it's an array of guests or a single guest object
                    if (isset($additionalGuestsData[0]) && is_array($additionalGuestsData[0])) {
                        // Array of guests
                        foreach ($additionalGuestsData as $guestData) {
                            if (is_array($guestData) && !empty($guestData)) {
                                $additionalGuest = [
                                    'salutation' => $guestData['salutation'] ?? 'Mr',
                                    'first_name' => $guestData['full_name'] ?? $guestData['name'] ?? '',
                                    'passenger_type' => $guestData['passenger_type'] ?? 'N/A',
                                    'gender' => $guestData['gender'] ?? 'N/A',
                                    'mobile_phone' => $guestData['phone'] ?? '',
                                    'phone' => $guestData['phone'] ?? '',
                                    'email' => $guestData['email'] ?? '',
                                ];
                                // Add country code to phone if available
                                if (!empty($guestData['country_code']) && !empty($additionalGuest['phone'])) {
                                    $formattedPhone = '+' . $guestData['country_code'] . ' ' . $additionalGuest['phone'];
                                    $additionalGuest['mobile_phone'] = $formattedPhone;
                                    $additionalGuest['phone'] = $formattedPhone;
                                }
                                $allPassengers[] = $additionalGuest;
                            }
                        }
                    } else {
                        // Single guest object
                        if (is_array($additionalGuestsData) && !empty($additionalGuestsData)) {
                            $additionalGuest = [
                                'salutation' => $additionalGuestsData['salutation'] ?? 'Mr',
                                'first_name' => $additionalGuestsData['full_name'] ?? $additionalGuestsData['name'] ?? '',
                                'passenger_type' => $additionalGuestsData['passenger_type'] ?? 'N/A',
                                'gender' => $additionalGuestsData['gender'] ?? 'N/A',
                                'mobile_phone' => $additionalGuestsData['phone'] ?? '',
                                'phone' => $additionalGuestsData['phone'] ?? '',
                                'email' => $additionalGuestsData['email'] ?? '',
                            ];
                            // Add country code to phone if available
                            if (!empty($additionalGuestsData['country_code']) && !empty($additionalGuest['phone'])) {
                                $formattedPhone = '+' . $additionalGuestsData['country_code'] . ' ' . $additionalGuest['phone'];
                                $additionalGuest['mobile_phone'] = $formattedPhone;
                                $additionalGuest['phone'] = $formattedPhone;
                            }
                            $allPassengers[] = $additionalGuest;
                        }
                    }
                }
            } catch (\Exception $e) {
                // If parsing fails, keep existing values
                Log::warning('Failed to parse additionalguest data from tour', [
                    'tour_id' => $tourId,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Remove duplicate passengers (by name and email)
        $uniquePassengers = [];
        $seenPassengers = [];
        foreach ($allPassengers as $passenger) {
            $key = strtolower(trim(($passenger['first_name'] ?? $passenger['name'] ?? '') . '|' . ($passenger['email'] ?? '')));
            if (!isset($seenPassengers[$key])) {
                $seenPassengers[$key] = true;
                $uniquePassengers[] = $passenger;
            }
        }
        
        // Add passengers to bookingDetails
        if (!empty($uniquePassengers)) {
            $bookingDetails['passengers'] = $uniquePassengers;
        }

        // Travel details
        $travelDetails = [
            'destination' => $tour->destination ?? $tour->tour_destination ?? 'N/A',
            'travel_date_from' => $tour->check_in_time ? \Carbon\Carbon::parse($tour->check_in_time)->format('l- d/m/Y') : 'N/A',
            'travel_date_to' => $tour->check_out_time ? \Carbon\Carbon::parse($tour->check_out_time)->format('l- d/m/Y') : 'N/A',
            'duration' => 'N/A',
        ];

        // Calculate duration
        if ($tour->check_in_time && $tour->check_out_time) {
            try {
                $checkIn = \Carbon\Carbon::parse($tour->check_in_time);
                $checkOut = \Carbon\Carbon::parse($tour->check_out_time);
                $duration = $checkIn->diffInDays($checkOut);
                $travelDetails['duration'] = $duration . ' Day' . ($duration > 1 ? 's' : '');
            } catch (\Exception $e) {
                // Keep as N/A
            }
        }

        // Calculate tour prices
        $tourPrices = self::calculateTourPrices($tourId);
        // Format hotels for Excel-like display
        $hotelOptions = self::formatHotelsForPdf($orders, $tour, $tourPrices);
        
        // Get DMC ID - first try from tour, otherwise from current user
        $dmcIdForBankDetails = null;
        if (!empty($tour->dmc_id)) {
            $dmcIdForBankDetails = $tour->dmc_id;
        } else {
            // Fallback to getting DMC ID from current user
            $currentUser = Auth::user();
            if ($currentUser) {
                $dmcIdForBankDetails = self::getDmcId($currentUser);
            }
        }
        
        // Fetch bank details from bank_details table based on DMC ID
        $bankDetails = [];
        $termsAndConditions = '';
        $paymentTerms = [];
        
        if ($dmcIdForBankDetails) {
            $bankDetailRecord = BankDetail::where('dmc_id', $dmcIdForBankDetails)
                ->where('is_active', 1)
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($bankDetailRecord) {
                // Extract bank details
                $bankDetails = [
                    'account_name' => $bankDetailRecord->account_name ?? '',
                    'account_number' => $bankDetailRecord->account_number ?? '',
                    'bank_address' => $bankDetailRecord->bank_address ?? '',
                    'ifsc' => $bankDetailRecord->ifsc ?? '',
                    'swift_bic_iban' => $bankDetailRecord->swift_bic_iban ?? '',
                    'bank_code' => $bankDetailRecord->bank_code ?? '',
                    'branch_code' => $bankDetailRecord->branch_code ?? '',
                    'aba_routing' => $bankDetailRecord->aba_routing ?? '',
                ];
                
                // Extract terms and conditions
                $termsAndConditions = $bankDetailRecord->terms_and_conditions ?? '';
                
                // Extract payment terms (already cast as array in model)
                $paymentTerms = $bankDetailRecord->payment_terms ?? [];
                if (is_string($paymentTerms)) {
                    $paymentTerms = json_decode($paymentTerms, true) ?? [];
                }
                if (!is_array($paymentTerms)) {
                    $paymentTerms = [];
                }
            }
        }
        
        // Fallback to DMC user bank_details if bank_details table record not found
        if (empty($bankDetails) && $dmcUser && isset($dmcUser->bank_details)) {
            $bankDetailsData = is_string($dmcUser->bank_details) ? json_decode($dmcUser->bank_details, true) : $dmcUser->bank_details;
            if (is_array($bankDetailsData)) {
                $bankDetails = $bankDetailsData;
            }
        }
        
        // Exclusions (can be extended to fetch from database if needed)
        $exclusions = '';
        
        try {
            // Configure DomPDF options to work without GD if possible
            //$pdf = Pdf::loadView('single-tour-package.pdf-itinerary', [
            $pdf = Pdf::loadView($viewName, [
                'tour' => $tour,
                'servicesByDate' => $servicesByDate,
                'servicesByType' => $servicesByType,
                'generatedAt' => now(),
                'dmcLogo' => $dmcLogo,
                'dmcCompanyName' => $dmcCompanyName,
                'dmcDetails' => $dmcDetails,
                'agentDetails' => $agentDetails,
                'proposalDetails' => $proposalDetails,
                'bookingDetails' => $bookingDetails,
                'travelDetails' => $travelDetails,
                'tourPrices' => $tourPrices,
                'hotelOptions' => $hotelOptions,
                'bankDetails' => $bankDetails,
                'termsAndConditions' => $termsAndConditions,
                'exclusions' => $exclusions,
                'paymentTerms' => $paymentTerms,
                'baseCurrency' => $baseCurrency,
                'selectedCurrency' => $selectedCurrency,
                'exchangeRate' => $exchangeRate,
                'quotationInformationHtml' => $quotationInformationHtml,
                'logoType' => $logoType,
                'user_agency' => $userAgencyForHeader,
            ]);
            
            $pdf->setPaper('a4');
            $pdf->setOption('enable-php', false);
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', false);

            if ($preview) {
                return $pdf->stream("tour-quotation.pdf");
            }

            return $pdf->download("tour-quotation.pdf");
        } catch (\Exception $e) {
            // If GD is required and not available, try without logo
            if (strpos($e->getMessage(), 'GD extension') !== false && !empty($dmcLogo)) {
                Log::warning('PDF generation failed with logo, retrying without logo', [
                    'error' => $e->getMessage()
                ]);
                
                // Retry without logo
                //$pdf = Pdf::loadView('single-tour-package.pdf-itinerary', [
                $pdf = Pdf::loadView($viewName, [
                    'tour' => $tour,
                    'servicesByDate' => $servicesByDate,
                    'servicesByType' => $servicesByType,
                    'generatedAt' => now(),
                    'dmcLogo' => null, // Remove logo
                    'dmcCompanyName' => $dmcCompanyName,
                    'dmcDetails' => $dmcDetails,
                    'agentDetails' => $agentDetails,
                    'proposalDetails' => $proposalDetails,
                    'bookingDetails' => $bookingDetails,
                    'travelDetails' => $travelDetails,
                    'tourPrices' => $tourPrices,
                    'hotelOptions' => $hotelOptions,
                    'bankDetails' => $bankDetails,
                    'termsAndConditions' => $termsAndConditions,
                    'exclusions' => $exclusions,
                    'paymentTerms' => $paymentTerms,
                    'baseCurrency' => $baseCurrency,
                    'selectedCurrency' => $selectedCurrency,
                    'exchangeRate' => $exchangeRate,
                    'quotationInformationHtml' => $quotationInformationHtml,
                    'logoType' => $logoType,
                    'user_agency' => $userAgencyForHeader,
                ]);
                
                $pdf->setPaper('a4');
                $pdf->setOption('enable-php', false);
                $pdf->setOption('isHtml5ParserEnabled', true);
                $pdf->setOption('isRemoteEnabled', false);

                if ($preview) {
                    return $pdf->stream("tour-quotation.pdf");
                }

                return $pdf->download("tour-quotation.pdf");
            }
            
            // Re-throw if it's a different error
            throw $e;
        }
    }

    /**
     * Prepare email template data for a tour
     * Returns an array with all data needed for the email template view
     */
    public static function prepareEmailTemplateData($tourId)
    {
        $tour = Tour::where('tour_id', $tourId)->first();
        if (!$tour) {
            return null;
        }

        $orders = Order::where('tour_id', $tourId)
            ->where('status', 1)
            ->orderBy('booking_id')
            ->get();

        $servicesByDate = self::groupServicesByDate($orders);
        ksort($servicesByDate);

        $servicesByType = self::groupServicesByType($orders, $tour);

        // Fetch DMC user data for logo and company name
        $dmcUser = null;
        $dmcLogo = null;
        $dmcCompanyName = null;
        $dmcDetails = [
            'name' => 'N/A',
            'address' => 'N/A',
            'city' => 'N/A',
            'country' => 'N/A',
            'email' => 'N/A',
            'email2' => 'N/A',
            'phone' => 'N/A',
            'postal_pin' => 'N/A',
            'company_name' => 'N/A',
        ];
        
        if (!empty($tour->dmc_id)) {
            $dmcUser = User::where('userId', $tour->dmc_id)->first();
            if ($dmcUser) {
                $logoUrl = $dmcUser->logo ?? null;
                $dmcCompanyName = $dmcUser->company_name ?? null;
                $dmc_name = $dmcUser->name ?? null;
                // Populate DMC details
                $dmcDetails = [
                    'name' => $dmcUser->name ?? 'N/A',
                    'address' => $dmcUser->address ?? 'N/A',
                    'city' => $dmcUser->city ?? 'N/A',
                    'country' => $dmcUser->user_country ?? $dmcUser->country ?? 'N/A',
                    'email' => $dmcUser->email ?? 'N/A',
                    'email2' => 'N/A',
                    'phone' => ($dmcUser->country_code ? '+' . $dmcUser->country_code . ' ' : '') . ($dmcUser->phone ?? 'N/A'),
                    'postal_pin' => 'N/A',
                    'company_name' => $dmcUser->company_name ?? 'N/A',
                ];
                
                // Convert logo URL to base64 for display
                if (!empty($logoUrl)) {
                    try {
                        $mimeType = 'image/png';
                        $urlPath = parse_url($logoUrl, PHP_URL_PATH);
                        if ($urlPath) {
                            $extension = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION));
                            $mimeMap = [
                                'jpg' => 'image/jpeg',
                                'jpeg' => 'image/jpeg',
                                'png' => 'image/png',
                                'gif' => 'image/gif',
                                'webp' => 'image/webp',
                                'svg' => 'image/svg+xml',
                            ];
                            $mimeType = $mimeMap[$extension] ?? 'image/png';
                        }
                        
                        $response = Http::timeout(10)->get($logoUrl);
                        if ($response->successful()) {
                            $imageContent = $response->body();
                            if (!empty($imageContent) && strlen($imageContent) > 100) {
                                $contentType = $response->header('Content-Type');
                                if ($contentType && strpos($contentType, 'image/') !== false) {
                                    $mimeType = explode(';', $contentType)[0];
                                }
                                $base64 = base64_encode($imageContent);
                                $dmcLogo = 'data:' . $mimeType . ';base64,' . $base64;
                            }
                        } else {
                            $imageContent = @file_get_contents($logoUrl);
                            if ($imageContent !== false && !empty($imageContent) && strlen($imageContent) > 100) {
                                $base64 = base64_encode($imageContent);
                                $dmcLogo = 'data:' . $mimeType . ';base64,' . $base64;
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to fetch DMC logo for email', [
                            'logo_url' => $logoUrl,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }

        // Fetch Agent/Agency information
        $agentDetails = [
            'name' => 'N/A',
            'address' => 'N/A',
            'contact_person' => 'N/A',
            'phone' => 'N/A',
            'email' => 'N/A',
        ];

        if (!empty($tour->agent_id)) {
            $agent = Agent::with('agency')->where('agent_id', $tour->agent_id)->first();
            if ($agent) {
                $agency = $agent->agency;
                
                $agentDetails = [
                    'name' => ($agency && $agency->agency_name) ? $agency->agency_name : ($agent->name ?? 'N/A'),
                    'address' => ($agency && $agency->address) ? $agency->address : 'N/A',
                    'contact_person' => ($agency && $agency->contact_person) ? $agency->contact_person : ($agent->name ?? 'N/A'),
                    'phone' => ($agency && $agency->phone) ? $agency->phone : ($agent->phone ?? 'N/A'),
                    'email' => ($agency && $agency->email) ? $agency->email : ($agent->email ?? 'N/A'),
                ];
            }
        }

        // Proposal details
        $proposalDetails = [
            'proposal_date' => now()->format('d M Y'),
            'proposal_validity' => 'N/A',
            'proposal_sent_by' => $dmc_name ?? 'N/A',
        ];

        // Get booking and guest details from orders
        $bookingDetails = [
            'booking_id' => $tour->display_id ?? ('Tour #' . ($tour->tour_id ?? 'N/A')),
            'lead_guest_name' => 'N/A',
            'email' => 'N/A',
            'phone' => 'N/A',
            'address' => 'N/A',
            'city' => 'N/A',
            'postal_code' => 'N/A',
            'no_of_adults' => (int)($tour->adult ?? 0),
            'no_of_children' => (int)($tour->child ?? 0),
            'no_of_infants' => (int)($tour->infant ?? 0),
        ];

        // Try to get guest information from first order
        if ($orders->count() > 0) {
            $firstOrder = $orders->first();
            $orderData = $firstOrder->data;
            if (is_string($orderData)) {
                $orderData = json_decode($orderData, true);
            }
            
            if (is_array($orderData) && !empty($orderData)) {
                $firstItem = is_array($orderData[0]) ? $orderData[0] : $orderData;
                
                $bookingDetails['lead_guest_name'] = $firstItem['fullName'] ?? $firstItem['name'] ?? 'N/A';
                $bookingDetails['email'] = $firstItem['email'] ?? 'N/A';
                $bookingDetails['gender'] = $firstItem['gender'] ?? 'N/A';
                $bookingDetails['passenger_type'] = $firstItem['passenger_type'] ?? 'N/A';
                $bookingDetails['salutation'] = $firstItem['salutation'] ?? 'N/A';
                $bookingDetails['phone'] = $firstItem['phone'] ?? 'N/A';
                
                $address1 = $firstItem['address1'] ?? '';
                $address2 = $firstItem['address2'] ?? '';
                if (!empty($address1) || !empty($address2)) {
                    $bookingDetails['address'] = trim($address1 . ' ' . $address2);
                }
                
                $bookingDetails['city'] = $firstItem['state'] ?? 'N/A';
                $bookingDetails['postal_code'] = $firstItem['zip'] ?? 'N/A';
            }
        }
        
        // If passenger details are still empty, try to get from tour's mainguest column
        if (($bookingDetails['lead_guest_name'] === 'N/A' || empty($bookingDetails['lead_guest_name'])) && !empty($tour->mainguest)) {
            try {
                $mainguestData = is_string($tour->mainguest) ? json_decode($tour->mainguest, true) : $tour->mainguest;
                if (is_array($mainguestData) && !empty($mainguestData)) {
                    // Map mainguest fields to bookingDetails
                    if (!empty($mainguestData['full_name'])) {
                        $bookingDetails['lead_guest_name'] = $mainguestData['full_name'];
                    }
                    if (!empty($mainguestData['email'])) {
                        $bookingDetails['email'] = $mainguestData['email'];
                    }
                    if (!empty($mainguestData['phone'])) {
                        $phone = $mainguestData['phone'];
                        // Add country code if available
                        if (!empty($mainguestData['country_code'])) {
                            $phone = '+' . $mainguestData['country_code'] . ' ' . $phone;
                        }
                        $bookingDetails['phone'] = $phone;
                    }
                    // Combine address1 and address2
                    $address1 = $mainguestData['address1'] ?? '';
                    $address2 = $mainguestData['address2'] ?? '';
                    if (!empty($address1) || !empty($address2)) {
                        $bookingDetails['address'] = trim($address1 . ' ' . $address2);
                    }
                    if (!empty($mainguestData['state'])) {
                        $bookingDetails['city'] = $mainguestData['state'];
                    }
                    if (!empty($mainguestData['zip'])) {
                        $bookingDetails['postal_code'] = $mainguestData['zip'];
                    }
                }
            } catch (\Exception $e) {
                // If parsing fails, keep existing values
                Log::warning('Failed to parse mainguest data from tour', [
                    'tour_id' => $tour->tour_id ?? null,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Travel details
        $travelDetails = [
            'destination' => $tour->destination ?? $tour->tour_destination ?? 'N/A',
            'travel_date_from' => $tour->check_in_time ? \Carbon\Carbon::parse($tour->check_in_time)->format('l- d/m/Y') : 'N/A',
            'travel_date_to' => $tour->check_out_time ? \Carbon\Carbon::parse($tour->check_out_time)->format('l- d/m/Y') : 'N/A',
            'duration' => 'N/A',
        ];

        // Calculate duration
        if ($tour->check_in_time && $tour->check_out_time) {
            try {
                $checkIn = \Carbon\Carbon::parse($tour->check_in_time);
                $checkOut = \Carbon\Carbon::parse($tour->check_out_time);
                $duration = $checkIn->diffInDays($checkOut);
                $travelDetails['duration'] = $duration . ' Day' . ($duration > 1 ? 's' : '');
            } catch (\Exception $e) {
                // Keep as N/A
            }
        }

        // Calculate tour prices
        $tourPrices = self::calculateTourPrices($tourId);
        $hotelOptions = self::formatHotelsForPdf($orders, $tour, $tourPrices);
        
        // Get DMC ID - first try from tour, otherwise from current user
        $dmcIdForBankDetails = null;
        if (!empty($tour->dmc_id)) {
            $dmcIdForBankDetails = $tour->dmc_id;
        } else {
            // Fallback to getting DMC ID from current user
            $currentUser = Auth::user();
            if ($currentUser) {
                $dmcIdForBankDetails = self::getDmcId($currentUser);
            }
        }
        
        // Fetch bank details from bank_details table based on DMC ID
        $bankDetails = [];
        $termsAndConditions = '';
        $paymentTerms = [];
        
        if ($dmcIdForBankDetails) {
            $bankDetailRecord = BankDetail::where('dmc_id', $dmcIdForBankDetails)
                ->where('is_active', 1)
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($bankDetailRecord) {
                // Extract bank details
                $bankDetails = [
                    'account_name' => $bankDetailRecord->account_name ?? '',
                    'account_number' => $bankDetailRecord->account_number ?? '',
                    'bank_address' => $bankDetailRecord->bank_address ?? '',
                    'ifsc' => $bankDetailRecord->ifsc ?? '',
                    'swift_bic_iban' => $bankDetailRecord->swift_bic_iban ?? '',
                    'bank_code' => $bankDetailRecord->bank_code ?? '',
                    'branch_code' => $bankDetailRecord->branch_code ?? '',
                    'aba_routing' => $bankDetailRecord->aba_routing ?? '',
                ];
                
                // Extract terms and conditions
                $termsAndConditions = $bankDetailRecord->terms_and_conditions ?? '';
                
                // Extract payment terms (already cast as array in model)
                $paymentTerms = $bankDetailRecord->payment_terms ?? [];
                if (is_string($paymentTerms)) {
                    $paymentTerms = json_decode($paymentTerms, true) ?? [];
                }
                if (!is_array($paymentTerms)) {
                    $paymentTerms = [];
                }
            }
        }
        
        // Fallback to DMC user bank_details if bank_details table record not found
        if (empty($bankDetails) && $dmcUser && isset($dmcUser->bank_details)) {
            $bankDetailsData = is_string($dmcUser->bank_details) ? json_decode($dmcUser->bank_details, true) : $dmcUser->bank_details;
            if (is_array($bankDetailsData)) {
                $bankDetails = $bankDetailsData;
            }
        }
        
        // Exclusions (can be extended to fetch from database if needed)
        $exclusions = '';
        
        return [
            'tour' => $tour,
            'servicesByDate' => $servicesByDate,
            'servicesByType' => $servicesByType,
            'generatedAt' => now(),
            'dmcLogo' => $dmcLogo,
            'dmcCompanyName' => $dmcCompanyName,
            'dmcDetails' => $dmcDetails,
            'agentDetails' => $agentDetails,
            'proposalDetails' => $proposalDetails,
            'bookingDetails' => $bookingDetails,
            'travelDetails' => $travelDetails,
            'tourPrices' => $tourPrices,
            'hotelOptions' => $hotelOptions,
            'bankDetails' => $bankDetails,
            'termsAndConditions' => $termsAndConditions,
            'exclusions' => $exclusions,
            'paymentTerms' => $paymentTerms,
        ];
    }

    /**
     * Calculate single sharing and double sharing prices for a tour
     * 
     * @param int|string $tourId - Can be tour_id (integer) or display_id (string like "DMC-ORD3107")
     * @return array ['single_sharing' => float, 'double_sharing' => float, 'triple_sharing' => float]
     */
    public static function calculateTourPrices($tourId)
    {
        // Check if input is display_id (string format like "DMC-ORD3107") or tour_id (integer)
        if (is_string($tourId) && (strpos($tourId, 'DMC-ORD') === 0 || strpos($tourId, 'ORD') === 0)) {
            // It's a display_id, find tour by display_id
            $tour = Tour::where('display_id', $tourId)->first();
        } else {
            // It's a tour_id, find tour by tour_id
            $tour = Tour::where('tour_id', $tourId)->first();
        }
        
        if (!$tour) {
            return [
                'single_sharing' => 0,
                'double_sharing' => 0,
                'triple_sharing' => 0,
                'baby_cot_sharing' => 0,
                'segregated' => [
                    'hotel' => ['single' => 0, 'double' => 0, 'triple' => 0, 'baby_cot' => 0],
                    'attraction' => ['single' => 0, 'double' => 0],
                    'restaurant' => ['single' => 0, 'double' => 0],
                    'entry_port' => ['single' => 0, 'double' => 0],
                    'exit_port' => ['single' => 0, 'double' => 0],
                    'guide' => ['single' => 0, 'double' => 0],
                    'travel_hourly' => ['single' => 0, 'double' => 0],
                    'travel_point' => ['single' => 0, 'double' => 0],
                    'local_transport' => ['single' => 0, 'double' => 0],
                    'other' => ['single' => 0, 'double' => 0],
                ],
                'supplements' => [],
            ];
        }
        // Use the actual tour_id from the found tour for querying orders
        $actualTourId = $tour->tour_id;
        $orders = Order::where('tour_id', $actualTourId)
            ->where('status', 1)
            ->get();

        // GROUP pricing inputs
        // IMPORTANT (project convention):
        // - `adult` is the TOTAL adult count (includes FOC adults)
        // - `foc_size` is how many of those adults are FOC (free of charge)
        // - `child` are paying children (FOC does not apply to child here)
        $tourType = strtoupper((string)($tour->tour_type ?? 'FIT'));
        $adultTotal = max(0, (int)($tour->adult ?? 0));
        $childTotal = max(0, (int)($tour->child ?? 0));
        $focSize = max(0, (int)($tour->foc_size ?? 0));

        // Paying pax excludes FOC adults
        $payingAdults = max(0, $adultTotal - $focSize);
        $payingPax = $payingAdults + $childTotal;

        // Total pax is the real headcount travelling
        $totalPax = $adultTotal + $childTotal;

        // When GROUP has FOC, distribute total pax cost over paying pax.
        // Example: adultTotal=12 (includes foc 2), childTotal=0 => total=12, paying=10 => factor=12/10.
        $focDistributionFactor = ($tourType === 'GROUP' && $payingPax > 0 && $totalPax > $payingPax)
            ? ((float)$totalPax / (float)$payingPax)
            : 1.0;

        $totalSingleSharing = 0;
        $totalDoubleSharing = 0;
        $totalTripleSharing = 0;
        $totalBabyCot = 0;
        // Separate tracker for non-hotel, non-supplement services (for other_services response key)
        $otherServiceSingle = 0.0;
        $otherServiceDouble = 0.0;
        // Track child-specific pricing component across attraction/restaurant services
        $totalChildComponent = 0; // Sum of child unit prices (attraction + restaurant + â€¦)
        
        // Segregated prices by service type
        $segregatedPrices = [
            'hotel' => ['single' => 0, 'double' => 0, 'triple' => 0, 'baby_cot' => 0],
            'attraction' => ['single' => 0, 'double' => 0],
            'restaurant' => ['single' => 0, 'double' => 0],
            'entry_port' => ['single' => 0, 'double' => 0],
            'exit_port' => ['single' => 0, 'double' => 0],
            'guide' => ['single' => 0, 'double' => 0],
            'travel_hourly' => ['single' => 0, 'double' => 0],
            'travel_point' => ['single' => 0, 'double' => 0],
            'local_transport' => ['single' => 0, 'double' => 0],
            'other' => ['single' => 0, 'double' => 0],
        ];

        // Supplements: services with "supplement": true (excluded from main total).
        // Non-hotel supplement rows expose full line booking total; hotel supplements use stay totals per rooming.
        $supplements = [];
        // Merge hotel supplements: same hotel/date-range => one supplement row (avoid duplicates)
        $hotelSupplementBuckets = [];

        // Counter for hotels to segregate individually
        $hotelCount = 0;
        // Track which hotel occupancies are actually booked on this tour
        $bookedHotelOccupancies = ['single' => false, 'double' => false, 'triple' => false];
        
        // Merge hotel rows: same hotel_id + same date range => one hotel bucket.
        // Each bucket stores per-head price for single/double/triple once (no multiplication when multiple orders exist).
        $hotelBuckets = [];
        // Store display/meta for each grouped hotel key
        $hotelBucketMeta = [];
        
        // Normalize booking date range to group same hotel orders together
        $getHotelBookingRangeLabel = function (array $item) use ($tour): string {
            $bookingDate = $item['bookingDate'] ?? null;
            try {
                if (is_array($bookingDate) && count($bookingDate) === 2 && !empty($bookingDate[0]) && !empty($bookingDate[1])) {
                    $start = Carbon::parse($bookingDate[0])->format('Y-m-d');
                    $end = Carbon::parse($bookingDate[1])->format('Y-m-d');
                    return $start . ' to ' . $end;
                }
            } catch (\Throwable $e) {
                // ignore
            }
            
            // Fallback to tour dates
            try {
                if (!empty($tour->check_in_time) && !empty($tour->check_out_time)) {
                    $start = Carbon::parse($tour->check_in_time)->format('Y-m-d');
                    $end = Carbon::parse($tour->check_out_time)->format('Y-m-d');
                    return $start . ' to ' . $end;
                }
            } catch (\Throwable $e) {
                // ignore
            }
            
            return '';
        };

        foreach ($orders as $order) {
            $rawData = $order->data;
            if (is_string($rawData)) {
                $rawData = json_decode($rawData, true);
            }

            if (empty($rawData)) {
                continue;
            }

            $items = isset($rawData[0]) ? $rawData : [$rawData];
            $type = strtolower($order->type ?? '');
            $babyCotPrice = null;
            $manualSinglePrice = null;
            // $manualDoublePrice = null; // not used (double always from room/hotel tables)

            foreach ($items as $item) {
                $isSupplement = !empty($item['supplement']);

                if ($type === 'hotel') {
                    $hotelCount++;
                    $babyCotPrice = 0;
                    
                    // Fetch hotel details early to use name as key
                    $hotelId = $item['hotelDetails']['hotel_id'] ?? $item['hotelDetails']['hotelId'] ?? $item['hotel_id'] ?? $item['hotelId'] ?? null;
                    $hotelName = $item['hotelDetails']['hotel_name'] ?? $item['hotelName'] ?? $item['name'] ?? null;
                    $hotel = null;
                    $weekendDays = ['Saturday', 'Sunday']; // Default fallback only

                    if ($hotelId) {
                        try {
                            $hotel = Hotel::where('hotel_unique_id', $hotelId)->first();
                            if ($hotel) {
                                if ($hotel->weekend_days) {
                                    $decodedWeekendDays = json_decode($hotel->weekend_days, true);
                                    if (is_array($decodedWeekendDays) && !empty($decodedWeekendDays)) {
                                        $weekendDays = $decodedWeekendDays;
                                    }
                                }
                                if ($hotel->name) {
                                    $hotelName = $hotel->name;
                                }
                            }
                        } catch (\Exception $e) {
                            Log::warning('Failed to fetch hotel details', [
                                'hotel_id' => $hotelId,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }

                    if (empty($hotelName)) {
                        $hotelName = 'Hotel ' . $hotelCount;
                    }

                    // Group key: same hotel_id + same date range => treat as one hotel
                    // (hotel name can change/collide; hotel_id is stable)
                    $rangeLabel = $getHotelBookingRangeLabel(is_array($item) ? $item : []);
                    $stableHotelId = $hotelId ?: ($hotel ? ($hotel->hotel_unique_id ?? null) : null);
                    $stableHotelId = $stableHotelId !== null ? (string)$stableHotelId : '';
                    $groupKey = $stableHotelId !== '' ? ('hotel_' . $stableHotelId) : ('hotel_name_' . preg_replace('/\s+/', '_', strtolower((string)$hotelName)));
                    $currentHotelKey = $rangeLabel ? ($groupKey . '__' . $rangeLabel) : $groupKey;

                    if (!isset($hotelBucketMeta[$currentHotelKey])) {
                        $hotelBucketMeta[$currentHotelKey] = [
                            'hotel_id' => $stableHotelId,
                            'hotel_name' => $hotelName,
                            'date_range' => $rangeLabel,
                            'display_name' => $rangeLabel ? ($hotelName . ' (' . $rangeLabel . ')') : $hotelName,
                        ];
                    }
                    
                    if (!isset($segregatedPrices[$currentHotelKey])) {
                        $segregatedPrices[$currentHotelKey] = [
                            'single' => 0,
                            'double' => 0,
                            'triple' => 0,
                            'baby_cot' => 0,
                            'occupancy' => null,        // 'single'|'double'|'triple'
                            'selected_persons' => null, // 1|2|3
                        ];
                    }

                    // Read selected persons from hotel JSON (rooms[0].selected_persons) and derive occupancy label.
                    $firstRoom = (!empty($item['rooms']) && is_array($item['rooms'])) ? ($item['rooms'][0] ?? null) : null;
                    $selectedPersonsForHotel = is_array($firstRoom)
                        ? ($firstRoom['selected_persons'] ?? $firstRoom['selectedPersons'] ?? null)
                        : ($item['pax'] ?? null);
                    $selectedPersonsForHotel = $selectedPersonsForHotel !== null ? (int)$selectedPersonsForHotel : null;
                    $hotelOccupancy = match ($selectedPersonsForHotel) {
                        1 => 'single',
                        2 => 'double',
                        3 => 'triple',
                        default => null,
                    };
                    if (!empty($hotelOccupancy)) {
                        $bookedHotelOccupancies[$hotelOccupancy] = true;
                    }
                    
                    // Check for direct totalPrice and head_count in JSON (e.g. from enquiry)
                    // We only use this to override SINGLE price when exactly 1 person;
                    // double/triple prices always come from room/hotel tables so they stay consistent.
                    $directTotalPrice = $item['totalPrice'] ?? $item['price'] ?? null;

                    if ($directTotalPrice !== null) {
                        $totalHeadCount = 0;
                        if (!empty($item['rooms']) && is_array($item['rooms'])) {
                            foreach ($item['rooms'] as $room) {
                                if (!empty($room['beds']) && is_array($room['beds'])) {
                                    foreach ($room['beds'] as $bed) {
                                        $totalHeadCount += floatval($bed['head_count'] ?? $bed['headCount'] ?? 0);
                                    }
                                }
                            }
                        }
                        
                        // If no headcount from beds, try fallback to top-level fields
                        if ($totalHeadCount == 0) {
                            $totalHeadCount = floatval($item['pax'] ?? $item['adults'] ?? $item['adultCount'] ?? 0);
                        }

                        if ($totalHeadCount == 1) {
                            // For exactly one person, use enquiry total as single price override
                            $manualSinglePrice = floatval($directTotalPrice);
                        }
                    }
                    
                    // Hotel pricing calculation with date-based weekday/weekend check
                    $singleWeekdayPrice = null;
                    $singleWeekendPrice = null;
                    $doubleWeekdayPrice = null;
                    $doubleWeekendPrice = null;


                    // Get prices from room data - first try to fetch from database using room_id (preferred) and hotel_id
                    if (!empty($item['rooms']) && is_array($item['rooms'])) {
                        foreach ($item['rooms'] as $roomData) {
                            $roomtype = $roomData['room_type'] ?? $roomData['roomType'] ?? null;
                            $roomIdFromJson = $roomData['room_id'] ?? $roomData['roomId'] ?? null;
                            
                            // Try to fetch room from database first - must match both room_id and hotel_id (more reliable than room_type)
                            if ($roomIdFromJson && $hotelId) {
                                try {
                                    // First try to get hotel_id from hotel_unique_id
                                    $hotel = Hotel::where('hotel_unique_id', $hotelId)->first();
                                    $dbHotelId = $hotel ? $hotel->hotel_unique_id : $hotelId;
                                    
                                    $roomRecord = Room::where('room_id', $roomIdFromJson)
                                        ->where('hotel_id', $dbHotelId)
                                        ->where('status', 1)
                                        ->first();
                                    if ($roomRecord) {
                                        if ($roomRecord->weekday_price !== null && $roomRecord->weekday_price !== '') {
                                            $singleWeekdayPrice = floatval($roomRecord->weekday_price);
                                        }
                                        if ($roomRecord->weekend_price !== null && $roomRecord->weekend_price !== '') {
                                            $singleWeekendPrice = floatval($roomRecord->weekend_price);
                                        }
                                        if ($roomRecord->double_weekday_price !== null && $roomRecord->double_weekday_price !== '') {
                                            // Double prices in DB are room prices; convert to per-head by dividing by 2
                                            $doubleWeekdayPrice = floatval($roomRecord->double_weekday_price) / 2;
                                        }
                                        if ($roomRecord->double_weekend_price !== null && $roomRecord->double_weekend_price !== '') {
                                            $doubleWeekendPrice = floatval($roomRecord->double_weekend_price) / 2;
                                        }
                                        $bed = Bed::where('room_id', $roomRecord->room_id)->first();
                                        if ($bed && $bed->baby_cot_price !== null) {
                                            $babyCotPrice = floatval($bed->baby_cot_price);
                                        }
                                        // If we got prices from database, break
                                        if ($singleWeekdayPrice !== null || $singleWeekendPrice !== null || $doubleWeekdayPrice !== null || $doubleWeekendPrice !== null) {
                                            break;
                                        }
                                    }
                                } catch (\Exception $e) {
                                    Log::warning('Failed to fetch room prices from database', [
                                        'room_type' => $roomtype,
                                        'hotel_id' => $hotelId,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            }
                            
                            // Fallback: Get prices from room data in item
                            $weekdayPrice = $roomData['weekday_price'] ?? $roomData['weekdayPrice'] ?? null;
                            $weekendPrice = $roomData['weekend_price'] ?? $roomData['weekendPrice'] ?? null;
                            $doubleWeekdayPriceVal = $roomData['double_weekday_price'] ?? $roomData['doubleWeekdayPrice'] ?? null;
                            $doubleWeekendPriceVal = $roomData['double_weekend_price'] ?? $roomData['doubleWeekendPrice'] ?? null;

                            if ($singleWeekdayPrice === null && $weekdayPrice !== null && $weekdayPrice !== '') {
                                $singleWeekdayPrice = floatval($weekdayPrice);
                            }
                            if ($singleWeekendPrice === null && $weekendPrice !== null && $weekendPrice !== '') {
                                $singleWeekendPrice = floatval($weekendPrice);
                            }
                            if ($doubleWeekdayPrice === null && $doubleWeekdayPriceVal !== null && $doubleWeekdayPriceVal !== '') {
                                $doubleWeekdayPrice = floatval($doubleWeekdayPriceVal) / 2;
                            }
                            if ($doubleWeekendPrice === null && $doubleWeekendPriceVal !== null && $doubleWeekendPriceVal !== '') {
                                $doubleWeekendPrice = floatval($doubleWeekendPriceVal) / 2;
                            }

                            if ($singleWeekdayPrice !== null || $singleWeekendPrice !== null || $doubleWeekdayPrice !== null || $doubleWeekendPrice !== null) {
                                break;
                            }
                        }
                    }

                    // Check direct item fields if not found in rooms
                    if ($singleWeekdayPrice === null) {
                        $weekdayPrice = $item['weekday_price'] ?? $item['weekdayPrice'] ?? null;
                        if ($weekdayPrice !== null && $weekdayPrice !== '') {
                            $singleWeekdayPrice = floatval($weekdayPrice);
                        }
                    }
                    if ($singleWeekendPrice === null) {
                        $weekendPrice = $item['weekend_price'] ?? $item['weekendPrice'] ?? null;
                        if ($weekendPrice !== null && $weekendPrice !== '') {
                            $singleWeekendPrice = floatval($weekendPrice);
                        }
                    }
                    if ($doubleWeekdayPrice === null) {
                        $doubleWeekdayPriceVal = $item['double_weekday_price'] ?? $item['doubleWeekdayPrice'] ?? null;
                        if ($doubleWeekdayPriceVal !== null && $doubleWeekdayPriceVal !== '') {
                            $doubleWeekdayPrice = floatval($doubleWeekdayPriceVal) / 2;
                        }
                    }
                    if ($doubleWeekendPrice === null) {
                        $doubleWeekendPriceVal = $item['double_weekend_price'] ?? $item['doubleWeekendPrice'] ?? null;
                        if ($doubleWeekendPriceVal !== null && $doubleWeekendPriceVal !== '') {
                            $doubleWeekendPrice = floatval($doubleWeekendPriceVal) / 2;
                        }
                    }

                    // Get booking dates
                    $bookingDates = [];
                    $bookingDate = $item['bookingDate'] ?? null;
                    
                    if ($bookingDate) {
                        if (is_array($bookingDate) && count($bookingDate) === 2) {
                            try {
                                $start = Carbon::parse($bookingDate[0]);
                                $end = Carbon::parse($bookingDate[1]);
                                
                                // Generate all dates in the booking period (excluding checkout day)
                                while ($start->lt($end)) {
                                    $bookingDates[] = $start->copy();
                                    $start->addDay();
                                }
                            } catch (\Exception $e) {
                                // If date parsing fails, use tour dates as fallback
                                if ($tour->check_in_time && $tour->check_out_time) {
                                    try {
                                        $start = Carbon::parse($tour->check_in_time);
                                        $end = Carbon::parse($tour->check_out_time);
                                        while ($start->lt($end)) {
                                            $bookingDates[] = $start->copy();
                                            $start->addDay();
                                        }
                                    } catch (\Exception $e2) {
                                        // If still fails, default to 1 night
                                        $bookingDates[] = Carbon::today();
                                    }
                                }
                            }
                        } else {
                            $singleDate = is_array($bookingDate) ? ($bookingDate[0] ?? null) : $bookingDate;
                            if ($singleDate) {
                                try {
                                    $bookingDates[] = Carbon::parse($singleDate);
                                } catch (\Exception $e) {
                                    // Fallback to tour dates
                                    if ($tour->check_in_time) {
                                        try {
                                            $bookingDates[] = Carbon::parse($tour->check_in_time);
                                        } catch (\Exception $e2) {
                                            $bookingDates[] = Carbon::today();
                                        }
                                    }
                                }
                            }
                        }
                    } elseif ($tour->check_in_time && $tour->check_out_time) {
                        // Fallback to tour dates if bookingDate not available
                        try {
                            $start = Carbon::parse($tour->check_in_time);
                            $end = Carbon::parse($tour->check_out_time);
                            while ($start->lt($end)) {
                                $bookingDates[] = $start->copy();
                                $start->addDay();
                            }
                        } catch (\Exception $e) {
                            $bookingDates[] = Carbon::today();
                        }
                    }

                    // If no dates found, default to 1 night
                    if (empty($bookingDates)) {
                        $bookingDates[] = Carbon::today();
                    }

                    // Calculate price for each night based on weekday/weekend
                    $hotelSingleTotal = 0;
                    $hotelDoubleTotal = 0;
                    $hotelTripleTotal = 0;
                    $extraBedTotal = 0; // total extra bed cost across all nights
                    
                    // Get extra bed price from beds table if available
                    $extraBedWeekdayPrice = null;
                    $extraBedWeekendPrice = null;
                    $roomIdForBed = null;
                    
                    // Try to get room_id from roomRecord to check for extra bed
                    if (!empty($item['rooms']) && is_array($item['rooms'])) {
                        foreach ($item['rooms'] as $roomData) {
                            $roomId = $roomData['room_id'] ?? $roomData['roomId'] ?? null;
                            
                            if ($roomId && $hotelId) {
                                try {
                                    $hotel = Hotel::where('hotel_unique_id', $hotelId)->first();
                                    $dbHotelId = $hotel ? $hotel->hotel_unique_id : $hotelId;
                                    
                                    $roomRecord = Room::where('room_id', $roomId)
                                        ->where('hotel_id', $dbHotelId)
                                        ->where('status', 1)
                                        ->first();
                                    if ($roomRecord && $roomRecord->room_id) {
                                        $roomIdForBed = $roomRecord->room_id;
                                        
                                        // Check beds table for extra_bed
                                        
                                        $bedRecord = Bed::where('room_id', $roomIdForBed)
                                            ->where('extra_bed', true)
                                            ->where('is_active', 1)
                                            ->first();
                                        if ($bedRecord && $bedRecord->extra_bed_price !== null) {
                                            // Extra bed price is per extra bed (per night)
                                            $extraBedPrice = floatval($bedRecord->extra_bed_price);
                                            $extraBedWeekdayPrice = $extraBedPrice;
                                            $extraBedWeekendPrice = $extraBedPrice;
                                            
                                            Log::info('Extra bed found for room', [
                                                'room_id' => $roomIdForBed,
                                                'extra_bed_price' => $extraBedPrice
                                            ]);
                                        }
                                        if ($bedRecord && $bedRecord->baby_cot_price !== null) {
                                            $babyCotPrice = floatval($bedRecord->baby_cot_price);
                                        }
                                        
                                        // If we found the room, break
                                        if ($roomIdForBed) {
                                            break;
                                        }
                                    }
                                } catch (\Exception $e) {
                                    Log::warning('Failed to fetch bed info for triple sharing', [
                                        'room_type' => $roomtype,
                                        'hotel_id' => $hotelId,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            }
                        }
                    }
                    
                    // Debug logging
                    Log::info('Hotel price calculation', [
                        'hotel_id' => $hotelId,
                        'room_type' => $roomtype ?? 'N/A',
                        'booking_dates_count' => count($bookingDates),
                        'booking_dates' => array_map(function($d) { return $d->format('Y-m-d (l)'); }, $bookingDates),
                        'weekend_days' => $weekendDays,
                        'single_weekday_price' => $singleWeekdayPrice,
                        'single_weekend_price' => $singleWeekendPrice,
                        'double_weekday_price' => $doubleWeekdayPrice,
                        'double_weekend_price' => $doubleWeekendPrice,
                    ]);
                    foreach ($bookingDates as $date) {
                        $dayName = $date->format('l'); // Full day name (Monday, Tuesday, etc.)
                        $isWeekend = in_array($dayName, $weekendDays);
                        $dateString = $date->format('Y-m-d');
                        
                        // Priority-based pricing: Check rates table first
                        $ratePrice = null;
                        $rateSingleWeekdayPrice = null;
                        $rateSingleWeekendPrice = null;
                        $rateDoubleWeekdayPrice = null;
                        $rateDoubleWeekendPrice = null;
                        $rateEventType = null;
                        
                        if ($hotelId) {
                            try {
                                // Query rates for this hotel and date with priority order
                                $rate = Rate::where('hotel_id', $hotelId)
                                    ->whereDate('start_date', '<=', $dateString)
                                    ->whereDate('end_date', '>=', $dateString)
                                    ->orderByRaw("
                                        CASE
                                            WHEN event_type = 'Blackout Date' THEN 1
                                            WHEN event_type = 'Season' THEN 2
                                            WHEN event_type = 'Fair Date' THEN 3
                                            ELSE 4
                                        END
                                    ")
                                    ->first();
                                
                                if ($rate) {
                                    $rateEventType = $rate->event_type;
                                    
                                    if ($rate->event_type == 'Blackout Date') {
                                        // Blackout Date: Use rate->price (first priority)
                                        $ratePrice = floatval($rate->price ?? 0);
                                        // For blackout, single uses full price, double is per-head (price / 2)
                                        $rateSingleWeekdayPrice = $ratePrice;
                                        $rateSingleWeekendPrice = $ratePrice;
                                        $rateDoubleWeekdayPrice = $ratePrice / 2;
                                        $rateDoubleWeekendPrice = $ratePrice / 2;
                                    } elseif ($rate->event_type == 'Season') {
                                        // Season: Use rate weekday/weekend prices (second priority)
                                        $rateSingleWeekdayPrice = $rate->weekday_price ? floatval($rate->weekday_price) : null;
                                        $rateSingleWeekendPrice = $rate->weekend_price ? floatval($rate->weekend_price) : null;
                                        // Check if double prices exist (they might not be in all migrations)
                                        $rateDoubleWeekdayPrice = (isset($rate->double_weekday_price) && $rate->double_weekday_price !== null && $rate->double_weekday_price !== '') 
                                            ? floatval($rate->double_weekday_price) / 2 
                                            : null;
                                        $rateDoubleWeekendPrice = (isset($rate->double_weekend_price) && $rate->double_weekend_price !== null && $rate->double_weekend_price !== '') 
                                            ? floatval($rate->double_weekend_price) / 2 
                                            : null;
                                    }
                                    // Fair Date is handled as additional price, skip for now as per priority
                                }
                            } catch (\Exception $e) {
                                Log::warning('Failed to fetch rate for date', [
                                    'hotel_id' => $hotelId,
                                    'date' => $dateString,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                        
                        // Determine per-night price ONLY from room/hotel data (ignore rates)
                        // Single: weekday/weekend single prices
                        // Double: weekday/weekend double prices (already stored per-head: double_* / 2 above)
                        $singlePriceToAdd = null;
                        $doublePriceToAdd = null;

                        if ($isWeekend) {
                            $singlePriceToAdd = $singleWeekendPrice ?? $singleWeekdayPrice;
                            $doublePriceToAdd = $doubleWeekendPrice ?? $doubleWeekdayPrice;
                        } else {
                            $singlePriceToAdd = $singleWeekdayPrice ?? $singleWeekendPrice;
                            $doublePriceToAdd = $doubleWeekdayPrice ?? $doubleWeekendPrice;
                        }

                        // Add prices to totals (per night)
                        if ($singlePriceToAdd !== null) {
                            $hotelSingleTotal += $singlePriceToAdd;
                        }
                        if ($doublePriceToAdd !== null) {
                            $hotelDoubleTotal += $doublePriceToAdd;
                        }

                        // Accumulate extra bed total per night (if available)
                        if ($extraBedWeekdayPrice !== null) {
                            $extraBedPriceToAdd = $isWeekend 
                                ? ($extraBedWeekendPrice ?? $extraBedWeekdayPrice) 
                                : ($extraBedWeekdayPrice ?? $extraBedWeekendPrice);

                            if ($extraBedPriceToAdd !== null) {
                                $extraBedTotal += $extraBedPriceToAdd;
                            }
                        }
                    }

                    // Simplified totals for hotel:
                    // - single_sharing: sum of single prices across nights (with optional manual override for 1 pax)
                    // - double_sharing: sum of per-head double prices across nights (always from room/hotel)
                    // - triple_sharing (when extra bed present):
                    //     (double_sharing_total * 2 + extra_bed_total) / 3
                    //   where extra_bed_total = extra_bed_price_per_night * nights
                    if ($extraBedTotal > 0) {
                        $hotelTripleTotal = ($hotelDoubleTotal * 2 + $extraBedTotal) / 3;
                    } else {
                        $hotelTripleTotal = 0;
                    }

                    // Override with manual SINGLE price if available (from directTotalPrice for 1 pax)
                    if ($manualSinglePrice !== null) {
                        $hotelSingleTotal = $manualSinglePrice;
                    }

                    if ($isSupplement) {
                        if (!isset($hotelSupplementBuckets[$currentHotelKey])) {
                            $meta = $hotelBucketMeta[$currentHotelKey] ?? [];
                            $hotelSupplementBuckets[$currentHotelKey] = [
                                'type' => 'hotel',
                                // keep stable key internally but expose friendly names to API
                                'key' => $currentHotelKey,
                                'hotel_id' => $meta['hotel_id'] ?? null,
                                'hotel_name' => $meta['hotel_name'] ?? null,
                                'date_range' => $meta['date_range'] ?? null,
                                'display_name' => $meta['display_name'] ?? ($meta['hotel_name'] ?? $currentHotelKey),
                                'name' => $meta['display_name'] ?? ($meta['hotel_name'] ?? $currentHotelKey),
                                'single' => null,
                                'double' => null,
                                'triple' => null,
                                'baby_cot' => 0,
                            ];
                        }

                        // Prices come from room pricing data (independent of order occupancy).
                        // Keep max value in case multiple orders exist for the same supplement hotel.
                        if ($hotelSingleTotal > 0) {
                            $prev = $hotelSupplementBuckets[$currentHotelKey]['single'];
                            $hotelSupplementBuckets[$currentHotelKey]['single'] = $prev === null ? (float)$hotelSingleTotal : max((float)$prev, (float)$hotelSingleTotal);
                        }
                        if ($hotelDoubleTotal > 0) {
                            $prev = $hotelSupplementBuckets[$currentHotelKey]['double'];
                            $hotelSupplementBuckets[$currentHotelKey]['double'] = $prev === null ? (float)$hotelDoubleTotal : max((float)$prev, (float)$hotelDoubleTotal);
                        }
                        if ($hotelTripleTotal > 0) {
                            $prev = $hotelSupplementBuckets[$currentHotelKey]['triple'];
                            $hotelSupplementBuckets[$currentHotelKey]['triple'] = $prev === null ? (float)$hotelTripleTotal : max((float)$prev, (float)$hotelTripleTotal);
                        }
                        $hotelSupplementBuckets[$currentHotelKey]['baby_cot'] = max((float)($hotelSupplementBuckets[$currentHotelKey]['baby_cot'] ?? 0), (float)$babyCotPrice);
                    } else {
                        if (!isset($hotelBuckets[$currentHotelKey])) {
                            $hotelBuckets[$currentHotelKey] = [
                                'single' => null,
                                'double' => null,
                                'triple' => null,
                            ];
                        }
                        // Single/double/triple prices come from the room's own pricing data
                        // (weekday_price, double_weekday_price, extra_bed_price), not from the occupancy
                        // of this specific order. So fill each slot from ANY order â€” first non-null wins.
                        if ($hotelBuckets[$currentHotelKey]['single'] === null && $hotelSingleTotal > 0) {
                            $hotelBuckets[$currentHotelKey]['single'] = (float)$hotelSingleTotal;
                        }
                        if ($hotelBuckets[$currentHotelKey]['double'] === null && $hotelDoubleTotal > 0) {
                            $hotelBuckets[$currentHotelKey]['double'] = (float)$hotelDoubleTotal;
                        }
                        if ($hotelBuckets[$currentHotelKey]['triple'] === null && $hotelTripleTotal > 0) {
                            $hotelBuckets[$currentHotelKey]['triple'] = (float)$hotelTripleTotal;
                        }

                        // Add to segregated hotel prices
                        $segregatedPrices['hotel']['single'] += $hotelSingleTotal;
                        $segregatedPrices['hotel']['double'] += $hotelDoubleTotal;
                        $segregatedPrices['hotel']['triple'] += $hotelTripleTotal;
                        $segregatedPrices['hotel']['baby_cot'] += $babyCotPrice;

                        $totalBabyCot += $babyCotPrice;

                        // Add to individual hotel prices
                        $segregatedPrices[$currentHotelKey]['single'] += $hotelSingleTotal;
                        $segregatedPrices[$currentHotelKey]['double'] += $hotelDoubleTotal;
                        $segregatedPrices[$currentHotelKey]['triple'] += $hotelTripleTotal;
                        $segregatedPrices[$currentHotelKey]['baby_cot'] += $babyCotPrice;
                    }
                } else {
                    // Other services pricing calculation
                    $totalPrice = $item['totalPrice'] ?? $item['total_price'] ?? $item['price'] ?? null;
                    if ($totalPrice !== null) {
                        $totalPriceFloat = floatval($totalPrice);
                        $normalizedType = strtolower($type ?? '');
                        
                        // Handle attraction and restaurant
                        //
                        // Per-pax resolution priority (per-adult unit price):
                        //   1. Explicit JSON per-pax fields (adultPrice / adult_price)  â† user override
                        //   2. Derived from the booking's own totalPrice                â† what the user actually saved
                        //   3. Catalog default (ticket_details for attraction, meals table for restaurant)
                        //
                        // The booking's totalPrice is authoritative because it represents what the user
                        // actually entered/charged for this specific booking. The catalog (meal.adult_price,
                        // ticket_details.adult_price) is just a default at the time of selection and may
                        // differ from what was finally saved on the booking. Falling back to the catalog
                        // when totalPrice already exists led to incorrect per-pax values
                        // (e.g. catalog 28 used instead of booked 500/10 = 50).
                        if ($normalizedType === 'attraction' || $normalizedType === 'restaurant') {
                            $adultCount = floatval($item['adultCount'] ?? 0);
                            $childCount = floatval($item['childCount'] ?? 0);

                            // (1) Explicit JSON per-pax (user override) â€” highest priority
                            $jsonAdultPrice = null;
                            $jsonChildPrice = null;
                            if (isset($item['adultPrice']) && $item['adultPrice'] !== '') {
                                $jsonAdultPrice = floatval($item['adultPrice']);
                            } elseif (isset($item['adult_price']) && $item['adult_price'] !== '') {
                                $jsonAdultPrice = floatval($item['adult_price']);
                            }
                            if (isset($item['childPrice']) && $item['childPrice'] !== '') {
                                $jsonChildPrice = floatval($item['childPrice']);
                            } elseif (isset($item['child_price']) && $item['child_price'] !== '') {
                                $jsonChildPrice = floatval($item['child_price']);
                            }

                            // (3) Catalog defaults â€” used only as a last resort
                            $catalogAdultPrice = null;
                            $catalogChildPrice = null;
                            if (isset($item['ticket_details']['adult_price']) && $item['ticket_details']['adult_price'] !== '') {
                                $catalogAdultPrice = floatval($item['ticket_details']['adult_price']);
                            }
                            if (isset($item['ticket_details']['child_price']) && $item['ticket_details']['child_price'] !== '') {
                                $catalogChildPrice = floatval($item['ticket_details']['child_price']);
                            }
                            if ($normalizedType === 'restaurant'
                                && ($catalogAdultPrice === null || $catalogChildPrice === null)
                                && !empty($item['MealDescription'][0]['meal_id'])
                            ) {
                                try {
                                    $mealId    = $item['MealDescription'][0]['meal_id'];
                                    $mealQuery = \App\Models\Meal::where('meal_id', $mealId)->first();
                                    if ($mealQuery) {
                                        if ($catalogAdultPrice === null && $mealQuery->adult_price !== null) {
                                            $catalogAdultPrice = (float) $mealQuery->adult_price;
                                        }
                                        if ($catalogChildPrice === null && $mealQuery->child_price !== null) {
                                            $catalogChildPrice = (float) $mealQuery->child_price;
                                        }
                                    }
                                } catch (\Throwable $e) {
                                    \Log::warning('Failed to fetch meal unit prices for restaurant', [
                                        'meal_id'       => $item['MealDescription'][0]['meal_id'] ?? null,
                                        'restaurant_id' => $item['restaurantId'] ?? null,
                                        'tour_id'       => $tour->tour_id ?? null,
                                        'error'         => $e->getMessage(),
                                    ]);
                                }
                            }

                            // (2) Derived per-adult from the booking's totalPrice.
                            // If children are present, subtract child cost (using best known child unit
                            // price: JSON > catalog) so the remainder represents only adult cost.
                            $derivedAdultPrice = null;
                            if ($totalPriceFloat > 0 && $adultCount > 0) {
                                $childUnitForSubtraction = $jsonChildPrice ?? $catalogChildPrice ?? 0;
                                $childCost = ($childCount > 0) ? ($childUnitForSubtraction * $childCount) : 0;
                                $derivedAdultPrice = max(0, $totalPriceFloat - $childCost) / $adultCount;
                            }

                            // Final per-adult unit price: JSON > derived (from booking total) > catalog
                            if ($jsonAdultPrice !== null && $jsonAdultPrice > 0) {
                                $adultUnitPrice = $jsonAdultPrice;
                            } elseif ($derivedAdultPrice !== null && $derivedAdultPrice > 0) {
                                $adultUnitPrice = $derivedAdultPrice;
                            } elseif ($catalogAdultPrice !== null && $catalogAdultPrice > 0) {
                                $adultUnitPrice = $catalogAdultPrice;
                            } else {
                                $adultUnitPrice = 0;
                            }

                            // Final per-child unit price: JSON > catalog (child cost stays in child_sharing only)
                            if ($jsonChildPrice !== null && $jsonChildPrice > 0) {
                                $childUnitPrice = $jsonChildPrice;
                            } elseif ($catalogChildPrice !== null && $catalogChildPrice > 0) {
                                $childUnitPrice = $catalogChildPrice;
                            } else {
                                $childUnitPrice = 0;
                            }

                            // Adult per pax â†’ main totals + segregated (with hotel and other services).
                            // Child per pax â†’ child_sharing only (never mixed into single/double).
                            $singleSharing = 0;
                            if ($adultCount >= 1) {
                                if ($adultUnitPrice > 0) {
                                    $singleSharing = $adultUnitPrice;
                                } else {
                                    $pax = $adultCount + $childCount;
                                    $singleSharing = $pax > 0 ? ($totalPriceFloat / $pax) : $totalPriceFloat;
                                }
                            }
                            // When adultCount < 1 (only children): nothing added to main/segregated; child goes to child_sharing below

                            // Guide and vehicle: add to adult section only (per adult)
                            if (isset($item['guide_options']['total_price']) && $adultCount > 0) {
                                $singleSharing += floatval($item['guide_options']['total_price']) / $adultCount;
                            }
                            if (isset($item['transfer_options']['cost']) && $adultCount > 0) {
                                $singleSharing += floatval($item['transfer_options']['cost']) / $adultCount;
                            }

                            $doubleSharing = $singleSharing;

                            // Child price: sum child unit prices only (e.g. attraction 20 + restaurant 12 = 32)
                            if (!$isSupplement && $childUnitPrice > 0) {
                                $totalChildComponent += $childUnitPrice;
                            }

                            // Add adult part to other-services total (unless supplement)
                            if (!$isSupplement) {
                                $otherServiceSingle += $singleSharing;
                                $otherServiceDouble += $doubleSharing;
                            }
                        }
                        // Handle entry_port and exit_port
                        elseif ($normalizedType === 'entry_port' || $normalizedType === 'exit_port') {
                            // Calculate per adult price: totalPrice / Adults
                            $adultCount = floatval($item['adult'] ?? $item['adults'] ?? $item['adultCount'] ?? 0);
                            
                            if ($adultCount > 0) {
                                $singleSharing = $totalPriceFloat / $adultCount;
                            } else {
                                // Fallback if no adult count found, use total price as single sharing
                                $singleSharing = $totalPriceFloat;
                            }
                            
                            // Double sharing: same as single (per-person price)
                            $doubleSharing = $singleSharing;

                            if (!$isSupplement) {
                                $otherServiceSingle += $singleSharing;
                                $otherServiceDouble += $doubleSharing;
                            }
                        }
                        // Handle travel_point, travel_hourly, local_transport
                        elseif (in_array($normalizedType, ['travel_point', 'travel_hourly', 'local_transport'])) {
                            // Calculate per adult price: totalPrice / Adults
                            $adultCount = floatval($item['adult'] ?? $item['adults'] ?? $item['adultCount'] ?? 0);
                            
                            if ($adultCount > 0) {
                                $singleSharing = $totalPriceFloat / $adultCount;
                            } else {
                                // Fallback if no adult count found or 0, use total price as single sharing (divide by 1)
                                $singleSharing = $totalPriceFloat;
                            }
                            
                            // Double sharing: same as single (per-person price)
                            $doubleSharing = $singleSharing;

                            if (!$isSupplement) {
                                $otherServiceSingle += $singleSharing;
                                $otherServiceDouble += $doubleSharing;
                            }
                        }
                        // Handle guide: per adult price (totalPrice / Adults)
                        elseif ($normalizedType === 'guide') {
                            $adultCount = floatval($item['adult'] ?? $item['adults'] ?? $item['adultCount'] ?? 0);
                            
                            if ($adultCount > 0) {
                                $singleSharing = $totalPriceFloat / $adultCount;
                            } else {
                                $singleSharing = $totalPriceFloat;
                            }
                            
                            $doubleSharing = $singleSharing;

                            if (!$isSupplement) {
                                $otherServiceSingle += $singleSharing;
                                $otherServiceDouble += $doubleSharing;
                            }
                        }
                        // Default calculation for other service types
                        else {
                            // Get pax count
                            $pax = $item['pax'] 
                                ?? (($item['adult'] ?? 0) + ($item['child'] ?? 0) + ($item['infant'] ?? 0))
                                ?? (($item['adultCount'] ?? 0) + ($item['childCount'] ?? 0) + ($item['seniorCount'] ?? 0))
                                ?? null;

                            // Single sharing: per person price
                            if ($pax && $pax > 0) {
                                $singleSharing = $totalPriceFloat / floatval($pax);
                            } else {
                                $singleSharing = $totalPriceFloat;
                            }

                            // Double sharing: total / 2 (per person for 2 people)
                            $doubleSharing = $totalPriceFloat;
                            
                            if (!$isSupplement) {
                                $otherServiceSingle += $singleSharing;
                                $otherServiceDouble += $doubleSharing;
                            }
                        }

                        if ($isSupplement) {
                            // Keep supplement row as a standalone payload.
                            // Supplements are shown as the full line booking total (not per pax).
                            // single/double/triple use the same total so any existing UI column shows full price.
                            $supplementFull = (float) $totalPriceFloat;
                            $supplementRow = [
                                'type'   => $normalizedType ?? $type,
                                'single' => $supplementFull,
                                'double' => $supplementFull,
                                'triple' => $supplementFull,
                            ];

                            if (($normalizedType ?? '') === 'attraction') {
                                $supplementRow['AttractionId'] = $item['AttractionId']
                                    ?? $item['attractionId']
                                    ?? $item['attraction_id']
                                    ?? null;
                                $supplementRow['ticketName'] = $item['ticketName']
                                    ?? $item['ticket_name']
                                    ?? $item['ticket']
                                    ?? null;
                                $supplementRow['transfer_options'] = $item['transfer_options']
                                    ?? $item['transferOptions']
                                    ?? [];
                                $supplementRow['guide_options'] = $item['guide_options']
                                    ?? $item['guideOptions']
                                    ?? [];
                                // Optional name fields (used by some UIs)
                                $supplementRow['AttractionName'] = $item['AttractionName']
                                    ?? $item['attractionName']
                                    ?? $item['name']
                                    ?? null;
                            } elseif (($normalizedType ?? '') === 'restaurant') {
                                $supplementRow['restaurant_id'] = $item['restaurant_id']
                                    ?? $item['restaurantId']
                                    ?? null;
                                $supplementRow['mealType'] = $item['mealType']
                                    ?? $item['meal_type']
                                    ?? null;
                                $supplementRow['MealDescription'] = $item['MealDescription']
                                    ?? $item['mealDescription']
                                    ?? ($item['MealDescription'] ?? [])
                                    ?? [];
                                $supplementRow['restaurantName'] = $item['restaurantName']
                                    ?? $item['restaurant_name']
                                    ?? $item['name']
                                    ?? null;
                            }

                            $supplements[] = $supplementRow;
                        } else {
                            // (already added to $otherServiceSingle/Double above per service type)
                        }
                    }
                }
            }
        }

        // Append merged hotel supplements (one per hotel/date-range)
        if (!empty($hotelSupplementBuckets)) {
            foreach ($hotelSupplementBuckets as $row) {
                if (!is_array($row)) continue;
                $supplements[] = $row;
            }
        }
        
        // Add merged hotel buckets once (prevents 3Ã— multiplication when same hotel/date has multiple orders)
        $hotelSingle = 0.0;
        $hotelDouble = 0.0;
        $hotelTriple = 0.0;
        foreach ($hotelBuckets as $bucket) {
            if (!is_array($bucket)) continue;
            if ($bucket['single'] !== null) $hotelSingle += (float)$bucket['single'];
            if ($bucket['double'] !== null) $hotelDouble += (float)$bucket['double'];
            if ($bucket['triple'] !== null) $hotelTriple += (float)$bucket['triple'];
        }

        // Compute effective per-child sharing price (from attraction/restaurant components)
        $childSharing = $totalChildComponent;

        // FOC rules:
        // - discount=0: ALL services are booked for total pax; distribute total cost over paying pax
        // - discount=1: FOC hotel cost is free; hotels are charged only for paying pax (no distribution on hotel component)
        //              other services still distribute over paying pax (can be refined per service rules later)
        $hasFocDistribution = ($focDistributionFactor !== 1.0);
        $discountFlag = !empty($tour->discount) && (int)$tour->discount === 1;
        $distributionfactor=($hasFocDistribution && !$discountFlag) ? $focDistributionFactor : 1.0;
        $hotelFactor = $distributionfactor;
        $otherFactor = $distributionfactor;

        // Apply factors
        $otherServiceSingle *= $otherFactor;
        $otherServiceDouble *= $otherFactor;
        $childSharing *= $otherFactor;

        $hotelSingle *= $hotelFactor;
        $hotelDouble *= $hotelFactor;
        $hotelTriple *= $hotelFactor;

        // Final per-head totals (supplements excluded).
        // Other-service prices (attraction, restaurant, transfers, etc.) are per-pax amounts
        // that don't depend on room occupancy â€” a guest in a triple room still consumes the
        // same attractions/meals as anyone else, so the same per-pax other-services cost
        // applies to triple sharing too. (otherServiceSingle == otherServiceDouble for these.)
        $totalSingleSharing = $hotelSingle + $otherServiceSingle;
        $totalDoubleSharing = $hotelDouble + $otherServiceDouble;
        $totalTripleSharing = ($hotelTriple > 0) ? ($hotelTriple + $otherServiceSingle) : 0;

        // Hotel-wise per-head prices (apply hotel factor so discount=0 shows distributed cost, discount=1 shows paying-only)
        $hotelPriceOptions = [];
        foreach ($hotelBuckets as $hotelKey => $bucket) {
            if (!is_array($bucket)) continue;
            $meta = $hotelBucketMeta[$hotelKey] ?? [];
            $hotelPriceOptions[] = [
                'key' => $hotelKey,
                'hotel_id' => $meta['hotel_id'] ?? null,
                'hotel_name' => $meta['hotel_name'] ?? null,
                'date_range' => $meta['date_range'] ?? null,
                'display_name' => $meta['display_name'] ?? ($meta['hotel_name'] ?? $hotelKey),
                'single' => ceil((float)($bucket['single'] ?? 0) * $hotelFactor),
                'double' => ceil((float)($bucket['double'] ?? 0) * $hotelFactor),
                'triple' => ceil((float)($bucket['triple'] ?? 0) * $hotelFactor),
            ];
        }


        // Format supplements (ceiled). Non-hotel rows use full line totalPrice on single/double/triple;
        // hotel supplement rows keep per-rooming totals from the supplement stay. Hotel type carries full meta.
        $supplementsFormatted = array_map(function ($s) {
            $row = [
                'type'   => $s['type'],
                'single' => ceil((float)($s['single'] ?? 0)),
                'double' => ceil((float)($s['double'] ?? 0)),
                'triple' => ceil((float)($s['triple'] ?? 0)),
            ];

            if (($s['type'] ?? null) === 'hotel') {
                // Hotel supplement: show hotel_name, date_range, display_name (same concept as hotel_price_options)
                $row['hotel_id']     = $s['hotel_id'] ?? null;
                $row['hotel_name']   = $s['hotel_name'] ?? ($s['name'] ?? null);
                $row['date_range']   = $s['date_range'] ?? null;
                $row['display_name'] = $s['display_name'] ?? ($s['hotel_name'] ?? ($s['name'] ?? null));
                $row['name']         = $row['display_name'];
            } elseif (($s['type'] ?? null) === 'attraction') {
                $row['name']              = $s['AttractionName'] ?? ($s['name'] ?? null);
                $row['attraction_id']     = $s['AttractionId'] ?? null;
                $row['ticket']            = $s['ticketName'] ?? null;
                $row['transfer_required'] = $s['transfer_options']['transfer_required'] ?? null;
                $row['guide_required']    = $s['guide_options']['guide_required'] ?? null;
            } elseif (($s['type'] ?? null) === 'restaurant') {
                $row['name']          = $s['restaurantName'] ?? ($s['name'] ?? null);
                $row['restaurant_id'] = $s['restaurant_id'] ?? null;
                $row['mealType']      = $s['mealType'] ?? null;
                $row['quantity']      = $s['MealDescription'][0]['quantity'] ?? null;
            } elseif (($s['type'] ?? null) === 'entry_port') {
                $row['name']          = $s['name'] ?? 'Entry Port';
                $row['entry_port_id'] = $s['entry_port_id'] ?? null;
            } elseif (($s['type'] ?? null) === 'exit_port') {
                $row['name']         = $s['name'] ?? 'Exit Port';
                $row['exit_port_id'] = $s['exit_port_id'] ?? null;
            } else {
                $row['name'] = $s['name'] ?? ($s['type'] ?? null);
            }

            return $row;
        }, $supplements);

        return [
            // Per-head totals (hotel + other services, supplements excluded)
            'single_sharing'       => ceil($totalSingleSharing),
            'double_sharing'       => ceil($totalDoubleSharing),
            'triple_sharing'       => ceil($totalTripleSharing),
            // Hotel-wise per-head prices (each hotel separately, for rooming scenarios)
            'hotel_price_options'  => $hotelPriceOptions,
            // Other services per-head total (non-hotel, non-supplement)
            'other_services_single' => ceil($otherServiceSingle),
            'other_services_double' => ceil($otherServiceDouble),
            // Supplements (hotel + other services marked supplement=true)
            'supplements'          => $supplementsFormatted,
            'supplyments'          => $supplementsFormatted,
        ];
    }

    protected static function groupServicesByDate($orders)
    {
        $grouped = [];

        foreach ($orders as $order) {
            $rawData = $order->data;
            if (is_string($rawData)) {
                $rawData = json_decode($rawData, true);
            }

            if (empty($rawData)) {
                continue;
            }

            $items = isset($rawData[0]) ? $rawData : [$rawData];

            foreach ($items as $item) {
                $dates = self::extractDatesFromItem($item, $order->type);
                foreach ($dates as $date) {
                    if (!$date) {
                        continue;
                    }

                    $grouped[$date][] = self::formatServiceItem($order->type, $item);
                }
            }
        }

        // sort services inside each day by time
        foreach ($grouped as $date => $services) {
            usort($services, function ($a, $b) {
                return strcmp($a['time_sort'], $b['time_sort']);
            });
            $grouped[$date] = $services;
        }

        return $grouped;
    }

    protected static function groupServicesByType($orders, $tour = null)
    {
        $grouped = [];

        foreach ($orders as $order) {
            $typeKey = strtolower(str_replace('_', ' ', $order->type ?? 'other'));
            $rawData = $order->data;

            if (is_string($rawData)) {
                $rawData = json_decode($rawData, true);
            }

            if (empty($rawData)) {
                continue;
            }

            $items = isset($rawData[0]) ? $rawData : [$rawData];

            foreach ($items as $item) {
                $card = self::formatServiceCard($order->type, $item, $order, $tour);
                if ($card) {
                    $grouped[$typeKey][] = $card;
                }
            }
        }

        foreach ($grouped as $type => $cards) {
            usort($cards, function ($a, $b) {
                return strcmp($a['date_sort'], $b['date_sort']);
            });
            $grouped[$type] = $cards;
        }

        // Custom order for service types
        $serviceOrder = [
            'entry port' => 1,      // Arrivals
            'hotel' => 2,
            'attraction' => 3,
            'attraction package' => 3,  // Group with attractions
            'restaurant' => 3,       // Group with attractions
            'point to point' => 4,  // Local transfers
            'hourly' => 4,
            'local transport' => 4,
            'local transfer' => 4,
            'port transport' => 4,
            'local transfer vehicle' => 4,
            'travel point' => 4,
            'travel hourly' => 4,
            'guide' => 5,
            'exit port' => 6,       // Departures
        ];

        uksort($grouped, function ($a, $b) use ($serviceOrder) {
            $orderA = $serviceOrder[strtolower($a)] ?? 999;
            $orderB = $serviceOrder[strtolower($b)] ?? 999;
            
            if ($orderA === $orderB) {
                return strcmp($a, $b);
            }
            
            return $orderA <=> $orderB;
        });

        return $grouped;
    }

    protected static function extractDatesFromItem($item, $type)
    {
        $dates = [];
        $bookingDate = $item['bookingDate'] ?? null;

        if ($bookingDate) {
            if (is_array($bookingDate) && count($bookingDate) === 2) {
                try {
                    $start = Carbon::parse($bookingDate[0]);
                    $end = Carbon::parse($bookingDate[1]);

                    while ($start->lte($end)) {
                        // skip checkout day for hotels
                        if (!($type === 'hotel' && $start->isSameDay($end))) {
                            $dates[] = $start->format('Y-m-d');
                        }
                        $start->addDay();
                    }
                } catch (\Exception $e) {
                    $dates = $bookingDate;
                }
            } else {
                $bookingDate = is_array($bookingDate) ? ($bookingDate[0] ?? null) : $bookingDate;
                if ($bookingDate) {
                    $dates[] = $bookingDate;
                }
            }
        } elseif (!empty($item['pickupdate'])) {
            $dates[] = $item['pickupdate'];
        } elseif (!empty($item['exitpickupdate'])) {
            $dates[] = $item['exitpickupdate'];
        } elseif (!empty($item['date'])) {
            $dates[] = $item['date'];
        }

        return $dates;
    }

    protected static function formatServiceItem($type, $item)
    {
        $serviceType = ucwords(str_replace('_', ' ', $type));
        $title = $item['name']
            ?? $item['hotelname']
            ?? $item['AttractionName']
            ?? $item['restaurantName']
            ?? $serviceType;

        $time = $item['entrytime']
            ?? $item['visitTime']
            ?? $item['time']
            ?? $item['pickuptime']
            ?? $item['exitpickuptime']
            ?? null;

        $location = $item['entrypickup']
            ?? $item['entrydropoff']
            ?? $item['location']
            ?? $item['city']
            ?? null;

        $pax = $item['pax']
            ?? (($item['adult'] ?? 0) + ($item['child'] ?? 0) + ($item['infant'] ?? 0))
            ?? null;

        $timeSort = '99:99';
        if ($time) {
            try {
                $timeSort = Carbon::parse($time)->format('H:i');
            } catch (\Exception $e) {
                $timeSort = '99:99';
            }
        }

        return [
            'type' => $serviceType,
            'title' => $title,
            'time' => $time,
            'time_sort' => $timeSort,
            'location' => $location,
            'pax' => $pax,
            'notes' => $item['guide_name'] ?? $item['vehicle'] ?? null,
        ];
    }

    protected static function formatServiceCard($type, $item, $order = null, $tour = null)
    {
        $serviceType = ucwords(str_replace('_', ' ', $type ?? 'Service'));
        $normalizedType = strtolower(str_replace(' ', '_', $type ?? ''));
        if ($normalizedType === 'entry_port') {
            $serviceType = 'Arrival';
        } elseif ($normalizedType === 'exit_port') {
            $serviceType = 'Departure';
        }
        $title = $item['name']
            ?? $item['hotelname']
            ?? $item['AttractionName']
            ?? $item['restaurantName']
            ?? $serviceType;

        $dates = self::extractDatesFromItem($item, $type);
        $dateLabel = '';
        $dateSort = '9999-12-31';

        if (!empty($dates)) {
            $formatted = array_map(function ($date) {
                try {
                    return Carbon::parse($date)->format('d M Y');
                } catch (\Exception $e) {
                    return $date;
                }
            }, $dates);

            $dateLabel = count($formatted) > 1
                ? $formatted[0] . ' - ' . end($formatted)
                : $formatted[0];

            try {
                $dateSort = Carbon::parse($dates[0])->format('Y-m-d');
            } catch (\Exception $e) {
                $dateSort = $dates[0];
            }
        }

        $time = $item['entrytime']
            ?? $item['visitTime']
            ?? $item['time']
            ?? $item['pickuptime']
            ?? $item['exitpickuptime']
            ?? null;

        $location = $item['entrypickup']
            ?? $item['entrydropoff']
            ?? $item['location']
            ?? $item['city']
            ?? $item['address']
            ?? null;

        $pax = $item['pax']
            ?? (($item['adult'] ?? 0) + ($item['child'] ?? 0) + ($item['infant'] ?? 0))
            ?? (($item['adultCount'] ?? 0) + ($item['childCount'] ?? 0) + ($item['seniorCount'] ?? 0))
            ?? null;

        // Standardize per-item guest counts (used for transport/ports and other services)
        $adultCountStd = $item['adultCount']
            ?? $item['adults']
            ?? $item['adult']
            ?? $item['no_of_adults']
            ?? null;
        $childCountStd = $item['childCount']
            ?? $item['children']
            ?? $item['child']
            ?? $item['no_of_children']
            ?? null;
        $infantCountStd = $item['infants']
            ?? $item['infantCount']
            ?? $item['infant']
            ?? $item['no_of_infants']
            ?? null;

        $notes = $item['guide_name']
            ?? $item['vehicle']
            ?? $item['ticketName']
            ?? null;

        $chips = [];
        if ($dateLabel) {
            $chips[] = ['label' => 'Date', 'value' => $dateLabel];
        }

        if ($time) {
            $chips[] = ['label' => 'Time', 'value' => $time];
        }

        if ($pax) {
            $chips[] = ['label' => 'Pax', 'value' => $pax];
        }

        $pickup = $item['entrypickup']
            ?? $item['pickup']
            ?? $item['pickup_location']
            ?? $item['pickuplocation']
            ?? $item['exitpickup']
            ?? $item['exit_pickup']
            ?? null;

        $dropoff = $item['entrydropoff']
            ?? $item['dropoff']
            ?? $item['dropoff_location']
            ?? $item['dropofflocation']
            ?? $item['exitdropoff']
            ?? $item['exit_dropoff']
            ?? null;

        if ($pickup) {
            $chips[] = ['label' => 'Pickup', 'value' => $pickup];
        }

        if ($dropoff) {
            $chips[] = ['label' => 'Dropoff', 'value' => $dropoff];
        }

        $roomsSummary = [];
        if (strtolower($type) === 'hotel' && !empty($item['rooms']) && is_array($item['rooms'])) {
            foreach ($item['rooms'] as $room) {
                $bedSummary = [];
                if (!empty($room['beds']) && is_array($room['beds'])) {
                    foreach ($room['beds'] as $bed) {
                        // Fetch bed type from beds table using bed_id
                        $bedType = 'Bed'; // default
                        if (!empty($bed['bed_id'])) {
                            try {
                                $bedRecord = Bed::where('bed_id', $bed['bed_id'])->first();
                                if ($bedRecord && !empty($bedRecord->room_type)) {
                                    $bedType = $bedRecord->room_type;
                                }
                            } catch (\Exception $e) {
                                Log::warning('Failed to fetch bed from beds table', [
                                    'bed_id' => $bed['bed_id'],
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                        
                        // Fallback to bed_type if bed_id lookup fails
                        if ($bedType === 'Bed' && !empty($bed['bed_type'])) {
                            $bedType = self::friendlyLabel($bed['bed_type'], 'Bed');
                        }
                        
                        $bedSummary[] = [
                            'type' => $bedType,
                            'occupancy' => $bed['head_count'] ?? null,
                            'meal' => $bed['selectedMeals']['meal_plan']['type']
                                ?? $bed['selectedMeals']['meal_1']['type']
                                ?? ($bed['mealTypes'][0] ?? null)
                                ?? $bed['mealType']
                                ?? null,
                            'meal_price' => $bed['selectedMeals']['meal_plan']['price']
                                ?? $bed['selectedMeals']['meal_1']['price']
                                ?? null,
                            'price' => $bed['price'] ?? null,
                        ];
                    }
                }

                $roomsSummary[] = [
                    'name' => self::friendlyLabel($room['room_type'] ?? null, 'Room'),
                    'beds' => $bedSummary,
                ];
            }
        }

        $transferTypes = [
            'entry_port',
            'exit_port',
            'point_to_point',
            'hourly',
            'travel_point',
            'travel_hourly',
            'local_transport',
            'local_transfer',
            'port_transport',
            'local_transfer_vehicle',
        ];

        // Entry port flight details
        $entryPortFlightDetails = null;
        if (strtolower($type) === 'entry_port') {
            $entryPortFlightDetails = [
                'flight_name' => $item['flightName'] ?? $item['flight_name'] ?? $item['originFlightName'] ?? null,
                'flight_no' => $item['flightNo'] ?? $item['flight_no'] ?? $item['originFlightNumber'] ?? $item['arrivalFlightNumber'] ?? null,
                'origin_departure_time' => $item['departureTime'] ?? $item['departure_time'] ?? $item['originDepartureTime'] ?? null,
                'origin_departure_terminal' => $item['originTerminal'] ?? $item['origin_terminal'] ?? $item['originDepartureTerminal'] ?? null,
                'destination_arrival_time' => $item['arrivalTime'] ?? $item['arrival_time'] ?? $item['destinationArrivalTime'] ?? $item['entrytime'] ?? null,
                'destination_arrival_terminal' => $item['arrivalTerminal'] ?? $item['arrival_terminal'] ?? $item['destinationArrivalTerminal'] ?? null,
            ];
        }

        // Exit port flight details
        $exitPortFlightDetails = null;
        if (strtolower($type) === 'exit_port') {
            $exitPortFlightDetails = [
                'flight_name' => $item['flightName'] ?? $item['flight_name'] ?? $item['originFlightName'] ?? null,
                'flight_no' => $item['flightNo'] ?? $item['flight_no'] ?? $item['originFlightNumber'] ?? $item['arrivalFlightNumber'] ?? null,
                'origin_departure_time' => $item['departureTime'] ?? $item['departure_time'] ?? $item['originDepartureTime'] ?? $item['exitpickuptime'] ?? $item['exit_time'] ?? null,
                'origin_departure_terminal' => $item['originTerminal'] ?? $item['origin_terminal'] ?? $item['originDepartureTerminal'] ?? null,
                'destination_arrival_time' => $item['arrivalTime'] ?? $item['arrival_time'] ?? $item['destinationArrivalTime'] ?? null,
                'destination_arrival_terminal' => $item['arrivalTerminal'] ?? $item['arrival_terminal'] ?? $item['destinationArrivalTerminal'] ?? null,
            ];
        }

        $vehicleDetails = null;
        if (in_array(strtolower($type), $transferTypes, true)) {
            // Get transfer type (from transfer_options or direct item)
            $transferOptions = $item['transfer_options'] ?? null;
            $transferType = null;
            $transferWay = null;
            if ($transferOptions && !empty($transferOptions['type'])) {
                $transferType = $transferOptions['type'];
            } else {
                // For travel_point, travel_hourly, local_transport, and local_transfer, use the type field directly
                $transferType = $item['type'] ?? null;
            }
            // Way (One Way / Two Way / Both Way depending on UI/payload)
            if ($transferOptions && (isset($transferOptions['way']) || isset($transferOptions['Way']))) {
                $transferWay = $transferOptions['way'] ?? $transferOptions['Way'];
            } else {
                $transferWay = $item['way'] ?? $item['Way'] ?? $item['transfer_way'] ?? null;
            }
            // Normalize way values like "both-way"/"one-way"/"2-way"/"1-way" for display
            if (is_string($transferWay)) {
                $wayRaw = trim($transferWay);
                $wayNorm = strtolower(str_replace(['_', ' '], '-', $wayRaw));
                if (in_array($wayNorm, ['one-way', '1-way'], true)) {
                    $transferWay = 'One Way';
                } elseif (in_array($wayNorm, ['both-way', 'two-way', '2-way'], true)) {
                    // UI sometimes uses "both-way" but business meaning is round trip
                    $transferWay = 'Both Way';
                } else {
                    // Keep original but make it readable if it's like "both-way"
                    $transferWay = ucwords(str_replace('-', ' ', $wayRaw));
                }
            }
            
            // Get vehicle details (from transfer_options.vehicle_details or direct item)
            $vehicleDetailsFromOptions = $transferOptions['vehicle_details'] ?? null;
            
            $vehicleType = null;
            $seatingCapacity = null;
            $vehicleNumber = null;
            $vehicleBrand = null;
            
            // Try to fetch from Vehicle model - check jobsheet first, then fallback to vehicles_id
            $vehicleRecord = null;
            $vehicleId = null;
            
            // Check jobsheet first if order and tour are available
            if ($order && $tour && !empty($order->booking_id)) {
                try {
                    $jobsheet = Jobsheet::where('order_id', $order->booking_id)->first();
                    if ($jobsheet && !empty($jobsheet->vehicle_id)) {
                        // Get vehicle from jobsheet where vehicle_id matches and dmc_id matches tour->dmc_id
                        $vehicleRecord = Vehicle::where('vehicle_id', $jobsheet->vehicle_id)
                            ->where('dmc_id', $tour->dmc_id)
                            ->first();
                        if ($vehicleRecord) {
                            $vehicleId = $jobsheet->vehicle_id;
                        }
                    }
                } catch (\Exception $e) {
                    // If jobsheet check fails, continue to fallback
                    Log::warning('Failed to fetch vehicle from jobsheet', [
                        'booking_id' => $order->booking_id ?? null,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Fallback to vehicles_id from item if no jobsheet vehicle found
            if (!$vehicleRecord && !empty($item['vehicles_id'])) {
                try {
                    $vehicleRecord = Vehicle::where('vehicle_id', $item['vehicles_id'])->first();
                    if ($vehicleRecord) {
                        $vehicleId = $item['vehicles_id'];
                    }
                } catch (\Exception $e) {
                    // If Vehicle model not found, continue without it
                }
            }
            
            if ($vehicleRecord) {
                $vehicleType = $vehicleRecord->vehicle_type ?? null;
                $seatingCapacity = $vehicleRecord->sitting_capacity ?? $vehicleRecord->seating_capacity ?? $vehicleRecord->max_passenger_capacity ?? null;
                $vehicleNumber = $vehicleRecord->vehicle_plate_no ?? null;
                $vehicleBrand = $vehicleRecord->vehicle_model ?? $vehicleRecord->vehicle_name ?? null;
            }
            
            // Get from transfer_options.vehicle_details if available
            if ($vehicleDetailsFromOptions && is_array($vehicleDetailsFromOptions)) {
                $vehicleType = $vehicleType ?? $vehicleDetailsFromOptions['vehicle_type'] ?? null;
                $seatingCapacity = $seatingCapacity ?? $vehicleDetailsFromOptions['seating_capacity'] ?? null;
            }
            
            // Parse vehicles_name if it contains type and seating info (e.g., "Jaguar F-Pace (SUV) - 7 seats")
            $vehiclesName = $item['vehicles_name'] ?? null;
            if ($vehiclesName && (!$vehicleType || !$seatingCapacity)) {
                // Try to extract from format like "Jaguar F-Pace (SUV) - 7 seats"
                if (preg_match('/\(([^)]+)\)/', $vehiclesName, $typeMatch)) {
                    $vehicleType = $vehicleType ?? $typeMatch[1];
                }
                if (preg_match('/(\d+)\s*seat/i', $vehiclesName, $seatMatch)) {
                    $seatingCapacity = $seatingCapacity ?? $seatMatch[1];
                }
            }
            
            // Fallback to direct item fields
            $vehicleType = $vehicleType ?? $item['vehicle_type'] ?? null;
            $seatingCapacity = $seatingCapacity ?? $item['seating_capacity'] ?? null;
            $vehicleNumber = $vehicleNumber ?? $item['vehicle_number'] ?? $item['vehicleNumber'] ?? null;
            $vehicleBrand = $vehicleBrand ?? $item['vehicle_brand'] ?? $item['vehicleBrand'] ?? $item['vehicle_model'] ?? null;
            
            // Format Vehicle Type / Seater
            $vehicleTypeSeater = '';
            if ($vehicleType && $seatingCapacity) {
                $vehicleTypeSeater = $vehicleType . ' / ' . $seatingCapacity . ' Seater';
            } elseif ($vehicleType) {
                $vehicleTypeSeater = $vehicleType;
            } elseif ($seatingCapacity) {
                $vehicleTypeSeater = $seatingCapacity . ' Seater';
            } else {
                $vehicleTypeSeater = 'N/A';
            }
            
            $vehicleDetails = [
                'name' => $vehiclesName,
                'type' => $item['type'] ?? null,
                'transfer_type' => $transferType ?? $item['type'] ?? null,
                'way' => $transferWay,
                'vehicle_type' => $vehicleType,
                'vehicle_type_seater' => $vehicleTypeSeater,
                'vehicle_number' => $vehicleNumber ?: 'N/A',
                'vehicle_brand' => $vehicleBrand ?: 'N/A',
                'seating_capacity' => $seatingCapacity,
                'max_passenger_capacity' => $seatingCapacity ?: 'N/A', // Same as seating capacity
                'vehicle_model' => $item['vehicle_model'] ?? null,
                'model_year' => $item['model_year'] ?? null,
                'travel_type' => $item['travel_type'] ?? null,
                'mode' => $item['Mode'] ?? $item['mode'] ?? null,
            ];
        }

        // Attraction details
        $attractionDetails = null;
        if (in_array(strtolower($type), ['attraction', 'attraction_package'], true)) {
            $adultCount = $item['adultCount'] ?? $item['adult'] ?? 0;
            $childCount = $item['childCount'] ?? $item['child'] ?? 0;
            $seniorCount = $item['seniorCount'] ?? $item['senior'] ?? 0;
            
            // Extract transfer options - prioritize transfer_options over Selection
            $transferOptions = $item['transfer_options'] ?? null;
            $transferRequired = 'N/A';
            $transferType = 'N/A';
            
            if ($transferOptions) {
                // Get transfer_required from transfer_options
                if (isset($transferOptions['transfer_required'])) {
                    $transferRequired = $transferOptions['transfer_required'] ? 'Yes' : 'No';
                }
                // Get transfer type from transfer_options
                if (!empty($transferOptions['type'])) {
                    $transferType = $transferOptions['type'];
                }
            }
            
            $transportNote = null;
            if ($transferRequired === 'No') {
                $transportNote = 'Transport not included';
            }

            $transferSummary = null;
            if ($transferOptions && is_array($transferOptions)) {
                $vd = $transferOptions['vehicle_details'] ?? [];
                $transferSummary = [
                    'required' => !empty($transferOptions['transfer_required']),
                    'type' => $transferOptions['type'] ?? null,
                    'way' => $transferOptions['way'] ?? ($transferOptions['Way'] ?? null),
                    'vehicle_name' => $transferOptions['vehicle_name'] ?? ($vd['vehicle_name'] ?? null),
                    'vehicle_type' => is_array($vd) ? ($vd['vehicle_type'] ?? null) : null,
                    'seating_capacity' => is_array($vd) ? ($vd['seating_capacity'] ?? null) : null,
                    'pickup_location_name' => $transferOptions['pickup_location_name'] ?? null,
                    'pickup_time' => $transferOptions['pickup_time'] ?? null,
                    'cost' => $transferOptions['cost'] ?? null,
                ];
            }

            $guideSummary = null;
            $guideOptions = $item['guide_options'] ?? null;
            if (!empty($guideOptions) && is_array($guideOptions)) {
                $guideSummary = [
                    'required' => !empty($guideOptions['guide_required']),
                    'guide_name' => $guideOptions['guide_name'] ?? null,
                    'language' => $guideOptions['language'] ?? null,
                    'pickup_time' => $guideOptions['pickup_time'] ?? null,
                    'package_hours' => $guideOptions['package_hours'] ?? null,
                    'hours' => $guideOptions['hours'] ?? null,
                    'base_price' => $guideOptions['base_price'] ?? null,
                    'surcharge' => $guideOptions['surcharge'] ?? null,
                    'total_price' => $guideOptions['total_price'] ?? null,
                ];
            }

            $attractionDetails = [
                'adult_count' => $adultCount > 0 ? $adultCount : null,
                'child_count' => $childCount > 0 ? $childCount : null,
                'senior_count' => $seniorCount > 0 ? $seniorCount : null,
                'visit_time' => $item['visitTime'] ?? null,
                'transport_note' => $transportNote,
                'transfer_required' => $transferRequired,
                'transfer_type' => $transferType,
                'transfer' => $transferSummary,
                'guide' => $guideSummary,
            ];
        }

        // Restaurant details (similar structure to attractions)
        $restaurantDetails = null;
        if (strtolower($type) === 'restaurant') {
            $adultCount = $item['adultCount'] ?? $item['adult'] ?? 0;
            $childCount = $item['childCount'] ?? $item['child'] ?? 0;
            // Restaurant uses "infants" in some payloads (older payloads may use seniorCount)
            $infantCount = $item['infants'] ?? $item['infantCount'] ?? $item['infant'] ?? 0;
            $seniorCount = $item['seniorCount'] ?? $item['senior'] ?? 0;
            
            $mealItems = [];
            if (!empty($item['MealDescription']) && is_array($item['MealDescription'])) {
                foreach ($item['MealDescription'] as $mealItem) {
                    $itemName = $mealItem['item_name'] ?? $mealItem['name'] ?? null;
                    if ($itemName) {
                        $mealItems[] = [
                            'item_name' => $itemName,
                            'name' => $mealItem['name'] ?? null,
                            'quantity' => $mealItem['quantity'] ?? 1,
                        ];
                    }
                }
            }
            
            // Extract transfer options
            $transferOptions = $item['transfer_options'] ?? null;
            $transferRequired = 'N/A';
            $transferType = 'N/A';
            
            if ($transferOptions) {
                // Get transfer_required from transfer_options
                if (isset($transferOptions['transfer_required'])) {
                    $transferRequired = $transferOptions['transfer_required'] ? 'Yes' : 'No';
                }
                // Get transfer type from transfer_options
                if (!empty($transferOptions['type'])) {
                    $transferType = $transferOptions['type'];
                }
            }

            $restaurantTransferSummary = null;
            if ($transferOptions && is_array($transferOptions)) {
                $vd = $transferOptions['vehicle_details'] ?? [];
                $restaurantTransferSummary = [
                    'required' => !empty($transferOptions['transfer_required']),
                    'type' => $transferOptions['type'] ?? null,
                    'way' => $transferOptions['way'] ?? ($transferOptions['Way'] ?? null),
                    'vehicle_name' => $transferOptions['vehicle_name'] ?? ($vd['vehicle_name'] ?? null),
                    'vehicle_type' => is_array($vd) ? ($vd['vehicle_type'] ?? null) : null,
                    'seating_capacity' => is_array($vd) ? ($vd['seating_capacity'] ?? null) : null,
                    'pickup_location_name' => $transferOptions['pickup_location_name'] ?? null,
                    'pickup_time' => $transferOptions['pickup_time'] ?? null,
                    'cost' => $transferOptions['cost'] ?? null,
                ];
            }
            
            // Clean mealSpecificType to remove emojis and special characters
            $mealSpecificType = $item['mealSpecificType'] ?? null;
            if ($mealSpecificType) {
                // Remove all non-ASCII characters except spaces, keep only printable ASCII (32-126)
                // This will remove all emojis, special Unicode characters, and any characters that might render as "?"
                $mealSpecificType = preg_replace('/[^\x20-\x7E]/u', '', $mealSpecificType);
                $mealSpecificType = trim($mealSpecificType); // Remove leading/trailing whitespace
            }
            
            $restaurantDetails = [
                'ticket_name' => $item['ticketName'] ?? null,
                'adult_count' => $adultCount > 0 ? $adultCount : null,
                'child_count' => $childCount > 0 ? $childCount : null,
                'infant_count' => $infantCount > 0 ? $infantCount : null,
                'senior_count' => $seniorCount > 0 ? $seniorCount : null, // kept for backward compatibility
                'visit_time' => $item['visitTime'] ?? null,
                'meal_type' => $mealSpecificType ?: null,
                'meal_plan' => $item['mealType'] ?? null,
                'meal_items' => $mealItems,
                'transfer_required' => $transferRequired,
                'transfer_type' => $transferType,
                'transfer' => $restaurantTransferSummary,
            ];
        }

        // Guide details
        $guideDetails = null;
        if (strtolower($type) === 'guide') {
            $languages = $item['languages'] ?? [];
            if (is_string($languages)) {
                $languages = json_decode($languages, true) ?? [];
            }
            if (!is_array($languages)) {
                $languages = [];
            }
            
            // Format languages as comma-separated string for Language Proficiency
            $languageProficiency = '';
            if (!empty($languages)) {
                $languageList = [];
                foreach ($languages as $lang) {
                    if (is_array($lang)) {
                        // If language is an array with 'language' and 'proficiency' keys
                        $langName = $lang['language'] ?? '';
                        $proficiency = $lang['proficiency'] ?? '';
                        if ($langName) {
                            $languageList[] = $proficiency ? $langName . ' (' . $proficiency . ')' : $langName;
                        }
                    } else {
                        // If language is a simple string
                        $languageList[] = $lang;
                    }
                }
                $languageProficiency = implode(', ', $languageList);
            }
            $guide = Guide::where('guide_id', $item['guide_id'])->first();
            // Get total experience (try experience_years first, then experience)
            $totalExperience = $guide->experience_years ?? $guide->experience ?? null;
            if ($totalExperience !== null) {
                $totalExperience = $totalExperience . ' years';
            }
            
            $guideDetails = [
                'guide_name' => $guide->name ?? null,
                'language_proficiency' => $languageProficiency ?: 'N/A',
                'total_experience' => $totalExperience ?: 'N/A',
                'languages' => array_filter($languages),
                'hours' => $item['hours'] ?? null,
                'entry_time' => $item['entrytime'] ?? null,
            ];
        }

        return [
            'type' => $serviceType,
            'title' => $title,
            'subtitle' => $location,
            'time' => $time,
            'pax' => $pax,
            'adult_count' => (is_numeric($adultCountStd) && (float)$adultCountStd > 0) ? (int)$adultCountStd : null,
            'child_count' => (is_numeric($childCountStd) && (float)$childCountStd > 0) ? (int)$childCountStd : null,
            'infant_count' => (is_numeric($infantCountStd) && (float)$infantCountStd > 0) ? (int)$infantCountStd : null,
            'notes' => $notes,
            'chips' => $chips,
            'icon' => self::serviceIcon($type),
            'date_sort' => $dateSort,
            'rooms' => $roomsSummary,
            'hotel_info' => [
                'name' => $item['hotelDetails']['hotel_name'] ?? null,
                'location' => $item['hotelDetails']['location'] ?? null,
                'check_in_time' => $item['hotelDetails']['checkInTime'] ?? null,
                'check_out_time' => $item['hotelDetails']['checkOutTime'] ?? null,
            ],
            'vehicle' => $vehicleDetails,
            'attraction' => $attractionDetails,
            'restaurant' => $restaurantDetails,
            'guide' => $guideDetails,
            'entry_port_flight' => $entryPortFlightDetails,
            'exit_port_flight' => $exitPortFlightDetails,
        ];
    }

    protected static function serviceIcon($type)
    {
        $map = [
            'hotel' => 'ðŸ¨',
            'guide' => 'ðŸ‘¤',
            'restaurant' => 'ðŸ½ï¸',
            'attraction' => 'ðŸŽ¯',
            'entry_port' => 'âœˆï¸',
            'exit_port' => 'ðŸ›«',
            'travel_point' => 'ðŸš',
            'travel_hourly' => 'ðŸš—',
            'local_transport' => 'ðŸš•',
        ];

        $key = strtolower($type ?? '');
        return $map[$key] ?? 'ðŸ§­';
    }

    protected static function friendlyLabel($value, $fallback = 'N/A')
    {
        if (empty($value)) {
            return $fallback;
        }

        if (is_string($value) && preg_match('/^\s*\d+\s*$/', $value)) {
            return $fallback;
        }

        return $value;
    }

    /**
     * Format hotels for Excel-like PDF display
     * Returns array of hotel options with pricing details
     */
    protected static function formatHotelsForPdf($orders, $tour = null, $tourPrices = null)
    {
        $hotelOptions = [];
        $hotelIndex = 1;
        $segregated = $tourPrices !== null ? ($tourPrices['segregated'] ?? []) : [];
        $segHotelKeys = ['hotel', 'attraction', 'restaurant', 'entry_port', 'exit_port', 'guide', 'travel_hourly', 'travel_point', 'local_transport', 'other'];

        foreach ($orders as $order) {
            if (strtolower($order->type ?? '') !== 'hotel') {
                continue;
            }

            $rawData = $order->data;
            if (is_string($rawData)) {
                $rawData = json_decode($rawData, true);
            }

            if (empty($rawData)) {
                continue;
            }

            $items = isset($rawData[0]) ? $rawData : [$rawData];

            foreach ($items as $item) {
                $hotelName = $item['hotelDetails']['hotel_name'] ?? $item['hotelname'] ?? 'N/A';
                $hotelCategory = $item['hotelDetails']['category'] ?? $item['hotelDetails']['category_name'] ?? 'N/A';
                $rooms = $item['rooms'] ?? [];
                $childPrice = $item['childPrice'] ?? $item['child_price'] ?? 'N/A';
                $infantPrice = $item['infantPrice'] ?? $item['infant_price'] ?? 'N/A';

                // Use prices from tourPrices['segregated'][hotelName] when available (no separate room calculation)
                $segThis = isset($segregated[$hotelName]) && !in_array($hotelName, $segHotelKeys, true)
                    ? $segregated[$hotelName]
                    : null;

                if ($segThis !== null) {
                    $firstTotalSingle = (float) ($segThis['single'] ?? 0);
                    $firstTotalDouble = (float) ($segThis['double'] ?? 0);
                    $firstTotalTriple = (float) ($segThis['triple'] ?? 0);
                    $firstTotalBabyCot = (float) ($segThis['baby_cot'] ?? 0);
                    $adultPrice = 'N/A';
                    $roomTypeName = 'N/A';
                    // Also compute room counts from rooms payload (for display)
                    $totalSingleRooms = 0;
                    $totalDoubleRooms = 0;
                    $totalTripleRooms = 0;
                    if (is_array($rooms) && count($rooms) > 0) {
                        $firstRoom = $rooms[0];
                        $roomTypeName = $firstRoom['room_type'] ?? $firstRoom['roomType'] ?? 'N/A';
                        foreach ($rooms as $room) {
                            $noOfRooms = (int)($room['no_of_room'] ?? $room['number_of_rooms'] ?? 0);
                            $noOfRooms = $noOfRooms > 0 ? $noOfRooms : 1;
                            $beds = (is_array($room) && isset($room['beds']) && is_array($room['beds'])) ? $room['beds'] : [];
                            if (!empty($beds)) {
                                foreach ($beds as $bed) {
                                    $occupancy = (int)($bed['head_count'] ?? $bed['occupancy'] ?? 1);
                                    if ($occupancy >= 3) {
                                        $totalTripleRooms += $noOfRooms;
                                    } elseif ($occupancy >= 2) {
                                        $totalDoubleRooms += $noOfRooms;
                                    } else {
                                        $totalSingleRooms += $noOfRooms;
                                    }
                                }
                            } else {
                                $totalSingleRooms += $noOfRooms;
                            }
                        }
                    }
                    $roomCategories = [
                        [
                            'name' => $roomTypeName,
                            'single_price' => $firstTotalSingle,
                            'double_price' => $firstTotalDouble,
                            'triple_price' => $firstTotalTriple,
                            'child_price' => 0,
                        ],
                    ];
                    $supplementalSingle = 0;
                    $supplementalDouble = 0;
                    $supplementalTriple = 0;
                } else {
                    // Fallback when tourPrices not passed or hotel not in segregated: use existing room calculation
                    $totalPrice = floatval($item['totalPrice'] ?? $item['price'] ?? 0);
                    $transferCost = floatval($item['transfer_options']['cost'] ?? 0);
                    $headCount = 0;
                    foreach ($rooms as $room) {
                        foreach ($room['beds'] ?? [] as $bed) {
                            $headCount += (int)($bed['head_count'] ?? 0);
                        }
                    }
                    $adultPrice = $headCount > 0 ? ceil(($transferCost + $totalPrice) / $headCount) : 'N/A';
                    $roomCategories = [];
                    $totalSingleRooms = 0;
                    $totalDoubleRooms = 0;
                    $totalTripleRooms = 0;
                    if (is_array($rooms) && count($rooms) > 0) {
                        $roomsByType = [];
                        foreach ($rooms as $room) {
                            $roomType = $room['room_type'] ?? 'N/A';
                            if (!isset($roomsByType[$roomType])) {
                                $roomsByType[$roomType] = [];
                            }
                            $roomsByType[$roomType][] = $room;
                        }
                        foreach ($roomsByType as $roomType => $roomsOfType) {
                            $firstRoom = $roomsOfType[0];
                            $noOfRooms = 0;
                            foreach ($roomsOfType as $room) {
                                $noOfRooms += (int)($room['no_of_room'] ?? $room['number_of_rooms'] ?? 0);
                            }
                            $prices = self::calculateHotelRoomPrices($item, $firstRoom, $tour);
                            $singlePriceTotal = floatval($prices['single_total'] ?? 0);
                            $doublePriceTotal = floatval($prices['double_total'] ?? 0);
                            $triplePriceTotal = floatval($prices['triple_total'] ?? 0);
                            $beds = $firstRoom['beds'] ?? [];
                            $roomSingleCount = 0;
                            $roomDoubleCount = 0;
                            $roomTripleCount = 0;
                            if (is_array($beds) && count($beds) > 0) {
                                foreach ($beds as $bed) {
                                    $occupancy = (int)($bed['head_count'] ?? $bed['occupancy'] ?? 1);
                                    if ($occupancy >= 3) {
                                        $roomTripleCount += $noOfRooms;
                                    } elseif ($occupancy >= 2) {
                                        $roomDoubleCount += $noOfRooms;
                                    } else {
                                        $roomSingleCount += $noOfRooms;
                                    }
                                }
                            } else {
                                $roomSingleCount = $noOfRooms;
                            }
                            $totalSingleRooms += $roomSingleCount;
                            $totalDoubleRooms += $roomDoubleCount;
                            $totalTripleRooms += $roomTripleCount;
                            $roomCategories[] = [
                                'name' => $roomType,
                                'single_price' => $singlePriceTotal,
                                'double_price' => $doublePriceTotal,
                                'triple_price' => $triplePriceTotal,
                            ];
                        }
                    }
                    if (count($roomCategories) === 0) {
                        $roomCategories = [['name' => 'N/A', 'single_price' => 0, 'double_price' => 0, 'triple_price' => 0]];
                    }
                    $firstTotalSingle = 0;
                    $firstTotalDouble = 0;
                    $firstTotalTriple = 0;
                    foreach ($roomCategories as $roomCat) {
                        $firstTotalSingle += floatval($roomCat['single_price'] ?? 0);
                        $firstTotalDouble += floatval($roomCat['double_price'] ?? 0);
                        $firstTotalTriple += floatval($roomCat['triple_price'] ?? 0);
                    }
                    $supplementalSingle = $firstTotalSingle - $firstTotalDouble;
                    $supplementalDouble = 0;
                    $supplementalTriple = 0;
                    $adultPrice = $headCount > 0 ? ceil(($transferCost + $totalPrice) / $headCount) : 'N/A';
                }

                $hotelOptions[] = [
                    'option_number' => $hotelIndex++,
                    'hotel_name' => $hotelName,
                    'hotel_category' => $hotelCategory,
                    // Keep raw rooms payload so email template can extract beds[*].head_count
                    'rooms' => is_array($rooms) ? $rooms : [],
                    'adult_price' => isset($adultPrice) && is_numeric($adultPrice) ? number_format($adultPrice, 2) : ($adultPrice ?? 'N/A'),
                    'child_price' => is_numeric($childPrice) ? number_format($childPrice, 2) : ($childPrice ?? 'N/A'),
                    'infant_price' => is_numeric($infantPrice) ? number_format($infantPrice, 2) : ($infantPrice ?? 'N/A'),
                    'no_of_rooms' => [
                        'single' => $totalSingleRooms ?? 0,
                        'double' => $totalDoubleRooms ?? 0,
                        'triple' => $totalTripleRooms ?? 0,
                    ],
                    'room_categories' => $roomCategories,
                    'first_total' => [
                        'single' => $firstTotalSingle,
                        'double' => $firstTotalDouble,
                        'triple' => $firstTotalTriple,
                        'child' => 0,
                        'baby_cot' => $firstTotalBabyCot ?? 0,
                    ],
                    'supplemental_cost' => [
                        'single' => $supplementalSingle,
                        'double' => $supplementalDouble,
                        'triple' => $supplementalTriple,
                    ],
                    'final_total' => [
                        'single' => $firstTotalSingle + $supplementalSingle,
                        'double' => $firstTotalDouble + $supplementalDouble,
                        'triple' => $firstTotalTriple + $supplementalTriple,
                    ],
                ];
            }
        }

        // Add package total (this hotel + all other services) for each hotel
        if ($tourPrices !== null && !empty($hotelOptions)) {
            $totalSingle = (float) ($tourPrices['single_sharing'] ?? 0);
            $totalDouble = (float) ($tourPrices['double_sharing'] ?? 0);
            $totalTriple = (float) ($tourPrices['triple_sharing'] ?? 0);
            $totalBabyCot = (float) ($tourPrices['baby_cot_sharing'] ?? 0);
            $childSharing = (float) ($tourPrices['child_sharing'] ?? 0);
            $segregated = $tourPrices['segregated'] ?? [];
            $segHotel = $segregated['hotel'] ?? ['single' => 0, 'double' => 0, 'triple' => 0, 'baby_cot' => 0];

            foreach ($hotelOptions as &$hotel) {
                $hotelName = $hotel['hotel_name'] ?? '';
                $segThis = $segregated[$hotelName] ?? ['single' => 0, 'double' => 0, 'triple' => 0, 'baby_cot' => 0];
                $thisSingle = (float) ($segThis['single'] ?? 0);
                $thisDouble = (float) ($segThis['double'] ?? 0);
                $thisTriple = (float) ($segThis['triple'] ?? 0);
                $thisBabyCot = (float) ($segThis['baby_cot'] ?? 0);
                $allHotelsSingle = (float) ($segHotel['single'] ?? 0);
                $allHotelsDouble = (float) ($segHotel['double'] ?? 0);
                $allHotelsTriple = (float) ($segHotel['triple'] ?? 0);
                $allHotelsBabyCot = (float) ($segHotel['baby_cot'] ?? 0);

                // Package total = total - (all other hotels) + this hotel; subtract every other hotel's price
                $hotel['package_total'] = [
                    'single'  => ceil($totalSingle - $allHotelsSingle + $thisSingle),
                    'double'  => ceil($totalDouble - $allHotelsDouble + $thisDouble),
                    'triple'  => ceil($totalTriple - $allHotelsTriple + $thisTriple),
                    'child'   => ceil($childSharing),
                    'infant'  => ceil($totalBabyCot - $allHotelsBabyCot + $thisBabyCot),
                ];
            }
            unset($hotel);
        }
        return $hotelOptions;
    }

    /**
     * Calculate hotel room prices for a specific room using the same logic as calculateTourPrices
     * Returns per-night prices for single, double, and triple sharing
     */
    protected static function calculateHotelRoomPrices($item, $room, $tour = null)
    {
        $hotelId = $item['hotelDetails']['hotel_id'] ?? $item['hotelDetails']['hotelId'] ?? $item['hotel_id'] ?? $item['hotelId'] ?? null;
        $weekendDays = ['Saturday', 'Sunday']; // Default fallback
        
        // Get weekend days from hotel
        if ($hotelId) {
            try {
                $hotel = Hotel::where('hotel_unique_id', $hotelId)->first();
                if ($hotel && $hotel->weekend_days) {
                    $decodedWeekendDays = json_decode($hotel->weekend_days, true);
                    if (is_array($decodedWeekendDays) && !empty($decodedWeekendDays)) {
                        $weekendDays = $decodedWeekendDays;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to fetch hotel weekend_days', [
                    'hotel_id' => $hotelId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Get room prices from database
        $singleWeekdayPrice = null;
        $singleWeekendPrice = null;
        $doubleWeekdayPrice = null;
        $doubleWeekendPrice = null;
        
        $roomtype = $room['room_type'] ?? $room['roomType'] ?? null;
        
        if ($roomtype && $hotelId) {
            try {
                $hotel = Hotel::where('hotel_unique_id', $hotelId)->first();
                $dbHotelId = $hotel ? $hotel->hotel_unique_id : $hotelId;
                
                $roomRecord = Room::where('room_type', $roomtype)
                    ->where('hotel_id', $dbHotelId)
                    ->where('status', 1)
                    ->first();
                
                if ($roomRecord) {
                    if ($roomRecord->weekday_price !== null && $roomRecord->weekday_price !== '') {
                        $singleWeekdayPrice = floatval($roomRecord->weekday_price);
                    }
                    if ($roomRecord->weekend_price !== null && $roomRecord->weekend_price !== '') {
                        $singleWeekendPrice = floatval($roomRecord->weekend_price);
                    }
                    if ($roomRecord->double_weekday_price !== null && $roomRecord->double_weekday_price !== '') {
                        $doubleWeekdayPrice = floatval($roomRecord->double_weekday_price) / 2;
                    }
                    if ($roomRecord->double_weekend_price !== null && $roomRecord->double_weekend_price !== '') {
                        $doubleWeekendPrice = floatval($roomRecord->double_weekend_price) / 2;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to fetch room prices from database', [
                    'room_type' => $roomtype,
                    'hotel_id' => $hotelId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Fallback to room data if database prices not found
        if ($singleWeekdayPrice === null) {
            $weekdayPrice = $room['weekday_price'] ?? $room['weekdayPrice'] ?? null;
            if ($weekdayPrice !== null && $weekdayPrice !== '') {
                $singleWeekdayPrice = floatval($weekdayPrice);
            }
        }
        if ($singleWeekendPrice === null) {
            $weekendPrice = $room['weekend_price'] ?? $room['weekendPrice'] ?? null;
            if ($weekendPrice !== null && $weekendPrice !== '') {
                $singleWeekendPrice = floatval($weekendPrice);
            }
        }
        if ($doubleWeekdayPrice === null) {
            $doubleWeekdayPriceVal = $room['double_weekday_price'] ?? $room['doubleWeekdayPrice'] ?? null;
            if ($doubleWeekdayPriceVal !== null && $doubleWeekdayPriceVal !== '') {
                $doubleWeekdayPrice = floatval($doubleWeekdayPriceVal) / 2;
            }
        }
        if ($doubleWeekendPrice === null) {
            $doubleWeekendPriceVal = $room['double_weekend_price'] ?? $room['doubleWeekendPrice'] ?? null;
            if ($doubleWeekendPriceVal !== null && $doubleWeekendPriceVal !== '') {
                $doubleWeekendPrice = floatval($doubleWeekendPriceVal) / 2;
            }
        }

        // Get booking dates
        $bookingDates = [];
        $bookingDate = $item['bookingDate'] ?? null;
        
        if ($bookingDate) {
            if (is_array($bookingDate) && count($bookingDate) === 2) {
                try {
                    $start = Carbon::parse($bookingDate[0]);
                    $end = Carbon::parse($bookingDate[1]);
                    
                    while ($start->lt($end)) {
                        $bookingDates[] = $start->copy();
                        $start->addDay();
                    }
                } catch (\Exception $e) {
                    if ($tour && $tour->check_in_time && $tour->check_out_time) {
                        try {
                            $start = Carbon::parse($tour->check_in_time);
                            $end = Carbon::parse($tour->check_out_time);
                            while ($start->lt($end)) {
                                $bookingDates[] = $start->copy();
                                $start->addDay();
                            }
                        } catch (\Exception $e2) {
                            $bookingDates[] = Carbon::today();
                        }
                    }
                }
            } else {
                $singleDate = is_array($bookingDate) ? ($bookingDate[0] ?? null) : $bookingDate;
                if ($singleDate) {
                    try {
                        $bookingDates[] = Carbon::parse($singleDate);
                    } catch (\Exception $e) {
                        if ($tour && $tour->check_in_time) {
                            try {
                                $bookingDates[] = Carbon::parse($tour->check_in_time);
                            } catch (\Exception $e2) {
                                $bookingDates[] = Carbon::today();
                            }
                        }
                    }
                }
            }
        } elseif ($tour && $tour->check_in_time && $tour->check_out_time) {
            try {
                $start = Carbon::parse($tour->check_in_time);
                $end = Carbon::parse($tour->check_out_time);
                while ($start->lt($end)) {
                    $bookingDates[] = $start->copy();
                    $start->addDay();
                }
            } catch (\Exception $e) {
                $bookingDates[] = Carbon::today();
            }
        }

        if (empty($bookingDates)) {
            $bookingDates[] = Carbon::today();
        }

        // Calculate average per-night prices
        $totalSinglePrice = 0;
        $totalDoublePrice = 0;
        $totalTriplePrice = 0;
        
        // Get extra bed price for triple sharing
        $extraBedWeekdayPrice = null;
        $extraBedWeekendPrice = null;
        
        $roomId = $room['room_id'] ?? $room['roomId'] ?? null;
        if ($roomId && $hotelId) {
            try {
                $hotel = Hotel::where('hotel_unique_id', $hotelId)->first();
                $dbHotelId = $hotel ? $hotel->hotel_unique_id : $hotelId;
                
                $roomRecord = Room::where('room_id', $roomId)
                    ->where('hotel_id', $dbHotelId)
                    ->where('status', 1)
                    ->first();
                    
                if ($roomRecord && $roomRecord->room_id) {
                    $bedRecord = Bed::where('room_id', $roomRecord->room_id)
                        ->where('extra_bed', true)
                        ->where('is_active', 1)
                        ->first();
                    if ($bedRecord && $bedRecord->extra_bed_price !== null) {
                        $extraBedPrice = floatval($bedRecord->extra_bed_price);
                        $extraBedWeekdayPrice = $extraBedPrice;
                        $extraBedWeekendPrice = $extraBedPrice;
                    }
                }
            } catch (\Exception $e) {
                // Ignore errors
            }
        }

        foreach ($bookingDates as $date) {
            $dayName = $date->format('l');
            $isWeekend = in_array($dayName, $weekendDays);
            $dateString = $date->format('Y-m-d');
            
            // Check rates table
            $ratePrice = null;
            $rateSingleWeekdayPrice = null;
            $rateSingleWeekendPrice = null;
            $rateDoubleWeekdayPrice = null;
            $rateDoubleWeekendPrice = null;
            $rateEventType = null;
            
            if ($hotelId) {
                try {
                    $rate = Rate::where('hotel_id', $hotelId)
                        ->whereDate('start_date', '<=', $dateString)
                        ->whereDate('end_date', '>=', $dateString)
                        ->orderByRaw("
                            CASE
                                WHEN event_type = 'Blackout Date' THEN 1
                                WHEN event_type = 'Season' THEN 2
                                WHEN event_type = 'Fair Date' THEN 3
                                ELSE 4
                            END
                        ")
                        ->first();
                    
                    if ($rate) {
                        $rateEventType = $rate->event_type;
                        
                        if ($rate->event_type == 'Blackout Date') {
                            $ratePrice = floatval($rate->price ?? 0);
                            $rateSingleWeekdayPrice = $ratePrice;
                            $rateSingleWeekendPrice = $ratePrice;
                            $rateDoubleWeekdayPrice = $ratePrice;
                            $rateDoubleWeekendPrice = $ratePrice;
                        } elseif ($rate->event_type == 'Season') {
                            $rateSingleWeekdayPrice = $rate->weekday_price ? floatval($rate->weekday_price) : null;
                            $rateSingleWeekendPrice = $rate->weekend_price ? floatval($rate->weekend_price) : null;
                            $rateDoubleWeekdayPrice = (isset($rate->double_weekday_price) && $rate->double_weekday_price !== null && $rate->double_weekday_price !== '') 
                                ? floatval($rate->double_weekday_price) / 2 
                                : null;
                            $rateDoubleWeekendPrice = (isset($rate->double_weekend_price) && $rate->double_weekend_price !== null && $rate->double_weekend_price !== '') 
                                ? floatval($rate->double_weekend_price) / 2 
                                : null;
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore errors
                }
            }
            
            // Determine price to use
            $singlePriceToAdd = null;
            $doublePriceToAdd = null;
            
            if ($rateEventType == 'Blackout Date' && $ratePrice !== null) {
                $singlePriceToAdd = $ratePrice;
                $doublePriceToAdd = $ratePrice;
            } elseif ($rateEventType == 'Season') {
                if ($isWeekend) {
                    $singlePriceToAdd = $rateSingleWeekendPrice ?? $rateSingleWeekdayPrice ?? $singleWeekendPrice ?? $singleWeekdayPrice;
                    $doublePriceToAdd = $rateDoubleWeekendPrice ?? $rateDoubleWeekdayPrice ?? $doubleWeekendPrice ?? $doubleWeekdayPrice;
                } else {
                    $singlePriceToAdd = $rateSingleWeekdayPrice ?? $rateSingleWeekendPrice ?? $singleWeekdayPrice ?? $singleWeekendPrice;
                    $doublePriceToAdd = $rateDoubleWeekdayPrice ?? $rateDoubleWeekendPrice ?? $doubleWeekdayPrice ?? $doubleWeekendPrice;
                }
            } elseif ($isWeekend) {
                $singlePriceToAdd = $singleWeekendPrice ?? $singleWeekdayPrice;
                $doublePriceToAdd = $doubleWeekendPrice ?? $doubleWeekdayPrice;
            } else {
                $singlePriceToAdd = $singleWeekdayPrice ?? $singleWeekendPrice;
                $doublePriceToAdd = $doubleWeekdayPrice ?? $doubleWeekendPrice;
            }
            
            if ($singlePriceToAdd !== null) {
                $totalSinglePrice += $singlePriceToAdd;
            }
            if ($doublePriceToAdd !== null) {
                $totalDoublePrice += $doublePriceToAdd;
            }
            
            // Triple = double + extra bed
            if ($doublePriceToAdd !== null && $extraBedWeekdayPrice !== null) {
                $extraBedPriceToAdd = $isWeekend 
                    ? ($extraBedWeekendPrice ?? $extraBedWeekdayPrice) 
                    : ($extraBedWeekdayPrice ?? $extraBedWeekendPrice);
                $totalTriplePrice += $doublePriceToAdd + $extraBedPriceToAdd;
            }
        }
        
        $totalNights = count($bookingDates);
        
        return [
            'single_total' => $totalSinglePrice,
            'double_total' => $totalDoublePrice,
            'triple_total' => $totalTriplePrice,
            'single_per_night' => $totalNights > 0 ? ($totalSinglePrice / $totalNights) : 0,
            'double_per_night' => $totalNights > 0 ? ($totalDoublePrice / $totalNights) : 0,
            'triple_per_night' => $totalNights > 0 ? ($totalTriplePrice / $totalNights) : 0,
            'total_nights' => $totalNights,
        ];
    }

    /**
     * Append a status change record into a tour's track_details JSON column.
     *
     * Structure example:
     * [
     *   { "from": null, "to": "New Enquiry", "date": "2026-02-17 05:29:52", "amount": null, "comment": null, "actual_amount": null, ... },
     *   { "from": "New Enquiry", "to": "Prospect", "date": "2026-02-17 05:33:10", "amount": 500, "comment": "...", "actual_amount": 600, ... }
     * ]
     *
     * No other checks - always appends.
     *
     * @param \App\Models\Tour      $tour
     * @param string|null           $fromStatus  null or empty = initial entry (from null to toStatus)
     * @param string                $toStatus
     * @param \Carbon\Carbon|string|null $changedAt
     * @param float|int|string|null $amount
     * @param string|null           $comment
     * @param float|int|string|null $actualAmount
     * @param string|null           $changedByName
     * @param int|string|null       $changedByUserId
     * @param string|null           $action        e.g. "updated", "Added" (for service booking actions)
     * @param string|null           $serviceType   hotel|attraction|restaurant|guide|vehicle
     * @param string|int|null       $serviceId
     * @param string|null           $serviceName
     * @param float|int|string|null $sgdAmount
     * @param string|null           $selectedCurrency
     * @param \Carbon\Carbon|string|null $paymentDate
     * @param string|null           $paymentType
     * @return void
     */
    public static function appendTourStatusTrack(\App\Models\Tour $tour, ?string $fromStatus, string $toStatus, $changedAt = null, $amount = null, $comment = null, $actualAmount = null, ?string $changedByName = null, $changedByUserId = null, ?string $action = null, ?string $serviceType = null, $serviceId = null, ?string $serviceName = null, $sgdAmount = null, ?string $selectedCurrency = null, $paymentDate = null, ?string $paymentType = null): void
    {
        try {
            $changedAt = $changedAt ?? now();
            $changedAtString = $changedAt instanceof \Carbon\Carbon
                ? $changedAt->format('Y-m-d H:i:s')
                : (string) $changedAt;

            $history = [];
            $rawTrack = $tour->track_details ?? null;
            if (!empty($rawTrack)) {
                $decoded = is_array($rawTrack)
                    ? $rawTrack
                    : json_decode($rawTrack, true);
                if (is_array($decoded)) {
                    $history = $decoded;
                }
            }

            $currentCreatedAtString = $tour->created_at
                ? $tour->created_at->format('Y-m-d H:i:s')
                : $changedAtString;

            $fromIsNull = $fromStatus === null || $fromStatus === '';

            $entryExtra = [];
            if ($action !== null && $action !== '') {
                $entryExtra['action'] = (string) $action;
            }
            if ($serviceType !== null && $serviceType !== '') {
                $entryExtra['service_type'] = (string) $serviceType;
            }
            if ($serviceId !== null && $serviceId !== '') {
                $entryExtra['service_id'] = is_numeric($serviceId) ? (string) $serviceId : $serviceId;
            }
            if ($serviceName !== null && $serviceName !== '') {
                $entryExtra['service_name'] = (string) $serviceName;
            }
            if ($sgdAmount !== null && $sgdAmount !== '') {
                $entryExtra['sgd_amount'] = is_numeric($sgdAmount) ? (float) $sgdAmount : $sgdAmount;
            }
            if ($selectedCurrency !== null && $selectedCurrency !== '') {
                $entryExtra['selected_currency'] = (string) $selectedCurrency;
            }
            if ($paymentDate !== null && $paymentDate !== '') {
                $entryExtra['payment_date'] = $paymentDate instanceof \Carbon\Carbon
                    ? $paymentDate->format('Y-m-d H:i:s')
                    : (string) $paymentDate;
            }
            if ($paymentType !== null && $paymentType !== '') {
                $entryExtra['payment_type'] = (string) $paymentType;
            }

            if ($fromIsNull) {
                // Initial entry only: from null to toStatus (e.g. tour created -> New Enquiry)
                $history[] = array_merge([
                    'from' => null,
                    'to' => $toStatus,
                    'date' => $currentCreatedAtString,
                    'amount' => $amount !== null ? (is_numeric($amount) ? (float) $amount : $amount) : null,
                    'comment' => $comment !== null && $comment !== '' ? (string) $comment : null,
                    'actual_amount' => $actualAmount !== null ? (is_numeric($actualAmount) ? (float) $actualAmount : $actualAmount) : null,
                    'changed_by_name' => $changedByName,
                    'changed_by_user_id' => $changedByUserId !== null ? (string) $changedByUserId : null,
                ], $entryExtra);
            } else {
                if (empty($history)) {
                    $history[] = [
                        'from' => null,
                        'to' => $fromStatus,
                        'date' => $currentCreatedAtString,
                        'amount' => null,
                        'comment' => null,
                        'actual_amount' => null,
                        'changed_by_name' => null,
                        'changed_by_user_id' => null,
                    ];
                }

                $history[] = array_merge([
                    'from' => $fromStatus,
                    'to' => $toStatus,
                    'date' => $changedAtString,
                    'amount' => $amount !== null ? (is_numeric($amount) ? (float) $amount : $amount) : null,
                    'comment' => $comment !== null && $comment !== '' ? (string) $comment : null,
                    'actual_amount' => $actualAmount !== null ? (is_numeric($actualAmount) ? (float) $actualAmount : $actualAmount) : null,
                    'changed_by_name' => $changedByName,
                    'changed_by_user_id' => $changedByUserId !== null ? (string) $changedByUserId : null,
                ], $entryExtra);
            }

            $tour->update(['track_details' => json_encode($history)]);
        } catch (\Throwable $e) {
            \Log::error('Failed to append tour status track', [
                'tour_id' => $tour->tour_id ?? null,
                'from' => $fromStatus,
                'to' => $toStatus,
                'amount' => $amount,
                'comment' => $comment,
                'actual_amount' => $actualAmount,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Convenience wrapper: load tour by ID and append status track.
     *
     * @param int                   $tourId
     * @param string|null           $fromStatus  null or empty = initial entry (from null to toStatus)
     * @param string                $toStatus
     * @param \Carbon\Carbon|string|null $changedAt
     * @param float|int|string|null $amount
     * @param string|null           $comment
     * @param float|int|string|null $actualAmount
     * @param string|null           $changedByName
     * @param int|string|null       $changedByUserId
     * @param string|null           $action        e.g. "updated", "Added"
     * @param string|null           $serviceType   hotel|attraction|restaurant|guide|vehicle
     * @param string|int|null       $serviceId
     * @param string|null           $serviceName
     * @param float|int|string|null $sgdAmount
     * @param string|null           $selectedCurrency
     * @param \Carbon\Carbon|string|null $paymentDate
     * @param string|null           $paymentType
     * @return void
     */
    public static function appendTourStatusTrackById(int $tourId, ?string $fromStatus, string $toStatus, $changedAt = null, $amount = null, $comment = null, $actualAmount = null, ?string $changedByName = null, $changedByUserId = null, ?string $action = null, ?string $serviceType = null, $serviceId = null, ?string $serviceName = null, $sgdAmount = null, ?string $selectedCurrency = null, $paymentDate = null, ?string $paymentType = null): void
    {
        $tour = \App\Models\Tour::where('tour_id', $tourId)->first();

        if (!$tour) {
            \Log::warning('appendTourStatusTrackById: tour not found', [
                'tour_id' => $tourId,
                'from'    => $fromStatus,
                'to'      => $toStatus,
            ]);
            return;
        }

        self::appendTourStatusTrack($tour, $fromStatus, $toStatus, $changedAt, $amount, $comment, $actualAmount, $changedByName, $changedByUserId, $action, $serviceType, $serviceId, $serviceName, $sgdAmount, $selectedCurrency, $paymentDate, $paymentType);
    }

    /**
     * Merge refund flag into order update payloads when tour_status is Definite or Actual (same as booking reject flow).
     *
     * @param  array<string, mixed>  $updateData
     * @return array<string, mixed>
     */
    public static function withDefiniteOrActualTourIsRefundFlag(int $tourId, array $updateData): array
    {
        $status = Tour::where('tour_id', $tourId)->value('tour_status');
        if (in_array($status ?? '', ['Definite', 'Actual'], true)) {
            $updateData['is_refund'] = 1;
        }

        return $updateData;
    }

    /**
     * When a service is removed (soft deleted) from a tour, revert tour_status to "New Enquiry"
     * only if the tour went through negotiation (has records in enquiry_comments).
     * If the tour was directly confirmed without any enquiry_comments, do not change tour_status.
     *
     * Call this after removing/rejecting a service from a tour.
     *
     * @param int $tourId
     * @return void
     */
    public static function maybeRevertTourStatusToNewEnquiry(int $tourId): void
    {
        try {
            $tour = Tour::where('tour_id', $tourId)->first();
            if (!$tour) {
                return;
            }

            $currentStatus = $tour->tour_status ?? '';
            // Definite / Actual: service reject/remove must NOT revert to New Enquiry, clear payment_details,
            // soft-delete enquiry_comments, or force orders back to enquiry bookingType.
            if (in_array($currentStatus, ['Definite', 'Actual'], true)) {
                return;
            }

            $statusesToRevert = ['Prospect', 'Tentative', 'Confirmed'];

            // Do nothing if already "New Enquiry"
            if ($currentStatus === 'New Enquiry') {
                return;
            }

            // Do nothing if status is not in the list that should trigger revert
            if (! in_array($currentStatus, $statusesToRevert, true)) {
                return;
            }

            // Check if tour_id exists in enquiry_comments (negotiation history)
            $hasEnquiryComments = DB::table('enquiry_comments')
                ->where('tour_id', $tourId)
                ->whereNull('deleted_at')
                ->exists();

            // Only revert if tour went through negotiation
            if ($hasEnquiryComments) {
                $previousStatus = $tour->tour_status;
                $tour->tour_status = 'New Enquiry';
                // Clear payment_details when reverting - tour will need re-confirmation
                $tour->payment_details = null;
                $tour->save();

                // Update all existing orders for this tour to bookingType = enquiry
                Order::where('tour_id', $tourId)->update(['bookingType' => 'enquiry']);

                // Soft delete negotiation records for this tour in enquiry_comments
                DB::table('enquiry_comments')
                    ->where('tour_id', $tourId)
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => Carbon::now()]);

                $currentUser = Auth::user();
                $changedByName = $currentUser ? ($currentUser->name ?? null) : null;
                $changedByUserId = $currentUser ? ($currentUser->userId ?? $currentUser->id ?? null) : null;

                self::appendTourStatusTrack($tour, $previousStatus, 'New Enquiry', null, null, 'Service removed - reverted to New Enquiry', null, $changedByName, $changedByUserId);

                Log::info('Tour status reverted to New Enquiry after service removal', [
                    'tour_id' => $tourId,
                    'previous_status' => $previousStatus,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('maybeRevertTourStatusToNewEnquiry failed', [
                'tour_id' => $tourId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * When adding a new service to an enquiry: if tour_status is prospect, tentative, confirmed, or actual
     * AND tour_id exists in enquiry_comments (negotiation history), revert tour_status to "New Enquiry".
     * Call this when creating new orders (adding services) during enquiry update.
     *
     * @param int $tourId
     * @return void
     */
    public static function maybeRevertTourStatusWhenAddingService(int $tourId): void
    {
        try {
            $tour = Tour::where('tour_id', $tourId)->first();
            if (!$tour) {
                return;
            }

            $currentStatus = $tour->tour_status ?? '';
            $statusesToRevert = ['Prospect', 'Tentative', 'Confirmed', 'Actual'];

            // Do nothing if already "New Enquiry"
            if ($currentStatus === 'New Enquiry') {
                return;
            }

            // Do nothing if status is not in the list that should trigger revert
            if (!in_array($currentStatus, $statusesToRevert, true)) {
                return;
            }

            // Check if tour_id exists in enquiry_comments (negotiation history)
            $hasEnquiryComments = DB::table('enquiry_comments')
                ->where('tour_id', $tourId)
                ->whereNull('deleted_at')
                ->exists();

            // Only revert if tour went through negotiation
            if ($hasEnquiryComments) {
                $previousStatus = $tour->tour_status;
                $tour->tour_status = 'New Enquiry';
                $tour->payment_details = null;
                $tour->save();

                Order::where('tour_id', $tourId)->update(['bookingType' => 'enquiry']);

                DB::table('enquiry_comments')
                    ->where('tour_id', $tourId)
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => Carbon::now()]);

                $currentUser = Auth::user();
                $changedByName = $currentUser ? ($currentUser->name ?? null) : null;
                $changedByUserId = $currentUser ? ($currentUser->userId ?? $currentUser->id ?? null) : null;

                self::appendTourStatusTrack($tour, $previousStatus, 'New Enquiry', null, null, 'New service added - reverted to New Enquiry', null, $changedByName, $changedByUserId);

                Log::info('Tour status reverted to New Enquiry after adding new service', [
                    'tour_id' => $tourId,
                    'previous_status' => $previousStatus,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('maybeRevertTourStatusWhenAddingService failed', [
                'tour_id' => $tourId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * ISO codes available for invoice currency selection / conversion.
     *
     * @return array<int, string>
     */
    public static function getInvoiceAvailableCurrencies(): array
    {
        return [
            'SGD', 'USD', 'EUR', 'GBP', 'INR', 'AUD', 'NZD', 'CAD', 'CHF', 'JPY', 'CNY',
            'HKD', 'TWD', 'KRW', 'THB', 'MYR', 'IDR', 'PHP', 'VND', 'AED', 'SAR', 'QAR',
            'KWD', 'BHD', 'OMR', 'ZAR', 'NGN', 'EGP', 'KES', 'GHS', 'MAD', 'BRL', 'ARS',
            'CLP', 'COP', 'PEN', 'MXN', 'RUB', 'UAH', 'TRY', 'ILS', 'PLN', 'CZK', 'HUF',
            'RON', 'SEK', 'NOK', 'DKK', 'ISK', 'BGN', 'HRK', 'PKR', 'LKR', 'BDT', 'MVR',
            'KZT', 'DOP', 'JMD',
        ];
    }

    /**
     * Currencies for payment modal dropdowns: invoice list + any codes from countries table.
     *
     * @return array<int, string>
     */
    public static function getPaymentAvailableCurrencies(): array
    {
        $codes = self::getInvoiceAvailableCurrencies();

        $countryCurrencies = Country::query()
            ->whereNotNull('currency')
            ->where('currency', '!=', '')
            ->distinct()
            ->pluck('currency');

        foreach ($countryCurrencies as $raw) {
            $raw = trim((string) $raw);
            if ($raw === '') {
                continue;
            }

            $upper = strtoupper($raw);
            if (strlen($upper) === 3 && ctype_alpha($upper)) {
                $codes[] = $upper;
                continue;
            }

            $normalized = CurrencyHelper::normalizeCurrencyToCode($raw, $codes, '');
            if ($normalized !== '' && strlen($normalized) === 3) {
                $codes[] = $normalized;
            }
        }

        $codes = array_values(array_unique(array_map(
            static fn ($code) => strtoupper(trim((string) $code)),
            $codes
        )));
        sort($codes);

        return $codes;
    }

    /**
     * Resolve DMC / tour currency for invoices (e.g. VND for Vietnam DMC).
     */
    public static function resolveDmcCurrencyForInvoice(?User $dmc, ?Tour $tour = null): string
    {
        $available = self::getInvoiceAvailableCurrencies();

        if ($dmc && trim((string) ($dmc->currency ?? '')) !== '') {
            return CurrencyHelper::normalizeCurrencyToCode($dmc->currency, $available, 'SGD');
        }

        if ($tour && trim((string) ($tour->currency ?? '')) !== '') {
            return CurrencyHelper::normalizeCurrencyToCode($tour->currency, $available, 'SGD');
        }

        if ($dmc && trim((string) ($dmc->country ?? '')) !== '') {
            $country = Country::where('name', $dmc->country)->first();
            if ($country && trim((string) ($country->currency ?? '')) !== '') {
                return CurrencyHelper::normalizeCurrencyToCode($country->currency, $available, 'SGD');
            }
        }

        if ($tour) {
            return self::resolveTourEmailCurrency($tour, $dmc);
        }

        return 'SGD';
    }

    /**
     * Tour display/base currency for quotation & itinerary PDFs (e.g. VND for Vietnam DMC).
     * Prefers the tour's DMC currency, then tour currency, then DMC country currency.
     */
    public static function resolveTourDisplayCurrency(Tour $tour): string
    {
        $dmc = null;
        if (!empty($tour->dmc_id)) {
            $dmc = User::where('userId', $tour->dmc_id)->first();
        }

        return self::resolveDmcCurrencyForInvoice($dmc, $tour);
    }

    /**
     * Invoice display base currency: prefer DMC country currency over legacy SGD default.
     */
    public static function resolveInvoiceBaseCurrency(Invoice $invoice): string
    {
        $invoice->loadMissing(['tour', 'dmc']);

        $dmc = $invoice->dmc;
        if (!$dmc && $invoice->dmc_id) {
            $dmc = User::where('userId', $invoice->dmc_id)->first();
        }

        $fromDmc = self::resolveDmcCurrencyForInvoice($dmc, $invoice->tour);
        $stored = strtoupper(trim((string) ($invoice->base_currency ?? '')));
        $available = self::getInvoiceAvailableCurrencies();

        if ($stored !== '' && $stored !== 'SGD') {
            return CurrencyHelper::normalizeCurrencyToCode($stored, $available, $fromDmc);
        }

        return $fromDmc;
    }

    /**
     * Selected invoice display currency (query param or invoice DMC base).
     */
    public static function getInvoiceSelectedCurrency($requested, Invoice $invoice): string
    {
        $available = self::getInvoiceAvailableCurrencies();
        $default = self::resolveInvoiceBaseCurrency($invoice);
        $selected = strtoupper(trim((string) ($requested ?? $default)));

        if ($selected === '') {
            $selected = $default;
        }

        return in_array($selected, $available, true) ? $selected : $default;
    }

    /**
     * Build currency conversion map keyed by invoice base currency.
     *
     * @return array<string, float>
     */
    public static function buildInvoiceCurrencyConversion(Invoice $invoice, string $selectedCurrency): array
    {
        $baseCurrency = self::resolveInvoiceBaseCurrency($invoice);
        $selectedCurrency = strtoupper($selectedCurrency);

        $tour = $invoice->tour;
        $tourStatus = $tour->tour_status ?? '';
        $statusesWithTax = ['Confirmed', 'Definite', 'Actual'];
        $shouldShowTax = in_array($tourStatus, $statusesWithTax, true);

        $notes = is_string($invoice->notes) ? json_decode($invoice->notes, true) : ($invoice->notes ?? []);
        $baseAmount = $notes['base_amount'] ?? ($invoice->getNegotiatedAmount() ?? ($invoice->total_amount ?? 0));
        $gstAmount = $invoice->gst_amount ?? 0;
        $finalPrice = $baseAmount + $gstAmount;
        $outstandingBalance = $invoice->outstanding_balance ?? 0;

        $amountInBase = $shouldShowTax ? (float) $outstandingBalance : (float) $finalPrice;
        $conversion = [$baseCurrency => $amountInBase];

        if ($selectedCurrency !== $baseCurrency) {
            $converted = CurrencyHelper::convertAmount($amountInBase, $baseCurrency, $selectedCurrency);
            if ($converted !== null) {
                $conversion[$selectedCurrency] = $converted;
            }
        }

        return $conversion;
    }

    /**
     * Exchange rate from invoice base currency to selected currency.
     */
    public static function getInvoiceExchangeRate(string $baseCurrency, string $selectedCurrency, array $currencyConversion): float
    {
        $baseCurrency = strtoupper($baseCurrency);
        $selectedCurrency = strtoupper($selectedCurrency);

        if ($selectedCurrency === $baseCurrency) {
            return 1.0;
        }

        $baseAmount = $currencyConversion[$baseCurrency] ?? 0;
        $convertedAmount = $currencyConversion[$selectedCurrency] ?? null;

        if ($baseAmount > 0 && $convertedAmount !== null && $convertedAmount > 0) {
            return (float) $convertedAmount / (float) $baseAmount;
        }

        $rate = CurrencyHelper::getExchangeRate($baseCurrency, $selectedCurrency);

        if ($rate !== null && $rate > 0) {
            return (float) $rate;
        }

        $converted = CurrencyHelper::convertAmount(1, $baseCurrency, $selectedCurrency);

        return ($converted !== null && $converted > 0) ? (float) $converted : 0.0;
    }

    public static function shouldShowInvoiceCurrencyConversion(string $baseCurrency, string $selectedCurrency, array $currencyConversion): bool
    {
        if (strtoupper($selectedCurrency) === strtoupper($baseCurrency)) {
            return false;
        }

        $convertedAmount = $currencyConversion[strtoupper($selectedCurrency)] ?? null;

        return $convertedAmount !== null && (float) $convertedAmount > 0;
    }

    /**
     * Dual-currency price string for invoice PDFs (base + converted when selected differs).
     */
    public static function formatInvoiceDualPrice($amount, string $baseCurrency, string $selectedCurrency, float $exchangeRate): string
    {
        if (!is_numeric($amount)) {
            return '0.00';
        }

        $amt = (float) $amount;
        $baseCurrency = strtoupper($baseCurrency);
        $selectedCurrency = strtoupper($selectedCurrency);

        if ($selectedCurrency === $baseCurrency) {
            return number_format(round($amt, 2), 2);
        }

        $converted = CurrencyHelper::convertAmount($amt, $baseCurrency, $selectedCurrency);
        if ($converted === null && $exchangeRate > 0 && $exchangeRate !== 1.0) {
            $converted = $amt * $exchangeRate;
        }

        if ($converted === null) {
            return self::formatMoneyAdaptive($amt) . ' ' . $baseCurrency;
        }

        return self::formatMoneyAdaptive($amt) . ' ' . $baseCurrency
            . ' (' . self::formatMoneyAdaptive($converted) . ' ' . $selectedCurrency . ')';
    }

    /**
     * Format a monetary value with adaptive precision so small converted
     * amounts (e.g. 60 IDR = 0.0048 AUD) do not collapse to "0.00".
     */
    public static function formatMoneyAdaptive($value): string
    {
        if (!is_numeric($value)) {
            return '0.00';
        }

        $value = (float) $value;
        $abs = abs($value);

        if ($abs == 0.0) {
            return '0.00';
        }

        if ($abs >= 0.01) {
            $decimals = 2;
        } elseif ($abs >= 0.0001) {
            $decimals = 4;
        } else {
            $decimals = 6;
        }

        return number_format(round($value, $decimals), $decimals);
    }

    // Get DMC Dynamic Currency
    public static function getDmcCurrencyByCountry()
    {
        $user = Auth::user();

        if (!$user) {
            return 'SGD';
        }

        $dmc_id = null;

        switch ($user->role_id) {

            case 1: // Admin
            case 20: 
                return 'SGD'; 

            case 11: // DMC
                $dmc_id = $user->userId;
                break;

            case 33:
            case 34:
            case 35:
            case 36:
            case 128:
            case 129:
            case 130:
            case 131:
            case 132:
            case 133:
            case 137:
            case 134:
            case 135:
            case 136:
            case 138:
                $dmc_id = $user->created_by;
                break;

            case 37:    // Sales Manager
            case 12:
            case 38:
            case 64:
            case 65:
            case 66:
            case 67:
            case 68:
            case 69:
            case 70:
            case 71:
            case 72:
            case 73:
            case 74:
            case 75:
            case 76:
            case 77:
            case 78:
                $sales_head = User::where('userId', $user->created_by)->first();
                $dmc_id = $sales_head?->created_by;
                break;

            case 38:    // Assistant Sales Manager
            case 40:
            case 81:
            case 84:
            case 87:
            case 90:
            case 93:
            case 96:
            case 99:
            case 102:
            case 105:
            case 108:
            case 111:
            case 114:
            case 117:
            case 120:
            
                $sales_manager = User::where('userId', $user->created_by)->first();
                $sales_head = User::where('userId', $sales_manager?->created_by)->first();
                $dmc_id = $sales_head?->created_by;
                break;

            default:
                return 'SGD';
        }

        if (!$dmc_id) {
            return 'SGD';
        }

        // Get DMC
        $dmc = User::where('userId', $dmc_id)->first();

        if (!$dmc || !$dmc->country) {
            return 'SGD';
        }

        // Get currency from countries table using country name
        $country = Country::where('name', $dmc->country)->first();
        

        return $country->currency ?? 'SGD';
    }

    /**
     * Negotiated package price excluding tax: latest DMC (OM) counter-offer amount, else computed package total.
     *
     * @param float $approxActual From calculatePackageBookingActualAmount()
     * @param \Illuminate\Support\Collection|array<int, mixed> $bookingComments PackageInquiryComment models for the booking
     */
    public static function packageNegotiatedPriceExclTax(float $approxActual, $bookingComments): float
    {
        $comments = collect($bookingComments)->sortByDesc('created_at')->values();
        $latestOm = $comments->first(function ($c) {
            return strtolower((string) (data_get($c, 'sender_type') ?? '')) === 'om';
        });
        $om = (float) (data_get($latestOm, 'amount') ?? 0);

        return $om > 0 ? $om : $approxActual;
    }

    /**
     * Tax breakdown for package_bookings.taxes JSON (same rules as package/booking-list).
     *
     * @param float $baseAmount Excl. tax (negotiated package amount)
     * @param mixed $taxesJson package_bookings.taxes
     * @param array<string, mixed> $bookingDetails decoded booking_details
     * @return array{breakdown: array<string, float>, total_tax: float, persons: int, days: int}
     */
    public static function calculatePackageBookingTaxBreakdown(float $baseAmount, $taxesJson, array $bookingDetails = []): array
    {
        $persons = (int) (data_get($bookingDetails, 'adult_count') ?? data_get($bookingDetails, 'adult') ?? 0)
            + (int) (data_get($bookingDetails, 'child_count') ?? data_get($bookingDetails, 'child') ?? 0);
        $itinerary = data_get($bookingDetails, 'itinerary');
        $days = (! empty($itinerary) && is_array($itinerary)) ? count($itinerary) : 1;
        $taxesArr = self::normalizeJsonArray($taxesJson);
        $computedById = [];
        $breakdown = [];
        $totalTax = 0.0;
        foreach ($taxesArr as $tax) {
            if (! is_array($tax)) {
                continue;
            }
            $taxId = $tax['tax_id'] ?? null;
            $taxName = $tax['tax_name'] ?? 'Tax';
            $taxType = strtolower((string) ($tax['tax_type'] ?? 'percentage'));
            $taxValue = (float) ($tax['tax_value'] ?? 0);
            $calculateOn = $tax['calculate_on'] ?? 'total';
            $ifFixed = $tax['if_fixed'] ?? null;
            $baseForThis = $baseAmount;
            if (is_numeric($calculateOn)) {
                $refId = (int) $calculateOn;
                $refAmount = $computedById[$refId] ?? 0;
                $baseForThis = $baseAmount + $refAmount;
            } elseif (strtolower((string) $calculateOn) === 'total') {
                $baseForThis = $baseAmount;
            }
            $amount = 0.0;
            if ($taxType === 'percentage') {
                $amount = ($baseForThis * $taxValue) / 100;
            } else {
                if ($ifFixed === 'person' || $ifFixed === 'per_person') {
                    $amount = $taxValue * max(0, $persons);
                } elseif ($ifFixed === 'per_tour_per_day') {
                    $amount = $taxValue * max(1, $days);
                } elseif ($ifFixed === 'per_person_per_day') {
                    $amount = $taxValue * max(0, $persons) * max(1, $days);
                } else {
                    $amount = $taxValue;
                }
            }
            $amount = ceil($amount);
            $breakdown[$taxName] = ($breakdown[$taxName] ?? 0) + $amount;
            if ($taxId !== null) {
                $computedById[$taxId] = ($computedById[$taxId] ?? 0) + $amount;
            }
            $totalTax += $amount;
        }

        return [
            'breakdown' => $breakdown,
            'total_tax' => $totalTax,
            'persons' => $persons,
            'days' => $days,
        ];
    }

    /**
     * Gross tour amount (sum of booked service sell prices) used by the negotiation modals.
     * Mirrors the inline calculation in the new-enquiries / follow-ups blades so the value stored
     * at negotiation time matches what those lists display. Hotels use pickup/item total only
     * (transport is already included); other services add transfer + guide prices.
     *
     * @param  \App\Models\Tour|int  $tour
     */
    public static function calculateTourGrossAmount($tour): float
    {
        if (is_numeric($tour)) {
            $tour = Tour::where('tour_id', (int) $tour)->first();
        }
        if (!$tour) {
            return 0.0;
        }

        $isPro = (int) ($tour->is_pro ?? 0);
        $bookings = Order::where('tour_id', $tour->tour_id)->get();

        $total = 0.0;
        foreach ($bookings as $booking) {
            if (!in_array((int) $booking->status, [1, 3], true)) {
                continue;
            }
            $data = is_string($booking->data) ? json_decode($booking->data, true) : $booking->data;
            if (!is_array($data)) {
                continue;
            }
            $orderType = $booking->type ?? '';
            foreach ($data as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $itemPrice = (float) ($item['totalPrice'] ?? $item['price'] ?? 0);

                // Hotel: pickup total only - do NOT add transfer (transport added automatically).
                $transferPrice = 0.0;
                if ($orderType !== 'hotel' && isset($item['transfer_options']['cost']) && $item['transfer_options']['cost'] > 0) {
                    if ($isPro === 1 && isset($item['transfer_options']['totalPrice'])) {
                        $transferPrice = (float) $item['transfer_options']['totalPrice'];
                    } else {
                        $transferPrice = (float) $item['transfer_options']['cost'];
                    }
                }

                $guidePrice = 0.0;
                if (isset($item['guide_options']) && is_array($item['guide_options'])) {
                    $gv = $item['guide_options']['total_price']
                        ?? $item['guide_options']['cost']
                        ?? $item['guide_options']['Cost']
                        ?? $item['guide_options']['sell']
                        ?? $item['guide_options']['Sell']
                        ?? 0;
                    if ($gv > 0) {
                        $guidePrice = (float) $gv;
                    }
                }

                $total += $itemPrice + $transferPrice + $guidePrice;
            }
        }

        return (float) ceil($total);
    }
}
