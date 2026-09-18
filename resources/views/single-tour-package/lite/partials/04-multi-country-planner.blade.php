{{-- === STP LITE: 04-multi-country-planner ===
     Depends: country-segments.js — auto rows from selected cities; user sets stay dates only
     Services + Continue stay locked until every city has a valid date range
     === --}}
<div id="multiCountryPlanner" class="stp-lite-card d-none">
    <div class="stp-lite-card-header">
        <div>
            <h2>Stay dates (per city)</h2>
            <small>One row per city — set Stay from / until. Tick <strong>Return</strong> to add another stay for the same city.</small>
        </div>
        <span id="stayDatesStatus" class="badge text-bg-secondary" style="font-size:0.72rem;">Dates required</span>
    </div>
    <div class="stp-lite-card-body">
        <div id="segmentsWrapper"></div>
        <div id="stayDatesHint" class="stp-lite-alert alert alert-warning py-2 px-3 mt-2 mb-0">
            Set a date range for every stay (including Return stays) before Hotel and other services unlock.
        </div>
    </div>
</div>

{{-- Country-tinted service hosts — gated until stay dates ready --}}
<div id="countrySectionsGate" class="stp-lite-services-gate is-locked mt-2">
    <div class="stp-lite-services-gate__overlay" id="servicesLockedOverlay">
        <div>
            <i class="ri-lock-line" style="font-size:1.4rem;"></i>
            <div class="fw-semibold mt-1">Services locked</div>
            <small>Complete stay dates for every selected city first.</small>
        </div>
    </div>
    <div id="countrySectionsHost"></div>
</div>

<input type="hidden" name="tour_package_currency" id="tour_package_currency" value="{{ $dmcCurrency ?? 'SGD' }}">
{{-- === END 04-multi-country-planner === --}}
