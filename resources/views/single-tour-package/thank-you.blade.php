@extends('layouts.layout')
@section('title', 'Thank You - Tour Package Created')
@section('content')

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        
        <!-- Success Animation Section -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg">
                    <div class="card-body text-center py-5">
                        <div class="text-start mb-4">
                            <a
                                href="{{ route('dashboard') }}"
                                class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"
                                aria-label="Back to Dashboard"
                            >
                                <i class="ri-arrow-left-line" aria-hidden="true"></i>
                                <span>Back to Dashboard</span>
                            </a>
                        </div>

                        <!-- Success Animation -->
                        <div class="success-animation mb-4">
                            <div class="checkmark-circle">
                                <div class="checkmark"></div>
                            </div>
                        </div>

                        <!-- Thank You Message -->
                        <h1 class="display-4 text-success fw-bold mb-3">Thank You!</h1>
                        <h4 class="text-muted mb-4">Your Tour Package Has Been Successfully Created</h4>
                        
                        <!-- Tour Details -->
                        @if(session('tour_details'))
                            @php $tourDetails = session('tour_details'); @endphp
                            <div class="row justify-content-center mb-4">
                                <div class="col-md-8">
                                    <div class="card bg-light border-0">
                                        <div class="card-body">
                                            <h5 class="card-title text-primary mb-3">
                                                <i class="ri-map-pin-line me-2"></i>Tour Package Details
                                            </h5>
                                            <div class="row text-start">
                                                <div class="col-md-6">
                                                    <p class="mb-2">
                                                        <strong>Tour ID:</strong> 
                                                        <span class="badge bg-primary ms-2">{{ $tourDetails['display_id'] ?? 'N/A' }}</span>
                                                    </p>
                                                    <p class="mb-2">
                                                        <strong>Destination:</strong> {{ $tourDetails['destination'] ?? 'N/A' }}
                                                    </p>
                                                    
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-2">
                                                        <strong>Check-in:</strong> {{ $tourDetails['check_in_date'] ?? 'N/A' }}
                                                    </p>
                                                    <p class="mb-2">
                                                        <strong>Check-out:</strong> {{ $tourDetails['check_out_date'] ?? 'N/A' }}
                                                    </p>
                                                    <p class="mb-2">
                                                        <strong>Total Guests:</strong> 
                                                        <span class="badge bg-info">{{ $tourDetails['total_guests'] ?? 0 }} Guests</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Service Summary -->
                        @if(session('created_orders'))
                            @php $createdOrders = session('created_orders'); @endphp
                            <div class="row justify-content-center mb-4">
                                <div class="col-md-10">
                                    <h5 class="text-primary mb-3">
                                        <i class="ri-service-line me-2"></i>Services Successfully Booked
                                    </h5>
                                    <div class="row">
                                        @foreach($createdOrders as $order)
                                            <div class="col-md-4 mb-3">
                                                <div class="card border-success">
                                                    <div class="card-body text-center py-3">
                                                        @php
                                                            $icons = [
                                                                'hotel' => 'ri-hotel-line text-success',
                                                                'attraction' => 'ri-ticket-line text-danger', 
                                                                'guide' => 'ri-user-star-line text-info',
                                                                'restaurant' => 'ri-restaurant-line text-warning',
                                                                'transport' => 'ri-car-line text-primary',
                                                                'entry_port' => 'ri-login-circle-line text-success',
                                                                'exit_port' => 'ri-logout-circle-line text-danger'
                                                            ];
                                                            $icon = $icons[$order['type']] ?? 'ri-service-line';
                                                        @endphp
                                                        <i class="{{ $icon }} fs-2 mb-2"></i>
                                                        <h6 class="mb-1">{{ ucfirst($order['type']) }}</h6>
                                                        <small class="text-muted">{{ $order['data_count'] ?? 0 }} item(s) booked</small>
                                                        @if(isset($order['order_id']))
                                                            <div class="mt-1">
                                                                <span class="badge bg-success">Order #{{ $order['order_id'] }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <a
                                href="{{ route('single-tour-package.create') }}"
                                class="btn btn-primary btn-lg px-3"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="Create Another Tour Package"
                                aria-label="Create Another Tour Package"
                            >
                                <i class="ri-add-line"></i>
                            </a>

                            <a
                                href="{{ route('tour.itinerary.preview', ['encryptedTourId' => Crypt::encrypt($tourDetails['tour_id'])]) }}"
                                class="btn btn-outline-secondary btn-lg px-3"
                                target="_blank"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="Packaged Quotation"
                                aria-label="Packaged Quotation"
                            >
                                <i class="ri-file-list-3-line"></i>
                            </a>
                            <a
                                href="{{ route('tour.detailed-quotation.preview', ['encryptedTourId' => Crypt::encrypt($tourDetails['tour_id'])]) }}"
                                class="btn btn-outline-secondary btn-lg px-3"
                                target="_blank"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="Acco + Service Quotation"
                                aria-label="Acco + Service Quotation"
                            >
                                <i class="ri-file-text-line"></i>
                            </a>

                            <a
                                href="{{ route('tour.email.preview', ['encryptedTourId' => Crypt::encrypt($tourDetails['tour_id'])]) }}"
                                class="btn btn-outline-info btn-lg px-3"
                                target="_blank"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="Preview Email Template"
                                aria-label="Preview Email Template"
                            >
                                <i class="ri-mail-line"></i>
                            </a>
                        </div>

                        <!-- Additional Info -->
                        <div class="mt-5 pt-4 border-top">
                            <p class="text-muted mb-2">
                                <i class="ri-information-line me-2"></i>
                                Your tour package has been created and all service orders have been saved.
                            </p>
                            <p class="text-muted small">
                                You can view and manage your packages from the dashboard or create additional packages as needed.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
/* Success Animation Styles */
.success-animation {
    display: flex;
    justify-content: center;
    align-items: center;
}

.checkmark-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #28a745, #20c997);
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    animation: scaleIn 0.6s ease-out;
    box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);
}

.checkmark {
    width: 60px;
    height: 60px;
    position: relative;
}

.checkmark::before {
    content: '';
    position: absolute;
    width: 30px;
    height: 15px;
    border: 4px solid white;
    border-top: none;
    border-right: none;
    transform: rotate(-45deg);
    left: 15px;
    top: 20px;
    animation: checkmarkDraw 0.8s ease-out 0.3s both;
}

@keyframes scaleIn {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

@keyframes checkmarkDraw {
    0% {
        width: 0;
        height: 0;
    }
    50% {
        width: 15px;
        height: 0;
    }
    100% {
        width: 30px;
        height: 15px;
    }
}

/* Card Animations */
.card {
    animation: slideInUp 0.8s ease-out;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Button Hover Effects */
.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

/* Service Cards */
.card.border-success {
    border-width: 2px !important;
    transition: all 0.3s ease;
}

.card.border-success:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(40, 167, 69, 0.2);
}

/* Background Gradient */
.content-wrapper {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 100vh;
}

/* Text Animations */
.display-4 {
    animation: fadeInDown 1s ease-out 0.5s both;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Badge Animations */
.badge {
    animation: bounceIn 0.6s ease-out 1s both;
}

@keyframes bounceIn {
    0% {
        opacity: 0;
        transform: scale(0.3);
    }
    50% {
        opacity: 1;
        transform: scale(1.1);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}
</style>
@endsection
