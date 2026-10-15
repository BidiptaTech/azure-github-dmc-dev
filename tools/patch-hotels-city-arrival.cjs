/**
 * UI-only: city-scoped hotels + multi-city arrival/departure labels.
 * Run: node tools/patch-hotels-city-arrival.cjs
 */
const fs = require('fs');
const path = require('path');

const file = path.join(__dirname, '..', 'resources', 'views', 'day-level', 'create.blade.php');
let src = fs.readFileSync(file, 'utf8');
const backup = file + '.pre-hotels-city.bak';
if (!fs.existsSync(backup)) {
  fs.writeFileSync(backup, src);
  console.log('backup →', backup);
}

function mustReplace(label, from, to) {
  if (!src.includes(from)) {
    console.error('MISSING:', label);
    process.exit(1);
  }
  src = src.replace(from, to);
  console.log('ok:', label);
}

function mustInsertAfter(label, marker, insert) {
  const i = src.indexOf(marker);
  if (i < 0) {
    console.error('MISSING MARKER:', label);
    process.exit(1);
  }
  if (src.includes(insert.slice(0, 80))) {
    console.log('skip (already):', label);
    return;
  }
  src = src.slice(0, i + marker.length) + insert + src.slice(i + marker.length);
  console.log('ok insert:', label);
}

// --- CSS ---
mustInsertAfter(
  'hotel city css',
  '        .dl-svc-city-card__body { padding: 0.85rem 1rem 1rem; }\n',
  `
        .hotels-form-park {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
            padding: 0;
            margin: -1px;
        }
        .dl-hotel-city-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 0.15rem 0.5rem rgba(15, 23, 42, 0.045);
            margin-bottom: 1rem;
            overflow: hidden;
        }
        .dl-hotel-city-card__head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem 1rem;
            padding: 0.75rem 1rem;
            background: #f8fafc;
            border-bottom: 1px solid #eef2f7;
        }
        .dl-hotel-city-card__title {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 750;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #0f172a;
        }
        .dl-hotel-city-card__meta {
            margin: 0.15rem 0 0;
            font-size: 0.78rem;
            color: #64748b;
        }
        .dl-hotel-city-card__nights {
            padding: 0.28rem 0.65rem;
            border-radius: 999px;
            background: #ecfdf5;
            color: #047857;
            font-size: 0.72rem;
            font-weight: 650;
        }
        .dl-hotel-city-card__body { padding: 0.85rem 1rem 1rem; }
        .dl-hotel-city-card__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }
        .city-hotel-form-slot:empty { display: none; }
        .city-hotel-form-slot:not(:empty) {
            margin-bottom: 0.85rem;
            padding: 0.85rem;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #f8fafc;
        }
        .hotel-city-field.is-locked .select2-container {
            pointer-events: none;
            opacity: 0.85;
        }
`
);

// --- Hotels HTML: park form, city blocks host ---
const hotelsStart = src.indexOf('<div class="col-12">\n                        <div class="card sketch-card hotels-section">');
const hotelsEndMarker = '                    <div class="col-12">\n                        <div class="card sketch-card attraction-day-section border-0">';
const hotelsEnd = src.indexOf(hotelsEndMarker);
if (hotelsStart < 0 || hotelsEnd < 0) {
  console.error('hotels section markers missing', hotelsStart, hotelsEnd);
  process.exit(1);
}

const hotelsOld = src.slice(hotelsStart, hotelsEnd);

// Extract form body inner (from Stay details through Add Hotel button panel) — keep IDs
const formBodyStart = hotelsOld.indexOf('<div class="card-body hotels-form-body">');
const tableWrapStart = hotelsOld.indexOf('<div class="table-responsive modern-table-wrap hotels-compact-table-wrap">');
if (formBodyStart < 0 || tableWrapStart < 0) {
  console.error('form/table markers missing');
  process.exit(1);
}

// form panels only (exclude outer card-body open and table)
const formInner = hotelsOld.slice(
  formBodyStart + '<div class="card-body hotels-form-body">'.length,
  tableWrapStart
).trim();

