@extends('layouts.layout')
@section('title', 'Ticket Bulk Upload')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Ticket Bulk Upload</h4>
                <p class="text-muted mb-0">Upload multiple tickets for your attractions using Excel/CSV file</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Bulk Upload</a></li>
                    <li class="breadcrumb-item active">Tickets</li>
                </ol>
            </nav>
        </div>

        <div class="row">
            <!-- Instructions Card -->
            <div class="col-12 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-information-line me-2"></i>Instructions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Step 1: Download Template</h6>
                            <p class="text-muted small mb-2">Download our CSV template with your attractions and their existing tickets.</p>
                            <a href="{{ route('bulk-upload.tickets.template') }}" class="btn btn-outline-primary btn-sm">
                                <i class="ri-download-line me-1"></i>Download Template
                            </a>
                        </div>
                        
                        <div class="mb-3">
                            <h6>Step 2: Fill Ticket Data</h6>
                            <p class="text-muted small">Add new tickets for your attractions. Each row represents one ticket for an attraction. Required fields are marked with *</p>
                        </div>
                        
                        <div class="mb-3">
                            <h6>Step 3: Upload File</h6>
                            <p class="text-muted small">Upload your completed CSV file using the upload area.</p>
                        </div>

                        <div class="alert alert-info">
                            <small>
                                <i class="ri-lightbulb-line me-1"></i>
                                <strong>Tips:</strong>
                                <ul class="mb-0 mt-1">
                                    <li>Maximum file size: 10MB</li>
                                    <li>Supported format: .csv only</li>
                                    <li>Multiple tickets per attraction allowed</li>
                                    <li>Price fields should be numeric values</li>
                                    <li>Only DMC users can upload tickets</li>
                                </ul>
                            </small>
                        </div>

                        <div class="alert alert-warning">
                            <small>
                                <i class="ri-alert-line me-1"></i>
                                <strong>Note:</strong> You can only create tickets for attractions that belong to your account.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Section -->
            <div class="col-12 col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-ticket-2-line me-2"></i>Upload Tickets
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="ri-check-circle-line me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="ri-error-warning-line me-2"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('errors') && count(session('errors')) > 0)
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <h6><i class="ri-alert-line me-2"></i>Upload Errors:</h6>
                                <ul class="mb-0">
                                    @foreach(session('errors') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('bulk-upload.tickets.upload') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label">Select CSV File</label>
                                <div class="dropzone-area border rounded p-4 text-center" id="dropzone">
                                    <div class="dropzone-content">
                                        <i class="ri-upload-cloud-2-line display-4 text-muted mb-3"></i>
                                        <h5>Drag & drop your file here</h5>
                                        <p class="text-muted">or <a href="#" class="text-primary" id="browseBtn">browse files</a></p>
                                        <input type="file" name="file" id="fileInput" class="d-none" accept=".csv" required>
                                        <small class="text-muted">Supported format: .csv (Max: 10MB)</small>
                                    </div>
                                </div>
                                
                                <div id="fileInfo" class="mt-3 d-none">
                                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                                        <div class="d-flex align-items-center">
                                            <i class="ri-file-text-line text-success me-2"></i>
                                            <div>
                                                <div class="fw-medium" id="fileName"></div>
                                                <small class="text-muted" id="fileSize"></small>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="removeFile">
                                            <i class="ri-close-line"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="skipDuplicates">
                                    <label class="form-check-label" for="skipDuplicates">
                                        Skip duplicate ticket names
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-primary" id="uploadBtn" disabled>
                                    <i class="ri-upload-line me-1"></i>Upload Tickets
                                </button>
                            </div>
                        </form>

                        <!-- Progress Bar -->
                        <div id="progressSection" class="mt-4 d-none">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Uploading...</span>
                                <span id="progressText">0%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" id="progressBar" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Uploads - Modern UI -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-gradient-primary text-white border-0">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 text-white">
                        <i class="ri-history-line me-2"></i>Recent Upload History
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
                            <p class="text-muted small mb-0">Your upload history will appear here once you start uploading files.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.dropzone-area {
    border: 2px dashed #e0e0e0 !important;
    transition: all 0.3s ease;
    cursor: pointer;
}

.dropzone-area:hover,
.dropzone-area.dragover {
    border-color: #696cff !important;
    background-color: #f8f9ff;
}

.dropzone-area.dragover {
    transform: scale(1.02);
}

.btn-disabled {
    pointer-events: none;
    opacity: 0.6;
}

/* Modern Upload History Styles */
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

/* Soft colored badges for counts */
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

/* Responsive improvements */
@media (max-width: 768px) {
    .card-header.bg-gradient-primary {
        padding: 1rem;
    }
    
    .upload-card {
        padding: 1rem !important;
    }
    
    .badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }
}

/* Animation for loading states */
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

/* Status indicator improvements */
.badge i {
    font-size: 0.875em;
}

/* Table header icons */
.table thead th i {
    opacity: 0.7;
    font-size: 0.9em;
}
</style>

<script src="{{ asset('js/bulk-upload.js') }}"></script>
@endsection 