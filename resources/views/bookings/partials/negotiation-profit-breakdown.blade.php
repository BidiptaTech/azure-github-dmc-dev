<style>
    /* Split negotiate body: left negotiates, right profit — right grows with content */
    #agentNegotiationModal .modal-dialog {
        width: calc(100% - 2rem);
        max-width: 1050px;
    }
    #agentNegotiationModal .negotiation-split-body {
        display: flex;
        align-items: stretch;
        gap: 0;
        max-height: min(72vh, 640px);
        overflow: hidden;
        padding: 0 !important;
    }
    #agentNegotiationModal .negotiation-main-scroll,
    #agentNegotiationModal .negotiation-profit-scroll {
        overflow-y: auto;
        overflow-x: hidden;
        max-height: min(72vh, 640px);
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }
    #agentNegotiationModal .negotiation-main-scroll {
        flex: 1 1 0;
        min-width: 0;
        padding: 1.1rem 1.15rem 0.9rem;
        border-right: 1px solid #e5e9f0;
        background: #fff;
    }
    #agentNegotiationModal .negotiation-profit-scroll {
        flex: 0 0 auto;
        width: max-content;
        min-width: 300px;
        max-width: min(44vw, 460px);
        padding: 0 0.9rem 0.9rem;
        background: #f7f9fc;
    }

    /* Slim, unobtrusive scrollbars */
    #agentNegotiationModal .negotiation-main-scroll::-webkit-scrollbar,
    #agentNegotiationModal .negotiation-profit-scroll::-webkit-scrollbar {
        width: 8px;
    }
    #agentNegotiationModal .negotiation-main-scroll::-webkit-scrollbar-thumb,
    #agentNegotiationModal .negotiation-profit-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }
    #agentNegotiationModal .negotiation-main-scroll::-webkit-scrollbar-track,
    #agentNegotiationModal .negotiation-profit-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    /* Left: country offer cards */
    #agentNegotiationModal .negotiation-pricing-summary {
        position: relative;
        background: #fff;
        border: 1px solid #e5e9f0;
        border-radius: 0.7rem;
        padding: 0.9rem 1rem;
        margin-bottom: 0.85rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
        transition: box-shadow 0.15s ease, border-color 0.15s ease;
    }
    #agentNegotiationModal .negotiation-pricing-summary::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0.7rem;
        bottom: 0.7rem;
        width: 3px;
        border-radius: 0 3px 3px 0;
        background: #4f46e5;
    }
    #agentNegotiationModal .negotiation-pricing-summary:focus-within {
        border-color: #c7d2fe;
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.1);
    }
    #agentNegotiationModal .negotiation-pricing-summary > .d-flex strong {
        font-size: 0.92rem;
        color: #0f172a;
        letter-spacing: -0.01em;
    }
    #agentNegotiationModal .negotiation-pricing-summary .negotiation-label {
        color: #7c879b;
        font-size: 0.66rem;
        letter-spacing: 0.06em;
    }
    #agentNegotiationModal .negotiation-pricing-summary .negotiation-value {
        font-variant-numeric: tabular-nums;
        font-size: 0.94rem;
        color: #1e293b;
    }
    #agentNegotiationModal .negotiation-pricing-summary .form-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #475569;
        margin-bottom: 0.3rem;
    }
    #agentNegotiationModal .agent-nego-offer-input,
    #agentNegotiationModal .dmc-nego-offer-input {
        border: 1px solid #d5dbe6;
        border-radius: 0.5rem;
        background: #fbfcfe;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        color: #0f172a;
    }
    #agentNegotiationModal .agent-nego-offer-input:focus,
    #agentNegotiationModal .dmc-nego-offer-input:focus {
        border-color: #4f46e5;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
    }
    #agentNegotiationModal .agent-nego-offer-input.is-invalid,
    #agentNegotiationModal .dmc-nego-offer-input.is-invalid {
        border-color: #dc2626;
        background: #fef2f2;
        color: #b91c1c;
    }
    #agentNegotiationModal .agent-nego-offer-input.is-invalid:focus,
    #agentNegotiationModal .dmc-nego-offer-input.is-invalid:focus {
        border-color: #dc2626;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15);
    }
    #agentNegotiationModal .agent-nego-offer-error {
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.35;
    }

    /* Right: margin panel */
    .nego-profit-panel-title {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.09em;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 0.45rem;
        margin: 0 -0.9rem 0.7rem;
        padding: 0.85rem 0.9rem 0.6rem;
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f7f9fc;
        border-bottom: 1px solid #e5e9f0;
    }
    .nego-profit-panel-title::before {
        content: '';
        width: 3px;
        height: 0.85rem;
        border-radius: 999px;
        background: #4f46e5;
    }
    .nego-profit-country {
        width: max-content;
        min-width: 100%;
        box-sizing: border-box;
        background: #fff;
        border: 1px solid #e5e9f0;
        border-radius: 0.6rem;
        padding: 0.7rem 0.75rem 0.75rem;
        margin-bottom: 0.7rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    .nego-profit-country-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.55rem;
    }
    .nego-profit-country-head strong {
        font-size: 0.82rem;
        font-weight: 600;
        color: #0f172a;
        letter-spacing: -0.01em;
    }
    .nego-profit-country-head small {
        font-size: 0.62rem;
        font-weight: 600;
        color: #64748b;
        background: #f1f5f9;
        border: 1px solid #e5e9f0;
        padding: 0.1rem 0.45rem;
        border-radius: 999px;
        white-space: nowrap;
    }
    .nego-profit-table {
        width: max-content;
        min-width: 100%;
        font-size: 0.7rem;
        margin-bottom: 0.6rem;
        border-collapse: collapse;
    }
    .nego-profit-table th,
    .nego-profit-table td {
        padding: 0.38rem 0.45rem;
        border: 0;
        border-bottom: 1px solid #eef1f6;
        vertical-align: middle;
    }
    .nego-profit-table th {
        font-size: 0.58rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: #8894a8;
        background: transparent;
        border-bottom: 1px solid #e5e9f0;
        white-space: nowrap;
        padding-top: 0;
    }
    .nego-profit-table tbody tr:last-child td {
        border-bottom: 0;
    }
    .nego-profit-table tbody tr:hover td {
        background: #fafbfd;
    }
    .nego-profit-table td:not(:first-child) {
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
        color: #475569;
    }
    .nego-profit-table th:first-child,
    .nego-profit-table td:first-child {
        min-width: 7.5rem;
        max-width: 11rem;
    }
    .nego-profit-service {
        font-weight: 600;
        color: #1e293b;
        display: block;
        line-height: 1.25;
        overflow-wrap: anywhere;
    }
    .nego-profit-meta {
        display: block;
        font-size: 0.6rem;
        color: #94a3b8;
        text-transform: capitalize;
    }
    .nego-profit-segments {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.45rem;
    }
    .nego-profit-segment {
        min-width: 0;
        border-radius: 0.5rem;
        padding: 0.5rem 0.55rem;
        background: #f8fafc;
        border: 1px solid #e5e9f0;
        border-left: 3px solid #cbd5e1;
    }
    .nego-profit-segment.is-profit {
        background: #f2fbf6;
        border-color: #d7efe1;
        border-left-color: #16a34a;
    }
    .nego-profit-segment.is-loss {
        background: #fdf4f4;
        border-color: #f5dcdc;
        border-left-color: #dc2626;
    }
    .nego-profit-segment-title {
        font-size: 0.58rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #7c879b;
        margin-bottom: 0.2rem;
    }
    .nego-profit-segment .nego-profit-val {
        font-size: 0.84rem;
        font-weight: 700;
        line-height: 1.15;
        font-variant-numeric: tabular-nums;
        letter-spacing: -0.01em;
        white-space: nowrap;
    }
    .nego-profit-segment small {
        display: block;
        margin-top: 0.15rem;
        font-variant-numeric: tabular-nums;
    }
    .nego-profit-pos {
        color: #15803d !important;
        font-weight: 700;
    }
    .nego-profit-neg {
        color: #dc2626 !important;
        font-weight: 700;
    }
    .nego-profit-empty {
        font-size: 0.78rem;
        color: #64748b;
        padding: 1.1rem 0.75rem;
        background: #fff;
        border: 1px dashed #d5dbe6;
        border-radius: 0.55rem;
        text-align: center;
    }
    @media (max-width: 991.98px) {
        #agentNegotiationModal .negotiation-split-body {
            flex-direction: column;
            max-height: none;
        }
        #agentNegotiationModal .negotiation-main-scroll,
        #agentNegotiationModal .negotiation-profit-scroll {
            max-height: 42vh;
            border-right: 0;
            width: 100%;
            max-width: none;
        }
        #agentNegotiationModal .negotiation-profit-scroll {
            border-top: 1px solid #e5e9f0;
        }
    }
