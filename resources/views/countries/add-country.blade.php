@extends('layouts.layout')
@section('title', 'Add Country')
@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Add New Country
                <a href="{{ route('countries.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form action="{{ route('countries.store') }}" method="POST" class="card-body">
                @csrf

                <div class="row">
                    <!-- Country Name -->
                    <div class="col-md-4 mb-3">
                        <label for="name" class="form-label"><strong>Country Name</strong><span class="text-danger">*</span></label>
                        <select class="form-control @error('name') is-invalid @enderror" name="name" id="countrySelect" required>
                            <option value="">Select Country</option>
                            @foreach($countries as $country)
                                <option value="{{ $country['name'] }}" data-code="{{ $country['country_code'] }}" data-currency="{{ $country['currency'] }}">
                                    {{ $country['name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('name')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                
                    <!-- Country Code -->
                    <div class="col-md-4 mb-3">
                        <label for="country_code" class="form-label"><strong>Country Code (Dialing Code)</strong><span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="countryCode" name="country_code" readonly required>
                        @error('country_code')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Card Name -->
                    <div class="col-md-4 mb-3">
                        <label for="card_type" class="form-label"><strong>Card Name</strong><span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('card_type') is-invalid @enderror" name="card_type" placeholder="Enter Card Name" required>
                        @error('card_type')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row">
                    <!-- Card Length -->
                    <div class="col-md-4 mb-3">
                        <label for="card_length" class="form-label"><strong>Card Length</strong><span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('card_length') is-invalid @enderror" name="card_length" placeholder="Enter Card Length" required>
                        @error('card_length')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                
                    <!-- Min Dial Length -->
                    <div class="col-md-4 mb-3">
                        <label for="min_length" class="form-label"><strong>Min Dial Length</strong><span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('min_length') is-invalid @enderror" name="min_length" placeholder="Enter Min Dial Length" required>
                        @error('min_length')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Max Dial Length -->
                    <div class="col-md-4 mb-3">
                        <label for="max_length" class="form-label"><strong>Max Dial Length</strong><span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('max_length') is-invalid @enderror" name="max_length" placeholder="Enter Max Dial Length" required>
                        @error('max_length')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row">
                    <!-- Tax Percentage -->
                    <div class="col-md-6 mb-3">
                        <label for="tax_percentage" class="form-label"><strong>Tax Percentage (%)</strong><span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control @error('tax_percentage') is-invalid @enderror" name="tax_percentage" placeholder="Enter Tax Percentage" required>
                        @error('tax_percentage')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Currency -->
                    <div class="col-md-6 mb-3">
                        <label for="currency" class="form-label"><strong>Currency</strong><span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('currency') is-invalid @enderror" name="currency" id="currency" required>
                        @error('currency')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row">
                     <!-- Gateway Percentage -->
                     <div class="col-md-6 mb-3">
                        <label for="gateway_percentage" class="form-label"><strong>Gateway Percentage (%)</strong><span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control @error('gateway_percentage') is-invalid @enderror" name="gateway_percentage" placeholder="Enter Gateway Percentage" required>
                        @error('gateway_percentage')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Commission Percentage -->
                    <div class="col-md-6 mb-3">
                        <label for="commission_percentage" class="form-label"><strong>Commission Percentage (%)</strong><span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control @error('commission_percentage') is-invalid @enderror" name="commission_percentage" placeholder="Enter Commission Percentage" required>
                        @error('commission_percentage')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="header_pdf" class="form-label"><strong>Upload Header PDF</strong></label>
                        <input type="file" class="form-control @error('header_pdf') is-invalid @enderror" name="header_pdf" accept="application/pdf">
                        @error('header_pdf')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="footer_pdf" class="form-label"><strong>Upload Footer PDF</strong></label>
                        <input type="file" class="form-control @error('footer_pdf') is-invalid @enderror" name="footer_pdf" accept="application/pdf">
                        @error('footer_pdf')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
                
                <!-- Buttons -->
                <div class="row mt-4 text-center">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <button type="reset" class="btn btn-secondary px-4">Reset</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('countrySelect').addEventListener('change', function() {
        let selectedOption = this.options[this.selectedIndex];
        let countryCode = selectedOption.getAttribute('data-code');
        let currency = selectedOption.getAttribute('data-currency');

        document.getElementById('countryCode').value = countryCode || '';
        document.getElementById('currency').value = currency || '';
    });
</script>
@endsection
