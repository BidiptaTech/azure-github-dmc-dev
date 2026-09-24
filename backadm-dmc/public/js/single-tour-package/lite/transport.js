/* === STP LITE: transport.js ===
 * Other Transport — Local Transfer / Point-to-Point / Hourly
 * Payload: transport_data (local_transport | travel_point | travel_hourly)
 * === */
(function (window, document) {
    'use strict';

    var S = function () { return window.StpLiteTransportShared || {}; };

    function shellHtml(stay) {
        var T = S();
        var cur = stay.currency || 'SGD';
        var zone = T.zoneOn && T.zoneOn();
        var modeName = 'transport_mode_' + T.esc(stay.planIndex || 'x');
        return (
            '<div class="stp-lite-svc stp-lite-transport" data-currency="' + T.esc(cur) + '">' +
            '  <div class="row g-2 mb-2 align-items-end">' +
            '    <div class="col-md-2"><label class="stp-lite-label">City</label>' +
            '      <div class="stp-lite-city-static">' + T.esc(T.cityLabel(stay)) + '</div></div>' +
            '    <div class="col-md-4"><label class="stp-lite-label">Mode</label>' +
            '      <div class="d-flex flex-wrap gap-2 pt-1">' +
            '        <label class="form-check form-check-inline mb-0"><input class="form-check-input transport-mode" type="radio" name="' + modeName + '" value="point_to_point" checked> Point to Point</label>' +
            '        <label class="form-check form-check-inline mb-0"><input class="form-check-input transport-mode" type="radio" name="' + modeName + '" value="hourly"> Hourly</label>' +
            (zone ? '<label class="form-check form-check-inline mb-0"><input class="form-check-input transport-mode" type="radio" name="' + modeName + '" value="local"> Local Transfer</label>' : '') +
            '      </div></div>' +
            '    <div class="col-md-2"><label class="stp-lite-label">Date</label>' +
            '      <input type="date" class="form-control form-control-sm transport-date" value="' + T.esc(stay.start || '') + '"></div>' +
            '    <div class="col-md-2 transport-hours-wrap d-none"><label class="stp-lite-label">Hours</label>' +
            '      <select class="form-select form-select-sm transport-hours">' +
            [1,2,3,4,5,6,7,8,9,10,11,12].map(function (h) { return '<option value="' + h + '">' + h + 'h</option>'; }).join('') +
            '</select></div>' +
            '    <div class="col-md-2"><label class="stp-lite-label">Pickup time</label>' +
            T.ampmTimeHtml('transport', '') +
            '</div>' +
            '  </div>' +
            '  <div class="row g-2 mb-2 transport-zone-row d-none">' +
            '    <div class="col-md-4"><label class="stp-lite-label">Pickup (zone / location)</label>' +
            '      <select class="form-select form-select-sm transport-from-zone"><option value="">Select pickup</option></select></div>' +
            '    <div class="col-md-4"><label class="stp-lite-label">Dropoff (zone / location)</label>' +
            '      <select class="form-select form-select-sm transport-to-zone"><option value="">Select dropoff</option></select></div>' +
            '    <div class="col-md-4 d-flex align-items-end">' +
            '      <button type="button" class="btn btn-sm btn-outline-primary transport-search-btn w-100">' +
            '        <i class="ri-search-line me-1"></i>Search Vehicles</button></div>' +
            '  </div>' +
            '  <div class="row g-2 mb-2 transport-maps-row">' +
            '    <div class="col-md-4"><label class="stp-lite-label">Pickup</label>' +
            '      <input type="text" class="form-control form-control-sm google-maps-autocomplete transport-pickup-text" placeholder="Search pickup on map…" autocomplete="off"></div>' +
            '    <div class="col-md-4 transport-dropoff-wrap"><label class="stp-lite-label">Dropoff</label>' +
            '      <input type="text" class="form-control form-control-sm google-maps-autocomplete transport-dropoff-text" placeholder="Search dropoff on map…" autocomplete="off"></div>' +
            '    <div class="col-md-4 d-flex align-items-end transport-maps-search-wrap">' +
            '      <button type="button" class="btn btn-sm btn-outline-primary transport-search-btn-maps w-100">' +
            '        <i class="ri-search-line me-1"></i>Search Vehicles</button></div>' +
            '  </div>' +
            '  <div class="row g-2 mb-2">' +
            '    <div class="col-md-3"><label class="stp-lite-label">Vehicle</label>' +
            '      <select class="form-select form-select-sm transport-vehicle" disabled><option value="">Search first</option></select></div>' +
            '    <div class="col-md-2"><label class="stp-lite-label">Service</label>' +
            '      <select class="form-select form-select-sm transport-service-type">' +
            '        <option value="private">Private</option><option value="shared">Shared</option></select></div>' +
            '    <div class="col-md-2"><label class="stp-lite-label">Adults</label>' +
            '      <input type="number" min="1" class="form-control form-control-sm stp-lite-int transport-adults" data-guest-cap="adults" value="1"></div>' +
            '    <div class="col-md-2" data-guest-child-ui><label class="stp-lite-label">Children</label>' +
            '      <input type="number" min="0" class="form-control form-control-sm stp-lite-int transport-children" data-guest-cap="children" value="0"></div>' +
            '    <div class="col-md-3 transport-custom-price-wrap' + (zone ? ' d-none' : '') + '"><label class="stp-lite-label">Car cost</label>' +
            '      <input type="number" min="0" step="0.01" class="form-control form-control-sm transport-custom-price" placeholder="Enter car price" value=""></div>' +
            '  </div>' +
            '  <div class="row g-2 mb-2">' +
            '    <div class="col-12 d-flex align-items-end gap-2 flex-wrap">' +
            '      <button type="button" class="btn btn-sm stp-lite-get-price-btn transport-get-price-btn" disabled>' +
            '        <i class="ri-price-tag-3-line me-1"></i>Get Price</button>' +
            '      <button type="button" class="btn btn-sm btn-primary transport-add-btn" disabled>' +
            '        <i class="ri-add-line me-1"></i>Add</button></div>' +
            '  </div>' +
            T.pricePanelHtml(cur, 'transport') +
            '  <div class="stp-lite-svc-added mt-2" data-transport-added-list></div>' +
            '  <input type="hidden" class="transport_data_chunk" value="[]">' +
            '</div>'
        );
    }

    function modeOf(root) {
        var checked = root.querySelector('.transport-mode:checked');
        return checked ? checked.value : 'point_to_point';
    }

    function syncModeUi(root) {
        var mode = modeOf(root);
        var zoneRow = root.querySelector('.transport-zone-row');
        var mapsRow = root.querySelector('.transport-maps-row');
        var hoursWrap = root.querySelector('.transport-hours-wrap');
        var customWrap = root.querySelector('.transport-custom-price-wrap');
        var dropoffWrap = root.querySelector('.transport-dropoff-wrap');
        var dropoff = root.querySelector('.transport-dropoff-text');
        var serviceType = root.querySelector('.transport-service-type');
        var zone = S().zoneOn && S().zoneOn();
        var isLocal = mode === 'local' && zone;
        var isHourly = mode === 'hourly';
        var mapsMode = !zone;

        if (zoneRow) zoneRow.classList.toggle('d-none', !isLocal);
        if (mapsRow) mapsRow.classList.toggle('d-none', isLocal);
        if (hoursWrap) hoursWrap.classList.toggle('d-none', !isHourly);
        // Hourly: package prices from vehicle hourly_price_1..12 (no manual car cost).
        // Zone OFF: car cost for PTP. Zone ON: car cost only for PTP.
        if (customWrap) {
            if (isHourly) customWrap.classList.add('d-none');
            else if (mapsMode) customWrap.classList.remove('d-none');
            else customWrap.classList.toggle('d-none', mode !== 'point_to_point');
        }
        if (dropoffWrap) dropoffWrap.classList.toggle('d-none', isHourly);
        if (dropoff) {
            dropoff.disabled = isHourly;
            if (isHourly) dropoff.value = '';
        }
        // Hourly = Private only. PTP with zone ON = Private only.
        if (serviceType) {
            var sharedOpt = serviceType.querySelector('option[value="shared"]');
            var privateOnly = isHourly || (mode === 'point_to_point' && !mapsMode);
            if (sharedOpt) sharedOpt.hidden = !!privateOnly;
            if (privateOnly) {
                serviceType.value = 'private';
                serviceType.disabled = true;
            } else {
                serviceType.disabled = false;
            }
        }
    }

    function fillLocationSelect(select, placeholder, groups) {
        if (!select) return;
        select.innerHTML = '<option value="">' + placeholder + '</option>';
        (groups || []).forEach(function (g) {
            if (!g.items || !g.items.length) return;
            var og = document.createElement('optgroup');
            og.label = g.label;
            g.items.forEach(function (item) {
                var opt = document.createElement('option');
                opt.value = item.value;
                opt.textContent = item.label;
                opt.dataset.zoneType = item.zoneType || '';
                opt.dataset.zoneId = item.zoneId || item.value;
                opt.dataset.label = item.label;
                opt.dataset.lat = item.lat || '';
                opt.dataset.lng = item.lng || '';
                og.appendChild(opt);
            });
            select.appendChild(og);
        });
        select.disabled = false;
    }

    function loadZones(root, stay) {
        var T = S();
        if (!T.zoneOn || !T.zoneOn()) return Promise.resolve();
        return Promise.all([
            T.fetchZones(stay.cityName, stay.country),
            T.fetchHotels(stay.cityName, stay.country),
            T.fetchAttractions(stay.cityName, stay.country),
            T.fetchRestaurants(stay.cityName, stay.country)
        ]).then(function (results) {
            var zones = (results[0] && results[0].zones) || [];
            var hotels = (results[1] && results[1].hotels) || [];
            var attractions = (results[2] && results[2].attractions) || [];
            var restaurants = (results[3] && results[3].restaurants) || [];

            function mapZones() {
                return zones.map(function (z) {
                    return {
                        value: z.zone_id,
                        label: (z.zone_name || z.location || 'Zone') + (z.zone_type ? ' (' + z.zone_type + ')' : ''),
                        zoneType: z.zone_type || 'Zone',
                        zoneId: z.zone_id,
                        lat: z.latitude || '',
                        lng: z.longitude || ''
                    };
                });
            }
            function mapHotels() {
                return hotels.map(function (h) {
                    return {
                        value: h.hotel_unique_id || h.id,
                        label: h.name || 'Hotel',
                        zoneType: 'Hotel',
                        zoneId: h.hotel_unique_id || h.id,
                        lat: h.latitude || '',
                        lng: h.longitude || ''
                    };
                });
            }
            function mapAttractions() {
                return attractions.map(function (a) {
                    return {
                        value: a.attraction_id || a.id,
                        label: a.name || 'Attraction',
                        zoneType: 'Attraction',
                        zoneId: a.attraction_id || a.id,
                        lat: a.latitude || '',
                        lng: a.longitude || ''
                    };
                });
            }
            function mapRestaurants() {
                return restaurants.map(function (r) {
                    return {
                        value: r.restaurant_id || r.id,
                        label: r.name || 'Restaurant',
                        zoneType: 'Restaurant',
                        zoneId: r.restaurant_id || r.id,
                        lat: r.latitude || '',
                        lng: r.longitude || ''
                    };
                });
            }

            var groups = [
                { label: 'Zones', items: mapZones() },
                { label: 'Hotels', items: mapHotels() },
                { label: 'Attractions', items: mapAttractions() },
                { label: 'Restaurants', items: mapRestaurants() }
            ];
            fillLocationSelect(root.querySelector('.transport-from-zone'), 'Select pickup', groups);
            fillLocationSelect(root.querySelector('.transport-to-zone'), 'Select dropoff', groups);
        }).catch(function () {
            var from = root.querySelector('.transport-from-zone');
            var to = root.querySelector('.transport-to-zone');
            if (from) { from.innerHTML = '<option value="">Failed to load</option>'; from.disabled = false; }
            if (to) { to.innerHTML = '<option value="">Failed to load</option>'; to.disabled = false; }
        });
    }

    function readChunk(root) {
        try {
            var v = JSON.parse((root.querySelector('.transport_data_chunk') || {}).value || '[]');
            return Array.isArray(v) ? v : [];
        } catch (e) { return []; }
    }

    function writeChunk(root, rows) {
        var el = root.querySelector('.transport_data_chunk');
        if (el) el.value = JSON.stringify(rows || []);
        S().syncHiddenJson('transport_data', '.transport_data_chunk');
        S().updateServiceHeaderTotal(root, 'transport');
    }

    function invalidate(root) {
        root.__lastPrice = null;
        var panel = root.querySelector('[data-transport-price-panel]');
        if (panel) panel.classList.add('d-none');
        var add = root.querySelector('.transport-add-btn');
        if (add) add.disabled = true;
    }

    function searchVehicles(root, stay, preferredVehicleId) {
        var T = S();
        var vehicle = root.querySelector('.transport-vehicle');
        var btns = root.querySelectorAll('.transport-search-btn, .transport-search-btn-maps');
        var getBtn = root.querySelector('.transport-get-price-btn');
        var mode = modeOf(root);
        var keepId = preferredVehicleId != null ? preferredVehicleId : root.__preferredVehicleId;
        var keepName = root.__preferredVehicleName || '';
        invalidate(root);
        btns.forEach(function (btn) { btn.disabled = true; });
        if (vehicle) {
            vehicle.innerHTML = '<option value="">Searching…</option>';
            vehicle.disabled = true;
        }

        var promise;
        if (mode === 'local' && T.zoneOn && T.zoneOn()) {
            var from = root.querySelector('.transport-from-zone');
            var to = root.querySelector('.transport-to-zone');
            var fOpt = from && from.options[from.selectedIndex];
            var tOpt = to && to.options[to.selectedIndex];
            if (!from || !from.value || !to || !to.value) {
                alert('Select pickup and dropoff locations.');
                btns.forEach(function (btn) { btn.disabled = false; });
                return Promise.resolve();
            }
            var q = T.inv(stay.cityName, stay.country);
            promise = T.fetchVehiclesByZones({
                from_zone_id: fOpt.dataset.zoneId || from.value,
                to_zone_id: tOpt.dataset.zoneId || to.value,
                from_zone_type: fOpt.dataset.zoneType || '',
                to_zone_type: tOpt.dataset.zoneType || '',
                zone_status: 1,
                city: q.city,
                country: q.country,
                dmc_id: q.dmc_id
            });
        } else {
            var pickupText = (root.querySelector('.transport-pickup-text') || {}).value || '';
            if (!pickupText.trim()) {
                alert('Enter a pickup location.');
                btns.forEach(function (btn) { btn.disabled = false; });
                return Promise.resolve();
            }
            if (mode === 'point_to_point') {
                var dropText = (root.querySelector('.transport-dropoff-text') || {}).value || '';
                if (!dropText.trim()) {
                    alert('Enter a dropoff location.');
                    btns.forEach(function (btn) { btn.disabled = false; });
                    return Promise.resolve();
                }
            }
            promise = T.fetchVehiclesByCity(stay.cityName, stay.country, true);
        }

        return promise.then(function (res) {
            var list = (res && res.vehicles) || [];
            if (mode === 'point_to_point') {
                list = list.filter(function (v) {
                    var s = parseInt(v.sharable, 10);
                    return s === 1 || s === 3 || isNaN(s);
                });
            }
            T.populateVehicles(vehicle, list);
            if (!list.length && vehicle) {
                vehicle.innerHTML = '<option value="">No vehicles found</option>';
                vehicle.disabled = false;
            }
            if (keepId) T.selectVehicleValue(vehicle, keepId, keepName);
            if (getBtn) getBtn.disabled = !(list.length || keepId);
        }).catch(function () {
            if (vehicle) {
                vehicle.innerHTML = '<option value="">Error loading vehicles</option>';
                vehicle.disabled = false;
            }
        }).finally(function () {
            btns.forEach(function (btn) { btn.disabled = false; });
        });
    }

    function getPrice(root) {
        var T = S();
        var mode = modeOf(root);
        var vehicle = root.querySelector('.transport-vehicle');
        var typeEl = root.querySelector('.transport-service-type');
        var adultsEl = root.querySelector('.transport-adults');
        var childrenEl = root.querySelector('.transport-children');
        var hoursEl = root.querySelector('.transport-hours');
        var customEl = root.querySelector('.transport-custom-price');
        var opt = vehicle && vehicle.options[vehicle.selectedIndex];
        if (!opt || !opt.value) {
            alert('Select a vehicle first.');
            return;
        }

        var priced;
        var mapsMode = !T.zoneOn || !T.zoneOn();
        var svc = typeEl ? typeEl.value : 'private';

        // Hourly: always Private + vehicle hourly_price_1..12 package (not base / not car cost)
        if (mode === 'hourly') {
            if (typeEl) {
                typeEl.value = 'private';
                typeEl.disabled = true;
            }
            priced = T.calcHourlyPrice(
                opt,
                'private',
                adultsEl ? adultsEl.value : 1,
                childrenEl ? childrenEl.value : 0,
                0,
                hoursEl ? hoursEl.value : 1
            );
            priced.mode = 'hourly';
            if (!(priced && priced.total > 0)) {
                alert('No hourly package price for this vehicle/hours. Set Hourly prices on the vehicle (1–12 hours).');
                return;
            }
        } else if (mapsMode || mode === 'point_to_point') {
            var custom = parseFloat((customEl && customEl.value) || '0') || 0;
            if (custom <= 0) {
                alert(mapsMode
                    ? 'Enter car cost (zone is off). Shared = cost × pax, Private = cost.'
                    : 'Enter a custom price for Point to Point.');
                return;
            }
            if (mapsMode) {
                priced = T.calcManualCarTotal(
                    custom,
                    svc,
                    adultsEl ? adultsEl.value : 1,
                    childrenEl ? childrenEl.value : 0,
                    0
                );
                priced.mode = mode;
            } else {
                priced = { total: custom, mode: 'point_to_point', base: custom };
            }
        } else {
            // Local Transfer + zone ON: zone mapping prices only
            priced = T.calcVehiclePrice(
                opt,
                svc,
                adultsEl ? adultsEl.value : 1,
                childrenEl ? childrenEl.value : 0,
                0,
                0
            );
            if (!(priced && priced.total > 0)) {
                alert(svc === 'shared'
                    ? 'No zone shared price for this route/vehicle. Check vehicle zone mapping.'
                    : 'No zone private price for this route/vehicle. Check vehicle zone mapping.');
                return;
            }
        }

        root.__lastPrice = priced;
        var cur = root.getAttribute('data-currency') || 'SGD';
        var panel = root.querySelector('[data-transport-price-panel]');
        var totalEl = root.querySelector('.transport-price-total');
        var detail = root.querySelector('.transport-price-detail');
        if (panel) panel.classList.remove('d-none');
        if (totalEl) totalEl.textContent = cur + ' ' + Number(priced.total || 0).toFixed(2);
        if (detail) {
            if (String(svc).toLowerCase() === 'shared' && typeof T.vehiclePriceDetailHtml === 'function') {
                detail.innerHTML = T.vehiclePriceDetailHtml({
                    type: 'shared',
                    mode: priced.mode || 'shared',
                    adults: adultsEl ? adultsEl.value : 1,
                    children: childrenEl ? childrenEl.value : 0,
                    adultUnit: priced.adultUnit,
                    childUnit: priced.childUnit,
                    shared_price: opt.dataset.sharedPrice,
                    unit: priced.unit,
                    total: priced.total
                }, cur);
            } else {
                var srcLabel = '';
                if (priced.source === 'hourly_package') srcLabel = ' · Hourly package';
                else if (priced.source === 'zone') srcLabel = ' · Zone price';
                else if (priced.source === 'base') srcLabel = ' · Base price';
                var hoursLabel = (mode === 'hourly' && (priced.hours || (hoursEl && hoursEl.value)))
                    ? (' · ' + (priced.hours || hoursEl.value) + 'h')
                    : '';
                detail.textContent = (priced.mode || mode) + hoursLabel + srcLabel + ' · ' + (opt.dataset.vehicleName || opt.textContent);
            }
        }
        var add = root.querySelector('.transport-add-btn');
        if (add) add.disabled = false;
    }

    function collectPayload(root, stay) {
        var T = S();
        var mode = modeOf(root);
        var vehicle = root.querySelector('.transport-vehicle');
        var opt = vehicle && vehicle.options[vehicle.selectedIndex];
        var typeEl = root.querySelector('.transport-service-type');
        var adults = parseInt((root.querySelector('.transport-adults') || {}).value, 10) || 1;
        var children = parseInt((root.querySelector('.transport-children') || {}).value, 10) || 0;
        var hours = parseInt((root.querySelector('.transport-hours') || {}).value, 10) || 0;
        var from = root.querySelector('.transport-from-zone');
        var to = root.querySelector('.transport-to-zone');
        var fOpt = from && from.options[from.selectedIndex];
        var tOpt = to && to.options[to.selectedIndex];
        var pickupText = (fOpt && fOpt.dataset.label) || (root.querySelector('.transport-pickup-text') || {}).value || '';
        var dropoffText = (tOpt && tOpt.dataset.label) || (root.querySelector('.transport-dropoff-text') || {}).value || '';
        var total = root.__lastPrice ? Number(root.__lastPrice.total || 0) : 0;
        var travelType = mode === 'local' ? 'local_transport' : (mode === 'hourly' ? 'travel_hourly' : 'travel_point');
        var supplement = T.autoSupplement(adults);
        var serviceType = mode === 'hourly' ? 'private' : (typeEl ? typeEl.value : 'private');
        var hourlyPkg = (mode === 'hourly' && opt && T.packageHourlySellPrice)
            ? T.packageHourlySellPrice(opt, hours)
            : 0;

        return {
            travel_type: travelType,
            vehicles_id: opt ? opt.value : '',
            vehicles_name: opt ? (opt.dataset.vehicleName || opt.textContent) : '',
            type: serviceType,
            entrypickup: pickupText,
            entrydropoff: dropoffText,
            from_zone_id: fOpt ? (fOpt.dataset.zoneId || from.value) : '',
            to_zone_id: tOpt ? (tOpt.dataset.zoneId || to.value) : '',
            pickupdate: (root.querySelector('.transport-date') || {}).value || stay.start || '',
            entrytime: T.readAmPmValue(root, 'transport'),
            selectedHours: mode === 'hourly' ? hours : '',
            adults: adults,
            children: children,
            totalPrice: total,
            grand_total: total,
            price_detail: root.__lastPrice ? (root.__lastPrice.mode || '') : '',
            price_source: root.__lastPrice ? (root.__lastPrice.source || '') : '',
            supplement: !!supplement,
            is_supplement: !!supplement,
            city: stay.cityName || '',
            country: stay.country || '',
            currency: stay.currency || '',
            plan_index: stay.planIndex || '',
            private_price: opt ? (opt.dataset.privatePrice || '') : '',
            shared_price: opt ? (opt.dataset.sharedPrice || '') : '',
            adult_price: opt ? (opt.dataset.adultPrice || opt.dataset.sharedPrice || '') : '',
            child_price: opt ? (opt.dataset.childPrice || opt.dataset.sharedPrice || '') : '',
            hourly_package_price: hourlyPkg || '',
            remarks: ''
        };
    }

    function setAddMode(root, editing) {
        var btn = root.querySelector('.transport-add-btn');
        if (!btn) return;
        btn.innerHTML = editing
            ? '<i class="ri-save-line me-1"></i>Update'
            : '<i class="ri-add-line me-1"></i>Add';
    }

    function renderAdded(root) {
        var T = S();
        var host = root.querySelector('[data-transport-added-list]');
        if (!host) return;
        var rows = readChunk(root);
        var cur = root.getAttribute('data-currency') || 'SGD';
        if (!rows.length) { host.innerHTML = ''; return; }
        var html = '<div class="table-responsive stp-lite-svc-added-wrap"><table class="table table-sm align-middle mb-0 stp-lite-svc-added-table"><thead><tr>' +
            '<th>Vehicle</th><th>Type</th><th>Route</th><th>Guests</th><th class="text-end">Total</th><th>Supplement</th><th class="text-end">Actions</th>' +
            '</tr></thead><tbody>';
        rows.forEach(function (row, idx) {
            var editing = root.__editingIdx === idx;
            html += '<tr class="' + (editing ? 'is-editing' : '') + '" data-idx="' + idx + '">' +
                '<td><div class="fw-semibold">' + T.esc(row.vehicles_name || 'Vehicle') + '</div>' +
                T.editingMarkHtml(editing) + '</td>' +
                '<td><small>' + T.esc(row.travel_type || '') + (row.selectedHours ? ' · ' + row.selectedHours + 'h' : '') + '</small></td>' +
                '<td><small>' + T.esc(row.entrypickup || '—') + (row.entrydropoff ? ' → ' + T.esc(row.entrydropoff) : '') + '</small></td>' +
                '<td><small>' + T.esc(row.adults || 0) + 'A / ' + T.esc(row.children || 0) + 'C</small></td>' +
                '<td class="text-end fw-semibold text-nowrap">' + cur + ' ' + Number(row.totalPrice || 0).toFixed(2) + '</td>' +
                '<td><div class="form-check mb-0"><input class="form-check-input transport-is-supplement" type="checkbox" data-idx="' + idx + '"' +
                (row.supplement || row.is_supplement ? ' checked' : '') + '>' +
                '<label class="form-check-label" style="font-size:0.72rem;">Supplement</label></div></td>' +
                '<td class="text-end">' + T.addedTableActions('transport', idx, true) + '</td>' +
                '</tr>';
        });
        html += '</tbody></table></div>';
        host.innerHTML = html;
    }

    function hydrate(root, stay, row) {
        if (!row) return;
        root.__hydrating = true;
        var dateEl = root.querySelector('.transport-date');
        var adultsEl = root.querySelector('.transport-adults');
        var childrenEl = root.querySelector('.transport-children');
        var typeEl = root.querySelector('.transport-service-type');
        var hoursEl = root.querySelector('.transport-hours');
        var customEl = root.querySelector('.transport-custom-price');
        if (dateEl) dateEl.value = row.pickupdate || stay.start || '';
        if (adultsEl) adultsEl.value = String(row.adults || 1);
        if (childrenEl) childrenEl.value = String(row.children || 0);
        if (typeEl) typeEl.value = row.type || 'private';
        if (hoursEl && row.selectedHours) hoursEl.value = String(row.selectedHours);
        if (customEl && row.travel_type === 'travel_point') customEl.value = String(row.totalPrice || 0);
        S().setAmPmValue(root, 'transport', row.entrytime || '');

        var modeVal = row.travel_type === 'local_transport' ? 'local'
            : (row.travel_type === 'travel_hourly' ? 'hourly' : 'point_to_point');
        var modeRadio = root.querySelector('.transport-mode[value="' + modeVal + '"]');
        if (modeRadio) modeRadio.checked = true;
        syncModeUi(root);

        var from = root.querySelector('.transport-from-zone');
        var to = root.querySelector('.transport-to-zone');
        if (from && row.from_zone_id) from.value = String(row.from_zone_id);
        if (to && row.to_zone_id) to.value = String(row.to_zone_id);
        var pt = root.querySelector('.transport-pickup-text');
        var dt = root.querySelector('.transport-dropoff-text');
        if (pt) pt.value = row.entrypickup || '';
        if (dt) dt.value = row.entrydropoff || '';

        root.__preferredVehicleId = row.vehicles_id || '';
        root.__preferredVehicleName = row.vehicles_name || '';
        root.__lastPrice = { total: row.totalPrice || 0, mode: row.price_detail || row.travel_type || '' };
        var cur = root.getAttribute('data-currency') || 'SGD';
        var panel = root.querySelector('[data-transport-price-panel]');
        var totalEl = root.querySelector('.transport-price-total');
        if (panel) panel.classList.remove('d-none');
        if (totalEl) totalEl.textContent = cur + ' ' + Number(row.totalPrice || 0).toFixed(2);
        var add = root.querySelector('.transport-add-btn');
        if (add) add.disabled = false;
        searchVehicles(root, stay, row.vehicles_id);
        root.__hydrating = false;
    }

    function addRow(root, stay) {
        if (!root.__lastPrice) { alert('Please Get Price first.'); return; }
        var rows = readChunk(root);
        var payload = collectPayload(root, stay);
        if (root.__editingIdx != null && root.__editingIdx >= 0 && root.__editingIdx < rows.length) {
            payload.supplement = rows[root.__editingIdx].supplement;
            payload.is_supplement = rows[root.__editingIdx].is_supplement;
            rows[root.__editingIdx] = payload;
            root.__editingIdx = null;
            setAddMode(root, false);
        } else {
            rows.push(payload);
        }
        writeChunk(root, rows);
        renderAdded(root);
        invalidate(root);
        root.__preferredVehicleId = '';
        root.__preferredVehicleName = '';
        var T = S();
        var vehicle = root.querySelector('.transport-vehicle');
        if (vehicle) {
            vehicle.innerHTML = '<option value="">Click Search Vehicles</option>';
            vehicle.disabled = true;
        }
        T.clearSelectOrInput(root.querySelector('.transport-service-type'), 'private');
        T.clearSelectOrInput(root.querySelector('.transport-from-zone'), '');
        T.clearSelectOrInput(root.querySelector('.transport-to-zone'), '');
        T.clearSelectOrInput(root.querySelector('.transport-pickup-text'), '');
        T.clearSelectOrInput(root.querySelector('.transport-dropoff-text'), '');
        T.clearSelectOrInput(root.querySelector('.transport-hours'), '');
        var custom = root.querySelector('.transport-custom-price');
        if (custom) custom.value = '0';
        T.setAmPmValue(root, 'transport', '');
        var getBtn = root.querySelector('.transport-get-price-btn');
        if (getBtn) getBtn.disabled = true;
        root.querySelectorAll('.transport-search-btn, .transport-search-btn-maps').forEach(function (btn) {
            btn.disabled = false;
        });
        var addBtn = root.querySelector('.transport-add-btn');
        if (addBtn) addBtn.disabled = true;
    }

    function ensureMapsAutocomplete(root, stay, forceReinit) {
        if (!window.StpLiteMaps) return;
        var mapsRow = root.querySelector('.transport-maps-row');
        if (!mapsRow || mapsRow.classList.contains('d-none')) return;
        var country = stay && stay.country;
        var city = stay && stay.cityName;
        var run = function () {
            if (forceReinit && typeof window.StpLiteMaps.reinitIn === 'function') {
                window.StpLiteMaps.reinitIn(root, country, city);
            } else if (typeof window.StpLiteMaps.initIn === 'function') {
                window.StpLiteMaps.initIn(root, country, city);
            }
        };
        // Defer so accordion paint / mode toggle completes (hidden fields skip init)
        if (root.__mapsInitTimer) clearTimeout(root.__mapsInitTimer);
        root.__mapsInitTimer = setTimeout(run, 80);
    }

    function bindShell(root, stay) {
        if (!root || root.__bound) return;
        root.__bound = true;
        var T = S();
        var g = T.tourGuests();
        var adultsEl = root.querySelector('.transport-adults');
        var childrenEl = root.querySelector('.transport-children');
        if (adultsEl) adultsEl.value = String(g.adults || 1);
        if (childrenEl) childrenEl.value = String(g.children || 0);

        T.bindAmPm(root);
        T.bindStayDate(root.querySelector('.transport-date'), stay, function () { invalidate(root); });
        T.bindGuestCaps(root);
        loadZones(root, stay);
        syncModeUi(root);
        // PTP / Hourly always use Google Maps fields (Local Transfer uses zones when zone ON)
        ensureMapsAutocomplete(root, stay);

        root.querySelectorAll('.transport-mode').forEach(function (el) {
            el.addEventListener('change', function () {
                if (!root.__hydrating) {
                    syncModeUi(root);
                    // Mode swap (Local ↔ PTP/Hourly) shows/hides maps row — rebind Places
                    ensureMapsAutocomplete(root, stay, true);
                    invalidate(root);
                }
            });
        });
        root.querySelectorAll('.transport-search-btn, .transport-search-btn-maps').forEach(function (btn) {
            btn.addEventListener('click', function () { searchVehicles(root, stay); });
        });
        root.querySelector('.transport-get-price-btn').addEventListener('click', function () { getPrice(root); });
        root.querySelector('.transport-add-btn').addEventListener('click', function () { addRow(root, stay); });

        root.querySelectorAll('.transport-vehicle, .transport-service-type, .transport-adults, .transport-children, .transport-hours, .transport-custom-price, .transport-from-zone, .transport-to-zone, .transport-pickup-text, .transport-dropoff-text').forEach(function (el) {
            el.addEventListener('change', function () { if (!root.__hydrating) invalidate(root); });
            el.addEventListener('input', function () { if (!root.__hydrating) invalidate(root); });
        });
        // Focus on maps fields re-binds if Places loaded late
        root.querySelectorAll('.transport-pickup-text, .transport-dropoff-text').forEach(function (el) {
            el.addEventListener('focus', function () { ensureMapsAutocomplete(root, stay); });
        });

        root.addEventListener('change', function (e) {
            var chk = e.target.closest('.transport-is-supplement');
            if (!chk) return;
            var rows = readChunk(root);
            var i = parseInt(chk.getAttribute('data-idx'), 10) || 0;
            if (rows[i]) {
                rows[i].supplement = !!chk.checked;
                rows[i].is_supplement = !!chk.checked;
                writeChunk(root, rows);
            }
        });
        root.addEventListener('click', function (e) {
            var removeBtn = e.target.closest('.transport-remove');
            if (removeBtn) {
                if (!(window.StpLiteTransportShared && window.StpLiteTransportShared.confirmRemoveService
                    ? window.StpLiteTransportShared.confirmRemoveService()
                    : window.confirm('Are you sure you want to remove this service?'))) return;
                var rows = readChunk(root);
                rows.splice(parseInt(removeBtn.getAttribute('data-idx'), 10) || 0, 1);
                writeChunk(root, rows);
                root.__editingIdx = null;
                setAddMode(root, false);
                renderAdded(root);
                return;
            }
            var editBtn = e.target.closest('.transport-edit-added');
            if (editBtn) {
                var idx = parseInt(editBtn.getAttribute('data-idx'), 10) || 0;
                var row = readChunk(root)[idx];
                if (!row) return;
                root.__editingIdx = idx;
                setAddMode(root, true);
                hydrate(root, stay, row);
                renderAdded(root);
                return;
            }
            var viewBtn = e.target.closest('.transport-view-breakup');
            if (viewBtn) {
                var r = readChunk(root)[parseInt(viewBtn.getAttribute('data-idx'), 10) || 0];
                if (!r) return;
                T.showPriceBreakdownModal(
                    r.vehicles_name || 'Transport',
                    r.currency || root.getAttribute('data-currency'),
                    r.totalPrice,
                    (typeof T.vehiclePriceDetailHtml === 'function'
                        ? T.vehiclePriceDetailHtml(r, r.currency || root.getAttribute('data-currency'))
                        : ('<div class="small text-muted">' + T.esc(r.travel_type || '') +
                            '<br>' + T.esc(r.entrypickup || '') +
                            (r.entrydropoff ? ' → ' + T.esc(r.entrydropoff) : '') +
                            '<br>' + T.esc(r.adults || 0) + 'A / ' + T.esc(r.children || 0) + 'C</div>'))
                );
            }
        });

        renderAdded(root);
    }

    function mountAll(scope) {
        var T = S();
        (scope || document).querySelectorAll('[data-stp-transport-mount]').forEach(function (el) {
            var stay = T.stayFromPanel(el, 'transport');
            el.innerHTML = shellHtml(stay);
            bindShell(el.querySelector('.stp-lite-transport'), stay);
        });
        if (window.StpLiteGuestCaps) window.StpLiteGuestCaps.refreshGuestDependentUI();
    }

    window.StpLiteTransport = {
        mountAll: mountAll,
        seedAdded: function (root, rows) {
            if (!root) return;
            writeChunk(root, rows || []);
            renderAdded(root);
        }
    };
})(window, document);
/* === END transport.js === */
