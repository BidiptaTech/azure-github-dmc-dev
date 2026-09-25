<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <div class="layout-wrapper layout-content-navbar  ">
    <div class="layout-container">
        <!-- Menu -->
        <aside id="layout-menu" class="layout-menu layout-menu1 menu-vertical menu bg-menu-theme">
            <div class="app-brand demo">
                @php
                $currentUser = Auth::user();
                $brandUser = $currentUser;

                // Resolve DMC branding for hierarchical roles (sales/finance/product/ops etc.).
                if ($currentUser) {
                    $dmcId = \App\Helpers\CommonHelper::getDmcId($currentUser);
                    if (!empty($dmcId)) {
                        $dmcUser = \App\Models\User::where('userId', $dmcId)->first();
                        if ($dmcUser) {
                            $brandUser = $dmcUser;
                        }
                    }
                }

                $masterLogo = \App\Helpers\CommonHelper::masterSettingsName('logo')['master_value'] ?? '';
                $masterName = \App\Helpers\CommonHelper::masterSettingsName('name')['master_value'] ?? 'Dashboard';

                $brandName = trim((string) ($brandUser->company_name ?? ''));
                if ($brandName === '') {
                    $brandName = $masterName;
                }

                $brandLogo = trim((string) ($brandUser->logo ?? ''));
                if ($brandLogo === '') {
                    $brandLogo = $masterLogo;
                }

                if ($brandLogo !== '' && !preg_match('/^(https?:\/\/|data:image\/)/i', $brandLogo)) {
                    $brandLogo = asset(ltrim($brandLogo, '/'));
                }
                @endphp
                <a href="{{ route('dashboard') }}" class="app-brand-link" title="{{ $brandName }}">
                    <span class="app-brand-logo demo">
                        <span class="sidebar-brand-logo-box" aria-hidden="true">
                            <img src="{{ $brandLogo }}" class="logo-img rounded-logo" alt="">
                        </span>
                    </span>
                    <span class="app-brand-text demo menu-text">
                        <span class="sidebar-brand-name">{{ $brandName }}</span>
                    </span>
                </a>
                <a href="javascript:void(0);" class="layout-menu-toggle sidebar-menu-toggle" aria-label="Toggle navigation">
                    <i class="menu-icon tf-icons ri-menu-fold-line"></i>
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

        

        @if(in_array(auth()->user()->role_id, [33, 37, 38, 128, 129, 130, 134, 135, 136, 138]))
            @php
                $dmcCanLiteForm = \App\Helpers\CommonHelper::dmcCanAccessLiteForm(auth()->user());
                $dmcCanProForm = \App\Helpers\CommonHelper::dmcCanAccessProForm(auth()->user());
            @endphp
            @if($dmcCanProForm)
            <li class="menu-item @if(Request::is('enquiry-form-pro/create')) active @endif" style="position: relative;">
                <a href="#" class="menu-link" id="createSingleTourProBtn" data-enquiry-pro-create-url="{{ route('enquiry-form-pro.create') }}">
                    <i class="menu-icon tf-icons ri-file-list-3-line"></i>
                    <div data-i18n="Create Tour">Create Tour</div>
                    <span class="badge-pro">Pro</span>
                </a>
            </li>
            @endif

            @if($dmcCanLiteForm)
            <!-- Single Tour Package for DMCs -->
            <li class="menu-item @if(Request::is('single-tour-package/create')) active @endif" style="position: relative;">
                <a href="{{ route('single-tour-package.create') }}" class="menu-link">
                    <i class="menu-icon tf-icons ri-route-line"></i>
                    <div data-i18n="Create Tour">Create Tour</div>
                    <span class="badge-lite">Lite</span>
                </a>
            </li>
            @endif

            <li class="menu-item @if(Request::is('packages/booking/create')) active @endif" style="position: relative;">
                <a href="{{ route('packages.booking.create') }}" class="menu-link">
                    <i class="menu-icon tf-icons ri-route-line"></i>
                    <div data-i18n="Prebuilt Packages">Prebuilt Packages</div>
                </a>
            </li>

            {{-- <li class="menu-item @if(Request::is('day-level*')) active @endif">
                <a href="{{ route('day-level.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons ri-calendar-2-line"></i>
                    <div data-i18n="Day Level Packages">Day Level Packages</div>
                </a>
            </li> --}}
        @endif

        <!-- End Tour -->

        @if(in_array(auth()->user()->role_id, [1, 2, 3, 4, 20, 21, 22, 11, 33, 34, 27,54,55,56,57,58,86,95,104,113,122,36, 37, 38, 69,70,71,72,73,87,96,105,114,123,124,125,126,127,128,129,132,133,131,134,136,137,138
        ]))
        <!-- Bookings -->
        {{-- @if(hasPermission('view booking')) --}}
        <li class="menu-header mt-5">
            <span class="menu-header-text" data-i18n="Bookings">Bookings</span>
        </li>
        
        <li class="menu-item @if((Request::is('bookings/*') && !Request::is('bookings/tentative')) || Request::is('package-bookings/*') || Request::is('predefined-package-booking-list') || Request::is('enquirylist') || Request::is('custom-packages/*')) open active @endif">
            <a href="#" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ri-bookmark-3-line"></i>
                <div data-i18n="Bookings">Bookings</div>
            </a>
            <ul class="menu-sub">
                @php
                    $isPackageBookingView = Request::is('package-bookings/*');
                    $sidebarCounts = $isPackageBookingView ? ($packageBookingCounts ?? []) : ($bookingCounts ?? []);
                    $routeFor = function (string $key) use ($isPackageBookingView) {
                        if ($isPackageBookingView) {
                            return match ($key) {
                                'new_enquiries' => route('package-bookings.new-enquiries'),
                                'follow_ups' => route('package-bookings.follow-ups'),
                                'confirmed' => route('package-bookings.confirmed'),
                                'definite' => route('package-bookings.definite'),
                                'actual' => route('package-bookings.actual'),
                                'cancelled' => route('package-bookings.cancelled'),
                                'refunds' => route('package-bookings.refunds'),
                                default => '#',
                            };
                        }
                        return match ($key) {
                            'new_enquiries' => route('bookings.new-enquiries'),
                            'follow_ups' => route('bookings.follow-ups'),
                            'confirmed' => route('bookings.confirmed'),
                            'definite' => route('bookings.definite'),
                            'actual' => route('bookings.actual'),
                            'cancelled' => route('bookings.cancelled'),
                            'refunds' => route('bookings.refunds'),
                            default => '#',
                        };
                    };
                    $activeFor = function (string $toursPath, string $pkgPath) use ($isPackageBookingView) {
                        return $isPackageBookingView ? Request::is($pkgPath) : Request::is($toursPath);
                    };
                @endphp
                @if(in_array(auth()->user()->role_id, [1, 2, 11, 33,  12, 37, 38, 128, 129, 130, 134, 135, 136, 138]))
                    <li class="menu-item @if(Request::is('enquirylist') && !Request::is('enquiries*')) active @endif">
                        <a href="{{ route('enquirylist.index') }}" class="menu-link">
                            <div data-i18n="Quick Enquiry">Quick Enquiry</div>
                        </a>
                    </li>
                
                    <li class="menu-item @if($activeFor('bookings/new-enquiries', 'package-bookings/new-enquiries')) active @endif">
                        <a href="{{ $routeFor('new_enquiries') }}" class="menu-link">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <span data-i18n="Enquiries">Enquiries</span>
                                @if(($sidebarCounts['new_enquiries'] ?? 0) > 0)
                                    <span class="badge bg-danger rounded-pill text-white ms-2">{{ $sidebarCounts['new_enquiries'] }}</span>
                                @endif
                            </div>
                        </a>
                    </li>
                    <!-- Show Booking -->
                    <li class="menu-item @if($activeFor('bookings/follow-ups', 'package-bookings/follow-ups')) active @endif">
                        <a href="{{ $routeFor('follow_ups') }}" class="menu-link">
                            <div class="d-flex justify-content-between align-items-center">
                                <span data-i18n="Follow Ups">Follow Ups</span>
                                @if(($sidebarCounts['follow_ups'] ?? 0) > 0)
                                    <span class="badge bg-danger rounded-pill text-white ms-2">{{ $sidebarCounts['follow_ups'] }}</span>
                                @endif
                            </div>
                        </a>
                    </li>
                @endif
                @if(in_array(auth()->user()->role_id, [1,2,11,12,26,33,34,36,37,38,49,50,51,52,53,64,65,66,67,68,69,70,71,72,73,80,81,87,89,90,96,98,99,105,107,108,114,116,117,123,124,125,126,127,128,129,131,132,133,134,135,136,137,138]))
                    <li class="menu-item @if($activeFor('bookings/confirmed', 'package-bookings/confirmed')) active @endif">
                        <a href="{{ $routeFor('confirmed') }}" class="menu-link">
                            <div class="d-flex justify-content-between align-items-center">
                                <span data-i18n="Confirmed">Confirmed</span>
                                @if(($sidebarCounts['confirmed'] ?? 0) > 0) 
                                    <span class="badge bg-danger rounded-pill text-white ms-2">{{ $sidebarCounts['confirmed'] }}</span>
                                @endif
                            </div>
                        </a>
                    </li>
                
                    <li class="menu-item @if($activeFor('bookings/definite', 'package-bookings/definite')) active @endif">
                        <a href="{{ $routeFor('definite') }}" class="menu-link">
                            <div class="d-flex justify-content-between align-items-center">
                                <span data-i18n="Definite">Definite</span>
                                @if(($sidebarCounts['definite'] ?? 0) > 0)
                                    <span class="badge bg-danger rounded-pill text-white ms-2">{{ $sidebarCounts['definite'] }}</span>
                                @endif
                            </div>
                        </a>
                    </li>
                    
                    <li class="menu-item @if($activeFor('bookings/actual', 'package-bookings/actual')) active @endif">
                        <a href="{{ $routeFor('actual') }}" class="menu-link">
                            <div class="d-flex justify-content-between align-items-center">
                                <span data-i18n="Actual">Actual</span>
                                @if(($sidebarCounts['actual'] ?? 0) > 0)
                                    <span class="badge bg-danger rounded-pill text-white ms-2">{{ $sidebarCounts['actual'] }}</span>
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
                            <li class="menu-item @if($activeFor('bookings/cancelled', 'package-bookings/cancelled')) active @endif">
                                <a href="{{ $routeFor('cancelled') }}" class="menu-link">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span data-i18n="Cancelled">Cancelled</span>
                                        @if(($sidebarCounts['cancelled'] ?? 0) > 0)
                                            <span class="badge bg-danger rounded-pill text-white ms-2">{{ $sidebarCounts['cancelled'] }}</span>
                                        @endif
                                    </div>
                                </a>
                            </li>
                            <li class="menu-item @if($activeFor('bookings/refunds', 'package-bookings/refunds')) active @endif">
                                <a href="{{ $routeFor('refunds') }}" class="menu-link">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span data-i18n="Refunds">Refunds</span>
                                        @if(($sidebarCounts['refunds'] ?? 0) > 0)
                                            <span class="badge bg-danger rounded-pill text-white ms-2">{{ $sidebarCounts['refunds'] }}</span>
                                        @endif
                                    </div>
                                </a>
                            </li>
                        {{-- </ul>
                    </li> --}}
                @endif

                @if(in_array(auth()->user()->role_id, [1,2,11, 33, 34, 36, 37, 38, 26,49,50,51,52,53,80,89,98,107,116,
                64,65,66,67,68,81,90,99,108,117,124,125,128,129,130,131,132,134,135,136,137,138]))
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

            @if(in_array(auth()->user()->role_id, [1,2,3,4,10,11,19,20,44,45,46,47,48,25,59,60,61,62,63,83,101,110,119, 35,74,75,76,77,78,84,93,102,111,120,130, 132, 133, 135, 136, 137, 138,139,140]))

                <!-- Products Section -->
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="All Products">All Products</span>
                </li>

            <li class="menu-item @if(Request::is('packages*') || Request::is('packaged-attractions*') || Request::is('hotels*') || Request::is('attraction*') || Request::is('restaurant*') || Request::is('multiRestaurant*') || Request::is('guide*') || Request::is('vehicle*') || Request::is('driver*')) open active @endif">
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
                    
                    {{-- <li class="menu-item @if(Request::is('packages/create')) active @endif">
                        <a href="{{ route('packages.create') }}" class="menu-link">
                            <div data-i18n="Create Package" class="menu-tooltip">
                                <span class="menu-text-with-tooltip">Create Predefined Package</span>
                                <span class="tooltip-text">Create Predefined Package</span>
                            </div>
                        </a>
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

                <!-- Restaurant -->
                @if(hasPermission('view restaurant') || hasPermission('create restaurant'))
                <li class="menu-item @if((Request::is('restaurant*') && !Request::is('restaurants/restaurant-approval*')) || Request::is('multiRestaurant*')) open @endif">
                    <a href="#" class="menu-link menu-toggle">
                        {{-- <i class="menu-icon tf-icons ri-restaurant-2-line"></i> --}}
                        <div data-i18n="Restaurant">Restaurant</div>
                    </a>
                    <ul class="menu-sub">
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
                        @if(auth()->check() && in_array(auth()->user()->role_id, [1, 11, 20]))
                        <li class="menu-item @if(Request::is('multiRestaurant*')) active @endif">
                            <a href="{{ route('multiResturant.index') }}" class="menu-link" title="Multi Restaurants">
                                {{-- <i class="menu-icon tf-icons ri-restaurant-2-line"></i> --}}
                                <div data-i18n="Multi Restaurants" class="menu-tooltip">
                                    <span class="menu-text-with-tooltip">Multi Restaurants</span>
                                    <span class="tooltip-text">Multi Restaurants</span>
                                </div>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

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

                <li class="menu-item @if(Request::is('packages') && !Request::is('packaged-attractions*')) active @endif">
                    <a href="{{ route('packages.index') }}" class="menu-link" title="Prebuilt Fix Packages">
                        {{-- <i class="menu-icon tf-icons ri-gift-line"></i> --}}
                        <div data-i18n="Prebuilt Fix Packages" class="menu-tooltip">
                            <span class="menu-text-with-tooltip">Prebuilt Fix Packages</span>
                            <span class="tooltip-text">Prebuilt Fix Packages</span>
                        </div>
                    </a>
                </li>
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
            @endif

            @if(in_array(auth()->user()->role_id, [1,2,3,4,11,19,20,44,45,46,47,48,25,59,60,61,62,63,83,101,110,119, 35,74,75,76,77,78,84,93,102,111,120,130, 132, 133, 135, 136, 137, 138,139,140]))

                <!-- Products Section -->
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Product Settings">Product Settings</span>
                </li>

            <li class="menu-item @if(Request::is('category*') || Request::is('facility*') || Request::is('ports*') || Request::is('single-tour-package*') || Request::is('zones*') || Request::is('miscellaneous*') || Request::is('default-values*') || Request::is('services/hotels') || Request::is('services/attractions') || Request::is('services/restaurants') || Request::is('services/miscellaneous'))  open active @endif">
                <a href="#" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ri-stack-line" style="color: #3565bd"></i>
                    <div data-i18n="Product Settings">Product Settings</div>
                </a>
                <ul class="menu-sub">
                    @if(hasPermission('view facility') || hasPermission('view category') || Auth::user()->role_id == 11 || Auth::user()->role_id == 35 || Auth::user()->role_id == 139 || Auth::user()->role_id == 140 || Auth::user()->role_id == 130 || Auth::user()->role_id == 132 || Auth::user()->role_id == 133 || Auth::user()->role_id == 135 || Auth::user()->role_id == 136 || Auth::user()->role_id == 137 || Auth::user()->role_id == 138)
                <li class="menu-item @if(Request::is('category*') || Request::is('facility*') || Request::is('zones*') || Request::is('default-values*') || Request::is('ports*') || Request::is('miscellaneous*')) open @endif">
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
                        
                        <!-- Miscellaneous Items - Admin Only -->
                        <li class="menu-item @if(Request::is('miscellaneous') || Request::is('miscellaneous/*')) active @endif">
                            <a href="{{ route('miscellaneous.index') }}" class="menu-link" title="Miscellaneous Items">
                                <div data-i18n="Miscellaneous Items" class="menu-tooltip">
                                    <span class="menu-text-with-tooltip">Miscellaneous Items</span>
                                    <span class="tooltip-text">Miscellaneous Items</span>
                                </div>
                            </a>
                        </li>
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
                    @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 11 || Auth::user()->role_id == 35 || Auth::user()->role_id == 76 || Auth::user()->role_id == 111 || Auth::user()->role_id == 139 || Auth::user()->role_id == 140 || Auth::user()->role_id == 130 || Auth::user()->role_id == 132 || Auth::user()->role_id == 133 || Auth::user()->role_id == 135 || Auth::user()->role_id == 136 || Auth::user()->role_id == 137 || Auth::user()->role_id == 138)
                    <li class="menu-item @if(Request::is('zones')) active @endif">
                        <a href="{{ route('zones.index') }}" class="menu-link" title="Zones">
                                <div data-i18n="Zones" class="menu-tooltip">
                                    <span class="menu-text-with-tooltip">Zone Mapping</span>
                                    <span class="tooltip-text">Zone Mapping</span>
                                </div>
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()->role_id == 11 || auth()->user()->role_id == 35 || auth()->user()->role_id == 130 || auth()->user()->role_id == 132 || auth()->user()->role_id == 133 || auth()->user()->role_id == 135 || auth()->user()->role_id == 136 || auth()->user()->role_id == 137 || auth()->user()->role_id == 138)
                    <!-- Default Value (same permissions as Zones) -->
                    <li class="menu-item @if(Request::is('default-values') || Request::is('default-values/*')) active @endif">
                        <a href="{{ route('default-values.index') }}" class="menu-link" title="Default Product Mapping">
                                <div data-i18n="Default Product Mapping" class="menu-tooltip">
                                    <span class="menu-text-with-tooltip">Default Product Mapping</span>
                                    <span class="tooltip-text">Default Product Mapping</span>
                                </div>
                        </a>
                    </li>
                        @endif
                </ul>
            </li>

                    <!-- Select Products (Services Management moved under Product Configuration) -->
                    @php
                        $allowedRoles = [11, 35, 74, 77, 78, 84, 93, 120, 130, 132, 133, 135, 136, 137, 138, 139, 140];
                    @endphp
                    @if(in_array(Auth::user()->role_id, $allowedRoles))
                        <li class="menu-item @if(Request::is('services/hotels') || Request::is('services/attractions') || Request::is('services/restaurants') || Request::is('services/miscellaneous')) open @endif">
                            <a href="javascript:void(0);" class="menu-link menu-toggle" title="Select Products">
                                <div data-i18n="Select Products">Select Products</div>
                            </a>
                            <ul class="menu-sub">
                                @php
                                    $allowedRoles = [11, 35, 77, 84, 130, 132, 133, 135, 136, 137, 138, 139, 140];
                                @endphp
                                @if(in_array(Auth::user()->role_id, $allowedRoles))
                                    <li class="menu-item @if(Request::is('services/hotels')) active @endif">
                                        <a href="{{ route('services.hotels') }}" class="menu-link">
                                            <div data-i18n="Hotels & Acco.">Hotels & Acco.</div>
                                        </a>
                                    </li>
                                @endif

                                @php
                                    $allowedRoles = [11, 35,74, 93, 130, 132, 133, 135, 136, 137, 138, 139, 140];
                                @endphp
                                @if(in_array(Auth::user()->role_id, $allowedRoles))
                                <li class="menu-item @if(Request::is('services/attractions')) active @endif">
                                    <a href="{{ route('services.attractions') }}" class="menu-link">
                                        <div data-i18n="Attractions">Attractions</div>
                                    </a>
                                </li>
                                @endif

                                @php
                                    $allowedRoles = [11, 35, 78, 120, 130, 132, 133, 135, 136, 137, 138, 139, 140];
                                @endphp
                                @if(in_array(Auth::user()->role_id, $allowedRoles))
                                <li class="menu-item @if(Request::is('services/restaurants')) active @endif">
                                    <a href="{{ route('services.restaurants') }}" class="menu-link">
                                        <div data-i18n="Restaurants & Dining">Restaurants & Dining</div>
                                    </a>
                                </li>
                                @endif

                                @php
                                    $allowedRoles = [11, 35, 77, 78, 84, 120, 130, 132, 133, 135, 136, 137, 138, 139, 140];
                                @endphp
                                @if(in_array(Auth::user()->role_id, $allowedRoles))
                                <li class="menu-item @if(Request::is('services/miscellaneous')) active @endif">
                                    <a href="{{ route('services.miscellaneous') }}" class="menu-link">
                                        <div data-i18n="Miscellaneous">Miscellaneous</div>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    {{-- </ul>
                </li> --}}
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
                <!-- End Products Section -->
            @endif

            @if(in_array(auth()->user()->role_id, [1,2,3,4,10,11,19,20,33,36,37,38,126,127,128, 129, 130, 134, 135, 136, 138]))
              <!-- Reports -->
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="View Reports">View Reports</span>
                </li>
                <li class="menu-item @if(Request::is('reports/sales-revenue*') || Request::is('reports/ledger') || Request::is('reports/balance-sheet*') || Request::is('booking-list/daily-arrival')) open active @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-bar-chart-box-line"></i>
                        <div data-i18n="Reports & Analytics">Reports & Analytics</div>
                    </a>
                    <ul class="menu-sub">
                        @if(in_array(auth()->user()->role_id, [1,2,3,4,10,11,19,20,33,37,38,128, 129, 130, 134, 135, 136, 138]))
                            <li class="menu-item @if(Request::is('reports/sales-revenue')) active @endif">
                                <a href="{{ route('reports.sales-revenue') }}" class="menu-link">
                                    
                                    <div data-i18n="Sales & Revenue Report">Sales & Revenue Report</div>
                                </a>
                            </li>
                            <li class="menu-item @if(Request::is('reports/ledger')) active @endif">
                                <a href="{{ route('reports.ledger') }}" class="menu-link">
                                    <div data-i18n="Ledger Report">Ledger Report</div>
                                </a>
                            </li>
                        @endif
                        {{-- <li class="menu-item @if(Request::is('reports/balance-sheet')) active @endif">
                            <a href="{{ route('reports.balance-sheet') }}" class="menu-link">
                                <div data-i18n="Balance Sheet & P&L">Balance Sheet & P&L</div>
                            </a>
                        </li> --}}
                        @if(in_array(auth()->user()->role_id, [11, 36, 126,127]))
                            <li class="menu-item @if(Request::is('booking-list/daily-arrival')) active @endif">
                                <a href="{{ route('booking-list.daily-arrival') }}" class="menu-link">
                                    <div data-i18n="Daily Arrival">Daily Arrival</div>
                                </a>
                            </li>
                        @endif

                        <li class="menu-item">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <div data-i18n="Analytical Report (upcoming)">Analytical Report (upcoming)</div>
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item disabled">
                                    <a href="javascript:void(0);" class="menu-link" style="pointer-events:none;opacity:.55;cursor:not-allowed;">
                                        <div data-i18n="Market Analysis">Market Analysis</div>
                                    </a>
                                </li>
                                <li class="menu-item disabled">
                                    <a href="javascript:void(0);" class="menu-link" style="pointer-events:none;opacity:.55;cursor:not-allowed;">
                                        <div data-i18n="TA Revenue Analysis">TA Revenue Analysis</div>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="menu-item">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <div data-i18n="Supplier Payables">Supplier Payables</div>
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item disabled">
                                    <a href="javascript:void(0);" class="menu-link" style="pointer-events:none;opacity:.55;cursor:not-allowed;">
                                        <div data-i18n="Hotels & Accomodations">Hotels & Accomodations</div>
                                    </a>
                                </li>
                                <li class="menu-item disabled">
                                    <a href="javascript:void(0);" class="menu-link" style="pointer-events:none;opacity:.55;cursor:not-allowed;">
                                        <div data-i18n="Attractions">Attractions</div>
                                    </a>
                                </li>
                                <li class="menu-item disabled">
                                    <a href="javascript:void(0);" class="menu-link" style="pointer-events:none;opacity:.55;cursor:not-allowed;">
                                        <div data-i18n="Tour Guide">Tour Guide</div>
                                    </a>
                                </li>
                                <li class="menu-item disabled">
                                    <a href="javascript:void(0);" class="menu-link" style="pointer-events:none;opacity:.55;cursor:not-allowed;">
                                        <div data-i18n="Drivers">Drivers</div>
                                    </a>
                                </li>
                                <li class="menu-item disabled">
                                    <a href="javascript:void(0);" class="menu-link" style="pointer-events:none;opacity:.55;cursor:not-allowed;">
                                        <div data-i18n="Restaurants">Restaurants</div>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        {{-- <li class="menu-item">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <div data-i18n="P & L Report (Coming up)">P & L Report (Coming up)</div>
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item disabled">
                                    <a href="javascript:void(0);" class="menu-link" style="pointer-events:none;opacity:.55;cursor:not-allowed;">
                                        <div data-i18n="Hotels & Accomodations">Hotels & Accomodations</div>
                                    </a>
                                </li>
                                <li class="menu-item disabled">
                                    <a href="javascript:void(0);" class="menu-link" style="pointer-events:none;opacity:.55;cursor:not-allowed;">
                                        <div data-i18n="Attractions">Attractions</div>
                                    </a>
                                </li>
                                <li class="menu-item disabled">
                                    <a href="javascript:void(0);" class="menu-link" style="pointer-events:none;opacity:.55;cursor:not-allowed;">
                                        <div data-i18n="Tour Guide">Tour Guide</div>
                                    </a>
                                </li>
                                <li class="menu-item disabled">
                                    <a href="javascript:void(0);" class="menu-link" style="pointer-events:none;opacity:.55;cursor:not-allowed;">
                                        <div data-i18n="Drivers">Drivers</div>
                                    </a>
                                </li>
                                <li class="menu-item disabled">
                                    <a href="javascript:void(0);" class="menu-link" style="pointer-events:none;opacity:.55;cursor:not-allowed;">
                                        <div data-i18n="Restaurants">Restaurants</div>
                                    </a>
                                </li>
                            </ul>
                        </li> --}}

                        <li class="menu-item disabled">
                            <a href="javascript:void(0);" class="menu-link"
                            style="pointer-events:none;opacity:.55;cursor:not-allowed;">
                                <div data-i18n="P & L Report (Coming up)">P & L Report (Coming up)</div>
                            </a>
                        </li>

                        <li class="menu-item disabled">
                            <a href="javascript:void(0);" class="menu-link" style="pointer-events:none;opacity:.55;cursor:not-allowed;">
                                <div data-i18n="Sales Report (Account Mgmt)">Sales Report (Account Mgmt)</div>
                            </a>
                        </li>
                    </ul>
                </li>
                <!-- End Reports -->
            @endif
            
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
                @if(in_array(Auth::user()->role_id, [1, 2, 7, 11, 26,49,50,51,52,53,80,89,98,107,116,34,64,65,66,67,68,81,90,99,108,117,124,125,128,131,132,134,135,137,138]))
                    <li class="menu-header small text-uppercase">
                        <span class="menu-header-text">Service Delivery</span>
                    </li>
                    <li class="menu-item @if(Request::is('jobsheet/view') || Request::is('jobsheet/create-guide-jobsheet') || Request::is('jobsheet/create-driver-jobsheet') || Request::is('jobsheet/drivers') || Request::is('jobsheet/guides') || Request::is('lost-found*')) open @endif">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-file-list-3-line"></i>

                        <div data-i18n="Service Delivery">Service Delivery</div>
                        </a>
                        
                        <ul class="menu-sub">
                            @if(in_array(auth()->user()->role_id, [34, 128, 131, 132, 134, 135, 137, 138]))
                                <li class="menu-item @if(Request::is('bookings/today')) active @endif">
                                    <a href="{{ route('bookings.today') }}" class="menu-link" title="Trip Logs">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span data-i18n="Trip Logs">Trip Logs</span>
                                        </div>
                                    </a>
                                </li>
                                <li class="menu-item @if(Request::is('lost-found*')) active @endif">
                                    <a href="{{ route('lost-found.index') }}" class="menu-link" title="Lost & Found and Incident Management">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span data-i18n="Lost & Found and Incident Management">Lost & Found and Incident Management</span>
                                        </div>
                                    </a>
                                </li>
                            @endif
                            <li class="menu-item @if(Request::is('jobsheet/create-guide-jobsheet') || Request::is('jobsheet/create-driver-jobsheet') || Request::is('jobsheet/drivers') || Request::is('jobsheet/guides')) open @endif">
                                <a href="javascript:void(0);" class="menu-link menu-toggle">
                                    <div data-i18n="Jobs">Jobs</div>
                                </a>
                                <ul class="menu-sub">
                                    <li class="menu-item @if(Request::is('jobsheet/drivers') || Request::is('jobsheet/guides')) open @endif">
                                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                                            <div data-i18n="Assign Jobs">Assign Jobs</div>
                                        </a>
                                        <ul class="menu-sub">
                                            @if(in_array(Auth::user()->role_id, [1, 2,7,14,97,8,15,106, 10, 11, 26, 51,107, 34, 66, 108, 128, 131, 132, 134, 135, 137, 138]))
                                            <li class="menu-item @if(Request::is('jobsheet/drivers')) active @endif">
                                                <a href="{{ route('jobsheet.drivers') }}" class="menu-link">
                                                    <div data-i18n="Drivers">Drivers</div>
                                                </a>
                                            </li>
                                            @endif

                                            @if(in_array(Auth::user()->role_id, [1, 2,7,14,97, 10, 11, 26, 50, 98, 34, 65, 99, 128, 131, 132, 134, 135, 137, 138]))
                                            <li class="menu-item @if(Request::is('jobsheet/guides')) active @endif">
                                                <a href="{{ route('jobsheet.guides') }}" class="menu-link">
                                                    <div data-i18n="Guides">Guides</div>
                                                </a>
                                            </li>
                                            @endif
                                        </ul>
                                    </li>

                                    <li class="menu-item @if(Request::is('jobsheet/create-driver-jobsheet') || Request::is('jobsheet/create-guide-jobsheet')) open @endif">
                                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                                            <div data-i18n="Job Sheets">Job Sheets</div>
                                        </a>
                                        <ul class="menu-sub">
                                            <li class="menu-item @if(Request::is('jobsheet/create-driver-jobsheet')) active @endif">
                                                <a href="{{ route('jobsheet.create.driver') }}" class="menu-link">
                                                    <div data-i18n="Driver Jobsheet">Driver Jobsheet</div>
                                                </a>
                                            </li>
                                            <li class="menu-item @if(Request::is('jobsheet/create-guide-jobsheet')) active @endif">
                                                <a href="{{ route('jobsheet.create.guide') }}" class="menu-link">
                                                    <div data-i18n="Guide Jobsheet">Guide Jobsheet</div>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
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

            {{-- Services Management moved under Product Settings -> Product Configuration -> Select Products --}}

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

            @php
                $allowedRoles = [1, 2, 3, 4, 11, 19, 20, 33, 37, 38, 128, 129, 130, 134, 135, 136, 138];
            @endphp

            @if(in_array(Auth::user()->role_id, $allowedRoles))
                <!-- Agency Management -->
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Agency Management">Agency Management</span>
                </li>

                <li class="menu-item @if(Request::is('agencies*') || Request::is('agents*') || Request::is('services/agencies')) open active @endif">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-building-line"></i>
                        <div data-i18n="Agency Management">Agency Management</div>
                    </a>
                    <ul class="menu-sub">
                        @if(in_array(Auth::user()->role_id, [1, 20]))
                        <!-- List Agencies -->
                        <li class="menu-item @if(Request::is('agencies')) active @endif">
                            <a href="{{ route('agencies.index') }}" class="menu-link">
                                <div data-i18n="Add Agencies">Add Agencies</div>
                            </a>
                        </li>
                        @endif
                        <!-- DMC Agencies Selection -->
                        @php
                            $allowedRoles = [11, 33, 37, 38, 128, 129, 130, 134, 135, 136, 138];
                        @endphp
                        @if(in_array(Auth::user()->role_id, $allowedRoles))
                        <li class="menu-item @if(Request::is('services/agencies')) active @endif">
                            <a href="{{ route('services.agencies') }}" class="menu-link">
                                <div data-i18n="Select Agencies">Select Agencies</div>
                            </a>
                        </li>
                        @endif

                        @if(hasPermission('view agent') && Auth::user()->role_id != 1)
                        <li class="menu-item @if(Request::is('agents')) active @endif">
                            <a href="{{ route('agents.index') }}" class="menu-link">
                                <div data-i18n="Add TA Contacts">Add TA Contacts</div>
                            </a>
                        </li>
                        @endif

                        <!-- Create Agency -->
                        <!-- <li class="menu-item @if(Request::is('agencies/create')) active @endif">
                            <a href="{{ route('agencies.create') }}" class="menu-link">
                                <div data-i18n="Create Agency">Create Agency</div>
                            </a>
                        </li> -->
                    </ul>
                </li>
                @endif

                <!-- Tax Management for DMC -->
                @php
                    $dmcTaxRoles = 11;
                @endphp

                @if(Auth::user()->role_id == $dmcTaxRoles)
                    <li class="menu-header mt-5">
                        <span class="menu-header-text" data-i18n="Tax Management">Tax Management</span>
                    </li>

                    <li class="menu-item @if(Request::is('tax') || Request::is('tax/*')) open @endif">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons ri-percent-line"></i>
                            <div data-i18n="Tax Management">Tax Management</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item @if(Request::is('tax') || Request::is('tax/*')) open @endif">
                                <a href="javascript:void(0);" class="menu-link menu-toggle">
                                    <div data-i18n="Settings">Settings</div>
                                </a>
                                <ul class="menu-sub">
                                    <!-- Add Tax -->
                                    <li class="menu-item @if((Request::is('tax') || Request::is('tax/*')) && !Request::is('tax/settings')) active @endif">
                                        <a href="{{ route('tax.index') }}" class="menu-link">
                                            <div data-i18n="Add Tax">Add Tax</div>
                                        </a>
                                    </li>

                                    <!-- Tax Settings -->
                                    <li class="menu-item @if(Request::is('tax/settings')) active @endif">
                                        <a href="{{ route('tax.settings') }}" class="menu-link">
                                            <div data-i18n="Tax Settings">Tax Settings</div>
                                        </a>
                                    </li>
                                </ul>
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
                </li>-->


                
                <!-- User Role Management -->
                @if( !( (auth()->user()->role_id >= 79 && auth()->user()->role_id <= 123) || in_array(auth()->user()->role_id, [125, 127, 140]) ) )
                @if(hasPermission('view users') || hasPermission('view roles') || hasPermission('view features') || $auth_user->role_id == 124)
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="User Management">User Management</span>
                </li>
                <li class="menu-item @if(Request::is('users*', 'roles*', 'features*', 'bank-details*')) open @endif">
                    <a href="#" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons ri-user-line"></i>
                        <div data-i18n="All Users">All Users</div>
                    </a>
                    <ul class="menu-sub">
                        @php
                            $excludedRoles = [81, 38, 84, 87, 90, 93, 96, 99, 102, 105, 108, 111, 114, 117, 120, 123, 125, 127];
                        @endphp

                        @if(hasPermission('view users') && !in_array(auth()->user()->role_id, $excludedRoles))
                            <li class="menu-item @if(Request::is('users')) active @endif">
                                <a href="{{ route('users.index') }}" class="menu-link">
                                    <div data-i18n="Users">Users</div>
                                </a>
                            </li>
                        @endif
                       

                        

                        <!-- Registered Agents View -->
                         {{-- @if(auth()->user()->role_id == 20 || auth()->user()->role_id == 19 || auth()->user()->role_id == 1 || auth()->user()->role_id == 2 || auth()->user()->role_id == 3 || auth()->user()->role_id == 4)
                            <li class="menu-item @if(Request::is('registered-agents*')) active @endif">
                                <a href="{{ route('registered-agents.index') }}" class="menu-link">
                                    <div data-i18n="Registered Agents">Registered Agents</div>
                                </a>
                            </li>
                        @endif --}}
                        
                        <!-- Bank Details -->
                        @php
                            $allowedBankDetailsRoles = [1, 11];
                        @endphp
                        @if(in_array(auth()->user()->role_id, $allowedBankDetailsRoles))
                            <li class="menu-item @if(Request::is('bank-details*')) active @endif">
                                <a href="{{ route('bank-details.index') }}" class="menu-link">
                                    <div data-i18n="Bank Details">Bank Details</div>
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

                @php
                    $smartNotificationRoles = [1, 21, 11, 34, 128, 131, 132, 134, 135, 137, 138];
                @endphp
                @if(in_array(auth()->user()->role_id, $smartNotificationRoles))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="Smart App Notification">Smart Notification</span>
                </li>
                <li class="menu-item @if(Request::is('smart-notification') && !Request::is('smart-notification/history*')) active @endif">
                    <a href="{{ route('smart-notification.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons ri-notification-3-line"></i>
                        <div data-i18n="Send Notification">Send Notification</div>
                    </a>
                </li>
                <li class="menu-item @if(Request::is('smart-notification/history*')) active @endif">
                    <a href="{{ route('smart-notification.history') }}" class="menu-link">
                        <i class="menu-icon tf-icons ri-history-line"></i>
                        <div data-i18n="Notification History">Notification History</div>
                    </a>
                </li>
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


                @php
                    $aiConfigurationRoles = [1, 11,33, 37, 38, 128, 129, 130, 134, 135, 136, 138];
                @endphp
                @if(in_array(auth()->user()->role_id, $aiConfigurationRoles))
                <li class="menu-header mt-5">
                    <span class="menu-header-text" data-i18n="AI Management">AI Management</span>
                </li>
                @php
                    $aiKeywordsRoles = [1];
                @endphp
                @if(in_array(auth()->user()->role_id, $aiKeywordsRoles))
                <li class="menu-item @if(Request::is('ai-key-words*')) active @endif">
                    <a href="{{ route('ai-key-words.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons ri-key-line"></i>
                            <div data-i18n="AI Keywords">AI Keywords</div>
                        </a>
                    </li>
                @endif
                @php
                    $dayLevelRoles = [11, 33, 37, 38, 128, 129, 130, 134, 135, 136, 138];
                @endphp
                @if(in_array(auth()->user()->role_id, $dayLevelRoles))
                <li class="menu-item @if(Request::is('day-level*')) active @endif">
                    <a href="{{ route('day-level.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons ri-calendar-2-line"></i>
                        <div data-i18n="AI Definition Tool">AI Definition Tool</div>
                    </a>
                </li>
                @endif
                @endif

                    <!-- Settings -->
                    @php
                        $sidebarRoleId = Auth::user()->role_id;
                        $sidebarIsAdmin = in_array($sidebarRoleId, [1]);
                        // 11 = DMC, 138 = Multi-role (same General Settings as DMC; stores use parent dmc_id)
                        $sidebarIsDmc = in_array($sidebarRoleId, [11, 138]);-
                        $sidebarIsOperational = in_array($sidebarRoleId, [34, 124,125]);
                        $sidebarIsFinance = in_array($sidebarRoleId, [36, 126,127]);
                        $sidebarIsLimitedGeneralSettings = $sidebarIsDmc || $sidebarIsOperational || $sidebarIsFinance;
                    @endphp
                    @if(
                        ($sidebarIsAdmin && (hasPermission('settings') || hasPermission('edit settings') || hasPermission('view country')))
                        || ($sidebarIsLimitedGeneralSettings)
                    )
                    <li class="menu-header mt-5">
                        <span class="menu-header-text" data-i18n="Setting">Setting</span>
                    </li>
                    
                    <li class="menu-item @if(Request::is('master-setting*', 'country*', 'countries*', 'mail*', 'guide-languages*', 'suppliers*', 'cities*', 'app-management*', 'dmc-func-app*', 'itinerary_settings.pdf', 'quotation_settings.pdf')) open @endif">
                        <a href="#" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons ri-settings-3-line"></i>
                            <div data-i18n="General Settings">General Settings</div>
                        </a>

                        <ul class="menu-sub">
                            @if($sidebarIsAdmin)
                            @if(hasPermission('settings') && hasPermission('edit settings'))
                            <li class="menu-item @if(Request::is('master-setting')) active @endif">
                                <a href="{{ route('master-setting') }}" class="menu-link">
                                    <div data-i18n="Master Setting">Master Setting</div>
                                </a>
                            </li>
                            @endif

                            <!-- List City -->
                            @if(hasPermission('view country'))
                            <li class="menu-item @if(Request::is('country')) active @endif">
                                <a href="{{ route('country.index') }}" class="menu-link">
                                    <div data-i18n="City Charges">City Charges</div>
                                </a>
                            </li>
                            @endif

                            <!-- Cities -->
                            <li class="menu-item @if(Request::is('cities*')) active @endif">
                                <a href="{{ route('cities.index') }}" class="menu-link">
                                    <div data-i18n="Cities">Cities</div>
                                </a>
                            </li>
                            @endif

                        <!-- Countries -->
                        @if(hasPermission('view country') || $sidebarIsLimitedGeneralSettings)
                            <li class="menu-item @if(Request::is('countries')) active @endif">
                                <a href="{{ route('countries.index') }}" class="menu-link">
                                    <div data-i18n="Countries">Countries</div>
                                </a>
                            </li>
                        @endif

                        @if($sidebarIsAdmin || $sidebarIsDmc)
                            <!-- Email Settings -->
                            <li class="menu-item @if(Request::is('mail/settings')) active @endif">
                                <a href="{{ route('mail.settings') }}" class="menu-link">
                                    <div data-i18n="Email Settings">Email Settings</div>
                                </a>
                            </li>
                            <!-- Email Templates (same as DMC) -->
                            <li class="menu-item @if(Request::is('mail') || Request::is('mail/index') || Request::is('mail/booking-*') || Request::is('mail/tour-*') || Request::is('mail/welcome-*') || Request::is('mail/enquiry-*') || Request::is('mail/job-*') || Request::is('mail/agent-*') || Request::is('mail/templates*')) active @endif">
                                <a href="{{ route('mail.index') }}" class="menu-link">
                                    <div data-i18n="Email Templates">Email Templates</div>
                                </a>
                            </li>
                        @endif

                        @if($sidebarIsAdmin)
                            <!-- App Settings -->
                            <li class="menu-item @if(Request::is('app-management')) active @endif">
                                <a href="{{ route('app-management.index') }}" class="menu-link">
                                    <div data-i18n="App Management Settings">App Management Settings</div>
                                </a>
                            </li>

                            <li class="menu-item @if(Request::is('dmc-func-app*')) active @endif">
                                <a href="{{ route('dmc-func-app.index') }}" class="menu-link">
                                    <div data-i18n="DMC Func App">DMC Func App</div>
                                </a>
                            </li>

                            <!-- Guide Languages -->
                            <li class="menu-item @if(Request::is('guide-languages*')) active @endif">
                                <a href="{{ route('guide-languages.index') }}" class="menu-link">
                                    <div data-i18n="Guide Languages">Guide Languages</div>
                                </a>
                            </li>

                            @if(hasPermission('settings') || hasPermission('edit settings'))
                            <li class="menu-item @if(Request::is('suppliers*')) active @endif">
                                <a href="{{ route('suppliers.index') }}" class="menu-link">
                                    <div data-i18n="Online Api Master">Online Api Master</div>
                                </a>
                            </li>
                            @endif
                        @endif
                        @if(in_array(auth()->user()->role_id, [11, 33,34,37,38, 77, 84, 128, 131, 132, 134, 135, 137, 138]))
                            <li class="menu-item @if(Request::is('itinerary_settings.pdf')) active @endif">
                                <a href="{{ route('itinerary_settings.pdf') }}" class="menu-link">
                                    <div data-i18n="Itinerary Settings">Itinerary Settings</div>
                                </a>
                            </li>
                            <li class="menu-item @if(Request::is('quotation_settings.pdf')) active @endif">
                                <a href="{{ route('quotation_settings.pdf') }}" class="menu-link">
                                    <div data-i18n="Quotation Settings">Quotation Settings</div>
                                </a>
                            </li>
                        @endif

                        
                    </ul>
                </li>
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

        <!-- Submenu Modal (for ul.menu-sub items) -->
        <div class="modal fade" id="submenuModal" tabindex="-1" aria-label="Submenu options" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body submenu-modal-body" id="submenuModalBody">
                        <!-- Filled dynamically -->
                    </div>
                </div>
            </div>
        </div>
