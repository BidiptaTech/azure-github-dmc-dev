# -*- coding: utf-8 -*-
"""Add per-vehicle Shared/Private Type column to A/D + Local vehicle tables."""
from pathlib import Path

FILES = [
    Path(r"c:\xampp\htdocs\Azure_new_files\resources\views\enquiryform_pro\create.blade.php"),
    Path(r"c:\xampp\htdocs\Azure_new_files\resources\views\enquiryform_pro\edit.blade.php"),
]

HEADER_OLD = """                                                    <th style=\"width:32px;\">#</th>
                                                    <th>Vehicle</th>
                                                    <th style=\"width:70px;\">Seats</th>
                                                    <th style=\"width:110px;\">Quantity</th>"""

HEADER_NEW = """                                                    <th style=\"width:32px;\">#</th>
                                                    <th>Vehicle</th>
                                                    <th style=\"width:70px;\">Seats</th>
                                                    <th style=\"width:88px;\">Type</th>
                                                    <th style=\"width:110px;\">Quantity</th>"""

ARR_TYPE_OLD = """                            <div class=\"col-1\" id=\"arrivalTransferTypeField\">
                                <label class=\"form-label small\">Type</label>
                                <select class=\"form-select form-select-sm\" id=\"arrivalTransferType\" style=\"font-size: 10px;\" onchange=\"filterArrivalVehiclesByServiceType()\">
                                    <option value=\"P\">Private</option>
                                    <option value=\"S\" selected>Shared</option>
                                </select>
                            </div>"""

ARR_TYPE_NEW = """                            <div class=\"col-1\" id=\"arrivalTransferTypeField\" style=\"display:none;\">
                                <label class=\"form-label small\">Type</label>
                                <select class=\"form-select form-select-sm\" id=\"arrivalTransferType\" style=\"font-size: 10px;\" onchange=\"filterArrivalVehiclesByServiceType()\">
                                    <option value=\"P\">Private</option>
                                    <option value=\"S\" selected>Shared</option>
                                </select>
                            </div>"""

DEP_TYPE_OLD = """                            <div class=\"col-1\" id=\"departureTransferTypeField\">
                                <label class=\"form-label small\">Type</label>
                                <select class=\"form-select form-select-sm\" id=\"departureTransferType\" style=\"font-size: 10px;\" onchange=\"filterDepartureVehiclesByServiceType()\">
                                    <option value=\"P\">Private</option>
                                    <option value=\"S\" selected>Shared</option>
                                </select>
                            </div>"""

DEP_TYPE_NEW = """                            <div class=\"col-1\" id=\"departureTransferTypeField\" style=\"display:none;\">
                                <label class=\"form-label small\">Type</label>
                                <select class=\"form-select form-select-sm\" id=\"departureTransferType\" style=\"font-size: 10px;\" onchange=\"filterDepartureVehiclesByServiceType()\">
                                    <option value=\"P\">Private</option>
                                    <option value=\"S\" selected>Shared</option>
                                </select>
                            </div>"""

LOCAL_TYPE_OLD = """                        <div class=\"col-3\">
                            <label class=\"form-label small\" style=\"margin-bottom: 2px;\">Transfer Type</label>
                            <select class=\"form-select form-select-sm\" id=\"localType\" onchange=\"filterLocalTransferVehiclesByServiceType()\">
                                <option value=\"P\">Private</option>
                                <option value=\"S\" selected>Shared</option>
                            </select>
                        </div>"""

LOCAL_TYPE_NEW = """                        <div class=\"col-3\" style=\"display:none;\">
                            <label class=\"form-label small\" style=\"margin-bottom: 2px;\">Transfer Type</label>
                            <select class=\"form-select form-select-sm\" id=\"localType\" onchange=\"filterLocalTransferVehiclesByServiceType()\">
                                <option value=\"P\">Private</option>
                                <option value=\"S\" selected>Shared</option>
                            </select>
                        </div>"""


def must_replace(text, old, new, label, count=None):
    c = text.count(old)
    if not c:
        print(f"  WARN {label} not found")
        return text, 0
    text = text.replace(old, new, count if count is not None else c)
    applied = min(c, count) if count else c
    print(f"  OK {label} x{applied}")
    return text, applied


