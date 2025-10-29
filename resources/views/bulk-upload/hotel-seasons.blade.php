@extends('layouts.layout')

@section('content')
<div class="page-content">
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card gradient-header">
                    <div class="card-header border-0 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="header-content">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="icon-wrapper me-3">
                                        <i class="ri-calendar-2-line"></i>
                                    </div>
                                    <div>
                                        <h4 class="card-title mb-1 text-white">Bulk Upload Hotel Seasons</h4>
                                        <h6 class="text-white-50 mb-0">{{ $hotel->name }}</h6>
                                    </div>
                                </div>
                                <p class="text-white-75 mb-0">
                                    <i class="ri-map-pin-line me-1"></i>{{ $hotel->city ?? 'N/A' }}, {{ $hotel->country ?? 'N/A' }}
                                </p>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('seasons.template_for_hotel', $hotel->hotel_unique_id) }}" 
                                   class="btn btn-light btn-sm shadow-sm">
                                    <i class="ri-download-cloud-2-line me-1"></i>Download Template
                                </a>
                                <a href="{{ route('hotels.season', $hotel->hotel_unique_id) }}" 
                                   class="btn btn-outline-light btn-sm">
                                    <i class="ri-arrow-left-line me-1"></i>Back to Seasons
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
                <div class="card modern-card">
                    <div class="card-body p-4">
                        
                        <!-- Success/Warning/Error Messages -->
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show modern-alert mb-4" role="alert">
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
                            <div class="alert alert-warning alert-dismissible fade show modern-alert mb-4" role="alert">
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
                                                Please review the errors and correct the data in your CSV file for the failed rows.
                                            </small>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show modern-alert mb-4" role="alert">
                                <div class="d-flex align-items-start">
                                    <div class="alert-icon me-3">
                                        <i class="ri-close-circle-line"></i>
                                    </div>
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
                        
                        <!-- Upload Instructions -->
                        <div class="instruction-box mb-4">
                            <div class="instruction-header">
                                <i class="ri-information-line me-2"></i>
                                <strong>Season Upload Instructions</strong>
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
                                                <span>Fill in your season data (required fields marked with *)</span>
                                            </div>
                                            <div class="instruction-item">
                                                <i class="ri-file-text-line text-warning"></i>
                                                <span>Maximum file size: 10MB • Formats: CSV, TXT</span>
                                            </div>
                                            <div class="instruction-item">
                                                <i class="ri-database-line text-info"></i>
                                                <span>Maximum 100 seasons per upload</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="instruction-section">
                                            <h6 class="instruction-section-title">
                                                <i class="ri-shield-check-line me-2"></i>Season Validation
                                            </h6>
                                            <div class="instruction-item">
                                                <i class="ri-calendar-check-line text-success"></i>
                                                <span><strong>Date Overlap:</strong> System prevents overlapping seasons</span>
                                            </div>
                                            <div class="instruction-item">
                                                <i class="ri-calendar-schedule-line text-primary"></i>
                                                <span><strong>Date Format:</strong> Use MM/DD/YYYY format only (e.g., 06/01/2025)</span>
                                            </div>
                                            <div class="instruction-item">
                                                <i class="ri-calendar-event-line text-warning"></i>
                                                <span><strong>Date Logic:</strong> Start date must be before end date</span>
                                            </div>
                                            <div class="instruction-item">
                                                <i class="ri-user-line text-info"></i>
                                                <span><strong>DMC Access:</strong> Only your hotel seasons will be affected</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Season Pricing Structure Overview -->
                                <div class="row g-4 mt-3">
                                    <div class="col-12">
                                        <h6 class="instruction-section-title">
                                            <i class="ri-money-dollar-circle-line me-2"></i>Season Pricing Structure Overview
                                        </h6>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="season-price-card single-occupancy-card">
                                            <div class="season-price-header">
                                                <i class="ri-user-line me-2"></i>
                                                <h6 class="mb-0 text-white">Single Occupancy Pricing</h6>
                                            </div>
                                            <div class="season-price-content">
                                                <div class="season-price-item">
                                                    <i class="ri-sun-line text-warning me-2"></i>
                                                    <span><strong>Weekday Price:</strong> Monday to Friday rates</span>
                                                </div>
                                                <div class="season-price-item">
                                                    <i class="ri-calendar-2-line text-success me-2"></i>
                                                    <span><strong>Weekend Price:</strong> Saturday and Sunday rates</span>
                                                </div>
                                                <div class="season-price-example">
                                                    <small class="text-muted">
                                                        <strong>Example:</strong> Weekday: $150.00, Weekend: $200.00
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="season-price-card double-occupancy-card">
                                            <div class="season-price-header">
                                                <i class="ri-group-line me-2"></i>
                                                <h6 class="mb-0 text-white">Double Occupancy Pricing</h6>
                                            </div>
                                            <div class="season-price-content">
                                                <div class="season-price-item">
                                                    <i class="ri-sun-line text-warning me-2"></i>
                                                    <span><strong>Weekday Price:</strong> Monday to Friday rates for two guests</span>
                                                </div>
                                                <div class="season-price-item">
                                                    <i class="ri-calendar-2-line text-success me-2"></i>
                                                    <span><strong>Weekend Price:</strong> Saturday and Sunday rates for two guests</span>
                                                </div>
                                                <div class="season-price-example">
                                                    <small class="text-muted">
                                                        <strong>Example:</strong> Weekday: $250.00, Weekend: $300.00
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row g-4 mt-2">
                                    <div class="col-md-6">
                                        <div class="instruction-section">
                                            <h6 class="instruction-section-title">
                                                <i class="ri-list-check-line me-2"></i>Required Fields
                                            </h6>
                                            <div class="instruction-item">
                                                <i class="ri-text text-danger"></i>
                                                <span><strong>Season Name:</strong> Descriptive name for the season</span>
                                            </div>
                                            <div class="instruction-item">
                                                <i class="ri-money-dollar-line text-primary"></i>
                                                <span><strong>All Price Fields:</strong> Single & Double, Weekday & Weekend</span>
                                            </div>
                                            <div class="instruction-item">
                                                <i class="ri-calendar-line text-warning"></i>
                                                <span><strong>Date Range:</strong> Start and end dates in MM/DD/YYYY format (month/day/year)</span>
                                            </div>
                                            <div class="instruction-item">
                                                <i class="ri-toggle-line text-info"></i>
                                                <span><strong>Status:</strong> 1 = Active, 0 = Inactive</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="instruction-section">
                                            <h6 class="instruction-section-title">
                                                <i class="ri-error-warning-line me-2"></i>Important Rules
                                            </h6>
                                            <div class="instruction-item">
                                                <i class="ri-calendar-close-line text-danger"></i>
                                                <span>Cannot create overlapping seasons for the same hotel</span>
                                            </div>
                                            <div class="instruction-item">
                                                <i class="ri-calculator-line text-primary"></i>
                                                <span>Enter prices as numbers without currency symbols</span>
                                            </div>
                                            <div class="instruction-item">
                                                <i class="ri-calendar-check-line text-warning"></i>
                                                <span>All dates must be valid and start before end</span>
                                            </div>
                                            <div class="instruction-item">
                                                <i class="ri-calendar-check-line text-warning"></i>
                                                <span>Use forward slashes (/) not dashes (-) in dates (e.g., 01/05/2025)</span>
                                            </div>
                                            <div class="instruction-item">
                                                <i class="ri-save-line text-success"></i>
                                                <span>Save file as CSV format before uploading</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="alert alert-info border-0 mb-0 pro-tips-alert">
                                            <div class="d-flex align-items-start">
                                                <i class="ri-lightbulb-line me-2 mt-1 text-warning"></i>
                                                <div>
                                                    <strong class="text-primary">Pro Tips:</strong> 
                                                    <div class="pro-tips-content mt-2">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <div class="pro-tip-item">
                                                                    <i class="ri-calendar-event-line text-success me-2"></i>
                                                                    <span>Plan seasons well in advance to avoid booking conflicts</span>
                                                                </div>
                                                                <div class="pro-tip-item">
                                                                    <i class="ri-price-tag-3-line text-primary me-2"></i>
                                                                    <span>Consider market demand when setting seasonal prices</span>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="pro-tip-item">
                                                                    <i class="ri-calendar-schedule-line text-warning me-2"></i>
                                                                    <span>Use consistent naming convention for your seasons</span>
                                                                </div>
                                                                <div class="pro-tip-item">
                                                                    <i class="ri-file-excel-line text-warning me-2"></i>
                                                                    <span>Excel may auto-format dates - check they remain MM/DD/YYYY</span>
                                                                </div>
                                                                <div class="pro-tip-item">
                                                                    <i class="ri-check-double-line text-success me-2"></i>
                                                                    <span>Review all data before uploading to prevent errors</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Form -->
                        <div class="upload-section">
                            <form action="{{ route('seasons.upload_for_hotel', $hotel->hotel_unique_id) }}" 
                                  method="POST" 
                                  enctype="multipart/form-data" 
                                  class="upload-form">
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
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary w-100 upload-btn">
                                            <i class="ri-upload-cloud-2-line me-2"></i>Upload Seasons
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload History Section -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-gradient-primary text-white border-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0 text-white">
                                <i class="ri-history-line me-2"></i>Recent Upload History
                                <small class="d-block text-white-50 fs-6 mt-1">{{ $hotel->name }}</small>
                            </h5>
                            <span class="badge bg-light bg-opacity-20 text-white">
                                {{ $uploadHistory ? $uploadHistory->count() : 0 }} uploads
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if($uploadHistory && $uploadHistory->count() > 0)
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
                                                        <span class="fw-medium text-dark">{{ $history->formatted_date }}</span>
                                                        <small class="text-muted">{{ $history->relative_time }}</small>
                                                    </div>
                                                </td>
                                                <td class="py-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="file-icon me-3">
                                                            <i class="ri-file-excel-2-line text-success fs-4"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-medium text-dark">{{ $history->original_file_name }}</div>
                                                            <small class="text-muted">Season CSV File</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3">
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2">
                                                            <i class="ri-database-2-line me-1"></i>{{ $history->total_records }} seasons
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="py-3">
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                                            <i class="ri-check-line me-1"></i>{{ $history->success_count }} success
                                                        </span>
                                                        @if($history->error_count > 0)
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
                                                    <div class="fw-medium text-dark">{{ $history->original_file_name }}</div>
                                                    <small class="text-muted">{{ $history->compact_date }}</small>
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
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex gap-2">
                                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                                    {{ $history->total_records }} seasons
                                                </span>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                                    {{ $history->success_count }} ✓
                                                </span>
                                                @if($history->error_count > 0)
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                                        {{ $history->error_count }} ✗
                                                    </span>
                                                @endif
                                            </div>
                                            <small class="text-muted">{{ $history->relative_time }}</small>
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
                                        @endif
                                    </div>
                                    
                                    <!-- Mobile Error Details Modal -->
                                    @if(($history->error_count ?? 0) > 0 && !empty($history->errors))
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
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="empty-state">
                                    <div class="mb-3">
                                        <i class="ri-inbox-line text-muted" style="font-size: 4rem;"></i>
                                    </div>
                                    <h6 class="text-muted mb-2">No Upload History</h6>
                                    <p class="text-muted small mb-0">Upload history for <strong>{{ $hotel->name }}</strong> will appear here once you start uploading season files.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern Alert Styles */
