<?php

namespace App\Helpers;

use App\Models\Bed;
use App\Models\Guide;
use App\Models\Hotel;
use App\Models\Meal;
use App\Models\MiscellaneousItem;
use App\Models\MiscellaneousPrice;
use App\Models\Order;
use App\Models\Rate;
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
                'miscellaneous' => self::buildMiscellaneousCost($item),
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
        if (in_array('view_details', $sources, true)) {
            $source = 'view_details';
        } elseif (in_array('database', $sources, true) && in_array('payload', $sources, true)) {
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
        $hotelUniqueId = self::resolveHotelUniqueId($item);
        $dmcId = $item['priceModeId'] ?? $item['dmc_id'] ?? $item['priceMode_id'] ?? null;
        $weekendDays = self::resolveWeekendDays($hotelUniqueId);

        $rooms = is_array($item['rooms'] ?? null) ? $item['rooms'] : [];

        // Prefer View-details snapshot from Pro create/edit (exact season/fair/blackout costs).
        $snapshot = is_array($item['lodging_cost_snapshot'] ?? null) ? $item['lodging_cost_snapshot'] : null;
        if ($snapshot && ! empty($snapshot['components']) && is_array($snapshot['components'])) {
            foreach ($snapshot['components'] as $component) {
                if (! is_array($component)) {
                    continue;
                }
                $components[] = $component;
            }
            $components = self::normalizeLodgingSnapshotComponents($components, $item, $nights);
            $source = (string) ($snapshot['source'] ?? 'view_details');
            if ($source === '' || $source === 'payload') {
                $source = 'view_details';
            }
        } else {
            // Fallback: rebuild from room + rates tables
            foreach ($rooms as $roomRow) {
            if (! is_array($roomRow)) {
                continue;
            }

            $roomId = (int) ($roomRow['room_id'] ?? 0);
            $numberOfRooms = max(1, (int) ($roomRow['number_of_rooms'] ?? 1));
            $beds = is_array($roomRow['beds'] ?? null) ? $roomRow['beds'] : [];
            $headCount = 2;
            $bedId = null;
            $maxOccupancy = 2;
            if (! empty($beds[0]) && is_array($beds[0])) {
                $headCount = max(1, (int) ($beds[0]['head_count'] ?? $beds[0]['max_occupancy'] ?? 2));
                $maxOccupancy = max(1, (int) ($beds[0]['max_occupancy'] ?? $headCount));
                $bedId = $beds[0]['bed_id'] ?? null;
            }

            // Same single/double rule as sell: max guests 1 → single, else double.
            $useDouble = min(2, $maxOccupancy) > 1 || $headCount >= 2;
            $room = $roomId > 0 ? Room::query()->where('room_id', $roomId)->first() : null;

            $roomCostTotal = 0.0;
            $perNightCosts = [];
            $mealNightUnits = [
                'breakfast' => [],
                'lunch' => [],
                'dinner' => [],
            ];

            if ($room) {
                $source = 'database';
                if (! empty($dates)) {
                    foreach ($dates as $date) {
                        $night = self::roomNightCostDetail(
                            $room,
                            $date,
                            $useDouble,
                            $hotelUniqueId,
                            $dmcId,
                            $weekendDays
                        );
                        $perNightCosts[] = [
                            'date' => $date->toDateString(),
                            'cost' => $night['cost'],
                            'occupancy' => $night['occupancy'],
                            'day_type' => $night['day_type'],
                            'event_type' => $night['event_type'],
                            'source' => $night['source'],
                            'surcharge_cost' => $night['surcharge_cost'],
                        ];
                        $roomCostTotal += $night['cost'] * $numberOfRooms;

                        $rate = $night['rate'];
                        foreach (['breakfast', 'lunch', 'dinner'] as $mealKey) {
                            $mealNightUnits[$mealKey][] = self::mealUnitCostForNight($room, $rate, $mealKey);
                        }
                    }
                } else {
                    $fallbackDate = Carbon::today();
                    $night = self::roomNightCostDetail(
                        $room,
                        $fallbackDate,
                        $useDouble,
                        $hotelUniqueId,
                        $dmcId,
                        $weekendDays
                    );
                    $roomCostTotal = $night['cost'] * $numberOfRooms * max(1, $nights);
                    $perNightCosts[] = [
                        'nights' => max(1, $nights),
                        'cost_per_night' => $night['cost'],
                        'occupancy' => $night['occupancy'],
                        'day_type' => $night['day_type'],
                        'event_type' => $night['event_type'],
                        'source' => $night['source'],
                        'surcharge_cost' => $night['surcharge_cost'],
                    ];
                    foreach (['breakfast', 'lunch', 'dinner'] as $mealKey) {
                        $unit = self::mealUnitCostForNight($room, $night['rate'], $mealKey);
                        $mealNightUnits[$mealKey] = array_fill(0, max(1, $nights), $unit);
                    }
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
                        'max_occupancy' => $maxOccupancy,
                        'per_night' => $perNightCosts,
                    ],
                ];
            }

            // Meals: rate-aware unit cost × head_count × rooms (same selection as sell meal plan)
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
                        continue;
                    }
                    if (! $matched) {
                        continue;
                    }

                    $units = $mealNightUnits[$mealKey] ?? [];
                    if (empty($units)) {
                        $fallbackUnit = self::pickPositive(
                            $room->{$mealKey . '_cost_price'} ?? null,
                            $room->{$mealKey . '_price'} ?? 0
                        );
                        $units = array_fill(0, max(1, $nights), $fallbackUnit);
                    }

                    $mealTotal = 0.0;
                    $unitSum = 0.0;
                    foreach ($units as $unit) {
                        $unitSum += (float) $unit;
                        $mealTotal += (float) $unit * $headCount * $numberOfRooms;
                    }
                    $avgUnit = count($units) > 0 ? $unitSum / count($units) : 0.0;
                    if ($mealTotal <= 0) {
                        continue;
                    }
                    $components[] = [
                        'key' => 'meal_' . $mealKey,
                        'label' => ucfirst($mealKey),
                        'cost' => round($mealTotal, 2),
                        'meta' => [
                            'unit_cost' => round($avgUnit, 2),
                            'head_count' => $headCount,
                            'nights' => max(1, $nights),
                            'number_of_rooms' => $numberOfRooms,
                            'per_night_unit_cost' => array_map(static fn ($u) => round((float) $u, 2), $units),
                        ],
                    ];
                    $source = 'database';
                }
            }
        }
        } // end snapshot else

        $hasComponentKey = static function (array $components, string $key): bool {
            foreach ($components as $c) {
                if (is_array($c) && ($c['key'] ?? null) === $key) {
                    return true;
                }
            }

            return false;
        };

        // Extra bed (skip if lodging_cost_snapshot already included it)
        $extraBed = is_array($item['extra_bed'] ?? null) ? $item['extra_bed'] : null;
        if ($extraBed && ! empty($extraBed['enabled']) && ! $hasComponentKey($components, 'extra_bed')) {
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
            if ($hasComponentKey($components, $payloadKey)) {
                continue;
            }
            $block = is_array($item[$payloadKey] ?? null) ? $item[$payloadKey] : null;
            if (! $block || empty($block['enabled'])) {
                continue;
            }
            $children = max(0, (int) ($block['quantity'] ?? $block['children'] ?? 0));
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
                        'quantity' => $children,
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
        if (! empty($ticketId)) {
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

        $guideOptions = is_array($item['guide_options'] ?? null) ? $item['guide_options'] : null;

        $guideId = $item['guide_id'] ?? $item['guideId'] ?? null;
        $hours = (int) ($item['hours'] ?? $item['entrytime'] ?? $item['service_hours'] ?? 0);
        $guideName = trim((string) ($item['guide_name'] ?? $item['guideName'] ?? $item['name'] ?? ''));

        if ($guideOptions) {
            if ($hours <= 0) {
                $hours = (int) ($guideOptions['hours'] ?? $guideOptions['service_hours'] ?? $guideOptions['package_hours'] ?? 0);
            }
            $guideId = $guideId
                ?: ($guideOptions['guide_id'] ?? $guideOptions['guideId'] ?? null);
            if ($guideName === '') {
                $guideName = trim((string) (
                    $guideOptions['guide_name']
                    ?? $guideOptions['guideName']
                    ?? $guideOptions['name']
                    ?? ''
                ));
            }
        }

        // Empty string / "0" are not valid guide_ids (arrival/exit used to send "").
        if ($guideId === '' || $guideId === '0' || $guideId === 0) {
            $guideId = null;
        }

        // Pro entry/exit always book 12h; default when hours missing.
        if ($hours <= 0) {
            $hours = 12;
        }

        $guideCost = 0.0;
        $matchedTier = null;
        $guide = null;
        if (! empty($guideId)) {
            $guide = Guide::query()->where('guide_id', $guideId)->first();
        }
        // Fallback: resolve by name when id was missing from payload (legacy arrival/exit).
        if (! $guide && $guideName !== '') {
            $guide = Guide::query()->where('name', $guideName)->first();
            if ($guide) {
                $guideId = $guide->guide_id;
            }
        }

        if ($guide) {
            $resolved = self::resolveGuideHourlyCost($guide, max(1, $hours));
            $guideCost = (float) ($resolved['cost'] ?? 0);
            $matchedTier = $resolved['tier'] ?? null;
            if ($guideCost > 0) {
                $source = 'database';
            }
        }

        // Last resort only — prefer never using sell figures for cost_price.
        if ($guideCost <= 0) {
            $guideCost = self::firstNumeric($item, [
                'total_cost', 'cost_price', 'adult_cost_price', 'base_cost', 'baseCost',
            ]);
            if ($guideCost <= 0 && $guideOptions) {
                $guideCost = self::firstNumeric($guideOptions, [
                    'total_cost', 'cost_price', 'adult_cost_price', 'base_cost', 'baseCost',
                ]);
            }
        }

        if ($guideCost > 0) {
            $components[] = [
                'key' => 'guide',
                'label' => $guideName !== '' ? $guideName : 'Guide',
                'cost' => round($guideCost, 2),
                'meta' => [
                    'guide_id' => $guideId,
                    'hours' => $hours,
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

    /**
     * Shared = unit × pax; Private = unit × vehicle qty (same as enquiry Pro sell / lineCost).
     * Payload `cost` may be way-applied; zone unit costs are per way.
     *
     * @return array{cost: float, meta: array}|null
     */
    private static function resolveTransferOptionsCost(array $item, array $transfer): ?array
    {
        $transferTypeRaw = strtolower(trim((string) (
            $transfer['type']
            ?? $transfer['transferType']
            ?? $transfer['transfer_type']
            ?? ''
        )));
        $isShared = in_array($transferTypeRaw, ['s', 'shared', 'sic'], true);

        $wayRaw = strtolower(trim((string) ($transfer['way'] ?? $transfer['transferWay'] ?? '')));
        $isBothWay = in_array($wayRaw, ['both-way', 'both way', 'both', 'two-way', 'return', '2way'], true);
        $wayMultiplier = $isBothWay ? 2 : 1;

        $adults = self::qtyFromItem($transfer, ['adults', 'adultsQty', 'adult_qty', 'Adults']);
        if ($adults <= 0) {
            $adults = self::qtyFromItem($item, ['adults', 'adultsQty', 'adultCount', 'adult']);
        }
        $children = self::qtyFromItem($transfer, ['children', 'child', 'childQty', 'child_qty', 'Children']);
        if ($children <= 0) {
            $children = self::qtyFromItem($item, ['children', 'child', 'childQty', 'childCount']);
        }
        $pax = max(0, $adults + $children);

        $vehicleQty = self::qtyFromItem($transfer, ['qty', 'quantity', 'vehicle_qty', 'vehicleQty', 'booked_vehicles']);
        if ($vehicleQty <= 0) {
            $vehicleQty = self::qtyFromItem($item, ['vehicle_qty', 'vehicleQty', 'booked_vehicles', 'qty', 'quantity']);
        }
        if ($vehicleQty <= 0) {
            $vehicleQty = 1;
        }

        $explicitLine = self::firstNumeric($transfer, ['lineCost', 'line_cost']);
        $payloadCost = self::firstNumeric($transfer, [
            'cost', 'Cost', 'adult_cost', 'adultCost', 'base_cost', 'baseCost', 'cost_price',
        ]);

        $zoneSharedUnit = self::firstNumeric($transfer, [
            'zoneSharedCostPrice', 'shared_cost_price', 'sharedCostPrice', 'unitCost', 'unit_cost',
        ]);
        $zonePrivateUnit = self::firstNumeric($transfer, [
            'zonePrivateCostPrice', 'private_cost_price', 'privateCostPrice', 'unitCost', 'unit_cost',
        ]);

        $unitCost = 0.0;
        $transferCost = 0.0;

        if ($explicitLine > 0) {
            $transferCost = $explicitLine;
            $unitCost = $isShared && $pax > 0
                ? ($explicitLine / max(1, $pax))
                : ($explicitLine / max(1, $vehicleQty));
        } elseif ($isShared) {
            if ($zoneSharedUnit > 0) {
                $unitCost = $zoneSharedUnit * $wayMultiplier;
            } elseif ($payloadCost > 0) {
                $unitCost = $payloadCost;
            }
            if ($unitCost > 0) {
                $transferCost = $unitCost * max(1, $pax);
            }
        } else {
            if ($zonePrivateUnit > 0) {
                $unitCost = $zonePrivateUnit * $wayMultiplier;
            } elseif ($payloadCost > 0) {
                $unitCost = $payloadCost;
            }
            $transferCost = $unitCost * max(1, $vehicleQty);
        }

        if ($transferCost <= 0) {
            return null;
        }

        return [
            'cost' => round($transferCost, 2),
            'meta' => [
                'type' => $isShared ? 'Shared' : 'Private',
                'way' => $transfer['way'] ?? null,
                'transfer_type' => $isShared ? 'shared' : 'private',
                'adults' => $adults,
                'children' => $children,
                'pax' => $pax,
                'vehicle_qty' => $vehicleQty,
                'unit_cost' => round($unitCost, 2),
                'formula' => $isShared ? 'unit_cost × pax' : 'unit_cost × qty',
            ],
        ];
    }

    private static function appendTransferAndGuideComponents(array $item, array &$components, string &$source): void
    {
        $transfer = is_array($item['transfer_options'] ?? null) ? $item['transfer_options'] : null;
        if (! $transfer && is_array($item['transferInfo'] ?? null)) {
            $transfer = $item['transferInfo'];
        }

        $hasTransfer = $transfer && (
            ! empty($transfer['transfer_required'])
            || ! empty($transfer['vehicle_id'])
            || ! empty($transfer['vehicleId'])
            || self::firstNumeric($transfer, [
                'cost', 'total_cost', 'cost_price', 'sell', 'totalPrice',
                'zoneSharedCostPrice', 'zonePrivateCostPrice', 'shared_cost_price', 'private_cost_price',
            ]) > 0
        );

        if ($hasTransfer) {
            $resolved = self::resolveTransferOptionsCost($item, $transfer);
            if ($resolved) {
                $components[] = [
                    'key' => 'transfer',
                    'label' => 'Transfer',
                    'cost' => $resolved['cost'],
                    'meta' => $resolved['meta'],
                ];
            }
        }

        $guideOptions = is_array($item['guide_options'] ?? null) ? $item['guide_options'] : null;
        $hasGuide = $guideOptions && (
            ! empty($guideOptions['guide_required'])
            || ! empty($guideOptions['guide_id'])
            || ! empty($guideOptions['guideId'])
            || ! empty($guideOptions['guide_name'])
            || ! empty($guideOptions['guideName'])
            || ! empty($guideOptions['name'])
        );
        if ($hasGuide) {
            $guideBuilt = self::buildGuideCost(array_merge($item, [
                'guide_id' => $guideOptions['guide_id'] ?? $guideOptions['guideId'] ?? null,
                'guide_name' => $guideOptions['guide_name']
                    ?? $guideOptions['guideName']
                    ?? $guideOptions['name']
                    ?? 'Guide',
                'hours' => $guideOptions['hours']
                    ?? $guideOptions['service_hours']
                    ?? $guideOptions['package_hours']
                    ?? ($item['hours'] ?? 0),
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
        $source = 'payload';

        // Match enquiry Pro sell logic:
        // Shared: zone/unit cost × pax (adults + children); qty is coverage only.
        // Private: zone/unit cost × vehicle quantity.
        $transferTypeRaw = strtolower(trim((string) (
            $item['transferType']
            ?? $item['transfer_type']
            ?? $item['type']
            ?? ''
        )));
        $isShared = in_array($transferTypeRaw, ['s', 'shared', 'sic'], true);

        $adults = self::qtyFromItem($item, ['adults', 'adultsQty', 'adult_qty', 'Adults']);
        $children = self::qtyFromItem($item, ['children', 'child', 'childQty', 'child_qty', 'Children']);
        $pax = max(0, $adults + $children);

        $vehicleQty = self::qtyFromItem($item, [
            'vehicle_qty', 'vehicleQty', 'booked_vehicles', 'quantity', 'qty',
        ]);
        if ($vehicleQty <= 0) {
            $vehicleQty = 1;
        }
        // Prefer per-vehicle qty from vehicles[] when present
        $vehicles = is_array($item['vehicles'] ?? null) ? $item['vehicles'] : [];
        $primaryVehicle = is_array($vehicles[0] ?? null) ? $vehicles[0] : [];
        if ($primaryVehicle) {
            $vQty = self::qtyFromItem($primaryVehicle, ['qty', 'quantity', 'vehicle_qty', 'vehicleQty']);
            if ($vQty > 0) {
                $vehicleQty = $vQty;
            }
            $vAdults = self::qtyFromItem($primaryVehicle, ['adults', 'adultsQty', 'adult_qty']);
            $vChild = self::qtyFromItem($primaryVehicle, ['children', 'child', 'childQty', 'child_qty']);
            if (($vAdults + $vChild) > 0) {
                $adults = $vAdults;
                $children = $vChild;
                $pax = $adults + $children;
            }
        }

        // Resolve UNIT cost only (never treat line totals as unit).
        $unitCost = 0.0;
        if ($isShared) {
            $unitCost = self::firstNumeric($item, [
                'zoneSharedCostPrice', 'shared_cost_price', 'sharedCostPrice', 'unitCost', 'unit_cost',
            ]);
            if ($unitCost <= 0 && $primaryVehicle) {
                $unitCost = self::firstNumeric($primaryVehicle, [
                    'zoneSharedCostPrice', 'shared_cost_price', 'sharedCostPrice', 'unitCost', 'unit_cost',
                ]);
            }
        } else {
            $unitCost = self::firstNumeric($item, [
                'zonePrivateCostPrice', 'private_cost_price', 'privateCostPrice', 'unitCost', 'unit_cost',
            ]);
            if ($unitCost <= 0 && $primaryVehicle) {
                $unitCost = self::firstNumeric($primaryVehicle, [
                    'zonePrivateCostPrice', 'private_cost_price', 'privateCostPrice', 'unitCost', 'unit_cost',
                ]);
            }
        }

        // Derive unit from lineCost when unit missing (lineCost already matches sell formula)
        if ($unitCost <= 0) {
            $lineCost = self::firstNumeric($item, ['lineCost', 'line_cost']);
            if ($lineCost <= 0 && $primaryVehicle) {
                $lineCost = self::firstNumeric($primaryVehicle, ['lineCost', 'line_cost']);
            }
            if ($lineCost > 0) {
                if ($isShared && $pax > 0) {
                    $unitCost = $lineCost / $pax;
                } elseif (! $isShared && $vehicleQty > 0) {
                    $unitCost = $lineCost / $vehicleQty;
                }
            }
        }

        // Last resort: adultCost/cost may be unit (legacy) — only when no line/unit/zone present
        if ($unitCost <= 0) {
            $unitCost = self::firstNumeric($item, [
                'adult_cost', 'adultCost', 'cost_price', 'base_cost', 'baseCost',
            ]);
            // If `cost` equals line total for shared (≈ unit×pax), peel pax; else treat as unit
            $payloadCost = self::firstNumeric($item, ['cost', 'Cost']);
            if ($unitCost <= 0 && $payloadCost > 0) {
                if ($isShared && $pax > 1 && abs($payloadCost - ($payloadCost / $pax) * $pax) < 0.01) {
                    // Prefer peeling when cost looks like a line total vs tiny unit
                    $maybeUnit = $payloadCost / $pax;
                    // Heuristic: if vehicles also have unitCost use that; else if cost >> typical unit use peel
                    $unitCost = $maybeUnit > 0 ? $maybeUnit : $payloadCost;
                } else {
                    $unitCost = $payloadCost;
                }
            }
        }

        $vehicleCost = 0.0;
        if ($unitCost > 0) {
            if ($isShared) {
                $vehicleCost = $unitCost * max(1, $pax);
            } else {
                $vehicleCost = $unitCost * max(1, $vehicleQty);
            }
        }

        // Prefer explicit lineCost from payload when it already matches the sell-side total
        $explicitLine = self::firstNumeric($item, ['lineCost', 'line_cost']);
        if ($explicitLine <= 0 && $primaryVehicle) {
            $explicitLine = self::firstNumeric($primaryVehicle, ['lineCost', 'line_cost']);
        }
        if ($explicitLine > 0) {
            $vehicleCost = $explicitLine;
        }

        if ($vehicleCost > 0) {
            $components[] = [
                'key' => $type,
                'label' => ucfirst(str_replace('_', ' ', $type)),
                'cost' => round($vehicleCost, 2),
                'meta' => [
                    'vehicle' => $item['vehicles_name'] ?? $item['vehicle_name'] ?? $item['vehicleName']
                        ?? ($primaryVehicle['vehicleName'] ?? $primaryVehicle['vehicle_name'] ?? null),
                    'transfer_type' => $isShared ? 'shared' : 'private',
                    'adults' => $adults,
                    'children' => $children,
                    'pax' => $pax,
                    'vehicle_qty' => $vehicleQty,
                    'unit_cost' => round($unitCost, 2),
                    'formula' => $isShared ? 'unit_cost × pax' : 'unit_cost × qty',
                ],
            ];
        }

        // Guide: always resolve from guides.*_cost_price tiers (never guide_options sell).
        $guideOptions = is_array($item['guide_options'] ?? null) ? $item['guide_options'] : null;
        if ($guideOptions && (
            ! empty($guideOptions['guide_required'])
            || ! empty($guideOptions['guide_id'])
            || ! empty($guideOptions['guideId'])
        )) {
            $guideBuilt = self::buildGuideCost(array_merge($item, [
                'guide_id' => $guideOptions['guide_id'] ?? $guideOptions['guideId'] ?? null,
                'guide_name' => $guideOptions['guide_name'] ?? $guideOptions['guideName'] ?? $guideOptions['name'] ?? 'Guide',
                'hours' => $guideOptions['hours']
                    ?? $guideOptions['service_hours']
                    ?? $guideOptions['package_hours']
                    ?? ($item['hours'] ?? 12),
                'guide_options' => $guideOptions,
            ]));
            foreach ($guideBuilt['components'] as $component) {
                $components[] = $component;
            }
            if (($guideBuilt['source'] ?? '') === 'database') {
                $source = $vehicleCost > 0 ? 'mixed' : 'database';
            }
        }

        return [
            'components' => $components,
            'source' => $source,
        ];
    }

    /**
     * Miscellaneous: unit cost × pax (adult / child / infant).
     * Prefer payload unit costs (what the Pro form showed) so multi-item saves
     * stay correct; fall back to miscellaneous_prices only when payload units are absent.
     */
    private static function buildMiscellaneousCost(array $item): array
    {
        $components = [];
        $source = 'payload';

        $adults = self::qtyFromItem($item, ['adultsQty', 'adults', 'adult', 'adultCount']);
        $children = self::qtyFromItem($item, ['childQty', 'children', 'child', 'childCount']);
        $infants = self::qtyFromItem($item, ['infantQty', 'infants', 'infant', 'infantCount']);

        $misId = $item['mis_id'] ?? $item['misId'] ?? null;
        if ($misId === null || $misId === '') {
            $rawItemId = (string) ($item['itemId'] ?? $item['item_id'] ?? '');
            if (preg_match('/(\d+)\s*$/', $rawItemId, $m)) {
                $misId = (int) $m[1];
            }
        } else {
            $misId = (int) $misId;
        }

        $dmcId = $item['dmc_id'] ?? $item['dmcId'] ?? null;
        $city = trim((string) ($item['city'] ?? $item['destination'] ?? ''));
        if (str_contains($city, ',')) {
            $city = trim(explode(',', $city)[0]);
        }

        $payloadAdult = self::firstNumericIfPresent($item, ['adultCost', 'adult_cost', 'adult_cost_price']);
        $payloadChild = self::firstNumericIfPresent($item, ['childCost', 'child_cost', 'child_cost_price']);
        $payloadInfant = self::firstNumericIfPresent($item, ['infantCost', 'infant_cost', 'infant_cost_price']);
        $hasPayloadUnits = $payloadAdult !== null || $payloadChild !== null || $payloadInfant !== null;

        $adultUnit = 0.0;
        $childUnit = 0.0;
        $infantUnit = 0.0;

        // Form payload wins whenever unit cost keys were sent (including legitimate 0).
        if ($hasPayloadUnits) {
            $adultUnit = (float) ($payloadAdult ?? 0);
            $childUnit = (float) ($payloadChild ?? 0);
            $infantUnit = (float) ($payloadInfant ?? 0);
            $source = 'payload';
        }

        // Fill any missing unit from DB (or all units when payload had none).
        $needDb = ! $hasPayloadUnits
            || ($payloadAdult === null && $payloadChild === null && $payloadInfant === null);
        // Also fill individual nulls when only some payload keys existed.
        $fillAdultFromDb = ! $hasPayloadUnits || $payloadAdult === null;
        $fillChildFromDb = ! $hasPayloadUnits || $payloadChild === null;
        $fillInfantFromDb = ! $hasPayloadUnits || $payloadInfant === null;

        if ($misId > 0 && ($fillAdultFromDb || $fillChildFromDb || $fillInfantFromDb || $needDb)) {
            $priceQuery = MiscellaneousPrice::query()
                ->where('mis_id', $misId)
                ->where('status', 1);
            if ($dmcId !== null && $dmcId !== '') {
                $priceQuery->where('dmc_id', $dmcId);
            }
            $prices = $priceQuery->get();

            // If DMC filter yielded nothing useful for this city, retry city match without DMC.
            $price = self::pickMiscellaneousPriceRow($prices, $city);
            if (! $price && $dmcId !== null && $dmcId !== '' && $city !== '') {
                $pricesAny = MiscellaneousPrice::query()
                    ->where('mis_id', $misId)
                    ->where('status', 1)
                    ->get();
                $price = self::pickMiscellaneousPriceRow($pricesAny, $city, true);
            }

            if ($price) {
                if ($fillAdultFromDb) {
                    $adultUnit = (float) ($price->adult_cost ?? 0);
                }
                if ($fillChildFromDb) {
                    $childUnit = (float) ($price->child_cost ?? 0);
                }
                if ($fillInfantFromDb) {
                    $infantUnit = (float) ($price->infant_cost ?? 0);
                }
                $source = $hasPayloadUnits ? 'mixed' : 'database';
            }
        }

        $total = ($adultUnit * max(0, $adults))
            + ($childUnit * max(0, $children))
            + ($infantUnit * max(0, $infants));

        if ($total <= 0) {
            // Explicit line total cost if provided (never use sell/totalPrice).
            $total = self::firstNumeric($item, ['total_cost', 'cost_price', 'cost', 'Cost']);
        }

        if ($total > 0) {
            $label = trim((string) (
                $item['itemName']
                ?? $item['item_name']
                ?? $item['name']
                ?? 'Miscellaneous'
            ));
            if ($label === '' && $misId > 0) {
                $dbItem = MiscellaneousItem::query()->where('mis_id', $misId)->first();
                $label = $dbItem->item_name ?? 'Miscellaneous';
            }

            $components[] = [
                'key' => 'miscellaneous',
                'label' => $label !== '' ? $label : 'Miscellaneous',
                'cost' => round($total, 2),
                'meta' => [
                    'mis_id' => $misId ?: null,
                    'adults' => $adults,
                    'children' => $children,
                    'infants' => $infants,
                    'adult_unit_cost' => $adultUnit,
                    'child_unit_cost' => $childUnit,
                    'infant_unit_cost' => $infantUnit,
                ],
            ];
        }

        return [
            'components' => $components,
            'source' => $source,
        ];
    }

    /**
     * Pick best miscellaneous_prices row for a city (exact city first; optional blank-city fallback).
     *
     * @param  \Illuminate\Support\Collection<int, mixed>  $prices
     */
    private static function pickMiscellaneousPriceRow($prices, string $city, bool $exactCityOnly = false)
    {
        if ($prices === null || $prices->isEmpty()) {
            return null;
        }
        if ($city !== '') {
            $exact = $prices->first(function ($p) use ($city) {
                return strcasecmp(trim((string) ($p->city ?? '')), $city) === 0;
            });
            if ($exact) {
                return $exact;
            }
            if ($exactCityOnly) {
                return null;
            }
        }
        if ($exactCityOnly) {
            return null;
        }

        return $prices->first(function ($p) {
            return trim((string) ($p->city ?? '')) === '';
        }) ?: $prices->first();
    }

    /**
     * Like firstNumeric, but returns null when none of the keys are present (so 0 can still win).
     *
     * @param  array<string, mixed>  $source
     * @param  list<string>  $keys
     */
    private static function firstNumericIfPresent(array $source, array $keys): ?float
    {
        foreach ($keys as $key) {
            if (! array_key_exists($key, $source)) {
                continue;
            }
            if (is_numeric($source[$key])) {
                return (float) $source[$key];
            }
        }

        return null;
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

    private static function pickPositive($preferred, $fallback): float
    {
        $preferred = floatval($preferred ?? 0);
        if ($preferred > 0) {
            return $preferred;
        }

        return floatval($fallback ?? 0);
    }

    private static function resolveHotelUniqueId(array $item): ?string
    {
        $id = $item['hotelDetails']['hotel_id']
            ?? $item['hotelDetails']['hotel_unique_id']
            ?? $item['hotel_unique_id']
            ?? $item['hotelId']
            ?? null;

        if ($id === null || $id === '') {
            return null;
        }

        return (string) $id;
    }

    private static function resolveWeekendDays(?string $hotelUniqueId): array
    {
        $default = ['Saturday', 'Sunday'];
        if (! $hotelUniqueId) {
            return $default;
        }

        $hotel = Hotel::query()->where('hotel_unique_id', $hotelUniqueId)->first();
        if (! $hotel || empty($hotel->weekend_days)) {
            return $default;
        }

        $decoded = is_string($hotel->weekend_days)
            ? json_decode($hotel->weekend_days, true)
            : $hotel->weekend_days;

        return (is_array($decoded) && ! empty($decoded)) ? $decoded : $default;
    }

    private static function applicableRate(?string $hotelUniqueId, Carbon $date, $dmcId = null): ?Rate
    {
        if (! $hotelUniqueId) {
            return null;
        }

        $dateString = $date->toDateString();
        $query = Rate::query()
            ->where('hotel_id', $hotelUniqueId)
            ->where('is_active', 1)
            ->whereDate('start_date', '<=', $dateString)
            ->whereDate('end_date', '>=', $dateString);

        if (! empty($dmcId)) {
            // Pro calendar loads all hotel rates (no hard DMC-only filter). Prefer matching DMC when set.
            $dmcId = (int) $dmcId;
        }

        // Match EnquiryFormPro getHotelsByDestination: all active rates for hotel (Blackout > Fair > Season).
        return $query->orderByRaw("
                CASE
                    WHEN event_type = 'Blackout Date' THEN 1
                    WHEN event_type = 'Fair Date' THEN 2
                    WHEN event_type = 'Season' THEN 3
                    ELSE 4
                END
            ")->first();
    }

    /**
     * Room night cost with Season / Fair / Blackout awareness (mirrors sell logic on cost columns).
     *
     * @return array{cost: float, occupancy: string, day_type: string, event_type: ?string, source: string, surcharge_cost: float, rate: ?Rate}
     */
    private static function roomNightCostDetail(
        Room $room,
        Carbon $date,
        bool $useDouble,
        ?string $hotelUniqueId,
        $dmcId,
        array $weekendDays
    ): array {
        $weekend = self::isWeekend($date, $weekendDays);
        $costSingle = self::pickPositive(
            $weekend ? ($room->weekend_cost_price ?? null) : ($room->weekday_cost_price ?? null),
            $weekend ? ($room->weekend_price ?? 0) : ($room->weekday_price ?? 0)
        );
        $costDouble = self::pickPositive(
            $weekend ? ($room->double_weekend_cost_price ?? null) : ($room->double_weekday_cost_price ?? null),
            $weekend ? ($room->double_weekend_price ?? 0) : ($room->double_weekday_price ?? 0)
        );
        $base = $useDouble ? ($costDouble > 0 ? $costDouble : $costSingle) : $costSingle;
        $eventType = null;
        $source = 'room';
        $surchargeCost = 0.0;
        $rate = self::applicableRate($hotelUniqueId, $date, $dmcId);

        if ($rate) {
            $eventType = $rate->event_type;
            $source = 'rate';
            if ($eventType === 'Blackout Date') {
                $base = self::pickPositive($rate->price_cost ?? null, $base);
            } elseif ($eventType === 'Fair Date') {
                $surchargeCost = self::pickPositive($rate->price_cost ?? null, $rate->price ?? 0);
                $base = $base + $surchargeCost;
            } elseif ($eventType === 'Season') {
                if ($useDouble) {
                    $season = self::pickPositive(
                        $weekend ? ($rate->double_weekend_cost_price ?? null) : ($rate->double_weekday_cost_price ?? null),
                        $weekend ? ($rate->double_weekend_price ?? null) : ($rate->double_weekday_price ?? null)
                    );
                    if ($season <= 0) {
                        $season = self::pickPositive(
                            $weekend ? ($rate->weekend_cost_price ?? null) : ($rate->weekday_cost_price ?? null),
                            $weekend ? ($rate->weekend_price ?? null) : ($rate->weekday_price ?? null)
                        );
                    }
                } else {
                    $season = self::pickPositive(
                        $weekend ? ($rate->weekend_cost_price ?? null) : ($rate->weekday_cost_price ?? null),
                        $weekend ? ($rate->weekend_price ?? null) : ($rate->weekday_price ?? null)
                    );
                }
                if ($season > 0) {
                    $base = $season;
                }
            }
        }

        return [
            'cost' => round(max(0, $base), 2),
            'occupancy' => $useDouble ? 'double' : 'single',
            'day_type' => $weekend ? 'weekend' : 'weekday',
            'event_type' => $eventType,
            'source' => $source,
            'surcharge_cost' => round($surchargeCost, 2),
            'rate' => $rate,
        ];
    }

    private static function mealUnitCostForNight(Room $room, ?Rate $rate, string $mealKey): float
    {
        $roomCost = $room->{$mealKey . '_cost_price'} ?? null;
        $roomSell = $room->{$mealKey . '_price'} ?? 0;
        $fallback = self::pickPositive($roomCost, $roomSell);
        if (! $rate) {
            return $fallback;
        }

        $rateCost = floatval($rate->{$mealKey . '_cost_price'} ?? 0);
        $rateSell = floatval($rate->{$mealKey . '_price'} ?? 0);

        // Prefer rate cost when set; else rate sell; else room cost/sell.
        if ($rateCost > 0) {
            return $rateCost;
        }
        if ($rateSell > 0) {
            return $rateSell;
        }

        return $fallback;
    }

    private static function roomNightCost(Room $room, Carbon $date, bool $useDouble): float
    {
        $detail = self::roomNightCostDetail($room, $date, $useDouble, null, null, ['Saturday', 'Sunday']);

        return (float) $detail['cost'];
    }

    private static function isWeekend(Carbon $date, array $weekendDays = ['Saturday', 'Sunday']): bool
    {
        if (empty($weekendDays)) {
            $weekendDays = ['Saturday', 'Sunday'];
        }

        return in_array($date->format('l'), $weekendDays, true);
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
     * Fix snapshot quirks: room × number_of_rooms, addon COST (not sell), meta.quantity (not children).
     */
    private static function normalizeLodgingSnapshotComponents(array $components, array $item, int $nights): array
    {
        $rooms = is_array($item['rooms'] ?? null) ? $item['rooms'] : [];
        $roomId = (int) (($rooms[0]['room_id'] ?? 0));
        $numberOfRooms = max(1, (int) (($rooms[0]['number_of_rooms'] ?? 1)));
        $bedId = null;
        if (! empty($rooms[0]['beds'][0]['bed_id'])) {
            $bedId = $rooms[0]['beds'][0]['bed_id'];
        }

        $roomModel = null;
        if ($roomId > 0) {
            $roomModel = Room::query()->where('room_id', $roomId)->first();
        }

        foreach ($components as &$component) {
            if (! is_array($component)) {
                continue;
            }
            $key = (string) ($component['key'] ?? '');
            $meta = is_array($component['meta'] ?? null) ? $component['meta'] : [];

            if ($key === 'room') {
                $nr = max(1, (int) ($meta['number_of_rooms'] ?? $numberOfRooms));
                $perNight = is_array($meta['per_night'] ?? null) ? $meta['per_night'] : [];
                $alreadyScaled = ! empty($meta['rooms_applied']);
                if ($nr > 1 && ! $alreadyScaled && ! empty($perNight)) {
                    $sumUnit = 0.0;
                    foreach ($perNight as $night) {
                        $sumUnit += (float) ($night['cost'] ?? 0);
                    }
                    $roomCost = (float) ($component['cost'] ?? 0);
                    // per_night still per-room unit while component.cost already × rooms
                    $looksUnscaled = $sumUnit > 0 && abs($roomCost - ($sumUnit * $nr)) < max(1.0, $nr * 0.5);
                    // or both still unscaled
                    $bothUnscaled = $sumUnit > 0 && abs($roomCost - $sumUnit) < 0.02;
                    if ($looksUnscaled || $bothUnscaled) {
                        $scaledNights = [];
                        $scaledTotal = 0.0;
                        foreach ($perNight as $night) {
                            if (! is_array($night)) {
                                continue;
                            }
                            $row = $night;
                            foreach (['cost', 'room_cost_with_surcharge', 'surcharge_cost'] as $field) {
                                if (isset($row[$field]) && is_numeric($row[$field])) {
                                    $row[$field] = round(((float) $row[$field]) * $nr, 2);
                                }
                            }
                            $mealTotal = (float) ($row['meal_cost_total'] ?? 0);
                            $extra = (float) ($row['extra_bed_cost'] ?? 0);
                            $cwb = (float) ($row['child_with_bed_cost'] ?? 0);
                            $cnb = (float) ($row['child_without_bed_cost'] ?? 0);
                            $row['night_cost_total'] = round(
                                (float) ($row['room_cost_with_surcharge'] ?? $row['cost'] ?? 0)
                                + $mealTotal + $extra + $cwb + $cnb,
                                2
                            );
                            $scaledNights[] = $row;
                            $scaledTotal += (float) ($row['cost'] ?? 0);
                        }
                        $meta['per_night'] = $scaledNights;
                        $meta['number_of_rooms'] = $nr;
                        $meta['rooms_applied'] = true;
                        $meta['note'] = 'per_night.cost / room_cost_with_surcharge already × number_of_rooms; night_cost_total = room + meals + add-ons';
                        if ($bothUnscaled) {
                            $component['cost'] = round($scaledTotal, 2);
                        }
                        $component['meta'] = $meta;
                    }
                } else {
                    $meta['rooms_applied'] = true;
                    $component['meta'] = $meta;
                }
                continue;
            }

            if ($key === 'extra_bed') {
                $qty = max(0, (int) ($meta['quantity'] ?? $meta['children'] ?? 0));
                $unit = 0.0;
                if ($bedId && Schema::hasColumn('beds', 'extra_bed_cost_price')) {
                    $bed = Bed::query()->where('bed_id', $bedId)->first();
                    if ($bed && is_numeric($bed->extra_bed_cost_price ?? null)) {
                        $unit = (float) $bed->extra_bed_cost_price;
                    }
                }
                $nightCount = max(1, (int) ($meta['nights'] ?? $nights));
                $component['cost'] = round($unit * $qty * $nightCount, 2);
                $component['meta'] = [
                    'quantity' => $qty,
                    'unit_cost' => $unit,
                    'nights' => $nightCount,
                    'source' => $unit > 0 ? 'database' : 'view_details',
                ];
                continue;
            }

            if ($key === 'child_with_bed' || $key === 'child_without_bed') {
                $qty = max(0, (int) ($meta['quantity'] ?? $meta['children'] ?? 0));
                $column = $key === 'child_with_bed' ? 'child_with_bed_cost' : 'child_without_bed_cost';
                $unit = 0.0;
                if ($roomModel && Schema::hasColumn('rooms', $column) && is_numeric($roomModel->{$column} ?? null)) {
                    $unit = (float) $roomModel->{$column};
                }
                $nightCount = max(1, (int) ($meta['nights'] ?? $nights));
                $component['cost'] = round($unit * $qty * $nightCount, 2);
                $component['meta'] = [
                    'quantity' => $qty,
                    'unit_cost' => $unit,
                    'nights' => $nightCount,
                    'source' => $unit > 0 ? 'database' : 'view_details',
                ];
            }
        }
        unset($component);

        return $components;
    }

    /**
     * Sum order booking totalPrice values country-wise for a tour.
     * actual_price = sum of totalPrice for bookings in that country
     * (attraction/restaurant also include transfer_options.cost + guide_options.total_price).
     * current_price = actual_price after applying currency_markups discount_type/discount_value
     * (markup row matched by booking city, then country, then currency — applied once per country).
     *
     * @param  int|string  $tourId
     * @return array<string, array{
     *   country: string,
     *   actual_price: float,
     *   current_price: float,
     *   currency: string|null,
     *   discount_type: string|null,
     *   discount_value: float
     * }>
     */
    public static function getCountryWiseTotalPrice($tourId): array
    {
        $tourId = trim((string) $tourId);
        if ($tourId === '') {
            return [];
        }

        $tour = \App\Models\Tour::where('tour_id', $tourId)->first();
        $markups = [];
        if ($tour) {
            $raw = $tour->currency_markups ?? null;
            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                $markups = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
            } elseif (is_array($raw)) {
                $markups = $raw;
            }
        }

        $destinationCountries = self::parseDestinationCountries($tour?->destination ?? null);

        $orders = Order::query()
            ->where('tour_id', $tourId)
            ->get(['booking_id', 'tour_id', 'type', 'data', 'country', 'city', 'currency']);

        // country => ['actual' => float, 'city_totals' => [city => float], 'currency' => ?string]
        $buckets = [];

        foreach ($destinationCountries as $countryName) {
            $key = mb_strtolower($countryName);
            $buckets[$key] = [
                'country' => $countryName,
                'actual' => 0.0,
                'city_totals' => [],
                'currency' => null,
            ];
        }

        foreach ($orders as $order) {
            $data = is_string($order->data) ? json_decode($order->data, true) : $order->data;
            if (! is_array($data)) {
                continue;
            }

            $items = (isset($data[0]) && is_array($data[0])) ? $data : [$data];
            $orderType = strtolower(trim((string) ($order->type ?? '')));
            $orderCountry = trim((string) ($order->country ?? ''));
            $orderCity = trim((string) ($order->city ?? ''));
            $orderCurrency = strtoupper(trim((string) ($order->currency ?? '')));

            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $country = trim((string) ($item['country'] ?? $orderCountry));
                $city = trim((string) ($item['city'] ?? $orderCity));
                $currency = strtoupper(trim((string) ($item['currency'] ?? $orderCurrency)));
                $price = self::bookingItemSellTotal($item, $orderType);

                if ($country === '') {
                    continue;
                }

                $key = mb_strtolower($country);
                if (! isset($buckets[$key])) {
                    $buckets[$key] = [
                        'country' => $country,
                        'actual' => 0.0,
                        'city_totals' => [],
                        'currency' => null,
                    ];
                }

                $buckets[$key]['actual'] += $price;

                $cityKey = $city !== '' ? $city : '_';
                if (! isset($buckets[$key]['city_totals'][$cityKey])) {
                    $buckets[$key]['city_totals'][$cityKey] = 0.0;
                }
                $buckets[$key]['city_totals'][$cityKey] += $price;

                if (($buckets[$key]['currency'] === null || $buckets[$key]['currency'] === '') && $currency !== '') {
                    $buckets[$key]['currency'] = $currency;
                }
            }
        }

        $result = [];
        foreach ($buckets as $key => $bucket) {
            $actual = round((float) $bucket['actual'], 2);
            $discountMeta = self::resolveCountryDiscountMeta(
                (string) $bucket['country'],
                is_array($bucket['city_totals']) ? $bucket['city_totals'] : [],
                $bucket['currency'] ?? null,
                $markups
            );
            $current = self::applyDiscountAmount(
                (float) $bucket['actual'],
                $discountMeta['discount_type'],
                (float) $discountMeta['discount_value']
            );

            $result[$bucket['country']] = [
                'country' => $bucket['country'],
                'actual_price' => $actual,
                'current_price' => round(max(0, $current), 2),
                'currency' => $bucket['currency'],
                'discount_type' => $discountMeta['discount_type'],
                'discount_value' => $discountMeta['discount_value'],
            ];
        }

        return $result;
    }

    /**
     * Sell total for one booking line: totalPrice plus attraction/restaurant
     * transfer_options.cost and guide_options.total_price when present.
     *
     * @param  array<string, mixed>  $item
     */
    private static function bookingItemSellTotal(array $item, string $orderType): float
    {
        $price = (float) ($item['totalPrice'] ?? $item['price'] ?? 0);

        $isAttractionOrRestaurant = in_array($orderType, [
            'attraction',
            'attractions',
            'restaurant',
            'restaurants',
            'restaurent', // legacy typo used elsewhere in codebase
        ], true);

        // Also detect from payload when order.type is missing/generic
        if (! $isAttractionOrRestaurant) {
            $isAttractionOrRestaurant = isset($item['AttractionId'])
                || isset($item['AttractionName'])
                || isset($item['restaurantId'])
                || isset($item['restaurantName']);
        }

        if (! $isAttractionOrRestaurant) {
            return $price;
        }

        $transfer = 0.0;
        if (isset($item['transfer_options']) && is_array($item['transfer_options'])) {
            $transfer = (float) ($item['transfer_options']['cost']
                ?? $item['transfer_options']['totalPrice']
                ?? 0);
        }

        $guide = 0.0;
        if (isset($item['guide_options']) && is_array($item['guide_options'])) {
            $guide = (float) ($item['guide_options']['total_price']
                ?? $item['guide_options']['cost']
                ?? $item['guide_options']['Cost']
                ?? 0);
        }

        return $price + max(0, $transfer) + max(0, $guide);
    }

    /**
     * @return array<int, string>
     */
    private static function parseDestinationCountries(?string $destination): array
    {
        if (! is_string($destination) || trim($destination) === '') {
            return [];
        }

        $parts = preg_split('/\s*,\s*/', $destination) ?: [];
        $names = [];
        foreach ($parts as $part) {
            $name = trim((string) $part);
            if ($name !== '') {
                $names[] = $name;
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * Resolve discount for a country: prefer a markup row whose city appears in this
     * country's bookings, else match by country, else by currency.
     *
     * @param  array<string, float>  $cityTotals
     * @param  array<int, array<string, mixed>>  $markups
     * @return array{discount_type: string|null, discount_value: float}
     */
    private static function resolveCountryDiscountMeta(
        string $country,
        array $cityTotals,
        ?string $currency,
        array $markups
    ): array {
        $row = null;

        foreach (array_keys($cityTotals) as $cityKey) {
            if ($cityKey === '_') {
                continue;
            }
            $row = self::lookupCurrencyMarkupRow($markups, (string) $cityKey, '', '');
            if ($row) {
                break;
            }
        }

        if (! $row) {
            $row = self::lookupCurrencyMarkupRow(
                $markups,
                '',
                $country,
                (string) ($currency ?? '')
            );
        }

        $type = isset($row['discount_type']) ? strtolower(trim((string) $row['discount_type'])) : '';
        if ($type === 'fixed') {
            $type = 'flat';
        }

        return [
            'discount_type' => in_array($type, ['percentage', 'flat', 'foc'], true) ? $type : null,
            'discount_value' => (float) ($row['discount_value'] ?? 0),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $markups
     * @return array<string, mixed>|null
     */
    private static function lookupCurrencyMarkupRow(
        array $markups,
        string $city,
        string $country,
        string $currency
    ): ?array {
        $city = trim($city);
        if ($city !== '') {
            foreach ($markups as $row) {
                if (! is_array($row)) {
                    continue;
                }
                if (strcasecmp((string) ($row['city'] ?? ''), $city) === 0) {
                    return $row;
                }
            }
        }

        $country = trim($country);
        if ($country !== '') {
            foreach ($markups as $row) {
                if (! is_array($row)) {
                    continue;
                }
                if (strcasecmp((string) ($row['country'] ?? ''), $country) === 0) {
                    return $row;
                }
            }
        }

        $currency = strtoupper(trim($currency));
        if ($currency !== '') {
            foreach ($markups as $row) {
                if (! is_array($row)) {
                    continue;
                }
                if (strtoupper(trim((string) ($row['currency'] ?? ''))) === $currency) {
                    return $row;
                }
            }
        }

        return null;
    }

    /**
     * current = actual - discount (percentage / flat / foc). Never below zero.
     */
    private static function applyDiscountAmount(float $actual, ?string $discountType, float $discountValue): float
    {
        $type = strtolower(trim((string) $discountType));
        if ($type === 'fixed') {
            $type = 'flat';
        }

        $discountMoney = 0.0;
        if ($type === 'percentage' && $discountValue > 0) {
            $discountMoney = $actual * $discountValue / 100;
        } elseif (in_array($type, ['flat', 'foc'], true) && $discountValue > 0) {
            $discountMoney = $discountValue;
        }

        return max(0, $actual - $discountMoney);
    }
}
