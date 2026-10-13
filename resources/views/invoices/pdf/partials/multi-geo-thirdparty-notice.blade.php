@if(!empty($showMultiGeoThirdPartyNotice))
@php
    $multiCountries = $invoiceMultiGeo['countries'] ?? [];
    $multiCities = $invoiceMultiGeo['cities'] ?? [];
    $geoHint = '';
    if (!empty($multiCountries)) {
        $geoHint = implode(', ', $multiCountries);
    } elseif (!empty($multiCities)) {
        $geoHint = implode(', ', $multiCities);
    }
    $geoHintSuffix = $geoHint !== '' ? ' (' . $geoHint . ')' : '';
@endphp
<div style="margin: 12px 0 16px; padding: 10px 12px; border: 1px solid #f59e0b; background: #fffbeb; color: #92400e; font-size: 11px; line-height: 1.45;">
    <strong style="display: block; margin-bottom: 4px;">Multi-country / multi-city booking</strong>
    This itinerary covers more than one country or city{{ $geoHintSuffix }}.
    Country-wise service groups and negotiation are not shown because multi-country pricing is not enabled for this DMC.
    Please enable multi-country / third-party pricing in the DMC profile settings to display amounts separately by country, city, and currency.
</div>
@endif