def patch_js_core(text):
    n = 0

    old_fmt = """    /** Format multi-vehicle selection for listing tables */
    function formatMultiVehicleDisplay(item) {
        if (!item) return '-';
        const list = (typeof normalizeArrDepVehiclesList === 'function')
            ? normalizeArrDepVehiclesList(item)
            : (Array.isArray(item.vehicles) ? item.vehicles : []);
        if (list.length) {
            return list.map(function (v) {
                let name = String(v.vehicleName || v.vehicle_name || v.vehicles_name || 'Vehicle')
                    .replace(/\\s*\\(\\d+\\s*seats?\\)\\s*$/i, '')
                    .trim();
                const qty = Math.max(1, parseInt(v.qty || v.quantity || 1, 10) || 1);
                return qty > 1 ? (name + ' ×' + qty) : name;
            }).join(', ');
        }
        const single = String(item.vehicleName || item.vehicle_name || item.vehicles_name || '').trim();
        return single || '-';
    }"""

    new_fmt = """    /** Normalize Shared/Private to P|S */
    function normalizeTransferTypePS(val) {
        const s = String(val == null ? 'S' : val).trim();
        const lower = s.toLowerCase();
        if (s === 'P' || lower === 'private') return 'P';
        return 'S';
    }

    function transferTypeLabelPS(val) {
        return normalizeTransferTypePS(val) === 'P' ? 'Private' : 'Shared';
    }

    /** Format multi-vehicle selection for listing tables */
    function formatMultiVehicleDisplay(item) {
        if (!item) return '-';
        const list = (typeof normalizeArrDepVehiclesList === 'function')
            ? normalizeArrDepVehiclesList(item)
            : (Array.isArray(item.vehicles) ? item.vehicles : []);
        if (list.length) {
            return list.map(function (v) {
                let name = String(v.vehicleName || v.vehicle_name || v.vehicles_name || 'Vehicle')
                    .replace(/\\s*\\(\\d+\\s*seats?\\)\\s*$/i, '')
                    .trim();
                const qty = Math.max(1, parseInt(v.qty || v.quantity || 1, 10) || 1);
                return qty > 1 ? (name + ' ×' + qty) : name;
            }).join(', ');
        }
        const single = String(item.vehicleName || item.vehicle_name || item.vehicles_name || '').trim();
        return single || '-';
    }

    /** Per-vehicle Shared/Private for listing TYPE column */
    function formatMultiVehicleTypeDisplay(item) {
        if (!item) return '-';
        const list = (typeof normalizeArrDepVehiclesList === 'function')
            ? normalizeArrDepVehiclesList(item)
            : (Array.isArray(item.vehicles) ? item.vehicles : []);
        if (list.length) {
            return list.map(function (v) {
                const label = transferTypeLabelPS(v.transferType || v.type || item.transferType || item.type);
                const qty = Math.max(1, parseInt(v.qty || v.quantity || 1, 10) || 1);
                return qty > 1 ? (label + ' ×' + qty) : label;
            }).join(', ');
        }
        const fallback = item.transferType || item.type || item.transfer_type;
        if (fallback == null || fallback === '' || fallback === '-') return '-';
        return transferTypeLabelPS(fallback);
    }"""

    text, a = must_replace(text, old_fmt, new_fmt, "format helpers", 1)
    n += a

    old_collect = """    function collectArrDepVehicleRows(side) {
        const container = getArrDepVehicleRowsContainer(side);
        if (!container) return [];
        return Array.from(container.querySelectorAll('.arr-dep-vehicle-row')).map(function (row) {
            const sel = row.querySelector('.arr-dep-vehicle-select');
            const qtyEl = row.querySelector('.arr-dep-vehicle-qty');
            const opt = sel?.selectedOptions?.[0] || null;
            const vehicleId = String(sel?.value || '').trim();
            const seats = parseInt(opt?.getAttribute('data-seating') || opt?.dataset?.seating || '0', 10) || 0;
            let qty = parseInt(qtyEl?.value || '1', 10);
            if (!Number.isFinite(qty) || qty < 1) qty = 1;
            return {
                row: row,
                select: sel,
                qtyInput: qtyEl,
                vehicleId: vehicleId,
                vehicleName: (opt?.text || '').replace(/\\s*\\(\\d+\\s*seats?\\)\\s*$/i, '').trim(),
                vehicleType: opt?.getAttribute('data-type') || '',
                seats: seats,
                qty: qty,
                option: opt
            };
        });
    }"""

    new_collect = """    function collectArrDepVehicleRows(side) {
        const container = getArrDepVehicleRowsContainer(side);
        if (!container) return [];
        return Array.from(container.querySelectorAll('.arr-dep-vehicle-row')).map(function (row) {
            const sel = row.querySelector('.arr-dep-vehicle-select');
            const qtyEl = row.querySelector('.arr-dep-vehicle-qty');
            const typeEl = row.querySelector('.arr-dep-vehicle-xfer-type');
            const opt = sel?.selectedOptions?.[0] || null;
            const vehicleId = String(sel?.value || '').trim();
            const seats = parseInt(opt?.getAttribute('data-seating') || opt?.dataset?.seating || '0', 10) || 0;
            let qty = parseInt(qtyEl?.value || '1', 10);
            if (!Number.isFinite(qty) || qty < 1) qty = 1;
            const transferType = (typeof normalizeTransferTypePS === 'function')
                ? normalizeTransferTypePS(typeEl?.value || getArrDepTransferType(side))
                : (typeEl?.value || 'S');
            return {
                row: row,
                select: sel,
                qtyInput: qtyEl,
                typeSelect: typeEl,
                vehicleId: vehicleId,
                vehicleName: (opt?.text || '').replace(/\\s*\\(\\d+\\s*seats?\\)\\s*$/i, '').trim(),
                vehicleType: opt?.getAttribute('data-type') || '',
                seats: seats,
                qty: qty,
                transferType: transferType,
                type: transferType,
                option: opt
            };
        });
    }"""

    text, a = must_replace(text, old_collect, new_collect, "collectArrDepVehicleRows", 1)
    n += a

    old_payload = """    function collectArrDepVehiclesPayload(side) {
        return collectArrDepVehicleRows(side)
            .filter(r => r.vehicleId)
            .map(r => ({
                vehicleId: r.vehicleId,
                vehicle_id: r.vehicleId,
                vehicleName: r.vehicleName,
                vehicleType: r.vehicleType,
                seats: r.seats,
                seating_capacity: r.seats,
                qty: r.qty,
                quantity: r.qty
            }));
    }"""

    new_payload = """    function collectArrDepVehiclesPayload(side) {
        return collectArrDepVehicleRows(side)
            .filter(r => r.vehicleId)
            .map(r => ({
                vehicleId: r.vehicleId,
                vehicle_id: r.vehicleId,
                vehicleName: r.vehicleName,
                vehicleType: r.vehicleType,
                seats: r.seats,
                seating_capacity: r.seats,
                qty: r.qty,
                quantity: r.qty,
                transferType: r.transferType || 'S',
                type: r.transferType || 'S'
            }));
    }"""

    text, a = must_replace(text, old_payload, new_payload, "collectArrDepVehiclesPayload", 1)
    n += a

    old_norm = """    function normalizeArrDepVehiclesList(entry) {
        if (!entry) return [];
        if (Array.isArray(entry.vehicles) && entry.vehicles.length) {
            return entry.vehicles.map(function (v) {
                const seats = parseInt(v.seats || v.seating_capacity || v.seatingCapacity || 0, 10) || 0;
                let qty = parseInt(v.qty || v.quantity || v.vehicle_qty || 1, 10);
                if (!Number.isFinite(qty) || qty < 1) qty = 1;
                return {
                    vehicleId: String(v.vehicleId || v.vehicle_id || ''),
                    vehicleName: v.vehicleName || v.vehicle_name || v.vehicles_name || '',
                    vehicleType: v.vehicleType || v.vehicle_type || '',
                    seats: seats,
                    qty: qty
                };
            }).filter(v => v.vehicleId);
        }
        const vid = String(entry.vehicleId || entry.vehicle_id || '');
        if (!vid) return [];
        let qty = parseInt(entry.vehicleQty || entry.vehicle_qty || entry.quantity || 1, 10);
        if (!Number.isFinite(qty) || qty < 1) qty = 1;
        return [{
            vehicleId: vid,
            vehicleName: entry.vehicleName || entry.vehicle_name || entry.vehicles_name || '',
            vehicleType: entry.vehicleType || entry.vehicle_type || '',
            seats: parseInt(entry.seats || entry.seating_capacity || 0, 10) || 0,
            qty: qty
        }];
    }"""

    new_norm = """    function normalizeArrDepVehiclesList(entry) {
        if (!entry) return [];
        const entryType = (typeof normalizeTransferTypePS === 'function')
            ? normalizeTransferTypePS(entry.transferType || entry.type || entry.transfer_type || 'S')
            : 'S';
        if (Array.isArray(entry.vehicles) && entry.vehicles.length) {
            return entry.vehicles.map(function (v) {
                const seats = parseInt(v.seats || v.seating_capacity || v.seatingCapacity || 0, 10) || 0;
                let qty = parseInt(v.qty || v.quantity || v.vehicle_qty || 1, 10);
                if (!Number.isFinite(qty) || qty < 1) qty = 1;
                const t = (typeof normalizeTransferTypePS === 'function')
                    ? normalizeTransferTypePS(v.transferType || v.type || entryType)
                    : (v.transferType || entryType || 'S');
                return {
                    vehicleId: String(v.vehicleId || v.vehicle_id || ''),
                    vehicleName: v.vehicleName || v.vehicle_name || v.vehicles_name || '',
                    vehicleType: v.vehicleType || v.vehicle_type || '',
                    seats: seats,
                    qty: qty,
                    transferType: t,
                    type: t,
                    lineCost: v.lineCost,
                    lineSell: v.lineSell,
                    unitCost: v.unitCost,
                    unitSell: v.unitSell
                };
            }).filter(v => v.vehicleId);
        }
        const vid = String(entry.vehicleId || entry.vehicle_id || '');
        if (!vid) return [];
        let qty = parseInt(entry.vehicleQty || entry.vehicle_qty || entry.quantity || 1, 10);
        if (!Number.isFinite(qty) || qty < 1) qty = 1;
        return [{
            vehicleId: vid,
            vehicleName: entry.vehicleName || entry.vehicle_name || entry.vehicles_name || '',
            vehicleType: entry.vehicleType || entry.vehicle_type || '',
            seats: parseInt(entry.seats || entry.seating_capacity || 0, 10) || 0,
            qty: qty,
            transferType: entryType,
            type: entryType
        }];
    }"""

    text, a = must_replace(text, old_norm, new_norm, "normalizeArrDepVehiclesList", 1)
    n += a

    # sync master type from rows helper — insert after getArrDepTransferType
    old_get_type = """    function getArrDepTransferType(side) {
        if (side === 'local') return document.getElementById('localType')?.value || 'S';
        return document.getElementById(side === 'arrival' ? 'arrivalTransferType' : 'departureTransferType')?.value || 'S';
    }"""

    new_get_type = """    function getArrDepTransferType(side) {
        if (side === 'local') return document.getElementById('localType')?.value || 'S';
        return document.getElementById(side === 'arrival' ? 'arrivalTransferType' : 'departureTransferType')?.value || 'S';
    }

    function syncMasterTransferTypeFromRows(side) {
        const rows = (typeof collectArrDepVehicleRows === 'function')
            ? collectArrDepVehicleRows(side).filter(function (r) { return r.vehicleId; })
            : [];
        let master = 'S';
        if (rows.length) {
            const types = rows.map(function (r) {
                return (typeof normalizeTransferTypePS === 'function')
                    ? normalizeTransferTypePS(r.transferType)
                    : (r.transferType || 'S');
            });
            const allSame = types.every(function (t) { return t === types[0]; });
            master = allSame ? types[0] : types[0];
        }
        const id = side === 'local' ? 'localType'
            : (side === 'arrival' ? 'arrivalTransferType' : 'departureTransferType');
        const el = document.getElementById(id);
        if (el) el.value = master;
        return master;
    }"""

    text, a = must_replace(text, old_get_type, new_get_type, "syncMasterTransferTypeFromRows", 1)
    n += a

    old_recalc = """    function recalcArrDepVehicleRowTotals(side, row) {
        const qty = Math.max(1, parseInt(row.querySelector('.arr-dep-vehicle-qty')?.value || '1', 10) || 1);
        const unitCost = parseFloat(row.querySelector('.arr-dep-unit-cost')?.value || '0') || 0;
        const unitSell = parseFloat(row.querySelector('.arr-dep-unit-sell')?.value || '0') || 0;
        const transferType = getArrDepTransferType(side);
        const isShared = transferType === 'S' || String(transferType).toLowerCase() === 'shared' || transferType === 'sic';
        const pax = side === 'local'
            ? (typeof getLocalTourPax === 'function' ? getLocalTourPax() : 1)
            : getArrDepTourPax(side);
        const mult = isShared ? Math.max(0, pax) : 1;
        const lineCost = unitCost * qty * mult;
        const lineSell = unitSell * qty * mult;
        const costEl = row.querySelector('.arr-dep-line-cost');
        const sellEl = row.querySelector('.arr-dep-line-sell');
        if (costEl) costEl.textContent = lineCost.toFixed(2);
        if (sellEl) sellEl.textContent = lineSell.toFixed(2);
        return { lineCost, lineSell, unitCost, unitSell, qty };
    }"""

    new_recalc = """    function recalcArrDepVehicleRowTotals(side, row) {
        const qty = Math.max(1, parseInt(row.querySelector('.arr-dep-vehicle-qty')?.value || '1', 10) || 1);
        const unitCost = parseFloat(row.querySelector('.arr-dep-unit-cost')?.value || '0') || 0;
        const unitSell = parseFloat(row.querySelector('.arr-dep-unit-sell')?.value || '0') || 0;
        const transferType = (typeof normalizeTransferTypePS === 'function')
            ? normalizeTransferTypePS(row.querySelector('.arr-dep-vehicle-xfer-type')?.value || getArrDepTransferType(side))
            : (row.querySelector('.arr-dep-vehicle-xfer-type')?.value || getArrDepTransferType(side));
        const isShared = transferType === 'S';
        const pax = side === 'local'
            ? (typeof getLocalTourPax === 'function' ? getLocalTourPax() : 1)
            : getArrDepTourPax(side);
        const mult = isShared ? Math.max(0, pax) : 1;
        const lineCost = unitCost * qty * mult;
        const lineSell = unitSell * qty * mult;
        const costEl = row.querySelector('.arr-dep-line-cost');
        const sellEl = row.querySelector('.arr-dep-line-sell');
        if (costEl) costEl.textContent = lineCost.toFixed(2);
        if (sellEl) sellEl.textContent = lineSell.toFixed(2);
        return { lineCost, lineSell, unitCost, unitSell, qty, transferType, isShared };
    }"""

    text, a = must_replace(text, old_recalc, new_recalc, "recalcArrDepVehicleRowTotals", 1)
    n += a

    old_build = """        function buildArrDepVehicleRowHtml(side, selectedId, qty) {
        const master = getArrDepVehicleMasterSelect(side);
        const optsHtml = master ? master.innerHTML : '<option value=\"\">Select Vehicle</option>';
        const q = Math.max(1, parseInt(qty || 1, 10) || 1);
        return ''
            + '<tr class=\"arr-dep-vehicle-row\">'
            +   '<td class=\"mv-row-index text-muted\">1</td>'
            +   '<td><select class=\"form-select form-select-sm arr-dep-vehicle-select\" style=\"font-size:11px;min-width:140px;\">' + optsHtml + '</select></td>'
            +   '<td><span class=\"mv-seats-badge\"><i class=\"ri-user-line\"></i><span class=\"arr-dep-vehicle-seats\">0</span></span></td>'
            +   '<td><div class=\"mv-qty-stepper\">'
            +     '<button type=\"button\" class=\"arr-dep-qty-minus\" aria-label=\"Decrease\">−</button>'
            +     '<input type=\"number\" class=\"arr-dep-vehicle-qty\" min=\"1\" step=\"1\" value=\"' + q + '\">'
            +     '<button type=\"button\" class=\"arr-dep-qty-plus\" aria-label=\"Increase\">+</button>'
            +   '</div></td>'
            +   '<td><input type=\"number\" step=\"0.01\" min=\"0\" class=\"form-control form-control-sm mv-unit-input arr-dep-unit-cost\" value=\"0\"></td>'
            +   '<td><input type=\"number\" step=\"0.01\" min=\"0\" class=\"form-control form-control-sm mv-unit-input arr-dep-unit-sell\" value=\"0\"></td>'
            +   '<td><span class=\"mv-total-cost arr-dep-line-cost\">0.00</span></td>'
            +   '<td><span class=\"mv-total-sell arr-dep-line-sell\">0.00</span></td>'
            +   '<td><button type=\"button\" class=\"btn btn-sm btn-outline-danger arr-dep-vehicle-remove\" title=\"Remove\"><i class=\"ri-delete-bin-line\"></i></button></td>'
            + '</tr>';
    }"""

    new_build = """        function buildArrDepVehicleRowHtml(side, selectedId, qty, transferType) {
        const master = getArrDepVehicleMasterSelect(side);
        const optsHtml = master ? master.innerHTML : '<option value=\"\">Select Vehicle</option>';
        const q = Math.max(1, parseInt(qty || 1, 10) || 1);
        const tt = (typeof normalizeTransferTypePS === 'function')
            ? normalizeTransferTypePS(transferType || getArrDepTransferType(side))
            : (transferType || 'S');
        const sSel = tt === 'S' ? ' selected' : '';
        const pSel = tt === 'P' ? ' selected' : '';
        return ''
            + '<tr class=\"arr-dep-vehicle-row\">'
            +   '<td class=\"mv-row-index text-muted\">1</td>'
            +   '<td><select class=\"form-select form-select-sm arr-dep-vehicle-select\" style=\"font-size:11px;min-width:140px;\">' + optsHtml + '</select></td>'
            +   '<td><span class=\"mv-seats-badge\"><i class=\"ri-user-line\"></i><span class=\"arr-dep-vehicle-seats\">0</span></span></td>'
            +   '<td><select class=\"form-select form-select-sm arr-dep-vehicle-xfer-type\" style=\"font-size:10px;min-width:78px;\">'
            +     '<option value=\"S\"' + sSel + '>Shared</option>'
            +     '<option value=\"P\"' + pSel + '>Private</option>'
            +   '</select></td>'
            +   '<td><div class=\"mv-qty-stepper\">'
            +     '<button type=\"button\" class=\"arr-dep-qty-minus\" aria-label=\"Decrease\">−</button>'
            +     '<input type=\"number\" class=\"arr-dep-vehicle-qty\" min=\"1\" step=\"1\" value=\"' + q + '\">'
            +     '<button type=\"button\" class=\"arr-dep-qty-plus\" aria-label=\"Increase\">+</button>'
            +   '</div></td>'
            +   '<td><input type=\"number\" step=\"0.01\" min=\"0\" class=\"form-control form-control-sm mv-unit-input arr-dep-unit-cost\" value=\"0\"></td>'
            +   '<td><input type=\"number\" step=\"0.01\" min=\"0\" class=\"form-control form-control-sm mv-unit-input arr-dep-unit-sell\" value=\"0\"></td>'
            +   '<td><span class=\"mv-total-cost arr-dep-line-cost\">0.00</span></td>'
            +   '<td><span class=\"mv-total-sell arr-dep-line-sell\">0.00</span></td>'
            +   '<td><button type=\"button\" class=\"btn btn-sm btn-outline-danger arr-dep-vehicle-remove\" title=\"Remove\"><i class=\"ri-delete-bin-line\"></i></button></td>'
            + '</tr>';
    }"""

    text, a = must_replace(text, old_build, new_build, "buildArrDepVehicleRowHtml", 1)
    n += a

    # bind type change in bindArrDepVehicleRowEvents
    old_bind_start = """        function bindArrDepVehicleRowEvents(side, row) {
        const sel = row.querySelector('.arr-dep-vehicle-select');
        const qty = row.querySelector('.arr-dep-vehicle-qty');
        const rem = row.querySelector('.arr-dep-vehicle-remove');
        const minus = row.querySelector('.arr-dep-qty-minus');
        const plus = row.querySelector('.arr-dep-qty-plus');
        const unitCost = row.querySelector('.arr-dep-unit-cost');
        const unitSell = row.querySelector('.arr-dep-unit-sell');
        const onChange = function () {
            syncArrDepPrimaryVehicleSelect(side);
            updateArrDepVehicleCoverage(side);"""

    new_bind_start = """        function bindArrDepVehicleRowEvents(side, row) {
        const sel = row.querySelector('.arr-dep-vehicle-select');
        const qty = row.querySelector('.arr-dep-vehicle-qty');
        const typeSel = row.querySelector('.arr-dep-vehicle-xfer-type');
        const rem = row.querySelector('.arr-dep-vehicle-remove');
        const minus = row.querySelector('.arr-dep-qty-minus');
        const plus = row.querySelector('.arr-dep-qty-plus');
        const unitCost = row.querySelector('.arr-dep-unit-cost');
        const unitSell = row.querySelector('.arr-dep-unit-sell');
        const onChange = function () {
            if (typeof syncMasterTransferTypeFromRows === 'function') syncMasterTransferTypeFromRows(side);
            syncArrDepPrimaryVehicleSelect(side);
            updateArrDepVehicleCoverage(side);"""

    text, a = must_replace(text, old_bind_start, new_bind_start, "bindArrDepVehicleRowEvents start", 1)
    n += a

    old_bind_listeners = """        sel?.addEventListener('change', onChange);
        qty?.addEventListener('change', onChange);
        qty?.addEventListener('input', onChange);"""

    new_bind_listeners = """        const onTypeChange = function () {
            if (typeof applyArrDepVehicleFilterToSelect === 'function') {
                applyArrDepVehicleFilterToSelect(side, sel);
            }
            // Clear vehicle if no longer valid for this type
            if (sel && sel.value) {
                const opt = sel.selectedOptions?.[0];
                if (opt && (opt.disabled || opt.hidden || opt.style.display === 'none')) {
                    sel.value = '';
                }
            }
            const uc = row.querySelector('.arr-dep-unit-cost');
            const us = row.querySelector('.arr-dep-unit-sell');
            if (uc) delete uc.dataset.userEdited;
            if (us) delete us.dataset.userEdited;
            onChange();
        };
        sel?.addEventListener('change', onChange);
        typeSel?.addEventListener('change', onTypeChange);
        qty?.addEventListener('change', onChange);
        qty?.addEventListener('input', onChange);"""

    text, a = must_replace(text, old_bind_listeners, new_bind_listeners, "bind type listener", 1)
    n += a

    # applyArrDepRowTotalsToHidden - per-row shared
    old_apply = """    function applyArrDepRowTotalsToHidden(side) {
        const isArr = side === 'arrival';
        const rows = collectArrDepVehicleRows(side);
        const transferType = getArrDepTransferType(side);
        const isShared = transferType === 'S' || String(transferType).toLowerCase() === 'shared';
        const pax = getArrDepTourPax(side);
        let totalCost = 0, totalSell = 0, storedCost = 0, storedSell = 0;
        rows.forEach(function (r) {
            if (!r.vehicleId || !r.row) return;
            const t = recalcArrDepVehicleRowTotals(side, r.row);
            totalCost += t.lineCost;
            totalSell += t.lineSell;
            if (isShared) {
                storedCost += t.unitCost * t.qty;
                storedSell += t.unitSell * t.qty;
            } else {
                storedCost += t.lineCost;
                storedSell += t.lineSell;
            }
        });
        const costEl = document.getElementById(isArr ? 'arrivalCost' : 'departureCost');
        const sellEl = document.getElementById(isArr ? 'arrivalSell' : 'departureSell');
        if (costEl) costEl.value = storedCost || 0;
        if (sellEl) sellEl.value = storedSell || 0;
        const disp = document.getElementById(isArr ? 'arrivalZonePriceDisplay' : 'departureZonePriceDisplay');
        const meta = document.getElementById(isArr ? 'arrivalZonePriceMeta' : 'departureZonePriceMeta');
        const row = document.getElementById(isArr ? 'arrivalZonePriceRow' : 'departureZonePriceRow');
        if (row) row.style.display = '';
        if (disp) {
            const c = formatArrDepZonePrice(totalCost);
            const s = formatArrDepZonePrice(totalSell);
            disp.textContent = (c !== '—' || s !== '—') ? (c + ' / ' + s) : '—';
        }
        if (meta) {
            const n = rows.filter(r => r.vehicleId).length;
            meta.textContent = n
                ? ('(' + n + ' vehicle' + (n > 1 ? 's' : '') + (isShared ? (', ×' + pax + ' pax') : '') + ')')
                : '';
        }
    }"""

    new_apply = """    function applyArrDepRowTotalsToHidden(side) {
        const isArr = side === 'arrival';
        const rows = collectArrDepVehicleRows(side);
        if (typeof syncMasterTransferTypeFromRows === 'function') syncMasterTransferTypeFromRows(side);
        const pax = getArrDepTourPax(side);
        let totalCost = 0, totalSell = 0, storedCost = 0, storedSell = 0;
        let anyShared = false, anyPrivate = false;
        rows.forEach(function (r) {
            if (!r.vehicleId || !r.row) return;
            const t = recalcArrDepVehicleRowTotals(side, r.row);
            totalCost += t.lineCost;
            totalSell += t.lineSell;
            if (t.isShared) {
                anyShared = true;
                storedCost += t.unitCost * t.qty;
                storedSell += t.unitSell * t.qty;
            } else {
                anyPrivate = true;
                storedCost += t.lineCost;
                storedSell += t.lineSell;
            }
        });
        const costEl = document.getElementById(isArr ? 'arrivalCost' : 'departureCost');
        const sellEl = document.getElementById(isArr ? 'arrivalSell' : 'departureSell');
        if (costEl) costEl.value = storedCost || 0;
        if (sellEl) sellEl.value = storedSell || 0;
        const disp = document.getElementById(isArr ? 'arrivalZonePriceDisplay' : 'departureZonePriceDisplay');
        const meta = document.getElementById(isArr ? 'arrivalZonePriceMeta' : 'departureZonePriceMeta');
        const row = document.getElementById(isArr ? 'arrivalZonePriceRow' : 'departureZonePriceRow');
        if (row) row.style.display = '';
        if (disp) {
            const c = formatArrDepZonePrice(totalCost);
            const s = formatArrDepZonePrice(totalSell);
            disp.textContent = (c !== '—' || s !== '—') ? (c + ' / ' + s) : '—';
        }
        if (meta) {
            const n = rows.filter(r => r.vehicleId).length;
            let suffix = '';
            if (anyShared && anyPrivate) suffix = ', mixed';
            else if (anyShared) suffix = ', ×' + pax + ' pax';
            meta.textContent = n
                ? ('(' + n + ' vehicle' + (n > 1 ? 's' : '') + suffix + ')')
                : '';
        }
    }"""

    text, a = must_replace(text, old_apply, new_apply, "applyArrDepRowTotalsToHidden", 1)
    n += a

    # addArrDepVehicleRow signature
    old_add = """    function addArrDepVehicleRow(side, selectedId, qty) {
        const container = getArrDepVehicleRowsContainer(side);
        if (!container) return null;
        // Parse <tr> via a temporary table so the browser does not discard it
        const wrap = document.createElement('table');
        const tbody = document.createElement('tbody');
        wrap.appendChild(tbody);
        tbody.innerHTML = buildArrDepVehicleRowHtml(side, selectedId, qty);
        const row = tbody.firstElementChild;
        if (!row) return null;
        container.appendChild(row);
        const sel = row.querySelector('.arr-dep-vehicle-select');
        if (sel && selectedId) {
            sel.value = String(selectedId);
            if (!sel.value) {
                const match = Array.from(sel.options).find(o => String(o.value) === String(selectedId));
                if (match) sel.value = match.value;
            }
        }
        // Apply current type/city filter visibility onto the new select
        if (typeof applyArrDepVehicleFilterToSelect === 'function') {
            applyArrDepVehicleFilterToSelect(side, sel);
        }
        bindArrDepVehicleRowEvents(side, row);
        syncArrDepPrimaryVehicleSelect(side);
        updateArrDepVehicleCoverage(side);
        return row;
    }"""

    new_add = """    function addArrDepVehicleRow(side, selectedId, qty, transferType) {
        const container = getArrDepVehicleRowsContainer(side);
        if (!container) return null;
        // Parse <tr> via a temporary table so the browser does not discard it
        const wrap = document.createElement('table');
        const tbody = document.createElement('tbody');
        wrap.appendChild(tbody);
        tbody.innerHTML = buildArrDepVehicleRowHtml(side, selectedId, qty, transferType);
        const row = tbody.firstElementChild;
        if (!row) return null;
        container.appendChild(row);
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
        // Apply current type/city filter visibility onto the new select
        if (typeof applyArrDepVehicleFilterToSelect === 'function') {
            applyArrDepVehicleFilterToSelect(side, sel);
        }
        bindArrDepVehicleRowEvents(side, row);
        if (typeof syncMasterTransferTypeFromRows === 'function') syncMasterTransferTypeFromRows(side);
        syncArrDepPrimaryVehicleSelect(side);
        updateArrDepVehicleCoverage(side);
        return row;
    }"""

    text, a = must_replace(text, old_add, new_add, "addArrDepVehicleRow", 1)
    n += a

    old_ensure = """    function ensureArrDepVehicleRows(side, vehicles) {
        const container = getArrDepVehicleRowsContainer(side);
        if (!container) return;
        container.innerHTML = '';
        const list = (Array.isArray(vehicles) && vehicles.length) ? vehicles : [{ vehicleId: '', qty: 1 }];
        list.forEach(function (v) {
            addArrDepVehicleRow(side, v.vehicleId || v.vehicle_id || '', v.qty || v.quantity || 1);
        });
        syncArrDepPrimaryVehicleSelect(side);
        updateArrDepVehicleCoverage(side);
    }"""

    new_ensure = """    function ensureArrDepVehicleRows(side, vehicles) {
        const container = getArrDepVehicleRowsContainer(side);
        if (!container) return;
        container.innerHTML = '';
        const list = (Array.isArray(vehicles) && vehicles.length) ? vehicles : [{ vehicleId: '', qty: 1, transferType: 'S' }];
        list.forEach(function (v) {
            addArrDepVehicleRow(
                side,
                v.vehicleId || v.vehicle_id || '',
                v.qty || v.quantity || 1,
                v.transferType || v.type || getArrDepTransferType(side)
            );
        });
        if (typeof syncMasterTransferTypeFromRows === 'function') syncMasterTransferTypeFromRows(side);
        syncArrDepPrimaryVehicleSelect(side);
        updateArrDepVehicleCoverage(side);
    }"""

    text, a = must_replace(text, old_ensure, new_ensure, "ensureArrDepVehicleRows", 1)
    n += a

    # applyArrDepVehicleFilterToSelect - use row type
    old_filter = """    function applyArrDepVehicleFilterToSelect(side, vehicleSelect, preferredCity) {
        if (!vehicleSelect || !vehicleSelect.options) return;
        const isArr = side === 'arrival';
        const serviceType = document.getElementById(isArr ? 'arrivalTransferType' : 'departureTransferType')?.value || 'S';"""

    new_filter = """    function applyArrDepVehicleFilterToSelect(side, vehicleSelect, preferredCity) {
        if (!vehicleSelect || !vehicleSelect.options) return;
        const row = vehicleSelect.closest ? vehicleSelect.closest('.arr-dep-vehicle-row') : null;
        const rowType = row && row.querySelector('.arr-dep-vehicle-xfer-type')
            ? row.querySelector('.arr-dep-vehicle-xfer-type').value
            : null;
        const serviceType = (typeof normalizeTransferTypePS === 'function')
            ? normalizeTransferTypePS(rowType || getArrDepTransferType(side))
            : (rowType || getArrDepTransferType(side) || 'S');"""

    text, a = must_replace(text, old_filter, new_filter, "applyArrDepVehicleFilterToSelect", 1)
    n += a

    return text, n


