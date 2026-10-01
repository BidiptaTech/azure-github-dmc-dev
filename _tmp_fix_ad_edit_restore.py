# -*- coding: utf-8 -*-
"""Fix A/D edit modal restore on create + edit templates."""
from pathlib import Path
import re

CREATE = Path(r"c:\xampp\htdocs\Azure_new_files\resources\views\enquiryform_pro\create.blade.php")
EDIT = Path(r"c:\xampp\htdocs\Azure_new_files\resources\views\enquiryform_pro\edit.blade.php")
CITY = Path(r"c:\xampp\htdocs\Azure_new_files\resources\views\enquiryform_pro\partials\city-destination-scripts.blade.php")

edit_text = EDIT.read_text(encoding="utf-8")
create_text = CREATE.read_text(encoding="utf-8")
city_text = CITY.read_text(encoding="utf-8")

# ---------------------------------------------------------------------------
# Helpers from edit → create
# ---------------------------------------------------------------------------
h_start = edit_text.find("    function setArrDepSelectByIdOrName(elOrId, idValue, nameValue) {")
h_end = edit_text.find("    /** Drop duplicate arrival/departure rows loaded from orders", h_start)
if h_start < 0 or h_end < 0:
    raise SystemExit("helpers block not found in edit")
helpers = edit_text[h_start:h_end]

if "function setArrDepSelectByIdOrName" not in create_text:
    anchor = "\n    function editArrivalDeparture(index) {"
    if anchor not in create_text:
        raise SystemExit("editArrivalDeparture missing in create")
    create_text = create_text.replace(anchor, "\n" + helpers + anchor, 1)
    print("create: inserted helpers")
else:
    print("create: helpers already present")

# ---------------------------------------------------------------------------
# Improved vehicle restore snippets
# ---------------------------------------------------------------------------
ARR_ENSURE_OLD = """                        if (typeof ensureArrDepVehicleRows === 'function') {
                            ensureArrDepVehicleRows('arrival', rows.length ? rows : [{ vehicleId: '', qty: 1 }]);
                            if (typeof filterArrivalVehiclesByServiceType === 'function') filterArrivalVehiclesByServiceType();
                        }"""

DEP_ENSURE_OLD = """                        if (typeof ensureArrDepVehicleRows === 'function') {
                            ensureArrDepVehicleRows('departure', rows.length ? rows : [{ vehicleId: '', qty: 1 }]);
                            if (typeof filterDepartureVehiclesByServiceType === 'function') filterDepartureVehiclesByServiceType();
                        }"""

