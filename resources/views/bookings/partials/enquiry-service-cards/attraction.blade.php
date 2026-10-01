@php
    $attractionName = $booking['AttractionName'] ?? 'Attraction Booking';
    $ticketName = $booking['ticketName'] ?? 'Standard Ticket';
    $adults = (int) ($booking['adultCount'] ?? 0);
    $children = (int) ($booking['childCount'] ?? 0);
    $seniors = (int) ($booking['seniorCount'] ?? 0);
    $infants = (int) ($booking['infantQty'] ?? $booking['infants'] ?? 0);
    $guests = $adults + $children + $seniors + $infants;
    $countryLabel = $orderCountry !== '' ? $orderCountry : trim((string) ($booking['country'] ?? ''));
    if ($countryLabel === '') {
        $countryLabel = 'N/A';
    }
    try {
        $visitDate = !empty($booking['bookingDate']) ? \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') : 'N/A';
    } catch (\Throwable $e) {
        $visitDate = (string) ($booking['bookingDate'] ?? 'N/A');
    }
    $td = is_array($booking['ticket_details'] ?? null) ? $booking['ticket_details'] : [];
    $tf = (isset($booking['transfer_options']) && is_array($booking['transfer_options']))
        ? $booking['transfer_options']
        : ((isset($booking['transferOptions']) && is_array($booking['transferOptions'])) ? $booking['transferOptions'] : []);
    $go = (isset($booking['guide_options']) && is_array($booking['guide_options']))
        ? $booking['guide_options']
        : ((isset($booking['guideOptions']) && is_array($booking['guideOptions'])) ? $booking['guideOptions'] : []);
    $isPro = (int) ($tour->is_pro ?? 0) === 1;

    // Ticket unit sell / cost (Pro stores both; legacy uses adult_price as sell)
    $adultSell = (float) ($td['adult_sell'] ?? $td['adult_price'] ?? $booking['adultSell'] ?? 0);
    $adultCost = (float) ($td['adult_cost'] ?? $booking['adultCost'] ?? 0);
    $childSell = (float) ($td['child_sell'] ?? $td['child_price'] ?? $booking['childSell'] ?? 0);
    $childCost = (float) ($td['child_cost'] ?? $booking['childCost'] ?? 0);
    $infantSell = (float) ($td['infant_sell'] ?? $booking['infantSell'] ?? 0);
    $infantCost = (float) ($td['infant_cost'] ?? $booking['infantCost'] ?? 0);
    $seniorSell = (float) ($td['senior_price'] ?? 0);

    $ticketPrice = (float) ($booking['totalPrice'] ?? $booking['sell'] ?? 0);
    if ($ticketPrice <= 0) {
        $ticketPrice = ($adultSell * $adults) + ($childSell * $children) + ($infantSell * $infants) + ($seniorSell * $seniors);
    }
    $ticketCostTotal = (float) ($booking['cost'] ?? 0);
    if ($isPro && $ticketCostTotal <= 0) {
        $ticketCostTotal = ($adultCost * $adults) + ($childCost * $children) + ($infantCost * $infants);
    }

    // Transfer: Pro prefers sell (totalPrice / sell / vehicles lineSell); also keep cost
    $transferSell = 0.0;
    $transferCost = 0.0;
    $vehList = (isset($tf['vehicles']) && is_array($tf['vehicles'])) ? $tf['vehicles'] : [];
    if (!empty($vehList)) {
        foreach ($vehList as $v) {
            if (!is_array($v)) {
                continue;
            }
            $transferSell += (float) ($v['lineSell'] ?? $v['line_sell'] ?? $v['totalPrice'] ?? $v['sell'] ?? 0);
            $transferCost += (float) ($v['lineCost'] ?? $v['line_cost'] ?? $v['cost'] ?? 0);
        }
    }
    if ($transferSell <= 0) {
        $transferSell = $isPro
            ? (float) ($tf['totalPrice'] ?? $tf['sell'] ?? $tf['lineSell'] ?? 0)
            : (float) ($tf['cost'] ?? $tf['totalPrice'] ?? $tf['sell'] ?? 0);
    }
    if ($transferCost <= 0) {
        $transferCost = (float) ($tf['cost'] ?? $tf['lineCost'] ?? 0);
    }
    // Non-pro display total stays on cost path when no sell stored
    $transferPrice = $isPro ? $transferSell : (float) ($tf['cost'] ?? $tf['totalPrice'] ?? $transferSell);

    $guidePrice = (float) ($go['total_price'] ?? $go['cost'] ?? $go['Cost'] ?? $go['sell'] ?? $go['Sell'] ?? 0);
    $grandTotal = $ticketPrice + $transferPrice + $guidePrice;
@endphp

