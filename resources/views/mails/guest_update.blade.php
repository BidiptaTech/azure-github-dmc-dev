
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Updated - Your Tour Tracking Credentials</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        
        .update-notice {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            border-radius: 16px;
            padding: 25px;
            margin: 30px 0;
            border: 2px solid #22c55e;
            box-shadow: 0 4px 6px rgba(34, 197, 94, 0.1);
        }
        
        .update-notice-title {
            font-size: 18px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 15px;
            color: #166534;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .update-notice-text {
            color: #14532d;
            font-size: 15px;
            line-height: 1.7;
            margin: 0;
        }
        
        .credentials-box {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            border: 2px solid #38bdf8;
            box-shadow: 0 4px 6px rgba(56, 189, 248, 0.1);
        }
        
        .credentials-title {
            font-size: 20px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 20px;
            color: #0369a1;
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
            border-left: 4px solid #38bdf8;
            font-family: 'Courier New', monospace;
        }
        
        .tour-info-box {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-radius: 16px;
            padding: 25px;
            margin: 30px 0;
            border: 2px solid #fbbf24;
            box-shadow: 0 4px 6px rgba(251, 191, 36, 0.1);
        }
        
        .tour-info-title {
            font-size: 18px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 15px;
            color: #92400e;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .tour-info-content {
            color: #78350f;
            font-size: 15px;
            line-height: 1.7;
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
            color: #667eea;
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
            .tour-info-box,
            .update-notice {
                padding: 20px;
            }
            
            .contact-info {
                flex-direction: column;
                gap: 15px;
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
            <h1 class="header-title">Account Updated</h1>
            <p class="header-subtitle">Your tour tracking credentials have been updated</p>
        </div>
        
        <!-- Email Body -->
        <div class="email-body">
            <p class="greeting">Hello {{ $guest_name ?? 'Guest' }}!</p>
            
            <p class="intro-text">
                We're writing to inform you that your guest account information has been successfully updated. 
                Your updated credentials and tour information are provided below for your reference.
            </p>
            
            <!-- Update Notice -->
            <div class="update-notice">
                <h3 class="update-notice-title">
                    <i class="fas fa-check-circle"></i> What's Been Updated
                </h3>
                <p class="update-notice-text">
                    Your account credentials have been refreshed. Please use the updated information below to access 
                    your tour tracking dashboard and stay connected with your tour activities.
                </p>
            </div>
            
            <!-- Credentials Box -->
            <div class="credentials-box">
                <h3 class="credentials-title">
                    <i class="fas fa-key"></i> Your Updated Login Credentials
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
                
                @if(isset($contact) && $contact)
                <div class="credential-item">
                    <div class="credential-label">
                        <i class="fas fa-phone"></i> Contact Number
                    </div>
                    <div class="credential-value">{{ $country_code ?? '' }} {{ $contact }}</div>
                </div>
                @endif
            </div>
            
            <!-- Tour Info -->
            @if(isset($tour_id) && $tour_id)
            <div class="tour-info-box">
                <h3 class="tour-info-title">
                    <i class="fas fa-route"></i> Tour Information
                </h3>
                <div class="tour-info-content">
                    <strong>Tour ID{{ is_array($tour_id) && count($tour_id) > 1 ? 's' : '' }}:</strong> 
                    {{ is_array($tour_id) ? implode(', ', $tour_id) : $tour_id }}<br>
                    You can use {{ is_array($tour_id) && count($tour_id) > 1 ? 'these IDs' : 'this ID' }} to track your tour and access all related information in the app.
                </div>
            </div>
            @endif
            
            <!-- Security Notice -->
            <div class="security-notice">
                <div class="security-notice-title">
                    <i class="fas fa-shield-alt"></i> Security Reminder
                </div>
                <p class="security-notice-text">
                    If you did not request this update or if you notice any suspicious activity, 
                    please contact our support team immediately. Always keep your credentials secure 
                    and do not share them with anyone.
                </p>
            </div>
            
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
                Thank you for keeping your information up to date. Have an amazing tour!
            </p>
            
            <p style="text-align: left; color: #1e293b; font-size: 16px; margin-top: 20px;">
                Warm regards,<br>
                <strong>Team Travclicks</strong><br>
                <span style="font-size: 14px; color: #667eea; font-weight: 600;">Travel Technology Transformed</span>
            </p>
        </div>
        
        <!-- Email Footer -->
        <div class="email-footer">
            <p class="footer-text" style="font-size: 14px; line-height: 1.8;">
                This invitation was initiated by <strong>{{ $dmc_company_name ?? 'Your DMC' }}</strong> via the Travclicks platform. 
                For any support, please contact <a href="mailto:support@travclicks.com" style="color: #667eea; text-decoration: none;">support@travclicks.com</a> or your DMC directly.
            </p>
        </div>
    </div>
</body>
</html>

