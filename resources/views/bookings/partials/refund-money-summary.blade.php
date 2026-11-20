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
    $moneyDiscountCurrency = strtoupper(trim((string) ($tour->money_discount_currency ?? $moneyCurrency)));
    $mixedFx = ! empty($tour->money_mixed_fx);
    $discountFxNote = trim((string) ($tour->money_discount_fx_note ?? ''));
    $displayTotal = (float) ($tour->money_display_total ?? $moneyTotal);
    $displayPaid = (float) ($tour->money_display_paid ?? $moneyPaid);
    $displayDiscount = (float) ($tour->money_display_discount ?? $moneyDiscount);
    $onlineTotal = (float) ($tour->money_online_total ?? 0);
    $onlinePaid = (float) ($tour->money_online_paid ?? 0);
    $onlineCurrency = strtoupper(trim((string) ($tour->money_online_currency ?? '')));
    $offlinePaid = (float) ($tour->money_offline_paid ?? $moneyPaid);
    $isTravclicksView = (bool) ($isTravclicksView
        ?? in_array((int) (auth()->user()->role_id ?? 0), [1, 2, 3, 4], true));

    // Precomputed in BookingsController::attachTourMoneyRefunded — always in money_currency.
    $refundedAmount = (float) ($tour->money_refunded ?? 0);
    $refundedCurrency = $moneyCurrency !== '' ? $moneyCurrency : 'SGD';
    $dmcMarkupTotal = (float) ($tour->money_dmc_markup ?? 0);
    $dmcMarkupRuleText = trim((string) ($tour->money_dmc_markup_label ?? ''));
    $refundedLabel = $isTravclicksView ? 'Refunded to DMC' : 'Refunded';

    $rmsAmt = static function (float $amount, string $cur): string {
        $cur = strtoupper(trim($cur));
        $decimals = ($cur === 'IDR' && abs($amount - round($amount)) < 0.001) ? 0 : 2;

        return number_format($amount, $decimals).($cur !== '' ? ' '.$cur : '');
    };
    $rmsFmt = static function (float $amount, string $cur) use ($rmsAmt): string {
        $cur = strtoupper(trim($cur));
        $decimals = ($cur === 'IDR' && abs($amount - round($amount)) < 0.001) ? 0 : 2;

        return ($cur !== '' ? $cur.' ' : '').number_format($amount, $decimals);
    };
    $rmsJoin = static function (array $parts): string {
        $parts = array_values(array_filter($parts, static fn ($p) => $p !== null && $p !== ''));

        return $parts === [] ? '' : ' ('.implode(' + ', $parts).')';
    };

    $tourTotalLabel = $rmsFmt($mixedFx ? $displayTotal : $moneyTotal, $moneyCurrency);
    if ($mixedFx) {
        $tourTotalLabel .= $rmsJoin([
            $onlineTotal > 0.009 && $onlineCurrency !== '' ? $rmsAmt($onlineTotal, $onlineCurrency) : null,
            $moneyTotal > 0.009 && $moneyCurrency !== '' ? $rmsAmt($moneyTotal, $moneyCurrency) : null,
        ]);
    }

    $paidLabel = $rmsFmt($mixedFx ? $displayPaid : $moneyPaid, $moneyCurrency);
    if ($mixedFx) {
        $paidLabel .= $rmsJoin([
            $onlinePaid > 0.009 && $onlineCurrency !== '' ? $rmsAmt($onlinePaid, $onlineCurrency) : null,
            $offlinePaid > 0.009 && $moneyCurrency !== '' ? $rmsAmt($offlinePaid, $moneyCurrency) : null,
        ]);
    }

    $discountLabel = $rmsFmt($mixedFx ? $displayDiscount : $moneyDiscount, $moneyCurrency);
    if ($mixedFx && $discountFxNote !== '' && $moneyDiscount > 0.009) {
        $discountLabel .= ' ('.$rmsAmt($moneyDiscount, $moneyDiscountCurrency !== '' ? $moneyDiscountCurrency : $discountFxNote).')';
    } elseif (! $mixedFx && $moneyDiscountCurrency !== '' && $moneyDiscountCurrency !== $moneyCurrency) {
        $discountLabel = $rmsFmt($moneyDiscount, $moneyDiscountCurrency);
    }

    $settlementInfo = 'Amounts are shown in the settlement currency confirmed at negotiation'
        .($moneyCurrency !== '' ? ' ('.$moneyCurrency.')' : '')
        .'. Figures in parentheses are the original amounts in each currency.';
@endphp
<div class="refund-money-summary mt-2">
    <span class="rms-label-with-info">
        Tour total
        @if($mixedFx)
            <i class="ri-information-line rms-info-icon"
                title="{{ e($settlementInfo) }}"
                aria-label="{{ e($settlementInfo) }}"></i>
        @endif
    </span>
    <span>{{ $tourTotalLabel }}</span>
    @if($moneyDiscount > 0.009)
        <span title="Subtracted from Tour total to get the payable amount">Discount{{ $moneyDiscountLabel !== '' ? ' ('.$moneyDiscountLabel.')' : '' }}</span>
        <span class="rms-discount">−{{ $discountLabel }}</span>
    @endif
    <span>Paid</span>
    <span class="rms-paid">{{ $paidLabel }}</span>
    <span>Due</span>
    <span class="{{ $moneyDue > 0 ? 'rms-due' : '' }}">{{ $rmsFmt($moneyDue, $moneyCurrency) }}</span>
    @if($isTravclicksView && $dmcMarkupTotal > 0)
        <span title="DMC markup already paid to DMC — deducted from TravClicks refund">DMC markup{{ $dmcMarkupRuleText !== '' ? ' ('.$dmcMarkupRuleText.')' : '' }}</span>
        <span class="rms-dmc-markup">−{{ $rmsFmt($dmcMarkupTotal, $refundedCurrency) }}</span>
    @endif
    <span>{{ $refundedLabel }}</span>
    <span class="rms-refunded">{{ $rmsFmt($refundedAmount, $refundedCurrency) }}</span>
</div>
