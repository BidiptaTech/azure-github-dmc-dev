@extends('layouts.layout')
@section('title', 'New Enquiries')
@extends('layouts.datatablecss')

<!-- Date Range Picker CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-3 mb-2">
                <span class="text-muted fw-light">Bookings /</span> New Enquiries
            </h4>
            <p class="text-muted">Manage all new enquiries and convert them to bookings</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-primary fs-6">
                <i class="ri-file-list-line me-1"></i>
                <span id="rangeCount">{{ $tours->where('created_at', '>=', now()->startOfMonth())->where('created_at', '<=', now()->endOfMonth())->count() }}</span>
                <span id="rangeLabel">{{ date('F') }}</span> Enquiries
            </span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1" id="statEnquiriesCount">{{ $tours->where('created_at', '>=', now()->startOfMonth())->where('created_at', '<=', now()->endOfMonth())->count() }}</h5>
                            <p class="text-muted mb-0" id="statEnquiriesLabel">{{ date('F') }} Enquiries</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-primary rounded">
                                <i class="ri-questionnaire-line ri-24px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1" id="statTodayCount">{{ $tours->where('created_at', '>=', now()->today())->count() }}</h5>
                            <p class="text-muted mb-0">Today's Enquiries</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-success rounded">
                                <i class="ri-calendar-line ri-24px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1" id="statAdultsCount">{{ $tours->where('created_at', '>=', now()->startOfMonth())->where('created_at', '<=', now()->endOfMonth())->where('adult', '>', 0)->sum('adult') }}</h5>
                            <p class="text-muted mb-0" id="statAdultsLabel">{{ date('F') }} Adults</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-info rounded">
                                <i class="ri-user-line ri-24px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1" id="statChildrenCount">{{ $tours->where('created_at', '>=', now()->startOfMonth())->where('created_at', '<=', now()->endOfMonth())->where('child', '>', 0)->sum('child') }}</h5>
                            <p class="text-muted mb-0" id="statChildrenLabel">{{ date('F') }} Children</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-warning rounded">
                                <i class="ri-user-smile-line ri-24px"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Filters</h5>
            <button class="btn btn-sm btn-outline-secondary" onclick="resetFilters()">
                <i class="ri-refresh-line me-1"></i> Reset
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Tour ID, Display ID, Destination...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Country</label>
                    <select class="form-select" id="countryFilter">
                        <option value="">All Countries</option>
                        @foreach($tours->pluck('destination')->unique()->filter() as $destination)
                            <option value="{{ $destination }}">{{ $destination }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">City</label>
                    <select class="form-select" id="cityFilter">
                        <option value="">All Cities</option>
                        @foreach($tours->pluck('city')->unique()->filter() as $city)
                            <option value="{{ $city }}">{{ $city }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Agent</label>
                    <select class="form-select" id="agentFilter">
                        <option value="">All Agents</option>
                        @foreach($tours->where('agent_name', '!=', null)->pluck('agent_name', 'agent_id')->unique() as $agentId => $agentName)
                            <option value="{{ $agentName }}">{{ $agentName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <input type="text" class="form-control" id="dateRange" placeholder="Select date range" readonly>
                    <input type="hidden" id="dateRangeStart">
                    <input type="hidden" id="dateRangeEnd">
                </div>
            </div>
        </div>
    </div>

    <!-- Tours Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">New Enquiries List</h5>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-warning btn-sm dropdown-toggle" type="button" id="exportDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-download"></i> Export
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportCopy">Copy</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportCSV">CSV</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportExcel">Excel</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportPDF">PDF</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportPrint">Print</a></li>
                    </ul>
                </div>
                {{-- <button class="btn btn-sm btn-primary" onclick="bulkActions()">
                    <i class="ri-settings-line me-1"></i> Bulk Actions
                </button> --}}
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="datatables-basic table table-bordered" id="toursTable">
                    <thead class="table-light">
                        <tr>
                            {{-- <th>
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th> --}}
                            <th>#</th>
                            <th>Tour Details</th>
                            <th>Destination</th>
                            <th>Guests</th>
                            <th>Agent</th>
                            <th>Services</th>
                            <th>Check-in/Check-out</th>
                            <th>Created At</th>
                            <th>Negotiation</th>
                            <th>Actions</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tours as $key => $tour)
                        <tr 
                            data-created-at="{{ optional($tour->created_at)->toDateString() }}"
                            data-updated-at="{{ optional($tour->updated_at)->toDateString() }}"
                            data-adult="{{ (int)($tour->adult ?? 0) }}"
                            data-child="{{ (int)($tour->child ?? 0) }}"
                            data-tour-status="{{ $tour->tour_status ?? '' }}"
                        >
                            {{-- <td>
                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $tour->tour_id }}">
                            </td> --}}
                            <td>{{ $key + 1 }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-primary">{{ $tour->display_id }}</strong>
                                    <small class="text-muted">Tour ID: #{{ $tour->tour_id }}</small>
                                    @if($tour->multi_enq_id)
                                        <small class="text-info">Multi: {{ $tour->multi_enq_id }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ $tour->destination ?? 'N/A' }}</span>
                                    <small class="text-muted">{{ $tour->city ?? 'N/A' }}</small>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex gap-2">
                                    @if($tour->adult > 0)
                                        <span class="badge bg-primary">{{ $tour->adult }} Adults</span>
                                    @endif
                                    @if($tour->child > 0)
                                        <span class="badge bg-warning">{{ $tour->child }} Children</span>
                                    @endif
                                    @if($tour->adult == 0 && $tour->child == 0)
                                        <span class="text-muted">No guests specified</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    @if($tour->agent_name)
                                        <span class="fw-medium text-primary">{{ $tour->agent_name }}</span>
                                        <small class="text-muted">
                                            <i class="fas fa-building me-1"></i>
                                            {{ $tour->agent_company_name ?? 'N/A' }}
                                        </small>
                                    @else
                                        <span class="text-muted">No agent assigned</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    @php
                                        // Fetch orders for this tour to get actual service data
                                        $orders = \App\Models\Order::where('tour_id', $tour->tour_id)->where('bookingType', 'enquiry')->get();
                                        $svc = [
                                            'hotel' => 0,
                                            'attraction' => 0,
                                            'restaurant' => 0,
                                            'guide' => 0,
                                            'entry_port' => 0,
                                            'exit_port' => 0,
                                            'travel_hourly' => 0,
                                            'travel_point' => 0,
                                            'local_transport' => 0,
                                        ];
                                        $serviceData = [];
                                        
                                        foreach($orders as $order) {
                                            if(isset($svc[$order->type])) {
                                                $svc[$order->type]++;
                                                if(!isset($serviceData[$order->type])) {
                                                    $serviceData[$order->type] = [];
                                                }
                                                $serviceData[$order->type][] = $order;
                                            }
                                        }
                                        
                                        $icons = [
                                            'hotel' => 'ri-hotel-line',
                                            'attraction' => 'ri-building-2-line',
                                            'restaurant' => 'ri-restaurant-2-line',
                                            'guide' => 'ri-user-voice-line',
                                            'entry_port' => 'ri-flight-land-line',
                                            'exit_port' => 'ri-flight-takeoff-line',
                                            'travel_hourly' => 'ri-time-line',
                                            'travel_point' => 'ri-route-line',
                                            'local_transport' => 'ri-car-line',
                                        ];
                                        
                                        // For debugging
                                        $debugInfo = [
                                            'tour_id' => $tour->tour_id,
                                            'orders_count' => $orders->count(),
                                            'svc' => $svc,
                                            'serviceData_keys' => array_keys($serviceData)
                                        ];
                                    @endphp
                                    @foreach($svc as $key=>$count)
                                        @if(intval($count) > 0)
                                            @if(in_array($key, ['hotel', 'attraction', 'restaurant', 'guide', 'entry_port', 'exit_port', 'travel_hourly', 'travel_point', 'local_transport']))
                                                <span class="badge bg-light text-dark border" style="cursor: pointer;" 
                                                      onclick="openServiceModal('{{ $key }}', {{ $tour->tour_id }}, event)"
                                                      data-debug-info="{{ json_encode($debugInfo) }}">
                                                    <i class="{{ $icons[$key] }} me-1"></i>
                                                    @if($key === 'entry_port')
                                                        Arrival: {{ $count }}
                                                    @elseif($key === 'exit_port')
                                                        Departure: {{ $count }}
                                                    @elseif($key === 'travel_hourly')
                                                        Local-Tour Hourly: {{ $count }}
                                                    @elseif($key === 'travel_point')
                                                        Local-Tour Point to Point: {{ $count }}
                                                    @elseif($key === 'local_transport')
                                                        Local Transport: {{ $count }}
                                                    @else
                                                        {{ ucfirst($key) }}: {{ $count }}
                                                    @endif
                                                </span>
                                            @else
                                                <span class="badge bg-light text-dark border">
                                                    <i class="{{ $icons[$key] }} me-1"></i>
                                                    @if($key === 'entry_port')
                                                        Arrival: {{ $count }}
                                                    @elseif($key === 'exit_port')
                                                        Departure: {{ $count }}
                                                    @elseif($key === 'travel_hourly')
                                                        Local-Tour Hourly: {{ $count }}
                                                    @elseif($key === 'travel_point')
                                                        Local-Tour Point to Point: {{ $count }}
                                                    @elseif($key === 'local_transport')
                                                        Local Transport: {{ $count }}
                                                    @else
                                                        {{ ucfirst($key) }}: {{ $count }}
                                                    @endif
                                                </span>
                                            @endif
                                        @endif
                                    @endforeach
                                    @if(array_sum(array_map('intval', $svc)) === 0)
                                        <span class="text-muted">No services</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    @if($tour->check_in_time)
                                        <small><strong>In:</strong> {{ \Carbon\Carbon::parse($tour->check_in_time)->format('D, M d, Y') }}</small>
                                    @endif
                                    @if($tour->check_out_time)
                                        <small><strong>Out:</strong> {{ \Carbon\Carbon::parse($tour->check_out_time)->format('D, M d, Y') }}</small>
                                    @endif
                                    @if(!$tour->check_in_time && !$tour->check_out_time)
                                        <span class="text-muted">Not specified</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span>{{ $tour->created_at->format('D,  M d, Y') }}</span>
                                    <small class="text-muted">{{ $tour->created_at->format('h:i A') }}</small>
                                </div>
                            </td>
                            <td>
                            @php 
                                $enquiryComment = $enquary_comments->where('tour_id', $tour->tour_id)->first();
                            @endphp
                                @if($enquiryComment && $enquiryComment->sender_type == "agent" && $tour->tour_status == "New Enquiry")
                                    <button 
                                        type="button"
                                        class="btn btn-sm btn-warning"
                                        data-tour-id="{{ $tour->tour_id }}"
                                        data-enquiry-id="{{ $enquiryComment->enquiry_id ?? '' }}"
                                        data-price="{{ $enquiryComment->amount ?? 0 }}"
                                        data-actual="{{ $enquiryComment->actual_amount ?? 0 }}"
                                        data-comment="{{ $enquiryComment->comment ?? '' }}"
                                        onclick="openNewEnquiryModal(this, '{{ route('update-price-comment') }}')"
                                    >
                                        Check Negotiation
                                    </button>
                                @elseif($enquiryComment && $enquiryComment->sender_type == "OM" && $tour->tour_status == "New Enquiry")
                                    <span class="text-muted">Waiting for agent response</span>
                                @else
                                    <span class="text-muted">No negotiation</span>
                                @endif
                            </td>
                            {{-- <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('bookings.view-tour', $tour->tour_id) }}">
                                                <i class="ri-eye-line me-2"></i> View Details
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="convertToProspect('{{ $tour->tour_id }}')">
                                                <i class="ri-arrow-right-line me-2"></i> Move to Follow Up
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="convertToTentative('{{ $tour->tour_id }}')">
                                                <i class="ri-bookmark-line me-2"></i> Mark as Tentative
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" onclick="deleteTour('{{ $tour->tour_id }}')">
                                                <i class="ri-delete-bin-line me-2"></i> Delete
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td> --}}
                            <td>
                                <a href="{{ route('bookings.view-tour', Crypt::encrypt($tour->tour_id)) }}" 
                                   class="btn btn-outline-primary btn-sm rounded-pill">
                                    <i class="ri-eye-line"></i> View
                                </a>
                            </td>
                            
                        </tr>
                        @empty
                        <span class="text-muted">No new enquiries found</span>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    <p class="text-muted mb-0">
                        Showing {{ $tours->firstItem() ?? 0 }} to {{ $tours->lastItem() ?? 0 }} of {{ $tours->total() }} results
                    </p>
                </div>
                <div>
                    {{ $tours->links() }}
                </div>
            </div> --}}
        </div>
    </div>
    
    <!-- Update Price Modal (New Enquiries) -->
    <div class="modal fade" id="newEnquiryUpdateModal" tabindex="-1" aria-labelledby="newEnquiryUpdateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newEnquiryUpdateModalLabel">Update Price & Comment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="newEnquiryUpdateForm" method="POST" action="">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="enquiry_id" id="new_enquiry_modal_enquiry_id" />
                        
                        <!-- Current details display -->
                        <div class="border rounded p-3 bg-light mb-3">
                            <div class="row g-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">Actual Amount</small>
                                    <div class="fw-semibold" id="new_enquiry_display_actual">—</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Previous Negotiated Amount</small>
                                    <div class="fw-semibold" id="new_enquiry_display_price">—</div>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted d-block">Last Comment</small>
                                    <div class="fw-semibold" id="new_enquiry_display_comment">—</div>
                                </div>
                            </div>
                        </div>

                        <!-- New update inputs -->
                        <div class="mb-3">
                            <label for="new_enquiry_current_price" class="form-label">New Price</label>
                            <input id="new_enquiry_current_price" type="number" name="price" class="form-control" placeholder="Enter new price" onkeyup="validateNewEnquiryPrice(this)" required />
                            <div id="new-enquiry-warning-message" class="alert alert-warning mt-2 py-2 px-3 d-none">
                                Enquiry price cannot exceed the actual amount.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="new_enquiry_comment" class="form-label">New Comment</label>
                            <textarea id="new_enquiry_comment" name="comment" rows="3" class="form-control" placeholder="Enter new comment" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Service Modals for each tour -->
    @foreach($tours as $tour)
    @php
    // Re-fetch orders for this tour for modal rendering
    $orders = \App\Models\Order::where('tour_id', $tour->tour_id)->get();
    $svc = [
        'hotel' => 0,
        'attraction' => 0,
        'restaurant' => 0,
        'guide' => 0,
        'entry_port' => 0,
        'exit_port' => 0,
        'travel_hourly' => 0,
        'travel_point' => 0,
        'local_transport' => 0,
    ];
    $serviceData = [];
    
    foreach($orders as $order) {
        if(isset($svc[$order->type])) {
            $svc[$order->type]++;
            if(!isset($serviceData[$order->type])) {
                $serviceData[$order->type] = [];
            }
            $serviceData[$order->type][] = $order;
        }
    }
@endphp

