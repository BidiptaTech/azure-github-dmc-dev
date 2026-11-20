# -*- coding: utf-8 -*-
from pathlib import Path

FILES = [
    Path(r"c:\xampp\htdocs\Azure_new_files\resources\views\enquiryform_pro\create.blade.php"),
    Path(r"c:\xampp\htdocs\Azure_new_files\resources\views\enquiryform_pro\edit.blade.php"),
]


def must_replace(text, old, new, label, count=None):
    c = text.count(old)
    if not c:
        print(f"  WARN {label} not found")
        return text, 0
    text = text.replace(old, new, count if count is not None else c)
    applied = min(c, count) if count else c
    print(f"  OK {label} x{applied}")
    return text, applied


OLD_APPLY_LOCAL = """    function applyLocalMultiVehicleTotalsToForm() {
        // Recalc from current unit inputs (user edits)
        const rows = collectArrDepVehicleRows('local');
        const type = document.getElementById('localType')?.value || 'S';
        const isShared = type === 'S' || String(type).toLowerCase() === 'shared';
        const tourPax = getLocalTourPax();
        let totalCost = 0, totalSell = 0, storedCost = 0, storedSell = 0;
        const enriched = [];
        rows.forEach(function (r) {
            if (!r.vehicleId || !r.row) return;
            const t = recalcArrDepVehicleRowTotals('local', r.row);
            totalCost += t.lineCost;
            totalSell += t.lineSell;
            if (isShared) {
                storedCost += t.unitCost * t.qty;
                storedSell += t.unitSell * t.qty;
            } else {
                storedCost += t.lineCost;
                storedSell += t.lineSell;
            }
            enriched.push({
                vehicleId: r.vehicleId,
                vehicleName: r.vehicleName,
                vehicleType: r.vehicleType,
                seats: r.seats,
                qty: t.qty,
                unitCost: t.unitCost,
                unitSell: t.unitSell,
                lineCost: t.lineCost,
                lineSell: t.lineSell
            });
        });
        window._localMultiVehicleResult = { vehicles: enriched, totalCost, totalSell, storedCost, storedSell };
        const master = document.getElementById('localVehicleType');
        if (master) {
            master.dataset.multiTotalCost = String(totalCost || 0);
            master.dataset.multiTotalSell = String(totalSell || 0);
            master.dataset.multiStoredCost = String(storedCost || 0);
            master.dataset.multiStoredSell = String(storedSell || 0);
        }
        const summary = document.getElementById('localVehiclePriceSummary');
        if (summary) {
            summary.style.display = '';
            summary.innerHTML = '<i class="ri-coin-line me-1"></i><strong>Zone price (Cost / Sell):</strong> '
                + '<span class="text-primary fw-bold ms-1">' + totalCost.toFixed(2) + ' / ' + totalSell.toFixed(2) + '</span>'
                + ' <span class="text-muted ms-1">(' + enriched.length + ' vehicle' + (enriched.length > 1 ? 's' : '')
                + (isShared ? (', ×' + tourPax + ' pax') : '') + ')</span>';
        }"""

