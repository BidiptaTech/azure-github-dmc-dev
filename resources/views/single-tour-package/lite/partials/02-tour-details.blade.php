{{-- === STP LITE: 02-tour-details ===
     Depends: tour-details.js, guest-caps.js, country-mode.js, select2, daterangepicker
     Owns: reference, cities (MDMC-scoped multi-select; badges inside Select2), dates, guests (compact), agency, agent
     Country (Master DMC) is NOT shown — inferred from selected cities
     === --}}
@php
    $enq = $enquiry ?? null;
    $isLiteEdit = !empty($tour) && !empty($tour->tour_id);
    // Enquiry create locks guests/dates/agency; edit prefills but stays editable
    $lockEnquiryFields = (bool) $enq && !$isLiteEdit;
    $datesLocked = $lockEnquiryFields && $enq->check_in_time && $enq->check_out_time;
    $startYmd = $enq && $enq->check_in_time ? \Carbon\Carbon::parse($enq->check_in_time)->format('Y-m-d') : '';
    $endYmd = $enq && $enq->check_out_time ? \Carbon\Carbon::parse($enq->check_out_time)->format('Y-m-d') : '';
    $datesDisplay = ($startYmd && $endYmd)
        ? \Carbon\Carbon::parse($startYmd)->format('M d, Y') . ' - ' . \Carbon\Carbon::parse($endYmd)->format('M d, Y')
        : '';
    $adults = $enq ? (int) ($enq->adult ?? 1) : 1;
    $male = $enq ? (int) ($enq->male_count ?? 0) : 0;
    $female = $enq ? (int) ($enq->female_count ?? 0) : 0;
    $children = $enq ? (int) ($enq->child ?? 0) : 0;
    $infants = $enq ? (int) ($enq->infant ?? 0) : 0;
    $childAges = $enq && $enq->child_ages ? $enq->child_ages : '[]';
    $guestsLocked = $lockEnquiryFields;
    $enqAgent = ($enq && isset($enq->agent)) ? $enq->agent : null;
    $enqAgencyId = $enqAgent->agency_id
        ?? optional(($agents ?? collect())->firstWhere('agent_id', $enq->agent_id ?? null))->agency_id
        ?? ($tour->agency_id ?? null);
