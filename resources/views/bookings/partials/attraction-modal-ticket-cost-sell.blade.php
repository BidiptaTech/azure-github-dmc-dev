{{--
  Attraction modal ticket cost/sell extras for Pro (legacy card layout).
  Expects: $booking, $currency, optional $tour
--}}
@php
    $isPro = (int) ($tour->is_pro ?? 0) === 1;
    $td = is_array($booking['ticket_details'] ?? null) ? $booking['ticket_details'] : [];
    $adultSell = (float) ($td['adult_sell'] ?? $td['adult_price'] ?? $booking['adultSell'] ?? 0);
    $adultCost = (float) ($td['adult_cost'] ?? $booking['adultCost'] ?? 0);
    $childSell = (float) ($td['child_sell'] ?? $td['child_price'] ?? $booking['childSell'] ?? 0);
    $childCost = (float) ($td['child_cost'] ?? $booking['childCost'] ?? 0);
@endphp
@if($isPro && ($adultCost > 0 || $childCost > 0))
<div class="row g-2 mb-2">
    <div class="col-md-6">
        <div class="bg-white border rounded p-2">
            <small class="text-muted d-block" style="font-size: 0.7rem;">Adult Cost / Sell</small>
            <div class="fw-medium" style="font-size: 0.8rem;">
                <span class="text-muted">{{ $currency }} {{ number_format($adultCost, 2) }}</span>
                <span class="mx-1">/</span>
                <span class="text-success fw-bold">{{ $currency }} {{ number_format($adultSell, 2) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="bg-white border rounded p-2">
            <small class="text-muted d-block" style="font-size: 0.7rem;">Child Cost / Sell</small>
            <div class="fw-medium" style="font-size: 0.8rem;">
                <span class="text-muted">{{ $currency }} {{ number_format($childCost, 2) }}</span>
                <span class="mx-1">/</span>
                <span class="text-success fw-bold">{{ $currency }} {{ number_format($childSell, 2) }}</span>
            </div>
        </div>
    </div>
</div>
@endif
