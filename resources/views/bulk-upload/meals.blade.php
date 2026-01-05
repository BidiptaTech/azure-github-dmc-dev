@extends('layouts.layout')

@section('title', 'Meal Bulk Upload')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <h4 class="mb-1">Meal Bulk Upload</h4>
                <p class="mb-4">Upload meals for restaurants owned by your DMC.</p>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show enhanced-alert" role="alert">
            <div class="d-flex align-items-start">
                <div class="alert-icon me-3">
                    <i class="ri-check-double-line fs-4 text-success"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="alert-content">
                        {!! nl2br(str_replace(['**', '*'], ['<strong>', '</strong>'], e(session('success')))) !!}
                    </div>
                </div>
                <button type="button" class="btn-close ms-3" data-bs-dismiss="alert"></button>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('errors') && count(session('errors')) > 0)
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Upload Errors:</strong>
            <ul class="mb-0">
                @foreach(session('errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Select Restaurant for Meal Upload</h5>
                    </div>
                    <div class="card-body">
                        @if($restaurants->count() > 0)
                            <div class="row">
                                @foreach($restaurants as $restaurant)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 border shadow-sm">
                                        <div class="card-body d-flex flex-column">
                                            <h6 class="card-title text-primary">{{ $restaurant->name }}</h6>
                                            <p class="card-text text-muted mb-2">
                                                <i class="ri-restaurant-line me-1"></i>Restaurant ID: {{ $restaurant->restaurant_id }}
                                            </p>
                                            
                                                                         <div class="mb-3">
                                         <small class="text-muted d-block mb-1">Available Meals:</small>
                                         <div class="d-flex flex-wrap gap-1">
                                             @if(isset($restaurant->breakfast_available) && $restaurant->breakfast_available)
                                                 <span class="badge bg-success">Breakfast</span>
                                             @endif
                                             @if(isset($restaurant->lunch_available) && $restaurant->lunch_available)
                                                 <span class="badge bg-info">Lunch</span>
                                             @endif
                                             @if(isset($restaurant->dinner_available) && $restaurant->dinner_available)
                                                 <span class="badge bg-warning">Dinner</span>
                                             @endif
                                             @if(!isset($restaurant->breakfast_available) && !isset($restaurant->lunch_available) && !isset($restaurant->dinner_available))
                                                 <span class="badge bg-secondary">Meal info not available</span>
                                             @endif
                                         </div>
                                     </div>
                                    
                                    <div class="mt-auto">
                                        <div class="btn-group w-100" role="group">
                                            <a href="{{ route('bulk-upload.meals.template', $restaurant->restaurant_id) }}" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="ri-download-line me-1"></i>Download Template
                                            </a>
                                            <button type="button" class="btn btn-primary btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#uploadModal{{ $restaurant->restaurant_id }}">
                                                <i class="ri-upload-line me-1"></i>Upload Meals
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Modal for each restaurant -->
                        <div class="modal fade" id="uploadModal{{ $restaurant->restaurant_id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Upload Meals for {{ $restaurant->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('bulk-upload.meals.upload', $restaurant->restaurant_id) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Select CSV File</label>
                                                <input type="file" class="form-control" name="file" accept=".csv,.txt" required>
                                                <div class="form-text">
                                                    <small class="text-muted">
                                                        Please upload a CSV file with meal data. Maximum file size: 10MB.
                                                        <br>
                                                        <strong>Available meals for this restaurant:</strong>
                                                        @if(isset($restaurant->breakfast_available) && $restaurant->breakfast_available) Breakfast @endif
                                                        @if(isset($restaurant->lunch_available) && $restaurant->lunch_available) Lunch @endif
                                                        @if(isset($restaurant->dinner_available) && $restaurant->dinner_available) Dinner @endif
                                                    </small>
                                                </div>
                                            </div>
                                            
                                            <div class="alert alert-info">
                                                <h6><i class="ri-information-line me-1"></i>Upload Guidelines:</h6>
                                                <ul class="mb-0 small">
                                                    <li>Download the template first to see the required format</li>
                                                    <li>You can only upload meals for meal types that are available for this restaurant</li>
                                                    <li>For <strong>Buffet</strong>: Adult Price and Child Price are required</li>
                                                    <li>For <strong>Set Menu</strong>: Item Price and Item Type are required</li>
                                                    <li>Item Description is required for all meal types</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ri-upload-line me-1"></i>Upload Meals
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
                        <i class="ri-restaurant-line display-1 text-muted"></i>
                        <h5 class="mt-3">No Restaurants Found</h5>
                        <p class="text-muted">You don't have any restaurants assigned to your DMC yet.<br>
                        Please contact your administrator to assign restaurants to your DMC.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Uploads - Modern UI -->
        @php
            $uploadHistory = \App\Models\UploadHistory::getRecentHistory('meals', auth()->user()->userId, 10);
        @endphp
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
                                                    <small class="text-muted">Meal CSV File</small>
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
                            <p class="text-muted small mb-0">Your meal upload history will appear here once you start uploading files.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

<!-- Modern Upload History Styles - Matching Attraction Tickets Design -->
<style>
/* Color Variables */
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    --border-radius: 0.75rem;
    --border-radius-sm: 0.5rem;
}

/* Modern Upload History Styles */
.bg-gradient-primary {
    background: var(--primary-gradient);
}

.modern-table {
    border: none !important;
}

.modern-table thead th {
    background-color: #f8fafc;
    font-weight: 600;
    color: #475569;
    border: none !important;
    border-bottom: 2px solid #e2e8f0 !important;
    padding: 1rem 1.25rem;
}

.modern-table tbody tr {
    border: none !important;
    transition: all 0.2s ease;
}

.modern-table tbody tr:hover {
    background-color: #f8fafc;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.upload-row td {
    border: none !important;
    border-bottom: 1px solid #f1f5f9 !important;
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

.card.border-0.shadow-sm {
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06) !important;
    border-radius: 12px;
    overflow: hidden;
}

.card-header.bg-gradient-primary {
    padding: 1.25rem 1.5rem;
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

/* Enhanced Alert Styles */
.enhanced-alert {
    border: none;
    border-radius: 0.75rem;
    background: linear-gradient(135deg, #d1edff 0%, #e8f8f5 100%);
    border-left: 4px solid #28a745;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.1);
}

.enhanced-alert .alert-icon {
    background: rgba(40, 167, 69, 0.1);
    border-radius: 50%;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.enhanced-alert .alert-content {
    font-size: 0.95rem;
    line-height: 1.6;
    color: #155724;
}

.enhanced-alert .alert-content strong {
    color: #0f5132;
    font-weight: 600;
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
    
    .modern-table thead th {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
    }
}
</style>

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
</script>
@endsection 