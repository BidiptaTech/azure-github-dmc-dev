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

        /* Header */
        .quotation-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .quotation-header-table td {
            vertical-align: top;
            padding: 0;
        }

        .quotation-logo-cell {
            width: 150px;
        }

        .quotation-logo {
            width: 105px;
            height: 105px;
            object-fit: contain;
            margin-top: -40px;
        }

        .quotation-title {
            text-align: center;
            font-weight: bold;
            font-size: 20px;
            padding-top: 1px;
        }

        .quotation-dmc-details {
            text-align: left;
            font-size: 12px;
            line-height: 1.5;
            padding-top: 6px;
            white-space: pre-line;
        }
    </style>
</head>
<body>
    @php
        $adults = (int)($bookingDetails['no_of_adults'] ?? 0);
        $children = (int)($bookingDetails['no_of_children'] ?? 0);
        $infants = (int)($bookingDetails['no_of_infants'] ?? 0);

        $leadGuestName = $bookingDetails['lead_guest_name'] ?? '';

        $paxParts = [];
        $paxParts[] = str_pad((string) $adults, 2, '0', STR_PAD_LEFT) . 'A';
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
            ? $travelFrom->format('d-m-Y') . '-' . strtolower($travelTo->format('jS F'))
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

        $segregated = $tourPrices['segregated'] ?? [];
        $supplements = $tourPrices['supplements'] ?? [];

        $otherServiceTypes = [
            'attraction',
            'restaurant',
            'entry_port',
            'exit_port',
            'guide',
            'travel_hourly',
            'travel_point',
            'local_transport',
            'other',
        ];

        $otherSingleTotal = 0.0;
        $otherDoubleTotal = 0.0;
        foreach ($otherServiceTypes as $typeKey) {
            if (!isset($segregated[$typeKey]) || !is_array($segregated[$typeKey])) continue;
            $otherSingleTotal += (float)($segregated[$typeKey]['single'] ?? 0);
            $otherDoubleTotal += (float)($segregated[$typeKey]['double'] ?? 0);
        }

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

                if ($suppType === 'hotel' && $suppName !== '') {
                    $k = strtolower(trim($suppName));
                    $suppHotelCounts[$k] = ($suppHotelCounts[$k] ?? 0) + 1;
                }

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
            // DMC/Company header data (requested: derive from logged-in user)
            $dmcLogoSrc = !empty($dmcLogo) ? (string)$dmcLogo : null;

            $dmcUser = null;
            $dmcCompanyNameHeader = $dmcDetails['company_name'] ?? ($dmcCompanyName ?? '');
            $dmcAddressHeader = $dmcDetails['address'] ?? '';
            $dmcPhoneHeader = $dmcDetails['phone'] ?? '';
            $dmcEmailHeader = $dmcDetails['email'] ?? '';
            $dmcCompanyRegNo = $dmcDetails['company_reg_no'] ?? null;
            $dmcLicenceNo = $dmcDetails['licence_no'] ?? null;

            try {
                $currentUser = \Illuminate\Support\Facades\Auth::user();
                if ($currentUser) {
                    $dmcId = \App\Helpers\CommonHelper::getDmcId($currentUser);
                    if (!empty($dmcId)) {
                        $dmcUser = \App\Models\User::where('userId', $dmcId)->first();
                    }
                }
            } catch (\Throwable $e) {
                // keep fallback values if DMC lookup fails
            }

            if ($dmcUser) {
                $dmcCompanyNameHeader = $dmcUser->company_name ?? ($dmcUser->companyName ?? $dmcCompanyNameHeader);
                $dmcAddressHeader = $dmcUser->address ?? $dmcAddressHeader;
                $dmcEmailHeader = $dmcUser->email ?? $dmcEmailHeader;

                // Build phone with country code if present
                $phoneRaw = $dmcUser->phone ?? null;
                if (!empty($phoneRaw)) {
                    $cc = $dmcUser->country_code ?? null;
                    $dmcPhoneHeader = !empty($cc) ? ('+' . $cc . ' ' . $phoneRaw) : (string)$phoneRaw;
                }

                $dmcCompanyRegNo = $dmcUser->company_reg_no ?? ($dmcUser->companyRegNo ?? $dmcCompanyRegNo);
                $dmcLicenceNo = $dmcUser->licence_no ?? ($dmcUser->licenceNo ?? $dmcLicenceNo);
            }

            $dmcLines = trim((string)$dmcCompanyNameHeader) . "\n";
            $dmcLines .= trim((string)$dmcAddressHeader) . "\n";

            if (!empty($dmcPhoneHeader)) {
                $dmcLines .= "Tel: " . trim((string)$dmcPhoneHeader) . "\n";
            }
            if (!empty($dmcEmailHeader)) {
                $dmcLines .= "Email: " . trim((string)$dmcEmailHeader) . "\n";
            }

            if (!empty($dmcCompanyRegNo)) {
                $dmcLines .= "Company Reg No: " . trim((string)$dmcCompanyRegNo) . "\n";
            }
            if (!empty($dmcLicenceNo)) {
                $dmcLines .= "Licence No: " . trim((string)$dmcLicenceNo);
            } else {
                // Trim trailing newlines for cleaner PDF output
                $dmcLines = rtrim($dmcLines);
            }
        @endphp

        <table class="quotation-header-table">
            <tr>
                <td class="quotation-logo-cell">
                    @if(!empty($dmcLogoSrc))
                        <img src="{{ $dmcLogoSrc }}" class="quotation-logo" alt="DMC Logo" />
                    @endif
                </td>
                <td style="text-align: center; vertical-align: top;">
                    <div class="quotation-title">QUOTATION</div>
                </td>
                <td class="quotation-logo-cell"></td>
                
            </tr>
        </table>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
            <tr>
                <td style="width: 50%; vertical-align: top; padding-right: 8px;">
                    <div class="quotation-dmc-details">{{ $dmcLines }}</div>
                </td>
                <td style="width: 30%; vertical-align: top; padding-left: 2px;">
                    <div class="top-lines">
                        <div class="top-line"><span class="bold">LEAD GUEST NAME:</span> {{ $leadGuestName }}</div>
                        <div class="top-line"><span class="bold">No. of Pax:</span> {{ $paxText }}</div>
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

                    <div class="section-label">Inclusions:</div>
                    @if(!empty($hotelOptions) && is_array($hotelOptions))
                        <ul class="inclusion-list">
                            @foreach($hotelOptions as $h)
                                @php
                                    $hotelName = $h['hotel_name'] ?? 'Hotel';
                                    $hotelKey = strtolower(trim((string)$hotelName));
                                    $roomCategoryName = $h['room_categories'][0]['name'] ?? ($h['hotel_category'] ?? 'Room');
                                    $hotelSingle = $h['first_total']['single'] ?? 0;
                                    $hotelDouble = $h['first_total']['double'] ?? 0;
                                @endphp
                                @php
                                    $skipThisHotel = isset($suppHotelCounts[$hotelKey]) && $suppHotelCounts[$hotelKey] > 0;
                                    if ($skipThisHotel) {
                                        $suppHotelCounts[$hotelKey]--;
                                    }
                                @endphp
                                @if(!$skipThisHotel)
                                    <li class="inclusion">
                                        {{ strtoupper($hotelName) }}-{{ strtoupper($roomCategoryName) }}
                                        <span class="subtle"> ({{ 'Single: ' . $formatMoney($hotelSingle) . ' / ' . 'Double: ' . $formatMoney($hotelDouble) }})</span>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @else
                        <div class="inclusion">No hotel options available</div>
                    @endif

                    <div class="money-line">
                        <div class="inclusion"><span class="bold">Date:</span> {{ $inclusionDateRange }}</div>
                    </div>
                </td>

                <td class="quotation-col">
                    <div class="panel-title">Other services cost for entire package</div>

                    <div class="money-line">
                        <div class="inclusion">
                            <span class="bold">
                                {{ $currencyLabel === 'INR' ? 'INR' : $selectedCurrency }} {{ $formatAmount($otherTotalForOccupancy) }}
                            </span>
                            per person
                            @if($occupancyKey === 'double') <span class="subtle">(Twin sharing)</span> @endif
                        </div>
                    </div>

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
                    @endif

                    @if(empty($bookedAttractions) && empty($bookedRestaurants) && empty($bookedArrivals) && empty($bookedDepartures) && empty($bookedLocalTransfers))
                        <div class="inclusion">No other services booked</div>
                    @endif

                    <div class="money-line">
                        <div class="inclusion"><span class="bold">Date:</span> {{ $inclusionDateRange }}</div>
                    </div>
                </td>
            </tr>
        </table>

        @if(!empty($supplements) && is_array($supplements))
            <div style="margin-top: 10px;">
                <div class="panel-title">Supplements</div>
                <table style="width: 100%; border-collapse: collapse; border: 2px solid #000; table-layout: fixed;">
                    <thead>
                        <tr>
                            <th style="border: 1px solid #000; padding: 6px; background: #f3f3f3; text-align: left; width: 60%;">Supplement</th>
                            <th style="border: 1px solid #000; padding: 6px; background: #f3f3f3; text-align: center; width: 20%;">Single</th>
                            <th style="border: 1px solid #000; padding: 6px; background: #f3f3f3; text-align: center; width: 20%;">Double</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($supplements as $s)
                            @php
                                $suppType = $s['type'] ?? '';
                                $suppName = $s['name'] ?? ($suppType ?: 'Supplement');
                                $suppSingle = $s['single'] ?? 0;
                                $suppDouble = $s['double'] ?? 0;
                            @endphp
                            <tr>
                                <td style="border: 1px solid #000; padding: 6px; vertical-align: top;">
                                    {{ strtoupper((string)$suppType) ?: 'SUPPLEMENT' }}: {{ (string)$suppName }}
                                </td>
                                <td style="border: 1px solid #000; padding: 6px; text-align: center;">
                                    {{ $formatMoney($suppSingle) }}
                                </td>
                                <td style="border: 1px solid #000; padding: 6px; text-align: center;">
                                    {{ $formatMoney($suppDouble) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>
</body>
</html>

