<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Models\Tour;
use App\Models\Driver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\JobSheet;
use App\Models\Vehicle;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class TodaysBookingsController extends Controller
{
    protected static $transferTypes = [
        'entry_port' => 'Airport Arrival',
        'exit_port' => 'Airport Departure',
        'travel_hourly' => 'Travel Hourly',
        'travel_point' => 'Transfer',
        'local_transport' => 'Point to Point',
    ];

    /**
     * Display Trip Log (today's bookings) for operation-level users.
     * Date defaults to today; optional ?date=Y-m-d to filter.
     */
    public function index(Request $request)
    {
        try{
        $user = Auth::user();
        $operationRoleIds = [34, 128, 131, 132, 134, 135, 137, 138];

        if (!$user || !in_array($user->role_id, $operationRoleIds)) {
            abort(403);
        }

        $dmcId = CommonHelper::getDmcId($user);
        $dateInput = $request->get('date', now()->toDateString());
        $tripDate = Carbon::parse($dateInput)->toDateString();
        $end_date = $request->get('end_date', now()->toDateString());
        $transferLogs = [];
        $attractionLogs = [];
        $restaurantLogs = [];
        $hotelLogs = [];
        if($tripDate > $end_date){
            return redirect()->back()->with('error', 'Start Date must be before End Date')->withInput();
        }

        if ($dmcId) {
            $tours = Tour::with(['booking' => function ($q) {
                $q->whereNull('deleted_at');
            }, 'agent'])
                ->where('dmc_id', $dmcId)
                ->whereDate('check_in_time', '<=', $end_date)
                ->whereDate('check_out_time', '>=', $tripDate)
                ->orderBy('check_in_time')
                ->get();

            $driverIds = $tours->pluck('assign_driver_id')->filter()->unique()->values()->all();
            
            $drivers = collect();
            if (!empty($driverIds)) {
                foreach (Driver::with('user')->whereIn('driver_id', $driverIds)->orWhereIn('id', $driverIds)->get() as $d) {
                    $drivers[$d->driver_id] = $d;
                    if (isset($d->id)) $drivers[$d->id] = $d;
                }
            }

            foreach ($tours as $tour) {
                $guestName = $this->getTourGuestName($tour);
                $referenceBase = $tour->display_id ?? $tour->tour_id;
                $driverName = '—';
                if ($tour->assign_driver_id && isset($drivers[$tour->assign_driver_id])) {
                    $driver = $drivers[$tour->assign_driver_id];
                    $driverName = $driver->user ? ($driver->user->name ?? '—') : '—';
                }

                foreach ($tour->booking as $order) {
                    $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
                    $items = is_array($orderData) && isset($orderData[0]) && is_array($orderData[0])
                        ? $orderData
                        : (is_array($orderData) ? [$orderData] : []);
                        if (isset(self::$transferTypes[$order->type])) {

                            // 🔹 Default driver
                            $driverName = '—';
                        
                            // 🔹 Get driver from JobSheet first
                            $jobsheet = JobSheet::where('order_id', $order->booking_id)->first();
                        
                            if ($jobsheet && $jobsheet->driver_id) {
                                $driver = Driver::where('driver_id', $jobsheet->driver_id)->first();
                            } else {
                        
                                $vehicleId = $orderData[0]['vehicles_id'] ?? null;
                                if ($vehicleId !== null && $vehicleId !== '') {
                                    $vehicle = Vehicle::where('vehicle_id', $vehicleId)->first();
                                    $driver = $vehicle
                                        ? Driver::where('driver_id', $vehicle->driver_id)->first()
                                        : null;
                                } else {
                                    $driver = null;
                                }
                            }
                        
                            if (!empty($driver)) {
                                $driverName = $driver->name . '-' . $driver->license_no;
                            }
                        
                            // 🔹 Now build rows
                            foreach ($items as $item) {
                                if (!is_array($item)) continue;
                                if (!$this->itemMatchesDate($item, $order->type, $tripDate, $end_date)) continue;
                        
                                $row = $this->buildTransferRow(
                                    $order->type,
                                    $item,
                                    $tour,
                                    $referenceBase,
                                    $guestName,
                                    $driverName,
                                    $tripDate
                                );
                        
                                if ($row) {
                                    $transferLogs[] = $row;
                                }
                            }
                        } elseif ($order->type === 'attraction') {
                            
                            if(isset($items[0]['transfer_options']['transfer_required']) && $items[0]['transfer_options']['transfer_required'] == true){
                                
                                $vehicleId = $items[0]['transfer_options']['vehicle_id'] ?? null;
                                $driverName = '—';
                                if ($vehicleId !== null && $vehicleId !== '') {
                                    $vehicle = Vehicle::where('vehicle_id', $vehicleId)->first();
                                    if ($vehicle && $vehicle->driver_id) {
                                        $driver = Driver::where('driver_id', $vehicle->driver_id)->first();
                                        $driverName = $driver ? $driver->name.'-'.$driver->license_no : '—';
                                    }
                                }

                                foreach ($items as $item) {
                                    if (!is_array($item)) continue;
                                    if (!$this->itemMatchesDate($item, 'attraction', $tripDate, $end_date)) continue;
                                    $row = $this->buildTransferRow(
                                        $order->type,
                                        $item,
                                        $tour,
                                        $referenceBase,
                                        $guestName,
                                        $driverName,
                                        $tripDate
                                    );
                                    if ($row) {
                                        $transferLogs[] = $row;
                                    }
                                }
                            }
                        
                        foreach ($items as $item) {
                            if (!is_array($item)) continue;
                            if (!$this->itemMatchesDate($item, 'attraction', $tripDate, $end_date)) continue;
                            $attractionLogs[] = $this->buildAttractionRow($item, $tour, $referenceBase, $guestName, $tripDate);
                        }
                    } elseif ($order->type === 'restaurant') {
                        if(isset($items[0]['transfer_options']['transfer_required']) && $items[0]['transfer_options']['transfer_required'] == true){
                            $vehicleId = $items[0]['transfer_options']['vehicle_id'] ?? null;
                            $driverName = '—';
                            if ($vehicleId !== null && $vehicleId !== '') {
                                $vehicle = Vehicle::where('vehicle_id', $vehicleId)->first();
                                if ($vehicle && $vehicle->driver_id) {
                                    $driver = Driver::where('driver_id', $vehicle->driver_id)->first();
                                    $driverName = $driver ? $driver->name.'-'.$driver->license_no : '—';
                                }
                            }

                            foreach ($items as $item) {
                                if (!is_array($item)) continue;
                                if (!$this->itemMatchesDate($item, 'restaurant', $tripDate, $end_date)) continue;
                                $row = $this->buildTransferRow(
                                    $order->type,
                                    $item,
                                    $tour,
                                    $referenceBase,
                                    $guestName,
                                    $driverName,
                                    $tripDate
                                );
                                if ($row) {
                                    $transferLogs[] = $row;
                                }
                            }
                        }
                        foreach ($items as $item) {
                            if (!is_array($item)) continue;
                            if (!$this->itemMatchesDate($item, 'restaurant', $tripDate, $end_date)) continue;
                            $restaurantLogs[] = $this->buildRestaurantRow($item, $tour, $referenceBase, $guestName, $tripDate);
                        }
                    } elseif ($order->type === 'hotel') {
                        foreach ($items as $item) {
                            if (!is_array($item)) continue;
                            if (!$this->itemMatchesDate($item, 'hotel', $tripDate, $end_date)) continue;
                            $hotelLogs[] = $this->buildHotelRow($item, $tour, $referenceBase, $guestName, $tripDate);
                        }
                    }
                }
            }

            usort($transferLogs, fn($a, $b) => ($a['time'] ?? '') <=> ($b['time'] ?? ''));

            $allLogs = [];
            foreach ($transferLogs as $row) {
                $allLogs[] = array_merge($row, ['log_type' => 'Transfer']);
            }
            foreach ($attractionLogs as $row) {
                $allLogs[] = array_merge($row, ['log_type' => 'Attraction', 'icon' => 'ri-camera-line']);
            }
            foreach ($restaurantLogs as $row) {
                $allLogs[] = array_merge($row, ['log_type' => 'Restaurant', 'icon' => 'ri-restaurant-2-line']);
            }
            foreach ($hotelLogs as $row) {
                $allLogs[] = array_merge($row, ['log_type' => 'Hotel', 'icon' => 'ri-hotel-bed-line']);
            }
            usort($allLogs, fn($a, $b) => ($a['sort_at'] ?? '') <=> ($b['sort_at'] ?? ''));
        } else {
            $allLogs = [];
        }

        return view('bookings.todays', [
            'tripDate' => $tripDate,
            'end_date' => $end_date,
            'transferLogs' => $transferLogs,
            'attractionLogs' => $attractionLogs,
            'restaurantLogs' => $restaurantLogs,
            'hotelLogs' => $hotelLogs,
            'allLogs' => $allLogs ?? [],
        ]);
    }catch(\Exception $e){
        
        return redirect()->back()->with('error', $e->getMessage())->withInput();
    }
    }

    /** Return true if the booking item falls on the given trip date. */
    protected function itemMatchesDate(array $item, string $orderType, string $tripDate, string $end_date): bool
    {
        if (isset(self::$transferTypes[$orderType])) {
            $d = $item['pickupdate'] ?? $item['bookingDate'] ?? $item['exitpickupdate'] ?? null;
            if (is_array($d)) $d = $d[0] ?? null;
            return $d ? (Carbon::parse($d)->toDateString() >= $tripDate && Carbon::parse($d)->toDateString() <= $end_date) : false;
        }
        if ($orderType === 'attraction' || $orderType === 'restaurant') {
            $d = $item['bookingDate'] ?? null;
            if (is_array($d)) $d = $d[0] ?? null;
            return $d ? (Carbon::parse($d)->toDateString() >= $tripDate && Carbon::parse($d)->toDateString() <= $end_date) : false;
        }
        if ($orderType === 'hotel') {
            $dates = $item['bookingDate'] ?? [];
            if (!is_array($dates)) $dates = [$dates];
            foreach ($dates as $d) {
                if ($d && Carbon::parse($d)->toDateString() >= $tripDate && Carbon::parse($d)->toDateString() <= $end_date) return true;
            }
            return false;
        }
        return true;
    }

    protected function getTourGuestName(Tour $tour): string
    {
        $mg = $tour->mainguest;
        if (is_string($mg)) $mg = json_decode($mg, true);
        if (is_array($mg) && !empty($mg['full_name'])) return $mg['full_name'];
        return '—';
    }

    protected function buildTransferRow(string $orderType, array $item, Tour $tour, string $refBase, string $guestName, string $driverName, string $tripDate): array
    {
        if (in_array($orderType, ['attraction', 'restaurant'])) {

            $transfer = $item['transfer_options'] ?? null;
    
            if (!$transfer || empty($transfer['transfer_required'])) {
                return []; // no transfer attached
            }
    
            $date = $item['bookingDate'] ?? $tripDate;
            $time = $transfer['pickup_time'] ?? $item['visitTime'] ?? '—';
    
            $from = $transfer['pickup_location_name'] ?? '—';
            $to   = $transfer['destination']
                    ?? $item['AttractionName']
                    ?? $item['restaurantName']
                    ?? '—';
    
            $pteSic = strtolower($transfer['type'] ?? '') === 'private'
                ? 'PTE'
                : 'SIC';
    
            $adults = (int)($item['adultCount'] ?? 0);
            $child  = (int)($item['childCount'] ?? 0);
            if($orderType == 'attraction'){
                $typeLabel = 'Attraction';
            }
            elseif($orderType == 'restaurant'){
                $typeLabel = 'Restaurant';
            }
            else{
                $typeLabel = 'Transfer';
            }
    
            $icon = 'ri-car-line';
            if($orderType == 'attraction'){
                $icon = 'ri-camera-line';
            }
            elseif($orderType == 'restaurant'){
                $icon = 'ri-restaurant-2-line';
            }
        }
        else{
            $date = $tripDate;
            if (isset($item['pickupdate'])) $date = is_array($item['pickupdate']) ? ($item['pickupdate'][0] ?? $tripDate) : $item['pickupdate'];
            elseif (isset($item['bookingDate'])) $date = is_array($item['bookingDate']) ? ($item['bookingDate'][0] ?? $tripDate) : $item['bookingDate'];
            elseif (isset($item['exitpickupdate'])) $date = is_array($item['exitpickupdate']) ? ($item['exitpickupdate'][0] ?? $tripDate) : $item['exitpickupdate'];

            $time = $item['entrytime'] ?? $item['exittime'] ?? $item['pickup_time'] ?? '—';
            if ($time !== '—' && preg_match('/^\d{4}-\d{2}-\d{2}/', $time)) $time = Carbon::parse($time)->format('H:i');

            $from = $item['entrypickup'] ?? $item['exitpickup'] ?? $item['pickup_location'] ?? $item['entrypickup'] ?? '—';
            $to = $item['entrydropoff'] ?? $item['exitdropoff'] ?? $item['drop_location'] ?? $item['entrydropoff'] ?? '—';

            $typeLabel = self::$transferTypes[$orderType] ?? 'Transfer';
            $pteSic = $item['type'] ?? 'PTE';
            if (stripos($pteSic, 'sic') !== false || stripos($pteSic, 'seat') !== false) $pteSic = 'SIC';
            else $pteSic = 'PTE';

            $adults = (int)($item['adults'] ?? $item['adultCount'] ?? 0);
            $child = (int)($item['children'] ?? $item['childCount'] ?? 0);

            $icon = match($orderType) {
                'entry_port' => 'ri-flight-land-line',
                'exit_port' => 'ri-flight-takeoff-line',
                'travel_hourly' => 'ri-time-line',
                'travel_point', 'local_transport' => 'ri-car-line',
                default => 'ri-route-line',
            };
        }

        $timeForSort = $time;
        if ($timeForSort === '—' || !preg_match('/^\d{1,2}:\d{2}/', (string)$timeForSort)) {
            $timeForSort = '00:00';
        }
        $sortAt = Carbon::parse($date)->format('Y-m-d') . ' ' . $timeForSort;

        return [
            'icon' => $icon,
            'date' => Carbon::parse($date)->format('d M \'y'),
            'time' => $time,
            'reference_no' => $refBase ,
            'guest' => $guestName,
            'transfer_type' => $typeLabel,
            'from' => $from,
            'to' => $to,
            'type' => $pteSic,
            'adults' => $adults,
            'child' => $child,
            'driver' => $driverName,
            'sort_at' => $sortAt,
        ];
    }

    protected function buildAttractionRow(array $item, Tour $tour, string $refBase, string $guestName, string $tripDate): array
    {
        $name = $item['AttractionName'] ?? $item['attractionName'] ?? '—';
        $date = $item['bookingDate'] ?? $tripDate;
        if (is_array($date)) $date = $date[0] ?? $tripDate;
        $time = $item['visitTime'] ?? '—';
        $ticket = $item['ticketName'] ?? '—';
        $adults = (int)($item['adultCount'] ?? 0);
        $child = (int)($item['childCount'] ?? 0);
        $ref = $refBase;
        $timeForSort = $time !== '—' && preg_match('/^\d{1,2}:\d{2}/', (string)$time) ? $time : '00:00';
        $sortAt = Carbon::parse($date)->format('Y-m-d') . ' ' . $timeForSort;
        return [
            'date' => Carbon::parse($date)->format('d M \'y'),
            'time' => $time,
            'reference_no' => $ref,
            'guest' => $guestName,
            'name' => $name,
            'ticket_type' => $ticket,
            'adults' => $adults,
            'child' => $child,
            'sort_at' => $sortAt,
        ];
    }

    protected function buildRestaurantRow(array $item, Tour $tour, string $refBase, string $guestName, string $tripDate): array
    {
        $name = $item['restaurant_name'] ?? $item['name'] ?? '—';
        $date = $item['bookingDate'] ?? $tripDate;
        if (is_array($date)) $date = $date[0] ?? $tripDate;
        $time = $item['meal_time'] ?? $item['visitTime'] ?? '—';
        $meal = $item['meal_name'] ?? $item['meal_type'] ?? '—';
        $adults = (int)($item['adultCount'] ?? $item['adults'] ?? 0);
        $child = (int)($item['childCount'] ?? $item['children'] ?? 0);
        $ref = $refBase;
        $timeForSort = $time !== '—' && preg_match('/^\d{1,2}:\d{2}/', (string)$time) ? $time : '00:00';
        $sortAt = Carbon::parse($date)->format('Y-m-d') . ' ' . $timeForSort;
        return [
            'date' => Carbon::parse($date)->format('d M \'y'),
            'time' => $time,
            'reference_no' => $ref,
            'guest' => $guestName,
            'name' => $name,
            'meal_type' => $meal,
            'adults' => $adults,
            'child' => $child,
            'sort_at' => $sortAt,
        ];
    }

    protected function buildHotelRow(array $item, Tour $tour, string $refBase, string $guestName, string $tripDate): array
    {
        $name = $item['hotelDetails']['hotel_name'] ?? $item['hotel_name'] ?? '—';
        $bookingDates = $item['bookingDate'] ?? [];
        if (!is_array($bookingDates)) $bookingDates = [$bookingDates];
        $checkIn = !empty($bookingDates) ? Carbon::parse($bookingDates[0])->format('d M \'y') : '—';
        $checkOut = count($bookingDates) > 1 ? Carbon::parse(end($bookingDates))->format('d M \'y') : '—';
        $rooms = $item['No_of_rooms'] ?? count($item['rooms'] ?? []) ?: '—';
        $ref = $refBase;
        $firstDate = !empty($bookingDates) ? $bookingDates[0] : $tripDate;
        $sortAt = Carbon::parse($firstDate)->format('Y-m-d') . ' 00:00';
        return [
            'date' => $checkIn,
            'time' => '—',
            'reference_no' => $ref,
            'guest' => $guestName,
            'name' => $name,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'rooms' => $rooms,
            'sort_at' => $sortAt,
        ];
    }
}
