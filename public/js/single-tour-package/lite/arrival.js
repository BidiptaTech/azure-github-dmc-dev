/* === STP LITE: arrival.js ===
 * Arrival / entry port — Search → Get Price → Add → list
 * Pickup = ports by country; Dropoff = hotels/attractions/restaurants (backup)
 * === */
(function (window, document) {
    'use strict';

    var S = function () { return window.StpLiteTransportShared || {}; };

    /** Use inventory (sibling) DMC zone_on for this stay — not Master's login flag. */
    function stayZoneOn(T, stay) {
        if (stay && stay.zoneOn != null && stay.zoneOn !== '') {
            return parseInt(stay.zoneOn, 10) === 1;
        }
        if (T.zoneOnForStay) {
            return !!T.zoneOnForStay(
                stay && stay.cityName,
                stay && stay.country,
                stay && stay.dmcId
            );
        }
        return !!(T.zoneOn && T.zoneOn());
    }

    function shellHtml(stay) {
        var T = S();
        var cur = stay.currency || 'SGD';
        var zone = stayZoneOn(T, stay);
        return (
            '<div class="stp-lite-svc stp-lite-arrival" data-currency="' + T.esc(cur) + '">' +
            '  <div class="row g-2 mb-2">' +
            '    <div class="col-md-3"><label class="stp-lite-label">City</label>' +
            '      <div class="stp-lite-city-static">' + T.esc(T.cityLabel(stay)) + '</div></div>' +
            '    <div class="col-md-3"><label class="stp-lite-label">Flight / Train No.</label>' +
            '      <input type="text" class="form-control form-control-sm arrival-flight-no" placeholder="Optional"></div>' +
            '    <div class="col-md-3"><label class="stp-lite-label">Pickup time</label>' +
            T.ampmTimeHtml('arrival', '') +
            '</div>' +
            '    <div class="col-md-3"><label class="stp-lite-label">Date</label>' +
            '      <input type="date" class="form-control form-control-sm arrival-date" value="' + T.esc(stay.start || '') + '"></div>' +
            '  </div>' +
            '  <div class="row g-2 mb-2">' +
            (zone
                ? ('    <div class="col-md-4"><label class="stp-lite-label">Pickup (Port)</label>' +
                   '      <select class="form-select form-select-sm arrival-pickup"><option value="">Loading ports…</option></select></div>' +
                   '    <div class="col-md-4"><label class="stp-lite-label">Dropoff</label>' +
                   '      <select class="form-select form-select-sm arrival-dropoff"><option value="">Loading…</option></select></div>')
                : ('    <div class="col-md-4"><label class="stp-lite-label">Pickup</label>' +
                   '      <input type="text" class="form-control form-control-sm google-maps-autocomplete arrival-pickup-text" placeholder="Search pickup on map…" autocomplete="off"></div>' +
                   '    <div class="col-md-4"><label class="stp-lite-label">Dropoff</label>' +
                   '      <input type="text" class="form-control form-control-sm google-maps-autocomplete arrival-dropoff-text" placeholder="Search dropoff on map…" autocomplete="off"></div>')) +
            '    <div class="col-md-4 d-flex align-items-end">' +
            '      <button type="button" class="btn btn-sm btn-outline-primary arrival-search-btn w-100">' +
            '        <i class="ri-search-line me-1"></i>Search Vehicles</button></div>' +
            '  </div>' +
            '  <div class="row g-2 mb-2">' +
            '    <div class="col-md-3"><label class="stp-lite-label">Vehicle</label>' +
            '      <select class="form-select form-select-sm arrival-vehicle" disabled><option value="">Search first</option></select></div>' +
            '    <div class="col-md-2"><label class="stp-lite-label">Service</label>' +
            '      <select class="form-select form-select-sm arrival-service-type">' +
            '        <option value="private">Private</option><option value="shared">Shared</option></select></div>' +
            '    <div class="col-md-2"><label class="stp-lite-label">No. of Vehicles</label>' +
            '      <input type="number" min="1" step="1" class="form-control form-control-sm stp-lite-int arrival-vehicle-count" value="1"></div>' +
            '    <div class="col-md-2"><label class="stp-lite-label">Adults</label>' +
            '      <input type="number" min="1" class="form-control form-control-sm stp-lite-int arrival-adults" data-guest-cap="adults" value="1"></div>' +
            '    <div class="col-md-2" data-guest-child-ui><label class="stp-lite-label">Children</label>' +
            '      <input type="number" min="0" class="form-control form-control-sm stp-lite-int arrival-children" data-guest-cap="children" value="0"></div>' +
            (!zone
                ? ('    <div class="col-md-3"><label class="stp-lite-label">Car cost</label>' +
                   '      <input type="number" min="0" step="0.01" class="form-control form-control-sm arrival-custom-price" placeholder="Enter car price" value=""></div>')
                : '') +
            '    <div class="col-md-3 d-flex align-items-end gap-2 flex-wrap">' +
            '      <button type="button" class="btn btn-sm stp-lite-get-price-btn arrival-get-price-btn" disabled>' +
            '        <i class="ri-price-tag-3-line me-1"></i>Get Price</button>' +
            '      <button type="button" class="btn btn-sm btn-primary arrival-add-btn" disabled>' +
            '        <i class="ri-add-line me-1"></i>Add</button></div>' +
            '  </div>' +
            T.pricePanelHtml(cur, 'arrival') +
            '  <div class="stp-lite-svc-added mt-2" data-arrival-added-list></div>' +
            '  <input type="hidden" class="entry_port_data_chunk" value="[]">' +
            '</div>'
        );
    }

    function readChunk(root) {
        try {
            var v = JSON.parse((root.querySelector('.entry_port_data_chunk') || {}).value || '[]');
            return Array.isArray(v) ? v : [];
        } catch (e) { return []; }
    }

    function writeChunk(root, rows) {
        var el = root.querySelector('.entry_port_data_chunk');
        if (el) el.value = JSON.stringify(rows || []);
        S().syncHiddenJson('entry_port_data', '.entry_port_data_chunk');
        S().updateServiceHeaderTotal(root, 'arrival');
    }

    function readVehicleCount(root, selector) {
        var n = parseInt((root.querySelector(selector) || {}).value, 10);
        return n > 0 ? n : 1;
    }

    function applyPrivateVehicleCount(priced, count) {
        if (!priced) return priced;
        var n = count > 0 ? count : 1;
        if (String(priced.mode || '').toLowerCase() === 'private' && n > 1) {
            return Object.assign({}, priced, { total: Number(priced.total || 0) * n, vehicle_count: n });
        }
        return Object.assign({}, priced, { vehicle_count: n });
    }

    function invalidate(root) {
        root.__lastPrice = null;
        var panel = root.querySelector('[data-arrival-price-panel]');
        if (panel) panel.classList.add('d-none');
        var add = root.querySelector('.arrival-add-btn');
        if (add) add.disabled = true;
    }

    /** Pickup/dropoff changed → vehicle list is invalid until Search Vehicles again. */
    function clearVehicleOnRouteChange(root) {
        root.__preferredVehicleId = '';
        root.__preferredVehicleName = '';
        var vehicle = root.querySelector('.arrival-vehicle');
        if (vehicle) {
            vehicle.innerHTML = '<option value="">Click Search Vehicles</option>';
            vehicle.disabled = true;
        }
        var getBtn = root.querySelector('.arrival-get-price-btn');
        if (getBtn) getBtn.disabled = true;
        invalidate(root);
    }

    function loadPickupDropoff(root, stay) {
        var T = S();
        var pickup = root.querySelector('.arrival-pickup');
        var dropoff = root.querySelector('.arrival-dropoff');
        if (!stayZoneOn(T, stay)) return Promise.resolve();

        // Backup: pickup = all ports in country; dropoff = hotels + attractions + restaurants (+ ports)
        return Promise.all([
            T.fetchPorts(stay.country, stay.cityName),
            T.fetchHotels(stay.cityName, stay.country),
            T.fetchAttractions(stay.cityName, stay.country),
            T.fetchRestaurants(stay.cityName, stay.country)
        ]).then(function (results) {
            var ports = (results[0] && results[0].ports) || [];
            var hotels = (results[1] && results[1].hotels) || [];
            var attractions = (results[2] && results[2].attractions) || [];
            var restaurants = (results[3] && results[3].restaurants) || [];

            if (pickup) {
                pickup.innerHTML = ports.length
                    ? '<option value="">Select pickup port</option>'
                    : '<option value="">No ports for this country</option>';
                ports.forEach(function (p) {
                    var opt = document.createElement('option');
                    opt.value = p.port_id || p.id;
                    opt.textContent = p.port_name || p.name || 'Port';
                    opt.dataset.zoneType = 'Port';
                    opt.dataset.zoneId = p.port_id || p.id || '';
                    opt.dataset.label = p.port_name || p.name || '';
                    opt.dataset.lat = p.latitude || p.lat || '';
                    opt.dataset.lng = p.longitude || p.lng || '';
                    pickup.appendChild(opt);
                });
                pickup.disabled = false;
            }

            if (dropoff) {
                dropoff.innerHTML = '<option value="">Select dropoff</option>';
                function addGroup(label, items, type, idKey, nameKey) {
                    if (!items.length) return;
                    var og = document.createElement('optgroup');
                    og.label = label;
                    items.forEach(function (item) {
                        var opt = document.createElement('option');
                        opt.value = item[idKey] || item.id;
                        opt.textContent = item[nameKey] || item.name || label;
                        opt.dataset.zoneType = type;
                        opt.dataset.zoneId = opt.value;
                        opt.dataset.label = opt.textContent;
                        opt.dataset.lat = item.latitude || '';
                        opt.dataset.lng = item.longitude || '';
                        og.appendChild(opt);
                    });
                    dropoff.appendChild(og);
                }
                addGroup('Ports', ports, 'Port', 'port_id', 'port_name');
                addGroup('Hotels', hotels, 'Hotel', 'hotel_unique_id', 'name');
                addGroup('Attractions', attractions, 'Attraction', 'attraction_id', 'name');
                addGroup('Restaurants', restaurants, 'Restaurant', 'restaurant_id', 'name');
                dropoff.disabled = false;
            }
        }).catch(function () {
            if (pickup) { pickup.innerHTML = '<option value="">Error loading ports</option>'; pickup.disabled = false; }
            if (dropoff) { dropoff.innerHTML = '<option value="">Error loading</option>'; dropoff.disabled = false; }
        });
    }

    function searchVehicles(root, stay, preferredVehicleId) {
        var T = S();
        var vehicle = root.querySelector('.arrival-vehicle');
        var btn = root.querySelector('.arrival-search-btn');
        var getBtn = root.querySelector('.arrival-get-price-btn');
        var keepId = preferredVehicleId != null ? preferredVehicleId : root.__preferredVehicleId;
        var keepName = root.__preferredVehicleName || '';
        invalidate(root);
        if (btn) btn.disabled = true;
        if (vehicle) {
            vehicle.innerHTML = '<option value="">Searching…</option>';
            vehicle.disabled = true;
        }

        var promise;
        if (stayZoneOn(T, stay)) {
            var pickup = root.querySelector('.arrival-pickup');
            var dropoff = root.querySelector('.arrival-dropoff');
            var pOpt = pickup && pickup.options[pickup.selectedIndex];
            var dOpt = dropoff && dropoff.options[dropoff.selectedIndex];
            if (!pickup || !pickup.value || !dropoff || !dropoff.value) {
                alert('Select pickup port and dropoff.');
                if (btn) btn.disabled = false;
                return Promise.resolve();
            }
            var q = T.inv(stay.cityName, stay.country);
            promise = T.fetchVehiclesByZones({
                from_zone_id: pOpt.dataset.zoneId || pickup.value,
                to_zone_id: dOpt.dataset.zoneId || dropoff.value,
                from_zone_type: pOpt.dataset.zoneType || 'Port',
                to_zone_type: dOpt.dataset.zoneType || 'Hotel',
                zone_status: 1,
                city: q.city,
                country: q.country,
                dmc_id: q.dmc_id
            });
        } else {
            promise = T.fetchVehiclesByCity(stay.cityName, stay.country, false);
        }

        return promise.then(function (res) {
            var list = (res && res.vehicles) || [];
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
            if (btn) btn.disabled = false;
        });
    }

    function getPrice(root, stay) {
        var T = S();
        stay = stay || (T.stayFromPanel && T.stayFromPanel(root, 'arrival')) || {};
        var vehicle = root.querySelector('.arrival-vehicle');
        var typeEl = root.querySelector('.arrival-service-type');
        var adultsEl = root.querySelector('.arrival-adults');
        var childrenEl = root.querySelector('.arrival-children');
        var opt = vehicle && vehicle.options[vehicle.selectedIndex];
        if (!opt || !opt.value) {
            alert('Select a vehicle first.');
            return;
        }
        var priced;
        var svc = typeEl ? typeEl.value : 'private';
        if (!stayZoneOn(T, stay)) {
            var customEl = root.querySelector('.arrival-custom-price');
            var car = parseFloat((customEl && customEl.value) || '0') || 0;
            if (car <= 0) {
                alert('Enter car cost (zone is off). Shared = cost × pax, Private = cost.');
                return;
            }
            priced = T.calcManualCarTotal(
                car,
                svc,
                adultsEl ? adultsEl.value : 1,
                childrenEl ? childrenEl.value : 0,
                0
            );
        } else {
            // Zone ON: sell from zone mapping private_price / shared_price only (not vehicle base_price)
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
        var vehCount = readVehicleCount(root, '.arrival-vehicle-count');
        priced = applyPrivateVehicleCount(priced, vehCount);
        root.__lastPrice = priced;
        var cur = root.getAttribute('data-currency') || 'SGD';
        var panel = root.querySelector('[data-arrival-price-panel]');
        var totalEl = root.querySelector('.arrival-price-total');
        var detail = root.querySelector('.arrival-price-detail');
        if (panel) panel.classList.remove('d-none');
        if (totalEl) totalEl.textContent = cur + ' ' + Number(priced.total || 0).toFixed(2);
        if (detail) {
            var curLabel = cur;
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
                    total: priced.total,
                    vehicle_count: vehCount
                }, curLabel);
            } else {
                var src = priced.source === 'zone' ? 'Zone price' : (priced.source === 'base' ? 'Base price' : '');
                detail.textContent = (priced.mode || svc) + (src ? ' · ' + src : '') +
                    (vehCount > 1 ? ' · ' + vehCount + ' vehicles' : '') +
                    ' · ' + (opt.dataset.vehicleName || opt.textContent);
            }
        }
        var add = root.querySelector('.arrival-add-btn');
        if (add) add.disabled = false;
    }

    function collectPayload(root, stay) {
        var T = S();
        var vehicle = root.querySelector('.arrival-vehicle');
        var opt = vehicle && vehicle.options[vehicle.selectedIndex];
        var typeEl = root.querySelector('.arrival-service-type');
        var adults = parseInt((root.querySelector('.arrival-adults') || {}).value, 10) || 1;
        var children = parseInt((root.querySelector('.arrival-children') || {}).value, 10) || 0;
        var pickup = root.querySelector('.arrival-pickup');
        var dropoff = root.querySelector('.arrival-dropoff');
        var pOpt = pickup && pickup.options[pickup.selectedIndex];
        var dOpt = dropoff && dropoff.options[dropoff.selectedIndex];
        var pickupText = (pOpt && pOpt.dataset.label) || (root.querySelector('.arrival-pickup-text') || {}).value || '';
        var dropoffText = (dOpt && dOpt.dataset.label) || (root.querySelector('.arrival-dropoff-text') || {}).value || '';
        var total = root.__lastPrice ? Number(root.__lastPrice.total || 0) : 0;
        var supplement = T.autoSupplement(adults);
        var entrytime = T.readAmPmValue(root, 'arrival');
        var vehicleCount = readVehicleCount(root, '.arrival-vehicle-count');

        return {
            id: 'entry-' + Date.now() + '-' + Math.random().toString(36).slice(2, 9),
            travel_type: 'entry_port',
            bookingType: T.resolveRowBookingType ? T.resolveRowBookingType(null) : 'enquiry',
            bookingDate: (root.querySelector('.arrival-date') || {}).value || stay.start || '',
            vehicles_id: opt ? (parseInt(opt.value, 10) || opt.value) : '',
            vehicles_name: opt ? (opt.dataset.vehicleName || opt.textContent) : '',
            image: opt ? (opt.dataset.image || '') : '',
            dmc_id: parseInt((T.inv(stay.cityName, stay.country).dmc_id || stay.dmcId || (window.STP_LITE_CONFIG || {}).dmcId || 0), 10) || 0,
            Mode: 'dmc',
            type: typeEl ? typeEl.value : 'private',
            vehicle_type: opt ? (opt.dataset.vehicleType || '') : '',
            seating_capacity: opt ? (parseInt(opt.dataset.seating, 10) || 0) : 0,
            vehicle_count: vehicleCount,
            booked_vehicles: vehicleCount,
            arrival_transport_type: 'flight',
            arrival_flight_no: (root.querySelector('.arrival-flight-no') || {}).value || '',
            entrypickup: pickupText,
            entrydropoff: dropoffText,
            entrypickup_zone_id: pOpt ? (pOpt.dataset.zoneId || pickup.value) : '',
            entrydropoff_zone_id: dOpt ? (dOpt.dataset.zoneId || dropoff.value) : '',
            PickupPlaceid: {
                lat: pOpt ? (pOpt.dataset.lat || '') : '',
                lng: pOpt ? (pOpt.dataset.lng || '') : ''
            },
            DropoffPlaceid: {
                lat: dOpt ? (dOpt.dataset.lat || '') : '',
                lng: dOpt ? (dOpt.dataset.lng || '') : ''
            },
            entrypickup_lat: pOpt ? (pOpt.dataset.lat || '') : '',
            entrypickup_lng: pOpt ? (pOpt.dataset.lng || '') : '',
            entrydropoff_lat: dOpt ? (dOpt.dataset.lat || '') : '',
            entrydropoff_lng: dOpt ? (dOpt.dataset.lng || '') : '',
            pickupdate: (root.querySelector('.arrival-date') || {}).value || stay.start || '',
            entrytime: entrytime,
            adults: adults,
            children: children,
            totalPrice: total,
            grand_total: total,
            price: total,
            price_detail: root.__lastPrice ? (root.__lastPrice.mode || '') : '',
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
            private_cost_price: opt ? (opt.dataset.privateCost || '') : '',
            shared_cost_price: opt ? (opt.dataset.sharedCost || '') : '',
            zonePrivateCostPrice: opt ? (opt.dataset.privateCost || '') : '',
            zoneSharedCostPrice: opt ? (opt.dataset.sharedCost || '') : '',
            mapping_id: opt ? (opt.dataset.mappingId || '') : '',
            remarks: '',
            Tax: 0,
            distance: 0
        };
    }

    function setAddMode(root, editing) {
        var btn = root.querySelector('.arrival-add-btn');
        if (!btn) return;
        btn.innerHTML = editing
            ? '<i class="ri-save-line me-1"></i>Update'
            : '<i class="ri-add-line me-1"></i>Add';
    }

    function renderAdded(root) {
        var T = S();
        var host = root.querySelector('[data-arrival-added-list]');
        if (!host) return;
        var rows = readChunk(root);
        var cur = root.getAttribute('data-currency') || 'SGD';
        if (!rows.length) { host.innerHTML = ''; return; }
        var html = '<div class="table-responsive stp-lite-svc-added-wrap"><table class="table table-sm align-middle mb-0 stp-lite-svc-added-table"><thead><tr>' +
            '<th>Vehicle</th><th>Route</th><th>Guests</th><th class="text-end">Total</th><th>Supplement</th><th class="text-end">Actions</th>' +
            '</tr></thead><tbody>';
        rows.forEach(function (row, idx) {
            var editing = root.__editingIdx === idx;
            html += '<tr class="' + (editing ? 'is-editing' : '') + '" data-idx="' + idx + '">' +
                '<td><div class="fw-semibold">' + T.esc(row.vehicles_name || 'Vehicle') + '</div>' +
                '<small class="text-muted">' + T.esc(row.type || '') +
                ((row.vehicle_count || row.booked_vehicles) ? ' · ' + T.esc(row.vehicle_count || row.booked_vehicles) + ' veh' : '') +
                '</small>' +
                T.editingMarkHtml(editing) + '</td>' +
                '<td><small>' + T.esc(row.entrypickup || '—') + ' → ' + T.esc(row.entrydropoff || '—') + '</small></td>' +
                '<td><small>' + T.esc(row.adults || 0) + 'A / ' + T.esc(row.children || 0) + 'C</small></td>' +
                '<td class="text-end fw-semibold text-nowrap">' + cur + ' ' + Number(row.totalPrice || 0).toFixed(2) + '</td>' +
                '<td><div class="form-check mb-0"><input class="form-check-input arrival-is-supplement" type="checkbox" data-idx="' + idx + '"' +
                (row.supplement || row.is_supplement ? ' checked' : '') + '>' +
                '<label class="form-check-label" style="font-size:0.72rem;">Supplement</label></div></td>' +
                '<td class="text-end">' + T.addedTableActions('arrival', idx, true) + '</td>' +
                '</tr>';
        });
        html += '</tbody></table></div>';
        host.innerHTML = html;
    }

    function hydrate(root, stay, row) {
        if (!row) return;
        root.__hydrating = true;
        var flight = root.querySelector('.arrival-flight-no');
        var dateEl = root.querySelector('.arrival-date');
        var adultsEl = root.querySelector('.arrival-adults');
        var childrenEl = root.querySelector('.arrival-children');
        var typeEl = root.querySelector('.arrival-service-type');
        var countEl = root.querySelector('.arrival-vehicle-count');
        if (flight) flight.value = row.arrival_flight_no || '';
        if (dateEl) dateEl.value = row.pickupdate || stay.start || '';
        if (adultsEl) adultsEl.value = String(row.adults || 1);
        if (childrenEl) childrenEl.value = String(row.children || 0);
        if (typeEl) typeEl.value = row.type || 'private';
        if (countEl) countEl.value = String(row.vehicle_count || row.booked_vehicles || 1);
        S().setAmPmValue(root, 'arrival', row.entrytime || '');
        var pickup = root.querySelector('.arrival-pickup');
        var dropoff = root.querySelector('.arrival-dropoff');
        if (pickup && row.entrypickup_zone_id) pickup.value = String(row.entrypickup_zone_id);
        if (dropoff && row.entrydropoff_zone_id) dropoff.value = String(row.entrydropoff_zone_id);
        var pt = root.querySelector('.arrival-pickup-text');
        var dt = root.querySelector('.arrival-dropoff-text');
        if (pt) pt.value = row.entrypickup || '';
        if (dt) dt.value = row.entrydropoff || '';
        root.__preferredVehicleId = row.vehicles_id || '';
        root.__preferredVehicleName = row.vehicles_name || '';
        root.__lastPrice = { total: row.totalPrice || 0, mode: row.price_detail || row.type || '' };
        var cur = root.getAttribute('data-currency') || 'SGD';
        var panel = root.querySelector('[data-arrival-price-panel]');
        var totalEl = root.querySelector('.arrival-price-total');
        if (panel) panel.classList.remove('d-none');
        if (totalEl) totalEl.textContent = cur + ' ' + Number(row.totalPrice || 0).toFixed(2);
        var add = root.querySelector('.arrival-add-btn');
        if (add) add.disabled = false;
        searchVehicles(root, stay, row.vehicles_id);
        root.__hydrating = false;
    }

    function addRow(root, stay) {
        if (!root.__lastPrice) { alert('Please Get Price first.'); return; }
        var rows = readChunk(root);
        var payload = collectPayload(root, stay);
        var T = S();
        if (root.__editingIdx != null && root.__editingIdx >= 0 && root.__editingIdx < rows.length) {
            payload.supplement = rows[root.__editingIdx].supplement;
            payload.is_supplement = rows[root.__editingIdx].is_supplement;
            payload.bookingType = T.resolveRowBookingType
                ? T.resolveRowBookingType(rows[root.__editingIdx])
                : (rows[root.__editingIdx].bookingType || payload.bookingType);
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
        var vehicle = root.querySelector('.arrival-vehicle');
        if (vehicle) {
            vehicle.innerHTML = '<option value="">Click Search Vehicles</option>';
            vehicle.disabled = true;
        }
        T.clearSelectOrInput(root.querySelector('.arrival-service-type'), 'private');
        T.clearSelectOrInput(root.querySelector('.arrival-pickup'), '');
        T.clearSelectOrInput(root.querySelector('.arrival-dropoff'), '');
        T.clearSelectOrInput(root.querySelector('.arrival-pickup-text'), '');
        T.clearSelectOrInput(root.querySelector('.arrival-dropoff-text'), '');
        T.clearSelectOrInput(root.querySelector('.arrival-flight-no'), '');
        T.clearSelectOrInput(root.querySelector('.arrival-custom-price'), '');
        T.clearSelectOrInput(root.querySelector('.arrival-vehicle-count'), '1');
        T.setAmPmValue(root, 'arrival', '');
        // Get Price stays off until Search Vehicles again; Search must stay usable
        var getBtn = root.querySelector('.arrival-get-price-btn');
        if (getBtn) getBtn.disabled = true;
        var searchBtn = root.querySelector('.arrival-search-btn');
        if (searchBtn) searchBtn.disabled = false;
        var addBtn = root.querySelector('.arrival-add-btn');
        if (addBtn) addBtn.disabled = true;
    }

    function bindShell(root, stay) {
        if (!root || root.__bound) return;
        root.__bound = true;
        var T = S();
        var g = T.tourGuests();
        var adultsEl = root.querySelector('.arrival-adults');
        var childrenEl = root.querySelector('.arrival-children');
        if (adultsEl) adultsEl.value = String(g.adults || 1);
        if (childrenEl) childrenEl.value = String(g.children || 0);

        T.bindAmPm(root);
        T.bindStayDate(root.querySelector('.arrival-date'), stay, function () { invalidate(root); });
        T.bindGuestCaps(root);
        loadPickupDropoff(root, stay);
        if (!stayZoneOn(T, stay) && window.StpLiteMaps) {
            window.StpLiteMaps.initIn(root, stay.country, stay.cityName);
        }

        root.querySelector('.arrival-search-btn').addEventListener('click', function () { searchVehicles(root, stay); });
        root.querySelector('.arrival-get-price-btn').addEventListener('click', function () { getPrice(root, stay); });
        root.querySelector('.arrival-add-btn').addEventListener('click', function () { addRow(root, stay); });

        root.querySelectorAll('.arrival-pickup, .arrival-dropoff, .arrival-pickup-text, .arrival-dropoff-text').forEach(function (el) {
            el.addEventListener('change', function () { if (!root.__hydrating) clearVehicleOnRouteChange(root); });
            el.addEventListener('input', function () { if (!root.__hydrating) clearVehicleOnRouteChange(root); });
        });
        root.querySelectorAll('.arrival-vehicle, .arrival-service-type, .arrival-vehicle-count, .arrival-adults, .arrival-children, .arrival-custom-price').forEach(function (el) {
            el.addEventListener('change', function () { if (!root.__hydrating) invalidate(root); });
            el.addEventListener('input', function () { if (!root.__hydrating) invalidate(root); });
        });
        root.addEventListener('stp:time-changed', function () { if (!root.__hydrating) invalidate(root); });

        root.addEventListener('change', function (e) {
            var chk = e.target.closest('.arrival-is-supplement');
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
            var removeBtn = e.target.closest('.arrival-remove');
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
            var editBtn = e.target.closest('.arrival-edit-added');
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
            var viewBtn = e.target.closest('.arrival-view-breakup');
            if (viewBtn) {
                var r = readChunk(root)[parseInt(viewBtn.getAttribute('data-idx'), 10) || 0];
                if (!r) return;
                T.showPriceBreakdownModal(
                    r.vehicles_name || 'Arrival',
                    r.currency || root.getAttribute('data-currency'),
                    r.totalPrice,
                    (typeof T.vehiclePriceDetailHtml === 'function'
                        ? T.vehiclePriceDetailHtml(r, r.currency || root.getAttribute('data-currency'))
                        : ('<div class="small text-muted">' + T.esc(r.type || '') + ' · ' +
                            T.esc(r.entrypickup || '') + ' → ' + T.esc(r.entrydropoff || '') +
                            '<br>' + T.esc(r.adults || 0) + 'A / ' + T.esc(r.children || 0) + 'C</div>'))
                );
            }
        });

        renderAdded(root);
    }

    function mountAll(scope) {
        var T = S();
        (scope || document).querySelectorAll('[data-stp-arrival-mount]').forEach(function (el) {
            var stay = T.stayFromPanel(el, 'arrival');
            el.innerHTML = shellHtml(stay);
            bindShell(el.querySelector('.stp-lite-arrival'), stay);
        });
        if (window.StpLiteGuestCaps) window.StpLiteGuestCaps.refreshGuestDependentUI();
    }

    window.StpLiteArrival = {
        mountAll: mountAll,
        seedAdded: function (root, rows) {
            if (!root) return;
            writeChunk(root, rows || []);
            renderAdded(root);
        }
    };
})(window, document);
/* === END arrival.js === */
