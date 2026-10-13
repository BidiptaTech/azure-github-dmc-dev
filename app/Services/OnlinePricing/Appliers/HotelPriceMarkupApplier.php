<?php

namespace App\Services\OnlinePricing\Appliers;

use App\Services\OnlinePricing\MarkupCalculator;
use App\Services\OnlinePricing\MarkupContext;

class HotelPriceMarkupApplier
{
    public function __construct(
        private MarkupCalculator $calculator,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $hotels
     * @return array<int, array<string, mixed>>
     */
    public function apply(array $hotels, MarkupContext $context): array
    {
        return array_map(fn (array $hotel) => $this->applyToHotel($hotel, $context), $hotels);
    }

    /**
     * @param  array<string, mixed>  $hotel
     * @return array<string, mixed>
     */
    private function applyToHotel(array $hotel, MarkupContext $context): array
    {
        $rooms = $hotel['rooms'] ?? [];
        if (is_array($rooms) && $rooms !== []) {
            $hotel['rooms'] = array_map(
                fn (array $room) => $this->applyToRoom($room, $context),
                $rooms,
            );
        }

        $minFromRooms = $this->lowestRoomPrice($hotel['rooms'] ?? []);
        if ($minFromRooms !== null) {
            $hotel['min_rate'] = $minFromRooms;
            $hotel['minRate'] = $minFromRooms;
            $hotel['lowestPrice'] = $minFromRooms;
        } elseif ($this->hasNumericPrice($hotel, 'min_rate') || $this->hasNumericPrice($hotel, 'minRate')) {
            $base = (float) ($hotel['min_rate'] ?? $hotel['minRate'] ?? 0);
            $marked = $this->markUp($base, $context);
            $hotel['min_rate'] = $marked;
            $hotel['minRate'] = $marked;
            $hotel['lowestPrice'] = $marked;
        }

        if ($this->hasNumericPrice($hotel, 'max_rate') || $this->hasNumericPrice($hotel, 'maxRate')) {
            $base = (float) ($hotel['max_rate'] ?? $hotel['maxRate'] ?? 0);
            $hotel['max_rate'] = $this->markUp($base, $context);
            $hotel['maxRate'] = $hotel['max_rate'];
        }

        foreach (['price', 'totalPrice', 'amount'] as $key) {
            if ($this->hasNumericPrice($hotel, $key)) {
                $hotel[$key] = $this->markUp((float) $hotel[$key], $context);
            }
        }

        return $hotel;
    }

    /**
     * @param  array<string, mixed>  $room
     * @return array<string, mixed>
     */
    private function applyToRoom(array $room, MarkupContext $context): array
    {
        foreach (['price', 'currencyConvertedPrice'] as $priceKey) {
            if (! isset($room[$priceKey]) || ! is_array($room[$priceKey])) {
                continue;
            }
            foreach (['actual', 'adult', 'child', 'tax', 'taxValue'] as $field) {
                if (isset($room[$priceKey][$field]) && is_numeric($room[$priceKey][$field])) {
                    $room[$priceKey][$field] = $this->markUp((float) $room[$priceKey][$field], $context);
                }
            }
        }

        foreach (['roomPrice', 'totalPrice', 'amount', 'rate', 'net'] as $scalarKey) {
            if ($this->hasNumericPrice($room, $scalarKey)) {
                $room[$scalarKey] = $this->markUp((float) $room[$scalarKey], $context);
            }
        }

        if (isset($room['price']) && is_numeric($room['price'])) {
            $room['price'] = $this->markUp((float) $room['price'], $context);
        }

        // Travels with the enquiry into orders.data so the pre-approval recheck can
        // re-apply this exact stack to the supplier's fresh price. Suppliers that ship a
        // `booking` block carry it there; for the rest the modal reads `room.markup`.
        $room['markup'] = $context->toArray();

        if (is_array($room['booking'] ?? null)) {
            $room['booking']['markup'] = $room['markup'];
        }

        return $room;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rooms
     */
    private function lowestRoomPrice(array $rooms): ?float
    {
        $min = null;

        foreach ($rooms as $room) {
            $candidates = [];
            if (isset($room['price']['actual'])) {
                $candidates[] = (float) $room['price']['actual'];
            }
            if (isset($room['currencyConvertedPrice']['actual'])) {
                $candidates[] = (float) $room['currencyConvertedPrice']['actual'];
            }
            if (isset($room['price']) && is_numeric($room['price'])) {
                $candidates[] = (float) $room['price'];
            }

            foreach ($candidates as $value) {
                if ($value > 0 && ($min === null || $value < $min)) {
                    $min = $value;
                }
            }
        }

        return $min;
    }

    private function markUp(float $basePrice, MarkupContext $context): float
    {
        return $this->calculator->applyStack($basePrice, $context->stackedRules());
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function hasNumericPrice(array $item, string $key): bool
    {
        return isset($item[$key]) && is_numeric($item[$key]) && (float) $item[$key] > 0;
    }
}