NEW_APPLY_LOCAL = """    function applyLocalMultiVehicleTotalsToForm() {
        // Recalc from current unit inputs (user edits) — per-row Shared/Private
        const rows = collectArrDepVehicleRows('local');
        if (typeof syncMasterTransferTypeFromRows === 'function') syncMasterTransferTypeFromRows('local');
        const tourPax = getLocalTourPax();
        let totalCost = 0, totalSell = 0, storedCost = 0, storedSell = 0;
        let anyShared = false, anyPrivate = false;
        const enriched = [];
        rows.forEach(function (r) {
            if (!r.vehicleId || !r.row) return;
            const t = recalcArrDepVehicleRowTotals('local', r.row);
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
            enriched.push({
                vehicleId: r.vehicleId,
                vehicleName: r.vehicleName,
                vehicleType: r.vehicleType,
                seats: r.seats,
                qty: t.qty,
                transferType: t.transferType || r.transferType || 'S',
                type: t.transferType || r.transferType || 'S',
                unitCost: t.unitCost,
                unitSell: t.unitSell,
                lineCost: t.lineCost,
                lineSell: t.lineSell
            });
        });
        window._localMultiVehicleResult = { vehicles: enriched, totalCost, totalSell, storedCost, storedSell };
        const master = document.getElementById('localVehicleType');
        if (master) {
            master.dataset.multiTotalCost = String(totalCost || 0);
            master.dataset.multiTotalSell = String(totalSell || 0);
            master.dataset.multiStoredCost = String(storedCost || 0);
            master.dataset.multiStoredSell = String(storedSell || 0);
        }
        const summary = document.getElementById('localVehiclePriceSummary');
        if (summary) {
            let suffix = '';
            if (anyShared && anyPrivate) suffix = ', mixed';
            else if (anyShared) suffix = ', ×' + tourPax + ' pax';
            summary.style.display = '';
            summary.innerHTML = '<i class="ri-coin-line me-1"></i><strong>Zone price (Cost / Sell):</strong> '
                + '<span class="text-primary fw-bold ms-1">' + totalCost.toFixed(2) + ' / ' + totalSell.toFixed(2) + '</span>'
                + ' <span class="text-muted ms-1">(' + enriched.length + ' vehicle' + (enriched.length > 1 ? 's' : '')
                + suffix + ')</span>';
        }"""


# Replace the inner forEach in filterArrival to use per-row applyArrDepVehicleFilterToSelect
# Simpler approach: after building selects, for row selects call applyArrDepVehicleFilterToSelect

ARR_FILTER_HOOK_OLD = """        selects.forEach(function (vehicleSelect) {
            if (!vehicleSelect || !vehicleSelect.options) return;
            Array.from(vehicleSelect.options).forEach(option => {
                if (option.value === '') {
                    option.disabled = false;
                    option.style.display = '';
                    option.hidden = false;
                    return;
                }
                const sharable = parseInt(option.getAttribute('data-sharable') || '1', 10);
                let shouldShow = false;
                // REMOVED pax/seat capacity filter — seats used only for coverage
                if (serviceType === 'S') {
                    if (sharable === 2 || sharable === 3) shouldShow = true;
                } else if (serviceType === 'P') {
                    if (sharable === 1 || sharable === 3) shouldShow = true;
                }
                if (shouldShow && typeof vehicleOptionMatchesCity === 'function'
                    && !vehicleOptionMatchesCity(option, city)) {
                    shouldShow = false;
                }
                option.disabled = !shouldShow;
                option.style.display = shouldShow ? '' : 'none';
                option.hidden = !shouldShow;
            });
            if (typeof hideEmptyVehicleOptgroups === 'function') {
                hideEmptyVehicleOptgroups(vehicleSelect);
            }
        });

        // Build matches from first row (or master) for auto-pick
        const primarySelect = rowSelects[0] || master;"""

ARR_FILTER_HOOK_NEW = """        // Per-row Type filter (Shared/Private on each vehicle row)
        if (typeof applyArrDepVehicleFilterToSelect === 'function') {
            rowSelects.forEach(function (vehicleSelect) {
                applyArrDepVehicleFilterToSelect('arrival', vehicleSelect, city);
            });
            if (master) applyArrDepVehicleFilterToSelect('arrival', master, city);
        } else {
            selects.forEach(function (vehicleSelect) {
                if (!vehicleSelect || !vehicleSelect.options) return;
                Array.from(vehicleSelect.options).forEach(option => {
                    if (option.value === '') {
                        option.disabled = false;
                        option.style.display = '';
                        option.hidden = false;
                        return;
                    }
                    const sharable = parseInt(option.getAttribute('data-sharable') || '1', 10);
                    let shouldShow = false;
                    if (serviceType === 'S') {
                        if (sharable === 2 || sharable === 3) shouldShow = true;
                    } else if (serviceType === 'P') {
                        if (sharable === 1 || sharable === 3) shouldShow = true;
                    }
                    if (shouldShow && typeof vehicleOptionMatchesCity === 'function'
                        && !vehicleOptionMatchesCity(option, city)) {
                        shouldShow = false;
                    }
                    option.disabled = !shouldShow;
                    option.style.display = shouldShow ? '' : 'none';
                    option.hidden = !shouldShow;
                });
                if (typeof hideEmptyVehicleOptgroups === 'function') {
                    hideEmptyVehicleOptgroups(vehicleSelect);
                }
            });
        }

        // Build matches from first row (or master) for auto-pick
        const primarySelect = rowSelects[0] || master;"""


