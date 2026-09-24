{{-- Compact service line for Packaged / Acco+Service quotations (PDF-safe) --}}
@php
    $card = is_array($card ?? null) ? $card : [];
    $kind = strtolower((string) ($kind ?? 'service'));
    $showPrices = !empty($showPrices);
    $currencyCode = strtoupper((string) ($currencyCode ?? ''));
    $moneyFn = $moneyFn ?? null;
    $label = trim((string) ($label ?? 'Service'));
    $plain = (string) ($plain ?? '');

    $chipValue = function ($cardIn, $chipLabel) {
        foreach (($cardIn['chips'] ?? []) as $chip) {
            if (!is_array($chip)) {
                continue;
            }
            if (strcasecmp((string) ($chip['label'] ?? ''), $chipLabel) === 0) {
                $v = trim((string) ($chip['value'] ?? ''));
                if ($v !== '') {
                    return $v;
                }
            }
        }
        return '';
    };

    $fmtMoney = function ($amount) use ($moneyFn, $currencyCode) {
        $amount = (float) $amount;
        if ($amount <= 0) {
            return '';
        }
        if (is_callable($moneyFn)) {
            return $moneyFn($amount, $currencyCode !== '' ? $currencyCode : null);
        }
        $cur = $currencyCode !== '' ? $currencyCode : '';
        return trim($cur . ' ' . number_format(ceil($amount), 0, '.', ','));
    };

    $renderTransfer = function ($tr) {
        if (!is_array($tr)) {
            return '';
        }
        $has = !empty($tr['vehicle_name']) || !empty($tr['type']) || !empty($tr['pickup_location_name'])
            || !empty($tr['pickup_time']) || !empty($tr['vehicle_type'])
            || (isset($tr['cost']) && is_numeric($tr['cost']) && (float) $tr['cost'] > 0);
        if (!$has) {
            return '';
        }
        $vehBits = array_filter([
            $tr['vehicle_name'] ?? null,
            (isset($tr['vehicle_type'], $tr['seating_capacity']) && $tr['vehicle_type'] && $tr['seating_capacity'])
                ? ($tr['vehicle_type'] . ' / ' . $tr['seating_capacity'] . ' seats')
                : ($tr['vehicle_type'] ?? null),
        ]);
        $vehLine = implode(' — ', $vehBits);
        $transferMeta = array_filter([$tr['type'] ?? null, $tr['way'] ?? null]);
        $parts = [];
        if (!empty($transferMeta)) {
            $parts[] = implode(' · ', $transferMeta);
        }
        if ($vehLine !== '') {
            $parts[] = $vehLine;
        }
        $html = '<div class="svc-sub"><span class="bold">Vehicle:</span> ' . e(implode(' — ', $parts));
        if (!empty($tr['pickup_location_name']) || !empty($tr['pickup_time'])) {
            $html .= ' · ';
            if (!empty($tr['pickup_location_name'])) {
                $html .= '<span class="bold">Pickup:</span> ' . e($tr['pickup_location_name']);
            }
            if (!empty($tr['pickup_time'])) {
                if (!empty($tr['pickup_location_name'])) {
                    $html .= ' / ';
                }
                $html .= '<span class="bold">Time:</span> ' . e($tr['pickup_time']);
            }
        }
        $html .= '</div>';
        return $html;
    };

    $dateLabel = $chipValue($card, 'Date');
    $timeLabel = $chipValue($card, 'Time');
    if ($timeLabel === '' && !empty($card['time'])) {
        $timeLabel = (string) $card['time'];
    }
@endphp

