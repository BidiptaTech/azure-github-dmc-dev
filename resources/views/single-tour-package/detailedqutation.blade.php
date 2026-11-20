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
        .country-stack-section {
            margin-top: 8px;
        }
        .country-stack-section:first-of-type {
            margin-top: 2px;
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
    {{-- Guest, inclusion, identity, and pricing view-model is prepared by QuotationDataBuilder --}}

    <div class="page">


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

        {{-- Country Hotels + Other Services (inclusions) — stacked full-width per city --}}
        @if(!empty($allCountryKeys))
            @foreach($allCountryKeys as $bucketKey)
                @php
                    $countryName = $countryMeta[$bucketKey]['country'] ?? $bucketKey;
                    $countryCity = $countryMeta[$bucketKey]['city'] ?? '';
                    $countryCurrency = $countryMeta[$bucketKey]['currency'] ?? strtoupper((string)$baseCurrency);
                    $countryIsReturn = !empty($countryMeta[$bucketKey]['is_return']);
                    $countryDateRange = trim((string) ($countryMeta[$bucketKey]['date_range'] ?? ''));
                    if ($countryDateRange === '') {
                        $countryDateRange = $resolveCountryDateRange($countryCity, $countryName);
                    }
                    $countryBoxTitle = isset($formatLocationTitle) && is_callable($formatLocationTitle)
                        ? $formatLocationTitle($countryCity, $countryName, $countryCurrency, $countryIsReturn, $countryDateRange)
                        : (trim((string) $countryCity) !== ''
                            ? trim((string) $countryCity) . ' (' . $countryName . ') (' . $countryCurrency . ')' . ($countryIsReturn ? ' · Return' : '') . ($countryDateRange !== '' ? ' · ' . $countryDateRange : '')
                            : $countryName . ' (' . $countryCurrency . ')' . ($countryIsReturn ? ' · Return' : '') . ($countryDateRange !== '' ? ' · ' . $countryDateRange : ''));
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
                    @if($showCountryPricing)
                    <div class="country-box-body">

                        <div class="country-stack-section">
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
                                </div>

                        <div class="country-stack-section">
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
                        </div>
                    </div>
                    @endif
                </div>
            @endforeach
        @else
            <div class="country-box">
                <div class="country-box-title">Services</div>
                <div class="country-box-body">No hotel or other services booked</div>
            </div>
        @endif

{{-- ========== SKETCH LAYOUT: Country → Overall → Supplement → Total ========== --}}


        {{-- 1) Package Price by Country — hotel + other combined (per pax) --}}
        @if(!empty($countrySharingRows))
        <div class="overall-price-box">
            <div class="panel-title">Packaged Price by Country</div>
            @foreach($countrySharingRows as $share)
                <div style="border-top: 1px solid #222;">
                    <div class="country-box-title">{{ $share['title'] ?? '' }}</div>
                    <div class="country-box-body">
                        <div class="country-stack-section">
                            <div class="country-col-label">Hotel-Accommodation &amp; Other Services (per pax)</div>
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
                                        <td>{!! $share['cell_single'] ?? '--' !!}</td>
                                        <td>{!! $share['cell_double'] ?? '--' !!}</td>
                                        <td>{!! $share['cell_triple'] ?? '--' !!}</td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </div>
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
                            {{-- skipped — city totals roll into TOTAL COST --}}
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
