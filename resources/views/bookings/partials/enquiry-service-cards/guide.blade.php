@php
    $guideName = $booking['guide_name'] ?? 'Guide Booking';
    $hours = $booking['hours'] ?? 'N/A';
    $totalPrice = (float) ($booking['totalPrice'] ?? 0);
    $basePrice = (float) ($booking['basePrice'] ?? 0);
    $surcharge = (float) ($booking['surcharge'] ?? 0);
    $adults = (int) ($booking['adults'] ?? 0);
    $children = (int) ($booking['children'] ?? 0);
    $guests = $adults + $children;
    $countryLabel = $orderCountry !== '' ? $orderCountry : trim((string) ($booking['country'] ?? ''));
    if ($countryLabel === '') {
        $countryLabel = 'N/A';
    }
    try {
        $serviceDate = !empty($booking['bookingDate'])
            ? \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y')
            : 'N/A';
    } catch (\Throwable $e) {
        $serviceDate = (string) ($booking['bookingDate'] ?? 'N/A');
    }
    $guideImage = $booking['image'] ?? null;
@endphp

<div class="svc-panel">
    <div class="svc-panel-head">
        <div class="svc-panel-head-main">
            @if(!empty($guideImage))
                <img src="{{ $guideImage }}" alt="" class="svc-thumb" onerror="this.style.display='none'">
            @else
                <div class="svc-thumb svc-thumb-fallback"><i class="ri-user-voice-line"></i></div>
            @endif
            <div>
                <p class="svc-title">{{ $guideName }}</p>
                <p class="svc-subtitle">Guide Service • {{ $hours }}H</p>
            </div>
        </div>
        <div class="svc-price">{{ $currency }} {{ number_format($totalPrice, 2) }}</div>
    </div>

    <div class="svc-section mb-0" style="border:0;border-radius:0;">
        <p class="svc-section-title">Guide Information</p>
        <div class="svc-dl">
            <div class="svc-dl-row">
                <span class="svc-dl-label">Guide Name</span>
                <span class="svc-dl-value">{{ $guideName }}</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">Country</span>
                <span class="svc-dl-value">{{ $countryLabel }}</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">Base Price</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($basePrice, 2) }}</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">Surcharge</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($surcharge, 2) }}</span>
            </div>
        </div>
    </div>

    <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
        <p class="svc-section-title">Service Schedule</p>
        <div class="svc-dl">
            <div class="svc-dl-row">
                <span class="svc-dl-label">Date</span>
                <span class="svc-dl-value">{{ $serviceDate }}</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">Time</span>
                <span class="svc-dl-value">{{ $booking['entrytime'] ?? 'TBC' }}</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">Duration</span>
                <span class="svc-dl-value">{{ $hours }}H</span>
            </div>
            @if(!empty($booking['Night_Start_Time']) && !empty($booking['Night_End_Time']))
            <div class="svc-dl-row">
                <span class="svc-dl-label">Night Service</span>
                <span class="svc-dl-value">{{ $booking['Night_Start_Time'] }} – {{ $booking['Night_End_Time'] }}</span>
            </div>
            @endif
        </div>
    </div>

    <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
        <p class="svc-section-title">Group Information</p>
        <div class="svc-guest-grid">
            <div class="svc-guest-box"><div class="num">{{ $adults }}</div><div class="lbl">Adults</div></div>
            <div class="svc-guest-box"><div class="num">{{ $children }}</div><div class="lbl">Children</div></div>
        </div>
        <div class="svc-total-bar">Total: {{ $guests }} Guest{{ $guests === 1 ? '' : 's' }}</div>
    </div>

    <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
        <p class="svc-section-title">Pricing Breakdown</p>
        <div class="svc-dl">
            <div class="svc-dl-row">
                <span class="svc-dl-label">Base Price</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($basePrice, 2) }}</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">Surcharge</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($surcharge, 2) }}</span>
            </div>
            <div class="svc-dl-row full">
                <span class="svc-dl-label">Total Amount</span>
                <span class="svc-dl-value svc-amount" style="color:var(--svc-accent);">{{ $currency }} {{ number_format($totalPrice, 2) }}</span>
            </div>
        </div>
    </div>
</div>
