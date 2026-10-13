{{-- Included inside a parent <script> block. Requires cityCountryMap, cityIdMap, selectedDestinations. --}}
    @php
        $transferDestinationCatalog = [
            'ports' => ($ports ?? collect())->map(function ($p) {
                return [
                    'id' => $p->port_id,
                    'name' => $p->port_name,
                    'city_id' => (int) ($p->city_id ?? 0),
                    'country' => (string) ($p->country ?? ''),
                ];
            })->values(),
            'hotels' => ($hotels ?? collect())->map(function ($h) {
                return [
                    'id' => $h->hotel_unique_id,
                    'name' => $h->name,
                    'city' => (string) ($h->city ?? ''),
                    'country' => (string) ($h->country ?? ''),
                    'zone_id' => (string) ($h->zone_id ?? ''),
                ];
            })->values(),
            'attractions' => ($attractions ?? collect())->map(function ($a) {
                return [
                    'id' => $a->attraction_id,
                    'name' => $a->name,
                    'location' => (string) ($a->location ?? ''),
                    'country' => (string) ($a->country ?? ''),
                    'zone_id' => (string) ($a->zone_id ?? ''),
                ];
            })->values(),
            'restaurants' => ($restaurants ?? collect())->map(function ($r) {
                return [
                    'id' => $r->restaurant_id,
                    'name' => $r->name,
                    'city' => (string) ($r->city ?? ''),
                    'country' => (string) ($r->country ?? ''),
                    'zone_id' => (string) ($r->zone_id ?? ''),
                ];
            })->values(),
        ];
    @endphp
    window.transferDestinationCatalog = @json($transferDestinationCatalog);

    function getSelectedCountriesFromCities() {
        if (typeof selectedDestinations === 'undefined' || !Array.isArray(selectedDestinations)) {
            return [];
        }
        const countries = selectedDestinations
            .map(function (city) { return (cityCountryMap && cityCountryMap[city]) ? cityCountryMap[city] : ''; })
            .filter(Boolean);
        return [...new Set(countries)];
    }

    /** Resolve country for a city name via master-DMC scoped cityCountryMap (Lite parity). */
    function resolveCountryForCity(cityName) {
        const city = String(cityName || '').trim();
        if (!city) return '';
        if (typeof cityCountryMap !== 'undefined' && cityCountryMap && cityCountryMap[city]) {
            return String(cityCountryMap[city]);
        }
        const parts = city.split(',').map(function (p) { return p.trim(); }).filter(Boolean);
        for (let i = 0; i < parts.length; i++) {
            if (cityCountryMap && cityCountryMap[parts[i]]) {
                return String(cityCountryMap[parts[i]]);
            }
        }
        return '';
    }

    function getSelectedCityIdsFromCities() {
        try {
            const ids = (selectedDestinations || []).map(function (name) {
                const key = String(name || '').trim();
                return cityIdMap && Object.prototype.hasOwnProperty.call(cityIdMap, key)
                    ? parseInt(cityIdMap[key], 10)
                    : null;
            }).filter(function (v) { return Number.isFinite(v) && v > 0; });
            return Array.from(new Set(ids));
        } catch (e) {
            return [];
        }
    }

    /**
     * Active city scope for accommodation / arrival / departure filters.
     * Prefer explicit preferredCity, then A/D City select, hotelDestination, else header cities.
     */
    function getActiveServiceCityNames(preferredCity) {
        const preferred = String(preferredCity || '').trim();
        if (preferred) return [preferred];
        const arrDepCity = String(document.getElementById('arrivalDepartureCity')?.value || '').trim();
        if (arrDepCity) return [arrDepCity];
        const hotelCity = String(document.getElementById('hotelDestination')?.value || '').trim();
        if (hotelCity) return [hotelCity];
        if (typeof selectedDestinations !== 'undefined' && Array.isArray(selectedDestinations) && selectedDestinations.length) {
            return selectedDestinations.map(function (c) { return String(c || '').trim(); }).filter(Boolean);
        }
        return [];
    }

    function getActiveServiceCityIds(cityNames) {
        const names = cityNames || getActiveServiceCityNames();
        const ids = [];
        names.forEach(function (name) {
            if (cityIdMap && Object.prototype.hasOwnProperty.call(cityIdMap, name)) {
                const id = parseInt(cityIdMap[name], 10);
                if (Number.isFinite(id) && id > 0) ids.push(id);
            }
        });
        return Array.from(new Set(ids));
    }

    function getActiveServiceCountries(cityNames) {
        const names = cityNames || getActiveServiceCityNames();
        const countries = names.map(resolveCountryForCity).filter(Boolean);
        return Array.from(new Set(countries));
    }

    /** City names for a selected arrival/departure port (multi-city: Batam port → Batam hotels). */
    function resolveCityNamesFromPortSelect(portSelectId) {
        const portSel = document.getElementById(portSelectId);
        if (!portSel || !portSel.value) return [];
        const opt = portSel.selectedOptions?.[0]
            || portSel.querySelector('option[value="' + escapeCssAttr(portSel.value) + '"]');
        if (!opt) return [];

        const cityId = parseInt(opt.getAttribute('data-city-id') || '0', 10) || 0;
        if (cityId && typeof cityIdMap !== 'undefined' && cityIdMap) {
            const matched = [];
            Object.keys(cityIdMap).forEach(function (name) {
                if (parseInt(cityIdMap[name], 10) === cityId) matched.push(name);
            });
            if (matched.length) return matched;
        }

        const country = String(opt.getAttribute('data-country') || '').trim();
        if (country) {
            const fromHeader = (typeof selectedDestinations !== 'undefined' ? selectedDestinations : [])
                .filter(function (c) { return resolveCountryForCity(c) === country; });
            if (fromHeader.length) return fromHeader;
            if (typeof cityCountryMap !== 'undefined' && cityCountryMap) {
                return Object.keys(cityCountryMap).filter(function (n) {
                    return cityCountryMap[n] === country;
                });
            }
        }
        return [];
    }

    /** Resolve currency code for a country name via countries.currency map. */
    function resolveCurrencyForCountry(countryName) {
        const country = String(countryName || '').trim();
        if (!country) return '';
        if (typeof countryCurrencyMap !== 'undefined' && countryCurrencyMap && countryCurrencyMap[country]) {
            return String(countryCurrencyMap[country] || '').trim().toUpperCase();
        }
        return '';
    }
    window.resolveCurrencyForCountry = resolveCurrencyForCountry;

    /**
     * Per-service city + country + currency for order JSON / multi-country discount buckets.
     */
    function resolveServiceGeoFields(item) {
        const row = item && typeof item === 'object' ? item : {};
        // Prefer explicit city — transfer rows use destination as "Arrival: Port → Hotel"
        let city = String(
            row.city || row.hotelCity || row.hotel_city || row.destination || row.location || ''
        ).trim();
        if (/^(Arrival|Departure)\s*:/i.test(city)) {
            city = String(row.city || row.hotelCity || row.hotel_city || '').trim();
        }
        if (city.includes(',')) {
            city = city.split(',')[0].trim();
        }
        if (!city && typeof selectedDestinations !== 'undefined' && selectedDestinations.length === 1) {
            city = String(selectedDestinations[0] || '').trim();
        }
        let country = resolveCountryForCity(city);
        if (!country && row.country) {
            const raw = String(row.country).trim();
            if (raw && !raw.includes(',')) {
                const looksLikeCity = !!(cityCountryMap && Object.prototype.hasOwnProperty.call(cityCountryMap, raw));
                country = looksLikeCity ? (cityCountryMap[raw] || '') : raw;
            }
        }
        if (!country) {
            const countries = getSelectedCountriesFromCities();
            if (countries.length === 1) country = countries[0];
        }
        let currency = String(row.currency || '').trim().toUpperCase();
        if (!currency && country) {
            currency = resolveCurrencyForCountry(country);
        }
        return { city: city || '', country: country || '', currency: currency || '' };
    }

    /** Spread into order JSON: city + country + currency (orders.country / orders.currency). */
    function serviceOrderGeo(item, destinationFallback) {
        const fb = String(destinationFallback || '').split(',')[0].trim();
        const merged = Object.assign({}, item || {});
        if (!merged.destination && !merged.city) {
            merged.destination = fb;
        }
        const g = resolveServiceGeoFields(merged);
        const city = g.city || fb;
        const country = g.country || resolveCountryForCity(city) || '';
        const currency = g.currency || resolveCurrencyForCountry(country) || '';
        return { city: city, country: country, currency: currency };
    }
    window.serviceOrderGeo = serviceOrderGeo;
    window.resolveServiceGeoFields = resolveServiceGeoFields;

    /**
     * Stamp city/country/currency onto arrival/departure (and similar) list rows
     * so order save does not fall back to header multi-city / first city (e.g. Singapore).
     */
    function stampArrivalDepartureGeo(item, preferredCity) {
        const row = item && typeof item === 'object' ? item : {};
        const city = String(
            preferredCity
            || row.city
            || row.destination
            || document.getElementById('arrivalDepartureCity')?.value
            || document.getElementById('hotelDestination')?.value
            || ''
        ).split(',')[0].trim();
        const geo = serviceOrderGeo(Object.assign({}, row, { city: city, destination: city }), city);
        row.city = geo.city || city;
        row.country = geo.country || '';
        row.currency = geo.currency || '';
        return row;
    }
    window.stampArrivalDepartureGeo = stampArrivalDepartureGeo;

    /** Normalize city label (first CSV segment, trimmed). */
    function normalizeServiceCityName(value) {
        return String(value || '').split(',')[0].trim();
    }
    window.normalizeServiceCityName = normalizeServiceCityName;

    function serviceCityKey(value) {
        return normalizeServiceCityName(value).toLowerCase();
    }
    window.serviceCityKey = serviceCityKey;

    /** Hotel row city for multi-city arrival/departure grouping. */
    function getHotelServiceCity(hotel) {
        if (!hotel || typeof hotel !== 'object') return '';
        return normalizeServiceCityName(hotel.city || hotel.destination || hotel.hotelCity || hotel.hotel_city || '');
    }
    window.getHotelServiceCity = getHotelServiceCity;

    /**
     * Group hotels by service city.
     * Same city → one group (one A/D pair). Different cities → separate groups.
     * @returns {Map<string, { cityName: string, hotels: array }>}
     */
    function groupHotelsByServiceCity(hotels) {
        const map = new Map();
        (hotels || []).forEach(function (h) {
            const cityName = getHotelServiceCity(h);
            // Keep city/destination stamped so later syncs keep same-city hotels in one group
            if (cityName) {
                if (!h.city) h.city = cityName;
                if (!h.destination) h.destination = cityName;
            }
            const key = serviceCityKey(cityName) || '__none__';
            if (!map.has(key)) {
                map.set(key, { cityName: cityName || '', hotels: [] });
            }
            map.get(key).hotels.push(h);
        });
        return map;
    }
    window.groupHotelsByServiceCity = groupHotelsByServiceCity;

    // ==================== CITY-WISE TOUR DATE WINDOWS ====================
    window.enquiryProInitialCityDateRanges = @json($initialData['city_date_ranges'] ?? []);
    window.enquiryProCityDateRanges = Array.isArray(window.enquiryProInitialCityDateRanges)
        ? window.enquiryProInitialCityDateRanges.map(function (range) {
            return {
                city: String(range.city || '').trim(),
                start_date: String(range.start_date || range.start || '').substring(0, 10),
                end_date: String(range.end_date || range.end || '').substring(0, 10)
            };
        })
        : [];

    function enquiryProDateOnly(value) {
        return String(value || '').substring(0, 10);
    }

    function enquiryProIsoDateToUtc(value) {
        const parts = enquiryProDateOnly(value).split('-').map(Number);
        if (parts.length !== 3 || parts.some(function (n) { return !Number.isFinite(n); })) return null;
        return new Date(Date.UTC(parts[0], parts[1] - 1, parts[2]));
    }

    function enquiryProUtcDateString(date) {
        return date ? date.toISOString().substring(0, 10) : '';
    }

    function enquiryProTourDateBounds() {
        const startInput = (typeof getHeaderStartInput === 'function')
            ? getHeaderStartInput()
            : document.querySelector('input#tourStartDate');
        const endInput = (typeof getHeaderEndInput === 'function')
            ? getHeaderEndInput()
            : document.querySelector('input#tourEndDate');
        return {
            start: enquiryProDateOnly(startInput?.value),
            end: enquiryProDateOnly(endInput?.value)
        };
    }

    function enquiryProShiftDate(value, days) {
        const date = enquiryProIsoDateToUtc(value);
        if (!date) return '';
        return enquiryProUtcDateString(new Date(date.getTime() + (days * 86400000)));
    }

    function enquiryProNightCount(start, end) {
        const from = enquiryProIsoDateToUtc(start);
        const to = enquiryProIsoDateToUtc(end);
        if (!from || !to) return 0;
        return Math.max(0, Math.round((to.getTime() - from.getTime()) / 86400000));
    }

    function enquiryProBuildDefaultCityRanges(cities, tourStart, tourEnd) {
        const start = enquiryProIsoDateToUtc(tourStart);
        const end = enquiryProIsoDateToUtc(tourEnd);
        if (!start || !end || end <= start || !cities.length) return [];
        const totalNights = Math.round((end.getTime() - start.getTime()) / 86400000);
        if (totalNights < cities.length) return [];

        return cities.map(function (city, index) {
            const from = new Date(start.getTime() + Math.round((totalNights * index) / cities.length) * 86400000);
            const to = new Date(start.getTime() + Math.round((totalNights * (index + 1)) / cities.length) * 86400000);
            return { city: city, start_date: enquiryProUtcDateString(from), end_date: enquiryProUtcDateString(to) };
        });
    }

    function getCityDateRange(city) {
        const key = serviceCityKey(city);
        if (!key) return null;
        return (window.enquiryProCityDateRanges || []).find(function (range) {
            return serviceCityKey(range.city) === key;
        }) || null;
    }
    window.getCityDateRange = getCityDateRange;

    function getDefaultMealCountForCity(city) {
        const range = getCityDateRange(city);
        if (range && range.start_date && range.end_date) {
            const nights = enquiryProNightCount(range.start_date, range.end_date);
            if (nights > 0) return nights;
        }
        const fallback = parseInt(document.getElementById('nightsDisplay')?.textContent, 10);
        return Number.isFinite(fallback) && fallback > 0 ? fallback : 1;
    }
    window.getDefaultMealCountForCity = getDefaultMealCountForCity;

    function getCityAnchoredDateTime(city, dateTime, offsetDays, fallbackTime) {
        const timePart = (String(dateTime || '').split('T')[1] || fallbackTime || '12:00').substring(0, 5);
        const range = getCityDateRange(city);
        let start = enquiryProDateOnly(dateTime);
        if (!start && range && range.start_date) start = range.start_date;
        if (!start) start = enquiryProTourDateBounds().start || '';
        if (!start) return dateTime || '';

        let target = enquiryProShiftDate(start, offsetDays || 0) || start;
        if (range && range.start_date && target < range.start_date) target = range.start_date;
        if (range && range.end_date && target > range.end_date) target = range.end_date;
        return target + 'T' + timePart;
    }
    window.getCityAnchoredDateTime = getCityAnchoredDateTime;

    function syncCityDateRangePanel(options) {
        options = options || {};
        const section = document.getElementById('cityDateRangeSection');
        const rows = document.getElementById('cityDateRangeRows');
        if (!section || !rows) return;

        const cities = (typeof selectedDestinations !== 'undefined' && Array.isArray(selectedDestinations))
            ? selectedDestinations.map(function (city) { return String(city || '').trim(); }).filter(Boolean)
            : [];
        const bounds = enquiryProTourDateBounds();
        const readOnly = section.dataset.readonly === '1';
        section.style.display = cities.length ? '' : 'none';
        if (!cities.length) {
            rows.innerHTML = '';
            window.enquiryProCityDateRanges = [];
            return;
        }

        const existingByCity = {};
        (window.enquiryProCityDateRanges || []).forEach(function (range) {
            existingByCity[serviceCityKey(range.city)] = range;
        });
        const defaults = enquiryProBuildDefaultCityRanges(cities, bounds.start, bounds.end);
        const defaultByCity = {};
        defaults.forEach(function (range) { defaultByCity[serviceCityKey(range.city)] = range; });

        // Rebuild the ranges: keep existing cities' windows, redistribute remaining nights
        // evenly among new cities so adding/removing a city always produces a valid chain.
        const hasExisting = cities.some(function (c) { return !!existingByCity[serviceCityKey(c)]; });
        if (!hasExisting) {
            // First time: use the even-split defaults for all cities.
            window.enquiryProCityDateRanges = defaults.length ? defaults : cities.map(function (c) {
                return { city: c, start_date: '', end_date: '' };
            });
        } else if (readOnly) {
            window.enquiryProCityDateRanges = cities.map(function (city) {
                const current = existingByCity[serviceCityKey(city)];
                return current ? { ...current, city: city } : { city: city, start_date: '', end_date: '' };
            });
        } else {
            // Separate cities that already have a range from brand-new ones.
            const kept = [];
            const newCityIndices = [];
            cities.forEach(function (city, index) {
                const current = existingByCity[serviceCityKey(city)];
                if (current && current.start_date && current.end_date
                    && current.start_date >= bounds.start && current.end_date <= bounds.end) {
                    kept.push({ index: index, range: { ...current, city: city } });
                } else {
                    newCityIndices.push(index);
                }
            });

            // Build the result array, inserting new cities into the gaps.
            const result = new Array(cities.length);
            kept.forEach(function (item) { result[item.index] = item.range; });

            const totalNights = enquiryProNightCount(bounds.start, bounds.end);
            const keptNights = kept.reduce(function (sum, item) {
                return sum + enquiryProNightCount(item.range.start_date, item.range.end_date);
            }, 0);

            if (newCityIndices.length) {
                const nightsForNew = Math.max(newCityIndices.length, totalNights - keptNights);
                const nightsPerNew = Math.max(1, Math.floor(nightsForNew / newCityIndices.length));

                // If existing cities take up too many nights, shrink them proportionally.
                if (keptNights + newCityIndices.length > totalNights && kept.length) {
                    const targetKept = totalNights - (newCityIndices.length * 1);
                    let assignedKept = 0;
                    kept.forEach(function (item, i) {
                        const oldN = enquiryProNightCount(item.range.start_date, item.range.end_date);
                        const share = i === kept.length - 1
                            ? targetKept - assignedKept
                            : Math.max(1, Math.round(oldN * targetKept / keptNights));
                        item.newNights = share;
                        assignedKept += share;
                    });
                }
            }

            // Walk left-to-right and stitch the chain: each city starts where the previous ended.
            let cursor = bounds.start;
            for (let i = 0; i < cities.length; i++) {
                if (result[i]) {
                    const item = kept.find(function (k) { return k.index === i; });
                    const nights = item.newNights !== undefined
                        ? item.newNights
                        : enquiryProNightCount(item.range.start_date, item.range.end_date);
                    result[i].start_date = cursor;
                    result[i].end_date = enquiryProShiftDate(cursor, nights);
                    cursor = result[i].end_date;
                } else {
                    const isLast = i === cities.length - 1;
                    const nightsPerNew = Math.max(1, Math.floor(
                        Math.max(newCityIndices.length, totalNights - keptNights) / newCityIndices.length));
                    const nights = isLast
                        ? enquiryProNightCount(cursor, bounds.end) || nightsPerNew
                        : nightsPerNew;
                    result[i] = {
                        city: cities[i],
                        start_date: cursor,
                        end_date: enquiryProShiftDate(cursor, nights)
                    };
                    cursor = result[i].end_date;
                }
            }
            // Last city always reaches the tour end.
            result[cities.length - 1].end_date = bounds.end;

            window.enquiryProCityDateRanges = result;
        }

        const total = window.enquiryProCityDateRanges.length;
        rows.innerHTML = '';
        window.enquiryProCityDateRanges.forEach(function (range, index) {
            // Every city needs at least one night, so each side of the chain reserves a day per remaining city.
            const startMin = enquiryProShiftDate(bounds.start, index) || bounds.start;
            const startMax = enquiryProShiftDate(bounds.end, -(total - index)) || bounds.end;
            const endMin = enquiryProShiftDate(bounds.start, index + 1) || bounds.end;
            const endMax = enquiryProShiftDate(bounds.end, -(total - index - 1)) || bounds.end;
            const nights = enquiryProNightCount(range.start_date, range.end_date);
            const lockStart = index === 0;
            const lockEnd = index === total - 1;

            const col = document.createElement('div');
            col.className = 'city-stay-col';
            col.innerHTML = `
                <div class="city-stay-card${readOnly ? ' is-locked' : ''}">
                    <div class="city-stay-card-head">
                        <span class="city-stay-seq">${index + 1}</span>
                        <span class="city-stay-name" title="${range.city}">${range.city}</span>
                        <span class="city-stay-nights">${nights} ${nights === 1 ? 'Night' : 'Nights'}</span>
                    </div>
                    <div class="city-stay-card-body">
                        <div class="city-stay-field">
                            <label>Check-in</label>
                            <input type="date" class="form-control form-control-sm city-date-range-start"
                                data-index="${index}" value="${range.start_date}" min="${startMin}" max="${startMax}"
                                ${(readOnly || lockStart) ? 'disabled' : ''}>
                        </div>
                        <span class="city-stay-arrow"><i class="ri-arrow-right-line"></i></span>
                        <div class="city-stay-field">
                            <label>Check-out</label>
                            <input type="date" class="form-control form-control-sm city-date-range-end"
                                data-index="${index}" value="${range.end_date}" min="${endMin}" max="${endMax}"
                                ${(readOnly || lockEnd) ? 'disabled' : ''}>
                        </div>
                    </div>
                    <div class="city-stay-note">${lockStart ? 'Starts with the tour' : 'Starts when ' + (window.enquiryProCityDateRanges[index - 1]?.city || 'previous city') + ' ends'}${lockEnd ? ' &middot; Ends with the tour' : ''}</div>
                </div>`;
            rows.appendChild(col);
        });

        if (!readOnly) {
            rows.querySelectorAll('.city-date-range-start,.city-date-range-end').forEach(function (input) {
                input.addEventListener('change', function () {
                    const index = parseInt(this.dataset.index, 10);
                    const isStart = this.classList.contains('city-date-range-start');
                    applyCityDateRangeEdit(index, isStart ? 'start_date' : 'end_date', this.value);
                });
            });
        }
        validateCityDateRanges({ showError: options.showError === true });
    }
    window.syncCityDateRangePanel = syncCityDateRangePanel;

    // Cities are stored as one continuous chain: a city's check-out is always the next city's check-in.
    function applyCityDateRangeEdit(index, field, value) {
        const ranges = window.enquiryProCityDateRanges || [];
        const range = ranges[index];
        const newValue = enquiryProDateOnly(value);
        if (!range || !newValue) return;
        const previousRanges = JSON.parse(JSON.stringify(ranges));

        range[field] = newValue;
        if (field === 'end_date') {
            if (range.end_date <= range.start_date) {
                range.start_date = enquiryProShiftDate(range.end_date, -1);
            }
            if (ranges[index + 1]) {
                ranges[index + 1].start_date = range.end_date;
                if (ranges[index + 1].end_date <= ranges[index + 1].start_date) {
                    ranges[index + 1].end_date = enquiryProShiftDate(ranges[index + 1].start_date, 1);
                }
            }
        } else {
            if (range.end_date <= range.start_date) {
                range.end_date = enquiryProShiftDate(range.start_date, 1);
            }
            if (ranges[index - 1]) {
                ranges[index - 1].end_date = range.start_date;
                if (ranges[index - 1].end_date <= ranges[index - 1].start_date) {
                    ranges[index - 1].start_date = enquiryProShiftDate(ranges[index - 1].end_date, -1);
                }
            }
        }

        syncCityDateRangePanel({ showError: true });
        if (typeof applyOpenServiceCityDateRanges === 'function') applyOpenServiceCityDateRanges();
        if (typeof window.onEnquiryProCityDateRangesEdited === 'function') {
            try {
                window.onEnquiryProCityDateRangesEdited({
                    index: index,
                    field: field,
                    value: newValue,
                    previousRanges: previousRanges,
                    nextRanges: JSON.parse(JSON.stringify(window.enquiryProCityDateRanges || []))
                });
            } catch (error) {
                console.error('Failed handling editable city date change:', error);
            }
        }
    }

    function validateCityDateRanges(options) {
        options = options || {};
        const cities = (typeof selectedDestinations !== 'undefined' && Array.isArray(selectedDestinations))
            ? selectedDestinations.map(function (city) { return String(city || '').trim(); }).filter(Boolean)
            : [];
        const bounds = enquiryProTourDateBounds();
        const ranges = window.enquiryProCityDateRanges || [];
        let message = '';

        if (!cities.length) message = 'Select at least one city.';
        else if (ranges.length !== cities.length) message = 'Set dates for every selected city.';
        else if (!bounds.start || !bounds.end) message = 'Select the tour start and end dates first.';
        else if (ranges.some(function (r) { return !r.start_date || !r.end_date; })) message = 'Set From and To dates for every city.';
        else if (ranges.some(function (r) {
            return r.start_date < bounds.start || r.end_date > bounds.end || r.end_date <= r.start_date;
        })) message = 'Each city must be inside the tour dates and include at least one night.';
        else if (ranges[0].start_date !== bounds.start || ranges[ranges.length - 1].end_date !== bounds.end) {
            message = 'The first city must start on the tour start date and the last city must end on the tour end date.';
        } else {
            for (let i = 1; i < ranges.length; i++) {
                if (ranges[i].start_date !== ranges[i - 1].end_date) {
                    message = 'City date ranges must connect without gaps or overlaps.';
                    break;
                }
            }
        }

        const errorEl = document.getElementById('cityDateRangeError');
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.style.display = message && options.showError ? '' : 'none';
        }
        return { valid: !message, message: message, ranges: ranges };
    }
    window.validateCityDateRanges = validateCityDateRanges;

    // Each service date input, the city select that drives it, and the day of the city window it should land on.
    const ENQUIRY_PRO_CITY_DATE_FIELDS = {
        accommodation: [
            { id: 'checkInDate', anchor: 'start', time: '14:00' },
            { id: 'checkOutDate', anchor: 'end', time: '12:00' }
        ],
        tour: [{ id: 'tourDateTime', anchor: 'start', time: '09:00' }],
        meal: [{ id: 'mealDateTime', anchor: 'start', time: '12:00' }],
        guide: [{ id: 'guideDate', anchor: 'start', time: '10:00' }],
        misc: [{ id: 'miscDate', anchor: 'start', time: '09:00' }],
        local: [{ id: 'localDateTime', anchor: 'start', time: '09:00' }],
        arrivaldeparture: [
            { id: 'arrivalDateTime', anchor: 'start', time: '09:00' },
            { id: 'departureDateTime', anchor: 'end', time: '09:00' }
        ]
    };
    ENQUIRY_PRO_CITY_DATE_FIELDS.arrival = ENQUIRY_PRO_CITY_DATE_FIELDS.arrivaldeparture;
    ENQUIRY_PRO_CITY_DATE_FIELDS.departure = ENQUIRY_PRO_CITY_DATE_FIELDS.arrivaldeparture;

    const ENQUIRY_PRO_CITY_SELECT_BY_CONTEXT = {
        accommodation: 'hotelDestination',
        tour: 'tourDestination',
        meal: 'mealDestination',
        guide: 'guideDestination',
        misc: 'miscDestination',
        local: 'localDestination',
        arrivaldeparture: 'arrivalDepartureCity'
    };

    // Flags the service modals set while they populate an existing entry for editing.
    const ENQUIRY_PRO_EDIT_FLAGS = {
        accommodation: 'editingAccommodationIndex',
        tour: 'editingTourIndex',
        meal: 'editingMealIndex',
        guide: 'editingGuideIndex',
        misc: 'editingMiscIndex',
        local: 'editingTransferIndex',
        arrivaldeparture: 'editingArrivalDepartureIndex'
    };
    window._enquiryProLastCityByContext = window._enquiryProLastCityByContext || {};

    function enquiryProIsEditingContext(key) {
        if (key === 'accommodation' && window._populatingAccommodationEdit) return true;
        const flag = ENQUIRY_PRO_EDIT_FLAGS[key];
        const value = flag ? window[flag] : null;
        return value !== null && value !== undefined && value !== false;
    }

    function enquiryProApplyRangeToInput(input, range, field, force) {
        const isDateOnly = input.type === 'date';
        input.min = isDateOnly ? range.start_date : range.start_date + 'T00:00';
        input.max = isDateOnly ? range.end_date : range.end_date + 'T23:59';

        const current = String(input.value || '');
        const currentDate = enquiryProDateOnly(current);
        const currentTime = current.split('T')[1] || field.time;
        const anchorDate = field.anchor === 'end' ? range.end_date : range.start_date;
        let targetDate = currentDate;
        if (force || !currentDate) targetDate = anchorDate;
        else if (currentDate < range.start_date) targetDate = range.start_date;
        else if (currentDate > range.end_date) targetDate = range.end_date;
        if (targetDate === currentDate) return;

        input.value = isDateOnly ? targetDate : targetDate + 'T' + currentTime.substring(0, 5);
    }

    function applyCityDateRangeToContext(city, context, options) {
        options = options || {};
        const key = String(context || '').toLowerCase();
        if (key === 'all') {
            applyOpenServiceCityDateRanges(options);
            return getCityDateRange(city);
        }
        const fields = ENQUIRY_PRO_CITY_DATE_FIELDS[key];
        const range = getCityDateRange(city);
        if (!range || !fields || !range.start_date || !range.end_date) return null;

        // Moving a service to another city must re-seat its dates on that city's window; clamping alone
        // would keep a shared boundary date (e.g. the previous city's check-out) and collapse the stay.
        const cityKey = serviceCityKey(city);
        const previousKey = window._enquiryProLastCityByContext[key];
        const cityChanged = previousKey !== undefined && previousKey !== cityKey;
        window._enquiryProLastCityByContext[key] = cityKey;
        const force = options.reset === true
            || (cityChanged && !enquiryProIsEditingContext(key));

        fields.forEach(function (field) {
            const input = document.getElementById(field.id);
            if (input) enquiryProApplyRangeToInput(input, range, field, force);
        });

        if (key === 'accommodation') {
            const checkIn = document.getElementById('checkInDate');
            const checkOut = document.getElementById('checkOutDate');
            if (checkIn && checkOut && checkIn.value && checkOut.value
                && enquiryProDateOnly(checkOut.value) <= enquiryProDateOnly(checkIn.value)) {
                const nextDay = enquiryProShiftDate(enquiryProDateOnly(checkIn.value), 1);
                const bounded = nextDay && nextDay <= range.end_date ? nextDay : range.end_date;
                checkOut.value = checkOut.type === 'date'
                    ? bounded
                    : bounded + 'T' + ((checkOut.value.split('T')[1] || '12:00').substring(0, 5));
            }
            // updateCheckOutMinDate() strips the max attribute, so restore the city ceiling afterwards.
            if (checkOut) checkOut.max = checkOut.type === 'date' ? range.end_date : range.end_date + 'T23:59';
            if (typeof calculateAccommodationNights === 'function') {
                try { calculateAccommodationNights(); } catch (e) { /* modal not ready */ }
            }
        }
        return range;
    }
    window.applyCityDateRangeToContext = applyCityDateRangeToContext;

    function applyOpenServiceCityDateRanges(options) {
        Object.keys(ENQUIRY_PRO_CITY_SELECT_BY_CONTEXT).forEach(function (context) {
            const city = document.getElementById(ENQUIRY_PRO_CITY_SELECT_BY_CONTEXT[context])?.value || '';
            if (city) applyCityDateRangeToContext(city, context, options);
        });
    }
    window.applyOpenServiceCityDateRanges = applyOpenServiceCityDateRanges;

    // Re-apply the window after the service modals' own change handlers have run.
    document.addEventListener('change', function (event) {
        const id = event.target?.id;
        if (!id || window._enquiryProReapplyingCityWindow) return;
        const context = Object.keys(ENQUIRY_PRO_CITY_DATE_FIELDS).find(function (key) {
            return ENQUIRY_PRO_CITY_DATE_FIELDS[key].some(function (field) { return field.id === id; });
        });
        if (!context) return;
        const citySelectId = ENQUIRY_PRO_CITY_SELECT_BY_CONTEXT[context] || ENQUIRY_PRO_CITY_SELECT_BY_CONTEXT.arrivaldeparture;
        const city = document.getElementById(citySelectId)?.value || '';
        if (!city) return;
        window._enquiryProReapplyingCityWindow = true;
        try { applyCityDateRangeToContext(city, context); }
        finally { window._enquiryProReapplyingCityWindow = false; }
    });

    // Service modals prefill their dates while opening, so align them with the city window once they are visible.
    function enquiryProQueueCityWindowSync() {
        setTimeout(function () { applyOpenServiceCityDateRanges(); }, 80);
    }
    document.addEventListener('shown.bs.modal', enquiryProQueueCityWindowSync);
    if (window.jQuery) {
        window.jQuery(document).on('shown.bs.modal', enquiryProQueueCityWindowSync);
    }

    function validateServiceDateForCity(city, startValue, endValue, serviceLabel) {
        const range = getCityDateRange(city);
        if (!range) return { valid: false, message: `Set the city dates for ${city || 'this city'} first.` };
        const start = enquiryProDateOnly(startValue);
        const end = enquiryProDateOnly(endValue || startValue);
        const valid = !!start && start >= range.start_date && end <= range.end_date && end >= start;
        return {
            valid: valid,
            message: valid ? '' : `${serviceLabel || 'Service'} for ${range.city} must be between ${range.start_date} and ${range.end_date}.`
        };
    }
    window.validateServiceDateForCity = validateServiceDateForCity;

    function ensureModalServiceDateWithinCity(cityInputId, startInputId, endInputId, serviceLabel) {
        const city = document.getElementById(cityInputId)?.value || '';
        const start = document.getElementById(startInputId)?.value || '';
        const end = endInputId ? (document.getElementById(endInputId)?.value || '') : start;
        const result = validateServiceDateForCity(city, start, end, serviceLabel);
        if (!result.valid) {
            alert(result.message);
            const input = document.getElementById(!start ? startInputId : (endInputId && !end ? endInputId : startInputId));
            if (input) input.focus();
            return false;
        }
        return true;
    }
    window.ensureModalServiceDateWithinCity = ensureModalServiceDateWithinCity;

    document.addEventListener('change', function (event) {
        const contextById = {
            hotelDestination: 'accommodation',
            tourDestination: 'tour',
            mealDestination: 'meal',
            guideDestination: 'guide',
            miscDestination: 'misc',
            localDestination: 'local',
            arrivalDepartureCity: 'arrivaldeparture'
        };
        const context = contextById[event.target?.id];
        if (!context) return;
        // A user picking another city always re-seats the dates; programmatic changes (edit population) only clamp.
        applyCityDateRangeToContext(event.target.value, context, { reset: event.isTrusted === true });
    });

    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () { syncCityDateRangePanel(); }, 0);
    });

    /** Find hotel-synced entry_port / exit_port for a city (empty city → first hotel-synced of that type). */
    function findHotelSyncedArrDep(list, travelType, cityName) {
        const key = serviceCityKey(cityName);
        const rows = Array.isArray(list) ? list : [];
        let fallback = null;
        let best = null;
        for (let i = 0; i < rows.length; i++) {
            const e = rows[i];
            if (!e) continue;
            const tt = e.travel_type;
            const ty = (e.type || '').toString();
            const matchesType = (tt === travelType)
                || ((!tt || tt === '') && (
                    (travelType === 'entry_port' && ty === 'Arrival')
                    || (travelType === 'exit_port' && ty === 'Departure')
                ));
            if (!matchesType) continue;
            // Prefer hotel-linked rows; still accept port rows missing sourceType (loaded edit data)
            if (e.sourceType && e.sourceType !== 'hotel' && e.sourceType !== 'standalone') {
                // allow through — some loaders omit sourceType
            }
            if (e.sourceType === 'standalone') continue;
            const eKey = serviceCityKey(e.city || e.destination || '');
            if (!key) {
                return e;
            }
            if (eKey && eKey === key) {
                // Prefer row with port/vehicle over blank placeholders
                if (!best || ((e.portId || e.portName) && !(best.portId || best.portName))) {
                    best = e;
                }
                continue;
            }
            if (!eKey && !fallback) fallback = e;
        }
        return best || (key ? null : fallback);
    }
    window.findHotelSyncedArrDep = findHotelSyncedArrDep;

    function escapeCssAttr(value) {
        const s = String(value ?? '');
        if (window.CSS && typeof CSS.escape === 'function') return CSS.escape(s);
        return s.replace(/\\/g, '\\\\').replace(/"/g, '\\"');
    }

    function setOptionCityVisibility(option, visible) {
        if (!option) return;
        option.hidden = !visible;
        option.disabled = !visible;
        option.style.display = visible ? '' : 'none';
        if (visible) {
            option.removeAttribute('disabled');
        } else {
            option.setAttribute('disabled', 'disabled');
        }
    }

    function refreshSelect2AfterFilter(select) {
        if (!select || typeof window.jQuery === 'undefined') return;
        const $sel = window.jQuery(select);
        if (!$sel.length || !$sel.hasClass('select2-hidden-accessible')) return;
        const val = $sel.val();
        const opt = select.querySelector('option[value="' + escapeCssAttr(String(val || '')) + '"]');
        if (val && opt && (opt.disabled || opt.hidden)) {
            $sel.val('').trigger('change');
        } else {
            $sel.trigger('change.select2');
        }
    }

    /** Case-insensitive city match; also matches first comma-separated segment (e.g. "Singapore, SG"). */
    function cityFieldMatches(fieldValue, cityNames) {
        const raw = String(fieldValue || '').trim().toLowerCase();
        if (!raw || !cityNames || !cityNames.length) return false;
        const parts = raw.split(',').map(function (p) { return p.trim(); }).filter(Boolean);
        return cityNames.some(function (c) {
            const n = String(c || '').trim().toLowerCase();
            if (!n) return false;
            return raw === n || parts.indexOf(n) !== -1;
        });
    }

    function countryFieldMatches(fieldValue, countries) {
        const raw = String(fieldValue || '').trim().toLowerCase();
        if (!raw || !countries || !countries.length) return false;
        return countries.some(function (c) {
            return String(c || '').trim().toLowerCase() === raw;
        });
    }

    function optionMatchesCityScope(option, cityNames, cityIds, countries) {
        return transferItemMatchesCityScope({
            type: (option.getAttribute('data-type') || '').toLowerCase(),
            city: option.getAttribute('data-city') || '',
            location: option.getAttribute('data-location') || '',
            country: option.getAttribute('data-country') || '',
            city_id: parseInt(option.getAttribute('data-city-id') || '0', 10) || 0
        }, cityNames, cityIds, countries);
    }

    /** City-scope match for catalog items and dropdown options. */
    function transferItemMatchesCityScope(item, cityNames, cityIds, countries) {
        if (!item) return false;
        const dataType = String(item.type || '').toLowerCase();
        const dataCountry = item.country || '';
        const dataLocation = item.location || '';
        const dataCity = item.city || '';
        const dataCityId = parseInt(item.city_id || '0', 10) || 0;
        const names = cityNames || [];
        const ids = cityIds || [];
        const countriesList = countries || [];

        if (dataType === 'port') {
            if (dataCityId && ids.length) return ids.includes(dataCityId);
            if (dataCity && names.length) return cityFieldMatches(dataCity, names);
            if (dataCountry && countriesList.length) return countryFieldMatches(dataCountry, countriesList);
            if (dataCountry && names.length) return cityFieldMatches(dataCountry, names);
            return false;
        }
        if (dataType === 'hotel' || dataType === 'restaurant') {
            if (dataCity && cityFieldMatches(dataCity, names)) return true;
            // City-state: country name equals city (Singapore / Singapore)
            if (dataCountry && cityFieldMatches(dataCountry, names)) return true;
            if (!dataCity && dataCountry && countryFieldMatches(dataCountry, countriesList)) return true;
            return false;
        }
        if (dataType === 'attraction') {
            if (dataLocation && cityFieldMatches(dataLocation, names)) return true;
            if (dataCountry && cityFieldMatches(dataCountry, names)) return true;
            if (!dataLocation && dataCountry && countryFieldMatches(dataCountry, countriesList)) return true;
            return false;
        }
        if (dataCity || dataLocation || dataCityId || dataCountry) {
            return cityFieldMatches(dataCity, names)
                || cityFieldMatches(dataLocation, names)
                || (dataCityId && ids.includes(dataCityId))
                || countryFieldMatches(dataCountry, countriesList)
                || cityFieldMatches(dataCountry, names);
        }
        return null;
    }

    function escapeHtmlAttr(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    /** Build Ports/Hotels/Attractions/Restaurants optgroups for one city (single-city dropoff logic). */
    function buildTransferDestinationOptionsHTML(forCity) {
        const catalog = window.transferDestinationCatalog || {};
        const city = String(forCity || '').trim();
        const cities = city ? [city] : (typeof getActiveServiceCityNames === 'function' ? getActiveServiceCityNames() : []);
        const cityIds = typeof getActiveServiceCityIds === 'function' ? getActiveServiceCityIds(cities) : [];
        const countries = typeof getActiveServiceCountries === 'function' ? getActiveServiceCountries(cities) : [];
        const filterOn = cities.length > 0;

        function keep(item, type) {
            if (!filterOn) return true;
            return transferItemMatchesCityScope(Object.assign({}, item, { type: type }), cities, cityIds, countries) === true;
        }

        let html = '';
        const ports = catalogItemsArray(catalog.ports).filter(function (p) { return keep(p, 'port'); });
        if (ports.length) {
            html += '<optgroup label="Ports">';
            ports.forEach(function (p) {
                html += '<option value="' + escapeHtmlAttr(p.id) + '" data-name="' + escapeHtmlAttr(p.name) + '" data-type="port" data-port-id="' + escapeHtmlAttr(p.id) + '" data-city-id="' + escapeHtmlAttr(p.city_id || '') + '" data-country="' + escapeHtmlAttr(p.country || '') + '">' + escapeHtmlAttr(p.name) + '</option>';
            });
            html += '</optgroup>';
        }

        const hotels = catalogItemsArray(catalog.hotels).filter(function (h) { return keep(h, 'hotel'); });
        if (hotels.length) {
            html += '<optgroup label="Hotels">';
            hotels.forEach(function (h) {
                html += '<option value="' + escapeHtmlAttr(h.id) + '" data-name="' + escapeHtmlAttr(h.name) + '" data-type="hotel" data-hotel-unique-id="' + escapeHtmlAttr(h.id) + '" data-zone-id="' + escapeHtmlAttr(h.zone_id || '') + '" data-city="' + escapeHtmlAttr(h.city || '') + '" data-country="' + escapeHtmlAttr(h.country || '') + '">' + escapeHtmlAttr(h.name) + '</option>';
            });
            html += '</optgroup>';
        }

        const attractions = catalogItemsArray(catalog.attractions).filter(function (a) { return keep(a, 'attraction'); });
        if (attractions.length) {
            html += '<optgroup label="Attractions">';
            attractions.forEach(function (a) {
                html += '<option value="' + escapeHtmlAttr(a.id) + '" data-name="' + escapeHtmlAttr(a.name) + '" data-type="attraction" data-attraction-id="' + escapeHtmlAttr(a.id) + '" data-zone-id="' + escapeHtmlAttr(a.zone_id || '') + '" data-location="' + escapeHtmlAttr(a.location || '') + '" data-country="' + escapeHtmlAttr(a.country || '') + '">' + escapeHtmlAttr(a.name) + '</option>';
            });
            html += '</optgroup>';
        }

        const restaurants = catalogItemsArray(catalog.restaurants).filter(function (r) { return keep(r, 'restaurant'); });
        if (restaurants.length) {
            html += '<optgroup label="Restaurants">';
            restaurants.forEach(function (r) {
                html += '<option value="' + escapeHtmlAttr(r.id) + '" data-name="' + escapeHtmlAttr(r.name) + '" data-type="restaurant" data-restaurant-id="' + escapeHtmlAttr(r.id) + '" data-zone-id="' + escapeHtmlAttr(r.zone_id || '') + '" data-city="' + escapeHtmlAttr(r.city || '') + '" data-country="' + escapeHtmlAttr(r.country || '') + '">' + escapeHtmlAttr(r.name) + '</option>';
            });
            html += '</optgroup>';
        }

        return html;
    }
    window.buildTransferDestinationOptionsHTML = buildTransferDestinationOptionsHTML;

    function catalogItemsArray(val) {
        if (!val) return [];
        if (Array.isArray(val)) return val;
        return Object.keys(val).map(function (k) { return val[k]; }).filter(Boolean);
    }

    function mergeTransferCatalogKey(key, items) {
        window.transferDestinationCatalog = window.transferDestinationCatalog || {
            ports: [], hotels: [], attractions: [], restaurants: []
        };
        const existing = catalogItemsArray(window.transferDestinationCatalog[key]);
        const byId = {};
        existing.forEach(function (item) {
            if (item && item.id != null && item.id !== '') byId[String(item.id)] = item;
        });
        (items || []).forEach(function (item) {
            if (item && item.id != null && item.id !== '') byId[String(item.id)] = item;
        });
        window.transferDestinationCatalog[key] = Object.keys(byId).map(function (k) { return byId[k]; });
    }

    /**
     * Ensure dropoff catalog has this city's hotels / attractions / restaurants
     * (same DMC sources as Tour/Meal modals — fixes ports-only lists).
     */
    async function ensureTransferCatalogForCity(city, extra) {
        const dest = String(city || '').trim();
        if (!dest) return;
        extra = extra || {};

        if (extra.attractions && extra.attractions.length) {
            mergeTransferCatalogKey('attractions', extra.attractions.map(function (a) {
                return {
                    id: a.id || a.attraction_id,
                    name: a.name,
                    location: a.location || dest,
                    country: a.country || '',
                    zone_id: a.zone_id || ''
                };
            }));
        }

        if (extra.restaurants && extra.restaurants.length) {
            mergeTransferCatalogKey('restaurants', extra.restaurants.map(function (r) {
                return {
                    id: r.restaurant_id || r.id,
                    name: r.name,
                    city: r.city || dest,
                    country: r.country || '',
                    zone_id: r.zone_id || ''
                };
            }));
        }

        // Hotels for this city (DMC-scoped API)
        try {
            const hotelsUrl = (typeof window.enquiryProGetHotelsUrl === 'string' && window.enquiryProGetHotelsUrl)
                ? window.enquiryProGetHotelsUrl
                : '/enquiry-form-pro/get-hotels';
            const res = await fetch(hotelsUrl + (hotelsUrl.indexOf('?') >= 0 ? '&' : '?') + 'destination=' + encodeURIComponent(dest));
            const data = await res.json();
            const list = (data && (data.hotels || data.data)) ? (data.hotels || data.data) : [];
            if (Array.isArray(list) && list.length) {
                mergeTransferCatalogKey('hotels', list.map(function (h) {
                    return {
                        id: h.hotel_unique_id || h.id,
                        name: h.name,
                        city: h.city || dest,
                        country: h.country || '',
                        zone_id: h.zone_id || ''
                    };
                }));
            }
        } catch (e) {
            console.warn('ensureTransferCatalogForCity hotels fetch failed', e);
        }

        // Restaurants already on page (allRestaurants / catalog) for this city
        try {
            const fromCatalog = catalogItemsArray((window.transferDestinationCatalog || {}).restaurants)
                .filter(function (r) {
                    return cityFieldMatches(r.city || '', [dest]) || cityFieldMatches(r.country || '', [dest]);
                });
            if (fromCatalog.length) mergeTransferCatalogKey('restaurants', fromCatalog);
        } catch (e) { /* ignore */ }
    }
    window.ensureTransferCatalogForCity = ensureTransferCatalogForCity;
    window.mergeTransferCatalogKey = mergeTransferCatalogKey;

    /** Pre-select default / accommodation hotel (single-city parity); user can still change dropoff. */
    function applyDefaultTransferDropoffHotel(selectEl, forCity) {
        if (!selectEl) return false;
        const city = String(forCity || '').trim();
        const hotelOptions = Array.from(selectEl.options).filter(function (o) {
            return o.getAttribute('data-type') === 'hotel' && o.value && !o.disabled && !o.hidden;
        });
        if (!hotelOptions.length) return false;

        let selectedHotel = null;
        const accommodationHotel = (typeof getHotelFromAccommodationList === 'function')
            ? getHotelFromAccommodationList()
            : null;

        if (accommodationHotel) {
            selectedHotel = hotelOptions.find(function (opt) {
                const hotelUniqueId = opt.getAttribute('data-hotel-unique-id') || opt.value;
                const zoneId = opt.getAttribute('data-zone-id');
                const hotelCity = opt.getAttribute('data-city') || '';
                const idMatch = String(hotelUniqueId) === String(accommodationHotel.hotel_unique_id) ||
                    (zoneId && String(zoneId) === String(accommodationHotel.zone_id)) ||
                    String(opt.value) === String(accommodationHotel.hotel_unique_id);
                if (!idMatch) return false;
                if (city && hotelCity && !cityFieldMatches(hotelCity, [city])) return false;
                return true;
            });
        }

        if (!selectedHotel && window.defaultValues && window.defaultValues.hotel) {
            selectedHotel = hotelOptions.find(function (o) {
                return String(o.value) === String(window.defaultValues.hotel) ||
                    String(o.getAttribute('data-hotel-unique-id') || '') === String(window.defaultValues.hotel);
            });
        }

        if (!selectedHotel) {
            hotelOptions.sort(function (a, b) {
                return (a.textContent || '').localeCompare(b.textContent || '');
            });
            selectedHotel = hotelOptions[0];
        }

        if (selectedHotel) {
            selectEl.value = selectedHotel.value;
            if (window.jQuery && jQuery(selectEl).hasClass('select2-hidden-accessible')) {
                jQuery(selectEl).val(selectedHotel.value).trigger('change');
            }
            return true;
        }
        return false;
    }
    window.applyDefaultTransferDropoffHotel = applyDefaultTransferDropoffHotel;

    function filterSelectOptionsByCityScope(select, cityNames) {
        if (!select) return;
        const names = cityNames || [];
        const cityIds = getActiveServiceCityIds(names);
        const countries = getActiveServiceCountries(names);
        const noCitiesSelected = names.length === 0;
        const currentValue = select.value;
        let touched = false;

        select.querySelectorAll('option').forEach(function (option) {
            if (!option.value) {
                setOptionCityVisibility(option, true);
                return;
            }
            const match = optionMatchesCityScope(option, names, cityIds, countries);
            if (match === null) return;
            touched = true;
            const visible = !noCitiesSelected && !!match;
            setOptionCityVisibility(option, visible);
            if (!visible && option.value === currentValue) {
                select.value = '';
            }
        });

        // Hide optgroups that have no visible options (cleaner dropoff lists)
        select.querySelectorAll('optgroup').forEach(function (group) {
            const hasVisible = Array.from(group.querySelectorAll('option')).some(function (o) {
                return o.value && !o.disabled && !o.hidden;
            });
            group.hidden = !hasVisible;
            if (hasVisible) {
                group.removeAttribute('disabled');
            } else {
                group.setAttribute('disabled', 'disabled');
            }
        });

        if (touched) refreshSelect2AfterFilter(select);
    }

    /**
     * Show only city-scoped ports / hotels / attractions / restaurants.
     * When an explicit preferred/hotel/A-D city is active, BOTH arrival and departure
     * ports use that city (fixes 2nd-hotel Batam still listing Kolkata ports).
     */
    function filterPortsBySelectedCountries(preferredCity) {
        const baseCities = getActiveServiceCityNames(preferredCity);
        const headerCities = (typeof selectedDestinations !== 'undefined' && Array.isArray(selectedDestinations) && selectedDestinations.length)
            ? selectedDestinations.map(function (c) { return String(c || '').trim(); }).filter(Boolean)
            : baseCities;
        const noCitiesSelected = headerCities.length === 0 && baseCities.length === 0;
        // Prefer explicit service city over full multi-city header list
        const scopeCities = baseCities.length ? baseCities : headerCities;

        const arrivalPort = document.getElementById('arrivalPort');
        const departurePort = document.getElementById('departurePort');
        const arrivalDest = document.getElementById('arrivalDestination');
        const departureDest = document.getElementById('departureDestination');

        if (arrivalPort) {
            const arrivalPortField = document.getElementById('arrivalPortField');
            if (arrivalPortField) arrivalPortField.style.display = noCitiesSelected ? 'none' : '';
            if (noCitiesSelected) arrivalPort.value = '';
        }
        if (departurePort) {
            const departurePortField = document.getElementById('departurePortField');
            if (departurePortField) departurePortField.style.display = noCitiesSelected ? 'none' : '';
            if (noCitiesSelected) departurePort.value = '';
        }

        filterSelectOptionsByCityScope(arrivalPort, scopeCities);
        filterSelectOptionsByCityScope(departurePort, scopeCities);

        // Drop-off / pickup: prefer selected port city, else active service city
        const arrivalCities = resolveCityNamesFromPortSelect('arrivalPort');
        filterSelectOptionsByCityScope(arrivalDest, arrivalCities.length ? arrivalCities : scopeCities);

        const departureCities = resolveCityNamesFromPortSelect('departurePort');
        filterSelectOptionsByCityScope(
            departureDest,
            departureCities.length ? departureCities : scopeCities
        );

        // Other transfer pickers use hotel/header scope
        const otherCities = scopeCities;
        ['localPickup', 'localDrop', 'hotelTransferDestination'].forEach(function (id) {
            filterSelectOptionsByCityScope(document.getElementById(id), otherCities);
        });

        // Attraction / restaurant modal dropoffs: use that modal's destination city when set
        const tourCity = String(document.getElementById('tourDestination')?.value || '').trim();
        const mealCity = String(document.getElementById('mealDestination')?.value || '').trim();
        if (tourCity) {
            filterModalTransferDestinationsByCity(tourCity, '.attraction-transfer-destination');
            document.querySelectorAll('.attraction-transfer-destination').forEach(function (sel) {
                if (!sel.value && typeof applyDefaultTransferDropoffHotel === 'function') {
                    applyDefaultTransferDropoffHotel(sel, tourCity);
                }
            });
        }
        if (mealCity) {
            filterModalTransferDestinationsByCity(mealCity, '#restaurantTransferDestination');
            const restDrop = document.getElementById('restaurantTransferDestination');
            if (restDrop && !restDrop.value && typeof applyDefaultTransferDropoffHotel === 'function') {
                applyDefaultTransferDropoffHotel(restDrop, mealCity);
            }
        }

        console.log('Filtered ports/services scopeCities:', scopeCities,
            'arrivalCities:', arrivalCities, 'departureCities:', departureCities);
    }

    /**
     * Scope attraction/restaurant transfer dropoffs to one city.
     * Rebuilds from transferDestinationCatalog every time (avoids stale cache / HTML parse bugs).
     */
    function filterModalTransferDestinationsByCity(cityName, selector) {
        const city = String(cityName || '').trim();
        const sel = selector || '.attraction-transfer-destination, #restaurantTransferDestination';
        const optionsHtml = buildTransferDestinationOptionsHTML(city);

        document.querySelectorAll(sel).forEach(function (select) {
            if (!select) return;
            const prevValue = select.value;
            select.innerHTML = '<option value="">Select Dropoff</option>' + optionsHtml;
            if (prevValue && select.querySelector('option[value="' + escapeCssAttr(prevValue) + '"]')) {
                select.value = prevValue;
            } else {
                select.value = '';
            }
        });
    }
    window.filterModalTransferDestinationsByCity = filterModalTransferDestinationsByCity;

    /**
     * Keep city destination pickers in sync with header cities.
     * Do NOT include arrivalDestination / departureDestination (those are hotels/ports).
     */
    function syncHeaderCitiesToServiceModals() {
        const cities = (typeof selectedDestinations !== 'undefined') ? [...selectedDestinations] : [];
        const modalIds = [
            'hotelDestination', 'tourDestination', 'guideDestination',
            'mealDestination', 'miscDestination', 'localDestination',
            'arrivalDepartureCity'
        ];

        modalIds.forEach(function (id) {
            const sel = document.getElementById(id);
            if (!sel) return;

            sel.querySelectorAll('option').forEach(function (opt) {
                if (!opt.value) {
                    setOptionCityVisibility(opt, true);
                    return;
                }
                setOptionCityVisibility(opt, cities.length === 0 || cities.includes(opt.value));
            });

            if (cities.length === 0) {
                sel.value = '';
            } else if (!sel.value || !cities.includes(sel.value)) {
                // Multi-city: default Destination to the first header city (e.g. Singapore)
                sel.value = cities[0];
                sel.dispatchEvent(new Event('change'));
            }
        });

        filterPortsBySelectedCountries();
    }

    function ensureHotelOptionInSelect(destSelect, hotelOpt, uniqueId) {
        if (!destSelect || !hotelOpt || !uniqueId) return null;
        let destOpt = destSelect.querySelector('option[data-type="hotel"][data-hotel-unique-id="' + escapeCssAttr(uniqueId) + '"]')
            || destSelect.querySelector('option[value="' + escapeCssAttr(uniqueId) + '"]');

        if (!destOpt) {
            destOpt = document.createElement('option');
            destOpt.value = uniqueId;
            destOpt.setAttribute('data-name', hotelOpt.getAttribute('data-hotel-name') || hotelOpt.textContent || '');
            destOpt.setAttribute('data-type', 'hotel');
            destOpt.setAttribute('data-hotel-unique-id', uniqueId);
            destOpt.setAttribute('data-zone-id', hotelOpt.getAttribute('data-zone-id') || '');
            destOpt.setAttribute(
                'data-city',
                hotelOpt.getAttribute('data-city') || document.getElementById('hotelDestination')?.value || ''
            );
            destOpt.setAttribute(
                'data-country',
                hotelOpt.getAttribute('data-country')
                    || resolveCountryForCity(document.getElementById('hotelDestination')?.value || '')
            );
            destOpt.textContent = hotelOpt.getAttribute('data-hotel-name') || hotelOpt.textContent || uniqueId;
            const hotelsGroup = Array.from(destSelect.querySelectorAll('optgroup')).find(function (g) {
                return /hotel/i.test(g.label || '');
            });
            (hotelsGroup || destSelect).appendChild(destOpt);
        }

        setOptionCityVisibility(destOpt, true);
        return destOpt;
    }

    /**
     * After hotel is chosen, set Arrival Drop Off + Departure Pickup to that hotel
     * (when they are in the same city scope) so zone price can resolve.
     */
    function syncArrivalDropOffToSelectedHotel() {
        const hotelSelect = document.getElementById('hotelSelect');
        if (!hotelSelect || !hotelSelect.value) return;

        const hotelOpt = hotelSelect.options[hotelSelect.selectedIndex];
        if (!hotelOpt) return;
        const uniqueId = hotelOpt.getAttribute('data-hotel-unique-id') || '';
        if (!uniqueId) return;

        const hotelCity = String(
            hotelOpt.getAttribute('data-city') || document.getElementById('hotelDestination')?.value || ''
        ).trim();

        const arrivalDest = document.getElementById('arrivalDestination');
        const departureDest = document.getElementById('departureDestination');

        const arrivalOpt = ensureHotelOptionInSelect(arrivalDest, hotelOpt, uniqueId);
        if (arrivalOpt && arrivalDest) {
            arrivalDest.value = arrivalOpt.value;
        }

        // Prefill departure pickup with same hotel when departure port city matches hotel city
        // (or departure port not chosen yet / same base city).
        const depPortCities = resolveCityNamesFromPortSelect('departurePort');
        const canSyncDeparture = !depPortCities.length
            || !hotelCity
            || depPortCities.includes(hotelCity);
        if (canSyncDeparture) {
            const depOpt = ensureHotelOptionInSelect(departureDest, hotelOpt, uniqueId);
            if (depOpt && departureDest) {
                departureDest.value = depOpt.value;
            }
        }

        if (typeof refreshArrivalTransferZonePrice === 'function') {
            if (window._suppressArrDepZoneRefresh) {
                window._pendingArrivalZoneRefresh = true;
            } else {
                refreshArrivalTransferZonePrice();
            }
        }
        if (typeof refreshDepartureTransferZonePrice === 'function') {
            if (window._suppressArrDepZoneRefresh) {
                window._pendingDepartureZoneRefresh = true;
            } else {
                refreshDepartureTransferZonePrice();
            }
        }
    }

    /** Select2 config that hides disabled/hidden city-filtered port options. */
    function enquiryProPortSelect2Options(extra) {
        const base = {
            placeholder: 'Search and select port',
            allowClear: true,
            width: '100%',
            templateResult: function (state) {
                if (!state.id) return state.text;
                if (state.element && (state.element.disabled || state.element.hidden)) {
                    return null;
                }
                return state.text;
            }
        };
        return Object.assign(base, extra || {});
    }

    /** Whether a vehicle <option> belongs to the selected service city. */
    function vehicleOptionMatchesCity(option, city) {
        if (!option || !city) return true;
        const vCity = String(option.getAttribute('data-city') || '').trim();
        // When a city filter is active, hide vehicles with no city (or a different city)
        if (!vCity) return false;
        if (typeof cityFieldMatches === 'function') {
            return cityFieldMatches(vCity, [city]);
        }
        return vCity.toLowerCase() === String(city).toLowerCase();
    }
    window.vehicleOptionMatchesCity = vehicleOptionMatchesCity;

    /** Resolve city for Arrival/Departure vehicle + default mapping. */
    function getArrivalDepartureServiceCity(preferredCity) {
        const preferred = String(preferredCity || '').trim();
        if (preferred) return preferred;

        const arrDepCity = String(document.getElementById('arrivalDepartureCity')?.value || '').trim();
        if (arrDepCity) return arrDepCity;

        const hotelDest = String(document.getElementById('hotelDestination')?.value || '').trim();
        if (hotelDest) return hotelDest;

        // Prefer city from currently selected dropoff/pickup hotel
        const dropHotel = document.getElementById('arrivalDestination');
        if (dropHotel && dropHotel.selectedIndex >= 0) {
            const opt = dropHotel.options[dropHotel.selectedIndex];
            if (opt && String(opt.getAttribute('data-type') || '') === 'hotel') {
                const c = String(opt.getAttribute('data-city') || '').trim();
                if (c) return c;
            }
        }
        const pickHotel = document.getElementById('departureDestination');
        if (pickHotel && pickHotel.selectedIndex >= 0) {
            const opt = pickHotel.options[pickHotel.selectedIndex];
            if (opt && String(opt.getAttribute('data-type') || '') === 'hotel') {
                const c = String(opt.getAttribute('data-city') || '').trim();
                if (c) return c;
            }
        }

        if (typeof selectedDestinations !== 'undefined' && selectedDestinations && selectedDestinations.length) {
            return String(selectedDestinations[0] || '').trim();
        }
        return '';
    }
    window.getArrivalDepartureServiceCity = getArrivalDepartureServiceCity;

    /** Preferred vehicle id from city-scoped defaults (private vs shared). */
    function getPreferredVehicleDefaultId(transferType) {
        const dv = window.defaultValues || {};
        if (String(transferType || '').toUpperCase() === 'P') {
            return String(dv.car_private || dv.car_shared || '');
        }
        return String(dv.car_shared || dv.car_private || '');
    }
    window.getPreferredVehicleDefaultId = getPreferredVehicleDefaultId;

    /** Pick default vehicle from already-filtered matching options. */
    function pickDefaultVehicleFromMatches(matchingVehicles, transferType) {
        if (!matchingVehicles || !matchingVehicles.length) return null;
        const preferredId = getPreferredVehicleDefaultId(transferType);
        if (preferredId) {
            const found = matchingVehicles.find(function (opt) {
                return String(opt.value) === preferredId;
            });
            if (found) return found;
        }
        return matchingVehicles[0];
    }
    window.pickDefaultVehicleFromMatches = pickDefaultVehicleFromMatches;

    /** Whether a guide <option> belongs to the selected service city. */
    function guideOptionMatchesCity(option, city) {
        if (!option || !city) return true;
        const gCity = String(option.getAttribute('data-city') || '').trim();
        if (!gCity) return false;
        if (typeof cityFieldMatches === 'function') {
            return cityFieldMatches(gCity, [city]);
        }
        return gCity.toLowerCase() === String(city).toLowerCase();
    }
    window.guideOptionMatchesCity = guideOptionMatchesCity;

    /** Filter a guide <select> to the given city; returns matching options. */
    function filterGuideSelectByCity(selectEl, city) {
        if (!selectEl || !selectEl.options) return [];
        const matching = [];
        Array.from(selectEl.options).forEach(function (opt) {
            if (!opt.value) {
                opt.disabled = false;
                opt.hidden = false;
                opt.style.display = '';
                return;
            }
            const ok = !city || guideOptionMatchesCity(opt, city);
            opt.disabled = !ok;
            opt.hidden = !ok;
            opt.style.display = ok ? '' : 'none';
            if (ok) matching.push(opt);
        });
        if (selectEl.value) {
            const stillValid = matching.some(function (o) {
                return String(o.value) === String(selectEl.value);
            });
            if (!stillValid) {
                selectEl.value = '';
                if (typeof jQuery !== 'undefined' && jQuery(selectEl).data('select2')) {
                    jQuery(selectEl).val('').trigger('change.select2');
                }
            }
        }
        return matching;
    }
    window.filterGuideSelectByCity = filterGuideSelectByCity;

    /**
     * City-filter Arrival/Departure guide dropdowns and auto-select city default guide.
     */
    function applyDefaultArrivalDepartureGuides(preferredCity) {
        const city = (typeof getArrivalDepartureServiceCity === 'function')
            ? getArrivalDepartureServiceCity(preferredCity)
            : String(preferredCity || '').trim();

        if (city && typeof window.resolveActiveDefaultValues === 'function') {
            window.resolveActiveDefaultValues(city);
        }

        const preferredId = String((window.defaultValues && window.defaultValues.guide) || '');
        const targets = [
            {
                selectId: 'arrivalGuide',
                checkboxId: 'arrivalGuideCheckbox',
                fieldsId: 'arrivalGuideFieldsRow',
                headerId: 'arrivalGuideHeaderRow',
                syncCounts: 'syncArrivalGuideCounts',
                refreshPrice: 'refreshArrivalGuidePriceDisplay'
            },
            {
                selectId: 'departureGuide',
                checkboxId: 'departureGuideCheckbox',
                fieldsId: 'departureGuideFieldsRow',
                headerId: 'departureGuideHeaderRow',
                syncCounts: 'syncDepartureGuideCounts',
                refreshPrice: 'refreshDepartureGuidePriceDisplay'
            }
        ];

        targets.forEach(function (t) {
            const select = document.getElementById(t.selectId);
            if (!select) return;

            const matching = filterGuideSelectByCity(select, city);
            let pick = null;
            if (preferredId) {
                pick = matching.find(function (o) {
                    return String(o.value) === preferredId;
                }) || null;
            }
            // If city default guide is missing, clear stale previous-city selection
            if (!pick) {
                if (select.value && !matching.some(function (o) { return String(o.value) === String(select.value); })) {
                    select.value = '';
                }
                return;
            }

            select.value = pick.value;
            const checkbox = document.getElementById(t.checkboxId);
            const fieldsRow = document.getElementById(t.fieldsId);
            const headerRow = document.getElementById(t.headerId);
            if (checkbox) checkbox.checked = true;
            if (fieldsRow) fieldsRow.style.display = 'block';
            if (headerRow) headerRow.style.display = 'block';

            if (typeof jQuery !== 'undefined' && jQuery(select).data('select2')) {
                jQuery(select).val(pick.value).trigger('change').trigger('change.select2');
            } else {
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }

            if (typeof window[t.syncCounts] === 'function') window[t.syncCounts]();
            if (typeof window[t.refreshPrice] === 'function') window[t.refreshPrice]();
        });
    }
    window.applyDefaultArrivalDepartureGuides = applyDefaultArrivalDepartureGuides;

    /** Filter Tour Details (.attraction-guide-select) guides by destination city + apply default. */
    function filterAttractionGuidesByCity(city, root) {
        city = String(city
            || document.getElementById('tourDestination')?.value
            || '').trim();

        if (city && typeof window.resolveActiveDefaultValues === 'function') {
            window.resolveActiveDefaultValues(city);
        }

        const preferredId = String((window.defaultValues && window.defaultValues.guide) || '');
        const scope = root && root.querySelectorAll ? root : document;

        scope.querySelectorAll('.attraction-guide-select').forEach(function (select) {
            const matching = filterGuideSelectByCity(select, city);
            if (!preferredId) return;
            const pick = matching.find(function (o) {
                return String(o.value) === preferredId;
            });
            if (pick) {
                select.value = pick.value;
            }
        });
    }
    window.filterAttractionGuidesByCity = filterAttractionGuidesByCity;

    /** Re-init Select2 on guide Location so only city-visible options appear. */
    function reinitGuideLocationSelect2() {
        if (typeof jQuery === 'undefined' || !jQuery.fn.select2) return;
        const $el = jQuery('#guideLocation');
        if (!$el.length) return;
        const $modal = jQuery('#guideModal');
        const val = $el.val();
        if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy');
        }
        $el.select2({
            placeholder: 'Select Location',
            allowClear: true,
            width: '100%',
            dropdownParent: $modal.length ? $modal : jQuery(document.body),
            // Native <select> often still lists disabled options; hide them in Select2 UI
            templateResult: function (data) {
                if (!data.id) return data.text;
                const el = data.element;
                if (el && (el.disabled || el.hidden || el.style.display === 'none')) {
                    return null;
                }
                return data.text;
            }
        });
        if (val) {
            const escaped = (typeof escapeCssAttr === 'function')
                ? escapeCssAttr(String(val))
                : String(val).replace(/\\/g, '\\\\').replace(/"/g, '\\"');
            const opt = $el.find('option[value="' + escaped + '"]')[0];
            if (opt && !opt.disabled && !opt.hidden) {
                $el.val(val).trigger('change.select2');
            } else {
                $el.val('').trigger('change.select2');
            }
        } else {
            $el.val('').trigger('change.select2');
        }
    }
    window.reinitGuideLocationSelect2 = reinitGuideLocationSelect2;

    /**
     * Guide modal Location (#guideLocation): show Attractions / Restaurants / Ports for selected city only.
     */
    function filterGuideLocationByCity(preferredCity) {
        const select = document.getElementById('guideLocation');
        if (!select) return;

        const city = String(preferredCity
            || document.getElementById('guideDestination')?.value
            || '').trim();
        const cityNames = city ? [city] : [];
        const cityIds = (typeof getActiveServiceCityIds === 'function')
            ? getActiveServiceCityIds(cityNames)
            : [];
        const countries = (typeof getActiveServiceCountries === 'function')
            ? getActiveServiceCountries(cityNames)
            : [];

        const prev = String(select.value || '');

        Array.from(select.options).forEach(function (opt) {
            if (!opt.value) {
                setOptionCityVisibility(opt, true);
                return;
            }
            // No city selected → hide catalog until destination is chosen
            const match = !!city && (typeof optionMatchesCityScope === 'function'
                ? optionMatchesCityScope(opt, cityNames, cityIds, countries)
                : true);
            const ok = !!match;
            setOptionCityVisibility(opt, ok);
        });

        Array.from(select.querySelectorAll('optgroup')).forEach(function (group) {
            const anyVisible = Array.from(group.querySelectorAll('option')).some(function (o) {
                return o.value && !o.disabled && !o.hidden && o.style.display !== 'none';
            });
            group.hidden = !anyVisible;
            group.disabled = !anyVisible;
            group.style.display = anyVisible ? '' : 'none';
        });

        const prevStillVisible = prev && Array.from(select.options).some(function (o) {
            return String(o.value) === prev && !o.disabled && !o.hidden;
        });
        select.value = prevStillVisible ? prev : '';

        if (typeof reinitGuideLocationSelect2 === 'function') {
            reinitGuideLocationSelect2();
        } else if (typeof refreshSelect2AfterFilter === 'function') {
            refreshSelect2AfterFilter(select);
        }
    }
    window.filterGuideLocationByCity = filterGuideLocationByCity;

    /** Hide vehicle <optgroup>s that have no visible options. */
    function hideEmptyVehicleOptgroups(selectEl) {
        if (!selectEl) return;
        Array.from(selectEl.querySelectorAll('optgroup')).forEach(function (group) {
            const anyVisible = Array.from(group.querySelectorAll('option')).some(function (o) {
                return !o.disabled && !o.hidden && o.style.display !== 'none';
            });
            group.hidden = !anyVisible;
            group.disabled = !anyVisible;
            group.style.display = anyVisible ? '' : 'none';
        });
    }
    window.hideEmptyVehicleOptgroups = hideEmptyVehicleOptgroups;

    /**
     * Meal Details modal: city-filter vehicles + guides for mealDestination, apply defaults.
     */
    function applyMealModalCityFilters(preferredCity) {
        const city = String(preferredCity
            || document.getElementById('mealDestination')?.value
            || '').trim();

        if (city && typeof window.resolveActiveDefaultValues === 'function') {
            window.resolveActiveDefaultValues(city);
        }

        if (typeof filterRestaurantVehiclesByServiceType === 'function') {
            filterRestaurantVehiclesByServiceType();
        }

        const guideSelect = document.getElementById('restaurantGuideSelect');
        if (!guideSelect || typeof filterGuideSelectByCity !== 'function') return;

        const matching = filterGuideSelectByCity(guideSelect, city);
        const preferredId = String((window.defaultValues && window.defaultValues.guide) || '');
        const checkbox = document.getElementById('restaurantGuideCheckbox');
        const details = document.getElementById('restaurantGuideDetailsSection');

        const pick = preferredId
            ? (matching.find(function (o) { return String(o.value) === preferredId; }) || null)
            : null;

        if (pick) {
            guideSelect.value = pick.value;
            if (checkbox) checkbox.checked = true;
            if (details) details.style.display = 'block';
            if (typeof updateRestaurantGuidePricing === 'function') {
                updateRestaurantGuidePricing();
            }
        }
    }
    window.applyMealModalCityFilters = applyMealModalCityFilters;

    /** Re-init Select2 on local pickup/drop after options rebuild. */
    function reinitLocalPickupDropSelect2() {
        if (typeof jQuery === 'undefined' || !jQuery.fn.select2) return;
        const $modal = jQuery('#transferModal');
        ['#localPickup', '#localDrop'].forEach(function (sel) {
            const $el = jQuery(sel);
            if (!$el.length) return;
            const val = $el.val();
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }
            $el.select2({
                placeholder: sel === '#localPickup'
                    ? 'Search and select pickup location'
                    : 'Search and select drop location',
                allowClear: true,
                width: '100%',
                dropdownParent: $modal.length ? $modal : jQuery(document.body)
            });
            if (val) $el.val(val).trigger('change.select2');
        });
    }
    window.reinitLocalPickupDropSelect2 = reinitLocalPickupDropSelect2;

    /**
     * Local Transfer modal: filter pickup/drop/vehicle/guide by city + apply defaults.
     */
    function applyLocalTransferCityFilters(preferredCity, options) {
        const opts = options || {};
        const city = String(preferredCity
            || document.getElementById('localDestination')?.value
            || '').trim();

        if (city && typeof window.resolveActiveDefaultValues === 'function') {
            window.resolveActiveDefaultValues(city);
        }

        Promise.resolve(
            city && typeof ensureTransferCatalogForCity === 'function'
                ? ensureTransferCatalogForCity(city)
                : null
        ).then(function () {
            if (typeof filterModalTransferDestinationsByCity === 'function') {
                filterModalTransferDestinationsByCity(city, '#localPickup, #localDrop');
            }
            reinitLocalPickupDropSelect2();

            if (!opts.skipDefaults && city && typeof applyDefaultTransferDropoffHotel === 'function') {
                const dropEl = document.getElementById('localDrop');
                applyDefaultTransferDropoffHotel(dropEl, city);
                if (dropEl && dropEl.value && typeof jQuery !== 'undefined') {
                    jQuery('#localPickup').val(dropEl.value).trigger('change');
                }
            }

            if (typeof filterLocalTransferVehiclesByServiceType === 'function') {
                filterLocalTransferVehiclesByServiceType();
            }

            const guideSelect = document.getElementById('localTransportGuideSelect');
            if (guideSelect && typeof filterGuideSelectByCity === 'function') {
                const matching = filterGuideSelectByCity(guideSelect, city);
                const preferredId = String((window.defaultValues && window.defaultValues.guide) || '');
                const checkbox = document.getElementById('localTransportGuideCheckbox');
                const details = document.getElementById('localTransportGuideDetailsSection');
                const pick = preferredId
                    ? (matching.find(function (o) { return String(o.value) === preferredId; }) || null)
                    : null;
                if (pick && !opts.skipGuideDefault) {
                    guideSelect.value = pick.value;
                    if (checkbox) checkbox.checked = true;
                    if (details) details.style.display = 'block';
                    if (typeof updateLocalTransportGuidePricing === 'function') {
                        updateLocalTransportGuidePricing();
                    }
                }
            }
        });
    }
    window.applyLocalTransferCityFilters = applyLocalTransferCityFilters;

    function onLocalDestinationChanged() {
        applyLocalTransferCityFilters(document.getElementById('localDestination')?.value || '');
    }
    window.onLocalDestinationChanged = onLocalDestinationChanged;

    /**
     * Clear A/D selects that are out of city scope (or clear all when forceAll).
     * Used when switching hotel city (Kolkata → Batam) so stale values cannot stick.
     */
    function clearArrivalDepartureSelectionsOutOfCity(city, forceAll) {
        const cityNames = city ? [String(city).trim()] : [];
        const cityIds = cityNames.length && typeof getActiveServiceCityIds === 'function'
            ? getActiveServiceCityIds(cityNames) : [];
        const countries = cityNames.length && typeof getActiveServiceCountries === 'function'
            ? getActiveServiceCountries(cityNames) : [];

        function clearSelect(id, isPort) {
            const el = document.getElementById(id);
            if (!el) return;
            const opt = el.selectedOptions && el.selectedOptions[0];
            let keep = false;
            if (!forceAll && opt && opt.value && cityNames.length) {
                if (typeof optionMatchesCityScope === 'function') {
                    keep = !!optionMatchesCityScope(opt, cityNames, cityIds, countries);
                } else if (typeof vehicleOptionMatchesCity === 'function' && (id.indexOf('Vehicle') >= 0)) {
                    keep = vehicleOptionMatchesCity(opt, cityNames[0]);
                } else if (typeof guideOptionMatchesCity === 'function' && (id.indexOf('Guide') >= 0)) {
                    keep = guideOptionMatchesCity(opt, cityNames[0]);
                }
            }
            if (keep) return;
            el.value = '';
            if (typeof jQuery !== 'undefined' && jQuery(el).hasClass('select2-hidden-accessible')) {
                jQuery(el).val(null).trigger('change');
            }
        }

        clearSelect('arrivalPort', true);
        clearSelect('departurePort', true);
        clearSelect('arrivalDestination', false);
        clearSelect('departureDestination', false);
        clearSelect('arrivalVehicleType', false);
        clearSelect('departureVehicleType', false);
        clearSelect('arrivalGuide', false);
        clearSelect('departureGuide', false);
    }
    window.clearArrivalDepartureSelectionsOutOfCity = clearArrivalDepartureSelectionsOutOfCity;

    /**
     * Arrival/Departure modal: filter ports / drop-off / pickup / vehicles / guides
     * by City select, then apply city Default Product Mapping.
     */
    function applyArrivalDepartureCityFilters(preferredCity, options) {
        const opts = options || {};
        const city = String(preferredCity
            || document.getElementById('arrivalDepartureCity')?.value
            || document.getElementById('hotelDestination')?.value
            || '').trim();

        const citySel = document.getElementById('arrivalDepartureCity');
        // Always sync City select to the active hotel/service city (2nd hotel Batam fix)
        if (citySel && city && citySel.value !== city) {
            citySel.value = city;
        }

        if (city && typeof window.resolveActiveDefaultValues === 'function') {
            window.resolveActiveDefaultValues(city);
        }

        // Drop stale Kolkata values before re-filtering for Batam
        if (!opts.skipDefaults && city) {
            clearArrivalDepartureSelectionsOutOfCity(city, !!opts.forceClear);
        }

        if (typeof filterPortsBySelectedCountries === 'function') {
            filterPortsBySelectedCountries(city);
        }

        // Mark city so vehicle filters force re-pick defaults
        window._arrDepVehicleForceCity = city;

        if (typeof filterArrivalVehiclesByServiceType === 'function') {
            filterArrivalVehiclesByServiceType(city);
        }
        if (typeof filterDepartureVehiclesByServiceType === 'function') {
            filterDepartureVehiclesByServiceType(city);
        }

        if (!opts.skipDefaults && city && typeof applyArrivalDepartureDefaults === 'function') {
            if (!window._applyingArrDepCityDefaults) {
                window._applyingArrDepCityDefaults = true;
                try {
                    applyArrivalDepartureDefaults(city);
                } finally {
                    window._applyingArrDepCityDefaults = false;
                }
            }
        } else if (city && typeof applyDefaultArrivalDepartureGuides === 'function') {
            applyDefaultArrivalDepartureGuides(city);
        }

        window._arrDepVehicleForceCity = '';

        setTimeout(function () {
            if (window._suppressArrDepZoneRefresh) {
                window._pendingArrivalZoneRefresh = true;
                window._pendingDepartureZoneRefresh = true;
                return;
            }
            if (typeof scheduleArrivalZonePriceRefresh === 'function') {
                scheduleArrivalZonePriceRefresh();
            } else if (typeof refreshArrivalTransferZonePrice === 'function') {
                refreshArrivalTransferZonePrice();
            }
            if (typeof scheduleDepartureZonePriceRefresh === 'function') {
                scheduleDepartureZonePriceRefresh();
            } else if (typeof refreshDepartureTransferZonePrice === 'function') {
                refreshDepartureTransferZonePrice();
            }
        }, 120);
    }
    window.applyArrivalDepartureCityFilters = applyArrivalDepartureCityFilters;

    function onArrivalDepartureCityChanged() {
        applyArrivalDepartureCityFilters(document.getElementById('arrivalDepartureCity')?.value || '', { forceClear: true });
    }
    window.onArrivalDepartureCityChanged = onArrivalDepartureCityChanged;

    /**
     * Resolve city-scoped Default Product Mapping and apply to open service modals.
     * context: accommodation | tour | meal | guide | local | all
     */
    function onServiceCityChanged(city, context) {
        city = String(city || '').trim();
        if (!city || window._skipCityDefaults) {
            return (typeof window.resolveActiveDefaultValues === 'function')
                ? window.resolveActiveDefaultValues(city)
                : (window.defaultValues || {});
        }

        const defaults = (typeof window.resolveActiveDefaultValues === 'function')
            ? window.resolveActiveDefaultValues(city)
            : (window.defaultValues || {});

        if (typeof filterPortsBySelectedCountries === 'function') {
            filterPortsBySelectedCountries(city);
        }

        const ctx = String(context || 'all').toLowerCase();
        if (typeof applyCityDateRangeToContext === 'function') {
            applyCityDateRangeToContext(city, ctx);
        }
        const isEditingAccommodation = window.editingAccommodationIndex !== null
            && window.editingAccommodationIndex !== undefined;
        const skipArrDep = !!window._populatingAccommodationEdit
            || (!!isEditingAccommodation && !window.isAddingNewArrivalDeparture && !window.isArrivalDepartureOnlyMode);

        if ((ctx === 'accommodation' || ctx === 'arrival' || ctx === 'departure' || ctx === 'all')
            && !skipArrDep) {
            const arrDepCitySel = document.getElementById('arrivalDepartureCity');
            // Always overwrite City when hotel/service city changes (Kolkata → Batam)
            if (arrDepCitySel && city) {
                arrDepCitySel.value = city;
            }
            if (typeof applyArrivalDepartureCityFilters === 'function') {
                applyArrivalDepartureCityFilters(city, { forceClear: true });
            } else if (typeof applyArrivalDepartureDefaults === 'function') {
                applyArrivalDepartureDefaults(city);
            }
        }

        if ((ctx === 'tour' || ctx === 'all') && typeof applyDefaultTransferDropoffHotel === 'function') {
            document.querySelectorAll('.attraction-transfer-destination').forEach(function (sel) {
                applyDefaultTransferDropoffHotel(sel, city);
            });
        }

        if ((ctx === 'tour' || ctx === 'all') && typeof filterAttractionGuidesByCity === 'function') {
            filterAttractionGuidesByCity(city);
        }

        if ((ctx === 'guide' || ctx === 'all') && typeof filterGuideLocationByCity === 'function') {
            filterGuideLocationByCity(city);
        }

        if ((ctx === 'meal' || ctx === 'all') && typeof applyDefaultTransferDropoffHotel === 'function') {
            applyDefaultTransferDropoffHotel(document.getElementById('restaurantTransferDestination'), city);
        }

        if ((ctx === 'meal' || ctx === 'all') && typeof applyMealModalCityFilters === 'function') {
            applyMealModalCityFilters(city);
        }

        if ((ctx === 'local' || ctx === 'all') && typeof applyLocalTransferCityFilters === 'function') {
            applyLocalTransferCityFilters(city, { skipDefaults: ctx === 'all' });
        }

        return defaults;
    }
    window.onServiceCityChanged = onServiceCityChanged;

    /** Enhance dropoff defaulting to always resolve city-scoped hotel first. */
    const _applyDefaultTransferDropoffHotelOrig = applyDefaultTransferDropoffHotel;
    applyDefaultTransferDropoffHotel = function (selectEl, forCity) {
        const city = String(forCity || '').trim();
        if (city && typeof window.resolveActiveDefaultValues === 'function') {
            window.resolveActiveDefaultValues(city);
        }
        return _applyDefaultTransferDropoffHotelOrig(selectEl, forCity);
    };
    window.applyDefaultTransferDropoffHotel = applyDefaultTransferDropoffHotel;
