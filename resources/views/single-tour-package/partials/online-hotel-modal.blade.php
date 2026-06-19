{{-- Online hotel booking modal (Tiniva live API) --}}
<div class="modal fade" id="onlineHotelModal" tabindex="-1" aria-labelledby="onlineHotelModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;">
                <h5 class="modal-title" id="onlineHotelModalLabel">
                    <i class="ri-global-line me-1"></i> Online Hotel Booking
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="background: #f8f9fa;">
                <div class="alert alert-info py-2 mb-3" style="font-size: 0.85rem;">
                    <i class="ri-information-line me-1"></i>
                    Hotels are fetched live from the third-party API. Select dates, city and pax, then click <strong>Fetch Hotels</strong>.
                </div>

                {{-- Search criteria (maps to fetchHotels API) --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-building-line me-1"></i>City</label>
                                <select class="form-select form-select-sm" id="onlineHotelCity"></select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-calendar-line me-1"></i>Check-in</label>
                                <input type="date" class="form-control form-control-sm" id="onlineHotelCheckIn">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-calendar-line me-1"></i>Check-out</label>
                                <input type="date" class="form-control form-control-sm" id="onlineHotelCheckOut">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-group-line me-1"></i>Pax Info</label>
                                <input type="text" class="form-control form-control-sm" id="onlineHotelPaxInfo" readonly style="background: #fff;">
                                <small class="text-muted" style="font-size: 0.7rem;">Format: adults|children</small>
                            </div>
                        </div>
                        <div class="mt-3 d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-primary" id="onlineHotelFetchBtn">
                                <span class="spinner-border spinner-border-sm d-none me-1" id="onlineHotelFetchSpinner"></span>
                                <i class="ri-search-line me-1"></i> Fetch Hotels
                            </button>
                            <small class="text-muted" id="onlineHotelFetchStatus" style="font-size: 0.8rem;"></small>
                        </div>
                    </div>
                </div>

                {{-- Hotel selection (mirrors offline fields) --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row g-2 mb-2">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-hotel-line me-1"></i>Select Hotel</label>
                                <select class="form-select form-select-sm" id="onlineHotelSelect" disabled>
                                    <option value="">Fetch hotels first</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-door-open-line me-1"></i>Room Type</label>
                                <select class="form-select form-select-sm" id="onlineRoomTypeSelect" disabled>
                                    <option value="">Room Type</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-hotel-bed-line me-1"></i>Bed Type</label>
                                <select class="form-select form-select-sm" id="onlineBedTypeSelect" disabled>
                                    <option value="">Bed Type</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-group-line me-1"></i>Number of Persons</label>
                                <input type="number" class="form-control form-control-sm" id="onlineSelectedPersons" value="1" min="1" max="99">
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-restaurant-line me-1"></i>Meal Plan</label>
                                <select class="form-select form-select-sm" id="onlineMealPlanSelect">
                                    <option value="">Select Meal Plan</option>
                                    <option value="room only">Room Only</option>
                                    <option value="room with breakfast">Room with Breakfast</option>
                                    <option value="room with breakfast + dinner">Room with Breakfast + Dinner</option>
                                    <option value="room with breakfast + lunch">Room with Breakfast + Lunch</option>
                                    <option value="room with all meals (breakfast + lunch + dinner)">Room with All Meals</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-hotel-bed-2-line me-1"></i>Number of Rooms</label>
                                <input type="number" class="form-control form-control-sm" id="onlineNumberOfRooms" value="1" min="1" max="999">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size: 0.8rem;"><i class="ri-money-dollar-circle-line me-1"></i>Price</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">SGD</span>
                                    <input type="text" class="form-control" id="onlineRoomPriceDisplay" value="0.00" readonly style="background:#f8f9fa; text-align:right;">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold mb-2" style="font-size: 0.85rem;"><i class="ri-chat-quote-line me-1"></i>Remarks</label>
                            <textarea class="form-control form-control-sm" id="onlineHotelRemarks" rows="2" placeholder="Optional notes for this online hotel booking..."></textarea>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-semibold mb-2" style="font-size: 0.85rem;"><i class="ri-calendar-check-line me-1"></i>Select Hotel Nights</label>
                            <div id="onlineNightSelection" class="d-flex flex-wrap gap-2 mb-2"></div>
                            <div id="onlineNightSelectionSummary">
                                <small class="text-muted">No nights selected.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="onlineHotelAddBtn" disabled>
                    <i class="ri-add-line me-1"></i> Add Hotel
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    function bindHotelSourceToggle() {
        $(document).off('change.hotelSource', 'input[name="hotelSourceType"]');
        $(document).on('change.hotelSource', 'input[name="hotelSourceType"]', function () {
            const isOnline = this.value === 'online';
            const offlinePanel = document.getElementById('offlineHotelPanel');
            if (offlinePanel) {
                offlinePanel.style.display = isOnline ? 'none' : '';
            }
            if (isOnline) {
                window.openOnlineHotelModal();
            } else {
                const modalEl = document.getElementById('onlineHotelModal');
                if (modalEl && window.bootstrap) {
                    bootstrap.Modal.getInstance(modalEl)?.hide();
                }
            }
        });
    }

    const fetchUrl = @json(route('fetch-online-hotels'));
    const csrfToken = @json(csrf_token());

    let onlineHotelsCache = [];
    let onlineSelectedNights = [];
    let onlineCurrentRooms = [];

    function getAdultsChildren() {
        const adults = parseInt(document.getElementById('adults')?.value, 10) || 1;
        const children = parseInt(document.getElementById('children')?.value, 10) || 0;
        return { adults, children };
    }

    function buildPaxInfo() {
        const { adults, children } = getAdultsChildren();
        return adults + '|' + children;
    }

    function syncOnlineSearchDefaults() {
        const citySelect = document.getElementById('hotelCitySelect');
        const onlineCity = document.getElementById('onlineHotelCity');
        if (citySelect && onlineCity) {
            onlineCity.innerHTML = '';
            Array.from(citySelect.options).forEach(function (opt) {
                if (!opt.value) return;
                const o = document.createElement('option');
                o.value = opt.value;
                o.textContent = opt.textContent;
                onlineCity.appendChild(o);
            });
            if (citySelect.value) {
                onlineCity.value = citySelect.value;
            }
        }

        const planStart = (typeof getHotelNightPlanStart === 'function') ? getHotelNightPlanStart() : (window.tourStartDate || '');
        const planNights = (typeof getHotelNightPlanNightCount === 'function') ? getHotelNightPlanNightCount() : (window.tourNights || 0);
        const checkInEl = document.getElementById('onlineHotelCheckIn');
        const checkOutEl = document.getElementById('onlineHotelCheckOut');
        if (checkInEl && planStart) {
            checkInEl.value = moment(planStart).format('YYYY-MM-DD');
        }
        if (checkOutEl && planStart && planNights > 0) {
            checkOutEl.value = moment(planStart).add(planNights, 'days').format('YYYY-MM-DD');
        }

        const paxEl = document.getElementById('onlineHotelPaxInfo');
        if (paxEl) paxEl.value = buildPaxInfo();

        const personsEl = document.getElementById('onlineSelectedPersons');
        if (personsEl) {
            const { adults, children } = getAdultsChildren();
            personsEl.value = Math.max(1, adults + children);
        }

        generateOnlineNightButtons();
    }

    function generateOnlineNightButtons() {
        const wrap = document.getElementById('onlineNightSelection');
        if (!wrap) return;
        wrap.innerHTML = '';
        onlineSelectedNights = [];

        const planNights = (typeof getHotelNightPlanNightCount === 'function') ? getHotelNightPlanNightCount() : (window.tourNights || 0);
        const planStart = (typeof getHotelNightPlanStart === 'function') ? getHotelNightPlanStart() : (window.tourStartDate || '');
        if (!planNights || !planStart) {
            document.getElementById('onlineNightSelectionSummary').innerHTML = '<small class="text-muted">Set travel dates first.</small>';
            return;
        }

        for (let i = 1; i <= planNights; i++) {
            const startDate = moment(planStart).add(i - 1, 'days');
            const endDate = moment(planStart).add(i, 'days');
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-primary btn-sm online-night-btn';
            btn.dataset.night = String(i);
            btn.innerHTML = '<strong>Night ' + i + '</strong><br><small>' + startDate.format('MMM DD') + ' - ' + endDate.format('MMM DD') + '</small>';
            btn.addEventListener('click', function () {
                toggleOnlineNight(parseInt(this.dataset.night, 10));
            });
            wrap.appendChild(btn);
        }
        updateOnlineNightSummary();
    }

    function toggleOnlineNight(nightNum) {
        const idx = onlineSelectedNights.indexOf(nightNum);
        if (idx >= 0) {
            onlineSelectedNights.splice(idx, 1);
        } else {
            onlineSelectedNights.push(nightNum);
            onlineSelectedNights.sort((a, b) => a - b);
        }
        document.querySelectorAll('.online-night-btn').forEach(function (btn) {
            const n = parseInt(btn.dataset.night, 10);
            btn.classList.toggle('active', onlineSelectedNights.includes(n));
            btn.classList.toggle('btn-primary', onlineSelectedNights.includes(n));
            btn.classList.toggle('btn-outline-primary', !onlineSelectedNights.includes(n));
        });
        updateOnlineNightSummary();
        validateOnlineAddBtn();
    }

    function updateOnlineNightSummary() {
        const el = document.getElementById('onlineNightSelectionSummary');
        if (!el) return;
        if (!onlineSelectedNights.length) {
            el.innerHTML = '<small class="text-muted">No nights selected.</small>';
            return;
        }
        el.innerHTML = '<small class="text-success">' + onlineSelectedNights.length + ' night(s) selected: ' + onlineSelectedNights.join(', ') + '</small>';
    }

    function hotelLabel(h) {
        return h.hotelName || h.name || h.hotel_name || h.title || ('Hotel #' + (h.hotelId || h.id || ''));
    }

    function hotelId(h) {
        return String(h.hotelId || h.hotel_id || h.id || h.hotel_unique_id || '');
    }

    function toNumber(value) {
        const n = parseFloat(value);
        return Number.isFinite(n) ? n : 0;
    }

    function roomMealLabel(room) {
        const mealPlanName = String(room?.mealPlanName || '').toUpperCase();
        if (mealPlanName === 'EP') return 'room only';
        if (mealPlanName === 'CP') return 'room with breakfast';
        if (mealPlanName === 'MAP') return 'room with breakfast + dinner';
        if (mealPlanName === 'AP') return 'room with all meals (breakfast + lunch + dinner)';
        if (room?.breakFast === true) return 'room with breakfast';
        return '';
    }

    function roomDisplayPrice(room) {
        const p = room?.currencyConvertedPrice || room?.price || {};
        return toNumber(
            p.actual ??
            p.discounted ??
            p.total ??
            room?.actual ??
            room?.totalPrice ??
            room?.amount ??
            0
        );
    }

    function extractHotelsFromResponse(data) {
        if (!data || typeof data !== 'object') return [];
        if (Array.isArray(data.hotels) && data.hotels.length) return data.hotels;
        if (Array.isArray(data?.provider?.HotelDetails)) return data.provider.HotelDetails;
        if (Array.isArray(data?.provider?.hotels)) return data.provider.hotels;
        if (Array.isArray(data?.provider?.data)) return data.provider.data;
        if (Array.isArray(data?.provider?.results)) return data.provider.results;
        if (Array.isArray(data?.HotelDetails)) return data.HotelDetails;
        return [];
    }

    function populateOnlineHotels(hotels) {
        onlineHotelsCache = Array.isArray(hotels) ? hotels : [];
        const sel = document.getElementById('onlineHotelSelect');
        sel.innerHTML = '<option value="">Select hotel</option>';
        onlineHotelsCache.forEach(function (h, idx) {
            const opt = document.createElement('option');
            opt.value = hotelId(h) || String(idx);
            opt.textContent = hotelLabel(h);
            opt.dataset.index = String(idx);
            sel.appendChild(opt);
        });
        sel.disabled = onlineHotelsCache.length === 0;
        document.getElementById('onlineRoomTypeSelect').innerHTML = '<option value="">Room Type</option>';
        document.getElementById('onlineBedTypeSelect').innerHTML = '<option value="">Bed Type</option>';
        document.getElementById('onlineRoomTypeSelect').disabled = true;
        document.getElementById('onlineBedTypeSelect').disabled = true;
        validateOnlineAddBtn();
    }

    function populateOnlineBedTypes(room) {
        const bedSel = document.getElementById('onlineBedTypeSelect');
        if (!bedSel) return;

        bedSel.innerHTML = '<option value="">Bed Type</option>';
        if (!room) {
            bedSel.disabled = true;
            return;
        }

        const bedTypes = [];
        if (room.bedType) bedTypes.push(String(room.bedType));
        if (room.extraBedType && !bedTypes.includes(String(room.extraBedType))) {
            bedTypes.push(String(room.extraBedType));
        }

        if (!bedTypes.length) {
            bedSel.disabled = true;
            return;
        }

        bedTypes.forEach(function (name) {
            const opt = document.createElement('option');
            opt.value = name;
            opt.textContent = name;
            bedSel.appendChild(opt);
        });
        bedSel.disabled = false;
        bedSel.value = bedTypes[0];
    }

    function applySelectedRoomDetails(room) {
        if (!room) return;

        const price = roomDisplayPrice(room);
        if (price > 0) {
            document.getElementById('onlineRoomPriceDisplay').value = price.toFixed(2);
        }

        populateOnlineBedTypes(room);

        const meal = roomMealLabel(room);
        const mealSel = document.getElementById('onlineMealPlanSelect');
        if (mealSel && meal) {
            mealSel.value = meal;
        }
    }

    function populateOnlineRooms(hotel) {
        const roomSel = document.getElementById('onlineRoomTypeSelect');
        const bedSel = document.getElementById('onlineBedTypeSelect');
        roomSel.innerHTML = '<option value="">Room Type</option>';
        bedSel.innerHTML = '<option value="">Bed Type</option>';
        bedSel.disabled = true;

        const rooms = hotel.rooms || hotel.roomTypes || hotel.room_types || [];
        onlineCurrentRooms = Array.isArray(rooms) ? rooms : [];
        if (Array.isArray(rooms) && rooms.length) {
            rooms.forEach(function (room, idx) {
                const opt = document.createElement('option');
                const name = room.roomName || room.roomType || room.room_type || room.name || room.type || ('Room ' + (idx + 1));
                opt.value = name;
                opt.textContent = name;
                opt.dataset.index = String(idx);
                const price = roomDisplayPrice(room);
                if (price > 0) {
                    opt.dataset.price = String(price);
                }
                roomSel.appendChild(opt);
            });
            roomSel.disabled = false;
            roomSel.selectedIndex = 1;
            applySelectedRoomDetails(onlineCurrentRooms[0]);
        } else {
            roomSel.disabled = true;

            const beds = hotel.beds || hotel.bedTypes || [];
            if (Array.isArray(beds) && beds.length) {
                beds.forEach(function (bed) {
                    const opt = document.createElement('option');
                    const name = bed.bedType || bed.bed_type || bed.name || '';
                    if (!name) return;
                    opt.value = name;
                    opt.textContent = name;
                    bedSel.appendChild(opt);
                });
                bedSel.disabled = bedSel.options.length <= 1;
            }

            const hotelPrice = toNumber(hotel.price || hotel.totalPrice || hotel.lowestPrice || hotel.amount || 0);
            document.getElementById('onlineRoomPriceDisplay').value = hotelPrice.toFixed(2);
        }
    }

    function validateOnlineAddBtn() {
        const btn = document.getElementById('onlineHotelAddBtn');
        const ok = document.getElementById('onlineHotelSelect')?.value &&
            onlineSelectedNights.length > 0;
        if (btn) btn.disabled = !ok;
    }

    window.openOnlineHotelModal = function () {
        syncOnlineSearchDefaults();
        const modalEl = document.getElementById('onlineHotelModal');
        if (!modalEl) {
            console.error('onlineHotelModal element not found');
            return;
        }
        if (!window.bootstrap || !window.bootstrap.Modal) {
            console.error('Bootstrap Modal is not available');
            return;
        }
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };

    bindHotelSourceToggle();
    $(function () { bindHotelSourceToggle(); });

    document.getElementById('onlineHotelFetchBtn')?.addEventListener('click', function () {
        const city = document.getElementById('onlineHotelCity')?.value;
        const checkIn = document.getElementById('onlineHotelCheckIn')?.value;
        const checkOut = document.getElementById('onlineHotelCheckOut')?.value;
        const paxInfo = document.getElementById('onlineHotelPaxInfo')?.value || buildPaxInfo();
        const statusEl = document.getElementById('onlineHotelFetchStatus');
        const spinner = document.getElementById('onlineHotelFetchSpinner');

        if (!city || !checkIn || !checkOut) {
            if (typeof showNotification === 'function') {
                showNotification('City, check-in and check-out are required.', 'warning');
            }
            return;
        }

        if (spinner) spinner.classList.remove('d-none');
        if (statusEl) statusEl.textContent = 'Fetching hotels...';

        fetch(fetchUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ checkIn: checkIn, checkOut: checkOut, city: city, paxInfo: paxInfo })
        })
        .then(r => r.json())
        .then(data => {
            if (data && data.success) {
                const hotels = extractHotelsFromResponse(data);
                populateOnlineHotels(hotels);
                if (statusEl) statusEl.textContent = hotels.length + ' hotel(s) found.';
                if (typeof showNotification === 'function') {
                    showNotification('Online hotels loaded successfully.', 'success');
                }
            } else {
                populateOnlineHotels([]);
                if (statusEl) statusEl.textContent = data?.message || 'No hotels found.';
                if (typeof showNotification === 'function') {
                    showNotification(data?.message || 'Failed to fetch online hotels.', 'error');
                }
            }
        })
        .catch(function (err) {
            populateOnlineHotels([]);
            if (statusEl) statusEl.textContent = 'Request failed.';
            console.error(err);
            if (typeof showNotification === 'function') {
                showNotification('Error fetching online hotels.', 'error');
            }
        })
        .finally(function () {
            if (spinner) spinner.classList.add('d-none');
        });
    });

    document.getElementById('onlineHotelSelect')?.addEventListener('change', function () {
        const idx = this.selectedIndex >= 0 ? this.options[this.selectedIndex].dataset.index : null;
        const hotel = idx !== null && idx !== undefined ? onlineHotelsCache[parseInt(idx, 10)] : null;
        if (hotel) populateOnlineRooms(hotel);
        validateOnlineAddBtn();
    });

    document.getElementById('onlineRoomTypeSelect')?.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        const roomIdx = opt?.dataset?.index !== undefined ? parseInt(opt.dataset.index, 10) : -1;
        const room = roomIdx >= 0 ? onlineCurrentRooms[roomIdx] : null;
        if (room) {
            applySelectedRoomDetails(room);
        } else if (opt && opt.dataset.price) {
            document.getElementById('onlineRoomPriceDisplay').value = parseFloat(opt.dataset.price).toFixed(2);
        }
    });

    document.getElementById('onlineHotelAddBtn')?.addEventListener('click', function () {
        if (typeof selectedHotels === 'undefined' || typeof displaySelectedHotels !== 'function') {
            alert('Hotel list is not ready. Please reload the page.');
            return;
        }
        if (!tourStartDate) {
            alert('Please add travel dates first before adding hotels.');
            return;
        }

        const hotelSel = document.getElementById('onlineHotelSelect');
        const idx = hotelSel.selectedIndex >= 0 ? parseInt(hotelSel.options[hotelSel.selectedIndex].dataset.index, 10) : -1;
        const hotelRaw = idx >= 0 ? onlineHotelsCache[idx] : null;
        if (!hotelRaw) return;

        const nightNumbers = onlineSelectedNights.slice().sort((a, b) => a - b);
        const planStart = (typeof getHotelNightPlanStart === 'function') ? getHotelNightPlanStart() : tourStartDate;
        const startNight = Math.min(...nightNumbers);
        const endNight = Math.max(...nightNumbers);
        const checkInDate = moment(planStart).add(startNight - 1, 'days');
        const checkOutDate = moment(planStart).add(endNight, 'days');
        const roomType = document.getElementById('onlineRoomTypeSelect')?.value || 'Standard';
        const bedType = document.getElementById('onlineBedTypeSelect')?.value || '';
        const mealPlan = document.getElementById('onlineMealPlanSelect')?.value || 'Not specified';
        const selectedPersons = parseInt(document.getElementById('onlineSelectedPersons')?.value, 10) || 1;
        const numberOfRooms = parseInt(document.getElementById('onlineNumberOfRooms')?.value, 10) || 1;
        const price = parseFloat(document.getElementById('onlineRoomPriceDisplay')?.value) || 0;
        const cityName = document.getElementById('onlineHotelCity')?.selectedOptions?.[0]?.textContent || '';
        const remarks = document.getElementById('onlineHotelRemarks')?.value || '';

        const hotelData = {
            id: hotelId(hotelRaw) || ('online-' + Date.now()),
            name: hotelLabel(hotelRaw),
            roomType: roomType,
            bedType: bedType || null,
            selectedPersons: selectedPersons,
            price: price,
            combinedRoomTotal: price,
            roomPriceManuallyEdited: true,
            customRoomPrice: price,
            mealPlan: mealPlan,
            mealPrices: { breakfast_price: 0, lunch_price: 0, dinner_price: 0 },
            numberOfRooms: numberOfRooms,
            nights: nightNumbers,
            checkInDate: checkInDate.format('MMM DD'),
            checkOutDate: checkOutDate.format('MMM DD'),
            totalNights: nightNumbers.length,
            remarks: remarks,
            isOnlineHotel: true,
            onlineHotelSource: 'tiniva',
            onlineHotelRaw: hotelRaw,
            city: cityName,
            pricePerNight: nightNumbers.length ? price / nightNumbers.length : price
        };

        selectedHotels.push(hotelData);
        if (typeof lastSelectedHotelId !== 'undefined') {
            lastSelectedHotelId = hotelData.id;
        }
        displaySelectedHotels();
        if (typeof window.updateHotelDataField === 'function') {
            window.updateHotelDataField();
        }
        if (typeof showNotification === 'function') {
            showNotification('Online hotel "' + hotelData.name + '" added successfully.', 'success');
        }

        const modalEl = document.getElementById('onlineHotelModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getInstance(modalEl)?.hide();
        }
        document.querySelector('input[name="hotelSourceType"][value="offline"]')?.click();
    });

    document.getElementById('onlineHotelModal')?.addEventListener('hidden.bs.modal', function () {
        const onlineRadio = document.querySelector('input[name="hotelSourceType"][value="online"]');
        if (onlineRadio && onlineRadio.checked) {
            document.querySelector('input[name="hotelSourceType"][value="offline"]')?.click();
        }
    });
})();
</script>
@endpush