<!-- Modal for Create Single Tour Pro Initial Information -->
<div class="modal fade" id="createTourProModal" tabindex="-1" aria-labelledby="createTourProModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="createTourProForm" method="POST" action="{{ route('enquiry-form-pro.initialize') }}" novalidate>
                @csrf
                @php
                    // Tour cannot start today — earliest selectable start is tomorrow.
                    $ctpMinStartDate = \Carbon\Carbon::now()->addDay()->format('Y-m-d');
                    $ctpMinEndDate = \Carbon\Carbon::now()->addDays(2)->format('Y-m-d');
                    // DMC group_pax threshold (same source as Lite create FIT/GROUP auto-switch)
                    $ctpProDmcId = \App\Helpers\CommonHelper::getDmcId(auth()->user());
                    $ctpProDmcGroupPax = 0;
                    if ($ctpProDmcId) {
                        $ctpProDmcGroupPax = (int) (\App\Models\User::where('userId', $ctpProDmcId)->value('group_pax') ?? 0);
                    }
                @endphp
                <div class="ctp-shell">
                    <aside class="ctp-sidenav" aria-label="Tour setup steps">
                        <button type="button" class="ctp-step is-active" data-ctp-step="basic">
                            <i class="ri-map-2-line"></i>
                            <span>
                                <span class="ctp-step-title">Basic Details</span>
                                <span class="ctp-step-sub">Type, dates, guests</span>
                            </span>
                        </button>
                        <button type="button" class="ctp-step" data-ctp-step="guest">
                            <i class="ri-user-line"></i>
                            <span>
                                <span class="ctp-step-title">Guest Information</span>
                                <span class="ctp-step-sub">Lead guest details</span>
                            </span>
                        </button>
                        <div class="ctp-sidenav-foot" aria-hidden="true">
                            <span class="ctp-sidenav-foot-text">Create memorable<br>experiences</span>
                            <div class="ctp-flight-stage">
                                <span class="ctp-flight-glow"></span>
                                <svg class="ctp-flight-path" viewBox="0 0 210 56" preserveAspectRatio="none" aria-hidden="true">
                                    <path d="M6 46 C 45 42, 70 18, 105 14 S 160 18, 205 6" fill="none" stroke="currentColor" stroke-width="1.25" stroke-dasharray="3 5" stroke-linecap="round"/>
                                </svg>
                                <span class="ctp-wake ctp-wake-a"></span>
                                <span class="ctp-wake ctp-wake-b"></span>
                                <span class="ctp-wake ctp-wake-c"></span>
                                <span class="ctp-wake ctp-wake-d"></span>
                                <span class="ctp-sidenav-plane">
                                    <i class="ri-plane-line"></i>
                                </span>
                            </div>
                        </div>
                    </aside>

                    <div class="ctp-main">
                        <div class="ctp-main-head">
                            <div class="ctp-main-head-left">
                                <span class="ctp-main-icon"><i class="ri-add-box-line"></i></span>
                                <div>
                                    <h6 id="createTourProModalLabel">Create Single Tour Pro</h6>
                                    <p>Plan and configure a customized tour for your guests</p>
                                </div>
                            </div>
                            <button type="button" class="ctp-close" data-bs-dismiss="modal" aria-label="Close"><i class="ri-close-line"></i></button>
                        </div>

                        <div class="modal-body ctp-main-body">
                    <!-- Tour Details -->
                    <section class="ctp-card" id="ctpSectionTour">
                        <div class="ctp-card-head">
                            <span class="ctp-card-head-icon"><i class="ri-calendar-event-line"></i></span>
                            <div>
                                <h6>Tour Details</h6>
                                <p>Select tour type and travel dates</p>
                            </div>
                        </div>
                        <div class="ctp-card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <span class="ctp-label">Type <span class="ctp-req">*</span></span>
                                    <div class="ctp-radios ctp-tour-type-locked" title="Auto from DMC Group Pax vs adults + children">
                                        <label class="ctp-radio" for="tourTypeFIT">
                                            <input type="radio" name="tour_type_ui" id="tourTypeFIT" value="FIT" checked disabled>
                                            <span>FIT</span>
                                        </label>
                                        <label class="ctp-radio" for="tourTypeGroup">
                                            <input type="radio" name="tour_type_ui" id="tourTypeGroup" value="GROUP" disabled>
                                            <span>Group</span>
                                        </label>
                                    </div>
                                    <input type="hidden" name="tour_type" id="ctp_tour_type_value" value="FIT">
                                    <small class="text-muted d-block mt-1" id="ctpTourTypeAutoHint" style="font-size:0.72rem; line-height:1.3;">
                                        FIT/GROUP switches automatically from DMC Group Pax — you cannot change them manually.
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <label class="ctp-label" for="tourStartDate">Start Date <span class="ctp-req">*</span></label>
                                    <div class="ctp-icon-field">
                                        <i class="ri-calendar-line ctp-field-ico"></i>
                                        <input type="date" class="form-control" id="tourStartDate" name="tour_start_date" required min="{{ $ctpMinStartDate }}" value="{{ $ctpMinStartDate }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="ctp-label" for="tourEndDate">End Date <span class="ctp-req">*</span></label>
                                    <div class="ctp-icon-field">
                                        <i class="ri-calendar-line ctp-field-ico"></i>
                                        <input type="date" class="form-control" id="tourEndDate" name="tour_end_date" required min="{{ $ctpMinEndDate }}" value="{{ $ctpMinEndDate }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- FIT: Adult / Child / Infant (unchanged behaviour) -->
                    <div class="row g-2 d-none" id="tourProFitPaxRow">
                        <div class="col-md-2 col-4">
                            <label class="ctp-label">Adult <span class="ctp-req">*</span></label>
                            <input type="number" class="form-control" id="adultCount" min="1" value="1">
                        </div>
                        <div class="col-md-2 col-4">
                            <label class="ctp-label">Child</label>
                            <input type="number" class="form-control" id="childCount" min="0" value="0">
                        </div>
                        <div class="col-md-2 col-4">
                            <label class="ctp-label">Infant</label>
                            <input type="number" class="form-control" id="infantCount" min="0" value="0">
                        </div>
                    </div>

                    <!-- Guests -->
                    <section class="ctp-card" id="tourProGuestsRow">
                        <div class="ctp-card-head">
                            <span class="ctp-card-head-icon"><i class="ri-group-line"></i></span>
                            <div>
                                <h6>Guests <span class="ctp-req">*</span></h6>
                                <p id="tourProGuestsHelper">Select tour guests to set passengers. Set adults (male + female), children, and infants. Infants do not use a pax slot.</p>
                            </div>
                        </div>
                        <div class="ctp-card-body">
                            <div class="ctp-guest-strip">
                                <span id="tourProGuestSummary" class="ctp-guest-strip-text">Click “Select tour guests” to set passengers...</span>
                                <button type="button" class="btn" id="tourProOpenGuestModalBtn">
                                    <i class="ri-group-line me-1"></i>Select Tour Guests
                                </button>
                            </div>
                        </div>
                    </section>

                    <input type="hidden" name="adult_count" id="ctp_hidden_adult_count" value="1">
                    <input type="hidden" name="child_count" id="ctp_hidden_child_count" value="0">
                    <input type="hidden" name="infant_count" id="ctp_hidden_infant_count" value="0">
                    <input type="hidden" name="male" id="ctp_hidden_male" value="1">
                    <input type="hidden" name="female" id="ctp_hidden_female" value="0">
                    <input type="hidden" name="group_size" id="ctp_hidden_group_size" value="0">
                    <input type="hidden" name="foc_size" id="ctp_hidden_foc_size" value="0">
                    <input type="hidden" name="paying_pax" id="ctp_hidden_paying_pax" value="0">
                    <input type="hidden" name="discount" id="ctp_hidden_discount" value="0">
                    <input type="hidden" name="auto_foc" id="ctp_hidden_auto_foc" value="0">
                    <input type="hidden" name="child_ages" id="ctp_hidden_child_ages" value="[]">

                    <!-- Destination + Agency -->
                    <section class="ctp-card" id="ctpSectionDestination">
                        <div class="ctp-card-body">
                            <div class="ctp-dest-title">
                                <span class="ctp-label"><i class="ri-map-pin-line"></i> Destination <span class="ctp-req">*</span></span>
                                <label class="ctp-radio" for="ctpDestSingleCity">
                                    <input type="radio" name="ctp_dest_mode" id="ctpDestSingleCity" value="single" checked>
                                    <span>Single City</span>
                                </label>
                                <label class="ctp-radio" for="ctpDestMultipleCities">
                                    <input type="radio" name="ctp_dest_mode" id="ctpDestMultipleCities" value="multiple">
                                    <span>Multiple Cities</span>
                                </label>
                                <input type="checkbox" class="d-none" id="multipleDestination" name="multiple_destination" value="1" tabindex="-1" aria-hidden="true">
                            </div>

                            <div class="mb-3" id="singleDestinationDiv">
                                <div class="position-relative ctp-icon-field">
                                    <input type="text" class="form-control ctp-has-end-ico" id="destinationSingle" placeholder="Type to search city..." autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
                                    <i class="ri-search-line ctp-field-ico-end"></i>
                                    <div id="destinationSuggestionsSingle" class="list-group position-absolute w-100 ctp-suggestions"></div>
                                </div>
                                <input type="hidden" id="destinationSingleValue" name="destination_single">
                            </div>

                            <div class="mb-3" id="multipleDestinationDiv" style="display: none;">
                                <div class="position-relative ctp-icon-field">
                                    <input type="text" class="form-control ctp-has-end-ico" id="destinationMultiple" placeholder="Type to search and select multiple cities..." autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
                                    <i class="ri-search-line ctp-field-ico-end"></i>
                                    <div id="destinationSuggestions" class="list-group position-absolute w-100 ctp-suggestions"></div>
                                </div>
                                <div id="selectedDestinations" class="mt-2"></div>
                                <input type="hidden" id="destinationsArray" name="destinations">
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="ctp-label" for="ctpAgencySelect"><i class="ri-building-2-line"></i> Agency <span class="ctp-req">*</span></label>
                                    <div class="ctp-icon-field">
                                        <i class="ri-phone-line ctp-field-ico"></i>
                                        <select class="form-select" id="ctpAgencySelect">
                                            <option value="">Type to search agency...</option>
                                        </select>
                                    </div>
                                    <input type="hidden" id="agencyIdValue" name="agency_id">
                                </div>
                                <div class="col-md-6">
                                    <label class="ctp-label" for="ctpAgentSelect"><i class="ri-user-line"></i> Agent <span class="ctp-req">*</span></label>
                                    <div class="ctp-icon-field">
                                        <i class="ri-phone-line ctp-field-ico"></i>
                                        <select class="form-select" id="ctpAgentSelect" disabled>
                                            <option value="">Choose agent...</option>
                                        </select>
                                    </div>
                                    <input type="hidden" id="agentIdValue" name="agent_id">
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Lead Guest Information -->
                    <section class="ctp-card" id="ctpLeadGuestAccordion">
                        <div class="accordion-item border-0 bg-transparent">
                            <button type="button"
                                    class="ctp-lead-guest-toggle"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#ctpLeadGuestBody"
                                    aria-expanded="true"
                                    aria-controls="ctpLeadGuestBody"
                                    id="ctpLeadGuestToggle">
                                <span class="d-flex align-items-center">
                                    <span class="ctp-card-head-icon me-2"><i class="ri-user-line"></i></span>
                                    <span class="ctp-lead-head-copy">
                                        <span class="d-block fw-semibold" style="font-size: 13.5px; line-height: 1.2;">Lead Guest Information</span>
                                        <span class="ctp-card-sub">Enter the primary customer's details</span>
                                    </span>
                                </span>
                                <i class="ri-arrow-up-s-line ctp-lead-chevron"></i>
                            </button>
                            <div id="ctpLeadGuestBody" class="collapse show">
                        <div class="row g-3 mb-3">
                            <div class="col-md-2 col-4">
                                <label class="ctp-label" for="salutation">Salutation <span class="ctp-req">*</span></label>
                                <select class="form-select" id="salutation" name="salutation" required>
                                    <option value="Mr">Mr</option>
                                    <option value="Mrs">Mrs</option>
                                    <option value="Ms">Ms</option>
                                    <option value="Miss">Miss</option>
                                    <option value="Dr">Dr</option>
                                    <option value="Prof">Prof</option>
                                </select>
                            </div>
                            <div class="col-md-5 col-8">
                                <label class="ctp-label" for="customerName">Full Name <span class="ctp-req">*</span></label>
                                <div class="ctp-icon-field">
                                    <i class="ri-user-line ctp-field-ico"></i>
                                    <input type="text" class="form-control" id="customerName" name="customer_name" placeholder="Enter full name" required maxlength="100" autocomplete="name" data-ctp-filter="name">
                                </div>
                                <div class="ctp-lead-error" data-ctp-error-for="customerName"></div>
                            </div>
                            <div class="col-md-5">
                                <label class="ctp-label" for="customerEmail">Email</label>
                                <div class="ctp-icon-field">
                                    <i class="ri-mail-line ctp-field-ico"></i>
                                    <input type="text" class="form-control" id="customerEmail" name="email" placeholder="Enter email" inputmode="email" autocomplete="email" maxlength="255" data-ctp-filter="email">
                                </div>
                                <div class="ctp-lead-error" data-ctp-error-for="customerEmail"></div>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="ctp-label" for="ctpCustomerCountryCode">Country Code</label>
                                @php
                                    $ctpCountriesForCode = \App\Models\Country::query()->orderBy('name')->get(['name', 'country_code']);
                                    $ctpSingapore = $ctpCountriesForCode->firstWhere('name', 'Singapore');
                                    $ctpDefaultCountryCode = $ctpSingapore->country_code ?? ($ctpCountriesForCode->first()->country_code ?? '');
                                @endphp
                                <div class="ctp-icon-field">
                                    <i class="ri-phone-line ctp-field-ico"></i>
                                    <select class="form-select" id="ctpCustomerCountryCode" name="customer_country_code">
                                    <option value="">Select</option>
                                    @foreach($ctpCountriesForCode as $ctpCountry)
                                        @if(!empty($ctpCountry->country_code))
                                            <option value="{{ $ctpCountry->country_code }}" {{ (string) $ctpDefaultCountryCode === (string) $ctpCountry->country_code ? 'selected' : '' }}>{{ $ctpCountry->name }} ({{ $ctpCountry->country_code }})</option>
                                        @endif
                                    @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="ctp-label" for="contactNumber">Phone Number</label>
                                <div class="ctp-icon-field">
                                    <i class="ri-phone-line ctp-field-ico"></i>
                                    <input type="tel" class="form-control" id="contactNumber" name="contact_number" placeholder="Enter phone number" inputmode="numeric" maxlength="15" autocomplete="tel" data-ctp-filter="phone">
                                </div>
                                <div class="ctp-lead-error" data-ctp-error-for="contactNumber"></div>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="ctp-label" for="ctpCustomerAddress1">Address Line 1</label>
                                <div class="ctp-icon-field">
                                    <i class="ri-map-pin-line ctp-field-ico"></i>
                                    <input type="text" class="form-control" id="ctpCustomerAddress1" name="customer_address1" placeholder="Enter address line 1" maxlength="255" data-ctp-filter="address">
                                </div>
                                <div class="ctp-lead-error" data-ctp-error-for="ctpCustomerAddress1"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="ctp-label" for="ctpCustomerAddress2">Address Line 2</label>
                                <div class="ctp-icon-field">
                                    <i class="ri-map-pin-line ctp-field-ico"></i>
                                    <input type="text" class="form-control" id="ctpCustomerAddress2" name="customer_address2" placeholder="Enter address line 2" maxlength="255" data-ctp-filter="address">
                                </div>
                                <div class="ctp-lead-error" data-ctp-error-for="ctpCustomerAddress2"></div>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="ctp-label" for="ctpCustomerState">State</label>
                                <input type="text" class="form-control" id="ctpCustomerState" name="customer_state" placeholder="Enter state" maxlength="100" data-ctp-filter="name">
                                <div class="ctp-lead-error" data-ctp-error-for="ctpCustomerState"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="ctp-label" for="ctpCustomerZip">ZIP Code</label>
                                <input type="text" class="form-control" id="ctpCustomerZip" name="customer_zip" placeholder="Enter 5-digit ZIP" inputmode="numeric" maxlength="5" autocomplete="postal-code" data-ctp-filter="zip">
                                <div class="ctp-lead-error" data-ctp-error-for="ctpCustomerZip"></div>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="ctp-label" for="ctpCustomerPassport">Passport</label>
                                <input type="text" class="form-control" id="ctpCustomerPassport" name="customer_passport" placeholder="Passport number" maxlength="20" autocomplete="off" data-ctp-filter="passport">
                                <div class="ctp-lead-error" data-ctp-error-for="ctpCustomerPassport"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="ctp-label" for="ctpCustomerPassportExpiry">Passport Expiry Date</label>
                                <div class="ctp-icon-field">
                                    <i class="ri-calendar-line ctp-field-ico"></i>
                                    <input type="date" class="form-control" id="ctpCustomerPassportExpiry" name="customer_passport_expiry">
                                </div>
                            </div>
                        </div>
                        <div class="row g-3 mb-0">
                            <div class="col-12">
                                <label class="ctp-label" for="ctpCustomerSpecialRequests">Special Requests</label>
                                <textarea class="form-control" id="ctpCustomerSpecialRequests" name="customer_special_requests" rows="2" placeholder="Enter any special requests or notes" maxlength="2000" data-ctp-filter="notes"></textarea>
                                <div class="ctp-lead-error" data-ctp-error-for="ctpCustomerSpecialRequests"></div>
                            </div>
                        </div>
                            </div>
                        </div>
                    </section>

                        </div>
                        <div class="modal-footer ctp-main-foot">
                            <button type="button" class="btn ctp-btn-cancel" data-bs-dismiss="modal">
                                <i class="ri-close-line me-1"></i>Cancel
                            </button>
                            <button type="submit" class="btn" id="submitTourProBtn" disabled title="Fill all required fields (contact and email are optional).">
                                Continue <i class="ri-arrow-right-line ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Select Tour Guests + FOC (Group — Create Lite parity) -->
