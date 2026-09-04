{{--
  Country-wise negotiation breakdown + shared summary rows for standard invoice PDFs.
  Expects: $formatPrice, $actualAmount, $negotiatedAmount, $discount, $finalPrice,
           $gstAmount, $shouldShowTax, $taxBreakdown, $paymentReceived, $outstandingBalance,
           $isThirdPartyInvoice, $thirdPartyNegotiation, $selectedCurrency, $baseCurrency
  Optional: $summaryColspan (default 7)
--}}
@php
    $summaryColspan = $summaryColspan ?? 7;
    $negRows = (!empty($isThirdPartyInvoice) && !empty($thirdPartyNegotiation['rows']))
        ? $thirdPartyNegotiation['rows']
        : [];
@endphp

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
    $rowActual = (float) ($negRow['actual_selected'] ?? 0);
    $rowNeg = (float) ($negRow['negotiated_selected'] ?? 0);
    $rowDisc = (float) ($negRow['discount_selected'] ?? ($rowActual - $rowNeg));
@endphp
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
