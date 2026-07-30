@php
    $attractionName = $booking['AttractionName'] ?? 'Attraction Booking';
    $ticketName = $booking['ticketName'] ?? 'Standard Ticket';
    $totalPrice = (float) ($booking['totalPrice'] ?? 0);
    $adults = (int) ($booking['adultCount'] ?? 0);
    $children = (int) ($booking['childCount'] ?? 0);
    $seniors = (int) ($booking['seniorCount'] ?? 0);
    $guests = $adults + $children + $seniors;
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
    $tf = (isset($booking['transfer_options']) && is_array($booking['transfer_options'])) ? $booking['transfer_options'] : [];
    $hasTransfer = !empty($tf['transfer_required']) && in_array($tf['transfer_required'], [true, 'true', 'Yes', 1, '1'], true);
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
        <div class="svc-price">{{ $currency }} {{ number_format($totalPrice, 2) }}</div>
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
        <div class="svc-guest-grid" style="grid-template-columns:1fr 1fr 1fr;">
            <div class="svc-guest-box"><div class="num">{{ $adults }}</div><div class="lbl">Adults</div></div>
            <div class="svc-guest-box"><div class="num">{{ $children }}</div><div class="lbl">Children</div></div>
            <div class="svc-guest-box"><div class="num">{{ $seniors }}</div><div class="lbl">Seniors</div></div>
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

    @if(!empty($td))
    <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
        <p class="svc-section-title">Ticket &amp; Pricing</p>
        <div class="svc-dl">
            <div class="svc-dl-row">
                <span class="svc-dl-label">Adult</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format((float) ($td['adult_price'] ?? 0), 2) }}</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">Child</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format((float) ($td['child_price'] ?? 0), 2) }}</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">Senior</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format((float) ($td['senior_price'] ?? 0), 2) }}</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">Total</span>
                <span class="svc-dl-value svc-amount" style="color:var(--svc-accent);">{{ $currency }} {{ number_format($totalPrice, 2) }}</span>
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

    @if($hasTransfer)
    <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
        <p class="svc-section-title">Transfer Details</p>
        <div class="svc-dl">
            <div class="svc-dl-row">
                <span class="svc-dl-label">Type</span>
                <span class="svc-dl-value">{{ $tf['type'] ?? 'N/A' }}</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">Way</span>
                <span class="svc-dl-value">{{ $tf['way'] ?? 'N/A' }}</span>
            </div>
            @if(!empty($tf['pickup_location_name']))
            <div class="svc-dl-row full">
                <span class="svc-dl-label">Pickup</span>
                <span class="svc-dl-value">{{ $tf['pickup_location_name'] }}</span>
            </div>
            @endif
            @if(!empty($tf['vehicle_details']['vehicle_name']) || !empty($tf['vehicle_id']))
            <div class="svc-dl-row full">
                <span class="svc-dl-label">Vehicle</span>
                <span class="svc-dl-value">{{ $tf['vehicle_details']['vehicle_name'] ?? $tf['vehicle_id'] }}</span>
            </div>
            @endif
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
