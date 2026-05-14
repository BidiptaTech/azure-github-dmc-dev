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

        .quotation-main-table td {
            border: 1px solid #000;
            vertical-align: top;
            padding: 6px 6px;
        }

        .quotation-col {
            width: 50%;
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
            color: #000;
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
            $converted = ceil(((float)$amount) * $exchangeRate);
            return (string)(int)$converted;
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

        $otherTotalForOccupancy = $occupancyKey === 'double' ? $otherDoubleTotal : $otherSingleTotal;

        // Build booked inclusions list from servicesByType (derived from orders for this tour)
        // We intentionally only show the categories requested by the user.
        $bookedAttractions = []; // list (keep duplicates)
        $bookedRestaurants = []; // list (keep duplicates)
        $bookedArrivals = []; // "Arrival: ..." => true
        $bookedDepartures = []; // "Departure: ..." => true
        $bookedLocalTransfers = []; // "Local Transfer: ..." => true

        if (!empty($servicesByType) && is_array($servicesByType)) {
            foreach ($servicesByType as $type => $cards) {
                if (!is_array($cards) || empty($cards)) continue;

                $normalizedType = str_replace(' ', '_', strtolower($type));

                // Attraction name(s)
                if ($normalizedType === 'attraction' || $normalizedType === 'attraction_package') {
                    foreach ($cards as $card) {
                        if (!is_array($card)) continue;
                        $title = $card['title'] ?? ($card['attraction']['title'] ?? null);
                        if (!empty($title)) $bookedAttractions[] = $title;
                    }
                }

                // Restaurant name + meal plan
                if ($normalizedType === 'restaurant') {
                    foreach ($cards as $card) {
                        if (!is_array($card)) continue;
                        $name = $card['title'] ?? ($card['restaurant']['name'] ?? null);
                        $mealPlan = $card['restaurant']['meal_plan'] ?? ($card['meal_plan'] ?? null);
                        if (!empty($name)) {
                            $line = $name;
                            if (!empty($mealPlan)) $line .= ' - ' . $mealPlan;
                            $bookedRestaurants[] = $line;
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
                            $text = 'Arrival: ' . $portName;
                            if (!empty($entryTime)) $text .= ' (' . $entryTime . ')';
                            if (!empty($transferType)) $text .= ' - ' . $transferType;
                            $bookedArrivals[$text] = true;
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
                            $text = 'Departure: ' . $portName;
                            if (!empty($exitTime)) $text .= ' (' . $exitTime . ')';
                            if (!empty($transferType)) $text .= ' - ' . $transferType;
                            $bookedDepartures[$text] = true;
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
                            $text = 'Local Transfer: ' . $transferType;
                            if (!empty($vehicleTypeSeater)) $text .= ' - ' . $vehicleTypeSeater;
                            $bookedLocalTransfers[$text] = true;
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
            foreach ($bookedAttractions as $a) {
                if (isset($suppAttractionCounts[$a]) && $suppAttractionCounts[$a] > 0) {
                    $suppAttractionCounts[$a]--;
                    continue;
                }
                $filtered[] = $a;
            }
            $bookedAttractions = $filtered;
        }

        if (!empty($suppRestaurantCounts)) {
            $filtered = [];
            foreach ($bookedRestaurants as $r) {
                if (isset($suppRestaurantCounts[$r]) && $suppRestaurantCounts[$r] > 0) {
                    $suppRestaurantCounts[$r]--;
                    continue;
                }
                $filtered[] = $r;
            }
            $bookedRestaurants = $filtered;
        }
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
                    </div>
                </td>
            </tr>
        </table>
        

        <table class="quotation-main-table">
            <tr>
                <td class="quotation-col">
                    <div class="panel-title">Hotel cost for entire package</div>

                    {{-- Date first --}}
                    <div class="money-line">
                        <div class="inclusion"><span class="bold">Date:</span> {{ $inclusionDateRange }}</div>
                    </div>

                    {{-- Inclusions list --}}
                    <div class="section-label">Inclusions:</div>
                    @php
                        // Build a lookup from hotel_price_options (has correctly computed triple)
                        // keyed by lowercase hotel_name for quick matching
                        $hotelPriceLookup = [];
                        foreach ($tourPrices['hotel_price_options'] ?? [] as $hp) {
                            $k = strtolower(trim((string)($hp['hotel_name'] ?? '')));
                            if ($k !== '') {
                                $hotelPriceLookup[$k] = $hp;
                            }
                        }
                    @endphp
                    @if(!empty($hotelOptions) && is_array($hotelOptions))
                        @php
                            // Deduplicate: same hotel_name + room_category shown only once
                            $seenHotelKeys = [];
                        @endphp
                        <ul class="inclusion-list">
                            @foreach($hotelOptions as $h)
                                @php
                                    $hotelName        = $h['hotel_name'] ?? 'Hotel';
                                    $hotelNameLower   = strtolower(trim((string)$hotelName));
                                    $roomCategoryName = $h['room_categories'][0]['name'] ?? ($h['hotel_category'] ?? 'Room');
                                    $roomCatLower     = strtolower(trim((string)$roomCategoryName));
                                    $dedupKey         = $hotelNameLower . '||' . $roomCatLower;

                                    // Skip if already shown (duplicate order for same hotel+room)
                                    if (isset($seenHotelKeys[$dedupKey])) continue;
                                    $seenHotelKeys[$dedupKey] = true;

                                    // Prices: prefer hotel_price_options (has triple); fall back to first_total
                                    $priceRow    = $hotelPriceLookup[$hotelNameLower] ?? null;
                                    $hotelSingle = (float)($priceRow['single'] ?? $h['first_total']['single'] ?? 0);
                                    $hotelDouble = (float)($priceRow['double'] ?? $h['first_total']['double'] ?? 0);
                                    $hotelTriple = (float)($priceRow['triple'] ?? $h['first_total']['triple'] ?? 0);
                                @endphp
                                <li class="inclusion">
                                    {{ strtoupper($hotelName) }}-{{ strtoupper($roomCategoryName) }}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="inclusion">No hotel options available</div>
                    @endif
                </td>

                <td class="quotation-col">
                    <div class="panel-title">Other services cost for entire package</div>

                    {{-- Date first --}}
                    <div class="money-line">
                        <div class="inclusion"><span class="bold">Date:</span> {{ $inclusionDateRange }}</div>
                    </div>

                    {{-- Inclusions list --}}
                    <div class="section-label">Inclusions:</div>
                    @php $hasAnyOtherInclusions = (!empty($bookedAttractions) || !empty($bookedRestaurants) || !empty($bookedArrivals) || !empty($bookedDepartures) || !empty($bookedLocalTransfers)); @endphp
                    @if($hasAnyOtherInclusions)
                        <ul class="inclusion-list">
                            @if(!empty($bookedAttractions))
                                @foreach($bookedAttractions as $a)
                                    <li class="inclusion"><span class="bold">Attraction:</span> {{ $a }}</li>
                                @endforeach
                            @endif
                            @if(!empty($bookedRestaurants))
                                @foreach($bookedRestaurants as $r)
                                    <li class="inclusion"><span class="bold">Restaurant:</span> {{ $r }}</li>
                                @endforeach
                            @endif
                            @if(!empty($bookedArrivals))
                                @foreach(array_keys($bookedArrivals) as $ar)
                                    <li class="inclusion"><span class="bold">Arrival:</span> {{ $ar }}</li>
                                @endforeach
                            @endif
                            @if(!empty($bookedDepartures))
                                @foreach(array_keys($bookedDepartures) as $dp)
                                    <li class="inclusion"><span class="bold">Departure:</span> {{ $dp }}</li>
                                @endforeach
                            @endif
                            @if(!empty($bookedLocalTransfers))
                                @foreach(array_keys($bookedLocalTransfers) as $lt)
                                    <li class="inclusion"><span class="bold">Local Transfer:</span> {{ $lt }}</li>
                                @endforeach
                            @endif
                        </ul>
                    @else
                        <div class="inclusion">No other services booked</div>
                    @endif

                    {{-- Price is shown in the overall section below --}}
                </td>
            </tr>
        </table>

        {{-- Overall totals (Hotel + Other services; supplements excluded) --}}
        @php
            $overallSingle = (float)($tourPrices['single_sharing'] ?? 0);
            $overallDouble = (float)($tourPrices['double_sharing'] ?? 0);
            $overallTriple = (float)($tourPrices['triple_sharing'] ?? 0);
        @endphp
        <div style="margin-top: 10px;">
            <div class="panel-title">Packaged price per person</div>
            <table style="width: 100%; border-collapse: collapse; border: 2px solid #000; table-layout: fixed;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #000; padding: 6px; background: #f3f3f3; text-align: center; width: 33.33%;">Single</th>
                        <th style="border: 1px solid #000; padding: 6px; background: #f3f3f3; text-align: center; width: 33.33%;">Double</th>
                        <th style="border: 1px solid #000; padding: 6px; background: #f3f3f3; text-align: center; width: 33.33%;">Triple</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;">{{ $formatMoney($overallSingle) }}</td>
                        <td style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;">{{ $formatMoney($overallDouble) }}</td>
                        <td style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;">{{ $formatMoney($overallTriple) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        

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
                            @endphp
                            <tr>
                                <td style="border: 1px solid #000; padding: 6px; vertical-align: top;">
                                    {{ $hotelLabel }}
                                    @if($niceDate)
                                        <span class="subtle"> ({{ $niceDate }})</span>
                                    @endif
                                </td>
                                <td style="border: 1px solid #000; padding: 6px; text-align: center;">{{ $formatMoney($suppSingle) }}</td>
                                <td style="border: 1px solid #000; padding: 6px; text-align: center;">{{ $formatMoney($suppDouble) }}</td>
                                <td style="border: 1px solid #000; padding: 6px; text-align: center;">{{ $suppTriple > 0 ? $formatMoney($suppTriple) : '—' }}</td>
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
                                $suppType  = strtolower((string)($s['type'] ?? ''));
                                $typeLabel = strtoupper($suppType) ?: 'SUPPLEMENT';
                                $svcName   = $s['name'] ?? ($s['AttractionName'] ?? ($s['restaurantName'] ?? ''));
                                $svcLabel  = $svcName !== '' ? ($typeLabel . ': ' . $svcName) : $typeLabel;
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

    </div>
</body>
</html>
