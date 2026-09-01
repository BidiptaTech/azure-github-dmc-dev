<?php

namespace App\Helpers;

use App\Models\Bed;
use App\Models\Guide;
use App\Models\Meal;
use App\Models\Order;
use App\Models\Room;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

/**
 * Build / resolve service-wise cost_price JSON stored on orders.
 *
 * Shape:
 * {
 *   "total_cost": 1234.56,
 *   "currency": "IDR",
 *   "source": "database|payload|mixed",
 *   "components": [
 *     {"key":"room","label":"...","cost":1000,"meta":{...}}
 *   ]
 * }
 */
class OrderCostPriceHelper
{
    public static function buildForOrder(Order $order): array
    {
        $data = is_string($order->data) ? json_decode($order->data, true) : $order->data;
        if (! is_array($data)) {
            return self::emptyResult($order->currency ?? null);
        }

        $items = (isset($data[0]) && is_array($data[0])) ? $data : [$data];
        $type = strtolower(trim((string) ($order->type ?? '')));
        $currency = strtoupper(trim((string) ($order->currency ?? '')));

        $components = [];
        $sources = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $built = match ($type) {
                'hotel' => self::buildHotelCost($item),
                'attraction' => self::buildAttractionCost($item),
                'restaurant' => self::buildRestaurantCost($item),
                'guide' => self::buildGuideCost($item),
                'entry_port', 'exit_port', 'travel_hourly', 'travel_point', 'local_transport' => self::buildTransportCost($item, $type),
                'miscellaneous' => self::buildGenericItemCost($item, 'miscellaneous'),
                default => self::buildGenericItemCost($item, $type ?: 'service'),
            };

            foreach ($built['components'] as $component) {
                $components[] = $component;
            }
            if (! empty($built['source'])) {
                $sources[] = $built['source'];
            }

            if ($currency === '' && ! empty($item['currency'])) {
                $currency = strtoupper(trim((string) $item['currency']));
            }
        }

        $total = 0.0;
        foreach ($components as $component) {
            $total += (float) ($component['cost'] ?? 0);
        }

        $source = 'payload';
        if (in_array('database', $sources, true) && in_array('payload', $sources, true)) {
            $source = 'mixed';
        } elseif (in_array('database', $sources, true)) {
            $source = 'database';
        }

