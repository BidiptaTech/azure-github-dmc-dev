@extends('layouts.layout')
@section('title', 'Your Tour Itinerary')

@section('content')
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
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
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
        
        .tour-title {
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
        
        .tour-title i {
            font-size: 28px;
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .tour-subtitle {
            margin-top: 8px;
            font-size: 16px;
            opacity: 0.95;
            position: relative;
            z-index: 2;
        }
        
        .tour-badge {
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
        
        .tour-badge i {
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
        
        /* Enhanced Trip Summary Box */
        .trip-summary {
            background: linear-gradient(135deg, #f8faff 0%, #f1f5f9 100%);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 35px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            position: relative;
        }
        
        .trip-summary::before {
            content: '\f5a0';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            color: rgba(99, 102, 241, 0.3);
        }
        
        .summary-title {
            font-size: 20px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 25px;
            color: #6366f1;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .summary-title i {
            color: #6366f1;
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
            color: #6366f1;
            font-size: 12px;
        }
        
        .summary-value {
            color: #1e293b;
            font-size: 16px;
            font-weight: 600;
        }
        
        .price-highlight {
            color: #6366f1;
            font-weight: 700;
            font-size: 18px;
        }
        
        /* Enhanced Itinerary Styles */
        .itinerary-section {
            margin-bottom: 35px;
        }
        
        .section-title {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 25px 0;
            color: #6366f1;
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
            background: linear-gradient(to right, #6366f1, #8b5cf6);
            border-radius: 3px;
        }
        
        .section-title i {
            color: #6366f1;
            font-size: 22px;
        }
        
        .timeline-container {
            position: relative;
        }
        
        .timeline-connector {
            position: absolute;
            left: 25px;
            top: 60px;
            bottom: 0;
            width: 3px;
            background: linear-gradient(to bottom, #6366f1, #8b5cf6);
            z-index: 1;
            border-radius: 2px;
        }
        
        .itinerary-day {
            margin-bottom: 25px;
            background: linear-gradient(135deg, #ffffff 0%, #f8faff 100%);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            position: relative;
            z-index: 2;
            margin-left: 15px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .itinerary-day:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
        
        .day-bullet {
            position: absolute;
            left: -22px;
            top: 20px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border: 4px solid #fff;
            box-shadow: 0 0 0 2px #6366f1;
            z-index: 3;
        }
        
        .day-header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            padding: 16px 25px;
            font-weight: 700;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .day-number {
            font-size: 16px;
            background-color: rgba(255,255,255,0.2);
            padding: 6px 12px;
            border-radius: 50px;
            border: 1px solid rgba(255,255,255,0.3);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .day-number i {
            font-size: 14px;
        }
        
        .day-title {
            font-size: 18px;
            flex: 1;
            text-align: center;
        }
        
        .day-date {
            font-size: 14px;
            opacity: 0.95;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .day-date i {
            font-size: 12px;
        }
        
        .day-content {
            padding: 25px;
        }
        
        .day-description {
            margin-bottom: 20px;
            font-size: 16px;
            line-height: 1.7;
            color: #475569;
        }
        
        .day-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 20px;
        }
        
        .day-detail {
            background: linear-gradient(135deg, #f8faff 0%, #f1f5f9 100%);
            padding: 16px 20px;
            border-radius: 12px;
            font-size: 14px;
            border: 1px solid #e2e8f0;
            transition: transform 0.2s ease;
        }
        
        .day-detail:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .detail-label {
            font-weight: 700;
            color: #6366f1;
            margin-bottom: 8px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .detail-label i {
            font-size: 12px;
        }
        
        .detail-value {
            color: #1e293b;
            font-weight: 500;
        }
        
        /* Enhanced Trip Details Styles */
        .trip-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 35px;
        }
        
        .detail-box {
            background: linear-gradient(135deg, #ffffff 0%, #f8faff 100%);
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            transition: transform 0.2s ease;
            position: relative;
        }
        
        .detail-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
        
        .inclusions::before {
            content: '\f058';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            color: rgba(5, 150, 105, 0.3);
        }
        
        .exclusions::before {
            content: '\f057';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            color: rgba(239, 68, 68, 0.3);
        }
        
        .detail-box-title {
            font-size: 18px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .inclusions .detail-box-title {
            color: #059669;
        }
        
        .exclusions .detail-box-title {
            color: #ef4444;
        }
        
        .detail-box-title i {
            font-size: 18px;
        }
        
        .detail-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        
        .detail-list li {
            position: relative;
            padding-left: 30px;
            margin-bottom: 12px;
            font-size: 15px;
            line-height: 1.6;
            color: #475569;
        }
        
        .inclusions .detail-list li:before {
            content: "\f00c";
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            left: 0;
            color: #059669;
            font-size: 14px;
            top: 2px;
        }
        
        .exclusions .detail-list li:before {
            content: "\f00d";
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            left: 0;
            color: #ef4444;
            font-size: 14px;
            top: 2px;
        }
        
        /* Enhanced Contact Box */
        .contact-box {
            background: linear-gradient(135deg, #f8faff 0%, #f1f5f9 100%);
            border-radius: 16px;
            padding: 25px 30px;
            margin-bottom: 35px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            position: relative;
        }
        
        .contact-box::before {
            content: '\f2b5';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            color: rgba(99, 102, 241, 0.3);
        }
        
        .contact-title {
            font-size: 18px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 20px;
            color: #6366f1;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .contact-title i {
            color: #6366f1;
            font-size: 18px;
        }
        
        .contact-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .contact-item {
            background-color: white;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid #f3f4f6;
            transition: transform 0.2s ease;
        }
        
        .contact-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .contact-label {
            font-weight: 600;
            color: #64748b;
            margin-bottom: 6px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .contact-label i {
            color: #6366f1;
            font-size: 12px;
        }
        
        .contact-value {
            font-size: 14px;
            color: #1e293b;
            font-weight: 500;
        }
        
        /* Enhanced CTA Button */
        .cta-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-align: center;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            text-decoration: none;
            padding: 16px 30px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            margin: 35px auto;
            max-width: 320px;
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.3);
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .cta-button i {
            font-size: 18px;
        }
        
        .cta-button:hover {
            background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
            transform: translateY(-2px);
        }
        
        /* Enhanced Contact Info */
        .contact-info {
            background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
            border-radius: 16px;
            padding: 25px;
            margin-top: 35px;
            border: 1px solid #e5e7eb;
        }
        
        .contact-info-title {
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
        
        .contact-info-title i {
            color: #6366f1;
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
            color: #6366f1;
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
            color: #6366f1;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 500;
            transition: color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .footer-link:hover {
            color: #5b21b6;
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
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            border-radius: 12px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(99, 102, 241, 0.3);
        }
        
        .social-link:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 6px 12px rgba(99, 102, 241, 0.4);
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
            
            .summary-grid,
            .trip-details,
            .day-details {
                grid-template-columns: 1fr;
            }
            
            .contact-methods {
                grid-template-columns: 1fr;
            }
            
            .tour-title {
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
            
            .itinerary-day {
                margin-left: 0;
            }
            
            .timeline-connector,
            .day-bullet {
                display: none;
            }
            
            .day-header {
                flex-direction: column;
                text-align: center;
                gap: 8px;
            }
        }
    </style>

    <div class="email-container">
        <!-- Enhanced Email Header -->
        <div class="email-header">
            <div class="logo-container">
                @php
                    $logoSetting = \App\Helpers\CommonHelper::masterSettingsName('logo');
                    $nameSetting = \App\Helpers\CommonHelper::masterSettingsName('name');
                    
                    $logo = $logoSetting['master_value'] ?? '';
                    $companyName = $nameSetting['master_value'] ?? config('app.name');
                @endphp
                <img src="{{ $logo }}" alt="{{ $companyName }}" class="logo">
            </div>
            <h1 class="tour-title">
                <i class="fas fa-map-marked-alt"></i> Your Tour Itinerary
            </h1>
            <p class="tour-subtitle">{{ $tour->destination ?? 'Amazing Destination' }} - {{ $tour->duration ?? '7 Days' }}</p>
            <div class="tour-badge">
                <i class="fas fa-route"></i> #{{ $tour->tour_id ?? 'TOUR-'.rand(10000, 99999) }}
            </div>
        </div>
        
        <!-- Enhanced Email Body -->
        <div class="email-body">
            <p class="greeting">
                Dear {{ $tour->customer_name ?? 'Valued Traveler' }}, <i class="fas fa-smile-beam"></i>
            </p>
            
            <p class="intro-text">
                Thank you for choosing {{ $companyName }} for your upcoming journey! We're thrilled to share your confirmed itinerary for your {{ $tour->duration ?? '7 Days' }} adventure to {{ $tour->destination ?? 'Amazing Destination' }}. Please review the details below and let us know if you have any questions.
            </p>
            
            <!-- Enhanced Trip Summary Box -->
            <div class="trip-summary">
                <h3 class="summary-title">
                    <i class="fas fa-clipboard-list"></i> Trip Summary
                </h3>
                <div class="summary-grid">
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-hashtag"></i> Tour ID
                        </span>
                        <span class="summary-value">{{ $tour->tour_id ?? 'TOUR-'.rand(10000, 99999) }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-map-marker-alt"></i> Destination
                        </span>
                        <span class="summary-value">{{ $tour->destination ?? 'Amazing Destination' }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-calendar-alt"></i> Start Date
                        </span>
                        <span class="summary-value">{{ \Carbon\Carbon::parse($tour->start_date ?? now()->addDays(30))->format('l, jS M Y') }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-calendar-check"></i> End Date
                        </span>
                        <span class="summary-value">{{ \Carbon\Carbon::parse($tour->end_date ?? now()->addDays(37))->format('l, jS M Y') }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-clock"></i> Duration
                        </span>
                        <span class="summary-value">{{ $tour->duration ?? '7 Days' }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-users"></i> Travelers
                        </span>
                        <span class="summary-value">{{ $tour->total_travelers ?? '2' }} {{ ($tour->total_travelers ?? 2) == 1 ? 'Person' : 'Persons' }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-bed"></i> Accommodation
                        </span>
                        <span class="summary-value">{{ $tour->accommodation ?? '4-Star Hotels' }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">
                            <i class="fas fa-dollar-sign"></i> Total Price
                        </span>
                        <span class="summary-value price-highlight">{{ $tour->currency ?? 'SGD' }} {{ number_format($tour->total_price ?? 2500, 2) }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Detailed Itinerary -->
            <div class="itinerary-section">
                <h3 class="section-title">
                    <i class="fas fa-route"></i> Your Detailed Itinerary
                </h3>
                
                <div class="timeline-container">
                    <div class="timeline-connector"></div>
                    
                    @if(isset($tour->itinerary) && is_array($tour->itinerary))
                        @foreach($tour->itinerary as $day)
                        <div class="itinerary-day">
                            <div class="day-bullet"></div>
                            <div class="day-header">
                                <span class="day-number">
                                    <i class="fas fa-calendar-day"></i> Day {{ $day['day'] ?? $loop->iteration }}
                                </span>
                                <span class="day-title">{{ $day['title'] ?? 'Exciting Day Adventure' }}</span>
                                <span class="day-date">
                                    <i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($day['date'] ?? now()->addDays($loop->iteration))->format('jS M Y') }}
                                </span>
                            </div>
                            <div class="day-content">
                                <p class="day-description">{{ $day['description'] ?? 'An amazing day filled with exciting activities and beautiful sights to explore.' }}</p>
                                <div class="day-details">
                                    <div class="day-detail">
                                        <div class="detail-label">
                                            <i class="fas fa-utensils"></i> Meals
                                        </div>
                                        <div class="detail-value">{{ isset($day['meals']) ? implode(', ', $day['meals']) : 'Breakfast, Lunch, Dinner' }}</div>
                                    </div>
                                    <div class="day-detail">
                                        <div class="detail-label">
                                            <i class="fas fa-bed"></i> Accommodation
                                        </div>
                                        <div class="detail-value">{{ $day['accommodation'] ?? '4-Star Hotel' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        @for($i = 1; $i <= 7; $i++)
                        <div class="itinerary-day">
                            <div class="day-bullet"></div>
                            <div class="day-header">
                                <span class="day-number">
                                    <i class="fas fa-calendar-day"></i> Day {{ $i }}
                                </span>
                                <span class="day-title">Exciting Day {{ $i }} Adventure</span>
                                <span class="day-date">
                                    <i class="fas fa-calendar"></i> {{ now()->addDays($i + 29)->format('jS M Y') }}
                                </span>
                            </div>
                            <div class="day-content">
                                <p class="day-description">An amazing day filled with exciting activities and beautiful sights to explore.</p>
                                <div class="day-details">
                                    <div class="day-detail">
                                        <div class="detail-label">
                                            <i class="fas fa-utensils"></i> Meals
                                        </div>
                                        <div class="detail-value">Breakfast, Lunch, Dinner</div>
                                    </div>
                                    <div class="day-detail">
                                        <div class="detail-label">
                                            <i class="fas fa-bed"></i> Accommodation
                                        </div>
                                        <div class="detail-value">4-Star Hotel</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endfor
                    @endif
                </div>
            </div>
            
            <!-- Enhanced Inclusions & Exclusions -->
            <div class="trip-details">
                <div class="detail-box inclusions">
                    <h3 class="detail-box-title">
                        <i class="fas fa-check-circle"></i> What's Included
                    </h3>
                    <ul class="detail-list">
                        @if(isset($tour->inclusions) && is_array($tour->inclusions))
                            @foreach($tour->inclusions as $inclusion)
                            <li>{{ $inclusion }}</li>
                            @endforeach
                        @else
                            <li>All accommodation as specified</li>
                            <li>Daily breakfast at hotels</li>
                            <li>Transportation as per itinerary</li>
                            <li>English-speaking tour guide</li>
                            <li>Entrance fees to attractions</li>
                            <li>Airport transfers</li>
                        @endif
                    </ul>
                </div>
                
                <div class="detail-box exclusions">
                    <h3 class="detail-box-title">
                        <i class="fas fa-times-circle"></i> What's Not Included
                    </h3>
                    <ul class="detail-list">
                        @if(isset($tour->exclusions) && is_array($tour->exclusions))
                            @foreach($tour->exclusions as $exclusion)
                            <li>{{ $exclusion }}</li>
                            @endforeach
                        @else
                            <li>International airfare</li>
                            <li>Travel insurance</li>
                            <li>Personal expenses</li>
                            <li>Meals not mentioned</li>
                            <li>Tips and gratuities</li>
                            <li>Optional activities</li>
                        @endif
                    </ul>
                </div>
            </div>
            
            <!-- Enhanced Contact Information -->
            <div class="contact-box">
                <h3 class="contact-title">
                    <i class="fas fa-user-tie"></i> Your Travel Contact
                </h3>
                <div class="contact-details">
                    <div class="contact-item">
                        <div class="contact-label">
                            <i class="fas fa-user"></i> Contact Person
                        </div>
                        <div class="contact-value">{{ $tour->contact_person ?? 'Travel Coordinator' }}</div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-label">
                            <i class="fas fa-envelope"></i> Email
                        </div>
                        <div class="contact-value">{{ $tour->contact_email ?? 'travel@example.com' }}</div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-label">
                            <i class="fas fa-phone"></i> Phone
                        </div>
                        <div class="contact-value">{{ $tour->contact_phone ?? '+1 (555) 123-4567' }}</div>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-bottom: 25px;">
                <p style="font-size: 16px; color: #1e293b; font-weight: 600; margin-bottom: 8px;">
                    <i class="fas fa-heart" style="color: #6366f1;"></i> We're excited for your upcoming adventure!
                </p>
                
                <p style="font-size: 15px; color: #64748b; margin-bottom: 20px;">
                    Please review your itinerary carefully. If you have any questions or need to make changes, please contact us as soon as possible. We're committed to making your journey memorable and hassle-free.
                </p>
                
                <p style="font-size: 15px; color: #64748b;">
                    We recommend downloading a copy of this itinerary to keep handy during your travels. You can also access your itinerary anytime through our online portal.
                </p>
            </div>
            
            <!-- Enhanced CTA Button -->
            <a href="{{ url('/') }}" class="cta-button">
                <i class="fas fa-download"></i> Download Full Itinerary (PDF)
            </a>
            
            <div style="text-align: center; margin-top: 30px;">
                <p style="font-size: 16px; color: #1e293b; margin-bottom: 20px;">
                    We look forward to providing you with an exceptional travel experience!
                </p>
                
                <p style="font-size: 16px; color: #1e293b;">
                    Warm regards,<br>
                    <strong>The {{ $companyName }} Team</strong>
                </p>
            </div>
            
            <!-- Enhanced Contact Information -->
            <div class="contact-info">
                <h4 class="contact-info-title">
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
            
            <p class="copyright">
                &copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.
            </p>
        </div>
    </div>
@endsection
