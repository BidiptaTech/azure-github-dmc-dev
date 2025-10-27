@extends('layouts.layout')

@section('title', 'Import Agencies')

@section('content')
<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Modern Header Section -->
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card gradient-header border-0">
                    <div class="card-header border-0 pb-0">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div class="header-content">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="icon-wrapper me-3">
                                        <i class="ri-building-line"></i>
                                    </div>
                                    <div>
                                        <h4 class="card-title mb-1 text-white">Import Agencies</h4>
                                        <h6 class="text-white-50 mb-0">Bulk Upload Multiple Agencies</h6>
                                    </div>
                                </div>
                                <p class="text-white-75 mb-0">
                                    <i class="ri-information-line me-1"></i>Quickly add multiple agencies to your system by uploading a CSV file
                                </p>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('agencies.import.template') }}" 
                                   class="btn btn-light btn-sm shadow-sm">
                                    <i class="ri-download-cloud-2-line me-1"></i>Download Template
                                </a>
                                <a href="{{ route('agencies.index') }}" 
                                   class="btn btn-outline-light btn-sm">
                                    <i class="ri-arrow-left-line me-1"></i>Back to Agencies
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Section -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card modern-card border-0">
                    <div class="card-body p-4">
                        <!-- Success/Error Messages -->
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show modern-alert" role="alert">
                                <div class="d-flex align-items-start">
                                    <div class="alert-icon me-3">
                                        <i class="ri-checkbox-circle-line"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="alert-heading mb-2">
                                            <i class="ri-check-double-line me-2"></i>Upload Successful!
                                        </h5>
                                        <div class="alert-message">
                                            {!! session('success') !!}
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                        @endif

                        @if(session('warning'))
                            <div class="alert alert-warning alert-dismissible fade show modern-alert" role="alert">
                                <div class="d-flex align-items-start">
                                    <div class="alert-icon me-3">
                                        <i class="ri-alert-line"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="alert-heading mb-2">
                                            <i class="ri-error-warning-line me-2"></i>Partial Success
                                        </h5>
                                        <div class="alert-message">
                                            {!! session('warning') !!}
                                        </div>
                                        <div class="mt-3">
                                            <small class="text-muted">
                                                <i class="ri-information-line me-1"></i>
                                                Please review the errors above and correct the data in your CSV file for the failed rows.
                                            </small>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show modern-alert" role="alert">
                                <div class="d-flex align-items-start">
                                    <div class="flex-grow-1">
                                        <h5 class="alert-heading mb-2">
                                            <i class="ri-close-circle-line me-2"></i>Upload Failed
                                        </h5>
                                        <div class="alert-message">
                                            {!! session('error') !!}
                                        </div>
                                        <div class="mt-3">
                                            <small class="text-muted">
                                                <i class="ri-information-line me-1"></i>
                                                Please check your CSV file format and ensure all required fields are filled correctly.
                                            </small>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show modern-alert" role="alert">
                                <div class="d-flex align-items-start">
                                    <div class="alert-icon me-3">
                                        <i class="ri-error-warning-line"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="alert-heading mb-2">
                                            <i class="ri-close-circle-line me-2"></i>Validation Errors
                                        </h5>
                                        <ul class="mb-0 error-list">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Upload Instructions -->
                        <div class="instruction-box mb-4">
                            <div class="instruction-header">
                                <i class="ri-information-line me-2"></i>
                                <strong>Upload Instructions</strong>
                            </div>
                            <div class="instruction-content">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="instruction-section">
                                            <h6 class="instruction-section-title">
                                                <i class="ri-file-download-line me-2"></i>Getting Started
                                            </h6>
                                            <div class="instruction-item">
                                                <i class="ri-download-2-line text-success"></i>
                                                <span>Download the CSV template using the button above</span>
                                            </div>
                                            <div class="instruction-item">
                                                <i class="ri-edit-line text-primary"></i>
                                                <span>Fill in your agency data (required fields marked with *)</span>
                                            </div>
                                            <div class="instruction-item">
                                                <i class="ri-file-text-line text-warning"></i>
                                                <span>Maximum file size: 10MB • Formats: CSV, TXT</span>
                                            </div>
                                            <div class="instruction-item">
                                                <i class="ri-database-line text-info"></i>
                                                <span>Maximum 500 rows per upload recommended</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="instruction-section">
                                            <h6 class="instruction-section-title">
                                                <i class="ri-settings-3-line me-2"></i>Field Requirements
                                            </h6>
                                            <div class="instruction-item">
                                                <i class="ri-checkbox-circle-line text-danger"></i>
                                                <span><strong>Required:</strong> Agency Name, Email, Country, City</span>
                                            </div>
                                            <div class="instruction-item">
                                                <i class="ri-mail-line text-primary"></i>
                                                <span>Email must be unique and in valid format</span>
                                            </div>
                                            <div class="instruction-item">
                                                <i class="ri-shield-check-line text-success"></i>
                                                <span><strong>Auto-populated:</strong> ID Card Type (based on country)</span>
                                            </div>
                                            <div class="instruction-item">
                                                <i class="ri-alert-line text-warning"></i>
                                                <span>Duplicate emails will be skipped automatically</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Field Details Table -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="instruction-section">
                                            <h6 class="instruction-section-title">
                                                <i class="ri-table-line me-2"></i>CSV Column Details
                                            </h6>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-hover mb-0 modern-table">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th width="20%">Column Name</th>
                                                            <th width="15%">Required</th>
                                                            <th width="35%">Description</th>
                                                            <th width="30%">Example</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td><code class="field-code">agency_name</code></td>
                                                            <td><span class="badge bg-danger">Required</span></td>
                                                            <td>Full name of the agency</td>
                                                            <td><span class="text-muted">Sunrise Travel Agency</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><code class="field-code">email</code></td>
                                                            <td><span class="badge bg-danger">Required</span></td>
                                                            <td>Unique email address</td>
                                                            <td><span class="text-muted">info@sunrisetravel.com</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><code class="field-code">phone</code></td>
                                                            <td><span class="badge bg-secondary">Optional</span></td>
                                                            <td>Contact phone number</td>
                                                            <td><span class="text-muted">+1-555-123-4567</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><code class="field-code">country</code></td>
                                                            <td><span class="badge bg-danger">Required</span></td>
                                                            <td>Country name (ID card type will be auto-populated)</td>
                                                            <td><span class="text-muted">United States</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><code class="field-code">city</code></td>
                                                            <td><span class="badge bg-danger">Required</span></td>
                                                            <td>City name</td>
                                                            <td><span class="text-muted">New York</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><code class="field-code">address</code></td>
                                                            <td><span class="badge bg-secondary">Optional</span></td>
                                                            <td>Full street address</td>
                                                            <td><span class="text-muted">123 Main Street, Suite 100</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><code class="field-code">postal_code</code></td>
                                                            <td><span class="badge bg-secondary">Optional</span></td>
                                                            <td>Postal or ZIP code</td>
                                                            <td><span class="text-muted">10001</span></td>
                                                        </tr>
                                                        <tr>
                                                            <td><code class="field-code">contact_person</code></td>
                                                            <td><span class="badge bg-secondary">Optional</span></td>
                                                            <td>Primary contact person name</td>
                                                            <td><span class="text-muted">John Smith</span></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="alert alert-info border-0 mb-0">
                                            <div class="d-flex align-items-start">
                                                <i class="ri-lightbulb-line me-2 mt-1"></i>
                                                <div>
                                                    <strong>Pro Tips:</strong>
                                                    <span class="ms-2">Ensure email addresses are unique. Country and city names must match existing records. Use proper email format (user@domain.com). ID Card Type is automatically set based on country. Card numbers are NOT imported for security reasons. Soft-deleted agencies with same email will be restored and updated.</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Form -->
                        <div class="upload-section">
                            <form action="{{ route('agencies.import.upload') }}" 
                                  method="POST" 
                                  enctype="multipart/form-data" 
                                  class="upload-form"
                                  id="importForm">
                                @csrf
                                <div class="row align-items-end g-3">
                                    <div class="col-md-8">
                                        <label for="file" class="form-label fw-bold">
                                            <i class="ri-file-upload-line me-1"></i>Select CSV File
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="file-input-wrapper">
                                            <input type="file" 
                                                   class="form-control modern-input @error('file') is-invalid @enderror" 
                                                   id="file" 
                                                   name="file" 
                                                   accept=".csv,.txt" 
                                                   required>
                                            <div class="file-input-icon">
                                                <i class="ri-attachment-line"></i>
                                            </div>
                                        </div>
                                        @error('file')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div id="filePreview" class="alert alert-success d-none mt-2">
                                            <h6 class="alert-heading mb-2">
                                                <i class="ri-file-text-line me-1"></i>File Selected:
                                            </h6>
                                            <p class="mb-1"><strong>Name:</strong> <span id="fileName"></span></p>
                                            <p class="mb-0"><strong>Size:</strong> <span id="fileSize"></span></p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary w-100 upload-btn" id="submitBtn">
                                            <i class="ri-upload-cloud-2-line me-2"></i>
                                            <span class="btn-text">Upload Agencies</span>
                                            <span class="btn-loader d-none">
                                                <span class="spinner-border spinner-border-sm me-2"></span>
                                                Uploading...
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Display Errors -->
                        @if($errors->any())
                            <div class="error-section mt-4">
                                <div class="alert alert-warning modern-alert">
                                    <div class="alert-header">
                                        <i class="ri-error-warning-line me-2"></i>
                                        <strong>Upload Errors Detected</strong>
                                    </div>
                                    <div class="error-list">
                                        @foreach($errors->all() as $error)
                                            <div class="error-item">
                                                <i class="ri-close-circle-line text-danger me-2"></i>
                                                {{ $error }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload History Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-gradient-primary text-white border-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0 text-white">
                                <i class="ri-history-line me-2"></i>Recent Upload History
                                <small class="d-block text-white-50 fs-6 mt-1">Agencies Import</small>
                            </h5>
                            <span class="badge bg-light bg-opacity-20 text-white">
                                {{ isset($uploadHistory) && $uploadHistory ? $uploadHistory->count() : 0 }} uploads
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if(isset($uploadHistory) && $uploadHistory && $uploadHistory->count() > 0)
                            <!-- Desktop View -->
                            <div class="table-responsive d-none d-lg-block">
                                <table class="table table-hover mb-0 modern-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="border-0 py-3">
                                                <i class="ri-calendar-line text-primary me-1"></i>Upload Time
                                            </th>
                                            <th class="border-0 py-3">
                                                <i class="ri-file-text-line text-primary me-1"></i>File Details
                                            </th>
                                            <th class="border-0 py-3">
                                                <i class="ri-database-line text-primary me-1"></i>Records
                                            </th>
                                            <th class="border-0 py-3">
                                                <i class="ri-checkbox-circle-line text-primary me-1"></i>Results
                                            </th>
                                            <th class="border-0 py-3">
                                                <i class="ri-shield-check-line text-primary me-1"></i>Status
                                            </th>
                                            <th class="border-0 py-3 text-center">
                                                <i class="ri-error-warning-line text-primary me-1"></i>Details
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($uploadHistory as $history)
                                            <tr class="upload-row">
                                                <td class="py-3">
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-medium text-dark">{{ \Carbon\Carbon::parse($history->created_at)->format('M d, Y') }}</span>
                                                        <small class="text-muted">{{ \Carbon\Carbon::parse($history->created_at)->format('h:i A') }}</small>
                                                        <small class="text-muted">{{ \Carbon\Carbon::parse($history->created_at)->diffForHumans() }}</small>
                                                    </div>
                                                </td>
                                                <td class="py-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="file-icon me-3">
                                                            <i class="ri-file-excel-2-line text-success fs-4"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-medium text-dark">{{ $history->original_file_name ?? 'agencies_import.csv' }}</div>
                                                            <small class="text-muted">CSV File</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3">
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2">
                                                            <i class="ri-database-2-line me-1"></i>{{ $history->total_records ?? 0 }} rows
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="py-3">
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                                            <i class="ri-check-line me-1"></i>{{ $history->success_count ?? 0 }} success
                                                        </span>
                                                        @if(($history->error_count ?? 0) > 0)
                                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">
                                                                <i class="ri-close-line me-1"></i>{{ $history->error_count }} failed
                                                            </span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="py-3">
                                                    @if($history->status === 'success')
                                                        <span class="badge bg-success px-3 py-2">
                                                            <i class="ri-check-double-line me-1"></i>Completed
                                                        </span>
                                                    @elseif($history->status === 'partial')
                                                        <span class="badge bg-warning px-3 py-2">
                                                            <i class="ri-alert-line me-1"></i>Partial Success
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger px-3 py-2">
                                                            <i class="ri-close-circle-line me-1"></i>Failed
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="py-3 text-center">
                                                    @if(($history->error_count ?? 0) > 0 && !empty($history->errors))
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-danger view-errors-btn" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#errorModal{{ $history->id }}">
                                                            <i class="ri-error-warning-line me-1"></i>View Errors
                                                        </button>
                                                    @else
                                                        <span class="text-muted small">
                                                            <i class="ri-checkbox-circle-line me-1"></i>No errors
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                            
                                            <!-- Error Details Modal -->
                                            @if(($history->error_count ?? 0) > 0 && !empty($history->errors))
                                            <div class="modal fade" id="errorModal{{ $history->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title">
                                                                <i class="ri-error-warning-line me-2"></i>Error Details - {{ $history->original_file_name }}
                                                            </h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="alert alert-info mb-3">
                                                                <strong>Upload Summary:</strong><br>
                                                                <i class="ri-file-text-line me-1"></i> File: {{ $history->original_file_name }}<br>
                                                                <i class="ri-database-line me-1"></i> Total Records: {{ $history->total_records ?? 0 }}<br>
                                                                <i class="ri-check-line me-1"></i> Successful: {{ $history->success_count ?? 0 }}<br>
                                                                <i class="ri-close-line me-1"></i> Failed: {{ $history->error_count ?? 0 }}
                                                            </div>
                                                            
                                                            <h6 class="mb-3 text-danger">
                                                                <i class="ri-alert-line me-2"></i>Error Messages:
                                                            </h6>
                                                            <div class="error-details-list">
                                                                @php
                                                                    $errors = is_array($history->errors) ? $history->errors : json_decode($history->errors, true);
                                                                @endphp
                                                                @if(is_array($errors) && count($errors) > 0)
                                                                    @foreach($errors as $index => $error)
                                                                        <div class="error-detail-item mb-2 p-3 border-start border-danger border-3 bg-light">
                                                                            <div class="d-flex align-items-start">
                                                                                <span class="badge bg-danger me-2">{{ $index + 1 }}</span>
                                                                                <div class="flex-grow-1">
                                                                                    {{ $error }}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                @else
                                                                    <p class="text-muted">No detailed error information available.</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                <i class="ri-close-line me-1"></i>Close
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Mobile/Tablet View -->
                            <div class="d-lg-none">
                                @foreach($uploadHistory as $history)
                                    <div class="upload-card border-bottom px-3 py-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="d-flex align-items-center">
                                                <i class="ri-file-excel-2-line text-success fs-4 me-2"></i>
                                                <div>
                                                    <div class="fw-medium text-dark">{{ $history->original_file_name ?? 'agencies_import.csv' }}</div>
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($history->created_at)->format('M d, Y h:i A') }}</small>
                                                </div>
                                            </div>
                                            @if($history->status === 'success')
                                                <span class="badge bg-success">
                                                    <i class="ri-check-line me-1"></i>Success
                                                </span>
                                            @elseif($history->status === 'partial')
                                                <span class="badge bg-warning">
                                                    <i class="ri-alert-line me-1"></i>Partial
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="ri-close-line me-1"></i>Failed
                                                </span>
                                            @endif
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="d-flex gap-2 flex-wrap">
                                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                                    {{ $history->total_records ?? 0 }} rows
                                                </span>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                                    {{ $history->success_count ?? 0 }} ✓
                                                </span>
                                                @if(($history->error_count ?? 0) > 0)
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                                        {{ $history->error_count }} ✗
                                                    </span>
                                                @endif
                                            </div>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($history->created_at)->diffForHumans() }}</small>
                                        </div>
                                        @if(($history->error_count ?? 0) > 0 && !empty($history->errors))
                                            <div class="mt-2">
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger w-100" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#errorModalMobile{{ $history->id }}">
                                                    <i class="ri-error-warning-line me-1"></i>View Error Details
                                                </button>
                                            </div>
                                            
                                            <!-- Mobile Error Details Modal -->
                                            <div class="modal fade" id="errorModalMobile{{ $history->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-fullscreen-sm-down modal-dialog-scrollable">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h6 class="modal-title">
                                                                <i class="ri-error-warning-line me-2"></i>Error Details
                                                            </h6>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="alert alert-info mb-3">
                                                                <strong>Upload Summary:</strong><br>
                                                                <small>
                                                                    <i class="ri-file-text-line me-1"></i> {{ $history->original_file_name }}<br>
                                                                    <i class="ri-database-line me-1"></i> Total: {{ $history->total_records ?? 0 }}<br>
                                                                    <i class="ri-check-line me-1"></i> Success: {{ $history->success_count ?? 0 }}<br>
                                                                    <i class="ri-close-line me-1"></i> Failed: {{ $history->error_count ?? 0 }}
                                                                </small>
                                                            </div>
                                                            
                                                            <h6 class="mb-3 text-danger">
                                                                <i class="ri-alert-line me-2"></i>Errors:
                                                            </h6>
                                                            <div class="error-details-list">
                                                                @php
                                                                    $errors = is_array($history->errors) ? $history->errors : json_decode($history->errors, true);
                                                                @endphp
                                                                @if(is_array($errors) && count($errors) > 0)
                                                                    @foreach($errors as $index => $error)
                                                                        <div class="error-detail-item mb-2 p-2 border-start border-danger border-3 bg-light">
                                                                            <div class="d-flex align-items-start">
                                                                                <span class="badge bg-danger me-2">{{ $index + 1 }}</span>
                                                                                <div class="flex-grow-1">
                                                                                    <small>{{ $error }}</small>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                @else
                                                                    <p class="text-muted small">No error details available.</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                                                                <i class="ri-close-line me-1"></i>Close
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="empty-state">
                                    <div class="mb-3">
                                        <i class="ri-inbox-line text-muted" style="font-size: 4rem;"></i>
                                    </div>
                                    <h6 class="text-muted mb-2">No Upload History</h6>
                                    <p class="text-muted small mb-0">Your agency upload history will appear here once you start importing agencies.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- / Content -->
