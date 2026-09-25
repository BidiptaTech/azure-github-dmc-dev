# -*- coding: utf-8 -*-
"""Fix Edit Arrival/Departure modal: persist vehicles + guide deselect; sync listings."""
from pathlib import Path

HELPER = r'''
    /** Remove all guides linked to a standalone arrival/departure entry. */
    function removeGuidesLinkedToArrDep(entryId, side, guideListId) {
        const sid = String(side || '').toLowerCase();
        const eid = String(entryId || '');
        const gid = guideListId != null ? String(guideListId) : '';
        guideList = guideList.filter(function (g) {
            if (gid && String(g.id) === gid) return false;
            const linked = String(g.linkedTo || '').toLowerCase();
            if (linked === sid) {
                const src = String(g.arrivalId || g.departureId || g.sourceId || '');
                if (eid && src === eid) return false;
            }
            return true;
        });
    }

    /**
     * Persist multi-vehicle rows from Edit Arrival/Departure modal onto
     * arrivalDepartureList[index] + linked transferList row. Listing refresh is caller's job.
     */
    async function syncStandaloneArrDepVehiclesFromModal(side, listIndex) {
        const isArr = side === 'arrival';
        const item = arrivalDepartureList[listIndex];
        if (!item) return null;
        const dmcId = '{{ $dmc_id ?? "" }}';
        const transferChecked = !!(document.getElementById(isArr ? 'arrivalTransfer' : 'departureTransfer')?.checked);
        const dateTime = document.getElementById(isArr ? 'arrivalDateTime' : 'departureDateTime')?.value || item.dateTime || '';
        const portSelect = document.getElementById(isArr ? 'arrivalPort' : 'departurePort');
        const portId = portSelect?.value || item.portId || '';
        const portName = portSelect?.selectedOptions?.[0]?.text || item.portName || '';
        const destSelect = document.getElementById(isArr ? 'arrivalDestination' : 'departureDestination');
        const destId = destSelect?.value || item.transferDestinationId || '';
        const destName = destSelect?.selectedOptions?.[0]?.getAttribute('data-name') || item.transferDestinationName || '';
        const destType = destSelect?.selectedOptions?.[0]?.getAttribute('data-type') || 'hotel';
        let actualDestId = destId;
        if (destType === 'hotel') {
            const hu = destSelect?.selectedOptions?.[0]?.getAttribute('data-hotel-unique-id');
            if (hu) actualDestId = hu;
        }
        const adults = parseInt(document.getElementById(isArr ? 'arrivalAdults' : 'departureAdults')?.value || item.adultsQty || item.adults || 0, 10) || 0;
        const child = parseInt(document.getElementById(isArr ? 'arrivalChild' : 'departureChild')?.value || item.childQty || item.child || 0, 10) || 0;
        const infant = parseInt(document.getElementById(isArr ? 'arrivalInfant' : 'departureInfant')?.value || item.infantQty || item.infant || 0, 10) || 0;
        const masterType = document.getElementById(isArr ? 'arrivalTransferType' : 'departureTransferType')?.value || item.transferType || 'S';
        const focSvc = document.getElementById(isArr ? 'arrivalFocServiceDiscount' : 'departureFocServiceDiscount')?.checked === true;
        const city = String(document.getElementById('arrivalDepartureCity')?.value || item.city || '').trim();

        let vehicles = (typeof collectArrDepVehiclesPayload === 'function')
            ? collectArrDepVehiclesPayload(side)
            : [];
        if ((!vehicles || !vehicles.length) && document.getElementById(isArr ? 'arrivalVehicleType' : 'departureVehicleType')?.value) {
            const sel = document.getElementById(isArr ? 'arrivalVehicleType' : 'departureVehicleType');
            vehicles = [{
                vehicleId: sel.value,
                vehicleName: (sel.selectedOptions?.[0]?.text || '').replace(/\s*\(\d+\s*seats?\)\s*$/i, '').trim(),
                vehicleType: sel.selectedOptions?.[0]?.getAttribute('data-type') || '',
                seats: parseInt(sel.selectedOptions?.[0]?.getAttribute('data-seating') || '0', 10) || 0,
                qty: 1,
                transferType: masterType,
                adults: adults,
                child: child,
                infant: infant
            }];
        }

        // Clear transfer if unchecked or no vehicles
        const existingTransferIdx = item.transferId
            ? transferList.findIndex(function (t) { return String(t.id) === String(item.transferId); })
            : transferList.findIndex(function (t) {
                const st = String(t.sourceType || '').toLowerCase();
                return st === side && String(t.sourceId || '') === String(item.id || '');
            });

        if (!transferChecked || !vehicles.length || !portId) {
            if (existingTransferIdx !== -1) transferList.splice(existingTransferIdx, 1);
            arrivalDepartureList[listIndex] = Object.assign({}, arrivalDepartureList[listIndex], {
                hasTransfer: false,
                transferId: null,
                vehicles: [],
                vehicleId: '',
                vehicleName: '',
                vehicleType: '',
                cost: 0, sell: 0,
                adultCost: 0, adultSell: 0,
                childCost: 0, childSell: 0,
                adults: adults, adultsQty: adults,
                child: child, childQty: child,
                infant: infant, infantQty: infant,
                focServiceDiscount: focSvc
            });
            return arrivalDepartureList[listIndex];
        }

        let result = { vehicles: vehicles, totalCost: 0, totalSell: 0, storedCost: 0, storedSell: 0, transferType: masterType };
        if (typeof computeArrDepMultiVehicleTotals === 'function' && portId && actualDestId) {
            try {
                result = await computeArrDepMultiVehicleTotals(side, {
                    vehicles: vehicles,
                    portId: portId,
                    dest: { id: actualDestId, type: destType },
                    transferType: masterType,
                    adults: adults,
                    child: child,
                    dmcId: dmcId
                });
            } catch (e) {
                console.warn('syncStandaloneArrDepVehiclesFromModal price failed', e);
            }
        }
        const syncedVehicles = result.vehicles || vehicles;
        const xferType = result.transferType || (syncedVehicles[0] && (syncedVehicles[0].transferType || syncedVehicles[0].type)) || masterType;
        const vehicleCost = parseFloat(result.storedCost != null ? result.storedCost : result.totalCost) || 0;
        const vehicleSell = parseFloat(result.storedSell != null ? result.storedSell : result.totalSell) || vehicleCost;
        const v0 = syncedVehicles[0] || {};
        const vehicleNameDisplay = (typeof formatMultiVehicleDisplay === 'function')
            ? formatMultiVehicleDisplay({ vehicles: syncedVehicles, vehicleName: v0.vehicleName })
            : (v0.vehicleName || '');

        arrivalDepartureList[listIndex] = Object.assign({}, arrivalDepartureList[listIndex], {
            dateTime: dateTime,
            portId: portId,
            portName: portName,
            city: city || arrivalDepartureList[listIndex].city,
            hasTransfer: true,
            transferType: xferType,
            transferWay: 'one-way',
            vehicleId: v0.vehicleId || '',
            vehicleType: v0.vehicleType || '',
            vehicleName: vehicleNameDisplay,
            vehicles: syncedVehicles,
            vehicleQty: v0.qty || 1,
            transferDestinationId: destId,
            transferDestinationName: destName,
            adults: adults, adultsQty: adults,
            child: child, childQty: child,
            infant: infant, infantQty: infant,
            cost: vehicleCost, sell: vehicleSell,
            adultCost: vehicleCost, adultSell: vehicleSell,
            childCost: vehicleCost, childSell: vehicleSell,
            focServiceDiscount: focSvc
        });
        if (typeof stampArrivalDepartureGeo === 'function') {
            stampArrivalDepartureGeo(arrivalDepartureList[listIndex], city);
        }

        const destination = isArr
            ? (destName ? ('Arrival: ' + portName + ' → ' + destName) : ('Arrival: ' + portName))
            : (destName ? ('Departure: ' + destName + ' → ' + portName) : ('Departure: ' + portName));
        const transferId = existingTransferIdx !== -1
            ? transferList[existingTransferIdx].id
            : (typeof generateId === 'function' ? generateId('transfer') : ('transfer-' + Date.now()));
        const transferPayload = {
            id: transferId,
            transportMode: 'local',
            isStandalone: false,
            sourceType: side,
            sourceId: item.id,
            dateTime: dateTime,
            portName: portName,
            destination: destination,
            city: arrivalDepartureList[listIndex].city || city,
            country: arrivalDepartureList[listIndex].country || '',
            currency: arrivalDepartureList[listIndex].currency || '',
            vehicleId: v0.vehicleId || '',
            vehicleType: v0.vehicleType || '',
            vehicleName: vehicleNameDisplay,
            vehicles: syncedVehicles,
            vehicleQty: v0.qty || 1,
            type: xferType,
            transferType: xferType,
            way: 'one-way',
            hasTransfer: true,
            adults: adults,
            adultsQty: adults,
            child: child,
            childQty: child,
            cost: vehicleCost,
            sell: vehicleSell,
            adultCost: vehicleCost,
            adultSell: vehicleSell,
            focServiceDiscount: focSvc
        };
        if (existingTransferIdx !== -1) {
            transferList[existingTransferIdx] = Object.assign({}, transferList[existingTransferIdx], transferPayload);
        } else {
            transferList.push(transferPayload);
        }
        arrivalDepartureList[listIndex].transferId = transferId;
        return arrivalDepartureList[listIndex];
    }

    /** Persist guide checkbox from Edit Arrival/Departure modal. */
    function syncStandaloneArrDepGuideFromModal(side, listIndex) {
        const isArr = side === 'arrival';
        const item = arrivalDepartureList[listIndex];
        if (!item) return;
        const chk = document.getElementById(isArr ? 'arrivalGuideCheckbox' : 'departureGuideCheckbox');
        const checked = !!(chk && chk.checked);
        const guideSelect = document.getElementById(isArr ? 'arrivalGuide' : 'departureGuide');
        const adultQty = parseInt(document.getElementById(isArr ? 'arrivalGuideAdultQty' : 'departureGuideAdultQty')?.value || '0', 10) || 0;
        const childQty = parseInt(document.getElementById(isArr ? 'arrivalGuideChildQty' : 'departureGuideChildQty')?.value || '0', 10) || 0;
        const focSvc = document.getElementById(isArr ? 'arrivalGuideFocServiceDiscount' : 'departureGuideFocServiceDiscount')?.checked === true;
        const dateTime = document.getElementById(isArr ? 'arrivalDateTime' : 'departureDateTime')?.value || item.dateTime || '';
        const portName = document.getElementById(isArr ? 'arrivalPort' : 'departurePort')?.selectedOptions?.[0]?.text || item.portName || '';

        if (!checked || !guideSelect || !guideSelect.value) {
            removeGuidesLinkedToArrDep(item.id, side, item.guideId);
            arrivalDepartureList[listIndex].guideId = null;
            if (typeof updateGuideTable === 'function') updateGuideTable();
            return;
        }

        const masterGuideId = guideSelect.value;
        const opt = guideSelect.selectedOptions?.[0];
        const guideName = opt?.getAttribute('data-name') || opt?.text || '';
        const languages = opt?.getAttribute('data-languages') || '';
        const priceAttr = opt?.getAttribute('data-twelve-hour-price') || '0';
        const guidePrice = parseFloat(priceAttr) || 0;
        const guideCost = (typeof resolveGuideCostSellFromOption === 'function')
            ? resolveGuideCostSellFromOption(opt, 12).cost
            : guidePrice;

        let existingIdx = item.guideId
            ? guideList.findIndex(function (g) { return String(g.id) === String(item.guideId); })
            : -1;
        if (existingIdx === -1) {
            existingIdx = guideList.findIndex(function (g) {
                return String(g.linkedTo || '').toLowerCase() === side
                    && String(g.arrivalId || g.departureId || g.sourceId || '') === String(item.id || '');
            });
        }

        const base = {
            dateTime: dateTime,
            tourActivity: (isArr ? 'Arrival Guide - ' : 'Departure Guide - ') + portName,
            language: languages || 'N/A',
            guideName: guideName,
            guideId: masterGuideId,
            hours: 12,
            cost: guideCost,
            sell: guidePrice,
            adultsQty: adultQty,
            childQty: childQty,
            focServiceDiscount: focSvc,
            isStandalone: false,
            linkedTo: side,
            city: item.city || ''
        };
        if (isArr) base.arrivalId = item.id;
        else base.departureId = item.id;

        if (existingIdx !== -1) {
            guideList[existingIdx] = Object.assign({}, guideList[existingIdx], base);
            arrivalDepartureList[listIndex].guideId = guideList[existingIdx].id;
        } else {
            const guideEntryId = (typeof generateId === 'function') ? generateId('guide') : ('guide-' + Date.now());
            guideList.push(Object.assign({ id: guideEntryId, supplement: false }, base));
            arrivalDepartureList[listIndex].guideId = guideEntryId;
        }
        if (typeof updateGuideTable === 'function') updateGuideTable();
    }

'''

