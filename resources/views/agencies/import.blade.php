@extends('layouts.layout')

@section('title', 'Import Agencies')

@section('content')
<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="d-flex align-items-end row">
                        <div class="col-sm-7">
                            <div class="card-body">
                                <h5 class="card-title text-primary mb-2">
                                    <i class="ri-file-upload-line me-2"></i>Import Agencies
                                </h5>
                                <p class="mb-3">
                                    Quickly add multiple agencies to your system by uploading a CSV file. Download the template below to get started.
                                </p>
                                <a href="{{ route('agencies.index') }}" class="btn btn-sm btn-outline-primary">
                                    <i class="ri-arrow-left-line me-1"></i>Back to Agencies List
                                </a>
                            </div>
                        </div>
                        <div class="col-sm-5 text-center text-sm-left">
                            <div class="card-body pb-0 px-0 px-md-4">
                                <i class="ri-file-list-3-line" style="font-size: 5rem; color: #65a30d;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Import Instructions -->
            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-label-primary">
                        <h5 class="mb-0">
                            <i class="ri-information-line me-2"></i>Import Instructions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-4">
                            <h6 class="alert-heading mb-2">
                                <i class="ri-lightbulb-line me-1"></i>Important Notes
                            </h6>
                            <ul class="mb-0 ps-3">
                                <li class="mb-1"><strong>ID Card Type</strong> is auto-populated based on the country selected</li>
                                <li class="mb-1"><strong>Card Number</strong> field is not imported for security</li>
                                <li><strong>Branches</strong> are not imported - only head office details</li>
                            </ul>
                        </div>

                        <h6 class="mb-3"><i class="ri-list-check me-2"></i>How to Import:</h6>
                        <ol class="ps-3">
                            <li class="mb-2">
                                <strong>Download Template</strong>
                                <p class="text-muted small mb-0">Click the button below to download the CSV template with sample data</p>
                            </li>
                            <li class="mb-2">
                                <strong>Fill in Your Data</strong>
                                <p class="text-muted small mb-0">Open the template in Excel/Google Sheets and enter your agency information</p>
                            </li>
                            <li class="mb-2">
                                <strong>Save as CSV</strong>
                                <p class="text-muted small mb-0">Ensure the file is saved in CSV format (comma-separated values)</p>
                            </li>
                            <li class="mb-2">
                                <strong>Upload File</strong>
                                <p class="text-muted small mb-0">Use the upload form to import your agencies</p>
                            </li>
                        </ol>

                        <div class="alert alert-warning small mb-0">
                            <strong><i class="ri-alert-line me-1"></i>Validation:</strong>
                            Duplicate emails will be skipped. Soft-deleted agencies with the same email will be restored and updated.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Import Form -->
            <div class="col-lg-8 mb-4">
                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong><i class="ri-checkbox-circle-line me-2"></i>Success!</strong>
                        {!! session('success') !!}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="ri-error-warning-line me-2"></i>Error!</strong>
                        {!! session('error') !!}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong><i class="ri-alert-line me-2"></i>Warning!</strong>
                        {!! session('warning') !!}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="ri-error-warning-line me-2"></i>Validation Errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Download Template Card -->
                <div class="card mb-4">
                    <div class="card-header bg-label-success">
                        <h5 class="mb-0">
                            <i class="ri-download-2-line me-2"></i>Step 1: Download Template
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">Download the CSV template file with predefined columns and sample data:</p>
                        
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Column Name</th>
                                        <th>Required</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>agency_name</code></td>
                                        <td><span class="badge bg-danger">Required</span></td>
                                        <td>Name of the agency</td>
                                    </tr>
                                    <tr>
                                        <td><code>email</code></td>
                                        <td><span class="badge bg-danger">Required</span></td>
                                        <td>Agency email (must be unique)</td>
                                    </tr>
                                    <tr>
                                        <td><code>phone</code></td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td>Contact phone number</td>
                                    </tr>
                                    <tr>
                                        <td><code>country</code></td>
                                        <td><span class="badge bg-danger">Required</span></td>
                                        <td>Country name (ID card type auto-populated)</td>
                                    </tr>
                                    <tr>
                                        <td><code>city</code></td>
                                        <td><span class="badge bg-danger">Required</span></td>
                                        <td>City name</td>
                                    </tr>
                                    <tr>
                                        <td><code>address</code></td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td>Full address</td>
                                    </tr>
                                    <tr>
                                        <td><code>postal_code</code></td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td>Postal/ZIP code</td>
                                    </tr>
                                    <tr>
                                        <td><code>contact_person</code></td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td>Primary contact person name</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <a href="{{ route('agencies.import.template') }}" class="btn btn-success">
                            <i class="ri-download-2-line me-2"></i>Download CSV Template
                        </a>
                    </div>
                </div>

                <!-- Upload Form Card -->
                <div class="card">
                    <div class="card-header bg-label-primary">
                        <h5 class="mb-0">
                            <i class="ri-upload-2-line me-2"></i>Step 2: Upload CSV File
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('agencies.import.upload') }}" method="POST" enctype="multipart/form-data" id="importForm">
                            @csrf
                            
                            <div class="mb-4">
                                <label for="file" class="form-label">Select CSV File</label>
                                <input type="file" 
                                       class="form-control @error('file') is-invalid @enderror" 
                                       id="file" 
                                       name="file" 
                                       accept=".csv,text/csv"
                                       required>
                                @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <i class="ri-information-line me-1"></i>
                                    Accepted formats: CSV, TXT (comma-separated). Maximum file size: 10MB
                                </div>
                            </div>

                            <div id="filePreview" class="alert alert-info d-none mb-4">
                                <h6 class="alert-heading mb-2">
                                    <i class="ri-file-text-line me-1"></i>File Selected:
                                </h6>
                                <p class="mb-1"><strong>Name:</strong> <span id="fileName"></span></p>
                                <p class="mb-0"><strong>Size:</strong> <span id="fileSize"></span></p>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="ri-upload-2-line me-2"></i>
                                    <span class="btn-text">Import Agencies</span>
                                    <span class="btn-loader d-none">
                                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                        Importing...
                                    </span>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('file').value = ''; document.getElementById('filePreview').classList.add('d-none');">
                                    <i class="ri-close-line me-1"></i>Clear
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Help Section -->
        <div class="row">
            <div class="col-12">
                <div class="card bg-label-info">
                    <div class="card-body">
                        <h6 class="mb-2">
                            <i class="ri-question-line me-2"></i>Need Help?
                        </h6>
                        <p class="mb-2">Common issues and solutions:</p>
                        <ul class="mb-0">
                            <li><strong>File format error:</strong> Make sure your file is saved as CSV (Comma delimited)</li>
                            <li><strong>Validation errors:</strong> Check that all required fields are filled and email addresses are valid</li>
                            <li><strong>Duplicate emails:</strong> Agencies with existing emails will be skipped automatically</li>
                            <li><strong>Large files:</strong> If you have many agencies, consider splitting into multiple files of 500 records each</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- / Content -->
</div>

<style>
.card {
    transition: box-shadow 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.table code {
    background-color: #f5f5f5;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.875rem;
}

#filePreview {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
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
            
            if (fileExtension !== 'csv' && !validTypes.includes(file.type)) {
                alert('Please select a valid CSV file.');
                fileInput.value = '';
                filePreview.classList.add('d-none');
                return;
            }

            // Validate file size (10MB max)
            const maxSize = 10 * 1024 * 1024; // 10MB in bytes
            if (file.size > maxSize) {
                alert('File size exceeds 10MB. Please select a smaller file.');
                fileInput.value = '';
                filePreview.classList.add('d-none');
                return;
            }

            // Display file info
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
            alert('Please select a CSV file to import.');
            return;
        }

        // Show loading state
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
        
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

    // Auto-hide alerts after 10 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert:not(.alert-info):not(.alert-warning)');
        alerts.forEach(function(alert) {
            if (alert.querySelector('.btn-close')) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        });
    }, 10000);
});
</script>
@endsection