ARR_ENSURE_NEW = r"""                        if (typeof ensureArrDepVehicleRows === 'function') {
                            ensureArrDepVehicleRows('arrival', rows.length ? rows : [{ vehicleId: '', qty: 1 }]);
                            if (typeof filterArrivalVehiclesByServiceType === 'function') filterArrivalVehiclesByServiceType(cityToRestore);
                            (function () {
                                const container = document.getElementById('arrivalVehicleRows');
                                const rowEls = container ? container.querySelectorAll('.arr-dep-vehicle-row') : [];
                                rows.forEach(function (v, i) {
                                    const row = rowEls[i];
                                    if (!row) return;
                                    const sel = row.querySelector('.arr-dep-vehicle-select');
                                    const qtyEl = row.querySelector('.arr-dep-vehicle-qty');
                                    const typeEl = row.querySelector('.arr-dep-vehicle-xfer-type');
                                    const vid = String(v.vehicleId || v.vehicle_id || '');
                                    if (sel && vid) {
                                        if (typeof applyArrDepVehicleFilterToSelect === 'function') applyArrDepVehicleFilterToSelect('arrival', sel, cityToRestore);
                                        sel.value = vid;
                                        if (sel.value !== vid) {
                                            const baseName = String(v.vehicleName || '').replace(/\s*\([^)]*\)\s*$/, '').trim();
                                            const opt = Array.from(sel.options).find(function (o) {
                                                return String(o.value) === vid || (baseName && (o.textContent || '').indexOf(baseName) !== -1);
                                            });
                                            if (opt) {
                                                if (typeof setOptionCityVisibility === 'function') setOptionCityVisibility(opt, true);
                                                sel.value = opt.value;
                                            }
                                        }
                                    }
                                    if (qtyEl) qtyEl.value = v.qty || v.quantity || 1;
                                    if (typeEl) {
                                        typeEl.value = (typeof normalizeTransferTypePS === 'function')
                                            ? normalizeTransferTypePS(v.transferType || v.type || 'S')
                                            : (v.transferType || v.type || 'S');
                                    }
                                    if (typeof writeArrDepRowPaxInputs === 'function') {
                                        writeArrDepRowPaxInputs(row, v.adults ?? v.adultsQty, v.child ?? v.childQty, v.infant ?? v.infantQty);
                                    }
                                });
                                if (typeof fillArrDepRowUnitPrices === 'function' && rows.length) {
                                    fillArrDepRowUnitPrices('arrival', rows);
                                    Array.from(rowEls).forEach(function (row) {
                                        row.querySelectorAll('.arr-dep-unit-cost, .arr-dep-unit-sell').forEach(function (inp) {
                                            inp.dataset.userEdited = '1';
                                        });
                                    });
                                }
                                if (typeof syncArrDepPrimaryVehicleSelect === 'function') syncArrDepPrimaryVehicleSelect('arrival');
                                if (typeof updateArrDepVehicleCoverage === 'function') updateArrDepVehicleCoverage('arrival');
                            })();
                        }"""

DEP_ENSURE_NEW = ARR_ENSURE_NEW.replace("'arrival'", "'departure'").replace(
    "arrivalVehicleRows", "departureVehicleRows"
).replace("filterArrivalVehiclesByServiceType", "filterDepartureVehiclesByServiceType")

ARR_STO_OLD = """                    setTimeout(() => {
                        if (typeof ensureArrDepCitySelectOptions === 'function') ensureArrDepCitySelectOptions(cityToRestore);
                        filterArrivalVehiclesByServiceType(cityToRestore);
                        applyVehicleToSelect(document.getElementById('arrivalVehicleType'), data.vehicleId, data.vehicleName);
                        setArrDepSelectByIdOrName('arrivalPort', data.portId, data.portName);
                        setArrDepSelectByIdOrName('arrivalDestination', data.transferDestinationId, data.transferDestinationName);
                        setArrDepTransferPriceUI('arrival', savedArrCost, savedArrSell, savedArrCost > 0 || savedArrSell > 0 ? '' : '(Saved price)', true);
                        if (typeof applyArrDepGuideUiFromEntry === 'function') {
                            applyArrDepGuideUiFromEntry('arrival', arrivalDepartureList[index] || data, cityToRestore);
                        }
                        setTimeout(() => {
                            window._suppressArrDepZoneRefresh = false;
                            window._suppressArrDepLiveSync = false;
                            window._populatingArrDepEdit = false;
                        }, 150);
                    }, 200);"""

