<?php

namespace App\Services\HotelSuppliers\Tinivia;

use App\Services\HotelSuppliers\Adapters\TiniviaHotelAdapter;
use App\Services\HotelSuppliers\Contracts\OnlineHotelBookingService;
use App\Services\HotelSuppliers\HotelSearchRequest;
use App\Services\HotelSuppliers\RecheckPriceComparison;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

/**
 * Confirms Tinivia online hotel orders: checkRoomAvailability → confirmBookingRequest.
 *
 * The enquiry stored a rate plan id, which Tinivia will not book against. The recheck
 * re-prices the property and pulls the fresh `roomRateKey` for the stored room, and the
 * approval step books that key using the agency reference ID as Tinivia's `bookingId`.
 */
class TiniviaBookingService implements OnlineHotelBookingService
{
    public function __construct(
        private RecheckPriceComparison $priceComparison,
        private TiniviaHotelAdapter $adapter = new TiniviaHotelAdapter(),
    ) {}

    public function supplierCode(): string
    {
        return 'tinivia';
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
        );

        $searchResult = $this->adapter->fetchHotelRooms(
            $searchRequest,
            $context['hotel_code'],
            $credentials,
        );

        $hotel = $searchResult['hotel'] ?? null;

        if (! is_array($hotel) || empty($hotel['rooms'])) {
            throw new RuntimeException('No availability returned from Tinivia for this hotel and dates.');
        }

        $matchedRoom = $this->matchStoredRoom($hotel['rooms'], $context);
        $rateKey = (string) ($matchedRoom['room_rate_key'] ?? '');

        if ($rateKey === '') {
            throw new RuntimeException('Tinivia did not return a room rate key for the selected room.');
        }

        $price = is_array($matchedRoom['currency_converted_price'] ?? null)
            && ($matchedRoom['currency_converted_price']['actual'] ?? 0) > 0
                ? $matchedRoom['currency_converted_price']
                : ($matchedRoom['price'] ?? []);

        $supplierNet = (float) ($price['actual'] ?? 0);
        $supplierGross = (float) ($price['gross'] ?? $supplierNet);

        $comparison = $this->priceComparison->compare(
            $booking,
            $supplierGross > 0 ? $supplierGross : $supplierNet,
            $context['stored_supplier_price'],
            $options,
        );

        return $comparison + [
            'supplier_code' => 'tinivia',
            'session_id' => null,
            'room_rate_key' => $rateKey,
            'currency' => (string) ($hotel['currency'] ?? $context['currency']),
            'check_in' => $context['check_in'],
            'check_out' => $context['check_out'],
            'hotel_code' => $context['hotel_code'],
            'hotel_name' => (string) ($hotel['hotel_name'] ?? $context['hotel_name']),
            'room_code' => (string) ($matchedRoom['room_id'] ?? $context['room_code']),
            'room_name' => (string) ($matchedRoom['room_name'] ?? $context['room_name']),
            'rate_plan_id' => (string) ($matchedRoom['rate_plan_id'] ?? $context['rate_plan_id']),
            'rate_plan_name' => (string) ($matchedRoom['rate_plan_name'] ?? ''),
            'meal_plan_code' => (string) ($matchedRoom['meal_plan'] ?? $context['meal_plan_code']),
            'meal_plan_name' => (string) ($matchedRoom['meal_plan_label'] ?? $matchedRoom['meal_plan'] ?? $context['meal_plan_name']),
            'avail_flag' => (bool) ($matchedRoom['is_available'] ?? true),
            'available_rooms' => (int) ($matchedRoom['available_rooms'] ?? 0),
            'refundable' => (bool) ($matchedRoom['refundable'] ?? false),
            'free_cancellation' => (bool) ($matchedRoom['free_cancellation'] ?? false),
            'cancellation_policies' => $matchedRoom['cancellation_policy'] ?? [],
            'cancellation_policy_details' => $matchedRoom['cancellation_policy_details'] ?? [],
            'supplier_net_price' => $supplierNet,
            'supplier_gross_price' => $supplierGross,
            'recheck_payload' => [
                'checkIn' => $context['check_in'],
                'checkOut' => $context['check_out'],
                'productId' => $context['hotel_code'],
                'paxInfo' => $context['pax_info'],
            ],
            'recheck_room' => $matchedRoom['raw'] ?? [],
            'recheck_response' => $searchResult['provider'] ?? null,
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
            throw new RuntimeException('Agency booking ID (reference ID) is required for Tinivia booking.');
        }

        $rateKey = trim((string) ($recheckResult['room_rate_key'] ?? ''));

        if ($rateKey === '') {
            throw new RuntimeException('The Tinivia room rate key is missing. Please recheck availability again.');
        }

        $bookPayload = [
            'bookingId' => $agencyBookingId,
            'roomRateKey' => $rateKey,
            'primaryGuest' => $this->buildPrimaryGuest($booking),
        ];

        $client = new TiniviaClient($credentials);
        $bookBody = $client->confirmBookingRequest($bookPayload);

        if ($reason = $client->failureReason($bookBody)) {
            throw new RuntimeException('Tinivia booking failed: ' . $reason);
        }

