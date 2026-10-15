{{-- === STP LITE: lite-boot ===
     Depends: $dmcGroupPax / $UserDmc / $isThirdPartyDmc from controller
     Owns: STP_LITE_CONFIG + lite-overrides.js (auto toggles, sanitize, room names, accordion)
     === --}}
@php
    $liteGroupPax = (int) ($dmcGroupPax ?? optional($UserDmc ?? null)->group_pax ?? 0);
    $liteThirdParty = !empty($isThirdPartyDmc)
        ? true
        : (strtolower(trim((string) (optional($UserDmc ?? null)->thirdparty ?? 'no'))) === 'yes');
    $liteRestricted = !empty($isRestrictedThirdParty);
    $liteOwnCountries = array_values($ownDmcCountries ?? []);
@endphp
<script>
window.STP_LITE_CONFIG = Object.assign({}, window.STP_LITE_CONFIG || {}, {
    dmcGroupPax: @json($liteGroupPax),
    isThirdPartyDmc: @json($liteThirdParty),
    isRestrictedThirdParty: @json($liteRestricted),
    ownDmcCountries: @json($liteOwnCountries),
    dmcCurrency: @json($dmcCurrency ?? (optional($UserDmc ?? null)->currency ?? 'SGD')),
    zoneOn: @json((int) (optional($UserDmc ?? null)->zone_on ?? 0)),
    siblingDmcZoneOnMap: @json($siblingDmcZoneOnMap ?? new \stdClass())
});
</script>
<script src="{{ asset('js/single-tour-package/lite/lite-overrides.js') }}?v={{ @filemtime(public_path('js/single-tour-package/lite/lite-overrides.js')) }}"></script>
{{-- === END lite-boot === --}}
