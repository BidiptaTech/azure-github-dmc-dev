@extends('layouts.layout')
@section('title', 'EnquiryList')
@extends('layouts.datatablecss')


@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-1">Enquiry Listing</h4>
                        <p class="text-muted mb-0">Manage and view all agent enquiries</p>
                    </div>
                    <!-- Export Dropdown Button with modern design -->
                    <div class="dropdown">
                        <button class="btn btn-primary btn-sm dropdown-toggle" 
                                type="button" 
                                id="exportDropdown"
                                data-bs-toggle="dropdown" 
                                aria-expanded="false"
                                style="padding: 0.5rem 1rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-download"></i>
                            Export Data
                        </button>
                        <ul class="dropdown-menu shadow-sm" aria-labelledby="exportDropdown">
                            <li><a class="dropdown-item d-flex align-items-center gap-2" href="javascript:void(0);" id="exportCopy">
                                <i class="fas fa-copy"></i> Copy</a></li>
                            <li><a class="dropdown-item d-flex align-items-center gap-2" href="javascript:void(0);" id="exportCSV">
                                <i class="fas fa-file-csv"></i> CSV</a></li>
                            <li><a class="dropdown-item d-flex align-items-center gap-2" href="javascript:void(0);" id="exportExcel">
                                <i class="fas fa-file-excel"></i> Excel</a></li>
                            <li><a class="dropdown-item d-flex align-items-center gap-2" href="javascript:void(0);" id="exportPDF">
                                <i class="fas fa-file-pdf"></i> PDF</a></li>
                            <li><a class="dropdown-item d-flex align-items-center gap-2" href="javascript:void(0);" id="exportPrint">
                                <i class="fas fa-print"></i> Print</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Component -->
        <x-alert />

        <!-- Main Data Table Card -->
        <div class="card">
            <div class="card-datatable">
                <div class="table-container">
                    <table class="datatables-basic table table-bordered collapsed" id="enquiriesTable">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center dtr-control" style="width: 50px;">
                                <i class="fas fa-sort me-1"></i>#
                            </th>
                            <th>Display ID</th>
                            <th>Agent Details</th>
                            <th>Location</th>
                            <th class="text-center">Pax Info</th>
                            <th>Travel Dates</th>
                            <th>Create Tour</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody class="sortable">
                        @foreach($enquiries as $enquiry)
                        <tr class="draggable-row" data-id="{{ $loop->iteration }}">
                            <td class="text-center dtr-control">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="drag-handle me-2"><i class="fas fa-grip-vertical text-muted"></i></span>
                                    <span class="row-number">{{ $loop->iteration }}</span>
                                </div>
                            </td>
                            
                            <td>
                                <div class="d-flex flex-column rounded px-3 py-2" 
                                    style="background: linear-gradient(145deg, #ffffff, #e6e6e6); 
                                            box-shadow: 4px 4px 10px #cfcfcf, -4px -4px 10px #ffffff;">

                                    <!-- Display ID -->
                                    <div class="mb-1">
                                        <span class="fw-semibold text-primary">
                                            {{ $enquiry->display_id ?? 'N/A' }}
                                        </span>
                                    </div>

                                    <!-- Multi Enquiry ID -->
                                    <div>
                                        <span class="text-muted small">Multi Enq ID:</span>
                                        <span class="fw-semibold small" 
                                            style="background: linear-gradient(45deg, #6e5b1b, #a89525, #eead35); 
                                                    -webkit-background-clip: text; 
                                                    -webkit-text-fill-color: transparent;">
                                            {{ $enquiry->multi_enq_id ?? 'N/A' }}
                                        </span>                               
                                    </div>
                                </div>
                            </td>

                            <!-- Enhanced Agent Details Column -->
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-wrapper me-3">
                                        <div class="avatar bg-primary bg-opacity-10 rounded-circle">
                                            <span class="avatar-initial rounded-circle bg-primary">
                                                {{ substr($enquiry->agent->name ?? 'NA', 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold mb-1 text-primary">{{ $enquiry->agent->name ?? 'N/A' }}</span>
                                        <div class="d-flex align-items-center">
                                            {{-- <span class="badge bg-primary bg-opacity-10 text-primary small">
                                                <i class="fas fa-id-badge me-1"></i>
                                                {{ $enquiry->agent->agent_id ?? 'N/A' }}
                                            </span> --}}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Enhanced Location Column -->
                            <td>
                                <div class="location-badge p-2 rounded-3 bg-light">
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold mb-1">
                                            <i class="fas fa-globe-americas text-info me-1"></i>
                                            {{ $enquiry->country ?? 'N/A' }}
                                        </span>
                                        <span class="text-muted small">
                                            <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                            {{ $enquiry->city ?? 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Enhanced Pax Column -->
                            <td>
                                <div class="pax-info-card text-center p-2 rounded-3">
                                    <div class="total-pax mb-2">
                                        <span class="fw-bold fs-5 text-primary">
                                            {{ ($enquiry->adult ?? 0) + ($enquiry->child ?? 0) }}
                                        </span>
                                    </div>
                                    <div class="pax-details d-flex justify-content-center gap-2">
                                        <span class="badge bg-success bg-opacity-10 text-success" title="Adults">
                                            <i class="fas fa-user me-1"></i>{{ $enquiry->adult ?? 0 }}
                                        </span>
                                        <span class="badge bg-info bg-opacity-10 text-info" title="Children">
                                            <i class="fas fa-child me-1"></i>{{ $enquiry->child ?? 0 }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Travel Dates Column -->
                            <td>
                                <div class="d-flex flex-column">
                                    @if ($enquiry->check_in_time)
                                    <div class="mb-1">
                                        <span class="fw-semibold">
                                            <i class="fas fa-calendar-check text-success me-1"></i>
                                            <small class="text-muted">Check In:</small>
                                        </span>
                                        <br>
                                        <span class="fw-medium">
                                            {{ \Carbon\Carbon::parse($enquiry->check_in_time)->format('D, M d, Y') }}
                                        </span>
                                    </div>
                                    @endif
                                    
                                    @if ($enquiry->check_out_time)
                                    <div>
                                        <span class="fw-semibold">
                                            <i class="fas fa-calendar-minus text-danger me-1"></i>
                                            <small class="text-muted">Check Out:</small>
                                        </span>
                                        <br>
                                        <span class="fw-medium">
                                            {{ \Carbon\Carbon::parse($enquiry->check_out_time)->format('D, M d, Y') }}
                                        </span>
                                    </div>
                                    @endif
                                    
                                    @if (!$enquiry->check_in_time && !$enquiry->check_out_time)
                                    <span class="text-muted">N/A</span>
                                    @endif
                                    
                                    @if ($enquiry->check_in_time && $enquiry->check_out_time)
                                    <div class="mt-1">
                                        <small class="badge bg-info bg-opacity-10 text-info">
                                            {{ \Carbon\Carbon::parse($enquiry->check_in_time)->diffInDays(\Carbon\Carbon::parse($enquiry->check_out_time)) + 1 }} days
                                        </small>
                                    </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if(in_array(auth()->user()->role_id, [1, 2, 3, 4, 10, 11, 25, 33, 37, 38, 128, 129, 130, 134, 135, 136, 138]))
                                <a href="{{ route('single-tour-package.create', Crypt::encrypt(['enquiry_id' => $enquiry->enquiry_id])) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i>Create
                                </a>
                                <!-- <button class="btn btn-primary btn-sm create-tour-btn"

                                    data-enquiry-id="{{ $enquiry->enquiry_id }}"
                                    data-agent-id="{{ $enquiry->agent_id }}"
                                    data-destination="{{ $enquiry->country }}"
                                    data-city="{{ $enquiry->city }}"
                                    data-adult="{{ $enquiry->adult }}"
                                    data-child="{{ $enquiry->child }}"
                                    data-infant="{{ $enquiry->infant }}"
                                    data-male="{{ $enquiry->male_count }}"
                                    data-female="{{ $enquiry->female_count }}"
                                    data-check-in="{{ $enquiry->check_in_time ? \Carbon\Carbon::parse($enquiry->check_in_time)->format('d/m/Y') : '' }}"
                                    data-check-out="{{ $enquiry->check_out_time ? \Carbon\Carbon::parse($enquiry->check_out_time)->format('d/m/Y') : '' }}"
                                    data-child-ages="{{ $enquiry->child_ages }}"
                                    data-dmc-id="{{ $enquiry->dmc_id }}"
                                    data-multi-enq-id="{{ $enquiry->multi_enq_id }}"
                                    data-hotel-ids="{{ $enquiry->hotel_ids }}"
                                    data-attraction-ids="{{ $enquiry->attraction_ids }}"
                                    data-restaurant-ids="{{ $enquiry->restaurant_ids }}"
                                    data-guide-ids="{{ $enquiry->guide_ids }}">
                                    <i class="fas fa-plus me-1"></i>Create
                                </button> -->
                                @else
                                <span>Not Authorized</span>
                                @endif
                            </td>
                            <td>{{ $enquiry->created_at->format('d-m-Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .card {
        border-radius: 0.75rem;
        border: none;
        box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
    }

    .table thead th {
        background-color: #f6f7f8;
        border-bottom: none;
        padding: 0.75rem;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #f0f2f4;
    }

    .dropdown-menu {
        padding: 0.5rem 0;
        border: none;
        box-shadow: 0 2px 16px rgba(67, 89, 113, 0.15);
    }

    .dropdown-item {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }

    .dropdown-item:hover {
        background-color: #f6f7f8;
    }

    .btn-primary {
        background-color: #696cff;
        border-color: #696cff;
    }

    .btn-primary:hover {
        background-color: #484bff;
        border-color: #484bff;
    }

    .fw-semibold {
        font-weight: 600 !important;
    }

    .text-muted {
        color: #a1acb8 !important;
    }
    .text-primary {
    /* color: #666cff !important; */
  }

    .card-datatable {
        padding: 1rem;
        overflow: hidden !important;
        width: 100%;
        max-width: 100%;
    }
    
    .table-container {
        width: 100%;
        max-width: 100%;
        overflow: hidden;
        position: relative;
    }
    
    /* Force DataTable to respect container width */
    .dataTables_wrapper {
        width: 100% !important;
    }
    
    .datatables-basic {
        width: 100% !important;
    }
    
    /* Ensure table cells don't exceed container */
    .datatables-basic th,
    .datatables-basic td {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Allow specific columns to wrap */
    .datatables-basic th:nth-child(2),
    .datatables-basic td:nth-child(2),
    .datatables-basic th:nth-child(3),
    .datatables-basic td:nth-child(3) {
        white-space: normal;
        word-wrap: break-word;
    }
    
    /* DataTable responsive control button */
    .datatables-basic td.dtr-control:before,
    .datatables-basic th.dtr-control:before {
        top: 50%;
        left: 10px;
        height: 16px;
        width: 16px;
        margin-top: -8px;
        display: block;
        position: absolute;
        color: white;
        border: 2px solid white;
        border-radius: 50%;
        box-shadow: 0 0 3px rgba(0,0,0,0.3);
        box-sizing: content-box;
        text-align: center;
        text-indent: 0 !important;
        font-family: 'Courier New', Courier, monospace;
        line-height: 12px;
        content: '+';
        background-color: #696cff;
        cursor: pointer;
        z-index: 10;
    }
    
    .datatables-basic td.dtr-control.parent:before {
        content: '-';
        background-color: #dc3545;
    }
    
    /* Ensure the control column has relative positioning */
    .datatables-basic td.dtr-control,
    .datatables-basic th.dtr-control {
        position: relative;
        padding-left: 35px !important;
    }

    /* Custom scrollbar */
    .card-datatable::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .card-datatable::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .card-datatable::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .card-datatable::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Enhanced Table Styles */
    .table {
        border-spacing: 0 0.8rem !important;
        border-collapse: separate !important;
    }

    .table tbody tr {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        margin: 0.5rem 0;
        border: 1px solid transparent;
    }

    .table tbody tr:hover {
        transform: translateY(-3px) scale(1.01);
        box-shadow: 0 8px 25px rgba(67, 89, 113, 0.15);
        border: 1px solid #e7eef8;
        background: linear-gradient(to right, #ffffff, #f8f9ff);
        z-index: 1;
        position: relative;
    }

    /* Enhance cell hover effects */
    .table tbody td {
        position: relative;
        overflow: hidden;
    }

    .table tbody td::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 2px;
        background: #696cff;
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .table tbody tr:hover td::after {
        transform: scaleX(1);
    }

    /* Enhanced Dragging Effects */
    .draggable-row.dragging {
        opacity: 0.9;
        background: #f8f9fa;
        box-shadow: 0 0 15px rgba(67, 89, 113, 0.1);
        border: 2px dashed #696cff;
        animation: pulse 1.5s infinite;
    }

    /* Improved Swap Animation */
    .swap-animation {
        animation: swapRows 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    /* Enhance specific column hover effects */
    .location-badge:hover {
        transform: translateY(-2px);
        background: linear-gradient(to right, #f8f9fa, #ffffff) !important;
    }

    .pax-info-card:hover {
        transform: translateY(-2px);
        background: linear-gradient(to bottom, #f8f9fa, #ffffff);
    }

    /* Enhanced Avatar Hover Effect */
    .avatar-wrapper {
        transition: all 0.3s ease;
    }

    .avatar-wrapper:hover {
        transform: scale(1.1) rotate(5deg);
    }

    /* Improved Badge Hover Effects */
    .badge {
        transition: all 0.3s ease;
    }

    .badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(67, 89, 113, 0.15);
    }

    /* Animation Keyframes */
    @keyframes swapRows {
        0% {
            transform: translateY(0) scale(1);
            background: #ffffff;
        }
        50% {
            transform: translateY(10px) scale(1.02);
            background: #f8f9ff;
            box-shadow: 0 15px 30px rgba(67, 89, 113, 0.2);
        }
        100% {
            transform: translateY(0) scale(1);
            background: #ffffff;
        }
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(105, 108, 255, 0.4);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(105, 108, 255, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(105, 108, 255, 0);
        }
    }

    /* Highlight effect for drag handle */
    .drag-handle {
        position: relative;
        cursor: move;
        opacity: 0;
        transition: all 0.3s ease;
    }

    .draggable-row:hover .drag-handle {
        opacity: 1;
        animation: wiggle 1s ease-in-out infinite;
    }

    @keyframes wiggle {
        0%, 100% { transform: translateY(0); }
        25% { transform: translateY(-2px); }
        75% { transform: translateY(2px); }
    }

    /* Row selection highlight */
    .table tbody tr.selected {
        background: rgba(105, 108, 255, 0.05);
    }

    /* Improve spacing between rows */
    .table tbody tr:not(:last-child) {
        margin-bottom: 0.5rem;
    }

    /* Draggable Row Styles */
    .draggable-row {
        cursor: move;
    }

    .draggable-row.dragging {
        opacity: 0.5;
        background: #f8f9fa;
    }

    .drag-handle {
        cursor: move;
        opacity: 0;
        transition: opacity 0.2s;
    }

    .draggable-row:hover .drag-handle {
        opacity: 1;
    }

    /* Avatar Styles */
    .avatar {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .avatar-initial {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: #fff;
    }

    /* Pax Info Card Styles */
    .pax-info-card {
        background-color: #f8f9fa;
        transition: all 0.3s ease;
    }

    .pax-info-card:hover {
        background-color: #fff;
        box-shadow: 0 2px 8px rgba(67, 89, 113, 0.08);
    }

    /* Location Badge Styles */
    .location-badge {
        transition: all 0.3s ease;
    }

    .location-badge:hover {
        background-color: #fff !important;
        box-shadow: 0 2px 8px rgba(67, 89, 113, 0.08);
    }

    /* Badge Styles */
    .badge {
        padding: 0.4rem 0.8rem;
        font-weight: 500;
    }

    /* Fix for zoom issues */
    .table tbody tr {
        transform-style: preserve-3d;
        backface-visibility: hidden;
        will-change: transform;
        position: relative;
        z-index: 1;
    }

    /* Improved hover effect that works with pagination */
    .table tbody tr {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        background: #fff;
        position: relative;
        z-index: 1;
    }

    .table tbody tr::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to right, #ffffff, #f8f9ff);
        border-radius: 0.5rem;
        z-index: -1;
        opacity: 0;
        transition: all 0.3s ease;
        transform: scale(0.95);
        box-shadow: 0 8px 25px rgba(67, 89, 113, 0.15);
        border: 1px solid #e7eef8;
    }

    .table tbody tr:hover::before {
        opacity: 1;
        transform: scale(1);
    }

    /* Fix for zoom compatibility */
    @media screen and (min-resolution: 1dppx) {
        .table tbody tr:hover {
            transform: translateY(-3px) scale(1.01) !important;
        }
    }

    @media screen and (min-resolution: 2dppx) {
        .table tbody tr:hover {
            transform: translateY(-2px) scale(1.005) !important;
        }
    }

    /* Remove DataTables selection background color */
    table.dataTable tbody tr.selected,
    table.dataTable tbody tr.selected td,
    table.dataTable tbody th.selected,
    table.dataTable tbody td.selected,
    table.dataTable tbody tr.selected:hover,
    table.dataTable tbody tr.selected:hover td {
        background-color: transparent !important;
        color: inherit !important;
    }

    /* Remove any selection color */
    .table tbody tr.selected,
    .table tbody tr.selected td,
    .table tbody tr.even.selected,
    .table tbody tr.odd.selected {
        background-color: transparent !important;
        color: inherit !important;
    }

    /* Override any other DataTables selection styles */
    .dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody > table > tbody > tr.selected > *,
    .dataTables_wrapper .dataTables_scroll div.dataTables_scrollBody > table > tbody > tr > .selected {
        background-color: transparent !important;
    }

    /* Remove hover background for selected rows */
    .table tbody tr.selected:hover,
    .table tbody tr.selected:focus {
        background-color: transparent !important;
    }

    /* Remove any selection outline */
    .table tbody tr:focus,
    .table tbody td:focus {
        outline: none !important;
    }
    
    /* Responsive column collapse indicator */
    .table-responsive.columns-collapsed::before {
        content: '📱 Some columns are collapsed for mobile view';
        display: block;
        background: linear-gradient(45deg, #ff6b6b, #ff8e53);
        color: white;
        padding: 0.5rem 1rem;
        margin-bottom: 1rem;
        border-radius: 0.5rem;
        text-align: center;
        font-size: 0.875rem;
        font-weight: 500;
        box-shadow: 0 2px 8px rgba(255, 107, 107, 0.3);
    }
    
    /* Enhanced responsive table styles */
    .dtr-details {
        background: #f8f9fa;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-top: 0.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .dtr-details dt {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.25rem;
    }
    
    .dtr-details dd {
        margin-bottom: 0.75rem;
        color: #6c757d;
    }
    
    /* Enhanced column collapse functionality */
    .collapsed-columns-info {
        margin-bottom: 1rem;
    }
    
    .collapsed-columns-info .alert {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    /* Responsive breakpoint enhancements */
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.875rem;
        }
        
        .datatables-basic th,
        .datatables-basic td {
            padding: 0.5rem 0.25rem;
        }
        
        .pax-info-card .total-pax {
            font-size: 1rem !important;
        }
        
        .location-badge {
            padding: 0.5rem !important;
        }
        
        /* Enhanced mobile responsive behavior */
        .dtr-bs-modal .modal-dialog {
            max-width: 95vw;
        }
        
        .dtr-bs-modal .modal-body {
            padding: 1rem;
        }
    }
    
    @media (max-width: 576px) {
        .table-responsive {
            font-size: 0.8rem;
        }
        
        .avatar {
            width: 32px !important;
            height: 32px !important;
        }
        
        .badge {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.75rem !important;
        }
        
        /* Mobile-first responsive design */
        .table thead th {
            font-size: 0.75rem;
            padding: 0.5rem 0.25rem;
        }
        
        .table tbody td {
            padding: 0.5rem 0.25rem;
        }
        
        /* Force column collapsing on mobile */
        .datatables-basic.dataTable.dtr-inline.collapsed>tbody>tr>td:first-child:before,
        .datatables-basic.dataTable.dtr-inline.collapsed>tbody>tr>th:first-child:before {
            top: 50%;
            transform: translateY(-50%);
            left: 4px;
            height: 14px;
            width: 14px;
            display: block;
            position: absolute;
            color: white;
            border: 2px solid white;
            border-radius: 14px;
            box-shadow: 0 0 3px #444;
            box-sizing: content-box;
            text-align: center;
            text-indent: 0 !important;
            font-family: 'Courier New', Courier, monospace;
            line-height: 14px;
            content: '+';
            background-color: #696cff;
        }
    }
    
    /* Enhanced responsive modal for collapsed columns */
    .dtr-bs-modal .modal-header {
        background: linear-gradient(45deg, #696cff, #484bff);
        color: white;
        border-bottom: none;
        border-radius: 0.75rem 0.75rem 0 0;
    }
    
    .dtr-bs-modal .modal-title {
        font-weight: 600;
    }
    
    .dtr-bs-modal .modal-body {
        background: #f8f9fa;
    }
    
    .dtr-bs-modal .table {
        margin-bottom: 0;
    }
    
    .dtr-bs-modal .table td {
        border: none;
        padding: 0.75rem;
        background: white;
        border-radius: 0.5rem;
        margin-bottom: 0.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .dtr-bs-modal .table td:first-child {
        font-weight: 600;
        color: #495057;
        background: #e9ecef;
    }
    
    /* Enhanced responsive animations */
    .expanding {
        animation: expandRow 0.3s ease-out;
    }
    
    @keyframes expandRow {
        0% {
            opacity: 0.8;
            transform: scale(0.98);
        }
        100% {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    /* Mobile-specific responsive classes */
    .mobile-xs .table-responsive {
        font-size: 0.75rem;
    }
    
    .mobile-sm .table-responsive {
        font-size: 0.8rem;
    }
    
    .mobile-md .table-responsive {
        font-size: 0.875rem;
    }
    
    /* Enhanced responsive toggle button */
    .dtr-toggle {
        transition: all 0.3s ease;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #696cff;
        color: white;
        border: none;
        cursor: pointer;
    }
    
    .dtr-toggle:hover {
        background: #484bff;
        transform: scale(1.1);
    }
    
    .dtr-toggle:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(105, 108, 255, 0.25);
    }
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script>
    $(document).ready(function() {
        // Wait for any existing DataTable initialization to complete
        setTimeout(function() {
            
            // Check if DataTable is already initialized by the layout
            if ($.fn.DataTable.isDataTable('.datatables-basic')) {
                console.log('DataTable already exists, enhancing it...');
                var table = $('.datatables-basic').DataTable();
                
                // Enable responsive if not already enabled
                if (!table.responsive) {
                    table.destroy();
                    table = $('.datatables-basic').DataTable({
                        responsive: true,
                        columnDefs: [
                            { targets: 0, className: 'dtr-control' },
                            { responsivePriority: 1, targets: 0 }, // # - Always visible
                            { responsivePriority: 2, targets: 1 }, // Display ID - Always visible  
                            { responsivePriority: 3, targets: 2 }, // Agent Details - High priority
                            { responsivePriority: 4, targets: 3 }, // Location - Medium priority
                            { responsivePriority: 5, targets: 4 }, // Pax Info - Medium priority
                            { responsivePriority: 7, targets: 5 }, // Travel Dates - Low priority
                            { responsivePriority: 6, targets: 6 }  // Create Tour - Medium priority
                        ]
                    });
                }
            } else {
                console.log('Initializing new DataTable...');
                var table = $('.datatables-basic').DataTable({
                    responsive: true,
                                            columnDefs: [
                            { targets: 0, className: 'dtr-control' },
                            { responsivePriority: 1, targets: 0 }, // # - Always visible
                            { responsivePriority: 2, targets: 1 }, // Display ID - Always visible  
                            { responsivePriority: 3, targets: 2 }, // Agent Details - High priority
                            { responsivePriority: 4, targets: 3 }, // Location - Medium priority
                            { responsivePriority: 5, targets: 4 }, // Pax Info - Medium priority
                            { responsivePriority: 7, targets: 5 }, // Travel Dates - Low priority
                            { responsivePriority: 6, targets: 6 }  // Create Tour - Medium priority
                        ]
                });
            }

            // Custom export button functionality
            $('#exportCopy').on('click', function() {
                var currentTable = $('.datatables-basic').DataTable();
                if (currentTable.button) {
                    currentTable.button('.buttons-copy').trigger();
                }
            });

            $('#exportCSV').on('click', function() {
                var currentTable = $('.datatables-basic').DataTable();
                if (currentTable.button) {
                    currentTable.button('.buttons-csv').trigger();
                }
            });

            $('#exportExcel').on('click', function() {
                var currentTable = $('.datatables-basic').DataTable();
                if (currentTable.button) {
                    currentTable.button('.buttons-excel').trigger();
                }
            });

            $('#exportPDF').on('click', function() {
                var currentTable = $('.datatables-basic').DataTable();
                if (currentTable.button) {
                    currentTable.button('.buttons-pdf').trigger();
                }
            });

            $('#exportPrint').on('click', function() {
                var currentTable = $('.datatables-basic').DataTable();
                if (currentTable.button) {
                    currentTable.button('.buttons-print').trigger();
                }
            });

            // Handle Create Tour button click
            // $(document).on('click', '.create-tour-btn', function() {
            //     var button = $(this);
            //     var enquiryData = {
            //         enquiry_id: button.data('enquiry-id'),
            //         destination: button.data('destination'),
            //         city: button.data('city'),
            //         adult: button.data('adult'),
            //         child: button.data('child'),
            //         infant: button.data('infant'),
            //         male: button.data('male'),
            //         female: button.data('female'),
            //         check_in: button.data('check-in'),
            //         check_out: button.data('check-out'),
            //         children_ages: button.data('child-ages'),
            //         dmc_id: button.data('dmc-id'),
            //         hotel_ids: button.data('hotel-ids'),
            //         attraction_ids: button.data('attraction-ids'),
            //         restaurant_ids: button.data('restaurant-ids'),
            //         guide_ids: button.data('guide-ids')
            //     };

            //     // Disable button and show loading
            //     button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Creating...');

            //     // Make AJAX call
            //     $.ajax({
            //         url: '{{ route("create.tour") }}',
            //         type: 'POST',
            //         data: enquiryData,
            //         headers: {
            //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            //             'agent-id': button.data('agent-id')
            //         },
            //         success: function(response) {
            //             // Show success message
            //             showToast('success', 'Tour created successfully!');
                        
            //             // Update button to show success
            //             button.removeClass('btn-primary').addClass('btn-success')
            //                   .html('<i class="fas fa-check me-1"></i>Created')
            //                   .prop('disabled', true);
                        
            //             // Refresh the page after a short delay to show the success message
            //             setTimeout(function() {
            //                 location.reload();
            //             }, 500);
            //         },
            //         error: function(xhr, status, error) {
            //             // Re-enable button
            //             button.prop('disabled', false).html('<i class="fas fa-plus me-1"></i>Create');
                        
            //             // Show error message
            //             var errorMessage = 'Failed to create tour';
            //             if (xhr.responseJSON && xhr.responseJSON.message) {
            //                 errorMessage = xhr.responseJSON.message;
            //             }
            //             showToast('error', 'Error', errorMessage);
            //             console.error('Error creating tour:', xhr.responseJSON);
            //         }
            //     });
            // });

            // Toast notification function
            function showToast(type, title, message) {
                // Create toast element
                var toastId = 'toast-' + Date.now();
                var iconClass = type === 'success' ? 'fas fa-check-circle text-success' : 'fas fa-exclamation-circle text-danger';
                var bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
                
                var toastHtml = `
                    <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="${iconClass} me-2"></i>
                                <strong>${title}</strong><br>
                                ${message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;
                
                // Add toast container if it doesn't exist
                if (!$('#toast-container').length) {
                    $('body').append('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>');
                }
                
                // Add toast and show
                $('#toast-container').append(toastHtml);
                var toast = new bootstrap.Toast(document.getElementById(toastId));
                toast.show();
                
                // Remove toast element after it's hidden
                $('#' + toastId).on('hidden.bs.toast', function() {
                    $(this).remove();
                });
            }
            
        }, 500); // Increased delay to ensure layout scripts load first
    });
</script>
@endsection

@extends('layouts.datatablejs')
