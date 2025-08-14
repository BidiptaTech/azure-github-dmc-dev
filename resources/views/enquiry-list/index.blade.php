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
            <div class="card-datatable table-responsive">
                <table class="datatables-basic table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">
                                <i class="fas fa-sort me-1"></i>#
                            </th>
                            <th style="min-width: 140px;">Display ID</th>
                            <th style="min-width: 200px;">Agent Details</th>
                            <th style="min-width: 180px;">Location</th>
                            <th class="text-center" style="width: 120px;">Pax Info</th>
                            <th style="min-width: 200px;">Check In</th>
                            <th style="min-width: 200px;">Check Out</th>
                        </tr>
                    </thead>
                    <tbody class="sortable">
                        @foreach($enquiries as $enquiry)
                        <tr class="draggable-row" data-id="{{ $loop->iteration }}">
                            <td class="text-center">
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

                            <!-- Check In Column -->
                            <td>
                                @if ($enquiry->check_in_time)
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold">
                                        <i class="fas fa-calendar-check text-success me-1"></i>
                                        {{ \Carbon\Carbon::parse($enquiry->check_in_time)->format('D, F d, Y') }}
                                    </span>
                                    <span class="text-muted small">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ \Carbon\Carbon::parse($enquiry->check_in_time)->format('h:i A') }}
                                    </span>
                                </div>
                                @else
                                <span class="text-muted">N/A</span>
                                @endif
                            </td>

                            <!-- Check Out Column -->
                            <td>
                                @if ($enquiry->check_out_time)
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold">
                                        <i class="fas fa-calendar-minus text-danger me-1"></i>
                                        {{ \Carbon\Carbon::parse($enquiry->check_out_time)->format('D, F d, Y') }}
                                    </span>
                                    <span class="text-muted small">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ \Carbon\Carbon::parse($enquiry->check_out_time)->format('h:i A') }}
                                    </span>
                                </div>
                                @else
                                <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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

    .table-responsive {
        padding: 1rem;
    }

    /* Custom scrollbar */
    .table-responsive::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
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
</style>
@endsection

@section('scripts')
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script>
    $(document).ready(function() {
        // Function to initialize hover effects
        function initializeHoverEffects() {
            $('.draggable-row').hover(
                function() {
                    $(this).addClass('row-hover');
                    $(this).prev().css('transform', 'translateY(-2px)');
                    $(this).next().css('transform', 'translateY(2px)');
                },
                function() {
                    $(this).removeClass('row-hover');
                    $(this).prev().css('transform', '');
                    $(this).next().css('transform', '');
                }
            );

            // Reinitialize click effects
            $('.draggable-row').click(function() {
                $('.draggable-row').removeClass('selected');
                $(this).addClass('selected');
            });
        }

        // Initialize DataTable with enhanced configuration
        const table = $('.datatables-basic').DataTable({
            responsive: true,
            ordering: true,
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            select: false,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search enquiries...",
                lengthMenu: "Show _MENU_ entries per page",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    first: '<i class="fas fa-angle-double-left"></i>',
                    previous: '<i class="fas fa-angle-left"></i>',
                    next: '<i class="fas fa-angle-right"></i>',
                    last: '<i class="fas fa-angle-double-right"></i>'
                }
            },
            lengthMenu: [
                [10, 25, 50, 100],
                ['10 rows', '25 rows', '50 rows', '100 rows']
            ],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                '<"row"<"col-sm-12"tr>>' +
                '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            
            // Add drawCallback to reinitialize hover effects after pagination
            drawCallback: function() {
                initializeHoverEffects();
            }
        });

        // Initialize hover effects on first load
        initializeHoverEffects();

        // Reinitialize hover effects when page length changes
        $('.dataTables_length select').on('change', function() {
            setTimeout(initializeHoverEffects, 100);
        });

        // Reinitialize hover effects when search is performed
        $('.dataTables_filter input').on('keyup', function() {
            setTimeout(initializeHoverEffects, 100);
        });

        // Handle zoom level changes
        let lastZoom = 100;
        
        function handleZoom() {
            const currentZoom = Math.round(window.devicePixelRatio * 100);
            
            if (currentZoom !== lastZoom) {
                lastZoom = currentZoom;
                adjustHoverEffects(currentZoom);
            }
        }

        function adjustHoverEffects(zoomLevel) {
            const scale = Math.max(1.01 - (zoomLevel - 100) * 0.0005, 1.001);
            const yOffset = Math.max(3 - (zoomLevel - 100) * 0.02, 1);

            document.documentElement.style.setProperty('--hover-scale', scale.toString());
            document.documentElement.style.setProperty('--hover-y-offset', `-${yOffset}px`);
        }

        // Listen for zoom changes
        window.addEventListener('resize', handleZoom);
        
        // Check for zoom changes periodically
        setInterval(handleZoom, 1000);

        // Export button handlers
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

        // Initialize Sortable with enhanced configuration
        new Sortable(document.querySelector('.sortable'), {
            handle: '.drag-handle',
            animation: 150,
            dragClass: 'dragging',
            ghostClass: 'drag-ghost',
            chosenClass: 'drag-chosen',
            onStart: function(evt) {
                const row = evt.item;
                row.classList.add('dragging');
                document.querySelectorAll('.draggable-row:not(.dragging)').forEach(r => {
                    r.style.transform = 'scale(0.98)';
                });
            },
            onEnd: function(evt) {
                const row = evt.item;
                row.classList.remove('dragging');
                document.querySelectorAll('.draggable-row').forEach(r => {
                    r.style.transform = '';
                });

                const rows = Array.from(evt.to.children);
                rows.forEach((row, index) => {
                    const rowNumber = index + 1;
                    const numberElement = row.querySelector('.row-number');
                    
                    numberElement.style.transform = 'scale(1.2)';
                    numberElement.style.color = '#696cff';
                    numberElement.textContent = rowNumber;
                    
                    row.classList.add('swap-animation');
                    
                    setTimeout(() => {
                        numberElement.style.transform = '';
                        numberElement.style.color = '';
                        row.classList.remove('swap-animation');
                    }, 600);
                });

                // Reinitialize hover effects after sorting
                setTimeout(initializeHoverEffects, 100);
            }
        });

        // Add CSS variables for dynamic hover effects
        document.documentElement.style.setProperty('--hover-scale', '1.01');
        document.documentElement.style.setProperty('--hover-y-offset', '-3px');

        // Add these CSS rules dynamically
        const style = document.createElement('style');
        style.textContent = `
            .table tbody tr:hover {
                transform: translateY(var(--hover-y-offset)) scale(var(--hover-scale)) !important;
            }
        `;
        document.head.appendChild(style);

        // Remove any existing selection classes when clicking
        $('.table tbody').on('click', 'tr', function() {
            $(this).removeClass('selected');
            $(this).find('td').removeClass('selected');
        });
    });
</script>
@endsection