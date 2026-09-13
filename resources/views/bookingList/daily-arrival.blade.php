@extends('layouts.layout', ['collapseSidebar' => true])
@section('title', 'Finance - Daily Arrival (Monthly)')

@section('content')
<div class="container-xxl flex-grow-1">
    <style>
        .finance-daily-arrival-wrap {
            overflow-x: auto;
            overflow-y: auto;
            max-height: calc(100vh - 240px);
            scrollbar-gutter: stable both-edges;
            position: relative;
            /* Hide native scrollbar while preserving scroll behavior */
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .finance-daily-arrival-wrap::-webkit-scrollbar {
            width: 0;
            height: 0;
        }

        .finance-daily-arrival-shell {
            position: relative;
            padding-bottom: 18px; /* space for custom scrollbar */
        }

        .finance-xscrollbar {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 14px;
            display: flex;
            align-items: center;
            padding: 0 2px;
            z-index: 12;
            background: linear-gradient(to top, rgba(248, 249, 250, 0.98), rgba(248, 249, 250, 0.85));
        }

        .finance-xscrollbar-track {
            position: relative;
            width: 100%;
            height: 8px;
            border-radius: 999px;
            background: #e5e7eb;
            overflow: hidden;
        }

        .finance-xscrollbar-thumb {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 80px;
            border-radius: 999px;
            background: #9ca3af;
            transform: translateX(0);
            cursor: grab;
            touch-action: none;
            transition: background-color 0.12s ease;
        }

        .finance-xscrollbar-thumb:hover {
            background: #6b7280;
        }

        .finance-xscrollbar-thumb.is-dragging {
            cursor: grabbing;
            background: #4b5563;
        }

        .finance-xscrollbar.is-disabled .finance-xscrollbar-track {
            background: #f1f5f9;
        }

        .finance-xscrollbar.is-disabled .finance-xscrollbar-thumb {
            opacity: 0.45;
            cursor: default;
        }

        .finance-daily-arrival-table {
            white-space: nowrap;
            min-width: 1200px;
            table-layout: fixed; /* stable widths => stable sticky offsets */
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .finance-daily-arrival-table th,
        .finance-daily-arrival-table td {
            border-color: #dee2e6;
        }

        /* Sticky column cells. `left` is calculated dynamically in JS to avoid gaps/flicker. */
        .finance-daily-arrival-table .sticky-col {
            position: sticky;
            left: 0; /* overwritten by JS */
            background: #fff !important; /* opaque to prevent bleeding */
            z-index: var(--sticky-z, 1);
            border-right: 0 !important; /* use box-shadow for separation */
            background-clip: padding-box;
        }

        /* Default header background (sticky header also overrides to white). */
        .finance-daily-arrival-table thead th {
            background: #f8f9fa;
        }

        /* Outer border */
        .finance-daily-arrival-table {
            outline: 1px solid #dee2e6;
            
        }

        /* Grid lines (NO borders; avoids sticky artifacts/bleeding)
           Vertical:
           - Sticky columns draw separator on their RIGHT edge
           - Scrollable columns draw separator on their LEFT edge
           Horizontal:
           - Header draws a bottom line
           - Body rows (except first) draw a top line
        */
        .finance-daily-arrival-table thead th.sticky-col {
            box-shadow: inset 0 1px 0 #dee2e6, inset -1px 0 0 #dee2e6, inset 0 -1px 0 #dee2e6;
        }
        .finance-daily-arrival-table thead th:not(.sticky-col) {
            box-shadow: inset 0 1px 0 #dee2e6, inset 1px 0 0 #dee2e6, inset 0 -1px 0 #dee2e6;
        }

        /* Header alignment (CSS-only, does not affect <td>). */
        .finance-daily-arrival-table thead th {
            vertical-align: middle;
            text-align: center;
        }
        /* Keep numeric header alignment (Bootstrap `text-end`) */
        .finance-daily-arrival-table thead th.text-end {
            text-align: right;
        }

        .finance-daily-arrival-table tbody tr:first-child td.sticky-col {
            box-shadow: inset -1px 0 0 #dee2e6;
        }
        .finance-daily-arrival-table tbody tr:first-child td:not(.sticky-col) {
            box-shadow: inset 1px 0 0 #dee2e6;
        }
        .finance-daily-arrival-table tbody tr:not(:first-child) td.sticky-col {
            box-shadow: inset -1px 0 0 #dee2e6, inset 0 1px 0 #dee2e6;
        }
        .finance-daily-arrival-table tbody tr:not(:first-child) td:not(.sticky-col) {
            box-shadow: inset 1px 0 0 #dee2e6, inset 0 1px 0 #dee2e6;
        }

        /* Ensure sticky cells stay fully opaque even when Bootstrap hovers */
        .finance-daily-arrival-table .sticky-col,
        .finance-daily-arrival-table .sticky-col * {
            background-color: #fff !important;
        }

        /* Reusable text truncation for stable layout */
        .finance-daily-arrival-table .cell-ellipsis {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            min-width: 0; /* important for ellipsis inside sticky cells */
        }

        /* Wrapping (multi-line) override for specific columns only */
        .finance-daily-arrival-table .wrap-text {
            white-space: normal !important;
            text-overflow: clip !important;
            overflow: hidden;
            overflow-wrap: anywhere;
            word-break: break-word;
            min-width: 0; /* important for sticky + wrapping */
        }

        /* Ellipsis enforcement for "all other" columns */
        .finance-daily-arrival-table .ellipsis-text {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            min-width: 0;
        }

        /* QUERY NO: ellipsis fix for the last sticky column.
           <td> ellipsis can fail under sticky + layering; the inner wrapper reliably clips. */
        .finance-daily-arrival-table td.sticky-col.sticky-last[data-sticky-index="6"] .query-no-ellipsis,
        .finance-daily-arrival-table th.sticky-col.sticky-last[data-sticky-index="6"] .query-no-ellipsis {
            display: block;
            width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            min-width: 0;
        }

        /* IMPORTANT: Column widths are controlled ONLY by <colgroup>. */

        /* ROE is editable: user can change ROE, then Rate(INR) recalculates via JS. */
        .finance-daily-arrival-table .roe-input {
            width: 100%;
            border: 0;
            outline: none;
            background: transparent;
            text-align: right;
            padding: 2px 4px;
            font: inherit;
            color: inherit;
        }

        .finance-daily-arrival-table .roe-input:focus {
            background: rgba(255, 255, 255, 0.95);
        }

        /* Used by JS to update the INR rate when ROE changes. */
        .finance-daily-arrival-table .inr-rate-cell {
            white-space: nowrap;
        }
    </style>
    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
        <div>
            <h5 class="mb-1">Finance - Daily Arrival</h5>
            <div class="text-muted" style="font-size: 0.85rem;">
                Showing bookings from <strong>{{ $startDate }}</strong> to <strong>{{ $endDate }}</strong>
            </div>
        </div>

        <form method="get" action="{{ route('finance.daily-arrival') }}" id="daily-arrival-filter-form" class="d-flex align-items-end gap-2 flex-wrap">
            <div>
                <label class="form-label mb-1" style="font-size: 0.8rem;">Month</label>
                <input type="month" name="month" id="filter-month" class="form-control" value="{{ $monthValue ?? '' }}" style="min-width: 170px;">
            </div>
            <div>
                <label class="form-label mb-1" style="font-size: 0.8rem;">Start Date</label>
                <input type="date" name="start_date" id="filter-start-date" class="form-control" value="{{ $startDate ?? '' }}" style="min-width: 150px;">
            </div>
            <div>
                <label class="form-label mb-1" style="font-size: 0.8rem;">End Date</label>
                <input type="date" name="end_date" id="filter-end-date" class="form-control" value="{{ $endDate ?? '' }}" style="min-width: 150px;">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Apply</button>
        </form>
    </div>

    @if(empty($rows))
        <div class="border rounded p-3 text-center text-muted">
            No bookings found for the selected month.
        </div>
    @else
        <div class="finance-daily-arrival-shell" data-xscroll-shell>
            <div class="finance-daily-arrival-wrap" data-xscroll-content>
                <table class="table table-striped align-middle finance-daily-arrival-table" style="font-size: 12px;">
                <colgroup>
                    <col style="width: 40px;">
                    <col style="width: 100px;">
                    <col style="width: 100px;">
                    <col style="width: 110px;"> <!-- Guest Name -->
                    <col style="width: 180px;"> <!-- Hotel -->
                    <col style="width: 110px;"> <!-- Agent Name -->
                    <col style="width: 110px;"> <!-- Query No -->
                    <col style="width: 80px;">
                    <col style="width: 120px;">
                    <col style="width: 60px;">
                    <col style="width: 120px;">
                    <col style="width: 60px;">
                    <col style="width: 120px;">
                    <col style="width: 70px;">
                    <col style="width: 120px;">
                    <col style="width: 130px;">
                    <col style="width: 90px;">
                    @for($i = 1; $i <= 10; $i++)
                        <col style="width: 120px;">
                        <col style="width: 120px;">
                    @endfor
                </colgroup>

                <thead class="table-light">
                    <tr>
                        <th class="sticky-col" data-sticky-index="0">#</th>
                        <th class="sticky-col" data-sticky-index="1">ARR DATE</th>
                        <th class="sticky-col" data-sticky-index="2">DEP DATE</th>
                        <th class="sticky-col" data-sticky-index="3">GUEST NAME</th>
                        <th class="sticky-col" data-sticky-index="4">HOTEL</th>
                        <th class="sticky-col" data-sticky-index="5">AGENT NAME</th>
                        <th class="sticky-col sticky-last" data-sticky-index="6">
                            <div class="query-no-ellipsis wrap-text">QUERY NO</div>
                        </th>

                        <th class="text-end">ADULTS</th>
                        <th class="text-end">RATES<br><span class="text-muted">SGD</span></th>

                        <th class="text-end">CBW</th>
                        <th class="text-end">RATES<br><span class="text-muted">CBW</span></th>

                        <th class="text-end">CBN</th>
                        <th class="text-end">RATES<br><span class="text-muted">CBN</span></th>

                        <th class="text-end">OTHER</th>
                        <th class="text-end">RATES<br><span class="text-muted">OTHER</span></th>

                        
                        <th class="text-end">ROE</th>
                        <th class="text-end">Rate(INR)</th>
                        @for($i = 1; $i <= 10; $i++)
                            <th class="text-end cell-ellipsis payment-col" title="PART PAYMENT {{ $i }}">PART PAYMENT {{ $i }}</th>
                            <th class="cell-ellipsis" title="PART PAYMENT {{ $i }} DATE">DATE {{ $i }}</th>
                        @endfor
                    </tr>
                </thead>

                <tbody>
                    @foreach($rows as $row)
                        <tr>
                            <td class="sticky-col" data-sticky-index="0">{{ $loop->iteration }}</td>
                            <td class="sticky-col" data-sticky-index="1">{{ $row['arr_date'] ?? '—' }}</td>
                            <td class="sticky-col" data-sticky-index="2">{{ $row['dep_date'] ?? '—' }}</td>
                            <td class="sticky-col cell-ellipsis wrap-text" data-sticky-index="3"
                                title="{{ $row['guest_name'] ?? '—' }}">{{ $row['guest_name'] ?? '—' }}</td>
                            <td class="sticky-col cell-ellipsis" data-sticky-index="4"
                                title="{{ $row['hotel'] ?? '—' }}">{{ $row['hotel'] ?? '—' }}</td>
                            <td class="sticky-col cell-ellipsis wrap-text" data-sticky-index="5"
                                title="{{ $row['agent_name'] ?? '—' }}">{{ $row['agent_name'] ?? '—' }}</td>
                            <td class="sticky-col sticky-last" data-sticky-index="6">
                                <div class="query-no-ellipsis wrap-text" title="{{ $row['query_no'] ?? '—' }}">
                                    {{ $row['query_no'] ?? '—' }}
                                </div>
                            </td>

                            <td class="text-end">{{ (int)($row['adults'] ?? 0) }}</td>
                            <td class="text-end">{{ number_format((float)($row['amount_sgd'] ?? 0), 0) }}</td>

                            <td class="text-end">{{ (int)($row['cbw_qty'] ?? 0) }}</td>
                            <td class="text-end">{{ number_format((float)($row['cbw_rate_sgd_pp'] ?? 0), 0) }}</td>

                            <td class="text-end">{{ (int)($row['cnb_qty'] ?? 0) }}</td>
                            <td class="text-end">{{ number_format((float)($row['cnb_rate_sgd_pp'] ?? 0), 0) }}</td>

                            <td class="text-end">{{ (int)($row['other_qty'] ?? 0) }}</td>
                            <td class="text-end">{{ number_format((float)($row['other_rate_sgd_pp'] ?? 0), 0) }}</td>


                            <td class="text-end">
                                <input
                                    type="number"
                                    inputmode="decimal"
                                    step="0.01"
                                    class="roe-input"
                                    value="{{ number_format((float)($row['roe'] ?? 0), 2, '.', '') }}"
                                    data-amount-sgd="{{ (float)($row['amount_sgd'] ?? 0) }}"
                                    aria-label="ROE"
                                />
                            </td>
                            <td class="text-end inr-rate-cell">{{ number_format((float)($row['amount_sgd'] ?? 0) * (float)($row['roe'] ?? 0), 0) }}</td>
                            @for($i = 1; $i <= 10; $i++)
                                @php
                                    $key = 'part_payment_' . $i;
                                    $dateKey = 'part_payment_date_' . $i;
                                    $raw = $row[$key] ?? null;
                                    $display = (is_numeric($raw)) ? number_format((float)$raw, 0) : '—';
                                    $dateRaw = $row[$dateKey] ?? null;
                                    $dateDisplay = !empty($dateRaw) ? $dateRaw : '—';
                                @endphp
                                <td class="text-end cell-ellipsis payment-col" title="{{ $display }}">{{ $display }}</td>
                                <td class="cell-ellipsis" title="{{ $dateDisplay }}">{{ $dateDisplay }}</td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
                </table>
            </div>
            <div class="finance-xscrollbar" data-xscrollbar>
                <div class="finance-xscrollbar-track" data-xscrollbar-track>
                    <div class="finance-xscrollbar-thumb" data-xscrollbar-thumb role="scrollbar" aria-label="Horizontal scroll"></div>
                </div>
            </div>
            <script>
                (function () {
                    const filterForm = document.getElementById('daily-arrival-filter-form');
                    const monthInput = document.getElementById('filter-month');
                    const startInput = document.getElementById('filter-start-date');
                    const endInput = document.getElementById('filter-end-date');

                    if (filterForm && monthInput && startInput && endInput) {
                        monthInput.addEventListener('change', function () {
                            if (monthInput.value) {
                                startInput.value = '';
                                endInput.value = '';
                            }
                        });

                        const clearMonthIfDateUsed = function () {
                            if (startInput.value || endInput.value) {
                                monthInput.value = '';
                            }
                        };
                        startInput.addEventListener('change', clearMonthIfDateUsed);
                        endInput.addEventListener('change', clearMonthIfDateUsed);

                        filterForm.addEventListener('submit', function () {
                            // Final cleanup before request to prevent stale values.
                            if (startInput.value || endInput.value) {
                                monthInput.value = '';
                            } else if (monthInput.value) {
                                startInput.value = '';
                                endInput.value = '';
                            }
                        });
                    }
                })();
            </script>
            <script>
                (function () {
                    const table = document.querySelector('.finance-daily-arrival-table');
                    if (!table) return;

                    function roundToDevicePixel(x) {
                        const dpr = window.devicePixelRatio || 1;
                        return Math.round(x * dpr) / dpr;
                    }

                    function applyOffsets() {
                        const headerRow = table.tHead && table.tHead.rows.length
                            ? table.tHead.rows[0]
                            : table.querySelector('thead tr');
                        if (!headerRow) return;

                        // Collect sticky header cells in visual order (by data-sticky-index).
                        const stickyHeaderCells = Array.from(
                            headerRow.querySelectorAll('th.sticky-col[data-sticky-index]')
                        ).sort((a, b) => {
                            return Number(a.dataset.stickyIndex) - Number(b.dataset.stickyIndex);
                        });

                        const stickyCount = stickyHeaderCells.length;
                        if (!stickyCount) return;

                        const offsetsByIndex = {};
                        let left = 0;
                        for (let pos = 0; pos < stickyCount; pos++) {
                            const th = stickyHeaderCells[pos];
                            const idx = Number(th.dataset.stickyIndex);
                            const w = th.getBoundingClientRect().width;
                            offsetsByIndex[idx] = roundToDevicePixel(left);
                            left += w;
                        }

                        const lastStickyIdx = Number(stickyHeaderCells[stickyCount - 1].dataset.stickyIndex);

                        // Ensure exactly the last sticky column gets the `sticky-last` class
                        // (so box-shadow divider doesn't show after the final fixed column).
                        stickyHeaderCells.forEach((th) => {
                            const idx = Number(th.dataset.stickyIndex);
                            if (idx === lastStickyIdx) th.classList.add('sticky-last');
                            else th.classList.remove('sticky-last');
                        });
                        table.querySelectorAll('.sticky-col').forEach((el) => {
                            const idx = Number(el.dataset.stickyIndex);
                            if (Number.isFinite(idx) && idx === lastStickyIdx) el.classList.add('sticky-last');
                            else el.classList.remove('sticky-last');
                        });

                        for (let pos = 0; pos < stickyCount; pos++) {
                            const idx = Number(stickyHeaderCells[pos].dataset.stickyIndex);
                            const zHeader = 1000 + (stickyCount - pos);
                            const zBody = 500 + (stickyCount - pos);
                            const leftPx = offsetsByIndex[idx] ?? 0;

                            table.querySelectorAll('thead .sticky-col[data-sticky-index="' + idx + '"]')
                                .forEach((el) => {
                                    el.style.left = leftPx + 'px';
                                    el.style.setProperty('--sticky-z', zHeader);
                                });

                            table.querySelectorAll('tbody .sticky-col[data-sticky-index="' + idx + '"]')
                                .forEach((el) => {
                                    el.style.left = leftPx + 'px';
                                    el.style.setProperty('--sticky-z', zBody);
                                });
                        }
                    }

                    let rafId = null;
                    const schedule = () => {
                        if (rafId) cancelAnimationFrame(rafId);
                        rafId = requestAnimationFrame(applyOffsets);
                    };

                    // Ensure offsets are computed after layout.
                    window.addEventListener('load', schedule, { once: true });
                    document.addEventListener('DOMContentLoaded', schedule);
                    window.addEventListener('resize', schedule);
                    window.addEventListener('orientationchange', schedule);

                    schedule();
                })();
            </script>

            <script>
                (function () {
                    const table = document.querySelector('.finance-daily-arrival-table');
                    if (!table) return;

                    const inrFormatter = new Intl.NumberFormat('en-US', {
                        maximumFractionDigits: 0,
                    });

                    function getNumber(val) {
                        const n = parseFloat(val);
                        return Number.isFinite(n) ? n : 0;
                    }

                    function recalcForRow(roeInput) {
                        const row = roeInput.closest('tr');
                        if (!row) return;

                        const amountSgd = getNumber(roeInput.dataset.amountSgd);
                        const roe = getNumber(roeInput.value);
                        const inr = Math.round(amountSgd * roe);

                        const inrCell = row.querySelector('.inr-rate-cell');
                        if (inrCell) inrCell.textContent = inrFormatter.format(inr);
                    }

                    // Event delegation: handles all current + future rows.
                    table.addEventListener('input', function (e) {
                        if (e.target && e.target.classList && e.target.classList.contains('roe-input')) {
                            recalcForRow(e.target);
                        }
                    });

                    table.addEventListener('change', function (e) {
                        if (e.target && e.target.classList && e.target.classList.contains('roe-input')) {
                            recalcForRow(e.target);
                        }
                    });
                })();
            </script>
            <script>
                (function () {
                    const MIN_THUMB_WIDTH = 40;

                    function initCustomXScroll(shell) {
                        const content = shell.querySelector('[data-xscroll-content]');
                        const bar = shell.querySelector('[data-xscrollbar]');
                        const track = shell.querySelector('[data-xscrollbar-track]');
                        const thumb = shell.querySelector('[data-xscrollbar-thumb]');
                        if (!content || !bar || !track || !thumb) return;

                        let dragState = null;
                        let rafId = null;

                        function getMetrics() {
                            const viewportWidth = content.clientWidth || 0;
                            const fullWidth = content.scrollWidth || 0;
                            const maxScrollLeft = Math.max(0, fullWidth - viewportWidth);
                            const trackWidth = track.clientWidth || 0;

                            if (!trackWidth || !fullWidth || viewportWidth <= 0) {
                                return {
                                    viewportWidth,
                                    fullWidth,
                                    maxScrollLeft,
                                    trackWidth,
                                    thumbWidth: trackWidth,
                                    maxThumbLeft: 0,
                                };
                            }

                            const ratio = viewportWidth / fullWidth;
                            const thumbWidth = Math.min(
                                trackWidth,
                                Math.max(MIN_THUMB_WIDTH, Math.round(trackWidth * ratio))
                            );
                            const maxThumbLeft = Math.max(0, trackWidth - thumbWidth);

                            return {
                                viewportWidth,
                                fullWidth,
                                maxScrollLeft,
                                trackWidth,
                                thumbWidth,
                                maxThumbLeft,
                            };
                        }

                        function render() {
                            rafId = null;
                            const m = getMetrics();

                            thumb.style.width = m.thumbWidth + 'px';

                            const hasOverflow = m.maxScrollLeft > 0;
                            bar.classList.toggle('is-disabled', !hasOverflow);

                            if (!hasOverflow) {
                                thumb.style.transform = 'translateX(0px)';
                                return;
                            }

                            const ratio = content.scrollLeft / m.maxScrollLeft;
                            const left = Math.max(0, Math.min(m.maxThumbLeft, ratio * m.maxThumbLeft));
                            thumb.style.transform = 'translateX(' + left + 'px)';
                        }

                        function scheduleRender() {
                            if (rafId !== null) return;
                            rafId = requestAnimationFrame(render);
                        }

                        function scrollByThumbDelta(deltaX) {
                            const m = getMetrics();
                            if (m.maxScrollLeft <= 0 || m.maxThumbLeft <= 0) return;
                            const scrollRatio = m.maxScrollLeft / m.maxThumbLeft;
                            content.scrollLeft = dragState.startScrollLeft + (deltaX * scrollRatio);
                        }

                        content.addEventListener('scroll', scheduleRender, { passive: true });
                        window.addEventListener('resize', scheduleRender);

                        if (window.ResizeObserver) {
                            const ro = new ResizeObserver(scheduleRender);
                            ro.observe(content);
                            const table = content.querySelector('table');
                            if (table) ro.observe(table);
                        }

                        track.addEventListener('pointerdown', function (e) {
                            if (e.target === thumb) return;
                            const m = getMetrics();
                            if (m.maxScrollLeft <= 0) return;

                            const rect = track.getBoundingClientRect();
                            const clickX = e.clientX - rect.left;
                            const targetLeft = Math.max(0, Math.min(m.maxThumbLeft, clickX - (m.thumbWidth / 2)));
                            const ratio = m.maxThumbLeft > 0 ? (targetLeft / m.maxThumbLeft) : 0;
                            content.scrollLeft = ratio * m.maxScrollLeft;
                            scheduleRender();
                        });

                        thumb.addEventListener('pointerdown', function (e) {
                            const m = getMetrics();
                            if (m.maxScrollLeft <= 0) return;

                            e.preventDefault();
                            thumb.classList.add('is-dragging');
                            dragState = {
                                pointerId: e.pointerId,
                                startX: e.clientX,
                                startScrollLeft: content.scrollLeft,
                            };
                            thumb.setPointerCapture(e.pointerId);
                        });

                        thumb.addEventListener('pointermove', function (e) {
                            if (!dragState || e.pointerId !== dragState.pointerId) return;
                            const deltaX = e.clientX - dragState.startX;
                            scrollByThumbDelta(deltaX);
                            scheduleRender();
                        });

                        function stopDrag(e) {
                            if (!dragState || e.pointerId !== dragState.pointerId) return;
                            thumb.classList.remove('is-dragging');
                            dragState = null;
                        }

                        thumb.addEventListener('pointerup', stopDrag);
                        thumb.addEventListener('pointercancel', stopDrag);
                        thumb.addEventListener('lostpointercapture', function () {
                            thumb.classList.remove('is-dragging');
                            dragState = null;
                        });

                        scheduleRender();
                    }

                    document.querySelectorAll('[data-xscroll-shell]').forEach(initCustomXScroll);
                })();
            </script>
        </div>
    @endif
</div>
@endsection

{{-- Sidebar collapse controlled server-side via `collapseSidebar` layout flag. --}}