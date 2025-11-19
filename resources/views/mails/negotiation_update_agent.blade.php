<div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f5f5; margin: 0; padding: 20px 0; color: #333; line-height: 1.6;">
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width: 650px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
        
        <!-- Email Header with Logo -->
        <tr>
            <td style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 35px 40px; text-align: center; color: white;">
                @if(isset($dmc_logo) && $dmc_logo)
                <div style="margin-bottom: 20px;">
                    <img src="{{ $dmc_logo }}" alt="DMC Logo" style="max-width: 100px; height: auto; border-radius: 8px; border: 3px solid rgba(255,255,255,0.9); box-shadow: 0 4px 12px rgba(0,0,0,0.15); background: white; padding: 6px; display: inline-block;">
                </div>
                @endif
                <h1 style="margin: 0; font-size: 28px; font-weight: 700; letter-spacing: 0.5px; color: white;">
                    <span style="font-size: 32px;">💰</span>
                    <span>Price Negotiation Update</span>
                </h1>
                <div style="display: inline-block; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 8px 20px; border-radius: 20px; font-size: 14px; font-weight: 600; margin-top: 12px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                    ✓ Negotiation Submitted Successfully
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
                    Thank you for submitting your price negotiation for tour <strong style="color: #4f46e5;">{{ $tour_display_id ?? 'N/A' }}</strong> via <span style="font-weight: 700; color: #4f46e5;">Travclicks</span>. Your negotiation has been successfully received and is now under review by <span style="font-weight: 700; color: #f59e0b;">{{ $dmc_name ?? 'DMC' }}</span>.
                </p>
                
                <!-- Highlight Box -->
                <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 20px; margin: 25px 0; border-radius: 8px; font-size: 15px; color: #92400e; line-height: 1.7;">
                    <strong>Note:</strong> The DMC team will review your negotiation and respond shortly. You'll be notified once they've made a decision. Track all updates in your Travclicks dashboard.
                </div>
                
                <!-- Price Details Box -->
                <div style="background: linear-gradient(135deg, #f8faff 0%, #f1f5f9 100%); border-radius: 12px; padding: 25px 30px; margin: 35px 0; border: 2px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                    <h3 style="font-size: 18px; font-weight: 700; margin: 0 0 20px 0; color: #f59e0b;">
                        <span style="font-size: 20px;">💵</span> Negotiation Summary
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
                    
                    <!-- Tour Status -->
                    <div style="background: white; padding: 16px 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 15px;">
                        <div style="display: table; width: 100%;">
                            <div style="display: table-cell; vertical-align: middle; width: 40px;">
                                <span style="font-size: 24px;">📊</span>
                            </div>
                            <div style="display: table-cell; vertical-align: middle;">
                                <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Current Tour Status</div>
                                <div style="font-size: 16px; font-weight: 600;">
                                    <span style="display: inline-block; background: 
                                        @if(isset($tour_status))
                                            @if($tour_status == 'New Enquiries') #3b82f6
                                            @elseif($tour_status == 'Prospect') #8b5cf6
                                            @elseif($tour_status == 'Tentative') #f59e0b
                                            @elseif($tour_status == 'Confirmed') #10b981
                                            @elseif($tour_status == 'Definite') #059669
                                            @else #6b7280
                                            @endif
                                        @else
                                            #6b7280
                                        @endif
                                    ; color: white; padding: 6px 16px; border-radius: 20px; font-size: 14px;">
                                        {{ $tour_status ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Destination -->
                    @if(isset($destination) && $destination)
                    <div style="background: white; padding: 16px 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 15px;">
                        <div style="display: table; width: 100%;">
                            <div style="display: table-cell; vertical-align: middle; width: 40px;">
                                <span style="font-size: 24px;">🌍</span>
                            </div>
                            <div style="display: table-cell; vertical-align: middle;">
                                <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Destination</div>
                                <div style="font-size: 16px; color: #1e293b; font-weight: 600;">{{ $destination }}@if(isset($city) && $city), {{ $city }}@endif</div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Actual Amount (Current Price) -->
                    <div style="background: white; padding: 16px 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 15px;">
                        <div style="display: table; width: 100%;">
                            <div style="display: table-cell; vertical-align: middle; width: 40px;">
                                <span style="font-size: 24px;">💵</span>
                            </div>
                            <div style="display: table-cell; vertical-align: middle;">
                                <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Current Price (Actual Amount)</div>
                                <div style="font-size: 18px; color: #1e293b; font-weight: 700;">{{ $currency ?? '$' }} {{ number_format($actual_amount ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Previous Negotiated Price (if exists) -->
                    @if(isset($previous_negotiated_amount) && $previous_negotiated_amount > 0 && $previous_negotiated_amount != $actual_amount)
                    <div style="background: white; padding: 16px 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 15px;">
                        <div style="display: table; width: 100%;">
                            <div style="display: table-cell; vertical-align: middle; width: 40px;">
                                <span style="font-size: 24px;">💲</span>
                            </div>
                            <div style="display: table-cell; vertical-align: middle;">
                                <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Previous Negotiated Price</div>
                                <div style="font-size: 16px; color: #64748b; font-weight: 600; text-decoration: line-through;">{{ $currency ?? '$' }} {{ number_format($previous_negotiated_amount, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Your Negotiated Price (New Offer) -->
                    <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 18px 20px; border-radius: 8px; border: 2px solid #f59e0b; box-shadow: 0 2px 8px rgba(245, 158, 11, 0.2);">
                        <div style="display: table; width: 100%;">
                            <div style="display: table-cell; vertical-align: middle; width: 40px;">
                                <span style="font-size: 28px;">🎯</span>
                            </div>
                            <div style="display: table-cell; vertical-align: middle;">
                                <div style="font-size: 12px; color: #92400e; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Your Negotiated Price</div>
                                <div style="font-size: 22px; color: #92400e; font-weight: 800;">{{ $currency ?? '$' }} {{ number_format($negotiated_amount ?? 0, 2) }}</div>
                                @if(isset($actual_amount) && isset($negotiated_amount) && $actual_amount > 0)
                                <div style="font-size: 13px; color: #92400e; font-weight: 600; margin-top: 6px;">
                                    Discount: {{ $currency ?? '$' }} {{ number_format($actual_amount - $negotiated_amount, 2) }} 
                                    ({{ number_format((($actual_amount - $negotiated_amount) / $actual_amount) * 100, 1) }}% off)
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Comment/Remarks -->
                    @if(isset($comment) && $comment)
                    <div style="background: #f8fafc; padding: 16px 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-top: 15px;">
                        <div style="display: table; width: 100%;">
                            <div style="display: table-cell; vertical-align: top; width: 40px;">
                                <span style="font-size: 24px;">💬</span>
                            </div>
                            <div style="display: table-cell; vertical-align: top;">
                                <div style="font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Your Remarks</div>
                                <div style="font-size: 15px; color: #475569; line-height: 1.6; font-style: italic;">"{{ $comment }}"</div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                
                <!-- What's Next Section -->
                <div style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-radius: 12px; padding: 25px 30px; margin: 30px 0; border: 2px solid #3b82f6;">
                    <h3 style="font-size: 18px; font-weight: 700; margin: 0 0 15px 0; color: #1e40af;">
                        <span style="font-size: 20px;">⏰</span> What Happens Next?
                    </h3>
                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                        <tr>
                            <td style="padding: 10px 0; padding-left: 32px; position: relative; font-size: 15px; color: #1e40af; line-height: 1.7;">
                                <span style="position: absolute; left: 8px; color: #3b82f6; font-weight: bold; font-size: 18px;">1.</span>
                                <strong>DMC Review:</strong> The DMC will review your negotiated price and remarks.
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 10px 0; padding-left: 32px; position: relative; font-size: 15px; color: #1e40af; line-height: 1.7;">
                                <span style="position: absolute; left: 8px; color: #3b82f6; font-weight: bold; font-size: 18px;">2.</span>
                                <strong>Decision:</strong> They'll either accept, counter-offer, or decline the negotiation.
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 10px 0; padding-left: 32px; position: relative; font-size: 15px; color: #1e40af; line-height: 1.7;">
                                <span style="position: absolute; left: 8px; color: #3b82f6; font-weight: bold; font-size: 18px;">3.</span>
                                <strong>Notification:</strong> You'll receive an email notification with their response.
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 10px 0; padding-left: 32px; position: relative; font-size: 15px; color: #1e40af; line-height: 1.7;">
                                <span style="position: absolute; left: 8px; color: #3b82f6; font-weight: bold; font-size: 18px;">4.</span>
                                <strong>Track Progress:</strong> Monitor status updates in your dashboard in real-time.
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- CTA Section -->
                <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 12px; padding: 25px 30px; text-align: center; margin: 35px 0; border: 2px solid #fbbf24; box-shadow: 0 4px 12px rgba(251, 191, 36, 0.2);">
                    <p style="margin: 0 0 15px 0; font-size: 17px; color: #92400e; font-weight: 600;">
                        <span style="font-size: 24px; margin-right: 8px;">👉</span> Track Your Negotiation Status
                    </p>
                    <a href="{{ $dashboard_link ?? url('/agent/dashboard') }}" style="display: inline-block; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: white !important; text-decoration: none; padding: 16px 36px; border-radius: 10px; font-weight: 700; font-size: 17px; margin: 15px 0 10px 0; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); letter-spacing: 0.5px;">View in Dashboard</a>
                </div>
                
                <!-- Tips Section -->
                <div style="margin: 30px 0;">
                    <p style="font-size: 16px; font-weight: 600; margin-bottom: 15px; color: #1e293b;">💡 Negotiation Tips:</p>
                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                        <tr>
                            <td style="padding: 12px 0; padding-left: 32px; position: relative; font-size: 16px; color: #475569; line-height: 1.7; border-bottom: 1px solid #f1f5f9;">
                                <span style="position: absolute; left: 8px; color: #10b981; font-weight: bold; font-size: 18px;">✓</span>
                                Be reasonable with your price negotiations to increase acceptance chances.
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px 0; padding-left: 32px; position: relative; font-size: 16px; color: #475569; line-height: 1.7; border-bottom: 1px solid #f1f5f9;">
                                <span style="position: absolute; left: 8px; color: #10b981; font-weight: bold; font-size: 18px;">✓</span>
                                Provide clear remarks explaining your negotiation rationale.
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 12px 0; padding-left: 32px; position: relative; font-size: 16px; color: #475569; line-height: 1.7;">
                                <span style="position: absolute; left: 8px; color: #10b981; font-weight: bold; font-size: 18px;">✓</span>
                                Check your dashboard regularly for DMC responses and updates.
                            </td>
                        </tr>
                    </table>
                </div>
                
                <p style="font-size: 16px; line-height: 1.8; margin-bottom: 20px; color: #475569;">
                    Thank you for using <span style="font-weight: 700; color: #4f46e5;">Travclicks</span> — streamlining travel negotiations and partnerships.
                </p>
                
                <!-- Signature -->
                <div style="margin-top: 40px; font-size: 16px; color: #1e293b;">
                    <p style="margin: 5px 0;">Best regards,</p>
                    <p style="font-weight: 700; color: #f59e0b; margin-top: 8px; font-size: 18px;">{{ $dmc_name ?? 'DMC Team' }}</p>
                    <p style="font-style: italic; color: #64748b; font-size: 15px; margin-top: 8px;">via Travclicks Platform</p>
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
                    This notification was sent by <strong>{{ $dmc_name ?? 'DMC' }}</strong> via the Travclicks platform. 
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

