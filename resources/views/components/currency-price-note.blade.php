@props([
    'watchDmc' => false,
    'dmcCurrency' => null,
    'dmcSelected' => false,
    'currency' => null,
    'country' => null,
    'watchCountry' => false,
    'countrySelectId' => 'country',
])

@php
    $productCountry = filled($country) ? trim((string) $country) : null;
    $productCurrency = null;

    if (filled($currency)) {
        $productCurrency = strtoupper(trim((string) $currency));
    } elseif (filled($productCountry)) {
        $productCurrency = \App\Models\Country::where('name', $productCountry)->value('currency');
        if (!filled($productCurrency)) {
            $productCurrency = \App\Models\Country::whereRaw(
                'LOWER(TRIM(name)) = ?',
                [strtolower($productCountry)]
            )->value('currency');
        }
        $productCurrency = filled($productCurrency) ? strtoupper(trim((string) $productCurrency)) : null;
    }

    $isAdminDmcMode = $watchDmc && in_array(auth()->user()->role_id ?? null, [1, 20]);
    $shouldWatchCountry = (bool) $watchCountry;

    $countryCurrencyMap = [];
    if ($shouldWatchCountry || $isAdminDmcMode) {
        $countryCurrencyMap = \App\Models\Country::query()
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->whereNotNull('currency')
            ->where('currency', '!=', '')
            ->pluck('currency', 'name')
            ->map(fn ($c) => strtoupper(trim((string) $c)))
            ->toArray();
    }

    if ($isAdminDmcMode && $dmcSelected) {
        $noteText = filled($dmcCurrency)
            ? "* For The Selected DMC Currency is {$dmcCurrency}"
            : '* For The Selected DMC Currency is not set';
    } elseif (filled($productCurrency)) {
        $noteText = "* Product currency is {$productCurrency}";
    } else {
        $noteText = '* Product currency is not set';
    }

    $noteUid = 'cpn-' . substr(md5(($productCountry ?? '') . ($countrySelectId ?? '') . microtime(true)), 0, 10);
@endphp

<small
    id="{{ $noteUid }}"
    class="currency-price-note"
    data-admin-dmc-mode="{{ $isAdminDmcMode ? '1' : '0' }}"
    data-product-currency="{{ $productCurrency }}"
    data-product-country="{{ $productCountry }}"
    data-watch-country="{{ $shouldWatchCountry ? '1' : '0' }}"
    data-country-select-id="{{ $countrySelectId }}"
    data-country-map-id="{{ $noteUid }}-map"
    style="color: #8B0000; font-size: 0.65rem; line-height: 1.2;"
>
    <span class="currency-price-note-text">{{ $noteText }}</span>
</small>
@if($shouldWatchCountry || $isAdminDmcMode)
<script type="application/json" id="{{ $noteUid }}-map">{!! json_encode($countryCurrencyMap, JSON_UNESCAPED_UNICODE) !!}</script>
@endif
