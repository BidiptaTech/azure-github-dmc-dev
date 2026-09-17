/* === STP LITE: guide.js ===
 * Tour guides — package hours → Get Price (client) → Add → list + Supplement
 * Payload: guide_data
 * Packages: 1/2/4/6/8/10/12 only when that guide hour price > 0
 * Night pickup → add night_surcharge
 * === */
(function (window, document) {
    'use strict';

    var S = function () { return window.StpLiteTransportShared || {}; };

    function cfg() { return window.STP_LITE_CONFIG || {}; }

    /** Standard package tiers (same as classic create/edit). */
    var PACKAGE_TIERS = [
        { hours: 1,  key: 'hourly_price',        label: '1 Hour' },
        { hours: 2,  key: 'two_hour_price',      label: '2 Hours' },
        { hours: 4,  key: 'four_hour_price',     label: '4 Hours' },
        { hours: 6,  key: 'six_hour_price',      label: '6 Hours' },
        { hours: 8,  key: 'eight_hour_price',    label: '8 Hours' },
        { hours: 10, key: 'ten_hour_price',      label: '10 Hours' },
        { hours: 12, key: 'twelve_hour_price',   label: '12 Hours' }
    ];

    function shellHtml(stay) {
        var T = S();
        var cur = stay.currency || 'SGD';
        var g = T.tourGuests();
        return (
            '<div class="stp-lite-svc stp-lite-guide" data-currency="' + T.esc(cur) + '">' +
            '  <div class="row g-2 mb-2">' +
            '    <div class="col-md-3"><label class="stp-lite-label">City</label>' +
            '      <div class="stp-lite-city-static">' + T.esc(T.cityLabel(stay)) + '</div></div>' +
            '    <div class="col-md-3"><label class="stp-lite-label">Guide</label>' +
            '      <select class="form-select form-select-sm guide-select" disabled><option value="">Loading…</option></select></div>' +
            '    <div class="col-md-2"><label class="stp-lite-label">Package</label>' +
            '      <select class="form-select form-select-sm guide-hours" disabled><option value="">Select guide</option></select></div>' +
            '    <div class="col-md-2"><label class="stp-lite-label">Pickup time</label>' +
            T.ampmTimeHtml('guide', '') +
            '</div>' +
            '    <div class="col-md-2"><label class="stp-lite-label">Date</label>' +
            '      <input type="date" class="form-control form-control-sm guide-date" value="' + T.esc(stay.start || '') + '"></div>' +
            '  </div>' +
            '  <div class="row g-2 mb-2">' +
            '    <div class="col-md-2"><label class="stp-lite-label">Adults</label>' +
            '      <input type="number" min="0" class="form-control form-control-sm stp-lite-int guide-adults" data-guest-cap="adults" value="' + (g.adults || 1) + '"></div>' +
            '    <div class="col-md-2" data-guest-child-ui><label class="stp-lite-label">Children</label>' +
            '      <input type="number" min="0" class="form-control form-control-sm stp-lite-int guide-children" data-guest-cap="children" value="' + (g.children || 0) + '"></div>' +
            '    <div class="col-md-8 d-flex align-items-end gap-2 flex-wrap">' +
            '      <button type="button" class="btn btn-sm stp-lite-get-price-btn guide-get-price-btn">' +
            '        <i class="ri-price-tag-3-line me-1"></i>Get Price</button>' +
            '      <button type="button" class="btn btn-sm btn-primary guide-add-btn" disabled>' +
            '        <i class="ri-add-line me-1"></i>Add</button></div>' +
            '  </div>' +
            T.pricePanelHtml(cur, 'guide') +
            '  <div class="stp-lite-svc-added mt-2" data-guide-added-list></div>' +
            '  <input type="hidden" class="guide_data_chunk" value="[]">' +
            '</div>'
        );
    }

    function readChunk(root) {
        try {
            var v = JSON.parse((root.querySelector('.guide_data_chunk') || {}).value || '[]');
            return Array.isArray(v) ? v : [];
        } catch (e) { return []; }
    }

    function writeChunk(root, rows) {
        var el = root.querySelector('.guide_data_chunk');
        if (el) el.value = JSON.stringify(rows || []);
        S().syncHiddenJson('guide_data', '.guide_data_chunk');
        S().updateServiceHeaderTotal(root, 'guide');
    }

    function invalidate(root) {
        root.__lastPrice = null;
        var panel = root.querySelector('[data-guide-price-panel]');
        if (panel) panel.classList.add('d-none');
        var add = root.querySelector('.guide-add-btn');
        if (add) add.disabled = true;
    }

    function findGuide(root, guideId) {
        return (root.__guides || []).find(function (g) {
            return String(g.guide_id || g.id) === String(guideId);
        }) || null;
    }

    function packagePrice(guide, hours) {
        hours = parseInt(hours, 10) || 0;
        if (!guide || hours < 1) return 0;
        var tier = PACKAGE_TIERS.find(function (t) { return t.hours === hours; });
        if (!tier) return 0;
        return parseFloat(guide[tier.key]) || 0;
    }

    function availablePackages(guide) {
        if (!guide) return [];
        var cur = '';
        return PACKAGE_TIERS
            .map(function (t) {
                var price = parseFloat(guide[t.key]) || 0;
                return { hours: t.hours, label: t.label, price: price };
            })
            .filter(function (p) { return p.price > 0; });
    }

    function toMinutes(t) {
        if (t == null || t === '') return null;
        var str = String(t).trim();
        var m = str.match(/^(\d{1,2}):(\d{2})(?::\d{2})?\s*(AM|PM)?$/i);
        if (m) {
            var h = parseInt(m[1], 10) || 0;
            var min = parseInt(m[2], 10) || 0;
            var ap = (m[3] || '').toUpperCase();
            if (ap === 'PM' && h < 12) h += 12;
            if (ap === 'AM' && h === 12) h = 0;
            return h * 60 + min;
        }
        var p = str.split(':');
        if (p.length < 2) return null;
        return (parseInt(p[0], 10) || 0) * 60 + (parseInt(p[1], 10) || 0);
    }

    /** Pickup inside night window (supports overnight ranges e.g. 22:00–08:00). */
    function isNightTime(timeStr, start, end) {
        var cur = toMinutes(timeStr);
        var s = toMinutes(start);
        var e = toMinutes(end);
        if (cur == null || s == null || e == null) return false;
        if (s === e) return true;
        if (s < e) return cur >= s && cur < e;
        return cur >= s || cur < e;
    }

    function nightSurchargeFor(guide, pickupTime) {
        if (!guide || !pickupTime) return 0;
        var start = guide.night_start_time || guide.night_start || '';
        var end = guide.night_end_time || guide.night_end || '';
        if (!isNightTime(pickupTime, start, end)) return 0;
        return parseFloat(guide.night_surcharge) || 0;
    }

    function fillHourOptions(root, guide, preferredHours) {
        var hoursEl = root.querySelector('.guide-hours');
        if (!hoursEl) return;
        var pkgs = availablePackages(guide);
        var keep = preferredHours != null ? String(preferredHours) : String(hoursEl.value || '');
        hoursEl.innerHTML = '<option value="">Select package</option>';
        pkgs.forEach(function (p) {
            var opt = document.createElement('option');
            opt.value = String(p.hours);
            opt.textContent = p.label;
            opt.dataset.price = String(p.price);
            hoursEl.appendChild(opt);
        });
        if (!pkgs.length) {
            var empty = document.createElement('option');
            empty.value = '';
            empty.textContent = guide ? 'No priced packages' : 'Select guide';
            empty.disabled = true;
            hoursEl.appendChild(empty);
            hoursEl.disabled = true;
            return;
        }
        hoursEl.disabled = false;
        if (keep && hoursEl.querySelector('option[value="' + keep + '"]')) {
            hoursEl.value = keep;
        } else {
            hoursEl.value = '';
        }
    }

    function loadGuides(root, stay) {
        var T = S();
        var select = root.querySelector('.guide-select');
        if (!select) return Promise.resolve();
        var q = T.inv(stay.cityName, stay.country);
        select.disabled = true;
        select.innerHTML = '<option value="">Loading guides…</option>';
        return T.fetchJson((cfg().routes.fetchGuidesByDmc || '') + '?' + q.qs)
            .then(function (res) {
                var list = (res && res.guides) || [];
                root.__guides = list;
                select.innerHTML = '<option value="">Select guide</option>';
                list.forEach(function (g) {
                    var opt = document.createElement('option');
                    opt.value = g.guide_id || g.id;
                    opt.textContent = g.name || g.guide_name || 'Guide';
                    opt.dataset.name = g.name || g.guide_name || '';
                    opt.dataset.hourlyPrice = g.hourly_price || 0;
                    opt.dataset.twoHourPrice = g.two_hour_price || 0;
                    opt.dataset.fourHourPrice = g.four_hour_price || 0;
                    opt.dataset.sixHourPrice = g.six_hour_price || 0;
                    opt.dataset.eightHourPrice = g.eight_hour_price || 0;
                    opt.dataset.tenHourPrice = g.ten_hour_price || 0;
                    opt.dataset.twelveHourPrice = g.twelve_hour_price || 0;
                    opt.dataset.nightSurcharge = g.night_surcharge || 0;
                    opt.dataset.nightStartTime = g.night_start_time || '';
                    opt.dataset.nightEndTime = g.night_end_time || '';
                    select.appendChild(opt);
                });
                select.disabled = false;
                if (select.value) {
                    fillHourOptions(root, findGuide(root, select.value), (root.querySelector('.guide-hours') || {}).value);
                } else {
                    fillHourOptions(root, null);
                }
            })
            .catch(function () {
                select.innerHTML = '<option value="">Error loading</option>';
                select.disabled = false;
            });
    }

    function getPrice(root) {
        var T = S();
        var select = root.querySelector('.guide-select');
        var hoursEl = root.querySelector('.guide-hours');
        if (!select || !select.value) {
            alert('Select a guide first.');
            return;
        }
        if (!hoursEl || !hoursEl.value) {
            alert('Select a package first.');
            return;
        }
        var guide = findGuide(root, select.value) || {};
        var hours = parseInt(hoursEl.value, 10) || 0;
        var base = packagePrice(guide, hours);
        if (base <= 0) {
            alert('Selected package has no price for this guide.');
            return;
        }
        var pickup = T.readAmPmValue(root, 'guide');
        var surcharge = nightSurchargeFor(guide, pickup);
        var total = base + surcharge;
        root.__lastPrice = { total: total, base: base, surcharge: surcharge, hours: hours };
        var cur = root.getAttribute('data-currency') || 'SGD';
        var panel = root.querySelector('[data-guide-price-panel]');
        var totalEl = root.querySelector('.guide-price-total');
        var detail = root.querySelector('.guide-price-detail');
        if (panel) panel.classList.remove('d-none');
        if (totalEl) totalEl.textContent = cur + ' ' + total.toFixed(2);
        if (detail) {
            detail.textContent = hours + 'h package ' + cur + ' ' + base.toFixed(2) +
                (surcharge ? ' + night surcharge ' + cur + ' ' + surcharge.toFixed(2) : '');
        }
        var add = root.querySelector('.guide-add-btn');
        if (add) add.disabled = false;
    }

    function collectPayload(root, stay) {
        var T = S();
        var select = root.querySelector('.guide-select');
        var opt = select && select.options[select.selectedIndex];
        var hours = parseInt((root.querySelector('.guide-hours') || {}).value, 10) || 0;
        var adults = parseInt((root.querySelector('.guide-adults') || {}).value, 10) || 0;
        var children = parseInt((root.querySelector('.guide-children') || {}).value, 10) || 0;
        var total = root.__lastPrice ? Number(root.__lastPrice.total || 0) : 0;
        var supplement = T.autoSupplement(adults);
        var entrytime = T.readAmPmValue(root, 'guide');

        return {
            guide_id: select ? select.value : '',
            guide_name: opt ? (opt.dataset.name || opt.textContent) : '',
            hours: hours,
            entrypickup: '',
            entrytime: entrytime,
            adults: adults,
            children: children,
            basePrice: root.__lastPrice ? root.__lastPrice.base : 0,
            surcharge: root.__lastPrice ? root.__lastPrice.surcharge : 0,
            totalPrice: total,
            grand_total: total,
            pickupdate: (root.querySelector('.guide-date') || {}).value || stay.start || '',
            bookingDate: (root.querySelector('.guide-date') || {}).value || stay.start || '',
            supplement: !!supplement,
            is_supplement: !!supplement,
            city: stay.cityName || '',
            country: stay.country || '',
            currency: stay.currency || '',
            plan_index: stay.planIndex || '',
            remarks: ''
        };
    }

    function setAddMode(root, editing) {
        var btn = root.querySelector('.guide-add-btn');
        if (!btn) return;
        btn.innerHTML = editing
            ? '<i class="ri-save-line me-1"></i>Update'
            : '<i class="ri-add-line me-1"></i>Add';
    }

    function renderAdded(root) {
        var T = S();
        var host = root.querySelector('[data-guide-added-list]');
        if (!host) return;
        var rows = readChunk(root);
        var cur = root.getAttribute('data-currency') || 'SGD';
        if (!rows.length) { host.innerHTML = ''; return; }
        var html = '<div class="table-responsive stp-lite-svc-added-wrap"><table class="table table-sm align-middle mb-0 stp-lite-svc-added-table"><thead><tr>' +
            '<th>Guide</th><th>Package</th><th>Guests</th><th class="text-end">Total</th><th>Supplement</th><th class="text-end">Actions</th>' +
            '</tr></thead><tbody>';
        rows.forEach(function (row, idx) {
            var editing = root.__editingIdx === idx;
            html += '<tr class="' + (editing ? 'is-editing' : '') + '" data-idx="' + idx + '">' +
                '<td><div class="fw-semibold">' + T.esc(row.guide_name || 'Guide') + '</div>' +
                '<small class="text-muted">' + T.esc(row.entrytime || '—') + ' · ' + T.esc(row.bookingDate || '') + '</small>' +
                T.editingMarkHtml(editing) + '</td>' +
                '<td><small>' + T.esc(row.hours || 0) + 'h' +
                (row.surcharge ? ' · night' : '') + '</small></td>' +
                '<td><small>' + T.esc(row.adults || 0) + 'A / ' + T.esc(row.children || 0) + 'C</small></td>' +
                '<td class="text-end fw-semibold text-nowrap">' + cur + ' ' + Number(row.totalPrice || 0).toFixed(2) + '</td>' +
                '<td><div class="form-check mb-0"><input class="form-check-input guide-is-supplement" type="checkbox" data-idx="' + idx + '"' +
                (row.supplement || row.is_supplement ? ' checked' : '') + '>' +
                '<label class="form-check-label" style="font-size:0.72rem;">Supplement</label></div></td>' +
                '<td class="text-end">' + T.addedTableActions('guide', idx, true) + '</td>' +
                '</tr>';
        });
        html += '</tbody></table></div>';
        host.innerHTML = html;
    }

    function hydrate(root, stay, row) {
        if (!row) return;
        root.__hydrating = true;
        var select = root.querySelector('.guide-select');
        var dateEl = root.querySelector('.guide-date');
        var adultsEl = root.querySelector('.guide-adults');
        var childrenEl = root.querySelector('.guide-children');
        if (dateEl) dateEl.value = row.bookingDate || row.pickupdate || stay.start || '';
        if (adultsEl) adultsEl.value = String(row.adults || 0);
        if (childrenEl) childrenEl.value = String(row.children || 0);
        S().setAmPmValue(root, 'guide', row.entrytime || '');
        root.__lastPrice = {
            total: row.totalPrice || 0,
            base: row.basePrice || 0,
            surcharge: row.surcharge || 0,
            hours: row.hours || 0
        };
        var cur = root.getAttribute('data-currency') || 'SGD';
        var panel = root.querySelector('[data-guide-price-panel]');
        var totalEl = root.querySelector('.guide-price-total');
        var detail = root.querySelector('.guide-price-detail');
        if (panel) panel.classList.remove('d-none');
        if (totalEl) totalEl.textContent = cur + ' ' + Number(row.totalPrice || 0).toFixed(2);
        if (detail) {
            detail.textContent = (row.hours || 0) + 'h package ' + cur + ' ' + Number(row.basePrice || 0).toFixed(2) +
                (row.surcharge ? ' + night surcharge ' + cur + ' ' + Number(row.surcharge || 0).toFixed(2) : '');
        }
        var add = root.querySelector('.guide-add-btn');
        if (add) add.disabled = false;

        function applyGuide() {
            if (select && row.guide_id) select.value = String(row.guide_id);
            fillHourOptions(root, findGuide(root, row.guide_id), row.hours);
            root.__hydrating = false;
        }

        if ((root.__guides || []).length) {
            applyGuide();
        } else {
            loadGuides(root, stay).then(applyGuide).catch(function () {
                root.__hydrating = false;
            });
        }
    }

    function addRow(root, stay) {
        if (!root.__lastPrice) { alert('Please Get Price first.'); return; }
        var rows = readChunk(root);
        var payload = collectPayload(root, stay);
        if (root.__editingIdx != null && root.__editingIdx >= 0 && root.__editingIdx < rows.length) {
            payload.supplement = rows[root.__editingIdx].supplement;
            payload.is_supplement = rows[root.__editingIdx].is_supplement;
            rows[root.__editingIdx] = payload;
            root.__editingIdx = null;
            setAddMode(root, false);
        } else {
            rows.push(payload);
        }
        writeChunk(root, rows);
        renderAdded(root);
        invalidate(root);
        var guide = root.querySelector('.guide-select');
        if (guide) S().clearSelectOrInput(guide, '');
        fillHourOptions(root, null);
        S().setAmPmValue(root, 'guide', '');
        var getBtn = root.querySelector('.guide-get-price-btn');
        if (getBtn) getBtn.disabled = false;
        var addBtn = root.querySelector('.guide-add-btn');
        if (addBtn) addBtn.disabled = true;
    }

    function onGuideChange(root) {
        var select = root.querySelector('.guide-select');
        var guide = select && select.value ? findGuide(root, select.value) : null;
        fillHourOptions(root, guide);
        if (!root.__hydrating) invalidate(root);
    }

    function bindShell(root, stay) {
        if (!root || root.__bound) return;
        root.__bound = true;
        var T = S();
        T.bindAmPm(root);
        T.bindStayDate(root.querySelector('.guide-date'), stay, function () { invalidate(root); });
        T.bindGuestCaps(root);
        loadGuides(root, stay);

        root.querySelector('.guide-get-price-btn').addEventListener('click', function () { getPrice(root); });
        root.querySelector('.guide-add-btn').addEventListener('click', function () { addRow(root, stay); });

        var guideSelect = root.querySelector('.guide-select');
        if (guideSelect) {
            guideSelect.addEventListener('change', function () { onGuideChange(root); });
        }
        root.querySelectorAll('.guide-hours, .guide-adults, .guide-children').forEach(function (el) {
            el.addEventListener('change', function () { if (!root.__hydrating) invalidate(root); });
            el.addEventListener('input', function () { if (!root.__hydrating) invalidate(root); });
        });
        root.addEventListener('stp:time-changed', function () { if (!root.__hydrating) invalidate(root); });

        root.addEventListener('change', function (e) {
            var chk = e.target.closest('.guide-is-supplement');
            if (!chk) return;
            var rows = readChunk(root);
            var i = parseInt(chk.getAttribute('data-idx'), 10) || 0;
            if (rows[i]) {
                rows[i].supplement = !!chk.checked;
                rows[i].is_supplement = !!chk.checked;
                writeChunk(root, rows);
            }
        });
        root.addEventListener('click', function (e) {
            var removeBtn = e.target.closest('.guide-remove');
            if (removeBtn) {
                var rows = readChunk(root);
                rows.splice(parseInt(removeBtn.getAttribute('data-idx'), 10) || 0, 1);
                writeChunk(root, rows);
                root.__editingIdx = null;
                setAddMode(root, false);
                renderAdded(root);
                return;
            }
            var editBtn = e.target.closest('.guide-edit-added');
            if (editBtn) {
                var idx = parseInt(editBtn.getAttribute('data-idx'), 10) || 0;
                var row = readChunk(root)[idx];
                if (!row) return;
                root.__editingIdx = idx;
                setAddMode(root, true);
                hydrate(root, stay, row);
                renderAdded(root);
                return;
            }
            var viewBtn = e.target.closest('.guide-view-breakup');
            if (viewBtn) {
                var r = readChunk(root)[parseInt(viewBtn.getAttribute('data-idx'), 10) || 0];
                if (!r) return;
                var cur = r.currency || root.getAttribute('data-currency') || 'SGD';
                T.showPriceBreakdownModal(
                    r.guide_name || 'Guide',
                    cur,
                    r.totalPrice,
                    '<div class="small text-muted">' + T.esc(r.hours || 0) + 'h package · ' +
                    cur + ' ' + Number(r.basePrice || 0).toFixed(2) +
                    (r.surcharge ? '<br>Night surcharge · ' + cur + ' ' + Number(r.surcharge || 0).toFixed(2) : '') +
                    (r.entrytime ? '<br>Time: ' + T.esc(r.entrytime) : '') + '</div>'
                );
            }
        });

        renderAdded(root);
    }

    function mountAll(scope) {
        var T = S();
        (scope || document).querySelectorAll('[data-stp-guide-mount]').forEach(function (el) {
            var stay = T.stayFromPanel(el, 'guide');
            el.innerHTML = shellHtml(stay);
            bindShell(el.querySelector('.stp-lite-guide'), stay);
        });
        if (window.StpLiteGuestCaps) window.StpLiteGuestCaps.refreshGuestDependentUI();
    }

    window.StpLiteGuide = {
        mountAll: mountAll,
        seedAdded: function (root, rows) {
            if (!root) return;
            writeChunk(root, rows || []);
            renderAdded(root);
        }
    };
})(window, document);
/* === END guide.js === */