const hotelsNew = `                    <div class="col-12">
                        <div class="card sketch-card hotels-section">
                            <div class="card-header modern-section-header d-flex align-items-center gap-2">
                                <div class="section-header-icon section-header-icon--muted">
                                    <i class="ri-hotel-line"></i>
                                </div>
                                <div>
                                    <strong class="d-block">Hotels</strong>
                                    <span class="section-subtitle">One stay per city — Singapore Day 1→6, Batam Day 7→10</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="hotelCityBlocks"></div>
                            </div>
                        </div>
                    </div>

                    <div id="hotelsFormPark" class="hotels-form-park" aria-hidden="true">
                        <div id="hotelsFormBody" class="hotels-form-body">
                                ${formInner}
                                <div class="hotels-form-panel">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-lg-3 col-md-4">
                                            <label class="form-label d-none d-md-block">&nbsp;</label>
                                            <button type="button" class="btn btn-outline-secondary w-100" onclick="parkHotelForm()">Close</button>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>

`;

// The formInner already ends with Add Hotel panel — we may have duplicated Close. Check.
// formInner includes the priority + Add Hotel panel. We appended another panel with Close.
// Better: add Close next to Add Hotel via replace in formInner.

let formInnerFixed = formInner;
if (!formInnerFixed.includes('parkHotelForm()')) {
  formInnerFixed = formInnerFixed.replace(
    `<button type="button" class="btn btn-outline-primary w-100 hotels-add-btn" id="hotelAddBtn" onclick="addHotel()">Add Hotel</button>
                                    </div>
                                    </div>
                                </div>`,
    `<button type="button" class="btn btn-outline-primary w-100 hotels-add-btn" id="hotelAddBtn" onclick="addHotel()">Add Hotel</button>
                                    </div>
                                    <div class="col-lg-3 col-md-4">
                                        <label class="form-label d-none d-md-block">&nbsp;</label>
                                        <button type="button" class="btn btn-outline-secondary w-100" onclick="parkHotelForm()">Close</button>
                                    </div>
                                    </div>
                                </div>`
  );
}

const hotelsNew2 = `                    <div class="col-12">
                        <div class="card sketch-card hotels-section">
                            <div class="card-header modern-section-header d-flex align-items-center gap-2">
                                <div class="section-header-icon section-header-icon--muted">
                                    <i class="ri-hotel-line"></i>
                                </div>
                                <div>
                                    <strong class="d-block">Hotels</strong>
                                    <span class="section-subtitle">One stay per city — Singapore Day 1→6, Batam Day 7→10</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="hotelCityBlocks"></div>
                            </div>
                        </div>
                    </div>

                    <div id="hotelsFormPark" class="hotels-form-park" aria-hidden="true">
                        <div id="hotelsFormBody" class="hotels-form-body">
                                ${formInnerFixed}
                        </div>
                    </div>

`;

src = src.slice(0, hotelsStart) + hotelsNew2 + src.slice(hotelsEnd);
console.log('ok: hotels HTML restructure');

// Lock city field visually when opened from a city card — mark hotel_city_select col
src = src.replace(
  '<div class="col-lg-3 col-md-6">\n                                            <label class="form-label" for="hotel_city_select">City</label>',
  '<div class="col-lg-3 col-md-6 hotel-city-field" id="hotel_city_field_wrap">\n                                            <label class="form-label" for="hotel_city_select">City</label>'
);
console.log('ok: hotel city field wrap');

// --- JS: activeHotelCityKey state ---
mustInsertAfter(
  'activeHotelCityKey',
  '        let hotelRoomsCache = [];\n',
  '        let activeHotelCityKey = null;\n'
);

