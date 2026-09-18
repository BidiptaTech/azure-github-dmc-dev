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
        (cfg().mdmcCountries || []).forEach(function (c) {
            var n = String(c.name || c || '').trim().toLowerCase();
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

    function initAgencyAgent() {
        var $agency = $('#agency_id');
        var $agent = $('#agent_id');
        if (!$agency.length || !$agent.length) return;

        // Snapshot contacts from blade (data-agency) — filter locally, no AJAX
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
            $agent.select2({ placeholder: 'Choose agency contact...', allowClear: true, width: '100%' });
        }

        function fillAgents(agencyId, selectedId) {
            if ($agent.prop('disabled') && document.querySelector('input[type="hidden"][name="agent_id"]')) {
                return;
            }
            var html = '<option value="">Choose agency contact...</option>';
            cache.forEach(function (a) {
                if (agencyId && a.agency === String(agencyId)) {
                    html += '<option value="' + a.value + '">' + a.text + '</option>';
                }
            });
            if ($agent.hasClass('select2-hidden-accessible')) {
                $agent.select2('destroy');
            }
            $agent.html(html);
            if ($.fn.select2) {
                $agent.select2({ placeholder: 'Choose agency contact...', allowClear: true, width: '100%' });
            }
            if (selectedId) {
                $agent.val(String(selectedId)).trigger('change');
            } else {
                $agent.val(null).trigger('change');
            }
        }

        $agency.on('change', function () {
            fillAgents($(this).val(), null);
        });

        // Filter immediately (empty agency → placeholder only)
        fillAgents($agency.val() || '', $agent.val() || null);
    }

    function loadAgents(agencyId, selectedAgentId) {
        var $agency = $('#agency_id');
        var $agent = $('#agent_id');
        if (!$agency.length || !$agent.length) return Promise.resolve();
        if (agencyId != null && String($agency.val()) !== String(agencyId)) {
            $agency.val(agencyId);
        }
        // Re-run filter with optional preselect (init already bound fill via change)
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
