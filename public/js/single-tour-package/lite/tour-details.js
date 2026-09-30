/* === STP LITE: tour-details.js ===
 * Depends: moment, daterangepicker, select2, jQuery, geo.js, country-mode.js
 * Owns: travel dates; agency→agent; primary City multi-select (MDMC cities; badges in Select2)
 * Multi city → auto Multi Country (via country-mode sync)
 * === */
(function (window, document, $) {
    'use strict';

    function cfg() {
        return window.STP_LITE_CONFIG || {};
    }

    function mdmcCountrySet() {
        var set = {};
        var source = cfg().mdmcCountries || [];
        if (cfg().isRestrictedThirdParty && Array.isArray(cfg().ownDmcCountries) && cfg().ownDmcCountries.length) {
            source = cfg().ownDmcCountries;
        }
        (source || []).forEach(function (c) {
            var n = String((c && c.name) || c || '').trim().toLowerCase();
            if (n) set[n] = true;
        });
        return set;
    }

    function initDateRange() {
        var $input = $('#travel_dates');
        if (!$input.length || typeof $input.daterangepicker !== 'function') return;

        var locked = $input.attr('data-locked') === 'true';
        if (locked) return;

        var startVal = $('#start_date').val();
        var endVal = $('#end_date').val();

        var options = {
            autoUpdateInput: false,
            locale: { format: 'MMM D, YYYY', cancelLabel: 'Clear' },
            minDate: moment().startOf('day')
        };

        if (startVal && endVal) {
            options.startDate = moment(startVal, 'YYYY-MM-DD');
            options.endDate = moment(endVal, 'YYYY-MM-DD');
            $input.val(
                moment(startVal, 'YYYY-MM-DD').format('MMM D, YYYY') +
                ' - ' +
                moment(endVal, 'YYYY-MM-DD').format('MMM D, YYYY')
            );
        }

        $input.daterangepicker(options);

        $input.on('apply.daterangepicker', function (ev, picker) {
            $input.val(
                picker.startDate.format('MMM D, YYYY') +
                ' - ' +
                picker.endDate.format('MMM D, YYYY')
            );
            $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
            $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
            document.dispatchEvent(new CustomEvent('stp:dates-changed', {
                detail: {
                    start: picker.startDate.format('YYYY-MM-DD'),
                    end: picker.endDate.format('YYYY-MM-DD')
                }
            }));
        });

        $input.on('cancel.daterangepicker', function () {
            $input.val('');
            $('#start_date').val('');
            $('#end_date').val('');
            document.dispatchEvent(new CustomEvent('stp:dates-changed', {
                detail: { start: '', end: '', cleared: true }
            }));
        });
    }

    function getTourCityItems() {
        var $tc = $('#tour_cities');
        if (!$tc.length) return [];
        var items = [];
        if ($tc.data('select2')) {
            ($tc.select2('data') || []).forEach(function (item) {
                if (!item) return;
                var text = String(item.text || '');
                var country = item.country ? String(item.country).trim() : '';
                if (!country) {
                    var m = text.match(/\(([^)]+)\)\s*$/);
                    if (m && m[1]) country = String(m[1]).trim();
                }
                var name = text.replace(/\s*\([^)]*\)\s*$/, '').trim();
                items.push({
                    id: String(item.id != null ? item.id : ''),
                    name: name,
                    country: country,
                    text: text || name
                });
            });
        } else {
            Array.prototype.forEach.call($tc[0].selectedOptions || [], function (opt) {
                var text = String(opt.textContent || '');
                var country = String(opt.getAttribute('data-country') || '').trim();
                if (!country) {
                    var m = text.match(/\(([^)]+)\)\s*$/);
                    if (m && m[1]) country = String(m[1]).trim();
                }
                items.push({
                    id: String(opt.value || ''),
                    name: String(opt.getAttribute('data-city-name') || text.replace(/\s*\([^)]*\)\s*$/, '')).trim(),
                    country: country,
                    text: text
                });
            });
        }
        return items;
    }

    function clearTourCitiesSearch($tc) {
        // Select2 keeps typed search text after select unless cleared
        try {
            var $field = $tc.data('select2')
                ? $tc.data('select2').$dropdown.find('.select2-search__field')
                : $();
            if ($field && $field.length) $field.val('');
            var $inline = $tc.next('.select2-container').find('.select2-search__field');
            if ($inline.length) $inline.val('');
        } catch (e) { /* ignore */ }
    }

    function syncHiddenCityMirrors(items) {
        items = items || getTourCityItems();
        var $single = $('#single_city');
        var $multi = $('#multi_cities');
        var $liteCountries = $('#lite_countries');

        // Mirror options into hidden multi_cities / single_city
        if ($multi.length) {
            $multi.empty();
            items.forEach(function (item) {
                var $opt = $('<option></option>')
                    .attr('value', item.id)
                    .attr('data-city-name', item.name)
                    .attr('data-country', item.country)
                    .prop('selected', true)
                    .text(item.text || item.name);
                $multi.append($opt);
                if (item.name) window.rememberLiteCityGeo(item.name, item.country);
            });
        }

        if ($single.length) {
            $single.empty().append('<option value="">Select city...</option>');
            if (items.length === 1) {
                var one = items[0];
                $single.append(
                    $('<option></option>')
                        .attr('value', one.id)
                        .attr('data-city-name', one.name)
                        .attr('data-country', one.country)
                        .prop('selected', true)
                        .text(one.text || one.name)
                );
            }
        }

        // Infer countries from cities → lite_countries (hidden)
        var countries = [];
        var seen = {};
        items.forEach(function (item) {
            var c = String(item.country || '').trim();
            if (c && !seen[c]) {
                seen[c] = true;
                countries.push(c);
            }
        });
        if ($liteCountries.length) {
            // Ensure options exist
            countries.forEach(function (c) {
                if (!$liteCountries.find('option').filter(function () { return String($(this).val()) === c; }).length) {
                    $liteCountries.append($('<option></option>').attr('value', c).text(c));
                }
            });
            $liteCountries.val(countries);
        }

        var uc = document.getElementById('user_country');
        if (uc && countries.length) {
            uc.value = countries[0];
            var map = cfg().countryIdByName || {};
            var cid = document.getElementById('country_id');
            if (cid) cid.value = map[countries[0]] || '';
        }

        document.dispatchEvent(new CustomEvent('stp:cities-changed', {
            detail: { cities: items, countries: countries }
        }));

        if (window.StpLiteCountryMode && typeof window.StpLiteCountryMode.sync === 'function') {
            window.StpLiteCountryMode.sync();
        }
    }

    function initTourCitiesSelect() {
        var $tc = $('#tour_cities');
        if (!$tc.length || !$.fn.select2) return;

        var allowed = mdmcCountrySet();

        $tc.select2({
            placeholder: 'Select city…',
            allowClear: true,
            width: '100%',
            closeOnSelect: true, // close + clear search after each pick; badges stay in the input
            ajax: {
                url: cfg().routes.ajaxCities,
                dataType: 'json',
                delay: 200,
                data: function (params) {
                    return { q: params.term || '' };
                },
                processResults: function (data) {
                    var results = (data && data.results) ? data.results.slice() : [];
                    // Only cities whose country is in Master DMC list
                    if (Object.keys(allowed).length) {
                        results = results.filter(function (row) {
                            var c = String(row.country || '').toLowerCase();
                            if (!c && row.text) {
                                var m = String(row.text).match(/\(([^)]+)\)\s*$/);
                                if (m) c = String(m[1]).toLowerCase().trim();
                            }
                            return !c || !!allowed[c];
                        });
                    }
                    return { results: results };
                },
                cache: true
            }
        });

        $tc.on('select2:select', function () {
            clearTourCitiesSearch($tc);
            // Ensure dropdown closes even if Select2 keeps it open after ajax select
            try { $tc.select2('close'); } catch (e) { /* ignore */ }
        });

        $tc.on('select2:selecting', function (e) {
            if (!cfg().isRestrictedThirdParty) return;
            var data = e.params && e.params.args && e.params.args.data ? e.params.args.data : {};
            var country = String(data.country || '').trim();
            if (!country && data.text) {
                var mSel = String(data.text).match(/\(([^)]+)\)\s*$/);
                if (mSel) country = String(mSel[1]).trim();
            }
            if (country && window.isForeignLockedCountry && window.isForeignLockedCountry(country)) {
                e.preventDefault();
                alert('Third party access is disabled: you can only add cities in this DMC country.');
            }
        });

        $tc.on('select2:unselecting', function (e) {
            if (!cfg().isRestrictedThirdParty) return;
            if (cfg().mode !== 'edit') return;
            var data = e.params && e.params.args && e.params.args.data ? e.params.args.data : {};
            var country = String(data.country || '').trim();
            if (!country) {
                var el = e.params && e.params.args && e.params.args.element;
                if (el) country = String(el.getAttribute('data-country') || '').trim();
            }
            if (!country && data.text) {
                var mUn = String(data.text).match(/\(([^)]+)\)\s*$/);
                if (mUn) country = String(mUn[1]).trim();
            }
            if (country && window.isForeignLockedCountry && window.isForeignLockedCountry(country)) {
                e.preventDefault();
                alert("You cannot remove another country's city on this tour.");
            }
        });

        $tc.on('change select2:select select2:clear select2:unselect', function () {
            // Stamp data-country on options for later reads
            if ($tc.data('select2')) {
                ($tc.select2('data') || []).forEach(function (item) {
                    if (!item) return;
                    var country = item.country ? String(item.country).trim() : '';
                    var text = String(item.text || '');
                    if (!country) {
                        var m = text.match(/\(([^)]+)\)\s*$/);
                        if (m && m[1]) country = String(m[1]).trim();
                    }
                    var name = text.replace(/\s*\([^)]*\)\s*$/, '').trim();
                    var $opt = $tc.find('option').filter(function () {
                        return String($(this).val()) === String(item.id);
                    });
                    if ($opt.length) {
                        if (country) $opt.attr('data-country', country);
                        if (name) $opt.attr('data-city-name', name);
                    }
                    if (name) window.rememberLiteCityGeo(name, country);
                });
            }
            clearTourCitiesSearch($tc);
            syncHiddenCityMirrors();
        });
    }

    function isAgentSelectLocked() {
        var agentSelect = document.getElementById('agent_id');
        return !!(agentSelect && agentSelect.hasAttribute('disabled'));
    }

    function renderAgentSelectAddContactButton() {
        if (isAgentSelectLocked()) return;
        var agencyId = $('#agency_id').val();
        if (!agencyId) return;

        var $agent = $('#agent_id');
        var select2 = $agent.data('select2');
        if (!select2 || !select2.$dropdown) return;

        var $results = select2.$dropdown.find('.select2-results');
        $results.find('.add-agency-contact-wrap').remove();

        var $wrap = $('<div class="add-agency-contact-wrap border-bottom p-2"></div>');
        var $btn = $('<button type="button" class="btn btn-sm w-100 add-agency-contact-btn"><i class="ri-user-add-line me-1"></i>Add Agency Contact</button>');
        $wrap.css({ background: '#e8f7ff', borderBottom: '1px solid #b8e8ff' });
        $btn.css({
            color: '#ffffff',
            background: '#18C1FF',
            border: '1px solid #566f79',
            borderRadius: '8px',
            fontWeight: '600',
            fontSize: '0.82rem'
        });
        $btn.on('mousedown', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $agent.select2('close');
            setTimeout(openAddAgencyContactModal, 0);
        });
        $wrap.append($btn);
        $results.prepend($wrap);
    }

    function bindAgentSelectAddContactUi() {
        var $agent = $('#agent_id');
        $agent.off('select2:open.agentAdd select2:results:message.agentAdd select2:results:all.agentAdd')
            .on('select2:open.agentAdd select2:results:message.agentAdd select2:results:all.agentAdd', function () {
                setTimeout(renderAgentSelectAddContactButton, 0);
            });
    }

    function initAgentSelect2() {
        var $agent = $('#agent_id');
        if (!$agent.length || !$.fn.select2) return;

        if ($agent.hasClass('select2-hidden-accessible')) {
            $agent.select2('destroy');
        }

        $agent.select2({
            placeholder: 'Choose agency contact...',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function () {
                    return '';
                }
            },
            escapeMarkup: function (markup) {
                return markup;
            }
        });
        bindAgentSelectAddContactUi();
    }

    function openAddAgencyContactModal() {
        if (isAgentSelectLocked()) return;

        var agencyId = $('#agency_id').val();
        if (!agencyId) {
            alert('Please select an agency company first.');
            return;
        }

        var agencyName = $('#agency_id option:selected').text() || '';
        var form = document.getElementById('addAgencyContactForm');
        var errorsEl = document.getElementById('quickAgencyContactErrors');
        if (form) form.reset();
        if (errorsEl) {
            errorsEl.innerHTML = '';
            errorsEl.classList.add('d-none');
        }

        var agencyIdInput = document.getElementById('quickAgencyContactAgencyId');
        var agencyNameEl = document.getElementById('quickAgencyContactAgencyName');
        if (agencyIdInput) agencyIdInput.value = agencyId;
        if (agencyNameEl) agencyNameEl.textContent = agencyName;

        var modalEl = document.getElementById('addAgencyContactModal');
        if (!modalEl) {
            alert('Unable to open add contact form. Please refresh the page and try again.');
            return;
        }
        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else if (window.jQuery && $(modalEl).modal) {
            $(modalEl).modal('show');
        }
    }

    function saveQuickAgencyContact() {
        var form = document.getElementById('addAgencyContactForm');
        var errorsEl = document.getElementById('quickAgencyContactErrors');
        var saveBtn = document.getElementById('saveQuickAgencyContactBtn');
        if (!form) {
            alert('Contact form not found. Please refresh the page.');
            return;
        }

        // HTML5 required checks (nested-form browsers may skip these)
        if (typeof form.reportValidity === 'function' && !form.reportValidity()) {
            return;
        }

        if (errorsEl) {
            errorsEl.innerHTML = '';
            errorsEl.classList.add('d-none');
        }

        var formData = new FormData(form);
        var agencyId = formData.get('agency_id') || (document.getElementById('quickAgencyContactAgencyId') || {}).value || '';
        if (!agencyId) {
            // Re-read from agency select if hidden was cleared
            agencyId = $('#agency_id').val() || '';
            var agencyIdInput = document.getElementById('quickAgencyContactAgencyId');
            if (agencyIdInput) agencyIdInput.value = agencyId;
            formData.set('agency_id', agencyId);
        }
        if (!agencyId) {
            alert('Please select an agency company first.');
            return;
        }

        // Backend validates phone as numeric — strip spaces / symbols
        var phoneRaw = String(formData.get('phone') || '');
        var phoneDigits = phoneRaw.replace(/[^\d]/g, '');
        if (phoneDigits) {
            formData.set('phone', phoneDigits);
            var phoneInput = form.querySelector('input[name="phone"]');
            if (phoneInput) phoneInput.value = phoneDigits;
        }

        var url = (cfg().routes && cfg().routes.agentsQuickStore) || '';
        if (!url) {
            alert('Save contact route is not configured.');
            return;
        }

        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': cfg().csrfToken || (document.querySelector('meta[name="csrf-token"]') || {}).content || ''
            },
            body: formData
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, status: response.status, data: data };
                }).catch(function () {
                    return { ok: false, status: response.status, data: { message: 'Invalid server response (' + response.status + ').' } };
                });
            })
            .then(function (result) {
                if (!result.ok || !result.data || !result.data.success) {
                    var html = '';
                    var errors = (result.data && result.data.errors) ? result.data.errors : null;
                    if (errors) {
                        Object.keys(errors).forEach(function (key) {
                            (errors[key] || []).forEach(function (msg) {
                                html += '<div>' + msg + '</div>';
                            });
                        });
                    } else {
                        html = '<div>' + ((result.data && result.data.message) ? result.data.message : 'Failed to add agency contact.') + '</div>';
                    }
                    if (errorsEl) {
                        errorsEl.innerHTML = html;
                        errorsEl.classList.remove('d-none');
                    } else {
                        alert(html.replace(/<[^>]+>/g, ' '));
                    }
                    return;
                }

                var modalEl = document.getElementById('addAgencyContactModal');
                if (modalEl && window.bootstrap && bootstrap.Modal) {
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                } else if (modalEl && window.jQuery && $(modalEl).modal) {
                    $(modalEl).modal('hide');
                }

                var newAgentId = result.data.agent ? result.data.agent.agent_id : null;
                return loadAgentsByAgencyId(agencyId, newAgentId);
            })
            .catch(function (err) {
                console.error('saveQuickAgencyContact', err);
                if (errorsEl) {
                    errorsEl.innerHTML = '<div>Failed to add agency contact. Please try again.</div>';
                    errorsEl.classList.remove('d-none');
                } else {
                    alert('Failed to add agency contact. Please try again.');
                }
            })
            .finally(function () {
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="ri-save-line me-1"></i>Save Contact';
                }
            });
    }

    // Expose immediately so modal onclick works even before init()
    window.saveQuickAgencyContact = saveQuickAgencyContact;
    window.openAddAgencyContactModal = openAddAgencyContactModal;

    function loadAgentsByAgencyId(agencyId, selectedAgentId) {
        var agentSelect = document.getElementById('agent_id');
        var $agent = $('#agent_id');
        var locked = isAgentSelectLocked();
        if (!agentSelect) return Promise.resolve();

        var url = (cfg().routes && cfg().routes.fetchAgentsByAgency) || '';
        if (!url) return Promise.resolve();

        agentSelect.innerHTML = '<option value="">Loading agency contacts...</option>';
        $agent.val(null).trigger('change');
        if (!locked) $agent.prop('disabled', true);

        return fetch(url + '?agency_id=' + encodeURIComponent(agencyId), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': cfg().csrfToken || ''
            }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var html = '<option value="">Choose agency contact...</option>';
                if (data.success && data.agents && data.agents.length) {
                    data.agents.forEach(function (agent) {
                        html += '<option value="' + agent.agent_id + '" data-agency="' + agencyId + '">'
                            + (agent.name || '') + '</option>';
                    });
                }
                if ($agent.hasClass('select2-hidden-accessible')) {
                    $agent.select2('destroy');
                }
                $agent.html(html);
                initAgentSelect2();
                $agent.prop('disabled', !!locked);
                if (selectedAgentId) {
                    $agent.val(String(selectedAgentId)).trigger('change');
                }
            })
            .catch(function () {
                agentSelect.innerHTML = '<option value="">Error loading agency contacts</option>';
                initAgentSelect2();
                $agent.prop('disabled', !!locked);
            });
    }

    function initAgencyAgent() {
        var $agency = $('#agency_id');
        var $agent = $('#agent_id');
        if (!$agency.length || !$agent.length) return;

        // Snapshot contacts from blade (data-agency) — filter locally until refresh
        var cache = [];
        $agent.find('option').each(function () {
            if (!this.value) return;
            cache.push({
                value: this.value,
                text: this.textContent,
                agency: String(this.getAttribute('data-agency') || '')
            });
        });

        if ($.fn.select2) {
            $agency.select2({ placeholder: 'Choose agency...', allowClear: true, width: '100%' });
        }

        function fillAgents(agencyId, selectedId) {
            if ($agent.prop('disabled') && document.querySelector('input[type="hidden"][name="agent_id"]')) {
                return;
            }
            var html = '<option value="">Choose agency contact...</option>';
            cache.forEach(function (a) {
                if (agencyId && a.agency === String(agencyId)) {
                    html += '<option value="' + a.value + '" data-agency="' + a.agency + '">' + a.text + '</option>';
                }
            });
            if ($agent.hasClass('select2-hidden-accessible')) {
                $agent.select2('destroy');
            }
            $agent.html(html);
            initAgentSelect2();
            if (selectedId) {
                $agent.val(String(selectedId)).trigger('change');
            } else {
                $agent.val(null).trigger('change');
            }
        }

        $agency.off('change.liteAgency').on('change.liteAgency', function () {
            fillAgents($(this).val(), null);
        });

        $('#saveQuickAgencyContactBtn').off('click.liteAgency').on('click.liteAgency', saveQuickAgencyContact);

        $(document).off('mousedown.agentAdd', '.add-agency-contact-btn')
            .on('mousedown.agentAdd', '.add-agency-contact-btn', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $('#agent_id').select2('close');
                setTimeout(openAddAgencyContactModal, 0);
            });

        $(document).off('input.agentAdd', '.select2-container--open .select2-search__field')
            .on('input.agentAdd', '.select2-container--open .select2-search__field', function () {
                if ($(this).closest('.select2-container').prev('#agent_id').length) {
                    setTimeout(renderAgentSelectAddContactButton, 0);
                }
            });

        // Filter immediately (empty agency → placeholder only)
        fillAgents($agency.val() || '', $agent.val() || null);

        window.openAddAgencyContactModal = openAddAgencyContactModal;
        window.saveQuickAgencyContact = saveQuickAgencyContact;
    }

    function loadAgents(agencyId, selectedAgentId) {
        var $agency = $('#agency_id');
        var $agent = $('#agent_id');
        if (!$agency.length || !$agent.length) return Promise.resolve();
        if (agencyId != null && String($agency.val()) !== String(agencyId)) {
            $agency.val(agencyId).trigger('change.select2');
        }
        if (agencyId) {
            return loadAgentsByAgencyId(agencyId, selectedAgentId);
        }
        $agency.trigger('change');
        if (selectedAgentId) {
            $agent.val(String(selectedAgentId)).trigger('change');
        }
        return Promise.resolve();
    }

    function syncGroupDetailsFields() {
        var adults = parseInt(($('#adults').val() || '0'), 10) || 0;
        var children = parseInt(($('#children').val() || '0'), 10) || 0;
        var foc = parseInt(($('#foc_size').val() || '0'), 10) || 0;
        var total = adults + children;
        $('#group_size').val(String(total));
        $('#group_size_display').val(String(total));
        var includeFoc = $('#include_foc_in_group_price').is(':checked');
        $('#discount').val(includeFoc ? '1' : '0');
        var paying = includeFoc ? Math.max(0, total - foc) : total;
        $('#paying_pax').val(String(paying));
        $('#total_pax_display').val(String(total));
    }

    function initGroupDetails() {
        $('#foc_size, #include_foc_in_group_price').on('change input', syncGroupDetailsFields);
        document.addEventListener('stp:guests-changed', syncGroupDetailsFields);
        document.addEventListener('stp:tour-type-changed', syncGroupDetailsFields);
        syncGroupDetailsFields();
    }

    function init() {
        initDateRange();
        initTourCitiesSelect();
        initAgencyAgent();
        initGroupDetails();
        syncHiddenCityMirrors();
    }

    window.StpLiteTourDetails = {
        init: init,
        loadAgents: loadAgents,
        getTourCityItems: getTourCityItems,
        syncHiddenCityMirrors: syncHiddenCityMirrors,
        syncGroupDetailsFields: syncGroupDetailsFields
    };
})(window, document, window.jQuery);
/* === END tour-details.js === */