@if ($kind === 'attraction')
    @php
        $ad = is_array($card['attraction'] ?? null) ? $card['attraction'] : [];
        $title = trim((string) ($card['title'] ?? 'Attraction'));
        $visitTime = trim((string) ($ad['visit_time'] ?? $timeLabel));
        $aAdults = (int) ($ad['adult_count'] ?? $ad['adultCount'] ?? 0);
        $aChildren = (int) ($ad['child_count'] ?? $ad['childCount'] ?? 0);
        $aAdultPrice = (float) ($ad['adult_price'] ?? ($ad['ticket_details']['adult_price'] ?? 0));
        $aChildPrice = (float) ($ad['child_price'] ?? ($ad['ticket_details']['child_price'] ?? 0));
        $ticketName = trim((string) ($ad['ticket_details']['ticket_name'] ?? $ad['ticket_details']['name'] ?? $card['notes'] ?? ''));
        $tr = is_array($ad['transfer'] ?? null) ? $ad['transfer'] : null;
        $gd = is_array($ad['guide'] ?? null) ? $ad['guide'] : null;
        $guideHours = is_array($gd) ? ($gd['package_hours'] ?? $gd['hours'] ?? null) : null;

        $inlineParts = [];
        $metaBits = array_filter([$dateLabel !== '' ? $dateLabel : null, $visitTime !== '' ? $visitTime : null]);
        if (!empty($metaBits)) {
            $inlineParts[] = implode(' · ', $metaBits);
        }
        if ($ticketName !== '') {
            $inlineParts[] = $ticketName;
        }
        $paxBits = [];
        if ($aAdults > 0 || ($showPrices && $aAdultPrice > 0)) {
            $paxBits[] = ($aAdults > 0 ? $aAdults . 'A' : 'Adult') . ($showPrices && $aAdultPrice > 0 ? ' ' . $fmtMoney($aAdultPrice) : '');
        }
        if ($aChildren > 0 || ($showPrices && $aChildPrice > 0)) {
            $paxBits[] = ($aChildren > 0 ? $aChildren . 'C' : 'Child') . ($showPrices && $aChildPrice > 0 ? ' ' . $fmtMoney($aChildPrice) : '');
        }
        if (!empty($paxBits)) {
            $inlineParts[] = implode(', ', $paxBits);
        }
        $inlineText = !empty($inlineParts) ? ' — ' . implode(' — ', $inlineParts) : '';

        $guideParts = [];
        if (is_array($gd)) {
            $guideParts = array_filter([$gd['guide_name'] ?? null, $gd['language'] ?? null]);
            if (!empty($gd['pickup_time'])) {
                $guideParts[] = 'Pickup: ' . $gd['pickup_time'];
            }
            if ($guideHours !== null && $guideHours !== '') {
                $guideParts[] = $guideHours . ' hrs';
            }
        }
    @endphp
    <li class="inclusion">
        <span class="bold">Attraction:</span> {{ $title }}@if($inlineText !== '')<span class="svc-inline">{{ $inlineText }}</span>@endif
        {!! $renderTransfer($tr) !!}
        @if (!empty($guideParts))
            <div class="svc-sub"><span class="bold">Guide:</span> {{ implode(' · ', $guideParts) }}</div>
        @endif
    </li>
@elseif ($kind === 'restaurant')
    @php
        $rs = is_array($card['restaurant'] ?? null) ? $card['restaurant'] : [];
        $title = trim((string) ($card['title'] ?? 'Restaurant'));
        $mealPlan = trim((string) ($rs['meal_plan'] ?? ''));
        $mealType = trim((string) ($rs['meal_type'] ?? ''));
        $visitTime = trim((string) ($rs['visit_time'] ?? $timeLabel));
        $rAdults = (int) ($rs['adult_count'] ?? $rs['adultCount'] ?? 0);
        $rChildren = (int) ($rs['child_count'] ?? $rs['childCount'] ?? 0);
        $rAdultPrice = (float) ($rs['adult_price'] ?? ($rs['meal_details']['adult_price'] ?? 0));
        $rChildPrice = (float) ($rs['child_price'] ?? ($rs['meal_details']['child_price'] ?? 0));
        $mealItems = is_array($rs['meal_items'] ?? null) ? $rs['meal_items'] : [];
        $tr = is_array($rs['transfer'] ?? null) ? $rs['transfer'] : null;
        $menuBits = [];
        foreach ($mealItems as $mi) {
            if (!is_array($mi)) {
                continue;
            }
            $n = trim((string) ($mi['item_name'] ?? $mi['name'] ?? ''));
            if ($n === '') {
                continue;
            }
            $q = (int) ($mi['quantity'] ?? 1);
            $menuBits[] = $q > 1 ? ($n . ' x' . $q) : $n;
        }
        $inlineParts = array_filter([
            $mealPlan !== '' ? $mealPlan : null,
            $mealType !== '' ? $mealType : null,
            $dateLabel !== '' ? $dateLabel : null,
            $visitTime !== '' ? $visitTime : null,
        ]);
        $paxBits = [];
        if ($rAdults > 0 || ($showPrices && $rAdultPrice > 0)) {
            $paxBits[] = ($rAdults > 0 ? $rAdults . 'A' : 'Adult') . ($showPrices && $rAdultPrice > 0 ? ' ' . $fmtMoney($rAdultPrice) : '');
        }
        if ($rChildren > 0 || ($showPrices && $rChildPrice > 0)) {
            $paxBits[] = ($rChildren > 0 ? $rChildren . 'C' : 'Child') . ($showPrices && $rChildPrice > 0 ? ' ' . $fmtMoney($rChildPrice) : '');
        }
        if (!empty($paxBits)) {
            $inlineParts[] = implode(', ', $paxBits);
        }
        $inlineText = !empty($inlineParts) ? ' — ' . implode(' · ', $inlineParts) : '';
    @endphp
    <li class="inclusion">
        <span class="bold">Restaurant:</span> {{ $title }}@if($inlineText !== '')<span class="svc-inline">{{ $inlineText }}</span>@endif
        @if (!empty($menuBits))
            <div class="svc-sub"><span class="bold">Menu:</span> {{ implode(', ', $menuBits) }}</div>
        @endif
        {!! $renderTransfer($tr) !!}
    </li>
