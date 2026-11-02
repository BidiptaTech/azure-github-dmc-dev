@php
    $moneyCurrency = trim((string) ($tour->money_currency ?? ''));
    if ($moneyCurrency === '') {
        $moneyCurrency = trim((string) ($currency ?? ''));
    }
    $moneyTotal = (float) ($tour->money_total ?? 0);
    $moneyPaid = (float) ($tour->money_paid ?? 0);
    $moneyDue = (float) ($tour->money_due ?? 0);
    $refundedAmount = 0.0;
    $refundedCurrency = $moneyCurrency;
    if (($tour->tour_status ?? '') === 'Refunded' && $tour->saved_refund_amount !== null && $tour->saved_refund_amount !== '') {
        $refundedAmount = (float) $tour->saved_refund_amount;
    } else {
        $refundCurrencies = [];
        foreach (collect($tour->booking ?? []) as $refundOrder) {
            if (! ((bool) ($refundOrder->refunded ?? false))) {
                continue;
            }
            if ($refundOrder->refund_amount === null || $refundOrder->refund_amount === '') {
                continue;
            }
            $refundedAmount += (float) $refundOrder->refund_amount;
            $orderCurrency = trim((string) ($refundOrder->refund_price_currency ?? ''));
            if ($orderCurrency !== '') {
                $refundCurrencies[$orderCurrency] = true;
            }
        }
        if (count($refundCurrencies) === 1) {
            $refundedCurrency = (string) array_key_first($refundCurrencies);
        }
    }
@endphp
<div class="mt-2" style="font-size: 0.82rem; line-height: 1.45; min-width: 9rem; font-weight: 700; color: #0f172a;">
    <div class="d-flex justify-content-between gap-2">
        <span>Tour total</span>
        <span class="text-nowrap">{{ $moneyCurrency }} {{ number_format($moneyTotal, 2) }}</span>
    </div>
    <div class="d-flex justify-content-between gap-2">
        <span>Paid</span>
        <span class="text-success text-nowrap">{{ $moneyCurrency }} {{ number_format($moneyPaid, 2) }}</span>
    </div>
    <div class="d-flex justify-content-between gap-2">
        <span>Due</span>
        <span class="text-nowrap {{ $moneyDue > 0 ? 'text-danger' : '' }}">{{ $moneyCurrency }} {{ number_format($moneyDue, 2) }}</span>
    </div>
    <div class="d-flex justify-content-between gap-2">
        <span>Refunded</span>
        <span class="text-success text-nowrap">{{ $refundedCurrency }} {{ number_format($refundedAmount, 2) }}</span>
    </div>
</div>
