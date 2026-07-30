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
        return !!(obj && (obj.enabled || obj.price > 0 || obj.children > 0));
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

        return '' +
            '<div class="svc-panel">' +
                '<div class="svc-panel-head">' +
                    '<div class="svc-panel-head-main">' +
                        thumb +
                        '<div style="min-width:0;">' +
                            '<h6 class="svc-title">' + name + '</h6>' +
                            '<p class="svc-subtitle"><i class="ri-map-pin-line me-1"></i>' + location +
                            (hotelBooking.country ? ' · ' + w.escapeServiceHtml(hotelBooking.country) : '') +
                            '</p>' +
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
