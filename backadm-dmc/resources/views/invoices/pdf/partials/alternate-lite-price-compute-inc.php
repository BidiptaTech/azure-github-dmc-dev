<?php

/**
 * Loaded from invoices.pdf.alternate (@php) when mode=price-only — must run in parent scope (not via Blade @include).
 *
 * @var \Illuminate\View\View $invoice injected from view data
 * @var mixed $exchangeRate
 * @var string $selectedCurrency — base currency string already uppercased by parent
 */

$exchangeRateLite = isset($exchangeRate) ? (float) $exchangeRate : 1.0;
$selectedCurrencyLite = strtoupper($selectedCurrency ?? 'SGD');
$litePriceIsPro = $invoice->tour && (int) ($invoice->tour->is_pro ?? 0) === 1;

$getAttractionPrices = function ($item, $serviceDetails) use ($invoice, $litePriceIsPro) {
    $basePrice = 0;
    $transferCost = 0;
    $guideTotalPrice = 0;

    if ($litePriceIsPro && $invoice->tour) {
        $orders = \App\Models\Order::where('tour_id', $invoice->tour->tour_id)
            ->where('type', 'attraction')
            ->whereNull('deleted_at')
            ->get();
        $itemAttractionName = trim($serviceDetails['attraction_name'] ?? $item->description ?? '');
        $itemBookingDate = $serviceDetails['booking_date'] ?? '';
        foreach ($orders as $order) {
            $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
            if (! is_array($orderData)) {
                continue;
            }
            $bookings = isset($orderData[0]) && is_array($orderData[0]) ? $orderData : [$orderData];
            foreach ($bookings as $booking) {
                if (! is_array($booking)) {
                    continue;
                }
                $bookingAttractionName = trim($booking['AttractionName'] ?? '');
                $bookingDate = $booking['bookingDate'] ?? $booking['date'] ?? '';
                if ($itemAttractionName && $bookingAttractionName && strtolower($itemAttractionName) === strtolower($bookingAttractionName) && $itemBookingDate == $bookingDate) {
                    $basePrice = (float) ($booking['price'] ?? $booking['totalPrice'] ?? 0);
                    $transferCost = 0;
                    if (isset($booking['transfer_options']['cost']) && $booking['transfer_options']['cost'] > 0) {
                        $transferCost = isset($booking['transfer_options']['totalPrice']) ? (float) $booking['transfer_options']['totalPrice'] : (float) $booking['transfer_options']['cost'];
                    }
                    $guideTotalPrice = isset($booking['guide_options']['total_price']) && $booking['guide_options']['total_price'] > 0 ? (float) $booking['guide_options']['total_price'] : 0;

                    return ['base' => $basePrice, 'transfer' => $transferCost, 'guide' => $guideTotalPrice, 'total' => $basePrice + $transferCost + $guideTotalPrice];
                }
            }
        }
    }

    if (isset($serviceDetails['attraction_base_price']) || isset($serviceDetails['transfer_cost']) || isset($serviceDetails['guide_total_price'])) {
        $basePrice = $serviceDetails['attraction_base_price'] ?? 0;
        $transferCost = $serviceDetails['transfer_cost'] ?? 0;
        $guideTotalPrice = $serviceDetails['guide_total_price'] ?? 0;

        return [
            'base' => $basePrice,
            'transfer' => $transferCost,
            'guide' => $guideTotalPrice,
            'total' => $basePrice + $transferCost + $guideTotalPrice,
        ];
    }

    if ($invoice->tour) {
        $orders = \App\Models\Order::where('tour_id', $invoice->tour->tour_id)
            ->where('type', 'attraction')
            ->whereNull('deleted_at')
            ->get();

        foreach ($orders as $order) {
            $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
            if (! is_array($orderData)) {
                continue;
            }

            $bookings = isset($orderData[0]) && is_array($orderData[0]) ? $orderData : [$orderData];
            foreach ($bookings as $booking) {
                if (! is_array($booking)) {
                    continue;
                }

                $itemAttractionName = $serviceDetails['attraction_name'] ?? '';
                $bookingAttractionName = $booking['AttractionName'] ?? '';

                if ($itemAttractionName && $bookingAttractionName
                    && strtolower(trim($itemAttractionName)) === strtolower(trim($bookingAttractionName))) {
                    $basePrice = (float) ($booking['price'] ?? $booking['totalPrice'] ?? 0);
                    $transferCost = 0;
                    if (isset($booking['transfer_options']['cost']) && $booking['transfer_options']['cost'] > 0) {
                        if ($litePriceIsPro && isset($booking['transfer_options']['totalPrice'])) {
                            $transferCost = (float) $booking['transfer_options']['totalPrice'];
                        } else {
                            $transferCost = (float) $booking['transfer_options']['cost'];
                        }
                    }
                    $guideTotalPrice = isset($booking['guide_options']['total_price']) && $booking['guide_options']['total_price'] > 0 ? (float) $booking['guide_options']['total_price'] : 0;

                    return [
                        'base' => $basePrice,
                        'transfer' => $transferCost,
                        'guide' => $guideTotalPrice,
                        'total' => $basePrice + $transferCost + $guideTotalPrice,
                    ];
                }
            }
        }
    }

    $fallbackTotal = $item->total_price ?? 0;

    return [
        'base' => $fallbackTotal,
        'transfer' => 0,
        'guide' => 0,
        'total' => $fallbackTotal,
    ];
};