</style>
<script>
    function escapeNegotiationHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatNegotiationProfitNumber(value) {
        if (typeof formatNegotiationAmount === 'function') {
            return formatNegotiationAmount(value);
        }
        const number = Number(value || 0);
        return Number.isFinite(number) ? number.toFixed(2) : '0.00';
    }

    function negotiationProfitKey(country, currency) {
        return String(country || '').trim().toLowerCase() + '|' + String(currency || '').trim().toUpperCase();
    }

    function applyNegotiationMarginTone(el, isProfit) {
        if (!el) return;
        el.classList.remove('is-profit', 'is-loss', 'nego-profit-pos', 'nego-profit-neg');
        el.classList.add(isProfit ? 'is-profit' : 'is-loss');
    }

    function applyNegotiationValueTone(el, isProfit) {
        if (!el) return;
        el.classList.remove('nego-profit-pos', 'nego-profit-neg', 'text-success', 'text-danger', 'text-info');
        el.classList.add(isProfit ? 'nego-profit-pos' : 'nego-profit-neg');
    }

    /**
     * Recalculate Country Margin / Margin % from left-side Offer Amount inputs.
     * Margin = offer - cost; Margin % = (offer - cost) / offer * 100.
     */
    function syncAgentNegotiationProfitFromOffers() {
        const panel = document.getElementById('agentNegotiationProfitPanel');
        if (!panel) return;

        const offerByKey = {};
        document.querySelectorAll('#agentNegotiationCountryBlocks .agent-nego-offer-input').forEach(function (input) {
            const key = negotiationProfitKey(input.getAttribute('data-country'), input.getAttribute('data-currency'));
            const amount = Number(input.value);
            offerByKey[key] = Number.isFinite(amount) ? amount : 0;
        });

        panel.querySelectorAll('.nego-profit-country[data-nego-key]').forEach(function (card) {
            const key = card.getAttribute('data-nego-key');
            const cost = Number(card.getAttribute('data-cost') || 0);
            if (!Object.prototype.hasOwnProperty.call(offerByKey, key)) {
                return;
            }

            const offer = offerByKey[key];
            const profit = offer - cost;
            const marginPct = offer > 0 ? (profit / offer) * 100 : 0;
            const isProfit = profit >= 0;
            const isMarginPos = marginPct >= 0;

            const marginSeg = card.querySelector('[data-role="country-margin-seg"]');
            const marginVal = card.querySelector('[data-role="country-margin-val"]');
            const marginMeta = card.querySelector('[data-role="country-margin-meta"]');
            const pctSeg = card.querySelector('[data-role="margin-pct-seg"]');
            const pctVal = card.querySelector('[data-role="margin-pct-val"]');

            applyNegotiationMarginTone(marginSeg, isProfit);
            applyNegotiationValueTone(marginVal, isProfit);
            if (marginVal) {
                marginVal.textContent = (profit >= 0 ? '+' : '') + formatNegotiationProfitNumber(profit);
            }
            if (marginMeta) {
                marginMeta.textContent = 'Offer ' + formatNegotiationProfitNumber(offer) + ' · Cost ' + formatNegotiationProfitNumber(cost);
            }

            applyNegotiationMarginTone(pctSeg, isMarginPos);
            applyNegotiationValueTone(pctVal, isMarginPos);
            if (pctVal) {
                pctVal.textContent = (marginPct >= 0 ? '+' : '') + formatNegotiationProfitNumber(marginPct) + '%';
            }
        });
    }

    /** Fill the independent right-side profit panel (does not alter negotiate form cards). */
    function renderAgentNegotiationProfitPanel(countryGroups) {
        const panel = document.getElementById('agentNegotiationProfitPanel');
        if (!panel) {
            return;
        }

        try {
            const groups = Array.isArray(countryGroups) ? countryGroups : [];
            const withServices = groups.filter(function (g) {
                return Array.isArray(g.services) && g.services.length > 0;
            });

            if (!withServices.length) {
                panel.innerHTML = '<div class="nego-profit-empty">No booked service sell/cost data for this tour.</div>';
                return;
            }

            const countriesHtml = withServices.map(function (group) {
                const currency = String(group.currency || '').trim();
                const country = String(group.country || currency || 'Country');
                const totalSell = Number(group.sell_total || 0);
                const totalCost = Number(group.cost_total || 0);
                const totalProfit = Number(group.profit_total || 0);
                const totalMargin = Number(group.margin_total || 0);
                const services = Array.isArray(group.services) ? group.services : [];
                const negoKey = negotiationProfitKey(country, currency);

                const rows = services.map(function (service) {
                    const sell = Number(service.sell || 0);
                    const cost = Number(service.cost || 0);
                    const profit = Number(service.profit != null ? service.profit : (sell - cost));
                    const type = service.type ? String(service.type).replace(/_/g, ' ') : '';
                    const count = Number(service.count || 1);

                    return '' +
                        '<tr>' +
                            '<td>' +
                                '<span class="nego-profit-service">' + escapeNegotiationHtml(service.service || 'Service') + '</span>' +
                                '<span class="nego-profit-meta">' +
                                    (type ? escapeNegotiationHtml(type) : 'service') +
                                    (count > 1 ? ' • ' + count + ' item(s)' : '') +
                                '</span>' +
                            '</td>' +
                            '<td class="text-end">' + formatNegotiationProfitNumber(sell) + '</td>' +
                            '<td class="text-end">' + formatNegotiationProfitNumber(cost) + '</td>' +
                            '<td class="text-end ' + (profit >= 0 ? 'nego-profit-pos' : 'nego-profit-neg') + '">' +
                                (profit >= 0 ? '+' : '') + formatNegotiationProfitNumber(profit) +
                            '</td>' +
                        '</tr>';
                }).join('');

                const profitTone = totalProfit >= 0 ? 'is-profit' : 'is-loss';
                const profitClass = totalProfit >= 0 ? 'nego-profit-pos' : 'nego-profit-neg';
                const marginClass = totalMargin >= 0 ? 'nego-profit-pos' : 'nego-profit-neg';

                return '' +
                    '<div class="nego-profit-country" data-nego-key="' + escapeNegotiationHtml(negoKey) + '" data-country="' + escapeNegotiationHtml(country) + '" data-currency="' + escapeNegotiationHtml(currency) + '" data-cost="' + totalCost + '" data-sell="' + totalSell + '">' +
                        '<div class="nego-profit-country-head">' +
                            '<strong>' + escapeNegotiationHtml(country) + ' (' + escapeNegotiationHtml(currency) + ')</strong>' +
                            '<small>' + services.length + ' service(s)</small>' +
                        '</div>' +
                        '<table class="nego-profit-table">' +
                            '<thead><tr>' +
                                '<th>Product</th>' +
                                '<th class="text-end">Sell</th>' +
                                '<th class="text-end">Cost</th>' +
                                '<th class="text-end">Margin</th>' +
                            '</tr></thead>' +
                            '<tbody>' + rows + '</tbody>' +
                        '</table>' +
                        '<div class="nego-profit-segments">' +
                            '<div class="nego-profit-segment ' + profitTone + '" data-role="country-margin-seg">' +
                                '<div class="nego-profit-segment-title">Country Margin</div>' +
                                '<div class="nego-profit-val ' + profitClass + '" data-role="country-margin-val">' +
                                    (totalProfit >= 0 ? '+' : '') + formatNegotiationProfitNumber(totalProfit) +
                                '</div>' +
                                '<small class="text-muted" style="font-size:0.62rem;" data-role="country-margin-meta">Offer ' + formatNegotiationProfitNumber(totalSell) + ' · Cost ' + formatNegotiationProfitNumber(totalCost) + '</small>' +
                            '</div>' +
                            '<div class="nego-profit-segment ' + (totalMargin >= 0 ? 'is-profit' : 'is-loss') + '" data-role="margin-pct-seg">' +
                                '<div class="nego-profit-segment-title">Margin %</div>' +
                                '<div class="nego-profit-val ' + marginClass + '" data-role="margin-pct-val">' +
                                    (totalMargin >= 0 ? '+' : '') + formatNegotiationProfitNumber(totalMargin) + '%' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>';
            }).join('');

            panel.innerHTML =
                '<div class="nego-profit-panel-title">Country-wise Margin View</div>' +
                countriesHtml;

            // Align right panel with current offer inputs (if already rendered)
            if (typeof syncAgentNegotiationProfitFromOffers === 'function') {
                syncAgentNegotiationProfitFromOffers();
            }
        } catch (error) {
            console.error('Failed to render negotiation margin panel', error);
            panel.innerHTML = '<div class="nego-profit-empty">Unable to load margin view.</div>';
        }
    }
</script>
