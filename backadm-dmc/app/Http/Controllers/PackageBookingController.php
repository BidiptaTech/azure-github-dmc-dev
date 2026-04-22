<?php

namespace App\Http\Controllers;

use App\Helpers\CommonHelper;
use App\Models\Agent;
use App\Models\Package;
use App\Models\PackageBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Agency;
use App\Models\Hotel;
use App\Models\Attraction;
use App\Models\Guide;
use App\Models\Restaurant;
use App\Models\Port;
use App\Models\Bed;

class PackageBookingController extends Controller
{
    public function create($package_id = null)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $dmcId = CommonHelper::getDmcId($user);
        $prefilledPackageId = null;
        if (!empty($package_id)) {
            try {
                $prefilledPackageId = Crypt::decrypt($package_id);
            } catch (\Throwable $e) {
                $prefilledPackageId = $package_id;
            }
        }
        $agencies = Agency::whereJsonContains('dmc_id', (int)$dmcId)->get();

        $agents = Agent::whereIn('agency_id', $agencies->pluck('agency_id'))
            ->orderBy('name')
            ->select('agent_id', 'name', 'company_name')
            ->get();

        return view('package.package-booking', [
            'agencies' => $agencies,
            'prefilledPackageId' => $prefilledPackageId,
        ]);
    }

    public function getAgentsByAgency(Request $request)
    {
        try {
            $agencyId = $request->query('agency_id');
            if (!$agencyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Agency ID is required',
                ], 400);
            }
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }
            $dmcId = CommonHelper::getDmcId($user);
            
            $agents = Agent::where('agency_id', $agencyId)
                ->orderBy('name')
                ->select('agent_id', 'name', 'company_name')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Agents fetched successfully',
                'agents' => $agents,
            ]);
        } catch (\Throwable $e) {
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching agents',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    public function filterPackages(Request $request)
    {
        $validated = $request->validate([
            'travel_start_date' => 'required|date',
            'travel_end_date' => 'required|date|after_or_equal:travel_start_date',
            'pax_count' => 'required|integer|min:1',
        ]);

        $startDate = Carbon::parse($validated['travel_start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['travel_end_date'])->startOfDay();
        $durationDays = $startDate->diffInDays($endDate) + 1;
        $totalPax = (int) $validated['pax_count'];

        $packages = Package::query()
            ->whereDate('start_date', '<=', $startDate->toDateString())
            ->whereDate('expire_date', '>=', $endDate->toDateString())
            ->where('duration_days', $durationDays)
            ->orderBy('title')
            ->get(['package_id', 'title', 'destination', 'city', 'duration_days', 'max_pax', 'start_date', 'expire_date'])
            ->map(function ($package) {
                return [
                    'package_id' => $package->package_id,
                    'title' => (string) $package->title,
                    'destination' => (string) $package->destination,
                    'city' => (string) $package->city,
                    'duration_days' => (int) ($package->duration_days ?? 0),
                    'max_pax' => (int) ($package->max_pax ?? 0),
                    'start_date' => (string) $package->start_date,
                    'expire_date' => (string) $package->expire_date,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'duration_days' => $durationDays,
            'total_pax' => $totalPax,
            'packages' => $packages,
        ]);
    }

    public function packageDetails($packageId)
    {
        $package = Package::where('package_id', $packageId)->first();
        if (!$package) {
            return response()->json(['success' => false, 'message' => 'Package not found.'], 404);
        }

        $user = Auth::user();
        $dmcId = CommonHelper::getDmcId($user);
        
        $arrivalData = $this->parseJsonField($package->arrival_data);
        $departureData = $this->parseJsonField($package->departure_data);

        if (is_array($arrivalData)) {
            $pickupPortId = (string) ($arrivalData['pickup_port_id'] ?? '');
            $dropoffHotelId = (string) ($arrivalData['dropoff_hotel_id'] ?? '');
            $arrivalData['pickup_port_name'] = $this->resolvePortName($pickupPortId);
            $arrivalData['dropoff_hotel_name'] = $this->resolveHotelName($dropoffHotelId);
        }
        if (is_array($departureData)) {
            $pickupHotelId = (string) ($departureData['pickup_hotel_id'] ?? '');
            $dropoffPortId = (string) ($departureData['dropoff_port_id'] ?? '');
            $departureData['pickup_hotel_name'] = $this->resolveHotelName($pickupHotelId);
            $departureData['dropoff_port_name'] = $this->resolvePortName($dropoffPortId);
        }

        return response()->json([
            'success' => true,
            'package' => [
                'package_id' => $package->package_id,
                'title' => (string) $package->title,
                'destination' => (string) $package->destination,
                'city' => (string) $package->city,
                'selected_hotels' => $this->parseJsonField($package->selected_hotels),
                'selected_attractions' => $this->parseJsonField($package->selected_attractions),
                'selected_guides' => $this->parseJsonField($package->selected_guide),
                'selected_restaurants' => $this->parseJsonField($package->selected_restaurants),
                'arrival_data' => $arrivalData,
                'departure_data' => $departureData,
                'transfer_data' => $this->parseJsonField($package->transfer_data),
                'price_adult' => (float) ($package->price_adult ?? 0),
                'price_child' => (float) ($package->price_child ?? 0),
                'duration_days' => (int) ($package->duration_days ?? 1),
                'price_data' => $this->parseJsonField($package->price_data),
            ],
        ]);
    }

    private function resolvePortName($portId): ?string
    {
        
        $portId = trim((string) $portId);
        if ($portId === '') {
            return null;
        }

        $port = Port::where('port_id', $portId)
            ->orWhere('id', $portId)
            ->first();

        if (!$port) {
            return null;
        }

        return $port->port_name ?? $port->name ?? null;
    }

    private function resolveHotelName($hotelId): ?string
    {
        $hotelId = trim((string) $hotelId);
        if ($hotelId === '') {
            return null;
        }

        $hotel = Hotel::where('hotel_unique_id', $hotelId)
            ->first();

        if (!$hotel) {
            return null;
        }

        return $hotel->name ?? $hotel->hotel_name ?? null;
    }

    public function bedOptions(Request $request)
    {
        $rawBedIds = (string) $request->query('bed_ids', '');
        $bedIds = collect(explode(',', $rawBedIds))
            ->map(fn ($v) => trim((string) $v))
            ->filter(fn ($v) => $v !== '')
            ->values();

        if ($bedIds->isEmpty()) {
            return response()->json(['success' => true, 'options' => []]);
        }

        $seedBeds = Bed::whereIn('bed_id', $bedIds->all())
            ->orWhereIn('id', $bedIds->all())
            ->get(['id', 'bed_id', 'room_id', 'room_type', 'extra_bed']);

        if ($seedBeds->isEmpty()) {
            return response()->json(['success' => true, 'options' => []]);
        }

        $roomIds = $seedBeds->pluck('room_id')->filter()->unique()->values();
        $roomBeds = Bed::whereIn('room_id', $roomIds->all())
            ->get(['id', 'bed_id', 'room_id', 'room_type', 'extra_bed']);

        $bedsByRoom = [];
        foreach ($roomBeds as $bed) {
            $roomKey = (string) ($bed->room_id ?? '');
            if ($roomKey === '') {
                continue;
            }
            $bedsByRoom[$roomKey] ??= [];
            $bedsByRoom[$roomKey][] = [
                'id' => $bed->id,
                'bed_id' => (string) ($bed->bed_id ?? $bed->id),
                'room_type' => (string) ($bed->room_type ?? ''),
                'extra_bed' => (int) ($bed->extra_bed ?? 0) === 1,
            ];
        }

        $optionsByRequestedBedId = [];
        foreach ($seedBeds as $seed) {
            $roomKey = (string) ($seed->room_id ?? '');
            if ($roomKey === '') {
                continue;
            }
            $seedBedId = (string) ($seed->bed_id ?? $seed->id);
            if ($seedBedId !== '') {
                $optionsByRequestedBedId[$seedBedId] = $bedsByRoom[$roomKey] ?? [];
            }
            $seedId = (string) ($seed->id ?? '');
            if ($seedId !== '') {
                $optionsByRequestedBedId[$seedId] = $bedsByRoom[$roomKey] ?? [];
            }
        }

        return response()->json(['success' => true, 'options' => $optionsByRequestedBedId]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|string',
            'travel_start_date' => 'required|date',
            'travel_end_date' => 'required|date|after_or_equal:travel_start_date',
            'pax_count' => 'required|integer|min:1',
            'agent_id' => 'nullable',
            'selected_hotels' => 'nullable|string',
            'selected_attractions' => 'nullable|string',
            'selected_guides' => 'nullable|string',
            'selected_restaurants' => 'nullable|string',
            'arrival_data' => 'nullable|string',
            'departure_data' => 'nullable|string',
            'transfer_data' => 'nullable|string',
            'supplementary_data' => 'nullable|string',
            'price_data' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $package = Package::where('package_id', $validated['package_id'])->first();
        if (!$package) {
            return back()->withInput()->with('error', 'Selected package not found.');
        }

        $selectedHotels = $this->parseJsonField($validated['selected_hotels'] ?? '[]');
        $selectedAttractions = $this->parseJsonField($validated['selected_attractions'] ?? '[]');
        $selectedGuides = $this->parseJsonField($validated['selected_guides'] ?? '[]');
        $selectedRestaurants = $this->parseJsonField($validated['selected_restaurants'] ?? '[]');
        $arrivalData = $this->parseJsonField($validated['arrival_data'] ?? '{}');
        $departureData = $this->parseJsonField($validated['departure_data'] ?? '{}');
        $transferData = $this->parseJsonField($validated['transfer_data'] ?? '[]');
        $supplementaryData = $this->parseJsonField($validated['supplementary_data'] ?? '{}');
        $clientPriceData = $this->parseJsonField($validated['price_data'] ?? '{}');

        $totalPax = (int) ($validated['pax_count'] ?? 0);

        $startDate = Carbon::parse($validated['travel_start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['travel_end_date'])->startOfDay();
        $duration = $startDate->diffInDays($endDate) + 1;
        if ($package->start_date>$startDate && $package->expire_date<$endDate) {
            return back()->withInput()->with('error', 'Selected package does not match the chosen date duration. Start date: '.$package->start_date.' End date: '.$package->expire_date.' Selected start date: '.$startDate.' Selected end date: '.$endDate);
        }

        $ceilToFive = function ($n) {
            $num = (float) $n;
            if (!is_finite($num)) {
                return 0.0;
            }
            return (float) (ceil($num / 5) * 5);
        };

        $packagePriceData = $this->parseJsonField($package->price_data ?? '{}');
        $markupType = strtolower((string) ($packagePriceData['markup_type'] ?? 'flat'));
        $markupAmount = (float) ($packagePriceData['markup_amount'] ?? 0);

        $clientTotal = (float) ($clientPriceData['total_price'] ?? 0);
        $totalPrice = $ceilToFive($clientTotal);

        if ($markupAmount > 0) {
            $finalRaw = $markupType === 'percentage'
                ? ($totalPrice + ($totalPrice * $markupAmount / 100))
                : ($totalPrice + $markupAmount);
        } else {
            $finalRaw = $totalPrice;
        }
        $finalPrice = $ceilToFive($finalRaw);

        $priceDataPayload = [
            'total_price' => $totalPrice,
            'final_price' => $finalPrice,
            'markup_type' => $markupType,
            'markup_amount' => $markupAmount,
        ];

        $itineraryDates = [];
        for ($i = 0; $i < $duration; $i++) {
            $itineraryDates[] = [
                'day' => $i + 1,
                'date' => $startDate->copy()->addDays($i)->format('Y-m-d'),
            ];
        }

        try {
            DB::beginTransaction();

            $lastBooking = PackageBooking::withTrashed()->orderBy('id', 'desc')->first();
            $bookingIdRaw = (string) ($lastBooking->booking_id ?? '');
            $bookingNumeric = (int) preg_replace('/\D+/', '', $bookingIdRaw);
            $nextNumeric = CommonHelper::createId($bookingNumeric);
            $bookingId = 'PB' . str_pad((string) $nextNumeric, 5, '0', STR_PAD_LEFT);

            $user = Auth::user();
            $dmcId = null;
            if ($user) {
                $resolvedDmc = CommonHelper::getDmcId($user);
                $dmcId = $resolvedDmc ?: $user->userId;
            }

            $bookingDetails = [
                'pax_count' => $totalPax,
                'total_pax' => $totalPax,
                'total_price' => $totalPrice,
                'final_price' => $finalPrice,
                'markup_type' => $markupType,
                'markup_amount' => $markupAmount,
                'currency' => 'SGD',
                'itinerary' => $itineraryDates,
                'notes' => $validated['notes'] ?? '',
                'arrival_data' => $arrivalData,
                'departure_data' => $departureData,
                'transfer_data' => $transferData,
                'supplementary_data' => $supplementaryData,
            ];

            $travelDates = [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'duration_days' => $duration,
            ];

            PackageBooking::create([
                'booking_id' => $bookingId,
                'package_id' => $package->package_id,
                'total_price' => $priceDataPayload,
                'booking_details' => $bookingDetails,
                'travel_dates' => $travelDates,
                'selected_hotels' => $selectedHotels,
                'selected_attractions' => $selectedAttractions,
                'selected_guides' => $selectedGuides,
                'selected_restaurants' => $selectedRestaurants,
                'arrival_data' => $arrivalData,
                'departure_data' => $departureData,
                'transfer_data' => $transferData,
                'supplementary_data' => $supplementaryData,
                'status' => '1',
                'booking_status' => 'New Enquiry',
                'booked_by' => $user?->userId,
                'agent_id' => $validated['agent_id'] ?: null,
                'dmc_id' => $dmcId,
                'package' => [
                    'package_id' => $package->package_id,
                    'title' => $package->title,
                    'destination' => $package->destination,
                    'city' => $package->city,
                ],
                'user_info' => $user ? [
                    'user_id' => $user->userId,
                    'name' => $user->name ?? '',
                    'email' => $user->email ?? '',
                ] : null,
                'taxes' => [],
                'payment_details' => [],
            ]);

            DB::commit();
            return redirect()->route('predefined.package.booking.list')
                ->with('success', 'Package booking created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Package booking create failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withInput()->with('error', 'Failed to create package booking. '.$e->getMessage());
        }
    }

    /**
     * Package booking detail page: day-wise itinerary with draggable services.
     */
    public function showBookingDetails(string $bookingId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Note: do not eager-load relation "package" — the bookings table has a JSON column also named "package",
        // which shadows the relationship; title/id come from that snapshot.
        $booking = PackageBooking::with(['agent'])
            ->where('booking_id', $bookingId)
            ->firstOrFail();

        $this->assertUserCanAccessPackageBooking($booking);

        $packageItinerary = $this->buildPackageItineraryByDate($booking);

        return view('package.package-booking-details', [
            'booking' => $booking,
            'packageItinerary' => $packageItinerary,
            'priceHide' => 0,
        ]);
    }

    /**
     * Travel date range plus hotels, attractions, and restaurants grouped by tour_start_date (itinerary UI).
     *
     * @return array{
     *   allDates: array<string, bool>,
     *   hotelsByDate: array<string, list<array{index: int, data: array}>>,
     *   attractionsByDate: array<string, list<array{index: int, data: array}>>,
     *   guidesByDate: array<string, list<array{index: int, data: array}>>,
     *   restaurantsByDate: array<string, list<array{index: int, data: array}>>,
     *   transfersByDate: array<string, list<array{index: int, data: array}>>,
     *   arrivalByDate: array<string, list<array{data: array}>>,
     *   departureByDate: array<string, list<array{data: array}>>,
     *   defaultDate: ?string
     * }
     */
    private function buildPackageItineraryByDate(PackageBooking $booking): array
    {
        $travelDates = is_array($booking->travel_dates) ? $booking->travel_dates : (json_decode($booking->travel_dates, true) ?: []);
        $start = $travelDates['start_date'] ?? null;
        $end = $travelDates['end_date'] ?? null;

        $bookingDetails = is_array($booking->booking_details) ? $booking->booking_details : (json_decode($booking->booking_details, true) ?: []);
        if (!$start || !$end) {
            if (!empty($bookingDetails['itinerary']) && is_array($bookingDetails['itinerary'])) {
                $first = reset($bookingDetails['itinerary']);
                $last = end($bookingDetails['itinerary']);
                $start = $first['date'] ?? $start;
                $end = $last['date'] ?? $end;
            }
        }

        if (!$start || !$end) {
            return [
                'allDates' => [],
                'hotelsByDate' => [],
                'attractionsByDate' => [],
                'guidesByDate' => [],
                'restaurantsByDate' => [],
                'transfersByDate' => [],
                'arrivalByDate' => [],
                'departureByDate' => [],
                'defaultDate' => null,
            ];
        }

        $allDates = [];
        $c = Carbon::parse($start)->startOfDay();
        $endC = Carbon::parse($end)->startOfDay();
        while ($c->lte($endC)) {
            $d = $c->format('Y-m-d');
            $allDates[$d] = true;
            $c->addDay();
        }

        $defaultDate = array_key_first($allDates);

        $hotelsRaw = $this->parseJsonField($booking->selected_hotels);
        $attractionsRaw = $this->parseJsonField($booking->selected_attractions);
        $guidesRaw = $this->parseJsonField($booking->selected_guides);
        $restaurantsRaw = $this->parseJsonField($booking->selected_restaurants);
        $transfersRaw = $this->parseJsonField($booking->transfer_data);

        $arrival = $this->parseJsonField($booking->arrival_data);
        $departure = $this->parseJsonField($booking->departure_data);

        $hotelNameByUniqueId = [];
        $portNameByPortId = [];
        $attractionNameById = [];
        $restaurantNameById = [];
        $resolveHotelName = function ($hotelUniqueId) use (&$hotelNameByUniqueId) {
            $key = (string) ($hotelUniqueId ?? '');
            if ($key === '') {
                return null;
            }
            if (array_key_exists($key, $hotelNameByUniqueId)) {
                return $hotelNameByUniqueId[$key];
            }
            $name = Hotel::where('hotel_unique_id', $key)->value('name');
            $hotelNameByUniqueId[$key] = $name ? (string) $name : null;
            return $hotelNameByUniqueId[$key];
        };
        $resolvePortName = function ($portId) use (&$portNameByPortId) {
            $key = (string) ($portId ?? '');
            if ($key === '') {
                return null;
            }
            if (array_key_exists($key, $portNameByPortId)) {
                return $portNameByPortId[$key];
            }
            $name = Port::where('port_id', $key)->value('port_name');
            $portNameByPortId[$key] = $name ? (string) $name : null;
            return $portNameByPortId[$key];
        };
        $resolveAttractionName = function ($attractionId) use (&$attractionNameById) {
            $key = (string) ($attractionId ?? '');
            if ($key === '') {
                return null;
            }
            if (array_key_exists($key, $attractionNameById)) {
                return $attractionNameById[$key];
            }
            $name = Attraction::where('attraction_id', $key)->value('name');
            if (!$name) {
                $name = Attraction::where('id', $key)->value('name');
            }
            $attractionNameById[$key] = $name ? (string) $name : null;
            return $attractionNameById[$key];
        };
        $resolveRestaurantName = function ($restaurantId) use (&$restaurantNameById) {
            $key = (string) ($restaurantId ?? '');
            if ($key === '') {
                return null;
            }
            if (array_key_exists($key, $restaurantNameById)) {
                return $restaurantNameById[$key];
            }
            $name = Restaurant::where('restaurant_id', $key)->value('restaurant_name');
            if (!$name) {
                $name = Restaurant::where('id', $key)->value('restaurant_name');
            }
            if (!$name) {
                $name = Restaurant::where('id', $key)->value('name');
            }
            $restaurantNameById[$key] = $name ? (string) $name : null;
            return $restaurantNameById[$key];
        };

        $transferItems = [];
        foreach ($transfersRaw as $t) {
            if (!is_array($t)) {
                continue;
            }

            $pickupType = strtolower((string) ($t['pickup_type'] ?? ''));
            $dropoffType = strtolower((string) ($t['dropoff_type'] ?? ''));
            $pickupId = $t['pickup_zone_id'] ?? null;
            $dropoffId = $t['dropoff_zone_id'] ?? null;

            $pickupResolved = null;
            if ($pickupType === 'hotel') {
                $pickupResolved = $resolveHotelName($pickupId);
            } elseif ($pickupType === 'attraction') {
                $pickupResolved = $resolveAttractionName($pickupId);
            } elseif ($pickupType === 'restaurant') {
                $pickupResolved = $resolveRestaurantName($pickupId);
            } elseif ($pickupType === 'port' || $pickupType === 'airport' || $pickupType === 'seaport') {
                $pickupResolved = $resolvePortName($pickupId);
            }

            $dropoffResolved = null;
            if ($dropoffType === 'hotel') {
                $dropoffResolved = $resolveHotelName($dropoffId);
            } elseif ($dropoffType === 'attraction') {
                $dropoffResolved = $resolveAttractionName($dropoffId);
            } elseif ($dropoffType === 'restaurant') {
                $dropoffResolved = $resolveRestaurantName($dropoffId);
            } elseif ($dropoffType === 'port' || $dropoffType === 'airport' || $dropoffType === 'seaport') {
                $dropoffResolved = $resolvePortName($dropoffId);
            }

            $t['pickup_display_name'] = $pickupResolved ?: ($t['pickup_label'] ?? null);
            $t['dropoff_display_name'] = $dropoffResolved ?: ($t['dropoff_label'] ?? null);
            $transferItems[] = $t;
        }

        $arrivalByDate = $this->initEmptyDateBuckets($allDates);
        $departureByDate = $this->initEmptyDateBuckets($allDates);

        if (is_array($arrival) && (!empty($arrival['enabled']) || !empty($arrival['pickup_port_id']) || !empty($arrival['vehicles']))) {
            $arrival['pickup_port_name'] = $resolvePortName($arrival['pickup_port_id'] ?? null);
            $arrival['dropoff_hotel_name'] = $resolveHotelName($arrival['dropoff_hotel_id'] ?? null);
            $d = $arrival['tour_start_date'] ?? $defaultDate;
            if ($d === null || !isset($arrivalByDate[$d])) {
                $d = $defaultDate;
            }
            if ($d !== null && isset($arrivalByDate[$d])) {
                $arrivalByDate[$d][] = ['data' => $arrival];
            }
        }

        if (is_array($departure) && (!empty($departure['enabled']) || !empty($departure['dropoff_port_id']) || !empty($departure['vehicles']))) {
            $departure['dropoff_port_name'] = $resolvePortName($departure['dropoff_port_id'] ?? null);
            $departure['pickup_hotel_name'] = $resolveHotelName($departure['pickup_hotel_id'] ?? null);
            $d = $departure['tour_start_date'] ?? $defaultDate;
            if ($d === null || !isset($departureByDate[$d])) {
                $d = $defaultDate;
            }
            if ($d !== null && isset($departureByDate[$d])) {
                $departureByDate[$d][] = ['data' => $departure];
            }
        }

        return [
            'allDates' => $allDates,
            'hotelsByDate' => $this->groupPackageItemsByTourDate($allDates, $defaultDate, $hotelsRaw),
            'attractionsByDate' => $this->groupPackageItemsByTourDate($allDates, $defaultDate, $attractionsRaw),
            'guidesByDate' => $this->groupPackageItemsByTourDate($allDates, $defaultDate, $guidesRaw),
            'restaurantsByDate' => $this->groupPackageItemsByTourDate($allDates, $defaultDate, $restaurantsRaw),
            'transfersByDate' => $this->groupPackageItemsByTourDate($allDates, $defaultDate, $transferItems),
            'arrivalByDate' => $arrivalByDate,
            'departureByDate' => $departureByDate,
            'defaultDate' => $defaultDate,
        ];
    }

    /**
     * @param array<string, bool> $allDates
     * @return array<string, list<mixed>>
     */
    private function initEmptyDateBuckets(array $allDates): array
    {
        $out = [];
        foreach (array_keys($allDates) as $d) {
            $out[$d] = [];
        }
        return $out;
    }

    /**
     * @param  array<string, bool>  $allDates
     * @param  array<int|string, mixed>  $items
     * @return array<string, list<array{index: int, data: array}>>
     */
    private function groupPackageItemsByTourDate(array $allDates, ?string $defaultDate, array $items): array
    {
        $byDate = [];
        foreach (array_keys($allDates) as $d) {
            $byDate[$d] = [];
        }
        foreach ($items as $idx => $item) {
            if (!is_array($item)) {
                continue;
            }
            $d = $item['tour_start_date'] ?? $defaultDate;
            if ($d === null || !isset($byDate[$d])) {
                $d = $defaultDate;
            }
            if ($d === null || !isset($byDate[$d])) {
                continue;
            }
            $byDate[$d][] = ['index' => (int) $idx, 'data' => $item];
        }

        return $byDate;
    }

    /**
     * Update tour_start_date for a single service after drag-and-drop.
     */
    public function updateServiceTourDate(Request $request, string $bookingId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'section' => 'required|in:hotels,attractions,guides,restaurants,transfers,arrival,departure',
            'index' => 'nullable|integer|min:0',
            'tour_start_date' => 'required|date_format:Y-m-d',
        ]);

        $booking = PackageBooking::where('booking_id', $bookingId)->firstOrFail();
        $this->assertUserCanAccessPackageBooking($booking);

        $section = $validated['section'];
        $idx = $validated['index'] ?? null;
        if (in_array($section, ['hotels', 'attractions', 'guides', 'restaurants', 'transfers'], true) && $idx === null) {
            return response()->json(['success' => false, 'message' => 'Index is required for this section.'], 422);
        }

        $travelDates = is_array($booking->travel_dates)
            ? $booking->travel_dates
            : (json_decode($booking->travel_dates, true) ?: []);
        $rangeStart = isset($travelDates['start_date']) ? Carbon::parse($travelDates['start_date'])->startOfDay() : null;
        $rangeEnd = isset($travelDates['end_date']) ? Carbon::parse($travelDates['end_date'])->startOfDay() : null;
        $newDate = Carbon::parse($validated['tour_start_date'])->startOfDay();

        if ($rangeStart && $rangeEnd && ($newDate->lt($rangeStart) || $newDate->gt($rangeEnd))) {
            return response()->json([
                'success' => false,
                'message' => 'Date must fall within the booking travel period.',
            ], 422);
        }

        $dateStr = $newDate->format('Y-m-d');

        try {
            DB::beginTransaction();

            if (in_array($section, ['hotels', 'attractions', 'guides', 'restaurants', 'transfers'], true)) {
                $column = match ($section) {
                    'hotels' => 'selected_hotels',
                    'attractions' => 'selected_attractions',
                    'guides' => 'selected_guides',
                    'restaurants' => 'selected_restaurants',
                    'transfers' => 'transfer_data',
                    default => null,
                };
                $arr = $booking->{$column};
                if (!is_array($arr)) {
                    $arr = is_string($arr) ? json_decode($arr, true) : [];
                }
                if (!is_array($arr) || !array_key_exists((int) $idx, $arr)) {
                    DB::rollBack();

                    return response()->json(['success' => false, 'message' => 'Service not found.'], 404);
                }
                $arr[(int) $idx]['tour_start_date'] = $dateStr;
                $booking->{$column} = $arr;
            } elseif ($section === 'arrival') {
                $data = $booking->arrival_data;
                if (!is_array($data)) {
                    $data = is_string($data) ? json_decode($data, true) : [];
                }
                if (!is_array($data)) {
                    $data = [];
                }
                $data['tour_start_date'] = $dateStr;
                $booking->arrival_data = $data;
            } elseif ($section === 'departure') {
                $data = $booking->departure_data;
                if (!is_array($data)) {
                    $data = is_string($data) ? json_decode($data, true) : [];
                }
                if (!is_array($data)) {
                    $data = [];
                }
                $data['tour_start_date'] = $dateStr;
                $booking->departure_data = $data;
            }

            $this->syncBookingDetailsPortBlocks($booking);
            $booking->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Service date updated.',
                'tour_start_date' => $dateStr,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('updateServiceTourDate failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Could not update service date.'], 500);
        }
    }

    private function assertUserCanAccessPackageBooking(PackageBooking $booking): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }
        if (in_array((int) $user->role_id, [1, 2, 23], true)) {
            return;
        }
        $resolved = CommonHelper::getDmcId($user);
        $userDmc = $resolved ?: $user->userId;
        if ($booking->dmc_id !== null && (string) $booking->dmc_id !== (string) $userDmc) {
            abort(403, 'You do not have access to this booking.');
        }
    }

    /**
     * Keep booking_details JSON in sync with top-level arrival/departure/transfer columns.
     */
    private function syncBookingDetailsPortBlocks(PackageBooking $booking): void
    {
        $details = $booking->booking_details;
        if (!is_array($details)) {
            $details = is_string($details) ? json_decode($details, true) : [];
        }
        if (!is_array($details)) {
            $details = [];
        }
        $details['arrival_data'] = $booking->arrival_data;
        $details['departure_data'] = $booking->departure_data;
        $details['transfer_data'] = $booking->transfer_data;
        $booking->booking_details = $details;
    }

    /**
     * Build one bucket per calendar day in the travel range, each with draggable service cards.
     *
     * @return array<int, array{date: string, day: int, services: array<int, array<string, mixed>>}>
     */
    private function buildItineraryDayBuckets(PackageBooking $booking): array
    {
        $travelDates = $booking->travel_dates;
        if (!is_array($travelDates)) {
            $travelDates = is_string($travelDates) ? json_decode($travelDates, true) : [];
        }
        $start = $travelDates['start_date'] ?? null;
        $end = $travelDates['end_date'] ?? null;

        $bookingDetails = $booking->booking_details;
        if (!is_array($bookingDetails)) {
            $bookingDetails = is_string($bookingDetails) ? json_decode($bookingDetails, true) : [];
        }
        if (!$start || !$end) {
            if (!empty($bookingDetails['itinerary']) && is_array($bookingDetails['itinerary'])) {
                $first = reset($bookingDetails['itinerary']);
                $last = end($bookingDetails['itinerary']);
                $start = $first['date'] ?? $start;
                $end = $last['date'] ?? $end;
            }
        }
        if (!$start || !$end) {
            return [];
        }

        $defaultDate = Carbon::parse($start)->format('Y-m-d');
        $days = [];
        $c = Carbon::parse($start)->startOfDay();
        $endC = Carbon::parse($end)->startOfDay();
        $dayNum = 1;
        while ($c->lte($endC)) {
            $d = $c->format('Y-m-d');
            $days[$d] = [
                'date' => $d,
                'day' => $dayNum++,
                'services' => [],
            ];
            $c->addDay();
        }

        if ($days === []) {
            return [];
        }

        $firstDayKey = array_key_first($days);

        $assign = function (array $service, string $type, string $label, string $section, $index) use (&$days, $defaultDate, $firstDayKey) {
            $d = $service['tour_start_date'] ?? $defaultDate;
            if (!isset($days[$d])) {
                $d = $defaultDate;
            }
            if (!isset($days[$d])) {
                $d = $firstDayKey;
            }
            $days[$d]['services'][] = [
                'type' => $type,
                'label' => $label,
                'section' => $section,
                'index' => $index,
            ];
        };

        foreach ($booking->selected_hotels ?? [] as $i => $h) {
            if (!is_array($h)) {
                continue;
            }
            $assign($h, 'hotel', (string) ($h['hotel_name'] ?? $h['name'] ?? 'Hotel'), 'hotels', $i);
        }
        foreach ($booking->selected_attractions ?? [] as $i => $a) {
            if (!is_array($a)) {
                continue;
            }
            $assign($a, 'attraction', (string) ($a['name'] ?? 'Attraction'), 'attractions', $i);
        }
        foreach ($booking->selected_guides ?? [] as $i => $g) {
            if (!is_array($g)) {
                continue;
            }
            $assign($g, 'guide', (string) ($g['name'] ?? 'Guide'), 'guides', $i);
        }
        foreach ($booking->selected_restaurants ?? [] as $i => $r) {
            if (!is_array($r)) {
                continue;
            }
            $assign($r, 'restaurant', (string) ($r['restaurant_name'] ?? $r['name'] ?? 'Restaurant'), 'restaurants', $i);
        }
        $transfers = $booking->transfer_data ?? [];
        if (!is_array($transfers)) {
            $transfers = is_string($transfers) ? json_decode($transfers, true) : [];
        }
        foreach ($transfers as $i => $t) {
            if (!is_array($t)) {
                continue;
            }
            $label = trim(($t['pickup_label'] ?? '') . ' → ' . ($t['dropoff_label'] ?? ''));
            if ($label === '→' || $label === '') {
                $label = 'Transfer';
            }
            $assign($t, 'transfer', $label, 'transfers', $i);
        }

        $arrival = $booking->arrival_data ?? [];
        if (!is_array($arrival)) {
            $arrival = is_string($arrival) ? json_decode($arrival, true) : [];
        }
        if (is_array($arrival) && (!empty($arrival['enabled']) || !empty($arrival['pickup_port_id']) || !empty($arrival['vehicles']))) {
            $assign($arrival, 'arrival', 'Arrival transfer', 'arrival', null);
        }

        $departure = $booking->departure_data ?? [];
        if (!is_array($departure)) {
            $departure = is_string($departure) ? json_decode($departure, true) : [];
        }
        if (is_array($departure) && (!empty($departure['enabled']) || !empty($departure['pickup_hotel_id']) || !empty($departure['vehicles']))) {
            $assign($departure, 'departure', 'Departure transfer', 'departure', null);
        }

        return array_values($days);
    }

    private function parseJsonField($value)
    {
        if (is_array($value)) {
            return $value;
        }
        if (!is_string($value) || trim($value) === '') {
            return [];
        }
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }
}