$getRestaurantPrices = function ($item, $serviceDetails) use ($invoice, $litePriceIsPro) {
    $basePrice = 0;
    $transferCost = 0;
    $guideCost = 0;

    if ($litePriceIsPro && $invoice->tour) {
        $orders = \App\Models\Order::where('tour_id', $invoice->tour->tour_id)->where('type', 'restaurant')->whereNull('deleted_at')->get();
        $itemRestaurantName = trim($serviceDetails['restaurant_name'] ?? $item->description ?? '');
        if (! $itemRestaurantName && ! empty($item->description)) {
            $itemRestaurantName = trim(explode(' - ', $item->description)[0] ?? '');
        }
        $itemBookingDate = $serviceDetails['booking_date'] ?? '';
        $itemMealType = $serviceDetails['meal_type'] ?? '';
        $itemHasTransfer = isset($serviceDetails['transfer_required']) && ($serviceDetails['transfer_required'] === 'Yes' || $serviceDetails['transfer_required'] === true || $serviceDetails['transfer_required'] === 'true');
        foreach ($orders as $order) {
            $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
            if (! is_array($orderData)) {
                continue;
            }
            $bookings = isset($orderData[0]) && is_array($orderData[0]) ? $orderData : [$orderData];
            foreach ($bookings as $booking) {
                if (! is_array($booking)) {
                    continue;
                }
                $bName = trim($booking['restaurantName'] ?? $booking['restaurant_name'] ?? '');
                $bDate = $booking['bookingDate'] ?? $booking['date'] ?? '';
                $bMeal = $booking['mealType'] ?? $booking['meal_type'] ?? '';
                $bHasTransfer = isset($booking['transfer_options']['transfer_required']) && ($booking['transfer_options']['transfer_required'] === true || $booking['transfer_options']['transfer_required'] === 'Yes' || $booking['transfer_options']['transfer_required'] === 'true');
                if ($itemRestaurantName && $bName && strtolower($itemRestaurantName) === strtolower($bName) && $itemBookingDate == $bDate && (! $itemMealType || $itemMealType == $bMeal) && $itemHasTransfer === $bHasTransfer) {
                    $basePrice = (float) ($booking['mealPrice'] ?? $booking['totalPrice'] ?? 0);
                    $transferCost = 0;
                    if ($bHasTransfer && isset($booking['transfer_options']['cost']) && $booking['transfer_options']['cost'] > 0) {
                        $transferCost = isset($booking['transfer_options']['totalPrice']) ? (float) $booking['transfer_options']['totalPrice'] : (float) $booking['transfer_options']['cost'];
                    }
                    $guideCost = 0;
                    if (! empty($booking['guide_options'])) {
                        $gv = $booking['guide_options']['total_price'] ?? $booking['guide_options']['cost'] ?? $booking['guide_options']['Cost'] ?? $booking['guide_options']['sell'] ?? $booking['guide_options']['Sell'] ?? 0;
                        if ((float) $gv > 0) {
                            $guideCost = (float) $gv;
                        }
                    }

                    return ['base' => $basePrice, 'transfer' => $transferCost, 'guide' => $guideCost, 'total' => $basePrice + $transferCost + $guideCost];
                }
            }
        }
    }

    if (isset($serviceDetails['restaurant_base_price']) || isset($serviceDetails['transfer_cost']) || isset($serviceDetails['guide_total_price'])) {
        $basePrice = $serviceDetails['restaurant_base_price'] ?? 0;
        $transferCost = $serviceDetails['transfer_cost'] ?? 0;
        $guideCost = $litePriceIsPro ? (float) ($serviceDetails['guide_total_price'] ?? 0) : 0;

        return ['base' => $basePrice, 'transfer' => $transferCost, 'guide' => $guideCost, 'total' => $basePrice + $transferCost + $guideCost];
    }

    if ($invoice->tour) {
        $orders = \App\Models\Order::where('tour_id', $invoice->tour->tour_id)->where('type', 'restaurant')->whereNull('deleted_at')->get();
        foreach ($orders as $order) {
            $orderData = is_string($order->data) ? json_decode($order->data, true) : $order->data;
            if (! is_array($orderData)) {
                continue;
            }
            $bookings = isset($orderData[0]) && is_array($orderData[0]) ? $orderData : [$orderData];
            foreach ($bookings as $booking) {
                if (! is_array($booking)) {
                    continue;
                }
                $itemRestaurantName = $serviceDetails['restaurant_name'] ?? '';
                if (! $itemRestaurantName) {
                    $desc = $item->description ?? '';
                    $itemRestaurantName = trim(explode(' - ', $desc)[0] ?? '');
                }
                $bookingRestaurantName = $booking['restaurantName'] ?? ($booking['restaurant_name'] ?? '');
                if ($itemRestaurantName && $bookingRestaurantName && strtolower(trim($itemRestaurantName)) === strtolower(trim($bookingRestaurantName))) {
                    $basePrice = (float) ($booking['mealPrice'] ?? $booking['totalPrice'] ?? 0);
                    $transferCost = 0;
                    if (isset($booking['transfer_options']['cost']) && $booking['transfer_options']['cost'] > 0) {
                        if ($litePriceIsPro && isset($booking['transfer_options']['totalPrice'])) {
                            $transferCost = (float) $booking['transfer_options']['totalPrice'];
                        } else {
                            $transferCost = (float) $booking['transfer_options']['cost'];
                        }
                    }
                    $guideCost = 0;
                    if ($litePriceIsPro && ! empty($booking['guide_options'])) {
                        $gv = $booking['guide_options']['total_price'] ?? $booking['guide_options']['cost'] ?? $booking['guide_options']['Cost'] ?? $booking['guide_options']['sell'] ?? $booking['guide_options']['Sell'] ?? 0;
                        if ((float) $gv > 0) {
                            $guideCost = (float) $gv;
                        }
                    }

                    return ['base' => $basePrice, 'transfer' => $transferCost, 'guide' => $guideCost, 'total' => $basePrice + $transferCost + $guideCost];
                }
            }
        }
    }

    $fallbackTotal = $item->total_price ?? 0;

    return ['base' => $fallbackTotal, 'transfer' => 0, 'guide' => 0, 'total' => $fallbackTotal];
};

