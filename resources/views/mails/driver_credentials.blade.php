<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Your Driver App Credentials</title>
    <style>
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.6;
        }
        
        .email-container {
            max-width: 650px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
        }
        
        .email-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
            position: relative;
        }
        
        .logo-container {
            margin-bottom: 20px;
        }
        
        .logo {
            max-width: 140px;
            border-radius: 12px;
            border: 4px solid rgba(255,255,255,0.9);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .header-title {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        .header-subtitle {
            margin-top: 8px;
            font-size: 16px;
            opacity: 0.95;
        }
        
        .email-body {
            padding: 40px;
        }
        
        .greeting {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #1e293b;
        }
        
        .intro-text {
            margin-bottom: 30px;
            font-size: 16px;
            color: #64748b;
            line-height: 1.7;
        }
        
        .credentials-box {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            border: 2px solid #f59e0b;
            box-shadow: 0 4px 6px rgba(245, 158, 11, 0.1);
        }
        
        .credentials-title {
            font-size: 20px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 20px;
            color: #92400e;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .credential-item {
            background: white;
            padding: 18px;
            border-radius: 12px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid #f3f4f6;
        }
        
        .credential-item:last-child {
            margin-bottom: 0;
        }
        
        .credential-label {
            font-weight: 600;
            color: #64748b;
            font-size: 14px;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .credential-value {
            color: #1e293b;
            font-size: 18px;
            font-weight: 700;
            word-break: break-all;
            background: #f8fafc;
            padding: 10px 15px;
            border-radius: 8px;
            border-left: 4px solid #f59e0b;
            font-family: 'Courier New', monospace;
        }
        
        .driver-info-box {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-radius: 16px;
            padding: 25px;
            margin: 30px 0;
            border: 2px solid #3b82f6;
            box-shadow: 0 4px 6px rgba(59, 130, 246, 0.1);
        }
        
        .driver-info-title {
            font-size: 18px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 15px;
            color: #1e40af;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .driver-info-item {
            color: #1e3a8a;
            font-size: 15px;
            margin-bottom: 8px;
            display: flex;
            align-items: start;
            gap: 10px;
        }
        
        .driver-info-item i {
            color: #3b82f6;
            margin-top: 3px;
            min-width: 20px;
        }
        
        .features-section {
            margin: 35px 0;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #f59e0b;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .feature-item {
            display: flex;
            align-items: start;
            margin-bottom: 18px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 12px;
            border-left: 4px solid #f59e0b;
        }
        
        .feature-icon {
            color: #f59e0b;
            font-size: 20px;
            margin-right: 15px;
            margin-top: 2px;
            min-width: 25px;
        }
        
        .feature-text {
            color: #475569;
            font-size: 15px;
            margin: 0;
        }
        
        .cta-button {
            display: block;
            text-align: center;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            text-decoration: none;
            padding: 18px 35px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 18px;
            margin: 30px auto;
            max-width: 350px;
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.3);
            transition: all 0.3s ease;
        }
        
        .cta-button:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
        }
        
        .security-notice {
            background: #fef2f2;
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
            border-left: 4px solid #ef4444;
        }
        
        .security-notice-title {
            font-weight: 700;
            color: #991b1b;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .security-notice-text {
            color: #7f1d1d;
            font-size: 14px;
            margin: 0;
        }
        
        .support-box {
            background: #f8fafc;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        
        .support-title {
            font-size: 18px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 15px;
            color: #1e293b;
        }
        
        .contact-info {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
            margin-top: 15px;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            font-size: 14px;
        }
        
        .contact-icon {
            color: #f59e0b;
            font-size: 16px;
        }
        
        .email-footer {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            padding: 30px;
            text-align: center;
            font-size: 14px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        
        .footer-text {
            margin: 10px 0;
        }
        
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 12px;
            }
            
            .email-body {
                padding: 25px 20px;
            }
            
            .credentials-box,
            .driver-info-box {
                padding: 20px;
            }
            
            .contact-info {
                flex-direction: column;
                gap: 15px;
            }
            
            .cta-button {
                padding: 16px 25px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Email Header -->
        <div class="email-header">
            <div class="logo-container">
                @if(isset($company_logo) && $company_logo)
                    <img src="{{ $company_logo }}" alt="{{ $company_name ?? 'Company Logo' }}" class="logo">
                @endif
            </div>
            <h1 class="header-title">Welcome to Our Team!</h1>
            <p class="header-subtitle">Your driver app credentials are ready</p>
        </div>
        
        <!-- Email Body -->
        <div class="email-body">
            <p class="greeting">Hello {{ $driver_name ?? 'Driver' }}!</p>
            
            <p class="intro-text">
                Welcome to our driver network! We're excited to have you join our team. Your driver account has been 
                successfully created, and you can now access the driver app to manage your assignments, view schedules, 
                and track your earnings.
            </p>
            
            <!-- Credentials Box -->
            <div class="credentials-box">
                <h3 class="credentials-title">
                    <i class="fas fa-key"></i> Your Login Credentials
                </h3>
                
                <div class="credential-item">
                    <div class="credential-label">
                        <i class="fas fa-envelope"></i> Email / Username
                    </div>
                    <div class="credential-value">{{ $email ?? 'Not provided' }}</div>
                </div>
                
                <div class="credential-item">
                    <div class="credential-label">
                        <i class="fas fa-lock"></i> Password
                    </div>
                    <div class="credential-value">{{ $app_password ?? '********' }}</div>
                </div>
            </div>
            
            <!-- Driver Info -->
            @if(isset($driver_id) || isset($phone) || isset($license_no))
            <div class="driver-info-box">
                <h3 class="driver-info-title">
                    <i class="fas fa-id-card"></i> Your Driver Profile
                </h3>
                @if(isset($driver_id))
                <div class="driver-info-item">
                    <i class="fas fa-hashtag"></i>
                    <span><strong>Driver ID:</strong> {{ $driver_id }}</span>
                </div>
                @endif
                @if(isset($phone))
                <div class="driver-info-item">
                    <i class="fas fa-phone"></i>
                    <span><strong>Contact:</strong> {{ $phone }}</span>
                </div>
                @endif
                @if(isset($license_no))
                <div class="driver-info-item">
                    <i class="fas fa-id-card-alt"></i>
                    <span><strong>License No:</strong> {{ $license_no }}</span>
                </div>
                @endif
                @if(isset($operational_city))
                <div class="driver-info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span><strong>Operational City:</strong> {{ $operational_city }}</span>
                </div>
                @endif
            </div>
            @endif
            
            <!-- Security Notice -->
            <div class="security-notice">
                <div class="security-notice-title">
                    <i class="fas fa-shield-alt"></i> Security Notice
                </div>
                <p class="security-notice-text">
                    Please keep your credentials secure and do not share them with anyone. 
                    We recommend changing your password after your first login for enhanced security.
                </p>
            </div>
            
            <!-- Features Section -->
            <div class="features-section">
                <h3 class="section-title">
                    <i class="fas fa-star"></i> What You Can Do
                </h3>
                
                <div class="feature-item">
                    <i class="fas fa-calendar-check feature-icon"></i>
                    <p class="feature-text">
                        <strong>View Assignments:</strong> Check your upcoming tours and assignments in real-time
                    </p>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-route feature-icon"></i>
                    <p class="feature-text">
                        <strong>Navigate Routes:</strong> Access tour routes, pickup locations, and schedules
                    </p>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-clock feature-icon"></i>
                    <p class="feature-text">
                        <strong>Track Hours:</strong> Monitor your working hours and availability status
                    </p>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-wallet feature-icon"></i>
                    <p class="feature-text">
                        <strong>View Earnings:</strong> Keep track of your payments and financial records
                    </p>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-headset feature-icon"></i>
                    <p class="feature-text">
                        <strong>Get Support:</strong> Access our support team anytime for assistance
                    </p>
                </div>
            </div>
            
            <!-- CTA Button -->
            <a href="#" class="cta-button">
                <i class="fas fa-mobile-alt"></i> Download Driver App
            </a>
            
            <!-- Support Box -->
            <div class="support-box">
                <h4 class="support-title">Need Help?</h4>
                <p style="color: #64748b; margin-bottom: 15px;">
                    Our support team is here to assist you 24/7
                </p>
                <div class="contact-info">
                    @if(isset($support_email) && $support_email)
                    <div class="contact-item">
                        <i class="fas fa-envelope contact-icon"></i>
                        <span>{{ $support_email }}</span>
                    </div>
                    @endif
                    @if(isset($support_phone) && $support_phone)
                    <div class="contact-item">
                        <i class="fas fa-phone contact-icon"></i>
                        <span>{{ $support_phone }}</span>
                    </div>
                    @endif
                </div>
            </div>
            
            <p style="text-align: center; color: #64748b; font-size: 15px; margin-top: 30px;">
                We're excited to have you on board. Safe travels!
            </p>
            
            <p style="text-align: center; color: #1e293b; font-size: 16px; margin-top: 20px;">
                Best regards,<br>
                <strong>{{ $company_name ?? 'The Operations Team' }}</strong>
            </p>
        </div>
        
        <!-- Email Footer -->
        <div class="email-footer">
            <p class="footer-text">
                &copy; {{ date('Y') }} {{ $company_name ?? 'Company Name' }}. All rights reserved.
            </p>
            <p class="footer-text" style="font-size: 12px; opacity: 0.8;">
                This email was sent to {{ $email ?? 'you' }} regarding your driver account registration.
            </p>
        </div>
    </div>
</body>
</html>

