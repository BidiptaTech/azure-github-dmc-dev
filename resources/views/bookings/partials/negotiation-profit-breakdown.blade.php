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

    /* Single profile markup + agency discount strip (not per-country) */
    .nego-profile-markup-once {
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
        border-radius: 0.7rem;
        padding: 0.75rem 0.95rem;
        margin-bottom: 0.85rem;
    }
    .nego-profile-markup-once__head {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        justify-content: space-between;
        gap: 0.35rem 0.75rem;
        margin-bottom: 0.65rem;
    }
    .nego-profile-markup-once__title {
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #334155;
    }
    .nego-profile-markup-once__hint {
        font-size: 0.72rem;
        color: #64748b;
    }
    .nego-profile-markup-once__grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 0.65rem;
    }
    @media (max-width: 720px) {
        .nego-profile-markup-once__grid { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 520px) {
        .nego-profile-markup-once__grid { grid-template-columns: 1fr; }
    }
    .nego-profile-markup-once__cell {
        background: #fff;
        border: 1px solid #e5e9f0;
        border-radius: 0.55rem;
        padding: 0.55rem 0.7rem;
    }
    .nego-profile-markup-once__cell.is-discount {
        border-color: #bbf7d0;
        background: #f0fdf4;
    }
    .nego-profile-markup-once__cell .negotiation-label {
        display: block;
        margin-bottom: 0.2rem;
    }
    .nego-profile-markup-once__value {
        display: flex;
        align-items: baseline;
        gap: 0.35rem;
        font-variant-numeric: tabular-nums;
    }
    .nego-profile-markup-once__value strong {
        font-size: 1.15rem;
        color: #0f172a;
        letter-spacing: -0.02em;
    }
    .nego-profile-markup-once__suffix {
        font-size: 0.78rem;
        font-weight: 600;
        color: #64748b;
    }
    .nego-profile-markup-once__cell .input-group-sm > .form-control {
        font-weight: 600;
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
    .nego-split-bases .negotiation-value {
        font-size: 0.92rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        color: #0f172a;
    }
    .nego-add-amt {
        font-size: 0.72rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        color: #0f766e;
        margin-top: 0.22rem;
        min-height: 1rem;
    }
    .nego-add-amt.is-zero {
        color: #94a3b8;
        font-weight: 600;
    }
    .nego-split-note {
        font-size: 0.66rem;
        color: #94a3b8;
        margin-top: 0.15rem;
        line-height: 1.25;
    }
    .nego-markup-disabled,
    .negotiation-pricing-summary input[type="number"]:disabled {
        background: #f1f5f9 !important;
        color: #94a3b8 !important;
        cursor: not-allowed;
        opacity: 1;
    }
    .nego-calc-summary {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.45rem 0.75rem;
        background: #f8fafc;
        border: 1px solid #e8edf5;
        border-radius: 0.5rem;
        padding: 0.55rem 0.7rem;
        margin-bottom: 0.7rem;
    }
    .nego-calc-summary .nego-calc-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 0.5rem;
    }
    .nego-calc-summary .nego-calc-row span:first-child {
        font-size: 0.66rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #7c879b;
    }
    .nego-calc-summary .nego-calc-row span:last-child {
        font-size: 0.82rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        color: #0f172a;
    }
    .nego-calc-summary .nego-calc-row.is-discount span:last-child {
        color: #dc2626;
    }
    .nego-calc-summary .nego-calc-row.is-markup span:last-child {
        color: #0f766e;
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
    {{-- DMC profile markup_json (hotel / other) — negotiation fields are read-only from this --}}
    window.STP_DMC_PROFILE_MARKUP = @json(\App\Helpers\CommonHelper::getDmcProfileMarkupConfig(auth()->user()));

    window.getNegotiationProfileMarkup = function () {
        var cfg = window.STP_DMC_PROFILE_MARKUP || {};
        var hotel = cfg.hotel || {};
        var other = cfg.other || {};
        var normType = function (t) {
            t = String(t || 'percentage').toLowerCase();
            if (t === 'fixed') t = 'flat';
            return t === 'flat' ? 'flat' : 'percentage';
        };
        return {
            hotelType: normType(hotel.markup_type),
            otherType: normType(other.markup_type),
            hotelRaw: Math.max(0, Number(hotel.markup_value) || 0),
            otherRaw: Math.max(0, Number(other.markup_value) || 0),
            dmcId: cfg.dmc_id || null
        };
    };

    /** Resolve profile markup + display suffixes (once for all countries). */
    window.resolveNegotiationMarkupFromProfile = function (currency) {
        var p = window.getNegotiationProfileMarkup();
        var cur = String(currency || '').trim() || 'AMT';
        return {
            hotelType: p.hotelType,
            otherType: p.otherType,
            hotelRaw: p.hotelRaw,
            otherRaw: p.otherRaw,
            hotelSuffix: p.hotelType === 'percentage' ? '%' : 'Flat',
            otherSuffix: p.otherType === 'percentage' ? '%' : 'Flat',
            hotelMoneySuffix: p.hotelType === 'percentage' ? '%' : cur,
            otherMoneySuffix: p.otherType === 'percentage' ? '%' : cur,
            markupType: p.hotelType
        };
    };

    /**
     * Prefer live DMC profile markup; fall back to server country-group values
     * (tour DMC) when the session has no operating DMC profile.
     */
    window.resolveNegotiationMarkupForGroup = function (group, currency) {
        group = group || {};
        var cfg = window.STP_DMC_PROFILE_MARKUP || {};
        if (cfg && Number(cfg.dmc_id || 0) > 0 && typeof window.resolveNegotiationMarkupFromProfile === 'function') {
            return window.resolveNegotiationMarkupFromProfile(currency);
        }
        var hotelType = String(group.hotel_markup_type || group.markup_type || 'percentage').toLowerCase();
        var otherType = String(group.other_markup_type || group.markup_type || 'percentage').toLowerCase();
        if (hotelType === 'fixed') hotelType = 'flat';
        if (otherType === 'fixed') otherType = 'flat';
        if (hotelType !== 'flat') hotelType = 'percentage';
        if (otherType !== 'flat') otherType = 'percentage';
        var cur = String(currency || '').trim() || 'AMT';
        return {
            hotelType: hotelType,
            otherType: otherType,
            hotelRaw: Math.max(0, Number(group.hotel_markup_raw != null ? group.hotel_markup_raw : (group.markup_raw || 0)) || 0),
            otherRaw: Math.max(0, Number(group.other_markup_raw != null ? group.other_markup_raw : 0) || 0),
            hotelSuffix: hotelType === 'percentage' ? '%' : 'Flat',
            otherSuffix: otherType === 'percentage' ? '%' : 'Flat',
            hotelMoneySuffix: hotelType === 'percentage' ? '%' : cur,
            otherMoneySuffix: otherType === 'percentage' ? '%' : cur,
            markupType: hotelType
        };
    };

    /**
     * Resolve once-discount defaults from country groups (agency special_discount).
     * Optional agentOffers can override with the latest negotiated discount.
     */
    window.resolveNegotiationDiscountOnce = function (groups, agentOffers) {
        var discountType = 'percentage';
        var discountRaw = 0;
        var first = (Array.isArray(groups) && groups.length) ? groups[0] : {};
        if (first && (first.discount_type || first.discount_raw != null || first.discount != null)) {
            discountType = String(first.discount_type || 'percentage').toLowerCase();
            discountRaw = Number(first.discount_raw != null ? first.discount_raw : (first.discount || 0)) || 0;
        }
        if (Array.isArray(agentOffers) && agentOffers.length) {
            for (var i = 0; i < agentOffers.length; i++) {
                var offer = agentOffers[i] || {};
                if (offer.discount_type != null || offer.discount_value != null) {
                    discountType = String(offer.discount_type || discountType || 'percentage').toLowerCase();
                    discountRaw = Number(offer.discount_value != null ? offer.discount_value : discountRaw) || 0;
                    break;
                }
            }
        }
        if (discountType === 'fixed') discountType = 'flat';
        if (discountType !== 'flat' && discountType !== 'foc') discountType = 'percentage';
        return {
            discountType: discountType,
            discountRaw: Math.max(0, discountRaw),
            discountSuffix: discountType === 'percentage' ? '%' : (discountType === 'foc' ? 'FOC' : 'Flat')
        };
    };

    window.readNegotiationProfileDiscountOnce = function (hostEl) {
        if (!hostEl) return null;
        var wrap = hostEl.querySelector('[data-nego-profile-markup]') || hostEl;
        var typeEl = wrap.querySelector('.nego-profile-discount-type');
        var inputEl = wrap.querySelector('.nego-profile-discount-input');
        if (!typeEl && !inputEl) return null;
        var discountType = String((typeEl && typeEl.value) || 'percentage').toLowerCase();
        if (discountType === 'fixed') discountType = 'flat';
        if (discountType !== 'flat' && discountType !== 'foc') discountType = 'percentage';
        var discountRaw = Math.max(0, parseFloat(inputEl && inputEl.value != null ? inputEl.value : 0) || 0);
        return { discountType: discountType, discountRaw: discountRaw };
    };

    /**
     * Apply once-discount to every country card (data attrs + hiddens), then invoke per-card sync.
     */
    window.applyNegotiationProfileDiscountOnceToCards = function (hostEl, cardsRoot, syncCardFn) {
        var disc = (typeof window.readNegotiationProfileDiscountOnce === 'function')
            ? window.readNegotiationProfileDiscountOnce(hostEl)
            : null;
        if (!disc || !cardsRoot) return disc;
        cardsRoot.querySelectorAll('.negotiation-pricing-summary').forEach(function (card) {
            var offerInput = card.querySelector('.dmc-nego-offer-input, .agent-nego-offer-input');
            if (offerInput) {
                offerInput.setAttribute('data-discount-type', disc.discountType);
            }
            var typeHidden = card.querySelector('input[name*="[discount_type]"]');
            if (typeHidden) typeHidden.value = disc.discountType;
            var valueHidden = card.querySelector('.dmc-nego-discount-hidden, .agent-nego-discount-hidden');
            if (valueHidden) valueHidden.value = String(disc.discountRaw);
            if (typeof syncCardFn === 'function') {
                syncCardFn(card);
            }
        });
        return disc;
    };

    /**
     * Render DMC profile hotel/other markup + editable agency discount once above country cards.
     * Returns { markup, discount }.
     */
    window.renderNegotiationProfileMarkupOnce = function (hostEl, groups, agentOffers) {
        if (!hostEl) return null;
        var first = (Array.isArray(groups) && groups.length) ? groups[0] : {};
        var currency = first.currency || '';
        var mk = (typeof window.resolveNegotiationMarkupForGroup === 'function')
            ? window.resolveNegotiationMarkupForGroup(first, currency)
            : { hotelType: 'percentage', otherType: 'percentage', hotelRaw: 0, otherRaw: 0, hotelSuffix: '%', otherSuffix: '%', markupType: 'percentage' };
        var disc = (typeof window.resolveNegotiationDiscountOnce === 'function')
            ? window.resolveNegotiationDiscountOnce(groups, agentOffers)
            : { discountType: 'percentage', discountRaw: 0, discountSuffix: '%' };
        var fmt = function (n) {
            return (typeof formatNegotiationAmount === 'function')
                ? formatNegotiationAmount(n)
                : Number(n || 0).toFixed(2);
        };
        var esc = function (s) {
            return String(s == null ? '' : s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        };
        var discountLabel = disc.discountType === 'percentage'
            ? 'Agency discount (%)'
            : (disc.discountType === 'foc' ? 'Agency discount (FOC)' : 'Agency discount');
        hostEl.innerHTML =
            '<div class="nego-profile-markup-once" data-nego-profile-markup>' +
            '  <div class="nego-profile-markup-once__head">' +
            '    <span class="nego-profile-markup-once__title">Profile pricing</span>' +
            '    <span class="nego-profile-markup-once__hint">Markup from DMC profile · Discount from agency (editable)</span>' +
            '  </div>' +
            '  <div class="nego-profile-markup-once__grid">' +
            '    <div class="nego-profile-markup-once__cell">' +
            '      <span class="negotiation-label">Hotel markup</span>' +
            '      <div class="nego-profile-markup-once__value">' +
            '        <strong class="nego-profile-hotel-display">' + esc(fmt(mk.hotelRaw)) + '</strong>' +
            '        <span class="nego-profile-markup-once__suffix">' + esc(mk.hotelSuffix) + '</span>' +
            '      </div>' +
            '    </div>' +
            '    <div class="nego-profile-markup-once__cell">' +
            '      <span class="negotiation-label">Other markup</span>' +
            '      <div class="nego-profile-markup-once__value">' +
            '        <strong class="nego-profile-other-display">' + esc(fmt(mk.otherRaw)) + '</strong>' +
            '        <span class="nego-profile-markup-once__suffix">' + esc(mk.otherSuffix) + '</span>' +
            '      </div>' +
            '    </div>' +
            '    <div class="nego-profile-markup-once__cell is-discount">' +
            '      <span class="negotiation-label">' + esc(discountLabel) + '</span>' +
            '      <div class="input-group input-group-sm">' +
            '        <input type="number" class="form-control nego-profile-discount-input" min="0" step="0.01" value="' + esc(String(disc.discountRaw)) + '">' +
            '        <span class="input-group-text">' + esc(disc.discountSuffix) + '</span>' +
            '      </div>' +
            '    </div>' +
            '  </div>' +
            '  <input type="hidden" class="nego-profile-hotel-raw" value="' + esc(String(mk.hotelRaw)) + '">' +
            '  <input type="hidden" class="nego-profile-other-raw" value="' + esc(String(mk.otherRaw)) + '">' +
            '  <input type="hidden" class="nego-profile-hotel-type" value="' + esc(mk.hotelType) + '">' +
            '  <input type="hidden" class="nego-profile-other-type" value="' + esc(mk.otherType) + '">' +
            '  <input type="hidden" class="nego-profile-markup-type" value="' + esc(mk.markupType) + '">' +
            '  <input type="hidden" class="nego-profile-discount-type" value="' + esc(disc.discountType) + '">' +
            '</div>';
        return { markup: mk, discount: disc };
    };

    /** Hotel sell vs other-service sell for split markup — prefer service SELL rows (margin view source of truth). */
    window.negotiationHotelOtherGross = function (group) {
        group = group || {};
        let hotel = 0;
        let other = 0;
        if (Array.isArray(group.services) && group.services.length) {
            group.services.forEach(function (service) {
                const sell = Number(service && service.sell ? service.sell : 0);
                if (!Number.isFinite(sell) || sell <= 0) return;
                if (String(service && service.type ? service.type : '').toLowerCase() === 'hotel') {
                    hotel += sell;
                } else {
                    other += sell;
                }
            });
            if (hotel > 0.009 || other > 0.009) {
                return {
                    hotelGross: hotel,
                    otherGross: other,
                    hasHotel: hotel > 0.009,
                    hasOther: other > 0.009
                };
            }
        }
        hotel = Number(group.hotel_gross);
        other = Number(group.other_gross);
        hotel = Number.isFinite(hotel) && hotel > 0 ? hotel : 0;
        other = Number.isFinite(other) && other > 0 ? other : 0;
        return {
            hotelGross: hotel,
            otherGross: other,
            hasHotel: hotel > 0.009,
            hasOther: other > 0.009
        };
    };

    /**
     * Hotel markup on hotel services only; other markup on non-hotel services only.
     * Discount on (hotel + other + both markups). Offer = ceil(gross + markup − discount).
     * hotelMarkupType / otherMarkupType may differ (DMC profile markup_json).
     */
    window.computeSplitNegotiationPricing = function (opts) {
        opts = opts || {};
        const hotelGross = Math.max(0, parseFloat(opts.hotelGross) || 0);
        const otherGross = Math.max(0, parseFloat(opts.otherGross) || 0);
        const gross = hotelGross + otherGross;
        const hasHotel = hotelGross > 0.009;
        const hasOther = otherGross > 0.009;
        const fallbackType = String(opts.markupType || 'flat').toLowerCase();
        let hotelType = String(opts.hotelMarkupType || fallbackType || 'flat').toLowerCase();
        let otherType = String(opts.otherMarkupType || fallbackType || 'flat').toLowerCase();
        if (hotelType === 'fixed') hotelType = 'flat';
        if (otherType === 'fixed') otherType = 'flat';
        const discountType = String(opts.discountType || 'flat').toLowerCase();
        const hotelRaw = hasHotel ? (parseFloat(opts.hotelRaw) || 0) : 0;
        const otherRaw = hasOther ? (parseFloat(opts.otherRaw) || 0) : 0;
        const discountRaw = parseFloat(opts.discountRaw) || 0;

        let hotelMoney = 0;
        let otherMoney = 0;
        if (hotelType === 'percentage') {
            hotelMoney = hotelGross * hotelRaw / 100;
        } else {
            hotelMoney = hasHotel ? hotelRaw : 0;
        }
        if (otherType === 'percentage') {
            otherMoney = otherGross * otherRaw / 100;
        } else {
            otherMoney = hasOther ? otherRaw : 0;
        }
        const markupMoney = hotelMoney + otherMoney;
        let discountMoney = 0;
        const discountBase = gross + markupMoney;
        if (discountType === 'percentage') {
            discountMoney = discountBase * discountRaw / 100;
        } else if (discountType === 'flat' || discountType === 'foc') {
            discountMoney = discountRaw;
        }
        const payable = Math.max(0, Math.ceil(gross + markupMoney - discountMoney));

        return {
            hotelGross: hotelGross,
            otherGross: otherGross,
            gross: gross,
            hasHotel: hasHotel,
            hasOther: hasOther,
            hotelMoney: hotelMoney,
            otherMoney: otherMoney,
            markupMoney: markupMoney,
            discountMoney: discountMoney,
            payable: payable
        };
    };

    window.applyNegotiationMarkupFieldState = function (card, prefix, hasHotel, hasOther) {
        if (!card) return;
        const hotelInput = card.querySelector('.' + prefix + '-hotel-markup');
        const otherInput = card.querySelector('.' + prefix + '-other-markup');
        if (hotelInput) {
            hotelInput.disabled = !hasHotel;
            hotelInput.readOnly = !!hasHotel;
            hotelInput.classList.toggle('bg-light', !!hasHotel);
            hotelInput.classList.toggle('nego-markup-disabled', !hasHotel);
            hotelInput.setAttribute('title', hasHotel
                ? 'From DMC profile markup (read-only)'
                : 'Disabled — no hotel services booked for this country');
        }
        if (otherInput) {
            otherInput.disabled = !hasOther;
            otherInput.readOnly = !!hasOther;
            otherInput.classList.toggle('bg-light', !!hasOther);
            otherInput.classList.toggle('nego-markup-disabled', !hasOther);
            otherInput.setAttribute('title', hasOther
                ? 'From DMC profile markup (read-only)'
                : 'Disabled — no other services booked for this country');
        }
        const hotelNote = card.querySelector('.' + prefix + '-hotel-note');
        const otherNote = card.querySelector('.' + prefix + '-other-note');
        if (hotelNote) hotelNote.textContent = hasHotel ? 'From profile' : 'No hotel booked';
        if (otherNote) otherNote.textContent = hasOther ? 'From profile' : 'No other services booked';
    };

    window.updateNegotiationSplitBreakdown = function (card, prefix, currency, pricing) {
        if (!card || !pricing) return;
        const money = function (amount) {
            return (typeof formatNegotiationAmount === 'function')
                ? formatNegotiationAmount(amount)
                : Number(amount || 0).toFixed(2);
        };
        const setText = function (sel, text) {
            const el = card.querySelector(sel);
            if (el) el.textContent = text;
        };
        const setAdd = function (sel, amount, enabled) {
            const el = card.querySelector(sel);
            if (!el) return;
            if (!enabled) {
                el.textContent = '—';
                el.classList.add('is-zero');
                return;
            }
            el.classList.toggle('is-zero', !(amount > 0));
            el.textContent = (amount > 0 ? '+' : '') + currency + ' ' + money(amount);
        };
        setText('.' + prefix + '-hotel-base', currency + ' ' + money(pricing.hotelGross));
        setText('.' + prefix + '-other-base', currency + ' ' + money(pricing.otherGross));
        setText('.' + prefix + '-gross-display', currency + ' ' + money(pricing.gross));
        setAdd('.' + prefix + '-hotel-add', pricing.hotelMoney, pricing.hasHotel);
        setAdd('.' + prefix + '-other-add', pricing.otherMoney, pricing.hasOther);
        const discountAdd = card.querySelector('.' + prefix + '-discount-add');
        if (discountAdd) {
            discountAdd.classList.toggle('is-zero', !(pricing.discountMoney > 0));
            discountAdd.textContent = (pricing.discountMoney > 0 ? '−' : '') + currency + ' ' + money(pricing.discountMoney);
        }
        setText('.' + prefix + '-markup-total', (pricing.markupMoney > 0 ? '+' : '') + currency + ' ' + money(pricing.markupMoney));
        setText('.' + prefix + '-after-markup', currency + ' ' + money(pricing.gross + pricing.markupMoney));
        setText('.' + prefix + '-discount-total', (pricing.discountMoney > 0 ? '−' : '') + currency + ' ' + money(pricing.discountMoney));
        window.applyNegotiationMarkupFieldState(card, prefix, pricing.hasHotel, pricing.hasOther);
    };

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
