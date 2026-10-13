{{-- Enquiry Pro ↔ STP Online Hotel / Attraction integration --}}
@php
    $enquiryProOnlineApiEnabled = \App\Helpers\CommonHelper::masterDmcOnlineApiEnabled(auth()->user());
@endphp
<script>
window.ENQUIRY_PRO_ONLINE_CONFIG = {
    enabled: @json(!empty($enquiryProOnlineApiEnabled)),
    hotelEnabled: @json(!empty($enquiryProOnlineApiEnabled)),
    attractionEnabled: @json(!empty($enquiryProOnlineApiEnabled))
};
window.onlineApiEnabled = !!window.ENQUIRY_PRO_ONLINE_CONFIG.enabled;
</script>

@if(!empty($enquiryProOnlineApiEnabled))
<script>
(function () {
    'use strict';

    if (window.__enquiryProOnlineBookingHooks) return;
    window.__enquiryProOnlineBookingHooks = true;

    function onlineEnabled() {
        return !!(window.ENQUIRY_PRO_ONLINE_CONFIG && window.ENQUIRY_PRO_ONLINE_CONFIG.enabled);
    }

    function notify(msg, type) {
        if (typeof showNotification === 'function') {
            showNotification(msg, type || 'info');
            return;
        }
        if (type === 'error' || type === 'warning') alert(msg);
    }

    function toNum(v) {
        const n = parseFloat(String(v == null ? '' : v).replace(/[^0-9.-]/g, ''));
        return Number.isFinite(n) ? n : 0;
    }

    function ymdOnly(v) {
        if (!v) return '';
        const s = String(v);
        if (s.includes('T')) return s.split('T')[0];
        if (s.length >= 10) return s.substring(0, 10);
        return s;
    }

    function nightsBetween(checkIn, checkOut) {
        const a = ymdOnly(checkIn);
        const b = ymdOnly(checkOut);
        if (!a || !b) return 1;
        const d1 = new Date(a + 'T00:00:00');
        const d2 = new Date(b + 'T00:00:00');
        if (isNaN(d1) || isNaN(d2) || d2 <= d1) return 1;
        return Math.max(1, Math.round((d2 - d1) / 86400000));
    }

    function headerDates() {
        const startEl = typeof getHeaderStartInput === 'function' ? getHeaderStartInput() : document.getElementById('tourStart');
        const endEl = typeof getHeaderEndInput === 'function' ? getHeaderEndInput() : document.getElementById('tourEnd');
        const start = (typeof getHeaderInputISO === 'function' ? getHeaderInputISO(startEl) : null)
            || startEl?.value || '';
        const end = (typeof getHeaderInputISO === 'function' ? getHeaderInputISO(endEl) : null)
            || endEl?.value || '';
        return { start: ymdOnly(start), end: ymdOnly(end) };
    }

    function firstCity() {
        const hv = typeof getHeaderValues === 'function' ? getHeaderValues() : {};
        if (Array.isArray(hv.cities) && hv.cities.length) return String(hv.cities[0]);
        const dest = document.getElementById('hotelDestination')?.value
            || document.getElementById('tourDestination')?.value
            || document.getElementById('destinationDisplay')?.value
            || '';
        if (!dest) return 'Singapore';
        return String(dest).split(',')[0].trim() || 'Singapore';
    }

    function newId(prefix) {
        if (typeof generateId === 'function') return generateId(prefix);
        return prefix + '-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 8);
    }

    function onlineHotelBadgeHtml() {
        return '<span class="badge ep-online-badge ep-online-badge--hotel ms-1" title="Live online hotel booking">ONLINE HOTEL</span>';
    }

    function onlineAttractionBadgeHtml() {
        return '<span class="badge ep-online-badge ep-online-badge--attraction ms-1" title="Live online attraction booking">ONLINE ATTRACTION</span>';
    }

    window.getOnlineHotelSearchDefaults = function () {
        const dates = headerDates();
        const city = firstCity();
        let checkOut = dates.end;
        if (!checkOut && dates.start) {
            const d = new Date(dates.start + 'T00:00:00');
            d.setDate(d.getDate() + 1);
            checkOut = d.toISOString().slice(0, 10);
        }
        return {
            city: city,
            cityLabel: city,
            checkIn: dates.start || '',
            checkOut: checkOut || ''
        };
    };

    window.getOnlineAttractionSearchDefaults = function () {
        const dates = headerDates();
        const city = firstCity();
        return {
            city: city,
            cityLabel: city,
            visitDate: dates.start || '',
            targetLabel: city + ' · Online Attraction'
        };
    };

    window.getHotelNightPlanStart = function () {
        return headerDates().start || '';
    };

    window.getHotelNightPlanNightCount = function () {
        const d = headerDates();
        if (d.start && d.end) return nightsBetween(d.start, d.end);
        return 1;
    };

    function ensureHiddenHotelCitySelect(city) {
        let el = document.getElementById('hotelCitySelect');
        if (!el) {
            el = document.createElement('select');
            el.id = 'hotelCitySelect';
            el.className = 'd-none';
            el.setAttribute('aria-hidden', 'true');
            document.body.appendChild(el);
        }
        const name = city || firstCity();
        el.innerHTML = '';
        const opt = document.createElement('option');
        opt.value = name;
        opt.textContent = name;
        opt.selected = true;
        el.appendChild(opt);
        el.value = name;
    }

    function mapOnlineHotelToAccommodation(hotelData) {
        hotelData = hotelData || {};
        const booking = hotelData.onlineHotelBooking || {};
        const sel = booking.selection || {};
        const checkIn = ymdOnly(sel.stay_check_in || booking.check_in || hotelData.checkIn || '') || headerDates().start;
        const checkOut = ymdOnly(sel.stay_check_out || booking.check_out || hotelData.checkOut || '') || headerDates().end;
        const nights = Array.isArray(hotelData.nights) && hotelData.nights.length
            ? hotelData.nights.length
            : nightsBetween(checkIn, checkOut);
        const rooms = parseInt(hotelData.numberOfRooms, 10) || 1;
        const persons = parseInt(hotelData.selectedPersons, 10) || 1;
        // Stay total (Lite parity) — never treat pricePerNight as grand total.
        let totalPrice = toNum(hotelData.price || hotelData.combinedRoomTotal || hotelData.customRoomPrice);
        const perNightHint = toNum(hotelData.pricePerNight);
        if (totalPrice <= 0 && perNightHint > 0) {
            totalPrice = Math.round(perNightHint * nights * 100) / 100;
        }
        // Option 1: online display price → Avg Cost and Sell are the same (per-night avg).
        const perNight = nights > 0 ? (totalPrice / nights) : totalPrice;
        const avg = perNightHint > 0 ? perNightHint : perNight;
        const mealPlan = hotelData.mealPlan || 'Not specified';
        const roomType = hotelData.roomType || 'Standard';
        const bedType = hotelData.bedType || '';
        const hotelName = hotelData.name || 'Online Hotel';
        const hotelId = hotelData.id || ('online-' + Date.now());
        const city = hotelData.city || firstCity();

        return {
            id: newId('hotel-online'),
            hotelId: hotelId,
            hotel_unique_id: hotelId,
            hotelName: hotelName,
            destination: city,
            country: (typeof resolveCountryForCity === 'function' ? resolveCountryForCity(city) : '') || '',
            currency: (typeof resolveCurrencyForCountry === 'function'
                ? resolveCurrencyForCountry(typeof resolveCountryForCity === 'function' ? resolveCountryForCity(city) : '')
                : '') || '',
            check_in_time: (booking.hotel && booking.hotel.check_in_time) || null,
            check_out_time: (booking.hotel && booking.hotel.check_out_time) || null,
            roomId: hotelData.roomId || (booking.room && booking.room.code) || '',
            bedId: hotelData.bedId || ('online-bed-' + Date.now()),
            databaseRoomId: hotelData.roomId || (booking.room && booking.room.code) || '',
            roomType: roomType,
            bedType: bedType,
            bedTypeRaw: bedType,
            maxOccupancy: persons,
            checkIn: checkIn ? (checkIn + 'T14:00') : '',
            checkOut: checkOut ? (checkOut + 'T12:00') : '',
            nights: nights,
            rooms: rooms,
            extraBed: 0,
            hasExtraBed: false,
            hasCwb: false,
            hasCnb: false,
            hasInfant: false,
            mealPlan: mealPlan,
            mealPlanLabel: mealPlan,
            mealPax: persons,
            extraBedAvailable: false,
            supplement: false,
            roomPrice: avg,
            cost: avg,
            sell: avg,
            avgCost: avg,
            avgSell: avg,
            extraBedPrice: 0,
            extraBedCostPrice: 0,
            cwbPrice: 0,
            cwbCostPrice: 0,
            cnbPrice: 0,
            cnbCostPrice: 0,
            infantPrice: 0,
            infantCostPrice: 0,
            childWithBed: 0,
            childWithoutBed: 0,
            roomData: null,
            bedData: null,
            weekendDays: [],
            hotelRates: [],
            focServiceDiscount: false,
            // Lite-parity online store fields
            isOnlineHotel: true,
            hotelSourceType: 'online',
            priceMode: 'online',
            onlineHotelSource: hotelData.onlineHotelSource || null,
            onlineHotelBooking: booking,
            onlineHotelRaw: hotelData.onlineHotelRaw || null,
            api_environment: hotelData.api_environment || (booking && booking.api_environment) || '',
            totalPrice: totalPrice,
            grand_total: totalPrice,
            price_payload: {
                room_total: totalPrice,
                meal_total: 0,
                grand_total: totalPrice,
                nights: nights,
                is_online: true
            },
            transferIds: [],
            hotelTransfer: null,
            skipArrivalDeparture: true,
            selectedPersons: persons,
            remarks: hotelData.remarks || '',
            nightsList: Array.isArray(hotelData.nights) ? hotelData.nights.slice() : [],
            onlineAddSnapshot: hotelData
        };
    }

    function mapOnlineAttractionToTour(payload) {
        payload = payload || {};
        const adults = Math.max(0, parseInt(payload.adults, 10) || 0);
        const children = Math.max(0, parseInt(payload.children, 10) || 0);
        const infants = Math.max(0, parseInt(payload.infants, 10) || 0);
        const adultP = toNum(payload.adultPrice);
        const childP = toNum(payload.childPrice);
        const infantP = 0;
        // Option 1: online display unit prices → cost === sell
        const visitDate = ymdOnly(payload.visitDate) || headerDates().start;
        const timeSlot = payload.timeSlot || '10:00';
        let dateTime = visitDate;
        if (visitDate) {
            const t = String(timeSlot).split('-')[0].trim();
            const hhmm = /^\d{1,2}:\d{2}/.test(t) ? t.substring(0, 5) : '10:00';
            dateTime = visitDate + 'T' + hhmm;
        }
        const city = payload.cityValue || firstCity();
        const total = toNum(payload.totalPrice) || ((adultP * adults) + (childP * children));

        return {
            id: newId('tour-online'),
            destination: city,
            attractionId: payload.attractionId || payload.skuId || '',
            attractionName: payload.attractionName || 'Online Attraction',
            ticketId: payload.ticketId || payload.ticketSkuId || payload.providerTicketId || '',
            ticketName: payload.ticketName || 'Ticket',
            dateTime: dateTime,
            visitTime: timeSlot,
            adultsQty: adults,
            adultCost: adultP,
            adultSell: adultP,
            childQty: children,
            childCost: childP,
            childSell: childP,
            infantQty: infants,
            infantCost: infantP,
            infantSell: infantP,
            transferId: null,
            transferInfo: null,
            guideId: null,
            guideInfo: null,
            guide_options: null,
            guideRequired: false,
            focServiceDiscount: false,
            supplement: false,
            // Lite-parity online store fields
            isOnlineAttraction: true,
            attractionSourceType: 'online',
            sku_id: payload.skuId || payload.attractionId || null,
            ticket_sku_id: payload.ticketSkuId || null,
            provider_ticket_id: payload.providerTicketId || null,
            lowest_ticket_price: toNum(payload.lowestTicketPrice) || null,
            highest_ticket_price: toNum(payload.highestTicketPrice) || null,
            supplier_code: payload.supplierCode || 'sg_attractions',
            api_environment: payload.apiEnvironment || null,
            onlineAttractionRaw: payload.onlineAttractionRaw || null,
            totalPrice: total,
            grand_total: total,
            remarks: payload.remarks || '',
            Selection: 'withoutTransport',
            transfer_options: null,
            onlineAddSnapshot: payload
        };
    }

    window.pushSelectedHotel = function (hotelData) {
        if (!onlineEnabled()) {
            notify('Online hotel API is not enabled for this account.', 'warning');
            return;
        }
        if (typeof accommodationList === 'undefined' || !Array.isArray(accommodationList)) {
            notify('Accommodation list is not ready. Reload the page.', 'error');
            return;
        }
        const row = mapOnlineHotelToAccommodation(hotelData);
        const editIdx = window.__enquiryProOnlineHotelEditIndex;
        if (editIdx != null && accommodationList[editIdx]) {
            row.id = accommodationList[editIdx].id;
            accommodationList[editIdx] = row;
        } else {
            accommodationList.push(row);
        }
        window.__enquiryProOnlineHotelEditIndex = null;
        window.__enquiryProOnlineHotelEditSnapshot = null;
        if (typeof window.updateAccommodationTable === 'function') window.updateAccommodationTable();
        else if (typeof updateAccommodationTable === 'function') updateAccommodationTable();
        if (typeof window.recalculateTotals === 'function') window.recalculateTotals();
        else if (typeof recalculateTotals === 'function') recalculateTotals();
        resetOnlineSourceToggles();
    };

    window.pushSelectedOnlineAttraction = function (payload) {
        if (!onlineEnabled()) {
            notify('Online attraction API is not enabled for this account.', 'warning');
            return;
        }
        if (typeof tourList === 'undefined' || !Array.isArray(tourList)) {
            notify('Attraction list is not ready. Reload the page.', 'error');
            return;
        }
        const row = mapOnlineAttractionToTour(payload);
        const editIdx = window.__enquiryProOnlineAttractionEditIndex;
        if (editIdx != null && tourList[editIdx]) {
            row.id = tourList[editIdx].id;
            tourList[editIdx] = row;
        } else {
            tourList.push(row);
        }
        window.__enquiryProOnlineAttractionEditIndex = null;
        window.__enquiryProOnlineAttractionEditSnapshot = null;
        if (typeof window.updateTourTable === 'function') window.updateTourTable();
        else if (typeof updateTourTable === 'function') updateTourTable();
        if (typeof window.recalculateTotals === 'function') window.recalculateTotals();
        else if (typeof recalculateTotals === 'function') recalculateTotals();
        window.__enquiryProOnlineAttractionActive = false;
        resetOnlineSourceToggles();
    };

    window.updateHotelDataField = function () { /* pro uses accommodationList */ };
    window.updateAttractionDataField = function () { /* pro uses tourList */ };

    function resetOnlineSourceToggles() {
        document.querySelectorAll('.ep-hotel-source-type[value="offline"], .ep-attraction-source-type[value="offline"]').forEach(function (r) {
            r.checked = true;
        });
        document.querySelectorAll('.ep-hotel-source-type[value="online"], .ep-attraction-source-type[value="online"]').forEach(function (r) {
            r.checked = false;
        });
    }

    function setOnlineHotelAddBtnLabel(isEdit) {
        const btn = document.getElementById('onlineHotelAddBtn');
        if (!btn) return;
        btn.innerHTML = isEdit
            ? '<i class="ri-save-line me-1"></i> Update Hotel'
            : '<i class="ri-add-line me-1"></i> Add Hotel';
    }

    function setOnlineAttractionAddBtnLabel(isEdit) {
        const btn = document.getElementById('onlineAttractionAddBtn');
        if (!btn) return;
        btn.innerHTML = isEdit
            ? '<i class="ri-save-line me-1"></i> Update Attraction'
            : '<i class="ri-add-line me-1"></i> Add Attraction';
    }

    function hotelSnapshotFromRow(hotel) {
        if (!hotel) return null;
        if (hotel.onlineAddSnapshot && typeof hotel.onlineAddSnapshot === 'object') {
            return hotel.onlineAddSnapshot;
        }
        const checkIn = ymdOnly(hotel.checkIn);
        const checkOut = ymdOnly(hotel.checkOut);
        return {
            id: hotel.hotelId || hotel.hotel_unique_id,
            name: hotel.hotelName,
            roomType: hotel.roomType,
            bedType: hotel.bedType,
            mealPlan: hotel.mealPlan || hotel.mealPlanLabel,
            selectedPersons: hotel.selectedPersons || hotel.mealPax || 1,
            numberOfRooms: hotel.rooms || 1,
            price: toNum(hotel.totalPrice) || (toNum(hotel.sell) * (parseInt(hotel.nights, 10) || 1)),
            pricePerNight: toNum(hotel.sell || hotel.cost),
            nights: Array.isArray(hotel.nightsList) ? hotel.nightsList : [],
            remarks: hotel.remarks || '',
            city: hotel.destination || firstCity(),
            checkIn: checkIn,
            checkOut: checkOut,
            stay_check_in: checkIn,
            stay_check_out: checkOut,
            isOnlineHotel: true,
            onlineHotelSource: hotel.onlineHotelSource,
            onlineHotelRaw: hotel.onlineHotelRaw,
            onlineHotelBooking: hotel.onlineHotelBooking,
            api_environment: hotel.api_environment || ''
        };
    }

    function attractionSnapshotFromRow(tour) {
        if (!tour) return null;
        if (tour.onlineAddSnapshot && typeof tour.onlineAddSnapshot === 'object') {
            return tour.onlineAddSnapshot;
        }
        return {
            cityValue: tour.destination || firstCity(),
            attractionId: tour.attractionId || tour.sku_id,
            skuId: tour.sku_id || tour.attractionId,
            supplierCode: tour.supplier_code || 'sg_attractions',
            apiEnvironment: tour.api_environment || '',
            attractionName: tour.attractionName,
            timeSlot: tour.visitTime || '',
            ticketId: tour.ticketId,
            ticketName: tour.ticketName,
            ticketSkuId: tour.ticket_sku_id,
            providerTicketId: tour.provider_ticket_id,
            adultPrice: toNum(tour.adultCost || tour.adultSell),
            childPrice: toNum(tour.childCost || tour.childSell),
            seniorPrice: 0,
            adults: parseInt(tour.adultsQty, 10) || 0,
            children: parseInt(tour.childQty, 10) || 0,
            infants: parseInt(tour.infantQty, 10) || 0,
            totalPrice: toNum(tour.totalPrice),
            visitDate: ymdOnly(tour.dateTime),
            remarks: tour.remarks || '',
            onlineAttractionRaw: tour.onlineAttractionRaw
        };
    }

    window.openEnquiryProOnlineHotel = function (editIndex) {
        if (!onlineEnabled()) {
            notify('Online hotel booking is not enabled for your Master DMC.', 'warning');
            return;
        }
        const isEdit = editIndex != null && editIndex !== '';
        window.__enquiryProOnlineHotelEditIndex = isEdit ? editIndex : null;
        window.__enquiryProOnlineHotelEditSnapshot = isEdit
            ? hotelSnapshotFromRow(accommodationList[editIndex])
            : null;
        ensureHiddenHotelCitySelect(
            (window.__enquiryProOnlineHotelEditSnapshot && window.__enquiryProOnlineHotelEditSnapshot.city) || firstCity()
        );
        window.__enquiryProOnlineHotelActive = true;
        setOnlineHotelAddBtnLabel(isEdit);
        if (typeof window.openOnlineHotelModal === 'function') {
            window.openOnlineHotelModal();
        } else {
            notify('Online hotel modal is not available. Reload the page.', 'error');
        }
    };

    window.openEnquiryProOnlineAttraction = function (editIndex) {
        if (!onlineEnabled()) {
            notify('Online attraction booking is not enabled for your Master DMC.', 'warning');
            return;
        }
        const isEdit = editIndex != null && editIndex !== '';
        window.__enquiryProOnlineAttractionEditIndex = isEdit ? editIndex : null;
        window.__enquiryProOnlineAttractionEditSnapshot = isEdit
            ? attractionSnapshotFromRow(tourList[editIndex])
            : null;
        window.__enquiryProOnlineAttractionActive = true;
        window.__stpLiteOnlineAttractionRoot = document.getElementById('tourTable') || document.body;
        setOnlineAttractionAddBtnLabel(isEdit);
        if (typeof window.openOnlineAttractionModal === 'function') {
            window.openOnlineAttractionModal(1, 1);
        } else {
            notify('Online attraction modal is not available. Reload the page.', 'error');
            window.__enquiryProOnlineAttractionActive = false;
            window.__stpLiteOnlineAttractionRoot = null;
        }
    };

    /**
     * Rebuild online hotel order JSON in STP Lite shape (stay total on beds[].price /
     * totalPrice / price_payload — not Pro per-night lodging_cost_snapshot).
     */
    function formatStayLabel(checkIn, checkOut) {
        if (!checkIn || !checkOut) return '';
        try {
            if (typeof moment !== 'undefined') {
                return moment(checkIn, 'YYYY-MM-DD').format('MMM D') + ' → ' +
                    moment(checkOut, 'YYYY-MM-DD').format('MMM D, YYYY');
            }
            const a = new Date(checkIn + 'T00:00:00');
            const b = new Date(checkOut + 'T00:00:00');
            if (isNaN(a) || isNaN(b)) return checkIn + ' → ' + checkOut;
            const optsShort = { month: 'short', day: 'numeric' };
            const optsLong = { month: 'short', day: 'numeric', year: 'numeric' };
            return a.toLocaleDateString('en-US', optsShort) + ' → ' + b.toLocaleDateString('en-US', optsLong);
        } catch (e) {
            return checkIn + ' → ' + checkOut;
        }
    }

    function resolveOnlineStayTotal(src, nights) {
        const snap = src.onlineAddSnapshot || {};
        const booking = src.onlineHotelBooking || {};
        const sel = booking.selection || {};
        // Prefer modal/API stay totals over listing avg cost (per-night).
        let stay = toNum(
            snap.price ||
            snap.combinedRoomTotal ||
            snap.customRoomPrice ||
            sel.price ||
            src.totalPrice ||
            src.grand_total ||
            (src.price_payload && src.price_payload.grand_total)
        );
        if (stay > 0) return stay;
        const avg = toNum(src.sell || src.cost || src.avgSell || src.avgCost || snap.pricePerNight);
        const n = Math.max(1, parseInt(nights, 10) || 1);
        return avg > 0 ? Math.round(avg * n * 100) / 100 : 0;
    }

    function mapOnlineHotelToLiteStoreRow(src, proRow) {
        src = src || {};
        proRow = proRow || {};
        const booking = src.onlineHotelBooking || {};
        const onlineHotel = booking.hotel || {};
        const sel = booking.selection || {};
        const snap = src.onlineAddSnapshot || {};

        const checkIn = ymdOnly(
            sel.stay_check_in || booking.check_in || src.checkIn ||
            (Array.isArray(proRow.bookingDate) ? proRow.bookingDate[0] : '') || ''
        );
        const checkOut = ymdOnly(
            sel.stay_check_out || booking.check_out || src.checkOut ||
            (Array.isArray(proRow.bookingDate) ? proRow.bookingDate[1] : '') || ''
        );
        const nights = Math.max(
            1,
            parseInt(src.nights, 10) ||
            (Array.isArray(src.nightsList) ? src.nightsList.length : 0) ||
            (Array.isArray(snap.nights) ? snap.nights.length : 0) ||
            nightsBetween(checkIn, checkOut)
        );
        const roomsCount = Math.max(1, parseInt(src.rooms || snap.numberOfRooms || sel.number_of_rooms, 10) || 1);
        const persons = Math.max(1, parseInt(src.selectedPersons || sel.persons || snap.selectedPersons, 10) || 1);
        const stayTotal = resolveOnlineStayTotal(src, nights);

        const hv = (typeof getHeaderValues === 'function') ? getHeaderValues() : {};
        const adults = Math.max(1, parseInt(hv.adults, 10) || persons || 1);
        const children = Math.max(0, parseInt(hv.children, 10) || 0);
        const infants = Math.max(0, parseInt(hv.infants, 10) || 0);

        const mealPlan = src.mealPlan || src.mealPlanLabel || sel.meal_plan || snap.mealPlan || 'room only';
        const roomType = src.roomType || sel.room_type || snap.roomType || '';
        const bedType = src.bedType || src.bedTypeRaw || sel.bed_type || snap.bedType || 'Standard';
        const hotelId = src.hotel_unique_id || src.hotelId || snap.id || onlineHotel.code || '';
        const hotelName = src.hotelName || snap.name || onlineHotel.name || '';
        const city = src.destination || snap.city || firstCity() || proRow.city || '';
        const country = src.country || proRow.country ||
            (typeof resolveCountryForCity === 'function' ? resolveCountryForCity(city) : '') || '';
        const currency = src.currency || proRow.currency ||
            (booking.currency) ||
            (typeof resolveCurrencyForCountry === 'function' ? resolveCurrencyForCountry(country) : '') || 'SGD';
        const dmcId = parseInt(
            (proRow.priceModeId != null ? proRow.priceModeId : null) ||
            '{{ $dmc_id ?? "" }}' ||
            (booking.markup && booking.markup.dmc_id) ||
            0,
            10
        ) || null;
        const roomId = src.roomId || src.databaseRoomId || (booking.room && booking.room.code) || snap.roomId || '';
        const bedId = src.bedId || snap.bedId || ('online-bed-' + Date.now());
        const rawImages = src.onlineHotelRaw && src.onlineHotelRaw.images;
        const image = (proRow.hotelDetails && proRow.hotelDetails.image) ||
            onlineHotel.image ||
            (Array.isArray(rawImages) && rawImages.length ? rawImages[0] : '') ||
            '';
        const checkInTime = onlineHotel.check_in_time || src.check_in_time || '15:00:00';
        const checkOutTime = onlineHotel.check_out_time || src.check_out_time || '12:00:00';

        const pricePayload = {
            room_total: stayTotal,
            meal_total: 0,
            grand_total: stayTotal,
            nights: nights,
            currency: currency,
            is_online: true,
            breakdown: []
        };

        return {
            fullName: proRow.fullName || '',
            email: proRow.email || '',
            phone: proRow.phone || '',
            countryCode: proRow.countryCode || '',
            address1: proRow.address1 || '',
            address2: proRow.address2 != null ? proRow.address2 : null,
            state: proRow.state || 'State not provided',
            zip: proRow.zip || '',
            specialRequests: proRow.specialRequests != null ? proRow.specialRequests : null,
            id: null,
            bookingType: 'enquiry',
            bookingDate: [checkIn, checkOut],
            hotelDetails: {
                hotel_id: hotelId,
                hotel_name: hotelName,
                image: image,
                location: city || onlineHotel.address || '',
                checkInTime: checkInTime,
                checkOutTime: checkOutTime,
                cancellation_charge: null,
                country: country,
                city: city
            },
            priceMode: 'online',
            priceModeId: dmcId,
            is_adhoc: false,
            adhoc_price: null,
            price_payload: pricePayload,
            helperPriceResult: Object.assign({}, pricePayload),
            room_total: stayTotal,
            meal_total: 0,
            grand_total: stayTotal,
            meal_plan: mealPlan,
            hotel_name: hotelName,
            hotel_unique_id: hotelId,
            hotel_id: hotelId,
            room_type: roomType,
            bed_label: bedType,
            number_of_rooms: roomsCount,
            stay_start: checkIn,
            stay_end: checkOut,
            stay_label: formatStayLabel(checkIn, checkOut),
            selected_adults: adults,
            selected_children: children,
            selected_children_no_bed: 0,
            selected_children_with_bed: 0,
            selected_infants: infants,
            selected_persons: persons,
            rooms: [{
                room_id: roomId,
                room_type: roomType,
                occupancy: persons <= 1 ? 'single' : 'double',
                selected_persons: persons,
                selected_adults: adults,
                selected_children: children,
                selected_children_no_bed: 0,
                selected_children_with_bed: 0,
                selected_infants: infants,
                number_of_rooms: roomsCount,
                breakfast_included: 0,
                supplement_breakfast_included: 0,
                beds: [{
                    bed_id: bedId,
                    bed_type: bedType,
                    baby_cot: 0,
                    baby_cot_price: 0,
                    baby_cot_cost: 0,
                    head_count: persons,
                    max_occupancy: persons,
                    extra_bed: 0,
                    extra_bed_price: 0,
                    extra_bed_cost: 0,
                    // Online: cost === sell (stay total)
                    price: stayTotal,
                    cost: stayTotal,
                    sell: stayTotal,
                    cost_price: stayTotal,
                    mealTypes: [mealPlan],
                    meal_plan: mealPlan
                }]
            }],
            totalPrice: stayTotal,
            price: stayTotal,
            cost: stayTotal,
            sell: stayTotal,
            cost_price: stayTotal,
            transfer_options: null,
            child_with_bed: null,
            child_without_bed: null,
            children: children,
            children_price: null,
            child_meal_factor: null,
            extra_bed: null,
            tour_id: proRow.tour_id != null ? String(proRow.tour_id) : (src.tour_id != null ? String(src.tour_id) : null),
            city: city,
            country: country,
            currency: currency,
            remarks: src.remarks || sel.remarks || snap.remarks || '',
            supplement: false,
            isOnlineHotel: true,
            hotelSourceType: 'online',
            onlineHotelSource: src.onlineHotelSource || snap.onlineHotelSource || (booking.supplier_code) || null,
            onlineHotelBooking: booking,
            api_environment: src.api_environment || booking.api_environment || snap.api_environment || ''
        };
    }

    // --- Patch listing / arrival / edit / transform once DOM + host functions exist ---
    function patchHostFunctions() {
        if (typeof window.updateAccommodationTable === 'function' && !window.updateAccommodationTable.__epOnlinePatched) {
            const orig = window.updateAccommodationTable;
            window.updateAccommodationTable = function () {
                orig.apply(this, arguments);
                try {
                    const tbody = document.getElementById('accommodationTableBody');
                    if (!tbody || typeof accommodationList === 'undefined') return;
                    Array.from(tbody.querySelectorAll('tr')).forEach(function (tr, index) {
                        const hotel = accommodationList[index];
                        if (!hotel || !(hotel.isOnlineHotel || hotel.hotelSourceType === 'online')) return;
                        const nameCell = tr.children[1];
                        if (!nameCell || nameCell.querySelector('.ep-online-badge')) return;
                        const anchor = nameCell.querySelector('a') || nameCell;
                        const badge = document.createElement('div');
                        badge.className = 'mt-1';
                        badge.innerHTML = onlineHotelBadgeHtml()
                            + ' <small class="text-muted" style="font-size:0.65rem;">Live API · cost = sell</small>';
                        anchor.appendChild(badge);
                        // Online: sell locked to cost (same value)
                        const sellInput = tr.querySelectorAll('.accommodation-price-field')[1];
                        if (sellInput) {
                            sellInput.readOnly = true;
                            sellInput.style.backgroundColor = '#f5f5f5';
                            sellInput.title = 'Online hotel: cost and sell are the same';
                        }
                    });
                } catch (e) { console.warn('ep online hotel badge', e); }
            };
            window.updateAccommodationTable.__epOnlinePatched = true;
        }

        if (typeof window.updateTourTable === 'function' && !window.updateTourTable.__epOnlinePatched) {
            const origTour = window.updateTourTable;
            window.updateTourTable = function () {
                origTour.apply(this, arguments);
                try {
                    const tbody = document.getElementById('tourTableBody');
                    if (!tbody || typeof tourList === 'undefined') return;
                    Array.from(tbody.querySelectorAll('tr')).forEach(function (tr, index) {
                        const tour = tourList[index];
                        if (!tour || !(tour.isOnlineAttraction || tour.attractionSourceType === 'online')) return;
                        const nameCell = tr.children[2];
                        if (!nameCell || nameCell.querySelector('.ep-online-badge')) return;
                        const badge = document.createElement('div');
                        badge.className = 'mt-1';
                        badge.innerHTML = onlineAttractionBadgeHtml()
                            + ' <small class="text-muted" style="font-size:0.65rem;">No transfer · cost = sell</small>';
                        nameCell.appendChild(badge);
                        // Lock sell fields to match cost for online
                        [5, 8, 11].forEach(function (colIdx) {
                            const input = tr.children[colIdx] && tr.children[colIdx].querySelector('input');
                            if (input) {
                                input.readOnly = true;
                                input.disabled = true;
                                input.style.backgroundColor = '#f5f5f5';
                                input.title = 'Online attraction: cost and sell are the same';
                            }
                        });
                    });
                } catch (e) { console.warn('ep online attraction badge', e); }
            };
            window.updateTourTable.__epOnlinePatched = true;
        }

        if (typeof window.recalculateEntryExitPorts === 'function' && !window.recalculateEntryExitPorts.__epOnlinePatched) {
            const origPorts = window.recalculateEntryExitPorts;
            window.recalculateEntryExitPorts = function () {
                // Temporarily hide online hotels from arrival/departure calc (lite parity)
                if (typeof accommodationList !== 'undefined' && Array.isArray(accommodationList)) {
                    const backup = accommodationList.slice();
                    const offlineOnly = accommodationList.filter(function (h) {
                        return !(h && (h.isOnlineHotel || h.hotelSourceType === 'online' || h.skipArrivalDeparture));
                    });
                    accommodationList.length = 0;
                    Array.prototype.push.apply(accommodationList, offlineOnly);
                    try {
                        origPorts.apply(this, arguments);
                    } finally {
                        accommodationList.length = 0;
                        Array.prototype.push.apply(accommodationList, backup);
                    }
                    return;
                }
                origPorts.apply(this, arguments);
            };
            window.recalculateEntryExitPorts.__epOnlinePatched = true;
        }

        if (typeof window.editAccommodation === 'function' && !window.editAccommodation.__epOnlinePatched) {
            const origEditH = window.editAccommodation;
            window.editAccommodation = function (index) {
                const hotel = (typeof accommodationList !== 'undefined') ? accommodationList[index] : null;
                if (hotel && (hotel.isOnlineHotel || hotel.hotelSourceType === 'online')) {
                    window.openEnquiryProOnlineHotel(index);
                    return;
                }
                return origEditH.apply(this, arguments);
            };
            window.editAccommodation.__epOnlinePatched = true;
        }

        if (typeof window.editTour === 'function' && !window.editTour.__epOnlinePatched) {
            const origEditT = window.editTour;
            window.editTour = function (index) {
                const tour = (typeof tourList !== 'undefined') ? tourList[index] : null;
                if (tour && (tour.isOnlineAttraction || tour.attractionSourceType === 'online')) {
                    window.openEnquiryProOnlineAttraction(index);
                    return;
                }
                return origEditT.apply(this, arguments);
            };
            window.editTour.__epOnlinePatched = true;
        }

        if (typeof window.transformAccommodationData === 'function' && !window.transformAccommodationData.__epOnlinePatched) {
            const origTxH = window.transformAccommodationData;
            window.transformAccommodationData = function () {
                const rows = origTxH.apply(this, arguments) || [];
                return rows.map(function (row, idx) {
                    const src = (typeof accommodationList !== 'undefined') ? accommodationList[idx] : null;
                    if (!src || !(src.isOnlineHotel || src.hotelSourceType === 'online')) return row;
                    return mapOnlineHotelToLiteStoreRow(src, row);
                });
            };
            window.transformAccommodationData.__epOnlinePatched = true;
        }

        if (typeof window.transformTourData === 'function' && !window.transformTourData.__epOnlinePatched) {
            const origTxT = window.transformTourData;
            window.transformTourData = function () {
                const rows = origTxT.apply(this, arguments) || [];
                return rows.map(function (row, idx) {
                    const src = (typeof tourList !== 'undefined') ? tourList[idx] : null;
                    if (!src || !(src.isOnlineAttraction || src.attractionSourceType === 'online')) return row;
                    row.isOnlineAttraction = true;
                    row.attractionSourceType = 'online';
                    row.sku_id = src.sku_id || null;
                    row.ticket_sku_id = src.ticket_sku_id || null;
                    row.provider_ticket_id = src.provider_ticket_id || null;
                    row.lowest_ticket_price = src.lowest_ticket_price || null;
                    row.highest_ticket_price = src.highest_ticket_price || null;
                    row.supplier_code = src.supplier_code || 'sg_attractions';
                    row.api_environment = src.api_environment || null;
                    row.onlineAttractionRaw = src.onlineAttractionRaw || null;
                    row.onlineAddSnapshot = src.onlineAddSnapshot || null;
                    row.Selection = 'withoutTransport';
                    row.transfer_options = null;
                    row.transport = null;
                    row.mode = 'online';
                    // Online: cost === sell (unit + line totals for orders.cost_price)
                    row.adultCost = toNum(src.adultCost || src.adultSell);
                    row.adultSell = toNum(src.adultSell || src.adultCost);
                    row.childCost = toNum(src.childCost || src.childSell);
                    row.childSell = toNum(src.childSell || src.childCost);
                    row.infantCost = toNum(src.infantCost || src.infantSell);
                    row.infantSell = toNum(src.infantSell || src.infantCost);
                    const adults = Math.max(0, parseInt(row.adultsQty || row.adultCount || src.adultsQty, 10) || 0);
                    const children = Math.max(0, parseInt(row.childQty || row.childCount || src.childQty, 10) || 0);
                    const infants = Math.max(0, parseInt(row.infantQty || row.infants || src.infantQty, 10) || 0);
                    const lineTotal = toNum(src.totalPrice || src.grand_total) ||
                        (row.adultSell * adults + row.childSell * children + row.infantSell * infants);
                    row.totalPrice = lineTotal;
                    row.grand_total = lineTotal;
                    row.cost = lineTotal;
                    row.sell = lineTotal;
                    row.cost_price = lineTotal;
                    if (row.ticket_details) {
                        row.ticket_details.adult_cost = row.adultCost;
                        row.ticket_details.adult_sell = row.adultSell;
                        row.ticket_details.adult_price = row.adultSell;
                        row.ticket_details.child_cost = row.childCost;
                        row.ticket_details.child_sell = row.childSell;
                        row.ticket_details.child_price = row.childSell;
                        row.ticket_details.infant_cost = row.infantCost;
                        row.ticket_details.infant_sell = row.infantSell;
                        row.ticket_details.infant_price = row.infantSell;
                    }
                    return row;
                });
            };
            window.transformTourData.__epOnlinePatched = true;
        }
    }

    function bindSourceToggles() {
        document.querySelectorAll('.ep-hotel-source-type').forEach(function (radio) {
            radio.addEventListener('change', function () {
                if (!this.checked) return;
                if (this.value === 'online') {
                    window.openEnquiryProOnlineHotel();
                }
            });
        });
        document.querySelectorAll('.ep-attraction-source-type').forEach(function (radio) {
            radio.addEventListener('change', function () {
                if (!this.checked) return;
                if (this.value === 'online') {
                    window.openEnquiryProOnlineAttraction();
                }
            });
        });

        document.getElementById('onlineHotelModal')?.addEventListener('hidden.bs.modal', function () {
            window.__enquiryProOnlineHotelActive = false;
            window.__enquiryProOnlineHotelEditIndex = null;
            window.__enquiryProOnlineHotelEditSnapshot = null;
            setOnlineHotelAddBtnLabel(false);
            resetOnlineSourceToggles();
        });
        document.getElementById('onlineAttractionModal')?.addEventListener('hidden.bs.modal', function () {
            window.__enquiryProOnlineAttractionActive = false;
            window.__enquiryProOnlineAttractionEditIndex = null;
            window.__enquiryProOnlineAttractionEditSnapshot = null;
            window.__stpLiteOnlineAttractionRoot = null;
            setOnlineAttractionAddBtnLabel(false);
            resetOnlineSourceToggles();
        });
    }

    function boot() {
        patchHostFunctions();
        bindSourceToggles();
        // Re-patch after a tick in case host functions redefine later on edit load
        setTimeout(patchHostFunctions, 800);
        setTimeout(patchHostFunctions, 2500);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
</script>

<style>
.ep-online-source-toggle {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: #f3e8ff;
    border: 1px solid #e9d5ff;
    border-radius: 6px;
    padding: 2px 8px;
    margin-right: 6px;
    font-size: 10px;
    font-weight: 600;
    color: #6b21a8;
}
.ep-online-source-toggle .form-check {
    margin: 0;
    min-height: 0;
    padding-left: 1.2rem;
}
.ep-online-source-toggle .form-check-input {
    width: 12px;
    height: 12px;
    margin-top: 0.15rem;
}
.ep-online-source-toggle .form-check-label {
    font-size: 10px;
    font-weight: 600;
    cursor: pointer;
}
.ep-online-badge {
    display: inline-block;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.03em;
    padding: 2px 6px;
    border-radius: 4px;
    vertical-align: middle;
}
.ep-online-badge--hotel {
    background: linear-gradient(135deg, #a855f7, #7c3aed);
    color: #fff;
}
.ep-online-badge--attraction {
    background: linear-gradient(135deg, #06b6d4, #0891b2);
    color: #fff;
}
</style>
@endif
