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
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Edit Ticket</h5>
                                <a href="{{ route('tickets.add_ticket', Crypt::encrypt($ticket->attraction_id)) }}" class="btn btn-sm btn-outline-danger">
                                    <i class="mdi mdi-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>

                        <div class="card-body ticket-form-compact">
                            <form action="{{ route('tickets.update', Crypt::encrypt($ticket->ticket_id)) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row g-2">
                                    <div class="col-md-4 mb-2">
                                        <label for="ticket_id" class="form-label"><strong>Ticket ID</strong></label>
                                        <input type="text" class="form-control form-control-sm" id="ticket_id" value="{{ $ticket->ticket_id }}" readonly>
                                    </div>

                                    <div class="col-md-4 mb-2">
                                        <label for="name" class="form-label"><strong>Ticket Name</strong><span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" id="name" name="name" placeholder="e.g. General Admission" value="{{ old('name', $ticket->name) }}" required>
                                        @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-12 mt-1 mb-2">
                                        <div class="section-title"><i class="ri-money-dollar-circle-line me-1"></i> Pricing <small class="text-muted fw-normal">(Sell = customer pays · Cost = attraction fee)</small></div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm ticket-price-table">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th rowspan="2" class="align-middle" style="width:10%">Age</th>
                                                        <th colspan="2" class="text-center visitor-group">Local</th>
                                                        <th colspan="2" class="text-center visitor-group">Foreigner</th>
                                                    </tr>
                                                    <tr>
                                                        <th>Sell</th>
                                                        <th>Cost</th>
                                                        <th>Sell <span class="text-danger">*</span></th>
                                                        <th>Cost <span class="text-danger">*</span></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><span class="badge bg-info-subtle text-info age-badge">Child</span></td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm ticket-price-input" id="child_price" name="child_price" placeholder="0.00" value="{{ old('child_price', $ticket->child_price) }}">
                                                            @error('child_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm ticket-price-input" id="child_cost_price" name="child_cost_price" placeholder="0.00" value="{{ old('child_cost_price', $ticket->child_cost_price) }}">
                                                            @error('child_cost_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm ticket-price-input" id="child_price_nri" name="child_price_nri" placeholder="0.00" value="{{ old('child_price_nri', $ticket->child_price_nri) }}" required>
                                                            @error('child_price_nri')<div class="text-danger small">{{ $message }}</div>@enderror
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm ticket-price-input" id="child_cost_price_nri" name="child_cost_price_nri" placeholder="0.00" value="{{ old('child_cost_price_nri', $ticket->child_cost_price_nri) }}" required>
                                                            @error('child_cost_price_nri')<div class="text-danger small">{{ $message }}</div>@enderror
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="badge bg-primary-subtle text-primary age-badge">Adult</span></td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm ticket-price-input" id="adult_price" name="adult_price" placeholder="0.00" value="{{ old('adult_price', $ticket->adult_price) }}" required>
                                                            @error('adult_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm ticket-price-input" id="adult_cost_price" name="adult_cost_price" placeholder="0.00" value="{{ old('adult_cost_price', $ticket->adult_cost_price) }}">
                                                            @error('adult_cost_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm ticket-price-input" id="adult_price_nri" name="adult_price_nri" placeholder="0.00" value="{{ old('adult_price_nri', $ticket->adult_price_nri) }}" required>
                                                            @error('adult_price_nri')<div class="text-danger small">{{ $message }}</div>@enderror
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm ticket-price-input" id="adult_cost_price_nri" name="adult_cost_price_nri" placeholder="0.00" value="{{ old('adult_cost_price_nri', $ticket->adult_cost_price_nri) }}" required>
                                                            @error('adult_cost_price_nri')<div class="text-danger small">{{ $message }}</div>@enderror
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="badge bg-secondary-subtle text-secondary age-badge">Senior</span></td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm ticket-price-input" id="senior_adult_price" name="senior_adult_price" placeholder="0.00" value="{{ old('senior_adult_price', $ticket->senior_adult_price) }}">
                                                            @error('senior_adult_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm ticket-price-input" id="senior_adult_cost_price" name="senior_adult_cost_price" placeholder="0.00" value="{{ old('senior_adult_cost_price', $ticket->senior_adult_cost_price) }}">
                                                            @error('senior_adult_cost_price')<div class="text-danger small">{{ $message }}</div>@enderror
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm ticket-price-input" id="senior_adult_price_nri" name="senior_adult_price_nri" placeholder="0.00" value="{{ old('senior_adult_price_nri', $ticket->senior_adult_price_nri) }}" required>
                                                            @error('senior_adult_price_nri')<div class="text-danger small">{{ $message }}</div>@enderror
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control form-control-sm ticket-price-input" id="senior_adult_cost_price_nri" name="senior_adult_cost_price_nri" placeholder="0.00" value="{{ old('senior_adult_cost_price_nri', $ticket->senior_adult_cost_price_nri) }}" required>
                                                            @error('senior_adult_cost_price_nri')<div class="text-danger small">{{ $message }}</div>@enderror
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
                                        <textarea class="form-control form-control-sm" id="description" name="description" rows="2" placeholder="Enter description">{{ old('description', $ticket->description) }}</textarea>
                                        <div id="description_error" class="text-danger small mt-1 d-none"></div>
                                    </div>

                                    <div class="col-md-12 mb-2">
                                        <label for="remarks" class="form-label"><strong>Remarks</strong> <small class="text-muted">(Optional)</small></label>
                                        <textarea id="remarks" name="remarks" class="form-control form-control-sm" rows="2" placeholder="Optional remarks">{{ old('remarks', $ticket->remarks) }}</textarea>
                                        @error('remarks')<div class="text-danger small">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-12 mb-2">
                                        <label for="terms_conditions" class="form-label"><strong>Terms & Conditions</strong><span class="text-danger">*</span></label>
                                        <textarea id="terms_conditions" name="terms_conditions" class="form-control form-control-sm" rows="3" placeholder="Enter terms and conditions...">{{ old('terms_conditions', $ticket->terms_conditions) }}</textarea>
                                        <div id="terms_conditions_error" class="text-danger small mt-1 d-none"></div>
                                    </div>

                                    <div class="col-md-4 mb-2">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="status" name="status" value="1" {{ (old('status', $ticket->status) == 1) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="status"><strong>Active</strong></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">Update Ticket</button>
                                        <a href="{{ route('tickets.add_ticket', Crypt::encrypt($ticket->attraction_id)) }}" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
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

        function clampTicketPriceInput(el) {
            if (!el || el.value === '' || el.value === null) return;
            const n = parseFloat(String(el.value).replace(',', '.'));
            if (isNaN(n)) return;
            el.value = Number(n.toFixed(2));
        }
        document.querySelectorAll('.ticket-price-input').forEach(function (el) {
            el.addEventListener('blur', function () { clampTicketPriceInput(this); });
            if (el.value !== '') {
                clampTicketPriceInput(el);
            }
        });

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
        const ticketForm = document.querySelector('form[action*="tickets"]');
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
            });
        }
        $('#description, #terms_conditions').on('summernote.change', function () {
            if ($('<div>').html($(this).summernote('code')).text().trim() !== '') {
                setEditorError(this.id, '');
            }
        });

        @error('description')
            setEditorError('description', @json($message));
        @enderror
        @error('terms_conditions')
            setEditorError('terms_conditions', @json($message));
        @enderror
    });
</script>
@endsection