<div class="modal fade" id="tourProGuestModal" tabindex="-1" aria-labelledby="tourProGuestModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 10px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 1rem 1.25rem;">
                <h5 class="modal-title fw-bold d-flex align-items-center mb-0 text-white" id="tourProGuestModalLabel" style="font-size: 1.05rem;">
                    <span class="d-inline-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;background:rgba(255,255,255,0.2);border-radius:8px;"><i class="ri-group-line"></i></span>
                    Select Tour Guests
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 1.25rem; background: #ffffff;">
                <div class="p-2 border rounded mb-3" id="ctpProGroupDetailsSection" style="background:#ffffff;border-color:#e9ecef !important;border-radius:10px;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="fw-semibold" style="color:#495057; font-size:0.82rem;"><i class="ri-group-2-line me-1 text-primary"></i>Group Details</div>
                        <span class="badge" style="background:#e7f1ff;color:#0d6efd;border-radius:6px;font-size:0.7rem;">FOC</span>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold mb-1" style="color:#495057; font-size:0.74rem;">Group Size</label>
                            <div class="input-group input-group-sm">
                                <input type="number" min="0" step="1" class="form-control" id="pro_group_size_display" value="1" style="border-radius:8px 0 0 8px;">
                                <span class="input-group-text" style="border-radius:0 8px 8px 0;">pax</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold mb-1" for="pro_foc_size" style="color:#495057; font-size:0.74rem;">FOC Size</label>
                            <div class="input-group input-group-sm">
                                <input type="number" min="0" step="1" class="form-control" id="pro_foc_size" value="0" style="border-radius:8px 0 0 8px;">
                                <span class="input-group-text" style="border-radius:0 8px 8px 0;">pax</span>
                            </div>
                        </div>
                        <div class="col-12" id="pro_includeFOCInPriceRow">
                            <div class="form-check d-flex align-items-center gap-2" style="margin-top:2px;">
                                <input class="form-check-input" type="checkbox" id="pro_include_foc_in_group_price">
                                <label class="form-check-label" for="pro_include_foc_in_group_price" style="color:#495057; font-size:0.74rem;">Treat FOC pax as discount (free)</label>
                                <i class="ri-information-line text-dark fw-bold ctp-foc-info" style="font-size:1.05rem; cursor: help;" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" title="<div style='text-align:left;'><div class='fw-semibold mb-1'>Note:</div><div><span class='text-warning fw-semibold'>☑</span> FOC cost is discounted in paying pax.</div><div><span class='text-warning fw-semibold'>☐</span> FOC cost is included in paying pax.</div></div>"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold mb-1" style="color:#495057; font-size:0.74rem;">Paying Pax</label>
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control" id="pro_paying_pax" value="1" readonly style="background:#f8f9fa;border-radius:8px 0 0 8px;">
                                <span class="input-group-text" style="border-radius:0 8px 8px 0;">pax</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold mb-1" style="color:#495057; font-size:0.74rem;">Total Pax</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="pro_total_pax_display" value="1" readonly style="background:#f8f9fa;border-radius:8px 0 0 8px;">
                                <span class="input-group-text" style="border-radius:0 8px 8px 0;">pax</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card" style="border: 1px solid #e9ecef; border-radius: 8px;">
                            <div class="card-header py-2" style="background:#f8f9fa;border:none;border-bottom:1px solid #e9ecef;">
                                <h6 class="mb-0 fw-semibold" style="color:#495057;font-size:0.875rem;"><i class="ri-user-line me-2 text-primary"></i>Adults</h6>
                            </div>
                            <div class="card-body py-2">
                                <div class="mb-3 text-center">
                                    <label class="form-label fw-semibold d-block small">Adults</label>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <button type="button" class="btn btn-sm border" onclick="window.ctpGuestAdultsDelta(-1)" style="width:36px;height:36px;"><i class="ri-subtract-line"></i></button>
                                        <span class="mx-3 fw-bold fs-5" id="ctpModalAdults">1</span>
                                        <button type="button" class="btn btn-sm border" onclick="window.ctpGuestAdultsDelta(1)" style="width:36px;height:36px;"><i class="ri-add-line"></i></button>
                                    </div>
                                </div>
                                <div class="mb-2 text-center">
                                    <label class="form-label fw-semibold d-block small"><i class="ri-men-line text-primary"></i> Male</label>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <button type="button" class="btn btn-sm border" onclick="window.ctpGuestDelta('male',-1)" style="width:36px;height:36px;"><i class="ri-subtract-line"></i></button>
                                        <span class="mx-3 fw-bold fs-5" id="ctpModalMale">1</span>
                                        <button type="button" class="btn btn-sm border" onclick="window.ctpGuestDelta('male',1)" style="width:36px;height:36px;"><i class="ri-add-line"></i></button>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <label class="form-label fw-semibold d-block small"><i class="ri-women-line text-primary"></i> Female</label>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <button type="button" class="btn btn-sm border" onclick="window.ctpGuestFemaleDelta(-1)" style="width:36px;height:36px;"><i class="ri-subtract-line"></i></button>
                                        <span class="mx-3 fw-bold fs-5" id="ctpModalFemale">0</span>
                                        <button type="button" class="btn btn-sm border" onclick="window.ctpGuestFemaleDelta(1)" style="width:36px;height:36px;"><i class="ri-add-line"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card" style="border: 1px solid #e9ecef; border-radius: 8px;">
                            <div class="card-header py-2" style="background:#f8f9fa;border:none;border-bottom:1px solid #e9ecef;">
                                <h6 class="mb-0 fw-semibold" style="color:#495057;font-size:0.875rem;"><i class="ri-user-smile-line me-2 text-primary"></i>Children &amp; Infants</h6>
                            </div>
                            <div class="card-body py-2">
                                <div class="mb-3 text-center">
                                    <label class="form-label fw-semibold d-block small">Children <small class="text-muted d-block fw-normal">Ages 1–17</small></label>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <button type="button" class="btn btn-sm border" onclick="window.ctpGuestDelta('children',-1)" style="width:36px;height:36px;"><i class="ri-subtract-line"></i></button>
                                        <span class="mx-3 fw-bold fs-5" id="ctpModalChildren">0</span>
                                        <button type="button" class="btn btn-sm border" onclick="window.ctpGuestDelta('children',1)" style="width:36px;height:36px;"><i class="ri-add-line"></i></button>
                                    </div>
                                    <div id="ctpChildAgesSection" class="mt-2 text-start" style="display:none;">
                                        <label class="form-label small fw-semibold">Child ages</label>
                                        <div id="ctpChildAgeDropdowns" class="d-flex flex-column gap-1"></div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <label class="form-label fw-semibold d-block small">Infants <small class="text-muted d-block fw-normal">Under 1 year</small></label>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <button type="button" class="btn btn-sm border" onclick="window.ctpGuestDelta('infants',-1)" style="width:36px;height:36px;"><i class="ri-subtract-line"></i></button>
                                        <span class="mx-3 fw-bold fs-5" id="ctpModalInfants">0</span>
                                        <button type="button" class="btn btn-sm border" onclick="window.ctpGuestDelta('infants',1)" style="width:36px;height:36px;"><i class="ri-add-line"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #e9ecef;background:#f8f9fa;">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm text-white" id="tourProGuestApplyBtn" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"><i class="ri-check-line me-1"></i>Apply Selection</button>
            </div>
        </div>
    </div>
