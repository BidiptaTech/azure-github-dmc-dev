/**
 * Optimize day services: one form + Day select (keep existing day field IDs).
 */
const fs = require('fs');
const path = require('path');
const file = path.join(__dirname, '..', 'resources', 'views', 'day-level', 'create.blade.php');
let src = fs.readFileSync(file, 'utf8');

function mustReplace(label, from, to) {
  if (!src.includes(from)) throw new Error('MISSING: ' + label);
  src = src.replace(from, to);
  console.log('ok', label);
}

// --- CSS: day pick + hide inactive panels ---
if (!src.includes('.dl-day-pick-bar {')) {
  const cssInsert = `
        /* One form per city — Day select swaps which day panel is shown */
        .dl-svc-city-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 0.15rem 0.5rem rgba(15, 23, 42, 0.045);
            margin-bottom: 1rem;
            overflow: hidden;
        }
        .dl-svc-city-card__head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem 1rem;
            padding: 0.75rem 1rem;
            background: #f8fafc;
            border-bottom: 1px solid #eef2f7;
        }
        .dl-svc-city-card__title {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 750;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #0f172a;
        }
        .dl-svc-city-card__meta {
            margin: 0.15rem 0 0;
            font-size: 0.78rem;
            color: #64748b;
        }
        .dl-svc-city-card__nights {
            padding: 0.28rem 0.65rem;
            border-radius: 999px;
            background: #eef2ff;
            color: #3730a3;
            font-size: 0.72rem;
            font-weight: 650;
        }
        .dl-svc-city-card__body { padding: 0.85rem 1rem 1rem; }
        .dl-day-pick-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 0.65rem 1rem;
            margin-bottom: 0.85rem;
            padding: 0.65rem 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: linear-gradient(180deg, #f8fafc 0%, #fff 100%);
        }
        .dl-day-pick-bar label {
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 0.25rem;
        }
        .dl-day-pick-bar .form-select {
            min-width: 10rem;
            max-width: 14rem;
            font-weight: 600;
        }
        .dl-day-pick-bar__hint {
            font-size: 0.78rem;
            color: #94a3b8;
            padding-bottom: 0.35rem;
        }
        .dl-day-panel { display: none; }
        .dl-day-panel.is-active { display: block; }
        .dl-day-panel > .day-card {
            border: 0;
            box-shadow: none !important;
            margin-bottom: 0 !important;
            background: transparent;
        }
        .dl-day-panel .day-card-header,
        .dl-day-panel .dl-day-seg-head { display: none !important; }
        .dl-day-panel .day-card > .card-body {
            padding: 0 !important;
            background: transparent;
        }
        .dl-day-panel .row.g-2.align-items-end.mb-2:first-child {
            /* keep city select in DOM for logic; hide from user */
        }
        .dl-day-panel .activity-city-row { display: none !important; }
`;
  const marker = '        /* Day segments — open, stacked, professional */';
  if (src.includes(marker)) {
    src = src.replace(marker, cssInsert + marker);
    console.log('ok css');
  } else {
    throw new Error('css marker missing');
  }
}

// --- helpers ---
const helpers = `
        function getItineraryCityGroupsForUi() {
            const covered = new Set();
            const groups = [];
            if (Array.isArray(multiCityPlans) && multiCityPlans.length) {
                multiCityPlans.forEach((p, idx) => {
                    const din = Math.max(1, parseInt(String(p?.day_in || 1), 10) || 1);
                    const dout = Math.max(din, Math.min(daysCount, parseInt(String(p?.day_out || din), 10) || din));
                    const days = [];
                    for (let d = din; d <= dout; d++) {
                        days.push(d);
                        covered.add(d);
                    }
                    groups.push({
                        key: 'c' + idx,
                        city_name: String(p?.city_name || 'City').split(',')[0].trim() || 'City',
                        day_in: din,
                        day_out: dout,
                        nights: Math.max(1, dout - din),
                        days
                    });
                });
            }
            const orphans = [];
            for (let d = 1; d <= daysCount; d++) {
                if (!covered.has(d)) orphans.push(d);
            }
            if (!groups.length) {
                const days = [];
                for (let d = 1; d <= daysCount; d++) days.push(d);
                const mainName = (getCityNameFromSelect('city_id') || 'Itinerary').split(',')[0].trim() || 'Itinerary';
                groups.push({
                    key: 'all',
                    city_name: mainName,
                    day_in: 1,
                    day_out: Math.max(1, daysCount),
                    nights: Math.max(1, daysCount > 1 ? daysCount - 1 : 1),
                    days
                });
            } else if (orphans.length) {
                groups.push({
                    key: 'other',
                    city_name: 'Other days',
                    day_in: orphans[0],
                    day_out: orphans[orphans.length - 1],
                    nights: Math.max(1, orphans.length > 1 ? orphans.length - 1 : 1),
                    days: orphans
                });
            }
            return groups;
        }

        function switchItineraryCityDay(cityKey, dayVal) {
            const wrap = document.getElementById('dayWiseServiceBlocks');
            if (!wrap) return;
            const day = String(dayVal || '');
            wrap.querySelectorAll('.dl-day-panel[data-city-key="' + cityKey + '"]').forEach((panel) => {
                panel.classList.toggle('is-active', String(panel.getAttribute('data-day') || '') === day);
            });
            const sel = document.getElementById('city_service_day_' + cityKey);
            if (sel && String(sel.value) !== day) sel.value = day;
            // Refresh transfer visibility for the newly shown day
            const dNum = parseInt(day, 10) || 0;
            if (dNum && typeof updateDayTransferVisibility === 'function') {
                try { updateDayTransferVisibility(dNum); } catch (e) { /* ignore */ }
            } else if (typeof updateAllDayTransferVisibility === 'function') {
                updateAllDayTransferVisibility();
            }
        }

`;

