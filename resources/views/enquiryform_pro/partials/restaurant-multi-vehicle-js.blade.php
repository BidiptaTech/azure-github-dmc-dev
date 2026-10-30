{{-- Restaurant Meal Details: multi-vehicle transfer panel (same pax/Shared/Private flow as attraction).
     Included INSIDE create/edit main <script> — do not wrap with script tags. --}}
(function () {
    'use strict';

    var REST_MV_SIDE = 'rest:meal';
    var REST_MV_UID = 'meal';

    function isRestMvSide(side) {
        return side === REST_MV_SIDE || (typeof side === 'string' && side.indexOf('rest:') === 0);
    }

    function ensureRestMvStyles() {
        if (typeof ensureAttractionMvStyles === 'function') {
            try { ensureAttractionMvStyles(); } catch (e) { /* ignore */ }
        }
        var css = document.getElementById('restaurant-mv-accordion-css');
        if (!css) {
            css = document.createElement('style');
            css.id = 'restaurant-mv-accordion-css';
            document.head.appendChild(css);
        }
        css.textContent = [
            '#restaurantMvPanelHost .attraction-mv-panel{margin-top:6px;}',
            '#restaurantTransferDetailsSection .restaurant-mv-legacy-sync{display:none!important;}'
        ].join('');
    }

    function getRestaurantMvPax() {
        var checked = document.querySelector('#mealsTableBody .meal-checkbox:checked');
        var row = checked ? checked.closest('tr') : document.querySelector('#mealsTableBody tr');
        if (row) {
            return {
                adults: Math.max(0, parseInt(row.querySelector('.meal-adult-qty')?.value || '0', 10) || 0),
                child: Math.max(0, parseInt(row.querySelector('.meal-child-qty')?.value || '0', 10) || 0),
                infant: Math.max(0, parseInt(row.querySelector('.meal-infant-qty')?.value || '0', 10) || 0)
            };
        }
        return {
            adults: parseInt(document.getElementById('adultCountInput')?.value || '0', 10) || 0,
            child: parseInt(document.getElementById('childCountInput')?.value || '0', 10) || 0,
            infant: 0
        };
    }

    function resolveVehicleOptionSeats(opt) {
        if (!opt) return 0;
        var seats = parseInt(
            opt.getAttribute('data-seating')
            || opt.getAttribute('data-city-tour-seating')
            || opt.dataset?.seating
            || '0',
            10
        ) || 0;
        if (!seats) {
            var m = String(opt.textContent || opt.text || '').match(/\((\d+)\s*seats?\)/i);
            if (m) seats = parseInt(m[1], 10) || 0;
        }
        return seats;
    }

    function getRestaurantVehicleOptionsHtml() {
        var legacy = document.getElementById('restaurantTransferVehicleType');
        if (legacy && legacy.innerHTML && legacy.options && legacy.options.length > 1) {
            return legacy.innerHTML;
        }
        if (typeof getVehicleOptionsHTML === 'function') {
            return getVehicleOptionsHTML();
        }
        return '<option value="">Select Vehicle</option>';
    }

    function getRestaurantName() {
        var sel = document.getElementById('mealRestaurant');
        if (sel && sel.value && sel.selectedOptions && sel.selectedOptions[0]) {
            return sel.selectedOptions[0].getAttribute('data-name')
                || sel.selectedOptions[0].text
                || 'Restaurant';
        }
        return 'Restaurant';
    }

    function refreshRestMvItemHeader(row, idx) {
        if (!row) return;
        var sel = row.querySelector('.arr-dep-vehicle-select');
        var opt = sel?.selectedOptions?.[0];
        var seats = resolveVehicleOptionSeats(opt);
        var qty = Math.max(1, parseInt(row.querySelector('.arr-dep-vehicle-qty')?.value || '1', 10) || 1);
        var adults = Math.max(0, parseInt(row.querySelector('.arr-dep-vehicle-adults')?.value || '0', 10) || 0);
        var child = Math.max(0, parseInt(row.querySelector('.arr-dep-vehicle-child')?.value || '0', 10) || 0);
        var typeVal = row.querySelector('.arr-dep-vehicle-xfer-type')?.value || 'S';
        var wayVal = row.querySelector('.arr-dep-vehicle-way')?.value || 'one-way';
        var sell = parseFloat(row.querySelector('.arr-dep-line-sell')?.textContent || '0') || 0;
        var nameEl = row.querySelector('[data-mv-item-name]');
        var seatsEl = row.querySelector('.arr-dep-vehicle-seats');
        var tagsEl = row.querySelector('[data-mv-item-tags]');
        var sellEl = row.querySelector('[data-mv-item-sell]');
        var idxEl = row.querySelector('.mv-row-index, .attraction-mv-item-idx');
        if (idxEl) idxEl.textContent = String((idx != null ? idx : 0) + 1);
        if (seatsEl) seatsEl.textContent = String(seats);
        if (nameEl) {
            var label = sel?.value
                ? String(opt?.text || 'Vehicle').replace(/\s*\(\d+\s*seats?\)\s*$/i, '').trim()
                : 'Select vehicle';
            nameEl.textContent = 'Vehicle ' + ((idx != null ? idx : 0) + 1) + ' · ' + label;
        }
        if (tagsEl) {
            var cap = seats * qty;
            var assigned = adults + child;
            var seatClass = seats > 0 ? 'is-seats' : 'is-warn';
            tagsEl.innerHTML = ''
                + '<span class="attraction-mv-tag ' + seatClass + '"><i class="ri-user-line"></i> '
                + seats + ' seats' + (qty > 1 ? (' × ' + qty) : '') + '</span>'
                + '<span class="attraction-mv-tag">' + (typeVal === 'P' ? 'Private' : 'Shared') + '</span>'
                + '<span class="attraction-mv-tag">' + (wayVal === 'one-way' ? '1-Way' : '2-Way') + '</span>'
                + '<span class="attraction-mv-tag ' + (assigned > 0 ? 'is-ok' : '') + '">'
                + assigned + ' pax assigned</span>'
                + (cap > 0 && assigned > cap
                    ? '<span class="attraction-mv-tag is-warn">Over capacity</span>'
                    : '');
        }
        if (sellEl) sellEl.textContent = sell.toFixed(2);
    }

    // ---- Patch A/D getters so rest:meal reuses the same vehicle row engine ----
    if (typeof window.getArrDepVehicleRowsContainer === 'function') {
        var _rows = window.getArrDepVehicleRowsContainer;
        window.getArrDepVehicleRowsContainer = function (side) {
            if (isRestMvSide(side)) {
                return document.getElementById('restaurantVehicleRows');
            }
            return _rows(side);
        };
    }
    if (typeof window.getArrDepVehicleMasterSelect === 'function') {
        var _master = window.getArrDepVehicleMasterSelect;
        window.getArrDepVehicleMasterSelect = function (side) {
            if (isRestMvSide(side)) {
                return document.getElementById('restaurantVehicleMaster');
            }
            return _master(side);
        };
    }
    if (typeof window.getArrDepTransferType === 'function') {
        var _tt = window.getArrDepTransferType;
        window.getArrDepTransferType = function (side) {
            if (isRestMvSide(side)) {
                var container = document.getElementById('restaurantVehicleRows');
                var fromVehicle = container?.querySelector('.arr-dep-vehicle-xfer-type')?.value;
                var legacy = document.getElementById('restaurantTransferType')?.value;
                var raw = fromVehicle || legacy || 'S';
                return (typeof normalizeTransferTypePS === 'function')
                    ? normalizeTransferTypePS(raw)
                    : raw;
            }
            return _tt(side);
        };
    }
    if (typeof window.getArrDepHeaderPax === 'function') {
        var _hp = window.getArrDepHeaderPax;
        window.getArrDepHeaderPax = function (side) {
            if (isRestMvSide(side)) return getRestaurantMvPax();
            return _hp(side);
        };
    }
    if (typeof window.getArrDepTourPax === 'function') {
        var _tp = window.getArrDepTourPax;
        window.getArrDepTourPax = function (side) {
            if (isRestMvSide(side)) {
                var p = getRestaurantMvPax();
                return Math.max(0, (p.adults || 0) + (p.child || 0));
            }
            return _tp(side);
        };
    }
    if (typeof window.updateArrDepVehicleCoverage === 'function') {
        var _cov = window.updateArrDepVehicleCoverage;
        window.updateArrDepVehicleCoverage = function (side) {
            if (!isRestMvSide(side)) return _cov(side);
            var wrap = document.getElementById('restaurantVehicleCoverage');
            if (!wrap) return;
            var tourPax = window.getArrDepTourPax(REST_MV_SIDE);
            var container = window.getArrDepVehicleRowsContainer(REST_MV_SIDE);
            var rows = container
                ? Array.from(container.querySelectorAll('.arr-dep-vehicle-row'))
                : [];
            var covered = 0;
            var seatCap = 0;
            rows.forEach(function (row, idx) {
                var sel = row.querySelector('.arr-dep-vehicle-select');
                var opt = sel?.selectedOptions?.[0];
                var seats = resolveVehicleOptionSeats(opt);
                var qty = Math.max(1, parseInt(row.querySelector('.arr-dep-vehicle-qty')?.value || '1', 10) || 1);
                var adults = Math.max(0, parseInt(row.querySelector('.arr-dep-vehicle-adults')?.value || '0', 10) || 0);
                var child = Math.max(0, parseInt(row.querySelector('.arr-dep-vehicle-child')?.value || '0', 10) || 0);
                if (sel && sel.value) {
                    covered += adults + child;
                    seatCap += seats * qty;
                }
                refreshRestMvItemHeader(row, idx);
                if (typeof recalcArrDepVehicleRowTotals === 'function') {
                    recalcArrDepVehicleRowTotals(REST_MV_SIDE, row);
                }
            });
            if (covered <= 0 && seatCap > 0) covered = Math.min(tourPax, seatCap);
            var remaining = Math.max(0, tourPax - covered);
            var totalEl = wrap.querySelector('[data-mv-total]');
            var coveredEl = wrap.querySelector('[data-mv-covered]');
            var remainingEl = wrap.querySelector('[data-mv-remaining]');
            var banner = wrap.querySelector('[data-mv-banner]');
            var coveredCard = wrap.querySelector('[data-mv-card="covered"]');
            var remainCard = wrap.querySelector('[data-mv-card="remaining"]');
            if (totalEl) totalEl.textContent = String(tourPax);
            if (coveredEl) coveredEl.textContent = String(covered);
            if (remainingEl) remainingEl.textContent = String(remaining);
            if (coveredCard) coveredCard.classList.toggle('is-ok', covered >= tourPax && rows.some(function (r) {
                return r.querySelector('.arr-dep-vehicle-select')?.value;
            }));
            if (remainCard) {
                remainCard.classList.toggle('is-warn', remaining > 0);
                remainCard.classList.toggle('is-ok', remaining === 0 && covered > 0);
            }
            wrap.style.display = '';
            if (banner) {
                banner.className = 'mv-coverage-banner';
                if (tourPax <= 0) {
                    banner.classList.add('is-muted');
                    banner.innerHTML = '<i class="ri-information-line"></i> Set adults/child on the meal row to calculate coverage.';
                } else if (!rows.some(function (r) { return r.querySelector('.arr-dep-vehicle-select')?.value; })) {
                    banner.classList.add('is-muted');
                    banner.innerHTML = '<i class="ri-information-line"></i> Select a vehicle to cover guests.';
                } else if (remaining > 0) {
                    banner.classList.add('is-warn');
                    banner.innerHTML = '<i class="ri-error-warning-line"></i> ' + remaining
                        + ' guest' + (remaining === 1 ? '' : 's')
                        + ' remaining. Add another vehicle or increase quantity.';
                } else {
                    banner.classList.add('is-ok');
                    banner.innerHTML = '<i class="ri-checkbox-circle-line"></i> All guests are covered.';
                }
            }
            syncLegacyRestaurantTransferSelects();
        };
    }

    function buildRestMvItemHtml(side, selectedId, qty, transferType, paxOpts) {
        var master = (typeof getArrDepVehicleMasterSelect === 'function')
            ? getArrDepVehicleMasterSelect(side)
            : null;
        var optsHtml = master ? master.innerHTML : getRestaurantVehicleOptionsHtml();
        var q = Math.max(1, parseInt(qty || 1, 10) || 1);
        var tt = (typeof normalizeTransferTypePS === 'function')
            ? normalizeTransferTypePS(transferType || 'S')
            : (transferType || 'S');
        var sSel = tt === 'S' ? ' selected' : '';
        var pSel = tt === 'P' ? ' selected' : '';
        paxOpts = paxOpts || {};
        var aVal = Math.max(0, parseInt(paxOpts.adults ?? paxOpts.adultsQty ?? 0, 10) || 0);
        var cVal = Math.max(0, parseInt(paxOpts.child ?? paxOpts.childQty ?? 0, 10) || 0);
        var iVal = Math.max(0, parseInt(paxOpts.infant ?? paxOpts.infantQty ?? 0, 10) || 0);
        var defaultWay = paxOpts.way || 'one-way';
        var oneSel = (defaultWay === 'one-way') ? ' selected' : '';
        var bothSel = (defaultWay !== 'one-way') ? ' selected' : '';
        return ''
            + '<div class="attraction-mv-item arr-dep-vehicle-row is-open">'
            +   '<button type="button" class="attraction-mv-item-toggle" onclick="toggleAttractionMvItem(this)">'
            +     '<div class="attraction-mv-item-main">'
            +       '<span class="attraction-mv-item-idx mv-row-index">1</span>'
            +       '<div class="attraction-mv-item-meta">'
            +         '<div class="attraction-mv-item-name" data-mv-item-name>Vehicle 1 · Select vehicle</div>'
            +         '<div class="attraction-mv-item-tags" data-mv-item-tags>'
            +           '<span class="attraction-mv-tag is-warn"><i class="ri-user-line"></i> 0 seats</span>'
            +         '</div>'
            +       '</div>'
            +     '</div>'
            +     '<div class="attraction-mv-item-right">'
            +       '<span class="attraction-mv-item-sell" data-mv-item-sell>0.00</span>'
            +       '<i class="ri-arrow-down-s-line attraction-mv-chevron"></i>'
            +     '</div>'
            +   '</button>'
            +   '<div class="attraction-mv-item-body">'
            +     '<div class="attraction-mv-grid">'
            +       '<div class="attraction-mv-field attraction-mv-field--vehicle"><label>Vehicle</label>'
            +         '<select class="form-select form-select-sm arr-dep-vehicle-select">' + optsHtml + '</select></div>'
            +       '<div class="attraction-mv-field attraction-mv-field--seats"><label>Seats</label>'
            +         '<span class="mv-seats-badge"><i class="ri-user-line"></i><span class="arr-dep-vehicle-seats">0</span></span></div>'
            +       '<div class="attraction-mv-field attraction-mv-field--type"><label>Type</label>'
            +         '<select class="form-select form-select-sm arr-dep-vehicle-xfer-type">'
            +           '<option value="S"' + sSel + '>Shared</option>'
            +           '<option value="P"' + pSel + '>Private</option>'
            +         '</select></div>'
            +       '<div class="attraction-mv-field attraction-mv-field--way"><label>Way</label>'
            +         '<select class="form-select form-select-sm arr-dep-vehicle-way">'
            +           '<option value="one-way"' + oneSel + '>1-Way</option>'
            +           '<option value="both-way"' + bothSel + '>2-Way</option>'
            +         '</select></div>'
            +       '<div class="attraction-mv-field attraction-mv-field--qty"><label>Qty</label>'
            +         '<div class="mv-qty-stepper">'
            +           '<button type="button" class="arr-dep-qty-minus" aria-label="Decrease">−</button>'
            +           '<input type="number" class="arr-dep-vehicle-qty" min="1" step="1" value="' + q + '">'
            +           '<button type="button" class="arr-dep-qty-plus" aria-label="Increase">+</button>'
            +         '</div></div>'
            +       '<div class="attraction-mv-field attraction-mv-field--pax"><label>Adults</label>'
            +         '<input type="number" min="0" step="1" class="form-control form-control-sm arr-dep-vehicle-adults" value="' + aVal + '"></div>'
            +       '<div class="attraction-mv-field attraction-mv-field--pax"><label>Child</label>'
            +         '<input type="number" min="0" step="1" class="form-control form-control-sm arr-dep-vehicle-child" value="' + cVal + '"></div>'
            +       '<div class="attraction-mv-field attraction-mv-field--pax"><label>Infant</label>'
            +         '<input type="number" min="0" step="1" class="form-control form-control-sm arr-dep-vehicle-infant" value="' + iVal + '"></div>'
            +       '<div class="attraction-mv-field attraction-mv-field--price"><label>Cost</label>'
            +         '<input type="number" step="0.01" min="0" class="form-control form-control-sm mv-unit-input arr-dep-unit-cost" value="0"></div>'
            +       '<div class="attraction-mv-field attraction-mv-field--price"><label>Sell</label>'
            +         '<input type="number" step="0.01" min="0" class="form-control form-control-sm mv-unit-input arr-dep-unit-sell" value="0"></div>'
            +       '<div class="attraction-mv-field attraction-mv-field--total"><label>T.Cost</label>'
            +         '<span class="mv-total-cost arr-dep-line-cost">0.00</span></div>'
            +       '<div class="attraction-mv-field attraction-mv-field--total"><label>T.Sell</label>'
            +         '<span class="mv-total-sell arr-dep-line-sell">0.00</span></div>'
            +       '<div class="attraction-mv-field attraction-mv-field--action"><label>&nbsp;</label>'
            +         '<button type="button" class="btn btn-sm btn-outline-danger arr-dep-vehicle-remove" title="Remove">'
            +           '<i class="ri-delete-bin-line"></i></button></div>'
            +     '</div>'
            +   '</div>'
            + '</div>';
    }

    window.addRestaurantMvVehicle = function (selectedId, qty, transferType, paxOpts) {
        var panel = document.querySelector('#restaurantMvPanelHost .attraction-mv-panel');
        if (panel) {
            panel.classList.remove('is-collapsed');
            var collapseBtn = panel.querySelector('.attr-mv-collapse-btn span');
            if (collapseBtn) collapseBtn.textContent = 'Vehicles';
        }
        return window.addArrDepVehicleRow(REST_MV_SIDE, selectedId, qty, transferType, paxOpts);
    };

    (function patchAddArrDepVehicleRowForRest() {
        var _addRow = (typeof window.addArrDepVehicleRow === 'function')
            ? window.addArrDepVehicleRow
            : null;
        window.addArrDepVehicleRow = function (side, selectedId, qty, transferType, paxOpts) {
            if (!isRestMvSide(side)) {
                return _addRow ? _addRow(side, selectedId, qty, transferType, paxOpts) : null;
            }
            try {
                var container = document.getElementById('restaurantVehicleRows');
                if (!container) {
                    console.warn('Restaurant MV: vehicle container not found');
                    return null;
                }
                var wrap = document.createElement('div');
                wrap.innerHTML = buildRestMvItemHtml(side, selectedId, qty, transferType, paxOpts);
                var row = wrap.firstElementChild;
                if (!row) return null;
                container.appendChild(row);
                Array.from(container.querySelectorAll('.attraction-mv-item, .arr-dep-vehicle-row')).forEach(function (item, i) {
                    item.style.zIndex = '';
                    item.style.position = 'static';
                    item.classList.toggle('is-open', item === row);
                    var idxEl = item.querySelector('.mv-row-index, .attraction-mv-item-idx');
                    if (idxEl) idxEl.textContent = String(i + 1);
                    refreshRestMvItemHeader(item, i);
                });
                var sel = row.querySelector('.arr-dep-vehicle-select');
                var typeEl = row.querySelector('.arr-dep-vehicle-xfer-type');
                if (typeEl && transferType) {
                    typeEl.value = (typeof normalizeTransferTypePS === 'function')
                        ? normalizeTransferTypePS(transferType) : transferType;
                }
                if (sel && selectedId) {
                    sel.value = String(selectedId);
                    if (!sel.value) {
                        var match = Array.from(sel.options).find(function (o) {
                            return String(o.value) === String(selectedId);
                        });
                        if (match) sel.value = match.value;
                    }
                }
                var wayEl = row.querySelector('.arr-dep-vehicle-way');
                if (wayEl && paxOpts && paxOpts.way) {
                    wayEl.value = paxOpts.way;
                }
                window._arrDepVehicleForceCity = String(document.getElementById('mealDestination')?.value || '').trim();
                if (typeof applyArrDepVehicleFilterToSelect === 'function') {
                    applyArrDepVehicleFilterToSelect(side, sel);
                }
                if (typeof bindArrDepVehicleRowEvents === 'function') {
                    bindArrDepVehicleRowEvents(side, row);
                }
                if (paxOpts && (paxOpts.adults != null || paxOpts.child != null || paxOpts.infant != null)) {
                    if (typeof writeArrDepRowPaxInputs === 'function') {
                        writeArrDepRowPaxInputs(row, paxOpts.adults, paxOpts.child, paxOpts.infant);
                    }
                    if (typeof clampArrDepRowPaxToCapacity === 'function') clampArrDepRowPaxToCapacity(row, side);
                    if (typeof syncAllMultiVehiclePaxLimits === 'function') syncAllMultiVehiclePaxLimits(side);
                } else if (typeof autoFillArrDepRowPax === 'function') {
                    autoFillArrDepRowPax(side, row);
                }
                if (typeof syncMasterTransferTypeFromRows === 'function') syncMasterTransferTypeFromRows(side);
                if (typeof syncArrDepPrimaryVehicleSelect === 'function') syncArrDepPrimaryVehicleSelect(side);
                if (typeof updateArrDepVehicleCoverage === 'function') updateArrDepVehicleCoverage(side);
                scheduleRestaurantMvPriceRefresh();
                return row;
            } catch (err) {
                console.error('Restaurant MV add vehicle failed', err);
                return null;
            }
        };
    })();

    if (typeof window.bindArrDepVehicleRowEvents === 'function') {
        var _bind = window.bindArrDepVehicleRowEvents;
        window.bindArrDepVehicleRowEvents = function (side, row) {
            _bind(side, row);
            if (!isRestMvSide(side)) return;
            var refresh = function () {
                if (typeof clampArrDepRowPaxToCapacity === 'function') {
                    clampArrDepRowPaxToCapacity(row, side);
                }
                if (typeof syncAllMultiVehiclePaxLimits === 'function') {
                    syncAllMultiVehiclePaxLimits(side);
                }
                scheduleRestaurantMvPriceRefresh();
                if (typeof updateArrDepVehicleCoverage === 'function') updateArrDepVehicleCoverage(side);
            };
            row.querySelectorAll(
                '.arr-dep-vehicle-select, .arr-dep-vehicle-qty, .arr-dep-vehicle-xfer-type, '
                + '.arr-dep-vehicle-way, '
                + '.arr-dep-vehicle-adults, .arr-dep-vehicle-child, .arr-dep-vehicle-infant, '
                + '.arr-dep-unit-cost, .arr-dep-unit-sell'
            ).forEach(function (el) {
                el.addEventListener('change', refresh);
                el.addEventListener('input', refresh);
            });
            row.querySelector('.arr-dep-qty-minus')?.addEventListener('click', refresh);
            row.querySelector('.arr-dep-qty-plus')?.addEventListener('click', refresh);
            row.querySelector('.arr-dep-vehicle-remove')?.addEventListener('click', function () {
                setTimeout(function () {
                    if (typeof syncAllMultiVehiclePaxLimits === 'function') syncAllMultiVehiclePaxLimits(side);
                    refresh();
                }, 10);
            });
            if (typeof syncAllMultiVehiclePaxLimits === 'function') syncAllMultiVehiclePaxLimits(side);
        };
    }

    document.addEventListener('click', function (ev) {
        var btn = ev.target?.closest?.('#restaurantVehicleRows .arr-dep-vehicle-remove');
        if (!btn) return;
        var item = btn.closest('.attraction-mv-item');
        if (!item) return;
        setTimeout(function () {
            if (item.parentElement) item.remove();
            if (typeof updateArrDepVehicleCoverage === 'function') {
                updateArrDepVehicleCoverage(REST_MV_SIDE);
            }
            scheduleRestaurantMvPriceRefresh();
        }, 0);
    }, true);

    function buildRestaurantTransferPanelHtml(restaurantName, vehicleOptionsHtml) {
        ensureRestMvStyles();
        var name = String(restaurantName || 'Restaurant').replace(/</g, '&lt;');
        var opts = vehicleOptionsHtml || getRestaurantVehicleOptionsHtml();
        return ''
            + '<div class="attraction-mv-panel" data-unique-id="' + REST_MV_UID + '">'
            +   '<div class="attr-mv-head">'
            +     '<div class="attr-mv-head-left">'
            +       '<h6 class="attr-mv-title"><i class="ri-car-line"></i> Transfer Vehicles</h6>'
            +       '<p class="attr-mv-sub">for <strong>' + name + '</strong> · add vehicles by pax</p>'
            +       '<label class="attr-mv-include">'
            +         '<input type="checkbox" class="form-check-input restaurant-transfer-included" checked'
            +         ' onchange="onRestaurantTransferIncludedToggle(this)"> Include</label>'
            +     '</div>'
            +     '<div class="attr-mv-head-actions">'
            +       '<button type="button" class="attr-mv-collapse-btn" title="Collapse / expand vehicles"'
            +         ' onclick="toggleAttractionMvPanel(this)">'
            +         '<i class="ri-arrow-down-s-line"></i><span>Vehicles</span></button>'
            +       '<button type="button" class="btn btn-sm btn-primary attr-mv-add-btn" style="font-size:10px;white-space:nowrap;padding:3px 8px;"'
            +         ' onclick="addRestaurantMvVehicle()">'
            +         '<i class="ri-add-line"></i> Add Vehicle</button>'
            +     '</div>'
            +   '</div>'
            +   '<div class="attr-mv-body">'
            +     '<div id="restaurantVehicleRows" class="attraction-mv-accordion arr-dep-vehicle-rows"></div>'
            +     '<div id="restaurantVehicleCoverage" class="mv-coverage-wrap" style="display:none;">'
            +       '<div class="mv-coverage-cards">'
            +         '<div class="mv-coverage-card" data-mv-card="total">'
            +           '<div class="mv-card-label"><i class="ri-group-line"></i> Total Pax</div>'
            +           '<div class="mv-card-value" data-mv-total>0</div></div>'
            +         '<div class="mv-coverage-card" data-mv-card="covered">'
            +           '<div class="mv-card-label"><i class="ri-checkbox-circle-line"></i> Covered</div>'
            +           '<div class="mv-card-value" data-mv-covered>0</div></div>'
            +         '<div class="mv-coverage-card" data-mv-card="remaining">'
            +           '<div class="mv-card-label"><i class="ri-user-unfollow-line"></i> Remaining</div>'
            +           '<div class="mv-card-value" data-mv-remaining>0</div></div>'
            +       '</div>'
            +       '<div class="mv-coverage-banner" data-mv-banner></div>'
            +     '</div>'
            +   '</div>'
            +   '<select class="d-none" id="restaurantVehicleMaster" aria-hidden="true" tabindex="-1">'
            +     opts
            +   '</select>'
            + '</div>';
    }

    window.onRestaurantTransferIncludedToggle = function (el) {
        var cb = document.getElementById('restaurantTransferCheckbox');
        if (!cb) return;
        cb.checked = !!el.checked;
        if (typeof toggleRestaurantTransferFields === 'function') {
            toggleRestaurantTransferFields();
        }
    };

    function syncLegacyRestaurantTransferSelects() {
        var container = document.getElementById('restaurantVehicleRows');
        var first = container?.querySelector('.arr-dep-vehicle-row');
        if (!first) return;
        var sel = first.querySelector('.arr-dep-vehicle-select');
        var typeEl = first.querySelector('.arr-dep-vehicle-xfer-type');
        var wayEl = first.querySelector('.arr-dep-vehicle-way');
        var legacyVeh = document.getElementById('restaurantTransferVehicleType');
        var legacyType = document.getElementById('restaurantTransferType');
        var legacyWay = document.getElementById('restaurantTransferWay');
        if (legacyVeh && sel && sel.value) {
            legacyVeh.value = sel.value;
        }
        if (legacyType && typeEl) {
            legacyType.value = typeEl.value || 'S';
        }
        if (legacyWay && wayEl) {
            legacyWay.value = wayEl.value || 'one-way';
        }
    }

    window.ensureRestaurantMvPanel = function (vehicles) {
        ensureRestMvStyles();
        var host = document.getElementById('restaurantMvPanelHost');
        if (!host) return null;
        var name = getRestaurantName();
        var opts = getRestaurantVehicleOptionsHtml();
        var existing = host.querySelector('.attraction-mv-panel');
        var existingVehicles = null;
        if (existing && typeof collectArrDepVehiclesPayload === 'function') {
            try {
                existingVehicles = collectArrDepVehiclesPayload(REST_MV_SIDE);
            } catch (e) { existingVehicles = null; }
        }
        if (!existing || !host.querySelector('#restaurantVehicleRows')) {
            host.innerHTML = buildRestaurantTransferPanelHtml(name, opts);
        } else {
            var sub = host.querySelector('.attr-mv-sub strong');
            if (sub) sub.textContent = name;
            var master = document.getElementById('restaurantVehicleMaster');
            if (master && opts) master.innerHTML = opts;
        }
        window._arrDepVehicleForceCity = String(document.getElementById('mealDestination')?.value || '').trim();
        var container = document.getElementById('restaurantVehicleRows');
        var hasRows = !!(container && container.querySelector('.arr-dep-vehicle-row'));
        var seed = Array.isArray(vehicles) && vehicles.length
            ? vehicles
            : (existingVehicles && existingVehicles.length ? existingVehicles : null);
        // Use window.addArrDepVehicleRow (patched accordion builder) — not the local table-row helper
        if (seed || !hasRows) {
            if (container) container.innerHTML = '';
            var list = seed;
            if (!list || !list.length) {
                var paxSeed = getRestaurantMvPax();
                var legacyVehId = document.getElementById('restaurantTransferVehicleType')?.value || '';
                var legacyType = document.getElementById('restaurantTransferType')?.value || 'S';
                var legacyWay = document.getElementById('restaurantTransferWay')?.value || 'one-way';
                list = [{
                    vehicleId: legacyVehId,
                    qty: 1,
                    transferType: legacyType,
                    way: legacyWay,
                    adults: paxSeed.adults,
                    child: paxSeed.child,
                    infant: paxSeed.infant
                }];
            }
            list.forEach(function (v) {
                if (typeof window.addArrDepVehicleRow === 'function') {
                    window.addArrDepVehicleRow(
                        REST_MV_SIDE,
                        v.vehicleId || v.vehicle_id || '',
                        v.qty || v.quantity || 1,
                        v.transferType || v.type || 'S',
                        {
                            adults: v.adults ?? v.adultsQty,
                            child: v.child ?? v.childQty,
                            infant: v.infant ?? v.infantQty,
                            way: v.way || 'one-way'
                        }
                    );
                }
            });
        }
        if (typeof updateArrDepVehicleCoverage === 'function') {
            updateArrDepVehicleCoverage(REST_MV_SIDE);
        }
        scheduleRestaurantMvPriceRefresh();
        return host.querySelector('.attraction-mv-panel');
    };

    window.resetRestaurantMvPanel = function () {
        var host = document.getElementById('restaurantMvPanelHost');
        if (host) host.innerHTML = '';
        window._restaurantMvResult = null;
    };

    var _restMvTimer = null;
    window.scheduleRestaurantMvPriceRefresh = function () {
        clearTimeout(_restMvTimer);
        _restMvTimer = setTimeout(function () {
            refreshRestaurantMvPrices();
        }, 220);
    };

    window.refreshRestaurantMvPrices = async function () {
        var destSelect = document.getElementById('restaurantTransferDestination');
        var restaurantSelect = document.getElementById('mealRestaurant');
        var restaurantId = restaurantSelect?.value || '';
        var restaurantName = getRestaurantName();
        if (!destSelect || !destSelect.value || !restaurantId) {
            if (typeof updateArrDepVehicleCoverage === 'function') {
                updateArrDepVehicleCoverage(REST_MV_SIDE);
            }
            return null;
        }
        var destOpt = destSelect.selectedOptions?.[0]
            || (window.jQuery ? jQuery(destSelect).find(':selected')[0] : null);
        var destinationType = destOpt?.getAttribute?.('data-type')
            || (window.jQuery ? jQuery(destSelect).find(':selected').attr('data-type') : null)
            || 'hotel';
        var actualDestinationId = destSelect.value;
        if (destinationType === 'port' && destOpt?.getAttribute?.('data-port-id')) {
            actualDestinationId = destOpt.getAttribute('data-port-id');
        } else if (destinationType === 'hotel' && destOpt?.getAttribute?.('data-hotel-unique-id')) {
            actualDestinationId = destOpt.getAttribute('data-hotel-unique-id');
        } else if (destinationType === 'attraction' && destOpt?.getAttribute?.('data-attraction-id')) {
            actualDestinationId = destOpt.getAttribute('data-attraction-id');
        } else if (destinationType === 'restaurant' && destOpt?.getAttribute?.('data-restaurant-id')) {
            actualDestinationId = destOpt.getAttribute('data-restaurant-id');
        }
        var isPickup = !!document.getElementById('restaurantTransferIsPickup')?.checked;
        var pickupId, pickupType, dropoffId, dropoffType, pickupName, dropoffName;
        var destName = destOpt?.getAttribute?.('data-name')
            || (window.jQuery ? jQuery(destSelect).find(':selected').attr('data-name') : null)
            || destOpt?.text
            || '';
        if (isPickup) {
            pickupId = actualDestinationId; pickupType = destinationType; pickupName = destName;
            dropoffId = restaurantId; dropoffType = 'restaurant'; dropoffName = restaurantName;
        } else {
            pickupId = restaurantId; pickupType = 'restaurant'; pickupName = restaurantName;
            dropoffId = actualDestinationId; dropoffType = destinationType; dropoffName = destName;
        }

        var vehicles = (typeof collectArrDepVehiclesPayload === 'function')
            ? collectArrDepVehiclesPayload(REST_MV_SIDE) : [];
        var container = document.getElementById('restaurantVehicleRows');
        var domRows = container ? Array.from(container.querySelectorAll('.arr-dep-vehicle-row')) : [];
        vehicles.forEach(function (v, i) {
            var wayEl = domRows[i]?.querySelector('.arr-dep-vehicle-way');
            v.way = wayEl?.value || v.way || 'one-way';
        });
        var pax = getRestaurantMvPax();
        var dmcId = '{{ $dmc_id ?? "" }}';
        var totalCost = 0, totalSell = 0, storedCost = 0, storedSell = 0;
        var enriched = [];

        for (var i = 0; i < vehicles.length; i++) {
            var v = vehicles[i];
            var zonePrice = { private_price: 0, shared_price: 0, private_cost_price: 0, shared_cost_price: 0 };
            try {
                if (typeof fetchZonePrice === 'function' && v.vehicleId && pickupId && dropoffId && dmcId) {
                    zonePrice = await fetchZonePrice(v.vehicleId, pickupId, pickupType, dropoffId, dropoffType, dmcId);
                }
            } catch (e) { /* non-fatal */ }
            var rowType = (typeof normalizeTransferTypePS === 'function')
                ? normalizeTransferTypePS(v.transferType || v.type || 'S')
                : (v.transferType || 'S');
            var rowShared = rowType === 'S';
            var vAdults = Math.max(0, parseInt(v.adults ?? v.adultsQty ?? 0, 10) || 0);
            var vChild = Math.max(0, parseInt(v.child ?? v.childQty ?? 0, 10) || 0);
            var vInfant = Math.max(0, parseInt(v.infant ?? v.infantQty ?? 0, 10) || 0);
            var vehiclePax = Math.max(0, vAdults + vChild);
            var billPax = vehiclePax > 0 ? vehiclePax : Math.max(0, (pax.adults || 0) + (pax.child || 0));
            var qty = Math.max(1, parseInt(v.qty || 1, 10) || 1);
            var way = v.way || 'one-way';
            var unit = (typeof calculateTransferPrice === 'function')
                ? calculateTransferPrice(zonePrice, rowType, way, vAdults || pax.adults, vChild || pax.child)
                : { cost: 0, sell: 0 };
            var unitCost = parseFloat(unit.cost) || 0;
            var unitSell = parseFloat(unit.sell) || 0;
            var domRow = domRows[i];
            var ucEl = domRow?.querySelector('.arr-dep-unit-cost');
            var usEl = domRow?.querySelector('.arr-dep-unit-sell');
            if (ucEl && ucEl.dataset.userEdited === '1') {
                unitCost = parseFloat(ucEl.value) || unitCost;
            } else if (ucEl) {
                ucEl.value = unitCost.toFixed(2);
            }
            if (usEl && usEl.dataset.userEdited === '1') {
                unitSell = parseFloat(usEl.value) || unitSell;
            } else if (usEl) {
                usEl.value = unitSell.toFixed(2);
            }
            var lineCost = rowShared ? unitCost * billPax : unitCost * qty;
            var lineSell = rowShared ? unitSell * billPax : unitSell * qty;
            totalCost += lineCost;
            totalSell += lineSell;
            if (rowShared) {
                storedCost += unitCost * qty;
                storedSell += unitSell * qty;
            } else {
                storedCost += lineCost;
                storedSell += lineSell;
            }
            if (typeof recalcArrDepVehicleRowTotals === 'function' && domRow) {
                recalcArrDepVehicleRowTotals(REST_MV_SIDE, domRow);
            }
            var seats = resolveVehicleOptionSeats(domRow?.querySelector('.arr-dep-vehicle-select')?.selectedOptions?.[0])
                || parseInt(v.seats || 0, 10) || 0;
            enriched.push({
                vehicleId: v.vehicleId,
                vehicle_id: v.vehicleId,
                vehicleName: v.vehicleName,
                vehicleType: v.vehicleType,
                seats: seats,
                qty: qty,
                quantity: qty,
                transferType: rowType,
                type: rowType,
                way: way,
                adults: vAdults,
                child: vChild,
                infant: vInfant,
                adultsQty: vAdults,
                childQty: vChild,
                infantQty: vInfant,
                unitCost: unitCost,
                unitSell: unitSell,
                lineCost: lineCost,
                lineSell: lineSell,
                zonePrivatePrice: zonePrice.private_price || 0,
                zoneSharedPrice: zonePrice.shared_price || 0,
                zonePrivateCostPrice: zonePrice.private_cost_price || 0,
                zoneSharedCostPrice: zonePrice.shared_cost_price || 0
            });
        }

        if (typeof updateArrDepVehicleCoverage === 'function') updateArrDepVehicleCoverage(REST_MV_SIDE);
        syncLegacyRestaurantTransferSelects();

        var primaryWay = (enriched[0] && enriched[0].way) || 'one-way';
        var result = {
            vehicles: enriched,
            totalCost: totalCost,
            totalSell: totalSell,
            storedCost: storedCost,
            storedSell: storedSell,
            pickupId: pickupId,
            pickupType: pickupType,
            pickupName: pickupName,
            dropoffId: dropoffId,
            dropoffType: dropoffType,
            dropoffName: dropoffName,
            way: primaryWay,
            destinationId: destSelect.value,
            destinationName: destName,
            destinationType: destinationType,
            isDestinationPickup: isPickup,
            restaurantId: restaurantId,
            restaurantName: restaurantName
        };
        window._restaurantMvResult = result;
        return result;
    };

    window.buildRestaurantTransferInfoFromMv = async function (opts) {
        opts = opts || {};
        var transferChecked = document.getElementById('restaurantTransferCheckbox')?.checked;
        var destSelect = document.getElementById('restaurantTransferDestination');
        if (!transferChecked || !destSelect || !destSelect.value) return null;

        window.ensureRestaurantMvPanel();
        var result = window._restaurantMvResult;
        try {
            result = await refreshRestaurantMvPrices() || result;
        } catch (e) {
            console.warn('Restaurant multi-vehicle transfer build failed', e);
        }
        if (!result || !Array.isArray(result.vehicles) || !result.vehicles.length) {
            return null;
        }
        var withVehicle = result.vehicles.filter(function (v) { return !!v.vehicleId; });
        if (!withVehicle.length) return null;

        var primary = withVehicle[0];
        var pax = getRestaurantMvPax();
        var adultsQty = opts.adultsQty != null ? opts.adultsQty : pax.adults;
        var childQty = opts.childQty != null ? opts.childQty : pax.child;
        var infantQty = opts.infantQty != null ? opts.infantQty : pax.infant;
        var lineTotalCost = parseFloat(result.totalCost) || 0;
        var lineTotalSell = parseFloat(result.totalSell) || 0;
        var displayCost = parseFloat(result.storedCost != null ? result.storedCost : result.totalCost) || 0;
        var displaySell = parseFloat(result.storedSell != null ? result.storedSell : result.totalSell) || 0;
        var foc = document.getElementById('restaurantTransferFocServiceDiscount')?.checked === true;
        var transferId = opts.transferId
            || (typeof generateId === 'function' ? generateId('transfer') : ('transfer-' + Date.now()));

        return {
            id: transferId,
            dateTime: opts.dateTime || '',
            service: (result.pickupName || '') + ' / ' + (result.dropoffName || ''),
            restaurantName: result.restaurantName || getRestaurantName(),
            transportMode: 'local',
            type: primary.transferType || primary.type || 'S',
            transferType: primary.transferType || primary.type || 'S',
            vehicleId: primary.vehicleId || '',
            vehicleName: primary.vehicleName || '',
            vehicleType: primary.vehicleType || '',
            capacity: primary.seats || 0,
            vehicleQty: primary.qty || 1,
            vehicles: withVehicle,
            way: result.way || primary.way || 'one-way',
            destination: result.destinationName || '',
            destinationId: result.destinationId || destSelect.value,
            destinationType: result.destinationType || '',
            pickup: result.pickupName || '',
            dropoff: result.dropoffName || '',
            isDestinationPickup: !!result.isDestinationPickup,
            pickupId: result.pickupId || '',
            dropId: result.dropoffId || '',
            dropoffId: result.dropoffId || '',
            pickupType: result.pickupType || '',
            dropType: result.dropoffType || '',
            dropoffType: result.dropoffType || '',
            adults: adultsQty,
            adultsQty: adultsQty,
            child: childQty,
            childQty: childQty,
            infant: infantQty,
            infantQty: infantQty,
            adultCost: lineTotalCost > 0 ? lineTotalCost : displayCost,
            adultSell: lineTotalSell > 0 ? lineTotalSell : displaySell,
            childCost: lineTotalCost > 0 ? lineTotalCost : displayCost,
            childSell: lineTotalSell > 0 ? lineTotalSell : displaySell,
            cost: lineTotalCost > 0 ? lineTotalCost : displayCost,
            sell: lineTotalSell > 0 ? lineTotalSell : displaySell,
            storedCost: displayCost,
            storedSell: displaySell,
            lineCost: lineTotalCost,
            lineSell: lineTotalSell,
            totalCost: lineTotalCost,
            totalSell: lineTotalSell,
            zonePrivatePrice: primary.zonePrivatePrice || 0,
            zoneSharedPrice: primary.zoneSharedPrice || 0,
            zonePrivateCostPrice: primary.zonePrivateCostPrice || 0,
            zoneSharedCostPrice: primary.zoneSharedCostPrice || 0,
            isStandalone: false,
            sourceType: 'meal',
            sourceId: opts.sourceId || null,
            focServiceDiscount: foc,
            taxIncluded: true
        };
    };

    document.addEventListener('DOMContentLoaded', function () {
        ensureRestMvStyles();
        var dest = document.getElementById('restaurantTransferDestination');
        if (dest) {
            dest.addEventListener('change', function () {
                if (document.getElementById('restaurantTransferCheckbox')?.checked) {
                    scheduleRestaurantMvPriceRefresh();
                }
            });
        }
        var pickup = document.getElementById('restaurantTransferIsPickup');
        if (pickup) {
            pickup.addEventListener('change', function () {
                if (document.getElementById('restaurantTransferCheckbox')?.checked) {
                    scheduleRestaurantMvPriceRefresh();
                }
            });
        }
        var restSel = document.getElementById('mealRestaurant');
        if (restSel) {
            restSel.addEventListener('change', function () {
                if (document.getElementById('restaurantTransferCheckbox')?.checked) {
                    if (typeof ensureRestaurantMvPanel === 'function') ensureRestaurantMvPanel();
                    scheduleRestaurantMvPriceRefresh();
                }
            });
        }
        document.getElementById('mealsTableBody')?.addEventListener('change', function (ev) {
            var t = ev.target;
            if (!t || !t.classList) return;
            if (t.classList.contains('meal-adult-qty')
                || t.classList.contains('meal-child-qty')
                || t.classList.contains('meal-infant-qty')
                || t.classList.contains('meal-checkbox')) {
                if (document.getElementById('restaurantTransferCheckbox')?.checked) {
                    if (typeof reclampAllMultiVehicleRowsPax === 'function') {
                        reclampAllMultiVehicleRowsPax(REST_MV_SIDE);
                    }
                    if (typeof updateArrDepVehicleCoverage === 'function') {
                        updateArrDepVehicleCoverage(REST_MV_SIDE);
                    }
                    scheduleRestaurantMvPriceRefresh();
                }
            }
        });
        if (window.jQuery) {
            jQuery(document).on('change', '#restaurantTransferDestination, #mealRestaurant', function () {
                if (document.getElementById('restaurantTransferCheckbox')?.checked) {
                    scheduleRestaurantMvPriceRefresh();
                }
            });
        }
    });
})();
