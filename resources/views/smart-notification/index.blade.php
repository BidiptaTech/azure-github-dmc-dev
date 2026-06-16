@extends('layouts.layout')

@section('title', 'Smart Notification')

@push('css')
<style>
    .smart-multiselect {
        position: relative;
    }
    .smart-multiselect-trigger {
        min-height: 38px;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        background: #fff;
        color: #566a7f;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.4375rem 0.875rem;
        width: 100%;
        cursor: pointer;
        text-align: left;
    }
    .smart-multiselect-trigger:focus {
        outline: none;
        border-color: #696cff;
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
    }
    .smart-multiselect-text {
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        margin-right: 8px;
    }
    .smart-multiselect-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        box-shadow: 0 0.25rem 1rem rgba(67, 89, 113, 0.12);
        max-height: 230px;
        overflow-y: auto;
        z-index: 1090;
        display: none;
    }
    .smart-multiselect-dropdown.open {
        display: block;
    }
    .smart-multiselect-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        cursor: pointer;
        margin: 0;
        width: 100%;
    }
    .smart-multiselect-option:hover {
        background: #f5f5f9;
    }
    .smart-multiselect-option input {
        margin: 0;
    }
    .smart-multiselect-empty {
        padding: 10px 12px;
        color: #a1acb8;
        font-size: 0.875rem;
        margin: 0;
    }
    .smart-multiselect-toggle-icon {
        transition: transform 0.2s ease;
    }
    .smart-multiselect-dropdown.open + .smart-multiselect-trigger .smart-multiselect-toggle-icon,
    .smart-multiselect-trigger.open .smart-multiselect-toggle-icon {
        transform: rotate(180deg);
    }
    .smart-option-tooltip {
        position: fixed;
        z-index: 2000;
        min-width: 220px;
        max-width: 300px;
        padding: 10px 12px;
        background: #233446;
        color: #fff;
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(67, 89, 113, 0.35);
        pointer-events: none;
        display: none;
        font-size: 0.8125rem;
        line-height: 1.45;
    }
    .smart-option-tooltip.show {
        display: block;
    }
    .smart-option-tooltip-row + .smart-option-tooltip-row {
        margin-top: 4px;
    }
    .smart-option-tooltip-label {
        color: #c8d2dc;
        margin-right: 4px;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Smart Notification /</span> Send Notification
    </h4>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Smart Notification</h5>
        </div>
        <div class="card-body">
            <form id="smartNotificationForm">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-select" required>
                            <option value="">Select Type</option>
                            @if($isAdminOrAgentRole)
                                <option value="dmc" {{ old('type') === 'dmc' ? 'selected' : '' }}>DMC</option>
                                <option value="agents" {{ old('type') === 'agents' ? 'selected' : '' }}>Agents</option>
                                <option value="operations" {{ old('type') === 'operations' ? 'selected' : '' }}>Operation</option>
                            @else
                                <option value="agents" {{ old('type') === 'agents' ? 'selected' : '' }}>Agents</option>
                                <option value="guests" {{ old('type') === 'guests' ? 'selected' : '' }}>Guests</option>
                                <option value="drivers" {{ old('type') === 'drivers' ? 'selected' : '' }}>Drivers</option>
                                <option value="guides" {{ old('type') === 'guides' ? 'selected' : '' }}>Guides</option>
                            @endif
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Sending To <span class="text-danger">*</span></label>
                        <div id="sendingToMultiSelect" class="smart-multiselect">
                            <button type="button" id="sendingToTrigger" class="smart-multiselect-trigger" aria-expanded="false">
                                <span id="sendingToDisplayText" class="smart-multiselect-text">All</span>
                                <i class="ri-arrow-down-s-line smart-multiselect-toggle-icon"></i>
                            </button>
                            <div id="sendingToDropdown" class="smart-multiselect-dropdown"></div>
                            <div id="sendingToHiddenInputs"></div>
                            <div id="sendingToTooltip" class="smart-option-tooltip"></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="notification_title" class="form-label">Notification Title <span class="text-danger">*</span></label>
                        <input
                            type="text"
                            name="notification_title"
                            id="notification_title"
                            class="form-control"
                            value="{{ old('notification_title') }}"
                            placeholder="Enter notification title"
                            required
                        >
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea
                            name="message"
                            id="message"
                            class="form-control"
                            rows="5"
                            placeholder="Enter your message"
                            required
                        >{{ old('message') }}</textarea>
                    </div>
                </div>

                <div id="smartNotificationAlert" class="alert d-none" role="alert"></div>

                <div class="d-flex justify-content-end">
                    <x-button-spinner id="sendNotificationBtn" label="Send" loading-text="Sending..." />
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const recipientsUrl = @json(route('smart-notification.recipients'));
    const sendUrl = @json(route('smart-notification.send'));
    const $type = $('#type');
    const $form = $('#smartNotificationForm');
    const $alert = $('#smartNotificationAlert');
    const $sendBtn = $('#sendNotificationBtn');
    const $component = $('#sendingToMultiSelect');
    const $trigger = $('#sendingToTrigger');
    const $dropdown = $('#sendingToDropdown');
    const $display = $('#sendingToDisplayText');
    const $hiddenInputs = $('#sendingToHiddenInputs');
    const $tooltip = $('#sendingToTooltip');

    let options = [{ id: 'all', name: 'All', tooltip: [] }];
    let selectedValues = ['all'];

    function escapeHtml(value) {
        return $('<div>').text(value).html();
    }

    function isOpen() {
        return $dropdown.hasClass('open');
    }

    function openDropdown() {
        $dropdown.addClass('open');
        $trigger.addClass('open').attr('aria-expanded', 'true');
    }

    function closeDropdown() {
        $dropdown.removeClass('open');
        $trigger.removeClass('open').attr('aria-expanded', 'false');
        hideTooltip();
    }

    function hideTooltip() {
        $tooltip.removeClass('show').empty();
    }

    function buildTooltipHtml(tooltipRows) {
        if (!tooltipRows || !tooltipRows.length) {
            return '';
        }

        return tooltipRows.map(function (row) {
            return (
                '<div class="smart-option-tooltip-row">' +
                    '<span class="smart-option-tooltip-label">' + escapeHtml(row.label) + ':</span>' +
                    '<span>' + escapeHtml(row.value) + '</span>' +
                '</div>'
            );
        }).join('');
    }

    function showTooltip(option, event) {
        if (!option || !option.tooltip || !option.tooltip.length) {
            hideTooltip();
            return;
        }

        $tooltip.html(buildTooltipHtml(option.tooltip)).addClass('show');

        const offset = 12;
        let left = event.clientX + offset;
        let top = event.clientY + offset;
        const tooltipWidth = $tooltip.outerWidth();
        const tooltipHeight = $tooltip.outerHeight();

        if (left + tooltipWidth > window.innerWidth - 8) {
            left = event.clientX - tooltipWidth - offset;
        }

        if (top + tooltipHeight > window.innerHeight - 8) {
            top = event.clientY - tooltipHeight - offset;
        }

        $tooltip.css({ left: left + 'px', top: top + 'px' });
    }

    function renderHiddenInputs() {
        $hiddenInputs.empty();
        selectedValues.forEach(function (value) {
            $hiddenInputs.append('<input type="hidden" name="sending_to[]" value="' + escapeHtml(value) + '">');
        });
    }

    function renderDisplayValue() {
        if (selectedValues.includes('all')) {
            $display.text('All');
            return;
        }

        const selectedNames = options
            .filter(function (option) { return selectedValues.includes(String(option.id)); })
            .map(function (option) { return option.name; });

        if (!selectedNames.length) {
            $display.text('All');
            selectedValues = ['all'];
            renderHiddenInputs();
            return;
        }

        $display.text(selectedNames.join(', '));
    }

    function normalizeSelections(changedId) {
        if (changedId === 'all') {
            selectedValues = ['all'];
            return;
        }

        selectedValues = selectedValues.filter(function (value) {
            return value !== 'all';
        });

        if (!selectedValues.length) {
            selectedValues = ['all'];
        }
    }

    function renderDropdownOptions() {
        const optionHtml = options.map(function (option) {
            const id = String(option.id);
            const isChecked = selectedValues.includes(id);

            return (
                '<label class="smart-multiselect-option" data-option-id="' + escapeHtml(id) + '">' +
                    '<input type="checkbox" class="sending-to-checkbox" data-id="' + escapeHtml(id) + '" ' + (isChecked ? 'checked' : '') + '>' +
                    '<span>' + escapeHtml(option.name) + '</span>' +
                '</label>'
            );
        }).join('');

        $dropdown.html(optionHtml || '<p class="smart-multiselect-empty">No recipients found</p>');
    }

    function refreshUI() {
        renderDropdownOptions();
        renderDisplayValue();
        renderHiddenInputs();
    }

    function setOptions(recipients) {
        options = [{ id: 'all', name: 'All', tooltip: [] }].concat(
            (recipients || []).map(function (recipient) {
                return {
                    id: String(recipient.id),
                    name: recipient.name,
                    tooltip: recipient.tooltip || [],
                };
            })
        );
        selectedValues = ['all'];
        refreshUI();
    }

    function loadRecipients(type) {
        if (!type) {
            setOptions([]);
            return;
        }
        closeDropdown();
        $trigger.prop('disabled', true);

        $.get(recipientsUrl, { type: type })
            .done(function (response) {
                const recipients = (response && response.recipients) ? response.recipients : [];
                setOptions(recipients);
            })
            .fail(function () {
                setOptions([]);
                alert('Unable to load recipients. Please try again.');
            })
            .always(function () {
                $trigger.prop('disabled', false);
            });
    }

    $type.on('change', function () {
        loadRecipients($(this).val());
    });

    $trigger.on('click', function (event) {
        event.stopPropagation();
        if ($trigger.prop('disabled')) {
            return;
        }

        if (isOpen()) {
            closeDropdown();
        } else {
            openDropdown();
        }
    });

    $dropdown.on('mouseenter', '.smart-multiselect-option', function (event) {
        const optionId = String($(this).data('option-id'));
        const option = options.find(function (item) {
            return String(item.id) === optionId;
        });

        showTooltip(option, event);
    });

    $dropdown.on('mousemove', '.smart-multiselect-option', function (event) {
        const optionId = String($(this).data('option-id'));
        const option = options.find(function (item) {
            return String(item.id) === optionId;
        });

        if (option && option.tooltip && option.tooltip.length) {
            showTooltip(option, event);
        }
    });

    $dropdown.on('mouseleave', '.smart-multiselect-option', function () {
        hideTooltip();
    });

    $dropdown.on('click', '.sending-to-checkbox', function (event) {
        event.stopPropagation();
        const changedId = String($(this).data('id'));
        const checked = $(this).is(':checked');

        if (checked) {
            if (!selectedValues.includes(changedId)) {
                selectedValues.push(changedId);
            }
        } else {
            selectedValues = selectedValues.filter(function (value) {
                return value !== changedId;
            });
        }

        normalizeSelections(changedId);
        refreshUI();
    });

    $(document).on('click', function (event) {
        if (!$component.is(event.target) && $component.has(event.target).length === 0) {
            closeDropdown();
        }
    });

    function showAlert(type, message) {
        $alert
            .removeClass('d-none alert-success alert-danger')
            .addClass(type === 'success' ? 'alert-success' : 'alert-danger')
            .text(message);
    }

    function resetSendButton() {
        const icon = $sendBtn.find('.btn-spinner-icon');
        const label = $sendBtn.find('.btn-spinner-label');

        if (icon.length) {
            icon.addClass('d-none');
        }

        if (label.length) {
            label.text('Send');
        }

        $sendBtn.prop('disabled', false);
    }

    $form.on('submit', function (event) {
        event.preventDefault();

        if (!$type.val()) {
            showAlert('danger', 'Please select a Type before sending.');
            return;
        }

        $alert.addClass('d-none');

        $.ajax({
            url: sendUrl,
            method: 'POST',
            data: $form.serialize(),
        })
            .done(function (response) {
                showAlert('success', response.message || 'Notification sent successfully.');
            })
            .fail(function (xhr) {
                const response = xhr.responseJSON || {};
                showAlert('danger', response.message || 'Failed to send notification.');
            })
            .always(function () {
                resetSendButton();
            });
    });

    $(document).ready(function () {
        setOptions([]);
        closeDropdown();

        if ($type.val()) {
            loadRecipients($type.val());
        }
    });
})();
</script>
@endpush
