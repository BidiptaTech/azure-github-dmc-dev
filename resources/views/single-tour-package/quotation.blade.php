<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packaged Quotation</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        @page {
            margin: 8mm 8mm 8mm 8mm;
        }
        @include('invoices.pdf.partials.header-css')

        /* Readable header / logo — not oversized */
        .header {
            margin-bottom: 6px;
        }
        .header-table td {
            padding: 3px !important;
            vertical-align: middle !important;
        }
        .header-left {
            width: 16% !important;
            padding: 0 6px 0 0 !important;
        }
        .header-center {
            width: 66% !important;
        }
        .header-right {
            width: 18% !important;
        }
        .dmc-logo-wrapper {
            height: 85px !important;
            min-height: 85px !important;
            width: 100%;
        }
        .dmc-logo-wrapper img {
            max-width: 100px !important;
            max-height: 85px !important;
            width: auto !important;
            height: auto !important;
            margin-top: 0 !important;
            object-fit: contain !important;
        }
        .header-center .dmc-name {
            font-size: 16px !important;
            margin: 0 0 3px 0 !important;
        }
        .header-center .dmc-address,
        .header-center .dmc-contact,
        .header-center .dmc-meta {
            font-size: 9px !important;
            line-height: 1.35 !important;
            margin-top: 2px !important;
        }
        .header-center .dmc-meta div {
            margin-top: 1px !important;
        }
        .header-right .doc-number {
            font-size: 10px !important;
        }
        .header-doc-title {
            font-size: 15px !important;
            margin: 2px 0 0 0 !important;
            letter-spacing: 0.04em;
        }

        .page { padding: 0; }

        .guest-meta {
            width: 100%;
            border-collapse: collapse;
            margin: 2px 0 8px 0;
        }
        .guest-meta td {
            vertical-align: top;
            padding: 0;
            border: none;
        }
        .top-lines { width: 100%; margin: 0; }
        .top-line {
            margin: 0 0 2px 0;
            line-height: 1.4;
            font-size: 11px;
        }
        .bold { font-weight: bold; }

        .panel-title {
            border: none;
            border-bottom: 1px solid #000;
            padding: 5px 7px;
            font-weight: bold;
            text-align: center;
            background: #f0f0f0;
            margin: 0;
            text-transform: uppercase;
            font-size: 11px;
        }
        .section-label {
            font-weight: bold;
            margin-bottom: 4px;
            font-size: 11px;
        }

        .inclusion {
            margin: 0 0 5px 0;
            line-height: 1.4;
            font-size: 11px;
        }
        .inclusion-list {
            margin: 0;
            padding-left: 16px;
        }
        .inclusion-list li {
            margin: 0 0 5px 0;
            line-height: 1.4;
            page-break-inside: auto;
        }
        .svc-inline { font-size: 10px; }
        .svc-sub {
            margin: 2px 0 2px 10px;
            font-size: 9.5px;
            line-height: 1.35;
        }

        .country-box {
            border: 1px solid #000;
            margin: 0 0 8px 0;
            page-break-inside: auto;
        }
        .country-box-title {
            border-bottom: 1px solid #000;
            padding: 5px 7px;
            font-weight: bold;
            text-align: center;
            background: #f0f0f0;
            text-transform: uppercase;
            font-size: 11px;
        }
        .country-box-body { padding: 5px 7px; }
        .country-date-row {
            padding: 4px 7px;
            border-bottom: 1px solid #ccc;
            font-size: 10.5px;
            line-height: 1.35;
        }

        .country-box-inner {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .country-box-inner td {
            width: 50%;
            vertical-align: top;
            padding: 5px 7px;
            border: none;
        }
        .country-box-inner td + td {
            border-left: 1px solid #000;
        }
        .country-col-label {
            font-weight: bold;
            margin: 0 0 4px 0;
            padding-bottom: 2px;
            border-bottom: 1px solid #ccc;
            text-transform: uppercase;
            font-size: 10.5px;
        }

        .overall-price-box {
            border: 1px solid #000;
            margin: 8px 0 0 0;
            page-break-inside: auto;
        }
        .price-split-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .price-split-table > tbody > tr > td {
            width: 50%;
            vertical-align: top;
            padding: 5px 7px;
            border: none;
        }
        .price-split-table > tbody > tr > td + td {
            border-left: 1px solid #000;
        }
        .price-grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid #000;
        }
        .price-grid th,
        .price-grid td {
            border: 1px solid #000;
            padding: 5px 4px;
            text-align: center;
            vertical-align: middle;
            font-size: 10.5px;
            line-height: 1.35;
        }
        .price-grid th {
            background: #f0f0f0;
            font-weight: bold;
        }
        .price-grid td { font-weight: bold; }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .totals-table th,
        .totals-table td {
            border: 1px solid #000;
            padding: 6px 7px;
            vertical-align: middle;
            font-size: 11px;
            line-height: 1.4;
        }
        .totals-table th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .totals-table .city-cell {
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            background: #f7f7f7;
            width: 20%;
        }
        .totals-table .svc-cell { text-align: left; width: 42%; }
        .totals-table .pax-cell { text-align: center; width: 12%; white-space: nowrap; }
        .totals-table .amt-cell { text-align: right; font-weight: bold; width: 26%; white-space: nowrap; }

        .api-footnote {
            margin-top: 8px;
            font-size: 9px;
            color: #444;
            line-height: 1.35;
        }
        .quotation-information {
            border: 1px solid #000;
            padding: 5px 7px;
            margin-top: 8px;
            line-height: 1.4;
            font-size: 11px;
        }
        .quotation-information p { margin: 0 0 4px 0; }
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

        // Use hotel_price_options (same selected_persons as Overall Package) for rooming text
        $hotelOptionsForRooming = is_array($tourPrices['hotel_price_options'] ?? null)
            ? $tourPrices['hotel_price_options']
            : (is_array($hotelOptions ?? null) ? $hotelOptions : null);
        $hotelDisplayOccupancy = \App\Helpers\CommonHelper::resolveQuotationHotelDisplayOccupancy(
            $orders ?? collect(),
            $hotelOptionsForRooming,
            $adults
        );
        $occupancyKey = $hotelDisplayOccupancy['occupancy_key'];
        $roomingText = $hotelDisplayOccupancy['rooming_text'];
        $displayOccupancyKey = $occupancyKey;

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

        // currency_markups.discount_value — shown under Rooming; subtracted from TOTAL COST only
        $markupDiscountRows = is_array($tourPrices['discounts'] ?? null) ? $tourPrices['discounts'] : [];
        $markupDiscountTotalNative = (float) ($tourPrices['discount_total'] ?? 0);
        $markupDiscountDisplayAmount = 0.0;
        $markupDiscountDisplayCurrency = $selectedCurrency;
        $markupDiscountConvertedOk = true;
        if (!empty($markupDiscountRows)) {
            foreach ($markupDiscountRows as $dRow) {
                $dAmount = (float) ($dRow['amount'] ?? 0);
                if ($dAmount <= 0) {
                    continue;
                }
                $dCurrency = strtoupper(trim((string) ($dRow['currency'] ?? $baseCurrency)));
                if ($dCurrency === '') {
                    $dCurrency = strtoupper((string) $baseCurrency);
                }
                $converted = \App\Helpers\CurrencyHelper::convertAmount($dAmount, $dCurrency, $selectedCurrency);
                if ($converted === null) {
                    if ($dCurrency === $selectedCurrency) {
                        $converted = $dAmount;
                    } else {
                        $markupDiscountConvertedOk = false;
                        $converted = $dAmount;
                        $markupDiscountDisplayCurrency = $dCurrency;
                    }
                }
                $markupDiscountDisplayAmount += (float) $converted;
            }
            $markupDiscountDisplayAmount = ceil($markupDiscountDisplayAmount);
        } elseif ($markupDiscountTotalNative > 0) {
            $markupDiscountDisplayAmount = ceil($markupDiscountTotalNative);
        }
        $showMarkupDiscount = $markupDiscountDisplayAmount > 0;

        // currency_markups hotel_markup + other_markup — added to TOTAL COST
        $markupAddRows = is_array($tourPrices['markups'] ?? null) ? $tourPrices['markups'] : [];
        $markupAddTotalNative = (float) ($tourPrices['markup_total'] ?? 0);
        $markupAddDisplayAmount = 0.0;
        $markupAddConvertedOk = true;
        if (!empty($markupAddRows)) {
            foreach ($markupAddRows as $mRow) {
                $mAmount = (float) ($mRow['amount'] ?? 0);
                if ($mAmount <= 0) {
                    continue;
                }
                $mCurrency = strtoupper(trim((string) ($mRow['currency'] ?? $baseCurrency)));
                if ($mCurrency === '') {
                    $mCurrency = strtoupper((string) $baseCurrency);
                }
                $converted = \App\Helpers\CurrencyHelper::convertAmount($mAmount, $mCurrency, $selectedCurrency);
                if ($converted === null) {
                    if ($mCurrency === $selectedCurrency) {
                        $converted = $mAmount;
                    } else {
                        $markupAddConvertedOk = false;
                        $converted = $mAmount;
                    }
                }
                $markupAddDisplayAmount += (float) $converted;
            }
            $markupAddDisplayAmount = ceil($markupAddDisplayAmount);
        } elseif ($markupAddTotalNative > 0) {
            $markupAddDisplayAmount = ceil($markupAddTotalNative);
        }

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

        // Show only the hotel price cell that matches booked room occupancy (others stay blank).
        $formatOccupancyHotelCells = function ($single, $double, $triple, $tripleAvailable, callable $moneyFormatter) use ($displayOccupancyKey) {
            $blank = '';
            $singleCell = $blank;
            $doubleCell = $blank;
            $tripleCell = $blank;

            if ($displayOccupancyKey === 'single' && (float) $single > 0) {
                $singleCell = $moneyFormatter($single);
            } elseif ($displayOccupancyKey === 'double' && (float) $double > 0) {
                $doubleCell = $moneyFormatter($double);
            } elseif ($displayOccupancyKey === 'triple' && $tripleAvailable && (float) $triple > 0) {
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

        // Other-services totals from actual order amounts (not per-pax sharing rates).
        $otherOrderTotalByBucketKey = [];
        $overallOtherServicesOrderTotal = 0.0;
        $overallOtherServicesOrdersConvertedOk = true;

        foreach ($countryQuotationGroups ?? [] as $group) {
            if (! is_array($group)) {
                continue;
            }

            $bucketKey = (string) ($group['key'] ?? '');
            $otherAmt = (float) ($group['other_total'] ?? 0);
            if ($bucketKey !== '') {
                $otherOrderTotalByBucketKey[$bucketKey] = $otherAmt;
            }

            $groupCurrency = strtoupper(trim((string) ($group['currency'] ?? $baseCurrency)));
            if ($groupCurrency === '') {
                $groupCurrency = strtoupper((string) $baseCurrency);
            }

            $convertedOther = \App\Helpers\CurrencyHelper::convertAmount($otherAmt, $groupCurrency, $selectedCurrency);
            if ($convertedOther === null) {
                if ($groupCurrency === $selectedCurrency) {
                    $overallOtherServicesOrderTotal += $otherAmt;
                } else {
                    $overallOtherServicesOrdersConvertedOk = false;
                }
            } else {
                $overallOtherServicesOrderTotal += (float) $convertedOther;
            }
        }

        if ($overallOtherServicesOrdersConvertedOk) {
            $overallOtherServicesOrderTotal = ceil($overallOtherServicesOrderTotal);
        } else {
            $overallOtherServicesOrderTotal = array_sum($otherOrderTotalByBucketKey);
        }
        $otherServicesDisplayPerPax = $otherTotalForOccupancy;

        // Build booked inclusions list from servicesByType (derived from orders for this tour)
        // We intentionally only show the categories requested by the user.
        $bookedAttractionCards = []; // full cards (transfer / guide details for PDF)
        $bookedRestaurantCards = [];
        $bookedArrivals = []; // [['text' => ..., 'country' => ...], ...]
        $bookedDepartures = [];
        $bookedLocalTransfers = [];
        $bookedGuides = [];
        $bookedPointToPoint = [];
        $bookedHourly = [];

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

        // city → country from master cities list
        $cityToCountryMap = [];
        foreach (($cities ?? []) as $cRow) {
            $cName = trim((string) (is_object($cRow) ? ($cRow->name ?? '') : ($cRow['name'] ?? '')));
            $cCountry = trim((string) (is_object($cRow) ? ($cRow->country ?? '') : ($cRow['country'] ?? '')));
            if ($cName !== '' && $cCountry !== '') {
                $cityToCountryMap[mb_strtolower($cName)] = $cCountry;
            }
        }

        // Preferred city + stay dates per country from tour.city
        // e.g. "Batam (Indonesia) [2026-09-23→2026-09-26], Singapore (Singapore) [2026-09-26→2026-09-29]"
        $preferredCityByCountry = [];
        $dateRangeByCountry = []; // country(lower) => 'd M Y to d M Y'
        $dateRangeByCity = [];    // city(lower) => 'd M Y to d M Y'
        $dateBoundsByCountry = []; // country(lower) => ['start' => Y-m-d, 'end' => Y-m-d]
        $dateBoundsByCity = [];
        $tourCityRaw = trim((string) ($tour->city ?? ''));
        if ($tourCityRaw !== '') {
            $planRe = '/^(.+?)\s*\[(\d{4}-\d{2}-\d{2})\s*(?:→|->)\s*(\d{4}-\d{2}-\d{2})\]\s*$/u';
            foreach (preg_split('/\s*,\s*/', $tourCityRaw) ?: [] as $part) {
                $part = trim((string) $part);
                if ($part === '') {
                    continue;
                }
                $startYmd = '';
                $endYmd = '';
                if (preg_match($planRe, $part, $dm)) {
                    $part = trim((string) $dm[1]);
                    $startYmd = trim((string) $dm[2]);
                    $endYmd = trim((string) $dm[3]);
                } else {
                    $part = trim((string) preg_replace('/\s*\[[^\]]*\]\s*/', '', $part));
                }
                $cityName = $part;
                $countryName = '';
                if (preg_match('/^(.+?)\s*\(([^)]+)\)\s*$/', $part, $m)) {
                    $cityName = trim($m[1]);
                    $countryName = trim($m[2]);
                } else {
                    $cityName = trim($part);
                    $countryName = $cityToCountryMap[mb_strtolower($cityName)] ?? '';
                }
                if ($cityName === '') {
                    continue;
                }
                if ($countryName === '') {
                    $countryName = $cityName; // city-state fallback
                }
                $preferredCityByCountry[mb_strtolower($countryName)] = $cityName;

                if ($startYmd !== '' && $endYmd !== '') {
                    $cKey = mb_strtolower($countryName);
                    $cityKey = mb_strtolower($cityName);
                    if (!isset($dateBoundsByCountry[$cKey])) {
                        $dateBoundsByCountry[$cKey] = ['start' => $startYmd, 'end' => $endYmd];
                    } else {
                        if ($startYmd < $dateBoundsByCountry[$cKey]['start']) {
                            $dateBoundsByCountry[$cKey]['start'] = $startYmd;
                        }
                        if ($endYmd > $dateBoundsByCountry[$cKey]['end']) {
                            $dateBoundsByCountry[$cKey]['end'] = $endYmd;
                        }
                    }
                    if (!isset($dateBoundsByCity[$cityKey])) {
                        $dateBoundsByCity[$cityKey] = ['start' => $startYmd, 'end' => $endYmd];
                    } else {
                        if ($startYmd < $dateBoundsByCity[$cityKey]['start']) {
                            $dateBoundsByCity[$cityKey]['start'] = $startYmd;
                        }
                        if ($endYmd > $dateBoundsByCity[$cityKey]['end']) {
                            $dateBoundsByCity[$cityKey]['end'] = $endYmd;
                        }
                    }
                }
            }
            $formatPlanRange = static function (string $startYmd, string $endYmd): string {
                try {
                    return \Carbon\Carbon::parse($startYmd)->format('d M Y')
                        . ' to '
                        . \Carbon\Carbon::parse($endYmd)->format('d M Y');
                } catch (\Throwable $e) {
                    return $startYmd . ' to ' . $endYmd;
                }
            };
            foreach ($dateBoundsByCountry as $k => $bounds) {
                $dateRangeByCountry[$k] = $formatPlanRange($bounds['start'], $bounds['end']);
            }
            foreach ($dateBoundsByCity as $k => $bounds) {
                $dateRangeByCity[$k] = $formatPlanRange($bounds['start'], $bounds['end']);
            }
        }

        $resolveCountryDateRange = function ($city, $country) use ($dateRangeByCity, $dateRangeByCountry, $inclusionDateRange) {
            $cityKey = mb_strtolower(trim((string) $city));
            $countryKey = mb_strtolower(trim((string) $country));
            if ($cityKey !== '' && !empty($dateRangeByCity[$cityKey])) {
                return $dateRangeByCity[$cityKey];
            }
            if ($countryKey !== '' && !empty($dateRangeByCountry[$countryKey])) {
                return $dateRangeByCountry[$countryKey];
            }
            return $inclusionDateRange;
        };

        // Title: City (Country) (CURRENCY) — e.g. Kolkata (India) (INR)
        $formatLocationTitle = function ($city, $country, $currency) use ($preferredCityByCountry) {
            $country = trim((string) $country);
            if ($country === '') {
                $country = 'Other';
            }
            $currency = strtoupper(trim((string) $currency));
            if ($currency === '') {
                $currency = 'SGD';
            }
            $city = trim((string) $city);
            if ($city === '') {
                $city = $preferredCityByCountry[mb_strtolower($country)] ?? $country;
            }
            if ($city === '') {
                $city = $country;
            }
            return $city . ' (' . $country . ') (' . $currency . ')';
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
                                'card' => $card,
                                'country' => $cardCountry($card),
                                'currency' => $cardCurrency($card),
                                'city' => trim((string) ($card['city'] ?? explode(',', (string) ($card['subtitle'] ?? $card['location'] ?? ''))[0] ?? '')),
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
                                'card' => $card,
                                'country' => $cardCountry($card),
                                'currency' => $cardCurrency($card),
                                'city' => trim((string) ($card['city'] ?? explode(',', (string) ($card['subtitle'] ?? $card['location'] ?? ''))[0] ?? '')),
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
                                'card' => $card,
                                'country' => $cardCountry($card),
                                'currency' => $cardCurrency($card),
                                'city' => trim((string) ($card['city'] ?? explode(',', (string) ($card['subtitle'] ?? $card['location'] ?? ''))[0] ?? '')),
                            ];
                        }
                    }
                }

                // Guide
                if ($normalizedType === 'guide') {
                    foreach ($cards as $card) {
                        if (!is_array($card)) continue;
                        $gd = is_array($card['guide'] ?? null) ? $card['guide'] : [];
                        $guideName = trim((string) ($gd['guide_name'] ?? $card['title'] ?? 'Guide'));
                        if ($guideName === '') {
                            $guideName = 'Guide';
                        }
                        $lang = trim((string) ($gd['language_proficiency'] ?? ''));
                        $hours = $gd['hours'] ?? null;
                        $entryTime = '';
                        foreach ($card['chips'] ?? [] as $chip) {
                            if (!is_array($chip)) continue;
                            if (strtolower((string) ($chip['label'] ?? '')) === 'time') {
                                $entryTime = (string) ($chip['value'] ?? '');
                            }
                        }
                        if ($entryTime === '' && !empty($gd['entry_time'])) {
                            $entryTime = (string) $gd['entry_time'];
                        }
                        $bits = array_filter([
                            $guideName,
                            ($lang !== '' && strtoupper($lang) !== 'N/A') ? $lang : null,
                            ($hours !== null && $hours !== '') ? ($hours . ' hr' . ((float) $hours != 1 ? 's' : '')) : null,
                            $entryTime !== '' ? $entryTime : null,
                        ]);
                        $bookedGuides[] = [
                            'text' => implode(' - ', $bits),
                            'card' => $card,
                            'country' => $cardCountry($card),
                            'currency' => $cardCurrency($card),
                            'city' => trim((string) ($card['city'] ?? explode(',', (string) ($card['subtitle'] ?? $card['location'] ?? ''))[0] ?? '')),
                        ];
                    }
                }

                // Point to point vehicle
                if ($normalizedType === 'travel_point' || $normalizedType === 'point_to_point') {
                    foreach ($cards as $card) {
                        if (!is_array($card)) continue;
                        $vehicleData = is_array($card['vehicle'] ?? null) ? $card['vehicle'] : [];
                        $vehicleName = trim((string) ($vehicleData['name'] ?? $card['title'] ?? ''));
                        $vehicleTypeSeater = trim((string) ($vehicleData['vehicle_type_seater'] ?? ''));
                        if ($vehicleTypeSeater === 'N/A') {
                            $vehicleTypeSeater = '';
                        }
                        $way = trim((string) ($vehicleData['way'] ?? ''));
                        $pickup = trim((string) ($vehicleData['pickup'] ?? ''));
                        $dropoff = trim((string) ($vehicleData['dropoff'] ?? ''));
                        foreach ($card['chips'] ?? [] as $chip) {
                            if (!is_array($chip)) continue;
                            $label = strtolower((string) ($chip['label'] ?? ''));
                            $value = trim((string) ($chip['value'] ?? ''));
                            if ($label === 'pickup' && $value !== '') $pickup = $value;
                            if ($label === 'dropoff' && $value !== '') $dropoff = $value;
                        }
                        $route = '';
                        if ($pickup !== '' && $dropoff !== '') {
                            $route = $pickup . ' → ' . $dropoff;
                        } elseif ($pickup !== '') {
                            $route = $pickup;
                        } elseif ($dropoff !== '') {
                            $route = $dropoff;
                        }
                        $bits = array_filter([
                            $vehicleName !== '' && strtoupper($vehicleName) !== 'N/A' ? $vehicleName : null,
                            $vehicleTypeSeater !== '' ? $vehicleTypeSeater : null,
                            $way !== '' ? $way : null,
                            $route !== '' ? $route : null,
                        ]);
                        $text = !empty($bits) ? implode(' - ', $bits) : 'Point to Point';
                        $bookedPointToPoint[] = [
                            'text' => $text,
                            'card' => $card,
                            'country' => $cardCountry($card),
                            'currency' => $cardCurrency($card),
                            'city' => trim((string) ($card['city'] ?? explode(',', (string) ($card['subtitle'] ?? $card['location'] ?? ''))[0] ?? '')),
                        ];
                    }
                }

                // Hourly vehicle
                if ($normalizedType === 'travel_hourly' || $normalizedType === 'hourly') {
                    foreach ($cards as $card) {
                        if (!is_array($card)) continue;
                        $vehicleData = is_array($card['vehicle'] ?? null) ? $card['vehicle'] : [];
                        $vehicleName = trim((string) ($vehicleData['name'] ?? $card['title'] ?? ''));
                        $vehicleTypeSeater = trim((string) ($vehicleData['vehicle_type_seater'] ?? ''));
                        if ($vehicleTypeSeater === 'N/A') {
                            $vehicleTypeSeater = '';
                        }
                        $mode = trim((string) ($vehicleData['mode'] ?? $vehicleData['travel_type'] ?? ''));
                        $hours = $vehicleData['hours'] ?? null;
                        foreach ($card['chips'] ?? [] as $chip) {
                            if (!is_array($chip)) continue;
                            $label = strtolower((string) ($chip['label'] ?? ''));
                            if (in_array($label, ['hours', 'hour', 'package'], true) && ($chip['value'] ?? '') !== '') {
                                $hours = $chip['value'];
                            }
                        }
                        $bits = array_filter([
                            $vehicleName !== '' && strtoupper($vehicleName) !== 'N/A' ? $vehicleName : null,
                            $vehicleTypeSeater !== '' ? $vehicleTypeSeater : null,
                            $mode !== '' ? $mode : null,
                            ($hours !== null && $hours !== '') ? ($hours . ' hr' . ((float) $hours != 1 ? 's' : '')) : null,
                        ]);
                        $text = !empty($bits) ? implode(' - ', $bits) : 'Hourly Transfer';
                        $bookedHourly[] = [
                            'text' => $text,
                            'card' => $card,
                            'country' => $cardCountry($card),
                            'currency' => $cardCurrency($card),
                            'city' => trim((string) ($card['city'] ?? explode(',', (string) ($card['subtitle'] ?? $card['location'] ?? ''))[0] ?? '')),
                        ];
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

        // Occupancy meta from hotel_price_options (no prices — pax / CWB / CNB only)
        $hotelOccByName = [];
        foreach (($tourPrices['hotel_price_options'] ?? []) as $hpOcc) {
            if (!is_array($hpOcc)) {
                continue;
            }
            $n = mb_strtolower(trim((string) ($hpOcc['hotel_name'] ?? ($hpOcc['display_name'] ?? ''))));
            if ($n === '') {
                continue;
            }
            $hotelOccByName[$n] = [
                'selected_persons' => max(0, (int) ($hpOcc['selected_persons'] ?? 0)),
                'children' => max(0, (int) ($hpOcc['children'] ?? 0)),
                'has_cwb' => (float) ($hpOcc['child_with_bed_total'] ?? 0) > 0,
                'has_cnb' => (float) ($hpOcc['child_without_bed_total'] ?? 0) > 0,
                'date_range' => trim((string) ($hpOcc['date_range'] ?? $hpOcc['booking_range'] ?? '')),
            ];
        }

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
                $city = trim((string)($h['city'] ?? ''));
                if ($city === '') {
                    $city = $preferredCityByCountry[mb_strtolower($country)] ?? '';
                }
                $bucketKey = $countryBucketKey($country, $currency);
                if (!isset($countryMeta[$bucketKey])) {
                    $countryMeta[$bucketKey] = ['country' => $country, 'city' => $city, 'currency' => $currency];
                } elseif ($city !== '' && empty($countryMeta[$bucketKey]['city'])) {
                    $countryMeta[$bucketKey]['city'] = $city;
                }

                // Room occupancy from booked rooms (prefer selected_persons)
                $selectedPersons = 0;
                $hasExtraBed = false;
                $roomCount = 0;
                $roomsPayload = isset($h['rooms']) && is_array($h['rooms']) ? $h['rooms'] : [];
                foreach ($roomsPayload as $roomRow) {
                    if (!is_array($roomRow)) {
                        continue;
                    }
                    $noOfRooms = (int) ($roomRow['no_of_room'] ?? $roomRow['number_of_rooms'] ?? 0);
                    $roomCount += $noOfRooms > 0 ? $noOfRooms : 1;
                    $sp = (int) ($roomRow['selected_persons'] ?? $roomRow['selectedPersons'] ?? 0);
                    $selectedPersons = max($selectedPersons, $sp);
                    $beds = isset($roomRow['beds']) && is_array($roomRow['beds']) ? $roomRow['beds'] : [];
                    foreach ($beds as $bedRow) {
                        if (!is_array($bedRow)) {
                            continue;
                        }
                        if (!empty($bedRow['extra_bed']) || !empty($bedRow['extraBed']) || !empty($bedRow['extrabed'])) {
                            $hasExtraBed = true;
                        }
                        if ($sp <= 0) {
                            $selectedPersons = max(
                                $selectedPersons,
                                (int) ($bedRow['head_count'] ?? $bedRow['headCount'] ?? $bedRow['occupancy'] ?? 0)
                            );
                        }
                    }
                }
                if ($roomCount <= 0) {
                    // Fallback from formatHotelsForPdf no_of_rooms summary
                    $nor = $h['no_of_rooms'] ?? null;
                    if (is_array($nor)) {
                        $roomCount = (int) ($nor['single'] ?? 0) + (int) ($nor['double'] ?? 0) + (int) ($nor['triple'] ?? 0);
                    } elseif (is_numeric($nor)) {
                        $roomCount = (int) $nor;
                    }
                }
                if ($roomCount <= 0) {
                    $roomCount = 1;
                }

                $occMeta = $hotelOccByName[$hotelNameLower] ?? null;
                if ($occMeta) {
                    if ((int) ($occMeta['selected_persons'] ?? 0) > 0) {
                        $selectedPersons = (int) $occMeta['selected_persons'];
                    }
                }
                if ($selectedPersons <= 0) {
                    $selectedPersons = 2;
                }
                // Triple sharing usually means double + extra bed
                if ($selectedPersons >= 3) {
                    $hasExtraBed = true;
                }

                $children = (int) ($occMeta['children'] ?? 0);
                $hasCwb = !empty($occMeta['has_cwb']);
                $hasCnb = !empty($occMeta['has_cnb']);

                // Extra bed = 1 adult on extra bed; remaining adults share the main bed(s).
                // Example: 3 adult occupancy + 1 child no bed → "2 Adults · Extra Bed (1 Adult) · 1 Child with no Bed"
                $extraBedAdults = 0;
                $mainAdults = max(1, $selectedPersons);
                if ($hasExtraBed && $selectedPersons >= 3) {
                    $extraBedAdults = 1;
                    $mainAdults = max(1, $selectedPersons - $extraBedAdults);
                } elseif ($hasExtraBed && $selectedPersons === 2) {
                    // Double + flagged extra bed but only 2 persons: treat as 2 adults + extra bed available
                    $mainAdults = 2;
                    $extraBedAdults = 0;
                }

                $totalPaxShown = $mainAdults + $extraBedAdults + ($children > 0 ? $children : 0);

                $occupancyBits = [];
                if ($mainAdults > 0) {
                    $occupancyBits[] = $mainAdults . ' Adult' . ($mainAdults > 1 ? 's' : '');
                }
                if ($hasExtraBed) {
                    if ($extraBedAdults > 0) {
                        $occupancyBits[] = 'Extra Bed (' . $extraBedAdults . ' Adult' . ($extraBedAdults > 1 ? 's' : '') . ')';
                    } else {
                        $occupancyBits[] = 'Extra Bed';
                    }
                }
                if ($children > 0) {
                    if ($hasCwb) {
                        $occupancyBits[] = $children . ' Child with Bed';
                    } elseif ($hasCnb) {
                        $occupancyBits[] = $children . ' Child with no Bed';
                    } else {
                        // Child present but no CWB charge → staying with adults (no bed)
                        $occupancyBits[] = $children . ' Child with no Bed';
                    }
                } elseif ($hasCwb) {
                    $occupancyBits[] = 'Child with Bed';
                } elseif ($hasCnb) {
                    $occupancyBits[] = 'Child with no Bed';
                }

                $hotelsByCountry[$bucketKey][] = [
                    'hotel_name' => $hotelName,
                    'room_category' => $roomCategoryName,
                    'room_count' => $roomCount,
                    'selected_persons' => $selectedPersons,
                    'total_pax' => max($selectedPersons + ($children > 0 ? $children : 0), $totalPaxShown),
                    'adults' => $mainAdults + $extraBedAdults,
                    'children' => $children,
                    'has_cwb' => $hasCwb,
                    'has_cnb' => $hasCnb,
                    'has_extra_bed' => $hasExtraBed,
                    'occupancy_bits' => $occupancyBits,
                    'date_range' => trim((string) ($occMeta['date_range'] ?? ($h['date_range'] ?? ($h['check_in'] ?? '') . (($h['check_out'] ?? '') !== '' ? ' to ' . $h['check_out'] : '')))),
                ];
            }
        }

        // Other services grouped by country + currency
        $otherByCountry = [];
        $pushOther = function ($country, $currency, $kind, $value, $city = '') use (&$otherByCountry, &$countryMeta, $countryBucketKey, $preferredCityByCountry) {
            $bucketKey = $countryBucketKey($country, $currency);
            $countryName = trim((string) $country) !== '' ? trim((string) $country) : 'Other';
            $currencyCode = strtoupper(trim((string) $currency));
            $cityName = trim((string) $city);
            if ($cityName === '') {
                $cityName = $preferredCityByCountry[mb_strtolower($countryName)] ?? '';
            }
            if (!isset($countryMeta[$bucketKey])) {
                $countryMeta[$bucketKey] = [
                    'country' => $countryName,
                    'city' => $cityName,
                    'currency' => $currencyCode,
                ];
            } else {
                if ($cityName !== '' && empty($countryMeta[$bucketKey]['city'])) {
                    $countryMeta[$bucketKey]['city'] = $cityName;
                }
                if (empty($countryMeta[$bucketKey]['currency'])) {
                    $countryMeta[$bucketKey]['currency'] = $currencyCode;
                }
            }
            $otherByCountry[$bucketKey][$kind][] = $value;
        };

        $cardCity = function ($card) {
            $city = trim((string) ($card['city'] ?? ($card['attraction']['city'] ?? ($card['restaurant']['city'] ?? ''))));
            if ($city === '' && !empty($card['location'])) {
                $city = trim(explode(',', (string) $card['location'])[0]);
            }
            return $city;
        };

        foreach ($bookedAttractionCards as $card) {
            $pushOther($cardCountry($card), $cardCurrency($card), 'attractions', $card, $cardCity($card));
        }
        foreach ($bookedRestaurantCards as $card) {
            $pushOther($cardCountry($card), $cardCurrency($card), 'restaurants', $card, $cardCity($card));
        }
        foreach ($bookedArrivals as $row) {
            $pushOther($row['country'] ?? 'Other', $row['currency'] ?? $baseCurrency, 'arrivals', $row, $row['city'] ?? '');
        }
        foreach ($bookedDepartures as $row) {
            $pushOther($row['country'] ?? 'Other', $row['currency'] ?? $baseCurrency, 'departures', $row, $row['city'] ?? '');
        }
        foreach ($bookedLocalTransfers as $row) {
            $pushOther($row['country'] ?? 'Other', $row['currency'] ?? $baseCurrency, 'local_transfers', $row, $row['city'] ?? '');
        }
        foreach ($bookedGuides as $row) {
            $pushOther($row['country'] ?? 'Other', $row['currency'] ?? $baseCurrency, 'guides', $row, $row['city'] ?? '');
        }
        foreach ($bookedPointToPoint as $row) {
            $pushOther($row['country'] ?? 'Other', $row['currency'] ?? $baseCurrency, 'point_to_point', $row, $row['city'] ?? '');
        }
        foreach ($bookedHourly as $row) {
            $pushOther($row['country'] ?? 'Other', $row['currency'] ?? $baseCurrency, 'hourly', $row, $row['city'] ?? '');
        }

        // Sort key for Other Services: date then time (uses card date_sort / time_sort when present).
        $serviceDateTimeSortKey = function ($payload) {
            $card = null;
            if (is_array($payload)) {
                if (!empty($payload['card']) && is_array($payload['card'])) {
                    $card = $payload['card'];
                } elseif (
                    isset($payload['chips'])
                    || isset($payload['date_sort'])
                    || isset($payload['attraction'])
                    || isset($payload['restaurant'])
                    || isset($payload['vehicle'])
                ) {
                    $card = $payload;
                }
            }

            $dateSort = '9999-12-31';
            $timeSort = '00:00'; // no time → start of that date

            if (is_array($card)) {
                if (!empty($card['date_sort'])) {
                    $dateSort = (string) $card['date_sort'];
                } else {
                    foreach (($card['chips'] ?? []) as $chip) {
                        if (!is_array($chip)) {
                            continue;
                        }
                        if (strcasecmp((string) ($chip['label'] ?? ''), 'Date') !== 0) {
                            continue;
                        }
                        $raw = trim((string) ($chip['value'] ?? ''));
                        if ($raw === '') {
                            continue;
                        }
                        try {
                            $dateSort = \Carbon\Carbon::parse($raw)->format('Y-m-d');
                        } catch (\Throwable $e) {
                            $dateSort = $raw;
                        }
                        break;
                    }
                }

                if (!empty($card['time_sort'])) {
                    $timeSort = (string) $card['time_sort'];
                } else {
                    $timeRaw = trim((string) (
                        $card['time']
                        ?? ($card['attraction']['visit_time'] ?? null)
                        ?? ($card['restaurant']['visit_time'] ?? null)
                        ?? ($card['entry_port_flight']['destination_arrival_time'] ?? null)
                        ?? ($card['exit_port_flight']['origin_departure_time'] ?? null)
                        ?? ''
                    ));
                    if ($timeRaw === '') {
                        foreach (($card['chips'] ?? []) as $chip) {
                            if (!is_array($chip)) {
                                continue;
                            }
                            if (strcasecmp((string) ($chip['label'] ?? ''), 'Time') === 0) {
                                $timeRaw = trim((string) ($chip['value'] ?? ''));
                                break;
                            }
                        }
                    }
                    if ($timeRaw === '' && !empty($payload['text']) && preg_match('/\(([^)]*\d{1,2}:\d{2}[^)]*)\)/', (string) $payload['text'], $tm)) {
                        $timeRaw = trim($tm[1]);
                    }
                    if ($timeRaw !== '') {
                        try {
                            $timeSort = \Carbon\Carbon::parse($timeRaw)->format('H:i');
                        } catch (\Throwable $e) {
                            if (preg_match('/(\d{1,2}):(\d{2})/', $timeRaw, $m)) {
                                $timeSort = str_pad($m[1], 2, '0', STR_PAD_LEFT) . ':' . $m[2];
                            } else {
                                $timeSort = $timeRaw;
                            }
                        }
                    }
                }
            }

            return $dateSort . ' ' . $timeSort;
        };

        $buildSortedOtherServices = function (array $bucket) use ($serviceDateTimeSortKey) {
            $items = [];
            foreach (($bucket['attractions'] ?? []) as $card) {
                $items[] = [
                    'kind' => 'attraction',
                    'label' => '',
                    'card' => $card,
                    'plain' => '',
                    'sort' => $serviceDateTimeSortKey($card),
                ];
            }
            foreach (($bucket['restaurants'] ?? []) as $card) {
                $items[] = [
                    'kind' => 'restaurant',
                    'label' => '',
                    'card' => $card,
                    'plain' => '',
                    'sort' => $serviceDateTimeSortKey($card),
                ];
            }
            // Chronological order for all services (incl. mid-trip / return Arrival & Departure)
            $vehicleKinds = [
                'arrivals' => 'Arrival',
                'departures' => 'Departure',
                'local_transfers' => 'Local Transfer',
                'point_to_point' => 'Point to Point',
                'hourly' => 'Hourly Transfer',
            ];
            foreach ($vehicleKinds as $bucketKey => $label) {
                foreach (($bucket[$bucketKey] ?? []) as $row) {
                    $items[] = [
                        'kind' => 'vehicle',
                        'label' => $label,
                        'card' => (is_array($row) && !empty($row['card'])) ? $row['card'] : null,
                        'plain' => is_array($row) ? (string) ($row['text'] ?? '') : (string) $row,
                        'row' => $row,
                        'sort' => $serviceDateTimeSortKey($row),
                    ];
                }
            }
            foreach (($bucket['guides'] ?? []) as $row) {
                $items[] = [
                    'kind' => 'guide',
                    'label' => 'Guide',
                    'card' => null,
                    'plain' => is_array($row) ? (string) ($row['text'] ?? '') : (string) $row,
                    'row' => $row,
                    'sort' => $serviceDateTimeSortKey($row),
                ];
            }
            usort($items, function ($a, $b) {
                $cmp = strcmp((string) ($a['sort'] ?? ''), (string) ($b['sort'] ?? ''));
                if ($cmp !== 0) {
                    return $cmp;
                }
                // Same datetime: Arrival before other, Departure after other
                $rank = function ($item) {
                    $label = strtolower((string) ($item['label'] ?? ''));
                    if ($label === 'arrival') {
                        return 0;
                    }
                    if ($label === 'departure') {
                        return 2;
                    }
                    return 1;
                };
                $ra = $rank($a);
                $rb = $rank($b);
                if ($ra !== $rb) {
                    return $ra <=> $rb;
                }
                return strcmp((string) ($a['label'] ?? $a['kind'] ?? ''), (string) ($b['label'] ?? $b['kind'] ?? ''));
            });
            return $items;
        };

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

            // Restrict only when thirdparty=yes AND thirdparty_enabled=no
            // (thirdparty=no, or thirdparty=yes + enabled=yes → all countries/services)
            $isThirdPartyDmc = strtolower(trim((string) ($tourDmcUser?->thirdparty ?? 'no'))) === 'yes';
            $thirdPartyEnabled = strtolower(trim((string) ($tourDmcUser?->thirdparty_enabled ?? 'no'))) === 'yes';
            $isRestrictedThirdParty = $isThirdPartyDmc && !$thirdPartyEnabled;

            $allowedDmcCountries = [];
            if ($isRestrictedThirdParty && $tourDmcUser) {
                foreach (preg_split('/\s*,\s*/', (string) ($tourDmcUser->country ?? '')) ?: [] as $part) {
                    $name = trim((string) $part);
                    if ($name !== '' && !\App\Helpers\CommonHelper::looksLikeCurrencyCode($name)) {
                        $allowedDmcCountries[] = $name;
                    }
                }
                $dmcOperatingCountry = \App\Helpers\CommonHelper::resolveUserOperatingCountry($tourDmcUser);
                if ($dmcOperatingCountry !== null && trim((string) $dmcOperatingCountry) !== '') {
                    $allowedDmcCountries[] = trim((string) $dmcOperatingCountry);
                }
                $allowedDmcCountries = array_values(array_unique($allowedDmcCountries));
            }
            $isPricedCountry = function ($countryName) use ($isRestrictedThirdParty, $allowedDmcCountries) {
                if (!$isRestrictedThirdParty || empty($allowedDmcCountries)) {
                    return true;
                }
                $countryName = trim((string) $countryName);
                if ($countryName === '' || \App\Helpers\CommonHelper::looksLikeCurrencyCode($countryName)) {
                    return false;
                }
                foreach ($allowedDmcCountries as $allowed) {
                    if (\App\Helpers\CommonHelper::countriesMatch($countryName, $allowed)) {
                        return true;
                    }
                }
                return false;
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

        <table class="guest-meta">
            <tr>
                <td>
                    <div class="top-lines">
                        <div class="top-line"><span class="bold">Reference No:</span> {{ $formattedDisplayId }}</div>
                        <div class="top-line"><span class="bold">LEAD GUEST NAME:</span> {{ $leadGuestName }}</div>
                        <div class="top-line"><span class="bold">No. of Pax:</span> {{ $paxText }}</div>
                        @if($hasFoc)
                            <div class="top-line"><span class="bold">FOC Pax:</span> {{ $focSize }}</div>
                            <div class="top-line"><span class="bold">Total Pax:</span> {{ $totalPax }}</div>
                        @endif
                        <div class="top-line"><span class="bold">Travelling Date:</span> {{ $travellingDate }}</div>
                        @if(trim((string) $roomingText) !== '')
                            <div class="top-line"><span class="bold">Rooming:</span> {{ $roomingText }}</div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        {{-- Country Hotels + Other Services (inclusions) — keep above pricing --}}
        @if(!empty($allCountryKeys))
            @foreach($allCountryKeys as $bucketKey)
                @php
                    $countryName = $countryMeta[$bucketKey]['country'] ?? $bucketKey;
                    $countryCity = $countryMeta[$bucketKey]['city'] ?? '';
                    $countryCurrency = $countryMeta[$bucketKey]['currency'] ?? strtoupper((string)$baseCurrency);
                    $countryBoxTitle = $formatLocationTitle($countryCity, $countryName, $countryCurrency);
                    $countryDateRange = $resolveCountryDateRange($countryCity, $countryName);
                    $showCountryPricing = $isPricedCountry($countryName);
                    $countryHotels = $showCountryPricing ? ($hotelsByCountry[$bucketKey] ?? []) : [];
                    $bucket = $showCountryPricing ? ($otherByCountry[$bucketKey] ?? []) : [];
                    $countryAttractions = $bucket['attractions'] ?? [];
                    $countryRestaurants = $bucket['restaurants'] ?? [];
                    $countryArrivals = $bucket['arrivals'] ?? [];
                    $countryDepartures = $bucket['departures'] ?? [];
                    $countryLocalTransfers = $bucket['local_transfers'] ?? [];
                    $countryGuides = $bucket['guides'] ?? [];
                    $countryPointToPoint = $bucket['point_to_point'] ?? [];
                    $countryHourly = $bucket['hourly'] ?? [];
                    $hasOther = !empty($countryAttractions) || !empty($countryRestaurants) || !empty($countryArrivals) || !empty($countryDepartures) || !empty($countryLocalTransfers) || !empty($countryGuides) || !empty($countryPointToPoint) || !empty($countryHourly);
                    $sortedOtherServices = $hasOther ? $buildSortedOtherServices($bucket) : [];
                @endphp
                <div class="country-box">
                    <div class="country-box-title">{{ $countryBoxTitle }}</div>
                    @if(!$showCountryPricing)
                        <div class="country-date-row">
                            <span class="bold">Date:</span> {{ $countryDateRange }}
                        </div>
                    @else
                    <div class="country-date-row">
                        <span class="bold">Date:</span> {{ $countryDateRange }}
                    </div>
                    <table class="country-box-inner">
                        <tr>
                            <td>
                                <div class="country-col-label">Hotels</div>
                                @if(!empty($countryHotels))
                                    <ul class="inclusion-list">
                                        @foreach($countryHotels as $h)
                                            @php
                                                $hName = trim((string) ($h['hotel_name'] ?? 'Hotel'));
                                                $hRoom = trim((string) ($h['room_category'] ?? 'Room'));
                                                $hPax = max(1, (int) ($h['total_pax'] ?? $h['selected_persons'] ?? 1));
                                                $hRoomCount = max(1, (int) ($h['room_count'] ?? 1));
                                                $hRoomLabel = $hRoomCount . ' ' . $hRoom . ($hRoomCount > 1 ? ' Rooms' : ' Room');
                                                $hBits = is_array($h['occupancy_bits'] ?? null) ? $h['occupancy_bits'] : [];
                                            @endphp
                                            <li class="inclusion">
                                                <span class="bold">{{ strtoupper($hName) }}</span> — {{ strtoupper($hRoom) }}, {{ $hRoomLabel }} ({{ $hPax }} Pax)
                                                @if(!empty($h['date_range']))
                                                    <span class="svc-inline"> · Stay: {{ $h['date_range'] }}</span>
                                                @endif
                                                @if(!empty($hBits))
                                                    <span class="svc-inline"> · {{ implode(' · ', $hBits) }}</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="inclusion">No hotels booked</div>
                                @endif
                            </td>
                            <td>
                                <div class="country-col-label">Other Services</div>
                                @if($hasOther)
                                    <ul class="inclusion-list">
                                        @foreach($sortedOtherServices as $svcItem)
                                            @php
                                                $svcKind = (string) ($svcItem['kind'] ?? '');
                                                $svcCard = $svcItem['card'] ?? null;
                                                $svcLabel = (string) ($svcItem['label'] ?? '');
                                                $svcPlain = (string) ($svcItem['plain'] ?? '');
                                            @endphp
                                            @if($svcKind === 'attraction')
                                                @include('single-tour-package.partials.quotation-service-detail', [
                                                    'kind' => 'attraction',
                                                    'card' => $svcCard,
                                                    'showPrices' => false,
                                                    'currencyCode' => $countryCurrency,
                                                    'moneyFn' => $formatNativeMoney,
                                                ])
                                            @elseif($svcKind === 'restaurant')
                                                @include('single-tour-package.partials.quotation-service-detail', [
                                                    'kind' => 'restaurant',
                                                    'card' => $svcCard,
                                                    'showPrices' => false,
                                                    'currencyCode' => $countryCurrency,
                                                    'moneyFn' => $formatNativeMoney,
                                                ])
                                            @elseif($svcKind === 'vehicle')
                                                @if(is_array($svcCard))
                                                    @include('single-tour-package.partials.quotation-service-detail', [
                                                        'kind' => 'vehicle',
                                                        'label' => $svcLabel !== '' ? $svcLabel : 'Transfer',
                                                        'card' => $svcCard,
                                                        'plain' => $svcPlain,
                                                    ])
                                                @else
                                                    <li class="inclusion"><span class="bold">{{ $svcLabel !== '' ? $svcLabel : 'Transfer' }}:</span> {{ $svcPlain }}</li>
                                                @endif
                                            @elseif($svcKind === 'guide')
                                                <li class="inclusion"><span class="bold">Guide:</span> {{ $svcPlain }}</li>
                                            @endif
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
                <div style="padding: 4px;">No hotel or other services booked</div>
            </div>
        @endif

{{-- ========== SKETCH LAYOUT: Country → Overall → Supplement → Total ========== --}}
        @php
            $formatSelectedPersonsCells = function ($single, $double, $triple, $selectedPersons, callable $moneyFormatter) {
                return [
                    ((float) $single > 0) ? $moneyFormatter($single) : '--',
                    ((float) $double > 0) ? $moneyFormatter($double) : '--',
                    ((float) $triple > 0) ? $moneyFormatter($triple) : '--',
                ];
            };

            $hotelSelectedPersons = 1;
            foreach (($tourPrices['hotel_price_options'] ?? []) as $hpRow) {
                $hotelSelectedPersons = max($hotelSelectedPersons, (int) ($hpRow['selected_persons'] ?? 0));
            }
            if ($hotelSelectedPersons < 1) {
                $hotelSelectedPersons = ($displayOccupancyKey === 'triple') ? 3
                    : (($displayOccupancyKey === 'double') ? 2 : 1);
            }

            $countrySharingRows = is_array($tourPrices['country_sharing'] ?? null)
                ? $tourPrices['country_sharing']
                : [];
            if ($isRestrictedThirdParty && !empty($allowedDmcCountries)) {
                $countrySharingRows = array_values(array_filter($countrySharingRows, function ($share) use ($isPricedCountry) {
                    return $isPricedCountry($share['country'] ?? '');
                }));
            }

            $overallHotelSingle = 0.0;
            $overallHotelDouble = 0.0;
            $overallHotelTriple = 0.0;
            $overallOther = 0.0;
            $overallConvertedOk = false;
            $overallDisplayCurrency = $selectedCurrency;
            $overallDisplayLabel = $currencyLabel;

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

            if (!$overallConvertedOk && !empty($countrySharingRows)) {
                    $fallbackCurrency = null;
                    $sameCurrency = true;
                $fbHotelSingle = $fbHotelDouble = $fbHotelTriple = $fbOther = 0.0;
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
                }
            } elseif ($overallConvertedOk) {
                $overallHotelSingle = ceil($overallHotelSingle);
                $overallHotelDouble = ceil($overallHotelDouble);
                $overallHotelTriple = $overallHotelTriple > 0 ? ceil($overallHotelTriple) : 0;
                $overallOther = ceil($overallOther);
            }

            $overallHotelSingleDisplay = $overallConvertedOk ? $overallHotelSingle : $hotelOnlySingleTotal;
            $overallHotelDoubleDisplay = $overallConvertedOk ? $overallHotelDouble : $hotelOnlyDoubleTotal;
            $overallHotelTripleDisplay = $overallConvertedOk ? $overallHotelTriple : $hotelOnlyTripleTotal;
            $otherServicesDisplayPerPax = $overallConvertedOk
                ? (float) $overallOther
                : (float) ($occupancyKey === 'double' ? $otherDoubleTotal : $otherSingleTotal);

            $moneyFmt = function ($amount) use ($formatMoney) {
                return $formatMoney($amount);
            };
            $moneyFmtNative = function ($amount, $currency) use ($formatNativeMoney) {
                return $formatNativeMoney($amount, $currency);
            };
            $overallMoneyFmt = $overallConvertedOk
                ? function ($amount) use ($formatNativeMoney, $overallDisplayCurrency) {
                    return $formatNativeMoney($amount, $overallDisplayCurrency);
                }
                : function ($amount) use ($formatMoney) {
                    return $formatMoney($amount);
                };

            $overallShowSp = 1;
            if ((float) $overallHotelTripleDisplay > 0) {
                $overallShowSp = 3;
            } elseif ((float) $overallHotelDoubleDisplay > 0) {
                $overallShowSp = 2;
            }
            [$overallCellSingle, $overallCellDouble, $overallCellTriple] = $formatSelectedPersonsCells(
                $overallHotelSingleDisplay,
                $overallHotelDoubleDisplay,
                $overallHotelTripleDisplay,
                $overallShowSp,
                $overallMoneyFmt
            );

            $suppHotels = [];
            $suppServices = [];
            foreach ($supplements as $s) {
                if (strtolower((string)($s['type'] ?? '')) === 'hotel') {
                    $suppHotels[] = $s;
                } else {
                    $suppServices[] = $s;
                }
            }

            $paxForOverall = max(1, (int) $totalPax);
            $sharingLabel = function (int $sp): string {
                return match (max(1, min(3, $sp))) {
                    3 => 'Triple Sharing',
                    2 => 'Double Sharing',
                    default => 'Single Sharing',
                };
            };

            $overallTargetCurrency = $overallConvertedOk
                ? strtoupper((string) $overallDisplayCurrency)
                : strtoupper((string) $selectedCurrency);
            if ($overallTargetCurrency === '') {
                $overallTargetCurrency = strtoupper((string) $baseCurrency);
            }
            $convertToOverall = function ($amount, $fromCurrency) use ($overallTargetCurrency, $baseCurrency): float {
                $amount = (float) $amount;
                if ($amount <= 0) {
                    return 0.0;
                }
                $from = strtoupper(trim((string) $fromCurrency));
                if ($from === '') {
                    $from = strtoupper((string) $baseCurrency);
                }
                if ($from === $overallTargetCurrency) {
                    return $amount;
                }
                $converted = \App\Helpers\CurrencyHelper::convertAmount($amount, $from, $overallTargetCurrency);
                return $converted !== null ? (float) $converted : $amount;
            };
            $fmtOverallAmt = function ($amount) use ($overallTargetCurrency, $formatNativeMoney) {
                $amount = (float) $amount;
                if ($amount <= 0) {
                    return '0.00';
                }
                return $formatNativeMoney(ceil($amount), $overallTargetCurrency);
            };
            // Keep Price × multiplier = Total Price (ceil unit first, then multiply).
            $overallLine = function (string $name, float $unit, int $multiplier, ?string $priceDisplay = null) use ($fmtOverallAmt): array {
                $unitCeil = (float) ceil(max(0.0, $unit));
                $mult = max(1, $multiplier);
                return [
                    'name' => $name,
                    'price' => $unitCeil,
                    'multiplier' => $mult,
                    'price_display' => $priceDisplay,
                    'total' => $unitCeil * $mult,
                    'row_kind' => 'normal',
                ];
            };

            // City markups from tours.currency_markups — bake into services (hidden)
            $cityMarkupIndex = [];
            $rawCityMarkups = $tour->currency_markups ?? ($tour->getAttributes()['currency_markups'] ?? null);
            if (is_string($rawCityMarkups)) {
                $decodedMarkups = json_decode($rawCityMarkups, true);
                $rawCityMarkups = (json_last_error() === JSON_ERROR_NONE) ? $decodedMarkups : null;
            }
            if (is_array($rawCityMarkups)) {
                foreach ($rawCityMarkups as $mRow) {
                    if (!is_array($mRow)) {
                        continue;
                    }
                    $mCountry = trim((string) ($mRow['country'] ?? $mRow['city'] ?? ''));
                    $mCurrency = strtoupper(trim((string) ($mRow['currency'] ?? '')));
                    if ($mCountry === '' && $mCurrency === '') {
                        continue;
                    }
                    $cityMarkupIndex[mb_strtolower($mCountry) . '|' . $mCurrency] = [
                        'markup_type' => strtolower(trim((string) ($mRow['markup_type'] ?? 'flat'))) ?: 'flat',
                        'hotel_markup' => (float) ($mRow['hotel_markup'] ?? 0),
                        'other_markup' => (float) ($mRow['other_markup'] ?? 0),
                        'discount_type' => strtolower(trim((string) ($mRow['discount_type'] ?? 'flat'))) ?: 'flat',
                        'discount_value' => (float) ($mRow['discount_value'] ?? 0),
                        'currency' => $mCurrency !== '' ? $mCurrency : strtoupper((string) $baseCurrency),
                        'country' => $mCountry,
                    ];
                }
            }
            $lookupCityMarkup = function ($country, $currency) use ($cityMarkupIndex) {
                $country = trim((string) $country);
                $currency = strtoupper(trim((string) $currency));
                $key = mb_strtolower($country) . '|' . $currency;
                if (isset($cityMarkupIndex[$key])) {
                    return $cityMarkupIndex[$key];
                }
                foreach ($cityMarkupIndex as $row) {
                    if ($country !== '' && strcasecmp((string) ($row['country'] ?? ''), $country) === 0) {
                        return $row;
                    }
                }
                foreach ($cityMarkupIndex as $row) {
                    if ($currency !== '' && strtoupper((string) ($row['currency'] ?? '')) === $currency) {
                        return $row;
                    }
                }
                return null;
            };

            $overallPackageRows = [];
            $hotelPriceOptions = is_array($tourPrices['hotel_price_options'] ?? null)
                ? $tourPrices['hotel_price_options']
                : [];

            // Child bed totals by country|currency
            $countryChildBedTotals = [];
            $countryChildCounts = [];
            foreach ($hotelPriceOptions as $hpChild) {
                if (!is_array($hpChild)) {
                    continue;
                }
                $cKey = mb_strtolower(trim((string) ($hpChild['country'] ?? 'Other')))
                    . '|' . strtoupper(trim((string) ($hpChild['currency'] ?? $baseCurrency)));
                $cBed = (float) ($hpChild['child_with_bed_total'] ?? 0)
                    + (float) ($hpChild['child_without_bed_total'] ?? 0);
                $cKids = max(0, (int) ($hpChild['children'] ?? 0));
                if ($cBed > 0) {
                    $countryChildBedTotals[$cKey] = ($countryChildBedTotals[$cKey] ?? 0.0) + $cBed;
                }
                if ($cKids > 0) {
                    $countryChildCounts[$cKey] = max((int) ($countryChildCounts[$cKey] ?? 0), $cKids);
                }
            }

            // Tour guest counts for Adult / Child multipliers
            $overallAdultCount = max(1, (int) (($payingPax > 0) ? $payingPax : ($displayAdults ?? $adults ?? 1)));
            $overallChildCount = max(0, (int) ($children ?? 0));

            // Total Package Price per city (per person display):
            // City (bold) | Hotel-Accommodation
            //             | Other Service(s)
            $cityOverallBuckets = [];
            foreach ($countrySharingRows as $share) {
                if (!is_array($share)) {
                    continue;
                }
                $shareCountry = trim((string) ($share['country'] ?? 'Other'));
                if (!$isPricedCountry($shareCountry)) {
                    continue;
                }
                $shareCurrency = strtoupper(trim((string) ($share['currency'] ?? $baseCurrency)));
                if ($shareCurrency === '') {
                    $shareCurrency = strtoupper((string) $baseCurrency);
                }
                $shareKey = (string) ($share['key'] ?? (mb_strtolower($shareCountry) . '|' . $shareCurrency));
                $shareChildKey = mb_strtolower(trim($shareCountry)) . '|' . $shareCurrency;
                $shareCity = trim((string) ($share['city'] ?? ($countryMeta[$shareKey]['city'] ?? '')));
                if ($shareCity === '') {
                    $shareCity = $preferredCityByCountry[mb_strtolower($shareCountry)] ?? $shareCountry;
                }
                if ($shareCity === '') {
                    $shareCity = $shareCountry;
                }

                $hSingle = (float) ($share['hotel_single'] ?? 0);
                $hDouble = (float) ($share['hotel_double'] ?? 0);
                $hTriple = (float) ($share['hotel_triple'] ?? 0);
                if ($isProTour) {
                    $hSingle = $hDouble > 0 ? $hDouble : $hSingle;
                }
                $hotelAdultUnitNative = 0.0;
                if ($hTriple > 0) {
                    $hotelAdultUnitNative = $hTriple;
                } elseif ($hDouble > 0) {
                    $hotelAdultUnitNative = $hDouble;
                } elseif ($hSingle > 0) {
                    $hotelAdultUnitNative = $hSingle;
                }

                $otherAdultUnitNative = (float) ($share['other_services_single'] ?? ($share['other_services_double'] ?? 0));
                $hotelAdultUnit = $hotelAdultUnitNative > 0
                    ? (float) ceil($convertToOverall($hotelAdultUnitNative, $shareCurrency))
                    : 0.0;
                $otherAdultUnit = $otherAdultUnitNative > 0
                    ? (float) ceil($convertToOverall($otherAdultUnitNative, $shareCurrency))
                    : 0.0;

                $childBedNative = (float) ($countryChildBedTotals[$shareChildKey] ?? 0);
                $otherChildNative = (float) ($share['other_services_child'] ?? 0);
                $cityChildCount = max(
                    (int) ($countryChildCounts[$shareChildKey] ?? 0),
                    $overallChildCount
                );
                if (($childBedNative > 0 || $otherChildNative > 0) && $cityChildCount <= 0) {
                    $cityChildCount = 1;
                }
                $hotelChildUnit = 0.0;
                $otherChildUnit = 0.0;
                if ($childBedNative > 0 && $cityChildCount > 0) {
                    $hotelChildUnit = (float) ceil(
                        (float) ceil($convertToOverall($childBedNative, $shareCurrency)) / $cityChildCount
                    );
                }
                if ($otherChildNative > 0 && $cityChildCount > 0) {
                    $otherChildUnit = (float) ceil(
                        (float) ceil($convertToOverall($otherChildNative, $shareCurrency)) / $cityChildCount
                    );
                }

                if ($hotelAdultUnit <= 0 && $otherAdultUnit <= 0 && $hotelChildUnit <= 0 && $otherChildUnit <= 0) {
                    continue;
                }

                if (!isset($cityOverallBuckets[$shareKey])) {
                    $cityOverallBuckets[$shareKey] = [
                        'label' => $shareCity,
                        'hotel_adult_unit' => 0.0,
                        'other_adult_unit' => 0.0,
                        'hotel_child_unit' => 0.0,
                        'other_child_unit' => 0.0,
                        'adult_count' => $overallAdultCount,
                        'child_count' => 0,
                    ];
                }
                $cityOverallBuckets[$shareKey]['hotel_adult_unit'] += $hotelAdultUnit;
                $cityOverallBuckets[$shareKey]['other_adult_unit'] += $otherAdultUnit;
                $cityOverallBuckets[$shareKey]['hotel_child_unit'] += $hotelChildUnit;
                $cityOverallBuckets[$shareKey]['other_child_unit'] += $otherChildUnit;
                $cityOverallBuckets[$shareKey]['adult_count'] = max(
                    (int) $cityOverallBuckets[$shareKey]['adult_count'],
                    $overallAdultCount
                );
                $cityOverallBuckets[$shareKey]['child_count'] = max(
                    (int) $cityOverallBuckets[$shareKey]['child_count'],
                    $cityChildCount
                );
            }

            foreach ($cityOverallBuckets as $bucket) {
                $cityLabel = trim((string) ($bucket['label'] ?? 'City'));
                if ($cityLabel === '') {
                    $cityLabel = 'City';
                }
                $hotelAdultUnit = (float) ($bucket['hotel_adult_unit'] ?? 0);
                $otherAdultUnit = (float) ($bucket['other_adult_unit'] ?? 0);
                $hotelChildUnit = (float) ($bucket['hotel_child_unit'] ?? 0);
                $otherChildUnit = (float) ($bucket['other_child_unit'] ?? 0);
                $adultCnt = max(1, (int) ($bucket['adult_count'] ?? $overallAdultCount));
                $childCnt = max(0, (int) ($bucket['child_count'] ?? 0));
                $citySubtotal = 0.0;
                $cityServiceRows = [];

                if ($hotelAdultUnit > 0) {
                    $lineTotal = $hotelAdultUnit * $adultCnt;
                    $citySubtotal += $lineTotal;
                    $cityServiceRows[] = [
                        'service' => 'Hotel-Accommodation',
                        'price' => $hotelAdultUnit,
                        'total' => $lineTotal,
                        'pax' => $adultCnt,
                        'pax_type' => 'adult',
                    ];
                }
                if ($otherAdultUnit > 0) {
                    $lineTotal = $otherAdultUnit * $adultCnt;
                    $citySubtotal += $lineTotal;
                    $cityServiceRows[] = [
                        'service' => 'Other Service(s)',
                        'price' => $otherAdultUnit,
                        'total' => $lineTotal,
                        'pax' => $adultCnt,
                        'pax_type' => 'adult',
                    ];
                }
                if ($hotelChildUnit > 0 && $childCnt > 0) {
                    $lineTotal = $hotelChildUnit * $childCnt;
                    $citySubtotal += $lineTotal;
                    $cityServiceRows[] = [
                        'service' => 'Hotel-Accommodation (Child)',
                        'price' => $hotelChildUnit,
                        'total' => $lineTotal,
                        'pax' => $childCnt,
                        'pax_type' => 'child',
                    ];
                }
                if ($otherChildUnit > 0 && $childCnt > 0) {
                    $lineTotal = $otherChildUnit * $childCnt;
                    $citySubtotal += $lineTotal;
                    $cityServiceRows[] = [
                        'service' => 'Other Service(s) (Child)',
                        'price' => $otherChildUnit,
                        'total' => $lineTotal,
                        'pax' => $childCnt,
                        'pax_type' => 'child',
                    ];
                }

                if (!empty($cityServiceRows)) {
                    $overallPackageRows[] = [
                        'row_kind' => 'city_group',
                        'city' => $cityLabel,
                        'services' => $cityServiceRows,
                        'city_total' => $citySubtotal,
                        'adult_count' => $adultCnt,
                        'child_count' => $childCnt,
                    ];
                }
            }

            foreach ($suppHotels as $s) {
                $hotelLabel = trim((string) ($s['hotel_name'] ?? ($s['display_name'] ?? ($s['name'] ?? 'Hotel'))));
                if ($hotelLabel === '') {
                    $hotelLabel = 'Hotel';
                }
                $selPersons = (int) ($s['selected_persons'] ?? 0);
                if ($selPersons <= 0) {
                    if (!empty($s['show_triple'])) {
                        $selPersons = 3;
                    } elseif (!empty($s['show_double'])) {
                        $selPersons = 2;
                    } else {
                        $selPersons = 1;
                    }
                }
                $suppSingle = (float) ($s['single'] ?? 0);
                $suppDouble = (float) ($s['double'] ?? 0);
                $suppTriple = (float) ($s['triple'] ?? 0);
                if ($isProTour && $selPersons >= 2) {
                    $suppSingle = $suppDouble > 0 ? $suppDouble : $suppSingle;
                }
                $unitNative = $selPersons >= 3 ? $suppTriple : ($selPersons >= 2 ? $suppDouble : $suppSingle);
                if ($unitNative <= 0) {
                    continue;
                }
                $suppCurrency = strtoupper(trim((string) ($s['currency'] ?? '')));
                if ($suppCurrency === '') {
                    $suppCurrency = strtoupper((string) $baseCurrency);
                }
                $unit = $convertToOverall($unitNative, $suppCurrency);
                if ($unit <= 0) {
                    continue;
                }
                $overallPackageRows[] = $overallLine(
                    $hotelLabel . ' (' . $sharingLabel($selPersons) . ') (Supplement)',
                    $unit,
                    $selPersons
                );
            }
            foreach ($suppServices as $s) {
                $suppTypeRaw = trim((string) ($s['type'] ?? ''));
                $svcName = trim((string) ($s['name'] ?? ($s['AttractionName'] ?? ($s['restaurantName'] ?? ''))));
                $typePretty = $suppTypeRaw !== '' ? \Illuminate\Support\Str::headline(str_replace([' ', '-'], '_', $suppTypeRaw)) : '';
                $namePretty = $svcName !== '' ? \Illuminate\Support\Str::headline($svcName) : '';
                if ($svcName !== '' && $typePretty !== '' && strtolower(preg_replace('/[^a-z0-9]+/', '', $suppTypeRaw)) !== strtolower(preg_replace('/[^a-z0-9]+/', '', $svcName))) {
                    $svcLabel = $typePretty . ': ' . $namePretty;
                } elseif ($svcName !== '') {
                    $svcLabel = $namePretty;
                } else {
                    $svcLabel = $typePretty !== '' ? $typePretty : 'Supplement';
                }
                $unitNative = $occupancyKey === 'double'
                    ? (float) ($s['double'] ?? 0)
                    : (float) ($s['single'] ?? 0);
                if ($unitNative <= 0) {
                    $unitNative = (float) ($s['single'] ?? ($s['double'] ?? 0));
                }
                if ($unitNative <= 0) {
                    continue;
                }
                $suppCurrency = strtoupper(trim((string) ($s['currency'] ?? '')));
                if ($suppCurrency === '') {
                    $suppCurrency = strtoupper((string) $baseCurrency);
                }
                $unit = $convertToOverall($unitNative, $suppCurrency);
                if ($unit <= 0) {
                    continue;
                }
                $overallPackageRows[] = $overallLine(
                    $svcLabel . ' (Supplement)',
                    $unit,
                    $paxForOverall
                );
            }

            // TOTAL COST = city line items + supplements (exclude display-only city_total rows)
            $overallLinesSubtotal = 0.0;
            foreach ($overallPackageRows as $r) {
                $kind = (string) ($r['row_kind'] ?? 'normal');
                if ($kind === 'city_total') {
                    continue;
                }
                if ($kind === 'city_group') {
                    $overallLinesSubtotal += (float) ($r['city_total'] ?? 0);
                    continue;
                }
                $overallLinesSubtotal += (float) ($r['total'] ?? 0);
            }

            $overallDiscountShown = 0.0;
            // Prefer pre-converted discount from calculateTourPrices (create/edit markup form)
            if ((float) ($markupDiscountDisplayAmount ?? 0) > 0) {
                $overallDiscountShown = (float) $markupDiscountDisplayAmount;
            } else {
                foreach ($cityMarkupIndex as $mInfo) {
                    $dRaw = (float) ($mInfo['discount_value'] ?? 0);
                    if ($dRaw <= 0) {
                        continue;
                    }
                    if (($mInfo['discount_type'] ?? 'flat') === 'percentage') {
                        $overallDiscountShown += $overallLinesSubtotal * $dRaw / 100.0;
                    } else {
                        $overallDiscountShown += $convertToOverall($dRaw, $mInfo['currency'] ?? $overallTargetCurrency);
                    }
                }
            }

            $overallMarkupShown = 0.0; // markup is already baked into per-pax rates — never show to customer

            // Discount first, then Tax on (Subtotal − Discount)
            $taxableBase = max(0.0, (float) $overallLinesSubtotal - (float) $overallDiscountShown);
            $taxPersons = max(1, (int) (($displayAdults ?? $adults ?? 0) + ($children ?? 0)));
            if ($taxPersons < 1) {
                $taxPersons = max(1, (int) (($bookingDetails['no_of_adults'] ?? 0) + ($bookingDetails['no_of_children'] ?? 0)));
            }
            $taxDays = \App\Helpers\TaxHelper::calculateDays(
                $tour->check_in_time ?? null,
                $tour->check_out_time ?? null
            );
            $taxResult = \App\Helpers\TaxHelper::calculateTourTaxes(
                (float) $taxableBase,
                $tour->taxes ?? null,
                $taxPersons,
                $taxDays
            );
            $overallTaxShown = (float) ceil((float) ($taxResult['total_tax'] ?? 0));
            $overallTaxBreakdown = is_array($taxResult['breakdown'] ?? null) ? $taxResult['breakdown'] : [];

            // TOTAL COST = Subtotal − Discount + Tax
            $totalCostAmount = max(0.0, $taxableBase + $overallTaxShown);
            $overallDisplayLabel = $overallTargetCurrency === 'INR' ? 'INR' : $overallTargetCurrency;
            $totalCostLabel = $fmtOverallAmt($totalCostAmount);
            $showTotalsBreakdown = ((float) $overallDiscountShown > 0) || ((float) $overallTaxShown > 0);
        @endphp

        {{-- 1) Package Price by Country --}}
        @if(!empty($countrySharingRows))
        <div class="overall-price-box">
            <div class="panel-title">Package Price by Country</div>
            @foreach($countrySharingRows as $share)
                @php
                    $shareCountry = $share['country'] ?? 'Other';
                    $shareCity = $share['city'] ?? ($countryMeta[$share['key'] ?? '']['city'] ?? '');
                    $shareCurrency = strtoupper((string)($share['currency'] ?? $baseCurrency));
                    $shareTitle = $formatLocationTitle($shareCity, $shareCountry, $shareCurrency);
                    $shareHotelSingle = (float)($share['hotel_single'] ?? 0);
                    $shareHotelDouble = (float)($share['hotel_double'] ?? 0);
                    $shareHotelTriple = (float)($share['hotel_triple'] ?? 0);
                    $shareKey = (string)($share['key'] ?? (mb_strtolower($shareCountry) . '|' . $shareCurrency));
                    $shareChildKey = mb_strtolower(trim((string) $shareCountry)) . '|' . $shareCurrency;
                    $shareChildBed = (float) ($countryChildBedTotals[$shareChildKey] ?? 0);
                    // Other is per-pax (same formula as CommonHelper), not order-line total
                    $shareOther = (float)($share['other_services_single'] ?? ($share['other_services_double'] ?? 0));
                    $shareOtherChild = (float)($share['other_services_child'] ?? 0);
                    if ($isProTour) {
                        $shareHotelSingle = $shareHotelDouble > 0 ? $shareHotelDouble : $shareHotelSingle;
                    }
                    // Show every occupancy column that has a per-pax value
                    $shareShowSp = 1;
                    if ($shareHotelTriple > 0) $shareShowSp = 3;
                    elseif ($shareHotelDouble > 0) $shareShowSp = 2;

                    // Adult / Child under the booked occupancy cell
                    $hotelOccCell = function ($adultAmt, $isBookedCol) use ($shareCurrency, $shareChildBed, $formatNativeMoney) {
                        $adultAmt = (float) $adultAmt;
                        if ($adultAmt <= 0) {
                            return '--';
                        }
                        $html = e($formatNativeMoney($adultAmt, $shareCurrency)) . '(Adult)';
                        if ($isBookedCol && $shareChildBed > 0) {
                            $html .= '<br>' . e($formatNativeMoney($shareChildBed, $shareCurrency)) . '(Child)';
                        }
                        return $html;
                    };
                    $shareCellSingle = $hotelOccCell($shareHotelSingle, $shareShowSp === 1);
                    $shareCellDouble = $hotelOccCell($shareHotelDouble, $shareShowSp === 2);
                    $shareCellTriple = $hotelOccCell($shareHotelTriple, $shareShowSp === 3);

                    // Other Services: adult per-pax + attraction/restaurant child total
                    $otherCellHtml = '--';
                    if ($shareOther > 0 || $shareOtherChild > 0) {
                        $parts = [];
                        if ($shareOther > 0) {
                            $parts[] = e($formatNativeMoney($shareOther, $shareCurrency)) . '(Adult)';
                        }
                        if ($shareOtherChild > 0) {
                            $parts[] = e($formatNativeMoney($shareOtherChild, $shareCurrency)) . '(Child)';
                        }
                        $otherCellHtml = implode('<br>', $parts);
                    }
                @endphp
                <div style="border-top: 1px solid #222;">
                    <div class="country-box-title">{{ $shareTitle }}</div>
                    <table class="price-split-table">
                        <tr>
                            <td>
                                <div class="country-col-label">Hotel-Accommodation (per pax)</div>
                                <table class="price-grid">
                                    <thead>
                                        <tr>
                                            <th style="width: 33.33%;">Single</th>
                                            <th style="width: 33.33%;">Double</th>
                                            <th style="width: 33.33%;">Triple</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{!! $shareCellSingle !!}</td>
                                            <td>{!! $shareCellDouble !!}</td>
                                            <td>{!! $shareCellTriple !!}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                            <td>
                                <div class="country-col-label">Other Services (per pax)</div>
                                <table class="price-grid">
                                    <thead>
                                        <tr>
                                            <th style="width: 33.33%;">Single</th>
                                            <th style="width: 33.33%;">Double</th>
                                            <th style="width: 33.33%;">Triple</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="3">{!! $otherCellHtml !!}</td>
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

        {{-- 2) Overall Package — City | Particulars | Pax | Total (× pax) --}}
        <div class="overall-price-box" style="margin-top: 6px;">
            <div class="panel-title">Total Package Price ({{ $overallDisplayLabel }})</div>
            <table class="totals-table">
                <thead>
                    <tr>
                        <th class="city-cell" style="width: 20%;">City</th>
                        <th class="svc-cell" style="text-align: left; width: 42%;">Particulars</th>
                        <th class="pax-cell" style="width: 12%;">Pax</th>
                        <th class="amt-cell" style="width: 26%;">Total Price</th>
                    </tr>
                </thead>
                <tbody>
                    @php $hasAnyPackageRow = false; @endphp
                    @foreach($overallPackageRows as $row)
                        @php
                            $rowKind = (string) ($row['row_kind'] ?? 'normal');
                            $hasAnyPackageRow = true;
                        @endphp
                        @if($rowKind === 'city_group')
                            @php
                                $services = is_array($row['services'] ?? null) ? $row['services'] : [];
                                $cityName = (string) ($row['city'] ?? '');
                                $svcCount = max(1, count($services));
                            @endphp
                            @foreach($services as $svcIdx => $svc)
                                @php
                                    $svcPax = max(0, (int) ($svc['pax'] ?? 0));
                                    $svcPaxType = strtolower((string) ($svc['pax_type'] ?? 'adult'));
                                    $paxLabel = $svcPax > 0
                                        ? ($svcPax . ' ' . ($svcPaxType === 'child' ? 'Child' : 'Adult') . ($svcPax > 1 ? ($svcPaxType === 'child' ? 'ren' : 's') : ''))
                                        : '—';
                                    $svcName = (string) ($svc['service'] ?? '');
                                @endphp
                                <tr>
                                    @if($svcIdx === 0)
                                        <td class="city-cell" rowspan="{{ $svcCount }}">{{ $cityName }}</td>
                                    @endif
                                    <td class="svc-cell">{{ $svcName }}</td>
                                    <td class="pax-cell">{{ $paxLabel }}</td>
                                    <td class="amt-cell">{{ $fmtOverallAmt($svc['total'] ?? 0) }}</td>
                                </tr>
                            @endforeach
                        @elseif($rowKind === 'city_total')
                            {{-- city totals are implied by TOTAL COST; skip display row --}}
                        @else
                            <tr>
                                <td class="city-cell">—</td>
                                <td class="svc-cell">{{ $row['name'] ?? '' }}</td>
                                <td class="pax-cell">{{ max(1, (int) ($row['multiplier'] ?? 1)) }}</td>
                                <td class="amt-cell">{{ $fmtOverallAmt($row['total'] ?? ($row['price'] ?? 0)) }}</td>
                            </tr>
                        @endif
                    @endforeach
                    @if(!$hasAnyPackageRow)
                        <tr>
                            <td colspan="4">No package items</td>
                        </tr>
                    @endif

                    @if($showTotalsBreakdown)
                        <tr>
                            <td style="text-align: right; background: #f7f7f7;" colspan="3"><strong>Subtotal</strong></td>
                            <td class="amt-cell" style="background: #f7f7f7;">{{ $fmtOverallAmt($overallLinesSubtotal) }}</td>
                        </tr>
                        @if((float) $overallDiscountShown > 0)
                            <tr>
                                <td style="text-align: right;" colspan="3">Discount</td>
                                <td class="amt-cell">{{ $fmtOverallAmt($overallDiscountShown) }}</td>
                            </tr>
                        @endif
                        @if((float) $overallTaxShown > 0)
                            <tr>
                                <td style="text-align: right;" colspan="3">
                                    Tax
                                    @if(!empty($overallTaxBreakdown) && count($overallTaxBreakdown) === 1)
                                        ({{ array_key_first($overallTaxBreakdown) }})
                                    @endif
                                </td>
                                <td class="amt-cell">{{ $fmtOverallAmt($overallTaxShown) }}</td>
                            </tr>
                        @endif
                    @endif
                    <tr>
                        <td style="text-align: right; font-weight: bold; background: #f0f0f0;" colspan="3">TOTAL COST</td>
                        <td class="amt-cell" style="background: #f0f0f0;">{{ $totalCostLabel }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

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