DEP_FILTER_HOOK_OLD = None  # find similar in departure


def patch_dep_filter(text):
    # Departure may use slightly different comments
    marker = "function filterDepartureVehiclesByServiceType"
    idx = text.find(marker)
    if idx < 0:
        print("  WARN filterDeparture not found")
        return text, 0
    # Find the selects.forEach after this function start within next 80 lines worth
    chunk = text[idx:idx + 3500]
    old_snip = """        selects.forEach(function (vehicleSelect) {
            if (!vehicleSelect || !vehicleSelect.options) return;
            Array.from(vehicleSelect.options).forEach(option => {
                if (option.value === '') {
                    option.disabled = false;
                    option.style.display = '';
                    option.hidden = false;
                    return;
                }
                const sharable = parseInt(option.getAttribute('data-sharable') || '1', 10);
                let shouldShow = false;"""
    if old_snip not in chunk:
        # try without arrow
        print("  WARN departure selects.forEach snip not found")
        return text, 0

    # Replace only within this function: find end of forEach block until primarySelect
    start = text.find(old_snip, idx)
    end_marker = "        // Build matches from first row (or master) for auto-pick\n        const primarySelect = rowSelects[0] || master;"
    # departure might say slightly different
    end = text.find("const primarySelect = rowSelects[0] || master;", start)
    if end < 0:
        print("  WARN departure primarySelect not found")
        return text, 0
    # include comment before primarySelect if present
    pre = text.rfind("\n", 0, end)
    # find line start of primarySelect block
    block_end = end
    # go back to include comment
    comment = text.rfind("// Build matches", start, end)
    if comment > 0:
        block_end = comment

    # Find the start of selects.forEach - we already have start
    # Find end: just before // Build matches or primarySelect
    if comment > 0:
        old_block = text[start:comment]
    else:
        # find closing of forEach - look for `        });\n\n        const primary`
        end2 = text.find("        const primarySelect = rowSelects[0] || master;", start)
        old_block = text[start:end2]

    new_block = """        // Per-row Type filter (Shared/Private on each vehicle row)
        if (typeof applyArrDepVehicleFilterToSelect === 'function') {
            rowSelects.forEach(function (vehicleSelect) {
                applyArrDepVehicleFilterToSelect('departure', vehicleSelect, city);
            });
            if (master) applyArrDepVehicleFilterToSelect('departure', master, city);
        } else {
""" + old_block + """        }

"""
    # This is getting messy. Simpler: replace filterDeparture's selects.forEach with applyArrDep like arrival.
    return text, 0


