@extends('layouts.layout')
@section('title', 'Driver Bulk Upload')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Driver Bulk Upload</h4>
                <p class="text-muted mb-0">Upload multiple drivers at once using Excel/CSV file</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Bulk Upload</a></li>
                    <li class="breadcrumb-item active">Drivers</li>
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
                            <p class="text-muted small mb-2">Download our CSV template with sample data and required columns.</p>
                            <a href="{{ route('bulk-upload.drivers.template') }}" class="btn btn-outline-primary btn-sm">
                                <i class="ri-download-line me-1"></i>Download Template
                            </a>
                        </div>
                        
                        <div class="mb-3">
                            <h6>Step 2: Fill Data</h6>
                            <p class="text-muted small">Fill in your driver data according to the template format. Required fields are marked with *</p>
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
                                    <li>Supported formats: .csv</li>
                                    <li>Ensure valid email addresses</li>
                                    <li>License numbers must be unique</li>
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
                            <i class="ri-steering-2-line me-2"></i>Upload Drivers
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

                        <form action="{{ route('bulk-upload.drivers.upload') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label">Select Excel/CSV File</label>
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
                                    <input class="form-check-input" type="checkbox" id="skipDuplicates">
                                    <label class="form-check-label" for="skipDuplicates">
                                        Skip duplicate entries
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-primary" id="uploadBtn" disabled>
                                    <i class="ri-upload-line me-1"></i>Upload Drivers
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

        <!-- Recent Uploads -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-history-line me-2"></i>Recent Upload History
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>File Name</th>
                                <th>Total Records</th>
                                <th>Success</th>
                                <th>Failed</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="ri-inbox-line display-4 d-block mb-2"></i>
                                    No upload history available
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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
        .then(response => response.json())
        .then(data => {
            clearInterval(progressInterval);
            progressBar.style.width = '100%';
            progressText.textContent = '100%';
            
            setTimeout(() => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Upload failed: ' + (data.message || 'Unknown error'));
                    progressSection.classList.add('d-none');
                    uploadBtn.disabled = false;
                }
            }, 500);
        })
        .catch(error => {
            clearInterval(progressInterval);
            progressSection.classList.add('d-none');
            uploadBtn.disabled = false;
            
            // Fallback to regular form submission
            this.submit();
        });
    });
});
</script>
@endsection 