# Insert helper before saveArrivalDepartureOnly
ANCHOR = "    async function saveArrivalDepartureOnly() {"

# After editing arrival basic fields, call vehicle sync — inject before guide handling
ARRIVAL_GUIDE_MARK = """                // Handle arrival guide - only if arrival is properly booked (has date and port)
                const arrivalGuideChecked = document.getElementById('arrivalGuideCheckbox')?.checked || false;"""

ARRIVAL_GUIDE_REPL = """                // Persist multi-vehicle + transfer from modal into listings
                await syncStandaloneArrDepVehiclesFromModal('arrival', index);

                // Handle arrival guide - only if arrival is properly booked (has date and port)
                syncStandaloneArrDepGuideFromModal('arrival', index);
                /* legacy guide block replaced — keep marker for skip
                const arrivalGuideChecked = document.getElementById('arrivalGuideCheckbox')?.checked || false;"""

# This approach of commenting is messy. Better to replace the whole guide block.

ARRIVAL_GUIDE_OLD_START = """                // Handle arrival guide - only if arrival is properly booked (has date and port)
                const arrivalGuideChecked = document.getElementById('arrivalGuideCheckbox')?.checked || false;
                const arrivalGuideSelect = document.getElementById('arrivalGuide');
                
                // Get adult and child quantities from modal inputs
                const arrivalGuideAdultQty = parseInt(document.getElementById('arrivalGuideAdultQty')?.value || '0') || 0;
                const arrivalGuideChildQty = parseInt(document.getElementById('arrivalGuideChildQty')?.value || '0') || 0;
                const arrivalGuideFocSvc = document.getElementById('arrivalGuideFocServiceDiscount')?.checked === true;
                
                // Find existing guide in guideList
                const existingArrivalGuideIndex = item.guideId ? guideList.findIndex(g => String(g.id) === String(item.guideId)) : -1;
                
                if (arrivalGuideChecked && arrivalGuideSelect && arrivalGuideSelect.value && arrivalDateTime && arrivalPortId) {
"""

