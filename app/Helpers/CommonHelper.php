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
use App\Models\Agent;
use App\Models\Attraction;
use App\Models\Restaurant;
use App\Models\OperationalCountry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\DmcMail;

use Illuminate\Support\Facades\Log;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use League\Flysystem\Filesystem;
use League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter;

class CommonHelper
{
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
                        if($booking->type == 'attraction_package' || $booking->type == 'attraction'){
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
                    if($booking->type == 'attraction_package' || $booking->type == 'attraction'){
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

    public static function getDmcId($auth_user){
        if($auth_user->agent_id){
            $agent = Agent::where('agent_id', $auth_user->agent_id)->first();
            $sales_manager_dmc = $agent->sales_manager_dmc;
            $dmcId = null;
            
            if($agent->role_id == 11){
                $dmcId = $sales_manager_dmc;
            }
            elseif($agent->role_id == 33 || $agent->role_id == 128 || $agent->role_id == 129 || $agent->role_id == 130 || $agent->role_id == 134 || $agent->role_id == 135 || $agent->role_id == 136 || $agent->role_id == 138){
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
        }
        elseif($auth_user->userId){
            $user = User::where('userId', $auth_user->userId)->first();
            if($user->role_id == 11){
                return $user->created_by;
            }
            elseif($user->role_id == 33 || $user->role_id == 128 || $user->role_id == 129 || $user->role_id == 130 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 138){
                $sales_head = User::where('userId', $user->userId)->first();
                return $sales_head->created_by;
            }
            elseif($user->role_id == 37){
                $sales_manager = User::where('userId', $user->userId)->first();
                $sales_head = User::where('userId', $sales_manager->created_by)->first();
                return $sales_head->created_by;
            }
            elseif($user->role_id == 38){
                $assistant_sales_manager = User::where('userId', $user->userId)->first();
                $sales_manager = User::where('userId', $assistant_sales_manager->created_by)->first();
                $sales_head = User::where('userId', $sales_manager->created_by)->first();
                return $sales_head->created_by;
            }
        }
        return null;
    }
}