def patch_compute(text):
    """Rewrite computeArrDepMultiVehicleTotals loop for per-vehicle type."""
    old = """        const vehicles = (opts.vehicles && opts.vehicles.length)
            ? opts.vehicles
            : collectArrDepVehiclesPayload(side);

        let totalCost = 0;
        let totalSell = 0;
        let unitCostSum = 0; // for shared: sum(unit*qty) used as per-pax adultCost
        let unitSellSum = 0;
        let metaParts = [];
        const enriched = [];
        const isShared = transferType === 'S' || transferType === 'sic' || String(transferType).toLowerCase() === 'shared';

        for (let i = 0; i < vehicles.length; i++) {
            const v = vehicles[i];
            const vehicleId = String(v.vehicleId || v.vehicle_id || '');
            if (!vehicleId) continue;
            let qty = parseInt(v.qty || v.quantity || 1, 10);
            if (!Number.isFinite(qty) || qty < 1) qty = 1;
            let zonePrice = { private_price: 0, shared_price: 0, private_cost_price: 0, shared_cost_price: 0 };
            if (portId && dest.id) {
                try {
                    if (isArr) {
                        zonePrice = await fetchZonePrice(vehicleId, portId, 'port', dest.id, dest.type || 'hotel', dmcId);
                    } else {
                        zonePrice = await fetchZonePrice(vehicleId, dest.id, dest.type || 'hotel', portId, 'port', dmcId);
                    }
                } catch (e) {
                    console.warn('Multi-vehicle zone price failed', side, vehicleId, e);
                }
            }
            const unit = (typeof calculateTransferPrice === 'function')
                ? calculateTransferPrice(zonePrice, transferType, 'one-way', adults, child)
                : { cost: 0, sell: 0 };
            const unitCost = parseFloat(unit.cost) || 0;
            const unitSell = parseFloat(unit.sell) || 0;
            let lineCost, lineSell;
            if (isShared) {
                // Shared: unit × qty × tour pax
                lineCost = unitCost * qty * tourPax;
                lineSell = unitSell * qty * tourPax;
                unitCostSum += unitCost * qty;
                unitSellSum += unitSell * qty;
            } else {
                // Private: unit × qty
                lineCost = unitCost * qty;
                lineSell = unitSell * qty;
                unitCostSum += lineCost;
                unitSellSum += lineSell;
            }
            totalCost += lineCost;
            totalSell += lineSell;
            enriched.push({
                vehicleId: vehicleId,
                vehicle_id: vehicleId,
                vehicleName: v.vehicleName || v.vehicle_name || '',
                vehicleType: v.vehicleType || v.vehicle_type || '',
                seats: parseInt(v.seats || v.seating_capacity || 0, 10) || 0,
                qty: qty,
                quantity: qty,
                unitCost: unitCost,
                unitSell: unitSell,
                lineCost: lineCost,
                lineSell: lineSell,
                zonePrivatePrice: unit.privatePrice || zonePrice.private_price || 0,
                zoneSharedPrice: unit.sharedPrice || zonePrice.shared_price || 0,
                zonePrivateCostPrice: unit.privateCostPrice || zonePrice.private_cost_price || 0,
                zoneSharedCostPrice: unit.sharedCostPrice || zonePrice.shared_cost_price || 0
            });
            if (unit.meta) metaParts.push(unit.meta);
        }

        // For storage: shared adultCost/Sell = sum(unit*qty) so existing submit × pax still works;
        // private adultCost/Sell = total (already qty-multiplied).
        const storedCost = isShared ? unitCostSum : totalCost;
        const storedSell = isShared ? unitSellSum : totalSell;

        return {
            vehicles: enriched,
            totalCost: totalCost,
            totalSell: totalSell,
            storedCost: storedCost,
            storedSell: storedSell,
            tourPax: tourPax,
            isShared: isShared,
            meta: enriched.length
                ? (
                    (totalCost <= 0 && totalSell <= 0)
                        ? '(No zone mapping for this route)'
                        : ('(' + enriched.length + ' vehicle' + (enriched.length > 1 ? 's' : '')
                            + (isShared ? (', ×' + tourPax + ' pax') : '') + ')')
                )
                : ''
        };
    }"""

    new = """        const vehicles = (opts.vehicles && opts.vehicles.length)
            ? opts.vehicles
            : collectArrDepVehiclesPayload(side);

        let totalCost = 0;
        let totalSell = 0;
        let storedCost = 0;
        let storedSell = 0;
        let metaParts = [];
        const enriched = [];
        let anyShared = false, anyPrivate = false;

        for (let i = 0; i < vehicles.length; i++) {
            const v = vehicles[i];
            const vehicleId = String(v.vehicleId || v.vehicle_id || '');
            if (!vehicleId) continue;
            let qty = parseInt(v.qty || v.quantity || 1, 10);
            if (!Number.isFinite(qty) || qty < 1) qty = 1;
            const rowType = (typeof normalizeTransferTypePS === 'function')
                ? normalizeTransferTypePS(v.transferType || v.type || transferType)
                : (v.transferType || v.type || transferType || 'S');
            const rowShared = rowType === 'S';
            if (rowShared) anyShared = true; else anyPrivate = true;
            let zonePrice = { private_price: 0, shared_price: 0, private_cost_price: 0, shared_cost_price: 0 };
            if (portId && dest.id) {
                try {
                    if (isArr) {
                        zonePrice = await fetchZonePrice(vehicleId, portId, 'port', dest.id, dest.type || 'hotel', dmcId);
                    } else {
                        zonePrice = await fetchZonePrice(vehicleId, dest.id, dest.type || 'hotel', portId, 'port', dmcId);
                    }
                } catch (e) {
                    console.warn('Multi-vehicle zone price failed', side, vehicleId, e);
                }
            }
            const unit = (typeof calculateTransferPrice === 'function')
                ? calculateTransferPrice(zonePrice, rowType, 'one-way', adults, child)
                : { cost: 0, sell: 0 };
            const unitCost = parseFloat(unit.cost) || 0;
            const unitSell = parseFloat(unit.sell) || 0;
            let lineCost, lineSell;
            if (rowShared) {
                lineCost = unitCost * qty * tourPax;
                lineSell = unitSell * qty * tourPax;
                storedCost += unitCost * qty;
                storedSell += unitSell * qty;
            } else {
                lineCost = unitCost * qty;
                lineSell = unitSell * qty;
                storedCost += lineCost;
                storedSell += lineSell;
            }
            totalCost += lineCost;
            totalSell += lineSell;
            enriched.push({
                vehicleId: vehicleId,
                vehicle_id: vehicleId,
                vehicleName: v.vehicleName || v.vehicle_name || '',
                vehicleType: v.vehicleType || v.vehicle_type || '',
                seats: parseInt(v.seats || v.seating_capacity || 0, 10) || 0,
                qty: qty,
                quantity: qty,
                transferType: rowType,
                type: rowType,
                unitCost: unitCost,
                unitSell: unitSell,
                lineCost: lineCost,
                lineSell: lineSell,
                zonePrivatePrice: unit.privatePrice || zonePrice.private_price || 0,
                zoneSharedPrice: unit.sharedPrice || zonePrice.shared_price || 0,
                zonePrivateCostPrice: unit.privateCostPrice || zonePrice.private_cost_price || 0,
                zoneSharedCostPrice: unit.sharedCostPrice || zonePrice.shared_cost_price || 0
            });
            if (unit.meta) metaParts.push(unit.meta);
        }

        const isShared = anyShared && !anyPrivate;
        const primaryType = enriched.length
            ? (enriched[0].transferType || transferType)
            : transferType;

        return {
            vehicles: enriched,
            totalCost: totalCost,
            totalSell: totalSell,
            storedCost: storedCost,
            storedSell: storedSell,
            tourPax: tourPax,
            isShared: isShared,
            transferType: primaryType,
            meta: enriched.length
                ? (
                    (totalCost <= 0 && totalSell <= 0)
                        ? '(No zone mapping for this route)'
                        : ('(' + enriched.length + ' vehicle' + (enriched.length > 1 ? 's' : '')
                            + (anyShared && anyPrivate ? ', mixed'
                                : (isShared ? (', ×' + tourPax + ' pax') : '')) + ')')
                )
                : ''
        };
    }"""

    return must_replace(text, old, new, "computeArrDepMultiVehicleTotals", 1)


