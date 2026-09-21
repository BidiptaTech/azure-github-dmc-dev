/* === STP LITE: guests.js ===
 * Lead guest + additional guests (backup field IDs / FormData keys)
 * === */
(function (window, document) {
    'use strict';

    var additionalGuestCount = 0;

    function getTotalPax() {
        if (window.StpLiteGuestCaps && typeof window.StpLiteGuestCaps.getCaps === 'function') {
            var caps = window.StpLiteGuestCaps.getCaps() || {};
            return (parseInt(caps.adults, 10) || 0) + (parseInt(caps.children, 10) || 0);
        }
        var adults = parseInt((document.getElementById('adults') || {}).value || '1', 10) || 1;
        var children = parseInt((document.getElementById('children') || {}).value || '0', 10) || 0;
        return adults + children;
    }

    function getMaxAdditionalGuests() {
        return Math.max(0, getTotalPax());
    }

    function updateGuestLimitInfo() {
        var totalPax = getTotalPax();
        var maxGuests = getMaxAdditionalGuests();
        var totalPaxSpan = document.getElementById('totalPaxCount');
        var maxGuestsSpan = document.getElementById('maxAdditionalGuests');
        var addBtn = document.getElementById('addGuestBtn');
        if (totalPaxSpan) totalPaxSpan.textContent = String(totalPax);
        if (maxGuestsSpan) maxGuestsSpan.textContent = String(maxGuests);
        if (addBtn) {
            var atLimit = additionalGuestCount >= maxGuests || maxGuests === 0;
            addBtn.disabled = atLimit;
            addBtn.classList.toggle('disabled', atLimit);
        }
    }

    function guestCardHtml(guestIndex) {
        return (
            '<div class="card mb-2 border guest-card" data-guest-index="' + guestIndex + '">' +
            '  <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-3">' +
            '    <h6 class="mb-0 fw-semibold" style="font-size:0.85rem;"><i class="ri-user-line me-1"></i>Guest ' + guestIndex + '</h6>' +
            '    <button type="button" class="btn btn-sm btn-outline-danger stp-lite-remove-guest" data-guest-index="' + guestIndex + '">' +
            '      <i class="ri-delete-bin-line me-1"></i>Remove</button>' +
            '  </div>' +
            '  <div class="card-body py-2 px-3">' +
            '    <div class="row g-2">' +
            '      <div class="col-md-2"><label class="stp-lite-label">Salutation</label>' +
            '        <select class="form-select form-select-sm" name="additional_guests[' + guestIndex + '][salutation]" data-no-select2="true">' +
            '          <option value="">Select</option><option value="Mr">Mr</option><option value="Mrs">Mrs</option>' +
            '          <option value="Ms">Ms</option><option value="Miss">Miss</option><option value="Dr">Dr</option></select></div>' +
            '      <div class="col-md-3"><label class="stp-lite-label">Name</label>' +
            '        <input type="text" class="form-control form-control-sm stp-lite-guest-name" name="additional_guests[' + guestIndex + '][name]" placeholder="Full name" data-sanitize="name" pattern="[A-Za-z]+([ \'\\-.][A-Za-z]+)*" title="Letters only"></div>' +
            '      <div class="col-md-3"><label class="stp-lite-label">Passport No.</label>' +
            '        <input type="text" class="form-control form-control-sm" name="additional_guests[' + guestIndex + '][passport_no]" placeholder="Passport"></div>' +
            '      <div class="col-md-2"><label class="stp-lite-label">Passport Expiry</label>' +
            '        <input type="date" class="form-control form-control-sm" name="additional_guests[' + guestIndex + '][passport_exp]"></div>' +
            '      <div class="col-md-2"><label class="stp-lite-label">Contact No.</label>' +
            '        <input type="text" class="form-control form-control-sm stp-lite-guest-phone" name="additional_guests[' + guestIndex + '][contact_no]" placeholder="Contact" data-sanitize="phone" inputmode="numeric" pattern="[0-9]{6,15}" title="Digits only"></div>' +
            '    </div>' +
            '  </div>' +
            '</div>'
        );
    }

    function renumberAdditionalGuests() {
        var cards = document.querySelectorAll('.guest-card');
        additionalGuestCount = cards.length;
        cards.forEach(function (card, index) {
            var newIndex = index + 1;
            card.setAttribute('data-guest-index', String(newIndex));
            var header = card.querySelector('.card-header h6');
            if (header) header.innerHTML = '<i class="ri-user-line me-1"></i>Guest ' + newIndex;
            var removeBtn = card.querySelector('.stp-lite-remove-guest');
            if (removeBtn) removeBtn.setAttribute('data-guest-index', String(newIndex));
            card.querySelectorAll('input, select').forEach(function (input) {
                var name = input.getAttribute('name') || '';
                var m = name.match(/^additional_guests\[\d+]\[(\w+)]$/);
                if (m) input.setAttribute('name', 'additional_guests[' + newIndex + '][' + m[1] + ']');
            });
        });
    }

    function addAdditionalGuest() {
        var maxGuests = getMaxAdditionalGuests();
        if (maxGuests === 0) {
            alert('Set Adults / Children before adding additional guests.');
            return;
        }
        if (additionalGuestCount >= maxGuests) {
            alert('Maximum additional guests reached (' + maxGuests + ').');
            return;
        }
        var guestIndex = additionalGuestCount + 1;
        var container = document.getElementById('additionalGuestsContainer');
        if (!container) return;
        container.insertAdjacentHTML('beforeend', guestCardHtml(guestIndex));
        additionalGuestCount++;
        updateGuestLimitInfo();
    }

    function removeAdditionalGuest(guestIndex) {
        var card = document.querySelector('.guest-card[data-guest-index="' + guestIndex + '"]');
        if (!card) return;
        card.remove();
        renumberAdditionalGuests();
        updateGuestLimitInfo();
    }

    function getLeadVal(id, name) {
        var section = document.getElementById('customerAccordion');
        var el = section
            ? section.querySelector('#' + id + ', [name="' + name + '"]')
            : (document.getElementById(id) || document.querySelector('[name="' + name + '"]'));
        return ((el && el.value) || '').trim();
    }

    function collectMainGuest() {
        return {
            salutation: getLeadVal('customerSalutation', 'customer_salutation'),
            full_name: getLeadVal('customerFullName', 'customer_full_name'),
            email: getLeadVal('customerEmail', 'customer_email'),
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
    }

    /** Backup-compatible customer blob embedded into each service JSON row. */
    function getCustomerDataForServices() {
        var g = collectMainGuest();
        return {
            fullName: g.full_name || '',
            email: g.email || '',
            phone: g.phone || '',
            countryCode: g.country_code || '',
            address1: g.address1 || '',
            address2: g.address2 || null,
            state: g.state || null,
            zip: g.zip || '',
            specialRequests: g.special_requests || null
        };
    }

    function collectAdditionalGuests() {
        var list = [];
        document.querySelectorAll('.guest-card').forEach(function (card) {
            var guestData = {};
            card.querySelectorAll('input, select').forEach(function (input) {
                var name = input.getAttribute('name');
                if (!name || name.indexOf('additional_guests') === -1) return;
                var m = name.match(/\[(\d+)]\[(\w+)]/);
                if (m) guestData[m[2]] = input.value || '';
            });
            var gName = String(guestData.name || guestData.guest_name || '').trim();
            var gContact = String(guestData.contact_no || guestData.contact || '').trim();
            var gEmail = String(guestData.email || '').trim();
            if (gName || gContact || gEmail) list.push(guestData);
        });
        return list;
    }

    function init() {
        var addBtn = document.getElementById('addGuestBtn');
        if (addBtn) {
            addBtn.addEventListener('click', function (e) {
                e.preventDefault();
                addAdditionalGuest();
            });
        }
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.stp-lite-remove-guest');
            if (!btn) return;
            e.preventDefault();
            removeAdditionalGuest(btn.getAttribute('data-guest-index'));
        });
        document.addEventListener('stp:guests-changed', updateGuestLimitInfo);
        updateGuestLimitInfo();
    }

    function hydrate(mainGuest, additional) {
        mainGuest = mainGuest || {};
        function set(id, val) {
            var el = document.getElementById(id);
            if (!el || val == null || val === '') return;
            el.value = val;
            try { el.dispatchEvent(new Event('change', { bubbles: true })); } catch (e) { /* ignore */ }
        }
        // Allow clearing then set — empty string skips; set known fields explicitly
        function setAll(id, val) {
            var el = document.getElementById(id);
            if (!el) return;
            el.value = val != null ? String(val) : '';
            try { el.dispatchEvent(new Event('change', { bubbles: true })); } catch (e) { /* ignore */ }
        }
        setAll('customerSalutation', mainGuest.salutation || mainGuest.customer_salutation || '');
        setAll('customerFullName', mainGuest.full_name || mainGuest.fullName || mainGuest.name || '');
        setAll('customerEmail', mainGuest.email || '');
        setAll('customerPhone', mainGuest.phone || '');
        setAll('customerCountryCode', mainGuest.country_code || mainGuest.countryCode || '');
        setAll('customerAddress1', mainGuest.address1 || '');
        setAll('customerAddress2', mainGuest.address2 || '');
        setAll('customerState', mainGuest.state || '');
        setAll('customerZip', mainGuest.zip || '');
        setAll('customerPassport', mainGuest.passport || mainGuest.passport_no || '');
        setAll('customerPassportExpiry', mainGuest.passport_expiry || mainGuest.passport_exp || '');
        setAll('customerSpecialRequests', mainGuest.special_requests || mainGuest.specialRequests || '');

        var host = document.getElementById('additionalGuestsContainer');
        if (host) host.innerHTML = '';
        additionalGuestCount = 0;
        (additional || []).forEach(function (g) {
            if (!g) return;
            addAdditionalGuest();
            var idx = additionalGuestCount;
            var card = document.querySelector('.guest-card[data-guest-index="' + idx + '"]');
            if (!card) return;
            var map = {
                salutation: g.salutation || '',
                name: g.name || g.full_name || g.fullName || '',
                passport_no: g.passport_no || g.passportNo || '',
                passport_exp: g.passport_exp || g.passportExp || g.passport_expiry || '',
                contact_no: g.contact_no || g.contactNo || g.phone || ''
            };
            Object.keys(map).forEach(function (k) {
                var input = card.querySelector('[name="additional_guests[' + idx + '][' + k + ']"]');
                if (input) input.value = map[k];
            });
        });
        updateGuestLimitInfo();
    }

    function validateGuestFields() {
        var San = window.StpLiteInputSanitize || {};
        var nameOk = typeof San.isValidName === 'function' ? San.isValidName : function () { return true; };
        var emailOk = typeof San.isValidEmail === 'function' ? San.isValidEmail : function () { return true; };
        var phoneOk = typeof San.isValidPhone === 'function' ? San.isValidPhone : function () { return true; };
        var validateNameEl = typeof San.validateNameField === 'function' ? San.validateNameField : null;
        var validateEmailEl = typeof San.validateEmailField === 'function' ? San.validateEmailField : null;
        var validatePhoneEl = typeof San.validatePhoneField === 'function' ? San.validatePhoneField : null;

        var nameEl = document.getElementById('customerFullName');
        var emailEl = document.getElementById('customerEmail');
        var phoneEl = document.getElementById('customerPhone');
        var fullName = ((nameEl && nameEl.value) || '').trim();
        var email = ((emailEl && emailEl.value) || '').trim();
        var phone = ((phoneEl && phoneEl.value) || '').trim();

        if (validateNameEl) validateNameEl(nameEl);
        if (validateEmailEl) validateEmailEl(emailEl);
        if (validatePhoneEl) validatePhoneEl(phoneEl);

        if (fullName && !nameOk(fullName)) {
            if (nameEl) {
                try { nameEl.focus(); } catch (e) { /* ignore */ }
            }
            return { ok: false, message: 'Lead guest name: letters only (no numbers or special characters).' };
        }
        if (email && !emailOk(email)) {
            if (emailEl) {
                try { emailEl.focus(); } catch (e2) { /* ignore */ }
            }
            return { ok: false, message: 'Lead guest email must be valid (e.g. name@example.com).' };
        }
        if (phone && !phoneOk(phone)) {
            if (phoneEl) {
                try { phoneEl.focus(); } catch (ePhone) { /* ignore */ }
            }
            return { ok: false, message: 'Lead guest phone: digits only (6–15 numbers).' };
        }

        var guestCards = document.querySelectorAll('.guest-card');
        for (var i = 0; i < guestCards.length; i += 1) {
            var nameInput = guestCards[i].querySelector('input[name*="[name]"]');
            var contactInput = guestCards[i].querySelector('input[name*="[contact_no]"]');
            if (nameInput) {
                if (validateNameEl) validateNameEl(nameInput);
                var gName = String(nameInput.value || '').trim();
                if (gName && !nameOk(gName)) {
                    try { nameInput.focus(); } catch (e3) { /* ignore */ }
                    return { ok: false, message: 'Additional guest name: letters only (no numbers or special characters).' };
                }
            }
            if (contactInput) {
                if (validatePhoneEl) validatePhoneEl(contactInput);
                var gContact = String(contactInput.value || '').trim();
                if (gContact && !phoneOk(gContact)) {
                    try { contactInput.focus(); } catch (e4) { /* ignore */ }
                    return { ok: false, message: 'Additional guest contact: digits only (6–15 numbers).' };
                }
            }
        }
        return { ok: true };
    }

    window.StpLiteGuests = {
        init: init,
        hydrate: hydrate,
        collectMainGuest: collectMainGuest,
        collectAdditionalGuests: collectAdditionalGuests,
        getCustomerDataForServices: getCustomerDataForServices,
        updateGuestLimitInfo: updateGuestLimitInfo,
        addAdditionalGuest: addAdditionalGuest,
        validateGuestFields: validateGuestFields
    };

    // Backup global aliases used by inline handlers if any remain
    window.addAdditionalGuest = addAdditionalGuest;
    window.removeAdditionalGuest = removeAdditionalGuest;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})(window, document);
/* === END guests.js === */
