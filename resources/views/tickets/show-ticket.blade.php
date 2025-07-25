@extends('layouts.layout')
@extends('layouts.datatablecss')

@section('content')
    <div class="page-content">
        <div class="container-fluid py-4">
            <!-- Breadcrumb & Title -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                <div class="mb-3 mb-md-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none"><i class="fas fa-home me-1"></i>Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('tickets.index') }}" class="text-decoration-none"><i class="fas fa-ticket-alt me-1"></i>Tickets</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Ticket Details</li>
                        </ol>
                    </nav>
                    <h2 class="fw-bold text-primary mb-0 animate__animated animate__fadeIn">
                        <i class="fas fa-ticket-alt me-2"></i>Ticket Details
                    </h2>
                </div>
                <div class="d-flex gap-2 animate__animated animate__fadeIn">
                    @if(hasPermission('edit ticket'))
                        <a href="{{ route('tickets.edit', $ticket->id) }}" class="btn btn-primary px-3 btn-hover-effect">
                            <i class="fas fa-edit me-1"></i> Edit Ticket
                        </a>
                    @endif
                    <a href="javascript:history.back()" class="btn btn-outline-secondary px-3 btn-hover-effect">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
            
            <!-- Ticket Header Card -->
            <div class="card border-0 shadow-lg mb-4 position-relative overflow-hidden animate__animated animate__fadeIn">
                <div class="ticket-badge position-absolute">Ticket</div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="d-flex align-items-center mb-3">
                                <div class="ticket-icon me-3 animate__animated animate__pulse animate__infinite animate__slower">
                                    <i class="fas fa-ticket-alt"></i>
                                </div>
                                <div>
                                    <h3 class="text-dark mb-1 fw-bold">{{ $ticket->name }}</h3>
                                    <p class="text-muted mb-0">Ticket ID: <span class="fw-semibold text-primary">{{ $ticket->ticket_id }}</span></p>
                                </div>
                            </div>
                            
                            <div class="status-display mt-3">
                                @if($ticket->status == 1)
                                    <span class="badge bg-success-subtle text-success px-3 py-2 status-badge">
                                        <i class="fas fa-check-circle me-1"></i> Active
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger px-3 py-2 status-badge">
                                        <i class="fas fa-times-circle me-1"></i> Inactive
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="ticket-timeline p-3 mt-3 mt-md-0 bg-light rounded-4 border-start border-4 border-primary">
                                <div class="timeline-item d-flex align-items-start mb-3">
                                    <div class="timeline-icon me-2 pulse-effect">
                                        <i class="fas fa-plus-circle text-success"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <p class="mb-0 small text-muted">Created</p>
                                        <p class="mb-0 fw-semibold">
                                            {{ $ticket->created_at->format('l, F d, Y') }}
                                            <span class="text-muted small">at {{ $ticket->created_at->format('h:i A') }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="timeline-item d-flex align-items-start">
                                    <div class="timeline-icon me-2 pulse-effect">
                                        <i class="fas fa-edit text-info"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <p class="mb-0 small text-muted">Last Updated</p>
                                        <p class="mb-0 fw-semibold">
                                            {{ $ticket->updated_at->format('l, F d, Y') }}
                                            <span class="text-muted small">at {{ $ticket->updated_at->format('h:i A') }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row g-4">
                <!-- Pricing Information -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-lg h-100 animate__animated animate__fadeInLeft">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-tag me-2 text-primary"></i>Pricing Information
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="pricing-card p-3 bg-light rounded-4 pricing-card-hover">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="price-icon me-2">
                                                <i class="fas fa-user-tie text-primary"></i>
                                            </div>
                                            <h6 class="mb-0">Adult Price</h6>
                                        </div>
                                        <p class="display-6 mb-0 fw-bold text-primary">{{ $ticket->adult_price }}</p>
                                        <p class="text-muted small mb-0 mt-2">Standard rate for adults</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="pricing-card p-3 bg-light rounded-4 pricing-card-hover">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="price-icon me-2">
                                                <i class="fas fa-child text-success"></i>
                                            </div>
                                            <h6 class="mb-0">Child Price</h6>
                                        </div>
                                        <p class="display-6 mb-0 fw-bold text-success">{{ $ticket->child_price ?? 'N/A' }}</p>
                                        <p class="text-muted small mb-0 mt-2">Special rate for children</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="pricing-card p-3 bg-light rounded-4 pricing-card-hover">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="price-icon me-2">
                                                <i class="fas fa-user-clock text-info"></i>
                                            </div>
                                            <h6 class="mb-0">Senior Citizen Price</h6>
                                        </div>
                                        <p class="display-6 mb-0 fw-bold text-info">{{ $ticket->senior_adult_price ?? 'N/A' }}</p>
                                        <p class="text-muted small mb-0 mt-2">Discounted rate for seniors</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="pricing-card p-3 bg-light rounded-4 pricing-card-hover">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="price-icon me-2">
                                                <i class="fas fa-globe text-warning"></i>
                                            </div>
                                            <h6 class="mb-0">Foreigner Price</h6>
                                        </div>
                                        <p class="display-6 mb-0 fw-bold text-warning">{{ $ticket->adult_price_nri ?? 'N/A' }}</p>
                                        <p class="text-muted small mb-0 mt-2">Rate for international visitors</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-lg h-100 animate__animated animate__fadeInRight">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-align-left me-2 text-primary"></i>Important Notes
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="description-content p-3 bg-light rounded-4 border-start border-4 border-info">
                                @if($ticket->description)
                                    <div class="mb-0 description-text">{{ strip_tags($ticket->description) }}</div>
                                @else
                                    <div class="text-center text-muted p-4">
                                        <i class="fas fa-file-alt fs-2 mb-3"></i>
                                        <p class="mb-0">No description available</p>
                                    </div>
                                @endif
                            </div>
                            
                            @if($ticket->terms_conditions)
                            <div class="mt-4">
                                <h6 class="mb-3"><i class="fas fa-gavel me-2 text-secondary"></i>Terms & Conditions</h6>
                                <div class="p-3 bg-light rounded-4 border-start border-4 border-secondary">
                                    <div class="terms-text">{{ strip_tags($ticket->terms_conditions) }}</div>
                                </div>
                            </div>
                            @endif
                            
                            @if($ticket->remarks)
                            <div class="mt-4">
                                <h6 class="mb-3"><i class="fas fa-comment-alt me-2 text-warning"></i>Remarks</h6>
                                <div class="p-3 bg-light rounded-4 border-start border-4 border-warning">
                                    <div class="remarks-text">{{ strip_tags($ticket->remarks) }}</div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Additional Information -->
                <div class="col-12 animate__animated animate__fadeInUp">
                    <div class="card border-0 shadow-lg">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2 text-primary"></i>Additional Information
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-3 mb-4">
                                    <div class="info-card p-3 bg-light rounded-4 h-100">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="info-icon me-2">
                                                <i class="fas fa-calendar-alt text-primary"></i>
                                            </div>
                                            <h6 class="mb-0">Created</h6>
                                        </div>
                                        <p class="mb-0 fw-semibold">{{ $ticket->created_at->format('M d, Y') }}</p>
                                        <p class="text-muted small mb-0">{{ $ticket->created_at->format('h:i A') }}</p>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 mb-4">
                                    <div class="info-card p-3 bg-light rounded-4 h-100">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="info-icon me-2">
                                                <i class="fas fa-calendar-check text-success"></i>
                                            </div>
                                            <h6 class="mb-0">Last Updated</h6>
                                        </div>
                                        <p class="mb-0 fw-semibold">{{ $ticket->updated_at->format('M d, Y') }}</p>
                                        <p class="text-muted small mb-0">{{ $ticket->updated_at->format('h:i A') }}</p>
                                    </div>
                                </div>
                                
                                @if($ticket->created_by)
                                <div class="col-md-3 mb-4">
                                    <div class="info-card p-3 bg-light rounded-4 h-100">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="info-icon me-2">
                                                <i class="fas fa-user text-info"></i>
                                            </div>
                                            <h6 class="mb-0">Created By</h6>
                                        </div>
                                        <p class="mb-0 fw-semibold">{{ $ticket->created_by }}</p>
                                    </div>
                                </div>
                                @endif
                                
                                @if($ticket->updated_by)
                                <div class="col-md-3 mb-4">
                                    <div class="info-card p-3 bg-light rounded-4 h-100">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="info-icon me-2">
                                                <i class="fas fa-user-edit text-warning"></i>
                                            </div>
                                            <h6 class="mb-0">Updated By</h6>
                                        </div>
                                        <p class="mb-0 fw-semibold">{{ $ticket->updated_by }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<style>
    /* Ticket Badge */
    .ticket-badge {
        top: 0;
        right: 0;
        background: linear-gradient(135deg, #6610f2, #0d6efd);
        color: white;
        padding: 8px 16px;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        transform: rotate(45deg) translateX(30px) translateY(-20px);
        width: 150px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    
    /* Ticket Icon */
    .ticket-icon {
        background: linear-gradient(135deg, #0d6efd, #0dcaf0);
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
    }
    
    /* Timeline */
    .timeline-icon {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        border-radius: 50%;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }
    
    /* Pricing Cards */
    .pricing-card {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
        position: relative;
        overflow: hidden;
    }
    
    .pricing-card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    }
    
    .price-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    }
    
    /* Description */
    .description-content {
        min-height: 150px;
        line-height: 1.7;
    }
    
    .description-text, .terms-text, .remarks-text {
        font-size: 0.95rem;
        line-height: 1.7;
        color: #444;
    }
    
    /* Info Cards */
    .info-card {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }
    
    .info-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 12px rgba(0,0,0,0.08);
    }
    
    .info-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    }
    
    /* Status Badge */
    .status-badge {
        font-weight: 500;
        border-radius: 30px;
        box-shadow: 0 3px 6px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }
    
    .status-badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 10px rgba(0,0,0,0.1);
    }
    
    /* Breadcrumb */
    .breadcrumb-item + .breadcrumb-item::before {
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        content: "\f054";
        font-size: 0.7rem;
    }
    
    /* Button Hover Effect */
    .btn-hover-effect {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        z-index: 1;
    }
    
    .btn-hover-effect:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    /* Card Styles */
    .card {
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    
    .card:hover {
        box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
    }
    
    /* Animation Effects */
    .pulse-effect {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
        100% {
            transform: scale(1);
        }
    }
    
    /* Rounded Corners */
    .rounded-4 {
        border-radius: 12px;
    }
</style>
@endpush

@push('scripts')
<script>
    // Add scroll reveal effect to elements
    document.addEventListener('DOMContentLoaded', function() {
        // Add hover effect to cards
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
</script>
@endpush
@endsection 