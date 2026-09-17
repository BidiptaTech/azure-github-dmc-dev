/* === STP LITE: departure.js ===
 * Departure / exit port transfers — Search vehicles → Get Price → Add → list
 * Pickup = hotels/attractions/restaurants; Dropoff = ports by country (backup)
 * Payload: exit_port_data (travel_type: exit_port)
 * === */
(function (window, document) {
    'use strict';

    var S = function () { return window.StpLiteTransportShared || {}; };

    function shellHtml(stay) {
        var T = S();
        var cur = stay.currency || 'SGD';
        var zone = T.zoneOn && T.zoneOn();
        return (
            '<div class="stp-lite-svc stp-lite-departure" data-currency="' + T.esc(cur) + '">' +
            '  <div class="row g-2 mb-2">' +
            '    <div class="col-md-3"><label class="stp-lite-label">City</label>' +
            '      <div class="stp-lite-city-static">' + T.esc(T.cityLabel(stay)) + '</div></div>' +
            '    <div class="col-md-3"><label class="stp-lite-label">Flight / Train No.</label>' +
            '      <input type="text" class="form-control form-control-sm departure-flight-no" placeholder="Optional"></div>' +
            '    <div class="col-md-3"><label class="stp-lite-label">Exit time</label>' +
            T.ampmTimeHtml('departure', '') +
            '</div>' +
            '    <div class="col-md-3"><label class="stp-lite-label">Date</label>' +
            '      <input type="date" class="form-control form-control-sm departure-date" value="' + T.esc(stay.end || stay.start || '') + '"></div>' +
            '  </div>' +
            '  <div class="row g-2 mb-2">' +
            (zone
                ? ('    <div class="col-md-4"><label class="stp-lite-label">Pickup</label>' +
                   '      <select class="form-select form-select-sm departure-pickup"><option value="">Loading…</option></select></div>' +
                   '    <div class="col-md-4"><label class="stp-lite-label">Dropoff (Port)</label>' +
                   '      <select class="form-select form-select-sm departure-dropoff"><option value="">Loading ports…</option></select></div>')
                : ('    <div class="col-md-4"><label class="stp-lite-label">Pickup</label>' +
                   '      <input type="text" class="form-control form-control-sm google-maps-autocomplete departure-pickup-text" placeholder="Search pickup on map…" autocomplete="off"></div>' +
                   '    <div class="col-md-4"><label class="stp-lite-label">Dropoff</label>' +
                   '      <input type="text" class="form-control form-control-sm google-maps-autocomplete departure-dropoff-text" placeholder="Search dropoff on map…" autocomplete="off"></div>')) +
            '    <div class="col-md-4 d-flex align-items-end">' +
            '      <button type="button" class="btn btn-sm btn-outline-primary departure-search-btn w-100">' +
            '        <i class="ri-search-line me-1"></i>Search Vehicles</button></div>' +
            '  </div>' +
            '  <div class="row g-2 mb-2">' +
            '    <div class="col-md-3"><label class="stp-lite-label">Vehicle</label>' +
            '      <select class="form-select form-select-sm departure-vehicle" disabled><option value="">Search first</option></select></div>' +
            '    <div class="col-md-2"><label class="stp-lite-label">Service</label>' +
            '      <select class="form-select form-select-sm departure-service-type">' +
            '        <option value="private">Private</option><option value="shared">Shared</option></select></div>' +
            '    <div class="col-md-2"><label class="stp-lite-label">Adults</label>' +
            '      <input type="number" min="1" class="form-control form-control-sm stp-lite-int departure-adults" data-guest-cap="adults" value="1"></div>' +
            '    <div class="col-md-2" data-guest-child-ui><label class="stp-lite-label">Children</label>' +
            '      <input type="number" min="0" class="form-control form-control-sm stp-lite-int departure-children" data-guest-cap="children" value="0"></div>' +
            (!zone
                ? ('    <div class="col-md-3"><label class="stp-lite-label">Car cost</label>' +
                   '      <input type="number" min="0" step="0.01" class="form-control form-control-sm departure-custom-price" placeholder="Enter car price" value=""></div>')
                : '') +
            '    <div class="col-md-3 d-flex align-items-end gap-2 flex-wrap">' +
            '      <button type="button" class="btn btn-sm stp-lite-get-price-btn departure-get-price-btn" disabled>' +
            '        <i class="ri-price-tag-3-line me-1"></i>Get Price</button>' +
            '      <button type="button" class="btn btn-sm btn-primary departure-add-btn" disabled>' +
            '        <i class="ri-add-line me-1"></i>Add</button></div>' +
            '  </div>' +
            T.pricePanelHtml(cur, 'departure') +
            '  <div class="stp-lite-svc-added mt-2" data-departure-added-list></div>' +
            '  <input type="hidden" class="exit_port_data_chunk" value="[]">' +
            '</div>'
        );
    }

    function readChunk(root) {
        try {
            var v = JSON.parse((root.querySelector('.exit_port_data_chunk') || {}).value || '[]');
            return Array.isArray(v) ? v : [];
        } catch (e) { return []; }
    }

    function writeChunk(root, rows) {
        var el = root.querySelector('.exit_port_data_chunk');
        if (el) el.value = JSON.stringify(rows || []);
        S().syncHiddenJson('exit_port_data', '.exit_port_data_chunk');
        S().updateServiceHeaderTotal(root, 'departure');
    }

    function invalidate(root) {
        root.__lastPrice = null;
        var panel = root.querySelector('[data-departure-price-panel]');
        if (panel) panel.classList.add('d-none');
        var add = root.querySelector('.departure-add-btn');
        if (add) add.disabled = true;
    }

    function loadPickupDropoff(root, stay) {
        var T = S();
        var pickup = root.querySelector('.departure-pickup');
        var dropoff = root.querySelector('.departure-dropoff');
        if (!T.zoneOn || !T.zoneOn()) return Promise.resolve();

        // Backup: pickup = hotels + attractions + restaurants (+ ports); dropoff = ports by country
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
                pickup.innerHTML = '<option value="">Select pickup</option>';
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
                    pickup.appendChild(og);
                }
                addGroup('Hotels', hotels, 'Hotel', 'hotel_unique_id', 'name');
                addGroup('Attractions', attractions, 'Attraction', 'attraction_id', 'name');
                addGroup('Restaurants', restaurants, 'Restaurant', 'restaurant_id', 'name');
                addGroup('Ports', ports, 'Port', 'port_id', 'port_name');
                pickup.disabled = false;
            }

            if (dropoff) {
                dropoff.innerHTML = ports.length
                    ? '<option value="">Select dropoff port</option>'
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
                    dropoff.appendChild(opt);
                });
                dropoff.disabled = false;
            }
        }).catch(function () {
            if (pickup) { pickup.innerHTML = '<option value="">Error loading</option>'; pickup.disabled = false; }
            if (dropoff) { dropoff.innerHTML = '<option value="">Error loading ports</option>'; dropoff.disabled = false; }
        });
    }

    function searchVehicles(root, stay, preferredVehicleId) {
        var T = S();
        var vehicle = root.querySelector('.departure-vehicle');
        var btn = root.querySelector('.departure-search-btn');
        var getBtn = root.querySelector('.departure-get-price-btn');
        var keepId = preferredVehicleId != null ? preferredVehicleId : root.__preferredVehicleId;
        var keepName = root.__preferredVehicleName || '';
        invalidate(root);
        if (btn) btn.disabled = true;
        if (vehicle) {
            vehicle.innerHTML = '<option value="">Searching…</option>';
            vehicle.disabled = true;
        }

        var promise;
        if (T.zoneOn && T.zoneOn()) {
            var pickup = root.querySelector('.departure-pickup');
            var dropoff = root.querySelector('.departure-dropoff');
            var pOpt = pickup && pickup.options[pickup.selectedIndex];
            var dOpt = dropoff && dropoff.options[dropoff.selectedIndex];
            if (!pickup || !pickup.value || !dropoff || !dropoff.value) {
                alert('Select pickup and dropoff port.');
                if (btn) btn.disabled = false;
                return Promise.resolve();
            }
            var q = T.inv(stay.cityName, stay.country);
            promise = T.fetchVehiclesByZones({
                from_zone_id: pOpt.dataset.zoneId || pickup.value,
                to_zone_id: dOpt.dataset.zoneId || dropoff.value,
                from_zone_type: pOpt.dataset.zoneType || 'Hotel',
                to_zone_type: dOpt.dataset.zoneType || 'Port',
                zone_status: 1,
                city: q.city,
                country: q.country,
                dmc_id: q.dmc_id
            });
        } else {
            promise = T.fetchVehiclesByCity(stay.cityName, stay.country, true);
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

    function getPrice(root) {
        var T = S();
        var vehicle = root.querySelector('.departure-vehicle');
        var typeEl = root.querySelector('.departure-service-type');
        var adultsEl = root.querySelector('.departure-adults');
        var childrenEl = root.querySelector('.departure-children');
        var opt = vehicle && vehicle.options[vehicle.selectedIndex];
        if (!opt || !opt.value) {
            alert('Select a vehicle first.');
            return;
        }
        var priced;
        if (!T.zoneOn || !T.zoneOn()) {
            var customEl = root.querySelector('.departure-custom-price');
            var car = parseFloat((customEl && customEl.value) || '0') || 0;
            if (car <= 0) {
                alert('Enter car cost (zone is off). Shared = cost × pax, Private = cost.');
                return;
            }
            priced = T.calcManualCarTotal(
                car,
                typeEl ? typeEl.value : 'private',
                adultsEl ? adultsEl.value : 1,
                childrenEl ? childrenEl.value : 0,
                0
            );
        } else {
            priced = T.calcVehiclePrice(
                opt,
                typeEl ? typeEl.value : 'private',
                adultsEl ? adultsEl.value : 1,
                childrenEl ? childrenEl.value : 0,
                0,
                0
            );
        }
        root.__lastPrice = priced;
        var cur = root.getAttribute('data-currency') || 'SGD';
        var panel = root.querySelector('[data-departure-price-panel]');
        var totalEl = root.querySelector('.departure-price-total');
        var detail = root.querySelector('.departure-price-detail');
        if (panel) panel.classList.remove('d-none');
        if (totalEl) totalEl.textContent = cur + ' ' + Number(priced.total || 0).toFixed(2);
        if (detail) detail.textContent = (priced.mode || 'private') + ' · ' + (opt.dataset.vehicleName || opt.textContent);
        var add = root.querySelector('.departure-add-btn');
        if (add) add.disabled = false;
    }

    function collectPayload(root, stay) {
        var T = S();
        var vehicle = root.querySelector('.departure-vehicle');
        var opt = vehicle && vehicle.options[vehicle.selectedIndex];
        var typeEl = root.querySelector('.departure-service-type');
        var adults = parseInt((root.querySelector('.departure-adults') || {}).value, 10) || 1;
        var children = parseInt((root.querySelector('.departure-children') || {}).value, 10) || 0;
        var pickup = root.querySelector('.departure-pickup');
        var dropoff = root.querySelector('.departure-dropoff');
        var pOpt = pickup && pickup.options[pickup.selectedIndex];
        var dOpt = dropoff && dropoff.options[dropoff.selectedIndex];
        var pickupText = (pOpt && pOpt.dataset.label) || (root.querySelector('.departure-pickup-text') || {}).value || '';
        var dropoffText = (dOpt && dOpt.dataset.label) || (root.querySelector('.departure-dropoff-text') || {}).value || '';
        var total = root.__lastPrice ? Number(root.__lastPrice.total || 0) : 0;
        var supplement = T.autoSupplement(adults);
        var exittime = T.readAmPmValue(root, 'departure');

        return {
            travel_type: 'exit_port',
            vehicles_id: opt ? opt.value : '',
            vehicles_name: opt ? (opt.dataset.vehicleName || opt.textContent) : '',
            type: typeEl ? typeEl.value : 'private',
            exitpickup: pickupText,
            exitdropoff: dropoffText,
            exitpickup_zone_id: pOpt ? (pOpt.dataset.zoneId || pickup.value) : '',
            exitdropoff_zone_id: dOpt ? (dOpt.dataset.zoneId || dropoff.value) : '',
            exitpickup_lat: pOpt ? (pOpt.dataset.lat || '') : '',
            exitpickup_lng: pOpt ? (pOpt.dataset.lng || '') : '',
            exitdropoff_lat: dOpt ? (dOpt.dataset.lat || '') : '',
            exitdropoff_lng: dOpt ? (dOpt.dataset.lng || '') : '',
            exitpickupdate: (root.querySelector('.departure-date') || {}).value || stay.end || stay.start || '',
            pickupdate: (root.querySelector('.departure-date') || {}).value || stay.end || stay.start || '',
            exittime: exittime,
            departure_flight_no: (root.querySelector('.departure-flight-no') || {}).value || '',
            adults: adults,
            children: children,
            totalPrice: total,
            grand_total: total,
            price_detail: root.__lastPrice ? (root.__lastPrice.mode || '') : '',
            supplement: !!supplement,
            is_supplement: !!supplement,
            city: stay.cityName || '',
            country: stay.country || '',
            currency: stay.currency || '',
            plan_index: stay.planIndex || '',
            private_price: opt ? (opt.dataset.privatePrice || '') : '',
            shared_price: opt ? (opt.dataset.sharedPrice || '') : '',
            remarks: ''
        };
    }

    function setAddMode(root, editing) {
        var btn = root.querySelector('.departure-add-btn');
        if (!btn) return;
        btn.innerHTML = editing
            ? '<i class="ri-save-line me-1"></i>Update'
            : '<i class="ri-add-line me-1"></i>Add';
    }

    function renderAdded(root) {
        var T = S();
        var host = root.querySelector('[data-departure-added-list]');
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
                '<small class="text-muted">' + T.esc(row.type || '') + '</small>' +
                T.editingMarkHtml(editing) + '</td>' +
                '<td><small>' + T.esc(row.exitpickup || '—') + ' → ' + T.esc(row.exitdropoff || '—') + '</small></td>' +
                '<td><small>' + T.esc(row.adults || 0) + 'A / ' + T.esc(row.children || 0) + 'C</small></td>' +
                '<td class="text-end fw-semibold text-nowrap">' + cur + ' ' + Number(row.totalPrice || 0).toFixed(2) + '</td>' +
                '<td><div class="form-check mb-0"><input class="form-check-input departure-is-supplement" type="checkbox" data-idx="' + idx + '"' +
                (row.supplement || row.is_supplement ? ' checked' : '') + '>' +
                '<label class="form-check-label" style="font-size:0.72rem;">Supplement</label></div></td>' +
                '<td class="text-end">' + T.addedTableActions('departure', idx, true) + '</td>' +
                '</tr>';
        });
        html += '</tbody></table></div>';
        host.innerHTML = html;
    }

    function hydrate(root, stay, row) {
        if (!row) return;
        root.__hydrating = true;
        var flight = root.querySelector('.departure-flight-no');
        var dateEl = root.querySelector('.departure-date');
        var adultsEl = root.querySelector('.departure-adults');
        var childrenEl = root.querySelector('.departure-children');
        var typeEl = root.querySelector('.departure-service-type');
        if (flight) flight.value = row.departure_flight_no || '';
        if (dateEl) dateEl.value = row.exitpickupdate || row.pickupdate || stay.end || stay.start || '';
        if (adultsEl) adultsEl.value = String(row.adults || 1);
        if (childrenEl) childrenEl.value = String(row.children || 0);
        if (typeEl) typeEl.value = row.type || 'private';
        S().setAmPmValue(root, 'departure', row.exittime || '');
        var pickup = root.querySelector('.departure-pickup');
        var dropoff = root.querySelector('.departure-dropoff');
        if (pickup && row.exitpickup_zone_id) pickup.value = String(row.exitpickup_zone_id);
        if (dropoff && row.exitdropoff_zone_id) dropoff.value = String(row.exitdropoff_zone_id);
        var pt = root.querySelector('.departure-pickup-text');
        var dt = root.querySelector('.departure-dropoff-text');
        if (pt) pt.value = row.exitpickup || '';
        if (dt) dt.value = row.exitdropoff || '';
        root.__preferredVehicleId = row.vehicles_id || '';
        root.__preferredVehicleName = row.vehicles_name || '';
        root.__lastPrice = { total: row.totalPrice || 0, mode: row.price_detail || row.type || '' };
        var cur = root.getAttribute('data-currency') || 'SGD';
        var panel = root.querySelector('[data-departure-price-panel]');
        var totalEl = root.querySelector('.departure-price-total');
        if (panel) panel.classList.remove('d-none');
        if (totalEl) totalEl.textContent = cur + ' ' + Number(row.totalPrice || 0).toFixed(2);
        var add = root.querySelector('.departure-add-btn');
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
        var vehicle = root.querySelector('.departure-vehicle');
        if (vehicle) {
            vehicle.innerHTML = '<option value="">Click Search Vehicles</option>';
            vehicle.disabled = true;
        }
        T.clearSelectOrInput(root.querySelector('.departure-service-type'), 'private');
        T.clearSelectOrInput(root.querySelector('.departure-pickup'), '');
        T.clearSelectOrInput(root.querySelector('.departure-dropoff'), '');
        T.clearSelectOrInput(root.querySelector('.departure-pickup-text'), '');
        T.clearSelectOrInput(root.querySelector('.departure-dropoff-text'), '');
        T.clearSelectOrInput(root.querySelector('.departure-flight-no'), '');
        T.clearSelectOrInput(root.querySelector('.departure-custom-price'), '');
        T.setAmPmValue(root, 'departure', '');
        var getBtn = root.querySelector('.departure-get-price-btn');
        if (getBtn) getBtn.disabled = true;
        var searchBtn = root.querySelector('.departure-search-btn');
        if (searchBtn) searchBtn.disabled = false;
        var addBtn = root.querySelector('.departure-add-btn');
        if (addBtn) addBtn.disabled = true;
    }

    function bindShell(root, stay) {
        if (!root || root.__bound) return;
        root.__bound = true;
        var T = S();
        var g = T.tourGuests();
        var adultsEl = root.querySelector('.departure-adults');
        var childrenEl = root.querySelector('.departure-children');
        if (adultsEl) adultsEl.value = String(g.adults || 1);
        if (childrenEl) childrenEl.value = String(g.children || 0);

        T.bindAmPm(root);
        T.bindStayDate(root.querySelector('.departure-date'), stay, function () { invalidate(root); });
        T.bindGuestCaps(root);
        loadPickupDropoff(root, stay);
        if ((!T.zoneOn || !T.zoneOn()) && window.StpLiteMaps) {
            window.StpLiteMaps.initIn(root, stay.country, stay.cityName);
        }

        root.querySelector('.departure-search-btn').addEventListener('click', function () { searchVehicles(root, stay); });
        root.querySelector('.departure-get-price-btn').addEventListener('click', function () { getPrice(root); });
        root.querySelector('.departure-add-btn').addEventListener('click', function () { addRow(root, stay); });

        root.querySelectorAll('.departure-vehicle, .departure-service-type, .departure-adults, .departure-children, .departure-pickup, .departure-dropoff, .departure-pickup-text, .departure-dropoff-text, .departure-custom-price').forEach(function (el) {
            el.addEventListener('change', function () { if (!root.__hydrating) invalidate(root); });
            el.addEventListener('input', function () { if (!root.__hydrating) invalidate(root); });
        });
        root.addEventListener('stp:time-changed', function () { if (!root.__hydrating) invalidate(root); });

        root.addEventListener('change', function (e) {
            var chk = e.target.closest('.departure-is-supplement');
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
            var removeBtn = e.target.closest('.departure-remove');
            if (removeBtn) {
                var rows = readChunk(root);
                rows.splice(parseInt(removeBtn.getAttribute('data-idx'), 10) || 0, 1);
                writeChunk(root, rows);
                root.__editingIdx = null;
                setAddMode(root, false);
                renderAdded(root);
                return;
            }
            var editBtn = e.target.closest('.departure-edit-added');
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
            var viewBtn = e.target.closest('.departure-view-breakup');
            if (viewBtn) {
                var r = readChunk(root)[parseInt(viewBtn.getAttribute('data-idx'), 10) || 0];
                if (!r) return;
                T.showPriceBreakdownModal(
                    r.vehicles_name || 'Departure',
                    r.currency || root.getAttribute('data-currency'),
                    r.totalPrice,
                    '<div class="small text-muted">' + T.esc(r.type || '') + ' · ' +
                    T.esc(r.exitpickup || '') + ' → ' + T.esc(r.exitdropoff || '') +
                    '<br>' + T.esc(r.adults || 0) + 'A / ' + T.esc(r.children || 0) + 'C' +
                    (r.exittime ? '<br>Time: ' + T.esc(r.exittime) : '') + '</div>'
                );
            }
        });

        renderAdded(root);
    }

    function mountAll(scope) {
        var T = S();
        (scope || document).querySelectorAll('[data-stp-departure-mount]').forEach(function (el) {
            var stay = T.stayFromPanel(el, 'departure');
            el.innerHTML = shellHtml(stay);
            bindShell(el.querySelector('.stp-lite-departure'), stay);
        });
        if (window.StpLiteGuestCaps) window.StpLiteGuestCaps.refreshGuestDependentUI();
    }

    window.StpLiteDeparture = {
        mountAll: mountAll,
        seedAdded: function (root, rows) {
            if (!root) return;
            writeChunk(root, rows || []);
            renderAdded(root);
        }
    };
})(window, document);
/* === END departure.js === */