ARR_STO_NEW = r"""                    setTimeout(() => {
                        if (typeof ensureArrDepCitySelectOptions === 'function') ensureArrDepCitySelectOptions(cityToRestore);
                        if (typeof applyArrivalDepartureCityFilters === 'function') {
                            applyArrivalDepartureCityFilters(cityToRestore, { skipDefaults: true, skipGuideDefault: true });
                        }
                        setArrDepSelectByIdOrName('arrivalPort', data.portId, data.portName);
                        setArrDepSelectByIdOrName('arrivalDestination', data.transferDestinationId, data.transferDestinationName);
                        const list2 = (typeof normalizeArrDepVehiclesList === 'function')
                            ? normalizeArrDepVehiclesList(data.vehicles && data.vehicles.length ? data : (linkedTransfer || data))
                            : (Array.isArray(data.vehicles) ? data.vehicles : []);
                        const rows2 = list2.length ? list2 : (data.vehicleId ? [{ vehicleId: data.vehicleId, qty: data.vehicleQty || 1, transferType: data.transferType, adults: data.adultsQty, child: data.childQty, infant: data.infantQty, unitCost: data.cost, unitSell: data.sell }] : []);
                        if (typeof ensureArrDepVehicleRows === 'function' && rows2.length) {
                            ensureArrDepVehicleRows('arrival', rows2);
                        }
                        if (typeof filterArrivalVehiclesByServiceType === 'function') filterArrivalVehiclesByServiceType(cityToRestore);
                        const container2 = document.getElementById('arrivalVehicleRows');
                        const rowEls2 = container2 ? container2.querySelectorAll('.arr-dep-vehicle-row') : [];
                        rows2.forEach(function (v, i) {
                            const row = rowEls2[i];
                            if (!row) return;
                            const sel = row.querySelector('.arr-dep-vehicle-select');
                            const qtyEl = row.querySelector('.arr-dep-vehicle-qty');
                            const typeEl = row.querySelector('.arr-dep-vehicle-xfer-type');
                            const vid = String(v.vehicleId || v.vehicle_id || '');
                            if (sel && vid) {
                                if (typeof applyArrDepVehicleFilterToSelect === 'function') applyArrDepVehicleFilterToSelect('arrival', sel, cityToRestore);
                                sel.value = vid;
                                if (sel.value !== vid) {
                                    applyVehicleToSelect(sel, vid, v.vehicleName);
                                }
                            }
                            if (qtyEl) qtyEl.value = v.qty || v.quantity || 1;
                            if (typeEl) {
                                typeEl.value = (typeof normalizeTransferTypePS === 'function')
                                    ? normalizeTransferTypePS(v.transferType || v.type || 'S')
                                    : (v.transferType || v.type || 'S');
                            }
                            if (typeof writeArrDepRowPaxInputs === 'function') {
                                writeArrDepRowPaxInputs(row, v.adults ?? v.adultsQty, v.child ?? v.childQty, v.infant ?? v.infantQty);
                            }
                        });
                        if (typeof fillArrDepRowUnitPrices === 'function' && rows2.length) {
                            fillArrDepRowUnitPrices('arrival', rows2);
                            Array.from(rowEls2).forEach(function (row) {
                                row.querySelectorAll('.arr-dep-unit-cost, .arr-dep-unit-sell').forEach(function (inp) {
                                    inp.dataset.userEdited = '1';
                                });
                            });
                        }
                        applyVehicleToSelect(document.getElementById('arrivalVehicleType'), data.vehicleId, data.vehicleName);
                        setArrDepSelectByIdOrName('arrivalPort', data.portId, data.portName);
                        setArrDepSelectByIdOrName('arrivalDestination', data.transferDestinationId, data.transferDestinationName);
                        setArrDepTransferPriceUI('arrival', savedArrCost, savedArrSell, savedArrCost > 0 || savedArrSell > 0 ? '' : '(Saved price)', true);
                        if (typeof applyArrDepGuideUiFromEntry === 'function') {
                            applyArrDepGuideUiFromEntry('arrival', arrivalDepartureList[index] || data, cityToRestore);
                        }
                        if (typeof updateArrDepVehicleCoverage === 'function') updateArrDepVehicleCoverage('arrival');
                        setTimeout(() => {
                            window._suppressArrDepZoneRefresh = false;
                            window._suppressArrDepLiveSync = false;
                            window._populatingArrDepEdit = false;
                        }, 150);
                    }, 200);"""

