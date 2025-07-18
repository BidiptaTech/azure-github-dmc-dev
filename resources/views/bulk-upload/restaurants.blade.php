@extends('layouts.layout')
@section('title', 'Restaurant Bulk Upload')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Restaurant Bulk Upload</h4>
                <p class="text-muted mb-0">Upload multiple restaurants with their meals at once using CSV file</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Bulk Upload</a></li>
                    <li class="breadcrumb-item active">Restaurants</li>
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
                            <p class="text-muted small mb-2">Download CSV template with your existing restaurants and meals data.</p>
                            <a href="{{ route('bulk-upload.restaurants.template') }}" class="btn btn-outline-primary btn-sm">
                                <i class="ri-download-line me-1"></i>Download Template
                            </a>
                        </div>
                        
                        <div class="mb-3">
                            <h6>Step 2: Fill Data</h6>
                            <p class="text-muted small">Review and modify your existing data, or add new restaurants and meals. If you have no existing restaurants, the template will include sample data. Required fields are marked with *</p>
                        </div>
                        
                        <div class="mb-3">
                            <h6>Step 3: Upload File</h6>
                            <p class="text-muted small">Upload your completed CSV file using the upload area.</p>
                        </div>

                        <div class="alert alert-success mb-3">
                            <h6 class="alert-heading">📊 Export & Import Feature</h6>
                            <p class="mb-0">The template download includes all your existing restaurants. You can modify this data and re-upload to update your restaurants, or add new ones.</p>
                        </div>

                        <div class="alert alert-info mb-4">
                            <h6 class="alert-heading">🍽️ Restaurant Data Upload Format</h6>
                            <p class="mb-3">Virtual DMCs can upload restaurant information only (no meal data):</p>
                            
                            <div class="mb-3">
                                <strong>Required Fields:</strong>
                                <ul class="mb-2 mt-1 small">
                                    <li><strong>Restaurant Name*</strong> - Name of the restaurant</li>
                                    <li><strong>Cuisine*</strong> - Type of cuisine (Italian, French, Indian, etc.)</li>
                                    <li><strong>Country*</strong> - Restaurant location country</li>
                                    <li><strong>City*</strong> - Restaurant location city</li>
                                </ul>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Optional Fields:</strong>
                                <ul class="mb-2 mt-1 small">
                                    <li><strong>Latitude/Longitude:</strong> Geographic coordinates for location</li>
                                    <li><strong>Meal Availability:</strong> Breakfast/Lunch/Dinner (1=Available, 0=Not Available)</li>
                                    <li><strong>Opening Times:</strong> Use HH:MM format (24-hour)</li>
                                    <li><strong>Owned By:</strong> 0=Third Party, 1=Hotel Owned</li>
                                    <li><strong>Images:</strong> Master image URL and additional images (comma-separated)</li>
                                    <li><strong>Description:</strong> Restaurant details and overview</li>
                                    <li><strong>Terms & Conditions:</strong> Restaurant policies</li>
                                    <li><strong>Status:</strong> 1=Active, 0=Inactive</li>
                                </ul>
                            </div>
                            
                            <hr class="my-3">
                            <p class="mb-2"><strong>Upload Format:</strong></p>
                            <ul class="mb-0 small">
                                <li>One restaurant per row</li>
                                <li>Download template to see correct column order</li>
                                <li>All columns must be present (can be empty for optional fields)</li>
                            </ul>
                        </div>

                        <div class="alert alert-warning">
                            <small>
                                <i class="ri-lightbulb-line me-1"></i>
                                <strong>Tips:</strong>
                                <ul class="mb-0 mt-1">
                                    <li>Maximum file size: 10MB</li>
                                    <li>Supported formats: .csv</li>
                                    <li>Use exact text for dropdown values</li>
                                    <li>Prices should be numeric values only</li>
                                    <li>Status: 1 for Active, 0 for Inactive</li>
                                </ul>
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
                            <i class="ri-restaurant-2-line me-2"></i>Upload Restaurants
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

                        <form action="{{ route('bulk-upload.restaurants.upload') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label">Select CSV File</label>
                                <div class="dropzone-area border rounded p-4 text-center" id="dropzone">
                                    <div class="dropzone-content">
                                        <i class="ri-upload-cloud-2-line display-4 text-muted mb-3"></i>
                                        <h5>Drag & drop your file here</h5>
                                        <p class="text-muted">or <a href="#" class="text-primary" id="browseBtn">browse files</a></p>
                                        <input type="file" name="file" id="fileInput" class="d-none" accept=".csv" required>
                                        <small class="text-muted">Supported formats: .csv (Max: 10MB)</small>
                                    </div>
                                </div>
                                
                                <div id="fileInfo" class="mt-3 d-none">
                                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                                        <div class="d-flex align-items-center">
                                            <i class="ri-file-excel-2-line text-success me-2"></i>
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
                                    <input class="form-check-input" type="checkbox" name="skipDuplicates" id="skipDuplicates">
                                    <label class="form-check-label" for="skipDuplicates">
                                        Skip duplicate entries
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-primary" id="uploadBtn" disabled>
                                    <i class="ri-upload-line me-1"></i>Upload Restaurants
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
                @if(isset($uploadHistory) && $uploadHistory->count() > 0)
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
                                            @if($history->success_count == $history->total_records)
                                                <span class="badge bg-success px-3 py-2">
                                                    <i class="ri-check-double-line me-1"></i>Completed
                                                </span>
                                            @elseif($history->success_count > 0)
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
                                            <div class="fw-medium text-dark">{{ $history->file_name }}</div>
                                            <small class="text-muted">{{ date('M d, Y H:i', strtotime($history->created_at)) }}</small>
                                        </div>
                                    </div>
                                    @if($history->success_count == $history->total_records)
                                        <span class="badge bg-success">
                                            <i class="ri-check-line me-1"></i>Success
                                        </span>
                                    @elseif($history->success_count > 0)
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
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($history->created_at)->diffForHumans() }}</small>
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

