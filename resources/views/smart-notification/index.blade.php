@extends('layouts.layout')

@section('title', 'Smart Notification')

@push('css')
<style>
    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
        min-height: 38px;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #0684d8;
        border-color: #0684d8;
        color: #fff;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff;
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
                        <label for="sending_to" class="form-label">Sending To <span class="text-danger">*</span></label>
                        <select name="sending_to[]" id="sending_to" class="form-select" required>
                            <option value="all" selected>All</option>
                        </select>
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
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const recipientsUrl = @json(route('smart-notification.recipients'));
    const $type = $('#type');
    const $sendingTo = $('#sending_to');
    let isAllMode = true;

    function destroySendingToSelect2() {
        if ($sendingTo.data('select2')) {
            $sendingTo.select2('destroy');
        }
    }

    function initSendingToSelect2(multiple) {
        destroySendingToSelect2();

        $sendingTo.prop('multiple', multiple);

        $sendingTo.select2({
            width: '100%',
            placeholder: multiple ? 'Search and select recipients' : 'All',
            allowClear: false,
            closeOnSelect: !multiple,
        });
    }

    function resetSendingToDefault() {
        destroySendingToSelect2();
        $sendingTo.prop('multiple', false);
        $sendingTo.empty().append('<option value="all" selected>All</option>');
        initSendingToSelect2(false);
        isAllMode = true;
    }

    function buildRecipientOptions(recipients) {
        const options = ['<option value="all">All</option>'];

        recipients.forEach(function (recipient) {
            const name = $('<div>').text(recipient.name).html();
            options.push('<option value="' + recipient.id + '">' + name + '</option>');
        });

        return options.join('');
    }

    function loadRecipients(type) {
        if (!type) {
            resetSendingToDefault();
            return;
        }

        $sendingTo.prop('disabled', true);

        $.get(recipientsUrl, { type: type })
            .done(function (response) {
                const recipients = (response && response.recipients) ? response.recipients : [];

                destroySendingToSelect2();
                $sendingTo.prop('multiple', true);
                $sendingTo.empty().html(buildRecipientOptions(recipients));
                initSendingToSelect2(true);
                isAllMode = false;

                $sendingTo.val(null).trigger('change');
            })
            .fail(function () {
                resetSendingToDefault();
                alert('Unable to load recipients. Please try again.');
            })
            .always(function () {
                $sendingTo.prop('disabled', false);
            });
    }

    $type.on('change', function () {
        loadRecipients($(this).val());
    });

    $sendingTo.on('select2:select', function (e) {
        if (isAllMode) {
            return;
        }

        const selectedId = e.params.data.id;

        if (selectedId === 'all') {
            $sendingTo.val(['all']).trigger('change');
            return;
        }

        const values = ($sendingTo.val() || []).filter(function (value) {
            return value !== 'all';
        });

        if (!values.includes(selectedId)) {
            values.push(selectedId);
        }

        $sendingTo.val(values).trigger('change');
    });

    $sendingTo.on('change', function () {
        if (isAllMode) {
            return;
        }

        const values = $sendingTo.val() || [];

        if (values.includes('all') && values.length > 1) {
            $sendingTo.val(['all']).trigger('change.select2');
        }
    });

    $(document).ready(function () {
        initSendingToSelect2(false);

        if ($type.val()) {
            loadRecipients($type.val());
        }
    });
})();
</script>
@endpush
