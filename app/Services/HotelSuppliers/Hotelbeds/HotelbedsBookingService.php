<?php

namespace App\Services\HotelSuppliers\Hotelbeds;

use App\Services\HotelSuppliers\Contracts\OnlineHotelBookingService;
use App\Services\HotelSuppliers\RecheckPriceComparison;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Confirms Hotelbeds online hotel orders: always CheckRate → Book.
 *
 * Enquiries may be approved days later, so CheckRate runs for every approve
 * (including rates that were BOOKABLE at search time). Stale rateKeys fall back
 * to Availability filtered by hotel code, then CheckRate again.
 */
class HotelbedsBookingService implements OnlineHotelBookingService
{
    public function __construct(
        private RecheckPriceComparison $priceComparison,
    ) {}

    public function supplierCode(): string
    {
        return 'hotelbeds';
    }

    /**
     * @param  array<string, mixed>  $booking
     * @param  array<string, string|null>  $credentials
     * @return array<string, mixed>
     */
    public function recheckFromOrderBooking(array $booking, array $credentials, array $options = []): array
    {
        $context = $this->resolveBookingContext($booking);
        $client = new HotelbedsClient($credentials);

        $rateKey = $context['rate_key'];
        $checkRateBody = null;
        $usedFallback = false;

        try {
            $checkRateBody = $client->checkRate($rateKey);
        } catch (RuntimeException $e) {
            Log::info('Hotelbeds CheckRate failed; attempting availability fallback', [
                'hotel_code' => $context['hotel_code'],
                'error' => $e->getMessage(),
            ]);

            $rateKey = $this->freshRateKeyFromAvailability($client, $context);
            $usedFallback = true;
            $checkRateBody = $client->checkRate($rateKey);
        }

        $parsed = $this->parseCheckRateResponse($checkRateBody, $context);

        if ($parsed['rate_key'] === '') {
            throw new RuntimeException('Hotelbeds CheckRate did not return a bookable rateKey.');
        }

        $comparison = $this->priceComparison->compare(
            $booking,
            $parsed['supplier_gross_price'] > 0
                ? $parsed['supplier_gross_price']
                : $parsed['supplier_net_price'],
            $context['stored_supplier_price'],
            $options,
        );

        return $comparison + [
            'supplier_code' => 'hotelbeds',
            'api_environment' => $credentials['api_environment'] ?? 'demo',
            'session_id' => null,
            'rate_key' => $parsed['rate_key'],
            'rate_type' => $parsed['rate_type'],
            'rate_class' => $parsed['rate_class'],
            'currency' => $parsed['currency'],
            'check_in' => $parsed['check_in'],
            'check_out' => $parsed['check_out'],
            'hotel_code' => $parsed['hotel_code'],
            'hotel_name' => $parsed['hotel_name'],
            'room_code' => $parsed['room_code'],
            'room_name' => $parsed['room_name'],
            'meal_plan_code' => $parsed['meal_plan_code'],
            'meal_plan_name' => $parsed['meal_plan_name'],
            'cancellation_policy_type' => $parsed['cancellation_policy_type'],
            'cancellation_policies' => $parsed['cancellation_policies'],
            'adults' => $parsed['adults'],
            'children' => $parsed['children'],
            'supplier_net_price' => $parsed['supplier_net_price'],
            'supplier_gross_price' => $parsed['supplier_gross_price'],
            'used_availability_fallback' => $usedFallback,
            'recheck_payload' => [
                'rateKey' => $rateKey,
                'fallback' => $usedFallback,
            ],
            'recheck_response' => $checkRateBody,
            'recheck_rate' => $parsed['raw_rate'],
        ];
    }