</div>

<style>
/* Root Variables */
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --info-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    --primary-color: #667eea;
    --success-color: #28a745;
    --info-color: #17a2b8;
    --warning-color: #ffc107;
    --danger-color: #dc3545;
    --light-bg: #f8f9fa;
    --border-color: #e9ecef;
    --text-muted: #6c757d;
    --shadow-sm: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    --shadow-md: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    --shadow-lg: 0 1rem 3rem rgba(0, 0, 0, 0.175);
    --border-radius: 0.75rem;
    --border-radius-sm: 0.5rem;
}

.content-wrapper {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
}

.modern-card {
    border: none;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-sm);
    transition: all 0.3s ease;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
}

.modern-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.gradient-header {
    background: var(--primary-gradient);
    border: none;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-md);
    overflow: hidden;
}

.gradient-header .card-header {
    background: transparent;
    border: none;
    padding: 2rem;
}

.header-content .icon-wrapper {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

/* Text Colors */
.text-white-50 {
    color: rgba(255, 255, 255, 0.7) !important;
}

.text-white-75 {
    color: rgba(255, 255, 255, 0.85) !important;
}

/* Instruction Box */
.instruction-box {
    background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
    border-radius: var(--border-radius-sm);
    padding: 1.5rem;
    border: 1px solid rgba(102, 126, 234, 0.1);
}

.instruction-header {
    color: var(--primary-color);
    font-size: 1.1rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
}

.instruction-section-title {
    color: var(--primary-color);
    font-size: 1rem;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
}

.instruction-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0;
    font-size: 0.9rem;
}

