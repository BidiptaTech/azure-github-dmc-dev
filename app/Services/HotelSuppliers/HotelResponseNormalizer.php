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

        $legacy = [
            'hotelId' => $hotel['hotel_id'] ?? null,
            'hotelName' => $hotel['hotel_name'] ?? null,
            'hotel_id' => $hotel['hotel_id'] ?? null,
            'hotel_name' => $hotel['hotel_name'] ?? null,
            'name' => $hotel['hotel_name'] ?? null,
            'starRating' => $hotel['star_rating'] ?? null,
            'address' => $hotel['address'] ?? null,
            'currency' => $hotel['currency'] ?? 'SGD',
            'minRate' => $hotel['min_rate'] ?? null,
            'maxRate' => $hotel['max_rate'] ?? null,
            'lowestPrice' => $hotel['min_rate'] ?? null,
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
        $price = $room['price'] ?? ['actual' => 0, 'tax' => 0];
        $actual = (float) ($price['actual'] ?? 0);
        $tax = (float) ($price['tax'] ?? 0);

        return array_merge($room, [
            'roomId' => $room['room_id'] ?? null,
            'roomName' => $room['room_name'] ?? null,
            'roomType' => $room['room_name'] ?? null,
            'ratePlanId' => $room['rate_plan_id'] ?? null,
            'rateKey' => $room['rate_plan_id'] ?? null,
            'bedType' => $room['bed_type'] ?? null,
            'extraBedType' => $room['extra_bed_type'] ?? null,
            'mealPlanName' => $room['meal_plan'] ?? null,
            'boardCode' => $room['board_code'] ?? null,
            'breakFast' => (bool) ($room['breakfast_included'] ?? false),
            'maxOccupancy' => (int) ($room['max_occupancy'] ?? 0),
            'inclusions' => $room['inclusions'] ?? [],
            'price' => [
                'actual' => $actual,
                'taxValue' => $tax,
            ],
            'currencyConvertedPrice' => [
                'actual' => $actual,
                'taxValue' => $tax,
            ],
        ]);
    }
}