        return [
            'supplier_code' => 'tinivia',
            'agency_booking_id' => $agencyBookingId,
            'supplier_booking_reference' => $this->extractSupplierReference($bookBody, $agencyBookingId),
            'book_payload' => $bookPayload,
            'book_response' => $bookBody,
            'booked_at' => now()->toIso8601String(),
            'recheck' => [
                'room_rate_key' => $rateKey,
                'supplier_net_price' => $recheckResult['supplier_net_price'] ?? null,
                'supplier_gross_price' => $recheckResult['supplier_gross_price'] ?? null,
                'currency' => $recheckResult['currency'] ?? null,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $recheckResult
     */
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
        $value = Cache::pull($this->cacheKey($orderId, $bookingIndex, $token));

        return is_array($value) ? $value : null;
    }

    private function cacheKey(int $orderId, int $bookingIndex, string $token): string
    {
        return "tinivia_recheck:{$orderId}:{$bookingIndex}:{$token}";
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
        $rawRoom = is_array($online['raw_room'] ?? null) ? $online['raw_room'] : [];
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

        $paxInfo = (string) ($search['pax_info'] ?? $this->paxInfoFromRooms($booking['rooms'] ?? []));

        // The enquiry stored the rate plan id under `rate_key`; the bookable
        // `roomRateKey` only exists on a live availability response.
        $ratePlanId = (string) ($rawRoom['ratePlanId'] ?? $storedRoom['rate_key'] ?? '');

        return [
            'city_name' => (string) ($search['city_name'] ?? $booking['city'] ?? $hotelDetails['city'] ?? ''),
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'pax_info' => $paxInfo !== '' ? $paxInfo : '2|0',
            'hotel_code' => $hotelCode,
            'hotel_name' => (string) ($hotel['name'] ?? $hotelDetails['hotel_name'] ?? ''),
            'currency' => strtoupper((string) ($online['currency'] ?? $booking['currency'] ?? 'INR')),
            'room_code' => (string) ($storedRoom['code'] ?? $rawRoom['roomId'] ?? ''),
            'room_name' => (string) ($storedRoom['name'] ?? $rawRoom['roomName'] ?? ''),
            'rate_plan_id' => $ratePlanId,
            'meal_plan_code' => (string) ($storedRoom['meal_plan_name'] ?? $rawRoom['mealPlanName'] ?? ''),
            'meal_plan_name' => (string) ($storedRoom['meal_plan_name'] ?? ''),
            'stored_supplier_price' => $this->storedSupplierPrice($rawRoom),
        ];
    }

    /**
     * What Tinivia charged when the enquiry was taken.
     *
     * The markup appliers rewrite the presented room's price blocks but never touch
     * `raw`, so the stored raw room is the only markup-free record of Tinivia's price.
     *
     * @param  array<string, mixed>  $rawRoom
     */
    private function storedSupplierPrice(array $rawRoom): ?float
    {
        foreach (['currencyConvertedPrice', 'price'] as $block) {
            if (! is_array($rawRoom[$block] ?? null)) {
                continue;
            }

            foreach (['finalPriceWithTax', 'finalPrice', 'actual'] as $key) {
                $value = (float) ($rawRoom[$block][$key] ?? 0);

                if ($value > 0) {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * Tinivia returns one row per room/rate-plan pair, so narrow by room id, then rate
     * plan, then meal plan, keeping only rows that are still sellable.
     *
     * @param  array<int, array<string, mixed>>  $rooms
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function matchStoredRoom(array $rooms, array $context): array
    {
        $rooms = array_values(array_filter($rooms, 'is_array'));

        $bookable = array_values(array_filter(
            $rooms,
            fn (array $room) => ($room['is_available'] ?? true) && (string) ($room['room_rate_key'] ?? '') !== '',
        ));

        if ($bookable === []) {
            throw new RuntimeException('The selected room rate is no longer available from Tinivia.');
        }

        $filters = [
            fn (array $room) => $context['room_code'] === ''
                || (string) ($room['room_id'] ?? '') === $context['room_code'],
            fn (array $room) => $context['rate_plan_id'] === ''
                || (string) ($room['rate_plan_id'] ?? '') === $context['rate_plan_id'],
            fn (array $room) => $context['meal_plan_code'] === ''
                || strcasecmp((string) ($room['meal_plan'] ?? ''), $context['meal_plan_code']) === 0,
        ];

        $candidates = $bookable;

        foreach ($filters as $filter) {
            $narrowed = array_values(array_filter($candidates, $filter));

            if ($narrowed !== []) {
                $candidates = $narrowed;
            }
        }

        if ($context['room_code'] !== ''
            && (string) ($candidates[0]['room_id'] ?? '') !== $context['room_code']) {
            throw new RuntimeException('The selected room is no longer available from Tinivia for these dates.');
        }

        return $candidates[0];
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
     * @return array<string, string>
     */
    private function buildPrimaryGuest(array $booking): array
    {
        [$firstName, $lastName] = $this->splitName((string) ($booking['fullName'] ?? ''));

        $email = trim((string) ($booking['email'] ?? ''));
        $phone = preg_replace('/\D+/', '', (string) ($booking['phone'] ?? '')) ?: '';

        return [
            'firstName' => $firstName,
            'lastName' => $lastName,
            'emailId' => $email !== '' ? $email : $this->fallbackEmail(),
            'mobileNo' => $phone !== '' ? $phone : $this->fallbackMobile(),
        ];
    }

    private function fallbackEmail(): string
    {
        $email = trim((string) config('mail.from.address', ''));

        return $email !== '' ? $email : 'bookings@example.com';
    }

    private function fallbackMobile(): string
    {
        return '9999999999';
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

    /**
     * @param  array<string, mixed>  $body
     */
    private function extractSupplierReference(array $body, string $fallback): string
    {
        foreach (['bookingId', 'booking_id', 'bookingReference', 'confirmationNo', 'confirmationNumber', 'referenceNo'] as $key) {
            $value = $body[$key] ?? $body['data'][$key] ?? null;

            if (is_scalar($value) && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return $fallback;
    }
}