DEP_STO_OLD = """                    setTimeout(() => {
                        if (typeof ensureArrDepCitySelectOptions === 'function') ensureArrDepCitySelectOptions(cityToRestore);
                        filterDepartureVehiclesByServiceType(cityToRestore);
                        applyVehicleToSelect(document.getElementById('departureVehicleType'), data.vehicleId, data.vehicleName);
                        setArrDepSelectByIdOrName('departurePort', data.portId, data.portName);
                        setArrDepSelectByIdOrName('departureDestination', data.transferDestinationId, data.transferDestinationName);
                        setArrDepTransferPriceUI('departure', savedDepCost, savedDepSell, savedDepCost > 0 || savedDepSell > 0 ? '' : '(Saved price)', true);
                        if (typeof applyArrDepGuideUiFromEntry === 'function') {
                            applyArrDepGuideUiFromEntry('departure', arrivalDepartureList[index] || data, cityToRestore);
                        }
                        setTimeout(() => {
                            window._suppressArrDepZoneRefresh = false;
                            window._suppressArrDepLiveSync = false;
                            window._populatingArrDepEdit = false;
                        }, 150);
                    }, 200);"""

DEP_STO_NEW = (ARR_STO_NEW
    .replace("setArrDepSelectByIdOrName('arrivalPort'", "setArrDepSelectByIdOrName('departurePort'")
    .replace("setArrDepSelectByIdOrName('arrivalDestination'", "setArrDepSelectByIdOrName('departureDestination'")
    .replace("ensureArrDepVehicleRows('arrival'", "ensureArrDepVehicleRows('departure'")
    .replace("filterArrivalVehiclesByServiceType", "filterDepartureVehiclesByServiceType")
    .replace("arrivalVehicleRows", "departureVehicleRows")
    .replace("applyArrDepVehicleFilterToSelect('arrival'", "applyArrDepVehicleFilterToSelect('departure'")
    .replace("fillArrDepRowUnitPrices('arrival'", "fillArrDepRowUnitPrices('departure'")
    .replace("getElementById('arrivalVehicleType')", "getElementById('departureVehicleType')")
    .replace("setArrDepTransferPriceUI('arrival'", "setArrDepTransferPriceUI('departure'")
    .replace("applyArrDepGuideUiFromEntry('arrival'", "applyArrDepGuideUiFromEntry('departure'")
    .replace("updateArrDepVehicleCoverage('arrival')", "updateArrDepVehicleCoverage('departure')")
    .replace("savedArrCost", "savedDepCost")
    .replace("savedArrSell", "savedDepSell")
)


def patch_timeout_block(text, label):
    m = re.search(
        r"            // Populate the clicked entry data with a delay to ensure modal is fully initialized\n"
        r"            setTimeout\(\(\) => \{.*?"
        r"                window\.isEditingArrivalDeparture = false;\n"
        r"            \}, 400\);",
        text,
        re.S,
    )
    if not m:
        raise SystemExit(f"{label}: populate timeout not found")
    block = m.group(0)

    # If create still has the old weak restore, replace entire timeout with edit's improved base first
    if "resolveCityForArrDepRow" not in block:
        edit_m = re.search(
            r"            // Populate the clicked entry data with a delay to ensure modal is fully initialized\n"
            r"            setTimeout\(\(\) => \{.*?"
            r"                window\.isEditingArrivalDeparture = false;\n"
            r"            \}, 400\);",
            edit_text,
            re.S,
        )
        if not edit_m:
            raise SystemExit("edit timeout not found for base copy")
        block = edit_m.group(0)
        print(f"{label}: replaced weak timeout with edit base")

    # Ensure applyVehicleToSelect exists in block (edit has it)
    if "function applyVehicleToSelect" not in block and "applyVehicleToSelect" in block:
        pass  # uses inner function from edit base

    n = 0
    if ARR_ENSURE_OLD in block:
        block = block.replace(ARR_ENSURE_OLD, ARR_ENSURE_NEW, 1)
        n += 1
        print(f"{label}: arrival ensure patched")
    elif "fillArrDepRowUnitPrices('arrival'" in block:
        print(f"{label}: arrival ensure already enhanced")
    else:
        # maybe already has filterArrivalVehiclesByServiceType(cityToRestore) without fill
        alt = ARR_ENSURE_OLD.replace("filterArrivalVehiclesByServiceType();", "filterArrivalVehiclesByServiceType(cityToRestore);")
        if alt in block:
            block = block.replace(alt, ARR_ENSURE_NEW, 1)
            n += 1
            print(f"{label}: arrival ensure patched (alt)")
        else:
            print(f"{label}: WARN arrival ensure not found")

    if DEP_ENSURE_OLD in block:
        block = block.replace(DEP_ENSURE_OLD, DEP_ENSURE_NEW, 1)
        n += 1
        print(f"{label}: dep ensure patched")
    else:
        alt = DEP_ENSURE_OLD.replace("filterDepartureVehiclesByServiceType();", "filterDepartureVehiclesByServiceType(cityToRestore);")
        if alt in block:
            block = block.replace(alt, DEP_ENSURE_NEW, 1)
            n += 1
            print(f"{label}: dep ensure patched (alt)")
        elif "fillArrDepRowUnitPrices('departure'" in block:
            print(f"{label}: dep ensure already enhanced")
        else:
            print(f"{label}: WARN dep ensure not found")

    if ARR_STO_OLD in block:
        block = block.replace(ARR_STO_OLD, ARR_STO_NEW, 1)
        n += 1
        print(f"{label}: arrival setTimeout patched")
    elif "const list2 =" in block and "arrivalVehicleRows" in block:
        print(f"{label}: arrival setTimeout already enhanced")
    else:
        print(f"{label}: WARN arrival setTimeout not found")

    if DEP_STO_OLD in block:
        block = block.replace(DEP_STO_OLD, DEP_STO_NEW, 1)
        n += 1
        print(f"{label}: dep setTimeout patched")
    elif "departureVehicleRows" in block and "const list2 =" in block:
        # might have been partially updated
        print(f"{label}: dep setTimeout maybe already enhanced")
    else:
        print(f"{label}: WARN dep setTimeout not found")

    # Also set editing index before openAccommodationModal in editArrivalDeparture
    text = text[: m.start()] + block + text[m.end() :]
    return text


