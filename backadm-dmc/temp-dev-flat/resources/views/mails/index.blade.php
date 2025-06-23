@extends('layouts.layout')
@section('title', 'Email Templates')

@section('content')
<style>
    /* Enhanced Simple Email Templates Design */
    .email-templates-container {
        padding: 1.5rem;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        min-height: 100vh;
    }
    
    .main-header {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
    }
    
    .main-title {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    /* Enhanced Navigation Tabs */
    .enhanced-nav-tabs {
        background: white;
        border-radius: 12px;
        padding: 6px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
    }
    
    .enhanced-nav-tabs .nav-link {
        border: none;
        background: transparent;
        color: #64748b;
        font-weight: 600;
        padding: 12px 20px;
        border-radius: 8px;
        margin: 0 3px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 0.9rem;
        position: relative;
        overflow: hidden;
    }
    
    .enhanced-nav-tabs .nav-link:hover {
        color: #3b82f6;
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        transform: translateY(-1px);
    }
    
    .enhanced-nav-tabs .nav-link.active {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
        box-shadow: 0 4px 14px 0 rgba(59, 130, 246, 0.4);
        transform: translateY(-1px);
    }
    
    .enhanced-nav-tabs .nav-link.active::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255,255,255,0.2), transparent);
        pointer-events: none;
    }
    
    /* Enhanced Template Cards */
    .template-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        height: 100%;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        position: relative;
        cursor: pointer;
    }
    
    .template-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(139, 92, 246, 0.05));
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }
    
    .template-card:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        transform: translateY(-8px) scale(1.02);
        border-color: #3b82f6;
    }
    
    .template-card:hover::before {
        opacity: 1;
    }
    
    .template-card:hover .template-header {
        background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
    }
    
    .template-card:hover .template-icon {
        transform: scale(1.1) rotate(5deg);
    }
    
    .template-card:hover .template-title {
        color: #3b82f6;
    }
    
    .template-card:hover .btn-preview {
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
    }
    
    /* Template Card Header */
    .template-header {
        height: 100px;
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .template-header::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 50%);
        animation: shimmer 4s ease-in-out infinite;
    }
    
    @keyframes shimmer {
        0%, 100% { transform: translate(-50%, -50%) rotate(0deg); }
        50% { transform: translate(-50%, -50%) rotate(180deg); }
    }
    
    .template-icon {
        font-size: 2.5rem;
        color: white;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 2;
        position: relative;
        text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    /* Card Content */
    .template-content {
        padding: 1.5rem;
        position: relative;
        z-index: 1;
    }
    
    .template-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }
    
    .badge-booking { 
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        color: #dc2626;
        border: 1px solid #fecaca;
    }
    
    .badge-tour { 
        background: linear-gradient(135deg, #f0fdfa, #ccfbf1);
        color: #059669;
        border: 1px solid #a7f3d0;
    }
    
    .badge-staff { 
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        color: #2563eb;
        border: 1px solid #bfdbfe;
    }
    
    .badge-customer { 
        background: linear-gradient(135deg, #fdf4ff, #f3e8ff);
        color: #9333ea;
        border: 1px solid #e9d5ff;
    }
    
    .badge-payment { 
        background: linear-gradient(135deg, #f0fdf4, #dcfce7);
        color: #16a34a;
        border: 1px solid #bbf7d0;
    }
    
    .template-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.75rem;
        line-height: 1.4;
        transition: all 0.3s ease;
    }
    
    .template-description {
        color: #64748b;
        font-size: 0.9rem;
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }
    
    /* Enhanced Preview Button */
    .action-buttons-container {
        display: flex;
        justify-content: center;
        padding-top: 1rem;
        border-top: 1px solid #f1f5f9;
    }
    
    .btn-preview {
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        cursor: pointer;
        font-size: 0.875rem;
        background: linear-gradient(135deg, #64748b, #475569);
        color: white;
        position: relative;
        overflow: hidden;
    }
    
    .btn-preview::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }
    
    .btn-preview:hover::before {
        left: 100%;
    }
    
    .btn-preview:hover {
        color: white;
        text-decoration: none;
    }
    
    /* Section Headers */
    .section-header {
        text-align: center;
        margin-bottom: 2.5rem;
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        position: relative;
        overflow: hidden;
    }
    
    .section-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    }
    
    .section-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.75rem;
        background: linear-gradient(135deg, #1e293b, #3b82f6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .section-subtitle {
        color: #64748b;
        font-size: 1rem;
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.6;
    }
    
    /* Enhanced Loader */
    .tab-loader {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(5px);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }
    
    .loader-content {
        text-align: center;
        background: white;
        padding: 2.5rem;
        border-radius: 16px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
    }
    
    .enhanced-spinner {
        width: 50px;
        height: 50px;
        border: 4px solid #f3f4f6;
        border-top: 4px solid;
        border-image: linear-gradient(135deg, #3b82f6, #8b5cf6) 1;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .loader-text {
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 600;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .email-templates-container {
            padding: 1rem;
        }
        
        .main-title {
            font-size: 1.5rem;
        }
        
        .section-title {
            font-size: 1.5rem;
        }
        
        .template-card:hover {
            transform: translateY(-4px) scale(1.01);
        }
        
        .section-header {
            padding: 1.5rem;
        }
    }
</style>

    <!-- Enhanced Loader -->
    <div class="tab-loader">
        <div class="loader-content">
            <div class="enhanced-spinner"></div>
            <div class="loader-text">Loading templates...</div>
        </div>
    </div>

    <div class="email-templates-container">
        <!-- Main Header -->
        <div class="main-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="main-title">Email Templates</h1>
                <a href="{{ route('mail.settings') }}" class="btn-preview">
                    <i class="ri-settings-3-line me-1"></i>Email Settings
                </a>
            </div>
        </div>

        <!-- Enhanced Navigation Tabs -->
        <ul class="nav enhanced-nav-tabs" role="tablist">
            <li class="nav-item">
                <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#all-tab" role="tab">
                    <i class="ri-apps-2-line me-1"></i>All Templates
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#booking-tab" role="tab">
                    <i class="ri-bookmark-3-line me-1"></i>Booking
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tour-tab" role="tab">
                    <i class="ri-route-line me-1"></i>Tour
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#staff-tab" role="tab">
                    <i class="ri-briefcase-4-line me-1"></i>Staff
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#customer-tab" role="tab">
                    <i class="ri-customer-service-2-line me-1"></i>Customer
                </button>
            </li>
        </ul>
        
        <div class="tab-content">
            <!-- All Templates Tab -->
            <div class="tab-pane fade show active" id="all-tab" role="tabpanel">
                <div class="section-header">
                    <h2 class="section-title">All Email Templates</h2>
                    <p class="section-subtitle">Browse all available email templates across different categories. Professional templates designed for optimal customer communication.</p>
                </div>
                    
                <div class="row g-4">
                    <!-- Booking Confirmation -->
                    <div class="col-lg-4 col-md-6">
                        <div class="template-card">
                            <div class="template-header">
                                <i class="ri-checkbox-circle-line template-icon"></i>
                            </div>
                            <div class="template-content">
                                <span class="template-badge badge-booking">Booking</span>
                                <h3 class="template-title">Booking Confirmation</h3>
                                <p class="template-description">Professional email template sent to customers when a booking is confirmed. Includes booking details and next steps.</p>
                                <div class="action-buttons-container">
                                    <a href="{{ route('mail.booking-confirmation') }}" class="btn-preview" target="_blank">
                                        <i class="ri-eye-line me-1"></i>Preview Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Booking Reminder -->
                    <div class="col-lg-4 col-md-6">
                        <div class="template-card">
                            <div class="template-header">
                                <i class="ri-alarm-line template-icon"></i>
                            </div>
                            <div class="template-content">
                                <span class="template-badge badge-booking">Booking</span>
                                <h3 class="template-title">Booking Reminder</h3>
                                <p class="template-description">Friendly reminder email sent to customers about upcoming bookings with important details.</p>
                                <div class="action-buttons-container">
                                    <a href="{{ route('mail.booking-reminder') }}" class="btn-preview" target="_blank">
                                        <i class="ri-eye-line me-1"></i>Preview Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Booking Cancellation -->
                    <div class="col-lg-4 col-md-6">
                        <div class="template-card">
                            <div class="template-header">
                                <i class="ri-close-circle-line template-icon"></i>
                            </div>
                            <div class="template-content">
                                <span class="template-badge badge-booking">Booking</span>
                                <h3 class="template-title">Booking Cancellation</h3>
                                <p class="template-description">Professional cancellation email with refund details and alternative options.</p>
                                <div class="action-buttons-container">
                                    <a href="{{ route('mail.booking-cancellation') }}" class="btn-preview" target="_blank">
                                        <i class="ri-eye-line me-1"></i>Preview Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Confirmation -->
                    <div class="col-lg-4 col-md-6">
                        <div class="template-card">
                            <div class="template-header">
                                <i class="ri-secure-payment-line template-icon"></i>
                            </div>
                            <div class="template-content">
                                <span class="template-badge badge-payment">Payment</span>
                                <h3 class="template-title">Payment Confirmation</h3>
                                <p class="template-description">Secure payment confirmation email with transaction details and receipt information.</p>
                                <div class="action-buttons-container">
                                    <a href="{{ route('mail.payment-confirmation') }}" class="btn-preview" target="_blank">
                                        <i class="ri-eye-line me-1"></i>Preview Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tour Itinerary -->
                    <div class="col-lg-4 col-md-6">
                        <div class="template-card">
                            <div class="template-header">
                                <i class="ri-map-pin-line template-icon"></i>
                            </div>
                            <div class="template-content">
                                <span class="template-badge badge-tour">Tour</span>
                                <h3 class="template-title">Tour Itinerary</h3>
                                <p class="template-description">Detailed tour itinerary email with schedule, inclusions, and travel information.</p>
                                <div class="action-buttons-container">
                                    <a href="{{ route('mail.tour-itinerary') }}" class="btn-preview" target="_blank">
                                        <i class="ri-eye-line me-1"></i>Preview Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Feedback Request -->
                    <div class="col-lg-4 col-md-6">
                        <div class="template-card">
                            <div class="template-header">
                                <i class="ri-chat-smile-3-line template-icon"></i>
                            </div>
                            <div class="template-content">
                                <span class="template-badge badge-tour">Tour</span>
                                <h3 class="template-title">Feedback Request</h3>
                                <p class="template-description">Engaging feedback request email with survey links and tour details.</p>
                                <div class="action-buttons-container">
                                    <a href="{{ route('mail.feedback-request') }}" class="btn-preview" target="_blank">
                                        <i class="ri-eye-line me-1"></i>Preview Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Job Assignment -->
                    <div class="col-lg-4 col-md-6">
                        <div class="template-card">
                            <div class="template-header">
                                <i class="ri-briefcase-line template-icon"></i>
                            </div>
                            <div class="template-content">
                                <span class="template-badge badge-staff">Staff</span>
                                <h3 class="template-title">Job Assignment</h3>
                                <p class="template-description">Professional job assignment email for guides and drivers with detailed information.</p>
                                <div class="action-buttons-container">
                                    <a href="{{ route('mail.job-assignment') }}" class="btn-preview" target="_blank">
                                        <i class="ri-eye-line me-1"></i>Preview Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Welcome Email -->
                    <div class="col-lg-4 col-md-6">
                        <div class="template-card">
                            <div class="template-header">
                                <i class="ri-hand-heart-line template-icon"></i>
                            </div>
                            <div class="template-content">
                                <span class="template-badge badge-customer">Customer</span>
                                <h3 class="template-title">Welcome Email</h3>
                                <p class="template-description">Warm welcome email for new users with account details and service introduction.</p>
                                <div class="action-buttons-container">
                                    <a href="{{ route('mail.welcome-email') }}" class="btn-preview" target="_blank">
                                        <i class="ri-eye-line me-1"></i>Preview Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Enquiry Response -->
                    <div class="col-lg-4 col-md-6">
                        <div class="template-card">
                            <div class="template-header">
                                <i class="ri-question-answer-line template-icon"></i>
                            </div>
                            <div class="template-content">
                                <span class="template-badge badge-customer">Customer</span>
                                <h3 class="template-title">Enquiry Response</h3>
                                <p class="template-description">Professional enquiry response email with detailed answers and contact information.</p>
                                <div class="action-buttons-container">
                                    <a href="{{ route('mail.enquiry-response') }}" class="btn-preview" target="_blank">
                                        <i class="ri-eye-line me-1"></i>Preview Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Booking Emails Tab -->
            <div class="tab-pane fade" id="booking-tab" role="tabpanel">
                <div class="section-header">
                    <h2 class="section-title">Booking Email Templates</h2>
                    <p class="section-subtitle">Professional email templates for the complete booking journey - from confirmation to cancellation.</p>
                </div>
                
                <div class="row g-4">
                    <!-- Booking Templates -->
                    <div class="col-lg-6">
                        <div class="template-card">
                            <div class="template-header">
                                <i class="ri-checkbox-circle-line template-icon"></i>
                            </div>
                            <div class="template-content">
                                <span class="template-badge badge-booking">Confirmation</span>
                                <h3 class="template-title">Booking Confirmation</h3>
                                <p class="template-description">Professional email template sent to customers when a booking is confirmed. Includes comprehensive booking details and clear next steps.</p>
                                <div class="action-buttons-container">
                                    <a href="{{ route('mail.booking-confirmation') }}" class="btn-preview" target="_blank">
                                        <i class="ri-eye-line me-1"></i>Preview Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="template-card">
                            <div class="template-header">
                                <i class="ri-alarm-line template-icon"></i>
                            </div>
                            <div class="template-content">
                                <span class="template-badge badge-booking">Reminder</span>
                                <h3 class="template-title">Booking Reminder</h3>
                                <p class="template-description">Friendly reminder email sent to customers about upcoming bookings with important preparation information.</p>
                                <div class="action-buttons-container">
                                    <a href="{{ route('mail.booking-reminder') }}" class="btn-preview" target="_blank">
                                        <i class="ri-eye-line me-1"></i>Preview Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="template-card">
                            <div class="template-header">
                                <i class="ri-close-circle-line template-icon"></i>
                            </div>
                            <div class="template-content">
                                <span class="template-badge badge-booking">Cancellation</span>
                                <h3 class="template-title">Booking Cancellation</h3>
                                <p class="template-description">Professional cancellation email with detailed refund information and alternative booking options.</p>
                                <div class="action-buttons-container">
                                    <a href="{{ route('mail.booking-cancellation') }}" class="btn-preview" target="_blank">
                                        <i class="ri-eye-line me-1"></i>Preview Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="template-card">
                            <div class="template-header">
                                <i class="ri-secure-payment-line template-icon"></i>
                            </div>
                            <div class="template-content">
                                <span class="template-badge badge-payment">Payment</span>
                                <h3 class="template-title">Payment Confirmation</h3>
                                <p class="template-description">Secure payment confirmation email with detailed transaction information and digital receipt.</p>
                                <div class="action-buttons-container">
                                    <a href="{{ route('mail.payment-confirmation') }}" class="btn-preview" target="_blank">
                                        <i class="ri-eye-line me-1"></i>Preview Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                
            <!-- Tour Emails Tab -->
            <div class="tab-pane fade" id="tour-tab" role="tabpanel">
                <div class="section-header">
                    <h2 class="section-title">Tour Email Templates</h2>
                    <p class="section-subtitle">Enhance tour experiences with detailed itineraries and engaging feedback requests.</p>
                </div>
                
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="template-card">
                            <div class="template-header">
                                <i class="ri-map-pin-line template-icon"></i>
                            </div>
                            <div class="template-content">
                                <span class="template-badge badge-tour">Itinerary</span>
                                <h3 class="template-title">Tour Itinerary</h3>
                                <p class="template-description">Comprehensive tour itinerary email with detailed day-by-day schedule and important travel information.</p>
                                <div class="action-buttons-container">
                                    <a href="{{ route('mail.tour-itinerary') }}" class="btn-preview" target="_blank">
                                        <i class="ri-eye-line me-1"></i>Preview Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="template-card">
                            <div class="template-header">
                                <i class="ri-chat-smile-3-line template-icon"></i>
                            </div>
                            <div class="template-content">
                                <span class="template-badge badge-tour">Feedback</span>
                                <h3 class="template-title">Feedback Request</h3>
                                <p class="template-description">Engaging feedback request email with survey links and explanation of how feedback improves services.</p>
                                <div class="action-buttons-container">
                                    <a href="{{ route('mail.feedback-request') }}" class="btn-preview" target="_blank">
                                        <i class="ri-eye-line me-1"></i>Preview Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Staff Emails Tab -->
            <div class="tab-pane fade" id="staff-tab" role="tabpanel">
                <div class="section-header">
                    <h2 class="section-title">Staff Email Templates</h2>
                    <p class="section-subtitle">Professional communication templates for guides, drivers, and team members.</p>
                </div>
                
                <div class="row g-4">
                    <div class="col-lg-8 mx-auto">
                        <div class="template-card">
                            <div class="template-header">
                                <i class="ri-briefcase-line template-icon"></i>
                            </div>
                            <div class="template-content">
                                <span class="template-badge badge-staff">Assignment</span>
                                <h3 class="template-title">Job Assignment</h3>
                                <p class="template-description">Professional job assignment email sent to guides and drivers when assigned to tours. Includes detailed assignment information and special instructions.</p>
                                <div class="action-buttons-container">
                                    <a href="{{ route('mail.job-assignment') }}" class="btn-preview" target="_blank">
                                        <i class="ri-eye-line me-1"></i>Preview Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Service Tab -->
            <div class="tab-pane fade" id="customer-tab" role="tabpanel">
                <div class="section-header">
                    <h2 class="section-title">Customer Service Templates</h2>
                    <p class="section-subtitle">Build lasting relationships with welcoming messages and professional enquiry responses.</p>
                </div>
                
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="template-card">
                            <div class="template-header">
                                <i class="ri-hand-heart-line template-icon"></i>
                            </div>
                            <div class="template-content">
                                <span class="template-badge badge-customer">Welcome</span>
                                <h3 class="template-title">Welcome Email</h3>
                                <p class="template-description">Warm and engaging welcome email for new users. Includes account verification and service introduction.</p>
                                <div class="action-buttons-container">
                                    <a href="{{ route('mail.welcome-email') }}" class="btn-preview" target="_blank">
                                        <i class="ri-eye-line me-1"></i>Preview Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="template-card">
                            <div class="template-header">
                                <i class="ri-question-answer-line template-icon"></i>
                            </div>
                            <div class="template-content">
                                <span class="template-badge badge-customer">Enquiry</span>
                                <h3 class="template-title">Enquiry Response</h3>
                                <p class="template-description">Professional and helpful enquiry response email with detailed answers and accessible contact details.</p>
                                <div class="action-buttons-container">
                                    <a href="{{ route('mail.enquiry-response') }}" class="btn-preview" target="_blank">
                                        <i class="ri-eye-line me-1"></i>Preview Template
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Enhanced tab switching with loading
        const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
        const loader = document.querySelector('.tab-loader');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Show enhanced loader
                loader.style.display = 'flex';
                
                // Hide loader after content loads
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 600);
            });
        });
        
        // Add click effect to template cards
        const templateCards = document.querySelectorAll('.template-card');
        templateCards.forEach(card => {
            card.addEventListener('click', function(e) {
                // Only trigger if not clicking on the preview button
                if (!e.target.closest('.btn-preview')) {
                    const previewBtn = this.querySelector('.btn-preview');
                    if (previewBtn) {
                        previewBtn.click();
                    }
                }
            });
        });
    });
</script>
@endsection