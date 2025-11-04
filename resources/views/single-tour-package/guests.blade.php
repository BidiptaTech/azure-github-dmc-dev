@extends('layouts.layout')
@section('content')

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css">

<style>
    .guest-card {
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background: linear-gradient(145deg, #ffffff, #f8f9fa);
    }

    .guest-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .form-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
        color: white;
    }

    .form-section .card-header {
        background: transparent;
        border: none;
        padding: 0 0 20px 0;
    }

    .form-section h5 {
        color: white;
        font-weight: 700;
        font-size: 1.5rem;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-section .form-label {
        color: rgba(255, 255, 255, 0.95);
        font-weight: 600;
        margin-bottom: 8px;
    }

    .form-section .form-control,
    .form-section .form-select {
        border-radius: 10px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        background: rgba(255, 255, 255, 0.95);
        padding: 12px 15px;
        transition: all 0.3s ease;
    }

    .form-section .form-control:focus,
    .form-section .form-select:focus {
        border-color: #fff;
        box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
        background: white;
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

    .form-section .input-group .form-select:focus {
        border-right: none;
    }

    .form-section .input-group .btn-outline-light {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        border-left: none;
        color: #667eea;
        transition: all 0.3s ease;
    }

    .form-section .input-group .btn-outline-light:hover {
        background: white !important;
        color: #764ba2;
        transform: scale(1.05);
    }

    .form-section .input-group input[type="password"],
    .form-section .input-group input[type="text"]#app_password {
        border-right: none;
    }

    .btn-gradient {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border: none;
        color: white;
        padding: 12px 30px;
        border-radius: 25px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(245, 87, 108, 0.4);
    }

    .btn-gradient:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(245, 87, 108, 0.6);
        color: white;
    }

    .btn-reset {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        border: none;
        color: #333;
        padding: 12px 30px;
        border-radius: 25px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-reset:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(168, 237, 234, 0.6);
    }

    .table-section {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .table-section h5 {
        color: #667eea;
        font-weight: 700;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .table {
        border-radius: 10px;
        overflow: hidden;
    }

    .table thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 15px;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .table tbody tr {
        transition: all 0.3s ease;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
    }

    .table tbody td {
        padding: 15px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }

    .btn-icon {
        width: 35px;
        height: 35px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-icon:hover {
        transform: scale(1.1);
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }

    .btn-danger {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border: none;
    }

    .alert {
        border-radius: 10px;
        border: none;
        padding: 15px 20px;
        margin-bottom: 20px;
        animation: slideInDown 0.5s ease;
    }

    .alert-success {
        background: linear-gradient(135deg, #81FBB8 0%, #28C76F 100%);
        color: white;
    }

    .alert-danger {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        border: none !important;
        color: white !important;
        border-radius: 5px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        border: none !important;
        color: white !important;
        border-radius: 5px;
    }

    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
    }

    .page-header h4 {
        margin: 0;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .icon-box {
        width: 50px;
        height: 50px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .required {
        color: #ff6b6b;
    }

    .badge-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 5px 12px;
        border-radius: 20px;
        font-weight: 600;
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
                    <p class="mb-0 mt-2" style="opacity: 0.9;">Manage your tour guests efficiently with our beautiful interface</p>
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
                <p class="mb-0" style="opacity: 0.9; font-size: 0.9rem;">Fill in the details below to add or update guest information</p>
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
                        <label for="app_password" class="form-label">
                            <i class="ri-lock-password-line me-1"></i>App Password
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="app_password" name="app_password" 
                                   placeholder="Enter app password" autocomplete="new-password">
                            <button class="btn btn-outline-light" type="button" id="toggleAppPassword" 
                                    style="border: 2px solid rgba(255, 255, 255, 0.3); background: rgba(255, 255, 255, 0.95);">
                                <i class="ri-eye-off-line" id="appPasswordIcon"></i>
                            </button>
                            <button class="btn btn-outline-primary" type="button" id="generatePassword"
                                    style="border: 2px solid rgba(255, 255, 255, 0.3); background: rgba(255, 255, 255, 0.95);">
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
                { data: 'created_at_formatted', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[6, 'desc']],
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

