<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<style>
    /* Modern Sidebar Styling */
    .layout-menu {
        background: #ffffff;
        border-right: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
    }
    
    /* Logo area styling */
    .app-brand {
        padding: 1.25rem 1.5rem;
        height: 70px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6); 
        margin-bottom: 1rem;
        box-shadow: 0 4px 12px rgba(107, 114, 241, 0.2);
    }
    
    .app-brand-text {
        color: white !important;
        font-weight: 700 !important;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    
    /* Menu header styling */
    .menu-header {
        margin-top: 1.5rem !important;
        padding: 0.5rem 1.5rem !important;
    }
    
    .menu-header-text {
        color: #6366f1 !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        background: linear-gradient(90deg, #6366f1, #8b5cf6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    /* Menu item styling */
    .menu-item {
        margin: 6px 8px;
        border-radius: 12px;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .menu-item.active {
        background: linear-gradient(118deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.1));
        box-shadow: 0 2px 10px rgba(99, 102, 241, 0.12);
    }
    
    .menu-link {
        padding: 0.8rem 1.5rem;
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    
    .menu-link:hover {
        background-color: rgba(99, 102, 241, 0.04);
        transform: translateX(4px);
    }
    
    .menu-item.active .menu-link {
        box-shadow: none;
        color: #ffffff !important;
    }
    
    .menu-item.active .menu-link div {
        color: #ffffff !important;
        font-weight: 600;
    }
    
    /* Colorful Icon styling */
    .menu-icon {
        margin-right: 0.75rem;
        font-size: 1.25rem !important;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
        border-radius: 10px;
        background: #f9fafb;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    /* Individual icon colors */
    .ri-dashboard-3-line {
        color: #f97316 !important; /* Orange */
        background: rgba(249, 115, 22, 0.1);
    }
    
    .ri-ship-line {
        color: #0ea5e9 !important; /* Sky blue */
        background: rgba(14, 165, 233, 0.1);
    }
    
    .ri-map-pin-user-line {
        color: #ec4899 !important; /* Pink */
        background: rgba(236, 72, 153, 0.1);
    }
    
    .ri-route-line {
        color: #14b8a6 !important; /* Teal */
        background: rgba(20, 184, 166, 0.1);
    }
    
    .ri-bookmark-3-line {
        color: #8b5cf6 !important; /* Purple */
        background: rgba(139, 92, 246, 0.1);
    }
    
    .ri-questionnaire-line {
        color: #f43f5e !important; /* Rose */
        background: rgba(244, 63, 94, 0.1);
    }
    
    .ri-hotel-bed-line {
        color: #10b981 !important; /* Emerald */
        background: rgba(16, 185, 129, 0.1);
    }
    
    .ri-shield-check-line {
        color: #6366f1 !important; /* Indigo */
        background: rgba(99, 102, 241, 0.1);
    }
    
    .ri-function-line {
        color: #0284c7 !important; /* Blue */
        background: rgba(2, 132, 199, 0.1);
    }
    
    .ri-hotel-line {
        color: #0d9488 !important; /* Teal */
        background: rgba(13, 148, 136, 0.1);
    }
    
    .ri-camera-3-line {
        color: #8b5cf6 !important; /* Purple */
        background: rgba(139, 92, 246, 0.1);
    }
    
    .ri-stack-line {
        color: #10b981 !important; /* Emerald */
        background: rgba(16, 185, 129, 0.1);
    }
    
    .ri-restaurant-2-line {
        color: #ea580c !important; /* Orange */
        background: rgba(234, 88, 12, 0.1);
    }
    
    .ri-compass-3-line {
        color: #0369a1 !important; /* Blue */
        background: rgba(3, 105, 161, 0.1);
    }
    
    .ri-steering-2-line {
        color: #4f46e5 !important; /* Indigo */
        background: rgba(79, 70, 229, 0.1);
    }
    
    .ri-task-line {
        color: #be123c !important; /* Rose */
        background: rgba(190, 18, 60, 0.1);
    }
    
    .ri-user-line {
        color: #7c3aed !important; /* Violet */
        background: rgba(124, 58, 237, 0.1);
    }
    
    .ri-settings-3-line {
        color: #3b82f6 !important; /* Blue */
        background: rgba(59, 130, 246, 0.1);
    }

    .ri-mail-send-line {
        color: #39c262 !important; /* Blue */
        background: rgba(59, 130, 246, 0.1);
    }
    
    .ri-earth-line {
        color: #059669 !important; /* Emerald */
        background: rgba(5, 150, 105, 0.1);
    }
    
    .ri-bar-chart-box-line {
        color: #7c2d12 !important; 
        background: rgba(124, 45, 18, 0.1);
    }

    .ri-file-list-3-line {
        color: #2c54a0 !important; /* Amber */
        background: rgba(124, 45, 18, 0.1);
    }
    
    .ri-hand-heart-line {
        color: #e11d48 !important; /* Rose */
        background: rgba(225, 29, 72, 0.1);
    }
    
    /* Active icon state with shine effect */
    .menu-item.active .menu-icon {
        color: white !important;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        box-shadow: 0 3px 10px rgba(99, 102, 241, 0.3);
        transform: translateY(-2px);
    }
    
    /* Submenu styling */
    .menu-sub {
        padding-left: 3.35rem !important;
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .menu-sub .menu-link {
        padding: 0.6rem 1.5rem;
        color: #000;
        font-size: 0.92rem;
    }
    
    .menu-sub .menu-link:hover {
        color: #6366f1;
    }
    
    /* Menu toggle icon animation */
    .menu-toggle:after {
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        color: #94a3b8;
    }
    
    .menu-item.open > .menu-toggle:after {
        transform: rotate(90deg);
        color: #6366f1;
    }
    
    /* Menu section hover effect */
    .menu-item.open {
        background-color: rgba(99, 102, 241, 0.04);
        border-radius: 12px;
    }
    
    /* Custom scrollbar */
    .menu-inner::-webkit-scrollbar {
        width: 5px;
    }
    
    .menu-inner::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .menu-inner::-webkit-scrollbar-thumb {
        background-color: rgba(99, 102, 241, 0.2);
        border-radius: 10px;
    }
    
    .menu-inner::-webkit-scrollbar-thumb:hover {
        background-color: rgba(99, 102, 241, 0.4);
    }
    
    /* Smooth hover transitions */
    .menu-link, .menu-icon {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* Reset parent menu color */
    .menu-item.open > .menu-link {
        color: #6366f1 !important; /* Keep parent menu normal color */
    }
    
    .menu-item.open > .menu-link div {
        color: #6366f1 !important;
    }
    
    /* Only make the specific active item white */
    .menu-item.active:not(.open) > .menu-link {
        color: #ffffff !important;
    }
    
    .menu-item.active:not(.open) > .menu-link div {
        color: #ffffff !important;
    }
    
    /* For submenu items that are active */
    .menu-sub .menu-item.active .menu-link {
        color: #ffffff !important;
        /* background: linear-gradient(118deg, #4bbca5, #725aab); */
        background: linear-gradient(25deg, #c851ec, #566ee4);
        box-shadow: 0 2px 10px rgba(99, 102, 241, 0.3);
    }
    
    .menu-sub .menu-item.active .menu-link div {
        color: #ffffff !important;
    }
    
    /* Ensure other submenu items are not white */
    .menu-sub .menu-item:not(.active) .menu-link {
        color: #000 !important;
    }
    
    .menu-sub .menu-item:not(.active) .menu-link div {
        color: #000 !important;
    }

    .menu-vertical .menu-item .menu-link{
        font-weight: bold;
    }

    /* Ensure menu items show full text without truncation */
    .menu-vertical .menu-item .menu-link > div:not(.badge) {
        overflow: visible !important;
        text-overflow: inherit !important;
        white-space: wrap !important;
        word-wrap: break-word !important;
        line-height: 1.467;
        hyphens: auto;
    }

    /* Additional override for menu text in tooltips */
    .menu-tooltip div[data-i18n] {
        overflow: visible !important;
        text-overflow: inherit !important;
        white-space: wrap !important;
        word-wrap: break-word !important;
    }

    /* Override any ellipsis display in menu items */
    .menu-item .menu-link div,
    .menu-item .menu-link span.menu-text-with-tooltip {
        overflow: visible !important;
        text-overflow: inherit !important;
        white-space: wrap !important;
        word-wrap: break-word !important;
        text-overflow: initial !important;
    }

    #template-customizer .template-customizer-open-btn{
        display: none !important;
    }

    /* 3D Menu Toggle Icon Styling */
    .layout-menu-toggle {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
    }

    .layout-menu-toggle .menu-icon {
        padding: 10px;
        border-radius: 12px;
        background: linear-gradient(145deg, #ffffff, #f0f0f0);
        box-shadow: 4px 4px 8px rgba(0,0,0,0.1), 
                    -4px -4px 8px rgba(255,255,255,0.9);
        color: #6366f1 !important;
        font-size: 1.4rem !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(99, 102, 241, 0.1);
    }

    .layout-menu-toggle:hover .menu-icon {
        transform: translateY(-3px) rotate(180deg);
        box-shadow: 6px 6px 10px rgba(0,0,0,0.15), 
                    -6px -6px 10px rgba(255,255,255,0.95);
        background: linear-gradient(145deg, #6366f1, #8b5cf6);
        color: white !important;
    }

    .layout-menu-toggle:active .menu-icon {
        transform: translateY(1px);
        box-shadow: 2px 2px 5px rgba(0,0,0,0.1), 
                    -2px -2px 5px rgba(255,255,255,0.8);
    }

    .rounded-logo {
        width: 55px;
        height: 55px;
        object-fit: cover;
        border-radius: 50%; /* Makes it a perfect circle */
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2),
                    inset 0 0 10px rgba(255, 255, 255, 0.2); /* 3D inner & outer */
        border: 3px solid #ffffff;
        background-color: #f9f9f9;
        transition: transform 0.3s ease;
        }

        .rounded-logo:hover {
        transform: scale(1.05) rotate(1deg);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.25),
                    inset 0 0 12px rgba(255, 255, 255, 0.3);
        }

    /* Improved Animated Ellipsis */
    .animated-ellipsis {
        display: inline-flex;
        margin-left: 2px;
        position: relative;
        top: -1px;
    }

    .animated-ellipsis span {
        animation: waveEffect 1.8s infinite;
        animation-fill-mode: both;
        font-weight: bold;
        color: white;
        font-size: 16px;
        margin-left: 1px;
        text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    }

    .animated-ellipsis span:nth-child(2) {
        animation-delay: 0.2s;
    }

    .animated-ellipsis span:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes waveEffect {
        0%, 100% {
            transform: translateY(0);
        }
        25% {
            transform: translateY(-4px);
        }
        50% {
            transform: translateY(0);
        }
        75% {
            transform: translateY(4px);
        }
    }
    .small-brand-text {
        font-size: 1.1rem;
        letter-spacing: 0.5px;
    }

    .ri-gift-line {
        color: #f59e0b !important; /* Amber */
        background: rgba(245, 158, 11, 0.1);
    }
    
    .ri-service-line {
        color: #06b6d4 !important; /* Cyan */
        background: rgba(6, 182, 212, 0.1);
    }
    
    .ri-building-line {
        color: #7c3aed !important; /* Violet */
        background: rgba(124, 58, 237, 0.1);
    }
    
    .ri-car-line {
        color: #dc2626 !important; /* Red */
        background: rgba(220, 38, 38, 0.1);
    }
    
    .roadmap-icon:hover {
  color: #2ecc71;
  transform: scale(1.1);
  transition: 0.3s;
 }

    /* Menu Text with Full Display */
    .menu-text-with-tooltip {
        white-space: wrap;
        overflow: visible;
        text-overflow: inherit;
        max-width: 100%;
        position: relative;
        word-wrap: break-word;
        line-height: 1.3;
    }

    /* Tooltip Container */
    .menu-tooltip {
        position: relative;
        display: inline-block;
        width: 100%;
    }

    /* Tooltip Text */
    .menu-tooltip .tooltip-text {
        visibility: hidden;
        opacity: 0;
        background: linear-gradient(135deg, #1f2937, #374151);
        color: white;
        text-align: left;
        border-radius: 8px;
        padding: 10px 15px;
        position: fixed;
        z-index: 999999;
        font-size: 14px;
        font-weight: 500;
        white-space: nowrap;
        min-width: max-content;
        max-width: 350px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
        border: 2px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
        transform: scale(0.8);
        pointer-events: none;
        left: 280px;
        top: 100px;
    }

    /* Tooltip Arrow */
    .menu-tooltip .tooltip-text::before {
        content: "";
        position: absolute;
        top: 50%;
        left: -8px;
        transform: translateY(-50%);
        border-top: 8px solid transparent;
        border-bottom: 8px solid transparent;
        border-right: 8px solid #1f2937;
        filter: drop-shadow(-2px 0 4px rgba(0, 0, 0, 0.1));
    }

    /* Show Tooltip on Hover */
    .menu-tooltip:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
        transform: translateX(0);
    }

    /* Prevent tooltip from interfering with menu functionality */
    .menu-tooltip .tooltip-text {
        user-select: none;
    }

    /* Force tooltip visibility on hover - CSS fallback */
    .menu-tooltip:hover .tooltip-text {
        visibility: visible !important;
        opacity: 1 !important;
        transform: scale(1) !important;
        display: block !important;
    }

    /* Responsive tooltip positioning */
    @media (max-width: 768px) {
        .menu-tooltip .tooltip-text {
            font-size: 12px;
            padding: 6px 10px;
            margin-left: 10px;
        }
    }

    /* Better tooltip positioning for smaller screens */
    @media (max-width: 1200px) {
        .menu-tooltip .tooltip-text {
            left: 100%;
            margin-left: 10px;
        }
    }

    /* Special styling for truncated text */
    .truncated-text {
        position: relative;
    }

    .truncated-text:hover {
        cursor: help;
    }

    /* Debug styles - add red border to see tooltip areas */
    .menu-tooltip {
        position: relative;
    }
    
    /* Ensure proper z-index stacking */
    .layout-menu .menu-tooltip .tooltip-text {
        z-index: 99999 !important;
    }

    /* Booking Count Badge Styling */
    .menu-link .badge {
        font-size: 0.65rem !important;
        padding: 0.25rem 0.5rem !important;
        font-weight: 600 !important;
        min-width: 1.5rem;
        height: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1 !important;
        border: 2px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        animation: pulseCount 2s infinite;
    }

    @keyframes pulseCount {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        50% {
            transform: scale(1.05);
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
        }
    }

    /* Active menu item badge styling */
    .menu-item.active .menu-link .badge {
        background-color: rgba(255, 255, 255, 0.9) !important;
        color: #dc3545 !important;
        border-color: rgba(255, 255, 255, 0.5);
    }

    /* Submenu badge styling */
    .menu-sub .menu-link .badge {
        font-size: 0.6rem !important;
        padding: 0.2rem 0.4rem !important;
        min-width: 1.2rem;
        height: 1.2rem;
    }

    /* Prevent badge from interfering with hover effects */
    .menu-link .badge {
        pointer-events: none;
        flex-shrink: 0;
    }

    /* Ensure proper spacing in menu items with badges */
    .menu-link .d-flex {
        width: 100%;
        min-height: 1.5rem;
        align-items: center;
    }
</style>
        <body>
            <div class="layout-wrapper layout-content-navbar  ">
    <div class="layout-container">
        <!-- Menu -->
        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
            <div class="app-brand demo ">
                <a href="{{ url('/index') }}" class="app-brand-link">
                    <span class="app-brand-logo demo">
                        @php
                        $logoSetting = \App\Helpers\CommonHelper::masterSettingsName('logo');
                        $fileStorage = \App\Helpers\CommonHelper::masterSettingsName('file_storage')['master_value']
                        ?? 'local'; // Default to local if not set
                        @endphp
                        <div class="logo-icon">
                            <img src="{{ $logoSetting['master_value'] }}" class="logo-img rounded-logo" alt="Logo">
                        </div>
                        {{-- <div class="logo-name flex-grow-1">
                            <h5 class="mb-0 text-white">
                                {{ \App\Helpers\CommonHelper::masterSettingsName('name')['master_value'] }}</h5>
                        </div> --}}
                    </span>
                    </span>
                    <span class="app-brand-text demo menu-text fw-semibold ms-2">
                        @php
                            $name = \App\Helpers\CommonHelper::masterSettingsName('name')['master_value'];
                            $limit = 10;
                            $displayName = strlen($name) > $limit ? substr($name, 0, $limit) : $name;
                        @endphp
                        <span class="small-brand-text" title="{{ $name }}">{{ $displayName }}</span>
                        @if(strlen($name) > $limit)
                            <span class="animated-ellipsis"><span>.</span><span>.</span><span>.</span></span>
                        @endif
                    </span>
                </a>
                <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                    <i class="menu-icon tf-icons ri-menu-fold-line" style="margin-left: 10px"></i>
                </a>
            </div>
            <div class="menu-inner-shadow"></div>
            <!-- Dashboards -->
            <ul class="menu-inner py-1" style="padding-bottom: 100px;">
                <li class="menu-item" style="height: 8px;"></li>
                <li class="menu-item @if(Request::is('dashboard')) active @endif">
                    <a href="{{ route('dashboard') }}" class="menu-link">
                        <i class="menu-icon tf-icons ri-dashboard-3-line"></i>
                        <div data-i18n="Dashboard">Dashboard</div>
                    </a>
                </li>

                <!-- <li class="menu-item" style="height: 8px;"></li>
                @if(in_array(auth()->user()->role_id, [2, 33]))
                <li class="menu-item @if(Request::is('custom-packages*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-gift-line"></i>
                        <div data-i18n="Custom Packages">Custom Packages</div>
                    </a>
                    <ul class="menu-sub">
                        
                        <li class="menu-item @if(Request::is('custom-packages/create')) active @endif">
                            <a href="{{ route('custom-packages.create') }}" class="menu-link">
                                <div data-i18n="Create Custom Package">Create Custom Package</div>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif -->

                <!-- Tour -->
            @if(hasPermission('view tour'))
            
        @endif

        <!-- End Tour -->

        @if(in_array(auth()->user()->role_id, [1, 2, 3, 4, 20, 21, 22, 11, 33, 34, 36, 37, 38, 128, 129, 130, 134, 135, 136, 138]))
        <!-- Bookings -->
        {{-- @if(hasPermission('view booking')) --}}
        <li class="menu-header mt-5">
            <span class="menu-header-text" data-i18n="Bookings">Bookings</span>
        </li>
        
        <li class="menu-item @if(Request::is('bookings/*') && !Request::is('bookings/tentative') || Request::is('predefined-package-booking-list') || Request::is('enquirylist')) open active @endif">
            <a href="#" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri-bookmark-3-line"></i>
                <div data-i18n="Bookings">Bookings</div>
            </a>
            <ul class="menu-sub">
                @if(in_array(auth()->user()->role_id, [1, 2, 11, 33,  12, 37, 38, 128, 129, 130, 134, 135, 136, 138]))
                <li class="menu-item @if(Request::is('enquirylist') && !Request::is('enquiries*')) active @endif">
                    <a href="{{ route('enquirylist.index') }}" class="menu-link">
                        <div data-i18n="Quick Enquiry">Quick Enquiry</div>
                    </a>
                </li>
                @endif
                <li class="menu-item @if(Request::is('bookings/new-enquiries')) active @endif">
                    <a href="{{ route('bookings.new-enquiries') }}" class="menu-link" >
                        <div class="d-flex justify-content-between align-items-center">
                            <span data-i18n="Enquiries">Enquiries</span>
                            @if(isset($bookingCounts) && $bookingCounts['new_enquiries'] > 0)
                                <span class="badge bg-danger rounded-pill text-white ms-2">{{ $bookingCounts['new_enquiries'] }}</span>
                            @endif
                        </div>
                    </a>
                </li>
                <!-- Show Booking -->
                <li class="menu-item @if(Request::is('bookings/follow-ups')) active @endif">
                    <a href="{{ route('bookings.follow-ups') }}" class="menu-link" title="Follow Ups">
                        <div class="d-flex justify-content-between align-items-center">
                            <span data-i18n="Follow Ups">Follow Ups</span>
                            @if(isset($bookingCounts) && $bookingCounts['follow_ups'] > 0)
                                <span class="badge bg-danger rounded-pill text-white ms-2">{{ $bookingCounts['follow_ups'] }}</span>
                            @endif
                        </div>
                    </a>
                </li>

                <li class="menu-item @if(Request::is('bookings/confirmed')) active @endif">
                    <a href="{{ route('bookings.confirmed') }}" class="menu-link" title="On Hold Bookings">
                        <div class="d-flex justify-content-between align-items-center">
                            <span data-i18n="Confirmed">Confirmed</span>
                            @if(isset($bookingCounts) && $bookingCounts['confirmed'] > 0) 
                                <span class="badge bg-danger rounded-pill text-white ms-2">{{ $bookingCounts['confirmed'] }}</span>
                            @endif
                        </div>
                    </a>
                </li>
                <li class="menu-item @if(Request::is('bookings/definite')) active @endif">
                    <a href="{{ route('bookings.definite') }}" class="menu-link" title="Definite Bookings">
                        <div class="d-flex justify-content-between align-items-center">
                            <span data-i18n="Definite">Definite</span>
                            @if(isset($bookingCounts) && $bookingCounts['definite'] > 0)
                                <span class="badge bg-danger rounded-pill text-white ms-2">{{ $bookingCounts['definite'] }}</span>
                            @endif
                        </div>
                    </a>
                </li>
                <li class="menu-item @if(Request::is('bookings/actual')) active @endif">
                    <a href="{{ route('bookings.actual') }}" class="menu-link" title="Actual Bookings">
                        <div class="d-flex justify-content-between align-items-center">
                            <span data-i18n="Actual">Actual</span>
                            @if(isset($bookingCounts) && $bookingCounts['actual'] > 0)
                                <span class="badge bg-danger rounded-pill text-white ms-2">{{ $bookingCounts['actual'] }}</span>
                            @endif
                        </div>
                    </a>
                </li>
                {{-- <li class="menu-item @if(Request::is('bookings/cancelled') || Request::is('bookings/refunds')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-close-circle-line"></i>
                        <div class="d-flex justify-content-between align-items-center">
                            <span data-i18n="Cancellations & Refunds">Cancellations & Refunds</span>
                            @if(isset($bookingCounts) && ($bookingCounts['cancelled'] > 0 || $bookingCounts['refunds'] > 0))
                                <span class="badge bg-danger rounded-pill text-white ms-2">{{ $bookingCounts['cancelled'] + $bookingCounts['refunds'] }}</span>
                            @endif
                        </div>
                    </a>
                    <ul class="menu-sub"> --}}
                        <li class="menu-item @if(Request::is('bookings/cancelled')) active @endif">
                            <a href="{{ route('bookings.cancelled') }}" class="menu-link" title="Cancelled Bookings">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span data-i18n="Cancelled">Cancelled</span>
                                    @if(isset($bookingCounts) && $bookingCounts['cancelled'] > 0)
                                        <span class="badge bg-danger rounded-pill text-white ms-2">{{ $bookingCounts['cancelled'] }}</span>
                                    @endif
                                </div>
                            </a>
                        </li>
                        {{-- <li class="menu-item @if(Request::is('bookings/refunds')) active @endif">
                            <a href="{{ route('bookings.refunds') }}" class="menu-link" title="Refunds">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span data-i18n="Refunds">Refunds</span>
                                    @if(isset($bookingCounts) && $bookingCounts['refunds'] > 0)
                                        <span class="badge bg-danger rounded-pill text-white ms-2">{{ $bookingCounts['refunds'] }}</span>
                                    @endif
                                </div>
                            </a>
                        </li> --}}
                    {{-- </ul>
                </li> --}}
                @if(in_array(auth()->user()->role_id, [1,2,11, 33, 128, 129, 130, 134, 135, 136, 138, 34, 36, 37, 38]))
                    <!-- Show Booking -->
                    <li class="menu-item @if(Request::is('predefined-package-booking-list')) active @endif">
                        <a href="{{ route('predefined.package.booking.list') }}" class="menu-link" title="Packages">
                            {{-- <i class="menu-icon tf-icons ri-gift-line"></i> --}}
                            <div data-i18n="Packages" class="menu-tooltip">
                                <span class="menu-text-with-tooltip">Packages</span>
                                <span class="tooltip-text">Packages</span>
                            </div>
                        </a>
                    </li>
                    @endif
            </ul>
        </li>  
    @endif
    <!-- End Bookings --> 

        <!-- Booking List -->
        {{-- @if(hasPermission('view booking')) --}}
           {{-- <li class="menu-header mt-5">
                <span class="menu-header-text" data-i18n="Booking List">Booking List</span>
            </li>
            
            <li class="menu-item @if(Request::is('bookinglist*') || Request::is('enquiries')) open active @endif">
                <a href="#" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ri-bookmark-3-line"></i>
                    <div data-i18n="Booking List">Booking List</div>
                </a>
                <ul class="menu-sub">
                    <!-- Show Booking -->
                    <li class="menu-item @if(Request::is('bookinglist')) active @endif">
                        <a href="{{ route('bookinglist.index') }}" class="menu-link">
                            <div data-i18n="Booking List">Booking List</div>
                        </a>
                    </li>
                    <li class="menu-item @if(Request::is('enquiries')) active @endif">
                        <a href="{{ route('bookinglist.enquiry') }}" class="menu-link">
                            <div data-i18n="Enquiry List">Enquiry List</div>
                        </a>
                    </li>
                </ul>
            </li>  --}}
        {{-- @endif --}}
        <!-- End Booking List --> 

        {{-- Dmc = 11, Sales Head(dmc) = 33, Sales Manager(dmc) = [12, 37], Asst. Sales Manager(dmc) = 38 --}}
            {{-- @if(in_array(auth()->user()->role_id, [1, 2, 11, 33,  12, 37, 38, 128, 129, 130, 134, 135, 136, 138]))
            @if(hasPermission('view enquiry'))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Enquiries">Enquiries</span>
                </li>
                
                <li class="menu-item @if(Request::is('enquirylist*') && !Request::is('enquiries*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-customer-service-2-line" style="color: #3565bd"></i>
                        <div data-i18n="Enquiries">Enquiries</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item @if(Request::is('enquirylist') && !Request::is('enquiries*')) active @endif">
                            <a href="{{ route('enquirylist.index') }}" class="menu-link">
                                <div data-i18n="Enquiries">Enquiries</div>
                            </a>
                        </li>
                    </ul>
                </li>  
            @endif
            @endif --}}

            <!-- Enquiry -->
            <!-- @if(in_array(auth()->user()->role_id, [1,2,3,4,5,6,7,8,9,10,11,12,13, 14, 15, 16, 17,20,21,22,37, 49, 50, 51, 52, 53, 64, 65, 66, 67, 68, 90, 124, 125, 33, 37, 128, 129, 130, 134, 135, 136, 138]))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Negotiation">Negotiation</span>
                </li>

                <li class="menu-item @if(Request::is('enquiry') && !Request::is('enquiries*') && !Request::is('enquirylist*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-questionnaire-line"></i>
                        <div data-i18n="Negotiation">Negotiation</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item @if(Request::is('enquiry') && !Request::is('enquiries*') && !Request::is('enquirylist*')) active @endif">
                            <a href="{{ route('enquiry') }}" class="menu-link">
                                <div data-i18n="Negotiation List">Negotiation List</div>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif -->

            <!-- End Enquiry -->

            <!-- Products Section -->
            <li class="menu-header mt-5">
                <span class="menu-header-text" data-i18n="All Products">All Products</span>
            </li>

            <li class="menu-item @if(Request::is('packages*') || Request::is('packaged-attractions*') || Request::is('hotels*') || Request::is('attraction*') || Request::is('restaurant*') || Request::is('guide*') || Request::is('vehicle*') || Request::is('driver*') || Request::is('category*') || Request::is('facility*') || Request::is('ports*') || Request::is('single-tour-package*') || Request::is('zones*')) open active @endif">
                <a href="#" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ri-stack-line" style="color: #3565bd"></i>
                    <div data-i18n="All Products">All Products</div>
                </a>
                <ul class="menu-sub">
                    <!-- Packages -->
            {{-- <li class="menu-item @if((Request::is('packages*') && !Request::is('packaged-attractions*')) || Request::is('predefined-package-booking-list') || Request::is('single-tour-package*')) open active @endif">
                <a href="#" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ri-gift-line"></i>
                    <div data-i18n="Packages">Packages</div>
                </a>
                <ul class="menu-sub"> --}}
                    <li class="menu-item @if(Request::is('packages') && !Request::is('packaged-attractions*')) active @endif">
                        <a href="{{ route('packages.index') }}" class="menu-link" title="Packages">
                            {{-- <i class="menu-icon tf-icons ri-gift-line"></i> --}}
                            <div data-i18n="Packages" class="menu-tooltip">
                                <span class="menu-text-with-tooltip">Packages</span>
                                <span class="tooltip-text">Packages</span>
                            </div>
                        </a>
                    </li>
                    {{-- <li class="menu-item @if(Request::is('packages/create')) active @endif">
                        <a href="{{ route('packages.create') }}" class="menu-link">
                            <div data-i18n="Create Package" class="menu-tooltip">
                                <span class="menu-text-with-tooltip">Create Predefined Package</span>
                                <span class="tooltip-text">Create Predefined Package</span>
                            </div>
                        </a>
                    </li> --}}
                    @if(in_array(auth()->user()->role_id, [33]))
                    <!-- Single Tour Package for DMCs -->
                     <li class="menu-item @if(Request::is('single-tour-package/create')) active @endif">
                        <a href="{{ route('single-tour-package.create') }}" class="menu-link" title="Create Tour Package">
                                <div data-i18n="Create Tour Package" class="menu-tooltip">
                                <span class="menu-text-with-tooltip">Create Tour Package</span>
                                <span class="tooltip-text">Create Tour Package</span>
                            </div>
                        </a>
                    </li> 
                    {{-- <li class="menu-item @if(Request::is('single-tour-package')) active @endif">
                        <a href="{{ route('single-tour-package.index') }}" class="menu-link">
                            <div data-i18n="Single Tour Packages" class="menu-tooltip">
                                <span class="menu-text-with-tooltip">Single Tour Packages</span>
                                <span class="tooltip-text">Single Tour Packages</span>
                            </div>
                        </a>
                    </li> --}}
                    @endif
                    
                {{-- </ul>
            </li> --}}

            <!-- Packaged Attractions -->
            {{-- <li class="menu-item @if(Request::is('packaged-attractions*') && !Request::is('packaged-attractions/packaged-attraction-approval*')) open active @endif">
                <a href="#" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ri-stack-line"></i>
                    <div data-i18n="Attraction Package">Attraction Package</div>
                </a>
                <ul class="menu-sub"> --}}
                    {{-- <li class="menu-item @if(Request::is('packaged-attractions')) active @endif">
                        <a href="{{ route('packaged-attractions.index') }}" class="menu-link" title="Packaged Attractions & Create Tab">
                            <i class="menu-icon tf-icons ri-stack-line"></i>
                            <div data-i18n="Packaged Attractions & Create Tab" class="menu-tooltip">
                                <span class="menu-text-with-tooltip">Packaged Attractions & Create Tab</span>
                                <span class="tooltip-text">Packaged Attractions & Create Tab</span>
                            </div>
                        </a>
                    </li> --}}
                    {{-- <li class="menu-item @if(Request::is('packaged-attractions/create')) active @endif">
                        <a href="{{ route('packaged-attractions.create') }}" class="menu-link">
                            <div data-i18n="Create Attraction Package" class="menu-tooltip">
                                <span class="menu-text-with-tooltip">Create Packaged Attraction</span>
                                <span class="tooltip-text">Create Packaged Attraction</span>
                            </div>
                        </a>
                    </li> --}}
                {{-- </ul>
            </li> --}}

            <!-- Hotels & Accommodations -->
            {{-- @if(hasPermission('view hotel') || auth()->user()->role_id == 1 || auth()->user()->role_id == 2 || hasPermission('create hotel'))
            <li class="menu-item @if(Request::is('hotels') || Request::is('hotels/create')) open active @endif">
                <a href="#" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ri-hotel-line"></i>
                    <div data-i18n="Hotels & Accommodations">Hotels & Accommodations</div>
                </a>
                <ul class="menu-sub"> --}}
                    @if(hasPermission('view hotel') || auth()->user()->role_id == 1 || auth()->user()->role_id == 2)
                    <li class="menu-item @if(Request::is('hotels')) active @endif">
                        <a href="{{ route('hotels.index') }}" class="menu-link" title="Hotels & Accommodations">
                            {{-- <i class="menu-icon tf-icons ri-hotel-line"></i> --}}
                            <div data-i18n="Hotels & Accommodations" class="menu-tooltip">
                                <span class="menu-text-with-tooltip">Hotels & Accommodations</span>
                                <span class="tooltip-text">Hotels & Accommodations</span>
                            </div>
                        </a>
                    </li>
                    @endif
                    {{-- @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 20)
                    @if(hasPermission('create hotel'))
                    <li class="menu-item @if(Request::is('hotels/create')) active @endif">
                        <a href=" {{ route('hotels.create') }}" class="menu-link">
                        <div data-i18n="Create Hotels">Create Hotels</div>
                        </a>
                    </li>
                    @endif
                    @endif
                </ul>
            </li>
            @endif --}}

            <!-- Attractions & Experiences -->
            @if(hasPermission('view attraction') || hasPermission('create attraction'))
            <li class="menu-item @if(Request::is('attraction*') && !Request::is('attractions/attraction-approval*') || Request::is('packaged-attractions')) open @endif">
                <a href="#" class="menu-link menu-toggle">
                    {{-- <i class="menu-icon tf-icons ri-camera-3-line"></i> --}}
                    <div data-i18n="Attractions">Attractions</div>
                </a>
                <ul class="menu-sub">
                    @if(hasPermission('view attraction'))
                    <li class="menu-item @if(Request::is('attraction')) active @endif">
                        <a href="{{ route('attraction.index') }}" class="menu-link" title="Attractions & Experiences">
                            {{-- <i class="menu-icon tf-icons ri-camera-3-line"></i> --}}
                            <div data-i18n="Attractions & Experiences" class="menu-tooltip">
                                <span class="menu-text-with-tooltip">Attractions & Experiences</span>
                                <span class="tooltip-text">Attractions & Experiences</span>
                            </div>
                        </a>
                    </li>
                    @endif
                    <li class="menu-item @if(Request::is('packaged-attractions')) active @endif">
                        <a href="{{ route('packaged-attractions.index') }}" class="menu-link" title="Bundle Attractions">
                            {{-- <i class="menu-icon tf-icons ri-stack-line"></i> --}}
                            <div data-i18n="Bundle Attractions" class="menu-tooltip">
                                <span class="menu-text-with-tooltip">Bundle Attractions</span>
                                <span class="tooltip-text">Bundle Attractions</span>
                            </div>
                        </a>
                    </li>
                    {{-- @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 20)
                    @if(hasPermission('create attraction'))
                    <li class="menu-item @if(Request::is('attraction/create')) active @endif">
                        <a href="{{ route('attraction.create') }}" class="menu-link">
                            <div data-i18n="Create Attractions & Experiences" class="menu-tooltip">
                                <span class="menu-text-with-tooltip">Create Attractions & Experiences</span>
                                <span class="tooltip-text">Create Attractions & Experiences</span>
                            </div>
                        </a>
                    </li>
                    @endif
                    @endif --}}
                </ul>
            </li>
            @endif

            <!-- Restaurant & Dining -->
            {{-- @if(hasPermission('view restaurant') || hasPermission('create restaurant'))
            <li class="menu-item @if(Request::is('restaurant*') && !Request::is('restaurants/restaurant-approval*')) open active @endif">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ri-restaurant-2-line"></i>
                    <div data-i18n="Restaurant & Dining">Restaurant & Dining</div>
                </a>
                <ul class="menu-sub"> --}}
                    @if(hasPermission('view restaurant'))
                    <li class="menu-item @if(Request::is('restaurant')) active @endif">
                        <a href="{{ route('restaurant.index') }}" class="menu-link" title="Restaurant & Dining" >
                            {{-- <i class="menu-icon tf-icons ri-restaurant-2-line"></i> --}}
                            <div data-i18n="Restaurant & Dining" class="menu-tooltip">
                                <span class="menu-text-with-tooltip">Restaurant & Dining</span>
                                <span class="tooltip-text">Restaurant & Dining</span>
                            </div>
                        </a>
                    </li>
                    @endif
                    {{-- @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 20)
                    @if(hasPermission('create restaurant'))
                    <li class="menu-item @if(Request::is('restaurant/create')) active @endif">
                        <a href="{{ route('restaurant.create') }}" class="menu-link">
                            <div data-i18n="Create Restaurant">Create Restaurant</div>
                        </a>
                    </li>
                    @endif
                    @endif
                </ul>
            </li>
            @endif --}}

            <!-- Tour Guides -->
            {{-- @if(hasPermission('view guide') || hasPermission('create guide'))
            <li class="menu-item @if(Request::is('guide*') && !Request::is('guide/guide-approval*')) open active @endif">
                <a href="#" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ri-compass-3-line"></i>
                    <div data-i18n="Guide">Tour Guides</div>
                </a>
                <ul class="menu-sub"> --}}
                    @if(hasPermission('view guide'))
                    <li class="menu-item @if(Request::is('guide')) active @endif">
                        <a href="{{ route('guide.index') }}" class="menu-link" title="Tour Guides">
                            {{-- <i class="menu-icon tf-icons ri-compass-3-line"></i> --}}
                            <div data-i18n="Tour Guides" class="menu-tooltip">
                                <span class="menu-text-with-tooltip">Tour Guides</span>
                                <span class="tooltip-text">Tour Guides</span>
                            </div>
                        </a>
                    </li>
                    @endif
                    {{-- @if(hasPermission('create guide'))
                    <li class="menu-item @if(Request::is('guide/create')) active @endif">
                        <a href="{{ route('guide.create') }}" class="menu-link">
                            <div data-i18n="Create Tour Guide">Create Tour Guide</div>
                        </a>
                    </li>
                    @endif
                </ul>
            </li>
            @endif --}}

            <!-- Vehicles -->
            {{-- @if(hasPermission('view vehicle') || hasPermission('create vehicle'))
            <li class="menu-item @if(Request::is('vehicle*')) open active @endif">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ri-car-line"></i>
                    <div data-i18n="Vehicles">Vehicles</div>
                </a>
                <ul class="menu-sub"> --}}
                    @if(hasPermission('view vehicle'))
                    <li class="menu-item @if(Request::is('vehicle')) active @endif">
                        <a href="{{ route('vehicle.index') }}" class="menu-link" title="Vehicles">
                            {{-- <i class="menu-icon tf-icons ri-car-line"></i> --}}
                            <div data-i18n="Vehicles" class="menu-tooltip">
                                <span class="menu-text-with-tooltip">Vehicles</span>
                                <span class="tooltip-text">Vehicles</span>
                            </div>
                        </a>
                    </li>
                    @endif
                    {{-- @if(hasPermission('create vehicle'))
                    <li class="menu-item @if(Request::is('vehicle/create')) active @endif">
                        <a href="{{ route('vehicle.create') }}" class="menu-link">
                            <div data-i18n="Create Vehicles">Create Vehicle</div>
                        </a>
                    </li>
                    @endif
                </ul>
            </li>
            @endif --}}

            <!-- Drivers -->
            {{-- @if(hasPermission('view driver') || hasPermission('create driver'))
            <li class="menu-item @if(Request::is('driver*') && !Request::is('driver/driver-approval*')) open active @endif">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ri-steering-2-line"></i>
                    <div data-i18n="Driver">Drivers</div>
                </a>
                <ul class="menu-sub"> --}}
                    @if(hasPermission('view driver'))
                    <li class="menu-item @if(Request::is('driver')) active @endif">
                            <a href="{{ route('driver.index') }}" class="menu-link" title="Drivers">
                            {{-- <i class="menu-icon tf-icons ri-steering-2-line"></i> --}} 
                            <div data-i18n="Drivers" class="menu-tooltip">
                                <span class="menu-text-with-tooltip">Drivers</span>
                                <span class="tooltip-text">Drivers</span>
                            </div>
                        </a>
                    </li>
                    @endif
                    {{-- @if(hasPermission('create driver'))
                    <li class="menu-item @if(Request::is('driver/create')) active @endif">
                        <a href="{{ route('driver.create') }}" class="menu-link">
                            <div data-i18n="Create Driver">Create Driver</div>
                        </a>
                    </li>
                    @endif
                </ul>
            </li>
            @endif --}}

            <!-- Product Configuration -->
           
            @if(hasPermission('view facility') || hasPermission('view category'))
            <li class="menu-item @if(Request::is('category*') || Request::is('facility*') || Request::is('zones*') || Request::is('ports*')) open @endif">
                 <a href="#" class="menu-link menu-toggle" title="Product Configuration">
                     {{-- <i class="menu-icon tf-icons ri-function-line"></i> --}}
                     <div data-i18n="Product Configuration">Product Configuration</div>
                 </a>
                 <ul class="menu-sub">
                     @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
                     @if(hasPermission('view category'))
                     <li class="menu-item @if(Request::is('category')) active @endif">
                         <a href="{{ route('category.index') }}" class="menu-link" title="Facility Categories">
                             <div data-i18n="Facility Categories" class="menu-tooltip">
                                 <span class="menu-text-with-tooltip">Facility Categories</span>
                                 <span class="tooltip-text">Facility Categories</span>
                             </div>
                         </a>
                     </li>
                     @endif
                     @if(hasPermission('view facility'))
                     <li class="menu-item @if(Request::is('facility')) active @endif">
                         <a href="{{ route('facility.index') }}" class="menu-link" title="Facilities">
                             <div data-i18n="Facilities" class="menu-tooltip">
                                 <span class="menu-text-with-tooltip">Facilities</span>
                                 <span class="tooltip-text">Facilities</span>
                             </div>
                         </a>
                     </li>
                     @endif
                     @endif
                     @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 2)
                     <!-- Show Port -->
                     <li class="menu-item @if(Request::is('ports')) active @endif">
                         <a href="{{ route('ports.index') }}" class="menu-link">
                             {{-- <i class="menu-icon tf-icons ri-ship-line"></i> --}}
                             <div data-i18n="Ports" class="menu-tooltip">
                                 <span class="menu-text-with-tooltip">Ports</span>
                                 <span class="tooltip-text">Ports</span>
                             </div>
                         </a>
                     </li> 
                     @endif

                  <!-- Zones (hard-coded link under Product Configuration) -->
                  @if(Auth::user()->role_id == 11)
                 <li class="menu-item @if(Request::is('zones')) active @endif">
                     <a href="{{ route('zones.index') }}" class="menu-link" title="Zones">
                            <div data-i18n="Zones" class="menu-tooltip">
                                <span class="menu-text-with-tooltip">Zones</span>
                                <span class="tooltip-text">Zones</span>
                            </div>
                        </a>
                    </li>
                    @endif
                 </ul>
             </li>
             @endif

            <!-- Ports -->
            {{-- @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 2)
            <li class="menu-item @if(Request::is('ports*')) open active @endif">
                <a href="#" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ri-ship-line"></i>
                    <div data-i18n="Ports">Ports</div>
                </a>
                <ul class="menu-sub"> --}}
                    
                    {{-- <!-- Add Port -->
                    <li class="menu-item @if(Request::is('ports/create')) active @endif">
                        <a href="{{ route('ports.create') }}" class="menu-link">
                            <div data-i18n="Create Port">Create Port</div>
                        </a>
                    </li>
                </ul>
            </li>
            @endif --}}
                </ul>
            </li>

            <!-- Predefined Packages Booking List -->
            {{-- @if(in_array(auth()->user()->role_id, [1,2,11, 33, 128, 129, 130, 134, 135, 136, 138, 34, 36, 37, 38]))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Predefined Packages Booking List">Predefined Packages Booking List</span>
                </li>
                
                <li class="menu-item @if(Request::is('predefined-package-booking-list*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ri-suitcase-line" style="color: #1E90FF;"></i>

                        <div data-i18n="Predefined Packages Booking List">Predefined Packages Booking List</div>
                    </a>
                    <ul class="menu-sub">
                        <!-- Show Booking -->
                        <li class="menu-item @if(Request::is('predefined-package-booking-list')) active @endif">
                            <a href="{{ route('predefined.package.booking.list') }}" class="menu-link">
                                <div data-i18n="Predefined Packages Booking List">Predefined Packages Booking List</div>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif --}}
            <!-- End Predefined Packages Booking List -->

            <!-- Reports -->
            <li class="menu-header mt-5">
                <span class="menu-header-text" data-i18n="View Reports">View Reports</span>
            </li>
            <li class="menu-item @if(Request::is('reports/sales-revenue*') || Request::is('reports/ledger') || Request::is('reports/balance-sheet*')) open active @endif">
                <a href="#" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ri-bar-chart-box-line"></i>
                    <div data-i18n="Reports">Reports</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item @if(Request::is('reports/sales-revenue')) active @endif">
                        <a href="{{ route('reports.sales-revenue') }}" class="menu-link">
                            
                            <div data-i18n="Sales & Revenue">Sales & Revenue</div>
                        </a>
                    </li>
                    <li class="menu-item @if(Request::is('reports/ledger')) active @endif">
                        <a href="{{ route('reports.ledger') }}" class="menu-link">
                            <div data-i18n="Ledger">Ledger</div>
                        </a>
                    </li>
                    {{-- <li class="menu-item @if(Request::is('reports/balance-sheet')) active @endif">
                        <a href="{{ route('reports.balance-sheet') }}" class="menu-link">
                            <div data-i18n="Balance Sheet & P&L">Balance Sheet & P&L</div>
                        </a>
                    </li> --}}
                </ul>
            </li>
            <!-- End Reports -->
            
            <!-- Bulk Upload -->
            {{-- @if(in_array(auth()->user()->role_id, [11, 20]))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Bulk Upload">Bulk Upload</span>
                </li>
                
                <li class="menu-item @if(Request::is('bulk-upload*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-upload-cloud-2-line" style="color: #10b981 !important; background: rgba(16, 185, 129, 0.1);"></i>
                        <div data-i18n="Bulk Upload">Bulk Upload</div>
                    </a>
                    <ul class="menu-sub"> --}}
                        {{-- <li class="menu-item @if(Request::is('bulk-upload/hotels')) active @endif">
                            <a href="{{ route('bulk-upload.hotels') }}" class="menu-link">
                                <div data-i18n="Hotels">Hotels</div>
                            </a>
                        </li> --}}
                        {{-- <li class="menu-item @if(Request::is('bulk-upload/guides')) active @endif">
                            <a href="{{ route('bulk-upload.guides') }}" class="menu-link">
                                <div data-i18n="Guides">Guides</div>
                            </a>
                        </li>
                        <li class="menu-item @if(Request::is('bulk-upload/drivers')) active @endif">
                            <a href="{{ route('bulk-upload.drivers') }}" class="menu-link">
                                <div data-i18n="Drivers">Drivers</div>
                            </a>
                        </li>
                        <li class="menu-item @if(Request::is('bulk-upload/vehicles')) active @endif">
                            <a href="{{ route('bulk-upload.vehicles') }}" class="menu-link">
                                <div data-i18n="Vehicles">Vehicles</div>
                            </a>
                        </li>
                        @if(auth()->user()->role_id !== '11')
                        <li class="menu-item @if(Request::is('bulk-upload/restaurants')) active @endif">
                            <a href="{{ route('bulk-upload.restaurants') }}" class="menu-link">
                                <div data-i18n="Restaurants">Restaurants</div>
                            </a>
                        </li>
                        <li class="menu-item @if(Request::is('bulk-upload/attractions')) active @endif">
                            <a href="{{ route('bulk-upload.attractions') }}" class="menu-link">
                                <div data-i18n="Attractions">Attractions</div>
                            </a>
                        </li>
                        @endif --}}
                        {{-- @if(auth()->user()->role_id == '11')
                        <li class="menu-item @if(Request::is('bulk-upload/tickets')) active @endif">
                            <a href="{{ route('bulk-upload.tickets') }}" class="menu-link">
                                <div data-i18n="Attraction Tickets">Attraction Tickets</div>
                            </a>
                        </li>
                        <li class="menu-item @if(Request::is('bulk-upload/meals')) active @endif">
                            <a href="{{ route('bulk-upload.meals') }}" class="menu-link">
                                <div data-i18n="Restaurant Meals">Restaurant Meals</div>
                            </a>
                        </li>
                        @endif --}}
                    {{-- </ul>
                </li>
            @endif --}}
            <!-- End Bulk Upload -->

                <!-- Jobsheets -->
                @if(in_array(Auth::user()->role_id, [1, 2, 7, 11 ,34, 66, 108, 128, 131, 132, 134, 135, 137, 138]))
                    <li class="menu-header small text-uppercase">
                        <span class="menu-header-text">Service Delivery</span>
                    </li>
                    <li class="menu-item @if(Request::is('jobsheet/view') || Request::is('jobsheet/create-guide-jobsheet') || Request::is('jobsheet/create-driver-jobsheet') || Request::is('jobsheet/drivers') || Request::is('jobsheet/guides')) open @endif">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-file-list-3-line"></i>

                        <div data-i18n="Service Delivery">Service Delivery</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item @if(Request::is('jobsheet/view') || Request::is('jobsheet/create-guide-jobsheet') || Request::is('jobsheet/create-driver-jobsheet')) open @endif">
                                <a href="javascript:void(0);" class="menu-link menu-toggle">
                                {{-- <i class="menu-icon tf-icons ri-file-list-3-line"></i> --}}
        
                                <div data-i18n="Jobsheets">Jobsheets</div>
                                </a>
                                <ul class="menu-sub">
                            <li class="menu-item @if(Request::is('jobsheet/view')) active @endif">
                                <a href="{{ route('jobsheet.view') }}" class="menu-link">
                                    <div data-i18n="View Jobsheets">View Jobsheets</div>
                                </a>
                            </li>
                            <li class="menu-item @if(Request::is('jobsheet/create-guide-jobsheet')) active @endif">
                                <a href="{{ route('jobsheet.create.guide') }}" class="menu-link">
                                    <div data-i18n="Guide Jobsheet">Guide Jobsheet</div>
                                </a>
                            </li>
                            <li class="menu-item @if(Request::is('jobsheet/create-driver-jobsheet')) active @endif">
                                <a href="{{ route('jobsheet.create.driver') }}" class="menu-link">
                                    <div data-i18n="Driver Jobsheet">Driver Jobsheet</div>
                                </a>
                            </li>
                            </ul>
                            </li>
                            @if(in_array(Auth::user()->role_id, [1 ,7,14,97,8,15,106, 10, 11, 26, 50, 98,51,107, 34,65, 99, 66, 108, 128, 131, 132, 134, 135, 137, 138]))
                    {{-- <li class="menu-header mt-5">
                        <span class="menu-header-text" data-i18n="Assigned Job">Assigned Job</span>
                    </li> --}}

                    <li class="menu-item @if(Request::is('jobsheet/drivers') || Request::is('jobsheet/guides')) open @endif">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            {{-- <i class="menu-icon tf-icons ri-task-line"></i> --}}
                            <div data-i18n="Assigned Jobs">Assigned Jobs</div>
                        </a>
                        <ul class="menu-sub">
                            @if(in_array(Auth::user()->role_id, [1, 2,7,14,97,8,15,106, 10, 11, 26, 51,107, 34, 66, 108, 128, 131, 132, 134, 135, 137, 138]))
                            <!-- Driver Jobs -->
                            <li class="menu-item @if(Request::is('jobsheet/drivers')) active @endif">
                                <a href="{{ route('jobsheet.drivers') }}" class="menu-link">
                                    <div data-i18n="Driver Jobs">Driver Jobs</div>
                                </a>
                            </li>
                            @endif

                            @if(in_array(Auth::user()->role_id, [1, 2,7,14,97, 10, 11, 26, 50, 98, 34, 65, 99, 128, 131, 132, 134, 135, 137, 138]))
                            <!-- Guide Jobs -->
                            <li class="menu-item @if(Request::is('jobsheet/guides')) active @endif">
                                <a href="{{ route('jobsheet.guides') }}" class="menu-link">
                                    <div data-i18n="Guide Jobs">Guide Jobs</div>
                                </a>
                            </li>

                            @endif
                        </ul>
                    </li>
                @endif
                        </ul>
                    </li>
                @endif

                <!-- End Jobsheets -->

                <!-- JobSheet -->
                {{-- @if(in_array(Auth::user()->role_id, [1 ,7,14,97,8,15,106, 10, 11, 26, 50, 98,51,107, 34,65, 99, 66, 108, 128, 131, 132, 134, 135, 137, 138]))
                    <li class="menu-header mt-5">
                        <span class="menu-header-text" data-i18n="Assigned Job">Assigned Job</span>
                    </li>

                    <li class="menu-item @if(Request::is('jobsheet/drivers') || Request::is('jobsheet/guides')) active open @endif">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons ri-task-line"></i>
                            <div data-i18n="Assigned Job">Assigned Job</div>
                        </a>
                        <ul class="menu-sub">
                            @if(in_array(Auth::user()->role_id, [1, 2,7,14,97,8,15,106, 10, 11, 26, 51,107, 34, 66, 108, 128, 131, 132, 134, 135, 137, 138]))
                            <!-- Driver Jobs -->
                            <li class="menu-item @if(Request::is('jobsheet/drivers')) active @endif">
                                <a href="{{ route('jobsheet.drivers') }}" class="menu-link">
                                    <div data-i18n="Driver Jobs">Driver Jobs</div>
                                </a>
                            </li>
                            @endif

                            @if(in_array(Auth::user()->role_id, [1, 2,7,14,97, 10, 11, 26, 50, 98, 34, 65, 99, 128, 131, 132, 134, 135, 137, 138]))
                            <!-- Guide Jobs -->
                            <li class="menu-item @if(Request::is('jobsheet/guides')) active @endif">
                                <a href="{{ route('jobsheet.guides') }}" class="menu-link">
                                    <div data-i18n="Guide Jobs">Guide Jobs</div>
                                </a>
                            </li>

                            @endif
                        </ul>
                    </li>
                @endif --}}
                <!-- End JobSheet -->
                


            {{-- @if(Auth::user()->role_id == 11)
            <li class="menu-header mt-5">
                <span class="menu-header-text" data-i18n="Zone">Zone</span>
            </li>

            <li class="menu-item @if(Request::is('zones*')) open active @endif">
                <a href="#" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ri-map-pin-user-line"></i>
                    <div data-i18n="Zone">Zone</div>
                </a>
                <ul class="menu-sub">
                    <!-- Show Zone -->
                    <li class="menu-item @if(Request::is('zones') && !Request::is('zones/create')) active @endif">
                        <a href="{{ route('zones.index') }}" class="menu-link">
                            <div data-i18n="Zone List">Zone List</div>
                        </a>
                    </li>
                    <!-- Add Zone -->
                    <li class="menu-item @if(Request::is('zones/create')) active @endif">
                        <a href="{{ route('zones.create') }}" class="menu-link">
                            <div data-i18n="Add Zone">Add Zone</div>
                        </a>
                    </li>
                </ul>
            </li>
            @endif --}}
            <!-- End Zone -->

            <!-- Services Management for DMC -->
            @php
                $allowedRoles = [11, 35, 74, 77, 78, 84, 93, 120, 130, 132, 133, 135, 136, 137, 138];
            @endphp

            @if(in_array(Auth::user()->role_id, $allowedRoles))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Services Management">Services Management</span>
                </li>

                <li class="menu-item @if(Request::is('services/*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-service-line"></i>
                        <div data-i18n="Services">Services</div>
                    </a>
                    <ul class="menu-sub">
                        <!-- DMC Hotels Selection -->
                        @php
                            $allowedRoles = [11, 35, 77, 84, 130, 132, 133, 135, 136, 137, 138];
                        @endphp
                        @if(in_array(Auth::user()->role_id, $allowedRoles))
                            <li class="menu-item @if(Request::is('services/hotels')) active @endif">
                                <a href="{{ route('services.hotels') }}" class="menu-link">
                                    <div data-i18n="Select Hotels">Select Hotels</div>
                                </a>
                            </li>
                        @endif
                        <!-- DMC Attractions Selection -->
                        @php
                            $allowedRoles = [11, 35,74, 93, 130, 132, 133, 135, 136, 137, 138];
                        @endphp
                        @if(in_array(Auth::user()->role_id, $allowedRoles))
                        <li class="menu-item @if(Request::is('services/attractions')) active @endif">
                            <a href="{{ route('services.attractions') }}" class="menu-link">
                                    <div data-i18n="Select Attractions">Select Attractions</div>
                                </a>
                            </li>
                        @endif
                        
                        <!-- DMC Restaurants Selection -->
                        @php
                            $allowedRoles = [11, 35, 78, 120, 130, 132, 133, 135, 136, 137, 138];
                        @endphp
                        @if(in_array(Auth::user()->role_id, $allowedRoles))
                        <li class="menu-item @if(Request::is('services/restaurants')) active @endif">
                            <a href="{{ route('services.restaurants') }}" class="menu-link">
                                <div data-i18n="Select Restaurants">Select Restaurants</div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
            @endif
            <!-- End Services Management -->

            <!-- Booking -->
            {{-- @if(auth()->user()->role_id == 21||auth()->user()->role_id == 26 || auth()->user()->role_id == 34 || auth()->user()->role_id == 124 
            || auth()->user()->role_id == 125 || auth()->user()->role_id == 1 || auth()->user()->role_id == 2 || auth()->user()->role_id == 3 ||
            auth()->user()->role_id == 4|| auth()->user()->role_id == 12|| auth()->user()->role_id == 28|| auth()->user()->role_id == 33 || 
            auth()->user()->role_id == 128 || auth()->user()->role_id == 129 || auth()->user()->role_id == 130 || auth()->user()->role_id == 134 ||
             auth()->user()->role_id == 135 || auth()->user()->role_id == 136 || auth()->user()->role_id == 138 || auth()->user()->role_id == 37)
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Hotel Booking">Hotel Booking</span>
                </li>
                <li class="menu-item @if(Request::is('booking*') && !Request::is('bookinglist*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-hotel-bed-line"></i>
                        <div data-i18n="Hotel Booking">Hotel Booking</div>
                    </a>
                    <ul class="menu-sub">
                        <!-- Show Booking -->
                        <li class="menu-item @if(Request::is('booking')) active @endif">
                            <a href="{{ route('booking.index') }}" class="menu-link">
                                <div data-i18n="Approve Hotel Booking List">Approve Hotel Booking List</div>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif  --}}

            {{-- @if(in_array(Auth::user()->role_id, [1, 2]))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Manage Approvals">Manage Approvals</span>
                </li>
                <li class="menu-item @if(Request::is('hotels/hotel-approval*', 'attractions/attraction-approval*', 'restaurants/restaurant-approval*', 'guide/guide-approval*', 'driver/driver-approval*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-shield-check-line"></i> 
                        <div data-i18n="Manage Approvals">Manage Approvals</div>
                    </a>                                       
                    <ul class="menu-sub">
                        <!-- Show Hotel Approval -->
                        @if(in_array(Auth::user()->role_id, [1, 2]))
                            <li class="menu-item @if(Request::is('hotels/hotel-approval')) active @endif">
                                <a href="{{ route('hotels.approval') }}" class="menu-link">
                                    <div data-i18n="Hotel Approval">Hotel Approval</div>
                                </a>
                            </li>
                        @endif

                        <!-- Show Attraction Approval -->
                        @if(in_array(Auth::user()->role_id, [1, 2]))
                            <li class="menu-item @if(Request::is('attractions/attraction-approval')) active @endif">
                                <a href="{{ route('attractions.approval') }}" class="menu-link">
                                    <div data-i18n="Attraction Approval">Attraction Approval</div>
                                </a>
                            </li>
                        @endif

                        <!-- Show Restaurant Approval -->
                        @if(in_array(Auth::user()->role_id, [1, 2]))
                            <li class="menu-item @if(Request::is('restaurants/restaurant-approval')) active @endif">
                                <a href="{{ route('restaurants.approval') }}" class="menu-link">
                                    <div data-i18n="Restaurant Approval">Restaurant Approval</div>
                                </a>
                            </li>
                        @endif

                        <!-- Show Guide Approval -->
                        @if(in_array(Auth::user()->role_id, [1, 2]))
                            <li class="menu-item @if(Request::is('guide/guide-approval')) active @endif">
                                <a href="{{ route('guide.approval') }}" class="menu-link">
                                    <div data-i18n="Guide Approval">Guide Approval</div>
                                </a>
                            </li>
                        @endif

                        <!-- Show Driver Approval -->
                        @if(in_array(Auth::user()->role_id, [1, 2]))
                        <li class="menu-item @if(Request::is('driver/driver-approval')) active @endif">
                            <a href="{{ route('driver.approval') }}" class="menu-link">
                                <div data-i18n="Driver Approval">Driver Approval</div>
                            </a>
                        </li>
                    @endif
                    </ul>
                </li>
            @endif --}}












                @if(auth()->user()->role_id == 11 || auth()->user()->role_id == 20)
                <!-- Agency Management -->
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Agency Management">Agency Management</span>
                </li>

                <li class="menu-item @if(Request::is('agency*')) open active @endif">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-building-line"></i>
                        <div data-i18n="Agency Management">Agency Management</div>
                    </a>
                    <ul class="menu-sub">
                        <!-- List Agencies -->
                        <li class="menu-item @if(Request::is('agencies')) active @endif">
                            <a href="{{ route('agencies.index') }}" class="menu-link">
                                <div data-i18n="List Agencies">List Agencies</div>
                            </a>
                        </li>

                        <!-- Create Agency -->
                        <li class="menu-item @if(Request::is('agencies/create')) active @endif">
                            <a href="{{ route('agencies.create') }}" class="menu-link">
                                <div data-i18n="Create Agency">Create Agency</div>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
                <!-- Operation Country -->
                {{-- @if(hasPermission('view country'))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="City Listing">City Listing</span>
                </li>
                <li class="menu-item @if(Request::is('country')) active @endif">
                    @if(hasPermission('view country'))
                    <a href="{{ route('country.index') }}" class="menu-link">
                        <i class="menu-icon ri-earth-line"></i>
                        <div data-i18n="List City">List City</div>
                    @endif
                    </a>
                </li>
                @endif --}}

                <!-- <li class="menu-header mt-4">
                    <span class="menu-header-text text-uppercase font-weight-bold" data-i18n="Report">Report</span>
                </li>
                
                <li class="menu-item @if(Request::is('report*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-bar-chart-box-line"></i>
                        <div data-i18n="Report">Report</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item @if(Request::is('report')) active @endif">
                            <a href="{{ route('report.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons ri-file-list-3-line"></i>
                                <div data-i18n="Sales Report">Sales Report</div>
                            </a>
                        </li>
                    </ul>
                </li>                 -->
                
                <!-- User Role Management -->
                @if(!(auth()->user()->role_id >= 79 && auth()->user()->role_id <= 123 ))
                @if(hasPermission('view users') || hasPermission('view roles') || hasPermission('view features') || hasPermission('view agent'))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="User Management">User Management</span>
                </li>
                <li class="menu-item @if(Request::is('users*', 'agents*', 'roles*', 'features*')) open @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-user-line"></i>
                        <div data-i18n="All Users">All Users</div>
                    </a>
                    <ul class="menu-sub">
                        @php
                            $excludedRoles = [81, 38, 84, 87, 90, 93, 96, 99, 102, 105, 108, 111, 114, 117, 120, 123, 124, 125, 126, 127];
                        @endphp

                        @if(hasPermission('view users') && !in_array(auth()->user()->role_id, $excludedRoles))
                            <li class="menu-item @if(Request::is('users')) active @endif">
                                <a href="{{ route('users.index') }}" class="menu-link">
                                    <div data-i18n="Users">Users</div>
                                </a>
                            </li>
                        @endif
                       

                        @if(hasPermission('view agent'))
                        <li class="menu-item @if(Request::is('agents')) active @endif">
                            <a href="{{ route('agents.index') }}" class="menu-link">
                                <div data-i18n="Agents">Agents</div>
                            </a>
                        </li>
                        @endif

                        <!-- Registered Agents View -->
                         @if(auth()->user()->role_id == 20 || auth()->user()->role_id == 19 || auth()->user()->role_id == 1 || auth()->user()->role_id == 2 || auth()->user()->role_id == 3 || auth()->user()->role_id == 4)
                            <li class="menu-item @if(Request::is('registered-agents*')) active @endif">
                                <a href="{{ route('registered-agents.index') }}" class="menu-link">
                                    <div data-i18n="Registered Agents">Registered Agents</div>
                                </a>
                            </li>
                        @endif
                        @if(hasPermission('view roles') && auth()->user()->user_type == 1)
                        <li class="menu-item @if(Request::is('roles')) active @endif">
                            <a href="{{ route('roles.index') }}" class="menu-link">
                                <div data-i18n="Roles">Roles</div>
                            </a>
                        </li>
                        @endif
                        {{-- <li class="menu-item @if(Request::is('countries')) active @endif">
                            <a href="{{ route('countries.index') }}" class="menu-link">
                                <div data-i18n="Countries">Countries</div>
                            </a>
                        </li> --}}
                        @if(hasPermission('view features') && auth()->user()->user_type == 1)
                        <li class="menu-item @if(Request::is('features')) active @endif">
                            <a href="{{ route('features') }}" class="menu-link">
                                <div data-i18n="Features">Features</div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif
                @endif
                <!-- End User Role Management -->   

                {{-- <!-- Settings -->
                @if(hasPermission('settings') || hasPermission('edit settings'))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Settings">Settings</span>
                </li>
                @endif
                <li class="menu-item @if(Request::is('master-setting')) active @endif">
                    <a href="{{ route('master-setting') }}" class="menu-link">
                        <i class="menu-icon tf-icons ri-settings-3-line"></i>
                        <div data-i18n="Master Settings">Master Settings</div>
                    </a>
                </li>
                @endif --}}

                    <!-- Settings -->
                    @if(in_array(Auth::user()->role_id, [1]))
                    @if(hasPermission('settings') || hasPermission('edit settings') || hasPermission('view country'))
                    <li class="menu-header mt-5">
                        <span class="menu-header-text" data-i18n="Setting">Setting</span>
                    </li>
                    
                    <li class="menu-item @if(Request::is('master-setting*', 'country*', 'countries*', 'mail/settings*')) open @endif">
                        <a href="#" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons ri-settings-3-line"></i>
                            <div data-i18n="General Settings">General Settings</div>
                        </a>

                        <ul class="menu-sub">
                            @if(hasPermission('settings') && hasPermission('edit settings'))
                            <li class="menu-item @if(Request::is('master-setting')) active @endif">
                                <a href="{{ route('master-setting') }}" class="menu-link">
                                    <div data-i18n="Master Setting">Master Setting</div>
                                </a>
                            </li>
                            @endif

                        <!-- List City -->
                        <li class="menu-item @if(Request::is('country')) active @endif">
                            @if(hasPermission('view country'))
                            <a href="{{ route('country.index') }}" class="menu-link">
                                <div data-i18n="List City">List City</div>
                            </a>
                            @endif
                        </li>

                        <!-- Countries -->
                        <li class="menu-item @if(Request::is('countries')) active @endif">
                            <a href="{{ route('countries.index') }}" class="menu-link">
                                <div data-i18n="Countries">Countries</div>
                            </a>
                        </li>

                        <!-- Email Settings -->
                        <li class="menu-item @if(Request::is('mail/settings')) active @endif">
                            <a href="{{ route('mail.settings') }}" class="menu-link">
                                <div data-i18n="Email Settings">Email Settings</div>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
                @endif
                <!-- End Settings -->

                {{-- <!-- Mail -->
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Mail Center">Mail Center</span>
                </li>

                <li class="menu-item @if(Request::is('mail*') && !Request::is('mail/settings*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-mail-send-line"></i>
                        <div data-i18n="Email Templates">Email Templates</div>
                    </a>

                    <ul class="menu-sub">
                        <!-- All Email Templates -->
                        <li class="menu-item @if(Request::is('mail') || Request::is('mail/index')) active @endif">
                            <a href="{{ route('mail.index') }}" class="menu-link">
                                <div data-i18n="All Templates">All Templates</div>
                            </a>
                        </li>
                        
                        <!-- Booking Emails -->
                        <li class="menu-item @if(Request::is('mail/booking-*')) open @endif">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <div data-i18n="Booking Emails">Booking Emails</div>
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item @if(Request::is('mail/booking-confirmation')) active @endif">
                                    <a href="{{ route('mail.booking-confirmation') }}" class="menu-link">
                                        <div data-i18n="Confirmation">Confirmation</div>
                                    </a>
                                </li>
                                
                                <li class="menu-item @if(Request::is('mail/booking-reminder')) active @endif">
                                    <a href="{{ route('mail.booking-reminder') }}" class="menu-link">
                                        <div data-i18n="Reminder">Reminder</div>
                                    </a>
                                </li>
                                
                                <li class="menu-item @if(Request::is('mail/booking-cancellation')) active @endif">
                                    <a href="{{ route('mail.booking-cancellation') }}" class="menu-link">
                                        <div data-i18n="Cancellation">Cancellation</div>
                                    </a>
                                </li>
                                
                                <li class="menu-item @if(Request::is('mail/payment-confirmation')) active @endif">
                                    <a href="{{ route('mail.payment-confirmation') }}" class="menu-link">
                                        <div data-i18n="Payment">Payment</div>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Tour Emails -->
                        <li class="menu-item @if(Request::is('mail/tour-*')) open @endif">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <div data-i18n="Tour Emails">Tour Emails</div>
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item @if(Request::is('mail/tour-itinerary')) active @endif">
                                    <a href="{{ route('mail.tour-itinerary') }}" class="menu-link">
                                        <div data-i18n="Itinerary">Itinerary</div>
                                    </a>
                                </li>
                                
                                <li class="menu-item @if(Request::is('mail/feedback-request')) active @endif">
                                    <a href="{{ route('mail.feedback-request') }}" class="menu-link">
                                        <div data-i18n="Feedback Request">Feedback Request</div>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Staff Emails -->
                        <li class="menu-item @if(Request::is('mail/job-*') || Request::is('mail/agent-*')) open @endif">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <div data-i18n="Staff Emails">Staff Emails</div>
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item @if(Request::is('mail/job-assignment')) active @endif">
                                    <a href="{{ route('mail.job-assignment') }}" class="menu-link">
                                        <div data-i18n="Job Assignment">Job Assignment</div>
                                    </a>
                                </li>
                                <li class="menu-item @if(Request::is('mail/agent-creation')) active @endif">
                                    <a href="{{ route('mail.agent-creation') }}" class="menu-link">
                                        <div data-i18n="Agent Creation">Agent Creation</div>
                                    </a>
                                </li>
                                <li class="menu-item @if(Request::is('mail/agent-update')) active @endif">
                                    <a href="{{ route('mail.agent-update') }}" class="menu-link">
                                        <div data-i18n="Agent Update">Agent Update</div>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <!-- Customer Service -->
                        <li class="menu-item @if(Request::is('mail/welcome-*') || Request::is('mail/enquiry-*')) open @endif">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <div data-i18n="Customer Service">Customer Service</div>
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item @if(Request::is('mail/welcome-email')) active @endif">
                                    <a href="{{ route('mail.welcome-email') }}" class="menu-link">
                                        <div data-i18n="Welcome Email">Welcome Email</div>
                                    </a>
                                </li>
                                
                                <li class="menu-item @if(Request::is('mail/enquiry-response')) active @endif">
                                    <a href="{{ route('mail.enquiry-response') }}" class="menu-link">
                                        <div data-i18n="Enquiry Response">Enquiry Response</div>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li> --}}
                <!-- End Mail -->
                
                <!-- Tranasaction -->
                {{-- @if(hasPermission('transaction'))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Tranasaction">Tranasaction</span>
                </li>
                <li class="menu-item @if(Request::is('transaction')) active @endif"> --}}
                    {{-- @if(hasPermission('transaction')) --}}
                    {{-- <a href="{{ route('transaction') }}" class="menu-link">
                        <i class="menu-icon tf-icons ri-hand-heart-line"></i>
                        <div data-i18n="All Tranasaction">All Tranasaction</div>
                    </a> --}}
                    {{-- @endif --}}
                {{-- </li>
                @endif --}}
                <li class="menu-item" style="height: 102px;"></li>
            </ul>
            <!-- Add this right before the closing </ul> tag at the end of the menu -->
           
        </aside>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Enhanced tooltip functionality
            const tooltips = document.querySelectorAll('.menu-tooltip');
            
            tooltips.forEach((tooltip, index) => {
                const tooltipText = tooltip.querySelector('.tooltip-text');
                const menuText = tooltip.querySelector('.menu-text-with-tooltip');
                
                if (tooltipText && menuText) {
                    // Always show tooltip on hover
                    tooltipText.style.display = 'block';
                    
                    // Add hover events for dynamic positioning
                    tooltip.addEventListener('mouseenter', function(e) {
                        // Get the position of the hovered menu item
                        const rect = this.getBoundingClientRect();
                        
                        // Position tooltip to the right of the menu item
                        tooltipText.style.left = (rect.right + 10) + 'px';
                        tooltipText.style.top = (rect.top + (rect.height / 2) - 20) + 'px';
                        
                        // Show tooltip
                        tooltipText.style.visibility = 'visible';
                        tooltipText.style.opacity = '1';
                        tooltipText.style.transform = 'scale(1)';
                        tooltipText.style.display = 'block';
                    });
                    
                    tooltip.addEventListener('mouseleave', function() {
                        tooltipText.style.visibility = 'hidden';
                        tooltipText.style.opacity = '0';
                        tooltipText.style.transform = 'scale(0.8)';
                    });
                    
                    // Ensure tooltip positioning works on different screen sizes
                    function adjustTooltipPosition() {
                        const rect = tooltip.getBoundingClientRect();
                        const tooltipRect = tooltipText.getBoundingClientRect();
                        
                        // If tooltip would go off screen, position it to the left
                        if (rect.right + tooltipRect.width + 20 > window.innerWidth) {
                            tooltipText.style.left = 'auto';
                            tooltipText.style.right = '100%';
                            tooltipText.style.marginLeft = '0';
                            tooltipText.style.marginRight = '15px';
                            
                            // Update arrow direction
                            const arrow = tooltipText.querySelector('::before');
                            if (arrow) {
                                tooltipText.style.setProperty('--arrow-direction', 'right');
                            }
                        }
                    }
                    
                    // Adjust position on window resize
                    window.addEventListener('resize', adjustTooltipPosition);
                    adjustTooltipPosition();
                }
            });
            
            // Enhanced menu interactions
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    if (!this.classList.contains('active')) {
                        this.style.transform = 'translateX(2px)';
                    }
                });
                
                item.addEventListener('mouseleave', function() {
                    if (!this.classList.contains('active')) {
                        this.style.transform = 'translateX(0)';
                    }
                });
            });
        });
        </script>        
        