@elseif ($kind === 'vehicle')
    @php
        $veh = is_array($card['vehicle'] ?? null) ? $card['vehicle'] : [];
        $entryPort = is_array($card['entry_port_flight'] ?? null) ? $card['entry_port_flight'] : [];
        $exitPort = is_array($card['exit_port_flight'] ?? null) ? $card['exit_port_flight'] : [];
        $title = trim((string) ($card['title'] ?? ''));
        $vehName = trim((string) ($veh['name'] ?? $veh['vehicle_name'] ?? ''));
        $vehType = trim((string) ($veh['vehicle_type_seater'] ?? $veh['vehicle_type'] ?? ''));
        $way = trim((string) ($veh['way'] ?? $veh['transfer_type'] ?? ''));
        $pickup = trim((string) ($veh['pickup'] ?? $chipValue($card, 'Pickup')));
        $dropoff = trim((string) ($veh['dropoff'] ?? $chipValue($card, 'Dropoff')));
        $hours = $veh['hours'] ?? null;

        // Arrival / departure time can live on chips, card.time, or port flight detail fields
        if ($timeLabel === '') {
            $timeLabel = trim((string) (
                $card['time']
                ?? ($entryPort['destination_arrival_time'] ?? null)
                ?? ($exitPort['origin_departure_time'] ?? null)
                ?? ($veh['pickup_time'] ?? null)
                ?? ''
            ));
        }
        // Fallback: pull "(10:30)" from plain text built for arrival/departure
        if ($timeLabel === '' && $plain !== '' && preg_match('/\(([^)]*\d{1,2}:\d{2}[^)]*)\)/', $plain, $tm)) {
            $timeLabel = trim($tm[1]);
        }

        $vehLineParts = array_filter([$vehName, $vehType, $way]);
        $inlineParts = [];
        if ($dateLabel !== '') {
            $inlineParts[] = 'Date: ' . $dateLabel;
        }
        if ($timeLabel !== '') {
            $inlineParts[] = 'Time: ' . $timeLabel;
        }
        if (!empty($vehLineParts) || ($hours !== null && $hours !== '')) {
            $vehTxt = implode(' · ', $vehLineParts);
            if ($hours !== null && $hours !== '') {
                $vehTxt = trim($vehTxt . ' (' . $hours . ' hrs)');
            }
            if ($vehTxt !== '') {
                $inlineParts[] = $vehTxt;
            }
        }
        if ($pickup !== '' || $dropoff !== '') {
            $route = [];
            if ($pickup !== '') {
                $route[] = 'Pickup: ' . $pickup;
            }
            if ($dropoff !== '') {
                $route[] = 'Drop: ' . $dropoff;
            }
            $inlineParts[] = implode(' / ', $route);
        }
        $inlineText = !empty($inlineParts) ? ' — ' . implode(' — ', $inlineParts) : '';

        // Prefer a clean location title; plain often already embeds time which we show separately
        $displayLabel = $label !== '' ? $label : 'Transfer';
        $displayTitle = $title;
        if ($displayTitle === '') {
            if ($pickup !== '') {
                $displayTitle = $pickup;
            } elseif ($dropoff !== '') {
                $displayTitle = $dropoff;
            } elseif ($plain !== '') {
                // Strip trailing " (time) - type" noise when we already show time/type inline
                $displayTitle = preg_replace('/\s*\([^)]*\d{1,2}:\d{2}[^)]*\)\s*/', ' ', $plain);
                $displayTitle = trim(preg_replace('/\s+-\s+[^-]+$/', '', (string) $displayTitle));
                if ($displayTitle === '') {
                    $displayTitle = $plain;
                }
            } else {
                $displayTitle = 'Transfer';
            }
        }
    @endphp
    <li class="inclusion">
        <span class="bold">{{ $displayLabel }}:</span> {{ $displayTitle }}@if($inlineText !== '')<span class="svc-inline">{{ $inlineText }}</span>@endif
    </li>
@else
    @php
        $inlineParts = array_filter([$dateLabel !== '' ? $dateLabel : null, $timeLabel !== '' ? $timeLabel : null]);
        $inlineText = !empty($inlineParts) ? ' — ' . implode(' · ', $inlineParts) : '';
        $displayLabel = $label !== '' ? $label : 'Service';
        $displayTitle = $plain !== '' ? $plain : (string) ($card['title'] ?? '');
    @endphp
    <li class="inclusion">
        <span class="bold">{{ $displayLabel }}:</span> {{ $displayTitle }}@if($inlineText !== '')<span class="svc-inline">{{ $inlineText }}</span>@endif
    </li>
@endif