        return [
            'total_cost' => round($total, 2),
            'currency' => $currency !== '' ? $currency : null,
            'source' => $source,
            'components' => $components,
            'built_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Return the frozen cost_price JSON from the order row only (never live-recalculate).
     */
    public static function storedCostPrice(Order $order): ?array
    {
        $stored = is_string($order->cost_price) ? json_decode($order->cost_price, true) : $order->cost_price;

        return is_array($stored) ? $stored : null;
    }

    /**
     * Safe historical cost total from orders.cost_price JSON.
     * Does not re-query room/ticket/meal/guide master prices.
     */
    public static function totalCostFromOrder(Order $order): float
    {
        $stored = self::storedCostPrice($order);
        if ($stored && isset($stored['total_cost']) && is_numeric($stored['total_cost'])) {
            return round((float) $stored['total_cost'], 2);
        }

        return 0.0;
    }

    /**
     * Snapshot cost_price onto the order at booking/update-of-service-data time.
     */
    public static function snapshotOntoOrder(Order $order): array
    {
        $built = self::buildForOrder($order);
        $order->cost_price = $built;

        return $built;
    }

    private static function emptyResult(?string $currency = null): array
    {
        return [
            'total_cost' => 0.0,
            'currency' => $currency ? strtoupper(trim($currency)) : null,
            'source' => 'payload',
            'components' => [],
            'built_at' => now()->toDateTimeString(),
        ];
    }

    private static function buildHotelCost(array $item): array
    {
        $components = [];
        $source = 'payload';
        $nights = self::resolveNights($item);
        $dates = self::resolveStayDates($item);

        $rooms = is_array($item['rooms'] ?? null) ? $item['rooms'] : [];
        foreach ($rooms as $roomRow) {
            if (! is_array($roomRow)) {
                continue;
            }

            $roomId = (int) ($roomRow['room_id'] ?? 0);
            $numberOfRooms = max(1, (int) ($roomRow['number_of_rooms'] ?? 1));
            $beds = is_array($roomRow['beds'] ?? null) ? $roomRow['beds'] : [];
            $headCount = 2;
            $bedId = null;
            if (! empty($beds[0]) && is_array($beds[0])) {
                $headCount = max(1, (int) ($beds[0]['head_count'] ?? $beds[0]['max_occupancy'] ?? 2));
                $bedId = $beds[0]['bed_id'] ?? null;
            }

            $useDouble = $headCount >= 2;
            $room = $roomId > 0 ? Room::query()->where('room_id', $roomId)->first() : null;

            $roomCostTotal = 0.0;
            $perNightCosts = [];
            if ($room) {
                $source = 'database';
                if (! empty($dates)) {
                    foreach ($dates as $date) {
                        $nightCost = self::roomNightCost($room, $date, $useDouble);
                        $perNightCosts[] = [
                            'date' => $date->toDateString(),
                            'cost' => $nightCost,
                            'occupancy' => $useDouble ? 'double' : 'single',
                            'day_type' => self::isWeekend($date) ? 'weekend' : 'weekday',
                        ];
                        $roomCostTotal += $nightCost * $numberOfRooms;
                    }
                } else {
                    $fallbackDate = Carbon::today();
                    $nightCost = self::roomNightCost($room, $fallbackDate, $useDouble);
                    $roomCostTotal = $nightCost * $numberOfRooms * max(1, $nights);
                    $perNightCosts[] = [
                        'nights' => max(1, $nights),
                        'cost_per_night' => $nightCost,
                        'occupancy' => $useDouble ? 'double' : 'single',
                    ];
                }
            } else {
                // Fallback: payload room/bed sell is not cost — try explicit cost fields.
                $payloadRoomCost = self::firstNumeric($roomRow, [
                    'cost', 'cost_price', 'total_cost', 'room_cost', 'room_cost_price',
                ]);
                if ($payloadRoomCost <= 0 && ! empty($beds[0]) && is_array($beds[0])) {
                    $payloadRoomCost = self::firstNumeric($beds[0], [
                        'cost', 'cost_price', 'total_cost', 'adult_cost', 'adultCost',
                    ]);
                }
                $roomCostTotal = $payloadRoomCost > 0
                    ? $payloadRoomCost
                    : 0.0;
            }

            if ($roomCostTotal > 0) {
                $components[] = [
                    'key' => 'room',
                    'label' => trim((string) ($roomRow['room_type'] ?? ($room->room_type ?? 'Room'))),
                    'cost' => round($roomCostTotal, 2),
                    'meta' => [
                        'room_id' => $roomId ?: null,
                        'bed_id' => $bedId,
                        'number_of_rooms' => $numberOfRooms,
                        'nights' => max(1, $nights),
                        'head_count' => $headCount,
                        'per_night' => $perNightCosts,
                    ],
                ];
            }

            // Meals from room catalog cost prices × head_count × nights × rooms
            if ($room) {
                $selectedMealLabels = [];
                foreach ($beds as $bed) {
                    if (! is_array($bed)) {
                        continue;
                    }
                    foreach (($bed['mealTypes'] ?? []) as $mealLabel) {
                        $selectedMealLabels[] = strtolower(trim((string) $mealLabel));
                    }
                    $selectedMeals = $bed['selectedMeals'] ?? null;
                    if (is_array($selectedMeals)) {
                        foreach ($selectedMeals as $mealBlock) {
                            if (! is_array($mealBlock)) {
                                continue;
                            }
                            foreach (['breakfast', 'lunch', 'dinner'] as $mealKey) {
                                if (! empty($mealBlock[$mealKey]) || ! empty($mealBlock[$mealKey . '_price'])) {
                                    $selectedMealLabels[] = $mealKey;
                                }
                            }
                        }
                    }
                }
                $selectedMealLabels = array_values(array_unique($selectedMealLabels));

                foreach (['breakfast', 'lunch', 'dinner'] as $mealKey) {
                    $matched = false;
                    foreach ($selectedMealLabels as $label) {
                        if ($label === $mealKey || str_contains($label, $mealKey)) {
                            $matched = true;
                            break;
                        }
                    }
                    if (! $matched && empty($selectedMealLabels) && (int) ($room->{$mealKey} ?? 0) === 1) {
                        // included meal flags alone are not enough without selection
                        continue;
                    }
                    if (! $matched) {
                        continue;
                    }

                    $unit = (float) ($room->{$mealKey . '_cost_price'} ?? 0);
                    if ($unit <= 0) {
                        continue;
                    }
                    $mealTotal = $unit * $headCount * max(1, $nights) * $numberOfRooms;
                    $components[] = [
                        'key' => 'meal_' . $mealKey,
                        'label' => ucfirst($mealKey),
                        'cost' => round($mealTotal, 2),
                        'meta' => [
                            'unit_cost' => $unit,
                            'head_count' => $headCount,
                            'nights' => max(1, $nights),
                            'number_of_rooms' => $numberOfRooms,
                        ],
                    ];
                    $source = 'database';
                }
            }
        }

        // Extra bed
        $extraBed = is_array($item['extra_bed'] ?? null) ? $item['extra_bed'] : null;
        if ($extraBed && ! empty($extraBed['enabled'])) {
            $qty = max(0, (int) ($extraBed['quantity'] ?? 0));
            $unitCost = 0.0;
            $bedCostSource = 'payload';

            $firstRoomId = (int) (($rooms[0]['room_id'] ?? 0));
            $firstBedId = $rooms[0]['beds'][0]['bed_id'] ?? null;
            if ($firstBedId && Schema::hasColumn('beds', 'extra_bed_cost_price')) {
                $bedQuery = Bed::query();
                if (is_numeric($firstBedId)) {
                    $bedQuery->where('bed_id', $firstBedId);
                } else {
                    $bedQuery->where('bed_id', (string) $firstBedId);
                }
                $bed = $bedQuery->first();
                if (! $bed && $firstRoomId > 0) {
                    $bed = Bed::query()->where('room_id', $firstRoomId)->first();
                }
                if ($bed && is_numeric($bed->extra_bed_cost_price ?? null)) {
                    $unitCost = (float) $bed->extra_bed_cost_price;
                    $bedCostSource = 'database';
                }
            }
            if ($unitCost <= 0) {
                $unitCost = self::firstNumeric($extraBed, ['cost_price', 'cost', 'price']);
            }
            $extraTotal = self::firstNumeric($extraBed, ['total_cost']);
            if ($extraTotal <= 0) {
                $extraTotal = $unitCost * max(1, $qty) * max(1, $nights);
            }
            if ($extraTotal > 0) {
                $components[] = [
                    'key' => 'extra_bed',
                    'label' => 'Extra Bed',
                    'cost' => round($extraTotal, 2),
                    'meta' => [
                        'quantity' => $qty,
                        'unit_cost' => $unitCost,
                        'nights' => max(1, $nights),
                        'source' => $bedCostSource,
                    ],
                ];
                if ($bedCostSource === 'database') {
                    $source = $source === 'payload' ? 'database' : $source;
                }
            }
        }

        // Child with / without bed from room catalog costs when available
        foreach (['child_with_bed' => 'child_with_bed_cost', 'child_without_bed' => 'child_without_bed_cost'] as $payloadKey => $roomColumn) {
            $block = is_array($item[$payloadKey] ?? null) ? $item[$payloadKey] : null;
            if (! $block || empty($block['enabled'])) {
                continue;
            }
            $children = max(0, (int) ($block['children'] ?? 0));
            if ($children <= 0) {
                continue;
            }

            $unit = 0.0;
            $blockSource = 'payload';
            $roomId = (int) (($rooms[0]['room_id'] ?? 0));
            if ($roomId > 0 && Schema::hasColumn('rooms', $roomColumn)) {
                $room = Room::query()->where('room_id', $roomId)->first();
                if ($room && is_numeric($room->{$roomColumn} ?? null)) {
                    $unit = (float) $room->{$roomColumn};
                    $blockSource = 'database';
                }
            }
            if ($unit <= 0) {
                $unit = self::firstNumeric($block, ['cost_price', 'cost', 'price']);
            }
            $total = self::firstNumeric($block, ['total_cost']);
            if ($total <= 0) {
                $total = $unit * $children * max(1, $nights);
            }
            if ($total > 0) {
                $components[] = [
                    'key' => $payloadKey,
                    'label' => $payloadKey === 'child_with_bed' ? 'Child With Bed' : 'Child Without Bed',
                    'cost' => round($total, 2),
                    'meta' => [
                        'children' => $children,
                        'unit_cost' => $unit,
                        'nights' => max(1, $nights),
                        'source' => $blockSource,
                    ],
                ];
                if ($blockSource === 'database') {
                    $source = $source === 'payload' ? 'database' : $source;
                }
            }
        }

        // Transfer cost (prefer explicit cost fields; do not treat sell as cost unless only cost exists)
        $transfer = is_array($item['transfer_options'] ?? null) ? $item['transfer_options'] : null;
        if ($transfer && (! empty($transfer['transfer_required']) || self::firstNumeric($transfer, ['cost', 'total_cost', 'cost_price']) > 0)) {
            $transferCost = self::firstNumeric($transfer, [
                'total_cost', 'cost_price', 'cost', 'Cost', 'adult_cost', 'adultCost', 'base_cost', 'baseCost',
            ]);
            // If only sell is present and cost equals sell from booking payload, still store it as best-known cost.
            if ($transferCost <= 0) {
                $transferCost = self::firstNumeric($transfer, ['sell', 'Sell', 'totalPrice']);
            }
            if ($transferCost > 0) {
                $components[] = [
                    'key' => 'transfer',
                    'label' => 'Transfer',
                    'cost' => round($transferCost, 2),
                    'meta' => [
                        'type' => $transfer['type'] ?? null,
                        'way' => $transfer['way'] ?? null,
                    ],
                ];
            }
        }

        return [
            'components' => $components,
            'source' => $source,
        ];
    }

    private static function buildAttractionCost(array $item): array
    {
        $components = [];
        $source = 'payload';

        $adults = self::qtyFromItem($item, ['adultCount', 'adultsQty', 'adults', 'adult']);
        $children = self::qtyFromItem($item, ['childCount', 'childQty', 'children', 'child']);
        $seniors = self::qtyFromItem($item, ['seniorCount', 'seniorQty', 'seniors', 'senior']);
        $isNri = self::isNriItem($item);

        $ticketId = $item['ticket_id'] ?? $item['ticketId'] ?? ($item['ticket_details']['ticket_id'] ?? null);
        $ticket = null;
        $isOnlineAttraction = ! empty($item['isOnlineAttraction'])
            || strtolower((string) ($item['attractionSourceType'] ?? '')) === 'online';

        // tickets.ticket_id is bigint. Online SKUs such as "SPPARK-premium" are not local ticket IDs.
        // Querying them inside an open PostgreSQL transaction aborts the txn (SQLSTATE 25P02 on the later orders insert).
        if (! $isOnlineAttraction && ! empty($ticketId) && self::isNumericDatabaseId($ticketId)) {
            $ticket = Ticket::query()->where('ticket_id', $ticketId)->first();
        }

        $adultUnit = 0.0;
        $childUnit = 0.0;
        $seniorUnit = 0.0;

        if ($ticket) {
            $source = 'database';
            if ($isNri) {
                $adultUnit = (float) ($ticket->adult_cost_price_nri ?? $ticket->adult_cost_price ?? 0);
                $childUnit = (float) ($ticket->child_cost_price_nri ?? $ticket->child_cost_price ?? 0);
                $seniorUnit = (float) ($ticket->senior_adult_cost_price_nri ?? $ticket->senior_adult_cost_price ?? 0);
            } else {
                $adultUnit = (float) ($ticket->adult_cost_price ?? 0);
                $childUnit = (float) ($ticket->child_cost_price ?? 0);
                $seniorUnit = (float) ($ticket->senior_adult_cost_price ?? 0);
            }
        }

        if ($adultUnit <= 0 && $childUnit <= 0 && $seniorUnit <= 0) {
            $ticketDetails = is_array($item['ticket_details'] ?? null) ? $item['ticket_details'] : [];
            $adultUnit = self::firstNumeric($ticketDetails, ['adult_cost', 'adult_cost_price', 'adultCost'])
                ?: self::firstNumeric($item, ['adultCost', 'adult_cost', 'adult_cost_price']);
            $childUnit = self::firstNumeric($ticketDetails, ['child_cost', 'child_cost_price', 'childCost'])
                ?: self::firstNumeric($item, ['childCost', 'child_cost', 'child_cost_price']);
            $seniorUnit = self::firstNumeric($ticketDetails, ['senior_cost', 'senior_adult_cost_price', 'seniorCost'])
                ?: self::firstNumeric($item, ['seniorCost', 'senior_cost']);
        }

        $ticketCost = ($adultUnit * $adults) + ($childUnit * $children) + ($seniorUnit * $seniors);
        if ($ticketCost <= 0) {
            $ticketCost = self::firstNumeric($item, ['total_cost', 'cost', 'Cost']);
        }

        if ($ticketCost > 0) {
            $components[] = [
                'key' => 'attraction_ticket',
                'label' => trim((string) ($item['ticketName'] ?? $item['ticket_name'] ?? $item['AttractionName'] ?? 'Attraction Ticket')),
                'cost' => round($ticketCost, 2),
                'meta' => [
                    'ticket_id' => $ticketId,
                    'attraction_id' => $item['attraction_id'] ?? $item['AttractionId'] ?? null,
                    'adults' => $adults,
                    'children' => $children,
                    'seniors' => $seniors,
                    'adult_unit_cost' => $adultUnit,
                    'child_unit_cost' => $childUnit,
                    'senior_unit_cost' => $seniorUnit,
                    'nri' => $isNri,
                ],
            ];
        }

        self::appendTransferAndGuideComponents($item, $components, $source);

        return [
            'components' => $components,
            'source' => $source,
        ];
    }

    private static function buildRestaurantCost(array $item): array
    {
        $components = [];
        $source = 'payload';

        $adults = self::qtyFromItem($item, ['adultCount', 'adultsQty', 'adults', 'adult']);
        $children = self::qtyFromItem($item, ['childCount', 'childQty', 'children', 'child']);

        $mealId = $item['meal_id'] ?? $item['mealId'] ?? null;
        $meal = null;
        if (! empty($mealId)) {
            $meal = Meal::query()->where('meal_id', $mealId)->first();
        }

        // Some restaurant orders store multiple meals under meals / MealDescription.
        $mealRows = [];
        foreach (['meals', 'MealDescription', 'mealDescription'] as $mealKey) {
            if (! is_array($item[$mealKey] ?? null) || empty($item[$mealKey])) {
                continue;
            }
            foreach ($item[$mealKey] as $mealRow) {
                if (is_array($mealRow)) {
                    $mealRows[] = $mealRow;
                }
            }
        }

        if (! empty($mealRows)) {
            foreach ($mealRows as $mealRow) {
                $rowMealId = $mealRow['meal_id'] ?? $mealRow['mealId'] ?? null;
                $rowMeal = $rowMealId ? Meal::query()->where('meal_id', $rowMealId)->first() : null;
                $rowAdults = self::qtyFromItem($mealRow, ['adultsQty', 'adults', 'adult', 'adultCount']);
                $rowChildren = self::qtyFromItem($mealRow, ['childQty', 'children', 'child', 'childCount']);
                $qty = isset($mealRow['quantity']) && is_numeric($mealRow['quantity']) ? max(0, (int) $mealRow['quantity']) : 0;
                if ($rowAdults <= 0 && $rowChildren <= 0) {
                    if ($qty > 0) {
                        $rowAdults = $qty;
                    } else {
                        $rowAdults = $adults;
                        $rowChildren = $children;
                    }
                }

                $adultUnit = 0.0;
                $childUnit = 0.0;
                $rowSource = 'payload';
                if ($rowMeal) {
                    $rowSource = 'database';
                    $adultUnit = (float) ($rowMeal->adult_cost_price ?? $rowMeal->item_cost_price ?? 0);
                    $childUnit = (float) ($rowMeal->child_cost_price ?? 0);
                    $source = 'database';
                }
                if ($adultUnit <= 0) {
                    $adultUnit = self::firstNumeric($mealRow, ['adult_cost', 'adultCost', 'adult_cost_price', 'cost']);
                }
                if ($childUnit <= 0) {
                    $childUnit = self::firstNumeric($mealRow, ['child_cost', 'childCost', 'child_cost_price']);
                }

                $mealCost = ($adultUnit * max(0, $rowAdults)) + ($childUnit * max(0, $rowChildren));
                if ($mealCost <= 0) {
                    $mealCost = self::firstNumeric($mealRow, ['total_cost', 'cost', 'price']);
                }
                if ($mealCost <= 0) {
                    continue;
                }

                $components[] = [
                    'key' => 'meal',
                    'label' => trim((string) (
                        $mealRow['item_name']
                        ?? $mealRow['meal_name']
                        ?? $mealRow['mealName']
                        ?? $mealRow['name']
                        ?? ($rowMeal->name ?? 'Meal')
                    )),
                    'cost' => round($mealCost, 2),
                    'meta' => [
                        'meal_id' => $rowMealId,
                        'adults' => $rowAdults,
                        'children' => $rowChildren,
                        'adult_unit_cost' => $adultUnit,
                        'child_unit_cost' => $childUnit,
                        'source' => $rowSource,
                    ],
                ];
            }
        } else {
            $adultUnit = 0.0;
            $childUnit = 0.0;
            if ($meal) {
                $source = 'database';
                $adultUnit = (float) ($meal->adult_cost_price ?? $meal->item_cost_price ?? 0);
                $childUnit = (float) ($meal->child_cost_price ?? 0);
            }
            if ($adultUnit <= 0) {
                $adultUnit = self::firstNumeric($item, ['adultCost', 'adult_cost', 'adult_cost_price']);
            }
            if ($childUnit <= 0) {
                $childUnit = self::firstNumeric($item, ['childCost', 'child_cost', 'child_cost_price']);
            }

            $mealCost = ($adultUnit * $adults) + ($childUnit * $children);
            if ($mealCost <= 0) {
                $mealCost = self::firstNumeric($item, ['total_cost', 'cost', 'Cost', 'mealPrice']);
            }

            if ($mealCost > 0) {
                $components[] = [
                    'key' => 'meal',
                    'label' => trim((string) ($item['mealName'] ?? $item['meal_name'] ?? $item['restaurantName'] ?? ($meal->name ?? 'Restaurant Meal'))),
                    'cost' => round($mealCost, 2),
                    'meta' => [
                        'meal_id' => $mealId,
                        'restaurant_id' => $item['restaurant_id'] ?? $item['restaurantId'] ?? null,
                        'adults' => $adults,
                        'children' => $children,
                        'adult_unit_cost' => $adultUnit,
                        'child_unit_cost' => $childUnit,
                    ],
                ];
            }
        }

        self::appendTransferAndGuideComponents($item, $components, $source);

        return [
            'components' => $components,
            'source' => $source,
        ];
    }

    private static function buildGuideCost(array $item): array
    {
        $components = [];
        $source = 'payload';

        $guideId = $item['guide_id'] ?? $item['guideId'] ?? null;
        $hours = (int) ($item['hours'] ?? $item['entrytime'] ?? $item['service_hours'] ?? 0);
        if ($hours <= 0 && isset($item['guide_options']) && is_array($item['guide_options'])) {
            $hours = (int) ($item['guide_options']['hours'] ?? $item['guide_options']['package_hours'] ?? 0);
            $guideId = $guideId ?: ($item['guide_options']['guide_id'] ?? $item['guide_options']['guideId'] ?? null);
        }

        $guideCost = 0.0;
        $matchedTier = null;
        if (! empty($guideId)) {
            $guide = Guide::query()->where('guide_id', $guideId)->first();
            if ($guide) {
                $resolved = self::resolveGuideHourlyCost($guide, max(1, $hours ?: 12));
                $guideCost = (float) ($resolved['cost'] ?? 0);
                $matchedTier = $resolved['tier'] ?? null;
                if ($guideCost > 0) {
                    $source = 'database';
                }
            }
        }

        if ($guideCost <= 0) {
            $guideCost = self::firstNumeric($item, [
                'total_cost', 'cost', 'Cost', 'adultCost', 'adult_cost', 'basePrice', 'base_price',
            ]);
            if ($guideCost <= 0 && isset($item['guide_options']) && is_array($item['guide_options'])) {
                $guideCost = self::firstNumeric($item['guide_options'], [
                    'total_cost', 'total_price', 'cost', 'Cost', 'base_price', 'adult_cost', 'adultCost',
                ]);
            }
        }

        if ($guideCost > 0) {
            $components[] = [
                'key' => 'guide',
                'label' => trim((string) ($item['guide_name'] ?? $item['guideName'] ?? 'Guide')),
                'cost' => round($guideCost, 2),
                'meta' => [
                    'guide_id' => $guideId,
                    'hours' => $hours ?: null,
                    'tier' => $matchedTier,
                ],
            ];
        }

        return [
            'components' => $components,
            'source' => $source,
        ];
    }

    /**
     * @return array{cost: float, tier: string|null}
     */
    private static function resolveGuideHourlyCost(Guide $guide, int $hours): array
    {
        $tiers = [
            1 => 'hourly_cost_price',
            2 => 'two_hour_cost_price',
            4 => 'four_hour_cost_price',
            6 => 'six_hour_cost_price',
            8 => 'eight_hour_cost_price',
            10 => 'ten_hour_cost_price',
            12 => 'twelve_hour_cost_price',
        ];

        if (isset($tiers[$hours]) && is_numeric($guide->{$tiers[$hours]} ?? null) && (float) $guide->{$tiers[$hours]} > 0) {
            return [
                'cost' => (float) $guide->{$tiers[$hours]},
                'tier' => $tiers[$hours],
            ];
        }

        // Pick the closest higher (or equal) configured tier.
        $chosenCost = 0.0;
        $chosenTier = null;
        foreach ($tiers as $tierHours => $column) {
            $value = (float) ($guide->{$column} ?? 0);
            if ($value <= 0) {
                continue;
            }
            if ($tierHours >= $hours) {
                return [
                    'cost' => $value,
                    'tier' => $column,
                ];
            }
            $chosenCost = $value;
            $chosenTier = $column;
        }

        if ($chosenCost > 0) {
            return [
                'cost' => $chosenCost,
                'tier' => $chosenTier,
            ];
        }

        $minimum = (float) ($guide->minimum_cost_price ?? 0);

        return [
            'cost' => max(0, $minimum),
            'tier' => $minimum > 0 ? 'minimum_cost_price' : null,
        ];
    }

    private static function appendTransferAndGuideComponents(array $item, array &$components, string &$source): void
    {
        $transfer = is_array($item['transfer_options'] ?? null) ? $item['transfer_options'] : null;
        if ($transfer && (! empty($transfer['transfer_required']) || self::firstNumeric($transfer, ['cost', 'total_cost', 'cost_price']) > 0)) {
            $transferCost = self::firstNumeric($transfer, [
                'total_cost', 'cost_price', 'cost', 'Cost', 'adult_cost', 'adultCost', 'base_cost', 'baseCost',
            ]);
            if ($transferCost > 0) {
                $components[] = [
                    'key' => 'transfer',
                    'label' => 'Transfer',
                    'cost' => round($transferCost, 2),
                    'meta' => [
                        'type' => $transfer['type'] ?? null,
                        'way' => $transfer['way'] ?? null,
                    ],
                ];
            }
        }

        $guideOptions = is_array($item['guide_options'] ?? null) ? $item['guide_options'] : null;
        if ($guideOptions && (! empty($guideOptions['guide_required']) || ! empty($guideOptions['guide_id']))) {
            $guideBuilt = self::buildGuideCost(array_merge($item, [
                'guide_id' => $guideOptions['guide_id'] ?? $guideOptions['guideId'] ?? null,
                'guide_name' => $guideOptions['guide_name'] ?? $guideOptions['guideName'] ?? 'Guide',
                'hours' => $guideOptions['hours'] ?? $guideOptions['package_hours'] ?? ($item['hours'] ?? 0),
                'guide_options' => $guideOptions,
            ]));
            foreach ($guideBuilt['components'] as $component) {
                $components[] = $component;
            }
            if (($guideBuilt['source'] ?? '') === 'database') {
                $source = $source === 'payload' ? 'database' : $source;
            }
        }
    }

    private static function qtyFromItem(array $item, array $keys): int
    {
        foreach ($keys as $key) {
            if (isset($item[$key]) && is_numeric($item[$key])) {
                return max(0, (int) $item[$key]);
            }
        }

        return 0;
    }

    private static function isNriItem(array $item): bool
    {
        $nri = strtolower(trim((string) ($item['nri'] ?? ($item['ticket_details']['nri'] ?? ''))));

        return in_array($nri, ['nri', 'non-residential', 'non_residential', '1', 'true'], true);
    }

    private static function buildTransportCost(array $item, string $type): array
    {
        $components = [];
        $cost = self::firstNumeric($item, [
            'total_cost', 'cost_price', 'cost', 'Cost', 'adult_cost', 'adultCost', 'base_cost', 'baseCost',
        ]);
        if ($cost <= 0) {
            // Many transport payloads store supplier cost under cost and sell separately.
            $cost = self::firstNumeric($item, ['sell', 'Sell', 'totalPrice', 'price']);
        }
        if ($cost > 0) {
            $components[] = [
                'key' => $type,
                'label' => ucfirst(str_replace('_', ' ', $type)),
                'cost' => round($cost, 2),
                'meta' => [
                    'vehicle' => $item['vehicles_name'] ?? $item['vehicle_name'] ?? null,
                ],
            ];
        }

        if (isset($item['guide_options']) && is_array($item['guide_options'])) {
            $guideCost = self::firstNumeric($item['guide_options'], [
                'total_cost', 'cost_price', 'cost', 'Cost', 'adult_cost', 'adultCost',
            ]);
            if ($guideCost > 0) {
                $components[] = [
                    'key' => 'guide',
                    'label' => 'Guide',
                    'cost' => round($guideCost, 2),
                    'meta' => [
                        'guide_name' => $item['guide_options']['guide_name'] ?? $item['guide_options']['guideName'] ?? null,
                    ],
                ];
            }
        }

        return [
            'components' => $components,
            'source' => 'payload',
        ];
    }

    private static function buildGenericItemCost(array $item, string $type): array
    {
        $components = [];
        $cost = self::firstNumeric($item, [
            'total_cost', 'cost_price', 'cost', 'Cost', 'adult_cost', 'adultCost', 'base_cost', 'baseCost', 'net_cost', 'netCost',
        ]);
        if ($cost <= 0 && isset($item['guide_options']) && is_array($item['guide_options'])) {
            $cost = self::firstNumeric($item['guide_options'], [
                'total_cost', 'cost_price', 'cost', 'Cost', 'adult_cost', 'adultCost',
            ]);
        }
        if ($cost > 0) {
            $components[] = [
                'key' => $type,
                'label' => ucfirst(str_replace('_', ' ', $type)),
                'cost' => round($cost, 2),
                'meta' => [],
            ];
        }

        if (isset($item['transfer_options']) && is_array($item['transfer_options'])) {
            $transferCost = self::firstNumeric($item['transfer_options'], [
                'total_cost', 'cost_price', 'cost', 'Cost', 'adult_cost', 'adultCost',
            ]);
            if ($transferCost > 0) {
                $components[] = [
                    'key' => 'transfer',
                    'label' => 'Transfer',
                    'cost' => round($transferCost, 2),
                    'meta' => [],
                ];
            }
        }

        return [
            'components' => $components,
            'source' => 'payload',
        ];
    }

    private static function roomNightCost(Room $room, Carbon $date, bool $useDouble): float
    {
        $weekend = self::isWeekend($date);
        if ($useDouble) {
            $cost = $weekend
                ? (float) ($room->double_weekend_cost_price ?? 0)
                : (float) ($room->double_weekday_cost_price ?? 0);
            if ($cost > 0) {
                return $cost;
            }
        }

        $cost = $weekend
            ? (float) ($room->weekend_cost_price ?? 0)
            : (float) ($room->weekday_cost_price ?? 0);

        return max(0, $cost);
    }

    private static function isWeekend(Carbon $date): bool
    {
        // Carbon: 6 = Saturday, 0 = Sunday
        return in_array((int) $date->dayOfWeek, [0, 6], true);
    }

    /**
     * @return array<int, Carbon>
     */
    private static function resolveStayDates(array $item): array
    {
        $bookingDate = $item['bookingDate'] ?? null;
        if (! is_array($bookingDate) || count($bookingDate) < 2) {
            return [];
        }

        try {
            $checkIn = Carbon::parse($bookingDate[0])->startOfDay();
            $checkOut = Carbon::parse($bookingDate[1])->startOfDay();
        } catch (\Throwable $e) {
            return [];
        }

        if ($checkOut->lte($checkIn)) {
            return [$checkIn->copy()];
        }

        $dates = [];
        $cursor = $checkIn->copy();
        while ($cursor->lt($checkOut)) {
            $dates[] = $cursor->copy();
            $cursor->addDay();
        }

        return $dates;
    }

    private static function resolveNights(array $item): int
    {
        if (isset($item['nights']) && is_numeric($item['nights']) && (int) $item['nights'] > 0) {
            return (int) $item['nights'];
        }

        $dates = self::resolveStayDates($item);

        return max(1, count($dates));
    }

    private static function firstNumeric(array $source, array $keys): float
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $source)) {
                continue;
            }
            if (is_numeric($source[$key])) {
                return (float) $source[$key];
            }
        }

        return 0.0;
    }

    /**
     * Local master IDs (ticket_id, meal_id, etc.) are bigint. Non-numeric supplier SKUs must not be queried.
     */
    private static function isNumericDatabaseId(mixed $value): bool
    {
        if (is_int($value)) {
            return $value > 0;
        }

        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        $value = trim((string) $value);

        return $value !== '' && ctype_digit($value);
    }
}
