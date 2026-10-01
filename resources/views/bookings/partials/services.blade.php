{{--
  Shared professional service-modal design + helpers
  for confirmed / definite / actual (and compatible list pages).

  @include('bookings.partials.services')
--}}
<style>
    :root {
        --svc-ink: #0f172a;
        --svc-muted: #64748b;
        --svc-line: #e2e8f0;
        --svc-surface: #ffffff;
        --svc-canvas: #f1f5f9;
        --svc-accent: #0f766e;
        --svc-accent-soft: #ccfbf1;
        --svc-header: #0f172a;
        --svc-danger: #b91c1c;
        --svc-warn: #b45309;
    }

    .svc-modal .modal-content {
        border: 1px solid var(--svc-line);
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 12px 40px rgba(15, 23, 42, 0.14);
    }

    .svc-modal .modal-header {
        background: var(--svc-header) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        padding: 0.85rem 1.1rem !important;
    }

    .svc-modal .modal-header .modal-title,
    .svc-modal .modal-header h5,
    .svc-modal .modal-header h6 {
        color: #fff;
        font-size: 0.95rem;
        font-weight: 600;
        letter-spacing: 0.01em;
        margin: 0;
    }

    .svc-modal .modal-body {
        background: var(--svc-canvas) !important;
        padding: 1rem !important;
    }

    .svc-modal .modal-footer {
        background: var(--svc-surface) !important;
        border-top: 1px solid var(--svc-line) !important;
        padding: 0.75rem 1rem !important;
    }

    .svc-panel {
        background: var(--svc-surface);
        border: 1px solid var(--svc-line);
        border-radius: 6px;
        margin-bottom: 0.75rem;
        overflow: hidden;
    }

    .svc-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        border-bottom: 1px solid var(--svc-line);
        background: #fff;
    }

    .svc-panel-head-main {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
    }

    .svc-thumb {
        width: 44px;
        height: 44px;
        border-radius: 4px;
        object-fit: cover;
        border: 1px solid var(--svc-line);
        flex-shrink: 0;
        background: #e2e8f0;
    }

    .svc-thumb-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--svc-muted);
        font-size: 1.15rem;
    }

    .svc-title {
        font-size: 0.95rem;
        font-weight: 650;
        color: var(--svc-ink);
        margin: 0;
        line-height: 1.3;
        word-break: break-word;
    }

    .svc-subtitle {
        font-size: 0.75rem;
        color: var(--svc-muted);
        margin: 0.15rem 0 0;
    }

    .svc-price {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--svc-ink);
        white-space: nowrap;
        background: var(--svc-accent-soft);
        color: var(--svc-accent);
        border: 1px solid #99f6e4;
        border-radius: 4px;
        padding: 0.35rem 0.55rem;
    }

    .svc-section {
        background: var(--svc-surface);
        border: 1px solid var(--svc-line);
        border-radius: 6px;
        margin-bottom: 0.75rem;
    }

    .svc-section-title {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--svc-muted);
        padding: 0.65rem 0.9rem 0.35rem;
        margin: 0;
        border-bottom: none;
    }

    .svc-dl {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
        margin: 0;
    }

    .svc-dl-row {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        padding: 0.55rem 0.9rem;
        border-top: 1px solid var(--svc-line);
    }

    .svc-dl-row.full {
        grid-column: 1 / -1;
    }

    .svc-dl-label {
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--svc-muted);
    }

    .svc-dl-value {
        font-size: 0.86rem;
        font-weight: 550;
        color: var(--svc-ink);
        line-height: 1.35;
        word-break: break-word;
    }

    .svc-meal-plan {
        white-space: normal !important;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .svc-amount {
        font-variant-numeric: tabular-nums;
        font-weight: 700;
        color: var(--svc-ink);
    }

    .svc-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        padding: 0.75rem 0.9rem 0.9rem;
    }

    .svc-btn {
        border-radius: 4px !important;
        font-size: 0.78rem !important;
        font-weight: 600 !important;
        padding: 0.35rem 0.7rem !important;
        letter-spacing: 0.01em;
    }

    .svc-btn-edit {
        background: #1e293b !important;
        border-color: #1e293b !important;
        color: #fff !important;
    }

    .svc-btn-approve {
        background: #fff !important;
        border: 1px solid #0f766e !important;
        color: #0f766e !important;
    }

    .svc-btn-reject {
        background: #fff !important;
        border: 1px solid #b91c1c !important;
        color: #b91c1c !important;
    }

    .svc-footer-btn {
        border-radius: 4px !important;
        font-size: 0.8rem !important;
        font-weight: 600 !important;
    }

    .svc-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.7rem;
        font-weight: 650;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-radius: 999px;
        padding: 0.2rem 0.5rem;
        border: 1px solid var(--svc-line);
        color: var(--svc-muted);
        background: #f8fafc;
    }

    .svc-status-pill.is-approved {
        color: #0f766e;
        border-color: #99f6e4;
        background: var(--svc-accent-soft);
    }

    @media (max-width: 575.98px) {
        .svc-dl {
            grid-template-columns: 1fr;
        }
        .svc-panel-head {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    /* Soften legacy colorful bodies when hosted in the professional shell */
    .svc-modal .card {
        border: 1px solid var(--svc-line) !important;
        border-radius: 6px !important;
        box-shadow: none !important;
        border-left: 1px solid var(--svc-line) !important;
        overflow: hidden;
    }
    .svc-modal .card-header {
        background: #fff !important;
        border-bottom: 1px solid var(--svc-line) !important;
        color: var(--svc-ink) !important;
    }
    .svc-modal .card-header h5,
    .svc-modal .card-header h6,
    .svc-modal .card-header .text-white,
    .svc-modal .card-header small,
    .svc-modal .card-header .opacity-90 {
        color: var(--svc-ink) !important;
        opacity: 1 !important;
    }
    .svc-modal .card-header .badge.bg-white {
        background: var(--svc-accent-soft) !important;
        color: var(--svc-accent) !important;
        border: 1px solid #99f6e4;
        border-radius: 4px;
        font-weight: 700;
    }
    .svc-modal .rounded-circle.me-2,
    .svc-modal .rounded-circle.p-1 {
        border-radius: 4px !important;
        background: #e2e8f0 !important;
    }
    .svc-modal .rounded-circle.p-1 i,
    .svc-modal .rounded-circle.me-2 i {
        color: var(--svc-ink) !important;
    }
    .svc-modal .bg-light.rounded {
        background: #fff !important;
        border: 1px solid var(--svc-line);
        border-radius: 6px !important;
    }
    .svc-modal .text-success.fw-bold,
    .svc-modal .fw-bold.text-success {
        color: var(--svc-ink) !important;
    }
    .svc-modal .fw-bold.text-danger {
        color: var(--svc-ink) !important;
    }
    .svc-modal .text-primary {
        color: var(--svc-ink) !important;
    }

    /* Arrival / Local / Restaurant static Blade modals (follow-ups, new-enquiries, etc.) */
    .service-modal-compact .modal-content {
        border: 1px solid var(--svc-line);
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 12px 40px rgba(15, 23, 42, 0.14);
    }
    .service-modal-compact .modal-header {
        border-bottom: none !important;
        padding: 0.85rem 1.1rem !important;
    }
    .service-modal-compact .modal-body {
        background: var(--svc-canvas) !important;
    }
    .service-modal-compact .modal-footer {
        background: #fff !important;
        border-top: 1px solid var(--svc-line) !important;
    }
    .service-modal-compact .card {
        border: 1px solid var(--svc-line) !important;
        border-radius: 6px !important;
        box-shadow: none !important;
        border-left-width: 3px !important;
    }
    .service-modal-compact .card-header .badge.bg-white {
        border-radius: 4px;
        font-weight: 700;
    }
    .service-modal-compact .bg-light.rounded {
        background: #fff !important;
        border: 1px solid var(--svc-line);
        border-radius: 6px !important;
    }
    .service-modal-compact .rounded-circle.p-1 {
        border-radius: 6px !important;
    }
    .service-modal-compact .text-center .bg-white.rounded.p-1,
    .service-modal-compact .text-center .bg-white.rounded.p-1.border {
        border: 1px solid var(--svc-line) !important;
        border-radius: 6px !important;
    }
    .service-modal-compact .text-center .bg-white.rounded .fw-bold.text-success,
    .service-modal-compact .text-center .bg-white.rounded .fw-bold.text-warning {
        color: var(--svc-ink) !important;
    }
    .service-modal-compact [style*="border-color: #28a745"],
    .service-modal-compact [style*="border-color: #17a2b8"],
    .service-modal-compact [style*="border-color: #fd79a8"],
    .service-modal-compact [style*="border-color: #fd9853"],
    .service-modal-compact [style*="border-color: #6c757d"] {
        border-color: var(--svc-line) !important;
        background: #fff !important;
    }
    .service-modal-compact .fw-bold[style*="color: #fd79a8"],
    .service-modal-compact .fw-bold[style*="color: #fd9853"],
    .service-modal-compact .fw-bold[style*="color: #00cec9"] {
        color: var(--svc-accent) !important;
    }

    .svc-guest-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
        padding: 0.55rem 0.9rem 0.75rem;
    }
    .svc-guest-box {
        border: 1px solid var(--svc-line);
        border-radius: 6px;
        padding: 0.55rem 0.4rem;
        text-align: center;
        background: #fff;
    }
    .svc-guest-box .num {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--svc-ink);
        line-height: 1.2;
    }
    .svc-guest-box .lbl {
        font-size: 0.65rem;
        color: var(--svc-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-weight: 600;
    }
    .svc-total-bar {
        margin: 0 0.9rem 0.75rem;
        background: var(--svc-header);
        color: #fff;
        border-radius: 6px;
        padding: 0.45rem 0.75rem;
        font-size: 0.8rem;
        font-weight: 650;
        text-align: center;
    }
</style>
<script>
(function (w) {
    'use strict';

    w.resolveServiceDisplayCurrency = function (preferred, fallback) {
        var cur = (preferred || fallback || w.bookingCurrency || 'SGD');
        if (typeof cur !== 'string') {
            cur = String(cur || 'SGD');
        }
        cur = cur.trim().toUpperCase();
        return cur || 'SGD';
    };

    w.serviceMoney = function (amount, preferredCurrency, fallbackCurrency) {
        var cur = w.resolveServiceDisplayCurrency(preferredCurrency, fallbackCurrency);
        var n = parseFloat(amount);
        if (isNaN(n)) {
            n = 0;
        }
        return cur + ' ' + n.toFixed(2);
    };

    w.formatHotelMealPlanFromRooms = function (rooms) {
        if (!Array.isArray(rooms) || rooms.length === 0) {
            return 'Room Only';
        }
        var room = rooms[0] || {};
        var beds = Array.isArray(room.beds) ? room.beds : [];
        if (beds.length === 0) {
            return 'Room Only';
        }
        var bed = beds[0] || {};

        var selected = bed.selectedMeals;
        if (selected && typeof selected === 'object') {
            var labels = [];
            var values = Array.isArray(selected) ? selected : Object.values(selected);
            values.forEach(function (meal) {
                if (typeof meal === 'string' && meal.trim()) {
                    labels.push(meal.trim());
                } else if (meal && typeof meal === 'object' && meal.type) {
                    labels.push(String(meal.type).trim());
                }
            });
            if (labels.length) {
                return labels.join(', ');
            }
        }

        var mealTypes = bed.mealTypes;
        if (Array.isArray(mealTypes) && mealTypes.length > 0) {
            var first = mealTypes[0];
            if (typeof first === 'string' && first.trim()) {
                return first.trim();
            }
            if (first && typeof first === 'object' && first.type) {
                return String(first.type).trim();
            }
        }

        return 'Room Only';
    };

    w.escapeServiceHtml = function (value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    };

    w.renderHotelMealPlanHtml = function (mealPlan) {
        var label = (mealPlan && String(mealPlan).trim()) ? String(mealPlan).trim() : 'Room Only';
        var safe = w.escapeServiceHtml(label);
        return '' +
            '<div class="svc-dl-row full">' +
                '<span class="svc-dl-label">Meal Plan</span>' +
                '<span class="svc-dl-value svc-meal-plan" title="' + safe + '">' + safe + '</span>' +
            '</div>';
    };

    function dlRow(label, value, full) {
        return '' +
            '<div class="svc-dl-row' + (full ? ' full' : '') + '">' +
                '<span class="svc-dl-label">' + w.escapeServiceHtml(label) + '</span>' +
                '<span class="svc-dl-value">' + (value == null || value === '' ? '—' : value) + '</span>' +
            '</div>';
    }

    function section(title, innerHtml) {
        return '' +
            '<div class="svc-section">' +
                '<h6 class="svc-section-title">' + w.escapeServiceHtml(title) + '</h6>' +
                '<div class="svc-dl">' + innerHtml + '</div>' +
            '</div>';
    }

    function hasChildBlock(obj) {
        if (!obj) return false;
        var children = parseInt(obj.children || 0, 10) || 0;
        var total = parseFloat(obj.total_cost != null ? obj.total_cost : (obj.totalCost || 0)) || 0;
        // Price/catalog alone does not mean selected — need children qty or a positive line total
        return children > 0 || total > 0;
    }

    function nightsNum(hotelBooking) {
        if (typeof hotelBooking.nights === 'number') {
            return hotelBooking.nights;
        }
        return parseInt(hotelBooking.nights, 10) || 0;
    }

    /**
     * Professional hotel details body (keeps hotel_buttons_* host for existing action logic).
     */
    w.renderProfessionalHotelContent = function (hotelBooking, opts) {
        opts = opts || {};
        var tourId = opts.tourId;
        var hotelOrderIndex = opts.hotelOrderIndex;
        var bookingIndex = opts.bookingIndex;
        var currency = w.resolveServiceDisplayCurrency(
            (hotelBooking && hotelBooking.currency) || opts.currency,
            w.bookingCurrency
        );
        var money = function (n) {
            return w.escapeServiceHtml(w.serviceMoney(n, currency));
        };
        var name = w.escapeServiceHtml(hotelBooking.hotelName || 'Hotel Accommodation');
        var location = w.escapeServiceHtml(hotelBooking.location || '—');
        var mealPlan = (hotelBooking.mealPlan && String(hotelBooking.mealPlan).trim())
            ? String(hotelBooking.mealPlan).trim()
            : 'Room Only';
        var mealSafe = w.escapeServiceHtml(mealPlan);
        var isApproved = hotelBooking.isApprove == 1
            || hotelBooking.isApprove === '1'
            || hotelBooking.isApprove === true
            || hotelBooking.is_approve == 1
            || hotelBooking.is_approve === '1'
            || hotelBooking.is_approve === true;
        var statusPill = isApproved
            ? '<span class="svc-status-pill is-approved">Approved</span>'
            : '<span class="svc-status-pill">Pending approval</span>';

        var orderTypeRaw = String(hotelBooking.orderType || hotelBooking.order_type || '').trim().toLowerCase();
        var isOnlineOrder = orderTypeRaw === 'online';
        var orderTypeLabel = isOnlineOrder ? 'Online Order' : 'Offline Order';
        var orderTypeBadgeClass = isOnlineOrder ? 'bg-success' : 'bg-secondary';
        var orderTypeIcon = isOnlineOrder ? 'ri-global-line' : 'ri-store-2-line';
        var orderTypeBadge = '<span class="badge ' + orderTypeBadgeClass + '" style="font-size:0.7rem;font-weight:600;padding:0.35em 0.65em;letter-spacing:0.02em;" title="' + orderTypeLabel + '">' +
            '<i class="' + orderTypeIcon + ' me-1"></i>' + orderTypeLabel +
            '</span>';

        var thumb = hotelBooking.image
            ? '<img src="' + w.escapeServiceHtml(hotelBooking.image) + '" alt="' + name + '" class="svc-thumb">'
            : '<div class="svc-thumb svc-thumb-fallback"><i class="ri-hotel-line"></i></div>';

        var transferHtml = '';
        var tf = hotelBooking.transferOptions;
        if (tf && (tf.transfer_required === true || tf.transfer_required === 'true' || tf.transfer_required === 'Yes')) {
            var vehicle = '—';
            if (tf.vehicle_details && tf.vehicle_details.vehicle_name) {
                vehicle = w.escapeServiceHtml(tf.vehicle_details.vehicle_name);
                if (tf.vehicle_details.seating_capacity) {
                    vehicle += ' <span class="text-muted" style="font-weight:500;">(' + w.escapeServiceHtml(tf.vehicle_details.seating_capacity) + ' seats)</span>';
                }
            } else if (tf.vehicle_id) {
                vehicle = w.escapeServiceHtml(tf.vehicle_id);
            }
            transferHtml = section('Transfer',
                dlRow('Type', w.escapeServiceHtml(tf.type || 'N/A')) +
                dlRow('Cost', tf.totalPrice && tf.totalPrice > 0 ? ('<span class="svc-amount">' + money(tf.totalPrice) + '</span>') : '—') +
                (tf.destination_name ? dlRow('Destination', w.escapeServiceHtml(tf.destination_name)) : '') +
                (tf.pickup_location_name ? dlRow('Pickup', w.escapeServiceHtml(tf.pickup_location_name)) : '') +
                dlRow('Vehicle', vehicle, true)
            );
        }

        var childHtml = '';
        var cwb = hotelBooking.childWithBed;
        var cnb = hotelBooking.childWithoutBed;
        if (hasChildBlock(cwb) || hasChildBlock(cnb)) {
            var nights = nightsNum(hotelBooking);
            var childRows = '';
            if (hasChildBlock(cwb)) {
                var cwbTotal = (parseFloat(cwb.price || 0) || 0) * (parseInt(cwb.children || 0, 10) || 0) * nights;
                childRows +=
                    dlRow('Child with bed', 'Yes') +
                    dlRow('Children', w.escapeServiceHtml(cwb.children || 0)) +
                    dlRow('Price / night', '<span class="svc-amount">' + money(cwb.price || 0) + '</span>') +
                    dlRow('Line total', '<span class="svc-amount">' + money(cwbTotal) + '</span>');
            }
            if (hasChildBlock(cnb)) {
                var cnbTotal = (parseFloat(cnb.price || 0) || 0) * (parseInt(cnb.children || 0, 10) || 0) * nights;
                childRows +=
                    dlRow('Child without bed', 'Yes') +
                    dlRow('Children', w.escapeServiceHtml(cnb.children || 0)) +
                    dlRow('Price / night', '<span class="svc-amount">' + money(cnb.price || 0) + '</span>') +
                    dlRow('Line total', '<span class="svc-amount">' + money(cnbTotal) + '</span>');
            }
            childHtml = section('Child Accommodation', childRows);
        }

        var babyCotHtml = '';
        var babyCotEnabled = !!(
            hotelBooking.hasInfant === true
            || hotelBooking.has_infant === true
            || hotelBooking.has_infant === 1
            || parseInt(hotelBooking.baby_cot || hotelBooking.babyCot || 0, 10) === 1
        );
        var babyCotUnit = parseFloat(hotelBooking.baby_cot_price != null ? hotelBooking.baby_cot_price : (hotelBooking.babyCotPrice || 0)) || 0;
        var babyCotTotalStored = parseFloat(hotelBooking.baby_cot_cost != null ? hotelBooking.baby_cot_cost : (hotelBooking.babyCotCost || 0)) || 0;
        var babyCotInfants = parseInt(
            hotelBooking.selected_infants != null ? hotelBooking.selected_infants
                : (hotelBooking.infants != null ? hotelBooking.infants : (hotelBooking.infant || 0)),
            10
        ) || 0;
        if (babyCotEnabled && babyCotInfants <= 0) babyCotInfants = 1;
        var babyCotRooms = Math.max(1, parseInt(hotelBooking.number_of_rooms != null ? hotelBooking.number_of_rooms : (hotelBooking.rooms || 1), 10) || 1);
        var babyCotNights = Math.max(1, nightsNum(hotelBooking) || 1);
        if (babyCotTotalStored <= 0 && babyCotUnit > 0 && babyCotEnabled) {
            babyCotTotalStored = babyCotUnit * babyCotInfants * babyCotRooms * babyCotNights;
        }
        if (babyCotEnabled && (babyCotUnit > 0 || babyCotTotalStored > 0)) {
            babyCotHtml = section('Baby Cot',
                dlRow('Baby cot', 'Yes') +
                dlRow('Infants', w.escapeServiceHtml(babyCotInfants)) +
                dlRow('Rooms', w.escapeServiceHtml(babyCotRooms)) +
                dlRow('Nights', w.escapeServiceHtml(babyCotNights)) +
                dlRow('Price / night', '<span class="svc-amount">' + money(babyCotUnit) + '</span>') +
                dlRow('Line total', '<span class="svc-amount">' + money(babyCotTotalStored) + '</span>', true)
            );
        }

        return '' +
            '<div class="svc-panel">' +
                '<div class="svc-panel-head">' +
                    '<div class="svc-panel-head-main">' +
                        thumb +
                        '<div style="min-width:0;">' +
                            '<h6 class="svc-title">' + name + '</h6>' +
                            '<p class="svc-subtitle mb-1"><i class="ri-map-pin-line me-1"></i>' + location +
                            (hotelBooking.country ? ' · ' + w.escapeServiceHtml(hotelBooking.country) : '') +
                            '</p>' +
                            '<div class="d-flex align-items-center flex-wrap gap-1">' + orderTypeBadge + '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="text-end">' +
                        '<div class="svc-price mb-1">' + money(hotelBooking.totalPrice || 0) + '</div>' +
                        statusPill +
                    '</div>' +
                '</div>' +
            '</div>' +

            section('Stay Schedule',
                dlRow('Check-in', w.escapeServiceHtml(hotelBooking.checkInDate || 'N/A')) +
                dlRow('Check-out', w.escapeServiceHtml(hotelBooking.checkOutDate || 'N/A')) +
                dlRow('Nights', w.escapeServiceHtml(hotelBooking.nights != null ? hotelBooking.nights : 'N/A')) +
                dlRow('Check-in time', w.escapeServiceHtml(hotelBooking.checkInTime || 'N/A'))
            ) +

            section('Room Details',
                dlRow('Rooms', w.escapeServiceHtml(hotelBooking.rooms || '1')) +
                dlRow('Room type', w.escapeServiceHtml(hotelBooking.roomType || 'Standard')) +
                dlRow('Bed type', w.escapeServiceHtml(hotelBooking.bedType || 'N/A')) +
                dlRow('Meal plan', '<span class="svc-meal-plan" title="' + mealSafe + '">' + mealSafe + '</span>', true)
            ) +

            transferHtml +
            childHtml +
            babyCotHtml +

            section('Pricing',
                dlRow('Hotel total', '<span class="svc-amount">' + money(hotelBooking.totalPrice || 0) + '</span>', true) +
                (hotelBooking.currency || hotelBooking.country
                    ? dlRow('Currency', w.escapeServiceHtml(currency)) +
                      dlRow('Country', w.escapeServiceHtml(hotelBooking.country || '—'))
                    : '')
            ) +

            '<div class="svc-section">' +
                '<h6 class="svc-section-title">Actions</h6>' +
                '<div class="svc-actions" id="hotel_buttons_' + tourId + '_' + hotelOrderIndex + '_' + bookingIndex + '"></div>' +
            '</div>';
    };

    /**
     * Professional attraction details body (matches enquiry-service-cards/attraction).
     * booking: mapped attractionBooking from get-attraction-data OR raw order JSON item
     * opts: { tourId, attractionOrderIndex, bookingIndex, currency, isPro, actionsHostId, subtitle }
     */
    w.renderProfessionalAttractionContent = function (booking, opts) {
        opts = opts || {};
        booking = booking || {};
        var details = booking.attraction_details || booking.attractionDetails || {};
        var td = booking.ticketDetails || booking.ticket_details || details.ticket_details || {};
        var tf = booking.transferOptions || booking.transfer_options || details.transfer_options || null;
        var go = booking.guideOptions || booking.guide_options || details.guide_options || null;

        var currency = w.resolveServiceDisplayCurrency(
            booking.currency || opts.currency || details.currency,
            w.bookingCurrency
        );
        var money = function (n) {
            return w.escapeServiceHtml(w.serviceMoney(n, currency));
        };
        var isPro = parseInt(opts.isPro != null ? opts.isPro : (booking.isPro || 0), 10) === 1;

        var name = w.escapeServiceHtml(
            booking.attractionName || booking.AttractionName || details.AttractionName || 'Attraction Booking'
        );
        var ticketName = w.escapeServiceHtml(
            booking.ticketName || booking.ticket_name || details.ticketName || 'Standard Ticket'
        );
        var subtitle = w.escapeServiceHtml(opts.subtitle || (ticketName + ' • Individual Booking'));

        var adults = parseInt(booking.adultCount != null ? booking.adultCount : (details.adultCount || 0), 10) || 0;
        var children = parseInt(booking.childCount != null ? booking.childCount : (details.childCount || 0), 10) || 0;
        var seniors = parseInt(booking.seniorCount != null ? booking.seniorCount : (details.seniorCount || 0), 10) || 0;
        var infants = parseInt(
            booking.infantQty != null ? booking.infantQty : (details.infantQty || details.infants || 0),
            10
        ) || 0;
        var guests = adults + children + seniors + infants;

        var adultSell = parseFloat(
            td.adult_sell != null ? td.adult_sell : (td.adult_price != null ? td.adult_price : (booking.adultSell || details.adultSell || 0))
        ) || 0;
        var adultCost = parseFloat(
            td.adult_cost != null ? td.adult_cost : (booking.adultCost || details.adultCost || 0)
        ) || 0;
        var childSell = parseFloat(
            td.child_sell != null ? td.child_sell : (td.child_price != null ? td.child_price : (booking.childSell || details.childSell || 0))
        ) || 0;
        var childCost = parseFloat(
            td.child_cost != null ? td.child_cost : (booking.childCost || details.childCost || 0)
        ) || 0;
        var seniorSell = parseFloat(td.senior_price || 0) || 0;

        var ticketSell = parseFloat(
            booking.totalPrice != null ? booking.totalPrice : (booking.total_price != null ? booking.total_price : (details.totalPrice || details.sell || 0))
        ) || 0;
        if (ticketSell <= 0) {
            ticketSell = (adultSell * adults) + (childSell * children) + (seniorSell * seniors);
        }
        // If unit sells missing but ticket total exists, leave units at 0 — still show ticket total (sell)

        var transferSell = 0;
        var transferCost = 0;
        var vehiclesHtml = '';
        if (tf && typeof tf === 'object') {
            var vehicles = Array.isArray(tf.vehicles) ? tf.vehicles : [];
            vehicles.forEach(function (v, i) {
                if (!v || typeof v !== 'object') return;
                var vName = v.vehicle_name || v.vehicleName || v.name || ('Vehicle ' + (i + 1));
                var vType = v.type || v.transferType || '';
                var vQty = parseInt(v.qty != null ? v.qty : (v.quantity || 1), 10) || 1;
                var vAdults = parseInt(v.adults != null ? v.adults : (v.adultsQty || 0), 10) || 0;
                var vChild = parseInt(v.child != null ? v.child : (v.childQty || v.children || 0), 10) || 0;
                var vInfant = parseInt(v.infant != null ? v.infant : (v.infantQty || v.infants || 0), 10) || 0;
                var lineSell = parseFloat(v.lineSell != null ? v.lineSell : (v.line_sell != null ? v.line_sell : (v.totalPrice != null ? v.totalPrice : (v.sell || 0)))) || 0;
                var lineCost = parseFloat(v.lineCost != null ? v.lineCost : (v.line_cost != null ? v.line_cost : (v.cost || 0))) || 0;
                transferSell += lineSell;
                transferCost += lineCost;
                var paxLabel = vAdults + 'A / ' + vChild + 'C';
                if (vInfant > 0) paxLabel += ' / ' + vInfant + 'I';
                vehiclesHtml +=
                    dlRow(
                        'Vehicle ' + (i + 1),
                        w.escapeServiceHtml(vName) +
                            (vType ? ' <span class="text-muted">(' + w.escapeServiceHtml(vType) + ')</span>' : '') +
                            (vQty > 1 ? ' × ' + vQty : '') +
                            ((vAdults + vChild + vInfant) > 0 ? ' <span class="text-muted">— ' + w.escapeServiceHtml(paxLabel) + '</span>' : ''),
                        true
                    );
                if (isPro && lineCost > 0) {
                    vehiclesHtml += dlRow('Line Cost', '<span class="svc-amount">' + money(lineCost) + '</span>');
                }
                vehiclesHtml += dlRow(
                    isPro ? 'Line Sell' : 'Line Price',
                    '<span class="svc-amount">' + money(lineSell > 0 ? lineSell : lineCost) + '</span>'
                );
            });
            if (transferSell <= 0) {
                transferSell = isPro
                    ? (parseFloat(tf.totalPrice != null ? tf.totalPrice : (tf.sell != null ? tf.sell : (tf.cost || 0))) || 0)
                    : (parseFloat(tf.cost != null ? tf.cost : (tf.totalPrice || 0)) || 0);
            }
            if (transferCost <= 0) {
                transferCost = parseFloat(tf.cost || tf.lineCost || 0) || 0;
            }
        }
        var transferDisplay = isPro ? transferSell : (tf ? (parseFloat(tf.cost != null ? tf.cost : transferSell) || 0) : 0);

        var guideTotal = 0;
        if (go && typeof go === 'object') {
            guideTotal = parseFloat(
                go.total_price != null ? go.total_price : (go.sell != null ? go.sell : (go.Sell != null ? go.Sell : (go.cost || go.Cost || 0)))
            ) || 0;
        }
        var grand = ticketSell + transferDisplay + guideTotal;

        var visitDate = booking.bookingDate || booking.booking_date || details.bookingDate || 'N/A';
        try {
            if (visitDate && visitDate !== 'N/A') {
                visitDate = new Date(visitDate).toLocaleDateString('en-US', {
                    weekday: 'short', year: 'numeric', month: 'short', day: 'numeric'
                });
            }
        } catch (e) { /* keep raw */ }
        var visitTime = booking.visitTime || booking.visit_time || details.visitTime || 'Full Day';
        var selection = booking.selection || booking.Selection || details.Selection || 'Standard';
        var country = booking.country || details.country || 'N/A';

        var hasTransfer = !!(tf && (
            tf.transfer_required === true || tf.transfer_required === 'true' || tf.transfer_required === 'Yes' ||
            tf.transfer_required === 1 || (Array.isArray(tf.vehicles) && tf.vehicles.length) ||
            transferDisplay > 0 || transferCost > 0 || tf.vehicle_details || tf.vehicle_id
        ));

        var transferSection = '';
        if (hasTransfer) {
            var vehicleFallback = '';
            if (!vehiclesHtml) {
                var vName = (tf.vehicle_details && (tf.vehicle_details.vehicle_name || tf.vehicle_details.name))
                    || tf.vehicle_name || tf.vehicle_id || '—';
                vehicleFallback = dlRow('Vehicle', w.escapeServiceHtml(String(vName)), true);
            }
            transferSection = section('Transfer / Vehicle',
                dlRow('Type', w.escapeServiceHtml(tf.type || 'N/A')) +
                dlRow('Way', w.escapeServiceHtml(tf.way || 'N/A')) +
                (tf.destination_name ? dlRow('Destination', w.escapeServiceHtml(tf.destination_name), true) : '') +
                (tf.pickup_location_name ? dlRow('Pickup', w.escapeServiceHtml(tf.pickup_location_name), true) : '') +
                vehiclesHtml +
                vehicleFallback +
                (isPro && transferCost > 0 ? dlRow('Transfer Cost', '<span class="svc-amount">' + money(transferCost) + '</span>') : '') +
                dlRow(isPro ? 'Transfer Sell' : 'Transfer Price', '<span class="svc-amount">' + money(transferDisplay) + '</span>')
            );
        }

        var guideSection = '';
        if (go && (
            go.guide_required === true || go.guide_required === 'true' || go.guide_required === 'Yes' ||
            go.guide_name || go.guideName || guideTotal > 0
        )) {
            guideSection = section('Guide Details',
                dlRow('Guide', w.escapeServiceHtml(go.guide_name || go.guideName || go.name || 'Assigned Guide')) +
                ((go.package_hours || go.hours) ? dlRow('Duration', w.escapeServiceHtml(String(go.package_hours || go.hours)) + ' H') : '') +
                (go.pickup_time ? dlRow('Pickup Time', w.escapeServiceHtml(go.pickup_time)) : '') +
                ((parseFloat(go.base_price || 0) > 0) ? dlRow('Base Price', '<span class="svc-amount">' + money(go.base_price) + '</span>') : '') +
                dlRow('Guide Price', '<span class="svc-amount">' + money(guideTotal) + '</span>')
            );
        }

        var ticketRows =
            dlRow('Adult Sell', '<span class="svc-amount">' + money(adultSell) + '</span>') +
            (isPro ? dlRow('Adult Cost', '<span class="svc-amount">' + money(adultCost) + '</span>') : '') +
            dlRow('Child Sell', '<span class="svc-amount">' + money(childSell) + '</span>') +
            (isPro ? dlRow('Child Cost', '<span class="svc-amount">' + money(childCost) + '</span>') : '') +
            ((seniorSell > 0 || seniors > 0) ? dlRow('Senior', '<span class="svc-amount">' + money(seniorSell) + '</span>') : '') +
            dlRow('Ticket Total', '<span class="svc-amount">' + money(ticketSell) + '</span>');

        var summaryRows =
            dlRow('Tickets', '<span class="svc-amount">' + money(ticketSell) + '</span>');
        if (isPro && transferCost > 0) {
            summaryRows += dlRow('Transfer Cost', '<span class="svc-amount">' + money(transferCost) + '</span>');
        }
        if (transferDisplay > 0) {
            summaryRows += dlRow(isPro ? 'Transfer Sell' : 'Transfer', '<span class="svc-amount">' + money(transferDisplay) + '</span>');
        }
        if (guideTotal > 0) {
            summaryRows += dlRow('Guide', '<span class="svc-amount">' + money(guideTotal) + '</span>');
        }
        summaryRows += dlRow('Grand Total', '<span class="svc-amount" style="color:var(--svc-accent);">' + money(grand) + '</span>', true);

        var actionsHostId = opts.actionsHostId || '';
        var actionsHtml = opts.actionsHtml || '';
        if (!actionsHtml && actionsHostId) {
            actionsHtml = '<div class="d-flex gap-1 flex-wrap" id="' + w.escapeServiceHtml(actionsHostId) + '"></div>';
        }

        var special = booking.specialRequests || booking.special_requests || details.specialRequests || '';

        return '' +
            '<div class="svc-panel">' +
                '<div class="svc-panel-head">' +
                    '<div class="svc-panel-head-main">' +
                        '<div class="svc-thumb svc-thumb-fallback"><i class="ri-building-2-line"></i></div>' +
                        '<div style="min-width:0;">' +
                            '<p class="svc-title">' + name + '</p>' +
                            '<p class="svc-subtitle">' + subtitle + '</p>' +
                        '</div>' +
                    '</div>' +
                    '<div class="svc-price">' + money(grand) + '</div>' +
                '</div>' +
            '</div>' +

            section('Visit Schedule',
                dlRow('Visit Date', w.escapeServiceHtml(visitDate)) +
                dlRow('Visit Time', w.escapeServiceHtml(visitTime)) +
                dlRow('Selection', w.escapeServiceHtml(String(selection).charAt(0).toUpperCase() + String(selection).slice(1))) +
                dlRow('Country', w.escapeServiceHtml(country))
            ) +

            '<div class="svc-section">' +
                '<h6 class="svc-section-title">Guest Information</h6>' +
                '<div class="svc-guest-grid" style="grid-template-columns:repeat(' + (infants > 0 ? 4 : 3) + ',1fr);">' +
                    '<div class="svc-guest-box"><div class="num">' + adults + '</div><div class="lbl">Adults</div></div>' +
                    '<div class="svc-guest-box"><div class="num">' + children + '</div><div class="lbl">Children</div></div>' +
                    '<div class="svc-guest-box"><div class="num">' + seniors + '</div><div class="lbl">Seniors</div></div>' +
                    (infants > 0 ? '<div class="svc-guest-box"><div class="num">' + infants + '</div><div class="lbl">Infants</div></div>' : '') +
                '</div>' +
                '<div class="svc-total-bar">Total: ' + guests + ' Guest' + (guests === 1 ? '' : 's') + '</div>' +
            '</div>' +

            section('Ticket & Pricing', ticketRows) +
            transferSection +
            guideSection +
            ((transferDisplay > 0 || guideTotal > 0) ? section('Price Summary', summaryRows) : '') +
            (special ? section('Special Requests', '<div class="svc-dl-row full"><span class="svc-dl-value">' + w.escapeServiceHtml(special) + '</span></div>') : '') +

            (actionsHtml
                ? ('<div class="svc-section">' +
                    '<h6 class="svc-section-title">Booking Actions</h6>' +
                    '<div class="svc-actions">' + actionsHtml + '</div>' +
                   '</div>')
                : '');
    };

    /**
     * Professional restaurant details body (Arrival-transfer style sections).
     * opts: { tourId, restaurantOrderIndex, bookingIndex, currency, isPro, transferPrice, guidePrice, actionsHtml, qrHtml }
     */
    w.renderProfessionalRestaurantContent = function (booking, opts) {
        opts = opts || {};
        var fullBooking = (booking && (booking.restaurant_details || booking.restaurantDetails)) || booking || {};
        if (booking && booking.restaurantDetails && !booking.restaurant_details) {
            // merge flat API fields
            fullBooking = Object.assign({}, fullBooking, {
                restaurantName: fullBooking.restaurantName || booking.restaurantDetails.restaurant_name,
                mealType: fullBooking.mealType || booking.restaurantDetails.meal_type,
                mealSpecificType: fullBooking.mealSpecificType || booking.restaurantDetails.meal_specific_type,
                adultCount: fullBooking.adultCount != null ? fullBooking.adultCount : booking.restaurantDetails.adult_count,
                childCount: fullBooking.childCount != null ? fullBooking.childCount : booking.restaurantDetails.child_count,
                bookingDate: fullBooking.bookingDate || booking.restaurantDetails.booking_date,
                visitTime: fullBooking.visitTime || booking.restaurantDetails.visit_time
            });
        }

        var currency = w.resolveServiceDisplayCurrency(
            (booking && booking.currency) || opts.currency || (fullBooking && fullBooking.currency),
            w.bookingCurrency
        );
        var money = function (n) {
            return w.escapeServiceHtml(w.serviceMoney(n, currency));
        };

        var name = w.escapeServiceHtml(fullBooking.restaurantName || booking.restaurant_name || 'Restaurant Booking');
        var mealType = w.escapeServiceHtml(fullBooking.mealType || booking.meal_type || 'Meal');
        var mealSpecific = w.escapeServiceHtml(fullBooking.mealSpecificType || booking.meal_specific_type || 'Standard');
        var adults = parseInt(fullBooking.adultCount != null ? fullBooking.adultCount : (booking.adult_count || 0), 10) || 0;
        var children = parseInt(fullBooking.childCount != null ? fullBooking.childCount : (booking.child_count || 0), 10) || 0;
        var party = adults + children;

        var diningDate = 'Date TBD';
        if (fullBooking.bookingDate || booking.booking_date) {
            try {
                diningDate = new Date(fullBooking.bookingDate || booking.booking_date).toLocaleDateString('en-US', {
                    weekday: 'short', month: 'short', day: 'numeric', year: 'numeric'
                });
            } catch (e) {
                diningDate = String(fullBooking.bookingDate || booking.booking_date);
            }
        }
        var diningTime = w.escapeServiceHtml(fullBooking.visitTime || booking.visit_time || 'TBC');

        var mealPrice = parseFloat(fullBooking.mealPrice || booking.meal_price || fullBooking.totalPrice || booking.total_price || booking.totalPrice || 0) || 0;
        var transferPrice = parseFloat(opts.transferPrice != null ? opts.transferPrice : 0) || 0;
        var guidePrice = parseFloat(opts.guidePrice != null ? opts.guidePrice : 0) || 0;
        var isPro = parseInt(opts.isPro || 0, 10) === 1;
        var grand = mealPrice + transferPrice + (isPro ? guidePrice : 0);

        var tf = booking.transferOptions || fullBooking.transfer_options || null;
        var transferRequired = tf && (
            tf.transfer_required === true || tf.transfer_required === 'true' ||
            tf.transfer_required === 'Yes' || tf.transfer_required === 1
        );
        var transferHtml = '';
        if (transferRequired) {
            var vehicle = '—';
            if (tf.vehicle_details && tf.vehicle_details.vehicle_name) {
                vehicle = w.escapeServiceHtml(tf.vehicle_details.vehicle_name);
                if (tf.vehicle_details.seating_capacity) {
                    vehicle += ' <span class="text-muted">(' + w.escapeServiceHtml(tf.vehicle_details.seating_capacity) + ' seats)</span>';
                }
            } else if (tf.vehicle_id) {
                vehicle = w.escapeServiceHtml(tf.vehicle_id);
            }
            transferHtml = section('Transfer Details',
                dlRow('Type', w.escapeServiceHtml(tf.type || 'N/A')) +
                dlRow('Cost', transferPrice > 0 ? ('<span class="svc-amount">' + money(transferPrice) + '</span>') : '—') +
                (tf.pickup_location_name ? dlRow('Pickup', w.escapeServiceHtml(tf.pickup_location_name), true) : '') +
                dlRow('Vehicle', vehicle, true)
            );
        }

        var guideHtml = '';
        var g = fullBooking.guide_options || fullBooking.guideInfo || null;
        if (g && typeof g === 'object') {
            var guideName = w.escapeServiceHtml(g.guideName || g.guide_name || g.name || 'N/A');
            var gCost = parseFloat(g.cost ?? g.Cost ?? g.sell ?? g.Sell ?? g.total_price ?? 0) || 0;
            guideHtml = section('Guide Details',
                dlRow('Guide', guideName) +
                dlRow('Service type', w.escapeServiceHtml(g.serviceType || g.service_type || 'N/A')) +
                dlRow('Language', w.escapeServiceHtml(g.language || g.languages || 'N/A')) +
                dlRow('Hours', w.escapeServiceHtml((g.hours || g.service_hours || 'N/A') + '')) +
                (gCost > 0 ? dlRow('Guide cost', '<span class="svc-amount">' + money(gCost) + '</span>') : '')
            );
        }

        var pricingRows =
            dlRow('Meal price', '<span class="svc-amount">' + money(mealPrice) + '</span>') +
            dlRow('Vehicle price', '<span class="svc-amount">' + money(transferPrice) + '</span>');
        if (isPro && guidePrice > 0) {
            pricingRows += dlRow('Guide price', '<span class="svc-amount">' + money(guidePrice) + '</span>');
        }
        pricingRows += dlRow('Grand total', '<span class="svc-amount">' + money(grand) + '</span>', true);

        var actionsHtml = opts.actionsHtml || '';
        var qrHtml = opts.qrHtml || '';

        return '' +
            '<div class="svc-panel">' +
                '<div class="svc-panel-head">' +
                    '<div class="svc-panel-head-main">' +
                        '<div class="svc-thumb svc-thumb-fallback"><i class="ri-restaurant-2-line"></i></div>' +
                        '<div style="min-width:0;">' +
                            '<h6 class="svc-title">' + name + '</h6>' +
                            '<p class="svc-subtitle">' + mealType + ' · ' + mealSpecific + '</p>' +
                        '</div>' +
                    '</div>' +
                    '<div class="svc-price">' + money(grand) + '</div>' +
                '</div>' +
            '</div>' +

            '<div class="svc-section">' +
                '<h6 class="svc-section-title">Reservation Details</h6>' +
                '<div class="svc-dl">' +
                    dlRow('Dining date', w.escapeServiceHtml(diningDate)) +
                    dlRow('Dining time', diningTime) +
                '</div>' +
                '<div class="svc-guest-grid">' +
                    '<div class="svc-guest-box"><div class="num">' + adults + '</div><div class="lbl">Adults</div></div>' +
                    '<div class="svc-guest-box"><div class="num">' + children + '</div><div class="lbl">Children</div></div>' +
                '</div>' +
                '<div class="svc-total-bar">Total: ' + party + ' Guest' + (party === 1 ? '' : 's') + '</div>' +
            '</div>' +

            transferHtml +
            guideHtml +
            section('Pricing Overview', pricingRows) +

            (actionsHtml
                ? ('<div class="svc-section"><h6 class="svc-section-title">Booking Status</h6><div class="svc-actions">' + actionsHtml + '</div></div>')
                : '') +
            qrHtml;
    };

    /**
     * Professional Arrival / Departure / Local Transport body.
     * opts: { tourId, orderIndex, bookingIndex, transferLabel, currency, actionsHostId }
     */
    w.renderProfessionalTransportContent = function (booking, opts) {
        opts = opts || {};
        booking = booking || {};
        var esc = w.escapeServiceHtml;
        var currency = w.resolveServiceDisplayCurrency(
            booking.currency || opts.currency,
            w.bookingCurrency
        );
        var transferLabel = opts.transferLabel || 'Local';
        var orderIndex = opts.orderIndex != null ? opts.orderIndex : 0;
        var vehicleName = booking.vehicles_name || booking.vehicle_name || 'Vehicle Transfer';
        var typeLabel = booking.type || 'Standard';
        var totalPrice = parseFloat(booking.totalPrice != null ? booking.totalPrice : (booking.total_price || 0));
        if (isNaN(totalPrice)) totalPrice = 0;
        var adults = parseInt(booking.adults || 0, 10) || 0;
        var children = parseInt(booking.children || 0, 10) || 0;
        var guests = adults + children;
        var pickup = booking.pickupPoint || booking.entrypickup || booking.exitpickup || booking.pickupLocation || 'N/A';
        var dropoff = booking.dropoffPoint || booking.entrydropoff || booking.exitdropoff || booking.dropoffLocation || 'N/A';
        var city = booking.city || 'N/A';
        var country = booking.country || 'N/A';
        var dateLabel = booking.bookingDate || booking.booking_date || 'N/A';
        var timeLabel = booking.entrytime || booking.entry_time || booking.time || 'TBC';
        var thumb = booking.image
            ? '<img src="' + esc(booking.image) + '" alt="" class="svc-thumb" onerror="this.style.display=\'none\'">'
            : '<div class="svc-thumb svc-thumb-fallback"><i class="ri-car-line"></i></div>';
        var actionsHostId = opts.actionsHostId || '';
        var isApproved = booking.is_approve == 1 || booking.is_approve === '1' || booking.is_approve === true;
        var actionsHtml = '';
        if (isApproved) {
            actionsHtml = '<span class="svc-status-pill is-approved"><i class="ri-check-line"></i> Approved' +
                (booking.reference_id ? (' · Ref: ' + esc(booking.reference_id)) : '') +
                (booking.display_due_date ? (' · Due: ' + esc(booking.display_due_date)) : '') +
                '</span>';
        } else if (actionsHostId) {
            actionsHtml = '<div class="d-flex gap-1 flex-wrap" id="' + esc(actionsHostId) + '"></div>';
        }

        return '' +
            '<div class="svc-panel">' +
                '<div class="svc-panel-head">' +
                    '<div class="svc-panel-head-main">' + thumb +
                        '<div>' +
                            '<p class="svc-title">' + esc(vehicleName) + '</p>' +
                            '<p class="svc-subtitle">' + esc(transferLabel) + ' ' + (parseInt(orderIndex, 10) + 1) + ' • ' + esc(typeLabel) + '</p>' +
                        '</div>' +
                    '</div>' +
                    '<div class="svc-price">' + esc(currency) + ' ' + totalPrice.toFixed(2) + '</div>' +
                '</div>' +
                section('Service Schedule',
                    dlRow('Date', esc(dateLabel)) +
                    dlRow('Time', esc(timeLabel)) +
                    dlRow('Type', esc(typeLabel)) +
                    dlRow('Transfer', esc(transferLabel))
                ) +
                '<div class="svc-section">' +
                    '<h6 class="svc-section-title">Group Information</h6>' +
                    '<div class="svc-guest-grid">' +
                        '<div class="svc-guest-box"><div class="num">' + adults + '</div><div class="lbl">Adults</div></div>' +
                        '<div class="svc-guest-box"><div class="num">' + children + '</div><div class="lbl">Children</div></div>' +
                    '</div>' +
                    '<div class="svc-total-bar">Total: ' + guests + ' Guest' + (guests === 1 ? '' : 's') + '</div>' +
                '</div>' +
                section('Route Information',
                    dlRow('Pickup', '<i class="ri-map-pin-line me-1" style="color:var(--svc-accent);"></i>' + esc(pickup)) +
                    dlRow('Dropoff', '<i class="ri-map-pin-2-line me-1" style="color:var(--svc-danger);"></i>' + esc(dropoff))
                ) +
                section('Vehicle &amp; Location',
                    dlRow('Vehicle', esc(vehicleName)) +
                    dlRow('Service', esc(typeLabel)) +
                    dlRow('City', esc(city)) +
                    dlRow('Country', esc(country)) +
                    dlRow('Total Price', '<span class="svc-amount" style="color:var(--svc-accent);">' + esc(currency) + ' ' + totalPrice.toFixed(2) + '</span>', true)
                ) +
                (booking.specialRequests
                    ? section('Special Requests', dlRow('', esc(booking.specialRequests), true))
                    : '') +
                '<div class="svc-section">' +
                    '<h6 class="svc-section-title">Booking Status</h6>' +
                    '<div class="svc-actions">' + actionsHtml + '</div>' +
                '</div>' +
            '</div>';
    };

    /**
     * Professional Bootstrap modal chrome for any service type.
     * config: { modalId, title, iconClass, bodyId, footerLeftHtml, footerRightHtml, sizeClass }
     */
    w.buildProfessionalServiceModalShell = function (config) {
        config = config || {};
        var modalId = config.modalId;
        var title = w.escapeServiceHtml(config.title || 'Details');
        var icon = config.iconClass || 'ri-file-list-3-line';
        var bodyId = config.bodyId || ('svcContent_' + modalId);
        var size = config.sizeClass || 'modal-lg';
        var onClose = config.onClose || ("closeProfessionalServiceModal('" + modalId + "')");
        var footerLeft = config.footerLeftHtml || '';
        var footerRight = config.footerRightHtml || (
            '<button type="button" class="btn btn-outline-secondary btn-sm svc-footer-btn" onclick="' + onClose + '">' +
                '<i class="ri-close-line me-1"></i>Close</button>'
        );

        return '' +
            '<div class="modal fade svc-modal" id="' + modalId + '" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">' +
                '<div class="modal-dialog modal-dialog-centered ' + size + ' modal-dialog-scrollable">' +
                    '<div class="modal-content">' +
                        '<div class="modal-header">' +
                            '<h5 class="modal-title"><i class="' + icon + ' me-2"></i>' + title + '</h5>' +
                            '<button type="button" class="btn-close btn-close-white" onclick="' + onClose + '" aria-label="Close"></button>' +
                        '</div>' +
                        '<div class="modal-body">' +
                            '<div id="' + bodyId + '">' +
                                '<div class="text-center py-4">' +
                                    '<div class="spinner-border text-secondary" role="status"><span class="visually-hidden">Loading...</span></div>' +
                                    '<p class="text-muted mt-2 mb-0" style="font-size:0.85rem;">Loading details…</p>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="modal-footer justify-content-between">' +
                            '<div>' + footerLeft + '</div>' +
                            '<div class="d-flex gap-2">' + footerRight + '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
    };

    w.closeProfessionalServiceModal = function (modalId) {
        try {
            var el = document.getElementById(modalId);
            if (!el) return;
            var instance = bootstrap.Modal.getInstance(el);
            if (instance) {
                instance.hide();
            }
            setTimeout(function () {
                if (el && el.parentNode) {
                    el.parentNode.removeChild(el);
                }
            }, 250);
        } catch (e) {
            console.error(e);
        }
    };

    /** Shared professional action button markup used by hotel/attraction/etc. */
    w.buildProfessionalServiceActionButtons = function (opts) {
        opts = opts || {};
        var html = '';
        if (opts.canEdit && opts.onEdit) {
            html += '<button type="button" class="btn btn-sm svc-btn svc-btn-edit" onclick="' + opts.onEdit + '">' +
                '<i class="ri-pencil-line me-1"></i>Edit</button>';
        }
        if (opts.canApprove && opts.onApprove) {
            html += '<button type="button" class="btn btn-sm svc-btn svc-btn-approve" onclick="' + opts.onApprove + '">' +
                '<i class="ri-check-line me-1"></i>Approve</button>';
        }
        if (opts.canReject && opts.onReject) {
            html += '<button type="button" class="btn btn-sm svc-btn svc-btn-reject" onclick="' + opts.onReject + '">' +
                '<i class="ri-close-line me-1"></i>Reject</button>';
        }
        return html;
    };
})(window);
</script>
