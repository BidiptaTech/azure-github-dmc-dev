/* === STP LITE: guest-caps.js ===
 * Depends: tour-type.js (synced after guest change)
 * Owns: global guest state; caps for every service section; hide child UI when children=0
 * Hidden fields (backup parity): adults, male, female, children, infants, child_ages
 * === */
(function (window, document) {
    'use strict';

    var state = {
        adults: 1,
        male: 0,
        female: 0,
        children: 0,
        infants: 0,
        childAges: []
    };

    function toInt(v, fallback) {
        var n = parseInt(v, 10);
        return isNaN(n) ? (fallback || 0) : n;
    }

    function readFromDom() {
        state.male = toInt((document.getElementById('male') || {}).value, 0);
        state.female = toInt((document.getElementById('female') || {}).value, 0);
        state.children = toInt((document.getElementById('children') || {}).value, 0);
        state.infants = toInt((document.getElementById('infants') || {}).value, 0);
        state.adults = state.male + state.female;
        if (state.adults < 1) {
            state.adults = 1;
            state.male = 1;
            state.female = 0;
        }
        var agesEl = document.getElementById('child_ages');
        try {
            state.childAges = agesEl && agesEl.value ? JSON.parse(agesEl.value) : [];
            if (!Array.isArray(state.childAges)) state.childAges = [];
        } catch (e) {
            state.childAges = [];
        }
        return state;
    }

    function writeToDom() {
        var adultsEl = document.getElementById('adults');
        var maleEl = document.getElementById('male');
        var femaleEl = document.getElementById('female');
        var childrenEl = document.getElementById('children');
        var infantsEl = document.getElementById('infants');
        var agesEl = document.getElementById('child_ages');

        if (adultsEl) adultsEl.value = String(state.adults);
        if (maleEl) maleEl.value = String(state.male);
        if (femaleEl) femaleEl.value = String(state.female);
        if (childrenEl) childrenEl.value = String(state.children);
        if (infantsEl) infantsEl.value = String(state.infants);
        if (agesEl) agesEl.value = JSON.stringify(state.childAges || []);

        renderSummary();
        refreshGuestDependentUI();
        window.tourGuestCaps = getCaps();

        if (window.StpLiteTourType && typeof window.StpLiteTourType.sync === 'function') {
            window.StpLiteTourType.sync();
        }

        document.dispatchEvent(new CustomEvent('stp:guests-changed', { detail: getCaps() }));
    }

    function getCaps() {
        return {
            adults: state.adults,
            male: state.male,
            female: state.female,
            children: state.children,
            infants: state.infants,
            childAges: state.childAges.slice()
        };
    }

    function renderSummary() {
        var box = document.getElementById('mainGuestSummary');
        if (!box) return;
        // Compact one-row chips for the tour-details card
        box.innerHTML =
            '<span class="stp-lite-chip adults"><i class="ri-group-line"></i>' + state.adults + 'A</span>' +
            '<span class="stp-lite-chip meta">' + state.male + 'M/' + state.female + 'F</span>' +
            '<span class="stp-lite-chip children"><i class="ri-user-smile-line"></i>' + state.children + 'C</span>' +
            '<span class="stp-lite-chip infants"><i class="ri-user-heart-line"></i>' + state.infants + 'I</span>';
    }

    /**
     * Clamp a section's guest counts so they never exceed global caps.
     * Returns clamped object { adults, children, infants }.
     */
    function clampSectionGuests(sectionGuests) {
        var g = sectionGuests || {};
        var adults = Math.min(Math.max(0, toInt(g.adults, 0)), state.adults);
        var children = state.children > 0
            ? Math.min(Math.max(0, toInt(g.children, 0)), state.children)
            : 0;
        var infants = Math.min(Math.max(0, toInt(g.infants, 0)), state.infants);
        return { adults: adults, children: children, infants: infants };
    }

    function refreshGuestDependentUI() {
        var hideChild = state.children <= 0;
        document.querySelectorAll('[data-guest-child-ui]').forEach(function (el) {
            el.classList.toggle('d-none', hideChild);
            el.querySelectorAll('input, select, textarea, button').forEach(function (ctrl) {
                if (hideChild) {
                    ctrl.setAttribute('disabled', 'disabled');
                    if (ctrl.type === 'checkbox') {
                        ctrl.checked = false;
                    } else if (ctrl.type === 'number' || ctrl.classList.contains('stp-lite-int')) {
                        ctrl.value = '0';
                    }
                } else {
                    ctrl.removeAttribute('disabled');
                }
            });
        });

        // Cap number inputs marked with data-guest-cap="adults|children|infants"
        document.querySelectorAll('[data-guest-cap]').forEach(function (el) {
            var key = el.getAttribute('data-guest-cap');
            var max = state[key] != null ? state[key] : null;
            if (max == null) return;
            el.setAttribute('max', String(max));
            var val = toInt(el.value, 0);
            if (val > max) el.value = String(max);
            if (key === 'children' && max <= 0) el.value = '0';
        });

        var stub = document.getElementById('phase1GuestCapsStub');
        if (stub) {
            stub.innerHTML =
                '<div><strong>Global caps:</strong> ' +
                state.adults + ' adults, ' + state.children + ' children, ' + state.infants + ' infants</div>' +
                '<div class="mt-1">' +
                (hideChild
                    ? '<span class="text-muted">Children = 0 → child fields hidden in every section below.</span>'
                    : '<span class="text-success">Children available → child fields can show (≤ ' + state.children + ').</span>') +
                '</div>';
        }
    }

    function applySelection(next) {
        var male = Math.max(0, toInt(next.male, 0));
        var female = Math.max(0, toInt(next.female, 0));
        var children = Math.max(0, toInt(next.children, 0));
        var infants = Math.max(0, toInt(next.infants, 0));
        var adults = male + female;
        if (adults < 1) {
            adults = 1;
            male = 1;
            female = 0;
        }
        state.male = male;
        state.female = female;
        state.adults = adults;
        state.children = children;
        state.infants = infants;
        state.childAges = Array.isArray(next.childAges) ? next.childAges.slice(0, children) : [];
        while (state.childAges.length < children) state.childAges.push('');
        writeToDom();
    }

    /* ---- Guest modal (compact, same field IDs as backup where shared) ---- */
    function ensureModal() {
        if (document.getElementById('mainGuestSelectorModal')) return;
        var wrap = document.createElement('div');
        wrap.innerHTML =
            '<div class="modal fade" id="mainGuestSelectorModal" tabindex="-1" aria-hidden="true">' +
            '  <div class="modal-dialog modal-md modal-dialog-centered">' +
            '    <div class="modal-content" style="border:none;border-radius:10px;overflow:hidden;">' +
            '      <div class="modal-header text-white" style="background:linear-gradient(135deg,#1e3a5f,#0f766e);">' +
            '        <h5 class="modal-title text-white mb-0" style="font-size:1rem;">Select Tour Guests</h5>' +
            '        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>' +
            '      </div>' +
            '      <div class="modal-body">' +
            '        <div class="row g-3">' +
            '          <div class="col-md-6">' +
            '            <div class="guest-counter mb-3"><label class="form-label fw-semibold">Adults</label>' +
            '              <div class="d-flex align-items-center justify-content-center">' +
            '                <button type="button" class="btn" data-guest-adj="adults" data-delta="-1"><i class="ri-subtract-line"></i></button>' +
            '                <span class="mx-3 fw-bold" id="mainModalAdults">1</span>' +
            '                <button type="button" class="btn" data-guest-adj="adults" data-delta="1"><i class="ri-add-line"></i></button>' +
            '              </div></div>' +
            '            <div class="guest-counter mb-3"><label class="form-label fw-semibold">Male</label>' +
            '              <div class="d-flex align-items-center justify-content-center">' +
            '                <button type="button" class="btn" data-guest-adj="male" data-delta="-1"><i class="ri-subtract-line"></i></button>' +
            '                <span class="mx-3 fw-bold" id="mainModalMale">0</span>' +
            '                <button type="button" class="btn" data-guest-adj="male" data-delta="1"><i class="ri-add-line"></i></button>' +
            '              </div></div>' +
            '            <div class="guest-counter"><label class="form-label fw-semibold">Female</label>' +
            '              <div class="d-flex align-items-center justify-content-center">' +
            '                <button type="button" class="btn" data-guest-adj="female" data-delta="-1"><i class="ri-subtract-line"></i></button>' +
            '                <span class="mx-3 fw-bold" id="mainModalFemale">0</span>' +
            '                <button type="button" class="btn" data-guest-adj="female" data-delta="1"><i class="ri-add-line"></i></button>' +
            '              </div></div>' +
            '          </div>' +
            '          <div class="col-md-6">' +
            '            <div class="guest-counter mb-3"><label class="form-label fw-semibold">Children <small class="text-muted">Ages 1-17</small></label>' +
            '              <div class="d-flex align-items-center justify-content-center">' +
            '                <button type="button" class="btn" data-guest-adj="children" data-delta="-1"><i class="ri-subtract-line"></i></button>' +
            '                <span class="mx-3 fw-bold" id="mainModalChildren">0</span>' +
            '                <button type="button" class="btn" data-guest-adj="children" data-delta="1"><i class="ri-add-line"></i></button>' +
            '              </div>' +
            '              <div id="childAgesSection" class="mt-2 d-none"><div id="childAgeDropdowns"></div></div>' +
            '            </div>' +
            '            <div class="guest-counter"><label class="form-label fw-semibold">Infants <small class="text-muted">Under 1</small></label>' +
            '              <div class="d-flex align-items-center justify-content-center">' +
            '                <button type="button" class="btn" data-guest-adj="infants" data-delta="-1"><i class="ri-subtract-line"></i></button>' +
            '                <span class="mx-3 fw-bold" id="mainModalInfants">0</span>' +
            '                <button type="button" class="btn" data-guest-adj="infants" data-delta="1"><i class="ri-add-line"></i></button>' +
            '              </div></div>' +
            '          </div>' +
            '        </div>' +
            '      </div>' +
            '      <div class="modal-footer">' +
            '        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>' +
            '        <button type="button" class="btn btn-primary btn-sm" id="applyMainGuestSelectionBtn">Apply</button>' +
            '      </div>' +
            '    </div>' +
            '  </div>' +
            '</div>';
        document.body.appendChild(wrap.firstElementChild);

        var modal = document.getElementById('mainGuestSelectorModal');
        modal.__draft = { male: 0, female: 0, children: 0, infants: 0, childAges: [] };

        modal.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-guest-adj]');
            if (!btn) return;
            var key = btn.getAttribute('data-guest-adj');
            var delta = toInt(btn.getAttribute('data-delta'), 0);
            adjustDraft(key, delta);
        });

        document.getElementById('applyMainGuestSelectionBtn').addEventListener('click', function () {
            applySelection(modal.__draft);
            var instance = bootstrap.Modal.getInstance(modal);
            if (instance) instance.hide();
        });
    }

    function paintDraft() {
        var modal = document.getElementById('mainGuestSelectorModal');
        if (!modal || !modal.__draft) return;
        var d = modal.__draft;
        var adults = d.male + d.female;
        document.getElementById('mainModalAdults').textContent = String(adults);
        document.getElementById('mainModalMale').textContent = String(d.male);
        document.getElementById('mainModalFemale').textContent = String(d.female);
        document.getElementById('mainModalChildren').textContent = String(d.children);
        document.getElementById('mainModalInfants').textContent = String(d.infants);
        renderChildAgeDropdowns();
    }

    function renderChildAgeDropdowns() {
        var section = document.getElementById('childAgesSection');
        var box = document.getElementById('childAgeDropdowns');
        var modal = document.getElementById('mainGuestSelectorModal');
        if (!section || !box || !modal) return;
        var d = modal.__draft;
        if (!d.children) {
            section.classList.add('d-none');
            box.innerHTML = '';
            return;
        }
        section.classList.remove('d-none');
        var html = '';
        for (var i = 0; i < d.children; i++) {
            var val = d.childAges[i] != null ? d.childAges[i] : '';
            html += '<label class="form-label mb-1" style="font-size:0.75rem;">Child ' + (i + 1) + ' age</label>';
            html += '<select class="form-select form-select-sm mb-2" data-child-age-index="' + i + '">';
            html += '<option value="">Select age</option>';
            for (var a = 1; a <= 17; a++) {
                html += '<option value="' + a + '"' + (String(val) === String(a) ? ' selected' : '') + '>' + a + '</option>';
            }
            html += '</select>';
        }
        box.innerHTML = html;
        box.querySelectorAll('[data-child-age-index]').forEach(function (sel) {
            sel.addEventListener('change', function () {
                var idx = toInt(sel.getAttribute('data-child-age-index'), 0);
                modal.__draft.childAges[idx] = sel.value;
            });
        });
    }

    function adjustDraft(key, delta) {
        var modal = document.getElementById('mainGuestSelectorModal');
        if (!modal || !modal.__draft) return;
        var d = modal.__draft;

        if (key === 'adults') {
            var adults = d.male + d.female + delta;
            if (adults < 1) adults = 1;
            // Prefer adjusting male when changing total adults
            if (delta > 0) d.male += delta;
            else {
                var rem = -delta;
                while (rem > 0 && d.male > 0) { d.male--; rem--; }
                while (rem > 0 && d.female > 0) { d.female--; rem--; }
                if (d.male + d.female < 1) { d.male = 1; d.female = 0; }
            }
        } else if (key === 'male') {
            d.male = Math.max(0, d.male + delta);
            if (d.male + d.female < 1) d.male = 1;
        } else if (key === 'female') {
            d.female = Math.max(0, d.female + delta);
            if (d.male + d.female < 1) { d.male = 1; d.female = 0; }
        } else if (key === 'children') {
            d.children = Math.max(0, d.children + delta);
            while (d.childAges.length < d.children) d.childAges.push('');
            d.childAges = d.childAges.slice(0, d.children);
        } else if (key === 'infants') {
            d.infants = Math.max(0, d.infants + delta);
        }
        paintDraft();
    }

    function openMainGuestSelector() {
        ensureModal();
        readFromDom();
        var modal = document.getElementById('mainGuestSelectorModal');
        modal.__draft = {
            male: state.male,
            female: state.female,
            children: state.children,
            infants: state.infants,
            childAges: state.childAges.slice()
        };
        paintDraft();
        var instance = bootstrap.Modal.getOrCreateInstance(modal);
        instance.show();
    }

    function setCaps(next) {
        next = next || {};
        state.male = toInt(next.male, state.male);
        state.female = toInt(next.female, state.female);
        state.children = toInt(next.children, state.children);
        state.infants = toInt(next.infants, state.infants);
        if (next.adults != null) {
            state.adults = toInt(next.adults, state.adults);
            if (state.male + state.female !== state.adults) {
                if (state.male + state.female === 0) {
                    state.male = Math.max(1, state.adults);
                    state.female = 0;
                }
            }
        } else {
            state.adults = Math.max(1, state.male + state.female);
        }
        if (Array.isArray(next.childAges)) state.childAges = next.childAges.slice();
        writeToDom();
        return getCaps();
    }

    function init() {
        readFromDom();
        writeToDom();
        window.openMainGuestSelector = openMainGuestSelector;
    }

    window.StpLiteGuestCaps = {
        init: init,
        getCaps: getCaps,
        setCaps: setCaps,
        clampSectionGuests: clampSectionGuests,
        refreshGuestDependentUI: refreshGuestDependentUI,
        applySelection: applySelection,
        open: openMainGuestSelector
    };
})(window, document);
/* === END guest-caps.js === */
