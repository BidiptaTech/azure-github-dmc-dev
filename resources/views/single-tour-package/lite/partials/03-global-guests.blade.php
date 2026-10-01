{{-- === STP LITE: 03-global-guests ===
     Depends: guest-caps.js, tour-type.js (GROUP box)
     Owns: global guest display + hidden fields + GROUP FOC fields (backup names)
     === --}}
@php
    $enq = $enquiry ?? null;
    $adults = $enq ? (int) ($enq->adult ?? 1) : 1;
    $male = $enq ? (int) ($enq->male_count ?? 0) : 0;
    $female = $enq ? (int) ($enq->female_count ?? 0) : 0;
    $children = $enq ? (int) ($enq->child ?? 0) : 0;
    $infants = $enq ? (int) ($enq->infant ?? 0) : 0;
    $childAges = $enq && $enq->child_ages ? $enq->child_ages : '[]';
    $guestsLocked = (bool) $enq;
@endphp
<div class="stp-lite-card" id="stpLiteGlobalGuests">
    <div class="stp-lite-card-header">
        <div>
            <h2>Guest Information (Global)</h2>
            <small>These counts cap every service section — children = 0 hides child fields below</small>
        </div>
    </div>
    <div class="stp-lite-card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="stp-lite-label"><i class="ri-group-line me-1"></i>Guests</label>
                <div class="stp-lite-guest-display" @if($guestsLocked) style="opacity:0.85;cursor:not-allowed;" @endif>
                    <div id="mainGuestSummary" class="stp-lite-guest-badges flex-column align-items-start"></div>
                    @if($guestsLocked)
                        <span class="text-muted"><i class="ri-lock-line"></i></span>
                    @else
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="openMainGuestSelector()">
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
            </div>

            <div class="col-md-6">
                <div id="groupDetailsWrapper" class="stp-lite-group-box d-none">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="fw-semibold" style="font-size:0.85rem;color:#1e3a5f;">
                            <i class="ri-group-2-line me-1"></i>Group Details
                        </div>
                        <span class="badge text-bg-primary" style="font-size:0.68rem;">FOC</span>
                    </div>
                    <div class="row g-2">
                        <input type="hidden" id="group_size" name="group_size" value="{{ old('group_size') }}">
                        <div class="col-6">
                            <label class="stp-lite-label">Group Size</label>
                            <div class="input-group input-group-sm">
                                <input type="number" min="0" step="1" class="form-control stp-lite-int" id="group_size_display" value="0" readonly>
                                <span class="input-group-text">pax</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <label for="foc_size" class="stp-lite-label">FOC Size</label>
                            <div class="input-group input-group-sm">
                                <input type="number" min="0" step="1" class="form-control stp-lite-int" id="foc_size" name="foc_size" value="{{ old('foc_size', 0) }}">
                                <span class="input-group-text">pax</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_foc_in_group_price" name="include_foc_in_group_price" value="1" checked>
                                <label class="form-check-label" for="include_foc_in_group_price" style="font-size:0.78rem;">
                                    Treat FOC pax as discount (free)
                                </label>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="stp-lite-label">Paying Pax</label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control" id="paying_pax" name="paying_pax" value="{{ old('paying_pax') }}" readonly>
                                <span class="input-group-text">pax</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="stp-lite-label">Total Pax</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="total_pax_display" value="0" readonly>
                                <span class="input-group-text">pax</span>
                            </div>
                        </div>
                        <input type="hidden" id="discount" name="discount" value="{{ old('discount', 1) }}">
                        <input type="hidden" id="auto_foc" name="auto_foc" value="{{ old('auto_foc', 0) }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- === END 03-global-guests === --}}
