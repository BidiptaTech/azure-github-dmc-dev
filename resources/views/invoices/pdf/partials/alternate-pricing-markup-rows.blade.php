{{-- Lite invoice: payment-modal style markup rows (3-col inv-lines-table). --}}
@php
    $markupFmtPrice = $markupFmtPrice ?? ($fmtMoney ?? null);
    if (!is_callable($markupFmtPrice)) {
        $markupFmtPrice = static fn ($n) => number_format(round((float) $n, 2), 2);
    }
@endphp
@if(!empty($pricingMarkup['has_markup_display']))
<tr class="inv-total-row inv-total-sep">
    <td class="inv-col-service">&nbsp;</td>
    <td class="inv-total-label-cell" style="text-align:left; color:#312e81; font-weight:700;">Pricing Breakdown (Before / After Negotiation)</td>
    <td class="inv-col-amount">&nbsp;</td>
</tr>
<tr class="inv-total-row">
    <td class="inv-col-service">&nbsp;</td>
    <td class="inv-total-label-cell">Gross (before markup)</td>
    <td class="inv-col-amount"><span class="inv-svc-amt">{{ $markupFmtPrice($pricingMarkup['gross']) }}</span></td>
</tr>
<tr class="inv-total-row">
    <td class="inv-col-service">&nbsp;</td>
    <td class="inv-total-label-cell">
        Hotel Markup
        <span style="color:#64748b; font-weight:normal;">({{ $payTypeLabel($pricingMarkup['markup_type'], $pricingMarkup['hotel_markup_raw']) }})</span>
    </td>
    <td class="inv-col-amount"><span class="inv-svc-amt">
        @if(($pricingMarkup['markup_type'] ?? '') === 'flat' || ($pricingMarkup['markup_type'] ?? '') === 'fixed')
            +{{ $markupFmtPrice($pricingMarkup['hotel_markup_money']) }}
        @else
            {{ $payTypeLabel($pricingMarkup['markup_type'], $pricingMarkup['hotel_markup_raw']) }}
        @endif
    </span></td>
</tr>
<tr class="inv-total-row">
    <td class="inv-col-service">&nbsp;</td>
    <td class="inv-total-label-cell">
        Other Service Markup
        <span style="color:#64748b; font-weight:normal;">({{ $payTypeLabel($pricingMarkup['markup_type'], $pricingMarkup['other_markup_raw']) }})</span>
    </td>
    <td class="inv-col-amount"><span class="inv-svc-amt">
        @if(($pricingMarkup['markup_type'] ?? '') === 'flat' || ($pricingMarkup['markup_type'] ?? '') === 'fixed')
            +{{ $markupFmtPrice($pricingMarkup['other_markup_money']) }}
        @else
            {{ $payTypeLabel($pricingMarkup['markup_type'], $pricingMarkup['other_markup_raw']) }}
        @endif
    </span></td>
</tr>
<tr class="inv-total-row">
    <td class="inv-col-service">&nbsp;</td>
    <td class="inv-total-label-cell">
        Discount
        <span style="color:#64748b; font-weight:normal;">({{ $payTypeLabel($pricingMarkup['discount_type'], $pricingMarkup['discount_raw']) }})</span>
    </td>
    <td class="inv-col-amount"><span class="inv-svc-amt">−{{ $markupFmtPrice($pricingMarkup['discount_money']) }}</span></td>
</tr>
<tr class="inv-total-row">
    <td class="inv-col-service">&nbsp;</td>
    <td class="inv-total-label-cell"><strong>Actual Price</strong> <span style="color:#64748b; font-weight:normal;">(Gross + markup − discount)</span></td>
    <td class="inv-col-amount"><span class="inv-svc-amt"><strong>{{ $markupFmtPrice($pricingMarkup['actual_price']) }}</strong></span></td>
</tr>
<tr class="inv-total-row">
    <td class="inv-col-service">&nbsp;</td>
    <td class="inv-total-label-cell"><strong>Confirmed Price</strong> <span style="color:#64748b; font-weight:normal;">(After negotiation)</span></td>
    <td class="inv-col-amount"><span class="inv-svc-amt"><strong>{{ $markupFmtPrice($pricingMarkup['confirmed_price']) }}</strong></span></td>
</tr>
@if(!empty($pricingMarkup['markup_rows']))
<tr class="inv-total-row">
    <td colspan="3" style="padding: 4px 4px 6px !important;">
        <div style="font-weight:700; color:#475569; font-size:9px; margin-bottom:3px;">City / Country Markup &amp; Discount</div>
        <table class="inv-markup-city-table">
            <thead>
                <tr>
                    <th>City / Country</th>
                    <th>Hotel</th>
                    <th>Other</th>
                    <th>Discount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pricingMarkup['markup_rows'] as $mkRow)
                <tr>
                    <td>
                        {{ $mkRow['place'] }}
                        @if(!empty($mkRow['currency']))
                            <span style="color:#64748b;">({{ $mkRow['currency'] }})</span>
                        @endif
                    </td>
                    <td>{{ $payTypeLabel($mkRow['markup_type'], $mkRow['hotel_raw']) }}</td>
                    <td>{{ $payTypeLabel($mkRow['markup_type'], $mkRow['other_raw']) }}</td>
                    <td>{{ $payTypeLabel($mkRow['discount_type'], $mkRow['discount_raw']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </td>
</tr>
@endif
@endif
