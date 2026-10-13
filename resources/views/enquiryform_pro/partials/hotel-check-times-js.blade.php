<script>
(function (w) {
    const DEFAULT_CHECK_IN_TIME = '11:00';
    const DEFAULT_CHECK_OUT_TIME = '10:00';

    w.enquiryProNormalizeHotelTime = function (timeVal, fallback) {
        const fb = fallback || DEFAULT_CHECK_IN_TIME;
        if (timeVal === null || timeVal === undefined || timeVal === '') return fb;
        const s = String(timeVal).trim();
        const match = s.match(/(\d{1,2}):(\d{2})/);
        if (!match) return fb;
        return `${match[1].padStart(2, '0')}:${match[2]}`;
    };

    w.enquiryProTimeToMinutes = function (timeVal) {
        const normalized = w.enquiryProNormalizeHotelTime(timeVal, '00:00');
        const parts = normalized.split(':');
        return (parseInt(parts[0], 10) || 0) * 60 + (parseInt(parts[1], 10) || 0);
    };

    w.enquiryProExtractDateAndTime = function (dateTimeVal) {
        if (dateTimeVal === null || dateTimeVal === undefined || dateTimeVal === '') {
            return { date: '', time: null };
        }
        const s = String(dateTimeVal).trim().replace(' ', 'T');
        const datePart = s.split('T')[0];
        let time = null;
        if (s.includes('T')) {
            const match = s.split('T')[1].match(/(\d{1,2}):(\d{2})/);
            if (match) time = `${match[1].padStart(2, '0')}:${match[2]}`;
        }
        return { date: datePart, time: time };
    };

    w.enquiryProGetHotelCheckTimes = function (hotelData) {
        const data = hotelData || {};
        return {
            checkIn: w.enquiryProNormalizeHotelTime(data.check_in_time, DEFAULT_CHECK_IN_TIME),
            checkOut: w.enquiryProNormalizeHotelTime(data.check_out_time, DEFAULT_CHECK_OUT_TIME)
        };
    };

    /**
     * Late checkout: selected checkout time is after hotel table check_out_time.
     * Example: hotel CO 12:00, user selects 12:01 → true (bill +1 night).
     */
    w.enquiryProIsLateCheckout = function (checkOutVal, hotelData) {
        const extracted = w.enquiryProExtractDateAndTime(checkOutVal);
        if (!extracted.time) return false;
        const hotelCheckout = w.enquiryProGetHotelCheckTimes(hotelData).checkOut;
        return w.enquiryProTimeToMinutes(extracted.time) > w.enquiryProTimeToMinutes(hotelCheckout);
    };

    /**
     * Nights = calendar days (checkout date − check-in date)
     * + 1 when selected checkout time is after hotel standard check_out_time.
     */
    w.enquiryProCalculateHotelNights = function (checkInVal, checkOutVal, hotelData) {
        const ci = w.enquiryProExtractDateAndTime(checkInVal);
        const co = w.enquiryProExtractDateAndTime(checkOutVal);
        if (!ci.date || !co.date) return 0;

        const inDate = new Date(ci.date + 'T12:00:00');
        const outDate = new Date(co.date + 'T12:00:00');
        if (isNaN(inDate.getTime()) || isNaN(outDate.getTime())) return 0;

        let nights = Math.round((outDate - inDate) / (1000 * 60 * 60 * 24));
        if (nights < 0) return 0;

        if (w.enquiryProIsLateCheckout(checkOutVal, hotelData)) {
            nights += 1;
        }

        return Math.max(0, nights);
    };

    /** Billable night dates (one date per billed night), including checkout date on late CO. */
    w.enquiryProGetBillableStayDateStrings = function (checkInVal, checkOutVal, hotelData) {
        const nights = w.enquiryProCalculateHotelNights(checkInVal, checkOutVal, hotelData);
        const ci = w.enquiryProExtractDateAndTime(checkInVal);
        if (!ci.date || nights <= 0) return [];

        const dates = [];
        const cursor = new Date(ci.date + 'T12:00:00');
        if (isNaN(cursor.getTime())) return [];

        for (let i = 0; i < nights; i++) {
            const y = cursor.getFullYear();
            const m = String(cursor.getMonth() + 1).padStart(2, '0');
            const d = String(cursor.getDate()).padStart(2, '0');
            dates.push(`${y}-${m}-${d}`);
            cursor.setDate(cursor.getDate() + 1);
        }
        return dates;
    };

    w.enquiryProApplyDateWithHotelTime = function (datePart, timeHHmm) {
        if (!datePart) return '';
        const d = String(datePart).split('T')[0];
        return `${d}T${timeHHmm}`;
    };

    w.enquiryProGetSelectedHotelDataFromDropdown = function () {
        const hotelSelect = document.getElementById('hotelSelect');
        if (!hotelSelect || !hotelSelect.value) return null;
        const opt = hotelSelect.options[hotelSelect.selectedIndex];
        const dataStr = opt && opt.getAttribute('data-hotel-data');
        if (!dataStr) return null;
        try {
            return JSON.parse(dataStr);
        } catch (e) {
            return null;
        }
    };

    w.enquiryProAppendHotelTimeToDateValue = function (dateValue, kind, hotelData) {
        if (!dateValue) return dateValue;
        if (String(dateValue).includes('T')) return dateValue;
        const times = w.enquiryProGetHotelCheckTimes(hotelData);
        const time = kind === 'checkOut' ? times.checkOut : times.checkIn;
        return w.enquiryProApplyDateWithHotelTime(dateValue, time);
    };

    w.enquiryProApplyHotelCheckTimesToInputs = function (hotelData, options) {
        if (!hotelData) return;
        const opts = options || {};
        const preserveDates = opts.preserveDates !== false;
        const { checkIn, checkOut } = w.enquiryProGetHotelCheckTimes(hotelData);
        const checkInInput = document.getElementById('checkInDate');
        const checkOutInput = document.getElementById('checkOutDate');
        const tourStart = typeof getHeaderStartInput === 'function' ? getHeaderStartInput() : null;
        const tourEnd = typeof getHeaderEndInput === 'function' ? getHeaderEndInput() : null;
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const todayStr = today.toISOString().split('T')[0];

        if (checkInInput) {
            let datePart = preserveDates && checkInInput.value
                ? checkInInput.value.split('T')[0]
                : (tourStart && tourStart.value ? tourStart.value : todayStr);
            checkInInput.value = w.enquiryProApplyDateWithHotelTime(datePart, checkIn);
        }

        if (checkOutInput) {
            let datePart = preserveDates && checkOutInput.value
                ? checkOutInput.value.split('T')[0]
                : (tourEnd && tourEnd.value ? tourEnd.value : '');
            if (!datePart && checkInInput && checkInInput.value) {
                const ci = new Date(checkInInput.value.split('T')[0] + 'T12:00:00');
                ci.setDate(ci.getDate() + 1);
                datePart = ci.toISOString().split('T')[0];
            }
            if (!datePart) {
                const tomorrow = new Date(today);
                tomorrow.setDate(tomorrow.getDate() + 1);
                datePart = tomorrow.toISOString().split('T')[0];
            }
            checkOutInput.value = w.enquiryProApplyDateWithHotelTime(datePart, checkOut);
        }

        if (typeof calculateAccommodationNights === 'function') calculateAccommodationNights();
        if (typeof updateCheckOutMinDate === 'function') updateCheckOutMinDate();
    };
})(window);
</script>
