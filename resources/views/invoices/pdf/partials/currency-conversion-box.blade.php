@if($showCurrencyConversion)
@php
    // Prefer Final Price (or Outstanding when tax statuses apply) so conversion matches summary.
    if (isset($syncInvoiceCurrencyConversion) && is_callable($syncInvoiceCurrencyConversion)) {
        $convSource = null;
        if (!empty($shouldShowTax) && isset($outstandingBalance)) {
            $convSource = $outstandingBalance;
        } elseif (isset($finalPrice)) {
            $convSource = $finalPrice;
        } elseif (isset($negotiatedAmount)) {
            $convSource = $negotiatedAmount;
        }
        if ($convSource !== null) {
            $syncInvoiceCurrencyConversion($convSource);
        }
    }
@endphp
<!-- Currency Conversion (aligned with Final Price / Outstanding above) -->
<div class="currency-section">
    <table class="currency-table">
        <thead>
            <tr>
                <th colspan="{{ max(1, count($currencyConversion)) }}" class="text-center">Currency Conversion</th>
            </tr>
            <tr>
                @foreach(array_keys($currencyConversion) as $curr)
                <th>{{ $curr }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
                @foreach($currencyConversion as $curr => $amount)
                <td>{{ number_format(round((float) $amount, 2), 2) }}</td>
                @endforeach
            </tr>
        </tbody>
    </table>
    <div style="font-size: 10px; color: #64748b; margin-top: 4px;">
        Equivalent of {{ !empty($shouldShowTax) ? 'Outstanding Balance' : 'Final Price' }} in each currency (live rate).
    </div>
</div>
@endif