def main():
    for path in FILES:
        print(f"=== {path.name} ===")
        text = path.read_text(encoding="utf-8")
        total = 0
        text, a = must_replace(text, OLD_APPLY_LOCAL, NEW_APPLY_LOCAL, "applyLocalMultiVehicleTotalsToForm", 1)
        total += a
        text, a = must_replace(text, ARR_FILTER_HOOK_OLD, ARR_FILTER_HOOK_NEW, "filterArrival per-row", 1)
        total += a

        # Departure: same pattern but side=departure — search for unique arrival-replaced already so only dep remains
        # After arrival patch, remaining identical selects.forEach with REMOVED comment is departure
        # Check if ARR_FILTER_HOOK_OLD still exists (departure copy)
        if ARR_FILTER_HOOK_OLD in text:
            dep_new = ARR_FILTER_HOOK_NEW.replace("'arrival'", "'departure'")
            text, a = must_replace(text, ARR_FILTER_HOOK_OLD, dep_new, "filterDeparture per-row", 1)
            total += a
        else:
            # try departure-specific without REMOVED comment
            dep_old2 = ARR_FILTER_HOOK_OLD.replace(
                "// REMOVED pax/seat capacity filter — seats used only for coverage\n                ",
                ""
            )
            if dep_old2 in text:
                dep_new = ARR_FILTER_HOOK_NEW.replace("'arrival'", "'departure'")
                # also remove that comment from new else branch already
                text, a = must_replace(text, dep_old2, dep_new, "filterDeparture per-row alt", 1)
                total += a
            else:
                print("  WARN filterDeparture per-row not found")

        # Local filter: use applyArrDepVehicleFilterToSelect per row
        local_old = """        selects.forEach(function (vehicleSelect) {
            if (!vehicleSelect || !vehicleSelect.options) return;
            Array.from(vehicleSelect.options).forEach(function (option) {
                if (option.value === '') {
                    option.disabled = false;
                    option.hidden = false;
                    option.style.display = '';
                    return;
                }
                const sharable = parseInt(option.getAttribute('data-sharable') || '3', 10);
                let shouldShow = false;
                // No pax/seat filtering — seats used only for coverage
                if (serviceType === 'S') {
                    if (sharable === 2 || sharable === 3) shouldShow = true;
                } else if (serviceType === 'P') {
                    if (sharable === 1 || sharable === 3) shouldShow = true;
                }
                if (shouldShow && serviceCity && typeof vehicleOptionMatchesCity === 'function'
                    && !vehicleOptionMatchesCity(option, serviceCity)) {
                    shouldShow = false;
                }
                option.disabled = !shouldShow;
                option.hidden = !shouldShow;
                option.style.display = shouldShow ? '' : 'none';
            });
            if (typeof hideEmptyVehicleOptgroups === 'function') hideEmptyVehicleOptgroups(vehicleSelect);
        });"""

        local_new = """        if (typeof applyArrDepVehicleFilterToSelect === 'function') {
            rowSelects.forEach(function (vehicleSelect) {
                applyArrDepVehicleFilterToSelect('local', vehicleSelect, serviceCity);
            });
            if (master) applyArrDepVehicleFilterToSelect('local', master, serviceCity);
        } else {
            selects.forEach(function (vehicleSelect) {
                if (!vehicleSelect || !vehicleSelect.options) return;
                Array.from(vehicleSelect.options).forEach(function (option) {
                    if (option.value === '') {
                        option.disabled = false;
                        option.hidden = false;
                        option.style.display = '';
                        return;
                    }
                    const sharable = parseInt(option.getAttribute('data-sharable') || '3', 10);
                    let shouldShow = false;
                    if (serviceType === 'S') {
                        if (sharable === 2 || sharable === 3) shouldShow = true;
                    } else if (serviceType === 'P') {
                        if (sharable === 1 || sharable === 3) shouldShow = true;
                    }
                    if (shouldShow && serviceCity && typeof vehicleOptionMatchesCity === 'function'
                        && !vehicleOptionMatchesCity(option, serviceCity)) {
                        shouldShow = false;
                    }
                    option.disabled = !shouldShow;
                    option.hidden = !shouldShow;
                    option.style.display = shouldShow ? '' : 'none';
                });
                if (typeof hideEmptyVehicleOptgroups === 'function') hideEmptyVehicleOptgroups(vehicleSelect);
            });
        }"""

        text, a = must_replace(text, local_old, local_new, "filterLocal per-row", 1)
        total += a

        # When saving local transfer, include transferType per vehicle - check addLocalTransfer payload
        path.write_text(text, encoding="utf-8")
        print(f"  TOTAL {total}")


if __name__ == "__main__":
    main()
