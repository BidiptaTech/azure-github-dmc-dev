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
    $guidePrice = 0.0;
    if (!empty($booking['guide_options']['total_price'])) {
        $guidePrice = (float) $booking['guide_options']['total_price'];
    }
    $grandTotal = $hotelPrice + $guidePrice;

    $checkIn = 'N/A';
    $checkOut = 'N/A';
    $nights = null;
    if (isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 0) {
        try {
            $checkIn = \Carbon\Carbon::parse($booking['bookingDate'][0])->format('M d, Y');
            if (count($booking['bookingDate']) > 1) {
                $ci = \Carbon\Carbon::parse($booking['bookingDate'][0]);
                $co = \Carbon\Carbon::parse(end($booking['bookingDate']));
                $checkOut = $co->format('M d, Y');
                $nights = $ci->diffInDays($co);
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

    $tf = (isset($booking['transfer_options']) && is_array($booking['transfer_options'])) ? $booking['transfer_options'] : [];
    $hasTransfer = !empty($tf['transfer_required']) && in_array($tf['transfer_required'], [true, 'true', 'Yes', 1, '1'], true);
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
                <p class="svc-subtitle">Enquiry {{ $index + 1 }} • {{ ucfirst($booking['bookingType'] ?? 'Standard') }}</p>
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
            <div class="svc-dl-row full">
                <span class="svc-dl-label">Total Price</span>
                <span class="svc-dl-value svc-amount" style="color:var(--svc-accent);">{{ $currency }} {{ number_format($grandTotal, 2) }}</span>
            </div>
        </div>
    </div>

    @include('bookings.partials.hotel-booking-modal-sections', ['booking' => $booking, 'currency' => $currency, 'professional' => true])

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
            @if(!empty($tf['destination_name']))
            <div class="svc-dl-row full">
                <span class="svc-dl-label">Destination</span>
                <span class="svc-dl-value">{{ $tf['destination_name'] }}</span>
            </div>
            @endif
            @if(!empty($tf['pickup_location_name']))
            <div class="svc-dl-row full">
                <span class="svc-dl-label">Pickup</span>
                <span class="svc-dl-value">{{ $tf['pickup_location_name'] }}</span>
            </div>
            @endif
            @php
                $vehicleName = $tf['vehicle_details']['vehicle_name'] ?? ($tf['vehicle_id'] ?? 'N/A');
                $transferCost = $tf['cost'] ?? 0;
                if ((int) ($tour->is_pro ?? 0) === 1 && isset($tf['totalPrice'])) {
                    $transferCost = $tf['totalPrice'];
                }
            @endphp
            <div class="svc-dl-row">
                <span class="svc-dl-label">Vehicle</span>
                <span class="svc-dl-value">{{ $vehicleName }}</span>
            </div>
            @if((float) $transferCost > 0)
            <div class="svc-dl-row">
                <span class="svc-dl-label">Cost</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format((float) $transferCost, 2) }}</span>
            </div>
            @endif
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
