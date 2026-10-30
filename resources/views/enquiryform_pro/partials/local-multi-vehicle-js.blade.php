{{-- Local Transfer Package: attraction-style full-width multi-vehicle accordion.
     Included INSIDE create/edit main <script> — do not wrap with script tags. --}}
(function () {
    'use strict';

    function ensureLocalMvStyles() {
        if (typeof ensureAttractionMvStyles === 'function') {
            try { ensureAttractionMvStyles(); } catch (e) { /* ignore */ }
        }
        var css = document.getElementById('local-mv-accordion-css');
        if (!css) {
            css = document.createElement('style');
            css.id = 'local-mv-accordion-css';
            document.head.appendChild(css);
        }
        css.textContent = [
            /* Match Attraction Tour Details — compact sizes, T.Sell fully visible */
            '#transferModal .modal-dialog.transfer-modal-wide{max-width:min(1200px,96vw)!important;width:96vw!important;margin:0.75rem auto;}',
            '#transferModal .modal-dialog.transfer-modal-wide .modal-content{width:100%;}',
            '#transferModal .modal-dialog.transfer-modal-wide .modal-body{padding:8px 12px!important;overflow-x:visible!important;}',
            '#transferModal #localTransferForm .attraction-mv-panel{margin-top:0;width:100%;box-sizing:border-box;overflow:visible;}',
            '#transferModal #localTransferForm .attraction-mv-panel .attr-mv-body{overflow:visible;padding:6px 8px 8px;}',
            '#transferModal #localVehicleRows.attraction-mv-accordion{display:block!important;width:100%;overflow:visible;}',
            '#transferModal #localVehicleRows .attraction-mv-item{width:100%;box-sizing:border-box;overflow:visible!important;}',
            /* Same single-row flex as Attraction (display:contents + compact 26px controls) */
            '#transferModal #localVehicleRows .attraction-mv-item.is-open > .attraction-mv-item-body{'
            + 'display:flex!important;flex-wrap:nowrap!important;align-items:end;gap:6px;width:100%;'
            + 'padding:6px 8px 8px!important;box-sizing:border-box;overflow:visible!important;'
            + '}',
            '#transferModal #localVehicleRows .attraction-mv-grid,#transferModal #localVehicleRows .attraction-mv-grid-prices{display:contents!important;}',
            '#transferModal #localVehicleRows .attraction-mv-field{flex:1 1 0;min-width:0;max-width:none;}',
            '#transferModal #localVehicleRows .attraction-mv-field label{'
            + 'display:block;font-size:8px!important;font-weight:700;color:#64748b;'
            + 'text-transform:uppercase;letter-spacing:.02em;margin-bottom:1px!important;white-space:nowrap;'
            + '}',
            '#transferModal #localVehicleRows .attraction-mv-field .form-select,'
            + '#transferModal #localVehicleRows .attraction-mv-field .form-control{'
            + 'font-size:10px!important;padding:2px 4px!important;min-height:26px!important;height:26px!important;width:100%;'
            + '}',
            '#transferModal #localVehicleRows .attraction-mv-field--vehicle{flex:1.6 1 0;min-width:110px;}',
            '#transferModal #localVehicleRows .attraction-mv-field--vehicle .arr-dep-vehicle-select{max-width:none;width:100%;}',
            '#transferModal #localVehicleRows .attraction-mv-field--seats{flex:0.55 1 0;min-width:42px;}',
            '#transferModal #localVehicleRows .attraction-mv-field--type{flex:0.9 1 0;min-width:68px;}',
            '#transferModal #localVehicleRows .attraction-mv-field--way{flex:0.85 1 0;min-width:64px;}',
            '#transferModal #localVehicleRows .attraction-mv-field--qty{flex:0.9 1 0;min-width:68px;}',
            '#transferModal #localVehicleRows .attraction-mv-field--pax{flex:0.55 1 0;min-width:40px;}',
            '#transferModal #localVehicleRows .attraction-mv-field--pax .form-control{text-align:center;padding-left:2px;padding-right:2px;}',
            '#transferModal #localVehicleRows .attraction-mv-field--price{flex:0.85 1 0;min-width:58px;}',
            '#transferModal #localVehicleRows .attraction-mv-field--price .form-control{text-align:right;}',
            '#transferModal #localVehicleRows .attraction-mv-field--total{flex:0.95 1 0;min-width:66px;}',
            '#transferModal #localVehicleRows .attraction-mv-field--action{flex:0 0 28px;width:28px;min-width:28px;}',
            '#transferModal #localVehicleRows .attraction-mv-field .mv-qty-stepper{height:26px!important;display:inline-flex;width:100%;}',
            '#transferModal #localVehicleRows .attraction-mv-field .mv-qty-stepper button{width:20px;height:26px;font-size:11px;padding:0;flex-shrink:0;}',
            '#transferModal #localVehicleRows .attraction-mv-field .mv-qty-stepper input{width:100%;min-width:0;height:26px;font-size:10px;}',
            '#transferModal #localVehicleRows .attraction-mv-field .mv-seats-badge{'
            + 'display:inline-flex;align-items:center;justify-content:center;gap:2px;min-height:26px;width:100%;'
            + 'padding:2px 4px;border:1px solid #dbeafe;border-radius:4px;background:#eff6ff;color:#1d4ed8;'
            + 'font-weight:700;font-size:10px;box-sizing:border-box;'
            + '}',
            '#transferModal #localVehicleRows .attraction-mv-field .mv-total-cost,'
            + '#transferModal #localVehicleRows .attraction-mv-field .mv-total-sell{'
            + 'display:block;min-width:0;width:100%;padding:4px;font-size:10px;line-height:18px;'
            + 'text-align:right;border-radius:4px;box-sizing:border-box;'
            + '}',
            '#transferModal #localVehicleRows .attraction-mv-field .btn{'
            + 'padding:2px 4px;font-size:11px;min-height:26px;height:26px;line-height:1;width:100%;'
            + '}',
            '#transferModal #localVehiclePriceSummary{font-size:11px;padding:6px 10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;margin-top:6px;}',
            '#transferModal #localVehicleTypeField .mv-info-banner{margin-bottom:8px;font-size:11px;}',
            '#transferModal #localTransferForm .local-mv-legacy-table{display:none!important;}'
        ].join('');
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

    function refreshLocalMvItemHeader(row, idx) {
        if (!row) return;
        var sel = row.querySelector('.arr-dep-vehicle-select');
        var opt = sel?.selectedOptions?.[0];
        var seats = resolveVehicleOptionSeats(opt);
        var qty = Math.max(1, parseInt(row.querySelector('.arr-dep-vehicle-qty')?.value || '1', 10) || 1);
        var adults = Math.max(0, parseInt(row.querySelector('.arr-dep-vehicle-adults')?.value || '0', 10) || 0);
        var child = Math.max(0, parseInt(row.querySelector('.arr-dep-vehicle-child')?.value || '0', 10) || 0);
        var typeVal = row.querySelector('.arr-dep-vehicle-xfer-type')?.value || 'S';
        var wayVal = row.querySelector('.arr-dep-vehicle-way')?.value
            || document.getElementById('localWay')?.value
            || 'both-way';
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

    function getLocalDefaultWay() {
        return document.getElementById('localWay')?.value || 'both-way';
    }

    function syncLocalWayFromPrimaryVehicle() {
        var container = document.getElementById('localVehicleRows');
        var wayEl = container?.querySelector('.arr-dep-vehicle-row .arr-dep-vehicle-way');
        var legacy = document.getElementById('localWay');
        if (wayEl && legacy && wayEl.value) {
            legacy.value = wayEl.value;
        }
    }

    function buildLocalMvItemHtml(side, selectedId, qty, transferType, paxOpts) {
        var master = (typeof getArrDepVehicleMasterSelect === 'function')
            ? getArrDepVehicleMasterSelect(side)
            : document.getElementById('localVehicleType');
        var optsHtml = master ? master.innerHTML : '<option value="">Select Vehicle</option>';
        var q = Math.max(1, parseInt(qty || 1, 10) || 1);
        var tt = (typeof normalizeTransferTypePS === 'function')
            ? normalizeTransferTypePS(transferType || document.getElementById('localType')?.value || 'S')
            : (transferType || 'S');
        var sSel = tt === 'S' ? ' selected' : '';
        var pSel = tt === 'P' ? ' selected' : '';
        paxOpts = paxOpts || {};
        var aVal = Math.max(0, parseInt(paxOpts.adults ?? paxOpts.adultsQty ?? 0, 10) || 0);
        var cVal = Math.max(0, parseInt(paxOpts.child ?? paxOpts.childQty ?? 0, 10) || 0);
        var iVal = Math.max(0, parseInt(paxOpts.infant ?? paxOpts.infantQty ?? 0, 10) || 0);
        var defaultWay = paxOpts.way || getLocalDefaultWay();
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

    window.addLocalMvVehicle = function (selectedId, qty, transferType, paxOpts) {
        var panel = document.querySelector('#localTransferForm .attraction-mv-panel');
        if (panel) {
            panel.classList.remove('is-collapsed');
            var collapseBtn = panel.querySelector('.attr-mv-collapse-btn span');
            if (collapseBtn) collapseBtn.textContent = 'Vehicles';
        }
        return window.addArrDepVehicleRow('local', selectedId, qty, transferType, paxOpts);
    };

    // Patch addArrDepVehicleRow for local accordion items
    (function patchAddArrDepVehicleRowForLocal() {
        var _addRow = (typeof window.addArrDepVehicleRow === 'function')
            ? window.addArrDepVehicleRow
            : null;
        window.addArrDepVehicleRow = function (side, selectedId, qty, transferType, paxOpts) {
            if (side !== 'local') {
                return _addRow ? _addRow(side, selectedId, qty, transferType, paxOpts) : null;
            }
            try {
                ensureLocalMvStyles();
                var container = document.getElementById('localVehicleRows');
                if (!container) {
                    console.warn('Local MV: vehicle container not found');
                    return null;
                }
                // Ensure container is accordion div (not leftover tbody)
                if (container.tagName === 'TBODY') {
                    console.warn('Local MV: container still TBODY — markup not upgraded');
                    return _addRow ? _addRow(side, selectedId, qty, transferType, paxOpts) : null;
                }
                var wrap = document.createElement('div');
                wrap.innerHTML = buildLocalMvItemHtml(side, selectedId, qty, transferType, paxOpts);
                var row = wrap.firstElementChild;
                if (!row) return null;
                container.appendChild(row);
                Array.from(container.querySelectorAll('.attraction-mv-item, .arr-dep-vehicle-row')).forEach(function (item, i) {
                    item.style.zIndex = '';
                    item.style.position = 'static';
                    item.classList.toggle('is-open', item === row);
                    var idxEl = item.querySelector('.mv-row-index, .attraction-mv-item-idx');
                    if (idxEl) idxEl.textContent = String(i + 1);
                    refreshLocalMvItemHeader(item, i);
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
                if (wayEl) {
                    wayEl.value = (paxOpts && paxOpts.way) ? paxOpts.way : getLocalDefaultWay();
                }
                window._arrDepVehicleForceCity = String(document.getElementById('localDestination')?.value || '').trim();
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
                syncLocalWayFromPrimaryVehicle();
                if (typeof scheduleLocalMultiVehiclePriceRefresh === 'function') {
                    scheduleLocalMultiVehiclePriceRefresh();
                }
                return row;
            } catch (err) {
                console.error('Local MV add vehicle failed', err);
                return _addRow ? _addRow(side, selectedId, qty, transferType, paxOpts) : null;
            }
        };
    })();

    // Extra listeners for local accordion rows (Way + header refresh)
    if (typeof window.bindArrDepVehicleRowEvents === 'function') {
        var _bind = window.bindArrDepVehicleRowEvents;
        window.bindArrDepVehicleRowEvents = function (side, row) {
            _bind(side, row);
            if (side !== 'local') return;
            var refresh = function () {
                if (typeof clampArrDepRowPaxToCapacity === 'function') {
                    clampArrDepRowPaxToCapacity(row, side);
                }
                if (typeof syncAllMultiVehiclePaxLimits === 'function') {
                    syncAllMultiVehiclePaxLimits(side);
                }
                syncLocalWayFromPrimaryVehicle();
                if (typeof scheduleLocalMultiVehiclePriceRefresh === 'function') {
                    scheduleLocalMultiVehiclePriceRefresh();
                }
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
        };
    }

    // Coverage: refresh accordion headers for local
    if (typeof window.updateArrDepVehicleCoverage === 'function') {
        var _cov = window.updateArrDepVehicleCoverage;
        window.updateArrDepVehicleCoverage = function (side) {
            var ret = _cov(side);
            if (side === 'local') {
                var container = document.getElementById('localVehicleRows');
                if (container) {
                    Array.from(container.querySelectorAll('.arr-dep-vehicle-row')).forEach(function (row, idx) {
                        refreshLocalMvItemHeader(row, idx);
                    });
                }
            }
            return ret;
        };
    }

    // Remove whole accordion item on delete
    document.addEventListener('click', function (ev) {
        var btn = ev.target?.closest?.('#localVehicleRows .arr-dep-vehicle-remove');
        if (!btn) return;
        var item = btn.closest('.attraction-mv-item');
        if (!item) return;
        setTimeout(function () {
            if (item.parentElement) item.remove();
            if (typeof updateArrDepVehicleCoverage === 'function') updateArrDepVehicleCoverage('local');
            if (typeof scheduleLocalMultiVehiclePriceRefresh === 'function') scheduleLocalMultiVehiclePriceRefresh();
            syncLocalWayFromPrimaryVehicle();
        }, 0);
    }, true);

    // Patch refreshLocalMultiVehiclePrices to use per-vehicle Way + write unit Cost/Sell
    if (typeof window.refreshLocalMultiVehiclePrices === 'function'
        || typeof refreshLocalMultiVehiclePrices === 'function') {
        // Will wrap after function is on window — assign below
    }

    function wrapRefreshLocalMultiVehiclePrices() {
        var original = window.refreshLocalMultiVehiclePrices;
        if (typeof original !== 'function') return;
        if (original._localMvPatched) return;
        var wrapped = async function () {
            ensureLocalMvStyles();
            widenTransferModalForLocal();
            // Seed accordion if empty
            var container = document.getElementById('localVehicleRows');
            if (container && !container.querySelector('.arr-dep-vehicle-row')) {
                if (typeof window.addArrDepVehicleRow === 'function') {
                    var paxAdults = parseInt(document.getElementById('localAdults')?.value || '0', 10) || 0;
                    var paxChild = parseInt(document.getElementById('localChild')?.value || '0', 10) || 0;
                    window.addArrDepVehicleRow('local', '', 1, 'S', {
                        adults: paxAdults,
                        child: paxChild,
                        infant: 0,
                        way: getLocalDefaultWay()
                    });
                }
            }

            var vehicles = (typeof collectArrDepVehiclesPayload === 'function')
                ? collectArrDepVehiclesPayload('local') : [];
            var pickupSelect = document.getElementById('localPickup');
            var dropSelect = document.getElementById('localDrop');
            if (!pickupSelect?.value || !dropSelect?.value || !vehicles.length) {
                if (typeof updateArrDepVehicleCoverage === 'function') updateArrDepVehicleCoverage('local');
                return;
            }
            var defaultWay = getLocalDefaultWay();
            var defaultType = document.getElementById('localType')?.value || 'S';
            var adults = parseInt(document.getElementById('localAdults')?.value || '0', 10) || 0;
            var child = parseInt(document.getElementById('localChild')?.value || '0', 10) || 0;
            var dmcId = '{{ $dmc_id ?? "" }}';
            var pickupType = (window.jQuery ? jQuery('#localPickup').find(':selected').attr('data-type') : '') || '';
            var dropType = (window.jQuery ? jQuery('#localDrop').find(':selected').attr('data-type') : '') || '';
            var pickupIdForFetch = pickupSelect.value;
            var dropIdForFetch = dropSelect.value;
            if (typeof resolveLocalTransferZoneLookup === 'function') {
                var resolvedLoc = resolveLocalTransferZoneLookup(pickupSelect.value, pickupType, dropSelect.value, dropType);
                pickupIdForFetch = resolvedLoc.actualPickupId || pickupIdForFetch;
                dropIdForFetch = resolvedLoc.actualDropId || dropIdForFetch;
                pickupType = resolvedLoc.actualPickupType || pickupType;
                dropType = resolvedLoc.actualDropType || dropType;
            }
            var tourPax = (typeof getLocalTourPax === 'function') ? getLocalTourPax() : Math.max(0, adults + child);
            var domRows = container
                ? Array.from(container.querySelectorAll('.arr-dep-vehicle-row'))
                : [];
            var enriched = [];
            var totalCost = 0, totalSell = 0, storedCost = 0, storedSell = 0;
            var anyShared = false;

            for (var i = 0; i < vehicles.length; i++) {
                var v = vehicles[i];
                var wayEl = domRows[i]?.querySelector('.arr-dep-vehicle-way');
                var way = wayEl?.value || v.way || defaultWay;
                v.way = way;
                var zonePrice = { private_price: 0, shared_price: 0, private_cost_price: 0, shared_cost_price: 0 };
                try {
                    if (typeof fetchZonePrice === 'function' && v.vehicleId && pickupIdForFetch && dropIdForFetch && dmcId) {
                        zonePrice = await fetchZonePrice(v.vehicleId, pickupIdForFetch, pickupType, dropIdForFetch, dropType, dmcId);
                    }
                } catch (e) {
                    console.warn('Local multi-vehicle zone price failed', v.vehicleId, e);
                }
                var rowType = (typeof normalizeTransferTypePS === 'function')
                    ? normalizeTransferTypePS(v.transferType || v.type || defaultType)
                    : (v.transferType || v.type || defaultType || 'S');
                var rowShared = rowType === 'S';
                if (rowShared) anyShared = true;
                var vAdults = Math.max(0, parseInt(v.adults ?? v.adultsQty ?? 0, 10) || 0);
                var vChild = Math.max(0, parseInt(v.child ?? v.childQty ?? 0, 10) || 0);
                var vInfant = Math.max(0, parseInt(v.infant ?? v.infantQty ?? 0, 10) || 0);
                var vehiclePax = Math.max(0, vAdults + vChild);
                var unit = (typeof calculateTransferPrice === 'function')
                    ? calculateTransferPrice(zonePrice, rowType, way, vAdults || adults, vChild || child)
                    : { cost: 0, sell: 0 };
                var unitCost = parseFloat(unit.cost) || 0;
                var unitSell = parseFloat(unit.sell) || 0;
                var qty = Math.max(1, parseInt(v.qty || 1, 10) || 1);
                var billPax = vehiclePax > 0 ? vehiclePax : tourPax;
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
                var lineCost = rowShared ? unitCost * Math.max(0, billPax) : unitCost * qty;
                var lineSell = rowShared ? unitSell * Math.max(0, billPax) : unitSell * qty;
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
                    recalcArrDepVehicleRowTotals('local', domRow);
                }
                refreshLocalMvItemHeader(domRow, i);
                enriched.push({
                    vehicleId: v.vehicleId,
                    vehicleName: v.vehicleName,
                    vehicleType: v.vehicleType,
                    seats: v.seats,
                    qty: qty,
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
                    zonePrivatePrice: unit.privatePrice || zonePrice.private_price || 0,
                    zoneSharedPrice: unit.sharedPrice || zonePrice.shared_price || 0,
                    zonePrivateCostPrice: unit.privateCostPrice || zonePrice.private_cost_price || 0,
                    zoneSharedCostPrice: unit.sharedCostPrice || zonePrice.shared_cost_price || 0
                });
            }

            window._localMultiVehicleResult = {
                vehicles: enriched,
                totalCost: totalCost,
                totalSell: totalSell,
                storedCost: storedCost,
                storedSell: storedSell
            };
            var master = document.getElementById('localVehicleType');
            if (master) {
                master.dataset.currentPrice = String(storedSell || 0);
                master.dataset.multiTotalCost = String(totalCost || 0);
                master.dataset.multiTotalSell = String(totalSell || 0);
                master.dataset.multiStoredCost = String(storedCost || 0);
                master.dataset.multiStoredSell = String(storedSell || 0);
            }
            syncLocalWayFromPrimaryVehicle();
            if (typeof updateArrDepVehicleCoverage === 'function') updateArrDepVehicleCoverage('local');
            var summary = document.getElementById('localVehiclePriceSummary');
            if (summary) {
                summary.style.display = '';
                summary.innerHTML = '<i class="ri-coin-line me-1"></i><strong>Zone price (Cost / Sell):</strong> '
                    + '<span class="text-primary fw-bold ms-1">' + totalCost.toFixed(2) + ' / ' + totalSell.toFixed(2) + '</span>'
                    + ' <span class="text-muted ms-1">(' + enriched.length + ' vehicle' + (enriched.length > 1 ? 's' : '')
                    + (anyShared ? (', ×' + tourPax + ' pax') : '') + ')</span>';
            }
        };
        wrapped._localMvPatched = true;
        window.refreshLocalMultiVehiclePrices = wrapped;
    }

    function widenTransferModalForLocal() {
        var dialog = document.querySelector('#transferModal .modal-dialog');
        var localForm = document.getElementById('localTransferForm');
        if (!dialog) return;
        var localVisible = localForm && localForm.style.display !== 'none'
            && !localForm.classList.contains('d-none');
        var mode = document.querySelector('input[name="transferMode"]:checked')?.value;
        if (localVisible && (!mode || mode === 'local')) {
            dialog.classList.add('transfer-modal-wide');
            dialog.style.maxWidth = 'min(1280px, 96vw)';
            dialog.style.width = '96vw';
        } else {
            dialog.classList.remove('transfer-modal-wide');
            dialog.style.maxWidth = '';
            dialog.style.width = '';
        }
    }

    window.ensureLocalMvPanel = function (vehicles) {
        ensureLocalMvStyles();
        widenTransferModalForLocal();
        var container = document.getElementById('localVehicleRows');
        if (!container) return null;
        var list = (Array.isArray(vehicles) && vehicles.length)
            ? vehicles
            : [{ vehicleId: '', qty: 1, transferType: 'S', way: getLocalDefaultWay() }];
        container.innerHTML = '';
        list.forEach(function (v) {
            window.addArrDepVehicleRow(
                'local',
                v.vehicleId || v.vehicle_id || '',
                v.qty || v.quantity || 1,
                v.transferType || v.type || 'S',
                {
                    adults: v.adults ?? v.adultsQty,
                    child: v.child ?? v.childQty,
                    infant: v.infant ?? v.infantQty,
                    way: v.way || getLocalDefaultWay()
                }
            );
        });
        if (typeof scheduleLocalMultiVehiclePriceRefresh === 'function') {
            scheduleLocalMultiVehiclePriceRefresh();
        }
        return container;
    };

    // Widen modal when switching to local mode
    var _origSwitch = typeof window.switchTransferMode === 'function' ? window.switchTransferMode : null;
    if (_origSwitch) {
        window.switchTransferMode = function (mode) {
            var ret = _origSwitch.apply(this, arguments);
            setTimeout(function () {
                ensureLocalMvStyles();
                widenTransferModalForLocal();
                if (mode === 'local') {
                    var c = document.getElementById('localVehicleRows');
                    if (c && !c.querySelector('.arr-dep-vehicle-row') && typeof window.addArrDepVehicleRow === 'function') {
                        var a = parseInt(document.getElementById('localAdults')?.value || '0', 10) || 0;
                        var ch = parseInt(document.getElementById('localChild')?.value || '0', 10) || 0;
                        window.addArrDepVehicleRow('local', '', 1, 'S', {
                            adults: a, child: ch, infant: 0, way: getLocalDefaultWay()
                        });
                    }
                }
            }, 30);
            return ret;
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        ensureLocalMvStyles();
        // Expose refresh on window if only local binding exists
        if (typeof window.refreshLocalMultiVehiclePrices !== 'function'
            && typeof refreshLocalMultiVehiclePrices === 'function') {
            window.refreshLocalMultiVehiclePrices = refreshLocalMultiVehiclePrices;
        }
        wrapRefreshLocalMultiVehiclePrices();

        document.getElementById('localWay')?.addEventListener('change', function () {
            var firstWay = document.querySelector('#localVehicleRows .arr-dep-vehicle-way');
            if (firstWay && !firstWay.dataset.userSet) {
                firstWay.value = this.value;
            }
            if (typeof scheduleLocalMultiVehiclePriceRefresh === 'function') {
                scheduleLocalMultiVehiclePriceRefresh();
            }
        });

        document.getElementById('transferModal')?.addEventListener('shown.bs.modal', function () {
            ensureLocalMvStyles();
            widenTransferModalForLocal();
            wrapRefreshLocalMultiVehiclePrices();
            var mode = document.querySelector('input[name="transferMode"]:checked')?.value;
            if (mode === 'local') {
                var c = document.getElementById('localVehicleRows');
                if (c && !c.querySelector('.arr-dep-vehicle-row')) {
                    var a = parseInt(document.getElementById('localAdults')?.value || '0', 10) || 0;
                    var ch = parseInt(document.getElementById('localChild')?.value || '0', 10) || 0;
                    window.addArrDepVehicleRow('local', '', 1, 'S', {
                        adults: a, child: ch, infant: 0, way: getLocalDefaultWay()
                    });
                }
                if (typeof scheduleLocalMultiVehiclePriceRefresh === 'function') {
                    scheduleLocalMultiVehiclePriceRefresh();
                }
            }
        });
    });

    // Also try wrap immediately in case DOMContentLoaded already fired
    setTimeout(function () {
        if (typeof window.refreshLocalMultiVehiclePrices === 'function') {
            wrapRefreshLocalMultiVehiclePrices();
        }
    }, 0);
})();