</div>

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

        // Open sidebar submenus in a floating modal instead of expanding inline.
        // Disabled to keep all nested arrows working inside sidebar.
        const enableFloatingSubmenuModal = false;
        const submenuModalEl = document.getElementById('submenuModal');
        const submenuModalTitleEl = document.getElementById('submenuModalLabel'); // Optional: header may be removed
        const submenuModalBodyEl = document.getElementById('submenuModalBody');

        if (enableFloatingSubmenuModal && submenuModalEl && window.bootstrap && window.bootstrap.Modal) {
            const submenuModal = new window.bootstrap.Modal(submenuModalEl, {
                backdrop: false,
                focus: false
            });
            let currentModalTrigger = null;
            let hoverSuppressedTrigger = null;

            const isSubmenuModalOpen = () =>
                submenuModalEl.classList.contains('show') ||
                submenuModalEl.style.display === 'block' ||
                submenuModalEl.getAttribute('aria-hidden') === 'false';
            let modalSubmenuStack = [];

            const closeSubmenuModal = () => {
                if (!isSubmenuModalOpen()) return;
                hoverSuppressedTrigger = currentModalTrigger;
                currentModalTrigger = null;
                modalSubmenuStack = [];
                submenuModal.hide();
            };

            const renderSubmenuInModal = (submenu, title) => {
                if (!submenu || !submenuModalBodyEl) return;

                submenuModalBodyEl.innerHTML = '';
                const list = document.createElement('ul');
                list.className = 'submenu-modal-list';

                if (modalSubmenuStack.length > 1) {
                    const backLi = document.createElement('li');
                    backLi.className = 'menu-item submenu-modal-back';
                    backLi.innerHTML = '<a href="#" class="menu-link submenu-modal-back-link" data-submenu-back="1"><div class="d-flex align-items-center"><i class="ri-arrow-left-line me-2"></i><div data-i18n="Back">Back</div></div></a>';
                    list.appendChild(backLi);
                }

                const directLis = Array.from(submenu.children).filter(el => el && el.matches && el.matches('li.menu-item'));
                (directLis.length ? directLis : Array.from(submenu.querySelectorAll('li.menu-item'))).forEach(li => {
                    const liClone = li.cloneNode(true);

                    // Add a left-side arrow icon before every nested submenu label inside the modal.
                    const modalAnchor = liClone.querySelector('a.menu-link');
                    if (modalAnchor && !modalAnchor.querySelector('.submenu-modal-item-icon')) {
                        // Avoid adding if the original item already has some icon/graphic.
                        if (!modalAnchor.querySelector('i') && !modalAnchor.querySelector('svg')) {
                            const labelNode = modalAnchor.querySelector('[data-i18n]') || modalAnchor.querySelector('div') || modalAnchor.querySelector('span');
                            const iconEl = document.createElement('i');
                            iconEl.className = 'ri-arrow-right-double-fill submenu-modal-item-icon';
                            iconEl.setAttribute('aria-hidden', 'true');
                            if (labelNode && labelNode.parentElement === modalAnchor) {
                                modalAnchor.insertBefore(iconEl, labelNode);
                            } else {
                                modalAnchor.prepend(iconEl);
                            }
                        }
                    }

                    // Keep nested submenu DOM so a second-level modal can be opened from modal items.
                    liClone.querySelectorAll('ul.menu-sub').forEach(nested => {
                        nested.style.display = 'none';
                    });
                    list.appendChild(liClone);
                });

                submenuModalBodyEl.appendChild(list);
                if (submenuModalTitleEl) submenuModalTitleEl.textContent = (title || 'Options').trim();
            };

            const layoutMenuEl = document.getElementById('layout-menu');
            if (layoutMenuEl) {
                // Use event delegation so submenu triggers inside any `menu-header` group open reliably.
                layoutMenuEl.addEventListener('click', function(e) {
                    const trigger = e.target.closest('a.menu-toggle');
                    if (!trigger || !layoutMenuEl.contains(trigger)) return;

                    const parentItem = trigger.closest('.menu-item');
                    const submenu = parentItem ? parentItem.querySelector('ul.menu-sub') : null;
                    if (!submenu) return;

                    // Requirement: only nested submenu should open in modal.
                    // If this menu-toggle is NOT inside another submenu container, keep the default inline behavior.
                    // (Example: "Restaurant" should stay inline; "Enquiries" inside "Bookings" should open modal.)
                    const isNestedSubmenuTrigger = !!trigger.closest('ul.menu-sub');
                    if (!isNestedSubmenuTrigger) return;

                    // Prevent theme inline submenu toggle; we render it inside modal instead.
                    e.preventDefault();
                    e.stopPropagation();
                    // Some sidebar themes also bind their own click handlers on the same element.
                    // This prevents inline submenu expansion from happening right after our modal opens.
                    e.stopImmediatePropagation();

                    // Modal title from the trigger's i18n label/text.
                    const i18nNode = trigger.querySelector('[data-i18n]');
                    const title = i18nNode ? i18nNode.textContent.trim() : (trigger.textContent || 'Options');
                    if (submenuModalTitleEl) submenuModalTitleEl.textContent = title.trim() || 'Options';

                    currentModalTrigger = trigger;
                    // Clicking a nested submenu should re-enable hover reopening for other triggers.
                    hoverSuppressedTrigger = null;
                    modalSubmenuStack = [];

                    modalSubmenuStack.push({
                        submenu,
                        title
                    });
                    renderSubmenuInModal(submenu, title);

                    // Close any expanded inline state if theme added it already.
                    parentItem.classList.remove('open');
                    // Hide nested inline submenu levels under the clicked trigger.
                    // This ensures only the modal shows nested options.
                    parentItem.querySelectorAll('ul.menu-sub').forEach(u => {
                        u.style.display = 'none';
                    });

                    // Position modal next to the clicked menu item (responsive fallback for small screens).
                    const rect = trigger.getBoundingClientRect();
                    const dialog = submenuModalEl.querySelector('.modal-dialog');
                    if (window.innerWidth < 768) {
                        dialog.style.left = '40%';
                        dialog.style.top = '50%';
                        dialog.style.transform = 'translate(-50%, -50%)';
                        dialog.style.width = '90vw';
                        dialog.style.maxWidth = '540px';
                    } else {
                        dialog.style.transform = 'none';
                        dialog.style.width = '280px';
                        dialog.style.maxWidth = '280px';
                        dialog.style.left = `${rect.right + 14}px`;
                        dialog.style.top = `${Math.max(10, rect.top)}px`;
                    }

                    submenuModal.show();

                    // If modal overflows the viewport horizontally, clamp it.
                    requestAnimationFrame(() => {
                        const dRect = dialog.getBoundingClientRect();
                        if (window.innerWidth >= 768) {
                            if (dRect.right > window.innerWidth - 8) {
                                const newLeft = Math.max(8, window.innerWidth - dRect.width - 8);
                                dialog.style.left = `${newLeft}px`;
                            }
                            if (dRect.bottom > window.innerHeight - 8) {
                                const newTop = Math.max(8, window.innerHeight - dRect.height - 8);
                                dialog.style.top = `${newTop}px`;
                            }
                        }
                    });
                }, true);
            }

            // Hide modal after user clicks a link inside it (navigation will happen anyway).
            submenuModalBodyEl.addEventListener('click', function(e) {
                const backLink = e.target.closest('[data-submenu-back="1"]');
                if (backLink) {
                    e.preventDefault();
                    if (modalSubmenuStack.length > 1) {
                        modalSubmenuStack.pop();
                        const prev = modalSubmenuStack[modalSubmenuStack.length - 1];
                        renderSubmenuInModal(prev.submenu, prev.title);
                    }
                    return;
                }

                const toggleLink = e.target.closest('a.menu-toggle');
                if (toggleLink) {
                    const li = toggleLink.closest('li.menu-item');
                    const nested = li ? li.querySelector(':scope > ul.menu-sub') : null;
                    if (nested) {
                        e.preventDefault();
                        const i18nNode = toggleLink.querySelector('[data-i18n]');
                        const nestedTitle = i18nNode ? i18nNode.textContent.trim() : (toggleLink.textContent || 'Options');
                        modalSubmenuStack.push({
                            submenu: nested,
                            title: nestedTitle
                        });
                        renderSubmenuInModal(nested, nestedTitle);
                        return;
                    }
                }

                const link = e.target.closest('a');
                if (link) {
                    // Close modal after selection; keep it closed even if the cursor stays hovered on the trigger.
                    closeSubmenuModal();
                }
            });

            // Reliable close when user clicks the modal wrapper (outside `.modal-content`).
            submenuModalEl.addEventListener('click', function(e) {
                if (e.target === submenuModalEl) {
                    closeSubmenuModal();
                }
            }, true);

            // Close modal when clicking/tapping outside (reliable: use pointerdown).
            document.addEventListener('pointerdown', function(e) {
                if (!isSubmenuModalOpen()) return;

                // IMPORTANT: `.modal` wrapper covers the whole screen, so we must only treat clicks
                // inside `.modal-content` as "inside". Everything else should close the modal.
                const modalContent = submenuModalEl.querySelector('.modal-content');
                if (modalContent && modalContent.contains(e.target)) return;

                closeSubmenuModal();
            }, true);

        }
        
        // Enhanced menu interactions
        // Scope hover animation to the real sidebar only; modal items are cloned `.menu-item`s too.
        const menuItems = document.querySelectorAll('#layout-menu .menu-item');
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Open modal when Create Single Tour Pro is clicked
        const createTourBtn = document.getElementById('createSingleTourProBtn');
        if (createTourBtn) {
            createTourBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const modalEl = document.getElementById('createTourProModal');
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
                loadDestinations();
                if (modalEl) {
                    modalEl.addEventListener('shown.bs.modal', function onCtpShown() {
                        modalEl.removeEventListener('shown.bs.modal', onCtpShown);
                        ctpBindAgencyAgentHandlers();
                        loadAgenciesForDmc();
                        ctpInitTourProSelect2();
                        if (typeof window.ctpApplyTourStartFloor === 'function') window.ctpApplyTourStartFloor();
                        if (typeof ctpUpdateSubmitButtonState === 'function') ctpUpdateSubmitButtonState();
                        if (typeof ctpBindLeadGuestAccordion === 'function') ctpBindLeadGuestAccordion();
                    });
                }
            });
        }

        // Create Tour Pro — FIT pax vs Group (FOC + guest modal, Create Lite parity)
        // Auto FIT/GROUP from DMC group_pax (same rule as Lite: adults + children >= threshold → GROUP)
        window.CTP_DMC_GROUP_PAX = {{ (int) ($ctpProDmcGroupPax ?? 0) }};
        window.tourProGuestConfigured = false;
        let selectedDestinations = [];
        let allDestinations = [];
        let availableAgencies = [];
        let availableAgents = [];

        function ctpSafeInt(v) {
            const n = parseInt(String(v ?? '').trim(), 10);
            return Number.isFinite(n) ? n : 0;
        }
        function ctpIsGroup() {
            const hidden = document.getElementById('ctp_tour_type_value');
            if (hidden && String(hidden.value || '').toUpperCase() === 'GROUP') return true;
            const r = document.getElementById('tourTypeGroup');
            return !!(r && r.checked);
        }

        /** Adults + children for DMC group_pax threshold (infants excluded — Lite parity). */
        function ctpReadPaxForThreshold() {
            const guestModal = document.getElementById('tourProGuestModal');
            const modalOpen = !!(guestModal && guestModal.classList.contains('show'));
            const maleEl = document.getElementById('ctpModalMale');
            const femaleEl = document.getElementById('ctpModalFemale');
            const childEl = document.getElementById('ctpModalChildren');
            const infEl = document.getElementById('ctpModalInfants');
            if (modalOpen && maleEl && femaleEl && childEl) {
                return {
                    adults: Math.max(0, ctpSafeInt(maleEl.textContent) + ctpSafeInt(femaleEl.textContent)),
                    children: Math.max(0, ctpSafeInt(childEl.textContent)),
                    infants: Math.max(0, ctpSafeInt(infEl ? infEl.textContent : 0))
                };
            }
            return {
                adults: Math.max(0, ctpSafeInt(document.getElementById('ctp_hidden_adult_count')?.value)),
                children: Math.max(0, ctpSafeInt(document.getElementById('ctp_hidden_child_count')?.value)),
                infants: Math.max(0, ctpSafeInt(document.getElementById('ctp_hidden_infant_count')?.value))
            };
        }

        function ctpUpdateTourTypeHint(threshold, total, next) {
            const hint = document.getElementById('ctpTourTypeAutoHint');
            if (!hint) return;
            if (threshold > 0) {
                hint.textContent = 'Auto from DMC Group Pax (' + threshold + '). Current paying pax: ' + total
                    + ' (adults + children). '
                    + (next === 'GROUP' ? 'Switched to GROUP.' : 'FIT until pax reaches ' + threshold + '.');
            } else {
                hint.textContent = 'DMC Group Pax is not set — defaulting to FIT. FIT/GROUP cannot be changed manually.';
            }
        }

        /**
         * Lock FIT/GROUP radios and set type (Lite parity).
         * Does not reset guestConfigured — safe to call while editing guests.
         */
        function ctpSetTourType(type, opts) {
            const options = opts || {};
            const next = String(type || 'FIT').toUpperCase() === 'GROUP' ? 'GROUP' : 'FIT';
            const prev = ctpIsGroup() ? 'GROUP' : 'FIT';
            const fit = document.getElementById('tourTypeFIT');
            const group = document.getElementById('tourTypeGroup');
            const hidden = document.getElementById('ctp_tour_type_value');
            if (fit) {
                fit.checked = next === 'FIT';
                fit.disabled = true;
            }
            if (group) {
                group.checked = next === 'GROUP';
                group.disabled = true;
            }
            if (hidden) hidden.value = next;

            const threshold = Math.max(0, ctpSafeInt(window.CTP_DMC_GROUP_PAX));
            const pax = ctpReadPaxForThreshold();
            const total = pax.adults + pax.children;
            ctpUpdateTourTypeHint(threshold, total, next);

            const helper = document.getElementById('tourProGuestsHelper');
            if (helper) {
                helper.textContent = next === 'GROUP'
                    ? 'Select tour guests to set passengers. Group size = paying pax; FOC adds to total pax. Adults + children must match total pax (male + female = adults). Infants are extra.'
                    : 'Select tour guests to set passengers. Set adults (male + female), children, and infants. Infants do not use a pax slot.';
            }

            const focSec = document.getElementById('ctpProGroupDetailsSection');
            if (focSec) focSec.classList.toggle('d-none', next !== 'GROUP');

            // When auto-switching to GROUP, seed paying group size from current adults+children
            if (next === 'GROUP' && (prev !== 'GROUP' || options.forceSeedGroup)) {
                const gsd = document.getElementById('pro_group_size_display');
                const focEl = document.getElementById('pro_foc_size');
                const paying = Math.max(1, total > 0 ? total : 1);
                if (gsd) gsd.value = String(paying);
                if (focEl && (prev !== 'GROUP' || options.forceSeedGroup)) {
                    // Keep existing FOC when already GROUP; reset only on first switch
                    if (prev !== 'GROUP') focEl.value = '0';
                }
                const guestModal = document.getElementById('tourProGuestModal');
                if (guestModal && guestModal.classList.contains('show') && typeof ctpSyncModalGuestsToCap === 'function') {
                    ctpSyncModalGuestsToCap();
                }
            }

            return next;
        }

        function ctpSyncTourTypeFromPax(opts) {
            const threshold = Math.max(0, ctpSafeInt(window.CTP_DMC_GROUP_PAX));
            const pax = ctpReadPaxForThreshold();
            const total = pax.adults + pax.children;
            const next = (threshold > 0 && total >= threshold) ? 'GROUP' : 'FIT';
            return ctpSetTourType(next, opts);
        }
        window.ctpSyncTourTypeFromPax = ctpSyncTourTypeFromPax;
        window.ctpSetTourType = ctpSetTourType;

        /** Enable Continue when required fields are set. Contact number and email are optional. */
        function ctpExpandLeadGuestAccordion() {
            const body = document.getElementById('ctpLeadGuestBody');
            if (!body) return;
            if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                bootstrap.Collapse.getOrCreateInstance(body, { toggle: false }).show();
            } else {
                body.classList.add('show');
                document.getElementById('ctpLeadGuestToggle')?.classList.remove('collapsed');
            }
        }
        function ctpBindLeadGuestAccordion() {
            const body = document.getElementById('ctpLeadGuestBody');
            const modalBody = document.querySelector('#createTourProModal .modal-body');
            if (!body || body.dataset.ctpBound === '1') return;
            body.dataset.ctpBound = '1';
            body.addEventListener('shown.bs.collapse', function () {
                if (!modalBody) return;
                requestAnimationFrame(function () {
                    const toggle = document.getElementById('ctpLeadGuestToggle');
                    const top = (toggle ? toggle.offsetTop : body.offsetTop) - 6;
                    modalBody.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
                });
            });
        }
        function ctpUpdateSubmitButtonState() {
            const btn = document.getElementById('submitTourProBtn');
            if (!btn) return;
            let ok = true;

            const start = (document.getElementById('tourStartDate')?.value || '').trim();
            const end = (document.getElementById('tourEndDate')?.value || '').trim();
            if (!start || !end) ok = false;

            const multipleDestChecked = document.getElementById('multipleDestination')?.checked === true;
            if (multipleDestChecked) {
                if (!selectedDestinations.length) ok = false;
            } else {
                const singleDest = (document.getElementById('destinationSingleValue')?.value || '').trim();
                if (!singleDest) ok = false;
            }

            if (!(document.getElementById('agencyIdValue')?.value || '').trim()) ok = false;
            if (!(document.getElementById('agentIdValue')?.value || '').trim()) ok = false;

            const custName = (document.getElementById('customerName')?.value || '').trim();
            if (!custName || !/^[\p{L}]+(?:[\p{L}\s\-]*[\p{L}])?$/u.test(custName)) ok = false;
            if (typeof ctpLeadGuestHasInvalidFields === 'function' && ctpLeadGuestHasInvalidFields()) ok = false;

            if (!window.tourProGuestConfigured) ok = false;

            btn.disabled = !ok;
            btn.style.opacity = ok ? '1' : '0.55';
            btn.style.cursor = ok ? 'pointer' : 'not-allowed';
            btn.title = ok
                ? 'Continue to enquiry form'
                : 'Fill all required fields (destination, agency, agent, customer name, guests). Contact and email are optional.';
        }
        window.ctpUpdateSubmitButtonState = ctpUpdateSubmitButtonState;

        function loadDestinations() {
            fetch('{{ route("enquiry-form-pro.get-destinations") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.destinations.length > 0) {
                        allDestinations = data.destinations;
                    }
                })
                .catch(error => {
                    console.error('Error loading destinations:', error);
                });
        }

        function ctpSetHidden(id, val) {
            const el = document.getElementById(id);
            if (el) el.value = String(val);
        }
        function ctpSyncFitHiddenPax() {
            if (ctpIsGroup()) return;
            const elA = document.getElementById('adultCount');
            const elC = document.getElementById('childCount');
            const elI = document.getElementById('infantCount');
            const a = Math.max(0, ctpSafeInt(elA ? elA.value : 0));
            const c = Math.max(0, ctpSafeInt(elC ? elC.value : 0));
            const i = Math.max(0, ctpSafeInt(elI ? elI.value : 0));
            ctpSetHidden('ctp_hidden_adult_count', a);
            ctpSetHidden('ctp_hidden_child_count', c);
            ctpSetHidden('ctp_hidden_infant_count', i);
            ctpSetHidden('ctp_hidden_male', 0);
            ctpSetHidden('ctp_hidden_female', 0);
            ctpSetHidden('ctp_hidden_group_size', 0);
            ctpSetHidden('ctp_hidden_foc_size', 0);
            ctpSetHidden('ctp_hidden_paying_pax', 0);
            ctpSetHidden('ctp_hidden_discount', 0);
            ctpSetHidden('ctp_hidden_auto_foc', 0);
            ctpSetHidden('ctp_hidden_child_ages', '[]');
        }
        function ctpInitDefaultGroupHidden() {
            ctpSetHidden('ctp_hidden_group_size', 1);
            ctpSetHidden('ctp_hidden_foc_size', 0);
            ctpSetHidden('ctp_hidden_paying_pax', 1);
            ctpSetHidden('ctp_hidden_discount', 0);
            ctpSetHidden('ctp_hidden_auto_foc', 0);
            ctpSetHidden('ctp_hidden_adult_count', 1);
            ctpSetHidden('ctp_hidden_child_count', 0);
            ctpSetHidden('ctp_hidden_infant_count', 0);
            ctpSetHidden('ctp_hidden_male', 1);
            ctpSetHidden('ctp_hidden_female', 0);
            ctpSetHidden('ctp_hidden_child_ages', '[]');
        }
        function ctpInitDefaultFitHidden() {
            ctpSetHidden('ctp_hidden_group_size', 0);
            ctpSetHidden('ctp_hidden_foc_size', 0);
            ctpSetHidden('ctp_hidden_paying_pax', 0);
            ctpSetHidden('ctp_hidden_discount', 0);
            ctpSetHidden('ctp_hidden_auto_foc', 0);
            ctpSetHidden('ctp_hidden_adult_count', 1);
            ctpSetHidden('ctp_hidden_child_count', 0);
            ctpSetHidden('ctp_hidden_infant_count', 0);
            ctpSetHidden('ctp_hidden_male', 1);
            ctpSetHidden('ctp_hidden_female', 0);
            ctpSetHidden('ctp_hidden_child_ages', '[]');
        }
        function ctpRefreshTourTypeUI() {
            // Auto FIT/GROUP from current pax; reset guest selection defaults for the mode
            const next = ctpSyncTourTypeFromPax();
            window.tourProGuestConfigured = false;
            if (next === 'GROUP') ctpInitDefaultGroupHidden();
            else ctpInitDefaultFitHidden();
            // After defaults, re-apply type so hint/FOC section stay in sync
            ctpSetTourType(next, { forceSeedGroup: next === 'GROUP' });
            ctpRenderGuestSummary();
            ctpUpdateSubmitButtonState();
        }
        // Lock FIT/GROUP — no manual toggle (Lite parity)
        document.querySelectorAll('.ctp-tour-type-locked, .ctp-tour-type-locked label, .ctp-tour-type-locked input').forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
            });
        });
        // Initial sync (defaults to FIT until guests are chosen)
        ctpSyncTourTypeFromPax();
        const ctpLeadGuestRules = {
            name: {
                strip: /[^\p{L}\s\-]/gu,
                valid: /^[\p{L}\s\-]*$/u,
                error: 'Only letters are allowed. Quotes and special characters are not permitted.',
                blockKeys: ["'", '"', '`']
            },
            phone: {
                strip: /[^0-9]/g,
                valid: /^[0-9]*$/,
                error: 'Phone number accepts digits 0-9 only. Letters such as e and special characters are not allowed.',
                blockKeys: ['e', 'E', '+', '-', '.', ',', ' ']
            },
            zip: {
                strip: /[^0-9]/g,
                valid: /^[0-9]{0,5}$/,
                complete: /^[0-9]{5}$/,
                error: 'ZIP code accepts digits 0-9 only. Letters such as e and special characters are not allowed.',
                incompleteError: 'ZIP code must be exactly 5 digits.',
                blockKeys: ['e', 'E', '+', '-', '.', ',', ' ']
            },
            passport: {
                strip: /[^A-Za-z0-9]/g,
                valid: /^[A-Za-z0-9]*$/,
                error: 'Passport number accepts letters and numbers only. Special characters are not allowed.'
            },
            notes: {
                strip: /[<>{}[\]\\`$^*=~|]/g,
                valid: /^[^<>{}[\]\\`$^*=~|]*$/,
                error: 'Special requests cannot contain unsafe characters such as < > { } [ ] \\ ` $ * = ~ |'
            },
            email: {
                strip: /[<>"'\\\s]/g,
                valid: /^[A-Za-z0-9._%+\-@]*$/,
                format: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                error: 'Enter a valid email address without spaces or special characters, or leave it blank.'
            },
            address: {
                strip: /[^\p{L}0-9\s.,#'\-\/]/gu,
                valid: /^[\p{L}0-9\s.,#'\-\/]*$/u,
                error: 'Address can only contain letters, numbers and common punctuation ( , . # - / ).'
            }
        };
        function ctpLeadGuestShowError(id, msg) {
            const field = document.getElementById(id);
            const err = document.querySelector('#ctpLeadGuestBody [data-ctp-error-for="' + id + '"]');
            if (field) field.classList.toggle('is-invalid', !!msg);
            if (err) {
                err.textContent = msg || '';
                err.classList.toggle('is-visible', !!msg);
            }
        }
        function ctpLeadGuestSanitize(el, showError) {
            const rule = ctpLeadGuestRules[el.getAttribute('data-ctp-filter')];
            if (!rule) return true;
            if (el.dataset.ctpComposing === '1') return true;
            const original = el.value;
            let next = original.replace(rule.strip, '');
            if (el.maxLength > 0) next = next.slice(0, el.maxLength);
            if (next !== original) {
                const start = el.selectionStart;
                const end = el.selectionEnd;
                const removed = original.length - next.length;
                el.value = next;
                try {
                    if (typeof start === 'number' && typeof end === 'number') {
                        el.setSelectionRange(Math.max(0, start - removed), Math.max(0, end - removed));
                    }
                } catch (e) {}
                if (showError !== false) ctpLeadGuestShowError(el.id, rule.error);
            } else if (rule.format && next.trim() && !rule.format.test(next.trim())) {
                if (showError !== false) ctpLeadGuestShowError(el.id, rule.error);
            } else if (!rule.valid.test(next)) {
                if (showError !== false) ctpLeadGuestShowError(el.id, rule.error);
            } else if (showError !== false) {
                ctpLeadGuestShowError(el.id, '');
            }
            if (rule.complete && el.value.trim() && !rule.complete.test(el.value.trim())) {
                return false;
            }
            return rule.valid.test(el.value) && (!rule.format || !el.value.trim() || rule.format.test(el.value.trim()));
        }
        function ctpLeadGuestHasInvalidFields() {
            const fields = document.querySelectorAll('#ctpLeadGuestBody [data-ctp-filter]');
            for (let i = 0; i < fields.length; i++) {
                const el = fields[i];
                const rule = ctpLeadGuestRules[el.getAttribute('data-ctp-filter')];
                if (!rule) continue;
                const val = el.value || '';
                if (!rule.valid.test(val)) return true;
                if (rule.format && val.trim() && !rule.format.test(val.trim())) return true;
                if (rule.complete && val.trim() && !rule.complete.test(val.trim())) return true;
            }
            return false;
        }
        function ctpValidateLeadGuestFields(showErrors) {
            let ok = true;
            document.querySelectorAll('#ctpLeadGuestBody [data-ctp-filter]').forEach(function(el) {
                if (!ctpLeadGuestSanitize(el, showErrors)) ok = false;
                const rule = ctpLeadGuestRules[el.getAttribute('data-ctp-filter')];
                const val = (el.value || '').trim();
                if (rule && rule.complete && val && !rule.complete.test(val)) {
                    ok = false;
                    if (showErrors) ctpLeadGuestShowError(el.id, rule.incompleteError || rule.error);
                }
            });
            const nameEl = document.getElementById('customerName');
            const nameVal = (nameEl?.value || '').trim();
            if (!nameVal) {
                ok = false;
                if (showErrors) ctpLeadGuestShowError('customerName', 'Full name is required and can only contain letters.');
            } else if (!/^[\p{L}]+(?:[\p{L}\s\-]*[\p{L}])?$/u.test(nameVal) || /['"`]/.test(nameVal)) {
                ok = false;
                if (showErrors) ctpLeadGuestShowError('customerName', 'Full name can only contain letters. Quotes and special characters are not allowed.');
            }
            return ok;
        }
        window.ctpLeadGuestHasInvalidFields = ctpLeadGuestHasInvalidFields;
        document.querySelectorAll('#ctpLeadGuestBody [data-ctp-filter]').forEach(function(el) {
            const rule = ctpLeadGuestRules[el.getAttribute('data-ctp-filter')];
            el.addEventListener('compositionstart', function() { el.dataset.ctpComposing = '1'; });
            el.addEventListener('compositionend', function() {
                el.dataset.ctpComposing = '0';
                ctpLeadGuestSanitize(el, true);
                ctpUpdateSubmitButtonState();
            });
            el.addEventListener('input', function() {
                ctpLeadGuestSanitize(el, true);
                ctpUpdateSubmitButtonState();
            });
            el.addEventListener('paste', function() {
                setTimeout(function() {
                    ctpLeadGuestSanitize(el, true);
                    ctpUpdateSubmitButtonState();
                }, 0);
            });
            el.addEventListener('blur', function() {
                ctpLeadGuestSanitize(el, true);
                if (rule && rule.complete) {
                    const val = (el.value || '').trim();
                    if (val && !rule.complete.test(val)) {
                        ctpLeadGuestShowError(el.id, rule.incompleteError || rule.error);
                    }
                }
            });
            if (rule && rule.blockKeys) {
                el.addEventListener('keydown', function(ev) {
                    if (rule.blockKeys.indexOf(ev.key) !== -1) {
                        ev.preventDefault();
                        ctpLeadGuestShowError(el.id, rule.error);
                    }
                });
            }
        });
        function ctpUpdateFOCFieldsInModal() {
            const gsd = document.getElementById('pro_group_size_display');
            const focEl = document.getElementById('pro_foc_size');
            const gs = Math.max(0, ctpSafeInt(gsd ? gsd.value : 0));
            const foc = Math.max(0, ctpSafeInt(focEl ? focEl.value : 0));
            const row = document.getElementById('pro_includeFOCInPriceRow');
            const cb = document.getElementById('pro_include_foc_in_group_price');
            const total = gs + foc;
            const pp = document.getElementById('pro_paying_pax');
            const tp = document.getElementById('pro_total_pax_display');
            if (pp) pp.value = String(gs);
            if (tp) tp.value = String(total);
            if (row) row.classList.toggle('d-none', foc <= 0);
            if (cb) {
                cb.disabled = foc <= 0;
                if (foc <= 0) cb.checked = false;
            }
        }
        function ctpModalTourCap() {
            const gsd = document.getElementById('pro_group_size_display');
            const focEl = document.getElementById('pro_foc_size');
            const gs = Math.max(0, ctpSafeInt(gsd ? gsd.value : 0));
            const foc = Math.max(0, ctpSafeInt(focEl ? focEl.value : 0));
            return gs + foc;
        }
        /** Total tour pax = group + FOC. Infants do not count toward this cap; children do. */
        function ctpSyncModalGuestsToCap() {
            ctpUpdateFOCFieldsInModal();
            const cap = ctpModalTourCap();
            const childEl = document.getElementById('ctpModalChildren');
            const infEl = document.getElementById('ctpModalInfants');
            const maleEl = document.getElementById('ctpModalMale');
            const femaleEl = document.getElementById('ctpModalFemale');
            const adEl = document.getElementById('ctpModalAdults');
            if (!childEl || !infEl || !maleEl || !femaleEl) return;
            let ch = ctpSafeInt(childEl.textContent);
            if (ch > cap) {
                ch = cap;
                childEl.textContent = String(ch);
                window.ctpUpdateChildAgeDropdownsPro(ch);
            }
            let m = ctpSafeInt(maleEl.textContent);
            let f = ctpSafeInt(femaleEl.textContent);
            const rem = Math.max(0, cap - ch);
            if (m + f > rem) {
                f = Math.min(f, rem);
                m = rem - f;
                if (m < 0) {
                    m = 0;
                    f = rem;
                }
            } else if (m + f < rem) {
                m = rem - f;
            }
            maleEl.textContent = String(m);
            femaleEl.textContent = String(f);
            if (adEl) adEl.textContent = String(m + f);
        }
        function ctpRebalanceHeadcountToTotal() {
            ctpSyncModalGuestsToCap();
        }
        document.addEventListener('input', function(e) {
            if (!e.target) return;
            if (e.target.id === 'pro_group_size_display' || e.target.id === 'pro_foc_size') {
                ctpRebalanceHeadcountToTotal();
                ctpSyncTourTypeFromPax();
            }
        }, true);
        document.addEventListener('change', function(e) {
            if (e.target && e.target.id === 'pro_include_foc_in_group_price') {
                ctpUpdateFOCFieldsInModal();
            }
        }, true);
        window.ctpGuestDelta = function(type, change) {
            const maleEl = document.getElementById('ctpModalMale');
            const femaleEl = document.getElementById('ctpModalFemale');
            const childEl = document.getElementById('ctpModalChildren');
            const infEl = document.getElementById('ctpModalInfants');
            const adEl = document.getElementById('ctpModalAdults');
            if (!maleEl || !femaleEl || !childEl || !infEl) return;
            let m = ctpSafeInt(maleEl.textContent);
            let f = ctpSafeInt(femaleEl.textContent);
            let c = ctpSafeInt(childEl.textContent);
            let inf = ctpSafeInt(infEl.textContent);
            const isGroup = ctpIsGroup();
            const cap = isGroup ? ctpModalTourCap() : Infinity;

            if (type === 'male' || type === 'female') {
                if (isGroup) {
                    if (type === 'male') {
                        if (change > 0) {
                            if (f <= 0) return;
                            m++;
                            f--;
                        } else if (change < 0) {
                            if (m <= 0) return;
                            m--;
                            f++;
                        }
                    } else {
                        if (change > 0) {
                            if (m <= 0) return;
                            f++;
                            m--;
                        } else if (change < 0) {
                            if (f <= 0) return;
                            f--;
                            m++;
                        }
                    }
                    if (m + f < 1) return;
                    if (m + f + c > cap) return;
                } else {
                    const cur = type === 'male' ? m : f;
                    const nv = Math.max(0, cur + change);
                    if (type === 'male') m = nv; else f = nv;
                    if (m + f < 1) return;
                }
                maleEl.textContent = String(m);
                femaleEl.textContent = String(f);
                if (adEl) adEl.textContent = String(m + f);
                ctpSyncTourTypeFromPax();
                return;
            }
            if (type === 'children') {
                const nv = c + change;
                if (nv < 0) return;
                if (isGroup && m + f + nv > cap) return;
                c = nv;
                childEl.textContent = String(c);
                window.ctpUpdateChildAgeDropdownsPro(c);
                if (adEl) adEl.textContent = String(m + f);
                if (isGroup) ctpSyncModalGuestsToCap();
                ctpSyncTourTypeFromPax();
                return;
            }
            if (type === 'infants') {
                const nv = inf + change;
                if (nv < 0) return;
                infEl.textContent = String(nv);
            }
        };
        window.ctpGuestAdultsDelta = function(change) {
            const maleEl = document.getElementById('ctpModalMale');
            const femaleEl = document.getElementById('ctpModalFemale');
            const childEl = document.getElementById('ctpModalChildren');
            const adEl = document.getElementById('ctpModalAdults');
            if (!maleEl || !femaleEl || !childEl) return;
            let m = ctpSafeInt(maleEl.textContent);
            let f = ctpSafeInt(femaleEl.textContent);
            const c = ctpSafeInt(childEl.textContent);

            if (!ctpIsGroup()) {
                let adults = m + f;
                let newAdults = Math.max(0, adults + change);
                const currentAdults = m + f;
                if (newAdults > currentAdults) {
                    m += newAdults - currentAdults;
                } else if (newAdults < currentAdults) {
                    let toRemove = currentAdults - newAdults;
                    const fromM = Math.min(m, toRemove);
                    m -= fromM;
                    toRemove -= fromM;
                    if (toRemove > 0) f = Math.max(0, f - toRemove);
                    newAdults = m + f;
                }
                if (newAdults < 1) return;
                maleEl.textContent = String(m);
                femaleEl.textContent = String(f);
                if (adEl) adEl.textContent = String(m + f);
                ctpSyncTourTypeFromPax();
                return;
            }

            const cap = ctpModalTourCap();
            const maxAd = Math.max(0, cap - c);
            let total = m + f + change;
            total = Math.max(maxAd > 0 ? 1 : 0, Math.min(maxAd, total));
            const diff = total - (m + f);
            if (diff > 0) m += diff;
            else {
                let rem = -diff;
                const fromM = Math.min(m, rem);
                m -= fromM;
                rem -= fromM;
                f = Math.max(0, f - rem);
            }
            maleEl.textContent = String(m);
            femaleEl.textContent = String(f);
            if (adEl) adEl.textContent = String(m + f);
            ctpSyncTourTypeFromPax();
        };
        window.ctpGuestFemaleDelta = function(change) {
            const maleEl = document.getElementById('ctpModalMale');
            const femaleEl = document.getElementById('ctpModalFemale');
            const adEl = document.getElementById('ctpModalAdults');
            if (!maleEl || !femaleEl) return;
            let m = ctpSafeInt(maleEl.textContent);
            let f = ctpSafeInt(femaleEl.textContent);
            if (change > 0) {
                if (m <= 0) return;
                m--;
                f++;
            } else {
                if (f <= 0) return;
                f--;
                m++;
            }
            maleEl.textContent = String(m);
            femaleEl.textContent = String(f);
            if (adEl) adEl.textContent = String(m + f);
            ctpSyncTourTypeFromPax();
        };
        function ctpCollectChildAgesFromModal(expectedCount) {
            const expected = Math.max(0, parseInt(expectedCount, 10) || 0);
            if (!expected) return [];
            const box = document.getElementById('ctpChildAgeDropdowns');
            if (!box) return null;
            const selects = box.querySelectorAll('select.ctp-child-age');
            if (selects.length !== expected) return null;
            const ages = [];
            for (let i = 0; i < selects.length; i++) {
                const raw = String(selects[i].value ?? '').trim();
                if (raw === '') return null;
                const age = parseInt(raw, 10);
                if (!Number.isFinite(age) || age < 0 || age > 17) return null;
                ages.push(age);
            }
            return ages;
        }
        function ctpEnsureChildAgeDropdowns(childCount) {
            const box = document.getElementById('ctpChildAgeDropdowns');
            const n = Math.max(0, parseInt(childCount, 10) || 0);
            const current = box ? box.querySelectorAll('select.ctp-child-age').length : 0;
            if (current !== n) {
                window.ctpUpdateChildAgeDropdownsPro(n);
            }
        }
        window.ctpUpdateChildAgeDropdownsPro = function(childCount) {
            const sec = document.getElementById('ctpChildAgesSection');
            const box = document.getElementById('ctpChildAgeDropdowns');
            if (!sec || !box) return;
            const n = Math.max(0, parseInt(childCount, 10) || 0);
            const preserved = [];
            box.querySelectorAll('select.ctp-child-age').forEach(function(s) {
                preserved.push(String(s.value ?? '').trim());
            });
            if (!n) {
                sec.style.display = 'none';
                box.innerHTML = '';
                return;
            }
            sec.style.display = 'block';
            box.innerHTML = '';
            for (let i = 1; i <= n; i++) {
                const preset = preserved[i - 1] != null ? preserved[i - 1] : '';
                let opts = '<option value="">Age</option>';
                for (let a = 0; a <= 17; a++) {
                    const sel = preset !== '' && String(a) === preset ? ' selected' : '';
                    opts += '<option value="' + a + '"' + sel + '>' + a + '</option>';
                }
                box.insertAdjacentHTML('beforeend', '<div class="d-flex align-items-center gap-1 mb-1"><span class="small">C' + i + ':</span><select class="form-select form-select-sm ctp-child-age">' + opts + '</select></div>');
            }
        };
        function ctpCopyModalInputsFromHidden() {
            const gsd = document.getElementById('pro_group_size_display');
            const focEl = document.getElementById('pro_foc_size');
            if (gsd) gsd.value = String(Math.max(1, ctpSafeInt(document.getElementById('ctp_hidden_group_size').value) || 1));
            if (focEl) focEl.value = String(Math.max(0, ctpSafeInt(document.getElementById('ctp_hidden_foc_size').value)));
            const cb = document.getElementById('pro_include_foc_in_group_price');
            if (cb) cb.checked = document.getElementById('ctp_hidden_discount').value === '1';
            const maleEl = document.getElementById('ctpModalMale');
            const femaleEl = document.getElementById('ctpModalFemale');
            const childEl = document.getElementById('ctpModalChildren');
            const infEl = document.getElementById('ctpModalInfants');
            const adEl = document.getElementById('ctpModalAdults');
            if (maleEl) maleEl.textContent = String(ctpSafeInt(document.getElementById('ctp_hidden_male').value));
            if (femaleEl) femaleEl.textContent = String(ctpSafeInt(document.getElementById('ctp_hidden_female').value));
            if (childEl) childEl.textContent = String(ctpSafeInt(document.getElementById('ctp_hidden_child_count').value));
            if (infEl) infEl.textContent = String(ctpSafeInt(document.getElementById('ctp_hidden_infant_count').value));
            const m = maleEl ? ctpSafeInt(maleEl.textContent) : 0;
            const f = femaleEl ? ctpSafeInt(femaleEl.textContent) : 0;
            if (adEl) adEl.textContent = String(m + f);
            if (ctpIsGroup()) ctpRebalanceHeadcountToTotal();
            window.ctpUpdateChildAgeDropdownsPro(childEl ? ctpSafeInt(childEl.textContent) : 0);
            let ages = [];
            try {
                ages = JSON.parse(document.getElementById('ctp_hidden_child_ages').value || '[]');
            } catch (e2) { ages = []; }
            document.querySelectorAll('.ctp-child-age').forEach(function(sel, idx) {
                if (ages[idx] != null && ages[idx] !== '') sel.value = String(parseInt(ages[idx], 10));
            });
        }
        function ctpOpenGuestModal() {
            // Sync FIT/GROUP from current pax before showing FOC section
            ctpSyncTourTypeFromPax();
            const focSec = document.getElementById('ctpProGroupDetailsSection');
            if (focSec) focSec.classList.toggle('d-none', !ctpIsGroup());
            if (!window.tourProGuestConfigured) {
                if (ctpIsGroup()) ctpInitDefaultGroupHidden();
                else ctpInitDefaultFitHidden();
            }
            ctpCopyModalInputsFromHidden();
            // After copying guests into modal, re-sync (in case hidden adults+children cross threshold)
            ctpSyncTourTypeFromPax();
            if (focSec) focSec.classList.toggle('d-none', !ctpIsGroup());
            const el = document.getElementById('tourProGuestModal');
            if (!el || !window.bootstrap) return;
            const bm = bootstrap.Modal.getOrCreateInstance(el);
            el.addEventListener('shown.bs.modal', function onShown() {
                el.removeEventListener('shown.bs.modal', onShown);
                try {
                    document.querySelectorAll('#tourProGuestModal [data-bs-toggle="tooltip"]').forEach(function(t) {
                        bootstrap.Tooltip.getOrCreateInstance(t);
                    });
                } catch (e3) { /* ignore */ }
                ctpSyncTourTypeFromPax();
            }, { once: true });
            bm.show();
        }
        const ctpOpenBtn = document.getElementById('tourProOpenGuestModalBtn');
        if (ctpOpenBtn) ctpOpenBtn.addEventListener('click', function() { ctpOpenGuestModal(); });
        const ctpApplyBtn = document.getElementById('tourProGuestApplyBtn');
        if (ctpApplyBtn) {
            ctpApplyBtn.addEventListener('click', function() {
                // Auto FIT/GROUP from modal adults+children before saving (Lite parity)
                ctpSyncTourTypeFromPax();
                const childEl = document.getElementById('ctpModalChildren');
                let c = ctpSafeInt(childEl.textContent);
                const m = ctpSafeInt(document.getElementById('ctpModalMale').textContent);
                const f = ctpSafeInt(document.getElementById('ctpModalFemale').textContent);
                const inf = ctpSafeInt(document.getElementById('ctpModalInfants').textContent);
                if (m + f < 1) {
                    alert('At least one adult is required.');
                    return;
                }
                if (ctpIsGroup()) {
                    ctpSyncModalGuestsToCap();
                    const gs = Math.max(0, ctpSafeInt(document.getElementById('pro_group_size_display').value));
                    const foc = Math.max(0, ctpSafeInt(document.getElementById('pro_foc_size').value));
                    const cap = gs + foc;
                    c = Math.min(ctpSafeInt(childEl.textContent), cap);
                    childEl.textContent = String(c);
                    ctpSyncModalGuestsToCap();
                    c = ctpSafeInt(childEl.textContent);
                }
                ctpEnsureChildAgeDropdowns(c);
                const childAges = ctpCollectChildAgesFromModal(c);
                if (c > 0 && childAges === null) {
                    alert('Please select an age for each child.');
                    return;
                }
                if (ctpIsGroup()) {
                    let gs = Math.max(0, ctpSafeInt(document.getElementById('pro_group_size_display').value));
                    const foc = Math.max(0, ctpSafeInt(document.getElementById('pro_foc_size').value));
                    // Keep group size aligned with adults+children when FOC is 0
                    const payingFromGuests = Math.max(0, (m + f) + c - foc);
                    if (gs < 1) gs = Math.max(1, payingFromGuests);
                    const gsd = document.getElementById('pro_group_size_display');
                    if (gsd) gsd.value = String(gs);
                    const cb = document.getElementById('pro_include_foc_in_group_price');
                    ctpSetHidden('ctp_hidden_group_size', gs);
                    ctpSetHidden('ctp_hidden_foc_size', foc);
                    ctpSetHidden('ctp_hidden_paying_pax', gs);
                    ctpSetHidden('ctp_hidden_auto_foc', foc);
                    ctpSetHidden('ctp_hidden_discount', (cb && cb.checked) ? 1 : 0);
                } else {
                    ctpSetHidden('ctp_hidden_group_size', 0);
                    ctpSetHidden('ctp_hidden_foc_size', 0);
                    ctpSetHidden('ctp_hidden_paying_pax', 0);
                    ctpSetHidden('ctp_hidden_auto_foc', 0);
                    ctpSetHidden('ctp_hidden_discount', 0);
                }
                ctpSetHidden('ctp_hidden_male', m);
                ctpSetHidden('ctp_hidden_female', f);
                ctpSetHidden('ctp_hidden_adult_count', m + f);
                ctpSetHidden('ctp_hidden_child_count', c);
                ctpSetHidden('ctp_hidden_infant_count', inf);
                ctpSetHidden('ctp_hidden_child_ages', JSON.stringify(childAges));
                window.tourProGuestConfigured = true;
                ctpSyncTourTypeFromPax();
                ctpRenderGuestSummary();
                const gmod = document.getElementById('tourProGuestModal');
                const inst = gmod && window.bootstrap ? bootstrap.Modal.getInstance(gmod) : null;
                if (inst) inst.hide();
            });
        }
        function ctpRenderGuestSummary() {
            const span = document.getElementById('tourProGuestSummary');
            if (!span) return;
            if (!window.tourProGuestConfigured) {
                span.textContent = 'Click “Select tour guests” to set passengers...';
                span.classList.remove('is-set');
                ctpUpdateSubmitButtonState();
                return;
            }
            span.classList.add('is-set');
            const a = ctpSafeInt(document.getElementById('ctp_hidden_adult_count').value);
            const c = ctpSafeInt(document.getElementById('ctp_hidden_child_count').value);
            const i = ctpSafeInt(document.getElementById('ctp_hidden_infant_count').value);
            const mv = ctpSafeInt(document.getElementById('ctp_hidden_male').value);
            const fv = ctpSafeInt(document.getElementById('ctp_hidden_female').value);
            if (ctpIsGroup()) {
                const gs = ctpSafeInt(document.getElementById('ctp_hidden_group_size').value);
                const foc = ctpSafeInt(document.getElementById('ctp_hidden_foc_size').value);
                span.innerHTML = 'Paying <strong>' + gs + '</strong> + FOC <strong>' + foc + '</strong> · ' + a + ' adults (' + mv + 'M/' + fv + 'F), ' + c + ' ch, ' + i + ' inf';
            } else {
                span.innerHTML = a + ' adults (' + mv + 'M/' + fv + 'F), ' + c + ' ch, ' + i + ' inf';
            }
            ctpUpdateSubmitButtonState();
        }

        ctpRefreshTourTypeUI();
        ctpUpdateSubmitButtonState();

        function ctpHasSelect2() {
            return typeof jQuery !== 'undefined' && jQuery.fn && typeof jQuery.fn.select2 === 'function';
        }
        function ctpSelect2Config(kind) {
            return {
                placeholder: kind === 'agent' ? 'Type to search agent...' : 'Type to search agency...',
                allowClear: true,
                width: '100%',
                dropdownParent: jQuery('#createTourProModal')
            };
        }
        function ctpDestroySelect2(selectEl) {
            if (!selectEl || !ctpHasSelect2()) return;
            const $el = jQuery(selectEl);
            if ($el.data('select2')) $el.select2('destroy');
        }
        function ctpRebuildSelect2(selectEl, placeholder, items, disabled) {
            if (!selectEl) return;
            ctpDestroySelect2(selectEl);
            selectEl.innerHTML = '';
            const emptyOpt = document.createElement('option');
            emptyOpt.value = '';
            emptyOpt.textContent = '';
            selectEl.appendChild(emptyOpt);
            (items || []).forEach(function(item) {
                const opt = document.createElement('option');
                opt.value = String(item.id);
                opt.textContent = item.text;
                selectEl.appendChild(opt);
            });
            selectEl.disabled = !!disabled;
            if (ctpHasSelect2()) {
                const kind = selectEl.id === 'ctpAgentSelect' ? 'agent' : 'agency';
                const cfg = ctpSelect2Config(kind);
                if (placeholder) cfg.placeholder = placeholder;
                const $el = jQuery(selectEl);
                $el.prop('disabled', !!disabled);
                $el.select2(cfg);
            }
        }
        let ctpAgencyAgentHandlersBound = false;
        let ctpAgencyPickTimer = null;
        function ctpBindAgencyAgentHandlers() {
            if (ctpAgencyAgentHandlersBound || typeof jQuery === 'undefined') return;
            const $modal = jQuery('#createTourProModal');
            if (!$modal.length) return;
            ctpAgencyAgentHandlersBound = true;

            function ctpOnAgencyChosen(agencyId) {
                const id = agencyId ? String(agencyId) : '';
                const agencyIdVal = document.getElementById('agencyIdValue');
                if (agencyIdVal) agencyIdVal.value = id;
                clearTimeout(ctpAgencyPickTimer);
                ctpAgencyPickTimer = setTimeout(function() {
                    loadAgentsByAgency(id);
                    ctpUpdateSubmitButtonState();
                }, 0);
            }
            function ctpOnAgentChosen(agentId) {
                const id = agentId ? String(agentId) : '';
                const agentIdVal = document.getElementById('agentIdValue');
                if (agentIdVal) agentIdVal.value = id;
                ctpUpdateSubmitButtonState();
            }

            $modal.on('change.ctpPro', '#ctpAgencySelect', function() {
                ctpOnAgencyChosen(jQuery(this).val());
            });
            $modal.on('select2:select.ctpPro', '#ctpAgencySelect', function(e) {
                const id = (e.params && e.params.data && e.params.data.id != null)
                    ? e.params.data.id
                    : jQuery(this).val();
                ctpOnAgencyChosen(id);
            });
            $modal.on('select2:clear.ctpPro', '#ctpAgencySelect', function() {
                ctpOnAgencyChosen('');
            });

            $modal.on('change.ctpPro', '#ctpAgentSelect', function() {
                ctpOnAgentChosen(jQuery(this).val());
            });
            $modal.on('select2:select.ctpPro', '#ctpAgentSelect', function(e) {
                const id = (e.params && e.params.data && e.params.data.id != null)
                    ? e.params.data.id
                    : jQuery(this).val();
                ctpOnAgentChosen(id);
            });
            $modal.on('select2:clear.ctpPro', '#ctpAgentSelect', function() {
                ctpOnAgentChosen('');
            });
        }
        function ctpInitTourProSelect2() {
            if (!ctpHasSelect2()) return;
            const $agency = jQuery('#ctpAgencySelect');
            const $agent = jQuery('#ctpAgentSelect');
            if ($agency.length && !$agency.data('select2')) {
                $agency.select2(ctpSelect2Config('agency'));
            }
            if ($agent.length && !$agent.data('select2')) {
                $agent.select2(ctpSelect2Config('agent'));
            }
        }
        function ctpResetAgentSelect() {
            const agentSel = document.getElementById('ctpAgentSelect');
            const agentIdVal = document.getElementById('agentIdValue');
            if (!agentSel) return;
            if (agentIdVal) agentIdVal.value = '';
            availableAgents = [];
            ctpRebuildSelect2(agentSel, 'Choose agent...', [], true);
        }

        function loadAgenciesForDmc() {
            const agencySel = document.getElementById('ctpAgencySelect');
            const agencyIdVal = document.getElementById('agencyIdValue');
            if (!agencySel) return;
            if (agencyIdVal) agencyIdVal.value = '';
            ctpRebuildSelect2(agencySel, 'Loading agencies...', [], true);
            ctpResetAgentSelect();

            fetch('{{ route("enquiry-form-pro.get-agencies") }}?by_dmc=1')
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success && data.agencies && data.agencies.length > 0) {
                        availableAgencies = data.agencies;
                        const items = data.agencies.map(function(agency) {
                            return { id: agency.agency_id, text: agency.agency_name };
                        });
                        ctpRebuildSelect2(agencySel, 'Type to search agency...', items, false);
                    } else {
                        availableAgencies = [];
                        ctpRebuildSelect2(agencySel, 'No agencies for this DMC', [], true);
                    }
                    ctpInitTourProSelect2();
                })
                .catch(function(error) {
                    console.error('Error loading agencies:', error);
                    availableAgencies = [];
                    ctpRebuildSelect2(agencySel, 'Error loading agencies', [], true);
                    ctpInitTourProSelect2();
                });
        }

        function loadAgentsByAgency(agencyId) {
            const agentSel = document.getElementById('ctpAgentSelect');
            const agentIdVal = document.getElementById('agentIdValue');
            if (!agentSel) return;
            if (!agencyId) {
                ctpResetAgentSelect();
                ctpUpdateSubmitButtonState();
                return;
            }
            if (agentIdVal) agentIdVal.value = '';
            ctpRebuildSelect2(agentSel, 'Loading agents...', [], true);

            fetch('{{ route("enquiry-form-pro.get-agents") }}?agency_id=' + encodeURIComponent(agencyId), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success && data.agents && data.agents.length > 0) {
                        availableAgents = data.agents;
                        const items = data.agents.map(function(agent) {
                            return { id: agent.agent_id, text: agent.name };
                        });
                        ctpRebuildSelect2(agentSel, 'Type to search agent...', items, false);
                    } else {
                        availableAgents = [];
                        ctpRebuildSelect2(agentSel, 'No agents available', [], true);
                    }
                    ctpUpdateSubmitButtonState();
                })
                .catch(function(error) {
                    console.error('Error loading agents:', error);
                    availableAgents = [];
                    ctpRebuildSelect2(agentSel, 'Error loading agents', [], true);
                    ctpUpdateSubmitButtonState();
                });
        }

        // Multiple destination checkbox toggle
        const multipleDestCheckbox = document.getElementById('multipleDestination');
        const singleDestDiv = document.getElementById('singleDestinationDiv');
        const multipleDestDiv = document.getElementById('multipleDestinationDiv');
        const destinationSingle = document.getElementById('destinationSingle');
        const destinationMultiple = document.getElementById('destinationMultiple');

        multipleDestCheckbox.addEventListener('change', function() {
            if (this.checked) {
                // Switch to multiple mode
                singleDestDiv.style.display = 'none';
                multipleDestDiv.style.display = 'block';
                // Clear single destination
                destinationSingleInput.value = '';
                destinationSingleValue.value = '';
            } else {
                // Switch to single mode
                singleDestDiv.style.display = 'block';
                multipleDestDiv.style.display = 'none';
                // Clear multiple destinations
                destinationInput.value = '';
                selectedDestinations = [];
                updateSelectedDestinations();
            }
            
            ctpUpdateSubmitButtonState();
        });

        const destSingleRadio = document.getElementById('ctpDestSingleCity');
        const destMultiRadio = document.getElementById('ctpDestMultipleCities');
        if (destSingleRadio && destMultiRadio && multipleDestCheckbox) {
            destSingleRadio.addEventListener('change', function() {
                if (this.checked && multipleDestCheckbox.checked) {
                    multipleDestCheckbox.checked = false;
                    multipleDestCheckbox.dispatchEvent(new Event('change'));
                }
            });
            destMultiRadio.addEventListener('change', function() {
                if (this.checked && !multipleDestCheckbox.checked) {
                    multipleDestCheckbox.checked = true;
                    multipleDestCheckbox.dispatchEvent(new Event('change'));
                }
            });
        }

        document.querySelectorAll('#createTourProModal [data-ctp-step]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (btn.disabled) return;
                const step = btn.getAttribute('data-ctp-step');
                const modalBody = document.querySelector('#createTourProModal .ctp-main-body');
                const target = step === 'guest'
                    ? document.getElementById('ctpLeadGuestAccordion')
                    : document.getElementById('ctpSectionTour');
                document.querySelectorAll('#createTourProModal [data-ctp-step]').forEach(function(el) {
                    if (!el.disabled) el.classList.toggle('is-active', el === btn);
                });
                if (step === 'guest' && typeof ctpExpandLeadGuestAccordion === 'function') {
                    ctpExpandLeadGuestAccordion();
                }
                if (modalBody && target) {
                    modalBody.scrollTo({ top: Math.max(0, target.offsetTop - 8), behavior: 'smooth' });
                }
            });
        });

        // Single destination autocomplete
        const destinationSingleInput = document.getElementById('destinationSingle');
        const suggestionBoxSingle = document.getElementById('destinationSuggestionsSingle');
        const destinationSingleValue = document.getElementById('destinationSingleValue');

        destinationSingleInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            
            if (query.length < 1) {
                suggestionBoxSingle.style.display = 'none';
                return;
            }

            const filtered = allDestinations.filter(dest => 
                dest.name.toLowerCase().includes(query)
            );

            if (filtered.length > 0) {
                suggestionBoxSingle.innerHTML = '';
                filtered.forEach(dest => {
                    const item = document.createElement('a');
                    item.href = 'javascript:void(0);';
                    item.className = 'list-group-item list-group-item-action';
                    item.style.padding = '6px 10px';
                    item.style.fontSize = '10px';
                    item.style.cursor = 'pointer';
                    item.textContent = dest.country ? (dest.name + ' (' + dest.country + ')') : dest.name;
                    item.addEventListener('click', function() {
                        destinationSingleInput.value = dest.name;
                        destinationSingleValue.value = dest.name;
                        suggestionBoxSingle.style.display = 'none';
                        ctpUpdateSubmitButtonState();
                    });
                    suggestionBoxSingle.appendChild(item);
                });
                suggestionBoxSingle.style.display = 'block';
            } else {
                suggestionBoxSingle.style.display = 'none';
            }
        });

        // Multiple destination autocomplete
        const destinationInput = document.getElementById('destinationMultiple');
        const suggestionBox = document.getElementById('destinationSuggestions');
        const selectedDestinationsDiv = document.getElementById('selectedDestinations');

        destinationInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            
            if (query.length < 1) {
                suggestionBox.style.display = 'none';
                return;
            }

            const filtered = allDestinations.filter(dest => 
                dest.name.toLowerCase().includes(query) && 
                !selectedDestinations.includes(dest.name)
            );

            if (filtered.length > 0) {
                suggestionBox.innerHTML = '';
                filtered.forEach(dest => {
                    const item = document.createElement('a');
                    item.href = 'javascript:void(0);';
                    item.className = 'list-group-item list-group-item-action';
                    item.style.padding = '6px 10px';
                    item.style.fontSize = '10px';
                    item.style.cursor = 'pointer';
                    item.textContent = dest.country ? (dest.name + ' (' + dest.country + ')') : dest.name;
                    item.addEventListener('click', function() {
                        addDestination(dest.name);
                        destinationInput.value = '';
                        suggestionBox.style.display = 'none';
                    });
                    suggestionBox.appendChild(item);
                });
                suggestionBox.style.display = 'block';
            } else {
                suggestionBox.style.display = 'none';
            }
        });

        function addDestination(name) {
            if (!selectedDestinations.includes(name)) {
                selectedDestinations.push(name);
                updateSelectedDestinations();
            }
        }

        function removeDestination(name) {
            selectedDestinations = selectedDestinations.filter(d => d !== name);
            updateSelectedDestinations();
        }

        function updateSelectedDestinations() {
            selectedDestinationsDiv.innerHTML = '';
            selectedDestinations.forEach(dest => {
                const badge = document.createElement('span');
                badge.className = 'badge bg-info me-1 mb-1 d-inline-flex align-items-center';
                badge.style.fontSize = '11px';
                badge.style.padding = '4px 8px';
                badge.innerHTML = `${dest} <i class="ri-close-line ms-1" style="cursor: pointer; font-size: 14px;"></i>`;
                badge.querySelector('i').addEventListener('click', function() {
                    removeDestination(dest);
                });
                selectedDestinationsDiv.appendChild(badge);
            });

            // Update hidden input
            document.getElementById('destinationsArray').value = JSON.stringify(selectedDestinations);
            ctpUpdateSubmitButtonState();
        }

        /** Local YYYY-MM-DD (avoids UTC shift from toISOString). */
        function ctpLocalDateStr(dateObj) {
            const y = dateObj.getFullYear();
            const m = String(dateObj.getMonth() + 1).padStart(2, '0');
            const d = String(dateObj.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        }

        function ctpAddDays(dateStr, days) {
            const parts = String(dateStr || '').split('-').map(Number);
            if (parts.length !== 3 || parts.some(isNaN)) return dateStr;
            const dt = new Date(parts[0], parts[1] - 1, parts[2]);
            dt.setDate(dt.getDate() + days);
            return ctpLocalDateStr(dt);
        }

        /** Earliest selectable tour start = tomorrow. */
        function ctpMinTourStartDate() {
            const d = new Date();
            d.setHours(0, 0, 0, 0);
            d.setDate(d.getDate() + 1);
            return ctpLocalDateStr(d);
        }

        // Tour can start from tomorrow onwards — today and past dates are not selectable
        const tourStartDateInput = document.getElementById('tourStartDate');
        const tourEndDateInput = document.getElementById('tourEndDate');

        /** Re-apply the tomorrow floor (also keeps a long-open page correct past midnight). */
        window.ctpApplyTourStartFloor = function () {
            const startEl = document.getElementById('tourStartDate');
            const endEl = document.getElementById('tourEndDate');
            if (!startEl || !endEl) return;
            const minStartStr = ctpMinTourStartDate();
            startEl.setAttribute('min', minStartStr);
            if (!startEl.value || startEl.value < minStartStr) {
                startEl.value = minStartStr;
            }
            const minEndStr = ctpAddDays(startEl.value, 1);
            endEl.setAttribute('min', minEndStr);
            if (!endEl.value || endEl.value < minEndStr) {
                endEl.value = minEndStr;
            }
        };

        if (tourStartDateInput && tourEndDateInput) {
            window.ctpApplyTourStartFloor();

            // Update end date min when start date changes
            tourStartDateInput.addEventListener('change', function() {
                const floorStr = ctpMinTourStartDate();
                if (this.value && this.value < floorStr) {
                    alert('Start date must be tomorrow or later');
                    this.value = floorStr;
                }
                const minEndDateStr = ctpAddDays(this.value || floorStr, 1);
                tourEndDateInput.setAttribute('min', minEndDateStr);

                // If end date is less than start date + 1, update it
                if (!tourEndDateInput.value || tourEndDateInput.value < minEndDateStr) {
                    tourEndDateInput.value = minEndDateStr;
                }
                ctpUpdateSubmitButtonState();
            });
            tourEndDateInput.addEventListener('change', ctpUpdateSubmitButtonState);
        }
        
        // Form validation (contact number and email are optional)
        document.getElementById('createTourProForm').addEventListener('submit', function(e) {
            if (!ctpValidateLeadGuestFields(true)) {
                e.preventDefault();
                if (typeof ctpExpandLeadGuestAccordion === 'function') ctpExpandLeadGuestAccordion();
                return false;
            }

            if (!(document.getElementById('agencyIdValue')?.value || '').trim()) {
                e.preventDefault();
                alert('Please select an agency from the list.');
                return false;
            }
            if (!(document.getElementById('agentIdValue')?.value || '').trim()) {
                e.preventDefault();
                alert('Please select an agent from the list.');
                return false;
            }

            const multipleDestChecked = document.getElementById('multipleDestination').checked;
            
            // Validate destination based on mode
            if (multipleDestChecked) {
                if (selectedDestinations.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one destination');
                    return false;
                }
            } else {
                const singleDest = document.getElementById('destinationSingleValue').value;
                if (!singleDest || singleDest.trim() === '') {
                    e.preventDefault();
                    alert('Please select a destination');
                    return false;
                }
            }

            // Validate dates — tour must start tomorrow or later
            const startDateStr = document.getElementById('tourStartDate').value;
            const endDateStr = document.getElementById('tourEndDate').value;
            const minStartDateStr = ctpMinTourStartDate();

            if (!startDateStr || startDateStr < minStartDateStr) {
                e.preventDefault();
                alert('Start date must be tomorrow or later');
                return false;
            }

            const minEndDateStr = ctpAddDays(startDateStr, 1);

            if (!endDateStr || endDateStr < minEndDateStr) {
                e.preventDefault();
                alert('End date must be at least 1 day after start date');
                return false;
            }

            if (!window.tourProGuestConfigured) {
                e.preventDefault();
                alert('Click "Select tour guests", set passengers, then Apply Selection.');
                return false;
            }
            // Final FIT/GROUP sync from saved adults+children (Lite parity)
            ctpSyncTourTypeFromPax();
            const adults = ctpSafeInt(document.getElementById('ctp_hidden_adult_count').value);
            const children = ctpSafeInt(document.getElementById('ctp_hidden_child_count').value);
            const infants = ctpSafeInt(document.getElementById('ctp_hidden_infant_count').value);
            if (adults + children + infants === 0) {
                e.preventDefault();
                alert('Please specify at least one passenger.');
                return false;
            }
            if (adults < 1) {
                e.preventDefault();
                alert('At least one adult is required.');
                return false;
            }
        });

        // Close suggestions when clicking outside
        document.addEventListener('click', function(e) {
            // Close multiple destinations suggestions
            if (!destinationInput.contains(e.target) && !suggestionBox.contains(e.target)) {
                suggestionBox.style.display = 'none';
            }
            // Close single destination suggestions
            if (!destinationSingleInput.contains(e.target) && !suggestionBoxSingle.contains(e.target)) {
                suggestionBoxSingle.style.display = 'none';
            }
        });
    });
</script>


