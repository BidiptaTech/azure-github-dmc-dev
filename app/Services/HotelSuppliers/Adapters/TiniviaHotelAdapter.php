<?php

namespace App\Services\HotelSuppliers\Adapters;

use App\Services\HotelSuppliers\Contracts\HotelSupplierAdapter;
use App\Services\HotelSuppliers\HotelSearchRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class TiniviaHotelAdapter implements HotelSupplierAdapter
{
    public function code(): string
    {
        return 'tinivia';
    }

    /**
     * @param  array<string, string|null>  $credentials
     * @return array{hotels: array<int, array<string, mixed>>, provider: mixed}
     */
    public function fetchHotels(HotelSearchRequest $request, array $credentials): array
    {
        $baseUrl = rtrim((string) ($credentials['base_url'] ?? ''), '/');
        $apiKey = trim((string) ($credentials['api_key'] ?? ''));

        if ($baseUrl === '') {
            $baseUrl = rtrim((string) config('services.tiniva.base_url', ''), '/');
        }
        if ($apiKey === '') {
            $apiKey = trim((string) config('services.tiniva.api_key', ''));
        }

        if ($baseUrl === '' || $apiKey === '') {
            throw new RuntimeException('Tinivia credentials are incomplete (base_url and api_key required).');
        }

        $payload = $request->toPayload();

        $headers = [
            'apikey' => $apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        $jwt = trim((string) ($credentials['jwt'] ?? ''));
        if ($jwt === '') {
            $jwt = trim((string) config('services.tiniva.jwt', ''));
        }
        if ($jwt !== '') {
            $headers['Jwt'] = $jwt;
        }

        $entityId = trim((string) ($credentials['entity_id'] ?? ''));
        if ($entityId === '') {
            $entityId = trim((string) config('services.tiniva.entity_id', ''));
        }
        if ($entityId !== '') {
            $headers['entityId'] = $entityId;
        }

        $timeout = (int) ($credentials['timeout'] ?? config('services.tiniva.timeout', 30));

        $response = Http::timeout($timeout > 0 ? $timeout : 30)
            ->withHeaders($headers)
            ->acceptJson()
            ->asJson()
            ->post($baseUrl . '/api/ext/fetchHotels', $payload);

        if (! $response->successful()) {
            Log::warning('Tinivia fetchHotels failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload,
            ]);

            throw new RuntimeException('Tinivia hotel search failed with HTTP ' . $response->status());
        }

        $body = $response->json();
        $rawHotels = $this->extractRawHotels($body);

        return [
            'hotels' => array_map(fn (array $item) => $this->normalizeHotel($item), $rawHotels),
            'provider' => $body,
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

        if (isset($body['hotels']) && is_array($body['hotels'])) {
            return $body['hotels'];
        }

        if (isset($body['HotelDetails']) && is_array($body['HotelDetails'])) {
            return $body['HotelDetails'];
        }

        if (isset($body['provider']['HotelDetails']) && is_array($body['provider']['HotelDetails'])) {
            return $body['provider']['HotelDetails'];
        }

        if (isset($body['data']) && is_array($body['data'])) {
            return $body['data'];
        }

        if (isset($body['results']) && is_array($body['results'])) {
            return $body['results'];
        }

        return array_is_list($body) ? $body : [];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function normalizeHotel(array $item): array
    {
        $property = is_array($item['propertyDetail'] ?? null) ? $item['propertyDetail'] : [];
        $images = $property['hotelImageUrl'] ?? $item['images'] ?? [];
        if (! is_array($images)) {
            $images = [];
        }

        $rooms = [];
        foreach ($item['rooms'] ?? [] as $room) {
            if (! is_array($room)) {
                continue;
            }
            $rooms[] = $this->normalizeRoom($room);
        }

        [$minRate, $maxRate] = $this->resolveRateRange($rooms);

        return [
            'hotel_id' => (string) ($item['hotelId'] ?? $item['hotel_id'] ?? $property['productId'] ?? ''),
            'hotel_name' => (string) ($item['hotelName'] ?? $property['hotelName'] ?? $item['name'] ?? ''),
            'star_rating' => (string) ($item['starRating'] ?? $item['star_rating'] ?? ''),
            'property_type' => (string) ($item['propertyType'] ?? $item['property_type'] ?? ''),
            'address' => (string) ($property['address'] ?? $item['address'] ?? ''),
            'currency' => (string) ($item['currency'] ?? 'INR'),
            'images' => $images,
            'description' => (string) ($property['description'] ?? $item['description'] ?? ''),
            'rooms' => $rooms,
            'min_rate' => $minRate,
            'max_rate' => $maxRate,
            'supplier_code' => $this->code(),
            'raw' => $item,
        ];
    }

    /**
     * @param  array<string, mixed>  $room
     * @return array<string, mixed>
     */
    private function normalizeRoom(array $room): array
    {
        $price = $this->normalizePriceBlock(is_array($room['price'] ?? null) ? $room['price'] : null);
        $currencyConvertedPrice = $this->normalizePriceBlock(
            is_array($room['currencyConvertedPrice'] ?? null) ? $room['currencyConvertedPrice'] : null,
        );

        if (($currencyConvertedPrice['actual'] ?? 0) <= 0 && ($price['actual'] ?? 0) > 0) {
            $currencyConvertedPrice = $price;
        }

        return [
            'room_id' => (string) ($room['roomId'] ?? $room['room_id'] ?? ''),
            'room_name' => (string) ($room['roomName'] ?? $room['roomType'] ?? $room['name'] ?? ''),
            'rate_plan_id' => (string) ($room['ratePlanId'] ?? $room['rate_plan_id'] ?? ''),
            'rate_plan_name' => (string) ($room['ratePlanName'] ?? $room['rate_plan_name'] ?? ''),
            'bed_type' => (string) ($room['bedType'] ?? $room['bed_type'] ?? ''),
            'extra_bed_type' => (string) ($room['extraBedType'] ?? $room['extra_bed_type'] ?? ''),
            'meal_plan' => (string) ($room['mealPlanName'] ?? $room['meal_plan'] ?? ''),
            'breakfast_included' => (bool) ($room['breakFast'] ?? $room['breakfast_included'] ?? false),
            'max_occupancy' => (int) ($room['maxOccupancy'] ?? $room['max_occupancy'] ?? 0),
            'max_adult' => (int) ($room['maxAdult'] ?? $room['max_adult'] ?? 0),
            'max_child' => (int) ($room['maxChild'] ?? $room['max_child'] ?? 0),
            'bedroom_count' => (int) ($room['bedRoom'] ?? $room['bedroom_count'] ?? 0),
            'bathroom_count' => (int) ($room['bathRoom'] ?? $room['bathroom_count'] ?? 0),
            'living_room_count' => (int) ($room['livingRoom'] ?? $room['living_room_count'] ?? 0),
            'free_cancellation' => (bool) ($room['freeCancellation'] ?? $room['free_cancellation'] ?? false),
            'price' => $price,
            'currency_converted_price' => $currencyConvertedPrice,
            'daywise_price' => is_array($room['daywisePrice'] ?? null) ? $room['daywisePrice'] : [],
            'currency_converted_daywise_price' => is_array($room['currencyConvertedDaywisePrice'] ?? null)
                ? $room['currencyConvertedDaywisePrice']
                : [],
            'inclusions' => is_array($room['inclusions'] ?? null) ? $room['inclusions'] : [],
            'cancellation_policy' => is_array($room['cancellationPolicy'] ?? null) ? $room['cancellationPolicy'] : [],
            'raw' => $room,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $block
     * @return array<string, mixed>
     */
    private function normalizePriceBlock(?array $block): array
    {
        if (! is_array($block)) {
            return [
                'actual' => 0.0,
                'taxValue' => 0.0,
                'tax' => 0.0,
            ];
        }

        $actual = (float) ($block['actual'] ?? $block['discounted'] ?? 0);
        $tax = (float) ($block['taxValue'] ?? $block['tax'] ?? 0);

        return array_merge($block, [
            'actual' => $actual,
            'taxValue' => $tax,
            'tax' => $tax,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rooms
     * @return array{0: ?float, 1: ?float}
     */
    private function resolveRateRange(array $rooms): array
    {
        $prices = [];

        foreach ($rooms as $room) {
            $price = (float) (
                $room['currency_converted_price']['actual']
                ?? $room['price']['actual']
                ?? 0
            );

            if ($price > 0) {
                $prices[] = $price;
            }
        }

        if ($prices === []) {
            return [null, null];
        }

        return [min($prices), max($prices)];
    }
}
