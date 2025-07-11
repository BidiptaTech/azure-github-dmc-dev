@extends('layouts.layout')
@section('title', 'Hotels')
@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

@include('hotel.tapview', ['hotel' => $hotel])
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Hotel Contact Details
                {{-- <a href="javascript:history.back()" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a> --}}
            </h5>
            
            <form id="hotelForm" method="POST" action="{{ route('hotels.createcontacts') }}" class="card-body">
                @csrf
                <input type="hidden" class="form-control" name="id" value="{{ $hotel->hotel_unique_id }}">
                <div class="row g-4">
                    <!-- Hotel Owner and Management Details -->
                    <fieldset id="tarrifs" class="border-0 border-top p-4 rounded mb-4" style="border-top: 2px solid green !important;">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <h6 class="border-bottom pb-2">Hotel Owner & Management Company</h6>
                                <div class="mb-3">
                                    <label class="form-label"><strong>Hotel Owner Name</strong><span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="hotel_owner_company_name"
                                        value="{{ old('hotel_owner_company_name', $hotel->hotel_owner_company_name ?? '') }}"
                                        placeholder="Hotel Owner Name"
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                        @error('hotel_owner_company_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><strong>Hotel Management Company Name</strong></label>
                                    <input type="text" class="form-control" name="management_comp_name"
                                        value="{{ old('management_comp_name', $hotel->management_comp_name ?? '') }}"
                                        placeholder="Hotel Owner Name"
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                        @error('management_comp_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                </div>
                            </div>

                            <!-- Reservation Details -->
                            <div class="col-md-4">
                                <h6 class="border-bottom pb-2">Reservation Details</h6>
                                <div class="mb-3">
                                    <label class="form-label"><strong>Reservation Contact No</strong></label>
                                    <input type="text" class="form-control" id="hotel_reservation_cont_no" name="hotel_reservation_cont_no"
                                        value="{{ old('hotel_reservation_cont_no', $hotel->hotel_reservation_cont_no ?? '') }}"
                                        placeholder="Hotel Reservation Contact No"
                                        oninput="validatePhone(this)"
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                    <small class="validation-message" id="hotel_reservation_cont_no-validation-message"></small>
                                    @error('hotel_reservation_cont_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><strong>Reservation Email</strong></label>
                                    <input type="email" class="form-control" id="hotel_reservation_email" name="hotel_reservation_email"
                                        value="{{ old('hotel_reservation_email', $hotel->hotel_reservation_email ?? '') }}"
                                        placeholder="Hotel Reservation Email"
                                        oninput="validateEmail(this)">
                                    <small class="validation-message" id="hotel_reservation_email-validation-message"></small>
                                    @error('hotel_reservation_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Revenue Director Details -->
                            <div class="col-md-4">
                                <h6 class="border-bottom pb-2">Revenue Director Details</h6>
                                <div class="mb-3">
                                    <label class="form-label"><strong>Contact No</strong></label>
                                    <input type="text" class="form-control" id="revenue_director_cont_no" name="revenue_director_cont_no"
                                        value="{{ old('revenue_director_cont_no', $hotel->revenue_director_cont_no ?? '') }}"
                                        placeholder="Revenue Director Contact No"
                                        oninput="validatePhone(this)"
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                    <small class="validation-message" id="revenue_director_cont_no-validation-message"></small>
                                    @error('revenue_director_cont_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><strong>Email</strong></label>
                                    <input type="email" class="form-control" id="revenue_director_email" name="revenue_director_email"
                                        value="{{ old('revenue_director_email', $hotel->revenue_director_email ?? '') }}"
                                        placeholder="Revenue Director Email"
                                        oninput="validateEmail(this)"
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                    <small class="validation-message" id="revenue_director_email-validation-message"></small>
                                    @error('revenue_director_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Sales & Marketing Details -->
                    <fieldset id="tarrifs" class="border-0 border-top p-4 rounded mb-4" style="border-top: 2px solid green !important;">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <h6 class="border-bottom pb-2">Sales & Marketing Details</h6>
                            <div class="mb-3">
                                <label class="form-label"><strong>Contact No</strong></label>
                                <input type="text" class="form-control" id="sales_director_cont_no" name="sales_director_cont_no"
                                    value="{{ old('sales_director_cont_no', $hotel->sales_director_cont_no ?? '') }}"
                                    placeholder="Sales & Marketing Contact No"
                                    oninput="validatePhone(this)"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="sales_director_cont_no-validation-message"></small>
                                @error('sales_director_cont_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Email</strong></label>
                                <input type="email" class="form-control" id="sales_director_email" name="sales_director_email"
                                    value="{{ old('sales_director_email', $hotel->sales_director_email ?? '') }}"
                                    placeholder="Sales & Marketing Email"
                                    oninput="validateEmail(this)"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="sales_director_email-validation-message"></small>
                                @error('sales_director_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Finance Director Details -->
                        <div class="col-md-4">
                            <h6 class="border-bottom pb-2">Finance Director Details</h6>
                            <div class="mb-3">
                                <label class="form-label"><strong>Contact No</strong></label>
                                <input type="text" class="form-control" id="finance_director_cont_no" name="finance_director_cont_no"
                                    value="{{ old('finance_director_cont_no', $hotel->finance_director_cont_no ?? '') }}"
                                    placeholder="Finance Director Contact No"
                                    oninput="validatePhone(this)"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="finance_director_cont_no-validation-message"></small>
                                @error('finance_director_cont_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Email</strong></label>
                                <input type="email" class="form-control" id="finance_director_email" name="finance_director_email"
                                    value="{{ old('finance_director_email', $hotel->finance_director_email ?? '') }}"
                                    placeholder="Finance Director Email"
                                    oninput="validateEmail(this)"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="finance_director_email-validation-message"></small>
                                @error('finance_director_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Food & Beverage Director Details -->
                        <div class="col-md-4">
                            <h6 class="border-bottom pb-2">Food & Beverage Director Details</h6>
                            <div class="mb-3">
                                <label class="form-label"><strong>Contact No</strong></label>
                                <input type="text" class="form-control" id="beverage_director_cont_no" name="beverage_director_cont_no"
                                    value="{{ old('food_beverage_director_cont_no', $hotel->food_beverage_director_cont_no ?? '') }}"
                                    placeholder="Food & Beverage Contact No"
                                    oninput="validatePhone(this)"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="beverage_director_cont_no-validation-message"></small>
                                @error('beverage_director_cont_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Email</strong></label>
                                <input type="email" class="form-control" id="beverage_director_email" name="beverage_director_email"
                                    value="{{ old('food_beverage_director_email', $hotel->food_beverage_director_email ?? '') }}"
                                    placeholder="Food & Beverage Email"
                                    oninput="validateEmail(this)"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="beverage_director_email-validation-message"></small>
                                @error('beverage_director_cont_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    </fieldset>

                    <fieldset id="tarrifs" class="border-0 border-top p-4 rounded mb-4" style="border-top: 2px solid green !important;">
                    <div class="row g-4">
                        <!-- Marketing Manager Details -->
                        <div class="col-md-4">
                            <h6 class="border-bottom pb-2">Marketing Manager Details</h6>
                            <div class="mb-3">
                                <label class="form-label"><strong>Contact No</strong></label>
                                <input type="text" class="form-control" id="marketing_manager_cont_no" name="marketing_manager_cont_no"
                                    value="{{ old('marketing_manager_cont_no', $hotel->marketing_manager_cont_no ?? '') }}"
                                    placeholder="Marketing Manager Contact No"
                                    oninput="validatePhone(this)"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="marketing_manager_cont_no-validation-message"></small>
                                @error('marketing_manager_cont_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Email</strong></label>
                                <input type="email" class="form-control" id="marketing_manager_email" name="marketing_manager_email"
                                    value="{{ old('marketing_manager_email', $hotel->marketing_manager_email ?? '') }}"
                                    placeholder="Marketing Manager Email"
                                    oninput="validateEmail(this)"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="marketing_manager_email-validation-message"></small>
                                @error('marketing_manager_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Account Manager Details -->
                        <div class="col-md-4">
                            <h6 class="border-bottom pb-2">Account Manager Details</h6>
                            <div class="mb-3">
                                <label class="form-label"><strong>Contact No</strong></label>
                                <input type="text" class="form-control" id="account_manager_cont_no" name="account_manager_cont_no"
                                    value="{{ old('account_manager_cont_no', $hotel->account_manager_cont_no ?? '') }}"
                                    placeholder="Account Manager Contact No"
                                    oninput="validatePhone(this)"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="account_manager_cont_no-validation-message"></small>
                                @error('account_manager_cont_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Email</strong></label>
                                <input type="email" class="form-control" id="account_manager_email" name="account_manager_email"
                                    value="{{ old('account_manager_email', $hotel->account_manager_email ?? '') }}"
                                    placeholder="Account Manager Email"
                                    oninput="validateEmail(this)"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="account_manager_email-validation-message"></small>
                                @error('account_manager_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- General Manager Details -->
                        <div class="col-md-4">
                            <h6 class="border-bottom pb-2">General Manager Details</h6>
                            <div class="mb-3">
                                <label class="form-label"><strong>Contact No</strong></label>
                                <input type="text" class="form-control" id="general_manager_cont_no" name="general_manager_cont_no"
                                    value="{{ old('general_manager_cont_no', $hotel->general_manager_cont_no ?? '') }}"
                                    placeholder="General Manager Contact No"
                                    oninput="validatePhone(this)"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="general_manager_cont_no-validation-message"></small>
                                @error('general_manager_cont_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><strong>Email</strong></label>
                                <input type="email" class="form-control" id="general_manager_email" name="general_manager_email"
                                    value="{{ old('general_manager_email', $hotel->general_manager_email ?? '') }}"
                                    placeholder="General Manager Email"
                                    oninput="validateEmail(this)"
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                <small class="validation-message" id="general_manager_email-validation-message"></small>
                                @error('general_manager_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    </fieldset>

                    <div class="col-md-4">
                        <h6 class="border-bottom pb-2">Whatsapp Details</h6>
                        <div class="mb-3">
                            <label class="form-label"><strong>Duty Manager Whatsapp No</strong></label>
                            <input type="text" class="form-control" id="whatsapp" name="whatsapp"
                                value="{{ old('whatsapp', $hotel->whatsapp ?? '') }}"
                                placeholder="Whatsapp Number"
                                oninput="validatePhone(this)"
                                @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                            <small class="validation-message" id="whatsapp-validation-message"></small>
                            @error('whatsapp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" class="btn btn-primary px-4"
                    >Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script>
    function showValidationMessage(inputElement, isValid, message) {
        const messageElement = document.getElementById(`${inputElement.id}-validation-message`);
        
        if (!messageElement) return;
        
        if (isValid) {
            messageElement.innerHTML = `
                <div class="valid-feedback d-block">
                    <i class="fas fa-check-circle text-success"></i> 
                    Looks good!
                </div>`;
            inputElement.classList.remove('is-invalid');
            inputElement.classList.add('is-valid');
        } else {
            messageElement.innerHTML = `
                <div class="invalid-feedback d-block">
                    <i class="fas fa-exclamation-circle"></i> 
                    ${message}
                </div>`;
            inputElement.classList.remove('is-valid');
            inputElement.classList.add('is-invalid');
        }
    }

    // function validateEmail(input) {
    //     const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    //     const value = input.value.trim();
        
    //     if (value === '') {
    //         showValidationMessage(input, false, 'Email address is required');
    //     } else if (!emailRegex.test(value)) {
    //         showValidationMessage(input, false, `
    //             Please enter a valid email address:
    //             <ul class="mt-1 mb-0">
    //                 <li>Must contain @ symbol</li>
    //                 <li>Must end with a valid domain (e.g., .com, .org)</li>
    //                 <li>Example: john.doe@example.com</li>
    //             </ul>
    //         `);
    //     } else {
    //         showValidationMessage(input, true, '');
    //     }
    // }

    // function validatePhone(input) {
    //     // Force numeric input by immediately replacing non-numeric characters
    //     input.value = input.value.replace(/[^0-9]/g, '');
        
    //     const phoneRegex = /^[0-9]{8,15}$/;
    //     const value = input.value.trim();
        
    //     if (value === '') {
    //         showValidationMessage(input, false, 'Phone number is required');
    //     } else if (!phoneRegex.test(value)) {
    //         showValidationMessage(input, false, `
    //             Please enter a valid phone number:
    //             <ul class="mt-1 mb-0">
    //                 <li>Must contain 8-15 digits</li>
    //                 <li>Only numbers are allowed (0-9)</li>
    //                 <li>No spaces or special characters</li>
    //             </ul>
    //         `);
    //     } else {
    //         showValidationMessage(input, true, '');
    //     }
    // }

    function validateEmail(input) {
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        const value = input.value.trim();
        
        if (value === '') {
            // Remove validation when field is empty
            input.classList.remove('is-invalid');
            input.classList.remove('is-valid');
            const messageElement = document.getElementById(`${input.id}-validation-message`);
            if (messageElement) {
                messageElement.innerHTML = '';
            }
        } else if (!emailRegex.test(value)) {
            showValidationMessage(input, false, `
                Please enter a valid email address:
                <ul class="mt-1 mb-0">
                    <li>Must contain @ symbol</li>
                    <li>Must end with a valid domain (e.g., .com, .org)</li>
                    <li>Example: john.doe@example.com</li>
                </ul>
            `);
        } else {
            showValidationMessage(input, true, '');
        }
    }

    function validatePhone(input) {
        // Force numeric input by immediately replacing non-numeric characters
        input.value = input.value.replace(/[^0-9]/g, '');
        
        const phoneRegex = /^[0-9]{8,15}$/;
        const value = input.value.trim();
        
        if (value === '') {
            // Remove validation when field is empty
            input.classList.remove('is-invalid');
            input.classList.remove('is-valid');
            const messageElement = document.getElementById(`${input.id}-validation-message`);
            if (messageElement) {
                messageElement.innerHTML = '';
            }
        } else if (!phoneRegex.test(value)) {
            showValidationMessage(input, false, `
                Please enter a valid phone number:
                <ul class="mt-1 mb-0">
                    <li>Must contain 8-15 digits</li>
                    <li>Only numbers are allowed (0-9)</li>
                    <li>No spaces or special characters</li>
                </ul>
            `);
        } else {
            showValidationMessage(input, true, '');
        }
    }

    // Add CSS for validation messages and input field icons
    document.head.insertAdjacentHTML('beforeend', `
        <style>
            /* Base validation message styles */
            .validation-message {
                margin-top: 0.5rem;
                font-size: 0.85rem;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            }

            /* Error state styles */
            .validation-message .invalid-feedback {
                display: block;
                color: #e74c3c;
                background-color: #fef5f5;
                border-left: 3px solid #e74c3c;
                padding: 0.75rem 1rem;
                border-radius: 4px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                animation: slideIn 0.3s ease-in-out;
            }

            /* Success state styles */
            .validation-message .valid-feedback {
                display: block;
                color: #2ecc71;
                background-color: #f4fff6;
                border-left: 3px solid #2ecc71;
                padding: 0.75rem 1rem;
                border-radius: 4px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                animation: slideIn 0.3s ease-in-out;
            }

            /* List styles within validation messages */
            .validation-message ul {
                margin: 0.5rem 0 0 0;
                padding-left: 1.5rem;
                list-style-type: none;
            }

            .validation-message ul li {
                position: relative;
                padding: 0.2rem 0;
                color: #666;
            }

            .validation-message ul li::before {
                content: "•";
                color: #e74c3c;
                font-weight: bold;
                position: absolute;
                left: -1rem;
            }

            /* Icon styles */
            .validation-message i {
                margin-right: 0.5rem;
                font-size: 1rem;
            }

            /* Input field styles with validation icons */
            .form-control.is-valid {
                border-color: #2ecc71 !important;
                background-color: #fff !important;
                padding-right: calc(1.5em + 0.75rem);
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%232ecc71' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right calc(0.375em + 0.1875rem) center;
                background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
            }

            .form-control.is-invalid {
                border-color: #e74c3c !important;
                background-color: #fff !important;
                padding-right: calc(1.5em + 0.75rem);
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23e74c3c'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23e74c3c' stroke='none'/%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right calc(0.375em + 0.1875rem) center;
                background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
            }

            /* Animation for validation messages */
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Hover effect for validation messages */
            .validation-message .invalid-feedback:hover,
            .validation-message .valid-feedback:hover {
                transform: translateY(-1px);
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
                transition: all 0.3s ease;
            }

            /* Required field indicator */
            .required-field::after {
                content: "*";
                color: #e74c3c;
                margin-left: 4px;
            }

            /* Responsive adjustments */
            @media (max-width: 768px) {
                .validation-message {
                    font-size: 0.8rem;
                }
                
                .validation-message .invalid-feedback,
                .validation-message .valid-feedback {
                    padding: 0.5rem 0.75rem;
                }
            }

            /* Focus state styles */
            .form-control:focus {
                box-shadow: 0 0 0 0.2rem rgba(46, 204, 113, 0.25);
                border-color: #2ecc71;
            }

            .form-control.is-invalid:focus {
                box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.25);
                border-color: #e74c3c;
            }
        </style>
    `);

    // Validate all fields on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Get all phone and email input fields
        const phoneFields = document.querySelectorAll('input[pattern^="^[0-9]"]');
        const emailFields = document.querySelectorAll('input[pattern^="[a-zA-Z0-9._%+-]"]');
        
        // Validate phone fields with existing values
        phoneFields.forEach(field => {
            if (field.value.trim() !== '') {
                validatePhone(field);
            }
        });
        
        // Validate email fields with existing values
        emailFields.forEach(field => {
            if (field.value.trim() !== '') {
                validateEmail(field);
            }
        });
    });
</script>
@endsection

@endsection
