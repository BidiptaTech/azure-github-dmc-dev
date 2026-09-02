<?php

namespace App\Services\HotelSuppliers\MgBedbank;

use App\Services\HotelSuppliers\Adapters\MgBedbankHotelAdapter;
use App\Services\HotelSuppliers\Contracts\OnlineHotelBookingService;
use App\Services\HotelSuppliers\HotelSearchRequest;
use App\Services\HotelSuppliers\RecheckPriceComparison;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

/**
 * Confirms MG Bedbank online hotel orders: SearchHotel → RecheckHotel → BookHotel.
 */
class MgBedbankBookingService implements OnlineHotelBookingService
{
    public function __construct(
        private RecheckPriceComparison $priceComparison,
        private MgBedbankHotelAdapter $adapter = new MgBedbankHotelAdapter(),
    ) {}

    public function supplierCode(): string
    {
        return 'mg_bedbank';
    }

    /**
     * @param  array<string, mixed>  $booking  Single hotel booking row from orders.data
     * @param  array<string, string|null>  $credentials
     * @return array<string, mixed>
     */
    public function recheckFromOrderBooking(array $booking, array $credentials, array $options = []): array
    {
        $context = $this->resolveBookingContext($booking);
        $searchRequest = new HotelSearchRequest(
            cityName: $context['city_name'],
            checkIn: $context['check_in'],
            checkOut: $context['check_out'],
            paxInfo: $context['pax_info'],
            rooms: $context['rooms'],
        );

        $searchResult = $this->adapter->fetchHotelRooms(
            $searchRequest,
            $context['hotel_code'],
            $credentials,
        );

        $hotel = $searchResult['hotel'] ?? null;

        if (! is_array($hotel) || empty($hotel['rooms'])) {
            throw new RuntimeException('No availability returned from MG Bedbank for this hotel and dates.');
        }

        $matchedRoom = $this->matchStoredRoom($hotel['rooms'], $context);
        $rawRoom = $matchedRoom['raw'] ?? [];
        $allocations = $this->normalizeAllocations($rawRoom['rooms']['room'] ?? []);

        if ($allocations === []) {
            throw new RuntimeException('MG Bedbank did not return rate keys for the selected room.');
        }

        $client = new MgBedbankClient($credentials);
        $sessionId = (string) ($searchResult['session_id'] ?? $hotel['session_id'] ?? '');

        if ($sessionId === '') {
            throw new RuntimeException('MG Bedbank search did not return a session ID.');
        }

        $recheckPayload = [
            'SessionID' => $sessionId,
            'Nationality' => $context['nationality'],
            'Country' => $context['country'],
            'City' => $context['city'],
            'CheckIn' => $context['check_in'],
            'CheckOut' => $context['check_out'],
            'HotelCode' => $context['hotel_code'],
            'RoomDetails' => [
                'Code' => (string) ($rawRoom['code'] ?? $context['room_code']),
                'MealPlan' => (string) ($rawRoom['mealPlan'] ?? $context['meal_plan_code']),
                'CancellationPolicyType' => (string) ($rawRoom['cancellationPolicyType'] ?? $context['cancellation_policy_type']),
                'PackageRate' => (bool) ($rawRoom['packageRate'] ?? $context['package_rate']),
            ],
            'Rooms' => [
                'Room' => array_map(
                    fn (array $allocation) => $this->recheckRoomPayload($allocation),
                    $allocations,
                ),
            ],
            'AvailFlag' => true,
            'Currency' => $context['currency'],
            'Language' => $client->credential('language', 'En'),
            'DetailLevel' => 'Basic',
        ];

        $recheckBody = $client->recheckHotel($recheckPayload);
        

        if ($reason = $client->failureReason($recheckBody)) {
            throw new RuntimeException('MG Bedbank recheck failed: ' . $reason);
        }

        $roomDetails = $recheckBody['hotels']['hotel']['roomDetails'] ?? [];
        $recheckAllocations = $this->normalizeAllocations($roomDetails['rooms']['room'] ?? []);
        $supplierNet = (float) ($roomDetails['netPrice'] ?? 0);
        $supplierGross = (float) ($roomDetails['grossPrice'] ?? 0);

        $comparison = $this->priceComparison->compare(
            $booking,
            $supplierGross > 0 ? $supplierGross : $supplierNet,
            $context['stored_supplier_price'],
            $options,
        );

        return $comparison + [
            'supplier_code' => 'mg_bedbank',
            'session_id' => (string) ($recheckBody['sessionID'] ?? $sessionId),
            'currency' => (string) ($recheckBody['currency'] ?? $context['currency']),
            'check_in' => (string) ($recheckBody['checkIn'] ?? $context['check_in']),
            'check_out' => (string) ($recheckBody['checkOut'] ?? $context['check_out']),
            'hotel_code' => $context['hotel_code'],
            'hotel_name' => (string) ($recheckBody['hotels']['hotel']['name'] ?? $context['hotel_name']),
            'room_code' => (string) ($roomDetails['code'] ?? $context['room_code']),
            'room_name' => (string) ($roomDetails['name'] ?? $context['room_name']),
            'meal_plan_code' => (string) ($roomDetails['mealPlan'] ?? $context['meal_plan_code']),
            'meal_plan_name' => (string) ($roomDetails['mealPlanName'] ?? $context['meal_plan_name']),
            'cancellation_policy_type' => (string) ($roomDetails['cancellationPolicyType'] ?? $context['cancellation_policy_type']),
            'package_rate' => (bool) ($roomDetails['packageRate'] ?? $context['package_rate']),
            'avail_flag' => (bool) ($roomDetails['availFlag'] ?? true),
            'supplier_net_price' => $supplierNet,
            'supplier_gross_price' => $supplierGross,
            'allocations' => $recheckAllocations,
            'recheck_payload' => $recheckPayload,
            'recheck_response' => $recheckBody,
            'search_response' => $searchResult['provider'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $recheckResult  Output from recheckFromOrderBooking()
     * @param  array<string, mixed>  $booking
     * @param  array<string, string|null>  $credentials
     * @return array<string, mixed>
     */
    public function bookFromRecheckResult(
        array $recheckResult,
        array $booking,
        string $agencyBookingId,
        array $credentials,
    ): array {
        $agencyBookingId = trim($agencyBookingId);

        if ($agencyBookingId === '') {
            throw new RuntimeException('Agency booking ID (reference ID) is required for MG Bedbank booking.');
        }

        $client = new MgBedbankClient($credentials);
        $allocations = $recheckResult['allocations'] ?? [];
        $paxList = $this->buildPaxList($booking, $allocations);
        $paxIndex = 0;

        $bookRooms = [];

        foreach ($allocations as $allocation) {
            $adults = max(1, (int) ($allocation['noOfAdults'] ?? 1));
            $roomPax = [];

            for ($i = 0; $i < $adults; $i++) {
                $roomPax[] = $paxList[$paxIndex] ?? $paxList[0] ?? $this->defaultPax($booking);
                $paxIndex++;
            }

            $bookRooms[] = [
                'PaxDetails' => ['Pax' => $roomPax],
                'RoomNo' => (string) ($allocation['roomNo'] ?? count($bookRooms) + 1),
                'NoOfAdults' => (string) $adults,
                'NoOfChild' => filled($allocation['noOfChild'] ?? null)
                    ? (string) (int) $allocation['noOfChild']
                    : '',
                'ExtraBed' => (bool) ($allocation['extraBed'] ?? false),
                'RateKey' => (string) ($allocation['rateKey'] ?? ''),
            ];
        }

        $bookPayload = [
            'SessionID' => (string) ($recheckResult['session_id'] ?? ''),
            'AgencyBookingID' => $agencyBookingId,
            'Nationality' => (string) ($recheckResult['recheck_payload']['Nationality'] ?? 'SG'),
            'CheckIn' => (string) ($recheckResult['check_in'] ?? ''),
            'CheckOut' => (string) ($recheckResult['check_out'] ?? ''),
            'HotelCode' => (string) ($recheckResult['hotel_code'] ?? ''),
            'RoomDetails' => [
                'Code' => (string) ($recheckResult['room_code'] ?? ''),
                'MealPlan' => (string) ($recheckResult['meal_plan_code'] ?? ''),
                'CancellationPolicyType' => (string) ($recheckResult['cancellation_policy_type'] ?? ''),
                'PackageRate' => (bool) ($recheckResult['package_rate'] ?? false),
            ],
            'Rooms' => ['Room' => $bookRooms],
            'Currency' => (string) ($recheckResult['currency'] ?? 'SGD'),
            'Language' => strtoupper($client->credential('language', 'EN')),
            'AvailFlag' => true,
            'OnHold' => false,
            'SpecialReq' => trim((string) ($booking['specialRequests'] ?? $booking['remarks'] ?? '')),
            'DetailLevel' => 'FULL',
        ];

        $bookBody = $client->bookHotel($bookPayload);

        if ($reason = $client->failureReason($bookBody)) {
            throw new RuntimeException('MG Bedbank booking failed: ' . $reason);
        }

        return [
            'supplier_code' => 'mg_bedbank',
            'agency_booking_id' => $agencyBookingId,
            'book_payload' => $bookPayload,
            'book_response' => $bookBody,
            'booked_at' => now()->toIso8601String(),
            'recheck' => [
                'session_id' => $recheckResult['session_id'] ?? null,
                'supplier_net_price' => $recheckResult['supplier_net_price'] ?? null,
                'supplier_gross_price' => $recheckResult['supplier_gross_price'] ?? null,
                'currency' => $recheckResult['currency'] ?? null,
            ],
        ];
    }

    public function cacheRecheckResult(int $orderId, int $bookingIndex, array $recheckResult): string
    {
        $token = bin2hex(random_bytes(16));
        $key = $this->cacheKey($orderId, $bookingIndex, $token);

        Cache::put($key, $recheckResult, now()->addMinutes(20));

        return $token;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function pullCachedRecheckResult(int $orderId, int $bookingIndex, string $token): ?array
    {
        $key = $this->cacheKey($orderId, $bookingIndex, $token);

        $value = Cache::pull($key);

        return is_array($value) ? $value : null;
    }

    private function cacheKey(int $orderId, int $bookingIndex, string $token): string
    {
        return "mg_bedbank_recheck:{$orderId}:{$bookingIndex}:{$token}";
    }

    /**
     * @param  array<string, mixed>  $booking
     * @return array<string, mixed>
     */
    private function resolveBookingContext(array $booking): array
    {
        $online = is_array($booking['onlineHotelBooking'] ?? null)
            ? $booking['onlineHotelBooking']
            : [];

        $search = is_array($online['search'] ?? null) ? $online['search'] : [];
        $storedRoom = is_array($online['room'] ?? null) ? $online['room'] : [];
        $hotel = is_array($online['hotel'] ?? null) ? $online['hotel'] : [];
        $hotelDetails = is_array($booking['hotelDetails'] ?? null) ? $booking['hotelDetails'] : [];
        $dates = is_array($booking['bookingDate'] ?? null) ? $booking['bookingDate'] : [];

        $checkIn = (string) ($online['check_in'] ?? $dates[0] ?? '');
        $checkOut = (string) ($online['check_out'] ?? $dates[1] ?? '');

        if ($checkIn === '' || $checkOut === '') {
            throw new RuntimeException('Check-in and check-out dates are required for online hotel booking.');
        }

        $hotelCode = (string) ($hotel['code'] ?? $hotelDetails['hotel_id'] ?? '');

        if ($hotelCode === '') {
            throw new RuntimeException('Hotel code is missing from the stored online booking.');
        }

        $country = strtoupper((string) ($search['country'] ?? $hotel['country_code'] ?? 'SG'));
        $city = strtoupper((string) ($search['city'] ?? $hotel['city_code'] ?? ''));
        $paxInfo = (string) ($search['pax_info'] ?? $this->paxInfoFromRooms($booking['rooms'] ?? []));

        if ($city === '') {
            throw new RuntimeException('MG destination city code is missing from the stored online booking.');
        }

        return [
            'city_name' => (string) ($search['city_name'] ?? $booking['city'] ?? $hotelDetails['city'] ?? 'Singapore'),
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'pax_info' => $paxInfo !== '' ? $paxInfo : '2|0',
            'rooms' => $this->storedRoomCount($online, $booking),
            'hotel_code' => $hotelCode,
            'hotel_name' => (string) ($hotel['name'] ?? $hotelDetails['hotel_name'] ?? ''),
            'country' => $country,
            'city' => $city,
            'nationality' => $country,
            'currency' => strtoupper((string) ($online['currency'] ?? $booking['currency'] ?? 'SGD')),
            'room_code' => (string) ($storedRoom['code'] ?? ''),
            'room_name' => (string) ($storedRoom['name'] ?? ''),
            'meal_plan_code' => (string) ($storedRoom['meal_plan_code'] ?? ''),
            'meal_plan_name' => (string) ($storedRoom['meal_plan_name'] ?? ''),
            'cancellation_policy_type' => (string) ($storedRoom['cancellation_policy_type'] ?? ''),
            'package_rate' => (bool) ($storedRoom['package_rate'] ?? false),
            'stored_supplier_price' => $this->storedSupplierPrice($online),
        ];
    }

    /**
     * What MG charged when the enquiry was taken.
     *
     * The markup appliers rewrite the presented room's `price` block but never touch
     * `raw_room`, so the stored raw room is the only markup-free record of MG's own price.
     *
     * @param  array<string, mixed>  $online
     */
    private function storedSupplierPrice(array $online): ?float
    {
        $rawRoom = is_array($online['raw_room'] ?? null) ? $online['raw_room'] : [];
        $storedRoom = is_array($online['room'] ?? null) ? $online['room'] : [];

        $candidates = [
            $rawRoom['grossPrice'] ?? null,
            $rawRoom['netPrice'] ?? null,
            $storedRoom['gross_price'] ?? null,
            $storedRoom['net_price'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if ((float) $candidate > 0) {
                return (float) $candidate;
            }
        }

        return null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rooms
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function matchStoredRoom(array $rooms, array $context): array
    {
        $candidates = [];

        foreach ($rooms as $room) {
            if (! is_array($room)) {
                continue;
            }

            $roomCode = (string) ($room['room_id'] ?? '');
            $boardCode = strtoupper((string) ($room['board_code'] ?? ''));
            $policyType = (string) ($room['raw']['cancellationPolicyType'] ?? $room['booking']['room']['cancellation_policy_type'] ?? '');
            $packageRate = (bool) ($room['package_rate'] ?? $room['raw']['packageRate'] ?? false);

            $codeMatch = $context['room_code'] === '' || $roomCode === $context['room_code'];
            $mealMatch = $context['meal_plan_code'] === '' || $boardCode === strtoupper($context['meal_plan_code']);
            $policyMatch = $context['cancellation_policy_type'] === '' || $policyType === $context['cancellation_policy_type'];
            $packageMatch = (bool) $context['package_rate'] === $packageRate;

            if ($codeMatch && $mealMatch && $policyMatch && $packageMatch) {
                $candidates[] = $room;
            }
        }

        if ($candidates === [] && $context['room_code'] !== '') {
            foreach ($rooms as $room) {
                if (is_array($room) && (string) ($room['room_id'] ?? '') === $context['room_code']) {
                    $candidates[] = $room;
                }
            }
        }

        if ($candidates === []) {
            throw new RuntimeException('The selected room rate is no longer available from MG Bedbank.');
        }

        return $candidates[0];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizeAllocations(mixed $rooms): array
    {
        if (! is_array($rooms)) {
            return [];
        }

        $list = isset($rooms['roomNo']) || isset($rooms['rateKey']) ? [$rooms] : $rooms;
        $normalized = [];

        foreach ($list as $room) {
            if (! is_array($room)) {
                continue;
            }

            $normalized[] = [
                'roomNo' => (int) ($room['roomNo'] ?? $room['RoomNo'] ?? count($normalized) + 1),
                'rateKey' => (string) ($room['rateKey'] ?? $room['RateKey'] ?? ''),
                'noOfAdults' => (int) ($room['noOfAdults'] ?? $room['NoOfAdults'] ?? 0),
                'noOfChild' => (int) ($room['noOfChild'] ?? $room['NoOfChild'] ?? 0),
                'child1Age' => (int) ($room['child1Age'] ?? $room['Child1Age'] ?? 0),
                'child2Age' => (int) ($room['child2Age'] ?? $room['Child2Age'] ?? 0),
                'extraBed' => (bool) ($room['extraBed'] ?? $room['ExtraBed'] ?? false),
                'netPrice' => (float) ($room['netPrice'] ?? $room['NetPrice'] ?? 0),
                'grossPrice' => (float) ($room['grossPrice'] ?? $room['GrossPrice'] ?? 0),
            ];
        }

        return array_values(array_filter($normalized, fn (array $row) => $row['rateKey'] !== ''));
    }

    /**
     * @param  array<string, mixed>  $allocation
     * @return array<string, mixed>
     */
    private function recheckRoomPayload(array $allocation): array
    {
        return [
            'RoomNo' => (int) ($allocation['roomNo'] ?? 1),
            'RateKey' => (string) ($allocation['rateKey'] ?? ''),
            'NoOfAdults' => (int) ($allocation['noOfAdults'] ?? 1),
            'NoOfChild' => (int) ($allocation['noOfChild'] ?? 0),
            'Child1Age' => (int) ($allocation['child1Age'] ?? 0),
            'Child2Age' => (int) ($allocation['child2Age'] ?? 0),
            'ExtraBed' => (bool) ($allocation['extraBed'] ?? false),
        ];
    }

    /**
     * How many room blocks the enquiry was priced for.
     *
     * Recorded on the stored search by the adapter; older orders predate that field, so
     * fall back to the operator's room count from the booking form and finally to one.
     *
     * @param  array<string, mixed>  $online
     * @param  array<string, mixed>  $booking
     */
    private function storedRoomCount(array $online, array $booking): int
    {
        $search = is_array($online['search'] ?? null) ? $online['search'] : [];
        $selection = is_array($online['selection'] ?? null) ? $online['selection'] : [];

        $candidates = [
            $search['rooms'] ?? null,
            $selection['number_of_rooms'] ?? null,
            $booking['numberOfRooms'] ?? null,
            is_array($booking['rooms'] ?? null) ? count($booking['rooms']) : null,
        ];

        foreach ($candidates as $candidate) {
            if ((int) $candidate > 0) {
                return (int) $candidate;
            }
        }

        return 1;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rooms
     */
    private function paxInfoFromRooms(array $rooms): string
    {
        $adults = 0;
        $children = 0;

        foreach ($rooms as $room) {
            if (! is_array($room)) {
                continue;
            }

            $beds = is_array($room['beds'] ?? null) ? $room['beds'] : [];
            foreach ($beds as $bed) {
                if (! is_array($bed)) {
                    continue;
                }

                $headCount = (int) ($bed['head_count'] ?? $bed['max_occupancy'] ?? 0);
                $adults += $headCount > 0 ? $headCount : 1;
            }
        }

        if ($adults <= 0) {
            $adults = (int) ($rooms[0]['selected_persons'] ?? 2) ?: 2;
        }

        return $adults . '|' . $children;
    }

    /**
     * @param  array<string, mixed>  $booking
     * @param  array<int, array<string, mixed>>  $allocations
     * @return array<int, array<string, string>>
     */
    private function buildPaxList(array $booking, array $allocations): array
    {
        $fullName = trim((string) ($booking['fullName'] ?? ''));
        [$firstName, $lastName] = $this->splitName($fullName);

        $totalAdults = 0;

        foreach ($allocations as $allocation) {
            $totalAdults += max(1, (int) ($allocation['noOfAdults'] ?? 1));
        }

        $pax = [];

        for ($i = 0; $i < max(1, $totalAdults); $i++) {
            $pax[] = [
                'Salutation' => 'Mr.',
                'FirstName' => $firstName,
                'LastName' => $lastName,
                'Age' => '',
            ];
        }

        return $pax;
    }

    /**
     * @param  array<string, mixed>  $booking
     * @return array<string, string>
     */
    private function defaultPax(array $booking): array
    {
        [$firstName, $lastName] = $this->splitName(trim((string) ($booking['fullName'] ?? 'Guest')));

        return [
            'Salutation' => 'Mr.',
            'FirstName' => $firstName,
            'LastName' => $lastName,
            'Age' => '',
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $fullName): array
    {
        $fullName = trim($fullName);

        if ($fullName === '') {
            return ['Guest', 'User'];
        }

        $parts = preg_split('/\s+/', $fullName) ?: [];
        $firstName = (string) ($parts[0] ?? 'Guest');
        $lastName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : $firstName;

        return [$firstName, $lastName];
    }
}
