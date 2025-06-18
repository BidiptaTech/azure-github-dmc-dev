@extends('layouts.layout')
@section('title', 'Payment Confirmation')

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
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
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
        
        .payment-title {
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
        
        .payment-title i {
            font-size: 28px;
            animation: checkmark 2s ease-in-out infinite;
        }
        
        @keyframes checkmark {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .payment-subtitle {
            margin-top: 8px;
            font-size: 16px;
            opacity: 0.95;
            position: relative;
            z-index: 2;
        }
        
        .payment-badge {
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
        
        .payment-badge i {
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
        
        /* Enhanced Payment Summary Box */
        .payment-summary {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 35px;
            border: 2px solid #a7f3d0;
            box-shadow: 0 4px 6px rgba(5, 150, 105, 0.1);
            position: relative;
        }
        
        .payment-summary::before {
            content: '\f155';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            color: rgba(5, 150, 105, 0.3);
        }
        
        .summary-title {
            font-size: 20px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 25px;
            color: #059669;
            padding-bottom: 12px;
            border-bottom: 2px solid #a7f3d0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .summary-title i {
            color: #059669;
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
        
        .summary-item.full-width {
            grid-column: 1 / -1;
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
            color: #059669;
            font-size: 12px;
        }
        
        .summary-value {
            color: #1e293b;
            font-size: 16px;
            font-weight: 600;
        }
        
        .amount-highlight {
            color: #059669;
            font-weight: 700;
            font-size: 20px;
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
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
            border: 1px solid #059669;
        }
        
        .status-badge i {
            font-size: 12px;
        }
        
        /* Enhanced Receipt Section */
        .receipt-section {
            margin-bottom: 35px;
        }
        
        .section-title {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 25px 0;
            color: #059669;
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
            background: linear-gradient(to right, #059669, #10b981);
            border-radius: 3px;
        }
        
        .section-title i {
            color: #059669;
            font-size: 22px;
        }
        
        .receipt {
            background: linear-gradient(135deg, #f8faff 0%, #f1f5f9 100%);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            position: relative;
        }
        
        .receipt::before {
            content: '\f543';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            color: rgba(99, 102, 241, 0.3);
        }
        
        .receipt-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            padding: 16px 25px;
            font-weight: 700;
            font-size: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .receipt-header i {
            font-size: 16px;
            margin-right: 8px;
        }
        
        .receipt-date {
            font-size: 14px;
            opacity: 0.9;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .receipt-date i {
            font-size: 12px;
        }
        
        .receipt-content {
            padding: 25px;
        }
        
        .receipt-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .receipt-row:last-child {
            border-bottom: none;
        }
        
        .receipt-label {
            font-weight: 600;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .receipt-label i {
            color: #6366f1;
            font-size: 14px;
        }
        
        .receipt-value {
            color: #1e293b;
            font-weight: 500;
            text-align: right;
        }
        
        .receipt-footer {
            background: linear-gradient(135deg, #ddd6fe 0%, #c4b5fd 100%);
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #8b5cf6;
            font-weight: 700;
            color: #5b21b6;
            font-size: 18px;
        }
        
        /* Enhanced Success Message */
        .success-message {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border-radius: 16px;
            padding: 25px 30px;
            margin-bottom: 35px;
            border: 2px solid #a7f3d0;
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.1);
            text-align: center;
            position: relative;
        }
        
        .success-message::before {
            content: '\f058';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            color: rgba(5, 150, 105, 0.3);
        }
        
        .success-message h4 {
            font-size: 18px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 15px;
            color: #065f46;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .success-message h4 i {
            color: #059669;
            font-size: 18px;
        }
        
        .success-message p {
            margin-bottom: 0;
            color: #065f46;
            line-height: 1.6;
        }
        
        /* Enhanced CTA Button */
        .cta-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-align: center;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
            text-decoration: none;
            padding: 16px 30px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            margin: 35px auto;
            max-width: 320px;
            box-shadow: 0 6px 20px rgba(5, 150, 105, 0.3);
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .cta-button i {
            font-size: 18px;
        }
        
        .cta-button:hover {
            background: linear-gradient(135deg, #047857 0%, #059669 100%);
            box-shadow: 0 8px 25px rgba(5, 150, 105, 0.4);
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
            color: #059669;
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
            color: #059669;
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
            color: #059669;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 500;
            transition: color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .footer-link:hover {
            color: #047857;
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
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
            border-radius: 12px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(5, 150, 105, 0.3);
        }
        
        .social-link:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 6px 12px rgba(5, 150, 105, 0.4);
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
            
            .payment-title {
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
            
            .receipt-header {
                flex-direction: column;
                gap: 8px;
                text-align: center;
            }
            
            .receipt-footer {
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
            <h1 class="payment-title">
                <i class="fas fa-check-circle"></i> Payment Confirmed!
            </h1>
            <p class="payment-subtitle">Thank you for your payment!</p>
            <div class="payment-badge">
                <i class="fas fa-receipt"></i> #{{ $payment->payment_id ?? 'PAY-'.rand(10000, 99999) }}
            </div>
        </div>
        
        <!-- Enhanced Email Body -->
        <div class="email-body">
            <p class="greeting">
                Dear {{ $payment->customer_name ?? 'Valued Customer' }}, <i class="fas fa-smile"></i>
            </p>
            
            <p class="intro-text">
                We're delighted to confirm that we have received your payment for your booking with {{ $companyName }}. Your transaction has been processed successfully, and your booking is now fully confirmed!
            </p>
            
            <!-- Enhanced Payment Summary Box -->
            <div class="payment-summary">
                <h3 class="summary-title">
                    <i class="fas fa-credit-card"></i> Payment Summary
                </h3>
                <div class="summary-grid">
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-hashtag"></i> Payment ID
                        </span>
                        <span class="summary-value">{{ $payment->payment_id ?? 'PAY-'.rand(10000, 99999) }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-calendar-alt"></i> Payment Date
                        </span>
                        <span class="summary-value">{{ \Carbon\Carbon::parse($payment->payment_date ?? now())->format('jS M Y') }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-ticket-alt"></i> Booking ID
                        </span>
                        <span class="summary-value">{{ $payment->booking_id ?? 'BOK-'.rand(10000, 99999) }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-tag"></i> Booking Type
                        </span>
                        <span class="summary-value">{{ $payment->booking_type ?? 'Tour Package' }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-credit-card"></i> Payment Method
                        </span>
                        <span class="summary-value">
                            {{ $payment->payment_method ?? 'Credit Card' }}
                            @if(isset($payment->card_last_four))
                                (ending in {{ $payment->card_last_four }})
                            @endif
                        </span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-check-circle"></i> Status
                        </span>
                        <span class="summary-value">
                            <span class="status-badge">
                                <i class="fas fa-check"></i> {{ $payment->status ?? 'Completed' }}
                            </span>
                        </span>
                    </div>
                    <div class="summary-item full-width">
                        <span class="summary-label">
                            <i class="fas fa-dollar-sign"></i> Amount Paid
                        </span>
                        <span class="summary-value amount-highlight">{{ $payment->currency ?? 'SGD' }} {{ number_format($payment->amount ?? 750, 2) }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Receipt Details -->
            <div class="receipt-section">
                <h3 class="section-title">
                    <i class="fas fa-file-invoice"></i> Transaction Details
                </h3>
                
                <div class="receipt">
                    <div class="receipt-header">
                        <span>
                            <i class="fas fa-receipt"></i> Payment Receipt
                        </span>
                        <span class="receipt-date">
                            <i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($payment->payment_date ?? now())->format('jS M Y, g:i A') }}
                        </span>
                    </div>
                    <div class="receipt-content">
                        <div class="receipt-row">
                            <div class="receipt-label">
                                <i class="fas fa-barcode"></i> Transaction ID
                            </div>
                            <div class="receipt-value">{{ $payment->transaction_id ?? 'TXN-'.rand(1000000, 9999999) }}</div>
                        </div>
                        <div class="receipt-row">
                            <div class="receipt-label">
                                <i class="fas fa-info-circle"></i> Booking Details
                            </div>
                            <div class="receipt-value">{{ $payment->booking_details ?? 'Tour Booking Package' }}</div>
                        </div>
                        <div class="receipt-row">
                            <div class="receipt-label">
                                <i class="fas fa-credit-card"></i> Payment Method
                            </div>
                            <div class="receipt-value">
                                {{ $payment->payment_method ?? 'Credit Card' }}
                                @if(isset($payment->card_last_four))
                                    (ending in {{ $payment->card_last_four }})
                                @endif
                            </div>
                        </div>
                        <div class="receipt-row">
                            <div class="receipt-label">
                                <i class="fas fa-shield-alt"></i> Authorization Code
                            </div>
                            <div class="receipt-value">{{ $payment->auth_code ?? strtoupper(Str::random(8)) }}</div>
                        </div>
                    </div>
                    <div class="receipt-footer">
                        <div>
                            <i class="fas fa-coins"></i> Total Amount
                        </div>
                        <div>{{ $payment->currency ?? 'SGD' }} {{ number_format($payment->amount ?? 750, 2) }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Success Message -->
            <div class="success-message">
                <h4>
                    <i class="fas fa-check-circle"></i> Payment Successfully Processed!
                </h4>
                <p>Your payment has been processed successfully, and your booking is now confirmed. You can view the complete details of your booking by clicking the button below.</p>
            </div>
            
            <!-- Enhanced CTA Button -->
            <a href="{{ route('bookinglist.index') }}" class="cta-button">
                <i class="fas fa-eye"></i> View Booking Details
            </a>
            
            <div style="text-align: center; margin-top: 30px;">
                <p style="font-size: 16px; color: #1e293b; font-weight: 600; margin-bottom: 8px;">
                    <i class="fas fa-heart" style="color: #059669;"></i> Thank you for choosing {{ $companyName }}!
                </p>
                
                <p style="font-size: 15px; color: #64748b; margin-bottom: 20px;">
                    If you have any questions about this payment or your booking, please don't hesitate to contact our customer service team using the information below.
                </p>
                
                <p style="font-size: 16px; color: #1e293b;">
                    Warm regards,<br>
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