def patch_local_pricing(text):
    """Local multi-vehicle pricing uses per-row type."""
    old = """        const type = document.getElementById('localType')?.value || 'S';
        const way = document.getElementById('localWay')?.value || 'one-way';
        const adults = parseInt(document.getElementById('localAdults')?.value || '0', 10) || 0;
        const child = parseInt(document.getElementById('localChild')?.value || '0', 10) || 0;
        const dmcId = '{{ $dmc_id ?? "" }}';
        let pickupType = $('#localPickup').find(':selected').attr('data-type') || '';
        let dropType = $('#localDrop').find(':selected').attr('data-type') || '';
        let pickupIdForFetch = pickupSelect.value;
        let dropIdForFetch = dropSelect.value;
        if (typeof resolveLocalTransferZoneLookup === 'function') {
            const resolvedLoc = resolveLocalTransferZoneLookup(pickupSelect.value, pickupType, dropSelect.value, dropType);
            pickupIdForFetch = resolvedLoc.actualPickupId || pickupIdForFetch;
            dropIdForFetch = resolvedLoc.actualDropId || dropIdForFetch;
            pickupType = resolvedLoc.actualPickupType || pickupType;
            dropType = resolvedLoc.actualDropType || dropType;
        }
        const tourPax = getLocalTourPax();
        const isShared = type === 'S' || String(type).toLowerCase() === 'shared';
        const enriched = [];
        let totalCost = 0, totalSell = 0, storedCost = 0, storedSell = 0;
        for (let i = 0; i < vehicles.length; i++) {
            const v = vehicles[i];
            let zonePrice = { private_price: 0, shared_price: 0, private_cost_price: 0, shared_cost_price: 0 };
            try {
                zonePrice = await fetchZonePrice(v.vehicleId, pickupIdForFetch, pickupType, dropIdForFetch, dropType, dmcId);
            } catch (e) {
                console.warn('Local multi-vehicle zone price failed', v.vehicleId, e);
            }
            const unit = calculateTransferPrice(zonePrice, type, way, adults, child);
            const unitCost = parseFloat(unit.cost) || 0;
            const unitSell = parseFloat(unit.sell) || 0;
            const qty = v.qty || 1;
            const lineCost = isShared ? unitCost * qty * tourPax : unitCost * qty;
            const lineSell = isShared ? unitSell * qty * tourPax : unitSell * qty;
            totalCost += lineCost;
            totalSell += lineSell;
            if (isShared) {
                storedCost += unitCost * qty;
                storedSell += unitSell * qty;"""

    # Need more of the loop - read carefully. Safer to only replace the pricing lines inside loop.
    # Let me use a smaller unique chunk.
    old_loop_unit = """            const unit = calculateTransferPrice(zonePrice, type, way, adults, child);
            const unitCost = parseFloat(unit.cost) || 0;
            const unitSell = parseFloat(unit.sell) || 0;
            const qty = v.qty || 1;
            const lineCost = isShared ? unitCost * qty * tourPax : unitCost * qty;
            const lineSell = isShared ? unitSell * qty * tourPax : unitSell * qty;
            totalCost += lineCost;
            totalSell += lineSell;
            if (isShared) {
                storedCost += unitCost * qty;
                storedSell += unitSell * qty;"""

    new_loop_unit = """            const rowType = (typeof normalizeTransferTypePS === 'function')
                ? normalizeTransferTypePS(v.transferType || v.type || type)
                : (v.transferType || v.type || type || 'S');
            const rowShared = rowType === 'S';
            const unit = calculateTransferPrice(zonePrice, rowType, way, adults, child);
            const unitCost = parseFloat(unit.cost) || 0;
            const unitSell = parseFloat(unit.sell) || 0;
            const qty = v.qty || 1;
            const lineCost = rowShared ? unitCost * qty * tourPax : unitCost * qty;
            const lineSell = rowShared ? unitSell * qty * tourPax : unitSell * qty;
            totalCost += lineCost;
            totalSell += lineSell;
            if (rowShared) {
                storedCost += unitCost * qty;
                storedSell += unitSell * qty;"""

    text, a = must_replace(text, old_loop_unit, new_loop_unit, "local per-row pricing", 1)

    # Also push transferType into enriched for local - find enriched.push in local function
    old_enriched = """            enriched.push({
                vehicleId: r.vehicleId,
                vehicleName: r.vehicleName,
                vehicleType: r.vehicleType,
                seats: r.seats,
                qty: t.qty,
                unitCost: t.unitCost,
                unitSell: t.unitSell,
                lineCost: t.lineCost,
                lineSell: t.lineSell
            });"""

    # There may be two patterns - local refresh vs applyLocal. Check create file.
    # The local async function builds enriched differently:
    # Looking at earlier read around 5517...
    return text, a


