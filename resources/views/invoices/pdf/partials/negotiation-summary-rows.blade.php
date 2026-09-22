{{--
  Country-wise negotiation breakdown + shared summary rows for standard invoice PDFs.
  Expects: $formatPrice, $actualAmount, $negotiatedAmount, $discount, $finalPrice,
           $gstAmount, $shouldShowTax, $taxBreakdown, $paymentReceived, $outstandingBalance,
           $isThirdPartyInvoice, $thirdPartyNegotiation, $selectedCurrency, $baseCurrency
  Optional: $summaryColspan (default 7), $invoice (for markup from tour)
--}}
@php
    $summaryColspan = $summaryColspan ?? 7;
    $negRows = (!empty($isThirdPartyInvoice) && !empty($thirdPartyNegotiation['rows']))
        ? $thirdPartyNegotiation['rows']
        : [];

    $tourForMarkup = $invoice->tour ?? ($tour ?? null);
    $pricingMarkup = \App\Helpers\CommonHelper::buildInvoicePricingMarkupDisplay(
        $tourForMarkup,
        (float) ($actualAmount ?? 0),
        (float) ($negotiatedAmount ?? $actualAmount ?? 0),
        (string) ($selectedCurrency ?? $baseCurrency ?? 'SGD'),
        !empty($thirdPartyNegotiation) && is_array($thirdPartyNegotiation) ? $thirdPartyNegotiation : null
    );

    $payTrim = static function ($n) {
        return rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
    };
    $payTypeLabel = static function ($type, $raw) use ($payTrim) {
        $type = strtolower(trim((string) $type));
        $raw = (float) $raw;
        if ($type === 'percentage') {
            return $payTrim($raw) . '%';
        }
        if ($type === 'foc') {
            return 'FOC';
        }
        if ($type === 'flat' || $type === 'fixed') {
            return $raw > 0 ? ('Fixed ' . $payTrim($raw)) : 'Fixed';
        }
        return $raw > 0 ? $payTrim($raw) : '—';
    };
    $mkCur = $pricingMarkup['currency'] ?? ($selectedCurrency ?? '');
@endphp

@if(!empty($pricingMarkup['has_markup_display']))
<tr style="background-color: #eef2ff;">
    <td colspan="{{ $summaryColspan + 1 }}" style="font-weight: 700; color: #312e81; padding: 8px 10px;">
        Pricing Breakdown (Before / After Negotiation)
    </td>
</tr>
<tr style="background-color: #f8fafc;">
    <td colspan="{{ $summaryColspan }}" class="text-right" style="color: #64748b;">Gross (before markup)</td>
    <td class="text-right">{{ $formatPrice($pricingMarkup['gross']) }}</td>
</tr>
<tr style="background-color: #f0f9ff;">
    <td colspan="{{ $summaryColspan }}" class="text-right" style="color: #334155;">
        Hotel Markup
        <span style="color: #64748b; font-weight: normal;">({{ $payTypeLabel($pricingMarkup['markup_type'], $pricingMarkup['hotel_markup_raw']) }})</span>
    </td>
    <td class="text-right">
        @if(($pricingMarkup['markup_type'] ?? '') === 'flat' || ($pricingMarkup['markup_type'] ?? '') === 'fixed')
            +{{ $formatPrice($pricingMarkup['hotel_markup_money']) }}
        @else
            {{ $payTypeLabel($pricingMarkup['markup_type'], $pricingMarkup['hotel_markup_raw']) }}
        @endif
    </td>
</tr>
<tr style="background-color: #f0f9ff;">
    <td colspan="{{ $summaryColspan }}" class="text-right" style="color: #334155;">
        Other Service Markup
        <span style="color: #64748b; font-weight: normal;">({{ $payTypeLabel($pricingMarkup['markup_type'], $pricingMarkup['other_markup_raw']) }})</span>
    </td>
    <td class="text-right">
        @if(($pricingMarkup['markup_type'] ?? '') === 'flat' || ($pricingMarkup['markup_type'] ?? '') === 'fixed')
            +{{ $formatPrice($pricingMarkup['other_markup_money']) }}
        @else
            {{ $payTypeLabel($pricingMarkup['markup_type'], $pricingMarkup['other_markup_raw']) }}
        @endif
    </td>
</tr>
<tr style="background-color: #edf7ed;">
    <td colspan="{{ $summaryColspan }}" class="text-right" style="color: #334155;">
        Discount
        <span style="color: #64748b; font-weight: normal;">({{ $payTypeLabel($pricingMarkup['discount_type'], $pricingMarkup['discount_raw']) }})</span>
    </td>
    <td class="text-right">−{{ $formatPrice($pricingMarkup['discount_money']) }}</td>
