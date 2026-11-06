{{-- Pro DMC markup price-breakdown (Lite parity via StpLiteDmcMarkup) --}}
<script src="{{ asset('js/single-tour-package/lite/dmc-markup.js') }}?v={{ filemtime(public_path('js/single-tour-package/lite/dmc-markup.js')) }}"></script>
<style>
    .ep-mk-summary-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 4px 0; font-size: 12px; gap: 12px;
    }
    .ep-mk-summary-row.is-markup .ep-mk-amt { color: #7c3aed; font-weight: 700; }
    .ep-mk-summary-row.is-total {
        border-top: 1px dashed #93c5fd; margin-top: 6px; padding-top: 8px;
        font-weight: 700;
    }
    .ep-mk-summary-row.is-total .ep-mk-amt { color: #15803d; font-size: 14px; }
    .ep-mk-line {
        display: flex; justify-content: space-between; gap: 10px;
        padding: 3px 0; font-size: 12px; color: #334155;
    }
    .ep-mk-line strong { color: #0f172a; }
    .ep-price-details-btn { font-size: 10px !important; padding: 1px 6px !important; line-height: 1.2 !important; }
</style>
<div class="modal fade" id="enquiryProServicePriceBreakdownModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content ep-price-details-modal">
            <div class="modal-header ep-price-details-header py-2">
                <h6 class="modal-title mb-0 fw-bold" id="enquiryProServicePriceBreakdownTitle" style="font-size: 14px; color: #1e293b;">
                    <i class="ri-file-list-3-line me-1" style="color:#2563eb;"></i>Price breakdown
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="enquiryProServicePriceBreakdownBody"></div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    'use strict';

    function money(cur, amount) {
        if (window.StpLiteDmcMarkup && typeof window.StpLiteDmcMarkup.moneyTxt === 'function') {
            return window.StpLiteDmcMarkup.moneyTxt(cur, amount);
        }
        return String(cur || 'SGD') + ' ' + (Number(amount) || 0).toFixed(2);
    }

    function esc(v) {
        if (typeof enquiryProEscapeHtml === 'function') return enquiryProEscapeHtml(v);
        return String(v == null ? '' : v)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function resolveCurrency(item) {
        if (item && item.currency) return String(item.currency).toUpperCase();
        if (typeof resolveServiceCurrency === 'function') {
            const c = resolveServiceCurrency(item || {});
            if (c) return String(c).toUpperCase();
        }
        if (typeof getEnquiryProPrimaryCurrency === 'function') {
            return String(getEnquiryProPrimaryCurrency() || 'SGD').toUpperCase();
        }
        return 'SGD';
    }

    /** Apply DMC profile markup (hotel | other) — Lite StpLiteDmcMarkup parity. */
    window.enquiryProApplyDmcMarkup = function (basePrice, kind) {
        kind = String(kind || 'other').toLowerCase() === 'hotel' ? 'hotel' : 'other';
        if (window.StpLiteDmcMarkup && typeof window.StpLiteDmcMarkup.apply === 'function') {
            return window.StpLiteDmcMarkup.apply(basePrice, kind);
        }
        const profile = window.enquiryProDmcProfileMarkup
            || (window.STP_LITE_CONFIG && window.STP_LITE_CONFIG.dmcProfileMarkup)
            || {};
        const rule = profile[kind] || {};
        let type = String(rule.markup_type || 'percentage').toLowerCase();
        if (type === 'fixed') type = 'flat';
        if (type !== 'flat' && type !== 'percentage') type = 'percentage';
        const val = Math.max(0, Number(rule.markup_value) || 0);
        const base = Math.max(0, Number(basePrice) || 0);
        let amount = 0;
        if (val > 0) {
            amount = type === 'flat'
                ? Math.round(val * 100) / 100
                : Math.round((base * val / 100) * 100) / 100;
        }
        return {
            base_price: Math.round(base * 100) / 100,
            markup_amount: amount,
            price: Math.round((base + amount) * 100) / 100,
            markup_type: type,
            markup_value: val,
            markup_kind: kind,
            dmc_id: profile.dmc_id || null
        };
    };

    /**
     * Peel a final (already marked) amount back to base + markup for display.
     * See docs/enquiry-pro-cost-sell-markup-price.md
     */
    window.enquiryProSplitDmcMarkupFromFinal = function (finalPrice, kind) {
        kind = String(kind || 'other').toLowerCase() === 'hotel' ? 'hotel' : 'other';
        if (window.StpLiteDmcMarkup && typeof window.StpLiteDmcMarkup.splitFromFinal === 'function') {
            return window.StpLiteDmcMarkup.splitFromFinal(finalPrice, kind);
        }
        const applied = window.enquiryProApplyDmcMarkup(0, kind);
        const finalAmt = Math.max(0, Number(finalPrice) || 0);
        const val = Math.max(0, Number(applied.markup_value) || 0);
        if (val <= 0 || finalAmt <= 0) {
            return Object.assign({}, applied, {
                base_price: Math.round(finalAmt * 100) / 100,
                markup_amount: 0,
                price: Math.round(finalAmt * 100) / 100
            });
        }
        let base = finalAmt;
        let amount = 0;
        if (String(applied.markup_type || '').toLowerCase() === 'flat') {
            amount = Math.min(finalAmt, val);
            base = Math.max(0, finalAmt - amount);
        } else {
            base = finalAmt / (1 + (val / 100));
            amount = Math.max(0, finalAmt - base);
        }
        return {
            base_price: Math.round(base * 100) / 100,
            markup_amount: Math.round(amount * 100) / 100,
            price: Math.round(finalAmt * 100) / 100,
            markup_type: applied.markup_type,
            markup_value: applied.markup_value,
            markup_kind: kind,
            dmc_id: applied.dmc_id
        };
    };

    /**
     * Display pair: Sell = base (before DMC markup), Markup Price = after markup.
     * Prefer stored base_grand_total / dmc_profile_markup when present.
     */
    window.enquiryProMarkupPriceFromSell = function (sellBase, kind) {
        const applied = window.enquiryProApplyDmcMarkup(sellBase, kind);
        return {
            base: applied.base_price,
            markup_amount: applied.markup_amount,
            markup_price: applied.price,
            applied: applied
        };
    };

    /**
     * Resolve the BASE sell for listing/footer.
     * Prefer stored base only when the passed value is missing, equals that base, or is the
     * already-marked final (edit JSON). Never replace a smaller per-night/unit sell with a
     * stay-level base_grand_total (edit hotel listing bug: Avg Cost 380 / Sell wrongly 2084).
     */
    window.enquiryProResolveDisplayBaseSell = function (value, kind, storedMeta) {
        const meta = storedMeta || {};
        const raw = Math.max(0, parseFloat(value) || 0);
        const fromMeta = parseFloat(
            meta.base_grand_total != null ? meta.base_grand_total
                : (meta.dmc_profile_markup && meta.dmc_profile_markup.base_price)
        );
        const marked = parseFloat(
            meta.dmc_profile_markup && meta.dmc_profile_markup.price != null
                ? meta.dmc_profile_markup.price
                : NaN
        );
        if (Number.isFinite(fromMeta) && fromMeta > 0) {
            const valueLooksMarked = Number.isFinite(marked) && marked > 0 && Math.abs(raw - marked) < 0.05;
            const valueEqualsBase = Math.abs(raw - fromMeta) < 0.05;
            if (!(raw > 0) || valueLooksMarked || valueEqualsBase) {
                return Math.round(fromMeta * 100) / 100;
            }
            // Stay/line grand total must not replace per-night (or per-unit) sell
            if (fromMeta > raw * 1.5 + 0.01) {
                // keep raw below
            } else {
                return Math.round(fromMeta * 100) / 100;
            }
        }
        // If caller flags value as already marked, peel back to base
        if (meta.valueIncludesMarkup === true && raw > 0) {
            return window.enquiryProSplitDmcMarkupFromFinal(raw, kind).base_price;
        }
        return Math.round(raw * 100) / 100;
    };

    /** Format helper for Markup Price cells. */
    window.enquiryProFormatMarkupPrice = function (sellBase, kind) {
        return window.enquiryProMarkupPriceFromSell(sellBase, kind).markup_price;
    };

    /** Readonly Markup Price <td> for service listing tables (create + edit). */
    window.enquiryProMarkupPriceTdHtml = function (sellBase, kind) {
        const mk = window.enquiryProFormatMarkupPrice
            ? Number(window.enquiryProFormatMarkupPrice(sellBase, kind || 'other'))
            : (parseFloat(sellBase) || 0);
        const val = (Number.isFinite(mk) ? mk : 0).toFixed(2);
        return '<td><input type="text" class="ep-markup-price-field" value="' + val
            + '" readonly style="background-color: #f3e8ff; color: #6b21a8;" title="Sell after DMC markup"></td>';
    };

    /** Copy prior-save base/markup meta onto a transform payload (edit re-save anti-double-markup). */
    window.enquiryProCopyStoredMarkupMeta = function (target, source) {
        if (!target || !source || typeof source !== 'object') return target;
        const fromMeta = parseFloat(
            source.base_grand_total != null ? source.base_grand_total
                : (source.dmc_profile_markup && source.dmc_profile_markup.base_price)
        );
        if (Number.isFinite(fromMeta) && fromMeta > 0) {
            target.base_grand_total = fromMeta;
        }
        if (source.dmc_profile_markup && typeof source.dmc_profile_markup === 'object') {
            target.dmc_profile_markup = source.dmc_profile_markup;
        }
        return target;
    };

    /**
     * Stamp DMC "other" markup onto service order JSON (sell total only).
     * baseTotal must be BEFORE markup. If edit re-save passes an already-marked total that
     * matches stored dmc_profile_markup.price, peel via base_grand_total first.
     */
    window.enquiryProAttachOtherServiceMarkup = function (data, baseTotal) {
        data = data || {};
        let base = baseTotal != null ? Number(baseTotal) : Number(data.totalPrice != null ? data.totalPrice : data.sell);
        if (!Number.isFinite(base) || base < 0) base = 0;
        const storedBase = parseFloat(
            data.base_grand_total != null ? data.base_grand_total
                : (data.dmc_profile_markup && data.dmc_profile_markup.base_price)
        );
        const storedMarked = parseFloat(
            data.dmc_profile_markup && data.dmc_profile_markup.price != null
                ? data.dmc_profile_markup.price
                : NaN
        );
        if (Number.isFinite(storedBase) && storedBase > 0
            && Number.isFinite(storedMarked) && storedMarked > 0
            && Math.abs(base - storedMarked) < 0.05) {
            base = storedBase;
        } else if (Number.isFinite(storedBase) && storedBase > 0
            && Math.abs(base - storedBase) < 0.05) {
            base = storedBase;
        }
        const applied = window.enquiryProApplyDmcMarkup(base, 'other');
        data.totalPrice = applied.price;
        if (data.price != null && data.price !== '') data.price = applied.price;
        data.grand_total = applied.price;
        data.base_grand_total = applied.base_price;
        data.dmc_profile_markup = applied;
        return applied;
    };

    /**
     * Stamp DMC markup onto nested transfer_options / transferInfo sell fields.
     * Base = resolved line sell (Shared×pax / Private×qty / MV lineSell) — never re-mark if already stamped.
     */
    window.enquiryProStampTransferOptionsMarkup = function (transferOptions, linkedTransfer) {
        if (!transferOptions || typeof transferOptions !== 'object') return transferOptions;

        const round2 = function (n) { return Math.round((Number(n) || 0) * 100) / 100; };
        const vehicleBaseSell = function (v) {
            if (!v || typeof v !== 'object') return 0;
            const stored = parseFloat(
                (v.base_grand_total != null ? v.base_grand_total : null)
                || (v.dmc_profile_markup && v.dmc_profile_markup.base_price)
                || 0
            );
            if (Number.isFinite(stored) && stored > 0) return stored;
            return parseFloat(v.lineSell != null ? v.lineSell
                : (v.line_sell != null ? v.line_sell
                    : (v.totalSell != null ? v.totalSell
                        : (v.totalPrice != null ? v.totalPrice : v.sell)))) || 0;
        };

        // Booking modals sum vehicles[].lineSell — stamp each vehicle sell with markup
        const vehicles = Array.isArray(transferOptions.vehicles) ? transferOptions.vehicles
            : (linkedTransfer && Array.isArray(linkedTransfer.vehicles) ? linkedTransfer.vehicles : null);
        let base = 0;
        let markedTotal = 0;
        let markupAmount = 0;
        let appliedMeta = null;

        if (vehicles && vehicles.length) {
            if (!Array.isArray(transferOptions.vehicles)) {
                transferOptions.vehicles = vehicles;
            }
            vehicles.forEach(function (v) {
                if (!v || typeof v !== 'object') return;
                const vBase = vehicleBaseSell(v);
                const vMk = window.enquiryProApplyDmcMarkup(vBase, 'other');
                v.lineSell = vMk.price;
                v.line_sell = vMk.price;
                v.sell = vMk.price;
                v.Sell = vMk.price;
                v.totalPrice = vMk.price;
                v.totalSell = vMk.price;
                v.base_grand_total = vMk.base_price;
                v.dmc_profile_markup = vMk;
                base = round2(base + vMk.base_price);
                markedTotal = round2(markedTotal + vMk.price);
                markupAmount = round2(markupAmount + vMk.markup_amount);
                appliedMeta = vMk;
            });
        } else {
            // Prefer pre-markup base (edit re-save) so we never double-apply DMC markup
            let storedBase = parseFloat(
                (transferOptions.base_grand_total != null ? transferOptions.base_grand_total : null)
                || (transferOptions.dmc_profile_markup && transferOptions.dmc_profile_markup.base_price)
                || (linkedTransfer && linkedTransfer.base_grand_total)
                || (linkedTransfer && linkedTransfer.dmc_profile_markup && linkedTransfer.dmc_profile_markup.base_price)
                || 0
            );
            const seed = Object.assign({}, linkedTransfer || {}, transferOptions, {
                sell: transferOptions.sell != null ? transferOptions.sell : (linkedTransfer && linkedTransfer.sell),
                lineSell: transferOptions.lineSell != null ? transferOptions.lineSell
                    : (transferOptions.line_sell != null ? transferOptions.line_sell
                        : (linkedTransfer && (linkedTransfer.lineSell != null ? linkedTransfer.lineSell : linkedTransfer.line_sell))),
                totalSell: transferOptions.totalSell != null ? transferOptions.totalSell
                    : (linkedTransfer && linkedTransfer.totalSell),
                vehicles: null,
                type: transferOptions.type || (linkedTransfer && linkedTransfer.type),
                transferType: transferOptions.transferType || (linkedTransfer && linkedTransfer.transferType),
                way: transferOptions.way || (linkedTransfer && linkedTransfer.way),
                adults: transferOptions.adults != null ? transferOptions.adults : (linkedTransfer && linkedTransfer.adults),
                adultsQty: transferOptions.adultsQty != null ? transferOptions.adultsQty : (linkedTransfer && linkedTransfer.adultsQty),
                child: transferOptions.child != null ? transferOptions.child : (linkedTransfer && linkedTransfer.child),
                childQty: transferOptions.childQty != null ? transferOptions.childQty : (linkedTransfer && linkedTransfer.childQty),
                vehicleQty: transferOptions.vehicleQty || transferOptions.qty
                    || (linkedTransfer && (linkedTransfer.vehicleQty || linkedTransfer.qty)) || 1
            });
            if (Number.isFinite(storedBase) && storedBase > 0) {
                base = storedBase;
            } else if (typeof window.enquiryProResolveTransferSellBase === 'function') {
                base = window.enquiryProResolveTransferSellBase(seed).base;
            } else {
                base = parseFloat(transferOptions.lineSell || transferOptions.sell || transferOptions.totalPrice || 0) || 0;
            }
            appliedMeta = window.enquiryProApplyDmcMarkup(base, 'other');
            markedTotal = appliedMeta.price;
            markupAmount = appliedMeta.markup_amount;
            base = appliedMeta.base_price;
        }

        const applied = appliedMeta ? {
            base_price: round2(base),
            markup_amount: round2(markupAmount),
            price: round2(markedTotal),
            markup_type: appliedMeta.markup_type,
            markup_value: appliedMeta.markup_value,
            markup_kind: appliedMeta.markup_kind || 'other',
            dmc_id: appliedMeta.dmc_id
        } : window.enquiryProApplyDmcMarkup(base, 'other');

        transferOptions.sell = applied.price;
        transferOptions.Sell = applied.price;
        transferOptions.totalPrice = applied.price;
        transferOptions.total_price = applied.price;
        transferOptions.lineSell = applied.price;
        transferOptions.line_sell = applied.price;
        transferOptions.base_grand_total = applied.base_price;
        transferOptions.dmc_profile_markup = applied;
        return transferOptions;
    };

    /** Stamp DMC markup onto nested guide_options / guideInfo sell (flat — never × pax). */
    window.enquiryProStampGuideOptionsMarkup = function (guideOptions, guideSource) {
        if (!guideOptions || typeof guideOptions !== 'object') return guideOptions;
        if (guideOptions.dmc_profile_markup && Number(guideOptions.dmc_profile_markup.price) > 0
            && Math.abs(Number(guideOptions.sell) - Number(guideOptions.dmc_profile_markup.price)) < 0.02) {
            return guideOptions;
        }
        const src = guideSource || guideOptions;
        let storedBase = parseFloat(
            (guideOptions.base_grand_total != null ? guideOptions.base_grand_total : null)
            || (guideOptions.dmc_profile_markup && guideOptions.dmc_profile_markup.base_price)
            || (src && src.base_grand_total)
            || (src && src.dmc_profile_markup && src.dmc_profile_markup.base_price)
            || 0
        );
        let base = 0;
        if (Number.isFinite(storedBase) && storedBase > 0) {
            base = storedBase;
        } else {
            base = (typeof window.enquiryProResolveGuideSellBase === 'function')
                ? window.enquiryProResolveGuideSellBase(src)
                : (parseFloat((src && src.sell) || guideOptions.sell || 0) || 0);
        }
        const applied = window.enquiryProApplyDmcMarkup(base, 'other');
        guideOptions.sell = applied.price;
        guideOptions.Sell = applied.price;
        guideOptions.total_price = applied.price;
        guideOptions.totalPrice = applied.price;
        guideOptions.base_grand_total = applied.base_price;
        guideOptions.dmc_profile_markup = applied;
        return guideOptions;
    };

    /** Lite parity: Shared = unit × pax; Private = unit × vehicle qty. Markup applied on that base. */
    window.enquiryProIsSharedTransfer = function (item) {
        const t = String((item && (item.type || item.transferType || item.transfer_type)) || '').toLowerCase();
        return t === 's' || t === 'shared' || t === 'sic';
    };

    window.enquiryProTransferVehicleQty = function (item) {
        item = item || {};
        if (Array.isArray(item.vehicles) && item.vehicles.length) {
            return Math.max(1, item.vehicles.reduce(function (sum, v) {
                return sum + Math.max(1, parseInt(v && (v.qty || v.quantity || v.vehicle_qty) || 1, 10) || 1);
            }, 0));
        }
        const q = parseInt(
            item.vehicleQty || item.vehicle_qty || item.booked_vehicles || item.vehicle_count
            || item.qty || item.quantity || 1, 10
        );
        return Math.max(1, Number.isFinite(q) ? q : 1);
    };

    window.enquiryProTransferBillPax = function (item) {
        item = item || {};
        const adults = Math.max(0, parseInt(item.adultsQty ?? item.adults ?? item.adult ?? 0, 10) || 0);
        const child = Math.max(0, parseInt(item.childQty ?? item.child ?? item.children ?? 0, 10) || 0);
        return Math.max(0, adults + child);
    };

    window.enquiryProTransferWayMultiplier = function (way) {
        const w = String(way || '').toLowerCase();
        return (w === 'both-way' || w === 'both way' || w === 'both' || w === 'two-way'
            || w === 'return' || w === '2way' || w === 'round trip') ? 2 : 1;
    };

    /**
     * Seed / remember the Way that currently matches Cost/Sell unit inputs.
     * Used so manual prices can rescale 1-way ↔ both-way like zone pricing.
     */
    window.enquiryProSeedTransferUnitLastWay = function (domRow, way) {
        if (!domRow) return;
        const wayEl = domRow.querySelector('.arr-dep-vehicle-way');
        const w = String(way || wayEl?.value || 'one-way');
        if (wayEl && !wayEl.dataset.lastWay) wayEl.dataset.lastWay = w;
        if (!domRow.dataset.lastWay) domRow.dataset.lastWay = w;
        return w;
    };

    /**
     * When Cost/Sell were typed by the user (dataset.userEdited), rescale those
     * after-way units when Way changes — same ×2 / ÷2 behaviour as calculateTransferPrice.
     * Qty / Shared pax still apply separately via line = unit × qty|pax.
     */
    window.enquiryProApplyManualUnitWayRescale = function (domRow, nextWay, opts) {
        opts = opts || {};
        const ucEl = opts.ucEl || domRow?.querySelector?.('.arr-dep-unit-cost') || null;
        const usEl = opts.usEl || domRow?.querySelector?.('.arr-dep-unit-sell') || null;
        const wayEl = opts.wayEl || domRow?.querySelector?.('.arr-dep-vehicle-way') || null;
        let unitCost = parseFloat(opts.unitCost != null ? opts.unitCost : (ucEl && ucEl.value)) || 0;
        let unitSell = parseFloat(opts.unitSell != null ? opts.unitSell : (usEl && usEl.value)) || 0;
        const costEdited = !!(ucEl && ucEl.dataset.userEdited === '1');
        const sellEdited = !!(usEl && usEl.dataset.userEdited === '1');
        const next = String(nextWay || wayEl?.value || 'one-way');
        const nextMult = window.enquiryProTransferWayMultiplier(next);

        if (!costEdited && !sellEdited) {
            if (wayEl) wayEl.dataset.lastWay = next;
            if (domRow) domRow.dataset.lastWay = next;
            return { unitCost: unitCost, unitSell: unitSell, rescaled: false, nextWay: next };
        }

        const prev = String(
            (wayEl && wayEl.dataset.lastWay)
            || (domRow && domRow.dataset.lastWay)
            || next
        );
        const prevMult = window.enquiryProTransferWayMultiplier(prev);
        let rescaled = false;

        if (prevMult !== nextMult && (unitCost > 0 || unitSell > 0)) {
            if (costEdited && unitCost > 0) {
                unitCost = (unitCost / prevMult) * nextMult;
                if (ucEl) ucEl.value = unitCost.toFixed(2);
            }
            if (sellEdited && unitSell > 0) {
                unitSell = (unitSell / prevMult) * nextMult;
                if (usEl) usEl.value = unitSell.toFixed(2);
            }
            rescaled = true;
        }

        if (wayEl) wayEl.dataset.lastWay = next;
        if (domRow) domRow.dataset.lastWay = next;
        return { unitCost: unitCost, unitSell: unitSell, rescaled: rescaled, nextWay: next };
    };

    /**
     * Peel parent entry cost/sell onto vehicle unitCost/unitSell when missing
     * so hotel/A-D modal restore can fill Cost/Sell inputs from saved data.
     */
    window.enquiryProPeelTransferUnitsOntoVehicleList = function (list, entry) {
        if (!Array.isArray(list) || !list.length) return list || [];
        entry = entry || {};
        const entryCost = parseFloat(entry.cost != null ? entry.cost : entry.adultCost) || 0;
        const entrySell = parseFloat(entry.sell != null ? entry.sell : entry.adultSell) || 0;
        const entryPax = Math.max(0,
            (parseInt(entry.adults ?? entry.adultsQty ?? 0, 10) || 0)
            + (parseInt(entry.child ?? entry.childQty ?? 0, 10) || 0)
        );
        const entryType = String(entry.transferType || entry.type || '').toUpperCase();
        return list.map(function (v) {
            const copy = Object.assign({}, v || {});
            let uc = parseFloat(copy.unitCost != null ? copy.unitCost : copy.unit_cost) || 0;
            let us = parseFloat(copy.unitSell != null ? copy.unitSell : copy.unit_sell) || 0;
            if (uc <= 0) uc = parseFloat(copy.adultCost != null ? copy.adultCost : copy.cost) || 0;
            if (us <= 0) us = parseFloat(copy.adultSell != null ? copy.adultSell : copy.sell) || 0;
            const qty = Math.max(1, parseInt(copy.qty || copy.quantity || 1, 10) || 1);
            const rowType = String(copy.transferType || copy.type || entryType || 'S').toUpperCase();
            const rowPax = Math.max(0,
                (parseInt(copy.adults ?? copy.adultsQty ?? 0, 10) || 0)
                + (parseInt(copy.child ?? copy.childQty ?? 0, 10) || 0)
            ) || entryPax;
            if (uc <= 0 && list.length === 1 && entryCost > 0) {
                // Shared saves cost as line (unit × pax); Private as unit × qty
                if ((rowType === 'S' || rowType === 'SHARED') && rowPax > 1) {
                    uc = entryCost / rowPax;
                } else {
                    uc = entryCost / qty;
                }
            }
            if (us <= 0 && list.length === 1 && entrySell > 0) {
                if ((rowType === 'S' || rowType === 'SHARED') && rowPax > 1) {
                    us = entrySell / rowPax;
                } else {
                    us = entrySell / qty;
                }
            }
            if (uc > 0) copy.unitCost = uc;
            if (us > 0) copy.unitSell = us;
            return copy;
        });
    };

    /** Mark restored Cost/Sell inputs as manual so zone refresh cannot wipe them. */
    window.enquiryProMarkArrDepUnitsUserEdited = function (side, opts) {
        opts = opts || {};
        let container = null;
        if (typeof getArrDepVehicleRowsContainer === 'function') {
            container = getArrDepVehicleRowsContainer(side);
        }
        if (!container) {
            const idMap = {
                arrival: 'arrivalVehicleRows',
                departure: 'departureVehicleRows',
                local: 'localVehicleRows',
                restaurant: 'restaurantVehicleRows'
            };
            const sideStr = String(side || '');
            container = document.getElementById(idMap[side] || '')
                || (sideStr.indexOf('attr:') === 0
                    ? document.getElementById('attractionVehicleRows_' + sideStr.slice(5))
                    : null)
                || (sideStr === 'rest:meal' || sideStr === 'restaurant'
                    ? document.getElementById('restaurantVehicleRows')
                    : null)
                || (sideStr.indexOf('attr-mv-') === 0
                    ? document.getElementById(String(side) + '-rows')
                    : null);
        }
        if (!container && opts.container) container = opts.container;
        if (!container) return false;
        let any = false;
        const force = !!opts.force;
        container.querySelectorAll('.arr-dep-unit-cost, .arr-dep-unit-sell').forEach(function (inp) {
            const val = parseFloat(inp.value) || 0;
            if (force || val > 0) {
                inp.dataset.userEdited = '1';
                any = true;
            }
        });
        return any;
    };

    window.enquiryProSetArrDepGuideManualPrices = function (side, guideEntry) {
        const key = side === 'arrival' ? '_arrivalGuideManualPrices' : '_departureGuideManualPrices';
        if (!guideEntry) {
            window[key] = null;
            return null;
        }
        const masterId = String(guideEntry.guideId || guideEntry.guide_id || guideEntry.guideID || '').trim();
        const cost = parseFloat(guideEntry.cost != null ? guideEntry.cost : guideEntry.adultCost) || 0;
        const sell = parseFloat(guideEntry.sell != null ? guideEntry.sell : guideEntry.adultSell) || 0;
        const payload = {
            guideId: masterId,
            entryId: guideEntry.id != null ? String(guideEntry.id) : '',
            cost: cost,
            sell: sell,
            hours: parseInt(guideEntry.hours, 10) || 12
        };
        window[key] = (cost > 0 || sell > 0) ? payload : null;
        return window[key];
    };

    window.enquiryProGetArrDepGuideManualPrices = function (side, selectedGuideMasterId) {
        const key = side === 'arrival' ? '_arrivalGuideManualPrices' : '_departureGuideManualPrices';
        const m = window[key];
        if (!m) return null;
        if (selectedGuideMasterId && m.guideId
            && String(m.guideId) !== String(selectedGuideMasterId)) {
            return null;
        }
        if ((parseFloat(m.cost) || 0) <= 0 && (parseFloat(m.sell) || 0) <= 0) return null;
        return m;
    };

    window.enquiryProClearArrDepGuideManualPrices = function (side) {
        if (side === 'arrival' || !side) window._arrivalGuideManualPrices = null;
        if (side === 'departure' || !side) window._departureGuideManualPrices = null;
    };

    /**
     * Prefer saved/manual guide cost+sell when syncing hotel A/D guides so catalog
     * twelve_hour_price (e.g. 169) does not overwrite listing values (170/172).
     */
    window.enquiryProPreferSavedGuideCostSell = function (existingGuide, catalogCost, catalogSell, manual) {
        let cost = parseFloat(catalogCost) || 0;
        let sell = parseFloat(catalogSell) || 0;
        const prevCost = parseFloat(existingGuide && existingGuide.cost) || 0;
        const prevSell = parseFloat(existingGuide && existingGuide.sell) || 0;
        const manCost = parseFloat(manual && manual.cost) || 0;
        const manSell = parseFloat(manual && manual.sell) || 0;
        if (manCost > 0) cost = manCost;
        else if (prevCost > 0) cost = prevCost;
        if (manSell > 0) sell = manSell;
        else if (prevSell > 0) sell = prevSell;
        return { cost: cost, sell: sell };
    };

    /**
     * Resolve transfer sell base before DMC markup (Lite rule).
     * Shared: per-pax unit × (adults+child). Private: private unit × vehicle qty.
     * Both-way: same as one-way, with way ×2 on the unit when unit is zone/base (not already on line).
     * Local MV: parent.sell is often LINE (unit×pax); vehicles[].unitSell is the true unit — never × pax again.
     */
    window.enquiryProResolveTransferSellBase = function (item) {
        item = item || {};
        const isShared = window.enquiryProIsSharedTransfer(item);
        let adults = Math.max(0, parseInt(item.adultsQty ?? item.adults ?? 0, 10) || 0);
        let child = Math.max(0, parseInt(item.childQty ?? item.child ?? item.children ?? 0, 10) || 0);
        let pax = Math.max(0, adults + child);
        const qty = window.enquiryProTransferVehicleQty(item);
        const wayMult = window.enquiryProTransferWayMultiplier(item.way || item.transferWay || item.transfer_way);
        const v0 = (Array.isArray(item.vehicles) && item.vehicles.length) ? item.vehicles[0] : null;
        if (v0) {
            const va = Math.max(0, parseInt(v0.adults ?? v0.adultsQty ?? 0, 10) || 0);
            const vc = Math.max(0, parseInt(v0.child ?? v0.childQty ?? 0, 10) || 0);
            if ((va + vc) > 0 && pax <= 0) {
                adults = va;
                child = vc;
                pax = va + vc;
            }
        }

        // Dedicated unit fields (zone / unitSell) — never treat line totals as unit
        let dedicatedUnit = NaN;
        const unitCandidates = isShared
            ? [
                item.unitSell, item.unit_sell,
                v0 && v0.unitSell, v0 && v0.unit_sell,
                item.zoneSharedPrice, item.shared_price,
                v0 && v0.zoneSharedPrice
            ]
            : [
                item.unitSell, item.unit_sell,
                v0 && v0.unitSell, v0 && v0.unit_sell,
                item.zonePrivatePrice, item.private_price,
                v0 && v0.zonePrivatePrice
            ];
        for (let i = 0; i < unitCandidates.length; i++) {
            const n = parseFloat(unitCandidates[i]);
            if (Number.isFinite(n) && n > 0) { dedicatedUnit = n; break; }
        }

        // Explicit line totals — prefer vehicle lineSell / base_grand_total, else parent lineSell
        let explicitLine = parseFloat(
            item.base_grand_total != null ? item.base_grand_total
                : (item.dmc_profile_markup && item.dmc_profile_markup.base_price)
        );
        if (!Number.isFinite(explicitLine) || explicitLine <= 0) {
            explicitLine = parseFloat(item.lineSell != null ? item.lineSell : item.line_sell);
        }
        if ((!Number.isFinite(explicitLine) || explicitLine <= 0) && v0) {
            explicitLine = parseFloat(v0.lineSell != null ? v0.lineSell : v0.line_sell);
        }
        if (!Number.isFinite(explicitLine) || explicitLine <= 0) {
            explicitLine = parseFloat(item.totalSell != null ? item.totalSell : item.total_sell);
        }
        if (!Number.isFinite(explicitLine) || explicitLine <= 0) explicitLine = NaN;

        // Prefer stored BASE line only when we do not already have a clearer unit from vehicles
        // (avoids using a double-counted base_grand_total as Adult unit × pax again)
        const storedBaseLine = parseFloat(
            item.base_grand_total != null ? item.base_grand_total
                : (item.dmc_profile_markup && item.dmc_profile_markup.base_price)
        );
        if (Number.isFinite(storedBaseLine) && storedBaseLine > 0
            && !(Array.isArray(item.vehicles) && item.vehicles.length > 1)
            && !(Number.isFinite(dedicatedUnit) && dedicatedUnit > 0)) {
            let unitFromBase = isShared && pax > 0
                ? (storedBaseLine / pax)
                : (storedBaseLine / Math.max(1, qty));
            // Guard: if base already looks like (unit×pax)×pax, peel twice
            if (isShared && pax > 1 && Number.isFinite(dedicatedUnit) && dedicatedUnit > 0
                && Math.abs(storedBaseLine - dedicatedUnit * pax * pax) < 0.05) {
                unitFromBase = dedicatedUnit;
            }
            return {
                isShared: isShared,
                unit: Math.round(unitFromBase * 100) / 100,
                adults: adults,
                child: child,
                pax: pax,
                qty: qty,
                wayMult: wayMult,
                base: Math.round((isShared ? unitFromBase * Math.max(1, pax || 1) : unitFromBase * qty) * 100) / 100,
                vehicleName: item.vehicleName || item.vehicle_name || item.vehicles_name
                    || (v0 && (v0.vehicleName || v0.vehicle_name)) || ''
            };
        }

        // Multi-vehicle: sum each vehicle line (same Shared/Private + way rules per vehicle)
        if ((!Number.isFinite(explicitLine) || explicitLine <= 0)
            && Array.isArray(item.vehicles) && item.vehicles.length > 1) {
            let sum = 0;
            item.vehicles.forEach(function (v) {
                sum += window.enquiryProResolveTransferSellBase(Object.assign({}, item, v, {
                    vehicles: null,
                    way: v.way || item.way,
                    type: v.typeLabel || v.transferType || v.type || item.type,
                    transferType: v.transferType || item.transferType,
                    adults: v.adults ?? v.adultsQty ?? item.adults,
                    adultsQty: v.adultsQty ?? v.adults ?? item.adultsQty,
                    child: v.child ?? v.childQty ?? item.child,
                    childQty: v.childQty ?? v.child ?? item.childQty,
                    unitSell: v.unitSell != null ? v.unitSell : v.unit_sell,
                    lineSell: v.lineSell != null ? v.lineSell : v.line_sell,
                    sell: (v.unitSell != null || v.unit_sell != null)
                        ? (v.unitSell != null ? v.unitSell : v.unit_sell)
                        : (v.sell != null ? v.sell : item.sell)
                })).base;
            });
            if (sum > 0) {
                const unitGuess = isShared && pax > 0 ? (sum / pax) : (sum / qty);
                return {
                    isShared: isShared,
                    unit: Math.round(unitGuess * 100) / 100,
                    adults: adults,
                    child: child,
                    pax: pax,
                    qty: qty,
                    wayMult: wayMult,
                    base: Math.round(sum * 100) / 100,
                    vehicleName: item.vehicleName || item.vehicle_name || item.vehicles_name || ''
                };
            }
        }

        let unit = dedicatedUnit;
        const sellRaw = parseFloat(item.sell || item.adultSell || item.adult_sell || 0) || 0;

        // If dedicated unit missing, peel sell: parent.sell for local Shared is often LINE (unit×pax)
        if (!Number.isFinite(unit) || unit <= 0) {
            if (sellRaw > 0) {
                if (isShared && pax > 1) {
                    // sell == lineSell → peel; sell == unit×pax from vehicle line → peel;
                    // sell already equals a plausible unit (listing unit×qty) → keep
                    if (Number.isFinite(explicitLine) && explicitLine > 0
                        && Math.abs(explicitLine - sellRaw) < 0.02) {
                        unit = sellRaw / pax;
                    } else if (Number.isFinite(explicitLine) && explicitLine > 0
                        && Math.abs(explicitLine - sellRaw * pax) < 0.05) {
                        unit = sellRaw;
                    } else if (Math.abs(sellRaw / pax) > 0 && Number.isFinite(explicitLine)
                        && Math.abs(explicitLine - (sellRaw / pax) * pax) < 0.05) {
                        unit = sellRaw / pax;
                } else {
                    // Shared without lineSell: prefer peel when pax>1 (MV totalSell is LINE).
                    // Listing sometimes stores unit in sell — only peel when sell looks like unit×pax
                    // vs a dedicated vehicle unit, else keep sell as unit.
                    unit = sellRaw / pax;
                }
                } else if (!isShared && qty > 1 && Number.isFinite(explicitLine)
                    && Math.abs(explicitLine - sellRaw) < 0.01) {
                    unit = sellRaw / qty;
                } else {
                    unit = sellRaw;
                }
            } else {
                unit = 0;
            }
        } else if (isShared && pax > 1 && sellRaw > 0
            && Math.abs(dedicatedUnit - sellRaw) < 0.02
            && Math.abs(sellRaw - unit * pax) > 0.05) {
            // Guard: unitSell accidentally stored as LINE (same as parent.sell line)
            // e.g. dedicatedUnit=1120, sell=1120, pax=4 → real unit is 280
            if (Number.isFinite(explicitLine) && Math.abs(explicitLine - sellRaw) < 0.02) {
                unit = sellRaw / pax;
            } else if (!Number.isFinite(explicitLine) || explicitLine <= 0) {
                // parent.sell is line when it equals "unit" that is huge vs cost listing unit
                const costRaw = parseFloat(item.cost || item.adultCost || item.unitCost || 0) || 0;
                if (costRaw > 0 && Math.abs(costRaw - sellRaw) > 0.05
                    && Math.abs((costRaw * pax) - sellRaw) < 0.05) {
                    // cost looks like unit, sell looks like line — use cost as unit for peel ratio
                    unit = sellRaw / pax;
                } else if (costRaw > 0 && Math.abs(costRaw - sellRaw) < 0.02) {
                    // both look like units (listing) — keep dedicatedUnit
                } else if (Math.abs(sellRaw / pax - Math.round(sellRaw / pax * 100) / 100) < 0.011) {
                    // sell divisible by pax cleanly and equals dedicated → treat as line pollution
                    unit = sellRaw / pax;
                }
            }
        } else if (wayMult > 1) {
            // Zone unit is one-way; apply both-way on dedicated zone/unit when line not precomputed
            // calculateTransferPrice already ×2 into unitSell for MV — detect by comparing to zone
            const zoneOneWay = isShared
                ? (parseFloat(item.zoneSharedPrice) || parseFloat(v0 && v0.zoneSharedPrice) || 0)
                : (parseFloat(item.zonePrivatePrice) || parseFloat(v0 && v0.zonePrivatePrice) || 0);
            if (zoneOneWay > 0 && Math.abs(dedicatedUnit - zoneOneWay) < 0.01) {
                unit = dedicatedUnit * wayMult;
            }
        }

        // If dedicated unit equals Shared line (unit×pax) but vehicles have true unit, prefer vehicle
        if (isShared && pax > 1 && Number.isFinite(unit) && unit > 0 && v0) {
            const vu = parseFloat(v0.unitSell != null ? v0.unitSell : v0.unit_sell);
            if (Number.isFinite(vu) && vu > 0 && Math.abs(unit - vu * pax) < 0.05) {
                unit = vu;
            }
        }

        let line;
        if (Number.isFinite(explicitLine) && explicitLine > 0) {
            // Trust lineSell, but if it equals dedicated one-way unit only, scale up
            if (Number.isFinite(unit) && unit > 0
                && Math.abs(explicitLine - unit) < 0.01) {
                const zoneOneWay = isShared
                    ? (parseFloat(item.zoneSharedPrice) || 0)
                    : (parseFloat(item.zonePrivatePrice) || 0);
                const scaledUnit = (wayMult > 1 && zoneOneWay > 0 && Math.abs(unit - zoneOneWay) < 0.01)
                    ? unit * wayMult
                    : unit;
                line = isShared ? scaledUnit * Math.max(1, pax || 1) : scaledUnit * qty;
            } else if (isShared && pax > 1 && Number.isFinite(unit) && unit > 0
                && Math.abs(explicitLine - unit * pax * pax) < 0.05) {
                // lineSell was double-counted (unit×pax)×pax — correct to unit×pax
                line = unit * Math.max(1, pax || 1);
            } else {
                line = explicitLine;
            }
        } else {
            line = isShared ? (unit * Math.max(1, pax || 1)) : (unit * qty);
        }

        // Final Shared guard: never show Adult (unit×pax) × pax
        if (isShared && pax > 1 && Number.isFinite(unit) && unit > 0
            && Math.abs(line - unit * pax) > 0.05
            && Math.abs(line - unit) < 0.02) {
            line = unit * Math.max(1, pax || 1);
        }
        if (isShared && pax > 1 && Number.isFinite(unit) && unit > 0
            && Math.abs(line - unit * pax * pax) < 0.05) {
            line = unit * Math.max(1, pax || 1);
            unit = line / pax;
        }

        return {
            isShared: isShared,
            unit: Math.round(unit * 100) / 100,
            adults: adults,
            child: child,
            pax: pax,
            qty: qty,
            wayMult: wayMult,
            base: Math.round(Math.max(0, line) * 100) / 100,
            vehicleName: item.vehicleName || item.vehicle_name || item.vehicles_name
                || (v0 && (v0.vehicleName || v0.vehicle_name)) || ''
        };
    };

    /** Guide sell is flat (never × pax) — always return BASE (before DMC markup). */
    window.enquiryProResolveGuideSellBase = function (guide) {
        guide = guide || {};
        const fromMeta = parseFloat(
            guide.base_grand_total != null ? guide.base_grand_total
                : (guide.dmc_profile_markup && guide.dmc_profile_markup.base_price)
        );
        if (Number.isFinite(fromMeta) && fromMeta > 0) {
            return Math.round(fromMeta * 100) / 100;
        }
        const sell = parseFloat(guide.sell ?? guide.adultSell ?? guide.price ?? guide.total_price ?? guide.totalPrice) || 0;
        return Math.round(Math.max(0, sell) * 100) / 100;
    };

    /** Resolve linked guide for tour/meal (guideInfo often lacks sell — look up guideList / guide_options). */
    window.enquiryProResolveLinkedGuide = function (service) {
        service = service || {};
        let guide = null;
        if (service.guideId && typeof guideList !== 'undefined' && Array.isArray(guideList)) {
            guide = guideList.find(function (g) {
                return String(g.id) === String(service.guideId)
                    || String(g.guide_id || g.guideId || '') === String(service.guideId);
            }) || null;
        }
        if (!guide && service.guideInfo && (service.guideInfo.sell || service.guideInfo.adultSell || service.guideInfo.price)) {
            guide = service.guideInfo;
        }
        if (!guide && service.guide_options) {
            guide = service.guide_options;
        }
        if (!guide && service.guideInfo) {
            // guideInfo may only have guide_id/name — still return it so name shows; sell from options/list
            guide = Object.assign({}, service.guideInfo);
            if (!(parseFloat(guide.sell) > 0) && service.guide_options) {
                guide.sell = service.guide_options.sell || service.guide_options.total_price;
                guide.adultSell = service.guide_options.adultSell || guide.sell;
            }
            if (!(parseFloat(guide.sell) > 0) && typeof guideList !== 'undefined' && Array.isArray(guideList)) {
                const gid = service.guideId || guide.guide_id || guide.guideId;
                const fromList = guideList.find(function (g) {
                    return String(g.id) === String(gid)
                        || String(g.guide_id || g.guideId || '') === String(gid)
                        || (service.sourceTourId && String(g.sourceTourId) === String(service.id));
                });
                if (fromList) {
                    guide = Object.assign({}, guide, {
                        sell: fromList.sell,
                        adultSell: fromList.adultSell || fromList.sell,
                        base_grand_total: fromList.base_grand_total || guide.base_grand_total,
                        dmc_profile_markup: fromList.dmc_profile_markup || guide.dmc_profile_markup,
                        name: guide.name || fromList.name || fromList.guideName
                    });
                }
            }
        }
        // Always carry base_grand_total from guide_options when present (edit JSON stores marked sell)
        if (guide && service.guide_options) {
            const go = service.guide_options;
            if (!(parseFloat(guide.base_grand_total) > 0) && parseFloat(go.base_grand_total) > 0) {
                guide.base_grand_total = go.base_grand_total;
            }
            if (!guide.dmc_profile_markup && go.dmc_profile_markup) {
                guide.dmc_profile_markup = go.dmc_profile_markup;
            }
        }
        return guide;
    };

    /** Resolve linked transfer for tour/meal (prefer transferList / transferInfo with line totals). */
    window.enquiryProResolveLinkedTransfer = function (service) {
        service = service || {};
        let tr = service.transferInfo || null;
        if (service.transferId && typeof transferList !== 'undefined' && Array.isArray(transferList)) {
            const fromList = transferList.find(function (t) {
                return String(t.id) === String(service.transferId);
            });
            if (fromList) tr = Object.assign({}, tr || {}, fromList);
        }
        return tr;
    };

    window.enquiryProDmcMarkupSummaryHtml = function (applied, cur) {
        applied = applied || window.enquiryProApplyDmcMarkup(0, 'other');
        cur = cur || 'SGD';
        if (!(Number(applied.markup_amount || 0) > 0) && !(Number(applied.markup_value || 0) > 0)) {
            return ''
                + '<div class="ep-mk-summary-row is-total">'
                + '<span>Total Price</span>'
                + '<span class="ep-mk-amt">' + esc(money(cur, applied.price || applied.base_price || 0)) + '</span>'
                + '</div>';
        }
        let typeLabel = String(Number(applied.markup_value || 0)) + '%';
        if (String(applied.markup_type || '').toLowerCase() === 'flat') {
            typeLabel = 'Flat ' + money(cur, applied.markup_value || 0);
        }
        return ''
            + '<div class="ep-mk-summary-row">'
            + '<span><strong>Price (before markup)</strong></span>'
            + '<span class="ep-mk-amt">' + esc(money(cur, applied.base_price)) + '</span>'
            + '</div>'
            + '<div class="ep-mk-summary-row is-markup">'
            + '<span><strong>Markup Amount</strong> <small class="text-muted">(' + esc(typeLabel) + ')</small></span>'
            + '<span class="ep-mk-amt">' + esc(money(cur, applied.markup_amount)) + '</span>'
            + '</div>'
            + '<div class="ep-mk-summary-row is-total">'
            + '<span>Total Price</span>'
            + '<span class="ep-mk-amt">' + esc(money(cur, applied.price)) + '</span>'
            + '</div>';
    };

    function lineHtml(label, amount, cur) {
        return '<div class="ep-mk-line"><span>' + label + '</span><strong>' + esc(money(cur, amount)) + '</strong></div>';
    }

    function showServiceModal(title, bodyHtml) {
        const titleEl = document.getElementById('enquiryProServicePriceBreakdownTitle');
        const bodyEl = document.getElementById('enquiryProServicePriceBreakdownBody');
        const modalEl = document.getElementById('enquiryProServicePriceBreakdownModal');
        if (titleEl) titleEl.innerHTML = '<i class="ri-file-list-3-line me-1" style="color:#2563eb;"></i>' + esc(title || 'Price breakdown');
        if (bodyEl) {
            bodyEl.innerHTML = '<div class="ep-pd-wrap">' + (bodyHtml || '') + '</div>';
        }
        if (!modalEl || typeof bootstrap === 'undefined') return;
        if (modalEl.parentElement !== document.body) document.body.appendChild(modalEl);
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    function num(v) {
        const n = parseFloat(String(v == null ? '' : v).replace(/[^0-9.-]/g, ''));
        return Number.isFinite(n) ? n : 0;
    }

    function buildTourBreakdown(tour) {
        const cur = resolveCurrency(tour);
        const adults = Math.max(0, parseInt(tour.adultsQty, 10) || 0);
        const children = Math.max(0, parseInt(tour.childQty, 10) || 0);
        const infants = Math.max(0, parseInt(tour.infantQty, 10) || 0);
        const adultP = num(tour.adultSell);
        const childP = num(tour.childSell);
        const infantP = num(tour.infantSell);
        let ticketTotal = (adultP * adults) + (childP * children) + (infantP * infants);
        let lines = '';
        if (adults > 0) lines += lineHtml('<strong>Adult</strong> × ' + adults + ' (' + money(cur, adultP) + ')', adultP * adults, cur);
        if (children > 0) lines += lineHtml('<strong>Child</strong> × ' + children + ' (' + money(cur, childP) + ')', childP * children, cur);
        if (infants > 0) lines += lineHtml('<strong>Infant</strong> × ' + infants + ' (' + money(cur, infantP) + ')', infantP * infants, cur);

        let transferTotal = 0;
        const tr = window.enquiryProResolveLinkedTransfer(tour);
        if (tr) {
            const seed = Object.assign({}, tr, {
                adults: tr.adults != null ? tr.adults : adults,
                adultsQty: tr.adultsQty != null ? tr.adultsQty : (tr.adults != null ? tr.adults : adults),
                child: tr.child != null ? tr.child : children,
                childQty: tr.childQty != null ? tr.childQty : (tr.child != null ? tr.child : children)
            });
            const resolved = window.enquiryProResolveTransferSellBase(seed);
            transferTotal = resolved.base;
            const wayLabel = resolved.wayMult > 1 ? ' <small class="text-muted">(2-Way)</small>' : '';
            if (resolved.isShared && resolved.pax > 0) {
                lines += lineHtml(
                    '<strong>Transfer</strong> Shared ' + esc(Number(resolved.unit).toFixed(2))
                        + ' × ' + resolved.pax + wayLabel,
                    transferTotal,
                    cur
                );
            } else {
                lines += lineHtml(
                    '<strong>Transfer</strong> Private ' + esc(Number(resolved.unit).toFixed(2))
                        + (resolved.qty > 1 ? (' × ' + resolved.qty) : '') + wayLabel,
                    transferTotal,
                    cur
                );
            }
        }

        let guideTotal = 0;
        const guide = window.enquiryProResolveLinkedGuide(tour);
        if (guide || tour.guideId || tour.guideRequired) {
            guideTotal = window.enquiryProResolveGuideSellBase(guide || {});
            lines += lineHtml(
                '<strong>Guide</strong> ' + esc((guide && (guide.guideName || guide.name || guide.guide_name)) || ''),
                guideTotal,
                cur
            );
        }

        // Markup each service separately (same as store: ticket / transfer / guide)
        const ticketMk = window.enquiryProApplyDmcMarkup(ticketTotal, 'other');
        const transferMk = transferTotal > 0 ? window.enquiryProApplyDmcMarkup(transferTotal, 'other') : null;
        const guideMk = guideTotal > 0 ? window.enquiryProApplyDmcMarkup(guideTotal, 'other') : null;
        const combinedBase = ticketTotal + transferTotal + guideTotal;
        const combinedFinal = ticketMk.price
            + (transferMk ? transferMk.price : 0)
            + (guideMk ? guideMk.price : 0);
        const combinedMkAmt = ticketMk.markup_amount
            + (transferMk ? transferMk.markup_amount : 0)
            + (guideMk ? guideMk.markup_amount : 0);
        const applied = {
            base_price: Math.round(combinedBase * 100) / 100,
            markup_amount: Math.round(combinedMkAmt * 100) / 100,
            price: Math.round(combinedFinal * 100) / 100,
            markup_type: ticketMk.markup_type,
            markup_value: ticketMk.markup_value,
            markup_kind: 'other',
            dmc_id: ticketMk.dmc_id
        };
        return {
            title: (tour.attractionName || 'Attraction') + ' — Price breakdown',
            html: '<div class="ep-pd-section-label">Items</div>'
                + '<div class="ep-pd-summary" style="margin-bottom:10px;">' + lines + '</div>'
                + '<div class="ep-pd-section-label">Summary</div>'
                + '<div class="ep-pd-summary">' + window.enquiryProDmcMarkupSummaryHtml(applied, cur) + '</div>',
            applied: applied
        };
    }

    function buildRestaurantBreakdown(meal) {
        const cur = resolveCurrency(meal);
        const adults = Math.max(0, parseInt(meal.adultsQty, 10) || 0);
        const children = Math.max(0, parseInt(meal.childQty, 10) || 0);
        const adultP = num(meal.adultSell);
        const childP = num(meal.childSell);
        let mealTotal = (adultP * adults) + (childP * children);
        let lines = '';
        if (adults > 0) lines += lineHtml('<strong>Adult meal</strong> × ' + adults, adultP * adults, cur);
        if (children > 0) lines += lineHtml('<strong>Child meal</strong> × ' + children, childP * children, cur);

        let transferTotal = 0;
        const tr = window.enquiryProResolveLinkedTransfer(meal);
        if (tr) {
            const seed = Object.assign({}, tr, {
                adults: tr.adults != null ? tr.adults : adults,
                adultsQty: tr.adultsQty != null ? tr.adultsQty : (tr.adults != null ? tr.adults : adults),
                child: tr.child != null ? tr.child : children,
                childQty: tr.childQty != null ? tr.childQty : (tr.child != null ? tr.child : children)
            });
            const resolved = window.enquiryProResolveTransferSellBase(seed);
            transferTotal = resolved.base;
            const wayLabel = resolved.wayMult > 1 ? ' <small class="text-muted">(2-Way)</small>' : '';
            if (resolved.isShared && resolved.pax > 0) {
                lines += lineHtml(
                    '<strong>Transfer</strong> Shared ' + esc(Number(resolved.unit).toFixed(2))
                        + ' × ' + resolved.pax + wayLabel,
                    transferTotal,
                    cur
                );
            } else {
                lines += lineHtml(
                    '<strong>Transfer</strong> Private ' + esc(Number(resolved.unit).toFixed(2))
                        + (resolved.qty > 1 ? (' × ' + resolved.qty) : '') + wayLabel,
                    transferTotal,
                    cur
                );
            }
        }

        let guideTotal = 0;
        const guide = window.enquiryProResolveLinkedGuide(meal);
        if (guide || meal.guideId || meal.guideRequired) {
            guideTotal = window.enquiryProResolveGuideSellBase(guide || {});
            lines += lineHtml(
                '<strong>Guide</strong> ' + esc((guide && (guide.guideName || guide.name || guide.guide_name)) || ''),
                guideTotal,
                cur
            );
        }

        // Markup each service separately (same as store: meal / transfer / guide)
        const mealMk = window.enquiryProApplyDmcMarkup(mealTotal, 'other');
        const transferMk = transferTotal > 0 ? window.enquiryProApplyDmcMarkup(transferTotal, 'other') : null;
        const guideMk = guideTotal > 0 ? window.enquiryProApplyDmcMarkup(guideTotal, 'other') : null;
        const combinedBase = mealTotal + transferTotal + guideTotal;
        const combinedFinal = mealMk.price
            + (transferMk ? transferMk.price : 0)
            + (guideMk ? guideMk.price : 0);
        const combinedMkAmt = mealMk.markup_amount
            + (transferMk ? transferMk.markup_amount : 0)
            + (guideMk ? guideMk.markup_amount : 0);
        const applied = {
            base_price: Math.round(combinedBase * 100) / 100,
            markup_amount: Math.round(combinedMkAmt * 100) / 100,
            price: Math.round(combinedFinal * 100) / 100,
            markup_type: mealMk.markup_type,
            markup_value: mealMk.markup_value,
            markup_kind: 'other',
            dmc_id: mealMk.dmc_id
        };
        return {
            title: (meal.restaurantName || meal.mealName || 'Restaurant') + ' — Price breakdown',
            html: '<div class="ep-pd-section-label">Items</div><div class="ep-pd-summary" style="margin-bottom:10px;">' + lines + '</div>'
                + '<div class="ep-pd-section-label">Summary</div><div class="ep-pd-summary">'
                + window.enquiryProDmcMarkupSummaryHtml(applied, cur) + '</div>',
            applied: applied
        };
    }

    /** Parse "Arrival: A → B" / "A -> B" from transfer.destination / service display strings. */
    function parseTransferRouteLabels(tr) {
        tr = tr || {};
        let from = String(
            tr.pickup || tr.entrypickup || tr.pickupLocation || tr.pickupName
            || tr.from_name || tr.fromName || ''
        ).trim();
        let to = String(
            tr.dropoff || tr.entrydropoff || tr.dropoffLocation || tr.dropName
            || tr.transferDestinationName || tr.destinationName || tr.to_name || tr.toName
            || tr.hotelName || tr.hotel_name || ''
        ).trim();

        // Linked arrival/departure rows often only keep the full label on destination/service
        const label = String(tr.destination || tr.service || tr.tourActivity || '').trim();
        if (label) {
            const cleaned = label
                .replace(/^\s*(Arrival|Departure)\s*:\s*/i, '')
                .trim();
            const parts = cleaned.split(/\s*(?:→|->|—|–)\s*/);
            if (parts.length >= 2) {
                if (!from) from = String(parts[0] || '').trim();
                if (!to) to = String(parts.slice(1).join(' → ') || '').trim();
            }
        }

        // Arrival/departure list may hold transferDestinationName when transfer row does not
        if ((!to || !from) && tr.sourceId && typeof arrivalDepartureList !== 'undefined' && Array.isArray(arrivalDepartureList)) {
            const ad = arrivalDepartureList.find(function (e) {
                return String(e.id) === String(tr.sourceId)
                    || String(e.transferId) === String(tr.id);
            });
            if (ad) {
                if (!from) {
                    const isDep = /departure/i.test(String(ad.type || tr.sourceType || ''));
                    from = isDep
                        ? String(ad.transferDestinationName || ad.hotelName || '').trim()
                        : String(ad.portName || '').trim();
                }
                if (!to) {
                    const isDep = /departure/i.test(String(ad.type || tr.sourceType || ''));
                    to = isDep
                        ? String(ad.portName || '').trim()
                        : String(ad.transferDestinationName || ad.hotelName || '').trim();
                }
            }
        }

        // Fallback: port is pickup for arrival, dropoff for departure when only portName exists
        if (!from && tr.portName) {
            const isDep = /departure/i.test(String(tr.sourceType || tr.type || tr.destination || ''));
            if (!isDep) from = String(tr.portName).trim();
        }
        if (!to && tr.portName) {
            const isDep = /departure/i.test(String(tr.sourceType || tr.destination || ''));
            if (isDep) to = String(tr.portName).trim();
        }

        return { from: from, to: to };
    }

    function transferMetaHtml(tr, resolved) {
        const bits = [];
        bits.push(resolved.isShared ? 'shared' : 'private');
        const route = parseTransferRouteLabels(tr);
        if (route.from || route.to) {
            bits.push(esc(route.from || '-') + ' → ' + esc(route.to || '-'));
        }
        const time = tr.entrytime || tr.time || tr.visitTime || '';
        if (time) bits.push(esc(String(time)));
        if (!resolved.isShared && resolved.qty > 1) bits.push(resolved.qty + ' vehicles');
        return '<div class="text-muted small mb-2" style="font-size:11px;">' + bits.join(' · ') + '</div>';
    }

    function transferItemLinesHtml(resolved, cur) {
        let lines = '';
        if (resolved.isShared) {
            const pax = Math.max(1, resolved.pax || 1);
            if (resolved.adults > 0) {
                lines += lineHtml(
                    '<strong>Adult</strong> ' + esc(Number(resolved.unit).toFixed(2)) + ' × ' + resolved.adults,
                    resolved.unit * resolved.adults,
                    cur
                );
            }
            if (resolved.child > 0) {
                lines += lineHtml(
                    '<strong>Child</strong> ' + esc(Number(resolved.unit).toFixed(2)) + ' × ' + resolved.child,
                    resolved.unit * resolved.child,
                    cur
                );
            }
            if (!(resolved.adults > 0) && !(resolved.child > 0)) {
                lines += lineHtml(
                    '<strong>Shared</strong> ' + esc(Number(resolved.unit).toFixed(2)) + ' × ' + pax,
                    resolved.base,
                    cur
                );
            }
        } else {
            let label = '<strong>Private</strong> ' + esc(Number(resolved.unit).toFixed(2));
            if (resolved.qty > 1) label += ' × ' + resolved.qty + ' vehicles';
            lines += lineHtml(label, resolved.base, cur);
        }
        return lines;
    }

    function buildGuideBreakdown(guide) {
        const cur = resolveCurrency(guide);
        const sell = window.enquiryProResolveGuideSellBase(guide);
        let lines = lineHtml('<strong>Guide</strong> ' + esc(guide.guideName || guide.name || guide.tourActivity || ''), sell, cur);
        const applied = window.enquiryProApplyDmcMarkup(sell, 'other');
        return {
            title: (guide.guideName || guide.name || 'Guide') + ' — Price breakdown',
            html: '<div class="ep-pd-section-label">Items</div><div class="ep-pd-summary" style="margin-bottom:10px;">' + lines + '</div>'
                + '<div class="ep-pd-section-label">Summary</div><div class="ep-pd-summary">'
                + window.enquiryProDmcMarkupSummaryHtml(applied, cur) + '</div>',
            applied: applied
        };
    }

    function buildMiscBreakdown(misc) {
        const cur = resolveCurrency(misc);
        const adults = Math.max(0, parseInt(misc.adultsQty, 10) || 0);
        const children = Math.max(0, parseInt(misc.childQty, 10) || 0);
        const infants = Math.max(0, parseInt(misc.infantQty, 10) || 0);
        const adultP = num(misc.adultSell);
        const childP = num(misc.childSell);
        const infantP = num(misc.infantSell);
        const lineBase = (adultP * adults) + (childP * children) + (infantP * infants);
        let lines = '';
        if (adults > 0) {
            lines += lineHtml(
                '<strong>Adult</strong> × ' + adults + ' (' + money(cur, adultP) + ')',
                adultP * adults,
                cur
            );
        }
        if (children > 0) {
            lines += lineHtml(
                '<strong>Child</strong> × ' + children + ' (' + money(cur, childP) + ')',
                childP * children,
                cur
            );
        }
        if (infants > 0) {
            lines += lineHtml(
                '<strong>Infant</strong> × ' + infants + ' (' + money(cur, infantP) + ')',
                infantP * infants,
                cur
            );
        }
        if (!lines) {
            lines = lineHtml('<strong>Item</strong> ' + esc(misc.itemName || ''), lineBase, cur);
        }
        const applied = window.enquiryProApplyDmcMarkup(lineBase, 'other');
        return {
            title: (misc.itemName || 'Miscellaneous') + ' — Price breakdown',
            html: '<div class="ep-pd-section-label">Items</div>'
                + '<div class="ep-pd-summary" style="margin-bottom:10px;">' + lines + '</div>'
                + '<div class="ep-pd-section-label">Summary</div>'
                + '<div class="ep-pd-summary">' + window.enquiryProDmcMarkupSummaryHtml(applied, cur) + '</div>',
            applied: applied
        };
    }

    function buildTransferBreakdown(tr) {
        const cur = resolveCurrency(tr);
        let transferBase = 0;
        let lines = '';
        let metaResolved = null;

        // Prefer per-vehicle slices so Shared unitSell (e.g. 280) is not confused with LINE sell (1120)
        let slices = [];
        if (typeof expandArrDepVehiclesForOrderStore === 'function') {
            try { slices = expandArrDepVehiclesForOrderStore(tr) || []; } catch (e) { slices = []; }
        }
        if ((!slices || !slices.length) && Array.isArray(tr.vehicles) && tr.vehicles.length) {
            slices = tr.vehicles;
        }
        if (slices && slices.length) {
            slices.forEach(function (slice) {
                const vUnit = parseFloat(slice.unitSell != null ? slice.unitSell : slice.unit_sell);
                const vLine = parseFloat(slice.lineSell != null ? slice.lineSell : slice.line_sell);
                const seed = Object.assign({}, tr, slice, {
                    vehicles: null,
                    type: slice.typeLabel || slice.transferType || slice.type || tr.transferType || tr.type,
                    transferType: slice.transferType || slice.type || tr.transferType || tr.type,
                    // Pass UNIT as sell so resolve never treats LINE as Adult unit
                    sell: (Number.isFinite(vUnit) && vUnit > 0) ? vUnit
                        : (slice.adultSell != null ? slice.adultSell : tr.sell),
                    adultSell: (Number.isFinite(vUnit) && vUnit > 0) ? vUnit
                        : (slice.adultSell != null ? slice.adultSell : tr.adultSell),
                    unitSell: Number.isFinite(vUnit) && vUnit > 0 ? vUnit : slice.unitSell,
                    lineSell: (Number.isFinite(vLine) && vLine > 0) ? vLine
                        : ((Number.isFinite(vUnit) && vUnit > 0)
                            ? (window.enquiryProIsSharedTransfer(tr)
                                ? vUnit * Math.max(1, (parseInt(slice.adults ?? slice.adultsQty ?? tr.adults ?? 0, 10) || 0)
                                    + (parseInt(slice.child ?? slice.childQty ?? tr.child ?? 0, 10) || 0) || 1)
                                : vUnit * Math.max(1, parseInt(slice.qty || 1, 10) || 1))
                            : tr.lineSell),
                    adults: slice.adults ?? slice.adultsQty ?? tr.adults,
                    adultsQty: slice.adultsQty ?? slice.adults ?? tr.adultsQty ?? tr.adults,
                    child: slice.child ?? slice.childQty ?? tr.child,
                    childQty: slice.childQty ?? slice.child ?? tr.childQty ?? tr.child,
                    vehicleQty: slice.qty || slice.quantity || 1,
                    vehicleName: slice.vehicleName || slice.vehicle_name || tr.vehicleName,
                    base_grand_total: null,
                    dmc_profile_markup: null
                });
                const resolved = window.enquiryProResolveTransferSellBase(seed);
                transferBase += resolved.base;
                lines += transferItemLinesHtml(resolved, cur);
                metaResolved = resolved;
            });
        }
        if (!(transferBase > 0) && !lines) {
            const resolved = window.enquiryProResolveTransferSellBase(tr);
            transferBase = resolved.base;
            lines = transferItemLinesHtml(resolved, cur);
            metaResolved = resolved;
        }
        if (!metaResolved) {
            metaResolved = window.enquiryProResolveTransferSellBase(tr);
        }

        let guideBase = 0;
        let guide = null;
        if (tr.guideId && typeof guideList !== 'undefined' && Array.isArray(guideList)) {
            guide = guideList.find(function (g) { return String(g.id) === String(tr.guideId); }) || null;
        }
        if (!guide && tr.guideInfo) guide = tr.guideInfo;
        if (!guide && tr.guide_options) guide = tr.guide_options;
        if (guide) {
            guideBase = window.enquiryProResolveGuideSellBase(guide);
            lines += lineHtml(
                '<strong>Guide</strong> ' + esc(guide.guideName || guide.name || guide.guide_name || ''),
                guideBase,
                cur
            );
        }
        const transferMk = window.enquiryProApplyDmcMarkup(transferBase, 'other');
        const guideMk = guideBase > 0 ? window.enquiryProApplyDmcMarkup(guideBase, 'other') : null;
        const applied = {
            base_price: Math.round((transferBase + guideBase) * 100) / 100,
            markup_amount: Math.round((transferMk.markup_amount + (guideMk ? guideMk.markup_amount : 0)) * 100) / 100,
            price: Math.round((transferMk.price + (guideMk ? guideMk.price : 0)) * 100) / 100,
            markup_type: transferMk.markup_type,
            markup_value: transferMk.markup_value,
            markup_kind: 'other',
            dmc_id: transferMk.dmc_id
        };
        const name = metaResolved.vehicleName || tr.vehicleName || tr.vehicles_name || 'Transfer';
        return {
            title: name + ' — Price breakdown',
            html: transferMetaHtml(tr, metaResolved)
                + '<div class="ep-pd-section-label">Items</div><div class="ep-pd-summary" style="margin-bottom:10px;">'
                + lines + '</div>'
                + '<div class="ep-pd-section-label">Summary</div><div class="ep-pd-summary">'
                + window.enquiryProDmcMarkupSummaryHtml(applied, cur) + '</div>',
            applied: applied
        };
    }

    function buildArrivalDepartureBreakdown(item) {
        const cur = resolveCurrency(item);
        const isArrival = /arrival/i.test(String(item.type || item.serviceType || ''));
        const label = isArrival ? 'Arrival' : 'Departure';

        // Prefer per-vehicle slice totals (shared×pax / private×qty) when available
        let transferBase = 0;
        let lines = '';
        let metaSeed = item;
        if (typeof expandArrDepVehiclesForOrderStore === 'function') {
            const slices = expandArrDepVehiclesForOrderStore(item) || [];
            if (slices.length) {
                slices.forEach(function (slice) {
                    const seed = Object.assign({}, item, slice, {
                        type: slice.typeLabel || slice.transferType || item.transferType || item.type,
                        transferType: slice.transferType || item.transferType,
                        sell: slice.unitSell != null ? slice.unitSell : slice.adultSell,
                        unitSell: slice.unitSell,
                        lineSell: slice.lineSell,
                        adults: slice.adults,
                        adultsQty: slice.adultsQty != null ? slice.adultsQty : slice.adults,
                        child: slice.child,
                        childQty: slice.childQty != null ? slice.childQty : slice.child,
                        vehicleQty: slice.qty || 1,
                        vehicleName: slice.vehicleName
                    });
                    const resolved = window.enquiryProResolveTransferSellBase(seed);
                    transferBase += resolved.base;
                    lines += transferItemLinesHtml(resolved, cur);
                    metaSeed = seed;
                });
            }
        }
        if (!(transferBase > 0) && !lines) {
            const resolved = window.enquiryProResolveTransferSellBase(item);
            transferBase = resolved.base;
            lines = transferItemLinesHtml(resolved, cur);
            metaSeed = Object.assign({}, item, resolved);
        }

        let guideBase = 0;
        let guide = null;
        if (item.guideId && typeof guideList !== 'undefined' && Array.isArray(guideList)) {
            guide = guideList.find(function (g) { return String(g.id) === String(item.guideId); }) || null;
        }
        if (!guide && item.guideInfo) guide = item.guideInfo;
        if (!guide && item.guide_options) guide = item.guide_options;
        if (guide) {
            guideBase = window.enquiryProResolveGuideSellBase(guide);
            lines += lineHtml(
                '<strong>Guide</strong> ' + esc(guide.guideName || guide.name || guide.guide_name || ''),
                guideBase,
                cur
            );
        } else if (item.guideSell) {
            guideBase = num(item.guideSell);
            lines += lineHtml('<strong>Guide</strong>', guideBase, cur);
        }

        // Markup on transfer base and guide base separately (Lite: each service marked), then show combined total
        const transferMk = window.enquiryProApplyDmcMarkup(transferBase, 'other');
        const guideMk = guideBase > 0 ? window.enquiryProApplyDmcMarkup(guideBase, 'other') : null;
        const combinedBase = transferBase + guideBase;
        const combinedFinal = transferMk.price + (guideMk ? guideMk.price : 0);
        const combinedMkAmt = transferMk.markup_amount + (guideMk ? guideMk.markup_amount : 0);
        const applied = {
            base_price: Math.round(combinedBase * 100) / 100,
            markup_amount: Math.round(combinedMkAmt * 100) / 100,
            price: Math.round(combinedFinal * 100) / 100,
            markup_type: transferMk.markup_type,
            markup_value: transferMk.markup_value,
            markup_kind: 'other',
            dmc_id: transferMk.dmc_id
        };
        const resolvedMeta = window.enquiryProResolveTransferSellBase(metaSeed);

        return {
            title: label + ' — Price breakdown',
            html: transferMetaHtml(Object.assign({}, item, {
                pickup: item.portName || item.pickup,
                dropoff: item.transferDestinationName || item.dropoff
            }, metaSeed), resolvedMeta)
                + '<div class="ep-pd-section-label">Items</div><div class="ep-pd-summary" style="margin-bottom:10px;">'
                + lines + '</div>'
                + '<div class="ep-pd-section-label">Summary</div><div class="ep-pd-summary">'
                + window.enquiryProDmcMarkupSummaryHtml(applied, cur) + '</div>',
            applied: applied
        };
    }

    window.enquiryProOpenServicePriceBreakdown = function (kind, index) {
        kind = String(kind || '').toLowerCase();
        let built = null;
        if (kind === 'tour' || kind === 'attraction') {
            const list = (typeof tourList !== 'undefined' && Array.isArray(tourList)) ? tourList : [];
            built = list[index] ? buildTourBreakdown(list[index]) : null;
        } else if (kind === 'restaurant' || kind === 'meal') {
            const list = (typeof restaurantList !== 'undefined' && Array.isArray(restaurantList))
                ? restaurantList
                : ((typeof mealList !== 'undefined' && Array.isArray(mealList)) ? mealList : []);
            built = list[index] ? buildRestaurantBreakdown(list[index]) : null;
        } else if (kind === 'guide') {
            const list = (typeof guideList !== 'undefined' && Array.isArray(guideList)) ? guideList : [];
            built = list[index] ? buildGuideBreakdown(list[index]) : null;
        } else if (kind === 'transfer') {
            const list = (typeof transferList !== 'undefined' && Array.isArray(transferList)) ? transferList : [];
            built = list[index] ? buildTransferBreakdown(list[index]) : null;
        } else if (kind === 'arrival' || kind === 'departure' || kind === 'arrivaldeparture') {
            const list = (typeof arrivalDepartureList !== 'undefined' && Array.isArray(arrivalDepartureList))
                ? arrivalDepartureList
                : [];
            built = list[index] ? buildArrivalDepartureBreakdown(list[index]) : null;
        } else if (kind === 'misc' || kind === 'miscellaneous') {
            const list = (typeof miscList !== 'undefined' && Array.isArray(miscList)) ? miscList : [];
            built = list[index] ? buildMiscBreakdown(list[index]) : null;
        }
        if (!built) return;
        showServiceModal(built.title, built.html);
    };

    window.enquiryProPriceBreakBtnHtml = function (kind, index) {
        return '<br><button type="button" class="btn btn-link btn-sm p-0 ep-price-details-btn mt-1" '
            + 'onclick="enquiryProOpenServicePriceBreakdown(\'' + kind + '\',' + index + ')" '
            + 'title="View price breakdown with DMC markup">'
            + '<i class="ri-file-list-3-line"></i> View details</button>';
    };
})();
</script>
