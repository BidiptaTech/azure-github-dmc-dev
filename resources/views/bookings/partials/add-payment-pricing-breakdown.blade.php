{{-- Requires pricing vars from parent tour payment block: tourCurrency, tour, grossTourAmount, etc. --}}
@php
    $bdMarkupMoney = $tourMarkupMoney ?? 0;
    $bdDiscountMoney = $tourDiscountMoney ?? ($discountAmount ?? 0);
    $bdNegotiation = $negotiationDiscount ?? 0;

    $bdMarkupType = strtolower((string) ($tourMarkupType ?? ''));
    $bdDiscountType = strtolower((string) ($tourDiscountType ?? ''));
    $bdMarkupRaw = (float) ($tour->getAttributes()['markup_amount'] ?? $tour->markup_amount ?? 0);
    $bdDiscountRaw = (float) ($tour->getAttributes()['discount_amount'] ?? $tour->discount_amount ?? 0);

    $bdTrim = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');

    $markupLabel = 'Markup';
    if ($bdMarkupType === 'percentage') {
        $markupLabel = 'Markup (' . $bdTrim($bdMarkupRaw) . '%)';
    } elseif ($bdMarkupType === 'flat') {
        $markupLabel = 'Markup (Fixed)';
    }

    $discountLabel = 'Discount';
    if ($bdDiscountType === 'percentage') {
        $discountLabel = 'Discount (' . $bdTrim($bdDiscountRaw) . '%)';
    } elseif ($bdDiscountType === 'foc') {
        $discountLabel = 'Discount (FOC)';
    } elseif ($bdDiscountType === 'flat') {
        $discountLabel = 'Discount (Fixed)';
    }
@endphp
@if($bdMarkupMoney > 0 || $bdDiscountMoney > 0 || $bdNegotiation > 0)
<div class="row text-center mb-2 g-2">
    <div class="col-6 col-md-3">
        <small class="text-muted">Gross Price</small>
        <div class="fw-bold text-secondary">{{ number_format($grossTourAmount, 2) }} {{ $tourCurrency }}</div>
    </div>
    @if($bdMarkupMoney > 0)
    <div class="col-6 col-md-3">
        <small class="text-muted">{{ $markupLabel }}</small>
        <div class="fw-bold text-info">+ {{ number_format(round($bdMarkupMoney), 2) }} {{ $tourCurrency }}</div>
    </div>
    @endif
    @if($bdDiscountMoney > 0)
    <div class="col-6 col-md-3">
        <small class="text-muted">{{ $discountLabel }}</small>
        <div class="fw-bold text-success">- {{ number_format(round($bdDiscountMoney), 2) }} {{ $tourCurrency }}</div>
    </div>
    @endif
    @if($bdNegotiation > 0)
    <div class="col-6 col-md-3">
        <small class="text-muted">Negotiation</small>
        <div class="fw-bold text-success">- {{ number_format(round($bdNegotiation), 2) }} {{ $tourCurrency }}</div>
    </div>
    @endif
    <div class="col-6 col-md-3">
        <small class="text-muted">Actual Price</small>
        <div class="fw-bold text-primary">{{ number_format($netTourAmount, 2) }} {{ $tourCurrency }}</div>
    </div>
</div>
<hr class="my-2">
@endif
