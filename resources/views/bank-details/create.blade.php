@extends('layouts.layout')

@section('title', 'Create Bank Details')

@section('content')
<style>
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
    }
    
    .modern-card {
        border: none;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        border-radius: 16px;
    }
    
    .section-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem 2rem;
        border-radius: 12px 12px 0 0;
        font-weight: 600;
    }
    
    .form-control, .form-select {
        border: 2px solid #e3e6f0;
        border-radius: 10px;
        padding: 0.875rem 1.125rem;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
    }
    
    .form-label {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }
    
    .btn-modern {
        border-radius: 10px;
        padding: 0.875rem 1.75rem;
        font-weight: 600;
    }
    
    .payment-term-row {
        margin-bottom: 1rem;
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="mb-2"><i class="ri-bank-line me-2"></i>Create Bank Details</h2>
                    <p class="mb-0 opacity-90">Add new bank details, payment terms, and terms & conditions</p>
                </div>
                <a href="{{ route('bank-details.index') }}" class="btn btn-light">
                    <i class="ri-arrow-left-line me-2"></i>Back to List
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('bank-details.store') }}" method="POST">
            @csrf
            
            <!-- Terms & Conditions Section -->
            <div class="card modern-card mb-4">
                <div class="section-header">
                    <h5 class="mb-0"><i class="ri-file-text-line me-2"></i>Terms & Conditions</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="terms_and_conditions" class="form-label">
                            <i class="ri-file-edit-line me-2"></i>Terms & Conditions
                        </label>
                        <textarea name="terms_and_conditions" id="terms_and_conditions" rows="5" 
                                  class="form-control" placeholder="Enter terms and conditions...">{{ old('terms_and_conditions') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Payment Terms Section -->
            <div class="card modern-card mb-4">
                <div class="section-header">
                    <h5 class="mb-0"><i class="ri-list-check me-2"></i>Payment Terms</h5>
                </div>
                <div class="card-body">
                    <div id="payment-terms-container">
                        @if(old('payment_terms'))
                            @foreach(old('payment_terms') as $index => $term)
                                <div class="payment-term-row">
                                    <div class="input-group">
                                        <span class="input-group-text">{{ $index + 1 }}.</span>
                                        <input type="text" name="payment_terms[]" class="form-control" 
                                               value="{{ $term }}" placeholder="Enter payment term...">
                                        <button type="button" class="btn btn-danger remove-term" onclick="removePaymentTerm(this)">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="payment-term-row">
                                <div class="input-group">
                                    <span class="input-group-text">1.</span>
                                    <input type="text" name="payment_terms[]" class="form-control" 
                                           placeholder="Please pay the amount before the payment due date to avoid auto release of booking. Bank details are mentioned below.">
                                    <button type="button" class="btn btn-danger remove-term" onclick="removePaymentTerm(this)" style="display: none;">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-primary mt-3" onclick="addPaymentTerm()">
                        <i class="ri-add-line me-2"></i>Add Payment Term
                    </button>
                </div>
            </div>

            <!-- Bank Details Section -->
            <div class="card modern-card mb-4">
                <div class="section-header">
                    <h5 class="mb-0"><i class="ri-bank-card-line me-2"></i>Bank Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="account_name" class="form-label">
                                <i class="ri-user-line me-2"></i>Account Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="account_name" id="account_name" 
                                   class="form-control @error('account_name') is-invalid @enderror" 
                                   value="{{ old('account_name') }}" required>
                            @error('account_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="account_number" class="form-label">
                                <i class="ri-hashtag me-2"></i>Account Number <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="account_number" id="account_number" 
                                   class="form-control @error('account_number') is-invalid @enderror" 
                                   value="{{ old('account_number') }}" required>
                            @error('account_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bank_address" class="form-label">
                            <i class="ri-map-pin-line me-2"></i>Bank Address <span class="text-danger">*</span>
                        </label>
                        <textarea name="bank_address" id="bank_address" rows="3" 
                                  class="form-control @error('bank_address') is-invalid @enderror" required>{{ old('bank_address') }}</textarea>
                        @error('bank_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="ri-price-tag-3-line me-2"></i>Account Type Label (e.g. SGD Accounts, USA Accounts)
                            </label>
                            <input type="text" name="bank_type" id="bank_type"
                                   class="form-control @error('bank_type') is-invalid @enderror"
                                   value="{{ old('bank_type', 'SGD Accounts') }}">
                            @error('bank_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ifsc" class="form-label">
                                <i class="ri-building-line me-2"></i>IFSC (For India only)
                            </label>
                            <input type="text" name="ifsc" id="ifsc" 
                                   class="form-control @error('ifsc') is-invalid @enderror" 
                                   value="{{ old('ifsc') }}">
                            @error('ifsc')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="swift_bic_iban" class="form-label">
                                <i class="ri-global-line me-2"></i>SWIFT / BIC / IBAN Code (as applicable for international, Europe transfers)
                            </label>
                            <input type="text" name="swift_bic_iban" id="swift_bic_iban" 
                                   class="form-control @error('swift_bic_iban') is-invalid @enderror" 
                                   value="{{ old('swift_bic_iban') }}">
                            @error('swift_bic_iban')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="bank_code" class="form-label">
                                <i class="ri-building-2-line me-2"></i>Bank Code (For Singapore)
                            </label>
                            <input type="text" name="bank_code" id="bank_code" 
                                   class="form-control @error('bank_code') is-invalid @enderror" 
                                   value="{{ old('bank_code') }}">
                            @error('bank_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="branch_code" class="form-label">
                                <i class="ri-building-2-line me-2"></i>Branch Code (For Singapore)
                            </label>
                            <input type="text" name="branch_code" id="branch_code" 
                                   class="form-control @error('branch_code') is-invalid @enderror" 
                                   value="{{ old('branch_code') }}">
                            @error('branch_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="aba_routing" class="form-label">
                            <i class="ri-global-line me-2"></i>ABA / Routing Number (For USA only)
                        </label>
                        <input type="text" name="aba_routing" id="aba_routing" 
                               class="form-control @error('aba_routing') is-invalid @enderror" 
                               value="{{ old('aba_routing') }}">
                        @error('aba_routing')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                   value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <i class="ri-toggle-line me-2"></i>Active
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- India Bank Details Section (stored as JSON) -->
            <div class="card modern-card mb-4">
                <div class="section-header">
                    <h5 class="mb-0"><i class="ri-bank-card-line me-2"></i>Second Bank Details (e.g. India / INR Accounts)</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">GST Registration Number</label>
                            <input type="text" name="india_gst_number" class="form-control"
                                   value="{{ old('india_gst_number') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">PAN Number</label>
                            <input type="text" name="india_pan_number" class="form-control"
                                   value="{{ old('india_pan_number') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Account Name</label>
                            <input type="text" name="india_account_name" class="form-control"
                                   value="{{ old('india_account_name') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Account Number</label>
                            <input type="text" name="india_account_number" class="form-control"
                                   value="{{ old('india_account_number') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="india_bank_name" class="form-control"
                                   value="{{ old('india_bank_name') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">IFSC Code</label>
                            <input type="text" name="india_ifsc" class="form-control"
                                   value="{{ old('india_ifsc') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Account Type Label (e.g. INR Accounts)</label>
                            <input type="text" name="india_bank_type" class="form-control"
                                   value="{{ old('india_bank_type', 'INR Accounts') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bank Address</label>
                        <textarea name="india_bank_address" rows="3"
                                  class="form-control">{{ old('india_bank_address') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('bank-details.index') }}" class="btn btn-secondary">
                    <i class="ri-close-line me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-modern">
                    <i class="ri-save-line me-2"></i>Create Bank Details
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function addPaymentTerm() {
        const container = document.getElementById('payment-terms-container');
        const rows = container.querySelectorAll('.payment-term-row');
        const count = rows.length + 1;
        
        const newRow = document.createElement('div');
        newRow.className = 'payment-term-row';
        newRow.innerHTML = `
            <div class="input-group">
                <span class="input-group-text">${count}.</span>
                <input type="text" name="payment_terms[]" class="form-control" placeholder="Enter payment term...">
                <button type="button" class="btn btn-danger remove-term" onclick="removePaymentTerm(this)">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        `;
        
        container.appendChild(newRow);
        
        // Show remove buttons if more than one row
        if (rows.length >= 1) {
            rows.forEach(row => {
                const removeBtn = row.querySelector('.remove-term');
                if (removeBtn) removeBtn.style.display = 'block';
            });
        }
    }
    
    function removePaymentTerm(button) {
        const rows = document.querySelectorAll('.payment-term-row');
        if (rows.length > 1) {
            button.closest('.payment-term-row').remove();
            
            // Renumber remaining rows
            document.querySelectorAll('.payment-term-row').forEach((row, index) => {
                row.querySelector('.input-group-text').textContent = (index + 1) + '.';
            });
            
            // Hide remove buttons if only one row left
            if (rows.length === 2) {
                document.querySelectorAll('.remove-term').forEach(btn => {
                    btn.style.display = 'none';
                });
            }
        }
    }
</script>
@endsection

