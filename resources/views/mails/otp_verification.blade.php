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
    
    .email-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 40px 30px;
        text-align: center;
        color: white;
    }
    
    .email-body {
        padding: 40px;
    }
    
    .otp-box {
        font-size: 36px;
        letter-spacing: 8px;
        text-align: center;
        padding: 20px;
        background-color: #f0f0f0;
        border-radius: 12px;
        margin: 30px 0;
        font-weight: bold;
    }
    
    .email-footer {
        background: #f1f5f9;
        padding: 30px;
        text-align: center;
        font-size: 14px;
        color: #64748b;
    }
</style>
<div class="email-container">
    <div class="email-header">
        <h1>OTP Verification</h1>
    </div>
    <div class="email-body">
        <p>Hello {{ $name }},</p>
        <p>Please use the following OTP to complete your registration:</p>
        <div class="otp-box">{{ $otp }}</div>
        <p>This OTP will expire in 10 minutes.</p>
    </div>
    <div class="email-footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}</p>
    </div>
</div>