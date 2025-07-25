@extends('layouts.layout')

@section('title', 'View Agency')

@section('content')
<style>
    /* Modern Card Styling */
    .modern-card {
        border: none;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        border-radius: 16px;
        transition: all 0.3s ease;
        overflow: hidden;
        background: white;
    }

    .modern-card:hover {
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
        transform: translateY(-3px);
    }

    /* Header Styling */
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2.5rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        opacity: 0.3;
    }

    .page-header > * {
        position: relative;
        z-index: 1;
    }

    /* Agency Header */
    .agency-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .agency-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(255,255,255,0.1) 25%, transparent 25%, transparent 75%, rgba(255,255,255,0.1) 75%, rgba(255,255,255,0.1)), 
                    linear-gradient(45deg, rgba(255,255,255,0.1) 25%, transparent 25%, transparent 75%, rgba(255,255,255,0.1) 75%, rgba(255,255,255,0.1));
        background-size: 20px 20px;
        background-position: 0 0, 10px 10px;
        opacity: 0.3;
    }

    .agency-header > * {
        position: relative;
        z-index: 1;
    }

    .agency-avatar {
        width: 80px;
        height: 80px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.8rem;
        color: #667eea;
        background: white;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        margin-bottom: 1rem;
    }

    /* Section Headers */
    .section-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem 2rem;
        border-radius: 12px 12px 0 0;
        margin: 0;
        font-weight: 600;
        position: relative;
        overflow: hidden;
    }

    .section-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(255,255,255,0.1) 25%, transparent 25%, transparent 75%, rgba(255,255,255,0.1) 75%, rgba(255,255,255,0.1)), 
                    linear-gradient(45deg, rgba(255,255,255,0.1) 25%, transparent 25%, transparent 75%, rgba(255,255,255,0.1) 75%, rgba(255,255,255,0.1));
        background-size: 20px 20px;
        background-position: 0 0, 10px 10px;
        opacity: 0.3;
    }

    .section-header > * {
        position: relative;
        z-index: 1;
    }

    .section-header i {
        font-size: 1.2rem;
        margin-right: 0.5rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .section-header .badge {
        background: rgba(255,255,255,0.2) !important;
        color: white !important;
        border: 1px solid rgba(255,255,255,0.3);
        backdrop-filter: blur(10px);
        font-weight: 500;
        padding: 0.5rem 1rem;
        border-radius: 20px;
    }

    .branch-header {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    /* Info Cards */
    .info-card {
        background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(248,249,250,0.9) 100%);
        border-radius: 12px;
        padding: 1.5rem;
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
        border: 1px solid rgba(255,255,255,0.2);
        height: 100%;
    }

    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    }

    .info-label {
        font-weight: 600;
        color: #667eea;
        margin-bottom: 0.5rem;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
    }

    .info-label i {
        margin-right: 0.5rem;
        font-size: 1rem;
    }

    .info-value {
        font-size: 1rem;
        color: #2d3748;
        font-weight: 500;
        word-break: break-word;
    }

    .info-value a {
        color: #667eea;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .info-value a:hover {
        color: #764ba2;
        text-decoration: underline;
    }

    /* Buttons */
    .btn-modern {
        border-radius: 10px;
        padding: 0.875rem 1.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        border: none;
        position: relative;
        overflow: hidden;
    }

    .btn-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .btn-modern:hover::before {
        left: 100%;
    }

    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    }

    .btn-primary.btn-modern {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .btn-secondary.btn-modern {
        background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    }

    /* Meta Information */
    .meta-info {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 2rem;
    }

    .meta-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .meta-item:last-child {
        border-bottom: none;
    }

    .meta-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-size: 1.2rem;
        color: white;
    }

    .meta-icon.status { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .meta-icon.offices { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .meta-icon.user { background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); }
    .meta-icon.time { background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); }

    .meta-content h6 {
        margin: 0;
        font-size: 0.9rem;
        color: #667eea;
        font-weight: 600;
    }

    .meta-content p {
        margin: 0;
        font-size: 0.95rem;
        color: #2d3748;
    }

    /* Badges */
    .modern-badge {
        border-radius: 20px;
        padding: 0.5rem 1rem;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 16px;
        border: 2px dashed #dee2e6;
    }

    .empty-state i {
        font-size: 4rem;
        color: #dee2e6;
        margin-bottom: 1rem;
    }

    .empty-state h6 {
        color: #6c757d;
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: #adb5bd;
        margin-bottom: 1.5rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .page-header, .agency-header {
            padding: 1.5rem;
            text-align: center;
        }
        
        .agency-avatar {
            margin: 0 auto 1rem;
        }
        
        .btn-modern {
            width: 100%;
            margin-bottom: 0.5rem;
        }
        
        .info-card {
            margin-bottom: 1rem;
        }
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in-up {
        animation: fadeInUp 0.6s ease-out;
    }

    /* Card Body */
    .card-body {
        background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(248,249,250,0.9) 100%);
        backdrop-filter: blur(10px);
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        
        <!-- Page Header -->
        <div class="page-header fade-in-up">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2">
                        <i class="ri-eye-line me-2"></i>
                        Agency Details
                    </h2>
                    <p class="mb-0 opacity-90">View complete agency information including head office and all branch details</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="{{ route('agencies.index') }}" class="btn btn-light btn-modern">
                        <i class="ri-arrow-left-line me-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Agency Header -->
        <div class="agency-header fade-in-up" style="animation-delay: 0.1s;">
            <div class="row align-items-center">
                <div class="col-md-2 text-center text-md-start">
                    <div class="agency-avatar">
                        {{ strtoupper(substr($agency->agency_name, 0, 2)) }}
                    </div>
                </div>
                <div class="col-md-7">
                    <h3 class="mb-2">{{ $agency->agency_name }}</h3>
                    <p class="mb-2 opacity-90">
                        <i class="ri-map-pin-line me-2"></i>
                        {{ $agency->city }}, {{ $agency->country }}
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="modern-badge bg-{{ $agency->status ? 'success' : 'danger' }} text-white">
                            <i class="ri-{{ $agency->status ? 'checkbox-circle' : 'close-circle' }}-line me-1"></i>
                            {{ $agency->status ? 'Active' : 'Inactive' }}
                        </span>
                        <span class="modern-badge bg-info text-white">
                            <i class="ri-building-2-line me-1"></i>
                            {{ $agency->total_branches }} {{ $agency->total_branches == 1 ? 'Office' : 'Offices' }}
                        </span>
                        <span class="modern-badge bg-primary text-white">
                            <i class="ri-price-tag-3-line me-1"></i>
                            ID: {{ $agency->agency_id }}
                        </span>
                    </div>
                </div>
                <div class="col-md-3 text-center text-md-end">
                    <a href="{{ route('agencies.edit', $agency->agency_id) }}" class="btn btn-light btn-modern me-2">
                        <i class="ri-pencil-line me-1"></i> Edit Agency
                    </a>
                </div>
            </div>
        </div>

        <!-- Head Office Information -->
        <div class="modern-card mb-4 fade-in-up" style="animation-delay: 0.2s;">
            <div class="section-header d-flex align-items-center justify-content-between">
                <div>
                    <i class="ri-building-line"></i>
                    Head Office Information
                </div>
                <span class="badge">Head Office</span>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <!-- Agency Name -->
                    <div class="col-lg-6 col-md-6 mb-4">
                        <div class="info-card">
                            <div class="info-label">
                                <i class="ri-building-2-line"></i>
                                Agency Name
                            </div>
                            <div class="info-value">{{ $agency->agency_name }}</div>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="col-lg-6 col-md-6 mb-4">
                        <div class="info-card">
                            <div class="info-label">
                                <i class="ri-mail-line"></i>
                                Email Address
                            </div>
                            <div class="info-value">
                                <a href="mailto:{{ $agency->email }}">{{ $agency->email }}</a>
                            </div>
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="col-lg-6 col-md-6 mb-4">
                        <div class="info-card">
                            <div class="info-label">
                                <i class="ri-phone-line"></i>
                                Phone Number
                            </div>
                            <div class="info-value">
                                <a href="tel:{{ $agency->phone }}">{{ $agency->phone }}</a>
                            </div>
                        </div>
                    </div>

                    <!-- Country -->
                    <div class="col-lg-6 col-md-6 mb-4">
                        <div class="info-card">
                            <div class="info-label">
                                <i class="ri-earth-line"></i>
                                Country
                            </div>
                            <div class="info-value">{{ $agency->country }}</div>
                        </div>
                    </div>

                    <!-- City -->
                    <div class="col-lg-6 col-md-6 mb-4">
                        <div class="info-card">
                            <div class="info-label">
                                <i class="ri-map-pin-line"></i>
                                City
                            </div>
                            <div class="info-value">{{ $agency->city }}</div>
                        </div>
                    </div>

                    <!-- Postal Code -->
                    <div class="col-lg-6 col-md-6 mb-4">
                        <div class="info-card">
                            <div class="info-label">
                                <i class="ri-map-2-line"></i>
                                Postal Code
                            </div>
                            <div class="info-value">{{ $agency->postal_code ?? 'Not specified' }}</div>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="col-12 mb-4">
                        <div class="info-card">
                            <div class="info-label">
                                <i class="ri-home-line"></i>
                                Complete Address
                            </div>
                            <div class="info-value">{{ $agency->address }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Branch Offices -->
        @if($agency->hasBranches())
            <div class="modern-card mb-4 fade-in-up" style="animation-delay: 0.3s;">
                <div class="section-header">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <div>
                            <i class="ri-building-2-line"></i>
                            Branch Offices
                        </div>
                        <span class="badge">{{ count($agency->branches) }} {{ count($agency->branches) == 1 ? 'Branch' : 'Branches' }}</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    @foreach($agency->branches as $index => $branch)
                        <div class="modern-card mb-4">
                            <div class="section-header branch-header d-flex align-items-center justify-content-between">
                                <div>
                                    <i class="ri-building-2-line"></i>
                                    Branch Office {{ $index + 1 }}
                                </div>
                                <span class="badge">Branch</span>
                            </div>
                            <div class="card-body p-4">
                                <div class="row">
                                    <!-- Email -->
                                    <div class="col-lg-6 col-md-6 mb-4">
                                        <div class="info-card">
                                            <div class="info-label">
                                                <i class="ri-mail-line"></i>
                                                Email Address
                                            </div>
                                            <div class="info-value">
                                                @if($branch['email'] ?? '')
                                                    <a href="mailto:{{ $branch['email'] }}">{{ $branch['email'] }}</a>
                                                @else
                                                    <span class="text-muted">Not specified</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Phone -->
                                    <div class="col-lg-6 col-md-6 mb-4">
                                        <div class="info-card">
                                            <div class="info-label">
                                                <i class="ri-phone-line"></i>
                                                Phone Number
                                            </div>
                                            <div class="info-value">
                                                @if($branch['phone'] ?? '')
                                                    <a href="tel:{{ $branch['phone'] }}">{{ $branch['phone'] }}</a>
                                                @else
                                                    <span class="text-muted">Not specified</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Country -->
                                    <div class="col-lg-6 col-md-6 mb-4">
                                        <div class="info-card">
                                            <div class="info-label">
                                                <i class="ri-earth-line"></i>
                                                Country
                                            </div>
                                            <div class="info-value">{{ $branch['country'] ?? 'Not specified' }}</div>
                                        </div>
                                    </div>

                                    <!-- City -->
                                    <div class="col-lg-6 col-md-6 mb-4">
                                        <div class="info-card">
                                            <div class="info-label">
                                                <i class="ri-map-pin-line"></i>
                                                City
                                            </div>
                                            <div class="info-value">{{ $branch['city'] ?? 'Not specified' }}</div>
                                        </div>
                                    </div>

                                    <!-- Postal Code -->
                                    <div class="col-lg-6 col-md-6 mb-4">
                                        <div class="info-card">
                                            <div class="info-label">
                                                <i class="ri-map-2-line"></i>
                                                Postal Code
                                            </div>
                                            <div class="info-value">{{ $branch['postal_code'] ?? 'Not specified' }}</div>
                                        </div>
                                    </div>

                                    <!-- Address -->
                                    <div class="col-12 mb-4">
                                        <div class="info-card">
                                            <div class="info-label">
                                                <i class="ri-home-line"></i>
                                                Complete Address
                                            </div>
                                            <div class="info-value">{{ $branch['address'] ?? 'Not specified' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="modern-card fade-in-up" style="animation-delay: 0.3s;">
                <div class="empty-state">
                    <i class="ri-building-2-line"></i>
                    <h6>No Branch Offices</h6>
                    <p>This agency currently operates only from the head office.</p>
                    <a href="{{ route('agencies.edit', $agency->agency_id) }}" class="btn btn-primary btn-modern">
                        <i class="ri-add-line me-1"></i> Add Branch Office
                    </a>
                </div>
            </div>
        @endif

        <!-- Agency Meta Information -->
        <div class="modern-card fade-in-up" style="animation-delay: 0.4s;">
            <div class="section-header">
                <div>
                    <i class="ri-information-line"></i>
                    Agency Meta Information
                </div>
            </div>
            <div class="card-body p-4">
                <div class="meta-info">
                    <div class="row">
                        <!-- Status -->
                        <div class="col-lg-6 col-md-6 mb-3">
                            <div class="meta-item">
                                <div class="meta-icon status">
                                    <i class="ri-{{ $agency->status ? 'checkbox-circle' : 'close-circle' }}-line"></i>
                                </div>
                                <div class="meta-content">
                                    <h6>Agency Status</h6>
                                    <p>{{ $agency->status ? 'Active and operational' : 'Currently inactive' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Total Offices -->
                        <div class="col-lg-6 col-md-6 mb-3">
                            <div class="meta-item">
                                <div class="meta-icon offices">
                                    <i class="ri-building-2-line"></i>
                                </div>
                                <div class="meta-content">
                                    <h6>Total Offices</h6>
                                    <p>{{ $agency->total_branches }} {{ $agency->total_branches == 1 ? 'office' : 'offices' }} (including head office)</p>
                                </div>
                            </div>
                        </div>

                        <!-- Created By -->
                        <div class="col-lg-6 col-md-6 mb-3">
                            <div class="meta-item">
                                <div class="meta-icon user">
                                    <i class="ri-user-line"></i>
                                </div>
                                <div class="meta-content">
                                    <h6>Created By</h6>
                                    <p>{{ $agency->creator ? $agency->creator->name : 'System Administrator' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Created At -->
                        <div class="col-lg-6 col-md-6 mb-3">
                            <div class="meta-item">
                                <div class="meta-icon time">
                                    <i class="ri-calendar-line"></i>
                                </div>
                                <div class="meta-content">
                                    <h6>Created On</h6>
                                    <p>{{ $agency->created_at->format('F j, Y \a\t g:i A') }}</p>
                                </div>
                            </div>
                        </div>

                        @if($agency->updater)
                        <!-- Updated By -->
                        <div class="col-lg-6 col-md-6 mb-3">
                            <div class="meta-item">
                                <div class="meta-icon user">
                                    <i class="ri-user-edit-line"></i>
                                </div>
                                <div class="meta-content">
                                    <h6>Last Updated By</h6>
                                    <p>{{ $agency->updater->name }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Updated At -->
                        <div class="col-lg-6 col-md-6 mb-3">
                            <div class="meta-item">
                                <div class="meta-icon time">
                                    <i class="ri-calendar-edit-line"></i>
                                </div>
                                <div class="meta-content">
                                    <h6>Last Updated On</h6>
                                    <p>{{ $agency->updated_at->format('F j, Y \a\t g:i A') }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="modern-card fade-in-up" style="animation-delay: 0.5s;">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="mb-1">Need to make changes?</h6>
                        <small class="text-muted">Edit agency details or manage branch offices</small>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <a href="{{ route('agencies.edit', $agency->agency_id) }}" class="btn btn-primary btn-modern me-2">
                            <i class="ri-pencil-line me-1"></i> Edit Agency
                        </a>
                        <a href="{{ route('agencies.index') }}" class="btn btn-secondary btn-modern">
                            <i class="ri-arrow-left-line me-1"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Animate elements on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-up');
            }
        });
    });

    document.querySelectorAll('.modern-card, .agency-header').forEach(el => {
        observer.observe(el);
    });

    // Enhanced hover effects for info cards
    $('.info-card').hover(
        function() {
            $(this).css('transform', 'translateY(-5px) scale(1.02)');
        },
        function() {
            $(this).css('transform', 'translateY(0) scale(1)');
        }
    );

    // Add ripple effect to buttons
    $('.btn-modern').on('click', function(e) {
        const ripple = $('<span class="ripple"></span>');
        const rect = this.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.css({
            width: size,
            height: size,
            left: x,
            top: y,
            position: 'absolute',
            borderRadius: '50%',
            background: 'rgba(255,255,255,0.6)',
            transform: 'scale(0)',
            animation: 'ripple 0.6s linear',
            pointerEvents: 'none'
        });
        
        $(this).append(ripple);
        
        setTimeout(() => {
            ripple.remove();
        }, 600);
    });

    // Add CSS for ripple animation
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
            .btn-modern {
                position: relative;
                overflow: hidden;
            }
        `)
        .appendTo('head');

    // Print functionality (optional)
    if (typeof window.print === 'function') {
        $(document).on('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    }

    // Initialize tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Auto-highlight important information
    $('.info-value a').each(function() {
        $(this).parent().addClass('text-primary');
    });

    // Add loading states for async operations
    $('a[href]').on('click', function() {
        if (!$(this).hasClass('no-loading')) {
            const originalText = $(this).html();
            $(this).html('<i class="ri-loader-line spinning me-1"></i> Loading...');
            
            setTimeout(() => {
                $(this).html(originalText);
            }, 2000);
        }
    });

    // Add spinning animation for loader
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .spinning {
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        `)
        .appendTo('head');
});
</script>
@endsection 