def patch_local_enriched_push(text):
    # From earlier local pricing loop in create around 5517
    old = """            enriched.push({
                vehicleId: v.vehicleId,
                vehicleName: v.vehicleName,
                vehicleType: v.vehicleType,
                seats: v.seats,
                qty: qty,
                unitCost: unitCost,
                unitSell: unitSell,
                lineCost: lineCost,
                lineSell: lineSell
            });"""
    new = """            enriched.push({
                vehicleId: v.vehicleId,
                vehicleName: v.vehicleName,
                vehicleType: v.vehicleType,
                seats: v.seats,
                qty: qty,
                transferType: rowType,
                type: rowType,
                unitCost: unitCost,
                unitSell: unitSell,
                lineCost: lineCost,
                lineSell: lineSell
            });"""
    return must_replace(text, old, new, "local enriched transferType", 1)


def patch_listings(text):
    n = 0
    # A/D listing vehicle type
    old_ad = """            // Format vehicle type display (Shared/Private)
            const vehicleTypeDisplay = item.transferType === 'P' ? 'Private' : (item.transferType === 'S' ? 'Shared' : '-');"""
    new_ad = """            // Per-vehicle Shared/Private (falls back to single transferType)
            const vehicleTypeDisplay = (typeof formatMultiVehicleTypeDisplay === 'function')
                ? formatMultiVehicleTypeDisplay(item)
                : (item.transferType === 'P' ? 'Private' : (item.transferType === 'S' ? 'Shared' : '-'));"""
    text, a = must_replace(text, old_ad, new_ad, "A/D listing type display", 1)
    n += a

    # edit template uses transferServiceTypeDisplayLabel
    old_ad2 = """            // VEHICLE TYPE column = Shared/Private from transferType / JSON type (not row Arrival/Departure kind)
            const vehicleTypeDisplay = item.hasTransfer
                ? transferServiceTypeDisplayLabel({
                    transferType: item.transferType,
                    transfer_type: item.transfer_type
                })
                : '-';"""
    new_ad2 = """            // Per-vehicle Shared/Private (falls back to single transferType)
            const vehicleTypeDisplay = item.hasTransfer
                ? ((typeof formatMultiVehicleTypeDisplay === 'function')
                    ? formatMultiVehicleTypeDisplay(item)
                    : transferServiceTypeDisplayLabel({
                        transferType: item.transferType,
                        transfer_type: item.transfer_type
                    }))
                : '-';"""
    text, a = must_replace(text, old_ad2, new_ad2, "A/D listing type (edit)", 1)
    n += a

    # Local transfer getTypeClass - prefer multi type display
    old_gt = """        // Helper function to get type/class
        const getTypeClass = (transfer) => {
            const mode = transfer.transportMode || 'local';
            if (mode === 'local') {
                let type = transfer.type || transfer.transferType || '-';
                const typeLower = String(type).toLowerCase();
                if (typeLower === 'arrival' || typeLower === 'departure' || type === '-') {
                    type = transfer.transferType || transfer.transfer_type || type;
                }
                if (type === '-') return '-';
                if (type === 'S' || type === 'sic' || typeLower === 'shared') return 'Shared';
                if (type === 'P' || typeLower === 'private') return 'Private';
                return String(type).charAt(0).toUpperCase() + String(type).slice(1);
            } else {
                return transfer.class || transfer.cabinClass || '-';
            }
        };"""
    new_gt = """        // Helper function to get type/class
        const getTypeClass = (transfer) => {
            const mode = transfer.transportMode || 'local';
            if (mode === 'local') {
                if (typeof formatMultiVehicleTypeDisplay === 'function') {
                    const multi = formatMultiVehicleTypeDisplay(transfer);
                    if (multi && multi !== '-') return multi;
                }
                let type = transfer.type || transfer.transferType || '-';
                const typeLower = String(type).toLowerCase();
                if (typeLower === 'arrival' || typeLower === 'departure' || type === '-') {
                    type = transfer.transferType || transfer.transfer_type || type;
                }
                if (type === '-') return '-';
                if (type === 'S' || type === 'sic' || typeLower === 'shared') return 'Shared';
                if (type === 'P' || typeLower === 'private') return 'Private';
                return String(type).charAt(0).toUpperCase() + String(type).slice(1);
            } else {
                return transfer.class || transfer.cabinClass || '-';
            }
        };"""
    text, a = must_replace(text, old_gt, new_gt, "getTypeClass multi", 1)
    n += a

    # edit uses transferServiceTypeDisplayLabel wrapper
    old_gt2 = """        // Helper function to get type/class (Private/Shared from type OR transferType per JSON)
        const getTypeClass = (transfer) => {
            const mode = transfer.transportMode || 'local';
            if (mode === 'local') {
                return transferServiceTypeDisplayLabel(transfer);
            } else {
                return transfer.class || transfer.cabinClass || '-';
            }
        };"""
    new_gt2 = """        // Helper function to get type/class (Private/Shared from type OR transferType per JSON)
        const getTypeClass = (transfer) => {
            const mode = transfer.transportMode || 'local';
            if (mode === 'local') {
                if (typeof formatMultiVehicleTypeDisplay === 'function') {
                    const multi = formatMultiVehicleTypeDisplay(transfer);
                    if (multi && multi !== '-') return multi;
                }
                return transferServiceTypeDisplayLabel(transfer);
            } else {
                return transfer.class || transfer.cabinClass || '-';
            }
        };"""
    text, a = must_replace(text, old_gt2, new_gt2, "getTypeClass multi (edit)", 1)
    n += a

    return text, n


