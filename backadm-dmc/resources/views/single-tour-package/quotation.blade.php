<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Quotation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        @include('invoices.pdf.partials.header-css')

        .page {
            padding: 10px;
        }

        .top-lines {
            width: 100%;
            margin-bottom: 8px;
        }

        .top-line {
            margin: 2px 0;
        }

        .bold {
            font-weight: bold;
        }

        .quotation-main-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            table-layout: fixed;
        }

        .quotation-main-table > tbody > tr > td {
            border: 1px solid #000;
            vertical-align: top;
            padding: 6px 6px;
        }

        .quotation-col {
            width: 50%;
        }

        /* Titles sit in their own row; remove extra gap under grey bar */
        .quotation-main-table .panel-title {
            margin-bottom: 0;
        }

        /* No horizontal rule between inclusions and pricing (same visual column as body above) */
        .quotation-main-table > tbody > tr.quotation-band-body > td {
            border-bottom: none;
        }

        /* Pricing row: both cells share one <tr> so blocks are always on the same horizontal line */
        .quotation-main-table > tbody > tr.quotation-band-pricing > td {
            border-top: none;
            padding-top: 10px;
            padding-bottom: 5px;
            vertical-align: top;
        }

        .panel-title {
            border: 1px solid #000;
            padding: 6px 6px;
            font-weight: bold;
            text-align: center;
            background: #f3f3f3;
            margin-bottom: 6px;
            text-transform: uppercase;
            font-size: 12px;
        }

        .section-label {
            font-weight: bold;
            margin-bottom: 6px;
        }

        .inclusion {
            margin: 2px 0;
            line-height: 1.25;
        }

        .inclusion-list {
            margin: 0;
            padding-left: 18px; /* space for bullet */
        }

        .inclusion-list li {
            margin: 2px 0;
            line-height: 1.25;
        }

        .country-box {
            border: 2px solid #000;
            margin: 0 0 10px 0;
            page-break-inside: avoid;
        }

        .country-box-title {
            border-bottom: 1px solid #000;
            padding: 7px 8px;
            font-weight: bold;
            text-align: center;
            background: #eef2ff;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.04em;
        }

        .country-box-inner {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .country-box-inner td {
            width: 50%;
            vertical-align: top;
            padding: 8px;
            border: none;
        }

        .country-box-inner td + td {
            border-left: 1px solid #000;
        }

        .country-col-label {
            font-weight: bold;
            margin: 0 0 6px 0;
            text-transform: uppercase;
            font-size: 11px;
        }

        .overall-price-box {
            border: 2px solid #000;
            margin: 12px 0 0 0;
        }

        .money-line {
            margin: 6px 0 4px 0;
        }

        .table-like {
            border-collapse: collapse;
            width: 100%;
        }

        .table-like td {
            padding: 2px 0;
            vertical-align: top;
        }

        .subtle {
            color: #111;
        }

        .api-footnote {
            margin-top: 10px;
            font-size: 9.5px;
            color: #5c3317;
        }

        .quotation-information {
            border: 1px solid #000;
            padding: 6px 6px;
            margin-top: 10px;
            line-height: 1.25;
        }

        .quotation-information p {
            margin: 0 0 6px 0;
        }

    </style>
</head>
<body>
    @php
        $adults = (int)($bookingDetails['no_of_adults'] ?? 0);
        $children = (int)($bookingDetails['no_of_children'] ?? 0);
        $infants = (int)($bookingDetails['no_of_infants'] ?? 0);

        $leadGuestName = $bookingDetails['lead_guest_name'] ?? '';

        // Pax text:
        // In GROUP, `adult` already includes FOC, so show paying adults = adult - foc_size
        $tourTypeForPax = strtoupper((string)($tour->tour_type ?? 'FIT'));
        $focForPax = max(0, (int)($tour->foc_size ?? 0));
        $displayAdults = ($tourTypeForPax === 'GROUP' && $focForPax > 0) ? max(0, $adults - $focForPax) : $adults;

        $paxParts = [];
        $paxParts[] = str_pad((string) $displayAdults, 2, '0', STR_PAD_LEFT) . 'A';
        if ($children > 0) {
            $paxParts[] = str_pad((string) $children, 2, '0', STR_PAD_LEFT) . 'C';
        }
        if ($infants > 0) {
            $paxParts[] = str_pad((string) $infants, 2, '0', STR_PAD_LEFT) . 'I';
        }
        $paxText = implode(' ', $paxParts);

        $travelFrom = null;
        $travelTo = null;
        try {
            $travelFrom = (!empty($tour->check_in_time)) ? \Carbon\Carbon::parse($tour->check_in_time) : null;
            $travelTo = (!empty($tour->check_out_time)) ? \Carbon\Carbon::parse($tour->check_out_time) : null;
        } catch (\Throwable $e) {
            $travelFrom = null;
            $travelTo = null;
        }

        // Screenshot-like formatting (e.g., "11th march")
        $travellingDate = $travelFrom ? strtolower($travelFrom->format('jS F')) : 'N/A';
        $inclusionDateRange = ($travelFrom && $travelTo)
            ? $travelFrom->format('d M Y') . ' to ' . $travelTo->format('d M Y')
            : 'N/A';

        // Pro form: hotel single column uses double rate (both columns show the same hotel price).
        $isProTour = (int)($tour->is_pro ?? 0) === 1;

        // Very basic rooming heuristic: if >= 2 adults, show DBL
        $occupancyKey = $adults >= 2 ? 'double' : 'single';
        $roomingText = $adults >= 2 ? '01 DBL TWIN' : '01 SGL';

        $baseCurrency = strtoupper($baseCurrency ?? ($tour->currency ?? 'SGD'));
        $selectedCurrency = strtoupper($selectedCurrency ?? $baseCurrency);
        $exchangeRate = isset($exchangeRate) && is_numeric($exchangeRate) && (float)$exchangeRate > 0 ? (float)$exchangeRate : 1.0;

        // Dompdf sometimes cannot render the rupee glyph (₹) with the default font,
        // which results in a "?" character. Use "INR" text instead.
        $currencyLabel = $selectedCurrency === 'INR' ? 'INR' : $selectedCurrency;

        $formatAmount = function ($amount) use ($exchangeRate) {
            if (!is_numeric($amount)) return '0';
            $converted = ((float)$amount) * $exchangeRate;
            $abs = abs($converted);
            if ($abs == 0.0) return '0';
            // Normal amounts keep the existing whole-number look.
            if ($abs >= 1) return (string)(int)ceil($converted);
            // Small conversions (e.g. tiny base amount -> high-value currency) keep visible precision.
            if ($abs >= 0.01) return number_format($converted, 2);
            if ($abs >= 0.0001) return number_format($converted, 4);
            return number_format($converted, 6);
        };

        $formatMoney = function ($amount) use ($currencyLabel, $formatAmount) {
            $num = $formatAmount($amount);
            return $currencyLabel === 'INR' ? ($currencyLabel . ' ' . $num) : ($currencyLabel . ' ' . $num);
        };

        // Use new flat keys from the updated helper/controller
        $supplements = $tourPrices['supplyments'] ?? ($tourPrices['supplements'] ?? []);

        $otherSingleTotal = (float)($tourPrices['other_services_single'] ?? 0);
        $otherDoubleTotal = (float)($tourPrices['other_services_double'] ?? 0);

        // FOC / discount block (same convention as CommonHelper):
        // adult = total adults (includes FOC), foc_size = number of FOC adults
        $tourType   = strtoupper((string)($tour->tour_type ?? 'FIT'));
        $adultTotal = max(0, (int)($tour->adult ?? 0));
        $focSize    = max(0, (int)($tour->foc_size ?? 0));
        $payingPax  = max(0, $adultTotal - $focSize);
        $totalPax   = $adultTotal;
        $hasFoc     = $focSize > 0;
        // "discount = 1" means the tour has a discount/FOC-discount flag enabled
        $hasDiscount = !empty($tour->discount) && (int)$tour->discount === 1;
        $showFocBlock = $hasFoc && $hasDiscount;
        // FOC discount value = total price benefit given to FOC pax (per occupancy)
        // = per-pax price (after FOC distribution) × foc_size
        $focDiscountSingle = $showFocBlock
            ? ceil((float)($tourPrices['single_sharing'] ?? 0) * $focSize)
            : 0;
        $focDiscountDouble = $showFocBlock
            ? ceil((float)($tourPrices['double_sharing'] ?? 0) * $focSize)
            : 0;

        // GROUP + discount flag: show stored tours.discount_amount on PDF
        $showGroupDiscountAmount = ($tourType === 'GROUP' && $hasDiscount);
        $groupDiscountAmount = (float)($tour->discount_amount ?? 0);

        $otherTotalForOccupancy = $occupancyKey === 'double' ? $otherDoubleTotal : $otherSingleTotal;

        // Hotel-only totals per-head (supplements excluded)
        // overall total = hotel + other services (for all occupancies, including triple)
        $hotelOnlySingleTotal = max(0, (float)($tourPrices['single_sharing'] ?? 0) - $otherSingleTotal);
        $hotelOnlyDoubleTotal = max(0, (float)($tourPrices['double_sharing'] ?? 0) - $otherDoubleTotal);
        if ($isProTour) {
            $hotelOnlySingleTotal = $hotelOnlyDoubleTotal;
        }
        // Triple_sharing now also includes other-services per-pax (same as single/double),
        // so subtract it here to keep the Hotel cost box hotel-only.
        $tripleSharingTotal   = (float)($tourPrices['triple_sharing'] ?? 0);
        $hotelOnlyTripleTotal = $tripleSharingTotal > 0
            ? max(0, $tripleSharingTotal - $otherSingleTotal)
            : 0;

        // Triple occupancy is available when extra-bed pricing exists in tour totals.
        $tripleOccupancyAvailable = $hotelOnlyTripleTotal > 0;

        // Show only the hotel price cell that matches passenger count (others stay blank).
        $formatOccupancyHotelCells = function ($single, $double, $triple, $tripleAvailable, callable $moneyFormatter) use ($adults) {
            $blank = '';
            $singleCell = $blank;
            $doubleCell = $blank;
            $tripleCell = $blank;

            if ($adults <= 1 && (float) $single > 0) {
                $singleCell = $moneyFormatter($single);
            } elseif ($adults === 2 && (float) $double > 0) {
                $doubleCell = $moneyFormatter($double);
            } elseif ($adults >= 3 && $tripleAvailable && (float) $triple > 0) {
                $tripleCell = $moneyFormatter($triple);
            }

            return [$singleCell, $doubleCell, $tripleCell];
        };

        // Sum sell totals from each active order (same rules as negotiation gross).
        $extractQuotationOrderAmount = function ($order) use ($isProTour) {
            $data = is_string($order->data ?? null) ? json_decode($order->data, true) : ($order->data ?? null);
            if (! is_array($data)) {
                return 0.0;
            }

            $items = isset($data[0]) ? $data : [$data];
            $orderType = (string) ($order->type ?? '');
            $total = 0.0;

            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $itemPrice = (float) ($item['totalPrice'] ?? $item['price'] ?? 0);
                $transferPrice = 0.0;
                if ($orderType !== 'hotel' && isset($item['transfer_options']['cost']) && $item['transfer_options']['cost'] > 0) {
                    if ($isProTour && isset($item['transfer_options']['totalPrice'])) {
                        $transferPrice = (float) $item['transfer_options']['totalPrice'];
                    } else {
                        $transferPrice = (float) $item['transfer_options']['cost'];
                    }
                }

                $guidePrice = 0.0;
                if (isset($item['guide_options']) && is_array($item['guide_options'])) {
                    $gv = $item['guide_options']['total_price']
                        ?? $item['guide_options']['cost']
                        ?? $item['guide_options']['Cost']
                        ?? $item['guide_options']['sell']
                        ?? $item['guide_options']['Sell']
                        ?? 0;
                    if ($gv > 0) {
                        $guidePrice = (float) $gv;
                    }
                }

                $total += $itemPrice + $transferPrice + $guidePrice;
            }

            return $total;
        };

        $resolveQuotationOrderLabel = function ($order) {
            $typeLabel = \Illuminate\Support\Str::headline(str_replace('_', ' ', (string) ($order->type ?? 'Order')));
            $bookingId = $order->booking_id ?? '';
            $data = is_string($order->data ?? null) ? json_decode($order->data, true) : ($order->data ?? null);
            $item = is_array($data) ? (isset($data[0]) && is_array($data[0]) ? $data[0] : $data) : [];
            $name = trim((string) (
                $item['hotelDetails']['hotel_name']
                ?? $item['hotelname']
                ?? $item['AttractionName']
                ?? $item['attractionName']
                ?? $item['restaurantName']
                ?? $item['restaurant_name']
                ?? ''
            ));

            $label = $typeLabel;
            if ($name !== '') {
                $label .= ' — ' . $name;
            }
            if ($bookingId !== '') {
                $label .= ' (#' . $bookingId . ')';
            }

            return $label;
        };

        $quotationOrderRows = [];
        $overallQuotationTotal = 0.0;
        $overallQuotationConvertedOk = true;

        foreach (($orders ?? collect()) as $order) {
            if ((int) ($order->status ?? 0) !== 1) {
                continue;
            }

            $amount = $extractQuotationOrderAmount($order);
            if ($amount <= 0) {
                continue;
            }

            $orderCurrency = strtoupper(trim((string) ($order->currency ?? $baseCurrency)));
            if ($orderCurrency === '') {
                $orderCurrency = strtoupper((string) $baseCurrency);
            }

            $convertedAmount = \App\Helpers\CurrencyHelper::convertAmount($amount, $orderCurrency, $selectedCurrency);
            if ($convertedAmount === null) {
                if ($orderCurrency === $selectedCurrency) {
                    $convertedAmount = $amount;
                } else {
                    $overallQuotationConvertedOk = false;
                    $convertedAmount = $amount;
                }
            }

            $quotationOrderRows[] = [
                'label' => $resolveQuotationOrderLabel($order),
                'amount' => $amount,
                'currency' => $orderCurrency,
                'converted_amount' => (float) $convertedAmount,
            ];

            if ($overallQuotationConvertedOk) {
                $overallQuotationTotal += (float) $convertedAmount;
            }
        }

        if (! $overallQuotationConvertedOk) {
            $overallQuotationTotal = array_sum(array_column($quotationOrderRows, 'amount'));
        } else {
            $overallQuotationTotal = ceil($overallQuotationTotal);
        }

        // Build booked inclusions list from servicesByType (derived from orders for this tour)
        // We intentionally only show the categories requested by the user.
        $bookedAttractionCards = []; // full cards (transfer / guide details for PDF)
        $bookedRestaurantCards = [];
        $bookedArrivals = []; // [['text' => ..., 'country' => ...], ...]
        $bookedDepartures = [];
        $bookedLocalTransfers = [];

        $cardCountry = function ($card) {
            $country = trim((string)($card['country'] ?? ''));
            return $country !== '' ? $country : 'Other';
        };

        $cardCurrency = function ($card) use ($baseCurrency) {
            $currency = strtoupper(trim((string)($card['currency'] ?? '')));
            return $currency !== '' ? $currency : strtoupper((string)$baseCurrency);
        };

        $countryBucketKey = function ($country, $currency) {
            $country = trim((string)$country);
            if ($country === '') $country = 'Other';
            $currency = strtoupper(trim((string)$currency));
            if ($currency === '') $currency = 'SGD';
            return mb_strtolower($country) . '|' . $currency;
        };

        if (!empty($servicesByType) && is_array($servicesByType)) {
            foreach ($servicesByType as $type => $cards) {
                if (!is_array($cards) || empty($cards)) continue;

                $normalizedType = str_replace(' ', '_', strtolower($type));

                // Attraction name(s)
                if ($normalizedType === 'attraction' || $normalizedType === 'attraction_package') {
                    foreach ($cards as $card) {
                        if (!is_array($card)) continue;
                        $title = $card['title'] ?? ($card['attraction']['title'] ?? null);
                        if (!empty($title)) $bookedAttractionCards[] = $card;
                    }
                }

                // Restaurant cards (transfer details on quotation; no pricing)
                if ($normalizedType === 'restaurant') {
                    foreach ($cards as $card) {
                        if (!is_array($card)) continue;
                        $name = $card['title'] ?? ($card['restaurant']['name'] ?? null);
                        if (!empty($name)) {
                            $bookedRestaurantCards[] = $card;
                        }
                    }
                }

                // Arrival / Entry port transfer
                if ($normalizedType === 'entry_port') {
                    foreach ($cards as $card) {
                        if (!is_array($card)) continue;

                        $pickup = '';
                        $entryTime = '';
                        $entryDate = '';
                        foreach ($card['chips'] ?? [] as $chip) {
                            if (!is_array($chip)) continue;
                            $label = strtolower((string)($chip['label'] ?? ''));
                            $value = (string)($chip['value'] ?? '');
                            if ($label === 'pickup') $pickup = $value;
                            if ($label === 'time') $entryTime = $value;
                            if ($label === 'date') $entryDate = $value;
                        }

                        $vehicleData = $card['vehicle'] ?? [];
                        $transferTypeRaw = $vehicleData['transfer_type'] ?? $vehicleData['type'] ?? '';
                        $transferType = $transferTypeRaw;
                        if (!empty($transferTypeRaw) && $transferTypeRaw !== 'N/A' && strpos($transferTypeRaw, '_') !== false) {
                            $transferType = ucwords(str_replace('_', ' ', $transferTypeRaw));
                        }

                        $portName = !empty($pickup) ? $pickup : '';
                        if (!empty($portName)) {
                            $text = $portName;
                            if (!empty($entryTime)) $text .= ' (' . $entryTime . ')';
                            if (!empty($transferType)) $text .= ' - ' . $transferType;
                            $bookedArrivals[] = [
                                'text' => $text,
                                'country' => $cardCountry($card),
                                'currency' => $cardCurrency($card),
                            ];
                        }
                    }
                }

                // Departure / Exit port transfer
                if ($normalizedType === 'exit_port') {
                    foreach ($cards as $card) {
                        if (!is_array($card)) continue;

                        $dropoff = '';
                        $exitTime = '';
                        foreach ($card['chips'] ?? [] as $chip) {
                            if (!is_array($chip)) continue;
                            $label = strtolower((string)($chip['label'] ?? ''));
                            $value = (string)($chip['value'] ?? '');
                            if ($label === 'dropoff') $dropoff = $value;
                            if ($label === 'time') $exitTime = $value;
                        }

                        $vehicleData = $card['vehicle'] ?? [];
                        $transferTypeRaw = $vehicleData['transfer_type'] ?? $vehicleData['type'] ?? '';
                        $transferType = $transferTypeRaw;
                        if (!empty($transferTypeRaw) && $transferTypeRaw !== 'N/A' && strpos($transferTypeRaw, '_') !== false) {
                            $transferType = ucwords(str_replace('_', ' ', $transferTypeRaw));
                        }

                        $portName = !empty($dropoff) ? $dropoff : '';
                        if (!empty($portName)) {
                            $text = $portName;
                            if (!empty($exitTime)) $text .= ' (' . $exitTime . ')';
                            if (!empty($transferType)) $text .= ' - ' . $transferType;
                            $bookedDepartures[] = [
                                'text' => $text,
                                'country' => $cardCountry($card),
                                'currency' => $cardCurrency($card),
                            ];
                        }
                    }
                }

                // Local transport / transfer
                if ($normalizedType === 'local_transport' || $normalizedType === 'local_transfer') {
                    foreach ($cards as $card) {
                        if (!is_array($card)) continue;
                        $vehicleData = $card['vehicle'] ?? [];

                        $transferTypeRaw = $vehicleData['transfer_type'] ?? $vehicleData['type'] ?? '';
                        $transferType = $transferTypeRaw;
                        if (!empty($transferTypeRaw) && $transferTypeRaw !== 'N/A' && strpos($transferTypeRaw, '_') !== false) {
                            $transferType = ucwords(str_replace('_', ' ', $transferTypeRaw));
                        }

                        $vehicleTypeSeater = $vehicleData['vehicle_type_seater'] ?? '';
                        $vehicleTypeSeater = !empty($vehicleTypeSeater) ? (string)$vehicleTypeSeater : '';

                        if (!empty($transferType)) {
                            $text = $transferType;
                            if (!empty($vehicleTypeSeater)) $text .= ' - ' . $vehicleTypeSeater;
                            $bookedLocalTransfers[] = [
                                'text' => $text,
                                'country' => $cardCountry($card),
                                'currency' => $cardCurrency($card),
                            ];
                        }
                    }
                }
            }
        }

        // Exclude supplement occurrences from attraction/restaurant inclusions.
        // `formatServiceCard()` used by `servicesByType` does not preserve the raw `supplement` flag,
        // so we subtract by counts using the supplements payload returned by calculateTourPrices().
        $suppAttractionCounts = [];
        $suppRestaurantCounts = [];
        $suppHotelCounts = [];
        if (!empty($supplements) && is_array($supplements)) {
            foreach ($supplements as $s) {
                $suppType = strtolower((string)($s['type'] ?? ''));
                $suppName = (string)($s['name'] ?? '');

                // Hotel supplements are shown in the supplements table but NOT suppressed
                // from the main hotel inclusions panel — intentionally no suppHotelCounts logic.

                if ($suppType === 'attraction' && $suppName !== '') {
                    $suppAttractionCounts[$suppName] = ($suppAttractionCounts[$suppName] ?? 0) + 1;
                }

                if ($suppType === 'restaurant' && $suppName !== '') {
                    $mealType = $s['mealType'] ?? null;
                    $key = $suppName;
                    if (!empty($mealType)) {
                        $key .= ' - ' . $mealType;
                    }
                    $suppRestaurantCounts[$key] = ($suppRestaurantCounts[$key] ?? 0) + 1;
                }
            }
        }

        if (!empty($suppAttractionCounts)) {
            $filtered = [];
            foreach ($bookedAttractionCards as $c) {
                $a = $c['title'] ?? '';
                if ($a !== '' && isset($suppAttractionCounts[$a]) && $suppAttractionCounts[$a] > 0) {
                    $suppAttractionCounts[$a]--;
                    continue;
                }
                $filtered[] = $c;
            }
            $bookedAttractionCards = $filtered;
        }

        if (!empty($suppRestaurantCounts)) {
            $filtered = [];
            foreach ($bookedRestaurantCards as $rc) {
                $restPart = $rc['restaurant'] ?? [];
                $nm = $rc['title'] ?? '';
                $mealPlan = $restPart['meal_plan'] ?? null;
                $key = $nm;
                if (!empty($mealPlan)) {
                    $key .= ' - ' . $mealPlan;
                }
                if ($key !== '' && isset($suppRestaurantCounts[$key]) && $suppRestaurantCounts[$key] > 0) {
                    $suppRestaurantCounts[$key]--;
                    continue;
                }
                $filtered[] = $rc;
            }
            $bookedRestaurantCards = $filtered;
        }

        // Prefer tour destination order when segregating countries on the PDF.
        $preferredCountryOrder = [];
        $destinationRaw = (string)($tour->destination ?? '');
        if ($destinationRaw !== '') {
            foreach (preg_split('/\s*,\s*/', $destinationRaw) as $part) {
                $part = trim((string) preg_replace('/\s*\([^)]*\)\s*/', '', $part));
                $part = trim((string) preg_replace('/\[[^\]]*\]/', '', $part));
                if ($part !== '') {
                    $preferredCountryOrder[mb_strtolower($part)] = $part;
                }
            }
        }

        $sortCountryKeys = function (array $keys, array $meta) use ($preferredCountryOrder) {
            usort($keys, function ($a, $b) use ($preferredCountryOrder, $meta) {
                $aCountry = $meta[$a]['country'] ?? $a;
                $bCountry = $meta[$b]['country'] ?? $b;
                $aKey = mb_strtolower($aCountry);
                $bKey = mb_strtolower($bCountry);
                $aPos = array_key_exists($aKey, $preferredCountryOrder) ? array_search($aKey, array_keys($preferredCountryOrder), true) : PHP_INT_MAX;
                $bPos = array_key_exists($bKey, $preferredCountryOrder) ? array_search($bKey, array_keys($preferredCountryOrder), true) : PHP_INT_MAX;
                if ($aCountry === 'Other') $aPos = PHP_INT_MAX - 1;
                if ($bCountry === 'Other') $bPos = PHP_INT_MAX - 1;
                if ($aPos === $bPos) {
                    return strcasecmp(
                        $aCountry . ' ' . ($meta[$a]['currency'] ?? ''),
                        $bCountry . ' ' . ($meta[$b]['currency'] ?? '')
                    );
                }
                return $aPos <=> $bPos;
            });
            return $keys;
        };

        // Hotels grouped by country + currency (from orders)
        $hotelsByCountry = [];
        $countryMeta = [];
        $seenHotelKeys = [];
        if (!empty($hotelOptions) && is_array($hotelOptions)) {
            foreach ($hotelOptions as $h) {
                if (!is_array($h)) continue;
                $hotelName = $h['hotel_name'] ?? 'Hotel';
                $hotelNameLower = strtolower(trim((string)$hotelName));
                $roomCategoryName = $h['room_categories'][0]['name'] ?? ($h['hotel_category'] ?? 'Room');
                $roomCatLower = strtolower(trim((string)$roomCategoryName));
                $dedupKey = $hotelNameLower . '||' . $roomCatLower;
                if (isset($seenHotelKeys[$dedupKey])) continue;
                $seenHotelKeys[$dedupKey] = true;

                $country = trim((string)($h['country'] ?? ''));
                if ($country === '') $country = 'Other';
                $currency = strtoupper(trim((string)($h['currency'] ?? $baseCurrency)));
                if ($currency === '') $currency = strtoupper((string)$baseCurrency);
                $bucketKey = $countryBucketKey($country, $currency);
                $countryMeta[$bucketKey] = ['country' => $country, 'currency' => $currency];
                $hotelsByCountry[$bucketKey][] = [
                    'hotel_name' => $hotelName,
                    'room_category' => $roomCategoryName,
                ];
            }
        }

        // Other services grouped by country + currency
        $otherByCountry = [];
        $pushOther = function ($country, $currency, $kind, $value) use (&$otherByCountry, &$countryMeta, $countryBucketKey) {
            $bucketKey = $countryBucketKey($country, $currency);
            $countryMeta[$bucketKey] = [
                'country' => trim((string)$country) !== '' ? trim((string)$country) : 'Other',
                'currency' => strtoupper(trim((string)$currency)),
            ];
            $otherByCountry[$bucketKey][$kind][] = $value;
        };

        foreach ($bookedAttractionCards as $card) {
            $pushOther($cardCountry($card), $cardCurrency($card), 'attractions', $card);
        }
        foreach ($bookedRestaurantCards as $card) {
            $pushOther($cardCountry($card), $cardCurrency($card), 'restaurants', $card);
        }
        foreach ($bookedArrivals as $row) {
            $pushOther($row['country'] ?? 'Other', $row['currency'] ?? $baseCurrency, 'arrivals', $row['text']);
        }
        foreach ($bookedDepartures as $row) {
            $pushOther($row['country'] ?? 'Other', $row['currency'] ?? $baseCurrency, 'departures', $row['text']);
        }
        foreach ($bookedLocalTransfers as $row) {
            $pushOther($row['country'] ?? 'Other', $row['currency'] ?? $baseCurrency, 'local_transfers', $row['text']);
        }

        // One box per country+currency (hotels + other services together).
        $allCountryKeys = $sortCountryKeys(
            array_values(array_unique(array_merge(array_keys($hotelsByCountry), array_keys($otherByCountry)))),
            $countryMeta
        );

        $formatNativeMoney = function ($amount, $currency) {
            $currency = strtoupper(trim((string)$currency));
            if ($currency === '') $currency = 'SGD';
            $label = $currency === 'INR' ? 'INR' : $currency;
            $num = is_numeric($amount) ? (float)$amount : 0.0;
            if (abs($num) >= 1) {
                $formatted = number_format($num, 0, '.', ',');
            } else {
                $formatted = number_format($num, 2, '.', ',');
            }
            return $label . ' ' . $formatted;
        };
    @endphp

    <div class="page">
        @php
            // Format tour display id as:
            // - company_code/user_code/ORD1234 (both found)
            // - company_code/ORD1234 (only company code found)
            // - user_code/ORD1234 (only user code found)
            // Always remove "DMC-" prefix from tour->display_id before composing.
            $tourRawDisplayId = (string)($tour->display_id ?? $tour->tour_id ?? '');
            $ordPart = trim((string) preg_replace('/^DMC-/', '', $tourRawDisplayId));
            if ($ordPart === '') {
                $ordPart = trim($tourRawDisplayId);
            }

            $tourDmcUser = null;
            if (!empty($tour->dmc_id)) {
                $tourDmcUser = \App\Models\User::where('userId', $tour->dmc_id)->first();
            }
            $tourDmcCompanyCode = $tourDmcUser?->company_code ?? null;
            $tourDmcCompanyCode = is_string($tourDmcCompanyCode) ? trim($tourDmcCompanyCode) : '';
            $tourDmcCompanyCode = $tourDmcCompanyCode !== '' ? $tourDmcCompanyCode : null;

            // thirdparty_enabled=yes → show all country services + prices
            // thirdparty_enabled=no  → DMC country full; other countries date-only (no services/prices)
            $thirdPartyEnabled = strtolower((string) ($tourDmcUser?->thirdparty_enabled ?? 'no')) === 'yes';
            $dmcOperatingCountry = $tourDmcUser
                ? \App\Helpers\CommonHelper::resolveUserOperatingCountry($tourDmcUser)
                : null;
            $dmcCountryNorm = $dmcOperatingCountry !== null
                ? mb_strtolower(trim((string) $dmcOperatingCountry))
                : '';
            $isPricedCountry = function ($countryName) use ($thirdPartyEnabled, $dmcCountryNorm) {
                if ($thirdPartyEnabled || $dmcCountryNorm === '') {
                    return true;
                }
                return mb_strtolower(trim((string) $countryName)) === $dmcCountryNorm;
            };

            $createByUser = null;
            if (!empty($tour->created_by)) {
                $createByUser = \App\Models\User::where('userId', $tour->created_by)->first();
            }
            $createByUserCode = $createByUser?->user_code ?? null;
            $createByUserCode = is_string($createByUserCode) ? trim($createByUserCode) : '';
            $createByUserCode = $createByUserCode !== '' ? $createByUserCode : null;

            $formattedDisplayId = $ordPart !== '' ? $ordPart : '—';
            if ($tourDmcCompanyCode && $createByUserCode) {
                $formattedDisplayId = $tourDmcCompanyCode . '/' . $createByUserCode . '/' . $ordPart;
            } elseif ($tourDmcCompanyCode) {
                $formattedDisplayId = $tourDmcCompanyCode . '/' . $ordPart;
            } elseif ($createByUserCode) {
                $formattedDisplayId = $createByUserCode . '/' . $ordPart;
            }
        @endphp

        @include('invoices.pdf.partials.header', [
            'logoType' => $logoType ?? 'dmc',
            'showBlueTitle' => true,
            'docTitle' => 'QUOTATION',
            'docNumber' => $formattedDisplayId,
            'user_dmc' => $tourDmcUser,
            'user_agency' => $user_agency ?? null,
        ])

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
            <tr>
                <td style="width: 100%; vertical-align: top; padding-left: 2px;">
                    <div class="top-lines">
                        <div class="top-line"><span class="bold">Reference No:</span> {{ $formattedDisplayId }}</div>
                        <div class="top-line"><span class="bold">LEAD GUEST NAME:</span> {{ $leadGuestName }}</div>
                        <div class="top-line"><span class="bold">No. of Pax:</span> {{ $paxText }}</div>
                        @if($hasFoc)
                            <div class="top-line"><span class="bold">FOC Pax:</span> {{ $focSize }}</div>
                            <div class="top-line"><span class="bold">Total Pax:</span> {{ $totalPax }}</div>
                        @endif
                        <div class="top-line"><span class="bold">Travelling Date:</span> {{ $travellingDate }}</div>
                        <div class="top-line"><span class="bold">Rooming:</span> {{ $roomingText }}</div>
                        @if($showGroupDiscountAmount)
                            <div class="top-line"><span class="bold">Discount amount:</span> {{ $formatMoney($groupDiscountAmount) }}</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>


        
        @if(!empty($allCountryKeys))
            @foreach($allCountryKeys as $bucketKey)
                @php
                    $countryName = $countryMeta[$bucketKey]['country'] ?? $bucketKey;
                    $countryCurrency = $countryMeta[$bucketKey]['currency'] ?? strtoupper((string)$baseCurrency);
                    $showCountryPricing = $isPricedCountry($countryName);
                    $countryHotels = $showCountryPricing ? ($hotelsByCountry[$bucketKey] ?? []) : [];
                    $bucket = $showCountryPricing ? ($otherByCountry[$bucketKey] ?? []) : [];
                    $countryAttractions = $bucket['attractions'] ?? [];
                    $countryRestaurants = $bucket['restaurants'] ?? [];
                    $countryArrivals = $bucket['arrivals'] ?? [];
                    $countryDepartures = $bucket['departures'] ?? [];
                    $countryLocalTransfers = $bucket['local_transfers'] ?? [];
                    $hasOther = !empty($countryAttractions) || !empty($countryRestaurants) || !empty($countryArrivals) || !empty($countryDepartures) || !empty($countryLocalTransfers);
                @endphp
                <div class="country-box">
                    <div class="country-box-title">{{ $countryName }} ({{ $countryCurrency }})</div>
                    @if(!$showCountryPricing)
                        <div style="padding: 8px;">
                            <div class="inclusion"><span class="bold">Date:</span> {{ $inclusionDateRange }}</div>
                        </div>
                    @else
                    <table class="country-box-inner">
                        <tr>
                            <td>
                                <div class="money-line">
                                    <div class="inclusion"><span class="bold">Date:</span> {{ $inclusionDateRange }}</div>
                                </div>
                                <div class="country-col-label">Hotels</div>
                                @if(!empty($countryHotels))
                                    <ul class="inclusion-list">
                                        @foreach($countryHotels as $h)
                                            <li class="inclusion">
                                                {{ strtoupper($h['hotel_name']) }}-{{ strtoupper($h['room_category']) }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="inclusion">No hotels booked</div>
                                @endif
                            </td>
                            <td>
                                <div class="money-line">
                                    <div class="inclusion"><span class="bold">Date:</span> {{ $inclusionDateRange }}</div>
                                </div>
                                <div class="country-col-label">Other Services</div>
                                @if($hasOther)
                                    <ul class="inclusion-list">
                                        @foreach($countryAttractions as $attrCard)
                                            @php
                                                $attrTitle = $attrCard['title'] ?? '';
                                                $ad = $attrCard['attraction'] ?? null;
                                                $tr = is_array($ad) ? ($ad['transfer'] ?? null) : null;
                                                $gd = is_array($ad) ? ($ad['guide'] ?? null) : null;
                                            @endphp
                                            <li class="inclusion">
                                                <span class="bold">Attraction:</span> {{ $attrTitle }}
                                                @if(is_array($ad))
                                                    @if(is_array($tr) && (!empty($tr['vehicle_name']) || !empty($tr['type']) || !empty($tr['pickup_location_name']) || (isset($tr['cost']) && is_numeric($tr['cost']) && (float)$tr['cost'] > 0)))
                                                        @php
                                                            $vehBits = array_filter([
                                                                $tr['vehicle_name'] ?? null,
                                                                isset($tr['vehicle_type'], $tr['seating_capacity']) && $tr['vehicle_type'] && $tr['seating_capacity']
                                                                    ? $tr['vehicle_type'] . ' / ' . $tr['seating_capacity'] . ' seats'
                                                                    : ($tr['vehicle_type'] ?? null),
                                                            ]);
                                                            $vehLine = implode(' — ', $vehBits);
                                                            $transferMeta = array_filter([
                                                                $tr['type'] ?? null,
                                                                $tr['way'] ?? null,
                                                            ]);
                                                        @endphp
                                                        <div class="inclusion" style="margin: 2px 0 0 14px; line-height: 1.25;">
                                                            <span class="bold">Transfer / vehicle:</span>
                                                            @if(!empty($transferMeta))
                                                                {{ implode(' · ', $transferMeta) }}
                                                                @if($vehLine !== '') — @endif
                                                            @endif
                                                            {{ $vehLine }}
                                                            @if(!empty($tr['pickup_location_name']) || !empty($tr['pickup_time']))
                                                                <br>
                                                                @if(!empty($tr['pickup_location_name']))
                                                                    <span class="bold">Pickup:</span> {{ $tr['pickup_location_name'] }}
                                                                @endif
                                                                @if(!empty($tr['pickup_time']))
                                                                    @if(!empty($tr['pickup_location_name'])) — @endif
                                                                    <span class="bold">Time:</span> {{ $tr['pickup_time'] }}
                                                                @endif
                                                            @endif
                                                        </div>
                                                    @endif
                                                    @if(is_array($gd) && (
                                                        !empty($gd['guide_name']) ||
                                                        !empty($gd['language']) ||
                                                        !empty($gd['pickup_time']) ||
                                                        !empty($gd['package_hours']) ||
                                                        (isset($gd['hours']) && $gd['hours'] !== '' && $gd['hours'] !== null) ||
                                                        (isset($gd['base_price']) && is_numeric($gd['base_price']) && (float)$gd['base_price'] > 0) ||
                                                        (isset($gd['surcharge']) && is_numeric($gd['surcharge']) && (float)$gd['surcharge'] > 0) ||
                                                        (isset($gd['total_price']) && is_numeric($gd['total_price']) && (float)$gd['total_price'] > 0)
                                                    ))
                                                        @php
                                                            $guideHours = $gd['package_hours'] ?? $gd['hours'] ?? null;
                                                        @endphp
                                                        <div class="inclusion" style="margin: 2px 0 0 14px; line-height: 1.25;">
                                                            <span class="bold">Guide:</span>
                                                            @if(!empty($gd['guide_name']))
                                                                {{ $gd['guide_name'] }}
                                                            @endif
                                                            @if(!empty($gd['language']))
                                                                @if(!empty($gd['guide_name'])) · @endif
                                                                {{ $gd['language'] }}
                                                            @endif
                                                            @if(!empty($gd['pickup_time']))
                                                                <br><span class="bold">Pickup time:</span> {{ $gd['pickup_time'] }}
                                                            @endif
                                                            @if($guideHours !== null && $guideHours !== '')
                                                                <br><span class="bold">Duration:</span> {{ $guideHours }} hrs
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endif
                                            </li>
                                        @endforeach

                                        @foreach($countryRestaurants as $restCard)
                                            @php
                                                $restTitle = $restCard['title'] ?? '';
                                                $rs = $restCard['restaurant'] ?? null;
                                                $mealPlan = is_array($rs) ? ($rs['meal_plan'] ?? null) : null;
                                                $tr = is_array($rs) ? ($rs['transfer'] ?? null) : null;
                                            @endphp
                                            <li class="inclusion">
                                                <span class="bold">Restaurant:</span> {{ $restTitle }}@if(!empty($mealPlan)) — {{ $mealPlan }}@endif
                                                @if(is_array($rs))
                                                    @if(is_array($tr) && (!empty($tr['vehicle_name']) || !empty($tr['type']) || !empty($tr['pickup_location_name']) || !empty($tr['pickup_time'])))
                                                        @php
                                                            $vehBits = array_filter([
                                                                $tr['vehicle_name'] ?? null,
                                                                isset($tr['vehicle_type'], $tr['seating_capacity']) && $tr['vehicle_type'] && $tr['seating_capacity']
                                                                    ? $tr['vehicle_type'] . ' / ' . $tr['seating_capacity'] . ' seats'
                                                                    : ($tr['vehicle_type'] ?? null),
                                                            ]);
                                                            $vehLine = implode(' — ', $vehBits);
                                                            $transferMeta = array_filter([
                                                                $tr['type'] ?? null,
                                                                $tr['way'] ?? null,
                                                            ]);
                                                        @endphp
                                                        <div class="inclusion" style="margin: 2px 0 0 14px; line-height: 1.25;">
                                                            <span class="bold">Transfer / vehicle:</span>
                                                            @if(!empty($transferMeta))
                                                                {{ implode(' · ', $transferMeta) }}
                                                                @if($vehLine !== '') — @endif
                                                            @endif
                                                            {{ $vehLine }}
                                                            @if(!empty($tr['pickup_location_name']) || !empty($tr['pickup_time']))
                                                                <br>
                                                                @if(!empty($tr['pickup_location_name']))
                                                                    <span class="bold">Pickup:</span> {{ $tr['pickup_location_name'] }}
                                                                @endif
                                                                @if(!empty($tr['pickup_time']))
                                                                    @if(!empty($tr['pickup_location_name'])) — @endif
                                                                    <span class="bold">Time:</span> {{ $tr['pickup_time'] }}
                                                                @endif
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endif
                                            </li>
                                        @endforeach

                                        @foreach($countryArrivals as $ar)
                                            <li class="inclusion"><span class="bold">Arrival:</span> {{ $ar }}</li>
                                        @endforeach
                                        @foreach($countryDepartures as $dp)
                                            <li class="inclusion"><span class="bold">Departure:</span> {{ $dp }}</li>
                                        @endforeach
                                        @foreach($countryLocalTransfers as $lt)
                                            <li class="inclusion"><span class="bold">Local Transfer:</span> {{ $lt }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="inclusion">No other services booked</div>
                                @endif
                            </td>
                        </tr>
                    </table>
                    @endif
                </div>
            @endforeach
        @else
            <div class="country-box">
                <div class="country-box-title">Services</div>
                <div style="padding: 8px;">No hotel or other services booked</div>
            </div>
        @endif

        {{-- Country-wise Single / Double / Triple (same CommonHelper calculation, native currency) --}}
        @php
            $countrySharingRows = is_array($tourPrices['country_sharing'] ?? null)
                ? $tourPrices['country_sharing']
                : [];
            // Hide non-DMC country price blocks when thirdparty_enabled is off
            if (!$thirdPartyEnabled && $dmcCountryNorm !== '') {
                $countrySharingRows = array_values(array_filter($countrySharingRows, function ($share) use ($isPricedCountry) {
                    return $isPricedCountry($share['country'] ?? '');
                }));
            }
        @endphp
        @if(!empty($countrySharingRows))
        <div class="overall-price-box">
            <div class="panel-title" style="margin: 0; border: none; border-bottom: 1px solid #000;">Package Price by Country</div>
                @foreach($countrySharingRows as $share)
                    @php
                        $shareCountry = $share['country'] ?? 'Other';
                        $shareCurrency = strtoupper((string)($share['currency'] ?? $baseCurrency));
                        $shareHotelSingle = (float)($share['hotel_single'] ?? 0);
                        $shareHotelDouble = (float)($share['hotel_double'] ?? 0);
                        $shareHotelTriple = (float)($share['hotel_triple'] ?? 0);
                        $shareOther = (float)($share['other_services_single'] ?? ($share['other_services_double'] ?? 0));
                        if ($isProTour) {
                            $shareHotelSingle = $shareHotelDouble > 0 ? $shareHotelDouble : $shareHotelSingle;
                        }
                        $shareTripleAvailable = $shareHotelTriple > 0;
                        [$shareCellSingle, $shareCellDouble, $shareCellTriple] = $formatOccupancyHotelCells(
                            $shareHotelSingle,
                            $shareHotelDouble,
                            $shareHotelTriple,
                            $shareTripleAvailable,
                            fn ($amount) => $formatNativeMoney($amount, $shareCurrency)
                        );
                    @endphp
                    <div style="border-top: 1px solid #000;">
                        <div class="country-box-title" style="border-bottom: 1px solid #000;">{{ $shareCountry }} ({{ $shareCurrency }})</div>
                        <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                            <tr>
                                <td style="width: 50%; vertical-align: top; padding: 8px; border-right: 1px solid #000;">
                                    <div class="country-col-label">Hotel cost</div>
                                    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; table-layout: fixed;">
                                        <thead>
                                            <tr>
                                                <th style="border: 1px solid #000; padding: 6px; background: #f3f3f3; text-align: center; width: 33.33%;">Single</th>
                                                <th style="border: 1px solid #000; padding: 6px; background: #f3f3f3; text-align: center; width: 33.33%;">Double</th>
                                                <th style="border: 1px solid #000; padding: 6px; background: #f3f3f3; text-align: center; width: 33.33%;">Triple</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;">{{ $shareCellSingle }}</td>
                                                <td style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;">{{ $shareCellDouble }}</td>
                                                <td style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;">{{ $shareCellTriple }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td style="width: 50%; vertical-align: top; padding: 8px;">
                                    <div class="country-col-label">Other services cost</div>
                                    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; table-layout: fixed;">
                                        <thead>
                                            <tr>
                                                <th style="border: 1px solid #000; padding: 6px; background: #f3f3f3; text-align: center;">Price (per pax)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;">{{ $formatNativeMoney($shareOther, $shareCurrency) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>
                @endforeach
        </div>
        @endif

        {{-- Overall package price in selected/display currency (country amounts converted & summed) --}}
        @php
            $overallHotelSingle = 0.0;
            $overallHotelDouble = 0.0;
            $overallHotelTriple = 0.0;
            $overallOther = 0.0;
            $overallConvertedOk = false;

            if (!empty($countrySharingRows)) {
                $overallConvertedOk = true;
                foreach ($countrySharingRows as $share) {
                    $fromCurrency = strtoupper((string)($share['currency'] ?? $baseCurrency));
                    $hSingle = (float)($share['hotel_single'] ?? 0);
                    $hDouble = (float)($share['hotel_double'] ?? 0);
                    $hTriple = (float)($share['hotel_triple'] ?? 0);
                    $oOther = (float)($share['other_services_single'] ?? ($share['other_services_double'] ?? 0));
                    if ($isProTour) {
                        $hSingle = $hDouble > 0 ? $hDouble : $hSingle;
                    }

                    $cSingle = \App\Helpers\CurrencyHelper::convertAmount($hSingle, $fromCurrency, $selectedCurrency);
                    $cDouble = \App\Helpers\CurrencyHelper::convertAmount($hDouble, $fromCurrency, $selectedCurrency);
                    $cTriple = \App\Helpers\CurrencyHelper::convertAmount($hTriple, $fromCurrency, $selectedCurrency);
                    $cOther  = \App\Helpers\CurrencyHelper::convertAmount($oOther, $fromCurrency, $selectedCurrency);

                    if ($cSingle === null || $cDouble === null || $cOther === null) {
                        $overallConvertedOk = false;
                        break;
                    }

                    $overallHotelSingle += (float)$cSingle;
                    $overallHotelDouble += (float)$cDouble;
                    $overallHotelTriple += ($cTriple !== null) ? (float)$cTriple : 0.0;
                    $overallOther += (float)$cOther;
                }
            }

            $overallDisplayCurrency = $selectedCurrency;
            $overallDisplayLabel = $currencyLabel;

            if (!$overallConvertedOk) {
                // Fallback: sum filtered country rows in one currency when possible
                // (avoids mixing in non-DMC totals when thirdparty_enabled is off)
                if (!empty($countrySharingRows)) {
                    $fallbackCurrency = null;
                    $sameCurrency = true;
                    $fbHotelSingle = 0.0;
                    $fbHotelDouble = 0.0;
                    $fbHotelTriple = 0.0;
                    $fbOther = 0.0;
                    foreach ($countrySharingRows as $share) {
                        $fromCurrency = strtoupper((string)($share['currency'] ?? $baseCurrency));
                        if ($fallbackCurrency === null) {
                            $fallbackCurrency = $fromCurrency;
                        } elseif ($fallbackCurrency !== $fromCurrency) {
                            $sameCurrency = false;
                            break;
                        }
                        $hSingle = (float)($share['hotel_single'] ?? 0);
                        $hDouble = (float)($share['hotel_double'] ?? 0);
                        $hTriple = (float)($share['hotel_triple'] ?? 0);
                        $oOther = (float)($share['other_services_single'] ?? ($share['other_services_double'] ?? 0));
                        if ($isProTour) {
                            $hSingle = $hDouble > 0 ? $hDouble : $hSingle;
                        }
                        $fbHotelSingle += $hSingle;
                        $fbHotelDouble += $hDouble;
                        $fbHotelTriple += $hTriple;
                        $fbOther += $oOther;
                    }
                    if ($sameCurrency && $fallbackCurrency) {
                        $overallConvertedOk = true;
                        $overallDisplayCurrency = $fallbackCurrency;
                        $overallDisplayLabel = $fallbackCurrency === 'INR' ? 'INR' : $fallbackCurrency;
                        $overallHotelSingle = ceil($fbHotelSingle);
                        $overallHotelDouble = ceil($fbHotelDouble);
                        $overallHotelTriple = $fbHotelTriple > 0 ? ceil($fbHotelTriple) : 0;
                        $overallOther = ceil($fbOther);
                    } else {
                        $overallHotelSingle = null;
                        $overallHotelDouble = null;
                        $overallHotelTriple = null;
                        $overallOther = null;
                    }
                } else {
                    $overallHotelSingle = null;
                    $overallHotelDouble = null;
                    $overallHotelTriple = null;
                    $overallOther = null;
                }
            } else {
                $overallHotelSingle = ceil($overallHotelSingle);
                $overallHotelDouble = ceil($overallHotelDouble);
                $overallHotelTriple = $overallHotelTriple > 0 ? ceil($overallHotelTriple) : 0;
                $overallOther = ceil($overallOther);
            }

            $overallTripleAvailable = $overallConvertedOk
                ? ($overallHotelTriple > 0)
                : ($hotelOnlyTripleTotal > 0);
            $overallHotelSingleDisplay = $overallConvertedOk ? $overallHotelSingle : $hotelOnlySingleTotal;
            $overallHotelDoubleDisplay = $overallConvertedOk ? $overallHotelDouble : $hotelOnlyDoubleTotal;
            $overallHotelTripleDisplay = $overallConvertedOk ? $overallHotelTriple : $hotelOnlyTripleTotal;
            $overallHotelMoneyFormatter = $overallConvertedOk
                ? fn ($amount) => $formatNativeMoney($amount, $overallDisplayCurrency)
                : fn ($amount) => $formatMoney($amount);
            [$overallCellSingle, $overallCellDouble, $overallCellTriple] = $formatOccupancyHotelCells(
                $overallHotelSingleDisplay,
                $overallHotelDoubleDisplay,
                $overallHotelTripleDisplay,
                $overallTripleAvailable,
                $overallHotelMoneyFormatter
            );
        @endphp
        <div class="overall-price-box" style="margin-top: 10px;">
            <div class="panel-title" style="margin: 0; border: none; border-bottom: 1px solid #000;">Overall Package Price ({{ $overallDisplayLabel }})</div>
            <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                <tr>
                    <td style="width: 50%; vertical-align: top; padding: 8px; border-right: 1px solid #000;">
                        <div class="country-col-label">Hotel cost for entire package</div>
                        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; table-layout: fixed;">
                            <thead>
                                <tr>
                                    <th style="border: 1px solid #000; padding: 6px; background: #f3f3f3; text-align: center; width: 33.33%;">Single</th>
                                    <th style="border: 1px solid #000; padding: 6px; background: #f3f3f3; text-align: center; width: 33.33%;">Double</th>
                                    <th style="border: 1px solid #000; padding: 6px; background: #f3f3f3; text-align: center; width: 33.33%;">Triple</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;">{{ $overallCellSingle }}</td>
                                    <td style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;">{{ $overallCellDouble }}</td>
                                    <td style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;">{{ $overallCellTriple }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td style="width: 50%; vertical-align: top; padding: 8px;">
                        <div class="country-col-label">Other services cost for entire package</div>
                        <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; table-layout: fixed;">
                            <thead>
                                <tr>
                                    <th style="border: 1px solid #000; padding: 6px; background: #f3f3f3; text-align: center;">Price (per pax)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;">
                                        @if($overallConvertedOk)
                                            {{ $formatNativeMoney($overallOther, $overallDisplayCurrency) }}
                                        @else
                                            {{ $formatMoney($otherTotalForOccupancy) }}
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        @if(!empty($quotationOrderRows))
            <div class="overall-price-box" style="margin-top: 10px;">
                <div class="panel-title" style="margin: 0; border: none; border-bottom: 1px solid #000;">Overall Quotation Price ({{ $currencyLabel }})</div>
                <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                    <thead>
                        <tr>
                            <th style="border: 1px solid #000; padding: 6px; background: #f3f3f3; text-align: left; width: 70%;">Order</th>
                            <th style="border: 1px solid #000; padding: 6px; background: #f3f3f3; text-align: center; width: 30%;">Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quotationOrderRows as $orderRow)
                            <tr>
                                <td style="border: 1px solid #000; padding: 6px; vertical-align: top;">{{ $orderRow['label'] }}</td>
                                <td style="border: 1px solid #000; padding: 6px; text-align: center; font-weight: bold;">
                                    @if($overallQuotationConvertedOk)
                                        {{ $formatMoney($orderRow['converted_amount']) }}
                                    @else
                                        {{ $formatNativeMoney($orderRow['amount'], $orderRow['currency']) }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td style="border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold;">Overall Quotation Price</td>
                            <td style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;">
                                @if($overallQuotationConvertedOk)
                                    {{ $formatMoney($overallQuotationTotal) }}
                                @else
                                    {{ $formatNativeMoney($overallQuotationTotal, $selectedCurrency) }}
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        

        @php
            // Helper: format raw "YYYY-MM-DD to YYYY-MM-DD" into "01 Jun 2026 to 03 Jun 2026"
            $formatDateRange = function ($raw) {
                if (empty($raw)) return '';
                $parts = array_map('trim', explode(' to ', (string)$raw));
                if (count($parts) === 2) {
                    try {
                        $from = \Carbon\Carbon::parse($parts[0])->format('d M Y');
                        $to   = \Carbon\Carbon::parse($parts[1])->format('d M Y');
                        return $from . ' to ' . $to;
                    } catch (\Throwable $e) {}
                }
                return $raw;
            };

            // Split supplements into hotel vs other-service buckets
            $suppHotels   = [];
            $suppServices = [];
            foreach ($supplements as $s) {
                $t = strtolower((string)($s['type'] ?? ''));
                if ($t === 'hotel') {
                    $suppHotels[] = $s;
                } else {
                    $suppServices[] = $s;
                }
            }
        @endphp

        {{-- ── Hotel supplements box ── --}}
        @if(!empty($suppHotels))
            <div style="margin-top: 10px;">
                <div class="panel-title">Supplements – Hotels</div>
                <table style="width: 100%; border-collapse: collapse; border: 2px solid #000; table-layout: fixed;">
                    <thead>
                        <tr>
                            <th style="border: 1px solid #000; padding: 6px; background: #f3f3f3; text-align: left; width: 52%;">Hotel</th>
                            <th style="border: 1px solid #000; padding: 6px; background: #f3f3f3; text-align: center; width: 16%;">Single</th>
                            <th style="border: 1px solid #000; padding: 6px; background: #f3f3f3; text-align: center; width: 16%;">Double</th>
                            <th style="border: 1px solid #000; padding: 6px; background: #f3f3f3; text-align: center; width: 16%;">Triple</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suppHotels as $s)
                            @php
                                $hotelLabel     = $s['hotel_name'] ?? ($s['display_name'] ?? ($s['name'] ?? 'Hotel'));
                                $rawDateRange   = $s['date_range'] ?? null;
                                $niceDate       = $rawDateRange ? $formatDateRange($rawDateRange) : '';
                                $suppSingle     = (float)($s['single'] ?? 0);
                                $suppDouble     = (float)($s['double'] ?? 0);
                                $suppTriple     = (float)($s['triple'] ?? 0);
                                if ($isProTour) {
                                    $suppSingle = $suppDouble > 0 ? $suppDouble : $suppSingle;
                                }
                                $suppTripleAvailable = $suppTriple > 0;
                                [$suppCellSingle, $suppCellDouble, $suppCellTriple] = $formatOccupancyHotelCells(
                                    $suppSingle,
                                    $suppDouble,
                                    $suppTriple,
                                    $suppTripleAvailable,
                                    fn ($amount) => $formatMoney($amount)
                                );
                            @endphp
                            <tr>
                                <td style="border: 1px solid #000; padding: 6px; vertical-align: top;">
                                    {{ $hotelLabel }}
                                    @if($niceDate)
                                        <span class="subtle"> ({{ $niceDate }})</span>
                                    @endif
                                </td>
                                <td style="border: 1px solid #000; padding: 6px; text-align: center;">{{ $suppCellSingle }}</td>
                                <td style="border: 1px solid #000; padding: 6px; text-align: center;">{{ $suppCellDouble }}</td>
                                <td style="border: 1px solid #000; padding: 6px; text-align: center;">{{ $suppCellTriple }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- ── Other-service supplements box ── --}}
        @if(!empty($suppServices))
            <div style="margin-top: 10px;">
                <div class="panel-title">Supplements – Other Services</div>
                <table style="width: 100%; border-collapse: collapse; border: 2px solid #000; table-layout: fixed;">
                    <thead>
                        <tr>
                            <th style="border: 1px solid #000; padding: 6px; background: #f3f3f3; text-align: left; width: 70%;">Service</th>
                            <th style="border: 1px solid #000; padding: 6px; background: #f3f3f3; text-align: center; width: 30%;">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suppServices as $s)
                            @php
                                $suppTypeRaw = trim((string)($s['type'] ?? ''));
                                $svcName = trim((string)($s['name'] ?? ($s['AttractionName'] ?? ($s['restaurantName'] ?? ''))));
                                $tSlug = strtolower(str_replace([' ', '-'], '_', $suppTypeRaw));
                                $nSlug = strtolower(str_replace([' ', '-'], '_', $svcName));
                                $typePretty = $tSlug !== '' ? \Illuminate\Support\Str::headline($tSlug) : '';
                                $namePretty = $nSlug !== '' ? \Illuminate\Support\Str::headline($nSlug) : '';
                                $typeNorm = strtolower(preg_replace('/[^a-z0-9]+/', '', $suppTypeRaw));
                                $nameNorm = strtolower(preg_replace('/[^a-z0-9]+/', '', $svcName));
                                if ($svcName !== '' && $typeNorm !== '' && $typeNorm === $nameNorm) {
                                    $svcLabel = $typePretty;
                                } elseif ($svcName !== '' && $typePretty !== '' && $typeNorm !== $nameNorm) {
                                    $svcLabel = $typePretty . ': ' . $namePretty;
                                } elseif ($svcName !== '') {
                                    $svcLabel = $namePretty;
                                } else {
                                    $svcLabel = $typePretty !== '' ? $typePretty : 'Supplement';
                                }
                                $suppPrice = $occupancyKey === 'double'
                                    ? (float)($s['double'] ?? 0)
                                    : (float)($s['single'] ?? 0);
                            @endphp
                            <tr>
                                <td style="border: 1px solid #000; padding: 6px; vertical-align: top;">{{ $svcLabel }}</td>
                                <td style="border: 1px solid #000; padding: 6px; text-align: center;">{{ $formatMoney($suppPrice) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        @if(!empty($quotationInformationHtml))
            <div class="quotation-information">
                <div class="section-label">Quotation Information</div>
                {!! $quotationInformationHtml !!}
            </div>
        @endif

        <div class="api-footnote">
            <strong>Note:</strong> Please note that currency conversion is based on market rate and is subject to change at the time of payment.
        </div>

    </div>
</body>
</html>
