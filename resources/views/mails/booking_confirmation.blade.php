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
        
        .booking-title {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 2;
        }
        
        .booking-subtitle {
            margin-top: 8px;
            font-size: 16px;
            opacity: 0.95;
            position: relative;
            z-index: 2;
        }
        
        .booking-badge {
            background-color: rgba(255,255,255,0.2);
            border-radius: 50px;
            padding: 8px 20px;
            margin-top: 20px;
            display: inline-block;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 2px solid rgba(255,255,255,0.3);
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 2;
        }
        
        .email-body {
            padding: 40px;
        }
        
        .greeting {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 25px;
            color: #1e293b;
            text-align: center;
        }
        
        .booking-message {
            margin-bottom: 35px;
            font-size: 16px;
            text-align: center;
            color: #64748b;
            line-height: 1.7;
        }
        
        /* Enhanced Date Section */
        .dates-section {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 35px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .dates-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 25px;
            color: #1e293b;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        
        .dates-title i {
            color: #667eea;
            font-size: 22px;
        }
        
        .dates-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .date-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: 1px solid #f1f5f9;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        
        .date-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .date-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }
        
        .date-label {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        
        .date-label i {
            color: #667eea;
            font-size: 12px;
        }
        
        .date-day {
            font-size: 16px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 4px;
        }
        
        .date-full {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
        }
        
        .date-time {
            font-size: 14px;
            color: #64748b;
            margin-top: 4px;
            font-style: italic;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }
        
        .date-time i {
            font-size: 12px;
        }
        
        .booking-details {
            background: linear-gradient(135deg, #f8faff 0%, #f1f5f9 100%);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 35px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 25px;
            color: #667eea;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .section-title i {
            color: #667eea;
            font-size: 20px;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 16px;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            width: 45%;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .detail-value {
            width: 55%;
            font-weight: 500;
            color: #1e293b;
        }
        
        .detail-icon {
            font-size: 16px;
            color: #667eea;
            width: 20px;
            text-align: center;
        }
        
        .status-confirmed {
            color: #059669 !important;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .status-confirmed i {
            color: #059669;
            font-size: 16px;
        }
        
        .price-section {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            border: 2px solid #a7f3d0;
            text-align: center;
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.1);
            position: relative;
        }
        
        .price-section::before {
            content: '\f155';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            color: rgba(5, 150, 105, 0.3);
        }
        
        .price-label {
            color: #065f46;
            font-size: 16px;
            margin-bottom: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .price-label i {
            color: #059669;
            font-size: 18px;
        }
        
        .total-price {
            font-size: 36px;
            font-weight: 800;
            color: #059669;
            margin: 15px 0;
            text-shadow: 0 2px 4px rgba(5, 150, 105, 0.1);
        }
        
        .payment-status {
            margin-top: 15px;
            padding: 8px 16px;
            background-color: rgba(5, 150, 105, 0.1);
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #065f46;
            font-weight: 600;
            font-size: 14px;
        }
        
        .payment-status i {
            color: #059669;
            font-size: 14px;
        }
        
        .cta-button {
            display: block;
            text-align: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 16px 30px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            margin: 35px 0;
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .cta-button i {
            font-size: 18px;
        }
        
        .cta-button:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            transform: translateY(-2px);
        }
        
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
            color: #667eea;
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
            color: #667eea;
            font-weight: bold;
            font-size: 18px;
            width: 24px;
            text-align: center;
        }
        
        .method-text {
            font-size: 14px;
            font-weight: 500;
        }
        
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
            color: #667eea;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 500;
            transition: color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .footer-link:hover {
            color: #5a67d8;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
        }
        
        .social-link:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 6px 12px rgba(102, 126, 234, 0.4);
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
        
        /* Responsive styles */
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 12px;
            }
            
            .email-body {
                padding: 25px 20px;
            }
            
            .dates-grid {
                grid-template-columns: 1fr;
            }
            
            .contact-methods {
                grid-template-columns: 1fr;
            }
            
            .detail-row {
                flex-direction: column;
                gap: 4px;
            }
            
            .detail-label, .detail-value {
                width: 100%;
            }
            
            .total-price {
                font-size: 28px;
            }
            
            .cta-button {
                font-size: 14px;
                padding: 14px 25px;
            }
        }
