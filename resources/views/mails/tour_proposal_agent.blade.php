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
                    <span style="font-size: 32px;">✈️</span>
                    <span>New Travel Proposal</span>
                </h1>
                <div style="display: inline-block; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 8px 20px; border-radius: 20px; font-size: 14px; font-weight: 600; margin-top: 12px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                    ✓ Tour Successfully Created
                </div>
            </td>
        </tr>
        
        <!-- Email Body -->
        <tr>
            <td style="padding: 45px 40px; color: #333;">
                
                <!-- Greeting -->
                <p style="font-size: 20px; margin-bottom: 8px; color: #1e293b; font-weight: 600;">Hi {{ $agent_name ?? 'Valued Partner' }},</p>
                <p style="font-size: 18px; margin-bottom: 30px; color: #64748b; font-weight: 500;">{{ $agency_name ?? 'Travel Agency' }}</p>
                
                <!-- Main Message -->
                <p style="font-size: 16px; line-height: 1.8; margin-bottom: 20px; color: #475569;">
                    <strong>Great news!</strong> <span style="font-weight: 700; color: #4f46e5;">{{ $dmc_name ?? 'DMC' }}</span> has just responded to your requirement 
                    @if(isset($query_date))
                        <strong>{{ $query_date }}</strong>
                    @endif
                    for <span style="font-weight: 700; color: #4f46e5;">{{ $destination ?? 'your destination' }}</span> and shared a customized travel proposal with you via the <span style="font-weight: 700; color: #4f46e5;">Travclicks</span> platform.
                </p>
                
                <!-- Highlight Box -->
                <div style="background-color: #f0f9ff; border-left: 4px solid #3b82f6; padding: 20px; margin: 25px 0; border-radius: 8px; font-size: 15px; color: #1e40af; line-height: 1.7;">
                    You can now review the full details of your proposal, negotiate, and communicate directly with the DMC all through your Travclicks dashboard — all in one seamless place.
                </div>
                
                <!-- Tour Details Box -->
                <div style="background: linear-gradient(135deg, #f8faff 0%, #f1f5f9 100%); border-radius: 12px; padding: 25px 30px; margin: 35px 0; border: 2px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                    <h3 style="font-size: 18px; font-weight: 700; margin: 0 0 20px 0; color: #4f46e5;">
                        <span style="font-size: 20px;">📋</span> Your Tour Package Details
                    </h3>
                    
                    <!-- Tour ID -->
                    <div style="background: white; padding: 16px 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 15px;">
                        <div style="display: table; width: 100%;">
                            <div style="display: table-cell; vertical-align: middle; width: 40px;">
                                <span style="font-size: 24px;">📋</span>
                            </div>
                            <div style="display: table-cell; vertical-align: middle;">
                                <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Tour Display ID</div>
                                <div style="font-size: 16px; color: #1e293b; font-weight: 600;">{{ $tour_display_id ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Destination -->
                    <div style="background: white; padding: 16px 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 15px;">
                        <div style="display: table; width: 100%;">
                            <div style="display: table-cell; vertical-align: middle; width: 40px;">
                                <span style="font-size: 24px;">🌍</span>
                            </div>
                            <div style="display: table-cell; vertical-align: middle;">
                                <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Destination</div>
                                <div style="font-size: 16px; color: #1e293b; font-weight: 600;">{{ $destination ?? 'N/A' }}@if(isset($city) && $city), {{ $city }}@endif</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Travel Dates -->
                    <div style="background: white; padding: 16px 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 15px;">
                        <div style="display: table; width: 100%;">
                            <div style="display: table-cell; vertical-align: middle; width: 40px;">
                                <span style="font-size: 24px;">📅</span>
                            </div>
                            <div style="display: table-cell; vertical-align: middle;">
                                <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Travel Dates</div>
                                <div style="font-size: 16px; color: #1e293b; font-weight: 600;">{{ $check_in_date ?? 'N/A' }} - {{ $check_out_date ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Guests with Breakdown -->
                    <div style="background: white; padding: 16px 20px; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <div style="display: table; width: 100%;">
                            <div style="display: table-cell; vertical-align: top; width: 40px;">
                                <span style="font-size: 24px;">👥</span>
                            </div>
                            <div style="display: table-cell; vertical-align: top;">
                                <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Guests</div>
                                <div style="font-size: 16px; color: #1e293b; font-weight: 600; margin-bottom: 8px;">{{ $total_guests ?? 'N/A' }} Total Guests</div>
                                
                                <!-- Guest Breakdown -->
                                <div style="margin-top: 8px;">
                                    @if(isset($adults) && $adults > 0)
                                    <div style="display: inline-block; background: #f8fafc; padding: 6px 12px; border-radius: 6px; border: 1px solid #e2e8f0; margin-right: 8px; margin-bottom: 8px;">
                                        <span style="font-size: 16px;">👤</span>
                                        <span style="font-size: 14px; color: #475569; font-weight: 500;">Adults: <span style="font-weight: 700; color: #4f46e5;">{{ $adults }}</span></span>
                                    </div>
                                    @endif
                                    @if(isset($children) && $children > 0)
                                    <div style="display: inline-block; background: #f8fafc; padding: 6px 12px; border-radius: 6px; border: 1px solid #e2e8f0; margin-right: 8px; margin-bottom: 8px;">
                                        <span style="font-size: 16px;">👶</span>
                                        <span style="font-size: 14px; color: #475569; font-weight: 500;">Children: <span style="font-weight: 700; color: #4f46e5;">{{ $children }}</span></span>
                                    </div>
                                    @endif
                                    @if(isset($infants) && $infants > 0)
                                    <div style="display: inline-block; background: #f8fafc; padding: 6px 12px; border-radius: 6px; border: 1px solid #e2e8f0; margin-bottom: 8px;">
                                        <span style="font-size: 16px;">🍼</span>
                                        <span style="font-size: 14px; color: #475569; font-weight: 500;">Infants: <span style="font-weight: 700; color: #4f46e5;">{{ $infants }}</span></span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <p style="font-size: 16px; line-height: 1.8; margin-bottom: 20px; color: #475569;">
                    Please log in to review the proposal details, discuss inclusions, or request adjustments in real-time.
                </p>
                
                <!-- CTA Section -->
                <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 12px; padding: 25px 30px; text-align: center; margin: 35px 0; border: 2px solid #fbbf24; box-shadow: 0 4px 12px rgba(251, 191, 36, 0.2);">
                    <p style="margin: 0 0 15px 0; font-size: 17px; color: #92400e; font-weight: 600;">
                        <span style="font-size: 24px; margin-right: 8px;">👉</span> Ready to Review Your Proposal?
                    </p>
                    <a href="{{ $dashboard_link ?? url('/agent/dashboard') }}" style="display: inline-block; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: white !important; text-decoration: none; padding: 16px 36px; border-radius: 10px; font-weight: 700; font-size: 17px; margin: 15px 0 10px 0; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); letter-spacing: 0.5px;">Access Your Dashboard</a>
                </div>
                
                <!-- Benefits Section -->
                <div style="margin: 30px 0;">
                    <p style="font-size: 16px; font-weight: 600; margin-bottom: 15px; color: #1e293b;">Why Travclicks?</p>
                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                        <tr>
                            <td style="padding: 12px 0; padding-left: 32px; position: relative; font-size: 16px; color: #475569; line-height: 1.7; border-bottom: 1px solid #f1f5f9;">
                                <span style="position: absolute; left: 8px; color: #10b981; font-weight: bold; font-size: 18px;">✓</span>
                                <strong>Organized:</strong> Travclicks keeps all your destination communications organized, secure, and instantly accessible.
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px 0; padding-left: 32px; position: relative; font-size: 16px; color: #475569; line-height: 1.7; border-bottom: 1px solid #f1f5f9;">
                                <span style="position: absolute; left: 8px; color: #10b981; font-weight: bold; font-size: 18px;">✓</span>
                                <strong>Connected:</strong> Brings every part of your collaboration — proposals, conversations, and updates — into one connected workspace.
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px 0; padding-left: 32px; position: relative; font-size: 16px; color: #475569; line-height: 1.7;">
                                <span style="position: absolute; left: 8px; color: #10b981; font-weight: bold; font-size: 18px;">✓</span>
                                <strong>Efficient:</strong> Respond faster and close deals smarter with real-time collaboration tools.
                            </td>
                        </tr>
                    </table>
                </div>
                
                <p style="font-size: 16px; line-height: 1.8; margin-bottom: 20px; color: #475569;">
                    Thank you for using <span style="font-weight: 700; color: #4f46e5;">Travclicks</span> — where travel technology transforms collaboration.
                </p>
                
                <!-- Signature -->
                <div style="margin-top: 40px; font-size: 16px; color: #1e293b;">
                    <p style="margin: 5px 0;">Warm regards,</p>
                    <p style="font-weight: 700; color: #4f46e5; margin-top: 8px; font-size: 18px;">Team Travclicks</p>
                    <p style="font-style: italic; color: #64748b; font-size: 15px; margin-top: 8px;">Travel Technology Transformed</p>
                </div>
                
            </td>
        </tr>
        
        <!-- Email Footer -->
        <tr>
            <td style="background: #f8fafc; padding: 25px 40px; text-align: center; font-size: 14px; color: #64748b; border-top: 1px solid #e2e8f0; line-height: 1.7;">
                <p style="margin: 0 0 15px 0; font-style: italic; color: #4f46e5; font-weight: 600; font-size: 15px;">
                    *Empowering Destination Management through Smart Automation*
                </p>
                <p style="margin: 0 0 15px 0;">
                    This proposal was sent by <strong>{{ $dmc_name ?? 'DMC' }}</strong> via the Travclicks platform. 
                    For any support, please contact 
                    <a href="mailto:support@travclicks.com" style="color: #4f46e5; text-decoration: none; font-weight: 600;">support@travclicks.com</a>
                    @if(isset($dmc_email) || isset($dmc_phone))
                        or reach out to your DMC directly
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
                <p style="margin: 15px 0 0 0; font-size: 13px; color: #94a3b8; line-height: 1.6;">
                    This is an automated message from Travclicks. Do not reply to this email. For support, contact 
                    <a href="mailto:support@travclicks.com" style="color: #4f46e5; text-decoration: none; font-weight: 600;">support@travclicks.com</a>
                </p>
            </td>
        </tr>
        
    </table>
</div>