@endphp
<div class="stp-lite-card" id="stpLiteTourDetails">
    <div class="stp-lite-card-header">
        <div>
            <h2>Tour Details</h2>
            <small>Cities from Master DMC · guests cap every section below</small>
        </div>
    </div>
    <div class="stp-lite-card-body">
        <div class="row g-2 align-items-start">
            <div class="col-lg-3 col-md-6">
                <label for="reference_number" class="stp-lite-label"><i class="ri-hashtag me-1"></i>Reference</label>
                <input type="text" name="reference_number" id="reference_number" class="form-control"
                       placeholder="Reference number"
                       value="{{ old('reference_number', $enq->reference_number ?? '') }}">
            </div>

            <div class="col-lg-5 col-md-6" id="tourCitiesWrap">
                <label for="tour_cities" class="stp-lite-label"><i class="ri-map-pin-line me-1"></i>City</label>
                <select id="tour_cities" class="form-select" multiple data-placeholder="Select city…"></select>
                {{-- Hidden mirrors for store / planner (backup field names) --}}
                <select id="single_city" name="city" class="d-none" aria-hidden="true">
                    <option value="">Select city...</option>
                </select>
                <select id="multi_cities" class="d-none" multiple aria-hidden="true"></select>
                <div id="mdmcCountryWarn" class="alert alert-warning stp-lite-alert mt-2 d-none mb-0"></div>
            </div>

            <div class="col-lg-4 col-md-12">
                <label class="stp-lite-label"><i class="ri-group-line me-1"></i>Guests</label>
                <div class="stp-lite-guest-display stp-lite-guest-display--compact" @if($guestsLocked) style="opacity:0.85;cursor:not-allowed;" @endif>
                    <div id="mainGuestSummary" class="stp-lite-guest-badges"></div>
                    @if($guestsLocked)
                        <span class="text-muted"><i class="ri-lock-line"></i></span>
                    @else
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="openMainGuestSelector()" title="Edit guests">
                            <i class="ri-edit-line"></i>
                        </button>
                    @endif
                </div>
                <input type="hidden" name="adults" id="adults" value="{{ $adults }}">
                <input type="hidden" name="male" id="male" value="{{ $male }}">
                <input type="hidden" name="female" id="female" value="{{ $female }}">
                <input type="hidden" name="children" id="children" value="{{ $children }}">
                <input type="hidden" name="infants" id="infants" value="{{ $infants }}">
                <input type="hidden" name="child_ages" id="child_ages" value="{{ $childAges }}">

                <div id="groupDetailsWrapper" class="stp-lite-group-box d-none mt-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="fw-semibold" style="font-size:0.78rem;color:#1e3a5f;"><i class="ri-group-2-line me-1"></i>Group / FOC</span>
                    </div>
                    <div class="row g-1">
                        <input type="hidden" id="group_size" name="group_size" value="{{ old('group_size') }}">
                        <div class="col-4">
                            <label class="stp-lite-label mb-0" style="font-size:0.68rem;">Group</label>
                            <input type="number" min="0" class="form-control form-control-sm stp-lite-int" id="group_size_display" value="0" readonly>
                        </div>
                        <div class="col-4">
                            <label class="stp-lite-label mb-0" style="font-size:0.68rem;">FOC</label>
                            <input type="number" min="0" class="form-control form-control-sm stp-lite-int" id="foc_size" name="foc_size" value="{{ old('foc_size', 0) }}">
                        </div>
                        <div class="col-4">
                            <label class="stp-lite-label mb-0" style="font-size:0.68rem;">Paying</label>
                            <input type="number" class="form-control form-control-sm" id="paying_pax" name="paying_pax" value="{{ old('paying_pax') }}" readonly>
                        </div>
                        <div class="col-12 mt-1">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="include_foc_in_group_price" name="include_foc_in_group_price" value="1" checked>
                                <label class="form-check-label" for="include_foc_in_group_price" style="font-size:0.72rem;">Treat FOC as discount</label>
                            </div>
                        </div>
                        <input type="hidden" id="total_pax_display" value="0">
                        <input type="hidden" id="discount" name="discount" value="{{ old('discount', 1) }}">
                        <input type="hidden" id="auto_foc" name="auto_foc" value="{{ old('auto_foc', 0) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Countries inferred from cities (not shown). Kept for store + MDMC checks. --}}
        <select id="lite_countries" class="d-none" multiple aria-hidden="true">
            @foreach($countries as $country)
                <option value="{{ $country->name }}" data-country-id="{{ $country->id }}"
                    {{ ($enq && $enq->country == $country->name) ? 'selected' : '' }}>
                    {{ $country->name }}
                </option>
            @endforeach
        </select>
        <select name="user_country" id="user_country" class="d-none" required>
            <option value="">Choose a country...</option>
            @foreach($countries as $country)
                <option value="{{ $country->name }}" data-country-id="{{ $country->id }}"
                    {{ ($enq && $enq->country == $country->name) ? 'selected' : '' }}>
                    {{ $country->name }}
                </option>
            @endforeach
        </select>
        <input type="hidden" name="country_id" id="country_id" value="">

        <div class="row g-2 mt-2">
            <div class="col-md-4">
                <label for="travel_dates" class="stp-lite-label"><i class="ri-calendar-line me-1"></i>Travel Dates</label>
                <input type="text" id="travel_dates" class="form-control" placeholder="Select dates" readonly
                       value="{{ $datesDisplay }}"
                       data-locked="{{ $datesLocked ? 'true' : 'false' }}"
                       @if($datesLocked) style="background:#f8fafc;cursor:not-allowed;" @endif>
                <input type="hidden" name="start_date" id="start_date" value="{{ $startYmd }}">
                <input type="hidden" name="end_date" id="end_date" value="{{ $endYmd }}">
            </div>

            <div class="col-md-4">
                <label for="agency_id" class="stp-lite-label"><i class="ri-building-line me-1"></i>Agency Company</label>
                <select name="agency_id" id="agency_id" class="form-select" {{ ($lockEnquiryFields && $enq && $enq->agent_id) ? 'disabled' : '' }}>
                    <option value="">Choose agency...</option>
                    @foreach($agency as $agnc)
                        <option value="{{ $agnc->agency_id }}"
                            {{ ($enqAgencyId && (string) $enqAgencyId === (string) $agnc->agency_id) ? 'selected' : '' }}>
                            {{ $agnc->agency_name }}
                        </option>
                    @endforeach
                </select>
                @if($lockEnquiryFields && $enq && $enq->agent_id)
                    <input type="hidden" name="agency_id" value="{{ $enqAgencyId ?? '' }}">
                @endif
            </div>

            <div class="col-md-4">
                <label for="agent_id" class="stp-lite-label"><i class="ri-user-star-line me-1"></i>Agency Contact</label>
                <select name="agent_id" id="agent_id" class="form-select" required {{ ($lockEnquiryFields && $enq && $enq->agent_id) ? 'disabled' : '' }}>
                    <option value="">Choose agency contact...</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->agent_id }}"
                            data-agency="{{ $agent->agency_id }}"
                            {{ ($enq && $enq->agent_id == $agent->agent_id) ? 'selected' : '' }}>
                            {{ $agent->name }}
                        </option>
                    @endforeach
                </select>
                @if($lockEnquiryFields && $enq && $enq->agent_id)
                    <input type="hidden" name="agent_id" value="{{ $enq->agent_id }}">
                @endif
            </div>
        </div>
    </div>
</div>
{{-- === END 02-tour-details === --}}
{{-- Modal is included from create/edit after the main form (must not nest forms) --}}
