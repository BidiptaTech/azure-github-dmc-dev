<?php

namespace App\Services\HotelSuppliers\Adapters;

use App\Models\City;
use App\Services\HotelSuppliers\Contracts\TwoStepHotelSupplierAdapter;
use App\Services\HotelSuppliers\HotelSearchRequest;
use App\Services\HotelSuppliers\MgBedbank\MgBedbankClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * MG Bedbank needs three calls to price a city:
 *
 *   1. GetDestinations  → country/city codes, synced ahead of time onto our `cities`
 *                         table by `mg-bedbank:sync-destinations`.
 *   2. GetHotelList     → the hotel codes (and static content) for that city.
 *   3. SearchHotel      → live availability for those codes.
 *
 * Step 2 is cached because the catalogue is static relative to rates.
 *
 * The UI drives this two-step: `listHotels()` fills the hotel dropdown from the
 * catalogue, then `fetchHotelRooms()` prices only the hotel the user picked.
 */
class MgBedbankHotelAdapter implements TwoStepHotelSupplierAdapter
{
    /** MG returns this when nothing is available for the criteria — an empty result, not a failure. */
    private const NO_AVAILABILITY = 'JRVXML060';

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
        $client = new MgBedbankClient($credentials);
        $destination = $this->resolveDestination($request, $client);
        $catalogue = $this->hotelCatalogue($client, $destination);

        if ($catalogue === []) {
            Log::info('MG Bedbank returned no hotels for destination', $destination);

            return ['hotels' => [], 'provider' => ['status' => true, 'hotels' => ['hotel' => []]]];
        }

        $codes = $this->selectHotelCodes($client, array_keys($catalogue), $destination);
        $body = $this->searchAvailability($client, $request, $destination, $codes);

        if ($body === null) {
            return ['hotels' => [], 'provider' => ['status' => true, 'hotels' => ['hotel' => []]]];
        }

        $rawHotels = $body['hotels']['hotel'] ?? [];

        if (! is_array($rawHotels)) {
            $rawHotels = [];
        }

        $currency = (string) ($body['currency'] ?? 'SGD');

