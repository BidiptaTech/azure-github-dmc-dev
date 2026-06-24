{{-- Full Stay Rate Calendar modal — shared by enquiry form pro create & edit --}}
<style>
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
    box-shadow: 0 1px 2px rgba(13, 110, 253, 0.12);
}
/* ~80% centered modal — not fullscreen */
.ep-full-cal-modal-dialog {
    width: 75% !important;
    max-width: 75vw !important;
    margin: 1.25rem auto !important;
}
.ep-full-cal-modal-content {
    border: 0;
    border-radius: 14px;
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.18);
    overflow: hidden;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}
.ep-full-cal-modal-content .modal-header {
    background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%);
    border-bottom: 1px solid #e2e8f0;
    padding: 14px 18px;
}
.ep-full-cal-modal-content .modal-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #1e293b;
}
.ep-full-cal-modal-content .modal-body {
    background: #fff;
    padding: 16px 18px 18px;
    overflow-y: auto;
    max-height: calc(90vh - 58px);
}
.ep-full-cal-summary-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 14px;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    height: 100%;
}
.ep-full-cal-summary-card .ep-label {
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #64748b;
    margin-bottom: 2px;
}
.ep-full-cal-summary-card .ep-value {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
}
.ep-full-cal-summary-card.ep-nights .ep-value { color: #0d6efd; }
.ep-full-cal-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 12px;
    padding: 8px 10px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
}
.ep-full-cal-nav .ep-month-title {
    font-size: 15px;
    font-weight: 700;
    color: #1e293b;
    text-align: center;
    flex: 1;
}
.ep-full-cal-nav .btn {
    font-size: 12px;
    font-weight: 600;
    border-radius: 8px;
    padding: 5px 12px;
}
.ep-full-cal-grid-wrap {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
    overflow-x: auto;
}
.ep-full-cal-weekdays {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 4px;
    margin-bottom: 6px;
}
.ep-full-cal-weekday {
    text-align: center;
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    padding: 4px 0;
}
.ep-full-cal-weekday.ep-wknd { color: #dc2626; }
.ep-full-cal-weeks {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.ep-full-cal-week-row {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 4px;
}
.ep-full-cal-day {
    position: relative;
    min-height: 64px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: #fff;
    padding: 6px 6px 4px;
    display: flex;
    flex-direction: column;
    transition: box-shadow 0.15s ease, transform 0.15s ease;
    cursor: default;
}
.ep-full-cal-day:hover {
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.1);
    transform: translateY(-1px);
    z-index: 2;
}
.ep-full-cal-day.ep-other-month { opacity: 0.38; background: #f8fafc; }
.ep-full-cal-day.ep-weekend { background: #f8fafc; }
.ep-full-cal-day.ep-stay { background: #dbeafe; border-color: #3b82f6; color: #1e40af; }
.ep-full-cal-day.ep-blackout { background: #fee2e2; border-color: #ef4444; color: #991b1b; }
.ep-full-cal-day.ep-fair { background: #f3e8ff; border-color: #a855f7; color: #6b21a8; }
.ep-full-cal-day.ep-season { background: #ffedd5; border-color: #f97316; color: #9a3412; }
.ep-full-cal-day.ep-blackout.ep-stay { background: #fecaca; border-color: #dc2626; }
.ep-full-cal-day.ep-fair.ep-stay { background: #e9d5ff; border-color: #9333ea; }
.ep-full-cal-day.ep-season.ep-stay { background: #fed7aa; border-color: #ea580c; }
.ep-full-cal-day.ep-checkin {
    box-shadow: inset 0 0 0 2px #22c55e, 0 0 0 2px rgba(34, 197, 94, 0.25);
}
.ep-full-cal-day.ep-checkout {
    box-shadow: inset 0 0 0 2px #1e3a8a, 0 0 0 2px rgba(30, 58, 138, 0.2);
}
.ep-full-cal-day-num {
    font-size: 15px;
    font-weight: 700;
    line-height: 1.1;
}
.ep-full-cal-day-name {
    font-size: 9px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    margin-top: 2px;
}
.ep-full-cal-day-status {
    font-size: 8px;
    font-weight: 700;
    margin-top: auto;
    padding-top: 4px;
    line-height: 1.2;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}
.ep-full-cal-night-badge {
    position: absolute;
    top: 4px;
    right: 4px;
    background: #2563eb;
    color: #fff;
    font-size: 8px;
    font-weight: 700;
    padding: 1px 5px;
    border-radius: 4px;
    line-height: 1.4;
}
.ep-full-cal-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 8px 14px;
    margin-top: 14px;
    padding-top: 12px;
    border-top: 1px solid #e2e8f0;
    font-size: 11px;
    color: #64748b;
}
.ep-full-cal-legend .ep-key-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.ep-full-cal-legend .ep-key-swatch {
    width: 14px;
    height: 14px;
    border-radius: 4px;
    border: 1px solid #cbd5e1;
    flex-shrink: 0;
}
@media (max-width: 767.98px) {
    .ep-full-cal-modal-dialog {
        width: 96% !important;
        max-width: 96vw !important;
        margin: 0.5rem auto !important;
    }
    .ep-full-cal-day { min-height: 52px; padding: 4px; }
    .ep-full-cal-day-num { font-size: 13px; }
    .ep-full-cal-day-status { font-size: 7px; }
}
</style>

<div class="modal fade" id="epFullStayCalendarModal" tabindex="-1" aria-labelledby="epFullStayCalendarModalLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable ep-full-cal-modal-dialog">
        <div class="modal-content ep-full-cal-modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="epFullStayCalendarModalLabel">
                    <i class="ri-calendar-2-line me-1 text-primary"></i> Monthly Stay Calendar
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3 ep-full-cal-summary">
                    <div class="col-md-4 col-12">
                        <div class="ep-full-cal-summary-card">
                            <div class="ep-label">Check-in</div>
                            <div class="ep-value" id="epFullCalCheckIn">—</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="ep-full-cal-summary-card">
                            <div class="ep-label">Check-out</div>
                            <div class="ep-value" id="epFullCalCheckOut">—</div>
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="ep-full-cal-summary-card ep-nights">
                            <div class="ep-label">Nights</div>
                            <div class="ep-value" id="epFullCalNights">—</div>
                        </div>
                    </div>
                </div>

                <div class="ep-full-cal-nav">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="epFullCalPrevMonth" aria-label="Previous month">
                        <i class="ri-arrow-left-s-line"></i> Prev
                    </button>
                    <div class="ep-month-title" id="epFullCalMonthTitle">—</div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="epFullCalNextMonth" aria-label="Next month">
                        Next <i class="ri-arrow-right-s-line"></i>
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" id="epFullCalTodayBtn">Today</button>
                </div>

                <div class="ep-full-cal-grid-wrap">
                    <div class="ep-full-cal-weekdays" id="epFullCalWeekdayHdr"></div>
                    <div class="ep-full-cal-weeks" id="epFullStayCalendarGrid"></div>
                </div>

                <div class="ep-full-cal-legend">
                    <span class="ep-key-item"><span class="ep-key-swatch" style="background:#dbeafe;border-color:#3b82f6;"></span> Stay night</span>
                    <span class="ep-key-item"><span class="ep-key-swatch" style="background:#fee2e2;border-color:#ef4444;"></span> Blackout</span>
                    <span class="ep-key-item"><span class="ep-key-swatch" style="background:#f3e8ff;border-color:#a855f7;"></span> Fair</span>
                    <span class="ep-key-item"><span class="ep-key-swatch" style="background:#ffedd5;border-color:#f97316;"></span> Season</span>
                    <span class="ep-key-item"><span class="ep-key-swatch" style="box-shadow:inset 0 0 0 2px #22c55e;"></span> Check-in</span>
                    <span class="ep-key-item"><span class="ep-key-swatch" style="box-shadow:inset 0 0 0 2px #1e3a8a;"></span> Check-out</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const MONTH_NAMES = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
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

    function epFullCalFormatDisplayDate(raw) {
        if (typeof enquiryProFormatNoticeDate === 'function') {
            return enquiryProFormatNoticeDate(raw);
        }
        if (!raw) return '—';
        const parts = String(raw).substring(0, 10).split('-');
        if (parts.length !== 3) return raw;
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return `${parseInt(parts[2], 10)} ${months[parseInt(parts[1], 10) - 1] || parts[1]} ${parts[0]}`;
    }

    function epFullCalFormatDisplayDateTime(val) {
        if (!val) return '—';
        const [datePart, timePart] = val.split('T');
        const dateFmt = epFullCalFormatDisplayDate(datePart);
        if (!timePart) return dateFmt;
        const [hh, mm] = timePart.split(':');
        return `${dateFmt} ${hh}:${mm}`;
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
        if (!applicable) return 'Standard';
        if (applicable.event_type === 'Blackout Date') return 'Blackout';
        if (applicable.event_type === 'Fair Date') return 'Fair';
        if (applicable.event_type === 'Season') return 'Season';
        return applicable.event_type || 'Rate';
    }

    function epFullCalRatePriceLine(applicable) {
        if (!applicable) return '';
        if (applicable.event_type === 'Blackout Date' && applicable.price != null && applicable.price !== '') {
            return `Room rate: ${applicable.price}`;
        }
        if (applicable.event_type === 'Fair Date' && applicable.price != null && applicable.price !== '') {
            return `Fair supplement: +${applicable.price}`;
        }
        if (applicable.event_type === 'Season') {
            const wp = applicable.weekday_price || applicable.weekend_price;
            if (wp != null && wp !== '') return `Season from: ${wp}`;
        }
        if (applicable.price != null && applicable.price !== '') return `Price: ${applicable.price}`;
        return '';
    }

    function epFullCalBuildTooltip(dateStr, ctx) {
        const dt = new Date(dateStr + 'T12:00:00');
        const dayName = dt.toLocaleString('en-US', { weekday: 'long' });
        const isStay = ctx.staySet.has(dateStr);
        const nightNum = ctx.stayNightMap.get(dateStr);
        const isCheckIn = dateStr === ctx.checkInDate;
        const isCheckOut = dateStr === ctx.checkOutDate;
        const isWeekend = typeof isWeekendDate === 'function' ? isWeekendDate(dt, ctx.weekendDays) : false;
        const applicable = enquiryProGetApplicableRateForDate(dateStr, ctx.hotelRates);
        const lines = [
            `${dayName}`,
            epFullCalFormatDisplayDate(dateStr),
            `Status: ${epFullCalStatusLabel(applicable, isStay, nightNum, isCheckIn, isCheckOut)}`
        ];
        if (applicable?.event) lines.push(`Event: ${applicable.event}`);
        const priceLine = epFullCalRatePriceLine(applicable);
        if (priceLine) lines.push(priceLine);
        if (isWeekend) lines.push('Weekend rate may apply');
        return lines.join(' · ');
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
            weekendDays: epFullCalWeekendDays()
        };
    }

    function epFullCalUpdateSummary(ctx) {
        const inEl = document.getElementById('epFullCalCheckIn');
        const outEl = document.getElementById('epFullCalCheckOut');
        const nightsEl = document.getElementById('epFullCalNights');
        if (inEl) inEl.textContent = epFullCalFormatDisplayDateTime(ctx.checkInVal);
        if (outEl) outEl.textContent = epFullCalFormatDisplayDateTime(ctx.checkOutVal);
        if (nightsEl) nightsEl.textContent = String(ctx.nights || 0);
    }

    window.enquiryProRenderFullStayCalendar = function (year, month) {
        const grid = document.getElementById('epFullStayCalendarGrid');
        const titleEl = document.getElementById('epFullCalMonthTitle');
        const hdrEl = document.getElementById('epFullCalWeekdayHdr');
        if (!grid || !epFullCalDeps()) return;

        const ctx = epFullCalBuildContext();
        epFullCalUpdateSummary(ctx);

        window._epFullCalView = { year, month };
        if (titleEl) titleEl.textContent = `${MONTH_NAMES[month]} ${year}`;

        if (hdrEl) {
            hdrEl.innerHTML = WEEKDAY_HDR.map((name, i) => {
                const colDate = new Date(2024, 0, 7 + i);
                const isWknd = typeof isWeekendDate === 'function' ? isWeekendDate(colDate, ctx.weekendDays) : (i === 0 || i === 6);
                return `<div class="ep-full-cal-weekday${isWknd ? ' ep-wknd' : ''}">${name}</div>`;
            }).join('');
        }

        const firstOfMonth = new Date(year, month, 1);
        const startPad = firstOfMonth.getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        const cells = [];
        for (let i = 0; i < startPad; i++) {
            const d = new Date(year, month, 1 - (startPad - i));
            cells.push(epFullCalRenderDayCell(d, ctx, true));
        }
        for (let day = 1; day <= daysInMonth; day++) {
            cells.push(epFullCalRenderDayCell(new Date(year, month, day), ctx, false));
        }
        let tailDay = 1;
        while (cells.length % 7 !== 0) {
            cells.push(epFullCalRenderDayCell(new Date(year, month + 1, tailDay), ctx, true));
            tailDay += 1;
        }

        let html = '';
        for (let i = 0; i < cells.length; i += 7) {
            html += `<div class="ep-full-cal-week-row">${cells.slice(i, i + 7).join('')}</div>`;
        }
        grid.innerHTML = html;
    };

    function epFullCalRenderDayCell(dateObj, ctx, isOtherMonth) {
        const y = dateObj.getFullYear();
        const m = dateObj.getMonth();
        const day = dateObj.getDate();
        const dateStr = `${y}-${String(m + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const dt = new Date(dateStr + 'T12:00:00');
        const dayName = dt.toLocaleString('en-US', { weekday: 'short' });
        const isWeekend = typeof isWeekendDate === 'function' ? isWeekendDate(dt, ctx.weekendDays) : false;
        const applicable = enquiryProGetApplicableRateForDate(dateStr, ctx.hotelRates);
        const isStay = ctx.staySet.has(dateStr);
        const nightNum = ctx.stayNightMap.get(dateStr);
        const isCheckIn = dateStr === ctx.checkInDate;
        const isCheckOut = dateStr === ctx.checkOutDate;

        let cls = 'ep-full-cal-day';
        if (isOtherMonth) cls += ' ep-other-month';
        if (isWeekend) cls += ' ep-weekend';
        if (applicable?.event_type) cls += ' ' + epFullCalRateCssClass(applicable.event_type);
        if (isStay) cls += ' ep-stay';
        if (isCheckIn) cls += ' ep-checkin';
        if (isCheckOut) cls += ' ep-checkout';

        const status = epFullCalStatusLabel(applicable, isStay, nightNum, isCheckIn, isCheckOut);
        const tip = epFullCalBuildTooltip(dateStr, ctx);

        let inner = `<span class="ep-full-cal-day-num">${day}</span>`;
        inner += `<span class="ep-full-cal-day-name">${dayName}</span>`;
        if (isStay) inner = `<span class="ep-full-cal-night-badge">N${nightNum}</span>` + inner;
        inner += `<span class="ep-full-cal-day-status">${epFullCalEscape(status)}</span>`;

        return `<div class="${cls}" title="${epFullCalEscape(tip)}" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="false">${inner}</div>`;
    }

    function epFullCalInitTooltips() {
        const modal = document.getElementById('epFullStayCalendarModal');
        if (!modal || typeof bootstrap === 'undefined') return;
        modal.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            const existing = bootstrap.Tooltip.getInstance(el);
            if (existing) existing.dispose();
            new bootstrap.Tooltip(el, { trigger: 'hover', container: modal });
        });
    }

    /** Stack above accommodation / other open Bootstrap modals (edit form hotel picker). */
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
            if (topBackdrop) {
                topBackdrop.style.zIndex = String(modalZ - 10);
            }
        });
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

        enquiryProRenderFullStayCalendar(window._epFullCalView.year, window._epFullCalView.month);

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

    document.addEventListener('DOMContentLoaded', function () {
        const calModalEl = document.getElementById('epFullStayCalendarModal');
        if (calModalEl) {
            calModalEl.addEventListener('show.bs.modal', function () {
                epFullCalRaiseAboveOtherModals(calModalEl);
            });
            calModalEl.addEventListener('shown.bs.modal', function () {
                epFullCalRaiseAboveOtherModals(calModalEl);
            });
        }

        const prevBtn = document.getElementById('epFullCalPrevMonth');
        const nextBtn = document.getElementById('epFullCalNextMonth');
        const todayBtn = document.getElementById('epFullCalTodayBtn');

        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                let { year, month } = window._epFullCalView || {};
                month -= 1;
                if (month < 0) { month = 11; year -= 1; }
                enquiryProRenderFullStayCalendar(year, month);
                epFullCalInitTooltips();
            });
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                let { year, month } = window._epFullCalView || {};
                month += 1;
                if (month > 11) { month = 0; year += 1; }
                enquiryProRenderFullStayCalendar(year, month);
                epFullCalInitTooltips();
            });
        }
        if (todayBtn) {
            todayBtn.addEventListener('click', function () {
                const ctx = epFullCalBuildContext();
                const anchor = ctx.checkInDate ? new Date(ctx.checkInDate + 'T12:00:00') : new Date();
                enquiryProRenderFullStayCalendar(anchor.getFullYear(), anchor.getMonth());
                epFullCalInitTooltips();
            });
        }
    });
})();
</script>
