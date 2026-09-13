<?php

namespace App\Services\HotelSuppliers;

/**
 * Maps unified internal hotel shape to frontend-compatible payload (legacy + standard keys).
 */
class HotelResponseNormalizer
{
    /**
     * @param  array<int, array<string, mixed>>  $unifiedHotels
     * @return array<int, array<string, mixed>>
     */
    public function forFrontend(array $unifiedHotels): array
    {
        return array_map(fn (array $hotel) => $this->mapHotel($hotel), $unifiedHotels);
    }

    /**
     * @param  array<string, mixed>  $hotel
     * @return array<string, mixed>
     */
    private function mapHotel(array $hotel): array
    {
        $rooms = array_map(fn (array $room) => $this->mapRoom($room), $hotel['rooms'] ?? []);
        [$minRate, $maxRate] = $this->resolveRateRange($hotel, $rooms);

        $legacy = [
            'hotelId' => $hotel['hotel_id'] ?? null,
            'hotelName' => $hotel['hotel_name'] ?? null,
            'hotel_id' => $hotel['hotel_id'] ?? null,
            'hotel_name' => $hotel['hotel_name'] ?? null,
            'name' => $hotel['hotel_name'] ?? null,
            'starRating' => $hotel['star_rating'] ?? null,
            'propertyType' => $hotel['property_type'] ?? null,
            'address' => $hotel['address'] ?? null,
            'currency' => $hotel['currency'] ?? 'SGD',
            'min_rate' => $minRate,
            'max_rate' => $maxRate,
            'minRate' => $minRate,
            'maxRate' => $maxRate,
            'lowestPrice' => $minRate,
            'images' => $hotel['images'] ?? [],
            'description' => $hotel['description'] ?? null,
            'rooms' => $rooms,
            'supplier_code' => $hotel['supplier_code'] ?? null,
            'propertyDetail' => [
                'hotelName' => $hotel['hotel_name'] ?? null,
                'productId' => $hotel['hotel_id'] ?? null,
                'description' => $hotel['description'] ?? null,
                'address' => $hotel['address'] ?? null,
                'hotelImageUrl' => $hotel['images'] ?? [],
            ],
            'onlineHotelRaw' => $hotel['raw'] ?? null,
        ];

        return array_merge($hotel, $legacy);
    }

    /**
     * @param  array<string, mixed>  $room
     * @return array<string, mixed>
     */
    private function mapRoom(array $room): array
    {
        $price = $this->normalizePriceBlock(
            is_array($room['price'] ?? null) ? $room['price'] : null,
        );
        $currencyConvertedPrice = $this->normalizePriceBlock(
            is_array($room['currencyConvertedPrice'] ?? null)
                ? $room['currencyConvertedPrice']
                : (is_array($room['currency_converted_price'] ?? null) ? $room['currency_converted_price'] : null),
        );

        if (($currencyConvertedPrice['actual'] ?? 0) <= 0 && ($price['actual'] ?? 0) > 0) {
            $currencyConvertedPrice = $price;
        }

        return array_merge($room, [
            'roomId' => $room['room_id'] ?? null,
            'roomName' => $room['room_name'] ?? null,
            'roomType' => $room['room_name'] ?? null,
            'ratePlanId' => $room['rate_plan_id'] ?? null,
            'ratePlanName' => $room['rate_plan_name'] ?? null,
            'rateKey' => $room['rate_plan_id'] ?? null,
            'bedType' => $room['bed_type'] ?? null,
            'extraBedType' => $room['extra_bed_type'] ?? null,
            'mealPlanName' => $room['meal_plan'] ?? null,
            'meal_plan' => $room['meal_plan'] ?? null,
            'boardCode' => $room['board_code'] ?? null,
            'breakFast' => (bool) ($room['breakfast_included'] ?? false),
            'breakfast_included' => (bool) ($room['breakfast_included'] ?? false),
            'maxOccupancy' => (int) ($room['max_occupancy'] ?? 0),
            'maxAdult' => (int) ($room['max_adult'] ?? 0),
            'maxChild' => (int) ($room['max_child'] ?? 0),
            'freeCancellation' => (bool) ($room['free_cancellation'] ?? false),
            'inclusions' => $room['inclusions'] ?? [],
            'price' => $price,
            'currencyConvertedPrice' => $currencyConvertedPrice,
            'daywisePrice' => $room['daywise_price'] ?? $room['daywisePrice'] ?? [],
            'currencyConvertedDaywisePrice' => $room['currency_converted_daywise_price']
                ?? $room['currencyConvertedDaywisePrice']
                ?? [],
            'cancellationPolicy' => $room['cancellation_policy'] ?? $room['cancellationPolicy'] ?? [],
        ]);
    }

    /**
     * @param  array<string, mixed>  $hotel
     * @param  array<int, array<string, mixed>>  $rooms
     * @return array{0: ?float, 1: ?float}
     */
    private function resolveRateRange(array $hotel, array $rooms): array
    {
        $minRate = isset($hotel['min_rate']) ? (float) $hotel['min_rate'] : null;
        $maxRate = isset($hotel['max_rate']) ? (float) $hotel['max_rate'] : null;

        if ($minRate !== null && $minRate > 0) {
            return [$minRate, ($maxRate !== null && $maxRate > 0) ? $maxRate : $minRate];
        }

        $prices = [];

        foreach ($rooms as $room) {
            $price = (float) (
                $room['currencyConvertedPrice']['actual']
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
}
