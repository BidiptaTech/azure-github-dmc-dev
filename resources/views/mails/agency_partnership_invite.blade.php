<div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f5f5; margin: 0; padding: 20px 0; color: #333; line-height: 1.6;">
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width: 650px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
        
        <!-- Email Header with Logo -->
        <tr>
            <td style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 35px 40px; text-align: center; color: white;">
                @if(isset($dmc_logo) && $dmc_logo)
                <div style="margin-bottom: 20px;">
                    <img src="{{ $dmc_logo }}" alt="DMC Logo" style="max-width: 100px; height: auto; border-radius: 8px; border: 3px solid rgba(255,255,255,0.9); box-shadow: 0 4px 12px rgba(0,0,0,0.15); background: white; padding: 6px; display: inline-block;">
                </div>
                @endif
                <h1 style="margin: 0; font-size: 28px; font-weight: 700; letter-spacing: 0.5px; color: white;">
                    <span style="font-size: 32px;">🌍</span>
                    <span>New Partnership Invitation</span>
                </h1>
                <div style="display: inline-block; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 8px 20px; border-radius: 20px; font-size: 14px; font-weight: 600; margin-top: 12px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                    ✓ You've Been Added as a Trusted Partner
                </div>
            </td>
        </tr>
        
        <!-- Email Body -->
        <tr>
            <td style="padding: 45px 40px; color: #333;">
                
                <!-- Greeting -->
                <p style="font-size: 20px; margin-bottom: 8px; color: #1e293b; font-weight: 600;">Hi {{ $agency_name ?? 'Valued Partner' }},</p>
                <p style="font-size: 18px; margin-bottom: 30px; color: #64748b; font-weight: 500;">{{ $company_name ?? 'Travel Agency' }}</p>
                
                <!-- Main Message -->
                <p style="font-size: 16px; line-height: 1.8; margin-bottom: 20px; color: #475569;">
                    <strong>Good news!</strong> <span style="font-weight: 700; color: #4f46e5;">{{ $dmc_name ?? 'DMC' }}</span> has added your agency as a trusted partner on the <span style="font-weight: 700; color: #4f46e5;">Travclicks</span> platform.
                </p>
                
                <!-- Highlight Box -->
                <div style="background-color: #f0f9ff; border-left: 4px solid #3b82f6; padding: 22px 25px; margin: 30px 0; border-radius: 10px; font-size: 15px; color: #1e40af; line-height: 1.8; box-shadow: 0 2px 8px rgba(59, 130, 246, 0.15);">
                    You're already part of the Travclicks ecosystem — now you can collaborate directly with <strong>{{ $dmc_name ?? 'DMC' }}</strong> to access new destinations, rates, and opportunities.
                </div>
                
                <!-- CTA Section -->
                <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 12px; padding: 25px 30px; text-align: center; margin: 35px 0; border: 2px solid #fbbf24; box-shadow: 0 4px 12px rgba(251, 191, 36, 0.2);">
                    <p style="margin: 0 0 15px 0; font-size: 17px; color: #92400e; font-weight: 600;">
                        <span style="font-size: 24px; margin-right: 8px;">👉</span> Ready to Start Collaborating?
                    </p>
                    <a href="{{ $dashboard_link ?? url('/agent/dashboard') }}" style="display: inline-block; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: white !important; text-decoration: none; padding: 16px 36px; border-radius: 10px; font-weight: 700; font-size: 17px; margin: 15px 0 10px 0; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); letter-spacing: 0.5px;">View Your Travclicks Dashboard</a>
                </div>
                
                <!-- What You Can Do Section -->
                <div style="margin: 35px 0;">
                    <p style="font-size: 17px; font-weight: 700; margin-bottom: 20px; color: #1e293b; text-align: center;">
                        <span style="font-size: 20px;">💡</span> Here's what you can do right away:
                    </p>
                    
                    <!-- Action Items -->
                    <div style="background: white; padding: 16px 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div style="display: table; width: 100%;">
                            <div style="display: table-cell; vertical-align: middle; width: 35px;">
                                <span style="font-size: 20px; color: #10b981; font-weight: bold;">•</span>
                            </div>
                            <div style="display: table-cell; vertical-align: middle;">
                                <span style="font-size: 15px; color: #475569; line-height: 1.7;">Send new travel queries and requests to <strong style="color: #4f46e5;">{{ $dmc_name ?? 'DMC' }}</strong></span>
                            </div>
                        </div>
                    </div>
                    
                    <div style="background: white; padding: 16px 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div style="display: table; width: 100%;">
                            <div style="display: table-cell; vertical-align: middle; width: 35px;">
                                <span style="font-size: 20px; color: #10b981; font-weight: bold;">•</span>
                            </div>
                            <div style="display: table-cell; vertical-align: middle;">
                                <span style="font-size: 15px; color: #475569; line-height: 1.7;">Receive proposals, updates, and chat in real time</span>
                            </div>
                        </div>
                    </div>
                    
                    <div style="background: white; padding: 16px 20px; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div style="display: table; width: 100%;">
                            <div style="display: table-cell; vertical-align: middle; width: 35px;">
                                <span style="font-size: 20px; color: #10b981; font-weight: bold;">•</span>
                            </div>
                            <div style="display: table-cell; vertical-align: middle;">
                                <span style="font-size: 15px; color: #475569; line-height: 1.7;">Track all communications and bookings in one unified workspace</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <p style="font-size: 16px; line-height: 1.8; margin: 30px 0 20px 0; color: #475569;">
                    <span style="font-weight: 700; color: #4f46e5;">Travclicks</span> connects you seamlessly with multiple DMCs worldwide — helping you grow your partnerships, respond faster, and scale smarter.
                </p>
                
                <!-- Signature -->
                <div style="margin-top: 45px; font-size: 16px; color: #1e293b;">
                    <p style="margin: 5px 0;">Warm regards,</p>
                    <p style="font-weight: 700; color: #4f46e5; margin-top: 8px; font-size: 18px;">Team Travclicks</p>
                    <p style="font-style: italic; color: #64748b; font-size: 15px; margin-top: 8px;">Empowering Connected Travel Networks</p>
                </div>
                
            </td>
        </tr>
        
        <!-- Email Footer -->
        <tr>
            <td style="background: #f8fafc; padding: 25px 40px; text-align: center; font-size: 14px; color: #64748b; border-top: 1px solid #e2e8f0; line-height: 1.7;">
                <p style="margin: 0;">
                    This partnership was initiated by <strong>{{ $dmc_name ?? 'DMC' }}</strong> through the Travclicks platform. 
                    For assistance, please contact 
                    <a href="mailto:support@travclicks.com" style="color: #4f46e5; text-decoration: none; font-weight: 600;">support@travclicks.com</a>
                    @if(isset($dmc_email) || isset($dmc_phone))
                        or your DMC directly
                        @if(isset($dmc_email))
                            at <a href="mailto:{{ $dmc_email }}" style="color: #4f46e5; text-decoration: none; font-weight: 600;">{{ $dmc_email }}</a>
                        @endif
                        @if(isset($dmc_email) && isset($dmc_phone))
                            or
                        @endif
                        @if(isset($dmc_phone))
                            {{ $dmc_phone }}
                        @endif
                    @endif.
                </p>
            </td>
        </tr>
        
    </table>
</div>