</tr>
<tr style="background-color: #e7f3ff;">
    <td colspan="{{ $summaryColspan }}" class="text-right"><strong>Actual Price</strong> <span style="color: #64748b; font-weight: normal;">(Gross + markup − discount)</span></td>
    <td class="text-right"><strong>{{ $formatPrice($pricingMarkup['actual_price']) }}</strong></td>
</tr>
<tr style="background-color: #d4edda;">
    <td colspan="{{ $summaryColspan }}" class="text-right"><strong>Confirmed Price</strong> <span style="color: #64748b; font-weight: normal;">(After negotiation)</span></td>
    <td class="text-right"><strong>{{ $formatPrice($pricingMarkup['confirmed_price']) }}</strong></td>
</tr>

@if(!empty($pricingMarkup['markup_rows']))
<tr style="background-color: #f1f5f9;">
    <td colspan="{{ $summaryColspan + 1 }}" style="padding: 6px 10px;">
        <div style="font-weight: 700; color: #475569; margin-bottom: 4px;">City / Country Markup &amp; Discount</div>
        <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
            <thead>
                <tr style="background-color: #f8fafc;">
                    <th style="text-align: left; padding: 4px 6px; border: 1px solid #e2e8f0; color: #64748b; font-size: 10px; text-transform: uppercase;">City / Country</th>
                    <th style="text-align: left; padding: 4px 6px; border: 1px solid #e2e8f0; color: #64748b; font-size: 10px; text-transform: uppercase;">Hotel</th>
                    <th style="text-align: left; padding: 4px 6px; border: 1px solid #e2e8f0; color: #64748b; font-size: 10px; text-transform: uppercase;">Other</th>
                    <th style="text-align: left; padding: 4px 6px; border: 1px solid #e2e8f0; color: #64748b; font-size: 10px; text-transform: uppercase;">Discount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pricingMarkup['markup_rows'] as $mkRow)
                <tr>
                    <td style="padding: 4px 6px; border: 1px solid #e2e8f0; color: #334155;">
                        {{ $mkRow['place'] }}
                        @if(!empty($mkRow['currency']))
                            <span style="color: #64748b;">({{ $mkRow['currency'] }})</span>
                        @endif
                    </td>
                    <td style="padding: 4px 6px; border: 1px solid #e2e8f0;">{{ $payTypeLabel($mkRow['markup_type'], $mkRow['hotel_raw']) }}</td>
                    <td style="padding: 4px 6px; border: 1px solid #e2e8f0;">{{ $payTypeLabel($mkRow['markup_type'], $mkRow['other_raw']) }}</td>
                    <td style="padding: 4px 6px; border: 1px solid #e2e8f0;">{{ $payTypeLabel($mkRow['discount_type'], $mkRow['discount_raw']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </td>
</tr>
@endif

<tr>
    <td colspan="{{ $summaryColspan + 1 }}" style="border-top: 1px solid #cbd5e1; padding: 4px 0;"></td>
</tr>
@endif

@if(count($negRows) > 0)
<tr style="background-color: #f1f5f9;">
    <td colspan="{{ $summaryColspan + 1 }}" style="font-weight: 700; color: #1e293b; padding: 8px 10px;">
        Country-wise Negotiation
    </td>
</tr>
@foreach($negRows as $negRow)
@php
    $negCountry = trim((string) ($negRow['country'] ?? ''));
    $negCurrency = strtoupper(trim((string) ($negRow['currency'] ?? $selectedCurrency)));
    $negLabel = $negCountry !== '' ? $negCountry : $negCurrency;
    $rowGross = (float) ($negRow['gross_selected'] ?? $negRow['gross'] ?? 0);
    $rowActual = (float) ($negRow['actual_selected'] ?? 0);
    $rowNeg = (float) ($negRow['negotiated_selected'] ?? 0);
    $rowDisc = (float) ($negRow['discount_selected'] ?? ($rowActual - $rowNeg));
    $rowHotel = (float) ($negRow['hotel_markup'] ?? 0);
    $rowOther = (float) ($negRow['other_markup'] ?? 0);
    $rowMkType = strtolower(trim((string) ($negRow['markup_type'] ?? 'flat')));
    $rowDiscType = strtolower(trim((string) ($negRow['discount_type'] ?? 'flat')));
    $rowDiscVal = (float) ($negRow['discount_value'] ?? 0);
@endphp
@if($rowGross > 0 && abs($rowGross - $rowActual) > 0.009)
<tr style="background-color: #f8fafc;">
    <td colspan="{{ $summaryColspan }}" class="text-right" style="color: #64748b;">
        <strong>{{ $negLabel }}</strong>
        <span style="color: #64748b; font-weight: normal;"> ({{ $negCurrency }})</span>
        — Gross
    </td>
    <td class="text-right">{{ $formatPrice($rowGross) }}</td>
</tr>
@endif
@if($rowHotel > 0 || $rowOther > 0 || $rowDiscVal > 0)
<tr style="background-color: #f0f9ff;">
    <td colspan="{{ $summaryColspan }}" class="text-right" style="color: #475569; font-size: 11px;">
        {{ $negLabel }} — Markup:
        Hotel {{ $payTypeLabel($rowMkType, $rowHotel) }}
        · Other {{ $payTypeLabel($rowMkType, $rowOther) }}
        · Disc {{ $payTypeLabel($rowDiscType, $rowDiscVal) }}
    </td>
    <td class="text-right" style="font-size: 11px; color: #64748b;">—</td>
</tr>
@endif
<tr style="background-color: #f8fafc;">
    <td colspan="{{ $summaryColspan }}" class="text-right" style="color: #334155;">
        <strong>{{ $negLabel }}</strong>
        <span style="color: #64748b; font-weight: normal;"> ({{ $negCurrency }})</span>
        — Actual
    </td>
    <td class="text-right">{{ $formatPrice($rowActual) }}</td>
</tr>
<tr style="background-color: #eef6ff;">
    <td colspan="{{ $summaryColspan }}" class="text-right" style="color: #334155;">
        {{ $negLabel }} — Negotiated
    </td>
    <td class="text-right">{{ $formatPrice($rowNeg) }}</td>
</tr>
@if(abs($rowDisc) > 0.009)
<tr style="background-color: {{ $rowDisc > 0 ? '#edf7ed' : '#fff8e6' }};">
    <td colspan="{{ $summaryColspan }}" class="text-right" style="color: #334155;">
        {{ $negLabel }} — {{ $rowDisc > 0 ? 'Discount' : 'Additional Charges' }}
    </td>
    <td class="text-right">
        <strong>{{ $rowDisc > 0 ? '-' : '' }}{{ $formatPrice(abs($rowDisc)) }}</strong>
    </td>
</tr>
@endif
@endforeach
<tr>
    <td colspan="{{ $summaryColspan + 1 }}" style="border-top: 1px solid #cbd5e1; padding: 4px 0;"></td>
</tr>
@endif

<tr>
    <td colspan="{{ $summaryColspan }}" class="text-right"><strong>Total (Actual Amount):</strong></td>
    <td class="text-right"><strong>{{ $formatPrice($actualAmount) }}</strong></td>
</tr>
@if($negotiatedAmount !== null)
<tr style="background-color: #e7f3ff;">
    <td colspan="{{ $summaryColspan }}" class="text-right"><strong>Last Negotiated Amount:</strong></td>
    <td class="text-right"><strong>{{ $formatPrice($negotiatedAmount) }}</strong></td>
</tr>
@if($discount > 0)
<tr style="background-color: #d4edda;">
    <td colspan="{{ $summaryColspan }}" class="text-right"><strong>Total Discount:</strong></td>
    <td class="text-right"><strong>-{{ $formatPrice($discount) }}</strong></td>
</tr>
@elseif($discount < 0)
<tr style="background-color: #fff3cd;">
    <td colspan="{{ $summaryColspan }}" class="text-right"><strong>Total Additional Charges:</strong></td>
    <td class="text-right"><strong>{{ $formatPrice(abs($discount)) }}</strong></td>
</tr>
@endif
@endif

@if($shouldShowTax && $gstAmount > 0)
@if(!empty($taxBreakdown))
    @foreach($taxBreakdown as $taxName => $taxValue)
    <tr style="background-color: #fff3cd;">
        <td colspan="{{ $summaryColspan }}" class="text-right"><strong>{{ $taxName }}:</strong></td>
        <td class="text-right"><strong>{{ $formatPrice($taxValue) }}</strong></td>
    </tr>
    @endforeach
@else
<tr style="background-color: #fff3cd;">
    <td colspan="{{ $summaryColspan }}" class="text-right"><strong>Total Vat / GST Tax:</strong></td>
    <td class="text-right"><strong>{{ $formatPrice($gstAmount) }}</strong></td>
</tr>
@endif
@endif

<tr style="background-color: #d4edda;">
    <td colspan="{{ $summaryColspan }}" class="text-right"><strong>Final Price:</strong></td>
    <td class="text-right"><strong>{{ $formatPrice($finalPrice) }}</strong></td>
</tr>

@if($shouldShowTax)
<tr style="background-color: #d1ecf1;">
    <td colspan="{{ $summaryColspan }}" class="text-right"><strong>Payment Received:</strong></td>
    <td class="text-right"><strong>{{ $formatPrice($paymentReceived) }}</strong></td>
</tr>
<tr style="background-color: #f8d7da;">
    <td colspan="{{ $summaryColspan }}" class="text-right"><strong>Outstanding Balance:</strong></td>
    <td class="text-right"><strong>{{ $formatPrice($outstandingBalance) }}</strong></td>
</tr>
@endif
