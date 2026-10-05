{{-- Attraction Tour Details: multi-vehicle transfer accordion (A/D math).
     Included INSIDE create/edit main <script> — do not wrap with script tags. --}}
(function () {
    'use strict';

    function isAttrMvSide(side) {
        return typeof side === 'string' && side.indexOf('attr:') === 0;
    }
    function attrMvUid(side) {
        return isAttrMvSide(side) ? side.slice(5) : '';
    }
    function attrMvSide(uid) {
        return 'attr:' + String(uid || '');
    }

    function ensureAttractionMvStyles() {
        let css = document.getElementById('attraction-mv-accordion-css');
        if (!css) {
            css = document.createElement('style');
            css.id = 'attraction-mv-accordion-css';
            document.head.appendChild(css);
        }
        css.textContent = [
            '.attraction-transfer-panel-row>td{overflow:visible!important;position:relative;z-index:2;}',
            '.attraction-mv-panel{border:1px solid #dbe3f0;border-radius:8px;background:#fff;overflow:visible;position:relative;z-index:1;isolation:isolate;}',
            '.attraction-mv-panel .attr-mv-head{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:6px 10px;background:#f8fafc;border-bottom:1px solid #e2e8f0;position:relative;z-index:5;}',
            '.attraction-mv-panel .attr-mv-head-left{min-width:0;display:flex;align-items:center;gap:8px;flex-wrap:wrap;flex:1;}',
            '.attraction-mv-panel .attr-mv-head-actions{display:inline-flex;align-items:center;gap:6px;flex-shrink:0;}',
            '.attraction-mv-panel .attr-mv-collapse-btn{display:inline-flex;align-items:center;gap:4px;border:1px solid #cbd5e1;background:#fff;color:#334155;border-radius:6px;padding:3px 8px;font-size:10px;font-weight:600;line-height:1.2;cursor:pointer;white-space:nowrap;height:26px;}',
            '.attraction-mv-panel .attr-mv-collapse-btn:hover{background:#f1f5f9;border-color:#94a3b8;}',
            '.attraction-mv-panel .attr-mv-collapse-btn i{font-size:14px;transition:transform .15s ease;}',
            '.attraction-mv-panel.is-collapsed .attr-mv-collapse-btn i{transform:rotate(-90deg);}',
            '.attraction-mv-panel.is-collapsed .attr-mv-body{display:none!important;}',
            '.attraction-mv-panel .attr-mv-title{margin:0;font-size:12px;font-weight:700;color:#0f172a;display:inline-flex;align-items:center;gap:5px;}',
            '.attraction-mv-panel .attr-mv-title i{color:#2563eb;font-size:14px;}',
            '.attraction-mv-panel .attr-mv-sub{margin:0;font-size:10px;color:#64748b;}',
            '.attraction-mv-panel .attr-mv-include{display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:600;color:#475569;margin:0;}',
            '.attraction-mv-panel .attr-mv-body{padding:6px 8px 8px;position:relative;display:block!important;}',
            '.attraction-mv-accordion.arr-dep-vehicle-rows{display:block!important;position:static!important;width:100%!important;float:none!important;clear:both!important;margin:0!important;padding:0!important;}',
            '.attraction-mv-accordion > .attraction-mv-item,.attraction-mv-accordion > .arr-dep-vehicle-row{display:block!important;position:static!important;float:none!important;clear:both!important;width:100%!important;margin:0 0 10px 0!important;top:auto!important;left:auto!important;right:auto!important;bottom:auto!important;transform:none!important;z-index:auto!important;border:1px solid #e2e8f0;border-radius:6px;background:#fff;overflow:hidden;box-shadow:0 1px 2px rgba(15,23,42,.04);}',
            '.attraction-mv-accordion > .attraction-mv-item:last-child{margin-bottom:0!important;}',
            '.attraction-mv-item.is-open{border-color:#93c5fd;overflow:visible;box-shadow:0 2px 8px rgba(37,99,235,.12);}',
            '.attraction-mv-item-toggle{width:100%;border:0;background:#f8fafc;padding:5px 8px;display:flex;align-items:center;justify-content:space-between;gap:6px;cursor:pointer;text-align:left;position:relative;}',
            '.attraction-mv-item.is-open .attraction-mv-item-toggle{background:#eff6ff;border-bottom:1px solid #dbeafe;}',
            '.attraction-mv-item-toggle:hover{background:#f1f5f9;}',
            '.attraction-mv-item-main{display:flex;align-items:center;gap:6px;min-width:0;flex:1;}',
            '.attraction-mv-item-idx{width:18px;height:18px;border-radius:999px;background:#1e293b;color:#fff;font-size:10px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;}',
            '.attraction-mv-item-meta{min-width:0;display:flex;align-items:center;gap:6px;flex-wrap:wrap;}',
            '.attraction-mv-item-name{font-size:11px;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px;}',
            '.attraction-mv-item-tags{display:inline-flex;flex-wrap:wrap;gap:3px;}',
            '.attraction-mv-tag{display:inline-flex;align-items:center;gap:2px;font-size:9px;font-weight:600;color:#475569;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:999px;padding:0 5px;line-height:16px;}',
            '.attraction-mv-tag.is-seats{color:#1d4ed8;background:#eff6ff;border-color:#bfdbfe;}',
            '.attraction-mv-tag.is-warn{color:#b45309;background:#fffbeb;border-color:#fde68a;}',
            '.attraction-mv-tag.is-ok{color:#047857;background:#ecfdf5;border-color:#a7f3d0;}',
            '.attraction-mv-item-right{display:flex;align-items:center;gap:6px;flex-shrink:0;}',
            '.attraction-mv-item-sell{font-size:10px;font-weight:700;color:#047857;}',
            '.attraction-mv-chevron{color:#64748b;font-size:14px;transition:transform .15s ease;}',
            '.attraction-mv-item.is-open .attraction-mv-chevron{transform:rotate(180deg);}',
            '.attraction-mv-item-body{display:none;padding:6px 8px 8px;background:#fff;position:relative;}',
            '.attraction-mv-item.is-open > .attraction-mv-item-body{display:flex!important;flex-wrap:nowrap;align-items:end;gap:6px;width:100%;box-sizing:border-box;}',
            /* Full-width single row — fields grow to fill the box */
            '.attraction-mv-grid,.attraction-mv-grid-prices{display:contents;}',
            '.attraction-mv-field{flex:1 1 0;min-width:0;}',
            '.attraction-mv-field label{display:block;font-size:8px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.02em;margin-bottom:1px;white-space:nowrap;}',
            '.attraction-mv-field .form-select,.attraction-mv-field .form-control{font-size:10px;padding:2px 4px;min-height:26px;height:26px;width:100%;}',
            '.attraction-mv-field--vehicle{flex:1.6 1 0;min-width:110px;max-width:none;}',
            '.attraction-mv-field--vehicle .arr-dep-vehicle-select{max-width:none;width:100%;}',
            '.attraction-mv-field--seats{flex:0.55 1 0;min-width:42px;}',
            '.attraction-mv-field--type{flex:0.9 1 0;min-width:68px;}',
            '.attraction-mv-field--way{flex:0.85 1 0;min-width:64px;}',
            '.attraction-mv-field--qty{flex:0.9 1 0;min-width:68px;}',
            '.attraction-mv-field--pax{flex:0.55 1 0;min-width:40px;}',
            '.attraction-mv-field--pax .form-control{text-align:center;padding-left:2px;padding-right:2px;}',
            '.attraction-mv-field--price{flex:0.85 1 0;min-width:58px;}',
            '.attraction-mv-field--price .form-control{text-align:right;}',
            '.attraction-mv-field--total{flex:0.95 1 0;min-width:66px;}',
            '.attraction-mv-field--action{flex:0 0 28px;width:28px;min-width:28px;}',
            '.attraction-mv-field .mv-qty-stepper{height:26px;display:inline-flex;width:100%;}',
            '.attraction-mv-field .mv-qty-stepper button{width:20px;height:26px;font-size:11px;padding:0;flex-shrink:0;}',
            '.attraction-mv-field .mv-qty-stepper input{width:100%;min-width:0;height:26px;font-size:10px;}',
            '.attraction-mv-field .mv-seats-badge{display:inline-flex;align-items:center;justify-content:center;gap:2px;min-height:26px;width:100%;padding:2px 4px;border:1px solid #dbeafe;border-radius:4px;background:#eff6ff;color:#1d4ed8;font-weight:700;font-size:10px;box-sizing:border-box;}',
            '.attraction-mv-field .mv-seats-badge i{font-size:11px;}',
            '.attraction-mv-field .mv-total-cost,.attraction-mv-field .mv-total-sell{display:block;min-width:0;width:100%;padding:4px 4px;font-size:10px;line-height:18px;text-align:right;border-radius:4px;box-sizing:border-box;}',
            '.attraction-mv-field .btn{padding:2px 4px;font-size:11px;min-height:26px;height:26px;line-height:1;width:100%;}',
            '.attraction-mv-panel .mv-coverage-wrap{margin-top:8px;position:relative;display:block!important;clear:both!important;}',
            '.attraction-mv-panel .mv-coverage-cards{gap:4px;margin-top:4px;}',
            '.attraction-mv-panel .mv-coverage-card{padding:2px 6px;min-height:22px;gap:4px;}',
            '.attraction-mv-panel .mv-card-label{font-size:9px;}',
            '.attraction-mv-panel .mv-card-value{font-size:12px;}',
            '.attraction-mv-panel .mv-coverage-banner{margin-top:4px;padding:4px 8px;font-size:10px;}',
            'tr.attraction-row .attraction-transfer-way,'
            + 'tr.attraction-row .attraction-transfer-type,'
            + 'tr.attraction-row .attraction-vehicle-type{display:none!important;}',
            'tr.attraction-row td:has(> .attraction-transfer-way),'
            + 'tr.attraction-row td:has(> .attraction-transfer-type),'
            + 'tr.attraction-row td:has(> .attraction-vehicle-type){display:none!important;}'
        ].join('');
    }

    function findAttractionRowByUid(uid) {
        if (!uid) return null;
        return document.querySelector(
            '#attractionsTableBody tr.attraction-row .attraction-checkbox[data-unique-id="' + uid + '"]'
        )?.closest('tr.attraction-row')
            || document.querySelector('#attractionsTableBody tr.attraction-row[data-unique-id="' + uid + '"]')
            || null;
    }

    function getAttractionRowPax(row) {
        if (!row) {
            return {
                adults: parseInt(document.getElementById('adultCountInput')?.value || '0', 10) || 0,
                child: parseInt(document.getElementById('childCountInput')?.value || '0', 10) || 0,
                infant: 0
            };
        }
        return {
            adults: Math.max(0, parseInt(row.querySelector('.attraction-adult-qty')?.value || '0', 10) || 0),
            child: Math.max(0, parseInt(row.querySelector('.attraction-child-qty')?.value || '0', 10) || 0),
            infant: Math.max(0, parseInt(row.querySelector('.attraction-infant-qty')?.value || '0', 10) || 0)
        };
    }

    function resolveVehicleOptionSeats(opt) {
        if (!opt) return 0;
        let seats = parseInt(
            opt.getAttribute('data-seating')
            || opt.getAttribute('data-city-tour-seating')
            || opt.dataset?.seating
            || '0',
            10
        ) || 0;
        if (!seats) {
            const m = String(opt.textContent || opt.text || '').match(/\((\d+)\s*seats?\)/i);
            if (m) seats = parseInt(m[1], 10) || 0;
        }
        return seats;
    }

    function refreshAttractionMvItemHeader(row, idx) {
        if (!row) return;
        const sel = row.querySelector('.arr-dep-vehicle-select');
        const opt = sel?.selectedOptions?.[0];
        const seats = resolveVehicleOptionSeats(opt);
        const qty = Math.max(1, parseInt(row.querySelector('.arr-dep-vehicle-qty')?.value || '1', 10) || 1);
        const adults = Math.max(0, parseInt(row.querySelector('.arr-dep-vehicle-adults')?.value || '0', 10) || 0);
        const child = Math.max(0, parseInt(row.querySelector('.arr-dep-vehicle-child')?.value || '0', 10) || 0);
        const typeVal = row.querySelector('.arr-dep-vehicle-xfer-type')?.value || 'S';
        const wayVal = row.querySelector('.arr-dep-vehicle-way')?.value || 'both-way';
        const sell = parseFloat(row.querySelector('.arr-dep-line-sell')?.textContent || '0') || 0;
        const nameEl = row.querySelector('[data-mv-item-name]');
        const seatsEl = row.querySelector('.arr-dep-vehicle-seats');
        const tagsEl = row.querySelector('[data-mv-item-tags]');
        const sellEl = row.querySelector('[data-mv-item-sell]');
        const idxEl = row.querySelector('.mv-row-index, .attraction-mv-item-idx');
        if (idxEl) idxEl.textContent = String((idx != null ? idx : 0) + 1);
        if (seatsEl) seatsEl.textContent = String(seats);
        if (nameEl) {
            const label = sel?.value
                ? String(opt?.text || 'Vehicle').replace(/\s*\(\d+\s*seats?\)\s*$/i, '').trim()
                : 'Select vehicle';
            nameEl.textContent = 'Vehicle ' + ((idx != null ? idx : 0) + 1) + ' · ' + label;
        }
        if (tagsEl) {
            const cap = seats * qty;
            const assigned = adults + child;
            const seatClass = seats > 0 ? 'is-seats' : 'is-warn';
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

    // ---- Patch A/D getters so attr:{uid} reuses the same vehicle row engine ----
    if (typeof window.getArrDepVehicleRowsContainer === 'function') {
        const _rows = window.getArrDepVehicleRowsContainer;
        window.getArrDepVehicleRowsContainer = function (side) {
            if (isAttrMvSide(side)) {
                return document.getElementById('attractionVehicleRows_' + attrMvUid(side));
            }
            return _rows(side);
        };
    }
    if (typeof window.getArrDepVehicleMasterSelect === 'function') {
        const _master = window.getArrDepVehicleMasterSelect;
        window.getArrDepVehicleMasterSelect = function (side) {
            if (isAttrMvSide(side)) {
                return document.getElementById('attractionVehicleMaster_' + attrMvUid(side));
            }
            return _master(side);
        };
    }
    if (typeof window.getArrDepTransferType === 'function') {
        const _tt = window.getArrDepTransferType;
        window.getArrDepTransferType = function (side) {
            if (isAttrMvSide(side)) {
                const uid = attrMvUid(side);
                const container = document.getElementById('attractionVehicleRows_' + uid);
                const fromVehicle = container?.querySelector('.arr-dep-vehicle-xfer-type')?.value;
                const row = findAttractionRowByUid(uid);
                const sel = row?.querySelector('.attraction-transfer-type')
                    || document.querySelector('.attraction-transfer-type[data-unique-id="' + uid + '"]');
                const raw = fromVehicle || sel?.value || 'S';
                return (typeof normalizeTransferTypePS === 'function')
                    ? normalizeTransferTypePS(raw)
                    : raw;
            }
            return _tt(side);
        };
    }
    if (typeof window.getArrDepHeaderPax === 'function') {
        const _hp = window.getArrDepHeaderPax;
        window.getArrDepHeaderPax = function (side) {
            if (isAttrMvSide(side)) return getAttractionRowPax(findAttractionRowByUid(attrMvUid(side)));
            return _hp(side);
        };
    }
    if (typeof window.getArrDepTourPax === 'function') {
        const _tp = window.getArrDepTourPax;
        window.getArrDepTourPax = function (side) {
            if (isAttrMvSide(side)) {
                const p = getAttractionRowPax(findAttractionRowByUid(attrMvUid(side)));
                return Math.max(0, (p.adults || 0) + (p.child || 0));
            }
            return _tp(side);
        };
    }
    if (typeof window.updateArrDepVehicleCoverage === 'function') {
        const _cov = window.updateArrDepVehicleCoverage;
        window.updateArrDepVehicleCoverage = function (side) {
            if (!isAttrMvSide(side)) return _cov(side);
            const uid = attrMvUid(side);
            const wrap = document.getElementById('attractionVehicleCoverage_' + uid);
            if (!wrap) return;
            const tourPax = window.getArrDepTourPax(side);
            const container = window.getArrDepVehicleRowsContainer(side);
            const rows = container
                ? Array.from(container.querySelectorAll('.arr-dep-vehicle-row'))
                : [];
            let covered = 0;
            let seatCap = 0;
            rows.forEach(function (row, idx) {
                const sel = row.querySelector('.arr-dep-vehicle-select');
                const opt = sel?.selectedOptions?.[0];
                const seats = resolveVehicleOptionSeats(opt);
                const qty = Math.max(1, parseInt(row.querySelector('.arr-dep-vehicle-qty')?.value || '1', 10) || 1);
                const adults = Math.max(0, parseInt(row.querySelector('.arr-dep-vehicle-adults')?.value || '0', 10) || 0);
                const child = Math.max(0, parseInt(row.querySelector('.arr-dep-vehicle-child')?.value || '0', 10) || 0);
                if (sel && sel.value) {
                    covered += adults + child;
                    seatCap += seats * qty;
                }
                refreshAttractionMvItemHeader(row, idx);
                if (typeof recalcArrDepVehicleRowTotals === 'function') {
                    recalcArrDepVehicleRowTotals(side, row);
                }
            });
            if (covered <= 0 && seatCap > 0) covered = Math.min(tourPax, seatCap);
            const remaining = Math.max(0, tourPax - covered);
            const totalEl = wrap.querySelector('[data-mv-total]');
            const coveredEl = wrap.querySelector('[data-mv-covered]');
            const remainingEl = wrap.querySelector('[data-mv-remaining]');
            const banner = wrap.querySelector('[data-mv-banner]');
            const coveredCard = wrap.querySelector('[data-mv-card="covered"]');
            const remainCard = wrap.querySelector('[data-mv-card="remaining"]');
            if (totalEl) totalEl.textContent = String(tourPax);
            if (coveredEl) coveredEl.textContent = String(covered);
            if (remainingEl) remainingEl.textContent = String(remaining);
            if (coveredCard) coveredCard.classList.toggle('is-ok', covered >= tourPax && rows.some(r => r.querySelector('.arr-dep-vehicle-select')?.value));
            if (remainCard) {
                remainCard.classList.toggle('is-warn', remaining > 0);
                remainCard.classList.toggle('is-ok', remaining === 0 && covered > 0);
            }
            wrap.style.display = '';
            if (banner) {
                banner.className = 'mv-coverage-banner';
                if (tourPax <= 0) {
                    banner.classList.add('is-muted');
                    banner.innerHTML = '<i class="ri-information-line"></i> Set adults/child on the attraction row to calculate coverage.';
                } else if (!rows.some(r => r.querySelector('.arr-dep-vehicle-select')?.value)) {
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
        };
    }

    function buildAttractionMvItemHtml(side, selectedId, qty, transferType, paxOpts) {
        const master = (typeof getArrDepVehicleMasterSelect === 'function')
            ? getArrDepVehicleMasterSelect(side)
            : null;
        const optsHtml = master ? master.innerHTML : '<option value="">Select Vehicle</option>';
        const q = Math.max(1, parseInt(qty || 1, 10) || 1);
        const tt = (typeof normalizeTransferTypePS === 'function')
            ? normalizeTransferTypePS(transferType || getArrDepTransferType(side))
            : (transferType || 'S');
        const sSel = tt === 'S' ? ' selected' : '';
        const pSel = tt === 'P' ? ' selected' : '';
        paxOpts = paxOpts || {};
        const aVal = Math.max(0, parseInt(paxOpts.adults ?? paxOpts.adultsQty ?? 0, 10) || 0);
        const cVal = Math.max(0, parseInt(paxOpts.child ?? paxOpts.childQty ?? 0, 10) || 0);
        const iVal = Math.max(0, parseInt(paxOpts.infant ?? paxOpts.infantQty ?? 0, 10) || 0);
        // Default way from explicit paxOpts.way (row-level Way removed — per-vehicle only)
        let defaultWay = paxOpts.way || 'both-way';
        const oneSel = (defaultWay === 'one-way') ? ' selected' : '';
        const bothSel = (defaultWay !== 'one-way') ? ' selected' : '';
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

    window.toggleAttractionMvItem = function (btn) {
        const item = btn?.closest?.('.attraction-mv-item');
        if (!item) return;
        item.classList.toggle('is-open');
    };

    window.toggleAttractionMvPanel = function (btn) {
        const panel = btn?.closest?.('.attraction-mv-panel');
        if (!panel) return;
        const collapsed = panel.classList.toggle('is-collapsed');
        const label = btn.querySelector('span');
        if (label) label.textContent = collapsed ? 'Show' : 'Vehicles';
        btn.setAttribute('title', collapsed ? 'Expand vehicles' : 'Collapse vehicles');
        btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    };

    // Dedicated entry point — avoids broken onclick quotes from JSON.stringify in HTML attrs
    window.addAttractionMvVehicle = function (uid, selectedId, qty, transferType, paxOpts) {
        const side = attrMvSide(String(uid || '').replace(/^attr:/, ''));
        // Expanding panel if user adds while collapsed
        const panel = document.querySelector('.attraction-mv-panel[data-unique-id="' + String(uid || '').replace(/^attr:/, '') + '"]');
        if (panel) {
            panel.classList.remove('is-collapsed');
            const collapseBtn = panel.querySelector('.attr-mv-collapse-btn span');
            if (collapseBtn) collapseBtn.textContent = 'Vehicles';
        }
        return window.addArrDepVehicleRow(side, selectedId, qty, transferType, paxOpts);
    };

    // Override addArrDepVehicleRow for attraction accordion items (always patch)
    (function patchAddArrDepVehicleRow() {
        const _addRow = (typeof window.addArrDepVehicleRow === 'function')
            ? window.addArrDepVehicleRow
            : null;
        window.addArrDepVehicleRow = function (side, selectedId, qty, transferType, paxOpts) {
            if (!isAttrMvSide(side)) {
                return _addRow ? _addRow(side, selectedId, qty, transferType, paxOpts) : null;
            }
            try {
                const container = (typeof window.getArrDepVehicleRowsContainer === 'function')
                    ? window.getArrDepVehicleRowsContainer(side)
                    : document.getElementById('attractionVehicleRows_' + attrMvUid(side));
                if (!container) {
                    console.warn('Attraction MV: vehicle container not found for', side);
                    return null;
                }
                const wrap = document.createElement('div');
                wrap.innerHTML = buildAttractionMvItemHtml(side, selectedId, qty, transferType, paxOpts);
                const row = wrap.firstElementChild;
                if (!row) return null;
                container.appendChild(row);
                Array.from(container.querySelectorAll('.attraction-mv-item, .arr-dep-vehicle-row')).forEach(function (item, i) {
                    // Never leave table-row / absolute stacking artifacts
                    if (item.tagName === 'TR') {
                        item.style.cssText = 'display:block!important;position:static!important;width:100%;margin:0 0 10px 0;';
                    }
                    item.style.zIndex = '';
                    item.style.position = 'static';
                    item.classList.toggle('is-open', item === row);
                    const idxEl = item.querySelector('.mv-row-index, .attraction-mv-item-idx');
                    if (idxEl) idxEl.textContent = String(i + 1);
                    refreshAttractionMvItemHeader(item, i);
                });
                const sel = row.querySelector('.arr-dep-vehicle-select');
                const typeEl = row.querySelector('.arr-dep-vehicle-xfer-type');
                if (typeEl && transferType) {
                    typeEl.value = (typeof normalizeTransferTypePS === 'function')
                        ? normalizeTransferTypePS(transferType) : transferType;
                }
                if (sel && selectedId) {
                    sel.value = String(selectedId);
                    if (!sel.value) {
                        const match = Array.from(sel.options).find(o => String(o.value) === String(selectedId));
                        if (match) sel.value = match.value;
                    }
                }
                const wayEl = row.querySelector('.arr-dep-vehicle-way');
                if (wayEl && paxOpts && paxOpts.way) {
                    wayEl.value = paxOpts.way;
                }
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
                    // Fill remaining pax into the new vehicle (even before vehicle selected, will re-fill on change)
                    autoFillArrDepRowPax(side, row);
                }
                if (typeof syncMasterTransferTypeFromRows === 'function') syncMasterTransferTypeFromRows(side);
                if (typeof syncArrDepPrimaryVehicleSelect === 'function') syncArrDepPrimaryVehicleSelect(side);
                if (typeof updateArrDepVehicleCoverage === 'function') updateArrDepVehicleCoverage(side);
                scheduleAttractionMvPriceRefresh(side);
                row.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                return row;
            } catch (err) {
                console.error('Attraction MV add vehicle failed', err);
                return null;
            }
        };
    })();

    if (typeof window.bindArrDepVehicleRowEvents === 'function') {
        const _bind = window.bindArrDepVehicleRowEvents;
        window.bindArrDepVehicleRowEvents = function (side, row) {
            _bind(side, row);
            if (!isAttrMvSide(side)) return;
            const refresh = function () {
                // Re-clamp remaining adults/child/infant across vehicles before price refresh
                if (typeof clampArrDepRowPaxToCapacity === 'function') {
                    clampArrDepRowPaxToCapacity(row, side);
                }
                if (typeof syncAllMultiVehiclePaxLimits === 'function') {
                    syncAllMultiVehiclePaxLimits(side);
                }
                scheduleAttractionMvPriceRefresh(side);
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

    // When removing accordion item, remove the whole .attraction-mv-item (not only tr)
    document.addEventListener('click', function (ev) {
        const btn = ev.target?.closest?.('.attraction-mv-item .arr-dep-vehicle-remove');
        if (!btn) return;
        const item = btn.closest('.attraction-mv-item');
        const container = item?.parentElement;
        if (!item || !container || !container.id || container.id.indexOf('attractionVehicleRows_') !== 0) return;
        // Let default bind handler run first; if it only removes nothing useful, ensure item gone
        setTimeout(function () {
            if (item.parentElement) item.remove();
            const uid = container.id.replace('attractionVehicleRows_', '');
            if (typeof updateArrDepVehicleCoverage === 'function') {
                updateArrDepVehicleCoverage(attrMvSide(uid));
            }
            scheduleAttractionMvPriceRefresh(attrMvSide(uid));
        }, 0);
    }, true);

    window.buildAttractionTransferPanelHtml = function (uid, attractionName, vehicleOptionsHtml, asInner) {
        ensureAttractionMvStyles();
        const name = String(attractionName || 'Attraction').replace(/</g, '&lt;');
        const opts = vehicleOptionsHtml || '<option value="">Select Vehicle</option>';
        const safeUid = String(uid || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
        const inner = ''
            + '<div class="attraction-mv-panel" data-unique-id="' + uid + '">'
            +   '<div class="attr-mv-head">'
            +     '<div class="attr-mv-head-left">'
            +       '<h6 class="attr-mv-title"><i class="ri-car-line"></i> Transfer Vehicles</h6>'
            +       '<p class="attr-mv-sub">for <strong>' + name + '</strong> · add vehicles by pax</p>'
            +       '<label class="attr-mv-include">'
            +         '<input type="checkbox" class="form-check-input attraction-transfer-included" data-unique-id="' + uid + '" checked'
            +         ' onchange="onAttractionTransferIncludedToggle(this)"> Include</label>'
            +     '</div>'
            +     '<div class="attr-mv-head-actions">'
            +       '<button type="button" class="attr-mv-collapse-btn" title="Collapse / expand vehicles"'
            +         " onclick=\"toggleAttractionMvPanel(this)\">"
            +         '<i class="ri-arrow-down-s-line"></i><span>Vehicles</span></button>'
            +       '<button type="button" class="btn btn-sm btn-primary attr-mv-add-btn" style="font-size:10px;white-space:nowrap;padding:3px 8px;"'
            +         " onclick=\"addAttractionMvVehicle('" + safeUid + "')\">"
            +         '<i class="ri-add-line"></i> Add Vehicle</button>'
            +     '</div>'
            +   '</div>'
            +   '<div class="attr-mv-body">'
            +     '<div id="attractionVehicleRows_' + uid + '" class="attraction-mv-accordion arr-dep-vehicle-rows"></div>'
            +     '<div id="attractionVehicleCoverage_' + uid + '" class="mv-coverage-wrap" style="display:none;">'
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
            +   '<select class="d-none" id="attractionVehicleMaster_' + uid + '" aria-hidden="true" tabindex="-1">'
            +     opts
            +   '</select>'
            + '</div>';
        if (asInner) return inner;
        return ''
            + '<tr class="attraction-transfer-panel-row" data-unique-id="' + uid + '" style="display:none;">'
            +   '<td colspan="20" style="padding:6px 8px; background:#f8fafc; border-bottom:1px solid #e2e8f0;">'
            +     inner
            +   '</td>'
            + '</tr>';
    };

    window.ensureAttractionTransferPanelAfterRow = function (attractionRow, vehicles) {
        if (!attractionRow) return null;
        ensureAttractionMvStyles();
        const checkbox = attractionRow.querySelector('.attraction-checkbox, .attraction-transfer-checkbox');
        let uid = checkbox?.getAttribute('data-unique-id')
            || attractionRow.getAttribute('data-unique-id')
            || '';
        if (!uid) {
            const attrId = attractionRow.getAttribute('data-attraction-id') || '';
            const ticketId = attractionRow.getAttribute('data-ticket-id') || '0';
            uid = attrId + '_' + ticketId;
        }
        attractionRow.setAttribute('data-unique-id', uid);
        const name = attractionRow.getAttribute('data-attraction-name')
            || attractionRow.querySelector('td:nth-child(2)')?.textContent?.trim()
            || 'Attraction';
        const masterSrc = attractionRow.querySelector('.attraction-vehicle-type');
        const opts = (masterSrc && masterSrc.innerHTML)
            ? masterSrc.innerHTML
            : (typeof getVehicleOptionsHTML === 'function' ? getVehicleOptionsHTML() : '<option value="">Select Vehicle</option>');

        let panel = attractionRow.nextElementSibling;
        // Rebuild if old table-style panel OR Add Vehicle still uses broken onclick OR missing per-vehicle Way
        const needsRebuild = panel
            && panel.classList.contains('attraction-transfer-panel-row')
            && panel.getAttribute('data-unique-id') === uid
            && (
                !panel.querySelector('.attraction-mv-accordion')
                || !String(panel.querySelector('.attr-mv-add-btn, .attr-mv-head button.btn-primary')?.getAttribute('onclick') || '')
                    .includes('addAttractionMvVehicle')
                || !panel.querySelector('.attr-mv-collapse-btn')
                || (panel.querySelector('.arr-dep-vehicle-row')
                    && !panel.querySelector('.arr-dep-vehicle-way'))
                || (panel.querySelector('.arr-dep-vehicle-row')
                    && !panel.querySelector('.attraction-mv-field--vehicle'))
            );
        if (!panel || !panel.classList.contains('attraction-transfer-panel-row')
            || panel.getAttribute('data-unique-id') !== uid || needsRebuild) {
            // Preserve existing vehicle selections when rebuilding for button fix
            let existingVehicles = null;
            if (needsRebuild && panel && typeof collectArrDepVehiclesPayload === 'function') {
                try {
                    existingVehicles = collectArrDepVehiclesPayload(attrMvSide(uid));
                } catch (e) { existingVehicles = null; }
            }
            if (needsRebuild && panel) panel.remove();
            const html = window.buildAttractionTransferPanelHtml(uid, name, opts, false);
            attractionRow.insertAdjacentHTML('afterend', html);
            panel = attractionRow.nextElementSibling;
            if (existingVehicles && existingVehicles.length) {
                vehicles = existingVehicles;
            }
        }

        // Hide leftover row cells only (Vehicle / Type / Way were removed from markup)
        const vehicleTd = attractionRow.querySelector('.attraction-vehicle-type')?.closest('td');
        const typeTd = attractionRow.querySelector('.attraction-transfer-type')?.closest('td');
        const wayTd = attractionRow.querySelector('.attraction-transfer-way')?.closest('td');
        if (vehicleTd) vehicleTd.style.display = 'none';
        if (typeTd) typeTd.style.display = 'none';
        if (wayTd) wayTd.style.display = 'none';
        // Do NOT hide Guide / Qty / Select Guide headers — those are still live columns
        const table = attractionRow.closest('table');
        if (table) {
            Array.from(table.querySelectorAll('thead th')).forEach(function (th) {
                const label = String(th.textContent || '').trim().toLowerCase();
                if (label === 'way' || label === 'type' || label === 'vehicle') {
                    th.style.display = 'none';
                }
            });
        }

        // Always rebind Add Vehicle to the safe handler (in case panel was cached)
        const addBtn = panel?.querySelector?.('.attr-mv-add-btn, .attr-mv-head button.btn-primary');
        if (addBtn) {
            const safeUid = String(uid || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
            addBtn.setAttribute('onclick', "addAttractionMvVehicle('" + safeUid + "')");
        }
        const collapseBtn = panel?.querySelector?.('.attr-mv-collapse-btn');
        if (collapseBtn && !collapseBtn.getAttribute('onclick')) {
            collapseBtn.setAttribute('onclick', 'toggleAttractionMvPanel(this)');
        }

        const side = attrMvSide(uid);
        const container = document.getElementById('attractionVehicleRows_' + uid);
        const hasRows = !!(container && container.querySelector('.arr-dep-vehicle-row'));
        if (typeof ensureArrDepVehicleRows === 'function') {
            if (Array.isArray(vehicles) && vehicles.length) {
                ensureArrDepVehicleRows(side, vehicles);
            } else if (!hasRows) {
                ensureArrDepVehicleRows(side, [{ vehicleId: '', qty: 1, transferType: 'S' }]);
            }
        }
        return panel;
    };

    window.toggleAttractionTransferPanel = function (checkboxOrRow, forceShow) {
        const checkbox = checkboxOrRow?.classList?.contains('attraction-transfer-checkbox')
            ? checkboxOrRow
            : checkboxOrRow?.querySelector?.('.attraction-transfer-checkbox');
        const row = checkbox?.closest('tr.attraction-row') || checkboxOrRow?.closest?.('tr.attraction-row');
        if (!row) return;
        const show = forceShow != null ? !!forceShow : !!(checkbox && checkbox.checked);
        const panel = window.ensureAttractionTransferPanelAfterRow(row);
        if (panel && panel.classList.contains('attraction-transfer-panel-row')) {
            panel.style.display = show ? 'table-row' : 'none';
        }
        const included = (panel || row).querySelector?.('.attraction-transfer-included');
        if (included) included.checked = show;
        if (show) {
            const uid = row.getAttribute('data-unique-id')
                || panel?.getAttribute?.('data-unique-id');
            if (uid) {
                scheduleAttractionMvPriceRefresh(attrMvSide(uid));
                if (typeof updateArrDepVehicleCoverage === 'function') {
                    updateArrDepVehicleCoverage(attrMvSide(uid));
                }
            }
            // Do NOT auto-check the ticket here — Tour Sites pre-check Transfer/Pickup
            // without selecting the ticket. Ticket selection is user/default only.
        }
    };

    window.onAttractionTransferIncludedToggle = function (el) {
        const uid = el?.getAttribute('data-unique-id');
        if (!uid) return;
        const row = findAttractionRowByUid(uid);
        const xferCb = row?.querySelector('.attraction-transfer-checkbox');
        if (xferCb) {
            xferCb.checked = !!el.checked;
            window.toggleAttractionTransferPanel(xferCb);
        }
    };

    const _attrMvTimers = {};
    window.scheduleAttractionMvPriceRefresh = function (side) {
        const key = String(side || '');
        clearTimeout(_attrMvTimers[key]);
        _attrMvTimers[key] = setTimeout(function () {
            refreshAttractionMvPrices(side);
        }, 220);
    };

    window.refreshAttractionMvPrices = async function (side) {
        if (!isAttrMvSide(side)) return null;
        const uid = attrMvUid(side);
        const row = findAttractionRowByUid(uid);
        if (!row) return null;
        const destSelect = row.querySelector('.attraction-transfer-destination');
        const waySelect = row.querySelector('.attraction-transfer-way');
        const isPickup = !!row.querySelector('.attraction-is-pickup')?.checked;
        const attractionId = row.getAttribute('data-attraction-id') || '';
        const attractionName = row.getAttribute('data-attraction-name') || '';
        if (!destSelect || !destSelect.value || !attractionId) {
            if (typeof updateArrDepVehicleCoverage === 'function') updateArrDepVehicleCoverage(side);
            return null;
        }
        const destOpt = destSelect.selectedOptions?.[0];
        const destinationType = destOpt?.getAttribute('data-type') || 'other';
        let actualDestinationId = destSelect.value;
        if (destinationType === 'port' && destOpt?.getAttribute('data-port-id')) {
            actualDestinationId = destOpt.getAttribute('data-port-id');
        } else if (destinationType === 'hotel' && destOpt?.getAttribute('data-hotel-unique-id')) {
            actualDestinationId = destOpt.getAttribute('data-hotel-unique-id');
        } else if (destinationType === 'attraction' && destOpt?.getAttribute('data-attraction-id')) {
            actualDestinationId = destOpt.getAttribute('data-attraction-id');
        } else if (destinationType === 'restaurant' && destOpt?.getAttribute('data-restaurant-id')) {
            actualDestinationId = destOpt.getAttribute('data-restaurant-id');
        }
        const defaultWay = waySelect?.value || 'both-way';
        let pickupId, pickupType, dropoffId, dropoffType;
        if (isPickup) {
            pickupId = actualDestinationId; pickupType = destinationType;
            dropoffId = attractionId; dropoffType = 'attraction';
        } else {
            pickupId = attractionId; pickupType = 'attraction';
            dropoffId = actualDestinationId; dropoffType = destinationType;
        }

        // Enrich payload with per-row Way before pricing
        const vehicles = (typeof collectArrDepVehiclesPayload === 'function')
            ? collectArrDepVehiclesPayload(side) : [];
        const container = window.getArrDepVehicleRowsContainer(side);
        const domRows = container ? Array.from(container.querySelectorAll('.arr-dep-vehicle-row')) : [];
        vehicles.forEach(function (v, i) {
            const wayEl = domRows[i]?.querySelector('.arr-dep-vehicle-way');
            v.way = wayEl?.value || v.way || defaultWay;
        });
        const pax = getAttractionRowPax(row);
        const dmcId = '{{ $dmc_id ?? "" }}';
        let totalCost = 0, totalSell = 0, storedCost = 0, storedSell = 0;
        const enriched = [];

        for (let i = 0; i < vehicles.length; i++) {
            const v = vehicles[i];
            let zonePrice = { private_price: 0, shared_price: 0, private_cost_price: 0, shared_cost_price: 0 };
            try {
                if (typeof fetchZonePrice === 'function' && v.vehicleId && pickupId && dropoffId && dmcId) {
                    zonePrice = await fetchZonePrice(v.vehicleId, pickupId, pickupType, dropoffId, dropoffType, dmcId);
                }
            } catch (e) { /* non-fatal */ }
            const rowType = (typeof normalizeTransferTypePS === 'function')
                ? normalizeTransferTypePS(v.transferType || v.type || 'S')
                : (v.transferType || 'S');
            const rowShared = rowType === 'S';
            const vAdults = Math.max(0, parseInt(v.adults ?? v.adultsQty ?? 0, 10) || 0);
            const vChild = Math.max(0, parseInt(v.child ?? v.childQty ?? 0, 10) || 0);
            const vInfant = Math.max(0, parseInt(v.infant ?? v.infantQty ?? 0, 10) || 0);
            const vehiclePax = Math.max(0, vAdults + vChild);
            const billPax = vehiclePax > 0 ? vehiclePax : Math.max(0, (pax.adults || 0) + (pax.child || 0));
            const qty = Math.max(1, parseInt(v.qty || 1, 10) || 1);
            const way = v.way || defaultWay;
            const unit = (typeof calculateTransferPrice === 'function')
                ? calculateTransferPrice(zonePrice, rowType, way, vAdults || pax.adults, vChild || pax.child)
                : { cost: 0, sell: 0 };
            let unitCost = parseFloat(unit.cost) || 0;
            let unitSell = parseFloat(unit.sell) || 0;
            const domRow = domRows[i];
            const ucEl = domRow?.querySelector('.arr-dep-unit-cost');
            const usEl = domRow?.querySelector('.arr-dep-unit-sell');
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
            const lineCost = rowShared ? unitCost * billPax : unitCost * qty;
            const lineSell = rowShared ? unitSell * billPax : unitSell * qty;
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
                recalcArrDepVehicleRowTotals(side, domRow);
            }
            const seats = resolveVehicleOptionSeats(domRow?.querySelector('.arr-dep-vehicle-select')?.selectedOptions?.[0])
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

        if (typeof updateArrDepVehicleCoverage === 'function') updateArrDepVehicleCoverage(side);
        if (typeof syncArrDepPrimaryVehicleSelect === 'function') syncArrDepPrimaryVehicleSelect(side);

        const primaryWay = (enriched[0] && enriched[0].way) || defaultWay;
        const result = {
            vehicles: enriched,
            totalCost: totalCost,
            totalSell: totalSell,
            storedCost: storedCost,
            storedSell: storedSell,
            pickupId: pickupId,
            pickupType: pickupType,
            pickupName: isPickup
                ? (destOpt?.getAttribute('data-name') || destOpt?.text || '')
                : attractionName,
            dropoffId: dropoffId,
            dropoffType: dropoffType,
            dropoffName: isPickup
                ? attractionName
                : (destOpt?.getAttribute('data-name') || destOpt?.text || ''),
            way: primaryWay
        };
        row._attractionMvResult = result;
        return result;
    };

    window.buildAttractionTransferInfoFromRow = async function (opts) {
        opts = opts || {};
        const row = opts.row || null;
        if (!row) return null;
        const xferCb = row.querySelector('.attraction-transfer-checkbox');
        if (!xferCb || !xferCb.checked) return null;
        const destSelect = row.querySelector('.attraction-transfer-destination');
        if (!destSelect || !destSelect.value) return null;

        window.ensureAttractionTransferPanelAfterRow(row);
        const uid = row.getAttribute('data-unique-id')
            || row.querySelector('.attraction-checkbox')?.getAttribute('data-unique-id');
        const side = attrMvSide(uid);
        let result = row._attractionMvResult;
        try {
            result = await refreshAttractionMvPrices(side) || result;
        } catch (e) {
            console.warn('Attraction multi-vehicle transfer build failed', e);
        }
        if (!result || !Array.isArray(result.vehicles) || !result.vehicles.length) {
            return null;
        }
        const primary = result.vehicles[0];
        const transferWay = result.way || row.querySelector('.attraction-transfer-way')?.value || 'both-way';
        const isDestinationPickup = !!row.querySelector('.attraction-is-pickup')?.checked;
        const attractionName = row.getAttribute('data-attraction-name') || '';
        const destOpt = destSelect.selectedOptions?.[0];
        const destinationName = destOpt?.getAttribute('data-name') || destOpt?.text || '';
        const destinationType = destOpt?.getAttribute('data-type') || 'other';
        const displayCost = parseFloat(result.storedCost != null ? result.storedCost : result.totalCost) || 0;
        const displaySell = parseFloat(result.storedSell != null ? result.storedSell : result.totalSell) || 0;
        const lineTotalCost = parseFloat(result.totalCost) || 0;
        const lineTotalSell = parseFloat(result.totalSell) || 0;

        return {
            id: opts.transferId || (typeof generateId === 'function' ? generateId('transfer') : ('transfer-' + Date.now())),
            type: primary.transferType || primary.type || 'S',
            transferType: primary.transferType || primary.type || 'S',
            way: transferWay,
            vehicleId: primary.vehicleId || '',
            vehicleName: primary.vehicleName || '',
            vehicleType: primary.vehicleType || '',
            capacity: primary.seats || 0,
            vehicleQty: primary.qty || 1,
            vehicles: result.vehicles,
            // Listing may show unit×qty via storedCost; orders MUST use line totals
            cost: lineTotalCost > 0 ? lineTotalCost : displayCost,
            sell: lineTotalSell > 0 ? lineTotalSell : displaySell,
            storedCost: displayCost,
            storedSell: displaySell,
            lineCost: lineTotalCost,
            lineSell: lineTotalSell,
            totalCost: lineTotalCost,
            totalSell: lineTotalSell,
            totalPrice: lineTotalSell > 0 ? lineTotalSell : displaySell,
            service: (result.pickupName || attractionName) + ' / ' + (result.dropoffName || destinationName),
            attractionName: attractionName,
            destination: destinationName,
            destinationId: destSelect.value,
            destinationType: destinationType,
            pickup: result.pickupName || attractionName,
            dropoff: result.dropoffName || destinationName,
            pickupId: result.pickupId || '',
            dropoffId: result.dropoffId || '',
            dropId: result.dropoffId || '',
            pickupType: result.pickupType || '',
            dropoffType: result.dropoffType || '',
            isDestinationPickup: isDestinationPickup,
            dateTime: opts.dateTime || '',
            adults: opts.adultsQty != null ? opts.adultsQty : getAttractionRowPax(row).adults,
            child: opts.childQty != null ? opts.childQty : getAttractionRowPax(row).child,
            infant: opts.infantQty != null ? opts.infantQty : getAttractionRowPax(row).infant,
            infantQty: opts.infantQty != null ? opts.infantQty : getAttractionRowPax(row).infant,
            taxIncluded: true,
            transportMode: 'local',
            isStandalone: false,
            sourceType: 'tour',
            sourceId: opts.tourId || null,
            zonePrivatePrice: primary.zonePrivatePrice || 0,
            zoneSharedPrice: primary.zoneSharedPrice || 0,
            zonePrivateCostPrice: primary.zonePrivateCostPrice || 0,
            zoneSharedCostPrice: primary.zoneSharedCostPrice || 0
        };
    };

    window.readAttractionTicketCostSell = function (row) {
        const money = function (v) {
            const n = parseFloat(String(v == null ? '0' : v).replace(/[^0-9.-]/g, ''));
            return Number.isFinite(n) ? n : 0;
        };
        if (!row) {
            return { adultCost: 0, adultSell: 0, childCost: 0, childSell: 0, infantCost: 0, infantSell: 0 };
        }
        const adultSell = money(row.querySelector('.attraction-adult-charge')?.value);
        const childSell = money(row.querySelector('.attraction-child-charge')?.value);
        const infantSell = money(row.querySelector('.attraction-infant-charge')?.value);
        const adultCost = money(row.getAttribute('data-adult-cost')) || adultSell;
        const childCost = money(row.getAttribute('data-child-cost')) || childSell;
        const infantCost = money(row.getAttribute('data-infant-cost')) || infantSell;
        return { adultCost: adultCost, adultSell: adultSell, childCost: childCost, childSell: childSell, infantCost: infantCost, infantSell: infantSell };
    };

    /** Normalize multi-vehicle rows for transfer_options.vehicles[] on order JSON. */
    window.mapAttractionTransferVehiclesForOrder = function (vehicles) {
        return (Array.isArray(vehicles) ? vehicles : []).map(function (v) {
            const rawType = String(v.transferType || v.type || 'S').toLowerCase();
            const isPrivate = rawType === 'p' || rawType === 'private';
            const tt = isPrivate ? 'P' : 'S';
            const typeLabel = isPrivate ? 'Private' : 'Shared';
            const qty = Math.max(1, parseInt(v.qty || v.quantity || 1, 10) || 1);
            const adults = Math.max(0, parseInt(v.adults ?? v.adultsQty ?? 0, 10) || 0);
            const child = Math.max(0, parseInt(v.child ?? v.childQty ?? 0, 10) || 0);
            const infant = Math.max(0, parseInt(v.infant ?? v.infantQty ?? 0, 10) || 0);
            const billPax = Math.max(0, adults + child);
            let unitCost = parseFloat(v.unitCost != null ? v.unitCost : v.unit_cost);
            let unitSell = parseFloat(v.unitSell != null ? v.unitSell : v.unit_sell);
            if (!Number.isFinite(unitCost)) unitCost = parseFloat(v.cost) || 0;
            if (!Number.isFinite(unitSell)) unitSell = parseFloat(v.sell) || 0;
            let lineCost = parseFloat(v.lineCost != null ? v.lineCost : v.line_cost);
            let lineSell = parseFloat(v.lineSell != null ? v.lineSell : v.line_sell);
            if (!Number.isFinite(lineCost)) {
                lineCost = tt === 'S' ? unitCost * Math.max(1, billPax || 1) : unitCost * qty;
            }
            if (!Number.isFinite(lineSell)) {
                lineSell = tt === 'S' ? unitSell * Math.max(1, billPax || 1) : unitSell * qty;
            }
            const vehicleId = String(v.vehicleId || v.vehicle_id || '');
            const vehicleName = v.vehicleName || v.vehicle_name || '';
            const vehicleType = v.vehicleType || v.vehicle_type || '';
            const seats = parseInt(v.seats || v.seating_capacity || 0, 10) || 0;
            return {
                vehicle_id: vehicleId,
                vehicle_name: vehicleName,
                vehicle_type: vehicleType,
                seats: seats,
                type: typeLabel,
                way: v.way || 'both-way',
                qty: qty,
                adults: adults,
                child: child,
                infant: infant,
                unit_cost: unitCost,
                unit_sell: unitSell,
                line_cost: lineCost,
                line_sell: lineSell,
                zone_private_price: parseFloat(v.zonePrivatePrice) || 0,
                zone_shared_price: parseFloat(v.zoneSharedPrice) || 0,
                zone_private_cost_price: parseFloat(v.zonePrivateCostPrice) || 0,
                zone_shared_cost_price: parseFloat(v.zoneSharedCostPrice) || 0,
                // hydrate aliases used by listing / edit
                vehicleId: vehicleId,
                vehicleName: vehicleName,
                vehicleType: vehicleType,
                transferType: tt,
                unitCost: unitCost,
                unitSell: unitSell,
                lineCost: lineCost,
                lineSell: lineSell,
                zonePrivatePrice: parseFloat(v.zonePrivatePrice || v.zone_private_price) || 0,
                zoneSharedPrice: parseFloat(v.zoneSharedPrice || v.zone_shared_price) || 0,
                zonePrivateCostPrice: parseFloat(v.zonePrivateCostPrice || v.zone_private_cost_price) || 0,
                zoneSharedCostPrice: parseFloat(v.zoneSharedCostPrice || v.zone_shared_cost_price) || 0
            };
        }).filter(function (v) { return !!v.vehicle_id; });
    };

    /**
     * Build transfer_options + transferInfo for enquiry order JSON.
     * Uses summed lineCost/lineSell (never re-multiply shared unit × tour pax).
     */
    window.buildAttractionTransferOrderBlocks = function (linkedTransfer, tour) {
        if (!linkedTransfer) return null;
        const pickupName = linkedTransfer.pickup || '';
        const dropoffName = linkedTransfer.dropoff || '';
        const vehicles = window.mapAttractionTransferVehiclesForOrder(linkedTransfer.vehicles || []);
        let adults = parseInt(linkedTransfer.adults || linkedTransfer.adultsQty || 0, 10) || 0;
        let child = parseInt(linkedTransfer.child || linkedTransfer.childQty || 0, 10) || 0;
        let infant = parseInt(linkedTransfer.infant || linkedTransfer.infantQty || 0, 10) || 0;
        if (adults <= 0 && child <= 0 && infant <= 0 && tour) {
            adults = parseInt(tour.adultsQty || tour.adultCount || 0, 10) || 0;
            child = parseInt(tour.childQty || tour.childCount || 0, 10) || 0;
            infant = parseInt(tour.infantQty || tour.infantCount || 0, 10) || 0;
        }
        if (vehicles.length) {
            adults = vehicles.reduce(function (s, v) { return s + (parseInt(v.adults, 10) || 0); }, 0);
            child = vehicles.reduce(function (s, v) { return s + (parseInt(v.child, 10) || 0); }, 0);
            infant = vehicles.reduce(function (s, v) { return s + (parseInt(v.infant, 10) || 0); }, 0);
        }
        let lineTotalCost = parseFloat(linkedTransfer.lineCost != null ? linkedTransfer.lineCost : linkedTransfer.totalCost);
        let lineTotalSell = parseFloat(linkedTransfer.lineSell != null ? linkedTransfer.lineSell : linkedTransfer.totalSell);
        if ((!Number.isFinite(lineTotalCost) || lineTotalCost <= 0) && vehicles.length) {
            lineTotalCost = vehicles.reduce(function (s, v) { return s + (parseFloat(v.lineCost) || 0); }, 0);
        }
        if ((!Number.isFinite(lineTotalSell) || lineTotalSell <= 0) && vehicles.length) {
            lineTotalSell = vehicles.reduce(function (s, v) { return s + (parseFloat(v.lineSell) || 0); }, 0);
        }
        // Legacy single-vehicle: only then may cost/sell be unit×qty (shared listing storage)
        if (!Number.isFinite(lineTotalCost) || lineTotalCost < 0) lineTotalCost = 0;
        if (!Number.isFinite(lineTotalSell) || lineTotalSell < 0) lineTotalSell = 0;
        if (lineTotalCost <= 0 && lineTotalSell <= 0 && !vehicles.length) {
            const baseCost = parseFloat(linkedTransfer.cost) || 0;
            const baseSell = parseFloat(linkedTransfer.sell) || baseCost;
            const typeLabel = (typeof normalizeAttractionTransferTypeLabel === 'function')
                ? normalizeAttractionTransferTypeLabel(linkedTransfer.type || linkedTransfer.transferType)
                : (String(linkedTransfer.type || '').toLowerCase().indexOf('s') === 0 || String(linkedTransfer.type || '').toLowerCase() === 'shared' ? 'Shared' : 'Private');
            const isShared = typeLabel === 'Shared';
            const billPax = Math.max(1, adults + child);
            lineTotalCost = isShared ? baseCost * billPax : baseCost;
            lineTotalSell = isShared ? baseSell * billPax : baseSell;
        }

        const primary = vehicles[0] || null;
        const typeLabel = primary
            ? (primary.type || 'Private')
            : ((typeof normalizeAttractionTransferTypeLabel === 'function')
                ? normalizeAttractionTransferTypeLabel(linkedTransfer.type || linkedTransfer.transferType)
                : 'Private');
        const typeCode = primary
            ? (primary.transferType || (typeLabel === 'Shared' ? 'S' : 'P'))
            : (linkedTransfer.type || (typeLabel === 'Shared' ? 'S' : 'P'));
        const vehicleId = (primary && primary.vehicle_id)
            || linkedTransfer.vehicleId
            || linkedTransfer.vehicle_id
            || '';
        const vehicleName = (primary && primary.vehicle_name)
            || linkedTransfer.vehicleName
            || '';
        const vehicleType = (primary && primary.vehicle_type)
            || linkedTransfer.vehicleType
            || '';
        const capacity = (primary && primary.seats)
            || linkedTransfer.capacity
            || 0;

        const transfer_options = {
            transfer_required: true,
            type: typeLabel,
            way: linkedTransfer.way || (primary && primary.way) || 'one-way',
            vehicle_id: String(vehicleId),
            vehicles: vehicles,
            cost: lineTotalCost,
            sell: lineTotalSell,
            totalPrice: lineTotalSell,
            adults: adults,
            child: child,
            infant: infant,
            pickup_location_name: pickupName,
            destination_name: dropoffName,
            pickupId: linkedTransfer.pickupId || '',
            dropId: linkedTransfer.dropId || linkedTransfer.dropoffId || '',
            pickupType: linkedTransfer.pickupType || '',
            dropType: linkedTransfer.dropType || linkedTransfer.dropoffType || ''
        };

        const transferInfo = {
            id: linkedTransfer.id || (tour && tour.transferId) || null,
            destination: linkedTransfer.destination || dropoffName,
            destinationId: linkedTransfer.destinationId || null,
            vehicleId: String(vehicleId),
            vehicleName: vehicleName,
            vehicleType: vehicleType,
            type: typeCode,
            way: linkedTransfer.way || (primary && primary.way) || 'one-way',
            pickup: pickupName,
            dropoff: dropoffName,
            isDestinationPickup: !!linkedTransfer.isDestinationPickup,
            vehicles: vehicles,
            cost: lineTotalCost,
            sell: lineTotalSell,
            adults: adults,
            child: child,
            infant: infant
        };

        return { transfer_options: transfer_options, transferInfo: transferInfo };
    };

    window.initAttractionTransferPanelsInTable = function () {
        document.querySelectorAll('#attractionsTableBody tr.attraction-row').forEach(function (row) {
            const cb = row.querySelector('.attraction-transfer-checkbox');
            window.ensureAttractionTransferPanelAfterRow(row);
            // Tour Sites load with Transfer already checked — show vehicle section immediately
            // (do not wait for user to uncheck/recheck Transfer)
            if (cb?.checked) {
                window.toggleAttractionTransferPanel(cb, true);
            } else {
                const panel = row.nextElementSibling;
                if (panel && panel.classList.contains('attraction-transfer-panel-row')) {
                    panel.style.display = 'none';
                }
                const included = (panel || row).querySelector?.('.attraction-transfer-included');
                if (included) included.checked = false;
            }
        });
        // Restore any headers that old index-based hide logic may have collapsed
        const table = document.querySelector('#tourModal #attractionsTableBody')?.closest('table');
        if (table) {
            Array.from(table.querySelectorAll('thead th')).forEach(function (th) {
                const label = String(th.textContent || '').trim().toLowerCase();
                // Only hide legacy Vehicle / Way / Type headers if somehow still present
                if (label === 'way' || label === 'type' || label === 'vehicle') {
                    th.style.display = 'none';
                } else {
                    th.style.display = '';
                }
            });
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        ensureAttractionMvStyles();
        document.getElementById('attractionsTableBody')?.addEventListener('change', function (ev) {
            const t = ev.target;
            if (!t || !t.classList) return;
            // Ticket select: if Transfer already on, ensure vehicle panel is visible
            if (t.classList.contains('attraction-checkbox')) {
                const row = t.closest('tr.attraction-row');
                const xfer = row?.querySelector('.attraction-transfer-checkbox');
                if (t.checked && xfer?.checked && typeof window.toggleAttractionTransferPanel === 'function') {
                    window.toggleAttractionTransferPanel(xfer, true);
                }
            }
            if (t.classList.contains('attraction-adult-qty')
                || t.classList.contains('attraction-child-qty')
                || t.classList.contains('attraction-infant-qty')
                || t.classList.contains('attraction-transfer-destination')
                || t.classList.contains('attraction-transfer-way')
                || t.classList.contains('attraction-is-pickup')) {
                const row = t.closest('tr.attraction-row');
                const uid = row?.querySelector('.attraction-checkbox')?.getAttribute('data-unique-id')
                    || row?.getAttribute('data-unique-id');
                if (uid && row?.querySelector('.attraction-transfer-checkbox')?.checked) {
                    if (typeof reclampAllMultiVehicleRowsPax === 'function') {
                        reclampAllMultiVehicleRowsPax(attrMvSide(uid));
                    } else if (typeof redistributeArrDepVehiclePax === 'function') {
                        redistributeArrDepVehiclePax(attrMvSide(uid));
                    }
                    scheduleAttractionMvPriceRefresh(attrMvSide(uid));
                    if (typeof updateArrDepVehicleCoverage === 'function') {
                        updateArrDepVehicleCoverage(attrMvSide(uid));
                    }
                }
            }
        });
    });
})();