if (!src.includes('function getItineraryCityGroupsForUi()')) {
  const i = src.indexOf('        function renderDayServiceBlocks() {');
  if (i < 0) throw new Error('renderDayServiceBlocks missing');
  src = src.slice(0, i) + helpers + src.slice(i);
  console.log('ok helpers');
}

// --- replace renderDayServiceBlocks open ---
const start = src.indexOf('        function renderDayServiceBlocks() {');
const mid = src.indexOf('                            <div class="day-service-group group-attraction" id="attraction_group_${d}">', start);
if (start < 0 || mid < 0) throw new Error('rds markers missing ' + start + ' ' + mid);

const openNew = `        function renderDayServiceBlocks() {
            const wrap = document.getElementById('dayWiseServiceBlocks');
            if (!wrap) return;
            const groups = getItineraryCityGroupsForUi();
            let html = '';
            groups.forEach((group) => {
                html += \`
                    <div class="dl-svc-city-card" data-city-key="\${escapeHtml(group.key)}">
                        <div class="dl-svc-city-card__head">
                            <div>
                                <h3 class="dl-svc-city-card__title">\${escapeHtml(group.city_name)}</h3>
                                <p class="dl-svc-city-card__meta">Day \${group.day_in} → Day \${group.day_out}</p>
                            </div>
                            <span class="dl-svc-city-card__nights">\${group.nights} Night\${group.nights === 1 ? '' : 's'}</span>
                        </div>
                        <div class="dl-svc-city-card__body">
                            <div class="dl-day-pick-bar">
                                <div>
                                    <label for="city_service_day_\${escapeHtml(group.key)}">Add services for</label>
                                    <select id="city_service_day_\${escapeHtml(group.key)}" class="form-select"
                                        onchange="switchItineraryCityDay('\${escapeHtml(group.key)}', this.value)">
                                        \${group.days.map((d) => \`<option value="\${d}">Day \${d}</option>\`).join('')}
                                    </select>
                                </div>
                                <span class="dl-day-pick-bar__hint">Pick a day, add attraction / restaurant, switch day — no scrolling through every segment</span>
                            </div>
                \`;
                group.days.forEach((d, di) => {
                const activeClass = di === 0 ? ' is-active' : '';
                html += \`
                    <div class="dl-day-panel\${activeClass}" data-city-key="\${escapeHtml(group.key)}" data-day="\${d}" id="itinerary_day_panel_\${d}">
                    <div class="card sketch-card day-card mb-2" data-day="\${d}">
                        <div class="card-header day-card-header">
                            <div class="dl-day-seg-head">
                                <strong>Day \${d}</strong>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-2 align-items-end mb-2 activity-city-row">
                            <div class="col-md-4">
                                <label class="form-label" for="activity_city_select_\${d}">City</label>
                                <select id="activity_city_select_\${d}" class="form-select searchable-select">
                                    <option value="">Select city</option>
                                </select>
                                </div>
                            </div>

`;

if (!src.slice(start, mid).includes('dl-day-pick-bar')) {
  src = src.slice(0, start) + openNew + src.slice(mid);
  console.log('ok rds open');
} else {
  console.log('skip rds open');
}

// --- close ---
const closeOld = `                            <div class="mt-2" id="day_items_\${d}"></div>
                        </div>
                    </div>
                \`;
            }
            wrap.innerHTML = html;
            initSearchableSelects(wrap);
            hydrateDayServiceBlocksOptions();
            updateAllDayTransferVisibility();
            hydrateAllDayTransferCityOptions();
            renderAllExtraTransferRows();
        }`;

const closeNew = `                            <div class="mt-2" id="day_items_\${d}"></div>
                        </div>
                    </div>
                    </div>
                \`;
                });
                html += \`
                        </div>
                    </div>
                \`;
            });
            wrap.innerHTML = html;
            initSearchableSelects(wrap);
            hydrateDayServiceBlocksOptions();
            updateAllDayTransferVisibility();
            hydrateAllDayTransferCityOptions();
            renderAllExtraTransferRows();
        }`;

if (src.includes(closeOld)) {
  mustReplace('rds close', closeOld, closeNew);
} else if (!src.includes('group.days.forEach((d, di)')) {
  throw new Error('close pattern / open incomplete');
} else {
  // already has forEach close from partial?
  const hasClose = src.includes('});\n            wrap.innerHTML = html;\n            initSearchableSelects(wrap);');
  console.log('rds close status', hasClose ? 'already ok' : 'NEED FIX');
  if (!hasClose) {
    // try alternate close from previous open-all design
    const altOld = `                            <div class="mt-2" id="day_items_\${d}"></div>
                        </div>
                    </div>
                \`;
            }
            wrap.innerHTML = html;`;
    if (src.includes(altOld.replace(/\\\$/g, '$')) === false) {
      // use raw
    }
  }
}

// Section title
if (src.includes('Attraction / Restaurant by Day')) {
  mustReplace(
    'section title',
    `                                    <strong class="text-white d-block">Attraction / Restaurant by Day</strong>
                                    <span class="section-subtitle">Plan daily attractions, meals and transfers</span>`,
    `                                    <strong class="text-white d-block">City Services</strong>
                                    <span class="section-subtitle">Choose a day, add services — switch day without leaving the form</span>`
  );
}

fs.writeFileSync(file, src);
console.log('done', src.length);
