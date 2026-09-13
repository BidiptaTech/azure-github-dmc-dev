@php
    $restaurantName = $booking['restaurantName'] ?? 'Restaurant Booking';
    $mealType = ucfirst($booking['mealType'] ?? 'Meal');
    $mealSpecific = $booking['mealSpecificType'] ?? 'Standard';
    $mealPrice = (float) ($booking['totalPrice'] ?? 0);
    $adults = (int) ($booking['adultCount'] ?? 0);
    $children = (int) ($booking['childCount'] ?? 0);
    $party = $adults + $children;
    $countryLabel = $orderCountry !== '' ? $orderCountry : trim((string) ($booking['country'] ?? ''));
    if ($countryLabel === '') {
        $countryLabel = 'N/A';
    }
    try {
        $diningDate = !empty($booking['bookingDate'])
            ? \Carbon\Carbon::parse($booking['bookingDate'])->format('D, M d, Y')
            : 'N/A';
    } catch (\Throwable $e) {
        $diningDate = (string) ($booking['bookingDate'] ?? 'N/A');
    }
    $meals = (isset($booking['MealDescription']) && is_array($booking['MealDescription'])) ? $booking['MealDescription'] : [];
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
    $grandTotal = $mealPrice + $transferPrice + $guidePrice;
@endphp

<div class="svc-panel">
    <div class="svc-panel-head">
        <div class="svc-panel-head-main">
            <div class="svc-thumb svc-thumb-fallback"><i class="ri-restaurant-2-line"></i></div>
            <div>
                <p class="svc-title">{{ $restaurantName }}</p>
                <p class="svc-subtitle">{{ $mealType }} • {{ $mealSpecific }}</p>
            </div>
        </div>
        <div class="svc-price">{{ $currency }} {{ number_format($grandTotal, 2) }}</div>
    </div>

    <div class="svc-section mb-0" style="border:0;border-radius:0;">
        <p class="svc-section-title">Reservation</p>
        <div class="svc-dl">
            <div class="svc-dl-row">
                <span class="svc-dl-label">Dining Date</span>
                <span class="svc-dl-value">{{ $diningDate }}</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">Dining Time</span>
                <span class="svc-dl-value">{{ $booking['visitTime'] ?? 'TBC' }}</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">Meal</span>
                <span class="svc-dl-value">{{ $mealType }} / {{ $mealSpecific }}</span>
            </div>
            <div class="svc-dl-row">
                <span class="svc-dl-label">Country</span>
                <span class="svc-dl-value">{{ $countryLabel }}</span>
            </div>
        </div>
    </div>

    <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
        <p class="svc-section-title">Party Size</p>
        <div class="svc-guest-grid">
            <div class="svc-guest-box"><div class="num">{{ $adults }}</div><div class="lbl">Adults</div></div>
            <div class="svc-guest-box"><div class="num">{{ $children }}</div><div class="lbl">Children</div></div>
        </div>
        <div class="svc-total-bar">Party: {{ $party }} Guest{{ $party === 1 ? '' : 's' }}</div>
    </div>

    @if(count($meals) > 0)
    <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
        <p class="svc-section-title">Menu Items ({{ count($meals) }})</p>
        <div class="svc-dl">
            @foreach($meals as $mealIndex => $meal)
                @php
                    $qty = (int) ($meal['quantity'] ?? 1);
                    $unit = (float) ($meal['price'] ?? 0);
                    $sub = $qty * $unit;
                    $label = trim(($meal['category'] ?? 'Item') . ' / ' . ($meal['item_type'] ?? 'Menu'));
                @endphp
                <div class="svc-dl-row">
                    <span class="svc-dl-label">Item {{ $mealIndex + 1 }}</span>
                    <span class="svc-dl-value">{{ $label }} × {{ $qty }}</span>
                </div>
                <div class="svc-dl-row">
                    <span class="svc-dl-label">Subtotal</span>
                    <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($sub, 2) }}</span>
                </div>
            @endforeach
            <div class="svc-dl-row full">
                <span class="svc-dl-label">Meal Total</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($mealPrice, 2) }}</span>
            </div>
        </div>
    </div>
    @else
    <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
        <p class="svc-section-title">Pricing</p>
        <div class="svc-dl">
            <div class="svc-dl-row full">
                <span class="svc-dl-label">Meal Total</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($mealPrice, 2) }}</span>
            </div>
        </div>
    </div>
    @endif

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
                <span class="svc-dl-label">Meals</span>
                <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($mealPrice, 2) }}</span>
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
            <div class="svc-dl-row full"><span class="svc-dl-value">{{ $booking['specialRequests'] }}</span></div>
        </div>
    </div>
    @endif
</div>
