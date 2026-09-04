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
                    <table class="datatables-basic table table-bordered collapsed enquiry-table-fixed-cols" id="enquiriesTable" style="table-layout: fixed; width: 100%;"
                    @if(in_array(auth()->user()->role_id, [33, 37, 38, 128, 129, 130, 134, 135, 136, 138])) data-cols="6"
                    @else data-cols="5"
                    @endif>
                    <colgroup>
                        <col style="width: 3%;">
                        <col style="width: 34%;">
                        <col style="width: 15%;">
                        @if(in_array(auth()->user()->role_id, [33, 37, 38, 128, 129, 130, 134, 135, 136, 138]))
                        <col style="width: 14%;">
                        @endif
                        <col style="width: 17%;">
                        <col style="width: 17%;">
                    </colgroup>
                    <thead class="table-light" style="line-height: 1.4;">
                        <tr style="line-height: 1.4;">
                            <th class="text-center col-row-num" style="width: 3%; min-width: 32px; padding: 0.5rem 0.35rem; font-size: 0.8125rem; line-height: 1.4; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">#</th>
                            <th style="padding: 0.5rem 0.5rem; font-size: 0.8125rem; line-height: 1.4; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Tour Details</th>
                            <th style="padding: 0.5rem 0.5rem; font-size: 0.8125rem; line-height: 1.4; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Agent Details</th>
                            @if(in_array(auth()->user()->role_id, [33, 37, 38, 128, 129, 130, 134, 135, 136, 138]))
                                <th style="padding: 0.5rem 0.5rem; font-size: 0.8125rem; line-height: 1.4; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Create Tour</th>
                            @endif
                            <th style="padding: 0.5rem 0.5rem; font-size: 0.8125rem; line-height: 1.4; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Created At</th>
                            <th style="padding: 0.5rem 0.5rem; font-size: 0.8125rem; line-height: 1.4; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Auto Cancel Date</th>
                        </tr>
                    </thead>
                    <tbody class="sortable">
                        @foreach($enquiries as $enquiry)
                        <tr class="draggable-row" data-id="{{ $loop->iteration }}">
                            <td class="text-center col-row-num">
                                <span class="row-number">{{ $loop->iteration }}</span>
                            </td>

                            <!-- Tour Details: Display ID, Location, Pax, Travel Dates -->
                            <td class="tour-details-cell">
                                <div class="d-flex flex-column gap-1 small">
                                    <div>
                                        <span class="fw-semibold text-primary">{{ $enquiry->display_id ?? 'N/A' }}</span>
                                        @if($enquiry->multi_enq_id)
                                        <span class="text-muted ms-1">Multi: {{ $enquiry->multi_enq_id }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <i class="fas fa-globe-americas text-info me-1"></i>{{ $enquiry->country ?? 'N/A' }}
                                        <i class="fas fa-map-marker-alt text-danger ms-2 me-1"></i>{{ $enquiry->city ?? 'N/A' }}
                                    </div>
                                    <div>
                                        <i class="fas fa-user me-1 text-success"></i>{{ $enquiry->adult ?? 0 }}A
                                        <i class="fas fa-child me-1 text-info"></i>{{ $enquiry->child ?? 0 }}C
                                        <i class="fas fa-baby me-1 text-warning"></i>{{ $enquiry->infant ?? 0 }}I
                                    </div>
                                    <div>
                                        @if($enquiry->check_in_time || $enquiry->check_out_time)
                                            @if($enquiry->check_in_time)<strong>In:</strong> {{ \Carbon\Carbon::parse($enquiry->check_in_time)->format('M d, Y') }}@endif
                                            @if($enquiry->check_out_time)<span class="ms-1"><strong>Out:</strong> {{ \Carbon\Carbon::parse($enquiry->check_out_time)->format('M d, Y') }}</span>@endif
                                            @if($enquiry->check_in_time && $enquiry->check_out_time)
                                            <small class="badge bg-info bg-opacity-10 text-info ms-1">{{ \Carbon\Carbon::parse($enquiry->check_in_time)->diffInDays(\Carbon\Carbon::parse($enquiry->check_out_time)) + 1 }}d</small>
                                            @endif
                                        @else
                                        <span class="text-muted">Dates: N/A</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Agent Details Column -->
                            <td>
                                <div class="d-flex align-items-center">
                                   
                                    <div class="d-flex flex-column min-w-0">
                                        <span class="fw-semibold mb-0 text-primary">{{ $enquiry->agent->name ?? 'N/A' }}</span>
                                        <span class="text-muted small">
                                            <i class="fas fa-building me-1"></i>{{ $enquiry->agent->company_name ?? 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            @if(in_array(auth()->user()->role_id, [33, 37, 38, 128, 129, 130, 134, 135, 136, 138]))
                            <td>
                                @if($enquiry->unique_tour_id == null)
                                    <a href="{{ route('single-tour-package.create', Crypt::encrypt(['enquiry_id' => $enquiry->enquiry_id])) }}" class="btn btn-primary btn-sm btn-create-tour-sm" style="padding: 0.35rem 0.5rem; font-size: 0.75rem; line-height: 1.35;">
                                        <i class="fas fa-plus me-1" style="font-size: 0.65rem;"></i>Create Tour
                                    </a>
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success">Tour Created</span>
                                @endif
                            
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
                            </td>
                            @endif
                            <td>
                                <div class="d-flex flex-column">
                                    <span>{{ $enquiry->created_at->format('D,  M d, Y') }}</span>
                                    <small class="text-muted">{{ $enquiry->created_at->format('h:i A') }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    @if($enquiry->auto_cancel_date)
                                        <span class="fw-semibold">
                                            <i class="fas fa-calendar-times text-warning me-1"></i>
                                            {{ \Carbon\Carbon::parse($enquiry->auto_cancel_date)->format('D, M d, Y') }}
                                        </span>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($enquiry->auto_cancel_date)->format('h:i A') }}
                                        </small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </div>
                            </td>
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

    /* Enquiry list table header: compact height, long text shows ellipsis */
    table#enquiriesTable thead th,
    #enquiriesTable.datatables-basic thead th,
    .card-datatable table#enquiriesTable thead th {
        background-color: #f6f7f8 !important;
        border-bottom: none;
        padding: 0.2rem 0.5rem !important;
        font-size: 0.7rem !important;
        font-weight: 600 !important;
        line-height: 1.2 !important;
        height: 1.75rem !important;
        max-height: 1.75rem !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        display: table-cell;
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

    /* Create Tour button: fixed size so it stays same after DataTables redraw/refresh */
    #enquiriesTable .btn-create-tour-sm,
    #enquiriesTable tbody td a[href*="single-tour-package.create"].btn,
    .datatables-basic .btn-create-tour-sm,
    table#enquiriesTable tbody tr td:nth-child(4) a.btn-primary {
        padding: 0.35rem 0.5rem !important;
        font-size: 0.75rem !important;
        line-height: 1.35 !important;
        min-height: unset !important;
    }
    #enquiriesTable .btn-create-tour-sm i,
    #enquiriesTable tbody td a[href*="single-tour-package.create"].btn i,
    .datatables-basic .btn-create-tour-sm i {
        font-size: 0.65rem !important;
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
        table-layout: fixed !important;
    }
    
    /* Lock column widths from first paint so they don't change after DataTables init/refresh */
    #enquiriesTable.enquiry-table-fixed-cols,
    #enquiriesTable.enquiry-table-fixed-cols thead th,
    #enquiriesTable.enquiry-table-fixed-cols tbody td {
        box-sizing: border-box;
    }
    #enquiriesTable[data-cols="6"] th:nth-child(1),
    #enquiriesTable[data-cols="6"] td:nth-child(1) { width: 3% !important; max-width: 3% !important; }
    #enquiriesTable[data-cols="6"] th:nth-child(2),
    #enquiriesTable[data-cols="6"] td:nth-child(2) { width: 34% !important; max-width: 34% !important; }
    #enquiriesTable[data-cols="6"] th:nth-child(3),
    #enquiriesTable[data-cols="6"] td:nth-child(3) { width: 15% !important; max-width: 15% !important; }
    #enquiriesTable[data-cols="6"] th:nth-child(4),
    #enquiriesTable[data-cols="6"] td:nth-child(4) { width: 14% !important; max-width: 14% !important; }
    #enquiriesTable[data-cols="6"] th:nth-child(5),
    #enquiriesTable[data-cols="6"] td:nth-child(5) { width: 17% !important; max-width: 17% !important; }
    #enquiriesTable[data-cols="6"] th:nth-child(6),
    #enquiriesTable[data-cols="6"] td:nth-child(6) { width: 17% !important; max-width: 17% !important; }
    #enquiriesTable[data-cols="5"] th:nth-child(1),
    #enquiriesTable[data-cols="5"] td:nth-child(1) { width: 3% !important; max-width: 3% !important; }
    #enquiriesTable[data-cols="5"] th:nth-child(2),
    #enquiriesTable[data-cols="5"] td:nth-child(2) { width: 34% !important; max-width: 34% !important; }
    #enquiriesTable[data-cols="5"] th:nth-child(3),
    #enquiriesTable[data-cols="5"] td:nth-child(3) { width: 15% !important; max-width: 15% !important; }
    #enquiriesTable[data-cols="5"] th:nth-child(4),
    #enquiriesTable[data-cols="5"] td:nth-child(4) { width: 16% !important; max-width: 16% !important; }
    #enquiriesTable[data-cols="5"] th:nth-child(5),
    #enquiriesTable[data-cols="5"] td:nth-child(5) { width: 17% !important; max-width: 17% !important; }
    
    /* Ensure table cells don't exceed container */
    .datatables-basic th,
    .datatables-basic td {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Tour Details and Agent Details body cells can wrap; header stays single line with ellipsis */
    .datatables-basic .tour-details-cell,
    .datatables-basic td:nth-child(2),
    .datatables-basic td:nth-child(3) {
        white-space: normal;
        word-wrap: break-word;
    }
    
    /* # column: always show row number and drag handle */
    .datatables-basic td.col-row-num,
    .datatables-basic th.col-row-num {
        width: 3% !important;
        min-width: 32px !important;
        max-width: 40px !important;
    }
    /* Tour Details: fixed 34%; Create Tour 14%; Created At / Auto Cancel 17% each */
    .datatables-basic .tour-details-cell,
    .datatables-basic th:nth-child(2),
    .datatables-basic td:nth-child(2) {
        width: 34% !important;
        max-width: 34% !important;
    }
    .datatables-basic th:nth-last-child(1),
    .datatables-basic td:nth-last-child(1),
    .datatables-basic th:nth-last-child(2),
    .datatables-basic td:nth-last-child(2) {
        width: 14% !important;
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
        
        /* Keep enquiry list header compact on mobile too */
        table#enquiriesTable thead th,
        #enquiriesTable thead th {
            font-size: 0.7rem !important;
            padding: 0.2rem 0.5rem !important;
            max-height: 1.75rem !important;
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
        // Run immediately (setTimeout 0) so column widths don't flash: destroy layout's DataTable and re-init with fixed columns.
        var tableEl = $('#enquiriesTable');
        if (!tableEl.length) return;
        if ($.fn.DataTable.isDataTable(tableEl)) {
            tableEl.DataTable().destroy();
        }
        tableEl.DataTable({
                responsive: false,
                autoWidth: false,
                columnDefs: getColumnDefs(),
                buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
                language: { search: "_INPUT_", searchPlaceholder: "Search..." },
                lengthMenu: [10, 25, 50, 100],
                drawCallback: function() {
                    updateRowNumbers();
                    applyCreateTourButtonSize();
                }
        });
        
        function applyCreateTourButtonSize() {
                $('#enquiriesTable tbody a[href*="single-tour-package.create"].btn').css({
            'padding': '0.35rem 0.5rem',
            'font-size': '0.75rem',
            'line-height': '1.35'
        }).find('i').css('font-size', '0.65rem');
        }
        
        setTimeout(function() {
            updateRowNumbers();
            applyCreateTourButtonSize();
        }, 50);
        
        // Function to update row numbers after DataTable operations
        function updateRowNumbers() {
            var table = $('#enquiriesTable').DataTable();
                var info = table.page.info();
                var start = info.start;
                
                // Update row numbers for visible rows
                table.rows({page: 'current'}).every(function(rowIdx, tableLoop, rowLoop) {
                    var node = this.node();
                    var rowNumber = start + rowLoop + 1;
                    var rowNumberSpan = $(node).find('.row-number');
                    
                    if (rowNumberSpan.length) {
                        rowNumberSpan.text(rowNumber);
                    } else {
                        var firstCell = $(node).find('td:first');
                        if (firstCell.length) {
                            firstCell.html('<span class="row-number">' + rowNumber + '</span>');
                        }
                    }
                });
                
                // Also update the data-id attribute for drag and drop
                table.rows({page: 'current'}).every(function(rowIdx, tableLoop, rowLoop) {
                    var node = this.node();
                    var rowNumber = start + rowLoop + 1;
                    $(node).attr('data-id', rowNumber);
                });
            }
            
            // Function to get column definitions – widths must match colgroup or DataTables overrides
            function getColumnDefs() {
                @if(in_array(auth()->user()->role_id, [33, 37, 38, 128, 129, 130, 134, 135, 136, 138]))
                return [
                    { targets: 0, width: '3%', className: 'col-row-num' },
                    { targets: 1, width: '34%', className: 'tour-details-cell' },
                    { targets: 2, width: '15%' },
                    { targets: 3, width: '14%' },
                    { targets: 4, width: '17%' },
                    { targets: 5, width: '17%' }
                ];
                @else
                return [
                    { targets: 0, width: '3%', className: 'col-row-num' },
                    { targets: 1, width: '34%', className: 'tour-details-cell' },
                    { targets: 2, width: '15%' },
                    { targets: 3, width: '16%' },
                    { targets: 4, width: '17%' }
                ];
                @endif
            }
            
            // Add event listeners for DataTable events to update row numbers
            var table = $('#enquiriesTable').DataTable();
            
            // Update row numbers on page change
            table.on('page.dt', function() {
                setTimeout(updateRowNumbers, 100);
            });
            
            // Update row numbers on search
            table.on('search.dt', function() {
                setTimeout(updateRowNumbers, 100);
            });
            
            // Update row numbers on length change
            table.on('length.dt', function() {
                setTimeout(updateRowNumbers, 100);
            });
            
            // Update row numbers on order change
            table.on('order.dt', function() {
                setTimeout(updateRowNumbers, 100);
            });
            
            // Initial row number update after a short delay to ensure DOM is ready
            setTimeout(function() {
                updateRowNumbers();
            }, 200);
            
            // Additional update on window load to handle complete page refresh
            $(window).on('load', function() {
                setTimeout(function() {
                    updateRowNumbers();
                    applyCreateTourButtonSize();
                }, 100);
            });
            
            // Initialize Sortable for drag and drop functionality
            setTimeout(function() {
                var sortableElement = document.querySelector('.sortable');
                if (sortableElement && typeof Sortable !== 'undefined') {
                    var sortable = Sortable.create(sortableElement, {
                        handle: '.col-row-num',
                        animation: 150,
                        onEnd: function(evt) {
                            // Update row numbers after drag and drop
                            setTimeout(function() {
                                updateRowNumbers();
                            }, 100);
                        }
                    });
                }
            }, 300);
            
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
