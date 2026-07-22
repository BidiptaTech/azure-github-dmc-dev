<?php

namespace App\Services\HotelSuppliers\Adapters;

use App\Services\HotelSuppliers\Contracts\HotelSupplierAdapter;
use App\Services\HotelSuppliers\HotelSearchRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MgBedbankHotelAdapter implements HotelSupplierAdapter
{
    public function code(): string
    {
        return 'mg_bedbank';
    }

    /**
     * @param  array<string, string|null>  $credentials
     * @return array{hotels: array<int, array<string, mixed>>, provider: mixed}
     */
    public function fetchHotels(HotelSearchRequest $request, array $credentials): array
    {
        $baseUrl = rtrim(trim((string) ($credentials['base_url'] ?? config('services.mg_bedbank.base_url', ''))), '/');
        $agencyCode = trim((string) ($credentials['agency_code'] ?? config('services.mg_bedbank.agency_code', '')));
        $username = trim((string) ($credentials['username'] ?? config('services.mg_bedbank.username', '')));
        $password = trim((string) ($credentials['password'] ?? config('services.mg_bedbank.password', '')));

        if ($baseUrl === '' || $agencyCode === '' || $username === '' || $password === '') {
            throw new RuntimeException(
                'MG Bedbank credentials are incomplete (base URL, agency code, username and password required).'
            );
        }

        $destination = $this->resolveDestination($request, $credentials);
        $occupancy = $this->parsePaxInfo($request->paxInfo);
        $payload = [
            'Login' => [
                'AgencyCode' => $agencyCode,
                'Username' => $username,
                'Password' => $password,
            ],
            'Nationality' => $this->credential($credentials, 'nationality', 'SG'),
            'Country' => $destination['country'],
            'City' => $destination['city'],
            'CheckIn' => $request->checkIn,
            'CheckOut' => $request->checkOut,
            'Rooms' => [
                'Room' => $this->buildRooms($occupancy),
            ],
            'Currency' => $this->credential($credentials, 'currency', 'INR'),
            'Language' => $this->credential($credentials, 'language', 'En'),
            'AvailFlag' => true,
            'DetailLevel' => $this->credential($credentials, 'detail_level', 'Basic'),
        ];

        $hotelCodes = $this->parseHotelCodes($credentials['hotel_codes'] ?? null);
        if ($hotelCodes !== []) {
            $payload['Hotels'] = ['Code' => $hotelCodes];
        }

        $timeout = (int) ($credentials['timeout'] ?? config('services.mg_bedbank.timeout', 30));
        $response = Http::timeout($timeout > 0 ? $timeout : 30)
            ->acceptJson()
            ->asJson()
            ->post($baseUrl . '/SearchHotel', $payload);

        if (! $response->successful()) {
            Log::warning('MG Bedbank SearchHotel failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'city' => $destination['city'],
                'check_in' => $request->checkIn,
                'check_out' => $request->checkOut,
            ]);

            throw new RuntimeException('MG Bedbank hotel search failed with HTTP ' . $response->status());
        }

        $body = $response->json();
        if (! is_array($body)) {
            throw new RuntimeException('MG Bedbank returned an invalid response.');
        }

        if (($body['status'] ?? false) !== true) {
            $message = trim((string) ($body['errorMessage'] ?? ''));
            $errorCode = trim((string) ($body['errorCode'] ?? ''));

            // JRVXML060 = no availability for the given criteria — an empty result, not a failure.
            if ($errorCode === 'JRVXML060') {
                return ['hotels' => [], 'provider' => $body];
            }

            $detail = $message !== '' ? $message : 'The supplier rejected the hotel search.';
            if ($errorCode !== '') {
                $detail .= " [{$errorCode}]";
            }

            throw new RuntimeException('MG Bedbank hotel search failed: ' . $detail);
        }

        $rawHotels = $body['hotels']['hotel'] ?? [];
        if (! is_array($rawHotels)) {
            $rawHotels = [];
        }

        return [
            'hotels' => array_values(array_map(
                fn (array $hotel) => $this->normalizeHotel($hotel, (string) ($body['currency'] ?? 'SGD')),
                array_filter($rawHotels, 'is_array'),
            )),
            'provider' => $body,
        ];
    }

    /**
     * @param  array<string, string|null>  $credentials
     * @return array{country: string, city: string}
     */
    private function resolveDestination(HotelSearchRequest $request, array $credentials): array
    {
        $country = $this->credential($credentials, 'country_code');
        $city = '';
        $mapJson = trim((string) ($credentials['destination_map'] ?? ''));

        if ($mapJson !== '') {
            $map = json_decode($mapJson, true);
            if (! is_array($map)) {
                throw new RuntimeException('MG Bedbank destination map must be valid JSON.');
            }

            $needle = strtolower(trim($request->cityName));
            foreach ($map as $cityName => $cityCode) {
                if (strtolower(trim((string) $cityName)) === $needle) {
                    $city = trim((string) $cityCode);
                    break;
                }
            }
        }

        if ($city === '') {
            $city = $this->credential($credentials, 'city_code');
        }

        // Permit an MG destination code to be supplied directly as the city value.
        if ($city === '' && preg_match('/^[A-Z]{2}-[A-Z0-9]{3,}$/i', trim($request->cityName))) {
            $city = strtoupper(trim($request->cityName));
        }

        if ($country === '' && str_contains($city, '-')) {
            $country = strtoupper((string) strtok($city, '-'));
        }

        if ($country === '' || $city === '') {
            throw new RuntimeException(
                "MG Bedbank destination is not configured for [{$request->cityName}]. "
                . 'Set Country Code and either Default City Code or Destination Map in API Credentials.'
            );
        }

        return ['country' => strtoupper($country), 'city' => strtoupper($city)];
    }

    /**
     * @return array{adults: int, children: int, child_ages: array<int, int>}
     */
    private function parsePaxInfo(string $paxInfo): array
    {
        $parts = explode('|', $paxInfo);
        $adults = max(1, (int) ($parts[0] ?? 1));
        $children = max(0, (int) ($parts[1] ?? 0));
        $ages = [];

        if (isset($parts[2]) && trim($parts[2]) !== '') {
            $ages = array_values(array_filter(
                array_map('intval', preg_split('/[,;:]/', $parts[2]) ?: []),
                fn (int $age) => $age > 0,
            ));
        }

        while (count($ages) < $children) {
            $ages[] = 8;
        }

        return [
            'adults' => $adults,
            'children' => $children,
            'child_ages' => array_slice($ages, 0, $children),
        ];
    }

    /**
     * MG rooms cap at 2 adults / 2 children each, so split larger pax across rooms.
     *
     * @param  array{adults: int, children: int, child_ages: array<int, int>}  $occupancy
     * @return array<int, array<string, string|bool>>
     */
    private function buildRooms(array $occupancy): array
    {
        $adultsLeft = $occupancy['adults'];
        $agesLeft = $occupancy['child_ages'];
        $rooms = [];
        $roomNo = 1;

        while ($adultsLeft > 0 || $agesLeft !== []) {
            $adults = min(2, max($adultsLeft, 0));
            if ($adults === 0 && $agesLeft !== []) {
                $adults = 1; // MG requires at least one adult per room.
            }
            $ages = array_splice($agesLeft, 0, 2);
            $adultsLeft -= $adults;

            $rooms[] = [
                'RoomNo' => (string) $roomNo++,
                'NoOfAdults' => (string) $adults,
                'NoOfChild' => $ages !== [] ? (string) count($ages) : '',
                'Child1Age' => isset($ages[0]) ? (string) $ages[0] : '',
                'Child2Age' => isset($ages[1]) ? (string) $ages[1] : '',
                'ExtraBed' => false,
            ];
        }

        return $rooms;
    }

    /**
     * @return array<int, string>
     */
    private function parseHotelCodes(?string $hotelCodes): array
    {
        if (! filled($hotelCodes)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            'trim',
            preg_split('/[\s,;]+/', (string) $hotelCodes) ?: [],
        ))));
    }

    /**
     * @param  array<string, mixed>  $hotel
     * @return array<string, mixed>
     */
    private function normalizeHotel(array $hotel, string $currency): array
    {
        $rooms = [];
        $roomDetails = $hotel['roomDetails'] ?? [];

        if (is_array($roomDetails)) {
            foreach ($roomDetails as $roomDetail) {
                if (is_array($roomDetail)) {
                    $rooms[] = $this->normalizeRoom($roomDetail, $currency);
                }
            }
        }

        usort($rooms, fn (array $a, array $b) => ($a['price']['actual'] ?? 0) <=> ($b['price']['actual'] ?? 0));
        $prices = array_values(array_filter(array_map(
            fn (array $room) => (float) ($room['price']['actual'] ?? 0),
            $rooms,
        ), fn (float $price) => $price > 0));

        return [
            'hotel_id' => (string) ($hotel['code'] ?? ''),
            'hotel_name' => (string) ($hotel['name'] ?? ''),
            'star_rating' => (string) ($hotel['rating'] ?? ''),
            'address' => '',
            'currency' => $currency !== '' ? $currency : 'SGD',
            'min_rate' => $prices !== [] ? min($prices) : null,
            'max_rate' => $prices !== [] ? max($prices) : null,
            'latitude' => (string) ($hotel['latitude'] ?? ''),
            'longitude' => (string) ($hotel['longitude'] ?? ''),
            'images' => [],
            'description' => '',
            'rooms' => $rooms,
            'supplier_code' => $this->code(),
            'raw' => $hotel,
        ];
    }

    /**
     * @param  array<string, mixed>  $room
     * @return array<string, mixed>
     */
    private function normalizeRoom(array $room, string $currency): array
    {
        $allocatedRooms = $room['rooms']['room'] ?? [];
        $firstAllocation = is_array($allocatedRooms) && isset($allocatedRooms[0]) && is_array($allocatedRooms[0])
            ? $allocatedRooms[0]
            : [];
        $netPrice = (float) ($room['netPrice'] ?? $firstAllocation['netPrice'] ?? 0);
        $mealPlan = trim((string) ($room['mealPlanName'] ?? $room['mealPlan'] ?? ''));
        $policies = $room['cancellationPolicies']['policy'] ?? [];
        if (! is_array($policies)) {
            $policies = [];
        }

        return [
            'room_id' => (string) ($room['code'] ?? ''),
            'room_name' => (string) ($room['name'] ?? ''),
            'rate_plan_id' => (string) ($firstAllocation['rateKey'] ?? ''),
            'rate_plan_name' => $mealPlan,
            'bed_type' => '',
            'extra_bed_type' => '',
            'meal_plan' => $mealPlan,
            'board_code' => (string) ($room['mealPlan'] ?? ''),
            'breakfast_included' => strtoupper((string) ($room['mealPlan'] ?? '')) !== 'RO',
            'max_occupancy' => (int) (($firstAllocation['noOfAdults'] ?? 0) + ($firstAllocation['noOfChild'] ?? 0)),
            'max_adult' => (int) ($firstAllocation['noOfAdults'] ?? 0),
            'max_child' => (int) ($firstAllocation['noOfChild'] ?? 0),
            'free_cancellation' => $policies === [],
            'price' => [
                'actual' => $netPrice,
                'tax' => 0.0,
                'taxValue' => 0.0,
            ],
            'currency_converted_price' => [
                'actual' => $netPrice,
                'tax' => 0.0,
                'taxValue' => 0.0,
            ],
            'currency' => $currency,
            'average_night_price' => (float) ($room['avgNightPrice'] ?? 0),
            'gross_price' => (float) ($room['grossPrice'] ?? 0),
            'can_hold' => (bool) ($room['canHold'] ?? false),
            'package_rate' => (bool) ($room['packageRate'] ?? false),
            'cancellation_policy' => $policies,
            'inclusions' => [],
            'raw' => $room,
        ];
    }

    /**
     * @param  array<string, string|null>  $credentials
     */
    private function credential(array $credentials, string $key, string $default = ''): string
    {
        $value = trim((string) ($credentials[$key] ?? config("services.mg_bedbank.{$key}", $default)));

        return $value !== '' ? $value : $default;
    }
}
