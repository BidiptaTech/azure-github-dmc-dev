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
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
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
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
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
        box-shadow: 0 4px 6px rgba(124, 58, 237, 0.1);
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
        color: rgba(124, 58, 237, 0.3);
    }
    
    .summary-title {
        font-size: 20px;
        font-weight: 700;
        margin-top: 0;
        margin-bottom: 25px;
        color: #6d28d9;
        padding-bottom: 12px;
        border-bottom: 2px solid #c4b5fd;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .summary-title i {
        color: #6d28d9;
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
        color: #6d28d9;
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
        color: #6d28d9;
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
        background: linear-gradient(to right, #4f46e5, #7c3aed);
        border-radius: 3px;
    }
    
    .section-title i {
        color: #6d28d9;
        font-size: 22px;
    }
    
    /* Enhanced Login Info Box */
    .login-box {
        background: linear-gradient(135deg, #f8faff 0%, #f1f5f9 100%);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
        margin-bottom: 35px;
        position: relative;
    }
    
    .login-box::before {
        content: '\f2f6';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        top: 15px;
        right: 20px;
        font-size: 24px;
        color: rgba(99, 102, 241, 0.3);
    }
    
    .login-header {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: white;
        padding: 16px 25px;
        font-weight: 700;
        font-size: 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .login-header i {
        font-size: 16px;
        margin-right: 8px;
    }
    
    .login-content {
        padding: 25px;
    }
    
    .login-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .login-row:last-child {
        border-bottom: none;
    }
    
    .login-label {
        font-weight: 600;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 8px;
        width: 40%;
    }
    
    .login-label i {
        color: #6d28d9;
        font-size: 14px;
    }
    
    .login-value {
        color: #1e293b;
        font-weight: 500;
        text-align: right;
        width: 60%;
        word-break: break-all;
        font-family: 'Courier New', Courier, monospace;
        background: #f8fafc;
        padding: 8px 12px;
        border-radius: 6px;
        border: 1px dashed #cbd5e1;
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
        content: '\f05a';
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
        margin: 0 0 15px 0;
        color: #065f46;
        line-height: 1.6;
    }
    
    .instructions-box ul {
        margin: 0;
        padding-left: 20px;
        color: #065f46;
    }
    
    .instructions-box li {
        margin-bottom: 8px;
    }
    
    .instructions-box li:last-child {
        margin-bottom: 0;
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
        color: #6d28d9;
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
        color: #6d28d9;
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
        color: #6d28d9;
        text-decoration: none;
        margin: 0 15px;
        font-weight: 500;
        transition: color 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .footer-link:hover {
        color: #4c1d95;
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
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: white;
        border-radius: 12px;
        text-decoration: none;
        font-weight: bold;
        transition: all 0.3s ease;
        box-shadow: 0 4px 8px rgba(124, 58, 237, 0.3);
    }
    
    .social-link:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 6px 12px rgba(124, 58, 237, 0.4);
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
            font-size: 14px;
            padding: 14px 25px;
            max-width: 280px;
        }
        
        .login-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
        
        .login-label, .login-value {
            width: 100%;
            text-align: left;
        }
    }
</style>

<div class="email-container">
    <!-- Enhanced Email Header -->
    <div class="email-header">
        <div class="logo-container">
            <img src="{{ $company['logo'] ?? asset('images/logo.png') }}" alt="{{ $company['companyName'] ?? config('app.name') }}" class="logo">
        </div>
        <h1 class="welcome-title">
            <i class="fas fa-user-plus"></i> Welcome, {{ $name ?? 'Agent' }}!
        </h1>
        <p class="welcome-subtitle">Your account has been created successfully</p>
        <div class="welcome-badge">
            <i class="fas fa-handshake"></i> New Partner
        </div>
    </div>

    
    <!-- Enhanced Email Body -->
    <div class="email-body">
        <p class="greeting">
            Dear {{ $salutation ?? '' }} {{ $name ?? 'Agent' }}, <i class="fas fa-user-tie"></i>
        </p>
        
        <p class="intro-text">
            Welcome to {{ $company['companyName'] ?? config('app.name') }}! We're thrilled to have you join our network of trusted agents. Your account has been successfully created, and you're now ready to access our platform and start managing your bookings and services.
        </p>
        
        <!-- Enhanced Account Summary Box -->
        <div class="account-summary">
            <h3 class="summary-title">
                <i class="fas fa-id-card"></i> Your Agent Details
            </h3>
            <div class="summary-grid">
                <div class="summary-item">
                    <span class="summary-label">
                        <i class="fas fa-user"></i> Agent Name
                    </span>
                    <span class="summary-value">{{ $name ?? 'Not provided' }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">
                        <i class="fas fa-building"></i> Company
                    </span>
                    <span class="summary-value">{{ $company_name ?? 'Not provided' }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">
                        <i class="fas fa-envelope"></i> Email
                    </span>
                    <span class="summary-value">{{ $email ?? 'Not provided' }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">
                        <i class="fas fa-phone"></i> Phone
                    </span>
                    <span class="summary-value">{{ $phone ?? 'Not provided' }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">
                        <i class="fas fa-globe"></i> Country
                    </span>
                    <span class="summary-value">{{ $country ?? 'Not provided' }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">
                        <i class="fas fa-city"></i> City
                    </span>
                    <span class="summary-value">{{ $city ?? 'Not provided' }}</span>
                </div>
            </div>
        </div>
        
        <!-- Enhanced Login Information Section -->
        <div class="section-title">
            <i class="fas fa-sign-in-alt"></i> Login Information
        </div>
        <div class="login-box">
            <div class="login-header">
                <span>
                    <i class="fas fa-lock"></i> Your Credentials
                </span>
            </div>
            <div class="login-content">
                <div class="login-row">
                    <div class="login-label">
                        <i class="fas fa-envelope"></i> Email
                    </div>
                    <div class="login-value">{{ $email ?? 'Not provided' }}</div>
                </div>
                <div class="login-row">
                    <div class="login-label">
                        <i class="fas fa-key"></i> Password
                    </div>
                    <div class="login-value">{{ $password ?? 'Contact administrator' }}</div>
                </div>
            </div>
        </div>
        
        <!-- Enhanced Instructions Box -->
        <div class="section-title">
            <i class="fas fa-info-circle"></i> Next Steps
        </div>
        <div class="instructions-box">
            <h4 class="instructions-title">
                <i class="fas fa-list-check"></i> Getting Started
            </h4>
            <p>To start using our platform, please follow these steps:</p>
            <ul>
                <li>Log in using the credentials provided above</li>
                <li>Complete your profile by adding any missing information</li>
                <li>Change your password to something secure that you'll remember</li>
                <li>Explore the dashboard and familiarize yourself with the features</li>
                <li>Check out our available services and packages</li>
            </ul>
            <p style="margin-top: 15px;">For security reasons, we recommend changing your password immediately after your first login.</p>
        </div>
        
        <p style="text-align: center; color: #64748b; font-size: 15px; line-height: 1.6; margin-bottom: 25px;">
            Click the button below to access your account. If you have any questions or need assistance, please don't hesitate to contact our support team.
        </p>
        
        <!-- Enhanced CTA Button - Email Client Compatible -->
        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top: 35px; margin-bottom: 35px;">
            <tr>
                <td align="center">
                    <table border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td align="center" style="border-radius: 12px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);" bgcolor="#4f46e5">
                                <a href="{{ url('login') }}" target="_blank" style="font-size: 16px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #ffffff; text-decoration: none; padding: 16px 30px; border-radius: 12px; border: 1px solid #4f46e5; display: inline-block; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="fas fa-sign-in-alt" style="margin-right: 10px;"></i> Login to Your Account
                                </a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        
        <div style="text-align: center; margin-top: 30px;">
            <p style="font-size: 16px; color: #1e293b; font-weight: 600; margin-bottom: 8px;">
                <i class="fas fa-heart" style="color: #6d28d9;"></i> Thank you for partnering with us!
            </p>
            
            <p style="font-size: 15px; color: #64748b; margin-bottom: 20px;">
                We look forward to a successful and profitable relationship. Together, we'll create exceptional experiences for our mutual clients.
            </p>
            
            <p style="font-size: 16px; color: #1e293b;">
                Warm regards,<br>
                <strong> {{ $company['companyName'] ?? config('app.name') }} </strong>
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
        
        
        <p class="copyright">
            &copy; {{ date('Y') }} {{ $company['companyName'] }}. All rights reserved.
        </p>
    </div>
</div> 