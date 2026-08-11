{{--
  Shared Transfer (vehicle + price) + Guide (details + price) for enquiry modals.
  Expects: $booking, $currency, optional $tour
--}}
@php
    $tf = (isset($booking['transfer_options']) && is_array($booking['transfer_options']))
        ? $booking['transfer_options']
        : ((isset($booking['transferOptions']) && is_array($booking['transferOptions'])) ? $booking['transferOptions'] : []);
    $go = (isset($booking['guide_options']) && is_array($booking['guide_options']))
        ? $booking['guide_options']
        : ((isset($booking['guideOptions']) && is_array($booking['guideOptions'])) ? $booking['guideOptions'] : []);

    $hasTransfer = !empty($tf) && (
        in_array($tf['transfer_required'] ?? null, [true, 'true', 'Yes', 1, '1'], true)
        || !empty($tf['vehicle_details'])
        || !empty($tf['vehicle_id'])
        || !empty($tf['type'])
        || ((float) ($tf['totalPrice'] ?? $tf['cost'] ?? 0) > 0)
    );

    $hasGuide = !empty($go) && (
        in_array($go['guide_required'] ?? null, [true, 'true', 'Yes', 1, '1'], true)
        || !empty($go['guide_name'])
        || !empty($go['guideName'])
        || !empty($go['name'])
        || !empty($go['guide_id'])
        || !empty($go['guideId'])
        || ((float) ($go['total_price'] ?? $go['cost'] ?? $go['sell'] ?? 0) > 0)
    );

    $isPro = (int) ($tour->is_pro ?? 0) === 1;
    $transferCost = 0.0;
    if ($hasTransfer) {
        $transferCost = $isPro
            ? (float) ($tf['totalPrice'] ?? $tf['cost'] ?? 0)
            : (float) ($tf['cost'] ?? $tf['totalPrice'] ?? 0);
    }
    $vehicleName = $tf['vehicle_details']['vehicle_name']
        ?? $tf['vehicle_details']['name']
        ?? $tf['vehicle_name']
        ?? $tf['vehicle_id']
        ?? null;
    $vehicleCapacity = $tf['vehicle_details']['seating_capacity']
        ?? $tf['vehicle_details']['capacity']
        ?? null;

    $guideName = $go['guide_name'] ?? $go['guideName'] ?? $go['name'] ?? null;
    $guideHours = $go['package_hours'] ?? $go['hours'] ?? $go['duration'] ?? null;
    $guidePickup = $go['pickup_time'] ?? $go['pickupTime'] ?? null;
    $guideBase = (float) ($go['base_price'] ?? $go['basePrice'] ?? 0);
    $guideSurcharge = (float) ($go['surcharge'] ?? $go['night_surcharge'] ?? 0);
    $guideTotal = (float) ($go['total_price'] ?? $go['cost'] ?? $go['Cost'] ?? $go['sell'] ?? $go['Sell'] ?? 0);
@endphp

@if($hasTransfer)
<div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
    <p class="svc-section-title">Transfer / Vehicle</p>
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
        @if(!empty($vehicleName))
        <div class="svc-dl-row">
            <span class="svc-dl-label">Vehicle</span>
            <span class="svc-dl-value">
                {{ $vehicleName }}
                @if(!empty($vehicleCapacity))
                    <span class="text-muted">({{ $vehicleCapacity }} seats)</span>
                @endif
            </span>
        </div>
        @endif
        <div class="svc-dl-row">
            <span class="svc-dl-label">Transfer Price</span>
            <span class="svc-dl-value svc-amount" style="color:var(--svc-accent);">
                {{ $currency }} {{ number_format($transferCost, 2) }}
            </span>
        </div>
    </div>
</div>
@endif

@if($hasGuide)
<div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
    <p class="svc-section-title">Guide Details</p>
    <div class="svc-dl">
        <div class="svc-dl-row">
            <span class="svc-dl-label">Guide</span>
            <span class="svc-dl-value">{{ $guideName ?: 'Assigned Guide' }}</span>
        </div>
        @if(!empty($guideHours))
        <div class="svc-dl-row">
            <span class="svc-dl-label">Duration</span>
            <span class="svc-dl-value">{{ $guideHours }} H</span>
        </div>
        @endif
        @if(!empty($guidePickup))
        <div class="svc-dl-row">
            <span class="svc-dl-label">Pickup Time</span>
            <span class="svc-dl-value">{{ $guidePickup }}</span>
        </div>
        @endif
        @if($guideBase > 0)
        <div class="svc-dl-row">
            <span class="svc-dl-label">Base Price</span>
            <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($guideBase, 2) }}</span>
        </div>
        @endif
        @if($guideSurcharge > 0)
        <div class="svc-dl-row">
            <span class="svc-dl-label">Night Surcharge</span>
            <span class="svc-dl-value svc-amount">{{ $currency }} {{ number_format($guideSurcharge, 2) }}</span>
        </div>
        @endif
        <div class="svc-dl-row">
            <span class="svc-dl-label">Guide Price</span>
            <span class="svc-dl-value svc-amount" style="color:var(--svc-accent);">
                {{ $currency }} {{ number_format($guideTotal, 2) }}
            </span>
        </div>
    </div>
</div>
@endif
