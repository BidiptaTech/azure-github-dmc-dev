@extends('layouts.layout')
@section('content')
@extends('layouts.datatablecss')


@section('content')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
    <div class="page-content">
    <ul class="nav nav-pills mb-4 mt-4 d-flex justify-content-center" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ request()->routeIs('attraction.create') ? 'active' : '' }}" 
                   href="{{ route('attraction.create') }}" 
                   role="tab">
                    Attraction
                </a>
            </li>
            
            <li class="nav-item" role="presentation">
                
                <a class="nav-link {{ request()->routeIs('tickets.create') ? 'active' : '' }}" 
                   href="{{ route('tickets.create') }}" 
                   role="tab">
                    Ticket
                </a>
                
            </li>
        </ul>
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Create New Ticket</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('tickets.store') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <!-- Ticket Name -->
                                    <div class="col-md-4 mb-3">
                                        <label for="name" class="form-label"><strong>Ticket Name</strong><span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Child Price -->
                                    <div class="col-md-4 mb-3">
                                        <label for="child_price" class="form-label"><strong>Child Price</strong></label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control ticket-price-input" id="child_price" name="child_price" value="{{ old('child_price') }}">
                                        @error('child_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- Adult Price -->
                                    <div class="col-md-4 mb-3">
                                        <label for="adult_price" class="form-label"><strong>Adult Price</strong><span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control ticket-price-input" id="adult_price" name="adult_price" value="{{ old('adult_price') }}" required>
                                        @error('adult_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    
                                    
                                    <!-- Senior Adult Price -->
                                    <div class="col-md-4 mb-3">
                                        <label for="senior_adult_price" class="form-label"><strong>Senior Citizen Price</strong></label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control ticket-price-input" id="senior_adult_price" name="senior_adult_price" value="{{ old('senior_adult_price') }}">
                                        @error('senior_adult_price')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Child Price NRI -->
                                    <div class="col-md-4 mb-3">
                                        <label for="child_price_nri" class="form-label"><strong>Child Price(NRI)</strong><span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control ticket-price-input" id="child_price_nri" name="child_price_nri" placeholder="Enter Child Price" value="{{ old('child_price_nri') }}" required>
                                        @error('child_price_nri')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- Adult Price NRI-->
                                    <div class="col-md-4 mb-3">
                                        <label for="adult_price_nri" class="form-label"><strong>Adult Price(NRI)</strong><span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control ticket-price-input" id="adult_price_nri" name="adult_price_nri" placeholder="Enter Adult Price" value="{{ old('adult_price_nri') }}" required>
                                        @error('adult_price_nri')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>                                    
                                    
                                    <!-- Senior Adult Price NRI-->
                                    <div class="col-md-4 mb-3">
                                        <label for="senior_adult_price_nri" class="form-label"><strong>Senior Citizen Price(NRI)</strong><span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" class="form-control ticket-price-input" id="senior_adult_price_nri" name="senior_adult_price_nri" placeholder="Enter Senior Citizen Price" value="{{ old('senior_adult_price_nri') }}" required>
                                        @error('senior_adult_price_nri')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <!-- Description -->
                                    <div class="col-md-12 mb-3">
                                        <label for="description" class="form-label"><strong>Important Notes</strong><span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Remarks -->
                                    <div class="col-md-12 mb-3">
                                        <label for="remarks" class="form-label"><strong>Remarks</strong> <small class="text-muted">(Optional)</small></label>
                                        <textarea id="remarks" name="remarks" class="form-control" rows="4" placeholder="Enter any remarks or notes (optional)"></textarea>
                                        @error('remarks')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Terms & Conditions -->
                                    <div class="col-md-12 mb-3">
                                        <label for="terms_conditions" class="form-label"><strong>Terms & Conditions</strong><span class="text-danger">*</span></label>
                                        <textarea id="terms_conditions" name="terms_conditions" class="form-control" rows="6" placeholder="Enter terms and conditions..."></textarea>
                                        @error('terms_conditions')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Status -->
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="status" name="status" value="1" {{ old('status') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="status"><strong>Active</strong></label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">Create Ticket</button>
                                        <a href="{{ route('tickets.index') }}" class="btn btn-secondary">Cancel</a>
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