# Too fragile. Use simpler approach: inject sync calls and fix remove/populate only.

def patch_file(path: Path):
    text = path.read_text(encoding="utf-8")
    n = 0
    name = path.name
    print(f"=== {name} ===")

    if "function syncStandaloneArrDepVehiclesFromModal" not in text:
        if ANCHOR not in text:
            print("  WARN saveArrivalDepartureOnly not found")
        else:
            text = text.replace(ANCHOR, HELPER + ANCHOR, 1)
            n += 1
            print("  OK helpers inserted")
    else:
        print("  OK helpers already present")

    # Inject vehicle+guide sync before old guide handling (arrival edit)
    needle_arr = "                // Handle arrival guide - only if arrival is properly booked (has date and port)"
    inject_arr = (
        "                // Overwrite transfer/vehicles from multi-vehicle modal UI (listing sync)\n"
        "                await syncStandaloneArrDepVehiclesFromModal('arrival', index);\n"
        "                syncStandaloneArrDepGuideFromModal('arrival', index);\n"
        "                if (false) { // legacy guide block disabled\n"
        "                // Handle arrival guide - only if arrival is properly booked (has date and port)"
    )
    # Count how many times needle appears - we need edit branch only. Both edit and create-new may have it.
    count = text.count(needle_arr)
    print(f"  arrival guide markers: {count}")
    if count >= 1 and "await syncStandaloneArrDepVehiclesFromModal('arrival', index)" not in text:
        # Replace first occurrence only (edit path comes first in saveArrivalDepartureOnly)
        text = text.replace(needle_arr, inject_arr, 1)
        # Close the if(false) before updateGuideTable after arrival guide
        close_arr = "                // Update guide table after adding/removing arrival guide\n                updateGuideTable();\n            }"
        close_arr_new = "                } // end legacy guide block\n                // Update guide table after adding/removing arrival guide\n                updateGuideTable();\n            }"
        if close_arr in text:
            text = text.replace(close_arr, close_arr_new, 1)
            n += 1
            print("  OK arrival edit vehicle+guide sync")
        else:
            print("  WARN could not close arrival legacy block")
            n += 1
    elif "await syncStandaloneArrDepVehiclesFromModal('arrival', index)" in text:
        print("  OK arrival sync already present")

    needle_dep = "                // Handle departure guide"
    # Find exact - create has "// Handle departure guide" variations
    # Looking at create line 15234 area - need exact string
    for dep_marker in [
        "                // Handle departure guide - only if departure is properly booked",
        "                // Handle departure guide",
    ]:
        if dep_marker in text and "await syncStandaloneArrDepVehiclesFromModal('departure', index)" not in text:
            inject_dep = (
                "                await syncStandaloneArrDepVehiclesFromModal('departure', index);\n"
                "                syncStandaloneArrDepGuideFromModal('departure', index);\n"
                "                if (false) { // legacy guide block disabled\n"
                + dep_marker
            )
            text = text.replace(dep_marker, inject_dep, 1)
            close_dep = "                // Update guide table after adding/removing departure guide\n                updateGuideTable();\n            }"
            close_dep_new = "                } // end legacy guide block\n                // Update guide table after adding/removing departure guide\n                updateGuideTable();\n            }"
            if close_dep in text:
                text = text.replace(close_dep, close_dep_new, 1)
                n += 1
                print(f"  OK departure edit vehicle+guide sync ({dep_marker[:40]}...)")
            else:
                print("  WARN could not close departure legacy block")
                n += 1
            break
    else:
        if "await syncStandaloneArrDepVehiclesFromModal('departure', index)" in text:
            print("  OK departure sync already present")
        else:
            print("  WARN departure guide marker not found")

    # Fix guide populate: only use data.guideId (no orphan fallbacks)
    # CREATE arrival populate block
    old_arr_pop = """                    let arrGuideEntry = data.guideId
                        ? guideList.find(g => String(g.id) === String(data.guideId))
                        : null;
                    if (!arrGuideEntry) {
                        arrGuideEntry = guideList.find(g =>
                            String(g.linkedTo || '').toLowerCase() === 'arrival' &&
                            String(g.arrivalId || '') === String(data.id || '')
                        );
                    }
                    if (!arrGuideEntry) {
                        arrGuideEntry = guideList.find(g => String(g.linkedTo || '').toLowerCase() === 'arrival');
                    }
                    if (arrGuideEntry) {
                        data.guideId = arrGuideEntry.id;
                    }"""

    new_arr_pop = """                    // Only restore guide if this A/D entry still has guideId (respect user deselect)
                    let arrGuideEntry = data.guideId
                        ? guideList.find(g => String(g.id) === String(data.guideId))
                        : null;
                    if (!arrGuideEntry && data.guideId) {
                        arrGuideEntry = guideList.find(g =>
                            String(g.linkedTo || '').toLowerCase() === 'arrival' &&
                            String(g.arrivalId || g.sourceId || '') === String(data.id || '')
                        );
                    }"""

    old_dep_pop = """                    let depGuideEntry = data.guideId
                        ? guideList.find(g => String(g.id) === String(data.guideId))
                        : null;
                    if (!depGuideEntry) {
                        depGuideEntry = guideList.find(g =>
                            String(g.linkedTo || '').toLowerCase() === 'departure' &&
                            String(g.arrivalId || '') === String(data.id || '')
                        );
                    }
                    if (!depGuideEntry) {
                        depGuideEntry = guideList.find(g => String(g.linkedTo || '').toLowerCase() === 'departure');
                    }
                    if (depGuideEntry) {
                        data.guideId = depGuideEntry.id;
                    }"""

    new_dep_pop = """                    // Only restore guide if this A/D entry still has guideId (respect user deselect)
                    let depGuideEntry = data.guideId
                        ? guideList.find(g => String(g.id) === String(data.guideId))
                        : null;
                    if (!depGuideEntry && data.guideId) {
                        depGuideEntry = guideList.find(g =>
                            String(g.linkedTo || '').toLowerCase() === 'departure' &&
                            String(g.departureId || g.arrivalId || g.sourceId || '') === String(data.id || '')
                        );
                    }"""

    # Edit template has slightly different populate (city fallback)
    old_arr_pop_edit = """                    let arrGuideEntry = data.guideId
                        ? guideList.find(g => String(g.id) === String(data.guideId))
                        : null;
                    if (!arrGuideEntry) {
                        arrGuideEntry = guideList.find(g =>
                            String(g.linkedTo || '').toLowerCase() === 'arrival' &&
                            String(g.arrivalId || g.sourceId || '') === String(data.id || '')
                        );
                    }
                    if (!arrGuideEntry && cityToRestore) {
                        arrGuideEntry = guideList.find(g =>
                            String(g.linkedTo || '').toLowerCase() === 'arrival' &&
                            String(g.city || '').toLowerCase() === cityToRestore.toLowerCase()
                        );
                    }
                    if (arrGuideEntry) {
                        data.guideId = arrGuideEntry.id;
                    }"""

    if old_arr_pop in text:
        text = text.replace(old_arr_pop, new_arr_pop, 1)
        n += 1
        print("  OK arrival guide populate (no orphan fallback)")
    elif old_arr_pop_edit in text:
        text = text.replace(old_arr_pop_edit, new_arr_pop, 1)
        n += 1
        print("  OK arrival guide populate edit-style")
    else:
        print("  WARN arrival guide populate block not found")

    if old_dep_pop in text:
        text = text.replace(old_dep_pop, new_dep_pop, 1)
        n += 1
        print("  OK departure guide populate")
    else:
        # try edit variant
        old_dep_pop_edit = """                    let depGuideEntry = data.guideId
                        ? guideList.find(g => String(g.id) === String(data.guideId))
                        : null;
                    if (!depGuideEntry) {
                        depGuideEntry = guideList.find(g =>
                            String(g.linkedTo || '').toLowerCase() === 'departure' &&
                            String(g.departureId || g.sourceId || g.arrivalId || '') === String(data.id || '')
                        );
                    }
                    if (!depGuideEntry && cityToRestore) {
                        depGuideEntry = guideList.find(g =>
                            String(g.linkedTo || '').toLowerCase() === 'departure' &&
                            String(g.city || '').toLowerCase() === cityToRestore.toLowerCase()
                        );
                    }
                    if (depGuideEntry) {
                        data.guideId = depGuideEntry.id;
                    }"""
        if old_dep_pop_edit in text:
            text = text.replace(old_dep_pop_edit, new_dep_pop, 1)
            n += 1
            print("  OK departure guide populate edit-style")
        else:
            print("  WARN departure guide populate block not found")

    # Populate multi vehicles when opening edit modal
    veh_inject_marker = "                    document.getElementById('arrivalAdults').value = data.adultsQty || 2;"
    veh_inject = """                    // Restore multi-vehicle rows from saved entry / linked transfer
                    (function () {
                        const list = (typeof normalizeArrDepVehiclesList === 'function')
                            ? normalizeArrDepVehiclesList(data.vehicles && data.vehicles.length ? data : (linked || data))
                            : (Array.isArray(data.vehicles) ? data.vehicles : (linked && Array.isArray(linked.vehicles) ? linked.vehicles : []));
                        const rows = list.length ? list : (data.vehicleId ? [{ vehicleId: data.vehicleId, qty: data.vehicleQty || 1, transferType: data.transferType, adults: data.adultsQty, child: data.childQty, infant: data.infantQty }] : []);
                        if (typeof ensureArrDepVehicleRows === 'function') {
                            ensureArrDepVehicleRows('arrival', rows.length ? rows : [{ vehicleId: '', qty: 1 }]);
                            if (typeof filterArrivalVehiclesByServiceType === 'function') filterArrivalVehiclesByServiceType();
                        }
                    })();
                    document.getElementById('arrivalAdults').value = data.adultsQty || 2;"""

    if "ensureArrDepVehicleRows('arrival', rows.length" not in text and veh_inject_marker in text:
        text = text.replace(veh_inject_marker, veh_inject, 1)
        n += 1
        print("  OK arrival vehicle rows restore on edit open")
    else:
        print("  SKIP/WARN arrival vehicle restore")

    veh_dep_marker = "                    document.getElementById('departureAdults').value = data.adultsQty || 2;"
    # might use adults differently
    for m in [
        "                    document.getElementById('departureAdults').value = data.adultsQty || 2;",
        "                    document.getElementById('departureAdults').value = data.adultsQty || data.adults || 2;",
    ]:
        if m in text and "ensureArrDepVehicleRows('departure', rows.length" not in text:
            veh_dep = """                    (function () {
                        const list = (typeof normalizeArrDepVehiclesList === 'function')
                            ? normalizeArrDepVehiclesList(data.vehicles && data.vehicles.length ? data : (linked || data))
                            : (Array.isArray(data.vehicles) ? data.vehicles : (linked && Array.isArray(linked.vehicles) ? linked.vehicles : []));
                        const rows = list.length ? list : (data.vehicleId ? [{ vehicleId: data.vehicleId, qty: data.vehicleQty || 1, transferType: data.transferType, adults: data.adultsQty, child: data.childQty, infant: data.infantQty }] : []);
                        if (typeof ensureArrDepVehicleRows === 'function') {
                            ensureArrDepVehicleRows('departure', rows.length ? rows : [{ vehicleId: '', qty: 1 }]);
                            if (typeof filterDepartureVehiclesByServiceType === 'function') filterDepartureVehiclesByServiceType();
                        }
                    })();
""" + m
            text = text.replace(m, veh_dep, 1)
            n += 1
            print("  OK departure vehicle rows restore on edit open")
            break
    else:
        print("  SKIP/WARN departure vehicle restore")

    # After save editing block, ensure tables refresh
    if "window.editingArrivalDepartureIndex = null;" in text:
        refresh = """            if (typeof updateArrivalDepartureTable === 'function') updateArrivalDepartureTable();
            if (typeof updateTransferTable === 'function') updateTransferTable();
            if (typeof updateGuideTable === 'function') updateGuideTable();
            if (typeof recalculateTotals === 'function') recalculateTotals();
            window.editingArrivalDepartureIndex = null;"""
        # Only first occurrence inside saveArrivalDepartureOnly edit branch
        marker = "            window.editingArrivalDepartureIndex = null;\n        } else {"
        if marker in text and "updateArrivalDepartureTable === 'function') updateArrivalDepartureTable();\n            if (typeof updateTransferTable" not in text.split("async function saveArrivalDepartureOnly")[1][:25000]:
            text = text.replace(marker, refresh + "\n        } else {", 1)
            n += 1
            print("  OK listing refresh after edit save")
        else:
            print("  SKIP listing refresh (may already exist)")

    path.write_text(text, encoding="utf-8")
    print(f"  TOTAL ~{n}")


for f in [
    Path(r"c:\xampp\htdocs\Azure_new_files\resources\views\enquiryform_pro\create.blade.php"),
    Path(r"c:\xampp\htdocs\Azure_new_files\resources\views\enquiryform_pro\edit.blade.php"),
]:
    patch_file(f)