def patch_populate_restore_type(text):
    """When restoring vehicles into rows, also set type select."""
    old = """                        list.forEach(function (v, i) {
                            const sel = rowEls[i]?.querySelector('.arr-dep-vehicle-select');
                            const qty = rowEls[i]?.querySelector('.arr-dep-vehicle-qty');
                            if (sel && v.vehicleId) sel.value = v.vehicleId;
                            if (qty) qty.value = v.qty || 1;
                        });"""
    new = """                        list.forEach(function (v, i) {
                            const sel = rowEls[i]?.querySelector('.arr-dep-vehicle-select');
                            const qty = rowEls[i]?.querySelector('.arr-dep-vehicle-qty');
                            const typeEl = rowEls[i]?.querySelector('.arr-dep-vehicle-xfer-type');
                            if (sel && v.vehicleId) sel.value = v.vehicleId;
                            if (qty) qty.value = v.qty || 1;
                            if (typeEl) {
                                typeEl.value = (typeof normalizeTransferTypePS === 'function')
                                    ? normalizeTransferTypePS(v.transferType || v.type || 'S')
                                    : (v.transferType || v.type || 'S');
                            }
                        });"""
    return must_replace(text, old, new, "populate restore type", None)


def patch_capture_primary_type(text):
    """captureHotelModalArrDepForm uses first vehicle transferType."""
    old = """            const primary = vehicles[0] || null;
            return {
                portId,
                portName: portSel?.selectedOptions?.[0]?.text || '',
                flightNo: document.getElementById(isArrival ? 'arrivalFlightNo' : 'departureFlightNo')?.value || '',
                hasTransfer: !!(chk && chk.checked),
                transferType: xferType,
                vehicleId: primary?.vehicleId || vehicleId,
                vehicleName: primary?.vehicleName || vehicleOpt?.text || '',
                vehicleType: primary?.vehicleType || vehicleOpt?.getAttribute('data-type') || '',
                vehicles: vehicles,
                vehicleQty: primary?.qty || 1,"""
    new = """            const primary = vehicles[0] || null;
            if (typeof syncMasterTransferTypeFromRows === 'function') {
                syncMasterTransferTypeFromRows(side);
            }
            const primaryXfer = (typeof normalizeTransferTypePS === 'function')
                ? normalizeTransferTypePS(primary?.transferType || primary?.type || xferType)
                : (primary?.transferType || xferType);
            return {
                portId,
                portName: portSel?.selectedOptions?.[0]?.text || '',
                flightNo: document.getElementById(isArrival ? 'arrivalFlightNo' : 'departureFlightNo')?.value || '',
                hasTransfer: !!(chk && chk.checked),
                transferType: primaryXfer,
                vehicleId: primary?.vehicleId || vehicleId,
                vehicleName: primary?.vehicleName || vehicleOpt?.text || '',
                vehicleType: primary?.vehicleType || vehicleOpt?.getAttribute('data-type') || '',
                vehicles: vehicles,
                vehicleQty: primary?.qty || 1,"""
    return must_replace(text, old, new, "capture primary type", 1)


