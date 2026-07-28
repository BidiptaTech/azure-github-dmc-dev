<style>
    /* Split negotiate body: left negotiates, right profit — independent scrolls */
    #agentNegotiationModal .modal-dialog {
        max-width: 1100px;
    }
    #agentNegotiationModal .negotiation-split-body {
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(320px, 0.95fr);
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
        padding: 1rem 1rem 0.75rem;
        border-right: 1px solid #e2e8f0;
    }
    #agentNegotiationModal .negotiation-profit-scroll {
        padding: 0.85rem 0.85rem 0.75rem;
        background: #f8fafc;
    }
    .nego-profit-panel-title {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #475569;
        margin-bottom: 0.55rem;
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f8fafc;
        padding-bottom: 0.35rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .nego-profit-country {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 0.55rem 0.6rem;
        margin-bottom: 0.55rem;
    }
    .nego-profit-country-head {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 0.4rem;
        margin-bottom: 0.4rem;
    }
    .nego-profit-country-head strong {
        font-size: 0.82rem;
        color: #0f172a;
    }
    .nego-profit-country-head small {
        font-size: 0.68rem;
        color: #94a3b8;
    }
    .nego-profit-table {
        width: 100%;
        font-size: 0.72rem;
        margin-bottom: 0.4rem;
        border-collapse: collapse;
    }
    .nego-profit-table th,
    .nego-profit-table td {
        padding: 0.28rem 0.3rem;
        border: 1px solid #e2e8f0;
        vertical-align: middle;
    }
    .nego-profit-table th {
        font-size: 0.62rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        color: #64748b;
        background: #f8fafc;
        white-space: nowrap;
    }
    .nego-profit-service {
        font-weight: 600;
        color: #334155;
    }
    .nego-profit-meta {
        display: block;
        font-size: 0.6rem;
        color: #94a3b8;
    }
    .nego-profit-segments {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.35rem;
    }
    .nego-profit-segment {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.4rem;
        padding: 0.35rem 0.4rem;
    }
    .nego-profit-segment-title {
        font-size: 0.6rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #64748b;
        margin-bottom: 0.15rem;
    }
    .nego-profit-segment .nego-profit-val {
        font-size: 0.8rem;
        font-weight: 700;
        line-height: 1.15;
    }
    .nego-profit-empty {
        font-size: 0.78rem;
        color: #94a3b8;
        padding: 0.75rem 0.25rem;
    }
    @media (max-width: 991.98px) {
        #agentNegotiationModal .negotiation-split-body {
            grid-template-columns: 1fr;
            max-height: none;
        }
        #agentNegotiationModal .negotiation-main-scroll,
        #agentNegotiationModal .negotiation-profit-scroll {
            max-height: 42vh;
            border-right: 0;
        }
        #agentNegotiationModal .negotiation-profit-scroll {
            border-top: 1px solid #e2e8f0;
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
                            '<td class="text-end">' + escapeNegotiationHtml(currency) + ' ' + formatNegotiationProfitNumber(sell) + '</td>' +
                            '<td class="text-end">' + escapeNegotiationHtml(currency) + ' ' + formatNegotiationProfitNumber(cost) + '</td>' +
                            '<td class="text-end ' + (profit >= 0 ? 'text-success' : 'text-danger') + '">' +
                                (profit >= 0 ? '+' : '') + escapeNegotiationHtml(currency) + ' ' + formatNegotiationProfitNumber(profit) +
                            '</td>' +
                        '</tr>';
                }).join('');

                return '' +
                    '<div class="nego-profit-country">' +
                        '<div class="nego-profit-country-head">' +
                            '<strong>' + escapeNegotiationHtml(country) + ' (' + escapeNegotiationHtml(currency) + ')</strong>' +
                            '<small>' + services.length + ' service(s)</small>' +
                        '</div>' +
                        '<table class="nego-profit-table">' +
                            '<thead><tr>' +
                                '<th>Product</th>' +
                                '<th class="text-end">Sell</th>' +
                                '<th class="text-end">Cost</th>' +
                                '<th class="text-end">P/L</th>' +
                            '</tr></thead>' +
                            '<tbody>' + rows + '</tbody>' +
                        '</table>' +
                        '<div class="nego-profit-segments">' +
                            '<div class="nego-profit-segment">' +
                                '<div class="nego-profit-segment-title">Country Profit / Loss</div>' +
                                '<div class="nego-profit-val ' + (totalProfit >= 0 ? 'text-success' : 'text-danger') + '">' +
                                    (totalProfit >= 0 ? '+' : '') + escapeNegotiationHtml(currency) + ' ' + formatNegotiationProfitNumber(totalProfit) +
                                '</div>' +
                                '<small class="text-muted" style="font-size:0.62rem;">Sell ' + formatNegotiationProfitNumber(totalSell) + ' · Cost ' + formatNegotiationProfitNumber(totalCost) + '</small>' +
                            '</div>' +
                            '<div class="nego-profit-segment">' +
                                '<div class="nego-profit-segment-title">Profit Margin</div>' +
                                '<div class="nego-profit-val ' + (totalMargin >= 0 ? 'text-info' : 'text-danger') + '">' +
                                    formatNegotiationProfitNumber(totalMargin) + '%' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>';
            }).join('');

            panel.innerHTML =
                '<div class="nego-profit-panel-title">Country-wise profit view</div>' +
                countriesHtml;
        } catch (error) {
            console.error('Failed to render negotiation profit panel', error);
            panel.innerHTML = '<div class="nego-profit-empty">Unable to load profit view.</div>';
        }
    }
</script>