// --- Helper: hotels for city group ---
const hotelFns = `
        function hotelBelongsToCityGroup(row, group) {
            if (!row || !group) return false;
            const rowCity = normalizeCityNameKey(row.city_name);
            const groupCity = normalizeCityNameKey(group.city_name);
            if (rowCity && groupCity && rowCity === groupCity) return true;
            const d = parseInt(String(row.day || 0), 10) || 0;
            return d >= group.day_in && d <= group.day_out;
        }

        function parkHotelForm() {
            const park = document.getElementById('hotelsFormPark');
            const body = document.getElementById('hotelsFormBody');
            if (!park || !body) return;
            if (body.parentElement !== park) {
                try {
                    $(body).find('select.searchable-select').each(function () {
                        if ($(this).data('select2')) $(this).select2('destroy');
                    });
                } catch (e) { /* ignore */ }
                park.appendChild(body);
                initSearchableSelects(body);
            }
            document.querySelectorAll('.city-hotel-form-slot').forEach((el) => {
                el.innerHTML = '';
            });
            activeHotelCityKey = null;
            const cityWrap = document.getElementById('hotel_city_field_wrap');
            if (cityWrap) cityWrap.classList.remove('is-locked');
        }

        function openCityHotelForm(cityKey) {
            const blocks = document.getElementById('hotelCityBlocks');
            if (!blocks) return;
            const card = blocks.querySelector('.dl-hotel-city-card[data-city-key="' + cityKey + '"]');
            const slot = card ? card.querySelector('.city-hotel-form-slot') : null;
            const body = document.getElementById('hotelsFormBody');
            if (!slot || !body) return;
            parkHotelForm();
            try {
                $(body).find('select.searchable-select').each(function () {
                    if ($(this).data('select2')) $(this).select2('destroy');
                });
            } catch (e) { /* ignore */ }
            slot.appendChild(body);
            initSearchableSelects(body);
            activeHotelCityKey = cityKey;
            const groups = getItineraryCityGroupsForUi();
            const group = groups.find((g) => String(g.key) === String(cityKey));
            if (group) {
                const citySelect = document.getElementById('hotel_city_select');
                const match = citySelect
                    ? Array.from(citySelect.options).find((opt) => {
                        const nm = normalizeCityNameKey(opt.dataset.name || opt.textContent);
                        return nm === normalizeCityNameKey(group.city_name);
                    })
                    : null;
                if (match) {
                    safeSetSelectValue('hotel_city_select', match.value);
                    syncHotelDayDropdownWithMultiCity();
                    if (!isHydratingDayServices && !isPrefillingHotelForm) {
                        loadHotelCityServices();
                    }
                }
                const cityWrap = document.getElementById('hotel_city_field_wrap');
                if (cityWrap) cityWrap.classList.add('is-locked');
            }
            slot.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function renderHotelCityBlocks() {
            const wrap = document.getElementById('hotelCityBlocks');
            if (!wrap) return;
            const wasKey = activeHotelCityKey;
            const formWasOpen = !!(document.getElementById('hotelsFormBody')?.closest('.city-hotel-form-slot'));
            parkHotelForm();
            const groups = getItineraryCityGroupsForUi();
            wrap.innerHTML = groups.map((group) => {
                const rowsId = 'hotelRows_' + group.key;
                return \`
                    <div class="dl-hotel-city-card" data-city-key="\${escapeHtml(group.key)}" data-city-name="\${escapeHtml(group.city_name)}">
                        <div class="dl-hotel-city-card__head">
                            <div>
                                <h3 class="dl-hotel-city-card__title">\${escapeHtml(group.city_name)}</h3>
                                <p class="dl-hotel-city-card__meta">Day \${group.day_in} → Day \${group.day_out}</p>
                            </div>
                            <span class="dl-hotel-city-card__nights">\${group.nights} Night\${group.nights === 1 ? '' : 's'}</span>
                        </div>
                        <div class="dl-hotel-city-card__body">
                            <div class="dl-hotel-city-card__actions">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="openCityHotelForm('\${escapeHtml(group.key)}')">
                                    Add hotel — \${escapeHtml(group.city_name)}
                                </button>
                            </div>
                            <div class="city-hotel-form-slot" data-city-key="\${escapeHtml(group.key)}"></div>
                            <div class="table-responsive modern-table-wrap hotels-compact-table-wrap">
                                <table class="table table-sm data-table-sm hotels-compact-table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Day</th>
                                            <th>Hotel</th>
                                            <th>Nights</th>
                                            <th>Room &amp; Meal</th>
                                            <th class="text-end">Price / Night</th>
                                            <th class="text-end">Total</th>
                                            <th class="text-end" style="width:88px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="\${rowsId}">
                                        <tr><td colspan="7" class="text-muted">No hotels for \${escapeHtml(group.city_name)}</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                \`;
            }).join('');
            renderHotelRows();
            if (formWasOpen && wasKey && wrap.querySelector('.dl-hotel-city-card[data-city-key="' + wasKey + '"]')) {
                openCityHotelForm(wasKey);
            }
        }

        function getTransferCityContextForDay(dayVal) {
            const d = parseInt(String(dayVal || 0), 10) || 0;
            const arrivalPlan = multiCityPlans.find((p) => (parseInt(String(p?.day_in || 0), 10) || 0) === d) || null;
            const departurePlan = multiCityPlans.find((p) => (parseInt(String(p?.day_out || 0), 10) || 0) === d) || null;
            const cityName = (plan) => String(plan?.city_name || '').split(',')[0].trim();
            return {
                arrivalCity: arrivalPlan ? cityName(arrivalPlan) : '',
                departureCity: departurePlan ? cityName(departurePlan) : '',
                isTripStart: d === 1,
                isTripEnd: d === daysCount,
                hasPrevCity: !!(arrivalPlan && multiCityPlans.some((p) => (parseInt(String(p?.day_out || 0), 10) || 0) < d)),
                hasNextCity: !!(departurePlan && multiCityPlans.some((p) => (parseInt(String(p?.day_in || 0), 10) || 0) > d)),
            };
        }

`;