    /**
     * @param  array<string, mixed>  $recheckResult
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
            throw new RuntimeException('Agency booking ID (reference ID) is required for Hotelbeds booking.');
        }

        $rateKey = trim((string) ($recheckResult['rate_key'] ?? ''));

        if ($rateKey === '') {
            throw new RuntimeException('The Hotelbeds rateKey is missing. Please recheck availability again.');
        }

        $adults = max(1, (int) ($recheckResult['adults'] ?? $booking['selected_adults'] ?? 1));
        $children = max(0, (int) ($recheckResult['children'] ?? $booking['selected_children'] ?? 0));
        [$holderName, $holderSurname] = $this->splitHolderName($booking);
        $paxes = $this->buildPaxes($booking, $adults, $children);

        $supplierNet = (float) ($recheckResult['supplier_net_price'] ?? 0);
        $toleranceBase = (float) ($recheckResult['customer_price'] ?? $supplierNet);
        $tolerance = max(2.0, round($toleranceBase * 0.02, 2));

        $bookPayload = [
            'holder' => [
                'name' => $holderName,
                'surname' => $holderSurname,
            ],
            'rooms' => [
                [
                    'rateKey' => $rateKey,
                    'paxes' => $paxes,
                ],
            ],
            'clientReference' => $agencyBookingId,
            'remark' => trim((string) ($booking['specialRequests'] ?? $booking['remarks'] ?? '')),
            'tolerance' => $tolerance,
        ];

        $client = new HotelbedsClient($credentials);
        $bookBody = $client->book($bookPayload);
        $supplierReference = $this->extractSupplierReference($bookBody);

        return [
            'supplier_code' => 'hotelbeds',
            'api_environment' => $recheckResult['api_environment'] ?? ($credentials['api_environment'] ?? 'demo'),
            'agency_booking_id' => $agencyBookingId,
            'supplier_booking_reference' => $supplierReference,
            'book_payload' => $bookPayload,
            'book_response' => $bookBody,
            'booked_at' => now()->toIso8601String(),
            'recheck' => [
                'rate_key' => $rateKey,
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
        $token = ($recheckResult['api_environment'] ?? 'demo') . '.' . bin2hex(random_bytes(16));
        Cache::put($this->cacheKey($orderId, $bookingIndex, $token), $recheckResult, now()->addMinutes(20));

        return $token;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function pullCachedRecheckResult(int $orderId, int $bookingIndex, string $token): ?array
    {
        $value = Cache::get($this->cacheKey($orderId, $bookingIndex, $token));

        return is_array($value) ? $value : null;
    }

    public function forgetCachedRecheckResult(int $orderId, int $bookingIndex, string $token): void
    {
        Cache::forget($this->cacheKey($orderId, $bookingIndex, $token));
    }

    private function cacheKey(int $orderId, int $bookingIndex, string $token): string
    {
        return "hotelbeds_recheck:{$orderId}:{$bookingIndex}:{$token}";
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
        $rawRate = is_array($rawRoom['rate'] ?? null) ? $rawRoom['rate'] : [];
        $rawRoomBlock = is_array($rawRoom['room'] ?? null) ? $rawRoom['room'] : [];
        $hotel = is_array($online['hotel'] ?? null) ? $online['hotel'] : [];
        $hotelDetails = is_array($booking['hotelDetails'] ?? null) ? $booking['hotelDetails'] : [];
        $dates = is_array($booking['bookingDate'] ?? null) ? $booking['bookingDate'] : [];

        $checkIn = (string) ($online['check_in'] ?? $dates[0] ?? '');
        $checkOut = (string) ($online['check_out'] ?? $dates[1] ?? '');

        if ($checkIn === '' || $checkOut === '') {
            throw new RuntimeException('Check-in and check-out dates are required for online hotel booking.');
        }

        $hotelCode = (string) ($hotel['code'] ?? $hotelDetails['hotel_id'] ?? $booking['hotel_id'] ?? '');

        if ($hotelCode === '') {
            throw new RuntimeException('Hotel code is missing from the stored online booking.');
        }

        $rateKey = trim((string) ($storedRoom['rate_key'] ?? $rawRate['rateKey'] ?? ''));

        if ($rateKey === '') {
            throw new RuntimeException('Hotelbeds rateKey is missing from the stored enquiry. Re-search the hotel.');
        }

        $paxInfo = (string) ($search['pax_info'] ?? '');
        if ($paxInfo === '') {
            $adults = max(1, (int) ($booking['selected_adults'] ?? $rawRate['adults'] ?? 1));
            $children = max(0, (int) ($booking['selected_children'] ?? $rawRate['children'] ?? 0));
            $paxInfo = $adults . '|' . $children;
        }

        $occupancy = $this->parsePaxInfo($paxInfo);

        $storedNet = (float) ($rawRate['net'] ?? $storedRoom['net_price'] ?? 0);
        if ($storedNet <= 0) {
            $storedNet = (float) ($storedRoom['gross_price'] ?? 0);
        }

        return [
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'hotel_code' => $hotelCode,
            'hotel_name' => (string) ($hotel['name'] ?? $hotelDetails['hotel_name'] ?? ''),
            'room_code' => (string) ($storedRoom['code'] ?? $rawRoomBlock['code'] ?? ''),
            'room_name' => (string) ($storedRoom['name'] ?? $rawRoomBlock['name'] ?? ''),
            'board_code' => strtoupper(trim((string) (
                $storedRoom['meal_plan_code']
                ?? $rawRate['boardCode']
                ?? ''
            ))),
            'board_name' => (string) ($storedRoom['meal_plan_name'] ?? $rawRate['boardName'] ?? ''),
            'rate_class' => strtoupper(trim((string) ($storedRoom['rate_class'] ?? $rawRate['rateClass'] ?? ''))),
            'rate_key' => $rateKey,
            'currency' => strtoupper((string) ($online['currency'] ?? $booking['currency'] ?? 'EUR')),
            'pax_info' => $paxInfo,
            'adults' => $occupancy['adults'],
            'children' => $occupancy['children'],
            'child_ages' => $occupancy['child_ages'],
            'rooms' => max(1, (int) ($search['rooms'] ?? $booking['number_of_rooms'] ?? 1)),
            'stored_supplier_price' => $storedNet > 0 ? $storedNet : null,
        ];
    }

    /**
     * @return array{adults: int, children: int, child_ages: array<int, int>}
     */
    private function parsePaxInfo(string $paxInfo): array
    {
        $parts = explode('|', $paxInfo);
        $adults = max(1, (int) ($parts[0] ?? 1));
        $children = max(0, (int) ($parts[1] ?? 0));
        $childAges = $children > 0 ? array_fill(0, $children, 8) : [];

        return [
            'adults' => $adults,
            'children' => $children,
            'child_ages' => $childAges,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function freshRateKeyFromAvailability(HotelbedsClient $client, array $context): string
    {
        $payload = [
            'stay' => [
                'checkIn' => $context['check_in'],
                'checkOut' => $context['check_out'],
            ],
            'occupancies' => [
                [
                    'rooms' => max(1, (int) ($context['rooms'] ?? 1)),
                    'adults' => max(1, (int) ($context['adults'] ?? 1)),
                    'children' => max(0, (int) ($context['children'] ?? 0)),
                ],
            ],
            'hotels' => [
                'hotel' => [(int) $context['hotel_code'] ?: $context['hotel_code']],
            ],
        ];

        if ((int) ($context['children'] ?? 0) > 0 && ! empty($context['child_ages'])) {
            $payload['occupancies'][0]['paxes'] = array_map(
                fn (int $age) => ['type' => 'CH', 'age' => $age],
                $context['child_ages'],
            );
        }

        $body = $client->availability($payload);
        $hotels = $body['hotels']['hotels'] ?? ($body['hotels'] ?? []);

        if (! is_array($hotels) || $hotels === []) {
            throw new RuntimeException('Rate no longer available — re-search hotel.');
        }

        $hotel = null;
        foreach ($hotels as $candidate) {
            if (! is_array($candidate)) {
                continue;
            }
            if ((string) ($candidate['code'] ?? '') === (string) $context['hotel_code']) {
                $hotel = $candidate;
                break;
            }
        }

        $hotel ??= is_array($hotels[0] ?? null) ? $hotels[0] : null;

        if (! is_array($hotel)) {
            throw new RuntimeException('Rate no longer available — re-search hotel.');
        }

        $matched = $this->matchAvailabilityRate($hotel, $context);

        if ($matched === null) {
            throw new RuntimeException('Rate no longer available — re-search hotel.');
        }

        return $matched;
    }

    /**
     * @param  array<string, mixed>  $hotel
     * @param  array<string, mixed>  $context
     */
    private function matchAvailabilityRate(array $hotel, array $context): ?string
    {
        $roomCode = (string) ($context['room_code'] ?? '');
        $boardCode = strtoupper(trim((string) ($context['board_code'] ?? '')));
        $rateClass = strtoupper(trim((string) ($context['rate_class'] ?? '')));

        $candidates = [];

        foreach ($hotel['rooms'] ?? [] as $room) {
            if (! is_array($room)) {
                continue;
            }

            if ($roomCode !== '' && (string) ($room['code'] ?? '') !== $roomCode) {
                continue;
            }

            foreach ($room['rates'] ?? [] as $rate) {
                if (! is_array($rate)) {
                    continue;
                }

                $key = trim((string) ($rate['rateKey'] ?? ''));
                if ($key === '') {
                    continue;
                }

                $score = 0;
                $rateBoard = strtoupper(trim((string) ($rate['boardCode'] ?? '')));
                $rateCls = strtoupper(trim((string) ($rate['rateClass'] ?? '')));

                if ($boardCode !== '' && $rateBoard === $boardCode) {
                    $score += 2;
                }
                if ($rateClass !== '' && $rateCls === $rateClass) {
                    $score += 1;
                }

                $candidates[] = [
                    'score' => $score,
                    'net' => (float) ($rate['net'] ?? 0),
                    'rateKey' => $key,
                ];
            }
        }

        if ($candidates === []) {
            return null;
        }

        usort($candidates, function (array $a, array $b) {
            if ($a['score'] !== $b['score']) {
                return $b['score'] <=> $a['score'];
            }

            return $a['net'] <=> $b['net'];
        });

        return $candidates[0]['rateKey'];
    }

    /**
     * @param  array<string, mixed>  $body
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function parseCheckRateResponse(array $body, array $context): array
    {
        $hotel = $body['hotel'] ?? null;

        if (! is_array($hotel)) {
            // Some responses nest under hotels.hotel
            $nested = $body['hotels']['hotel'] ?? null;
            $hotel = is_array($nested) ? $nested : null;
        }

        if (! is_array($hotel)) {
            throw new RuntimeException('Hotelbeds CheckRate returned no hotel data.');
        }

        $room = null;
        $rate = null;

        foreach ($hotel['rooms'] ?? [] as $roomCandidate) {
            if (! is_array($roomCandidate)) {
                continue;
            }
            foreach ($roomCandidate['rates'] ?? [] as $rateCandidate) {
                if (! is_array($rateCandidate)) {
                    continue;
                }
                if (trim((string) ($rateCandidate['rateKey'] ?? '')) === '') {
                    continue;
                }
                $room = $roomCandidate;
                $rate = $rateCandidate;
                break 2;
            }
        }

        if (! is_array($rate) || ! is_array($room)) {
            throw new RuntimeException('Hotelbeds CheckRate returned no bookable rate.');
        }

        $net = (float) ($rate['net'] ?? 0);
        $gross = (float) ($rate['sellingRate'] ?? $net);
        $checkIn = (string) ($hotel['checkIn'] ?? $context['check_in']);
        $checkOut = (string) ($hotel['checkOut'] ?? $context['check_out']);
        $rateClass = strtoupper(trim((string) ($rate['rateClass'] ?? '')));

        $rawPolicies = is_array($rate['cancellationPolicies'] ?? null)
            ? $rate['cancellationPolicies']
            : [];

        return [
            'rate_key' => trim((string) ($rate['rateKey'] ?? '')),
            'rate_type' => (string) ($rate['rateType'] ?? ''),
            'rate_class' => $rateClass,
            'currency' => (string) ($hotel['currency'] ?? $context['currency']),
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'hotel_code' => (string) ($hotel['code'] ?? $context['hotel_code']),
            'hotel_name' => (string) ($hotel['name'] ?? $context['hotel_name']),
            'room_code' => (string) ($room['code'] ?? $context['room_code']),
            'room_name' => (string) ($room['name'] ?? $context['room_name']),
            'meal_plan_code' => strtoupper(trim((string) ($rate['boardCode'] ?? $context['board_code']))),
            'meal_plan_name' => (string) ($rate['boardName'] ?? $context['board_name']),
            'cancellation_policy_type' => HotelbedsCancellationPolicies::policyTypeFromRateClass($rateClass),
            'cancellation_policies' => HotelbedsCancellationPolicies::normalize(
                $rawPolicies,
                $checkIn,
                $checkOut,
                $net,
            ),
            'adults' => (int) ($rate['adults'] ?? $context['adults']),
            'children' => (int) ($rate['children'] ?? $context['children']),
            'supplier_net_price' => $net,
            'supplier_gross_price' => $gross > 0 ? $gross : $net,
            'raw_rate' => $rate,
        ];
    }

    /**
     * @param  array<string, mixed>  $booking
     * @return array{0: string, 1: string}
     */
    private function splitHolderName(array $booking): array
    {
        $full = trim((string) (
            $booking['fullName']
            ?? $booking['guest_name']
            ?? $booking['name']
            ?? ''
        ));

        if ($full === '') {
            return ['Guest', 'User'];
        }

        $parts = preg_split('/\s+/', $full, 2) ?: [];
        $first = trim((string) ($parts[0] ?? 'Guest'));
        $last = trim((string) ($parts[1] ?? 'User'));

        return [
            $first !== '' ? $first : 'Guest',
            $last !== '' ? $last : 'User',
        ];
    }

    /**
     * @param  array<string, mixed>  $booking
     * @return array<int, array<string, mixed>>
     */
    private function buildPaxes(array $booking, int $adults, int $children): array
    {
        [$first, $last] = $this->splitHolderName($booking);
        $paxes = [];

        for ($i = 0; $i < $adults; $i++) {
            $paxes[] = [
                'roomId' => 1,
                'type' => 'AD',
                'name' => $i === 0 ? $first : 'Guest',
                'surname' => $i === 0 ? $last : 'User',
            ];
        }

        // Ages not stored in pax_info beyond count — default child age 8 (same as search).
        $ages = [];
        for ($i = 0; $i < $children; $i++) {
            $ages[] = 8;
        }

        foreach ($ages as $age) {
            $paxes[] = [
                'roomId' => 1,
                'type' => 'CH',
                'age' => $age,
                'name' => 'Child',
                'surname' => $last,
            ];
        }

        return $paxes;
    }

    /**
     * @param  array<string, mixed>  $bookBody
     */
    private function extractSupplierReference(array $bookBody): ?string
    {
        $candidates = [
            $bookBody['booking']['reference'] ?? null,
            $bookBody['booking']['hotel']['reference'] ?? null,
            $bookBody['reference'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            $value = trim((string) ($candidate ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }
}
