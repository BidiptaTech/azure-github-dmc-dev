@extends('layouts.layout')
@section('title', 'Booking Reminder')

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
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
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
        
        .reminder-title {
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
        
        .reminder-title i {
            font-size: 28px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .reminder-subtitle {
            margin-top: 8px;
            font-size: 16px;
            opacity: 0.95;
            position: relative;
            z-index: 2;
        }
        
        .reminder-badge {
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
        
        .reminder-badge i {
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
        
        /* Enhanced Countdown Box */
        .countdown-box {
            background: linear-gradient(135deg, #fff7ed 0%, #fed7aa 100%);
            border-radius: 20px;
            padding: 35px 25px;
            text-align: center;
            margin-bottom: 35px;
            border: 2px solid #fbbf24;
            box-shadow: 0 8px 25px rgba(251, 191, 36, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .countdown-box::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }
        
        .countdown-text {
            color: #92400e;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 2;
        }
        
        .countdown-number {
            font-size: 4rem;
            font-weight: 800;
            color: #dc2626;
            display: block;
            line-height: 1;
            margin: 15px 0;
            text-shadow: 0 4px 8px rgba(220, 38, 38, 0.3);
            position: relative;
            z-index: 2;
            animation: bounce 2s infinite;
        }
        
        .countdown-number.today {
            font-size: 2.5rem;
            color: #059669;
            text-shadow: 0 4px 8px rgba(5, 150, 105, 0.3);
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        
        .countdown-label {
            color: #92400e;
            font-size: 18px;
            font-weight: 700;
            margin-top: 10px;
            position: relative;
            z-index: 2;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .countdown-urgent {
            background: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%);
            border-color: #f87171;
        }
        
        .countdown-urgent .countdown-number {
            color: #dc2626;
            animation: pulse-urgent 1s infinite;
        }
        
        @keyframes pulse-urgent {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .countdown-today {
            background: linear-gradient(135deg, #ecfdf5 0%, #a7f3d0 100%);
            border-color: #10b981;
        }
        
        /* Enhanced Booking Summary Box */
        .booking-summary {
            background: linear-gradient(135deg, #f8faff 0%, #f1f5f9 100%);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 35px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .summary-title {
            font-size: 20px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 25px;
            color: #dc2626;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
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
        
        /* Enhanced Instructions Box */
        .instructions-box {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border-radius: 16px;
            padding: 25px 30px;
            margin-bottom: 35px;
            border: 2px solid #a7f3d0;
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.1);
            position: relative;
        }
        
        .instructions-box::before {
            content: '\f0eb';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            color: rgba(5, 150, 105, 0.3);
        }
        
        .instructions-title {
            font-size: 18px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 15px;
            color: #065f46;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .instructions-title i {
            color: #059669;
            font-size: 18px;
        }
        
        .instructions-box ul {
            margin: 0;
            padding-left: 25px;
        }
        
        .instructions-box li {
            margin-bottom: 8px;
            color: #065f46;
            font-weight: 500;
            position: relative;
        }
        
        .instructions-box li::marker {
            color: #059669;
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
        
        /* Enhanced CTA Button */
        .cta-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-align: center;
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
            color: white;
            text-decoration: none;
            padding: 16px 30px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            margin: 35px auto;
            max-width: 320px;
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.3);
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .cta-button i {
            font-size: 18px;
        }
        
        .cta-button:hover {
            background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%);
            box-shadow: 0 8px 25px rgba(220, 38, 38, 0.4);
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
            
            .countdown-number {
                font-size: 3rem;
            }
            
            .reminder-title {
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
            <h1 class="reminder-title">
                <i class="fas fa-bell"></i> Your Booking is Coming Up!
            </h1>
            <p class="reminder-subtitle">A friendly reminder about your upcoming booking</p>
            <div class="reminder-badge">
                <i class="fas fa-ticket-alt"></i> #{{ $booking->booking_id }}
            </div>
        </div>
        
        <!-- Enhanced Email Body -->
        <div class="email-body">
            <p class="greeting">
                Dear {{ $booking->customer_name }}, <i class="fas fa-hand-wave"></i>
            </p>
            
            <p class="intro-text">
                This is a friendly reminder about your upcoming booking with {{ $companyName }}. We're looking forward to welcoming you soon and ensuring you have an amazing experience!
            </p>
            
            <!-- Enhanced Countdown Box -->
            @php
                $departureDate = $booking->departure_date ?? $booking->check_in_date ?? now()->addDays(3);
                $daysLeft = (int) floor(abs(now()->diffInDays(\Carbon\Carbon::parse($departureDate))));
                
                // Handle different scenarios
                if ($daysLeft == 0) {
                    $timeDisplay = 'Today!';
                    $timeLabel = '';
                    $numberClass = 'today';
                } elseif ($daysLeft == 1) {
                    $timeDisplay = '1';
                    $timeLabel = 'Day';
                    $numberClass = '';
                } else {
                    $timeDisplay = number_format($daysLeft);
                    $timeLabel = 'Days';
                    $numberClass = '';
                }
            @endphp
            
            <div class="countdown-box {{ $daysLeft <= 1 ? 'countdown-urgent' : '' }} {{ $daysLeft == 0 ? 'countdown-today' : '' }}">
                <p class="countdown-text">
                    <i class="fas fa-hourglass-half"></i> 
                    @if($daysLeft == 0)
                        Your booking is
                    @else
                        Your booking is in
                    @endif
                </p>
                <span class="countdown-number {{ $numberClass }}">{{ $timeDisplay }}</span>
                @if($timeLabel)
                    <p class="countdown-label">{{ $timeLabel }}</p>
                @endif
            </div>
            
            <!-- Enhanced Booking Summary Box -->
            <div class="booking-summary">
                <h3 class="summary-title">
                    <i class="fas fa-info-circle"></i> Booking Details
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
                            <i class="fas fa-calendar-plus"></i> Booking Date
                        </span>
                        <span class="summary-value">{{ \Carbon\Carbon::parse($booking->booking_date ?? now())->format('jS M Y') }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-plane"></i> Departure Date
                        </span>
                        <span class="summary-value">{{ \Carbon\Carbon::parse($booking->departure_date ?? $booking->check_in_date ?? now())->format('l, jS M Y') }}</span>
                    </div>
                    @if(isset($booking->departure_time))
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-clock"></i> Departure Time
                        </span>
                        <span class="summary-value">{{ $booking->departure_time }}</span>
                    </div>
                    @endif
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-map-marker-alt"></i> Meeting Point
                        </span>
                        <span class="summary-value">{{ $booking->meeting_point ?? $booking->location ?? 'TBA' }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Important Items Section -->
            <div class="section-title">
                <i class="fas fa-suitcase"></i> Don't Forget to Bring
            </div>
            <div class="instructions-box">
                <h4 class="instructions-title">
                    <i class="fas fa-clipboard-check"></i> Important Items to Pack
                </h4>
                <ul>
                    @if(isset($booking->important_items) && is_array($booking->important_items))
                        @foreach($booking->important_items as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    @else
                        <li>Valid identification documents</li>
                        <li>Comfortable walking shoes</li>
                        <li>Weather-appropriate clothing</li>
                        <li>Camera for memorable moments</li>
                        <li>Any personal medications</li>
                        <li>Sunscreen and hat</li>
                    @endif
                </ul>
            </div>
            
            <p style="text-align: center; color: #64748b; font-size: 15px; line-height: 1.6; margin-bottom: 25px;">
                If you need to make any changes to your booking, please contact us as soon as possible. Our team is here to help ensure your experience is as smooth and enjoyable as possible.
            </p>
            
            <!-- Enhanced CTA Button -->
            <a href="{{ route('bookinglist.index') }}" class="cta-button">
                <i class="fas fa-cog"></i> Manage My Booking
            </a>
            
            <div style="text-align: center; margin-top: 30px;">
                <p style="font-size: 16px; color: #1e293b; font-weight: 600; margin-bottom: 8px;">
                    <i class="fas fa-heart" style="color: #dc2626;"></i> We're looking forward to welcoming you!
                </p>
                
                <p style="font-size: 15px; color: #64748b; margin-bottom: 20px;">
                    If you have any questions, please don't hesitate to contact us using the information below.
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