mustInsertAfter(
  'hotel city fns',
  '        function syncHotelNightsWithMultiCity() {\n            syncHotelsWithMultiCity();\n        }\n',
  hotelFns
);

// refreshMultiCityDependentUi → also renderHotelCityBlocks
mustReplace(
  'refresh multi city hotels',
  `        function refreshMultiCityDependentUi() {
            renderMultiCityRows();
            setSectionCityOptions();
            if (typeof renderDayServiceBlocks === 'function') {
                renderDayServiceBlocks();
            }
            updateAllDayTransferVisibility();
            invalidateTransferOptionsCache();
            scheduleTransferOptionsReload(true);
            applyTransferDefaults();
            syncHotelsWithMultiCity();
        }`,
  `        function refreshMultiCityDependentUi() {
            renderMultiCityRows();
            setSectionCityOptions();
            if (typeof renderHotelCityBlocks === 'function') {
                renderHotelCityBlocks();
            }
            if (typeof renderDayServiceBlocks === 'function') {
                renderDayServiceBlocks();
            }
            updateAllDayTransferVisibility();
            invalidateTransferOptionsCache();
            scheduleTransferOptionsReload(true);
            applyTransferDefaults();
            syncHotelsWithMultiCity();
        }`
);

// initDays → render hotel city blocks
mustReplace(
  'initDays hotel blocks',
  `            renderDayServiceBlocks();
            // Hotels nights/day will be re-synced from multiCityPlans after Multi City changes.
            syncHotelNightsWithMultiCity();
        }`,
  `            if (typeof renderHotelCityBlocks === 'function') {
                renderHotelCityBlocks();
            }
            renderDayServiceBlocks();
            // Hotels nights/day will be re-synced from multiCityPlans after Multi City changes.
            syncHotelNightsWithMultiCity();
        }`
);

