@extends('layouts.layout')
@extends('layouts.datatablecss')

@section('content')
    <div class="page-content">
    <div class="container-fluid py-4">
        <!-- Breadcrumb & Title -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
            <div class="mb-3 mb-md-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('tickets.index') }}">Tickets</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Ticket Details</li>
                    </ol>
                </nav>
                <h2 class="fw-bold text-primary mb-0">
                    <i class="fas fa-ticket-alt me-2"></i>Single Ticket Details
                </h2>
            </div>
            <div class="d-flex gap-2">
                @if(hasPermission('edit ticket'))
                    <a href="{{ route('tickets.edit', $ticket->id) }}" class="btn btn-primary px-3">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                @endif
                <a href="javascript:history.back()" class="btn btn-outline-secondary px-3">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
        
        <!-- Ticket Header Card -->
        <div class="card border-0 shadow-sm mb-4 position-relative overflow-hidden">
            <div class="ticket-badge position-absolute">Ticket</div>
            <div class="card-body p-4">
            <div class="row">
                    <div class="col-md-7">
                        <div class="d-flex align-items-center mb-3">
                            <div class="ticket-icon me-3">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <div>
                                <h3 class="text-dark mb-1 fw-bold">{{ $ticket->name }}</h3>
                                <p class="text-muted mb-0">Ticket ID: <span class="fw-semibold">{{ $ticket->ticket_id }}</span></p>
                            </div>
                        </div>
                        
                        <div class="status-display mt-3">
                                                @if($ticket->status == 1)
                                <span class="badge bg-success-subtle text-success px-3 py-2">
                                    <i class="fas fa-check-circle me-1"></i> Active
                                </span>
                                                @else
                                <span class="badge bg-danger-subtle text-danger px-3 py-2">
                                    <i class="fas fa-times-circle me-1"></i> Inactive
                                </span>
                                                @endif
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="ticket-timeline p-3 mt-3 mt-md-0 bg-light rounded-3">
                            <div class="timeline-item d-flex align-items-start mb-2">
                                <div class="timeline-icon me-2">
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
                                <div class="timeline-icon me-2">
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
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-bottom py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tag me-2 text-primary"></i>Pricing Information
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="pricing-card p-3 bg-light rounded-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-user-tie me-2 text-primary"></i>
                                        <h6 class="mb-0">Adult Price</h6>
                                    </div>
                                    <p class="display-6 mb-0 fw-bold text-primary">{{ $ticket->adult_price }}</p>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="pricing-card p-3 bg-light rounded-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-child me-2 text-success"></i>
                                        <h6 class="mb-0">Child Price</h6>
                                    </div>
                                    <p class="display-6 mb-0 fw-bold text-success">{{ $ticket->child_price ?? 'N/A' }}</p>
                                </div>
                            </div>
                            
                                <div class="col-md-6">
                                <div class="pricing-card p-3 bg-light rounded-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-user-clock me-2 text-info"></i>
                                        <h6 class="mb-0">Senior Citizen Price</h6>
                                    </div>
                                    <p class="display-6 mb-0 fw-bold text-info">{{ $ticket->senior_adult_price ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Description -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-bottom py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-align-left me-2 text-primary"></i>Description
                        </h5>
                                        </div>
                    <div class="card-body p-4">
                        <div class="description-content p-3 bg-light rounded-3">
                                            @if($ticket->description)
                                <p class="mb-0">{{ strip_tags($ticket->description) }}</p>
                                            @else
                                <div class="text-center text-muted p-4">
                                    <i class="fas fa-file-alt fs-2 mb-3"></i>
                                    <p class="mb-0">No description available</p>
                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
            <!-- Additional Information -->
            {{-- <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent border-bottom py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2 text-primary"></i>Additional Information
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="info-item d-flex mb-3">
                                    <div class="info-label text-muted" style="width: 140px">Created At:</div>
                                    <div class="info-value">
                                        <i class="far fa-calendar-alt me-1"></i>
                                        {{ $ticket->created_at->format('l, F d, Y') }}
                                        <div class="small text-muted">{{ $ticket->created_at->format('h:i A') }}</div>
                                    </div>
                                </div>
                                <div class="info-item d-flex mb-3">
                                    <div class="info-label text-muted" style="width: 140px">Last Updated:</div>
                                    <div class="info-value">
                                        <i class="far fa-calendar-check me-1"></i>
                                        {{ $ticket->updated_at->format('l, F d, Y') }}
                                        <div class="small text-muted">{{ $ticket->updated_at->format('h:i A') }}</div>
                                    </div>
                                </div>
                                        </div>
                            <div class="col-md-6">
                                                @if($ticket->created_by)
                                <div class="info-item d-flex mb-3">
                                    <div class="info-label text-muted" style="width: 140px">Created By:</div>
                                    <div class="info-value">
                                        <i class="far fa-user me-1"></i>
                                        {{ $ticket->created_by }}
                                    </div>
                                </div>
                                                @endif
                                                @if($ticket->updated_by)
                                <div class="info-item d-flex mb-3">
                                    <div class="info-label text-muted" style="width: 140px">Last Updated By:</div>
                                    <div class="info-value">
                                        <i class="far fa-user-edit me-1"></i>
                                        {{ $ticket->updated_by }}
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>
</div>

@push('styles')
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
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        box-shadow: 0 4px 6px rgba(13, 110, 253, 0.2);
    }
    
    /* Timeline */
    .timeline-icon {
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    /* Pricing Cards */
    .pricing-card {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }
    
    .pricing-card:nth-child(1) {
        border-left-color: #0d6efd;
    }
    
    .pricing-card:nth-child(2) {
        border-left-color: #198754;
    }
    
    .pricing-card:nth-child(3) {
        border-left-color: #0dcaf0;
    }
    
    .pricing-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    
    /* Description */
    .description-content {
        min-height: 150px;
        line-height: 1.6;
    }
    
    /* Info Items */
    .info-item {
        padding: 10px;
        border-radius: 5px;
        transition: all 0.3s ease;
    }
    
    .info-item:hover {
        background-color: #f8f9fa;
    }
    
    .badge {
        font-weight: 500;
    }
    
    /* Breadcrumb */
    .breadcrumb-item + .breadcrumb-item::before {
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        content: "\f054";
        font-size: 0.7rem;
    }
</style>
@endpush
@endsection 