@extends('layouts.layout')
@section('content')
@extends('layouts.datatablecss')


@section('content')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
<style>
    .ticket-form-compact .form-label { margin-bottom: 0.2rem; font-size: 0.8125rem; }
    .ticket-form-compact .section-title {
        font-size: 0.9375rem;
        font-weight: 600;
        color: #405189;
        margin-bottom: 0.5rem;
        padding-bottom: 0.25rem;
        border-bottom: 1px solid #e9ecef;
    }
    .ticket-price-table { font-size: 0.8125rem; margin-bottom: 0; }
    .ticket-price-table th,
    .ticket-price-table td { padding: 0.35rem 0.5rem; vertical-align: middle; }
    .ticket-price-table thead th { font-size: 0.75rem; font-weight: 600; white-space: nowrap; }
    .ticket-price-table .form-control { max-width: 100%; }
    .ticket-price-table .age-badge { font-size: 0.7rem; padding: 0.2em 0.45em; }
    .ticket-price-table .visitor-group {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #6c757d;
    }
    .ticket-form-compact .form-control-sm { font-size: 0.8125rem; }
    .ticket-form-compact textarea.form-control { min-height: auto; }
</style>
    <div class="page-content">
    <ul class="nav nav-pills mb-4 mt-4 d-flex justify-content-center" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ request()->routeIs('attraction.edit') ? 'active' : '' }}" 
                   href="{{ route('attraction.edit', Crypt::encrypt($attraction->attraction_id)) }}" 
                   role="tab">
                    Attraction
                </a>
            </li>
            
            
            <li class="nav-item" role="presentation">
                
                <a class="nav-link {{ request()->routeIs('tickets.add_ticket') ? 'active' : '' }}" 
                   href="{{ route('tickets.add_ticket', Crypt::encrypt($attraction->attraction_id)) }}" 
                   role="tab">
                    Ticket
                </a>
            </li>
        </ul>
        <x-alert />
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    <h4 class="card-title mb-0">Create New Ticket</h4>
                                    <x-currency-price-note :country="$attraction->country ?? null" :watch-dmc="in_array($auth_user->role_id, [1, 20])" />
                                </div>
                                {{-- @if(auth()->user()->role_id == '11')
                                    <a href="{{ route('tickets.bulk_upload_for_attraction', $attraction->attraction_id) }}" 
                                       class="btn btn-warning btn-sm">
                                        <i class="ri-upload-cloud-2-line me-1"></i>Bulk Upload Tickets
                                    </a>
                                @endif --}}
                            </div>
                        </div>
                        <div class="card-body ticket-form-compact">
                            <form action="{{ route('tickets.store', Crypt::encrypt($attraction->attraction_id)) }}" method="POST" class="js-submit-loader-form" data-loader-message="Creating ticket...">
                                @csrf
                                <div class="row g-2">
                                    <input type="hidden" name="attraction_id" value="{{ $attraction->attraction_id }}">
                                    @if($auth_user->role_id == 1 || $auth_user->role_id == 20)
                                    <div class="col-12 mb-1">
                                        @if($dmcUsers->count() > 0)
                                            <div class="alert alert-info py-1 px-2 mb-0 small">
                                                Select a DMC to add tickets on their behalf.
                                            </div>
                                        @else
                                            <div class="alert alert-warning py-1 px-2 mb-0 small">
                                                No DMCs assigned to this attraction. Tickets cannot be added.
                                            </div>
                                        @endif
                                    </div>

                                    <div class="col-md-4 mb-2">
                                        <label for="dmc_selection" class="form-label"><strong>Select DMC</strong><span class="text-danger">*</span></label>
                                        <select id="dmc_selection" class="form-control form-control-sm" name="dmc_id" required @if($dmcUsers->count() == 0) disabled @endif>
                                            @if($dmcUsers->count() > 0)
                                                <option value="">Select DMC</option>
                                                @foreach($dmcUsers as $dmc)
                                                    <option value="{{ $dmc->userId }}" data-currency="{{ $dmc->currency ?? '' }}">{{ $dmc->company_name }} ({{ $dmc->name }})</option>
                                                @endforeach
                                            @else
                                                <option value="">No DMCs available</option>
                                            @endif
                                        </select>
                                    </div>
                                    @endif

                                    <div class="col-md-4 mb-2">
                                        <label for="name" class="form-label"><strong>Ticket Name</strong><span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" id="name" name="name" placeholder="e.g. General Admission" value="{{ old('name') }}" required>
                                        @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-2 mb-2">
                                        <label for="profit_type" class="form-label"><strong>Profit Type</strong></label>
                                        <select id="profit_type" name="profit_type" class="form-select form-select-sm">
                                            <option value="flat" {{ old('profit_type', 'flat') === 'flat' ? 'selected' : '' }}>Flat</option>
                                            <option value="percentage" {{ old('profit_type') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2 mb-2">
                                        <label for="profit_on_cost" class="form-label"><strong>Profit On Cost</strong></label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm" id="profit_on_cost" name="profit_on_cost" placeholder="0.00" value="{{ old('profit_on_cost') }}">
                                    </div>

                                    <div class="col-12 mt-1 mb-2">
                                        <div class="section-title"><i class="ri-money-dollar-circle-line me-1"></i> Pricing <small class="text-muted fw-normal">(Cost = attraction fee · Sell = customer pays)</small></div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm ticket-price-table">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th rowspan="2" class="align-middle" style="width:10%">Age</th>
                                                        <th colspan="2" class="text-center visitor-group">Local</th>
                                                        <th colspan="2" class="text-center visitor-group">Foreigner</th>
                                                    </tr>
                                                    <tr>
                                                        <th>Cost <span class="text-danger">*</span></th>
                                                        <th>Sell <span class="text-danger">*</span></th>
                                                        <th>Cost <span class="text-danger">*</span></th>
                                                        <th>Sell <span class="text-danger">*</span></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><span class="badge bg-info-subtle text-info age-badge">Child</span></td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm ticket-price-input ticket-cost-input" id="child_cost_price" name="child_cost_price" data-sell-target="child_price" placeholder="0.00" value="{{ old('child_cost_price') }}" required>
                                                            @error('child_cost_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm ticket-price-input ticket-sell-input" id="child_price" name="child_price" placeholder="0.00" value="{{ old('child_price') }}" required>
                                                            @error('child_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm ticket-price-input ticket-cost-input" id="child_cost_price_nri" name="child_cost_price_nri" data-sell-target="child_price_nri" placeholder="0.00" value="{{ old('child_cost_price_nri') }}" required>
                                                            @error('child_cost_price_nri')<div class="text-danger small">{{ $message }}</div>@enderror
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm ticket-price-input ticket-sell-input" id="child_price_nri" name="child_price_nri" placeholder="0.00" value="{{ old('child_price_nri') }}" required>
                                                            @error('child_price_nri')<div class="text-danger small">{{ $message }}</div>@enderror
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="badge bg-primary-subtle text-primary age-badge">Adult</span></td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm ticket-price-input ticket-cost-input" id="adult_cost_price" name="adult_cost_price" data-sell-target="adult_price" placeholder="0.00" value="{{ old('adult_cost_price') }}" required>
                                                            @error('adult_cost_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm ticket-price-input ticket-sell-input" id="adult_price" name="adult_price" placeholder="0.00" value="{{ old('adult_price') }}" required>
                                                            @error('adult_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm ticket-price-input ticket-cost-input" id="adult_cost_price_nri" name="adult_cost_price_nri" data-sell-target="adult_price_nri" placeholder="0.00" value="{{ old('adult_cost_price_nri') }}" required>
                                                            @error('adult_cost_price_nri')<div class="text-danger small">{{ $message }}</div>@enderror
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm ticket-price-input ticket-sell-input" id="adult_price_nri" name="adult_price_nri" placeholder="0.00" value="{{ old('adult_price_nri') }}" required>
                                                            @error('adult_price_nri')<div class="text-danger small">{{ $message }}</div>@enderror
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="badge bg-secondary-subtle text-secondary age-badge">Senior</span></td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm ticket-price-input ticket-cost-input" id="senior_adult_cost_price" name="senior_adult_cost_price" data-sell-target="senior_adult_price" placeholder="0.00" value="{{ old('senior_adult_cost_price') }}" required>
                                                            @error('senior_adult_cost_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm ticket-price-input ticket-sell-input" id="senior_adult_price" name="senior_adult_price" placeholder="0.00" value="{{ old('senior_adult_price') }}" required>
                                                            @error('senior_adult_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm ticket-price-input ticket-cost-input" id="senior_adult_cost_price_nri" name="senior_adult_cost_price_nri" data-sell-target="senior_adult_price_nri" placeholder="0.00" value="{{ old('senior_adult_cost_price_nri') }}" required>
                                                            @error('senior_adult_cost_price_nri')<div class="text-danger small">{{ $message }}</div>@enderror
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm ticket-price-input ticket-sell-input" id="senior_adult_price_nri" name="senior_adult_price_nri" placeholder="0.00" value="{{ old('senior_adult_price_nri') }}" required>
                                                            @error('senior_adult_price_nri')<div class="text-danger small">{{ $message }}</div>@enderror
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-1 mb-1">
                                        <div class="section-title"><i class="ri-file-text-line me-1"></i> Details</div>
                                    </div>

                                    <div class="col-md-12 mb-2">
                                        <label for="description" class="form-label"><strong>Important Notes</strong><span class="text-danger">*</span></label>
                                        <textarea class="form-control form-control-sm" id="description" name="description" rows="2" placeholder="Enter description">{{ old('description') }}</textarea>
                                        <div id="description_error" class="text-danger small mt-1 d-none"></div>
                                    </div>

                                    <div class="col-md-12 mb-2">
                                        <label for="remarks" class="form-label"><strong>Remarks</strong> <small class="text-muted">(Optional)</small></label>
                                        <textarea id="remarks" name="remarks" class="form-control form-control-sm" rows="2" placeholder="Optional remarks">{{ old('remarks') }}</textarea>
                                        @error('remarks')<div class="text-danger small">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-12 mb-2">
                                        <label for="terms_conditions" class="form-label"><strong>Terms & Conditions</strong><span class="text-danger">*</span></label>
                                        <textarea id="terms_conditions" name="terms_conditions" class="form-control form-control-sm" rows="3" placeholder="Enter terms and conditions...">{{ old('terms_conditions') }}</textarea>
                                        <div id="terms_conditions_error" class="text-danger small mt-1 d-none"></div>
                                    </div>

                                    <div class="col-md-4 mb-2">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="status" name="status" value="1" {{ old('status') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="status"><strong>Active</strong></label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary js-submit-loader-btn" id="submitBtn"
                                            @if($auth_user->role_id == 1 || $auth_user->role_id == 20)
                                                @if($dmcUsers->count() == 0) disabled title="Cannot submit: No DMCs available for this attraction" @endif
                                            @endif>
                                            <span class="js-submit-loader-btn-text">Create Ticket</span>
                                            <span class="js-submit-loader-btn-loading d-none">
                                                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                                Creating...
                                            </span>
                                        </button>
                                        <a href="{{ route('tickets.index') }}" class="btn btn-secondary">Cancel</a>
                                        @if(($auth_user->role_id == 1 || $auth_user->role_id == 20) && $dmcUsers->count() == 0)
                                            <div class="text-muted mt-2">
                                                <small><i class="fas fa-exclamation-triangle text-warning"></i> Form submission disabled: Attraction not associated with any DMCs</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">Tickets</h5>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        

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
                

                <table class="datatables-basic table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Ticket ID</th>
                            <th>Name</th>
                            <th>DMC Company</th>
                            <th>Created By</th>
                            <th>Adult Price</th>
                            <th>Child Price</th>
                            <th>Senior Adult Price</th>
                            <th>Status</th>
                            <th>Action</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $key => $ticket)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td>{{ $ticket->ticket_id }}</td>
                                <td>{{ $ticket->name }}</td>
                                <td>{{ $ticket->dmc->company_name ?? ($ticket->dmc->name ?? '—') }}</td>
                                <td>{{ $ticket->createdByUser->name ?? '—' }}</td>
                                <td>{{ $ticket->adult_price }}</td>
                                <td>{{ $ticket->child_price ?? 'N/A' }}</td>
                                <td>{{ $ticket->senior_adult_price ?? 'N/A' }}</td>
                                <td>
                                    @if($ticket->status == 1)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                
                                <td style="display: inline-block; white-space: nowrap;">
                                    <!-- View Button -->
                                    
                                    <a href="{{ route('tickets.show', Crypt::encrypt($ticket->ticket_id)) }}" 
                                    class="btn btn-info btn-sm rounded-circle" 
                                    style="width: 28px; height: 28px; padding: 0;">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 0 576 512" width="16px" fill="#ffffff">
                                            <path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"/>
                                        </svg>
                                    </a>
                                    

                                    <!-- Edit Button -->
                                   
                                    <a href="{{ route('tickets.edit', Crypt::encrypt($ticket->ticket_id)) }}" 
                                    class="btn btn-primary btn-sm rounded-circle" 
                                    style="width: 28px; height: 28px; padding: 0;">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                                            <path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/>
                                        </svg>
                                    </a>
                                    

                                    <!-- Delete Button -->
                                    
                                    <button type="button" 
                                            class="btn btn-danger btn-sm rounded-circle" 
                                            style="width: 28px; height: 28px; padding: 0;" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal" 
                                            onclick="setDeleteForm('{{ route('tickets.destroy', Crypt::encrypt($ticket->ticket_id)) }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                                            <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                                        </svg>
                                    </button>
                                    
                                </td>
                                
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" 
        aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure want to delete?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
<x-form-submit-loader message="Creating ticket..." />
@endsection 
@section('scripts')
@include('components.currency-price-note-dmc-script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        console.log('[add-ticket] DOMContentLoaded — binding DMC currency note');
        if (typeof bindCurrencyPriceNoteToDmcSelect === 'function') {
            bindCurrencyPriceNoteToDmcSelect('dmc_selection');
        } else {
            console.error('[add-ticket] bindCurrencyPriceNoteToDmcSelect is not defined');
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.js"></script>
<script>
    $(document).ready(function() {
        $('#description').summernote({
            height: 120,
            minHeight: 80,
            maxHeight: 300,
            placeholder: 'Enter your content here...',
        });
        $('#remarks').summernote({
            height: 80,
            minHeight: 60,
            maxHeight: 200,
            placeholder: 'Optional remarks...',
        });
        $('#terms_conditions').summernote({
            height: 120,
            minHeight: 80,
            maxHeight: 300,
            placeholder: 'Enter terms and conditions...',
        });
        // Initialize Select2 for city (only if element and plugin exist)
        if ($('#citySelect').length && typeof $.fn.select2 === 'function') {
            $('#citySelect').select2({
                placeholder: "Search and Select a City",
                allowClear: true,
                tags: true,
                width: '100%'
            });
        }

        function clampTicketPriceInput(el) {
            if (!el || el.value === '' || el.value === null) return;
            const n = parseFloat(String(el.value).replace(',', '.'));
            if (isNaN(n)) return;
            el.value = Number(n.toFixed(2));
        }

        function calculateSellFromCost(costValue) {
            const profitType = ($('#profit_type').val() || 'flat').toLowerCase();
            const profit = parseFloat(String($('#profit_on_cost').val() || '0').replace(',', '.'));
            const cost = parseFloat(String(costValue || '0').replace(',', '.'));

            if (isNaN(cost)) return '';

            const profitAmount = isNaN(profit) ? 0 : profit;
            let sell = cost;

            if (profitType === 'percentage') {
                sell = cost + (cost * profitAmount / 100);
            } else {
                sell = cost + profitAmount;
            }

            return Number(Math.max(0, sell).toFixed(2));
        }

        function updateSellFromCostInput(costInput) {
            if (!costInput) return;
            const sellId = costInput.getAttribute('data-sell-target');
            const sellInput = sellId ? document.getElementById(sellId) : null;
            if (!sellInput) return;

            if (costInput.value === '' || costInput.value === null) {
                return;
            }

            sellInput.value = calculateSellFromCost(costInput.value);
            sellInput.dataset.autoFilled = '1';
        }

        function updateAllSellPricesFromCost() {
            document.querySelectorAll('.ticket-cost-input').forEach(function (costInput) {
                updateSellFromCostInput(costInput);
            });
        }

        document.querySelectorAll('.ticket-price-input').forEach(function (el) {
            el.addEventListener('blur', function () { clampTicketPriceInput(this); });
            if (el.value !== '') {
                clampTicketPriceInput(el);
            }
        });

        document.querySelectorAll('.ticket-cost-input').forEach(function (costInput) {
            costInput.addEventListener('input', function () {
                updateSellFromCostInput(this);
            });
            costInput.addEventListener('change', function () {
                updateSellFromCostInput(this);
            });
        });

        document.querySelectorAll('.ticket-sell-input').forEach(function (sellInput) {
            sellInput.addEventListener('input', function () {
                this.dataset.autoFilled = '0';
            });
        });

        $('#profit_type, #profit_on_cost').on('input change', function () {
            updateAllSellPricesFromCost();
        });

        // On load, only auto-fill empty sell fields when cost already has a value
        document.querySelectorAll('.ticket-cost-input').forEach(function (costInput) {
            const sellId = costInput.getAttribute('data-sell-target');
            const sellInput = sellId ? document.getElementById(sellId) : null;
            if (sellInput && (sellInput.value === '' || sellInput.value === null) && costInput.value !== '') {
                updateSellFromCostInput(costInput);
            }
        });

        const ticketForm = document.querySelector('form[action*="tickets.store"]');
        const dmcSelect = document.getElementById('dmc_selection');
        const submitBtn = document.getElementById('submitBtn');

        // Summernote hides the real textarea, so the browser cannot show its
        // native "please fill this field" message. Validate manually instead.
        function getEditorText(id) {
            return $('<div>').html($('#' + id).summernote('code')).text().trim();
        }
        function setEditorError(id, message) {
            const errorEl = document.getElementById(id + '_error');
            const editor = $('#' + id).next('.note-editor');
            if (message) {
                errorEl.textContent = message;
                errorEl.classList.remove('d-none');
                editor.css('border-color', '#dc3545');
            } else {
                errorEl.classList.add('d-none');
                editor.css('border-color', '');
            }
        }
        const requiredEditors = [
            ['description', 'Important Notes is required. Please fill in this field.'],
            ['terms_conditions', 'Terms & Conditions is required. Please fill in this field.']
        ];
        if (ticketForm) {
            ticketForm.addEventListener('submit', function (e) {
                let firstInvalid = null;
                requiredEditors.forEach(function (field) {
                    if (getEditorText(field[0]) === '') {
                        setEditorError(field[0], field[1]);
                        if (!firstInvalid) firstInvalid = field[0];
                    } else {
                        setEditorError(field[0], '');
                    }
                });
                if (firstInvalid) {
                    e.preventDefault();
                    e.stopPropagation();
                    document.getElementById(firstInvalid + '_error').scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return false;
                }

                @if(($auth_user->role_id == 1 || $auth_user->role_id == 20) && $dmcUsers->count() == 0)
                e.preventDefault();
                alert('Cannot submit: This attraction is not associated with any DMCs. Please contact administrator to assign DMCs first.');
                return false;
                @endif
            });
        }
        $('#description, #terms_conditions').on('summernote.change', function () {
            if ($('<div>').html($(this).summernote('code')).text().trim() !== '') {
                setEditorError(this.id, '');
            }
        });

        // Show server-side validation errors on Summernote fields after redirect
        @error('description')
            setEditorError('description', @json($message));
        @enderror
        @error('terms_conditions')
            setEditorError('terms_conditions', @json($message));
        @enderror
        
        // Show/hide submit button based on DMC selection
        if (dmcSelect && submitBtn) {
            dmcSelect.addEventListener('change', function() {
                @if($auth_user->role_id == 1 || $auth_user->role_id == 20)
                if (this.value === '' && !this.disabled) {
                    submitBtn.disabled = true;
                    submitBtn.title = 'Please select a DMC first';
                } else if (!this.disabled) {
                    submitBtn.disabled = false;
                    submitBtn.title = '';
                }
                @endif
            });
        }
    });
    
    // Function to set the delete form action URL
    function setDeleteForm(url) {
        document.getElementById('deleteForm').action = url;
    }
</script>
@endsection