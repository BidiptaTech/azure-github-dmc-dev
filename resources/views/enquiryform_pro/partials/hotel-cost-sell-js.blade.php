{{-- Shared cost vs sell lodging helpers + price-details modal (Pro create/edit) --}}
<div class="modal fade" id="enquiryProHotelPriceDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content ep-price-details-modal">
            <div class="modal-header ep-price-details-header py-2">
                <h6 class="modal-title mb-0 fw-bold" style="font-size: 14px; color: #1e293b;">
                    <i class="ri-hotel-line me-1" style="color:#2563eb;"></i>Hotel Pricing Details
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="enquiryProHotelPriceDetailsBody"></div>
        </div>
    </div>
</div>
<style>
    .ep-cal-price-pair { display: flex; flex-direction: column; align-items: center; line-height: 1.05; margin-top: 1px; }
    .ep-cal-c { font-size: 7px; color: #6c757d; font-weight: 600; }
    .ep-cal-s { font-size: 7px; color: #198754; font-weight: 700; }
    .enquiry-pro-season-date-legend .ep-cal-day.ep-stay { min-height: 48px; }
    .ep-price-details-btn {
        font-size: 10px !important;
        padding: 1px 6px !important;
        line-height: 1.2 !important;
        display: inline-block;
        width: auto !important;
        max-width: max-content;
        white-space: nowrap;
        vertical-align: middle;
    }
    button.btn.btn-outline-primary.ep-price-details-btn {
        font-size: 10px !important;
        padding: 1px 6px !important;
        line-height: 1.2 !important;
    }
    .ep-price-details-modal { border: 0; border-radius: 10px; overflow: hidden; box-shadow: 0 16px 40px rgba(15,23,42,.18); }
    .ep-price-details-header {
        background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%);
        border-bottom: 1px solid #cbd5e1;
    }
    .ep-pd-wrap {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        padding: 14px 16px 16px;
        font-size: 12px;
    }
    .ep-pd-title { font-size: 12px; color: #475569; margin-bottom: 10px; }
    .ep-pd-section-label {
        font-size: 10px; color: #64748b; font-weight: 700;
        text-transform: uppercase; letter-spacing: .03em; margin: 8px 0 6px;
    }
    .ep-pd-night {
        background: #fff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 8px 10px;
        margin-bottom: 8px;
    }
    .ep-pd-night-top { display: flex; justify-content: space-between; gap: 8px; align-items: flex-start; }
    .ep-pd-night-meta { color: #475569; font-weight: 600; font-size: 12px; }
    .ep-pd-badge {
        display: inline-block; font-size: 9px; font-weight: 700; padding: 1px 6px;
        border-radius: 999px; margin-left: 4px; vertical-align: middle;
    }
    .ep-pd-badge-season { background: #ffedd5; color: #c2410c; }
    .ep-pd-badge-fair { background: #f3e8ff; color: #7e22ce; }
    .ep-pd-badge-blackout { background: #fee2e2; color: #b91c1c; }
    .ep-pd-badge-room { background: #f1f5f9; color: #64748b; }
    .ep-pd-cut { color: #64748b; font-size: 11px; margin-top: 4px; line-height: 1.35; }
    .ep-pd-night-totals { text-align: right; white-space: nowrap; }
    .ep-pd-c { color: #64748b; font-size: 11px; font-weight: 600; }
    .ep-pd-s { color: #198754; font-size: 12px; font-weight: 700; }
    .ep-pd-summary {
        background: #fff;
        border: 1px solid #93c5fd;
        border-radius: 8px;
        padding: 10px 12px;
        margin-top: 4px;
    }
    .ep-pd-summary-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 3px 0; font-size: 12px;
    }
    .ep-pd-summary-row strong { color: #1e40af; }
    .ep-pd-total-row {
        border-top: 1px solid #93c5fd; margin-top: 6px; padding-top: 8px;
        display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap;
    }
    .ep-pd-total-box { min-width: 120px; }
    .ep-pd-total-label { font-size: 10px; color: #64748b; font-weight: 600; text-transform: uppercase; }
    .ep-pd-total-cost { font-size: 15px; font-weight: 700; color: #475569; }
    .ep-pd-total-sell { font-size: 15px; font-weight: 700; color: #198754; }
</style>
<script>
(function () {
    function epNum(val, fallback) {
        const n = parseFloat(val);
        if (Number.isFinite(n) && n > 0) return n;
        const f = parseFloat(fallback);
        return Number.isFinite(f) && f >= 0 ? f : 0;
    }
    function epFmt(n) {
        const v = parseFloat(n);
        return Number.isFinite(v) ? v.toFixed(2) : '0.00';
    }
    function epRoundSell(n) {
        if (typeof roundToNextZero === 'function') return roundToNextZero(n);
        const v = parseFloat(n);
        if (!Number.isFinite(v) || v <= 0) return 0;
        return Math.ceil(v / 10) * 10;
    }
    function epEscape(value) {
        if (typeof enquiryProEscapeHtml === 'function') return enquiryProEscapeHtml(value);
        return String(value ?? '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function epMealFlags(combo) {
        if (typeof enquiryProMealPlanFlagsFromCombo === 'function') {
            return enquiryProMealPlanFlagsFromCombo(combo);
        }
        const plan = String(combo?.mealPlanLabel || combo?.mealPlan || '').toLowerCase();
        const all = plan.includes('all meals');
        return {
            breakfast: all || plan.includes('breakfast'),
            lunch: all || plan.includes('lunch'),
            dinner: all || plan.includes('dinner')
        };
    }

    function epBreakfastComplimentary(combo) {
        if (typeof enquiryProIsBreakfastComplimentary === 'function') {
            return enquiryProIsBreakfastComplimentary(combo);
        }
        const room = combo?.roomData || {};
        return !!(room.breakfast_included || room.breakfastIncluded);
    }

    function epMealUnitsForNight(combo, dateStr, rates, useCost) {
        const room = combo?.roomData || {};
        const applicableRate = typeof enquiryProGetApplicableRateForDate === 'function'
            ? enquiryProGetApplicableRateForDate(dateStr, rates || [])
            : null;
        const roomBf = epNum(room.breakfast_price || room.breakfastPrice, 0);
        const roomLn = epNum(room.lunch_price || room.lunchPrice, 0);
        const roomDn = epNum(room.dinner_price || room.dinnerPrice, 0);
        const roomBfCost = epNum(room.breakfast_cost_price, roomBf);
        const roomLnCost = epNum(room.lunch_cost_price, roomLn);
        const roomDnCost = epNum(room.dinner_cost_price, roomDn);

        if (!applicableRate) {
            return useCost
                ? { breakfast: roomBfCost, lunch: roomLnCost, dinner: roomDnCost }
                : { breakfast: roomBf, lunch: roomLn, dinner: roomDn };
        }

        const eventType = applicableRate.event_type || '';
        const rateBf = epNum(applicableRate.breakfast_price, 0);
        const rateLn = epNum(applicableRate.lunch_price, 0);
        const rateDn = epNum(applicableRate.dinner_price, 0);
        // Sell: season / fair / blackout meal when set, else room meal
        const bf = rateBf > 0 ? rateBf : roomBf;
        const ln = rateLn > 0 ? rateLn : roomLn;
        const dn = rateDn > 0 ? rateDn : roomDn;

        if (!useCost) {
            return { breakfast: bf, lunch: ln, dinner: dn };
        }

        // Cost: Fair / Blackout meal cost from rate when set, else 0.
        // Season: season meal cost when set, else room meal cost.
        if (eventType === 'Fair Date' || eventType === 'Blackout Date') {
            return {
                breakfast: epNum(applicableRate.breakfast_cost_price, 0),
                lunch: epNum(applicableRate.lunch_cost_price, 0),
                dinner: epNum(applicableRate.dinner_cost_price, 0)
            };
        }
        return {
            breakfast: epNum(applicableRate.breakfast_cost_price, roomBfCost),
            lunch: epNum(applicableRate.lunch_cost_price, roomLnCost),
            dinner: epNum(applicableRate.dinner_cost_price, roomDnCost)
        };
    }

    /**
     * Pro avg / calendar / details: configured unit meal rates per night
     * (do NOT multiply by adults — season breakfast 160 stays 160, not 320).
     */
    window.enquiryProLodgingMealForNight = function (combo, dateStr, rates, useCost) {
        const flags = epMealFlags(combo);
        if (!flags.breakfast && !flags.lunch && !flags.dinner) {
            return { breakfast: 0, lunch: 0, dinner: 0, total: 0, paxEq: 1 };
        }
        const units = epMealUnitsForNight(combo, dateStr, rates, !!useCost);
        const breakfastFree = epBreakfastComplimentary(combo);
        const breakfast = (flags.breakfast && !breakfastFree) ? units.breakfast : 0;
        const lunch = flags.lunch ? units.lunch : 0;
        const dinner = flags.dinner ? units.dinner : 0;
        return { breakfast, lunch, dinner, total: breakfast + lunch + dinner, paxEq: 1 };
    };

    window.enquiryProLodgingPriceSet = function (room, useCost) {
        room = room || {};
        if (useCost) {
            return {
                weekdaySingle: epNum(room.weekday_cost_price || room.weekdayCostPrice, room.weekday_price || room.weekdayPrice),
                weekendSingle: epNum(room.weekend_cost_price || room.weekendCostPrice, room.weekend_price || room.weekendPrice || room.weekday_cost_price || room.weekday_price),
                doubleWeekday: epNum(room.double_weekday_cost_price || room.doubleWeekdayCostPrice, room.double_weekday_price || room.doubleWeekdayPrice),
                doubleWeekend: epNum(room.double_weekend_cost_price || room.doubleWeekendCostPrice, room.double_weekend_price || room.doubleWeekendPrice || room.double_weekday_cost_price || room.double_weekday_price)
            };
        }
        return {
            weekdaySingle: epNum(room.weekday_price || room.weekdayPrice, 0),
            weekendSingle: epNum(room.weekend_price || room.weekendPrice, room.weekday_price || room.weekdayPrice),
            doubleWeekday: epNum(room.double_weekday_price || room.doubleWeekdayPrice, room.weekday_price || room.weekdayPrice),
            doubleWeekend: epNum(room.double_weekend_price || room.doubleWeekendPrice, room.double_weekday_price || room.doubleWeekdayPrice || room.weekend_price)
        };
    };

    window.enquiryProLodgingPriceForNight = function (combo, dateStr, rates, useCost) {
        const room = combo?.roomData || {};
        const weekendDays = combo?.weekendDays || [];
        const set = enquiryProLodgingPriceSet(room, !!useCost);
        const maxOcc = Math.max(1, parseInt(combo?.maxOccupancy || room.max_occupancy || 99, 10) || 99);
        const occupantsWithBed = Math.min(2, maxOcc);
        const isSingleOccupancy = occupantsWithBed <= 1;
        const date = new Date(dateStr + 'T12:00:00');
        const weekend = typeof isWeekendDate === 'function' ? isWeekendDate(date, weekendDays) : false;
        const applicableRate = typeof enquiryProGetApplicableRateForDate === 'function'
            ? enquiryProGetApplicableRateForDate(dateStr, rates || [])
            : null;
        const baseSingle = weekend ? set.weekendSingle : set.weekdaySingle;
        const baseDouble = weekend ? set.doubleWeekend : set.doubleWeekday;
        let roomBase = isSingleOccupancy ? baseSingle : (baseDouble || baseSingle);
        let surcharge = 0;
        let eventType = null;

        if (applicableRate) {
            eventType = applicableRate.event_type || null;
            if (applicableRate.event_type === 'Blackout Date') {
                if (useCost) {
                    const blackoutCost = epNum(applicableRate.price_cost, 0);
                    roomBase = blackoutCost > 0 ? blackoutCost : roomBase;
                } else {
                    roomBase = epNum(applicableRate.price, roomBase);
                }
            } else if (applicableRate.event_type === 'Fair Date') {
                // Sell: room + fair.price. Cost: room cost + fair.price_cost (fallback to sell surcharge).
                if (useCost) {
                    surcharge = epNum(applicableRate.price_cost, applicableRate.price);
                } else {
                    surcharge = epNum(applicableRate.price, 0);
                }
            } else if (applicableRate.event_type === 'Season') {
                if (useCost) {
                    const seasonSingle = weekend
                        ? epNum(applicableRate.weekend_cost_price, applicableRate.weekend_price || baseSingle)
                        : epNum(applicableRate.weekday_cost_price, applicableRate.weekday_price || baseSingle);
                    const seasonDouble = weekend
                        ? epNum(applicableRate.double_weekend_cost_price, applicableRate.double_weekend_price || baseDouble)
                        : epNum(applicableRate.double_weekday_cost_price, applicableRate.double_weekday_price || baseDouble);
                    roomBase = isSingleOccupancy ? seasonSingle : (seasonDouble || seasonSingle);
                } else {
                    const seasonSingle = weekend
                        ? epNum(applicableRate.weekend_price, baseSingle)
                        : epNum(applicableRate.weekday_price, baseSingle);
                    const seasonDouble = weekend
                        ? epNum(applicableRate.double_weekend_price, applicableRate.weekend_price || baseDouble)
                        : epNum(applicableRate.double_weekday_price, applicableRate.weekday_price || baseDouble);
                    roomBase = isSingleOccupancy ? seasonSingle : (seasonDouble || seasonSingle);
                }
            }
        }

        const roomPrice = roomBase + surcharge;
        const meal = enquiryProLodgingMealForNight(combo, dateStr, rates, !!useCost);
        return {
            date: dateStr,
            isWeekend: !!weekend,
            eventType,
            eventName: applicableRate?.event || null,
            roomBase,
            surcharge,
            room: roomPrice,
            meal: meal.total,
            breakfast: meal.breakfast,
            lunch: meal.lunch,
            dinner: meal.dinner,
            price: roomPrice + meal.total
        };
    };

    function epRatesForSource(combo) {
        if (typeof enquiryProRatesForHotel === 'function') {
            return enquiryProRatesForHotel({
                hotelRates: combo.hotelRates || combo.rates,
                rates: combo.hotelRates || combo.rates
            }) || [];
        }
        return combo.hotelRates || combo.rates
            || (typeof enquiryProGetHotelRates === 'function' ? enquiryProGetHotelRates() : []);
    }

    function epDatesForSource(source, combo) {
        if (source?.checkIn && source?.checkOut && typeof enquiryProStayDateStringsForHotel === 'function') {
            return enquiryProStayDateStringsForHotel(source);
        }
        return typeof getStayDateStrings === 'function' ? getStayDateStrings() : [];
    }

    /** Same night-by-night average used by View details + calendar (listing must match). */
    window.enquiryProAvgLodgingForStay = function (combo, useCost) {
        if (!combo) return 0;
        const data = enquiryProBuildHotelPriceBreakdown(combo);
        if (!data.nights.length) {
            const room = combo.roomData || {};
            const set = enquiryProLodgingPriceSet(room, !!useCost);
            const maxOcc = Math.max(1, parseInt(combo.maxOccupancy || 99, 10) || 99);
            let val = maxOcc <= 1 ? set.weekdaySingle : (set.doubleWeekday || set.weekdaySingle);
            const meal = enquiryProLodgingMealForNight(combo, '1970-01-01', [], !!useCost);
            val += meal.total;
            return useCost ? Math.round(val * 100) / 100 : epRoundSell(val);
        }
        return useCost ? data.avgCost : data.avgSell;
    };

    window.enquiryProApplyComboCostSell = function (combo, row) {
        if (!combo || !row) return;
        const priceInput = row.querySelector('.combo-price');
        const sellInput = row.querySelector('.combo-sell');
        const priceUserEdited = priceInput?.getAttribute('data-user-edited') === 'true';
        const sellUserEdited = sellInput?.getAttribute('data-user-edited') === 'true';

        const costNight = enquiryProAvgLodgingForStay(combo, true);
        let sellNight = enquiryProAvgLodgingForStay(combo, false);
        if (!Number.isFinite(sellNight)) sellNight = 0;
        if (!Number.isFinite(costNight)) combo.price = 0;

        combo.price = priceUserEdited ? (parseFloat(priceInput.value) || costNight) : costNight;
        combo.sell = sellUserEdited ? (parseFloat(sellInput.value) || sellNight) : sellNight;

        if (priceInput && !priceUserEdited) priceInput.value = epFmt(combo.price);
        if (sellInput && !sellUserEdited) sellInput.value = epFmt(combo.sell);
    };

    window.enquiryProBuildHotelPriceBreakdown = function (source) {
        const combo = {
            roomData: source?.roomData || source?.bedData || {},
            weekendDays: source?.weekendDays || [],
            hotelRates: source?.hotelRates || source?.rates || [],
            maxOccupancy: source?.maxOccupancy,
            extraBedPrice: source?.extraBedPrice,
            mealPlan: source?.mealPlan,
            mealPlanLabel: source?.mealPlanLabel || source?.mealPlan || 'room only',
            roomType: source?.roomType,
            hotelName: source?.hotelName,
            rooms: source?.rooms || 1
        };
        const dates = epDatesForSource(source, combo);
        const rates = epRatesForSource(combo);
        const nights = dates.map(d => {
            const cost = enquiryProLodgingPriceForNight(combo, d, rates, true);
            const sell = enquiryProLodgingPriceForNight(combo, d, rates, false);
            return {
                date: d,
                day: cost.isWeekend ? 'Weekend' : 'Weekday',
                eventType: sell.eventType || null,
                eventName: sell.eventName,
                roomCostBase: cost.roomBase,
                roomCost: cost.room,
                costSurcharge: cost.surcharge || 0,
                mealCost: cost.meal,
                breakfastCost: cost.breakfast,
                lunchCost: cost.lunch,
                dinnerCost: cost.dinner,
                cost: cost.price,
                roomSellBase: sell.roomBase,
                surcharge: sell.surcharge || 0,
                roomSell: sell.room,
                mealSell: sell.meal,
                breakfast: sell.breakfast,
                lunch: sell.lunch,
                dinner: sell.dinner,
                sell: sell.price
            };
        });
        const avgCost = nights.length ? nights.reduce((s, n) => s + n.cost, 0) / nights.length : 0;
        const avgSellRaw = nights.length ? nights.reduce((s, n) => s + n.sell, 0) / nights.length : 0;
        return {
            combo,
            nights,
            avgCost: Math.round(avgCost * 100) / 100,
            avgSell: epRoundSell(avgSellRaw),
            staySellTotal: nights.reduce((s, n) => s + n.sell, 0),
            stayCostTotal: nights.reduce((s, n) => s + n.cost, 0),
            roomCostTotal: nights.reduce((s, n) => s + n.roomCost, 0),
            roomSellTotal: nights.reduce((s, n) => s + n.roomSell, 0),
            mealCostTotal: nights.reduce((s, n) => s + n.mealCost, 0),
            mealSellTotal: nights.reduce((s, n) => s + n.mealSell, 0),
            fairSellTotal: nights.reduce((s, n) => s + (n.surcharge || 0), 0),
            fairCostTotal: nights.reduce((s, n) => s + (n.costSurcharge || 0), 0)
        };
    };

    function epEventBadge(eventType, eventName) {
        if (!eventType) return `<span class="ep-pd-badge ep-pd-badge-room">Standard</span>`;
        let cls = 'ep-pd-badge-room';
        if (eventType === 'Season') cls = 'ep-pd-badge-season';
        else if (eventType === 'Fair Date') cls = 'ep-pd-badge-fair';
        else if (eventType === 'Blackout Date') cls = 'ep-pd-badge-blackout';
        const label = eventName ? `${eventType}: ${eventName}` : eventType;
        return `<span class="ep-pd-badge ${cls}">${epEscape(label)}</span>`;
    }

    function epCutParts(parts) {
        return parts.filter(Boolean).join(' + ');
    }

    window.enquiryProRenderHotelPriceDetailsHtml = function (source) {
        const data = enquiryProBuildHotelPriceBreakdown(source);
        const rooms = Math.max(1, parseInt(source?.rooms || data.combo.rooms || 1, 10) || 1);
        const title = [source?.hotelName, source?.roomType || data.combo.roomType, source?.mealPlanLabel || data.combo.mealPlanLabel]
            .filter(Boolean).join(' · ') || 'Selected room';
        const stayLabel = data.nights.length
            ? `${data.nights.length} night${data.nights.length > 1 ? 's' : ''}${rooms > 1 ? ' · ' + rooms + ' rooms' : ''}`
            : '';

        const nightCards = data.nights.map((n, i) => {
            const sellParts = [];
            if (n.roomSellBase > 0) sellParts.push(`Room ${epFmt(n.roomSellBase)}`);
            if (n.surcharge > 0) sellParts.push(`Fair ${epFmt(n.surcharge)}`);
            if (n.breakfast > 0) sellParts.push(`Breakfast ${epFmt(n.breakfast)}`);
            if (n.lunch > 0) sellParts.push(`Lunch ${epFmt(n.lunch)}`);
            if (n.dinner > 0) sellParts.push(`Dinner ${epFmt(n.dinner)}`);

            const costParts = [];
            if (n.roomCostBase > 0) costParts.push(`Room ${epFmt(n.roomCostBase)}`);
            if (n.costSurcharge > 0) costParts.push(`Fair ${epFmt(n.costSurcharge)}`);
            if (n.breakfastCost > 0) costParts.push(`Breakfast ${epFmt(n.breakfastCost)}`);
            if (n.lunchCost > 0) costParts.push(`Lunch ${epFmt(n.lunchCost)}`);
            if (n.dinnerCost > 0) costParts.push(`Dinner ${epFmt(n.dinnerCost)}`);

            const sellCut = sellParts.length
                ? epCutParts(sellParts) + ` = ${epFmt(n.sell)}`
                : '';
            const costCut = costParts.length
                ? epCutParts(costParts) + ` = ${epFmt(n.cost)}`
                : '';

            return `
                <div class="ep-pd-night">
                    <div class="ep-pd-night-top">
                        <div>
                            <div class="ep-pd-night-meta">
                                N${i + 1} · ${epEscape(n.date)} (${epEscape(n.day)})
                                ${epEventBadge(n.eventType, n.eventName)}
                            </div>
                            ${sellCut ? `<div class="ep-pd-cut"><span style="color:#15803d;font-weight:600;">Sell:</span> ${sellCut}</div>` : ''}
                            ${costCut ? `<div class="ep-pd-cut"><span style="color:#64748b;font-weight:600;">Cost:</span> ${costCut}</div>` : ''}
                        </div>
                        <div class="ep-pd-night-totals">
                            <div class="ep-pd-c">C ${epFmt(n.cost)}</div>
                            <div class="ep-pd-s">S ${epFmt(n.sell)}</div>
                        </div>
                    </div>
                </div>`;
        }).join('');

        return `
            <div class="ep-pd-wrap">
                <div class="ep-pd-title">
                    <strong style="color:#1e293b;">${epEscape(title)}</strong>
                    ${stayLabel ? `<span class="text-muted"> · ${epEscape(stayLabel)}</span>` : ''}
                </div>

                <div class="ep-pd-section-label">Per-night price cut</div>
                ${nightCards || '<div class="text-muted text-center py-3">No stay dates selected</div>'}

                <div class="ep-pd-section-label">Summary</div>
                <div class="ep-pd-summary">
                    <div class="ep-pd-summary-row">
                        <span style="color:#475569;"><strong>Room (stay):</strong></span>
                        <span>C ${epFmt(data.roomCostTotal)} · <span style="color:#198754;font-weight:600;">S ${epFmt(data.roomSellTotal)}</span></span>
                    </div>
                    ${data.fairSellTotal > 0 || data.fairCostTotal > 0 ? `
                    <div class="ep-pd-summary-row">
                        <span style="color:#b45309;"><strong>Fair charge:</strong></span>
                        <span style="color:#b45309;font-weight:600;">C ${epFmt(data.fairCostTotal)} · S ${epFmt(data.fairSellTotal)}</span>
                    </div>` : ''}
                    ${(data.mealCostTotal > 0 || data.mealSellTotal > 0) ? `
                    <div class="ep-pd-summary-row">
                        <span style="color:#475569;"><strong>Meals:</strong></span>
                        <span>C ${epFmt(data.mealCostTotal)} · <span style="color:#198754;font-weight:600;">S ${epFmt(data.mealSellTotal)}</span></span>
                    </div>` : ''}
                    <div class="ep-pd-summary-row">
                        <span style="color:#475569;"><strong>Avg / night:</strong></span>
                        <span>C ${epFmt(data.avgCost)} · <span style="color:#198754;font-weight:700;">S ${epFmt(data.avgSell)}</span></span>
                    </div>
                    <div class="ep-pd-total-row">
                        <div class="ep-pd-total-box">
                            <div class="ep-pd-total-label">Stay cost</div>
                            <div class="ep-pd-total-cost">${epFmt(data.stayCostTotal)}</div>
                        </div>
                        <div class="ep-pd-total-box" style="text-align:right;">
                            <div class="ep-pd-total-label">Stay sell</div>
                            <div class="ep-pd-total-sell">${epFmt(data.staySellTotal)}</div>
                        </div>
                    </div>
                </div>
            </div>`;
    };

    function enquiryProRaisePriceDetailsModal(modalEl) {
        if (!modalEl) return;
        if (modalEl.parentElement !== document.body) document.body.appendChild(modalEl);
        let maxZ = 1050;
        document.querySelectorAll('.modal.show').forEach(el => {
            if (el === modalEl) return;
            const z = parseInt(window.getComputedStyle(el).zIndex, 10);
            if (!isNaN(z) && z >= maxZ) maxZ = z;
        });
        modalEl.style.zIndex = String(maxZ + 20);
        requestAnimationFrame(() => {
            const backdrops = document.querySelectorAll('.modal-backdrop.show');
            const topBackdrop = backdrops[backdrops.length - 1];
            if (topBackdrop) topBackdrop.style.zIndex = String(maxZ + 10);
        });
    }

    function enquiryProShowPriceDetailsModal() {
        const el = document.getElementById('enquiryProHotelPriceDetailsModal');
        if (!el || typeof bootstrap === 'undefined') return;
        enquiryProRaisePriceDetailsModal(el);
        bootstrap.Modal.getOrCreateInstance(el).show();
    }

    window.enquiryProHotelNeedsCatalogEnrich = function (hotel) {
        const room = hotel?.roomData || {};
        const weekday = epNum(room.weekday_price || room.weekdayPrice, 0);
        const doubleWd = epNum(room.double_weekday_price || room.doubleWeekdayPrice, 0);
        const hasRoomPrices = weekday > 0 || doubleWd > 0;
        const hasRates = Array.isArray(hotel?.hotelRates) && hotel.hotelRates.length > 0;
        return !(hasRoomPrices && hasRates);
    };

    window._epHotelCatalogByDest = window._epHotelCatalogByDest || {};

    window.enquiryProFetchHotelsCatalogForDestination = async function (destination) {
        const dest = String(destination || '').trim();
        if (!dest) return [];
        if (Array.isArray(window._epHotelCatalogByDest[dest])) {
            return window._epHotelCatalogByDest[dest];
        }
        let url = '';
        if (typeof window.enquiryProGetHotelsUrl === 'string' && window.enquiryProGetHotelsUrl) {
            url = window.enquiryProGetHotelsUrl + (window.enquiryProGetHotelsUrl.includes('?') ? '&' : '?')
                + 'destination=' + encodeURIComponent(dest);
        } else {
            url = '/enquiry-form-pro/get-hotels?destination=' + encodeURIComponent(dest);
        }
        if (typeof window.appendSiblingDmcQuery === 'function') {
            url = window.appendSiblingDmcQuery(url, dest);
        }
        try {
            const resp = await fetch(url, {
                method: 'GET',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await resp.json();
            const hotels = (data && data.success && Array.isArray(data.hotels)) ? data.hotels : [];
            window._epHotelCatalogByDest[dest] = hotels;
            return hotels;
        } catch (e) {
            console.warn('enquiryProFetchHotelsCatalogForDestination failed', e);
            return [];
        }
    };

    function epMatchCatalogRoom(hotel, catalogHotel) {
        const rooms = catalogHotel?.rooms || [];
        if (!rooms.length) return null;
        const dbRoomId = String(hotel.databaseRoomId || hotel.roomData?.room_id || hotel.roomData?.id || '');
        if (dbRoomId) {
            const byId = rooms.find(r => String(r.room_id || r.id) === dbRoomId);
            if (byId) return byId;
        }
        const roomType = String(hotel.roomType || hotel.roomData?.room_type || '').trim().toLowerCase();
        if (roomType) {
            const byType = rooms.find(r => String(r.room_type || r.name || '').trim().toLowerCase() === roomType);
            if (byType) return byType;
        }
        return rooms[0];
    }

    /** Attach live room prices + season/fair/blackout rates so View details / avg cost≠sell work on edit. */
    window.enquiryProEnrichHotelCatalogPricing = async function (hotel, options) {
        const opts = options || {};
        if (!hotel) return hotel;
        const needs = enquiryProHotelNeedsCatalogEnrich(hotel);
        if (!needs && !opts.force) return hotel;

        const dest = hotel.destination || hotel.city || hotel.hotelDetails?.city || '';
        const catalog = await enquiryProFetchHotelsCatalogForDestination(dest);
        if (!catalog.length) return hotel;

        const uid = String(hotel.hotel_unique_id || hotel.hotelId || '').trim();
        const name = String(hotel.hotelName || '').trim().toLowerCase();
        const match = catalog.find(h => {
            const hUid = String(h.hotel_unique_id || h.id || '').trim();
            if (uid && (hUid === uid || String(h.id) === uid)) return true;
            if (name && String(h.name || '').trim().toLowerCase() === name) return true;
            return false;
        });
        if (!match) return hotel;

        hotel.hotelRates = Array.isArray(match.rates) ? match.rates : (hotel.hotelRates || []);
        if (typeof parseWeekendDays === 'function') {
            const wd = parseWeekendDays(match.weekend_days || match.weekend || match.weekendDays || []);
            if (wd.length) hotel.weekendDays = wd;
        }
        hotel.check_in_time = hotel.check_in_time || match.check_in_time || null;
        hotel.check_out_time = hotel.check_out_time || match.check_out_time || null;

        const catalogRoom = epMatchCatalogRoom(hotel, match);
        if (catalogRoom) {
            const priorBeds = hotel.roomData?.beds;
            const priorBed = hotel.bedData;
            hotel.roomData = Object.assign({}, hotel.roomData || {}, catalogRoom);
            if (priorBeds) hotel.roomData.beds = priorBeds;
            if (priorBed && !hotel.bedData) hotel.bedData = priorBed;
            if (!hotel.databaseRoomId) {
                hotel.databaseRoomId = catalogRoom.room_id || catalogRoom.id || hotel.databaseRoomId;
            }
        }

        if (typeof enquiryProAvgLodgingForStay === 'function') {
            const costNight = enquiryProAvgLodgingForStay(hotel, true);
            const sellNight = enquiryProAvgLodgingForStay(hotel, false);
            if (Number.isFinite(costNight) && costNight > 0) {
                hotel.cost = costNight;
                hotel.roomPrice = costNight;
            }
            if (Number.isFinite(sellNight) && sellNight > 0) {
                hotel.sell = sellNight;
            }
        }
        // Fresh catalog → recalculate meal/lodging totals on next save (same as create).
        hotel.savedSelectedMeals = null;
        hotel.savedTotalPrice = null;
        return hotel;
    };

    window.enquiryProEnrichAllAccommodationCatalog = async function () {
        const list = (typeof accommodationList !== 'undefined') ? accommodationList : [];
        if (!list.length) return;
        await Promise.all(list.map(h => enquiryProEnrichHotelCatalogPricing(h)));
        if (typeof updateAccommodationTable === 'function') updateAccommodationTable();
        if (typeof recalculateTotals === 'function') recalculateTotals();
    };

    window.enquiryProOpenHotelPriceDetails = async function (index) {
        const hotel = (typeof accommodationList !== 'undefined' ? accommodationList : [])[index];
        if (!hotel) return;
        const body = document.getElementById('enquiryProHotelPriceDetailsBody');
        if (body) {
            body.innerHTML = '<div class="p-3 text-muted" style="font-size:12px;">Loading price details…</div>';
        }
        enquiryProShowPriceDetailsModal();
        try {
            await enquiryProEnrichHotelCatalogPricing(hotel);
            if (typeof updateAccommodationTable === 'function') updateAccommodationTable();
            if (body) body.innerHTML = enquiryProRenderHotelPriceDetailsHtml(hotel);
        } catch (e) {
            console.warn('enquiryProOpenHotelPriceDetails', e);
            if (body) body.innerHTML = enquiryProRenderHotelPriceDetailsHtml(hotel);
        }
    };

    window.enquiryProOpenComboPriceDetails = function (comboId) {
        const combo = (window.currentRoomCombinations || []).find(c => String(c.id) === String(comboId));
        if (!combo) return;
        const hotelSelect = document.getElementById('hotelSelect');
        const hotelName = hotelSelect?.options?.[hotelSelect.selectedIndex]?.text || '';
        const body = document.getElementById('enquiryProHotelPriceDetailsBody');
        if (body) body.innerHTML = enquiryProRenderHotelPriceDetailsHtml({
            ...combo,
            hotelName,
            hotelRates: combo.hotelRates || (typeof enquiryProGetHotelRates === 'function' ? enquiryProGetHotelRates() : [])
        });
        enquiryProShowPriceDetailsModal();
    };

    window.enquiryProStayNightPricePairHtml = function (combo, dateStr) {
        if (!combo) return '';
        const rates = epRatesForSource(combo);
        const cost = enquiryProLodgingPriceForNight(combo, dateStr, rates, true).price;
        const sell = enquiryProLodgingPriceForNight(combo, dateStr, rates, false).price;
        return `<span class="ep-cal-price-pair" title="Cost ${epFmt(cost)} / Sell ${epFmt(sell)} (room + meals)"><span class="ep-cal-c">C ${epFmt(cost)}</span><span class="ep-cal-s">S ${epFmt(sell)}</span></span>`;
    };

    window.enquiryProCurrentCalendarCombo = function () {
        let found = null;
        const checked = document.querySelector('.room-combination-checkbox:checked');
        if (checked && window.currentRoomCombinations) {
            const id = checked.getAttribute('data-combo-id');
            found = window.currentRoomCombinations.find(c => String(c.id) === String(id)) || null;
        }
        if (!found) found = window.currentRoomCombinations?.[0] || null;
        if (found && !found.hotelRates?.length && typeof enquiryProGetHotelRates === 'function') {
            found.hotelRates = enquiryProGetHotelRates();
        }
        return found;
    };
})();
</script>
