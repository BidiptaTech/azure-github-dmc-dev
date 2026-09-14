{{-- Full Stay Rate Calendar modal — shared by enquiry form pro create & edit --}}
<style>
    /* ── Modal shell ── */
    .ep-full-cal-modal-dialog {
        width: 88% !important;
        max-width: 1100px !important;
        margin: 0.75rem auto !important;
    }
    .ep-full-cal-modal-content {
        border: 0;
        border-radius: 8px;
        box-shadow: 0 16px 48px rgba(15, 23, 42, 0.14);
        overflow: hidden;
        max-height: 92vh;
        display: flex;
        flex-direction: column;
        background: #fff;
        font-family: inherit;
    }
    .ep-full-cal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 16px;
        background: #fff;
        border-bottom: 1px solid #e5e7eb;
        flex-shrink: 0;
    }
    .ep-full-cal-header .modal-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #111827;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .ep-full-cal-header .modal-title i { color: #2563eb; font-size: 1.1rem; }
    .ep-full-cal-body {
        padding: 0;
        overflow-y: auto;
        max-height: calc(92vh - 52px);
        background: #f9fafb;
    }

    /* ── Sticky summary + nav ── */
    .ep-full-cal-sticky {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }
    .ep-full-cal-summary-row {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 8px;
        padding: 10px 14px 8px;
    }
    @media (max-width: 991.98px) {
        .ep-full-cal-summary-row { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 575.98px) {
        .ep-full-cal-summary-row { grid-template-columns: 1fr; }
    }
    .ep-full-cal-summary-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 8px 10px;
        min-height: 62px;
        display: flex;
        gap: 8px;
        align-items: flex-start;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        transition: box-shadow 0.15s ease;
    }
    .ep-full-cal-summary-card:hover {
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.07);
    }
    .ep-full-cal-summary-card .ep-card-icon {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 14px;
    }
    .ep-full-cal-summary-card.ep-ci .ep-card-icon { background: #dcfce7; color: #16a34a; }
    .ep-full-cal-summary-card.ep-co .ep-card-icon { background: #dbeafe; color: #1e40af; }
    .ep-full-cal-summary-card.ep-nights .ep-card-icon { background: #eff6ff; color: #2563eb; }
    .ep-full-cal-summary-card.ep-dest .ep-card-icon { background: #f3f4f6; color: #4b5563; }
    .ep-full-cal-summary-card.ep-hotel .ep-card-icon { background: #fef3c7; color: #d97706; }
    .ep-full-cal-summary-card .ep-card-body { min-width: 0; flex: 1; }
    .ep-full-cal-summary-card .ep-label {
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #6b7280;
        margin-bottom: 2px;
        line-height: 1.2;
    }
    .ep-full-cal-summary-card .ep-value {
        font-size: 13px;
        font-weight: 700;
        color: #111827;
        line-height: 1.25;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .ep-full-cal-summary-card.ep-nights .ep-value { font-size: 18px; color: #2563eb; }
    .ep-full-cal-summary-card .ep-sub {
        font-size: 10px;
        color: #6b7280;
        margin-top: 2px;
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ── Navigation bar ── */
    .ep-full-cal-nav {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        padding: 6px 14px 10px;
    }
    .ep-full-cal-nav .ep-nav-group {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .ep-full-cal-nav .btn,
    .ep-full-cal-nav .form-select {
        font-size: 11px;
        font-weight: 600;
        border-radius: 6px;
        padding: 4px 10px;
        height: 30px;
        line-height: 1.2;
    }
    .ep-full-cal-nav .form-select {
        padding: 4px 28px 4px 8px;
        min-width: 0;
    }
    .ep-full-cal-nav .ep-month-select {
        width: 118px;
        min-width: 118px;
        flex-shrink: 0;
    }
    .ep-full-cal-nav .ep-year-select {
        width: 96px;
        min-width: 96px;
        flex-shrink: 0;
    }
    .ep-full-cal-nav .ep-month-title {
        font-size: 13px;
        font-weight: 700;
        color: #111827;
        padding: 0 8px;
        min-width: 100px;
        text-align: center;
    }
    .ep-full-cal-nav .ep-nav-spacer { flex: 1; min-width: 8px; }
.ep-full-cal-nav-header {
    flex-wrap: nowrap;
    padding: 0;
    gap: 4px;
    margin-left: auto;
    margin-right: 8px;
    flex-shrink: 0;
}
.ep-full-cal-nav-header .form-select {
    text-overflow: clip;
}
    @media (max-width: 767.98px) {
        .ep-full-cal-header { flex-wrap: wrap; }
        .ep-full-cal-nav-header {
            order: 3;
            width: 100%;
            margin: 6px 0 0;
            flex-wrap: wrap;
        }
    }

    /* ── Calendar grid ── */
    .ep-full-cal-grid-outer {
        padding: 10px 14px 12px;
    }
    .ep-full-cal-grid-wrap {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 10px;
        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.04);
        overflow-x: auto;
    }
    .ep-full-cal-weekdays {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 3px;
        margin-bottom: 4px;
    }
    .ep-full-cal-weekday {
        text-align: center;
        font-size: 10px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 4px 0;
    }
    .ep-full-cal-weekday.ep-wknd { color: #dc2626; }
    .ep-full-cal-weeks {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    .ep-full-cal-week-row {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 3px;
    }

    /* ── Day cells ── */
    .ep-full-cal-day {
        position: relative;
        min-height: 72px;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        background: #fff;
        padding: 5px 5px 4px;
        display: flex;
        flex-direction: column;
        gap: 1px;
        transition: box-shadow 0.12s ease, border-color 0.12s ease;
        cursor: default;
        outline: none;
    }
    .ep-full-cal-day:focus-visible {
        box-shadow: 0 0 0 2px #2563eb;
        z-index: 3;
    }
    .ep-full-cal-day:hover {
        box-shadow: 0 3px 10px rgba(15, 23, 42, 0.08);
        z-index: 2;
    }
    .ep-full-cal-day.ep-other-month { opacity: 0.35; background: #f9fafb; }
    .ep-full-cal-day.ep-weekend { background: #fafafa; }
    .ep-full-cal-day.ep-today {
        outline: 2px solid #2563eb;
        outline-offset: -2px;
    }
    .ep-full-cal-day.ep-standard { background: #fafafa; border-color: #e5e7eb; }

    /* Rate / stay colors — subtle fills */
    .ep-full-cal-day.ep-stay { background: #eff6ff; border-color: #93c5fd; color: #1e40af; }
    .ep-full-cal-day.ep-blackout { background: #fef2f2; border-color: #fca5a5; color: #991b1b; }
    .ep-full-cal-day.ep-fair { background: #faf5ff; border-color: #d8b4fe; color: #6b21a8; }
    .ep-full-cal-day.ep-season { background: #fff7ed; border-color: #fdba74; color: #9a3412; }
    .ep-full-cal-day.ep-blackout.ep-stay { background: #fee2e2; border-color: #f87171; }
    .ep-full-cal-day.ep-fair.ep-stay { background: #f3e8ff; border-color: #c084fc; }
    .ep-full-cal-day.ep-season.ep-stay { background: #ffedd5; border-color: #fb923c; }

    .ep-full-cal-day.ep-checkin {
        border-color: #22c55e !important;
        box-shadow: inset 0 0 0 1px #22c55e;
    }
    .ep-full-cal-day.ep-checkout {
        border-color: #1e3a8a !important;
        box-shadow: inset 0 0 0 1px #1e3a8a;
    }

    /* Stay-night flow connectors */
    .ep-full-cal-day.ep-stay-flow-left {
        border-top-left-radius: 2px;
        border-bottom-left-radius: 2px;
        margin-left: 0;
    }
    .ep-full-cal-day.ep-stay-flow-right {
        border-top-right-radius: 2px;
        border-bottom-right-radius: 2px;
        border-right: 2px solid #2563eb;
        margin-right: -1px;
        z-index: 1;
    }
    .ep-full-cal-day.ep-stay-flow-right::after {
        content: '';
        position: absolute;
        right: -4px;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 2px;
        background: #2563eb;
        z-index: 2;
    }

    .ep-full-cal-day-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 2px;
    }
    .ep-full-cal-day-num {
        font-size: 14px;
        font-weight: 700;
        line-height: 1.1;
        color: inherit;
    }
    .ep-full-cal-day-name {
        font-size: 8px;
        font-weight: 600;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .ep-full-cal-night-badge {
        background: #2563eb;
        color: #fff;
        font-size: 7px;
        font-weight: 700;
        padding: 1px 4px;
        border-radius: 3px;
        line-height: 1.3;
        white-space: nowrap;
    }
    .ep-full-cal-status-pill {
        display: inline-block;
        font-size: 7px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        padding: 1px 4px;
        border-radius: 3px;
        line-height: 1.3;
        margin-top: auto;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .ep-full-cal-status-pill.ep-pill-stay { background: #dbeafe; color: #1d4ed8; }
    .ep-full-cal-status-pill.ep-pill-checkin { background: #dcfce7; color: #15803d; }
    .ep-full-cal-status-pill.ep-pill-checkout { background: #dbeafe; color: #1e3a8a; }
    .ep-full-cal-status-pill.ep-pill-blackout { background: #fee2e2; color: #b91c1c; }
    .ep-full-cal-status-pill.ep-pill-fair { background: #f3e8ff; color: #7e22ce; }
    .ep-full-cal-status-pill.ep-pill-season { background: #ffedd5; color: #c2410c; }
    .ep-full-cal-status-pill.ep-pill-standard { background: #f3f4f6; color: #6b7280; }
    .ep-full-cal-day-price {
        font-size: 9px;
        font-weight: 600;
        color: #374151;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .ep-full-cal-day-meal {
        font-size: 7px;
        color: #6b7280;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ── Legend pills (bottom) ── */
    .ep-full-cal-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        padding: 10px 14px 14px;
        border-top: 1px solid #e5e7eb;
        background: #fff;
        margin-top: 0;
    }
    .ep-full-cal-legend .ep-legend-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 10px;
        font-weight: 600;
        color: #4b5563;
        padding: 3px 8px;
        border-radius: 20px;
        border: 1px solid #e5e7eb;
        background: #fafafa;
    }
    .ep-full-cal-legend .ep-key-swatch {
        width: 10px;
        height: 10px;
        border-radius: 3px;
        flex-shrink: 0;
        border: 1px solid rgba(0,0,0,0.08);
    }

    /* Compact legend button in accommodation panel */
    .ep-legend-header-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 4px;
    }
    .ep-view-full-calendar-btn {
        font-size: 11px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 6px;
        white-space: nowrap;
        box-shadow: 0 1px 2px rgba(37, 99, 235, 0.12);
    }

    /* Rich tooltip */
    .ep-full-cal-tooltip .tooltip-inner {
        max-width: 280px;
        text-align: left;
        font-size: 11px;
        padding: 8px 10px;
        line-height: 1.45;
    }

    @media (max-width: 767.98px) {
        .ep-full-cal-modal-dialog {
            width: 98% !important;
            max-width: 98vw !important;
        }
        .ep-full-cal-day { min-height: 58px; padding: 4px; }
        .ep-full-cal-day-num { font-size: 12px; }
    }
</style>

<div class="modal fade" id="epFullStayCalendarModal" tabindex="-1" aria-labelledby="epFullStayCalendarModalLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable ep-full-cal-modal-dialog">
        <div class="modal-content ep-full-cal-modal-content">
            <div class="modal-header ep-full-cal-header border-0">
                <h5 class="modal-title" id="epFullStayCalendarModalLabel">
                    <i class="ri-calendar-2-line" aria-hidden="true"></i>
                    Monthly Stay Calendar
                </h5>
                <div class="ep-full-cal-nav ep-full-cal-nav-header" role="toolbar" aria-label="Calendar navigation">
                    <button type="button" class="btn btn-primary btn-sm" id="epFullCalTodayBtn">Today</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="epFullCalPrevMonth" aria-label="Previous month">
                        <i class="ri-arrow-left-s-line"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="epFullCalNextMonth" aria-label="Next month">
                        <i class="ri-arrow-right-s-line"></i>
                    </button>
                    <select class="form-select form-select-sm ep-month-select" id="epFullCalMonthSelect" aria-label="Select month"></select>
                    <select class="form-select form-select-sm ep-year-select" id="epFullCalYearSelect" aria-label="Select year"></select>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body ep-full-cal-body">
                <div class="ep-full-cal-sticky">
                    <div class="ep-full-cal-summary-row ep-full-cal-summary">
                        <div class="ep-full-cal-summary-card ep-ci">
                            <div class="ep-card-icon"><i class="ri-login-box-line" aria-hidden="true"></i></div>
                            <div class="ep-card-body">
                                <div class="ep-label">Check-in</div>
                                <div class="ep-value" id="epFullCalCheckIn">—</div>
                                <div class="ep-sub" id="epFullCalCheckInSub">—</div>
                            </div>
                        </div>
                        <div class="ep-full-cal-summary-card ep-co">
                            <div class="ep-card-icon"><i class="ri-logout-box-line" aria-hidden="true"></i></div>
                            <div class="ep-card-body">
                                <div class="ep-label">Check-out</div>
                                <div class="ep-value" id="epFullCalCheckOut">—</div>
                                <div class="ep-sub" id="epFullCalCheckOutSub">—</div>
                            </div>
                        </div>
                        <div class="ep-full-cal-summary-card ep-nights">
                            <div class="ep-card-icon"><i class="ri-moon-line" aria-hidden="true"></i></div>
                            <div class="ep-card-body">
                                <div class="ep-label">Total Nights</div>
                                <div class="ep-value" id="epFullCalNights">—</div>
                                <div class="ep-sub" id="epFullCalNightsSub">Stay period</div>
                            </div>
                        </div>
                        <div class="ep-full-cal-summary-card ep-dest">
                            <div class="ep-card-icon"><i class="ri-map-pin-line" aria-hidden="true"></i></div>
                            <div class="ep-card-body">
                                <div class="ep-label">Destination</div>
                                <div class="ep-value" id="epFullCalDestination">—</div>
                                <div class="ep-sub" id="epFullCalDestinationSub">City</div>
                            </div>
                        </div>
                        <div class="ep-full-cal-summary-card ep-hotel">
                            <div class="ep-card-icon"><i class="ri-hotel-line" aria-hidden="true"></i></div>
                            <div class="ep-card-body">
                                <div class="ep-label">Hotel</div>
                                <div class="ep-value" id="epFullCalHotel">—</div>
                                <div class="ep-sub" id="epFullCalHotelSub">Selected property</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ep-full-cal-grid-outer">
                    <div class="ep-full-cal-grid-wrap">
                        <div class="ep-full-cal-weekdays" id="epFullCalWeekdayHdr" role="row"></div>
                        <div class="ep-full-cal-weeks" id="epFullStayCalendarGrid" role="grid" aria-label="Monthly calendar"></div>
                    </div>
                </div>

                <div class="ep-full-cal-legend" aria-label="Calendar legend">
                    <span class="ep-legend-pill"><span class="ep-key-swatch" style="background:#eff6ff;border-color:#93c5fd;"></span> Stay night</span>
                    <span class="ep-legend-pill"><span class="ep-key-swatch" style="background:#fef2f2;border-color:#fca5a5;"></span> Blackout</span>
                    <span class="ep-legend-pill"><span class="ep-key-swatch" style="background:#faf5ff;border-color:#d8b4fe;"></span> Fair</span>
                    <span class="ep-legend-pill"><span class="ep-key-swatch" style="background:#fff7ed;border-color:#fdba74;"></span> Season</span>
                    <span class="ep-legend-pill"><span class="ep-key-swatch" style="background:#fafafa;border-color:#e5e7eb;"></span> Available</span>
                    <span class="ep-legend-pill"><span class="ep-key-swatch" style="box-shadow:inset 0 0 0 2px #22c55e;background:#fff;"></span> Check-in</span>
                    <span class="ep-legend-pill"><span class="ep-key-swatch" style="box-shadow:inset 0 0 0 2px #1e3a8a;background:#fff;"></span> Check-out</span>
                    <span class="ep-legend-pill"><span class="ep-key-swatch" style="outline:2px solid #2563eb;background:#fff;"></span> Today</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const MONTH_NAMES = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const MONTH_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const WEEKDAY_HDR = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    window._epFullCalView = { year: new Date().getFullYear(), month: new Date().getMonth() };

    function epFullCalDeps() {
        return typeof getStayDateStrings === 'function'
            && typeof enquiryProGetHotelRates === 'function'
            && typeof enquiryProGetApplicableRateForDate === 'function';
    }

    function epFullCalWeekendDays() {
        if (typeof enquiryProWeekendDaysFromHotel === 'function') {
            const w = enquiryProWeekendDaysFromHotel();
            if (w && w.length) return w;
        }
        const h = typeof enquiryProGetHotelData === 'function' ? enquiryProGetHotelData() : null;
        const raw = h?.weekend_days || h?.weekend || h?.weekendDays || [];
        if (typeof parseWeekendDays === 'function') {
            const parsed = parseWeekendDays(raw);
            if (parsed.length) return parsed;
        }
        return ['Saturday', 'Sunday'];
    }

    function epFullCalCheckInOutMeta() {
        const checkInVal = document.getElementById('checkInDate')?.value || '';
        const checkOutVal = document.getElementById('checkOutDate')?.value || '';
        const checkInDate = checkInVal ? checkInVal.split('T')[0] : '';
        const checkOutDate = checkOutVal ? checkOutVal.split('T')[0] : '';
        const stayDates = typeof getStayDateStrings === 'function' ? getStayDateStrings() : [];
        const nightsEl = document.getElementById('numNights');
        const nights = stayDates.length || parseInt(nightsEl?.value, 10) || 0;
        return { checkInVal, checkOutVal, checkInDate, checkOutDate, nights, stayDates };
    }

    function epFullCalHotelMeta() {
        const destEl = document.getElementById('hotelDestination');
        const hotelSel = document.getElementById('hotelSelect');
        let destination = '—';
        let hotelName = '—';
        if (destEl && destEl.value) {
            const opt = destEl.options[destEl.selectedIndex];
            destination = (opt?.text || destEl.value || '').trim() || '—';
        }
        if (hotelSel && hotelSel.value) {
            const opt = hotelSel.options[hotelSel.selectedIndex];
            hotelName = (opt?.text || '').trim() || '—';
        }
        return { destination, hotelName };
    }

    function epFullCalTodayStr() {
        const t = new Date();
        return `${t.getFullYear()}-${String(t.getMonth() + 1).padStart(2, '0')}-${String(t.getDate()).padStart(2, '0')}`;
    }

    function epFullCalShiftDateStr(dateStr, deltaDays) {
        const d = new Date(dateStr + 'T12:00:00');
        d.setDate(d.getDate() + deltaDays);
        return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    }

    function epFullCalFormatDisplayDate(raw) {
        if (typeof enquiryProFormatNoticeDate === 'function') {
            return enquiryProFormatNoticeDate(raw);
        }
        if (!raw) return '—';
        const parts = String(raw).substring(0, 10).split('-');
        if (parts.length !== 3) return raw;
        return `${parseInt(parts[2], 10)} ${MONTH_SHORT[parseInt(parts[1], 10) - 1] || parts[1]} ${parts[0]}`;
    }

    function epFullCalFormatSummaryParts(val) {
        if (!val) return { main: '—', sub: '—' };
        const [datePart, timePart] = val.split('T');
        const main = epFullCalFormatDisplayDate(datePart);
        let sub = '—';
        if (datePart) {
            const dt = new Date(datePart + 'T12:00:00');
            const dayName = dt.toLocaleString('en-US', { weekday: 'long' });
            if (timePart) {
                const [hh, mm] = timePart.split(':');
                sub = `${dayName} · ${hh}:${mm}`;
            } else {
                sub = dayName;
            }
        }
        return { main, sub };
    }

    function epFullCalRateCssClass(eventType) {
        if (typeof enquiryProGetRateCssClass === 'function') {
            return enquiryProGetRateCssClass(eventType);
        }
        if (eventType === 'Blackout Date') return 'ep-blackout';
        if (eventType === 'Fair Date') return 'ep-fair';
        if (eventType === 'Season') return 'ep-season';
        return '';
    }

    function epFullCalStatusLabel(applicable, isStay, nightNum, isCheckIn, isCheckOut) {
        if (isCheckIn) return 'Check-in';
        if (isCheckOut) return 'Check-out';
        if (isStay) return `Stay N${nightNum}`;
        if (!applicable) return 'Available';
        if (applicable.event_type === 'Blackout Date') return 'Blackout';
        if (applicable.event_type === 'Fair Date') return 'Fair';
        if (applicable.event_type === 'Season') return 'Season';
        return applicable.event_type || 'Rate';
    }

    function epFullCalStatusPillClass(applicable, isStay, isCheckIn, isCheckOut) {
        if (isCheckIn) return 'ep-pill-checkin';
        if (isCheckOut) return 'ep-pill-checkout';
        if (isStay) return 'ep-pill-stay';
        if (!applicable) return 'ep-pill-standard';
        if (applicable.event_type === 'Blackout Date') return 'ep-pill-blackout';
        if (applicable.event_type === 'Fair Date') return 'ep-pill-fair';
        if (applicable.event_type === 'Season') return 'ep-pill-season';
        return 'ep-pill-standard';
    }

    function epFullCalRatePriceLine(applicable) {
        if (!applicable) return '';
        if (applicable.event_type === 'Blackout Date' && applicable.price != null && applicable.price !== '') {
            return `Room: ${applicable.price}`;
        }
        if (applicable.event_type === 'Fair Date' && applicable.price != null && applicable.price !== '') {
            return `Fair: +${applicable.price}`;
        }
        if (applicable.event_type === 'Season') {
            const wp = applicable.weekday_price || applicable.weekend_price;
            if (wp != null && wp !== '') return `From ${wp}`;
        }
        if (applicable.price != null && applicable.price !== '') return `${applicable.price}`;
        return '';
    }

    function epFullCalMealHint(applicable) {
        if (!applicable) return '';
        const parts = [];
        if (applicable.breakfast_price && parseFloat(applicable.breakfast_price) > 0) parts.push('Breakfast');
        if (applicable.lunch_price && parseFloat(applicable.lunch_price) > 0) parts.push('Lunch');
        if (applicable.dinner_price && parseFloat(applicable.dinner_price) > 0) parts.push('Dinner');
        if (parts.length) return parts.join(' · ');
        if (applicable.event_type === 'Season') return 'Season rate';
        return '';
    }

    function epFullCalBuildTooltipHtml(dateStr, ctx) {
        const dt = new Date(dateStr + 'T12:00:00');
        const dayName = dt.toLocaleString('en-US', { weekday: 'long' });
        const isStay = ctx.staySet.has(dateStr);
        const nightNum = ctx.stayNightMap.get(dateStr);
        const isCheckIn = dateStr === ctx.checkInDate;
        const isCheckOut = dateStr === ctx.checkOutDate;
        const isWeekend = typeof isWeekendDate === 'function' ? isWeekendDate(dt, ctx.weekendDays) : false;
        const applicable = enquiryProGetApplicableRateForDate(dateStr, ctx.hotelRates);
        const status = epFullCalStatusLabel(applicable, isStay, nightNum, isCheckIn, isCheckOut);
        const rows = [
            `<strong>${epFullCalEscape(dayName)}, ${epFullCalEscape(epFullCalFormatDisplayDate(dateStr))}</strong>`,
            `Status: ${epFullCalEscape(status)}`
        ];
        if (isStay && nightNum) rows.push(`Stay night: N${nightNum}`);
        if (applicable?.event) rows.push(`Event: ${epFullCalEscape(applicable.event)}`);
        const priceLine = epFullCalRatePriceLine(applicable);
        if (priceLine) rows.push(`Rate: ${epFullCalEscape(priceLine)}`);
        const meal = epFullCalMealHint(applicable);
        if (meal) rows.push(epFullCalEscape(meal));
        if (isWeekend) rows.push('Weekend pricing may apply');
        if (ctx.hotelMeta?.hotelName && ctx.hotelMeta.hotelName !== '—') {
            rows.push(`Hotel: ${epFullCalEscape(ctx.hotelMeta.hotelName)}`);
        }
        return rows.join('<br>');
    }

    function epFullCalEscape(value) {
        if (typeof enquiryProEscapeHtml === 'function') return enquiryProEscapeHtml(value);
        return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function epFullCalBuildContext() {
        const meta = epFullCalCheckInOutMeta();
        const hotelRates = enquiryProGetHotelRates();
        const stayDates = meta.stayDates;
        const staySet = new Set(stayDates);
        const stayNightMap = new Map();
        stayDates.forEach((d, i) => stayNightMap.set(d, i + 1));
        return {
            ...meta,
            hotelRates,
            staySet,
            stayNightMap,
            weekendDays: epFullCalWeekendDays(),
            hotelMeta: epFullCalHotelMeta()
        };
    }

    function epFullCalUpdateSummary(ctx) {
        const ciParts = epFullCalFormatSummaryParts(ctx.checkInVal);
        const coParts = epFullCalFormatSummaryParts(ctx.checkOutVal);
        const set = (id, text) => { const el = document.getElementById(id); if (el) el.textContent = text; };

        set('epFullCalCheckIn', ciParts.main);
        set('epFullCalCheckInSub', ciParts.sub);
        set('epFullCalCheckOut', coParts.main);
        set('epFullCalCheckOutSub', coParts.sub);
        set('epFullCalNights', String(ctx.nights || 0));
        set('epFullCalNightsSub', ctx.nights === 1 ? '1 night stay' : `${ctx.nights || 0} nights stay`);
        set('epFullCalDestination', ctx.hotelMeta.destination);
        set('epFullCalHotel', ctx.hotelMeta.hotelName);
    }

    function epFullCalSyncMonthYearSelects(year, month) {
        const monthSel = document.getElementById('epFullCalMonthSelect');
        const yearSel = document.getElementById('epFullCalYearSelect');
        if (monthSel) monthSel.value = String(month);
        if (yearSel) yearSel.value = String(year);
    }

    function epFullCalPopulateMonthYearSelects() {
        const monthSel = document.getElementById('epFullCalMonthSelect');
        const yearSel = document.getElementById('epFullCalYearSelect');
        if (monthSel && !monthSel.options.length) {
            monthSel.innerHTML = MONTH_NAMES.map((name, i) =>
                `<option value="${i}">${name}</option>`
            ).join('');
        }
        if (yearSel && !yearSel.options.length) {
            const cy = new Date().getFullYear();
            let opts = '';
            for (let y = cy - 2; y <= cy + 4; y++) {
                opts += `<option value="${y}">${y}</option>`;
            }
            yearSel.innerHTML = opts;
        }
    }

    window.enquiryProRenderFullStayCalendar = function (year, month) {
        const grid = document.getElementById('epFullStayCalendarGrid');
        const hdrEl = document.getElementById('epFullCalWeekdayHdr');
        if (!grid || !epFullCalDeps()) return;

        epFullCalPopulateMonthYearSelects();

        const ctx = epFullCalBuildContext();
        epFullCalUpdateSummary(ctx);

        window._epFullCalView = { year, month };
        epFullCalSyncMonthYearSelects(year, month);

        if (hdrEl) {
            hdrEl.innerHTML = WEEKDAY_HDR.map((name, i) => {
                const colDate = new Date(2024, 0, 7 + i);
                const isWknd = typeof isWeekendDate === 'function' ? isWeekendDate(colDate, ctx.weekendDays) : (i === 0 || i === 6);
                return `<div class="ep-full-cal-weekday${isWknd ? ' ep-wknd' : ''}" role="columnheader">${name}</div>`;
            }).join('');
        }

        const firstOfMonth = new Date(year, month, 1);
        const startPad = firstOfMonth.getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const todayStr = epFullCalTodayStr();

        const cells = [];
        for (let i = 0; i < startPad; i++) {
            const d = new Date(year, month, 1 - (startPad - i));
            cells.push(epFullCalRenderDayCell(d, ctx, true, todayStr));
        }
        for (let day = 1; day <= daysInMonth; day++) {
            cells.push(epFullCalRenderDayCell(new Date(year, month, day), ctx, false, todayStr));
        }
        let tailDay = 1;
        while (cells.length % 7 !== 0) {
            cells.push(epFullCalRenderDayCell(new Date(year, month + 1, tailDay), ctx, true, todayStr));
            tailDay += 1;
        }

        let html = '';
        for (let i = 0; i < cells.length; i += 7) {
            html += `<div class="ep-full-cal-week-row" role="row">${cells.slice(i, i + 7).join('')}</div>`;
        }
        grid.innerHTML = html;
    };

    function epFullCalRenderDayCell(dateObj, ctx, isOtherMonth, todayStr) {
        const y = dateObj.getFullYear();
        const m = dateObj.getMonth();
        const day = dateObj.getDate();
        const dateStr = `${y}-${String(m + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const dt = new Date(dateStr + 'T12:00:00');
        const dayName = dt.toLocaleString('en-US', { weekday: 'short' }).toUpperCase();
        const isWeekend = typeof isWeekendDate === 'function' ? isWeekendDate(dt, ctx.weekendDays) : false;
        const applicable = enquiryProGetApplicableRateForDate(dateStr, ctx.hotelRates);
        const isStay = ctx.staySet.has(dateStr);
        const nightNum = ctx.stayNightMap.get(dateStr);
        const isCheckIn = dateStr === ctx.checkInDate;
        const isCheckOut = dateStr === ctx.checkOutDate;
        const isToday = dateStr === todayStr;

        const prevStay = ctx.staySet.has(epFullCalShiftDateStr(dateStr, -1));
        const nextStay = ctx.staySet.has(epFullCalShiftDateStr(dateStr, 1));

        let cls = 'ep-full-cal-day';
        if (isOtherMonth) cls += ' ep-other-month';
        if (isWeekend) cls += ' ep-weekend';
        if (isToday) cls += ' ep-today';
        if (!applicable && !isStay && !isCheckIn && !isCheckOut) cls += ' ep-standard';
        if (applicable?.event_type) cls += ' ' + epFullCalRateCssClass(applicable.event_type);
        if (isStay) cls += ' ep-stay';
        if (isCheckIn) cls += ' ep-checkin';
        if (isCheckOut) cls += ' ep-checkout';
        if (isStay && prevStay) cls += ' ep-stay-flow-left';
        if (isStay && nextStay) cls += ' ep-stay-flow-right';

        const status = epFullCalStatusLabel(applicable, isStay, nightNum, isCheckIn, isCheckOut);
        const pillCls = epFullCalStatusPillClass(applicable, isStay, isCheckIn, isCheckOut);
        const priceLine = epFullCalRatePriceLine(applicable);
        const mealLine = epFullCalMealHint(applicable);
        const tipHtml = epFullCalBuildTooltipHtml(dateStr, ctx);

        let inner = '<div class="ep-full-cal-day-top">';
        inner += `<span class="ep-full-cal-day-num">${day}</span>`;
        if (isStay) inner += `<span class="ep-full-cal-night-badge">N${nightNum}</span>`;
        inner += '</div>';
        inner += `<span class="ep-full-cal-day-name">${dayName}</span>`;
        inner += `<span class="ep-full-cal-status-pill ${pillCls}">${epFullCalEscape(status)}</span>`;
        if (priceLine) inner += `<span class="ep-full-cal-day-price">${epFullCalEscape(priceLine)}</span>`;
        if (mealLine) inner += `<span class="ep-full-cal-day-meal">${epFullCalEscape(mealLine)}</span>`;

        return `<div class="${cls}" tabindex="0" role="gridcell" aria-label="${epFullCalEscape(status)} ${day}" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" data-bs-custom-class="ep-full-cal-tooltip" data-ep-tip="${encodeURIComponent(tipHtml)}">${inner}</div>`;
    }

    function epFullCalInitTooltips() {
        const modal = document.getElementById('epFullStayCalendarModal');
        if (!modal || typeof bootstrap === 'undefined') return;
        modal.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            const existing = bootstrap.Tooltip.getInstance(el);
            if (existing) existing.dispose();
            const encoded = el.getAttribute('data-ep-tip');
            const title = encoded ? decodeURIComponent(encoded) : (el.getAttribute('data-bs-title') || '');
            new bootstrap.Tooltip(el, {
                trigger: 'hover focus',
                container: modal,
                html: true,
                customClass: 'ep-full-cal-tooltip',
                title: title
            });
        });
    }

    function epFullCalRaiseAboveOtherModals(modalEl) {
        if (!modalEl) return;
        if (modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }
        let maxZ = 1050;
        document.querySelectorAll('.modal.show').forEach(el => {
            if (el === modalEl) return;
            const z = parseInt(window.getComputedStyle(el).zIndex, 10);
            if (!isNaN(z) && z >= maxZ) maxZ = z;
        });
        document.querySelectorAll('.modal-backdrop.show').forEach(el => {
            const z = parseInt(window.getComputedStyle(el).zIndex, 10);
            if (!isNaN(z) && z >= maxZ) maxZ = z;
        });
        const modalZ = maxZ + 20;
        modalEl.style.zIndex = String(modalZ);
        requestAnimationFrame(() => {
            const backdrops = document.querySelectorAll('.modal-backdrop.show');
            const topBackdrop = backdrops[backdrops.length - 1];
            if (topBackdrop) topBackdrop.style.zIndex = String(modalZ - 10);
        });
    }

    function epFullCalRenderAndTooltips(year, month) {
        enquiryProRenderFullStayCalendar(year, month);
        epFullCalInitTooltips();
    }

    window.enquiryProOpenFullStayCalendar = function () {
        if (!epFullCalDeps()) return;
        const ctx = epFullCalBuildContext();
        if (!ctx.stayDates.length || !ctx.hotelRates.length) {
            if (typeof toastr !== 'undefined') {
                toastr.info('Select hotel and stay dates to view the calendar.');
            } else {
                alert('Select hotel and stay dates to view the calendar.');
            }
            return;
        }

        const anchor = ctx.checkInDate ? new Date(ctx.checkInDate + 'T12:00:00') : new Date();
        window._epFullCalView = { year: anchor.getFullYear(), month: anchor.getMonth() };

        epFullCalRenderAndTooltips(window._epFullCalView.year, window._epFullCalView.month);

        const modalEl = document.getElementById('epFullStayCalendarModal');
        if (!modalEl || typeof bootstrap === 'undefined') return;

        epFullCalRaiseAboveOtherModals(modalEl);
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        modalEl.addEventListener('shown.bs.modal', function onShown() {
            epFullCalRaiseAboveOtherModals(modalEl);
            epFullCalInitTooltips();
            modalEl.removeEventListener('shown.bs.modal', onShown);
        });
    };

    function epFullCalNavigateMonth(delta) {
        let { year, month } = window._epFullCalView || {};
        month += delta;
        if (month < 0) { month = 11; year -= 1; }
        if (month > 11) { month = 0; year += 1; }
        epFullCalRenderAndTooltips(year, month);
    }

    document.addEventListener('DOMContentLoaded', function () {
        epFullCalPopulateMonthYearSelects();

        const calModalEl = document.getElementById('epFullStayCalendarModal');
        if (calModalEl) {
            calModalEl.addEventListener('show.bs.modal', function () {
                epFullCalRaiseAboveOtherModals(calModalEl);
            });
            calModalEl.addEventListener('shown.bs.modal', function () {
                epFullCalRaiseAboveOtherModals(calModalEl);
            });

            calModalEl.addEventListener('keydown', function (e) {
                const grid = document.getElementById('epFullStayCalendarGrid');
                if (!grid || !grid.contains(document.activeElement)) return;
                const cells = Array.from(grid.querySelectorAll('.ep-full-cal-day[tabindex="0"]'));
                const idx = cells.indexOf(document.activeElement);
                if (idx < 0) return;
                let next = idx;
                if (e.key === 'ArrowRight') next = Math.min(cells.length - 1, idx + 1);
                else if (e.key === 'ArrowLeft') next = Math.max(0, idx - 1);
                else if (e.key === 'ArrowDown') next = Math.min(cells.length - 1, idx + 7);
                else if (e.key === 'ArrowUp') next = Math.max(0, idx - 7);
                else return;
                e.preventDefault();
                cells[next]?.focus();
            });
        }

        const prevBtn = document.getElementById('epFullCalPrevMonth');
        const nextBtn = document.getElementById('epFullCalNextMonth');
        const todayBtn = document.getElementById('epFullCalTodayBtn');
        const monthSel = document.getElementById('epFullCalMonthSelect');
        const yearSel = document.getElementById('epFullCalYearSelect');

        if (prevBtn) prevBtn.addEventListener('click', () => epFullCalNavigateMonth(-1));
        if (nextBtn) nextBtn.addEventListener('click', () => epFullCalNavigateMonth(1));

        if (todayBtn) {
            todayBtn.addEventListener('click', function () {
                const now = new Date();
                epFullCalRenderAndTooltips(now.getFullYear(), now.getMonth());
            });
        }

        function onMonthYearChange() {
            const y = parseInt(yearSel?.value, 10);
            const m = parseInt(monthSel?.value, 10);
            if (!isNaN(y) && !isNaN(m)) epFullCalRenderAndTooltips(y, m);
        }
        if (monthSel) monthSel.addEventListener('change', onMonthYearChange);
        if (yearSel) yearSel.addEventListener('change', onMonthYearChange);
    });
})();
</script>
