/* === STP LITE: country-segments.js ===
 * Depends: geo.js, country-mode.js, guest-caps.js, jQuery, moment
 * Owns: auto City (this stay) rows from selected cities (dates only);
 *       optional Return plan → second stay row for the same city;
 *       gate services + Continue until all stay ranges valid;
 *       tinted country sections + service accordions
 * === */
(function (window, document, $) {
    'use strict';

    var segmentIndex = 0;
    var SERVICE_META = [
        { key: 'hotel', label: 'Hotel', subtitle: 'Stay hotels for this city', icon: 'ri-hotel-line', tint: 'hotel', emoji: '🏨' },
        { key: 'arrival', label: 'Arrival Transport Services', subtitle: 'Edit entry port transfers', icon: 'ri-login-circle-line', tint: 'arrival', emoji: '🚌' },
        { key: 'attraction', label: 'All Attraction Tickets', subtitle: 'All attractions from all days in one place', icon: 'ri-coupon-3-line', tint: 'attraction', emoji: '🎫' },
        { key: 'guide', label: 'All Tour Guide Services', subtitle: 'All guides from all days in one place', icon: 'ri-user-star-line', tint: 'guide', emoji: '👤' },
        { key: 'restaurant', label: 'All Restaurant Services', subtitle: 'All restaurants from all days in one place', icon: 'ri-restaurant-2-line', tint: 'restaurant', emoji: '🍽️' },
        { key: 'transport', label: 'Other Transport Services', subtitle: 'Local transfers and other transport services from all days', icon: 'ri-car-line', tint: 'transport', emoji: '🚗' },
        { key: 'departure', label: 'Departure Transport Services', subtitle: 'Edit exit port transfers', icon: 'ri-logout-circle-line', tint: 'departure', emoji: '✈️' },
        { key: 'miscellaneous', label: 'Miscellaneous Items', subtitle: 'Country-scoped extras for this stay', icon: 'ri-file-list-3-line', tint: 'miscellaneous', emoji: '🧾' }
    ];

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

    function cityLabelFromOption(opt) {
        if (!opt) return { id: '', name: '', country: '' };
        var name = String(opt.getAttribute('data-city-name') || '').trim()
            || String(opt.textContent || '').replace(/\s*\([^)]*\)\s*$/, '').trim();
        var country = String(opt.getAttribute('data-country') || '').trim();
        if (!country) {
            var m = String(opt.textContent || '').match(/\(([^)]+)\)\s*$/);
            if (m && m[1]) country = String(m[1]).trim();
        }
        return { id: String(opt.value || ''), name: name, country: country };
    }

    function getMasterCityData() {
        if (window.StpLiteTourDetails && typeof window.StpLiteTourDetails.getTourCityItems === 'function') {
            return window.StpLiteTourDetails.getTourCityItems();
        }
        return [];
    }

    function tourDates() {
        return {
            start: String($('#start_date').val() || ''),
            end: String($('#end_date').val() || '')
        };
    }

    /**
     * Half-open stay ranges: [start, end).
     * End of city A may equal start of city B (handover day) — not an overlap.
     * Identical or intersecting calendar days do overlap.
     */
    function datesOverlap(startA, endA, startB, endB) {
        if (typeof moment === 'undefined') return false;
        var a0 = moment(startA, 'YYYY-MM-DD', true);
        var a1 = moment(endA, 'YYYY-MM-DD', true);
        var b0 = moment(startB, 'YYYY-MM-DD', true);
        var b1 = moment(endB, 'YYYY-MM-DD', true);
        if (!a0.isValid() || !a1.isValid() || !b0.isValid() || !b1.isValid()) return false;
        return a0.isBefore(b1, 'day') && a1.isAfter(b0, 'day');
    }

    function isDateInsideTour(ymd, tourStart, tourEnd) {
        if (!ymd || !tourStart || !tourEnd || typeof moment === 'undefined') return false;
        var d = moment(ymd, 'YYYY-MM-DD', true);
        return d.isValid()
            && !d.isBefore(moment(tourStart, 'YYYY-MM-DD'), 'day')
            && !d.isAfter(moment(tourEnd, 'YYYY-MM-DD'), 'day');
    }

    /**
     * When travel dates missing → clear + disable per-city dates.
     * When travel dates updated → drop any stay dates outside the new window.
     */
    function syncStayDatesWithTourWindow(opts) {
        opts = opts || {};
        var tour = tourDates();
        var hasTour = !!(tour.start && tour.end);
        var $inputs = $('#segmentsWrapper .start-date, #segmentsWrapper .end-date');

        if (!hasTour) {
            $inputs.val('').prop('disabled', true).removeAttr('min').removeAttr('max');
            $('#segmentsWrapper .segment').removeClass('is-ready').addClass('is-incomplete');
            return { cleared: true, hasTour: false };
        }

        $inputs.prop('disabled', false).attr({ min: tour.start, max: tour.end });

        var changed = false;
        $('#segmentsWrapper .segment').each(function () {
            var $seg = $(this);
            var $start = $seg.find('.start-date');
            var $end = $seg.find('.end-date');
            var start = String($start.val() || '');
            var end = String($end.val() || '');

            if (start && !isDateInsideTour(start, tour.start, tour.end)) {
                $start.val('');
                start = '';
                changed = true;
            }
            if (end && !isDateInsideTour(end, tour.start, tour.end)) {
                $end.val('');
                end = '';
                changed = true;
            }
            // Single-city auto-fill when empty (and tour dates just set/updated)
            if (opts.prefillSingle && $('#segmentsWrapper .segment').length === 1 && !start && !end) {
                $start.val(tour.start);
                $end.val(tour.end);
                changed = true;
            }
        });

        return { cleared: false, hasTour: true, changed: changed };
    }

    function applySegmentDateLimits() {
        syncStayDatesWithTourWindow();
    }

    function collectPlans() {
        var plans = [];
        $('#segmentsWrapper .segment').each(function () {
            var $seg = $(this);
            var $city = $seg.find('.city-select');
            var opt = $city[0] && $city[0].options ? $city[0].options[$city[0].selectedIndex] : null;
            var parsed = cityLabelFromOption(opt);
            if (!parsed.name) {
                parsed.name = String($seg.attr('data-city-name') || '');
                parsed.country = String($seg.attr('data-country') || '');
                parsed.id = String($seg.attr('data-city-id') || '');
            }
            var start = String($seg.find('.start-date').val() || '');
            var end = String($seg.find('.end-date').val() || '');
            var country = parsed.country || window.resolveCountryForCity(parsed.name) || '';
            var currency = window.resolveCurrencyForCityName(parsed.name, country) || '';
            var isReturn = $seg.attr('data-is-return') === '1';
            var datesOk = !!(start && end && typeof moment !== 'undefined'
                && moment(end).diff(moment(start), 'days') >= 1);
            if (parsed.name) window.rememberLiteCityGeo(parsed.name, country, currency);
            plans.push({
                index: String($seg.data('index') || ''),
                cityId: parsed.id,
                cityName: parsed.name,
                country: country,
                currency: currency,
                start: start,
                end: end,
                isReturn: isReturn,
                complete: !!(parsed.name && country && datesOk)
            });
            $seg.toggleClass('is-ready', !!plans[plans.length - 1].complete);
            $seg.toggleClass('is-incomplete', !plans[plans.length - 1].complete);
            $seg.toggleClass('is-return-stay', isReturn);
        });
        return plans;
    }

    function getActivePlans() {
        var plans = collectPlans();
        if (plans.length) return plans;
        return getMasterCityData().map(function (item, i) {
            var country = item.country || window.resolveCountryForCity(item.name) || '';
            var currency = window.resolveCurrencyForCityName(item.name, country) || '';
            return {
                index: 'seed_' + i,
                cityId: item.id,
                cityName: item.name,
                country: country,
                currency: currency,
                start: '',
                end: '',
                isReturn: false,
                complete: false
            };
        });
    }

    function areStayDatesReady() {
        var cities = getMasterCityData();
        if (!cities.length) return false;
        var tourStart = $('#start_date').val();
        var tourEnd = $('#end_date').val();
        if (!tourStart || !tourEnd) return false;

        var plans = collectPlans();
        if (!plans.length) return false;
        if (!plans.every(function (p) { return p.complete; })) return false;

        // Every selected city must have at least one (primary) stay
        var covered = {};
        plans.forEach(function (p) {
            if (p.cityId) covered[String(p.cityId)] = true;
        });
        if (!cities.every(function (c) { return !!covered[String(c.id)]; })) return false;

        // Overlap check across all complete plans (including return stays)
        for (var i = 0; i < plans.length; i++) {
            for (var j = i + 1; j < plans.length; j++) {
                if (datesOverlap(plans[i].start, plans[i].end, plans[j].start, plans[j].end)) {
                    return false;
                }
            }
        }
        return true;
    }

    function updateServicesGate() {
        var ready = areStayDatesReady();
        var gate = document.getElementById('countrySectionsGate');
        var status = document.getElementById('stayDatesStatus');
        var hint = document.getElementById('stayDatesHint');

        if (gate) gate.classList.toggle('is-locked', !ready);
        if (status) {
            status.textContent = ready ? 'Dates ready' : 'Dates required';
            status.className = ready ? 'badge text-bg-success' : 'badge text-bg-secondary';
            status.style.fontSize = '0.72rem';
        }
        if (hint) hint.classList.toggle('d-none', ready);

        document.dispatchEvent(new CustomEvent('stp:stay-dates-ready', { detail: { ready: ready } }));
        return ready;
    }

    /**
     * Auto-create one City (this stay) row per selected city.
     * Optional Return checkbox adds a second stay row for the same city.
     * City is locked; user only edits Stay from / Stay until (+ Return).
     */
    function clampStayDates(startVal, endVal, tour, allowPrefill) {
        startVal = startVal || '';
        endVal = endVal || '';
        if (!tour.start || !tour.end) return { start: '', end: '' };
        if (startVal && !isDateInsideTour(startVal, tour.start, tour.end)) startVal = '';
        if (endVal && !isDateInsideTour(endVal, tour.start, tour.end)) endVal = '';
        if (allowPrefill && !startVal && !endVal) {
            startVal = tour.start;
            endVal = tour.end;
        }
        return { start: startVal, end: endVal };
    }

    function nextSegmentIndex() {
        segmentIndex++;
        return segmentIndex;
    }

    /** Stable tint 0–5 from selected-city order (same city = same color everywhere). */
    function cityTintIndex(cityId, cityName) {
        var cities = getMasterCityData();
        var id = String(cityId || '');
        var name = String(cityName || '').toLowerCase();
        for (var i = 0; i < cities.length; i++) {
            if ((id && String(cities[i].id) === id) ||
                (name && String(cities[i].name || '').toLowerCase() === name)) {
                return i % 6;
            }
        }
        var key = id || name || '0';
        var h = 0;
        for (var j = 0; j < key.length; j++) h = ((h << 5) - h) + key.charCodeAt(j);
        return Math.abs(h) % 6;
    }

    function buildStayRowHtml(item, opts) {
        opts = opts || {};
        var idx = opts.index;
        var isReturn = !!opts.isReturn;
        var startVal = opts.start || '';
        var endVal = opts.end || '';
        var datesDisabled = opts.datesDisabled ? ' disabled' : '';
        var returnChecked = !!opts.returnChecked;
        var allowReturn = !!opts.allowReturn;
        var tint = (opts.tintIndex != null) ? opts.tintIndex : cityTintIndex(item.id, item.name);
        var label = item.name + (item.country ? ' (' + item.country + ')' : '');
        var titleLabel = isReturn ? (label + ' · Return') : label;
        var returnBadge = isReturn
            ? '<span class="stp-lite-return-badge ms-1">Return stay</span>'
            : '';

        var returnCell = '';
        if (!isReturn && allowReturn) {
            returnCell =
                '      <div class="col-auto">' +
                '        <label class="stp-lite-label">Return</label>' +
                '        <div class="form-check form-switch mt-1 mb-0">' +
                '          <input class="form-check-input city-return-toggle" type="checkbox" role="switch"' +
                '                 id="city_return_' + idx + '" data-city-id="' + esc(item.id) + '"' +
                (returnChecked ? ' checked' : '') + '>' +
                '          <label class="form-check-label" for="city_return_' + idx + '" style="font-size:0.75rem;">Same city again</label>' +
                '        </div>' +
                '        <input type="hidden" name="segments[' + idx + '][return_plan]" value="' + (returnChecked ? '1' : '0') + '" class="city-return-value">' +
                '      </div>';
        } else if (isReturn) {
            returnCell =
                '      <div class="col-auto">' +
                '        <label class="stp-lite-label">&nbsp;</label>' +
                '        <div class="text-muted pt-1" style="font-size:0.72rem;max-width:7rem;line-height:1.2;">Return visit</div>' +
                '        <input type="hidden" name="segments[' + idx + '][return_plan]" value="1">' +
                '        <input type="hidden" name="segments[' + idx + '][is_return]" value="1">' +
                '      </div>';
        } else {
            returnCell =
                '      <div class="d-none">' +
                '        <input type="hidden" name="segments[' + idx + '][return_plan]" value="0">' +
                '      </div>';
        }

        return (
            '<div class="card mt-2 segment border-0 shadow-sm stp-lite-plan-card is-incomplete' + (isReturn ? ' is-return-stay' : '') + '"' +
            ' data-index="' + idx + '" data-city-id="' + esc(item.id) + '" data-city-name="' + esc(item.name) + '"' +
            ' data-country="' + esc(item.country) + '" data-is-return="' + (isReturn ? '1' : '0') + '"' +
            ' data-tint="' + tint + '">' +
            '  <div class="card-body py-2 px-2">' +
            '    <div class="row g-2 align-items-end">' +
            '      <div class="col-md-4">' +
            '        <label class="stp-lite-label">City (this stay)' + returnBadge + '</label>' +
            '        <div class="stp-lite-city-static" title="' + esc(titleLabel) + '">' + esc(label) + '</div>' +
            '        <select class="d-none city-select" aria-hidden="true" tabindex="-1">' +
            '          <option value="' + esc(item.id) + '" data-city-name="' + esc(item.name) +
            '" data-country="' + esc(item.country) + '" selected>' + esc(label) + '</option>' +
            '        </select>' +
            '        <input type="hidden" name="segments[' + idx + '][city]" value="' + esc(item.id) + '">' +
            '      </div>' +
            '      <div class="col-md-3">' +
            '        <label class="stp-lite-label">Stay from</label>' +
            '        <input type="date" class="form-control form-control-sm start-date" name="segments[' + idx + '][start_date]" value="' + esc(startVal) + '"' + datesDisabled + '>' +
            '      </div>' +
            '      <div class="col-md-3">' +
            '        <label class="stp-lite-label">Stay until</label>' +
            '        <input type="date" class="form-control form-control-sm end-date" name="segments[' + idx + '][end_date]" value="' + esc(endVal) + '"' + datesDisabled + '>' +
            '      </div>' +
            returnCell +
            '    </div>' +
            '  </div>' +
            '</div>'
        );
    }

    function syncAutoCityPlanRows(opts) {
        opts = opts || {};
        var cities = getMasterCityData();
        var $wrap = $('#segmentsWrapper');
        if (!$wrap.length) return;

        $('#multiCountryPlanner').toggleClass('d-none', cities.length === 0);

        // Snapshot primary + return stays per city id
        var saved = {};
        $wrap.find('.segment').each(function () {
            var $seg = $(this);
            var id = String($seg.attr('data-city-id') || $seg.find('.city-select').val() || '');
            if (!id) return;
            if (!saved[id]) {
                saved[id] = {
                    start: '', end: '', index: null,
                    returnChecked: false,
                    returnStart: '', returnEnd: '', returnIndex: null
                };
            }
            var isReturn = $seg.attr('data-is-return') === '1';
            if (isReturn) {
                // Keep return dates for re-open, but never force returnChecked=true here —
                // that prevented the Return switch from turning off.
                saved[id].returnStart = String($seg.find('.start-date').val() || '');
                saved[id].returnEnd = String($seg.find('.end-date').val() || '');
                saved[id].returnIndex = $seg.data('index');
            } else {
                saved[id].start = String($seg.find('.start-date').val() || '');
                saved[id].end = String($seg.find('.end-date').val() || '');
                saved[id].index = $seg.data('index');
                if ($seg.find('.city-return-toggle').length) {
                    saved[id].returnChecked = !!$seg.find('.city-return-toggle').prop('checked');
                } else if ($seg.find('.city-return-value').length) {
                    saved[id].returnChecked = String($seg.find('.city-return-value').val() || '') === '1';
                }
            }
        });

        // Explicit override from Return switch (most reliable on/off)
        if (opts && opts.forceReturnByCityId) {
            Object.keys(opts.forceReturnByCityId).forEach(function (cid) {
                if (!saved[cid]) {
                    saved[cid] = {
                        start: '', end: '', index: null,
                        returnChecked: false,
                        returnStart: '', returnEnd: '', returnIndex: null
                    };
                }
                saved[cid].returnChecked = !!opts.forceReturnByCityId[cid];
            });
        }

        $wrap.empty();

        var tour = tourDates();
        var datesDisabled = !(tour.start && tour.end);
        // Return plan only useful when itinerary has 2+ cities (leave & come back)
        var allowReturn = cities.length >= 2;

        cities.forEach(function (item, cityPos) {
            var prev = saved[String(item.id)] || {};
            var seedMap = window.__stpLiteStaySeedByCity || {};
            var seed = seedMap[String(item.name || '').toLowerCase()] || {};
            var idx = prev.index || nextSegmentIndex();
            if (!prev.index) segmentIndex = Math.max(segmentIndex, idx);

            var primaryDates = clampStayDates(
                prev.start || seed.start || '',
                prev.end || seed.end || '',
                tour,
                cities.length === 1 && cityPos === 0 && !(seed.start && seed.end)
            );
            var wantReturn = allowReturn && !!(prev.returnChecked || seed.returnChecked);

            $wrap.append(buildStayRowHtml(item, {
                index: idx,
                start: primaryDates.start,
                end: primaryDates.end,
                isReturn: false,
                returnChecked: wantReturn,
                allowReturn: allowReturn,
                datesDisabled: datesDisabled,
                tintIndex: cityPos % 6
            }));

            if (wantReturn) {
                var rIdx = prev.returnIndex || nextSegmentIndex();
                if (!prev.returnIndex) segmentIndex = Math.max(segmentIndex, rIdx);
                var returnDates = clampStayDates(
                    prev.returnStart || seed.returnStart || '',
                    prev.returnEnd || seed.returnEnd || '',
                    tour,
                    false
                );
                $wrap.append(buildStayRowHtml(item, {
                    index: rIdx,
                    start: returnDates.start,
                    end: returnDates.end,
                    isReturn: true,
                    allowReturn: false,
                    datesDisabled: datesDisabled,
                    tintIndex: cityPos % 6
                }));
            }
        });

        syncStayDatesWithTourWindow();
        collectPlans();
        updateServicesGate();
        if (!window.__stpLiteSkipCountrySections) {
            scheduleRenderCountrySections();
        }
    }

    function formatStayRange(start, end) {
        if (!(start && end && typeof moment !== 'undefined')) return 'Dates pending';
        return moment(start, 'YYYY-MM-DD').format('MMM D') + ' → ' + moment(end, 'YYYY-MM-DD').format('MMM D, YYYY');
    }

    /**
     * One service section per city stay (primary and return are separate sections).
     * Ordered by stay-from so itinerary reads chronologically.
     */
    function groupPlansByStay(plans) {
        var sorted = (plans || []).slice().sort(function (a, b) {
            var as = a.start || '';
            var bs = b.start || '';
            if (as && bs && as !== bs) return as < bs ? -1 : 1;
            if (a.isReturn !== b.isReturn) return a.isReturn ? 1 : -1;
            return String(a.index || '').localeCompare(String(b.index || ''));
        });

        return sorted.map(function (p, idx) {
            var country = p.country || 'Unknown';
            return {
                sectionKey: 'stay_' + String(p.index || idx) + (p.isReturn ? '_ret' : ''),
                country: country,
                cityId: p.cityId || '',
                cityName: p.cityName || '',
                currency: p.currency || window.getCurrencyForCountryName(country) || cfg().dmcCurrency || 'SGD',
                dmcId: window.StpLiteGeo.resolveSiblingDmcId(country),
                planIndex: p.index || '',
                isReturn: !!p.isReturn,
                start: p.start || '',
                end: p.end || '',
                complete: !!p.complete,
                tintIndex: cityTintIndex(p.cityId, p.cityName),
                plans: [p]
            };
        });
    }

    function serviceAccordionHtml(section) {
        var safe = String(section.sectionKey || section.country || 'x').replace(/[^a-zA-Z0-9_-]/g, '_');
        var country = section.country || '';
        var currency = section.currency || '';
        var html = '<div class="accordion stp-lite-services-accordion" id="svcAcc_' + esc(safe) + '">';
        SERVICE_META.forEach(function (svc) {
            var bodyId = 'svc_' + safe + '_' + svc.key;
            html +=
                '<div class="accordion-item stp-lite-service-item stp-lite-svc-tint-' + esc(svc.tint || svc.key) + ' border-0 mb-2">' +
                '  <div class="stp-lite-service-header" role="button" data-bs-toggle="collapse" data-bs-target="#' + bodyId + '"' +
                '       aria-expanded="false" aria-controls="' + bodyId + '">' +
                '    <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">' +
                '      <span class="stp-lite-svc-icon-wrap"><i class="' + svc.icon + '"></i></span>' +
                '      <span class="stp-lite-svc-emoji" aria-hidden="true">' + (svc.emoji || '') + '</span>' +
                '      <div class="stp-lite-svc-titles min-w-0">' +
                '        <div class="stp-lite-svc-title">' + esc(svc.label) + '</div>' +
                '        <div class="stp-lite-svc-sub">' + esc(svc.subtitle || '') + '</div>' +
                '      </div>' +
                '      <span class="stp-lite-svc-header-total d-none" data-svc-header-total="' + esc(svc.key) + '"></span>' +
                '      <span class="stp-lite-currency-pill">' + esc(currency) + '</span>' +
                '    </div>' +
                '    <i class="ri-arrow-down-s-line stp-lite-svc-chevron"></i>' +
                '  </div>' +
                '  <div id="' + bodyId + '" class="collapse stp-lite-service-body">' +
                '    <div class="stp-lite-service-panel" data-service="' + svc.key + '"' +
                '         data-country="' + esc(country) + '"' +
                '         data-currency="' + esc(currency) + '"' +
                '         data-dmc-id="' + esc(section.dmcId || '') + '"' +
                '         data-city-id="' + esc(section.cityId || '') + '"' +
                '         data-city-name="' + esc(section.cityName || '') + '"' +
                '         data-plan-index="' + esc(section.planIndex || '') + '"' +
                '         data-is-return="' + (section.isReturn ? '1' : '0') + '"' +
                '         data-stay-start="' + esc(section.start || '') + '"' +
                '         data-stay-end="' + esc(section.end || '') + '">' +
                '      <div data-stp-' + svc.key + '-mount="1"></div>' +
                '    </div>' +
                '  </div>' +
                '</div>';
        });
        html += '</div>';
        return html;
    }

    function renderCountrySections() {
        var host = document.getElementById('countrySectionsHost');
        if (!host) return;

        var plans = getActivePlans().filter(function (p) { return p.country || p.cityName; });
        if (!plans.length) {
            host.innerHTML = '<div class="stp-lite-alert alert alert-light border mb-0">Select cities above to prepare country service sections.</div>';
            window.__stpLiteSectionsKey = '';
            updateServicesGate();
            return;
        }

        // One section per stay (Singapore + Singapore Return = two sections)
        var groups = groupPlansByStay(plans);

        // Skip remount if stays unchanged (stops hotel "Loading…" storm)
        var key = groups.map(function (g) {
            return [g.planIndex, g.cityName, g.country, g.start, g.end, g.isReturn ? 1 : 0, g.dmcId, g.currency].join('|');
        }).join('||');
        if (key && key === window.__stpLiteSectionsKey && host.querySelector('.stp-lite-country-section')) {
            updateServicesGate();
            return;
        }
        window.__stpLiteSectionsKey = key;

        var html = '';
        groups.forEach(function (g) {
            var range = formatStayRange(g.start, g.end);
            var retBadge = g.isReturn ? ' <span class="stp-lite-return-badge">Return</span>' : '';
            var title = g.cityName || g.country || 'Stay';
            var sub = (g.country ? esc(g.country) + ' · ' : '') + esc(range);

            html +=
                '<section class="stp-lite-country-section' + (g.isReturn ? ' is-return-section' : '') + '"' +
                ' data-country="' + esc(g.country) + '"' +
                ' data-city-name="' + esc(g.cityName || '') + '"' +
                ' data-plan-index="' + esc(g.planIndex || '') + '"' +
                ' data-is-return="' + (g.isReturn ? '1' : '0') + '"' +
                ' data-stay-start="' + esc(g.start || '') + '"' +
                ' data-stay-end="' + esc(g.end || '') + '"' +
                ' data-currency="' + esc(g.currency) + '"' +
                ' data-dmc-id="' + esc(g.dmcId) + '"' +
                ' data-tint="' + g.tintIndex + '">' +
                '  <header class="stp-lite-country-header">' +
                '    <div>' +
                '      <h3 class="mb-0">' + esc(title) + retBadge + '</h3>' +
                '      <small>' + sub + '</small>' +
                '    </div>' +
                '    <span class="stp-lite-currency-badge">' + esc(g.currency) + '</span>' +
                '  </header>' +
                '  <div class="stp-lite-country-body">' +
                serviceAccordionHtml(g) +
                '  </div>' +
                '</section>';
        });

        host.innerHTML = html;

        // Lazy-mount each service when its accordion opens (hotels/rooms load only then)
        wireLazyServiceMounts(host);

        if (window.StpLiteGuestCaps && typeof window.StpLiteGuestCaps.refreshGuestDependentUI === 'function') {
            window.StpLiteGuestCaps.refreshGuestDependentUI();
        }

        updateServicesGate();
        document.dispatchEvent(new CustomEvent('stp:country-sections-rendered', { detail: { groups: groups } }));
    }

    var SERVICE_MOUNT_API = {
        hotel: 'StpLiteHotel',
        arrival: 'StpLiteArrival',
        attraction: 'StpLiteAttraction',
        guide: 'StpLiteGuide',
        restaurant: 'StpLiteRestaurant',
        transport: 'StpLiteTransport',
        departure: 'StpLiteDeparture',
        miscellaneous: 'StpLiteMiscellaneous'
    };

    function mountServicePanel(panel) {
        if (!panel || panel.getAttribute('data-mounted') === '1') return;
        var svc = panel.getAttribute('data-service');
        var apiName = SERVICE_MOUNT_API[svc];
        var api = apiName ? window[apiName] : null;
        if (!api || typeof api.mountAll !== 'function') return;
        panel.setAttribute('data-mounted', '1');
        api.mountAll(panel);
    }

    function wireLazyServiceMounts(host) {
        if (!host) return;
        host.querySelectorAll('.stp-lite-service-body').forEach(function (body) {
            if (body.__stpLazyWired) return;
            body.__stpLazyWired = true;
            body.addEventListener('shown.bs.collapse', function () {
                var panel = body.querySelector('.stp-lite-service-panel');
                mountServicePanel(panel);
            });
            // If already open (e.g. Continue opens hotel), mount immediately
            if (body.classList.contains('show')) {
                mountServicePanel(body.querySelector('.stp-lite-service-panel'));
            }
        });
    }

    var __renderSectionsTimer = null;
    function scheduleRenderCountrySections() {
        clearTimeout(__renderSectionsTimer);
        __renderSectionsTimer = setTimeout(renderCountrySections, 80);
    }

    function validateSegmentChange($segment) {
        var start = $segment.find('.start-date').val();
        var end = $segment.find('.end-date').val();
        var curIdx = String($segment.data('index') || '');
        var tour = tourDates();

        if (!tour.start || !tour.end) {
            alert('Set Travel Dates before assigning city stays.');
            $segment.find('.start-date, .end-date').val('');
            collectPlans();
            updateServicesGate();
            scheduleRenderCountrySections();
            return;
        }

        if (start && end && typeof moment !== 'undefined') {
            if (moment(start).isBefore(moment(tour.start), 'day')) {
                alert('Stay from must be on or after the main tour start.');
                $segment.find('.start-date').val('');
            } else if (moment(end).isAfter(moment(tour.end), 'day')) {
                alert('Stay until must be on or before the main tour end.');
                $segment.find('.end-date').val('');
            } else if (moment(end).diff(moment(start), 'days') < 1) {
                alert('Stay until must be after stay from.');
                $segment.find('.end-date').val('');
            } else {
                var overlaps = false;
                $('#segmentsWrapper .segment').each(function () {
                    var $o = $(this);
                    if (String($o.data('index') || '') === curIdx) return;
                    var oStart = $o.find('.start-date').val();
                    var oEnd = $o.find('.end-date').val();
                    if (oStart && oEnd && datesOverlap(start, end, oStart, oEnd)) overlaps = true;
                });
                if (overlaps) {
                    alert('These dates overlap another city. End of one city may equal start of the next, but shared days are not allowed.');
                    $segment.find('.end-date').val('');
                }
            }
        }

        collectPlans();
        updateServicesGate();
        scheduleRenderCountrySections();
    }

    function init() {
        document.addEventListener('stp:cities-changed', function () {
            window.__stpLiteSectionsKey = '';
            syncAutoCityPlanRows();
        });

        document.addEventListener('stp:country-mode-changed', function () {
            // Planner visibility driven by city count in syncAutoCityPlanRows
            if (!window.__stpLiteSkipCountrySections) {
                syncAutoCityPlanRows();
            }
        });

        document.addEventListener('stp:dates-changed', function () {
            // Travel dates set/cleared/updated → fix per-city stays to the new window
            syncStayDatesWithTourWindow({ prefillSingle: true });
            collectPlans();
            updateServicesGate();
            scheduleRenderCountrySections();
        });

        $(document).on('change', '.start-date, .end-date', function () {
            validateSegmentChange($(this).closest('.segment'));
        });

        // Return plan → add/remove second stay row for the same city
        $(document).on('change', '.city-return-toggle', function () {
            var $tog = $(this);
            var cityId = String($tog.attr('data-city-id') || '');
            var on = !!$tog.is(':checked');
            $tog.closest('.segment').find('.city-return-value').val(on ? '1' : '0');
            var force = {};
            if (cityId) force[cityId] = on;
            window.__stpLiteSectionsKey = '';
            syncAutoCityPlanRows({ forceReturnByCityId: force });
        });

        $(document).on('change input', '#start_date, #end_date', function () {
            syncStayDatesWithTourWindow({ prefillSingle: true });
            collectPlans();
            updateServicesGate();
            scheduleRenderCountrySections();
        });

        syncAutoCityPlanRows();
    }

    window.StpLiteCountrySegments = {
        init: init,
        render: renderCountrySections,
        getActivePlans: getActivePlans,
        areStayDatesReady: areStayDatesReady,
        syncAutoCityPlanRows: syncAutoCityPlanRows,
        updateServicesGate: updateServicesGate,
        cityTintIndex: cityTintIndex
    };
})(window, document, window.jQuery);
/* === END country-segments.js === */