create_text = patch_timeout_block(create_text, "create")
# Re-read edit from disk variable - patch edit_text similarly using current edit_text as base for weak check
edit_text = patch_timeout_block(edit_text, "edit")

# ---------------------------------------------------------------------------
# forceCityPick: never while populating edit
# ---------------------------------------------------------------------------
for label, text in [("create", create_text), ("edit", edit_text)]:
    old = "        const forceCityPick = !!window._arrDepVehicleForceCity;"
    new = "        const forceCityPick = !!window._arrDepVehicleForceCity && !window._populatingArrDepEdit && !window.isEditingArrivalDeparture;"
    c = text.count(old)
    if c:
        text = text.replace(old, new)
        print(f"{label}: forceCityPick patched x{c}")
    else:
        print(f"{label}: forceCityPick already patched or missing ({c})")
    if label == "create":
        create_text = text
    else:
        edit_text = text

# city-destination-scripts: don't force vehicle pick when skipDefaults
old_force = """        // Mark city so vehicle filters force re-pick defaults
        window._arrDepVehicleForceCity = city;
"""
new_force = """        // Mark city so vehicle filters force re-pick defaults (ADD only — never while editing restore)
        if (!opts.skipDefaults) {
            window._arrDepVehicleForceCity = city;
        }
"""
if old_force in city_text:
    city_text = city_text.replace(old_force, new_force, 1)
    print("city scripts: skip forceCity on skipDefaults")
else:
    print("city scripts: forceCity block not found or already patched")

# ---------------------------------------------------------------------------
# openAccommodationModal: preserve editingArrivalDepartureIndex when editing
# ---------------------------------------------------------------------------
old_clear = """        window.isArrivalDepartureOnlyMode = false;
        window.editingArrivalDepartureIndex = null;
        window.editingArrivalDepartureType = null;
        if (!isArrivalDepartureOnly) {
            window.isEditingArrivalDeparture = false; // Reset only when not editing arrival/departure
        }"""
new_clear = """        window.isArrivalDepartureOnlyMode = false;
        // Keep editing index/type when reopening for A/D edit (otherwise restore targets wrong row)
        if (!window.isEditingArrivalDeparture) {
            window.editingArrivalDepartureIndex = null;
            window.editingArrivalDepartureType = null;
        }
        if (!isArrivalDepartureOnly) {
            window.isEditingArrivalDeparture = false; // Reset only when not editing arrival/departure
        }"""

