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
use App\Mail\DmcMail;
use Illuminate\Support\Facades\Auth;
use App\Models\Country;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
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
            if($user->role_id == 11){
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
     * Send tour proposal email to agent
     * Date: Current
     * 
     * @param int $agentId - Agent ID
     * @param int $tourId - Tour ID
     * @param string $tourDisplayId - Tour Display ID
     * @param array $tourData - Tour details (destination, dates, guests, etc.)
     * @return bool|string - true on success, error message on failure
     */
    public static function sendTourProposalEmail($agentId, $tourId, $tourDisplayId, $tourData = [])
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

            // Get DMC details
            $dmcId = self::getDmcId(\Illuminate\Support\Facades\Auth::user());
            if (!$dmcId) {
                // Try to get DMC from agent's sales_manager_dmc
                $dmcId = $agent->sales_manager_dmc;
            }
            
            $dmc = User::where('userId', $dmcId)->first();
            $dmcName = $dmc ? ($dmc->company_name ?? $dmc->name ?? 'DMC') : 'DMC';
            $dmcLogo = $dmc ? ($dmc->logo ?? null) : null;
            $dmcEmail = $dmc ? ($dmc->email ?? null) : null;
            $dmcPhone = $dmc ? ($dmc->phone_number ?? null) : null;

            // Prepare email data
            $emailData = [
                'agent_name' => $agent->name ?? 'Valued Partner',
                'agency_name' => $agencyName,
                'dmc_name' => $dmcName,
                'dmc_logo' => $dmcLogo,
                'dmc_email' => $dmcEmail,
                'dmc_phone' => $dmcPhone,
                'tour_display_id' => $tourDisplayId,
                'destination' => $tourData['destination'] ?? 'N/A',
                'city' => $tourData['city'] ?? null,
                'check_in_date' => isset($tourData['check_in_time']) ? Carbon::parse($tourData['check_in_time'])->format('M d, Y') : 'N/A',
                'check_out_date' => isset($tourData['check_out_time']) ? Carbon::parse($tourData['check_out_time'])->format('M d, Y') : 'N/A',
                'adults' => $tourData['adult'] ?? 0,
                'children' => $tourData['child'] ?? 0,
                'infants' => $tourData['infant'] ?? 0,
                'total_guests' => ($tourData['adult'] ?? 0) + ($tourData['child'] ?? 0) + ($tourData['infant'] ?? 0),
                'query_date' => now()->format('M d, Y'),
                'dashboard_link' => self::url(),
            ];

            // Email subject
            $subject = "✈️ New Travel Proposal from {$dmcName} via Travclicks";

            // Render the email template
            try {
                $html = view('mails.tour_proposal_agent', $emailData)->render();
            } catch (\Exception $e) {
                Log::error("Error rendering tour proposal email template", [
                    'error' => $e->getMessage(),
                    'tour_id' => $tourId
                ]);
                return "Error rendering email template: " . $e->getMessage();
            }

            // Send the email
            try {
                Mail::to($agent->email)->send(new DmcMail($html, $subject));
                
                // Log successful email sending
                Log::info("Tour proposal email sent successfully", [
                    'agent_id' => $agentId,
                    'agent_email' => $agent->email,
                    'tour_id' => $tourId,
                    'tour_display_id' => $tourDisplayId
                ]);
                
                return true;
            } catch (\Exception $e) {
                Log::error("Failed to send tour proposal email", [
                    'error' => $e->getMessage(),
                    'agent_id' => $agentId,
                    'agent_email' => $agent->email,
                    'tour_id' => $tourId
                ]);
                return "Failed to send email: " . $e->getMessage();
            }

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
            $subject = "🌍 You've Been Invited to Partner with {$dmcName} on Travclicks";

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

    public static function url() {
        if (!function_exists('root_url')) {
            function root_url($path = '')
            {
                $base = config('app.url');
                $root = preg_replace('#/backadm-dmc/?$#', '', $base);
                return rtrim($root, '/') . '/' . ltrim($path, '/');
            }
        }
        return root_url('login');
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
            $subject = "💰 Price Negotiation Submitted - Tour {$tourDisplayId}";

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

    public static function downloadTourPdf($tourId, $targetCurrency = null, $preview = false)
    {
        $tour = Tour::where('tour_id', $tourId)->first();
        if (!$tour) {
            return null;
        }

        // Currency handling
        // Base currency is what prices are stored in (default SGD as per requirement)
        $baseCurrency = strtoupper($tour->currency ?? 'SGD');
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

        if (!empty($tour->agent_id)) {
            $agent = Agent::with('agency')->where('agent_id', $tour->agent_id)->first();
            if ($agent) {
                $agency = $agent->agency;
                
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
            $pdf = Pdf::loadView('single-tour-package.quotation', [
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
                $pdf = Pdf::loadView('single-tour-package.quotation', [
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
                    'paymentTerms' => $paymentTerms,
                    'baseCurrency' => $baseCurrency,
                    'selectedCurrency' => $selectedCurrency,
                    'exchangeRate' => $exchangeRate,
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

        $totalSingleSharing = 0;
        $totalDoubleSharing = 0;
        $totalTripleSharing = 0;
        $totalBabyCot = 0;
        // Track child-specific pricing component across attraction/restaurant services
        $totalChildComponent = 0; // Sum of child unit prices (attraction + restaurant + …)
        
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

        // Supplements: services with "supplement": true (per-head prices, excluded from main total)
        $supplements = [];

        // Counter for hotels to segregate individually
        $hotelCount = 0;

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
            $manualDoublePrice = null;

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

                    // Initialize individual hotel segregation
                    $currentHotelKey = $hotelName;
                    
                    if (!isset($segregatedPrices[$currentHotelKey])) {
                        $segregatedPrices[$currentHotelKey] = [
                            'single' => 0, 
                            'double' => 0, 
                            'triple' => 0, 
                            'baby_cot' => 0
                        ];
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
                        $supplements[] = [
                            'type'      => 'hotel',
                            'name'      => $currentHotelKey,
                            'single'    => $hotelSingleTotal,
                            'double'    => $hotelDoubleTotal,
                            'triple'    => $hotelTripleTotal,
                            'baby_cot'  => $babyCotPrice,
                        ];
                    } else {
                        $totalSingleSharing += $hotelSingleTotal;
                        $totalDoubleSharing += $hotelDoubleTotal;
                        $totalTripleSharing += $hotelTripleTotal;

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
                        // Prefer explicit adult/child unit prices when present (from attraction/meals tables),
                        // otherwise fall back to totalPrice/pax. For restaurants, also fall back to meals table via meal_id.
                        if ($normalizedType === 'attraction' || $normalizedType === 'restaurant') {
                            $adultCount = floatval($item['adultCount'] ?? 0);
                            $childCount = floatval($item['childCount'] ?? 0);

                            // Resolve unit prices from multiple possible locations in the payload
                            $adultUnitPrice = 0;
                            $childUnitPrice = 0;

                            if (isset($item['adultPrice'])) {
                                $adultUnitPrice = floatval($item['adultPrice']);
                            } elseif (isset($item['adult_price'])) {
                                $adultUnitPrice = floatval($item['adult_price']);
                            } elseif (isset($item['ticket_details']['adult_price'])) {
                                $adultUnitPrice = floatval($item['ticket_details']['adult_price']);
                            }

                            if (isset($item['childPrice'])) {
                                $childUnitPrice = floatval($item['childPrice']);
                            } elseif (isset($item['child_price'])) {
                                $childUnitPrice = floatval($item['child_price']);
                            } elseif (isset($item['ticket_details']['child_price'])) {
                                $childUnitPrice = floatval($item['ticket_details']['child_price']);
                            }

                            // Extra fallback for RESTAURANTS: if we still don't have unit prices,
                            // try to fetch them from meals table using MealDescription[0].meal_id.
                            if ($normalizedType === 'restaurant'
                            && ($adultUnitPrice <= 0 || $childUnitPrice <= 0)
                            && !empty($item['MealDescription'][0]['meal_id'])
                            ) {
                                try {
                                    $mealId       = $item['MealDescription'][0]['meal_id'];
                                    $restaurantId = $item['restaurantId'] ?? null;

                                    $mealQuery = \App\Models\Meal::where('meal_id', $mealId)->first();
                                    // if ($restaurantId) {
                                    //     $mealQuery->where('restaurant_id', $restaurantId);
                                    // }
                                    // if (!empty($tour->dmc_id)) {
                                    //     $mealQuery->where('dmc_id', $tour->dmc_id);
                                    // }

                                    if ($mealQuery) {
                                        if ($adultUnitPrice <= 0 && $mealQuery->adult_price !== null) {
                                            $adultUnitPrice = (float) $mealQuery->adult_price;
                                        }
                                        if ($childUnitPrice <= 0 && $mealQuery->child_price !== null) {
                                            $childUnitPrice = (float) $mealQuery->child_price;
                                        }
                                    }
                                } catch (\Throwable $e) {
                                    \Log::warning('Failed to fetch meal unit prices for restaurant child_sharing', [
                                        'meal_id'       => $item['MealDescription'][0]['meal_id'] ?? null,
                                        'restaurant_id' => $item['restaurantId'] ?? null,
                                        'tour_id'       => $tour->tour_id ?? null,
                                        'error'         => $e->getMessage(),
                                    ]);
                                }
                            }

                            // Adult per pax → main totals + segregated (with hotel and other services).
                            // Child per pax → child_sharing only (never mixed into single/double).
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

                            // Add only adult part to segregated and main totals (unless supplement)
                            if (!$isSupplement) {
                                $serviceKey = $normalizedType === 'attraction' ? 'attraction' : 'restaurant';
                                $segregatedPrices[$serviceKey]['single'] += $singleSharing;
                                $segregatedPrices[$serviceKey]['double'] += $doubleSharing;
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
                                $serviceKey = $normalizedType === 'entry_port' ? 'entry_port' : 'exit_port';
                                $segregatedPrices[$serviceKey]['single'] += $singleSharing;
                                $segregatedPrices[$serviceKey]['double'] += $doubleSharing;
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
                                $serviceKey = $normalizedType;
                                $segregatedPrices[$serviceKey]['single'] += $singleSharing;
                                $segregatedPrices[$serviceKey]['double'] += $doubleSharing;
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
                                $segregatedPrices['guide']['single'] += $singleSharing;
                                $segregatedPrices['guide']['double'] += $doubleSharing;
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
                            
                            // Add to segregated prices based on service type
                            if (!$isSupplement) {
                                $serviceKey = 'other';
                                if (isset($segregatedPrices[$normalizedType])) {
                                    $serviceKey = $normalizedType;
                                } elseif (in_array($normalizedType, ['travel_hourly', 'travel_point', 'local_transport', 'guide'])) {
                                    $serviceKey = $normalizedType;
                                }
                                $segregatedPrices[$serviceKey]['single'] += $singleSharing;
                                $segregatedPrices[$serviceKey]['double'] += $doubleSharing;
                            }
                        }

                        if ($isSupplement) {
                            // Keep supplement row as a standalone payload.
                            // Some downstream code expects extra keys for attraction/restaurant.
                            $supplementRow = [
                                'type'   => $normalizedType ?? $type,
                                'single' => $singleSharing,
                                'double' => $doubleSharing,
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
                            $totalSingleSharing += $singleSharing;
                            $totalDoubleSharing += $doubleSharing;
                        }
                    }
                }
            }
        }
        // Compute effective per-child sharing price (from attraction/restaurant components)
        $childSharing = $totalChildComponent;

        // Round segregated prices and format
        $segregatedPricesRounded = [];
        foreach ($segregatedPrices as $serviceType => $prices) {
            $segregatedPricesRounded[$serviceType] = [
                'single' => ceil($prices['single']),
                'double' => ceil($prices['double']),
            ];
            if (isset($prices['triple'])) {
                $segregatedPricesRounded[$serviceType]['triple'] = ceil($prices['triple']);
            }
            if (isset($prices['baby_cot'])) {
                $segregatedPricesRounded[$serviceType]['baby_cot'] = ceil($prices['baby_cot']);
            }
        }

        // Format supplements: per-head prices (ceil), excluded from main total
        $supplementsFormatted = array_map(function ($s) {
            $row = [
                'type'   => $s['type'],
                'single' => ceil($s['single'] ?? 0),
                'double' => ceil($s['double'] ?? 0),
            ];
            if (isset($s['name'])) {
                $row['name'] = $s['name'];
            }
            if(($s['type'] ?? null) == 'attraction') {
                $row['name'] = $s['AttractionName'] ?? 'AttractionName';
                $row['attraction_id'] = $s['AttractionId'] ?? null;
                $row['ticket'] = $s['ticketName'] ?? null;
                $row['transfer_required'] = $s['transfer_options']['transfer_required'] ?? null;
                $row['guide_required'] = $s['guide_options']['guide_required'] ?? null;

            } elseif(($s['type'] ?? null) == 'restaurant') {
                $row['name'] = $s['restaurantName'] ?? 'restaurantName';
                $row['restaurant_id'] = $s['restaurant_id'] ?? null;
                $row['mealType'] = $s['mealType'] ?? null;
                $firstMeal = $s['MealDescription'][0] ?? [];
                $row['quantity'] = $firstMeal['quantity'] ?? null;
            } elseif($s['type'] == 'entry_port') {
                $row['name'] = 'name';
                $row['entry_port_id'] = $s['entry_port_id'];
            } elseif($s['type'] == 'exit_port') {
                $row['name'] = 'name';
                $row['exit_port_id'] = $s['exit_port_id'];
            }
            if (isset($s['triple'])) {
                $row['triple'] = ceil($s['triple']);
            }
            if (isset($s['baby_cot'])) {
                $row['baby_cot'] = ceil($s['baby_cot']);
            }
            return $row;
        }, $supplements);

        // dd($totalSingleSharing, $totalDoubleSharing, $totalTripleSharing, $totalBabyCot, $childSharing, $segregatedPricesRounded);
        return [
            'single_sharing' => ceil($totalSingleSharing),
            'double_sharing' => ceil($totalDoubleSharing),
            'triple_sharing' => ceil($totalTripleSharing),
            'baby_cot_sharing' => ceil($totalBabyCot),
            // Average per-child price across attraction/restaurant services
            'child_sharing' => ceil($childSharing),
            'segregated' => $segregatedPricesRounded,
            'supplements' => $supplementsFormatted,
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
            if ($transferOptions && !empty($transferOptions['type'])) {
                $transferType = $transferOptions['type'];
            } else {
                // For travel_point, travel_hourly, local_transport, and local_transfer, use the type field directly
                $transferType = $item['type'] ?? null;
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
            
            $attractionDetails = [
                'ticket_name' => $item['ticketName'] ?? $item['ticketName'] ?? null,
                'adult_count' => $adultCount > 0 ? $adultCount : null,
                'child_count' => $childCount > 0 ? $childCount : null,
                'senior_count' => $seniorCount > 0 ? $seniorCount : null,
                'visit_time' => $item['visitTime'] ?? null,
                'transport_note' => $transportNote,
                'transfer_required' => $transferRequired,
                'transfer_type' => $transferType,
            ];
        }

        // Restaurant details (similar structure to attractions)
        $restaurantDetails = null;
        if (strtolower($type) === 'restaurant') {
            $adultCount = $item['adultCount'] ?? $item['adult'] ?? 0;
            $childCount = $item['childCount'] ?? $item['child'] ?? 0;
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
                'senior_count' => $seniorCount > 0 ? $seniorCount : null,
                'visit_time' => $item['visitTime'] ?? null,
                'meal_type' => $mealSpecificType ?: null,
                'meal_plan' => $item['mealType'] ?? null,
                'meal_items' => $mealItems,
                'transfer_required' => $transferRequired,
                'transfer_type' => $transferType,
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
            'hotel' => '🏨',
            'guide' => '👤',
            'restaurant' => '🍽️',
            'attraction' => '🎯',
            'entry_port' => '✈️',
            'exit_port' => '🛫',
            'travel_point' => '🚐',
            'travel_hourly' => '🚗',
            'local_transport' => '🚕',
        ];

        $key = strtolower($type ?? '');
        return $map[$key] ?? '🧭';
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
                    if (is_array($rooms) && count($rooms) > 0) {
                        $firstRoom = $rooms[0];
                        $roomTypeName = $firstRoom['room_type'] ?? $firstRoom['roomType'] ?? 'N/A';
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
}
