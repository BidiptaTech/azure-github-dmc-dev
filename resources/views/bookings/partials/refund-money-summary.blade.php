@php
    $moneyCurrency = trim((string) ($tour->money_currency ?? ''));
    if ($moneyCurrency === '') {
        $moneyCurrency = trim((string) ($currency ?? ''));
    }
    $moneyTotal = (float) ($tour->money_total ?? 0);
    $moneyPaid = (float) ($tour->money_paid ?? 0);
    $moneyDue = (float) ($tour->money_due ?? 0);
    $isTravclicksView = (bool) ($isTravclicksView
        ?? in_array((int) (auth()->user()->role_id ?? 0), [1, 2, 3, 4], true));

    $refundedAmount = 0.0;
    $refundedCurrency = $moneyCurrency;
    $refundCurrencies = [];
    $dmcMarkupTotal = 0.0;
    $dmcMarkupLabels = [];
    foreach (collect($tour->booking ?? []) as $refundOrder) {
        if ($isTravclicksView) {
            $markupVal = (float) ($refundOrder->online_dmc_markup ?? 0);
            if ($markupVal > 0) {
                $dmcMarkupTotal += $markupVal;
                $ruleLabel = trim((string) ($refundOrder->online_dmc_markup_label ?? ''));
                if ($ruleLabel !== '') {
                    $dmcMarkupLabels[$ruleLabel] = true;
                }
            }
            // TravClicks→DMC wallet credit layer
            if (! ((bool) ($refundOrder->travclicks_refunded ?? false))) {
                continue;
            }
            $amountValue = $refundOrder->travclicks_refund_amount ?? null;
        } else {
            // DMC→agent refund layer
            if (! ((bool) ($refundOrder->refunded ?? false))) {
                continue;
            }
            $amountValue = $refundOrder->refund_amount ?? null;
        }
        if ($amountValue === null || $amountValue === '') {
            continue;
        }
        $refundedAmount += (float) $amountValue;
        $orderCurrency = trim((string) ($refundOrder->refund_price_currency ?? ''));
        if ($orderCurrency !== '') {
            $refundCurrencies[$orderCurrency] = true;
        }
    }
    if (! $isTravclicksView
        && $refundedAmount <= 0
        && ($tour->tour_status ?? '') === 'Refunded'
        && $tour->saved_refund_amount !== null
        && $tour->saved_refund_amount !== ''
    ) {
        $refundedAmount = (float) $tour->saved_refund_amount;
    }
    if (count($refundCurrencies) === 1) {
        $refundedCurrency = (string) array_key_first($refundCurrencies);
    }
    // Never display a refund above what was billed/paid.
    $refundCap = $moneyPaid > 0 ? $moneyPaid : $moneyTotal;
    if ($refundCap > 0 && $refundedAmount > $refundCap + 0.009) {
        $refundedAmount = $refundCap;
    }
    $refundedLabel = $isTravclicksView ? 'Refunded to DMC' : 'Refunded';
    $dmcMarkupCurrency = $isTravclicksView && $dmcMarkupTotal > 0
        ? ($refundedCurrency !== '' ? $refundedCurrency : 'SGD')
        : '';
    $dmcMarkupRuleText = count($dmcMarkupLabels) === 1
        ? (string) array_key_first($dmcMarkupLabels)
        : '';
@endphp
<div class="refund-money-summary mt-2">
    <span>Tour total</span>
    <span>{{ $moneyCurrency }} {{ number_format($moneyTotal, 2) }}</span>
    <span>Paid</span>
    <span class="rms-paid">{{ $moneyCurrency }} {{ number_format($moneyPaid, 2) }}</span>
    <span>Due</span>
    <span class="{{ $moneyDue > 0 ? 'rms-due' : '' }}">{{ $moneyCurrency }} {{ number_format($moneyDue, 2) }}</span>
    @if($isTravclicksView && $dmcMarkupTotal > 0)
        <span title="DMC markup already paid to DMC — deducted from TravClicks refund">DMC markup{{ $dmcMarkupRuleText !== '' ? ' ('.$dmcMarkupRuleText.')' : '' }}</span>
        <span class="rms-dmc-markup">−{{ $dmcMarkupCurrency }} {{ number_format($dmcMarkupTotal, 2) }}</span>
    @endif
    <span>{{ $refundedLabel }}</span>
    <span class="rms-refunded">{{ $refundedCurrency }} {{ number_format($refundedAmount, 2) }}</span>
</div>