.instruction-item i {
    font-size: 1.1rem;
    width: 20px;
    text-align: center;
}

/* Upload Section */
.upload-section {
    background: linear-gradient(135deg, #fafafa 0%, #f0f0f0 100%);
    border-radius: var(--border-radius-sm);
    padding: 2rem;
    margin: 2rem 0;
    border: 2px dashed rgba(102, 126, 234, 0.3);
    transition: all 0.3s ease;
}

.upload-section:hover {
    border-color: var(--primary-color);
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
}

.file-input-wrapper {
    position: relative;
}

.modern-input {
    border: 2px solid var(--border-color);
    border-radius: var(--border-radius-sm);
    padding: 0.75rem 3rem 0.75rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
}

.modern-input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    transform: translateY(-1px);
}

.file-input-icon {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 1.2rem;
}

.upload-btn {
    background: var(--primary-gradient);
    border: none;
    border-radius: var(--border-radius-sm);
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    box-shadow: var(--shadow-sm);
}

.upload-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.btn .btn-loader {
    display: inline-block;
}

.btn.loading .btn-text {
    display: none;
}

.btn.loading .btn-loader {
    display: inline-block !important;
}

/* Tables */
.modern-table {
    border: none;
}

.modern-table thead th {
    background-color: #f8fafc;
    font-weight: 600;
    color: #475569;
    border-bottom: 2px solid #e2e8f0;
    padding: 1rem 1.25rem;
}