.modern-alert {
    border: none;
    border-radius: 0.5rem;
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

.modern-alert .alert-icon {
    font-size: 2rem;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.5);
    border-radius: 50%;
    flex-shrink: 0;
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

/* Season Pricing Cards */
.season-price-card {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    height: 100%;
}

.season-price-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.single-occupancy-card .season-price-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 1rem;
}

.double-occupancy-card .season-price-header {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    padding: 1rem;
}

.season-price-content {
    padding: 1.25rem;
    background: white;
}

.season-price-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.75rem;
    font-size: 0.9rem;
}

.season-price-item:last-of-type {
    margin-bottom: 1rem;
}

.season-price-example {
    background: #f8f9fa;
    padding: 0.75rem;
    border-radius: 8px;
    border-left: 4px solid #e9ecef;
}

/* Pro Tips Enhanced Styling */
.pro-tips-alert {
    background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
    border-left: 4px solid #2196f3;
}

.pro-tips-content {
    margin-top: 0.75rem;
}

.pro-tip-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 0.5rem;
    padding: 0.25rem 0;
    font-size: 0.9rem;
    line-height: 1.4;
}

.pro-tip-item:last-child {
    margin-bottom: 0;
}

.pro-tip-item i {
    margin-top: 0.1rem;
    flex-shrink: 0;
    width: 16px;
}

