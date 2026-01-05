@extends('layouts.layout')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-xl-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-gradient-primary text-white border-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0 text-white">
                                <i class="ri-user-star-line me-2"></i>Driver Bulk Upload
                            </h5>
                            <div class="d-flex gap-2">
                                <a href="{{ route('bulk-upload.drivers.template') }}" class="btn btn-light btn-sm">
                                    <i class="ri-download-line me-1"></i>Download Template
                                </a>
                                <span class="badge bg-light bg-opacity-20 text-white">
                                    {{ Auth::user()->country ?? 'Country Not Set' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        
                        <!-- Success/Error Messages -->
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
                                <i class="ri-alert-line me-2"></i>
                                <strong>Upload Errors:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach(session('errors') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(isset($errors) && is_object($errors) && method_exists($errors, 'any') && $errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="ri-error-warning-line me-2"></i>
                                <strong>Validation Errors:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        
                        <!-- Upload Instructions -->
                        <div class="alert alert-info border-0 mb-4">
                            <div class="d-flex align-items-start">
                                <i class="ri-information-line me-2 mt-1"></i>
                                <div>
                                    <h6 class="mb-2">📋 Upload Instructions</h6>
                                    <ul class="mb-0 small">
                                        <li><strong>Required Fields:</strong> Salutation, Gender, Name, Email, Phone, Address, Country, City, License No, License Expiry, Age, Profile Image</li>
                                        <li><strong>Email Validation:</strong> Must be unique - no duplicate emails allowed</li>
                                        <li><strong>License Validation:</strong> Must be unique for your account - no duplicate license numbers</li>
                                        <li><strong>Access Restriction:</strong> Only Virtual DMC and DMC users can upload drivers</li>
                                        <li><strong>Country Restriction:</strong> You can only upload drivers for your country: <strong>{{ Auth::user()->country ?? 'Please set your country' }}</strong></li>
                                        <li><strong>City Validation:</strong> City must exist in our database for your country</li>
                                        <li><strong>Age Validation:</strong> Must meet minimum age requirements for your country</li>
                                        <li><strong>File Format:</strong> CSV file only, maximum 10MB</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('bulk-upload.drivers.upload') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                            @csrf
                            
                            <!-- File Upload Area -->
                            <div class="mb-4">
                                <div class="dropzone-area border-2 border-dashed rounded-3 p-4 text-center position-relative" id="dropzone">
                                    <div class="dropzone-content">
                                        <i class="ri-upload-cloud-2-line display-4 text-muted mb-3"></i>
                                        <h6 class="mb-2">Drag & Drop your CSV file here</h6>
                                        <p class="text-muted mb-3">or</p>
                                        <button type="button" class="btn btn-outline-primary" id="browseBtn">
                                            <i class="ri-folder-open-line me-1"></i>Browse Files
                                        </button>
                                        <input type="file" name="file" id="fileInput" accept=".csv,.txt" class="d-none" required>
                                    </div>
                                    <div class="upload-progress d-none">
                                        <div class="progress mb-2">
                                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                        </div>
                                        <small class="text-muted">Uploading...</small>
                                    </div>
                                </div>
                                <div class="file-info mt-2 d-none">
                                    <small class="text-muted">
                                        <i class="ri-file-text-line me-1"></i>
                                        <span id="fileName"></span>
                                        <span id="fileSize" class="ms-2"></span>
                                    </small>
                                </div>
                            </div>

                            <!-- Upload Button -->
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary px-4" id="uploadBtn" disabled>
                                    <i class="ri-upload-line me-1"></i>Upload Drivers
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Uploads - Modern UI -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-gradient-primary text-white border-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0 text-white">
                                <i class="ri-history-line me-2"></i>Recent Upload History
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
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($uploadHistory as $history)
                                            <tr class="upload-row">
                                                <td class="py-3">
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-medium text-dark">{{ $history->created_at->format('M d, Y') }}</span>
                                                        <small class="text-muted">{{ $history->created_at->diffForHumans() }}</small>
                                                    </div>
                                                </td>
                                                <td class="py-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="file-icon me-3">
                                                            <i class="ri-file-excel-2-line text-success fs-4"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-medium text-dark">{{ $history->original_file_name ?? $history->file_name }}</div>
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
                                                    @if($history->error_count == 0)
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
                                    <div class="p-4 border-bottom upload-card">
                                        <div class="d-flex align-items-start">
                                            <div class="file-icon me-3">
                                                <i class="ri-file-excel-2-line text-success fs-3"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <h6 class="mb-1 text-dark">{{ $history->original_file_name ?? $history->file_name }}</h6>
                                                        <div class="d-flex align-items-center text-muted small">
                                                            <i class="ri-calendar-line me-1"></i>
                                                            {{ $history->created_at->format('M d, Y • h:i A') }}
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        @if($history->error_count == 0)
                                                            <span class="badge bg-success">
                                                                <i class="ri-check-double-line me-1"></i>Completed
                                                            </span>
                                                        @elseif($history->success_count > 0)
                                                            <span class="badge bg-warning">
                                                                <i class="ri-alert-line me-1"></i>Partial
                                                            </span>
                                                        @else
                                                            <span class="badge bg-danger">
                                                                <i class="ri-close-circle-line me-1"></i>Failed
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <div class="row g-3">
                                                    <div class="col-4">
                                                        <div class="text-center p-2 bg-info bg-opacity-10 rounded">
                                                            <div class="fw-bold text-info">{{ $history->total_records }}</div>
                                                            <div class="small text-muted">Total Records</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="text-center p-2 bg-success bg-opacity-10 rounded">
                                                            <div class="fw-bold text-success">{{ $history->success_count }}</div>
                                                            <div class="small text-muted">Successful</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="text-center p-2 bg-danger bg-opacity-10 rounded">
                                                            <div class="fw-bold text-danger">{{ $history->error_count }}</div>
                                                            <div class="small text-muted">Failed</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                @if($history->success_count > 0)
                                                    <div class="progress mt-3" style="height: 8px;">
                                                        <div class="progress-bar bg-success rounded" 
                                                             style="width: {{ ($history->success_count / $history->total_records) * 100 }}%">
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="ri-file-upload-line display-4 text-muted"></i>
                                </div>
                                <h6 class="text-muted">No upload history found</h6>
                                <p class="text-muted mb-0">Your recent driver uploads will appear here</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dropzone-area {
    border-color: #e3e6f0 !important;
    transition: all 0.3s ease;
}

.dropzone-area:hover {
    border-color: #696cff !important;
    background-color: #f8f9ff;
}

.dropzone-area.dragover {
    border-color: #696cff !important;
    background-color: #f0f2ff;
}

.upload-row:hover {
    background-color: #f8f9ff;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(105, 108, 255, 0.1);
    transition: all 0.2s ease;
}

.modern-table {
    border-collapse: separate;
    border-spacing: 0;
}

.modern-table th {
    background: linear-gradient(135deg, #f8f9ff 0%, #e3e6f0 100%);
    font-weight: 600;
    color: #5a5c69;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.upload-card {
    transition: all 0.3s ease;
    border-radius: 8px;
    margin: 0.5rem;
}

.upload-card:hover {
    background-color: #f8f9ff;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(105, 108, 255, 0.15);
}

.file-icon {
    padding: 0.75rem;
    background: linear-gradient(135deg, #e8f5e8 0%, #f0f9ff 100%);
    border-radius: 12px;
    border: 1px solid rgba(34, 197, 94, 0.2);
}

.metric-box {
    border-radius: 8px;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}

.metric-box:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-color: rgba(105, 108, 255, 0.2);
}

.progress {
    height: 8px;
    border-radius: 4px;
    overflow: hidden;
    background: rgba(105, 108, 255, 0.1);
}

.progress-bar {
    border-radius: 4px;
    background: linear-gradient(45deg, #22c55e 0%, #16a34a 100%);
    position: relative;
    overflow: hidden;
}

.progress-bar::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { left: -100%; }
    100% { left: 100%; }
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table-hover tbody tr:hover {
    background-color: #f8f9ff;
}

.btn-outline-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(105, 108, 255, 0.3);
}

.file-info {
    padding: 0.5rem;
    background-color: #f8f9ff;
    border-radius: 6px;
    border: 1px solid #e3e6f0;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #696cff 0%, #5a67d8 100%);
}

.card {
    border-radius: 12px;
    overflow: hidden;
}

.card-header {
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.empty-state {
    padding: 3rem 1rem;
}

.empty-state i {
    opacity: 0.6;
}

.border-opacity-25 {
    --bs-border-opacity: 0.25;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');
    const browseBtn = document.getElementById('browseBtn');
    const uploadBtn = document.getElementById('uploadBtn');
    const fileInfo = document.querySelector('.file-info');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const uploadForm = document.getElementById('uploadForm');

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    // Highlight drop area when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, unhighlight, false);
    });

    // Handle dropped files
    dropzone.addEventListener('drop', handleDrop, false);

    // Handle browse button click
    browseBtn.addEventListener('click', () => fileInput.click());

    // Handle file input change
    fileInput.addEventListener('change', handleFileSelect);
    
    // Prevent double file selection
    fileInput.addEventListener('click', function(e) {
        this.value = '';
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    function highlight() {
        dropzone.classList.add('dragover');
    }

    function unhighlight() {
        dropzone.classList.remove('dragover');
    }

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            fileInput.files = files;
            handleFileSelect();
        }
    }

    function handleFileSelect() {
        const file = fileInput.files[0];
        
        if (file) {
            // Validate file type
            if (!file.name.toLowerCase().endsWith('.csv') && !file.name.toLowerCase().endsWith('.txt')) {
                alert('Please select a CSV or TXT file.');
                fileInput.value = '';
                return;
            }

            // Validate file size (10MB)
            if (file.size > 10 * 1024 * 1024) {
                alert('File size must be less than 10MB.');
                fileInput.value = '';
                return;
            }

            // Show file info
            fileName.textContent = file.name;
            fileSize.textContent = `(${formatFileSize(file.size)})`;
            fileInfo.classList.remove('d-none');
            uploadBtn.disabled = false;
        } else {
            fileInfo.classList.add('d-none');
            uploadBtn.disabled = true;
        }
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Handle form submission
    uploadForm.addEventListener('submit', function(e) {
        if (!fileInput.files[0]) {
            e.preventDefault();
            alert('Please select a file to upload.');
            return;
        }

        // Show upload progress
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="ri-loader-4-line me-1 spinner-border spinner-border-sm"></i>Uploading...';
        
        // Show progress bar
        const progress = document.querySelector('.upload-progress');
        progress.classList.remove('d-none');
        
        // Simulate progress (you can replace this with actual progress tracking)
        let progressValue = 0;
        const progressBar = progress.querySelector('.progress-bar');
        const interval = setInterval(() => {
            progressValue += Math.random() * 30;
            if (progressValue >= 90) {
                clearInterval(interval);
                progressValue = 90;
            }
            progressBar.style.width = progressValue + '%';
        }, 200);
    });
});
</script>
@endsection 