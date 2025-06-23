@extends('layouts.layout')
@section('title', 'Welcome to Our Platform')

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
            background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%);
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
        
        .welcome-title {
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
        
        .welcome-title i {
            font-size: 28px;
            animation: welcome-wave 2s ease-in-out infinite;
        }
        
        @keyframes welcome-wave {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(20deg); }
            75% { transform: rotate(-10deg); }
        }
        
        .welcome-subtitle {
            margin-top: 8px;
            font-size: 16px;
            opacity: 0.95;
            position: relative;
            z-index: 2;
        }
        
        .welcome-badge {
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
        
        .welcome-badge i {
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
        
        /* Enhanced Account Summary Box */
        .account-summary {
            background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 35px;
            border: 2px solid #c4b5fd;
            box-shadow: 0 4px 6px rgba(139, 92, 246, 0.1);
            position: relative;
        }
        
        .account-summary::before {
            content: '\f007';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            color: rgba(139, 92, 246, 0.3);
        }
        
        .summary-title {
            font-size: 20px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 25px;
            color: #7c3aed;
            padding-bottom: 12px;
            border-bottom: 2px solid #c4b5fd;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .summary-title i {
            color: #7c3aed;
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
            color: #7c3aed;
            font-size: 12px;
        }
        
        .summary-value {
            color: #1e293b;
            font-size: 16px;
            font-weight: 600;
        }
        
        /* Enhanced Section Styles */
        .section-title {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 25px 0;
            color: #7c3aed;
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
            background: linear-gradient(to right, #8b5cf6, #a855f7);
            border-radius: 3px;
        }
        
        .section-title i {
            color: #7c3aed;
            font-size: 22px;
        }
        
        /* Enhanced Feature Cards */
        .feature-section {
            margin-bottom: 35px;
        }
        
        .feature-card {
            background: linear-gradient(135deg, #f8faff 0%, #f1f5f9 100%);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, #8b5cf6, #a855f7);
        }
        
        .feature-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%);
            color: white;
            width: 55px;
            height: 55px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 22px;
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(139, 92, 246, 0.3);
            flex-shrink: 0;
        }
        
        .feature-content {
            flex: 1;
            padding-top: 4px;
        }
        
        .feature-title {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 8px;
            color: #1e293b;
        }
        
        .feature-text {
            font-size: 14px;
            color: #64748b;
            margin: 0;
            line-height: 1.6;
        }
        
        /* Enhanced CTA Box */
        .cta-box {
            background: linear-gradient(135deg, #fefce8 0%, #fef3c7 100%);
            border-radius: 20px;
            padding: 35px 25px;
            text-align: center;
            margin: 35px 0;
            border: 2px solid #fbbf24;
            box-shadow: 0 8px 25px rgba(251, 191, 36, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .cta-box::before {
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
        
        .cta-title {
            font-size: 22px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 15px;
            color: #92400e;
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .cta-title i {
            color: #f59e0b;
            font-size: 22px;
        }
        
        .cta-box p {
            color: #92400e;
            font-size: 16px;
            margin-bottom: 25px;
            position: relative;
            z-index: 2;
        }
        
        /* Enhanced CTA Button */
        .cta-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-align: center;
            background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%);
            color: white;
            text-decoration: none;
            padding: 18px 35px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 18px;
            margin: 0 auto;
            max-width: 350px;
            box-shadow: 0 6px 20px rgba(139, 92, 246, 0.3);
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            z-index: 2;
        }
        
        .cta-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }
        
        .cta-button:hover::before {
            left: 100%;
        }
        
        .cta-button i {
            font-size: 20px;
            position: relative;
            z-index: 1;
        }
        
        .cta-button:hover {
            background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%);
            box-shadow: 0 8px 25px rgba(139, 92, 246, 0.4);
            transform: translateY(-2px);
        }
        
        /* Enhanced Contact Information */
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
            color: #7c3aed;
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
            color: #7c3aed;
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
            color: #7c3aed;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 500;
            transition: color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .footer-link:hover {
            color: #6d28d9;
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
            background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%);
            color: white;
            border-radius: 12px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(139, 92, 246, 0.3);
        }
        
        .social-link:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 6px 12px rgba(139, 92, 246, 0.4);
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
            
            .welcome-title {
                font-size: 24px;
                flex-direction: column;
                gap: 8px;
            }
            
            .greeting {
                flex-direction: column;
                gap: 8px;
            }
            
            .cta-button {
                font-size: 16px;
                padding: 16px 25px;
                max-width: 300px;
            }
            
            .feature-card {
                flex-direction: column;
                text-align: center;
            }
            
            .feature-icon {
                margin-right: 0;
                margin-bottom: 15px;
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
            <h1 class="welcome-title">
                <i class="fas fa-hand-sparkles"></i> Welcome, {{ $user->name }}!
            </h1>
            <p class="welcome-subtitle">We're thrilled to have you join our community</p>
            <div class="welcome-badge">
                <i class="fas fa-user-plus"></i> New Member
            </div>
        </div>
        
        <!-- Enhanced Email Body -->
        <div class="email-body">
            <p class="greeting">
                Dear {{ $user->name }}, <i class="fas fa-smile-beam"></i>
            </p>
            
            <p class="intro-text">
                Thank you for registering with {{ $companyName }}! Your account has been successfully created, and we're excited to have you as part of our community. Here's everything you need to know to get started on your amazing journey with us.
            </p>
            
            <!-- Enhanced Account Summary Box -->
            <div class="account-summary">
                <h3 class="summary-title">
                    <i class="fas fa-user-circle"></i> Your Account Details
                </h3>
                <div class="summary-grid">
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-user"></i> Name
                        </span>
                        <span class="summary-value">{{ $user->name }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-envelope"></i> Email Address
                        </span>
                        <span class="summary-value">{{ $user->email }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-calendar-plus"></i> Registration Date
                        </span>
                        <span class="summary-value">{{ \Carbon\Carbon::parse($user->registration_date ?? now())->format('l, jS M Y') }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-check-circle"></i> Account Status
                        </span>
                        <span class="summary-value">Active</span>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Features Section -->
            <div class="feature-section">
                <h3 class="section-title">
                    <i class="fas fa-magic"></i> What You Can Do
                </h3>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <div class="feature-content">
                        <h4 class="feature-title">Browse and Book Tours</h4>
                        <p class="feature-text">Explore our wide range of destinations and book your next adventure with ease. Discover amazing places around the world.</p>
                    </div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="feature-content">
                        <h4 class="feature-title">Manage Your Bookings</h4>
                        <p class="feature-text">View, modify or cancel your bookings from your personal dashboard. Keep track of all your upcoming adventures.</p>
                    </div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="feature-content">
                        <h4 class="feature-title">Save Favorites</h4>
                        <p class="feature-text">Create a wishlist of destinations and tours you'd like to experience. Never lose track of your dream destinations.</p>
                    </div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="feature-content">
                        <h4 class="feature-title">24/7 Support</h4>
                        <p class="feature-text">Access our customer support team anytime, anywhere. We're here to help make your travel experience seamless.</p>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced CTA Box -->
            <div class="cta-box">
                <h3 class="cta-title">
                    <i class="fas fa-rocket"></i> Ready to Start Exploring?
                </h3>
                <p>Complete your profile and start discovering amazing destinations today. Your next adventure awaits!</p>
                <a href="{{ $user->verification_link ?? '#' }}" class="cta-button">
                    <i class="fas fa-check-double"></i> Verify Your Account
                </a>
            </div>
            
            <p style="text-align: center; color: #64748b; font-size: 15px; line-height: 1.6; margin-bottom: 25px;">
                If you have any questions or need assistance, our customer support team is here to help. You can reach us at <a href="mailto:support@example.com" style="color: #7c3aed; text-decoration: none; font-weight: 600;">support@example.com</a> or call +65 9123 4567.
            </p>
            
            <div style="text-align: center; margin-top: 30px;">
                <p style="font-size: 16px; color: #1e293b; font-weight: 600; margin-bottom: 8px;">
                    <i class="fas fa-heart" style="color: #7c3aed;"></i> We look forward to helping you plan your next adventure!
                </p>
                
                <p style="font-size: 15px; color: #64748b; margin-bottom: 20px;">
                    Welcome to the {{ $companyName }} family. Let's create unforgettable memories together!
                </p>
                
                <p style="font-size: 16px; color: #1e293b;">
                    Warm regards,<br>
                    <strong>The {{ $companyName }} Team</strong>
                </p>
            </div>
            
            <!-- Enhanced Contact Information -->
            <div class="contact-info">
                <h4 class="contact-title">
                    <i class="fas fa-phone-alt"></i> Need Help?
                </h4>
                <div class="contact-methods">
                    <div class="contact-method">
                        <i class="fas fa-envelope method-icon"></i>
                        <span class="method-text">support@example.com</span>
                    </div>
                    <div class="contact-method">
                        <i class="fas fa-phone method-icon"></i>
                        <span class="method-text">+65 9123 4567</span>
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
                <a href="#" class="social-link facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" class="social-link twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" class="social-link instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" class="social-link linkedin">
                    <i class="fab fa-linkedin-in"></i>
                </a>
            </div>
            
            <p class="copyright">
                &copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.
            </p>
        </div>
    </div>
@endsection
