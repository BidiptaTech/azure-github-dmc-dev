{{-- Requires pricing vars from tour-payment-pricing partial + $tourCurrency --}}
@if($discountAmount > 0 || $negotiationDiscount > 0)
<div class="row text-center mb-2 g-2">
    <div class="col-6 col-md-3">
        <small class="text-muted">Gross Price</small>
        <div class="fw-bold text-secondary">{{ number_format($grossTourAmount, 2) }} {{ $tourCurrency }}</div>
    </div>
    @if($discountAmount > 0)
    <div class="col-6 col-md-3">
        <small class="text-muted">Discount (FOC)</small>
        <div class="fw-bold text-success">- {{ number_format(round($discountAmount), 2) }} {{ $tourCurrency }}</div>
    </div>
    @endif
    @if($discountAmount > 0 && $negotiationDiscount > 0)
    <div class="col-6 col-md-3">
        <small class="text-muted">After FOC</small>
        <div class="fw-bold text-secondary">{{ number_format($priceAfterFoc, 2) }} {{ $tourCurrency }}</div>
    </div>
    @endif
    @if($negotiationDiscount > 0)
    <div class="col-6 col-md-3">
        <small class="text-muted">Negotiation</small>
        <div class="fw-bold text-success">- {{ number_format(round($negotiationDiscount), 2) }} {{ $tourCurrency }}</div>
    </div>
    @endif
    <div class="col-6 col-md-3">
        <small class="text-muted">Actual Price</small>
        <div class="fw-bold text-primary">{{ number_format($netTourAmount, 2) }} {{ $tourCurrency }}</div>
    </div>
</div>
<hr class="my-2">
@endif