.modern-table tbody tr {
    border: none;
    transition: all 0.2s ease;
}

.modern-table tbody tr:hover {
    background-color: #f8fafc;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.modern-table tbody td {
    padding: 0.75rem 1.25rem;
    vertical-align: middle;
}

.field-code {
    background-color: #f0f4ff;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.875rem;
    color: #667eea;
    font-weight: 600;
}

/* Error Section */
.error-section .modern-alert {
    border-radius: var(--border-radius-sm);
    border: none;
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border-left: 4px solid var(--warning-color);
}

.alert-header {
    display: flex;
    align-items: center;
    font-size: 1.1rem;
    margin-bottom: 1rem;
    color: #856404;
}

.error-item {
    display: flex;
    align-items: flex-start;
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(255, 193, 7, 0.2);
    font-size: 0.9rem;
}

.error-item:last-child {
    border-bottom: none;
}

/* Buttons */
.btn {
    border-radius: var(--border-radius-sm);
    font-weight: 500;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

.btn-light {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.btn-outline-light {
    border: 2px solid rgba(255, 255, 255, 0.7);
    color: white;
}

.btn-outline-light:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: white;
}

/* Modern Alert Boxes */
.modern-alert {
    border: none;
    border-radius: var(--border-radius-sm);
    padding: 1.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
}

.modern-alert.alert-success {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    border-left: 4px solid #10b981;
}

.modern-alert.alert-warning {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-left: 4px solid #f59e0b;
}

.modern-alert.alert-danger {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    border-left: 4px solid #ef4444;
}

.alert-icon {
    font-size: 2rem;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 50%;
}

.modern-alert.alert-success .alert-icon {
    color: #10b981;
}

.modern-alert.alert-warning .alert-icon {
    color: #f59e0b;
}

.modern-alert.alert-danger .alert-icon {
    color: #ef4444;
}

.alert-heading {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
}

.alert-message {
    font-size: 1rem;
    line-height: 1.6;
}

.error-list {
    list-style: none;
    padding-left: 0;
}

.error-list li {
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: start;
}

.error-list li:before {
    content: "•";
    color: #ef4444;
    font-weight: bold;
    font-size: 1.2rem;
    margin-right: 0.5rem;
    line-height: 1.5;
}

.error-list li:last-child {
    border-bottom: none;
}

.error-summary-list {
    background: rgba(255, 255, 255, 0.5);
    padding: 1rem;
    border-radius: 8px;
    border-left: 3px solid #ef4444;
}

.error-summary-item {
    padding: 0.25rem 0;
    font-size: 0.95rem;
    line-height: 1.6;
}

/* Badges */
.badge {
    font-weight: 500;
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
}

.badge.bg-success {
    background-color: #16a34a !important;
    color: white;
}

.badge.bg-warning {
    background-color: #eab308 !important;
    color: white;
}

.badge.bg-danger {
    background-color: #dc2626 !important;
    color: white;
}

.badge.bg-info {
    background-color: #0ea5e9 !important;
    color: white;
}

.badge.bg-primary {
    background-color: #696cff !important;
    color: white;
}

.badge.bg-secondary {
    background-color: #8592a3 !important;
    color: white;
}

.badge.bg-success.bg-opacity-10 {
    background-color: rgba(34, 197, 94, 0.1) !important;
    color: #16a34a !important;
    border: 1px solid rgba(34, 197, 94, 0.2) !important;
}

.badge.bg-danger.bg-opacity-10 {
    background-color: rgba(239, 68, 68, 0.1) !important;
    color: #dc2626 !important;
    border: 1px solid rgba(239, 68, 68, 0.2) !important;
}

.badge.bg-info.bg-opacity-10 {
    background-color: rgba(14, 165, 233, 0.1) !important;
    color: #0ea5e9 !important;
    border: 1px solid rgba(14, 165, 233, 0.2) !important;
}

.badge.bg-light.bg-opacity-20 {
    background-color: rgba(255, 255, 255, 0.2) !important;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #696cff 0%, #5a67d8 100%) !important;
}

.card.border-0.shadow-sm {
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06) !important;
    border-radius: 12px;
    overflow: hidden;
}

