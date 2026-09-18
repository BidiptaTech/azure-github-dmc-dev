/* === STP LITE: lite-overrides.js ===
 * Depends: create/edit form DOM (tour_type, city_mode, adults/children, roomTypeSelect, accordions)
 * Owns: auto FIT/GROUP from group_pax; auto Single/Multi Country; room type names-only;
 *       hide hotel header total; input sanitize; guest child UI hide; accordion one-open globally;
 *       preserve custom price on pax change when marked data-custom-locked
 * === */
(function (window, document) {
    'use strict';

    function cfg() {
        return window.STP_LITE_CONFIG || {};
    }

    function toInt(v, d) {
        var n = parseInt(v, 10);
        return isNaN(n) ? (d || 0) : n;
    }

    /* ---------- FIT / GROUP (auto, read-only) ---------- */
    function ensureTourTypeHidden() {
        var form = document.getElementById('singleTourPackageForm') || document.querySelector('form');
        if (!form) return;
        var hidden = document.getElementById('tour_type_value');
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'tour_type';
            hidden.id = 'tour_type_value';
            form.appendChild(hidden);
        }
        return hidden;
    }

    function setTourType(type) {
        var next = String(type || 'FIT').toUpperCase() === 'GROUP' ? 'GROUP' : 'FIT';
        var fit = document.getElementById('fit');
        var group = document.getElementById('group');
        if (fit && group) {
            fit.checked = next === 'FIT';
            group.checked = next === 'GROUP';
            fit.disabled = true;
            group.disabled = true;
        }
        // Keep radio name=tour_type for existing JS (:checked still works when disabled).
        // Hidden field posts the value (disabled radios are omitted from submit).
        var hidden = ensureTourTypeHidden();
        if (hidden) hidden.value = next;
        window.selectedTourType = next;

        var groupWrap = document.getElementById('groupDetailsWrapper');
        if (groupWrap) groupWrap.classList.toggle('d-none', next !== 'GROUP');

        try {
            if (fit) fit.dispatchEvent(new Event('change', { bubbles: true }));
        } catch (e) { /* ignore */ }
        return next;
    }

    function syncTourTypeFromPax() {
        var threshold = toInt(cfg().dmcGroupPax, 0);
        var adults = toInt((document.getElementById('adults') || {}).value, 0);
        var children = toInt((document.getElementById('children') || {}).value, 0);
        var total = adults + children;
        var next = (threshold > 0 && total >= threshold) ? 'GROUP' : 'FIT';
        return setTourType(next);
    }

    function lockTourTypeToggle() {
        document.querySelectorAll('.tour-toggle label, .tour-toggle input').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                syncTourTypeFromPax();
            }, true);
        });
        var wrap = document.querySelector('.tour-toggle');
        if (wrap) {
            wrap.style.pointerEvents = 'none';
            wrap.title = 'Auto from DMC Group Pax (adults + children)';
        }
    }

    /* ---------- Single / Multi Country (auto, read-only) ---------- */
    function setCityMode(mode) {
        var next = String(mode || 'single').toLowerCase() === 'multi' ? 'multi' : 'single';
        if (cfg().isThirdPartyDmc) next = 'single';

        var single = document.getElementById('city_mode_single');
        var multi = document.getElementById('city_mode_multi');
        if (single && multi) {
            single.checked = next === 'single';
            multi.checked = next === 'multi';
            single.disabled = true;
            multi.disabled = true;
        }

        document.querySelectorAll('input[name="city_mode"], input[name="city_type"]').forEach(function (r) {
            if (r.type === 'hidden') {
                r.value = next;
                return;
            }
            // Keep name for existing JS that reads city_mode, but prevent user clicks
            r.disabled = true;
            r.checked = (r.value === next);
        });

        // Hidden submit helpers
        ['city_type', 'city_mode'].forEach(function (name) {
            var el = document.querySelector('input[type="hidden"][name="' + name + '"]');
            if (!el) {
                var form = document.getElementById('singleTourPackageForm') || document.querySelector('form');
                if (!form) return;
                el = document.createElement('input');
                el.type = 'hidden';
                el.name = name;
                form.appendChild(el);
            }
            el.value = next;
        });

        var wrap = document.querySelector('.city-toggle');
        if (wrap) {
            wrap.style.pointerEvents = 'none';
            wrap.title = 'Auto from selected countries';
        }

        // Trigger existing mode UI if present
        try {
            if (window.jQuery) {
                window.jQuery('input[name="city_mode"]').first().trigger('change');
            }
        } catch (e) { /* ignore */ }

        return next;
    }

    function collectSelectedCountries() {
        var set = {};
        var selectors = ['#multi_cities option:checked', '#single_city option:selected', '#user_country option:selected', '#lite_countries option:selected'];
        selectors.forEach(function (sel) {
            document.querySelectorAll(sel).forEach(function (opt) {
                var c = String(opt.getAttribute('data-country') || '').trim();
                if (!c) {
                    var text = String(opt.textContent || '');
                    var m = text.match(/\(([^)]+)\)\s*$/);
                    if (m) c = String(m[1]).trim();
                }
                if (!c && opt.value && sel.indexOf('user_country') !== -1) c = String(opt.value).trim();
                if (!c && opt.value && sel.indexOf('lite_countries') !== -1) c = String(opt.value).trim();
                if (c) set[c] = true;
            });
        });

        // Multi-city plan rows
        document.querySelectorAll('#segmentsWrapper [data-country], tr[data-country]').forEach(function (el) {
            var c = String(el.getAttribute('data-country') || '').trim();
            if (c) set[c] = true;
        });

        return Object.keys(set);
    }

    function syncCountryMode() {
        var countries = collectSelectedCountries();
        var mode = countries.length > 1 ? 'multi' : 'single';
        tintCountrySegments(countries);
        return setCityMode(mode);
    }

    function tintCountrySegments(countries) {
        var colors = [
            'rgba(15, 118, 110, 0.10)',
            'rgba(30, 58, 95, 0.10)',
            'rgba(180, 83, 9, 0.10)',
            'rgba(124, 58, 237, 0.10)'
        ];
        var borders = ['#0f766e', '#1e3a5f', '#9a3412', '#6d28d9'];
        document.querySelectorAll('#segmentsWrapper .segment, #segmentsWrapper .card, .mc-segment, [data-segment-country]').forEach(function (el) {
            var c = String(el.getAttribute('data-country') || el.getAttribute('data-segment-country') || '').trim();
            if (!c) {
                var label = el.querySelector('.segment-country, .country-name, [data-country]');
                if (label) c = String(label.getAttribute('data-country') || label.textContent || '').trim();
            }
            var idx = Math.max(0, countries.indexOf(c));
            if (c && countries.indexOf(c) === -1) {
                // hash fallback
                idx = Math.abs(c.split('').reduce(function (a, ch) { return a + ch.charCodeAt(0); }, 0)) % colors.length;
            }
            el.style.background = colors[idx % colors.length];
            el.style.borderLeft = '4px solid ' + borders[idx % borders.length];
        });
    }

    function lockCountryToggle() {
        document.querySelectorAll('.city-toggle label, .city-toggle input').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                syncCountryMode();
            }, true);
        });
    }

    /* ---------- Room type: names only ---------- */
    function stripRoomTypePrices() {
        var sel = document.getElementById('roomTypeSelect');
        if (!sel) return;
        Array.prototype.forEach.call(sel.options, function (opt) {
            if (!opt.value) return;
            var name = opt.dataset.roomType || opt.value;
            if (opt.textContent !== name) opt.textContent = name;
        });
    }

    function watchRoomTypeSelect() {
        var sel = document.getElementById('roomTypeSelect');
        if (!sel || sel.__stpLiteWatched) return;
        sel.__stpLiteWatched = true;
        var obs = new MutationObserver(function () { stripRoomTypePrices(); });
        obs.observe(sel, { childList: true });
        stripRoomTypePrices();
    }

    /* ---------- Hotel header: hide Total Price (keep Get Price) ---------- */
    function hideHotelHeaderTotal() {
        var total = document.getElementById('hotelTotalPrice');
        if (total) {
            var box = total.closest('.text-end') || total.parentElement;
            if (box) box.style.display = 'none';
        }
    }

    /* ---------- Input sanitize ---------- */
    function bindSanitize() {
        if (window.StpLiteInputSanitize && typeof window.StpLiteInputSanitize.bind === 'function') {
            window.StpLiteInputSanitize.bind(document);
            return;
        }
        var SPECIAL = /[<>"'`\\;$%^*=\[\]{}|~]/g;
        var NAME_STRIP = /[^a-zA-Z\s'.-]/g;
        document.addEventListener('keydown', function (e) {
            var el = e.target;
            if (!el || el.tagName !== 'INPUT') return;
            if (el.type === 'number' || el.getAttribute('inputmode') === 'numeric') {
                if (['e', 'E', '+', '-'].indexOf(e.key) !== -1) e.preventDefault();
            }
        }, true);
        document.addEventListener('input', function (e) {
            var el = e.target;
            if (!el) return;
            if (el.tagName === 'INPUT' && (el.type === 'number' || el.getAttribute('inputmode') === 'numeric')) {
                var cleaned = String(el.value || '').replace(/[^\d.]/g, '');
                if (el.step === '1' || el.classList.contains('stp-lite-int')) {
                    cleaned = cleaned.replace(/\./g, '');
                }
                if (el.value !== cleaned) el.value = cleaned;
                return;
            }
            var isName = el.dataset && el.dataset.sanitize === 'name'
                || (el.classList && el.classList.contains('stp-lite-guest-name'))
                || el.id === 'customerFullName';
            if (isName && el.tagName === 'INPUT') {
                var nameNext = String(el.value || '').replace(NAME_STRIP, '');
                if (el.value !== nameNext) el.value = nameNext;
                return;
            }
            var isPhone = el.dataset && el.dataset.sanitize === 'phone'
                || (el.classList && el.classList.contains('stp-lite-guest-phone'))
                || el.id === 'customerPhone'
                || /additional_guests\[\d+]\[contact_no]$/.test(String(el.getAttribute('name') || ''));
            if (isPhone && el.tagName === 'INPUT') {
                var phoneNext = String(el.value || '').replace(/[^\d]/g, '');
                if (el.value !== phoneNext) el.value = phoneNext;
                return;
            }
            if (el.type === 'email' || (el.dataset && el.dataset.sanitize === 'email')) {
                return;
            }
            if ((el.tagName === 'INPUT' && el.type === 'text') || el.tagName === 'TEXTAREA') {
                if (el.dataset.allowSpecial === '1') return;
                var next = String(el.value || '').replace(SPECIAL, '');
                if (el.value !== next) el.value = next;
            }
        }, true);
    }

    /* ---------- Guest caps: hide child UI when children=0 ---------- */
    function refreshChildUi() {
        var children = toInt((document.getElementById('children') || {}).value, 0);
        var hide = children <= 0;
        var selectors = [
            '#childWithBedSection', '#childWithoutBedSection', '#hotelChildSection',
            '.child-with-bed-wrap', '.child-without-bed-wrap', '[data-guest-child-ui]',
            '#pleaseAddChildFirst', '.please-add-child-first'
        ];
        selectors.forEach(function (sel) {
            document.querySelectorAll(sel).forEach(function (el) {
                el.classList.toggle('d-none', hide);
                if (hide) el.style.display = 'none';
                else if (el.style.display === 'none' && el.classList.contains('d-none') === false) {
                    el.style.display = '';
                }
            });
        });
        // Cap section number inputs marked data-guest-cap
        document.querySelectorAll('[data-guest-cap]').forEach(function (el) {
            var key = el.getAttribute('data-guest-cap');
            var max = toInt((document.getElementById(key) || {}).value, 0);
            if (key === 'adults') max = toInt((document.getElementById('adults') || {}).value, 0);
            el.setAttribute('max', String(max));
            if (toInt(el.value, 0) > max) el.value = String(max);
        });
        syncTourTypeFromPax();
    }

    /* ---------- Accordion: one open across entire form ---------- */
    function bindGlobalAccordion() {
        document.addEventListener('show.bs.collapse', function (e) {
            var opening = e.target;
            if (!opening || !opening.classList.contains('collapse')) return;
            // Skip tiny nested collapses that aren't service panels
            if (opening.closest('.modal')) return;
            if (opening.id && String(opening.id).indexOf('segmentFullCollapse_') === 0) return;

            document.querySelectorAll('.collapse.show').forEach(function (el) {
                if (el === opening) return;
                if (el.closest('.modal')) return;
                if (el.id && String(el.id).indexOf('segmentFullCollapse_') === 0) return;
                // Only close service-like panels
                if (!(el.id && (
                    el.id.indexOf('Section') !== -1 ||
                    el.id.indexOf('section') !== -1 ||
                    el.closest('#servicesAccordion, #servicesAccordionInner, #segmentServicesBundle')
                ))) return;
                try {
                    if (window.bootstrap && bootstrap.Collapse) {
                        bootstrap.Collapse.getOrCreateInstance(el, { toggle: false }).hide();
                    } else if (window.jQuery) {
                        window.jQuery(el).collapse('hide');
                    }
                } catch (err) { /* ignore */ }
            });
        });
    }

    /* ---------- Custom price lock: don't wipe on adult/child change ---------- */
    function bindCustomPriceLock() {
        document.addEventListener('input', function (e) {
            var el = e.target;
            if (!el || !el.id) return;
            if (String(el.id).indexOf('custom_price') !== -1) {
                el.dataset.customLocked = el.value !== '' ? '1' : '0';
            }
        }, true);

        // When recalculators try to set value on a locked custom price, restore
        document.addEventListener('change', function (e) {
            var t = e.target;
            if (!t || !t.id) return;
            if (t.id === 'adults' || t.id === 'children' || t.id === 'male' || t.id === 'female') {
                document.querySelectorAll('input[id*="custom_price"][data-custom-locked="1"]').forEach(function (inp) {
                    var lockedVal = inp.value;
                    setTimeout(function () {
                        if (inp.dataset.customLocked === '1' && inp.value !== lockedVal && lockedVal !== '') {
                            inp.value = lockedVal;
                            try { inp.dispatchEvent(new Event('input', { bubbles: true })); } catch (err) { /* ignore */ }
                        }
                    }, 50);
                });
            }
        }, true);
    }

    /* ---------- Relabel Multi City → Multi Country in live DOM ---------- */
    function relabelCountryMode() {
        document.querySelectorAll('label[for="city_mode_single"]').forEach(function (l) {
            l.textContent = 'Single Country';
        });
        document.querySelectorAll('label[for="city_mode_multi"]').forEach(function (l) {
            l.textContent = 'Multi Country';
        });
        // Remove MDMC / Master DMC wording from visible labels
        document.querySelectorAll('label, small, .form-label, h6, span').forEach(function (el) {
            if (!el.childElementCount && el.textContent) {
                var t = el.textContent;
                if (/Master DMC|MDMC|Master List/i.test(t)) {
                    el.textContent = t
                        .replace(/\s*\(Master DMC\)/ig, '')
                        .replace(/Master DMC\s*/ig, '')
                        .replace(/MDMC\s*/ig, '')
                        .replace(/\s*\(Master List\)/ig, '')
                        .replace(/Cities \(Master List\)/ig, 'Cities')
                        .replace(/Select Cities \(Master List\)/ig, 'Select Cities');
                }
            }
        });
    }

    function bindWatchers() {
        ['adults', 'children', 'male', 'female', 'infants'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('change', refreshChildUi);
        });
        document.addEventListener('stp:guests-changed', refreshChildUi);
        // After guest modal apply, many forms update hidden fields then call updateMainGuestSummary
        var _origApply = window.applyMainGuestSelection;
        if (typeof _origApply === 'function') {
            window.applyMainGuestSelection = function () {
                var r = _origApply.apply(this, arguments);
                refreshChildUi();
                return r;
            };
        }

        ['#multi_cities', '#single_city', '#user_country', '#lite_countries'].forEach(function (sel) {
            var el = document.querySelector(sel);
            if (!el) return;
            el.addEventListener('change', syncCountryMode);
            if (window.jQuery) {
                window.jQuery(el).on('change select2:select select2:clear select2:unselect', syncCountryMode);
            }
        });

        // Observe segment wrapper for new country plans
        var wrap = document.getElementById('segmentsWrapper');
        if (wrap) {
            new MutationObserver(function () { syncCountryMode(); }).observe(wrap, { childList: true, subtree: true });
        }
    }

    function boot() {
        window.STP_LITE_CONFIG = window.STP_LITE_CONFIG || {};
        lockTourTypeToggle();
        lockCountryToggle();
        bindSanitize();
        bindGlobalAccordion();
        bindCustomPriceLock();
        bindWatchers();
        watchRoomTypeSelect();
        hideHotelHeaderTotal();
        relabelCountryMode();
        refreshChildUi();
        syncTourTypeFromPax();
        syncCountryMode();
        console.info('[STP Lite] overrides active');
    }

    function whenReady(fn) {
        function go() {
            // Wait for jQuery used by the form (loaded after this script in blade)
            if (!window.jQuery) {
                setTimeout(go, 40);
                return;
            }
            fn();
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', go);
        } else {
            go();
        }
    }

    whenReady(function () {
        // After form's own DOMContentLoaded handlers
        setTimeout(boot, 100);
    });

    window.StpLiteOverrides = {
        syncTourTypeFromPax: syncTourTypeFromPax,
        syncCountryMode: syncCountryMode,
        refreshChildUi: refreshChildUi,
        stripRoomTypePrices: stripRoomTypePrices
    };
})(window, document);
/* === END lite-overrides.js === */
