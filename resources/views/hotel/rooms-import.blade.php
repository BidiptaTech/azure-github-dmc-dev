@extends('layouts.layout')
@section('title', 'Import Hotel Rooms')
@section('content')

<style>
    /* Modern Header Styles */
    .gradient-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
    }

    .icon-wrapper {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: white;
    }

    .text-white-75 {
        color: rgba(255, 255, 255, 0.75);
    }

    .text-white-50 {
        color: rgba(255, 255, 255, 0.5);
    }

    /* Alert Box Styles */
    .alert-modern {
        border-radius: 12px;
        border: none;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }

    .alert-icon {
        font-size: 1.5rem;
        flex-shrink: 0;
        margin-top: 0.25rem;
    }

    .alert-content {
        flex: 1;
    }

    .alert-modern.alert-success {
        background: linear-gradient(135deg, #d4f4dd 0%, #e8f5e9 100%);
        border-left: 4px solid #28a745;
    }

    .alert-modern.alert-warning {
        background: linear-gradient(135deg, #fff4e5 0%, #fef9e7 100%);
        border-left: 4px solid #ffc107;
    }

    .alert-modern.alert-danger {
        background: linear-gradient(135deg, #ffe5e5 0%, #f8d7da 100%);
        border-left: 4px solid #dc3545;
    }

    .error-summary-list {
        margin-top: 1rem;
        padding-left: 0;
    }

    .error-summary-item {
        padding: 0.5rem;
        margin-bottom: 0.5rem;
        background: white;
        border-radius: 8px;
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
    }

    /* Instruction Box Styles */
    .instruction-box {
        background: linear-gradient(to bottom right, #f8f9fa, #e9ecef);
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    }

    .instruction-section {
        margin-bottom: 1.5rem;
    }

    .instruction-section-title {
        color: #495057;
        font-weight: 600;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        font-size: 1.1rem;
    }

    .instruction-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .instruction-list li {
        padding: 0.75rem 0;
        padding-left: 2rem;
        position: relative;
        line-height: 1.6;
    }

    .instruction-list li:before {
        content: "✓";
        position: absolute;
        left: 0;
        color: #28a745;
        font-weight: bold;
        font-size: 1.2rem;
    }

    /* Modern Table Styles */
    .modern-table {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .modern-table thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .modern-table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        padding: 1rem !important;
        border: none !important;
    }

    .modern-table tbody tr {
        transition: all 0.3s ease;
    }

    .modern-table tbody tr:hover {
        background-color: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .field-code {
        background: #f1f3f5;
        padding: 0.25rem 0.5rem;
        border-radius: 5px;
        font-family: 'Courier New', monospace;
        color: #495057;
        font-weight: 600;
    }

    /* Upload Section Styles */
    .upload-section {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }

    .file-upload-wrapper {
        position: relative;
        border: 3px dashed #667eea;
        border-radius: 15px;
        padding: 3rem 2rem;
        text-align: center;
        transition: all 0.3s ease;
        background: linear-gradient(to bottom, #f8f9fa, #ffffff);
        cursor: pointer;
    }

    .file-upload-wrapper:hover {
        border-color: #764ba2;
        background: linear-gradient(to bottom, #f0f2ff, #ffffff);
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.2);
    }

    .file-upload-wrapper.drag-over {
        background: linear-gradient(to bottom, #e7eaff, #ffffff);
        border-color: #764ba2;
        transform: scale(1.02);
    }

    .file-preview {
        margin-top: 1.5rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 12px;
        display: none;
    }

    .file-preview.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .file-info {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .file-icon {
        font-size: 2.5rem;
        color: #667eea;
    }

    .loading-btn {
        position: relative;
        overflow: hidden;
    }

    .loading-btn.loading {
        pointer-events: none;
        opacity: 0.8;
    }

    .loading-btn .spinner-border {
        width: 1rem;
        height: 1rem;
        border-width: 0.15rem;
    }

    /* Upload History Styles */
    .history-section {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    }

    .history-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e9ecef;
    }

    .history-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
    }

    .view-errors-btn {
        border-radius: 20px;
        padding: 0.4rem 1rem;
        font-size: 0.85rem;
        transition: all 0.3s ease;
    }

    .view-errors-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(220, 53, 69, 0.3);
    }

    /* Mobile Card Styles */
    .history-mobile-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        border-left: 4px solid #667eea;
        transition: all 0.3s ease;
    }

    .history-mobile-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .mobile-card-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f1f3f5;
    }

    .mobile-card-row:last-child {
        border-bottom: none;
    }

    .mobile-label {
        font-weight: 600;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .mobile-value {
        text-align: right;
        color: #495057;
        font-size: 0.9rem;
    }

    /* Error Modal Styles */
    .error-modal .modal-header {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
    }

    .error-modal .modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    }

    .error-item {
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 0.75rem;
        border-left: 3px solid #dc3545;
    }

    .error-item strong {
        color: #dc3545;
    }

    /* Modern Upload History Styles */
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

    .text-white-50 {
        color: rgba(255, 255, 255, 0.7) !important;
    }

    .badge.bg-light.bg-opacity-20 {
        background-color: rgba(255, 255, 255, 0.2) !important;
    }

    /* Upload History Table Styles */
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

    /* Badge Enhancements */
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

    .upload-row,
    .upload-card {
        animation: fadeInUp 0.3s ease-out;
    }

    /* Responsive Improvements */
    @media (max-width: 768px) {
        .card-header.bg-gradient-primary {
            padding: 1rem;
        }
        
        .upload-card {
            padding: 0.75rem !important;
        }
    }

    /* Modern Alert Boxes */
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

    /* Compact Hotel Image Styles */
    .hotel-image-container {
        flex-shrink: 0;
    }

    .hotel-thumbnail {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 12px;
        border: 3px solid #f1f5f9;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .hotel-thumbnail:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
        border-color: #667eea;
    }

    .hotel-thumbnail-placeholder {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Icon Badge */
    .icon-badge {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* Info Badge */
    .info-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.375rem 0.75rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.8rem;
        color: #64748b;
        transition: all 0.2s ease;
    }

    .info-badge:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .info-badge i {
        font-size: 0.9rem;
        color: #667eea;
    }

    /* Responsive adjustments for hotel info */
    @media (max-width: 768px) {
        .hotel-thumbnail {
            width: 90px;
            height: 90px;
        }

        .icon-badge {
            width: 32px;
            height: 32px;
        }

        .info-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
    }

    @media (max-width: 576px) {
        .hotel-thumbnail {
            width: 80px;
            height: 80px;
        }
    }
</style>

<div class="content-wrapper">
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
                                        <i class="ri-hotel-bed-line"></i>
                                    </div>
                                    <div>
                                        <h4 class="card-title mb-1 text-white">Import Hotel Rooms</h4>
                                        <h6 class="text-white-50 mb-0">Bulk Upload Room Prices</h6>
                                    </div>
                                </div>
                                <p class="text-white-75 mb-0">
                                    <i class="ri-information-line me-1"></i>Quickly update room prices by uploading a CSV file
                                </p>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('hotels.createroom', ['id' => $hotel->hotel_unique_id]) }}" 
                                   class="btn btn-outline-light btn-sm">
                                    <i class="ri-arrow-left-line me-1"></i>Back to Rooms
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
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        
                        <!-- Success/Warning/Error Messages -->
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

                        <!-- Hotel Information -->
                        <div class="mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <!-- Hotel Image Section -->
                                        <div class="hotel-image-container">
                                            @if($hotel->main_image)
                                                <img src="{{ $hotel->main_image }}" 
                                                     alt="{{ $hotel->name }}" 
                                                     class="hotel-thumbnail">
                                            @else
                                                <div class="hotel-thumbnail hotel-thumbnail-placeholder">
                                                    <i class="ri-hotel-line text-white" style="font-size: 2.5rem; opacity: 0.6;"></i>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Hotel Details Section -->
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-start justify-content-between">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <div class="icon-badge me-2">
                                                            <i class="ri-building-line text-white"></i>
                                                        </div>
                                                        <div>
                                                            <h5 class="mb-0 fw-bold">{{ $hotel->name }}</h5>
                                                            <p class="text-muted mb-0 small">
                                                                <i class="ri-map-pin-line me-1"></i>{{ $hotel->city ?? 'N/A' }}@if($hotel->country), {{ $hotel->country }}@endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Additional Hotel Info -->
                                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                                        @if($hotel->hotel_unique_id)
                                                            <div class="info-badge">
                                                                <i class="ri-fingerprint-line me-1"></i>
                                                                <span>{{ substr($hotel->hotel_unique_id, 0, 8) }}...</span>
                                                            </div>
                                                        @endif
                                                        
                                                        @if($hotel->address)
                                                            <div class="info-badge">
                                                                <i class="ri-map-2-line me-1"></i>
                                                                <span>{{ Str::limit($hotel->address, 35) }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <!-- Download Button -->
                                                <div class="ms-3">
                                                    @if($hotel && $hotel->hotel_unique_id)
                                                        <a href="{{ route('rooms.import.template', ['hotel_id' => $hotel->hotel_unique_id]) }}" 
                                                           class="btn btn-primary btn-sm">
                                                            <i class="ri-download-cloud-2-line me-1"></i>Download Template
                                                        </a>
                                                    @else
                                                        <button type="button" class="btn btn-secondary btn-sm" disabled>
                                                            <i class="ri-download-cloud-2-line me-1"></i>Template
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <div class="alert alert-info mb-0 mt-2 py-2 px-3" style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px;">
                                                <small class="mb-0 d-flex align-items-center">
                                                    <i class="ri-information-line me-1"></i>
                                                    <span>Download template with admin base rooms for this hotel</span>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="instruction-box">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="instruction-section">
                                        <h6 class="instruction-section-title">
                                            <i class="ri-lightbulb-line me-2 text-warning"></i>Getting Started
                                        </h6>
                                        <ul class="instruction-list">
                                            <li>Download template with admin base rooms for <strong>{{ $hotel->name }}</strong></li>
                                            <li>Update prices for Single/Double Weekday/Weekend</li>
                                            <li>Update meal options (breakfast, lunch, dinner)</li>
                                            <li>Save as CSV file</li>
                                            <li>Upload to <strong>create your own custom rooms</strong> with your prices</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="instruction-section">
                                        <h6 class="instruction-section-title">
                                            <i class="ri-alert-line me-2 text-danger"></i>Important Notes
                                        </h6>
                                        <ul class="instruction-list">
                                            <li><strong>Template shows admin base rooms only</strong></li>
                                            <li>Update prices, meal options, and number of rooms in CSV</li>
                                            <li><strong>DO NOT change: Hotel name, Room type, or Dimension</strong></li>
                                            <li>System will reject any changes to protected fields</li>
                                            <li>If breakfast/lunch/dinner = 1, type and price are mandatory</li>
                                            <li>Meal types must be exactly "Buffet" or "Set Menu"</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CSV Column Details -->
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
                                                    <th width="10%">Required</th>
                                                    <th width="35%">Description</th>
                                                    <th width="25%">Example/Values</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><code class="field-code">hotel_name</code></td>
                                                    <td><span class="badge bg-danger">Cannot Change</span></td>
                                                    <td>Hotel name (must match exactly - DO NOT MODIFY)</td>
                                                    <td><span class="text-muted">Hotel ABC</span></td>
                                                </tr>
                                                <tr>
                                                    <td><code class="field-code">room_type</code></td>
                                                    <td><span class="badge bg-danger">Cannot Change</span></td>
                                                    <td>Room category name (must match admin base room - DO NOT MODIFY)</td>
                                                    <td><span class="text-muted">Deluxe Double Room</span></td>
                                                </tr>
                                                <tr>
                                                    <td><code class="field-code">no_of_room</code></td>
                                                    <td><span class="badge bg-success">Can Edit</span></td>
                                                    <td>Number of rooms (you can modify this to your requirement)</td>
                                                    <td><span class="text-muted">10</span></td>
                                                </tr>
                                                <tr>
                                                    <td><code class="field-code">dimension</code></td>
                                                    <td><span class="badge bg-danger">Cannot Change</span></td>
                                                    <td>Room dimension (must match admin base room - DO NOT MODIFY)</td>
                                                    <td><span class="text-muted">25</span></td>
                                                </tr>
                                                <tr>
                                                    <td><code class="field-code">weekday_price</code></td>
                                                    <td><span class="badge bg-danger">Required</span></td>
                                                    <td>Single occupancy weekday price</td>
                                                    <td><span class="text-muted">100.00</span></td>
                                                </tr>
                                                <tr>
                                                    <td><code class="field-code">weekend_price</code></td>
                                                    <td><span class="badge bg-danger">Required</span></td>
                                                    <td>Single occupancy weekend price</td>
                                                    <td><span class="text-muted">120.00</span></td>
                                                </tr>
                                                <tr>
                                                    <td><code class="field-code">double_weekday_price</code></td>
                                                    <td><span class="badge bg-danger">Required</span></td>
                                                    <td>Double occupancy weekday price</td>
                                                    <td><span class="text-muted">150.00</span></td>
                                                </tr>
                                                <tr>
                                                    <td><code class="field-code">double_weekend_price</code></td>
                                                    <td><span class="badge bg-danger">Required</span></td>
                                                    <td>Double occupancy weekend price</td>
                                                    <td><span class="text-muted">180.00</span></td>
                                                </tr>
                                                <tr>
                                                    <td><code class="field-code">breakfast</code></td>
                                                    <td><span class="badge bg-warning">Optional</span></td>
                                                    <td>Breakfast included (1=Yes, 0=No)</td>
                                                    <td><span class="text-muted">1</span></td>
                                                </tr>
                                                <tr>
                                                    <td><code class="field-code">breakfast_type</code></td>
                                                    <td><span class="badge bg-info">Conditional</span></td>
                                                    <td>Required if breakfast=1</td>
                                                    <td><span class="text-muted">Buffet or Set Menu</span></td>
                                                </tr>
                                                <tr>
                                                    <td><code class="field-code">breakfast_price</code></td>
                                                    <td><span class="badge bg-info">Conditional</span></td>
                                                    <td>Required if breakfast=1</td>
                                                    <td><span class="text-muted">25.00</span></td>
                                                </tr>
                                                <tr>
                                                    <td><code class="field-code">lunch</code></td>
                                                    <td><span class="badge bg-warning">Optional</span></td>
                                                    <td>Lunch included (1=Yes, 0=No)</td>
                                                    <td><span class="text-muted">0</span></td>
                                                </tr>
                                                <tr>
                                                    <td><code class="field-code">lunch_type</code></td>
                                                    <td><span class="badge bg-info">Conditional</span></td>
                                                    <td>Required if lunch=1</td>
                                                    <td><span class="text-muted">Buffet or Set Menu</span></td>
                                                </tr>
                                                <tr>
                                                    <td><code class="field-code">lunch_price</code></td>
                                                    <td><span class="badge bg-info">Conditional</span></td>
                                                    <td>Required if lunch=1</td>
                                                    <td><span class="text-muted">30.00</span></td>
                                                </tr>
                                                <tr>
                                                    <td><code class="field-code">dinner</code></td>
                                                    <td><span class="badge bg-warning">Optional</span></td>
                                                    <td>Dinner included (1=Yes, 0=No)</td>
                                                    <td><span class="text-muted">1</span></td>
                                                </tr>
                                                <tr>
                                                    <td><code class="field-code">dinner_type</code></td>
                                                    <td><span class="badge bg-info">Conditional</span></td>
                                                    <td>Required if dinner=1</td>
                                                    <td><span class="text-muted">Buffet or Set Menu</span></td>
                                                </tr>
                                                <tr>
                                                    <td><code class="field-code">dinner_price</code></td>
                                                    <td><span class="badge bg-info">Conditional</span></td>
                                                    <td>Required if dinner=1</td>
                                                    <td><span class="text-muted">35.00</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Room Creation Notice -->
                        <div class="alert alert-success mt-4" role="alert">
                            <div class="d-flex align-items-start">
                                <i class="ri-information-line me-2 fs-4"></i>
                                <div>
                                    <strong>How Room Customization Works:</strong>
                                    <p class="mb-2 mt-2">The template contains <strong>admin base rooms only</strong> (created by administrator).</p>
                                    <p class="mb-2">When you upload your CSV with custom prices, the system will:</p>
                                    <ul class="mb-0">
                                        <li><strong>First Upload:</strong> Creates NEW room rows with your custom prices</li>
                                        <li><strong>Subsequent Uploads:</strong> Updates your existing rooms</li>
                                        <li>Admin base rooms remain unchanged</li>
                                        <li>You can set your own custom prices independent from admin pricing</li>
                                        <li>Your rooms will appear in the listing showing only <strong>YOUR customized rooms</strong></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Validation Warning -->
                        <div class="alert alert-warning mt-4" role="alert">
                            <div class="d-flex align-items-start">
                                <i class="ri-error-warning-line me-2 fs-4"></i>
                                <div>
                                    <strong>⚠️ Important Validation Rules:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li><strong>Hotel Name:</strong> Must exactly match - any changes will be rejected</li>
                                        <li><strong>Room Type:</strong> Must exactly match the admin base room - any changes will be rejected</li>
                                        <li><strong>Dimension:</strong> Must exactly match the admin base room - any changes will be rejected</li>
                                        <li>If you modify any protected field (<code>Hotel name</code>, <code>Room type</code>, <code>Dimension</code>), the system will show an error</li>
                                        <li>You CAN update: <strong>Number of rooms, prices, and meal options</strong></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Pro Tips Alert -->
                        <div class="alert alert-info mt-4" role="alert">
                            <div class="d-flex align-items-start">
                                <i class="ri-lightbulb-flash-line me-2 fs-4"></i>
                                <div>
                                    <strong>Pro Tips:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Always download the latest template before uploading</li>
                                        <li>Template shows admin base rooms - you'll create your own copies</li>
                                        <li><strong>Do NOT modify: Hotel name, Room type, or Dimension</strong></li>
                                        <li><strong>You CAN modify: Number of rooms, prices, and meal options</strong></li>
                                        <li>First upload creates new rooms, subsequent uploads update your existing rooms</li>
                                        <li>Keep the file format as CSV (Comma delimited)</li>
                                        <li>Check for errors in the upload history after importing</li>
                                    </ul>  
                                </div>
                            </div>
                        </div>

                        <!-- Upload Form -->
                        <div class="upload-section mt-4">
                            <h5 class="mb-4">
                                <i class="ri-upload-cloud-2-line me-2"></i>Upload CSV File
                            </h5>
                            <form action="{{ route('rooms.import.upload') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                                @csrf
                                <input type="hidden" name="hotel_id" value="{{ $hotel->hotel_unique_id }}">
                                <div class="file-upload-wrapper" id="fileUploadWrapper">
                                    <i class="ri-file-upload-line" style="font-size: 3rem; color: #667eea;"></i>
                                    <h5 class="mt-3 mb-2">Drag & Drop your CSV file here</h5>
                                    <p class="text-muted mb-3">or click to browse</p>
                                    <input type="file" name="file" id="csvFile" class="d-none" accept=".csv" required>
                                    <button type="button" class="btn btn-primary" onclick="document.getElementById('csvFile').click()">
                                        <i class="ri-folder-open-line me-2"></i>Choose File
                                    </button>
                                    <p class="text-muted mt-3 mb-0 small">Supported format: CSV (Max 10MB)</p>
                                </div>

                                <div class="file-preview" id="filePreview">
                                    <div class="file-info">
                                        <i class="ri-file-text-line file-icon"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1" id="fileName">No file selected</h6>
                                            <small class="text-muted" id="fileSize">0 KB</small>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile()">
                                            <i class="ri-close-line"></i> Remove
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-4 d-flex gap-3">
                                    <button type="submit" class="btn btn-success loading-btn" id="submitBtn">
                                        <i class="ri-upload-2-line me-2"></i>
                                        <span id="btnText">Upload & Import</span>
                                        <span class="spinner-border spinner-border-sm ms-2 d-none" id="btnSpinner"></span>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
                                        <i class="ri-refresh-line me-2"></i>Reset
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Upload History Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-gradient-primary text-white border-0">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <h5 class="card-title mb-0 text-white">
                                    <i class="ri-history-line me-2"></i>Upload History
                                    <small class="d-block text-white-50 fs-6 mt-1">
                                        <i class="ri-hotel-line me-1"></i>For {{ $hotel->name }}
                                    </small>
                                </h5>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light bg-opacity-20 text-white">
                                    <i class="ri-file-list-line me-1"></i>{{ $uploadHistory ? $uploadHistory->count() : 0 }} records
                                </span>
                            </div>
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
                                                            <div class="fw-medium text-dark">{{ $history->original_file_name ?? 'rooms_import.csv' }}</div>
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
                                                    <div class="fw-medium text-dark">{{ $history->original_file_name ?? 'rooms_import.csv' }}</div>
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
                                    <h6 class="text-muted mb-2">No Upload History for This Hotel</h6>
                                    <p class="text-muted small mb-0">
                                        You haven't uploaded any room data for <strong>{{ $hotel->name }}</strong> yet.<br>
                                        Upload history will appear here once you import rooms for this hotel.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('csvFile');
        const filePreview = document.getElementById('filePreview');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const fileUploadWrapper = document.getElementById('fileUploadWrapper');
        const uploadForm = document.getElementById('uploadForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');

        // File input change event
        fileInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                validateAndDisplayFile(file);
            }
        });

        // Drag and drop functionality
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileUploadWrapper.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            fileUploadWrapper.addEventListener(eventName, () => {
                fileUploadWrapper.classList.add('drag-over');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            fileUploadWrapper.addEventListener(eventName, () => {
                fileUploadWrapper.classList.remove('drag-over');
            });
        });

        fileUploadWrapper.addEventListener('drop', function(e) {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                validateAndDisplayFile(files[0]);
            }
        });

        // Validate and display file
        function validateAndDisplayFile(file) {
            // Validate file type
            if (!file.name.endsWith('.csv')) {
                alert('Please upload a CSV file');
                fileInput.value = '';
                return;
            }

            // Validate file size (10MB max)
            if (file.size > 10 * 1024 * 1024) {
                alert('File size must be less than 10MB');
                fileInput.value = '';
                return;
            }

            // Display file info
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            filePreview.classList.add('active');
        }

        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        // Remove file
        window.removeFile = function() {
            fileInput.value = '';
            filePreview.classList.remove('active');
            fileName.textContent = 'No file selected';
            fileSize.textContent = '0 KB';
        };

        // Form submission
        uploadForm.addEventListener('submit', function(e) {
            if (!fileInput.files || fileInput.files.length === 0) {
                e.preventDefault();
                alert('Please select a CSV file to upload');
                return;
            }

            // Show loading state
            submitBtn.classList.add('loading');
            btnText.textContent = 'Uploading...';
            btnSpinner.classList.remove('d-none');
            submitBtn.disabled = true;
        });

        // Auto-hide alerts after 10 seconds
        const alerts = document.querySelectorAll('.alert-modern');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 10000);
        });

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

        // Smooth scroll to errors if present
        if (document.querySelector('.modern-alert.alert-danger, .modern-alert.alert-warning')) {
            setTimeout(() => {
                document.querySelector('.modern-alert')?.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
            }, 300);
        }
    });
</script>

@endsection

