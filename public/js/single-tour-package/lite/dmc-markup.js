/* === STP LITE: dmc-markup.js ===
 * Operating DMC profile markup_json (hotel / other) for lite Get Price UIs.
 * Depends: STP_LITE_CONFIG.dmcProfileMarkup
 * === */
(function (window) {
    'use strict';

    function cfg() {
        return window.STP_LITE_CONFIG || {};
    }

    function ruleFor(kind) {
        var profile = cfg().dmcProfileMarkup || {};
        var key = String(kind || 'other').toLowerCase() === 'hotel' ? 'hotel' : 'other';
        var rule = profile[key] || {};
        var type = String(rule.markup_type || 'percentage').toLowerCase();
        if (type === 'fixed') type = 'flat';
        if (type !== 'flat' && type !== 'percentage') type = 'percentage';
        return {
            markup_type: type,
            markup_value: Math.max(0, Number(rule.markup_value) || 0),
            dmc_id: profile.dmc_id || null,
            markup_kind: key
        };
    }

    function computeAmount(basePrice, rule) {
        basePrice = Math.max(0, Number(basePrice) || 0);
        rule = rule || ruleFor('other');
        var val = Math.max(0, Number(rule.markup_value) || 0);
        if (val <= 0) {
            return 0;
        }
        if (String(rule.markup_type || '').toLowerCase() === 'flat') {
            return Math.round(val * 100) / 100;
        }
        return Math.round((basePrice * val / 100) * 100) / 100;
    }

    /**
     * @param {number} basePrice
     * @param {'hotel'|'other'} kind
     * @returns {{base_price:number, markup_amount:number, price:number, markup_type:string, markup_value:number, markup_kind:string, dmc_id:*}}
     */
    function apply(basePrice, kind) {
        var rule = ruleFor(kind);
        var base = Math.max(0, Number(basePrice) || 0);
        var amount = computeAmount(base, rule);
        return {
            base_price: Math.round(base * 100) / 100,
            markup_amount: amount,
            price: Math.round((base + amount) * 100) / 100,
            markup_type: rule.markup_type,
            markup_value: rule.markup_value,
            markup_kind: rule.markup_kind,
            dmc_id: rule.dmc_id
        };
    }

    /** Split a final (already marked) price back into base + markup for display. */
    function splitFromFinal(finalPrice, kind) {
        var rule = ruleFor(kind);
        var finalAmt = Math.max(0, Number(finalPrice) || 0);
        var val = Math.max(0, Number(rule.markup_value) || 0);
        if (val <= 0 || finalAmt <= 0) {
            return {
                base_price: finalAmt,
                markup_amount: 0,
                price: finalAmt,
                markup_type: rule.markup_type,
                markup_value: rule.markup_value,
                markup_kind: rule.markup_kind,
                dmc_id: rule.dmc_id
            };
        }
        var amount = 0;
        var base = finalAmt;
        if (String(rule.markup_type || '').toLowerCase() === 'flat') {
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
            markup_type: rule.markup_type,
            markup_value: rule.markup_value,
            markup_kind: rule.markup_kind,
            dmc_id: rule.dmc_id
        };
    }

    function moneyTxt(cur, amount) {
        return String(cur || 'SGD').trim() + ' ' + Number(amount || 0).toFixed(2);
    }

    function typeLabel(applied, cur) {
        applied = applied || {};
        if (String(applied.markup_type || '').toLowerCase() === 'flat') {
            return 'Flat ' + moneyTxt(cur, applied.markup_value || 0);
        }
        return String(Number(applied.markup_value || 0)) + '%';
    }

    /** Summary rows: Price before markup + Markup Amount (same as hotel). */
    function summaryRowsHtml(applied, cur) {
        applied = applied || apply(0, 'other');
        if (!(Number(applied.markup_amount || 0) > 0) && !(Number(applied.markup_value || 0) > 0)) {
            return '';
        }
        return (
            '<div class="stp-lite-summary-row is-base">' +
            '<span class="stp-lite-summary-row__left"><strong>Price (before markup)</strong></span>' +
            '<strong class="stp-lite-summary-row__amt">' + moneyTxt(cur, applied.base_price) + '</strong></div>' +
            '<div class="stp-lite-summary-row is-markup">' +
            '<span class="stp-lite-summary-row__left"><strong>Markup Amount</strong> <small class="text-muted">(' + typeLabel(applied, cur) + ')</small></span>' +
            '<strong class="stp-lite-summary-row__amt">' + moneyTxt(cur, applied.markup_amount) + '</strong></div>'
        );
    }

    /** Footer totals block used by price panels / modals. */
    function totalsHtml(applied, cur) {
        applied = applied || apply(0, 'other');
        var html = '<div class="stp-lite-hotel-breakup-totals">';
        if (Number(applied.markup_amount || 0) > 0) {
            html +=
                '<div class="stp-lite-hotel-breakup-total is-markup">' +
                '<span><strong>Markup Amount:</strong></span>' +
                '<strong>' + moneyTxt(cur, applied.markup_amount) + '</strong></div>';
        }
        html +=
            '<div class="stp-lite-hotel-breakup-total">' +
            '<span><strong>Total Price:</strong></span>' +
            '<strong>' + moneyTxt(cur, applied.price) + '</strong></div></div>';
        return html;
    }

    /**
     * Fill a service Get Price panel (attraction/restaurant/guide/…).
     * @returns applied markup result
     */
    function fillPricePanel(root, prefix, currency, baseTotal, detailHtml) {
        var applied = apply(baseTotal, 'other');
        var cur = String(currency || 'SGD').trim() || 'SGD';
        var panel = root && root.querySelector('[data-' + prefix + '-price-panel]');
        var totalEl = root && root.querySelector('.' + prefix + '-price-total');
        var detailEl = root && root.querySelector('.' + prefix + '-price-detail');
        var markupEl = root && root.querySelector('.' + prefix + '-price-markup');
        var markupRow = root && root.querySelector('.' + prefix + '-price-markup-row');

        if (panel) panel.classList.remove('d-none');
        if (totalEl) totalEl.textContent = moneyTxt(cur, applied.price);
        if (markupEl) markupEl.textContent = moneyTxt(cur, applied.markup_amount);
        if (markupRow) markupRow.classList.toggle('d-none', !(Number(applied.markup_amount || 0) > 0));
        if (detailEl) {
            detailEl.innerHTML = String(detailHtml || '') + summaryRowsHtml(applied, cur);
        }
        return applied;
    }

    window.StpLiteDmcMarkup = {
        ruleFor: ruleFor,
        computeAmount: computeAmount,
        apply: apply,
        splitFromFinal: splitFromFinal,
        summaryRowsHtml: summaryRowsHtml,
        totalsHtml: totalsHtml,
        fillPricePanel: fillPricePanel,
        moneyTxt: moneyTxt
    };
})(window);
/* === END dmc-markup.js === */