</style>

    <div class="email-container">
        <!-- Email Header -->
        <div class="email-header">
            <div class="logo-container">
                @php
                    $logo = $company['logo'] ?? '';
                    $companyName = $company['companyName'] ?? config('app.name');
                @endphp
                <img src="{{ $logo }}" alt="{{ $companyName }}" class="logo">
            </div>
            <h1 class="booking-title"><i class="fas fa-party-horn"></i> Booking Confirmed!</h1>
            <p class="booking-subtitle">Thank you for choosing {{ $companyName }}</p>
            <div class="booking-badge">
                <i class="fas fa-ticket-alt"></i> #{{ $booking_id ?? 'BOK-'.rand(10000, 99999) }}
            </div>
        </div>
        
        <!-- Email Body -->
        <div class="email-body">
            <p class="greeting">Hello {{ $hotel_name ?? 'Valued Customer' }}! <i class="fas fa-hand-wave"></i></p>
            
            <div class="booking-message">
                <p>{{ $body ?? "We’re delighted to inform you that a customer has successfully booked rooms at your hotel. Everything is confirmed and in place to ensure a seamless experience.

                Below are the complete booking details for your reference." }}</p>
            </div>
            
            <!-- Enhanced Dates Section -->
            @if(isset($check_in_date) && isset($check_out_date))
            <div class="dates-section">
                <h3 class="dates-title">
                    <i class="fas fa-calendar-alt"></i> Your Travel Dates
                </h3>
                <div class="dates-grid">
                    <div class="date-card">
                        <div class="date-label">
                            <i class="fas fa-sign-in-alt"></i> Check-in
                        </div>
                        <div class="date-day">{{ \Carbon\Carbon::parse($check_in_date)->format('l') }}</div>
                        <div class="date-full">{{ \Carbon\Carbon::parse($check_in_date)->format('jS F Y') }}</div>
                        <div class="date-time">
                            <i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($check_in_time)->format('g:i A') ?? '3:00 PM' }}
                        </div>
                    </div>
                    <div class="date-card">
                        <div class="date-label">
                            <i class="fas fa-sign-out-alt"></i> Check-out
                        </div>
                        <div class="date-day">{{ \Carbon\Carbon::parse($check_out_date)->format('l') }}</div>
                        <div class="date-full">{{ \Carbon\Carbon::parse($check_out_date)->format('jS F Y') }}</div>
                        <div class="date-time">
                            <i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($check_out_time)->format('g:i A') ?? '11:00 AM' }}
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Booking Details Section -->
            <div class="booking-details">
                <h3 class="section-title">
                    <i class="fas fa-clipboard-list"></i> Booking Information
                </h3>
                
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-tag detail-icon"></i>
                        Booking Type:
                    </div>
                    <div class="detail-value">{{ $type ?? 'Tour Package' }}</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-calendar-day detail-icon"></i>
                        Booking Date:
                    </div>
                    <div class="detail-value">{{ \Carbon\Carbon::parse($booking_date ?? now())->format('l, jS F Y') }}</div>
                </div>
                
                
                
                @if(isset($location))
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-map-marker-alt detail-icon"></i>
                        Location:
                    </div>
                    <div class="detail-value">{{ $location }}</div>
                </div>
                @endif
                
                @if(isset($guests))
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-users detail-icon"></i>
                        Guests:
                    </div>
                    <div class="detail-value">{{ $guests }}</div>
                </div>
                @endif

                @if(isset($No_of_rooms) && $No_of_rooms > 0)
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-bed detail-icon"></i>
                        No of Rooms:
                    </div>
                    <div class="detail-value">{{ $No_of_rooms }}</div>
                </div>
                @endif

                @if(isset($No_of_beds) && $No_of_beds > 0)
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-bed detail-icon"></i>
                        No of Beds:
                    </div>
                    <div class="detail-value">{{ $No_of_beds }}</div>
                </div>
                @endif
                
                
                @if(isset($room_type))
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-door-open detail-icon"></i>
                        Room Type:
                    </div>
                    <div class="detail-value">{{ $room_type }}</div>
                </div>
                @endif
                
                @if(isset($bed_type))
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-bed detail-icon"></i>
                        Bed Type:
                    </div>
                    <div class="detail-value">{{ $bed_type }}</div>
                </div>
                @endif
                
                
                
                @if(isset($hotel_name))
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-hotel detail-icon"></i>
                        Hotel:
                    </div>
                    <div class="detail-value">{{ $hotel_name }}</div>
                </div>
                @endif
                
                @if(isset($check_in_time) || isset($check_out_time))
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-clock detail-icon"></i>
                        Check-In/Out Time:
                    </div>
                    <div class="detail-value">
                        Check-in: {{ $check_in_time ?? '15:00' }} / Check-out: {{ $check_out_time ?? '11:00' }}
                    </div>
                </div>
                @endif
                
                @if(isset($max_occupancy))
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-users detail-icon"></i>
                        Max Occupancy:
                    </div>
                    <div class="detail-value">{{ $max_occupancy }} person(s)</div>
                </div>
                @endif
                
                @if(isset($baby_cot) && $baby_cot > 0)
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-baby detail-icon"></i>
                        Baby Cot:
                    </div>
                    <div class="detail-value">{{ $baby_cot }}</div>
                </div>
                @endif

                
                @if(isset($meal_plan) && $meal_plan != null)
                    <div class="detail-row">
                        <div class="detail-label">
                            <i class="fas fa-concierge-bell detail-icon"></i>
                            Meal Options:
                        </div>
                        <div class="detail-value">{{ $meal_plan }}</div>
                    </div>                                            
                @endif
                
                
                {{-- @if(isset($booking->reference_number))
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-hashtag detail-icon"></i>
                        Reference:
                    </div>
                    <div class="detail-value">{{ $booking->reference_number }}</div>
                </div>
                @endif --}}
            </div>
            
            <!-- Customer Information Section -->
            <div class="booking-details">
                <h3 class="section-title">
                    <i class="fas fa-user"></i> Customer Information
                </h3>
                
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-user detail-icon"></i>
                        Full Name:
                    </div>
                    <div class="detail-value">{{ $fullName ?? $customer_name ?? 'Not provided' }}</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-envelope detail-icon"></i>
                        Email:
                    </div>
                    <div class="detail-value">{{ $email ?? 'Not provided' }}</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-phone detail-icon"></i>
                        Phone:
                    </div>
                    <div class="detail-value">{{ ($countryCode ?? '') . ' ' . ($phone ?? 'Not provided') }}</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-home detail-icon"></i>
                        Address:
                    </div>
                    <div class="detail-value">
                        {{ $address1 ?? 'Not provided' }}
                        @if(isset($address2) && !empty($address2))
                            <br>{{ $address2 }}
                        @endif
                    </div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-map detail-icon"></i>
                        State/ZIP:
                    </div>
                    <div class="detail-value">{{ ($state ?? 'Not provided') . ', ' . ($zip ?? '') }}</div>
                </div>
                
                @if(isset($specialRequests) && !empty($specialRequests))
                <div class="detail-row">
                    <div class="detail-label">
                        <i class="fas fa-clipboard-list detail-icon"></i>
                        Special Requests:
                    </div>
                    <div class="detail-value">{{ $specialRequests }}</div>
                </div>
                @endif
            </div>
            
            <!-- Price section removed as requested -->
            
            <!-- Call to Action Button -->
            {{-- <a href="{{ route('bookinglist.index') }}" class="cta-button">
                <i class="fas fa-eye"></i> View Full Booking Details
            </a> --}}
            
            <!-- Additional Information -->
            <p style="text-align: center; color: #64748b; font-size: 15px; line-height: 1.6;">
                Need help or have questions? Our support team is here to assist you 24/7. Feel free to reach out using any of the contact methods below.
            </p>
            
            <!-- Enhanced Contact Information -->
            <div class="contact-info">
                <h4 class="contact-title">
                    <i class="fas fa-comments"></i> Get in Touch
                </h4>
                <div class="contact-methods">
                    <div class="contact-method">
                        <i class="fas fa-envelope method-icon"></i>
                        <span class="method-text">{{ $mail_settings->support_email ?? 'NA' }}</span>
                    </div>
                    <div class="contact-method">
                        <i class="fas fa-phone method-icon"></i>
                        <span class="method-text">{{ $mail_settings->support_phone ?? '000000' }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Email Footer -->
        <div class="email-footer">
            <div class="footer-links">
                <a href="#" class="footer-link">
                    <i class="fas fa-shield-alt"></i> Privacy Policy
                </a>
                <a href="#" class="footer-link">
                    <i class="fas fa-file-contract"></i> Terms of Service
                </a>
                <a href="#" class="footer-link">
                    <i class="fas fa-user-times"></i> Unsubscribe
                </a>
            </div>
<!--             
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
             -->
            <p class="copyright">
                &copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.
            </p>
        </div>
    </div>