// updateAllDayTransferVisibility — city labels
mustReplace(
  'transfer visibility labels',
  `        function updateAllDayTransferVisibility() {
            for (let d = 1; d <= daysCount; d++) {
                const wrap = document.getElementById(\`day_transfer_wrap_\${d}\`);
                if (wrap) {
                    wrap.style.display = '';
                }
                const arrivalWrap = document.getElementById(\`day_arrival_wrap_\${d}\`);
                if (arrivalWrap) {
                    arrivalWrap.style.display = shouldShowArrivalForDay(d) ? '' : 'none';
                }
                const departureWrap = document.getElementById(\`day_departure_wrap_\${d}\`);
                if (departureWrap) {
                    departureWrap.style.display = shouldShowDepartureForDay(d) ? '' : 'none';
                }
                const extraWrap = document.getElementById(\`extra_transfer_wrap_\${d}\`);
                if (extraWrap) {
                    const isLegDay = shouldShowArrivalForDay(d) || shouldShowDepartureForDay(d);
                    const showExtra = daysCount >= 3 && d > 1 && d < daysCount && !isLegDay;
                    extraWrap.style.display = showExtra ? '' : 'none';
                }
            }

            if (serviceTransferOptions.length > 0 || transferLocationOptions.length > 0) {
                for (let d = 1; d <= daysCount; d++) {
                    populateServiceTransferSelectsForDay(d);
                }
            }
        }`,
  `        function updateAllDayTransferVisibility() {
            for (let d = 1; d <= daysCount; d++) {
                const wrap = document.getElementById(\`day_transfer_wrap_\${d}\`);
                if (wrap) {
                    wrap.style.display = '';
                }
                const showArrival = shouldShowArrivalForDay(d);
                const showDeparture = shouldShowDepartureForDay(d);
                const ctx = getTransferCityContextForDay(d);
                const arrivalWrap = document.getElementById(\`day_arrival_wrap_\${d}\`);
                if (arrivalWrap) {
                    arrivalWrap.style.display = showArrival ? '' : 'none';
                    if (showArrival) {
                        const titleEl = arrivalWrap.querySelector('.day-service-group__header strong');
                        const hintEl = arrivalWrap.querySelector('.day-service-group__hint');
                        if (titleEl) {
                            titleEl.textContent = ctx.arrivalCity
                                ? \`Arrival — \${ctx.arrivalCity}\`
                                : 'Arrival';
                        }
                        if (hintEl) {
                            hintEl.textContent = ctx.arrivalCity && ctx.hasPrevCity
                                ? \`Enter \${ctx.arrivalCity} (from previous city)\`
                                : (ctx.arrivalCity
                                    ? \`Airport pickup into \${ctx.arrivalCity}\`
                                    : 'Airport pickup on arrival day');
                        }
                    }
                }
                const departureWrap = document.getElementById(\`day_departure_wrap_\${d}\`);
                if (departureWrap) {
                    departureWrap.style.display = showDeparture ? '' : 'none';
                    if (showDeparture) {
                        const titleEl = departureWrap.querySelector('.day-service-group__header strong');
                        const hintEl = departureWrap.querySelector('.day-service-group__hint');
                        if (titleEl) {
                            titleEl.textContent = ctx.departureCity
                                ? \`Departure — \${ctx.departureCity}\`
                                : 'Departure';
                        }
                        if (hintEl) {
                            hintEl.textContent = ctx.departureCity && ctx.hasNextCity
                                ? \`Leave \${ctx.departureCity} (to next city)\`
                                : (ctx.departureCity
                                    ? \`Airport drop leaving \${ctx.departureCity}\`
                                    : 'Airport drop on departure day');
                        }
                    }
                }
                const extraWrap = document.getElementById(\`extra_transfer_wrap_\${d}\`);
                if (extraWrap) {
                    const isLegDay = showArrival || showDeparture;
                    const showExtra = daysCount >= 3 && d > 1 && d < daysCount && !isLegDay;
                    extraWrap.style.display = showExtra ? '' : 'none';
                }
            }

            if (serviceTransferOptions.length > 0 || transferLocationOptions.length > 0) {
                for (let d = 1; d <= daysCount; d++) {
                    populateServiceTransferSelectsForDay(d);
                }
            }
        }`
);