$notesLite = is_string($invoice->notes) ? json_decode($invoice->notes, true) : ($invoice->notes ?? []);
$ordersTotal = $notesLite['orders_total'] ?? null;
$baseAmountLite = $notesLite['base_amount'] ?? null;

$correctedTotalAmount = null;
if ($litePriceIsPro && $invoice->tour) {
    $correctedTotalAmount = 0;
    foreach ($invoice->items as $invItem) {
        $invSd = is_string($invItem->service_details) ? json_decode($invItem->service_details, true) : ($invItem->service_details ?? []);
        if (! is_array($invSd)) {
            $invSd = [];
        }
        if ($invItem->item_type === 'attraction') {
            $p = $getAttractionPrices($invItem, $invSd);
            $correctedTotalAmount += $p['total'];
        } elseif ($invItem->item_type === 'restaurant') {
            $p = $getRestaurantPrices($invItem, $invSd);
            $correctedTotalAmount += $p['total'];
        } else {
            $correctedTotalAmount += (float) ($invItem->total_price ?? 0);
        }
    }
}

$liteActualAmount = ($litePriceIsPro && $correctedTotalAmount !== null)
    ? $correctedTotalAmount
    : ($ordersTotal !== null ? $ordersTotal : $invoice->items->sum('total_price'));
if ($litePriceIsPro && $correctedTotalAmount !== null) {
    if ($ordersTotal !== null && $baseAmountLite !== null) {
        $storedDiscount = max(0, (float) $ordersTotal - (float) $baseAmountLite);
        $baseAmountLite = max(0, $correctedTotalAmount - $storedDiscount);
    } else {
        $neg = $invoice->getNegotiatedAmount();
        $baseAmountLite = $neg ?? $correctedTotalAmount;
    }
} elseif ($baseAmountLite === null) {
    $neg = $invoice->getNegotiatedAmount();
    $baseAmountLite = $neg ?? $liteActualAmount;
}
$liteNegotiatedAmount = $baseAmountLite;
$liteDiscountVsActual = $liteActualAmount - $baseAmountLite;

$tourLite = $invoice->tour;
$tourStatusLite = $tourLite->tour_status ?? '';
$statusesWithTaxLite = ['Confirmed', 'Definite', 'Actual'];
$liteShouldShowTax = in_array($tourStatusLite, $statusesWithTaxLite, true);

$taxBreakdownLite = $notesLite['tax_breakdown'] ?? [];
$liteGstAmount = (float) ($invoice->gst_amount ?? 0);
$liteFinalPrice = (float) $baseAmountLite + $liteGstAmount;

$litePaymentReceived = (float) ($invoice->payment_received ?? 0);
$liteOutstandingBalance = (float) ($invoice->outstanding_balance ?? $liteFinalPrice);

$litePdfFormatPrice = function ($amount) use ($selectedCurrencyLite, $exchangeRateLite) {
    if (! is_numeric($amount)) {
        return '0.00';
    }
    $amt = (float) $amount;
    if ($selectedCurrencyLite === 'SGD') {
        return number_format(round($amt, 2), 2);
    }

    return number_format(round($amt, 2), 2).' SGD ('.number_format(round($amt * $exchangeRateLite, 2), 2).' '.$selectedCurrencyLite.')';
};
