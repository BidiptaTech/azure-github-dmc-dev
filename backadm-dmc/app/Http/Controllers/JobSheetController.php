<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Port;
use App\Models\Country;
use App\Models\City;
use App\Helpers\CommonHelper;

use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Guide;
use App\Models\Order;
use App\Models\Tour;
use App\Models\Jobsheet;
use App\Models\Zone;
use App\Models\Hotel;
use App\Models\Attraction;
use App\Models\Restaurant;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JobSheetController extends Controller
{
    /**
     * Get zone information for a location based on its type and name
     */
    private function getZoneForLocation($locationName, $dmcId)
    {
        if (empty($locationName) || empty($dmcId)) {
            return 'N/A';
        }

        // Check if it's a port (airports, seaports, etc.)
        $port = Port::where('port_name', 'like', '%' . $locationName . '%')->first();
        if ($port) {
            // For ports, we can return the port type or a default zone
            return $port->type ?? 'Port';
        }

        // Check if it's a hotel
        $hotel = Hotel::where('name', 'like', '%' . $locationName . '%')->first();
        if ($hotel) {
            $zoneId = $hotel->getZoneForDmc($dmcId);
            if ($zoneId) {
                $zone = Zone::where('zone_id', $zoneId)->first();
                return $zone ? $zone->zone_name : 'N/A';
            }
        }

        // Check if it's an attraction
        $attraction = Attraction::where('name', 'like', '%' . $locationName . '%')->first();
        if ($attraction) {
            $zoneId = $attraction->getZoneForDmc($dmcId);
            if ($zoneId) {
                $zone = Zone::where('zone_id', $zoneId)->first();
                return $zone ? $zone->zone_name : 'N/A';
            }
        }

        // Check if it's a restaurant
        $restaurant = Restaurant::where('name', 'like', '%' . $locationName . '%')->first();
        if ($restaurant) {
            $zoneId = $restaurant->getZoneForDmc($dmcId);
            if ($zoneId) {
                $zone = Zone::where('zone_id', $zoneId)->first();
                return $zone ? $zone->zone_name : 'N/A';
            }
        }

        return 'N/A';
    }

    /**
     * Display a listing of the ports.
     */
    public function index()
    {
        // $ports = Port::with(['country', 'city'])->get();
        $user = auth()->user();
        $dmcs = [];

        if ($user->role_id == 10 || $user->role_id == 19) {
            $dmc_ids = User::where('master_dmc_id', $user->userId)->where('role_id', 11)->get()->pluck('userId')->toArray();
            $dmcs = User::wherein('userId', $dmc_ids)->get();
        }

        elseif(in_array($user->role_id, [25, 62, 110])){
            if($user->role_id == 25){
                $master_dmc_id = $user->created_by;
            }
            elseif($user->role_id == 62){
                $product_head = User::where('userId', $user->created_by)->first();
                $master_dmc_id = $product_head->created_by;
            }
            elseif($user->role_id == 110){
                $product_manager = User::where('userId', $user->created_by)->first();
                $product_head = User::where('userId', $product_manager->created_by)->first();
                $master_dmc_id = $product_head->created_by;
            }
            
            $dmc_ids = User::where('master_dmc_id', $master_dmc_id)->where('role_id', 11)->get()->pluck('userId')->toArray();
            $dmcs = User::wherein('userId', $dmc_ids)->get();
        }


        $dmcDrivers = [];
        if(in_array($user->role_id, [11, 34, 66, 108, 128, 131, 132, 134, 135, 137, 138 ])){
            if($user->role_id == 11 || $user->role_id == 20){
                $resolvedDmcId = $user->userId;
                $dmcDrivers = Driver::orderBy('updated_at', 'desc')->where('dmc_id', $resolvedDmcId)->get();
            }
            elseif($user->role_id == 34 || $user->role_id == 128 || $user->role_id == 131 || $user->role_id == 132 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 137 || $user->role_id == 138){
                $resolvedDmcId = $user->created_by;
                $dmcDrivers = Driver::orderBy('updated_at', 'desc')->where('dmc_id', $resolvedDmcId)->get();
            }
            elseif($user->role_id == 66){
                $op_head_id = $user->created_by;
                $op_head = User::where('userId', $op_head_id)->first();
                $resolvedDmcId = $op_head->created_by;
                $dmcDrivers = Driver::orderBy('updated_at', 'desc')->where('dmc_id', $resolvedDmcId)->get();
            }
            elseif($user->role_id == 108){
                $op_manager_id = $user->created_by;
                $op_manager = User::where('userId', $op_manager_id)->first();
                $op_head = User::where('userId', $op_manager->created_by)->first();

                $resolvedDmcId = $op_head->created_by;
                
                $dmcDrivers = Driver::orderBy('updated_at', 'desc')->where('dmc_id', $resolvedDmcId)->get();
            }
        }
        return view('jobSheet.driver-jobs', compact('dmcDrivers', 'dmcs')); 
    }

    /**
     * Display the create driver jobsheet form.
     */
    public function createDriverJobsheet()
    {
        try {
            $user = auth()->user();
            
            // Get user's dmcId based on role
            $dmcId = null;
            $orders = [];
            $drivers = [];
            $vehicles = [];
            $tomorrow = Carbon::tomorrow()->toDateString(); // e.g., '2025-06-12'
            if (in_array($user->role_id, [11, 34, 66, 108, 124, 128, 131, 132, 134, 135, 137, 138])) {
                
                if($user->role_id == 11 || $user->role_id == 20){
                    $dmcId = $user->userId;
                }
                elseif($user->role_id == 34 || $user->role_id == 128 || $user->role_id == 131 || $user->role_id == 132 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 137 || $user->role_id == 138){
                    $dmcId = $user->created_by;
                }
                elseif($user->role_id == 66 || $user->role_id == 124){
                    $operation_head = User::where('userId', $user->created_by)->first();
                    $dmcId = $operation_head ? $operation_head->created_by : null;
                }
                elseif($user->role_id == 108){
                    $operation_manager = User::where('userId', $user->created_by)->first();
                    $operation_head = $operation_manager ? User::where('userId', $operation_manager->created_by)->first() : null;
                    $dmcId = $operation_head ? $operation_head->created_by : null;
                }

                $drivers = Driver::where('dmc_id', $dmcId)->get();
                $vehicles = Vehicle::where('dmc_id', $dmcId)->get();
                if(!is_null($dmcId)){
                    $tomorrow = Carbon::tomorrow()->toDateString();
                    $orders = Order::whereIn('type', ['entry_port', 'travel_hourly', 'travel_point', 'exit_port', 'local_transport'])
                        ->where('data->0->>dmc_id', $dmcId)
                        ->where('data->0->>pickupdate', $tomorrow)
                        ->get()
                        ->map(function($order) use ($dmcId, $tomorrow) {
                            // Add zone information for pickup and dropoff
                            $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
                            if (is_array($orderData) && isset($orderData[0])) {
                                $dataItem = $orderData[0];
                                $order->pickup_zone = $this->getZoneForLocation($dataItem['entrypickup'] ?? '', $dmcId);
                                $order->dropoff_zone = $this->getZoneForLocation($dataItem['entrydropoff'] ?? '', $dmcId);
                                
                                // Get vehicle from order data
                                $vehicleIdFromOrder = $dataItem['vehicles_id'] ?? null;
                                $vehicleFromOrder = null;
                                $driverFromVehicle = null;
                                
                                if ($vehicleIdFromOrder) {
                                    $vehicleFromOrder = Vehicle::where('vehicle_id', $vehicleIdFromOrder)->first();
                                    
                                    // If vehicle has a driver assigned, get that driver
                                    if ($vehicleFromOrder && $vehicleFromOrder->driver_id) {
                                        $driverFromVehicle = Driver::where('driver_id', $vehicleFromOrder->driver_id)->first();
                                    }
                                }
                                
                                // Check if there's an assignment in the jobsheets table
                                 $jobsheet = Jobsheet::where('date', $tomorrow)
                                    ->where('type', $order->type)
                                    ->where('service_type', $dataItem['type'] ?? null)
                                    ->where('journey_time', $dataItem['entrytime'] ?? null)
                                    ->where('dmc_id', $dmcId)
                                    ->where('order_id', $order->order_id)
                                    ->first();
                                
                                // Priority: Jobsheet assignment > Vehicle from order data
                                if ($jobsheet) {
                                    $order->assigned_driver_id = $jobsheet->driver_id;
                                    $order->assigned_vehicle_id = $jobsheet->vehicle_id;
                                    $order->driver = $jobsheet->driver_id ? Driver::where('driver_id', $jobsheet->driver_id)->first() : null;
                                    $order->vehicle = $jobsheet->vehicle_id ? Vehicle::where('vehicle_id', $jobsheet->vehicle_id)->first() : null;
                                } else {
                                    // Use vehicle and driver from order data as default
                                    $order->assigned_vehicle_id = $vehicleFromOrder ? $vehicleFromOrder->vehicle_id : null;
                                    $order->assigned_driver_id = $driverFromVehicle ? $driverFromVehicle->driver_id : null;
                                    $order->vehicle = $vehicleFromOrder;
                                    $order->driver = $driverFromVehicle;
                                }
                            }
                            return $order;
                        });
                }
            }
            else{
                $orders = Order::whereIn('type', ['entry_port', 'travel_hourly', 'travel_point', 'exit_port'])
                ->whereRaw("data->0->>'pickupdate' = ?", [$tomorrow])
               ->get();
            }
            
            return view('CreateJobSheet.create-driver-jobsheet', compact('dmcId', 'orders', 'drivers', 'vehicles'));
            
        } catch (\Exception $e) {
            \Log::error('Error in createDriverJobsheet: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading driver jobsheet form: ' . $e->getMessage());
        }
    }


    /**
     * Get DMCs by Master DMC ID
     */
    public function getDmcsByMaster($masterDmcId)
    {
        try {
            $dmcs = User::where('master_dmc_id', $masterDmcId)
                ->where('role_id', 11) // DMC role
                ->select('userId', 'name')
                ->get();

            return response()->json([
                'success' => true,
                'dmcs' => $dmcs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching DMCs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Drivers by DMC ID
     */
    public function getDriversByDmc($dmcId)
    {
        try {
            $drivers = Driver::where('dmc_id', $dmcId)
                ->where('status', 1)
                ->select('driver_id', 'name')
                ->get();

            return response()->json([
                'success' => true,
                'drivers' => $drivers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching Drivers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Driver Schedule from orders
     */
    public function getDriverSchedule($driverId)
    {
        try {
            // Find driver's vehicles
            $vehicles = Vehicle::where('driver_id', $driverId)->pluck('vehicle_id')->toArray();
            
            if (empty($vehicles)) {
                return response()->json([
                    'success' => true,
                    'schedule' => []
                ]);
            }
            
            // Get orders with the relevant types and filter by tour_status
            $orders = Order::whereIn('type', ['entry_port', 'travel_hourly', 'travel_point', 'exit_port', 'local_transport'])
                ->whereNotNull('data')
                ->whereNotNull('tour_id')
                ->whereHas('tour', function($query) {
                    $query->whereIn('tour_status', ['Confirmed', 'Definite', 'Actual']);
                })
                ->get();
            
            $schedule = [];
            
            foreach ($orders as $order) {
                // Try to decode data as JSON array first
                $jsonData = is_array($order->data) ? $order->data:json_decode($order->data, true);
                
                
                // If data is a JSON string within a string (escaped JSON), need to decode again
                if (is_string($jsonData)) {
                    $jsonData = json_decode($jsonData, true);
                }
                
                // If it's an array with numeric keys, take each item
                if (is_array($jsonData) && isset($jsonData[0])) {
                    foreach ($jsonData as $data) {
                        if (!is_array($data)) {
                            continue;
                        }
                        
                        // Check if the order's vehicle belongs to the driver
                        if (isset($data['vehicles_id']) && in_array($data['vehicles_id'], $vehicles)) {
                            // Extract required information
                            $scheduleItem = [
                                'tour_id' => $order->tour_id ?? 'N/A',
                                'type' => $order->type,
                                'pickup_date' => $data['pickupdate'] ?? ($data['bookingDate'] ?? 'N/A'),
                                'pickup_time' => $data['entrytime'] ?? 'N/A',
                                'pickup_location' => $data['entrypickup'] ?? 'N/A',
                                'dropoff_location' => $data['entrydropoff'] ?? 'N/A',
                                'status' => $order->status,
                                'customer_name' => $data['fullName'] ?? 'N/A',
                                'customer_phone' => $data['phone'] ?? 'N/A',
                                'customer_email' => $data['email'] ?? 'N/A',
                                'vehicle_name' => $data['vehicles_name'] ?? 'N/A',
                                'booking_type' => $data['bookingType'] ?? 'N/A',
                                'total_price' => $data['totalPrice'] ?? 'N/A',
                                'pax' => ($data['adults'] + $data['children']) ?? '0',
                                'child' => $data['children'],
                                'service_type' => $data['type']
                            ];
                            
                            $schedule[] = $scheduleItem;
                        }
                    }
                } else {
                    // It's a single object
                    $data = $jsonData;
                    
                    if (!is_array($data)) {
                        continue;
                    }
                    
                    // Check if the order's vehicle belongs to the driver
                    if (isset($data['vehicles_id']) && in_array($data['vehicles_id'], $vehicles)) {
                        // Extract required information
                        $scheduleItem = [
                            'tour_id' => $order->tour_id ?? 'N/A',
                            'order_id' => $order->id,
                            'type' => $order->type,
                            'pickup_date' => $data['pickupdate'] ?? ($data['bookingDate'] ?? 'N/A'),
                            'pickup_time' => $data['entrytime'] ?? 'N/A',
                            'pickup_location' => $data['entrypickup'] ?? 'N/A',
                            'dropoff_location' => $data['entrydropoff'] ?? 'N/A',
                            'status' => $order->status,
                            'customer_name' => $data['fullName'] ?? 'N/A',
                            'customer_phone' => $data['phone'] ?? 'N/A',
                            'customer_email' => $data['email'] ?? 'N/A',
                            'vehicle_name' => $data['vehicles_name'] ?? 'N/A',
                            'booking_type' => $data['bookingType'] ?? 'N/A',
                            'total_price' => $data['totalPrice'] ?? 'N/A',
                        ];
                        
                        $schedule[] = $scheduleItem;
                    }
                }
            }
            
            // Sort by pickup date and time
            usort($schedule, function ($a, $b) {
                $dateCompare = strtotime($a['pickup_date']) - strtotime($b['pickup_date']);
                if ($dateCompare == 0) {
                    return strtotime($a['pickup_time']) - strtotime($b['pickup_time']);
                }
                return $dateCompare;
            });
            
            return response()->json([
                'success' => true,
                'schedule' => $schedule
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching driver schedule: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Display guide jobs page.
     */
    public function indexGuide()
    {
        $user = auth()->user();
        $dmcs = [];

        if ($user->role_id == 10) {
            $dmc_ids = User::where('master_dmc_id', $user->userId)->where('role_id', 11)->get()->pluck('userId')->toArray();
            $dmcs = User::wherein('userId', $dmc_ids)->get();
        }
        elseif(in_array($user->role_id, [25, 62, 110])){
            if($user->role_id == 25){
                $master_dmc_id = $user->created_by;
            }
            elseif($user->role_id == 62){
                $product_head = User::where('userId', $user->created_by)->first();
                $master_dmc_id = $product_head->created_by;
            }
            elseif($user->role_id == 110){
                $product_manager = User::where('userId', $user->created_by)->first();
                $product_head = User::where('userId', $product_manager->created_by)->first();
                $master_dmc_id = $product_head->created_by;
            }
            
            $dmc_ids = User::where('master_dmc_id', $master_dmc_id)->where('role_id', 11)->get()->pluck('userId')->toArray();
            $dmcs = User::wherein('userId', $dmc_ids)->get();
        }


        $dmcGuides = [];
        if(in_array($user->role_id, [11, 34, 66, 108, 128, 131, 132, 134, 135, 137, 138])){
            if($user->role_id == 11 || $user->role_id == 20){
                $resolvedDmcId = $user->userId;
                $dmcGuides = Guide::orderBy('updated_at', 'desc')->where('dmc_id', $resolvedDmcId)->with('languages')->get();
            }
            elseif($user->role_id == 34 || $user->role_id == 128 || $user->role_id == 131 || $user->role_id == 132 || $user->role_id == 134 || $user->role_id == 135 || $user->role_id == 137 || $user->role_id == 138){
                $resolvedDmcId = $user->created_by;
                $dmcGuides = Guide::orderBy('updated_at', 'desc')->where('dmc_id', $resolvedDmcId)->with('languages')->get();
            }
            elseif($user->role_id == 65){
                $op_head_id = $user->created_by;
                $op_head = User::where('userId', $op_head_id)->first();
                $resolvedDmcId = $op_head->created_by;
                $dmcGuides = Guide::orderBy('updated_at', 'desc')->where('dmc_id', $resolvedDmcId)->with('languages')->get();
            }
            elseif($user->role_id == 99){
                $op_manager_id = $user->created_by;
                $op_manager = User::where('userId', $op_manager_id)->first();
                $op_head = User::where('userId', $op_manager->created_by)->first();
                $resolvedDmcId = $op_head->created_by;
                $dmcGuides = Guide::orderBy('updated_at', 'desc')->where('dmc_id', $resolvedDmcId)->with('languages')->get();
            }
        }

        return view('jobSheet.guide-jobs', compact('dmcs', 'dmcGuides'));
    }

    /**
     * Get Guides by DMC ID
     */
    public function getGuidesByDmc($dmcId)
    {
        try {
            $guides = Guide::where('dmc_id', $dmcId)
                ->where('status', 1)
                ->with('languages')
                ->get();

            return response()->json([
                'success' => true,
                'guides' => $guides
            ]); 
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching Guides: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get Guide Schedule from orders
     */
    public function getGuideSchedule($guideId)
    {
        try {
            // Find guide
            $guide = Guide::where('guide_id', $guideId)->first();
            
            if (empty($guide)) {
                return response()->json([
                    'success' => true,
                    'schedule' => []
                ]);
            }
            
            // Get orders with the relevant types and filter by tour_status
            $orders = Order::whereIn('type', ['guide'])
                ->whereNotNull('data')
                ->whereNotNull('tour_id')
                ->whereHas('tour', function($query) {
                    $query->whereIn('tour_status', ['Confirmed', 'Definite', 'Actual']);
                })
                ->get();
            
            $schedule = [];
            foreach ($orders as $order) {
                // Check if data is already an array
                if (is_array($order->data)) {
                    $jsonData = $order->data;
                } else {
                    $jsonData = is_array($order->data) ? $order->data:json_decode($order->data, true);
                    
                    // If data is a JSON string within a string (escaped JSON), need to decode again
                    if (is_string($jsonData)) {
                        $jsonData = json_decode($jsonData, true);
                    }
                }
                
                // If it's an array with numeric keys, take each item
                if (is_array($jsonData) && isset($jsonData[0])) {
                    foreach ($jsonData as $data) {
                        if (!is_array($data)) {
                            continue;
                        }
                        
                        // Check if the order's vehicle belongs to the driver
                        if (isset($data['guide_id']) && $data['guide_id'] == $guide->guide_id) {
                            // Extract required information
                            $scheduleItem = [
                                'tour_id' => $order->tour_id ?? 'N/A',
                                'order_id' => $order->id,
                                'type' => $order->type,
                                'pickup_date' => $data['pickupdate'] ?? ($data['bookingDate'] ?? 'N/A'),
                                'pickup_time' => $data['entrytime'] ?? 'N/A',
                                'pickup_location' => $data['entrypickup'] ?? 'N/A',
                                'customer_name' => $data['fullName'] ?? 'N/A',
                                'customer_phone' => $data['phone'] ?? 'N/A',
                                'customer_email' => $data['email'] ?? 'N/A',
                                'guide_name' => $data['guide_name'] ?? 'N/A',
                                'booking_type' => $data['bookingType'] ?? 'N/A',
                                'total_price' => $data['totalPrice'] ?? 'N/A',
                                'pax' => ($data['adults'] + $data['children']) ?? '0',
                                'service_type' => $data['type'] ?? 'N/A'
                            ];
                            
                            $schedule[] = $scheduleItem;
                        }
                    }
                } else {
                    // It's a single object
                    $data = $jsonData;
                    
                    if (!is_array($data)) {
                        continue;
                    }
                    
                    // Check if the order's guide matches
                    if (isset($data['guide_id']) && $data['guide_id'] == $guide->guide_id) {
                        // Extract required information
                        $scheduleItem = [
                            'tour_id' => $order->tour_id ?? 'N/A',
                            'order_id' => $order->id,
                            'type' => $order->type,
                            'pickup_date' => $data['pickupdate'] ?? ($data['bookingDate'] ?? 'N/A'),
                            'pickup_time' => $data['entrytime'] ?? 'N/A',
                            'pickup_location' => $data['entrypickup'] ?? 'N/A',
                            'customer_name' => $data['fullName'] ?? 'N/A',
                            'customer_phone' => $data['phone'] ?? 'N/A',
                            'customer_email' => $data['email'] ?? 'N/A',
                            'guide_name' => $data['guide_name'] ?? 'N/A',
                            'booking_type' => $data['bookingType'] ?? 'N/A',
                            'total_price' => $data['totalPrice'] ?? 'N/A',
                            'pax' => ($data['adults'] ?? 0) + ($data['children'] ?? 0),
                            'service_type' => $data['type'] ?? 'N/A'
                        ];
                        
                        $schedule[] = $scheduleItem;
                    }
                }
            }
            
            // Sort by pickup date and time
            usort($schedule, function ($a, $b) {
                $dateCompare = strtotime($a['pickup_date']) - strtotime($b['pickup_date']);
                if ($dateCompare == 0) {
                    return strtotime($a['pickup_time']) - strtotime($b['pickup_time']);
                }
                return $dateCompare;
            });
            
            return response()->json([
                'success' => true,
                'schedule' => $schedule
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching guide schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Tour Details by ID
     */
    public function getTourDetails($tourId)
    {
        try {
            $tour = Tour::where('tour_id', $tourId)
                ->select('tour_id', 'check_in_time', 'check_out_time')
                ->first();
            
            // Get tomorrow's date
            $tomorrow = now()->addDay()->format('Y-m-d');

            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found'
                ], 404);
            }

            // Add tomorrow's date to the response
            $tour->tomorrow_date = $tomorrow;

            return response()->json([
                'success' => true,
                'tour' => $tour
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching tour details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new driver jobsheet.
     */
    public function storeDriverJobsheet(Request $request)
    {
        $user = auth()->user();
        try {
            $validator = Validator::make($request->all(), [
                'tourId' => 'required|exists:tours,tour_id',
                'date' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get tour details to validate date range
            $tour = Tour::where('tour_id', $request->tourId)->first();
            $selectedDate = new \DateTime($request->date);
            $startDate = new \DateTime($tour->check_in_time);
            $endDate = new \DateTime($tour->check_out_time);

            if ($selectedDate < $startDate || $selectedDate > $endDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected date must be between tour start and end dates'
                ], 422);
            }

            // TODO: Add your logic to store the jobsheet
            // For example:
            $lastJobsheet = Jobsheet::withTrashed()->orderBy('created_at', 'desc')->first();
            $jobsheet_max_id = $lastJobsheet->jobsheet_id ?? 0;
            $jobsheetId = CommonHelper::createId($jobsheet_max_id);
            while (Jobsheet::where('jobsheet_id', $jobsheetId)->exists()) {
                $jobsheetId = CommonHelper::createId($jobsheetId);
            }
            $jobsheet = new Jobsheet();
            $jobsheet->jobsheet_id = $jobsheetId;
            $jobsheet->dmc_id = $request->dmc_id;
            $jobsheet->tour_id = $request->tourId;
            $jobsheet->date = $request->date;
            $jobsheet->created_by = $user->userId;
            $jobsheet->save();

            return response()->json([
                'success' => true,
                'message' => 'Driver jobsheet created successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating driver jobsheet: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTourOrders($tourId, $date)
    {
        try {
            $orderTypes = ['entry_port', 'exit_port', 'travel_point', 'travel_hourly'];
            $dmc_id = null;
            $vehicles = [];
            $drivers = [];
            $firstItem = [];
            
            // First, get all orders for this tour
            $allOrders = Order::where('tour_id', $tourId)
                ->whereIn('type', $orderTypes)
                ->whereNotNull('data')
                ->get();
            
            // Filter orders by booking_date in the JSON data
            $filteredOrders = $allOrders->filter(function($order) use ($date) {
                $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
                
                if (!is_array($orderData) || empty($orderData)) {
                    return false;
                }
                
                // Check if any item in the data array has the matching booking_date
                foreach ($orderData as $item) {
                    if (is_array($item)) {
                        // Check different possible date fields (case insensitive)
                        $bookingDate = $item['booking_date'] ?? $item['bookingDate'] ?? $item['bookingdate'] ?? $item['Booking_date'] ?? null;
                        $pickupDate = $item['pickup_date'] ?? $item['pickupDate'] ?? $item['pickupdate'] ?? null;
                        
                        // Check if any of the date fields match the requested date
                        if (($bookingDate && substr($bookingDate, 0, 10) === $date) || 
                            ($pickupDate && substr($pickupDate, 0, 10) === $date)) {
                            return true;
                        }
                    }
                }
                
                return false;
            });

            // Convert to sequential array (values()) to remove preserved keys
            $orders = $filteredOrders->values()->map(function ($order) use (&$dmc_id, &$vehicles, &$drivers, &$firstItem) {
                // The data is already cast to JSON by Laravel, but we'll ensure it's properly formatted
                $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
                
                // Extract the first item from the data array if it exists
                $firstItem = is_array($orderData) && !empty($orderData) ? $orderData[0] : [];
                $dmc_id = $firstItem['dmc_id'] ?? null;
                
                // Check if vehicle exists for this order
                $vehicle = null;
                if (!empty($firstItem['vehicles_id'])) {
                    $vehicle = Vehicle::where('vehicle_id', $firstItem['vehicles_id'])->first();
                }
                
                // Check if driver exists for this vehicle
                $driver = null;
                if ($vehicle && $vehicle->driver_id) {
                    $driver = Driver::where('driver_id', $vehicle->driver_id)->first();
                }
                    
                return [
                    'id' => $order->id,
                    'type' => $order->type,
                    'pickup_time' => $firstItem['pickupTime'] ?? ($firstItem['entrytime'] ?? null),
                    'pickup_location' => $firstItem['entrypickup'] ?? null,
                    'dropoff_location' => $firstItem['entrydropoff'] ?? null,
                    'customer_name' => $firstItem['fullName'] ?? null,
                    'customer_phone' => $firstItem['phone'] ?? null,
                    'customer_email' => $firstItem['email'] ?? null,
                    'vehicle_name' => $firstItem['vehicleName'] ?? null,
                    'booking_type' => $order->bookingType ?? null,
                    'total_price' => $firstItem['totalPrice'] ?? null,
                    'pax' => $firstItem['pax'] ?? null,
                    'status' => $order->status,
                    'data' => $orderData, // Include the full data for reference
                    'vehicle' => $vehicle,
                    'driver' => $driver
                ];
            });
            
            // Get vehicles and drivers for the DMC if DMC ID is available
            if ($dmc_id) {
                $vehicles = Vehicle::select('vehicle_id', 'vehicle_name', 'vehicle_plate_no')
                    ->where('dmc_id', $dmc_id)
                    ->get();
                
                $drivers = Driver::select('driver_id', 'name', 'license_no')
                    ->where('dmc_id', $dmc_id)
                    ->get();
            }

            return response()->json([
                'success' => true,
                'data' => $orders,
                'vehicles' => $vehicles,
                'drivers' => $drivers,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getTourOrders: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching tour orders: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display the create guide jobsheet form.
     */
    public function createGuideJobsheet()
    {
        try {
            $user = auth()->user();
            $tomorrow = now()->addDay()->format('Y-m-d');
            $orders = [];
            $guides = [];
            
            // Define the order types we want to filter by
            $orderTypes = ['guide', 'dayguide', 'halfguide'];
            
            // Get user's dmcId based on role
            $dmcId = null;
            if (in_array($user->role_id, [11, 20, 34, 65, 99, 108, 124, 128, 131, 132, 134, 135, 137, 138])) {
                if($user->role_id == 11 || $user->role_id == 20){
                    $dmcId = $user->userId;
                }
                elseif($user->role_id == 34 || in_array($user->role_id, [128, 131, 132, 134, 135, 137, 138])){
                    $dmcId = $user->created_by;
                }
                elseif($user->role_id == 65 || $user->role_id == 124){
                    $operation_head = User::where('userId', $user->created_by)->first();
                    $dmcId = $operation_head ? $operation_head->created_by : null;
                }
                elseif($user->role_id == 99){
                    $operation_manager = User::where('userId', $user->created_by)->first();
                    $operation_head = $operation_manager ? User::where('userId', $operation_manager->created_by)->first() : null;
                    $dmcId = $operation_head ? $operation_head->created_by : null;
                }

                // Get guides for this DMC
                $guides = Guide::where('dmc_id', $dmcId)->with('languages')->get();

                // Get orders with tour information
                $orders = Order::select('orders.*', 'tours.id as tour_id_numeric', 'tours.tour_id', 'tours.display_id')
                    ->leftJoin('tours', 'orders.tour_id', '=', 'tours.tour_id')
                    ->whereIn('orders.type', $orderTypes)
                    ->whereRaw("data->0->>'pickupdate' = ?", [$tomorrow])
                    ->whereRaw("data->0->>'dmc_id' = ?", [$dmcId])
                    ->get();
                
                    
                // Process guide data for orders - check jobsheets table for assignments
                $orders->map(function($order) use ($tomorrow) {
                    $orderData = is_array($order->data) ? $order->data : json_decode($order->data, true);
                    $dataItem = is_array($orderData) && isset($orderData[0]) ? $orderData[0] : [];
                    
                    // Convert entrytime to proper time format (HH:MM:SS)
                    $entryTime = $dataItem['entrytime'] ?? null;
                    if ($entryTime !== null) {
                        $entryTime = trim($entryTime);
                        
                        // Handle numeric values like 4 or "4"
                        if (is_numeric($entryTime)) {
                            $entryTime = str_pad($entryTime, 2, '0', STR_PAD_LEFT) . ':00:00';
                        }
                        // Handle "4:00 AM" or "4:00 PM" format
                        elseif (preg_match('/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i', $entryTime, $matches)) {
                            $hour = (int)$matches[1];
                            $minute = $matches[2];
                            $meridiem = strtoupper($matches[3]);
                            
                            // Convert to 24-hour format
                            if ($meridiem === 'PM' && $hour !== 12) {
                                $hour += 12;
                            } elseif ($meridiem === 'AM' && $hour === 12) {
                                $hour = 0;
                            }
                            
                            $entryTime = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':' . $minute . ':00';
                        }
                        // Handle "4:00" or "04:00" format (HH:MM)
                        elseif (preg_match('/^(\d{1,2}):(\d{2})$/', $entryTime, $matches)) {
                            $hour = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                            $minute = $matches[2];
                            $entryTime = $hour . ':' . $minute . ':00';
                        }
                        // Handle "04:00:00" format (HH:MM:SS) - already in correct format
                        elseif (preg_match('/^\d{2}:\d{2}:\d{2}$/', $entryTime)) {
                            // Already in correct format, do nothing
                        }
                        // Invalid format
                        else {
                            $entryTime = null;
                        }
                    }
                    
                    // Check if there's an assignment in the jobsheets table
                    $jobsheet = null;
                    if ($entryTime !== null) {
                        $jobsheet = Jobsheet::where('date', $tomorrow)
                            ->where('type', $order->type)
                            ->where('service_type', $order->type) // For guides, service_type is same as type
                            ->where('journey_time', $entryTime)
                            ->first();
                    }
                    
                    // Get OrderGuide from order data if available
                    $orderGuide = null;
                    if (isset($dataItem['guide_id'])) {
                        $orderGuide = Guide::select('guide_id', 'name', 'email', 'government_license_no')
                            ->where('guide_id', $dataItem['guide_id'])
                            ->with('languages')
                            ->first();
                    }
                    $order->OrderGuide = $orderGuide;
                    
                    // Attach guide info from jobsheet
                    if ($jobsheet) {
                        $order->assigned_guide_id = $jobsheet->guide_id;
                        $order->guide = $jobsheet->guide_id ? Guide::where('guide_id', $jobsheet->guide_id)->with('languages')->first() : null;
                    } else {
                        $order->assigned_guide_id = null;
                        $order->guide = null;
                    }
                    
                    return $order;
                });
            }
            else {
                // For other roles, just get all orders for tomorrow
                $orders = Order::select('orders.*', 'tours.id as tour_id_numeric', 'tours.tour_id', 'tours.display_id')
                    ->leftJoin('tours', 'orders.tour_id', '=', 'tours.tour_id')
                    ->whereIn('orders.type', $orderTypes)
                    ->whereRaw("data->0->>'pickupdate' = ?", [$tomorrow])
                    ->get();
            }
           
            
            return view('CreateJobSheet.create-guide-jobsheet', compact('orders', 'guides', 'dmcId'));
            
        } catch (\Exception $e) {
            \Log::error('Error in createGuideJobsheet: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading guide jobsheet form: ' . $e->getMessage());
        }
    }

    /**
     * Get Tour Guide Orders
     */
    public function getTourGuideOrders($tourId, $date)
    {
        try {
            $orderTypes = ['guide'];
            $dmc_id = null;
            $guides = [];
            $firstItem = [];
            
            $orders = Order::where('tour_id', $tourId)
                ->whereIn('type', $orderTypes)
                ->whereNotNull('data')
                ->get()
                ->map(function ($order) use (&$dmc_id, &$guides, &$firstItem) {
                    // The data is already cast to JSON by Laravel, but we'll ensure it's properly formatted
                    $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
                    
                    // Extract the first item from the data array if it exists
                    $firstItem = is_array($orderData) && !empty($orderData) ? $orderData[0] : [];
                    $dmc_id = $firstItem['dmc_Id'] ?? null;
                    
                    // Get guide information if guide_id exists
                    $guide = null;
                    if (isset($firstItem['guide_id'])) {
                        $guide = Guide::where('dmc_id', $dmc_id)->first();
                    }
                    
                    return [
                        'id' => $order->id, // Added order ID for reference
                        'type' => $order->type,
                        'pickup_time' => $firstItem['entrytime'] ?? null,
                        'pickup_location' => $firstItem['entrypickup'] ?? null,
                        'customer_name' => $firstItem['fullName'] ?? null,
                        'customer_phone' => $firstItem['phone'] ?? null,
                        'customer_email' => $firstItem['email'] ?? null,
                        'guide_name' => $firstItem['guide_name'] ?? null,
                        'booking_type' => $order->bookingType ?? null,
                        'total_price' => $firstItem['totalPrice'] ?? null,
                        'pax' => $firstItem['pax'] ?? null,
                        'status' => $order->status,
                        'data' => $orderData, // Include the full data for reference
                        'guide' => $guide
                    ];
                });

            // Get guides for the DMC if DMC ID is available
            if($dmc_id){
                $guides = Guide::select('guide_id', 'name', 'government_license_no')
                    ->where('dmc_id', $dmc_id)
                    ->where('status', 1)
                    ->with('languages')
                    ->get();
            }
            
            return response()->json([
                'success' => true,
                'data' => $orders,
                'guides' => $guides,
                'dmc_id' => $dmc_id // Added DMC ID to the response
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getTourGuideOrders: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching tour guide orders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new guide jobsheet.
     */
    public function storeGuideJobsheet(Request $request)
    {
        try {
            
            $validator = Validator::make($request->all(), [
                'date' => 'required|date',
                'dmc_id' => 'nullable|exists:users,userId'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // For now, we'll just log the creation of a guide jobsheet
            // In a real application, you might want to save this to a jobsheets table
            \Log::info('Guide jobsheet created for date: ' . $request->date . ' by user: ' . auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Guide jobsheet created successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating guide jobsheet: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating guide jobsheet: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store driver and vehicle assignments for a jobsheet.
     */
    public function storeDriverAssignments(Request $request)
    {
        try {
            
            $validator = Validator::make($request->all(), [
                'tourId' => 'required|exists:tours,tour_id',
                'date' => 'required|date',
                'assignments' => 'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get tour details to validate date range
            $tour = Tour::where('tour_id', $request->tourId)->first();
            if (!$tour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tour not found'
                ], 404);
            }

            $selectedDate = new \DateTime($request->date);
            $startDate = new \DateTime($tour->check_in_time);
            $endDate = new \DateTime($tour->check_out_time);

            if ($selectedDate < $startDate || $selectedDate > $endDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected date must be between tour start and end dates'
                ], 422);
            }

            // Process each assignment
            $assignmentsData = [];
            foreach ($request->assignments as $assignment) {
                $orderId = $assignment['order_id'] ?? null;
                $orderType = $assignment['order_type'] ?? null;
                $driverId = $assignment['driver_id'] ?? null;
                $vehicleId = $assignment['vehicle_id'] ?? null;

                if (empty($orderId) || (!$driverId && !$vehicleId)) {
                    continue; // Skip invalid assignments
                }

                // Find the order
                $order = Order::where('booking_id', $orderId)->first();
                if (!$order) continue;

                // Update order data with driver and vehicle assignments
                $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
                
                if (is_array($orderData) && !empty($orderData)) {
                    // If order has data array items
                    foreach ($orderData as &$item) {
                        if (is_array($item)) {
                            // Assign driver and vehicle
                            if ($driverId) {
                                $driver = Driver::where('driver_id', $driverId)->first();
                                if ($driver) {
                                    $item['driver_id'] = $driver->driver_id;
                                    $item['driver_name'] = $driver->name;
                                }
                            }

                            if ($vehicleId) {
                                $vehicle = Vehicle::where('vehicle_id', $vehicleId)->first();
                                if ($vehicle) {
                                    $item['vehicles_id'] = $vehicle->vehicle_id;
                                    $item['vehicles_name'] = $vehicle->vehicle_name;
                                }
                            }
                        }
                    }

                    // Update the order with the modified data
                    $order->data = $orderData;
                    $order->save();

                    $assignmentsData[] = [
                        'order_id' => $orderId,
                        'order_type' => $orderType,
                        'driver_id' => $driverId,
                        'vehicle_id' => $vehicleId,
                        'date' => $request->date,
                        'tour_id' => $request->tourId
                    ];
                }
            }

            // Create or update jobsheet record
            $jobsheet = Jobsheet::where('tour_id', $request->tourId)
                ->where('date', $request->date)
                ->where('type', 'driver')
                ->first();

            if ($jobsheet) {
                // Update existing jobsheet
                $jobsheet->assignments = $assignmentsData;
                $jobsheet->updated_at = now();
                $jobsheet->save();
            } else {
                // Create new jobsheet
                $jobsheet = new Jobsheet();
                $jobsheet->tour_id = $request->tourId;
                $jobsheet->date = $request->date;
                $jobsheet->type = 'driver';
                $jobsheet->assignments = $assignmentsData;
                $jobsheet->status = 1;
                $jobsheet->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Driver assignments saved successfully',
                'assignments' => $assignmentsData
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in storeDriverAssignments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error saving driver assignments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle updates when a driver or vehicle assignment changes.
     */
    public function updateDriverVehicleAssignment(Request $request)
    {
        try {
            // Log all incoming request data for debugging
            \Log::info('updateDriverVehicleAssignment called', [
                'all_data' => $request->all(),
                'driver_id' => $request->driver_id,
                'vehicle_id' => $request->vehicle_id,
                'order_id' => $request->order_id,
                'tour_id' => $request->tour_id,
                'order_type' => $request->order_type,
                'type' => $request->type,
                'date' => $request->date,
                'dmc_id' => $request->dmc_id
            ]);
            
            // Validate required fields
            $validator = Validator::make($request->all(), [
                'date' => 'required|date',
                'dmc_id' => 'required',
                'order_type' => 'required',
                'type' => 'required',
                'entry_time' => 'nullable', // Changed to nullable
                'order_id' => 'required'
            ]);

            if ($validator->fails()) {
                \Log::error('Validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get the order to extract entry_time if not provided
            $order = Order::where('booking_id', $request->order_id)
                ->first();
            
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }
            
            // Extract entry_time from order data if not provided in request
            $entryTimeFromRequest = $request->entry_time;
            if (empty($entryTimeFromRequest)) {
                $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
                $dataItem = is_array($orderData) && isset($orderData[0]) ? $orderData[0] : [];
                $entryTimeFromRequest = $dataItem['entrytime'] ?? null;
                
                \Log::info('Entry time extracted from order data', [
                    'order_id' => $request->order_id,
                    'extracted_entrytime' => $entryTimeFromRequest,
                    'dataItem' => $dataItem
                ]);
            }

            // Convert entry_time to proper time format (HH:MM:SS)
            $entryTime = $entryTimeFromRequest;
            if ($entryTime !== null) {
                $entryTime = trim($entryTime);
                
                // Handle numeric values like 4 or "4"
                if (is_numeric($entryTime)) {
                    $entryTime = str_pad($entryTime, 2, '0', STR_PAD_LEFT) . ':00:00';
                }
                // Handle "4:00 AM" or "4:00 PM" format
                elseif (preg_match('/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i', $entryTime, $matches)) {
                    $hour = (int)$matches[1];
                    $minute = $matches[2];
                    $meridiem = strtoupper($matches[3]);
                    
                    // Convert to 24-hour format
                    if ($meridiem === 'PM' && $hour !== 12) {
                        $hour += 12;
                    } elseif ($meridiem === 'AM' && $hour === 12) {
                        $hour = 0;
                    }
                    
                    $entryTime = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':' . $minute . ':00';
                }
                // Handle "4:00" or "04:00" format (HH:MM)
                elseif (preg_match('/^(\d{1,2}):(\d{2})$/', $entryTime, $matches)) {
                    $hour = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                    $minute = $matches[2];
                    $entryTime = $hour . ':' . $minute . ':00';
                }
                // Handle "04:00:00" format (HH:MM:SS) - already in correct format
                elseif (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $entryTime)) {
                    // Invalid format
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid time format for entry_time'
                    ], 422);
                }
            }

            // Get the actual numeric tour_id (not the display_id)
            $actualTourId = $order->tour_id;
            $jobsheet = null;
            $vehicle = null;
            $driver = null;
            
            // Check if a record with the same date, order_type, type, and entry_time exists
            $existingJobsheet = Jobsheet::where('date', $request->date)
                ->where('journey_time', $entryTime)
                ->where('tour_id', $actualTourId)
                ->where('order_id', $request->order_id)
                ->first(); 
                
            $lastJobsheet = Jobsheet::withTrashed()->orderBy('created_at', 'desc')->first();
            $jobsheet_max_id = $lastJobsheet->jobsheet_id ?? 0;
            $jobsheetId = CommonHelper::createId($jobsheet_max_id);
            while (Jobsheet::where('jobsheet_id', $jobsheetId)->exists()) {
                $jobsheetId = CommonHelper::createId($jobsheetId);
            }
            
            $user = auth()->user();
            
            if ($existingJobsheet) {
                // Update existing record
                \Log::info('Updating existing jobsheet', [
                    'jobsheet_id' => $existingJobsheet->jobsheet_id,
                    'driver_id' => $request->driver_id,
                    'vehicle_id' => $request->vehicle_id
                ]);
                
                if ($request->has('driver_id') && !empty($request->driver_id)) {
                    $existingJobsheet->driver_id = $request->driver_id;
                    $vehicle = Vehicle::where('driver_id', $request->driver_id)
                        ->where('dmc_id', $request->dmc_id)
                        ->orderBy('created_at', 'desc')
                        ->first();
                }
                if ($request->has('vehicle_id') && !empty($request->vehicle_id)) {
                    $existingJobsheet->vehicle_id = $request->vehicle_id;
                    
                    // Get the vehicle and its assigned driver
                    $vehicle = Vehicle::where('vehicle_id', $request->vehicle_id)->first();
                    if ($vehicle && $vehicle->driver_id && !$request->driver_id) {
                        $driver = Driver::where('driver_id', $vehicle->driver_id)->first();
                        // Also update the jobsheet with this driver
                        $existingJobsheet->driver_id = $vehicle->driver_id;
                    }
                }
                if ($request->has('guide_id') && !empty($request->guide_id)) {
                    $existingJobsheet->guide_id = $request->guide_id;
                }
                $is_saved = $existingJobsheet->save();
                if($is_saved){
                    $jobsheet = $existingJobsheet;
                    $order = Order::where('booking_id', $request->order_id)->first();
                    $order->is_approve = true;
                    $order->save();
                }
                else{
                    return response()->json([
                        'success' => false,
                        'message' => 'Error updating driver/vehicle/guide assignment'
                    ], 500);
                }
            } else {
                // Create new record
                $jobsheet = new Jobsheet();
                $jobsheet->jobsheet_id = $jobsheetId;
                $jobsheet->dmc_id = $request->dmc_id;
                $jobsheet->created_by = $user->userId;
                $jobsheet->tour_id = $actualTourId; // Use the actual numeric tour_id
                $jobsheet->date = $request->date;
                $jobsheet->type = $request->order_type;
                $jobsheet->journey_time = $entryTime; // Use the formatted time
                $jobsheet->data = json_encode([
                    'pickup' => $request->entrypickup,
                    'dropoff' => $request->entrydropoff ?? null,
                ]);
                $jobsheet->service_type = $request->type;
                
                if ($request->has('driver_id') && !empty($request->driver_id)) {
                    $jobsheet->driver_id = $request->driver_id;
                    $vehicle = Vehicle::where('driver_id', $request->driver_id)
                        ->where('dmc_id', $request->dmc_id)
                        ->orderBy('created_at', 'desc')
                        ->first();
                }
                if ($request->has('vehicle_id') && !empty($request->vehicle_id)) {
                    $jobsheet->vehicle_id = $request->vehicle_id;
                    
                    // Get the vehicle and its assigned driver
                    $vehicle = Vehicle::where('vehicle_id', $request->vehicle_id)->first();
                    if ($vehicle && $vehicle->driver_id) {
                        $driver = Driver::where('driver_id', $vehicle->driver_id)->first();
                        // Also update the jobsheet with this driver
                        $jobsheet->driver_id = $vehicle->driver_id;
                    }
                }
                if ($request->has('guide_id') && !empty($request->guide_id)) {
                    $jobsheet->guide_id = $request->guide_id;
                }

                $jobsheet->order_id = $request->order_id;
                $jobsheet->save();
            }

            // Log the driver information being returned
            \Log::info('Assignment update response', [
                'jobsheet_id' => $jobsheet->jobsheet_id ?? null,
                'vehicle_id' => $jobsheet->vehicle_id ?? null,
                'driver_id' => $jobsheet->driver_id ?? null,
                'driver_returned' => $driver ? [
                    'driver_id' => $driver->driver_id,
                    'name' => $driver->name,
                    'license_no' => $driver->license_no,
                    'full_driver_object' => $driver->toArray()
                ] : null
            ]);
            
            // Ensure driver name is complete
            if ($driver) {
                \Log::info('Driver name check', [
                    'driver_id' => $driver->driver_id,
                    'name_field' => $driver->name,
                    'name_length' => strlen($driver->name ?? ''),
                    'attributes' => $driver->getAttributes()
                ]);
            }
            
            // Fresh fetch of driver if it exists to ensure all data is loaded
            if ($driver) {
                $driver = $driver->fresh(); // Reload from database
            }
            
            // Return success with driver information
            return response()->json([
                'success' => true,
                'message' => 'Assignment updated successfully',
                'jobsheet' => $jobsheet,
                'vehicle' => $vehicle,
                'driver' => $driver ? [
                    'driver_id' => $driver->driver_id,
                    'name' => $driver->name,
                    'license_no' => $driver->license_no ?? null
                ] : null
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating driver/vehicle/guide assignment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateGuideJobsheet(Request $request){
        $date = $request->date;
        $tour_id = $request->tour_id;
        $order_id = $request->order_id;
        $user = auth()->user();

        $order = Order::where('booking_id', $order_id)->first();
        
        // Check if data is already an array or a JSON string
        if (is_string($order->data)) {
            $order_data = json_decode($order->data, true);
        } else {
            $order_data = $order->data; // Already an array
        }
        
        // Safely extract data with null coalescing
        $firstOrderData = $order_data[0] ?? [];
        $pickup = $firstOrderData['entrypickup'] ?? null;
        $dropoff = null;
        $type = $order->type;
        $entry_time = $firstOrderData['entrytime'] ?? null;
        $entrypickup = $firstOrderData['entrypickup'] ?? null;

        $existingJobsheet = Jobsheet::where('date', $date)
            ->where('type', $request->order_type)
            ->where('service_type', $request->type)
            ->where('tour_id', $tour_id)
            ->where('date', $date)
            ->first();
        if($existingJobsheet){
            $existingJobsheet->guide_id = $request->guide_id;
            $existingJobsheet->save();
        }
        else{
            $lastJobsheet = Jobsheet::withTrashed()->orderBy('created_at', 'desc')->first();
                $jobsheet_max_id = $lastJobsheet->jobsheet_id ?? 0;
                $jobsheetId = CommonHelper::createId($jobsheet_max_id);
                while (Jobsheet::where('jobsheet_id', $jobsheetId)->exists()) {
                    $jobsheetId = CommonHelper::createId($jobsheetId);
                }

                $jobsheet = new Jobsheet();
                $jobsheet->jobsheet_id = $jobsheetId;
                $jobsheet->dmc_id = $request->dmc_id;
                $jobsheet->created_by = $user->userId;
                $jobsheet->tour_id = $tour_id;
                $jobsheet->date = $date;
                $jobsheet->type = $request->order_type;
                $jobsheet->journey_time = $request->entry_time;
                $jobsheet->guide_id = $request->guide_id;
                $jobsheet->data = json_encode([
                    'pickup' => $entrypickup,
                ]);
                $jobsheet->type = 'guide';
                $jobsheet->service_type = $request->type;
                $jobsheet->order_id = $order_id;
                $jobsheet->save();
        }
        return response()->json([
            'success' => true,
            'message' => 'Guide jobsheet updated successfully',
        ]);
        
    }
    
    /**
     * Display the jobsheets view.
     */
    public function viewJobsheets()
    {
        try {
            $user = auth()->user();
            $dmcId = null;
            // Determine DMC ID based on user role
            if (in_array($user->role_id, [11, 35, 78, 120, 130, 132, 133, 135, 136, 137, 138])) {
                if($user->role_id == 11 || $user->role_id == 20){
                    $dmcId = $user->userId;
                }
                elseif($user->role_id == 35 || $user->role_id == 130 || $user->role_id == 132 || $user->role_id == 133 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138){
                    $dmcId = $user->created_by;
                }
                elseif($user->role_id == 78){
                    $operation_head = User::where('userId', $user->created_by)->first();
                    $dmcId = $operation_head ? $operation_head->created_by : null;
                }
                elseif($user->role_id == 120){
                    $operation_manager = User::where('userId', $user->created_by)->first();
                    $operation_head = $operation_manager ? User::where('userId', $operation_manager->created_by)->first() : null;
                    $dmcId = $operation_head ? $operation_head->created_by : null;
                }
            }
            // Get all tours for the filter dropdown
            $tours = Tour::select('tour_id', 'display_id')->orderBy('created_at', 'desc')->get();
            return view('CreateJobSheet.view-jobsheets', compact('tours', 'dmcId'));
            
        } catch (\Exception $e) {
            \Log::error('Error in viewJobsheets: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading jobsheets view: ' . $e->getMessage());
        }
    }
    
    /**
     * Get jobsheet data for DataTables.
     */
    public function getJobsheetData(Request $request)
    {
        try {
            $user = auth()->user();
            $dmcId = null;
            
            // Determine DMC ID based on user role
            if (in_array($user->role_id, [11, 35, 78, 120, 130, 132, 133, 135, 136, 137, 138])) {
                if($user->role_id == 11 || $user->role_id == 20){
                    $dmcId = $user->userId;
                }
                elseif($user->role_id == 35 || $user->role_id == 130 || $user->role_id == 132 || $user->role_id == 133 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138){
                    $dmcId = $user->created_by;
                }
                elseif($user->role_id == 78){
                    $operation_head = User::where('userId', $user->created_by)->first();
                    $dmcId = $operation_head ? $operation_head->created_by : null;
                }
                elseif($user->role_id == 120){
                    $operation_manager = User::where('userId', $user->created_by)->first();
                    $operation_head = $operation_manager ? User::where('userId', $operation_manager->created_by)->first() : null;
                    $dmcId = $operation_head ? $operation_head->created_by : null;
                }
            }
            
            // Start with a base query - only join tables that exist and have proper relationships
            $query = Jobsheet::select(
                'jobsheets.*',
                'tours.display_id as tour_display_id',
                'users.name as dmc_name'
            )
            ->leftJoin('tours', \DB::raw('CAST(jobsheets.tour_id AS VARCHAR)'), '=', \DB::raw('CAST(tours.tour_id AS VARCHAR)'))
            ->leftJoin('users', \DB::raw('CAST(jobsheets.dmc_id AS VARCHAR)'), '=', \DB::raw('CAST(users."userId" AS VARCHAR)'));
            
            // Apply filters
            if ($dmcId) {
                $query->where('jobsheets.dmc_id', $dmcId);
            } else if (in_array($user->role_id, [10, 25, 62, 110])) {
                // For Master DMC and related roles, show jobsheets for all DMCs under them
                if ($user->role_id == 10) {
                    // Master DMC
                    $dmc_ids = User::where('master_dmc_id', $user->userId)
                        ->where('role_id', 11)
                        ->pluck('userId')
                        ->toArray();
                } else if ($user->role_id == 25) {
                    // Product Head
                    $master_dmc_id = $user->created_by;
                    $dmc_ids = User::where('master_dmc_id', $master_dmc_id)
                        ->where('role_id', 11)
                        ->pluck('userId')
                        ->toArray();
                } else if ($user->role_id == 62) {
                    // Product Manager
                    $product_head = User::where('userId', $user->created_by)->first();
                    $master_dmc_id = $product_head ? $product_head->created_by : null;
                    $dmc_ids = User::where('master_dmc_id', $master_dmc_id)
                        ->whereIn('role_id', [11,20])
                        ->pluck('userId')
                        ->toArray();
                } else if ($user->role_id == 110) {
                    // Product Executive
                    $product_manager = User::where('userId', $user->created_by)->first();
                    $product_head = $product_manager ? User::where('userId', $product_manager->created_by)->first() : null;
                    $master_dmc_id = $product_head ? $product_head->created_by : null;
                    $dmc_ids = User::where('master_dmc_id', $master_dmc_id)
                        ->where('role_id', 11)
                        ->pluck('userId')
                        ->toArray();
                }
                
                if (!empty($dmc_ids)) {
                    $query->whereIn('jobsheets.dmc_id', $dmc_ids);
                }
            }
            
            // Only filter by date if provided
            if ($request->filled('date')) {
                $query->where('jobsheets.date', $request->date);
            }
            
            // Get the results
            $jobsheets = $query->orderBy('jobsheets.created_at', 'desc')->get();
            
            // Format data for DataTables
            $formattedData = $jobsheets->map(function($jobsheet) {
                $data = json_decode($jobsheet->data, true) ?? [];
                
                // Extract data from JSON column
                $pickup = $data['pickup'] ?? 'N/A';
                $dropoff = $data['dropoff'] ?? 'N/A';
                $type = $data['type'] ?? 'N/A';
                $serviceType = $data['service_type'] ?? 'N/A';
                $journeyTime = $data['journey_time'] ?? 'N/A';
                
                // Get driver, vehicle, guide info from JSON data
                $driverName = $data['driver_name'] ?? 'Not Assigned';
                $vehicleName = $data['vehicle_name'] ?? 'Not Assigned';
                $guideName = $data['guide_name'] ?? 'Not Assigned';
                
                return [
                    'jobsheet_id' => $jobsheet->jobsheet_id,
                    'tour_id' => $jobsheet->tour_display_id ?? $jobsheet->tour_id,
                    'date' => $jobsheet->date,
                    'type' => $type,
                    'service_type' => $serviceType,
                    'journey_time' => $journeyTime,
                    'pickup' => $pickup,
                    'dropoff' => $dropoff,
                    'driver' => $driverName,
                    'vehicle' => $vehicleName,
                    'guide' => $guideName,
                    'actions' => '
                        <button class="btn btn-sm btn-info view-details" data-id="'.$jobsheet->jobsheet_id.'">
                            <i class="fas fa-eye"></i> View
                        </button>
                    ',
                    'created_at' => $jobsheet->created_at->format('d-m-Y')
                ];
            });
            
            return response()->json([
                'draw' => $request->input('draw', 1),
                'recordsTotal' => count($formattedData),
                'recordsFiltered' => count($formattedData),
                'data' => $formattedData
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in getJobsheetData: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching jobsheet data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get jobsheet details by ID.
     */
    public function getJobsheetDetails($id)
    {
        try {
            $jobsheet = Jobsheet::select(
                'jobsheets.*',
                'tours.display_id as tour_display_id',
                'users.name as dmc_name'
            )
            ->leftJoin('tours', \DB::raw('CAST(jobsheets.tour_id AS VARCHAR)'), '=', \DB::raw('CAST(tours.tour_id AS VARCHAR)'))
            ->leftJoin('users', \DB::raw('CAST(jobsheets.dmc_id AS VARCHAR)'), '=', \DB::raw('CAST(users."userId" AS VARCHAR)'))
            ->where('jobsheets.jobsheet_id', $id)
            ->first();
            
            if (!$jobsheet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jobsheet not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $jobsheet
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in getJobsheetDetails: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching jobsheet details: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Export jobsheets to Excel.
     */
    public function exportJobsheets(Request $request)
    {
        try {
            $user = auth()->user();
            $dmcId = null;
            
            // Determine DMC ID based on user role
            if (in_array($user->role_id, [11, 35, 78, 120, 130, 132, 133, 135, 136, 137, 138])) {
                if($user->role_id == 11 || $user->role_id == 20){
                    $dmcId = $user->userId;
                }
                elseif($user->role_id == 35 || $user->role_id == 130 || $user->role_id == 132 || $user->role_id == 133 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138){
                    $dmcId = $user->created_by;
                }
                elseif($user->role_id == 78){
                    $operation_head = User::where('userId', $user->created_by)->first();
                    $dmcId = $operation_head ? $operation_head->created_by : null;
                }
                elseif($user->role_id == 120){
                    $operation_manager = User::where('userId', $user->created_by)->first();
                    $operation_head = $operation_manager ? User::where('userId', $operation_manager->created_by)->first() : null;
                    $dmcId = $operation_head ? $operation_head->created_by : null;
                }
            }
            
            // Build the query
            $query = Jobsheet::select(
                'jobsheets.*',
                'tours.display_id as tour_display_id',
                'drivers.name as driver_name',
                'vehicles.vehicle_name',
                'guides.name as guide_name',
                'users.name as dmc_name'
            )
            ->leftJoin('tours', 'jobsheets.tour_id', '=', 'tours.tour_id')
            ->leftJoin('drivers', 'jobsheets.driver_id', '=', 'drivers.driver_id')
            ->leftJoin('vehicles', 'jobsheets.vehicle_id', '=', 'vehicles.vehicle_id')
            ->leftJoin('guides', 'jobsheets.guide_id', '=', 'guides.guide_id')
            ->leftJoin('users', 'jobsheets.dmc_id', '=', 'users.userId');
            
            // Apply filters
            if ($dmcId) {
                $query->where('jobsheets.dmc_id', $dmcId);
            } else if (in_array($user->role_id, [10, 25, 62, 110])) {
                // For Master DMC and related roles, show jobsheets for all DMCs under them
                if ($user->role_id == 10) {
                    // Master DMC
                    $dmc_ids = User::where('master_dmc_id', $user->userId)
                        ->where('role_id', 11)
                        ->pluck('userId')
                        ->toArray();
                } else if ($user->role_id == 25) {
                    // Product Head
                    $master_dmc_id = $user->created_by;
                    $dmc_ids = User::where('master_dmc_id', $master_dmc_id)
                        ->where('role_id', 11)
                        ->pluck('userId')
                        ->toArray();
                } else if ($user->role_id == 62) {
                    // Product Manager
                    $product_head = User::where('userId', $user->created_by)->first();
                    $master_dmc_id = $product_head ? $product_head->created_by : null;
                    $dmc_ids = User::where('master_dmc_id', $master_dmc_id)
                        ->where('role_id', 11)
                        ->pluck('userId')
                        ->toArray();
                } else if ($user->role_id == 110) {
                    // Product Executive
                    $product_manager = User::where('userId', $user->created_by)->first();
                    $product_head = $product_manager ? User::where('userId', $product_manager->created_by)->first() : null;
                    $master_dmc_id = $product_head ? $product_head->created_by : null;
                    $dmc_ids = User::where('master_dmc_id', $master_dmc_id)
                        ->where('role_id', 11)
                        ->pluck('userId')
                        ->toArray();
                }
                
                if (!empty($dmc_ids)) {
                    $query->whereIn('jobsheets.dmc_id', $dmc_ids);
                }
            }
            
            if ($request->filled('date')) {
                $query->where('jobsheets.date', $request->date);
            }
            
            // Get the results
            $jobsheets = $query->orderBy('jobsheets.created_at', 'desc')->get();
            
            // Format data for Excel export
            $exportData = $jobsheets->map(function($jobsheet) {
                $data = json_decode($jobsheet->data, true);
                $pickup = $data['pickup'] ?? 'N/A';
                $dropoff = $data['dropoff'] ?? 'N/A';
                
                return [
                    'Jobsheet ID' => $jobsheet->jobsheet_id,
                    'Tour ID' => $jobsheet->tour_display_id ?? $jobsheet->tour_id,
                    'Date' => $jobsheet->date,
                    'Type' => $jobsheet->type,
                    'Service Type' => $jobsheet->service_type,
                    'Journey Time' => $jobsheet->journey_time,
                    'Pickup Location' => $pickup,
                    'Dropoff Location' => $dropoff,
                    'Driver' => $jobsheet->driver_name ?? 'Not Assigned',
                    'Vehicle' => $jobsheet->vehicle_name ?? 'Not Assigned',
                    'Guide' => $jobsheet->guide_name ?? 'Not Assigned',
                    'DMC' => $jobsheet->dmc_name ?? 'N/A',
                    'Created At' => $jobsheet->created_at
                ];
            })->toArray();
            
            return response()->json([
                'success' => true,
                'data' => $exportData
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in exportJobsheets: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error exporting jobsheets: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get all tours for filter dropdown.
     */
    public function getAllTours()
    {
        try {
            $user = auth()->user();
            $dmcId = null;
            
            // Determine DMC ID based on user role
            if (in_array($user->role_id, [11, 35, 78, 120, 130, 132, 133, 135, 136, 137, 138])) {
                if ($user->role_id == 11 || $user->role_id == 20) {
                    $dmcId = $user->userId;
                }
                elseif ($user->role_id == 35 || $user->role_id == 130 || $user->role_id == 132 || $user->role_id == 133 || $user->role_id == 135 || $user->role_id == 136 || $user->role_id == 137 || $user->role_id == 138) {
                    $dmcId = $user->created_by;
                }
                elseif ($user->role_id == 78) {
                    $operation_head = User::where('userId', $user->created_by)->first();
                    $dmcId = $operation_head ? $operation_head->created_by : null;
                }
                elseif ($user->role_id == 120) {
                    $operation_manager = User::where('userId', $user->created_by)->first();
                    $operation_head = $operation_manager ? User::where('userId', $operation_manager->created_by)->first() : null;
                    $dmcId = $operation_head ? $operation_head->created_by : null;
                }
            }
            
            // Start with base query
            $query = Tour::select('tours.tour_id', 'tours.display_id');
            
            // If DMC ID is found, filter tours by orders containing the DMC ID
            if ($dmcId) {
                $orderTypes = ['entry_port', 'exit_port', 'travel_point', 'travel_hourly', 'guide'];
                
                $query->join('orders', 'tours.tour_id', '=', 'orders.tour_id')
                      ->whereIn('orders.type', $orderTypes)
                      ->whereNotNull('orders.data')
                      ->distinct();
                
                // Get all tour IDs first
                $allTourIds = $query->pluck('tours.tour_id')->toArray();
                
                // Then filter tours that have orders with this DMC ID
                $filteredTourIds = [];
                foreach ($allTourIds as $tourId) {
                    $orders = Order::where('tour_id', $tourId)
                        ->whereIn('type', $orderTypes)
                        ->whereNotNull('data')
                        ->get();
                    
                    foreach ($orders as $order) {
                        $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
                        
                        if (is_array($orderData)) {
                            // Check if it's an array of items or single item
                            $items = isset($orderData[0]) ? $orderData : [$orderData];
                            
                            foreach ($items as $item) {
                                if (is_array($item) && 
                                    (isset($item['dmc_id']) && $item['dmc_id'] == $dmcId) || 
                                    (isset($item['dmc_Id']) && $item['dmc_Id'] == $dmcId)) {
                                    $filteredTourIds[] = $tourId;
                                    break 2; // Break both loops once we find a match
                                }
                            }
                        }
                    }
                }
                
                // Get the filtered tours
                $tours = Tour::whereIn('tour_id', $filteredTourIds)
                    ->select('tour_id', 'display_id')
                    ->get();
            } else {
                // If no DMC ID or admin user, get all tours
                $tours = $query->get();
            }
            
            return response()->json([
                'success' => true,
                'tours' => $tours
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching tours: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching tours: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get orders by date without requiring a tour ID
     */
    public function getOrdersByDate($date, Request $request)
    {
        try {
            $user = auth()->user();
            $dmcId = null;
            $type = $request->query('type', 'driver'); // Default to driver orders, can be 'guide'
            // Determine the DMC ID based on user role
            if (in_array($user->role_id, [11, 20, 34, 65, 66, 99, 108, 124, 128, 131, 132, 134, 135, 137, 138])) {
                if($user->role_id == 11 || $user->role_id == 20){
                    $dmcId = $user->userId;
                }
                elseif($user->role_id == 34 || in_array($user->role_id, [128, 131, 132, 134, 135, 137, 138])){
                    $dmcId = $user->created_by;
                }
                elseif($user->role_id == 65 || $user->role_id == 66 || $user->role_id == 124){
                    $operation_head = User::where('userId', $user->created_by)->first();
                    $dmcId = $operation_head ? $operation_head->created_by : null;
                }
                elseif($user->role_id == 99 || $user->role_id == 108){
                    $operation_manager = User::where('userId', $user->created_by)->first();
                    $operation_head = $operation_manager ? User::where('userId', $operation_manager->created_by)->first() : null;
                    $dmcId = $operation_head ? $operation_head->created_by : null;
                }
            }
            
            // Define the order types we want to filter by
            $orderTypes = $type === 'guide' 
                ? ['guide', 'dayguide', 'halfguide'] 
                : ['entry_port', 'exit_port', 'travel_point', 'local_transport', 'travel_hourly'];
            
            // Get drivers/guides and vehicles for assignment dropdowns
            if ($type === 'guide') {
                $guides = Guide::where('dmc_id', $dmcId)->with('languages')->get();
            } else {
                $drivers = Driver::where('dmc_id', $dmcId)->get();
                $vehicles = Vehicle::where('dmc_id', $dmcId)->get();
            }
            // Query orders for the specified date
            $orders = [];
            if (!is_null($dmcId)) {
                // If DMC ID is available, filter by both DMC and date
                if($type === 'guide'){
                $orders = Order::select('orders.*', 'tours.id as tour_id_numeric', 'tours.tour_id', 'tours.display_id')
                    ->leftJoin('tours', 'orders.tour_id', '=', 'tours.tour_id')
                    ->whereIn('orders.type', $orderTypes)
                    ->whereRaw("data->0->>'dmc_Id' = ?", [$dmcId])
                    ->whereRaw("data->0->>'pickupdate' = ?", [$date])
                    ->whereNotNull('orders.tour_id')
                    ->whereIn('tours.tour_status', ['Confirmed', 'Definite', 'Actual'])
                    ->get();
                }
                else{
                    $orders = Order::select('orders.*', 'tours.id as tour_id_numeric', 'tours.tour_id', 'tours.display_id')
                    ->leftJoin('tours', 'orders.tour_id', '=', 'tours.tour_id')
                    ->whereIn('orders.type', $orderTypes)
                    ->whereRaw("data->0->>'dmc_id' = ?", [$dmcId])
                    ->whereRaw("data->0->>'pickupdate' = ?", [$date])
                    ->whereNotNull('orders.tour_id')
                    ->whereIn('tours.tour_status', ['Confirmed', 'Definite', 'Actual'])
                    ->get();
                }
            } else {
                // Otherwise just filter by date
                $orders = Order::select('orders.*', 'tours.id as tour_id_numeric', 'tours.tour_id', 'tours.display_id')
                    ->leftJoin('tours', 'orders.tour_id', '=', 'tours.tour_id')
                    ->whereIn('orders.type', $orderTypes)
                    ->whereRaw("data->0->>'pickupdate' = ?", [$date])
                    ->whereNotNull('orders.tour_id')
                    ->whereIn('tours.tour_status', ['Confirmed', 'Definite', 'Actual'])
                    ->get();
            }
            
            // Fetch assigned drivers/guides for each order and add zone information
            if ($type === 'guide') {
                $orders->map(function($order) use ($dmcId, $date) {
                    // Get order data
                    $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
                    $dataItem = is_array($orderData) && isset($orderData[0]) ? $orderData[0] : [];
                    
                    // Check if there's an assignment in the jobsheets table
                    $jobsheet = Jobsheet::where('date', $date)
                        ->where('type', $order->type)
                        ->where('service_type', $order->type) // For guides, service_type is same as type
                        ->where('journey_time', $dataItem['entrytime'] ?? null)
                        ->where('order_id', $order->booking_id)
                        ->first();
                    
                    // Get OrderGuide from order data if available
                    $orderGuide = null;
                    if (isset($dataItem['guide_id'])) {
                        $orderGuide = Guide::select('guide_id', 'name', 'email', 'government_license_no')
                            ->where('guide_id', $dataItem['guide_id'])
                            ->with('languages')
                            ->first();
                    }
                    $order->OrderGuide = $orderGuide;
                    
                    // Attach guide info from jobsheet
                    if ($jobsheet) {
                        $order->assigned_guide_id = $jobsheet->guide_id;
                        $order->guide = $jobsheet->guide_id ? Guide::where('guide_id', $jobsheet->guide_id)->with('languages')->first() : null;
                    } else {
                        $order->assigned_guide_id = null;
                        $order->guide = null;
                    }
                    
                    // Add zone information for pickup and dropoff
                    if (is_array($orderData) && isset($orderData[0])) {
                        $order->pickup_zone = $this->getZoneForLocation($dataItem['entrypickup'] ?? '', $dmcId);
                        $order->dropoff_zone = $this->getZoneForLocation($dataItem['entrydropoff'] ?? '', $dmcId);
                    }
                    
                    return $order;
                });
                return response()->json([
                    'success' => true,
                    'data' => $orders,
                    'guides' => $guides ?? []
                ]);
            } else {
                $orders->map(function($order) use ($dmcId, $date) {
                    // Get order data
                    $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
                    $dataItem = is_array($orderData) && isset($orderData[0]) ? $orderData[0] : [];
                    
                    // Get vehicle from order data
                    $vehicleIdFromOrder = $dataItem['vehicles_id'] ?? null;
                    $vehicleFromOrder = null;
                    $driverFromVehicle = null;
                    
                    if ($vehicleIdFromOrder) {
                        $vehicleFromOrder = Vehicle::where('vehicle_id', $vehicleIdFromOrder)->first();
                        
                        // If vehicle has a driver assigned, get that driver
                        if ($vehicleFromOrder && $vehicleFromOrder->driver_id) {
                            $driverFromVehicle = Driver::where('driver_id', $vehicleFromOrder->driver_id)->first();
                        }
                    }
                    
                    // Check if there's an assignment in the jobsheets table
                    $jobsheet = Jobsheet::where('date', $date)
                        ->where('type', $order->type)
                        ->where('service_type', $dataItem['type'] ?? null)
                        ->where('journey_time', $dataItem['entrytime'] ?? null)
                        ->where('order_id', $order->booking_id)
                        ->first();
                    // Priority: Jobsheet assignment > Vehicle from order data
                    if ($jobsheet) {
                        $order->assigned_driver_id = $jobsheet->driver_id;
                        $order->assigned_vehicle_id = $jobsheet->vehicle_id;
                        $order->driver = $jobsheet->driver_id ? Driver::where('driver_id', $jobsheet->driver_id)->first() : null;
                        $order->vehicle = $jobsheet->vehicle_id ? Vehicle::where('vehicle_id', $jobsheet->vehicle_id)->first() : null;
                    } else {
                        // Use vehicle and driver from order data as default
                        $order->assigned_vehicle_id = $vehicleFromOrder ? $vehicleFromOrder->vehicle_id : null;
                        $order->assigned_driver_id = $driverFromVehicle ? $driverFromVehicle->driver_id : null;
                        $order->vehicle = $vehicleFromOrder;
                        $order->driver = $driverFromVehicle;
                    }
                    
                    // Add zone information for pickup and dropoff
                    if (is_array($orderData) && isset($orderData[0])) {
                        $order->pickup_zone = $this->getZoneForLocation($dataItem['entrypickup'] ?? '', $dmcId);
                        $order->dropoff_zone = $this->getZoneForLocation($dataItem['entrydropoff'] ?? '', $dmcId);
                    }
                    
                    return $order;
                });
                return response()->json([
                    'success' => true,
                    'data' => $orders,
                    'drivers' => $drivers ?? [],
                    'vehicles' => $vehicles ?? []
                ]);
            }
            
        } catch (\Exception $e) {
            \Log::error('Error fetching orders by date: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching orders: ' . $e->getMessage()
            ], 500);
        }
    }
}