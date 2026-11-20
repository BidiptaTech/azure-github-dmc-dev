@extends('layouts.layout')
@section('content')

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css">

<style>
    :root {
        --guest-primary: #334155;
        --guest-primary-hover: #1e293b;
        --guest-border: #e2e8f0;
        --guest-bg: #f8fafc;
        --guest-text: #334155;
        --guest-text-muted: #64748b;
    }

    .guest-card {
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        transition: box-shadow 0.2s ease;
        background: #fff;
        border: 1px solid var(--guest-border);
    }

    .guest-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .form-section {
        background: #fff;
        border: 1px solid var(--guest-border);
        border-radius: 12px;
        padding: 28px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    }

    .form-section .card-header {
        background: transparent;
        border: none;
        border-bottom: 1px solid var(--guest-border);
        padding: 0 0 16px 0;
        margin-bottom: 20px;
    }

    .form-section h5 {
        color: var(--guest-primary);
        font-weight: 600;
        font-size: 1.15rem;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-section .card-header p {
        color: var(--guest-text-muted);
        font-size: 0.875rem;
    }

    .form-section .form-label {
        color: var(--guest-text);
        font-weight: 500;
        margin-bottom: 6px;
        font-size: 0.875rem;
    }

    .form-section .form-control,
    .form-section .form-select {
        border-radius: 8px;
        border: 1px solid var(--guest-border);
        background: #fff;
        padding: 10px 14px;
        color: var(--guest-text);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .form-section .form-control:focus,
    .form-section .form-select:focus {
        border-color: var(--guest-primary);
        box-shadow: 0 0 0 3px rgba(51, 65, 85, 0.12);
        background: #fff;
    }

    .form-section .form-control::placeholder {
        color: #94a3b8;
    }

    .form-section .input-group .form-select {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        border-right: none;
    }

    .form-section .input-group .form-control {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .form-section .input-group .btn-outline-light,
    .form-section .input-group .btn-outline-primary {
        border: 1px solid var(--guest-border);
        border-left: none;
        background: var(--guest-bg);
        color: var(--guest-text);
        border-radius: 0 8px 8px 0;
        transition: background 0.2s ease, color 0.2s ease;
    }

    .form-section .input-group .btn-outline-light:hover,
    .form-section .input-group .btn-outline-primary:hover {
        background: var(--guest-border);
        color: var(--guest-primary);
    }

    .form-section .input-group input[type="password"],
    .form-section .input-group input[type="text"]#app_password {
        border-right: none;
    }

    .btn-gradient {
        background: var(--guest-primary);
        border: 1px solid var(--guest-primary);
        color: #fff;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
        transition: background 0.2s ease, border-color 0.2s ease;
    }

    .btn-gradient:hover {
        background: var(--guest-primary-hover);
        border-color: var(--guest-primary-hover);
        color: #fff;
    }

    .btn-reset {
        background: #fff;
        border: 1px solid var(--guest-border);
        color: var(--guest-text);
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
        transition: background 0.2s ease, border-color 0.2s ease;
    }

    .btn-reset:hover {
        background: var(--guest-bg);
        border-color: #cbd5e1;
        color: var(--guest-primary);
    }

    .table-section {
        background: #fff;
        border: 1px solid var(--guest-border);
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    }

    .table-section h5 {
        color: var(--guest-primary);
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .table {
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid var(--guest-border);
    }

    .table thead th {
        background: var(--guest-bg);
        color: var(--guest-text);
        border: none;
        border-bottom: 1px solid var(--guest-border);
        padding: 12px 14px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .table tbody tr {
        transition: background 0.15s ease;
    }

    .table tbody tr:hover {
        background: var(--guest-bg);
    }

    .table tbody td {
        padding: 12px 14px;
        vertical-align: middle;
        border-bottom: 1px solid var(--guest-border);
        color: var(--guest-text);
        font-size: 0.875rem;
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: background 0.2s ease, color 0.2s ease;
    }

    .btn-icon:hover {
        background: var(--guest-bg);
    }

    .btn-primary {
        background: var(--guest-primary);
        border: 1px solid var(--guest-primary);
        color: #fff;
    }

    .btn-primary:hover {
        background: var(--guest-primary-hover);
        border-color: var(--guest-primary-hover);
        color: #fff;
    }

    .btn-danger {
        background: #dc2626;
        border: 1px solid #dc2626;
        color: #fff;
    }

    .btn-danger:hover {
        background: #b91c1c;
        border-color: #b91c1c;
        color: #fff;
    }

    .alert {
        border-radius: 8px;
        border: 1px solid transparent;
        padding: 12px 16px;
        margin-bottom: 16px;
        font-size: 0.875rem;
    }

    .alert-success {
        background: #f0fdf4;
        border-color: #bbf7d0;
        color: #166534;
    }

    .alert-danger {
        background: #fef2f2;
        border-color: #fecaca;
        color: #b91c1c;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--guest-primary) !important;
        border: 1px solid var(--guest-primary) !important;
        color: #fff !important;
        border-radius: 6px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: var(--guest-bg) !important;
        border: 1px solid var(--guest-border) !important;
        color: var(--guest-primary) !important;
        border-radius: 6px;
    }

    .page-header {
        background: #fff;
        border: 1px solid var(--guest-border);
        color: var(--guest-text);
        padding: 24px;
        border-radius: 12px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    }

    .page-header h4 {
        margin: 0;
        font-weight: 600;
        font-size: 1.25rem;
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--guest-primary);
    }

    .page-header p {
        color: var(--guest-text-muted);
        font-size: 0.875rem;
    }

    .page-header .btn-light {
        border: 1px solid var(--guest-border);
        color: var(--guest-text);
        background: #fff;
    }

    .page-header .btn-light:hover {
        background: var(--guest-bg);
        border-color: #cbd5e1;
        color: var(--guest-primary);
    }

    .icon-box {
        width: 44px;
        height: 44px;
        background: var(--guest-bg);
        border: 1px solid var(--guest-border);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: var(--guest-primary);
    }

    .required {
        color: #dc2626;
    }

    .badge-info, .badge.badge-info {
        background: var(--guest-bg);
        color: var(--guest-text-muted);
        border: 1px solid var(--guest-border);
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: 500;
        font-size: 0.75rem;
    }

    .page-header .badge.bg-light {
        background: var(--guest-bg) !important;
        color: var(--guest-text-muted) !important;
        border: 1px solid var(--guest-border);
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        
        <!-- Page Header -->
        <div class="page-header animate__animated animate__fadeInDown">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4>
                        <div class="icon-box">
                            <i class="ri-user-add-line"></i>
                        </div>
                        Guest Management System
                        @if(isset($tourId) && $tourId)
                            <span class="badge bg-light text-dark ms-2">Tour ID: {{ $tourId }}</span>
                        @endif
                    </h4>
                    <p class="mb-0 mt-2">Manage your tour guests efficiently</p>
                </div>
                @if(isset($tourId) && $tourId)
                    <a href="{{ route('bookings.confirmed') }}" class="btn btn-light">
                        <i class="ri-arrow-left-line me-1"></i> Back to Bookings
                    </a>
                @endif
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Guest Form -->
        <div class="form-section animate__animated animate__fadeInUp">
            <div class="card-header">
                <h5>
                    <i class="ri-file-list-3-line"></i>
                    <span id="formTitle">Add New Guest</span>
                </h5>
                <p class="mb-0">Fill in the details below to add or update guest information</p>
            </div>
            <form id="guestForm">
                @csrf
                <input type="hidden" id="guestId" name="guest_id">
                <input type="hidden" id="formMethod" value="POST">
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="guest_name" class="form-label">
                            <i class="ri-user-line me-1"></i>Guest Name <span class="required">*</span>
                        </label>
                        <input value="{{ (!empty($fullName) && $guests->isEmpty()) ? $fullName : '' }}" type="text" class="form-control" id="guest_name" name="guest_name" 
                               placeholder="Enter guest's full name" required>
                        <div class="invalid-feedback" id="guest_name_error"></div>
                    </div>

                    <div class="col-md-6">
                        <label for="tour_id" class="form-label">
                            <i class="ri-map-pin-line me-1"></i>Tour ID
                        </label>
                        <input type="text" class="form-control" id="tour_id" name="tour_id" 
                               placeholder="Enter associated tour ID"
                               value="{{ $tourId ?? '' }}"
                               {{ isset($tourId) && $tourId ? 'readonly' : '' }}>
                        <div class="invalid-feedback" id="tour_id_error"></div>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">
                            <i class="ri-mail-line me-1"></i>Email Address
                        </label>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="guest@example.com" value="{{ (!empty($email) && $guests->isEmpty()) ? $email : '' }}">
                        <div class="invalid-feedback" id="email_error"></div>
                    </div>

                    <div class="col-md-6">
                        <label for="contact" class="form-label">
                            <i class="ri-phone-line me-1"></i>Contact Number
                        </label>
                        <div class="input-group">
                            <select class="form-select" id="country_code" name="country_code" style="max-width: 130px;">
                                <option value="+61" {{ $countryCode == '61' ? 'selected' : '' }}>+61 Australia</option>
                                <option value="+880" {{ $countryCode == '880' ? 'selected' : '' }}>+880 Bangladesh</option>
                                <option value="+32" {{ $countryCode == '32' ? 'selected' : '' }}>+32 Belgium</option>
                                <option value="+55" {{ $countryCode == '55' ? 'selected' : '' }}>+55 Brazil</option>
                                <option value="+86" {{ $countryCode == '86' ? 'selected' : '' }}>+86 China</option>
                                <option value="+420" {{ $countryCode == '420' ? 'selected' : '' }}>+420 Czech Republic</option>
                                <option value="+45" {{ $countryCode == '45' ? 'selected' : '' }}>+45 Denmark</option>
                                <option value="+20" {{ $countryCode == '20' ? 'selected' : '' }}>+20 Egypt</option>
                                <option value="+358" {{ $countryCode == '358' ? 'selected' : '' }}>+358 Finland</option>
                                <option value="+33" {{ $countryCode == '33' ? 'selected' : '' }}>+33 France</option>
                                <option value="+49" {{ $countryCode == '49' ? 'selected' : '' }}>+49 Germany</option>
                                <option value="+30" {{ $countryCode == '30' ? 'selected' : '' }}>+30 Greece</option>
                                <option value="+62" {{ $countryCode == '62' ? 'selected' : '' }}>+62 Indonesia</option>
                                <option value="+91" {{ $countryCode == '91' ? 'selected' : '' }}>+91 India</option>
                                <option value="+353" {{ $countryCode == '353' ? 'selected' : '' }}>+353 Ireland</option>
                                <option value="+39" {{ $countryCode == '39' ? 'selected' : '' }}>+39 Italy</option>
                                <option value="+81" {{ $countryCode == '81' ? 'selected' : '' }}>+81 Japan</option>
                                <option value="+254" {{ $countryCode == '254' ? 'selected' : '' }}>+254 Kenya</option>
                                <option value="+82" {{ $countryCode == '82' ? 'selected' : '' }}>+82 South Korea</option>
                                <option value="+94" {{ $countryCode == '94' ? 'selected' : '' }}>+94 Sri Lanka</option>
                                <option value="+60" {{ $countryCode == '60' ? 'selected' : '' }}>+60 Malaysia</option>
                                <option value="+52" {{ $countryCode == '52' ? 'selected' : '' }}>+52 Mexico</option>
                                <option value="+977" {{ $countryCode == '977' ? 'selected' : '' }}>+977 Nepal</option>
                                <option value="+31" {{ $countryCode == '31' ? 'selected' : '' }}>+31 Netherlands</option>
                                <option value="+64" {{ $countryCode == '64' ? 'selected' : '' }}>+64 New Zealand</option>
                                <option value="+234" {{ $countryCode == '234' ? 'selected' : '' }}>+234 Nigeria</option>
                                <option value="+47" {{ $countryCode == '47' ? 'selected' : '' }}>+47 Norway</option>
                                <option value="+92" {{ $countryCode == '92' ? 'selected' : '' }}>+92 Pakistan</option>
                                <option value="+63" {{ $countryCode == '63' ? 'selected' : '' }}>+63 Philippines</option>
                                <option value="+48" {{ $countryCode == '48' ? 'selected' : '' }}>+48 Poland</option>
                                <option value="+351" {{ $countryCode == '351' ? 'selected' : '' }}>+351 Portugal</option>
                                <option value="+974" {{ $countryCode == '974' ? 'selected' : '' }}>+974 Qatar</option>
                                <option value="+7" {{ $countryCode == '7' ? 'selected' : '' }}>+7 Russia</option>
                                <option value="+966" {{ $countryCode == '966' ? 'selected' : '' }}>+966 Saudi Arabia</option>
                                <option value="+65" {{ $countryCode == '65' ? 'selected' : '' }}>+65 Singapore</option>
                                <option value="+27" {{ $countryCode == '27' ? 'selected' : '' }}>+27 South Africa</option>
                                <option value="+34" {{ $countryCode == '34' ? 'selected' : '' }}>+34 Spain</option>
                                <option value="+46" {{ $countryCode == '46' ? 'selected' : '' }}>+46 Sweden</option>
                                <option value="+41" {{ $countryCode == '41' ? 'selected' : '' }}>+41 Switzerland</option>
                                <option value="+66" {{ $countryCode == '66' ? 'selected' : '' }}>+66 Thailand</option>
                                <option value="+90" {{ $countryCode == '90' ? 'selected' : '' }}>+90 Turkey</option>
                                <option value="+971" {{ $countryCode == '971' ? 'selected' : '' }}>UAE +971</option>
                                <option value="+44" {{ $countryCode == '44' ? 'selected' : '' }}>+44 United Kingdom</option>
                                <option value="+1" {{ $countryCode == '1' ? 'selected' : '' }}>+1 United States</option>
                                <option value="+84" {{ $countryCode == '84' ? 'selected' : '' }}>+84 Vietnam</option>
                            </select>
                            <input value="{{ (!empty($phone) && $guests->isEmpty()) ? $phone : '' }}" type="text" class="form-control" id="contact" name="contact" 
                                   placeholder="123 456 7890" pattern="[0-9\s\-]+" 
                                   title="Please enter a valid phone number">
                        </div>
                        <div class="invalid-feedback" id="contact_error"></div>
                    </div>

                    <div class="col-md-6">
                        <label for="whatsapp_no" class="form-label">
                            <i class="ri-whatsapp-line me-1"></i>WhatsApp Number
                        </label>
                        <input type="text" class="form-control" id="whatsapp_no" name="whatsapp_no" 
                               placeholder="Enter WhatsApp number with country code (e.g., +91 1234567890)" 
                               title="Please enter a valid WhatsApp number with country code">
                        <div class="invalid-feedback" id="whatsapp_no_error"></div>
                    </div>

                    <div class="col-md-6">
                        <label for="app_password" class="form-label">
                            <i class="ri-lock-password-line me-1"></i>App Password
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="app_password" name="app_password" 
                                   placeholder="Enter app password" autocomplete="new-password">
                            <button class="btn btn-outline-light" type="button" id="toggleAppPassword">
                                <i class="ri-eye-off-line" id="appPasswordIcon"></i>
                            </button>
                            <button class="btn btn-outline-primary" type="button" id="generatePassword">
                                <i class="ri-key-line me-1"></i> Generate
                            </button>
                        </div>
                        <div class="invalid-feedback" id="app_password_error"></div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12 d-flex gap-3 justify-content-end">
                        <button type="reset" class="btn btn-reset" id="resetBtn">
                            <i class="ri-refresh-line me-2"></i>Reset Form
                        </button>
                        <button type="submit" class="btn btn-gradient" id="submitBtn">
                            <i class="ri-save-line me-2"></i>
                            <span id="submitBtnText">Save Guest</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Guests Table -->
        <div class="table-section animate__animated animate__fadeInUp animate__delay-1s">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5>
                    <i class="ri-team-line"></i>
                    All Guests
                </h5>
                <span class="badge badge-info">
                    <i class="ri-information-line me-1"></i>
                    Real-time updates
                </span>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover" id="guestsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Guest ID</th>
                            <th>Guest Name</th>
                            <th>Tour ID</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>WhatsApp No.</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables will populate this -->
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    let table;
    let isEditMode = false;
    const tourId = "{{ $tourId ?? '' }}";

    // Set up AJAX headers with CSRF token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Initialize DataTable
    function initializeDataTable() {
        table = $('#guestsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('guests.data') }}",
                type: 'GET',
                data: function(d) {
                    if (tourId) {
                        d.tour_id = tourId;
                    }
                },
                error: function(xhr, error, code) {
                    console.log('DataTables Ajax Error:');
                    console.log('Status:', xhr.status);
                    console.log('Error:', error);
                    console.log('Code:', code);
                    console.log('Response:', xhr.responseText);
                    console.log('URL:', "{{ route('guests.data') }}");
                    
                    let errorMsg = 'Error loading guests data.';
                    if (xhr.status === 404) {
                        errorMsg = 'Route not found (404). Please check routes configuration.';
                    } else if (xhr.status === 500) {
                        errorMsg = 'Server error (500). Check server logs.';
                    } else if (xhr.status === 419) {
                        errorMsg = 'Session expired (419). Please refresh the page.';
                    }
                    
                    showAlert('danger', errorMsg + ' Status: ' + xhr.status);
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'guest_id', name: 'guest_id' },
                { data: 'guest_name', name: 'guest_name' },
                { data: 'tour_id', name: 'tour_id' },
                { data: 'email', name: 'email' },
                { data: 'contact', name: 'contact' },
                { data: 'whatsapp_no', name: 'whatsapp_no' },
                { data: 'created_at_formatted', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[7, 'desc']],
            pageLength: 10,
            responsive: true,
            language: {
                processing: '<i class="ri-loader-4-line ri-spin ri-2x"></i>',
                emptyTable: 'No guests found',
                zeroRecords: 'No matching guests found'
            }
        });
    }

    // Show alert message
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show animate__animated animate__fadeInDown" role="alert">
                <i class="ri-${type === 'success' ? 'checkbox-circle' : 'error-warning'}-line me-2"></i>
                <strong>${message}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#alertContainer').html(alertHtml);
        
        setTimeout(() => {
            $('.alert').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Reset form
    function resetForm() {
        $('#guestForm')[0].reset();
        $('#guestId').val('');
        $('#formMethod').val('POST');
        $('#formTitle').text('Add New Guest');
        $('#submitBtnText').text('Save Guest');
        isEditMode = false;
        $('.form-control').removeClass('is-invalid');
        $('.form-select').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        // Reset country code to default
        $('#country_code').val('+91');
        
        // Preserve tour_id if it was passed via URL
        if (tourId) {
            $('#tour_id').val(tourId);
        }
    }

    // Submit form
    $('#guestForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const guestId = $('#guestId').val();
        const url = guestId ? "{{ route('guests.update', ['guestId' => '__ID__']) }}".replace('__ID__', guestId) : "{{ route('guests.store') }}";
        const method = guestId ? 'PUT' : 'POST';
        
        // For PUT request with FormData, we need to add _method
        if (guestId) {
            formData.append('_method', 'PUT');
        }
        
        // Clear previous errors
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        // Disable submit button
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-2"></i>Processing...');
        
        $.ajax({
            url: url,
            method: 'POST', // Always use POST for FormData, _method will handle PUT
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    resetForm();
                    table.ajax.reload(null, false);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        $(`#${key}`).addClass('is-invalid');
                        $(`#${key}_error`).text(value[0]);
                    });
                    showAlert('danger', 'Please fix the validation errors');
                } else {
                    showAlert('danger', xhr.responseJSON?.message || 'An error occurred');
                }
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Edit guest
    $(document).on('click', '.edit-guest', function() {
        const data = $(this).data();
        
        // Parse tour_id (it's a JSON array)
        let tourIdValue = '';
        try {
            const tourIdArray = typeof data.tourId === 'string' ? JSON.parse(data.tourId) : data.tourId;
            // Display all tour IDs as comma-separated for editing
            tourIdValue = Array.isArray(tourIdArray) ? tourIdArray.join(', ') : tourIdArray;
        } catch (e) {
            tourIdValue = data.tourId || '';
        }
        
        $('#guestId').val(data.guestId);
        $('#guest_name').val(data.guestName);
        $('#tour_id').val(tourIdValue);
        $('#email').val(data.email);
        $('#country_code').val(data.countryCode || '+91');
        $('#contact').val(data.contact);
        $('#whatsapp_no').val(data.whatsappNo || data['whatsapp-no'] || '');
        $('#app_password').val(data.appPassword || '');
        $('#formMethod').val('PUT');
        $('#formTitle').text('Update Guest Information');
        $('#submitBtnText').text('Update Guest');
        isEditMode = true;
        
        // Scroll to form
        $('html, body').animate({
            scrollTop: $('.form-section').offset().top - 100
        }, 500);
        
        // Highlight form
        $('.form-section').addClass('animate__animated animate__pulse');
        setTimeout(() => {
            $('.form-section').removeClass('animate__pulse');
        }, 1000);
    });

    // Delete guest
    $(document).on('click', '.delete-guest', function() {
        const guestId = $(this).data('guestId');
        
        if (confirm('Are you sure you want to delete this guest? This action cannot be undone.')) {
            $.ajax({
                url: "{{ route('guests.destroy', ['guestId' => '__ID__']) }}".replace('__ID__', guestId),
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        table.ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    showAlert('danger', xhr.responseJSON?.message || 'Error deleting guest');
                }
            });
        }
    });

    // Reset button
    $('#resetBtn').on('click', function() {
        resetForm();
    });

    // Toggle App Password visibility
  

    // Initialize DataTable on page load
    initializeDataTable();
});


document.getElementById('generatePassword').addEventListener('click', function() {
    const charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789@#$!";
    let password = "";
    for (let i = 0; i < 8; i++) {
        password += charset.charAt(Math.floor(Math.random() * charset.length));
    }

    const passwordInput = document.getElementById('app_password');
    const icon = document.getElementById('appPasswordIcon');

    // Set the generated password
    passwordInput.value = password;

    // Respect current visibility state
    if (passwordInput.type === 'password') {
        // keep hidden
        icon.classList.remove('ri-eye-line');
        icon.classList.add('ri-eye-off-line');
    } else {
        // visible
        icon.classList.remove('ri-eye-off-line');
        icon.classList.add('ri-eye-line');
    }
});


// Toggle password visibility
document.getElementById('toggleAppPassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('app_password');
    const icon = document.getElementById('appPasswordIcon');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('ri-eye-off-line');
        icon.classList.add('ri-eye-line');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('ri-eye-line');
        icon.classList.add('ri-eye-off-line');
    }
});

</script>
@endsection

