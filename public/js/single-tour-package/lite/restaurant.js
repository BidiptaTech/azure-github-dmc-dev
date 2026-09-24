/* === STP LITE: restaurant.js ===
 * Restaurants — meal cascade → Transfer/Guide extras → Get Price → Add → list
 * Payload: restaurant_data
 * === */
(function (window, document) {
    'use strict';

    var S = function () { return window.StpLiteTransportShared || {}; };
    var PREFIX = 'restaurant';

    function cfg() { return window.STP_LITE_CONFIG || {}; }

    function guestCounts(root) {
        return {
            adults: parseInt((root.querySelector('.restaurant-adults') || {}).value, 10) || 0,
            children: parseInt((root.querySelector('.restaurant-children') || {}).value, 10) || 0,
            infants: 0
        };
    }

    /** Map API meal_types type breakfast/lunch/dinner → meal_period 1/2/3 */
    function mapMealPeriod(mt) {
        if (mt == null || mt === '') return '';
        var raw = '';
        if (typeof mt === 'object') {
            var src = mt.meal_period != null ? mt.meal_period : (mt.type != null ? mt.type : mt.label);
            raw = src == null ? '' : String(src);
        } else {
            raw = String(mt);
        }
        raw = raw.toLowerCase().trim();
        if (raw === 'breakfast' || raw === 'bf' || raw === '1') return '1';
        if (raw === 'lunch' || raw === '2') return '2';
        if (raw === 'dinner' || raw === '3') return '3';
        if (/^[123]$/.test(raw)) return raw;
        return '';
    }

    function mealTypeLabel(mt, period) {
        if (mt && (mt.label || mt.name)) return mt.label || mt.name;
        if (period === '1' || String(mt && mt.type).toLowerCase() === 'breakfast') return 'Breakfast';
        if (period === '2' || String(mt && mt.type).toLowerCase() === 'lunch') return 'Lunch';
        if (period === '3' || String(mt && mt.type).toLowerCase() === 'dinner') return 'Dinner';
        return String((mt && mt.type) || period || 'Meal');
    }

    function parseMealTypes(raw) {
        var types = raw;
        if (typeof types === 'string') {
            try { types = JSON.parse(types); } catch (e) { types = []; }
        }
        if (!Array.isArray(types)) return [];
        return types.filter(function (mt) { return !!mapMealPeriod(mt); });
    }

    function isMultiRestaurantValue(val) {
        return String(val || '').indexOf('multi_restaurant_') === 0;
    }

    function parseMultiRestaurant(opt) {
        if (!opt) return null;
        if ((opt.dataset && opt.dataset.isMulti === '1') || isMultiRestaurantValue(opt.value)) {
            try { return JSON.parse(opt.dataset.multiRestaurant || '{}'); } catch (e) { return {}; }
        }
        return null;
    }

    function applyMasterFromRestaurantOption(root, rOpt) {
        var T = S();
        if (typeof T.applyMasterGuideVehicle !== 'function') return;
        var mr = parseMultiRestaurant(rOpt);
        if (mr) {
            T.applyMasterGuideVehicle(
                root,
                PREFIX,
                rOpt.dataset.vehicleIncluded === '1' || mr.vehicle,
                rOpt.dataset.guideIncluded === '1' || mr.guide,
                { preserve: !!root.__hydrating }
            );
            return;
        }
        if (!root.__hydrating && typeof T.unlockMasterGuideVehicle === 'function') {
            T.unlockMasterGuideVehicle(root, PREFIX);
        }
    }

    function applyMultiDish(root, mr) {
        var dish = root.querySelector('.restaurant-dish');
        if (!dish) return;
        dish.innerHTML = '<option value="">Select dish</option>';
        var opt = document.createElement('option');
        opt.value = 'buffet';
        opt.textContent = 'Buffet';
        opt.dataset.name = 'Buffet';
        opt.dataset.adultPrice = (mr && mr.adult_price) || 0;
        opt.dataset.childPrice = (mr && mr.child_price) || 0;
        opt.dataset.type = 'Buffet';
        opt.dataset.mealPeriod = '';
        dish.appendChild(opt);
        dish.value = 'buffet';
        dish.disabled = false;
    }

    function shellHtml(stay) {
        var T = S();
        var cur = stay.currency || 'SGD';
        var g = T.tourGuests();
        return (
            '<div class="stp-lite-svc stp-lite-restaurant" data-currency="' + T.esc(cur) + '">' +
            '  <div class="row g-2 mb-2">' +
            '    <div class="col-12 col-md-3"><label class="stp-lite-label">City</label>' +
            '      <div class="stp-lite-city-static">' + T.esc(T.cityLabel(stay)) + '</div></div>' +
            '    <div class="col-12 col-md-3"><label class="stp-lite-label">Restaurant</label>' +
            '      <select class="form-select form-select-sm restaurant-select" disabled><option value="">Loading…</option></select></div>' +
            '    <div class="col-6 col-md-3"><label class="stp-lite-label">Meal type</label>' +
            '      <select class="form-select form-select-sm restaurant-meal-type" disabled><option value="">Select restaurant</option></select></div>' +
            '    <div class="col-6 col-md-3"><label class="stp-lite-label">Dish</label>' +
            '      <select class="form-select form-select-sm restaurant-dish" disabled><option value="">Select meal type</option></select></div>' +
            '  </div>' +
            '  <div class="row g-2 mb-2 align-items-end">' +
            '    <div class="col-6 col-md-2"><label class="stp-lite-label">Date</label>' +
            '      <input type="date" class="form-control form-control-sm restaurant-date" value="' + T.esc(stay.start || '') + '"></div>' +
            '    <div class="col-6 col-md-2"><label class="stp-lite-label">Adults</label>' +
            '      <input type="number" min="0" class="form-control form-control-sm stp-lite-int restaurant-adults" data-guest-cap="adults" value="' + (g.adults || 1) + '"></div>' +
            '    <div class="col-6 col-md-2" data-guest-child-ui><label class="stp-lite-label">Children</label>' +
            '      <input type="number" min="0" class="form-control form-control-sm stp-lite-int restaurant-children" data-guest-cap="children" value="' + (g.children || 0) + '"></div>' +
            '    <div class="col-6 col-md-3 stp-lite-restaurant-time-col"><label class="stp-lite-label">Time</label>' +
            T.ampmTimeHtml('restaurant', '') +
            '</div>' +
            '    <div class="col-6 col-md-2">' + T.transferRequiredSelectHtml(PREFIX) + '</div>' +
            '    <div class="col-6 col-md-2">' + T.guideRequiredSelectHtml(PREFIX) + '</div>' +
            '  </div>' +
            T.transferExtrasHtml(PREFIX) +
            T.guideExtrasHtml(PREFIX) +
            '  <div class="row g-2 mb-2">' +
            '    <div class="col-md-12 d-flex align-items-end gap-2 flex-wrap">' +
            '      <button type="button" class="btn btn-sm stp-lite-get-price-btn restaurant-get-price-btn">' +
            '        <i class="ri-price-tag-3-line me-1"></i>Get Price</button>' +
            '      <button type="button" class="btn btn-sm btn-primary restaurant-add-btn" disabled>' +
            '        <i class="ri-add-line me-1"></i>Add</button></div>' +
            '  </div>' +
            T.pricePanelHtml(cur, 'restaurant') +
            '  <div class="stp-lite-svc-added mt-2" data-restaurant-added-list></div>' +
            '  <input type="hidden" class="restaurant_data_chunk" value="[]">' +
            '</div>'
        );
    }

    function readChunk(root) {
        try {
            var v = JSON.parse((root.querySelector('.restaurant_data_chunk') || {}).value || '[]');
            return Array.isArray(v) ? v : [];
        } catch (e) { return []; }
    }

    function writeChunk(root, rows) {
        var T = S();
        // Persist meal-only totals; header/list compose meal+xfer+guide via serviceRowDisplayTotal
        var el = root.querySelector('.restaurant_data_chunk');
        if (el) el.value = JSON.stringify(rows || []);
        T.syncHiddenJson('restaurant_data', '.restaurant_data_chunk');
        if (typeof T.updateServiceHeaderTotal === 'function') {
            T.updateServiceHeaderTotal(root, 'restaurant');
        } else {
            try {
                root.dispatchEvent(new CustomEvent('stp:service-chunk-changed', {
                    bubbles: true,
                    detail: {
                        service: 'restaurant',
                        total: (rows || []).reduce(function (s, r) {
                            return s + (typeof T.serviceRowDisplayTotal === 'function'
                                ? T.serviceRowDisplayTotal(r)
                                : (Number(r.totalPrice) || 0));
                        }, 0),
                        currency: root.getAttribute('data-currency') || 'SGD',
                        root: root
                    }
                }));
            } catch (e) { /* ignore */ }
        }
    }

    function invalidate(root) {
        root.__lastPrice = null;
        var panel = root.querySelector('[data-restaurant-price-panel]');
        if (panel) panel.classList.add('d-none');
        var add = root.querySelector('.restaurant-add-btn');
        if (add) add.disabled = true;
    }

    function loadRestaurants(root, stay) {
        var T = S();
        var select = root.querySelector('.restaurant-select');
        if (!select) return Promise.resolve();
        var q = T.inv(stay.cityName, stay.country);
        select.disabled = true;
        select.innerHTML = '<option value="">Loading restaurants…</option>';
        return T.fetchJson((cfg().routes.fetchRestaurantsByDmc || '') + '?' + q.qs)
            .then(function (res) {
                var list = (res && res.restaurants) || [];
                root.__restaurants = list;
                select.innerHTML = '<option value="">Select restaurant</option>';
                list.forEach(function (r) {
                    var opt = document.createElement('option');
                    opt.value = r.restaurant_id || r.id;
                    opt.textContent = r.name || r.restaurant_name || 'Restaurant';
                    opt.dataset.name = r.name || r.restaurant_name || '';
                    opt.dataset.mealTypes = JSON.stringify(parseMealTypes(r.meal_types));
                    opt.dataset.isMulti = '0';
                    select.appendChild(opt);
                });
                var packages = (res && res.multi_restaurants) || [];
                root.__multiRestaurants = packages;
                packages.forEach(function (m) {
                    var opt = document.createElement('option');
                    var pid = m.id || m.package_unique_id || '';
                    opt.value = 'multi_restaurant_' + pid;
                    opt.textContent = m.package_name || m.name || 'Multi Restaurant';
                    opt.dataset.name = m.package_name || m.name || 'Multi Restaurant';
                    opt.dataset.isMulti = '1';
                    opt.dataset.vehicleIncluded = (m.vehicle ? '1' : '0');
                    opt.dataset.guideIncluded = (m.guide ? '1' : '0');
                    opt.dataset.adultPrice = m.adult_price || 0;
                    opt.dataset.childPrice = m.child_price || 0;
                    try { opt.dataset.multiRestaurant = JSON.stringify(m); } catch (e) { opt.dataset.multiRestaurant = '{}'; }
                    if (select.firstChild) {
                        select.insertBefore(opt, select.firstChild.nextSibling);
                    } else {
                        select.appendChild(opt);
                    }
                });
                select.disabled = false;
            })
            .catch(function () {
                select.innerHTML = '<option value="">Error loading</option>';
                select.disabled = false;
            });
    }

    function fillMealTypes(root, restaurantId, stay) {
        var mealType = root.querySelector('.restaurant-meal-type');
        var dish = root.querySelector('.restaurant-dish');
        var rest = root.querySelector('.restaurant-select');
        if (!mealType) return;
        var hit = (root.__restaurants || []).find(function (r) {
            return String(r.restaurant_id || r.id) === String(restaurantId);
        });
        var rOpt = rest && rest.options[rest.selectedIndex];
        var mr = parseMultiRestaurant(rOpt);
        if (mr) {
            mealType.innerHTML = '<option value="">Select meal type</option>';
            var meals = [];
            if (mr.breakfast || (mr.breakfast_time && String(mr.breakfast_time).trim() !== '')) {
                meals.push({ period: '1', label: 'Breakfast' });
            }
            if (mr.lunch || (mr.lunch_time && String(mr.lunch_time).trim() !== '')) {
                meals.push({ period: '2', label: 'Lunch' });
            }
            if (mr.dinner || (mr.dinner_time && String(mr.dinner_time).trim() !== '')) {
                meals.push({ period: '3', label: 'Dinner' });
            }
            meals.forEach(function (m) {
                var opt = document.createElement('option');
                opt.value = m.period;
                opt.textContent = m.label;
                mealType.appendChild(opt);
            });
            mealType.disabled = !meals.length;
            applyMultiDish(root, mr);
            applyMasterFromRestaurantOption(root, rOpt);
            if (meals.length === 1) mealType.value = meals[0].period;
            return;
        }
        applyMasterFromRestaurantOption(root, rOpt);
        var types = parseMealTypes((hit && hit.meal_types) || (rOpt && rOpt.dataset.mealTypes) || []);
        mealType.innerHTML = '<option value="">Select meal type</option>';
        types.forEach(function (mt) {
            var period = mapMealPeriod(mt);
            if (!period) return;
            var opt = document.createElement('option');
            opt.value = period;
            opt.textContent = mealTypeLabel(mt, period);
            opt.dataset.rawType = String((mt && mt.type) || '');
            opt.dataset.open = (mt && (mt.open_time || mt.open)) || '';
            opt.dataset.close = (mt && (mt.close_time || mt.close)) || '';
            mealType.appendChild(opt);
        });
        if (!types.length) {
            var empty = document.createElement('option');
            empty.value = '';
            empty.textContent = restaurantId ? 'No meal types configured' : 'Select restaurant';
            empty.disabled = true;
            mealType.appendChild(empty);
        }
        mealType.disabled = !types.length;
        if (dish) {
            dish.innerHTML = '<option value="">Select meal type first</option>';
            dish.disabled = true;
        }
        if (types.length === 1) {
            mealType.value = mapMealPeriod(types[0]);
            if (!root.__hydrating && stay && restaurantId) {
                loadMeals(root, stay, restaurantId, mealType.value);
            }
        }
    }

    function mealsMatchingPeriod(list, period) {
        var wanted = mapMealPeriod(period);
        var rows = list || [];
        if (!wanted) return rows;
        // Strict: only dishes for this meal type (never blank/other periods)
        return rows.filter(function (m) {
            return mapMealPeriod(m && m.meal_period) === wanted;
        });
    }

    function fillDishOptions(dish, list, period) {
        dish.innerHTML = '<option value="">Select dish</option>';
        var wanted = mapMealPeriod(period);
        mealsMatchingPeriod(list, period).forEach(function (m) {
            var opt = document.createElement('option');
            opt.value = m.meal_id || m.id;
            opt.textContent = m.name || m.display_name || m.meal_name || 'Meal';
            opt.dataset.name = m.name || m.meal_name || '';
            opt.dataset.adultPrice = m.adult_price || 0;
            opt.dataset.childPrice = m.child_price || 0;
            opt.dataset.type = m.type || m.meal_type || '';
            opt.dataset.mealPeriod = mapMealPeriod(m.meal_period) || wanted || '';
            dish.appendChild(opt);
        });
        dish.disabled = false;
        if (dish.options.length <= 1) {
            dish.innerHTML = '<option value="">No dishes for this meal type</option>';
            dish.disabled = true;
        }
    }

    function loadMeals(root, stay, restaurantId, mealPeriod) {
        var T = S();
        var dish = root.querySelector('.restaurant-dish');
        if (!dish || !restaurantId) return Promise.resolve();
        if (isMultiRestaurantValue(restaurantId)) {
            var rest = root.querySelector('.restaurant-select');
            var rOpt = rest && rest.options[rest.selectedIndex];
            applyMultiDish(root, parseMultiRestaurant(rOpt) || {});
            return Promise.resolve();
        }
        var period = mapMealPeriod(mealPeriod);
        if (!period) {
            dish.innerHTML = '<option value="">Select meal type first</option>';
            dish.disabled = true;
            return Promise.resolve();
        }
        if (typeof T.fetchJson !== 'function') {
            dish.innerHTML = '<option value="">Error loading meals</option>';
            dish.disabled = false;
            return Promise.resolve();
        }
        var q = T.inv(stay.cityName, stay.country);
        dish.disabled = true;
        dish.innerHTML = '<option value="">Loading dishes…</option>';
        var baseQs = 'restaurant_id=' + encodeURIComponent(restaurantId) + '&' + q.qs;
        var withPeriod = (cfg().routes.fetchMealsByRestaurant || '') + '?' + baseQs +
            '&meal_period=' + encodeURIComponent(period);

        function apply(list) {
            fillDishOptions(dish, list, period);
        }

        // Only load dishes for the selected meal type — never fall back to all meals
        // (inactive Lunch must not show Breakfast/Dinner dishes)
        return T.fetchJson(withPeriod)
            .then(function (res) {
                var list = (res && res.meals) || [];
                apply(list);
                return list;
            })
            .catch(function () {
                dish.innerHTML = '<option value="">Error loading meals</option>';
                dish.disabled = false;
            });
    }

    function getPrice(root) {
        var T = S();
        var mealType = root.querySelector('.restaurant-meal-type');
        if (!mealType || !mapMealPeriod(mealType.value)) {
            alert('Select a meal type first.');
            return;
        }
        var dish = root.querySelector('.restaurant-dish');
        var opt = dish && dish.options[dish.selectedIndex];
        if (!opt || !opt.value) {
            alert('Select a dish first.');
            return;
        }
        var g = guestCounts(root);
        var adultP = parseFloat(opt.dataset.adultPrice) || 0;
        var childP = parseFloat(opt.dataset.childPrice) || 0;
        var mealTotal = (adultP * g.adults) + (childP * g.children);
        var xfer = T.calcInlineTransferPrice(root, PREFIX, g.adults, g.children, g.infants);
        T.refreshTransferCostDisplay(root, PREFIX, g.adults, g.children, g.infants);
        var guide = T.calcInlineGuidePrice(root, PREFIX);
        var total = mealTotal + (Number(xfer) || 0) + (Number(guide.total) || 0);
        var parts = [];
        var cur = root.getAttribute('data-currency') || 'SGD';
        var mealAmt = (adultP * g.adults) + (childP * g.children);
        parts.push(T.priceFormulaRowHtml(
            '<strong>Meal</strong> ' + g.adults + 'A × ' + adultP.toFixed(2)
                + (g.children ? ' + ' + g.children + 'C × ' + childP.toFixed(2) : ''),
            cur + ' ' + mealAmt.toFixed(2)
        ));
        if (xfer) {
            parts.push(T.priceFormulaRowHtml('<strong>Transfer</strong>', cur + ' ' + Number(xfer).toFixed(2)));
        }
        if (guide.total) {
            parts.push(T.priceFormulaRowHtml('<strong>Guide</strong>', cur + ' ' + Number(guide.total).toFixed(2)));
        }
        root.__lastPrice = {
            total: total,
            mealTotal: mealTotal,
            transferTotal: Number(xfer) || 0,
            guideTotal: Number(guide.total) || 0,
            adultPrice: adultP,
            childPrice: childP,
            breakdown: parts.join('')
        };
        var panel = root.querySelector('[data-restaurant-price-panel]');
        var totalEl = root.querySelector('.restaurant-price-total');
        var detail = root.querySelector('.restaurant-price-detail');
        if (panel) panel.classList.remove('d-none');
        if (totalEl) totalEl.textContent = cur + ' ' + total.toFixed(2);
        if (detail) detail.innerHTML = root.__lastPrice.breakdown;
        var add = root.querySelector('.restaurant-add-btn');
        if (add) add.disabled = false;
    }

    function collectPayload(root, stay) {
        var T = S();
        var rest = root.querySelector('.restaurant-select');
        var mealType = root.querySelector('.restaurant-meal-type');
        var dish = root.querySelector('.restaurant-dish');
        var rOpt = rest && rest.options[rest.selectedIndex];
        var dOpt = dish && dish.options[dish.selectedIndex];
        var mOpt = mealType && mealType.options[mealType.selectedIndex];
        var g = guestCounts(root);
        var adultP = dOpt ? (parseFloat(dOpt.dataset.adultPrice) || 0) : 0;
        var childP = dOpt ? (parseFloat(dOpt.dataset.childPrice) || 0) : 0;
        var mealTotal = (adultP * g.adults) + (childP * g.children);
        var transferOptions = T.collectTransferOptions(root, PREFIX, g.adults, g.children, g.infants);
        var guideOptions = T.collectGuideOptions(root, PREFIX);
        // Store meal-only; transfer/guide costs live in nested options (classic parity)
        var total = mealTotal;
        if (root.__lastPrice && root.__lastPrice.mealTotal != null) {
            total = Number(root.__lastPrice.mealTotal) || total;
        }
        var supplement = T.autoSupplement(g.adults);
        var visitTime = T.readAmPmValue(root, 'restaurant');

        return {
            restaurantId: rest ? rest.value : '',
            restaurantName: rOpt ? (rOpt.dataset.name || rOpt.textContent) : '',
            mealType: mealType ? mealType.value : '',
            mealTypeLabel: mOpt ? mOpt.textContent : '',
            mealSpecificType: dOpt ? (dOpt.dataset.type || '') : '',
            MealDescription: [{
                meal_id: dish ? dish.value : '',
                name: dOpt ? (dOpt.dataset.name || dOpt.textContent) : '',
                adult_price: adultP,
                child_price: childP
            }],
            adult_price: adultP,
            child_price: childP,
            adults: g.adults,
            children: g.children,
            visitTime: visitTime,
            bookingDate: (root.querySelector('.restaurant-date') || {}).value || stay.start || '',
            totalPrice: total,
            grand_total: total,
            supplement: !!supplement,
            is_supplement: !!supplement,
            transfer_options: transferOptions,
            guide_options: guideOptions,
            is_multi_restaurant: !!(rOpt && rOpt.dataset.isMulti === '1'),
            multi_restaurant_id: (rOpt && rOpt.dataset.isMulti === '1')
                ? String(rest.value || '').replace(/^multi_restaurant_/, '')
                : null,
            vehicle_included: !!(rOpt && rOpt.dataset.vehicleIncluded === '1'),
            guide_included: !!(rOpt && rOpt.dataset.guideIncluded === '1'),
            city: stay.cityName || '',
            country: stay.country || '',
            currency: stay.currency || '',
            plan_index: stay.planIndex || '',
            remarks: ''
        };
    }

    function setAddMode(root, editing) {
        var btn = root.querySelector('.restaurant-add-btn');
        if (!btn) return;
        btn.innerHTML = editing
            ? '<i class="ri-save-line me-1"></i>Update'
            : '<i class="ri-add-line me-1"></i>Add';
    }

    function renderAdded(root) {
        var T = S();
        var host = root.querySelector('[data-restaurant-added-list]');
        if (!host) return;
        var rows = readChunk(root);
        var cur = root.getAttribute('data-currency') || 'SGD';
        if (!rows.length) { host.innerHTML = ''; return; }
        var html = '<div class="table-responsive stp-lite-svc-added-wrap"><table class="table table-sm align-middle mb-0 stp-lite-svc-added-table"><thead><tr>' +
            '<th>Restaurant</th><th>Meal / Dish</th><th>Guests</th><th class="text-end">Total</th><th>Supplement</th><th class="text-end">Actions</th>' +
            '</tr></thead><tbody>';
        rows.forEach(function (row, idx) {
            var editing = root.__editingIdx === idx;
            var dishName = (row.MealDescription && row.MealDescription[0] && row.MealDescription[0].name) || '—';
            var mealLabel = row.mealTypeLabel || row.mealType || '';
            var xferNote = '';
            var extras = [];
            if (row.transfer_options && row.transfer_options.transfer_required) extras.push('Transfer');
            if (row.guide_options && row.guide_options.guide_required) extras.push('Guide');
            if (extras.length) xferNote = ' · ' + extras.join(' + ');
            var rowTotal = typeof T.serviceRowDisplayTotal === 'function'
                ? T.serviceRowDisplayTotal(row)
                : (row.totalPrice || 0);
            html += '<tr class="' + (editing ? 'is-editing' : '') + '" data-idx="' + idx + '">' +
                '<td><div class="fw-semibold">' + T.esc(row.restaurantName || 'Restaurant') + '</div>' +
                '<small class="text-muted">' + T.esc(row.bookingDate || '') + T.esc(xferNote) + '</small>' +
                T.editingMarkHtml(editing) + '</td>' +
                '<td><small>' + T.esc(mealLabel) + ' · ' + T.esc(dishName) + '</small></td>' +
                '<td><small>' + T.esc(row.adults || 0) + 'A / ' + T.esc(row.children || 0) + 'C</small></td>' +
                '<td class="text-end fw-semibold text-nowrap">' + cur + ' ' + Number(rowTotal || 0).toFixed(2) + '</td>' +
                '<td><div class="form-check mb-0"><input class="form-check-input restaurant-is-supplement" type="checkbox" data-idx="' + idx + '"' +
                (row.supplement || row.is_supplement ? ' checked' : '') + '>' +
                '<label class="form-check-label" style="font-size:0.72rem;">Supplement</label></div></td>' +
                '<td class="text-end">' + T.addedTableActions('restaurant', idx, true) + '</td>' +
                '</tr>';
        });
        html += '</tbody></table></div>';
        host.innerHTML = html;
    }

    function hydrate(root, stay, row) {
        if (!row) return;
        var T = S();
        root.__hydrating = true;
        var dateEl = root.querySelector('.restaurant-date');
        var adultsEl = root.querySelector('.restaurant-adults');
        var childrenEl = root.querySelector('.restaurant-children');
        var rest = root.querySelector('.restaurant-select');
        if (dateEl) dateEl.value = row.bookingDate || stay.start || '';
        if (adultsEl) adultsEl.value = String(row.adults || 0);
        if (childrenEl) childrenEl.value = String(row.children || 0);
        T.setAmPmValue(root, 'restaurant', row.visitTime || '');

        function finish() {
            var dish = root.querySelector('.restaurant-dish');
            var mealId = row.MealDescription && row.MealDescription[0] && row.MealDescription[0].meal_id;
            if (dish && mealId) dish.value = String(mealId);
            var mealOnly = Number(row.totalPrice != null ? row.totalPrice : (row.grand_total || 0)) || 0;
            var displayTotal = typeof T.serviceRowDisplayTotal === 'function'
                ? T.serviceRowDisplayTotal(row)
                : mealOnly;
            root.__lastPrice = {
                total: displayTotal,
                mealTotal: mealOnly,
                transferTotal: (row.transfer_options && Number(row.transfer_options.cost)) || 0,
                guideTotal: (row.guide_options && Number(row.guide_options.total_price)) || 0,
                adultPrice: row.adult_price || 0,
                childPrice: row.child_price || 0,
                breakdown: ''
            };
            var cur = root.getAttribute('data-currency') || 'SGD';
            var panel = root.querySelector('[data-restaurant-price-panel]');
            var totalEl = root.querySelector('.restaurant-price-total');
            if (panel) panel.classList.remove('d-none');
            if (totalEl) totalEl.textContent = cur + ' ' + Number(displayTotal || 0).toFixed(2);
            var add = root.querySelector('.restaurant-add-btn');
            if (add) add.disabled = false;
            root.__hydrating = false;
        }

        var extras = Promise.all([
            T.hydrateTransferExtras(root, PREFIX, row.transfer_options, stay),
            T.hydrateGuideExtras(root, PREFIX, row.guide_options, stay)
        ]).then(function () {
            if ((row.is_multi_restaurant || isMultiRestaurantValue(row.restaurantId)) && typeof T.applyMasterGuideVehicle === 'function') {
                var opt = rest && rest.options[rest.selectedIndex];
                T.applyMasterGuideVehicle(
                    root,
                    PREFIX,
                    row.vehicle_included || (opt && opt.dataset && opt.dataset.vehicleIncluded === '1'),
                    row.guide_included || (opt && opt.dataset && opt.dataset.guideIncluded === '1'),
                    { preserve: true }
                );
            }
        });

        if (rest && row.restaurantId) {
            var restVal = String(row.restaurantId);
            if ((row.is_multi_restaurant || row.multi_restaurant_id) && restVal.indexOf('multi_restaurant_') !== 0) {
                restVal = 'multi_restaurant_' + (row.multi_restaurant_id || restVal);
            }
            rest.value = restVal;
            fillMealTypes(root, restVal, stay);
            var mealType = root.querySelector('.restaurant-meal-type');
            var period = mapMealPeriod(row.mealType || '');
            if (mealType && period) mealType.value = period;
            Promise.all([
                loadMeals(root, stay, restVal, period || row.mealType || ''),
                extras
            ]).then(finish);
        } else {
            extras.then(finish);
        }
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
        var rest = root.querySelector('.restaurant-select');
        if (rest) S().clearSelectOrInput(rest, '');
        var mealType = root.querySelector('.restaurant-meal-type');
        if (mealType) {
            mealType.innerHTML = '<option value="">Select restaurant</option>';
            mealType.disabled = true;
            S().clearSelectOrInput(mealType, '');
        }
        var dish = root.querySelector('.restaurant-dish');
        if (dish) {
            dish.innerHTML = '<option value="">Select meal type</option>';
            dish.disabled = true;
            S().clearSelectOrInput(dish, '');
        }
        S().resetTransferGuideExtras(root, PREFIX);
        S().setAmPmValue(root, 'restaurant', '');
        // Keep Get Price enabled so user can search/add another item
        var getBtn = root.querySelector('.restaurant-get-price-btn');
        if (getBtn) getBtn.disabled = false;
        var addBtn = root.querySelector('.restaurant-add-btn');
        if (addBtn) addBtn.disabled = true;
    }

    function bindShell(root, stay) {
        if (!root || root.__bound) return;
        root.__bound = true;
        var T = S();
        T.bindAmPm(root);
        T.bindStayDate(root.querySelector('.restaurant-date'), stay, function () { invalidate(root); });
        T.bindGuestCaps(root);
        loadRestaurants(root, stay);

        T.bindTransferExtras(root, stay, PREFIX, function () {
            return guestCounts(root);
        }, function () { if (!root.__hydrating) invalidate(root); });
        T.bindGuideExtras(root, stay, PREFIX, function () { if (!root.__hydrating) invalidate(root); });

        var rest = root.querySelector('.restaurant-select');
        var mealType = root.querySelector('.restaurant-meal-type');
        if (rest) {
            rest.addEventListener('change', function () {
                if (!root.__hydrating) invalidate(root);
                fillMealTypes(root, rest.value, stay);
            });
        }
        if (mealType) {
            mealType.addEventListener('change', function () {
                if (!root.__hydrating) invalidate(root);
                loadMeals(root, stay, rest ? rest.value : '', mealType.value);
            });
        }

        root.querySelector('.restaurant-get-price-btn').addEventListener('click', function () { getPrice(root); });
        root.querySelector('.restaurant-add-btn').addEventListener('click', function () { addRow(root, stay); });

        root.querySelectorAll('.restaurant-dish, .restaurant-adults, .restaurant-children').forEach(function (el) {
            el.addEventListener('change', function () {
                if (!root.__hydrating) invalidate(root);
                var g = guestCounts(root);
                T.refreshTransferCostDisplay(root, PREFIX, g.adults, g.children, g.infants);
            });
            el.addEventListener('input', function () {
                if (!root.__hydrating) invalidate(root);
                var g = guestCounts(root);
                T.refreshTransferCostDisplay(root, PREFIX, g.adults, g.children, g.infants);
            });
        });
        root.addEventListener('stp:time-changed', function () { if (!root.__hydrating) invalidate(root); });

        root.addEventListener('change', function (e) {
            var chk = e.target.closest('.restaurant-is-supplement');
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
            var removeBtn = e.target.closest('.restaurant-remove');
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
            var editBtn = e.target.closest('.restaurant-edit-added');
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
            var viewBtn = e.target.closest('.restaurant-view-breakup');
            if (viewBtn) {
                var r = readChunk(root)[parseInt(viewBtn.getAttribute('data-idx'), 10) || 0];
                if (!r) return;
                var dishName = (r.MealDescription && r.MealDescription[0] && r.MealDescription[0].name) || '';
                var xfer = r.transfer_options || {};
                var guide = r.guide_options || {};
                var cur = r.currency || root.getAttribute('data-currency') || 'SGD';
                var mealAmt = (Number(r.adults || 0) * Number(r.adult_price || 0))
                    + (Number(r.children || 0) * Number(r.child_price || 0));
                var detailRows = '';
                detailRows += '<div class="small text-muted mb-1">' + T.esc(r.mealTypeLabel || r.mealType || '')
                    + (dishName ? ' · ' + T.esc(dishName) : '')
                    + (r.visitTime ? ' · ' + T.esc(r.visitTime) : '') + '</div>';
                detailRows += T.priceFormulaRowHtml(
                    '<strong>Meal</strong> ' + T.esc(r.adults || 0) + 'A × ' + Number(r.adult_price || 0).toFixed(2)
                        + (r.children ? ' + ' + T.esc(r.children) + 'C × ' + Number(r.child_price || 0).toFixed(2) : ''),
                    cur + ' ' + mealAmt.toFixed(2)
                );
                if (xfer.transfer_required) {
                    detailRows += T.priceFormulaRowHtml(
                        '<strong>Transfer</strong> (' + T.esc(xfer.type || '') + ')',
                        cur + ' ' + Number(xfer.cost || 0).toFixed(2)
                    );
                }
                if (guide.guide_required) {
                    detailRows += T.priceFormulaRowHtml(
                        '<strong>Guide</strong>',
                        cur + ' ' + Number(guide.total_price || 0).toFixed(2)
                    );
                }
                T.showPriceBreakdownModal(
                    r.restaurantName || 'Restaurant',
                    cur,
                    (typeof T.serviceRowDisplayTotal === 'function' ? T.serviceRowDisplayTotal(r) : r.totalPrice),
                    detailRows
                );
            }
        });

        renderAdded(root);
        T.updateServiceHeaderTotal(root, 'restaurant');
    }

    function mountAll(scope) {
        var T = S();
        (scope || document).querySelectorAll('[data-stp-restaurant-mount]').forEach(function (el) {
            var stay = T.stayFromPanel(el, 'restaurant');
            el.innerHTML = shellHtml(stay);
            bindShell(el.querySelector('.stp-lite-restaurant'), stay);
        });
        if (window.StpLiteGuestCaps) window.StpLiteGuestCaps.refreshGuestDependentUI();
    }

    window.StpLiteRestaurant = {
        mountAll: mountAll,
        seedAdded: function (root, rows) {
            if (!root) return;
            writeChunk(root, rows || []);
            renderAdded(root);
        }
    };
})(window, document);
/* === END restaurant.js === */
