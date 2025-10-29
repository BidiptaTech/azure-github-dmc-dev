@extends('layouts.layout')

@section('title', 'Tax Settings')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Tax /</span> Tax Settings
    </h4>

    <!-- Display flash message -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                var alert = document.getElementById('success-alert');
                if (alert) {
                    var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }
            }, 3000);
        });
    </script>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
        <i class="ri-error-warning-line me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                var alert = document.getElementById('error-alert');
                if (alert) {
                    var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }
            }, 5000);
        });
    </script>
    @endif

    <div class="row">
        <!-- Quick Add Tax Card -->
        <div class="col-md-8 offset-md-2">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ri-add-circle-line me-2 text-primary"></i>Quick Add Tax
                    </h5>
                </div>
                <div class="card-body">
                    <form id="quickAddTaxForm" action="{{ route('tax.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Tax Name <span class="text-danger">*</span></label>
                            <input type="text" name="tax_name" class="form-control" placeholder="e.g., VAT, GST, Service Tax" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tax Type <span class="text-danger">*</span></label>
                                <select name="tax_type" id="tax_type" class="form-select" required>
                                    <option value="">Select Type</option>
                                    <option value="percentage">Percentage (%)</option>
                                    <option value="fixed">Fixed Amount ($)</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tax Value <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="tax_value" class="form-control" placeholder="0.00" required>
                            </div>
                            <div class="col-md-4 mb-3" id="if_fixed_section" style="display: none;">
                                <label class="form-label">If fixed <span class="text-danger">*</span></label>
                                <select name="if_fixed" id="if_fixed" class="form-select">
                                    <option value="">Select Base</option>
                                    <option value="person">Per person</option>
                                    <option value="tour">Per tour</option>
                                    <option value="person_tour">Per person per tour</option>
                                    <option value="person_day">Per person per day</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Calculate On <span class="text-danger">*</span></label>
                            <select name="calculate_on" class="form-select" required>
                                <option value="">Select Option</option>
                                <option value="total">Total Amount</option>
                                @foreach($allTaxes as $index => $existingTax)
                                    <option value="tax_{{ $existingTax->tax_id }}">{{ $existingTax->tax_name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Choose when this tax should be calculated</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Optional - Add description or notes"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-save-line me-1"></i> Create Tax
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const taxType = document.getElementById('tax_type');
    const ifFixedSection = document.getElementById('if_fixed_section');
    const ifFixed = document.getElementById('if_fixed');
    
    taxType.addEventListener('change', function() {
        if (this.value === 'fixed') {
            ifFixedSection.style.display = 'block';
            ifFixed.setAttribute('required', 'required');
        } else {
            ifFixedSection.style.display = 'none';
            ifFixed.removeAttribute('required');
            ifFixed.value = '';
        }
    });
});
</script>
@endsection