        return [
            'hotels' => array_values(array_map(
                fn (array $hotel) => $this->normalizeHotel(
                    $hotel,
                    $currency,
                    $catalogue[(string) ($hotel['code'] ?? '')] ?? [],
                    $body,
                    $destination,
                    $request,
                ),
                array_filter($rawHotels, 'is_array'),
            )),
            'provider' => $body,
        ];
    }

    /**
     * Step 1 of the UI flow: every hotel MG knows about in this city, without rates.
     *
     * @param  array<string, string|null>  $credentials
     * @return array{hotels: array<int, array<string, mixed>>, provider: mixed}
     */
    public function listHotels(HotelSearchRequest $request, array $credentials): array
    {
        $client = new MgBedbankClient($credentials);
        $destination = $this->resolveDestination($request, $client);
        $catalogue = $this->hotelCatalogue($client, $destination);

        // Deliberately ignores the `hotel_codes` allow-list and the per-search cap: the
        // dropdown shows the whole catalogue, and availability is checked per hotel later.
        $hotels = [];

        foreach ($catalogue as $code => $content) {
            $hotels[] = $this->catalogueHotel((string) $code, $content, $destination);
        }

        usort($hotels, fn (array $a, array $b) => strcasecmp((string) $a['hotel_name'], (string) $b['hotel_name']));

        Log::info('MG Bedbank hotel catalogue listed', [
            'destination' => $destination,
            'hotels' => count($hotels),
        ]);

        return [
            'hotels' => $hotels,
            'provider' => ['status' => true, 'destination' => $destination, 'noOfHotels' => count($hotels)],
        ];
    }

    /**
     * Step 2 of the UI flow: price the single hotel the user selected.
     *
     * @param  array<string, string|null>  $credentials
     * @return array{hotel: ?array<string, mixed>, session_id: ?string, provider: mixed}
     */
    public function fetchHotelRooms(HotelSearchRequest $request, string $hotelCode, array $credentials): array
    {
        $hotelCode = trim($hotelCode);

        if ($hotelCode === '') {
            throw new RuntimeException('A hotel code is required to check MG Bedbank availability.');
        }

        $client = new MgBedbankClient($credentials);
        $destination = $this->resolveDestination($request, $client);
        $catalogue = $this->hotelCatalogue($client, $destination);
        $content = $catalogue[$hotelCode] ?? [];

        $body = $this->searchAvailability($client, $request, $destination, [$hotelCode]);

        if ($body === null) {
            return [
                'hotel' => null,
                'session_id' => null,
                'provider' => ['status' => true, 'errorCode' => self::NO_AVAILABILITY, 'hotels' => ['hotel' => []]],
            ];
        }

        $rawHotels = array_values(array_filter($body['hotels']['hotel'] ?? [], 'is_array'));
        $rawHotel = null;

        foreach ($rawHotels as $candidate) {
            if ((string) ($candidate['code'] ?? '') === $hotelCode) {
                $rawHotel = $candidate;
                break;
            }
        }

        $rawHotel ??= $rawHotels[0] ?? null;

        if (! is_array($rawHotel)) {
            return ['hotel' => null, 'session_id' => $body['sessionID'] ?? null, 'provider' => $body];
        }

        $currency = (string) ($body['currency'] ?? 'SGD');

        return [
            'hotel' => $this->normalizeHotel($rawHotel, $currency, $content, $body, $destination, $request),
            'session_id' => (string) ($body['sessionID'] ?? '') ?: null,
            'provider' => $body,
        ];
    }

    /**
     * Returns null when MG replied "nothing available", which is an empty result rather than a failure.
     *
     * @param  array{country: string, city: string}  $destination
     * @param  array<int, string>  $codes
     * @return array<string, mixed>|null
     */
    private function searchAvailability(
        MgBedbankClient $client,
        HotelSearchRequest $request,
        array $destination,
        array $codes,
    ): ?array {
        $occupancy = $this->parsePaxInfo($request->paxInfo);

        $body = $client->searchHotel([
            'Nationality' => $client->credential('nationality', 'SG'),
            'Country' => $destination['country'],
            'City' => $destination['city'],
            'CheckIn' => $request->checkIn,
            'CheckOut' => $request->checkOut,
            'Rooms' => ['Room' => $this->buildRooms($occupancy, $request->roomCount())],
            'Currency' => $client->credential('currency', 'SGD'),
            'Language' => $client->credential('language', 'En'),
            'AvailFlag' => true,
            'DetailLevel' => $client->credential('detail_level', 'Basic'),
            'Hotels' => ['Code' => array_values($codes)],
        ]);

        if ($reason = $client->failureReason($body)) {
            if (trim((string) ($body['errorCode'] ?? '')) === self::NO_AVAILABILITY) {
                return null;
            }

            throw new RuntimeException('MG Bedbank hotel search failed: ' . $reason);
        }

        return $body;
    }

    /**
     * @return array{country: string, city: string}
     */
    private function resolveDestination(HotelSearchRequest $request, MgBedbankClient $client): array
    {
        $city = $this->findCity($request);

        if ($city && filled($city->mg_country_code) && filled($city->mg_city_code)) {
            return [
                'country' => strtoupper((string) $city->mg_country_code),
                'city' => strtoupper((string) $city->mg_city_code),
            ];
        }

        $country = $client->credential('country_code');
        $cityCode = $this->cityCodeFromMap($request, $client);

        if ($cityCode === '') {
            $cityCode = $client->credential('city_code');
        }

        // Permit an MG destination code to be supplied directly as the city value.
        if ($cityCode === '' && preg_match('/^[A-Z]{2}-[A-Z0-9]{3,}$/i', trim($request->cityName))) {
            $cityCode = strtoupper(trim($request->cityName));
        }

        if ($country === '' && str_contains($cityCode, '-')) {
            $country = strtoupper((string) strtok($cityCode, '-'));
        }

        if ($country === '' || $cityCode === '') {
            throw new RuntimeException(
                "MG Bedbank has no destination code for [{$request->cityName}]. "
                . 'Run `php artisan mg-bedbank:sync-destinations`, or set the codes on the city record.'
            );
        }

        return ['country' => strtoupper($country), 'city' => strtoupper($cityCode)];
    }

    private function findCity(HotelSearchRequest $request): ?City
    {
        if ($request->cityId) {
            $city = City::query()->where('city_id', $request->cityId)->first();

            if ($city) {
                return $city;
            }
        }

        return City::query()
            ->whereRaw('LOWER(name) = ?', [strtolower(trim($request->cityName))])
            ->first();
    }

    private function cityCodeFromMap(HotelSearchRequest $request, MgBedbankClient $client): string
    {
        $mapJson = $client->credential('destination_map');

        if ($mapJson === '') {
            return '';
        }

        $map = json_decode($mapJson, true);

        if (! is_array($map)) {
            throw new RuntimeException('MG Bedbank destination map must be valid JSON.');
        }

        $needle = strtolower(trim($request->cityName));

        foreach ($map as $cityName => $cityCode) {
            if (strtolower(trim((string) $cityName)) === $needle) {
                return trim((string) $cityCode);
            }
        }

        return '';
    }

    /**
     * Static hotel content for a destination, keyed by MG hotel code.
     *
     * @param  array{country: string, city: string}  $destination
     * @return array<string, array<string, mixed>>
     */
    private function hotelCatalogue(MgBedbankClient $client, array $destination): array
    {
        $ttl = (int) $client->credential('hotel_list_ttl', '1440');
        $environment = $client->credential('api_environment', 'demo') ?: 'demo';
        // Bump the version segment whenever the cached entry shape changes.
        $key = "mg_bedbank:hotel_list:v2:{$environment}:{$destination['country']}:{$destination['city']}";

        if ($ttl <= 0) {
            return $this->fetchHotelCatalogue($client, $destination);
        }

        return Cache::remember(
            $key,
            now()->addMinutes($ttl),
            fn () => $this->fetchHotelCatalogue($client, $destination),
        );
    }

    /**
     * @param  array{country: string, city: string}  $destination
     * @return array<string, array<string, mixed>>
     */
    private function fetchHotelCatalogue(MgBedbankClient $client, array $destination): array
    {
        $body = $client->getHotelList($destination['country'], $destination['city']);

        if ($reason = $client->failureReason($body)) {
            throw new RuntimeException('MG Bedbank hotel list failed: ' . $reason);
        }

        $catalogue = [];

        foreach ($body['hotels']['hotel'] ?? [] as $hotel) {
            if (! is_array($hotel)) {
                continue;
            }

            $code = trim((string) ($hotel['code'] ?? ''));

            if ($code === '') {
                continue;
            }

            $catalogue[$code] = $this->trimCatalogueEntry($hotel);
        }

        return $catalogue;
    }

    /**
     * Keeps only the fields we surface, so the cached payload stays small.
     *
     * @param  array<string, mixed>  $hotel
     * @return array<string, mixed>
     */
    private function trimCatalogueEntry(array $hotel): array
    {
        $address = is_array($hotel['address'] ?? null) ? $hotel['address'] : [];
        $geo = is_array($hotel['geoLocation'] ?? null) ? $hotel['geoLocation'] : [];
        $reservation = is_array($hotel['reservation'] ?? null) ? $hotel['reservation'] : [];
        $images = $this->collectImages($hotel['photos'] ?? null);

        return [
            'name' => (string) ($hotel['name'] ?? ''),
            // MG sends "4 Star"; keep the label and let the caller pull the digit out.
            'rating' => (string) ($hotel['rating'] ?? ''),
            'type' => (string) ($hotel['type'] ?? ''),
            'chain_name' => (string) ($hotel['chainName'] ?? ''),
            'brand_name' => (string) ($hotel['brandName'] ?? ''),
            'mg_preferred' => filter_var($hotel['isMGPreferred'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'hotel_currency' => (string) ($hotel['currency'] ?? ''),
            'time_zone' => (string) ($hotel['timeZone'] ?? ''),
            'website' => (string) ($hotel['webSite'] ?? ''),
            'phone' => (string) ($reservation['telephone'] ?? ''),
            'email' => (string) ($reservation['email'] ?? ''),
            'check_in_time' => (string) ($hotel['checkInTime'] ?? ''),
            'check_out_time' => (string) ($hotel['checkOutTime'] ?? ''),
            'no_of_rooms' => (string) ($hotel['noOfRooms'] ?? ''),
            'short_description' => (string) ($hotel['shortDescription'] ?? ''),
            'long_description' => (string) ($hotel['longDescription'] ?? ''),
            'address' => $this->formatAddress($address),
            'address_line1' => (string) ($address['line1'] ?? ''),
            'address_line2' => (string) ($address['line2'] ?? ''),
            'area' => (string) ($address['area'] ?? ''),
            'landmark' => (string) ($address['landmark'] ?? ''),
            'zip_code' => $this->cleanAddressPart($address['zipCode'] ?? ''),
            'city_name' => (string) ($address['cityName'] ?? ''),
            'city_code' => (string) ($address['cityCode'] ?? ''),
            'country_name' => (string) ($address['countryName'] ?? ''),
            'country_code' => (string) ($address['countryCode'] ?? ''),
            'latitude' => (string) ($geo['latitude'] ?? ''),
            'longitude' => (string) ($geo['longitude'] ?? ''),
            'images' => $images,
            'main_image' => $images[0] ?? '',
            'rooms' => $this->catalogueRooms($hotel['rooms']['room'] ?? null),
        ];
    }

    /**
     * Static room definitions from GetHotelList, keyed by room code so live SearchHotel
     * rooms (which only carry a code and name) can be enriched with size and occupancy.
     *
     * @return array<string, array<string, mixed>>
     */
    private function catalogueRooms(mixed $rooms): array
    {
        if (! is_array($rooms)) {
            return [];
        }

        $parsed = [];

        foreach ($rooms as $room) {
            if (! is_array($room)) {
                continue;
            }

            $code = trim((string) ($room['code'] ?? ''));

            if ($code === '') {
                continue;
            }

            $images = $this->collectImages($room['photos'] ?? null);

            $parsed[$code] = [
                'code' => $code,
                'name' => (string) ($room['name'] ?? ''),
                'size' => (string) ($room['size'] ?? ''),
                'max_occupancy' => (int) ($room['maxOccupancy'] ?? 0),
                'max_adult' => (int) ($room['maxAdult'] ?? 0),
                'max_children' => (int) ($room['maxChildren'] ?? 0),
                'smoking_allowed' => filter_var($room['isSmokingAllowed'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'image' => $images[0] ?? '',
            ];
        }

        return $parsed;
    }

    /**
     * `photos.image` arrives as a list, as null, or missing entirely; main photo first.
     *
     * @return array<int, string>
     */
    private function collectImages(mixed $photos): array
    {
        $images = [];
        $main = [];

        foreach ((is_array($photos) ? ($photos['image'] ?? []) : []) ?: [] as $image) {
            if (! is_array($image) || ! filled($image['url'] ?? null)) {
                continue;
            }

            if (filter_var($image['isMain'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $main[] = (string) $image['url'];
            } else {
                $images[] = (string) $image['url'];
            }
        }

        return array_values(array_unique(array_merge($main, $images)));
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function formatAddress(array $address): string
    {
        $parts = array_filter(
            array_map(
                fn ($part) => $this->cleanAddressPart($part),
                [
                    $address['line1'] ?? '',
                    $address['line2'] ?? '',
                    $address['area'] ?? '',
                    $address['landmark'] ?? '',
                    $address['zipCode'] ?? '',
                ],
            ),
            fn (string $part) => $part !== '',
        );

        return implode(', ', array_unique($parts));
    }

    /**
     * MG fills unknown address parts with placeholders like "0" or "N/A".
     */
    private function cleanAddressPart(mixed $part): string
    {
        $value = trim((string) $part);

        return in_array(strtoupper($value), ['', '0', 'NA', 'N/A', 'NULL'], true) ? '' : $value;
    }

    /**
     * @param  array<int, string>  $available
     * @param  array{country: string, city: string}  $destination
     * @return array<int, string>
     */
    private function selectHotelCodes(MgBedbankClient $client, array $available, array $destination): array
    {
        $configured = $this->parseHotelCodes($client->credential('hotel_codes'));

        if ($configured !== []) {
            $intersection = array_values(array_intersect($configured, $available));

            if ($intersection !== []) {
                return $intersection;
            }

            Log::warning('MG Bedbank configured hotel codes are not in this destination; falling back to the full list.', [
                'configured' => $configured,
                'destination' => $destination,
            ]);
        }

        $max = (int) $client->credential('max_hotel_codes', '200');

        if ($max > 0 && count($available) > $max) {
            Log::info('MG Bedbank hotel list truncated for search', [
                'destination' => $destination,
                'available' => count($available),
                'sent' => $max,
            ]);

            return array_slice($available, 0, $max);
        }

        return $available;
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
     * Spreads the pax across the number of rooms the operator asked for.
     *
     * MG prices per room block, so the same pax costs roughly twice as much over two
     * rooms as it does in one. The room count therefore comes from the booking form
     * rather than being inferred: MG itself accepts as many adults in one room as the
     * room's occupancy allows, and replies with no availability when it cannot.
     *
     * Children are the only hard cap — the payload has just Child1Age/Child2Age, so a
     * room cannot carry more than two, and the room count grows if that is exceeded.
     *
     * @param  array{adults: int, children: int, child_ages: array<int, int>}  $occupancy
     * @return array<int, array<string, string|bool>>
     */
    private function buildRooms(array $occupancy, int $requestedRooms = 1): array
    {
        $adults = max(1, $occupancy['adults']);
        $agesLeft = $occupancy['child_ages'];

        // MG needs at least one adult per room, and at most two children in one.
        $roomCount = max(1, min($requestedRooms, $adults));
        $roomCount = max($roomCount, (int) ceil(count($agesLeft) / 2));

        $baseAdults = intdiv($adults, $roomCount);
        $extraAdults = $adults % $roomCount;

        $rooms = [];

        for ($roomNo = 1; $roomNo <= $roomCount; $roomNo++) {
            $roomAdults = $baseAdults + ($roomNo <= $extraAdults ? 1 : 0);
            $ages = array_splice($agesLeft, 0, 2);

            $rooms[] = [
                'RoomNo' => (string) $roomNo,
                'NoOfAdults' => (string) $roomAdults,
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
     * A catalogue-only hotel: everything GetHotelList knows, with no rates yet.
     *
     * @param  array<string, mixed>  $content
     * @param  array{country: string, city: string}  $destination
     * @return array<string, mixed>
     */
    private function catalogueHotel(string $code, array $content, array $destination): array
    {
        return [
            'hotel_id' => $code,
            'hotel_name' => (string) ($content['name'] ?? $code),
            'star_rating' => $this->parseStarRating($content['rating'] ?? ''),
            'rating_label' => (string) ($content['rating'] ?? ''),
            'property_type' => (string) ($content['type'] ?? ''),
            'address' => (string) ($content['address'] ?? ''),
            'area' => (string) ($content['area'] ?? ''),
            'city_name' => (string) ($content['city_name'] ?? ''),
            'country_name' => (string) ($content['country_name'] ?? ''),
            'currency' => '',
            'min_rate' => null,
            'max_rate' => null,
            'latitude' => (string) ($content['latitude'] ?? ''),
            'longitude' => (string) ($content['longitude'] ?? ''),
            // A city can hold thousands of hotels, so the list stays lean; the full content
            // comes back with the rooms once the user picks one.
            'images' => array_slice($content['images'] ?? [], 0, 1),
            'description' => (string) ($content['short_description'] ?? ''),
            'chain_name' => (string) ($content['chain_name'] ?? ''),
            'brand_name' => (string) ($content['brand_name'] ?? ''),
            'website' => (string) ($content['website'] ?? ''),
            'check_in_time' => (string) ($content['check_in_time'] ?? ''),
            'check_out_time' => (string) ($content['check_out_time'] ?? ''),
            'no_of_rooms' => (string) ($content['no_of_rooms'] ?? ''),
            'rooms' => [],
            'rates_pending' => true,
            'destination' => $destination,
            'supplier_code' => $this->code(),
        ];
    }

    /**
     * @param  array<string, mixed>  $hotel
     * @param  array<string, mixed>  $content  Static content from GetHotelList.
     * @param  array<string, mixed>  $body  The full SearchHotel response (session id, dates).
     * @param  array{country: string, city: string}  $destination
     * @return array<string, mixed>
     */
    private function normalizeHotel(
        array $hotel,
        string $currency,
        array $content,
        array $body = [],
        array $destination = ['country' => '', 'city' => ''],
        ?HotelSearchRequest $request = null,
    ): array {
        $hotelSummary = $this->bookingHotelSummary($hotel, $content, $destination);
        $context = [
            'supplier_code' => $this->code(),
            'session_id' => (string) ($body['sessionID'] ?? '') ?: null,
            'currency' => $currency !== '' ? $currency : 'SGD',
            'check_in' => (string) ($body['checkIn'] ?? $request?->checkIn ?? ''),
            'check_out' => (string) ($body['checkOut'] ?? $request?->checkOut ?? ''),
            'search' => [
                'country' => $destination['country'] ?? '',
                'city' => $destination['city'] ?? '',
                'city_name' => $request?->cityName ?? '',
                'pax_info' => $request?->paxInfo ?? '',
                // Recheck must ask for the same number of room blocks or MG quotes a
                // different total for identical pax.
                'rooms' => $request?->roomCount() ?? 1,
            ],
            'hotel' => $hotelSummary,
            'static_rooms' => is_array($content['rooms'] ?? null) ? $content['rooms'] : [],
        ];

        $rooms = [];
        $roomDetails = $hotel['roomDetails'] ?? [];

        if (is_array($roomDetails)) {
            foreach ($roomDetails as $roomDetail) {
                if (is_array($roomDetail)) {
                    $rooms[] = $this->normalizeRoom($roomDetail, $currency, $context);
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
            'hotel_name' => (string) ($hotel['name'] ?? $content['name'] ?? ''),
            'star_rating' => $this->parseStarRating($hotel['rating'] ?? $content['rating'] ?? ''),
            'rating_label' => (string) ($content['rating'] ?? $hotel['rating'] ?? ''),
            'property_type' => (string) ($content['type'] ?? ''),
            'address' => (string) ($content['address'] ?? ''),
            'area' => (string) ($content['area'] ?? ''),
            'phone' => (string) ($content['phone'] ?? ''),
            'email' => (string) ($content['email'] ?? ''),
            'brand_name' => (string) ($content['brand_name'] ?? ''),
            'no_of_rooms' => (string) ($content['no_of_rooms'] ?? ''),
            'currency' => $currency !== '' ? $currency : 'SGD',
            'min_rate' => $prices !== [] ? min($prices) : null,
            'max_rate' => $prices !== [] ? max($prices) : null,
            'latitude' => (string) ($hotel['latitude'] ?? $content['latitude'] ?? ''),
            'longitude' => (string) ($hotel['longitude'] ?? $content['longitude'] ?? ''),
            'images' => $content['images'] ?? [],
            'description' => (string) ($content['short_description'] ?? ''),
            'long_description' => (string) ($content['long_description'] ?? ''),
            'chain_name' => (string) ($content['chain_name'] ?? ''),
            'website' => (string) ($content['website'] ?? ''),
            'check_in_time' => (string) ($content['check_in_time'] ?? ''),
            'check_out_time' => (string) ($content['check_out_time'] ?? ''),
            'city_name' => (string) ($content['city_name'] ?? ''),
            'country_name' => (string) ($content['country_name'] ?? ''),
            'rooms' => $rooms,
            'rates_pending' => false,
            'session_id' => $context['session_id'],
            'destination' => $destination,
            'supplier_code' => $this->code(),
            'raw' => $hotel,
        ];
    }

    /**
     * The hotel facts a booking confirmation needs, merged from both MG calls.
     *
     * @param  array<string, mixed>  $hotel
     * @param  array<string, mixed>  $content
     * @param  array{country: string, city: string}  $destination
     * @return array<string, mixed>
     */
    private function bookingHotelSummary(array $hotel, array $content, array $destination): array
    {
        return [
            'code' => (string) ($hotel['code'] ?? ''),
            'name' => (string) ($hotel['name'] ?? $content['name'] ?? ''),
            'rating' => (string) ($hotel['rating'] ?? $content['rating'] ?? ''),
            'type' => (string) ($content['type'] ?? ''),
            'chain_name' => (string) ($content['chain_name'] ?? ''),
            'brand_name' => (string) ($content['brand_name'] ?? ''),
            'address' => (string) ($content['address'] ?? ''),
            'area' => (string) ($content['area'] ?? ''),
            'zip_code' => (string) ($content['zip_code'] ?? ''),
            'city_name' => (string) ($content['city_name'] ?? ''),
            'country_name' => (string) ($content['country_name'] ?? ''),
            'country_code' => $destination['country'] ?? '',
            'city_code' => $destination['city'] ?? '',
            'latitude' => (string) ($hotel['latitude'] ?? $content['latitude'] ?? ''),
            'longitude' => (string) ($hotel['longitude'] ?? $content['longitude'] ?? ''),
            'check_in_time' => (string) ($content['check_in_time'] ?? ''),
            'check_out_time' => (string) ($content['check_out_time'] ?? ''),
            'website' => (string) ($content['website'] ?? ''),
            'phone' => (string) ($content['phone'] ?? ''),
            'email' => (string) ($content['email'] ?? ''),
            'image' => (string) (($content['images'][0] ?? '') ?: ''),
        ];
    }

    /**
     * MG sends ratings as "4 Star", or "0 Star"/blank when the hotel is unrated.
     */
    private function parseStarRating(mixed $rating): string
    {
        if (preg_match('/([1-7])/', (string) $rating, $matches)) {
            return $matches[1];
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $room
     * @param  array<string, mixed>  $context  Session/hotel facts shared by every room of this search.
     * @return array<string, mixed>
     */
    private function normalizeRoom(array $room, string $currency, array $context = []): array
    {
        $allocatedRooms = $room['rooms']['room'] ?? [];
        $allocations = is_array($allocatedRooms) ? array_values(array_filter($allocatedRooms, 'is_array')) : [];
        $firstAllocation = $allocations[0] ?? [];
        $netPrice = (float) ($room['netPrice'] ?? $firstAllocation['netPrice'] ?? 0);
        $mealPlan = trim((string) ($room['mealPlanName'] ?? $room['mealPlan'] ?? ''));
        $policies = $room['cancellationPolicies']['policy'] ?? [];
        if (! is_array($policies)) {
            $policies = [];
        }

        // SearchHotel only gives a code and name; GetHotelList knows the real capacity.
        $roomCode = (string) ($room['code'] ?? '');
        $static = $context['static_rooms'][$roomCode] ?? [];
        $bookedAdults = (int) ($firstAllocation['noOfAdults'] ?? 0);
        $bookedChildren = (int) ($firstAllocation['noOfChild'] ?? 0);

        return [
            'room_id' => $roomCode,
            'room_name' => (string) ($room['name'] ?? $static['name'] ?? ''),
            'rate_plan_id' => (string) ($firstAllocation['rateKey'] ?? ''),
            'rate_plan_name' => $mealPlan,
            'bed_type' => '',
            'extra_bed_type' => '',
            'meal_plan' => $mealPlan,
            'board_code' => (string) ($room['mealPlan'] ?? ''),
            'breakfast_included' => strtoupper((string) ($room['mealPlan'] ?? '')) !== 'RO',
            'max_occupancy' => (int) ($static['max_occupancy'] ?? 0) ?: ($bookedAdults + $bookedChildren),
            'max_adult' => (int) ($static['max_adult'] ?? 0) ?: $bookedAdults,
            'max_child' => (int) ($static['max_children'] ?? 0) ?: $bookedChildren,
            'booked_adults' => $bookedAdults,
            'booked_children' => $bookedChildren,
            'room_size' => (string) ($static['size'] ?? ''),
            'smoking_allowed' => (bool) ($static['smoking_allowed'] ?? false),
            'images' => filled($static['image'] ?? null) ? [(string) $static['image']] : [],
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
            'booking' => $this->bookingPayload($room, $allocations, $policies, $context, $static),
            'raw' => $room,
        ];
    }

    /**
     * Everything needed to confirm this exact rate later: session, hotel, room, rate keys,
     * meal plan, cancellation terms and the untouched supplier payload.
     *
     * @param  array<string, mixed>  $room
     * @param  array<int, array<string, mixed>>  $allocations
     * @param  array<int, mixed>  $policies
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $static  Room definition from GetHotelList.
     * @return array<string, mixed>
     */
    private function bookingPayload(
        array $room,
        array $allocations,
        array $policies,
        array $context,
        array $static = [],
    ): array {
        $rateKeys = [];

        foreach ($allocations as $allocation) {
            $rateKey = trim((string) ($allocation['rateKey'] ?? ''));

            if ($rateKey !== '') {
                $rateKeys[] = $rateKey;
            }
        }

        return [
            'supplier_code' => $context['supplier_code'] ?? $this->code(),
            'session_id' => $context['session_id'] ?? null,
            'currency' => $context['currency'] ?? 'SGD',
            'check_in' => $context['check_in'] ?? '',
            'check_out' => $context['check_out'] ?? '',
            'search' => $context['search'] ?? [],
            'hotel' => $context['hotel'] ?? [],
            'room' => [
                'code' => (string) ($room['code'] ?? ''),
                'name' => (string) ($room['name'] ?? ''),
                'meal_plan_code' => (string) ($room['mealPlan'] ?? ''),
                'meal_plan_name' => (string) ($room['mealPlanName'] ?? ''),
                'cancellation_policy_type' => (string) ($room['cancellationPolicyType'] ?? ''),
                'promo_code' => (string) ($room['promoCode'] ?? ''),
                'avail_flag' => (bool) ($room['availFlag'] ?? false),
                'can_hold' => (bool) ($room['canHold'] ?? false),
                'package_rate' => (bool) ($room['packageRate'] ?? false),
                'net_price' => (float) ($room['netPrice'] ?? 0),
                'gross_price' => (float) ($room['grossPrice'] ?? 0),
                'avg_night_price' => (float) ($room['avgNightPrice'] ?? 0),
                'b2b_markup' => (float) ($room['b2BMarkup'] ?? 0),
                'msp' => (string) ($room['msp'] ?? ''),
                'rate_key' => $rateKeys[0] ?? '',
                'rate_keys' => $rateKeys,
                'allocations' => $allocations,
                'cancellation_policies' => $policies,
                'messages' => $this->roomMessages($room),
                'size' => (string) ($static['size'] ?? ''),
                'max_occupancy' => (int) ($static['max_occupancy'] ?? 0),
                'max_adult' => (int) ($static['max_adult'] ?? 0),
                'max_children' => (int) ($static['max_children'] ?? 0),
                'image' => (string) ($static['image'] ?? ''),
            ],
            'raw_room' => $room,
            'fetched_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>  $room
     * @return array<int, string>
     */
    private function roomMessages(array $room): array
    {
        $messages = [];

        foreach ($room['messages']['message'] ?? [] as $message) {
            if (! is_array($message)) {
                continue;
            }

            $content = trim((string) ($message['content'] ?? ''));

            if ($content !== '') {
                $messages[] = $content;
            }
        }

        return $messages;
    }
}
