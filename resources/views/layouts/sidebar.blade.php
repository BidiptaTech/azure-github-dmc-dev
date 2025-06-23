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
    
    .ri-landscape-line {
        color: #65a30d !important; /* Lime */
        background: rgba(101, 163, 13, 0.1);
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
                <li class="menu-item @if(Request::is('index')) active @endif">
                    <a href="{{ route('dashboard') }}" class="menu-link">
                        <i class="menu-icon tf-icons ri-dashboard-3-line"></i>
                        <div data-i18n="Dashboard">Dashboard</div>
                    </a>
                </li>

                {{-- @if(Auth::user()->role_id == 11)
                <!-- Special Discount -->
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Special Discount">Special Discount</span>
                </li>

                <li class="menu-item @if(Request::is('discount*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-coupon-line"></i>
                        <div data-i18n="Special Discount">Special Discount</div>
                    </a>

                    <ul class="menu-sub">
                        <!-- Show Discount -->
                        <li class="menu-item @if(Request::is('discount')) active @endif">
                            <a href="{{ route('discount.index') }}" class="menu-link">
                                <div data-i18n="List Discount">List Discount</div>
                            </a>
                        </li>
                        <!-- Create Discount -->
                        <li class="menu-item @if(Request::is('discount/create')) active @endif">
                            <a href="{{ route('discount.create') }}" class="menu-link">
                                <div data-i18n="Create Discount">Create Discount</div>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif --}}

                @if(in_array(auth()->user()->role_id, [1, 2, 11, 33, 12, 37, 38])) {{-- Dmc = 11, Sales Head(dmc) = 33, Sales Manager(dmc) = [12, 37], Asst. Sales Manager(dmc) = 38 --}}
            {{-- @if(hasPermission('view enquiry')) --}}
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Enquiries">Enquiries</span>
                </li>
                
                <li class="menu-item @if(Request::is('enquirylist*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-customer-service-2-line" style="color: #3565bd"></i>
                        <div data-i18n="Enquiries">Enquiries</div>
                    </a>
                    <ul class="menu-sub">
                        <!-- Show Tour -->
                        <li class="menu-item @if(Request::is('enquirylist')) active @endif">
                            <a href="{{ route('enquirylist.index') }}" class="menu-link">
                                <div data-i18n="Enquiries">Enquiries</div>
                            </a>
                        </li>
                    </ul>
                </li>  
            {{-- @endif --}}
            @endif

            <!-- Enquiry -->
            @if(in_array(auth()->user()->role_id, [1,2,3,4,5,6,7,8,9,10,11,12,13, 14, 15, 16, 17,20,21,22,37, 49, 50, 51, 52, 53, 64, 65, 66, 67, 68, 90, 124, 125, 33, 37]))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Negotiation">Negotiation</span>
                </li>

                <li class="menu-item @if(Request::is('enquiry*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-questionnaire-line"></i>
                        <div data-i18n="Negotiation">Negotiation</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item @if(Request::is('enquiry')) active @endif">
                            <a href="{{ route('enquiry') }}" class="menu-link">
                                <div data-i18n="Negotiation List">Negotiation List</div>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            <!-- End Enquiry -->

            <!-- Packages -->
            <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Packages">Packages</span>
                </li>

                <li class="menu-item @if(Request::is('package*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-gift-line"></i>
                        <div data-i18n="Packages">Predefined Packages</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item @if(Request::is('packages') || Request::is('package')) active @endif">
                            <a href="{{ route('packages.index') }}" class="menu-link">
                                <div data-i18n="Package Management">Package Management</div>
                            </a>
                        </li>
                        <li class="menu-item @if(Request::is('packages/create')) active @endif">
                            <a href="{{ route('packages.create') }}" class="menu-link">
                                <div data-i18n="Create Package">Create Package</div>
                            </a>
                        </li>
                    </ul>
                </li>
                <!-- End Packages -->

            <!-- Tour -->
            @if(hasPermission('view tour'))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Tour">Tour</span>
                </li>
 
                <li class="menu-item @if(Request::is('tours*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-route-line"></i>
                        <div data-i18n="Tour">Tour</div>
                    </a>
                    <ul class="menu-sub">
                        <!-- Show Tour -->
                        <li class="menu-item @if(Request::is('tours')) active @endif">
                            <a href="{{ route('tours') }}" class="menu-link">
                                <div data-i18n="Tour List">Tour List</div>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            <!-- End Tour -->

            <!-- Booking List -->
            {{-- @if(hasPermission('view booking')) --}}
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Booking List">Booking List</span>
                </li>
                
                <li class="menu-item @if(Request::is('bookinglist*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-bookmark-3-line"></i>
                        <div data-i18n="Booking List">Booking List</div>
                    </a>
                    <ul class="menu-sub">
                        <!-- Show Tour -->
                        <li class="menu-item @if(Request::is('bookinglist')) active @endif">
                            <a href="{{ route('bookinglist.index') }}" class="menu-link">
                                <div data-i18n="Booking List">Booking List</div>
                            </a>
                        </li>
                    </ul>
                </li>  
            {{-- @endif --}}
            <!-- End Booking List -->

            <!-- Reports -->
            <li class="menu-header mt-5">
                <span class="menu-header-text" data-i18n="View Reports">View Reports</span>
            </li>
            <li class="menu-item @if(Request::is('reports/sales-revenue*') || Request::is('reports/ledger')) open active @endif">
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
                </ul>
            </li>
            <!-- End Reports -->

                <!-- Jobsheets -->
                @if(in_array(Auth::user()->role_id, [1, 2, 7, 11 ,35, 78, 120]))
                    <li class="menu-header small text-uppercase">
                        <span class="menu-header-text">View Jobsheets</span>
                    </li>
                    <li class="menu-item @if(Request::is('jobsheet/view') || Request::is('jobsheet/create-guide-jobsheet') || Request::is('jobsheet/create-driver-jobsheet')) active open @endif">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-file-list-3-line"></i>

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
                @endif

                <!-- End Jobsheets -->

                <!-- JobSheet -->
                @if(in_array(Auth::user()->role_id, [1 ,7,14,97,8,15,106, 10, 11, 26, 50, 98,51,107, 34,65, 99, 66, 108]))
                    <li class="menu-header mt-5">
                        <span class="menu-header-text" data-i18n="Assigned Job">Assigned Job</span>
                    </li>

                    <li class="menu-item @if(Request::is('jobsheet/drivers') || Request::is('jobsheet/guides')) active open @endif">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons ri-task-line"></i>
                            <div data-i18n="Assigned Job">Assigned Job</div>
                        </a>
                        <ul class="menu-sub">
                            @if(in_array(Auth::user()->role_id, [1, 2,7,14,97,8,15,106, 10, 11, 26, 51,107, 34, 66, 108]))
                            <!-- Driver Jobs -->
                            <li class="menu-item @if(Request::is('jobsheet/drivers')) active @endif">
                                <a href="{{ route('jobsheet.drivers') }}" class="menu-link">
                                    <div data-i18n="Driver Jobs">Driver Jobs</div>
                                </a>
                            </li>
                            @endif

                            @if(in_array(Auth::user()->role_id, [1, 2,7,14,97, 10, 11, 26, 50, 98, 34, 65, 99]))
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
                <!-- End JobSheet -->
                
            <!-- Port -->
            @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 2)
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Port">Port</span>
                </li>

                <li class="menu-item @if(Request::is('ports*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-ship-line"></i>
                        <div data-i18n="Port">Port</div>
                    </a>
                    <ul class="menu-sub">
                        <!-- Show Port -->
                        <li class="menu-item @if(Request::is('ports')) active @endif">
                            <a href="{{ route('ports.index') }}" class="menu-link">
                                <div data-i18n="Port List">Port List</div>
                            </a>
                        </li>
                        <!-- Add Port -->
                        <li class="menu-item @if(Request::is('ports/create')) active @endif">
                            <a href="{{ route('ports.create') }}" class="menu-link">
                                <div data-i18n="Add Port">Add Port</div>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
            <!-- End Port -->

            @if(Auth::user()->role_id == 11)
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
            @endif
            <!-- End Zone -->

            <!-- Booking -->
            {{-- @if(auth()->user()->role_id == 21||auth()->user()->role_id == 26 || auth()->user()->role_id == 34 || auth()->user()->role_id == 124 
            || auth()->user()->role_id == 125 || auth()->user()->role_id == 1 || auth()->user()->role_id == 2 || auth()->user()->role_id == 3 ||
            auth()->user()->role_id == 4|| auth()->user()->role_id == 12|| auth()->user()->role_id == 28|| auth()->user()->role_id == 33|| auth()->user()->role_id == 37)
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

                <!-- Facility -->
                @if(hasPermission('view facility') || hasPermission('view category'))
                    <li class="menu-header mt-5">
                        <span class="menu-header-text" data-i18n="Facility">Facility</span>
                    </li>

                <li class="menu-item @if(Request::is('category*', 'facility*')) open active @endif">
                    
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-function-line"></i>
                        <div data-i18n="Facility">Facility</div>
                    </a>
                    <ul class="menu-sub">
                        @if(hasPermission('view category'))
                        <li class="menu-item @if(Request::is('category')) active @endif">
                            <a href="{{ route('category.index') }}" class="menu-link">
                                <div data-i18n="Facility Category">Facility Category</div>
                            </a>
                        </li>
                        @endif
                        @if(hasPermission('view facility'))
                        <li class="menu-item @if(Request::is('facility')) active @endif">
                            <a href="{{ route('facility.index') }}" class="menu-link">
                                <div data-i18n="Facility">Facility</div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                <!-- End Facility -->

                <!-- Hotels -->
                @if(hasPermission('view hotel') || auth()->user()->role_id == 1 || auth()->user()->role_id == 2 || hasPermission('create hotel'))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Hotels">Hotels</span>
                </li>
                <li class="menu-item @if(Request::is('hotels') || Request::is('hotels/create')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-hotel-line"></i>
                        <div data-i18n="Hotels">Hotels</div>
                    </a>
                    <ul class="menu-sub">
                        <!-- <li class="menu-item @if(Request::is('hotel-category')) active @endif">
                            <a href="{{ route('hotel-category.index') }}" class="menu-link">
                                <div data-i18n="Hotel Category">Hotel Category</div>
                            </a>
                        </li> -->
                        {{-- @if(in_array(Auth::user()->role_id, [1, 2, 3, 4]))
                            <li class="menu-item @if(Request::is('hotels/hotel-approval')) active @endif">
                                <a href="{{ route('hotels.approval') }}" class="menu-link">
                                    <div data-i18n="Hotel Approval">Hotel Approval</div>
                                </a>
                            </li>
                        @endif --}}

                        @if(hasPermission('view hotel'))
                        <li class="menu-item @if(Request::is('hotels')) active @endif">
                            <a href="{{ route('hotels.index') }}" class="menu-link">
                                <div data-i18n="List Hotels">List Hotels</div>
                            </a>
                        </li>
                        @endif
                        @if(hasPermission('create hotel'))
                        <li class="menu-item @if(Request::is('hotels/create')) active @endif">
                            <a href=" {{ route('hotels.create') }}" class="menu-link">
                            <div data-i18n="Create Hotels">Create Hotels</div>
                            </a>
                        </li>
                        @endif
                        <!--  Comment out for now create room form here  -->
                        {{-- 
                        @if(hasPermission('view room'))
                        <li class="menu-item @if(Request::is('hotel/rooms')) active @endif">
                            <a href="{{ route('hotels.room') }}" class="menu-link">
                                <div data-i18n="List Room Category">List Room Category</div>
                            </a>
                        </li>
                        @endif
                        @if(hasPermission('create room'))
                        <li class="menu-item @if(Request::is('hotel/create/rooms')) active @endif">
                            <a href="{{ route('hotels.createroom') }}" class="menu-link">
                                <div data-i18n="Create Room Category">Create Room Category</div>
                            </a>
                        </li>
                        @endif
                        --}}
                        {{-- @if(hasPermission('view bed')) --}}
                        {{--
                        <li class="menu-item @if(Request::is('beds/index')) active @endif">
                            <a href="{{ route('beds.index') }}" class="menu-link">
                                <div data-i18n="List Bed Type">Bed Types List</div>
                            </a>
                        </li>
                        --}}
                        {{-- @endif --}}
                        {{-- @if(hasPermission('create bed'))
                        <li class="menu-item @if(Request::is('beds/create')) active @endif">
                            <a href="{{ route('beds.create') }}" class="menu-link">
                                <div data-i18n="Create Bed Type">Create Bed Type</div>
                            </a>
                        </li>
                        @endif --}}
                        <!-- @if(hasPermission('view meal'))
                        <li class="menu-item @if(Request::is('meals')) active @endif">
                            <a href="{{ route('meals.index') }}" class="menu-link">
                                <div data-i18n="List Meals">List Meals</div>
                            </a>
                        </li>
                        @endif -->
                        <!-- @if(hasPermission('create meal'))
                        <li class="menu-item @if(Request::is('meals/create')) active @endif">
                            <a href="{{ route('meals.create') }}" class="menu-link">
                                <div data-i18n="Create Meals">Create Meals</div>
                            </a>
                        </li>
                        @endif -->
                    </ul>
                </li>
                @endif

                <!-- End Hotels -->

                <!-- Attraction -->
                @if(hasPermission('view attraction') || hasPermission('create attraction'))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Attractions & Experiences">Attractions & Experiences</span>
                </li>

                <li class="menu-item @if(Request::is('attraction*') && !Request::is('attractions/attraction-approval*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-landscape-line"></i>
                        <div data-i18n="Attractions & Experiences">Attractions & Experiences</div>
                    </a>
                    <ul class="menu-sub">
                        {{-- @if(in_array(Auth::user()->role_id, [1, 2, 3, 4]))
                            <!--- Pending Attraction -->
                            <li class="menu-item @if(Request::is('attractions/attraction-approval')) active @endif">
                                <a href="{{ route('attractions.approval') }}" class="menu-link">
                                    <div data-i18n="Attraction Approval">Attraction Approval</div>
                                </a>
                            </li>
                        @endif --}}

                        <!-- Show Attraction -->
                        @if(hasPermission('view attraction'))
                        <li class="menu-item @if(Request::is('attraction')) active @endif">
                            <a href="{{ route('attraction.index') }}" class="menu-link">
                                <div data-i18n="List Attractions & Experiences">List Attractions & Experiences</div>
                            </a>
                        </li>
                        @endif
                        <!-- Create Attraction -->
                        @if(hasPermission('create attraction'))
                        <li class="menu-item @if(Request::is('attraction/create')) active @endif">
                            <a href="{{ route('attraction.create') }}" class="menu-link">
                                <div data-i18n="Create Attractions & Experiences">Create Attractions & Experiences</div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif
                <!-- End Attraction -->

                <!-- Restaurant -->
                @if(hasPermission('view restaurant') || hasPermission('create restaurant'))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Restaurant">Restaurant</span>
                </li>

                <li class="menu-item @if(Request::is('restaurant*') && !Request::is('restaurants/restaurant-approval*')) open active @endif">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-restaurant-2-line"></i>
                        <div data-i18n="Restaurant">Restaurant</div>
                    </a>
                    <ul class="menu-sub">
                        {{-- @if(in_array(Auth::user()->role_id, [1, 2, 3, 4]))
                            <!-- Restaurant Approval -->
                            <li class="menu-item @if(Request::is('restaurants/restaurant-approval')) active @endif">
                                <a href="{{ route('restaurants.approval') }}" class="menu-link">
                                    <div data-i18n="Restaurant Approval">Restaurant Approval</div>
                                </a>
                            </li>
                        @endif --}}

                        <!-- List Restaurants -->
                        @if(hasPermission('view restaurant'))
                        <li class="menu-item @if(Request::is('restaurant')) active @endif">
                            <a href="{{ route('restaurant.index') }}" class="menu-link">
                                <div data-i18n="List Restaurants">List Restaurants</div>
                            </a>
                        </li>
                        @endif

                        <!-- Create Restaurant -->
                        @if(hasPermission('create restaurant'))
                        <li class="menu-item @if(Request::is('restaurant/create')) active @endif">
                            <a href="{{ route('restaurant.create') }}" class="menu-link">
                                <div data-i18n="Create Restaurant">Create Restaurant</div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                <!-- End Restaurant -->

                <!-- Guide -->
                @if(hasPermission('view guide') || hasPermission('create guide'))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Guide">Guide</span>
                </li>

                <li class="menu-item @if(Request::is('guide*') && !Request::is('guide/guide-approval*')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-compass-3-line"></i>
                        <div data-i18n="Guide">Guide</div>
                    </a>
                    <ul class="menu-sub">
                        {{-- @if(in_array(Auth::user()->role_id, [1, 2, 3, 4]))
                            <!-- Guide Approval -->
                            <li class="menu-item @if(Request::is('guide/guide-approval')) active @endif">
                                <a href="{{ route('guide.approval') }}" class="menu-link">
                                    <div data-i18n="Guide Approval">Guide Approval</div>
                                </a>
                            </li>
                        @endif --}}
                        <!-- Show Guide -->
                        @if(hasPermission('view guide'))
                        <li class="menu-item @if(Request::is('guide')) active @endif">
                            <a href="{{ route('guide.index') }}" class="menu-link">
                                <div data-i18n="List Guides">List Guides</div>
                            </a>
                        </li>
                        @endif

                        <!-- Create Guide -->
                        @if(hasPermission('create guide'))
                        <li class="menu-item @if(Request::is('guide/create')) active @endif">
                            <a href="{{ route('guide.create') }}" class="menu-link">
                                <div data-i18n="Create Guide">Create Guide</div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                <!-- End Guide -->

                <!-- Driver -->
                @if(hasPermission('view driver') || hasPermission('create driver') || hasPermission('view vehicle') || hasPermission('create vehicle'))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Driver">Driver</span>
                </li>

                <li class="menu-item @if((Request::is('driver*') || Request::is('vehicle*')) && !Request::is('driver/driver-approval*')) open active @endif">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-steering-2-line"></i>
                        <div data-i18n="Driver">Driver</div>
                    </a>
                    <ul class="menu-sub">
                        {{-- @if(in_array(Auth::user()->role_id, [1, 2, 3, 4]))
                            <!-- Driver Approval -->
                            <li class="menu-item @if(Request::is('driver/driver-approval')) active @endif">
                                <a href="{{ route('driver.approval') }}" class="menu-link">
                                    <div data-i18n="Driver Approval">Driver Approval</div>
                                </a>
                            </li>
                        @endif --}}
                        
                        <!-- List Drivers -->
                        @if(hasPermission('view driver'))
                        <li class="menu-item @if(Request::is('driver')) active @endif">
                            <a href="{{ route('driver.index') }}" class="menu-link">
                                <div data-i18n="List Driver">List Drivers</div>
                            </a>
                        </li>
                        @endif

                        <!-- Create Driver -->
                        @if(hasPermission('create driver'))
                        <li class="menu-item @if(Request::is('driver/create')) active @endif">
                            <a href="{{ route('driver.create') }}" class="menu-link">
                                <div data-i18n="Create Driver">Create Driver</div>
                            </a>
                        </li>
                        @endif
                        <!--Vehicles List-->
                        @if(hasPermission('view vehicle'))
                        <li class="menu-item @if(Request::is('vehicle')) active @endif">
                            <a href="{{ route('vehicle.index') }}" class="menu-link">
                                <div>Vehicles List</div>
                            </a>
                        </li>
                        @endif
                        <!--Create Vehicles-->
                        @if(hasPermission('create vehicle'))
                        <li class="menu-item @if(Request::is('vehicle/create')) active @endif">
                            <a href="{{ route('vehicle.create') }}" class="menu-link">
                                <div data-i18n="Create Vehicles">Create Vehicles</div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                <!-- End Driver -->

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
                @if(!(auth()->user()->role_id >= 79 && auth()->user()->role_id <= 123))
                @if(hasPermission('view users') || hasPermission('view roles') || hasPermission('view features') || hasPermission('view agent'))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="User Role Management">User Role Management</span>
                </li>
                <li class="menu-item @if(Request::is('users*', 'agents*', 'roles*', 'features*')) open @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-user-line"></i>
                        <div data-i18n="All Users">All Users</div>
                    </a>
                    <ul class="menu-sub">
                        @if(hasPermission('view users'))
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
                <!-- End Settings -->

                <!-- Mail -->
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
                        <li class="menu-item @if(Request::is('mail/job-*')) open @endif">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <div data-i18n="Staff Emails">Staff Emails</div>
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item @if(Request::is('mail/job-assignment')) active @endif">
                                    <a href="{{ route('mail.job-assignment') }}" class="menu-link">
                                        <div data-i18n="Job Assignment">Job Assignment</div>
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
                </li>
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
        </body>
        </html>