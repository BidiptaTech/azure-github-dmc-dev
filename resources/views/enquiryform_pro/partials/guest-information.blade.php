{{-- Lead Guest Information + Additional Guests (Lite parity). --}}
@php
    $customer_info = $customer_info ?? [];
    $additionalGuests = $additionalGuests ?? [];
    $showAppPassword = !empty($showAppPassword);
    $allCountriesForCode = \App\Models\Country::query()->orderBy('name')->get(['name', 'country_code']);
    $defaultCountryCode = $customer_info['countryCode'] ?? '';
    if ($defaultCountryCode === '') {
        $singapore = $allCountriesForCode->firstWhere('name', 'Singapore');
        $defaultCountryCode = $singapore->country_code ?? ($allCountriesForCode->first()->country_code ?? '');
    }
@endphp

<div id="guestInfoSection" class="mb-3" style="padding-bottom: 8px;">
    <div class="accordion mb-3" id="customerAccordion">
        <div class="accordion-item border-0">
            <div class="card shadow-sm border-0">
                <div class="card-header text-white d-flex justify-content-between align-items-center {{ $showAppPassword ? '' : 'collapsed' }}" role="button" data-bs-toggle="collapse" data-bs-target="#customerInformationSection" aria-expanded="{{ $showAppPassword ? 'true' : 'false' }}" aria-controls="customerInformationSection" style="cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 0.875rem 1.25rem;">
                    <div class="d-flex align-items-center">
                        <div style="width: 35px; height: 35px; background: rgba(255, 255, 255, 0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                            <i class="ri-user-line text-white" style="font-size: 1rem;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-semibold text-white" style="font-size: 0.85rem;">Lead Guest Information @if($showAppPassword)<span class="badge bg-light text-dark ms-1" style="font-size: 0.65rem;">App Password</span>@endif</h6>
                            <small style="color: rgba(255, 255, 255, 0.85); font-size: 0.75rem; display:block; margin-top:2px;">Manage customer details and contact information</small>
                        </div>
                    </div>
                    <i class="ri-arrow-down-s-line ms-2 text-white" style="font-size: 0.9rem;"></i>
                </div>
                <div id="customerInformationSection" class="collapse {{ $showAppPassword ? 'show' : '' }}" data-bs-parent="#customerAccordion">
                    <div class="card-body" style="background: #ffffff; padding: 0.75rem 1rem;">
                        <div class="row g-2">
                            <div class="col-md-2">
                                <label class="form-label mb-1" style="font-size: 0.8rem;">Salutation</label>
                                <select class="form-select form-select-sm" id="customerSalutation" name="customer_salutation" style="font-size: 0.85rem;">
                                    <option value="">Select</option>
                                    @foreach(['Mr', 'Mrs', 'Ms', 'Miss', 'Dr', 'Prof'] as $sal)
                                        <option value="{{ $sal }}" {{ ($customer_info['salutation'] ?? '') == $sal ? 'selected' : '' }}>{{ $sal }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1" style="font-size: 0.8rem;">Full Name</label>
                                <input type="text" class="form-control form-control-sm" id="customerFullName" name="customer_full_name" placeholder="Enter full name" value="{{ $customer_info['fullName'] ?? '' }}" style="font-size: 0.85rem;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1" style="font-size: 0.8rem;">Email</label>
                                @php $leadGuestEmail = trim((string) ($customer_info['email'] ?? '')); @endphp
                                <input type="email" class="form-control form-control-sm" id="customerLeadEmail" name="customer_email" placeholder="Enter email" value="{{ $leadGuestEmail }}" @if($showAppPassword && $leadGuestEmail !== '') readonly @endif style="font-size: 0.85rem;{{ ($showAppPassword && $leadGuestEmail !== '') ? ' background-color: #e9ecef;' : '' }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1" style="font-size: 0.8rem;">Country Code</label>
                                <select class="form-select form-select-sm" id="customerCountryCode" name="customer_country_code" style="font-size: 0.85rem;">
                                    <option value="">Select</option>
                                    @foreach($allCountriesForCode as $country)
                                        @if(!empty($country->country_code))
                                            <option value="{{ $country->country_code }}" {{ (string) $defaultCountryCode === (string) $country->country_code ? 'selected' : '' }}>{{ $country->name }} ({{ $country->country_code }})</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1" style="font-size: 0.8rem;">Phone Number</label>
                                <input type="tel" class="form-control form-control-sm" id="customerPhone" name="customer_phone" placeholder="Enter phone number" value="{{ $customer_info['phone'] ?? '' }}" style="font-size: 0.85rem;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1" style="font-size: 0.8rem;">Address Line 1</label>
                                <input type="text" class="form-control form-control-sm" id="customerAddress1" name="customer_address1" placeholder="Enter address line 1" value="{{ $customer_info['address1'] ?? '' }}" style="font-size: 0.85rem;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1" style="font-size: 0.8rem;">Address Line 2</label>
                                <input type="text" class="form-control form-control-sm" id="customerAddress2" name="customer_address2" placeholder="Enter address line 2" value="{{ $customer_info['address2'] ?? '' }}" style="font-size: 0.85rem;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1" style="font-size: 0.8rem;">State</label>
                                <input type="text" class="form-control form-control-sm" id="customerState" name="customer_state" placeholder="Enter state" value="{{ $customer_info['state'] ?? '' }}" style="font-size: 0.85rem;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1" style="font-size: 0.8rem;">ZIP Code</label>
                                <input type="text" class="form-control form-control-sm" id="customerZip" name="customer_zip" placeholder="Enter ZIP code" value="{{ $customer_info['zip'] ?? '' }}" style="font-size: 0.85rem;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1" style="font-size: 0.8rem;">Passport</label>
                                <input type="text" class="form-control form-control-sm" id="customerPassport" name="customer_passport" placeholder="Passport number" value="{{ $customer_info['passport'] ?? '' }}" style="font-size: 0.85rem;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1" style="font-size: 0.8rem;">Passport Expiry Date</label>
                                <input type="date" class="form-control form-control-sm" id="customerPassportExpiry" name="customer_passport_expiry" value="{{ $customer_info['passportExpiry'] ?? '' }}" style="font-size: 0.85rem;">
                            </div>
                            <div class="col-md-{{ $showAppPassword ? '6' : '6' }}">
                                <label class="form-label mb-1" style="font-size: 0.8rem;">Special Requests</label>
                                <textarea class="form-control form-control-sm" id="customerSpecialRequests" name="customer_special_requests" rows="2" placeholder="Enter any special requests or notes" style="font-size: 0.85rem;">{{ $customer_info['specialRequests'] ?? '' }}</textarea>
                            </div>
                            @if($showAppPassword)
                                <div class="col-md-6">
                                    <label class="form-label mb-1" style="font-size: 0.8rem;">
                                        <i class="ri-lock-password-line me-1"></i>App Password
                                    </label>
                                    <div class="d-flex gap-1">
                                        <input type="password" class="form-control form-control-sm" id="customerAppPassword" name="customer_app_password" placeholder="Enter app password" autocomplete="new-password" style="font-size: 0.85rem; flex: 1;">
                                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="togglePasswordVisibility(this)" title="Toggle visibility" style="min-width: 32px; padding: 0 6px;">
                                            <i class="ri-eye-off-line"></i>
                                        </button>
                                        <button class="btn btn-outline-primary btn-sm" type="button" onclick="generatePasswordFor(this)" title="Generate password" style="white-space: nowrap; padding: 0 8px; font-size: 0.75rem;">
                                            <i class="ri-key-line me-1"></i>Generate
                                        </button>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.7rem;">Credentials email is sent to the lead guest only when Email and App Password are set and you save.</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="accordion mb-3" id="additionalGuestsAccordion">
        <div class="accordion-item border-0">
            <div class="card shadow-sm border-0">
                <div class="card-header text-white d-flex justify-content-between align-items-center collapsed" role="button" data-bs-toggle="collapse" data-bs-target="#additionalGuestsSection" aria-expanded="false" aria-controls="additionalGuestsSection" style="cursor: pointer; background: linear-gradient(135deg, #0dcaf0 0%, #0d6efd 100%); border: none; padding: 0.875rem 1.25rem;">
                    <div class="d-flex align-items-center">
                        <div style="width: 35px; height: 35px; background: rgba(255, 255, 255, 0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                            <i class="ri-group-line text-white" style="font-size: 1rem;"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-semibold text-white" style="font-size: 0.85rem;">Additional Guest(s)</h6>
                            <small style="color: rgba(255, 255, 255, 0.85); font-size: 0.75rem;">Add guest details up to the tour pax (Adults + Children)</small>
                        </div>
                    </div>
                    <i class="ri-arrow-down-s-line ms-2 text-white" style="font-size: 0.9rem;"></i>
                </div>
                <div id="additionalGuestsSection" class="collapse" data-bs-parent="#additionalGuestsAccordion">
                    <div class="card-body" style="background: #ffffff; padding: 1.25rem;">
                        <div class="mb-3 text-end">
                            <button type="button" class="btn btn-sm btn-light" id="addGuestBtn" onclick="addNewGuest()" style="font-size: 0.8rem; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                <i class="ri-add-line me-1"></i>Add Guest
                            </button>
                        </div>
                        <div id="additionalGuestsContainer">
                            @if(!empty($additionalGuests))
                                @foreach($additionalGuests as $index => $guest)
                                    <div class="card mb-3 border shadow-sm guest-card" data-guest-index="{{ $index }}">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 fw-semibold">
                                                <i class="ri-user-line me-2"></i>Guest {{ $index + 1 }}
                                            </h6>
                                            <button type="button" class="btn btn-sm btn-danger remove-guest-btn" onclick="removeGuest(this)" data-guest-index="{{ $index }}" title="Remove Guest">
                                                <i class="ri-delete-bin-line"></i> Remove
                                            </button>
                                        </div>
                                        <div class="card-body" style="margin-top:10px">
                                            <div class="row g-3">
                                                <div class="col-md-2">
                                                    <label class="form-label mb-1" style="font-size: 0.8rem;">Salutation</label>
                                                    <select class="form-select form-select-sm guest-salutation" name="additional_guests[{{ $index }}][salutation]" style="font-size: 0.85rem;">
                                                        <option value="">Select</option>
                                                        @foreach(['Mr', 'Mrs', 'Ms', 'Miss', 'Dr'] as $sal)
                                                            <option value="{{ $sal }}" {{ ($guest['salutation'] ?? '') == $sal ? 'selected' : '' }}>{{ $sal }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold">Name</label>
                                                    <input type="text" class="form-control guest-name" name="additional_guests[{{ $index }}][name]" value="{{ $guest['name'] ?? $guest['guest_name'] ?? '' }}" placeholder="Enter full name">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold">Passport No.</label>
                                                    <input type="text" class="form-control guest-passport-no" name="additional_guests[{{ $index }}][passport_no]" value="{{ $guest['passport_no'] ?? $guest['passport'] ?? '' }}" placeholder="Enter passport number">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold">Passport Expiry</label>
                                                    <input type="date" class="form-control guest-passport-exp" name="additional_guests[{{ $index }}][passport_exp]" value="{{ $guest['passport_exp'] ?? '' }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Contact No.</label>
                                                    <input type="text" class="form-control guest-contact-no" name="additional_guests[{{ $index }}][contact_no]" value="{{ $guest['contact_no'] ?? $guest['contact'] ?? '' }}" placeholder="Enter contact number">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Email</label>
                                                    @php $additionalGuestEmail = trim((string) ($guest['email'] ?? '')); @endphp
                                                    <input type="email" class="form-control guest-email" name="additional_guests[{{ $index }}][email]" value="{{ $additionalGuestEmail }}" placeholder="Enter email" @if($showAppPassword && $additionalGuestEmail !== '') readonly @endif @if($showAppPassword && $additionalGuestEmail !== '') style="background-color: #e9ecef;" @endif>
                                                </div>
                                                @if($showAppPassword)
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold"><i class="ri-lock-password-line me-1"></i>App Password</label>
                                                        <div class="d-flex gap-1">
                                                            <input type="password" class="form-control guest-app-password" name="additional_guests[{{ $index }}][app_password]" placeholder="Enter app password" autocomplete="new-password" style="flex: 1;">
                                                            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="togglePasswordVisibility(this)" title="Toggle visibility" style="min-width: 32px; padding: 0 6px;">
                                                                <i class="ri-eye-off-line"></i>
                                                            </button>
                                                            <button class="btn btn-outline-primary btn-sm" type="button" onclick="generatePasswordFor(this)" title="Generate password" style="white-space: nowrap; padding: 0 8px; font-size: 0.75rem;">
                                                                <i class="ri-key-line me-1"></i>Generate
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-muted small mb-3 p-3 bg-light rounded">
                                    <i class="ri-information-line me-2"></i>No additional guest information has been added for this tour.
                                </div>
                            @endif
                        </div>
                        <div class="mt-3 small" id="guestLimitInfo" style="padding: 10px; background: #e7f3ff; border-radius: 6px; border: 1px solid #b3d9ff;">
                            <i class="ri-information-line me-1"></i>
                            Maximum <span id="maxAdditionalGuests">0</span> additional guest(s) can be added based on total pax (Adults + Children): <span id="totalPaxCount">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.proShowAppPassword = {{ $showAppPassword ? 'true' : 'false' }};
    let guestIndexCounter = {{ !empty($additionalGuests) ? count($additionalGuests) : 0 }};

    function getProTotalPax() {
        const adults = parseInt(document.getElementById('adultCountInput')?.value || '0', 10) || 0;
        const children = parseInt(document.getElementById('childCountInput')?.value || '0', 10) || 0;
        return adults + children;
    }

    function updateGuestCount() {
        const totalPax = getProTotalPax();
        const maxGuests = Math.max(0, totalPax);
        const current = document.querySelectorAll('#additionalGuestsContainer .guest-card').length;
        const totalPaxSpan = document.getElementById('totalPaxCount');
        const maxGuestsSpan = document.getElementById('maxAdditionalGuests');
        const addBtn = document.getElementById('addGuestBtn');
        if (totalPaxSpan) totalPaxSpan.textContent = totalPax;
        if (maxGuestsSpan) maxGuestsSpan.textContent = maxGuests;
        if (addBtn) {
            addBtn.disabled = current >= maxGuests || maxGuests === 0;
            addBtn.classList.toggle('disabled', addBtn.disabled);
        }
    }

    function collectProLeadGuestData() {
        const leadSection = document.getElementById('customerAccordion');
        const getLeadVal = (id, name) => {
            const el = leadSection ? leadSection.querySelector('#' + id + ', [name="' + name + '"]') : (document.getElementById(id) || document.querySelector('[name="' + name + '"]'));
            return (el?.value || '').trim();
        };
        const data = {
            salutation: getLeadVal('customerSalutation', 'customer_salutation'),
            full_name: getLeadVal('customerFullName', 'customer_full_name'),
            email: getLeadVal('customerLeadEmail', 'customer_email'),
            country_code: getLeadVal('customerCountryCode', 'customer_country_code'),
            phone: getLeadVal('customerPhone', 'customer_phone'),
            address1: getLeadVal('customerAddress1', 'customer_address1'),
            address2: getLeadVal('customerAddress2', 'customer_address2'),
            state: getLeadVal('customerState', 'customer_state'),
            zip: getLeadVal('customerZip', 'customer_zip'),
            special_requests: getLeadVal('customerSpecialRequests', 'customer_special_requests'),
            passport: getLeadVal('customerPassport', 'customer_passport'),
            passport_exp: getLeadVal('customerPassportExpiry', 'customer_passport_expiry')
        };
        if (window.proShowAppPassword) {
            data.app_password = (leadSection ? leadSection.querySelector('#customerAppPassword, [name="customer_app_password"]') : document.getElementById('customerAppPassword'))?.value || '';
        }
        return data;
    }

    function collectProAdditionalGuests() {
        const additionalGuests = [];
        document.querySelectorAll('#additionalGuestsContainer .guest-card').forEach((card) => {
            const guest = {
                salutation: card.querySelector('.guest-salutation')?.value || '',
                name: card.querySelector('.guest-name')?.value || '',
                email: card.querySelector('.guest-email')?.value || '',
                passport_no: card.querySelector('.guest-passport-no')?.value || '',
                passport_exp: card.querySelector('.guest-passport-exp')?.value || '',
                contact_no: card.querySelector('.guest-contact-no')?.value || '',
            };
            if (window.proShowAppPassword) {
                guest.app_password = card.querySelector('.guest-app-password')?.value || '';
            }
            if (guest.name.trim() !== '' || guest.email.trim() !== '' || guest.contact_no.trim() !== '') {
                additionalGuests.push(guest);
            }
        });
        return additionalGuests;
    }

    function appendProGuestFormData(formData) {
        const mainGuestData = collectProLeadGuestData();
        formData.append('mainguest', JSON.stringify(mainGuestData));
        formData.append('additionalguest', JSON.stringify(collectProAdditionalGuests()));
        if (mainGuestData.salutation) formData.set('salutation', mainGuestData.salutation);
        if (mainGuestData.full_name) formData.set('customer_name', mainGuestData.full_name);
        if (mainGuestData.phone) formData.set('contact_number', mainGuestData.phone);
        if (mainGuestData.email) formData.set('email', mainGuestData.email);
    }

    function syncLeadGuestWithHeader() {
        const pairs = [
            ['customerSalutation', 'salutationSelect'],
            ['customerFullName', 'customerNameInput'],
            ['customerLeadEmail', 'emailInput'],
            ['customerPhone', 'contactNumberInput']
        ];
        pairs.forEach(([leadId, headerId]) => {
            const lead = document.querySelector('#customerAccordion #' + leadId);
            const header = document.getElementById(headerId);
            if (!lead || !header) return;
            const copyToHeader = () => { header.value = lead.value; };
            const copyToLead = () => { lead.value = header.value; };
            lead.addEventListener('input', copyToHeader);
            lead.addEventListener('change', copyToHeader);
            header.addEventListener('input', copyToLead);
            header.addEventListener('change', copyToLead);
        });
    }

    function addNewGuest() {
        const maxGuests = Math.max(0, getProTotalPax());
        const currentCount = document.querySelectorAll('#additionalGuestsContainer .guest-card').length;
        if (maxGuests === 0) {
            alert('Total pax (Adults + Children) is 0. Please set pax before adding additional guests.');
            return;
        }
        if (currentCount >= maxGuests) {
            alert('Maximum number of additional guests reached. Maximum allowed: ' + maxGuests);
            return;
        }
        const container = document.getElementById('additionalGuestsContainer');
        const noGuestsMsg = container ? container.querySelector('.text-muted') : null;
        if (noGuestsMsg) noGuestsMsg.remove();
        const newIndex = guestIndexCounter++;
        const passwordHtml = window.proShowAppPassword ? `
            <div class="col-md-4">
                <label class="form-label fw-semibold"><i class="ri-lock-password-line me-1"></i>App Password</label>
                <div class="d-flex gap-1">
                    <input type="password" class="form-control guest-app-password" name="additional_guests[${newIndex}][app_password]" placeholder="Enter app password" autocomplete="new-password" style="flex: 1;">
                    <button class="btn btn-outline-secondary btn-sm" type="button" onclick="togglePasswordVisibility(this)" title="Toggle visibility" style="min-width: 32px; padding: 0 6px;"><i class="ri-eye-off-line"></i></button>
                    <button class="btn btn-outline-primary btn-sm" type="button" onclick="generatePasswordFor(this)" title="Generate password" style="white-space: nowrap; padding: 0 8px; font-size: 0.75rem;"><i class="ri-key-line me-1"></i>Generate</button>
                </div>
            </div>` : '';
        const guestCard = document.createElement('div');
        guestCard.className = 'card mb-3 border shadow-sm guest-card';
        guestCard.setAttribute('data-guest-index', newIndex);
        guestCard.innerHTML = `
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold"><i class="ri-user-line me-2"></i>Guest ${newIndex + 1}</h6>
                <button type="button" class="btn btn-sm btn-danger remove-guest-btn" onclick="removeGuest(this)" data-guest-index="${newIndex}" title="Remove Guest">
                    <i class="ri-delete-bin-line"></i> Remove
                </button>
            </div>
            <div class="card-body" style="margin-top:10px">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label mb-1" style="font-size: 0.8rem;">Salutation</label>
                        <select class="form-select form-select-sm guest-salutation" name="additional_guests[${newIndex}][salutation]">
                            <option value="">Select</option>
                            <option value="Mr">Mr</option>
                            <option value="Mrs">Mrs</option>
                            <option value="Ms">Ms</option>
                            <option value="Miss">Miss</option>
                            <option value="Dr">Dr</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Name</label>
                        <input type="text" class="form-control guest-name" name="additional_guests[${newIndex}][name]" placeholder="Enter full name">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Passport No.</label>
                        <input type="text" class="form-control guest-passport-no" name="additional_guests[${newIndex}][passport_no]" placeholder="Enter passport number">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Passport Expiry</label>
                        <input type="date" class="form-control guest-passport-exp" name="additional_guests[${newIndex}][passport_exp]">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Contact No.</label>
                        <input type="text" class="form-control guest-contact-no" name="additional_guests[${newIndex}][contact_no]" placeholder="Enter contact number">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" class="form-control guest-email" name="additional_guests[${newIndex}][email]" placeholder="Enter email">
                    </div>
                    ${passwordHtml}
                </div>
            </div>`;
        container.appendChild(guestCard);
        updateGuestCount();
    }

    function removeGuest(button) {
        if (!confirm('Are you sure you want to remove this guest?')) return;
        const guestCard = button.closest('.guest-card');
        if (guestCard) guestCard.remove();
        const container = document.getElementById('additionalGuestsContainer');
        if (container && !container.querySelector('.guest-card')) {
            container.innerHTML = '<div class="text-muted small mb-3 p-3 bg-light rounded"><i class="ri-information-line me-2"></i>No additional guest information has been added for this tour.</div>';
        }
        updateGuestCount();
    }

    function generateRandomPassword() {
        const upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const lower = 'abcdefghijklmnopqrstuvwxyz';
        const digits = '0123456789';
        const special = '!@#$%&*';
        const all = upper + lower + digits + special;
        let password = upper[Math.floor(Math.random() * upper.length)]
            + lower[Math.floor(Math.random() * lower.length)]
            + digits[Math.floor(Math.random() * digits.length)]
            + special[Math.floor(Math.random() * special.length)];
        for (let i = 4; i < 10; i++) {
            password += all[Math.floor(Math.random() * all.length)];
        }
        return password.split('').sort(() => Math.random() - 0.5).join('');
    }

    function togglePasswordVisibility(btn) {
        const container = btn.parentElement;
        const input = container.querySelector('input[type="password"], input[type="text"]');
        const icon = btn.querySelector('i');
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) icon.className = 'ri-eye-line';
        } else {
            input.type = 'password';
            if (icon) icon.className = 'ri-eye-off-line';
        }
    }

    function generatePasswordFor(btn) {
        const container = btn.parentElement;
        const input = container.querySelector('input');
        if (!input) return;
        input.value = generateRandomPassword();
        input.type = 'text';
        const eyeBtn = container.querySelector('.btn-outline-secondary i');
        if (eyeBtn) eyeBtn.className = 'ri-eye-line';
    }

    document.addEventListener('DOMContentLoaded', function () {
        syncLeadGuestWithHeader();
        updateGuestCount();
        ['adultCountInput', 'childCountInput'].forEach((id) => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', updateGuestCount);
        });
        const leadCollapse = document.getElementById('customerInformationSection');
        const extraCollapse = document.getElementById('additionalGuestsSection');
        const scrollOpenedGuestPanel = function (panel) {
            const content = document.querySelector('.enquiry-pro-content');
            if (!panel) return;
            requestAnimationFrame(function () {
                if (content) {
                    const top = panel.getBoundingClientRect().top - content.getBoundingClientRect().top + content.scrollTop - 12;
                    content.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
                } else {
                    panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        };
        if (leadCollapse) {
            leadCollapse.addEventListener('shown.bs.collapse', function () {
                scrollOpenedGuestPanel(document.getElementById('customerAccordion'));
            });
            if (leadCollapse.classList.contains('show')) {
                setTimeout(function () { scrollOpenedGuestPanel(document.getElementById('customerAccordion')); }, 250);
            }
        }
        if (extraCollapse) {
            extraCollapse.addEventListener('shown.bs.collapse', function () {
                scrollOpenedGuestPanel(document.getElementById('additionalGuestsAccordion'));
            });
        }
    });
</script>
