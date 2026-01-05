<style>
    /* Modern Email Styles */
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f5f5f5;
        margin: 0;
        padding: 0;
        color: #333;
        line-height: 1.6;
    }
    
    .email-container {
        max-width: 650px;
        margin: 30px auto;
        background-color: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    
    /* Header with Logo */
    .email-header {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        padding: 35px 40px;
        text-align: center;
        color: white;
        position: relative;
        overflow: hidden;
    }
    
    .email-header::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
        background-size: 30px 30px;
        animation: float 20s linear infinite;
    }
    
    @keyframes float {
        0% { transform: translate(0, 0); }
        100% { transform: translate(30px, 30px); }
    }
    
    .logo-container {
        margin-bottom: 20px;
        position: relative;
        z-index: 2;
    }
    
    .logo {
        max-width: 160px;
        height: auto;
        border-radius: 12px;
        border: 4px solid rgba(255,255,255,0.9);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        background: white;
        padding: 8px;
    }
    
    .header-title {
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
    
    .header-emoji {
        font-size: 32px;
    }
    
    /* Email Body */
    .email-body {
        padding: 45px 40px;
        color: #333;
    }
    
    .greeting {
        font-size: 20px;
        margin-bottom: 8px;
        color: #1e293b;
        font-weight: 600;
    }
    
    .company-name-line {
        font-size: 18px;
        margin-bottom: 30px;
        color: #64748b;
        font-weight: 500;
    }
    
    .paragraph {
        font-size: 16px;
        line-height: 1.8;
        margin-bottom: 20px;
        color: #475569;
    }
    
    .highlight-text {
        font-weight: 700;
        color: #4f46e5;
    }
    
    /* Benefits List */
    .benefits-section {
        margin: 30px 0;
        padding-left: 0;
    }
    
    .benefits-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #1e293b;
    }
    
    .benefits-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .benefits-list li {
        padding: 12px 0;
        padding-left: 32px;
        position: relative;
        font-size: 16px;
        color: #475569;
        line-height: 1.7;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .benefits-list li:last-child {
        border-bottom: none;
    }
    
    .benefits-list li::before {
        content: "•";
        position: absolute;
        left: 8px;
        color: #4f46e5;
        font-weight: bold;
        font-size: 24px;
        top: 8px;
    }
    
    /* CTA Button Section */
    .cta-section {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-radius: 12px;
        padding: 25px 30px;
        text-align: center;
        margin: 35px 0;
        border: 2px solid #fbbf24;
        box-shadow: 0 4px 12px rgba(251, 191, 36, 0.2);
    }
    
    .cta-emoji {
        font-size: 24px;
        margin-right: 8px;
    }
    
    .cta-link {
        display: inline-block;
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        color: white !important;
        text-decoration: none;
        padding: 16px 36px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 17px;
        margin: 15px 0 10px 0;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        transition: all 0.3s ease;
        letter-spacing: 0.5px;
    }
    
    .cta-link:hover {
        background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
    }
    
    /* Agent Credentials Box */
    .credentials-box {
        background: linear-gradient(135deg, #f8faff 0%, #f1f5f9 100%);
        border-radius: 12px;
        padding: 25px 30px;
        margin: 35px 0;
        border: 2px solid #e2e8f0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .credentials-title {
        font-size: 18px;
        font-weight: 700;
        margin: 0 0 20px 0;
        color: #4f46e5;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .credentials-title::before {
        content: "🔐";
        font-size: 20px;
    }
    
    .credentials-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }
    
    .credential-item {
        background: white;
        padding: 14px 18px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }
    
    .credential-label {
        font-size: 13px;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    
    .credential-value {
        font-size: 15px;
        color: #1e293b;
        font-weight: 600;
        word-break: break-word;
    }
    
    .credential-value.password {
        font-family: 'Courier New', Courier, monospace;
        color: #4f46e5;
        background: #f8fafc;
        padding: 6px 10px;
        border-radius: 6px;
        border: 1px dashed #cbd5e1;
    }
    
    /* Signature Section */
    .signature {
        margin-top: 40px;
        font-size: 16px;
        color: #1e293b;
    }
    
    .signature-name {
        font-weight: 700;
        color: #4f46e5;
        margin-top: 8px;
        font-size: 18px;
    }
    
    .tagline {
        font-style: italic;
        color: #64748b;
        font-size: 15px;
        margin-top: 8px;
    }
    
    /* Footer */
    .email-footer {
        background: #f8fafc;
        padding: 25px 40px;
        text-align: center;
        font-size: 14px;
        color: #64748b;
        border-top: 1px solid #e2e8f0;
        line-height: 1.7;
    }
    
    .footer-link {
        color: #4f46e5;
        text-decoration: none;
        font-weight: 600;
    }
    
    .footer-link:hover {
        text-decoration: underline;
    }
    
    /* Responsive Design */
    @media only screen and (max-width: 600px) {
        .email-container {
            margin: 10px;
            border-radius: 8px;
        }
        
        .email-body {
            padding: 30px 25px;
        }
        
        .email-header {
            padding: 25px 20px;
        }
        
        .logo {
            max-width: 120px;
        }
        
        .header-title {
            font-size: 22px;
            flex-direction: column;
            gap: 8px;
        }
        
        .credentials-grid {
            grid-template-columns: 1fr;
        }
        
        .cta-link {
            display: block;
            width: 100%;
            box-sizing: border-box;
            padding: 14px 20px;
        }
        
        .email-footer {
            padding: 20px 15px;
        }
    }
</style>

<div class="email-container">
    <!-- Email Header with Logo -->
    <div class="email-header">
        <div class="logo-container">
            @php
                $logoSetting = \App\Helpers\CommonHelper::masterSettingsName('logo');
                $logo = $logoSetting['master_value'] ?? $dmc_logo ?? asset('images/logo.png');
            @endphp
            <img src="{{ $logo }}" alt="Travclicks" class="logo">
        </div>
        <h1 class="header-title">
            <span class="header-emoji">🌏</span>
            <span>Welcome to Travclicks</span>
        </h1>
    </div>
    
    <!-- Email Body -->
    <div class="email-body">
        <p class="greeting">Hi {{ $name ?? 'Agent' }},</p>
        <p class="company-name-line">{{ $company_name ?? 'Travel Agent Company' }},</p>
        
        <p class="paragraph">
            Welcome aboard! <span class="highlight-text">{{ $dmc_company ?? config('app.name') }}</span> has registered your agency on the <span class="highlight-text">Travclicks</span> platform — your new gateway to smarter, faster, and more connected destination management.
        </p>
        
        <p class="benefits-title">Through your Travclicks dashboard, you can:</p>
        <ul class="benefits-list">
            <li>Send and manage travel queries directly with <strong>{{ $dmc_company ?? config('app.name') }}</strong></li>
            <li>Receive proposals, updates, and messages in real time</li>
            <li>Collaborate seamlessly across destinations and time zones</li>
        </ul>
        
        <!-- CTA Section -->
        <div class="cta-section">
            <p style="margin: 0 0 15px 0; font-size: 17px; color: #92400e; font-weight: 600;">
                <span class="cta-emoji">👉</span> Ready to Get Started?
            </p>
            <a href="{{ $activation_link ?? url('login') }}" class="cta-link">Set Up Your Travclicks Access</a>
        </div>
        
        <p class="paragraph">
            Once you log in, you can explore destinations managed by <strong>{{ $dmc_company ?? config('app.name') }}</strong>, connect with verified DMCs, and start building your business network — all from one intuitive platform.
        </p>
        
        <p class="paragraph">
            Welcome to the <span class="highlight-text">Travclicks</span> ecosystem, where technology meets travel collaboration.
        </p>
        
        <!-- Agent Credentials Box -->
        <div class="credentials-box">
            <h3 class="credentials-title">Your Login Credentials</h3>
            <div class="credentials-grid">
                <div class="credential-item">
                    <div class="credential-label">Salutation</div>
                    <div class="credential-value">{{ $salutation ?? 'N/A' }}</div>
                </div>
                <div class="credential-item">
                    <div class="credential-label">Full Name</div>
                    <div class="credential-value">{{ $name ?? 'N/A' }}</div>
                </div>
                <div class="credential-item">
                    <div class="credential-label">Email Address</div>
                    <div class="credential-value">{{ $email ?? 'N/A' }}</div>
                </div>
                <div class="credential-item">
                    <div class="credential-label">Phone Number</div>
                    <div class="credential-value">{{ $phone ?? 'N/A' }}</div>
                </div>
                <div class="credential-item">
                    <div class="credential-label">Company Name</div>
                    <div class="credential-value">{{ $company_name ?? 'N/A' }}</div>
                </div>
                <div class="credential-item">
                    <div class="credential-label">Password</div>
                    <div class="credential-value password">{{ $password ?? 'Contact administrator' }}</div>
                </div>
            </div>
        </div>
        
        <!-- Signature -->
        <div class="signature">
            <p style="margin: 5px 0;">Warm regards,</p>
            <p class="signature-name">Team Travclicks</p>
            <p class="tagline">Travel Technology Transformed</p>
        </div>
    </div>
    
    <!-- Email Footer -->
    <div class="email-footer">
        <p style="margin: 0;">
            This invitation was initiated by <strong>{{ $dmc_company ?? config('app.name') }}</strong> via the Travclicks platform. 
            For any support, please contact 
            <a href="mailto:support@travclicks.com" class="footer-link">support@travclicks.com</a> 
            or your DMC directly.
        </p>
    </div>
</div>