/* Enhanced Animation */
.season-price-card {
    animation: slideInUp 0.6s ease-out;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Color Variables */
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

/* Base Styles */
.page-content {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
    padding: 2rem 0;
}

/* Modern Card Styling */
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

/* Gradient Header */
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
    padding: 2rem;
    border: 1px solid rgba(102, 126, 234, 0.1);
}

.instruction-header {
    color: var(--primary-color);
    font-size: 1.1rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
}

.instruction-section {
    margin-bottom: 1.5rem;
}

.instruction-section:last-child {
    margin-bottom: 0;
}

.instruction-section-title {
    color: var(--primary-color);
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    border-bottom: 2px solid rgba(102, 126, 234, 0.1);
    padding-bottom: 0.5rem;
}

.instruction-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.5rem 0;
    font-size: 0.9rem;
    line-height: 1.4;
}

.instruction-item i {
    font-size: 1.1rem;
    width: 20px;
    text-align: center;
    margin-top: 0.1rem;
    flex-shrink: 0;
}

.instruction-item strong {
    color: var(--primary-color);
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

/* Button Enhancements */
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

/* Upload History Styles */
.bg-gradient-primary {
    background: linear-gradient(135deg, #696cff 0%, #5a67d8 100%);
}

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

.badge {
    font-weight: 500;
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
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

.modern-card {
    animation: fadeInUp 0.6s ease-out;
}

.gradient-header {
    animation: slideInLeft 0.8s ease-out;
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-content {
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
        padding: 1.5rem;
    }
    
    .season-price-card {
        margin-bottom: 1rem;
    }
    
    .season-price-content {
        padding: 1rem;
    }
    
    .season-price-item {
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
    }
    
    .pro-tip-item {
        font-size: 0.85rem;
        margin-bottom: 0.4rem;
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
    
    .instruction-box {
        padding: 1rem;
    }
}
</style>

<script src="{{ asset('js/bulk-upload.js') }}"></script>
@endsection