// Replace renderHotelRows to write into per-city tbodies
mustReplace(
  'renderHotelRows city',
  `        function renderHotelRows() {
            const body = document.getElementById('hotelRows');
            const current = [...hotels].sort((a, b) => (a.day || 0) - (b.day || 0));
            if (!current.length) {
                body.innerHTML = '<tr><td colspan="8" class="text-muted">No hotels added</td></tr>';
            } else {
                body.innerHTML = current.map((x) => {
                    const idx = hotels.indexOf(x);
                    const perNight = getHotelPerNightPrice(x);
                    const stayTotal = getHotelStayTotalPrice(x);
                    const hotelLabel = String(x.hotel_name || '-').replace(/\\s*-\\s*[^-]+$/i, '').trim() || x.hotel_name || '-';
                    return \`
                        <tr>
                            <td><div class="d-flex flex-wrap gap-1">\${formatHotelStayDayBadgesHtml(x)}</div></td>
                            <td>\${escapeHtml(x.city_name || '-')}</td>
                            <td>
                                <div class="hotel-cell-title">\${escapeHtml(hotelLabel)}</div>
                                <div class="hotel-cell-meta">\${escapeHtml(x.cat_label || '')}</div>
                            </td>
                            <td>\${escapeHtml(String(x.night || 1))}</td>
                            <td>
                                <div class="hotel-cell-title">\${escapeHtml(formatHotelRoomMealSummary(x))}</div>
                                \${x.bed_type ? \`<div class="hotel-cell-meta">Bed: \${escapeHtml(x.bed_type)}\${(parseInt(String(x.max_occupancy || 0), 10) > 0) ? \` (\${escapeHtml(String(x.max_occupancy))} pax)\` : ''}</div>\` : ''}
                            </td>
                            <td class="text-end">
                                <div class="hotel-price-night">\${getDayCurrency()} \${perNight.toFixed(2)}</div>
                                <div class="hotel-price-breakdown">\${escapeHtml(formatHotelPriceBreakdown(x))}</div>
                            </td>
                            <td class="text-end">
                                <div class="hotel-price-total">\${getDayCurrency()} \${stayTotal.toFixed(2)}</div>
                                <div class="hotel-price-breakdown">\${Math.max(1, parseInt(String(x.night || 1), 10) || 1)} night(s)</div>
                            </td>
                            <td class="action-cell text-end">
                                <span class="action-buttons">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-icon" onclick="editHotel(\${idx})" title="Edit" aria-label="Edit">\${actionIcon('edit')}</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="removeHotel(\${idx})" title="Remove" aria-label="Remove">\${actionIcon('remove')}</button>
                                </span>
                            </td>
                        </tr>
                    \`;
                }).join('');
            }
            document.getElementById('hotels_json').value = JSON.stringify(hotels);
            updateAllDayTransferVisibility();
            for (let d = 1; d <= daysCount; d++) {
                populateServiceTransferSelectsForDay(d);
            }
            syncPackageSubmitButtonsState();
        }`,
  `        function buildHotelRowHtml(x, idx) {
            const perNight = getHotelPerNightPrice(x);
            const stayTotal = getHotelStayTotalPrice(x);
            const hotelLabel = String(x.hotel_name || '-').replace(/\\s*-\\s*[^-]+$/i, '').trim() || x.hotel_name || '-';
            return \`
                <tr>
                    <td><div class="d-flex flex-wrap gap-1">\${formatHotelStayDayBadgesHtml(x)}</div></td>
                    <td>
                        <div class="hotel-cell-title">\${escapeHtml(hotelLabel)}</div>
                        <div class="hotel-cell-meta">\${escapeHtml(x.cat_label || '')}</div>
                    </td>
                    <td>\${escapeHtml(String(x.night || 1))}</td>
                    <td>
                        <div class="hotel-cell-title">\${escapeHtml(formatHotelRoomMealSummary(x))}</div>
                        \${x.bed_type ? \`<div class="hotel-cell-meta">Bed: \${escapeHtml(x.bed_type)}\${(parseInt(String(x.max_occupancy || 0), 10) > 0) ? \` (\${escapeHtml(String(x.max_occupancy))} pax)\` : ''}</div>\` : ''}
                    </td>
                    <td class="text-end">
                        <div class="hotel-price-night">\${getDayCurrency()} \${perNight.toFixed(2)}</div>
                        <div class="hotel-price-breakdown">\${escapeHtml(formatHotelPriceBreakdown(x))}</div>
                    </td>
                    <td class="text-end">
                        <div class="hotel-price-total">\${getDayCurrency()} \${stayTotal.toFixed(2)}</div>
                        <div class="hotel-price-breakdown">\${Math.max(1, parseInt(String(x.night || 1), 10) || 1)} night(s)</div>
                    </td>
                    <td class="action-cell text-end">
                        <span class="action-buttons">
                            <button type="button" class="btn btn-sm btn-outline-primary btn-icon" onclick="editHotel(\${idx})" title="Edit" aria-label="Edit">\${actionIcon('edit')}</button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="removeHotel(\${idx})" title="Remove" aria-label="Remove">\${actionIcon('remove')}</button>
                        </span>
                    </td>
                </tr>
            \`;
        }

        function renderHotelRows() {
            const groups = getItineraryCityGroupsForUi();
            const assigned = new Set();
            groups.forEach((group) => {
                const body = document.getElementById('hotelRows_' + group.key);
                if (!body) return;
                const cityHotels = hotels
                    .map((x, idx) => ({ x, idx }))
                    .filter(({ x, idx }) => {
                        if (assigned.has(idx)) return false;
                        if (!hotelBelongsToCityGroup(x, group)) return false;
                        assigned.add(idx);
                        return true;
                    })
                    .sort((a, b) => (a.x.day || 0) - (b.x.day || 0));
                if (!cityHotels.length) {
                    body.innerHTML = \`<tr><td colspan="7" class="text-muted">No hotels for \${escapeHtml(group.city_name)}</td></tr>\`;
                } else {
                    body.innerHTML = cityHotels.map(({ x, idx }) => buildHotelRowHtml(x, idx)).join('');
                }
            });
            // Fallback global tbody if present (legacy / single park)
            const legacyBody = document.getElementById('hotelRows');
            if (legacyBody && !groups.length) {
                legacyBody.innerHTML = '<tr><td colspan="8" class="text-muted">No hotels added</td></tr>';
            }
            const jsonEl = document.getElementById('hotels_json');
            if (jsonEl) jsonEl.value = JSON.stringify(hotels);
            updateAllDayTransferVisibility();
            for (let d = 1; d <= daysCount; d++) {
                populateServiceTransferSelectsForDay(d);
            }
            syncPackageSubmitButtonsState();
        }`
);

