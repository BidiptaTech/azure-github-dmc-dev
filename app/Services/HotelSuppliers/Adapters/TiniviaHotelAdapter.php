<?php

namespace App\Services\HotelSuppliers\Adapters;

use App\Services\HotelSuppliers\Contracts\HotelSupplierAdapter;
use App\Services\HotelSuppliers\HotelSearchRequest;
use App\Services\HotelSuppliers\Tinivia\TiniviaClient;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Tinivia prices the whole city in one `fetchHotels` call, so search stays single-step.
 *
 * Booking needs a second call: `fetchHotels` hands back a rate plan id, but
 * `confirmBookingRequest` only accepts the short-lived `roomRateKey` that
 * `checkRoomAvailability` issues for one property. `fetchHotelRooms()` is that call.
 */
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
        $client = new TiniviaClient($credentials);
        $payload = $request->toPayload();

        $body = $client->fetchHotels($payload);
        $rawHotels = $this->extractRawHotels($body);

        // Only trust the error keys when nothing usable came back — a populated
        // response occasionally carries advisory fields with those names.
        if ($rawHotels === [] && ($reason = $client->failureReason($body))) {
            Log::warning('Tinivia fetchHotels rejected', [
                'reason' => $reason,
                'payload' => $payload,
            ]);

            throw new RuntimeException('Tinivia hotel search failed: ' . $reason);
        }

        return [
            'hotels' => array_map(fn (array $item) => $this->normalizeHotel($item), $rawHotels),
            'provider' => $body,
        ];
    }

    /**
     * Live availability for one property, keyed by Tinivia's `productId` (the hotel id).
     *
     * @param  array<string, string|null>  $credentials
     * @return array{hotel: ?array<string, mixed>, session_id: ?string, provider: mixed}
     */
    public function fetchHotelRooms(HotelSearchRequest $request, string $hotelCode, array $credentials): array
    {
        $hotelCode = trim($hotelCode);

        if ($hotelCode === '') {
            throw new RuntimeException('A hotel code is required to check Tinivia availability.');
        }

        $client = new TiniviaClient($credentials);

        $payload = [
            'checkIn' => $request->checkIn,
            'checkOut' => $request->checkOut,
            'productId' => $hotelCode,
            'paxInfo' => $request->paxInfo,
        ];

        $body = $client->checkRoomAvailability($payload);
        $rawHotels = $this->extractRawHotels($body);

        if ($rawHotels === [] && ($reason = $client->failureReason($body))) {
            Log::warning('Tinivia checkRoomAvailability rejected', [
                'reason' => $reason,
                'payload' => $payload,
            ]);

            throw new RuntimeException('Tinivia availability check failed: ' . $reason);
        }

        $rawHotel = null;

        foreach ($rawHotels as $candidate) {
            if ((string) ($candidate['hotelId'] ?? $candidate['hotel_id'] ?? '') === $hotelCode) {
                $rawHotel = $candidate;
                break;
            }
        }

        $rawHotel ??= $rawHotels[0] ?? null;

        if (! is_array($rawHotel)) {
            return ['hotel' => null, 'session_id' => null, 'provider' => $body];
        }

        return [
            'hotel' => $this->normalizeHotel($rawHotel),
            'session_id' => null,
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

        foreach (['hotels', 'hotelDetails', 'HotelDetails', 'data', 'results'] as $key) {
            if (isset($body[$key]) && is_array($body[$key])) {
                return array_values(array_filter($body[$key], 'is_array'));
            }
        }

        if (isset($body['provider']['HotelDetails']) && is_array($body['provider']['HotelDetails'])) {
            return array_values(array_filter($body['provider']['HotelDetails'], 'is_array'));
        }

        return array_is_list($body) ? array_values(array_filter($body, 'is_array')) : [];
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
            'address' => $this->stringifyAddress($property['address'] ?? $item['address'] ?? ''),
            'currency' => (string) ($item['currency'] ?? $item['targetCurrency'] ?? 'INR'),
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

        $mealPlan = is_array($room['mealPlan'] ?? null) ? $room['mealPlan'] : [];

        return [
            'room_id' => (string) ($room['roomId'] ?? $room['room_id'] ?? ''),
            'room_name' => (string) ($room['roomName'] ?? $room['roomType'] ?? $room['name'] ?? ''),
            'rate_plan_id' => (string) ($room['ratePlanId'] ?? $room['rate_plan_id'] ?? ''),
            'rate_plan_name' => (string) ($room['ratePlanName'] ?? $room['rate_plan_name'] ?? ''),
            // Only checkRoomAvailability issues this, and confirmBookingRequest needs it.
            'room_rate_key' => (string) ($room['roomRateKey'] ?? $room['room_rate_key'] ?? ''),
            'bed_type' => (string) ($room['bedType'] ?? $room['bed_type'] ?? ''),
            'extra_bed_type' => (string) ($room['extraBedType'] ?? $room['extra_bed_type'] ?? ''),
            'meal_plan' => (string) ($room['mealPlanName'] ?? $room['meal_plan'] ?? ''),
            'meal_plan_label' => (string) ($mealPlan['mealPlanName'] ?? ''),
            'breakfast_included' => (bool) ($room['breakFast'] ?? $mealPlan['breakFastIncluded'] ?? $room['breakfast_included'] ?? false),
            'max_occupancy' => (int) ($room['maxOccupancy'] ?? $room['max_occupancy'] ?? 0),
            'max_adult' => (int) ($room['maxAdult'] ?? $room['max_adult'] ?? 0),
            'max_child' => (int) ($room['maxChild'] ?? $room['max_child'] ?? 0),
            'available_rooms' => (int) ($room['availableRooms'] ?? $room['available_rooms'] ?? 0),
            'is_available' => array_key_exists('isAvailable', $room)
                ? (bool) $room['isAvailable']
                : true,
            'bedroom_count' => (int) ($room['bedRoom'] ?? $room['bedroom_count'] ?? 0),
            'bathroom_count' => (int) ($room['bathRoom'] ?? $room['bathroom_count'] ?? 0),
            'living_room_count' => (int) ($room['livingRoom'] ?? $room['living_room_count'] ?? 0),
            'refundable' => (bool) ($room['refundable'] ?? false),
            'free_cancellation' => $this->resolveFreeCancellation($room),
            'price' => $price,
            'currency_converted_price' => $currencyConvertedPrice,
            'daywise_price' => is_array($room['daywisePrice'] ?? null) ? $room['daywisePrice'] : [],
            'currency_converted_daywise_price' => is_array($room['currencyConvertedDaywisePrice'] ?? null)
                ? $room['currencyConvertedDaywisePrice']
                : [],
            'inclusions' => is_array($room['inclusions'] ?? null) ? $room['inclusions'] : [],
            'cancellation_policy' => is_array($room['cancellationPolicy'] ?? null) ? $room['cancellationPolicy'] : [],
            'cancellation_policy_details' => is_array($room['cancellationPolicyDetails'] ?? null)
                ? $room['cancellationPolicyDetails']
                : [],
            'raw' => $room,
        ];
    }

    /**
     * `fetchHotels` sends a bool, `checkRoomAvailability` sends `{isSupported: bool}`.
     *
     * @param  array<string, mixed>  $room
     */
    private function resolveFreeCancellation(array $room): bool
    {
        $value = $room['freeCancellation'] ?? $room['free_cancellation'] ?? false;

        if (is_array($value)) {
            return (bool) ($value['isSupported'] ?? false);
        }

        return (bool) $value;
    }

    private function stringifyAddress(mixed $address): string
    {
        if (is_string($address)) {
            return $address;
        }

        if (! is_array($address)) {
            return '';
        }

        $parts = array_filter(array_map(
            fn ($part) => is_scalar($part) ? trim((string) $part) : '',
            $address,
        ));

        return implode(', ', $parts);
    }

    /**
     * Tinivia uses `actual` in `fetchHotels` and `finalPrice`/`finalPriceWithTax` in
     * `checkRoomAvailability`; both are flattened onto the same keys here.
     *
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
                'gross' => 0.0,
            ];
        }

        $actual = (float) (
            $block['actual']
            ?? $block['finalPrice']
            ?? $block['finalPriceWithTax']
            ?? $block['discounted']
            ?? 0
        );

        $tax = (float) ($block['taxValue'] ?? $block['tax'] ?? $block['finalTaxValue'] ?? 0);
        $gross = (float) ($block['finalPriceWithTax'] ?? 0);

        if ($gross <= 0) {
            $gross = $actual + $tax;
        }

        return array_merge($block, [
            'actual' => $actual,
            'taxValue' => $tax,
            'tax' => $tax,
            'gross' => $gross,
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
