/* === STP LITE: submit.js ===
 * Create tour (store) then post service JSON (store-orders) — backup FormData keys
 * Multi-country: user_country = "A, B"; city = "City [from→to], …"; store-orders per stay
 * === */
(function (window, document) {
    'use strict';

    var SERVICE_CHUNK_FIELDS = [
        { field: 'hotel_data', selector: '.hotel_data_chunk' },
        { field: 'attraction_data', selector: '.attraction_data_chunk' },
        { field: 'restaurant_data', selector: '.restaurant_data_chunk' },
        { field: 'guide_data', selector: '.guide_data_chunk' },
        { field: 'transport_data', selector: '.transport_data_chunk' },
        { field: 'entry_port_data', selector: '.entry_port_data_chunk' },
        { field: 'exit_port_data', selector: '.exit_port_data_chunk' },
        { field: 'miscellaneous_data', selector: '.miscellaneous_data_chunk' }
    ];

    function cfg() {
        return window.STP_LITE_CONFIG || {};
    }

    function val(id) {
        var el = document.getElementById(id);
        return el ? String(el.value || '').trim() : '';
    }

    function checkedRadio(name) {
        var el = document.querySelector('input[name="' + name + '"]:checked');
        return el ? el.value : '';
    }

    function readHiddenJson(id) {
        var el = document.getElementById(id);
        if (!el) return '[]';
        var v = (el.value || '').trim();
        return v || '[]';
    }

    function parseChunkJson(raw) {
        try {
            var rows = JSON.parse(raw || '[]');
            return Array.isArray(rows) ? rows : [];
        } catch (e) {
            return [];
        }
    }

    function sumRows(rows) {
        return (rows || []).reduce(function (sum, r) {
            return sum + (Number(r.totalPrice != null ? r.totalPrice : (r.grand_total || r.price || 0)) || 0);
        }, 0);
    }

    function uniqueCountries(list) {
        var out = [];
        (list || []).forEach(function (c) {
            c = String(c || '').trim();
            if (c && out.indexOf(c) === -1) out.push(c);
        });
        return out;
    }

    /** All selected countries as CSV — drives booking-list multi-country tabs/badge. */
    function resolveUserCountry() {
        if (window.StpLiteCountryMode && typeof window.StpLiteCountryMode.syncUserCountryFromSelection === 'function') {
            window.StpLiteCountryMode.syncUserCountryFromSelection();
        } else if (window.StpLiteCountryMode && typeof window.StpLiteCountryMode.sync === 'function') {
            window.StpLiteCountryMode.sync();
        }

        var countries = [];
        if (window.StpLiteCountryMode && typeof window.StpLiteCountryMode.getSelectedCountries === 'function') {
            countries = window.StpLiteCountryMode.getSelectedCountries() || [];
        }
        if (!countries.length) {
            collectStaySegments().forEach(function (seg) {
                if (seg.country) countries.push(seg.country);
            });
        }
        countries = uniqueCountries(countries);
        if (countries.length) return countries.join(', ');

        var uc = val('user_country');
        if (uc) return uc;
        return '';
    }

    function resolveCityType() {
        return val('city_type_value')
            || val('city_mode_value')
            || checkedRadio('city_mode')
            || checkedRadio('city_type')
            || 'single';
    }

    function resolveTourType() {
        return val('tour_type_value')
            || checkedRadio('tour_type')
            || window.selectedTourType
            || 'FIT';
    }

    /** Backup multi city CSV: "City (Country) [YYYY-MM-DD→YYYY-MM-DD], …" */
    function buildCityCsv() {
        var labels = [];
        collectStaySegments().forEach(function (seg) {
            if (!seg.cityName && !seg.country) return;
            var label = seg.cityName
                ? (seg.country ? seg.cityName + ' (' + seg.country + ')' : seg.cityName)
                : seg.country;
            if (seg.start && seg.end) {
                label += ' [' + seg.start + '→' + seg.end + ']';
            }
            labels.push(label);
        });
        if (labels.length) return labels.join(', ');

        // Fallback: selected tour cities (names, not ids)
        if (window.StpLiteTourDetails && typeof window.StpLiteTourDetails.getTourCityItems === 'function') {
            return (window.StpLiteTourDetails.getTourCityItems() || []).map(function (item) {
                return item.country ? (item.name + ' (' + item.country + ')') : item.name;
            }).filter(Boolean).join(', ');
        }
        return '';
    }

    function collectStaySegments() {
        var segs = [];
        document.querySelectorAll('.stp-lite-country-section').forEach(function (section, idx) {
            var cityName = String(section.getAttribute('data-city-name') || '').trim();
            var country = String(section.getAttribute('data-country') || '').trim();
            var start = String(section.getAttribute('data-stay-start') || '').trim();
            var end = String(section.getAttribute('data-stay-end') || '').trim();
            var currency = String(section.getAttribute('data-currency') || cfg().dmcCurrency || 'SGD').trim().toUpperCase();
            var cityId = '';
            var panel = section.querySelector('.stp-lite-service-panel[data-city-id]');
            if (panel) cityId = String(panel.getAttribute('data-city-id') || '').trim();

            var services = {};
            var hasAny = false;
            SERVICE_CHUNK_FIELDS.forEach(function (spec) {
                var rows = [];
                section.querySelectorAll(spec.selector).forEach(function (el) {
                    rows = rows.concat(parseChunkJson(el.value));
                });
                services[spec.field] = rows;
                if (rows.length) hasAny = true;
            });

            segs.push({
                key: idx,
                cityId: cityId,
                cityName: cityName,
                country: country,
                start: start,
                end: end,
                currency: currency || (cfg().dmcCurrency || 'SGD'),
                services: services,
                hasAny: hasAny
            });
        });

        // Fallback when sections not mounted yet: plans from planner
        if (!segs.length && window.StpLiteCountrySegments && typeof window.StpLiteCountrySegments.getActivePlans === 'function') {
            (window.StpLiteCountrySegments.getActivePlans() || []).forEach(function (p, i) {
                segs.push({
                    key: i,
                    cityId: p.cityId || '',
                    cityName: p.cityName || '',
                    country: p.country || '',
                    start: p.start || '',
                    end: p.end || '',
                    currency: String(p.currency || cfg().dmcCurrency || 'SGD').toUpperCase(),
                    services: {},
                    hasAny: false
                });
            });
        }
        return segs;
    }

    function collectTourFormData() {
        var caps = window.StpLiteGuestCaps && window.StpLiteGuestCaps.getCaps
            ? window.StpLiteGuestCaps.getCaps()
            : { adults: 1, male: 1, female: 0, children: 0, infants: 0, childAges: [] };
        var mainGuest = window.StpLiteGuests ? window.StpLiteGuests.collectMainGuest() : {};
        var additional = window.StpLiteGuests ? window.StpLiteGuests.collectAdditionalGuests() : [];
        var tourType = resolveTourType();
        var cityType = resolveCityType();
        var userCountry = resolveUserCountry();
        var cityCsv = buildCityCsv();
        var fd = new FormData();
        fd.append('_token', cfg().csrfToken || '');
        fd.append('adults', String(caps.adults || 1));
        fd.append('male', String(caps.male || 0));
        fd.append('female', String(caps.female || 0));
        fd.append('children', String(caps.children || 0));
        fd.append('infants', String(caps.infants || 0));
        fd.append('child_ages', JSON.stringify(caps.childAges || []));
        fd.append('agent_id', val('agent_id') || val('agent') || '0');
        fd.append('reference_number', val('reference_number'));
        fd.append('enquiry_id', val('enquiry_id') || '0');
        fd.append('mainguest', JSON.stringify(mainGuest));
        fd.append('additionalguest', JSON.stringify(additional));
        fd.append('tour_type', tourType);
        fd.append('city_type', cityType);
        fd.append('city_mode', cityType);
        fd.append('tour_booking_from', 'manual_single_form');
        fd.append('dmc_id', String(cfg().dmcId || val('dmc_id') || ''));
        fd.append('start_date', val('start_date'));
        fd.append('end_date', val('end_date'));
        // Backup: user_country CSV → tours.destination (multi-country badge/tabs)
        fd.append('user_country', userCountry);
        fd.append('city', cityCsv);
        fd.append('discount_price', val('discount_price') || '0');
        if (String(tourType).toUpperCase() === 'GROUP') {
            fd.append('foc_size', val('foc_size') || '0');
            fd.append('group_size', val('group_size') || '0');
            fd.append('paying_pax', val('paying_pax') || '0');
            fd.append('discount', val('discount') || '0');
        }
        return fd;
    }

    function enrichServiceRowsWithCustomer(jsonStr) {
        var customer = window.StpLiteGuests
            ? window.StpLiteGuests.getCustomerDataForServices()
            : {};
        try {
            var rows = JSON.parse(jsonStr || '[]');
            if (!Array.isArray(rows)) return jsonStr || '[]';
            rows = rows.map(function (row) {
                var r = Object.assign({}, row);
                if (!r.fullName && customer.fullName) r.fullName = customer.fullName;
                if (!r.email && customer.email) r.email = customer.email;
                if (!r.phone && customer.phone) r.phone = customer.phone;
                if (!r.countryCode && customer.countryCode) r.countryCode = customer.countryCode;
                if (r.address1 == null && customer.address1) r.address1 = customer.address1;
                if (r.address2 == null) r.address2 = customer.address2;
                if (r.state == null) r.state = customer.state;
                if (!r.zip && customer.zip) r.zip = customer.zip;
                if (r.specialRequests == null) r.specialRequests = customer.specialRequests;
                if (!r.bookingType) r.bookingType = 'enquiry';
                if (!r.userInfo) {
                    r.userInfo = {
                        fullName: r.fullName || customer.fullName || '',
                        email: r.email || customer.email || '',
                        phone: r.phone || customer.phone || '',
                        countryCode: r.countryCode || customer.countryCode || '',
                        address1: r.address1 || customer.address1 || '',
                        address2: r.address2 != null ? r.address2 : customer.address2,
                        state: r.state != null ? r.state : customer.state,
                        zip: r.zip || customer.zip || '',
                        specialRequests: r.specialRequests != null ? r.specialRequests : customer.specialRequests
                    };
                }
                return r;
            });
            return JSON.stringify(rows);
        } catch (e) {
            return jsonStr || '[]';
        }
    }

    async function createTour(fd) {
        var url = (cfg().routes && cfg().routes.store) || '';
        var resp = await fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: fd
        });
        var data = await resp.json().catch(function () { return {}; });
        if (!resp.ok || !data.success) {
            throw new Error((data && (data.error || data.message)) || 'Failed to create tour');
        }
        var tourId = parseInt(data.tour_id != null ? data.tour_id : (data.tour && data.tour.tour_id), 10);
        if (!tourId) throw new Error('Tour saved but no tour_id returned');
        data.tour_id = tourId;
        return data;
    }

    async function postOneSegmentOrders(tourId, agentId, seg) {
        var url = (cfg().routes && cfg().routes.storeOrders) || '';
        if (!url) throw new Error('store-orders route missing');
        var fd = new FormData();
        fd.append('_token', cfg().csrfToken || '');
        fd.append('tour_id', String(tourId));
        fd.append('agent_id', String(agentId || 0));
        var total = 0;
        SERVICE_CHUNK_FIELDS.forEach(function (spec) {
            var rows = (seg.services && seg.services[spec.field]) || [];
            total += sumRows(rows);
            fd.append(spec.field, enrichServiceRowsWithCustomer(JSON.stringify(rows)));
        });
        fd.append('total_price', String(total));
        // Backup: one country/city/currency per stay segment (never full destination CSV)
        fd.append('country', seg.country || '');
        fd.append('city', seg.cityName || '');
        fd.append('currency', seg.currency || cfg().dmcCurrency || 'SGD');
        var resp = await fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: fd
        });
        var data = await resp.json().catch(function () { return {}; });
        if (!resp.ok || (data.success === false)) {
            throw new Error((data && (data.error || data.message)) || ('Failed to store services for ' + (seg.cityName || seg.country || 'segment')));
        }
        return data;
    }

    async function postServiceOrders(tourId, agentId) {
        var segments = collectStaySegments().filter(function (s) { return s.hasAny; });
        if (!segments.length) {
            // Fallback: single merged POST (legacy single-city / no section DOM)
            var url = (cfg().routes && cfg().routes.storeOrders) || '';
            if (!url) throw new Error('store-orders route missing');
            var fd = new FormData();
            fd.append('_token', cfg().csrfToken || '');
            fd.append('tour_id', String(tourId));
            fd.append('agent_id', String(agentId || 0));
            var total = 0;
            SERVICE_CHUNK_FIELDS.forEach(function (spec) {
                var rows = parseChunkJson(readHiddenJson(spec.field));
                total += sumRows(rows);
                fd.append(spec.field, enrichServiceRowsWithCustomer(JSON.stringify(rows)));
            });
            if (!total && SERVICE_CHUNK_FIELDS.every(function (spec) {
                return !parseChunkJson(readHiddenJson(spec.field)).length;
            })) {
                throw new Error('Please add at least one service before saving.');
            }
            fd.append('total_price', String(total));
            var countries = uniqueCountries((resolveUserCountry() || '').split(','));
            fd.append('country', countries[0] || '');
            var cityCsv = buildCityCsv();
            var firstCity = cityCsv.split(',')[0] || '';
            firstCity = firstCity.replace(/\s*\[[^\]]*\]\s*$/, '').replace(/\s*\([^)]*\)\s*$/, '').trim();
            fd.append('city', firstCity);
            fd.append('currency', cfg().dmcCurrency || 'SGD');
            var resp = await fetch(url, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: fd
            });
            var data = await resp.json().catch(function () { return {}; });
            if (!resp.ok || (data.success === false)) {
                throw new Error((data && (data.error || data.message)) || 'Failed to store service orders');
            }
            return data;
        }

        var allCreated = [];
        var last = null;
        for (var i = 0; i < segments.length; i++) {
            last = await postOneSegmentOrders(tourId, agentId, segments[i]);
            if (last && Array.isArray(last.created_orders)) {
                allCreated = allCreated.concat(last.created_orders);
            }
        }
        if (!last) throw new Error('No services were stored.');
        last.created_orders = allCreated;
        return last;
    }

    function redirectToThankYou(ordersResult, tour) {
        var url = (ordersResult && ordersResult.redirect_url)
            || (cfg().routes && cfg().routes.thankYou)
            || '';
        if (!url) {
            alert('Tour package saved (#' + ((tour && tour.display_id) || (ordersResult && ordersResult.tour_id) || '') + ').');
            return;
        }
        var tourDetails = (ordersResult && ordersResult.tour_details) || {
            tour_id: tour && tour.tour_id,
            display_id: (tour && tour.display_id) || (ordersResult && ordersResult.display_id) || '',
            destination: resolveUserCountry() || '',
            city: buildCityCsv(),
            check_in_date: val('start_date'),
            check_out_date: val('end_date'),
            total_guests: 0,
            services_by_date: {}
        };
        if (!tourDetails.redirect_url) tourDetails.redirect_url = url;
        if (!tourDetails.destination) tourDetails.destination = resolveUserCountry() || '';
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.style.display = 'none';
        function addHidden(n, v) {
            var inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = n;
            inp.value = v;
            form.appendChild(inp);
        }
        addHidden('_token', cfg().csrfToken || '');
        addHidden('tour_details', JSON.stringify(tourDetails));
        addHidden('created_orders', JSON.stringify((ordersResult && ordersResult.created_orders) || []));
        document.body.appendChild(form);
        form.submit();
    }

    function routeWithTourId(template, tourId) {
        return String(template || '').replace('__ID__', String(tourId));
    }

    function resolveTourId() {
        var fromCfg = cfg().tourId || (cfg().edit && cfg().edit.tourId);
        var fromInput = val('tour_id');
        return parseInt(fromCfg || fromInput || 0, 10) || 0;
    }

    function isEditMode() {
        return (cfg().mode === 'edit') || !!resolveTourId();
    }

    async function postJsonForm(url, fd) {
        var resp = await fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: fd
        });
        var data = await resp.json().catch(function () { return {}; });
        if (!resp.ok || data.success === false) {
            var msg = (data && (data.error || data.message)) || ('Request failed (' + resp.status + ')');
            if (data && data.errors && typeof data.errors === 'object') {
                var first = Object.keys(data.errors)[0];
                if (first && data.errors[first] && data.errors[first][0]) {
                    msg = data.errors[first][0];
                }
            }
            throw new Error(msg);
        }
        return data;
    }

    function syncServiceHiddensBeforeSave() {
        if (window.StpLiteHotel && typeof window.StpLiteHotel.syncHotelDataHidden === 'function') {
            window.StpLiteHotel.syncHotelDataHidden();
        }
        // Refresh form-level hiddens from per-section chunks
        SERVICE_CHUNK_FIELDS.forEach(function (spec) {
            var merged = [];
            document.querySelectorAll(spec.selector).forEach(function (el) {
                merged = merged.concat(parseChunkJson(el.value));
            });
            var hidden = document.getElementById(spec.field);
            if (hidden && merged.length) hidden.value = JSON.stringify(merged);
        });
    }

    async function updateTourHeader(tourId, country, cityCsv) {
        var url = routeWithTourId((cfg().routes && cfg().routes.updateInfo) || '', tourId);
        if (!url) throw new Error('update-info route missing');
        var caps = window.StpLiteGuestCaps && window.StpLiteGuestCaps.getCaps
            ? window.StpLiteGuestCaps.getCaps()
            : { adults: 1, male: 1, female: 0, children: 0, infants: 0, childAges: [] };
        var fd = new FormData();
        fd.append('_token', cfg().csrfToken || '');
        fd.append('user_country', country);
        fd.append('start_date', val('start_date'));
        fd.append('end_date', val('end_date'));
        fd.append('adults', String(caps.adults || 1));
        fd.append('male', String(caps.male || 0));
        fd.append('female', String(caps.female || 0));
        fd.append('children', String(caps.children || 0));
        fd.append('infants', String(caps.infants || 0));
        fd.append('child_ages', JSON.stringify(caps.childAges || []));
        fd.append('agent_id', val('agent_id') || val('agent') || '0');
        fd.append('tour_type', resolveTourType());
        fd.append('delete_affected_services', '1');
        if (String(resolveTourType()).toUpperCase() === 'GROUP') {
            fd.append('foc_size', val('foc_size') || '0');
            fd.append('discount', val('discount') || '0');
            fd.append('discount_price', val('discount_price') || '0');
        }
        return postJsonForm(url, fd);
    }

    async function updateCityPlans(tourId, cityCsv) {
        var url = routeWithTourId((cfg().routes && cfg().routes.updateCityPlans) || '', tourId);
        if (!url) throw new Error('update-city-plans route missing');
        var fd = new FormData();
        fd.append('_token', cfg().csrfToken || '');
        fd.append('city_type', resolveCityType());
        fd.append('city', cityCsv || '');
        return postJsonForm(url, fd);
    }

    async function updateGuests(tourId) {
        var url = routeWithTourId((cfg().routes && cfg().routes.updateGuests) || '', tourId);
        if (!url) throw new Error('update-guests route missing');
        var mainGuest = window.StpLiteGuests ? window.StpLiteGuests.collectMainGuest() : {};
        var additional = window.StpLiteGuests ? window.StpLiteGuests.collectAdditionalGuests() : [];
        var fd = new FormData();
        fd.append('_token', cfg().csrfToken || '');
        fd.append('mainguest', JSON.stringify(mainGuest));
        fd.append('additionalguest', JSON.stringify(additional));
        return postJsonForm(url, fd);
    }

    async function clearAllServices(tourId) {
        var url = routeWithTourId((cfg().routes && cfg().routes.clearServices) || '', tourId);
        if (!url) throw new Error('clear-services route missing');
        var fd = new FormData();
        fd.append('_token', cfg().csrfToken || '');
        return postJsonForm(url, fd);
    }

    function validateBeforeSave() {
        if (!val('start_date') || !val('end_date')) {
            throw new Error('Set tour start and end dates first.');
        }
        if (!(val('agent_id') || val('agent'))) {
            throw new Error('Select an agency contact (agent) before saving.');
        }
        var country = resolveUserCountry();
        if (!country) {
            throw new Error('Please select a city so the country can be saved.');
        }
        var cityCsv = buildCityCsv();
        if (!cityCsv) {
            throw new Error('Please select a city.');
        }
        var segmentsWithServices = collectStaySegments().filter(function (s) { return s.hasAny; });
        var anyMerged = SERVICE_CHUNK_FIELDS.some(function (spec) {
            return parseChunkJson(readHiddenJson(spec.field)).length > 0;
        });
        if (!segmentsWithServices.length && !anyMerged) {
            throw new Error('Please add at least one service before saving.');
        }
        return { country: country, cityCsv: cityCsv };
    }

    async function updateTourPackage() {
        var btn = document.getElementById('stpLiteSaveTourBtn');
        var hint = document.getElementById('stpLiteSaveHint');
        if (btn) btn.disabled = true;
        if (hint) hint.textContent = 'Updating tour…';
        try {
            var tourId = resolveTourId();
            if (!tourId) throw new Error('Missing tour id for edit.');
            syncServiceHiddensBeforeSave();
            var v = validateBeforeSave();

            await updateTourHeader(tourId, v.country, v.cityCsv);
            if (hint) hint.textContent = 'Saving city plans…';
            await updateCityPlans(tourId, v.cityCsv);
            if (hint) hint.textContent = 'Saving guests…';
            await updateGuests(tourId);
            if (hint) hint.textContent = 'Replacing services…';
            await clearAllServices(tourId);
            if (hint) hint.textContent = 'Storing services…';
            syncServiceHiddensBeforeSave();
            var ordersResult = await postServiceOrders(tourId, val('agent_id') || val('agent'));
            if (hint) hint.textContent = 'Updated — reloading…';
            // Keep the same encrypted edit URL the system uses
            window.location.href = window.location.pathname + (window.location.search || '');
            return ordersResult;
        } catch (err) {
            if (hint) hint.textContent = 'Update failed.';
            alert(err && err.message ? err.message : 'Update failed.');
            if (btn) btn.disabled = false;
        }
    }

    async function saveTourPackage() {
        if (isEditMode()) {
            return updateTourPackage();
        }
        var btn = document.getElementById('stpLiteSaveTourBtn');
        var hint = document.getElementById('stpLiteSaveHint');
        if (btn) btn.disabled = true;
        if (hint) hint.textContent = 'Saving tour…';
        try {
            var v = validateBeforeSave();
            var fd = collectTourFormData();
            fd.set('user_country', v.country);
            fd.set('city', v.cityCsv);
            fd.set('city_type', resolveCityType());

            var tour = await createTour(fd);
            if (hint) hint.textContent = 'Tour #' + (tour.display_id || tour.tour_id) + ' created — storing services…';
            var ordersResult = await postServiceOrders(tour.tour_id, val('agent_id') || val('agent'));
            if (hint) hint.textContent = 'Saved — redirecting…';
            redirectToThankYou(ordersResult, tour);
        } catch (err) {
            if (hint) hint.textContent = 'Save failed.';
            alert(err && err.message ? err.message : 'Save failed.');
            if (btn) btn.disabled = false;
        }
    }

    function init() {
        var btn = document.getElementById('stpLiteSaveTourBtn');
        if (!btn) return;
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            saveTourPackage();
        });
    }

    window.StpLiteSubmit = {
        init: init,
        saveTourPackage: saveTourPackage,
        updateTourPackage: updateTourPackage,
        collectTourFormData: collectTourFormData,
        resolveUserCountry: resolveUserCountry,
        buildCityCsv: buildCityCsv
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})(window, document);
/* === END submit.js === */
