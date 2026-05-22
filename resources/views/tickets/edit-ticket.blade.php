@extends('layouts.layout')
@section('content')
@extends('layouts.datatablecss')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
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
                        
                        <div class="card-body">
                            <form action="{{ route('tickets.update', Crypt::encrypt($ticket->ticket_id)) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <!-- Ticket ID -->
                                    <div class="col-md-4 mb-3">
                                        <label for="ticket_id" class="form-label"><strong>Ticket ID</strong></label>
                                        <input type="text" class="form-control" id="ticket_id" value="{{ $ticket->ticket_id }}" readonly>
                                    </div>
                                    
                                    <!-- Ticket Name -->
                                    <div class="col-md-4 mb-3">
                                        <label for="name" class="form-label"><strong>Ticket Name</strong><span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $ticket->name) }}" required>
                                        @error('name')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                     <!-- Child Sell Price -->
                                     <div class="col-md-4 mb-3">
                                        <label for="child_price" class="form-label"><strong>Child Sell Price (local)</strong></label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control ticket-price-input" id="child_price" name="child_price" value="{{ old('child_price', $ticket->child_price) }}">
                                        @error('child_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Child Cost Price -->
                                    <div class="col-md-4 mb-3">
                                        <label for="child_cost_price" class="form-label"><strong>Child Cost Price (local)</strong></label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control ticket-price-input" id="child_cost_price" name="child_cost_price" value="{{ old('child_cost_price', $ticket->child_cost_price) }}">
                                        @error('child_cost_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- Adult Sell Price -->
                                    <div class="col-md-4 mb-3">
                                        <label for="adult_price" class="form-label"><strong>Adult Sell Price (local)</strong><span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control ticket-price-input" id="adult_price" name="adult_price" value="{{ old('adult_price', $ticket->adult_price) }}" required>
                                        @error('adult_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- Adult Cost Price -->
                                    <div class="col-md-4 mb-3">
                                        <label for="adult_cost_price" class="form-label"><strong>Adult Cost Price (local)</strong></label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control ticket-price-input" id="adult_cost_price" name="adult_cost_price" value="{{ old('adult_cost_price', $ticket->adult_cost_price) }}">
                                        @error('adult_cost_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- Senior Adult Sell Price -->
                                    <div class="col-md-4 mb-3">
                                        <label for="senior_adult_price" class="form-label"><strong>Senior Sell Price (local)</strong></label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control ticket-price-input" id="senior_adult_price" name="senior_adult_price" value="{{ old('senior_adult_price', $ticket->senior_adult_price) }}">
                                        @error('senior_adult_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Senior Adult Cost Price -->
                                    <div class="col-md-4 mb-3">
                                        <label for="senior_adult_cost_price" class="form-label"><strong>Senior Cost Price (local)</strong></label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control ticket-price-input" id="senior_adult_cost_price" name="senior_adult_cost_price" value="{{ old('senior_adult_cost_price', $ticket->senior_adult_cost_price) }}">
                                        @error('senior_adult_cost_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Child Sell Price NRI -->
                                    <div class="col-md-4 mb-3">
                                        <label for="child_price_nri" class="form-label"><strong>Child Sell Price (foreigner)</strong><span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control ticket-price-input" id="child_price_nri" name="child_price_nri" placeholder="Enter Child Sell Price" value="{{ old('child_price_nri', $ticket->child_price_nri) }}" required>
                                        @error('child_price_nri')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Child Cost Price NRI -->
                                    <div class="col-md-4 mb-3">
                                        <label for="child_cost_price_nri" class="form-label"><strong>Child Cost Price (foreigner)</strong><span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control ticket-price-input" id="child_cost_price_nri" name="child_cost_price_nri" placeholder="Enter Child Cost Price" value="{{ old('child_cost_price_nri', $ticket->child_cost_price_nri) }}" required>
                                        @error('child_cost_price_nri')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Adult Sell Price NRI-->
                                    <div class="col-md-4 mb-3">
                                        <label for="adult_price_nri" class="form-label"><strong>Adult Sell Price (foreigner)</strong><span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control ticket-price-input" id="adult_price_nri" name="adult_price_nri" placeholder="Enter Adult Sell Price" value="{{ old('adult_price_nri', $ticket->adult_price_nri) }}" required>
                                        @error('adult_price_nri')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- Adult Cost Price NRI-->
                                    <div class="col-md-4 mb-3">
                                        <label for="adult_cost_price_nri" class="form-label"><strong>Adult Cost Price (foreigner)</strong><span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control ticket-price-input" id="adult_cost_price_nri" name="adult_cost_price_nri" placeholder="Enter Adult Cost Price" value="{{ old('adult_cost_price_nri', $ticket->adult_cost_price_nri) }}" required>
                                        @error('adult_cost_price_nri')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- Senior Adult Sell Price NRI-->
                                    <div class="col-md-4 mb-3">
                                        <label for="senior_adult_price_nri" class="form-label"><strong>Senior Sell Price (foreigner)</strong><span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control ticket-price-input" id="senior_adult_price_nri" name="senior_adult_price_nri" placeholder="Enter Senior Sell Price" value="{{ old('senior_adult_price_nri', $ticket->senior_adult_price_nri) }}" required>
                                        @error('senior_adult_price_nri')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Senior Adult Cost Price NRI-->
                                    <div class="col-md-4 mb-3">
                                        <label for="senior_adult_cost_price_nri" class="form-label"><strong>Senior Cost Price (foreigner)</strong><span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control ticket-price-input" id="senior_adult_cost_price_nri" name="senior_adult_cost_price_nri" placeholder="Enter Senior Cost Price" value="{{ old('senior_adult_cost_price_nri', $ticket->senior_adult_cost_price_nri) }}" required>
                                        @error('senior_adult_cost_price_nri')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- Description -->
                                    <div class="col-md-12 mb-3">
                                        <label for="description" class="form-label"><strong>Important Notes</strong><span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $ticket->description) }}</textarea>
                                        @error('description')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Remarks -->
                                    <div class="col-md-12 mb-3">
                                        <label for="remarks" class="form-label"><strong>Remarks</strong> <small class="text-muted">(Optional)</small></label>
                                        <textarea id="remarks" name="remarks" class="form-control" rows="4" placeholder="Enter any remarks or notes (optional)">{{ old('remarks', $ticket->remarks) }}</textarea>
                                        @error('remarks')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Terms & Conditions -->
                                    <div class="col-md-12 mb-3">
                                        <label for="terms_conditions" class="form-label"><strong>Terms & Conditions</strong><span class="text-danger">*</span></label>
                                        <textarea id="terms_conditions" name="terms_conditions" class="form-control" rows="6" placeholder="Enter terms and conditions..." required>{{ old('terms_conditions', $ticket->terms_conditions) }}</textarea>
                                        @error('terms_conditions')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- Status -->
                                    <div class="col-md-4 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="status" name="status" value="1" {{ (old('status', $ticket->status) == 1) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="status"><strong>Active</strong></label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
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
            height: 200,      
            minHeight: 200,   
            maxHeight: 500,   
            placeholder: 'Enter your content here...', 
        });
        $('#remarks').summernote({
            height: 200,      
            minHeight: 200,   
            maxHeight: 500,   
            placeholder: 'Enter any remarks or notes (optional)...', 
        });
        $('#terms_conditions').summernote({
            height: 200,      
            minHeight: 200,   
            maxHeight: 500,   
            placeholder: 'Enter terms and conditions...', 
        });
        // Initialize Select2 for city
        $('#citySelect').select2({
            placeholder: "Search and Select a City",
            allowClear: true,
            tags: true,
            width: '100%'
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
    });
</script>
@endsection