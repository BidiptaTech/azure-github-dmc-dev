<?php

namespace App\Helpers;

use App\Models\Hotel;
use App\Models\Room;
use App\Models\Bed;
use App\Models\Rate;
use App\Helpers\CommonHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HotelPriceHelper
{
    /**
     * Calculate the hotel room price (room + meals) for a set of dates.
     *
     * Pricing rules:
     *  - Base room price comes from the rooms table:
     *      pax 1  -> weekday_price / weekend_price
     *      pax 2  -> double_weekday_price / double_weekend_price
     *      pax 3+ -> double price + extra_bed_price (from beds table) for each guest beyond 2
     *  - Weekday vs weekend is decided per date using the hotel's weekend_days column.
     *  - Meal prices come from the hotels table (breakfast_price / lunch_price / dinner_price).
     *  - If a date falls inside a rates-table entry for the hotel, the room price AND the
     *    meal prices for that date are taken from the rate instead of the room/hotel defaults.
     *  - When several rates overlap a date the priority is:
     *      Blackout Date > Fair Date > Season.
     *
     * Meal prices are treated as per-person-per-night and multiplied by pax.
     *
     * @param string       $hotelUniqueId  hotels.hotel_unique_id
     * @param string|int   $roomId         rooms.room_id
     * @param string|int   $bedId          beds.bed_id (used for the extra-bed price)
     * @param array        $dates          array of date strings (one per night), e.g. ['2026-06-11', '2026-06-12']
     * @param string       $mealPlan       e.g. "room with breakfast + dinner"
     * @param int          $pax            number of guests
     * @return array
     */
    public static function calculatePrice($hotelUniqueId, $roomId, $bedId, array $dates=[], $mealPlan, $pax): array
    {
        try {
            $pax = max(1, (int) $pax);

            $hotel = Hotel::where('hotel_unique_id', $hotelUniqueId)->first();
            if (!$hotel) {
                return self::errorResponse("Hotel not found for hotel_unique_id: {$hotelUniqueId}");
            }

            $room = Room::where('room_id', $roomId)
                ->where('hotel_id', $hotelUniqueId)
                ->first();
            if (!$room) {
                return self::errorResponse("Room not found for room_id: {$roomId}");
            }

            // Extra-bed price (used from pax 3 onwards).
            $extraBedPrice = 0.0;
            if (!empty($bedId)) {
                $bed = Bed::where('bed_id', $bedId)->first();
                if ($bed && $bed->extra_bed_price !== null && $bed->extra_bed_price !== '') {
                    $extraBedPrice = floatval($bed->extra_bed_price);
                }
            }

            // Which meals are included in the selected plan.
            $meals = self::parseMealPlan($mealPlan);

            // Weekend days for this hotel (defaults to Sat/Sun).
            $weekendDays = ['Saturday', 'Sunday'];
            if (!empty($hotel->weekend_days)) {
                $decodedWeekendDays = json_decode($hotel->weekend_days, true);
                if (is_array($decodedWeekendDays) && !empty($decodedWeekendDays)) {
                    $weekendDays = $decodedWeekendDays;
                }
            }

            // Default room prices (from rooms table).
            $roomWeekdaySingle = floatval($room->weekday_price ?? 0);
            $roomWeekendSingle = floatval($room->weekend_price ?? 0);
            $roomWeekdayDouble = floatval($room->double_weekday_price ?? 0);
            $roomWeekendDouble = floatval($room->double_weekend_price ?? 0);

            // Default meal prices (from hotels table).
            $hotelBreakfast = floatval($hotel->breakfast_price ?? 0);
            $hotelLunch     = floatval($hotel->lunch_price ?? 0);
            $hotelDinner    = floatval($hotel->dinner_price ?? 0);

            // Variant price handling (applies to Season + Blackout Date).
            // If the selected room has no explicit varient_price and is not the base room,
            // the variant is the price difference between the selected room and the hotel's
            // base room (base_room = 1) for this DMC.
            $selectedVarient    = floatval($room->varient_price ?? 0);
            $isSelectedBaseRoom = (int) ($room->base_room ?? 0) === 1;
            $baseRoom = null;
            if ($selectedVarient == 0.0 && !$isSelectedBaseRoom) {
                $dmcId = Auth::check() ? CommonHelper::getDmcId(Auth::user()) : null;
                if (empty($dmcId)) {
                    return self::errorResponse('No DMC found for the current user.');
                }
                $baseRoom = Room::where('base_room', 1)
                    ->where('hotel_id', $hotelUniqueId)
                    ->where('dmc_id', $dmcId)
                    ->first();
            }

            $roomTotal = 0.0;
            $mealTotal = 0.0;
            $breakdown = [];

            foreach ($dates as $rawDate) {
                $date = Carbon::parse($rawDate);
                $dateString = $date->format('Y-m-d');
                $isWeekend = in_array($date->format('l'), $weekendDays);

                // Look for an applicable rate (Blackout > Fair > Season).
                $rate = Rate::where('hotel_id', $hotelUniqueId)
                    ->where('is_active', 1)
                    ->whereDate('start_date', '<=', $dateString)
                    ->whereDate('end_date', '>=', $dateString)
                    ->orderByRaw("
                        CASE
                            WHEN event_type = 'Blackout Date' THEN 1
                            WHEN event_type = 'Fair Date' THEN 2
                            WHEN event_type = 'Season' THEN 3
                            ELSE 4
                        END
                    ")
                    ->first();

                $source     = $rate ? 'rate' : 'default';
                $eventType  = $rate ? $rate->event_type : null;
                $surcharge  = 0.0;

                // Meal prices: from the rate when a rate applies, otherwise hotel defaults.
                if ($rate) {
                    $breakfastPrice = floatval($rate->breakfast_price ?? 0);
                    $lunchPrice     = floatval($rate->lunch_price ?? 0);
                    $dinnerPrice    = floatval($rate->dinner_price ?? 0);
                } else {
                    $breakfastPrice = $hotelBreakfast;
                    $lunchPrice     = $hotelLunch;
                    $dinnerPrice    = $hotelDinner;
                }

                // Variant price for this night (Season + Blackout only).
                $variantPrice = self::resolveVariantPrice($room, $baseRoom, $selectedVarient, $isSelectedBaseRoom, $isWeekend, $pax);

                // Room price for this night.
                if ($rate && $eventType === 'Blackout Date') {
                    // Blackout Date: use the rate's flat price column as-is + variant price.
                    $roomPrice = floatval($rate->price ?? 0) + $variantPrice;
                } elseif ($rate && $eventType === 'Fair Date') {
                    // Fair Date: rooms-table price (pax based) + the rate's price as a surcharge.
                    $singlePrice = $isWeekend ? $roomWeekendSingle : $roomWeekdaySingle;
                    $doublePrice = $isWeekend ? $roomWeekendDouble : $roomWeekdayDouble;
                    $surcharge   = floatval($rate->price ?? 0);

                    if ($pax <= 1) {
                        $roomPrice = $singlePrice + $surcharge;
                    } elseif ($pax == 2) {
                        $roomPrice = $doublePrice + $surcharge;
                    } else {
                        $roomPrice = $doublePrice + ($extraBedPrice * ($pax - 2)) + $surcharge;
                    }
                } elseif ($rate && $eventType === 'Season') {
                    // Season: rate's weekday/weekend (single/double) price + variant price.
                    $singlePrice = $isWeekend ? floatval($rate->weekend_price ?? 0) : floatval($rate->weekday_price ?? 0);
                    $doublePrice = $isWeekend ? floatval($rate->double_weekend_price ?? 0) : floatval($rate->double_weekday_price ?? 0);

                    if ($pax <= 1) {
                        $roomPrice = $singlePrice + $variantPrice;
                    } elseif ($pax == 2) {
                        $roomPrice = $doublePrice + $variantPrice;
                    } else {
                        $roomPrice = $doublePrice + $variantPrice + ($extraBedPrice * ($pax - 2));
                    }
                } else {
                    // No applicable rate: use the rooms-table prices directly (no variant adjustment).
                    $singlePrice = $isWeekend ? $roomWeekendSingle : $roomWeekdaySingle;
                    $doublePrice = $isWeekend ? $roomWeekendDouble : $roomWeekdayDouble;

                    if ($pax <= 1) {
                        $roomPrice = $singlePrice;
                    } elseif ($pax == 2) {
                        $roomPrice = $doublePrice;
                    } else {
                        // pax 3+ : double room price plus an extra bed for each guest beyond 2.
                        $roomPrice = $doublePrice + ($extraBedPrice * ($pax - 2));
                    }
                }

                // Meal price for this night (per person * pax).
                $mealPerPerson = 0.0;
                if ($meals['breakfast']) {
                    $mealPerPerson += $breakfastPrice;
                }
                if ($meals['lunch']) {
                    $mealPerPerson += $lunchPrice;
                }
                if ($meals['dinner']) {
                    $mealPerPerson += $dinnerPrice;
                }
                $mealPrice = $mealPerPerson * $pax;

                $roomTotal += $roomPrice;
                $mealTotal += $mealPrice;

                // Variant only applies for Season and Blackout Date.
                $appliedVariant = ($eventType === 'Season' || $eventType === 'Blackout Date') ? $variantPrice : 0.0;

                $breakdown[] = [
                    'date'          => $dateString,
                    'day'           => $date->format('l'),
                    'is_weekend'    => $isWeekend,
                    'source'        => $source,
                    'event_type'    => $eventType,
                    'surcharge'     => round($surcharge, 2),
                    'variant_price' => round($appliedVariant, 2),
                    'room_price'    => round($roomPrice, 2),
                    'meal_price'    => round($mealPrice, 2),
                    'night_total'   => round($roomPrice + $mealPrice, 2),
                ];
            }

            return [
                'success'         => true,
                'hotel_unique_id' => $hotelUniqueId,
                'room_id'         => $roomId,
                'bed_id'          => $bedId,
                'meal_plan'       => $mealPlan,
                'meals'           => $meals,
                'pax'             => $pax,
                'nights'          => count($dates),
                'extra_bed_price' => round($extraBedPrice, 2),
                'room_total'      => round($roomTotal, 2),
                'meal_total'      => round($mealTotal, 2),
                'grand_total'     => round($roomTotal + $mealTotal, 2),
                'breakdown'       => $breakdown,
            ];
        } catch (\Exception $e) {
            Log::error('HotelPriceHelper::calculatePrice failed', [
                'hotel_unique_id' => $hotelUniqueId,
                'room_id'         => $roomId,
                'bed_id'          => $bedId,
                'error'           => $e->getMessage(),
                'trace'           => $e->getTraceAsString(),
            ]);

            return self::errorResponse('Failed to calculate hotel price: ' . $e->getMessage());
        }
    }

    /**
     * Resolve the variant price to add on top of a rate-derived room price
     * (used for Season and Blackout Date).
     *
     *  - If the room has an explicit varient_price (non-zero), use it.
     *  - If the room IS the base room (base_room = 1), there is no variant (0).
     *  - Otherwise (varient_price = 0 and base_room = 0) the variant is the price
     *    difference between the selected room and the hotel's base room for the
     *    current pax / weekday-weekend dimension.
     */
    private static function resolveVariantPrice($room, $baseRoom, float $selectedVarient, bool $isSelectedBaseRoom, bool $isWeekend, int $pax): float
    {
        if ($selectedVarient != 0.0) {
            return $selectedVarient;
        }

        if ($isSelectedBaseRoom) {
            return 0.0;
        }

        if ($baseRoom) {
            return self::roomDimensionPrice($room, $isWeekend, $pax)
                - self::roomDimensionPrice($baseRoom, $isWeekend, $pax);
        }

        return 0.0;
    }

    /**
     * Get a room's price for the given dimension (single/double, weekday/weekend).
     */
    private static function roomDimensionPrice($room, bool $isWeekend, int $pax): float
    {
        if (!$room) {
            return 0.0;
        }

        if ($pax <= 1) {
            return $isWeekend ? floatval($room->weekend_price ?? 0) : floatval($room->weekday_price ?? 0);
        }

        return $isWeekend ? floatval($room->double_weekend_price ?? 0) : floatval($room->double_weekday_price ?? 0);
    }

    /**
     * Determine which meals a meal-plan string includes.
     *
     * @param  string $mealPlan
     * @return array{breakfast: bool, lunch: bool, dinner: bool}
     */
    private static function parseMealPlan($mealPlan): array
    {
        $plan = strtolower(trim((string) $mealPlan));
        $includesAll = strpos($plan, 'all meals') !== false;

        return [
            'breakfast' => $includesAll || strpos($plan, 'breakfast') !== false,
            'lunch'     => $includesAll || strpos($plan, 'lunch') !== false,
            'dinner'    => $includesAll || strpos($plan, 'dinner') !== false,
        ];
    }

    /**
     * Build a consistent failure response.
     */
    private static function errorResponse(string $message): array
    {
        return [
            'success'     => false,
            'message'     => $message,
            'room_total'  => 0,
            'meal_total'  => 0,
            'grand_total' => 0,
            'nights'      => 0,
            'breakdown'   => [],
        ];
    }
}
