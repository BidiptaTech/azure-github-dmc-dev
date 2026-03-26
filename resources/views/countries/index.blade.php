@extends('layouts.layout')
@section('title', 'Country')
@extends('layouts.datatablecss')

@section('content')
@php
    $countryRoleId = (int) (Auth::user()->role_id ?? 0);
    $showRemitanceAndExchange = in_array($countryRoleId, \App\Models\Country::DMC_REMITTANCE_EXCHANGE_ROLE_IDS, true);
    $currentUser = Auth::user();
    $currentDmcId = null;
    if (in_array($countryRoleId, [11, 20], true)) {
        $currentDmcId = $currentUser->userId ?? null;
    } else {
        $currentDmcId = $currentUser->created_by ?? null;
    }
@endphp
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">Country Listing</h5>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        <!-- Add New Country Button -->
                        {{-- @if(hasPermission('create country')) --}}
                            <a href="{{ route('countries.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2">
                                <i class="fas fa-plus"></i> Add New Country
                            </a>
                        {{-- @endif --}}

                        <!-- Export Dropdown Button -->
                        <div class="dropdown">
                            <button class="btn btn-warning btn-sm dropdown-toggle" type="button" id="exportDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportCopy">Copy</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportCSV">CSV</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportExcel">Excel</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportPDF">PDF</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportPrint">Print</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <x-alert />

                <div class="mx-3 mt-2" id="countriesDmcInlineAlertWrap" style="display:none;">
                    <div class="alert alert-dismissible fade show py-2 mb-2" role="alert" id="countriesDmcInlineAlert">
                        <span id="countriesDmcInlineAlertMsg"></span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>

                @if($showRemitanceAndExchange && $currentDmcId)
                    <div class="card-body border-bottom py-3 mx-3 mt-2 mb-0 rounded-0 px-0 pt-0">
                        <h6 class="mb-2 small text-uppercase text-muted">DMC — set values by country</h6>
                        <p class="small text-muted mb-3">Choose a country, enter remittance charge and exchange rate, then save.</p>
                        <div class="row g-2 align-items-end flex-wrap">
                            <div class="col-12 col-md-4 col-lg-4">
                                <label for="dmcCountryPicker" class="form-label small mb-1">Country</label>
                                <select id="dmcCountryPicker" class="form-select form-select-sm">
                                    <option value="">— Select country —</option>
                                    @foreach(($countriesAll ?? $countries) as $c)
                                        @php
                                            $remOpt = $c->remittanceChargeDisplayForDmc((int) $currentDmcId);
                                            $exOpt = $c->exchangeRateDisplayForDmc((int) $currentDmcId);
                                        @endphp
                                        <option value="{{ $c->id }}" data-rem="{{ $remOpt }}" data-ex="{{ $exOpt }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-3 col-lg-2">
                                <label for="dmcRemittanceInput" class="form-label small mb-1">Remitance charge</label>
                                <input type="number" min="0" step="1" class="form-control form-control-sm" id="dmcRemittanceInput" placeholder="e.g. 12" autocomplete="off">
                            </div>
                            <div class="col-6 col-md-3 col-lg-2">
                                <label for="dmcExchangeInput" class="form-label small mb-1">Exchange rate</label>
                                <input type="number" min="0" step="1" class="form-control form-control-sm" id="dmcExchangeInput" placeholder="e.g. 10" autocomplete="off">
                            </div>
                            <div class="col-12 col-md-2 col-lg-2">
                                <button type="button" class="btn btn-primary btn-sm w-100 mt-3 mt-md-0" id="dmcCountrySaveBtn">Save</button>
                            </div>
                        </div>
                    </div>
                @endif
                
                <table class="datatables-basic table table-bordered table-sm w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Country Name</th>
                            @if(!$showRemitanceAndExchange)
                            <th>Country Code</th>
                            <th>Currency</th>
                            <th>Tax Percentage</th>
                            <th>Gateway Percentage</th>
                            <th>Commission Percentage</th>
                            @endif
                            @if($showRemitanceAndExchange)
                                <th>Remitance Charge</th>
                                <th>Exchange Rate</th>
                            @endif
                            @if(!$showRemitanceAndExchange)
                            <th>Status</th>
                            {{-- @if(hasPermission('edit country') || hasPermission('delete country')) --}}
                                <th>Action</th>
                            {{-- @endif --}}
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($countries as $key => $country)
                            <tr data-country-id="{{ $country->id }}">
                                <td>{{ ++$key }}</td>
                                <td class="country-name">{{ $country->name }}</td>
                                @if(!$showRemitanceAndExchange)
                                <td class="city-name">{{ $country->country_code }}</td>
                                <td>{{ $country->currency }}</td>
                                <td>{{ $country->tax_percentage }}</td>
                                <td>{{ $country->gateway_percentage }}</td>
                                <td>{{ $country->commission_percentage }}</td>
                                @endif
                                @if($showRemitanceAndExchange)
                                    @php
                                        $remValue = $country->remittanceChargeDisplayForDmc((int) ($currentDmcId ?? 0));
                                        $exValue = $country->exchangeRateDisplayForDmc((int) ($currentDmcId ?? 0));
                                    @endphp
                                    <td style="min-width: 120px;" class="text-muted small" id="country-rem-{{ $country->id }}">{{ $remValue !== '' ? $remValue : '—' }}</td>
                                    <td style="min-width: 120px;" class="text-muted small" id="country-ex-{{ $country->id }}">{{ $exValue !== '' ? $exValue : '—' }}</td>
                                @endif
                                @if(!$showRemitanceAndExchange)
                                <td>
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input status-toggle" type="checkbox" role="switch" 
                                               data-id="{{ $country->id }}" 
                                               {{ $country->is_active ? 'checked' : '' }}
                                               style="width: 2.5em; height: 1.25em; cursor: pointer;">
                                    </div>
                                </td>
                                {{-- @if(hasPermission('edit country') || hasPermission('delete country')) --}}
                                <td style="display: inline-block; white-space: nowrap;">
                                    <!-- Edit Button -->
                                    {{-- @if(hasPermission('edit country')) --}}
                                    <a href="{{ route('countries.edit',  Crypt::encrypt($country->id)) }}" 
                                    class="btn btn-primary btn-sm rounded-circle waves-effect waves-light" 
                                    style="min-width: 28px; min-height: 28px; padding: 0;">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                                            <path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/>
                                        </svg>
                                    </a>
                                    {{-- @endif --}}
                                    <!-- Delete Button -->
                                    {{-- @if(hasPermission('delete country')) --}}
                                    {{-- <button type="button" 
                                            class="btn btn-danger btn-sm rounded-circle waves-effect waves-light" 
                                            style="min-width: 28px; min-height: 28px; padding: 0;" 
                                            data-toggle="modal" 
                                            data-target="#deleteModal" 
                                            onclick="setDeleteForm('{{ route('countries.destroy', $country->id) }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                                            <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                                        </svg>
                                    </button> --}}
                                    {{-- @endif --}}
                                </td>
                                @endif
                                {{-- @endif --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Vehicle Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" Category="dialog" 
        aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" Category="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmation</h5>
            </div>
            <div class="modal-body">
                Are you sure want to delete?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <form id="deleteForm" action="" method="POST" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End Modal -->
@endsection

@section('styles')
<!-- Add Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<style>
    /* Compact countries listing table */
    .card-datatable.table-responsive {
        overflow-x: auto;
    }

    table.datatables-basic {
        font-size: 12px;
        line-height: 1.1;
        white-space: nowrap;
    }

    table.datatables-basic thead th,
    table.datatables-basic tbody td {
        padding: 0.35rem 0.45rem !important;
        vertical-align: middle;
    }

    table.datatables-basic thead th {
        font-weight: 600;
    }

    table.datatables-basic .form-check-input.status-toggle {
        transform: scale(0.9);
    }

    /* Slightly tighter action buttons */
    table.datatables-basic .btn.btn-sm {
        padding: 0.2rem 0.35rem;
    }
</style>
@endsection

@section('scripts')
<!-- Add Toastr JS before your other scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- Configure Toastr -->
<script>
    // Toastr can be blocked by CSP / network; use safe wrapper everywhere.
    function countriesNotify(type, message) {
        const msg = message || (type === 'success' ? 'Saved.' : 'Something went wrong.');

        // Inline fallback (always works)
        const wrap = document.getElementById('countriesDmcInlineAlertWrap');
        const box = document.getElementById('countriesDmcInlineAlert');
        const span = document.getElementById('countriesDmcInlineAlertMsg');
        if (wrap && box && span) {
            wrap.style.display = 'block';
            box.classList.remove('alert-success', 'alert-danger', 'alert-warning', 'alert-info');
            box.classList.add(type === 'success' ? 'alert-success' : (type === 'warning' ? 'alert-warning' : 'alert-danger'));
            span.textContent = msg;
            window.clearTimeout(window.__countriesInlineAlertTimer);
            window.__countriesInlineAlertTimer = window.setTimeout(function() {
                wrap.style.display = 'none';
            }, 3500);
        }

        // Toastr (if available)
        if (typeof window.toastr !== 'undefined') {
            try {
                window.toastr.clear();
                window.toastr[type === 'warning' ? 'warning' : (type === 'success' ? 'success' : 'error')](msg);
            } catch (e) {
                // ignore
            }
        }
    }

    if (typeof window.toastr !== 'undefined') {
        window.toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: "toast-top-right",
            showDuration: "300",
            hideDuration: "1000",
            timeOut: "3000"
        };
    }

    // Show persisted messages ASAP (even if DataTables fails to init)
    (function() {
        try {
            const msg = sessionStorage.getItem('countries_dmc_flash_success');
            if (msg) {
                sessionStorage.removeItem('countries_dmc_flash_success');
                countriesNotify('success', msg);
            }
            const err = sessionStorage.getItem('countries_dmc_flash_error');
            if (err) {
                sessionStorage.removeItem('countries_dmc_flash_error');
                countriesNotify('error', err);
            }
        } catch (e) {
            // ignore
        }
    })();
</script>

<!-- DataTable JS -->
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<!-- DataTables Initialization Script -->
<script>
    $(document).ready(function() {
        // Initialize DataTable with export buttons (guarded)
        let countriesTable = null;
        try {
            countriesTable = $('.datatables-basic').DataTable({
                responsive: true,
                buttons: [
                    'copy',
                    'csv',
                    'excel',
                    'pdf',
                    'print' // Enable copy, CSV, Excel, PDF, and Print buttons
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search...",
                },
                lengthMenu: [10, 25, 50, 100], // Customize number of entries per page
            });
        } catch (e) {
            console.error('DataTable init failed:', e);
        }

        // Custom export button functionality (for the dropdown)
        $('#exportCopy').on('click', function() {
            if (countriesTable) countriesTable.button('.buttons-copy').trigger();
        });

        $('#exportCSV').on('click', function() {
            if (countriesTable) countriesTable.button('.buttons-csv').trigger();
        });

        $('#exportExcel').on('click', function() {
            if (countriesTable) countriesTable.button('.buttons-excel').trigger();
        });

        $('#exportPDF').on('click', function() {
            if (countriesTable) countriesTable.button('.buttons-pdf').trigger();
        });

        $('#exportPrint').on('click', function() {
            if (countriesTable) countriesTable.button('.buttons-print').trigger();
        });

        // Status toggle functionality
        $(document).on('change', '.status-toggle', function() {
            const countryId = $(this).data('id');
            const isActive = $(this).prop('checked') ? 1 : 0;
            const toggleElement = $(this);

            // Disable the toggle while processing to prevent multiple clicks
            toggleElement.prop('disabled', true);

            $.ajax({
                url: "{{ route('countries.toggle-status') }}",
                type: 'POST',
                dataType: 'json',
                data: {
                    id: countryId,
                    is_active: isActive,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    toggleElement.prop('disabled', false);
                    if (response.success) {
                        countriesNotify('success', 'Status updated successfully!');
                    } else {
                        toggleElement.prop('checked', !isActive);
                        countriesNotify('error', response.message || 'Error updating status');
                    }
                },
                error: function(xhr, status, error) {
                    toggleElement.prop('disabled', false);
                    toggleElement.prop('checked', !isActive);
                    countriesNotify('error', 'An error occurred while updating status');
                    console.error("Error details:", xhr.responseText);
                }
            });
        });

        @if($showRemitanceAndExchange && $currentDmcId)
        (function() {
            function dmcFormatCell(val) {
                return (val === null || val === undefined || val === '' || String(val) === 'NaN') ? '—' : String(val);
            }

            function readDmcSaveErrorMessage(xhr) {
                var j = xhr.responseJSON;
                if (!j) {
                    if (xhr.status === 419) {
                        return 'Your session expired. Refresh the page and try again.';
                    }
                    if (xhr.status === 403) {
                        return 'You do not have permission to perform this action.';
                    }
                    return 'Unable to save. Please try again or contact support.';
                }
                if (j.errors && typeof j.errors === 'object') {
                    var parts = [];
                    Object.keys(j.errors).forEach(function(k) {
                        (j.errors[k] || []).forEach(function(m) {
                            parts.push(m);
                        });
                    });
                    if (parts.length) {
                        return parts.join(' ');
                    }
                }
                return j.message || 'Unable to save. Please check your values and try again.';
            }

            $('#dmcCountryPicker').on('change', function() {
                const opt = $(this).find('option:selected');
                $('#dmcRemittanceInput').val(opt.attr('data-rem') || '');
                $('#dmcExchangeInput').val(opt.attr('data-ex') || '');
            });

            $('#dmcCountrySaveBtn').on('click', function() {
                const countryId = $('#dmcCountryPicker').val();
                if (!countryId) {
                    countriesNotify('warning', 'Please select a country.');
                    return;
                }
                const remRaw = $('#dmcRemittanceInput').val();
                const exRaw = $('#dmcExchangeInput').val();
                const btn = $(this);
                if (remRaw !== '') {
                    const r = parseInt(remRaw, 10);
                    if (isNaN(r) || r < 0) {
                        countriesNotify('error', 'Remitance charge must be a whole number ≥ 0.');
                        return;
                    }
                }
                if (exRaw !== '') {
                    const e = parseInt(exRaw, 10);
                    if (isNaN(e) || e < 0) {
                        countriesNotify('error', 'Exchange rate must be a whole number ≥ 0.');
                        return;
                    }
                }
                btn.prop('disabled', true);

                $.ajax({
                    url: "{{ route('countries.update-remitance-exchange') }}",
                    type: 'POST',
                    dataType: 'json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    data: {
                        id: countryId,
                        remitance_charge: remRaw === '' ? '' : parseInt(remRaw, 10),
                        exchange_rate: exRaw === '' ? '' : parseInt(exRaw, 10),
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        btn.prop('disabled', false);
                        if (response.success) {
                            const remDisp = dmcFormatCell(response.remitance_charge);
                            const exDisp = dmcFormatCell(response.exchange_rate);
                            $('#country-rem-' + countryId).text(remDisp);
                            $('#country-ex-' + countryId).text(exDisp);
                            const sel = $('#dmcCountryPicker option[value="' + countryId + '"]');
                            const remAttr = (response.remitance_charge === null || response.remitance_charge === undefined) ? '' : String(response.remitance_charge);
                            const exAttr = (response.exchange_rate === null || response.exchange_rate === undefined) ? '' : String(response.exchange_rate);
                            sel.attr('data-rem', remAttr);
                            sel.attr('data-ex', exAttr);

                            // Ensure row exists in table (DMC view only shows countries with values)
                            const rowSelector = 'tr[data-country-id="' + countryId + '"]';
                            const rowEl = $(rowSelector);
                            if (!rowEl.length && countriesTable) {
                                const countryName = sel.text();
                                const nextIndex = countriesTable.rows().count() + 1;
                                countriesTable.row.add([
                                    nextIndex,
                                    '<span class="country-name">' + $('<div>').text(countryName).html() + '</span>',
                                    '<span class="text-muted small" id="country-rem-' + countryId + '">' + $('<div>').text(remDisp).html() + '</span>',
                                    '<span class="text-muted small" id="country-ex-' + countryId + '">' + $('<div>').text(exDisp).html() + '</span>',
                                ]).draw(false);
                            } else if (countriesTable) {
                                // If already present, just redraw to keep numbering stable
                                countriesTable.draw(false);
                            }

                            const okMsg = response.message || 'Saved successfully.';
                        countriesNotify('success', okMsg);
                            try { sessionStorage.setItem('countries_dmc_flash_success', okMsg); } catch (e) {}

                            // User requested: refresh page after save (message persists via sessionStorage).
                            setTimeout(function() {
                                window.location.reload();
                            }, 1200);
                        } else {
                            const errMsg = response.message || 'Could not save your changes.';
                        countriesNotify('error', errMsg);
                            try { sessionStorage.setItem('countries_dmc_flash_error', errMsg); } catch (e) {}
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false);
                        const errMsg = readDmcSaveErrorMessage(xhr);
                    countriesNotify('error', errMsg);
                        try { sessionStorage.setItem('countries_dmc_flash_error', errMsg); } catch (e) {}
                        console.error('Country save error:', xhr.status, xhr.responseText);
                    }
                });
            });
        })();
        @endif
    });
</script>
<!-- End DataTable JS -->

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
function setDeleteForm(url) {
    document.getElementById('deleteForm').action = url;
}
</script>
@endsection
