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
                                            <i class="ri-hotel-bed-line"></i>
                                        </div>
                                        <div>
                                            <h4 class="card-title mb-1 text-white">Bulk Upload Beds</h4>
                                            <h6 class="text-white-50 mb-0">{{ $hotel->name }}</h6>
                                        </div>
                                    </div>
                                    <p class="text-white-75 mb-0">
                                        <i class="ri-map-pin-line me-1"></i>{{ $hotel->city }}, {{ $hotel->country }}
                                    </p>
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ route('beds.template_for_hotel', $hotel->hotel_unique_id) }}" 
                                       class="btn btn-light btn-sm shadow-sm">
                                        <i class="ri-download-cloud-2-line me-1"></i>Download Template
                                    </a>
                                    <a href="{{ route('hotels.beds', $hotel->hotel_unique_id) }}" 
                                       class="btn btn-outline-light btn-sm">
                                        <i class="ri-arrow-left-line me-1"></i>Back to Beds
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
                            <x-alert />
                            
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
                                                    <span>Fill in your bed data (required fields marked with *)</span>
                                                </div>
                                                <div class="instruction-item">
                                                    <i class="ri-file-text-line text-warning"></i>
                                                    <span>Maximum file size: 10MB • Formats: CSV, TXT</span>
                                                </div>
                                                <div class="instruction-item">
                                                    <i class="ri-database-line text-info"></i>
                                                    <span>Maximum 1000 rows per upload</span>
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
                                                    <span><strong>Required:</strong> Room Type, Bed Type, No. of Rooms, Adult Count, Extra Bed, Baby Cot, Status</span>
                                                </div>
                                                <div class="instruction-item">
                                                    <i class="ri-money-dollar-circle-line text-info"></i>
                                                    <span>All price fields must be numeric and greater than or equal to 0</span>
                                                </div>
                                                <div class="instruction-item">
                                                    <i class="ri-text-direction-line text-primary"></i>
                                                    <span><strong>Text Values:</strong> Use exact Room Type and Bed Type names (not IDs)</span>
                                                </div>
                                                <div class="instruction-item">
                                                    <i class="ri-toggle-line text-info"></i>
                                                    <span><strong>Yes/No Fields:</strong> Extra Bed, Baby Cot, Force Child (Yes/No or 1/0)</span>
                                                </div>
                                                <div class="instruction-item">
                                                    <i class="ri-toggle-line text-info"></i>
                                                    <span><strong>Status:</strong> Active/Inactive or 1/0</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Available Bed Types Section -->
                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <div class="instruction-section">
                                                <h6 class="instruction-section-title">
                                                    <i class="ri-hotel-bed-line me-2"></i>Available Bed Types for {{ $hotel->name }}
                                                </h6>
                                                @if($bedTypes->count() > 0)
                                                    <div class="alert alert-info mb-3">
                                                        <strong>CSV Format:</strong> Use the exact bed type names shown below in your CSV file.
                                                    </div>
                                                    <div class="row">
                                                        @foreach($bedTypes as $bedType)
                                                            <div class="col-md-6 mb-2">
                                                                <div class="d-flex align-items-center p-2 bg-light rounded">
                                                                    <i class="ri-hotel-bed-line text-primary me-2"></i>
                                                                    <span class="fw-medium">{{ $bedType->name }}</span>
                                                                    <small class="text-muted ms-auto">(Max: {{ $bedType->max_occupancy }} guests)</small>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="alert alert-warning">
                                                        <i class="ri-alert-line me-2"></i>
                                                        No bed types are configured for this hotel. Please add bed types first before uploading beds.
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Available Room Categories Section -->
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <div class="instruction-section">
                                                <h6 class="instruction-section-title">
                                                    <i class="ri-building-line me-2"></i>Available Room Categories for {{ $hotel->name }}
                                                </h6>
                                                @if($roomCategories->count() > 0)
                                                    <div class="alert alert-info mb-3">
                                                        <strong>CSV Format:</strong> Use the exact room type names shown below in your CSV file.
                                                    </div>
                                                    <div class="row">
                                                        @foreach($roomCategories as $room)
                                                            <div class="col-md-6 mb-2">
                                                                <div class="d-flex align-items-center p-2 bg-light rounded">
                                                                    <i class="ri-building-line text-info me-2"></i>
                                                                    <span class="fw-medium">{{ $room->room_type }}</span>
                                                                    <small class="text-muted ms-auto">({{ $room->no_of_room }} rooms available)</small>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="alert alert-warning">
                                                        <i class="ri-alert-line me-2"></i>
                                                        No room categories are configured for this hotel. Please add room categories first.
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Available Extra Bed Types Section -->
                                    <div class="row mt-4">
                                        <div class="col-12">
                                            <div class="instruction-section">
                                                <h6 class="instruction-section-title">
                                                    <i class="ri-hotel-bed-line me-2"></i>Available Extra Bed Types
                                                </h6>
                                                <div class="alert alert-info mb-3">
                                                    <strong>CSV Format:</strong> Use one of these exact extra bed type names when Extra Bed = Yes.
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-4 mb-2">
                                                        <div class="d-flex align-items-center p-2 bg-light rounded">
                                                            <i class="ri-sofa-line text-success me-2"></i>
                                                            <span class="fw-medium">Sofa Bed</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 mb-2">
                                                        <div class="d-flex align-items-center p-2 bg-light rounded">
                                                            <i class="ri-hotel-bed-line text-success me-2"></i>
                                                            <span class="fw-medium">Wall Bed</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 mb-2">
                                                        <div class="d-flex align-items-center p-2 bg-light rounded">
                                                            <i class="ri-hotel-bed-line text-success me-2"></i>
                                                            <span class="fw-medium">Futon</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 mb-2">
                                                        <div class="d-flex align-items-center p-2 bg-light rounded">
                                                            <i class="ri-hotel-bed-line text-success me-2"></i>
                                                            <span class="fw-medium">Rollaway Bed</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 mb-2">
                                                        <div class="d-flex align-items-center p-2 bg-light rounded">
                                                            <i class="ri-hotel-bed-line text-success me-2"></i>
                                                            <span class="fw-medium">Bunk Bed</span>
                                                        </div>
                                                    </div>
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
                                                        <span class="ms-2">Use exact Room Type and Bed Type names from the lists above. Use Yes/No or 1/0 for boolean fields. Ensure total rooms don't exceed available rooms for each category. All required fields must be filled. Status must be Active/Inactive or 1/0.</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Upload Form -->
                            <div class="upload-section">
                                <form action="{{ route('beds.upload_for_hotel', $hotel->hotel_unique_id) }}" 
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
                                                <i class="ri-upload-cloud-2-line me-2"></i>Upload Beds
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

                    <!-- Recent Uploads - Modern UI -->
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
                                                                <small class="text-muted">CSV File</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="py-3">
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2">
                                                                <i class="ri-database-2-line me-1"></i>{{ $history->total_records }} rows
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
                                                </tr>
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
                                                        {{ $history->total_records }} rows
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
                                        <p class="text-muted small mb-0">Upload history for <strong>{{ $hotel->name }}</strong> will appear here once you start uploading bed files.</p>
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
/* Include the same styles from attraction-tickets.blade.php */
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

.page-content {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
    padding: 2rem 0;
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

.text-white-50 {
    color: rgba(255, 255, 255, 0.7) !important;
}

.text-white-75 {
    color: rgba(255, 255, 255, 0.85) !important;
}

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

.empty-state {
    padding: 3rem 2rem;
}

.card.border-0.shadow-sm {
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06) !important;
    border-radius: 12px;
    overflow: hidden;
}

.card-header.bg-gradient-primary {
    padding: 1.25rem 1.5rem;
}

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

.upload-row,
.upload-card {
    animation: fadeInUp 0.3s ease-out;
}

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
        padding: 1rem;
    }
    
    .btn {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
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

<script src="{{ asset('js/bulk-upload.js') }}"></script>
@endsection 