<!-- Hotel Details Modal -->
@if(isset($svc['hotel']) && $svc['hotel'] > 0)
 <div class="modal fade" id="hotelDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="hotelDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
            @if(isset($serviceData['hotel']) && count($serviceData['hotel']) > 0)
                @php
                    $firstHotelOrder = $serviceData['hotel'][0];
                    $firstHotelData = is_string($firstHotelOrder->data) ? json_decode($firstHotelOrder->data, true) : $firstHotelOrder->data;
                    $firstBooking = is_array($firstHotelData) ? $firstHotelData[0] : null;
                @endphp
                @if($firstBooking)
                    <!-- Hero Header -->
                    <div class="modal-header p-0 border-0 position-relative" style="height: 200px; background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                        <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-between p-4">
                            <div class="text-white">
                                <h3 class="mb-1 fw-bold">
                                    <i class="ri-hotel-line me-2"></i>Hotel Enquiries
                                </h3>
                                <p class="mb-0 opacity-75">Tour #{{ $tour->tour_id }} Hotel Details</p>
                                <div class="mt-2">
                                    <span class="badge bg-white bg-opacity-90 text-primary px-3 py-2">
                                        <i class="ri-calendar-line me-1"></i>
                                        @if(isset($firstBooking['bookingDate']) && is_array($firstBooking['bookingDate']) && count($firstBooking['bookingDate']) > 0)
                                            {{ \Carbon\Carbon::parse($firstBooking['bookingDate'][0])->format('M d') }} - 
                                            {{ \Carbon\Carbon::parse(end($firstBooking['bookingDate']))->format('M d, Y') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('hotel', {{ $tour->tour_id }})" aria-label="Close" style="filter: brightness(0) invert(1); font-size: 1.2rem;"></button>
                        </div>
                    </div>
                @else
                    <div class="modal-header p-4 border-0" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                        <h5 class="modal-title fw-bold text-white">
                            <i class="ri-hotel-line me-2"></i>
                            Hotel Booking Details - Tour #{{ $tour->tour_id }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('hotel', {{ $tour->tour_id }})" aria-label="Close"></button>
                    </div>
                @endif
            @else
                <div class="modal-header p-4 border-0" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                    <h5 class="modal-title fw-bold text-white">
                        <i class="ri-hotel-line me-2"></i>
                        Hotel Booking Details - Tour #{{ $tour->tour_id }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('hotel', {{ $tour->tour_id }})" aria-label="Close"></button>
                </div>
            @endif
            <div class="modal-body p-4">
                @if(isset($serviceData['hotel']) && count($serviceData['hotel']) > 0)
                    @foreach($serviceData['hotel'] as $index => $hotelOrder)
                    @php
                        $hotelData = is_string($hotelOrder->data) ? json_decode($hotelOrder->data, true) : $hotelOrder->data;
                    @endphp
                    
                    @if(is_array($hotelData))
                        @foreach($hotelData as $booking)
                            <div class="card mb-4 shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                                <!-- Booking Header -->
                                <div class="card-header border-0" style="background: linear-gradient(90deg, #74b9ff 0%, #0984e3 100%); padding: 20px;">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h5 class="mb-1 fw-bold text-white">
                                                <i class="ri-hotel-line me-2"></i>{{ $booking['hotelDetails']['hotel_name'] ?? 'Hotel Bookings' }}
                                            </h5>
                                            <p class="mb-0 text-white opacity-75">Enquiry {{ $index + 1 }} • {{ ucfirst($booking['bookingType'] ?? 'Standard') }}</p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <div class="bg-white rounded-pill px-3 py-2 d-inline-block">
                                                <span class="text-success fw-bold fs-5">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body p-4" style="background-color: #f8f9fa;">
                                    <!-- Guest Information -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="bg-white rounded p-3 shadow-sm h-100">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-primary rounded-circle p-2 me-3">
                                                        <i class="ri-user-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Customer Details</h6>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Full Name</small>
                                                    <div class="fw-medium">{{ $booking['fullName'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Email Address</small>
                                                    <div class="fw-medium text-primary">{{ $booking['email'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="mb-0">
                                                    <small class="text-muted">Phone Number</small>
                                                    <div class="fw-medium">{{ $booking['countryCode'] ?? '' }} {{ $booking['phone'] ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-white rounded p-3 shadow-sm h-100">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-info rounded-circle p-2 me-3">
                                                        <i class="ri-map-pin-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Address</h6>
                                                </div>
                                                <div class="text-muted">
                                                    @if($booking['address1'] ?? false)
                                                        <div>{{ $booking['address1'] }}</div>
                                                    @endif
                                                    @if($booking['address2'] ?? false)
                                                        <div>{{ $booking['address2'] }}</div>
                                                    @endif
                                                    @if($booking['state'] ?? false)
                                                        <div>{{ $booking['state'] }} {{ $booking['zip'] ?? '' }}</div>
                                                    @endif
                                                    @if(!($booking['address1'] ?? false) && !($booking['address2'] ?? false) && !($booking['state'] ?? false))
                                                        <div class="text-muted">Address not provided</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Stay Information -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="bg-white rounded p-3 shadow-sm h-100">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-success rounded-circle p-2 me-3">
                                                        <i class="ri-calendar-check-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Stay Schedule</h6>
                                                </div>
                                                <div class="mb-3">
                                                    <small class="text-muted">Check-in Date</small>
                                                    <div class="fw-bold text-success fs-5">
                                                        @if(isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 0)
                                                            {{ \Carbon\Carbon::parse($booking['bookingDate'][0])->format('D, M d, Y') }}
                                                        @else
                                                            N/A
                                                        @endif
                                                    </div>
                                                    @if(isset($booking['hotelDetails']['checkInTime']))
                                                        <small class="text-primary fw-medium">{{ $booking['hotelDetails']['checkInTime'] }}</small>
                                                    @endif
                                                </div>
                                                <div class="mb-3">
                                                    <small class="text-muted">Check-out Date</small>
                                                    <div class="fw-bold text-danger fs-5">
                                                        @if(isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 1)
                                                            {{ \Carbon\Carbon::parse(end($booking['bookingDate']))->format('D, M d, Y') }}
                                                        @else
                                                            N/A
                                                        @endif
                                                    </div>
                                                    @if(isset($booking['hotelDetails']['checkOutTime']))
                                                        <small class="text-danger fw-medium">{{ $booking['hotelDetails']['checkOutTime'] }}</small>
                                                    @endif
                                                </div>
                                                <div>
                                                    <small class="text-muted">Total Nights</small>
                                                    <div>
                                                        @if(isset($booking['bookingDate']) && is_array($booking['bookingDate']) && count($booking['bookingDate']) > 1)
                                                            @php
                                                                $checkIn = \Carbon\Carbon::parse($booking['bookingDate'][0]);
                                                                $checkOut = \Carbon\Carbon::parse(end($booking['bookingDate']));
                                                                $nights = $checkIn->diffInDays($checkOut);
                                                            @endphp
                                                            <span class="badge bg-info px-3 py-2">{{ $nights }} Night{{ $nights > 1 ? 's' : '' }}</span>
                                                        @else
                                                            <span class="badge bg-secondary px-3 py-2">Duration TBD</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-white rounded p-3 shadow-sm h-100">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-info rounded-circle p-2 me-3">
                                                        <i class="ri-building-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Hotel Details</h6>
                                                </div>
                                                <div class="mb-3">
                                                    <small class="text-muted">Location</small>
                                                    <div class="fw-medium">{{ $booking['hotelDetails']['location'] ?? 'Location not specified' }}</div>
                                                </div>
                                                @if(isset($booking['hotelDetails']['cancellation_charge']) && !empty($booking['hotelDetails']['cancellation_charge']))
                                                <div class="mb-3">
                                                    <small class="text-muted">Cancellation Policy</small>
                                                    <div class="fw-medium text-warning">{{ $booking['hotelDetails']['cancellation_charge'] }}</div>
                                                </div>
                                                @endif
                                                @if(isset($booking['hotelDetails']['image']))
                                                    <div class="mt-3">
                                                        <img src="{{ $booking['hotelDetails']['image'] }}" 
                                                             alt="{{ $booking['hotelDetails']['hotel_name'] ?? 'Hotel' }}" 
                                                             class="img-fluid rounded shadow-sm" 
                                                             style="height: 120px; width: 100%; object-fit: cover;">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Room & Accommodation Details -->
                                    @if(isset($booking['rooms']) && is_array($booking['rooms']))
                                        <div class="bg-white rounded p-3 shadow-sm mb-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-warning rounded-circle p-2 me-3">
                                                    <i class="ri-door-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Room & Accommodation Details</h6>
                                            </div>
                                            
                                            @foreach($booking['rooms'] as $roomIndex => $room)
                                                <div class="card mb-3" style="border: 2px solid #e9ecef; border-radius: 12px; overflow: hidden;">
                                                    <div class="card-header border-0" style="background: linear-gradient(90deg, #74b9ff 0%, #0984e3 100%); padding: 15px;">
                                                        <div class="row align-items-center">
                                                            <div class="col-md-8">
                                                                <h6 class="fw-bold text-white mb-1">
                                                                    <i class="ri-door-line me-2"></i>Room {{ $roomIndex + 1 }}: {{ $room['room_type'] ?? 'Standard Room' }}
                                                                </h6>
                                                                <small class="text-white opacity-75">Room ID: {{ $room['room_id'] ?? 'N/A' }}</small>
                                                            </div>
                                                            <div class="col-md-4 text-end">
                                                                @if(isset($room['beds']) && is_array($room['beds']))
                                                                    @php $totalRoomPrice = collect($room['beds'])->sum('price'); @endphp
                                                                    <div class="bg-white rounded-pill px-3 py-2 d-inline-block">
                                                                        <span class="text-success fw-bold fs-5">SGD {{ number_format($totalRoomPrice, 2) }}</span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="card-body" style="background-color: #f8f9fa;">
                                                        @if(isset($room['beds']) && is_array($room['beds']))
                                                            @foreach($room['beds'] as $bedIndex => $bed)
                                                                <div class="bg-white rounded p-3 mb-3 shadow-sm">
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="d-flex align-items-center mb-3">
                                                                                <div class="bg-primary rounded-circle p-2 me-3">
                                                                                    <i class="ri-hotel-bed-line text-white"></i>
                                                                                </div>
                                                                                <div>
                                                                                    <h6 class="fw-bold text-dark mb-0">{{ $bed['bed_type'] ?? 'Bed' }}</h6>
                                                                                    <small class="text-muted">Bed ID: {{ $bed['bed_id'] ?? 'N/A' }}</small>
                                                                                </div>
                                                                            </div>
                                                                            <div class="row">
                                                                                <div class="col-6 mb-2">
                                                                                    <small class="text-muted">Guests</small>
                                                                                    <div class="fw-medium text-primary">{{ $bed['head_count'] ?? 0 }} people</div>
                                                                                </div>
                                                                                <div class="col-6 mb-2">
                                                                                    <small class="text-muted">Max Capacity</small>
                                                                                    <div class="fw-medium">{{ $bed['max_occupancy'] ?? 'N/A' }}</div>
                                                                                </div>
                                                                                <div class="col-12">
                                                                                    <small class="text-muted">Room Price</small>
                                                                                    <div class="fs-5 fw-bold text-success">SGD {{ number_format($bed['price'] ?? 0, 2) }}</div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        
                                                                        <div class="col-md-6">
                                                                            @if(isset($bed['selectedMeals']) && is_array($bed['selectedMeals']))
                                                                                <div class="mb-3">
                                                                                    <div class="d-flex align-items-center mb-2">
                                                                                        <div class="bg-warning rounded-circle p-2 me-2">
                                                                                            <i class="ri-restaurant-line text-white"></i>
                                                                                        </div>
                                                                                        <h6 class="fw-bold mb-0 text-dark">Selected Meals</h6>
                                                                                    </div>
                                                                                    @foreach($bed['selectedMeals'] as $mealKey => $meal)
                                                                                        <div class="bg-light rounded p-2 mb-2">
                                                                                            <div class="d-flex justify-content-between align-items-center">
                                                                                                <span class="fw-medium">{{ $meal['type'] ?? 'Meal Plan' }}</span>
                                                                                                <span class="badge bg-success">SGD {{ number_format($meal['price'] ?? 0, 2) }}</span>
                                                                                            </div>
                                                                                        </div>
                                                                                    @endforeach
                                                                                    @php $totalMealPrice = collect($bed['selectedMeals'])->sum('price'); @endphp
                                                                                    <div class="border-top pt-2 mt-2">
                                                                                        <div class="d-flex justify-content-between">
                                                                                            <strong>Meal Total:</strong>
                                                                                            <strong class="text-warning">SGD {{ number_format($totalMealPrice, 2) }}</strong>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                            
                                                                            @if(isset($bed['mealTypes']) && is_array($bed['mealTypes']))
                                                                                <div>
                                                                                    <small class="text-muted fw-bold d-block mb-2">Available Meal Options:</small>
                                                                                    <div class="d-flex flex-wrap gap-1">
                                                                                        @foreach($bed['mealTypes'] as $mealType)
                                                                                            <span class="badge bg-outline-secondary">{{ $mealType }}</span>
                                                                                        @endforeach
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                            
                                            <!-- Booking Summary -->
                                            <div class="bg-primary bg-opacity-10 rounded p-3">
                                                <div class="row align-items-center">
                                                    <div class="col-md-8">
                                                        <h6 class="fw-bold text-dark mb-1">Hotel Booking Summary</h6>
                                                        <small class="text-muted">{{ count($booking['rooms']) }} room(s) • {{ ucfirst($booking['bookingType'] ?? 'Standard') }} booking</small>
                                                    </div>
                                                    <div class="col-md-4 text-end">
                                                        <small class="text-muted d-block">Total Amount</small>
                                                        <div class="fs-3 fw-bold text-white">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Special Requests -->
                                    @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                                        <div class="bg-white rounded p-3 shadow-sm">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-purple rounded-circle p-2 me-3" style="background-color: #6f42c1;">
                                                    <i class="ri-message-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Special Requests</h6>
                                            </div>
                                            <div class="bg-light rounded p-3">
                                                <p class="mb-0 text-dark">{{ $booking['specialRequests'] }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <div class="bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <i class="ri-hotel-line ri-48px text-muted"></i>
                        </div>
                        <h4 class="text-dark mb-3">No Hotel Data Available</h4>
                        <p class="text-muted mb-4">Hotel services are booked but detailed information is not available at this moment.</p>
                        <div class="alert alert-primary border-0 shadow-sm" style="max-width: 400px; margin: 0 auto;">
                            <div class="d-flex align-items-center">
                                <i class="ri-information-line text-primary me-2"></i>
                                <div>
                                    <strong>Note:</strong> {{ $svc['hotel'] }} hotel service(s) are associated with this tour.
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="modal-footer border-0 p-4" style="background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);">
                <button type="button" class="btn btn-outline-secondary px-4 py-2" onclick="closeServiceModal('hotel', {{ $tour->tour_id }})" style="border-radius: 25px;">
                    <i class="ri-close-line me-2"></i>Close
                </button>
                {{-- <button type="button" class="btn btn-primary px-4 py-2 ms-2" style="border-radius: 25px;">
                    <i class="ri-download-line me-2"></i>Download Details
                </button> --}}
            </div>
        </div>
    </div>
 </div>
@endif

 <!-- Attraction Details Modal -->
 @if(isset($svc['attraction']) && $svc['attraction'] > 0)
  <div class="modal fade" id="attractionDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="attractionDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
     <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
         <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
             <div class="modal-header p-0 border-0 position-relative" style="height: 180px; background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%);">
                 <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-between p-4">
                     <div class="text-white">
                         <h3 class="mb-1 fw-bold">
                             <i class="ri-building-2-line me-2"></i>Attraction Enquiries    
                         </h3>
                         <p class="mb-0 opacity-75">Tour #{{ $tour->tour_id }} Attraction Details</p>
                     </div>
                     <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('attraction', {{ $tour->tour_id }})" aria-label="Close" style="filter: brightness(0) invert(1); font-size: 1.2rem;"></button>
                 </div>
             </div>
             <div class="modal-body p-4" style="background-color: #f8f9fa;">
                 @if(isset($serviceData['attraction']) && count($serviceData['attraction']) > 0)
                     @foreach($serviceData['attraction'] as $index => $attractionOrder)
                     @php
                         $attractionData = is_string($attractionOrder->data) ? json_decode($attractionOrder->data, true) : $attractionOrder->data;
                     @endphp
                     
                     @if(is_array($attractionData))
                         @foreach($attractionData as $booking)
                             <div class="card mb-4 shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                                 <div class="card-header border-0" style="background: linear-gradient(90deg, #fd9853 0%, #fe7854 100%); padding: 20px;">
                                     <div class="row align-items-center">
                                         <div class="col-md-8">
                                             <h5 class="mb-1 fw-bold text-white">
                                                 <i class="ri-building-2-line me-2"></i>{{ $booking['AttractionName'] ?? 'Attraction Booking' }}
                                             </h5>
                                             <p class="mb-0 text-white opacity-75">{{ $booking['ticketName'] ?? 'Standard Ticket' }} • Enquiry {{ $index + 1 }}</p>
                                         </div>
                                         <div class="col-md-4 text-end">
                                             <div class="bg-white rounded-pill px-3 py-2 d-inline-block">
                                                 <span class="text-success fw-bold fs-5">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</span>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                                 
                                 <div class="card-body p-4" style="background-color: #f8f9fa;">
                                     <!-- Guest Information -->
                                     <div class="row mb-4">
                                         <div class="col-md-6">
                                             <div class="bg-white rounded p-3 shadow-sm h-100">
                                                 <div class="d-flex align-items-center mb-3">
                                                     <div class="bg-primary rounded-circle p-2 me-3">
                                                         <i class="ri-user-line text-white"></i>
                                                     </div>
                                                     <h6 class="fw-bold mb-0 text-dark">Customer Details</h6>
                                                 </div>
                                                 <div class="mb-2">
                                                     <small class="text-muted">Full Name</small>
                                                     <div class="fw-medium">{{ $booking['fullName'] ?? 'N/A' }}</div>
                                                 </div>
                                                 <div class="mb-2">
                                                     <small class="text-muted">Email Address</small>
                                                     <div class="fw-medium text-primary">{{ $booking['email'] ?? 'N/A' }}</div>
                                                 </div>
                                                 <div class="mb-0">
                                                     <small class="text-muted">Phone Number</small>
                                                     <div class="fw-medium">{{ $booking['countryCode'] ?? '' }} {{ $booking['phone'] ?? 'N/A' }}</div>
                                                 </div>
                                             </div>
                                         </div>
                                         <div class="col-md-6">
                                             <div class="bg-white rounded p-3 shadow-sm h-100">
                                                 <div class="d-flex align-items-center mb-3">
                                                     <div class="bg-info rounded-circle p-2 me-3">
                                                         <i class="ri-map-pin-line text-white"></i>
                                                     </div>
                                                     <h6 class="fw-bold mb-0 text-dark">Address</h6>
                                                 </div>
                                                 <div class="text-muted">
                                                     @if($booking['address1'] ?? false)
                                                         <div>{{ $booking['address1'] }}</div>
                                                     @endif
                                                     @if($booking['address2'] ?? false)
                                                         <div>{{ $booking['address2'] }}</div>
                                                     @endif
                                                     @if($booking['state'] ?? false)
                                                         <div>{{ $booking['state'] }} {{ $booking['zip'] ?? '' }}</div>
                                                     @endif
                                                     @if(!($booking['address1'] ?? false) && !($booking['address2'] ?? false) && !($booking['state'] ?? false))
                                                         <div class="text-muted">Address not provided</div>
                                                     @endif
                                                 </div>
                                             </div>
                                         </div>
                                     </div>

                                     <!-- Visit & Booking Information -->
                                     <div class="row mb-4">
                                         <div class="col-md-6">
                                             <div class="bg-white rounded p-3 shadow-sm h-100">
                                                 <div class="d-flex align-items-center mb-3">
                                                     <div class="bg-warning rounded-circle p-2 me-3">
                                                         <i class="ri-calendar-line text-white"></i>
                                                     </div>
                                                     <h6 class="fw-bold mb-0 text-dark">Visit Schedule</h6>
                                                 </div>
                                                 <div class="mb-3">
                                                     <small class="text-muted">Visit Date</small>
                                                     <div class="fw-bold text-success fs-5">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('D, M d, Y') }}</div>
                                                 </div>
                                                 <div class="mb-3">
                                                     <small class="text-muted">Visit Time</small>
                                                     <div class="fw-medium text-primary">{{ $booking['visitTime'] ?? 'Full Day Access' }}</div>
                                                 </div>
                                                 <div>
                                                     <small class="text-muted">Selection Type</small>
                                                     <div><span class="badge bg-info px-3 py-2">{{ ucfirst($booking['Selection'] ?? 'Standard') }}</span></div>
                                                 </div>
                                             </div>
                                         </div>
                                         <div class="col-md-6">
                                             <div class="bg-white rounded p-3 shadow-sm h-100">
                                                 <div class="d-flex align-items-center mb-3">
                                                     <div class="bg-info rounded-circle p-2 me-3">
                                                         <i class="ri-group-line text-white"></i>
                                                     </div>
                                                     <h6 class="fw-bold mb-0 text-dark">Guest Information</h6>
                                                 </div>
                                                 <div class="row">
                                                     <div class="col-4 text-center mb-3">
                                                         <div class="bg-light rounded p-2">
                                                             <div class="fs-4 fw-bold text-success">{{ $booking['adultCount'] ?? 0 }}</div>
                                                             <small class="text-muted">Adults</small>
                                                         </div>
                                                     </div>
                                                     <div class="col-4 text-center mb-3">
                                                         <div class="bg-light rounded p-2">
                                                             <div class="fs-4 fw-bold text-warning">{{ $booking['childCount'] ?? 0 }}</div>
                                                             <small class="text-muted">Children</small>
                                                         </div>
                                                     </div>
                                                     <div class="col-4 text-center mb-3">
                                                         <div class="bg-light rounded p-2">
                                                             <div class="fs-4 fw-bold text-info">{{ $booking['seniorCount'] ?? 0 }}</div>
                                                             <small class="text-muted">Seniors</small>
                                                         </div>
                                                     </div>
                                                 </div>
                                                 <div class="text-center">
                                                     <span class="badge bg-primary px-3 py-2">
                                                         Total: {{ ($booking['adultCount'] ?? 0) + ($booking['childCount'] ?? 0) + ($booking['seniorCount'] ?? 0) }} Guests
                                                     </span>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>

                                     <!-- Attraction Details -->
                                     <div class="bg-white rounded p-3 shadow-sm mb-4">
                                         <div class="d-flex align-items-center mb-3">
                                             <div class="bg-success rounded-circle p-2 me-3">
                                                 <i class="ri-building-2-line text-white"></i>
                                             </div>
                                             <h6 class="fw-bold mb-0 text-dark">Attraction Details</h6>
                                         </div>
                                         <div class="row">
                                             <div class="col-md-4 mb-3">
                                                 <small class="text-muted">Attraction ID</small>
                                                 <div class="fw-medium">{{ $booking['AttractionId'] ?? 'N/A' }}</div>
                                             </div>
                                             <div class="col-md-4 mb-3">
                                                 <small class="text-muted">Ticket ID</small>
                                                 <div class="fw-medium">{{ $booking['ticketId'] ?? 'N/A' }}</div>
                                             </div>
                                             <div class="col-md-4 mb-3">
                                                 <small class="text-muted">NRI Status</small>
                                                 <span class="badge bg-info">{{ ucfirst($booking['nri'] ?? 'N/A') }}</span>
                                             </div>
                                         </div>
                                     </div>

                                     <!-- Ticket & Pricing Details -->
                                     @if(isset($booking['ticket_details']))
                                     <div class="bg-white rounded p-3 shadow-sm mb-4">
                                         <div class="d-flex align-items-center mb-3">
                                             <div class="bg-success rounded-circle p-2 me-3">
                                                 <i class="ri-ticket-line text-white"></i>
                                             </div>
                                             <h6 class="fw-bold mb-0 text-dark">Ticket & Pricing Information</h6>
                                         </div>
                                         
                                         <!-- Pricing Cards -->
                                         <div class="row mb-3">
                                             <div class="col-md-4 mb-3">
                                                 <div class="border rounded-3 p-3 text-center" style="border-color: #28a745; background: linear-gradient(135deg, #d4edda, #f8f9fa);">
                                                     <div class="text-success mb-2">
                                                         <i class="ri-user-line ri-24px"></i>
                                                     </div>
                                                     <h6 class="fw-bold text-success mb-1">Adult Ticket</h6>
                                                     <div class="fs-4 fw-bold text-success">SGD {{ number_format($booking['ticket_details']['adult_price'] ?? 0, 2) }}</div>
                                                     <small class="text-muted">Per person</small>
                                                 </div>
                                             </div>
                                             <div class="col-md-4 mb-3">
                                                 <div class="border rounded-3 p-3 text-center" style="border-color: #ffc107; background: linear-gradient(135deg, #fff3cd, #f8f9fa);">
                                                     <div class="text-warning mb-2">
                                                         <i class="ri-user-smile-line ri-24px"></i>
                                                     </div>
                                                     <h6 class="fw-bold text-warning mb-1">Child Ticket</h6>
                                                     <div class="fs-4 fw-bold text-warning">SGD {{ number_format($booking['ticket_details']['child_price'] ?? 0, 2) }}</div>
                                                     <small class="text-muted">Per child</small>
                                                 </div>
                                             </div>
                                             <div class="col-md-4 mb-3">
                                                 <div class="border rounded-3 p-3 text-center" style="border-color: #17a2b8; background: linear-gradient(135deg, #d1ecf1, #f8f9fa);">
                                                     <div class="text-info mb-2">
                                                         <i class="ri-user-star-line ri-24px"></i>
                                                     </div>
                                                     <h6 class="fw-bold text-info mb-1">Senior Ticket</h6>
                                                     <div class="fs-4 fw-bold text-info">SGD {{ number_format($booking['ticket_details']['senior_price'] ?? 0, 2) }}</div>
                                                     <small class="text-muted">Per senior</small>
                                                 </div>
                                             </div>
                                         </div>

                                         <!-- Booking Summary -->
                                         <div class="bg-light rounded p-3 mb-3">
                                             <div class="row align-items-center">
                                                 <div class="col-md-8">
                                                     <h6 class="fw-bold text-dark mb-2">Booking Summary</h6>
                                                     <div class="d-flex gap-3">
                                                         @if($booking['adultCount'] ?? 0 > 0)
                                                             <span class="badge bg-success">{{ $booking['adultCount'] }} × SGD {{ number_format($booking['ticket_details']['adult_price'] ?? 0, 2) }}</span>
                                                         @endif
                                                         @if($booking['childCount'] ?? 0 > 0)
                                                             <span class="badge bg-warning">{{ $booking['childCount'] }} × SGD {{ number_format($booking['ticket_details']['child_price'] ?? 0, 2) }}</span>
                                                         @endif
                                                         @if($booking['seniorCount'] ?? 0 > 0)
                                                             <span class="badge bg-info">{{ $booking['seniorCount'] }} × SGD {{ number_format($booking['ticket_details']['senior_price'] ?? 0, 2) }}</span>
                                                         @endif
                                                     </div>
                                                 </div>
                                                 <div class="col-md-4 text-end">
                                                     <small class="text-muted d-block">Total Amount</small>
                                                     <div class="fs-3 fw-bold text-primary">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</div>
                                                 </div>
                                             </div>
                                         </div>

                                         @if(isset($booking['ticket_details']['description']) && !empty($booking['ticket_details']['description']))
                                         <!-- Ticket Description -->
                                         <div class="border-start border-3 border-primary ps-3">
                                             <h6 class="fw-bold text-dark mb-2">Ticket Information</h6>
                                             <div class="text-muted">{!! $booking['ticket_details']['description'] !!}</div>
                                         </div>
                                         @endif
                                     </div>
                                     @endif

                                     <!-- Special Requests -->
                                     @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                                         <div class="bg-white rounded p-3 shadow-sm">
                                             <div class="d-flex align-items-center mb-3">
                                                 <div class="bg-purple rounded-circle p-2 me-3" style="background-color: #6f42c1;">
                                                     <i class="ri-message-line text-white"></i>
                                                 </div>
                                                 <h6 class="fw-bold mb-0 text-dark">Special Requests</h6>
                                             </div>
                                             <div class="bg-light rounded p-3">
                                                 <p class="mb-0 text-dark">{{ $booking['specialRequests'] }}</p>
                                             </div>
                                         </div>
                                     @endif
                                 </div>
                             </div>
                         @endforeach
                     @endif
                     @endforeach
                 @else
                     <div class="text-center py-5">
                         <div class="bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                             <i class="ri-building-2-line ri-48px text-muted"></i>
                         </div>
                         <h4 class="text-dark mb-3">No Attraction Data Available</h4>
                         <p class="text-muted mb-4">Attraction services are booked but detailed information is not available.</p>
                     </div>
                 @endif
             </div>
             <div class="modal-footer border-0 p-4" style="background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);">
                 <button type="button" class="btn btn-outline-secondary px-4 py-2" onclick="closeServiceModal('attraction', {{ $tour->tour_id }})" style="border-radius: 25px;">
                     <i class="ri-close-line me-2"></i>Close
                 </button>
                 {{-- <button type="button" class="btn btn-primary px-4 py-2 ms-2" style="border-radius: 25px;">
                     <i class="ri-download-line me-2"></i>Download Details
                 </button> --}}
             </div>
         </div>
     </div>
  </div>
 @endif

 <!-- Restaurant Details Modal -->
 @if(isset($svc['restaurant']) && $svc['restaurant'] > 0)
  <div class="modal fade" id="restaurantDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="restaurantDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
     <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
         <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
             <div class="modal-header p-0 border-0 position-relative" style="height: 180px; background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%);">
                 <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-between p-4">
                     <div class="text-white">
                         <h3 class="mb-1 fw-bold">
                             <i class="ri-restaurant-2-line me-2"></i>Restaurant Enquiries
                         </h3>
                         <p class="mb-0 opacity-75">Tour #{{ $tour->tour_id }} Restaurant Details</p>
                     </div>
                     <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('restaurant', {{ $tour->tour_id }})" aria-label="Close" style="filter: brightness(0) invert(1); font-size: 1.2rem;"></button>
                 </div>
             </div>
             <div class="modal-body p-4" style="background-color: #f8f9fa;">
                 @if(isset($serviceData['restaurant']) && count($serviceData['restaurant']) > 0)
                     @foreach($serviceData['restaurant'] as $index => $restaurantOrder)
                     @php
                         $restaurantData = is_string($restaurantOrder->data) ? json_decode($restaurantOrder->data, true) : $restaurantOrder->data;
                     @endphp
                     
                     @if(is_array($restaurantData))
                         @foreach($restaurantData as $booking)
                             <div class="card mb-4 shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                                 <div class="card-header border-0" style="background: linear-gradient(90deg, #fd79a8 0%, #fdcb6e 100%); padding: 20px;">
                                     <div class="row align-items-center">
                                         <div class="col-md-8">
                                             <h5 class="mb-1 fw-bold text-white">
                                                 <i class="ri-restaurant-2-line me-2"></i>{{ $booking['restaurantName'] ?? 'Restaurant Booking' }}
                                             </h5>
                                             <p class="mb-0 text-white opacity-75">{{ ucfirst($booking['mealType'] ?? 'Meal') }} • {{ $booking['mealSpecificType'] ?? 'Standard' }}</p>
                                         </div>
                                         <div class="col-md-4 text-end">
                                             <div class="bg-white rounded-pill px-3 py-2 d-inline-block">
                                                 <span class="text-success fw-bold fs-5">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</span>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                                 
                                 <div class="card-body p-4" style="background-color: #f8f9fa;">
                                     <!-- Guest Information -->
                                     <div class="row mb-4">
                                         <div class="col-md-6">
                                             <div class="bg-white rounded p-3 shadow-sm h-100">
                                                 <div class="d-flex align-items-center mb-3">
                                                     <div class="bg-primary rounded-circle p-2 me-3">
                                                         <i class="ri-user-line text-white"></i>
                                                     </div>
                                                     <h6 class="fw-bold mb-0 text-dark">Customer Details</h6>
                                                 </div>
                                                 <div class="mb-2">
                                                     <small class="text-muted">Full Name</small>
                                                     <div class="fw-medium">{{ $booking['fullName'] ?? 'N/A' }}</div>
                                                 </div>
                                                 <div class="mb-2">
                                                     <small class="text-muted">Email Address</small>
                                                     <div class="fw-medium text-primary">{{ $booking['email'] ?? 'N/A' }}</div>
                                                 </div>
                                                 <div class="mb-0">
                                                     <small class="text-muted">Phone Number</small>
                                                     <div class="fw-medium">{{ $booking['countryCode'] ?? '' }} {{ $booking['phone'] ?? 'N/A' }}</div>
                                                 </div>
                                             </div>
                                         </div>
                                         <div class="col-md-6">
                                             <div class="bg-white rounded p-3 shadow-sm h-100">
                                                 <div class="d-flex align-items-center mb-3">
                                                     <div class="bg-warning rounded-circle p-2 me-3">
                                                         <i class="ri-calendar-line text-white"></i>
                                                     </div>
                                                     <h6 class="fw-bold mb-0 text-dark">Reservation Details</h6>
                                                 </div>
                                                 <div class="mb-3">
                                                     <small class="text-muted">Dining Date</small>
                                                     <div class="fw-bold text-success fs-5">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('D, M d, Y') }}</div>
                                                 </div>
                                                 <div class="mb-3">
                                                     <small class="text-muted">Dining Time</small>
                                                     <div class="fw-medium text-primary">{{ $booking['visitTime'] ?? 'Time to be confirmed' }}</div>
                                                 </div>
                                                 <div class="row">
                                                     <div class="col-6 text-center">
                                                         <div class="bg-light rounded p-2">
                                                             <div class="fs-4 fw-bold text-success">{{ $booking['adultCount'] ?? 0 }}</div>
                                                             <small class="text-muted">Adults</small>
                                                         </div>
                                                     </div>
                                                     <div class="col-6 text-center">
                                                         <div class="bg-light rounded p-2">
                                                             <div class="fs-4 fw-bold text-warning">{{ $booking['childCount'] ?? 0 }}</div>
                                                             <small class="text-muted">Children</small>
                                                         </div>
                                                     </div>
                                                 </div>
                                                 <div class="text-center mt-2">
                                                     <span class="badge bg-primary px-3 py-2">
                                                         Party of {{ ($booking['adultCount'] ?? 0) + ($booking['childCount'] ?? 0) }}
                                                     </span>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>

                                     <!-- Restaurant Overview -->
                                     <div class="bg-white rounded p-3 shadow-sm mb-4">
                                         <div class="d-flex align-items-center mb-3">
                                             <div class="bg-info rounded-circle p-2 me-3">
                                                 <i class="ri-information-line text-white"></i>
                                             </div>
                                             <h6 class="fw-bold mb-0 text-dark">Restaurant Overview</h6>
                                         </div>
                                         <div class="row">
                                             <div class="col-md-4 mb-3">
                                                 <small class="text-muted">Meal Price</small>
                                                 <div class="fw-medium text-success">SGD {{ number_format($booking['mealPrice'] ?? 0, 2) }}</div>
                                             </div>
                                             <div class="col-md-4 mb-3">
                                                 <small class="text-muted">Transport Price</small>
                                                 <div class="fw-medium text-info">SGD {{ number_format($booking['transportPrice'] ?? 0, 2) }}</div>
                                             </div>
                                             {{-- <div class="col-md-4 mb-3">
                                                 <small class="text-muted">DMC ID</small>
                                                 <span class="badge bg-secondary">{{ $booking['dmc_id'] ?? 'N/A' }}</span>
                                             </div>
                                             <div class="col-md-6 mb-3">
                                                 <small class="text-muted">Price Types</small>
                                                 <div>
                                                     @if(isset($booking['priceTypes']) && is_array($booking['priceTypes']))
                                                         @foreach($booking['priceTypes'] as $priceType)
                                                             <span class="badge bg-outline-primary me-1">{{ strtoupper($priceType) }}</span>
                                                         @endforeach
                                                     @else
                                                         <span class="text-muted">N/A</span>
                                                     @endif
                                                 </div>
                                             </div> --}}
                                             <div class="col-md-6 mb-3">
                                                 <small class="text-muted">Transport</small>
                                                 <div class="fw-medium">{{ $booking['transport'] ?? 'Not included' }}</div>
                                             </div>
                                         </div>
                                     </div>

                                     <!-- Menu & Meal Details -->
                                     @if(isset($booking['MealDescription']) && is_array($booking['MealDescription']))
                                     <div class="bg-white rounded p-3 shadow-sm mb-4">
                                         <div class="d-flex align-items-center mb-3">
                                             <div class="bg-success rounded-circle p-2 me-3">
                                                 <i class="ri-restaurant-line text-white"></i>
                                             </div>
                                             <h6 class="fw-bold mb-0 text-dark">Menu Selection & Pricing</h6>
                                         </div>
                                         
                                         @foreach($booking['MealDescription'] as $index => $meal)
                                             <div class="card mb-4 shadow-lg" style="border: none; border-radius: 15px; overflow: hidden;">
                                                 <!-- Item Header -->
                                                 <div class="card-header border-0 p-0 position-relative" style="background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%); height: 120px;">
                                                     <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-between p-4">
                                                         <div class="text-white">
                                                             <h5 class="mb-2 fw-bold">
                                                                 <i class="ri-restaurant-2-line me-2"></i>{{ 'Menu Item' }}
                                                             </h5>
                                                             <div class="d-flex flex-wrap gap-2">
                                                                 <span class="badge bg-opacity-20 text-white border border-white border-opacity-50 px-3 py-1">
                                                                     <i class="ri-wine-glass-line me-1"></i>{{ $meal['category'] ?? 'Category' }}
                                                                 </span>
                                                                 <span class="badge bg-opacity-20 text-white border border-white border-opacity-50 px-3 py-1">
                                                                     <i class="ri-leaf-line me-1"></i>{{ $meal['item_type'] ?? 'Type' }}
                                                                 </span>
                                                             </div>
                                                         </div>
                                                         <div class="text-end">
                                                             <div class="bg-white bg-opacity-95 rounded-3 px-4 py-3 shadow">
                                                                 <small class="text-muted d-block mb-1">Unit Price</small>
                                                                 <div class="fs-4 fw-bold text-success">SGD {{ number_format($meal['price'] ?? 0, 2) }}</div>
                                                             </div>
                                                         </div>
                                                     </div>
                                                 </div>
                                                 
                                                 <!-- Item Details -->
                                                 <div class="card-body p-4" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);">
                                                     <div class="row align-items-center mb-4">
                                                         <!-- Quantity Section -->
                                                         <div class="col-md-6">
                                                             <div class="text-center p-4 rounded-3" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border: 2px solid #2196f3;">
                                                                 <div class="d-flex align-items-center justify-content-center mb-2">
                                                                     <i class="ri-shopping-basket-line text-primary ri-24px me-2"></i>
                                                                     <h6 class="fw-bold text-primary mb-0">Quantity Ordered</h6>
                                                                 </div>
                                                                 <div class="fs-1 fw-bold text-primary mb-1">{{ $meal['quantity'] ?? 1 }}</div>
                                                                 <small class="text-muted">{{ ($meal['quantity'] ?? 1) == 1 ? 'item' : 'items' }}</small>
                                                             </div>
                                                         </div>
                                                         
                                                         <!-- Item Info -->
                                                         <div class="col-md-6">
                                                             <div class="bg-white rounded-3 p-4 shadow-sm border">
                                                                 <div class="d-flex align-items-center mb-3">
                                                                     <div class="bg-warning rounded-circle p-2 me-3">
                                                                         <i class="ri-information-line text-white"></i>
                                                                     </div>
                                                                     <h6 class="fw-bold mb-0 text-dark">Item Details</h6>
                                                                 </div>
                                                                 <div class="row">
                                                                     {{-- <div class="col-6 mb-2">
                                                                         <small class="text-muted">Item ID</small>
                                                                         <div class="fw-medium">#{{ $meal['meal_id'] ?? 'N/A' }}</div>
                                                                     </div> --}}
                                                                     <div class="col-6 mb-2">
                                                                         <small class="text-muted">Category</small>
                                                                         <div class="fw-medium">{{ $meal['category'] ?? 'N/A' }}</div>
                                                                     </div>
                                                                     <div class="col-12">
                                                                         <small class="text-muted">Dietary Type</small>
                                                                         <div>
                                                                             <span class="badge bg-success">{{ $meal['item_type'] ?? 'Standard' }}</span>
                                                                         </div>
                                                                     </div>
                                                                 </div>
                                                             </div>
                                                         </div>
                                                     </div>

                                                     <!-- Price Calculation -->
                                                     <div class="bg-gradient-light rounded-3 p-4 border border-primary border-opacity-25">
                                                         <div class="row align-items-center">
                                                             <div class="col-md-7">
                                                                 <div class="d-flex align-items-center mb-3">
                                                                     <div class="bg-primary rounded-circle p-2 me-3">
                                                                         <i class="ri-calculator-line text-white"></i>
                                                                     </div>
                                                                     <h6 class="fw-bold mb-0 text-dark">Price Calculation</h6>
                                                                 </div>
                                                                 <div class="d-flex align-items-center gap-3">
                                                                     <div class="text-center">
                                                                         <div class="fs-5 fw-bold text-success">SGD {{ number_format($meal['price'] ?? 0, 2) }}</div>
                                                                         <small class="text-muted">per item</small>
                                                                     </div>
                                                                     <div class="text-primary fs-3">×</div>
                                                                     <div class="text-center">
                                                                         <div class="fs-5 fw-bold text-primary">{{ $meal['quantity'] ?? 1 }}</div>
                                                                         <small class="text-muted">{{ ($meal['quantity'] ?? 1) == 1 ? 'item' : 'items' }}</small>
                                                                     </div>
                                                                     <div class="text-primary fs-3">=</div>
                                                                 </div>
                                                             </div>
                                                             <div class="col-md-5 text-end">
                                                                 <div class="bg-white rounded-3 p-4 shadow border border-success border-opacity-50">
                                                                     <small class="text-muted d-block mb-2">Item Subtotal</small>
                                                                     <div class="fs-2 fw-bold text-success">
                                                                         SGD {{ number_format(($meal['price'] ?? 0) * ($meal['quantity'] ?? 1), 2) }}
                                                                     </div>
                                                                 </div>
                                                             </div>
                                                         </div>
                                                     </div>
                                                 </div>
                                             </div>
                                         @endforeach
                                         
                                         <!-- Total Summary -->
                                         <div class="card shadow-lg mt-4" style="border: none; border-radius: 15px; overflow: hidden;">
                                             <div class="card-header border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px;">
                                                 <div class="row align-items-center">
                                                     <div class="col-md-8">
                                                         <h5 class="mb-1 fw-bold text-white">
                                                             <i class="ri-receipt-line me-2"></i>Order Summary
                                                         </h5>
                                                         <p class="mb-0 text-white opacity-75">
                                                             {{ count($booking['MealDescription']) }} item(s) • {{ $booking['mealType'] ?? 'Meal' }} • {{ $booking['mealSpecificType'] ?? 'Menu' }}
                                                         </p>
                                                     </div>
                                                     <div class="col-md-4 text-end">
                                                         <div class="bg-white bg-opacity-95 rounded-3 px-4 py-3 shadow">
                                                             <small class="text-muted d-block mb-1">Grand Total</small>
                                                             <div class="fs-2 fw-bold text-success">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</div>
                                                         </div>
                                                     </div>
                                                 </div>
                                             </div>
                                             <div class="card-body" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); padding: 20px;">
                                                 <div class="row">
                                                     <div class="col-md-4 text-center">
                                                         <div class="p-3">
                                                             <div class="bg-primary bg-opacity-10 rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                                 <i class="ri-restaurant-2-line text-primary ri-24px"></i>
                                                             </div>
                                                             <h6 class="fw-bold text-dark">{{ $booking['restaurantName'] ?? 'Restaurant' }}</h6>
                                                             <small class="text-muted">{{ $booking['mealType'] ?? 'Dining' }} Experience</small>
                                                         </div>
                                                     </div>
                                                     <div class="col-md-4 text-center">
                                                         <div class="p-3">
                                                             <div class="bg-warning bg-opacity-10 rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                                 <i class="ri-group-line text-warning ri-24px"></i>
                                                             </div>
                                                             <h6 class="fw-bold text-dark">{{ ($booking['adultCount'] ?? 0) + ($booking['childCount'] ?? 0) }} Guests</h6>
                                                             <small class="text-muted">{{ $booking['adultCount'] ?? 0 }} Adults, {{ $booking['childCount'] ?? 0 }} Children</small>
                                                         </div>
                                                     </div>
                                                     <div class="col-md-4 text-center">
                                                         <div class="p-3">
                                                             <div class="bg-success bg-opacity-10 rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                                 <i class="ri-calendar-check-line text-success ri-24px"></i>
                                                             </div>
                                                             <h6 class="fw-bold text-dark">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') }}</h6>
                                                             <small class="text-muted">{{ $booking['visitTime'] ?? 'Time TBD' }}</small>
                                                         </div>
                                                     </div>
                                                 </div>
                                                 
                                                 <!-- Payment Breakdown -->
                                                 <div class="mt-4 p-3 bg-light rounded-3">
                                                     <div class="row align-items-center">
                                                         <div class="col-md-6">
                                                             <div class="d-flex align-items-center">
                                                                 <i class="ri-money-dollar-circle-line text-success ri-24px me-2"></i>
                                                                 <div>
                                                                     <h6 class="fw-bold text-dark mb-0">Payment Summary</h6>
                                                                     <small class="text-muted">Meal Price: SGD {{ number_format($booking['mealPrice'] ?? 0, 2) }} | Transport: SGD {{ number_format($booking['transportPrice'] ?? 0, 2) }}</small>
                                                                 </div>
                                                             </div>
                                                         </div>
                                                         <div class="col-md-6 text-end">
                                                             <div class="fw-bold text-success fs-4">
                                                                 Total: SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}
                                                             </div>
                                                         </div>
                                                     </div>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                     @endif

                                     <!-- Special Requests -->
                                     @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                                         <div class="bg-white rounded p-3 shadow-sm">
                                             <div class="d-flex align-items-center mb-3">
                                                 <div class="bg-purple rounded-circle p-2 me-3" style="background-color: #6f42c1;">
                                                     <i class="ri-message-line text-white"></i>
                                                 </div>
                                                 <h6 class="fw-bold mb-0 text-dark">Special Requests</h6>
                                             </div>
                                             <div class="bg-light rounded p-3">
                                                 <p class="mb-0 text-dark">{{ $booking['specialRequests'] }}</p>
                                             </div>
                                         </div>
                                     @endif
                                 </div>
                             </div>
                         @endforeach
                     @endif
                     @endforeach
                 @else
                     <div class="text-center py-5">
                         <div class="bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                             <i class="ri-restaurant-2-line ri-48px text-muted"></i>
                         </div>
                         <h4 class="text-dark mb-3">No Restaurant Data Available</h4>
                         <p class="text-muted mb-4">Restaurant services are booked but detailed information is not available.</p>
                     </div>
                 @endif
             </div>
             <div class="modal-footer border-0 p-4" style="background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);">
                 <button type="button" class="btn btn-outline-secondary px-4 py-2" onclick="closeServiceModal('restaurant', {{ $tour->tour_id }})" style="border-radius: 25px;">
                     <i class="ri-close-line me-2"></i>Close
                 </button>
                 {{-- <button type="button" class="btn btn-primary px-4 py-2 ms-2" style="border-radius: 25px;">
                     <i class="ri-download-line me-2"></i>Download Details
                 </button> --}}
             </div>
         </div>
     </div>
  </div>
 @endif


<!-- Guide Details Modal -->
@if(isset($svc['guide']) && $svc['guide'] > 0)
 <div class="modal fade" id="guideDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="guideDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header p-0 border-0 position-relative" style="height: 180px; background: linear-gradient(135deg, #00cec9 0%, #55a3ff 100%);">
                <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-between p-4">
                    <div class="text-white">
                        <h3 class="mb-1 fw-bold">
                            <i class="ri-user-voice-line me-2"></i>Guide Enquiries
                        </h3>
                        <p class="mb-0 opacity-75">Tour #{{ $tour->tour_id }} Guide Details</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('guide', {{ $tour->tour_id }})" aria-label="Close" style="filter: brightness(0) invert(1); font-size: 1.2rem;"></button>
                </div>
            </div>
            <div class="modal-body p-4" style="background-color: #f8f9fa;">
                @if(isset($serviceData['guide']) && count($serviceData['guide']) > 0)
                    @foreach($serviceData['guide'] as $index => $guideOrder)
                    @php
                        $guideData = is_string($guideOrder->data) ? json_decode($guideOrder->data, true) : $guideOrder->data;
                    @endphp
                    
                    @if(is_array($guideData))
                        @foreach($guideData as $booking)
                            <div class="card mb-4 shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                                <div class="card-header border-0" style="background: linear-gradient(90deg, #00cec9 0%, #55a3ff 100%); padding: 20px;">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h5 class="mb-1 fw-bold text-white">
                                                <i class="ri-user-voice-line me-2"></i>{{ $booking['guide_name'] ?? 'Guide Booking' }}
                                            </h5>
                                            <p class="mb-0 text-white opacity-75">{{ $booking['hours'] ?? 'N/A' }} Hours</p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <div class="bg-white rounded-pill px-3 py-2 d-inline-block">
                                                <span class="text-success fw-bold fs-5">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body p-4" style="background-color: #f8f9fa;">
                                    <!-- Guide Information -->
                                    <div class="row mb-4">
                                        <div class="col-md-8">
                                            <div class="bg-white rounded p-3 shadow-sm h-100">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-success rounded-circle p-2 me-3">
                                                        <i class="ri-user-voice-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Guide Information</h6>
                                                </div>
                                                <div class="row">
                                                    <div class="col-6 mb-3">
                                                        <small class="text-muted">Guide Name</small>
                                                        <div class="fw-medium">{{ $booking['guide_name'] ?? 'N/A' }}</div>
                                                    </div>
                                                    {{-- <div class="col-6 mb-3">
                                                        <small class="text-muted">DMC ID</small>
                                                        <div class="fw-medium">{{ $booking['dmc_id'] ?? $booking['dmc_Id'] ?? 'N/A' }}</div>
                                                    </div> --}}
                                                    <div class="col-6 mb-3">
                                                        <small class="text-muted">Base Price</small>
                                                        <div class="fw-medium text-success">SGD {{ number_format($booking['basePrice'] ?? 0, 2) }}</div>
                                                    </div>
                                                    <div class="col-6 mb-3">
                                                        <small class="text-muted">Surcharge</small>
                                                        <div class="fw-medium text-warning">SGD {{ number_format($booking['surcharge'] ?? 0, 2) }}</div>
                                                    </div>
                                                    <div class="col-6 mb-3">
                                                        <small class="text-muted">Tax (%)</small>
                                                        <div class="fw-medium">{{ $booking['Tax'] ?? 0 }}%</div>
                                                    </div>
                                                    {{-- <div class="col-6 mb-3">
                                                        <small class="text-muted">Mode</small>
                                                        <span class="badge bg-info">{{ strtoupper($booking['Mode'] ?? 'N/A') }}</span>
                                                    </div> --}}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            @if(isset($booking['image']))
                                                <img src="{{ $booking['image'] }}" 
                                                     alt="{{ $booking['guide_name'] ?? 'Guide' }}" 
                                                     class="img-fluid rounded shadow-sm" 
                                                     style="height: 200px; width: 100%; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 200px;">
                                                    <i class="ri-user-voice-line ri-48px text-muted"></i>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Service Schedule & Details -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="bg-white rounded p-3 shadow-sm h-100">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-warning rounded-circle p-2 me-3">
                                                        <i class="ri-calendar-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Service Schedule</h6>
                                                </div>
                                                <div class="mb-3">
                                                    <small class="text-muted">Service Date</small>
                                                    <div class="fw-bold text-success fs-5">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('D, M d, Y') }}</div>
                                                </div>
                                                <div class="mb-3">
                                                    <small class="text-muted">Start Time</small>
                                                    <div class="fw-medium text-primary">{{ $booking['entrytime'] ?? 'To be confirmed' }}</div>
                                                </div>
                                                <div class="mb-3">
                                                    <small class="text-muted">Service Duration</small>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-info px-3 py-2 me-2">{{ $booking['hours'] ?? 'N/A' }} Hours</span>
                                                        <small class="text-muted">of guided service</small>
                                                    </div>
                                                </div>
                                                @if(($booking['Night_Start_Time'] ?? false) && ($booking['Night_End_Time'] ?? false))
                                                <div>
                                                    <small class="text-muted">Night Service Hours</small>
                                                    <div class="fw-medium text-warning">{{ $booking['Night_Start_Time'] }} - {{ $booking['Night_End_Time'] }}</div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-white rounded p-3 shadow-sm h-100">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-info rounded-circle p-2 me-3">
                                                        <i class="ri-group-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Group Information</h6>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-6 text-center">
                                                        <div class="bg-light rounded p-3">
                                                            <div class="fs-3 fw-bold text-success">{{ $booking['adults'] ?? 0 }}</div>
                                                            <small class="text-muted">Adults</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 text-center">
                                                        <div class="bg-light rounded p-3">
                                                            <div class="fs-3 fw-bold text-warning">{{ $booking['children'] ?? 0 }}</div>
                                                            <small class="text-muted">Children</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-center">
                                                    <span class="badge bg-primary px-3 py-2">
                                                        Group Size: {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} People
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Service & Pricing Details -->
                                    <div class="bg-white rounded p-3 shadow-sm mb-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-success rounded-circle p-2 me-3">
                                                <i class="ri-money-dollar-circle-line text-white"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark">Service Pricing Breakdown</h6>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <div class="text-center p-3 border rounded" style="border-color: #28a745;">
                                                    <small class="text-muted d-block">Base Price</small>
                                                    <div class="fs-5 fw-bold text-success">SGD {{ number_format($booking['basePrice'] ?? 0, 2) }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="text-center p-3 border rounded" style="border-color: #ffc107;">
                                                    <small class="text-muted d-block">Surcharge</small>
                                                    <div class="fs-5 fw-bold text-warning">SGD {{ number_format($booking['surcharge'] ?? 0, 2) }}</div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="text-center p-3 border rounded" style="border-color: #17a2b8;">
                                                    <small class="text-muted d-block">Tax ({{ $booking['Tax'] ?? 0 }}%)</small>
                                                    <div class="fs-5 fw-bold text-info">
                                                        SGD {{ number_format((($booking['basePrice'] ?? 0) + ($booking['surcharge'] ?? 0)) * (($booking['Tax'] ?? 0) / 100), 2) }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="text-center p-3 border rounded" style="border-color: #6f42c1; background: linear-gradient(135deg, #f8f9fa, #e9ecef);">
                                                    <small class="text-muted d-block">Total Amount</small>
                                                    <div class="fs-4 fw-bold text-primary">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Customer Information -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="bg-white rounded p-3 shadow-sm h-100">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-primary rounded-circle p-2 me-3">
                                                        <i class="ri-user-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Customer Details</h6>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Full Name</small>
                                                    <div class="fw-medium">{{ $booking['fullName'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Email</small>
                                                    <div class="fw-medium text-primary">{{ $booking['email'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="mb-0">
                                                    <small class="text-muted">Phone</small>
                                                    <div class="fw-medium">{{ $booking['countryCode'] ?? '' }} {{ $booking['phone'] ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-white rounded p-3 shadow-sm h-100">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-info rounded-circle p-2 me-3">
                                                        <i class="ri-map-pin-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Address & Location</h6>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Pickup Location</small>
                                                    <div class="fw-medium">{{ $booking['entrypickup'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="text-muted">
                                                    @if($booking['address1'] ?? false)
                                                        <div>{{ $booking['address1'] }}</div>
                                                    @endif
                                                    @if($booking['address2'] ?? false)
                                                        <div>{{ $booking['address2'] }}</div>
                                                    @endif
                                                    @if($booking['state'] ?? false)
                                                        <div>{{ $booking['state'] }} {{ $booking['zip'] ?? '' }}</div>
                                                    @endif
                                                    @if(!($booking['address1'] ?? false) && !($booking['address2'] ?? false) && !($booking['state'] ?? false))
                                                        <div class="text-muted">Address not provided</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Special Requests -->
                                    @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                                        <div class="bg-white rounded p-3 shadow-sm">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-purple rounded-circle p-2 me-3" style="background-color: #6f42c1;">
                                                    <i class="ri-message-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Special Requests</h6>
                                            </div>
                                            <div class="bg-light rounded p-3">
                                                <p class="mb-0 text-dark">{{ $booking['specialRequests'] }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <div class="bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <i class="ri-user-voice-line ri-48px text-muted"></i>
                        </div>
                        <h4 class="text-dark mb-3">No Guide Data Available</h4>
                        <p class="text-muted mb-4">Guide services are booked but detailed information is not available.</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer border-0 p-4" style="background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);">
                <button type="button" class="btn btn-outline-secondary px-4 py-2" onclick="closeServiceModal('guide', {{ $tour->tour_id }})" style="border-radius: 25px;">
                    <i class="ri-close-line me-2"></i>Close
                </button>
                {{-- <button type="button" class="btn btn-primary px-4 py-2 ms-2" style="border-radius: 25px;">
                    <i class="ri-download-line me-2"></i>Download Details
                </button> --}}
            </div>
        </div>
    </div>
 </div>
@endif

<!-- Entry Port (Arrival) Details Modal -->
@if(isset($svc['entry_port']) && $svc['entry_port'] > 0)
 <div class="modal fade" id="entry_portDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="entry_portDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header p-0 border-0 position-relative" style="height: 180px; background: linear-gradient(135deg, #00b894 0%, #55a3ff 100%);">
                <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-between p-4">
                    <div class="text-white">
                        <h3 class="mb-1 fw-bold">
                            <i class="ri-flight-land-line me-2"></i>Arrival Transfer
                        </h3>
                        <p class="mb-0 opacity-75">Tour #{{ $tour->tour_id }} Entry Port Details</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('entry_port', {{ $tour->tour_id }})" aria-label="Close" style="filter: brightness(0) invert(1); font-size: 1.2rem;"></button>
                </div>
            </div>
            <div class="modal-body p-4" style="background-color: #f8f9fa;">
                @if(isset($serviceData['entry_port']) && count($serviceData['entry_port']) > 0)
                    @foreach($serviceData['entry_port'] as $index => $entryOrder)
                    @php
                        $entryData = is_string($entryOrder->data) ? json_decode($entryOrder->data, true) : $entryOrder->data;
                    @endphp
                    
                    @if(is_array($entryData))
                        @foreach($entryData as $booking)
                            <div class="card mb-4 shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                                <div class="card-header border-0" style="background: linear-gradient(90deg, #00b894 0%, #55a3ff 100%); padding: 20px;">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h5 class="mb-1 fw-bold text-white">
                                                <i class="ri-car-line me-2"></i>{{ $booking['vehicles_name'] ?? 'Vehicle Transfer' }}
                                            </h5>
                                            <p class="mb-0 text-white opacity-75">{{ ucfirst($booking['type'] ?? 'Standard') }} Transfer • Enquiry {{ $index + 1 }}</p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <div class="bg-white rounded-pill px-3 py-2 d-inline-block">
                                                <span class="text-success fw-bold fs-5">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body p-4" style="background-color: #f8f9fa;">
                                    <!-- Transfer Schedule -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="bg-white rounded p-3 shadow-sm h-100">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-success rounded-circle p-2 me-3">
                                                        <i class="ri-calendar-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Transfer Schedule</h6>
                                                </div>
                                                <div class="mb-3">
                                                    <small class="text-muted">Arrival Date</small>
                                                    <div class="fw-bold text-success fs-5">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('D, M d, Y') }}</div>
                                                </div>
                                                <div class="mb-3">
                                                    <small class="text-muted">Pickup Time</small>
                                                    <div class="fw-medium text-primary">{{ $booking['entrytime'] ?? 'To be confirmed' }}</div>
                                                </div>
                                                <div>
                                                    <small class="text-muted">Transfer Type</small>
                                                    <div><span class="badge bg-info px-3 py-2">{{ ucfirst($booking['type'] ?? 'Standard') }}</span></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-white rounded p-3 shadow-sm h-100">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-info rounded-circle p-2 me-3">
                                                        <i class="ri-group-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Passenger Information</h6>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-6 text-center">
                                                        <div class="bg-light rounded p-3">
                                                            <div class="fs-3 fw-bold text-success">{{ $booking['adults'] ?? 0 }}</div>
                                                            <small class="text-muted">Adults</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 text-center">
                                                        <div class="bg-light rounded p-3">
                                                            <div class="fs-3 fw-bold text-warning">{{ $booking['children'] ?? 0 }}</div>
                                                            <small class="text-muted">Children</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-center">
                                                    <span class="badge bg-primary px-3 py-2">
                                                        Total: {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} Passengers
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Route Information -->
                                    <div class="bg-white rounded p-3 shadow-sm mb-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-primary rounded-circle p-2 me-3">
                                                <i class="ri-route-line text-white"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark">Route Details</h6>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="d-flex align-items-start">
                                                    <div class="bg-success rounded-circle p-2 me-3 mt-1">
                                                        <i class="ri-map-pin-line text-white"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted">Pickup Location</small>
                                                        <div class="fw-medium">{{ $booking['entrypickup'] ?? 'N/A' }}</div>
                                                        <small class="text-success">Origin</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="d-flex align-items-start">
                                                    <div class="bg-danger rounded-circle p-2 me-3 mt-1">
                                                        <i class="ri-flag-line text-white"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted">Drop-off Location</small>
                                                        <div class="fw-medium">{{ $booking['entrydropoff'] ?? 'N/A' }}</div>
                                                        <small class="text-danger">Destination</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">City</small>
                                                <div class="fw-medium">{{ $booking['city'] ?? 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Country</small>
                                                <div class="fw-medium">{{ $booking['country'] ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Vehicle Information -->
                                    <div class="row mb-4">
                                        <div class="col-md-8">
                                            <div class="bg-white rounded p-3 shadow-sm h-100">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-warning rounded-circle p-2 me-3">
                                                        <i class="ri-car-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Vehicle Details</h6>
                                                </div>
                                                <div class="row">
                                                    <div class="col-6 mb-3">
                                                        <small class="text-muted">Vehicle name</small>
                                                        <div class="fw-medium">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="col-6 mb-3">
                                                        <small class="text-muted">Service Type</small>
                                                        <div class="fw-medium">{{ $booking['type'] ?? 'N/A' }} Transfer</div>
                                                    </div>
                                                    {{-- <div class="col-6 mb-3">
                                                        <small class="text-muted">Mode</small>
                                                        <span class="badge bg-info">{{ strtoupper($booking['Mode'] ?? 'N/A') }}</span>
                                                    </div>
                                                    <div class="col-6 mb-3">
                                                        <small class="text-muted">Booking Type</small>
                                                        <span class="badge bg-primary">{{ ucfirst($booking['bookingType'] ?? 'Standard') }}</span>
                                                    </div> --}}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            @if(isset($booking['image']))
                                                <img src="{{ $booking['image'] }}" 
                                                     alt="{{ $booking['vehicles_name'] ?? 'Vehicle' }}" 
                                                     class="img-fluid rounded shadow-sm" 
                                                     style="height: 150px; width: 100%; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                                    <i class="ri-car-line ri-48px text-muted"></i>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Customer Information -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="bg-white rounded p-3 shadow-sm h-100">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-primary rounded-circle p-2 me-3">
                                                        <i class="ri-user-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Customer Details</h6>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Full Name</small>
                                                    <div class="fw-medium">{{ $booking['fullName'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Email Address</small>
                                                    <div class="fw-medium text-primary">{{ $booking['email'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="mb-0">
                                                    <small class="text-muted">Phone Number</small>
                                                    <div class="fw-medium">{{ $booking['countryCode'] ?? '' }} {{ $booking['phone'] ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-white rounded p-3 shadow-sm h-100">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-info rounded-circle p-2 me-3">
                                                        <i class="ri-map-pin-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Address Information</h6>
                                                </div>
                                                <div class="text-muted">
                                                    @if($booking['address1'] ?? false)
                                                        <div>{{ $booking['address1'] }}</div>
                                                    @endif
                                                    @if($booking['address2'] ?? false)
                                                        <div>{{ $booking['address2'] }}</div>
                                                    @endif
                                                    @if($booking['state'] ?? false)
                                                        <div>{{ $booking['state'] }} {{ $booking['zip'] ?? '' }}</div>
                                                    @endif
                                                    @if(!($booking['address1'] ?? false) && !($booking['address2'] ?? false) && !($booking['state'] ?? false))
                                                        <div class="text-muted">Address not provided</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Special Requests -->
                                    @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                                        <div class="bg-white rounded p-3 shadow-sm">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-purple rounded-circle p-2 me-3" style="background-color: #6f42c1;">
                                                    <i class="ri-message-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Special Requests</h6>
                                            </div>
                                            <div class="bg-light rounded p-3">
                                                <p class="mb-0 text-dark">{{ $booking['specialRequests'] }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <div class="bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <i class="ri-flight-land-line ri-48px text-muted"></i>
                        </div>
                        <h4 class="text-dark mb-3">No Arrival Transfer Data Available</h4>
                        <p class="text-muted mb-4">Entry port services are booked but detailed information is not available.</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer border-0 p-4" style="background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);">
                <button type="button" class="btn btn-outline-secondary px-4 py-2" onclick="closeServiceModal('entry_port', {{ $tour->tour_id }})" style="border-radius: 25px;">
                    <i class="ri-close-line me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
 </div>
@endif

<!-- Exit Port (Departure) Details Modal -->
@if(isset($svc['exit_port']) && $svc['exit_port'] > 0)
 <div class="modal fade" id="exit_portDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="exit_portDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content shadow-lg" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header p-0 border-0 position-relative" style="height: 180px; background: linear-gradient(135deg, #fd7f6f 0%, #feb47b 100%);">
                <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-between p-4">
                    <div class="text-white">
                        <h3 class="mb-1 fw-bold">
                            <i class="ri-flight-takeoff-line me-2"></i>Departure Transfer
                        </h3>
                        <p class="mb-0 opacity-75">Tour #{{ $tour->tour_id }} Exit Port Details</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('exit_port', {{ $tour->tour_id }})" aria-label="Close" style="filter: brightness(0) invert(1); font-size: 1.2rem;"></button>
                </div>
            </div>
            <div class="modal-body p-4" style="background-color: #f8f9fa;">
                @if(isset($serviceData['exit_port']) && count($serviceData['exit_port']) > 0)
                    @foreach($serviceData['exit_port'] as $index => $exitOrder)
                    @php
                        $exitData = is_string($exitOrder->data) ? json_decode($exitOrder->data, true) : $exitOrder->data;
                    @endphp
                    
                    @if(is_array($exitData))
                        @foreach($exitData as $booking)
                            <div class="card mb-4 shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                                <div class="card-header border-0" style="background: linear-gradient(90deg, #fd7f6f 0%, #feb47b 100%); padding: 20px;">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h5 class="mb-1 fw-bold text-white">
                                                <i class="ri-car-line me-2"></i>{{ $booking['vehicles_name'] ?? 'Vehicle Transfer' }}
                                            </h5>
                                            <p class="mb-0 text-white opacity-75">{{ ucfirst($booking['type'] ?? 'Standard') }} Transfer • Enquiry {{ $index + 1 }}</p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <div class="bg-white rounded-pill px-3 py-2 d-inline-block">
                                                <span class="text-success fw-bold fs-5">SGD {{ number_format($booking['totalPrice'] ?? 0, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body p-4" style="background-color: #f8f9fa;">
                                    <!-- Transfer Schedule -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="bg-white rounded p-3 shadow-sm h-100">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-warning rounded-circle p-2 me-3">
                                                        <i class="ri-calendar-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Transfer Schedule</h6>
                                                </div>
                                                <div class="mb-3">
                                                    <small class="text-muted">Departure Date</small>
                                                    <div class="fw-bold text-danger fs-5">{{ \Carbon\Carbon::parse($booking['bookingDate'])->format('D, M d, Y') }}</div>
                                                </div>
                                                <div class="mb-3">
                                                    <small class="text-muted">Pickup Time</small>
                                                    <div class="fw-medium text-primary">{{ $booking['entrytime'] ?? 'To be confirmed' }}</div>
                                                </div>
                                                <div>
                                                    <small class="text-muted">Transfer Type</small>
                                                    <div><span class="badge bg-warning px-3 py-2">{{ ucfirst($booking['type'] ?? 'Standard') }}</span></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-white rounded p-3 shadow-sm h-100">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-info rounded-circle p-2 me-3">
                                                        <i class="ri-group-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Passenger Information</h6>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-6 text-center">
                                                        <div class="bg-light rounded p-3">
                                                            <div class="fs-3 fw-bold text-success">{{ $booking['adults'] ?? 0 }}</div>
                                                            <small class="text-muted">Adults</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 text-center">
                                                        <div class="bg-light rounded p-3">
                                                            <div class="fs-3 fw-bold text-warning">{{ $booking['children'] ?? 0 }}</div>
                                                            <small class="text-muted">Children</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-center">
                                                    <span class="badge bg-primary px-3 py-2">
                                                        Total: {{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }} Passengers
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Route Information -->
                                    <div class="bg-white rounded p-3 shadow-sm mb-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-primary rounded-circle p-2 me-3">
                                                <i class="ri-route-line text-white"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark">Route Details</h6>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <div class="d-flex align-items-start">
                                                    <div class="bg-success rounded-circle p-2 me-3 mt-1">
                                                        <i class="ri-map-pin-line text-white"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted">Pickup Location</small>
                                                        <div class="fw-medium">{{ $booking['exitpickup'] ?? 'N/A' }}</div>
                                                        <small class="text-success">Origin</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="d-flex align-items-start">
                                                    <div class="bg-danger rounded-circle p-2 me-3 mt-1">
                                                        <i class="ri-flag-line text-white"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted">Drop-off Location</small>
                                                        <div class="fw-medium">{{ $booking['exitdropoff'] ?? 'N/A' }}</div>
                                                        <small class="text-danger">Destination</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">City</small>
                                                <div class="fw-medium">{{ $booking['city'] ?? 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Country</small>
                                                <div class="fw-medium">{{ $booking['country'] ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Vehicle Information -->
                                    <div class="row mb-4">
                                        <div class="col-md-8">
                                            <div class="bg-white rounded p-3 shadow-sm h-100">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-warning rounded-circle p-2 me-3">
                                                        <i class="ri-car-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Vehicle Details</h6>
                                                </div>
                                                <div class="row">
                                                    <div class="col-6 mb-3">
                                                        <small class="text-muted">Vehicle Name</small>
                                                        <div class="fw-medium">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="col-6 mb-3">
                                                        <small class="text-muted">Service Type</small>
                                                        <div class="fw-medium">{{ $booking['type'] ?? 'N/A' }} Transfer</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            @if(isset($booking['image']))
                                                <img src="{{ $booking['image'] }}" 
                                                     alt="{{ $booking['vehicles_name'] ?? 'Vehicle' }}" 
                                                     class="img-fluid rounded shadow-sm" 
                                                     style="height: 150px; width: 100%; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                                    <i class="ri-car-line ri-48px text-muted"></i>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Customer Information -->
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <div class="bg-white rounded p-3 shadow-sm h-100">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-primary rounded-circle p-2 me-3">
                                                        <i class="ri-user-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Customer Details</h6>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Full Name</small>
                                                    <div class="fw-medium">{{ $booking['fullName'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">Email Address</small>
                                                    <div class="fw-medium text-primary">{{ $booking['email'] ?? 'N/A' }}</div>
                                                </div>
                                                <div class="mb-0">
                                                    <small class="text-muted">Phone Number</small>
                                                    <div class="fw-medium">{{ $booking['countryCode'] ?? '' }} {{ $booking['phone'] ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="bg-white rounded p-3 shadow-sm h-100">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-info rounded-circle p-2 me-3">
                                                        <i class="ri-map-pin-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Address Information</h6>
                                                </div>
                                                <div class="text-muted">
                                                    @if($booking['address1'] ?? false)
                                                        <div>{{ $booking['address1'] }}</div>
                                                    @endif
                                                    @if($booking['address2'] ?? false)
                                                        <div>{{ $booking['address2'] }}</div>
                                                    @endif
                                                    @if($booking['state'] ?? false)
                                                        <div>{{ $booking['state'] }} {{ $booking['zip'] ?? '' }}</div>
                                                    @endif
                                                    @if(!($booking['address1'] ?? false) && !($booking['address2'] ?? false) && !($booking['state'] ?? false))
                                                        <div class="text-muted">Address not provided</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Special Requests -->
                                    @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                                        <div class="bg-white rounded p-3 shadow-sm">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-purple rounded-circle p-2 me-3" style="background-color: #6f42c1;">
                                                    <i class="ri-message-line text-white"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-dark">Special Requests</h6>
                                            </div>
                                            <div class="bg-light rounded p-3">
                                                <p class="mb-0 text-dark">{{ $booking['specialRequests'] }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <div class="bg-light rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <i class="ri-flight-takeoff-line ri-48px text-muted"></i>
                        </div>
                        <h4 class="text-dark mb-3">No Departure Transfer Data Available</h4>
                        <p class="text-muted mb-4">Exit port services are booked but detailed information is not available.</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer border-0 p-4" style="background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);">
                <button type="button" class="btn btn-outline-secondary px-4 py-2" onclick="closeServiceModal('exit_port', {{ $tour->tour_id }})" style="border-radius: 25px;">
                    <i class="ri-close-line me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
 </div>
@endif

<!-- Travel Hourly Details Modal -->
@if(isset($svc['travel_hourly']) && $svc['travel_hourly'] > 0)
    <div class="modal fade" id="travel_hourlyDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="travel_hourlyModalLabel{{ $tour->tour_id }}" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow-lg">
                @php
                    $firstOrder = $serviceData['travel_hourly'][0] ?? null;
                    $firstBookingData = null;
                    if ($firstOrder) {
                        $firstBookingData = is_string($firstOrder->data) ? json_decode($firstOrder->data, true) : $firstOrder->data;
                        $firstBookingData = is_array($firstBookingData) && isset($firstBookingData[0]) ? $firstBookingData[0] : $firstBookingData;
                    }
                @endphp
                
                <!-- Modal Header -->
                <div class="modal-header p-0 border-0 position-relative" style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-between p-4">
                        <div class="text-white">
                            <h3 class="mb-1 fw-bold">
                                <i class="ri-time-line me-2"></i>Local-Tour Hourly
                            </h3>
                            <p class="mb-0 opacity-75">Tour #{{ $tour->tour_id }} Hourly Tour Details • {{ $firstBookingData['city'] ?? 'Location not specified' }}</p>
                            <div class="mt-2">
                                <span class="badge bg-white bg-opacity-90 text-primary px-3 py-2">
                                    <i class="ri-calendar-line me-1"></i>
                                    {{ isset($firstBookingData['bookingDate']) ? \Carbon\Carbon::parse($firstBookingData['bookingDate'])->format('D, M d, Y') : 'Date not specified' }}
                                </span>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('travel_hourly', {{ $tour->tour_id }})" aria-label="Close" style="filter: brightness(0) invert(1); font-size: 1.2rem;"></button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4" style="background: #f8fafc;">
                    @if(isset($serviceData['travel_hourly']) && count($serviceData['travel_hourly']) > 0)
                        @foreach($serviceData['travel_hourly'] as $index => $hourlyOrder)
                            @php
                                $hourlyData = is_string($hourlyOrder->data) ? json_decode($hourlyOrder->data, true) : $hourlyOrder->data;
                            @endphp
                            
                            @if(is_array($hourlyData))
                                @foreach($hourlyData as $bookingIndex => $booking)
                                    @if($index > 0 || $bookingIndex > 0)
                                        <hr class="my-4">
                                    @endif
                            
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                        <div class="card-header bg-transparent border-0 text-white">
                                            <h5 class="card-title mb-0 fw-bold">
                                                <i class="ri-car-line me-2"></i>{{ $booking['vehicles_name'] ?? 'Hourly Tour Booking' }}
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Service Schedule & Group Information -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="bg-white rounded p-3 shadow-sm h-100">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-primary rounded-circle p-2 me-3">
                                                <i class="ri-calendar-line text-white"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark">Service Schedule</h6>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Booking Date</small>
                                                <div class="fw-medium">{{ isset($booking['bookingDate']) ? \Carbon\Carbon::parse($booking['bookingDate'])->format('D, M d, Y') : 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Time</small>
                                                <div class="fw-medium">{{ $booking['entrytime'] ?? 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Selected Hours</small>
                                                <span class="badge bg-info">{{ $booking['selectedHours'] ?? 'N/A' }} Hour(s)</span>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Service Type</small>
                                                <span class="badge bg-warning">{{ $booking['type'] ?? 'Standard' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-white rounded p-3 shadow-sm h-100">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-success rounded-circle p-2 me-3">
                                                <i class="ri-group-line text-white"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark">Group Information</h6>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Adults</small>
                                                <div class="fw-medium">{{ $booking['adults'] ?? 0 }}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Children</small>
                                                <div class="fw-medium">{{ $booking['children'] ?? 0 }}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Total Guests</small>
                                                <span class="badge bg-primary">{{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }}</span>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Night Service Timing</small>
                                                <div class="fw-medium text-muted small">{{ $booking['Night_Start_Time'] ?? 'N/A' }} - {{ $booking['Night_End_Time'] ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pickup Location & Vehicle Information -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="bg-white rounded p-3 shadow-sm h-100">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-success rounded-circle p-2 me-3">
                                                <i class="ri-map-pin-line text-white"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark">Pickup Location</h6>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 mb-3">
                                                <small class="text-muted">Pickup Point</small>
                                                <div class="fw-medium">{{ $booking['entrypickup'] ?? 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">City</small>
                                                <div class="fw-medium">{{ $booking['city'] ?? 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Country</small>
                                                <div class="fw-medium">{{ $booking['country'] ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <!-- Vehicle Information -->
                                    <div class="row mb-4">
                                        <div class="col-md-8">
                                            <div class="bg-white rounded p-3 shadow-sm h-100">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-warning rounded-circle p-2 me-3">
                                                        <i class="ri-car-line text-white"></i>
                                                    </div>
                                                    <h6 class="fw-bold mb-0 text-dark">Vehicle Details</h6>
                                                </div>
                                                <div class="row">
                                                    <div class="col-6 mb-3">
                                                        <small class="text-muted">Vehicle Name</small>
                                                        <div class="fw-medium">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                                    </div>
                                                    <div class="col-6 mb-3">
                                                        <small class="text-muted">Service Type</small>
                                                        <div class="fw-medium">{{ $booking['type'] ?? 'N/A' }} Transfer</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            @if(isset($booking['image']))
                                                <img src="{{ $booking['image'] }}" 
                                                     alt="{{ $booking['vehicles_name'] ?? 'Vehicle' }}" 
                                                     class="img-fluid rounded shadow-sm" 
                                                     style="height: 150px; width: 100%; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                                    <i class="ri-car-line ri-48px text-muted"></i>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing & Customer Information -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="bg-white rounded p-3 shadow-sm h-100">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-warning rounded-circle p-2 me-3">
                                                <i class="ri-money-dollar-circle-line text-white"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark">Pricing Details</h6>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Total Price</small>
                                                <div class="fw-bold text-success">${{ $booking['totalPrice'] ?? '0' }}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Tax</small>
                                                <div class="fw-medium">{{ $booking['Tax'] ?? '0' }}%</div>
                                            </div>
                                            {{-- <div class="col-12 mb-3">
                                                <small class="text-muted">Booking Type</small>
                                                <span class="badge bg-primary">{{ ucfirst($booking['bookingType'] ?? 'Standard') }}</span>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-white rounded p-3 shadow-sm h-100">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-info rounded-circle p-2 me-3">
                                                <i class="ri-user-line text-white"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark">Customer Information</h6>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 mb-2">
                                                <small class="text-muted">Name</small>
                                                <div class="fw-medium">{{ $booking['fullName'] ?? 'N/A' }}</div>
                                            </div>
                                            <div class="col-12 mb-2">
                                                <small class="text-muted">Email</small>
                                                <div class="fw-medium">{{ $booking['email'] ?? 'N/A' }}</div>
                                            </div>
                                            <div class="col-12 mb-2">
                                                <small class="text-muted">Phone</small>
                                                <div class="fw-medium">{{ $booking['phone'] ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Special Requests -->
                            @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                            <div class="bg-white rounded p-3 shadow-sm mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-secondary rounded-circle p-2 me-3">
                                        <i class="ri-message-2-line text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-dark">Special Requests</h6>
                                </div>
                                <p class="text-muted mb-0">{{ $booking['specialRequests'] }}</p>
                            </div>
                            @endif
                                @endforeach
                            @endif
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <i class="ri-time-line ri-48px text-muted mb-3"></i>
                            <h5 class="text-muted">No hourly tour data available</h5>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer bg-light border-0" style="border-radius: 0 0 8px 8px;">
                    <button type="button" class="btn btn-outline-secondary" onclick="closeServiceModal('travel_hourly', {{ $tour->tour_id }})">
                        <i class="ri-close-line me-1"></i>Close
                    </button>
                    {{-- <button type="button" class="btn btn-primary">
                        <i class="ri-download-line me-1"></i>Download Details
                    </button> --}}
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Travel Point Details Modal -->
@if(isset($svc['travel_point']) && $svc['travel_point'] > 0)
    <div class="modal fade" id="travel_pointDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="travel_pointModalLabel{{ $tour->tour_id }}" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow-lg">
                @php
                    $firstOrder = $serviceData['travel_point'][0] ?? null;
                    $firstBookingData = null;
                    $headerFromZone = 'N/A';
                    $headerToZone = 'N/A';
                    
                    if ($firstOrder) {
                        $firstBookingData = is_string($firstOrder->data) ? json_decode($firstOrder->data, true) : $firstOrder->data;
                        $firstBookingData = is_array($firstBookingData) && isset($firstBookingData[0]) ? $firstBookingData[0] : $firstBookingData;
                        
                        // Get zone names for header
                        if(isset($firstBookingData['from_zone_id']) && $firstBookingData['from_zone_id']) {
                            $fromZone = \DB::table('zones')->where('zone_id', $firstBookingData['from_zone_id'])->first();
                            $headerFromZone = $fromZone ? $fromZone->zone_type : 'Zone ' . $firstBookingData['from_zone_id'];
                        }
                        
                        if(isset($firstBookingData['to_zone_id']) && $firstBookingData['to_zone_id']) {
                            $toZone = \DB::table('zones')->where('zone_id', $firstBookingData['to_zone_id'])->first();
                            $headerToZone = $toZone ? $toZone->zone_type : 'Zone ' . $firstBookingData['to_zone_id'];
                        }
                    }
                @endphp
                
                <!-- Modal Header -->
                <div class="modal-header p-0 border-0 position-relative" style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-between p-4">
                        <div class="text-white">
                            <h3 class="mb-1 fw-bold">
                                <i class="ri-route-line me-2"></i>Local-Tour Point to Point
                            </h3>
                            <p class="mb-0 opacity-75">Tour #{{ $tour->tour_id }} Point to Point Transfer • {{ $headerFromZone }} → {{ $headerToZone }}</p>
                            <div class="mt-2">
                                <span class="badge bg-white bg-opacity-90 text-primary px-3 py-2">
                                    <i class="ri-calendar-line me-1"></i>
                                    {{ isset($firstBookingData['bookingDate']) ? \Carbon\Carbon::parse($firstBookingData['bookingDate'])->format('D, M d, Y') : 'Date not specified' }}
                                </span>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('travel_point', {{ $tour->tour_id }})" aria-label="Close" style="filter: brightness(0) invert(1); font-size: 1.2rem;"></button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4" style="background: #f8fafc;">
                    @if(isset($serviceData['travel_point']) && count($serviceData['travel_point']) > 0)
                        @foreach($serviceData['travel_point'] as $index => $pointOrder)
                            @php
                                $pointData = is_string($pointOrder->data) ? json_decode($pointOrder->data, true) : $pointOrder->data;
                            @endphp
                            
                            @if(is_array($pointData))
                                @foreach($pointData as $bookingIndex => $booking)
                                    @php
                                        // Fetch zone information
                                        $fromZoneName = 'N/A';
                                        $toZoneName = 'N/A';
                                        
                                        if(isset($booking['from_zone_id']) && $booking['from_zone_id']) {
                                            $fromZone = \DB::table('zones')->where('zone_id', $booking['from_zone_id'])->first();
                                            $fromZoneName = $fromZone ? $fromZone->zone_type : 'Zone ' . $booking['from_zone_id'];
                                        }
                                        
                                        if(isset($booking['to_zone_id']) && $booking['to_zone_id']) {
                                            $toZone = \DB::table('zones')->where('zone_id', $booking['to_zone_id'])->first();
                                            $toZoneName = $toZone ? $toZone->zone_type : 'Zone ' . $booking['to_zone_id'];
                                        }
                                    @endphp
                                    
                                    @if($index > 0 || $bookingIndex > 0)
                                        <hr class="my-4">
                                    @endif
                            
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                        <div class="card-header bg-transparent border-0 text-white">
                                            <h5 class="card-title mb-0 fw-bold">
                                                <i class="ri-car-line me-2"></i>{{ $booking['vehicles_name'] ?? 'Point to Point Transfer' }}
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Service Schedule & Group Information -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="bg-white rounded p-3 shadow-sm h-100">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-primary rounded-circle p-2 me-3">
                                                <i class="ri-calendar-line text-white"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark">Transfer Schedule</h6>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Date</small>
                                                <div class="fw-medium">{{ isset($booking['bookingDate']) ? \Carbon\Carbon::parse($booking['bookingDate'])->format('D, M d, Y') : 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Time</small>
                                                <div class="fw-medium">{{ $booking['entrytime'] ?? 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Distance</small>
                                                <span class="badge bg-info">{{ $booking['distance'] ?? 'N/A' }} km</span>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Service Type</small>
                                                <span class="badge bg-warning">{{ $booking['type'] ?? 'Standard' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-white rounded p-3 shadow-sm h-100">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-success rounded-circle p-2 me-3">
                                                <i class="ri-group-line text-white"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark">Group Information</h6>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Adults</small>
                                                <div class="fw-medium">{{ $booking['adults'] ?? 0 }}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Children</small>
                                                <div class="fw-medium">{{ $booking['children'] ?? 0 }}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Total Guests</small>
                                                <span class="badge bg-primary">{{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }}</span>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Night Service Timing</small>
                                                <div class="fw-medium text-muted small">{{ $booking['Night_Start_Time'] ?? 'N/A' }} - {{ $booking['Night_End_Time'] ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Route Details -->
                            <div class="bg-white rounded p-3 shadow-sm mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-warning rounded-circle p-2 me-3">
                                        <i class="ri-direction-line text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-dark">Route Details</h6>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-start">
                                            <div class="bg-success rounded-circle p-2 me-3 mt-1">
                                                <i class="ri-play-circle-line text-white"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted">Pickup Location</small>
                                                <div class="fw-medium">{{ $booking['entrypickup'] ?? 'N/A' }}</div>
                                                <small class="text-success">Origin</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-start">
                                            <div class="bg-danger rounded-circle p-2 me-3 mt-1">
                                                <i class="ri-flag-line text-white"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted">Drop-off Location</small>
                                                <div class="fw-medium">{{ $booking['entrydropoff'] ?? 'N/A' }}</div>
                                                <small class="text-danger">Destination</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <small class="text-muted">City</small>
                                        <div class="fw-medium">{{ $booking['city'] ?? 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <small class="text-muted">Country</small>
                                        <div class="fw-medium">{{ $booking['country'] ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Vehicle Information -->
                            <div class="row mb-4">
                                <div class="col-md-8">
                                    <div class="bg-white rounded p-3 shadow-sm h-100">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-warning rounded-circle p-2 me-3">
                                                <i class="ri-car-line text-white"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark">Vehicle Details</h6>
                                        </div>
                                        <div class="row">
                                            <div class="col-6 mb-3">
                                                <small class="text-muted">Vehicle Name</small>
                                                <div class="fw-medium">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <small class="text-muted">Service Type</small>
                                                <div class="fw-medium">{{ $booking['type'] ?? 'N/A' }} Transfer</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    @if(isset($booking['image']))
                                        <img src="{{ $booking['image'] }}" 
                                             alt="{{ $booking['vehicles_name'] ?? 'Vehicle' }}" 
                                             class="img-fluid rounded shadow-sm" 
                                             style="height: 150px; width: 100%; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                            <i class="ri-car-line ri-48px text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Pricing & Customer Information -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="bg-white rounded p-3 shadow-sm h-100">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-warning rounded-circle p-2 me-3">
                                                <i class="ri-money-dollar-circle-line text-white"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark">Pricing Details</h6>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Total Price</small>
                                                <div class="fw-bold text-success">${{ $booking['totalPrice'] ?? '0' }}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Tax</small>
                                                <div class="fw-medium">{{ $booking['Tax'] ?? '0' }}%</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">From Zone</small>
                                                <div class="fw-medium">{{ $fromZoneName }}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">To Zone</small>
                                                <div class="fw-medium">{{ $toZoneName }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-white rounded p-3 shadow-sm h-100">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-info rounded-circle p-2 me-3">
                                                <i class="ri-user-line text-white"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark">Customer Information</h6>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 mb-2">
                                                <small class="text-muted">Name</small>
                                                <div class="fw-medium">{{ $booking['fullName'] ?? 'N/A' }}</div>
                                            </div>
                                            <div class="col-12 mb-2">
                                                <small class="text-muted">Email</small>
                                                <div class="fw-medium">{{ $booking['email'] ?? 'N/A' }}</div>
                                            </div>
                                            <div class="col-12 mb-2">
                                                <small class="text-muted">Phone</small>
                                                <div class="fw-medium">{{ $booking['phone'] ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Special Requests -->
                            @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                            <div class="bg-white rounded p-3 shadow-sm mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-secondary rounded-circle p-2 me-3">
                                        <i class="ri-message-2-line text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-dark">Special Requests</h6>
                                </div>
                                <p class="text-muted mb-0">{{ $booking['specialRequests'] }}</p>
                            </div>
                            @endif
                            @endforeach
                            @endif
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <i class="ri-route-line ri-48px text-muted mb-3"></i>
                            <h5 class="text-muted">No point to point transfer data available</h5>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer bg-light border-0" style="border-radius: 0 0 8px 8px;">
                    <button type="button" class="btn btn-outline-secondary" onclick="closeServiceModal('travel_point', {{ $tour->tour_id }})">
                        <i class="ri-close-line me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Local Transport Details Modal -->
@if(isset($svc['local_transport']) && $svc['local_transport'] > 0)
    <div class="modal fade" id="local_transportDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="local_transportModalLabel{{ $tour->tour_id }}" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow-lg">
                @php
                    $firstOrder = $serviceData['local_transport'][0] ?? null;
                    $firstBookingData = null;
                    $headerFromZone = 'N/A';
                    $headerToZone = 'N/A';
                    
                    if ($firstOrder) {
                        $firstBookingData = is_string($firstOrder->data) ? json_decode($firstOrder->data, true) : $firstOrder->data;
                        $firstBookingData = is_array($firstBookingData) && isset($firstBookingData[0]) ? $firstBookingData[0] : $firstBookingData;
                        
                        // Get zone names for header
                        if(isset($firstBookingData['from_zone_id']) && $firstBookingData['from_zone_id']) {
                            $fromZone = \DB::table('zones')->where('zone_id', $firstBookingData['from_zone_id'])->first();
                            $headerFromZone = $fromZone ? $fromZone->zone_type : 'Zone ' . $firstBookingData['from_zone_id'];
                        }
                        
                        if(isset($firstBookingData['to_zone_id']) && $firstBookingData['to_zone_id']) {
                            $toZone = \DB::table('zones')->where('zone_id', $firstBookingData['to_zone_id'])->first();
                            $headerToZone = $toZone ? $toZone->zone_type : 'Zone ' . $firstBookingData['to_zone_id'];
                        }
                    }
                @endphp
                
                <!-- Modal Header -->
                <div class="modal-header p-0 border-0 position-relative" style="height: 200px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-between p-4">
                        <div class="text-white">
                            <h3 class="mb-1 fw-bold">
                                <i class="ri-car-line me-2"></i>Local Transport
                            </h3>
                            <p class="mb-0 opacity-75">Tour #{{ $tour->tour_id }} Local Transport Service • {{ $headerFromZone }} → {{ $headerToZone }}</p>
                            <div class="mt-2">
                                <span class="badge bg-white bg-opacity-90 text-primary px-3 py-2">
                                    <i class="ri-calendar-line me-1"></i>
                                    {{ isset($firstBookingData['bookingDate']) ? \Carbon\Carbon::parse($firstBookingData['bookingDate'])->format('M d, Y') : 'Date not specified' }}
                                </span>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('local_transport', {{ $tour->tour_id }})" aria-label="Close" style="filter: brightness(0) invert(1); font-size: 1.2rem;"></button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4" style="background: #f8fafc;">
                    @if(isset($serviceData['local_transport']) && count($serviceData['local_transport']) > 0)
                        @foreach($serviceData['local_transport'] as $index => $transportOrder)
                            @php
                                $transportData = is_string($transportOrder->data) ? json_decode($transportOrder->data, true) : $transportOrder->data;
                            @endphp
                            
                            @if(is_array($transportData))
                                @foreach($transportData as $bookingIndex => $booking)
                                    @php
                                        // Fetch zone information
                                        $fromZoneName = 'N/A';
                                        $toZoneName = 'N/A';
                                        
                                        if(isset($booking['from_zone_id']) && $booking['from_zone_id']) {
                                            $fromZone = \DB::table('zones')->where('zone_id', $booking['from_zone_id'])->first();
                                            $fromZoneName = $fromZone ? $fromZone->zone_type : 'Zone ' . $booking['from_zone_id'];
                                        }
                                        
                                        if(isset($booking['to_zone_id']) && $booking['to_zone_id']) {
                                            $toZone = \DB::table('zones')->where('zone_id', $booking['to_zone_id'])->first();
                                            $toZoneName = $toZone ? $toZone->zone_type : 'Zone ' . $booking['to_zone_id'];
                                        }
                                    @endphp
                                    
                                    @if($index > 0 || $bookingIndex > 0)
                                        <hr class="my-4">
                                    @endif
                            
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                        <div class="card-header bg-transparent border-0 text-white">
                                            <h5 class="card-title mb-0 fw-bold">
                                                <i class="ri-car-line me-2"></i>{{ $booking['vehicles_name'] ?? 'Local Transport Service' }}
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Service Schedule & Group Information -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="bg-white rounded p-3 shadow-sm h-100">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-primary rounded-circle p-2 me-3">
                                                <i class="ri-calendar-line text-white"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark">Transport Schedule</h6>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Date</small>
                                                <div class="fw-medium">{{ isset($booking['bookingDate']) ? \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y') : 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Time</small>
                                                <div class="fw-medium">{{ $booking['entrytime'] ?? 'N/A' }}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Distance</small>
                                                <span class="badge bg-info">{{ $booking['distance'] ?? 'N/A' }} km</span>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Service Type</small>
                                                <span class="badge bg-warning">{{ $booking['type'] ?? 'Standard' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-white rounded p-3 shadow-sm h-100">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-success rounded-circle p-2 me-3">
                                                <i class="ri-group-line text-white"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark">Group Information</h6>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Adults</small>
                                                <div class="fw-medium">{{ $booking['adults'] ?? 0 }}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Children</small>
                                                <div class="fw-medium">{{ $booking['children'] ?? 0 }}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Total Guests</small>
                                                <span class="badge bg-primary">{{ ($booking['adults'] ?? 0) + ($booking['children'] ?? 0) }}</span>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Service Hours</small>
                                                <div class="fw-medium text-muted small">{{ $booking['Night_Start_Time'] ?? 'N/A' }} - {{ $booking['Night_End_Time'] ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Route Details -->
                            <div class="bg-white rounded p-3 shadow-sm mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-warning rounded-circle p-2 me-3">
                                        <i class="ri-direction-line text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-dark">Route Details</h6>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-start">
                                            <div class="bg-success rounded-circle p-2 me-3 mt-1">
                                                <i class="ri-play-circle-line text-white"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted">Pickup Location</small>
                                                <div class="fw-medium">{{ $booking['entrypickup'] ?? 'N/A' }}</div>
                                                <small class="text-success">Origin</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex align-items-start">
                                            <div class="bg-danger rounded-circle p-2 me-3 mt-1">
                                                <i class="ri-flag-line text-white"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted">Drop-off Location</small>
                                                <div class="fw-medium">{{ $booking['entrydropoff'] ?? 'N/A' }}</div>
                                                <small class="text-danger">Destination</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <small class="text-muted">City</small>
                                        <div class="fw-medium">{{ $booking['city'] ?? 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <small class="text-muted">Country</small>
                                        <div class="fw-medium">{{ $booking['country'] ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Vehicle Information -->
                            <div class="row mb-4">
                                <div class="col-md-8">
                                    <div class="bg-white rounded p-3 shadow-sm h-100">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-warning rounded-circle p-2 me-3">
                                                <i class="ri-car-line text-white"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark">Vehicle Details</h6>
                                        </div>
                                        <div class="row">
                                            <div class="col-6 mb-3">
                                                <small class="text-muted">Vehicle Name</small>
                                                <div class="fw-medium">{{ $booking['vehicles_name'] ?? 'N/A' }}</div>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <small class="text-muted">Service Type</small>
                                                <div class="fw-medium">{{ $booking['type'] ?? 'N/A' }} Transport</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    @if(isset($booking['image']))
                                        <img src="{{ $booking['image'] }}" 
                                             alt="{{ $booking['vehicles_name'] ?? 'Vehicle' }}" 
                                             class="img-fluid rounded shadow-sm" 
                                             style="height: 150px; width: 100%; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                            <i class="ri-car-line ri-48px text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Pricing & Customer Information -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="bg-white rounded p-3 shadow-sm h-100">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-warning rounded-circle p-2 me-3">
                                                <i class="ri-money-dollar-circle-line text-white"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark">Pricing Details</h6>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Total Price</small>
                                                <div class="fw-bold text-success">${{ $booking['totalPrice'] ?? '0' }}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">Tax</small>
                                                <div class="fw-medium">{{ $booking['Tax'] ?? '0' }}%</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">From Zone</small>
                                                <div class="fw-medium">{{ $fromZoneName }}</div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <small class="text-muted">To Zone</small>
                                                <div class="fw-medium">{{ $toZoneName }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="bg-white rounded p-3 shadow-sm h-100">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="bg-info rounded-circle p-2 me-3">
                                                <i class="ri-user-line text-white"></i>
                                            </div>
                                            <h6 class="fw-bold mb-0 text-dark">Customer Information</h6>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 mb-2">
                                                <small class="text-muted">Name</small>
                                                <div class="fw-medium">{{ $booking['fullName'] ?? 'N/A' }}</div>
                                            </div>
                                            <div class="col-12 mb-2">
                                                <small class="text-muted">Email</small>
                                                <div class="fw-medium">{{ $booking['email'] ?? 'N/A' }}</div>
                                            </div>
                                            <div class="col-12 mb-2">
                                                <small class="text-muted">Phone</small>
                                                <div class="fw-medium">{{ $booking['phone'] ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Special Requests -->
                            @if(isset($booking['specialRequests']) && !empty($booking['specialRequests']))
                            <div class="bg-white rounded p-3 shadow-sm mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-secondary rounded-circle p-2 me-3">
                                        <i class="ri-message-2-line text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-dark">Special Requests</h6>
                                </div>
                                <p class="text-muted mb-0">{{ $booking['specialRequests'] }}</p>
                            </div>
                            @endif
                            @endforeach
                            @endif
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <i class="ri-car-line ri-48px text-muted mb-3"></i>
                            <h5 class="text-muted">No local transport data available</h5>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer bg-light border-0" style="border-radius: 0 0 8px 8px;">
                    <button type="button" class="btn btn-outline-secondary" onclick="closeServiceModal('local_transport', {{ $tour->tour_id }})">
                        <i class="ri-close-line me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
@endforeach
</div>

<script>
// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const countryFilter = document.getElementById('countryFilter');
    const cityFilter = document.getElementById('cityFilter');
    const agentFilter = document.getElementById('agentFilter');
    const dateRange = document.getElementById('dateRange');
    const dateRangeStart = document.getElementById('dateRangeStart');
    const dateRangeEnd = document.getElementById('dateRangeEnd');
    
    // Add event listeners
    if (searchInput) searchInput.addEventListener('input', filterTable);
    if (countryFilter) countryFilter.addEventListener('change', filterTable);
    if (cityFilter) cityFilter.addEventListener('change', filterTable);
    if (agentFilter) agentFilter.addEventListener('change', filterTable);
    // Date range picker will be initialized in scripts section where jQuery is available
    
    // Select all functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
    
    // Apply initial filter on page load to show today's data
    filterTable();
});

function filterTable() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
    const countryFilter = document.getElementById('countryFilter')?.value || '';
    const cityFilter = document.getElementById('cityFilter')?.value || '';
    const agentFilter = document.getElementById('agentFilter')?.value || '';
    const dateStart = document.getElementById('dateRangeStart')?.value || '';
    const dateEnd = document.getElementById('dateRangeEnd')?.value || '';
    
    const rows = document.querySelectorAll('#toursTable tbody tr');
    
    rows.forEach(row => {
        if (row.cells.length === 1) return; // Skip empty state row
        
        const tourDetails = row.cells[1]?.textContent.toLowerCase() || '';
        const destination = row.cells[2]?.querySelector('.fw-medium')?.textContent || '';
        const city = row.cells[2]?.querySelector('.text-muted')?.textContent || '';
        const agent = row.cells[4]?.querySelector('.fw-medium')?.textContent || '';
        const createdAt = row.getAttribute('data-created-at');
        const updatedAt = row.getAttribute('data-updated-at');
        
        let show = true;
        
        if (searchTerm && !tourDetails.includes(searchTerm) && !destination.toLowerCase().includes(searchTerm)) {
            show = false;
        }
        
        if (countryFilter && destination !== countryFilter) {
            show = false;
        }
        
        if (cityFilter && city !== cityFilter) {
            show = false;
        }
        
        if (agentFilter && agent !== agentFilter) {
            show = false;
        }
        
        // Date range filtering (check both created_at and updated_at)
        if (dateStart && dateEnd && (createdAt || updatedAt)) {
            const s = new Date(dateStart + 'T00:00:00');
            const e = new Date(dateEnd + 'T23:59:59');
            let dateInRange = false;
            
            // Check created_at if available
            if (createdAt) {
                const createdDate = new Date(createdAt + 'T00:00:00');
                if (createdDate >= s && createdDate <= e) {
                    dateInRange = true;
                }
            }
            
            // Check updated_at if available and created_at didn't match
            if (!dateInRange && updatedAt) {
                const updatedDate = new Date(updatedAt + 'T00:00:00');
                if (updatedDate >= s && updatedDate <= e) {
                    dateInRange = true;
                }
            }
            
            if (!dateInRange) {
                show = false;
            }
        }
        
        row.style.display = show ? '' : 'none';
    });

    // Update header/cards counts based on visible rows
    const visibleRows = Array.from(document.querySelectorAll('#toursTable tbody tr')).filter(r => r.style.display !== 'none' && r.cells.length > 1);
    const rangeCount = visibleRows.length;
    const adults = visibleRows.reduce((sum, r) => sum + parseInt(r.getAttribute('data-adult') || '0', 10), 0);
    const children = visibleRows.reduce((sum, r) => sum + parseInt(r.getAttribute('data-child') || '0', 10), 0);
    
    // Count today's enquiries from visible rows
    const today = new Date().toISOString().split('T')[0];
    const todayCount = visibleRows.filter(r => {
        const createdAt = r.getAttribute('data-created-at');
        return createdAt === today;
    }).length;

    // Update counts and labels
    const countEl = document.getElementById('rangeCount');
    const labelEl = document.getElementById('rangeLabel');
    const statEnquiries = document.getElementById('statEnquiriesCount');
    const statEnquiriesLabel = document.getElementById('statEnquiriesLabel');
    const statToday = document.getElementById('statTodayCount');
    const statAdults = document.getElementById('statAdultsCount');
    const statAdultsLabel = document.getElementById('statAdultsLabel');
    const statChildren = document.getElementById('statChildrenCount');
    const statChildrenLabel = document.getElementById('statChildrenLabel');

    if (countEl) countEl.textContent = rangeCount;
    if (statEnquiries) statEnquiries.textContent = rangeCount;
    if (statToday) statToday.textContent = todayCount;
    if (statAdults) statAdults.textContent = adults;
    if (statChildren) statChildren.textContent = children;

    if (dateStart && dateEnd) {
        const start = new Date(dateStart);
        const end = new Date(dateEnd);
        
        // Format the date range label
        let label;
        if (start.getMonth() === end.getMonth() && start.getFullYear() === end.getFullYear()) {
            // Same month
            if (start.getDate() === 1 && end.getDate() === new Date(end.getFullYear(), end.getMonth() + 1, 0).getDate()) {
                // Full month
                label = start.toLocaleString('default', { month: 'long', year: 'numeric' });
            } else {
                label = `${start.getDate()}-${end.getDate()} ${start.toLocaleString('default', { month: 'short' })}, ${start.getFullYear()}`;
            }
        } else {
            label = `${start.toLocaleString('default', { month: 'short' })} ${start.getDate()} - ${end.toLocaleString('default', { month: 'short' })} ${end.getDate()}, ${end.getFullYear()}`;
        }
        
        if (labelEl) labelEl.textContent = label;
        if (statEnquiriesLabel) statEnquiriesLabel.textContent = `Enquiries - ${label}`;
        if (statAdultsLabel) statAdultsLabel.textContent = `Adults - ${label}`;
        if (statChildrenLabel) statChildrenLabel.textContent = `Children - ${label}`;
    } else {
        const month = new Date().toLocaleString('default', { month: 'long' });
        if (labelEl) labelEl.textContent = month;
        if (statEnquiriesLabel) statEnquiriesLabel.textContent = `${month} Enquiries`;
        if (statAdultsLabel) statAdultsLabel.textContent = `${month} Adults`;
        if (statChildrenLabel) statChildrenLabel.textContent = `${month} Children`;
    }
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('countryFilter').value = '';
    document.getElementById('cityFilter').value = '';
    document.getElementById('agentFilter').value = '';
    const dr = document.getElementById('dateRange');
    const ds = document.getElementById('dateRangeStart');
    const de = document.getElementById('dateRangeEnd');
    if (dr) dr.value = '';
    if (ds) ds.value = '';
    if (de) de.value = '';
    filterTable();
}

function convertToProspect(tourId) {
    if (confirm('Are you sure you want to move this enquiry to Follow Up?')) {
        // Implementation for status update
        console.log('Converting tour', tourId, 'to Prospect status');
        // Add AJAX call here
    }
}

function convertToTentative(tourId) {
    if (confirm('Are you sure you want to mark this enquiry as Tentative?')) {
        // Implementation for status update
        console.log('Converting tour', tourId, 'to Tentative status');
        // Add AJAX call here
    }
}

function deleteTour(tourId) {
    if (confirm('Are you sure you want to delete this tour? This action cannot be undone.')) {
        // Implementation for deletion
        console.log('Deleting tour', tourId);
        // Add AJAX call here
    }
}

function exportData() {
    // Implementation for data export
    console.log('Exporting data...');
}

// Service Modal Functions
function openServiceModal(serviceType, tourId, event) {
    console.log('Opening service modal:', serviceType, 'for tour:', tourId);
    
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    // Construct modal ID
    const modalId = `${serviceType}DetailsModal${tourId}`;
    console.log('Looking for modal element:', modalId);
    
    // Find the modal element
    const modalElement = document.getElementById(modalId);
    console.log('Modal element found:', !!modalElement);
    
    if (!modalElement) {
        console.error('Modal element not found:', modalId);
        
        // Log available modals for debugging
        const availableModals = Array.from(document.querySelectorAll('[id*="DetailsModal"]')).map(el => el.id);
        console.log('Available service modals on page:', availableModals);
        
        // Show user-friendly error
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Modal Not Found',
                text: `Could not find ${serviceType} details modal for tour ${tourId}. Please refresh the page and try again.`,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else {
            alert(`Could not find ${serviceType} details modal for tour ${tourId}. Please refresh the page and try again.`);
        }
        return;
    }
    
    try {
        // Method 1: Try Bootstrap 5 method
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            console.log('Using Bootstrap 5 modal method');
            const modal = new bootstrap.Modal(modalElement, {
                backdrop: 'static',
                keyboard: false
            });
            modal.show();
            console.log('Bootstrap 5 modal show called');
            return;
        }
        
        // Method 2: Try jQuery method (fallback)
        if (typeof $ !== 'undefined' && $.fn.modal) {
            console.log('Using jQuery modal method');
            $(modalElement).modal({
                backdrop: 'static',
                keyboard: false
            });
            $(modalElement).modal('show');
            console.log('jQuery modal show called');
            return;
        }
        
        // Method 3: Manual modal display (last resort)
        console.log('Using manual modal display');
        modalElement.style.display = 'block';
        modalElement.classList.add('show');
        modalElement.setAttribute('aria-hidden', 'false');
        
        // Add backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = `backdrop-${modalId}`;
        document.body.appendChild(backdrop);
        
        // Prevent body scroll
        document.body.classList.add('modal-open');
        
        console.log('Manual modal display applied');
        
    } catch (error) {
        console.error('Error opening modal:', error);
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error',
                text: 'An error occurred while opening the modal. Please try again.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else {
            alert('An error occurred while opening the modal. Please try again.');
        }
    }
}

function closeServiceModal(serviceType, tourId) {
    const modalId = `${serviceType}DetailsModal${tourId}`;
    const modalElement = document.getElementById(modalId);
    
    if (!modalElement) {
        console.error('Modal element not found for closing:', modalId);
        return;
    }
    
    try {
        // Method 1: Bootstrap 5
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.hide();
            }
        }
        
        // Method 2: jQuery
        if (typeof $ !== 'undefined' && $.fn.modal) {
            $(modalElement).modal('hide');
        }
        
        // Method 3: Manual
        modalElement.style.display = 'none';
        modalElement.classList.remove('show');
        modalElement.setAttribute('aria-hidden', 'true');
        
        // Remove backdrop
        const backdrop = document.getElementById(`backdrop-${modalId}`);
        if (backdrop) {
            backdrop.remove();
        }
        
        // Remove any orphaned backdrops
        const allBackdrops = document.querySelectorAll('.modal-backdrop');
        allBackdrops.forEach(backdrop => backdrop.remove());
        
        // Re-enable body scroll
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        
    } catch (error) {
        console.error('Error closing modal:', error);
    }
}

// Legacy function for backwards compatibility
function openHotelModal(tourId) {
    openServiceModal('hotel', tourId);
}

function closeHotelModal(tourId) {
    closeServiceModal('hotel', tourId);
}

// Test function for services
function testServices() {
    console.log('Testing services functionality...');
    
    const rows = document.querySelectorAll('#toursTable tbody tr');
    console.log('Total rows found:', rows.length);
    
    // Test first few rows
    rows.forEach((row, index) => {
        if (index < 3) { // Only test first 3 rows
            const tourId = row.querySelector('[data-tour-id]')?.getAttribute('data-tour-id') || 'N/A';
            const servicesCell = row.cells[6]; // Services column
            const serviceBadges = servicesCell?.querySelectorAll('.badge') || [];
            
            console.log(`Row ${index + 1}:`, {
                tourId,
                servicesCell: servicesCell?.textContent,
                serviceBadgesCount: serviceBadges.length,
                serviceBadges: Array.from(serviceBadges).map(badge => badge.textContent)
            });
        }
    });
    
    // Test modal opening
    const firstRow = rows[0];
    if (firstRow) {
        const firstServiceBadge = firstRow.querySelector('.badge[onclick*="openServiceModal"]');
        if (firstServiceBadge) {
            console.log('Testing first service badge click...');
            const onclick = firstServiceBadge.getAttribute('onclick');
            console.log('onclick attribute:', onclick);
        } else {
            console.log('No clickable service badges found');
        }
    }
}

// function bulkActions() {
//     const selectedTours = document.querySelectorAll('.row-checkbox:checked');
//     if (selectedTours.length === 0) {
//         alert('Please select at least one tour for bulk actions.');
//         return;
//     }
    
//     // Implementation for bulk actions
//     console.log('Bulk actions for', selectedTours.length, 'tours');
// }
</script>
@endsection
@section('scripts')
<!-- Date Range Picker JS - Load after jQuery -->
<script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<script>
    // Wait for all scripts to load before initializing
    $(document).ready(function() {
        // Small delay to ensure all scripts are loaded
        setTimeout(function() {
            initializeDateRangePicker();
            initializeDataTable();
        }, 200);
    });
    
    function initializeDateRangePicker() {
        // Initialize date range picker first
        const dateRange = document.getElementById('dateRange');
        const dateRangeStart = document.getElementById('dateRangeStart');
        const dateRangeEnd = document.getElementById('dateRangeEnd');
        
        if (dateRange && typeof moment !== 'undefined' && typeof $.fn.daterangepicker !== 'undefined') {
            // Set default to current month
            const startOfMonth = moment().startOf('month');
            const endOfMonth = moment().endOf('month');
            
            $(dateRange).daterangepicker({
                opens: 'left',
                autoUpdateInput: true,
                maxDate: moment(), // No future dates
                startDate: startOfMonth,
                endDate: endOfMonth,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'MMM DD, YYYY'
                }
            });

            // Set initial values for current month
            $(dateRange).val(startOfMonth.format('MMM DD') + ' - ' + endOfMonth.format('MMM DD, YYYY'));
            if (dateRangeStart) dateRangeStart.value = startOfMonth.format('YYYY-MM-DD');
            if (dateRangeEnd) dateRangeEnd.value = endOfMonth.format('YYYY-MM-DD');

            $(dateRange).on('apply.daterangepicker', function(ev, picker) {
                const start = picker.startDate.clone().startOf('day');
                const end = picker.endDate.clone().endOf('day');
                $(this).val(start.format('MMM DD') + ' - ' + end.format('MMM DD, YYYY'));
                if (dateRangeStart) dateRangeStart.value = start.format('YYYY-MM-DD');
                if (dateRangeEnd) dateRangeEnd.value = end.format('YYYY-MM-DD');
                filterTable();
            });

            $(dateRange).on('cancel.daterangepicker', function() {
                $(this).val('');
                if (dateRangeStart) dateRangeStart.value = '';
                if (dateRangeEnd) dateRangeEnd.value = '';
                filterTable();
            });
            
            // Apply initial filter with current month data
            setTimeout(function() {
                filterTable();
            }, 100);
        } else {
            console.error('Date range picker could not be initialized. Missing dependencies:', {
                dateRange: !!dateRange,
                moment: typeof moment !== 'undefined',
                daterangepicker: typeof $.fn.daterangepicker !== 'undefined',
                jquery: typeof $ !== 'undefined'
            });
            
            // Fallback: still set initial date values for current month
            if (dateRange && typeof moment !== 'undefined') {
                const startOfMonth = moment().startOf('month');
                const endOfMonth = moment().endOf('month');
                if (dateRangeStart) dateRangeStart.value = startOfMonth.format('YYYY-MM-DD');
                if (dateRangeEnd) dateRangeEnd.value = endOfMonth.format('YYYY-MM-DD');
                setTimeout(function() {
                    filterTable();
                }, 100);
            }
        }
    }
    
    function initializeDataTable() {
        // Check if DataTable is already initialized
        if ($.fn.DataTable.isDataTable('.datatables-basic')) {
            $('.datatables-basic').DataTable().destroy();
        }
        
        // Initialize DataTable with export buttons
        var table = $('.datatables-basic').DataTable({
            responsive: true,
            dom: 'lrtip', // Removed 'B' to hide the buttons, keeping l=length, r=processing, t=table, i=info, p=pagination
            buttons: [
                'copy',
                'csv',
                'excel',
                'pdf',
                'print' // Keep buttons for functionality but don't show them
            ],
            searching: false, // Disable built-in searching since we use custom filters
            language: {
                search: "DataTable Search:",
                searchPlaceholder: "Search all columns...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            lengthMenu: [10, 25, 50, 100], // Customize number of entries per page
            pageLength: 50,
            // order: [[7, 'desc']], // Sort by Created Date column (index 7) in descending order
            columnDefs: [
                {
                    targets: [8, 9], // Negotiation and Actions columns (indices 8 and 9)
                    orderable: false,
                    searchable: false
                },
                {
                    targets: [3, 6], // Guests and Services columns (indices 3 and 6)
                    orderable: false
                }
            ],
            initComplete: function() {
                console.log('DataTable initialized successfully');
            }
        });

        // Custom export button functionality (for the dropdown)
        $('#exportCopy').on('click', function() {
            table.button('.buttons-copy').trigger();
        });

        $('#exportCSV').on('click', function() {
            table.button('.buttons-csv').trigger();
        });

        $('#exportExcel').on('click', function() {
            table.button('.buttons-excel').trigger();
        });

        $('#exportPDF').on('click', function() {
            table.button('.buttons-pdf').trigger();
        });

        $('#exportPrint').on('click', function() {
            table.button('.buttons-print').trigger();
        });
        
        // Modal helper functions for Update Price (New Enquiries)
        window.openNewEnquiryModal = function(button, route) {
            var modalEl = document.getElementById('newEnquiryUpdateModal');
            var form = document.getElementById('newEnquiryUpdateForm');
            var priceInput = document.getElementById('new_enquiry_current_price');
            var commentInput = document.getElementById('new_enquiry_comment');
            var idInput = document.getElementById('new_enquiry_modal_enquiry_id');
            var displayActual = document.getElementById('new_enquiry_display_actual');
            var displayPrice = document.getElementById('new_enquiry_display_price');
            var displayComment = document.getElementById('new_enquiry_display_comment');

            form.action = route || '';
            idInput.value = button.getAttribute('data-enquiry-id') || '';
            var actual = button.getAttribute('data-actual') || '';
            var prevPrice = button.getAttribute('data-price') || '';
            var prevComment = button.getAttribute('data-comment') || '';

            // Set displays
            displayActual.textContent = actual !== '' ? actual : '—';
            displayPrice.textContent = prevPrice !== '' ? prevPrice : '—';
            displayComment.textContent = prevComment !== '' ? prevComment : '—';

            // Prefill price with previous negotiated amount; comment left blank
            priceInput.value = prevPrice;
            commentInput.value = '';
            if (actual !== '') priceInput.setAttribute('max', actual); else priceInput.removeAttribute('max');

            var modal = new bootstrap.Modal(modalEl);
            modal.show();
        };

        window.validateNewEnquiryPrice = function(input) {
            var maxValue = parseFloat(input.getAttribute('max'));
            var currentValue = parseFloat(input.value);
            var warningMessage = document.getElementById('new-enquiry-warning-message');
            
            if (!isNaN(maxValue) && !isNaN(currentValue) && currentValue > maxValue) {
                input.value = maxValue; // Reset to maximum allowed value
                warningMessage.classList.remove('d-none');
                
                setTimeout(function() {
                    warningMessage.classList.add('d-none');
                }, 3000);
            }
        };
    }
</script>
@endsection

@extends('layouts.datatablejs')
