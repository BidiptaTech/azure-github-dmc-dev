{{-- === STP LITE: 01-config-toggles ===
     Depends: $isThirdPartyDmc, tour-type.js, country-mode.js
     Owns: FIT/GROUP + Single/Multi Country read-only toggles + hidden submit values
     === --}}
<div class="stp-lite-card" id="stpLiteConfigToggles">
    <div class="stp-lite-card-header">
        <div>
            <h2>Tour Configuration</h2>
            <small>FIT/GROUP and country mode switch automatically — you cannot change them manually</small>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <div class="tour-type-wrapper" style="min-width: 200px;">
                <div class="stp-lite-toggle tour-type is-locked" title="Auto from DMC Group Pax vs adults+children">
                    <input type="radio" name="tour_type_ui" id="fit" value="FIT" checked disabled>
                    <label for="fit">FIT</label>
                    <input type="radio" name="tour_type_ui" id="group" value="GROUP" disabled>
                    <label for="group">GROUP</label>
                    <span class="slider"></span>
                </div>
                <div class="stp-lite-toggle-hint" id="tourTypeAutoHint">Auto from DMC Group Pax.</div>
            </div>

            <div style="min-width: 230px;">
                <div class="stp-lite-toggle country-mode is-locked{{ !empty($isThirdPartyDmc) ? ' is-thirdparty-disabled' : '' }}"
                     title="{{ !empty($isThirdPartyDmc) ? 'Multi Country locked for 3rd party DMC' : 'Auto from selected countries' }}">
                    <input type="radio" name="city_mode_ui" id="city_mode_single" value="single" checked disabled>
                    <label for="city_mode_single">Single Country</label>
                    <input type="radio" name="city_mode_ui" id="city_mode_multi" value="multi" disabled>
                    <label for="city_mode_multi">Multi Country</label>
                    <span class="slider"></span>
                </div>
                <div class="stp-lite-toggle-hint" id="countryModeAutoHint">Auto Single / Multi Country.</div>
            </div>
        </div>
    </div>

    {{-- Posted values (disabled radios are not submitted) — same names/values as backup --}}
    <input type="hidden" name="tour_type" id="tour_type_value" value="FIT">
    <input type="hidden" name="city_mode" id="city_mode_value" value="single">
    <input type="hidden" name="city_type" id="city_type_value" value="single">
</div>
{{-- === END 01-config-toggles === --}}
