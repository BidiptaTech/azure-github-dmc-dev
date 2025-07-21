<!DOCTYPE html>
<html>
<head>
    <title>Job Assignment</title>
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
            background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
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
        
        .job-title {
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
        
        .job-title i {
            font-size: 28px;
            animation: rotate 3s linear infinite;
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .job-subtitle {
            margin-top: 8px;
            font-size: 16px;
            opacity: 0.95;
            position: relative;
            z-index: 2;
        }
        
        .job-badge {
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
        
        .job-badge i {
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
        
        /* Enhanced Job Summary Box */
        .job-summary {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 35px;
            border: 2px solid #7dd3fc;
            box-shadow: 0 4px 6px rgba(14, 165, 233, 0.1);
            position: relative;
        }
        
        .job-summary::before {
            content: '\f0b1';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            color: rgba(14, 165, 233, 0.3);
        }
        
        .summary-title {
            font-size: 20px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 25px;
            color: #0284c7;
            padding-bottom: 12px;
            border-bottom: 2px solid #7dd3fc;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .summary-title i {
            color: #0284c7;
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
            color: #0284c7;
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
            color: #0284c7;
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
            background: linear-gradient(to right, #0ea5e9, #06b6d4);
            border-radius: 3px;
        }
        
        .section-title i {
            color: #0284c7;
            font-size: 22px;
        }
        
        /* Enhanced Schedule Box */
        .schedule-box {
            background: linear-gradient(135deg, #f8faff 0%, #f1f5f9 100%);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            margin-bottom: 35px;
            position: relative;
        }
        
        .schedule-box::before {
            content: '\f017';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            color: rgba(99, 102, 241, 0.3);
        }
        
        .schedule-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            padding: 16px 25px;
            font-weight: 700;
            font-size: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .schedule-header i {
            font-size: 16px;
            margin-right: 8px;
        }
        
        .schedule-content {
            padding: 25px;
        }
        
        .schedule-time {
            font-size: 24px;
            font-weight: 800;
            color: #0284c7;
            margin-bottom: 20px;
            text-align: center;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            padding: 15px;
            border-radius: 12px;
            border: 1px solid #7dd3fc;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .schedule-time i {
            font-size: 20px;
            color: #0ea5e9;
        }
        
        .schedule-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .schedule-row:last-child {
            border-bottom: none;
        }
        
        .schedule-label {
            font-weight: 600;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .schedule-label i {
            color: #0284c7;
            font-size: 14px;
        }
        
        .schedule-value {
            color: #1e293b;
            font-weight: 500;
            text-align: right;
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
        
        .instructions-box p {
            margin: 0;
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
            background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
            color: white;
            text-decoration: none;
            padding: 16px 30px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            margin: 35px auto;
            max-width: 320px;
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.3);
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .cta-button i {
            font-size: 18px;
        }
        
        .cta-button:hover {
            background: linear-gradient(135deg, #0284c7 0%, #0891b2 100%);
            box-shadow: 0 8px 25px rgba(14, 165, 233, 0.4);
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
            color: #0284c7;
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
            color: #0284c7;
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
            color: #0284c7;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 500;
            transition: color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .footer-link:hover {
            color: #0369a1;
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
            background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
            color: white;
            border-radius: 12px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(14, 165, 233, 0.3);
        }
        
        .social-link:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 6px 12px rgba(14, 165, 233, 0.4);
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
            
            .job-title {
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
            
            .schedule-header {
                flex-direction: column;
                gap: 8px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
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
            <h1 class="job-title">
                <i class="fas fa-briefcase"></i> Job Assignment
            </h1>
            <p class="job-subtitle">You have been assigned to a new tour</p>
            <div class="job-badge">
                <i class="fas fa-hashtag"></i> {{ $assignment->job_id }}
            </div>
        </div>
        
        <!-- Enhanced Email Body -->
        <div class="email-body">
            <p class="greeting">
                Dear {{ $assignment->employee_name }}, <i class="fas fa-user-tie"></i>
            </p>
            
            <p class="intro-text">
                You have been assigned to a new tour. Please find the details below for your upcoming assignment with {{ $companyName }}. We appreciate your dedication and look forward to your excellent service.
            </p>
            
            <!-- Enhanced Job Summary Box -->
            <div class="job-summary">
                <h3 class="summary-title">
                    <i class="fas fa-clipboard-list"></i> Assignment Details
                </h3>
                <div class="summary-grid">
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-hashtag"></i> Job ID
                        </span>
                        <span class="summary-value">{{ $assignment->job_id }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-tag"></i> Job Type
                        </span>
                        <span class="summary-value">{{ $assignment->job_type }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-route"></i> Tour Name
                        </span>
                        <span class="summary-value">{{ $assignment->tour_name }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-calendar-alt"></i> Date
                        </span>
                        <span class="summary-value">{{ \Carbon\Carbon::parse($assignment->date)->format('l, jS M Y') }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-users"></i> Number of Guests
                        </span>
                        <span class="summary-value">{{ $assignment->guests }} persons</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-clock"></i> Duration
                        </span>
                        <span class="summary-value">{{ $assignment->duration }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Schedule Section -->
            <div class="section-title">
                <i class="fas fa-calendar-check"></i> Schedule Information
            </div>
            <div class="schedule-box">
                <div class="schedule-header">
                    <span>
                        <i class="fas fa-map-marker-alt"></i> Reporting Details
                    </span>
                </div>
                <div class="schedule-content">
                    <div class="schedule-time">
                        <i class="fas fa-clock"></i> {{ $assignment->time }}
                    </div>
                    <div class="schedule-row">
                        <div class="schedule-label">
                            <i class="fas fa-map-pin"></i> Meeting Point
                        </div>
                        <div class="schedule-value">{{ $assignment->meeting_point }}</div>
                    </div>
                    <div class="schedule-row">
                        <div class="schedule-label">
                            <i class="fas fa-user-check"></i> Contact Person
                        </div>
                        <div class="schedule-value">Tour Manager</div>
                    </div>
                    <div class="schedule-row">
                        <div class="schedule-label">
                            <i class="fas fa-phone"></i> Contact Number
                        </div>
                        <div class="schedule-value">+65 8123 4567</div>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Special Instructions -->
            @if(isset($assignment->special_instructions))
            <div class="section-title">
                <i class="fas fa-exclamation-circle"></i> Special Instructions
            </div>
            <div class="instructions-box">
                <h4 class="instructions-title">
                    <i class="fas fa-clipboard-check"></i> Important Notes
                </h4>
                <p>{{ $assignment->special_instructions }}</p>
            </div>
            @endif
            
            <p style="text-align: center; color: #64748b; font-size: 15px; line-height: 1.6; margin-bottom: 25px;">
                Please confirm your availability for this assignment by clicking the button below. If you have any questions or concerns about this assignment, please contact your supervisor immediately.
            </p>
            
            <!-- Enhanced CTA Button -->
            <a href="#" class="cta-button">
                <i class="fas fa-check-circle"></i> Confirm Assignment
            </a>
            
            <div style="text-align: center; margin-top: 30px;">
                <p style="font-size: 16px; color: #1e293b; font-weight: 600; margin-bottom: 8px;">
                    <i class="fas fa-heart" style="color: #0284c7;"></i> Thank you for your continued dedication and service.
                </p>
                
                <p style="font-size: 15px; color: #64748b; margin-bottom: 20px;">
                    Your professionalism and expertise make all the difference in creating memorable experiences for our guests.
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
</body>
</html>
