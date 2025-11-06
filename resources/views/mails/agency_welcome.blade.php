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
                    <span style="font-size: 32px;">✨</span>
                    <span>Welcome to the Future</span>
                </h1>
                <div style="display: inline-block; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: white; padding: 8px 20px; border-radius: 20px; font-size: 14px; font-weight: 600; margin-top: 12px; box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);">
                    🚀 Your Travel Business Just Got Transformed
                </div>
            </td>
        </tr>
        
        <!-- Email Body -->
        <tr>
            <td style="padding: 45px 40px; color: #333;">
                
                <!-- Greeting -->
                <p style="font-size: 20px; margin-bottom: 8px; color: #1e293b; font-weight: 600;">Hi {{ $agency_name ?? 'Valued Partner' }},</p>
                <p style="font-size: 18px; margin-bottom: 30px; color: #64748b; font-weight: 500;">{{ $company_name ?? 'Travel Agency' }}</p>
                
                <!-- Congratulations Message -->
                <p style="font-size: 16px; line-height: 1.8; margin-bottom: 20px; color: #475569;">
                    <strong>Congratulations</strong> — you're about to experience a whole new way of doing business in travel.
                </p>
                
                <p style="font-size: 16px; line-height: 1.8; margin-bottom: 25px; color: #475569;">
                    Welcome to <span style="font-weight: 700; color: #4f46e5;">Travclicks</span> — the next-generation travel technology built to connect <strong>Travel Agents</strong> and <strong>DMCs</strong> in real time, automate your workflow, and transform the way you sell destinations.
                </p>
                
                <!-- Problems Eliminated Box -->
                <div style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-left: 4px solid #ef4444; padding: 20px 25px; margin: 30px 0; border-radius: 10px; box-shadow: 0 2px 8px rgba(239, 68, 68, 0.15);">
                    <p style="margin: 0 0 15px 0; font-weight: 700; color: #991b1b; font-size: 16px;">
                        <span style="font-size: 20px;">❌</span> Say Goodbye To:
                    </p>
                    <div style="margin-left: 10px;">
                        <p style="margin: 8px 0; color: #7f1d1d; font-size: 15px; line-height: 1.6;">
                            <span style="font-weight: 600;">•</span> No more endless emails
                        </p>
                        <p style="margin: 8px 0; color: #7f1d1d; font-size: 15px; line-height: 1.6;">
                            <span style="font-weight: 600;">•</span> No more scattered spreadsheets
                        </p>
                        <p style="margin: 8px 0; color: #7f1d1d; font-size: 15px; line-height: 1.6;">
                            <span style="font-weight: 600;">•</span> No more missed opportunities
                        </p>
                        <p style="margin: 8px 0; color: #7f1d1d; font-size: 15px; line-height: 1.6;">
                            <span style="font-weight: 600;">•</span> No more fragmented communication over endless emails, phone calls, WhatsApp and other means of communication
                        </p>
                    </div>
                </div>
                
                <!-- Features Box -->
                <div style="background: linear-gradient(135deg, #f8faff 0%, #f1f5f9 100%); border-radius: 12px; padding: 25px 30px; margin: 35px 0; border: 2px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                    <h3 style="font-size: 18px; font-weight: 700; margin: 0 0 25px 0; color: #4f46e5; text-align: center;">
                        <span style="font-size: 22px;">🎯</span> With Travclicks, You Can:
                    </h3>
                    
                    <!-- Feature Items -->
                    <div style="background: white; padding: 16px 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 12px;">
                        <div style="display: table; width: 100%;">
                            <div style="display: table-cell; vertical-align: middle; width: 40px;">
                                <span style="font-size: 22px;">⚡</span>
                            </div>
                            <div style="display: table-cell; vertical-align: middle;">
                                <span style="font-size: 15px; color: #475569; line-height: 1.7; font-weight: 500;"><strong style="color: #1e293b;">Create & manage proposals</strong> in seconds</span>
                            </div>
                        </div>
                    </div>
                    
                    <div style="background: white; padding: 16px 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 12px;">
                        <div style="display: table; width: 100%;">
                            <div style="display: table-cell; vertical-align: middle; width: 40px;">
                                <span style="font-size: 22px;">💬</span>
                            </div>
                            <div style="display: table-cell; vertical-align: middle;">
                                <span style="font-size: 15px; color: #475569; line-height: 1.7; font-weight: 500;"><strong style="color: #1e293b;">Chat directly with DMCs</strong> — instantly, securely, globally</span>
                            </div>
                        </div>
                    </div>
                    
                    <div style="background: white; padding: 16px 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 12px;">
                        <div style="display: table; width: 100%;">
                            <div style="display: table-cell; vertical-align: middle; width: 40px;">
                                <span style="font-size: 22px;">📊</span>
                            </div>
                            <div style="display: table-cell; vertical-align: middle;">
                                <span style="font-size: 15px; color: #475569; line-height: 1.7; font-weight: 500;"><strong style="color: #1e293b;">Track every update</strong>, quote, and conversion in one place</span>
                            </div>
                        </div>
                    </div>
                    
                    <div style="background: white; padding: 16px 20px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 12px;">
                        <div style="display: table; width: 100%;">
                            <div style="display: table-cell; vertical-align: middle; width: 40px;">
                                <span style="font-size: 22px;">🌏</span>
                            </div>
                            <div style="display: table-cell; vertical-align: middle;">
                                <span style="font-size: 15px; color: #475569; line-height: 1.7; font-weight: 500;"><strong style="color: #1e293b;">Access multi-destination DMC networks</strong> & dynamic rates for everything you need for your travellers</span>
                            </div>
                        </div>
                    </div>
                    
                    <div style="background: white; padding: 16px 20px; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <div style="display: table; width: 100%;">
                            <div style="display: table-cell; vertical-align: middle; width: 40px;">
                                <span style="font-size: 22px;">🤖</span>
                            </div>
                            <div style="display: table-cell; vertical-align: middle;">
                                <span style="font-size: 15px; color: #475569; line-height: 1.7; font-weight: 500;"><strong style="color: #1e293b;">Leverage smart automation</strong> to save time and boost revenue</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Vision Statement -->
                <div style="background-color: #f0f9ff; border-left: 4px solid #3b82f6; padding: 25px; margin: 30px 0; border-radius: 10px; text-align: center; box-shadow: 0 2px 8px rgba(59, 130, 246, 0.15);">
                    <p style="margin: 0 0 12px 0; font-weight: 600; font-style: italic; color: #1e40af; font-size: 17px; line-height: 1.6;">
                        This is where <strong>speed meets simplicity</strong>, and <strong>technology meets travel</strong>.
                    </p>
                    <p style="margin: 0; color: #1e40af; font-size: 15px; line-height: 1.6;">
                        Whether you manage five clients or five hundred — Travclicks scales with you.
                    </p>
                </div>
                
                <!-- Access Info Box -->
                <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 12px; padding: 28px 35px; text-align: center; margin: 35px 0; border: 2px solid #fbbf24; box-shadow: 0 4px 12px rgba(251, 191, 36, 0.25);">
                    <p style="margin: 0 0 10px 0; font-size: 18px; color: #92400e; font-weight: 700;">
                        <span style="font-size: 26px; margin-right: 8px;">✨</span> Watch your inbox
                    </p>
                    <p style="margin: 0; font-size: 15px; color: #78350f; line-height: 1.6; font-weight: 500;">
                        Your unique Travclicks access credentials are on their way in the next email.
                    </p>
                </div>
                
                <p style="font-size: 17px; line-height: 1.8; margin: 25px 0; color: #475569; text-align: center; font-weight: 500;">
                    Welcome to the future of destination management — <span style="font-weight: 700; color: #4f46e5;">connected, automated, and unstoppable</span>.
                </p>
                
                <p style="font-size: 17px; line-height: 1.8; margin: 25px 0; color: #1e293b; text-align: center; font-weight: 700;">
                    Let's change how the travel world works — together.
                </p>
                
                <!-- Signature -->
                <div style="margin-top: 45px; font-size: 16px; color: #1e293b;">
                    <p style="margin: 5px 0;">Warm regards,</p>
                    <p style="font-weight: 700; color: #4f46e5; margin-top: 8px; font-size: 18px;">Team Travclicks</p>
                    <p style="font-style: italic; color: #64748b; font-size: 15px; margin-top: 8px;">Travel Technology Transformed</p>
                </div>
                
            </td>
        </tr>
        
        <!-- Email Footer -->
        <tr>
            <td style="background: #f8fafc; padding: 25px 40px; text-align: center; font-size: 14px; color: #64748b; border-top: 1px solid #e2e8f0; line-height: 1.7;">
                <p style="margin: 0;">
                    For assistance, please contact 
                    <a href="mailto:support@travclicks.com" style="color: #4f46e5; text-decoration: none; font-weight: 600;">support@travclicks.com</a>
                </p>
            </td>
        </tr>
        
    </table>
</div>
