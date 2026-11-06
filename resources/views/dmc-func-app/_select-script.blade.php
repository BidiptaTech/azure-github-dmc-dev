<script>
    (function () {
        const palette = @json(\App\Models\DmcFuncApp::badgePalette());
        const $functionName = $('#function_name');
        const $maxLimit = $('#maximum_limit');
        const $select = $('#dmc_id');
        const $hint = $('#dmcSlotHint');
        const $preview = $('#assignedDmcPreview');
        const $search = $('#dmcComboSearch');
        const $dropdown = $('#dmcComboDropdown');
        const $toggle = $('#dmcComboToggle');

        if (!$select.length) {
            return;
        }

        function colorForId(id) {
            return palette[Math.abs(parseInt(id, 10) || 0) % palette.length];
        }

        function escapeHtml(text) {
            return $('<div>').text(text || '').html();
        }

        function selectedIds() {
            return ($select.val() || []).map(function (id) { return String(id); });
        }

        function setSelectedIds(ids) {
            $select.val(ids).trigger('change');
        }

        function maxLimit() {
            return parseInt($maxLimit.val(), 10) || 0;
        }

        function optionMap() {
            const items = [];
            $select.find('option').each(function () {
                items.push({
                    id: String($(this).val()),
                    label: $(this).text().trim()
                });
            });
            return items;
        }

        function renderBadges() {
            const ids = selectedIds();
            if (!ids.length) {
                $preview.html('<span class="text-muted small">No DMCs assigned yet. Use the search box below to add.</span>');
                return;
            }

            const html = ids.map(function (id) {
                const label = $select.find('option[value="' + id + '"]').text().trim() || ('DMC #' + id);
                const color = colorForId(id);
                return '<span class="dmc-color-badge" data-id="' + escapeHtml(id) + '" style="background:' + color.bg + ';color:' + color.color + ';">'
                    + escapeHtml(label)
                    + '<button type="button" class="dmc-badge-remove" data-id="' + escapeHtml(id) + '" aria-label="Remove">&times;</button>'
                    + '</span>';
            }).join('');

            $preview.html(html);
        }

        function updateHint() {
            $hint.text(selectedIds().length + ' / ' + maxLimit() + ' selected');
        }

        function availableItems(query) {
            const selected = selectedIds();
            const q = (query || '').toLowerCase();
            return optionMap().filter(function (item) {
                if (selected.indexOf(item.id) !== -1) {
                    return false;
                }
                return !q || item.label.toLowerCase().indexOf(q) !== -1;
            });
        }

        function renderDropdown() {
            const max = maxLimit();
            const selectedCount = selectedIds().length;
            if (max > 0 && selectedCount >= max) {
                $dropdown.html('<div class="dmc-combo-empty">Maximum DMC limit reached (' + max + ').</div>').removeClass('d-none');
                return;
            }

            const items = availableItems($search.val());
            if (!items.length) {
                $dropdown.html('<div class="dmc-combo-empty">No matching DMCs found.</div>').removeClass('d-none');
                return;
            }

            const html = items.map(function (item) {
                return '<button type="button" class="dmc-combo-item" data-id="' + escapeHtml(item.id) + '">' + escapeHtml(item.label) + '</button>';
            }).join('');

            $dropdown.html(html).removeClass('d-none');
        }

        function openDropdown() {
            renderDropdown();
        }

        function closeDropdown() {
            $dropdown.addClass('d-none').empty();
        }

        function addDmc(id) {
            const max = maxLimit();
            const ids = selectedIds();
            id = String(id);

            if (ids.indexOf(id) !== -1) {
                return;
            }
            if (max > 0 && ids.length >= max) {
                alert('This method has reached its maximum DMC limit (' + max + ').');
                return;
            }

            ids.push(id);
            setSelectedIds(ids);
            $search.val('');
            renderBadges();
            updateHint();
            renderDropdown();
        }

        function removeDmc(id) {
            const ids = selectedIds().filter(function (value) { return value !== String(id); });
            setSelectedIds(ids);
            renderBadges();
            updateHint();
            if (!$dropdown.hasClass('d-none')) {
                renderDropdown();
            }
        }

        $functionName.on('input', function () {
            this.value = this.value.replace(/[^A-Za-z0-9_]/g, '');
        });

        $preview.on('click', '.dmc-badge-remove', function (e) {
            e.preventDefault();
            removeDmc($(this).data('id'));
        });

        $search.on('focus click', function () {
            openDropdown();
        });

        $search.on('input', function () {
            openDropdown();
        });

        $toggle.on('click', function (e) {
            e.preventDefault();
            if ($dropdown.hasClass('d-none')) {
                $search.trigger('focus');
                openDropdown();
            } else {
                closeDropdown();
            }
        });

        $dropdown.on('click', '.dmc-combo-item', function (e) {
            e.preventDefault();
            addDmc($(this).data('id'));
        });

        $search.on('keydown', function (e) {
            if (e.key === 'Escape') {
                closeDropdown();
                return;
            }
            if (e.key === 'Enter') {
                e.preventDefault();
                const first = $dropdown.find('.dmc-combo-item').first();
                if (first.length) {
                    addDmc(first.data('id'));
                }
            }
        });

        $(document).on('click.dmcPicker', function (e) {
            if (!$(e.target).closest('.dmc-combo, #assignedDmcPreview').length) {
                closeDropdown();
            }
        });

        $maxLimit.on('input change', function () {
            const max = maxLimit();
            const ids = selectedIds();
            if (max > 0 && ids.length > max) {
                setSelectedIds(ids.slice(0, max));
                alert('Selected DMCs were reduced to the new maximum limit.');
            }
            renderBadges();
            updateHint();
        });

        renderBadges();
        updateHint();
    })();
</script>