.dropzone-area:hover {
    border-color: #6366f1 !important;
    background-color: rgba(99, 102, 241, 0.02);
}

.dropzone-area.dragover {
    border-color: #6366f1 !important;
    background-color: rgba(99, 102, 241, 0.05);
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');
    const browseBtn = document.getElementById('browseBtn');
    const uploadBtn = document.getElementById('uploadBtn');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const removeFile = document.getElementById('removeFile');
    const uploadForm = document.getElementById('uploadForm');
    const progressSection = document.getElementById('progressSection');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    
    // Flag to prevent duplicate submissions
    let isSubmitting = false;

    // Drag and drop functionality
    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropzone.classList.add('dragover');
    });

    dropzone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropzone.classList.remove('dragover');
    });

    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropzone.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    });

    dropzone.addEventListener('click', function() {
        fileInput.click();
    });

    browseBtn.addEventListener('click', function(e) {
        e.preventDefault();
        fileInput.click();
    });

    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            handleFile(this.files[0]);
        }
    });

    removeFile.addEventListener('click', function() {
        fileInput.value = '';
        fileInfo.classList.add('d-none');
        uploadBtn.disabled = true;
    });

    function handleFile(file) {
        // Validate file type - CSV only
        const allowedTypes = [
            'text/csv',
            'application/csv',
            'text/comma-separated-values'
        ];
        
        // Check file extension as well
        const fileName = file.name.toLowerCase();
        const hasValidExtension = fileName.endsWith('.csv');
        
        if (!allowedTypes.includes(file.type) && !hasValidExtension) {
            alert('Please select a valid CSV file (.csv)');
            return;
        }

        // Validate file size (10MB)
        if (file.size > 10 * 1024 * 1024) {
            alert('File size must be less than 10MB.');
            return;
        }

        // Display file info
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = formatFileSize(file.size);
        fileInfo.classList.remove('d-none');
        uploadBtn.disabled = false;
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Form submission with progress
    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Prevent duplicate submissions
        if (isSubmitting) {
            console.log('Form is already being submitted');
            return false;
        }
        
        isSubmitting = true;
        
        const formData = new FormData(this);
        
        // Show progress section
        progressSection.classList.remove('d-none');
        uploadBtn.disabled = true;
        
        // Simulate progress (in real implementation, you'd use XMLHttpRequest for actual progress)
        let progress = 0;
        const progressInterval = setInterval(function() {
            progress += Math.random() * 15;
            if (progress >= 95) {
                progress = 95;
                clearInterval(progressInterval);
            }
            
            progressBar.style.width = progress + '%';
            progressText.textContent = Math.round(progress) + '%';
        }, 200);
        
        // Submit form
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    // If response is not JSON, it's likely a redirect
                    window.location.href = response.url;
                    return { redirected: true };
                }
            });
        })
        .then(data => {
            clearInterval(progressInterval);
            progressBar.style.width = '100%';
            progressText.textContent = '100%';
            
            if (data.redirected) return; // Already handled redirect
            
            setTimeout(() => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Upload failed: ' + (data.message || 'Unknown error'));
                    progressSection.classList.add('d-none');
                    uploadBtn.disabled = false;
                    isSubmitting = false;
                }
            }, 500);
        })
        .catch(error => {
            clearInterval(progressInterval);
            console.error('Error during fetch operation:', error);
            
            // Instead of submitting the form again, just reload the page
            // This prevents the double submission
            window.location.reload();
        });
    });
});
</script>
@endsection 