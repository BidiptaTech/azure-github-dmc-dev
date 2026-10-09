<?php

namespace App\Services\HotelSuppliers\Adapters;

use App\Services\HotelSuppliers\Contracts\HotelSupplierAdapter;
use App\Services\HotelSuppliers\HotelSearchRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

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
        $baseUrl = rtrim((string) ($credentials['base_url'] ?? ''), '/');
        $apiKey = trim((string) ($credentials['api_key'] ?? ''));
        $secret = trim((string) ($credentials['api_secret'] ?? ''));

        if (! array_key_exists('base_url', $credentials) && $baseUrl === '') {
            $baseUrl = rtrim((string) config('services.hotelbeds.base_url', ''), '/');
        }
        if (! array_key_exists('api_key', $credentials) && $apiKey === '') {
            $apiKey = trim((string) config('services.hotelbeds.api_key', ''));
        }
        if (! array_key_exists('api_secret', $credentials) && $secret === '') {
            $secret = trim((string) config('services.hotelbeds.api_secret', ''));
        }

        if ($baseUrl === '' || $apiKey === '' || $secret === '') {
            throw new RuntimeException('Hotelbeds credentials are incomplete (base_url, api_key and api_secret required).');
        }

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

        $timeout = (int) ($credentials['timeout'] ?? config('services.hotelbeds.timeout', 30));

        $response = Http::timeout($timeout > 0 ? $timeout : 30)
            ->withHeaders($this->buildAuthHeaders($apiKey, $secret))
            ->acceptJson()
            ->asJson()
            ->post($baseUrl . '/hotel-api/1.0/hotels', $payload);

        if (! $response->successful()) {
            Log::warning('Hotelbeds availability failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload,
            ]);

            throw new RuntimeException('Hotelbeds hotel search failed with HTTP ' . $response->status());
        }

        $body = $response->json();
        $rawHotels = $this->extractRawHotels($body);

        return [
            'hotels' => array_map(fn (array $item) => $this->normalizeHotel($item), $rawHotels),
            'provider' => $body,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function buildAuthHeaders(string $apiKey, string $secret): array
    {
        $timestamp = (string) time();
        $signature = hash('sha256', $apiKey . $secret . $timestamp);

        return [
            'Api-key' => $apiKey,
            'X-Signature' => $signature,
            'Accept' => 'application/json',
            'Accept-Encoding' => 'gzip',
            'Content-Type' => 'application/json',
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
     * @return array<string, mixed>
     */
    private function normalizeHotel(array $item): array
    {
        $currency = (string) ($item['currency'] ?? 'EUR');
        $rooms = [];

        foreach ($item['rooms'] ?? [] as $room) {
            if (! is_array($room)) {
                continue;
            }

            foreach ($room['rates'] ?? [] as $rate) {
                if (! is_array($rate)) {
                    continue;
                }
                $rooms[] = $this->normalizeRateAsRoom($room, $rate, $currency);
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
            'star_rating' => $this->parseStarRating($item['categoryCode'] ?? $item['categoryName'] ?? ''),
            'address' => trim(implode(', ', array_filter([
                (string) ($item['zoneName'] ?? ''),
                (string) ($item['destinationName'] ?? ''),
            ]))),
            'currency' => $currency,
            'min_rate' => $minRate,
            'max_rate' => $maxRate,
            'latitude' => (string) ($item['latitude'] ?? ''),
            'longitude' => (string) ($item['longitude'] ?? ''),
            'destination_code' => (string) ($item['destinationCode'] ?? ''),
            'destination_name' => (string) ($item['destinationName'] ?? ''),
            'zone_name' => (string) ($item['zoneName'] ?? ''),
            'category_code' => (string) ($item['categoryCode'] ?? ''),
            'category_name' => (string) ($item['categoryName'] ?? ''),
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
     * @return array<string, mixed>
     */
    private function normalizeRateAsRoom(array $room, array $rate, string $currency): array
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

        return [
            'room_id' => $roomCode,
            'room_name' => $displayName,
            'rate_plan_id' => (string) ($rate['rateKey'] ?? ''),
            'bed_type' => $bedType,
            'extra_bed_type' => '',
            'meal_plan' => $boardName,
            'board_code' => $boardCode,
            'breakfast_included' => in_array($boardCode, ['BB', 'HB', 'FB', 'AI'], true),
            'max_occupancy' => (int) (($rate['adults'] ?? 0) + ($rate['children'] ?? 0)),
            'price' => [
                'actual' => $net,
                'tax' => $tax,
            ],
            'inclusions' => $promotions,
            'currency' => $currency,
            'rate_class' => (string) ($rate['rateClass'] ?? ''),
            'rate_type' => (string) ($rate['rateType'] ?? ''),
            'payment_type' => (string) ($rate['paymentType'] ?? ''),
            'allotment' => (int) ($rate['allotment'] ?? 0),
            'packaging' => (bool) ($rate['packaging'] ?? false),
            'raw' => [
                'room' => $room,
                'rate' => $rate,
            ],
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
