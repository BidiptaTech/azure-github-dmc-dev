/* === STP LITE: miscellaneous.js ===
 * Country/city-scoped misc items after Departure — enquiry payload shape
 * Payload: miscellaneous_data
 * === */
(function (window, document) {
    'use strict';

    var S = function () { return window.StpLiteTransportShared || {}; };
    var PREFIX = 'miscellaneous';

    function cfg() { return window.STP_LITE_CONFIG || {}; }

    function uid() {
        return 'misc-' + Date.now() + '-' + Math.random().toString(36).slice(2, 9);
    }

    function shellHtml(stay) {
        var T = S();
        var cur = stay.currency || 'SGD';
        var g = T.tourGuests();
        var city = T.cityLabel(stay) || stay.cityName || '';
        var country = stay.country || '';
        return (
            '<div class="stp-lite-svc stp-lite-miscellaneous" data-currency="' + T.esc(cur) + '">' +
            '  <div class="stp-lite-misc-toolbar">' +
            '    <div class="stp-lite-misc-toolbar__field">' +
            '      <label class="stp-lite-label">Date</label>' +
            '      <input type="date" class="form-control form-control-sm misc-date" value="' + T.esc(stay.start || '') + '">' +
            '    </div>' +
            '    <div class="stp-lite-misc-toolbar__field">' +
            '      <label class="stp-lite-label">Country</label>' +
            '      <div class="stp-lite-city-static">' + T.esc(country || '—') + '</div>' +
            '    </div>' +
            '    <div class="stp-lite-misc-toolbar__field stp-lite-misc-toolbar__field--grow">' +
            '      <label class="stp-lite-label">City</label>' +
            '      <div class="stp-lite-city-static">' + T.esc(city || '—') + '</div>' +
            '    </div>' +
            '  </div>' +
            '  <div class="stp-lite-misc-picker" data-misc-picker>' +
            '    <div class="stp-lite-misc-picker__head">' +
            '      <span>Add item</span>' +
            '      <small class="text-muted">Search · select · set qty · add</small>' +
            '    </div>' +
            '    <div class="stp-lite-misc-search-row">' +
            '      <div class="stp-lite-misc-search-row__select">' +
            '        <label class="stp-lite-label">Item</label>' +
            '        <select class="form-select form-select-sm misc-item-select">' +
            '          <option value="">Search miscellaneous item…</option>' +
            '        </select>' +
            '      </div>' +
            '    </div>' +
            '    <div class="misc-items-body stp-lite-misc-configure d-none" data-misc-configure></div>' +
            '  </div>' +
            '  <div class="stp-lite-misc-added-block mt-3">' +
            '    <div class="stp-lite-misc-picker__head">' +
            '      <span>Added items</span>' +
            '      <small class="text-muted misc-added-count"></small>' +
            '    </div>' +
            '    <div class="stp-lite-svc-added" data-miscellaneous-added-list></div>' +
            '  </div>' +
            '  <input type="hidden" class="miscellaneous_data_chunk" value="[]"' +
            '         data-default-adults="' + (g.adults || 1) + '"' +
            '         data-default-children="' + (g.children || 0) + '"' +
            '         data-default-infants="' + (g.infants || 0) + '">' +
            '</div>'
        );
    }

    function readChunk(root) {
        try {
            var v = JSON.parse((root.querySelector('.miscellaneous_data_chunk') || {}).value || '[]');
            return Array.isArray(v) ? v : [];
        } catch (e) { return []; }
    }

    function writeChunk(root, rows) {
        var el = root.querySelector('.miscellaneous_data_chunk');
        if (el) el.value = JSON.stringify(rows || []);
        S().syncHiddenJson('miscellaneous_data', '.miscellaneous_data_chunk');
        if (typeof S().updateServiceHeaderTotal === 'function') {
            S().updateServiceHeaderTotal(root, 'miscellaneous');
        }
    }

    function money(n) {
        return (Number(n) || 0).toFixed(2);
    }

    function rowTotal(adultsQty, adultSell, childQty, childSell, infantQty, infantSell, foc) {
        if (foc) return 0;
        return (Number(adultsQty) || 0) * (Number(adultSell) || 0)
            + (Number(childQty) || 0) * (Number(childSell) || 0)
            + (Number(infantQty) || 0) * (Number(infantSell) || 0);
    }

    function addedItemIds(root, exceptIdx) {
        var ids = {};
        readChunk(root).forEach(function (row, idx) {
            if (exceptIdx != null && idx === exceptIdx) return;
            var id = String(row.itemId || row.mis_id || '');
            if (id) ids[id] = true;
        });
        return ids;
    }

    function defaultGuestQtys(root) {
        var T = S();
        var chunk = root.querySelector('.miscellaneous_data_chunk');
        var defA = parseInt((chunk && chunk.getAttribute('data-default-adults')) || '1', 10) || 1;
        var defC = parseInt((chunk && chunk.getAttribute('data-default-children')) || '0', 10) || 0;
        var defI = parseInt((chunk && chunk.getAttribute('data-default-infants')) || '0', 10) || 0;
        var g = T.tourGuests ? T.tourGuests() : {};
        if (g.adults != null) defA = Number(g.adults) || defA;
        if (g.children != null) defC = Number(g.children) || 0;
        if (g.infants != null) defI = Number(g.infants) || 0;
        return { adults: defA, children: defC, infants: defI };
    }

    function qtyStepperHtml(kind, label, qty, unitPrice, currency) {
        var T = S();
        return (
            '<div class="stp-lite-misc-step" data-kind="' + kind + '">' +
            '  <div class="stp-lite-misc-step__meta">' +
            '    <span class="stp-lite-misc-step__label">' + T.esc(label) + '</span>' +
            '    <span class="stp-lite-misc-step__rate">' + T.esc(currency) + ' ' + money(unitPrice) + '<small>/pax</small></span>' +
            '  </div>' +
            '  <div class="stp-lite-misc-step__ctrl">' +
            '    <button type="button" class="stp-lite-misc-step__btn misc-qty-dec" data-kind="' + kind + '" aria-label="Decrease">−</button>' +
            '    <input type="number" min="0" class="stp-lite-misc-qty misc-' + kind + '-qty" value="' + qty + '">' +
            '    <button type="button" class="stp-lite-misc-step__btn misc-qty-inc" data-kind="' + kind + '" aria-label="Increase">+</button>' +
            '  </div>' +
            '  <input type="hidden" class="misc-' + kind + '-charge" value="' + money(unitPrice) + '">' +
            '</div>'
        );
    }

    function calcCardTotal(card) {
        if (!card) return 0;
        if ((card.querySelector('.misc-foc-discount') || {}).checked) return 0;
        return rowTotal(
            parseInt((card.querySelector('.misc-adult-qty') || {}).value, 10) || 0,
            parseFloat((card.querySelector('.misc-adult-charge') || {}).value) || 0,
            parseInt((card.querySelector('.misc-child-qty') || {}).value, 10) || 0,
            parseFloat((card.querySelector('.misc-child-charge') || {}).value) || 0,
            parseInt((card.querySelector('.misc-infant-qty') || {}).value, 10) || 0,
            parseFloat((card.querySelector('.misc-infant-charge') || {}).value) || 0,
            false
        );
    }

    function refreshCardTotal(card, currency) {
        if (!card) return;
        var el = card.querySelector('[data-misc-line-total]');
        if (!el) return;
        var foc = !!(card.querySelector('.misc-foc-discount') || {}).checked;
        var total = calcCardTotal(card);
        el.textContent = foc ? 'FOC' : ((currency || 'SGD') + ' ' + money(total));
        el.classList.toggle('is-foc', foc);
    }

    function findItemById(root, itemId) {
        var items = root.__miscItems || [];
        var id = String(itemId || '');
        for (var i = 0; i < items.length; i += 1) {
            if (String(items[i].mis_id || items[i].id || '') === id) return items[i];
        }
        return null;
    }

    function destroyMiscSelect2(root) {
        var sel = root && root.querySelector('.misc-item-select');
        if (!sel || typeof window.jQuery === 'undefined') return;
        var $sel = window.jQuery(sel);
        if ($sel.hasClass('select2-hidden-accessible')) {
            try { $sel.select2('destroy'); } catch (e) { /* ignore */ }
        }
    }

    function initMiscSelect2(root) {
        var sel = root && root.querySelector('.misc-item-select');
        if (!sel || typeof window.jQuery === 'undefined' || !window.jQuery.fn || !window.jQuery.fn.select2) return;
        var $sel = window.jQuery(sel);
        if ($sel.hasClass('select2-hidden-accessible')) {
            try { $sel.select2('destroy'); } catch (e) { /* ignore */ }
        }
        $sel.select2({
            width: '100%',
            placeholder: 'Search miscellaneous item…',
            allowClear: true,
            dropdownParent: window.jQuery(root.querySelector('.stp-lite-misc-picker') || root)
        });
        $sel.off('change.stpMisc').on('change.stpMisc', function () {
            onItemSelected(root, root.__stay || {});
        });
    }

    function availableItems(root) {
        var taken = addedItemIds(root, root.__editingIdx);
        var editingId = '';
        if (root.__editingIdx != null && root.__editingIdx >= 0) {
            var editingRow = readChunk(root)[root.__editingIdx];
            editingId = editingRow ? String(editingRow.itemId || editingRow.mis_id || '') : '';
        }
        return (root.__miscItems || []).filter(function (item) {
            var id = String(item.mis_id || item.id || '');
            if (!id) return false;
            if (taken[id] && id !== editingId) return false;
            return true;
        });
    }

    function populateItemSelect(root, preferredId) {
        var T = S();
        var sel = root.querySelector('.misc-item-select');
        if (!sel) return;
        var items = availableItems(root);
        var stay = root.__stay || {};
        var html = '<option value="">Search miscellaneous item…</option>';
        items.forEach(function (item) {
            var id = String(item.mis_id || item.id || '');
            var name = item.item_name || item.itemName || 'Item';
            var adultP = Number(item.adult_price) || 0;
            var cur = root.getAttribute('data-currency') || stay.currency || 'SGD';
            var label = name + (adultP > 0 ? (' — ' + cur + ' ' + money(adultP)) : '');
            html += '<option value="' + T.esc(id) + '"' +
                (preferredId && String(preferredId) === id ? ' selected' : '') +
                '>' + T.esc(label) + '</option>';
        });
        destroyMiscSelect2(root);
        sel.innerHTML = html;
        if (preferredId) sel.value = String(preferredId);
        initMiscSelect2(root);
        if (!items.length) {
            hideConfigure(root);
            var host = root.querySelector('[data-misc-configure]');
            if (host) {
                host.classList.remove('d-none');
                host.innerHTML = '<div class="stp-lite-misc-empty">' +
                    ((root.__miscItems || []).length
                        ? 'All available items for this city are already added.'
                        : ('No miscellaneous items for ' + T.esc(stay.cityName || stay.country || 'this stay') + '.')) +
                    '</div>';
            }
            return;
        }
        if (preferredId && sel.value === String(preferredId)) {
            showConfigureForItem(root, stay, preferredId, null);
        } else if (!sel.value) {
            hideConfigure(root);
        }
    }

    function hideConfigure(root) {
        var host = root.querySelector('[data-misc-configure]');
        if (!host) return;
        host.classList.add('d-none');
        host.innerHTML = '';
    }

    function showConfigureForItem(root, stay, itemId, preset) {
        var T = S();
        var host = root.querySelector('[data-misc-configure]');
        if (!host) return;
        var item = findItemById(root, itemId);
        if (!item) {
            hideConfigure(root);
            return;
        }
        var cur = root.getAttribute('data-currency') || stay.currency || 'SGD';
        var defs = defaultGuestQtys(root);
        var name = item.item_name || item.itemName || 'Item';
        var desc = item.description || '';
        var adultP = Number(item.adult_price) || 0;
        var childP = Number(item.child_price) || 0;
        var infantP = Number(item.infant_price) || 0;
        var adultsQty = preset && preset.adultsQty != null ? preset.adultsQty : defs.adults;
        var childQty = preset && preset.childQty != null ? preset.childQty : defs.children;
        var infantQty = preset && preset.infantQty != null ? preset.infantQty : defs.infants;
        var focOn = !!(preset && (preset.focServiceDiscount || preset.foc_service_discount));
        var isEditing = root.__editingIdx != null && root.__editingIdx >= 0;
        var preview = rowTotal(adultsQty, adultP, childQty, childP, infantQty, infantP, focOn);

        host.classList.remove('d-none');
        host.innerHTML =
            '<article class="stp-lite-misc-card' + (isEditing ? ' is-editing' : '') + '"' +
            ' data-item-id="' + T.esc(String(itemId)) + '" data-item-name="' + T.esc(name) + '">' +
            '  <div class="stp-lite-misc-card__top">' +
            '    <div class="stp-lite-misc-card__title">' +
            '      <strong>' + T.esc(name) + '</strong>' +
            (desc ? '<span>' + T.esc(desc) + '</span>' : '') +
            '    </div>' +
            '    <label class="stp-lite-misc-foc">' +
            '      <input type="checkbox" class="form-check-input misc-foc-discount"' + (focOn ? ' checked' : '') + '>' +
            '      <span>FOC</span>' +
            '    </label>' +
            '  </div>' +
            '  <div class="stp-lite-misc-card__qtys">' +
            qtyStepperHtml('adult', 'Adults', adultsQty, adultP, cur) +
            qtyStepperHtml('child', 'Children', childQty, childP, cur) +
            qtyStepperHtml('infant', 'Infants', infantQty, infantP, cur) +
            '  </div>' +
            '  <div class="stp-lite-misc-card__foot">' +
            '    <div class="stp-lite-misc-card__total">' +
            '      <span class="stp-lite-misc-card__total-label">Line total</span>' +
            '      <strong data-misc-line-total>' + T.esc(cur) + ' ' + money(preview) + '</strong>' +
            '    </div>' +
            '    <div class="stp-lite-misc-card__actions">' +
            (isEditing
                ? '<button type="button" class="btn btn-sm btn-outline-secondary misc-cancel-edit-btn">Cancel</button>'
                : '') +
            '      <button type="button" class="btn btn-sm ' + (isEditing ? 'btn-success misc-update-btn' : 'btn-primary misc-add-one-btn') + '">' +
            (isEditing
                ? '<i class="ri-save-line me-1"></i>Update'
                : '<i class="ri-add-line me-1"></i>Add') +
            '      </button>' +
            '    </div>' +
            '  </div>' +
            '</article>';
        refreshCardTotal(host.querySelector('.stp-lite-misc-card'), cur);
    }

    function onItemSelected(root, stay) {
        var sel = root.querySelector('.misc-item-select');
        var id = sel ? String(sel.value || '') : '';
        if (!id) {
            if (root.__editingIdx == null) hideConfigure(root);
            return;
        }
        showConfigureForItem(root, stay || root.__stay || {}, id, null);
    }

    function renderItemsTable(root, items, stay) {
        root.__miscItems = items || [];
        root.__stay = stay || root.__stay || {};
        var preferredId = '';
        if (root.__editingIdx != null && root.__editingIdx >= 0) {
            var editingRow = readChunk(root)[root.__editingIdx];
            preferredId = editingRow ? String(editingRow.itemId || editingRow.mis_id || '') : '';
        }
        populateItemSelect(root, preferredId || undefined);
    }

    function refreshPicker(root, stay) {
        renderItemsTable(root, root.__miscItems || [], stay || root.__stay || {});
    }

    function loadItems(root, stay) {
        var T = S();
        var host = root.querySelector('[data-misc-configure]');
        var city = stay.cityName || '';
        var country = stay.country || '';
        root.__stay = stay;
        if (!city) {
            destroyMiscSelect2(root);
            var selEmpty = root.querySelector('.misc-item-select');
            if (selEmpty) selEmpty.innerHTML = '<option value="">City is required</option>';
            if (host) {
                host.classList.remove('d-none');
                host.innerHTML = '<div class="stp-lite-misc-empty">City is required to load items.</div>';
            }
            return;
        }
        if (host) {
            host.classList.remove('d-none');
            host.innerHTML = '<div class="stp-lite-misc-empty"><i class="ri-loader-4-line ri-spin me-1"></i>Loading…</div>';
        }

        var base = (cfg().routes && cfg().routes.getMiscellaneous) || '';
        if (!base) {
            if (host) host.innerHTML = '<div class="stp-lite-misc-empty text-danger">Miscellaneous route missing.</div>';
            return;
        }

        var inv = T.inv
            ? T.inv(city, country)
            : (window.buildInventoryDmcQuery
                ? window.buildInventoryDmcQuery(city, country)
                : { qs: 'city=' + encodeURIComponent(city) + '&country=' + encodeURIComponent(country) + '&dmc_id=' + (cfg().dmcId || '') });

        var url = base + (base.indexOf('?') >= 0 ? '&' : '?') + (inv.qs || ('city=' + encodeURIComponent(city)));
        if (url.indexOf('country=') < 0 && country) {
            url += '&country=' + encodeURIComponent(country);
        }

        T.fetchJson(url).then(function (data) {
            var items = Array.isArray(data) ? data : (data.items || data.data || []);
            if (!Array.isArray(items)) items = [];
            root.__miscItems = items;
            hideConfigure(root);
            renderItemsTable(root, items, stay);
        }).catch(function () {
            if (host) {
                host.classList.remove('d-none');
                host.innerHTML = '<div class="stp-lite-misc-empty text-danger">Failed to load miscellaneous items.</div>';
            }
        });
    }

    function buildPayloadFromCard(card, stay, dateVal, existingId) {
        var itemId = card.getAttribute('data-item-id') || '';
        var itemName = card.getAttribute('data-item-name') || '';
        var adultsQty = parseInt((card.querySelector('.misc-adult-qty') || {}).value, 10) || 0;
        var childQty = parseInt((card.querySelector('.misc-child-qty') || {}).value, 10) || 0;
        var infantQty = parseInt((card.querySelector('.misc-infant-qty') || {}).value, 10) || 0;
        var adultSell = parseFloat((card.querySelector('.misc-adult-charge') || {}).value) || 0;
        var childSell = parseFloat((card.querySelector('.misc-child-charge') || {}).value) || 0;
        var infantSell = parseFloat((card.querySelector('.misc-infant-charge') || {}).value) || 0;
        var foc = !!(card.querySelector('.misc-foc-discount') || {}).checked;
        var city = stay.cityName || '';
        var country = stay.country || '';
        var total = rowTotal(adultsQty, adultSell, childQty, childSell, infantQty, infantSell, foc);
        var qty = adultsQty + childQty + infantQty;

        return {
            id: existingId || uid(),
            itemId: itemId,
            itemName: itemName,
            destination: city,
            city: city,
            country: country,
            currency: stay.currency || cfg().dmcCurrency || 'SGD',
            dateTime: dateVal,
            bookingDate: dateVal,
            quantity: qty,
            adultsQty: adultsQty,
            adultCost: adultSell,
            adultSell: adultSell,
            childQty: childQty,
            childCost: childSell,
            childSell: childSell,
            infantQty: infantQty,
            infantCost: infantSell,
            infantSell: infantSell,
            supplement: false,
            focServiceDiscount: foc,
            foc_service_discount: foc,
            totalPrice: total,
            dmc_id: stay.dmcId || cfg().dmcId || '',
            bookingType: 'enquiry'
        };
    }

    function addOrUpdateCard(root, stay, card) {
        if (!card) return;
        var dateEl = root.querySelector('.misc-date');
        var dateVal = dateEl ? String(dateEl.value || '').trim() : '';
        if (!dateVal) {
            window.alert('Please select a date for miscellaneous items.');
            return;
        }

        var adultsQty = parseInt((card.querySelector('.misc-adult-qty') || {}).value, 10) || 0;
        var childQty = parseInt((card.querySelector('.misc-child-qty') || {}).value, 10) || 0;
        var infantQty = parseInt((card.querySelector('.misc-infant-qty') || {}).value, 10) || 0;
        if (adultsQty + childQty + infantQty <= 0) {
            window.alert('Please set a quantity greater than 0.');
            return;
        }

        var itemId = String(card.getAttribute('data-item-id') || '');
        var rows = readChunk(root);
        var editing = root.__editingIdx != null && root.__editingIdx >= 0;

        if (editing) {
            var existing = rows[root.__editingIdx] || {};
            rows[root.__editingIdx] = buildPayloadFromCard(card, stay, dateVal, existing.id);
            root.__editingIdx = null;
        } else {
            if (itemId && rows.some(function (r) { return String(r.itemId || r.mis_id || '') === itemId; })) {
                window.alert('This item is already added. Remove or modify it from the list below.');
                return;
            }
            rows.push(buildPayloadFromCard(card, stay, dateVal, null));
        }

        writeChunk(root, rows);
        renderAdded(root);
        refreshPicker(root, stay);
        hideConfigure(root);
        var sel = root.querySelector('.misc-item-select');
        if (sel) {
            sel.value = '';
            if (typeof window.jQuery !== 'undefined') {
                try { window.jQuery(sel).val(null).trigger('change'); } catch (e) { /* ignore */ }
            }
        }
    }

    function renderAdded(root) {
        var T = S();
        var host = root.querySelector('[data-miscellaneous-added-list]');
        var countEl = root.querySelector('.misc-added-count');
        if (!host) return;
        var rows = readChunk(root);
        var cur = root.getAttribute('data-currency') || 'SGD';
        if (countEl) countEl.textContent = rows.length ? (rows.length + ' item' + (rows.length === 1 ? '' : 's')) : '';

        if (!rows.length) {
            host.innerHTML = '<div class="stp-lite-misc-empty stp-lite-misc-empty--soft">No items added yet.</div>';
            return;
        }

        var html = '<div class="stp-lite-misc-added-list">';
        rows.forEach(function (row, idx) {
            var editing = root.__editingIdx === idx;
            var foc = !!(row.focServiceDiscount || row.foc_service_discount);
            var qty = (Number(row.adultsQty) || 0) + (Number(row.childQty) || 0) + (Number(row.infantQty) || 0);
            if (row.quantity != null && Number(row.quantity) > qty) qty = Number(row.quantity);
            html +=
                '<div class="stp-lite-misc-added-card' + (editing ? ' is-editing' : '') + '" data-idx="' + idx + '">' +
                '  <div class="stp-lite-misc-added-card__main">' +
                '    <div class="stp-lite-misc-added-card__name">' +
                '      <strong>' + T.esc(row.itemName || 'Item') + '</strong>' +
                T.editingMarkHtml(editing) +
                '      <small>' + T.esc(row.bookingDate || row.dateTime || '') +
                (row.destination || row.city ? ' · ' + T.esc(row.destination || row.city) : '') +
                (row.country ? ' · ' + T.esc(row.country) : '') + '</small>' +
                '    </div>' +
                '    <div class="stp-lite-misc-added-card__meta">' +
                '      <span>Qty ' + T.esc(String(qty)) + '</span>' +
                '      <span>' + T.esc(row.adultsQty || 0) + 'A · ' + T.esc(row.childQty || 0) + 'C · ' + T.esc(row.infantQty || 0) + 'I</span>' +
                (foc ? '<span class="stp-lite-misc-badge-foc">FOC</span>' : '') +
                '    </div>' +
                '  </div>' +
                '  <div class="stp-lite-misc-added-card__side">' +
                '    <div class="stp-lite-misc-added-card__price">' + cur + ' ' + Number(row.totalPrice || 0).toFixed(2) + '</div>' +
                '    <div class="stp-lite-misc-added-card__actions">' + T.addedTableActions(PREFIX, idx, false) + '</div>' +
                '  </div>' +
                '</div>';
        });
        html += '</div>';
        host.innerHTML = html;
    }

    function hydratePickerFromRow(root, stay, row) {
        if (!row) return;
        var dateEl = root.querySelector('.misc-date');
        if (dateEl) dateEl.value = String(row.bookingDate || row.dateTime || stay.start || '').slice(0, 10);

        var itemId = String(row.itemId || row.mis_id || '');
        populateItemSelect(root, itemId);
        showConfigureForItem(root, stay, itemId, row);
        var card = root.querySelector('.stp-lite-misc-card');
        if (card) {
            try { card.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) { /* ignore */ }
        }
    }

    function adjustQty(input, delta) {
        if (!input) return;
        var next = Math.max(0, (parseInt(input.value, 10) || 0) + delta);
        input.value = String(next);
        input.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function bindShell(root, stay) {
        if (!root || root.__bound) return;
        root.__bound = true;
        var T = S();
        T.bindStayDate(root.querySelector('.misc-date'), stay);
        root.__stay = stay;

        loadItems(root, stay);
        renderAdded(root);

        root.addEventListener('click', function (e) {
            var dec = e.target.closest('.misc-qty-dec');
            if (dec) {
                var cardDec = dec.closest('.stp-lite-misc-card');
                var kindDec = dec.getAttribute('data-kind');
                adjustQty(cardDec && cardDec.querySelector('.misc-' + kindDec + '-qty'), -1);
                return;
            }
            var inc = e.target.closest('.misc-qty-inc');
            if (inc) {
                var cardInc = inc.closest('.stp-lite-misc-card');
                var kindInc = inc.getAttribute('data-kind');
                adjustQty(cardInc && cardInc.querySelector('.misc-' + kindInc + '-qty'), 1);
                return;
            }

            var addBtn = e.target.closest('.misc-add-one-btn, .misc-update-btn');
            if (addBtn) {
                addOrUpdateCard(root, stay, addBtn.closest('.stp-lite-misc-card'));
                return;
            }

            var cancelBtn = e.target.closest('.misc-cancel-edit-btn');
            if (cancelBtn) {
                root.__editingIdx = null;
                renderAdded(root);
                refreshPicker(root, stay);
                hideConfigure(root);
                var selCancel = root.querySelector('.misc-item-select');
                if (selCancel) {
                    selCancel.value = '';
                    if (typeof window.jQuery !== 'undefined') {
                        try { window.jQuery(selCancel).val(null).trigger('change'); } catch (err) { /* ignore */ }
                    }
                }
                return;
            }

            var removeBtn = e.target.closest('.miscellaneous-remove');
            if (removeBtn) {
                if (!(window.StpLiteTransportShared && window.StpLiteTransportShared.confirmRemoveService
                    ? window.StpLiteTransportShared.confirmRemoveService()
                    : window.confirm('Are you sure you want to remove this service?'))) return;
                var rows = readChunk(root);
                rows.splice(parseInt(removeBtn.getAttribute('data-idx'), 10) || 0, 1);
                writeChunk(root, rows);
                root.__editingIdx = null;
                renderAdded(root);
                refreshPicker(root, stay);
                return;
            }

            var editBtn = e.target.closest('.miscellaneous-edit-added');
            if (editBtn) {
                var idx = parseInt(editBtn.getAttribute('data-idx'), 10) || 0;
                var row = readChunk(root)[idx];
                if (!row) return;
                root.__editingIdx = idx;
                hydratePickerFromRow(root, stay, row);
                renderAdded(root);
            }
        });

        root.addEventListener('input', function (e) {
            var card = e.target && e.target.closest ? e.target.closest('.stp-lite-misc-card') : null;
            if (!card) return;
            if (e.target.matches('.misc-adult-qty, .misc-child-qty, .misc-infant-qty')) {
                var v = parseInt(e.target.value, 10);
                if (isNaN(v) || v < 0) e.target.value = '0';
                refreshCardTotal(card, root.getAttribute('data-currency') || stay.currency || 'SGD');
            }
        });

        root.addEventListener('change', function (e) {
            var card = e.target && e.target.closest ? e.target.closest('.stp-lite-misc-card') : null;
            if (!card) return;
            if (e.target.classList && e.target.classList.contains('misc-foc-discount')) {
                refreshCardTotal(card, root.getAttribute('data-currency') || stay.currency || 'SGD');
            }
        });
    }

    function mountAll(scope) {
        var T = S();
        (scope || document).querySelectorAll('[data-stp-miscellaneous-mount]').forEach(function (el) {
            var stay = T.stayFromPanel(el, 'miscellaneous');
            el.innerHTML = shellHtml(stay);
            bindShell(el.querySelector('.stp-lite-miscellaneous'), stay);
        });
    }

    window.StpLiteMiscellaneous = {
        mountAll: mountAll,
        seedAdded: function (root, rows) {
            if (!root) return;
            writeChunk(root, rows || []);
            renderAdded(root);
            refreshPicker(root, root.__stay || {});
        }
    };
})(window, document);
/* === END miscellaneous.js === */
