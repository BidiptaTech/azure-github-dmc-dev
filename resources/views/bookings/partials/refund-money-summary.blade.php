@php
    $moneyCurrency = strtoupper(trim((string) ($tour->money_currency ?? '')));
    if ($moneyCurrency === '') {
        $moneyCurrency = strtoupper(trim((string) ($currency ?? '')));
    }
    $moneyTotal = (float) ($tour->money_total ?? 0);
    $moneyPaid = (float) ($tour->money_paid ?? 0);
    $moneyDue = (float) ($tour->money_due ?? 0);
    $moneyDiscount = (float) ($tour->money_discount ?? 0);
    $moneyDiscountLabel = trim((string) ($tour->money_discount_label ?? ''));
    $isTravclicksView = (bool) ($isTravclicksView
        ?? in_array((int) (auth()->user()->role_id ?? 0), [1, 2, 3, 4], true));

    // Precomputed in BookingsController::attachTourMoneyRefunded — always in money_currency.
    $refundedAmount = (float) ($tour->money_refunded ?? 0);
    $refundedCurrency = $moneyCurrency !== '' ? $moneyCurrency : 'SGD';
    $dmcMarkupTotal = (float) ($tour->money_dmc_markup ?? 0);
    $dmcMarkupRuleText = trim((string) ($tour->money_dmc_markup_label ?? ''));
    $refundedLabel = $isTravclicksView ? 'Refunded to DMC' : 'Refunded';
@endphp
<div class="refund-money-summary mt-2">
    <span>Tour total</span>
    <span>{{ $moneyCurrency }} {{ number_format($moneyTotal, 2) }}</span>
    @if($moneyDiscount > 0.009)
        <span title="Discount already reflected in Tour total">Discount{{ $moneyDiscountLabel !== '' ? ' ('.$moneyDiscountLabel.')' : '' }}</span>
        <span class="rms-discount">−{{ $moneyCurrency }} {{ number_format($moneyDiscount, 2) }}</span>
    @endif
    <span>Paid</span>
    <span class="rms-paid">{{ $moneyCurrency }} {{ number_format($moneyPaid, 2) }}</span>
    <span>Due</span>
    <span class="{{ $moneyDue > 0 ? 'rms-due' : '' }}">{{ $moneyCurrency }} {{ number_format($moneyDue, 2) }}</span>
    @if($isTravclicksView && $dmcMarkupTotal > 0)
        <span title="DMC markup already paid to DMC — deducted from TravClicks refund">DMC markup{{ $dmcMarkupRuleText !== '' ? ' ('.$dmcMarkupRuleText.')' : '' }}</span>
        <span class="rms-dmc-markup">−{{ $refundedCurrency }} {{ number_format($dmcMarkupTotal, 2) }}</span>
    @endif
    <span>{{ $refundedLabel }}</span>
    <span class="rms-refunded">{{ $refundedCurrency }} {{ number_format($refundedAmount, 2) }}</span>
</div>
