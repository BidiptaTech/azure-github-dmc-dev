/* === STP LITE: hydrate-edit.js ===
 * Prefill lite edit from STP_LITE_EDIT (tour + order JSON).
 * Prunes service rows outside stay/tour date range when dates change.
 * === */
(function (window, document) {
    'use strict';

    function editCfg() {
        return window.STP_LITE_EDIT || (window.STP_LITE_CONFIG && window.STP_LITE_CONFIG.edit) || null;
    }

    function parseCityCsv(cityCsv) {
        var raw = String(cityCsv || '').trim();
        if (!raw) return [];
        var out = [];
        // Match EditTourController::parseCityPlanTokens — supports → and ->
        var re = /([^,\[]+?)\s*\[(\d{4}-\d{2}-\d{2})\s*(?:→|->)\s*(\d{4}-\d{2}-\d{2})\]/g;
        var m;
        while ((m = re.exec(raw)) !== null) {
            var label = String(m[1] || '').trim();
            var cm = label.match(/^(.*?)\s*\(([^)]+)\)\s*$/);
            out.push({
                name: cm ? cm[1].trim() : label,
                country: cm ? cm[2].trim() : '',
                start: m[2],
                end: m[3],
                label: label
            });
        }
        if (out.length) return out;

        // Fallback: comma-split city labels without bracket dates
        return raw.split(',').map(function (s) { return s.trim(); }).filter(Boolean).map(function (part) {
            var label = part.replace(/\s*\[[^\]]*\]\s*$/, '').trim();
            var cm2 = label.match(/^(.*?)\s*\(([^)]+)\)\s*$/);
            return {
                name: cm2 ? cm2[1].trim() : label,
                country: cm2 ? cm2[2].trim() : '',
                start: '',
                end: '',
                label: label
            };
        });
    }

    /** Seed map used by country-segments while rebuilding stay rows on city select. */
    function buildStaySeed(cityItems) {
        var seed = {};
        (cityItems || []).forEach(function (c) {
            if (!c || !c.name) return;
            var key = String(c.name).toLowerCase();
            if (!seed[key]) {
                seed[key] = {
                    start: c.start || '',
                    end: c.end || '',
                    returnChecked: false,
                    returnStart: '',
                    returnEnd: ''
                };
                return;
            }
            // Second+ token for same city = Return stay
            seed[key].returnChecked = true;
            seed[key].returnStart = c.start || '';
            seed[key].returnEnd = c.end || '';
        });
        return seed;
    }

    function applyStayDates(cityItems) {
        var seed = buildStaySeed(cityItems);
        window.__stpLiteStaySeedByCity = seed;

        // Apply onto existing stay rows (correct classes: .start-date / .end-date)
        document.querySelectorAll('#segmentsWrapper .segment').forEach(function (row) {
            var name = String(row.getAttribute('data-city-name') || '').trim().toLowerCase();
            if (!name || !seed[name]) return;
            var isReturn = row.getAttribute('data-is-return') === '1';
            var startEl = row.querySelector('input.start-date');
            var endEl = row.querySelector('input.end-date');
            var s = isReturn ? seed[name].returnStart : seed[name].start;
            var e = isReturn ? seed[name].returnEnd : seed[name].end;
            if (startEl && s) startEl.value = s;
            if (endEl && e) endEl.value = e;
            if (!isReturn && seed[name].returnChecked) {
                var tog = row.querySelector('.city-return-toggle');
                if (tog) tog.checked = true;
                var hv = row.querySelector('.city-return-value');
                if (hv) hv.value = '1';
            }
        });

        // Rebuild stay rows so seed is applied for every city (incl. return stays)
        if (window.StpLiteCountrySegments) {
            var force = {};
            document.querySelectorAll('#segmentsWrapper .segment:not(.is-return-stay)').forEach(function (row) {
                var name = String(row.getAttribute('data-city-name') || '').trim().toLowerCase();
                var id = String(row.getAttribute('data-city-id') || '');
                if (id && seed[name] && seed[name].returnChecked) force[id] = true;
            });
            if (typeof window.StpLiteCountrySegments.syncAutoCityPlanRows === 'function') {
                window.StpLiteCountrySegments.syncAutoCityPlanRows(
                    Object.keys(force).length ? { forceReturnByCityId: force } : {}
                );
            }
            if (typeof window.StpLiteCountrySegments.updateServicesGate === 'function') {
                window.StpLiteCountrySegments.updateServicesGate();
            }
            if (typeof window.StpLiteCountrySegments.render === 'function') {
                window.StpLiteCountrySegments.render();
            }
        }
    }

    function rowCity(row) {
        if (!row) return '';
        if (row._order_city) return String(row._order_city).replace(/\s*\([^)]*\)\s*$/, '').trim();
        if (row.hotelDetails && row.hotelDetails.location) {
            return String(row.hotelDetails.location).replace(/\s*\([^)]*\)\s*$/, '').trim();
        }
        var keys = ['city', 'City', 'location', 'AttractionCity', 'restaurantCity', 'destination'];
        for (var i = 0; i < keys.length; i++) {
            if (row[keys[i]]) return String(row[keys[i]]).replace(/\s*\([^)]*\)\s*$/, '').trim();
        }
        return '';
    }

    function rowDate(row) {
        if (!row) return '';
        if (Array.isArray(row.bookingDate) && row.bookingDate[0]) return String(row.bookingDate[0]).slice(0, 10);
        if (row.bookingDate) return String(row.bookingDate).slice(0, 10);
        if (row.dateTime) return String(row.dateTime).slice(0, 10);
        if (row.pickupdate) return String(row.pickupdate).slice(0, 10);
        if (row.exitpickupdate) return String(row.exitpickupdate).slice(0, 10);
        if (row.stay_start) return String(row.stay_start).slice(0, 10);
        return '';
    }

    function rowEndDate(row) {
        if (!row) return '';
        if (Array.isArray(row.bookingDate) && row.bookingDate[1]) return String(row.bookingDate[1]).slice(0, 10);
        if (row.stay_end) return String(row.stay_end).slice(0, 10);
        return rowDate(row);
    }

    function setVal(id, value) {
        var el = document.getElementById(id);
        if (!el || value == null) return;
        el.value = value;
        try { el.dispatchEvent(new Event('change', { bubbles: true })); } catch (e) { /* ignore */ }
    }

    function guestPayload(cfg) {
        function hasLead(g) {
            if (!g || typeof g !== 'object') return false;
            return !!(g.full_name || g.fullName || g.name || g.email || g.phone || g.salutation
                || g.address1 || g.passport || g.passport_no);
        }
        var main = cfg.mainGuest || {};
        if (!hasLead(main) && cfg.customer) {
            var c = cfg.customer || {};
            main = {
                salutation: c.salutation || '',
                full_name: c.fullName || c.full_name || c.name || '',
                email: c.email || '',
                phone: c.phone || '',
                country_code: c.countryCode || c.country_code || '',
                address1: c.address1 || '',
                address2: c.address2 || '',
                state: c.state || '',
                zip: c.zip || '',
                special_requests: c.specialRequests || c.special_requests || '',
                passport: c.passport || c.passport_no || '',
                passport_exp: c.passport_exp || c.passport_expiry || ''
            };
        }
        return {
            main: main,
            additional: Array.isArray(cfg.additionalGuests) ? cfg.additionalGuests : []
        };
    }

    function applyHeader(cfg) {
        if (!cfg) return;
        setVal('reference_number', cfg.referenceNumber || '');
        setVal('start_date', cfg.startDate || '');
        setVal('end_date', cfg.endDate || '');
        var travel = document.getElementById('travel_dates');
        if (travel && cfg.datesDisplay) travel.value = cfg.datesDisplay;

        if (window.StpLiteGuestCaps && typeof window.StpLiteGuestCaps.setCaps === 'function') {
            window.StpLiteGuestCaps.setCaps({
                adults: cfg.adults || 1,
                male: cfg.male || 0,
                female: cfg.female || 0,
                children: cfg.children || 0,
                infants: cfg.infants || 0,
                childAges: cfg.childAges || []
            });
        } else {
            setVal('adults', cfg.adults || 1);
            setVal('male', cfg.male || 0);
            setVal('female', cfg.female || 0);
            setVal('children', cfg.children || 0);
            setVal('infants', cfg.infants || 0);
        }

        setVal('tour_type_value', cfg.tourType || 'FIT');
        setVal('city_type_value', cfg.cityType || 'single');
        setVal('city_mode_value', cfg.cityType || 'single');
        if (window.StpLiteCountryMode && typeof window.StpLiteCountryMode.setCityMode === 'function') {
            window.StpLiteCountryMode.setCityMode(cfg.cityType || 'single');
        }
        if (window.StpLiteTourType && typeof window.StpLiteTourType.sync === 'function') {
            window.StpLiteTourType.sync();
        }

        if (cfg.agencyId) setVal('agency_id', cfg.agencyId);
        if (cfg.agentId) {
            setTimeout(function () { setVal('agent_id', cfg.agentId); }, 400);
        }

        var guests = guestPayload(cfg);
        setTimeout(function () {
            if (window.StpLiteGuests && typeof window.StpLiteGuests.hydrate === 'function') {
                window.StpLiteGuests.hydrate(guests.main, guests.additional);
            }
        }, 0);
    }

    function selectCities(cityItems) {
        var $ = window.jQuery;
        var $tc = $('#tour_cities');
        if (!$tc.length || !cityItems.length) return Promise.resolve();

        return new Promise(function (resolve) {
            var done = 0;
            var total = cityItems.length;
            if (!total) { resolve(); return; }

            function next() {
                done++;
                if (done >= total) {
                    $tc.trigger('change');
                    resolve();
                }
            }

            cityItems.forEach(function (item) {
                var exists = $tc.find('option').filter(function () {
                    return String($(this).attr('data-city-name') || $(this).text()).toLowerCase().indexOf(item.name.toLowerCase()) !== -1
                        || String($(this).val()) === String(item.id || '');
                });
                if (exists.length) {
                    exists.prop('selected', true);
                    next();
                    return;
                }
                // Ajax cities — create option
                var opt = new Option(
                    item.country ? (item.name + ' (' + item.country + ')') : item.name,
                    item.id || item.name,
                    true,
                    true
                );
                opt.setAttribute('data-city-name', item.name);
                opt.setAttribute('data-country', item.country || '');
                $tc.append(opt).trigger('change');
                next();
            });
        });
    }

    function normCityName(s) {
        return String(s || '').replace(/\s*\([^)]*\)\s*$/, '').trim().toLowerCase();
    }

    function citiesMatch(a, b) {
        a = normCityName(a);
        b = normCityName(b);
        if (!a || !b) return true; // missing city → don't exclude
        return a === b || a.indexOf(b) !== -1 || b.indexOf(a) !== -1;
    }

    function formatStayLabel(start, end) {
        if (!(start && end)) return '';
        if (typeof moment !== 'undefined') {
            return moment(start, 'YYYY-MM-DD').format('MMM D') + ' → ' + moment(end, 'YYYY-MM-DD').format('MMM D, YYYY');
        }
        return start + ' → ' + end;
    }

    function normalizeHotelRow(row) {
        row = Object.assign({}, row || {});
        var hd = row.hotelDetails || {};
        var rooms = Array.isArray(row.rooms) ? row.rooms : [];
        var r0 = rooms[0] || {};
        var beds = Array.isArray(r0.beds) ? r0.beds : [];
        var b0 = beds[0] || {};
        if (!row.hotel_name) row.hotel_name = hd.hotel_name || hd.name || row.hotelName || 'Hotel';
        if (!row.hotel_unique_id) row.hotel_unique_id = String(hd.hotel_id || row.hotel_id || row.hotel_unique_id || '');
        if (!row.room_type) row.room_type = r0.room_type || r0.roomType || '';
        if (!row.bed_label) row.bed_label = b0.bed_type || b0.bed_label || '';
        if (!row.meal_plan) {
            row.meal_plan = b0.meal_plan || r0.meal_plan
                || (Array.isArray(b0.mealTypes) && b0.mealTypes[0]) || '';
        }
        if (!row.number_of_rooms) row.number_of_rooms = r0.number_of_rooms || row.rooms_count || 1;
        var total = row.grand_total != null ? row.grand_total : (row.totalPrice != null ? row.totalPrice : row.price);
        row.grand_total = Number(total || 0);
        if (row.totalPrice == null) row.totalPrice = row.grand_total;
        if (!row.is_adhoc) {
            row.is_adhoc = !!(row.priceMode === 'adhoc'
                || (row.price_payload && row.price_payload.is_adhoc)
                || (row.helperPriceResult && row.helperPriceResult.is_adhoc));
        }
        var adhocNum = Number(row.adhoc_price);
        if (row.adhoc_price == null || row.adhoc_price === '' || !isFinite(adhocNum) || adhocNum <= 0) {
            var payload = row.price_payload || row.helperPriceResult || null;
            var nights = 0;
            if (payload) {
                nights = parseInt(payload.nights, 10)
                    || (Array.isArray(payload.breakdown) ? payload.breakdown.length : 0)
                    || 0;
            }
            if (nights < 1 && row.stay_start && row.stay_end && typeof moment !== 'undefined') {
                nights = moment(row.stay_end, 'YYYY-MM-DD').diff(moment(row.stay_start, 'YYYY-MM-DD'), 'days');
            }
            if (nights < 1 && Array.isArray(row.bookingDate) && row.bookingDate[0] && row.bookingDate[1]
                && typeof moment !== 'undefined') {
                nights = moment(row.bookingDate[1], 'YYYY-MM-DD').diff(moment(row.bookingDate[0], 'YYYY-MM-DD'), 'days');
            }
            if (nights < 1) nights = 1;
            if (payload && Number(payload.adhoc_price) > 0) {
                row.adhoc_price = Number(payload.adhoc_price);
            } else if (row.is_adhoc && payload && Number(payload.room_total) > 0) {
                row.adhoc_price = Number(payload.room_total) / nights;
            } else if (row.is_adhoc && Number(row.room_total) > 0) {
                row.adhoc_price = Number(row.room_total) / nights;
            } else if (row.is_adhoc && Number(b0.price) > 0) {
                row.adhoc_price = Number(b0.price) / nights;
            } else if (row.is_adhoc && Number(row.grand_total || row.totalPrice || 0) > 0) {
                var meal = Number(row.meal_total || (payload && payload.meal_total) || 0) || 0;
                row.adhoc_price = Math.max(0, Number(row.grand_total || row.totalPrice) - meal) / nights;
            }
        }
        if (!row.stay_start && Array.isArray(row.bookingDate) && row.bookingDate[0]) {
            row.stay_start = String(row.bookingDate[0]).slice(0, 10);
        }
        if (!row.stay_end && Array.isArray(row.bookingDate) && row.bookingDate[1]) {
            row.stay_end = String(row.bookingDate[1]).slice(0, 10);
        }
        if (!row.stay_label) row.stay_label = formatStayLabel(row.stay_start, row.stay_end);
        if (!row.city) row.city = hd.location || hd.city || row._order_city || '';
        if (!row.country) row.country = hd.country || row._order_country || '';
        return row;
    }

    function normalizeServiceRow(kind, row) {
        row = Object.assign({}, row || {});
        if (kind === 'hotel') return normalizeHotelRow(row);
        if (!row.city) row.city = row.AttractionCity || row.restaurantCity || row.location || row._order_city || '';
        if (kind === 'attraction') {
            if (!row.AttractionName) row.AttractionName = row.attraction_name || row.name || 'Attraction';
            if (!row.ticketName) row.ticketName = row.ticket_name || (row.ticket_details && row.ticket_details.name) || '';
            if (row.adultCount == null && row.adults != null) row.adultCount = row.adults;
            if (row.childCount == null && row.children != null) row.childCount = row.children;
            if (Array.isArray(row.bookingDate)) row.bookingDate = row.bookingDate[0] || '';
            if (window.StpLiteTransportShared && typeof window.StpLiteTransportShared.serviceRowDisplayTotal === 'function') {
                var attrTotal = window.StpLiteTransportShared.serviceRowDisplayTotal(row);
                if (attrTotal > Number(row.totalPrice || 0)) {
                    row.totalPrice = attrTotal;
                    row.grand_total = attrTotal;
                }
            }
        }
        if (kind === 'restaurant') {
            if (!row.restaurantName) row.restaurantName = row.restaurant_name || row.name || 'Restaurant';
            if (!row.mealTypeLabel && row.mealType) row.mealTypeLabel = row.mealType;
            if (Array.isArray(row.bookingDate)) row.bookingDate = row.bookingDate[0] || '';
            if (window.StpLiteTransportShared && typeof window.StpLiteTransportShared.serviceRowDisplayTotal === 'function') {
                var restTotal = window.StpLiteTransportShared.serviceRowDisplayTotal(row);
                if (restTotal > Number(row.totalPrice || 0)) {
                    row.totalPrice = restTotal;
                    row.grand_total = restTotal;
                }
            }
        }
        if (kind === 'guide') {
            if (!row.guide_name) row.guide_name = row.guideName || row.name || 'Guide';
            if (Array.isArray(row.bookingDate)) row.bookingDate = row.bookingDate[0] || '';
        }
        if (kind === 'arrival' || kind === 'departure' || kind === 'transport') {
            if (!row.vehicles_name) {
                row.vehicles_name = row.vehicle_name || row.vehicleName
                    || (row.vehicleDetails && (row.vehicleDetails.name || row.vehicleDetails.vehicle_name))
                    || 'Vehicle';
            }
            if (row.totalPrice == null) row.totalPrice = row.price || row.grand_total || 0;
        }
        if (kind === 'miscellaneous') {
            if (!row.itemName) row.itemName = row.item_name || row.miscellaneous_name || 'Miscellaneous';
            if (!row.destination) row.destination = row.city || row._order_city || '';
            if (!row.city) row.city = row.destination || row._order_city || '';
            if (!row.bookingDate && row.dateTime) row.bookingDate = String(row.dateTime).slice(0, 10);
            if (Array.isArray(row.bookingDate)) row.bookingDate = row.bookingDate[0] || '';
            if (row.totalPrice == null) {
                var foc = !!(row.focServiceDiscount || row.foc_service_discount);
                row.totalPrice = foc ? 0 : (
                    (Number(row.adultSell || row.adultCost || 0) * (Number(row.adultsQty) || 0)) +
                    (Number(row.childSell || row.childCost || 0) * (Number(row.childQty) || 0)) +
                    (Number(row.infantSell || row.infantCost || 0) * (Number(row.infantQty) || 0))
                );
            }
        }
        if (row.totalPrice == null && row.price != null) row.totalPrice = row.price;
        return row;
    }

    function inStayRange(dateStr, start, end) {
        if (!dateStr) return true;
        if (!start || !end) return true;
        // Inclusive end so services on stay-until / checkout day still show
        return dateStr >= start && dateStr <= end;
    }

    function hotelOverlapsStay(row, start, end) {
        var s = rowDate(row);
        var e = rowEndDate(row) || s;
        if (!s || !start || !end) return true;
        return s < end && e > start;
    }

    function scoreRowForStay(row, stay, kind) {
        stay = stay || {};
        var city = stay.cityName || '';
        var start = stay.start || '';
        var end = stay.end || '';
        var rc = rowCity(row);
        if (city && rc && !citiesMatch(rc, city)) return -1;
        var dateOk = kind === 'hotel' ? hotelOverlapsStay(row, start, end) : inStayRange(rowDate(row), start, end);
        if (!dateOk) return -1;
        var score = 1;
        if (city && rc && citiesMatch(rc, city)) score += 2;
        if (start && end && rowDate(row)) score += 1;
        return score;
    }

    function findServiceRoot(panel, spec) {
        var mount = panel.querySelector('[data-stp-' + spec.key + '-mount]') || panel;
        return mount.querySelector('.stp-lite-' + (spec.key === 'arrival' ? 'arrival' : spec.key === 'departure' ? 'departure' : spec.key))
            || mount.querySelector('.stp-lite-svc')
            || mount.querySelector('.stp-lite-hotel')
            || mount;
    }

    function filterRowsForStay(rows, stay, kind) {
        return (rows || []).filter(function (row) {
            return scoreRowForStay(row, stay, kind) >= 0;
        });
    }

    function seedServicePanels(services) {
        services = services || {};
        var map = [
            { key: 'hotel', data: services.hotel_data, api: 'StpLiteHotel', chunk: '.hotel_data_chunk' },
            { key: 'arrival', data: services.entry_port_data, api: 'StpLiteArrival', chunk: '.entry_port_data_chunk' },
            { key: 'departure', data: services.exit_port_data, api: 'StpLiteDeparture', chunk: '.exit_port_data_chunk' },
            { key: 'transport', data: services.transport_data, api: 'StpLiteTransport', chunk: '.transport_data_chunk' },
            { key: 'attraction', data: services.attraction_data, api: 'StpLiteAttraction', chunk: '.attraction_data_chunk' },
            { key: 'guide', data: services.guide_data, api: 'StpLiteGuide', chunk: '.guide_data_chunk' },
            { key: 'restaurant', data: services.restaurant_data, api: 'StpLiteRestaurant', chunk: '.restaurant_data_chunk' },
            { key: 'miscellaneous', data: services.miscellaneous_data, api: 'StpLiteMiscellaneous', chunk: '.miscellaneous_data_chunk' }
        ];

        map.forEach(function (spec) {
            var panels = Array.prototype.slice.call(document.querySelectorAll('[data-service="' + spec.key + '"]'));
            if (!panels.length) return;

            var allRows = (spec.data || []).map(function (r) {
                return normalizeServiceRow(spec.key, r);
            });
            var buckets = panels.map(function () { return []; });

            allRows.forEach(function (row) {
                var bestPi = 0;
                var bestScore = -1;
                panels.forEach(function (panel, pi) {
                    var stay = window.StpLiteTransportShared
                        ? window.StpLiteTransportShared.stayFromPanel(panel, spec.key)
                        : {
                            cityName: panel.getAttribute('data-city-name') || '',
                            start: panel.getAttribute('data-stay-start') || '',
                            end: panel.getAttribute('data-stay-end') || ''
                        };
                    var score = scoreRowForStay(row, stay, spec.key);
                    if (score > bestScore) {
                        bestScore = score;
                        bestPi = pi;
                    }
                });
                // Always place the row somewhere so tables aren't empty on edit
                if (bestScore < 0) bestPi = 0;
                buckets[bestPi].push(row);
            });

            panels.forEach(function (panel, pi) {
                var root = findServiceRoot(panel, spec);
                var api = window[spec.api];
                var rows = buckets[pi] || [];
                if (api && typeof api.seedAdded === 'function' && root) {
                    api.seedAdded(root, rows);
                    return;
                }
                var chunk = root && root.querySelector(spec.chunk);
                if (chunk) chunk.value = JSON.stringify(rows);
            });
        });

        ['hotel_data', 'entry_port_data', 'exit_port_data', 'transport_data', 'attraction_data', 'guide_data', 'restaurant_data', 'miscellaneous_data'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el && services[id]) {
                var normalized = (services[id] || []).map(function (r) {
                    var kind = id === 'hotel_data' ? 'hotel'
                        : id === 'entry_port_data' ? 'arrival'
                        : id === 'exit_port_data' ? 'departure'
                        : id === 'miscellaneous_data' ? 'miscellaneous'
                        : id.replace('_data', '');
                    return normalizeServiceRow(kind, r);
                });
                el.value = JSON.stringify(normalized);
            }
        });
        if (window.StpLiteHotel && typeof window.StpLiteHotel.syncHotelDataHidden === 'function') {
            window.StpLiteHotel.syncHotelDataHidden();
        }
    }

    function pruneOutOfRangeServices() {
        var sections = document.querySelectorAll('.stp-lite-country-section');
        sections.forEach(function (section) {
            var start = section.getAttribute('data-stay-start') || '';
            var end = section.getAttribute('data-stay-end') || '';
            // Don't wipe seeded tables while stay dates are still empty / loading
            if (!start || !end) return;
            section.querySelectorAll('[data-service]').forEach(function (panel) {
                var key = panel.getAttribute('data-service') || '';
                var chunkSel = {
                    hotel: '.hotel_data_chunk',
                    arrival: '.entry_port_data_chunk',
                    departure: '.exit_port_data_chunk',
                    transport: '.transport_data_chunk',
                    attraction: '.attraction_data_chunk',
                    guide: '.guide_data_chunk',
                    restaurant: '.restaurant_data_chunk',
                    miscellaneous: '.miscellaneous_data_chunk'
                }[key];
                if (!chunkSel) return;
                var root = panel.querySelector('.stp-lite-hotel, .stp-lite-arrival, .stp-lite-departure, .stp-lite-transport, .stp-lite-attraction, .stp-lite-guide, .stp-lite-restaurant, .stp-lite-miscellaneous, .stp-lite-svc') || panel;
                var chunk = root.querySelector(chunkSel);
                if (!chunk) return;
                var rows = [];
                try { rows = JSON.parse(chunk.value || '[]') || []; } catch (e) { rows = []; }
                var kept = rows.filter(function (row) {
                    if (key === 'hotel') return hotelOverlapsStay(row, start, end);
                    return inStayRange(rowDate(row), start, end);
                });
                if (kept.length === rows.length) return;
                var apiName = {
                    hotel: 'StpLiteHotel',
                    arrival: 'StpLiteArrival',
                    departure: 'StpLiteDeparture',
                    transport: 'StpLiteTransport',
                    attraction: 'StpLiteAttraction',
                    guide: 'StpLiteGuide',
                    restaurant: 'StpLiteRestaurant',
                    miscellaneous: 'StpLiteMiscellaneous'
                }[key];
                var api = window[apiName];
                if (api && typeof api.seedAdded === 'function') api.seedAdded(root, kept);
                else chunk.value = JSON.stringify(kept);
            });
        });
        if (window.StpLiteHotel && window.StpLiteHotel.syncHotelDataHidden) {
            window.StpLiteHotel.syncHotelDataHidden();
        }
    }

    function bindPrune() {
        document.addEventListener('stp:dates-changed', function () {
            setTimeout(pruneOutOfRangeServices, 50);
        });
        document.addEventListener('stp:stay-dates-ready', function () {
            setTimeout(pruneOutOfRangeServices, 50);
        });
        document.addEventListener('change', function (e) {
            if (!e.target) return;
            if (e.target.matches && e.target.matches('.stay-from, .stay-until, .city-stay-from, .city-stay-until, #start_date, #end_date')) {
                setTimeout(pruneOutOfRangeServices, 80);
            }
        });
    }

    function run() {
        var cfg = editCfg();
        if (!cfg || !cfg.tourId) return;

        applyHeader(cfg);
        bindPrune();
        bindEditSectionReseed();

        var cities = parseCityCsv(cfg.city || '');
        if (!cities.length && cfg.destination) {
            cities = String(cfg.destination).split(',').map(function (c) {
                return { name: '', country: c.trim(), start: cfg.startDate || '', end: cfg.endDate || '' };
            }).filter(function (c) { return c.country; });
        }

        // Only fill missing bracket dates from tour window when a city has no own range
        cities = cities.map(function (c) {
            if (!c.start) c.start = '';
            if (!c.end) c.end = '';
            return c;
        });

        // Seed BEFORE selecting cities so syncAutoCityPlanRows picks up stay dates
        window.__stpLiteStaySeedByCity = buildStaySeed(cities);

        // Unique cities for the city multi-select (return stays reuse the same city)
        var seenCity = {};
        var citiesForSelect = cities.filter(function (c) {
            var k = String(c.name || '').toLowerCase();
            if (!k || seenCity[k]) return false;
            seenCity[k] = true;
            return true;
        });

        selectCities(citiesForSelect).then(function () {
            setTimeout(function () {
                applyStayDates(cities);
                setTimeout(function () {
                    silentMountAndSeedServices(cfg.services || {});
                    pruneOutOfRangeServices();
                }, 350);
            }, 250);
        });
    }

    function currentServicesSnapshot() {
        var cfg = editCfg() || {};
        var base = cfg.services || {};
        var out = {};
        ['hotel_data', 'entry_port_data', 'exit_port_data', 'transport_data', 'attraction_data', 'guide_data', 'restaurant_data', 'miscellaneous_data'].forEach(function (id) {
            var el = document.getElementById(id);
            var fromDom = null;
            if (el && el.value) {
                try {
                    var parsed = JSON.parse(el.value);
                    if (Array.isArray(parsed) && parsed.length) fromDom = parsed;
                } catch (e) { /* ignore */ }
            }
            // Prefer live chunks when present (user may have edited)
            var fromChunks = [];
            var chunkSel = {
                hotel_data: '.hotel_data_chunk',
                entry_port_data: '.entry_port_data_chunk',
                exit_port_data: '.exit_port_data_chunk',
                transport_data: '.transport_data_chunk',
                attraction_data: '.attraction_data_chunk',
                guide_data: '.guide_data_chunk',
                restaurant_data: '.restaurant_data_chunk',
                miscellaneous_data: '.miscellaneous_data_chunk'
            }[id];
            if (chunkSel) {
                document.querySelectorAll(chunkSel).forEach(function (chunk) {
                    try {
                        var rows = JSON.parse(chunk.value || '[]');
                        if (Array.isArray(rows)) fromChunks = fromChunks.concat(rows);
                    } catch (e2) { /* ignore */ }
                });
            }
            if (fromChunks.length) out[id] = fromChunks;
            else if (fromDom) out[id] = fromDom;
            else out[id] = base[id] || [];
        });
        return out;
    }

    /** Mount shells inside collapsed accordions + seed rows (open on click like create). */
    function silentMountAndSeedServices(services) {
        services = services || currentServicesSnapshot();
        document.querySelectorAll('.stp-lite-service-body').forEach(function (body) {
            body.classList.remove('show');
            body.classList.remove('collapsing');
        });
        ['StpLiteHotel', 'StpLiteArrival', 'StpLiteDeparture', 'StpLiteTransport', 'StpLiteAttraction', 'StpLiteGuide', 'StpLiteRestaurant', 'StpLiteMiscellaneous'].forEach(function (name) {
            if (window[name] && typeof window[name].mountAll === 'function') {
                window[name].mountAll(document);
            }
        });
        document.querySelectorAll('.stp-lite-service-panel').forEach(function (panel) {
            panel.setAttribute('data-mounted', '1');
        });
        seedServicePanels(services);
        document.querySelectorAll('.stp-lite-service-body').forEach(function (body) {
            body.classList.remove('show');
        });
        if (window.StpLiteTransportShared && typeof window.StpLiteTransportShared.refreshAllStaySectionTotals === 'function') {
            window.StpLiteTransportShared.refreshAllStaySectionTotals(document);
        }
    }

    function bindEditSectionReseed() {
        if (window.__stpLiteEditReseedBound) return;
        window.__stpLiteEditReseedBound = true;
        document.addEventListener('stp:country-sections-rendered', function () {
            var cfg = editCfg();
            if (!cfg || !cfg.tourId) return;
            setTimeout(function () {
                silentMountAndSeedServices(currentServicesSnapshot());
            }, 80);
        });
    }

    window.StpLiteHydrateEdit = {
        run: run,
        pruneOutOfRangeServices: pruneOutOfRangeServices,
        parseCityCsv: parseCityCsv,
        silentMountAndSeedServices: silentMountAndSeedServices
    };

    // Boot is triggered from main.js after module init (avoids race with create-mode).
})(window, document);
/* === END hydrate-edit.js === */