<div class="svc-panel">
    <div class="svc-panel-head">
        <div class="svc-panel-head-main">
            <div class="svc-thumb svc-thumb-fallback"><i class="ri-building-2-line"></i></div>
            <div>
                <p class="svc-title">{{ $attractionName }}</p>
                <p class="svc-subtitle">{{ $ticketName }} • Enquiry {{ $index + 1 }}</p>
            </div>
        </div>
        <div class="svc-price">{{ $currency }} {{ number_format($grandTotal, 2) }}</div>
    </div>

    <div class="svc-section mb-0" style="border:0;border-radius:0;">
        <p class="svc-section-title">Visit Schedule</p>
        <div class="svc-dl">
            <div class="svc-dl-row">
                <span class="svc-dl-label">Visit Date</span>
                <span class="svc-dl-value">{{ $visitDate }}</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">Visit Time</span>
                <span class="svc-dl-value">{{ $booking['visitTime'] ?? 'Full Day' }}</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">Selection</span>
                <span class="svc-dl-value">{{ ucfirst($booking['Selection'] ?? 'Standard') }}</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">Country</span>
                <span class="svc-dl-value">{{ $countryLabel }}</span>
            </div>
        </div>
    </div>

    <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
        <p class="svc-section-title">Guest Information</p>
        <div class="svc-guest-grid" style="grid-template-columns:1fr 1fr 1fr{{ $infants > 0 ? ' 1fr' : '' }};">
            <div class="svc-guest-box"><div class="num">{{ $adults }}</div><div class="lbl">Adults</div></div>
            <div class="svc-guest-box"><div class="num">{{ $children }}</div><div class="lbl">Children</div></div>
            <div class="svc-guest-box"><div class="num">{{ $seniors }}</div><div class="lbl">Seniors</div></div>
            @if($infants > 0)
            <div class="svc-guest-box"><div class="num">{{ $infants }}</div><div class="lbl">Infants</div></div>
            @endif
        </div>
        <div class="svc-total-bar">Total: {{ $guests }} Guest{{ $guests === 1 ? '' : 's' }}</div>
    </div>

    <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
        <p class="svc-section-title">Attraction Details</p>
        <div class="svc-dl">
            <div class="svc-dl-row">
                <span class="svc-dl-label">Ticket ID</span>
                <span class="svc-dl-value">{{ $booking['ticketId'] ?? 'N/A' }}</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">NRI Status</span>
                <span class="svc-dl-value">{{ ucfirst($booking['nri'] ?? 'N/A') }}</span>
            </div>
        </div>
    </div>

    @if(!empty($td) || $adultSell > 0 || $adultCost > 0)
    <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
        <p class="svc-section-title">Ticket &amp; Pricing</p>
        <div class="svc-dl">
            <div class="svc-dl-row">
                <span class="svc-dl-label">Adult Sell</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($adultSell, 2) }}</span>
            </div>
            @if($isPro)
            <div class="svc-dl-row">
                <span class="svc-dl-label">Adult Cost</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($adultCost, 2) }}</span>
            </div>
            @endif
            <div class="svc-dl-row">
                <span class="svc-dl-label">Child Sell</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($childSell, 2) }}</span>
            </div>
            @if($isPro)
            <div class="svc-dl-row">
                <span class="svc-dl-label">Child Cost</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($childCost, 2) }}</span>
            </div>
            @endif
            @if($seniorSell > 0 || $seniors > 0)
            <div class="svc-dl-row">
                <span class="svc-dl-label">Senior</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($seniorSell, 2) }}</span>
            </div>
            @endif
            @if($isPro && ($infantSell > 0 || $infantCost > 0 || $infants > 0))
            <div class="svc-dl-row">
                <span class="svc-dl-label">Infant Sell</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($infantSell, 2) }}</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">Infant Cost</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($infantCost, 2) }}</span>
            </div>
            @endif
            @if($isPro && $ticketCostTotal > 0)
            <div class="svc-dl-row">
                <span class="svc-dl-label">Ticket Cost</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($ticketCostTotal, 2) }}</span>
            </div>
            @endif
            <div class="svc-dl-row">
                <span class="svc-dl-label">Ticket Total</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($ticketPrice, 2) }}</span>
            </div>
            @if(!empty($td['description']))
            <div class="svc-dl-row full">
                <span class="svc-dl-label">Ticket Info</span>
                <span class="svc-dl-value">{!! $td['description'] !!}</span>
            </div>
            @endif
        </div>
    </div>
    @endif

    @include('bookings.partials.enquiry-service-cards.transfer-guide-sections', [
        'booking' => $booking,
        'currency' => $currency,
        'tour' => $tour ?? null,
    ])

    @if($transferPrice > 0 || $guidePrice > 0 || ($isPro && $transferCost > 0))
    <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
        <p class="svc-section-title">Price Summary</p>
        <div class="svc-dl">
            <div class="svc-dl-row">
                <span class="svc-dl-label">Tickets</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($ticketPrice, 2) }}</span>
            </div>
            @if($isPro && $transferCost > 0)
            <div class="svc-dl-row">
                <span class="svc-dl-label">Transfer Cost</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($transferCost, 2) }}</span>
            </div>
            @endif
            @if($transferPrice > 0)
            <div class="svc-dl-row">
                <span class="svc-dl-label">{{ $isPro ? 'Transfer Sell' : 'Transfer' }}</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($transferPrice, 2) }}</span>
            </div>
            @endif
            @if($guidePrice > 0)
            <div class="svc-dl-row">
                <span class="svc-dl-label">Guide</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($guidePrice, 2) }}</span>
            </div>
            @endif
            <div class="svc-dl-row full">
                <span class="svc-dl-label">Grand Total</span>
                <span class="svc-dl-value svc-amount" style="color:var(--svc-accent);">{{ $currency }} {{ number_format($grandTotal, 2) }}</span>
            </div>
        </div>
    </div>
    @endif

    @if(!empty($booking['specialRequests']))
    <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
        <p class="svc-section-title">Special Requests</p>
        <div class="svc-dl">
            <div class="svc-dl-row full"><span class="svc-dl-value">{{ $booking['specialRequests'] }}</span></div>
        </div>
    </div>
    @endif
</div>