for label, text in [("create", create_text), ("edit", edit_text)]:
    if old_clear in text:
        text = text.replace(old_clear, new_clear, 1)
        print(f"{label}: openAccommodationModal preserve edit index")
    else:
        print(f"{label}: openAccommodationModal clear block not found")
    if label == "create":
        create_text = text
    else:
        edit_text = text

# Set editingArrivalDepartureIndex BEFORE openAccommodationModal in editArrivalDeparture
old_flags = """            window.isAddingNewArrivalDeparture = false;
            
            // Set flag to prevent initializeModalDates from overwriting arrival/departure dates
            window.isEditingArrivalDeparture = true;
            window.willOpenArrivalDepartureOnly = true; // So openAccommodationModal keeps section and does not reset isEditingArrivalDeparture
            openAccommodationModal();"""

new_flags = """            window.isAddingNewArrivalDeparture = false;
            
            // Set flag to prevent initializeModalDates from overwriting arrival/departure dates
            window.isEditingArrivalDeparture = true;
            window.willOpenArrivalDepartureOnly = true; // So openAccommodationModal keeps section and does not reset isEditingArrivalDeparture
            window.editingArrivalDepartureIndex = index;
            window.editingArrivalDepartureType = arrivalDeparture.type;
            openAccommodationModal();"""

# create may have slightly different comment
old_flags_create = """            window.isAddingNewArrivalDeparture = false;
            
            // Set flag to prevent initializeModalDates from overwriting arrival/departure dates
            window.isEditingArrivalDeparture = true;
            window.willOpenArrivalDepartureOnly = true; // So openAccommodationModal keeps section and does not reset isEditingArrivalDeparture
            openAccommodationModal();"""

old_flags_edit = """            window.isAddingNewArrivalDeparture = false;
            
            // Set flag to prevent initializeModalDates from overwriting arrival/departure dates
            window.isEditingArrivalDeparture = true;
            window.willOpenArrivalDepartureOnly = true;
            openAccommodationModal();"""

if old_flags_create in create_text:
    create_text = create_text.replace(old_flags_create, new_flags, 1)
    print("create: set edit index before open modal")
elif "window.editingArrivalDepartureIndex = index;\n            window.editingArrivalDepartureType = arrivalDeparture.type;\n            openAccommodationModal();" in create_text:
    print("create: edit index already set before open")
else:
    print("create: WARN flags block not found")

new_flags_edit = """            window.isAddingNewArrivalDeparture = false;
            
            // Set flag to prevent initializeModalDates from overwriting arrival/departure dates
            window.isEditingArrivalDeparture = true;
            window.willOpenArrivalDepartureOnly = true;
            window.editingArrivalDepartureIndex = index;
            window.editingArrivalDepartureType = arrivalDeparture.type;
            openAccommodationModal();"""

if old_flags_edit in edit_text:
    edit_text = edit_text.replace(old_flags_edit, new_flags_edit, 1)
    print("edit: set edit index before open modal")
else:
    print("edit: WARN flags block not found")

# ---------------------------------------------------------------------------
# editTransfer: safer A/D lookup (no hotel first-match fallback)
# ---------------------------------------------------------------------------
OLD_XFER_LOOKUP = """        if (isPortArrDepLocal) {
            const wantArrival = destStr.indexOf('Arrival:') === 0;
            let adIdx = arrivalDepartureList.findIndex(item => String(item.transferId) === String(transfer.id));
            if (adIdx === -1) {
                adIdx = arrivalDepartureList.findIndex(item => String(item.id) === String(transfer.sourceId));
            }
            if (adIdx === -1) {
                adIdx = arrivalDepartureList.findIndex(item => {
                    if (item.sourceType !== 'hotel') return false;
                    const isArr = item.type === 'Arrival' || item.type === 'arrival';
                    if (wantArrival) return isArr || item.travel_type === 'entry_port';
                    return !isArr && (item.type === 'Departure' || item.type === 'departure' || item.travel_type === 'exit_port');
                });
            }
            if (adIdx !== -1) {
                editArrivalDeparture(adIdx);
                return;
            }
        }"""

