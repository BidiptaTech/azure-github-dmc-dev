@extends('layouts.layout')

@section('title', 'Add Agent')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Add New Special Discount
                <a href="{{ route('discount.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form action="{{ route('discount.store') }}" method="POST" enctype="multipart/form-data" class="card-body">
                @csrf
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="discount_amount" class="form-label"><strong>Discount Amount</strong><span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('discount_amount') is-invalid @enderror" name="discount_amount" step="0.01" placeholder="Enter Discount Amount" required>
                        @error('discount_amount')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                
                    <div class="col-md-3 mb-3">
                        <label for="discount_type" class="form-label"><strong>Discount Type</strong><span class="text-danger">*</span></label>
                        <select class="form-control" name="discount_type" required>
                            <option value="">Select Type</option>
                            <option value="percentage">Percentage</option>
                            <option value="fixed">Fixed Amount</option>
                        </select>
                        @error('discount_type')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                
                    <div class="col-md-3 mb-3">
                        <label for="expiry" class="form-label"><strong>Expiry Date</strong><span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('expiry') is-invalid @enderror" name="expiry" id="expiry" required>
                        @error('expiry')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                
                    <div class="col-md-3 mb-3">
                        <label for="agent_id" class="form-label"><strong>Select Agent</strong><span class="text-danger">*</span></label>
                        <select class="form-control" name="agent_id" required>
                            <option value="">Select Agent</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->agent_id }}">{{ $agent->name }}</option>
                            @endforeach
                        </select>
                        @error('agent_id')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>                    
                </div>
                
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font/css/materialdesignicons.min.css">
<script>
    const APP_URL = @json(config('app.url')); // Get APP_URL from Laravel
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Get today's date in YYYY-MM-DD format
        let today = new Date().toISOString().split('T')[0];

        // Set the min attribute of the expiry date input field
        document.getElementById("expiry").setAttribute("min", today);
    });
</script>

@endsection