.card-header.bg-gradient-primary {
    padding: 1.25rem 1.5rem;
}

/* Upload History Styles */
.upload-row td {
    border: none;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

.upload-card {
    transition: all 0.2s ease;
    background: white;
}

.upload-card:hover {
    background-color: #f8fafc;
}

.upload-card:last-child {
    border-bottom: none !important;
}

.file-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0f9ff;
    border-radius: 12px;
    border: 1px solid #e0f2fe;
}

.empty-state {
    padding: 3rem 2rem;
}

/* View Errors Button */
.view-errors-btn {
    border-radius: 6px;
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
    transition: all 0.3s ease;
}

.view-errors-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
}

/* Error Details Modal */
.error-detail-item {
    border-radius: 6px;
    transition: all 0.2s ease;
}

.error-detail-item:hover {
    background-color: #fff !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.modal-header.bg-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
}

.error-details-list {
    max-height: 500px;
    overflow-y: auto;
}

.error-details-list::-webkit-scrollbar {
    width: 8px;
}

.error-details-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.error-details-list::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.error-details-list::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

#filePreview {
    animation: fadeIn 0.3s ease-in;
}

.upload-row,
.upload-card {
    animation: fadeInUp 0.3s ease-out;
}

/* Responsive Styles */
@media (max-width: 768px) {
    .content-wrapper {
        padding: 1rem 0;
    }
    
    .gradient-header .card-header {
        padding: 1.5rem;
    }
    
    .header-content .icon-wrapper {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }
    
    .upload-section {
        padding: 1.5rem;
        margin: 1rem 0;
    }
    
    .instruction-box {
        padding: 1rem;
    }
    
    .btn {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
    
    .modern-table thead th {
        padding: 0.75rem;
        font-size: 0.875rem;
    }
    
    .upload-row td {
        padding: 0.75rem 0.5rem;
        font-size: 0.875rem;
    }
}

@media (max-width: 576px) {
    .gradient-header .card-header {
        padding: 1rem;
    }
    
    .d-flex.gap-2.flex-wrap {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('file');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const form = document.getElementById('importForm');
    const submitBtn = document.getElementById('submitBtn');

    // File input change event
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        
        if (file) {
            // Validate file type
            const validTypes = ['text/csv', 'application/vnd.ms-excel', 'text/plain'];
            const fileExtension = file.name.split('.').pop().toLowerCase();
            
            if (fileExtension !== 'csv' && fileExtension !== 'txt' && !validTypes.includes(file.type)) {
                alert('⚠️ Please select a valid CSV or TXT file.');
                fileInput.value = '';
                filePreview.classList.add('d-none');
                return;
            }

            // Validate file size (10MB max)
            const maxSize = 10 * 1024 * 1024; // 10MB in bytes
            if (file.size > maxSize) {
                alert('⚠️ File size exceeds 10MB. Please select a smaller file.');
                fileInput.value = '';
                filePreview.classList.add('d-none');
                return;
            }

            // Display file info with animation
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            filePreview.classList.remove('d-none');
        } else {
            filePreview.classList.add('d-none');
        }
    });

    // Form submit event
    form.addEventListener('submit', function(e) {
        if (!fileInput.files.length) {
            e.preventDefault();
            alert('⚠️ Please select a CSV file to import.');
            return;
        }

        // Show loading state
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
        
        // Show processing message
        const filePreviewDiv = document.getElementById('filePreview');
        if (filePreviewDiv && !filePreviewDiv.classList.contains('d-none')) {
            filePreviewDiv.classList.remove('alert-success');
            filePreviewDiv.classList.add('alert-info');
            filePreviewDiv.innerHTML = '<h6 class="alert-heading mb-2"><i class="ri-loader-line me-1"></i>Processing...</h6><p class="mb-0">Please wait while we process your file...</p>';
        }
        
        // Allow form to submit normally
    });

    // Helper function to format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
    }

    // Auto-hide success/info alerts after 10 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-success, .alert-info');
        alerts.forEach(function(alert) {
            if (alert.querySelector('.btn-close')) {
                try {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                } catch(e) {
                    // Bootstrap Alert may not be initialized, just hide it
                    alert.style.display = 'none';
                }
            }
        });
    }, 10000);

    // Add smooth scroll to upload history after form submit
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('scrollTo') === 'history') {
        setTimeout(function() {
            const historySection = document.querySelector('.upload-history-section');
            if (historySection) {
                historySection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 500);
    }
});
</script>
@endsection

