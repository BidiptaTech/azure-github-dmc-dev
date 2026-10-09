@php
    $hd = is_array($booking['hotelDetails'] ?? null) ? $booking['hotelDetails'] : [];
    $hotelName = $hd['hotel_name'] ?? 'Hotel Booking';
    $hotelImage = $hd['image'] ?? null;
    $location = $hd['location'] ?? 'Location not specified';
    $countryLabel = $orderCountry !== '' ? $orderCountry : trim((string) ($booking['country'] ?? ($hd['country'] ?? '')));
    if ($countryLabel === '') {
        $countryLabel = 'N/A';
    }

    $hotelPrice = (float) ($booking['price'] ?? $booking['totalPrice'] ?? 0);
    $tf = (isset($booking['transfer_options']) && is_array($booking['transfer_options']))
        ? $booking['transfer_options']
        : ((isset($booking['transferOptions']) && is_array($booking['transferOptions'])) ? $booking['transferOptions'] : []);
    $go = (isset($booking['guide_options']) && is_array($booking['guide_options']))
        ? $booking['guide_options']
        : ((isset($booking['guideOptions']) && is_array($booking['guideOptions'])) ? $booking['guideOptions'] : []);
    $isPro = (int) ($tour->is_pro ?? 0) === 1;
    $transferPrice = $isPro
        ? (float) ($tf['totalPrice'] ?? $tf['cost'] ?? 0)
        : (float) ($tf['cost'] ?? $tf['totalPrice'] ?? 0);
    $guidePrice = (float) ($go['total_price'] ?? $go['cost'] ?? $go['Cost'] ?? $go['sell'] ?? $go['Sell'] ?? 0);
    $grandTotal = $hotelPrice + $transferPrice + $guidePrice;

    $checkIn = 'N/A';
    $checkOut = 'N/A';
    $nights = null;
    if (!empty($booking['nights'])) {
        $nights = (int) $booking['nights'];
    }
    if (isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 0) {
        try {
            $checkIn = \Carbon\Carbon::parse($booking['bookingDate'][0])->format('M d, Y');
            if (count($booking['bookingDate']) > 1) {
                $ci = \Carbon\Carbon::parse($booking['bookingDate'][0]);
                $co = \Carbon\Carbon::parse(end($booking['bookingDate']));
                $checkOut = $co->format('M d, Y');
                if ($nights === null || $nights <= 0) {
                    $nights = $ci->diffInDays($co);
                }
            }
        } catch (\Throwable $e) {
            $checkIn = (string) ($booking['bookingDate'][0] ?? 'N/A');
        }
    }

    $addressParts = array_filter([
        $booking['address1'] ?? null,
        $booking['address2'] ?? null,
        trim(($booking['state'] ?? '') . ' ' . ($booking['zip'] ?? '')),
    ]);
    $addressLabel = count($addressParts) ? implode(', ', $addressParts) : 'Address not provided';
    $mealPlan = \App\Helpers\CommonHelper::resolveHotelMealPlanLabel($booking['rooms'] ?? []);
@endphp

<div class="svc-panel">
    <div class="svc-panel-head">
        <div class="svc-panel-head-main">
            @if(!empty($hotelImage))
                <img src="{{ $hotelImage }}" alt="" class="svc-thumb" onerror="this.style.display='none'">
            @else
                <div class="svc-thumb svc-thumb-fallback"><i class="ri-hotel-line"></i></div>
            @endif
            <div>
                <p class="svc-title">{{ $hotelName }}</p>
                <div class="d-flex align-items-center flex-wrap gap-1">
                    <p class="svc-subtitle mb-0">Enquiry {{ $index + 1 }} • {{ ucfirst($booking['bookingType'] ?? 'Standard') }}</p>
                    @include('bookings.partials.order-type-badge', ['orderType' => $orderType ?? null, 'size' => 'sm'])
                </div>
            </div>
        </div>
        <div class="svc-price">{{ $currency }} {{ number_format($grandTotal, 2) }}</div>
    </div>

    <div class="svc-section mb-0" style="border:0;border-radius:0;">
        <p class="svc-section-title">Customer</p>
        <div class="svc-dl">
            <div class="svc-dl-row">
                <span class="svc-dl-label">Name</span>
                <span class="svc-dl-value">{{ $booking['fullName'] ?? 'N/A' }}</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">Email</span>
                <span class="svc-dl-value">{{ $booking['email'] ?? 'N/A' }}</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">Phone</span>
                <span class="svc-dl-value">{{ trim(($booking['countryCode'] ?? '') . ' ' . ($booking['phone'] ?? 'N/A')) }}</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">Address</span>
                <span class="svc-dl-value">{{ $addressLabel }}</span>
            </div>
        </div>
    </div>

    <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
        <p class="svc-section-title">Stay Schedule</p>
        <div class="svc-dl">
            <div class="svc-dl-row">
                <span class="svc-dl-label">Check-in</span>
                <span class="svc-dl-value">{{ $checkIn }}@if(!empty($hd['checkInTime'])) <span class="text-muted">({{ $hd['checkInTime'] }})</span>@endif</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">Check-out</span>
                <span class="svc-dl-value">{{ $checkOut }}@if(!empty($hd['checkOutTime'])) <span class="text-muted">({{ $hd['checkOutTime'] }})</span>@endif</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">Nights</span>
                <span class="svc-dl-value">{{ $nights !== null ? ($nights . ' Night' . ($nights === 1 ? '' : 's')) : 'Duration TBD' }}</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">Meal Plan</span>
                <span class="svc-dl-value svc-meal-plan">{{ $mealPlan }}</span>
            </div>
        </div>
    </div>

    <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
        <p class="svc-section-title">Hotel &amp; Location</p>
        <div class="svc-dl">
            <div class="svc-dl-row">
                <span class="svc-dl-label">Location</span>
                <span class="svc-dl-value">{{ $location }}</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">Country</span>
                <span class="svc-dl-value">{{ $countryLabel }}</span>
            </div>
            @if(!empty($hd['cancellation_charge']))
            <div class="svc-dl-row full">
                <span class="svc-dl-label">Cancellation</span>
                <span class="svc-dl-value">{{ $hd['cancellation_charge'] }}</span>
            </div>
            @endif
            <div class="svc-dl-row">
                <span class="svc-dl-label">Hotel Total</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($hotelPrice, 2) }}</span>
            </div>
        </div>
    </div>

    @include('bookings.partials.hotel-booking-modal-sections', ['booking' => $booking, 'currency' => $currency, 'professional' => true])

    @include('bookings.partials.enquiry-service-cards.transfer-guide-sections', [
        'booking' => $booking,
        'currency' => $currency,
        'tour' => $tour ?? null,
    ])

    @if($transferPrice > 0 || $guidePrice > 0)
    <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
        <p class="svc-section-title">Price Summary</p>
        <div class="svc-dl">
            <div class="svc-dl-row">
                <span class="svc-dl-label">Hotel</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($hotelPrice, 2) }}</span>
            </div>
            @if($transferPrice > 0)
            <div class="svc-dl-row">
                <span class="svc-dl-label">Transfer</span>
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
            <div class="svc-dl-row full">
                <span class="svc-dl-value">{{ $booking['specialRequests'] }}</span>
            </div>
        </div>
    </div>
    @endif
</div>