// addHotel → park after save
mustReplace(
  'addHotel park',
  `            renderHotelRows();
            updateAllDayTransferVisibility();
            applyTransferDefaults();
            resetHotelFields();
        }

        function removeHotel(idx) {`,
  `            renderHotelRows();
            updateAllDayTransferVisibility();
            applyTransferDefaults();
            resetHotelFields();
            parkHotelForm();
        }

        function removeHotel(idx) {`
);

// editHotel → open city form
mustReplace(
  'editHotel open city',
  `            if (cityMatch) {
                safeSetSelectValue('hotel_city_select', cityMatch.value);
            }
            // Nights dropdown depends on hotel city + Multi City span.
            syncHotelDayDropdownWithMultiCity();`,
  `            if (cityMatch) {
                safeSetSelectValue('hotel_city_select', cityMatch.value);
            }
            const groupsForEdit = getItineraryCityGroupsForUi();
            const editCityKey = (groupsForEdit.find((g) => hotelBelongsToCityGroup(x, g)) || {}).key;
            if (editCityKey) {
                openCityHotelForm(editCityKey);
            }
            // Nights dropdown depends on hotel city + Multi City span.
            syncHotelDayDropdownWithMultiCity();`
);

// DOM ready: ensure hotel city blocks once
mustInsertAfter(
  'dom ready hotel blocks',
  '            initDays();\n',
  `            if (typeof renderHotelCityBlocks === 'function' && document.getElementById('hotelCityBlocks')) {
                renderHotelCityBlocks();
            }
`
);

fs.writeFileSync(file, src);
console.log('done →', file);
console.log('size', src.length);
