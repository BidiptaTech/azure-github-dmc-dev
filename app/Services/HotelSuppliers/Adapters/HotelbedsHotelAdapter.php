<?php

namespace App\Services\HotelSuppliers\Adapters;

use App\Services\HotelSuppliers\Contracts\HotelSupplierAdapter;
use App\Services\HotelSuppliers\Hotelbeds\HotelbedsCancellationPolicies;
use App\Services\HotelSuppliers\Hotelbeds\HotelbedsClient;
use App\Services\HotelSuppliers\HotelSearchRequest;

class HotelbedsHotelAdapter implements HotelSupplierAdapter
{
    /**
     * Hardcoded geolocation for Singapore (Hotelbeds availability by geolocation).
     * Replace with dynamic city → coordinates lookup later.
     */
    private const GEO_LATITUDE = 1.290270;

    private const GEO_LONGITUDE = 103.851959;

    private const GEO_RADIUS = 20;

    private const GEO_UNIT = 'km';

    public function code(): string
    {
        return 'hotelbeds';
    }

    /**
     * @param  array<string, string|null>  $credentials
     * @return array{hotels: array<int, array<string, mixed>>, provider: mixed}
     */
    public function fetchHotels(HotelSearchRequest $request, array $credentials): array
    {
        $client = new HotelbedsClient($credentials);
        $occupancy = $this->parsePaxInfo($request->paxInfo);

        $payload = [
            'stay' => [
                'checkIn' => $request->checkIn,
                'checkOut' => $request->checkOut,
            ],
            'occupancies' => [
                [
                    'rooms' => 1,
                    'adults' => $occupancy['adults'],
                    'children' => $occupancy['children'],
                ],
            ],
            'geolocation' => [
                'latitude' => self::GEO_LATITUDE,
                'longitude' => self::GEO_LONGITUDE,
                'radius' => self::GEO_RADIUS,
                'unit' => self::GEO_UNIT,
            ],
        ];

        if ($occupancy['children'] > 0 && $occupancy['child_ages'] !== []) {
            $payload['occupancies'][0]['paxes'] = array_map(
                fn (int $age) => ['type' => 'CH', 'age' => $age],
                $occupancy['child_ages'],
            );
        }

        $body = $client->availability($payload);
        $rawHotels = $this->extractRawHotels($body);

        $context = [
            'supplier_code' => $this->code(),
            'session_id' => null,
            'currency' => null,
            'check_in' => $request->checkIn,
            'check_out' => $request->checkOut,
            'search' => [
                'city_name' => $request->cityName,
                'pax_info' => $request->paxInfo,
                'rooms' => $request->roomCount(),
            ],
        ];

        return [
            'hotels' => array_map(
                fn (array $item) => $this->normalizeHotel($item, $context),
                $rawHotels
            ),
            'provider' => $body,
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

        // Default child age when count is given without ages (Hotelbeds requires ages for children).
        $childAges = $children > 0 ? array_fill(0, $children, 8) : [];

        return [
            'adults' => $adults,
            'children' => $children,
            'child_ages' => $childAges,
        ];
    }

    /**
     * @param  mixed  $body
     * @return array<int, array<string, mixed>>
     */
    private function extractRawHotels(mixed $body): array
    {
        if (! is_array($body)) {
            return [];
        }

        if (isset($body['hotels']['hotels']) && is_array($body['hotels']['hotels'])) {
            return $body['hotels']['hotels'];
        }

        if (isset($body['hotels']) && is_array($body['hotels']) && array_is_list($body['hotels'])) {
            return $body['hotels'];
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, mixed>  $context  Shared search/session facts for booking payloads.
     * @return array<string, mixed>
     */
    private function normalizeHotel(array $item, array $context = []): array
    {
        $currency = (string) ($item['currency'] ?? 'EUR');
        $hotelContext = array_merge($context, [
            'currency' => $currency,
            'hotel' => [
                'code' => (string) ($item['code'] ?? $item['hotel_id'] ?? ''),
                'name' => (string) ($item['name'] ?? $item['hotel_name'] ?? ''),
                'rating' => $this->parseStarRating($item['categoryCode'] ?? $item['categoryName'] ?? ''),
                'address' => trim(implode(', ', array_filter([
                    (string) ($item['zoneName'] ?? ''),
                    (string) ($item['destinationName'] ?? ''),
                ]))),
                'image' => '',
                'check_in_time' => '',
                'check_out_time' => '',
                'latitude' => (string) ($item['latitude'] ?? ''),
                'longitude' => (string) ($item['longitude'] ?? ''),
                'destination_code' => (string) ($item['destinationCode'] ?? ''),
                'destination_name' => (string) ($item['destinationName'] ?? ''),
                'zone_name' => (string) ($item['zoneName'] ?? ''),
                'category_code' => (string) ($item['categoryCode'] ?? ''),
                'category_name' => (string) ($item['categoryName'] ?? ''),
            ],
        ]);

        $rooms = [];

        foreach ($item['rooms'] ?? [] as $room) {
            if (! is_array($room)) {
                continue;
            }

            foreach ($room['rates'] ?? [] as $rate) {
                if (! is_array($rate)) {
                    continue;
                }
                $rooms[] = $this->normalizeRateAsRoom($room, $rate, $currency, $hotelContext);
            }
        }

        usort($rooms, fn (array $a, array $b) => ($a['price']['actual'] ?? 0) <=> ($b['price']['actual'] ?? 0));

        $minRate = (float) ($item['minRate'] ?? ($rooms[0]['price']['actual'] ?? 0));
        $maxRate = (float) ($item['maxRate'] ?? 0);
        if ($maxRate <= 0 && $rooms !== []) {
            $maxRate = (float) ($rooms[array_key_last($rooms)]['price']['actual'] ?? $minRate);
        }

        return [
            'hotel_id' => (string) ($item['code'] ?? $item['hotel_id'] ?? ''),
            'hotel_name' => (string) ($item['name'] ?? $item['hotel_name'] ?? ''),
            'star_rating' => $hotelContext['hotel']['rating'],
            'address' => $hotelContext['hotel']['address'],
            'currency' => $currency,
            'min_rate' => $minRate,
            'max_rate' => $maxRate,
            'latitude' => $hotelContext['hotel']['latitude'],
            'longitude' => $hotelContext['hotel']['longitude'],
            'destination_code' => $hotelContext['hotel']['destination_code'],
            'destination_name' => $hotelContext['hotel']['destination_name'],
            'zone_name' => $hotelContext['hotel']['zone_name'],
            'category_code' => $hotelContext['hotel']['category_code'],
            'category_name' => $hotelContext['hotel']['category_name'],
            'images' => [],
            'description' => (string) ($item['categoryName'] ?? ''),
            'rooms' => $rooms,
            'supplier_code' => $this->code(),
            'raw' => $item,
        ];
    }

    /**
     * @param  array<string, mixed>  $room
     * @param  array<string, mixed>  $rate
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function normalizeRateAsRoom(array $room, array $rate, string $currency, array $context = []): array
    {
        $roomName = trim((string) ($room['name'] ?? ''));
        $boardName = trim((string) ($rate['boardName'] ?? $rate['boardCode'] ?? ''));
        $boardCode = strtoupper(trim((string) ($rate['boardCode'] ?? '')));
        $displayName = $boardName !== '' ? $roomName . ' (' . $boardName . ')' : $roomName;

        $net = (float) ($rate['net'] ?? $rate['sellingRate'] ?? 0);
        $tax = $this->extractRateTax($rate);

        $promotions = [];
        foreach ($rate['promotions'] ?? [] as $promo) {
            if (is_array($promo) && filled($promo['name'] ?? null)) {
                $promotions[] = (string) $promo['name'];
            }
        }

        $roomCode = (string) ($room['code'] ?? '');
        $bedType = $this->inferBedType($roomName, $roomCode);
        $rateClass = strtoupper(trim((string) ($rate['rateClass'] ?? '')));
        $policyType = HotelbedsCancellationPolicies::policyTypeFromRateClass($rateClass);
        $policies = HotelbedsCancellationPolicies::normalize(
            is_array($rate['cancellationPolicies'] ?? null) ? $rate['cancellationPolicies'] : [],
            (string) ($context['check_in'] ?? ''),
            (string) ($context['check_out'] ?? ''),
            $net,
        );
        $rateKey = (string) ($rate['rateKey'] ?? '');
        $adults = (int) ($rate['adults'] ?? 0);
        $children = (int) ($rate['children'] ?? 0);

        $raw = [
            'room' => $room,
            'rate' => $rate,
        ];

        return [
            'room_id' => $roomCode,
            'room_name' => $displayName,
            'rate_plan_id' => $rateKey,
            'bed_type' => $bedType,
            'extra_bed_type' => '',
            'meal_plan' => $boardName,
            'board_code' => $boardCode,
            'breakfast_included' => in_array($boardCode, ['BB', 'HB', 'FB', 'AI'], true),
            'max_occupancy' => $adults + $children,
            'booked_adults' => $adults,
            'booked_children' => $children,
            'price' => [
                'actual' => $net,
                'tax' => $tax,
            ],
            'inclusions' => $promotions,
            'currency' => $currency,
            'rate_class' => $rateClass,
            'rate_type' => (string) ($rate['rateType'] ?? ''),
            'payment_type' => (string) ($rate['paymentType'] ?? ''),
            'allotment' => (int) ($rate['allotment'] ?? 0),
            'packaging' => (bool) ($rate['packaging'] ?? false),
            'cancellation_policy_type' => $policyType,
            'cancellation_policy' => $policies,
            'free_cancellation' => $policies === [] && $rateClass !== 'NRF',
            'booking' => $this->bookingPayload(
                $roomCode,
                $roomName,
                $boardCode,
                $boardName,
                $policyType,
                $rateKey,
                $net,
                $rate,
                $policies,
                $context,
                $raw,
            ),
            'raw' => $raw,
        ];
    }

    /**
     * Stored on each room so the modal can persist onlineHotelBooking in the same
     * shape as MG Bedbank (rate key, policies, hotel/search context, raw supplier rate).
     *
     * @param  array<string, mixed>  $rate
     * @param  array<int, array<string, mixed>>  $policies
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function bookingPayload(
        string $roomCode,
        string $roomName,
        string $boardCode,
        string $boardName,
        string $policyType,
        string $rateKey,
        float $net,
        array $rate,
        array $policies,
        array $context,
        array $raw,
    ): array {
        $adults = (int) ($rate['adults'] ?? 0);
        $children = (int) ($rate['children'] ?? 0);

        return [
            'supplier_code' => $context['supplier_code'] ?? $this->code(),
            'session_id' => $context['session_id'] ?? null,
            'currency' => $context['currency'] ?? 'EUR',
            'check_in' => $context['check_in'] ?? '',
            'check_out' => $context['check_out'] ?? '',
            'search' => $context['search'] ?? [],
            'hotel' => $context['hotel'] ?? [],
            'room' => [
                'code' => $roomCode,
                'name' => $roomName,
                'meal_plan_code' => $boardCode,
                'meal_plan_name' => $boardName,
                'cancellation_policy_type' => $policyType,
                'promo_code' => '',
                'avail_flag' => true,
                'can_hold' => false,
                'package_rate' => (bool) ($rate['packaging'] ?? false),
                'net_price' => $net,
                'gross_price' => $net,
                'avg_night_price' => 0.0,
                'b2b_markup' => 0.0,
                'msp' => '',
                'rate_key' => $rateKey,
                'rate_keys' => $rateKey !== '' ? [$rateKey] : [],
                'rate_class' => strtoupper(trim((string) ($rate['rateClass'] ?? ''))),
                'rate_type' => (string) ($rate['rateType'] ?? ''),
                'payment_type' => (string) ($rate['paymentType'] ?? ''),
                'allotment' => (int) ($rate['allotment'] ?? 0),
                'allocations' => [[
                    'roomNo' => 1,
                    'rateKey' => $rateKey,
                    'noOfAdults' => $adults,
                    'noOfChild' => $children,
                    'child1Age' => 0,
                    'child2Age' => 0,
                    'extraBed' => false,
                    'netPrice' => $net,
                    'grossPrice' => $net,
                    'avgNightPrice' => 0.0,
                    'b2BMarkup' => 0,
                ]],
                'cancellation_policies' => $policies,
                'messages' => [],
                'size' => '',
                'max_occupancy' => $adults + $children,
                'max_adult' => $adults,
                'max_children' => $children,
                'image' => '',
            ],
            'raw_room' => $raw,
            'fetched_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>  $rate
     */
    private function extractRateTax(array $rate): float
    {
        $taxTotal = 0.0;
        $taxBlock = $rate['taxes']['taxes'] ?? $rate['taxes'] ?? [];

        if (! is_array($taxBlock)) {
            return 0.0;
        }

        foreach ($taxBlock as $tax) {
            if (! is_array($tax)) {
                continue;
            }
            if (($tax['included'] ?? true) === false) {
                $taxTotal += (float) ($tax['clientAmount'] ?? $tax['amount'] ?? 0);
            }
        }

        return $taxTotal;
    }

    /**
     * Hotelbeds availability does not return bed type explicitly — infer from room code / name.
     */
    private function inferBedType(string $roomName, string $roomCode): string
    {
        $name = strtoupper(trim($roomName));
        $codePrefix = strtoupper(trim(explode('.', $roomCode)[0] ?? $roomCode));

        $codeMap = [
            'SGL' => 'Single Bed',
            'DUS' => 'Double (Single Use)',
            'DBT' => 'Double or Twin',
            'TWN' => 'Twin Beds',
            'TPL' => 'Triple Room',
            'QUD' => 'Quad Room',
            'FAM' => 'Family Room',
            'STU' => 'Studio',
            'JSU' => 'Junior Suite',
            'SUI' => 'Suite',
        ];

        if ($codePrefix !== '' && isset($codeMap[$codePrefix])) {
            return $codeMap[$codePrefix];
        }

        if (str_contains($name, 'KING')) {
            return 'King Bed';
        }
        if (str_contains($name, 'QUEEN')) {
            return 'Queen Bed';
        }
        if (str_contains($name, 'DOUBLE SINGLE USE') || str_contains($name, 'SINGLE USE')) {
            return 'Double (Single Use)';
        }
        if (str_contains($name, 'DOUBLE OR TWIN')) {
            return 'Double or Twin';
        }
        if (str_contains($name, 'TWIN')) {
            return 'Twin Beds';
        }
        if (str_contains($name, 'DOUBLE')) {
            return 'Double Bed';
        }
        if (str_contains($name, 'SINGLE')) {
            return 'Single Bed';
        }
        if (str_contains($name, 'SUITE')) {
            return 'Suite';
        }

        return $name !== '' ? 'Standard' : '';
    }

    private function parseStarRating(mixed $category): string
    {
        $value = (string) $category;

        if (preg_match('/(\d)/', $value, $matches)) {
            return $matches[1];
        }

        return '';
    }
}
