@extends('layouts.layout')

@section('title', 'Attraction Tickets Bulk Upload')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Attraction Tickets Bulk Upload</h4>
                <p class="text-muted mb-0">Upload tickets for attractions owned by your DMC</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#">Bulk Upload</a></li>
                    <li class="breadcrumb-item active">Attraction Tickets</li>
                </ol>
            </nav>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show enhanced-alert" role="alert">
            <div class="d-flex align-items-start">
                <div class="alert-icon me-3">
                    <i class="ri-check-double-line fs-4 text-success"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="alert-content">
                        <strong>Upload Successful!</strong><br>
                        {!! nl2br(str_replace(['**', '*'], ['<strong>', '</strong>'], e(session('success')))) !!}
                    </div>
                </div>
                <button type="button" class="btn-close ms-3" data-bs-dismiss="alert"></button>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show enhanced-alert" role="alert">
            <div class="d-flex align-items-start">
                <div class="alert-icon me-3">
                    <i class="ri-error-warning-line fs-4 text-danger"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="alert-content">
                        <strong>Upload Failed!</strong><br>
                        {{ session('error') }}
                    </div>
                </div>
                <button type="button" class="btn-close ms-3" data-bs-dismiss="alert"></button>
            </div>
        </div>
        @endif

        @error('file')
        <div class="alert alert-danger alert-dismissible fade show enhanced-alert" role="alert">
            <div class="d-flex align-items-start">
                <div class="alert-icon me-3">
                    <i class="ri-error-warning-line fs-4 text-danger"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="alert-content">
                        <strong>File Upload Error:</strong><br>
                        {{ $message }}
                    </div>
                </div>
                <button type="button" class="btn-close ms-3" data-bs-dismiss="alert"></button>
            </div>
        </div>
        @enderror

        @if(session('errors') && count(session('errors')) > 0)
        <div class="alert alert-warning alert-dismissible fade show enhanced-alert" role="alert">
            <div class="d-flex align-items-start">
                <div class="alert-icon me-3">
                    <i class="ri-alert-line fs-4 text-warning"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="alert-content">
                        <strong>Upload Errors Found:</strong><br>
                        <div class="error-list mt-2" style="max-height: 200px; overflow-y: auto;">
                            @foreach(session('errors') as $index => $error)
                                <div class="error-item mb-1">
                                    <small class="text-muted">{{ $index + 1 }}.</small> {{ $error }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close ms-3" data-bs-dismiss="alert"></button>
            </div>
        </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Select Attraction for Ticket Upload</h5>
                    </div>
                    <div class="card-body">
                        @if($attractions->count() > 0)
                            <div class="row">
                                @foreach($attractions as $attraction)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 border shadow-sm">
                                        <div class="card-body d-flex flex-column">
                                            <h6 class="card-title text-primary">{{ $attraction->attraction_name ?? $attraction->name }}</h6>
                                            <p class="card-text text-muted mb-2">
                                                <i class="ri-map-pin-line me-1"></i>{{ $attraction->country ?? 'Location not set' }}, {{ $attraction->location ?? 'Country not set' }}
                                            </p>
                                            <p class="card-text text-muted mb-2">
                                                <i class="ri-ticket-line me-1"></i>Attraction ID: {{ $attraction->attraction_id }}
                                            </p>
                                            
                                            <div class="mb-3">
                                                <small class="text-muted d-block mb-1">Existing Tickets:</small>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @if($attraction->tickets_count > 0)
                                                        <span class="badge bg-success">{{ $attraction->tickets_count }} tickets</span>
                                                    @else
                                                        <span class="badge bg-secondary">No tickets yet</span>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <small class="text-muted d-block mb-1">Operating Hours:</small>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @if($attraction->morning_opening)
                                                        <span class="badge bg-info">Morning</span>
                                                    @endif
                                                    @if($attraction->afternoon_opening)
                                                        <span class="badge bg-warning">Afternoon</span>
                                                    @endif
                                                    @if($attraction->evening_opening)
                                                        <span class="badge bg-primary">Evening</span>
                                                    @endif
                                                    @if($attraction->night_opening)
                                                        <span class="badge bg-dark">Night</span>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <div class="mt-auto">
                                                <div class="btn-group w-100" role="group">
                                                    <a href="{{ route('bulk-upload.tickets.template', $attraction->attraction_id) }}" 
                                                       class="btn btn-outline-primary btn-sm">
                                                        <i class="ri-download-line me-1"></i>Download Template
                                                    </a>
                                                    <button type="button" class="btn btn-primary btn-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#uploadModal{{ $attraction->attraction_id }}">
                                                        <i class="ri-upload-line me-1"></i>Upload Tickets
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Upload Modal for each attraction -->
                                <div class="modal fade" id="uploadModal{{ $attraction->attraction_id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Upload Tickets for {{ $attraction->attraction_name ?? $attraction->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('bulk-upload.tickets.upload', $attraction->attraction_id) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Select CSV File</label>
                                                        <input type="file" class="form-control" name="file" accept=".csv,.txt" required>
                                                        <div class="form-text">
                                                            <small class="text-muted">
                                                                Please upload a CSV file with ticket data. Maximum file size: 10MB.
                                                                <br>
                                                                <strong>Location:</strong> {{ $attraction->attraction_location ?? 'Not set' }}, {{ $attraction->attraction_country ?? 'Not set' }}
                                                            </small>
                                                        </div>
                                                    </div>

                                                                                        <div class="alert alert-info">
                                        <h6><i class="ri-information-line me-1"></i>Upload Guidelines:</h6>
                                        <ul class="mb-0 small">
                                            <li>Download the template first to see the required format</li>
                                            <li>Each row represents one ticket for this attraction</li>
                                            <li><strong>All price fields are required:</strong> Child Price, Adult Price, Senior Citizen Price, Child Price NRI, Adult Price NRI, Senior Citizen Price NRI</li>
                                            <li><strong>All prices must be greater than 0</strong></li>
                                            <li>Important Notes and Terms & Conditions are required</li>
                                            <li>Status: 1 = Active, 0 = Inactive</li>
                                        </ul>
                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="ri-upload-line me-1"></i>Upload Tickets
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="ri-ticket-line display-1 text-muted"></i>
                                <h5 class="mt-3">No Attractions Found</h5>
                                <p class="text-muted">
                                    You don't have any attractions assigned to your DMC yet.<br>
                                    Please contact your administrator to assign attractions to your DMC.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Uploads - Modern UI -->
        <div class="card border-0 shadow-sm mt-4">
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
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Upload Time</th>
                                    <th>File Name</th>
                                    <th>Records</th>
                                    <th>Success</th>
                                    <th>Errors</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($uploadHistory as $history)
                                    <tr>
                                        <td>{{ $history->created_at->format('M d, Y H:i') }}</td>
                                        <td>{{ $history->file_name }}</td>
                                        <td>{{ $history->total_records }}</td>
                                        <td><span class="badge bg-success">{{ $history->success_count }}</span></td>
                                        <td><span class="badge bg-danger">{{ $history->error_count }}</span></td>
                                        <td>
                                            @if($history->success_count > 0 && $history->error_count == 0)
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($history->success_count > 0 && $history->error_count > 0)
                                                <span class="badge bg-warning">Partial Success</span>
                                            @else
                                                <span class="badge bg-danger">Failed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="ri-inbox-line display-1 text-muted"></i>
                        <h6 class="mt-3">No Upload History</h6>
                        <p class="text-muted">Your upload history will appear here once you start uploading files.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.enhanced-alert {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.enhanced-alert .alert-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.enhanced-alert.alert-success .alert-icon {
    background-color: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.2);
}

.enhanced-alert.alert-danger .alert-icon {
    background-color: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.enhanced-alert.alert-warning .alert-icon {
    background-color: rgba(245, 158, 11, 0.1);
    border: 1px solid rgba(245, 158, 11, 0.2);
}

.error-list {
    font-size: 0.875rem;
}

.error-item {
    padding: 0.25rem 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.error-item:last-child {
    border-bottom: none;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #696cff 0%, #5a67d8 100%);
}

.card.border-0.shadow-sm {
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06) !important;
    border-radius: 12px;
    overflow: hidden;
}

.card-header.bg-gradient-primary {
    padding: 1.25rem 1.5rem;
}
</style>
@endsection 