NEW_XFER_LOOKUP = """        if (isPortArrDepLocal) {
            const wantArrival = destStr.indexOf('Arrival:') === 0;
            let adIdx = arrivalDepartureList.findIndex(item => String(item.transferId) === String(transfer.id));
            if (adIdx === -1 && transfer.sourceId) {
                adIdx = arrivalDepartureList.findIndex(item => String(item.id) === String(transfer.sourceId));
            }
            if (adIdx === -1) {
                // Match by city + port + arrival/departure type (multi-city safe)
                const tCity = String(transfer.city || '').trim().toLowerCase();
                const tPortId = transfer.portId != null ? String(transfer.portId) : '';
                const tPortName = String(transfer.portName || '').trim().toLowerCase();
                adIdx = arrivalDepartureList.findIndex(item => {
                    const isArr = item.type === 'Arrival' || item.type === 'arrival' || item.travel_type === 'entry_port';
                    const isDep = item.type === 'Departure' || item.type === 'departure' || item.travel_type === 'exit_port';
                    if (wantArrival && !isArr) return false;
                    if (!wantArrival && !isDep) return false;
                    if (tCity) {
                        const iCity = String(item.city || '').trim().toLowerCase();
                        if (iCity && iCity !== tCity) return false;
                    }
                    if (tPortId && item.portId && String(item.portId) === tPortId) return true;
                    if (tPortName) {
                        const iPort = String(item.portName || '').trim().toLowerCase();
                        if (iPort && (iPort === tPortName || iPort.indexOf(tPortName) !== -1 || tPortName.indexOf(iPort) !== -1)) return true;
                    }
                    return false;
                });
            }
            if (adIdx !== -1) {
                editArrivalDeparture(adIdx);
                return;
            }
        }"""

for label, text in [("create", create_text), ("edit", edit_text)]:
    if OLD_XFER_LOOKUP in text:
        text = text.replace(OLD_XFER_LOOKUP, NEW_XFER_LOOKUP, 1)
        print(f"{label}: editTransfer lookup fixed")
    elif "Match by city + port + arrival/departure type" in text:
        print(f"{label}: editTransfer lookup already fixed")
    else:
        print(f"{label}: WARN editTransfer lookup block not found")
    if label == "create":
        create_text = text
    else:
        edit_text = text

# Also fix sourceType arrival/departure findIndex to prefer sourceId + city
OLD_ARR_SRC = """            } else if (transfer.sourceType === 'arrival' || transfer.sourceType === 'Arrival') {
                // Find the arrival that has this transfer
                const arrivalIndex = arrivalDepartureList.findIndex(item => 
                    (item.type === 'Arrival' || item.type === 'arrival') && (item.transferId === transfer.id || item.id === transfer.sourceId)
                );
                if (arrivalIndex !== -1) {
                    editArrivalDeparture(arrivalIndex);
                    return;
                }
            } else if (transfer.sourceType === 'departure' || transfer.sourceType === 'Departure') {
                // Find the departure that has this transfer
                const departureIndex = arrivalDepartureList.findIndex(item => 
                    (item.type === 'Departure' || item.type === 'departure') && (item.transferId === transfer.id || item.id === transfer.sourceId)
                );
                if (departureIndex !== -1) {
                    editArrivalDeparture(departureIndex);
                    return;
                }
            }"""

