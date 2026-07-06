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

    w.enquiryProGetHotelCheckTimes = function (hotelData) {
        const data = hotelData || {};
        return {
            checkIn: w.enquiryProNormalizeHotelTime(data.check_in_time, DEFAULT_CHECK_IN_TIME),
            checkOut: w.enquiryProNormalizeHotelTime(data.check_out_time, DEFAULT_CHECK_OUT_TIME)
        };
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
