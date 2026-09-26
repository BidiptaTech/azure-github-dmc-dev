{{-- === STP LITE: Lead Guest + Additional Guests (backup field IDs / names) === --}}
@php
    $tourStatusNorm = strtolower(trim((string) ($tour->tour_status ?? '')));
    // App Password only for Definite / Actual (not Confirmed)
    $showAppPassword = in_array($tourStatusNorm, ['definite', 'actual'], true);
@endphp
<div id="guestInfoSection" class="stp-lite-guest-info mt-3" data-show-app-password="{{ $showAppPassword ? '1' : '0' }}">
    <div class="stp-lite-card" id="customerAccordion">
        <div class="stp-lite-card-header stp-lite-guest-head" role="button" data-bs-toggle="collapse" data-bs-target="#customerInformationSection" aria-expanded="true">
            <div>
                <h2 class="mb-0">
                    <i class="ri-user-line me-1"></i>Lead Guest information
                    @if($showAppPassword)
                        <span class="badge bg-secondary ms-1" style="font-size:0.65rem;">App Password</span>
                    @endif
                </h2>
                <small class="text-muted">Customer details posted as <code>mainguest</code> (backup format)</small>
            </div>
            <i class="ri-arrow-down-s-line"></i>
        </div>
        <div id="customerInformationSection" class="collapse show">
            <div class="stp-lite-card-body p-3">
                <div class="row g-2">
                    <div class="col-md-2">
                        <label class="stp-lite-label">Salutation</label>
                        <select class="form-select form-select-sm" id="customerSalutation" name="customer_salutation" data-no-select2="true">
                            <option value="">Select</option>
                            <option value="Mr">Mr</option>
                            <option value="Mrs">Mrs</option>
                            <option value="Ms">Ms</option>
                            <option value="Miss">Miss</option>
                            <option value="Dr">Dr</option>
                            <option value="Prof">Prof</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="stp-lite-label">Full Name</label>
                        <input type="text" class="form-control form-control-sm stp-lite-guest-name" id="customerFullName" name="customer_full_name" placeholder="Enter full name" data-sanitize="name" autocomplete="name" pattern="[A-Za-z]+([ '\-.][A-Za-z]+)*" title="Letters only (spaces, hyphen, apostrophe allowed)">
                    </div>
                    <div class="col-md-3">
                        <label class="stp-lite-label">Email</label>
                        <input type="email" class="form-control form-control-sm" id="customerEmail" name="customer_email" placeholder="name@example.com" data-sanitize="email" autocomplete="email" pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}" title="Enter a valid email address">
                    </div>
                    <div class="col-md-2">
                        <label class="stp-lite-label">Country Code</label>
                        @php
                            $country_cod = \App\Models\Country::query()->orderBy('name')->get(['name', 'country_code']);
                            $singapore = $country_cod->firstWhere('name', 'Singapore');
                            $defaultCountryCode = $singapore ? $singapore->country_code : ($country_cod->first()->country_code ?? '');
                        @endphp
                        <select class="form-select form-select-sm" id="customerCountryCode" name="customer_country_code" data-no-select2="true">
                            <option value="">Select</option>
                            @foreach($country_cod as $country)
                                @if(!empty($country->country_code))
                                    <option value="{{ $country->country_code }}" @selected((string) $defaultCountryCode === (string) $country->country_code)>
                                        {{ $country->name }} ({{ $country->country_code }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="stp-lite-label">Phone Number</label>
                        <input type="text" class="form-control form-control-sm stp-lite-guest-phone" id="customerPhone" name="customer_phone" placeholder="Phone" data-sanitize="phone" inputmode="numeric" pattern="[0-9]{6,15}" title="Digits only" autocomplete="tel">
                    </div>
                    <div class="col-md-3">
                        <label class="stp-lite-label">Address Line 1</label>
                        <input type="text" class="form-control form-control-sm" id="customerAddress1" name="customer_address1" placeholder="Address line 1">
                    </div>
                    <div class="col-md-3">
                        <label class="stp-lite-label">Address Line 2</label>
                        <input type="text" class="form-control form-control-sm" id="customerAddress2" name="customer_address2" placeholder="Address line 2">
                    </div>
                    <div class="col-md-2">
                        <label class="stp-lite-label">State</label>
                        <input type="text" class="form-control form-control-sm" id="customerState" name="customer_state" placeholder="State">
                    </div>
                    <div class="col-md-2">
                        <label class="stp-lite-label">ZIP Code</label>
                        <input type="text" class="form-control form-control-sm" id="customerZip" name="customer_zip" placeholder="ZIP">
                    </div>
                    <div class="col-md-2">
                        <label class="stp-lite-label">Passport</label>
                        <input type="text" class="form-control form-control-sm" id="customerPassport" name="customer_passport" placeholder="Passport number">
                    </div>
                    <div class="col-md-3">
                        <label class="stp-lite-label">Passport Expiry</label>
                        <input type="date" class="form-control form-control-sm" id="customerPassportExpiry" name="customer_passport_expiry">
                    </div>
                    <div class="col-md-6">
                        <label class="stp-lite-label">Special Requests</label>
                        <textarea class="form-control form-control-sm" id="customerSpecialRequests" name="customer_special_requests" rows="2" placeholder="Notes"></textarea>
                    </div>
                    @if($showAppPassword)
                    <div class="col-md-6" id="leadGuestAppPasswordWrap">
                        <label class="stp-lite-label"><i class="ri-lock-password-line me-1"></i>App Password</label>
                        <div class="d-flex gap-1">
                            <input type="password" class="form-control form-control-sm" id="customerAppPassword" name="customer_app_password" placeholder="Enter app password" autocomplete="new-password" style="flex: 1;">
                            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.StpLiteGuests && window.StpLiteGuests.togglePasswordVisibility(this)" title="Toggle visibility" style="min-width: 32px; padding: 0 6px;">
                                <i class="ri-eye-off-line"></i>
                            </button>
                            <button class="btn btn-outline-primary btn-sm" type="button" onclick="window.StpLiteGuests && window.StpLiteGuests.generatePasswordFor(this)" title="Generate password" style="white-space: nowrap; padding: 0 8px; font-size: 0.75rem;">
                                <i class="ri-key-line me-1"></i>Generate
                            </button>
                        </div>
                        <small class="text-muted" style="font-size: 0.7rem;">Credentials email is sent when Email and App Password are set and you save (Definite / Actual).</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="stp-lite-card" id="additionalGuestsAccordion">
        <div class="stp-lite-card-header stp-lite-guest-head stp-lite-guest-head--extra" role="button" data-bs-toggle="collapse" data-bs-target="#additionalGuestsSection" aria-expanded="true">
            <div>
                <h2 class="mb-0"><i class="ri-group-line me-1"></i>Additional Guest(s)</h2>
                <small class="text-muted">Posted as <code>additionalguest</code> — up to Adults + Children</small>
            </div>
            <i class="ri-arrow-down-s-line"></i>
        </div>
        <div id="additionalGuestsSection" class="collapse show">
            <div class="stp-lite-card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                    <div class="stp-lite-guest-limit" id="guestLimitInfo">
                        <i class="ri-information-line me-1"></i>
                        Max <strong id="maxAdditionalGuests">0</strong> additional guest(s) · Total pax <strong id="totalPaxCount">0</strong>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addGuestBtn">
                        <i class="ri-add-line me-1"></i>Add Guest
                    </button>
                </div>
                <div id="additionalGuestsContainer"></div>
            </div>
        </div>
    </div>

    @include('single-tour-package.lite.partials.07-pricing-by-city')

    <div class="d-flex justify-content-end align-items-center gap-2 mt-3 mb-4 flex-wrap">
        <small class="text-muted me-auto" id="stpLiteSaveHint">
            @if(!empty($tour) && !empty($tour->tour_id))
                Update tour info, guests, and re-save services (same JSON as backup).
            @else
                Save creates the tour then stores services (same JSON as backup).
            @endif
        </small>
        <span class="stp-lite-loader" id="stpLiteSaveBtnLoader" aria-hidden="true">
            <span class="spinner-border spinner-border-sm" role="status"></span>
            Processing…
        </span>
        <button type="button" class="btn btn-success" id="stpLiteSaveTourBtn">
            <i class="ri-save-3-line me-1"></i>
            @if(!empty($tour) && !empty($tour->tour_id))
                Update Tour Package
            @else
                Save Tour Package
            @endif
        </button>
    </div>
</div>

<div id="stpLiteSaveOverlay" class="stp-lite-save-overlay" aria-hidden="true">
    <div class="stp-lite-save-overlay__card" role="status" aria-live="polite">
        <div class="spinner-border text-success stp-lite-save-overlay__spinner" role="presentation"></div>
        <p class="stp-lite-save-overlay__title">Processing</p>
        <p class="stp-lite-save-overlay__msg" id="stpLiteSaveOverlayMsg">Please wait…</p>
    </div>
</div>
{{-- === END 06-lead-guests === --}}
