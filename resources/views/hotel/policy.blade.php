@extends('layouts.layout')
@section('content')
@include('hotel.tapview', ['hotel' => $hotel])
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .policy-section {
        margin-bottom: 2rem;
        padding: 1.5rem;
        border: 1px solid #eee;
        border-radius: 0.5rem;
        background-color: #fff;
    }
    .policy-section h4 {
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #eee;
    }
    .policy-section:not(:first-child) {
        margin-top: 2rem;
    }
    .nav-pills .nav-link {
        cursor: pointer;
    }
    #subpolicy-tabs {
        margin-bottom: 20px;
    }
    
    /* Readonly styling for unauthorized users */
    .readonly-mode input[readonly],
    .readonly-mode textarea[readonly],
    .readonly-mode select[disabled] {
        background-color: #f8f9fa !important;
        opacity: 0.7;
        cursor: not-allowed;
        color: #6c757d;
    }
    
    .readonly-mode .btn[disabled] {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .readonly-mode .summernote .note-editing-area {
        background-color: #f8f9fa;
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Hotel Standard Policies
                <a href="javascript:history.back()" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            
            <!-- Sub-policy Navigation Tabs -->
            {{-- <div class="px-4 pt-3">
                <ul class="nav nav-pills nav-fill" id="subpolicy-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" id="property-tab" data-bs-toggle="pill" data-bs-target="#property-policy" type="button">Property Policy</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="cancellation-tab" data-bs-toggle="pill" data-bs-target="#cancellation-policy" type="button">Cancellation Policy</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="refund-tab" data-bs-toggle="pill" data-bs-target="#refund-policy" type="button">Refund Policy</a>
                    </li>
                </ul>
            </div> --}}

            <!-- Sub-policy Navigation Tabs -->
            <div class="px-4 pt-3">
                <ul class="nav nav-pills nav-fill" id="subpolicy-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" id="property-tab" data-bs-toggle="pill" data-bs-target="#property-policy" type="button">Property Policy</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="cancellation-tab" data-bs-toggle="pill" data-bs-target="#cancellation-policy" type="button">Cancellation Policy</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="refund-tab" data-bs-toggle="pill" data-bs-target="#refund-policy" type="button">Refund Policy</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="child-tab" data-bs-toggle="pill" data-bs-target="#child-policy" type="button">Child Policy</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="pet-tab" data-bs-toggle="pill" data-bs-target="#pet-policy" type="button">Pet Policy</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="terms-tab" data-bs-toggle="pill" data-bs-target="#terms-policy" type="button">General T&C</a>
                    </li>
                </ul>
            </div>
            
            <!-- Policy Content Sections -->
            <div class="tab-content">
                <!-- 1. Property Policy Section -->
                <div class="tab-pane fade show active" id="property-policy">
                    <div class="policy-section">
                        <h4 class="text-primary">Property Policy</h4>
                        <form action="{{ route('update.policy') }}" method="POST" enctype="multipart/form-data">
                            @csrf 
                            <input type="hidden" id="hotel_id" name="hotel_id" value="{{ $hotel->hotel_unique_id }}">
                            
                            <div class="row">
                                <!-- Name -->
                                <div class="col-md-4 mb-3">
                                    <label for="name" class="form-label"><strong>Name</strong><span style="color: red;">*</span></label>
                                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $hotelPolicy->name ?? '') }}" placeholder="Enter Name" required
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                    @error('name')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Extras -->
                                <div class="mb-3 col-md-4">
                                    <label for="extras" class="form-label"><strong>Extras</strong><span style="color: red; font-weight: bold;">*</span></label>
                                    <textarea class="form-control" id="extras" name="extras" rows="4" placeholder="Enter Extras"
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>{{ old('extras', $hotelPolicy->extras ?? '') }}</textarea>
                                </div>

                                <!-- Property -->
                                <div class="mb-3 col-md-4">
                                    <label for="property" class="form-label"><strong>Detailed T&C</strong><span style="color: red; font-weight: bold;">*</span></label>
                                    <textarea class="form-control" id="property" name="property" rows="4" placeholder="Enter Property"
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>{{ old('property', $hotelPolicy->property ?? '') }}</textarea>
                                </div>

                                <!-- Policy -->
                                <div class="col-md-12 mb-3">
                                    <label for="policy" class="form-label"><strong>Hotel Policy</strong><span style="color: red;">*</span></label>
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20)
                                        <div class="alert alert-info">
                                            <i class="fas fa-eye"></i> <strong>View Only Mode:</strong> Policy editing is restricted for your role.
                                        </div>
                                    @endif
                                    <textarea id="property-summernote" name="policy" class="summernote form-control" rows="10"
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>{{ old('policy', $hotelPolicy->policy ?? '') }}</textarea>
                                    @error('policy')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- File Upload -->
                                <div class="col-md-4 mb-3">
                                    <label for="file" class="form-label"><strong>File</strong></label>
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20)
                                        <div class="form-control" style="padding: 20px; border: 2px dashed #ccc; text-align: center; background-color: #f8f9fa; color: #6c757d; cursor: not-allowed;">
                                            <i class="fas fa-lock"></i> File upload restricted for your role
                                        </div>
                                    @else
                                        <input type="file" name="file" class="form-control" accept="application/pdf">
                                    @endif
                                    @error('file')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                
                                    @if (!empty($hotelPolicy->file))
                                        <div class="mt-2">
                                            <a href="{{ $hotelPolicy->file }}" target="_blank">View Existing File</a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-flex gap-3">
                                @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
                                    <button type="submit" class="btn btn-primary px-4">Save Property Policy</button>
                                @else
                                    <button type="button" class="btn btn-secondary px-4" disabled>
                                        <i class="fas fa-lock"></i> Save Restricted
                                    </button>
                                    <small class="text-muted mt-2">
                                        <i class="fas fa-info-circle"></i> You don't have permission to save policy data.
                                    </small>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- 2. Cancellation Policy Section -->
                <div class="tab-pane fade" id="cancellation-policy">
                    <div class="policy-section">
                        <h4 class="text-primary">Cancellation Policy</h4>
                        <form action="{{ route('updatecancellation.policy') }}" method="POST" enctype="multipart/form-data">
                            @csrf 
                            <input type="hidden" id="hotel_id" name="hotel_id" value="{{ $hotel->hotel_unique_id }}">
                            
                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label for="cancellation_type" class="form-label">
                                        <strong>Cancellation Type</strong>
                                        <span style="color: red; font-weight: bold;">*</span>
                                    </label>
                                    <select name="cancellation_type" id="cancellation_type" class="form-control" required
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                        <option value="">Select an option</option>
                                        <option value="0" {{ old('cancellation_type', $hotel->cancellation_type) == 0 ? 'selected' : '' }}>Free</option>
                                        <option value="1" {{ old('cancellation_type', $hotel->cancellation_type) == 1 ? 'selected' : '' }}>Chargeable</option>
                                    </select>
                                </div>
                                
                                <div id="cancellation-options" style="{{ old('cancellation_type', $hotel->cancellation_type) == 1 ? '' : 'display: none;' }}">
                                    <div id="cancellation-fields">
                                        @foreach($cancellation_data as $index => $rule)
                                            <div class="row mb-3 cancellation-rule" id="cancellation-rule-{{ $index }}">
                                                <div class="col-md-4">
                                                    <label class="form-label"><strong>Duration</strong></label>
                                                    <input 
                                                        type="text" 
                                                        class="form-control" 
                                                        name="cancellation_duration[]" 
                                                        placeholder="Enter Duration" 
                                                        value="{{ old('cancellation_duration.' . $index, $rule['duration'] ?? '') }}"
                                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                                </div>

                                                <div class="col-md-4">
                                                    <label class="form-label"><strong>Type</strong></label>
                                                    <select class="form-select" name="type[]"
                                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                                        <option value="">Select Type</option>
                                                        <option value="flat" {{ (old('type.' . $index, $rule['type'] ?? '') == 'flat') ? 'selected' : '' }}>Flat</option>
                                                        <option value="percentage" {{ (old('type.' . $index, $rule['type'] ?? '') == 'percentage') ? 'selected' : '' }}>Percentage</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-4">
                                                    <label class="form-label"><strong>Price</strong></label>
                                                    <input 
                                                        type="number" 
                                                        class="form-control" 
                                                        name="cancellation_price[]" 
                                                        placeholder="Enter Price" 
                                                        value="{{ old('cancellation_price.' . $index, $rule['price'] ?? '') }}"
                                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>
                                                </div>
                                                <div class="col-md-2" style="margin-top: 1.5rem">
                                                    <button type="button" class="btn btn-danger" onclick="removeCancellationField({{ $index }})"
                                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>Delete</button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mb-3">
                                        @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
                                            <button type="button" id="add-cancellation-field" class="btn btn-primary">Add More</button>
                                        @else
                                            <button type="button" class="btn btn-secondary" disabled>
                                                <i class="fas fa-lock"></i> Add More Restricted
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- File Upload -->
                                <div class="mb-3 col-md-4">
                                    <label for="file" class="form-label"><strong>File</strong></label>
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20)
                                        <div class="form-control" style="padding: 20px; border: 2px dashed #ccc; text-align: center; background-color: #f8f9fa; color: #6c757d; cursor: not-allowed;">
                                            <i class="fas fa-lock"></i> File upload restricted for your role
                                        </div>
                                    @else
                                        <input type="file" name="file" class="form-control" accept="application/pdf">
                                    @endif
                                    @error('file')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror

                                    @if (!empty($hotel->cancellation_pdf))
                                        <div class="mt-2">
                                            <a href="{{ $hotel->cancellation_pdf }}" target="_blank">View Existing File</a>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="policy" class="form-label"><strong>Cancellation Policy</strong><span style="color: red;">*</span></label>
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20)
                                        <div class="alert alert-info">
                                            <i class="fas fa-eye"></i> <strong>View Only Mode:</strong> Cancellation policy editing is restricted for your role.
                                        </div>
                                    @endif
                                    <textarea id="cancellation-summernote" name="policy" class="summernote form-control"
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>{{ old('policy', $hotel->policy) }}</textarea>
                                    @error('policy')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
                                    <button type="submit" class="btn btn-primary px-4">Save Cancellation Policy</button>
                                @else
                                    <button type="button" class="btn btn-secondary px-4" disabled>
                                        <i class="fas fa-lock"></i> Save Restricted
                                    </button>
                                    <small class="text-muted mt-2">
                                        <i class="fas fa-info-circle"></i> You don't have permission to save policy data.
                                    </small>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- 3. Refund Policy Section -->
                <div class="tab-pane fade" id="refund-policy">
                    <div class="policy-section">
                        <h4 class="text-primary">Refund Policy</h4>
                        <form action="{{ route('updaterefund.policy') }}" method="POST" enctype="multipart/form-data">
                            @csrf 
                            <input type="hidden" id="hotel_id" name="hotel_id" value="{{ $hotel->hotel_unique_id }}">
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="refundpolicy" class="form-label"><strong>Refund Policy</strong><span style="color: red;">*</span></label>
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20)
                                        <div class="alert alert-info">
                                            <i class="fas fa-eye"></i> <strong>View Only Mode:</strong> Refund policy editing is restricted for your role.
                                        </div>
                                    @endif
                                    <textarea id="refund-summernote" name="refundpolicy" class="summernote form-control"
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>{{ old('refundpolicy', $hotel->refundpolicy) }}</textarea>
                                    @error('refundpolicy')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- File Upload -->
                                <div class="col-md-4 mb-3">
                                    <label for="file" class="form-label"><strong>File</strong></label>
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20)
                                        <div class="form-control" style="padding: 20px; border: 2px dashed #ccc; text-align: center; background-color: #f8f9fa; color: #6c757d; cursor: not-allowed;">
                                            <i class="fas fa-lock"></i> File upload restricted for your role
                                        </div>
                                    @else
                                        <input type="file" name="file" class="form-control" accept="application/pdf">
                                    @endif
                                    @error('file')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror

                                    @if (!empty($hotel->refundpolicy_pdf))
                                        <div class="mt-2">
                                            <a href="{{ $hotel->refundpolicy_pdf }}" target="_blank">View Existing File</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
                                    <button type="submit" class="btn btn-primary px-4">Save Refund Policy</button>
                                @else
                                    <button type="button" class="btn btn-secondary px-4" disabled>
                                        <i class="fas fa-lock"></i> Save Restricted
                                    </button>
                                    <small class="text-muted mt-2">
                                        <i class="fas fa-info-circle"></i> You don't have permission to save policy data.
                                    </small>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <!-- 4. Child Policy Section -->
                <div class="tab-pane fade" id="child-policy">
                    <div class="policy-section">
                        <h4 class="text-primary">Child Policy</h4>
                        <form action="{{ route('updatechild.policy') }}" method="POST" enctype="multipart/form-data">
                            @csrf 
                            <input type="hidden" id="hotel_id" name="hotel_id" value="{{ $hotel->hotel_unique_id }}">
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="childpolicy" class="form-label"><strong>Child Policy</strong><span style="color: red;">*</span></label>
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20)
                                        <div class="alert alert-info">
                                            <i class="fas fa-eye"></i> <strong>View Only Mode:</strong> Child policy editing is restricted for your role.
                                        </div>
                                    @endif
                                    <textarea id="child-summernote" name="childpolicy" class="summernote form-control"
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>{{ old('childpolicy', $hotel->childpolicy ?? '') }}</textarea>
                                    @error('childpolicy')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- File Upload -->
                                <div class="col-md-4 mb-3">
                                    <label for="file" class="form-label"><strong>File</strong></label>
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20)
                                        <div class="form-control" style="padding: 20px; border: 2px dashed #ccc; text-align: center; background-color: #f8f9fa; color: #6c757d; cursor: not-allowed;">
                                            <i class="fas fa-lock"></i> File upload restricted for your role
                                        </div>
                                    @else
                                        <input type="file" name="file" class="form-control" accept="application/pdf">
                                    @endif
                                    @error('file')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror

                                    @if (!empty($hotel->childpolicy_pdf))
                                        <div class="mt-2">
                                            <a href="{{ $hotel->childpolicy_pdf }}" target="_blank">View Existing File</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
                                    <button type="submit" class="btn btn-primary px-4">Save Child Policy</button>
                                @else
                                    <button type="button" class="btn btn-secondary px-4" disabled>
                                        <i class="fas fa-lock"></i> Save Restricted
                                    </button>
                                    <small class="text-muted mt-2">
                                        <i class="fas fa-info-circle"></i> You don't have permission to save policy data.
                                    </small>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <!-- 5. Pet Policy Section -->
                <div class="tab-pane fade" id="pet-policy">
                    <div class="policy-section">
                        <h4 class="text-primary">Pet Policy</h4>
                        <form action="{{ route('updatepet.policy') }}" method="POST" enctype="multipart/form-data">
                            @csrf 
                            <input type="hidden" id="hotel_id" name="hotel_id" value="{{ $hotel->hotel_unique_id }}">
                            
                            <div class="row">
                                <div class="mb-3 col-md-4">
                                    <label for="pet_allowed" class="form-label">
                                        <strong>Pets Allowed</strong>
                                        <span style="color: red; font-weight: bold;">*</span>
                                    </label>
                                    <select name="pet_allowed" id="pet_allowed" class="form-control" required
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                                        <option value="">Select an option</option>
                                        <option value="1" {{ old('pet_allowed', $hotel->pet_allowed) == 1 ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ old('pet_allowed', $hotel->pet_allowed) == 0 ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                                
                                <div id="pet-options" style="{{ old('pet_allowed', $hotel->pet_allowed) == 1 ? '' : 'display: none;' }}">
                                    <div class="col-md-12 mb-3">
                                        <label for="petpolicy" class="form-label"><strong>Pet Policy Details</strong><span style="color: red;">*</span></label>
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20)
                                            <div class="alert alert-info">
                                                <i class="fas fa-eye"></i> <strong>View Only Mode:</strong> Pet policy editing is restricted for your role.
                                            </div>
                                        @endif
                                        <textarea id="pet-summernote" name="petpolicy" class="summernote form-control"
                                            @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>{{ old('petpolicy', $hotel->petpolicy ?? '') }}</textarea>
                                        @error('petpolicy')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- File Upload -->
                                <div class="col-md-4 mb-3">
                                    <label for="file" class="form-label"><strong>File</strong></label>
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20)
                                        <div class="form-control" style="padding: 20px; border: 2px dashed #ccc; text-align: center; background-color: #f8f9fa; color: #6c757d; cursor: not-allowed;">
                                            <i class="fas fa-lock"></i> File upload restricted for your role
                                        </div>
                                    @else
                                        <input type="file" name="file" class="form-control" accept="application/pdf">
                                    @endif
                                    @error('file')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror

                                    @if (!empty($hotel->petpolicy_pdf))
                                        <div class="mt-2">
                                            <a href="{{ $hotel->petpolicy_pdf }}" target="_blank">View Existing File</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
                                    <button type="submit" class="btn btn-primary px-4">Save Pet Policy</button>
                                @else
                                    <button type="button" class="btn btn-secondary px-4" disabled>
                                        <i class="fas fa-lock"></i> Save Restricted
                                    </button>
                                    <small class="text-muted mt-2">
                                        <i class="fas fa-info-circle"></i> You don't have permission to save policy data.
                                    </small>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <!-- 6. General Terms & Conditions Section -->
                <div class="tab-pane fade" id="terms-policy">
                    <div class="policy-section">
                        <h4 class="text-primary">General Terms & Conditions</h4>
                        <form action="{{ route('updateterms.policy') }}" method="POST" enctype="multipart/form-data">
                            @csrf 
                            <input type="hidden" id="hotel_id" name="hotel_id" value="{{ $hotel->hotel_unique_id }}">
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="termspolicy" class="form-label"><strong>General Terms & Conditions</strong><span style="color: red;">*</span></label>
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20)
                                        <div class="alert alert-info">
                                            <i class="fas fa-eye"></i> <strong>View Only Mode:</strong> Terms & conditions editing is restricted for your role.
                                        </div>
                                    @endif
                                    <textarea id="terms-summernote" name="termspolicy" class="summernote form-control"
                                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) readonly @endif>{{ old('termspolicy', $hotel->termspolicy ?? '') }}</textarea>
                                    @error('termspolicy')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- File Upload -->
                                <div class="col-md-4 mb-3">
                                    <label for="file" class="form-label"><strong>File</strong></label>
                                    @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20)
                                        <div class="form-control" style="padding: 20px; border: 2px dashed #ccc; text-align: center; background-color: #f8f9fa; color: #6c757d; cursor: not-allowed;">
                                            <i class="fas fa-lock"></i> File upload restricted for your role
                                        </div>
                                    @else
                                        <input type="file" name="file" class="form-control" accept="application/pdf">
                                    @endif
                                    @error('file')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror

                                    @if (!empty($hotel->termspolicy_pdf))
                                        <div class="mt-2">
                                            <a href="{{ $hotel->termspolicy_pdf }}" target="_blank">View Existing File</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
                                    <button type="submit" class="btn btn-primary px-4">Save Terms & Conditions</button>
                                @else
                                    <button type="button" class="btn btn-secondary px-4" disabled>
                                        <i class="fas fa-lock"></i> Save Restricted
                                    </button>
                                    <small class="text-muted mt-2">
                                        <i class="fas fa-info-circle"></i> You don't have permission to save policy data.
                                    </small>
                                @endif
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
<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    $(document).ready(function() {
        // Check user role for protection
        var isUnauthorized = {{ Auth::user()->role_id != 1 && Auth::user()->role_id != 20 ? 'true' : 'false' }};
        
        // Initialize summernote editors
        $('.summernote').each(function() {
            var config = {
                height: 200,
                placeholder: "Enter policy details...",
            };
            
            // Disable summernote for unauthorized users
            if (isUnauthorized) {
                config.toolbar = false;
                config.callbacks = {
                    onInit: function() {
                        $(this).summernote('disable');
                    }
                };
            }
            
            $(this).summernote(config);
        });
        
        // Initialize flatpickr for time inputs
        const timePickerConfig = {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minuteIncrement: 15
        };
        
        // Apply to all time pickers
        document.querySelectorAll('.time-picker').forEach(function(input) {
            flatpickr(input, timePickerConfig);
        });
        
        // Handle tab switching with history
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        
        if (tabParam) {
            $(`#${tabParam}-tab`).tab('show');
        }
        
        // Update URL when tabs change
        $('#subpolicy-tabs a').on('shown.bs.tab', function (e) {
            const id = $(e.target).attr('id').replace('-tab', '');
            const url = new URL(window.location);
            url.searchParams.set('tab', id);
            window.history.replaceState({}, '', url);
        });

        // Add protection for unauthorized users
        if (isUnauthorized) {
            // Add readonly-mode class for styling
            $('body').addClass('readonly-mode');
            
            // Prevent form submissions
            $('form').on('submit', function(e) {
                e.preventDefault();
                alert('You do not have permission to save policy data. Contact your administrator.');
                return false;
            });
            
            // Add visual styling for readonly mode
            $('input[readonly], textarea[readonly], select[disabled]').css({
                'background-color': '#f8f9fa',
                'opacity': '0.7',
                'cursor': 'not-allowed'
            });
            
            // Disable dynamic cancellation functions
            window.removeCancellationField = function() {
                alert('You do not have permission to modify cancellation rules. Contact your administrator.');
                return false;
            };
            
            // Prevent time picker interactions
            $('.time-picker[readonly]').on('focus click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            });
            
            // Disable select dropdowns change events
            $('select[disabled]').on('change', function(e) {
                e.preventDefault();
                return false;
            });
        }
    });
    
    // Cancellation policy functions
    document.addEventListener('DOMContentLoaded', function () {
        const cancellationType = document.getElementById('cancellation_type');
        const cancellationOptions = document.getElementById('cancellation-options');
        const fieldsContainer = document.getElementById('cancellation-fields');
        let index = fieldsContainer ? fieldsContainer.querySelectorAll('.cancellation-rule').length : 0;
        
        if (cancellationType) {
            cancellationType.addEventListener('change', function () {
                cancellationOptions.style.display = this.value == '1' ? '' : 'none';
            });
        }
        
        const addButton = document.getElementById('add-cancellation-field');
        if (addButton) {
            addButton.addEventListener('click', function () {
                // Check user authorization
                var isUnauthorized = {{ Auth::user()->role_id != 1 && Auth::user()->role_id != 20 ? 'true' : 'false' }};
                if (isUnauthorized) {
                    alert('You do not have permission to add cancellation rules. Contact your administrator.');
                    return false;
                }
                
                const readonlyAttr = isUnauthorized ? 'readonly' : '';
                const disabledAttr = isUnauthorized ? 'disabled' : '';
                
                const fieldHTML = `
                    <div class="row mb-3 cancellation-rule" id="cancellation-rule-${index}">
                        <div class="col-md-4">
                            <label class="form-label"><strong>Duration</strong></label>
                            <input type="text" class="form-control" name="cancellation_duration[]" placeholder="Enter Duration" ${readonlyAttr}>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><strong>Type</strong></label>
                            <select class="form-select" name="type[]" ${disabledAttr}>
                                <option value="">Select Type</option>
                                <option value="flat">Flat</option>
                                <option value="percentage">Percentage</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><strong>Price</strong></label>
                            <input type="number" class="form-control" name="cancellation_price[]" placeholder="Enter Price" ${readonlyAttr}>
                        </div>
                        <div class="col-md-2" style="margin-top: 1.5rem">
                            <button type="button" class="btn btn-danger" onclick="removeCancellationField(${index})" ${disabledAttr}>Delete</button>
                        </div>
                    </div>`;
                fieldsContainer.insertAdjacentHTML('beforeend', fieldHTML);
                index++; // Increment the index for the next field
            });
        }
    });

    // Function to remove a cancellation field dynamically
    function removeCancellationField(index) {
        const field = document.getElementById('cancellation-rule-' + index);
        if (field) {
            field.remove(); // Remove the specific cancellation field
        }
    }
</script>
@endsection