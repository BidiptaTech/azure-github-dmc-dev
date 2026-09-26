/* === STP LITE: hotel.js ===
 * Depends: geo.buildInventoryDmcQuery, country-segments, guest-caps, STP_LITE_CONFIG.routes
 * Owns: hotel / room type (names only) / bed type / meal plan — same APIs as backup
 * Routes: fetch-hotels-by-dmc, fetch-rooms-by-hotel, fetch-beds-by-room
 * === */
(function (window, document) {
    'use strict';

    function cfg() {
        return window.STP_LITE_CONFIG || {};
    }

    function esc(s) {
        return String(s || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function isBreakfastIncluded(r) {
        if (!r) return false;
        return r.breakfast_included == 1 || r.breakfast_included === true || r.breakfast_included === '1'
            || String(r.breakfast_type || '').toLowerCase().indexOf('complement') !== -1
            || String(r.breakfast_type || '').toLowerCase().indexOf('inclu') !== -1;
    }

    function breakfastPhrase(rooms) {
        var list = Array.isArray(rooms) ? rooms : [];
        if (!list.length) return '';
        if (list.some(isBreakfastIncluded)) return 'complementary breakfast';
        var hasBf = list.some(function (r) {
            return r && (r.breakfast == 1 || r.breakfast === true || r.breakfast === '1');
        });
        return hasBf ? 'breakfast' : '';
    }

    function buildMealPlanOptions(rooms) {
        var list = Array.isArray(rooms) ? rooms : [];
        var plans = [];
        var roomText = 'room';
        var hasRoomsOnly = list.some(function (r) {
            return r && (r.rooms_only == 1 || r.rooms_only === true || r.rooms_only === '1');
        });
        var hasComplementary = list.some(isBreakfastIncluded);
        var bfPhrase = breakfastPhrase(list);
        var hasBreakfast = !!bfPhrase;
        var hasLunch = list.some(function (r) {
            return r && (r.lunch == 1 || r.lunch === true || r.lunch === '1');
        });
        var hasDinner = list.some(function (r) {
            return r && (r.dinner == 1 || r.dinner === true || r.dinner === '1');
        });

        if (!hasRoomsOnly && !hasComplementary) plans.push(roomText + ' only');
        if (hasBreakfast) {
            // Paid breakfast (not complementary) — show as add-on, not "included"
            plans.push(hasComplementary
                ? (roomText + ' with ' + bfPhrase)
                : 'Room + Breakfast Add-on');
        }
        if (hasLunch) plans.push(roomText + ' with lunch');
        if (hasDinner) plans.push(roomText + ' with dinner');
        if (hasBreakfast && hasLunch) {
            plans.push(hasComplementary
                ? (roomText + ' with ' + bfPhrase + ' + lunch')
                : 'Room + Breakfast Add-on + lunch');
        }
        if (hasBreakfast && hasDinner) {
            plans.push(hasComplementary
                ? (roomText + ' with ' + bfPhrase + ' + dinner')
                : 'Room + Breakfast Add-on + dinner');
        }
        if (hasLunch && hasDinner) plans.push(roomText + ' with lunch + dinner');
        if (hasBreakfast && hasLunch && hasDinner) {
            plans.push(hasComplementary
                ? (roomText + ' with all meals (' + bfPhrase + ' + lunch + dinner)')
                : 'Room + Breakfast Add-on + lunch + dinner');
        }
        return plans;
    }

    function shellHtml(country, currency, stay) {
        stay = stay || {};
        var cityLabel = stay.cityName || '';
        if (stay.isReturn) cityLabel += ' (Return)';
        if (stay.start && stay.end && typeof moment !== 'undefined') {
            cityLabel += ' · ' + moment(stay.start, 'YYYY-MM-DD').format('MMM D') + '–' +
                moment(stay.end, 'YYYY-MM-DD').format('MMM D');
        }
        return (
            '<div class="stp-lite-hotel" data-country="' + esc(country) + '" data-currency="' + esc(currency) + '"' +
            ' data-city-id="' + esc(stay.cityId || '') + '"' +
            ' data-city-name="' + esc(stay.cityName || '') + '"' +
            ' data-plan-index="' + esc(stay.planIndex || '') + '"' +
            ' data-is-return="' + (stay.isReturn ? '1' : '0') + '"' +
            ' data-stay-start="' + esc(stay.start || '') + '"' +
            ' data-stay-end="' + esc(stay.end || '') + '">' +
            '  <div class="row g-2 mb-2">' +
            '    <div class="col-md-3">' +
            '      <label class="stp-lite-label">City</label>' +
            '      <div class="stp-lite-city-static hotel-city-static" title="' + esc(cityLabel) + '">' + esc(cityLabel || '—') + '</div>' +
            '      <select class="d-none hotel-city-select" aria-hidden="true" tabindex="-1"></select>' +
            '    </div>' +
            '    <div class="col-md-3">' +
            '      <label class="stp-lite-label">Hotel</label>' +
            '      <select class="form-select form-select-sm hotel-select" disabled data-no-select2="true"><option value="">Select hotel</option></select>' +
            '    </div>' +
            '    <div class="col-md-3">' +
            '      <label class="stp-lite-label">Room Type</label>' +
            '      <select class="form-select form-select-sm hotel-room-type" disabled><option value="">Select hotel first</option></select>' +
            '    </div>' +
            '    <div class="col-md-3">' +
            '      <label class="stp-lite-label">Bed Type</label>' +
            '      <select class="form-select form-select-sm hotel-bed-type" disabled><option value="">Select room type first</option></select>' +
            '    </div>' +
            '  </div>' +
            '  <div class="row g-2 mb-2">' +
            '    <div class="col-md-2">' +
            '      <label class="stp-lite-label">Check-in</label>' +
            '      <input type="date" class="form-control form-control-sm hotel-check-in" value="' + esc(stay.start || '') + '"' +
            (stay.start ? ' min="' + esc(stay.start) + '"' : '') +
            (stay.end ? ' max="' + esc(stay.end) + '"' : '') + '>' +
            '    </div>' +
            '    <div class="col-md-2">' +
            '      <label class="stp-lite-label">Check-out</label>' +
            '      <input type="date" class="form-control form-control-sm hotel-check-out" value="' + esc(stay.end || '') + '"' +
            (stay.start ? ' min="' + esc(stay.start) + '"' : '') +
            (stay.end ? ' max="' + esc(stay.end) + '"' : '') + '>' +
            '    </div>' +
            '    <div class="col-md-3">' +
            '      <label class="stp-lite-label">Meal Plan</label>' +
            '      <select class="form-select form-select-sm hotel-meal-plan" disabled><option value="">Select hotel first</option></select>' +
            '    </div>' +
            '    <div class="col-md-1">' +
            '      <label class="stp-lite-label">Rooms</label>' +
            '      <input type="number" min="1" class="form-control form-control-sm stp-lite-int hotel-rooms" value="1">' +
            '    </div>' +
            '  </div>' +
            '  <div class="row g-2 mb-2 align-items-stretch hotel-occ-adhoc-row">' +
            '    <div class="col-lg-8 col-md-7">' +
            '      <div class="stp-lite-occ-card">' +
            '        <div class="stp-lite-occ-card__head">' +
            '          <div class="stp-lite-occ-card__title"><i class="ri-group-line"></i> Room occupancy</div>' +
            '          <div class="stp-lite-occ-summary hotel-occ-summary">Select bed type</div>' +
            '        </div>' +
            '        <div class="stp-lite-occ-card__body hotel-occ-body">' +
            '          <div class="stp-lite-occ-empty hotel-occ-need-bed">Select bed type first</div>' +
            '          <div class="stp-lite-occ-pickers d-none hotel-occ-pickers">' +
            '            <div class="stp-lite-occ-stepper" data-occ-role="adults">' +
            '              <div class="stp-lite-occ-stepper__icon"><i class="ri-user-fill"></i></div>' +
            '              <div class="stp-lite-occ-stepper__info">' +
            '                <strong>Adults</strong>' +
            '                <small class="hotel-adult-hint">In this room</small>' +
            '              </div>' +
            '              <div class="stp-lite-occ-stepper__ctrl">' +
            '                <button type="button" class="stp-lite-occ-btn hotel-adult-minus" aria-label="Fewer adults"><i class="ri-subtract-line"></i></button>' +
            '                <span class="stp-lite-occ-count hotel-adult-count">1</span>' +
            '                <button type="button" class="stp-lite-occ-btn hotel-adult-plus" aria-label="More adults"><i class="ri-add-line"></i></button>' +
            '              </div>' +
            '            </div>' +
            '            <div class="stp-lite-occ-stepper is-child hotel-child-nobed-stepper d-none" data-occ-role="children-no-bed">' +
            '              <div class="stp-lite-occ-stepper__icon"><i class="ri-user-smile-fill"></i></div>' +
            '              <div class="stp-lite-occ-stepper__info">' +
            '                <strong>Children (no bed)</strong>' +
            '                <small class="hotel-child-nobed-hint">Share room bed</small>' +
            '              </div>' +
            '              <div class="stp-lite-occ-stepper__ctrl">' +
            '                <button type="button" class="stp-lite-occ-btn hotel-child-nobed-minus" aria-label="Fewer children without bed"><i class="ri-subtract-line"></i></button>' +
            '                <span class="stp-lite-occ-count hotel-child-nobed-count">0</span>' +
            '                <button type="button" class="stp-lite-occ-btn hotel-child-nobed-plus" aria-label="More children without bed"><i class="ri-add-line"></i></button>' +
            '              </div>' +
            '            </div>' +
            '            <div class="stp-lite-occ-stepper is-child-bed hotel-child-withbed-stepper d-none" data-occ-role="children-with-bed">' +
            '              <div class="stp-lite-occ-stepper__icon"><i class="ri-hotel-bed-fill"></i></div>' +
            '              <div class="stp-lite-occ-stepper__info">' +
            '                <strong>Children (with bed)</strong>' +
            '                <small class="hotel-child-withbed-hint">Extra bed only</small>' +
            '              </div>' +
            '              <div class="stp-lite-occ-stepper__ctrl">' +
            '                <button type="button" class="stp-lite-occ-btn hotel-child-withbed-minus" aria-label="Fewer children with bed"><i class="ri-subtract-line"></i></button>' +
            '                <span class="stp-lite-occ-count hotel-child-withbed-count">0</span>' +
            '                <button type="button" class="stp-lite-occ-btn hotel-child-withbed-plus" aria-label="More children with bed"><i class="ri-add-line"></i></button>' +
            '              </div>' +
            '            </div>' +
            '            <div class="stp-lite-occ-stepper is-infant hotel-infant-stepper d-none" data-occ-role="infants">' +
            '              <div class="stp-lite-occ-stepper__icon"><i class="ri-parent-line"></i></div>' +
            '              <div class="stp-lite-occ-stepper__info">' +
            '                <strong>Infants</strong>' +
            '                <small class="hotel-infant-hint">By hotel age limit</small>' +
            '              </div>' +
            '              <div class="stp-lite-occ-stepper__ctrl">' +
            '                <button type="button" class="stp-lite-occ-btn hotel-infant-minus" aria-label="Fewer infants"><i class="ri-subtract-line"></i></button>' +
            '                <span class="stp-lite-occ-count hotel-infant-count">0</span>' +
            '                <button type="button" class="stp-lite-occ-btn hotel-infant-plus" aria-label="More infants"><i class="ri-add-line"></i></button>' +
            '              </div>' +
            '            </div>' +
            '            <div class="stp-lite-occ-bedopts hotel-child-pricing-section d-none">' +
            '              <label class="stp-lite-child-chip hotel-baby-cot-wrap d-none">' +
            '                <input class="hotel-chk-baby-cot" type="checkbox">' +
            '                <span class="stp-lite-child-chip__face">' +
            '                  <i class="ri-parent-line"></i>' +
            '                  <span class="stp-lite-child-chip__text">' +
            '                    <strong>Baby cot</strong>' +
            '                    <small class="hotel-baby-cot-label"></small>' +
            '                  </span>' +
            '                </span>' +
            '              </label>' +
            '            </div>' +
            '          </div>' +
            '          <div class="stp-lite-occ-age-note hotel-age-class-note d-none"></div>' +
            '          <input type="hidden" class="hotel-selected-adults" value="1">' +
            '          <input type="hidden" class="hotel-selected-children" value="0">' +
            '          <input type="hidden" class="hotel-selected-children-no-bed" value="0">' +
            '          <input type="hidden" class="hotel-selected-children-with-bed" value="0">' +
            '          <input type="hidden" class="hotel-selected-infants" value="0">' +
            '          <input type="hidden" class="hotel-selected-persons" value="1">' +
            '        </div>' +
            '      </div>' +
            '    </div>' +
            '    <div class="col-lg-4 col-md-5">' +
            '      <div class="stp-lite-hotel-adhoc-card">' +
            '        <div class="stp-lite-hotel-adhoc-card__head">' +
            '          <div class="stp-lite-hotel-adhoc-card__title"><i class="ri-money-dollar-circle-line"></i> AdHoc</div>' +
            '          <small class="text-muted">Manual Room rate</small>' +
            '        </div>' +
            '        <div class="stp-lite-hotel-adhoc-card__body">' +
            '          <div class="stp-lite-hotel-adhoc-row">' +
            '            <label class="stp-lite-child-chip hotel-adhoc-toggle-wrap">' +
            '              <input class="hotel-chk-adhoc" type="checkbox">' +
            '              <span class="stp-lite-child-chip__face">' +
            '                <i class="ri-toggle-line"></i>' +
            '                <span class="stp-lite-child-chip__text">' +
            '                  <strong>Enable AdHoc</strong>' +
            '                  <small>Room rate only</small>' +
            '                </span>' +
            '              </span>' +
            '            </label>' +
            '            <div class="hotel-adhoc-price-wrap d-none">' +
            '              <input type="text" inputmode="decimal" autocomplete="off"' +
            '                class="form-control form-control-sm hotel-adhoc-price"' +
            '                placeholder="Rate / night" value="" title="Manual Room rate (per night)">' +
            '            </div>' +
            '          </div>' +
            '        </div>' +
            '      </div>' +
            '    </div>' +
            '  </div>' +
            '  <div class="row g-2 mb-2">' +
            '    <div class="col-md-12 d-flex align-items-end gap-2 flex-wrap">' +
            '      <button type="button" class="btn btn-sm stp-lite-get-price-btn hotel-get-price-btn">' +
            '        <i class="ri-price-tag-3-line me-1"></i>Get Price' +
            '      </button>' +
            '      <button type="button" class="btn btn-sm btn-primary hotel-add-btn" disabled>' +
            '        <i class="ri-add-line me-1"></i>Add Hotel' +
            '      </button>' +
            '      <span class="stp-lite-loader hotel-price-loader"><span class="spinner-border spinner-border-sm"></span> Calculating...</span>' +
            '    </div>' +
            '  </div>' +
            '  <div class="stp-lite-hotel-breakup d-none" data-hotel-breakup-panel>' +
            '    <div class="stp-lite-hotel-breakup-card">' +
            '      <div class="stp-lite-hotel-breakup-head">' +
            '        <div>' +
            '          <div class="stp-lite-hotel-breakup-title"><i class="ri-hotel-line me-1"></i>Hotel Pricing Details</div>' +
            '          <div class="stp-lite-hotel-breakup-sub text-muted">Auto-updated after Get Price · ' + esc(currency) + '</div>' +
            '        </div>' +
            '        <button type="button" class="btn btn-sm btn-light hotel-breakup-close" title="Hide calculation">' +
            '          <i class="ri-close-line"></i> Close' +
            '        </button>' +
            '      </div>' +
            '      <div class="stp-lite-hotel-breakup-body" data-hotel-breakup-body>' +
            '        <div class="hotel-breakup-grid"></div>' +
            '        <div class="stp-lite-hotel-breakup-total">' +
            '          <span><strong>Total:</strong></span>' +
            '          <strong class="hotel-breakup-grand">' + esc(currency) + ' 0.00</strong>' +
            '        </div>' +
            '      </div>' +
            '    </div>' +
            '  </div>' +
            '  <div class="stp-lite-hotel-added mt-2" data-hotel-added-list></div>' +
            '  <input type="hidden" class="hotel_data_chunk" value="[]">' +
            '</div>'
        );
    }

    /** Stay bound to this hotel section (one stay = one section). */
    function stayContextFromRoot(root) {
        var panel = root && root.closest ? root.closest('[data-service="hotel"]') : null;
        var src = panel || root;
        if (!src || !src.getAttribute) return null;
        var cityName = src.getAttribute('data-city-name') || root.getAttribute('data-city-name') || '';
        var country = src.getAttribute('data-country') || root.getAttribute('data-country') || '';
        var start = src.getAttribute('data-stay-start') || root.getAttribute('data-stay-start') || '';
        var end = src.getAttribute('data-stay-end') || root.getAttribute('data-stay-end') || '';
        var planIndex = src.getAttribute('data-plan-index') || root.getAttribute('data-plan-index') || '';
        var isReturn = (src.getAttribute('data-is-return') || root.getAttribute('data-is-return')) === '1';
        var cityId = src.getAttribute('data-city-id') || root.getAttribute('data-city-id') || '';

        if ((!start || !end || !cityName) && planIndex && window.StpLiteCountrySegments) {
            var hit = (window.StpLiteCountrySegments.getActivePlans() || []).find(function (p) {
                return String(p.index) === String(planIndex);
            });
            if (hit) {
                start = hit.start || start;
                end = hit.end || end;
                cityName = hit.cityName || cityName;
                country = hit.country || country;
                cityId = hit.cityId || cityId;
                isReturn = !!hit.isReturn;
            }
        }
        if (!cityName) return null;
        return {
            cityId: cityId,
            cityName: cityName,
            country: country,
            planIndex: planIndex,
            isReturn: isReturn,
            start: start,
            end: end
        };
    }

    function citiesForCountry(country) {
        var list = [];
        if (window.StpLiteCountrySegments) {
            (window.StpLiteCountrySegments.getActivePlans() || []).forEach(function (p) {
                if (String(p.country || '').toLowerCase() !== String(country || '').toLowerCase()) return;
                list.push({
                    cityId: p.cityId,
                    cityName: p.cityName,
                    country: p.country,
                    planIndex: p.index,
                    isReturn: !!p.isReturn,
                    start: p.start || '',
                    end: p.end || ''
                });
            });
        }
        return list;
    }

    function stayDatesFromOption(opt, cityName, country) {
        if (opt) {
            var start = opt.getAttribute('data-stay-start') || '';
            var end = opt.getAttribute('data-stay-end') || '';
            if (start && end) return { start: start, end: end };
            var planIndex = opt.getAttribute('data-plan-index') || '';
            if (planIndex && window.StpLiteCountrySegments) {
                var hit = (window.StpLiteCountrySegments.getActivePlans() || []).find(function (p) {
                    return String(p.index) === String(planIndex);
                });
                if (hit && hit.start && hit.end) return { start: hit.start, end: hit.end };
            }
        }
        return stayDatesForCity(cityName, country);
    }

    function stayDatesForCity(cityName, country) {
        var start = '';
        var end = '';
        if (window.StpLiteCountrySegments && typeof window.StpLiteCountrySegments.getActivePlans === 'function') {
            (window.StpLiteCountrySegments.getActivePlans() || []).forEach(function (p) {
                if (String(p.cityName || '').toLowerCase() !== String(cityName || '').toLowerCase()) return;
                if (country && String(p.country || '').toLowerCase() !== String(country || '').toLowerCase()) return;
                // Prefer first matching stay if no specific plan option
                if (!start && !end) {
                    start = p.start || '';
                    end = p.end || '';
                }
            });
        }
        if (!start || !end) {
            start = (document.getElementById('start_date') || {}).value || '';
            end = (document.getElementById('end_date') || {}).value || '';
        }
        return { start: start, end: end };
    }

    /** Nights = [stayFrom, stayUntil) — checkout day is not a night. */
    function nightDatesFromStay(start, end) {
        if (typeof moment === 'undefined' || !start || !end) return [];
        var a = moment(start, 'YYYY-MM-DD', true);
        var b = moment(end, 'YYYY-MM-DD', true);
        if (!a.isValid() || !b.isValid() || !b.isAfter(a, 'day')) return [];
        var nights = [];
        var cur = a.clone();
        while (cur.isBefore(b, 'day')) {
            nights.push(cur.format('YYYY-MM-DD'));
            cur.add(1, 'day');
        }
        return nights;
    }

    /** Selected hotel check-in/out, clamped to the city stay window. */
    function selectedHotelDates(root) {
        var stay = stayContextFromRoot(root) || {};
        var checkInEl = root.querySelector('.hotel-check-in');
        var checkOutEl = root.querySelector('.hotel-check-out');
        var start = (checkInEl && checkInEl.value) || stay.start || '';
        var end = (checkOutEl && checkOutEl.value) || stay.end || '';
        if (stay.start && start && start < stay.start) start = stay.start;
        if (stay.end && start && start > stay.end) start = stay.end;
        if (stay.start && end && end < stay.start) end = stay.start;
        if (stay.end && end && end > stay.end) end = stay.end;
        if (start && end && end <= start && typeof moment !== 'undefined') {
            var next = moment(start, 'YYYY-MM-DD').add(1, 'day').format('YYYY-MM-DD');
            if (stay.end && next > stay.end) next = stay.end;
            end = next;
            if (checkOutEl) checkOutEl.value = end;
        }
        return { start: start, end: end, stayStart: stay.start || '', stayEnd: stay.end || '' };
    }

    function syncHotelDateLimits(root) {
        var stay = stayContextFromRoot(root) || {};
        var checkInEl = root.querySelector('.hotel-check-in');
        var checkOutEl = root.querySelector('.hotel-check-out');
        if (!checkInEl && !checkOutEl) return;
        var min = stay.start || '';
        var max = stay.end || '';
        if (checkInEl) {
            if (min) checkInEl.setAttribute('min', min);
            if (max) checkInEl.setAttribute('max', max);
            if (!checkInEl.value && min) checkInEl.value = min;
            if (min && checkInEl.value && checkInEl.value < min) checkInEl.value = min;
            if (max && checkInEl.value && checkInEl.value > max) checkInEl.value = max;
        }
        if (checkOutEl) {
            var outMin = (checkInEl && checkInEl.value) || min;
            if (outMin) checkOutEl.setAttribute('min', outMin);
            if (max) checkOutEl.setAttribute('max', max);
            if (!checkOutEl.value && max) checkOutEl.value = max;
            if (outMin && checkOutEl.value && checkOutEl.value < outMin) checkOutEl.value = outMin;
            if (max && checkOutEl.value && checkOutEl.value > max) checkOutEl.value = max;
        }
    }

    function tourPax() {
        if (window.StpLiteGuestCaps && typeof window.StpLiteGuestCaps.getCaps === 'function') {
            var caps = window.StpLiteGuestCaps.getCaps() || {};
            var a = parseInt(caps.adults, 10) || 0;
            var c = parseInt(caps.children, 10) || 0;
            var i = parseInt(caps.infants, 10) || 0;
            var ages = Array.isArray(caps.childAges) ? caps.childAges.slice() : [];
            return { adults: a, children: c, infants: i, childAges: ages, pax: Math.max(1, a + c) };
        }
        var adultsEl = parseInt((document.getElementById('adults') || {}).value || '0', 10) || 0;
        var childrenEl = parseInt((document.getElementById('children') || {}).value || '0', 10) || 0;
        var infantsEl = parseInt((document.getElementById('infants') || {}).value || '0', 10) || 0;
        var agesRaw = [];
        try {
            agesRaw = JSON.parse((document.getElementById('child_ages') || {}).value || '[]');
            if (!Array.isArray(agesRaw)) agesRaw = [];
        } catch (e) { agesRaw = []; }
        return { adults: adultsEl, children: childrenEl, infants: infantsEl, childAges: agesRaw, pax: Math.max(1, adultsEl + childrenEl) };
    }

    function hotelAgeLimits(root) {
        var hotelSelect = root && root.querySelector('.hotel-select');
        var opt = hotelSelect && hotelSelect.options[hotelSelect.selectedIndex];
        var fromOpt = function (key, fallback) {
            var n = parseInt((opt && opt.dataset[key]) || '', 10);
            if (!isNaN(n) && n >= 0) return n;
            return fallback;
        };
        // Fallback from cached hotel list
        var hotelId = hotelSelect ? hotelSelect.value : '';
        var hit = (root.__hotelData || []).find(function (h) {
            return String(h.hotel_unique_id) === String(hotelId);
        });
        return {
            infant: fromOpt('infantAgeLimit', hit ? parseInt(hit.infant_age_limit, 10) || 0 : 0),
            child: fromOpt('childAgeLimit', hit ? parseInt(hit.child_age_limit, 10) || 17 : 17),
            extraBedMin: fromOpt('extraBedAgeLimit', hit ? parseInt(hit.extra_bed_age_limit, 10) || 0 : 0)
        };
    }

    /**
     * Reclassify tour child ages against hotel infant / child upper age limits.
     * age ≤ infant limit → infant (+ baby cot)
     * age > child limit → adult
     * else → child
     */
    function classifyHotelPax(root) {
        var tour = tourPax();
        var lim = hotelAgeLimits(root);
        var infantLimit = Math.max(0, lim.infant || 0);
        var childLimit = Math.max(infantLimit, lim.child || 17);
        var adults = Math.max(0, tour.adults || 0);
        var infants = Math.max(0, tour.infants || 0);
        var children = 0;
        var asInfant = [];
        var asChild = [];
        var asAdult = [];
        var ages = tour.childAges || [];

        // If ages missing but children count exists, treat all as children (no reclass)
        if (!ages.length && (tour.children || 0) > 0) {
            children = tour.children || 0;
        } else {
            ages.forEach(function (raw) {
                var age = parseInt(raw, 10);
                if (isNaN(age) || age < 0) age = 0;
                if (age <= infantLimit) {
                    infants += 1;
                    asInfant.push(age);
                } else if (age > childLimit) {
                    adults += 1;
                    asAdult.push(age);
                } else {
                    children += 1;
                    asChild.push(age);
                }
            });
            // Pad if child count > ages length (legacy)
            var accounted = asInfant.length + asChild.length + asAdult.length;
            if ((tour.children || 0) > accounted) {
                children += (tour.children - accounted);
            }
        }

        var notes = [];
        if (asAdult.length) {
            notes.push(asAdult.length + ' tour child' + (asAdult.length > 1 ? 'ren' : '') +
                ' age ' + asAdult.join('/') + 'y → adult (hotel child max ' + childLimit + ')');
        }
        if (asInfant.length) {
            notes.push(asInfant.length + ' tour child' + (asInfant.length > 1 ? 'ren' : '') +
                ' age ' + asInfant.join('/') + 'y → infant (hotel infant max ' + infantLimit + ')');
        }

        return {
            adults: Math.max(1, adults),
            children: Math.max(0, children),
            infants: Math.max(0, infants),
            infantLimit: infantLimit,
            childLimit: childLimit,
            extraBedMin: Math.max(0, lim.extraBedMin || 0),
            asInfant: asInfant,
            asChild: asChild,
            asAdult: asAdult,
            notes: notes,
            pax: Math.max(1, adults + children)
        };
    }

    function currencyLabel(root) {
        return (root && root.getAttribute('data-currency')) || cfg().dmcCurrency || 'SGD';
    }

    function bedOccupancyInfo(root) {
        var bedType = root && root.querySelector('.hotel-bed-type');
        var opt = bedType && bedType.options[bedType.selectedIndex];
        var maxOccupancy = parseInt((opt && opt.dataset.maxOccupancy) || '0', 10) || 0;
        var adultCountRaw = parseInt((opt && opt.dataset.adultCount) || '', 10);
        var childCountRaw = parseInt((opt && opt.dataset.childCount) || '', 10);
        var bedMaxAdults = !isNaN(adultCountRaw) && adultCountRaw > 0 ? adultCountRaw : 0;
        var bedMaxChildren = !isNaN(childCountRaw) && childCountRaw >= 0 ? childCountRaw : -1;
        var extraBedAvailable = !!(opt && (opt.dataset.extraBed === '1' || (parseFloat(opt.dataset.extraBedPrice) || 0) > 0));
        var extraBedPrice = opt ? (parseFloat(opt.dataset.extraBedPrice) || 0) : 0;
        var babyCotAvailable = !!(opt && (opt.dataset.babyCot === '1' || (parseFloat(opt.dataset.babyCotPrice) || 0) > 0));
        var babyCotPrice = opt ? (parseFloat(opt.dataset.babyCotPrice) || 0) : 0;
        return {
            maxOccupancy: maxOccupancy,
            bedMaxAdults: bedMaxAdults,
            bedMaxChildren: bedMaxChildren,
            extraBedAvailable: extraBedAvailable,
            extraBedPrice: extraBedPrice,
            babyCotAvailable: babyCotAvailable,
            babyCotPrice: babyCotPrice,
            maxRoomOccupancy: extraBedAvailable ? maxOccupancy + 1 : maxOccupancy
        };
    }

    function selectedAdultsCount(root) {
        var el = root && root.querySelector('.hotel-selected-adults');
        var n = parseInt((el && el.value) || '0', 10) || 0;
        return n > 0 ? n : 0;
    }

    function selectedChildrenNoBedCount(root) {
        var el = root && root.querySelector('.hotel-selected-children-no-bed');
        return Math.max(0, parseInt((el && el.value) || '0', 10) || 0);
    }

    function selectedChildrenWithBedCount(root) {
        var el = root && root.querySelector('.hotel-selected-children-with-bed');
        return Math.max(0, parseInt((el && el.value) || '0', 10) || 0);
    }

    function selectedChildrenCount(root) {
        var split = selectedChildrenNoBedCount(root) + selectedChildrenWithBedCount(root);
        if (split > 0) return split;
        var el = root && root.querySelector('.hotel-selected-children');
        return Math.max(0, parseInt((el && el.value) || '0', 10) || 0);
    }

    function selectedInfantsCount(root) {
        var el = root && root.querySelector('.hotel-selected-infants');
        return Math.max(0, parseInt((el && el.value) || '0', 10) || 0);
    }

    function selectedPersonsCount(root) {
        var adults = selectedAdultsCount(root);
        var children = selectedChildrenCount(root);
        var total = adults + children;
        if (total > 0) return total;
        var hidden = root && root.querySelector('.hotel-selected-persons');
        var n = parseInt((hidden && hidden.value) || '0', 10) || 0;
        return n > 0 ? n : 1;
    }

    function syncPersonsHidden(root) {
        var adults = selectedAdultsCount(root);
        var children = selectedChildrenCount(root);
        var personsEl = root.querySelector('.hotel-selected-persons');
        var cEl = root.querySelector('.hotel-selected-children');
        if (cEl) cEl.value = String(children);
        if (personsEl) personsEl.value = String(Math.max(1, adults + children));
    }

    function occupancyLimits(root) {
        var classified = classifyHotelPax(root);
        var info = bedOccupancyInfo(root);
        var maxRoom = Math.max(1, info.maxRoomOccupancy || info.maxOccupancy || 1);
        var tourAdults = Math.max(1, classified.adults || 1);
        var tourChildren = Math.max(0, classified.children || 0);
        var tourInfants = Math.max(0, classified.infants || 0);
        var bedAdultCap = info.bedMaxAdults > 0 ? info.bedMaxAdults : maxRoom;
        var bedChildCap = info.bedMaxChildren >= 0 ? info.bedMaxChildren : 0;
        var extraBedSlots = info.extraBedAvailable ? 1 : 0;
        var maxAdults = Math.min(tourAdults, bedAdultCap, maxRoom);
        // No-bed children use bed child slots (e.g. 1C). With-bed uses extra bed only.
        var maxChildrenNoBed = Math.min(tourChildren, Math.max(0, bedChildCap));
        var maxChildrenWithBed = Math.min(tourChildren, extraBedSlots);
        var maxChildren = Math.min(tourChildren, maxChildrenNoBed + maxChildrenWithBed);
        var maxInfants = tourInfants;
        var eligibleWithBedAges = (classified.asChild || []).filter(function (age) {
            return age >= (classified.extraBedMin || 0);
        }).length;
        if (classified.extraBedMin > 0 && (classified.asChild || []).length) {
            maxChildrenWithBed = Math.min(maxChildrenWithBed, eligibleWithBedAges);
        }
        var capacityNotes = [];
        if (tourAdults > maxAdults) {
            capacityNotes.push('Tour needs ' + tourAdults + ' adult' + (tourAdults > 1 ? 's' : '') +
                ' but this bed allows ' + maxAdults + 'A' +
                (bedChildCap >= 0 ? ('+' + bedChildCap + 'C') : ''));
        }
        if (tourChildren > maxChildren) {
            capacityNotes.push('Tour needs ' + tourChildren + ' child' + (tourChildren > 1 ? 'ren' : '') +
                ' but this bed allows ' + maxChildrenNoBed + ' no-bed' +
                (maxChildrenWithBed > 0 ? (' + ' + maxChildrenWithBed + ' with extra bed') : ''));
        }
        return {
            maxRoom: maxRoom,
            maxAdults: Math.max(1, maxAdults),
            maxChildren: Math.max(0, maxChildren),
            maxChildrenNoBed: Math.max(0, maxChildrenNoBed),
            maxChildrenWithBed: Math.max(0, maxChildrenWithBed),
            maxInfants: maxInfants,
            bedMaxAdults: bedAdultCap,
            bedMaxChildren: bedChildCap,
            extraBedSlots: extraBedSlots,
            tourAdults: tourAdults,
            tourChildren: tourChildren,
            tourInfants: tourInfants,
            classified: classified,
            capacityNotes: capacityNotes,
            extraBedAvailable: !!info.extraBedAvailable,
            babyCotAvailable: !!info.babyCotAvailable,
            babyCotPrice: info.babyCotPrice || 0,
            maxOccupancy: info.maxOccupancy || maxRoom
        };
    }

    function defaultChildSplit(lim) {
        lim = lim || {};
        var tourChildren = Math.max(0, lim.tourChildren || 0);
        var maxNo = Math.max(0, lim.maxChildrenNoBed || 0);
        var maxWith = Math.max(0, lim.maxChildrenWithBed || 0);
        var noBed = Math.min(tourChildren, maxNo);
        var withBed = Math.min(Math.max(0, tourChildren - noBed), maxWith);
        return { noBed: noBed, withBed: withBed };
    }

    function updateOccupancySummary(root) {
        var el = root && root.querySelector('.hotel-occ-summary');
        if (!el) return;
        var bedType = root.querySelector('.hotel-bed-type');
        if (!bedType || !bedType.value) {
            el.textContent = 'Select bed type';
            return;
        }
        var adults = selectedAdultsCount(root);
        var noBed = selectedChildrenNoBedCount(root);
        var withBed = selectedChildrenWithBedCount(root);
        var infants = selectedInfantsCount(root);
        var parts = [];
        parts.push(adults + (adults === 1 ? ' adult' : ' adults'));
        if (noBed > 0) parts.push(noBed + (noBed === 1 ? ' child no bed' : ' children no bed'));
        if (withBed > 0) parts.push(withBed + (withBed === 1 ? ' child with bed' : ' children with bed'));
        if (infants > 0) parts.push(infants + (infants === 1 ? ' infant' : ' infants'));
        var cotChk = root.querySelector('.hotel-chk-baby-cot');
        if (infants > 0 && cotChk && cotChk.checked && !cotChk.disabled) parts.push('baby cot');
        el.innerHTML = '<i class="ri-checkbox-circle-fill"></i> ' + esc(parts.join(' · '));
    }

    function updateAgeClassNote(root, classified, lim) {
        var note = root && root.querySelector('.hotel-age-class-note');
        if (!note) return;
        classified = classified || classifyHotelPax(root);
        lim = lim || occupancyLimits(root);
        var parts = [];
        if (classified.notes && classified.notes.length) {
            parts = parts.concat(classified.notes);
        }
        if (lim.capacityNotes && lim.capacityNotes.length) {
            parts = parts.concat(lim.capacityNotes);
        }
        if (!parts.length) {
            note.classList.add('d-none');
            note.innerHTML = '';
            return;
        }
        note.classList.remove('d-none');
        note.innerHTML = '<i class="ri-information-line"></i> ' + esc(parts.join(' · '));
    }

    function writeOccupancyCounts(root, adults, childrenNoBed, childrenWithBed, infants) {
        var aEl = root.querySelector('.hotel-selected-adults');
        var cEl = root.querySelector('.hotel-selected-children');
        var cNoEl = root.querySelector('.hotel-selected-children-no-bed');
        var cWithEl = root.querySelector('.hotel-selected-children-with-bed');
        var iEl = root.querySelector('.hotel-selected-infants');
        var aCount = root.querySelector('.hotel-adult-count');
        var cNoCount = root.querySelector('.hotel-child-nobed-count');
        var cWithCount = root.querySelector('.hotel-child-withbed-count');
        var iCount = root.querySelector('.hotel-infant-count');
        var noBed = Math.max(0, parseInt(childrenNoBed, 10) || 0);
        var withBed = Math.max(0, parseInt(childrenWithBed, 10) || 0);
        var kids = noBed + withBed;
        if (aEl) aEl.value = String(adults);
        if (cNoEl) cNoEl.value = String(noBed);
        if (cWithEl) cWithEl.value = String(withBed);
        if (cEl) cEl.value = String(kids);
        if (iEl) iEl.value = String(infants != null ? infants : selectedInfantsCount(root));
        if (aCount) aCount.textContent = String(adults);
        if (cNoCount) cNoCount.textContent = String(noBed);
        if (cWithCount) cWithCount.textContent = String(withBed);
        if (iCount) iCount.textContent = String(infants != null ? infants : selectedInfantsCount(root));
        syncPersonsHidden(root);
        updateHotelChildPricingVisibility(root);
        updateOccupancySummary(root);
        updateAgeClassNote(root);
    }

    function clampOccupancy(root, adults, childrenNoBed, childrenWithBed, infants) {
        var lim = occupancyLimits(root);
        var a = Math.max(1, parseInt(adults, 10) || 1);
        var cNo = Math.max(0, parseInt(childrenNoBed, 10) || 0);
        var cWith = Math.max(0, parseInt(childrenWithBed, 10) || 0);
        var i = Math.max(0, parseInt(infants != null ? infants : selectedInfantsCount(root), 10) || 0);
        a = Math.min(a, lim.maxAdults);
        cNo = Math.min(cNo, lim.maxChildrenNoBed);
        cWith = Math.min(cWith, lim.maxChildrenWithBed);
        // Total children cannot exceed tour children
        if (cNo + cWith > lim.tourChildren) {
            var overflow = (cNo + cWith) - lim.tourChildren;
            if (cWith >= overflow) cWith -= overflow;
            else {
                overflow -= cWith;
                cWith = 0;
                cNo = Math.max(0, cNo - overflow);
            }
        }
        // Base occupancy: adults + no-bed children; with-bed uses extra bed slot
        var baseOcc = lim.maxOccupancy || lim.maxRoom;
        if (a + cNo > baseOcc) {
            cNo = Math.max(0, baseOcc - a);
            if (a + cNo > baseOcc) a = Math.max(1, baseOcc - cNo);
        }
        if (a + cNo + cWith > lim.maxRoom) {
            cWith = Math.max(0, lim.maxRoom - a - cNo);
        }
        i = Math.min(i, lim.maxInfants);
        return { adults: a, childrenNoBed: cNo, childrenWithBed: cWith, children: cNo + cWith, infants: i, lim: lim };
    }

    function updatePersonSelector(root, preferredTotal, preferredAdults, preferredChildrenNoBed, preferredInfants, preferredChildrenWithBed) {
        var needBed = root && root.querySelector('.hotel-occ-need-bed');
        var pickers = root && root.querySelector('.hotel-occ-pickers');
        var noBedStepper = root && root.querySelector('.hotel-child-nobed-stepper');
        var withBedStepper = root && root.querySelector('.hotel-child-withbed-stepper');
        var infantStepper = root && root.querySelector('.hotel-infant-stepper');
        var adultHint = root && root.querySelector('.hotel-adult-hint');
        var noBedHint = root && root.querySelector('.hotel-child-nobed-hint');
        var withBedHint = root && root.querySelector('.hotel-child-withbed-hint');
        var infantHint = root && root.querySelector('.hotel-infant-hint');
        if (!root) return;

        var bedType = root.querySelector('.hotel-bed-type');
        if (!bedType || !bedType.value) {
            if (needBed) needBed.classList.remove('d-none');
            if (pickers) pickers.classList.add('d-none');
            writeOccupancyCounts(root, 1, 0, 0, 0);
            updateAgeClassNote(root);
            return;
        }
        if (needBed) needBed.classList.add('d-none');
        if (pickers) pickers.classList.remove('d-none');

        var lim = occupancyLimits(root);
        var classified = lim.classified || classifyHotelPax(root);
        var adults;
        var childrenNoBed;
        var childrenWithBed;
        var infants;
        var split;

        // Legacy: preferredChildrenNoBed may be total children when withBed arg omitted
        if (preferredAdults != null || preferredChildrenNoBed != null || preferredInfants != null || preferredChildrenWithBed != null) {
            adults = preferredAdults != null ? preferredAdults : selectedAdultsCount(root);
            if (preferredChildrenWithBed != null ||
                (root.querySelector('.hotel-selected-children-no-bed') && preferredChildrenNoBed != null && preferredChildrenWithBed !== undefined)) {
                childrenNoBed = preferredChildrenNoBed != null ? preferredChildrenNoBed : selectedChildrenNoBedCount(root);
                childrenWithBed = preferredChildrenWithBed != null ? preferredChildrenWithBed : selectedChildrenWithBedCount(root);
            } else if (preferredChildrenNoBed != null && preferredChildrenWithBed == null) {
                // Caller passed total children — auto-split
                split = defaultChildSplit({
                    tourChildren: preferredChildrenNoBed,
                    maxChildrenNoBed: lim.maxChildrenNoBed,
                    maxChildrenWithBed: lim.maxChildrenWithBed
                });
                childrenNoBed = split.noBed;
                childrenWithBed = split.withBed;
            } else {
                childrenNoBed = selectedChildrenNoBedCount(root);
                childrenWithBed = selectedChildrenWithBedCount(root);
            }
            infants = preferredInfants != null ? preferredInfants : selectedInfantsCount(root);
        } else if (preferredTotal != null) {
            var total = parseInt(preferredTotal, 10) || 1;
            var kidsBudget = Math.min(classified.children || 0, Math.max(0, total - 1));
            adults = Math.max(1, total - kidsBudget);
            split = defaultChildSplit({
                tourChildren: kidsBudget,
                maxChildrenNoBed: lim.maxChildrenNoBed,
                maxChildrenWithBed: lim.maxChildrenWithBed
            });
            childrenNoBed = split.noBed;
            childrenWithBed = split.withBed;
            infants = classified.infants || 0;
        } else {
            adults = Math.min(lim.maxAdults, Math.max(1, classified.adults || 1));
            split = defaultChildSplit(lim);
            childrenNoBed = split.noBed;
            childrenWithBed = split.withBed;
            infants = Math.min(lim.maxInfants, classified.infants || 0);
        }

        var clamped = clampOccupancy(root, adults, childrenNoBed, childrenWithBed, infants);
        adults = clamped.adults;
        childrenNoBed = clamped.childrenNoBed;
        childrenWithBed = clamped.childrenWithBed;
        infants = clamped.infants;
        lim = clamped.lim;

        if (noBedStepper) noBedStepper.classList.toggle('d-none', lim.tourChildren < 1 || lim.maxChildrenNoBed < 1);
        if (withBedStepper) withBedStepper.classList.toggle('d-none', lim.tourChildren < 1 || lim.maxChildrenWithBed < 1);
        if (infantStepper) infantStepper.classList.toggle('d-none', lim.tourInfants < 1);

        var roomType = root.querySelector('.hotel-room-type');
        var roomOpt = roomType && roomType.options[roomType.selectedIndex];
        var withPrice = roomOpt ? (parseFloat(roomOpt.dataset.childWithBed) || 0) : 0;
        var withoutPrice = roomOpt ? (parseFloat(roomOpt.dataset.childWithoutBed) || 0) : 0;
        var cur = currencyLabel(root);

        if (adultHint) {
            adultHint.textContent = 'Max ' + lim.maxAdults +
                (lim.bedMaxAdults > 0
                    ? (' · bed ' + lim.bedMaxAdults + 'A' +
                        (lim.bedMaxChildren >= 0 ? ('+' + lim.bedMaxChildren + 'C') : ''))
                    : '') +
                (lim.extraBedAvailable ? ' · +extra bed' : '');
        }
        if (noBedHint) {
            noBedHint.textContent = lim.maxChildrenNoBed > 0
                ? ('Max ' + lim.maxChildrenNoBed + ' · hotel ≤' + (classified.childLimit || '?') + 'y' +
                    (withoutPrice > 0 ? (' · ' + cur + ' ' + withoutPrice.toFixed(0)) : ''))
                : 'No no-bed child slots';
        }
        if (withBedHint) {
            withBedHint.textContent = lim.maxChildrenWithBed > 0
                ? ('Max ' + lim.maxChildrenWithBed + ' · extra bed' +
                    (classified.extraBedMin > 0 ? (' · min ' + classified.extraBedMin + 'y') : '') +
                    (withPrice > 0 ? (' · ' + cur + ' ' + withPrice.toFixed(0)) : '') +
                    (lim.extraBedAvailable && lim.babyCotPrice === undefined && (lim.maxOccupancy)
                        ? ''
                        : ''))
                : 'No extra bed on this bed type';
        }
        if (infantHint) {
            infantHint.textContent = lim.tourInfants
                ? ('Max ' + lim.maxInfants + ' · hotel ≤' + (classified.infantLimit || '?') + 'y')
                : 'No infants';
        }

        writeOccupancyCounts(root, adults, childrenNoBed, childrenWithBed, infants);
        updateAgeClassNote(root, classified, lim);

        var minusA = root.querySelector('.hotel-adult-minus');
        var plusA = root.querySelector('.hotel-adult-plus');
        var minusNo = root.querySelector('.hotel-child-nobed-minus');
        var plusNo = root.querySelector('.hotel-child-nobed-plus');
        var minusWith = root.querySelector('.hotel-child-withbed-minus');
        var plusWith = root.querySelector('.hotel-child-withbed-plus');
        var minusI = root.querySelector('.hotel-infant-minus');
        var plusI = root.querySelector('.hotel-infant-plus');
        if (minusA) minusA.disabled = adults <= 1;
        if (plusA) plusA.disabled = adults >= lim.maxAdults || (adults + childrenNoBed) >= lim.maxOccupancy;
        if (minusNo) minusNo.disabled = childrenNoBed <= 0;
        if (plusNo) {
            plusNo.disabled = lim.maxChildrenNoBed < 1 ||
                childrenNoBed >= lim.maxChildrenNoBed ||
                (childrenNoBed + childrenWithBed) >= lim.tourChildren ||
                (adults + childrenNoBed) >= lim.maxOccupancy;
        }
        if (minusWith) minusWith.disabled = childrenWithBed <= 0;
        if (plusWith) {
            plusWith.disabled = lim.maxChildrenWithBed < 1 ||
                childrenWithBed >= lim.maxChildrenWithBed ||
                (childrenNoBed + childrenWithBed) >= lim.tourChildren ||
                (adults + childrenNoBed + childrenWithBed) >= lim.maxRoom;
        }
        if (minusI) minusI.disabled = infants <= 0;
        if (plusI) plusI.disabled = lim.tourInfants < 1 || infants >= lim.maxInfants;
    }

    function adjustOccupancy(root, role, delta) {
        if (root.__hydrating) return;
        var adults = selectedAdultsCount(root);
        var childrenNoBed = selectedChildrenNoBedCount(root);
        var childrenWithBed = selectedChildrenWithBedCount(root);
        var infants = selectedInfantsCount(root);
        if (role === 'adults') adults += delta;
        else if (role === 'children-no-bed' || role === 'children') childrenNoBed += delta;
        else if (role === 'children-with-bed') childrenWithBed += delta;
        else if (role === 'infants') infants += delta;
        var clamped = clampOccupancy(root, adults, childrenNoBed, childrenWithBed, infants);
        if (clamped.adults === selectedAdultsCount(root) &&
            clamped.childrenNoBed === selectedChildrenNoBedCount(root) &&
            clamped.childrenWithBed === selectedChildrenWithBedCount(root) &&
            clamped.infants === selectedInfantsCount(root)) {
            if (delta > 0) {
                var lim = clamped.lim;
                if (role === 'adults' && adults > lim.maxAdults) {
                    alert('This bed allows max ' + lim.maxAdults + ' adult' +
                        (lim.maxAdults === 1 ? '' : 's') +
                        (lim.bedMaxAdults > 0
                            ? ' (' + lim.bedMaxAdults + 'A' +
                                (lim.bedMaxChildren >= 0 ? ('+' + lim.bedMaxChildren + 'C') : '') + ').'
                            : '.'));
                } else if ((role === 'children-no-bed' || role === 'children') && childrenNoBed > lim.maxChildrenNoBed) {
                    alert('This bed allows max ' + lim.maxChildrenNoBed + ' child' +
                        (lim.maxChildrenNoBed === 1 ? '' : 'ren') + ' without bed.');
                } else if (role === 'children-with-bed' && childrenWithBed > lim.maxChildrenWithBed) {
                    alert(lim.maxChildrenWithBed < 1
                        ? 'This bed type has no extra bed for children with bed.'
                        : ('Extra bed allows max ' + lim.maxChildrenWithBed + ' child with bed.'));
                } else if (role === 'infants' && infants > lim.maxInfants) {
                    alert('Cannot exceed classified infants (' + lim.tourInfants + ').');
                } else if ((adults + childrenNoBed + childrenWithBed) > lim.maxRoom) {
                    alert('Maximum room occupancy is ' + lim.maxRoom + ' guests' +
                        (lim.extraBedAvailable ? ' (including extra bed).' : '.'));
                } else if ((childrenNoBed + childrenWithBed) > lim.tourChildren) {
                    alert('Cannot exceed tour children (' + lim.tourChildren + ').');
                }
            }
            return;
        }
        writeOccupancyCounts(root, clamped.adults, clamped.childrenNoBed, clamped.childrenWithBed, clamped.infants);
        updatePersonSelector(root, null, clamped.adults, clamped.childrenNoBed, clamped.infants, clamped.childrenWithBed);
        invalidatePriceState(root);
    }

    function selectPersons(root, numPersons) {
        var classified = classifyHotelPax(root);
        var lim = occupancyLimits(root);
        var children = Math.min(classified.children || 0, Math.max(0, numPersons - 1));
        var adults = Math.max(1, numPersons - children);
        var split = defaultChildSplit({
            tourChildren: children,
            maxChildrenNoBed: lim.maxChildrenNoBed,
            maxChildrenWithBed: lim.maxChildrenWithBed
        });
        updatePersonSelector(root, null, adults, split.noBed, classified.infants || 0, split.withBed);
        invalidatePriceState(root);
    }

    function isAdHocEnabled(root) {
        var chk = root && root.querySelector('.hotel-chk-adhoc');
        return !!(chk && chk.checked);
    }

    function sanitizeAdHocPriceValue(raw) {
        var s = String(raw == null ? '' : raw).replace(/[eE]/g, '');
        s = s.replace(/[^\d.]/g, '');
        var parts = s.split('.');
        if (parts.length > 2) {
            s = parts[0] + '.' + parts.slice(1).join('');
        }
        return s;
    }

    function getAdHocPriceValue(root) {
        var el = root && root.querySelector('.hotel-adhoc-price');
        if (!el) return null;
        var raw = sanitizeAdHocPriceValue(el.value);
        if (!String(el.value || '').trim()) return null;
        if (/[eE]/.test(String(el.value || ''))) return null;
        if (!raw) return null;
        var n = parseFloat(raw);
        if (!isFinite(n) || n < 0) return null;
        return n;
    }

    function isAdHocPriceReady(root) {
        if (!isAdHocEnabled(root)) return true;
        return getAdHocPriceValue(root) != null;
    }

    function isAdHocRow(row) {
        if (!row) return false;
        return !!(row.is_adhoc || row.priceMode === 'adhoc'
            || (row.price_payload && row.price_payload.is_adhoc)
            || (row.helperPriceResult && row.helperPriceResult.is_adhoc));
    }

    function nightCountFromRow(row) {
        if (!row) return 0;
        var payload = row.price_payload || row.helperPriceResult || null;
        if (payload) {
            var n = parseInt(payload.nights, 10) || 0;
            if (n > 0) return n;
            if (Array.isArray(payload.breakdown) && payload.breakdown.length) return payload.breakdown.length;
        }
        var s = row.stay_start || (Array.isArray(row.bookingDate) ? row.bookingDate[0] : '') || '';
        var e = row.stay_end || (Array.isArray(row.bookingDate) ? row.bookingDate[1] : '') || '';
        if (s && e && typeof moment !== 'undefined') {
            var diff = moment(e, 'YYYY-MM-DD').diff(moment(s, 'YYYY-MM-DD'), 'days');
            return diff > 0 ? diff : 0;
        }
        return 0;
    }

    function positiveAmount(v) {
        if (v == null || v === '') return null;
        var n = Number(v);
        return (isFinite(n) && n > 0) ? n : null;
    }

    function resolveAdHocPriceFromRow(row) {
        if (!row) return null;
        // Treat 0 as missing — stored payloads often keep adhoc_price/room_total at 0
        // while grand_total / bed.price still hold the real Manual Room rate.
        var direct = positiveAmount(row.adhoc_price);
        if (direct != null) return direct;
        var payload = row.price_payload || row.helperPriceResult || null;
        if (typeof payload === 'string') {
            try { payload = JSON.parse(payload); } catch (e) { payload = null; }
        }
        var fromPayload = positiveAmount(payload && payload.adhoc_price);
        if (fromPayload != null) return fromPayload;
        if (!isAdHocRow(row)) return null;
        var nights = nightCountFromRow(row) || 0;
        if (nights < 1) nights = 1;
        var roomTotal = positiveAmount(payload && payload.room_total) || positiveAmount(row.room_total);
        if (roomTotal != null) return roomTotal / nights;
        // Stored bed.price is room sell total for 1 room across the stay
        var b0 = firstBed(row);
        var bedPrice = positiveAmount(b0 && b0.price);
        if (bedPrice != null) return bedPrice / nights;
        var grand = positiveAmount(row.grand_total)
            || positiveAmount(row.totalPrice)
            || positiveAmount(row.price)
            || positiveAmount(payload && payload.grand_total);
        if (grand != null) {
            var meal = Number(row.meal_total != null ? row.meal_total
                : (payload && payload.meal_total != null ? payload.meal_total : 0)) || 0;
            var roomOnly = Math.max(0, grand - meal);
            if (roomOnly > 0) return roomOnly / nights;
        }
        return null;
    }

    function resolveRoomTotalFromRow(row) {
        if (!row) return 0;
        var payload = row.price_payload || row.helperPriceResult || null;
        if (typeof payload === 'string') {
            try { payload = JSON.parse(payload); } catch (e) { payload = null; }
        }
        var fromRow = positiveAmount(row.room_total);
        if (fromRow != null) return fromRow;
        var fromPayload = positiveAmount(payload && payload.room_total);
        if (fromPayload != null) return fromPayload;
        var nights = nightCountFromRow(row) || 1;
        var adhoc = resolveAdHocPriceFromRow(row);
        if (adhoc != null) return Number(adhoc) * nights;
        var b0 = firstBed(row);
        var bedPrice = positiveAmount(b0 && b0.price);
        if (bedPrice != null) return bedPrice;
        var grand = positiveAmount(row.grand_total)
            || positiveAmount(row.totalPrice)
            || positiveAmount(row.price);
        if (grand != null) {
            var meal = Number(row.meal_total != null ? row.meal_total
                : (payload && payload.meal_total != null ? payload.meal_total : 0)) || 0;
            return Math.max(0, grand - meal);
        }
        return 0;
    }

    function applyAdHocFromRow(root, row) {
        if (!root || !row) return;
        var adhocChk = root.querySelector('.hotel-chk-adhoc');
        var on = isAdHocRow(row);
        var price = resolveAdHocPriceFromRow(row);
        if (adhocChk) adhocChk.checked = on;
        syncAdHocUi(root);
        var adhocInput = root.querySelector('.hotel-adhoc-price');
        if (on && adhocInput && price != null && Number(price) > 0) {
            adhocInput.value = sanitizeAdHocPriceValue(String(price));
        }
        syncGetPriceBtn(root);
    }

    function syncAdHocUi(root) {
        if (!root) return;
        var row = root.querySelector('.stp-lite-hotel-adhoc-row');
        var wrap = root.querySelector('.hotel-adhoc-price-wrap');
        var input = root.querySelector('.hotel-adhoc-price');
        var on = isAdHocEnabled(root);
        if (row) row.classList.toggle('is-on', on);
        if (wrap) wrap.classList.toggle('d-none', !on);
        if (input) {
            input.disabled = !on;
            if (!on) input.value = '';
        }
        syncGetPriceBtn(root);
    }

    function syncGetPriceBtn(root, opts) {
        opts = opts || {};
        var btn = root && root.querySelector('.hotel-get-price-btn');
        if (!btn) return;
        if (opts.loading) {
            btn.disabled = true;
            return;
        }
        btn.disabled = !isAdHocPriceReady(root);
    }

    function moneyTxt(cur, amount) {
        return esc(cur) + ' ' + Number(amount || 0).toFixed(2);
    }

    function roomsSuffix(rooms) {
        rooms = parseInt(rooms, 10) || 1;
        return ' × ' + rooms + ' room' + (rooms > 1 ? 's' : '');
    }

    function nightsSuffix(nights) {
        nights = parseInt(nights, 10) || 1;
        return ' × ' + nights + ' night' + (nights > 1 ? 's' : '');
    }

    /**
     * Get Price result stays intact — only room_price / room_total are replaced
     * with the AdHoc Manual Room rate. Breakfast / lunch / dinner from the API
     * are never modified (complementary breakfast already has no meal charge).
     * Original inventory room rates are kept for strikethrough display.
     */
    function applyAdHocToPriceData(data, adhocPerNight) {
        if (!data || adhocPerNight == null || !isFinite(adhocPerNight)) return data;
        var nights = parseInt(data.nights, 10)
            || (Array.isArray(data.breakdown) ? data.breakdown.length : 0)
            || 1;
        var roomTotal = Number(adhocPerNight) * nights;
        var mealTotal = Number(data.meal_total || 0);
        var cwb = data.child_with_bed || null;
        var cnb = data.child_without_bed || null;
        var cwbTotal = cwb ? Number(cwb.total || 0) : 0;
        var cnbTotal = cnb ? Number(cnb.total || 0) : 0;
        var out = Object.assign({}, data, {
            is_adhoc: true,
            adhoc_price: Number(adhocPerNight),
            original_room_total: Number(data.room_total || 0),
            original_fair_charge_total: Number(data.fair_charge_total || 0),
            room_total: roomTotal,
            // Fair surcharge is part of inventory room pricing; AdHoc is a flat room rate.
            fair_charge_total: 0,
            fair_nights: 0,
            // Keep meal_total / breakfast_total / lunch_total / dinner_total from Get Price.
            grand_total: roomTotal + mealTotal + cwbTotal + cnbTotal
        });
        if (Array.isArray(data.breakdown) && data.breakdown.length) {
            out.breakdown = data.breakdown.map(function (n) {
                var mealPrice = Number(n.meal_price || 0);
                var origBase = Number(n.room_base != null ? n.room_base : (n.room_price || 0));
                var origPrice = Number(n.room_price != null ? n.room_price : origBase);
                return Object.assign({}, n, {
                    original_room_price: origPrice,
                    original_room_base: origBase,
                    original_surcharge: Number(n.surcharge || 0),
                    room_price: Number(adhocPerNight),
                    room_base: Number(adhocPerNight),
                    surcharge: 0,
                    variant_price: 0,
                    // Meals untouched from Get Price; night total = AdHoc room + API meals.
                    night_total: Number(adhocPerNight) + mealPrice,
                    source: 'AdHoc'
                });
            });
        }
        return out;
    }

    function formatNightCutHtml(n, cur, rooms, mealPlanLabel, nightIndex) {
        rooms = parseInt(rooms, 10) || 1;
        var isAdHoc = String(n.source || '') === 'AdHoc' || n.is_adhoc;
        var roomBase = Number(n.room_base != null ? n.room_base : (n.room_price || 0));
        var fair = Number(n.surcharge || 0);
        var variant = Number(n.variant_price || 0);
        var extraBed = Number(n.extra_bed_total || 0);
        var bf = Number(n.breakfast_meal || 0);
        var ln = Number(n.lunch_meal || 0);
        var dn = Number(n.dinner_meal || 0);
        var meal = Number(n.meal_price || 0);
        var nightTotal = Number(n.night_total || 0) * rooms;
        var dateLabel = String(n.date || '').trim();
        var dayLabel = String(n.day || '').trim();
        var evtType = String(n.event_type || n.eventType || '').trim();
        var evtBadge = evtType
            ? (' <span class="stp-lite-night-event">(' + esc(evtType) + ')</span>')
            : '';
        var roomUnitLabel = roomsSuffix(rooms);

        var formulaParts = [];
        if (isAdHoc) {
            var adhocRate = Number(n.room_price != null ? n.room_price : roomBase);
            var origRate = Number(n.original_room_base != null
                ? n.original_room_base
                : (n.original_room_price != null ? n.original_room_price : 0));
            var roomFormula = 'AdHoc room rate ';
            if (origRate > 0 && Math.abs(origRate - adhocRate) > 0.0001) {
                roomFormula += '<span class="stp-lite-strike">' + moneyTxt(cur, origRate) + '/night</span> ';
            }
            roomFormula += moneyTxt(cur, adhocRate) + '/night' + roomUnitLabel;
            formulaParts.push(roomFormula);
            if (bf > 0) formulaParts.push('Breakfast ' + moneyTxt(cur, bf) + '/night' + roomUnitLabel);
            if (ln > 0) formulaParts.push('Lunch ' + moneyTxt(cur, ln) + '/night' + roomUnitLabel);
            if (dn > 0) formulaParts.push('Dinner ' + moneyTxt(cur, dn) + '/night' + roomUnitLabel);
            if (meal > 0 && bf <= 0 && ln <= 0 && dn <= 0) {
                formulaParts.push('Meals ' + moneyTxt(cur, meal) + '/night' + roomUnitLabel);
            }
        } else {
            if (roomBase > 0) formulaParts.push('Room ' + moneyTxt(cur, roomBase) + '/night' + roomUnitLabel);
            if (fair > 0) formulaParts.push('Fair ' + moneyTxt(cur, fair) + '/night' + roomUnitLabel);
            if (variant > 0) formulaParts.push('Variant ' + moneyTxt(cur, variant) + '/night' + roomUnitLabel);
            if (extraBed > 0) formulaParts.push('Extra bed ' + moneyTxt(cur, extraBed) + '/night' + roomUnitLabel);
            if (bf > 0) formulaParts.push('Breakfast ' + moneyTxt(cur, bf) + '/night' + roomUnitLabel);
            if (ln > 0) formulaParts.push('Lunch ' + moneyTxt(cur, ln) + '/night' + roomUnitLabel);
            if (dn > 0) formulaParts.push('Dinner ' + moneyTxt(cur, dn) + '/night' + roomUnitLabel);
            if (meal > 0 && bf <= 0 && ln <= 0 && dn <= 0) {
                formulaParts.push('Meals ' + moneyTxt(cur, meal) + '/night' + roomUnitLabel);
            }
        }

        var title = dateLabel
            ? (dateLabel + (dayLabel ? ' (' + dayLabel + ')' : ''))
            : (nightIndex != null ? ('Night ' + nightIndex) : 'Night');
        var cutLine = formulaParts.length
            ? formulaParts.join(' + ')
            : esc(String(mealPlanLabel || '').trim() || 'Night total');

        return (
            '<div class="stp-lite-night-row is-cut">' +
            '  <div class="stp-lite-night-row__main">' +
            '    <div class="stp-lite-night-row__left">' +
            '      <i class="ri-calendar-line stp-lite-night-cal" aria-hidden="true"></i>' +
            '      <div class="stp-lite-night-row__meta">' +
            '        <div class="stp-lite-night-row__date">' + esc(title) + evtBadge + '</div>' +
            '        <div class="stp-lite-night-row__bits">' + cutLine + '</div>' +
            '      </div>' +
            '    </div>' +
            '    <div class="stp-lite-night-row__amt">' + moneyTxt(cur, nightTotal) + '</div>' +
            '  </div>' +
            '</div>'
        );
    }

    function summaryRowHtml(labelHtml, amountHtml, extraClass) {
        return (
            '<div class="stp-lite-summary-row' + (extraClass ? ' ' + extraClass : '') + '">' +
            '<span class="stp-lite-summary-row__left">' + labelHtml + '</span>' +
            '<strong class="stp-lite-summary-row__amt">' + amountHtml + '</strong></div>'
        );
    }

    function resolveBabyCotCharge(root, nights, rooms) {
        var cotChk = root && root.querySelector('.hotel-chk-baby-cot');
        var cotEnabled = !!(cotChk && cotChk.checked && !cotChk.disabled);
        if (!cotEnabled) {
            return { enabled: false, unit: 0, infants: 0, nights: nights, rooms: rooms, perNight: 0, total: 0 };
        }
        var occ = bedOccupancyInfo(root);
        var unit = Number(occ.babyCotPrice || 0);
        var infants = Math.max(1, selectedInfantsCount(root) || 1);
        nights = Math.max(1, parseInt(nights, 10) || 1);
        rooms = Math.max(1, parseInt(rooms, 10) || 1);
        var perNight = unit > 0 ? (unit * infants * rooms) : 0;
        return {
            enabled: true,
            unit: unit,
            infants: infants,
            nights: nights,
            rooms: rooms,
            perNight: perNight,
            total: perNight * nights
        };
    }

    function buildBreakdownHtml(data, numberOfRooms, cur, opts) {
        if (!data) return { html: '', grand: 0 };
        opts = opts || {};
        var rooms = parseInt(numberOfRooms, 10) || 1;
        var roomTotal = Number(data.room_total || 0) * rooms;
        var mealTotal = Number(data.meal_total || 0) * rooms;
        var grand = Number(data.grand_total || 0) * rooms;
        var fairCharge = Number(data.fair_charge_total || 0) * rooms;
        var roomBase = Math.max(0, roomTotal - fairCharge);
        var mealPlanLabel = String(opts.mealPlan || data.meal_plan || '').trim();
        var isAdHoc = !!(data.is_adhoc || opts.isAdHoc);
        var nights = parseInt(data.nights, 10) || (Array.isArray(data.breakdown) ? data.breakdown.length : 0) || 1;
        var cot = opts.babyCot || { enabled: false, total: 0, perNight: 0 };
        if (cot.enabled && cot.total > 0) grand += cot.total;
        var html = '';
        var perNightRoom = nights > 0 ? (roomBase / nights / rooms) : roomBase;
        // Prefer first-night inventory rate when AdHoc so strike matches Get Price
        var origPerNight = 0;
        if (isAdHoc && Array.isArray(data.breakdown) && data.breakdown.length) {
            var n0 = data.breakdown[0] || {};
            origPerNight = Number(n0.original_room_base != null
                ? n0.original_room_base
                : (n0.original_room_price != null ? n0.original_room_price : 0));
        }
        if (!origPerNight && isAdHoc && Number(data.original_room_total || 0) > 0) {
            var origFair = Number(data.original_fair_charge_total || 0);
            origPerNight = (Number(data.original_room_total) - origFair) / nights;
        }

        if (Array.isArray(data.breakdown) && data.breakdown.length) {
            html += '<div class="stp-lite-breakup-section-label">Room Rate/Night</div>';
            html += '<div class="stp-lite-night-list">';
            data.breakdown.forEach(function (n, idx) {
                html += formatNightCutHtml(n, cur, rooms, mealPlanLabel, idx + 1);
            });
            html += '</div>';
        }

        html += '<div class="stp-lite-breakup-section-label">Summary</div>';
        html += '<div class="stp-lite-summary-list">';
        // AdHoc replaces room only — never merge breakfast/lunch/dinner into this line.
        var roomFormula = '<strong>' + (isAdHoc ? 'AdHoc Room rate' : 'Room Cost') + '</strong> ';
        if (isAdHoc && origPerNight > 0 && Math.abs(origPerNight - perNightRoom) > 0.0001) {
            roomFormula += '<span class="stp-lite-strike">' + moneyTxt(cur, origPerNight) + '/night</span> ';
        }
        roomFormula += moneyTxt(cur, perNightRoom) + '/night'
            + nightsSuffix(nights)
            + roomsSuffix(rooms);
        html += summaryRowHtml(roomFormula, moneyTxt(cur, roomBase), isAdHoc ? 'is-adhoc' : '');
        if (fairCharge > 0 && !isAdHoc) {
            var fairPerNight = nights > 0 ? (fairCharge / nights / rooms) : fairCharge;
            html += summaryRowHtml(
                '<strong>Fair Charge</strong> ' + moneyTxt(cur, fairPerNight) + '/night'
                    + nightsSuffix(nights) + roomsSuffix(rooms),
                moneyTxt(cur, fairCharge),
                'is-fair'
            );
        }
        if (data.breakfast_complementary && data.meals && data.meals.breakfast) {
            html += summaryRowHtml('<strong>Breakfast (Meal)</strong>', 'Included (complementary)', 'is-ok');
        } else if (Number(data.breakfast_total || 0) > 0) {
            var bfAmt = Number(data.breakfast_total) * rooms;
            var bfUnit = nights > 0 ? (bfAmt / nights / rooms) : bfAmt;
            html += summaryRowHtml(
                '<strong>Breakfast (Meal)</strong> ' + moneyTxt(cur, bfUnit) + '/night'
                    + nightsSuffix(nights) + roomsSuffix(rooms),
                moneyTxt(cur, bfAmt)
            );
        }
        [['lunch_total', 'Lunch'], ['dinner_total', 'Dinner']].forEach(function (pair) {
            var amt = Number(data[pair[0]] || 0) * rooms;
            if (amt <= 0) return;
            var unit = nights > 0 ? (amt / nights / rooms) : amt;
            html += summaryRowHtml(
                '<strong>' + pair[1] + '</strong> ' + moneyTxt(cur, unit) + '/night'
                    + nightsSuffix(nights) + roomsSuffix(rooms),
                moneyTxt(cur, amt)
            );
        });
        if (mealTotal > 0
            && !Number(data.breakfast_total)
            && !Number(data.lunch_total)
            && !Number(data.dinner_total)
            && !(data.breakfast_complementary && data.meals && data.meals.breakfast)) {
            var mealUnit = nights > 0 ? (mealTotal / nights / rooms) : mealTotal;
            html += summaryRowHtml(
                '<strong>Meals</strong> ' + moneyTxt(cur, mealUnit) + '/night'
                    + nightsSuffix(nights) + roomsSuffix(rooms),
                moneyTxt(cur, mealTotal)
            );
        }
        if (data.extra_bed && Number(data.extra_bed) > 0) {
            html += summaryRowHtml(
                '<strong>Extra bed(s)</strong> <small class="text-muted">(incl. in room)</small> '
                    + Number(data.extra_bed) + ' × ' + moneyTxt(cur, Number(data.extra_bed_price || 0)),
                moneyTxt(cur, Number(data.extra_bed) * Number(data.extra_bed_price || 0) * rooms)
            );
        } else {
            // Aggregate extra bed from nightly breakdown when helper top-level fields are missing
            var xbNights = 0;
            var xbUnit = 0;
            if (Array.isArray(data.breakdown)) {
                data.breakdown.forEach(function (n) {
                    var xb = Number(n.extra_bed_total || 0);
                    if (xb > 0) {
                        xbNights += 1;
                        if (!xbUnit) xbUnit = xb;
                    }
                });
            }
            if (xbNights > 0) {
                html += summaryRowHtml(
                    '<strong>Extra bed(s)</strong> <small class="text-muted">(incl. in room)</small> '
                        + moneyTxt(cur, xbUnit) + '/night × ' + xbNights + ' night'
                        + (xbNights > 1 ? 's' : '') + roomsSuffix(rooms),
                    moneyTxt(cur, xbUnit * xbNights * rooms)
                );
            }
        }
        var cwb = data.child_with_bed || null;
        var cnb = data.child_without_bed || null;
        var cwbTotal = cwb ? Number(cwb.total || 0) * rooms : 0;
        var cnbTotal = cnb ? Number(cnb.total || 0) * rooms : 0;
        if (cwbTotal > 0) {
            var cwbUnit = nights > 0 ? (cwbTotal / nights / rooms) : cwbTotal;
            var cwbCount = cwb && cwb.count != null ? Number(cwb.count) : 0;
            html += summaryRowHtml(
                '<strong>Child with Bed</strong> ' + moneyTxt(cur, cwbUnit) + '/night'
                    + nightsSuffix(nights) + roomsSuffix(rooms)
                    + (cwbCount > 0 ? ' × ' + cwbCount + ' child' + (cwbCount > 1 ? 'ren' : '') : ''),
                moneyTxt(cur, cwbTotal)
            );
        }
        if (cnbTotal > 0) {
            var cnbUnit = nights > 0 ? (cnbTotal / nights / rooms) : cnbTotal;
            var cnbCount = cnb && cnb.count != null ? Number(cnb.count) : 0;
            html += summaryRowHtml(
                '<strong>Child without Bed</strong> ' + moneyTxt(cur, cnbUnit) + '/night'
                    + nightsSuffix(nights) + roomsSuffix(rooms)
                    + (cnbCount > 0 ? ' × ' + cnbCount + ' child' + (cnbCount > 1 ? 'ren' : '') : ''),
                moneyTxt(cur, cnbTotal)
            );
        }
        if (cot.enabled && cot.total > 0) {
            var cotUnit = Number(cot.unit || 0);
            var cotInfants = Math.max(1, parseInt(cot.infants, 10) || 1);
            html += summaryRowHtml(
                '<strong>Baby cot</strong> ' + moneyTxt(cur, cotUnit) + '/night'
                    + nightsSuffix(nights)
                    + (cotInfants > 1 ? ' × ' + cotInfants + ' infants' : '')
                    + roomsSuffix(rooms),
                moneyTxt(cur, Number(cot.total))
            );
        } else if (cot.enabled && Number(cot.unit || 0) <= 0) {
            html += summaryRowHtml('<strong>Baby cot</strong>', 'Incl.', 'is-ok');
        }
        // Tour/hotel infants always shown at 0 — never added to Total
        var infantN = Math.max(0, parseInt(opts.infants, 10) || 0);
        if (infantN <= 0) {
            try {
                var tourCaps = (window.StpLiteGuestCaps && window.StpLiteGuestCaps.getCaps)
                    ? window.StpLiteGuestCaps.getCaps()
                    : null;
                infantN = Math.max(0, parseInt((tourCaps && tourCaps.infants) || (document.getElementById('infants') || {}).value || 0, 10) || 0);
            } catch (eInf) { infantN = 0; }
        }
        if (infantN > 0) {
            html += summaryRowHtml(
                '<strong>Infant</strong> × ' + infantN,
                moneyTxt(cur, 0)
            );
        }
        html += '</div>';
        return { html: html, grand: grand, babyCot: cot };
    }

    function setAddEnabled(root, on) {
        var addBtn = root.querySelector('.hotel-add-btn');
        if (addBtn) addBtn.disabled = !on;
        root.__priceReady = !!on;
    }

    function setAddButtonMode(root, editing) {
        var addBtn = root.querySelector('.hotel-add-btn');
        if (!addBtn) return;
        if (editing) {
            addBtn.innerHTML = '<i class="ri-save-line me-1"></i>Update Hotel';
            addBtn.classList.add('btn-warning');
            addBtn.classList.remove('btn-primary');
        } else {
            addBtn.innerHTML = '<i class="ri-add-line me-1"></i>Add Hotel';
            addBtn.classList.add('btn-primary');
            addBtn.classList.remove('btn-warning');
        }
    }

    function renderBreakdown(root, data, numberOfRooms) {
        var panel = root.querySelector('[data-hotel-breakup-panel]');
        var body = root.querySelector('[data-hotel-breakup-body]');
        var grid = root.querySelector('.hotel-breakup-grid');
        var grandEl = root.querySelector('.hotel-breakup-grand');
        if (!panel || !grid || !data) return;

        var cur = currencyLabel(root);
        var mealPlanEl = root.querySelector('.hotel-meal-plan');
        var mealPlan = (mealPlanEl && mealPlanEl.value) || data.meal_plan || '';
        var nights = parseInt(data.nights, 10) || (Array.isArray(data.breakdown) ? data.breakdown.length : 0) || 1;
        var rooms = parseInt(numberOfRooms, 10) || 1;
        var babyCot = resolveBabyCotCharge(root, nights, rooms);
        var built = buildBreakdownHtml(data, rooms, cur, {
            mealPlan: mealPlan,
            babyCot: babyCot,
            isAdHoc: !!(data.is_adhoc),
            infants: selectedInfantsCount(root)
        });
        grid.innerHTML = built.html || '<div class="text-muted" style="font-size:0.72rem;">No breakdown returned.</div>';
        if (grandEl) grandEl.textContent = cur + ' ' + Number(built.grand || 0).toFixed(2);

        panel.classList.remove('d-none');
        if (body) body.classList.remove('d-none');
        root.__lastHotelPrice = Object.assign({}, data, {
            meal_plan: mealPlan,
            baby_cot: babyCot.enabled ? 1 : 0,
            baby_cot_price: babyCot.unit || 0,
            baby_cot_cost: babyCot.total || 0,
            grand_total_with_cot: built.grand,
            is_adhoc: !!data.is_adhoc,
            adhoc_price: data.adhoc_price != null ? Number(data.adhoc_price) : null
        });
        root.__lastHotelRooms = rooms;
        setAddEnabled(root, true);
    }

    function clearBreakdown(root, opts) {
        opts = opts || {};
        var panel = root.querySelector('[data-hotel-breakup-panel]');
        var body = root.querySelector('[data-hotel-breakup-body]');
        var grid = root.querySelector('.hotel-breakup-grid');
        if (panel) panel.classList.add('d-none');
        if (body) body.classList.add('d-none');
        if (grid) grid.innerHTML = '';
        root.__lastHotelPrice = null;
        if (opts.disableAdd !== false) setAddEnabled(root, false);
    }

    function invalidatePriceState(root) {
        if (!root || root.__hydrating) return;
        clearBreakdown(root, { disableAdd: true });
    }

    function readHotelChunk(root) {
        var input = root.querySelector('.hotel_data_chunk');
        if (!input) return [];
        try {
            var parsed = JSON.parse(input.value || '[]');
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function writeHotelChunk(root, rows) {
        var input = root.querySelector('.hotel_data_chunk');
        if (input) input.value = JSON.stringify(rows || []);
        syncHotelDataHidden();
        if (window.StpLiteTransportShared && typeof window.StpLiteTransportShared.updateServiceHeaderTotal === 'function') {
            window.StpLiteTransportShared.updateServiceHeaderTotal(root, 'hotel');
        }
    }

    /** Merge all stay chunks into #hotel_data for FOC / store parity. */
    function syncHotelDataHidden() {
        var all = [];
        document.querySelectorAll('.hotel_data_chunk').forEach(function (el) {
            try {
                var parsed = JSON.parse(el.value || '[]');
                if (Array.isArray(parsed)) all = all.concat(parsed);
            } catch (e) { /* ignore */ }
        });
        var hidden = document.getElementById('hotel_data');
        if (!hidden) {
            var form = document.getElementById('singleTourPackageForm');
            if (form) {
                hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.id = 'hotel_data';
                hidden.name = 'hotel_data';
                form.appendChild(hidden);
            }
        }
        if (hidden) hidden.value = JSON.stringify(all);
        document.dispatchEvent(new CustomEvent('stp:hotel-data-changed', { detail: { hotels: all } }));
    }

    function tourHasChildOrInfant() {
        var caps = window.StpLiteGuestCaps && window.StpLiteGuestCaps.getCaps
            ? window.StpLiteGuestCaps.getCaps()
            : { children: 0, infants: 0 };
        return (parseInt(caps.children, 10) || 0) + (parseInt(caps.infants, 10) || 0) > 0;
    }

    function updateHotelChildPricingVisibility(root) {
        if (!root) return;
        var section = root.querySelector('.hotel-child-pricing-section');
        var cotWrap = root.querySelector('.hotel-baby-cot-wrap');
        var cotChk = root.querySelector('.hotel-chk-baby-cot');
        var cotLabel = root.querySelector('.hotel-baby-cot-label');
        var bedInfo = bedOccupancyInfo(root);
        var roomInfants = selectedInfantsCount(root);
        var showCot = roomInfants > 0 && bedInfo.babyCotAvailable;
        var cur = currencyLabel(root);

        if (section) section.classList.toggle('d-none', !showCot);
        if (cotWrap) {
            cotWrap.classList.toggle('d-none', !showCot);
            if (cotLabel) {
                cotLabel.textContent = bedInfo.babyCotPrice > 0
                    ? (cur + ' ' + bedInfo.babyCotPrice.toFixed(2))
                    : 'Included / available';
            }
        }
        if (!showCot) {
            if (cotChk) { cotChk.checked = false; cotChk.disabled = true; }
        } else if (cotChk) {
            cotChk.disabled = false;
            if (!cotChk.checked) cotChk.checked = true;
        }
        updateOccupancySummary(root);
    }

    function ensurePriceModal() {
        if (document.getElementById('stpLiteHotelPriceModal')) return;
        var wrap = document.createElement('div');
        wrap.innerHTML =
            '<div class="modal fade" id="stpLiteHotelPriceModal" tabindex="-1" aria-hidden="true">' +
            '  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-md">' +
            '    <div class="modal-content stp-lite-price-modal">' +
            '      <div class="modal-header py-2">' +
            '        <h5 class="modal-title mb-0" id="stpLiteHotelPriceModalLabel" style="font-size:0.92rem;">Hotel Pricing Details</h5>' +
            '        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' +
            '      </div>' +
            '      <div class="modal-body p-2" id="stpLiteHotelPriceModalBody"></div>' +
            '      <div class="modal-footer py-2">' +
            '        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>' +
            '      </div>' +
            '    </div>' +
            '  </div>' +
            '</div>';
        document.body.appendChild(wrap.firstChild);
    }

    function buildFallbackPriceDataFromRow(row) {
        var nights = nightCountFromRow(row) || 1;
        var adhoc = resolveAdHocPriceFromRow(row);
        var roomTotal = resolveRoomTotalFromRow(row);
        if (!roomTotal && adhoc != null) roomTotal = Number(adhoc) * nights;
        var mealTotal = Number(row.meal_total != null ? row.meal_total : 0) || 0;
        var grand = Number(row.grand_total != null ? row.grand_total
            : (row.totalPrice != null ? row.totalPrice : row.price)) || 0;
        // If meals missing but grand > room, treat remainder as meals
        if (!mealTotal && grand > roomTotal && roomTotal > 0) {
            mealTotal = Math.max(0, grand - roomTotal);
        }
        if (!grand) grand = roomTotal + mealTotal;
        var perNight = adhoc != null ? Number(adhoc) : (nights > 0 ? roomTotal / nights : roomTotal);
        var mealPerNight = nights > 0 ? (mealTotal / nights) : 0;
        var breakdown = [];
        for (var i = 0; i < nights; i++) {
            breakdown.push({
                room_price: perNight,
                room_base: perNight,
                meal_price: mealPerNight,
                breakfast_meal: mealPerNight,
                night_total: perNight + mealPerNight,
                source: isAdHocRow(row) ? 'AdHoc' : 'Stored'
            });
        }
        return {
            success: true,
            is_adhoc: isAdHocRow(row),
            adhoc_price: adhoc != null ? adhoc : perNight,
            meal_plan: row.meal_plan || '',
            nights: nights,
            room_total: roomTotal,
            meal_total: mealTotal,
            breakfast_total: Number(row.breakfast_total || 0) || mealTotal,
            lunch_total: Number(row.lunch_total || 0) || 0,
            dinner_total: Number(row.dinner_total || 0) || 0,
            fair_charge_total: 0,
            grand_total: grand,
            breakdown: breakdown,
            child_with_bed: row.child_with_bed || null,
            child_without_bed: row.child_without_bed || null
        };
    }

    function openHotelPricePopup(row) {
        ensurePriceModal();
        var modalEl = document.getElementById('stpLiteHotelPriceModal');
        var bodyEl = document.getElementById('stpLiteHotelPriceModalBody');
        var titleEl = document.getElementById('stpLiteHotelPriceModalLabel');
        if (!modalEl || !bodyEl || !row) return;

        try {
            var cur = row.currency || cfg().dmcCurrency || 'SGD';
            var rooms = parseInt(row.number_of_rooms, 10)
                || parseInt((Array.isArray(row.rooms) ? (row.rooms[0] || {}).number_of_rooms : row.rooms), 10)
                || 1;
            var name = String(row.hotel_name || 'Hotel').split('(')[0].trim();
            if (titleEl) titleEl.textContent = name + ' — Price breakdown';

            var helper = row.price_payload || row.helperPriceResult || null;
            if (typeof helper === 'string') {
                try { helper = JSON.parse(helper); } catch (e) { helper = null; }
            }
            function helperRoomLooksEmpty(h) {
                if (!h || typeof h !== 'object') return true;
                if (Number(h.room_total || 0) > 0) return false;
                if (Array.isArray(h.breakdown)) {
                    for (var i = 0; i < h.breakdown.length; i++) {
                        var n = h.breakdown[i] || {};
                        if (Number(n.room_price != null ? n.room_price : (n.room_base || 0)) > 0) {
                            return false;
                        }
                    }
                }
                return true;
            }
            if (!helper || typeof helper !== 'object') {
                helper = buildFallbackPriceDataFromRow(row);
            } else if (isAdHocRow(row)) {
                var adhocAmt = resolveAdHocPriceFromRow(row);
                if (adhocAmt != null && (!helper.is_adhoc || helperRoomLooksEmpty(helper))) {
                    helper = applyAdHocToPriceData(helper, adhocAmt);
                } else if (helperRoomLooksEmpty(helper)) {
                    helper = buildFallbackPriceDataFromRow(row);
                }
            } else if (helperRoomLooksEmpty(helper) && Number(row.grand_total || row.totalPrice || 0) > 0) {
                helper = buildFallbackPriceDataFromRow(row);
            }

            var built = buildBreakdownHtml(helper, rooms, cur, {
                mealPlan: row.meal_plan || helper.meal_plan || '',
                isAdHoc: !!(helper.is_adhoc || isAdHocRow(row)),
                infants: savedInfantsFromRow(row)
            });
            bodyEl.innerHTML =
                '<div class="stp-lite-hotel-breakup-card is-modal">' +
                '  <div class="stp-lite-hotel-breakup-body p-2">' +
                (built.html || '<div class="text-muted text-center py-2" style="font-size:0.75rem;">No night breakdown available</div>') +
                '    <div class="stp-lite-hotel-breakup-total">' +
                '      <span><strong>Total:</strong></span><strong>' + cur + ' ' + Number(built.grand || 0).toFixed(2) + '</strong>' +
                '    </div>' +
                '  </div>' +
                '</div>';

            if (window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
                return;
            }
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
            modalEl.setAttribute('aria-hidden', 'false');
        } catch (err) {
            try {
                bodyEl.innerHTML = '<div class="text-danger text-center py-3" style="font-size:0.82rem;">Unable to open price breakdown.</div>';
                if (window.bootstrap && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                } else {
                    modalEl.classList.add('show');
                    modalEl.style.display = 'block';
                }
            } catch (e2) { /* ignore */ }
        }
    }

    function renderAddedHotels(root) {
        var host = root.querySelector('[data-hotel-added-list]');
        if (!host) return;
        var rows = readHotelChunk(root);
        var cur = currencyLabel(root);
        if (!rows.length) {
            host.innerHTML = '';
            return;
        }

        var html =
            '<div class="table-responsive stp-lite-hotel-added-wrap">' +
            '<table class="table table-sm align-middle mb-0 stp-lite-hotel-added-table">' +
            '<thead>' +
            '<tr>' +
            '<th>Hotel</th>' +
            '<th>Room / Bed</th>' +
            '<th>Meal</th>' +
            '<th>Stay</th>' +
            '<th class="text-end">Total</th>' +
            '<th>Supplement</th>' +
            '<th class="text-end">Actions</th>' +
            '</tr>' +
            '</thead><tbody>';

        rows.forEach(function (row, idx) {
            var editing = root.__editingIdx === idx;
            var hd = row.hotelDetails || {};
            var roomsArr = Array.isArray(row.rooms) ? row.rooms : [];
            var r0 = roomsArr[0] || {};
            var beds = Array.isArray(r0.beds) ? r0.beds : [];
            var b0 = beds[0] || {};
            var hotelName = row.hotel_name || hd.hotel_name || hd.name || 'Hotel';
            var roomType = row.room_type || r0.room_type || '—';
            var bedLabel = (row.bed_label || b0.bed_type || b0.bed_label || '—').toString().split(' - ')[0];
            var mealPlan = row.meal_plan || b0.meal_plan || r0.meal_plan
                || (Array.isArray(b0.mealTypes) && b0.mealTypes[0]) || '—';
            var stayLabel = row.stay_label || '';
            if (!stayLabel) {
                var s = row.stay_start || (Array.isArray(row.bookingDate) ? row.bookingDate[0] : '') || '';
                var e = row.stay_end || (Array.isArray(row.bookingDate) ? row.bookingDate[1] : '') || '';
                if (s && e && typeof moment !== 'undefined') {
                    stayLabel = moment(s, 'YYYY-MM-DD').format('MMM D') + ' → ' + moment(e, 'YYYY-MM-DD').format('MMM D, YYYY');
                } else if (s || e) {
                    stayLabel = (s || '') + (e ? (' → ' + e) : '');
                } else {
                    stayLabel = '—';
                }
            }
            var roomsCount = row.number_of_rooms || r0.number_of_rooms || 1;
            var personsN = row.selected_persons || r0.selected_persons || b0.head_count || 0;
            var adultsN = row.selected_adults != null ? row.selected_adults : null;
            var childrenNoBedN = row.selected_children_no_bed != null ? row.selected_children_no_bed
                : ((row.child_without_bed && row.child_without_bed.children) || 0);
            var childrenWithBedN = row.selected_children_with_bed != null ? row.selected_children_with_bed
                : ((row.child_with_bed && row.child_with_bed.children) || 0);
            var childrenN = row.selected_children != null ? row.selected_children
                : ((parseInt(childrenNoBedN, 10) || 0) + (parseInt(childrenWithBedN, 10) || 0));
            var infantsN = row.selected_infants != null ? row.selected_infants : (row.infants || 0);
            var personsLabel = '';
            if (adultsN != null) {
                var pBits = [adultsN + 'A'];
                if (parseInt(childrenNoBedN, 10) > 0) pBits.push(childrenNoBedN + 'C no bed');
                if (parseInt(childrenWithBedN, 10) > 0) pBits.push(childrenWithBedN + 'C with bed');
                else if (childrenN && !childrenNoBedN && !childrenWithBedN) pBits.push(childrenN + 'C');
                if (infantsN) pBits.push(infantsN + 'I');
                personsLabel = pBits.join(' + ');
            } else if (personsN) {
                personsLabel = personsN + ' pax';
            }
            var grand = Number(row.grand_total != null ? row.grand_total : (row.totalPrice != null ? row.totalPrice : row.price) || 0);
            var childBits = [];
            if (parseInt(childrenWithBedN, 10) > 0 || row.childWithBedEnabled || childBedFlagOn(row, 'with')) childBits.push('Child w/ bed');
            if (parseInt(childrenNoBedN, 10) > 0 || row.childWithoutBedEnabled || childBedFlagOn(row, 'without')) childBits.push('Child w/o bed');
            var bed0 = (Array.isArray(r0.beds) && r0.beds[0]) ? r0.beds[0] : {};
            if (bed0.baby_cot || row.baby_cot) childBits.push('Baby cot');
            var isAdHocRowFlag = isAdHocRow(row);
            html +=
                '<tr class="' + (editing ? 'is-editing' : '') + '" data-added-idx="' + idx + '">' +
                '  <td>' +
                '    <div class="fw-semibold">' + esc(hotelName) +
                (isAdHocRowFlag ? ' <span class="stp-lite-adhoc-badge" title="Manual Room rate">AdHoc</span>' : '') +
                '</div>' +
                (row.is_return ? ' <span class="stp-lite-return-badge">Return</span>' : '') +
                (editing ? ' <span class="stp-lite-editing-label">Editing</span>' : '') +
                '    <div class="stp-lite-hotel-added-rooms">' + esc(String(roomsCount)) + ' room(s)' +
                (personsLabel ? ' · ' + esc(personsLabel) : '') +
                (childBits.length ? ' · ' + esc(childBits.join(', ')) : '') + '</div>' +
                '  </td>' +
                '  <td>' +
                '    <div>' + esc(roomType) + '</div>' +
                '    <small class="text-muted">' + esc(bedLabel) + '</small>' +
                '  </td>' +
                '  <td><small>' + esc(mealPlan) + '</small></td>' +
                '  <td><small>' + esc(stayLabel) + '</small></td>' +
                '  <td class="text-end fw-semibold text-nowrap">' + cur + ' ' + grand.toFixed(2) + '</td>' +
                '  <td>' +
                '    <div class="form-check mb-0">' +
                '      <input class="form-check-input hotel-is-supplement" type="checkbox" data-idx="' + idx + '"' +
                (row.supplement || row.is_supplement ? ' checked' : '') + '>' +
                '      <label class="form-check-label" style="font-size:0.72rem;">Supplement</label>' +
                '    </div>' +
                '  </td>' +
                '  <td class="text-end">' +
                '    <div class="stp-lite-hotel-card__btns justify-content-end">' +
                '      <button type="button" class="btn btn-sm btn-outline-primary hotel-view-breakup" data-idx="' + idx + '" title="Price breakdown">' +
                '        <i class="ri-file-list-3-line me-1"></i>Price breakdown' +
                '      </button>' +
                '      <button type="button" class="btn btn-sm btn-outline-secondary hotel-edit-added" data-idx="' + idx + '" title="Modify">' +
                '        <i class="ri-pencil-line me-1"></i>Modify' +
                '      </button>' +
                '      <button type="button" class="btn btn-sm btn-outline-danger hotel-remove-added" data-idx="' + idx + '" title="Remove">' +
                '        <i class="ri-delete-bin-line"></i>' +
                '      </button>' +
                '    </div>' +
                '  </td>' +
                '</tr>';
        });

        html += '</tbody></table></div>';
        host.innerHTML = html;
    }

    function collectAddPayload(root, priceData) {
        var hotelSelect = root.querySelector('.hotel-select');
        var roomType = root.querySelector('.hotel-room-type');
        var bedType = root.querySelector('.hotel-bed-type');
        var mealPlan = root.querySelector('.hotel-meal-plan');
        var roomsEl = root.querySelector('.hotel-rooms');
        var cotChk = root.querySelector('.hotel-chk-baby-cot');
        var roomOpt = roomType && roomType.options[roomType.selectedIndex];
        var stay = stayContextFromRoot(root) || {};
        var hotelOpt = hotelSelect && hotelSelect.options[hotelSelect.selectedIndex];
        var bedOpt = bedType && bedType.options[bedType.selectedIndex];
        var rooms = parseInt((roomsEl && roomsEl.value) || '1', 10) || 1;
        var hotelDates = selectedHotelDates(root);
        var grand = Number(priceData.grand_total || 0) * rooms;
        if (priceData.grand_total_with_cot != null) {
            grand = Number(priceData.grand_total_with_cot || 0);
        }
        var stayLabel = '';
        if (hotelDates.start && hotelDates.end && typeof moment !== 'undefined') {
            stayLabel = moment(hotelDates.start, 'YYYY-MM-DD').format('MMM D') + ' → ' +
                moment(hotelDates.end, 'YYYY-MM-DD').format('MMM D, YYYY');
        }
        var cotEnabled = !!(cotChk && cotChk.checked && !cotChk.disabled);
        var unitCwb = roomOpt ? (parseFloat(roomOpt.dataset.childWithBed) || 0) : 0;
        var unitCnb = roomOpt ? (parseFloat(roomOpt.dataset.childWithoutBed) || 0) : 0;
        var occInfo = bedOccupancyInfo(root);
        var classified = classifyHotelPax(root);
        var kidsNoBed = selectedChildrenNoBedCount(root);
        var kidsWithBed = selectedChildrenWithBedCount(root);
        var kids = kidsNoBed + kidsWithBed;
        var adults = selectedAdultsCount(root) || Math.max(1, classified.adults || 1);
        var infants = selectedInfantsCount(root);
        var nights = parseInt(priceData.nights, 10) || (Array.isArray(priceData.breakdown) ? priceData.breakdown.length : 0) || 1;
        var apiCwb = priceData.child_with_bed || null;
        var apiCnb = priceData.child_without_bed || null;
        var cwbObj = null;
        var cnbObj = null;
        // Resolve total extra-bed sell (Get Price bakes this into room_total; also store on bed)
        var xbSellTotal = Number(priceData.extra_bed_total || 0);
        if (!(xbSellTotal > 0) && Array.isArray(priceData.breakdown)) {
            priceData.breakdown.forEach(function (n) {
                xbSellTotal += Number(n.extra_bed_total || 0);
            });
        }
        if (!(xbSellTotal > 0)) {
            xbSellTotal = Number(priceData.extra_bed_price || 0)
                * (Number(priceData.extra_bed || 0) || 0)
                * nights;
        }
        if (kidsWithBed > 0) {
            var cwbTotal = 0;
            var cwbUnit = unitCwb;
            // Prefer Get Price child_with_bed total (even when room dataset unit is 0)
            if (apiCwb && Number(apiCwb.total) > 0) {
                cwbTotal = Number(apiCwb.total) * rooms;
                if (!(cwbUnit > 0)) {
                    cwbUnit = Number(apiCwb.unit_price || apiCwb.price || 0) || 0;
                }
            } else if (unitCwb > 0) {
                cwbTotal = unitCwb * kidsWithBed * nights * rooms;
            }
            // Do NOT copy extra-bed into child_with_bed.total_cost — it is already in
            // room/grand total. Quotation pulls extra-bed via beds.extra_bed_cost when CWB.
            cwbObj = {
                enabled: true,
                price: cwbUnit,
                children: kidsWithBed,
                total_cost: cwbTotal,
                total: cwbTotal
            };
        }
        // Child without bed: keep enabled (count) even when unit price is 0 — quotation must not show a child price
        if (kidsNoBed > 0) {
            var cnbTotal = 0;
            var cnbUnit = unitCnb;
            if (apiCnb && Number(apiCnb.total) > 0) {
                cnbTotal = Number(apiCnb.total) * rooms;
                if (!(cnbUnit > 0)) {
                    cnbUnit = Number(apiCnb.unit_price || apiCnb.price || 0) || 0;
                }
            } else if (unitCnb > 0) {
                cnbTotal = unitCnb * kidsNoBed * nights * rooms;
            }
            cnbObj = {
                enabled: true,
                price: cnbUnit,
                children: kidsNoBed,
                total_cost: cnbTotal,
                total: cnbTotal
            };
        }
        var babyCotPrice = occInfo.babyCotPrice || 0;
        var babyCotCost = (cotEnabled && babyCotPrice > 0) ? (babyCotPrice * Math.max(1, infants) * nights * rooms) : 0;
        if (cotEnabled && babyCotCost > 0 && priceData.grand_total_with_cot == null) grand += babyCotCost;

        var customer = (window.StpLiteGuests && window.StpLiteGuests.getCustomerDataForServices)
            ? window.StpLiteGuests.getCustomerDataForServices()
            : { fullName: '', email: '', phone: '', countryCode: '', address1: '', address2: null, state: null, zip: '', specialRequests: null };

        var persons = selectedPersonsCount(root);
        var extraBedOn = occInfo.extraBedAvailable && (kidsWithBed > 0 || persons > occInfo.maxOccupancy);
        var occupancy = persons <= 1 ? 'single' : 'double';
        var hotelId = hotelSelect ? hotelSelect.value : '';
        var hotelName = hotelOpt ? String(hotelOpt.textContent || '').split('(')[0].trim() : '';
        var roomId = (roomOpt && (roomOpt.dataset.roomId || roomOpt.value)) || '';
        var bedId = bedOpt ? (bedOpt.dataset.bedId || bedOpt.value) : '';
        var bedLabel = bedOpt ? String(bedOpt.textContent || '').split(' - ')[0].trim() : '';
        var mealVal = mealPlan ? mealPlan.value : '';
        var checkIn = hotelDates.start || stay.start || '';
        var checkOut = hotelDates.end || stay.end || '';
        var cfgLocal = cfg();

        // Backup-compatible hotel_data row (+ lite UI helpers for Modify / table)
        return {
            fullName: customer.fullName,
            email: customer.email,
            phone: customer.phone,
            countryCode: customer.countryCode,
            address1: customer.address1,
            address2: customer.address2,
            state: customer.state,
            zip: customer.zip,
            specialRequests: customer.specialRequests,
            id: null,
            bookingType: (window.StpLiteTransportShared && window.StpLiteTransportShared.defaultBookingType)
                ? window.StpLiteTransportShared.defaultBookingType()
                : 'enquiry',
            bookingDate: [checkIn, checkOut],
            city: stay.cityName || '',
            country: stay.country || '',
            hotelDetails: {
                hotel_id: hotelId,
                hotel_name: hotelName,
                image: (hotelOpt && hotelOpt.dataset.image) || '',
                location: stay.cityName || '',
                country: stay.country || '',
                city: stay.cityName || '',
                checkInTime: (hotelOpt && hotelOpt.dataset.checkIn) || '',
                checkOutTime: (hotelOpt && hotelOpt.dataset.checkOut) || '',
                cancellation_charge: null,
                infant_age_limit: classified.infantLimit,
                child_age_limit: classified.childLimit,
                extra_bed_age_limit: classified.extraBedMin
            },
            priceMode: (priceData && priceData.is_adhoc) ? 'adhoc' : 'dmc',
            priceModeId: parseInt(cfgLocal.dmcId, 10) || 0,
            is_adhoc: !!(priceData && priceData.is_adhoc),
            adhoc_price: (priceData && priceData.adhoc_price != null) ? Number(priceData.adhoc_price) : null,
            rooms: [{
                room_id: roomId,
                room_type: roomType ? roomType.value : '',
                occupancy: occupancy,
                selected_persons: persons,
                selected_adults: adults,
                selected_children: kids,
                selected_children_no_bed: kidsNoBed,
                selected_children_with_bed: kidsWithBed,
                selected_infants: infants,
                number_of_rooms: rooms,
                breakfast_included: 0,
                supplement_breakfast_included: 0,
                beds: [{
                    bed_id: bedId,
                    bed_type: bedLabel,
                    baby_cot: cotEnabled ? 1 : 0,
                    baby_cot_price: babyCotPrice,
                    baby_cot_cost: babyCotCost,
                    head_count: persons,
                    max_occupancy: occInfo.maxOccupancy || persons,
                    extra_bed: extraBedOn ? 1 : 0,
                    extra_bed_price: occInfo.extraBedPrice || Number(priceData.extra_bed_price || 0) || 0,
                    extra_bed_cost: extraBedOn ? (xbSellTotal * rooms) : 0,
                    price: Number(priceData.room_total || 0),
                    mealTypes: [mealVal],
                    meal_plan: mealVal
                }]
            }],
            totalPrice: grand,
            price: grand,
            transfer_options: null,
            child_with_bed: cwbObj,
            child_without_bed: cnbObj,
            children: kids,
            selected_children_no_bed: kidsNoBed,
            selected_children_with_bed: kidsWithBed,
            infants: infants,
            selected_adults: adults,
            selected_children: kids,
            selected_infants: infants,
            age_classification: {
                infant_ages: classified.asInfant || [],
                child_ages: classified.asChild || [],
                adult_ages: classified.asAdult || [],
                notes: classified.notes || []
            },
            children_price: priceData.children_price != null ? parseInt(priceData.children_price, 10) : 2,
            child_meal_factor: priceData.child_meal_factor != null ? Number(priceData.child_meal_factor) : 1,
            supplement: false,
            is_supplement: false,
            remarks: '',
            currency: currencyLabel(root),
            // Lite UI / hydrate helpers
            hotel_unique_id: hotelId,
            hotel_name: hotelName,
            hotel_id: hotelId,
            room_type: roomType ? roomType.value : '',
            bed_id: bedId,
            bed_label: bedOpt ? bedOpt.textContent : '',
            meal_plan: mealVal,
            number_of_rooms: rooms,
            selected_persons: persons,
            occupancy: occupancy,
            childWithBedEnabled: kidsWithBed > 0,
            childWithoutBedEnabled: kidsNoBed > 0,
            childWithBedPrice: unitCwb,
            childWithoutBedPrice: unitCnb,
            plan_index: stay.planIndex || '',
            is_return: !!stay.isReturn,
            stay_start: checkIn,
            stay_end: checkOut,
            stay_label: stayLabel,
            room_total: Number(priceData.room_total || 0) * rooms,
            meal_total: Number(priceData.meal_total || 0) * rooms,
            grand_total: grand,
            price_payload: priceData,
            helperPriceResult: priceData
        };
    }

    function addHotelRow(root) {
        if (!root.__lastHotelPrice) {
            alert('Please Get Price first.');
            return;
        }
        var rows = readHotelChunk(root);
        var payload = collectAddPayload(root, root.__lastHotelPrice);
        if (root.__editingIdx != null && root.__editingIdx >= 0 && root.__editingIdx < rows.length) {
            var prevHotel = rows[root.__editingIdx] || {};
            payload.bookingType = (window.StpLiteTransportShared && window.StpLiteTransportShared.resolveRowBookingType)
                ? window.StpLiteTransportShared.resolveRowBookingType(prevHotel)
                : (prevHotel.bookingType || payload.bookingType);
            rows[root.__editingIdx] = payload;
            root.__editingIdx = null;
        } else {
            rows.push(payload);
        }
        writeHotelChunk(root, rows);
        setAddButtonMode(root, false);
        renderAddedHotels(root);
        clearBreakdown(root, { disableAdd: true });

        var hotelSelect = root.querySelector('.hotel-select');
        if (hotelSelect) hotelSelect.selectedIndex = 0;
        resetRoomDependent(root);
        updateHotelChildPricingVisibility(root);
        syncHotelDateLimits(root);
    }

    function removeAddedHotel(root, idx) {
        var rows = readHotelChunk(root);
        rows.splice(idx, 1);
        writeHotelChunk(root, rows);
        if (root.__editingIdx === idx) {
            root.__editingIdx = null;
            setAddButtonMode(root, false);
        } else if (root.__editingIdx != null && root.__editingIdx > idx) {
            root.__editingIdx -= 1;
        }
        renderAddedHotels(root);
    }

    function beginEditHotel(root, idx) {
        var rows = readHotelChunk(root);
        var row = rows[idx];
        if (!row) return;

        root.__editingIdx = idx;
        setAddButtonMode(root, true);
        renderAddedHotels(root);
        hydrateHotelForm(root, row);
    }

    function childBedFlagOn(row, kind) {
        if (!row) return false;
        if (kind === 'with') {
            if (row.childWithBedEnabled) return true;
            var v = row.child_with_bed;
            if (v && typeof v === 'object') return !!(v.enabled || v.total_cost || v.total || v.price);
            return !!(v && v !== 0 && v !== '0');
        }
        if (row.childWithoutBedEnabled) return true;
        var w = row.child_without_bed;
        if (w && typeof w === 'object') return !!(w.enabled || w.total_cost || w.total || w.price);
        return !!(w && w !== 0 && w !== '0');
    }

    function firstRoom(row) {
        var rooms = row && Array.isArray(row.rooms) ? row.rooms : [];
        return rooms[0] || {};
    }

    function firstBed(row) {
        var r0 = firstRoom(row);
        var beds = Array.isArray(r0.beds) ? r0.beds : [];
        return beds[0] || {};
    }

    function savedBedId(row) {
        var b0 = firstBed(row);
        return row.bed_id || b0.bed_id || row.bedId || '';
    }

    function savedBedLabel(row) {
        var b0 = firstBed(row);
        var raw = row.bed_label || b0.bed_type || b0.bed_label || '';
        return String(raw).split(' - ')[0].trim().toLowerCase();
    }

    function savedPersonsFromRow(row) {
        var r0 = firstRoom(row);
        var b0 = firstBed(row);
        var n = parseInt(row.selected_persons || r0.selected_persons || b0.head_count || 0, 10) || 0;
        return n > 0 ? n : null;
    }

    function savedAdultsFromRow(row) {
        if (!row) return null;
        if (row.selected_adults != null) return parseInt(row.selected_adults, 10) || null;
        var total = savedPersonsFromRow(row);
        var kids = savedChildrenFromRow(row) || 0;
        if (total == null) return null;
        return Math.max(1, total - kids);
    }

    function savedChildrenRawTotal(row) {
        if (!row) return 0;
        if (row.selected_children != null) return parseInt(row.selected_children, 10) || 0;
        if (row.children != null && typeof row.children !== 'object') return parseInt(row.children, 10) || 0;
        var cwb = row.child_with_bed && row.child_with_bed.children;
        var cnb = row.child_without_bed && row.child_without_bed.children;
        if (cwb || cnb) return (parseInt(cwb, 10) || 0) + (parseInt(cnb, 10) || 0);
        return 0;
    }

    function savedChildrenFromRow(row) {
        if (!row) return 0;
        var noBed = savedChildrenNoBedFromRow(row);
        var withBed = savedChildrenWithBedFromRow(row);
        if (noBed || withBed) return noBed + withBed;
        return savedChildrenRawTotal(row);
    }

    function savedChildrenNoBedFromRow(row) {
        if (!row) return 0;
        if (row.selected_children_no_bed != null) return parseInt(row.selected_children_no_bed, 10) || 0;
        var r0 = firstRoom(row);
        if (r0 && r0.selected_children_no_bed != null) return parseInt(r0.selected_children_no_bed, 10) || 0;
        if (row.child_without_bed && row.child_without_bed.children != null) {
            return parseInt(row.child_without_bed.children, 10) || 0;
        }
        if (childBedFlagOn(row, 'without') && !childBedFlagOn(row, 'with')) {
            return savedChildrenRawTotal(row);
        }
        return 0;
    }

    function savedChildrenWithBedFromRow(row) {
        if (!row) return 0;
        if (row.selected_children_with_bed != null) return parseInt(row.selected_children_with_bed, 10) || 0;
        var r0 = firstRoom(row);
        if (r0 && r0.selected_children_with_bed != null) return parseInt(r0.selected_children_with_bed, 10) || 0;
        if (row.child_with_bed && row.child_with_bed.children != null) {
            return parseInt(row.child_with_bed.children, 10) || 0;
        }
        if (childBedFlagOn(row, 'with') && !childBedFlagOn(row, 'without')) {
            return savedChildrenRawTotal(row);
        }
        return 0;
    }

    function savedInfantsFromRow(row) {
        if (!row) return 0;
        if (row.selected_infants != null) return parseInt(row.selected_infants, 10) || 0;
        if (row.infants != null) return parseInt(row.infants, 10) || 0;
        var r0 = firstRoom(row);
        if (r0 && r0.selected_infants != null) return parseInt(r0.selected_infants, 10) || 0;
        return 0;
    }

    function babyCotFlagOn(row) {
        if (!row) return false;
        var b0 = firstBed(row);
        return !!(row.baby_cot || (b0 && (b0.baby_cot === 1 || b0.baby_cot === true)));
    }

    function applyBedSelection(root, row) {
        var bedType = root.querySelector('.hotel-bed-type');
        if (!bedType || !bedType.options || bedType.options.length < 2) return false;
        var wantId = String(savedBedId(row) || '').trim();
        var wantLabel = savedBedLabel(row);
        var match = null;
        Array.prototype.forEach.call(bedType.options, function (opt) {
            if (!opt.value || match) return;
            var id = String(opt.dataset.bedId || opt.value || '').trim();
            var text = String(opt.textContent || '').split(' - ')[0].trim().toLowerCase();
            if (wantId && (id === wantId || String(opt.value) === wantId)) {
                match = opt;
                return;
            }
            if (wantLabel && (text === wantLabel || text.indexOf(wantLabel) !== -1 || wantLabel.indexOf(text) !== -1)) {
                match = opt;
            }
        });
        if (!match && bedType.options.length === 2) {
            match = bedType.options[1];
        }
        if (!match) return false;
        bedType.value = match.value;
        return true;
    }

    function applySavedMealPlan(root, row) {
        var mealPlan = root.querySelector('.hotel-meal-plan');
        if (!mealPlan || !row) return;
        var want = String(row.meal_plan || firstBed(row).meal_plan || firstRoom(row).meal_plan || '').trim();
        if (!want) return;
        var found = Array.prototype.some.call(mealPlan.options, function (opt) {
            return String(opt.value) === want;
        });
        if (found) {
            mealPlan.value = want;
            return;
        }
        Array.prototype.forEach.call(mealPlan.options, function (opt) {
            if (String(opt.value).toLowerCase() === want.toLowerCase()) mealPlan.value = opt.value;
        });
    }

    function hydrateHotelForm(root, row) {
        var stay = stayContextFromRoot(root) || {};
        var cityName = stay.cityName || row.city || '';
        var country = stay.country || row.country || '';
        var hotelSelect = root.querySelector('.hotel-select');
        var roomsEl = root.querySelector('.hotel-rooms');
        var cotChk = root.querySelector('.hotel-chk-baby-cot');

        root.__hydrating = true;
        root.__hydrateHotelRow = row;
        if (roomsEl) roomsEl.value = String(row.number_of_rooms || (Array.isArray(row.rooms) ? ((row.rooms[0] || {}).number_of_rooms) : row.rooms) || 1);
        var checkInEl = root.querySelector('.hotel-check-in');
        var checkOutEl = root.querySelector('.hotel-check-out');
        var rowIn = row.stay_start || (Array.isArray(row.bookingDate) ? row.bookingDate[0] : '') || '';
        var rowOut = row.stay_end || (Array.isArray(row.bookingDate) ? row.bookingDate[1] : '') || '';
        syncHotelDateLimits(root);
        if (checkInEl && rowIn) checkInEl.value = rowIn;
        if (checkOutEl && rowOut) checkOutEl.value = rowOut;
        syncHotelDateLimits(root);
        if (cotChk) cotChk.checked = babyCotFlagOn(row);

        applyAdHocFromRow(root, row);

        return Promise.resolve(loadHotelsForCity(root, cityName, country, { skipReset: true }))
            .then(function () {
                if (!hotelSelect) return null;
                var hotelId = row.hotel_unique_id || row.hotel_id
                    || (row.hotelDetails && (row.hotelDetails.hotel_id || row.hotelDetails.hotel_unique_id))
                    || '';
                hotelSelect.value = String(hotelId);
                if (!hotelSelect.value) {
                    Array.prototype.forEach.call(hotelSelect.options, function (opt) {
                        var name = String(opt.textContent || '').split('(')[0].trim().toLowerCase();
                        var saved = String(row.hotel_name || (row.hotelDetails && row.hotelDetails.hotel_name) || '').toLowerCase();
                        if (saved && name === saved) hotelSelect.value = opt.value;
                    });
                }
                if (!hotelSelect.value) return null;
                return loadRoomsForHotel(root, hotelSelect.value, cityName, country);
            })
            .then(function () {
                var roomType = root.querySelector('.hotel-room-type');
                var wantRoom = String(row.room_type || firstRoom(row).room_type || '').trim();
                if (!roomType || !wantRoom) return null;
                var matched = Array.prototype.some.call(roomType.options, function (opt) {
                    return String(opt.value) === wantRoom;
                });
                if (!matched) {
                    Array.prototype.forEach.call(roomType.options, function (opt) {
                        if (String(opt.value).toLowerCase() === wantRoom.toLowerCase()) wantRoom = opt.value;
                    });
                }
                roomType.value = wantRoom;
                updateHotelChildPricingVisibility(root);
                return loadBedsForRoomType(root, roomType.value);
            })
            .then(function () {
                applyBedSelection(root, row);
                applySavedMealPlan(root, row);
                var savedNoBed = savedChildrenNoBedFromRow(row);
                var savedWithBed = savedChildrenWithBedFromRow(row);
                if (!savedNoBed && !savedWithBed) {
                    var totalKids = savedChildrenFromRow(row) || 0;
                    updatePersonSelector(root, null, savedAdultsFromRow(row), totalKids, savedInfantsFromRow(row));
                } else {
                    updatePersonSelector(root, null, savedAdultsFromRow(row), savedNoBed, savedInfantsFromRow(row), savedWithBed);
                }
                if (cotChk) cotChk.checked = babyCotFlagOn(row);
                updateHotelChildPricingVisibility(root);

                // Re-apply AdHoc after dependent selects load (edit form parity)
                applyAdHocFromRow(root, row);

                var payload = row.price_payload || row.helperPriceResult || null;
                if (typeof payload === 'string') {
                    try { payload = JSON.parse(payload); } catch (e) { payload = null; }
                }
                var rowAdHocPrice = resolveAdHocPriceFromRow(row);
                if (payload) {
                    var roomEmpty = !(Number(payload.room_total || 0) > 0);
                    if (isAdHocRow(row) && rowAdHocPrice != null && (!payload.is_adhoc || roomEmpty)) {
                        payload = applyAdHocToPriceData(payload, Number(rowAdHocPrice));
                    }
                    renderBreakdown(root, payload, row.number_of_rooms || firstRoom(row).number_of_rooms || 1);
                } else if (isAdHocRow(row) || Number(row.grand_total || row.totalPrice || 0) > 0) {
                    renderBreakdown(root, buildFallbackPriceDataFromRow(row),
                        row.number_of_rooms || firstRoom(row).number_of_rooms || 1);
                }
            })
            .catch(function () { /* ignore hydrate errors */ })
            .finally(function () {
                root.__hydrating = false;
                root.__hydrateHotelRow = null;
                applyAdHocFromRow(root, row);
                syncGetPriceBtn(root);
                try {
                    root.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } catch (e) { /* ignore */ }
            });
    }

    function fetchHotelPrice(root) {
        var citySelect = root.querySelector('.hotel-city-select');
        var hotelSelect = root.querySelector('.hotel-select');
        var roomType = root.querySelector('.hotel-room-type');
        var bedType = root.querySelector('.hotel-bed-type');
        var mealPlan = root.querySelector('.hotel-meal-plan');
        var roomsEl = root.querySelector('.hotel-rooms');
        var addBtn = root.querySelector('.hotel-add-btn');
        var loader = root.querySelector('.hotel-price-loader');

        var stay = stayContextFromRoot(root);
        var cityOpt = citySelect && citySelect.selectedOptions && citySelect.selectedOptions[0];
        var cityName = (stay && stay.cityName) || (cityOpt ? (cityOpt.getAttribute('data-city-name') || cityOpt.textContent) : '');
        var country = (stay && stay.country) || (cityOpt ? (cityOpt.getAttribute('data-country') || '') : '') ||
            (root.getAttribute('data-country') || '');

        if (!hotelSelect || !hotelSelect.value) {
            alert('Please select a hotel.');
            return;
        }
        if (!roomType || !roomType.value) {
            alert('Please select a room type.');
            return;
        }
        if (!bedType || !bedType.value) {
            alert('Please select a bed type.');
            return;
        }
        if (!mealPlan || !mealPlan.value) {
            alert('Please select a meal plan.');
            return;
        }
        if (isAdHocEnabled(root) && !isAdHocPriceReady(root)) {
            alert('Please enter a valid Manual Room rate.');
            syncGetPriceBtn(root);
            return;
        }
        var persons = selectedPersonsCount(root);
        if (persons < 1) {
            alert('Please set adults / children for this room.');
            return;
        }

        var stayRange = selectedHotelDates(root);
        if ((!stayRange.start || !stayRange.end) && stay && stay.start && stay.end) {
            stayRange = { start: stay.start, end: stay.end };
        } else if ((!stayRange.start || !stayRange.end)) {
            stayRange = stayDatesFromOption(cityOpt, cityName, country);
        }
        syncHotelDateLimits(root);
        stayRange = selectedHotelDates(root);
        var dates = nightDatesFromStay(stayRange.start, stayRange.end);
        if (!dates.length) {
            alert('Set a valid Check-in / Check-out within this city stay (Check-out must be after Check-in).');
            return;
        }

        var roomOpt = roomType.options[roomType.selectedIndex];
        var bedOpt = bedType.options[bedType.selectedIndex];
        var roomId = (roomOpt && roomOpt.dataset.roomId) || roomType.value;
        if (bedOpt && bedOpt.dataset.roomId) roomId = bedOpt.dataset.roomId;
        var bedId = (bedOpt && (bedOpt.dataset.bedId || bedOpt.value)) || '';

        var guests = tourPax();
        var occ = bedOccupancyInfo(root);
        var roomAdults = selectedAdultsCount(root);
        var kidsNoBed = selectedChildrenNoBedCount(root);
        var kidsWithBed = selectedChildrenWithBedCount(root);
        var roomChildren = kidsNoBed + kidsWithBed;
        var extraBed = (occ.extraBedAvailable && (kidsWithBed > 0 || persons > occ.maxOccupancy)) ? 1 : 0;
        var childWithBed = kidsWithBed > 0;
        var childWithoutBed = kidsNoBed > 0;
        var numberOfRooms = parseInt((roomsEl && roomsEl.value) || '1', 10) || 1;
        var inv = window.buildInventoryDmcQuery
            ? window.buildInventoryDmcQuery(cityName, country)
            : { city: cityName, country: country, dmc_id: cfg().dmcId || '' };

        if (addBtn) addBtn.disabled = true;
        if (loader) loader.classList.add('is-on');
        syncGetPriceBtn(root, { loading: true });

        var adhocPerNight = isAdHocEnabled(root) ? getAdHocPriceValue(root) : null;

        fetch(cfg().routes.getHotelPrice || '', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': cfg().csrfToken || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                hotel_unique_id: hotelSelect.value,
                room_id: roomId,
                bed_id: bedId,
                meal_plan: mealPlan.value,
                pax: persons,
                adults: roomAdults,
                children: roomChildren,
                child_with_bed: childWithBed,
                child_without_bed: childWithoutBed,
                child_with_bed_count: kidsWithBed,
                child_without_bed_count: kidsNoBed,
                extra_bed: extraBed,
                dates: dates,
                city: inv.city || cityName || '',
                country: inv.country || country || '',
                dmc_id: inv.dmc_id || ''
            })
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.success) {
                    // Always use Get Price result; AdHoc only swaps room rate.
                    var priced = adhocPerNight != null
                        ? applyAdHocToPriceData(data, adhocPerNight)
                        : data;
                    renderBreakdown(root, priced, numberOfRooms);
                } else {
                    clearBreakdown(root, { disableAdd: true });
                    alert((data && data.message) ? data.message : 'Failed to calculate hotel price.');
                }
            })
            .catch(function () {
                clearBreakdown(root, { disableAdd: true });
                alert('Error calculating hotel price.');
            })
            .finally(function () {
                if (loader) loader.classList.remove('is-on');
                syncGetPriceBtn(root);
            });
    }

    function resetRoomDependent(root) {
        var roomType = root.querySelector('.hotel-room-type');
        var bedType = root.querySelector('.hotel-bed-type');
        var mealPlan = root.querySelector('.hotel-meal-plan');
        if (roomType) {
            roomType.innerHTML = '<option value="">Select hotel first</option>';
            roomType.disabled = true;
        }
        if (bedType) {
            bedType.innerHTML = '<option value="">Select room type first</option>';
            bedType.disabled = true;
        }
        if (mealPlan) {
            mealPlan.innerHTML = '<option value="">Select hotel first</option>';
            mealPlan.disabled = true;
        }
        var adhocChk = root.querySelector('.hotel-chk-adhoc');
        if (adhocChk) adhocChk.checked = false;
        syncAdHocUi(root);
        var addBtn = root.querySelector('.hotel-add-btn');
        if (addBtn) addBtn.disabled = true;
        clearBreakdown(root, { disableAdd: true });
        updatePersonSelector(root);
    }

    function loadHotelsForCity(root, cityName, country, opts) {
        opts = opts || {};
        var hotelSelect = root.querySelector('.hotel-select');
        if (!hotelSelect || !cityName) {
            if (hotelSelect && !cityName) {
                hotelSelect.innerHTML = '<option value="">Select city first</option>';
                hotelSelect.disabled = true;
            }
            return Promise.resolve();
        }

        if (!opts.skipReset) resetRoomDependent(root);

        var panel = root.closest('[data-service="hotel"]') || root.closest('.stp-lite-country-section');
        var dmcId = (panel && panel.getAttribute('data-dmc-id')) || '';
        var inv = window.buildInventoryDmcQuery
            ? window.buildInventoryDmcQuery(cityName, country)
            : { qs: 'city=' + encodeURIComponent(cityName) + '&country=' + encodeURIComponent(country || '') + '&dmc_id=' + (cfg().dmcId || ''), dmc_id: cfg().dmcId || 0 };
        if (dmcId) {
            inv.dmc_id = dmcId;
            inv.qs = 'city=' + encodeURIComponent(inv.city || cityName)
                + '&country=' + encodeURIComponent(inv.country || country || '')
                + '&dmc_id=' + encodeURIComponent(dmcId);
        }

        var url = (cfg().routes && cfg().routes.fetchHotelsByDmc) || '';
        if (!url) {
            hotelSelect.innerHTML = '<option value="">Hotel route missing</option>';
            hotelSelect.disabled = false;
            return Promise.resolve();
        }

        var reqId = (root.__hotelLoadId = (root.__hotelLoadId || 0) + 1);
        hotelSelect.disabled = true;
        hotelSelect.innerHTML = '<option value="">Loading hotels…</option>';

        var fetchFn = (window.StpLiteTransportShared && window.StpLiteTransportShared.fetchJsonCached)
            ? window.StpLiteTransportShared.fetchJsonCached
            : null;

        var req = fetchFn
            ? fetchFn(url + '?' + inv.qs)
            : fetch(url + '?' + inv.qs, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg().csrfToken || '' },
                credentials: 'same-origin'
            }).then(function (r) { return r.json(); });

        return req
            .then(function (response) {
                if (reqId !== root.__hotelLoadId || !hotelSelect.isConnected) return;
                root.__hotelData = [];
                hotelSelect.innerHTML = '<option value="">Select a hotel in ' + esc(cityName) + '</option>';
                if (response && response.success && response.hotels && response.hotels.length) {
                    root.__hotelData = response.hotels;
                    response.hotels.forEach(function (hotel) {
                        var opt = document.createElement('option');
                        opt.value = hotel.hotel_unique_id;
                        opt.textContent = hotel.name + (hotel.hotel_star_rating ? ' (' + hotel.hotel_star_rating + '★)' : '');
                        if (hotel.main_image) opt.dataset.image = hotel.main_image;
                        if (hotel.check_in_time) opt.dataset.checkIn = hotel.check_in_time;
                        if (hotel.check_out_time) opt.dataset.checkOut = hotel.check_out_time;
                        opt.dataset.infantAgeLimit = hotel.infant_age_limit != null ? hotel.infant_age_limit : '';
                        opt.dataset.childAgeLimit = hotel.child_age_limit != null ? hotel.child_age_limit : '';
                        opt.dataset.extraBedAgeLimit = hotel.extra_bed_age_limit != null ? hotel.extra_bed_age_limit : '';
                        hotelSelect.appendChild(opt);
                    });
                } else {
                    hotelSelect.innerHTML = '<option value="">No hotels found</option>';
                }
                hotelSelect.disabled = false;
            })
            .catch(function () {
                if (reqId !== root.__hotelLoadId || !hotelSelect.isConnected) return;
                hotelSelect.innerHTML = '<option value="">Error loading hotels</option>';
                hotelSelect.disabled = false;
            });
    }

    function loadRoomsForHotel(root, hotelId, cityName, country) {
        var roomType = root.querySelector('.hotel-room-type');
        var bedType = root.querySelector('.hotel-bed-type');
        var mealPlan = root.querySelector('.hotel-meal-plan');
        if (!roomType) return Promise.resolve();

        if (!hotelId) {
            resetRoomDependent(root);
            return Promise.resolve();
        }

        var inv = window.buildInventoryDmcQuery
            ? window.buildInventoryDmcQuery(cityName, country)
            : { dmc_id: cfg().dmcId || 0, city: cityName, country: country };
        var panel = root.closest('[data-service="hotel"]') || root.closest('.stp-lite-country-section');
        var panelDmc = panel && panel.getAttribute('data-dmc-id');
        if (panelDmc) inv.dmc_id = panelDmc;

        roomType.disabled = true;
        roomType.innerHTML = '<option value="">Loading rooms…</option>';
        if (bedType) {
            bedType.disabled = true;
            bedType.innerHTML = '<option value="">Select room type first</option>';
        }
        if (mealPlan) {
            mealPlan.disabled = true;
            mealPlan.innerHTML = '<option value="">Loading…</option>';
        }

        var qs = 'hotel_id=' + encodeURIComponent(hotelId)
            + '&dmc_id=' + encodeURIComponent(inv.dmc_id || '')
            + '&city=' + encodeURIComponent(inv.city || cityName || '')
            + '&country=' + encodeURIComponent(inv.country || country || '');

        var roomsUrl = (cfg().routes.fetchRoomsByHotel || '') + '?' + qs;
        var fetchFn = (window.StpLiteTransportShared && window.StpLiteTransportShared.fetchJsonCached)
            ? window.StpLiteTransportShared.fetchJsonCached
            : null;
        var req = fetchFn
            ? fetchFn(roomsUrl)
            : fetch(roomsUrl, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg().csrfToken || '' }
            }).then(function (r) { return r.json(); });

        return req
            .then(function (response) {
                root.__roomData = [];
                roomType.innerHTML = '<option value="">Select room type</option>';

                if (!(response.success && response.rooms && response.rooms.length)) {
                    roomType.innerHTML = '<option value="">No rooms for this DMC</option>';
                    roomType.disabled = false;
                    if (mealPlan) {
                        mealPlan.innerHTML = '<option value="">No meal plans</option>';
                        mealPlan.disabled = false;
                    }
                    return;
                }

                var dmcId = response.dmc_id || inv.dmc_id;
                var rooms = response.rooms.filter(function (room) {
                    return !room.created_by || String(room.created_by) === String(dmcId);
                });
                if (!rooms.length) rooms = response.rooms;

                root.__roomData = rooms;
                root.__weekendDays = response.weekend_days || ['Saturday', 'Sunday'];

                var roomTypes = [];
                var seen = {};
                rooms.forEach(function (room) {
                    var t = String(room.room_type || '').trim();
                    if (!t || seen[t]) return;
                    seen[t] = true;
                    roomTypes.push(t);
                });

                roomTypes.forEach(function (t) {
                    var sample = rooms.find(function (r) { return r.room_type === t; }) || {};
                    var opt = document.createElement('option');
                    opt.value = t;
                    opt.textContent = t;
                    opt.dataset.roomId = sample.room_id || '';
                    opt.dataset.weekdayPrice = sample.weekday_price || 0;
                    opt.dataset.weekendPrice = sample.weekend_price || 0;
                    opt.dataset.doubleWeekdayPrice = sample.double_weekday_price || 0;
                    opt.dataset.doubleWeekendPrice = sample.double_weekend_price || 0;
                    opt.dataset.breakfastPrice = sample.breakfast_price || 0;
                    opt.dataset.lunchPrice = sample.lunch_price || 0;
                    opt.dataset.dinnerPrice = sample.dinner_price || 0;
                    opt.dataset.childWithBed = sample.child_with_bed || 0;
                    opt.dataset.childWithoutBed = sample.child_without_bed || 0;
                    roomType.appendChild(opt);
                });
                roomType.disabled = false;
                populateMealPlans(root, rooms);
                updateHotelChildPricingVisibility(root);

                if (bedType) {
                    bedType.innerHTML = '<option value="">Select room type first</option>';
                    bedType.disabled = true;
                }
            })
            .catch(function () {
                roomType.innerHTML = '<option value="">Error loading rooms</option>';
                roomType.disabled = false;
            });
    }

    function populateMealPlans(root, rooms) {
        var mealPlan = root.querySelector('.hotel-meal-plan');
        if (!mealPlan) return;
        var plans = buildMealPlanOptions(rooms);
        mealPlan.innerHTML = '<option value="">Select meal plan</option>';
        plans.forEach(function (p) {
            var opt = document.createElement('option');
            opt.value = p;
            opt.textContent = p;
            mealPlan.appendChild(opt);
        });
        mealPlan.disabled = false;
    }

    function loadBedsForRoomType(root, roomType) {
        var bedType = root.querySelector('.hotel-bed-type');
        if (!bedType) return Promise.resolve();

        if (!roomType || !root.__roomData) {
            bedType.innerHTML = '<option value="">Select room type first</option>';
            bedType.disabled = true;
            return Promise.resolve();
        }

        var roomsOfType = root.__roomData.filter(function (r) {
            return String(r.room_type || '').trim().toLowerCase() === String(roomType || '').trim().toLowerCase();
        });
        populateMealPlans(root, roomsOfType.length ? roomsOfType : root.__roomData);

        if (!roomsOfType.length) {
            bedType.innerHTML = '<option value="">No rooms of this type</option>';
            bedType.disabled = false;
            return Promise.resolve();
        }

        var savedRoomId = '';
        if (root.__hydrating && root.__hydrateHotelRow) {
            savedRoomId = String(firstRoom(root.__hydrateHotelRow).room_id || '');
        }
        var roomId = savedRoomId || roomsOfType[0].room_id;
        bedType.disabled = true;
        bedType.innerHTML = '<option value="">Loading bed types…</option>';

        return fetch((cfg().routes.fetchBedsByRoom || '') + '?room_id=' + encodeURIComponent(roomId), {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg().csrfToken || '' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                bedType.innerHTML = '<option value="">Select bed type</option>';
                if (data.success && data.beds && data.beds.length) {
                    data.beds.forEach(function (bed) {
                        var text = bed.room_type || bed.bed_type || 'Standard Bed';
                        var rawMax = parseInt(bed.max_occupancy, 10) || 0;
                        // DB max_occupancy usually includes extra-bed slot when extra_bed is enabled.
                        var baseMax = rawMax;
                        if (rawMax && bed.extra_bed) {
                            baseMax = Math.max(1, rawMax - 1);
                        }
                        var adultN = parseInt(bed.adult_count, 10) || 0;
                        var childN = parseInt(bed.child_count, 10) || 0;
                        var acMax = adultN + childN;
                        // Label Max = total capacity. When extra_bed is on, DB max_occupancy
                        // already includes that slot — say "extra bed included" (not "+ Extra Bed",
                        // which wrongly suggests capacity beyond Max).
                        var displayMax = bed.extra_bed
                            ? Math.max(rawMax, acMax, baseMax + 1)
                            : Math.max(rawMax, acMax);
                        if (displayMax > 0) text += ' - Max ' + displayMax + ' guests';
                        if (bed.adult_count != null || bed.child_count != null) {
                            if (adultN || childN) text += ' (' + adultN + 'A+' + childN + 'C)';
                        }
                        if (bed.extra_bed) text += ' · Extra bed included';
                        if (bed.baby_cot) text += ' + Baby Cot';

                        var opt = document.createElement('option');
                        opt.value = bed.bed_id;
                        opt.textContent = text;
                        opt.dataset.bedId = bed.bed_id;
                        opt.dataset.roomId = bed.room_id || roomId;
                        opt.dataset.maxOccupancy = baseMax || rawMax || '';
                        opt.dataset.adultCount = bed.adult_count != null ? bed.adult_count : '';
                        opt.dataset.childCount = bed.child_count != null ? bed.child_count : '';
                        opt.dataset.extraBed = bed.extra_bed ? '1' : '0';
                        opt.dataset.extraBedPrice = bed.extra_bed_price || '';
                        opt.dataset.babyCot = bed.baby_cot ? '1' : '0';
                        opt.dataset.babyCotPrice = bed.baby_cot_price || '';
                        bedType.appendChild(opt);
                    });
                } else {
                    bedType.innerHTML = '<option value="">No bed types available</option>';
                }
                bedType.disabled = false;
                if (!root.__hydrating) updatePersonSelector(root);
            })
            .catch(function () {
                bedType.innerHTML = '<option value="">Error loading beds</option>';
                bedType.disabled = false;
                if (!root.__hydrating) updatePersonSelector(root);
            });
    }

    function bindShell(root) {
        if (!root || root.__stpHotelBound) return;
        root.__stpHotelBound = true;

        var stay = stayContextFromRoot(root);
        var country = (stay && stay.country) || root.getAttribute('data-country') || '';
        var citySelect = root.querySelector('.hotel-city-select');
        var hotelSelect = root.querySelector('.hotel-select');
        var roomType = root.querySelector('.hotel-room-type');

        // Lock this hotel block to the section's single stay (no multi-city dropdown)
        if (citySelect && stay) {
            citySelect.innerHTML = '';
            var opt = document.createElement('option');
            opt.value = stay.cityId || stay.cityName;
            opt.textContent = stay.cityName + (stay.isReturn ? ' (Return)' : '');
            opt.setAttribute('data-city-name', stay.cityName);
            opt.setAttribute('data-country', stay.country || country);
            opt.setAttribute('data-plan-index', stay.planIndex || '');
            opt.setAttribute('data-is-return', stay.isReturn ? '1' : '0');
            if (stay.start) opt.setAttribute('data-stay-start', stay.start);
            if (stay.end) opt.setAttribute('data-stay-end', stay.end);
            opt.selected = true;
            citySelect.appendChild(opt);
            loadHotelsForCity(root, stay.cityName, stay.country || country);
        } else if (citySelect) {
            var cities = citiesForCountry(country);
            citySelect.innerHTML = '';
            if (!cities.length) {
                citySelect.innerHTML = '<option value="">No city</option>';
            } else {
                cities.forEach(function (p, i) {
                    var o = document.createElement('option');
                    o.value = p.cityId || p.cityName;
                    o.textContent = p.cityName;
                    o.setAttribute('data-city-name', p.cityName);
                    o.setAttribute('data-country', p.country || country);
                    o.setAttribute('data-plan-index', p.planIndex || '');
                    if (p.start) o.setAttribute('data-stay-start', p.start);
                    if (p.end) o.setAttribute('data-stay-end', p.end);
                    if (i === 0) o.selected = true;
                    citySelect.appendChild(o);
                });
                loadHotelsForCity(root, cities[0].cityName, cities[0].country || country);
            }
        }

        syncHotelDateLimits(root);
        var checkInEl = root.querySelector('.hotel-check-in');
        var checkOutEl = root.querySelector('.hotel-check-out');
        function onHotelDatesChanged() {
            syncHotelDateLimits(root);
            invalidatePriceState(root);
        }
        if (checkInEl) {
            checkInEl.addEventListener('change', onHotelDatesChanged);
            checkInEl.addEventListener('input', onHotelDatesChanged);
        }
        if (checkOutEl) {
            checkOutEl.addEventListener('change', onHotelDatesChanged);
            checkOutEl.addEventListener('input', onHotelDatesChanged);
        }

        if (hotelSelect) {
            hotelSelect.addEventListener('change', function () {
                invalidatePriceState(root);
                var s = stayContextFromRoot(root) || {};
                var o = citySelect && citySelect.selectedOptions[0];
                var cityName = s.cityName || (o ? (o.getAttribute('data-city-name') || o.textContent) : '');
                var ctry = s.country || (o ? (o.getAttribute('data-country') || country) : country);
                loadRoomsForHotel(root, hotelSelect.value, cityName, ctry).then(function () {
                    updatePersonSelector(root);
                    updateAgeClassNote(root);
                });
            });
        }

        if (roomType) {
            roomType.addEventListener('change', function () {
                invalidatePriceState(root);
                updateHotelChildPricingVisibility(root);
                var roomsEl = root.querySelector('.hotel-rooms');
                if (roomsEl) {
                    var rv = parseInt(roomsEl.value, 10);
                    if (!rv || rv < 1) roomsEl.value = '1';
                }
                loadBedsForRoomType(root, roomType.value).then(function () {
                    updatePersonSelector(root);
                    // Default: first bed type + first meal plan, then auto Get Price
                    var bedType = root.querySelector('.hotel-bed-type');
                    if (bedType && !bedType.value) {
                        for (var bi = 0; bi < bedType.options.length; bi++) {
                            if (bedType.options[bi].value) {
                                bedType.value = bedType.options[bi].value;
                                break;
                            }
                        }
                        updatePersonSelector(root);
                    }
                    var mealPlan = root.querySelector('.hotel-meal-plan');
                    if (mealPlan) {
                        var firstMeal = '';
                        for (var mi = 0; mi < mealPlan.options.length; mi++) {
                            if (mealPlan.options[mi].value) {
                                firstMeal = mealPlan.options[mi].value;
                                break;
                            }
                        }
                        if (firstMeal) mealPlan.value = firstMeal;
                    }
                    // Auto-click Get Price when room category changes and meal is ready
                    if (roomType.value
                        && bedType && bedType.value
                        && mealPlan && mealPlan.value
                        && !root.__hydrating
                        && isAdHocPriceReady(root)) {
                        fetchHotelPrice(root);
                    }
                });
            });
        }

        var bedTypeEl = root.querySelector('.hotel-bed-type');
        if (bedTypeEl) {
            bedTypeEl.addEventListener('change', function () {
                updatePersonSelector(root);
                invalidatePriceState(root);
            });
        }

        var occBody = root.querySelector('.hotel-occ-body');
        if (occBody) {
            occBody.addEventListener('click', function (e) {
                var minusA = e.target.closest('.hotel-adult-minus');
                var plusA = e.target.closest('.hotel-adult-plus');
                var minusNo = e.target.closest('.hotel-child-nobed-minus');
                var plusNo = e.target.closest('.hotel-child-nobed-plus');
                var minusWith = e.target.closest('.hotel-child-withbed-minus');
                var plusWith = e.target.closest('.hotel-child-withbed-plus');
                var minusI = e.target.closest('.hotel-infant-minus');
                var plusI = e.target.closest('.hotel-infant-plus');
                if (minusA) { e.preventDefault(); adjustOccupancy(root, 'adults', -1); return; }
                if (plusA) { e.preventDefault(); adjustOccupancy(root, 'adults', 1); return; }
                if (minusNo) { e.preventDefault(); adjustOccupancy(root, 'children-no-bed', -1); return; }
                if (plusNo) { e.preventDefault(); adjustOccupancy(root, 'children-no-bed', 1); return; }
                if (minusWith) { e.preventDefault(); adjustOccupancy(root, 'children-with-bed', -1); return; }
                if (plusWith) { e.preventDefault(); adjustOccupancy(root, 'children-with-bed', 1); return; }
                if (minusI) { e.preventDefault(); adjustOccupancy(root, 'infants', -1); return; }
                if (plusI) { e.preventDefault(); adjustOccupancy(root, 'infants', 1); return; }
            });
        }

        var cotChkEl = root.querySelector('.hotel-chk-baby-cot');
        if (cotChkEl) {
            cotChkEl.addEventListener('change', function () {
                updateOccupancySummary(root);
                invalidatePriceState(root);
            });
        }

        document.addEventListener('stp:guests-changed', function () {
            if (document.contains(root)) updatePersonSelector(root);
        });

        // Any pricing-field change invalidates Get Price result + Add button
        root.querySelectorAll('.hotel-bed-type, .hotel-meal-plan, .hotel-rooms').forEach(function (el) {
            el.addEventListener('change', function () {
                updateOccupancySummary(root);
                invalidatePriceState(root);
            });
            el.addEventListener('input', function () { invalidatePriceState(root); });
        });

        var adhocChk = root.querySelector('.hotel-chk-adhoc');
        var adhocInput = root.querySelector('.hotel-adhoc-price');
        if (adhocChk) {
            adhocChk.addEventListener('change', function () {
                syncAdHocUi(root);
                invalidatePriceState(root);
            });
        }
        if (adhocInput) {
            adhocInput.addEventListener('keydown', function (e) {
                if (e.key === 'e' || e.key === 'E' || e.key === '+' || e.key === '-') {
                    e.preventDefault();
                }
            });
            adhocInput.addEventListener('input', function () {
                var cleaned = sanitizeAdHocPriceValue(adhocInput.value);
                if (adhocInput.value !== cleaned) adhocInput.value = cleaned;
                syncGetPriceBtn(root);
                invalidatePriceState(root);
            });
            adhocInput.addEventListener('paste', function (e) {
                try {
                    var text = (e.clipboardData || window.clipboardData).getData('text');
                    if (text != null) {
                        e.preventDefault();
                        adhocInput.value = sanitizeAdHocPriceValue(text);
                        syncGetPriceBtn(root);
                        invalidatePriceState(root);
                    }
                } catch (err) { /* ignore */ }
            });
        }
        syncAdHocUi(root);

        var getPriceBtn = root.querySelector('.hotel-get-price-btn');
        var addBtn = root.querySelector('.hotel-add-btn');
        var closeBtn = root.querySelector('.hotel-breakup-close');

        setAddEnabled(root, false);
        setAddButtonMode(root, false);
        updateHotelChildPricingVisibility(root);
        syncGetPriceBtn(root);

        if (getPriceBtn) {
            getPriceBtn.addEventListener('click', function () {
                fetchHotelPrice(root);
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                // Hide table only; keep Add enabled until fields change
                var panel = root.querySelector('[data-hotel-breakup-panel]');
                if (panel) panel.classList.add('d-none');
            });
        }

        if (addBtn) {
            addBtn.addEventListener('click', function () {
                addHotelRow(root);
            });
        }

        root.addEventListener('change', function (e) {
            var supp = e.target.closest('.hotel-is-supplement');
            if (supp && root.contains(supp)) {
                var rows = readHotelChunk(root);
                var i = parseInt(supp.getAttribute('data-idx'), 10) || 0;
                if (rows[i]) {
                    rows[i].supplement = !!supp.checked;
                    rows[i].is_supplement = !!supp.checked;
                    writeHotelChunk(root, rows);
                }
            }
        });

        root.addEventListener('click', function (e) {
            var removeBtn = e.target.closest('.hotel-remove-added');
            if (removeBtn && root.contains(removeBtn)) {
                e.preventDefault();
                if (!window.confirm('Are you sure you want to remove this service?')) return;
                removeAddedHotel(root, parseInt(removeBtn.getAttribute('data-idx'), 10) || 0);
                return;
            }
            var editBtn = e.target.closest('.hotel-edit-added');
            if (editBtn && root.contains(editBtn)) {
                e.preventDefault();
                beginEditHotel(root, parseInt(editBtn.getAttribute('data-idx'), 10) || 0);
            }
        });

        // Document-level so edit-form remounts still open the breakdown modal
        if (!window.__stpLiteHotelBreakupBound) {
            window.__stpLiteHotelBreakupBound = true;
            document.addEventListener('click', function (e) {
                var viewBtn = e.target.closest('.hotel-view-breakup');
                if (!viewBtn) return;
                var hotelRoot = viewBtn.closest('.stp-lite-hotel');
                if (!hotelRoot) return;
                e.preventDefault();
                e.stopPropagation();
                var rows = readHotelChunk(hotelRoot);
                var row = rows[parseInt(viewBtn.getAttribute('data-idx'), 10) || 0];
                if (row) openHotelPricePopup(row);
            }, true);
        }

        document.addEventListener('stp:guests-changed', function () {
            updateHotelChildPricingVisibility(root);
        });

        ensurePriceModal();
        renderAddedHotels(root);
        syncHotelDataHidden();
    }

    function mountAll(root) {
        var scope = root || document;
        scope.querySelectorAll('[data-stp-hotel-mount]').forEach(function (el) {
            var panel = el.closest('[data-service="hotel"]');
            var country = panel ? panel.getAttribute('data-country') : '';
            var currency = panel ? panel.getAttribute('data-currency') : '';
            var stay = panel ? {
                cityId: panel.getAttribute('data-city-id') || '',
                cityName: panel.getAttribute('data-city-name') || '',
                planIndex: panel.getAttribute('data-plan-index') || '',
                isReturn: panel.getAttribute('data-is-return') === '1',
                start: panel.getAttribute('data-stay-start') || '',
                end: panel.getAttribute('data-stay-end') || '',
                country: country
            } : {};
            el.innerHTML = shellHtml(country, currency, stay);
            bindShell(el.querySelector('.stp-lite-hotel'));
        });
        if (window.StpLiteGuestCaps) window.StpLiteGuestCaps.refreshGuestDependentUI();
        scope.querySelectorAll('.stp-lite-hotel').forEach(function (root) {
            updateHotelChildPricingVisibility(root);
        });
        syncHotelDataHidden();
    }

    window.StpLiteHotel = {
        mountAll: mountAll,
        shellHtml: shellHtml,
        loadHotelsForCity: loadHotelsForCity,
        syncHotelDataHidden: syncHotelDataHidden,
        updateHotelChildPricingVisibility: updateHotelChildPricingVisibility,
        seedAdded: function (root, rows) {
            if (!root) return;
            writeHotelChunk(root, rows || []);
            setAddButtonMode(root, false);
            renderAddedHotels(root);
        }
    };
})(window, document);
/* === END hotel.js === */