NEW_ARR_SRC = """            } else if (transfer.sourceType === 'arrival' || transfer.sourceType === 'Arrival') {
                // Prefer exact sourceId / transferId; fall back to city+port match
                let arrivalIndex = arrivalDepartureList.findIndex(item =>
                    (item.type === 'Arrival' || item.type === 'arrival') && String(item.id) === String(transfer.sourceId)
                );
                if (arrivalIndex === -1) {
                    arrivalIndex = arrivalDepartureList.findIndex(item =>
                        (item.type === 'Arrival' || item.type === 'arrival') && String(item.transferId) === String(transfer.id)
                    );
                }
                if (arrivalIndex === -1) {
                    const tCity = String(transfer.city || '').trim().toLowerCase();
                    const tPortId = transfer.portId != null ? String(transfer.portId) : '';
                    arrivalIndex = arrivalDepartureList.findIndex(item => {
                        if (!(item.type === 'Arrival' || item.type === 'arrival')) return false;
                        if (tCity && String(item.city || '').trim().toLowerCase() !== tCity) return false;
                        if (tPortId && item.portId && String(item.portId) === tPortId) return true;
                        return false;
                    });
                }
                if (arrivalIndex !== -1) {
                    editArrivalDeparture(arrivalIndex);
                    return;
                }
            } else if (transfer.sourceType === 'departure' || transfer.sourceType === 'Departure') {
                let departureIndex = arrivalDepartureList.findIndex(item =>
                    (item.type === 'Departure' || item.type === 'departure') && String(item.id) === String(transfer.sourceId)
                );
                if (departureIndex === -1) {
                    departureIndex = arrivalDepartureList.findIndex(item =>
                        (item.type === 'Departure' || item.type === 'departure') && String(item.transferId) === String(transfer.id)
                    );
                }
                if (departureIndex === -1) {
                    const tCity = String(transfer.city || '').trim().toLowerCase();
                    const tPortId = transfer.portId != null ? String(transfer.portId) : '';
                    departureIndex = arrivalDepartureList.findIndex(item => {
                        if (!(item.type === 'Departure' || item.type === 'departure')) return false;
                        if (tCity && String(item.city || '').trim().toLowerCase() !== tCity) return false;
                        if (tPortId && item.portId && String(item.portId) === tPortId) return true;
                        return false;
                    });
                }
                if (departureIndex !== -1) {
                    editArrivalDeparture(departureIndex);
                    return;
                }
            }"""

for label, text in [("create", create_text), ("edit", edit_text)]:
    if OLD_ARR_SRC in text:
        text = text.replace(OLD_ARR_SRC, NEW_ARR_SRC, 1)
        print(f"{label}: sourceType A/D lookup fixed")
    else:
        print(f"{label}: WARN sourceType A/D lookup not found")
    if label == "create":
        create_text = text
    else:
        edit_text = text

# create: skipArrDepCitySync like edit (hotel loadHotels shouldn't overwrite A/D city while editing)
old_skip = """        if (typeof filterPortsBySelectedCountries === 'function') {
                filterPortsBySelectedCountries(document.getElementById('hotelDestination')?.value || undefined);
            }"""
# That's in openAccommodationModal - for A/D only mode should use arrivalDepartureCity
# Patch openAccommodationModal Select2 filterPorts call
old_port_filter = """            if (typeof filterPortsBySelectedCountries === 'function') {
                filterPortsBySelectedCountries(document.getElementById('hotelDestination')?.value || undefined);
            }"""
new_port_filter = """            if (typeof filterPortsBySelectedCountries === 'function') {
                const portFilterCity = (isArrivalDepartureOnly || window.isEditingArrivalDeparture)
                    ? (document.getElementById('arrivalDepartureCity')?.value || undefined)
                    : (document.getElementById('hotelDestination')?.value || undefined);
                filterPortsBySelectedCountries(portFilterCity);
            }"""

for label, text in [("create", create_text), ("edit", edit_text)]:
    if old_port_filter in text:
        text = text.replace(old_port_filter, new_port_filter, 1)
        print(f"{label}: port filter city for A/D edit")
    else:
        print(f"{label}: port filter already patched or missing")
    if label == "create":
        create_text = text
    else:
        edit_text = text

CREATE.write_text(create_text, encoding="utf-8")
EDIT.write_text(edit_text, encoding="utf-8")
CITY.write_text(city_text, encoding="utf-8")
print("DONE")
