@extends('layouts.layout')
@section('title', 'Booking Cancellation')

@section('content')
    <style>
        /* Add Font Awesome for better icons */
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
        
        /* Modern Email Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.6;
        }
        
        .email-container {
            max-width: 680px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
        }
        
        /* Enhanced Header Styles */
        .email-header {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .email-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 25%, transparent 25%, transparent 75%, rgba(255,255,255,0.1) 75%), 
                        linear-gradient(45deg, rgba(255,255,255,0.1) 25%, transparent 25%, transparent 75%, rgba(255,255,255,0.1) 75%);
            background-size: 20px 20px;
            background-position: 0 0, 10px 10px;
            animation: subtle-move 20s linear infinite;
        }
        
        @keyframes subtle-move {
            0% { transform: translateX(0); }
            100% { transform: translateX(20px); }
        }
        
        .logo-container {
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
        }
        
        .logo {
            max-width: 150px;
            border-radius: 12px;
            border: 4px solid rgba(255,255,255,0.9);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .cancellation-title {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        
        .cancellation-title i {
            font-size: 28px;
            animation: shake 2s infinite;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .cancellation-subtitle {
            margin-top: 8px;
            font-size: 16px;
            opacity: 0.95;
            position: relative;
            z-index: 2;
        }
        
        .cancellation-badge {
            background-color: rgba(255,255,255,0.2);
            border-radius: 50px;
            padding: 8px 20px;
            margin-top: 20px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 2px solid rgba(255,255,255,0.3);
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 2;
        }
        
        .cancellation-badge i {
            font-size: 14px;
        }
        
        /* Enhanced Body Styles */
        .email-body {
            padding: 40px;
        }
        
        .greeting {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 25px;
            color: #1e293b;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .intro-text {
            margin-bottom: 35px;
            font-size: 16px;
            text-align: center;
            color: #64748b;
            line-height: 1.7;
        }
        
        /* Enhanced Cancellation Summary Box */
        .cancellation-summary {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 35px;
            border: 2px solid #fca5a5;
            box-shadow: 0 4px 6px rgba(239, 68, 68, 0.1);
        }
        
        .summary-title {
            font-size: 20px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 25px;
            color: #dc2626;
            padding-bottom: 12px;
            border-bottom: 2px solid #fca5a5;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .summary-title i {
            color: #dc2626;
            font-size: 20px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .summary-item {
            background: white;
            padding: 16px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid #f3f4f6;
            transition: transform 0.2s ease;
        }
        
        .summary-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .summary-label {
            font-weight: 600;
            color: #64748b;
            font-size: 14px;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .summary-label i {
            color: #dc2626;
            font-size: 12px;
        }
        
        .summary-value {
            color: #1e293b;
            font-size: 16px;
            font-weight: 600;
        }
        
        .amount-highlight {
            color: #dc2626;
            font-weight: 700;
        }
        
        /* Enhanced Refund Box */
        .refund-box {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.1);
            border: 2px solid #a7f3d0;
            margin-bottom: 35px;
            position: relative;
        }
        
        .refund-box::before {
            content: '\f155';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            color: rgba(5, 150, 105, 0.3);
        }
        
        .refund-header {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
            padding: 16px 25px;
            font-weight: 700;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .refund-header i {
            font-size: 18px;
        }
        
        .refund-content {
            padding: 25px;
        }
        
        .refund-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid #d1fae5;
        }
        
        .refund-row:last-child {
            border-bottom: none;
        }
        
        .refund-label {
            font-weight: 600;
            color: #065f46;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .refund-label i {
            color: #059669;
            font-size: 14px;
        }
        
        .refund-value {
            font-weight: 600;
            color: #1e293b;
        }
        
        .refund-footer {
            background: linear-gradient(135deg, #a7f3d0 0%, #6ee7b7 100%);
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #10b981;
            font-weight: 700;
            color: #065f46;
            font-size: 18px;
        }
        
        /* Enhanced Section Styles */
        .section-title {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 25px 0;
            color: #dc2626;
            position: relative;
            padding-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .section-title::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 80px;
            height: 3px;
            background: linear-gradient(to right, #dc2626, #ef4444);
            border-radius: 3px;
        }
        
        .section-title i {
            color: #dc2626;
            font-size: 22px;
        }
        
        /* Enhanced Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 700;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
            border: 1px solid #f59e0b;
        }
        
        .status-badge i {
            font-size: 12px;
        }
        
        /* Enhanced Info Box */
        .info-box {
            background: linear-gradient(135deg, #f8faff 0%, #f1f5f9 100%);
            border-radius: 16px;
            padding: 25px 30px;
            margin-bottom: 35px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            position: relative;
        }
        
        .info-box::before {
            content: '\f05a';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            color: rgba(99, 102, 241, 0.3);
        }
        
        .info-title {
            font-size: 18px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 15px;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-title i {
            color: #6366f1;
            font-size: 18px;
        }
        
        .info-box p {
            margin-bottom: 12px;
            color: #64748b;
            line-height: 1.6;
        }
        
        .info-box p:last-child {
            margin-bottom: 0;
        }
        
        /* Enhanced CTA Button */
        .cta-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-align: center;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            text-decoration: none;
            padding: 16px 30px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            margin: 35px auto;
            max-width: 320px;
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.3);
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .cta-button i {
            font-size: 18px;
        }
        
        .cta-button:hover {
            background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
            transform: translateY(-2px);
        }
        
        /* Enhanced Contact Info */
        .contact-info {
            background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
            border-radius: 16px;
            padding: 25px;
            margin-top: 35px;
            border: 1px solid #e5e7eb;
        }
        
        .contact-title {
            font-size: 18px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 20px;
            color: #374151;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .contact-title i {
            color: #dc2626;
            font-size: 20px;
        }
        
        .contact-methods {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .contact-method {
            background-color: white;
            border-radius: 12px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1px solid #f3f4f6;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .contact-method:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .method-icon {
            margin-right: 12px;
            color: #dc2626;
            font-weight: bold;
            font-size: 18px;
            width: 24px;
            text-align: center;
        }
        
        .method-text {
            font-size: 14px;
            font-weight: 500;
        }
        
        /* Enhanced Footer Styles */
        .email-footer {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            padding: 30px;
            text-align: center;
            font-size: 14px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        
        .footer-links {
            margin: 20px 0;
        }
        
        .footer-link {
            color: #dc2626;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 500;
            transition: color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .footer-link:hover {
            color: #b91c1c;
        }
        
        .footer-link i {
            font-size: 12px;
        }
        
        .social-links {
            margin: 25px 0;
        }
        
        .social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 8px;
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
            color: white;
            border-radius: 12px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(220, 38, 38, 0.3);
        }
        
        .social-link:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 6px 12px rgba(220, 38, 38, 0.4);
        }
        
        .social-link i {
            font-size: 18px;
        }
        
        /* Specific social media colors on hover */
        .social-link.facebook:hover {
            background: #1877f2;
        }
        
        .social-link.twitter:hover {
            background: #1da1f2;
        }
        
        .social-link.instagram:hover {
            background: linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%);
        }
        
        .social-link.linkedin:hover {
            background: #0077b5;
        }
        
        .copyright {
            margin-top: 20px;
            font-size: 13px;
            opacity: 0.8;
        }
        
        /* Enhanced Responsive Styles */
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 12px;
            }
            
            .email-body {
                padding: 25px 20px;
            }
            
            .summary-grid {
                grid-template-columns: 1fr;
            }
            
            .contact-methods {
                grid-template-columns: 1fr;
            }
            
            .cancellation-title {
                font-size: 24px;
                flex-direction: column;
                gap: 8px;
            }
            
            .greeting {
                flex-direction: column;
                gap: 8px;
            }
            
            .cta-button {
                font-size: 14px;
                padding: 14px 25px;
                max-width: 280px;
            }
            
            .refund-footer {
                flex-direction: column;
                gap: 8px;
                text-align: center;
            }
        }
    </style>

    <div class="email-container">
        <!-- Enhanced Email Header -->
        <div class="email-header">
            <div class="logo-container">
                @php
                    $logoSetting = \App\Helpers\CommonHelper::masterSettingsName('logo');
                    $nameSetting = \App\Helpers\CommonHelper::masterSettingsName('name');
                    
                    $logo = $logoSetting['master_value'] ?? '';
                    $companyName = $nameSetting['master_value'] ?? config('app.name');
                @endphp
                <img src="{{ $logo }}" alt="{{ $companyName }}" class="logo">
            </div>
            <h1 class="cancellation-title">
                <i class="fas fa-times-circle"></i> Booking Cancellation
            </h1>
            <p class="cancellation-subtitle">Your booking has been cancelled</p>
            <div class="cancellation-badge">
                <i class="fas fa-ticket-alt"></i> #{{ $booking->booking_id }}
            </div>
        </div>
        
        <!-- Enhanced Email Body -->
        <div class="email-body">
            <p class="greeting">
                Dear {{ $booking->customer_name }}, <i class="fas fa-hand-holding-heart"></i>
            </p>
            
            <p class="intro-text">
                We're confirming that your booking has been cancelled as requested. Please find the details of the cancellation below. We're sorry to see you go and hope to serve you again in the future.
            </p>
            
            <!-- Enhanced Cancellation Summary Box -->
            <div class="cancellation-summary">
                <h3 class="summary-title">
                    <i class="fas fa-info-circle"></i> Cancellation Details
                </h3>
                <div class="summary-grid">
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-hashtag"></i> Booking ID
                        </span>
                        <span class="summary-value">{{ $booking->booking_id }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-route"></i> Tour Name
                        </span>
                        <span class="summary-value">{{ $booking->tour_name ?? $booking->type ?? 'Tour Package' }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-calendar-plus"></i> Original Booking Date
                        </span>
                        <span class="summary-value">{{ \Carbon\Carbon::parse($booking->booking_date ?? now())->format('jS M Y') }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-calendar-times"></i> Cancellation Date
                        </span>
                        <span class="summary-value">{{ \Carbon\Carbon::parse($booking->cancellation_date ?? now())->format('jS M Y') }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-comment-alt"></i> Cancellation Reason
                        </span>
                        <span class="summary-value">{{ $booking->cancellation_reason ?? 'Customer Request' }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-user"></i> Cancelled By
                        </span>
                        <span class="summary-value">{{ $booking->cancelled_by ?? 'Customer' }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Refund Section -->
            <div class="section-title">
                <i class="fas fa-money-bill-wave"></i> Refund Information
            </div>
            <div class="refund-box">
                <div class="refund-header">
                    <i class="fas fa-credit-card"></i>
                    <span>Refund Details</span>
                </div>
                <div class="refund-content">
                    <div class="refund-row">
                        <div class="refund-label">
                            <i class="fas fa-dollar-sign"></i> Refund Amount
                        </div>
                        <div class="refund-value">{{ $booking->currency ?? 'SGD' }} {{ number_format($booking->refund_amount ?? $booking->total_price ?? 0, 2) }}</div>
                    </div>
                    <div class="refund-row">
                        <div class="refund-label">
                            <i class="fas fa-info-circle"></i> Refund Status
                        </div>
                        <div>
                            <span class="status-badge">
                                <i class="fas fa-clock"></i> {{ $booking->refund_status ?? 'Processing' }}
                            </span>
                        </div>
                    </div>
                    <div class="refund-row">
                        <div class="refund-label">
                            <i class="fas fa-calendar-alt"></i> Expected Processing Time
                        </div>
                        <div class="refund-value">5-7 business days</div>
                    </div>
                    <div class="refund-row">
                        <div class="refund-label">
                            <i class="fas fa-credit-card"></i> Refund Method
                        </div>
                        <div class="refund-value">Original Payment Method</div>
                    </div>
                </div>
                <div class="refund-footer">
                    <div>
                        <i class="fas fa-coins"></i> Total Refund Amount
                    </div>
                    <div>{{ $booking->currency ?? 'SGD' }} {{ number_format($booking->refund_amount ?? $booking->total_price ?? 0, 2) }}</div>
                </div>
            </div>
            
            <!-- Enhanced Information Box -->
            <div class="info-box">
                <h4 class="info-title">
                    <i class="fas fa-question-circle"></i> What happens next?
                </h4>
                <p>Your refund will be processed according to our refund policy. The funds will be returned to your original payment method within 5-7 business days.</p>
                <p>You will receive a confirmation email once the refund has been processed. If you have any questions about your refund or would like to book another tour in the future, please don't hesitate to contact our customer service team.</p>
                <p>We value your feedback and would appreciate hearing about your experience to help us improve our services.</p>
            </div>
            
            <div style="text-align: center; margin-bottom: 25px;">
                <p style="font-size: 16px; color: #1e293b; font-weight: 600; margin-bottom: 8px;">
                    <i class="fas fa-heart" style="color: #dc2626;"></i> We're sorry you had to cancel your booking.
                </p>
                
                <p style="font-size: 15px; color: #64748b; margin-bottom: 20px;">
                    We hope to have the opportunity to serve you in the future and provide you with an amazing experience.
                </p>
            </div>
            
            <!-- Enhanced CTA Button -->
            <a href="{{ url('/') }}" class="cta-button">
                <i class="fas fa-search"></i> Explore Other Tours
            </a>
            
            <div style="text-align: center; margin-top: 30px;">
                <p style="font-size: 16px; color: #1e293b; margin-bottom: 20px;">
                    Thank you for your understanding and for choosing {{ $companyName }}.
                </p>
                
                <p style="font-size: 16px; color: #1e293b;">
                    Best regards,<br>
                    <strong>The {{ $companyName }} Team</strong>
                </p>
            </div>
            
            <!-- Enhanced Contact Information -->
            <div class="contact-info">
                <h4 class="contact-title">
                    <i class="fas fa-headset"></i> Need Help?
                </h4>
                <div class="contact-methods">
                    <div class="contact-method">
                        <i class="fas fa-envelope method-icon"></i>
                        <span class="method-text">{{ $mail_settings->support_email ?? 'support@example.com' }}</span>
                    </div>
                    <div class="contact-method">
                        <i class="fas fa-phone method-icon"></i>
                        <span class="method-text">{{ $mail_settings->support_phone ?? '+1 (555) 123-4567' }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Enhanced Email Footer -->
        <div class="email-footer">
            <div class="footer-links">
                <a href="#" class="footer-link">
                    <i class="fas fa-shield-alt"></i> Privacy Policy
                </a>
                <a href="#" class="footer-link">
                    <i class="fas fa-file-contract"></i> Terms of Service
                </a>
                <a href="#" class="footer-link">
                    <i class="fas fa-phone-alt"></i> Contact Us
                </a>
            </div>
            
            <div class="social-links">
                <a href="{{ $mail_settings->facebook_url ?? '#' }}" class="social-link facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="{{ $mail_settings->twitter_url ?? '#' }}" class="social-link twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="{{ $mail_settings->instagram_url ?? '#' }}" class="social-link instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="{{ $mail_settings->linkedin_url ?? '#' }}" class="social-link linkedin">
                    <i class="fab fa-linkedin-in"></i>
                </a>
            </div>
            
            <p class="copyright">
                &copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.
            </p>
        </div>
    </div>
@endsection