def patch_sync_xfer_from_vehicles(text):
    """After compute, set xferType from result.transferType / first vehicle."""
    # In upsertCityPort after syncedVehicles assigned
    old = """                        syncedVehicles = result.vehicles || [];
                        vehicleCost = result.storedCost || 0;
                        vehicleSell = result.storedSell || 0;
                        if (syncedVehicles[0]) {
                            vehicleId = syncedVehicles[0].vehicleId || vehicleId;
                            vehicleName = syncedVehicles[0].vehicleName || vehicleName;
                            vehicleType = syncedVehicles[0].vehicleType || vehicleType;"""
    new = """                        syncedVehicles = result.vehicles || [];
                        vehicleCost = result.storedCost || 0;
                        vehicleSell = result.storedSell || 0;
                        if (result.transferType) {
                            xferType = (typeof normalizeTransferTypePS === 'function')
                                ? normalizeTransferTypePS(result.transferType) : result.transferType;
                        } else if (syncedVehicles[0] && (syncedVehicles[0].transferType || syncedVehicles[0].type)) {
                            xferType = (typeof normalizeTransferTypePS === 'function')
                                ? normalizeTransferTypePS(syncedVehicles[0].transferType || syncedVehicles[0].type)
                                : (syncedVehicles[0].transferType || syncedVehicles[0].type);
                        }
                        if (syncedVehicles[0]) {
                            vehicleId = syncedVehicles[0].vehicleId || vehicleId;
                            vehicleName = syncedVehicles[0].vehicleName || vehicleName;
                            vehicleType = syncedVehicles[0].vehicleType || vehicleType;"""
    return must_replace(text, old, new, "sync xferType from vehicles", 1)


def main():
    for path in FILES:
        print(f"=== {path.name} ===")
        text = path.read_text(encoding="utf-8")
        total = 0
        text, a = must_replace(text, HEADER_OLD, HEADER_NEW, "table Type header")
        total += a
        text, a = must_replace(text, ARR_TYPE_OLD, ARR_TYPE_NEW, "hide arrival Type", 1)
        total += a
        text, a = must_replace(text, DEP_TYPE_OLD, DEP_TYPE_NEW, "hide departure Type", 1)
        total += a
        text, a = must_replace(text, LOCAL_TYPE_OLD, LOCAL_TYPE_NEW, "hide local Type", 1)
        total += a

        text, a = patch_js_core(text)
        total += a
        text, a = patch_compute(text)
        total += a
        text, a = patch_local_pricing(text)
        total += a
        text, a = patch_local_enriched_push(text)
        total += a
        text, a = patch_listings(text)
        total += a
        text, a = patch_populate_restore_type(text)
        total += a
        text, a = patch_capture_primary_type(text)
        total += a
        text, a = patch_sync_xfer_from_vehicles(text)
        total += a

        path.write_text(text, encoding="utf-8")
        print(f"  TOTAL {total} patches -> {path.name}")


if __name__ == "__main__":
    main()
