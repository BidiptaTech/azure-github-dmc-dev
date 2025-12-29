    @extends('layouts.layout')
@section('title', 'Enquiry Pro')
@extends('layouts.datatablecss')
@section('css')
<style>
    /* Hide footer on Enquiry Pro page */
    footer.footer {
        display: none !important;
    }

    /* Hide navigation tabs */
    .nav-tabs-custom {
        display: none !important;
    }

    /* Hide navbar user dropdown */
    .navbar-nav-right {
        display: none !important;
    }

    /* Hide entire navbar */
    #layout-navbar {
        display: none !important;
    }

    /* Remove top padding from layout-page */
    .layout-navbar-fixed .layout-wrapper:not(.layout-without-menu) .layout-page {
        padding-top: 0 !important;
    }
    
    .layout-page {
        padding-top: 0 !important;
    }

    /* Main Container - Full Height */
    .enquiry-pro-container {
        display: flex;
        flex-direction: column;
        height: 100vh;
        overflow: hidden;
        padding: 0;
        margin: 0;
    }

    /* Fixed Top Header (Red Box) - Exactly 2 Rows Minimum */
    .enquiry-pro-header {
        position: sticky;
        top: 0;
        z-index: 100;
        background: #fff;
        border-bottom: 3px solid #ffe69c;
        padding: 3px 4px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        display: flex !important;
        flex-direction: column !important;
        min-height: 60px;
        gap: 0;
    }
    
    /* Row 1 Header Container - Contains Navigation Tabs + Customer Fields Side by Side */
    .row-1-header {
        display: flex;
        flex-direction: row;
        align-items: center;
        width: 100%;
        flex: 0 0 auto;
        margin-bottom: 2px;
        gap: 8px;
    }
    
    /* Navigation tabs take available space */
    .row-1-header .nav-tabs-custom {
        flex: 1 1 auto;
        margin-bottom: 0;
    }
    
    /* Row 1 Fields - Customer fields on the right side */
    .row-1-fields {
        flex: 0 0 auto;
        padding: 2px 4px;
        background: linear-gradient(135deg, #fff9e6 0%, #fffbf0 100%);
        border-radius: 3px;
        border: 1px solid #ffd966;
    }
    
    .row-1-fields .row {
        margin: 0;
        flex-wrap: nowrap;
        align-items: center;
    }
    
    .row-1-fields .col-auto {
        flex-shrink: 0;
    }
    
    /* Force customer-details to be on its own row (Row 2) */
    .enquiry-pro-header > .customer-details {
        display: flex !important;
        width: 100% !important;
        flex: 0 0 auto;
        float: none !important;
        clear: both !important;
    }
    
    /* Override any Bootstrap inline styles */
    .enquiry-pro-header .nav,
    .enquiry-pro-header .nav-tabs {
        display: flex !important;
        width: 100% !important;
    }

    /* Navigation Tabs - Row 1 - Minimum Height - Force Block Display */
    .nav-tabs-custom {
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 3px;
        padding-bottom: 3px;
        display: flex !important;
        flex-wrap: nowrap;
        align-items: center;
        min-height: 26px;
        flex-shrink: 0;
        width: 100%;
        box-sizing: border-box;
    }
    
    /* Ensure ul is block-level and reset Bootstrap defaults */
    .enquiry-pro-header ul.nav-tabs-custom {
        display: flex !important;
        list-style: none;
        margin: 0 !important;
        padding: 0 !important;
        width: 100%;
    }
    
    .enquiry-pro-header > .nav-tabs-custom {
        display: flex !important;
        margin-bottom: 3px !important;
    }
    
    /* Reset any Bootstrap nav styles that might interfere */
    .enquiry-pro-header .nav {
        margin-bottom: 0;
        padding-left: 0;
    }

    .nav-tabs-custom .nav-item {
        flex-shrink: 0;
    }

    .nav-tabs-custom .nav-link {
        border: none;
        color: #495057;
        font-weight: 500;
        padding: 3px 8px;
        margin-right: 1px;
        border-radius: 0;
        font-size: 10px;
        border-bottom: 2px solid transparent;
        line-height: 1.3;
        white-space: nowrap;
        min-height: 20px;
        display: flex;
        align-items: center;
    }

    .nav-tabs-custom .nav-link.active {
        color: #0d6efd;
        border-bottom: 2px solid #0d6efd;
        background: transparent;
        font-weight: 600;
    }

    .status-badge {
        background: #28a745;
        color: white;
        padding: 3px 8px;
        border-radius: 3px;
        font-weight: 700;
        font-size: 9px;
        line-height: 1.3;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        min-height: 18px;
    }
    
    .form-check {
        display: flex;
        align-items: center;
        margin: 0;
        min-height: 20px;
    }
    
    .form-check-label {
        font-size: 9px;
        white-space: nowrap;
        margin: 0;
        padding-left: 4px;
        line-height: 1.3;
    }
    
    .form-check-input {
        width: 12px;
        height: 12px;
        margin: 0;
        flex-shrink: 0;
    }

    /* Customer Details Section - Row 2 - Minimum Height - Force Block Display */
    .customer-details {
        background: linear-gradient(135deg, #fff9e6 0%, #fffbf0 100%);
        padding: 3px 4px;
        border-radius: 3px;
        font-size: 9px;
        border: 1px solid #ffd966;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        margin-top: 0;
        min-height: 26px;
        flex-shrink: 0;
        display: flex !important;
        align-items: center;
        width: 100%;
        box-sizing: border-box;
        clear: both;
    }
    
    /* Beautiful Row 2 Styling */
    .row-2-beautiful {
        background: linear-gradient(135deg, #fff9e6 0%, #fffbf0 100%);
        padding: 0 !important;
        border-radius: 3px;
        border: 1px solid #ffd966;
        min-height: 50px;
    }
    
    .row-2-beautiful .row {
        margin: 0;
        align-items: stretch;
        overflow-x: visible;
    }
    
    /* Make row scrollable when child/infant fields are present */
    .row-2-beautiful.scrollable .row {
        overflow-x: auto;
        overflow-y: hidden;
        flex-wrap: nowrap;
    }
    
    .row-2-beautiful.scrollable .row::-webkit-scrollbar {
        height: 6px;
    }
    
    .row-2-beautiful.scrollable .row::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    .row-2-beautiful.scrollable .row::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }
    
    .row-2-beautiful.scrollable .row::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    /* Remove margin-top from col-auto rows > * */
    .row-2-beautiful .col-auto .row > * {
        margin-top: 0 !important;
    }
    
    /* Field Groups */
    .field-group {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        gap: 8px;
        padding: 0;
        background: rgba(255,255,255,0.7);
        border-radius: 5px;
        border: 1px solid rgba(0,0,0,0.08);
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        overflow-x: auto;
    }
    
    .field-group:hover {
        background: rgba(255,255,255,0.95);
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        transform: translateY(-1px);
    }
    
    .pax-group {
        background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
        border-color: #c8e6c9;
    }
    
    .date-group {
        background: linear-gradient(135deg, #e3f2fd 0%, #e8f4fd 100%);
        border-color: #90caf9;
    }
    
    /* Destination column - fixed width of 305px to prevent scrollbar initially */
    .row-2-beautiful .row .col:last-child {
        flex: 0 0 305px;
        width: 305px;
        max-width: 305px;
    }
    
    /* Destination group - maintains fixed width */
    .destination-group {
        background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
        border-color: #ffcc80;
        flex: 0 0 auto;
        width: 100%;
    }
    
    /* When scrollable, row becomes scrollable but destination stays fixed at 305px */
    .row-2-beautiful.scrollable .row {
        display: flex;
        overflow-x: auto;
        overflow-y: hidden;
    }
    
    /* When scrollable, pax and date groups can scroll */
    .row-2-beautiful.scrollable .row .col-auto {
        flex: 0 0 auto;
    }
    
    /* Destination stays fixed on the right when scrollable - maintains 305px width */
    .row-2-beautiful.scrollable .row .col:last-child {
        flex: 0 0 305px;
        width: 305px;
        max-width: 305px;
        position: sticky;
        right: 0;
        background: linear-gradient(135deg, #fff9e6 0%, #fffbf0 100%);
        z-index: 10;
        box-shadow: -2px 0 4px rgba(0,0,0,0.1);
    }
    
    /* Ensure pax-group and date-group can scroll when scrollable */
    .row-2-beautiful.scrollable .pax-group,
    .row-2-beautiful.scrollable .date-group {
        flex: 0 0 auto;
        min-width: fit-content;
    }
    
    /* Field Items */
    .field-item {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 4px 6px;
        background: white;
        border-radius: 4px;
        border: 1px solid rgba(0,0,0,0.1);
        transition: all 0.2s ease;
        flex-shrink: 0;
        white-space: nowrap;
    }
    
    .field-item:hover {
        border-color: #2196f3;
        box-shadow: 0 2px 4px rgba(33,150,243,0.2);
    }
    
    .field-item.full-width {
        width: 100%;
        flex: 1;
    }
    
    /* Nights Display */
    .nights-display {
        font-weight: 700;
        color: #1565c0;
        font-size: 11px;
        padding: 2px 6px;
        background: rgba(21, 101, 192, 0.1);
        border-radius: 3px;
        min-width: 30px;
        text-align: center;
        display: inline-block;
    }
    
    /* Field Icons */
    .field-icon {
        font-size: 14px;
        color: #2196f3;
        display: flex;
        align-items: center;
        flex-shrink: 0;
    }
    
    .pax-group .field-icon {
        color: #4caf50;
    }
    
    .date-group .field-icon {
        color: #2196f3;
    }
    
    .destination-group .field-icon {
        color: #ff9800;
    }
    
    /* Beautiful Inputs */
    .beautiful-input {
        border: 1px solid #e0e0e0 !important;
        border-radius: 4px !important;
        padding: 4px 8px !important;
        font-size: 10px !important;
        background: white !important;
        transition: all 0.2s ease !important;
        min-width: 50px;
    }
    
    .beautiful-input:focus {
        border-color: #2196f3 !important;
        box-shadow: 0 0 0 3px rgba(33,150,243,0.1) !important;
        outline: none !important;
    }
    
    .beautiful-input:hover {
        border-color: #90caf9 !important;
    }
    
    /* Labels in Row 2 */
    .row-2-beautiful .detail-label {
        font-size: 10px;
        font-weight: 600;
        color: #424242;
        white-space: nowrap;
        min-width: fit-content;
    }
    
    /* Number inputs specific styling */
    .pax-group .beautiful-input {
        width: 45px;
        text-align: center;
        font-weight: 600;
        color: #2e7d32;
    }
    
    /* Date inputs specific styling */
    .date-group .beautiful-input {
        width: 125px;
        color: #1565c0;
    }
    
    /* Destination input styling */
    .destination-group .beautiful-input {
        min-width: 180px;
        color: #e65100;
    }
    
    /* Dynamic Details Containers - All in Row */
    .adult-details-container,
    .child-details-container,
    .infant-details-container {
        display: flex;
        flex-direction: row;
        gap: 8px;
        margin: 0;
        padding: 0;
        background: transparent;
        border: none;
        width: auto;
        flex-shrink: 0;
    }
    
    .adult-details-container .field-item,
    .child-details-container .field-item,
    .infant-details-container .field-item {
        margin: 0;
        flex-shrink: 0;
        padding: 4px 6px;
    }
    
    /* Make pax-group display all in one row */
    .pax-group {
        flex-direction: row;
        align-items: center;
    }
    
    .child-details-container .field-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .infant-details-container label {
        display: flex;
        align-items: center;
        margin: 0;
        font-size: 10px;
    }
    
    .enquiry-pro-header > .customer-details {
        display: flex !important;
    }

    .customer-details .row {
        margin: 0;
        width: 100%;
        flex-wrap: nowrap;
        overflow-x: auto;
        min-height: 20px;
        align-items: center;
    }
    
    .customer-details .col-auto,
    .customer-details .col {
        flex-shrink: 0;
    }

    .detail-label {
        font-size: 9px;
        font-weight: 600;
        margin: 0;
        white-space: nowrap;
        color: #495057;
        letter-spacing: 0.2px;
    }
    
    .customer-details .form-control-sm,
    .customer-details .form-select-sm {
        border: 1px solid #dee2e6;
        background: #ffffff;
        transition: all 0.2s ease;
    }
    
    .customer-details .form-control-sm:focus,
    .customer-details .form-select-sm:focus {
        border-color: #ffc107;
        box-shadow: 0 0 0 0.15rem rgba(255, 193, 7, 0.15);
    }
    
    .customer-details .col-auto {
        flex: 0 0 auto;
        padding: 0 4px;
    }
    
    .customer-details .col {
        flex: 1 1 auto;
        padding: 0 4px;
        min-width: 0;
    }

    .customer-details input[type="radio"] {
        width: 12px;
        height: 12px;
        margin-right: 2px;
    }

    .customer-details label {
        font-size: 9px;
        margin: 0;
        display: flex;
        align-items: center;
        white-space: nowrap;
    }

    .customer-details .form-control-sm,
    .customer-details .form-select-sm {
        height: 20px;
        padding: 1px 3px;
        font-size: 9px;
        border: 1px solid #ced4da;
        border-radius: 2px;
        min-width: 0;
    }
    
    .customer-details .gap-1 {
        gap: 2px !important;
    }
    
    .customer-details .d-flex {
        flex-shrink: 1;
        min-width: 0;
    }

    /* Scrollable Middle Content */
    .enquiry-pro-content {
        flex: 1;
        overflow-y: auto;
        padding: 8px 12px;
        background: #f8f9fa;
    }

    /* Section Styling - Compact */
    .section-card {
        background: white;
        border-radius: 3px;
        margin-bottom: 5px;
        border: 1px solid #dee2e6;
        overflow: hidden;
    }

    .section-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2px 12px;
        font-weight: 600;
        font-size: 13px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        line-height: 1.3;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .section-body {
        padding: 0;
        max-height: 160px; /* 4 rows max (header + 4 data rows) */
        overflow-y: auto;
        overflow-x: auto;
    }

    /* Table Styling - Compact */
    .table-custom {
        width: 100%;
        font-size: 10px;
        margin: 0;
    }

    .table-custom th {
        background: #e9ecef;
        font-weight: 600;
        padding: 4px 6px !important;
        border-bottom: 1px solid #dee2e6;
        white-space: nowrap;
        font-size: 11px;
        line-height: 1.3;
    }

    .table-custom thead tr th {
        padding: 2px 6px !important;
    }

    .table-custom td {
        padding: 2px 6px !important;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
        font-size: 11px;
        line-height: 1.3;
    }

    .table-custom tbody tr {
        height: 25px;
    }

    .table-custom input[type="number"],
    .table-custom input[type="text"] {
        width: 55px;
        padding: 1px 3px;
        border: 1px solid #ced4da;
        border-radius: 2px;
        font-size: 10px;
        height: 20px;
    }

    .table-custom input[type="checkbox"] {
        width: 13px;
        height: 13px;
        cursor: pointer;
    }

    /* Fixed Bottom Action Bar (Red Box) - Compact */
    .enquiry-pro-footer {
        position: sticky;
        bottom: 0;
        z-index: 100;
        background: #fff9e6;
        border: 1px solid #ffe69c;
        border-top: 2px solid #ffe69c;
        padding: 6px 12px;
        box-shadow: 0 -2px 6px rgba(0,0,0,0.1);
    }

    /* Charges Section inside Red Box */
    .charges-section {
        background: #fff9e6;
        border: 1px solid #ffe69c;
        border-radius: 4px;
        padding: 8px 12px;
        margin-bottom: 10px;
    }

    .charges-section label {
        display: inline-flex;
        align-items: center;
        font-size: 11px;
    }

    .action-buttons {
        display: flex;
        gap: 4px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .action-buttons .btn {
        padding: 4px 10px;
        font-weight: 500;
        border-radius: 3px;
        font-size: 11px;
        line-height: 1.3;
    }
    
    .action-buttons .btn-sm {
        padding: 3px 8px;
        font-size: 10px;
        line-height: 1.2;
    }

    .btn-xs {
        padding: 4px 10px !important;
        font-size: 11px !important;
        line-height: 1.2 !important;
    }

    /* Form Controls - Compact */
    .form-control-sm, .form-select-sm {
        font-size: 10px;
        padding: 3px 5px;
        padding-block: 4px;
        height: 24px;
        line-height: 1.2;
    }

    .form-label {
        font-size: 10px;
        margin-bottom: 2px;
        font-weight: 600;
        line-height: 1.2;
    }

    .form-check-label {
        font-size: 10px;
        line-height: 1.2;
    }

    .form-check-input {
        width: 14px;
        height: 14px;
        margin-top: 0;
    }

    /* Custom Scrollbar */
    .section-body::-webkit-scrollbar,
    .enquiry-pro-content::-webkit-scrollbar {
        width: 5px;
        height: 5px;
    }

    .section-body::-webkit-scrollbar-track,
    .enquiry-pro-content::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .section-body::-webkit-scrollbar-thumb,
    .enquiry-pro-content::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .section-body::-webkit-scrollbar-thumb:hover,
    .enquiry-pro-content::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Remove excess spacing */
    .row {
        margin: 0;
    }

    .col-md-2, .col-md-3, .col-md-6 {
        padding: 0 4px;
    }

    /* Empty section placeholder */
    .empty-section {
        padding: 10px;
        text-align: center;
        color: #6c757d;
        font-size: 10px;
    }

    /* Additional spacing reductions */
    .nav-item {
        margin-bottom: 0;
    }

    .alert {
        padding: 5px 8px;
        margin-bottom: 0;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .enquiry-pro-container {
            height: calc(100vh - 60px);
        }
        
        .section-body {
            max-height: 200px;
        }
    }

    /* miscellaneous section */
    input.form-control.form-control-sm.misc-adult-charge, input.form-control.form-control-sm.misc-adult-qty, input.form-control.form-control-sm.misc-child-charge, input.form-control.form-control-sm.misc-child-qty, input.form-control.form-control-sm.misc-infant-charge, input.form-control.form-control-sm.misc-infant-qty, input.form-control.form-control-sm.misc-sell-adult, input.form-control.form-control-sm.misc-sell-child, input.form-control.form-control-sm.misc-sell-infant {
        min-height: 0px !important;
    }

    /* Destination tags styling */
    .destination-tags-container {
        border: 1px solid #dee2e6 !important;
        overflow-x: auto !important;
        overflow-y: hidden !important;
        position: relative;
        display: flex !important;
        flex-wrap: nowrap !important;
        align-items: center !important;
        gap: 4px !important;
        padding: 4px !important;
        min-height: 38px !important;
        max-height: 38px !important;
    }
    
    /* Hide scrollbar for cleaner look but keep functionality */
    .destination-tags-container::-webkit-scrollbar {
        height: 4px;
    }
    
    .destination-tags-container::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .destination-tags-container::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 2px;
    }
    
    .destination-tags-container::-webkit-scrollbar-thumb:hover {
        background: #a0aec0;
    }
    
    .destination-tags-container:focus-within {
        border-color: #667eea !important;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .destination-tag {
        display: inline-flex !important;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        margin: 0 !important;
        background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        white-space: nowrap;
        flex-shrink: 0;
    }
    
    .destination-search-input {
        flex: 1 !important;
        min-width: 150px !important;
        margin: 0 !important;
        border: none !important;
        outline: none !important;
        padding: 4px !important;
        background: transparent !important;
    }
    
    .destination-tag .remove-tag {
        cursor: pointer;
        font-size: 14px;
        font-weight: bold;
        line-height: 1;
        opacity: 0.8;
        transition: opacity 0.2s;
    }
    
    .destination-tag .remove-tag:hover {
        opacity: 1;
    }
    
    .destination-dropdown {
        position: fixed !important;
        background: white !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 4px !important;
        max-height: 250px !important;
        overflow-y: auto !important;
        z-index: 99999 !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.25) !important;
    }
    
    .destination-option {
        transition: background-color 0.2s;
        padding: 8px 12px;
        cursor: pointer;
        font-size: 13px;
    }
    
    .destination-option:hover {
        background-color: #f0f0f0;
    }
    
    .destination-option.selected {
        background-color: #e7f3ff;
        color: #0066cc;
        font-weight: 500;
    }
</style>
@endsection

@section('content')
<!-- Debug: Check data counts -->
<script>
    console.log('=== DATA DEBUG ===');
    console.log('Hotels count:', {{ $hotels->count() ?? 0 }});
    console.log('Restaurants count:', {{ $restaurants->count() ?? 0 }});
    console.log('Attractions count:', {{ $attractions->count() ?? 0 }});
    console.log('Ports count:', {{ $ports->count() ?? 0 }});
    @if(isset($hotels) && $hotels->count() > 0)
        console.log('Hotels:', @json($hotels->pluck('name')));
    @endif
    @if(isset($restaurants) && $restaurants->count() > 0)
        console.log('Restaurants:', @json($restaurants->pluck('name')));
    @endif
    
    // Check dropdown after page loads
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            const arrivalSelect = document.getElementById('arrivalDestination');
            const departureSelect = document.getElementById('departureDestination');
            
            if (arrivalSelect) {
                console.log('=== ARRIVAL DROPDOWN DEBUG ===');
                console.log('Total options:', arrivalSelect.options.length);
                const hotelOptions = Array.from(arrivalSelect.options).filter(o => o.value.startsWith('hotel_'));
                const restaurantOptions = Array.from(arrivalSelect.options).filter(o => o.value.startsWith('restaurant_'));
                console.log('Hotel options in dropdown:', hotelOptions.length);
                console.log('Restaurant options in dropdown:', restaurantOptions.length);
                console.log('Hotel option values:', hotelOptions.map(o => o.value));
                console.log('Restaurant option values:', restaurantOptions.map(o => o.value));
            }
            
            if (departureSelect) {
                console.log('=== DEPARTURE DROPDOWN DEBUG ===');
                console.log('Total options:', departureSelect.options.length);
                const hotelOptions = Array.from(departureSelect.options).filter(o => o.value.startsWith('hotel_'));
                const restaurantOptions = Array.from(departureSelect.options).filter(o => o.value.startsWith('restaurant_'));
                console.log('Hotel options in dropdown:', hotelOptions.length);
                console.log('Restaurant options in dropdown:', restaurantOptions.length);
            }
        }, 2000);
    });
</script>

<div class="enquiry-pro-container">     
    
    <!-- Fixed Top Header (Red Box) -->
    <div class="enquiry-pro-header">
        <!-- Row 1: Navigation Tabs + Customer Fields -->
        <div class="row-1-header">
            <ul class="nav nav-tabs nav-tabs-custom">
                <li class="nav-item">
                    <a class="nav-link" href="#">Itinerary</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="#">Quotation</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">FOC</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Invoice</a>
                </li>
                <li class="nav-item ms-auto">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="costSheet">
                        <label class="form-check-label" for="costSheet">Cost Sheet</label>
                    </div>
                </li>
                <li class="nav-item ms-3">
                    <span class="status-badge">STATUS: QUOTATION</span>
                </li>
            </ul>
            
            <!-- Additional Customer Fields in Row 1 -->
            <div class="row-1-fields">
                <div class="row g-1 align-items-center">
                    <div class="col-auto">
                        <strong style="font-size: 9px; color: #2c3e50;">{{ $user->name }}</strong>
                    </div>
                    <div class="col-auto">
                        <label style="font-size: 9px; margin: 0; font-weight: 500;">
                            <input type="radio" name="type" value="FIT" {{ (isset($initialData['tour_type']) && $initialData['tour_type'] == 'FIT') || !isset($initialData) ? 'checked' : '' }}> FIT
                        </label>
                        <label style="font-size: 9px; margin: 0 0 0 4px; font-weight: 500;">
                            <input type="radio" name="type" value="Group" {{ isset($initialData['tour_type']) && $initialData['tour_type'] == 'Group' ? 'checked' : '' }}> Group
                        </label>
                    </div>
                    <div class="col-auto d-flex align-items-center">
                        <span class="detail-label me-1" style="font-size: 9px;">Sal:</span>
                        <select class="form-select form-select-sm" id="salutationSelect" style="width: 50px; font-size: 9px; padding: 1px 3px;">
                            <option value="Mr" {{ (isset($initialData['salutation']) && $initialData['salutation'] == 'Mr') || !isset($initialData) ? 'selected' : '' }}>Mr</option>
                            <option value="Mrs" {{ isset($initialData['salutation']) && $initialData['salutation'] == 'Mrs' ? 'selected' : '' }}>Mrs</option>
                            <option value="Ms" {{ isset($initialData['salutation']) && $initialData['salutation'] == 'Ms' ? 'selected' : '' }}>Ms</option>
                            <option value="Dr" {{ isset($initialData['salutation']) && $initialData['salutation'] == 'Dr' ? 'selected' : '' }}>Dr</option>
                            <option value="Prof">Prof</option>
                        </select>
                    </div>
                    <div class="col-auto d-flex align-items-center">
                        <span class="detail-label me-1" style="font-size: 9px;">Name:</span>
                        <input type="text" class="form-control form-control-sm" value="{{ $initialData['customer_name'] ?? 'To Be Advised' }}" id="customerNameInput" style="font-size: 9px; width: 110px; padding: 1px 3px;">
                    </div>
                    <div class="col-auto d-flex align-items-center">
                        <span class="detail-label me-1" style="font-size: 9px;">Contact:</span>
                        <input type="text" class="form-control form-control-sm" value="{{ $initialData['contact_number'] ?? '' }}" id="contactNumberInput" placeholder="Opt" style="font-size: 9px; width: 85px; padding: 1px 3px;">
                    </div>
                    <div class="col-auto d-flex align-items-center">
                        <span class="detail-label me-1" style="font-size: 9px;">Agency:</span>
                        <select class="form-select form-select-sm" id="agencySelect" style="font-size: 9px; width: 120px; padding: 1px 3px;" onchange="loadAgentsByAgency()">
                            <option value="">-- Select --</option>
                            @foreach($agencies as $agency)
                                <option value="{{ $agency->agency_id }}" {{ isset($initialData['agency_id']) && $initialData['agency_id'] == $agency->agency_id ? 'selected' : '' }}>{{ $agency->agency_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto d-flex align-items-center">
                        <span class="detail-label me-1" style="font-size: 9px;">Agent:</span>
                        <select class="form-select form-select-sm" id="agentSelect" style="font-size: 9px; width: 100px; padding: 1px 3px;" {{ !isset($initialData['agent_id']) ? 'disabled' : '' }}>
                            @if(isset($initialData['agent_id']))
                                <option value="{{ $initialData['agent_id'] }}" selected>{{ $initialData['agent_name'] }}</option>
                            @else
                                <option value="">-- Select --</option>
                            @endif
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Adult, Child, Infant, Start, End, Destination - Beautiful Design -->
        <div class="customer-details row-2-beautiful">
            <div class="row g-2 align-items-center">
                <!-- Pax Section -->
                <div class="col-auto">
                    <div class="field-group pax-group">
                        <!-- Adult Section -->
                        <div class="field-item">
                            <i class="ri-user-line field-icon"></i>
                            <span class="detail-label">Adult:</span>
                            <input type="number" class="form-control form-control-sm beautiful-input" value="{{ $initialData['adult_count'] ?? '2' }}" id="adultCountInput" min="0" onchange="updateAdultDetails()">
                        </div>
                        <!-- Adult Details (Man/Women) - Hidden by default -->
                        <div id="adultDetailsContainer" style="display: none;" class="adult-details-container">
                            <div class="field-item">
                                <span class="detail-label">Man:</span>
                                <input type="number" class="form-control form-control-sm beautiful-input" id="adultManInput" min="0" value="0" onchange="validateAdultBreakdown()">
                            </div>
                            <div class="field-item">
                                <span class="detail-label">Women:</span>
                                <input type="number" class="form-control form-control-sm beautiful-input" id="adultWomenInput" min="0" value="0" onchange="validateAdultBreakdown()">
                            </div>
                        </div>
                        
                        <!-- Child Section -->
                        <div class="field-item">
                            <i class="ri-user-smile-line field-icon"></i>
                            <span class="detail-label">Child:</span>
                            <input type="number" class="form-control form-control-sm beautiful-input" value="{{ $initialData['child_count'] ?? '0' }}" id="childCountInput" min="0" onchange="updateChildDetails()">
                        </div>
                        <!-- Child Details (Ages) - Hidden by default -->
                        <div id="childDetailsContainer" style="display: none;" class="child-details-container">
                            <!-- Child age inputs will be dynamically generated here -->
                        </div>
                        
                        <!-- Infant Section -->
                        <div class="field-item">
                            <i class="ri-baby-line field-icon"></i>
                            <span class="detail-label">Infant:</span>
                            <input type="number" class="form-control form-control-sm beautiful-input" value="{{ $initialData['infant_count'] ?? '0' }}" id="infantCountInput" min="0" onchange="updateInfantDetails()">
                        </div>
                        <!-- Infant Details (Baby Cot) - Hidden by default -->
                        <div id="infantDetailsContainer" style="display: none;" class="infant-details-container">
                            <div class="field-item">
                                <label class="detail-label" style="cursor: pointer;">
                                    <input type="checkbox" id="babyCotRequired" class="form-check-input" style="margin-right: 4px;"> Baby Cot Required
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Date Section -->
                <div class="col-auto">
                    <div class="field-group date-group">
                        <div class="field-item">
                            <i class="ri-calendar-check-line field-icon"></i>
                            <span class="detail-label">Start:</span>
                            <input type="date" class="form-control form-control-sm beautiful-input" value="{{ $initialData['tour_start_date'] ?? '' }}" id="tourStartDate" name="tour_start_date" onchange="updateStartDate()" autocomplete="off">
                            <small class="text-muted ms-1" id="tourStartDateDisplay"></small>
                        </div>
                        <div class="field-item">
                            <i class="ri-calendar-close-line field-icon"></i>
                            <span class="detail-label">End:</span>
                            <input type="date" class="form-control form-control-sm beautiful-input" value="{{ $initialData['tour_end_date'] ?? '' }}" id="tourEndDate" name="tour_end_date" onchange="updateEndDate()" autocomplete="off">
                            <small class="text-muted ms-1" id="tourEndDateDisplay"></small>
                        </div>
                        <div class="field-item" id="nightsDisplayContainer" style="display: none;">
                            <i class="ri-moon-line field-icon"></i>
                            <span class="detail-label">Nights:</span>
                            <span id="nightsDisplay" class="nights-display">0</span>
                        </div>
                    </div>
                </div>
                
                <!-- Destination Section -->
                <div class="col">
                    <div class="field-group destination-group">
                        <div class="field-item full-width">
                            <i class="ri-map-pin-line field-icon"></i>
                            <span class="detail-label">Destination:</span>
                            <div class="destination-tags-container beautiful-input flex-fill" id="destinationTagsContainer">
                                <input type="text" 
                                       class="destination-search-input" 
                                       id="destinationSearchInput" 
                                       placeholder="Type to search destinations..."
                                       autocomplete="off">
                            </div>
                            <input type="hidden" id="destinationSelect" name="destinations" value="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Destination Dropdown - Positioned outside to avoid clipping -->
    <div class="destination-dropdown" id="destinationDropdown" style="display: none; position: fixed; z-index: 99999; background: white; border: 1px solid #dee2e6; border-radius: 4px; max-height: 250px; overflow-y: auto; box-shadow: 0 4px 12px rgba(0,0,0,0.25); min-width: 200px;">
        @foreach($countries as $country)
            <div class="destination-option" data-value="{{ $country->name }}">
                {{ $country->name }}
            </div>
        @endforeach
    </div>

    <!-- Scrollable Middle Content -->
    <div class="enquiry-pro-content">
        
        <!-- Arrival/Departure Section -->
        <div class="section-card">
            <div class="section-header">
                <span>Arrival / Departure</span>
                <div>
                    <button class="btn btn-sm btn-light btn-xs" onclick="openArrivalDepartureModal()">+ Add</button>
                    <button class="btn btn-sm btn-light btn-xs ms-1" onclick="removeSelectedArrivalDeparture()">- Remove</button>
                </div>
            </div>
            <div class="section-body">
                <table class="table table-custom table-hover" id="arrivalDepartureTable" style="display: none;">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllArrivalDeparture"></th>
                            <th>Date/Time</th>
                            <th>Port</th>
                            <th>Flight/Train/Bus No</th>
                            <th>Type</th>
                            <th>Adults Qty</th>
                            <th>Cost/Pax</th>
                            <th>Sell/Pax</th>
                            <th>Child Qty</th>
                            <th>Cost/Pax</th>
                            <th>Sell/Pax</th>
                            <th>Infant Qty</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody id="arrivalDepartureTableBody">
                        <!-- Arrival/Departure entries will be added here -->
                    </tbody>
                </table>
                <div class="empty-section" id="emptyArrivalDepartureMessage">No arrival/departure added yet</div>
            </div>
        </div>

        <!-- Book Travel Section - COMMENTED OUT FOR NOW -->
        <!--
        <div class="section-card">
            <div class="section-header">
                <span>Book Travel</span>
                <div>
                    <button class="btn btn-sm btn-light btn-xs">+ Add</button>
                    <button class="btn btn-sm btn-light btn-xs ms-1">- Remove</button>
                </div>
            </div>
            <div class="section-body">
                <table class="table table-custom table-hover">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Route</th>
                            <th>Return</th>
                            <th>Adults Qty</th>
                            <th>Cost/Pax</th>
                            <th>Sell/Pax</th>
                            <th>Child Qty</th>
                            <th>Cost/Pax</th>
                            <th>Sell/Pax</th>
                            <th>Transfer</th>
                            <th>Vehicle</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>09 Dec '25 00:00</td>
                            <td>SIN - KUL</td>
                            <td>NA</td>
                            <td><input type="number" value="2"></td>
                            <td><input type="number" value="35.00"></td>
                            <td><input type="number" value="35.00"></td>
                            <td><input type="number" value="1"></td>
                            <td><input type="number" value="35.00"></td>
                            <td><input type="number" value="35.00"></td>
                            <td>Yes</td>
                            <td>Combi</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        -->

        <!-- Accommodation Section -->
        <div class="section-card">
            <div class="section-header">
                <span>Accommodation</span>
                <div>
                    <button class="btn btn-sm btn-light btn-xs" onclick="openAccommodationModal()">+ Add</button>
                    <button class="btn btn-sm btn-light btn-xs ms-1" onclick="removeSelectedAccommodation()">- Remove</button>
                </div>
            </div>
            <div class="section-body">
                <table class="table table-custom table-hover" id="accommodationTable" style="display: none;">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllAccommodation"></th>
                            <th>Hotel / Room / Bed / Meal</th>
                            <th>Check In Date</th>
                            <th>Check Out Date</th>
                            <th>No. of Nights</th>
                            <th>No. of Rooms</th>
                            <th>Adults per Rm</th>
                            <th>Extra Bed</th>
                            <th>Child w/o Bed</th>
                        </tr>
                    </thead>
                    <tbody id="accommodationTableBody">
                        <!-- Hotels will be added here -->
                    </tbody>
                </table>
                <div class="empty-section" id="emptyAccommodationMessage">No accommodation added yet</div>
            </div>
        </div>

        <!-- Attractions Section -->
        <div class="section-card">
            <div class="section-header">
                <span>Attractions</span>
                <div>
                    <button class="btn btn-sm btn-light btn-xs" onclick="openTourModal()">+ Add</button>
                    <button class="btn btn-sm btn-light btn-xs ms-1" onclick="removeSelectedTours()">- Remove</button>
                </div>
            </div>
            <div class="section-body">
                <table class="table table-custom table-hover" id="tourTable" style="display: none;">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllTours" onchange="toggleSelectAllTours()"></th>
                            <th>Date/Time</th>
                            <th>Tour Name</th>
                            <th>Adults Qty</th>
                            <th>Cost/Pax</th>
                            <th>Sell/Pax</th>
                            <th>Child Qty</th>
                            <th>Cost/Pax</th>
                            <th>Sell/Pax</th>
                        </tr>
                    </thead>
                    <tbody id="tourTableBody">
                    </tbody>
                </table>
                <div class="empty-section" id="emptyTourMessage">No tours added yet</div>
            </div>
        </div>

        <!-- Restaurants Section -->
        <div class="section-card">
            <div class="section-header">
                <span>Restaurants</span>
                <div>
                    <button class="btn btn-sm btn-light btn-xs" onclick="openMealModal()">+ Add</button>
                    <button class="btn btn-sm btn-light btn-xs ms-1" onclick="removeSelectedMeals()">- Remove</button>
                </div>
            </div>
            <div class="section-body">
                <table class="table table-custom table-hover" id="mealTable" style="display: none;">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllMealsMain" onchange="toggleSelectAllMealsMain()"></th>
                            <th>Date/Time</th>
                            <th>Restaurant</th>
                            <th>Adults Qty</th>
                            <th>Cost/Pax</th>
                            <th>Sell/Pax</th>
                            <th>Child Qty</th>
                            <th>Cost/Pax</th>
                            <th>Sell/Pax</th>
                        </tr>
                    </thead>
                    <tbody id="mealTableBody">
                    </tbody>
                </table>
                <div class="empty-section" id="emptyMealMessage">No meals added yet</div>
            </div>
        </div>

        <!-- Local Transfer Section -->
        <div class="section-card">
            <div class="section-header">
                <span>Local Transfer</span>
                <div>
                    <button class="btn btn-sm btn-light btn-xs" onclick="openTransferModal()">+ Add</button>
                    <button class="btn btn-sm btn-light btn-xs ms-1" onclick="removeSelectedTransfers()">- Remove</button>
                </div>
            </div>
            <div class="section-body">
                <table class="table table-custom table-hover" id="transferTable" style="display: none;">
                    <thead>
                        <tr>
                            <th><input type="checkbox"></th>
                            <th>Date/Time</th>
                            <th>Service</th>
                            <th>Mode</th>
                            <th>Vehicle Type</th>
                            <th>Type</th>
                            <th>Way</th>
                            <th>Adults</th>
                            <th>Child</th>
                            <th>Cost</th>
                            <th>Sell</th>
                        </tr>
                    </thead>
                    <tbody id="transferTableBody">
                    </tbody>
                </table>
                <div class="empty-section" id="emptyTransferMessage">No transfers added yet</div>
            </div>
        </div>

        <!-- Tour Guide Section -->
        <div class="section-card">
            <div class="section-header">
                <span>Tour Guide</span>
                <div>
                    <button class="btn btn-sm btn-light btn-xs" onclick="openGuideModal()">+ Add</button>
                    <button class="btn btn-sm btn-light btn-xs ms-1" onclick="removeSelectedGuides()">- Remove</button>
                </div>
            </div>
            <div class="section-body">
                <table class="table table-custom table-hover" id="guideTable" style="display: none;">
                    <thead>
                        <tr>
                            <th><input type="checkbox"></th>
                            <th>Date/Time</th>
                            <th>Tour/Activity</th>
                            <th>Language</th>
                            <th>Guide Name</th>
                            <th>Hours</th>
                            <th>Cost</th>
                            <th>Sell</th>
                        </tr>
                    </thead>
                    <tbody id="guideTableBody">
                    </tbody>
                </table>
                <div class="empty-section" id="emptyGuideMessage">No guides added yet</div>
            </div>
        </div>

        <!-- Miscellaneous Section -->
        <div class="section-card">
            <div class="section-header">
                <span>Miscellaneous</span>
                <div>
                    <button class="btn btn-sm btn-light btn-xs" onclick="openMiscModal()">+ Add</button>
                    <button class="btn btn-sm btn-light btn-xs ms-1" onclick="removeSelectedMisc()">- Remove</button>
                </div>
            </div>
            <div class="section-body">
                <table class="table table-custom table-hover" id="miscTable" style="display: none;">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllMiscMain" onchange="toggleSelectAllMiscMain()"></th>
                            <th>DATE/TIME</th>
                            <th>ITEM</th>
                            <th>ADULTS QTY</th>
                            <th>COST/PAX</th>
                            <th>SELL/PAX</th>
                            <th>CHILD QTY</th>
                            <th>COST/PAX</th>
                            <th>SELL/PAX</th>
                            <th>INFANT QTY</th>
                            <th>COST/PAX</th>
                            <th>SELL/PAX</th>
                        </tr>
                    </thead>
                    <tbody id="miscTableBody">
                    </tbody>
                </table>
                <div class="empty-section" id="emptyMiscMessage">No miscellaneous items added yet</div>
            </div>
        </div>

    </div>

    <!-- Fixed Bottom Action Bar (Red Box) -->
    <div class="enquiry-pro-footer">
        <!-- Summary Table -->
        <div style="margin-bottom: 8px; overflow-x: auto;">
            <div style="max-height: 80px; overflow-y: auto;">
                <table class="table table-bordered table-sm" style="font-size: 9px; margin-bottom: 0; background: white;">
                    <thead style="background: #e9ecef; position: sticky; top: 0; z-index: 10;">
                        <tr>
                            <th style="padding: 3px 5px; vertical-align: middle; border-right: 2px solid #dee2e6; min-width: 180px; background: #e9ecef;">
                                <input type="checkbox" style="width: 12px; height: 12px;"> Hotel / Room
                            </th>
                            <th style="padding: 3px 5px; text-align: center; background: #fff3cd; min-width: 50px;">Single</th>
                            <th style="padding: 3px 5px; text-align: center; background: #d1ecf1; min-width: 50px;">Twin</th>
                            <th style="padding: 3px 5px; text-align: center; background: #f8d7da; min-width: 50px;">Triple</th>
                            <th style="padding: 3px 5px; text-align: center; background: #d4edda; min-width: 60px;">Child w/ bed</th>
                            <th style="padding: 3px 5px; text-align: center; background: #e7d6f5; min-width: 60px;">Child w/o bed</th>
                            <th style="padding: 3px 5px; text-align: center; background: #e2e3e5; min-width: 50px;">Infant</th>
                        </tr>
                    </thead>
                    <tbody id="footerSummaryBody">
                        <!-- Dynamic rows will be inserted here -->
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="action-buttons">
            <!-- <button class="btn btn-primary btn-sm">Send Email</button>
            <button class="btn btn-info btn-sm">Print Quote</button>
            <button class="btn btn-secondary btn-sm">Print Cost Sheet</button>
            <button class="btn btn-warning btn-sm" onclick="recalculateTotals()">Recalculate</button>
            <button class="btn btn-success btn-sm">Quote</button>
            <button class="btn btn-primary btn-sm">Confirm</button>
            <button class="btn btn-info btn-sm">Re-Confirm</button> -->
            <button class="btn btn-success btn-sm" onclick="saveEnquiryData()"><i class="ri-save-line me-1"></i>Save & Show JSON</button>
            <button class="btn btn-danger btn-sm">Cancel</button>
            <!-- <button class="btn btn-dark btn-sm">Close</button> -->
        </div>
    </div>

</div>

<!-- JSON Display Modal -->
<div class="modal fade" id="jsonDisplayModal" tabindex="-1" aria-labelledby="jsonDisplayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="jsonDisplayModalLabel">
                    <i class="ri-file-code-line me-2"></i>Enquiry Data - JSON Format
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="background-color: #f8f9fa;">
                <div class="mb-3">
                    <button class="btn btn-primary btn-sm" onclick="copyJsonToClipboard()">
                        <i class="ri-file-copy-line me-1"></i>Copy to Clipboard
                    </button>
                    <button class="btn btn-secondary btn-sm ms-2" onclick="downloadJson()">
                        <i class="ri-download-line me-1"></i>Download JSON
                    </button>
                </div>
                <pre id="jsonOutput" style="background-color: #ffffff; border: 1px solid #dee2e6; border-radius: 5px; padding: 20px; max-height: 70vh; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 12px;"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: transparent; border: none; box-shadow: none;">
            <div class="modal-body text-center">
                <div style="background: white; border-radius: 15px; padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                    <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem; border-width: 0.4rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h4 class="mt-4 mb-2" style="color: #333; font-weight: 600;">Saving Your Tour...</h4>
                    <p class="text-muted mb-0">Please wait while we process your data</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 20px; overflow: hidden;">
            <div class="modal-body text-center p-5">
                <div class="success-checkmark mb-4">
                    <div class="check-icon">
                        <span class="icon-line line-tip"></span>
                        <span class="icon-line line-long"></span>
                        <div class="icon-circle"></div>
                        <div class="icon-fix"></div>
                    </div>
                </div>
                <h2 class="text-success mb-3" style="font-weight: 700;">Success!</h2>
                <h5 class="mb-2" id="successTourId" style="color: #333;"></h5>
                <p class="text-muted mb-4" id="successOrderCount"></p>
                <button type="button" class="btn btn-success btn-lg px-5" onclick="redirectToDashboard()" style="border-radius: 50px;">
                    <i class="ri-dashboard-line me-2"></i>Go to Dashboard
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Success Checkmark Animation */
.success-checkmark {
    width: 80px;
    height: 80px;
    margin: 0 auto;
}

.check-icon {
    width: 80px;
    height: 80px;
    position: relative;
    border-radius: 50%;
    box-sizing: content-box;
    border: 4px solid #4CAF50;
}

.check-icon::before {
    top: 3px;
    left: -2px;
    width: 30px;
    transform-origin: 100% 50%;
    border-radius: 100px 0 0 100px;
}

.check-icon::after {
    top: 0;
    left: 30px;
    width: 60px;
    transform-origin: 0 50%;
    border-radius: 0 100px 100px 0;
    animation: rotate-circle 4.25s ease-in;
}

.icon-line {
    height: 5px;
    background-color: #4CAF50;
    display: block;
    border-radius: 2px;
    position: absolute;
    z-index: 10;
}

.icon-line.line-tip {
    top: 46px;
    left: 14px;
    width: 25px;
    transform: rotate(45deg);
    animation: icon-line-tip 0.75s;
}

.icon-line.line-long {
    top: 38px;
    right: 8px;
    width: 47px;
    transform: rotate(-45deg);
    animation: icon-line-long 0.75s;
}

.icon-circle {
    top: -4px;
    left: -4px;
    z-index: 10;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    position: absolute;
    box-sizing: content-box;
    border: 4px solid rgba(76, 175, 80, .5);
}

.icon-fix {
    top: 8px;
    width: 5px;
    left: 26px;
    z-index: 1;
    height: 85px;
    position: absolute;
    transform: rotate(-45deg);
    background-color: #fff;
}

@keyframes icon-line-tip {
    0% {
        width: 0;
        left: 1px;
        top: 19px;
    }
    54% {
        width: 0;
        left: 1px;
        top: 19px;
    }
    70% {
        width: 50px;
        left: -8px;
        top: 37px;
    }
    84% {
        width: 17px;
        left: 21px;
        top: 48px;
    }
    100% {
        width: 25px;
        left: 14px;
        top: 46px;
    }
}

@keyframes icon-line-long {
    0% {
        width: 0;
        right: 46px;
        top: 54px;
    }
    65% {
        width: 0;
        right: 46px;
        top: 54px;
    }
    84% {
        width: 55px;
        right: 0px;
        top: 35px;
    }
    100% {
        width: 47px;
        right: 8px;
        top: 38px;
    }
}
</style>

<!-- Accommodation Selection Modal -->
<div class="modal fade" id="accommodationModal" tabindex="-1" aria-labelledby="accommodationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 1200px;">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white py-2">
                <h6 class="modal-title mb-0" id="accommodationModalLabel">
                    <i class="ri-hotel-line me-2" id="modalTitleIcon"></i><span id="modalTitleText">Select Hotels</span>
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Hotel Selection Form - 2 Row Layout -->
                <div class="row g-2 mb-1" id="hotelSelectionRow1">
                    <div class="col-3">
                        <label class="form-label small">Destination</label>
                        <select class="form-select form-select-sm" id="hotelDestination" onchange="loadHotelsByDestination()">
                            <option value="">-- Select Destination --</option>
                            @foreach($destinations as $dest)
                                <option value="{{ $dest->name }}" {{ ($destination ?? '') == $dest->name ? 'selected' : '' }}>{{ $dest->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-3">
                        <label class="form-label small">Hotel</label>
                        <select class="form-select form-select-sm" id="hotelSelect" onchange="loadRoomTypes()" disabled>
                            <option value="">-- Select Hotel --</option>
                        </select>
                    </div>
                    <div class="col-2">
                        <label class="form-label small">Check In Date & Time</label>
                        <input type="datetime-local" class="form-control form-control-sm" id="checkInDate" value="" onchange="updateCheckOutMinDate()">
                    </div>
                    <div class="col-2">
                        <label class="form-label small">Check Out Date & Time</label>
                        <input type="datetime-local" class="form-control form-control-sm" id="checkOutDate" value="" onchange="calculateAccommodationNights()">
                    </div>
                    <div class="col-1">
                        <label class="form-label small">Nights</label>
                        <input type="number" class="form-control form-control-sm" id="numNights" value="3" readonly>
                    </div>
                    
                </div>

                <!-- Room/Bed/Meal Combinations Table (shown after hotel selection) -->
                <div id="roomCombinationsSection" style="display: none;">
                    <div class="border-top pt-2 mt-2">
                        <h6 class="small mb-2 text-muted">Select Room Combinations</h6>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0" style="font-size: 11px;">
                                <thead style="position: sticky; top: 0; background: #fff; z-index: 10;">
                                    <tr style="border-bottom: 2px solid #dee2e6;">
                                        <th style="width: 30px; padding: 4px 8px; text-align: center;">
                                            <input type="checkbox" id="selectAllRoomCombinations" onchange="toggleSelectAllRoomCombinations()">
                                        </th>
                                        <th style="padding: 4px 8px; min-width: 120px;">Room Type</th>
                                        <th style="padding: 4px 8px; min-width: 120px;">Bed Type</th>
                                        <th style="padding: 4px 8px; min-width: 100px;">Meal Plan</th>
                                        <th style="width: 80px; padding: 4px 8px; text-align: center;">Max Occupancy</th>
                                        <th style="width: 60px; padding: 4px 8px; text-align: center;">Rooms</th>
                                        <th style="width: 60px; padding: 4px 8px; text-align: center;">Adults</th>
                                        <th style="width: 60px; padding: 4px 8px; text-align: center;">Extra Bed</th>
                                        <th style="width: 70px; padding: 4px 8px; text-align: center;">Child w/o</th>
                                        <th style="width: 80px; padding: 4px 8px;">Price</th>
                                    </tr>
                                </thead>
                                <tbody id="roomCombinationsTableBody">
                                    <!-- Room combinations will be added here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Hotel Transfer Section -->
                <div class="border-top pt-2 mt-2" id="hotelTransferSection" style="display: none;">
                    <div class="row g-2 mb-1">
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="hotelTransferCheckbox" onchange="toggleHotelTransferFields()">
                                <label class="form-check-label small" for="hotelTransferCheckbox">
                                    <strong>Add Transfer for this Hotel</strong>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Transfer Details (shown when checkbox is checked) -->
                    <div id="hotelTransferDetailsSection" style="display: none;">
                        <div class="row g-2 mb-1">
                            <div class="col-3">
                                <label class="form-label small">Destination</label>
                                <select class="form-select form-select-sm" id="hotelTransferDestination" style="font-size: 10px;">
                                    <option value="">Select Destination</option>
                                    <optgroup label="Ports">
                                        @foreach($ports as $port)
                                            <option value="port_{{ $port->id }}" data-name="{{ $port->port_name }}" data-type="port" data-country="{{ $port->country }}">{{ $port->port_name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Hotels">
                                        @foreach($hotels as $hotel)
                                            <option value="hotel_{{ $hotel->id }}" data-name="{{ $hotel->name }}" data-type="hotel" data-city="{{ $hotel->city ?? '' }}" data-country="{{ $hotel->country ?? '' }}">{{ $hotel->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Attractions">
                                        @foreach($attractions as $attr)
                                            <option value="attraction_{{ $attr->attraction_id }}" data-name="{{ $attr->name }}" data-type="attraction" data-location="{{ $attr->location ?? '' }}" data-country="{{ $attr->country ?? '' }}">{{ $attr->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Restaurants">
                                        @foreach($restaurants as $rest)
                                            <option value="restaurant_{{ $rest->restaurant_id }}" data-name="{{ $rest->name }}" data-type="restaurant" data-city="{{ $rest->city ?? '' }}" data-country="{{ $rest->country ?? '' }}">{{ $rest->name }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-2">
                                <label class="form-label small">Vehicle Type</label>
                                <select class="form-select form-select-sm" id="hotelTransferVehicleType" style="font-size: 10px;">
                                    <option value="">Select Vehicle</option>
                                    @php
                                        $vehicleTypes = $vehicles->groupBy('vehicle_type');
                                    @endphp
                                    @foreach($vehicleTypes as $type => $typeVehicles)
                                        <optgroup label="{{ ucfirst($type) }}">
                                            @foreach($typeVehicles as $vehicle)
                                                <option value="{{ $vehicle->vehicle_id }}" 
                                                    data-type="{{ $vehicle->vehicle_type }}"
                                                    data-seating="{{ $vehicle->seating_capacity }}"
                                                    data-base-price="{{ $vehicle->base_price ?? 0 }}"
                                                    data-sharable-price="{{ $vehicle->sharable_base_price ?? 0 }}">
                                                    {{ $vehicle->vehicle_name }} ({{ $vehicle->seating_capacity }} seats)
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2">
                                <label class="form-label small">Way</label>
                                <select class="form-select form-select-sm" id="hotelTransferWay" style="font-size: 10px;">
                                    <option value="one-way">One Way</option>
                                    <option value="both-way" selected>Both Way</option>
                                </select>
                            </div>
                            <div class="col-2">
                                <label class="form-label small">Transfer Type</label>
                                <select class="form-select form-select-sm" id="hotelTransferType" style="font-size: 10px;">
                                    <option value="P">Private</option>
                                    <option value="S" selected>Shared</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Arrival/Departure Flight Information (Hidden for now) -->
                <div class="border-top pt-2 mt-2" id="arrivalDepartureSection" style="display: none;">
                    <h6 class="small mb-1 text-muted" id="arrivalDepartureSectionTitle">Arrival/Departure Flight Information</h6>
                    <div class="row g-2 mb-1">
                        <div class="col-2" id="arrivalDateTimeField">
                            <label class="form-label small">Arrival Date & Time</label>
                            <input type="datetime-local" class="form-control form-control-sm" id="arrivalDateTime">
                        </div>
                        <div class="col-2" id="arrivalPortField">
                            <label class="form-label small">Arrival Port</label>
                            <select class="form-select form-select-sm select2-port" id="arrivalPort">
                                <option value="">Select Port</option>
                                @foreach($ports as $port)
                                    <option value="{{ $port->id }}" data-type="{{ $port->type }}" data-country="{{ $port->country }}">{{ $port->port_name }} ({{ $port->type }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-2" id="arrivalFlightNoField">
                            <label class="form-label small">Arrival Flight/Train/Bus</label>
                            <input type="text" class="form-control form-control-sm" id="arrivalFlightNo" placeholder="Flight No.">
                        </div>
                        <div class="col-1" id="arrivalTransferField">
                            <label class="form-label small">Transfer</label>
                            <div class="form-check mt-1">
                                <input type="checkbox" class="form-check-input" id="arrivalTransfer" onchange="toggleArrivalTransferFields()">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Arrival Transfer Details (shown only when transfer is checked) -->
                    <div id="arrivalTransferDetailsSection" style="display: none;">
                        <div class="row g-2 mb-1">
                            <div class="col-3" id="arrivalDestinationField">
                                <label class="form-label small">Destination</label>
                                <select class="form-select form-select-sm" id="arrivalDestination" style="font-size: 10px;">
                                    <option value="">Select Destination</option>
                                    <optgroup label="Ports">
                                        @foreach($ports as $port)
                                            <option value="port_{{ $port->id }}" data-name="{{ $port->port_name }}" data-type="port" data-country="{{ $port->country }}">{{ $port->port_name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Hotels">
                                        @foreach($hotels as $hotel)
                                            <option value="hotel_{{ $hotel->id }}" data-name="{{ $hotel->name }}" data-type="hotel" data-city="{{ $hotel->city ?? '' }}" data-country="{{ $hotel->country ?? '' }}">{{ $hotel->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Attractions">
                                        @foreach($attractions as $attr)
                                            <option value="attraction_{{ $attr->attraction_id }}" data-name="{{ $attr->name }}" data-type="attraction" data-location="{{ $attr->location ?? '' }}" data-country="{{ $attr->country ?? '' }}">{{ $attr->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Restaurants">
                                        @foreach($restaurants as $rest)
                                            <option value="restaurant_{{ $rest->restaurant_id }}" data-name="{{ $rest->name }}" data-type="restaurant" data-city="{{ $rest->city ?? '' }}" data-country="{{ $rest->country ?? '' }}">{{ $rest->name }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-2" id="arrivalVehicleTypeField">
                                <label class="form-label small">Vehicle Type</label>
                                <select class="form-select form-select-sm" id="arrivalVehicleType" style="font-size: 10px;" onchange="updateArrivalVehiclePricing()">
                                    <option value="">Select Vehicle</option>
                                    @php
                                        $vehicleTypes = $vehicles->groupBy('vehicle_type');
                                    @endphp
                                    @foreach($vehicleTypes as $type => $typeVehicles)
                                        <optgroup label="{{ ucfirst($type) }}">
                                            @foreach($typeVehicles as $vehicle)
                                                <option value="{{ $vehicle->vehicle_id }}" 
                                                    data-type="{{ $vehicle->vehicle_type }}"
                                                    data-seating="{{ $vehicle->seating_capacity }}"
                                                    data-base-price="{{ $vehicle->base_price ?? 0 }}"
                                                    data-sharable-price="{{ $vehicle->sharable_base_price ?? 0 }}">
                                                    {{ $vehicle->vehicle_name }} ({{ $vehicle->seating_capacity }} seats)
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-1" id="arrivalAdultsField">
                                <label class="form-label small">Adults</label>
                                <input type="number" class="form-control form-control-sm" id="arrivalAdults" value="2" min="0" max="99" onchange="validateArrivalPassengers()">
                            </div>
                            <div class="col-1" id="arrivalChildField">
                                <label class="form-label small">Child</label>
                                <input type="number" class="form-control form-control-sm" id="arrivalChild" value="0" min="0" max="99" onchange="validateArrivalPassengers()">
                            </div>
                            <div class="col-1" id="arrivalInfantField">
                                <label class="form-label small">Infant</label>
                                <input type="number" class="form-control form-control-sm" id="arrivalInfant" value="0" min="0" max="99" onchange="validateArrivalPassengers()">
                            </div>
                            <div class="col-2" id="arrivalTransferWayField">
                                <label class="form-label small">Way</label>
                                <select class="form-select form-select-sm" id="arrivalTransferWay" style="font-size: 10px;">
                                    <option value="one-way">1-Way</option>
                                    <option value="both-way" selected>2-Way</option>
                                </select>
                            </div>
                            <div class="col-2" id="arrivalTransferTypeField">
                                <label class="form-label small">Type</label>
                                <select class="form-select form-select-sm" id="arrivalTransferType" style="font-size: 10px;">
                                    <option value="P">Private</option>
                                    <option value="S" selected>Shared</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Separator between Arrival and Departure -->
                    <div class="border-top my-2" style="border-color: #dee2e6 !important;"></div>
                    <h6 class="small mb-2 text-muted" style="font-weight: 600;">Departure Information</h6>
                    
                    <div class="row g-2 mb-1">
                        <div class="col-2" id="departureDateTimeField">
                            <label class="form-label small">Departure Date & Time</label>
                            <input type="datetime-local" class="form-control form-control-sm" id="departureDateTime">
                        </div>
                        <div class="col-2" id="departurePortField">
                            <label class="form-label small">Departure Port</label>
                            <select class="form-select form-select-sm select2-port" id="departurePort">
                                <option value="">Select Port</option>
                                @foreach($ports as $port)
                                    <option value="{{ $port->id }}" data-type="{{ $port->type }}" data-country="{{ $port->country }}">{{ $port->port_name }} ({{ $port->type }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-2" id="departureFlightNoField">
                            <label class="form-label small">Departure Flight/Train/Bus</label>
                            <input type="text" class="form-control form-control-sm" id="departureFlightNo" placeholder="Flight No.">
                        </div>
                        <div class="col-1" id="departureTransferField">
                            <label class="form-label small">Transfer</label>
                            <div class="form-check mt-1">
                                <input type="checkbox" class="form-check-input" id="departureTransfer" onchange="toggleDepartureTransferFields()">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Departure Transfer Details (shown only when transfer is checked) -->
                    <div id="departureTransferDetailsSection" style="display: none;">
                        <div class="row g-2 mb-1">
                            <div class="col-3" id="departureDestinationField">
                                <label class="form-label small">Destination</label>
                                <select class="form-select form-select-sm" id="departureDestination" style="font-size: 10px;">
                                    <option value="">Select Destination</option>
                                    <optgroup label="Ports">
                                        @foreach($ports as $port)
                                            <option value="port_{{ $port->id }}" data-name="{{ $port->port_name }}" data-type="port" data-country="{{ $port->country }}">{{ $port->port_name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Hotels">
                                        @foreach($hotels as $hotel)
                                            <option value="hotel_{{ $hotel->id }}" data-name="{{ $hotel->name }}" data-type="hotel" data-city="{{ $hotel->city ?? '' }}" data-country="{{ $hotel->country ?? '' }}">{{ $hotel->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Attractions">
                                        @foreach($attractions as $attr)
                                            <option value="attraction_{{ $attr->attraction_id }}" data-name="{{ $attr->name }}" data-type="attraction" data-location="{{ $attr->location ?? '' }}" data-country="{{ $attr->country ?? '' }}">{{ $attr->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Restaurants">
                                        @foreach($restaurants as $rest)
                                            <option value="restaurant_{{ $rest->restaurant_id }}" data-name="{{ $rest->name }}" data-type="restaurant" data-city="{{ $rest->city ?? '' }}" data-country="{{ $rest->country ?? '' }}">{{ $rest->name }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-2" id="departureVehicleTypeField">
                                <label class="form-label small">Vehicle Type</label>
                                <select class="form-select form-select-sm" id="departureVehicleType" style="font-size: 10px;" onchange="updateDepartureVehiclePricing()">
                                    <option value="">Select Vehicle</option>
                                    @php
                                        $vehicleTypes = $vehicles->groupBy('vehicle_type');
                                    @endphp
                                    @foreach($vehicleTypes as $type => $typeVehicles)
                                        <optgroup label="{{ ucfirst($type) }}">
                                            @foreach($typeVehicles as $vehicle)
                                                <option value="{{ $vehicle->vehicle_id }}" 
                                                    data-type="{{ $vehicle->vehicle_type }}"
                                                    data-seating="{{ $vehicle->seating_capacity }}"
                                                    data-base-price="{{ $vehicle->base_price ?? 0 }}"
                                                    data-sharable-price="{{ $vehicle->sharable_base_price ?? 0 }}">
                                                    {{ $vehicle->vehicle_name }} ({{ $vehicle->seating_capacity }} seats)
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-1" id="departureAdultsField">
                                <label class="form-label small">Adults</label>
                                <input type="number" class="form-control form-control-sm" id="departureAdults" value="2" min="0" max="99" onchange="validateDeparturePassengers()">
                            </div>
                            <div class="col-1" id="departureChildField">
                                <label class="form-label small">Child</label>
                                <input type="number" class="form-control form-control-sm" id="departureChild" value="0" min="0" max="99" onchange="validateDeparturePassengers()">
                            </div>
                            <div class="col-1" id="departureInfantField">
                                <label class="form-label small">Infant</label>
                                <input type="number" class="form-control form-control-sm" id="departureInfant" value="0" min="0" max="99" onchange="validateDeparturePassengers()">
                            </div>
                            <div class="col-2" id="departureTransferWayField">
                                <label class="form-label small">Way</label>
                                <select class="form-select form-select-sm" id="departureTransferWay" style="font-size: 10px;">
                                    <option value="one-way">1-Way</option>
                                    <option value="both-way" selected>2-Way</option>
                                </select>
                            </div>
                            <div class="col-2" id="departureTransferTypeField">
                                <label class="form-label small">Type</label>
                                <select class="form-select form-select-sm" id="departureTransferType" style="font-size: 10px;">
                                    <option value="P">Private</option>
                                    <option value="S" selected>Shared</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selected Hotels List - Hidden (now using checkbox selection in combinations table) -->
                <div class="border-top pt-1 mt-1" id="selectedHotelsSection" style="display: none;">
                    <h6 class="small mb-1 text-muted">Selected Hotels (Max 4 displayed)</h6>
                    <div style="max-height: 120px; overflow-y: auto;">
                        <table class="table table-sm table-bordered mb-0" style="font-size: 10px;">
                            <thead class="table-light">
                                <tr>
                                    <th>Hotel / Room</th>
                                    <th>Dates</th>
                                    <th>Qty</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="selectedHotelsList">
                                <!-- Hotels will be added here -->
                            </tbody>
                        </table>
                        <p class="text-muted small mb-0 text-center py-1" id="noHotelsMessage" style="font-size: 10px;">No hotels selected yet</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Close
                </button>
                <button type="button" class="btn btn-success btn-sm" onclick="saveSelectedHotels()" id="saveAccommodationBtn">
                    <i class="ri-check-line me-1"></i><span id="saveAccommodationBtnText">Add Accommodation</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tour Selection Modal -->
<div class="modal fade" id="tourModal" tabindex="-1" aria-labelledby="tourModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(90deg, #17a2b8 0%, #138496 100%); padding: 8px 15px;">
                <h6 class="modal-title mb-0 text-white" id="tourModalLabel">
                    <i class="ri-map-pin-line me-2"></i><span id="tourModalTitleText">Tour Details</span>
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal" aria-label="Close" style="font-size: 10px;"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Top Controls -->
                <div class="border-bottom p-2" style="background: #f8f9fa;">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small mb-0" style="font-size: 11px; font-weight: 600;">Date & Time:</label>
                            <input type="datetime-local" class="form-control form-control-sm" id="tourDateTime" style="font-size: 11px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-0" style="font-size: 11px; font-weight: 600;">Destination:</label>
                            <select class="form-select form-select-sm" id="tourDestination" onchange="loadAttractionsByDestination()" style="font-size: 11px;">
                                <option value="">Select Destination</option>
                                @foreach($destinations as $dest)
                                    <option value="{{ $dest->name }}">{{ $dest->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Attractions Table -->
                <div style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0" style="font-size: 11px;">
                        <thead style="position: sticky; top: 0; background: #fff; z-index: 10;">
                            <tr style="border-bottom: 2px solid #dee2e6;">
                                <th style="width: 30px; padding: 4px 8px; text-align: center;">
                                    <input type="checkbox" id="selectAllAttractions" onchange="toggleSelectAllAttractions()">
                                </th>
                                <th style="padding: 4px 8px; min-width: 200px;">Attraction Name</th>
                                <th style="width: 60px; padding: 4px 8px; text-align: center;">Adults</th>
                                <th style="width: 100px; padding: 4px 8px;">Charges /pax</th>
                                <th style="width: 60px; padding: 4px 8px; text-align: center;">Child</th>
                                <th style="width: 100px; padding: 4px 8px;">Charges /pax</th>
                                <th style="width: 60px; padding: 4px 8px; text-align: center;">Infant</th>
                                <th style="width: 100px; padding: 4px 8px;">Charges /pax</th>
                                <th style="width: 80px; padding: 4px 8px; text-align: center;">Transfer</th>
                                <th style="width: 150px; padding: 4px 8px;">Destination</th>
                                <th style="width: 120px; padding: 4px 8px;">Vehicle Type</th>
                                <th style="width: 100px; padding: 4px 8px;">Way</th>
                                <th style="width: 120px; padding: 4px 8px;">Transfer Type</th>
                                <th style="width: 80px; padding: 4px 8px; text-align: center;">Guide</th>
                                <th style="width: 200px; padding: 4px 8px;">Select Guide</th>
                            </tr>
                        </thead>
                        <tbody id="attractionsTableBody">
                            @foreach($attractions as $attr)
                            <tr class="attraction-row" data-attraction-id="{{ $attr->id }}" data-attraction-name="{{ $attr->name }}" data-attraction-type="tour_sites">
                                <td style="padding: 2px 8px; text-align: center;">
                                    <input type="checkbox" class="attraction-checkbox" data-attr-id="{{ $attr->id }}">
                                </td>
                                <td style="padding: 2px 8px;">
                                    {{ $attr->name }}
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="number" class="form-control form-control-sm attraction-adult-qty" data-attr-id="{{ $attr->id }}" value="0" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="text" class="form-control form-control-sm attraction-adult-charge" data-attr-id="{{ $attr->id }}" value="SGD 0.00" style="font-size: 10px; padding: 2px 4px;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="number" class="form-control form-control-sm attraction-child-qty" data-attr-id="{{ $attr->id }}" value="0" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="text" class="form-control form-control-sm attraction-child-charge" data-attr-id="{{ $attr->id }}" value="SGD 0.00" style="font-size: 10px; padding: 2px 4px;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="number" class="form-control form-control-sm attraction-infant-qty" data-attr-id="{{ $attr->id }}" value="0" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="text" class="form-control form-control-sm attraction-infant-charge" data-attr-id="{{ $attr->id }}" value="SGD 0.00" style="font-size: 10px; padding: 2px 4px;">
                                </td>
                                <td style="padding: 2px 8px; text-align: center;">
                                    <input type="checkbox" class="form-check-input attraction-transfer-checkbox" data-attr-id="{{ $attr->id }}" checked>
                                </td>
                                <td style="padding: 2px 8px;">
                                    <select class="form-select form-select-sm attraction-transfer-destination" data-attr-id="{{ $attr->id }}" style="font-size: 10px; padding: 2px 4px;">
                                        <option value="">Select Destination</option>
                                        <optgroup label="Ports">
                                            @foreach($ports as $port)
                                                <option value="port_{{ $port->id }}" data-name="{{ $port->port_name }}" data-type="port">{{ $port->port_name }}</option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="Attractions">
                                            @foreach($attractions as $attr2)
                                                <option value="attraction_{{ $attr2->attraction_id }}" data-name="{{ $attr2->name }}" data-type="attraction" data-location="{{ $attr2->location ?? '' }}">{{ $attr2->name }}</option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="Restaurants">
                                            @foreach($restaurants as $rest)
                                                <option value="restaurant_{{ $rest->restaurant_id }}" data-name="{{ $rest->name }}" data-type="restaurant">{{ $rest->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                </td>
                                <td style="padding: 2px 8px;">
                                    <select class="form-select form-select-sm attraction-vehicle-type" data-attr-id="{{ $attr->id }}" style="font-size: 10px; padding: 2px 4px;">
                                        <option value="">Select Vehicle</option>
                                        @php
                                            $vehicleTypes = $vehicles->groupBy('vehicle_type');
                                        @endphp
                                        @foreach($vehicleTypes as $type => $typeVehicles)
                                            <optgroup label="{{ ucfirst($type) }}">
                                                @foreach($typeVehicles as $vehicle)
                                                    <option value="{{ $vehicle->vehicle_id }}" 
                                                        data-type="{{ $vehicle->vehicle_type }}"
                                                        data-seating="{{ $vehicle->seating_capacity }}"
                                                        data-base-price="{{ $vehicle->base_price ?? 0 }}"
                                                        data-sharable-price="{{ $vehicle->sharable_base_price ?? 0 }}">
                                                        {{ $vehicle->vehicle_name }} ({{ $vehicle->seating_capacity }} seats)
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </td>
                                <td style="padding: 2px 8px;">
                                    <select class="form-select form-select-sm attraction-transfer-way" data-attr-id="{{ $attr->id }}" style="font-size: 10px; padding: 2px 4px;">
                                        <option value="one-way">1-Way</option>
                                        <option value="both-way" selected>2-Way</option>
                                    </select>
                                </td>
                                <td style="padding: 2px 8px;">
                                    <select class="form-select form-select-sm attraction-transfer-type" data-attr-id="{{ $attr->id }}" style="font-size: 10px; padding: 2px 4px;">
                                        <option value="P">Private</option>
                                        <option value="S" selected>Shared</option>
                                    </select>
                                </td>
                                <td style="padding: 2px 8px; text-align: center;">
                                    <input type="checkbox" class="form-check-input attraction-guide-checkbox" data-attr-id="{{ $attr->id }}">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <select class="form-select form-select-sm attraction-guide-select" data-attr-id="{{ $attr->id }}" style="font-size: 10px; padding: 2px 4px;">
                                        <option value="">Select Guide</option>
                                        @foreach($guides as $guide)
                                            @php
                                                $languages = $guide->languages->pluck('language')->join(', ');
                                            @endphp
                                            <option value="{{ $guide->guide_id }}" data-name="{{ $guide->name }}" data-languages="{{ $languages }}">{{ $guide->name }} @if($languages)({{ $languages }})@endif</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2" style="background: #f8f9fa;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="addAnotherAttraction()" style="font-size: 11px;">
                    <i class="ri-add-line me-1"></i>Add Another
                </button>
                <button type="button" class="btn btn-primary btn-sm" onclick="saveAndCloseAttractions()" style="font-size: 11px;">
                    <i class="ri-save-line me-1"></i>Save & Close
                </button>
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal" style="font-size: 11px;">
                    <i class="ri-close-line me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Guide Modal -->
<div class="modal fade" id="guideModal" tabindex="-1" aria-labelledby="guideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h6 class="modal-title text-white mb-0">
                    <i class="ri-user-star-line me-2"></i>Select Guide
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <!-- Filter Section -->
                <div class="card mb-2" style="border: 1px solid #e0e0e0;">
                    <div class="card-body p-2">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label small mb-0" style="font-size: 11px; font-weight: 600;">Date & Time:</label>
                                <input type="datetime-local" class="form-control form-control-sm" id="guideDate" style="font-size: 11px;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-0" style="font-size: 11px; font-weight: 600;">Destination:</label>
                                <select class="form-select form-select-sm" id="guideDestination" onchange="loadGuidesByDestination()" style="font-size: 11px;">
                                    <option value="">Select Destination</option>
                                    @foreach($destinations as $dest)
                                        <option value="{{ $dest->name }}">{{ $dest->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guides Table (Max 10 records visible, then scroll) -->
                <div style="max-height: 380px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px;">
                    <table class="table table-sm table-hover mb-0" style="font-size: 11px;">
                        <thead style="position: sticky; top: 0; background: #fff; z-index: 10; box-shadow: 0 2px 2px -1px rgba(0,0,0,0.1);">
                            <tr style="border-bottom: 2px solid #dee2e6;">
                                <th style="width: 30px; padding: 4px 8px; text-align: center;">
                                    <input type="checkbox" id="selectAllGuides" onchange="toggleSelectAllGuides()">
                                </th>
                                <th style="padding: 4px 8px; min-width: 180px;">Guide Name</th>
                                <th style="padding: 4px 8px; min-width: 120px;">Language</th>
                                <th style="width: 100px; padding: 4px 8px; text-align: center;">Hours</th>
                                <th style="width: 120px; padding: 4px 8px; text-align: right;">Day Rate (Cost)</th>
                                <th style="width: 120px; padding: 4px 8px; text-align: right;">Sell Price</th>
                            </tr>
                        </thead>
                        <tbody id="guidesTableBody">
                            <tr>
                                <td colspan="6" class="text-center text-muted" style="padding: 20px;">
                                    Please select a destination to load guides
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2" style="background: #f8f9fa;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="addAnotherGuide()" style="font-size: 11px;">
                    <i class="ri-add-line me-1"></i>Add Another
                </button>
                <button type="button" class="btn btn-primary btn-sm" onclick="saveAndCloseGuides()" style="font-size: 11px;">
                    <i class="ri-save-line me-1"></i>Save & Close
                </button>
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal" style="font-size: 11px;">
                    <i class="ri-close-line me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Miscellaneous Modal -->
<div class="modal fade" id="miscModal" tabindex="-1" aria-labelledby="miscModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header py-2" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <h6 class="modal-title text-white mb-0">
                    <i class="ri-file-list-3-line me-2"></i><span id="miscModalTitleText">Miscellaneous Items</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <!-- Filter Section -->
                <div class="card mb-2" style="border: 1px solid #e0e0e0;">
                    <div class="card-body p-2">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label small mb-0" style="font-size: 11px; font-weight: 600;">Date & Time:</label>
                                <input type="datetime-local" class="form-control form-control-sm" id="miscDate" style="font-size: 11px;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-0" style="font-size: 11px; font-weight: 600;">Destination:</label>
                                <select class="form-select form-select-sm" id="miscDestination" onchange="loadMiscItemsByDestination()" style="font-size: 11px;">
                                    <option value="">Select Destination</option>
                                    @foreach($master_dmc_destinations as $dest)
                                        <option value="{{ $dest->name }}">{{ $dest->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Miscellaneous Items Table -->
                <div style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0" style="font-size: 11px;">
                        <thead style="position: sticky; top: 0; background: #fff; z-index: 10;">
                            <tr style="border-bottom: 2px solid #dee2e6;">
                                <th style="width: 30px; padding: 4px 8px; text-align: center;">
                                    <input type="checkbox" id="selectAllMiscItems" onchange="toggleSelectAllMiscItems()">
                                </th>
                                <th style="padding: 4px 8px; min-width: 200px;">Item Name</th>
                                <th style="width: 60px; padding: 4px 8px; text-align: center;">Adults</th>
                                <th style="width: 100px; padding: 4px 8px;">Charges /pax</th>
                                <th style="width: 60px; padding: 4px 8px; text-align: center;">Child</th>
                                <th style="width: 100px; padding: 4px 8px;">Charges /pax</th>
                                <th style="width: 60px; padding: 4px 8px; text-align: center;">Infant</th>
                                <th style="width: 100px; padding: 4px 8px;">Charges /pax</th>
                            </tr>
                        </thead>
                        <tbody id="miscItemsTableBody">
                            <tr>
                                <td colspan="8" class="text-center text-muted" style="padding: 20px;">
                                    Please select a destination to load miscellaneous items
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2" style="background: #f8f9fa;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="addAnotherMisc()" style="font-size: 11px;">
                    <i class="ri-add-line me-1"></i>Add Another
                </button>
                <button type="button" class="btn btn-primary btn-sm" onclick="saveAndCloseMisc()" style="font-size: 11px;">
                    <i class="ri-save-line me-1"></i><span id="saveMiscBtnText">Save & Close</span>
                </button>
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal" style="font-size: 11px;">
                    <i class="ri-close-line me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Meal/Restaurant Modal -->
<div class="modal fade" id="mealModal" tabindex="-1" aria-labelledby="mealModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(90deg, #17a2b8 0%, #138496 100%); padding: 8px 15px;">
                <h6 class="modal-title mb-0 text-white" id="mealModalLabel">
                    <i class="ri-restaurant-line me-2"></i><span id="mealModalTitleText">Meal Details</span>
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal" aria-label="Close" style="font-size: 10px;"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Top Controls -->
                <div class="border-bottom p-2" style="background: #f8f9fa;">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label small mb-0" style="font-size: 11px; font-weight: 600;">Date & Time:</label>
                            <input type="datetime-local" class="form-control form-control-sm" id="mealDateTime" style="font-size: 11px;">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-0" style="font-size: 11px; font-weight: 600;">Destination:</label>
                            <select class="form-select form-select-sm" id="mealDestination" onchange="loadRestaurantsByDestination()" style="font-size: 11px;">
                                <option value="">Select Destination</option>
                                @foreach($destinations as $dest)
                                    <option value="{{ $dest->name }}">{{ $dest->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-0" style="font-size: 11px; font-weight: 600;">Restaurant:</label>
                            <select class="form-select form-select-sm" id="mealRestaurant" onchange="updateMealsFromRestaurant()" style="font-size: 11px;">
                                <option value="">Select Restaurant</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Meals Table -->
                <div style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0" style="font-size: 11px;">
                        <thead style="position: sticky; top: 0; background: #fff; z-index: 10;">
                            <tr style="border-bottom: 2px solid #dee2e6;">
                                <th style="width: 30px; padding: 4px 8px; text-align: center;">
                                    <input type="checkbox" id="selectAllMeals" onchange="toggleSelectAllMeals()">
                                </th>
                                <th style="padding: 4px 8px; min-width: 150px;">Meal Type</th>
                                <th style="padding: 4px 8px; min-width: 80px;">No Of Meals</th>
                                <th style="width: 60px; padding: 4px 8px; text-align: center;">Adults</th>
                                <th style="width: 100px; padding: 4px 8px;">Charges /pax</th>
                                <th style="width: 60px; padding: 4px 8px; text-align: center;">Child</th>
                                <th style="width: 100px; padding: 4px 8px;">Charges /pax</th>
                                <th style="width: 60px; padding: 4px 8px; text-align: center;">Infant</th>
                                <th style="width: 100px; padding: 4px 8px;">Charges /pax</th>
                            </tr>
                        </thead>
                        <tbody id="mealsTableBody">
                            <!-- Additional Breakfast -->
                            <tr class="meal-row" data-meal-id="1" data-meal-name="Additional Breakfast" data-meal-type="breakfast">
                                <td style="padding: 2px 8px; text-align: center;">
                                    <input type="checkbox" class="meal-checkbox" data-meal-id="1">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <i class="ri-close-circle-fill text-danger me-1" style="font-size: 14px;"></i>
                                    Additional Breakfast
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="number" class="form-control form-control-sm meal-count" data-meal-id="1" value="5" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="number" class="form-control form-control-sm meal-adult-qty" data-meal-id="1" value="4" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="text" class="form-control form-control-sm meal-adult-charge" data-meal-id="1" value="SGD 10,000..." style="font-size: 10px; padding: 2px 4px;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="number" class="form-control form-control-sm meal-child-qty" data-meal-id="1" value="2" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="text" class="form-control form-control-sm meal-child-charge" data-meal-id="1" value="SGD 10,000..." style="font-size: 10px; padding: 2px 4px;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="number" class="form-control form-control-sm meal-infant-qty" data-meal-id="1" value="0" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="text" class="form-control form-control-sm meal-infant-charge" data-meal-id="1" value="SGD 0.00" style="font-size: 10px; padding: 2px 4px;">
                                </td>
                            </tr>
                            <!-- Dinner -->
                            <tr class="meal-row" data-meal-id="2" data-meal-name="Dinner" data-meal-type="dinner">
                                <td style="padding: 2px 8px; text-align: center;">
                                    <input type="checkbox" class="meal-checkbox" data-meal-id="2">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <i class="ri-close-circle-fill text-danger me-1" style="font-size: 14px;"></i>
                                    Dinner
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="number" class="form-control form-control-sm meal-count" data-meal-id="2" value="5" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="number" class="form-control form-control-sm meal-adult-qty" data-meal-id="2" value="4" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="text" class="form-control form-control-sm meal-adult-charge" data-meal-id="2" value="SGD 16.00" style="font-size: 10px; padding: 2px 4px;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="number" class="form-control form-control-sm meal-child-qty" data-meal-id="2" value="2" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="text" class="form-control form-control-sm meal-child-charge" data-meal-id="2" value="SGD 16.00" style="font-size: 10px; padding: 2px 4px;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="number" class="form-control form-control-sm meal-infant-qty" data-meal-id="2" value="0" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="text" class="form-control form-control-sm meal-infant-charge" data-meal-id="2" value="SGD 0.00" style="font-size: 10px; padding: 2px 4px;">
                                </td>
                            </tr>
                            <!-- Lunch -->
                            <tr class="meal-row" data-meal-id="3" data-meal-name="Lunch" data-meal-type="lunch">
                                <td style="padding: 2px 8px; text-align: center;">
                                    <input type="checkbox" class="meal-checkbox" data-meal-id="3">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <i class="ri-close-circle-fill text-danger me-1" style="font-size: 14px;"></i>
                                    Lunch
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="number" class="form-control form-control-sm meal-count" data-meal-id="3" value="5" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="number" class="form-control form-control-sm meal-adult-qty" data-meal-id="3" value="4" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="text" class="form-control form-control-sm meal-adult-charge" data-meal-id="3" value="SGD 16.00" style="font-size: 10px; padding: 2px 4px;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="number" class="form-control form-control-sm meal-child-qty" data-meal-id="3" value="2" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="text" class="form-control form-control-sm meal-child-charge" data-meal-id="3" value="SGD 16.00" style="font-size: 10px; padding: 2px 4px;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="number" class="form-control form-control-sm meal-infant-qty" data-meal-id="3" value="0" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="text" class="form-control form-control-sm meal-infant-charge" data-meal-id="3" value="SGD 0.00" style="font-size: 10px; padding: 2px 4px;">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Restaurant Transfer Section -->
                <div class="border-top pt-2 mt-2 px-2">
                    <div class="row g-2 mb-1">
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="restaurantTransferCheckbox" onchange="toggleRestaurantTransferFields()">
                                <label class="form-check-label small" for="restaurantTransferCheckbox">
                                    <strong>Add Transfer for this Restaurant</strong>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Transfer Details (shown when checkbox is checked) -->
                    <div id="restaurantTransferDetailsSection" style="display: none;">
                        <div class="row g-2 mb-1">
                            <div class="col-3">
                                <label class="form-label small">Destination</label>
                                <select class="form-select form-select-sm" id="restaurantTransferDestination" style="font-size: 10px;">
                                    <option value="">Select Destination</option>
                                    <optgroup label="Ports">
                                        @foreach($ports as $port)
                                            <option value="port_{{ $port->id }}" data-name="{{ $port->port_name }}" data-type="port" data-country="{{ $port->country }}">{{ $port->port_name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Hotels">
                                        @foreach($hotels as $hotel)
                                            <option value="hotel_{{ $hotel->id }}" data-name="{{ $hotel->name }}" data-type="hotel" data-city="{{ $hotel->city ?? '' }}" data-country="{{ $hotel->country ?? '' }}">{{ $hotel->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Attractions">
                                        @foreach($attractions as $attr)
                                            <option value="attraction_{{ $attr->attraction_id }}" data-name="{{ $attr->name }}" data-type="attraction" data-location="{{ $attr->location ?? '' }}" data-country="{{ $attr->country ?? '' }}">{{ $attr->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Restaurants">
                                        @foreach($restaurants as $rest)
                                            <option value="restaurant_{{ $rest->restaurant_id }}" data-name="{{ $rest->name }}" data-type="restaurant" data-city="{{ $rest->city ?? '' }}" data-country="{{ $rest->country ?? '' }}">{{ $rest->name }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-3">
                                <label class="form-label small">Vehicle Type</label>
                                <select class="form-select form-select-sm" id="restaurantTransferVehicleType" style="font-size: 10px;">
                                    <option value="">Select Vehicle</option>
                                    @php
                                        $vehicleTypes = $vehicles->groupBy('vehicle_type');
                                    @endphp
                                    @foreach($vehicleTypes as $type => $typeVehicles)
                                        <optgroup label="{{ ucfirst($type) }}">
                                            @foreach($typeVehicles as $vehicle)
                                                <option value="{{ $vehicle->vehicle_id }}" 
                                                    data-type="{{ $vehicle->vehicle_type }}"
                                                    data-seating="{{ $vehicle->seating_capacity }}"
                                                    data-base-price="{{ $vehicle->base_price ?? 0 }}"
                                                    data-sharable-price="{{ $vehicle->sharable_base_price ?? 0 }}">
                                                    {{ $vehicle->vehicle_name }} ({{ $vehicle->seating_capacity }} seats)
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3">
                                <label class="form-label small">Way</label>
                                <select class="form-select form-select-sm" id="restaurantTransferWay" style="font-size: 10px;">
                                    <option value="one-way">1 Way[H/R]</option>
                                    <option value="both-way">2 Way[H/R]</option>
                                </select>
                            </div>
                            <div class="col-3">
                                <label class="form-label small">Transfer Type</label>
                                <select class="form-select form-select-sm" id="restaurantTransferType" style="font-size: 10px;">
                                    <option value="S">Shared</option>
                                    <option value="P">Private</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Notes -->
                <div class="border-top p-2" style="background: #f8f9fa; font-size: 10px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="me-3"><strong>Number of records:</strong> 3</span>
                        </div>
                        <div>
                            <strong>Total Amount:</strong> <input type="text" class="form-control form-control-sm d-inline-block" id="mealTotalAmount" value="SGD 0.00" readonly style="width: 120px; font-size: 11px; padding: 2px 6px;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2" style="background: #f8f9fa;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="addAnotherMeal()" style="font-size: 11px;">
                    <i class="ri-add-line me-1"></i>Add Another
                </button>
                <button type="button" class="btn btn-primary btn-sm" onclick="saveAndCloseMeals()" style="font-size: 11px;">
                    <i class="ri-save-line me-1"></i>Save & Close
                </button>
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal" style="font-size: 11px;">
                    <i class="ri-close-line me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Transfer Package Modal -->
<div class="modal fade" id="transferModal" tabindex="-1" aria-labelledby="transferModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 700px;">
        <div class="modal-content">
            <div class="modal-header bg-success text-white py-2">
                <h6 class="modal-title mb-0" id="transferModalLabel">
                    <i class="ri-car-line me-2"></i><span id="transferModalTitleText">Add Transfer Package</span>
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 8px 12px;">
                <!-- Transport Mode Selection -->
                <div class="border rounded p-2 mb-2" style="background: #f8f9fa;">
                    <label class="form-label small mb-1" style="font-weight: 600;">Transport Mode</label>
                    <div class="d-flex gap-3 align-items-center">
                        <label class="d-flex align-items-center" style="cursor: pointer;">
                            <input type="radio" name="transferMode" value="local" checked style="margin-right: 5px;" onchange="switchTransferMode('local')">
                            <i class="ri-car-line" style="font-size: 24px; color: #666;"></i>
                            <span class="ms-1" style="font-size: 11px;">Local Transfer</span>
                        </label>
                        <label class="d-flex align-items-center" style="cursor: pointer;">
                            <input type="radio" name="transferMode" value="flight" style="margin-right: 5px;" onchange="switchTransferMode('flight')">
                            <i class="ri-flight-takeoff-line" style="font-size: 24px; color: #666;"></i>
                            <span class="ms-1" style="font-size: 11px;">Flight</span>
                        </label>
                        <label class="d-flex align-items-center" style="cursor: pointer;">
                            <input type="radio" name="transferMode" value="cruise" style="margin-right: 5px;" onchange="switchTransferMode('cruise')">
                            <i class="ri-ship-line" style="font-size: 24px; color: #666;"></i>
                            <span class="ms-1" style="font-size: 11px;">Cruise</span>
                        </label>
                        <label class="d-flex align-items-center" style="cursor: pointer;">
                            <input type="radio" name="transferMode" value="train" style="margin-right: 5px;" onchange="switchTransferMode('train')">
                            <i class="ri-train-line" style="font-size: 24px; color: #666;"></i>
                            <span class="ms-1" style="font-size: 11px;">Train</span>
                        </label>
                        <label class="d-flex align-items-center" style="cursor: pointer;">
                            <input type="radio" name="transferMode" value="bus" style="margin-right: 5px;" onchange="switchTransferMode('bus')">
                            <i class="ri-bus-line" style="font-size: 24px; color: #666;"></i>
                            <span class="ms-1" style="font-size: 11px;">Bus</span>
                        </label>
                    </div>
                </div>

                <!-- Local Transfer Form -->
                <div id="localTransferForm" class="transfer-mode-form">
                    <div class="row g-2 mb-2">
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 2px;">Date</label>
                            <input type="date" class="form-control form-control-sm" id="localDateTime">
                        </div>
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 2px;">Pickup</label>
                            <select class="form-select form-select-sm" id="localPickup">
                                <option value="">Select Pickup Location</option>
                                <optgroup label="Ports">
                                    @foreach($ports as $port)
                                        <option value="port_{{ $port->id }}" data-name="{{ $port->port_name }}" data-type="port" data-country="{{ $port->country }}">{{ $port->port_name }} ({{ $port->type }})</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Hotels">
                                    @foreach($hotels as $hotel)
                                        <option value="hotel_{{ $hotel->id }}" data-name="{{ $hotel->name }}" data-type="hotel" data-city="{{ $hotel->city ?? '' }}" data-country="{{ $hotel->country ?? '' }}">{{ $hotel->name }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Attractions">
                                    @foreach($attractions as $attr)
                                        <option value="attraction_{{ $attr->attraction_id }}" data-name="{{ $attr->name }}" data-type="attraction" data-location="{{ $attr->location ?? '' }}" data-country="{{ $attr->country ?? '' }}">{{ $attr->name }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Restaurants">
                                    @foreach($restaurants as $rest)
                                        <option value="restaurant_{{ $rest->restaurant_id }}" data-name="{{ $rest->name }}" data-type="restaurant" data-city="{{ $rest->city ?? '' }}" data-country="{{ $rest->country ?? '' }}">{{ $rest->name }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 2px;">Drop</label>
                            <select class="form-select form-select-sm" id="localDrop">
                                <option value="">Select Drop Location</option>
                                <optgroup label="Ports">
                                    @foreach($ports as $port)
                                        <option value="port_{{ $port->id }}" data-name="{{ $port->port_name }}" data-type="port" data-country="{{ $port->country }}">{{ $port->port_name }} ({{ $port->type }})</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Hotels">
                                    @foreach($hotels as $hotel)
                                        <option value="hotel_{{ $hotel->id }}" data-name="{{ $hotel->name }}" data-type="hotel" data-city="{{ $hotel->city ?? '' }}" data-country="{{ $hotel->country ?? '' }}">{{ $hotel->name }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Attractions">
                                    @foreach($attractions as $attr)
                                        <option value="attraction_{{ $attr->attraction_id }}" data-name="{{ $attr->name }}" data-type="attraction" data-location="{{ $attr->location ?? '' }}" data-country="{{ $attr->country ?? '' }}">{{ $attr->name }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Restaurants">
                                    @foreach($restaurants as $rest)
                                        <option value="restaurant_{{ $rest->restaurant_id }}" data-name="{{ $rest->name }}" data-type="restaurant" data-city="{{ $rest->city ?? '' }}" data-country="{{ $rest->country ?? '' }}">{{ $rest->name }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 2px;">Vehicle Type</label>
                            <select class="form-select form-select-sm" id="localVehicleType">
                                <option value="">Select Vehicle</option>
                                @php
                                    $vehicleTypes = $vehicles->groupBy('vehicle_type');
                                @endphp
                                @foreach($vehicleTypes as $type => $typeVehicles)
                                    <optgroup label="{{ ucfirst($type) }}">
                                        @foreach($typeVehicles as $vehicle)
                                            <option value="{{ $vehicle->vehicle_id }}" 
                                                data-type="{{ $vehicle->vehicle_type }}"
                                                data-seating="{{ $vehicle->seating_capacity }}"
                                                data-base-price="{{ $vehicle->base_price ?? 0 }}"
                                                data-sharable-price="{{ $vehicle->sharable_base_price ?? 0 }}">
                                                {{ $vehicle->vehicle_name }} ({{ $vehicle->seating_capacity }} seats)
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 2px;">Transfer Type</label>
                            <select class="form-select form-select-sm" id="localType">
                                <option value="P">Private</option>
                                <option value="S" selected>Shared</option>
                            </select>
                        </div>
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 2px;">Way</label>
                            <select class="form-select form-select-sm" id="localWay">
                                <option value="one-way">One Way</option>
                                <option value="both-way" selected>Both Way</option>
                            </select>
                        </div>
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 2px;">Adults</label>
                            <input type="number" class="form-control form-control-sm" id="localAdults" value="2" min="0">
                        </div>
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 2px;">Child</label>
                            <input type="number" class="form-control form-control-sm" id="localChild" value="0" min="0">
                        </div>
                    </div>
                </div>

                <!-- Flight Form -->
                <div id="flightForm" class="transfer-mode-form" style="display: none;">
                    <div class="row g-1 mb-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Depart From</label>
                            <input type="text" class="form-control form-control-sm" id="flightDepartFrom" placeholder="e.g., SIN">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Destination</label>
                            <input type="text" class="form-control form-control-sm" id="flightDestination" placeholder="e.g., KUL">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Trip Type</label>
                            <select class="form-select form-select-sm" id="flightTripType">
                                <option value="return">Return</option>
                                <option value="single">Single Trip</option>
                            </select>
                        </div>
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 1px;">Date of Departure</label>
                            <input type="date" class="form-control form-control-sm" id="flightDepartureDate">
                        </div>
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 1px;">Date of Return</label>
                            <input type="date" class="form-control form-control-sm" id="flightReturnDate">
                        </div>
                    </div>
                    <div class="row g-1 mb-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Airline</label>
                            <input type="text" class="form-control form-control-sm" id="flightAirline" placeholder="Any Airline">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Flight No</label>
                            <input type="text" class="form-control form-control-sm" id="flightNumber" placeholder="Flight No">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Operator</label>
                            <select class="form-select form-select-sm" id="flightOperator">
                                <option value="">Select Operator</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Seating Class</label>
                            <select class="form-select form-select-sm" id="flightClass">
                                <option value="economy">Economy</option>
                                <option value="business">Business</option>
                                <option value="first">First Class</option>
                            </select>
                        </div>
                        <div class="col-1">
                            <label class="form-label small" style="margin-bottom: 1px;">Adults</label>
                            <input type="number" class="form-control form-control-sm" id="flightAdults" value="2" min="0">
                        </div>
                        <div class="col-1">
                            <label class="form-label small" style="margin-bottom: 1px;">Child</label>
                            <input type="number" class="form-control form-control-sm" id="flightChild" value="0" min="0">
                        </div>
                        <div class="col-1">
                            <label class="form-label small" style="margin-bottom: 1px;">Cost</label>
                            <input type="number" class="form-control form-control-sm" id="flightCost" value="0" step="0.01">
                        </div>
                        <div class="col-1">
                            <label class="form-label small" style="margin-bottom: 1px;">Sell</label>
                            <input type="number" class="form-control form-control-sm" id="flightSell" value="0" step="0.01">
                        </div>
                    </div>
                    <div class="row g-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">
                                <input type="checkbox" id="flightTaxIncluded"> Tax Included
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Cruise Form -->
                <div id="cruiseForm" class="transfer-mode-form" style="display: none;">
                    <div class="row g-1 mb-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Depart From</label>
                            <input type="text" class="form-control form-control-sm" id="cruiseDepartFrom" placeholder="e.g., SIN">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">From Terminal</label>
                            <input type="text" class="form-control form-control-sm" id="cruiseFromTerminal" placeholder="Terminal">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">By</label>
                            <select class="form-select form-select-sm" id="cruiseBy">
                                <option value="cruise">Cruise</option>
                                <option value="ferry">Ferry</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Cruise Type</label>
                            <select class="form-select form-select-sm" id="cruiseType">
                                <option value="">Select Cruise Type</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Vessel</label>
                            <select class="form-select form-select-sm" id="cruiseVessel">
                                <option value="">Select Vessel</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Arrival To</label>
                            <select class="form-select form-select-sm" id="cruiseArrivalTo">
                                <option value="">Select Arrival To</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-1 mb-1">
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 1px;">Date of Departure</label>
                            <input type="date" class="form-control form-control-sm" id="cruiseDepartureDate">
                        </div>
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 1px;">Date of Arrival</label>
                            <input type="date" class="form-control form-control-sm" id="cruiseArrivalDate">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Operator</label>
                            <input type="text" class="form-control form-control-sm" id="cruiseOperator" placeholder="e.g., BINTAN FERRY">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Cabin Class</label>
                            <select class="form-select form-select-sm" id="cruiseCabinClass">
                                <option value="economy">Economy</option>
                                <option value="business">Business</option>
                                <option value="suite">Suite</option>
                            </select>
                        </div>
                        <div class="col-1">
                            <label class="form-label small" style="margin-bottom: 1px;">Adults</label>
                            <input type="number" class="form-control form-control-sm" id="cruiseAdults" value="2" min="0">
                        </div>
                        <div class="col-1">
                            <label class="form-label small" style="margin-bottom: 1px;">Child</label>
                            <input type="number" class="form-control form-control-sm" id="cruiseChild" value="0" min="0">
                        </div>
                    </div>
                    <div class="row g-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Cost</label>
                            <input type="number" class="form-control form-control-sm" id="cruiseCost" value="0" step="0.01">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Sell</label>
                            <input type="number" class="form-control form-control-sm" id="cruiseSell" value="0" step="0.01">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">
                                <input type="checkbox" id="cruiseTaxIncluded"> Tax Included
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Train Form -->
                <div id="trainForm" class="transfer-mode-form" style="display: none;">
                    <div class="row g-1 mb-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Depart From</label>
                            <input type="text" class="form-control form-control-sm" id="trainDepartFrom" placeholder="e.g., SIN">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Destination</label>
                            <input type="text" class="form-control form-control-sm" id="trainDestination" placeholder="e.g., KUL">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Trip Type</label>
                            <select class="form-select form-select-sm" id="trainTripType">
                                <option value="return">Return</option>
                                <option value="single">Single Trip</option>
                            </select>
                        </div>
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 1px;">Date of Departure</label>
                            <input type="date" class="form-control form-control-sm" id="trainDepartureDate">
                        </div>
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 1px;">Date of Return</label>
                            <input type="date" class="form-control form-control-sm" id="trainReturnDate">
                        </div>
                    </div>
                    <div class="row g-1 mb-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Operator</label>
                            <select class="form-select form-select-sm" id="trainOperator">
                                <option value="">Select Operator</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Seating Class</label>
                            <select class="form-select form-select-sm" id="trainClass">
                                <option value="1st-class">1st Class</option>
                                <option value="2nd-class">2nd Class</option>
                                <option value="sleeper">Sleeper</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Station Transfer?</label>
                            <select class="form-select form-select-sm" id="trainStationTransfer">
                                <option value="">Select</option>
                                <option value="combi">Combi</option>
                                <option value="van">Van</option>
                            </select>
                        </div>
                        <div class="col-1">
                            <label class="form-label small" style="margin-bottom: 1px;">Adults</label>
                            <input type="number" class="form-control form-control-sm" id="trainAdults" value="2" min="0">
                        </div>
                        <div class="col-1">
                            <label class="form-label small" style="margin-bottom: 1px;">Child</label>
                            <input type="number" class="form-control form-control-sm" id="trainChild" value="0" min="0">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Cost</label>
                            <input type="number" class="form-control form-control-sm" id="trainCost" value="0" step="0.01">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Sell</label>
                            <input type="number" class="form-control form-control-sm" id="trainSell" value="0" step="0.01">
                        </div>
                    </div>
                    <div class="row g-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">
                                <input type="checkbox" id="trainTaxIncluded"> Tax Included
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Bus Form -->
                <div id="busForm" class="transfer-mode-form" style="display: none;">
                    <div class="row g-1 mb-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Depart From</label>
                            <input type="text" class="form-control form-control-sm" id="busDepartFrom" placeholder="e.g., SIN">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Destination</label>
                            <input type="text" class="form-control form-control-sm" id="busDestination" placeholder="e.g., KUL">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Trip Type</label>
                            <select class="form-select form-select-sm" id="busTripType">
                                <option value="return">Return</option>
                                <option value="single">Single Trip</option>
                            </select>
                        </div>
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 1px;">Date of Departure</label>
                            <input type="date" class="form-control form-control-sm" id="busDepartureDate">
                        </div>
                        <div class="col-3">
                            <label class="form-label small" style="margin-bottom: 1px;">Date of Return</label>
                            <input type="date" class="form-control form-control-sm" id="busReturnDate">
                        </div>
                    </div>
                    <div class="row g-1 mb-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Operator</label>
                            <input type="text" class="form-control form-control-sm" id="busOperator" placeholder="e.g., STARMART COACH">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Seating Class</label>
                            <select class="form-select form-select-sm" id="busClass">
                                <option value="executive">Executive</option>
                                <option value="standard">Standard</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Bus Station Transfer?</label>
                            <select class="form-select form-select-sm" id="busStationTransfer">
                                <option value="">Select</option>
                                <option value="combi">Combi</option>
                                <option value="van">Van</option>
                            </select>
                        </div>
                        <div class="col-1">
                            <label class="form-label small" style="margin-bottom: 1px;">Adults</label>
                            <input type="number" class="form-control form-control-sm" id="busAdults" value="2" min="0">
                        </div>
                        <div class="col-1">
                            <label class="form-label small" style="margin-bottom: 1px;">Child</label>
                            <input type="number" class="form-control form-control-sm" id="busChild" value="0" min="0">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Cost</label>
                            <input type="number" class="form-control form-control-sm" id="busCost" value="0" step="0.01">
                        </div>
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">Sell</label>
                            <input type="number" class="form-control form-control-sm" id="busSell" value="0" step="0.01">
                        </div>
                    </div>
                    <div class="row g-1">
                        <div class="col-2">
                            <label class="form-label small" style="margin-bottom: 1px;">
                                <input type="checkbox" id="busTaxIncluded"> Tax Included
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-1">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-success btn-sm" onclick="saveTransferPackage()" id="saveTransferBtn">
                    <i class="ri-check-line me-1"></i><span id="saveTransferBtnText">Add Transfer</span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    #accommodationModal .form-label.small {
        font-size: 10px;
        font-weight: 500;
        margin-bottom: 2px;
    }
    #accommodationModal .form-control-sm,
    #accommodationModal .form-select-sm {
        font-size: 10px;
        padding: 2px 4px;
        height: 22px;
        min-height: 0.375rem;
        border: 1px solid #ced4da;
    }
    .table th {
        font-size: 0.6rem !important;
    }
    #accommodationModal .row.g-2 {
        row-gap: 4px !important;
        column-gap: 6px !important;
    }
    .btn-xs {
        padding: 2px 6px;
        font-size: 10px;
        line-height: 1.2;
        height: 20px;
    }
    #selectedHotelsList td {
        vertical-align: middle;
        padding: 3px 4px;
        font-size: 10px;
    }
    #accommodationModal .modal-body {
        padding: 8px 12px;
    }
    #accommodationModal .modal-header {
        padding: 6px 12px;
    }
    #accommodationModal .modal-footer {
        padding: 6px 12px;
    }
    #accommodationModal h6 {
        font-size: 11px;
        margin-bottom: 4px;
    }
    
    /* Tour Modal Compact Styling */
    #tourModal .form-label.small {
        font-size: 10px;
        font-weight: 500;
        margin-bottom: 2px;
    }
    #tourModal .form-control-sm,
    #tourModal .form-select-sm {
        font-size: 10px;
        padding: 2px 4px;
        height: 22px;
        min-height: 0.375rem;
        border: 1px solid #ced4da;
    }
    #tourModal .form-check-input {
        width: 14px;
        height: 14px;
        margin-top: 2px;
    }
    #tourModal .row.g-1 {
        row-gap: 2px !important;
        column-gap: 4px !important;
    }
    #tourModal .modal-body {
        padding: 8px 12px;
    }
    #tourModal .modal-header {
        padding: 6px 12px;
    }
    #tourModal .modal-footer {
        padding: 6px 12px;
    }
    #tourModal .border.rounded {
        padding: 4px 6px !important;
        margin-bottom: 4px !important;
    }
    #tourModal h6 {
        font-size: 10px !important;
        margin-bottom: 2px !important;
    }
    
    /* Tour Modal Table Styling */
    #tourModal table {
        border-collapse: collapse;
    }
    #tourModal table th {
        background: #fff;
        font-weight: 600;
        font-size: 11px;
        white-space: nowrap;
        border-bottom: 2px solid #dee2e6;
    }
    #tourModal table td {
        vertical-align: middle;
        font-size: 11px;
        border-bottom: 1px solid #e9ecef;
    }
    #tourModal table tbody tr:hover {
        background-color: #f8f9fa;
    }
    #tourModal table input[type="number"],
    #tourModal table input[type="text"],
    #tourModal table select {
        width: 100%;
        border: 1px solid #dee2e6;
    }
    #tourModal table input[type="checkbox"] {
        cursor: pointer;
        width: 16px;
        height: 16px;
    }
    #tourModal .table-sm td,
    #tourModal .table-sm th {
        padding: 4px 8px;
    }
    #tourModal .btn-check:checked + .btn {
        background-color: #0d6efd;
        color: white;
    }
    
    /* Meal Modal Compact Styling */
    #mealModal .form-label.small {
        font-size: 10px;
        font-weight: 500;
        margin-bottom: 2px;
    }
    #mealModal .form-control-sm,
    #mealModal .form-select-sm {
        font-size: 10px;
        padding: 2px 4px;
        height: 22px;
        min-height: 0.375rem;
        border: 1px solid #ced4da;
    }
    #mealModal .row.g-1 {
        row-gap: 2px !important;
        column-gap: 4px !important;
    }
    #mealModal .modal-body {
        padding: 8px 12px;
    }
    #mealModal .modal-header {
        padding: 6px 12px;
    }
    #mealModal .modal-footer {
        padding: 6px 12px;
    }
    #mealModal .border.rounded {
        padding: 4px 6px !important;
        margin-bottom: 4px !important;
    }
    #mealModal h6 {
        font-size: 10px !important;
        margin-bottom: 2px !important;
    }
    
    /* Meal Modal Table Styling */
    #mealModal table {
        border-collapse: collapse;
    }
    #mealModal table th {
        background: #fff;
        font-weight: 600;
        font-size: 11px;
        white-space: nowrap;
        border-bottom: 2px solid #dee2e6;
    }
    #mealModal table td {
        vertical-align: middle;
        font-size: 11px;
        border-bottom: 1px solid #e9ecef;
    }
    #mealModal table tbody tr:hover {
        background-color: #f8f9fa;
    }
    #mealModal table input[type="number"],
    #mealModal table input[type="text"],
    #mealModal table select {
        width: 100%;
        border: 1px solid #dee2e6;
    }
    #mealModal table input[type="checkbox"] {
        cursor: pointer;
        width: 16px;
        height: 16px;
    }
    #mealModal .table-sm td,
    #mealModal .table-sm th {
        padding: 4px 8px;
    }
    #mealModal .btn-check:checked + .btn {
        background-color: #0d6efd;
        color: white;
    }
    
    /* Guide Modal Compact Styling */
    #guideModal .form-label.small {
        font-size: 10px;
        font-weight: 500;
        margin-bottom: 2px;
    }
    #guideModal .form-control-sm,
    #guideModal .form-select-sm {
        font-size: 10px;
        padding: 2px 4px;
        height: 22px;
        min-height: 0.375rem;
        border: 1px solid #ced4da;
    }
    #guideModal .row.g-1 {
        row-gap: 2px !important;
        column-gap: 4px !important;
    }
    #guideModal .modal-body {
        padding: 8px 12px;
    }
    #guideModal .modal-header {
        padding: 6px 12px;
    }
    #guideModal .modal-footer {
        padding: 6px 12px;
    }

    /* Transfer Modal Styling */
    #transferModal .form-label.small {
        font-size: 10px;
        font-weight: 500;
        margin-bottom: 2px;
    }
    #transferModal .form-control-sm,
    #transferModal .form-select-sm {
        font-size: 10px;
        padding: 2px 4px;
        height: 22px;
        line-height: 1.2;
    }
    #transferModal .row.g-1 {
        row-gap: 2px !important;
        column-gap: 4px !important;
    }
    #transferModal .modal-body {
        padding: 8px 12px;
    }
    #transferModal .modal-header {
        padding: 6px 12px;
    }
    #transferModal .modal-footer {
        padding: 6px 12px;
    }
    
    /* Editable table cells - Match table-custom style */
    #accommodationTable input[type="date"],
    #accommodationTable input[type="number"],
    #accommodationTable select {
        padding: 1px 3px;
        border: 1px solid #ced4da;
        border-radius: 2px;
        font-size: 10px;
        height: 20px;
        min-height: 0.375rem;
    }
    
    #accommodationTable input[type="date"] {
        width: 95px;
    }
    
    #accommodationTable input[type="number"] {
        width: 45px;
        text-align: center;
    }
    
    #accommodationTable select {
        width: 55px;
        padding: 1px 2px;
    }
    
    #accommodationTable .editable-cell {
        cursor: pointer;
        padding: 0;
        font-size: 11px;
    }
    
    #accommodationTable .editable-cell:hover {
        background-color: #f0f0f0;
    }
    
    #accommodationTable input[readonly] {
        cursor: not-allowed;
        background-color: #f5f5f5;
    }

    /* Arrival/Departure table styling */
    #arrivalDepartureTable input[type="number"],
    #arrivalDepartureTable input[type="text"] {
        width: 55px;
        padding: 1px 3px;
        border: 1px solid #ced4da;
        border-radius: 2px;
        font-size: 10px;
        height: 20px;
    }

    /* Tour table styling */
    #tourTable input[type="number"],
    #tourTable input[type="text"],
    #tourTable input[type="checkbox"] {
        padding: 1px 3px;
        border: 1px solid #ced4da;
        border-radius: 2px;
        font-size: 10px;
        height: 20px;
    }

    #tourTable input[type="number"] {
        width: 55px;
        text-align: center;
    }

    /* Meal table styling */
    #mealTable input[type="number"],
    #mealTable input[type="text"] {
        padding: 1px 3px;
        border: 1px solid #ced4da;
        border-radius: 2px;
        font-size: 10px;
        height: 20px;
        width: 55px;
        text-align: center;
    }

    /* Transfer table styling */
    #transferTable input[type="number"],
    #transferTable input[type="text"],
    #transferTable input[type="checkbox"] {
        padding: 1px 3px;
        border: 1px solid #ced4da;
        border-radius: 2px;
        font-size: 10px;
        height: 20px;
    }

    #transferTable input[type="number"],
    #transferTable input[type="text"] {
        text-align: center;
    }

    /* Guide table styling */
    #guideTable input[type="number"],
    #guideTable input[type="text"] {
        padding: 1px 3px;
        border: 1px solid #ced4da;
        border-radius: 2px;
        font-size: 10px;
        height: 20px;
    }

    #guideTable input[type="number"] {
        text-align: center;
    }

    /* Select2 styling for port dropdowns in modal */
    .select2-container--default .select2-selection--single {
        height: 22px !important;
        min-height: 22px !important;
        padding: 0px 4px !important;
        font-size: 10px !important;
        border: 1px solid #ced4da !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 20px !important;
        padding-left: 4px !important;
        font-size: 10px !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 20px !important;
        top: 1px !important;
    }
    
    .select2-dropdown {
        font-size: 10px !important;
    }
    
    .select2-results__option {
        padding: 4px 8px !important;
        font-size: 10px !important;
    }
</style>
@endsection

@section('scripts')
<script>
    // Accommodation Modal Management
    let selectedHotelsTemp = [];
    let accommodationList = [];
    let editingHotelId = null;
    let currentHotelData = null;
    let arrivalDepartureList = [];
    let tourList = [];
    let guideList = [];
    let transferList = [];
    let mealList = [];
    let miscList = [];
    
    // Toggle arrival transfer fields visibility
    function toggleArrivalTransferFields() {
        const transferChecked = document.getElementById('arrivalTransfer').checked;
        const detailsSection = document.getElementById('arrivalTransferDetailsSection');
        if (detailsSection) {
            detailsSection.style.display = transferChecked ? 'block' : 'none';
        }
    }
    
    // Toggle departure transfer fields visibility
    function toggleDepartureTransferFields() {
        const transferChecked = document.getElementById('departureTransfer').checked;
        const detailsSection = document.getElementById('departureTransferDetailsSection');
        if (detailsSection) {
            detailsSection.style.display = transferChecked ? 'block' : 'none';
        }
    }
    
    // Toggle hotel transfer fields visibility
    function toggleHotelTransferFields() {
        const transferChecked = document.getElementById('hotelTransferCheckbox').checked;
        const detailsSection = document.getElementById('hotelTransferDetailsSection');
        if (detailsSection) {
            detailsSection.style.display = transferChecked ? 'block' : 'none';
        }
    }
    
    // ==================== DESTINATION TAGS FUNCTIONALITY ====================
    
    let selectedDestinations = [];
    
    // City to Country mapping from backend
    const cityCountryMap = @json($cityCountryMap ?? []);
    
    // Initialize destination tags functionality
    function initDestinationTags() {
        const container = document.getElementById('destinationTagsContainer');
        const searchInput = document.getElementById('destinationSearchInput');
        const dropdown = document.getElementById('destinationDropdown');
        const hiddenInput = document.getElementById('destinationSelect');
        
        if (!container || !searchInput || !dropdown) return;
        
        // Function to position dropdown using fixed positioning
        function positionDropdown() {
            const rect = container.getBoundingClientRect();
            dropdown.style.position = 'fixed';
            dropdown.style.top = (rect.bottom + 2) + 'px'; // 2px gap below input
            dropdown.style.left = rect.left + 'px';
            dropdown.style.width = rect.width + 'px';
            dropdown.style.zIndex = '99999';
            dropdown.style.display = 'block';
            
            console.log('Positioning dropdown:', {
                top: rect.bottom + 2,
                left: rect.left,
                width: rect.width,
                containerRect: rect
            });
        }
        
        // Show dropdown on input focus only if there's input
        searchInput.addEventListener('focus', () => {
            // Only show dropdown if user starts typing
            if (searchInput.value.trim().length > 0) {
                positionDropdown();
                filterDestinations(searchInput.value);
            }
        });
        
        // Filter destinations as user types
        searchInput.addEventListener('input', (e) => {
            const value = e.target.value;
            if (value.trim().length > 0) {
                positionDropdown();
                filterDestinations(value);
            } else {
                dropdown.style.display = 'none';
            }
        });
        
        // Click on container focuses input
        container.addEventListener('click', (e) => {
            if (e.target === container || e.target.classList.contains('destination-search-input')) {
                searchInput.focus();
            }
        });
        
        // Handle destination selection
        dropdown.addEventListener('click', (e) => {
            const option = e.target.closest('.destination-option');
            if (option) {
                const value = option.getAttribute('data-value');
                if (!selectedDestinations.includes(value)) {
                    addDestinationTag(value);
                }
                searchInput.value = '';
                filterDestinations('');
                searchInput.focus();
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!container.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
        
        // Handle keyboard navigation
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && searchInput.value === '' && selectedDestinations.length > 0) {
                removeDestinationTag(selectedDestinations[selectedDestinations.length - 1]);
            }
        });
        
        // Reposition on window resize or scroll
        window.addEventListener('resize', () => {
            if (dropdown.style.display === 'block') {
                positionDropdown();
            }
        });
        
        window.addEventListener('scroll', () => {
            if (dropdown.style.display === 'block') {
                positionDropdown();
            }
        }, true);
    }
    
    // Add destination tag
    function addDestinationTag(destination) {
        if (selectedDestinations.includes(destination)) return;
        
        selectedDestinations.push(destination);
        updateDestinationTags();
        updateHiddenInput();
        filterPortsBySelectedCountries();
    }
    
    // Remove destination tag
    function removeDestinationTag(destination) {
        selectedDestinations = selectedDestinations.filter(d => d !== destination);
        updateDestinationTags();
        updateHiddenInput();
        filterPortsBySelectedCountries();
    }
    
    // Filter ports and all destination options based on selected countries
    function filterPortsBySelectedCountries() {
        const arrivalPort = document.getElementById('arrivalPort');
        const departurePort = document.getElementById('departurePort');
        
        // If no countries selected, hide arrival and departure ports completely
        const noCountriesSelected = selectedDestinations.length === 0;
        
        // Handle arrival and departure port fields visibility
        if (arrivalPort) {
            const arrivalPortField = document.getElementById('arrivalPortField');
            if (arrivalPortField) {
                arrivalPortField.style.display = noCountriesSelected ? 'none' : '';
            }
            if (noCountriesSelected) {
                arrivalPort.value = '';
            }
        }
        
        if (departurePort) {
            const departurePortField = document.getElementById('departurePortField');
            if (departurePortField) {
                departurePortField.style.display = noCountriesSelected ? 'none' : '';
            }
            if (noCountriesSelected) {
                departurePort.value = '';
            }
        }
        
        // Find all selects with destination options (ports, restaurants, attractions, hotels)
        const allSelects = document.querySelectorAll('select');
        
        allSelects.forEach(select => {
            const currentValue = select.value;
            let hasPortOptions = false;
            let hasLocationBasedOptions = false;
            
            // Check both direct options and options in optgroups
            const options = select.querySelectorAll('option');
            
            options.forEach(option => {
                // Skip the default "Select" options
                if (option.value === '' || !option.value) {
                    return;
                }
                
                const dataType = option.getAttribute('data-type');
                const dataCountry = option.getAttribute('data-country');
                const dataLocation = option.getAttribute('data-location');
                const dataCity = option.getAttribute('data-city');
                
                // Handle ports (depend only on country, not DMC)
                if (dataType === 'port' || dataCountry) {
                    hasPortOptions = true;
                    
                    // If no countries selected, hide ports
                    if (noCountriesSelected) {
                        option.style.display = 'none';
                        if (option.value === currentValue) {
                            select.value = '';
                        }
                    } else {
                        // Show only ports from selected countries
                        if (selectedDestinations.includes(dataCountry)) {
                            option.style.display = '';
                        } else {
                            option.style.display = 'none';
                            if (option.value === currentValue) {
                                select.value = '';
                            }
                        }
                    }
                }
                // Handle attractions (have location field)
                else if (dataType === 'attraction' || dataLocation) {
                    hasLocationBasedOptions = true;
                    
                    if (noCountriesSelected) {
                        option.style.display = 'none';
                        if (option.value === currentValue) {
                            select.value = '';
                        }
                    } else {
                        // First try to use data-country attribute directly
                        const optionCountry = option.getAttribute('data-country');
                        if (optionCountry && selectedDestinations.includes(optionCountry)) {
                            option.style.display = '';
                        } else if (selectedDestinations.includes(dataLocation)) {
                            // Fallback: Show attractions if location matches selected destinations
                            option.style.display = '';
                        } else {
                            option.style.display = 'none';
                            if (option.value === currentValue) {
                                select.value = '';
                            }
                        }
                    }
                }
                // Handle restaurants and hotels (have city field)
                else if (dataType === 'restaurant' || dataType === 'hotel' || dataCity) {
                    hasLocationBasedOptions = true;
                    
                    if (noCountriesSelected) {
                        option.style.display = 'none';
                        if (option.value === currentValue) {
                            select.value = '';
                        }
                    } else {
                        // First try to use data-country attribute directly
                        const optionCountry = option.getAttribute('data-country');
                        if (optionCountry && selectedDestinations.includes(optionCountry)) {
                            option.style.display = '';
                        } else {
                            // Fallback: Show only items from cities in selected countries using cityCountryMap
                            const cityCountry = cityCountryMap[dataCity];
                            if (cityCountry && selectedDestinations.includes(cityCountry)) {
                                option.style.display = '';
                            } else {
                                option.style.display = 'none';
                                if (option.value === currentValue) {
                                    select.value = '';
                                }
                            }
                        }
                    }
                }
            });
            
            // Trigger change event if using Select2
            if ((hasPortOptions || hasLocationBasedOptions) && $(select).hasClass('select2-port')) {
                $(select).trigger('change.select2');
            }
        });
        
        console.log('Filtered destinations for countries:', selectedDestinations);
    }
    
    // Update destination tags display
    function updateDestinationTags() {
        const container = document.getElementById('destinationTagsContainer');
        const searchInput = document.getElementById('destinationSearchInput');
        
        if (!container || !searchInput) return;
        
        // Remove existing tags
        container.querySelectorAll('.destination-tag').forEach(tag => tag.remove());
        
        // Add tags before search input directly in the container
        selectedDestinations.forEach(destination => {
            const tag = document.createElement('span');
            tag.className = 'destination-tag';
            tag.innerHTML = `
                ${destination}
                <span class="remove-tag" onclick="removeDestinationTag('${destination}')">&times;</span>
            `;
            container.insertBefore(tag, searchInput);
        });
        
        // Update dropdown options
        filterDestinations(searchInput.value);
    }
    
    // Filter destinations in dropdown
    function filterDestinations(searchTerm) {
        const dropdown = document.getElementById('destinationDropdown');
        if (!dropdown) return;
        
        // Don't show dropdown if search term is empty
        if (!searchTerm || searchTerm.trim().length === 0) {
            dropdown.style.display = 'none';
            return;
        }
        
        const options = dropdown.querySelectorAll('.destination-option');
        const term = searchTerm.toLowerCase();
        
        options.forEach(option => {
            const value = option.getAttribute('data-value');
            const text = option.textContent.toLowerCase();
            const isSelected = selectedDestinations.includes(value);
            const matches = text.includes(term);
            
            // Show/hide based on search and selection
            option.style.display = matches ? 'block' : 'none';
            
            // Mark as selected
            if (isSelected) {
                option.classList.add('selected');
            } else {
                option.classList.remove('selected');
            }
        });
    }
    
    // Update hidden input with selected destinations
    function updateHiddenInput() {
        const hiddenInput = document.getElementById('destinationSelect');
        if (hiddenInput) {
            hiddenInput.value = selectedDestinations.join(',');
        }
    }
    
    // Get selected destinations
    function getSelectedDestinations() {
        return selectedDestinations;
    }
    
    // ==================== END DESTINATION TAGS ====================
    
    // Helper function to get header values (adults, children, infants, country)
    function getHeaderValues() {
        const adultCount = parseInt(document.getElementById('adultCountInput')?.value || 0);
        const childCount = parseInt(document.getElementById('childCountInput')?.value || 0);
        const infantCount = parseInt(document.getElementById('infantCountInput')?.value || 0);
        
        // Get country/destination (supports multiple selections with tags)
        let country = '';
        let countries = [];
        const destinationSelect = document.getElementById('destinationSelect');
        const destinationDisplay = document.getElementById('destinationDisplay');
        
        if (destinationSelect) {
            // Get from hidden input (comma-separated values)
            const value = destinationSelect.value;
            if (value) {
                countries = value.split(',').map(c => c.trim()).filter(c => c);
                country = countries.join(', '); // For backward compatibility
            }
            // Also try to get from global selectedDestinations array
            if (countries.length === 0 && typeof selectedDestinations !== 'undefined') {
                countries = [...selectedDestinations];
                country = countries.join(', ');
            }
        } else if (destinationDisplay) {
            country = destinationDisplay.value;
            countries = country.split(',').map(c => c.trim()).filter(c => c);
        }
        
        return {
            adults: adultCount,
            children: childCount,
            infants: infantCount,
            country: country,
            countries: countries // Array of selected destinations
        };
    }
    
    // Helper function to auto-fill modal fields from header
    function autoFillModalFields(modalType) {
        const headerValues = getHeaderValues();
        
        // Auto-fill based on modal type
        if (modalType === 'accommodation') {
            // Filter hotel destination dropdown to show only selected countries
            const hotelDestination = document.getElementById('hotelDestination');
            if (hotelDestination) {
                const options = hotelDestination.querySelectorAll('option');
                
                if (headerValues.countries.length > 0) {
                    // Enable dropdown and hide all options except the selected countries
                    hotelDestination.disabled = false;
                    options.forEach(option => {
                        if (option.value === '') {
                            option.style.display = ''; // Keep the default option
                        } else if (headerValues.countries.includes(option.value)) {
                            option.style.display = '';
                        } else {
                            option.style.display = 'none';
                        }
                    });
                    
                    // Auto-select if only one country
                    if (headerValues.countries.length === 1) {
                        hotelDestination.value = headerValues.countries[0];
                        // Trigger onchange to load hotels
                        if (typeof loadHotelsByDestination === 'function') {
                            loadHotelsByDestination();
                        }
                    }
                } else {
                    // No countries selected in header - disable dropdown
                    hotelDestination.disabled = true;
                    hotelDestination.value = '';
                    options.forEach(option => {
                        option.style.display = '';
                    });
                }
            }
            
            // Note: Adults per room is now set per combination in the table
            // No need to auto-fill a single field anymore
        } else if (modalType === 'tour') {
            // Filter tour destination dropdown to show only selected countries
            const tourDestination = document.getElementById('tourDestination');
            if (tourDestination) {
                const options = tourDestination.querySelectorAll('option');
                
                if (headerValues.countries.length > 0) {
                    // Enable dropdown and hide all options except the selected countries
                    tourDestination.disabled = false;
                    options.forEach(option => {
                        if (option.value === '') {
                            option.style.display = ''; // Keep the default option
                        } else if (headerValues.countries.includes(option.value)) {
                            option.style.display = '';
                        } else {
                            option.style.display = 'none';
                        }
                    });
                    
                    // Auto-select if only one country
                    if (headerValues.countries.length === 1) {
                        tourDestination.value = headerValues.countries[0];
                        // Trigger onchange to load attractions
                        if (typeof loadAttractionsByDestination === 'function') {
                            loadAttractionsByDestination();
                        }
                    }
                } else {
                    // No countries selected in header - disable dropdown
                    tourDestination.disabled = true;
                    tourDestination.value = '';
                    options.forEach(option => {
                        option.style.display = '';
                    });
                }
            }
            
            // Auto-fill all attraction rows with adult/child/infant counts and add validation
            setTimeout(() => {
                document.querySelectorAll('.attraction-adult-qty').forEach(input => {
                    if (!input.value || input.value == '0') input.value = headerValues.adults;
                    input.setAttribute('max', headerValues.adults);
                    input.addEventListener('input', function() {
                        if (parseInt(this.value) > headerValues.adults) {
                            this.value = headerValues.adults;
                            alert(`Adults cannot exceed ${headerValues.adults} (header value)`);
                        }
                    });
                });
                document.querySelectorAll('.attraction-child-qty').forEach(input => {
                    if (!input.value || input.value == '0') input.value = headerValues.children;
                    input.setAttribute('max', headerValues.children);
                    input.addEventListener('input', function() {
                        if (parseInt(this.value) > headerValues.children) {
                            this.value = headerValues.children;
                            alert(`Children cannot exceed ${headerValues.children} (header value)`);
                        }
                    });
                });
                document.querySelectorAll('.attraction-infant-qty').forEach(input => {
                    if (!input.value || input.value == '0') input.value = headerValues.infants;
                    input.setAttribute('max', headerValues.infants);
                    input.addEventListener('input', function() {
                        if (parseInt(this.value) > headerValues.infants) {
                            this.value = headerValues.infants;
                            alert(`Infants cannot exceed ${headerValues.infants} (header value)`);
                        }
                    });
                });
            }, 100);
        } else if (modalType === 'meal') {
            // Filter meal destination dropdown to show only selected countries
            const mealDestination = document.getElementById('mealDestination');
            if (mealDestination) {
                const options = mealDestination.querySelectorAll('option');
                
                if (headerValues.countries.length > 0) {
                    // Enable dropdown and hide all options except the selected countries
                    mealDestination.disabled = false;
                    options.forEach(option => {
                        if (option.value === '') {
                            option.style.display = ''; // Keep the default option
                        } else if (headerValues.countries.includes(option.value)) {
                            option.style.display = '';
                        } else {
                            option.style.display = 'none';
                        }
                    });
                    
                    // Auto-select if only one country
                    if (headerValues.countries.length === 1) {
                        mealDestination.value = headerValues.countries[0];
                        // Trigger onchange to load restaurants
                        if (typeof loadRestaurantsByDestination === 'function') {
                            loadRestaurantsByDestination();
                        }
                    }
                } else {
                    // No countries selected in header - disable dropdown
                    mealDestination.disabled = true;
                    mealDestination.value = '';
                    options.forEach(option => {
                        option.style.display = '';
                    });
                }
            }
            
            // Auto-fill all meal rows with adult/child/infant counts and add validation
            setTimeout(() => {
                document.querySelectorAll('.meal-adult-qty').forEach(input => {
                    if (!input.value || input.value == '0') input.value = headerValues.adults;
                    input.setAttribute('max', headerValues.adults);
                    input.addEventListener('input', function() {
                        if (parseInt(this.value) > headerValues.adults) {
                            this.value = headerValues.adults;
                            alert(`Adults cannot exceed ${headerValues.adults} (header value)`);
                        }
                    });
                });
                document.querySelectorAll('.meal-child-qty').forEach(input => {
                    if (!input.value || input.value == '0') input.value = headerValues.children;
                    input.setAttribute('max', headerValues.children);
                    input.addEventListener('input', function() {
                        if (parseInt(this.value) > headerValues.children) {
                            this.value = headerValues.children;
                            alert(`Children cannot exceed ${headerValues.children} (header value)`);
                        }
                    });
                });
                document.querySelectorAll('.meal-infant-qty').forEach(input => {
                    if (!input.value || input.value == '0') input.value = headerValues.infants;
                    input.setAttribute('max', headerValues.infants);
                    input.addEventListener('input', function() {
                        if (parseInt(this.value) > headerValues.infants) {
                            this.value = headerValues.infants;
                            alert(`Infants cannot exceed ${headerValues.infants} (header value)`);
                        }
                    });
                });
            }, 100);
        } else if (modalType === 'transfer') {
            // Auto-fill adult/child/infant counts for all transfer types (local, flight, cruise, train, bus) with validation
            const localAdults = document.getElementById('localAdults');
            const localChild = document.getElementById('localChild');
            const localInfant = document.getElementById('localInfant');
            
            if (localAdults) {
                if (!localAdults.value || localAdults.value == '0' || localAdults.value == '2') localAdults.value = headerValues.adults;
                localAdults.setAttribute('max', headerValues.adults);
                localAdults.addEventListener('input', function() {
                    if (parseInt(this.value) > headerValues.adults) {
                        this.value = headerValues.adults;
                        alert(`Adults cannot exceed ${headerValues.adults} (header value)`);
                    }
                });
            }
            if (localChild) {
                if (!localChild.value || localChild.value == '0') localChild.value = headerValues.children;
                localChild.setAttribute('max', headerValues.children);
                localChild.addEventListener('input', function() {
                    if (parseInt(this.value) > headerValues.children) {
                        this.value = headerValues.children;
                        alert(`Children cannot exceed ${headerValues.children} (header value)`);
                    }
                });
            }
            if (localInfant) {
                if (!localInfant.value || localInfant.value == '0') localInfant.value = headerValues.infants;
                localInfant.setAttribute('max', headerValues.infants);
                localInfant.addEventListener('input', function() {
                    if (parseInt(this.value) > headerValues.infants) {
                        this.value = headerValues.infants;
                        alert(`Infants cannot exceed ${headerValues.infants} (header value)`);
                    }
                });
            }
            
            // Similar for flight transfers
            const flightAdults = document.getElementById('flightAdults');
            const flightChild = document.getElementById('flightChild');
            const flightInfant = document.getElementById('flightInfant');
            
            if (flightAdults) {
                if (!flightAdults.value || flightAdults.value == '0' || flightAdults.value == '2') flightAdults.value = headerValues.adults;
                flightAdults.setAttribute('max', headerValues.adults);
                flightAdults.addEventListener('input', function() {
                    if (parseInt(this.value) > headerValues.adults) {
                        this.value = headerValues.adults;
                        alert(`Adults cannot exceed ${headerValues.adults} (header value)`);
                    }
                });
            }
            if (flightChild) {
                if (!flightChild.value || flightChild.value == '0') flightChild.value = headerValues.children;
                flightChild.setAttribute('max', headerValues.children);
                flightChild.addEventListener('input', function() {
                    if (parseInt(this.value) > headerValues.children) {
                        this.value = headerValues.children;
                        alert(`Children cannot exceed ${headerValues.children} (header value)`);
                    }
                });
            }
            if (flightInfant) {
                if (!flightInfant.value || flightInfant.value == '0') flightInfant.value = headerValues.infants;
                flightInfant.setAttribute('max', headerValues.infants);
                flightInfant.addEventListener('input', function() {
                    if (parseInt(this.value) > headerValues.infants) {
                        this.value = headerValues.infants;
                        alert(`Infants cannot exceed ${headerValues.infants} (header value)`);
                    }
                });
            }
            
            // Similar for cruise transfers
            const cruiseAdults = document.getElementById('cruiseAdults');
            const cruiseChild = document.getElementById('cruiseChild');
            const cruiseInfant = document.getElementById('cruiseInfant');
            
            if (cruiseAdults) {
                if (!cruiseAdults.value || cruiseAdults.value == '0' || cruiseAdults.value == '2') cruiseAdults.value = headerValues.adults;
                cruiseAdults.setAttribute('max', headerValues.adults);
                cruiseAdults.addEventListener('input', function() {
                    if (parseInt(this.value) > headerValues.adults) {
                        this.value = headerValues.adults;
                        alert(`Adults cannot exceed ${headerValues.adults} (header value)`);
                    }
                });
            }
            if (cruiseChild) {
                if (!cruiseChild.value || cruiseChild.value == '0') cruiseChild.value = headerValues.children;
                cruiseChild.setAttribute('max', headerValues.children);
                cruiseChild.addEventListener('input', function() {
                    if (parseInt(this.value) > headerValues.children) {
                        this.value = headerValues.children;
                        alert(`Children cannot exceed ${headerValues.children} (header value)`);
                    }
                });
            }
            if (cruiseInfant) {
                if (!cruiseInfant.value || cruiseInfant.value == '0') cruiseInfant.value = headerValues.infants;
                cruiseInfant.setAttribute('max', headerValues.infants);
                cruiseInfant.addEventListener('input', function() {
                    if (parseInt(this.value) > headerValues.infants) {
                        this.value = headerValues.infants;
                        alert(`Infants cannot exceed ${headerValues.infants} (header value)`);
                    }
                });
            }
            
            // Similar for train transfers
            const trainAdults = document.getElementById('trainAdults');
            const trainChild = document.getElementById('trainChild');
            const trainInfant = document.getElementById('trainInfant');
            
            if (trainAdults) {
                if (!trainAdults.value || trainAdults.value == '0' || trainAdults.value == '2') trainAdults.value = headerValues.adults;
                trainAdults.setAttribute('max', headerValues.adults);
                trainAdults.addEventListener('input', function() {
                    if (parseInt(this.value) > headerValues.adults) {
                        this.value = headerValues.adults;
                        alert(`Adults cannot exceed ${headerValues.adults} (header value)`);
                    }
                });
            }
            if (trainChild) {
                if (!trainChild.value || trainChild.value == '0') trainChild.value = headerValues.children;
                trainChild.setAttribute('max', headerValues.children);
                trainChild.addEventListener('input', function() {
                    if (parseInt(this.value) > headerValues.children) {
                        this.value = headerValues.children;
                        alert(`Children cannot exceed ${headerValues.children} (header value)`);
                    }
                });
            }
            if (trainInfant) {
                if (!trainInfant.value || trainInfant.value == '0') trainInfant.value = headerValues.infants;
                trainInfant.setAttribute('max', headerValues.infants);
                trainInfant.addEventListener('input', function() {
                    if (parseInt(this.value) > headerValues.infants) {
                        this.value = headerValues.infants;
                        alert(`Infants cannot exceed ${headerValues.infants} (header value)`);
                    }
                });
            }
            
            // Similar for bus transfers
            const busAdults = document.getElementById('busAdults');
            const busChild = document.getElementById('busChild');
            const busInfant = document.getElementById('busInfant');
            
            if (busAdults) {
                if (!busAdults.value || busAdults.value == '0' || busAdults.value == '2') busAdults.value = headerValues.adults;
                busAdults.setAttribute('max', headerValues.adults);
                busAdults.addEventListener('input', function() {
                    if (parseInt(this.value) > headerValues.adults) {
                        this.value = headerValues.adults;
                        alert(`Adults cannot exceed ${headerValues.adults} (header value)`);
                    }
                });
            }
            if (busChild) {
                if (!busChild.value || busChild.value == '0') busChild.value = headerValues.children;
                busChild.setAttribute('max', headerValues.children);
                busChild.addEventListener('input', function() {
                    if (parseInt(this.value) > headerValues.children) {
                        this.value = headerValues.children;
                        alert(`Children cannot exceed ${headerValues.children} (header value)`);
                    }
                });
            }
            if (busInfant) {
                if (!busInfant.value || busInfant.value == '0') busInfant.value = headerValues.infants;
                busInfant.setAttribute('max', headerValues.infants);
                busInfant.addEventListener('input', function() {
                    if (parseInt(this.value) > headerValues.infants) {
                        this.value = headerValues.infants;
                        alert(`Infants cannot exceed ${headerValues.infants} (header value)`);
                    }
                });
            }
        } else if (modalType === 'guide') {
            // Filter guide destination dropdown to show only selected countries
            const guideDestination = document.getElementById('guideDestination');
            if (guideDestination) {
                const options = guideDestination.querySelectorAll('option');
                
                if (headerValues.countries.length > 0) {
                    // Enable dropdown and hide all options except the selected countries
                    guideDestination.disabled = false;
                    options.forEach(option => {
                        if (option.value === '') {
                            option.style.display = ''; // Keep the default option
                        } else if (headerValues.countries.includes(option.value)) {
                            option.style.display = '';
                        } else {
                            option.style.display = 'none';
                        }
                    });
                    
                    // Auto-select if only one country
                    if (headerValues.countries.length === 1) {
                        guideDestination.value = headerValues.countries[0];
                        // Trigger onchange to load guides
                        if (typeof loadGuidesByDestination === 'function') {
                            loadGuidesByDestination();
                        }
                    }
                } else {
                    // No countries selected in header - disable dropdown
                    guideDestination.disabled = true;
                    guideDestination.value = '';
                    options.forEach(option => {
                        option.style.display = '';
                    });
                }
            }
        } else if (modalType === 'misc' || modalType === 'miscellaneous') {
            // Filter miscellaneous destination dropdown to show only selected countries
            const miscDestination = document.getElementById('miscDestination');
            if (miscDestination) {
                const options = miscDestination.querySelectorAll('option');
                
                if (headerValues.countries.length > 0) {
                    // Enable dropdown and hide all options except the selected countries
                    miscDestination.disabled = false;
                    options.forEach(option => {
                        if (option.value === '') {
                            option.style.display = ''; // Keep the default option
                        } else if (headerValues.countries.includes(option.value)) {
                            option.style.display = '';
                        } else {
                            option.style.display = 'none';
                        }
                    });
                    
                    // Auto-select if only one country
                    if (headerValues.countries.length === 1) {
                        miscDestination.value = headerValues.countries[0];
                        // Trigger onchange to load misc items
                        if (typeof loadMiscItemsByDestination === 'function') {
                            loadMiscItemsByDestination();
                        }
                    }
                } else {
                    // No countries selected in header - disable dropdown
                    miscDestination.disabled = true;
                    miscDestination.value = '';
                    options.forEach(option => {
                        option.style.display = '';
                    });
                }
            }
            
            // Auto-fill all misc item rows with adult/child/infant counts and add validation
            setTimeout(() => {
                document.querySelectorAll('.misc-adult-qty').forEach(input => {
                    if (!input.value || input.value == '0') input.value = headerValues.adults;
                    input.setAttribute('max', headerValues.adults);
                    input.addEventListener('input', function() {
                        if (parseInt(this.value) > headerValues.adults) {
                            this.value = headerValues.adults;
                            alert(`Adults cannot exceed ${headerValues.adults} (header value)`);
                        }
                    });
                });
                document.querySelectorAll('.misc-child-qty').forEach(input => {
                    if (!input.value || input.value == '0') input.value = headerValues.children;
                    input.setAttribute('max', headerValues.children);
                    input.addEventListener('input', function() {
                        if (parseInt(this.value) > headerValues.children) {
                            this.value = headerValues.children;
                            alert(`Children cannot exceed ${headerValues.children} (header value)`);
                        }
                    });
                });
                document.querySelectorAll('.misc-infant-qty').forEach(input => {
                    if (!input.value || input.value == '0') input.value = headerValues.infants;
                    input.setAttribute('max', headerValues.infants);
                    input.addEventListener('input', function() {
                        if (parseInt(this.value) > headerValues.infants) {
                            this.value = headerValues.infants;
                            alert(`Infants cannot exceed ${headerValues.infants} (header value)`);
                        }
                    });
                });
            }, 100);
        }
    }
    
    // Update Adult Details (Man/Women dropdowns)
    function updateAdultDetails() {
        const adultCount = parseInt(document.getElementById('adultCountInput').value) || 0;
        const container = document.getElementById('adultDetailsContainer');
        
        if (adultCount > 0) {
            container.style.display = 'flex';
            container.style.flexDirection = 'row';
            container.style.gap = '8px';
            container.style.margin = '0';
            container.style.padding = '0';
            
            // Initialize values if not set
            const manInput = document.getElementById('adultManInput');
            const womenInput = document.getElementById('adultWomenInput');
            
            if (!manInput.value || !womenInput.value) {
                // Default: split equally or set to 0
                const currentMan = parseInt(manInput.value) || 0;
                const currentWomen = parseInt(womenInput.value) || 0;
                const total = currentMan + currentWomen;
                
                if (total === 0) {
                    // Default split: if even, split equally; if odd, one more man
                    manInput.value = Math.ceil(adultCount / 2);
                    womenInput.value = Math.floor(adultCount / 2);
                }
            }
            
            // Set max values
            manInput.setAttribute('max', adultCount);
            womenInput.setAttribute('max', adultCount);
        } else {
            container.style.display = 'none';
            document.getElementById('adultManInput').value = 0;
            document.getElementById('adultWomenInput').value = 0;
        }
    }
    
    // Validate Adult Breakdown (Man + Women should equal Adult count)
    function validateAdultBreakdown() {
        const adultCount = parseInt(document.getElementById('adultCountInput').value) || 0;
        const manCount = parseInt(document.getElementById('adultManInput').value) || 0;
        const womenCount = parseInt(document.getElementById('adultWomenInput').value) || 0;
        const total = manCount + womenCount;
        
        if (total > adultCount) {
            alert(`Total Man (${manCount}) + Women (${womenCount}) = ${total} cannot exceed Adult count (${adultCount})`);
            // Auto-adjust: reduce the last changed field
            const manInput = document.getElementById('adultManInput');
            const womenInput = document.getElementById('adultWomenInput');
            
            if (manCount > 0) {
                const excess = total - adultCount;
                manInput.value = Math.max(0, manCount - excess);
            } else if (womenCount > 0) {
                const excess = total - adultCount;
                womenInput.value = Math.max(0, womenCount - excess);
            }
        }
    }
    
    // ==================== HEADER DATE MANAGEMENT ====================
    
    // --- Date helpers for dd-mm-yyyy display while keeping ISO for logic ---
    function formatISOToDisplay(iso) {
        if (!iso || !/^\d{4}-\d{2}-\d{2}$/.test(iso)) return '';
        const [y, m, d] = iso.split('-');
        return `${d}-${m}-${y}`;
    }
    
    function parseDisplayToISO(displayValue) {
        if (!displayValue) return null;
        const trimmed = displayValue.trim();
        if (/^\d{2}-\d{2}-\d{4}$/.test(trimmed)) {
            const [d, m, y] = trimmed.split('-');
            return `${y}-${m}-${d}`;
        }
        return normalizeDateToYYYYMMDD(trimmed);
    }
    
    // Always grab the main header date inputs (IDs are duplicated in layout sidebar)
    function getHeaderStartInput() {
        const nodes = document.querySelectorAll('input#tourStartDate');
        return nodes.length ? nodes[nodes.length - 1] : null;
    }
    
    function getHeaderEndInput() {
        const nodes = document.querySelectorAll('input#tourEndDate');
        return nodes.length ? nodes[nodes.length - 1] : null;
    }
    
    function generateId(prefix = 'svc') {
        return `${prefix}-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 8)}`;
    }
    
    function updateHeaderDisplays() {
        const startEl = getHeaderStartInput();
        const endEl = getHeaderEndInput();
        const startDisplay = document.getElementById('tourStartDateDisplay');
        const endDisplay = document.getElementById('tourEndDateDisplay');
        if (startDisplay) {
            startDisplay.textContent = '';
            startDisplay.style.display = 'none';
        }
        if (endDisplay) {
            endDisplay.textContent = '';
            endDisplay.style.display = 'none';
        }
    }
    
    function setHeaderInputValue(inputEl, isoValue) {
        if (!inputEl) return;
        if (isoValue) {
            inputEl.value = isoValue;
            inputEl.setAttribute('value', isoValue);
        } else {
            inputEl.value = '';
            inputEl.setAttribute('value', '');
        }
        updateHeaderDisplays();
    }
    
    function getHeaderInputISO(inputEl) {
        if (!inputEl) return null;
        // Allow dd-mm-yyyy typed manually; fallback to ISO normalization
        const parsed = parseDisplayToISO(inputEl.value) || normalizeDateToYYYYMMDD(inputEl.value);
        return parsed;
    }
    
    // Get header start and end dates (ISO strings + Date objects)
    function getHeaderDates() {
        const startDateInput = getHeaderStartInput();
        const endDateInput = getHeaderEndInput();
        const startISO = getHeaderInputISO(startDateInput);
        const endISO = getHeaderInputISO(endDateInput);
        
        return {
            startDate: startISO || null,
            endDate: endISO || null,
            startDateObj: startISO ? new Date(startISO) : null,
            endDateObj: endISO ? new Date(endISO) : null
        };
    }
    
    // Normalize date to YYYY-MM-DD format
    function normalizeDateToYYYYMMDD(dateValue) {
        if (!dateValue) return null;
        
        // Normalise common unicode apostrophes so regex works for "Dec '25"
        const cleanValue = String(dateValue).replace(/[\u2018\u2019]/g, "'").trim();
        
        // If it's already in YYYY-MM-DD format, return as is
        if (/^\d{4}-\d{2}-\d{2}$/.test(cleanValue)) {
            return cleanValue;
        }
        
        // If it has time component (YYYY-MM-DDTHH:mm), extract date part directly
        // This avoids timezone conversion issues with datetime-local inputs
        if (cleanValue.includes('T')) {
            const datePart = cleanValue.split('T')[0];
            // Validate it's a proper date format
            if (/^\d{4}-\d{2}-\d{2}$/.test(datePart)) {
                return datePart;
            }
        }
        
        // Handle formats like "21 Dec '25 09:00" or "21 Dec 2025"
        const customMatch = cleanValue.match(/^(\d{1,2})\s+([A-Za-z]{3})\s+'?(\d{2,4})(?:\s+\d{2}:\d{2})?/);
        if (customMatch) {
            try {
                const day = customMatch[1].padStart(2, '0');
                const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const month = (monthNames.indexOf(customMatch[2]) + 1).toString().padStart(2, '0');
                
                // Accept both two-digit and four-digit years
                let year = customMatch[3];
                if (year.length === 2) {
                    year = '20' + year;
                }
                
                return `${year}-${month}-${day}`;
            } catch (e) {
                console.error('Error parsing custom date format:', cleanValue, e);
            }
        }
        
        // For other formats, try to parse the date string directly without timezone conversion
        // Check if it's in a format like "YYYY-MM-DD HH:mm" (with space instead of T)
        const spaceMatch = cleanValue.match(/^(\d{4}-\d{2}-\d{2})\s+\d{2}:\d{2}/);
        if (spaceMatch) {
            return spaceMatch[1];
        }
        
        // NO TIMEZONE CONVERSION - Do not use Date object parsing
        // If we reach here, the format is not supported
        console.warn('Unsupported date format (no timezone conversion available):', cleanValue);
        return null;
    }
    
    // Normalize datetime to YYYY-MM-DDTHH:mm format for datetime-local input
    function normalizeDateTimeLocal(dateValue) {
        if (!dateValue) return '';
        
        const cleanValue = String(dateValue).trim();
        
        // If it's already in YYYY-MM-DDTHH:mm format, return as is
        if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/.test(cleanValue)) {
            return cleanValue.substring(0, 16); // Return YYYY-MM-DDTHH:mm
        }
        
        // If it's just a date (YYYY-MM-DD), add default time
        if (/^\d{4}-\d{2}-\d{2}$/.test(cleanValue)) {
            return cleanValue + 'T00:00';
        }
        
        // Try to parse other formats
        try {
            const date = new Date(cleanValue);
            if (!isNaN(date.getTime())) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                return `${year}-${month}-${day}T${hours}:${minutes}`;
            }
        } catch (e) {
            console.error('Error parsing datetime:', e);
        }
        
        return '';
    }
    
    // When header dates change, update service dates that fall outside the range
    function updateServiceDatesWhenHeaderChanges() {
        console.log('=== Updating service dates to match header range ===');
        
        const startDateInput = getHeaderStartInput();
        const endDateInput = getHeaderEndInput();
        
        if (!startDateInput || !endDateInput) {
            return;
        }
        
        const headerStart = getHeaderInputISO(startDateInput);
        const headerEnd = getHeaderInputISO(endDateInput);
        
        if (!headerStart || !headerEnd) {
            return;
        }
        
        console.log('Header range:', headerStart, 'to', headerEnd);
        
        let updated = false;
        
        // Update tours/attractions - DATE ONLY
        tourList.forEach((tour, index) => {
            const tourDateStr = normalizeDateToYYYYMMDD(tour.dateTime);
            if (tourDateStr) {
                if (tourDateStr < headerStart) {
                    console.log(`Tour ${index + 1} date ${tourDateStr} is before start, updating to ${headerStart}`);
                    tour.dateTime = headerStart;
                    updated = true;
                } else if (tourDateStr > headerEnd) {
                    console.log(`Tour ${index + 1} date ${tourDateStr} is after end, updating to ${headerEnd}`);
                    tour.dateTime = headerEnd;
                    updated = true;
                }
            }
        });
        
        // Update arrival/departure - DATE ONLY
        arrivalDepartureList.forEach((item, index) => {
            const itemDateStr = normalizeDateToYYYYMMDD(item.dateTime);
            if (itemDateStr) {
                if (itemDateStr < headerStart) {
                    console.log(`Arrival/Departure ${index + 1} date ${itemDateStr} is before start, updating to ${headerStart}`);
                    item.dateTime = headerStart;
                    updated = true;
                } else if (itemDateStr > headerEnd) {
                    console.log(`Arrival/Departure ${index + 1} date ${itemDateStr} is after end, updating to ${headerEnd}`);
                    item.dateTime = headerEnd;
                    updated = true;
                }
            }
        });
        
        // Update meals - DATE ONLY
        mealList.forEach((meal, index) => {
            const mealDateStr = normalizeDateToYYYYMMDD(meal.dateTime);
            if (mealDateStr) {
                if (mealDateStr < headerStart) {
                    console.log(`Meal ${index + 1} date ${mealDateStr} is before start, updating to ${headerStart}`);
                    meal.dateTime = headerStart;
                    updated = true;
                } else if (mealDateStr > headerEnd) {
                    console.log(`Meal ${index + 1} date ${mealDateStr} is after end, updating to ${headerEnd}`);
                    meal.dateTime = headerEnd;
                    updated = true;
                }
            }
        });
        
        // Update transfers - DATE ONLY
        transferList.forEach((transfer, index) => {
            const transferDateStr = normalizeDateToYYYYMMDD(transfer.dateTime);
            if (transferDateStr) {
                if (transferDateStr < headerStart) {
                    console.log(`Transfer ${index + 1} date ${transferDateStr} is before start, updating to ${headerStart}`);
                    transfer.dateTime = headerStart;
                    updated = true;
                } else if (transferDateStr > headerEnd) {
                    console.log(`Transfer ${index + 1} date ${transferDateStr} is after end, updating to ${headerEnd}`);
                    transfer.dateTime = headerEnd;
                    updated = true;
                }
            }
        });
        
        // Update guides - DATE ONLY
        guideList.forEach((guide, index) => {
            const guideDateStr = normalizeDateToYYYYMMDD(guide.dateTime);
            if (guideDateStr) {
                if (guideDateStr < headerStart) {
                    console.log(`Guide ${index + 1} date ${guideDateStr} is before start, updating to ${headerStart}`);
                    guide.dateTime = headerStart;
                    updated = true;
                } else if (guideDateStr > headerEnd) {
                    console.log(`Guide ${index + 1} date ${guideDateStr} is after end, updating to ${headerEnd}`);
                    guide.dateTime = headerEnd;
                    updated = true;
                }
            }
        });
        
        // Update accommodations
        accommodationList.forEach((hotel, index) => {
            let hotelUpdated = false;
            
            if (hotel.checkIn) {
                const checkInStr = normalizeDateToYYYYMMDD(hotel.checkIn);
                if (checkInStr && checkInStr < headerStart) {
                    console.log(`Hotel ${index + 1} check-in ${checkInStr} is before start, updating to ${headerStart}`);
                    hotel.checkIn = headerStart;
                    hotelUpdated = true;
                }
            }
            
            if (hotel.checkOut) {
                const checkOutStr = normalizeDateToYYYYMMDD(hotel.checkOut);
                if (checkOutStr && checkOutStr > headerEnd) {
                    console.log(`Hotel ${index + 1} check-out ${checkOutStr} is after end, updating to ${headerEnd}`);
                    hotel.checkOut = headerEnd;
                    hotelUpdated = true;
                }
            }
            
            if (hotelUpdated) {
                updated = true;
            }
        });
        
        if (updated) {
            console.log('Service dates updated, refreshing tables...');
            // Refresh all tables
            if (typeof updateTourTable === 'function') updateTourTable();
            if (typeof updateArrivalDepartureTable === 'function') updateArrivalDepartureTable();
            if (typeof updateMealTable === 'function') updateMealTable();
            if (typeof updateTransferTable === 'function') updateTransferTable();
            if (typeof updateGuideTable === 'function') updateGuideTable();
            if (typeof updateAccommodationTable === 'function') updateAccommodationTable();
            if (typeof recalculateTotals === 'function') recalculateTotals();
        } else {
            console.log('No service date updates needed');
        }
    }
    
    // Update header dates if service date is outside current range
    function updateHeaderDatesIfNeeded(serviceDate) {
        // Skip if dates are still being initialized
        if (window._headerDatesInitializing) {
            console.log('Skipping header date update - still initializing');
            return;
        }
        
        if (!serviceDate) {
            console.log('No service date provided to updateHeaderDatesIfNeeded');
            return;
        }
        
        const startDateInput = getHeaderStartInput();
        const endDateInput = getHeaderEndInput();
        
        if (!startDateInput || !endDateInput) {
            console.log('Header date inputs not found');
            return;
        }
        
        // Normalize date to YYYY-MM-DD format
        const serviceDateStr = normalizeDateToYYYYMMDD(serviceDate);
        
        const currentStartDate = getHeaderInputISO(startDateInput);
        const currentEndDate = getHeaderInputISO(endDateInput);
        
        console.log('updateHeaderDatesIfNeeded called:', {
            originalDate: serviceDate,
            normalized: serviceDateStr,
            currentStart: currentStartDate,
            currentEnd: currentEndDate
        });
        
        if (!serviceDateStr) {
            console.log('Could not normalize service date:', serviceDate);
            return;
        }
        
        let updated = false;
        
        // Update start date if service date is earlier or if start date is empty
        if (!currentStartDate || serviceDateStr < currentStartDate) {
            console.log(`Updating start date from ${currentStartDate} to ${serviceDateStr}`);
            setHeaderInputValue(startDateInput, serviceDateStr);
            updated = true;
        }
        
        // Update end date if service date is later or if end date is empty
        if (!currentEndDate || serviceDateStr > currentEndDate) {
            console.log(`Updating end date from ${currentEndDate} to ${serviceDateStr}`);
            setHeaderInputValue(endDateInput, serviceDateStr);
            updated = true;
        }
        
        if (updated) {
            const targetStartValue = getHeaderInputISO(startDateInput);
            const targetEndValue = getHeaderInputISO(endDateInput);
            
            console.log('Header dates updated, refreshing inputs:', {
                newStart: targetStartValue,
                newEnd: targetEndValue
            });
            
            // Set a flag to skip validation
            window._skipStartDateValidation = true;

            // Update values directly first (keeps UI in sync)
            setHeaderInputValue(startDateInput, targetStartValue);
            setHeaderInputValue(endDateInput, targetEndValue);

            // Trigger all relevant events to refresh any listeners/formatting
            ['input', 'change'].forEach(eventType => {
                startDateInput.dispatchEvent(new Event(eventType, { bubbles: true }));
                endDateInput.dispatchEvent(new Event(eventType, { bubbles: true }));
            });
            
            console.log('After all updates:');
            console.log('  startInput.value:', startDateInput.value);
            console.log('  startInput.valueAsDate:', startDateInput.valueAsDate);
            console.log('  endInput.value:', endDateInput.value);
            console.log('  endInput.valueAsDate:', endDateInput.valueAsDate);
            
            // IMPORTANT: Also update popup date fields if they exist
            const checkInDate = document.getElementById('checkInDate');
            const checkOutDate = document.getElementById('checkOutDate');
            
            if (checkInDate) {
                checkInDate.value = targetStartValue;
                checkInDate.setAttribute('value', targetStartValue);
                console.log('Updated popup checkInDate to:', targetStartValue);
            }
            
            if (checkOutDate) {
                checkOutDate.value = targetEndValue;
                checkOutDate.setAttribute('value', targetEndValue);
                console.log('Updated popup checkOutDate to:', targetEndValue);
            }
            
            // Recalculate nights
            if (typeof calculateNights === 'function') {
                setTimeout(() => calculateNights(), 50);
            }
            
            // Clear the skip validation flag
            setTimeout(() => {
                window._skipStartDateValidation = false;
            }, 200);
            
            console.log('Visual update completed (header + popup dates)');
        } else {
            console.log('No header date update needed');
        }
    }
    
    // Recalculate header dates based on all services
    function recalculateHeaderDatesFromServices() {
        console.log('=== Recalculating header dates from all services ===');
        
        const startDateInput = getHeaderStartInput();
        const endDateInput = getHeaderEndInput();
        
        if (!startDateInput || !endDateInput) {
            console.log('Header date inputs not found');
            return;
        }
        
        let earliestDate = null;
        let latestDate = null;
        
        // Collect all service dates
        const allDates = [];
        
        // Tours/Attractions
        tourList.forEach(tour => {
            if (tour.dateTime) {
                const normalized = normalizeDateToYYYYMMDD(tour.dateTime);
                if (normalized) allDates.push(normalized);
            }
        });
        
        // Meals/Restaurants
        mealList.forEach(meal => {
            if (meal.dateTime) {
                const normalized = normalizeDateToYYYYMMDD(meal.dateTime);
                if (normalized) allDates.push(normalized);
            }
        });
        
        // Guides
        guideList.forEach(guide => {
            if (guide.dateTime) {
                const normalized = normalizeDateToYYYYMMDD(guide.dateTime);
                if (normalized) allDates.push(normalized);
            }
        });
        
        // Transfers
        transferList.forEach(transfer => {
            if (transfer.dateTime) {
                const normalized = normalizeDateToYYYYMMDD(transfer.dateTime);
                if (normalized) allDates.push(normalized);
            }
        });
        
        // Accommodations (check-in and check-out)
        accommodationList.forEach(hotel => {
            if (hotel.checkIn) {
                const normalized = normalizeDateToYYYYMMDD(hotel.checkIn);
                if (normalized) allDates.push(normalized);
            }
            if (hotel.checkOut) {
                const normalized = normalizeDateToYYYYMMDD(hotel.checkOut);
                if (normalized) allDates.push(normalized);
            }
        });
        
        // Arrival/Departure
        arrivalDepartureList.forEach(item => {
            if (item.dateTime) {
                const normalized = normalizeDateToYYYYMMDD(item.dateTime);
                if (normalized) allDates.push(normalized);
            }
        });
        
        console.log('All service dates:', allDates);
        
        if (allDates.length === 0) {
            console.log('No services with dates found. Keeping current header dates.');
            return;
        }
        
        // Find earliest and latest dates
        earliestDate = allDates[0];
        latestDate = allDates[0];
        
        allDates.forEach(date => {
            if (date < earliestDate) {
                earliestDate = date;
            }
            if (date > latestDate) {
                latestDate = date;
            }
        });
        
        const currentStartISO = getHeaderInputISO(startDateInput);
        const currentEndISO = getHeaderInputISO(endDateInput);
        
        console.log('Earliest date:', earliestDate);
        console.log('Latest date:', latestDate);
        console.log('Current start:', currentStartISO);
        console.log('Current end:', currentEndISO);
        
        let updated = false;
        
        // Update start date if different
        if (currentStartISO !== earliestDate) {
            setHeaderInputValue(startDateInput, earliestDate);
            updated = true;
            console.log('✓ Updated start date to:', earliestDate);
        }
        
        // Update end date if different
        if (currentEndISO !== latestDate) {
            setHeaderInputValue(endDateInput, latestDate);
            updated = true;
            console.log('✓ Updated end date to:', latestDate);
        }
        
        if (updated) {
            // Recalculate nights
            if (typeof calculateNights === 'function') {
                calculateNights();
            }
            
            // Set flag to skip validation when triggering change events
            window._skipStartDateValidation = true;
            
            // Trigger multiple events
            startDateInput.dispatchEvent(new Event('input', { bubbles: true }));
            startDateInput.dispatchEvent(new Event('change', { bubbles: true }));
            
            // Set flag again for end date event (it gets reset after first event)
            window._skipStartDateValidation = true;
            endDateInput.dispatchEvent(new Event('input', { bubbles: true }));
            endDateInput.dispatchEvent(new Event('change', { bubbles: true }));
            
            console.log('✓ UI refresh complete');
        } else {
            console.log('No header date update needed');
        }
        
        console.log('=== Header date recalculation complete ===');
    }
    
    // Get default date and time for service modals (use header start date with current time)
    function getDefaultServiceDate() {
        const headerDates = getHeaderDates();
        const now = new Date();
        
        // Get date part (from header or today)
        let dateStr;
        if (headerDates.startDate) {
            dateStr = headerDates.startDate;
        } else {
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            dateStr = `${year}-${month}-${day}`;
        }
        
        // Add current time in HH:mm format
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        
        return `${dateStr}T${hours}:${minutes}`;
    }
    
    // Format datetime-local value for display (with UTC time)
    function formatDateTimeDisplay(datetimeValue) {
        if (!datetimeValue) return '';
        
        try {
            // Parse the datetime-local value (format: YYYY-MM-DDTHH:mm)
            const dt = new Date(datetimeValue);
            
            // Format date part
            const day = String(dt.getDate()).padStart(2, '0');
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const month = monthNames[dt.getMonth()];
            const year = String(dt.getFullYear()).slice(-2);
            
            // Format time part (local time)
            const hours = String(dt.getHours()).padStart(2, '0');
            const minutes = String(dt.getMinutes()).padStart(2, '0');
            
            // Get UTC time
            const utcHours = String(dt.getUTCHours()).padStart(2, '0');
            const utcMinutes = String(dt.getUTCMinutes()).padStart(2, '0');
            
            return `${day} ${month} '${year} ${hours}:${minutes} (UTC ${utcHours}:${utcMinutes})`;
        } catch (e) {
            console.error('Error formatting datetime:', e);
            return datetimeValue;
        }
    }
    
    // ==================== DATE FUNCTIONALITY ====================
    
    function updateStartDate() {
        const startDateInput = getHeaderStartInput();
        const endDateInput = getHeaderEndInput();
        const startDateISO = parseDisplayToISO(startDateInput.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        updateHeaderDisplays();
        
        // Skip validation if being updated by service date management
        // This allows services to set dates in the past if needed
        const skipValidation = window._skipStartDateValidation;
        console.log('→ updateStartDate called, skipValidation flag:', skipValidation);
        if (window._skipStartDateValidation) {
            window._skipStartDateValidation = false;
            console.log('✓ Skipping start date validation (set by service)');
        } else {
            console.log('→ Manual change detected, applying validation...');
            // Validate start date is not less than today (only for manual changes)
            if (startDateISO) {
                const selectedDate = new Date(startDateISO);
                selectedDate.setHours(0, 0, 0, 0);
                
                if (selectedDate < today) {
                    alert('Start date cannot be less than today');
                    const todayIso = today.toISOString().split('T')[0];
                    setHeaderInputValue(startDateInput, todayIso);
                    return;
                }
            }
        }
        
        // Update end date minimum and auto-set to start date + 1
        if (startDateISO) {
            const start = new Date(startDateISO);
            start.setHours(0, 0, 0, 0);
            
            // Minimum end date is start date + 1
            const minEndDate = new Date(startDateISO);
            minEndDate.setDate(minEndDate.getDate() + 1);
            minEndDate.setHours(0, 0, 0, 0);
            const minEndDateStr = minEndDate.toISOString().split('T')[0];
            
            // Auto-set end date to start date + 1 or update if invalid
            const currentEndISO = parseDisplayToISO(endDateInput.value);
            if (!currentEndISO || new Date(currentEndISO) < minEndDate) {
                // If we're updating end date due to service expansion, keep the flag set
                if (skipValidation) {
                    window._skipStartDateValidation = true;
                }
                setHeaderInputValue(endDateInput, minEndDateStr);
            }
            
            // Only adjust service dates if this was a manual change (not triggered by service date update)
            if (!skipValidation) {
                console.log('→ Manual change: Adjusting all service dates to header range...');
                // Calculate and display nights BEFORE adjusting services
                calculateNights();
                adjustAllServiceDatesToHeaderRange();
            } else {
                console.log('✓ Service-triggered change: Skipping service date adjustment');
                // Still calculate nights for display purposes
                calculateNights();
            }
        } else {
            // Reset end date minimum to today if start date is cleared
            const todayStr = today.toISOString().split('T')[0];
            endDateInput.setAttribute('min', todayStr);
            endDateInput.value = '';
            hideNightsDisplay();
        }
    }
    
    function updateEndDate() {
        const startDateInput = getHeaderStartInput();
        const endDateInput = getHeaderEndInput();

        if (!startDateInput || !endDateInput) {
            console.warn('updateEndDate: header date inputs not found');
            return;
        }
        const startDate = parseDisplayToISO(startDateInput.value);
        const endDate = parseDisplayToISO(endDateInput.value);
        updateHeaderDisplays();
        
        // Check if this is being triggered by service date update
        const skipAdjustment = window._skipStartDateValidation;
        console.log('→ updateEndDate called, skipAdjustment flag:', skipAdjustment);
        if (window._skipStartDateValidation) {
            window._skipStartDateValidation = false;
            console.log('✓ Skipping end date adjustment (set by service)');
        }
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            start.setHours(0, 0, 0, 0);
            const end = new Date(endDate);
            end.setHours(0, 0, 0, 0);
            
            // Minimum end date is start date + 1
            const minEndDate = new Date(startDate);
            minEndDate.setDate(minEndDate.getDate() + 1);
            minEndDate.setHours(0, 0, 0, 0);
            
            // Validate that end date is not before start date + 1 (only for manual changes)
            if (!skipAdjustment && end < minEndDate) {
                alert('End date cannot be before start date + 1 day');
                // Auto-adjust: set end date to start date + 1
                setHeaderInputValue(endDateInput, minEndDate.toISOString().split('T')[0]);
                calculateNights();
                return;
            }
            
            // Only adjust service dates if this was a manual change (not triggered by service date update)
            if (!skipAdjustment) {
                // Calculate and display nights BEFORE adjusting services
                calculateNights();
                adjustAllServiceDatesToHeaderRange();
            } else {
                // Still calculate nights for display purposes
                calculateNights();
            }
        } else {
            hideNightsDisplay();
        }
    }
    
    function calculateNights() {
        const startDateInput = getHeaderStartInput();
        const endDateInput = getHeaderEndInput();
        const nightsDisplay = document.getElementById('nightsDisplay');
        const nightsContainer = document.getElementById('nightsDisplayContainer');

        if (!startDateInput || !endDateInput) {
            hideNightsDisplay();
            return;
        }
        
        const startDate = parseDisplayToISO(startDateInput.value);
        const endDate = parseDisplayToISO(endDateInput.value);
        
        if (startDate && endDate) {
            // Parse dates in YYYY-MM-DD format to avoid timezone issues
            const startParts = startDate.split('-');
            const endParts = endDate.split('-');
            
            const start = new Date(parseInt(startParts[0]), parseInt(startParts[1]) - 1, parseInt(startParts[2]));
            const end = new Date(parseInt(endParts[0]), parseInt(endParts[1]) - 1, parseInt(endParts[2]));
            
            // Calculate difference in days (number of nights)
            const timeDiff = end.getTime() - start.getTime();
            const daysDiff = Math.round(timeDiff / (1000 * 60 * 60 * 24));
            
            if (daysDiff > 0) {
                nightsDisplay.textContent = daysDiff;
                nightsContainer.style.display = 'flex';
            } else {
                hideNightsDisplay();
            }
        } else {
            hideNightsDisplay();
        }
        
        // Update arrival/departure date ranges when tour dates change
        updateArrivalDepartureDateRanges();
    }
    
    // Update arrival/departure date ranges based on tour start/end dates
    function updateArrivalDepartureDateRanges() {
        const arrivalDateTime = document.getElementById('arrivalDateTime');
        const departureDateTime = document.getElementById('departureDateTime');
        
        // Set minimum to today (only disable past dates, allow all future dates)
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const todayDateTimeStr = today.toISOString().slice(0, 16);
        
        if (arrivalDateTime) {
            arrivalDateTime.setAttribute('min', todayDateTimeStr);
            arrivalDateTime.removeAttribute('max');
        }
        
        if (departureDateTime) {
            departureDateTime.setAttribute('min', todayDateTimeStr);
            departureDateTime.removeAttribute('max');
        }
    }
    
    // Update all service date ranges (tours, guides, meals) based on tour start/end dates
    function updateAllServiceDateRanges() {
        // Get all service date/time inputs
        const tourDateTime = document.getElementById('tourDateTime');
        const guideModalDateTime = document.getElementById('guideModalDateTime');
        const mealDateTime = document.getElementById('mealDateTime');
        const checkInDate = document.getElementById('checkInDate');
        const checkOutDate = document.getElementById('checkOutDate');
        const arrivalDateTime = document.getElementById('arrivalDateTime');
        const departureDateTime = document.getElementById('departureDateTime');
        const localDateTime = document.getElementById('localDateTime');
        
        // Get header start and end dates
        const tourStart = getHeaderStartInput();
        const tourEnd = getHeaderEndInput();
        const headerStartDate = tourStart?.value || '';
        const headerEndDate = tourEnd?.value || '';
        
        // Set minimum to today for all date fields (only disable past dates)
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const todayStr = today.toISOString().slice(0, 16); // Format: YYYY-MM-DDTHH:MM
        const todayDateStr = today.toISOString().split('T')[0]; // Format: YYYY-MM-DD
        
        // Set min to today and remove max (allow all future dates)
        if (tourDateTime) {
            tourDateTime.setAttribute('min', todayStr);
            tourDateTime.removeAttribute('max');
        }
        
        if (guideModalDateTime) {
            guideModalDateTime.setAttribute('min', todayStr);
            guideModalDateTime.removeAttribute('max');
        }
        
        if (mealDateTime) {
            mealDateTime.setAttribute('min', todayStr);
            mealDateTime.removeAttribute('max');
        }
        
        if (checkInDate) {
            checkInDate.setAttribute('min', todayDateStr);
            checkInDate.removeAttribute('max');
        }
        
        if (checkOutDate) {
            checkOutDate.setAttribute('min', todayDateStr);
            checkOutDate.removeAttribute('max');
        }
        
        if (arrivalDateTime) {
            arrivalDateTime.setAttribute('min', todayStr);
            arrivalDateTime.removeAttribute('max');
        }
        
        if (departureDateTime) {
            departureDateTime.setAttribute('min', todayStr);
            departureDateTime.removeAttribute('max');
        }
        
        // Set local transfer date constraints based on header dates
        if (localDateTime) {
            // Use header start date as min, or today if no header date
            const minDate = headerStartDate || todayDateStr;
            // Use header end date as max, or no max if no header date
            localDateTime.setAttribute('min', minDate);
            if (headerEndDate) {
                localDateTime.setAttribute('max', headerEndDate);
            } else {
                localDateTime.removeAttribute('max');
            }
        }
    }
    
    // Check if a date is outside header range and expand header dates if needed
    function expandHeaderDatesIfNeeded(dateValue, isDateTime = false) {
        try {
            const tourStart = getHeaderStartInput();
            const tourEnd = getHeaderEndInput();
            
            if (!tourStart || !tourEnd) {
                console.error('expandHeaderDatesIfNeeded: Header date inputs not found!');
                return;
            }
            
            if (!dateValue) {
                console.log('expandHeaderDatesIfNeeded: No date value provided');
                return;
            }
            
            // Extract date part from datetime-local if needed (normalize to ISO)
            const rawDateOnly = isDateTime ? dateValue.split('T')[0] : dateValue;
            const dateOnly = normalizeDateToYYYYMMDD(rawDateOnly);
            const currentStartISO = getHeaderInputISO(tourStart);
            const currentEndISO = getHeaderInputISO(tourEnd);
            
            console.log('=== expandHeaderDatesIfNeeded called ===');
            console.log('Input dateValue:', dateValue);
            console.log('isDateTime:', isDateTime);
            console.log('dateOnly:', dateOnly);
            console.log('Current header Start:', currentStartISO);
            console.log('Current header End:', currentEndISO);
            
            let headerUpdated = false;
            
            // Check if header dates are set (empty string means not set)
            if (!currentStartISO || !currentEndISO) {
                console.log('Header dates are empty or not set. Initializing...');
                
                // If header dates not set, set them based on selected date
                if (!currentStartISO) {
                    console.log('✓ Header START is empty, initializing...');
                    // Remove any min/max constraints that might block the value
                    tourStart.removeAttribute('min');
                    tourStart.removeAttribute('max');
                    
                    console.log('  Setting tourStart value to:', dateOnly);
                    setHeaderInputValue(tourStart, dateOnly);
                    
                    // Set flag to prevent adjustAllServiceDatesToHeaderRange from being called
                    window._skipStartDateValidation = true;
                    
                    // Force the input to update by triggering change event
                    tourStart.dispatchEvent(new Event('change', { bubbles: true }));
                    
                    headerUpdated = true;
                } else {
                    console.log('✓ Header START already has value:', tourStart.value, '- will check if expansion needed');
                }
                
                if (!currentEndISO) {
                    console.log('✓ Header END is empty, initializing...');
                    // Remove any min/max constraints that might block the value
                    tourEnd.removeAttribute('min');
                    tourEnd.removeAttribute('max');
                    
                    // Calculate next day using Date object only for arithmetic
                    const tempDate = new Date(dateOnly + 'T00:00:00');
                    tempDate.setDate(tempDate.getDate() + 1);
                    const year = tempDate.getFullYear();
                    const month = String(tempDate.getMonth() + 1).padStart(2, '0');
                    const day = String(tempDate.getDate()).padStart(2, '0');
                    const endDateValue = `${year}-${month}-${day}`;
                    
                    console.log('  Setting tourEnd value to:', endDateValue);
                    setHeaderInputValue(tourEnd, endDateValue);
                    
                    // Set flag to prevent adjustAllServiceDatesToHeaderRange from being called
                    window._skipStartDateValidation = true;
                    
                    // Force the input to update by triggering change event
                    tourEnd.dispatchEvent(new Event('change', { bubbles: true }));
                    
                    headerUpdated = true;
                } else {
                    console.log('✓ Header END already has value:', tourEnd.value, '- will check if expansion needed');
                }
            } else {
                console.log('Header dates already set. Checking if expansion needed...');
                
                const startDateStr = currentStartISO;
                const endDateStr = currentEndISO;
                
                console.log('Header Start date string:', startDateStr);
                console.log('Header End date string:', endDateStr);
                console.log('Comparing dates (string comparison)...');
                console.log('  dateOnly < startDateStr?', dateOnly < startDateStr, '(', dateOnly, '<', startDateStr, ')');
                console.log('  dateOnly > endDateStr?', dateOnly > endDateStr, '(', dateOnly, '>', endDateStr, ')');
                
                // Check if selected date is before start date (string comparison works for YYYY-MM-DD)
                if (dateOnly < startDateStr) {
                    console.log('✓ Service date is BEFORE header start date!');
                    console.log('  Expanding header start from', startDateStr, 'to', dateOnly);
                    
                    // Remove constraints and set value
                    tourStart.removeAttribute('min');
                    tourStart.removeAttribute('max');
                    setHeaderInputValue(tourStart, dateOnly);
                    console.log('  Header start value after setting:', dateOnly);
                    
                    // Set flag to prevent adjustAllServiceDatesToHeaderRange from being called
                    // We're expanding the header to fit services, not the other way around
                    window._skipStartDateValidation = true;
                    
                    // Force update - use 'change' event to trigger onchange handler
                    tourStart.dispatchEvent(new Event('change', { bubbles: true }));
                    
                    headerUpdated = true;
                }
                
                // Check if selected date is after end date (string comparison works for YYYY-MM-DD)
                if (dateOnly > endDateStr) {
                    console.log('✓ Service date is AFTER header end date!');
                    console.log('  Expanding header end from', endDateStr, 'to', dateOnly);
                    
                    // Remove constraints and set value
                    tourEnd.removeAttribute('min');
                    tourEnd.removeAttribute('max');
                    setHeaderInputValue(tourEnd, dateOnly);
                    console.log('  Header end value after setting:', dateOnly);
                    
                    // Set flag to prevent adjustAllServiceDatesToHeaderRange from being called
                    // We're expanding the header to fit services, not the other way around
                    window._skipStartDateValidation = true;
                    
                    // Force update - use 'change' event to trigger onchange handler
                    tourEnd.dispatchEvent(new Event('change', { bubbles: true }));
                    
                    headerUpdated = true;
                }
                
                if (!headerUpdated) {
                    console.log('✓ Service date is within header range. No expansion needed.');
                }
            }
            
            // If header was updated, recalculate nights and update all constraints
            if (headerUpdated) {
                const newStartISO = getHeaderInputISO(tourStart);
                const newEndISO = getHeaderInputISO(tourEnd);
                console.log('✓✓✓ Header dates UPDATED! ✓✓✓');
                console.log('New header range:', newStartISO, 'to', newEndISO);
                
                calculateNights();
                updateAllServiceDateRanges();
                updateArrivalDepartureDateRanges();
                
                // TEMPORARILY DISABLED ALERT TO DEBUG
                // Show visual alert AFTER all updates - using setTimeout to prevent blocking
                // setTimeout(() => {
                //     alert('Header dates automatically updated!\nNew range: ' + formatISOToDisplay(newStartISO) + ' to ' + formatISOToDisplay(newEndISO));
                // }, 100);
                
                console.log('📢 Header dates automatically updated! New range:', formatISOToDisplay(newStartISO), 'to', formatISOToDisplay(newEndISO));
            }
            
            console.log('=== expandHeaderDatesIfNeeded complete ===');
        } catch (error) {
            console.error('ERROR in expandHeaderDatesIfNeeded:', error);
            console.error('Stack trace:', error.stack);
            alert('ERROR in expandHeaderDatesIfNeeded: ' + error.message);
        }
    }
    
    // Adjust all service dates to fit within header date range
    function adjustAllServiceDatesToHeaderRange() {
        console.log('⚠️ adjustAllServiceDatesToHeaderRange() CALLED');
        console.trace('Call stack:');
        
        const tourStart = getHeaderStartInput();
        const tourEnd = getHeaderEndInput();
        
        const startISO = getHeaderInputISO(tourStart);
        const endISO = getHeaderInputISO(tourEnd);

        if (!tourStart || !tourEnd || !startISO || !endISO) {
            console.warn('adjustAllServiceDatesToHeaderRange: header dates missing');
            return;
        }

        console.log('Header range for adjustment:', startISO, 'to', endISO);
        
        // Use string comparison instead of Date objects to avoid timezone issues
        // ISO date strings (YYYY-MM-DD) can be compared directly
        
        let servicesUpdated = false;
        
        // Adjust Arrival/Departure dates - DATE ONLY
        if (arrivalDepartureList && arrivalDepartureList.length > 0) {
            arrivalDepartureList.forEach(item => {
                if (item.dateTime) {
                    const dateOnly = normalizeDateToYYYYMMDD(item.dateTime);
                    
                    // String comparison works for YYYY-MM-DD format
                    if (dateOnly < startISO) {
                        item.dateTime = startISO;
                        servicesUpdated = true;
                    } else if (dateOnly > endISO) {
                        item.dateTime = endISO;
                        servicesUpdated = true;
                    }
                }
            });
        }
        
        // Adjust Accommodation dates
        if (accommodationList && accommodationList.length > 0) {
            accommodationList.forEach(hotel => {
                if (hotel.checkIn) {
                    // String comparison for YYYY-MM-DD format
                    if (hotel.checkIn < startISO) {
                        hotel.checkIn = startISO;
                        servicesUpdated = true;
                    } else if (hotel.checkIn > endISO) {
                        hotel.checkIn = endISO;
                        servicesUpdated = true;
                    }
                }
                
                if (hotel.checkOut) {
                    // String comparison for YYYY-MM-DD format
                    if (hotel.checkOut < startISO) {
                        hotel.checkOut = startISO;
                        servicesUpdated = true;
                    } else if (hotel.checkOut > endISO) {
                        hotel.checkOut = endISO;
                        servicesUpdated = true;
                    }
                }
                
                // Recalculate nights if dates were adjusted (still need Date objects for day difference)
                if (hotel.checkIn && hotel.checkOut) {
                    // For calculating nights, we need the day difference
                    // Since we're comparing dates at midnight, this is safe
                    const checkInParts = hotel.checkIn.split('-');
                    const checkOutParts = hotel.checkOut.split('-');
                    const checkIn = new Date(checkInParts[0], checkInParts[1] - 1, checkInParts[2]);
                    const checkOut = new Date(checkOutParts[0], checkOutParts[1] - 1, checkOutParts[2]);
                    const timeDiff = checkOut.getTime() - checkIn.getTime();
                    hotel.nights = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
                }
            });
        }
        
        // Adjust Tour/Attraction dates - DATE ONLY
        if (tourList && tourList.length > 0) {
            tourList.forEach((tour, index) => {
                if (tour.dateTime) {
                    const dateOnly = normalizeDateToYYYYMMDD(tour.dateTime);
                    
                    console.log(`Checking tour ${index + 1}: dateOnly=${dateOnly}, startISO=${startISO}, endISO=${endISO}`);
                    
                    // String comparison for YYYY-MM-DD format (no timezone conversion)
                    if (dateOnly < startISO) {
                        console.log(`⚠️ CHANGING tour ${index + 1} date from ${dateOnly} to ${startISO}`);
                        tour.dateTime = startISO;
                        servicesUpdated = true;
                    } else if (dateOnly > endISO) {
                        console.log(`⚠️ CHANGING tour ${index + 1} date from ${dateOnly} to ${endISO}`);
                        tour.dateTime = endISO;
                        servicesUpdated = true;
                    } else {
                        console.log(`✓ Tour ${index + 1} date ${dateOnly} is within range, no change needed`);
                    }
                }
            });
        }
        
        // Adjust Restaurant/Meal dates - DATE ONLY
        if (mealList && mealList.length > 0) {
            mealList.forEach(meal => {
                if (meal.dateTime) {
                    const dateOnly = normalizeDateToYYYYMMDD(meal.dateTime);
                    
                    // String comparison for YYYY-MM-DD format (no timezone conversion)
                    if (dateOnly < startISO) {
                        meal.dateTime = startISO;
                        servicesUpdated = true;
                    } else if (dateOnly > endISO) {
                        meal.dateTime = endISO;
                        servicesUpdated = true;
                    }
                }
            });
        }
        
        // Adjust Local Transfer dates - DATE ONLY
        if (transferList && transferList.length > 0) {
            transferList.forEach(transfer => {
                if (transfer.dateTime) {
                    const dateOnly = normalizeDateToYYYYMMDD(transfer.dateTime);
                    
                    // String comparison for YYYY-MM-DD format (no timezone conversion)
                    if (dateOnly < startISO) {
                        transfer.dateTime = startISO;
                        servicesUpdated = true;
                    } else if (dateOnly > endISO) {
                        transfer.dateTime = endISO;
                        servicesUpdated = true;
                    }
                }
            });
        }
        
        // Adjust Tour Guide dates - DATE ONLY
        if (guideList && guideList.length > 0) {
            guideList.forEach(guide => {
                if (guide.dateTime) {
                    const dateOnly = normalizeDateToYYYYMMDD(guide.dateTime);
                    
                    // String comparison for YYYY-MM-DD format (no timezone conversion)
                    if (dateOnly < startISO) {
                        guide.dateTime = startISO;
                        servicesUpdated = true;
                    } else if (dateOnly > endISO) {
                        guide.dateTime = endISO;
                        servicesUpdated = true;
                    }
                }
            });
        }
        
        // If any services were updated, refresh all tables
        if (servicesUpdated) {
            console.log('Service dates adjusted to fit within header range:', startISO, 'to', endISO);
            updateArrivalDepartureTable();
            updateAccommodationTable();
            updateTourTable();
            updateMealTable();
            updateTransferTable();
            updateGuideTable();
        }
    }
    
    function hideNightsDisplay() {
        const nightsContainer = document.getElementById('nightsDisplayContainer');
        if (nightsContainer) {
            nightsContainer.style.display = 'none';
        }
    }
    
    // Set minimum date for start date (today) and disable past dates
    function initializeDates() {
        const startDateInput = getHeaderStartInput();
        const endDateInput = getHeaderEndInput();

        if (!startDateInput || !endDateInput) {
            console.warn('initializeDates: header date inputs not found');
            return;
        }
        
        // Don't set min date restrictions - allow past dates for flexibility
        // Services might have dates in the past, and we want to accommodate them
        console.log('initializeDates: Skipping min date restrictions to allow flexible date selection');
        console.log('initializeDates: Initial start date value:', startDateInput.value);
        console.log('initializeDates: Initial end date value:', endDateInput.value);
        
        // Remove any existing min/max constraints
        startDateInput.removeAttribute('min');
        startDateInput.removeAttribute('max');
        endDateInput.removeAttribute('min');
        endDateInput.removeAttribute('max');

        // Normalize any initial ISO values into dd-mm-yyyy display + dataset
        if (startDateInput.value) {
            const iso = normalizeDateToYYYYMMDD(startDateInput.value);
            setHeaderInputValue(startDateInput, iso);
        }
        if (endDateInput.value) {
            const iso = normalizeDateToYYYYMMDD(endDateInput.value);
            setHeaderInputValue(endDateInput, iso);
        }
        
        // Calculate nights if both dates are set and valid
        // DO NOT call updateStartDate() here to avoid triggering validation during initialization
        if (startDateInput.value && endDateInput.value) {
            const start = new Date(startDateInput.value);
            start.setHours(0, 0, 0, 0);
            const end = new Date(endDateInput.value);
            end.setHours(0, 0, 0, 0);
            const minEndDate = new Date(start);
            minEndDate.setDate(minEndDate.getDate() + 1);
            minEndDate.setHours(0, 0, 0, 0);
            
            // Validate end date is valid (must be >= start date + 1)
            if (end >= minEndDate) {
                calculateNights();
                console.log('initializeDates: Calculated nights successfully');
            } else {
                // Auto-adjust end date if invalid
                console.log('initializeDates: End date is invalid, adjusting...');
                endDateInput.value = minEndDate.toISOString().split('T')[0];
                calculateNights();
            }
        } else {
            console.log('initializeDates: One or both dates are empty, skipping calculation');
        }
    }
    
    // Toggle horizontal scroll for row 2
    function toggleRow2Scroll() {
        const childCount = parseInt(document.getElementById('childCountInput').value) || 0;
        const infantCount = parseInt(document.getElementById('infantCountInput').value) || 0;
        const row2Container = document.querySelector('.row-2-beautiful');
        
        // Enable scrolling only when child or infant > 0
        // Destination maintains 305px width in both states (CSS handles it)
        if (childCount > 0 || infantCount > 0) {
            row2Container.classList.add('scrollable');
        } else {
            row2Container.classList.remove('scrollable');
        }
    }
    
    // Update Child Details (Age dropdowns)
    function updateChildDetails() {
        const childCount = parseInt(document.getElementById('childCountInput').value) || 0;
        const container = document.getElementById('childDetailsContainer');
        
        if (childCount > 0) {
            container.style.display = 'flex';
            container.style.flexDirection = 'row';
            container.style.gap = '8px';
            container.style.margin = '0';
            container.style.padding = '0';
            
            // Clear existing child age inputs
            container.innerHTML = '';
            
            // Create age inputs for each child in a row
            for (let i = 1; i <= childCount; i++) {
                const fieldItem = document.createElement('div');
                fieldItem.className = 'field-item';
                fieldItem.style.flexShrink = '0';
                fieldItem.innerHTML = `
                    <span class="detail-label">C${i}:</span>
                    <select class="form-select form-select-sm beautiful-input" id="childAge${i}" style="width: 70px;">
                        <option value="">Age</option>
                        ${Array.from({length: 18}, (_, age) => `<option value="${age}">${age}</option>`).join('')}
                    </select>
                `;
                container.appendChild(fieldItem);
            }
        } else {
            container.style.display = 'none';
            container.innerHTML = '';
        }
        
        // Toggle scroll after updating
        toggleRow2Scroll();
    }
    
    // Update Infant Details (Baby Cot checkbox)
    function updateInfantDetails() {
        const infantCount = parseInt(document.getElementById('infantCountInput').value) || 0;
        const container = document.getElementById('infantDetailsContainer');
        
        if (infantCount > 0) {
            container.style.display = 'flex';
            container.style.flexDirection = 'row';
            container.style.gap = '8px';
            container.style.margin = '0';
            container.style.padding = '0';
        } else {
            container.style.display = 'none';
            document.getElementById('babyCotRequired').checked = false;
        }
        
        // Toggle scroll after updating
        toggleRow2Scroll();
    }
    
    // Get total pax from header
    function getTotalPax() {
        const adults = parseInt(document.getElementById('adultCountInput')?.value || document.getElementById('adults')?.value || 2);
        const child = parseInt(document.getElementById('childCountInput')?.value || document.getElementById('child')?.value || 0);
        return adults + child;
    }
    
    // Function to scan all existing services and expand header dates accordingly
    function scanAndExpandHeaderDates() {
        const startInput = getHeaderStartInput();
        const endInput = getHeaderEndInput();
        console.log('=== Scanning all services to expand header dates ===');
        console.log('Current header dates BEFORE scan:');
        console.log('  Start:', startInput ? startInput.value : '');
        console.log('  End:', endInput ? endInput.value : '');
        
        let servicesFound = 0;
        
        // Check arrival/departure dates
        if (arrivalDepartureList && arrivalDepartureList.length > 0) {
            console.log('Found', arrivalDepartureList.length, 'arrival/departure entries');
            arrivalDepartureList.forEach((item, index) => {
                if (item.dateTime) {
                    console.log(`Arrival/Departure ${index + 1}:`, item.type, item.dateTime);
                    expandHeaderDatesIfNeeded(item.dateTime, true);
                    servicesFound++;
                }
            });
        } else {
            console.log('No arrival/departure entries found');
        }
        
        // Check accommodation dates
        if (accommodationList && accommodationList.length > 0) {
            console.log('Found', accommodationList.length, 'accommodation entries');
            accommodationList.forEach((hotel, index) => {
                console.log(`Accommodation ${index + 1}:`, hotel);
                if (hotel.checkIn) {
                    console.log(`  Check-In:`, hotel.checkIn);
                    expandHeaderDatesIfNeeded(hotel.checkIn, false);
                    servicesFound++;
                }
                if (hotel.checkOut) {
                    console.log(`  Check-Out:`, hotel.checkOut);
                    expandHeaderDatesIfNeeded(hotel.checkOut, false);
                    servicesFound++;
                }
            });
        } else {
            console.log('No accommodation entries found');
        }
        
        // Check tour dates
        if (tourList && tourList.length > 0) {
            console.log('Found', tourList.length, 'tour entries');
            tourList.forEach((tour, index) => {
                if (tour.dateTime) {
                    console.log(`Tour ${index + 1}:`, tour.dateTime);
                    expandHeaderDatesIfNeeded(tour.dateTime, true);
                    servicesFound++;
                }
            });
        } else {
            console.log('No tour entries found');
        }
        
        // Check meal dates
        if (mealList && mealList.length > 0) {
            console.log('Found', mealList.length, 'meal entries');
            mealList.forEach((meal, index) => {
                if (meal.dateTime) {
                    console.log(`Meal ${index + 1}:`, meal.dateTime);
                    expandHeaderDatesIfNeeded(meal.dateTime, true);
                    servicesFound++;
                }
            });
        } else {
            console.log('No meal entries found');
        }
        
        // Check transfer dates
        if (transferList && transferList.length > 0) {
            console.log('Found', transferList.length, 'transfer entries');
            transferList.forEach((transfer, index) => {
                if (transfer.dateTime) {
                    console.log(`Transfer ${index + 1}:`, transfer.dateTime);
                    expandHeaderDatesIfNeeded(transfer.dateTime, true);
                    servicesFound++;
                }
            });
        } else {
            console.log('No transfer entries found');
        }
        
        // Check guide dates
        if (guideList && guideList.length > 0) {
            console.log('Found', guideList.length, 'guide entries');
            guideList.forEach((guide, index) => {
                if (guide.dateTime) {
                    console.log(`Guide ${index + 1}:`, guide.dateTime);
                    expandHeaderDatesIfNeeded(guide.dateTime, true);
                    servicesFound++;
                }
            });
        } else {
            console.log('No guide entries found');
        }
        
        console.log('=== Scan complete ===');
        console.log('Total services with dates found:', servicesFound);
        console.log('Current header dates AFTER scan:');
        console.log('  Start:', startInput ? startInput.value : '');
        console.log('  End:', endInput ? endInput.value : '');
    }
    
    // Function to load existing table data into JavaScript arrays
    function loadExistingDataIntoArrays() {
        console.log('=== Loading existing table data into arrays ===');
        
        // Load tours/attractions from table
        const tourTableBody = document.getElementById('tourTableBody');
        if (tourTableBody) {
            const rows = tourTableBody.querySelectorAll('tr');
            console.log(`Found ${rows.length} existing tour/attraction rows in table`);
            
            rows.forEach((row, index) => {
                const cells = row.cells;
                if (cells && cells.length >= 11) {
                    // Extract data from table cells
                    // Format: checkbox, dateTime, tourName, PTE, adultsQty, adultCost, adultSell, childQty, childCost, childSell, transfer, guide
                    const dateTime = cells[1]?.textContent.trim();
                    const tourName = cells[2]?.textContent.trim();
                    const pte = cells[3]?.textContent.trim() === '✓' || cells[3]?.innerHTML.includes('check');
                    const adultsQty = parseInt(cells[4]?.textContent.trim()) || 0;
                    const adultCost = parseFloat(cells[5]?.textContent.replace(/[^0-9.]/g, '')) || 0;
                    const adultSell = parseFloat(cells[6]?.textContent.replace(/[^0-9.]/g, '')) || 0;
                    const childQty = parseInt(cells[7]?.textContent.trim()) || 0;
                    const childCost = parseFloat(cells[8]?.textContent.replace(/[^0-9.]/g, '')) || 0;
                    const childSell = parseFloat(cells[9]?.textContent.replace(/[^0-9.]/g, '')) || 0;
                    
                    if (dateTime && tourName) {
                        const tourData = {
                            id: generateId('tour'),
                            destination: 'Singapore',
                            attractionId: null,
                            attractionName: tourName,
                            dateTime: dateTime,
                            pte: pte,
                            adultsQty: adultsQty,
                            adultCost: adultCost,
                            adultSell: adultSell,
                            childQty: childQty,
                            childCost: childCost,
                            childSell: childSell,
                            infantQty: 0,
                            infantCost: 0,
                            infantSell: 0,
                            transferId: null,
                            transferInfo: null,
                            guideId: null,
                            guideInfo: null
                        };
                        
                        tourList.push(tourData);
                        console.log(`Loaded tour/attraction ${index + 1}:`, tourName, dateTime);
                    }
                }
            });
        }
        
        console.log(`Total tours/attractions loaded: ${tourList.length}`);
        
        // Load accommodation from table
        const accommodationTableBody = document.getElementById('accommodationTableBody');
        if (accommodationTableBody) {
            const rows = accommodationTableBody.querySelectorAll('tr');
            console.log(`Found ${rows.length} existing accommodation rows in table`);
            
            rows.forEach((row, index) => {
                const cells = row.cells;
                if (cells && cells.length >= 3) {
                    const hotelName = cells[1]?.textContent.trim();
                    const dateRange = cells[2]?.textContent.trim(); // Format: "DD MMM YY - DD MMM YY"
                    
                    if (hotelName && dateRange) {
                        // Parse check-in and check-out dates from date range
                        const dates = dateRange.split(' - ');
                        const checkIn = dates[0]?.trim();
                        const checkOut = dates[1]?.trim();
                        
                        // Compute nights safely
                        let nights = 0;
                        if (checkIn && checkOut) {
                            const inDate = new Date(checkIn);
                            const outDate = new Date(checkOut);
                            if (!isNaN(inDate) && !isNaN(outDate) && outDate > inDate) {
                                nights = Math.ceil((outDate - inDate) / (1000 * 60 * 60 * 24));
                            }
                        }
                        
                        const accommodationData = {
                            id: generateId('hotel'),
                            hotelId: '', // unknown from table; user can re-pick in modal
                            hotelName: hotelName,
                            destination: '', // fallback; user can set in modal
                            checkIn: checkIn || '',
                            checkOut: checkOut || '',
                            rooms: 1,
                            adultsPerRoom: 2,
                            extraBed: 0,
                            childWithoutBed: 0,
                            mealPlan: 'CP',
                            supplement: '',
                            nights: nights || 0,
                            arrivalDepartureIds: [],
                            isStandalone: true
                        };
                        
                        accommodationList.push(accommodationData);
                        console.log(`Loaded accommodation ${index + 1}:`, hotelName, checkIn, '-', checkOut);
                    }
                }
            });
        }
        
        console.log(`Total accommodations loaded: ${accommodationList.length}`);
        
        // Load arrival/departure from table
        const arrivalDepartureTableBody = document.getElementById('arrivalDepartureTableBody');
        if (arrivalDepartureTableBody) {
            const rows = arrivalDepartureTableBody.querySelectorAll('tr');
            console.log(`Found ${rows.length} existing arrival/departure rows in table`);
            
            rows.forEach((row, index) => {
                const cells = row.cells;
                if (cells && cells.length >= 3) {
                    const dateTime = cells[1]?.textContent.trim();
                    const type = cells[2]?.textContent.trim(); // "Arrival" or "Departure"
                    
                    if (dateTime) {
                        const arrivalDepartureData = {
                            id: generateId('arrdep'),
                            dateTime: dateTime,
                            type: type
                        };
                        
                        arrivalDepartureList.push(arrivalDepartureData);
                        console.log(`Loaded ${type} ${index + 1}:`, dateTime);
                    }
                }
            });
        }
        
        console.log(`Total arrival/departures loaded: ${arrivalDepartureList.length}`);
        
        // Load meals from table
        const mealTableBody = document.getElementById('mealTableBody');
        if (mealTableBody) {
            const rows = mealTableBody.querySelectorAll('tr');
            console.log(`Found ${rows.length} existing meal rows in table`);
            
            rows.forEach((row, index) => {
                const cells = row.cells;
                if (cells && cells.length >= 3) {
                    const dateTime = cells[1]?.textContent.trim();
                    const restaurant = cells[2]?.textContent.trim();
                    
                    if (dateTime && restaurant) {
                        const mealData = {
                            id: generateId('meal'),
                            dateTime: dateTime,
                            destination: document.getElementById('mealDestination')?.value || '',
                            restaurantId: null,
                            restaurantName: restaurant,
                            mealType: restaurant, // fallback if type not provided
                            mealCount: 0,
                            adultsQty: 0,
                            adultCost: 0,
                            adultSell: 0,
                            childQty: 0,
                            childCost: 0,
                            childSell: 0,
                            infantQty: 0,
                            infantCost: 0,
                            infantSell: 0,
                            transferId: null,
                            transferInfo: null,
                            guideId: null,
                            guideInfo: null,
                            isStandalone: true
                        };
                        
                        mealList.push(mealData);
                        console.log(`Loaded meal ${index + 1}:`, restaurant, dateTime);
                    }
                }
            });
        }
        
        console.log(`Total meals loaded: ${mealList.length}`);
        
        // Load transfers from table
        const transferTableBody = document.getElementById('transferTableBody');
        if (transferTableBody) {
            const rows = transferTableBody.querySelectorAll('tr');
            console.log(`Found ${rows.length} existing transfer rows in table`);
            
            rows.forEach((row, index) => {
                const cells = row.cells;
                if (cells && cells.length >= 3) {
                    const dateTime = cells[1]?.textContent.trim();
                    const destination = cells[2]?.textContent.trim();
                    
                    if (dateTime) {
                        const transferData = {
                            id: generateId('transfer'),
                            dateTime: dateTime,
                            destination: destination,
                            // Assume standalone unless explicitly linked
                            isStandalone: true
                        };
                        
                        transferList.push(transferData);
                        console.log(`Loaded transfer ${index + 1}:`, destination, dateTime);
                    }
                }
            });
        }
        
        console.log(`Total transfers loaded: ${transferList.length}`);
        
        // Load guides from table
        const guideTableBody = document.getElementById('guideTableBody');
        if (guideTableBody) {
            const rows = guideTableBody.querySelectorAll('tr');
            console.log(`Found ${rows.length} existing guide rows in table`);
            
            rows.forEach((row, index) => {
                const cells = row.cells;
                if (cells && cells.length >= 3) {
                    const dateTime = cells[1]?.textContent.trim();
                    const tourName = cells[2]?.textContent.trim();
                    
                    if (dateTime) {
                        const guideData = {
                            id: generateId('guide'),
                            dateTime: dateTime,
                            tourName: tourName,
                            // Existing rows are considered standalone unless flagged otherwise
                            isStandalone: true
                        };
                        
                        guideList.push(guideData);
                        console.log(`Loaded guide ${index + 1}:`, tourName, dateTime);
                    }
                }
            });
        }
        
        console.log(`Total guides loaded: ${guideList.length}`);
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Set flag to prevent header date updates during initialization
        window._headerDatesInitializing = true;
        
        // FIX: Force set the correct values from backend if they don't match
        const backendStartDate = '{{ $initialData["tour_start_date"] ?? "" }}';
        const backendEndDate = '{{ $initialData["tour_end_date"] ?? "" }}';
        let startInput = getHeaderStartInput();
        let endInput = getHeaderEndInput();
        
        // Force replace inputs if values don't match backend
        if (backendStartDate && startInput && startInput.value !== backendStartDate) {
            const attrs = {
                class: startInput.className,
                id: startInput.id,
                name: startInput.name,
                onchange: startInput.getAttribute('onchange'),
                autocomplete: startInput.getAttribute('autocomplete')
            };
            startInput.outerHTML = `<input type="date" class="${attrs.class}" id="${attrs.id}" name="${attrs.name}" value="${backendStartDate}" onchange="${attrs.onchange}" autocomplete="${attrs.autocomplete}">`;
        }
        
        if (backendEndDate && endInput && endInput.value !== backendEndDate) {
            const attrs = {
                class: endInput.className,
                id: endInput.id,
                name: endInput.name,
                onchange: endInput.getAttribute('onchange'),
                autocomplete: endInput.getAttribute('autocomplete')
            };
            endInput.outerHTML = `<input type="date" class="${attrs.class}" id="${attrs.id}" name="${attrs.name}" value="${backendEndDate}" onchange="${attrs.onchange}" autocomplete="${attrs.autocomplete}">`;
        }
        
        // Initialize dates without triggering validation
        initializeDates();
        updateAdultDetails();
        updateChildDetails();
        updateInfantDetails();
        
        // Load existing table data into arrays BEFORE scanning
        loadExistingDataIntoArrays();
        
        // Now scan and expand header dates
        scanAndExpandHeaderDates();
        updateAllServiceDateRanges();
        
        // Clear the initialization flag immediately after scanning
        console.log('Clearing _headerDatesInitializing flag');
        window._headerDatesInitializing = false;
        console.log('Header dates initialization complete, flag is now:', window._headerDatesInitializing);
        
        // Initialize destination tags functionality
        // Use setTimeout to ensure DOM is fully rendered
        setTimeout(() => {
            initDestinationTags();
            
            // Load initial destinations if available
            @if(isset($initialData['destinations_array']) && is_array($initialData['destinations_array']))
                @foreach($initialData['destinations_array'] as $dest)
                    selectedDestinations.push('{{ $dest }}');
                @endforeach
                updateDestinationTags();
                updateHiddenInput();
            @endif
            
            // Filter ports based on initially selected countries
            filterPortsBySelectedCountries();
        }, 100);
        
        // Sync popup dates to header dates when they change
        const checkInDate = document.getElementById('checkInDate');
        const checkOutDate = document.getElementById('checkOutDate');
        
        if (checkInDate) {
            checkInDate.addEventListener('change', function() {
                console.log('Popup Check-In changed to:', this.value);
                const tourStartDate = getHeaderStartInput();
                if (tourStartDate) {
                    tourStartDate.value = this.value;
                    console.log('Header Start Date set to:', tourStartDate.value);
                    updateStartDate(); // Trigger header validation
                }
            });
        }
        
        if (checkOutDate) {
            checkOutDate.addEventListener('change', function() {
                console.log('Popup Check-Out changed to:', this.value);
                const tourEndDate = getHeaderEndInput();
                if (tourEndDate) {
                    tourEndDate.value = this.value;
                    console.log('Header End Date set to:', tourEndDate.value);
                    updateEndDate(); // Trigger header validation
                }
            });
        }
    });
    
    // Dynamic hotel room data from database
    const hotelRoomsData = {
        '1': { // The Singapore Riv
            'Deluxe Room': {
                beds: {
                    'King Bed': { price: 150, maxOccupancy: 2, extraBedPrice: 30 },
                    'Twin Bed': { price: 150, maxOccupancy: 2, extraBedPrice: 30 }
                }
            },
            'Superior Room': {
                beds: {
                    'King Bed': { price: 180, maxOccupancy: 2, extraBedPrice: 35 },
                    'Queen Bed': { price: 175, maxOccupancy: 2, extraBedPrice: 35 },
                    'Two Queen Beds': { price: 200, maxOccupancy: 4, extraBedPrice: 40 }
                }
            }
        },
        '2': { // Marina Bay Sands
            'Premier Room': {
                beds: {
                    'King Bed': { price: 350, maxOccupancy: 2, extraBedPrice: 50 },
                    'Twin Bed': { price: 350, maxOccupancy: 2, extraBedPrice: 50 }
                }
            },
            'Deluxe Room': {
                beds: {
                    'King Bed': { price: 450, maxOccupancy: 2, extraBedPrice: 60 },
                    'Two Double Beds': { price: 450, maxOccupancy: 4, extraBedPrice: 60 }
                }
            }
        },
        '3': { // Raffles Hotel
            'Classic Room': {
                beds: {
                    'King Bed': { price: 500, maxOccupancy: 2, extraBedPrice: 70 }
                }
            },
            'Grand Suite': {
                beds: {
                    'King Bed': { price: 800, maxOccupancy: 3, extraBedPrice: 100 },
                    'Two King Beds': { price: 900, maxOccupancy: 4, extraBedPrice: 120 }
                }
            }
        },
        '4': { // Mandarin Oriental
            'Deluxe Room': {
                beds: {
                    'King Bed': { price: 280, maxOccupancy: 2, extraBedPrice: 45 },
                    'Twin Bed': { price: 280, maxOccupancy: 2, extraBedPrice: 45 }
                }
            },
            'Suite Room': {
                beds: {
                    'King Bed': { price: 420, maxOccupancy: 3, extraBedPrice: 65 },
                    'King + Sofa Bed': { price: 450, maxOccupancy: 4, extraBedPrice: 70 }
                }
            }
        }
    };

    // Open Accommodation Modal
    function openAccommodationModal() {
        selectedHotelsTemp = [];
        editingHotelId = null;
        window.editingAccommodationIndex = null;
        window.isArrivalDepartureOnlyMode = false;
        window.editingArrivalDepartureIndex = null;
        window.editingArrivalDepartureType = null;
        window.isEditingArrivalDeparture = false; // Reset edit flag
        document.getElementById('selectedHotelsList').innerHTML = '';
        document.getElementById('noHotelsMessage').style.display = 'block';
        document.getElementById('saveAccommodationBtnText').textContent = 'Add Accommodation';
        
        // Hide room combinations section initially
        const roomCombinationsSection = document.getElementById('roomCombinationsSection');
        if (roomCombinationsSection) {
            roomCombinationsSection.style.display = 'none';
        }
        
        // Show hotel sections
        document.getElementById('hotelSelectionRow1').style.display = 'flex';
        document.getElementById('selectedHotelsSection').style.display = 'none';
        
        // Hide arrival/departure section (hidden for now)
        const arrivalDepartureSection = document.getElementById('arrivalDepartureSection');
        if (arrivalDepartureSection) {
            arrivalDepartureSection.style.display = 'none';
        }
        
        // Clear transfer checkboxes and reset dropdowns
        document.getElementById('arrivalTransfer').checked = false;
        document.getElementById('departureTransfer').checked = false;
        
        // Hide transfer details sections initially
        const arrivalTransferDetails = document.getElementById('arrivalTransferDetailsSection');
        const departureTransferDetails = document.getElementById('departureTransferDetailsSection');
        if (arrivalTransferDetails) arrivalTransferDetails.style.display = 'none';
        if (departureTransferDetails) departureTransferDetails.style.display = 'none';
        
        // Reset modal title
        document.getElementById('modalTitleIcon').className = 'ri-hotel-line me-2';
        document.getElementById('modalTitleText').textContent = 'Select Hotels';
        document.getElementById('arrivalDepartureSectionTitle').textContent = 'Arrival/Departure Flight Information';
        
        // Reset form
        resetHotelForm();
        
        // Initialize modal dates (set minimum dates and disable past dates)
        initializeModalDates();
        
        // Initialize Select2 for port dropdowns in accommodation modal
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2-port').select2({
                placeholder: 'Search and select port',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#accommodationModal')
            });
        }
        
        const accommodationModal = new bootstrap.Modal(document.getElementById('accommodationModal'));
        accommodationModal.show();
        
        // Clear destination if header has no destinations (override backend pre-selection)
        setTimeout(() => {
            const headerValues = getHeaderValues();
            const hotelDestination = document.getElementById('hotelDestination');
            
            // If header has no destinations, clear the dropdown
            if (headerValues.countries.length === 0 && hotelDestination) {
                console.log('No destinations in header, clearing destination dropdown');
                hotelDestination.value = '';
                // Also clear hotel dropdown
                const hotelSelect = document.getElementById('hotelSelect');
                if (hotelSelect) {
                    hotelSelect.innerHTML = '<option value="">-- Select Hotel --</option>';
                    hotelSelect.disabled = true;
                }
            }
        }, 50);
        
        // Auto-fill adults, children, infants, and country from header AFTER modal is shown
        setTimeout(() => {
            autoFillModalFields('accommodation');
        }, 100);
        
        // Populate existing arrival/departure data if available AFTER modal is shown
        setTimeout(() => {
            // Skip auto-population if we're explicitly adding new arrival/departure OR editing existing one
            if (window.skipArrivalDepartureAutoPopulate || window.isEditingArrivalDeparture) {
                return;
            }
            
            const standaloneArrival = arrivalDepartureList.find(item => item.type === 'Arrival' && item.accommodationIndex === null);
            const standaloneDeparture = arrivalDepartureList.find(item => item.type === 'Departure' && item.accommodationIndex === null);
            
            // If standalone entries exist, populate them
            if (standaloneArrival) {
                // Normalize date to YYYY-MM-DDTHH:mm format for datetime-local input
                const normalizedDateTime = normalizeDateTimeLocal(standaloneArrival.dateTime);
                document.getElementById('arrivalDateTime').value = normalizedDateTime || '';
                $('#arrivalPort').val(standaloneArrival.portId).trigger('change');
                document.getElementById('arrivalFlightNo').value = standaloneArrival.flightNo || '';
                document.getElementById('arrivalTransfer').checked = standaloneArrival.hasTransfer || false;
                document.getElementById('arrivalTransferWay').value = standaloneArrival.transferWay || 'both-way';
                document.getElementById('arrivalTransferType').value = standaloneArrival.transferType || 'S';
            }
            
            if (standaloneDeparture) {
                // Normalize date to YYYY-MM-DDTHH:mm format for datetime-local input
                const normalizedDateTime = normalizeDateTimeLocal(standaloneDeparture.dateTime);
                document.getElementById('departureDateTime').value = normalizedDateTime || '';
                $('#departurePort').val(standaloneDeparture.portId).trigger('change');
                document.getElementById('departureFlightNo').value = standaloneDeparture.flightNo || '';
                document.getElementById('departureTransfer').checked = standaloneDeparture.hasTransfer || false;
                document.getElementById('departureTransferWay').value = standaloneDeparture.transferWay || 'both-way';
                document.getElementById('departureTransferType').value = standaloneDeparture.transferType || 'S';
            }
        }, 200);
        
        // Calculate nights when dates change
        document.getElementById('checkInDate').addEventListener('change', calculateAccommodationNights);
        document.getElementById('checkOutDate').addEventListener('change', calculateAccommodationNights);
    }
    
    // Update check-out minimum date based on check-in date
    function updateCheckOutMinDate() {
        console.log('updateCheckOutMinDate called');
        const checkInInput = document.getElementById('checkInDate');
        const checkOutInput = document.getElementById('checkOutDate');
        const checkInDate = checkInInput.value;
        console.log('Popup Check-In value:', checkInDate);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        // Validate check-in date is not less than today
        if (checkInDate) {
            const selectedDate = new Date(checkInDate);
            selectedDate.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                alert('Check-in date cannot be less than today');
                checkInInput.value = today.toISOString().split('T')[0];
                return;
            }
            
            // Expand header dates if check-in is outside range
            expandHeaderDatesIfNeeded(checkInDate, false);
        }
        
        if (checkInDate) {
            // Set minimum checkout date to check-in + 1 day
            const checkIn = new Date(checkInDate);
            checkIn.setHours(0, 0, 0, 0);
            checkIn.setDate(checkIn.getDate() + 1);
            const minCheckOut = checkIn.toISOString().split('T')[0];
            checkOutInput.setAttribute('min', minCheckOut);
            checkOutInput.removeAttribute('max'); // Allow all future dates
            
            // Auto-set checkout to check-in + 1 or update if invalid
            if (!checkOutInput.value || new Date(checkOutInput.value) < checkIn) {
                checkOutInput.value = minCheckOut;
            }
            
            // Expand header dates if check-out is outside range
            if (checkOutInput.value) {
                expandHeaderDatesIfNeeded(checkOutInput.value, false);
            }
            
            // Calculate nights after updating
            calculateAccommodationNights();
        } else {
            // Reset checkout minimum to today if check-in is cleared
            const todayStr = today.toISOString().split('T')[0];
            checkOutInput.setAttribute('min', todayStr);
            checkOutInput.removeAttribute('max'); // Allow all future dates
            checkOutInput.value = '';
        }
    }
    
    // Initialize modal dates (set minimum dates and disable past dates)
    function initializeModalDates() {
        const checkInInput = document.getElementById('checkInDate');
        const checkOutInput = document.getElementById('checkOutDate');
        const tourStart = getHeaderStartInput();
        const tourEnd = getHeaderEndInput();
        const arrivalDateTime = document.getElementById('arrivalDateTime');
        const departureDateTime = document.getElementById('departureDateTime');
        const localDateTime = document.getElementById('localDateTime');
        
        // Set minimum date to today (disables all past dates)
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const todayStr = today.toISOString().split('T')[0];
        const todayDateTimeStr = today.toISOString().slice(0, 16);
        
        // Set minimum to today, no maximum (allow all future dates)
        checkInInput.setAttribute('min', todayStr);
        checkInInput.removeAttribute('max');
        
        // Populate from header dates if available and not already set
        if (tourStart && tourStart.value && !checkInInput.value) {
            // Set check-in to header start date with 11:00 AM time
            checkInInput.value = tourStart.value + 'T11:00';
            
            // Set check-out to header end date with 10:00 AM time
            if (tourEnd && tourEnd.value && !checkOutInput.value) {
                checkOutInput.value = tourEnd.value + 'T10:00';
            } else if (!checkOutInput.value) {
                // If no end date, set check-out to start date + 1 day with 10:00 AM
                const checkInDate = new Date(tourStart.value);
                checkInDate.setDate(checkInDate.getDate() + 1);
                checkOutInput.value = checkInDate.toISOString().split('T')[0] + 'T10:00';
            }
        } else if (!checkInInput.value && !checkOutInput.value) {
            // If no header dates, set check-in to today 11:00 AM and check-out to tomorrow 10:00 AM
            checkInInput.value = todayStr + 'T11:00';
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            checkOutInput.value = tomorrow.toISOString().split('T')[0] + 'T10:00';
        }
        
        // Calculate nights when dates are set
        if (checkInInput.value && checkOutInput.value) {
            calculateAccommodationNights();
        }
        
        // Update checkout minimum based on check-in
        if (checkInInput.value) {
            const checkIn = new Date(checkInInput.value);
            checkIn.setHours(0, 0, 0, 0);
            checkIn.setDate(checkIn.getDate() + 1);
            const minCheckOut = checkIn.toISOString().split('T')[0];
            checkOutInput.setAttribute('min', minCheckOut);
            checkOutInput.removeAttribute('max');
        } else {
            checkOutInput.setAttribute('min', todayStr);
            checkOutInput.removeAttribute('max');
        }
        
        // Set arrival/departure minimum to today (allow all future dates)
        arrivalDateTime.setAttribute('min', todayDateTimeStr);
        arrivalDateTime.removeAttribute('max');
        
        departureDateTime.setAttribute('min', todayDateTimeStr);
        departureDateTime.removeAttribute('max');
        
        // Set local transfer date constraints based on header dates
        if (localDateTime) {
            const headerStartDate = tourStart?.value || todayStr;
            const headerEndDate = tourEnd?.value || '';
            
            localDateTime.setAttribute('min', headerStartDate);
            if (headerEndDate) {
                localDateTime.setAttribute('max', headerEndDate);
            } else {
                localDateTime.removeAttribute('max');
            }
        }
        
        // Populate arrival/departure from header dates if available and not already set
        // Skip if we're editing an existing arrival/departure entry
        if (!window.isEditingArrivalDeparture) {
            if (tourStart && tourStart.value && !arrivalDateTime.value) {
                arrivalDateTime.value = tourStart.value + 'T00:00';
            }
            
            if (tourEnd && tourEnd.value && !departureDateTime.value) {
                departureDateTime.value = tourEnd.value + 'T23:59';
            }
        }
        
        // Calculate nights
        calculateAccommodationNights();
    }

    // Load agents by agency via AJAX
    function loadAgentsByAgency() {
        const agencyId = document.getElementById('agencySelect').value;
        const agentSelect = document.getElementById('agentSelect');
        
        // Reset agent select
        agentSelect.innerHTML = '<option value="">-- Loading agents... --</option>';
        agentSelect.disabled = true;
        
        if (!agencyId) {
            agentSelect.innerHTML = '<option value="">-- Select Agency First --</option>';
            return;
        }
        
        // Fetch agents via AJAX
        fetch('{{ route("enquiry-form-pro.get-agents") }}?agency_id=' + encodeURIComponent(agencyId), {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            agentSelect.innerHTML = '<option value="">-- Select Agent --</option>';
            
            if (!data.success || !data.agents || data.agents.length === 0) {
                agentSelect.innerHTML = '<option value="">No agents available for this agency</option>';
                return;
            }
            
            // Get pre-selected agent ID if available
            @if(isset($initialData) && isset($initialData['agent_id']))
            const preSelectedAgentId = {{ $initialData['agent_id'] }};
            @else
            const preSelectedAgentId = null;
            @endif
            
            // Populate agent dropdown
            data.agents.forEach(agent => {
                const option = document.createElement('option');
                option.value = agent.agent_id;
                option.textContent = agent.name;
                if (preSelectedAgentId && agent.agent_id == preSelectedAgentId) {
                    option.selected = true;
                }
                agentSelect.appendChild(option);
            });
            
            agentSelect.disabled = false;
        })
        .catch(error => {
            console.error('Error loading agents:', error);
            agentSelect.innerHTML = '<option value="">-- Error loading agents --</option>';
            alert('Error loading agents. Please try again.');
        });
    }

    // Load hotels by destination via AJAX
    function loadHotelsByDestination() {
        const destination = document.getElementById('hotelDestination').value;
        const hotelSelect = document.getElementById('hotelSelect');
        
        // Reset hotel select
        hotelSelect.innerHTML = '<option value="">-- Loading hotels... --</option>';
        hotelSelect.disabled = true;
        
        // Hide room combinations section
        const roomCombinationsSection = document.getElementById('roomCombinationsSection');
        if (roomCombinationsSection) {
            roomCombinationsSection.style.display = 'none';
        }
        
        // Clear room combinations table
        const roomCombinationsTableBody = document.getElementById('roomCombinationsTableBody');
        if (roomCombinationsTableBody) {
            roomCombinationsTableBody.innerHTML = '';
        }
        
        currentHotelData = null;
        window.currentRoomCombinations = [];
        
        if (!destination) {
            hotelSelect.innerHTML = '<option value="">-- Select Hotel --</option>';
            return;
        }
        
        // Fetch hotels via AJAX
        console.log('Loading hotels for destination:', destination);
        fetch('{{ route("enquiry-form-pro.get-hotels") }}?destination=' + encodeURIComponent(destination), {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            console.log('Hotels API response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Hotels API response data:', data);
            console.log('Hotels count:', data.hotels ? data.hotels.length : 0);
            console.log('DMC ID:', data.dmc_id);
            
            hotelSelect.innerHTML = '<option value="">-- Select Hotel --</option>';
            
            if (!data.success || !data.hotels || data.hotels.length === 0) {
                console.warn('No hotels found for destination:', destination, 'DMC ID:', data.dmc_id);
                console.error('IMPORTANT: No hotels assigned to DMC ID ' + data.dmc_id + ' for ' + destination);
                console.error('Please go to Hotel Management and assign hotels to your DMC!');
                alert('No hotels available for ' + destination + ' (DMC ID: ' + data.dmc_id + ')\n\nPlease assign hotels to your DMC in Hotel Management.');
                hotelSelect.disabled = false;
                return;
            }
            
            // Populate hotel dropdown
            data.hotels.forEach(hotel => {
                console.log('Adding hotel:', hotel.name, 'ID:', hotel.id, 'Rooms:', hotel.rooms ? hotel.rooms.length : 0);
                const option = document.createElement('option');
                option.value = hotel.id;
                option.setAttribute('data-hotel-name', hotel.name);
                option.setAttribute('data-hotel-data', JSON.stringify({
                    id: hotel.id,
                    name: hotel.name,
                    rooms: hotel.rooms
                }));
                option.textContent = hotel.name;
                hotelSelect.appendChild(option);
            });
            
            hotelSelect.disabled = false;
            console.log('Hotels loaded successfully. Total:', data.hotels.length);
        })
        .catch(error => {
            console.error('Error loading hotels:', error);
            hotelSelect.innerHTML = '<option value="">-- Error loading hotels --</option>';
            alert('Error loading hotels. Please try again.');
        });
    }

    // Reset hotel form
    function resetHotelForm() {
        // Reset hotel selection
        const hotelSelect = document.getElementById('hotelSelect');
        if (hotelSelect) hotelSelect.value = '';
        
        // Hide room combinations section
        const roomCombinationsSection = document.getElementById('roomCombinationsSection');
        if (roomCombinationsSection) {
            roomCombinationsSection.style.display = 'none';
        }
        
        // Clear room combinations table
        const roomCombinationsTableBody = document.getElementById('roomCombinationsTableBody');
        if (roomCombinationsTableBody) {
            roomCombinationsTableBody.innerHTML = '';
        }
        
        // Uncheck select all checkbox
        const selectAllCheckbox = document.getElementById('selectAllRoomCombinations');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
        }
        
        // Clear dates - they will be populated by initializeModalDates()
        const checkInDate = document.getElementById('checkInDate');
        const checkOutDate = document.getElementById('checkOutDate');
        const numNights = document.getElementById('numNights');
        if (checkInDate) checkInDate.value = '';
        if (checkOutDate) checkOutDate.value = '';
        if (numNights) numNights.value = '';
        
        // Hide and reset hotel transfer section
        const hotelTransferSection = document.getElementById('hotelTransferSection');
        if (hotelTransferSection) {
            hotelTransferSection.style.display = 'none';
        }
        
        const hotelTransferCheckbox = document.getElementById('hotelTransferCheckbox');
        if (hotelTransferCheckbox) {
            hotelTransferCheckbox.checked = false;
        }
        
        const hotelTransferDetailsSection = document.getElementById('hotelTransferDetailsSection');
        if (hotelTransferDetailsSection) {
            hotelTransferDetailsSection.style.display = 'none';
        }
        
        // Reset transfer fields
        const hotelTransferDestination = document.getElementById('hotelTransferDestination');
        if (hotelTransferDestination) hotelTransferDestination.value = '';
        
        editingHotelId = null;
        currentHotelData = null;
        window.currentRoomCombinations = [];
    }

    // Load room types when hotel is selected - now shows all combinations
    function loadRoomTypes() {
        const hotelSelect = document.getElementById('hotelSelect');
        const hotelId = hotelSelect.value;
        const roomCombinationsSection = document.getElementById('roomCombinationsSection');
        const roomCombinationsTableBody = document.getElementById('roomCombinationsTableBody');
        
        // Hide combinations section if no hotel selected
        if (!hotelId) {
            roomCombinationsSection.style.display = 'none';
            roomCombinationsTableBody.innerHTML = '';
            currentHotelData = null;
            return;
        }
        
        // Get hotel data from selected option
        const selectedOption = hotelSelect.options[hotelSelect.selectedIndex];
        const hotelDataStr = selectedOption.getAttribute('data-hotel-data');
        
        if (!hotelDataStr) {
            roomCombinationsSection.style.display = 'none';
            return;
        }
        
        try {
            currentHotelData = JSON.parse(hotelDataStr);
            
            if (!currentHotelData.rooms || currentHotelData.rooms.length === 0) {
                alert('No rooms available for this hotel');
                roomCombinationsSection.style.display = 'none';
                return;
            }
            
            // Generate all permutation combinations
            const combinations = generateRoomCombinations(currentHotelData.rooms);
            
            // Display combinations in table
            displayRoomCombinations(combinations);
            
            // Show the combinations section
            roomCombinationsSection.style.display = 'block';
            
            // Show the hotel transfer section
            const hotelTransferSection = document.getElementById('hotelTransferSection');
            if (hotelTransferSection) {
                hotelTransferSection.style.display = 'block';
            }
        } catch (e) {
            console.error('Error parsing hotel data:', e);
            roomCombinationsSection.style.display = 'none';
            
            // Hide hotel transfer section on error
            const hotelTransferSection = document.getElementById('hotelTransferSection');
            if (hotelTransferSection) {
                hotelTransferSection.style.display = 'none';
            }
        }
    }
    
    // Generate all permutation combinations of Room Type, Bed Type, and Meal Plan
    function generateRoomCombinations(rooms) {
        const combinations = [];
        const mealPlans = [
            { value: 'CP', label: 'CP (Breakfast)' },
            { value: 'MAP', label: 'MAP (Breakfast + Lunch/Dinner)' },
            { value: 'AP', label: 'AP (All Meals)' },
            { value: 'EP', label: 'EP (No Meals)' }
        ];
        
        // Group rooms by room_type to get unique room types
        const roomTypeMap = {};
        rooms.forEach(room => {
            if (!roomTypeMap[room.room_type]) {
                roomTypeMap[room.room_type] = [];
            }
            roomTypeMap[room.room_type].push(room);
        });
        
        // Generate combinations for each room type
        Object.keys(roomTypeMap).forEach(roomType => {
            const roomsOfType = roomTypeMap[roomType];
            
            // For each room of this type, get its bed types
            roomsOfType.forEach(room => {
                if (room.bed_types && room.bed_types.length > 0) {
                    room.bed_types.forEach(bedType => {
                        // For each meal plan, create a combination
                        mealPlans.forEach(mealPlan => {
                            combinations.push({
                                id: generateId('combo'),
                                roomType: room.room_type,
                                roomId: bedType.bed_type_id,
                                bedType: bedType.bed_type,
                                mealPlan: mealPlan.value,
                                mealPlanLabel: mealPlan.label,
                                maxOccupancy: bedType.max_occupancy || room.max_occupancy || 2,
                                price: bedType.price || room.price || 0,
                                extraBedPrice: bedType.extra_bed_price || room.extra_bed_price || 0
                            });
                        });
                    });
                }
            });
        });
        
        return combinations;
    }
    
    // Display room combinations in table
    function displayRoomCombinations(combinations) {
        const tbody = document.getElementById('roomCombinationsTableBody');
        tbody.innerHTML = '';
        
        if (combinations.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">No room combinations available</td></tr>';
            return;
        }
        
        // Get header values for validation
        const headerValues = getHeaderValues();
        
        combinations.forEach(combo => {
            const row = document.createElement('tr');
            row.className = 'room-combination-row';
            row.setAttribute('data-combo-id', combo.id);
            
            row.innerHTML = `
                <td style="padding: 2px 8px; text-align: center;">
                    <input type="checkbox" class="room-combination-checkbox" data-combo-id="${combo.id}">
                </td>
                <td style="padding: 2px 8px;">${combo.roomType}</td>
                <td style="padding: 2px 8px;">${combo.bedType}</td>
                <td style="padding: 2px 8px;">${combo.mealPlanLabel}</td>
                <td style="padding: 2px 8px; text-align: center;">${combo.maxOccupancy}</td>
                <td style="padding: 2px 8px;">
                    <input type="number" class="form-control form-control-sm combo-rooms" 
                           data-combo-id="${combo.id}" value="1" min="1" 
                           style="font-size: 10px; padding: 2px 4px; text-align: center;">
                </td>
                <td style="padding: 2px 8px;">
                    <input type="number" class="form-control form-control-sm combo-adults" 
                           data-combo-id="${combo.id}" value="${Math.min(2, headerValues.adults)}" min="1" max="${headerValues.adults}"
                           style="font-size: 10px; padding: 2px 4px; text-align: center;">
                </td>
                <td style="padding: 2px 8px;">
                    <input type="number" class="form-control form-control-sm combo-extra-bed" 
                           data-combo-id="${combo.id}" value="0" min="0" max="${headerValues.children}"
                           style="font-size: 10px; padding: 2px 4px; text-align: center;">
                </td>
                <td style="padding: 2px 8px;">
                    <input type="number" class="form-control form-control-sm combo-child-without" 
                           data-combo-id="${combo.id}" value="0" min="0" max="${headerValues.children}"
                           style="font-size: 10px; padding: 2px 4px; text-align: center;">
                </td>
                <td style="padding: 2px 8px;">${combo.price}</td>
            `;
            
            tbody.appendChild(row);
        });
        
        // Add validation event listeners
        tbody.querySelectorAll('.combo-adults').forEach(input => {
            input.addEventListener('input', function() {
                const max = parseInt(this.getAttribute('max'));
                if (parseInt(this.value) > max) {
                    this.value = max;
                    alert(`Adults cannot exceed ${max} (header value)`);
                }
            });
        });
        
        tbody.querySelectorAll('.combo-extra-bed, .combo-child-without').forEach(input => {
            input.addEventListener('input', function() {
                const max = parseInt(this.getAttribute('max'));
                if (parseInt(this.value) > max) {
                    this.value = max;
                    alert(`Children count cannot exceed ${max} (header value)`);
                }
            });
        });
        
        // Store combinations for later use
        window.currentRoomCombinations = combinations;
    }
    
    // Toggle select all room combinations
    function toggleSelectAllRoomCombinations() {
        const selectAll = document.getElementById('selectAllRoomCombinations');
        const checkboxes = document.querySelectorAll('.room-combination-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    }
    
    // Get selected room combinations with their input values
    function getSelectedRoomCombinations() {
        const selectedCombos = [];
        const checkboxes = document.querySelectorAll('.room-combination-checkbox:checked');
        
        checkboxes.forEach(checkbox => {
            const comboId = checkbox.getAttribute('data-combo-id');
            const combo = window.currentRoomCombinations.find(c => c.id === comboId);
            
            if (combo) {
                // Get the input values for this combination
                const roomsInput = document.querySelector(`.combo-rooms[data-combo-id="${comboId}"]`);
                const adultsInput = document.querySelector(`.combo-adults[data-combo-id="${comboId}"]`);
                const extraBedInput = document.querySelector(`.combo-extra-bed[data-combo-id="${comboId}"]`);
                const childWithoutInput = document.querySelector(`.combo-child-without[data-combo-id="${comboId}"]`);
                
                selectedCombos.push({
                    ...combo,
                    rooms: parseInt(roomsInput?.value || 1),
                    adultsPerRoom: parseInt(adultsInput?.value || 2),
                    extraBed: parseInt(extraBedInput?.value || 0),
                    childWithoutBed: parseInt(childWithoutInput?.value || 0)
                });
            }
        });
        
        return selectedCombos;
    }
    
    // Convert selected combinations to hotel entries format
    function convertCombinationsToHotels(combinations) {
        const hotelSelect = document.getElementById('hotelSelect');
        const hotelId = hotelSelect.value;
        const hotelName = hotelSelect.options[hotelSelect.selectedIndex].text;
        const destination = document.getElementById('hotelDestination').value;
        const checkIn = document.getElementById('checkInDate').value;
        const checkOut = document.getElementById('checkOutDate').value;
        const nights = document.getElementById('numNights').value;
        
        return combinations.map(combo => ({
            id: generateId('hotel'),
            hotelId: hotelId,
            hotelName: hotelName,
            destination: destination,
            roomId: combo.roomId,
            roomType: combo.roomType,
            bedType: combo.bedType,
            maxOccupancy: combo.maxOccupancy,
            checkIn: checkIn,
            checkOut: checkOut,
            nights: nights,
            rooms: combo.rooms,
            adultsPerRoom: combo.adultsPerRoom,
            extraBed: combo.extraBed,
            childWithoutBed: combo.childWithoutBed,
            mealPlan: combo.mealPlan,
            supplement: '',
            roomPrice: combo.price
        }));
    }

    // Load bed types when room type is selected (deprecated - now using combination table)
    function loadBedTypes() {
        // This function is kept for backward compatibility but is no longer used
        // Room combinations are now displayed in a table when hotel is selected
        return;
        const roomType = document.getElementById('roomType').value;
        const bedTypeSelect = document.getElementById('bedType');
        const maxOccupancyLabel = document.getElementById('maxOccupancyLabel');
        
        bedTypeSelect.innerHTML = '<option value="">-- Select Bed --</option>';
        bedTypeSelect.disabled = true;
        maxOccupancyLabel.textContent = '';
        document.getElementById('bedPriceLabel').textContent = '';
        document.getElementById('roomPrice').value = '0';
        document.getElementById('addHotelBtn').disabled = true;
        
        if (!currentHotelData || !roomType) {
            return;
        }
        
        // Filter rooms by room type
        const roomsOfType = currentHotelData.rooms.filter(room => room.room_type === roomType);
        
        if (roomsOfType.length === 0) {
            return;
        }
        
        // Get the first room of this type (they should all have same bed_types)
        const room = roomsOfType[0];
        
        // Check if bed_types array exists
        if (!room.bed_types || room.bed_types.length === 0) {
            console.error('No bed types available for this room');
            return;
        }
        
        // Populate bed types from bed_types array
        room.bed_types.forEach(bedType => {
            const option = document.createElement('option');
            option.value = bedType.bed_type_id;
            
            // Store complete bed and room data
            const combinedData = {
                ...room,
                bed_type: bedType.bed_type,
                max_occupancy: bedType.max_occupancy,
                extra_bed_price: bedType.extra_bed_price,
                has_extra_bed: bedType.has_extra_bed
            };
            
            option.setAttribute('data-room-data', JSON.stringify(combinedData));
            option.textContent = `${bedType.bed_type} (Max: ${bedType.max_occupancy})`;
            bedTypeSelect.appendChild(option);
        });
        
        bedTypeSelect.disabled = false;
    }

    // Update pricing when bed type is selected
    function updatePricing() {
        // This function is kept for backward compatibility but is no longer used
        // Pricing is now displayed in the room combinations table
        return;
    }
    
    // Validate total pax and calculate extra beds needed
    function validatePaxAndCalculateExtraBeds(roomData) {
        const totalPax = getTotalPax();
        const numRooms = parseInt(document.getElementById('numRooms').value) || 1;
        const adultsPerRoom = parseInt(document.getElementById('adultsPerRoom').value) || 2;
        const maxOccupancy = roomData.max_occupancy;
        const extraBedInput = document.getElementById('extraBed');
        
        // Calculate total occupancy
        const baseOccupancy = numRooms * adultsPerRoom;
        
        // Check if we need extra beds
        if (baseOccupancy < totalPax) {
            const paxDeficit = totalPax - baseOccupancy;
            
            // Check if max occupancy allows extra beds
            const maxTotalOccupancy = numRooms * maxOccupancy;
            
            if (maxTotalOccupancy >= totalPax) {
                // Auto-fill extra beds
                extraBedInput.value = paxDeficit;
            } else {
                alert(`Total pax (${totalPax}) exceeds maximum room capacity (${maxTotalOccupancy}). Please add more rooms or reduce pax.`);
            }
        } else {
            extraBedInput.value = 0;
        }
    }
    
    // Recalculate when rooms or adults change
    function recalculatePaxValidation() {
        const bedTypeSelect = document.getElementById('bedType');
        const roomId = bedTypeSelect.value;
        
        if (!roomId) return;
        
        const selectedOption = bedTypeSelect.options[bedTypeSelect.selectedIndex];
        const roomDataStr = selectedOption.getAttribute('data-room-data');
        
        if (roomDataStr) {
            try {
                const roomData = JSON.parse(roomDataStr);
                validatePaxAndCalculateExtraBeds(roomData);
            } catch (e) {
                console.error('Error:', e);
            }
        }
    }

    // Calculate number of nights
    function calculateAccommodationNights() {
        const checkIn = new Date(document.getElementById('checkInDate').value);
        const checkOut = new Date(document.getElementById('checkOutDate').value);
        
        if (checkIn && checkOut && checkOut > checkIn) {
            const diffTime = Math.abs(checkOut - checkIn);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            document.getElementById('numNights').value = diffDays;
        }
    }

    // Add or update hotel in temporary list (DEPRECATED - now using checkbox selection)
    function addHotelToList() {
        // This function is deprecated - the new flow uses checkbox selection in the combinations table
        console.warn('addHotelToList is deprecated. Use checkbox selection instead.');
        alert('Please select room combinations using the checkboxes in the table below.');
        return;

        if (!checkIn || !checkOut) {
            alert('Please select check-in and check-out dates');
            return;
        }
        
        // Get room data
        const selectedOption = bedTypeSelect.options[bedTypeSelect.selectedIndex];
        const roomDataStr = selectedOption.getAttribute('data-room-data');
        const roomData = JSON.parse(roomDataStr);
        const bedType = roomData.bed_type;
        const maxOccupancy = roomData.max_occupancy;
        
        // Final validation
        const totalPax = getTotalPax();
        const maxTotalOccupancy = parseInt(rooms) * maxOccupancy;
        
        if (totalPax > maxTotalOccupancy) {
            alert(`Total pax (${totalPax}) exceeds maximum room capacity (${maxTotalOccupancy}). Please adjust rooms or pax.`);
            return;
        }

        if (editingHotelId !== null) {
            // Update existing hotel
            const index = selectedHotelsTemp.findIndex(h => h.id === editingHotelId);
            if (index !== -1) {
                selectedHotelsTemp[index] = {
                    ...selectedHotelsTemp[index],
                    hotelId: hotelId,
                    hotelName: hotelName,
                    destination: destination,
                    roomId: roomId,
                    roomType: roomType,
                    bedType: bedType,
                    maxOccupancy: maxOccupancy,
                    checkIn: checkIn,
                    checkOut: checkOut,
                    nights: nights,
                    rooms: rooms,
                    adultsPerRoom: adultsPerRoom,
                    extraBed: extraBed,
                    childWithoutBed: childWithoutBed,
                    mealPlan: mealPlan,
                    supplement: '',
                    roomPrice: roomPrice
                };
            }
        } else {
            // Add new hotel
            const hotel = {
                id: generateId('hotel'), // Temporary ID
                hotelId: hotelId,
                hotelName: hotelName,
                destination: destination,
                roomId: roomId,
                roomType: roomType,
                bedType: bedType,
                maxOccupancy: maxOccupancy,
                checkIn: checkIn,
                checkOut: checkOut,
                nights: nights,
                rooms: rooms,
                adultsPerRoom: adultsPerRoom,
                extraBed: extraBed,
                childWithoutBed: childWithoutBed,
                mealPlan: mealPlan,
                supplement: '',
                roomPrice: roomPrice
            };
            selectedHotelsTemp.push(hotel);
        }

        updateSelectedHotelsList();
        resetHotelForm();
    }

    // Update the selected hotels list in modal
    function updateSelectedHotelsList() {
        const tbody = document.getElementById('selectedHotelsList');
        const noMessage = document.getElementById('noHotelsMessage');
        
        if (selectedHotelsTemp.length === 0) {
            tbody.innerHTML = '';
            noMessage.style.display = 'block';
            return;
        }

        noMessage.style.display = 'none';
        
        // Group hotels by hotel name, room type, bed type, and dates
        const groupedHotels = {};
        selectedHotelsTemp.forEach(hotel => {
            const key = `${hotel.hotelName}|${hotel.roomType}|${hotel.bedType}|${hotel.checkIn}|${hotel.checkOut}`;
            if (!groupedHotels[key]) {
                groupedHotels[key] = {
                    ...hotel,
                    ids: [hotel.id],
                    totalRooms: parseInt(hotel.rooms)
                };
            } else {
                groupedHotels[key].ids.push(hotel.id);
                groupedHotels[key].totalRooms += parseInt(hotel.rooms);
            }
        });
        
        // Convert to array and limit to 4 for display
        const displayHotels = Object.values(groupedHotels).slice(0, 4);
        const hasMore = Object.keys(groupedHotels).length > 4;
        
        tbody.innerHTML = displayHotels.map(hotel => `
            <tr>
                <td>
                    ${hotel.hotelName}<br>
                    <small class="text-muted">${hotel.roomType} | ${hotel.bedType}</small>
                </td>
                <td style="white-space: nowrap; font-size: 9px;">
                    ${formatDate(hotel.checkIn)} - ${formatDate(hotel.checkOut)}<br>
                    <small class="text-muted">${hotel.nights} nights</small>
                </td>
                <td style="text-align: center;">${hotel.totalRooms}</td>
                <td>
                    ${hotel.ids.map(id => `
                        <button type="button" class="btn btn-xs btn-warning me-1" onclick="editHotelFromTempList(${id})" title="Edit">
                            <i class="ri-edit-line"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-danger" onclick="removeFromTempList(${id})" title="Delete">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    `).join('<br>')}
                </td>
            </tr>
        `).join('') + (hasMore ? `<tr><td colspan="4" class="text-center text-muted" style="font-size: 9px;">... ${Object.keys(groupedHotels).length - 4} more hotel(s)</td></tr>` : '');
    }

    // Format date for display
    function formatDate(dateString) {
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = date.toLocaleString('default', { month: 'short' });
        const year = String(date.getFullYear()).slice(-2);
        return `${day} ${month} '${year}`;
    }

    // Edit hotel from temporary list
    function editHotelFromTempList(id) {
        const hotel = selectedHotelsTemp.find(h => h.id === id);
        if (!hotel) return;

        // Populate form with hotel data
        document.getElementById('hotelDestination').value = hotel.destination;
        document.getElementById('hotelSelect').value = hotel.hotelId;
        
        // Load room types then set values
        loadRoomTypes();
        setTimeout(() => {
            document.getElementById('roomType').value = hotel.roomType;
            loadBedTypes();
            setTimeout(() => {
                document.getElementById('bedType').value = hotel.bedType;
                updatePricing();
            }, 100);
        }, 100);
        
        document.getElementById('checkInDate').value = hotel.checkIn;
        document.getElementById('checkOutDate').value = hotel.checkOut;
        document.getElementById('numNights').value = hotel.nights;
        document.getElementById('numRooms').value = hotel.rooms;
        document.getElementById('adultsPerRoom').value = hotel.adultsPerRoom;
        document.getElementById('extraBed').value = hotel.extraBed;
        document.getElementById('childWithoutBed').value = hotel.childWithoutBed;
        // document.getElementById('mealPlan').value = hotel.mealPlan;

        // Set editing mode
        editingHotelId = id;
        document.getElementById('addButtonText').textContent = 'Update';
    }

    // Remove hotel from temporary list
    function removeFromTempList(id) {
        selectedHotelsTemp = selectedHotelsTemp.filter(hotel => hotel.id !== id);
        updateSelectedHotelsList();
    }

    // Save arrival/departure only (without accommodation)
    function saveArrivalDepartureOnly() {
        console.log('========================================');
        console.log('saveArrivalDepartureOnly() called');
        console.log('========================================');
        
        const arrivalDateTime = document.getElementById('arrivalDateTime').value;
        const arrivalPortSelect = document.getElementById('arrivalPort');
        const arrivalPortId = arrivalPortSelect.value;
        const arrivalPortName = arrivalPortSelect.selectedOptions[0]?.text || '';
        const arrivalFlightNo = document.getElementById('arrivalFlightNo').value;
        const departureDateTime = document.getElementById('departureDateTime').value;
        const departurePortSelect = document.getElementById('departurePort');
        const departurePortId = departurePortSelect.value;
        const departurePortName = departurePortSelect.selectedOptions[0]?.text || '';
        const departureFlightNo = document.getElementById('departureFlightNo').value;

        console.log('Arrival DateTime:', arrivalDateTime);
        console.log('Arrival Port ID:', arrivalPortId);
        console.log('Arrival Port Name:', arrivalPortName);
        console.log('Arrival Flight No:', arrivalFlightNo);
        console.log('Departure DateTime:', departureDateTime);
        console.log('Departure Port ID:', departurePortId);
        console.log('Departure Port Name:', departurePortName);
        console.log('Departure Flight No:', departureFlightNo);

        // Get pax numbers from header
        const adults = parseInt(document.querySelector('.customer-details input[type="number"][value="2"]')?.value || 2);
        const child = parseInt(document.querySelector('.customer-details input[type="number"][value="1"]')?.value || 1);
        const infant = parseInt(document.querySelector('.customer-details input[type="number"][value="0"]')?.value || 0);

        const isEditing = window.editingArrivalDepartureIndex !== undefined && window.editingArrivalDepartureIndex !== null;

        // Check if editing existing entry
        if (isEditing) {
            const index = window.editingArrivalDepartureIndex;
            const item = arrivalDepartureList[index];
            
            if (item.type === 'Arrival' && arrivalDateTime && arrivalPortId) {
                const arrivalTransfer = document.getElementById('arrivalTransfer')?.checked || false;
                const arrivalTransferWay = document.getElementById('arrivalTransferWay')?.value || 'both-way';
                const arrivalTransferType = document.getElementById('arrivalTransferType')?.value || 'S';
                
                // Get both vehicle_id (value) and vehicle type name (data attribute)
                const arrivalVehicleSelect = document.getElementById('arrivalVehicleType');
                const arrivalVehicleId = arrivalVehicleSelect?.value || '';
                const arrivalVehicleType = arrivalVehicleSelect?.selectedOptions[0]?.getAttribute('data-type') || '';
                
                const arrivalAdults = parseInt(document.getElementById('arrivalAdults')?.value || adults);
                const arrivalChild = parseInt(document.getElementById('arrivalChild')?.value || child);
                const arrivalInfant = parseInt(document.getElementById('arrivalInfant')?.value || infant);
                
                const arrivalDestinationSelect = document.getElementById('arrivalDestination');
                const arrivalDestinationId = arrivalDestinationSelect?.value || '';
                const arrivalDestinationName = arrivalDestinationSelect?.selectedOptions[0]?.getAttribute('data-name') || '';
                
                arrivalDepartureList[index] = {
                    ...item,
                    dateTime: arrivalDateTime,
                    portId: arrivalPortId,
                    portName: arrivalPortName,
                    flightNo: arrivalFlightNo || '-',
                    adultsQty: arrivalAdults,
                    childQty: arrivalChild,
                    infantQty: arrivalInfant,
                    hasTransfer: arrivalTransfer,
                    transferWay: arrivalTransferWay,
                    transferType: arrivalTransferType,
                    vehicleId: arrivalVehicleId,
                    vehicleType: arrivalVehicleType,
                    transferDestinationId: arrivalDestinationId,
                    transferDestinationName: arrivalDestinationName
                };
                
                // Handle transfer: update if exists and checked, remove if unchecked
                if (item.transferId) {
                    const transferIndex = transferList.findIndex(t => t.id === item.transferId);
                    if (transferIndex !== -1) {
                        if (arrivalTransfer) {
                            // Update existing transfer
                            transferList[transferIndex] = {
                                ...transferList[transferIndex],
                                dateTime: arrivalDateTime,
                                portName: arrivalPortName,
                                destination: arrivalDestinationName ? `Arrival: ${arrivalPortName} → ${arrivalDestinationName}` : `Arrival: ${arrivalPortName}`,
                                vehicleType: arrivalVehicleType,
                                type: arrivalTransferType,
                                way: arrivalTransferWay,
                                adults: arrivalAdults,
                                child: arrivalChild
                            };
                        } else {
                            // Remove transfer if unchecked
                            transferList.splice(transferIndex, 1);
                            arrivalDepartureList[index].transferId = null;
                        }
                    }
                } else if (arrivalTransfer) {
                    // Create new transfer if checked and doesn't exist
                    const transferId = generateId('transfer');
                    transferList.push({
                        id: transferId,
                        transportMode: 'local',
                        isStandalone: false,
                        sourceType: 'arrival',
                        sourceId: item.id,
                        dateTime: arrivalDateTime,
                        portName: arrivalPortName,
                        destination: arrivalDestinationName ? `Arrival: ${arrivalPortName} → ${arrivalDestinationName}` : `Arrival: ${arrivalPortName}`,
                        vehicleType: arrivalVehicleType,
                        type: arrivalTransferType,
                        way: arrivalTransferWay,
                        hasTransfer: true,
                        adults: arrivalAdults,
                        child: arrivalChild,
                        cost: 0,
                        sell: 0,
                        taxIncluded: false
                    });
                    arrivalDepartureList[index].transferId = transferId;
                }
            } else if (item.type === 'Departure' && departureDateTime && departurePortId) {
                const departureTransfer = document.getElementById('departureTransfer')?.checked || false;
                const departureTransferWay = document.getElementById('departureTransferWay')?.value || 'both-way';
                const departureTransferType = document.getElementById('departureTransferType')?.value || 'S';
                
                // Get both vehicle_id (value) and vehicle type name (data attribute)
                const departureVehicleSelect = document.getElementById('departureVehicleType');
                const departureVehicleId = departureVehicleSelect?.value || '';
                const departureVehicleType = departureVehicleSelect?.selectedOptions[0]?.getAttribute('data-type') || '';
                
                const departureAdults = parseInt(document.getElementById('departureAdults')?.value || adults);
                const departureChild = parseInt(document.getElementById('departureChild')?.value || child);
                const departureInfant = parseInt(document.getElementById('departureInfant')?.value || infant);
                
                const departureDestinationSelect = document.getElementById('departureDestination');
                const departureDestinationId = departureDestinationSelect?.value || '';
                const departureDestinationName = departureDestinationSelect?.selectedOptions[0]?.getAttribute('data-name') || '';
                
                arrivalDepartureList[index] = {
                    ...item,
                    dateTime: departureDateTime,
                    portId: departurePortId,
                    portName: departurePortName,
                    flightNo: departureFlightNo || '-',
                    adultsQty: departureAdults,
                    childQty: departureChild,
                    infantQty: departureInfant,
                    hasTransfer: departureTransfer,
                    transferWay: departureTransferWay,
                    transferType: departureTransferType,
                    vehicleId: departureVehicleId,
                    vehicleType: departureVehicleType,
                    transferDestinationId: departureDestinationId,
                    transferDestinationName: departureDestinationName
                };
                
                // Handle transfer: update if exists and checked, remove if unchecked
                if (item.transferId) {
                    const transferIndex = transferList.findIndex(t => t.id === item.transferId);
                    if (transferIndex !== -1) {
                        if (departureTransfer) {
                            // Update existing transfer
                            transferList[transferIndex] = {
                                ...transferList[transferIndex],
                                dateTime: departureDateTime,
                                portName: departurePortName,
                                destination: departureDestinationName ? `Departure: ${departureDestinationName} → ${departurePortName}` : `Departure: ${departurePortName}`,
                                vehicleType: departureVehicleType,
                                type: departureTransferType,
                                way: departureTransferWay,
                                adults: departureAdults,
                                child: departureChild
                            };
                        } else {
                            // Remove transfer if unchecked
                            transferList.splice(transferIndex, 1);
                            arrivalDepartureList[index].transferId = null;
                        }
                    }
                } else if (departureTransfer) {
                    // Create new transfer if checked and doesn't exist
                    const transferId = generateId('transfer');
                    transferList.push({
                        id: transferId,
                        transportMode: 'local',
                        isStandalone: false,
                        sourceType: 'departure',
                        sourceId: item.id,
                        dateTime: departureDateTime,
                        portName: departurePortName,
                        destination: departureDestinationName ? `Departure: ${departureDestinationName} → ${departurePortName}` : `Departure: ${departurePortName}`,
                        vehicleType: departureVehicleType,
                        type: departureTransferType,
                        way: departureTransferWay,
                        hasTransfer: true,
                        adults: departureAdults,
                        child: departureChild,
                        cost: 0,
                        sell: 0,
                        taxIncluded: false
                    });
                    arrivalDepartureList[index].transferId = transferId;
                }
            }
            
            window.editingArrivalDepartureIndex = null;
        } else {
            // Validate that at least one arrival or departure is filled
            const hasArrival = arrivalDateTime && arrivalPortId;
            const hasDeparture = departureDateTime && departurePortId;
            
            // Check for partial data entry - ONLY show error if user filled date but forgot port
            // If port is not selected, it simply means they don't want that section
            if (arrivalDateTime && !arrivalPortId) {
                alert('⚠️ Arrival Port Not Selected\n\nYou filled in the Arrival Date/Time but did not select a Port.\n\nPlease select an Arrival Port to continue.');
                return;
            }
            
            if (departureDateTime && !departurePortId) {
                alert('⚠️ Departure Port Not Selected\n\nYou filled in the Departure Date/Time but did not select a Port.\n\nPlease select a Departure Port to continue.');
                return;
            }
            
            // If both are empty, just close the modal without adding anything
            if (!hasArrival && !hasDeparture) {
                const accommodationModal = bootstrap.Modal.getInstance(document.getElementById('accommodationModal'));
                if (accommodationModal) {
                    accommodationModal.hide();
                }
                return;
            }
            
            // Add new standalone entries
            if (arrivalDateTime && arrivalPortId) {
                const arrivalTransfer = document.getElementById('arrivalTransfer')?.checked || false;
                
                // Only get transfer-related fields if transfer is checked
                let arrivalTransferWay = 'both-way';
                let arrivalTransferType = 'sic';
                let arrivalVehicleId = '';
                let arrivalVehicleType = 'sedan';
                let arrivalAdults = adults;
                let arrivalChild = child;
                let arrivalInfant = infant;
                let arrivalDestinationId = '';
                let arrivalDestinationName = '';
                
                if (arrivalTransfer) {
                    arrivalTransferWay = document.getElementById('arrivalTransferWay')?.value || 'both-way';
                    arrivalTransferType = document.getElementById('arrivalTransferType')?.value || 'S';
                    
                    // Get both vehicle_id (value) and vehicle type name (data attribute)
                    const arrivalVehicleSelect = document.getElementById('arrivalVehicleType');
                    arrivalVehicleId = arrivalVehicleSelect?.value || '';
                    arrivalVehicleType = arrivalVehicleSelect?.selectedOptions[0]?.getAttribute('data-type') || '';
                    
                    arrivalAdults = parseInt(document.getElementById('arrivalAdults')?.value || adults);
                    arrivalChild = parseInt(document.getElementById('arrivalChild')?.value || child);
                    arrivalInfant = parseInt(document.getElementById('arrivalInfant')?.value || infant);
                    const arrivalDestinationSelect = document.getElementById('arrivalDestination');
                    arrivalDestinationId = arrivalDestinationSelect?.value || '';
                    arrivalDestinationName = arrivalDestinationSelect?.selectedOptions[0]?.getAttribute('data-name') || '';
                } else {
                    // Use default values from header if transfer is not checked
                    arrivalAdults = adults;
                    arrivalChild = child;
                    arrivalInfant = infant;
                }
                
                const arrivalId = generateId('arrdep');
                // Calculate vehicle price
                const arrivalVehiclePrice = calculateVehiclePrice(arrivalVehicleType, arrivalTransferType, arrivalAdults, arrivalChild);
                
                const arrivalEntry = {
                    id: arrivalId,
                    dateTime: arrivalDateTime,
                    portId: arrivalPortId,
                    portName: arrivalPortName,
                    flightNo: arrivalFlightNo || '-',
                    type: 'Arrival',
                    adultsQty: arrivalAdults,
                    adultCost: arrivalVehiclePrice,
                    adultSell: arrivalVehiclePrice, // Default: cost = sell
                    childQty: arrivalChild,
                    childCost: arrivalVehiclePrice,
                    childSell: arrivalVehiclePrice, // Default: cost = sell
                    infantQty: arrivalInfant,
                    amount: 0,
                    hasTransfer: arrivalTransfer,
                    transferWay: arrivalTransferWay,
                    transferType: arrivalTransferType,
                    vehicleId: arrivalVehicleId,
                    vehicleType: arrivalVehicleType,
                    transferDestinationId: arrivalDestinationId,
                    transferDestinationName: arrivalDestinationName,
                    supplement: '',
                    accommodationIndex: null
                };
                
                // Add to transfer list if transfer is checked
                if (arrivalTransfer) {
                    const transferId = generateId('transfer');
                    transferList.push({
                        id: transferId,
                        transportMode: 'local',
                        isStandalone: false,
                        sourceType: 'arrival',
                        sourceId: arrivalId,
                        dateTime: arrivalDateTime,
                        portName: arrivalPortName,
                        destination: arrivalDestinationName ? `Arrival: ${arrivalPortName} → ${arrivalDestinationName}` : `Arrival: ${arrivalPortName}`,
                        vehicleType: arrivalVehicleType,
                        type: arrivalTransferType,
                        way: arrivalTransferWay,
                        hasTransfer: true,
                        adults: arrivalAdults,
                        child: arrivalChild,
                        cost: 0,
                        sell: 0,
                        taxIncluded: false
                    });
                    arrivalEntry.transferId = transferId;
                }
                
                arrivalDepartureList.push(arrivalEntry);
            }

            if (departureDateTime && departurePortId) {
                const departureTransfer = document.getElementById('departureTransfer')?.checked || false;
                
                // Only get transfer-related fields if transfer is checked
                let departureTransferWay = 'both-way';
                let departureTransferType = 'sic';
                let departureVehicleId = '';
                let departureVehicleType = 'sedan';
                let departureAdults = adults;
                let departureChild = child;
                let departureInfant = infant;
                let departureDestinationId = '';
                let departureDestinationName = '';
                
                if (departureTransfer) {
                    departureTransferWay = document.getElementById('departureTransferWay')?.value || 'both-way';
                    departureTransferType = document.getElementById('departureTransferType')?.value || 'S';
                    
                    // Get both vehicle_id (value) and vehicle type name (data attribute)
                    const departureVehicleSelect = document.getElementById('departureVehicleType');
                    departureVehicleId = departureVehicleSelect?.value || '';
                    departureVehicleType = departureVehicleSelect?.selectedOptions[0]?.getAttribute('data-type') || '';
                    
                    departureAdults = parseInt(document.getElementById('departureAdults')?.value || adults);
                    departureChild = parseInt(document.getElementById('departureChild')?.value || child);
                    departureInfant = parseInt(document.getElementById('departureInfant')?.value || infant);
                    const departureDestinationSelect = document.getElementById('departureDestination');
                    departureDestinationId = departureDestinationSelect?.value || '';
                    departureDestinationName = departureDestinationSelect?.selectedOptions[0]?.getAttribute('data-name') || '';
                } else {
                    // Use default values from header if transfer is not checked
                    departureAdults = adults;
                    departureChild = child;
                    departureInfant = infant;
                }
                
                const departureId = generateId('arrdep');
                
                // Calculate vehicle price
                const departureVehiclePrice = calculateVehiclePrice(departureVehicleType, departureTransferType, departureAdults, departureChild);
                
                const departureEntry = {
                    id: departureId,
                    dateTime: departureDateTime,
                    portId: departurePortId,
                    portName: departurePortName,
                    flightNo: departureFlightNo || '-',
                    type: 'Departure',
                    adultsQty: departureAdults,
                    adultCost: departureVehiclePrice,
                    adultSell: departureVehiclePrice, // Default: cost = sell
                    childQty: departureChild,
                    childCost: departureVehiclePrice,
                    childSell: departureVehiclePrice, // Default: cost = sell
                    infantQty: departureInfant,
                    amount: 0,
                    hasTransfer: departureTransfer,
                    transferWay: departureTransferWay,
                    transferType: departureTransferType,
                    vehicleId: departureVehicleId,
                    vehicleType: departureVehicleType,
                    transferDestinationId: departureDestinationId,
                    transferDestinationName: departureDestinationName,
                    supplement: '',
                    accommodationIndex: null
                };
                
                // Add to transfer list if transfer is checked
                if (departureTransfer) {
                    const transferId = generateId('transfer');
                    transferList.push({
                        id: transferId,
                        transportMode: 'local',
                        isStandalone: false,
                        sourceType: 'departure',
                        sourceId: departureId,
                        dateTime: departureDateTime,
                        portName: departurePortName,
                        destination: departureDestinationName ? `Departure: ${departureDestinationName} → ${departurePortName}` : `Departure: ${departurePortName}`,
                        vehicleType: departureVehicleType,
                        type: departureTransferType,
                        way: departureTransferWay,
                        hasTransfer: true,
                        adults: departureAdults,
                        child: departureChild,
                        cost: 0,
                        sell: 0,
                        taxIncluded: false
                    });
                    departureEntry.transferId = transferId;
                }
                
                arrivalDepartureList.push(departureEntry);
            }
        }

        // Update tables
        updateArrivalDepartureTable();
        updateTransferTable();
        
        // Update header dates
        if (isEditing) {
            // When editing, recalculate from all services to handle date changes properly
            recalculateHeaderDatesFromServices();
        } else {
            // When adding new, expand if needed
            if (arrivalDateTime) {
                console.log('Updating header dates for ARRIVAL...');
                updateHeaderDatesIfNeeded(arrivalDateTime);
            }
            if (departureDateTime) {
                console.log('Updating header dates for DEPARTURE...');
                updateHeaderDatesIfNeeded(departureDateTime);
            }
        }

        // Close modal
        const accommodationModal = bootstrap.Modal.getInstance(document.getElementById('accommodationModal'));
        accommodationModal.hide();

        // Clear fields
        document.getElementById('arrivalDateTime').value = '';
        $('#arrivalPort').val('').trigger('change');
        document.getElementById('arrivalFlightNo').value = '';
        document.getElementById('arrivalVehicleType').value = 'sedan';
        document.getElementById('arrivalAdults').value = '2';
        document.getElementById('arrivalChild').value = '0';
        document.getElementById('arrivalInfant').value = '0';
        document.getElementById('arrivalTransferWay').value = 'both-way';
        document.getElementById('arrivalTransferType').value = 'S';
        document.getElementById('departureDateTime').value = '';
        $('#departurePort').val('').trigger('change');
        document.getElementById('departureFlightNo').value = '';
        document.getElementById('departureVehicleType').value = 'sedan';
        document.getElementById('departureAdults').value = '2';
        document.getElementById('departureChild').value = '0';
        document.getElementById('departureInfant').value = '0';
        document.getElementById('departureTransferWay').value = 'both-way';
        document.getElementById('departureTransferType').value = 'S';
        
        window.isArrivalDepartureOnlyMode = false;
    }

    // Save selected hotels to main accommodation table
    function saveSelectedHotels() {
        // Check if we're in arrival/departure only mode
        if (window.isArrivalDepartureOnlyMode) {
            saveArrivalDepartureOnly();
            return;
        }
        
        // Check if we're editing an existing accommodation
        if (window.editingAccommodationIndex !== undefined && window.editingAccommodationIndex !== null) {
            // Get selected combinations
            const selectedCombinations = getSelectedRoomCombinations();
            
            if (selectedCombinations.length === 0) {
                alert('Please select at least one room combination');
                return;
            }
            
            // Get the first selected combination (for editing, we only allow one)
            const combo = selectedCombinations[0];
            
            // Get the form values
            const hotelSelect = document.getElementById('hotelSelect');
            const hotelId = hotelSelect.value;
            const hotelName = hotelSelect.options[hotelSelect.selectedIndex].text;
            const destination = document.getElementById('hotelDestination').value;
            const checkIn = document.getElementById('checkInDate').value;
            const checkOut = document.getElementById('checkOutDate').value;
            const nights = document.getElementById('numNights').value;
            
            // Get values from the selected combination
            const roomType = combo.roomType;
            const roomId = combo.roomId;
            const bedType = combo.bedType;
            const mealPlan = combo.mealPlan;
            const maxOccupancy = combo.maxOccupancy;
            const rooms = combo.rooms;
            const adultsPerRoom = combo.adultsPerRoom;
            const extraBed = combo.extraBed;
            const childWithoutBed = combo.childWithoutBed;
            const roomPrice = combo.price;
            
            // Get arrival/departure data
            const arrivalDateTime = document.getElementById('arrivalDateTime').value;
            const arrivalPortSelect = document.getElementById('arrivalPort');
            const arrivalPortId = arrivalPortSelect.value;
            const arrivalPortName = arrivalPortSelect.selectedOptions[0]?.text || '';
            const arrivalFlightNo = document.getElementById('arrivalFlightNo').value;
            const departureDateTime = document.getElementById('departureDateTime').value;
            const departurePortSelect = document.getElementById('departurePort');
            const departurePortId = departurePortSelect.value;
            const departurePortName = departurePortSelect.selectedOptions[0]?.text || '';
            const departureFlightNo = document.getElementById('departureFlightNo').value;
            
            // Update the existing accommodation
            accommodationList[window.editingAccommodationIndex] = {
                ...accommodationList[window.editingAccommodationIndex],
                hotelId: hotelId,
                hotelName: hotelName,
                destination: destination,
                roomId: roomId,
                roomType: roomType,
                bedType: bedType,
                maxOccupancy: maxOccupancy,
                checkIn: checkIn,
                checkOut: checkOut,
                nights: nights,
                rooms: rooms,
                adultsPerRoom: adultsPerRoom,
                extraBed: extraBed,
                childWithoutBed: childWithoutBed,
                mealPlan: mealPlan,
                roomPrice: roomPrice,
                // Store arrival/departure info with accommodation
                arrivalDateTime: arrivalDateTime,
                arrivalPortId: arrivalPortId,
                arrivalPortName: arrivalPortName,
                arrivalFlightNo: arrivalFlightNo,
                departureDateTime: departureDateTime,
                departurePortId: departurePortId,
                departurePortName: departurePortName,
                departureFlightNo: departureFlightNo
            };
            
            // Update arrival/departure list
            const adults = parseInt(document.querySelector('.customer-details input[type="number"][value="2"]')?.value || 2);
            const child = parseInt(document.querySelector('.customer-details input[type="number"][value="1"]')?.value || 1);
            const infant = parseInt(document.querySelector('.customer-details input[type="number"][value="0"]')?.value || 0);
            
            // Remove old arrival/departure entries for this accommodation
            // IMPORTANT: Save the IDs BEFORE modifying the accommodation object
            const oldHotel = accommodationList[window.editingAccommodationIndex];
            const oldArrivalDepartureIds = oldHotel.arrivalDepartureIds ? [...oldHotel.arrivalDepartureIds] : [];
            
            console.log('Removing old arrival/departure entries for accommodation index:', window.editingAccommodationIndex);
            console.log('Old arrival/departure IDs:', oldArrivalDepartureIds);
            
            // Remove entries by both ID and accommodation index to ensure clean update
            arrivalDepartureList = arrivalDepartureList.filter(item => {
                const matchesId = oldArrivalDepartureIds.map(String).includes(String(item.id));
                const matchesIndex = item.accommodationIndex === window.editingAccommodationIndex;
                return !(matchesId || matchesIndex);
            });
            
            // Add new arrival/departure entries
            const newArrivalDepartureIds = [];
            
            if (arrivalDateTime && arrivalPortId) {
                const arrivalId = generateId('arrdep');
                const arrivalTransfer = document.getElementById('arrivalTransfer')?.checked || false;
                const arrivalTransferWay = document.getElementById('arrivalTransferWay')?.value || 'both-way';
                const arrivalTransferType = document.getElementById('arrivalTransferType')?.value || 'S';
                const arrivalVehicleType = document.getElementById('arrivalVehicleType')?.value || '';
                
                // Calculate vehicle price
                const arrivalVehiclePrice = calculateVehiclePrice(arrivalVehicleType, arrivalTransferType, adults, child);
                
                arrivalDepartureList.push({
                    id: arrivalId,
                    dateTime: arrivalDateTime,
                    portId: arrivalPortId,
                    portName: arrivalPortName,
                    flightNo: arrivalFlightNo || '-',
                    type: 'Arrival',
                    adultsQty: adults,
                    adultCost: arrivalVehiclePrice,
                    adultSell: arrivalVehiclePrice, // Default: cost = sell
                    childQty: child,
                    childCost: arrivalVehiclePrice,
                    childSell: arrivalVehiclePrice, // Default: cost = sell
                    infantQty: infant,
                    amount: 0,
                    hasTransfer: arrivalTransfer,
                    transferWay: arrivalTransferWay,
                    transferType: arrivalTransferType,
                    vehicleType: arrivalVehicleType,
                    supplement: '',
                    accommodationIndex: window.editingAccommodationIndex
                });
                newArrivalDepartureIds.push(arrivalId);
            }
            
            if (departureDateTime && departurePortId) {
                const departureId = generateId('arrdep');
                const departureTransfer = document.getElementById('departureTransfer')?.checked || false;
                const departureTransferWay = document.getElementById('departureTransferWay')?.value || 'both-way';
                const departureTransferType = document.getElementById('departureTransferType')?.value || 'S';
                const departureVehicleType = document.getElementById('departureVehicleType')?.value || '';
                
                // Calculate vehicle price
                const departureVehiclePrice = calculateVehiclePrice(departureVehicleType, departureTransferType, adults, child);
                
                arrivalDepartureList.push({
                    id: departureId,
                    dateTime: departureDateTime,
                    portId: departurePortId,
                    portName: departurePortName,
                    flightNo: departureFlightNo || '-',
                    type: 'Departure',
                    adultsQty: adults,
                    adultCost: departureVehiclePrice,
                    adultSell: departureVehiclePrice, // Default: cost = sell
                    childQty: child,
                    childCost: departureVehiclePrice,
                    childSell: departureVehiclePrice, // Default: cost = sell
                    infantQty: infant,
                    amount: 0,
                    hasTransfer: departureTransfer,
                    transferWay: departureTransferWay,
                    transferType: departureTransferType,
                    vehicleType: departureVehicleType,
                    supplement: '',
                    accommodationIndex: window.editingAccommodationIndex
                });
                newArrivalDepartureIds.push(departureId);
            }
            
            // Store the IDs with the accommodation
            accommodationList[window.editingAccommodationIndex].arrivalDepartureIds = newArrivalDepartureIds;
            
            // Handle Hotel Transfer Updates
            const hotelTransferChecked = document.getElementById('hotelTransferCheckbox')?.checked || false;
            const oldTransferIds = accommodationList[window.editingAccommodationIndex].transferIds || [];
            
            // Remove old transfers associated with this accommodation
            if (oldTransferIds.length > 0) {
                transferList = transferList.filter(t => !oldTransferIds.includes(t.id));
            }
            
            // Add new transfer if checkbox is checked
            const newTransferIds = [];
            if (hotelTransferChecked) {
                const hotelTransferDestination = document.getElementById('hotelTransferDestination')?.value || '';
                
                if (hotelTransferDestination) {
                    // Get both vehicle_id (value) and vehicle type name (data attribute)
                    const hotelTransferVehicleSelect = document.getElementById('hotelTransferVehicleType');
                    const hotelTransferVehicleId = hotelTransferVehicleSelect?.value || '';
                    const hotelTransferVehicleType = hotelTransferVehicleSelect?.selectedOptions[0]?.getAttribute('data-type') || 'sedan';
                    const hotelTransferWay = document.getElementById('hotelTransferWay')?.value || 'both-way';
                    const hotelTransferType = document.getElementById('hotelTransferType')?.value || 'S';
                    
                    // Get destination name from the select option
                    const destSelect = document.getElementById('hotelTransferDestination');
                    const destOption = destSelect.options[destSelect.selectedIndex];
                    const destinationName = destOption.getAttribute('data-name') || destOption.text;
                    const destinationType = destOption.getAttribute('data-type') || 'other';
                    
                    // Create transfer entry
                    const transferId = generateId('transfer');
                    
                    const transferEntry = {
                        id: transferId,
                        dateTime: checkIn,
                        service: `${hotelName} / ${destinationName}`,
                        hotelName: hotelName,
                        hotelDestination: destination,
                        destination: destinationName,
                        destinationType: destinationType,
                        mode: 'Transfer',
                        vehicleId: hotelTransferVehicleId,
                        vehicleType: hotelTransferVehicleType,
                        type: hotelTransferType,
                        way: hotelTransferWay,
                        adults: adults,
                        adultsQty: adults,
                        adultCost: 0,
                        adultSell: 0,
                        child: child,
                        childQty: child,
                        childCost: 0,
                        childSell: 0,
                        infantQty: infant,
                        amount: 0,
                        isStandalone: false,
                        sourceType: 'hotel',
                        sourceId: null,
                        accommodationIndex: window.editingAccommodationIndex
                    };
                    
                    transferList.push(transferEntry);
                    newTransferIds.push(transferId);
                }
            }
            
            // Store transfer IDs with accommodation
            accommodationList[window.editingAccommodationIndex].transferIds = newTransferIds;
            
            // Update tables
            updateArrivalDepartureTable();
            updateTransferTable();
            
            // Clear the editing flag
            window.editingAccommodationIndex = null;
            
            // Update table
            updateAccommodationTable();
            
            // When editing, recalculate from all services to handle date changes properly
            recalculateHeaderDatesFromServices();
            
            // Close modal
            const accommodationModal = bootstrap.Modal.getInstance(document.getElementById('accommodationModal'));
            accommodationModal.hide();
            
            return;
        }
        
        // Normal flow - adding new hotels from selected combinations
        const selectedCombinations = getSelectedRoomCombinations();
        
        if (selectedCombinations.length === 0) {
            alert('Please select at least one room combination');
            return;
        }
        
        // Convert selected combinations to hotel entries
        selectedHotelsTemp = convertCombinationsToHotels(selectedCombinations);

        // Process Arrival/Departure information
        const arrivalDateTime = document.getElementById('arrivalDateTime').value;
        const arrivalPortSelect = document.getElementById('arrivalPort');
        const arrivalPortId = arrivalPortSelect.value;
        const arrivalPortName = arrivalPortSelect.selectedOptions[0]?.text || '';
        const arrivalFlightNo = document.getElementById('arrivalFlightNo').value;
        const departureDateTime = document.getElementById('departureDateTime').value;
        const departurePortSelect = document.getElementById('departurePort');
        const departurePortId = departurePortSelect.value;
        const departurePortName = departurePortSelect.selectedOptions[0]?.text || '';
        const departureFlightNo = document.getElementById('departureFlightNo').value;

        // Get pax numbers from header
        const adults = parseInt(document.querySelector('.customer-details input[type="number"][value="2"]')?.value || 2);
        const child = parseInt(document.querySelector('.customer-details input[type="number"][value="1"]')?.value || 1);
        const infant = parseInt(document.querySelector('.customer-details input[type="number"][value="0"]')?.value || 0);

        const newArrivalDepartureIds = [];

        // Store arrival/departure data with each hotel in temp list first
        selectedHotelsTemp = selectedHotelsTemp.map(hotel => ({
            ...hotel,
            arrivalDateTime: arrivalDateTime,
            arrivalPortId: arrivalPortId,
            arrivalPortName: arrivalPortName,
            arrivalFlightNo: arrivalFlightNo,
            departureDateTime: departureDateTime,
            departurePortId: departurePortId,
            departurePortName: departurePortName,
            departureFlightNo: departureFlightNo,
            arrivalDepartureIds: []
        }));

        // Update standalone arrival/departure entries if they exist
        const standaloneArrival = arrivalDepartureList.find(item => item.type === 'Arrival' && item.accommodationIndex === null);
        const standaloneDeparture = arrivalDepartureList.find(item => item.type === 'Departure' && item.accommodationIndex === null);
        
        if (standaloneArrival && arrivalDateTime && arrivalPortId) {
            // Update existing standalone arrival
            standaloneArrival.dateTime = arrivalDateTime;
            standaloneArrival.portId = arrivalPortId;
            standaloneArrival.portName = arrivalPortName;
            standaloneArrival.flightNo = arrivalFlightNo || '-';
        } else if (!standaloneArrival && arrivalDateTime && arrivalPortId) {
            // Create new standalone arrival if it doesn't exist
            const arrivalVehicleType = document.getElementById('arrivalVehicleType')?.value || '';
            const arrivalTransferType = document.getElementById('arrivalTransferType')?.value || 'S';
            const arrivalVehiclePrice = calculateVehiclePrice(arrivalVehicleType, arrivalTransferType, adults, child);
            
            arrivalDepartureList.push({
                id: generateId('arrdep'),
                dateTime: arrivalDateTime,
                portId: arrivalPortId,
                portName: arrivalPortName,
                flightNo: arrivalFlightNo || '-',
                type: 'Arrival',
                adultsQty: adults,
                adultCost: arrivalVehiclePrice,
                adultSell: arrivalVehiclePrice, // Default: cost = sell
                childQty: child,
                childCost: arrivalVehiclePrice,
                childSell: arrivalVehiclePrice, // Default: cost = sell
                infantQty: infant,
                amount: 0,
                supplement: '',
                accommodationIndex: null
            });
        }
        
        if (standaloneDeparture && departureDateTime && departurePortId) {
            // Update existing standalone departure
            standaloneDeparture.dateTime = departureDateTime;
            standaloneDeparture.portId = departurePortId;
            standaloneDeparture.portName = departurePortName;
            standaloneDeparture.flightNo = departureFlightNo || '-';
        } else if (!standaloneDeparture && departureDateTime && departurePortId) {
            // Create new standalone departure if it doesn't exist
            const departureVehicleType = document.getElementById('departureVehicleType')?.value || '';
            const departureTransferType = document.getElementById('departureTransferType')?.value || 'S';
            const departureVehiclePrice = calculateVehiclePrice(departureVehicleType, departureTransferType, adults, child);
            
            arrivalDepartureList.push({
                id: generateId('arrdep'),
                dateTime: departureDateTime,
                portId: departurePortId,
                portName: departurePortName,
                flightNo: departureFlightNo || '-',
                type: 'Departure',
                adultsQty: adults,
                adultCost: departureVehiclePrice,
                adultSell: departureVehiclePrice, // Default: cost = sell
                childQty: child,
                childCost: departureVehiclePrice,
                childSell: departureVehiclePrice, // Default: cost = sell
                infantQty: infant,
                amount: 0,
                supplement: '',
                accommodationIndex: null
            });
        }
        
        // Add to main accommodation list
        const startIndex = accommodationList.length;
        accommodationList = [...accommodationList, ...selectedHotelsTemp];
        
        // Now create arrival/departure entries linked to each hotel
        selectedHotelsTemp.forEach((hotel, idx) => {
            const accommodationIdx = startIndex + idx;
            const hotelArrivalDepartureIds = [];
            
            // Add Arrival if provided
            if (arrivalDateTime && arrivalPortId) {
                const arrivalId = generateId('arrdep');
                const arrivalTransfer = document.getElementById('arrivalTransfer')?.checked || false;
                // Get vehicle type name from data attribute instead of value (which is vehicle_id)
                const arrivalVehicleSelect = document.getElementById('arrivalVehicleType');
                const arrivalVehicleType = arrivalVehicleSelect?.selectedOptions[0]?.getAttribute('data-type') || '';
                const arrivalTransferType = document.getElementById('arrivalTransferType')?.value || 'S';
                const arrivalTransferWay = document.getElementById('arrivalTransferWay')?.value || 'both-way';
                const arrivalVehiclePrice = calculateVehiclePrice(arrivalVehicleType, arrivalTransferType, adults, child);
                
                const arrival = {
                    id: arrivalId,
                    dateTime: arrivalDateTime,
                    portId: arrivalPortId,
                    portName: arrivalPortName,
                    flightNo: arrivalFlightNo || '-',
                    type: 'Arrival',
                    adultsQty: adults,
                    adultCost: arrivalVehiclePrice,
                    adultSell: arrivalVehiclePrice, // Default: cost = sell
                    childQty: child,
                    childCost: arrivalVehiclePrice,
                    childSell: arrivalVehiclePrice, // Default: cost = sell
                    infantQty: infant,
                    amount: 0,
                    hasTransfer: arrivalTransfer,
                    transferWay: arrivalTransferWay,
                    transferType: arrivalTransferType,
                    vehicleType: arrivalVehicleType,
                    supplement: '',
                    accommodationIndex: accommodationIdx
                };
                arrivalDepartureList.push(arrival);
                hotelArrivalDepartureIds.push(arrivalId);
            }

            // Add Departure if provided
            if (departureDateTime && departurePortId) {
                const departureId = generateId('arrdep');
                const departureTransfer = document.getElementById('departureTransfer')?.checked || false;
                // Get vehicle type name from data attribute instead of value (which is vehicle_id)
                const departureVehicleSelect = document.getElementById('departureVehicleType');
                const departureVehicleType = departureVehicleSelect?.selectedOptions[0]?.getAttribute('data-type') || '';
                const departureTransferType = document.getElementById('departureTransferType')?.value || 'S';
                const departureTransferWay = document.getElementById('departureTransferWay')?.value || 'both-way';
                const departureVehiclePrice = calculateVehiclePrice(departureVehicleType, departureTransferType, adults, child);
                
                const departure = {
                    id: departureId,
                    dateTime: departureDateTime,
                    portId: departurePortId,
                    portName: departurePortName,
                    flightNo: departureFlightNo || '-',
                    type: 'Departure',
                    adultsQty: adults,
                    adultCost: departureVehiclePrice,
                    adultSell: departureVehiclePrice, // Default: cost = sell
                    childQty: child,
                    childCost: departureVehiclePrice,
                    childSell: departureVehiclePrice, // Default: cost = sell
                    infantQty: infant,
                    amount: 0,
                    hasTransfer: departureTransfer,
                    transferWay: departureTransferWay,
                    transferType: departureTransferType,
                    vehicleType: departureVehicleType,
                    supplement: '',
                    accommodationIndex: accommodationIdx
                };
                arrivalDepartureList.push(departure);
                hotelArrivalDepartureIds.push(departureId);
            }
            
            // Update the accommodation with the IDs
            accommodationList[accommodationIdx].arrivalDepartureIds = hotelArrivalDepartureIds;
        });
        
        // Process Hotel Transfer ONCE for all rooms of this hotel (not per room)
        const hotelTransferChecked = document.getElementById('hotelTransferCheckbox')?.checked || false;
        if (hotelTransferChecked && selectedHotelsTemp.length > 0) {
            const hotelTransferDestination = document.getElementById('hotelTransferDestination')?.value || '';
            // Get both vehicle_id (value) and vehicle type name (data attribute)
            const hotelTransferVehicleSelect = document.getElementById('hotelTransferVehicleType');
            const hotelTransferVehicleId = hotelTransferVehicleSelect?.value || '';
            const hotelTransferVehicleType = hotelTransferVehicleSelect?.selectedOptions[0]?.getAttribute('data-type') || 'sedan';
            const hotelTransferWay = document.getElementById('hotelTransferWay')?.value || 'both-way';
            const hotelTransferType = document.getElementById('hotelTransferType')?.value || 'S';
            const hotelTransferRemarks = document.getElementById('hotelTransferRemarks')?.value || '';
            
            if (hotelTransferDestination) {
                // Get destination name from the select option
                const destSelect = document.getElementById('hotelTransferDestination');
                const destOption = destSelect.options[destSelect.selectedIndex];
                const destinationName = destOption.getAttribute('data-name') || destOption.text;
                const destinationType = destOption.getAttribute('data-type') || 'other';
                
                // Create ONE transfer entry for this hotel booking (not per room)
                const transferId = generateId('transfer');
                const firstHotel = selectedHotelsTemp[0]; // Use first hotel for details
                const hotelName = firstHotel.hotelName;
                const hotelDestination = firstHotel.destination;
                
                const transferEntry = {
                    id: transferId,
                    dateTime: firstHotel.checkIn, // Use check-in date as transfer date
                    service: `${hotelName} / ${destinationName}`,
                    hotelName: hotelName,
                    hotelDestination: hotelDestination,
                    destination: destinationName,
                    destinationType: destinationType,
                    mode: 'Transfer',
                    vehicleId: hotelTransferVehicleId,
                    vehicleType: hotelTransferVehicleType,
                    type: hotelTransferType,
                    way: hotelTransferWay,
                    adults: adults,
                    adultsQty: adults,
                    adultCost: 0,
                    adultSell: 0,
                    child: child,
                    childQty: child,
                    childCost: 0,
                    childSell: 0,
                    infantQty: infant,
                    amount: 0,
                    isStandalone: false,
                    sourceType: 'accommodation',
                    sourceId: firstHotel.id,
                    accommodationIndex: startIndex, // Store the first accommodation index
                    remarks: hotelTransferRemarks || ''
                };
                
                transferList.push(transferEntry);
                
                // Associate this ONE transfer with ALL rooms of this hotel booking
                for (let i = startIndex; i < startIndex + selectedHotelsTemp.length; i++) {
                    if (!accommodationList[i].transferIds) {
                        accommodationList[i].transferIds = [];
                    }
                    accommodationList[i].transferIds.push(transferId);
                }
            }
        }
        
        updateAccommodationTable();
        
        // Update Transfer table if transfers were added
        if (document.getElementById('hotelTransferCheckbox')?.checked) {
            updateTransferTable();
        }

        // Update Arrival/Departure table
        updateArrivalDepartureTable();
        
        // Recalculate totals
        recalculateTotals();

        // Expand header dates if check-in/check-out are outside range
        const checkIn = document.getElementById('checkInDate').value;
        const checkOut = document.getElementById('checkOutDate').value;
        if (checkIn) {
            expandHeaderDatesIfNeeded(checkIn, false);
        }
        if (checkOut) {
            expandHeaderDatesIfNeeded(checkOut, false);
        }
        
        // Expand header dates for arrival/departure if provided
        if (arrivalDateTime) {
            expandHeaderDatesIfNeeded(arrivalDateTime, true);
        }
        if (departureDateTime) {
            expandHeaderDatesIfNeeded(departureDateTime, true);
        }

        // Close modal
        const accommodationModal = bootstrap.Modal.getInstance(document.getElementById('accommodationModal'));
        accommodationModal.hide();

        // Clear temp list and arrival/departure fields
        selectedHotelsTemp = [];
        document.getElementById('arrivalDateTime').value = '';
        $('#arrivalPort').val('').trigger('change');
        document.getElementById('arrivalFlightNo').value = '';
        document.getElementById('departureDateTime').value = '';
        $('#departurePort').val('').trigger('change');
        document.getElementById('departureFlightNo').value = '';
    }

    // Update main accommodation table
    function updateAccommodationTable() {
        const tbody = document.getElementById('accommodationTableBody');
        const table = document.getElementById('accommodationTable');
        const emptyMessage = document.getElementById('emptyAccommodationMessage');

        if (accommodationList.length === 0) {
            table.style.display = 'none';
            emptyMessage.style.display = 'block';
            return;
        }

        table.style.display = 'table';
        emptyMessage.style.display = 'none';

        tbody.innerHTML = accommodationList.map((hotel, index) => {
            // Ensure checkIn and checkOut have time component
            let checkInValue = hotel.checkIn;
            let checkOutValue = hotel.checkOut;
            
            // If no time component, add default times (11:00 for check-in, 10:00 for check-out)
            if (checkInValue && !checkInValue.includes('T')) {
                checkInValue = checkInValue + 'T11:00';
            }
            if (checkOutValue && !checkOutValue.includes('T')) {
                checkOutValue = checkOutValue + 'T10:00';
            }
            
            return `
            <tr>
                <td><input type="checkbox" class="accommodation-checkbox" value="${hotel.id}"></td>
                <td>
                    <a href="javascript:void(0)" onclick="editAccommodation(${index})" style="color: #0d6efd; text-decoration: underline; cursor: pointer;">
                        <strong>${hotel.hotelName}</strong><br>
                        <small style="color: #666; font-size: 0.6rem;">
                            Room: ${hotel.roomType || 'N/A'} | Bed: ${hotel.bedType || 'N/A'} | Meal: ${hotel.mealPlan || 'N/A'}
                        </small>
                    </a>
                </td>
                <td><input type="datetime-local" value="${checkInValue}" onchange="updateAccommodationField(${index}, 'checkIn', this.value); recalculateNights(${index})"></td>
                <td><input type="datetime-local" value="${checkOutValue}" onchange="updateAccommodationField(${index}, 'checkOut', this.value); recalculateNights(${index})"></td>
                <td><input type="number" value="${hotel.nights}" readonly style="background-color: #f5f5f5;"></td>
                <td><input type="number" value="${hotel.rooms}" min="1" onchange="updateAccommodationField(${index}, 'rooms', this.value)"></td>
                <td><input type="number" value="${hotel.adultsPerRoom}" min="1" onchange="updateAccommodationField(${index}, 'adultsPerRoom', this.value)"></td>
                <td><input type="number" value="${hotel.extraBed}" min="0" onchange="updateAccommodationField(${index}, 'extraBed', this.value)"></td>
                <td><input type="number" value="${hotel.childWithoutBed}" min="0" onchange="updateAccommodationField(${index}, 'childWithoutBed', this.value)"></td>
            </tr>
        `;
        }).join('');
        
        // Expand header dates based on accommodation check-in/check-out dates
        if (accommodationList && accommodationList.length > 0) {
            accommodationList.forEach(hotel => {
                if (hotel.checkIn) {
                    expandHeaderDatesIfNeeded(hotel.checkIn, false);
                }
                if (hotel.checkOut) {
                    expandHeaderDatesIfNeeded(hotel.checkOut, false);
                }
            });
        }
    }

    // Update accommodation field
    function updateAccommodationField(index, field, value) {
        if (accommodationList[index]) {
            accommodationList[index][field] = value;
        }
    }

    // Recalculate nights when dates change
    function recalculateNights(index) {
        const hotel = accommodationList[index];
        if (!hotel) return;

        // Expand header dates if accommodation dates are outside range
        if (hotel.checkIn) {
            expandHeaderDatesIfNeeded(hotel.checkIn, false);
        }
        if (hotel.checkOut) {
            expandHeaderDatesIfNeeded(hotel.checkOut, false);
        }

        const checkIn = new Date(hotel.checkIn);
        const checkOut = new Date(hotel.checkOut);
        
        if (checkIn && checkOut && checkOut > checkIn) {
            const diffTime = Math.abs(checkOut - checkIn);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            accommodationList[index].nights = diffDays;
            updateAccommodationTable();
        }
    }

    // Edit accommodation - opens modal with hotel data
    function editAccommodation(index) {
        const hotel = accommodationList[index];
        if (!hotel) return;
        
        // Set editing mode
        window.editingAccommodationIndex = index;
        
        // Reset and hide the temp hotels section
        selectedHotelsTemp = [];
        document.getElementById('selectedHotelsList').innerHTML = '';
        document.getElementById('noHotelsMessage').style.display = 'block';
        
        // Hide arrival/departure section (already hidden by default)
        const arrivalDepartureSection = document.getElementById('arrivalDepartureSection');
        if (arrivalDepartureSection) {
            arrivalDepartureSection.style.display = 'none';
        }
        
        // Set the destination first
        document.getElementById('hotelDestination').value = hotel.destination;
        
        // Set dates
        document.getElementById('checkInDate').value = hotel.checkIn;
        document.getElementById('checkOutDate').value = hotel.checkOut;
        document.getElementById('numNights').value = hotel.nights;
        
        // Load hotels for the destination
        loadHotelsByDestination();
        
        // Wait a bit for hotels to load, then set the hotel and load room combinations
        setTimeout(() => {
            const hotelSelectElement = document.getElementById('hotelSelect');
            hotelSelectElement.value = hotel.hotelId;
            
            // Verify hotel was selected
            if (hotelSelectElement.value != hotel.hotelId) {
                console.log('Hotel not found, trying again...');
                setTimeout(() => {
                    hotelSelectElement.value = hotel.hotelId;
                    loadRoomTypes();
                }, 200);
            } else {
                loadRoomTypes();
            }
            
            // Wait for room combinations to load, then select the matching combination
            setTimeout(() => {
                if (window.currentRoomCombinations && window.currentRoomCombinations.length > 0) {
                    // Find the matching combination
                    const matchingCombo = window.currentRoomCombinations.find(combo => 
                        combo.roomType === hotel.roomType && 
                        combo.bedType === hotel.bedType && 
                        combo.mealPlan === hotel.mealPlan
                    );
                    
                    if (matchingCombo) {
                        // Check the matching combination
                        const checkbox = document.querySelector(`.room-combination-checkbox[data-combo-id="${matchingCombo.id}"]`);
                        if (checkbox) {
                            checkbox.checked = true;
                            
                            // Set the values for this combination
                            const roomsInput = document.querySelector(`.combo-rooms[data-combo-id="${matchingCombo.id}"]`);
                            const adultsInput = document.querySelector(`.combo-adults[data-combo-id="${matchingCombo.id}"]`);
                            const extraBedInput = document.querySelector(`.combo-extra-bed[data-combo-id="${matchingCombo.id}"]`);
                            const childWithoutInput = document.querySelector(`.combo-child-without[data-combo-id="${matchingCombo.id}"]`);
                            
                            if (roomsInput) roomsInput.value = hotel.rooms;
                            if (adultsInput) adultsInput.value = hotel.adultsPerRoom;
                            if (extraBedInput) extraBedInput.value = hotel.extraBed;
                            if (childWithoutInput) childWithoutInput.value = hotel.childWithoutBed;
                        }
                    }
                }
                
                // Load existing transfer data if any
                if (hotel.transferIds && hotel.transferIds.length > 0) {
                    // Find the transfer associated with this hotel
                    const hotelTransfer = transferList.find(t => hotel.transferIds.includes(t.id));
                    if (hotelTransfer) {
                        // Check the transfer checkbox
                        const transferCheckbox = document.getElementById('hotelTransferCheckbox');
                        if (transferCheckbox) {
                            transferCheckbox.checked = true;
                            toggleHotelTransferFields(); // Show the fields
                        }
                        
                        // Populate transfer fields
                        setTimeout(() => {
                            const destSelect = document.getElementById('hotelTransferDestination');
                            if (destSelect && hotelTransfer.destinationType) {
                                // Reconstruct the value based on destination type
                                const destValue = `${hotelTransfer.destinationType}_${hotelTransfer.destination}`;
                                // Try to find matching option
                                for (let i = 0; i < destSelect.options.length; i++) {
                                    if (destSelect.options[i].getAttribute('data-name') === hotelTransfer.destination) {
                                        destSelect.value = destSelect.options[i].value;
                                        break;
                                    }
                                }
                            }
                            
                            const vehicleType = document.getElementById('hotelTransferVehicleType');
                            if (vehicleType) vehicleType.value = hotelTransfer.vehicleType || 'sedan';
                            
                            const way = document.getElementById('hotelTransferWay');
                            if (way) way.value = hotelTransfer.way || 'both-way';
                            
                            const type = document.getElementById('hotelTransferType');
                            if (type) type.value = hotelTransfer.type || 'S';
                        }, 600);
                    }
                }
            }, 500);
        }, 500);
        
        // Change the save button text to "Update"
        document.getElementById('saveAccommodationBtnText').textContent = 'Update Accommodation';
        
        // Open the modal
        const accommodationModal = new bootstrap.Modal(document.getElementById('accommodationModal'));
        accommodationModal.show();
    }
    

    // Open standalone Arrival/Departure modal (without accommodation)
    function openArrivalDepartureModal() {
        console.log('========================================');
        console.log('openArrivalDepartureModal() called');
        console.log('========================================');
        
        try {
            // Set flag to prevent auto-population of existing data FIRST
            window.skipArrivalDepartureAutoPopulate = true;
            
            // Set flag to indicate this is ADD mode (not EDIT mode)
            window.isAddingNewArrivalDeparture = true;
            
            // Open the modal and hide hotel sections
            openAccommodationModal();
        
        // Set flag for arrival/departure only mode AFTER opening modal (because openAccommodationModal resets it)
        window.isArrivalDepartureOnlyMode = true;
        
        // Update modal title and button text IMMEDIATELY after opening
        document.getElementById('modalTitleIcon').className = 'ri-flight-takeoff-line me-2';
        document.getElementById('modalTitleText').textContent = 'Add Arrival / Departure';
        document.getElementById('arrivalDepartureSectionTitle').textContent = 'Arrival/Departure Flight Information';
        document.getElementById('saveAccommodationBtnText').textContent = 'Add Arrival/Departure';
        
        // Hide hotel selection sections
        document.getElementById('hotelSelectionRow1').style.display = 'none';
        const hotelSelectionRow2 = document.getElementById('hotelSelectionRow2');
        if (hotelSelectionRow2) hotelSelectionRow2.style.display = 'none';
        const selectedHotelsSection = document.getElementById('selectedHotelsSection');
        if (selectedHotelsSection) selectedHotelsSection.style.display = 'none';
        
        // Hide room combinations and hotel transfer sections
        const roomCombinationsSection = document.getElementById('roomCombinationsSection');
        if (roomCombinationsSection) roomCombinationsSection.style.display = 'none';
        const hotelTransferSection = document.getElementById('hotelTransferSection');
        if (hotelTransferSection) hotelTransferSection.style.display = 'none';
        
        // Show arrival/departure section
        const arrivalDepartureSection = document.getElementById('arrivalDepartureSection');
        if (arrivalDepartureSection) {
            arrivalDepartureSection.style.display = 'block';
        }
        
        // Show all arrival/departure fields (both arrival and departure)
        document.getElementById('arrivalDateTimeField').style.display = 'block';
        document.getElementById('arrivalPortField').style.display = 'block';
        document.getElementById('arrivalFlightNoField').style.display = 'block';
        document.getElementById('arrivalTransferField').style.display = 'block';
        document.getElementById('departureDateTimeField').style.display = 'block';
        document.getElementById('departurePortField').style.display = 'block';
        document.getElementById('departureFlightNoField').style.display = 'block';
        document.getElementById('departureTransferField').style.display = 'block';
        
        // Reset transfer fields to default values
        document.getElementById('arrivalVehicleType').value = '';
        document.getElementById('arrivalAdults').value = '2';
        document.getElementById('arrivalChild').value = '0';
        document.getElementById('arrivalInfant').value = '0';
        document.getElementById('arrivalTransferWay').value = 'both-way';
        document.getElementById('arrivalTransferType').value = 'S';
        
        document.getElementById('departureVehicleType').value = '';
        document.getElementById('departureAdults').value = '2';
        document.getElementById('departureChild').value = '0';
        document.getElementById('departureInfant').value = '0';
        document.getElementById('departureTransferWay').value = 'both-way';
        document.getElementById('departureTransferType').value = 'S';
        
        // Hide transfer details sections
        const arrivalTransferDetails = document.getElementById('arrivalTransferDetailsSection');
        const departureTransferDetails = document.getElementById('departureTransferDetailsSection');
        if (arrivalTransferDetails) arrivalTransferDetails.style.display = 'none';
        if (departureTransferDetails) departureTransferDetails.style.display = 'none';
        
        // Re-initialize Select2 for port dropdowns after showing fields
        setTimeout(() => {
            if (typeof $.fn.select2 !== 'undefined') {
                // Destroy existing Select2 instances if they exist
                if ($('#arrivalPort').hasClass('select2-hidden-accessible')) {
                    $('#arrivalPort').select2('destroy');
                }
                if ($('#departurePort').hasClass('select2-hidden-accessible')) {
                    $('#departurePort').select2('destroy');
                }
                
                // Re-initialize Select2
                $('.select2-port').select2({
                    placeholder: 'Search and select port',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#accommodationModal')
                });
            }
            
            // Clear all arrival/departure fields ONLY if in ADD mode (not EDIT mode)
            if (window.isAddingNewArrivalDeparture) {
                document.getElementById('arrivalDateTime').value = '';
                document.getElementById('arrivalFlightNo').value = '';
                document.getElementById('arrivalTransfer').checked = false;
                document.getElementById('departureDateTime').value = '';
                document.getElementById('departureFlightNo').value = '';
                document.getElementById('departureTransfer').checked = false;
                
                // Reset Select2 dropdowns
                $('#arrivalPort').val(null).trigger('change');
                $('#departurePort').val(null).trigger('change');
                $('#arrivalDestination').val(null).trigger('change');
                $('#departureDestination').val(null).trigger('change');
                
                // Reset the flag after clearing
                window.isAddingNewArrivalDeparture = false;
            }
        }, 100);
        
        // Reset the flag after a delay (to allow openAccommodationModal's setTimeout to complete)
        setTimeout(() => {
            window.skipArrivalDepartureAutoPopulate = false;
        }, 300);
        
        console.log('openArrivalDepartureModal() completed successfully');
        } catch (error) {
            console.error('Error in openArrivalDepartureModal():', error);
            alert('Error opening arrival/departure modal: ' + error.message);
        }
    }

    // Remove selected accommodation
    function removeSelectedAccommodation() {
        const checkboxes = document.querySelectorAll('.accommodation-checkbox:checked');
        
        if (checkboxes.length === 0) {
            alert('Please select hotels to remove');
            return;
        }

        const idsToRemove = Array.from(checkboxes).map(cb => cb.value);
        
        // Also remove associated arrival/departure entries
        accommodationList.forEach(hotel => {
            if (idsToRemove.includes(String(hotel.id)) && hotel.arrivalDepartureIds) {
                const linkedIds = new Set(hotel.arrivalDepartureIds.map(id => String(id)));
                arrivalDepartureList = arrivalDepartureList.filter(item => !linkedIds.has(String(item.id)));
            }
        });
        
        accommodationList = accommodationList.filter(hotel => !idsToRemove.includes(String(hotel.id)));
        updateAccommodationTable();
        updateArrivalDepartureTable();
        recalculateHeaderDatesFromServices();
    }

    // Update Arrival/Departure table
    function updateArrivalDepartureTable() {
        const tbody = document.getElementById('arrivalDepartureTableBody');
        const table = document.getElementById('arrivalDepartureTable');
        const emptyMessage = document.getElementById('emptyArrivalDepartureMessage');

        // Filter to show only standalone arrival/departure entries (not linked to accommodation)
        const standaloneEntries = arrivalDepartureList
            .map((item, index) => ({ ...item, originalIndex: index }))
            .filter(item => item.accommodationIndex === null || item.accommodationIndex === undefined);

        if (standaloneEntries.length === 0) {
            table.style.display = 'none';
            emptyMessage.style.display = 'block';
            return;
        }

        table.style.display = 'table';
        emptyMessage.style.display = 'none';

        // Get header values for max validation
        const headerAdult = parseInt(document.getElementById('adultCountInput')?.value || 99);
        const headerChild = parseInt(document.getElementById('childCountInput')?.value || 99);
        const headerInfant = parseInt(document.getElementById('infantCountInput')?.value || 99);
        
        tbody.innerHTML = standaloneEntries.map((item) => {
            // Calculate amount: (adult sell * adult qty) + (child sell * child qty)
            const adultAmount = (parseFloat(item.adultSell || 0) * parseInt(item.adultsQty || 0));
            const childAmount = (parseFloat(item.childSell || 0) * parseInt(item.childQty || 0));
            const totalAmount = adultAmount + childAmount;
            
            // Format vehicle type and transfer type for display
            const vehicleTypeDisplay = item.vehicleType ? item.vehicleType.charAt(0).toUpperCase() + item.vehicleType.slice(1) : '';
            const transferTypeDisplay = item.transferType ? item.transferType.toUpperCase() : '';
            
            // Build the secondary info line (vehicle type and SIC/Private)
            let secondaryInfo = '';
            if (vehicleTypeDisplay && transferTypeDisplay) {
                secondaryInfo = `<div style="font-size: 9px; color: #6c757d; margin-top: 2px;">${vehicleTypeDisplay} / ${transferTypeDisplay}</div>`;
            } else if (vehicleTypeDisplay) {
                secondaryInfo = `<div style="font-size: 9px; color: #6c757d; margin-top: 2px;">${vehicleTypeDisplay}</div>`;
            } else if (transferTypeDisplay) {
                secondaryInfo = `<div style="font-size: 9px; color: #6c757d; margin-top: 2px;">${transferTypeDisplay}</div>`;
            }
            
            return `
            <tr>
                <td><input type="checkbox" class="arrivalDeparture-checkbox" value="${item.id}"></td>
                <td><input type="datetime-local" value="${normalizeDateTimeLocal(item.dateTime)}" onchange="updateArrivalDepartureDateTime(${item.originalIndex}, this.value)" style="width: 160px; font-size: 11px; padding: 2px 4px;"></td>
                <td>
                    <a href="javascript:void(0)" onclick="editArrivalDeparture(${item.originalIndex})" style="color: #0d6efd; text-decoration: underline; cursor: pointer;">
                        ${item.portName || '-'}
                    </a>
                    ${secondaryInfo}
                </td>
                <td>${item.flightNo || '-'}</td>
                <td>${item.type}</td>
                <td><input type="number" value="${item.adultsQty}" min="0" max="${headerAdult}" onchange="updateArrivalDepartureQty(${item.originalIndex}, 'adultsQty', this.value, ${headerAdult})"></td>
                <td style="text-align: center; vertical-align: middle;">${parseFloat(item.adultCost || 0).toFixed(2)}</td>
                <td><input type="number" value="${item.adultSell}" step="0.01" onchange="updateArrivalDepartureSell(${item.originalIndex}, 'adultSell', this.value)"></td>
                <td><input type="number" value="${item.childQty}" min="0" max="${headerChild}" onchange="updateArrivalDepartureQty(${item.originalIndex}, 'childQty', this.value, ${headerChild})"></td>
                <td style="text-align: center; vertical-align: middle;">${parseFloat(item.childCost || 0).toFixed(2)}</td>
                <td><input type="number" value="${item.childSell}" step="0.01" onchange="updateArrivalDepartureSell(${item.originalIndex}, 'childSell', this.value)"></td>
                <td><input type="number" value="${item.infantQty}" min="0" max="${headerInfant}" onchange="updateArrivalDepartureQty(${item.originalIndex}, 'infantQty', this.value, ${headerInfant})"></td>
                <td style="text-align: center; vertical-align: middle; font-weight: 600;">${totalAmount.toFixed(2)}</td>
            </tr>
            `;
        }).join('');
        
        // Expand header dates based on ALL arrival/departure dates (not just standalone)
        if (arrivalDepartureList && arrivalDepartureList.length > 0) {
            arrivalDepartureList.forEach(item => {
                if (item.dateTime) {
                    expandHeaderDatesIfNeeded(item.dateTime, true);
                }
            });
        }
    }

    // Format date for display (DATE ONLY - NO TIME)
    function formatDateTime(dateString) {
        if (!dateString) return '-';
        
        const isoDate = normalizeDateToYYYYMMDD(dateString);
        
        if (isoDate) {
            const [y, m, d] = isoDate.split('-');
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const month = monthNames[parseInt(m, 10) - 1] || m;
            return `${d} ${month} '${y.slice(-2)}`;
        }
        
        return dateString;
    }

    // Edit arrival/departure - find and edit the associated accommodation
    function editArrivalDeparture(index) {
        const arrivalDeparture = arrivalDepartureList[index];
        if (!arrivalDeparture) return;
        
        // Find the accommodation index
        const accommodationIdx = arrivalDeparture.accommodationIndex;
        
        if (accommodationIdx !== undefined && accommodationIdx !== null && accommodationList[accommodationIdx]) {
            // Edit the associated accommodation
            editAccommodation(accommodationIdx);
        } else {
            // Standalone arrival/departure - open modal with arrival/departure data only
            // Make sure the ADD flag is NOT set (this is EDIT mode)
            window.isAddingNewArrivalDeparture = false;
            
            // Set flag to prevent initializeModalDates from overwriting arrival/departure dates
            window.isEditingArrivalDeparture = true;
            
            openAccommodationModal();
            
            // Set flag for arrival/departure only mode AFTER opening modal (because openAccommodationModal resets it)
            window.isArrivalDepartureOnlyMode = true;
            
            // Hide hotel sections
            document.getElementById('hotelSelectionRow1').style.display = 'none';
            const hotelSelectionRow2 = document.getElementById('hotelSelectionRow2');
            if (hotelSelectionRow2) hotelSelectionRow2.style.display = 'none';
            const selectedHotelsSection = document.getElementById('selectedHotelsSection');
            if (selectedHotelsSection) selectedHotelsSection.style.display = 'none';
            
            // Hide room combinations and hotel transfer sections
            const roomCombinationsSection = document.getElementById('roomCombinationsSection');
            if (roomCombinationsSection) roomCombinationsSection.style.display = 'none';
            const hotelTransferSection = document.getElementById('hotelTransferSection');
            if (hotelTransferSection) hotelTransferSection.style.display = 'none';
            
            // Show arrival/departure section
            const arrivalDepartureSection = document.getElementById('arrivalDepartureSection');
            if (arrivalDepartureSection) {
                arrivalDepartureSection.style.display = 'block';
            }
            
            // Show ONLY the relevant fields based on what is being edited
            if (arrivalDeparture.type === 'Arrival') {
                // Editing Arrival - Show only Arrival fields, Hide Departure fields
                document.getElementById('arrivalDateTimeField').style.display = 'block';
                document.getElementById('arrivalPortField').style.display = 'block';
                document.getElementById('arrivalFlightNoField').style.display = 'block';
                document.getElementById('arrivalTransferField').style.display = 'block';
                document.getElementById('departureDateTimeField').style.display = 'none';
                document.getElementById('departurePortField').style.display = 'none';
                document.getElementById('departureFlightNoField').style.display = 'none';
                document.getElementById('departureTransferField').style.display = 'none';
                
                // Update modal title
                document.getElementById('modalTitleIcon').className = 'ri-flight-takeoff-line me-2';
                document.getElementById('modalTitleText').textContent = 'Edit Arrival';
            } else {
                // Editing Departure - Show only Departure fields, Hide Arrival fields
                document.getElementById('arrivalDateTimeField').style.display = 'none';
                document.getElementById('arrivalPortField').style.display = 'none';
                document.getElementById('arrivalFlightNoField').style.display = 'none';
                document.getElementById('arrivalTransferField').style.display = 'none';
                document.getElementById('departureDateTimeField').style.display = 'block';
                document.getElementById('departurePortField').style.display = 'block';
                document.getElementById('departureFlightNoField').style.display = 'block';
                document.getElementById('departureTransferField').style.display = 'block';
                
                // Update modal title
                document.getElementById('modalTitleIcon').className = 'ri-flight-land-line me-2';
                document.getElementById('modalTitleText').textContent = 'Edit Departure';
            }
            document.getElementById('arrivalDepartureSectionTitle').textContent = 'Flight Information';
            
            // Re-initialize Select2 for port dropdowns to ensure they work properly
            if (typeof $.fn.select2 !== 'undefined') {
                // Destroy existing Select2 instances if they exist
                if ($('#arrivalPort').hasClass('select2-hidden-accessible')) {
                    $('#arrivalPort').select2('destroy');
                }
                if ($('#departurePort').hasClass('select2-hidden-accessible')) {
                    $('#departurePort').select2('destroy');
                }
                
                // Re-initialize Select2
                $('.select2-port').select2({
                    placeholder: 'Search and select port',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#accommodationModal')
                });
            }
            
            // Populate the clicked entry data with a delay to ensure modal is fully initialized
            setTimeout(() => {
                console.log('=== EDITING ARRIVAL/DEPARTURE ===');
                console.log('Type:', arrivalDeparture.type);
                console.log('Original dateTime:', arrivalDeparture.dateTime);
                
                if (arrivalDeparture.type === 'Arrival') {
                    // Normalize date to YYYY-MM-DDTHH:mm format for datetime-local input
                    const normalizedDateTime = normalizeDateTimeLocal(arrivalDeparture.dateTime);
                    console.log('Normalized dateTime for Arrival:', normalizedDateTime);
                    
                    document.getElementById('arrivalDateTime').value = normalizedDateTime || '';
                    console.log('Set arrivalDateTime field to:', document.getElementById('arrivalDateTime').value);
                    
                    $('#arrivalPort').val(arrivalDeparture.portId).trigger('change');
                    document.getElementById('arrivalFlightNo').value = arrivalDeparture.flightNo || '';
                    document.getElementById('arrivalTransfer').checked = arrivalDeparture.hasTransfer || false;
                    toggleArrivalTransferFields(); // Show/hide transfer fields
                    if (arrivalDeparture.transferDestinationId) {
                        $('#arrivalDestination').val(arrivalDeparture.transferDestinationId).trigger('change');
                    }
                    document.getElementById('arrivalTransferWay').value = arrivalDeparture.transferWay || 'both-way';
                    document.getElementById('arrivalTransferType').value = arrivalDeparture.transferType || 'S';
                    document.getElementById('arrivalVehicleType').value = arrivalDeparture.vehicleType || 'sedan';
                    document.getElementById('arrivalAdults').value = arrivalDeparture.adultsQty || 2;
                    document.getElementById('arrivalChild').value = arrivalDeparture.childQty || 0;
                    document.getElementById('arrivalInfant').value = arrivalDeparture.infantQty || 0;
                } else {
                    // Normalize date to YYYY-MM-DDTHH:mm format for datetime-local input
                    const normalizedDateTime = normalizeDateTimeLocal(arrivalDeparture.dateTime);
                    console.log('Normalized dateTime for Departure:', normalizedDateTime);
                    
                    document.getElementById('departureDateTime').value = normalizedDateTime || '';
                    console.log('Set departureDateTime field to:', document.getElementById('departureDateTime').value);
                    
                    $('#departurePort').val(arrivalDeparture.portId).trigger('change');
                    document.getElementById('departureFlightNo').value = arrivalDeparture.flightNo || '';
                    document.getElementById('departureTransfer').checked = arrivalDeparture.hasTransfer || false;
                    toggleDepartureTransferFields(); // Show/hide transfer fields
                    if (arrivalDeparture.transferDestinationId) {
                        $('#departureDestination').val(arrivalDeparture.transferDestinationId).trigger('change');
                    }
                    document.getElementById('departureTransferWay').value = arrivalDeparture.transferWay || 'both-way';
                    document.getElementById('departureTransferType').value = arrivalDeparture.transferType || 'S';
                    document.getElementById('departureVehicleType').value = arrivalDeparture.vehicleType || 'sedan';
                    document.getElementById('departureAdults').value = arrivalDeparture.adultsQty || 2;
                    document.getElementById('departureChild').value = arrivalDeparture.childQty || 0;
                    document.getElementById('departureInfant').value = arrivalDeparture.infantQty || 0;
                }
                
                // Reset the flag after populating
                window.isEditingArrivalDeparture = false;
            }, 250);
            
            // Update button text based on type
            if (arrivalDeparture.type === 'Arrival') {
                document.getElementById('saveAccommodationBtnText').textContent = 'Update Arrival';
            } else {
                document.getElementById('saveAccommodationBtnText').textContent = 'Update Departure';
            }
            
            // Set editing flags (isArrivalDepartureOnlyMode already set earlier)
            window.editingArrivalDepartureIndex = index;
            window.editingArrivalDepartureType = arrivalDeparture.type;
        }
    }

    // Update arrival/departure field
    function updateArrivalDepartureField(index, field, value) {
        if (arrivalDepartureList[index]) {
            arrivalDepartureList[index][field] = value;
            
            // Update linked transfer if exists
            const arrDepEntry = arrivalDepartureList[index];
            if (arrDepEntry.transferId) {
                const transferIndex = transferList.findIndex(t => t.id === arrDepEntry.transferId);
                if (transferIndex !== -1) {
                    // Update relevant fields in the linked transfer
                    if (field === 'dateTime') {
                        transferList[transferIndex].dateTime = value;
                    } else if (field === 'vehicleType') {
                        transferList[transferIndex].vehicleType = value;
                    } else if (field === 'adultsQty') {
                        transferList[transferIndex].adults = parseInt(value) || 0;
                    } else if (field === 'childQty') {
                        transferList[transferIndex].child = parseInt(value) || 0;
                    } else if (field === 'transferWay') {
                        transferList[transferIndex].way = value;
                    } else if (field === 'transferType') {
                        transferList[transferIndex].type = value;
                    }
                    // Update transfer table to reflect changes
                    updateTransferTable();
                }
            }
        }
    }

    // Update arrival/departure quantity with validation
    function updateArrivalDepartureQty(index, field, value, maxValue) {
        const qty = parseInt(value) || 0;
        if (qty > maxValue) {
            alert(`Quantity cannot exceed header value of ${maxValue}`);
            value = maxValue;
        }
        if (arrivalDepartureList[index]) {
            arrivalDepartureList[index][field] = value;
            updateArrivalDepartureTable();
        }
    }

    // Update arrival/departure sell price and recalculate
    function updateArrivalDepartureSell(index, field, value) {
        if (arrivalDepartureList[index]) {
            arrivalDepartureList[index][field] = parseFloat(value) || 0;
            updateArrivalDepartureTable();
        }
    }

    // Update arrival/departure date/time
    function updateArrivalDepartureDateTime(index, value) {
        if (arrivalDepartureList[index]) {
            arrivalDepartureList[index].dateTime = value;
            updateArrivalDepartureTable();
            recalculateHeaderDatesFromServices();
        }
    }

    // Validate arrival passengers against header and vehicle capacity
    function validateArrivalPassengers() {
        const headerAdult = parseInt(document.getElementById('adultCountInput')?.value || 99);
        const headerChild = parseInt(document.getElementById('childCountInput')?.value || 99);
        const headerInfant = parseInt(document.getElementById('infantCountInput')?.value || 99);
        
        const arrivalAdults = document.getElementById('arrivalAdults');
        const arrivalChild = document.getElementById('arrivalChild');
        const arrivalInfant = document.getElementById('arrivalInfant');
        
        if (parseInt(arrivalAdults.value) > headerAdult) {
            alert(`Adult count cannot exceed header value of ${headerAdult}`);
            arrivalAdults.value = headerAdult;
        }
        if (parseInt(arrivalChild.value) > headerChild) {
            alert(`Child count cannot exceed header value of ${headerChild}`);
            arrivalChild.value = headerChild;
        }
        if (parseInt(arrivalInfant.value) > headerInfant) {
            alert(`Infant count cannot exceed header value of ${headerInfant}`);
            arrivalInfant.value = headerInfant;
        }
        
        // Check vehicle capacity
        const vehicleSelect = document.getElementById('arrivalVehicleType');
        const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
        if (selectedOption && selectedOption.dataset.seating) {
            const seatingCapacity = parseInt(selectedOption.dataset.seating);
            const totalPassengers = parseInt(arrivalAdults.value) + parseInt(arrivalChild.value);
            if (totalPassengers > seatingCapacity) {
                alert(`Total passengers (${totalPassengers}) exceeds vehicle capacity (${seatingCapacity} seats)`);
            }
        }
    }

    // Validate departure passengers against header and vehicle capacity
    function validateDeparturePassengers() {
        const headerAdult = parseInt(document.getElementById('adultCountInput')?.value || 99);
        const headerChild = parseInt(document.getElementById('childCountInput')?.value || 99);
        const headerInfant = parseInt(document.getElementById('infantCountInput')?.value || 99);
        
        const departureAdults = document.getElementById('departureAdults');
        const departureChild = document.getElementById('departureChild');
        const departureInfant = document.getElementById('departureInfant');
        
        if (parseInt(departureAdults.value) > headerAdult) {
            alert(`Adult count cannot exceed header value of ${headerAdult}`);
            departureAdults.value = headerAdult;
        }
        if (parseInt(departureChild.value) > headerChild) {
            alert(`Child count cannot exceed header value of ${headerChild}`);
            departureChild.value = headerChild;
        }
        if (parseInt(departureInfant.value) > headerInfant) {
            alert(`Infant count cannot exceed header value of ${headerInfant}`);
            departureInfant.value = headerInfant;
        }
        
        // Check vehicle capacity
        const vehicleSelect = document.getElementById('departureVehicleType');
        const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
        if (selectedOption && selectedOption.dataset.seating) {
            const seatingCapacity = parseInt(selectedOption.dataset.seating);
            const totalPassengers = parseInt(departureAdults.value) + parseInt(departureChild.value);
            if (totalPassengers > seatingCapacity) {
                alert(`Total passengers (${totalPassengers}) exceeds vehicle capacity (${seatingCapacity} seats)`);
            }
        }
    }

    // Update arrival vehicle pricing when vehicle is selected
    function updateArrivalVehiclePricing() {
        const vehicleSelect = document.getElementById('arrivalVehicleType');
        const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            const transferType = document.getElementById('arrivalTransferType').value;
            const basePrice = transferType === 'S' 
                ? parseFloat(selectedOption.dataset.sharablePrice || 0)
                : parseFloat(selectedOption.dataset.basePrice || 0);
            
            // Store the vehicle price for later use
            vehicleSelect.dataset.currentPrice = basePrice;
            
            console.log('Arrival vehicle selected:', {
                vehicle: selectedOption.text,
                type: transferType,
                price: basePrice,
                seating: selectedOption.dataset.seating
            });
        }
    }

    // Update departure vehicle pricing when vehicle is selected
    function updateDepartureVehiclePricing() {
        const vehicleSelect = document.getElementById('departureVehicleType');
        const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            const transferType = document.getElementById('departureTransferType').value;
            const basePrice = transferType === 'S' 
                ? parseFloat(selectedOption.dataset.sharablePrice || 0)
                : parseFloat(selectedOption.dataset.basePrice || 0);
            
            // Store the vehicle price for later use
            vehicleSelect.dataset.currentPrice = basePrice;
            
            console.log('Departure vehicle selected:', {
                vehicle: selectedOption.text,
                type: transferType,
                price: basePrice,
                seating: selectedOption.dataset.seating
            });
        }
    }

    // Calculate vehicle price based on passengers and vehicle capacity
    function calculateVehiclePrice(vehicleId, transferType, adults, child) {
        if (!vehicleId) return 0;
        
        const vehicleSelect = document.getElementById('arrivalVehicleType');
        const departureVehicleSelect = document.getElementById('departureVehicleType');
        
        // Try to find the vehicle in either dropdown
        let vehicleOption = null;
        if (vehicleSelect) {
            vehicleOption = Array.from(vehicleSelect.options).find(opt => opt.value == vehicleId);
        }
        if (!vehicleOption && departureVehicleSelect) {
            vehicleOption = Array.from(departureVehicleSelect.options).find(opt => opt.value == vehicleId);
        }
        
        if (!vehicleOption) return 0;
        
        const totalPassengers = parseInt(adults || 0) + parseInt(child || 0);
        const seatingCapacity = parseInt(vehicleOption.dataset.seating || 0);
        
        // Check if passengers fit in vehicle
        if (totalPassengers > seatingCapacity) {
            console.warn(`Passengers (${totalPassengers}) exceed vehicle capacity (${seatingCapacity})`);
        }
        
        // Get price based on transfer type
        const price = transferType === 'S' 
            ? parseFloat(vehicleOption.dataset.sharablePrice || 0)
            : parseFloat(vehicleOption.dataset.basePrice || 0);
        
        return price;
    }

    // Remove selected arrival/departure
    function removeSelectedArrivalDeparture() {
        const checkboxes = document.querySelectorAll('.arrivalDeparture-checkbox:checked');
        
        if (checkboxes.length === 0) {
            alert('Please select arrival/departure entries to remove');
            return;
        }

        const idsToRemove = Array.from(checkboxes).map(cb => cb.value);
        arrivalDepartureList = arrivalDepartureList.filter(item => !idsToRemove.includes(String(item.id)));
        updateArrivalDepartureTable();
        recalculateHeaderDatesFromServices();
    }

    // ==================== TOUR FUNCTIONS ====================
    
    // Open Tour Modal
    function openTourModal() {
        window.editingTourIndex = null;
        
        const defaultDateTime = getDefaultServiceDate();
        
        // Reset form fields
        const destinationSelect = document.getElementById('tourDestination');
        if (destinationSelect) {
            destinationSelect.value = '';
        }
        
        // Clear attractions table
        const tbody = document.getElementById('attractionsTableBody');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="15" class="text-center text-muted" style="padding: 20px;">Please select a destination to load attractions</td></tr>';
        }
        
        // Reset all checkboxes in the table
        const selectAllCheckbox = document.getElementById('selectAllAttractions');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
        }
        
        // Update modal title
        const modalTitle = document.getElementById('tourModalTitleText');
        if (modalTitle) {
            modalTitle.textContent = 'Tour Details';
        }
        
        // Set date range constraints
        if (typeof updateAllServiceDateRanges === 'function') {
            updateAllServiceDateRanges();
        }
        
        // Set default date/time
        const dateTimeInput = document.getElementById('tourDateTime');
        if (dateTimeInput) {
            dateTimeInput.value = defaultDateTime;
        }
        
        // Auto-fill country from header
        autoFillModalFields('tour');
        
        const tourModal = new bootstrap.Modal(document.getElementById('tourModal'));
        tourModal.show();
    }
    
    // Toggle transfer fields
    function toggleTransferFields() {
        // Check if old form elements exist (for backward compatibility)
        const transferRequired = document.getElementById('transferRequired');
        if (!transferRequired) return; // New table-based modal doesn't use these fields
        
        const required = transferRequired.value;
        const show = required === 'yes';
        
        const transferTypeField = document.getElementById('transferTypeField');
        const transferWayField = document.getElementById('transferWayField');
        const vehicleTypeField = document.getElementById('vehicleTypeField');
        const transferCostFields = document.getElementById('transferCostFields');
        const transferSellFields = document.getElementById('transferSellFields');
        
        if (transferTypeField) transferTypeField.style.display = show ? 'block' : 'none';
        if (transferWayField) transferWayField.style.display = show ? 'block' : 'none';
        if (vehicleTypeField) vehicleTypeField.style.display = show ? 'block' : 'none';
        if (transferCostFields) transferCostFields.style.display = show ? 'block' : 'none';
        if (transferSellFields) transferSellFields.style.display = show ? 'block' : 'none';
    }
    
    // Toggle guide fields
    function toggleGuideFields() {
        // Check if old form elements exist (for backward compatibility)
        const guideRequired = document.getElementById('guideRequired');
        if (!guideRequired) return; // New table-based modal doesn't use these fields
        
        const required = guideRequired.value;
        const show = required === 'yes';
        
        const guideLanguageField = document.getElementById('guideLanguageField');
        const guideNameField = document.getElementById('guideNameField');
        const guideHoursField = document.getElementById('guideHoursField');
        const guideCostField = document.getElementById('guideCostField');
        const guideSellField = document.getElementById('guideSellField');
        
        if (guideLanguageField) guideLanguageField.style.display = show ? 'block' : 'none';
        if (guideNameField) guideNameField.style.display = show ? 'block' : 'none';
        if (guideHoursField) guideHoursField.style.display = show ? 'block' : 'none';
        if (guideCostField) guideCostField.style.display = show ? 'block' : 'none';
        if (guideSellField) guideSellField.style.display = show ? 'block' : 'none';
    }
    
    // Helper function to get destination options HTML for attraction transfers
    function getDestinationOptionsHTML() {
        return `
            <optgroup label="Ports">
                @foreach($ports as $port)
                    <option value="port_{{ $port->id }}" data-name="{{ $port->port_name }}" data-type="port" data-country="{{ $port->country }}">{{ $port->port_name }}</option>
                @endforeach
            </optgroup>
            <optgroup label="Hotels">
                @foreach($hotels as $hotel)
                    <option value="hotel_{{ $hotel->id }}" data-name="{{ $hotel->name }}" data-type="hotel" data-city="{{ $hotel->city ?? '' }}">{{ $hotel->name }}</option>
                @endforeach
            </optgroup>
            <optgroup label="Attractions">
                @foreach($attractions as $attr2)
                    <option value="attraction_{{ $attr2->attraction_id }}" data-name="{{ $attr2->name }}" data-type="attraction" data-location="{{ $attr2->location ?? '' }}">{{ $attr2->name }}</option>
                @endforeach
            </optgroup>
            <optgroup label="Restaurants">
                @foreach($restaurants as $rest)
                    <option value="restaurant_{{ $rest->restaurant_id }}" data-name="{{ $rest->name }}" data-type="restaurant" data-city="{{ $rest->city ?? '' }}">{{ $rest->name }}</option>
                @endforeach
            </optgroup>
        `;
    }
    
    // Helper function to get meal transfer destination options HTML (simple destination names)
    function getMealDestinationOptionsHTML(selectedValue) {
        const destinations = [
            @foreach($destinations as $dest)
                '{{ $dest->name }}',
            @endforeach
        ];
        return destinations.map(dest => 
            `<option value="${dest}" ${selectedValue === dest ? 'selected' : ''}>${dest}</option>`
        ).join('');
    }
    
    // Helper function to get guide options HTML for attractions
    function getGuideOptionsHTML() {
        return `
            @foreach($guides as $guide)
                @php
                    $languages = $guide->languages->pluck('language')->join(', ');
                @endphp
                <option value="{{ $guide->guide_id }}" data-name="{{ $guide->name }}" data-languages="{{ $languages }}">{{ $guide->name }} @if($languages)({{ $languages }})@endif</option>
            @endforeach
        `;
    }
    
    // Helper function to get vehicle options HTML for attractions
    function getVehicleOptionsHTML() {
        return `
            @php
                $vehicleTypes = $vehicles->groupBy('vehicle_type');
            @endphp
            @foreach($vehicleTypes as $type => $typeVehicles)
                <optgroup label="{{ ucfirst($type) }}">
                    @foreach($typeVehicles as $vehicle)
                        <option value="{{ $vehicle->vehicle_id }}" 
                            data-type="{{ $vehicle->vehicle_type }}"
                            data-seating="{{ $vehicle->seating_capacity }}"
                            data-base-price="{{ $vehicle->base_price ?? 0 }}"
                            data-sharable-price="{{ $vehicle->sharable_base_price ?? 0 }}">
                            {{ $vehicle->vehicle_name }} ({{ $vehicle->seating_capacity }} seats)
                        </option>
                    @endforeach
                </optgroup>
            @endforeach
        `;
    }
    
    // Load attractions by destination
    function loadAttractionsByDestination() {
        const destination = document.getElementById('tourDestination').value;
        const tbody = document.getElementById('attractionsTableBody');
        
        if (!destination) {
            tbody.innerHTML = '<tr><td colspan="15" class="text-center text-muted" style="padding: 20px;">Please select a destination to load attractions</td></tr>';
            return;
        }
        
        // Show loading state
        tbody.innerHTML = '<tr><td colspan="15" class="text-center" style="padding: 20px;"><i class="ri-loader-4-line ri-spin"></i> Loading attractions...</td></tr>';
        
        // Make AJAX call to get attractions by destination
        console.log('Loading attractions for destination:', destination);
        fetch(`{{ route('enquiry-form-pro.get-attractions') }}?destination=${encodeURIComponent(destination)}`)
            .then(response => {
                console.log('Attractions API response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Attractions API response data:', data);
                console.log('Attractions count:', data.count);
                console.log('DMC ID:', data.dmc_id);
                
                if (data.success && data.attractions.length > 0) {
                    // Get header values for auto-fill
                    const headerValues = getHeaderValues();
                    
                    // Build table rows
                    let html = '';
                    data.attractions.forEach(attr => {
                        console.log('Attraction:', attr.name, 'Adult Price:', attr.adult_price, 'Child Price:', attr.child_price);
                        html += `
                            <tr class="attraction-row" data-attraction-id="${attr.id}" data-attraction-name="${attr.name}">
                                <td style="padding: 2px 8px; text-align: center;">
                                    <input type="checkbox" class="attraction-checkbox" data-attr-id="${attr.id}">
                                </td>
                                <td style="padding: 2px 8px;">
                                    ${attr.name}
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="number" class="form-control form-control-sm attraction-adult-qty" data-attr-id="${attr.id}" value="${headerValues.adults || 0}" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="text" class="form-control form-control-sm attraction-adult-charge" data-attr-id="${attr.id}" value="SGD ${parseFloat(attr.adult_price || 0).toFixed(2)}" style="font-size: 10px; padding: 2px 4px;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="number" class="form-control form-control-sm attraction-child-qty" data-attr-id="${attr.id}" value="${headerValues.children || 0}" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="text" class="form-control form-control-sm attraction-child-charge" data-attr-id="${attr.id}" value="SGD ${parseFloat(attr.child_price || 0).toFixed(2)}" style="font-size: 10px; padding: 2px 4px;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="number" class="form-control form-control-sm attraction-infant-qty" data-attr-id="${attr.id}" value="${headerValues.infants || 0}" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <input type="text" class="form-control form-control-sm attraction-infant-charge" data-attr-id="${attr.id}" value="SGD 0.00" style="font-size: 10px; padding: 2px 4px;">
                                </td>
                                <td style="padding: 2px 8px; text-align: center;">
                                    <input type="checkbox" class="form-check-input attraction-transfer-checkbox" data-attr-id="${attr.id}" checked>
                                </td>
                                <td style="padding: 2px 8px;">
                                    <select class="form-select form-select-sm attraction-transfer-destination" data-attr-id="${attr.id}" style="font-size: 10px; padding: 2px 4px;">
                                        <option value="">Select Destination</option>
                                        ${getDestinationOptionsHTML()}
                                    </select>
                                </td>
                                <td style="padding: 2px 8px;">
                                    <select class="form-select form-select-sm attraction-vehicle-type" data-attr-id="${attr.id}" style="font-size: 10px; padding: 2px 4px;">
                                        <option value="">Select Vehicle</option>
                                        ${getVehicleOptionsHTML()}
                                    </select>
                                </td>
                                <td style="padding: 2px 8px;">
                                    <select class="form-select form-select-sm attraction-transfer-way" data-attr-id="${attr.id}" style="font-size: 10px; padding: 2px 4px;">
                                        <option value="one-way">1-Way</option>
                                        <option value="both-way" selected>2-Way</option>
                                    </select>
                                </td>
                                <td style="padding: 2px 8px;">
                                    <select class="form-select form-select-sm attraction-transfer-type" data-attr-id="${attr.id}" style="font-size: 10px; padding: 2px 4px;">
                                        <option value="P">Private</option>
                                        <option value="S" selected>Shared</option>
                                    </select>
                                </td>
                                <td style="padding: 2px 8px; text-align: center;">
                                    <input type="checkbox" class="form-check-input attraction-guide-checkbox" data-attr-id="${attr.id}">
                                </td>
                                <td style="padding: 2px 8px;">
                                    <select class="form-select form-select-sm attraction-guide-select" data-attr-id="${attr.id}" style="font-size: 10px; padding: 2px 4px;">
                                        <option value="">Select Guide</option>
                                        ${getGuideOptionsHTML()}
                                    </select>
                                </td>
                            </tr>
                        `;
                    });
                    tbody.innerHTML = html;
                } else {
                    tbody.innerHTML = '<tr><td colspan="15" class="text-center text-muted" style="padding: 20px;">No attractions found for this destination</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading attractions:', error);
                tbody.innerHTML = '<tr><td colspan="15" class="text-center text-danger" style="padding: 20px;">Error loading attractions. Please try again.</td></tr>';
            });
    }
    
    // Filter attractions by type
    function filterAttractionsByType(type) {
        const rows = document.querySelectorAll('.attraction-row');
        
        if (type === 'all') {
            rows.forEach(row => row.style.display = '');
        } else {
            rows.forEach(row => {
                const rowType = row.getAttribute('data-attraction-type');
                row.style.display = (rowType === type) ? '' : 'none';
            });
        }
    }
    
    // Toggle select all attractions
    function toggleSelectAllAttractions() {
        const selectAll = document.getElementById('selectAllAttractions');
        const checkboxes = document.querySelectorAll('.attraction-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
    }
    
    // Save and close attractions
    function saveAndCloseAttractions() {
        const selectedRows = document.querySelectorAll('.attraction-checkbox:checked');
        
        if (selectedRows.length === 0) {
            alert('Please select at least one attraction');
            return;
        }
        
        // Get date/time from modal input field
        const dateTimeInput = document.getElementById('tourDateTime');
        const dateTime = dateTimeInput?.value || getDefaultServiceDate();
        
        console.log('=== saveAndCloseAttractions called ===');
        console.log('DateTime input value:', dateTime);
        console.log('Selected rows:', selectedRows.length);
        console.log('Editing tour index:', window.editingTourIndex);
        
        if (!dateTime) {
            alert('Please select date/time for the tour/attraction');
            return;
        }
        
        // Check if we're in edit mode
        if (window.editingTourIndex !== undefined && window.editingTourIndex !== null) {
            // Edit mode - only update the first selected attraction
            const checkbox = selectedRows[0];
            const attrId = checkbox.getAttribute('data-attr-id');
            const row = checkbox.closest('tr');
            const attractionName = row.getAttribute('data-attraction-name');
            
            // Get values from the row
            const adultsQty = parseInt(row.querySelector('.attraction-adult-qty').value) || 0;
            const adultCharge = row.querySelector('.attraction-adult-charge').value || 'SGD 0.00';
            const childQty = parseInt(row.querySelector('.attraction-child-qty').value) || 0;
            const childCharge = row.querySelector('.attraction-child-charge').value || 'SGD 0.00';
            const infantQty = parseInt(row.querySelector('.attraction-infant-qty').value) || 0;
            const infantCharge = row.querySelector('.attraction-infant-charge').value || 'SGD 0.00';
            const transferChecked = row.querySelector('.attraction-transfer-checkbox').checked;
            const transferDestinationSelect = row.querySelector('.attraction-transfer-destination');
            const transferDestination = transferDestinationSelect?.value || '';
            // Get vehicle type name from data attribute instead of value (which is vehicle_id)
            const vehicleTypeSelect = row.querySelector('.attraction-vehicle-type');
            const vehicleType = vehicleTypeSelect?.selectedOptions[0]?.getAttribute('data-type') || 'sedan';
            const transferWay = row.querySelector('.attraction-transfer-way').value;
            const transferType = row.querySelector('.attraction-transfer-type').value;
            const guideChecked = row.querySelector('.attraction-guide-checkbox').checked;
            const guideSelect = row.querySelector('.attraction-guide-select');
            const guideId = guideSelect?.value || '';
            
            // Validate: if guide checkbox is checked, guide must be selected
            if (guideChecked && !guideId) {
                alert(`Please select a guide for ${attractionName}`);
                return;
            }
            
            // Parse charges
            const adultCost = parseFloat(adultCharge.replace(/[^0-9.]/g, '')) || 0;
            const adultSell = adultCost;
            const childCost = parseFloat(childCharge.replace(/[^0-9.]/g, '')) || 0;
            const childSell = childCost;
            const infantCost = parseFloat(infantCharge.replace(/[^0-9.]/g, '')) || 0;
            const infantSell = infantCost;
            
            // Remove old transfer and guide if exists
            const oldTour = tourList[window.editingTourIndex];
            const tourId = oldTour.id; // Use existing tour ID
            let transferId = oldTour.transferId; // Keep existing transfer ID if updating
            let guideEntryId = oldTour.guideId; // Keep existing guide ID if updating
            
            if (oldTour.transferId) {
                transferList = transferList.filter(t => t.id !== oldTour.transferId);
            }
            if (oldTour.guideId) {
                guideList = guideList.filter(g => g.id !== oldTour.guideId);
            }
            
            // Create transfer info if needed
            let transferInfo = null;
            
            if (transferChecked && transferDestination) {
                // Use existing transfer ID or generate new one
                if (!transferId) {
                    transferId = generateId('transfer');
                }
                
                // Get destination name from the select option
                const destOption = transferDestinationSelect.options[transferDestinationSelect.selectedIndex];
                const destinationName = destOption.getAttribute('data-name') || destOption.text;
                const destinationType = destOption.getAttribute('data-type') || 'other';
                
                transferInfo = {
                    id: transferId,
                    type: transferType === 'P' ? 'P' : 'S',
                    way: transferWay || 'both-way',
                    vehicleType: vehicleType,
                    cost: 0,
                    sell: 0,
                    service: `${attractionName} / ${destinationName}`,
                    attractionName: attractionName,
                    destination: destinationName,
                    destinationType: destinationType,
                    dateTime: dateTime,
                    adults: adultsQty,
                    child: childQty,
                    taxIncluded: true,
                    isStandalone: false,
                    sourceType: 'tour',
                    sourceId: tourId
                };
                
                transferList.push(transferInfo);
            } else {
                // Transfer unchecked, clear the ID
                transferId = null;
            }
            
            // Get guide info if selected
            let guideInfo = null;
            let guideName = '';
            let guideLanguages = '';
            
            if (guideChecked && guideId) {
                const guideOption = guideSelect.options[guideSelect.selectedIndex];
                guideName = guideOption.getAttribute('data-name') || guideOption.text;
                guideLanguages = guideOption.getAttribute('data-languages') || '';
                
                guideInfo = {
                    guide_id: guideId,
                    name: guideName,
                    languages: guideLanguages
                };
                
                // Use existing guide ID or generate new one
                if (!guideEntryId) {
                    guideEntryId = generateId('guide');
                }
                
                // Add guide to guide list
                const guideEntry = {
                    id: guideEntryId,
                    guide_id: guideId,
                    dateTime: dateTime,
                    tourName: `${attractionName} - ${guideName}`,
                    languages: guideLanguages,
                    name: guideName,
                    hours: 0,
                    cost: 0,
                    sell: 0,
                    isStandalone: false  // Mark as linked to attraction
                };
                
                guideList.push(guideEntry);
            } else {
                // Guide unchecked, clear the ID
                guideEntryId = null;
            }
            
            // Update the existing tour
            tourList[window.editingTourIndex] = {
                ...tourList[window.editingTourIndex],
                destination: document.getElementById('tourDestination').value || 'Singapore',
                attractionId: attrId,
                attractionName: attractionName,
                dateTime: dateTime,
                adultsQty: adultsQty,
                adultCost: adultCost,
                adultSell: adultSell,
                childQty: childQty,
                childCost: childCost,
                childSell: childSell,
                infantQty: infantQty,
                infantCost: infantCost,
                infantSell: infantSell,
                transferId: transferId,
                transferInfo: transferInfo,
                guideId: guideEntryId,
                guideInfo: guideInfo,
                guideRequired: guideChecked
            };
            
            console.log('Updated tour:', tourList[window.editingTourIndex]);
            
            // Clear editing flag
            window.editingTourIndex = null;
            
            // Update table
            updateTourTable();
            updateTransferTable();
            updateGuideTable();
            recalculateTotals();
            
            // Close modal
            const tourModal = bootstrap.Modal.getInstance(document.getElementById('tourModal'));
            tourModal.hide();
            
            // Reset checkboxes
            document.getElementById('selectAllAttractions').checked = false;
            document.querySelectorAll('.attraction-checkbox').forEach(cb => cb.checked = false);
            
            // Reset modal title
            document.getElementById('tourModalTitleText').textContent = 'Tour Details';
            
            return;
        }
        
        // Add mode - process all selected attractions
        selectedRows.forEach(checkbox => {
            const attrId = checkbox.getAttribute('data-attr-id');
            const row = checkbox.closest('tr');
            const attractionName = row.getAttribute('data-attraction-name');
            
            // Get values from the row
            const adultsQty = parseInt(row.querySelector('.attraction-adult-qty').value) || 0;
            const adultCharge = row.querySelector('.attraction-adult-charge').value || 'SGD 0.00';
            const childQty = parseInt(row.querySelector('.attraction-child-qty').value) || 0;
            const childCharge = row.querySelector('.attraction-child-charge').value || 'SGD 0.00';
            const infantQty = parseInt(row.querySelector('.attraction-infant-qty').value) || 0;
            const infantCharge = row.querySelector('.attraction-infant-charge').value || 'SGD 0.00';
            const transferChecked = row.querySelector('.attraction-transfer-checkbox').checked;
            const transferDestinationSelect = row.querySelector('.attraction-transfer-destination');
            const transferDestination = transferDestinationSelect?.value || '';
            // Get vehicle type name from data attribute instead of value (which is vehicle_id)
            const vehicleTypeSelect = row.querySelector('.attraction-vehicle-type');
            const vehicleType = vehicleTypeSelect?.selectedOptions[0]?.getAttribute('data-type') || 'sedan';
            const transferWay = row.querySelector('.attraction-transfer-way').value;
            const transferType = row.querySelector('.attraction-transfer-type').value;
            const guideChecked = row.querySelector('.attraction-guide-checkbox').checked;
            const guideSelect = row.querySelector('.attraction-guide-select');
            const guideId = guideSelect?.value || '';
            
            // Validate: if guide checkbox is checked, guide must be selected
            if (guideChecked && !guideId) {
                alert(`Please select a guide for ${attractionName}`);
                return;
            }
            
            // Parse charges
            const adultCost = parseFloat(adultCharge.replace(/[^0-9.]/g, '')) || 0;
            const adultSell = adultCost;
            const childCost = parseFloat(childCharge.replace(/[^0-9.]/g, '')) || 0;
            const childSell = childCost;
            const infantCost = parseFloat(infantCharge.replace(/[^0-9.]/g, '')) || 0;
            const infantSell = infantCost;
            
            // Create tour ID first
            const tourId = generateId('tour');
            
            // Create transfer info if needed
            let transferInfo = null;
            let transferId = null;
            
            if (transferChecked && transferDestination) {
                transferId = generateId('transfer');
                
                // Get destination name from the select option
                const destOption = transferDestinationSelect.options[transferDestinationSelect.selectedIndex];
                const destinationName = destOption.getAttribute('data-name') || destOption.text;
                const destinationType = destOption.getAttribute('data-type') || 'other';
                
                transferInfo = {
                    id: transferId,
                    type: transferType === 'P' ? 'P' : 'S',
                    way: transferWay || 'both-way',
                    vehicleType: vehicleType,
                    cost: 0,
                    sell: 0,
                    service: `${attractionName} / ${destinationName}`,
                    attractionName: attractionName,
                    destination: destinationName,
                    destinationType: destinationType,
                    dateTime: dateTime,
                    adults: adultsQty,
                    child: childQty,
                    taxIncluded: true,
                    isStandalone: false,
                    sourceType: 'tour',
                    sourceId: tourId
                };
                
                transferList.push(transferInfo);
            }
            
            // Get guide info if selected
            let guideInfo = null;
            let guideName = '';
            let guideLanguages = '';
            let guideEntryId = null;
            
            if (guideChecked && guideId) {
                const guideOption = guideSelect.options[guideSelect.selectedIndex];
                guideName = guideOption.getAttribute('data-name') || guideOption.text;
                guideLanguages = guideOption.getAttribute('data-languages') || '';
                
                guideInfo = {
                    guide_id: guideId,
                    name: guideName,
                    languages: guideLanguages
                };
                
                // Add guide to guide list
                guideEntryId = generateId('guide');
                const guideEntry = {
                    id: guideEntryId,
                    guide_id: guideId,
                    dateTime: dateTime,
                    tourName: `${attractionName} - ${guideName}`,
                    languages: guideLanguages,
                    name: guideName,
                    hours: 0,
                    cost: 0,
                    sell: 0,
                    isStandalone: false  // Mark as linked to attraction
                };
                
                guideList.push(guideEntry);
            }
            
            // Create tour data using the pre-generated tourId
            const tourData = {
                id: tourId,
                destination: document.getElementById('tourDestination').value || 'Singapore',
                attractionId: attrId,
                attractionName: attractionName,
                dateTime: dateTime,
                adultsQty: adultsQty,
                adultCost: adultCost,
                adultSell: adultSell,
                childQty: childQty,
                childCost: childCost,
                childSell: childSell,
                infantQty: infantQty,
                infantCost: infantCost,
                infantSell: infantSell,
                transferId: transferId,
                transferInfo: transferInfo,
                guideId: guideEntryId,
                guideInfo: guideInfo,
                guideRequired: guideChecked
            };
            
            tourList.push(tourData);
            console.log('Pushed tour to list:', tourData.attractionName, tourData.dateTime);
        });
        
        console.log('Total tours in list after adding:', tourList.length);
        
        // Update tables
        updateTourTable();
        updateTransferTable();
        updateGuideTable();
        recalculateTotals();
        
        // Close modal
        const tourModal = bootstrap.Modal.getInstance(document.getElementById('tourModal'));
        tourModal.hide();
        
        // Reset checkboxes
        document.getElementById('selectAllAttractions').checked = false;
        document.querySelectorAll('.attraction-checkbox').forEach(cb => cb.checked = false);
        
        // Update header dates AFTER modal closes (with delay to ensure modal animation completes)
        setTimeout(() => {
            console.log('About to call updateHeaderDatesIfNeeded with:', dateTime);
            updateHeaderDatesIfNeeded(dateTime);
            console.log('updateHeaderDatesIfNeeded completed after modal close');
        }, 300);
    }
    
    // Add another attraction (keep modal open)
    function addAnotherAttraction() {
        // Save current selections without closing
        const selectedRows = document.querySelectorAll('.attraction-checkbox:checked');
        
        if (selectedRows.length === 0) {
            alert('Please select at least one attraction');
            return;
        }
        
        // Process selected attractions (same logic as saveAndCloseAttractions)
        saveAndCloseAttractions();
        
        // Reopen the modal
        const tourModal = new bootstrap.Modal(document.getElementById('tourModal'));
        tourModal.show();
    }
    
    // Save tour
    function saveTour() {
        const destination = document.getElementById('tourDestination').value;
        const attractionSelect = document.getElementById('attractionSelect');
        const attractionId = attractionSelect.value;
        const attractionName = attractionSelect.options[attractionSelect.selectedIndex]?.text || '';
        const dateTime = document.getElementById('tourDateTime').value;
        const pte = document.getElementById('tourPTE').checked;
        const adultsQty = parseInt(document.getElementById('tourAdultsQty').value);
        const adultCost = parseFloat(document.getElementById('tourAdultCost').value);
        const adultSell = parseFloat(document.getElementById('tourAdultSell').value);
        const childQty = parseInt(document.getElementById('tourChildQty').value);
        const childCost = parseFloat(document.getElementById('tourChildCost').value);
        const childSell = parseFloat(document.getElementById('tourChildSell').value);
        
        console.log('Tour/Attraction DateTime:', dateTime);
        console.log('Attraction ID:', attractionId);
        console.log('Attraction Name:', attractionName);
        
        if (!attractionId || !dateTime) {
            alert('Please select attraction and date/time');
            return;
        }
        
        const isEditing = window.editingTourIndex !== undefined && window.editingTourIndex !== null;
        
        // Get transfer info
        const transferRequired = document.getElementById('transferRequired').value === 'yes';
        let transferInfo = null;
        let transferId = null;
        
        if (transferRequired) {
            const transferType = document.getElementById('transferType').value;
            const transferWay = document.getElementById('transferWay').value;
            const vehicleType = document.getElementById('vehicleType').value;
            const transferCost = parseFloat(document.getElementById('transferCost').value);
            const transferSell = parseFloat(document.getElementById('transferSell').value);
            
            transferId = generateId('transfer');
            transferInfo = {
                id: transferId,
                type: transferType,
                way: transferWay,
                vehicleType: vehicleType,
                cost: transferCost,
                sell: transferSell,
                destination: attractionName,
                dateTime: dateTime,
                adults: adultsQty,
                adultsQty: adultsQty,
                child: childQty,
                childQty: childQty,
                taxIncluded: true,
                isStandalone: false
            };
            
            // Add to transfer list
            transferList.push(transferInfo);
        }
        
        // Get guide info
        const guideRequired = document.getElementById('guideRequired').value === 'yes';
        let guideInfo = null;
        let guideId = null;
        
        if (guideRequired) {
            const language = document.getElementById('guideLanguage').value;
            const guideName = document.getElementById('guideName').value;
            const hours = parseFloat(document.getElementById('guideHours').value);
            const guideCost = parseFloat(document.getElementById('guideCost').value);
            const guideSell = parseFloat(document.getElementById('guideSell').value);
            
            guideId = generateId('guide');
            guideInfo = {
                id: guideId,
                language: language,
                name: guideName,
                hours: hours,
                cost: guideCost,
                sell: guideSell,
                tourName: attractionName,
                dateTime: dateTime,
                isStandalone: false
            };
            
            // Add to guide list
            guideList.push(guideInfo);
        }
        
    const tourData = {
        id: generateId('tour'),
            destination: destination,
            attractionId: attractionId,
            attractionName: attractionName,
            dateTime: dateTime,
            pte: pte,
            adultsQty: adultsQty,
            adultCost: adultCost,
            adultSell: adultSell,
            childQty: childQty,
            childCost: childCost,
            childSell: childSell,
            transferId: transferId,
            transferInfo: transferInfo,
            guideId: guideId,
            guideInfo: guideInfo
        };
        
        // Check if editing
        if (isEditing) {
            // Remove old transfer and guide if exists
            const oldTour = tourList[window.editingTourIndex];
            if (oldTour.transferId) {
                transferList = transferList.filter(t => t.id !== oldTour.transferId);
            }
            if (oldTour.guideId) {
                guideList = guideList.filter(g => g.id !== oldTour.guideId);
            }
            
            tourList[window.editingTourIndex] = tourData;
            window.editingTourIndex = null;
        } else {
            tourList.push(tourData);
        }
        
        // Update header dates
        if (isEditing) {
            // When editing, recalculate from all services to handle date changes properly
            recalculateHeaderDatesFromServices();
        } else {
            // When adding new, just expand if needed
            console.log('Calling expandHeaderDatesIfNeeded for TOUR...');
            expandHeaderDatesIfNeeded(dateTime, true);
        }
        
        updateTourTable();
        updateGuideTable();
        updateTransferTable();
        recalculateTotals();
        
        // Close modal
        const tourModal = bootstrap.Modal.getInstance(document.getElementById('tourModal'));
        tourModal.hide();
    }
    
    // Update tour table
    function updateTourTable() {
        const tbody = document.getElementById('tourTableBody');
        const table = document.getElementById('tourTable');
        const emptyMessage = document.getElementById('emptyTourMessage');
        
        if (tourList.length === 0) {
            table.style.display = 'none';
            emptyMessage.style.display = 'block';
            return;
        }
        
        table.style.display = 'table';
        emptyMessage.style.display = 'none';
        
        tbody.innerHTML = tourList.map((tour, index) => {
            // Ensure dateTime has time component, if not add default time 10:00
            let dateTimeValue = tour.dateTime || '';
            if (dateTimeValue && !dateTimeValue.includes('T')) {
                dateTimeValue = dateTimeValue + 'T10:00';
            }
            
            return `
            <tr>
                <td><input type="checkbox" class="tour-checkbox" value="${tour.id}"></td>
                <td>
                    <input type="datetime-local" value="${dateTimeValue}" onchange="updateTourField(${index}, 'dateTime', this.value)" style="width: 180px; font-size: 10px;">
                </td>
                <td>
                    <a href="javascript:void(0)" onclick="editTour(${index})" style="color: #0d6efd; text-decoration: underline; cursor: pointer;">
                        ${tour.attractionName}
                    </a>
                </td>
                <td><input type="number" value="${tour.adultsQty}" onchange="updateTourField(${index}, 'adultsQty', this.value)"></td>
                <td><input type="number" value="${tour.adultCost}" onchange="updateTourField(${index}, 'adultCost', this.value)" step="0.01"></td>
                <td><input type="number" value="${tour.adultSell}" onchange="updateTourField(${index}, 'adultSell', this.value)" step="0.01"></td>
                <td><input type="number" value="${tour.childQty}" onchange="updateTourField(${index}, 'childQty', this.value)"></td>
                <td><input type="number" value="${tour.childCost}" onchange="updateTourField(${index}, 'childCost', this.value)" step="0.01"></td>
                <td><input type="number" value="${tour.childSell}" onchange="updateTourField(${index}, 'childSell', this.value)" step="0.01"></td>
            </tr>
        `;
        }).join('');
    }
    
    // Edit tour
    function editTour(index) {
        const tour = tourList[index];
        if (!tour) return;
        
        console.log('Editing tour:', tour);
        console.log('Guide info:', tour.guideInfo);
        
        window.editingTourIndex = index;
        
        // Set the destination and date
        document.getElementById('tourDestination').value = tour.destination;
        const normalizedDate = normalizeDateToYYYYMMDD(tour.dateTime);
        document.getElementById('tourDateTime').value = normalizedDate || '';
        
        // Load attractions for the destination
        loadAttractionsByDestination();
        
        // After attractions load, find and check the matching attraction
        setTimeout(() => {
            const attractionRows = document.querySelectorAll('.attraction-row');
            attractionRows.forEach(row => {
                const attrId = row.getAttribute('data-attraction-id');
                if (attrId == tour.attractionId) {
                    // Check the checkbox
                    const checkbox = row.querySelector('.attraction-checkbox');
                    if (checkbox) checkbox.checked = true;
                    
                    // Populate the values
                    const adultQty = row.querySelector('.attraction-adult-qty');
                    if (adultQty) adultQty.value = tour.adultsQty || 0;
                    
                    const adultCharge = row.querySelector('.attraction-adult-charge');
                    if (adultCharge) adultCharge.value = `SGD ${parseFloat(tour.adultCost || 0).toFixed(2)}`;
                    
                    const childQty = row.querySelector('.attraction-child-qty');
                    if (childQty) childQty.value = tour.childQty || 0;
                    
                    const childCharge = row.querySelector('.attraction-child-charge');
                    if (childCharge) childCharge.value = `SGD ${parseFloat(tour.childCost || 0).toFixed(2)}`;
                    
                    const infantQty = row.querySelector('.attraction-infant-qty');
                    if (infantQty) infantQty.value = tour.infantQty || 0;
                    
                    const infantCharge = row.querySelector('.attraction-infant-charge');
                    if (infantCharge) infantCharge.value = `SGD ${parseFloat(tour.infantCost || 0).toFixed(2)}`;
                    
                    // Populate transfer info if available
                    if (tour.transferInfo) {
                        const transferCheckbox = row.querySelector('.attraction-transfer-checkbox');
                        if (transferCheckbox) transferCheckbox.checked = true;
                        
                        // Set transfer destination
                        if (tour.transferInfo.destination) {
                            const transferDestSelect = row.querySelector('.attraction-transfer-destination');
                            if (transferDestSelect) {
                                // Find the option that matches the destination name
                                for (let i = 0; i < transferDestSelect.options.length; i++) {
                                    const optionName = transferDestSelect.options[i].getAttribute('data-name');
                                    if (optionName === tour.transferInfo.destination) {
                                        transferDestSelect.value = transferDestSelect.options[i].value;
                                        break;
                                    }
                                }
                            }
                        }
                        
                        // Set vehicle type
                        const vehicleTypeSelect = row.querySelector('.attraction-vehicle-type');
                        if (vehicleTypeSelect && tour.transferInfo.vehicleType) {
                            vehicleTypeSelect.value = tour.transferInfo.vehicleType;
                        }
                        
                        // Set way
                        const waySelect = row.querySelector('.attraction-transfer-way');
                        if (waySelect && tour.transferInfo.way) {
                            waySelect.value = tour.transferInfo.way;
                        }
                        
                        // Set transfer type
                        const typeSelect = row.querySelector('.attraction-transfer-type');
                        if (typeSelect && tour.transferInfo.type) {
                            typeSelect.value = tour.transferInfo.type;
                        }
                    }
                    
                    // Populate guide info if available
                    if (tour.guideRequired || tour.guideInfo) {
                        const guideCheckbox = row.querySelector('.attraction-guide-checkbox');
                        if (guideCheckbox) guideCheckbox.checked = true;
                        
                        if (tour.guideInfo && tour.guideInfo.guide_id) {
                            const guideSelect = row.querySelector('.attraction-guide-select');
                            if (guideSelect) {
                                console.log('Setting guide select to:', tour.guideInfo.guide_id);
                                guideSelect.value = tour.guideInfo.guide_id;
                            }
                        }
                    }
                }
            });
        }, 500);
        
        // Change modal title and button text
        document.getElementById('tourModalTitleText').textContent = 'Edit Tour / Attraction';
        
        // Open modal
        const tourModal = new bootstrap.Modal(document.getElementById('tourModal'));
        tourModal.show();
    }
    
    // Update tour field
    function updateTourField(index, field, value) {
        if (tourList[index]) {
            tourList[index][field] = value;
        }
    }
    
    // Toggle select all tours
    function toggleSelectAllTours() {
        const selectAll = document.getElementById('selectAllTours');
        const checkboxes = document.querySelectorAll('.tour-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
    }
    
    // Remove selected tours
    function removeSelectedTours() {
        const checkboxes = document.querySelectorAll('.tour-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Please select tours to remove');
            return;
        }
        
        if (!confirm(`Remove ${checkboxes.length} selected tour(s)?`)) {
            return;
        }
        
        const idsToRemove = Array.from(checkboxes).map(cb => cb.value);
        
        // Also remove associated transfers and guides
        tourList.forEach(tour => {
            if (idsToRemove.includes(String(tour.id))) {
                if (tour.transferId) {
                    transferList = transferList.filter(t => String(t.id) !== String(tour.transferId));
                }
                if (tour.guideId) {
                    guideList = guideList.filter(g => String(g.id) !== String(tour.guideId));
                }
            }
        });
        
        tourList = tourList.filter(tour => !idsToRemove.includes(String(tour.id)));
        
        updateTourTable();
        updateTransferTable();
        updateGuideTable();
        recalculateHeaderDatesFromServices();
        recalculateTotals();
    }
    
    // ==================== GUIDE FUNCTIONS ====================
    
    // Open Guide Modal
    function openGuideModal() {
        // Reset destination select
        const destinationSelect = document.getElementById('guideDestination');
        if (destinationSelect) {
            destinationSelect.value = '';
        }
        
        // Set default date
        const guideDate = document.getElementById('guideDate');
        if (guideDate) {
            guideDate.value = getDefaultServiceDate();
        }
        
        // Clear guides table
        const tbody = document.getElementById('guidesTableBody');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted" style="padding: 20px;">Please select a destination to load guides</td></tr>';
        }
        
        // Reset all checkboxes in the table
        const selectAllCheckbox = document.getElementById('selectAllGuides');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
        }
        
        // Set date range constraints
        if (typeof updateAllServiceDateRanges === 'function') {
            updateAllServiceDateRanges();
        }
        
        // Auto-fill destination from header using autoFillModalFields
        autoFillModalFields('guide');
        
        const guideModal = new bootstrap.Modal(document.getElementById('guideModal'));
        guideModal.show();
    }
    
    // Load guides by destination
    function loadGuidesByDestination() {
        const destination = document.getElementById('guideDestination').value;
        const tbody = document.getElementById('guidesTableBody');
        
        if (!destination) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted" style="padding: 20px;">Please select a destination to load guides</td></tr>';
            return;
        }
        
        // Show loading state
        tbody.innerHTML = '<tr><td colspan="6" class="text-center" style="padding: 20px;"><i class="ri-loader-4-line ri-spin"></i> Loading guides...</td></tr>';
        
        console.log('Loading guides for destination:', destination);
        
        // Make AJAX call to get guides by destination
        fetch(`{{ route('enquiry-form-pro.get-guides') }}?destination=${encodeURIComponent(destination)}`)
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Guides data received:', data);
                if (data.success && data.guides.length > 0) {
                    // Build table rows
                    let html = '';
                    data.guides.forEach(guide => {
                        // Get languages as comma-separated string with proficiency
                        const languagesDisplay = guide.languages.map(l => `${l.language} (${l.proficiency})`).join(', ') || 'N/A';
                        // Get first language for saving (or all languages as comma-separated)
                        const languagesForSave = guide.languages.map(l => l.language).join(', ') || 'N/A';
                        
                        // Parse prices as numbers
                        const twoHourPrice = parseFloat(guide.two_hour_price) || 0;
                        const fourHourPrice = parseFloat(guide.four_hour_price) || 0;
                        const sixHourPrice = parseFloat(guide.six_hour_price) || 0;
                        const eightHourPrice = parseFloat(guide.eight_hour_price) || 0;
                        const tenHourPrice = parseFloat(guide.ten_hour_price) || 0;
                        const twelveHourPrice = parseFloat(guide.twelve_hour_price) || parseFloat(guide.day_rate) || 0;
                        
                        html += `
                            <tr class="guide-row" data-guide-id="${guide.guide_id}" data-guide-name="${guide.name}" data-guide-languages="${languagesForSave}">
                                <td style="padding: 2px 8px; text-align: center;">
                                    <input type="checkbox" class="guide-checkbox" data-guide-id="${guide.guide_id}">
                                </td>
                                <td style="padding: 2px 8px;">${guide.name}</td>
                                <td style="padding: 2px 8px;">
                                    <span class="guide-languages-display" style="font-size: 10px; color: #495057;">
                                        ${languagesDisplay}
                                    </span>
                                </td>
                                <td style="padding: 2px 8px; text-align: center;">
                                    <select class="form-select form-select-sm guide-hours" data-guide-id="${guide.guide_id}" 
                                            onchange="updateGuidePricing(${guide.guide_id}, ${twoHourPrice}, ${fourHourPrice}, ${sixHourPrice}, ${eightHourPrice}, ${tenHourPrice}, ${twelveHourPrice})" 
                                            style="font-size: 10px; padding: 2px 4px;">
                                        <option value="2">2 Hours</option>
                                        <option value="4">4 Hours</option>
                                        <option value="6">6 Hours</option>
                                        <option value="8">8 Hours</option>
                                        <option value="10">10 Hours</option>
                                        <option value="12" selected>12 Hours</option>
                                    </select>
                                </td>
                                <td style="padding: 2px 8px; text-align: right;">
                                    <input type="text" class="form-control form-control-sm guide-cost" data-guide-id="${guide.guide_id}" 
                                           value="${twelveHourPrice.toFixed(2)}" readonly
                                           style="font-size: 10px; padding: 2px 4px; text-align: right; background: #f8f9fa;">
                                </td>
                                <td style="padding: 2px 8px; text-align: right;">
                                    <input type="number" class="form-control form-control-sm guide-sell" data-guide-id="${guide.guide_id}" 
                                           value="${twelveHourPrice.toFixed(2)}" step="0.01"
                                           style="font-size: 10px; padding: 2px 4px; text-align: right;">
                                </td>
                            </tr>
                        `;
                    });
                    tbody.innerHTML = html;
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted" style="padding: 20px;">No guides found for this destination</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading guides:', error);
                tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger" style="padding: 20px;">
                    Error loading guides: ${error.message}<br>
                    <small>Please check the browser console for more details.</small>
                </td></tr>`;
            });
    }
    
    // Update guide pricing based on hours selected
    function updateGuidePricing(guideId, twoHourPrice, fourHourPrice, sixHourPrice, eightHourPrice, tenHourPrice, twelveHourPrice) {
        const hoursSelect = document.querySelector(`.guide-hours[data-guide-id="${guideId}"]`);
        const costInput = document.querySelector(`.guide-cost[data-guide-id="${guideId}"]`);
        const sellInput = document.querySelector(`.guide-sell[data-guide-id="${guideId}"]`);
        
        if (!hoursSelect || !costInput || !sellInput) return;
        
        const hours = parseInt(hoursSelect.value);
        let price = 0;
        
        switch(hours) {
            case 2: price = twoHourPrice || 0; break;
            case 4: price = fourHourPrice || 0; break;
            case 6: price = sixHourPrice || 0; break;
            case 8: price = eightHourPrice || 0; break;
            case 10: price = tenHourPrice || 0; break;
            case 12: price = twelveHourPrice || 0; break;
            default: price = twelveHourPrice || 0;
        }
        
        costInput.value = price.toFixed(2);
        sellInput.value = price.toFixed(2);
    }
    
    // Toggle select all guides
    function toggleSelectAllGuides() {
        const selectAll = document.getElementById('selectAllGuides');
        const checkboxes = document.querySelectorAll('.guide-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
    }
    
    // Save and close guides
    function saveAndCloseGuides() {
        const selectedRows = document.querySelectorAll('.guide-checkbox:checked');
        
        if (selectedRows.length === 0) {
            alert('Please select at least one guide');
            return;
        }
        
        const dateInput = document.getElementById('guideDate');
        const dateTime = dateInput?.value || getDefaultServiceDate();
        
        console.log('=== saveAndCloseGuides called ===');
        console.log('Date input value:', dateTime);
        console.log('Selected rows:', selectedRows.length);
        
        selectedRows.forEach(checkbox => {
            const guideId = checkbox.getAttribute('data-guide-id');
            const row = checkbox.closest('tr');
            const guideName = row.querySelector('td:nth-child(2)').textContent;
            const languages = row.getAttribute('data-guide-languages') || 'N/A';
            const hoursSelect = row.querySelector(`.guide-hours[data-guide-id="${guideId}"]`);
            const costInput = row.querySelector(`.guide-cost[data-guide-id="${guideId}"]`);
            const sellInput = row.querySelector(`.guide-sell[data-guide-id="${guideId}"]`);
            
            const hours = parseFloat(hoursSelect?.value || 12);
            const cost = parseFloat(costInput?.value || 0);
            const sell = parseFloat(sellInput?.value || 0);
            
            const guideData = {
                id: generateId('guide'),
                dateTime: dateTime,
                tourName: `Guide Service - ${guideName}`,
                language: languages,
                name: guideName,
                hours: hours,
                cost: cost,
                sell: sell,
                isStandalone: true,
                guideId: guideId
            };
            
            console.log('Adding guide:', guideData);
            guideList.push(guideData);
        });
        
        // Update header dates
        updateHeaderDatesIfNeeded(dateTime);
        
        // Update guide table
        updateGuideTable();
        
        // Close modal
        const guideModal = bootstrap.Modal.getInstance(document.getElementById('guideModal'));
        guideModal.hide();
        
        // Reset checkboxes
        document.getElementById('selectAllGuides').checked = false;
        document.querySelectorAll('.guide-checkbox').forEach(cb => cb.checked = false);
        
        // Update header dates AFTER modal closes (with delay to ensure modal animation completes)
        setTimeout(() => {
            recalculateHeaderDatesFromServices();
            recalculateTotals();
        }, 300);
    }
    
    // Add another guide (keeps modal open)
    function addAnotherGuide() {
        const selectedRows = document.querySelectorAll('.guide-checkbox:checked');
        
        if (selectedRows.length === 0) {
            alert('Please select at least one guide');
            return;
        }
        
        const dateInput = document.getElementById('guideDate');
        const dateTime = dateInput?.value || getDefaultServiceDate();
        
        selectedRows.forEach(checkbox => {
            const guideId = checkbox.getAttribute('data-guide-id');
            const row = checkbox.closest('tr');
            const guideName = row.querySelector('td:nth-child(2)').textContent;
            const languages = row.getAttribute('data-guide-languages') || 'N/A';
            const hoursSelect = row.querySelector(`.guide-hours[data-guide-id="${guideId}"]`);
            const costInput = row.querySelector(`.guide-cost[data-guide-id="${guideId}"]`);
            const sellInput = row.querySelector(`.guide-sell[data-guide-id="${guideId}"]`);
            
            const hours = parseFloat(hoursSelect?.value || 12);
            const cost = parseFloat(costInput?.value || 0);
            const sell = parseFloat(sellInput?.value || 0);
            
            const guideData = {
                id: generateId('guide'),
                dateTime: dateTime,
                tourName: `Guide Service - ${guideName}`,
                language: languages,
                name: guideName,
                hours: hours,
                cost: cost,
                sell: sell,
                isStandalone: true,
                guideId: guideId
            };
            
            guideList.push(guideData);
            checkbox.checked = false;
        });
        
        // Update header dates
        updateHeaderDatesIfNeeded(dateTime);
        
        // Update guide table
        updateGuideTable();
        
        // Recalculate
        recalculateHeaderDatesFromServices();
        recalculateTotals();
        
        // Reset select all
        document.getElementById('selectAllGuides').checked = false;
    }
    
    // Update guide table
    function updateGuideTable() {
        const tbody = document.getElementById('guideTableBody');
        const table = document.getElementById('guideTable');
        const emptyMessage = document.getElementById('emptyGuideMessage');
        
        if (guideList.length === 0) {
            table.style.display = 'none';
            emptyMessage.style.display = 'block';
            return;
        }
        
        table.style.display = 'table';
        emptyMessage.style.display = 'none';
        
        tbody.innerHTML = guideList.map((guide, index) => {
            // Ensure dateTime has time component, if not add default time 09:00
            let dateTimeValue = guide.dateTime || '';
            if (dateTimeValue && !dateTimeValue.includes('T')) {
                dateTimeValue = dateTimeValue + 'T09:00';
            }

            // Determine checkbox display - show "Linked" for guides from attractions
            const checkboxHtml = guide.isStandalone !== false
                ? `<input type="checkbox" class="guide-checkbox" value="${guide.guide_id || guide.id}">`
                : `<span style="font-size: 10px; color: #6c757d; font-style: italic;">Linked</span>`;
            
            // Date and time is now editable in guide table
            const dateHtml = `<input type="datetime-local" value="${dateTimeValue}" onchange="updateGuideField(${index}, 'dateTime', this.value)" style="width: 180px;">`;

            return `
            <tr>
                <td>${checkboxHtml}</td>
                <td>${dateHtml}</td>
                <td>
                    ${guide.tourName}
                </td>
                <td>${guide.languages || guide.language || ''}</td>
                <td><input type="text" value="${guide.name || ''}" onchange="updateGuideField(${index}, 'name', this.value)" style="width: 100px;"></td>
                <td><input type="number" value="${guide.hours}" onchange="updateGuideField(${index}, 'hours', this.value)" step="0.5" style="width: 60px;"></td>
                <td><input type="number" value="${guide.cost}" onchange="updateGuideField(${index}, 'cost', this.value)" step="0.01"></td>
                <td><input type="number" value="${guide.sell}" onchange="updateGuideField(${index}, 'sell', this.value)" step="0.01"></td>
            </tr>
        `;
        }).join('');
    }
    
    // Edit guide (for inline editing from table)
    function editGuide(index) {
        const guide = guideList[index];
        if (!guide) return;
        
        // If it's linked to a tour, don't allow standalone edit
        if (guide.isStandalone === false) {
            alert('This guide is linked to a tour. Please edit the associated tour to modify guide details.');
            return;
        }
        
        // All fields are now editable inline in the table
        // Focus on the date field for the selected guide
        setTimeout(() => {
            const dateInput = document.querySelector(`#guideTableBody tr:nth-child(${index + 1}) input[type="date"]`);
            if (dateInput) {
                dateInput.focus();
            }
        }, 100);
    }
    
    // Remove selected guides
    function removeSelectedGuides() {
        const checkboxes = document.querySelectorAll('.guide-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Please select guides to remove');
            return;
        }
        
        if (!confirm(`Remove ${checkboxes.length} selected guide(s)?`)) {
            return;
        }
        
        const idsToRemove = Array.from(checkboxes).map(cb => cb.value);
        guideList = guideList.filter(guide => !idsToRemove.includes(String(guide.id)));
        
        updateGuideTable();
        recalculateHeaderDatesFromServices();
        recalculateTotals();
    }
    
    // Update guide field
    function updateGuideField(index, field, value) {
        if (guideList[index]) {
            const guide = guideList[index];
            guide[field] = value;
            
            // If dateTime field is changed, update linked tour date and expand header dates
            if (field === 'dateTime' && value) {
                // Always expand header dates
                expandHeaderDatesIfNeeded(value, false);
                
                // If guide is linked to a tour, update the tour date as well
                if (guide.isStandalone === false) {
                    // Find the tour that has this guide
                    const tourIndex = tourList.findIndex(tour => tour.guideId === guide.id);
                    if (tourIndex !== -1) {
                        tourList[tourIndex].dateTime = value;
                        updateTourTable();
                        
                        // Also update linked transfer if exists
                        if (tourList[tourIndex].transferId) {
                            const transferIndex = transferList.findIndex(t => t.id === tourList[tourIndex].transferId);
                            if (transferIndex !== -1) {
                                transferList[transferIndex].dateTime = value;
                                updateTransferTable();
                            }
                        }
                        console.log('Updated linked tour and transfer dates to:', value);
                    }
                }
                
                // Refresh guide table
                updateGuideTable();
            }
        }
    }

    // ==================== MISCELLANEOUS FUNCTIONS ====================
    
    // Open Miscellaneous Modal
    function openMiscModal() {
        console.log('========================================');
        console.log('openMiscModal() called');
        console.log('========================================');
        
        try {
            window.editingMiscIndex = null;
            
            // Set default date
            const dateInput = document.getElementById('miscDate');
            if (dateInput) {
                const defaultDate = getDefaultServiceDate();
                dateInput.value = defaultDate;
            }
            
            // Reset destination
            const destinationSelect = document.getElementById('miscDestination');
            if (destinationSelect) {
                destinationSelect.value = '';
            }
            
            // Clear items table
            const itemsTableBody = document.getElementById('miscItemsTableBody');
            if (itemsTableBody) {
                itemsTableBody.innerHTML = '<tr><td colspan="8" class="text-center text-muted" style="padding: 20px;">Please select a destination to load miscellaneous items</td></tr>';
            }
            
            // Reset checkboxes
            document.querySelectorAll('.misc-item-checkbox').forEach(cb => cb.checked = false);
            
            // Update modal title for ADD mode
            document.getElementById('miscModalTitleText').textContent = 'Add Miscellaneous Items';
            const saveMiscBtn = document.getElementById('saveMiscBtnText');
            if (saveMiscBtn) saveMiscBtn.textContent = 'Save & Close';
            
            // Auto-fill fields from header
            autoFillModalFields('misc');
            
            const miscModal = new bootstrap.Modal(document.getElementById('miscModal'));
            miscModal.show();
            
            console.log('openMiscModal() completed successfully');
        } catch (error) {
            console.error('Error in openMiscModal():', error);
            alert('Error opening miscellaneous modal: ' + error.message);
        }
    }
    
    // Load miscellaneous items by destination (from API based on DMC)
    function loadMiscItemsByDestination() {
        const destination = document.getElementById('miscDestination').value;
        const itemsTableBody = document.getElementById('miscItemsTableBody');
        
        if (!destination) {
            itemsTableBody.innerHTML = '<tr><td colspan="8" class="text-center text-muted" style="padding: 20px;">Please select a destination to load miscellaneous items</td></tr>';
            return;
        }
        
        // Show loading state
        itemsTableBody.innerHTML = '<tr><td colspan="8" class="text-center" style="padding: 20px;"><i class="ri-loader-4-line ri-spin me-2"></i>Loading miscellaneous items...</td></tr>';
        
        // Get DMC ID from the form or session
        const dmcId = '{{ $dmc_id ?? "" }}';
        
        console.log('DMC ID from backend:', dmcId);
        console.log('User role_id:', '{{ auth()->user()->role_id ?? "N/A" }}');
        console.log('User userId:', '{{ auth()->user()->userId ?? "N/A" }}');
        console.log('User created_by:', '{{ auth()->user()->created_by ?? "N/A" }}');
        
        if (!dmcId || dmcId === '') {
            itemsTableBody.innerHTML = '<tr><td colspan="8" class="text-center text-danger" style="padding: 20px;"><i class="ri-error-warning-line me-2"></i>DMC ID not found. Please contact support.<br><small class="text-muted">Role: {{ auth()->user()->role_id ?? "N/A" }}, User ID: {{ auth()->user()->userId ?? "N/A" }}</small></td></tr>';
            console.error('DMC ID not available. User role:', '{{ auth()->user()->role_id ?? "N/A" }}');
            return;
        }
        
        // Fetch items from API
        fetch(`{{ url('/api/miscellaneous/dmc') }}/${dmcId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load items');
                }
                return response.json();
            })
            .then(items => {
                console.log('Loaded miscellaneous items:', items);
                
                if (!items || items.length === 0) {
                    itemsTableBody.innerHTML = '<tr><td colspan="8" class="text-center text-muted" style="padding: 20px;">No miscellaneous items available. Please configure items in the DMC panel.</td></tr>';
                    return;
                }
                
                // Render items
                itemsTableBody.innerHTML = items.map(item => `
                    <tr class="misc-item-row" data-item-id="misc_${item.mis_id}" data-item-name="${item.item_name}" data-mis-id="${item.mis_id}">
                        <td style="padding: 2px 8px; text-align: center;">
                            <input type="checkbox" class="misc-item-checkbox" data-item-id="misc_${item.mis_id}">
                        </td>
                        <td style="padding: 2px 8px;">
                            ${item.image ? `<img src="${item.image}" alt="${item.item_name}" style="width: 20px; height: 20px; object-fit: cover; border-radius: 3px; margin-right: 5px;">` : ''}
                            <strong>${item.item_name}</strong>
                            ${item.description ? `<br><small class="text-muted">${item.description}</small>` : ''}
                        </td>
                        <td style="padding: 2px 8px;">
                            <input type="number" class="form-control form-control-sm misc-adult-qty" data-item-id="misc_${item.mis_id}" value="0" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                        </td>
                        <td style="padding: 2px 8px;">
                            <input type="text" class="form-control form-control-sm misc-adult-charge" data-item-id="misc_${item.mis_id}" value="SGD ${parseFloat(item.adult_price || 0).toFixed(2)}" style="font-size: 10px; padding: 2px 4px;">
                        </td>
                        <td style="padding: 2px 8px;">
                            <input type="number" class="form-control form-control-sm misc-child-qty" data-item-id="misc_${item.mis_id}" value="0" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                        </td>
                        <td style="padding: 2px 8px;">
                            <input type="text" class="form-control form-control-sm misc-child-charge" data-item-id="misc_${item.mis_id}" value="SGD ${parseFloat(item.child_price || 0).toFixed(2)}" style="font-size: 10px; padding: 2px 4px;">
                        </td>
                        <td style="padding: 2px 8px;">
                            <input type="number" class="form-control form-control-sm misc-infant-qty" data-item-id="misc_${item.mis_id}" value="0" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                        </td>
                        <td style="padding: 2px 8px;">
                            <input type="text" class="form-control form-control-sm misc-infant-charge" data-item-id="misc_${item.mis_id}" value="SGD ${parseFloat(item.infant_price || 0).toFixed(2)}" style="font-size: 10px; padding: 2px 4px;">
                        </td>
                    </tr>
                `).join('');
            })
            .catch(error => {
                console.error('Error loading miscellaneous items:', error);
                itemsTableBody.innerHTML = '<tr><td colspan="8" class="text-center text-danger" style="padding: 20px;"><i class="ri-error-warning-line me-2"></i>Error loading items. Please try again.</td></tr>';
            });
    }
    
    // Toggle select all miscellaneous items (in modal)
    function toggleSelectAllMiscItems() {
        const selectAll = document.getElementById('selectAllMiscItems');
        const checkboxes = document.querySelectorAll('.misc-item-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
    }
    
    // Save and close miscellaneous modal
    function saveAndCloseMisc() {
        console.log('========================================');
        console.log('saveAndCloseMisc() called');
        console.log('========================================');
        
        const selectedRows = Array.from(document.querySelectorAll('.misc-item-checkbox:checked'));
        console.log('Selected rows:', selectedRows.length);
        
        if (selectedRows.length === 0) {
            alert('Please select at least one miscellaneous item');
            return;
        }
        
        const dateTime = document.getElementById('miscDate').value;
        const destination = document.getElementById('miscDestination').value;
        console.log('Date:', dateTime, 'Destination:', destination);
        
        if (!dateTime || !destination) {
            alert('Please select date and destination');
            return;
        }
        
        // If editing, update the existing item
        if (window.editingMiscIndex !== null && window.editingMiscIndex !== undefined) {
            const checkbox = selectedRows[0]; // Only use first selected when editing
            const itemId = checkbox.getAttribute('data-item-id');
            const row = checkbox.closest('tr');
            const itemName = row.getAttribute('data-item-name');
            
            // Get values from the row
            const adultsQty = parseInt(row.querySelector('.misc-adult-qty').value) || 0;
            const adultCharge = row.querySelector('.misc-adult-charge').value || 'SGD 0.00';
            const childQty = parseInt(row.querySelector('.misc-child-qty').value) || 0;
            const childCharge = row.querySelector('.misc-child-charge').value || 'SGD 0.00';
            const infantQty = parseInt(row.querySelector('.misc-infant-qty').value) || 0;
            const infantCharge = row.querySelector('.misc-infant-charge').value || 'SGD 0.00';
            
            // Parse charges
            const adultCost = parseFloat(adultCharge.replace(/[^0-9.]/g, '')) || 0;
            const adultSell = adultCost;
            const childCost = parseFloat(childCharge.replace(/[^0-9.]/g, '')) || 0;
            const childSell = childCost;
            const infantCost = parseFloat(infantCharge.replace(/[^0-9.]/g, '')) || 0;
            const infantSell = infantCost;
            
            // Update the existing item
            miscList[window.editingMiscIndex] = {
                id: miscList[window.editingMiscIndex].id, // Keep existing ID
                itemId: itemId,
                itemName: itemName,
                destination: destination,
                dateTime: dateTime,
                adultsQty: adultsQty,
                adultCost: adultCost,
                adultSell: adultSell,
                childQty: childQty,
                childCost: childCost,
                childSell: childSell,
                infantQty: infantQty,
                infantCost: infantCost,
                infantSell: infantSell
            };
            
            window.editingMiscIndex = null;
        } else {
            // Add new items
            selectedRows.forEach(checkbox => {
                const itemId = checkbox.getAttribute('data-item-id');
                const row = checkbox.closest('tr');
                const itemName = row.getAttribute('data-item-name');
                
                // Get values from the row
                const adultsQty = parseInt(row.querySelector('.misc-adult-qty').value) || 0;
                const adultCharge = row.querySelector('.misc-adult-charge').value || 'SGD 0.00';
                const childQty = parseInt(row.querySelector('.misc-child-qty').value) || 0;
                const childCharge = row.querySelector('.misc-child-charge').value || 'SGD 0.00';
                const infantQty = parseInt(row.querySelector('.misc-infant-qty').value) || 0;
                const infantCharge = row.querySelector('.misc-infant-charge').value || 'SGD 0.00';
                
                // Parse charges
                const adultCost = parseFloat(adultCharge.replace(/[^0-9.]/g, '')) || 0;
                const adultSell = adultCost;
                const childCost = parseFloat(childCharge.replace(/[^0-9.]/g, '')) || 0;
                const childSell = childCost;
                const infantCost = parseFloat(infantCharge.replace(/[^0-9.]/g, '')) || 0;
                const infantSell = infantCost;
                
                const miscData = {
                    id: generateId('misc'),
                    itemId: itemId,
                    itemName: itemName,
                    destination: destination,
                    dateTime: dateTime,
                    adultsQty: adultsQty,
                    adultCost: adultCost,
                    adultSell: adultSell,
                    childQty: childQty,
                    childCost: childCost,
                    childSell: childSell,
                    infantQty: infantQty,
                    infantCost: infantCost,
                    infantSell: infantSell
                };
                
                miscList.push(miscData);
            });
        }
        
        updateMiscTable();
        recalculateHeaderDatesFromServices();
        recalculateTotals();
        
        const miscModal = bootstrap.Modal.getInstance(document.getElementById('miscModal'));
        miscModal.hide();
    }
    
    // Add another miscellaneous item
    function addAnotherMisc() {
        saveAndCloseMisc();
        setTimeout(() => openMiscModal(), 300);
    }
    
    // Update miscellaneous table
    function updateMiscTable() {
        const tbody = document.getElementById('miscTableBody');
        const table = document.getElementById('miscTable');
        const emptyMessage = document.getElementById('emptyMiscMessage');
        
        if (miscList.length === 0) {
            table.style.display = 'none';
            emptyMessage.style.display = 'block';
            return;
        }
        
        table.style.display = 'table';
        emptyMessage.style.display = 'none';
        
        tbody.innerHTML = miscList.map((item, index) => `
            <tr>
                <td><input type="checkbox" class="misc-checkbox" value="${item.id}"></td>
                <td><input type="datetime-local" value="${normalizeDateTimeLocal(item.dateTime)}" onchange="updateMiscField(${index}, 'dateTime', this.value)" style="width: 160px; font-size: 11px; padding: 2px 4px;"></td>
                <td>
                    <a href="javascript:void(0)" onclick="editMisc(${index})" style="color: #0d6efd; text-decoration: underline; cursor: pointer;">
                        ${item.itemName}
                    </a>
                </td>
                <td><input type="number" value="${item.adultsQty}" onchange="updateMiscField(${index}, 'adultsQty', this.value)"></td>
                <td><input type="number" value="${item.adultCost}" onchange="updateMiscField(${index}, 'adultCost', this.value)" step="0.01"></td>
                <td><input type="number" value="${item.adultSell}" onchange="updateMiscField(${index}, 'adultSell', this.value)" step="0.01"></td>
                <td><input type="number" value="${item.childQty}" onchange="updateMiscField(${index}, 'childQty', this.value)"></td>
                <td><input type="number" value="${item.childCost}" onchange="updateMiscField(${index}, 'childCost', this.value)" step="0.01"></td>
                <td><input type="number" value="${item.childSell}" onchange="updateMiscField(${index}, 'childSell', this.value)" step="0.01"></td>
                <td><input type="number" value="${item.infantQty}" onchange="updateMiscField(${index}, 'infantQty', this.value)"></td>
                <td><input type="number" value="${item.infantCost}" onchange="updateMiscField(${index}, 'infantCost', this.value)" step="0.01"></td>
                <td><input type="number" value="${item.infantSell}" onchange="updateMiscField(${index}, 'infantSell', this.value)" step="0.01"></td>
            </tr>
        `).join('');
    }
    
    // Edit miscellaneous item
    function editMisc(index) {
        const item = miscList[index];
        if (!item) return;
        
        console.log('=== EDITING MISCELLANEOUS ITEM ===');
        console.log('Index:', index);
        console.log('Item:', item);
        
        window.editingMiscIndex = index;
        
        // Set date
        const dateInput = document.getElementById('miscDate');
        if (dateInput) {
            const normalizedDate = normalizeDateToYYYYMMDD(item.dateTime);
            dateInput.value = normalizedDate || '';
        }
        
        // Set destination
        const destinationSelect = document.getElementById('miscDestination');
        if (destinationSelect && item.destination) {
            destinationSelect.value = item.destination;
        }
        
        // Load items for this destination
        loadMiscItemsByDestination();
        
        // Function to populate the form after items load
        const populateEditForm = (attempt = 1, maxAttempts = 10) => {
            console.log(`Attempt ${attempt} to populate edit form for item:`, item.itemId);
            
            // Find and check the matching item row
            const rows = Array.from(document.querySelectorAll('.misc-item-row'));
            const targetRow = rows.find(r => {
                const rowItemId = r.getAttribute('data-item-id');
                return String(item.itemId) === rowItemId;
            });
            
            if (targetRow) {
                console.log('Found target row for item:', item.itemId);
                
                // Reset all checkboxes first
                document.querySelectorAll('.misc-item-checkbox').forEach(cb => cb.checked = false);
                
                const itemId = targetRow.getAttribute('data-item-id');
                
                // Check the row
                const checkbox = targetRow.querySelector(`.misc-item-checkbox[data-item-id="${itemId}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                    console.log('Checkbox checked for item:', itemId);
                } else {
                    console.warn('Checkbox not found for item:', itemId);
                }
                
                // Fill editable fields on the row
                const setVal = (selector, value) => {
                    const el = targetRow.querySelector(`${selector}[data-item-id="${itemId}"]`);
                    if (el) el.value = value ?? el.value;
                };
                
                setVal('.misc-adult-qty', item.adultsQty);
                setVal('.misc-adult-charge', 'SGD ' + (item.adultCost || 0));
                setVal('.misc-child-qty', item.childQty);
                setVal('.misc-child-charge', 'SGD ' + (item.childCost || 0));
                setVal('.misc-infant-qty', item.infantQty);
                setVal('.misc-infant-charge', 'SGD ' + (item.infantCost || 0));
                
                // Scroll the row into view
                targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                console.warn(`Row not found for item ${item.itemId}, attempt ${attempt}/${maxAttempts}`);
                
                // Retry if items haven't loaded yet
                if (attempt < maxAttempts) {
                    setTimeout(() => populateEditForm(attempt + 1, maxAttempts), 200);
                } else {
                    console.error('Failed to find item row after', maxAttempts, 'attempts');
                }
            }
        };
        
        // Wait for items to load, then populate the form
        setTimeout(() => populateEditForm(), 500);
        
        document.getElementById('miscModalTitleText').textContent = 'Edit Miscellaneous Item';
        const saveMiscBtn = document.getElementById('saveMiscBtnText');
        if (saveMiscBtn) saveMiscBtn.textContent = 'Update Item';
        
        const miscModal = new bootstrap.Modal(document.getElementById('miscModal'));
        miscModal.show();
    }
    
    // Remove selected miscellaneous items
    function removeSelectedMisc() {
        const checkboxes = document.querySelectorAll('.misc-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Please select items to remove');
            return;
        }
        
        if (!confirm(`Remove ${checkboxes.length} selected item(s)?`)) {
            return;
        }
        
        const idsToRemove = Array.from(checkboxes).map(cb => cb.value);
        miscList = miscList.filter(item => !idsToRemove.includes(String(item.id)));
        
        updateMiscTable();
        recalculateHeaderDatesFromServices();
        recalculateTotals();
    }
    
    // Toggle select all miscellaneous items (main table)
    function toggleSelectAllMiscMain() {
        const selectAll = document.getElementById('selectAllMiscMain');
        const checkboxes = document.querySelectorAll('.misc-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
    }
    
    // Update miscellaneous field
    function updateMiscField(index, field, value) {
        if (miscList[index]) {
            miscList[index][field] = value;
            if (field === 'dateTime') {
                recalculateHeaderDatesFromServices();
            }
            recalculateTotals();
        }
    }

    // ==================== MEAL FUNCTIONS ====================
    
    // Open Meal Modal
    function openMealModal() {
        window.editingMealIndex = null;
        
        // Set default date/time
        const dateTimeInput = document.getElementById('mealDateTime');
        if (dateTimeInput) {
            const defaultDateTime = getDefaultServiceDate();
            dateTimeInput.value = defaultDateTime;
        }
        
        // Reset form fields that exist in new modal
        const destinationSelect = document.getElementById('mealDestination');
        if (destinationSelect) {
            destinationSelect.value = '';
        }
        
        // Reset restaurant select
        const restaurantSelect = document.getElementById('mealRestaurant');
        if (restaurantSelect) {
            restaurantSelect.innerHTML = '<option value="">Select Restaurant</option>';
        }
        
        // Clear the meals table
        const mealsTableBody = document.getElementById('mealsTableBody');
        if (mealsTableBody) {
            mealsTableBody.innerHTML = '<tr><td colspan="9" class="text-center text-muted" style="padding: 20px;">Select a destination and restaurant to load dishes</td></tr>';
        }
        
        // Reset all checkboxes in the table
        const selectAllCheckbox = document.getElementById('selectAllMeals');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
        }
        
        document.querySelectorAll('.meal-checkbox').forEach(cb => {
            cb.checked = false;
        });
        
        // Reset restaurant transfer section
        const restaurantTransferCheckbox = document.getElementById('restaurantTransferCheckbox');
        if (restaurantTransferCheckbox) {
            restaurantTransferCheckbox.checked = false;
        }
        
        const restaurantTransferDetailsSection = document.getElementById('restaurantTransferDetailsSection');
        if (restaurantTransferDetailsSection) {
            restaurantTransferDetailsSection.style.display = 'none';
        }
        
        // Update modal title
        const modalTitle = document.getElementById('mealModalTitleText');
        if (modalTitle) {
            modalTitle.textContent = 'Meal Details';
        }
        
        // Set date range constraints
        if (typeof updateAllServiceDateRanges === 'function') {
            updateAllServiceDateRanges();
        }
        
        // Auto-fill adults, children, infants, and country from header
        autoFillModalFields('meal');
        
        const mealModal = new bootstrap.Modal(document.getElementById('mealModal'));
        mealModal.show();
    }
    
    // Toggle restaurant transfer fields visibility
    function toggleRestaurantTransferFields() {
        const transferChecked = document.getElementById('restaurantTransferCheckbox').checked;
        const detailsSection = document.getElementById('restaurantTransferDetailsSection');
        if (detailsSection) {
            detailsSection.style.display = transferChecked ? 'block' : 'none';
        }
    }
    
    // Toggle meal transfer fields (deprecated - kept for backward compatibility)
    function toggleMealTransferFields() {
        // Check if old form elements exist (for backward compatibility)
        const mealTransferRequired = document.getElementById('mealTransferRequired');
        if (!mealTransferRequired) return; // New table-based modal doesn't use these fields
        
        const required = mealTransferRequired.value;
        const show = required === 'yes';
        
        const mealTransferTypeField = document.getElementById('mealTransferTypeField');
        const mealTransferWayField = document.getElementById('mealTransferWayField');
        const mealVehicleTypeField = document.getElementById('mealVehicleTypeField');
        const mealTransferCostFields = document.getElementById('mealTransferCostFields');
        const mealTransferSellFields = document.getElementById('mealTransferSellFields');
        
        if (mealTransferTypeField) mealTransferTypeField.style.display = show ? 'block' : 'none';
        if (mealTransferWayField) mealTransferWayField.style.display = show ? 'block' : 'none';
        if (mealVehicleTypeField) mealVehicleTypeField.style.display = show ? 'block' : 'none';
        if (mealTransferCostFields) mealTransferCostFields.style.display = show ? 'block' : 'none';
        if (mealTransferSellFields) mealTransferSellFields.style.display = show ? 'block' : 'none';
    }
    
    // Load restaurants by destination
    function loadRestaurantsByDestination() {
        const destination = document.getElementById('mealDestination').value;
        const restaurantSelect = document.getElementById('mealRestaurant');
        
        if (!restaurantSelect) return;
        
        // Get all restaurant options
        const allRestaurants = @json($restaurants);
        
        // Clear current options
        restaurantSelect.innerHTML = '<option value="">Select Restaurant</option>';
        
        // If no destination selected, don't show any restaurants
        if (!destination) {
            restaurantSelect.innerHTML = '<option value="">Select a destination first</option>';
            return;
        }
        
        // Filter restaurants based on destination (city field)
        const selectedDest = destination.toLowerCase().trim();
        const filteredRestaurants = allRestaurants.filter(restaurant => {
            // Check if restaurant's city matches the selected destination
            const restaurantCity = (restaurant.city || '').toLowerCase().trim();
            return restaurantCity === selectedDest;
        });
        
        // Add filtered restaurants to the dropdown
        if (filteredRestaurants.length === 0) {
            restaurantSelect.innerHTML = '<option value="">No restaurants available for ' + destination + '</option>';
            console.log('No restaurants found for destination: ' + destination);
            return;
        }
        
        filteredRestaurants.forEach(restaurant => {
            const option = document.createElement('option');
            option.value = restaurant.restaurant_id;
            option.textContent = restaurant.name;
            option.setAttribute('data-name', restaurant.name);
            option.setAttribute('data-city', restaurant.city);
            restaurantSelect.appendChild(option);
        });
        
        console.log(`Loaded ${filteredRestaurants.length} restaurant(s) for ${destination}`);
    }
    
    // Update meals from selected restaurant
    function updateMealsFromRestaurant() {
        const restaurantSelect = document.getElementById('mealRestaurant');
        const restaurantId = restaurantSelect.value;
        
        if (!restaurantId) {
            alert('Please select a restaurant first');
            return;
        }
        
        const restaurantName = restaurantSelect.options[restaurantSelect.selectedIndex].getAttribute('data-name');
        
        // Get all available meals
        const allMeals = @json($meals);
        
        // Filter meals for this restaurant
        const restaurantMeals = allMeals.filter(meal => meal.restaurant_id == restaurantId);
        
        if (restaurantMeals.length === 0) {
            alert('No meals/dishes found for this restaurant');
            return;
        }
        
        // Get the meals table body
        const mealsTableBody = document.getElementById('mealsTableBody');
        if (!mealsTableBody) return;
        
        // Clear existing rows
        mealsTableBody.innerHTML = '';
        
        // Count meals by type for summary
        let breakfastCount = 0, lunchCount = 0, dinnerCount = 0;
        
        // Debug: Log all meals to see their structure
        console.log('All meals from restaurant:', restaurantMeals);
        
        // Add new rows for each meal/dish
        restaurantMeals.forEach((meal, index) => {
            // Determine meal type/period
            // Database stores: 1 = breakfast, 2 = lunch, 3 = dinner
            let mealType = 'dinner'; // default
            let originalType = meal.type || meal.meal_period || '';
            
            // Convert numeric type to string
            let typeValue = String(originalType).trim();
            
            console.log(`Meal: "${meal.name}", Original Type: "${originalType}" (${typeof originalType}), Type Value: "${typeValue}"`);
            
            // Map numeric values to meal types
            if (typeValue === '1') {
                mealType = 'breakfast';
                breakfastCount++;
            } else if (typeValue === '2') {
                mealType = 'lunch';
                lunchCount++;
            } else if (typeValue === '3') {
                mealType = 'dinner';
                dinnerCount++;
            } else {
                // Fallback: Try text matching for backward compatibility
                const typeValueLower = typeValue.toLowerCase();
                if (typeValueLower.includes('breakfast') || typeValueLower.includes('bf') || typeValueLower === 'b') {
                    mealType = 'breakfast';
                    breakfastCount++;
                } else if (typeValueLower.includes('lunch') || typeValueLower === 'l') {
                    mealType = 'lunch';
                    lunchCount++;
                } else if (typeValueLower.includes('dinner') || typeValueLower === 'd') {
                    mealType = 'dinner';
                    dinnerCount++;
                } else if (typeValue === '') {
                    // If type is empty, try to guess from meal name
                    const mealNameLower = meal.name.toLowerCase();
                    if (mealNameLower.includes('breakfast') || mealNameLower.includes('morning') || mealNameLower.includes('bf')) {
                        mealType = 'breakfast';
                        breakfastCount++;
                    } else if (mealNameLower.includes('lunch') || mealNameLower.includes('afternoon')) {
                        mealType = 'lunch';
                        lunchCount++;
                    } else if (mealNameLower.includes('dinner') || mealNameLower.includes('evening')) {
                        mealType = 'dinner';
                        dinnerCount++;
                    } else {
                        // Default to dinner if we can't determine
                        mealType = 'dinner';
                        dinnerCount++;
                    }
                } else {
                    // Unknown type - log it and default to dinner
                    console.warn(`Unknown meal type "${originalType}" for meal "${meal.name}". Defaulting to dinner.`);
                    mealType = 'dinner';
                    dinnerCount++;
                }
            }
            
            const row = document.createElement('tr');
            row.className = 'meal-row';
            row.setAttribute('data-meal-id', meal.meal_id);
            row.setAttribute('data-meal-name', meal.name);
            row.setAttribute('data-meal-type', mealType);
            row.setAttribute('data-restaurant-id', restaurantId);
            row.setAttribute('data-restaurant-name', restaurantName);
            
            const adultPrice = parseFloat(meal.adult_price || meal.price || 0).toFixed(2);
            const childPrice = parseFloat(meal.child_price || meal.price || 0).toFixed(2);
            
            // Icon based on meal type
            let mealIcon = 'ri-restaurant-fill';
            let iconColor = 'text-success';
            if (mealType === 'breakfast') {
                mealIcon = 'ri-cup-line';
                iconColor = 'text-warning';
            } else if (mealType === 'lunch') {
                mealIcon = 'ri-restaurant-2-line';
                iconColor = 'text-info';
            } else if (mealType === 'dinner') {
                mealIcon = 'ri-restaurant-fill';
                iconColor = 'text-success';
            }
            
            row.innerHTML = `
                <td style="padding: 2px 8px; text-align: center;">
                    <input type="checkbox" class="meal-checkbox" data-meal-id="${meal.meal_id}">
                </td>
                <td style="padding: 2px 8px;">
                    <i class="${mealIcon} ${iconColor} me-1" style="font-size: 14px;"></i>
                    ${meal.name}
                    <small class="text-muted ms-2">(${mealType.charAt(0).toUpperCase() + mealType.slice(1)})</small>
                </td>
                <td style="padding: 2px 8px;">
                    <input type="number" class="form-control form-control-sm meal-count" data-meal-id="${meal.meal_id}" value="1" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                </td>
                <td style="padding: 2px 8px;">
                    <input type="number" class="form-control form-control-sm meal-adult-qty" data-meal-id="${meal.meal_id}" value="0" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                </td>
                <td style="padding: 2px 8px;">
                    <input type="text" class="form-control form-control-sm meal-adult-charge" data-meal-id="${meal.meal_id}" value="SGD ${adultPrice}" style="font-size: 10px; padding: 2px 4px;">
                </td>
                <td style="padding: 2px 8px;">
                    <input type="number" class="form-control form-control-sm meal-child-qty" data-meal-id="${meal.meal_id}" value="0" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                </td>
                <td style="padding: 2px 8px;">
                    <input type="text" class="form-control form-control-sm meal-child-charge" data-meal-id="${meal.meal_id}" value="SGD ${childPrice}" style="font-size: 10px; padding: 2px 4px;">
                </td>
                <td style="padding: 2px 8px;">
                    <input type="number" class="form-control form-control-sm meal-infant-qty" data-meal-id="${meal.meal_id}" value="0" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
                </td>
                <td style="padding: 2px 8px;">
                    <input type="text" class="form-control form-control-sm meal-infant-charge" data-meal-id="${meal.meal_id}" value="SGD 0.00" style="font-size: 10px; padding: 2px 4px;">
                </td>
            `;
            
            mealsTableBody.appendChild(row);
        });
        
        // Reset "Select All" checkbox
        const selectAllCheckbox = document.getElementById('selectAllMeals');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
        }
        
        // Show all meals (no filtering)
        
        // Debug: Log meal type counts
        console.log('Meal Type Summary:', {
            total: restaurantMeals.length,
            breakfast: breakfastCount,
            lunch: lunchCount,
            dinner: dinnerCount
        });
        
        // Show success message with meal type breakdown
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
        });
        
        let summaryMessage = `Loaded ${restaurantMeals.length} dish(es) from ${restaurantName}`;
        const breakdown = [];
        if (breakfastCount > 0) breakdown.push(`${breakfastCount} Breakfast`);
        if (lunchCount > 0) breakdown.push(`${lunchCount} Lunch`);
        if (dinnerCount > 0) breakdown.push(`${dinnerCount} Dinner`);
        
        if (breakdown.length > 0) {
            summaryMessage += `<br><small>(${breakdown.join(', ')})</small>`;
        }
        
        Toast.fire({
            icon: 'success',
            title: summaryMessage
        });
    }
    
    // Filter meals by type
    function filterMealsByType(type) {
        const rows = document.querySelectorAll('.meal-row');
        
        rows.forEach(row => {
            const rowType = row.getAttribute('data-meal-type');
            row.style.display = (rowType === type) ? '' : 'none';
        });
    }
    
    // Toggle select all meals
    function toggleSelectAllMeals() {
        const selectAll = document.getElementById('selectAllMeals');
        const checkboxes = document.querySelectorAll('.meal-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
    }
    
    // Save and close meals
    function saveAndCloseMeals() {
        // Check if we're editing an existing meal
        const isEditing = window.editingMealIndex !== undefined && window.editingMealIndex !== null;
        
        const selectedRows = document.querySelectorAll('.meal-checkbox:checked');
        
        if (selectedRows.length === 0 && !isEditing) {
            alert('Please select at least one meal');
            return;
        }
        
        // Get date/time from modal input field
        const dateTimeInput = document.getElementById('mealDateTime');
        console.log('=== saveAndCloseMeals ===');
        console.log('mealDateTime input element:', dateTimeInput);
        console.log('mealDateTime value:', dateTimeInput?.value);
        const dateTime = dateTimeInput?.value || getDefaultServiceDate();
        console.log('Final dateTime to use:', dateTime);
        console.log('isEditing:', isEditing);
        
        if (!dateTime) {
            alert('Please select date/time for the meal');
            return;
        }
        
        // If editing, update the existing meal
        if (isEditing && selectedRows.length > 0) {
            const checkbox = selectedRows[0]; // Only use first selected when editing
            const mealItemId = checkbox.getAttribute('data-meal-id'); // This is the meal item from database
            const row = checkbox.closest('tr');
            const mealName = row.getAttribute('data-meal-name');
            const mealType = row.getAttribute('data-meal-type') || mealName;
            
            // Get values from the row
            const mealCount = parseInt(row.querySelector('.meal-count').value) || 0;
            const adultsQty = parseInt(row.querySelector('.meal-adult-qty').value) || 0;
            const adultCharge = row.querySelector('.meal-adult-charge').value || 'SGD 0.00';
            const childQty = parseInt(row.querySelector('.meal-child-qty').value) || 0;
            const childCharge = row.querySelector('.meal-child-charge').value || 'SGD 0.00';
            const infantQty = parseInt(row.querySelector('.meal-infant-qty').value) || 0;
            const infantCharge = row.querySelector('.meal-infant-charge').value || 'SGD 0.00';
            
            // Get transfer info from restaurant transfer section (not per-row)
            const transferChecked = document.getElementById('restaurantTransferCheckbox')?.checked || false;
            const transferDestinationSelect = document.getElementById('restaurantTransferDestination');
            const transferDestination = transferDestinationSelect?.value || '';
            // Get destination name from data-name attribute (works with Select2)
            const transferDestinationOption = $('#restaurantTransferDestination').find(':selected');
            const transferDestinationName = transferDestinationOption.attr('data-name') || transferDestinationOption.text() || transferDestination;
            // Get vehicle type name from data attribute instead of value (which is vehicle_id)
            const vehicleTypeSelect = document.getElementById('restaurantTransferVehicleType');
            const vehicleType = vehicleTypeSelect?.selectedOptions[0]?.getAttribute('data-type') || 'sedan';
            const transferWay = document.getElementById('restaurantTransferWay')?.value || 'one-way';
            const transferType = document.getElementById('restaurantTransferType')?.value || 'S';
            
            console.log('=== Editing Meal - Transfer Status ===');
            console.log('Transfer checkbox checked:', transferChecked);
            console.log('Transfer destination:', transferDestination);
            console.log('Transfer destination name:', transferDestinationName);
            
            // Parse charges
            const adultCost = parseFloat(adultCharge.replace(/[^0-9.]/g, '')) || 0;
            const adultSell = adultCost;
            const childCost = parseFloat(childCharge.replace(/[^0-9.]/g, '')) || 0;
            const childSell = childCost;
            const infantCost = parseFloat(infantCharge.replace(/[^0-9.]/g, '')) || 0;
            const infantSell = infantCost;
            
            // Get restaurant info from dropdown (source of truth)
            const restaurantSelect = document.getElementById('mealRestaurant');
            const restaurantName = restaurantSelect && restaurantSelect.value 
                ? restaurantSelect.options[restaurantSelect.selectedIndex].getAttribute('data-name') 
                : 'Unknown Restaurant';
            
            // Get old meal and its transfer ID
            const oldMeal = mealList[window.editingMealIndex];
            const mealId = oldMeal.id; // Get existing meal ID
            let transferId = oldMeal.transferId; // Keep existing transfer ID if updating
            
            // FIRST: Remove old transfer if it exists (whether we're adding a new one or not)
            if (oldMeal && oldMeal.transferId) {
                console.log('Removing old transfer with ID:', oldMeal.transferId);
                const oldTransferCount = transferList.length;
                transferList = transferList.filter(t => t.id !== oldMeal.transferId);
                console.log('Transfers removed:', oldTransferCount - transferList.length);
            }
            
            // THEN: Create new transfer if checkbox is checked
            let transferInfo = null;
            let transferEntryId = null;
            
            if (transferChecked && transferDestination) {
                console.log('Creating/updating transfer for restaurant:', restaurantName);
                // Use existing transfer ID or generate new one
                if (!transferId) {
                    transferEntryId = generateId('transfer');
                } else {
                    transferEntryId = transferId;
                }
                
                transferInfo = {
                    destination: transferDestinationName,
                    vehicleType: vehicleType,
                    type: transferType,
                    way: transferWay
                };
                
                // Add to transfer list
                const transferEntry = {
                    id: transferEntryId,
                    dateTime: dateTime,
                    service: `${restaurantName} / ${transferDestinationName}`,
                    restaurantName: restaurantName, // Store restaurant name for entrypickup
                    transportMode: 'local',
                    type: transferType,
                    vehicleType: vehicleType,
                    way: transferWay,
                    destination: transferDestinationName,
                    adults: adultsQty,
                    child: childQty,
                    cost: 0,
                    sell: 0,
                    isStandalone: false,
                    sourceType: 'meal',
                    sourceId: mealId
                };
                
                transferList.push(transferEntry);
                transferId = transferEntryId;
                console.log('Transfer created/updated with ID:', transferEntryId);
            } else if (!transferChecked) {
                console.log('Transfer checkbox unchecked - no transfer will be created');
                transferId = null; // Clear transfer ID
            }
            
            // Get restaurant ID from dropdown (restaurantSelect and restaurantName already declared above)
            const restaurantId = restaurantSelect ? restaurantSelect.value : '';
            
            // Create meal data
            const mealData = {
                id: mealId, // Keep existing ID when editing (this is the meal list entry ID)
                destination: document.getElementById('mealDestination').value || 'Singapore',
                restaurantId: restaurantId,
                restaurantName: restaurantName,
                mealId: mealItemId, // This is the meal item ID from database
                mealName: mealName,
                mealType: mealType,
                dateTime: dateTime,
                mealCount: mealCount,
                adultsQty: adultsQty,
                adultCost: adultCost,
                adultSell: adultSell,
                childQty: childQty,
                childCost: childCost,
                childSell: childSell,
                infantQty: infantQty,
                infantCost: infantCost,
                infantSell: infantSell,
                transferId: transferId, // Will be null if transfer unchecked
                transferInfo: transferInfo, // Will be null if transfer unchecked
                guideId: null,
                guideInfo: null
            };
            
            console.log('Updated meal data:', mealData);
            
            // Update the meal
            mealList[window.editingMealIndex] = mealData;
            window.editingMealIndex = null;
            
            // Update header dates if needed
            recalculateHeaderDatesFromServices();
        } else {
            // Get restaurant info once (applies to all meals)
            const restaurantSelect = document.getElementById('mealRestaurant');
            const restaurantName = restaurantSelect && restaurantSelect.value 
                ? restaurantSelect.options[restaurantSelect.selectedIndex].getAttribute('data-name') 
                : 'Unknown Restaurant';
            const restaurantId = restaurantSelect ? restaurantSelect.value : '';
            
            // Get transfer info from restaurant transfer section (applies to all meals)
            const transferChecked = document.getElementById('restaurantTransferCheckbox')?.checked || false;
            const transferDestinationSelect = document.getElementById('restaurantTransferDestination');
            const transferDestination = transferDestinationSelect?.value || '';
            // Get destination name from data-name attribute (works with Select2)
            const transferDestinationOption = $('#restaurantTransferDestination').find(':selected');
            const transferDestinationName = transferDestinationOption.attr('data-name') || transferDestinationOption.text() || transferDestination;
            // Get vehicle type name from data attribute instead of value (which is vehicle_id)
            const vehicleTypeSelect = document.getElementById('restaurantTransferVehicleType');
            const vehicleType = vehicleTypeSelect?.selectedOptions[0]?.getAttribute('data-type') || 'sedan';
            const transferWay = document.getElementById('restaurantTransferWay')?.value || 'one-way';
            const transferType = document.getElementById('restaurantTransferType')?.value || 'S';
            
            // Adding new meals - loop through selected rows and collect IDs first
            const mealIds = [];
            selectedRows.forEach(checkbox => {
                mealIds.push(generateId('meal'));
            });
            
            // Create ONE transfer entry if checkbox is checked (shared by all meals)
            let transferInfo = null;
            let transferId = null;
            let transferEntryId = null;
            
            if (transferChecked && transferDestination) {
                transferEntryId = generateId('transfer');
                transferInfo = {
                    destination: transferDestinationName,
                    vehicleType: vehicleType,
                    type: transferType,
                    way: transferWay
                };
                
                // Add to transfer list - use first meal ID as source
                const transferEntry = {
                    id: transferEntryId,
                    dateTime: dateTime,
                    service: `${restaurantName} / ${transferDestinationName}`,
                    restaurantName: restaurantName, // Store restaurant name for entrypickup
                    transportMode: 'local',
                    type: transferType,
                    vehicleType: vehicleType,
                    way: transferWay,
                    destination: transferDestinationName,
                    adults: 0, // Will be calculated from all meals
                    child: 0,
                    cost: 0,
                    sell: 0,
                    isStandalone: false,
                    sourceType: 'meal',
                    sourceId: mealIds[0] // Link to first meal
                };
                
                transferList.push(transferEntry);
                transferId = transferEntryId;
            }
            
            // Adding new meals - loop through selected rows
            selectedRows.forEach((checkbox, index) => {
                const mealId = checkbox.getAttribute('data-meal-id');
                const row = checkbox.closest('tr');
                const mealName = row.getAttribute('data-meal-name');
                const mealType = row.getAttribute('data-meal-type') || mealName;
                
                // Get values from the row
                const mealCount = parseInt(row.querySelector('.meal-count').value) || 0;
                const adultsQty = parseInt(row.querySelector('.meal-adult-qty').value) || 0;
                const adultCharge = row.querySelector('.meal-adult-charge').value || 'SGD 0.00';
                const childQty = parseInt(row.querySelector('.meal-child-qty').value) || 0;
                const childCharge = row.querySelector('.meal-child-charge').value || 'SGD 0.00';
                const infantQty = parseInt(row.querySelector('.meal-infant-qty').value) || 0;
                const infantCharge = row.querySelector('.meal-infant-charge').value || 'SGD 0.00';
                
                // Parse charges
                const adultCost = parseFloat(adultCharge.replace(/[^0-9.]/g, '')) || 0;
                const adultSell = adultCost;
                const childCost = parseFloat(childCharge.replace(/[^0-9.]/g, '')) || 0;
                const childSell = childCost;
                const infantCost = parseFloat(infantCharge.replace(/[^0-9.]/g, '')) || 0;
                const infantSell = infantCost;
                
                // Create meal data (use shared transfer ID for all meals and pre-generated ID)
                const mealData = {
                    id: mealIds[index],
                    destination: document.getElementById('mealDestination').value || 'Singapore',
                    restaurantId: restaurantId,
                    restaurantName: restaurantName,
                    mealId: mealId,
                    mealName: mealName,
                    mealType: mealType,
                    dateTime: dateTime,
                    mealCount: mealCount,
                    adultsQty: adultsQty,
                    adultCost: adultCost,
                    adultSell: adultSell,
                    childQty: childQty,
                    childCost: childCost,
                    childSell: childSell,
                    infantQty: infantQty,
                    infantCost: infantCost,
                    infantSell: infantSell,
                    transferId: transferId, // Shared transfer ID
                    transferInfo: transferInfo, // Shared transfer info
                    guideId: null,
                    guideInfo: null
                };
                
                mealList.push(mealData);
                
                // Update header dates if needed
                updateHeaderDatesIfNeeded(dateTime);
            });
        }
        
        // Update tables
        updateMealTable();
        updateGuideTable();
        updateTransferTable();
        recalculateTotals();
        
        // Close modal
        const mealModal = bootstrap.Modal.getInstance(document.getElementById('mealModal'));
        mealModal.hide();
        
        // Reset checkboxes
        document.getElementById('selectAllMeals').checked = false;
        document.querySelectorAll('.meal-checkbox').forEach(cb => cb.checked = false);
    }
    
    // Add another meal (keep modal open)
    function addAnotherMeal() {
        const selectedRows = document.querySelectorAll('.meal-checkbox:checked');
        
        if (selectedRows.length === 0) {
            alert('Please select at least one meal');
            return;
        }
        
        // Save current selections without closing
        saveAndCloseMeals();
        
        // Reopen the modal
        const mealModal = new bootstrap.Modal(document.getElementById('mealModal'));
        mealModal.show();
    }
    
    // Save meal
    function saveMeal() {
        const destination = document.getElementById('mealDestination').value;
        const restaurantSelect = document.getElementById('mealRestaurant');
        const restaurantId = restaurantSelect.value;
        const restaurantName = restaurantSelect.options[restaurantSelect.selectedIndex]?.text || '';
        const dateTime = document.getElementById('mealDateTime').value;
        const adultsQty = parseInt(document.getElementById('mealAdultsQty').value);
        const adultCost = parseFloat(document.getElementById('mealAdultCost').value);
        const adultSell = parseFloat(document.getElementById('mealAdultSell').value);
        const childQty = parseInt(document.getElementById('mealChildQty').value);
        const childCost = parseFloat(document.getElementById('mealChildCost').value);
        const childSell = parseFloat(document.getElementById('mealChildSell').value);
        
        if (!restaurantId || !dateTime) {
            alert('Please select restaurant and date/time');
            return;
        }
        
        const isEditing = window.editingMealIndex !== undefined && window.editingMealIndex !== null;
        
        // Get transfer info
        const transferRequired = document.getElementById('mealTransferRequired').value === 'yes';
        let transferInfo = null;
        let transferId = null;
        
        if (transferRequired) {
            const transferType = document.getElementById('mealTransferType').value;
            const transferWay = document.getElementById('mealTransferWay').value;
            const vehicleType = document.getElementById('mealVehicleType').value;
            const transferCost = parseFloat(document.getElementById('mealTransferCost').value);
            const transferSell = parseFloat(document.getElementById('mealTransferSell').value);
            
            transferId = generateId('transfer');
            transferInfo = {
                id: transferId,
                type: transferType,
                way: transferWay,
                vehicleType: vehicleType,
                cost: transferCost,
                sell: transferSell,
                destination: restaurantName,
                dateTime: dateTime,
                adults: adultsQty,
                adultsQty: adultsQty,
                child: childQty,
                childQty: childQty,
                taxIncluded: true,
                isStandalone: false
            };
            
            // Add to transfer list
            transferList.push(transferInfo);
        }
        
    const mealData = {
        id: generateId('meal'),
            destination: destination,
            restaurantId: restaurantId,
            restaurantName: restaurantName,
            dateTime: dateTime,
            adultsQty: adultsQty,
            adultCost: adultCost,
            adultSell: adultSell,
            childQty: childQty,
            childCost: childCost,
            childSell: childSell,
            transferId: transferId,
            transferInfo: transferInfo
        };
        
        // Check if editing
        if (isEditing) {
            // Remove old transfer if exists
            const oldMeal = mealList[window.editingMealIndex];
            if (oldMeal.transferId) {
                transferList = transferList.filter(t => t.id !== oldMeal.transferId);
            }
            
            mealList[window.editingMealIndex] = mealData;
            window.editingMealIndex = null;
        } else {
            mealList.push(mealData);
        }
        
        // Update header dates
        if (isEditing) {
            // When editing, recalculate from all services to handle date changes properly
            recalculateHeaderDatesFromServices();
        } else {
            // When adding new, just expand if needed
            expandHeaderDatesIfNeeded(dateTime, true);
        }
        
        updateMealTable();
        updateTransferTable();
        recalculateTotals();
        
        // Close modal
        const mealModal = bootstrap.Modal.getInstance(document.getElementById('mealModal'));
        mealModal.hide();
    }
    
    // Update meal table
    function updateMealTable() {
        const tbody = document.getElementById('mealTableBody');
        const table = document.getElementById('mealTable');
        const emptyMessage = document.getElementById('emptyMealMessage');
        
        if (mealList.length === 0) {
            table.style.display = 'none';
            emptyMessage.style.display = 'block';
            return;
        }
        
        table.style.display = 'table';
        emptyMessage.style.display = 'none';
        
        tbody.innerHTML = mealList.map((meal, index) => {
            // Ensure dateTime has time component, if not add default time based on meal type
            let dateTimeValue = meal.dateTime || '';
            if (dateTimeValue && !dateTimeValue.includes('T')) {
                // Default times: Breakfast 08:00, Lunch 12:00, Dinner 19:00
                const mealType = (meal.mealType || '').toLowerCase();
                let defaultTime = '12:00'; // Default to lunch time
                if (mealType.includes('breakfast')) defaultTime = '08:00';
                else if (mealType.includes('dinner')) defaultTime = '19:00';
                dateTimeValue = dateTimeValue + 'T' + defaultTime;
            }
            
            return `
            <tr>
                <td><input type="checkbox" class="meal-checkbox" value="${meal.id}"></td>
                <td><input type="datetime-local" value="${dateTimeValue}" onchange="updateMealField(${index}, 'dateTime', this.value)" style="width: 180px; font-size: 10px;"></td>
                <td>
                    <a href="javascript:void(0)" onclick="editMeal(${index})" style="color: #0d6efd; text-decoration: underline; cursor: pointer;">
                        ${meal.restaurantName || 'Restaurant'} - ${meal.mealName || meal.mealType || 'Meal'}
                    </a>
                </td>
                <td><input type="number" value="${meal.adultsQty}" onchange="updateMealField(${index}, 'adultsQty', this.value)"></td>
                <td><input type="number" value="${meal.adultCost}" onchange="updateMealField(${index}, 'adultCost', this.value)" step="0.01"></td>
                <td><input type="number" value="${meal.adultSell}" onchange="updateMealField(${index}, 'adultSell', this.value)" step="0.01"></td>
                <td><input type="number" value="${meal.childQty}" onchange="updateMealField(${index}, 'childQty', this.value)"></td>
                <td><input type="number" value="${meal.childCost}" onchange="updateMealField(${index}, 'childCost', this.value)" step="0.01"></td>
                <td><input type="number" value="${meal.childSell}" onchange="updateMealField(${index}, 'childSell', this.value)" step="0.01"></td>
            </tr>
        `;
        }).join('');
    }
    
    // Ensure a meal row exists in the popup table for editing (for dynamically loaded meals)
    function ensureMealRowForEdit(meal) {
        const tbody = document.getElementById('mealsTableBody');
        if (!tbody) return null;
        
        const mealId = meal.restaurantId || meal.id || generateId('meal');
        const mealName = meal.restaurantName || 'Restaurant';
        const mealType = meal.mealType || 'custom';
        
        const existing = tbody.querySelector(`tr.meal-row[data-meal-id="${mealId}"]`);
        if (existing) return existing;
        
        // Get transfer info - check transferList first if transferId exists
        let transferInfo = meal.transferInfo || {};
        const hasTransfer = !!meal.transferInfo || !!meal.transferId;
        
        if (meal.transferId && !meal.transferInfo) {
            const linkedTransfer = transferList.find(t => t.id === meal.transferId);
            if (linkedTransfer) {
                transferInfo = {
                    destination: linkedTransfer.destination,
                    vehicleType: linkedTransfer.vehicleType || 'sedan',
                    type: linkedTransfer.mode === 'Private' ? 'private' : 'sic',
                    way: linkedTransfer.way === 'Both Way' ? 'both-way' : 'one-way'
                };
            }
        }
        
        const row = document.createElement('tr');
        row.className = 'meal-row';
        row.setAttribute('data-meal-id', mealId);
        row.setAttribute('data-meal-name', mealName);
        row.setAttribute('data-meal-type', mealType);
        row.innerHTML = `
            <td style="padding: 2px 8px; text-align: center;">
                <input type="checkbox" class="meal-checkbox" data-meal-id="${mealId}">
            </td>
            <td style="padding: 2px 8px;">
                ${mealName} <small class="text-muted">(${mealType})</small>
            </td>
            <td style="padding: 2px 8px;">
                <input type="number" class="form-control form-control-sm meal-count" data-meal-id="${mealId}" value="${meal.mealCount ?? 0}" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
            </td>
            <td style="padding: 2px 8px;">
                <input type="number" class="form-control form-control-sm meal-adult-qty" data-meal-id="${mealId}" value="${meal.adultsQty ?? 0}" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
            </td>
            <td style="padding: 2px 8px;">
                <input type="text" class="form-control form-control-sm meal-adult-charge" data-meal-id="${mealId}" value="${meal.adultCost ?? 0}" style="font-size: 10px; padding: 2px 4px;">
            </td>
            <td style="padding: 2px 8px;">
                <input type="number" class="form-control form-control-sm meal-child-qty" data-meal-id="${mealId}" value="${meal.childQty ?? 0}" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
            </td>
            <td style="padding: 2px 8px;">
                <input type="text" class="form-control form-control-sm meal-child-charge" data-meal-id="${mealId}" value="${meal.childCost ?? 0}" style="font-size: 10px; padding: 2px 4px;">
            </td>
            <td style="padding: 2px 8px;">
                <input type="number" class="form-control form-control-sm meal-infant-qty" data-meal-id="${mealId}" value="${meal.infantQty ?? 0}" min="0" style="font-size: 10px; padding: 2px 4px; text-align: center;">
            </td>
            <td style="padding: 2px 8px;">
                <input type="text" class="form-control form-control-sm meal-infant-charge" data-meal-id="${mealId}" value="${meal.infantCost ?? 0}" style="font-size: 10px; padding: 2px 4px;">
            </td>
            <td style="padding: 2px 8px; text-align: center;">
                <input type="checkbox" class="form-check-input meal-transfer-checkbox" data-meal-id="${mealId}" ${hasTransfer ? 'checked' : ''}>
            </td>
            <td style="padding: 2px 8px;">
                <select class="form-select form-select-sm meal-transfer-destination" data-meal-id="${mealId}" style="font-size: 10px; padding: 2px 4px;">
                    <option value="">Select Destination</option>
                    ${getMealDestinationOptionsHTML(transferInfo.destination)}
                </select>
            </td>
            <td style="padding: 2px 8px;">
                <select class="form-select form-select-sm meal-vehicle-type" data-meal-id="${mealId}" style="font-size: 10px; padding: 2px 4px;">
                    <option value="sedan" ${transferInfo.vehicleType === 'sedan' ? 'selected' : ''}>Sedan</option>
                    <option value="combi" ${transferInfo.vehicleType === 'combi' ? 'selected' : ''}>Combi</option>
                    <option value="van" ${transferInfo.vehicleType === 'van' ? 'selected' : ''}>Van</option>
                    <option value="bus" ${transferInfo.vehicleType === 'bus' ? 'selected' : ''}>Bus</option>
                </select>
            </td>
            <td style="padding: 2px 8px;">
                <select class="form-select form-select-sm meal-direction" data-meal-id="${mealId}" style="font-size: 10px; padding: 2px 4px;">
                    <option value="1way" ${transferInfo.way === '1way' || transferInfo.way === 'one-way' ? 'selected' : ''}>1 Way[H/R]</option>
                    <option value="2way" ${transferInfo.way === 'both-way' || transferInfo.way === '2way' ? 'selected' : ''}>2 Way[H/R]</option>
                </select>
            </td>
            <td style="padding: 2px 8px;">
                <select class="form-select form-select-sm meal-transfer-type" data-meal-id="${mealId}" style="font-size: 10px; padding: 2px 4px;">
                    <option value="S" ${transferInfo.type === 'S' || transferInfo.type === 'sic' ? 'selected' : ''}>Shared</option>
                    <option value="P" ${transferInfo.type === 'P' || transferInfo.type === 'private' ? 'selected' : ''}>Private</option>
                </select>
            </td>
        `;
        
        // Prepend so it's visible immediately
        tbody.prepend(row);
        return row;
    }
    
    // Edit meal
    function editMeal(index) {
        const meal = mealList[index];
        if (!meal) return;
        
        console.log('=== EDITING MEAL ===');
        console.log('Index:', index);
        console.log('Full meal object:', JSON.parse(JSON.stringify(meal)));
        console.log('Restaurant:', meal.restaurantName, 'ID:', meal.restaurantId);
        console.log('Meal Name:', meal.mealName, 'Type:', meal.mealType);
        console.log('Destination:', meal.destination);
        console.log('Transfer ID:', meal.transferId);
        console.log('Transfer Info:', meal.transferInfo);
        console.log('Current transferList:', JSON.parse(JSON.stringify(transferList)));
        
        if (meal.transferId) {
            const linkedTransfer = transferList.find(t => t.id === meal.transferId);
            console.log('Linked transfer found:', linkedTransfer);
        }
        
        window.editingMealIndex = index;
        
        // Normalize date and set destination
        const normalizedDate = normalizeDateToYYYYMMDD(meal.dateTime);
        document.getElementById('mealDateTime').value = normalizedDate || '';
        
        // Set destination
        const destinationSelect = document.getElementById('mealDestination');
        if (destinationSelect && meal.destination) {
            destinationSelect.value = meal.destination;
        }
        
        // Load restaurants for this destination
        loadRestaurantsByDestination();
        
        // Wait a moment for restaurants to load, then select the restaurant
        setTimeout(() => {
            const restaurantSelect = document.getElementById('mealRestaurant');
            if (restaurantSelect && meal.restaurantId) {
                restaurantSelect.value = meal.restaurantId;
                
                // Load meals from this restaurant
                updateMealsFromRestaurant();
                
                // Wait for meals to load, then populate the form
                setTimeout(() => {
                    populateMealFormForEdit(meal);
                    
                    // Populate restaurant transfer section if transfer exists
                    if (meal.transferId || meal.transferInfo) {
                        console.log('=== Populating Transfer Section for Edit ===');
                        console.log('Meal data:', meal);
                        
                        const restaurantTransferCheckbox = document.getElementById('restaurantTransferCheckbox');
                        if (restaurantTransferCheckbox) {
                            restaurantTransferCheckbox.checked = true;
                            toggleRestaurantTransferFields(); // Show the fields
                        }
                        
                        // Get transfer info - prioritize meal.transferInfo, then lookup from transferList
                        let tInfo = null;
                        
                        if (meal.transferInfo) {
                            // Use the transfer info stored with the meal
                            tInfo = meal.transferInfo;
                            console.log('Using meal.transferInfo:', tInfo);
                        } else if (meal.transferId) {
                            // Lookup from transferList
                            const linkedTransfer = transferList.find(t => t.id === meal.transferId);
                            console.log('Looking up transfer with ID:', meal.transferId, 'Found:', linkedTransfer);
                            if (linkedTransfer) {
                                tInfo = {
                                    destination: linkedTransfer.destination,
                                    vehicleType: linkedTransfer.vehicleType || 'sedan',
                                    type: linkedTransfer.mode === 'Private' ? 'private' : 'sic',
                                    way: linkedTransfer.way === 'Both Way' ? 'both-way' : 'one-way'
                                };
                            }
                        }
                        
                        if (tInfo) {
                            console.log('Final transfer info to populate:', tInfo);
                            
                            // Populate transfer fields
                            setTimeout(() => {
                                const destSelect = document.getElementById('restaurantTransferDestination');
                                if (destSelect && tInfo.destination) {
                                    destSelect.value = tInfo.destination;
                                    console.log('Set destination to:', tInfo.destination);
                                }
                                
                                const vehicleSelect = document.getElementById('restaurantTransferVehicleType');
                                if (vehicleSelect && tInfo.vehicleType) {
                                    vehicleSelect.value = tInfo.vehicleType;
                                    console.log('Set vehicle type to:', tInfo.vehicleType);
                                }
                                
                                const waySelect = document.getElementById('restaurantTransferWay');
                                if (waySelect && tInfo.way) {
                                    waySelect.value = tInfo.way;
                                    console.log('Set way to:', tInfo.way);
                                }
                                
                                const typeSelect = document.getElementById('restaurantTransferType');
                                if (typeSelect && tInfo.type) {
                                    typeSelect.value = tInfo.type;
                                    console.log('Set type to:', tInfo.type);
                                }
                            }, 100);
                        } else {
                            console.warn('No transfer info found for meal');
                        }
                    } else {
                        console.log('No transfer data for this meal');
                    }
                }, 300);
            } else {
                // If no restaurant ID, just try to populate what we can
                populateMealFormForEdit(meal);
            }
        }, 100);
        
        document.getElementById('mealModalTitleText').textContent = 'Edit Meal / Restaurant';
        const saveMealBtn = document.getElementById('saveMealBtnText');
        if (saveMealBtn) saveMealBtn.textContent = 'Update Meal';
        
        const mealModal = new bootstrap.Modal(document.getElementById('mealModal'));
        mealModal.show();
    }
    
    // Helper function to populate meal form fields for editing
    function populateMealFormForEdit(meal) {
        // Find matching meal row (by mealId or meal name)
        const rows = Array.from(document.querySelectorAll('.meal-row'));
        const targetRow = rows.find(r => {
            const rowMealId = r.getAttribute('data-meal-id');
            const rowName = r.getAttribute('data-meal-name');
            return (meal.mealId && String(meal.mealId) === rowMealId) || 
                   (meal.mealName && meal.mealName === rowName);
        });
        
        // If no matching row exists, create one dynamically
        let rowToUse = targetRow;
        if (!rowToUse) {
            rowToUse = ensureMealRowForEdit(meal);
        }
        
        // Reset all checkboxes first
        document.querySelectorAll('.meal-checkbox').forEach(cb => cb.checked = false);
        
        if (rowToUse) {
            const mealId = rowToUse.getAttribute('data-meal-id');
            
            // Check the row
            const checkbox = rowToUse.querySelector(`.meal-checkbox[data-meal-id="${mealId}"]`);
            if (checkbox) checkbox.checked = true;
            
            // Fill editable fields on the row
            const setVal = (selector, value) => {
                const el = rowToUse.querySelector(`${selector}[data-meal-id="${mealId}"]`);
                if (el) el.value = value ?? el.value;
            };
            
            setVal('.meal-count', meal.mealCount);
            setVal('.meal-adult-qty', meal.adultsQty);
            setVal('.meal-adult-charge', 'SGD ' + (meal.adultCost || 0));
            setVal('.meal-child-qty', meal.childQty);
            setVal('.meal-child-charge', 'SGD ' + (meal.childCost || 0));
            setVal('.meal-infant-qty', meal.infantQty);
            setVal('.meal-infant-charge', 'SGD ' + (meal.infantCost || 0));
            
            // Transfer fields
            const transferCheckbox = rowToUse.querySelector(`.meal-transfer-checkbox[data-meal-id="${mealId}"]`);
            if (transferCheckbox) {
                const hasTransfer = !!meal.transferInfo || !!meal.transferId;
                console.log('Has transfer:', hasTransfer, 'transferInfo:', meal.transferInfo, 'transferId:', meal.transferId);
                transferCheckbox.checked = hasTransfer;
                if (hasTransfer) {
                    // Try to get transfer info from transferList if transferId exists
                    let tInfo = meal.transferInfo || {};
                    if (meal.transferId) {
                        const linkedTransfer = transferList.find(t => t.id === meal.transferId);
                        console.log('Looking for transfer with id:', meal.transferId, 'Found:', linkedTransfer);
                        if (linkedTransfer) {
                            tInfo = {
                                destination: linkedTransfer.destination,
                                vehicleType: linkedTransfer.vehicleType || 'sedan',
                                type: linkedTransfer.mode === 'Private' ? 'private' : 'sic',
                                way: linkedTransfer.way === 'Both Way' ? 'both-way' : 'one-way'
                            };
                        }
                    }
                    console.log('Setting transfer values:', tInfo);
                    setVal('.meal-transfer-destination', tInfo.destination);
                    setVal('.meal-vehicle-type', tInfo.vehicleType || 'sedan');
                    setVal('.meal-transfer-type', tInfo.type || 'S');
                    const direction = tInfo.way === 'both-way' || tInfo.way === 'Both Way' || tInfo.way === '2way' ? '2way' : '1way';
                    setVal('.meal-direction', direction);
                }
            }
            
            // Optional and Supplement checkboxes
            const optionalCheckbox = rowToUse.querySelector(`.meal-optional-checkbox[data-meal-id="${mealId}"]`);
            if (optionalCheckbox && meal.optional) {
                optionalCheckbox.checked = meal.optional;
            }
            
            const supplementCheckbox = rowToUse.querySelector(`.meal-supplement-checkbox[data-meal-id="${mealId}"]`);
            if (supplementCheckbox && meal.supplement) {
                supplementCheckbox.checked = meal.supplement;
            }
            
            // Scroll the row into view
            rowToUse.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            console.warn('populateMealFormForEdit: no matching meal row found for', meal.mealName || meal.mealId);
        }
    }
    
    // Update meal field
    function updateMealField(index, field, value) {
        if (mealList[index]) {
            mealList[index][field] = value;
        }
    }
    
    // Toggle select all meals (main table)
    function toggleSelectAllMealsMain() {
        const selectAll = document.getElementById('selectAllMealsMain');
        const checkboxes = document.querySelectorAll('.meal-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
    }
    
    // Remove selected meals
    function removeSelectedMeals() {
        const checkboxes = document.querySelectorAll('.meal-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Please select meals to remove');
            return;
        }
        
        if (!confirm(`Remove ${checkboxes.length} selected meal(s)?`)) {
            return;
        }
        
        const idsToRemove = Array.from(checkboxes).map(cb => cb.value);
        
        // Also remove associated transfers and guides
        mealList.forEach(meal => {
            if (idsToRemove.includes(String(meal.id))) {
                if (meal.transferId) {
                    transferList = transferList.filter(t => String(t.id) !== String(meal.transferId));
                }
                if (meal.guideId) {
                    guideList = guideList.filter(g => String(g.id) !== String(meal.guideId));
                }
            }
        });
        
        mealList = mealList.filter(meal => !idsToRemove.includes(String(meal.id)));
        
        updateMealTable();
        updateTransferTable();
        updateGuideTable();
        recalculateHeaderDatesFromServices();
        recalculateTotals();
    }

    // ==================== TRANSFER FUNCTIONS ====================
    
    // Update transfer table - displays all transfers from tours and meals
    function updateTransferTable() {
        const tbody = document.getElementById('transferTableBody');
        const table = document.getElementById('transferTable');
        const emptyMessage = document.getElementById('emptyTransferMessage');
        
        if (transferList.length === 0) {
            table.style.display = 'none';
            emptyMessage.style.display = 'block';
            return;
        }
        
        table.style.display = 'table';
        emptyMessage.style.display = 'none';
        
        // Helper function to get transport mode icon
        const getModeIcon = (mode) => {
            const icons = {
                'local': '<i class="ri-car-line" style="font-size: 16px;" title="Local Transfer"></i>',
                'flight': '<i class="ri-flight-takeoff-line" style="font-size: 16px;" title="Flight"></i>',
                'cruise': '<i class="ri-ship-line" style="font-size: 16px;" title="Cruise"></i>',
                'train': '<i class="ri-train-line" style="font-size: 16px;" title="Train"></i>',
                'bus': '<i class="ri-bus-line" style="font-size: 16px;" title="Bus"></i>'
            };
            return icons[mode] || icons['local'];
        };
        
        // Helper function to get destination display text
        const getDestinationText = (transfer) => {
            // Check if this is a hotel transfer (has hotelName)
            if (transfer.hotelName) {
                return `${transfer.hotelName} / ${transfer.hotelDestination || transfer.destination || ''}`;
            }
            
            const mode = transfer.transportMode || 'local';
            if (mode === 'local') {
                return transfer.destination || (transfer.pickup ? transfer.pickup + ' → ' + transfer.drop : '-');
            } else {
                // For flight, train, bus, cruise
                return (transfer.departFrom || '') + (transfer.destination ? ' → ' + transfer.destination : (transfer.arrivalTo ? ' → ' + transfer.arrivalTo : ''));
            }
        };
        
        // Helper function to get vehicle/transport type
        const getVehicleType = (transfer) => {
            const mode = transfer.transportMode || 'local';
            if (mode === 'local') {
                // Return the vehicle type name properly formatted
                const vehicleType = transfer.vehicleType || '-';
                if (vehicleType === '-') return '-';
                // Capitalize first letter
                return vehicleType.charAt(0).toUpperCase() + vehicleType.slice(1);
            } else if (mode === 'flight') {
                return transfer.airline || transfer.operator || '-';
            } else if (mode === 'cruise') {
                return transfer.vessel || transfer.by || '-';
            } else if (mode === 'train' || mode === 'bus') {
                return transfer.operator || '-';
            }
            return '-';
        };
        
        // Helper function to get type/class
        const getTypeClass = (transfer) => {
            const mode = transfer.transportMode || 'local';
            if (mode === 'local') {
                const type = transfer.type || '-';
                if (type === '-') return '-';
                // Format the type properly
                if (type === 'S' || type === 'sic') return 'Shared';
                if (type === 'P' || type === 'private') return 'Private';
                return type.charAt(0).toUpperCase() + type.slice(1);
            } else {
                return transfer.class || transfer.cabinClass || '-';
            }
        };
        
        // Helper function to get way/trip type
        const getWayType = (transfer) => {
            const mode = transfer.transportMode || 'local';
            if (mode === 'local') {
                const way = transfer.way || '-';
                if (way === '-') return '-';
                // Format the way properly
                if (way === 'one-way') return 'One Way';
                if (way === 'both-way') return 'Both Way';
                return way;
            } else {
                return transfer.tripType || '-';
            }
        };
        
        tbody.innerHTML = transferList.map((transfer, index) => {
            // Determine display name based on source
            let displayName = '';
            if (transfer.isStandalone) {
                // For standalone transfers, show pickup → drop
                displayName = transfer.destination || `${transfer.pickupName || '-'} → ${transfer.dropName || '-'}`;
            } else if (transfer.service) {
                // For hotel transfers or other services with service field
                // Use the service field which has format "Hotel Name / Destination"
                displayName = transfer.service;
            } else if (transfer.destination) {
                // For attraction/restaurant transfers - show the destination name
                displayName = transfer.destination;
            } else {
                // For transfers from other services (arrival/departure)
                displayName = transfer.portName || '-';
            }
            
            // Ensure dateTime has time component, if not add default time 09:00
            let dateTimeValue = transfer.dateTime || '';
            if (dateTimeValue && !dateTimeValue.includes('T')) {
                dateTimeValue = dateTimeValue + 'T09:00';
            }
            
            // Determine checkbox display - show "Linked" for non-standalone transfers
            const checkboxHtml = transfer.isStandalone 
                ? `<input type="checkbox" class="transfer-checkbox" value="${transfer.id}">`
                : `<span style="font-size: 10px; color: #6c757d; font-style: italic;">Linked</span>`;
            
            // Determine if service name should be clickable (only standalone transfers)
            const serviceHtml = transfer.isStandalone
                ? `<a href="javascript:void(0)" onclick="editTransfer(${index})" style="color: #0d6efd; text-decoration: underline; cursor: pointer;">
                        ${displayName}
                    </a>`
                : `<span style="color: #6c757d;">${displayName}</span>`;
            
            // Get adult and child values - support both field name formats
            const adults = transfer.adults || transfer.adultsQty || 0;
            const child = transfer.child || transfer.childQty || 0;
            
            return `
            <tr>
                <td>${checkboxHtml}</td>
                <td><input type="datetime-local" value="${dateTimeValue}" onchange="updateTransferField(${index}, 'dateTime', this.value)" style="width: 180px; font-size: 10px;"></td>
                <td>
                    ${serviceHtml}
                </td>
                <td>${getModeIcon(transfer.transportMode || 'local')}</td>
                <td>${getVehicleType(transfer)}</td>
                <td>${getTypeClass(transfer)}</td>
                <td>${getWayType(transfer)}</td>
                <td><input type="number" value="${adults}" onchange="updateTransferField(${index}, 'adults', this.value)" style="width: 50px;"></td>
                <td><input type="number" value="${child}" onchange="updateTransferField(${index}, 'child', this.value)" style="width: 50px;"></td>
                <td><input type="number" value="${transfer.cost || 0}" readonly style="width: 70px; background-color: #e9ecef;"></td>
                <td><input type="number" value="${transfer.sell || 0}" onchange="updateTransferField(${index}, 'sell', this.value)" step="0.01" style="width: 70px;"></td>
            </tr>
        `;
        }).join('');
    }
    
    // Update transfer field
    function updateTransferField(index, field, value) {
        if (transferList[index]) {
            const transfer = transferList[index];
            transfer[field] = value;
            
            // Also update alternate field names for consistency
            if (field === 'adults') {
                transfer.adultsQty = value;
            } else if (field === 'child') {
                transfer.childQty = value;
            }
            
            // If dateTime field is changed, only expand header dates
            // Do NOT update linked service dates when changing from transfer table
            if (field === 'dateTime' && value) {
                // Always expand header dates only
                expandHeaderDatesIfNeeded(value, false);
                
                // Log the update
                if (transfer.isStandalone) {
                    console.log('Updated standalone transfer date to:', value, '(only header dates updated)');
                } else {
                    console.log('Updated linked transfer date to:', value, '(only header dates updated, source service unchanged)');
                }
                
                // Update the transfer table to reflect the change
                updateTransferTable();
            }
        }
    }
    
    // Open transfer modal (for standalone transfers)
    // Switch between transport mode forms
    function switchTransferMode(mode) {
        // Hide all forms
        document.querySelectorAll('.transfer-mode-form').forEach(form => {
            form.style.display = 'none';
        });
        
        // Show selected form
        const formMap = {
            'local': 'localTransferForm',
            'flight': 'flightForm',
            'cruise': 'cruiseForm',
            'train': 'trainForm',
            'bus': 'busForm'
        };
        
        const formId = formMap[mode];
        if (formId) {
            document.getElementById(formId).style.display = 'block';
        }
    }
    
    // Reset all transfer forms
    function resetAllTransferForms() {
        // Local Transfer
        document.getElementById('localDateTime').value = '';
        $('#localPickup').val('').trigger('change');
        $('#localDrop').val('').trigger('change');
        document.getElementById('localVehicleType').value = 'sedan';
        document.getElementById('localType').value = 'S';
        document.getElementById('localWay').value = 'both-way';
        document.getElementById('localAdults').value = '2';
        document.getElementById('localChild').value = '0';
        
        // Flight
        document.getElementById('flightDepartFrom').value = '';
        document.getElementById('flightDestination').value = '';
        document.getElementById('flightTripType').value = 'return';
        document.getElementById('flightDepartureDate').value = '';
        document.getElementById('flightReturnDate').value = '';
        document.getElementById('flightAirline').value = '';
        document.getElementById('flightNumber').value = '';
        document.getElementById('flightOperator').value = '';
        document.getElementById('flightClass').value = 'economy';
        document.getElementById('flightAdults').value = '2';
        document.getElementById('flightChild').value = '0';
        document.getElementById('flightCost').value = '0';
        document.getElementById('flightSell').value = '0';
        document.getElementById('flightTaxIncluded').checked = false;
        
        // Cruise
        document.getElementById('cruiseDepartFrom').value = '';
        document.getElementById('cruiseFromTerminal').value = '';
        document.getElementById('cruiseBy').value = 'cruise';
        document.getElementById('cruiseType').value = '';
        document.getElementById('cruiseVessel').value = '';
        document.getElementById('cruiseArrivalTo').value = '';
        document.getElementById('cruiseDepartureDate').value = '';
        document.getElementById('cruiseArrivalDate').value = '';
        document.getElementById('cruiseOperator').value = '';
        document.getElementById('cruiseCabinClass').value = 'economy';
        document.getElementById('cruiseAdults').value = '2';
        document.getElementById('cruiseChild').value = '0';
        document.getElementById('cruiseCost').value = '0';
        document.getElementById('cruiseSell').value = '0';
        document.getElementById('cruiseTaxIncluded').checked = false;
        
        // Train
        document.getElementById('trainDepartFrom').value = '';
        document.getElementById('trainDestination').value = '';
        document.getElementById('trainTripType').value = 'return';
        document.getElementById('trainDepartureDate').value = '';
        document.getElementById('trainReturnDate').value = '';
        document.getElementById('trainOperator').value = '';
        document.getElementById('trainClass').value = '1st-class';
        document.getElementById('trainStationTransfer').value = '';
        document.getElementById('trainAdults').value = '2';
        document.getElementById('trainChild').value = '0';
        document.getElementById('trainCost').value = '0';
        document.getElementById('trainSell').value = '0';
        document.getElementById('trainTaxIncluded').checked = false;
        
        // Bus
        document.getElementById('busDepartFrom').value = '';
        document.getElementById('busDestination').value = '';
        document.getElementById('busTripType').value = 'return';
        document.getElementById('busDepartureDate').value = '';
        document.getElementById('busReturnDate').value = '';
        document.getElementById('busOperator').value = '';
        document.getElementById('busClass').value = 'executive';
        document.getElementById('busStationTransfer').value = '';
        document.getElementById('busAdults').value = '2';
        document.getElementById('busChild').value = '0';
        document.getElementById('busCost').value = '0';
        document.getElementById('busSell').value = '0';
        document.getElementById('busTaxIncluded').checked = false;
    }
    
    // Open Transfer Modal for standalone transfers
    function openTransferModal() {
        // Reset all forms
        resetAllTransferForms();
        
        // Set default dates from header
        const defaultDate = getDefaultServiceDate();
        document.getElementById('localDateTime').value = defaultDate;
        document.getElementById('flightDepartureDate').value = defaultDate;
        document.getElementById('cruiseDepartureDate').value = defaultDate;
        document.getElementById('trainDepartureDate').value = defaultDate;
        document.getElementById('busDepartureDate').value = defaultDate;
        
        // Reset transport mode to local transfer
        const localRadio = document.querySelector('input[name="transferMode"][value="local"]');
        if (localRadio) localRadio.checked = true;
        switchTransferMode('local');
        
        window.editingTransferIndex = null;
        document.getElementById('transferModalTitleText').textContent = 'Add Transfer Package';
        document.getElementById('saveTransferBtnText').textContent = 'Add Transfer';
        
        // Auto-fill adults, children, infants, and country from header
        autoFillModalFields('transfer');
        
        // Initialize Select2 for pickup and drop dropdowns in transfer modal
        if (typeof $.fn.select2 !== 'undefined') {
            $('#localPickup').select2({
                placeholder: 'Search and select pickup location',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#transferModal')
            });
            $('#localDrop').select2({
                placeholder: 'Search and select drop location',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#transferModal')
            });
        }
        
        const transferModal = new bootstrap.Modal(document.getElementById('transferModal'));
        transferModal.show();
    }
    
    // Save Transfer Package
    function saveTransferPackage() {
        // Get selected transport mode
        const transportModeRadio = document.querySelector('input[name="transferMode"]:checked');
        const transportMode = transportModeRadio ? transportModeRadio.value : 'local';
        
    let transferData = {
        id: generateId('transfer'),
            transportMode: transportMode,
            isStandalone: true
        };
        
        // Collect data based on transport mode
        if (transportMode === 'local') {
            const dateTime = document.getElementById('localDateTime').value;
            const pickupSelect = document.getElementById('localPickup');
            const dropSelect = document.getElementById('localDrop');
            
            if (!dateTime) {
                alert('Please select date/time');
                return;
            }
            if (!pickupSelect.value || !dropSelect.value) {
                alert('Please select both pickup and drop locations');
                return;
            }
            
            // Use jQuery to get selected option data (works with Select2)
            const pickupOption = $('#localPickup').find(':selected');
            const dropOption = $('#localDrop').find(':selected');
            
            const pickupName = pickupOption.attr('data-name') || pickupOption.text() || '';
            const dropName = dropOption.attr('data-name') || dropOption.text() || '';
            const pickupType = pickupOption.attr('data-type') || '';
            const dropType = dropOption.attr('data-type') || '';
            
            // Get vehicle ID and type
            const vehicleSelect = document.getElementById('localVehicleType');
            const vehicleId = vehicleSelect.value;
            const vehicleType = vehicleSelect.selectedOptions[0]?.getAttribute('data-type') || '';
            
            transferData = {
                ...transferData,
                dateTime: dateTime,
                pickupId: pickupSelect.value,
                pickupName: pickupName,
                pickupType: pickupType,
                dropId: dropSelect.value,
                dropName: dropName,
                dropType: dropType,
                destination: `${pickupName} → ${dropName}`,
                vehicleId: vehicleId,
                vehicleType: vehicleType,
                type: document.getElementById('localType').value,
                way: document.getElementById('localWay').value,
                hasTransfer: true,
                adults: parseInt(document.getElementById('localAdults').value) || 0,
                child: parseInt(document.getElementById('localChild').value) || 0,
                cost: 0, // Cost will be calculated or set later
                sell: 0, // Sell will be editable in the table
                taxIncluded: false
            };
        } 
        else if (transportMode === 'flight') {
            const departureDate = document.getElementById('flightDepartureDate').value;
            const departFrom = document.getElementById('flightDepartFrom').value;
            const destination = document.getElementById('flightDestination').value;
            
            if (!departureDate || !departFrom || !destination) {
                alert('Please fill in departure date, depart from, and destination');
                return;
            }
            
            transferData = {
                ...transferData,
                dateTime: departureDate,
                departFrom: departFrom,
                destination: destination,
                tripType: document.getElementById('flightTripType').value,
                returnDate: document.getElementById('flightReturnDate').value,
                airline: document.getElementById('flightAirline').value,
                flightNumber: document.getElementById('flightNumber').value,
                operator: document.getElementById('flightOperator').value,
                class: document.getElementById('flightClass').value,
                adults: parseInt(document.getElementById('flightAdults').value) || 0,
                child: parseInt(document.getElementById('flightChild').value) || 0,
                cost: parseFloat(document.getElementById('flightCost').value) || 0,
                sell: parseFloat(document.getElementById('flightSell').value) || 0,
                taxIncluded: document.getElementById('flightTaxIncluded').checked
            };
        }
        else if (transportMode === 'cruise') {
            const departureDate = document.getElementById('cruiseDepartureDate').value;
            const departFrom = document.getElementById('cruiseDepartFrom').value;
            
            if (!departureDate || !departFrom) {
                alert('Please fill in departure date and depart from');
                return;
            }
            
            transferData = {
                ...transferData,
                dateTime: departureDate,
                departFrom: departFrom,
                fromTerminal: document.getElementById('cruiseFromTerminal').value,
                by: document.getElementById('cruiseBy').value,
                cruiseType: document.getElementById('cruiseType').value,
                vessel: document.getElementById('cruiseVessel').value,
                arrivalTo: document.getElementById('cruiseArrivalTo').value,
                arrivalDate: document.getElementById('cruiseArrivalDate').value,
                operator: document.getElementById('cruiseOperator').value,
                cabinClass: document.getElementById('cruiseCabinClass').value,
                adults: parseInt(document.getElementById('cruiseAdults').value) || 0,
                child: parseInt(document.getElementById('cruiseChild').value) || 0,
                cost: parseFloat(document.getElementById('cruiseCost').value) || 0,
                sell: parseFloat(document.getElementById('cruiseSell').value) || 0,
                taxIncluded: document.getElementById('cruiseTaxIncluded').checked
            };
        }
        else if (transportMode === 'train') {
            const departureDate = document.getElementById('trainDepartureDate').value;
            const departFrom = document.getElementById('trainDepartFrom').value;
            const destination = document.getElementById('trainDestination').value;
            
            if (!departureDate || !departFrom || !destination) {
                alert('Please fill in departure date, depart from, and destination');
                return;
            }
            
            transferData = {
                ...transferData,
                dateTime: departureDate,
                departFrom: departFrom,
                destination: destination,
                tripType: document.getElementById('trainTripType').value,
                returnDate: document.getElementById('trainReturnDate').value,
                operator: document.getElementById('trainOperator').value,
                class: document.getElementById('trainClass').value,
                stationTransfer: document.getElementById('trainStationTransfer').value,
                adults: parseInt(document.getElementById('trainAdults').value) || 0,
                child: parseInt(document.getElementById('trainChild').value) || 0,
                cost: parseFloat(document.getElementById('trainCost').value) || 0,
                sell: parseFloat(document.getElementById('trainSell').value) || 0,
                taxIncluded: document.getElementById('trainTaxIncluded').checked
            };
        }
        else if (transportMode === 'bus') {
            const departureDate = document.getElementById('busDepartureDate').value;
            const departFrom = document.getElementById('busDepartFrom').value;
            const destination = document.getElementById('busDestination').value;
            
            if (!departureDate || !departFrom || !destination) {
                alert('Please fill in departure date, depart from, and destination');
                return;
            }
            
            transferData = {
                ...transferData,
                dateTime: departureDate,
                departFrom: departFrom,
                destination: destination,
                tripType: document.getElementById('busTripType').value,
                returnDate: document.getElementById('busReturnDate').value,
                operator: document.getElementById('busOperator').value,
                class: document.getElementById('busClass').value,
                stationTransfer: document.getElementById('busStationTransfer').value,
                adults: parseInt(document.getElementById('busAdults').value) || 0,
                child: parseInt(document.getElementById('busChild').value) || 0,
                cost: parseFloat(document.getElementById('busCost').value) || 0,
                sell: parseFloat(document.getElementById('busSell').value) || 0,
                taxIncluded: document.getElementById('busTaxIncluded').checked
            };
        }
        
        const isEditing = window.editingTransferIndex !== null && window.editingTransferIndex !== undefined;
        
        if (isEditing) {
            transferList[window.editingTransferIndex] = transferData;
            window.editingTransferIndex = null;
        } else {
            transferList.push(transferData);
        }
        
        // Update header dates
        if (isEditing) {
            // When editing, recalculate from all services to handle date changes properly
            recalculateHeaderDatesFromServices();
        } else {
            // When adding new, just expand if needed
            if (transferData.dateTime) {
                updateHeaderDatesIfNeeded(transferData.dateTime);
            }
        }
        
        updateTransferTable();
        recalculateTotals();
        
        const transferModal = bootstrap.Modal.getInstance(document.getElementById('transferModal'));
        transferModal.hide();
    }
    
    // Edit transfer
    function editTransfer(index) {
        const transfer = transferList[index];
        if (!transfer) return;
        
        // If it's linked to tour/meal, don't allow standalone edit
        if (transfer.isStandalone === false) {
            alert('This transfer is linked to a tour or meal. Please edit the associated tour/meal to modify transfer details.');
            return;
        }
        
        window.editingTransferIndex = index;
        
        // Reset all forms first
        resetAllTransferForms();
        
        // Set transport mode
        const transportMode = transfer.transportMode || 'local';
        const modeRadio = document.querySelector(`input[name="transferMode"][value="${transportMode}"]`);
        if (modeRadio) {
            modeRadio.checked = true;
            switchTransferMode(transportMode);
        }
        
        // Load data based on transport mode
        if (transportMode === 'local') {
            // Normalize date to YYYY-MM-DD format for date input
            const normalizedDate = normalizeDateToYYYYMMDD(transfer.dateTime);
            document.getElementById('localDateTime').value = normalizedDate || '';
            if (transfer.pickupId) {
                setTimeout(() => {
                    $('#localPickup').val(transfer.pickupId).trigger('change');
                }, 100);
            }
            if (transfer.dropId) {
                setTimeout(() => {
                    $('#localDrop').val(transfer.dropId).trigger('change');
                }, 100);
            }
            document.getElementById('localVehicleType').value = transfer.vehicleType || 'sedan';
            document.getElementById('localType').value = transfer.type || 'S';
            document.getElementById('localWay').value = transfer.way || 'both-way';
            document.getElementById('localAdults').value = transfer.adults || 2;
            document.getElementById('localChild').value = transfer.child || 0;
        }
        else if (transportMode === 'flight') {
            document.getElementById('flightDepartFrom').value = transfer.departFrom || '';
            document.getElementById('flightDestination').value = transfer.destination || '';
            document.getElementById('flightTripType').value = transfer.tripType || 'return';
            // Normalize date to YYYY-MM-DD format for date input
            const normalizedDate = normalizeDateToYYYYMMDD(transfer.dateTime);
            document.getElementById('flightDepartureDate').value = normalizedDate || '';
            // Normalize return date
            const normalizedReturnDate = normalizeDateToYYYYMMDD(transfer.returnDate);
            document.getElementById('flightReturnDate').value = normalizedReturnDate || '';
            document.getElementById('flightAirline').value = transfer.airline || '';
            document.getElementById('flightNumber').value = transfer.flightNumber || '';
            document.getElementById('flightOperator').value = transfer.operator || '';
            document.getElementById('flightClass').value = transfer.class || 'economy';
            document.getElementById('flightAdults').value = transfer.adults || 2;
            document.getElementById('flightChild').value = transfer.child || 0;
            document.getElementById('flightCost').value = transfer.cost || 0;
            document.getElementById('flightSell').value = transfer.sell || 0;
            document.getElementById('flightTaxIncluded').checked = transfer.taxIncluded || false;
        }
        else if (transportMode === 'cruise') {
            document.getElementById('cruiseDepartFrom').value = transfer.departFrom || '';
            document.getElementById('cruiseFromTerminal').value = transfer.fromTerminal || '';
            document.getElementById('cruiseBy').value = transfer.by || 'cruise';
            document.getElementById('cruiseType').value = transfer.cruiseType || '';
            document.getElementById('cruiseVessel').value = transfer.vessel || '';
            document.getElementById('cruiseArrivalTo').value = transfer.arrivalTo || '';
            // Normalize date to YYYY-MM-DD format for date input
            const normalizedDate = normalizeDateToYYYYMMDD(transfer.dateTime);
            document.getElementById('cruiseDepartureDate').value = normalizedDate || '';
            // Normalize arrival date
            const normalizedArrivalDate = normalizeDateToYYYYMMDD(transfer.arrivalDate);
            document.getElementById('cruiseArrivalDate').value = normalizedArrivalDate || '';
            document.getElementById('cruiseOperator').value = transfer.operator || '';
            document.getElementById('cruiseCabinClass').value = transfer.cabinClass || 'economy';
            document.getElementById('cruiseAdults').value = transfer.adults || 2;
            document.getElementById('cruiseChild').value = transfer.child || 0;
            document.getElementById('cruiseCost').value = transfer.cost || 0;
            document.getElementById('cruiseSell').value = transfer.sell || 0;
            document.getElementById('cruiseTaxIncluded').checked = transfer.taxIncluded || false;
        }
        else if (transportMode === 'train') {
            document.getElementById('trainDepartFrom').value = transfer.departFrom || '';
            document.getElementById('trainDestination').value = transfer.destination || '';
            document.getElementById('trainTripType').value = transfer.tripType || 'return';
            // Normalize date to YYYY-MM-DD format for date input
            const normalizedDate = normalizeDateToYYYYMMDD(transfer.dateTime);
            document.getElementById('trainDepartureDate').value = normalizedDate || '';
            // Normalize return date
            const normalizedReturnDate = normalizeDateToYYYYMMDD(transfer.returnDate);
            document.getElementById('trainReturnDate').value = normalizedReturnDate || '';
            document.getElementById('trainOperator').value = transfer.operator || '';
            document.getElementById('trainClass').value = transfer.class || '1st-class';
            document.getElementById('trainStationTransfer').value = transfer.stationTransfer || '';
            document.getElementById('trainAdults').value = transfer.adults || 2;
            document.getElementById('trainChild').value = transfer.child || 0;
            document.getElementById('trainCost').value = transfer.cost || 0;
            document.getElementById('trainSell').value = transfer.sell || 0;
            document.getElementById('trainTaxIncluded').checked = transfer.taxIncluded || false;
        }
        else if (transportMode === 'bus') {
            document.getElementById('busDepartFrom').value = transfer.departFrom || '';
            document.getElementById('busDestination').value = transfer.destination || '';
            document.getElementById('busTripType').value = transfer.tripType || 'return';
            // Normalize date to YYYY-MM-DD format for date input
            const normalizedDate = normalizeDateToYYYYMMDD(transfer.dateTime);
            document.getElementById('busDepartureDate').value = normalizedDate || '';
            // Normalize return date
            const normalizedReturnDate = normalizeDateToYYYYMMDD(transfer.returnDate);
            document.getElementById('busReturnDate').value = normalizedReturnDate || '';
            document.getElementById('busOperator').value = transfer.operator || '';
            document.getElementById('busClass').value = transfer.class || 'executive';
            document.getElementById('busStationTransfer').value = transfer.stationTransfer || '';
            document.getElementById('busAdults').value = transfer.adults || 2;
            document.getElementById('busChild').value = transfer.child || 0;
            document.getElementById('busCost').value = transfer.cost || 0;
            document.getElementById('busSell').value = transfer.sell || 0;
            document.getElementById('busTaxIncluded').checked = transfer.taxIncluded || false;
        }
        
        document.getElementById('transferModalTitleText').textContent = 'Edit Transfer Package';
        document.getElementById('saveTransferBtnText').textContent = 'Update Transfer';
        
        // Initialize Select2 for pickup and drop dropdowns in transfer modal
        if (typeof $.fn.select2 !== 'undefined') {
            $('#localPickup').select2({
                placeholder: 'Search and select pickup location',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#transferModal')
            });
            $('#localDrop').select2({
                placeholder: 'Search and select drop location',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#transferModal')
            });
        }
        
        const transferModal = new bootstrap.Modal(document.getElementById('transferModal'));
        transferModal.show();
    }
    
    // Remove selected transfers
    function removeSelectedTransfers() {
        const checkboxes = document.querySelectorAll('.transfer-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Please select transfers to remove');
            return;
        }
        
        if (!confirm(`Remove ${checkboxes.length} selected transfer(s)?`)) {
            return;
        }
        
        const idsToRemove = Array.from(checkboxes).map(cb => cb.value);
        transferList = transferList.filter(transfer => !idsToRemove.includes(String(transfer.id)));
        
        updateTransferTable();
        recalculateHeaderDatesFromServices();
        recalculateTotals();
    }

    // ==================== TOTALS CALCULATION ====================
    
    // Recalculate all totals and populate footer table
    function recalculateTotals() {
        const tbody = document.getElementById('footerSummaryBody');
        let rows = [];
        
        // Calculate per-pax costs for tours, transfers, meals
        let tourCostPerPax = 0, tourSellPerPax = 0;
        let transferCostPerPax = 0, transferSellPerPax = 0;
        let childCostPerPax = 0, childSellPerPax = 0;
        
        const adults = parseInt(document.querySelector('.customer-details input[type="number"][value="2"]')?.value || 2);
        const children = parseInt(document.querySelector('.customer-details input[type="number"][value="1"]')?.value || 1);
        
        // Calculate tour costs per pax
        tourList.forEach(tour => {
            tourCostPerPax += tour.adultCost;
            tourSellPerPax += tour.adultSell;
            childCostPerPax += tour.childCost;
            childSellPerPax += tour.childSell;
        });
        
        // Calculate transfer costs per pax
        transferList.forEach(transfer => {
            const paxCount = (parseInt(transfer.adults) || 0) + (parseInt(transfer.child) || 0);
            if (paxCount > 0) {
                transferCostPerPax += (parseFloat(transfer.cost) || 0) / paxCount;
                transferSellPerPax += (parseFloat(transfer.sell) || 0) / paxCount;
            }
        });
        
        // Calculate meal costs per pax
        mealList.forEach(meal => {
            tourCostPerPax += meal.adultCost;
            tourSellPerPax += meal.adultSell;
            childCostPerPax += meal.childCost;
            childSellPerPax += meal.childSell;
        });
        
        // Add accommodation rows
        accommodationList.forEach((hotel, index) => {
            const nights = parseInt(hotel.nights) || 0;
            const roomPrice = parseFloat(hotel.roomPrice) || 0;
            const totalRoomCost = nights * roomPrice;
            
            // Calculate per-person cost based on sharing
            const singleCost = totalRoomCost + tourCostPerPax + transferCostPerPax;
            const singleSell = totalRoomCost + tourSellPerPax + transferSellPerPax;
            
            const twinCost = (totalRoomCost / 2) + tourCostPerPax + transferCostPerPax;
            const twinSell = (totalRoomCost / 2) + tourSellPerPax + transferSellPerPax;
            
            const tripleCost = (totalRoomCost / 3) + tourCostPerPax + transferCostPerPax;
            const tripleSell = (totalRoomCost / 3) + tourSellPerPax + transferSellPerPax;
            
            rows.push(`
                <tr>
                    <td style="padding: 3px 5px; border-right: 2px solid #dee2e6;">
                        <input type="checkbox" style="width: 12px; height: 12px; margin-right: 3px;">
                        ${hotel.hotelName}
                        <br><small class="text-muted" style="font-size: 8px;">${hotel.roomType || ''} | ${hotel.bedType || ''} | ${hotel.mealPlan || 'CP'}</small>
                    </td>
                    <td style="padding: 3px 5px; text-align: right; background: #fff3cd;">${singleSell.toFixed(2)}</td>
                    <td style="padding: 3px 5px; text-align: right; background: #d1ecf1;">${twinSell.toFixed(2)}</td>
                    <td style="padding: 3px 5px; text-align: right; background: #f8d7da;">${tripleSell.toFixed(2)}</td>
                    <td style="padding: 3px 5px; text-align: right; background: #d4edda;">${childSellPerPax.toFixed(2)}</td>
                    <td style="padding: 3px 5px; text-align: right; background: #e7d6f5;">${(childSellPerPax * 0.8).toFixed(2)}</td>
                    <td style="padding: 3px 5px; text-align: right; background: #e2e3e5;">0.00</td>
                </tr>
            `);
        });
        
        tbody.innerHTML = rows.join('');
    }

    // Select all accommodation
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAllAccommodation');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.accommodation-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        }

        // Select all arrival/departure
        const selectAllArrivalDeparture = document.getElementById('selectAllArrivalDeparture');
        if (selectAllArrivalDeparture) {
            selectAllArrivalDeparture.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.arrivalDeparture-checkbox');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        }

        // Initialize Select2 for port dropdowns - will be initialized per modal
        // Removed global initialization to allow per-modal initialization

        // Auto-load agents if agency is pre-selected from initial data
        @if(isset($initialData) && isset($initialData['agency_id']))
        const agencyId = {{ $initialData['agency_id'] }};
        if (agencyId) {
            // Trigger the loadAgentsByAgency function after a short delay
            setTimeout(function() {
                loadAgentsByAgency();
            }, 500);
        }
        @endif
    });

    // Collapse sidebar on page load for Enquiry Pro
    window.addEventListener('load', function() {
        setTimeout(function() {
            const html = document.documentElement;
            const layoutMenu = document.querySelector('.layout-menu');
            const menuToggle = document.querySelector('.layout-menu-toggle');
            
            // Check if Helpers object exists (from helpers.js)
            if (typeof Helpers !== 'undefined' && Helpers.toggleCollapsed) {
                // Use the template's built-in collapse function
                if (!html.classList.contains('layout-menu-collapsed')) {
                    Helpers.toggleCollapsed();
                }
            } else {
                // Fallback method if Helpers not available
                if (html) {
                    html.classList.remove('layout-menu-expanded');
                    html.classList.add('layout-menu-collapsed');
                }
                
                if (layoutMenu) {
                    layoutMenu.classList.add('closed');
                }
                
                // Update body/content margin
                const layoutPage = document.querySelector('.layout-page');
                if (layoutPage) {
                    layoutPage.style.marginLeft = '0';
                    layoutPage.style.marginInlineStart = '0';
                }
            }
        }, 100); // Small delay to ensure all scripts are loaded
    });

    // ==================== SAVE ENQUIRY DATA FUNCTIONS ====================
    
    // Helper function to fetch vehicle details by vehicle_id
    async function fetchVehicleDetails(vehicleId, dmcId) {
        if (!vehicleId || !dmcId) {
            return {
                vehicles_name: "",
                vehicle_type: "",
                vehicle_model: "",
                model_year: "",
                image: ""
            };
        }
        
        try {
            const response = await fetch(`/api/vehicle-details?vehicle_id=${vehicleId}&dmc_id=${dmcId}&mode=dmc`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                console.error('Failed to fetch vehicle details:', response.statusText);
                return {
                    vehicles_name: "",
                    vehicle_type: "",
                    vehicle_model: "",
                    model_year: "",
                    image: ""
                };
            }
            
            const data = await response.json();
            return {
                vehicles_name: data.vehicle_name || "",
                vehicle_type: data.vehicle_type || "",
                vehicle_model: data.vehicle_model || "",
                model_year: data.model_year || "",
                image: data.image || ""
            };
        } catch (error) {
            console.error('Error fetching vehicle details:', error);
            return {
                vehicles_name: "",
                vehicle_type: "",
                vehicle_model: "",
                model_year: "",
                image: ""
            };
        }
    }
    
    function getCustomerInfo() {
        return {
            fullName: document.getElementById('customerNameInput')?.value || "",
            email: "",
            phone: document.getElementById('contactNumberInput')?.value || "",
            countryCode: "",
            address1: "",
            address2: null,
            state: null,
            zip: "",
            specialRequests: null
        };
    }
    
    // Transform arrival/departure data to required format
    async function transformArrivalDepartureData() {
        const customerInfo = getCustomerInfo();
        const destination = document.getElementById('destinationSelect')?.value || 'Singapore';
        const dmcId = '{{ $dmc_id ?? "" }}';
        
        const entryPortData = [];
        const exitPortData = [];
        
        for (const item of arrivalDepartureList) {
            if (item.type === 'Arrival') {
                // Extract date and time
                let bookingDate = normalizeDateToYYYYMMDD(item.dateTime);
                let entrytime = "03:00 AM";
                
                if (item.dateTime && item.dateTime.includes('T')) {
                    const parts = item.dateTime.split('T');
                    bookingDate = parts[0];
                    if (parts[1]) {
                        const timeParts = parts[1].split(':');
                        let hours = parseInt(timeParts[0]);
                        const minutes = timeParts[1] || '00';
                        const ampm = hours >= 12 ? 'PM' : 'AM';
                        hours = hours % 12 || 12;
                        entrytime = `${String(hours).padStart(2, '0')}:${minutes} ${ampm}`;
                    }
                }
                
                // Fetch vehicle details if vehicle_id exists
                const vehicleDetails = await fetchVehicleDetails(item.vehicleId, dmcId);
                
                entryPortData.push({
                    id: item.id || `entry-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
                    bookingDate: bookingDate,
                    vehicle_id: item.vehicleId || '',
                    image: vehicleDetails.image,
                    dmc_id: dmcId,
                    vehicles_name: vehicleDetails.vehicles_name,
                    Mode: "dmc",
                    type: item.transferType || "Private",
                    vehicle_type: vehicleDetails.vehicle_type || item.vehicleType || "",
                    vehicle_model: vehicleDetails.vehicle_model,
                    model_year: vehicleDetails.model_year,
                    seating_capacity: 0,
                    travel_type: "entry_port",
                    entrypickup: item.portName || "",
                    entrydropoff: item.transferDestinationName || "",
                    PickupPlaceid: { lat: "", lng: "" },
                    DropoffPlaceid: { lat: "", lng: "" },
                    pickupdate: bookingDate,
                    entrytime: entrytime,
                    adults: parseInt(item.adultsQty) || 0,
                    children: parseInt(item.childQty) || 0,
                    componentDayIndex: 0,
                    totalPrice: parseFloat(item.adultSell) * parseInt(item.adultsQty || 0) + parseFloat(item.childSell || 0) * parseInt(item.childQty || 0),
                    Tax: 0,
                    distance: 0,
                    Night_Start_Time: null,
                    Night_End_Time: null,
                    city: destination,
                    country: destination,
                    fullName: customerInfo.fullName,
                    email: customerInfo.email,
                    phone: customerInfo.phone,
                    countryCode: customerInfo.countryCode,
                    address1: customerInfo.address1,
                    address2: customerInfo.address2,
                    state: customerInfo.state,
                    zip: customerInfo.zip,
                    specialRequests: customerInfo.specialRequests,
                    userInfo: {
                        fullName: customerInfo.fullName,
                        email: customerInfo.email,
                        phone: customerInfo.phone,
                        countryCode: customerInfo.countryCode,
                        address1: customerInfo.address1,
                        address2: customerInfo.address2,
                        state: customerInfo.state,
                        zip: customerInfo.zip,
                        specialRequests: customerInfo.specialRequests
                    },
                    bookingType: "enquiry"
                });
            } else if (item.type === 'Departure') {
                // Extract date and time
                let bookingDate = normalizeDateToYYYYMMDD(item.dateTime);
                let entrytime = "11:00 AM";
                
                if (item.dateTime && item.dateTime.includes('T')) {
                    const parts = item.dateTime.split('T');
                    bookingDate = parts[0];
                    if (parts[1]) {
                        const timeParts = parts[1].split(':');
                        let hours = parseInt(timeParts[0]);
                        const minutes = timeParts[1] || '00';
                        const ampm = hours >= 12 ? 'PM' : 'AM';
                        hours = hours % 12 || 12;
                        entrytime = `${String(hours).padStart(2, '0')}:${minutes} ${ampm}`;
                    }
                }
                
                // Fetch vehicle details if vehicle_id exists
                const vehicleDetails = await fetchVehicleDetails(item.vehicleId, dmcId);
                
                exitPortData.push({
                    fullName: customerInfo.fullName,
                    email: customerInfo.email,
                    phone: customerInfo.phone,
                    countryCode: customerInfo.countryCode,
                    address1: customerInfo.address1,
                    address2: customerInfo.address2,
                    state: customerInfo.state,
                    zip: customerInfo.zip,
                    specialRequests: customerInfo.specialRequests,
                    vehicle_id: item.vehicleId || '',
                    vehicles_name: vehicleDetails.vehicles_name,
                    dmc_id: String(dmcId),
                    Mode: "dmc",
                    type: item.transferType || "Shared",
                    image: vehicleDetails.image,
                    exitpickup: item.transferDestinationName || "",
                    exitdropoff: item.portName || "",
                    bookingDate: bookingDate,
                    exitpickupdate: bookingDate,
                    entrytime: entrytime,
                    PickupPlaceid: null,
                    DropoffPlaceid: null,
                    adults: parseInt(item.adultsQty) || 0,
                    children: parseInt(item.childQty) || 0,
                    totalPrice: parseFloat(item.adultSell || 0) * parseInt(item.adultsQty || 0) + parseFloat(item.childSell || 0) * parseInt(item.childQty || 0),
                    Tax: 0,
                    distance: 0,
                    Night_Start_Time: null,
                    Night_End_Time: null,
                    city: destination,
                    country: destination,
                    id: item.id || `exit-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
                    vehicle_type: vehicleDetails.vehicle_type || item.vehicleType || "",
                    vehicle_model: vehicleDetails.vehicle_model,
                    model_year: vehicleDetails.model_year,
                    seating_capacity: 0
                });
            }
        }
        
        return { entryPortData, exitPortData };
    }
    
    // Transform accommodation data to required hotel format
    function transformAccommodationData() {
        const customerInfo = getCustomerInfo();
        
        return accommodationList.map(hotel => {
            const hotelData = {
                fullName: customerInfo.fullName,
                email: customerInfo.email,
                phone: customerInfo.phone,
                countryCode: customerInfo.countryCode,
                address1: customerInfo.address1,
                address2: customerInfo.address2,
                state: customerInfo.state,
                zip: customerInfo.zip,
                specialRequests: customerInfo.specialRequests,
                id: hotel.id || null,
                bookingType: "enquiry",
                bookingDate: [hotel.checkIn, hotel.checkOut],
                hotelDetails: {
                    hotel_id: hotel.hotelId || "",
                    hotel_name: hotel.hotelName || "",
                    image: hotel.hotelImage || "",
                    location: hotel.location || "",
                    checkInTime: "15:00:00",
                    checkOutTime: "12:00:00",
                    cancellation_charge: null
                },
                priceMode: "dmc",
                priceModeId: '{{ $dmc_id ?? "" }}',
                rooms: hotel.rooms || [],
                totalPrice: parseFloat(hotel.totalPrice) || 0,
                tour_id: 0
            };
            
            // Add transfer_options if hotel has linked transfer
            if (hotel.transferIds && hotel.transferIds.length > 0) {
                const linkedTransfer = transferList.find(t => hotel.transferIds.includes(t.id));
                if (linkedTransfer) {
                    // For hotel transfers, pickup is the hotel name, dropoff is the destination
                    const pickupName = linkedTransfer.hotelName || hotel.hotelName || linkedTransfer.pickup || '';
                    const dropoffName = linkedTransfer.destination || linkedTransfer.dropoff || '';
                    
                    hotelData.transfer_options = {
                        transfer_required: true,
                        type: linkedTransfer.type || 'Private',
                        vehicle_id: linkedTransfer.vehicleId || '',
                        vehicle_details: {
                            vehicle_name: linkedTransfer.vehicleName || '',
                            vehicle_type: linkedTransfer.vehicleType || '',
                            seating_capacity: linkedTransfer.capacity || 0
                        },
                        cost: parseFloat(linkedTransfer.sell) || 0,
                        pickup_location_name: pickupName,
                        destination_name: dropoffName
                    };
                }
            }
            
            return hotelData;
        });
    }
    
    // Transform tour/attraction data to required format
    function transformTourData() {
        const customerInfo = getCustomerInfo();
        const dmcId = '{{ $dmc_id ?? "" }}';
        
        return tourList.map(tour => {
            const tourData = {
                fullName: customerInfo.fullName,
                email: customerInfo.email,
                phone: customerInfo.phone,
                countryCode: customerInfo.countryCode,
                address1: customerInfo.address1,
                address2: customerInfo.address2,
                state: customerInfo.state,
                zip: customerInfo.zip,
                specialRequests: customerInfo.specialRequests,
                bookingDate: normalizeDateToYYYYMMDD(tour.dateTime),
                visitTime: tour.visitTime || "16:00",
                adultCount: parseInt(tour.adultsQty) || 0,
                childCount: parseInt(tour.childQty) || 0,
                seniorCount: 0,
                AttractionId: tour.attractionId || 0,
                AttractionName: tour.attractionName || "",
                ticketId: tour.ticketId || 0,
                ticketName: tour.ticketName || "",
                ticket_details: {
                    adult_price: parseFloat(tour.adultCost) || 0,
                    child_price: parseFloat(tour.childCost) || 0,
                    senior_price: 0,
                    description: "",
                    nri: "residential"
                },
                transport: null,
                Selection: "withoutTransport",
                mode: "dmc",
                totalPrice: parseFloat(tour.adultSell || 0) * parseInt(tour.adultsQty || 0) + parseFloat(tour.childSell || 0) * parseInt(tour.childQty || 0),
                nri: "residential",
                bookingType: "enquiry",
                package_type: 0,
                package_attraction_id: 0,
                dmc_id: dmcId
            };
            
            // Add transfer_options if attraction has linked transfer
            if (tour.transferId) {
                const linkedTransfer = transferList.find(t => t.id === tour.transferId);
                if (linkedTransfer) {
                    // For attraction transfers, pickup is the attraction name, dropoff is the destination
                    const pickupName = linkedTransfer.attractionName || tour.attractionName || linkedTransfer.pickup || '';
                    const dropoffName = linkedTransfer.destination || linkedTransfer.dropoff || '';
                    
                    tourData.transfer_options = {
                        transfer_required: true,
                        type: linkedTransfer.type || 'Private',
                        vehicle_id: linkedTransfer.vehicleId || '',
                        vehicle_details: {
                            vehicle_name: linkedTransfer.vehicleName || '',
                            vehicle_type: linkedTransfer.vehicleType || '',
                            seating_capacity: linkedTransfer.capacity || 0
                        },
                        cost: parseFloat(linkedTransfer.sell) || 0,
                        pickup_location_name: pickupName,
                        destination_name: dropoffName
                    };
                }
            }
            
            // Add guide_options if attraction has linked guide
            if (tour.guideId) {
                const linkedGuide = guideList.find(g => g.id === tour.guideId);
                if (linkedGuide) {
                    tourData.guide_options = {
                        guide_required: true,
                        guide_id: linkedGuide.guide_id || '',
                        guide_name: linkedGuide.name || '',
                        hours: parseInt(linkedGuide.hours) || 2,
                        base_price: parseFloat(linkedGuide.cost) || 0,
                        total_price: parseFloat(linkedGuide.sell) || 0,
                        pickup_time: linkedGuide.time || ''
                    };
                }
            }
            
            return tourData;
        });
    }
    
    // Transform meal data to required restaurant format
    function transformMealData() {
        const customerInfo = getCustomerInfo();
        const dmcId = '{{ $dmc_id ?? "" }}';
        
        return mealList.map(meal => {
            const mealData = {
                fullName: customerInfo.fullName,
                email: customerInfo.email,
                phone: customerInfo.phone,
                countryCode: customerInfo.countryCode,
                address1: customerInfo.address1,
                address2: customerInfo.address2,
                state: customerInfo.state,
                zip: customerInfo.zip,
                specialRequests: customerInfo.specialRequests,
                bookingDate: normalizeDateToYYYYMMDD(meal.dateTime),
                visitTime: meal.visitTime || "3:30 PM",
                adultCount: parseInt(meal.adultsQty) || 0,
                childCount: parseInt(meal.childQty) || 0,
                restaurantId: meal.restaurantId || 0,
                restaurantName: meal.restaurantName || "",
                mealType: meal.mealType || "Breakfast",
                mealSpecificType: meal.mealSpecificType || "🍽️ Buffet",
                MealDescription: meal.meals || [],
                totalPrice: parseFloat(meal.adultSell || 0) * parseInt(meal.adultsQty || 0) + parseFloat(meal.childSell || 0) * parseInt(meal.childQty || 0),
                mealPrice: parseFloat(meal.adultSell || 0) * parseInt(meal.adultsQty || 0) + parseFloat(meal.childSell || 0) * parseInt(meal.childQty || 0),
                transport: null,
                transportPrice: 0,
                priceTypes: ["dmc"],
                dmc_id: String(dmcId),
                bookingType: "enquiry"
            };
            
            // Add transfer_options if meal has linked transfer
            if (meal.transferId) {
                const linkedTransfer = transferList.find(t => t.id === meal.transferId);
                if (linkedTransfer) {
                    // For restaurant/meal transfers, pickup is the restaurant name, dropoff is the destination
                    const pickupName = linkedTransfer.restaurantName || meal.restaurantName || linkedTransfer.pickup || '';
                    const dropoffName = linkedTransfer.destination || linkedTransfer.dropoff || '';
                    
                    mealData.transfer_options = {
                        transfer_required: true,
                        type: linkedTransfer.type || 'Private',
                        vehicle_id: linkedTransfer.vehicleId || '',
                        vehicle_details: {
                            vehicle_name: linkedTransfer.vehicleName || '',
                            vehicle_type: linkedTransfer.vehicleType || '',
                            seating_capacity: linkedTransfer.capacity || 0
                        },
                        cost: parseFloat(linkedTransfer.sell) || 0,
                        pickup_location_name: pickupName,
                        destination_name: dropoffName
                    };
                }
            }
            
            return mealData;
        });
    }
    
    // Transform guide data to required format
    function transformGuideData() {
        const customerInfo = getCustomerInfo();
        const dmcId = '{{ $dmc_id ?? "" }}';
        const destination = document.getElementById('destinationSelect')?.value || 'Singapore';
        
        // Only include standalone guides (not linked to attractions)
        return guideList.filter(guide => guide.isStandalone).map(guide => ({
            id: guide.id || `guide-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
            Mode: "dmc",
            dmc_Id: String(dmcId),
            fullName: customerInfo.fullName,
            email: customerInfo.email,
            phone: customerInfo.phone,
            countryCode: customerInfo.countryCode,
            address1: customerInfo.address1,
            address2: customerInfo.address2,
            state: customerInfo.state,
            zip: customerInfo.zip,
            specialRequests: customerInfo.specialRequests,
            guide_id: guide.guideId || 0,
            guide_name: guide.guideName || "",
            image: guide.image || "",
            entrytime: parseInt(guide.hours) || 2,
            adults: parseInt(guide.adultsQty) || 0,
            children: parseInt(guide.childQty) || 0,
            hours: parseInt(guide.hours) || 2,
            basePrice: parseFloat(guide.adultCost) || 0,
            surcharge: 0,
            totalPrice: parseFloat(guide.adultSell || 0) * parseInt(guide.adultsQty || 0) + parseFloat(guide.childSell || 0) * parseInt(guide.childQty || 0),
            pickupdate: normalizeDateToYYYYMMDD(guide.dateTime),
            bookingDate: normalizeDateToYYYYMMDD(guide.dateTime),
            dayIndex: 1,
            Tax: "7.00",
            city: destination,
            country: destination,
            languages: guide.languages || [],
            experience: 0,
            price: parseFloat(guide.adultSell) || 0,
            booking_id: 0
        }));
    }
    
    // Transform transfer data to required local_transport format
    async function transformTransferData() {
        const customerInfo = getCustomerInfo();
        const dmcId = '{{ $dmc_id ?? "" }}';
        const destination = document.getElementById('destinationSelect')?.value || 'Singapore';
        
        const standaloneTransfers = transferList.filter(transfer => transfer.isStandalone);
        const transformedData = [];
        
        for (const transfer of standaloneTransfers) {
            // Extract date and time
            let bookingDate = normalizeDateToYYYYMMDD(transfer.dateTime);
            let entrytime = "07:00 AM";
            
            if (transfer.dateTime && transfer.dateTime.includes('T')) {
                const parts = transfer.dateTime.split('T');
                bookingDate = parts[0];
                if (parts[1]) {
                    const timeParts = parts[1].split(':');
                    let hours = parseInt(timeParts[0]);
                    const minutes = timeParts[1] || '00';
                    const ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12 || 12;
                    entrytime = `${String(hours).padStart(2, '0')}:${minutes} ${ampm}`;
                }
            }
            
            // Fetch vehicle details if vehicle_id exists
            const vehicleDetails = await fetchVehicleDetails(transfer.vehicleId, dmcId);
            
            // Determine pickup and dropoff based on transfer source type
            // For restaurant/hotel/attraction transfers: pickup is the service name, dropoff is the destination
            const pickupName = transfer.restaurantName || transfer.hotelName || transfer.attractionName || transfer.pickup || "";
            const dropoffName = transfer.destination || transfer.dropoff || "";
            
            transformedData.push({
                bookingDate: bookingDate,
                vehicle_id: transfer.vehicleId || '',
                vehicles_name: vehicleDetails.vehicles_name,
                dmc_id: String(dmcId),
                image: vehicleDetails.image,
                Mode: "dmc",
                type: transfer.type || "Private",
                entrypickup: pickupName,
                entrydropoff: dropoffName,
                PickupPlaceid: "",
                DropoffPlaceid: "",
                pickupdate: bookingDate,
                entrytime: entrytime,
                adults: String(parseInt(transfer.adults) || 0),
                children: String(parseInt(transfer.child) || 0),
                totalPrice: String(parseFloat(transfer.sell) || 0),
                to_zone_id: "",
                from_zone_id: "",
                city: destination,
                country: destination,
                fullName: customerInfo.fullName,
                email: customerInfo.email,
                phone: customerInfo.phone,
                countryCode: customerInfo.countryCode,
                address1: customerInfo.address1,
                address2: customerInfo.address2,
                state: customerInfo.state,
                zip: customerInfo.zip,
                specialRequests: customerInfo.specialRequests,
                userInfo: {
                    fullName: customerInfo.fullName,
                    email: customerInfo.email,
                    phone: customerInfo.phone,
                    countryCode: customerInfo.countryCode,
                    address1: customerInfo.address1
                },
                bookingType: "enquiry",
                vehicle_type: vehicleDetails.vehicle_type,
                vehicle_model: vehicleDetails.vehicle_model,
                model_year: vehicleDetails.model_year
            });
        }
        
        return transformedData;
    }
    
    // Transform miscellaneous data (new format - keep flexible)
    function transformMiscellaneousData() {
        const customerInfo = getCustomerInfo();
        const dmcId = '{{ $dmc_id ?? "" }}';
        const destination = document.getElementById('destinationSelect')?.value || 'Singapore';
        
        return miscList.map(misc => ({
            id: misc.id || `misc-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
            bookingDate: normalizeDateToYYYYMMDD(misc.dateTime),
            itemName: misc.itemName || "",
            adultsQty: parseInt(misc.adultsQty) || 0,
            adultCost: parseFloat(misc.adultCost) || 0,
            adultSell: parseFloat(misc.adultSell) || 0,
            childQty: parseInt(misc.childQty) || 0,
            childCost: parseFloat(misc.childCost) || 0,
            childSell: parseFloat(misc.childSell) || 0,
            infantQty: parseInt(misc.infantQty) || 0,
            infantCost: parseFloat(misc.infantCost) || 0,
            infantSell: parseFloat(misc.infantSell) || 0,
            totalPrice: (parseFloat(misc.adultSell || 0) * parseInt(misc.adultsQty || 0)) + 
                       (parseFloat(misc.childSell || 0) * parseInt(misc.childQty || 0)) +
                       (parseFloat(misc.infantSell || 0) * parseInt(misc.infantQty || 0)),
            dmc_id: dmcId,
            city: destination,
            country: destination,
            fullName: customerInfo.fullName,
            email: customerInfo.email,
            phone: customerInfo.phone,
            countryCode: customerInfo.countryCode,
            address1: customerInfo.address1,
            address2: customerInfo.address2,
            state: customerInfo.state,
            zip: customerInfo.zip,
            specialRequests: customerInfo.specialRequests,
            bookingType: "enquiry"
        }));
    }
    
    async function saveEnquiryData() {
        // Get values from header fields
        const destinationSelect = document.getElementById('destinationSelect');
        const destination = destinationSelect?.value || '';
        const startDate = document.getElementById('tourStartDate')?.value;
        const endDate = document.getElementById('tourEndDate')?.value;
        const adults = parseInt(document.getElementById('adultCountInput')?.value) || 0;
        const children = parseInt(document.getElementById('childCountInput')?.value) || 0;
        const infants = parseInt(document.getElementById('infantCountInput')?.value) || 0;
        const agentId = document.getElementById('agentSelect')?.value;
        const agencyId = document.getElementById('agencySelect')?.value;
        const male = parseInt(document.getElementById('adultManInput')?.value) || 0;
        const female = parseInt(document.getElementById('adultWomenInput')?.value) || 0;
        const city = null; // Not used in current form
        const childAges = null; // Can be collected from child details if needed
        
        // Validate required fields
        if (!destination || destination.trim() === '') {
            alert('Please enter a destination');
            return;
        }
        if (!startDate || !endDate) {
            alert('Please select start and end dates');
            return;
        }
        if (!agentId || !agencyId) {
            alert('Please select agency and agent');
            return;
        }
        
        // Prepare data to send
        const formData = new FormData();
        formData.append('destination', destination);
        formData.append('start_date', startDate);
        formData.append('end_date', endDate);
        formData.append('adults', adults);
        formData.append('children', children);
        formData.append('infants', infants);
        formData.append('agent_id', agentId);
        formData.append('agency_id', agencyId);
        formData.append('male', male);
        formData.append('female', female);
        if (city) formData.append('city', city);
        if (childAges) formData.append('child_ages', childAges);
        
        // Transform and add service data in required format (await async functions)
        const { entryPortData, exitPortData } = await transformArrivalDepartureData();
        const transformedAccommodations = transformAccommodationData();
        const transformedTours = transformTourData();
        const transformedMeals = transformMealData();
        const transformedGuides = transformGuideData();
        const transformedTransfers = await transformTransferData();
        const transformedMiscellaneous = transformMiscellaneousData();
        
        // Add entry_port (Arrival)
        if (entryPortData.length > 0) {
            formData.append('entry_port', JSON.stringify(entryPortData));
        }
        
        // Add exit_port (Departure)
        if (exitPortData.length > 0) {
            formData.append('exit_port', JSON.stringify(exitPortData));
        }
        
        // Add hotel (Accommodation)
        if (transformedAccommodations.length > 0) {
            formData.append('accommodations', JSON.stringify(transformedAccommodations));
        }
        
        // Add attraction (Tours)
        if (transformedTours.length > 0) {
            formData.append('tours', JSON.stringify(transformedTours));
        }
        
        // Add restaurant (Meals)
        if (transformedMeals.length > 0) {
            formData.append('meals', JSON.stringify(transformedMeals));
        }
        
        // Add guide
        if (transformedGuides.length > 0) {
            formData.append('guides', JSON.stringify(transformedGuides));
        }
        
        // Add local_transport (Transfers)
        if (transformedTransfers.length > 0) {
            formData.append('transfers', JSON.stringify(transformedTransfers));
        }
        
        // Add miscellaneous
        if (transformedMiscellaneous.length > 0) {
            formData.append('miscellaneous', JSON.stringify(transformedMiscellaneous));
        }
        
        // Show loading modal and disable form
        const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
        loadingModal.show();
        
        // Disable all form inputs
        const allInputs = document.querySelectorAll('input, select, textarea, button');
        allInputs.forEach(input => input.disabled = true);
        
        // Send to server
        fetch('{{ route("enquiry-form-pro.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Hide loading modal
            loadingModal.hide();
            
            if (data.success) {
                // Re-enable form inputs so the success modal button works
                allInputs.forEach(input => input.disabled = false);
                
                // Show success modal with animation
                document.getElementById('successTourId').textContent = `Tour ID: ${data.display_id}`;
                document.getElementById('successOrderCount').textContent = `${data.total_orders} order(s) created successfully`;
                
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
            } else {
                // Re-enable form on error
                allInputs.forEach(input => input.disabled = false);
                alert('Error: ' + (data.message || 'Failed to save tour'));
            }
        })
        .catch(error => {
            // Hide loading modal
            loadingModal.hide();
            
            // Re-enable form on error
            allInputs.forEach(input => input.disabled = false);
            
            console.error('Error:', error);
            alert('An error occurred while saving the tour. Please try again.');
        });
    }
    
    // Redirect to dashboard
    function redirectToDashboard() {
        window.location.href = '{{ route("dashboard") }}';
    }
    
    async function generateEntryPortData() {
        const customerInfo = getCustomerInfo();
        const destination = document.getElementById('destinationSelect')?.value || 'Singapore';
        const dmcId = '{{ $dmc_id ?? "" }}';
        const arrivals = arrivalDepartureList.filter(item => item.type === 'Arrival');
        const entryData = [];
        
        for (const item of arrivals) {
            // Extract date and time from dateTime
            let bookingDate = item.dateTime;
            let entrytime = "03:00 AM";
            
            if (bookingDate && bookingDate.includes('T')) {
                const parts = bookingDate.split('T');
                bookingDate = parts[0];
                if (parts[1]) {
                    // Convert 24h time to 12h format
                    const timeParts = parts[1].split(':');
                    let hours = parseInt(timeParts[0]);
                    const minutes = timeParts[1] || '00';
                    const ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12 || 12;
                    entrytime = `${hours}:${minutes} ${ampm}`;
                }
            }
            
            // Fetch vehicle details if vehicle_id exists
            const vehicleDetails = await fetchVehicleDetails(item.vehicleId, dmcId);
            
            entryData.push({
                id: `entry-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
                bookingDate: bookingDate,
                vehicle_id: item.vehicleId || '',
                image: vehicleDetails.image,
                dmc_id: dmcId,
                vehicles_name: vehicleDetails.vehicles_name,
                Mode: "dmc",
                type: item.transferType || "Private",
                vehicle_type: vehicleDetails.vehicle_type,
                vehicle_model: vehicleDetails.vehicle_model,
                model_year: vehicleDetails.model_year,
                seating_capacity: 0,
                travel_type: "entry_port",
                entrypickup: item.portName || "",
                entrydropoff: item.transferDestinationName || "",
                PickupPlaceid: {
                    lat: "",
                    lng: ""
                },
                DropoffPlaceid: {
                    lat: "",
                    lng: ""
                },
                pickupdate: bookingDate,
                entrytime: entrytime,
                adults: item.adultsQty || 0,
                children: item.childQty || 0,
                componentDayIndex: 0,
                totalPrice: item.amount || 0,
                Tax: 0,
                distance: 0,
                Night_Start_Time: null,
                Night_End_Time: null,
                city: destination,
                country: destination,
                fullName: customerInfo.fullName,
                email: customerInfo.email,
                phone: customerInfo.phone,
                countryCode: customerInfo.countryCode,
                address1: customerInfo.address1,
                address2: customerInfo.address2,
                state: customerInfo.state,
                zip: customerInfo.zip,
                specialRequests: customerInfo.specialRequests,
                userInfo: {
                    fullName: customerInfo.fullName,
                    email: customerInfo.email,
                    phone: customerInfo.phone,
                    countryCode: customerInfo.countryCode,
                    address1: customerInfo.address1,
                    address2: customerInfo.address2,
                    state: customerInfo.state,
                    zip: customerInfo.zip,
                    specialRequests: customerInfo.specialRequests
                },
                bookingType: "enquiry"
            });
        }
        
        return entryData;
    }
    
    async function generateExitPortData() {
        const customerInfo = getCustomerInfo();
        const destination = document.getElementById('destinationSelect')?.value || 'Singapore';
        const dmcId = '{{ $dmc_id ?? "" }}';
        const departures = arrivalDepartureList.filter(item => item.type === 'Departure');
        const exitData = [];
        
        for (const item of departures) {
            // Extract date and time from dateTime
            let bookingDate = item.dateTime;
            let entrytime = "11:00 AM";
            
            if (bookingDate && bookingDate.includes('T')) {
                const parts = bookingDate.split('T');
                bookingDate = parts[0];
                if (parts[1]) {
                    // Convert 24h time to 12h format
                    const timeParts = parts[1].split(':');
                    let hours = parseInt(timeParts[0]);
                    const minutes = timeParts[1] || '00';
                    const ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12 || 12;
                    entrytime = `${hours}:${minutes} ${ampm}`;
                }
            }
            
            // Fetch vehicle details if vehicle_id exists
            const vehicleDetails = await fetchVehicleDetails(item.vehicleId, dmcId);
            
            exitData.push({
                id: `exit-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
                bookingDate: bookingDate,
                vehicle_id: item.vehicleId || '',
                vehicles_name: vehicleDetails.vehicles_name,
                dmc_id: dmcId,
                Mode: "dmc",
                type: item.transferType || "Private",
                image: vehicleDetails.image,
                travel_type: "exit_port",
                vehicle_type: vehicleDetails.vehicle_type || item.vehicleType || "",
                vehicle_model: vehicleDetails.vehicle_model,
                model_year: vehicleDetails.model_year || 0,
                seating_capacity: 0,
                exitpickup: item.transferDestinationName || "",
                exitdropoff: item.portName || "",
                PickupPlaceid: {
                    lat: "",
                    lng: ""
                },
                DropoffPlaceid: {
                    lat: "",
                    lng: ""
                },
                exitpickupdate: bookingDate,
                entrytime: entrytime,
                adults: item.adultsQty || 0,
                children: item.childQty || 0,
                totalPrice: item.amount || 0,
                Tax: 0,
                distance: 0,
                Night_Start_Time: null,
                Night_End_Time: null,
                city: destination,
                country: destination,
                fullName: customerInfo.fullName,
                email: customerInfo.email,
                phone: customerInfo.phone,
                countryCode: customerInfo.countryCode,
                address1: customerInfo.address1,
                address2: customerInfo.address2,
                state: customerInfo.state,
                zip: customerInfo.zip,
                specialRequests: customerInfo.specialRequests
            });
        }
        
        return exitData;
    }
    
    function generateGuideData() {
        const customerInfo = getCustomerInfo();
        const destination = document.getElementById('destinationSelect')?.value || 'Singapore';
        return guideList.map(guide => {
            // Extract date and time from dateTime
            let bookingDate = guide.dateTime;
            let entrytime = "09:00 AM";
            
            if (bookingDate && bookingDate.includes('T')) {
                const parts = bookingDate.split('T');
                bookingDate = parts[0];
                if (parts[1]) {
                    // Convert 24h time to 12h format
                    const timeParts = parts[1].split(':');
                    let hours = parseInt(timeParts[0]);
                    const minutes = timeParts[1] || '00';
                    const ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12 || 12;
                    entrytime = `${hours}:${minutes} ${ampm}`;
                }
            }
            
            return {
                id: guide.id,
                Mode: "dmc",
                dmc_Id: dmcId || "",
                fullName: customerInfo.fullName,
                email: customerInfo.email,
                phone: customerInfo.phone,
                countryCode: customerInfo.countryCode,
                address1: customerInfo.address1,
                address2: customerInfo.address2,
                state: customerInfo.state,
                zip: customerInfo.zip,
                specialRequests: customerInfo.specialRequests,
                guide_id: parseInt(guide.guide_id) || 0,
                guide_name: guide.name || "",
                image: "",
                entrypickup: entrytime,
                entrytime: entrytime,
                adults: parseInt(guide.adultsQty) || 0,
                children: parseInt(guide.childQty) || 0,
                hours: guide.hours || 2,
                basePrice: guide.cost || 20,
                surcharge: 0,
                totalPrice: guide.sell || 20,
                pickupdate: bookingDate,
                bookingDate: bookingDate,
                dayIndex: 1,
                Tax: "7.00",
                city: destination,
                country: destination,
                languages: guide.languages ? guide.languages.split(',').map(l => l.trim()) : [],
                experience: 0,
                price: guide.sell || 20
            };
        });
    }
    
    function generateRestaurantData() {
        const customerInfo = getCustomerInfo();
        const dmcId = '{{ $dmc_id ?? "" }}';
        return mealList.map(meal => {
            // Find linked transfer if exists
            const linkedTransfer = meal.transferId ? transferList.find(t => t.id === meal.transferId) : null;
            
            // Extract date and time from dateTime
            let bookingDate = meal.dateTime;
            let visitTime = "12:00 PM"; // Default lunch time
            
            if (bookingDate && bookingDate.includes('T')) {
                const parts = bookingDate.split('T');
                bookingDate = parts[0];
                if (parts[1]) {
                    // Convert 24h time to 12h format
                    const timeParts = parts[1].split(':');
                    let hours = parseInt(timeParts[0]);
                    const minutes = timeParts[1] || '00';
                    const ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12 || 12;
                    visitTime = `${hours}:${minutes} ${ampm}`;
                }
            }
            
            return {
                fullName: customerInfo.fullName,
                email: customerInfo.email,
                phone: customerInfo.phone,
                countryCode: customerInfo.countryCode,
                address1: customerInfo.address1,
                address2: customerInfo.address2,
                state: customerInfo.state,
                zip: customerInfo.zip,
                specialRequests: customerInfo.specialRequests,
                bookingDate: bookingDate,
                visitTime: visitTime,
                adultCount: meal.adultsQty || 0,
                childCount: meal.childQty || 0,
                restaurantId: parseInt(meal.restaurantId) || 0,
                restaurantName: meal.restaurantName || "",
                mealType: meal.mealType || "Lunch",
                mealSpecificType: "📋 Set Menu",
                MealDescription: [{
                    item_name: "Menu Item",
                    name: "Menu Item",
                    price: 0,
                    meal_id: parseInt(meal.restaurantId) || 0,
                    category: "",
                    item_type: "",
                    quantity: 1
                }],
                totalPrice: 0,
                mealPrice: 0,
                transport: null,
                transportPrice: 0,
                priceTypes: ["dmc"],
                dmc_id: dmcId || "",
                bookingType: "enquiry",
                transfer_options: linkedTransfer ? {
                    transfer_required: true,
                    type: linkedTransfer.type || "Private",
                    way: linkedTransfer.way || "One Way",
                    vehicle_id: linkedTransfer.vehicleId || "",
                    vehicle_details: linkedTransfer.vehicleId ? {
                        vehicle_id: linkedTransfer.vehicleId,
                        vehicle_name: linkedTransfer.vehicleName || "",
                        vehicle_type: linkedTransfer.vehicleType || "",
                        seating_capacity: linkedTransfer.seatingCapacity || "",
                        private_price: linkedTransfer.privatePrice || "0.00",
                        shared_price: linkedTransfer.sharedPrice || "0.00"
                    } : null,
                    cost: linkedTransfer.cost || 0,
                    pickup_location_id: linkedTransfer.pickupLocationId || "",
                    pickup_location_name: linkedTransfer.pickupLocationName || ""
                } : null
            };
        });
    }
    
    function generateAttractionData() {
        const customerInfo = getCustomerInfo();
        const dmcId = '{{ $dmc_id ?? "" }}';
        return tourList.map(tour => {
            // Find linked transfer if exists
            const linkedTransfer = tour.transferId ? transferList.find(t => t.id === tour.transferId) : null;
            const linkedGuide = tour.guideId ? guideList.find(g => g.id === tour.guideId) : null;
            
            // Extract date and time from dateTime
            let bookingDate = tour.dateTime;
            let visitTime = "10:00 - 21:00";
            
            if (bookingDate && bookingDate.includes('T')) {
                const parts = bookingDate.split('T');
                bookingDate = parts[0];
                if (parts[1]) {
                    // Convert 24h time to 12h format for display
                    const timeParts = parts[1].split(':');
                    let hours = parseInt(timeParts[0]);
                    const minutes = timeParts[1] || '00';
                    const ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12 || 12;
                    visitTime = `${hours}:${minutes} ${ampm}`;
                }
            }
            
            return {
                fullName: customerInfo.fullName,
                email: customerInfo.email,
                phone: customerInfo.phone,
                countryCode: customerInfo.countryCode,
                address1: customerInfo.address1,
                address2: customerInfo.address2,
                state: customerInfo.state,
                zip: customerInfo.zip,
                specialRequests: customerInfo.specialRequests,
                bookingDate: bookingDate,
                visitTime: visitTime,
                adultCount: tour.adultsQty || 0,
                childCount: tour.childQty || 0,
                seniorCount: 0,
                AttractionId: parseInt(tour.attractionId) || 0,
                AttractionName: tour.attractionName || "",
                ticketId: parseInt(tour.ticketId) || 0,
                ticketName: tour.ticketName || "",
                ticket_details: {
                    adult_price: tour.adultSell || 0,
                    child_price: tour.childSell || 0,
                    senior_price: 0,
                    description: "",
                    nri: "residential"
                },
                transport: null,
                Selection: linkedTransfer ? "withTransport" : "withoutTransport",
                mode: "dmc",
                totalPrice: (tour.adultSell * tour.adultsQty) + (tour.childSell * tour.childQty) || 0,
                nri: "residential",
                price: (tour.adultSell * tour.adultsQty) + (tour.childSell * tour.childQty) || 0,
                prices: {
                    price: (tour.adultSell * tour.adultsQty) + (tour.childSell * tour.childQty) || 0
                },
                dmc_id: dmcId || "",
                created_by_dmc: dmcId || "",
                user_id: "{{ auth()->user()->userId ?? '' }}",
                user_role: "{{ auth()->user()->role_id ?? '' }}",
                bookingType: "booking",
                package_type: 0,
                package_attraction_id: null,
                transfer_options: linkedTransfer ? {
                    transfer_required: true,
                    type: linkedTransfer.type || "Private",
                    way: linkedTransfer.way || "One Way",
                    vehicle_id: linkedTransfer.vehicleId || "",
                    vehicle_details: linkedTransfer.vehicleId ? {
                        vehicle_id: linkedTransfer.vehicleId,
                        vehicle_name: linkedTransfer.vehicleName || "",
                        vehicle_type: linkedTransfer.vehicleType || "",
                        seating_capacity: linkedTransfer.seatingCapacity || "",
                        private_price: linkedTransfer.privatePrice || "0.00",
                        shared_price: linkedTransfer.sharedPrice || "0.00"
                    } : null,
                    cost: linkedTransfer.cost || 0,
                    pickup_location_id: linkedTransfer.pickupLocationId || "",
                    pickup_location_name: linkedTransfer.pickupLocationName || ""
                } : null,
                guide_options: linkedGuide ? {
                    guide_required: true,
                    guide_id: linkedGuide.guide_id || "",
                    guide_name: linkedGuide.name || "",
                    pickup_time: "",
                    package_hours: linkedGuide.hours || "2",
                    base_price: linkedGuide.cost || 0,
                    hours: linkedGuide.hours || 2,
                    surcharge: 0,
                    total_price: linkedGuide.sell || 0
                } : null
            };
        });
    }
    
    function generateHotelData() {
        const customerInfo = getCustomerInfo();
        const dmcId = '{{ $dmc_id ?? "" }}';
        // Group accommodations by hotel and date range
        const hotelGroups = {};
        accommodationList.forEach(hotel => {
            const key = `${hotel.hotelName}_${hotel.checkIn}_${hotel.checkOut}`;
            if (!hotelGroups[key]) {
                hotelGroups[key] = {
                    hotel: hotel,
                    rooms: []
                };
            }
            hotelGroups[key].rooms.push(hotel);
        });
        
        return Object.values(hotelGroups).map(group => {
            // Find linked transfer for this hotel
            const linkedTransfer = group.hotel.transferIds && group.hotel.transferIds.length > 0 
                ? transferList.find(t => group.hotel.transferIds.includes(t.id))
                : null;
            
            // Extract date and time from checkIn and checkOut
            let checkInDate = group.hotel.checkIn;
            let checkOutDate = group.hotel.checkOut;
            let checkInTime = "11:00:00";
            let checkOutTime = "10:00:00";
            
            // If checkIn/checkOut contain time (datetime-local format), extract them
            if (checkInDate && checkInDate.includes('T')) {
                const parts = checkInDate.split('T');
                checkInDate = parts[0];
                checkInTime = parts[1] ? parts[1] + ':00' : "11:00:00";
            }
            if (checkOutDate && checkOutDate.includes('T')) {
                const parts = checkOutDate.split('T');
                checkOutDate = parts[0];
                checkOutTime = parts[1] ? parts[1] + ':00' : "10:00:00";
            }
            
            return {
                fullName: customerInfo.fullName,
                email: customerInfo.email,
                phone: customerInfo.phone,
                countryCode: customerInfo.countryCode,
                address1: customerInfo.address1,
                address2: customerInfo.address2,
                state: customerInfo.state,
                zip: customerInfo.zip,
                specialRequests: customerInfo.specialRequests,
                id: null,
                bookingType: "enquiry",
                bookingDate: [checkInDate, checkOutDate],
                checkInTime: checkInTime,
                checkOutTime: checkOutTime,
                hotelDetails: {
                    hotel_id: group.hotel.hotel_unique_id || group.hotel.hotelId || "",
                    hotel_name: group.hotel.hotelName || "",
                    image: group.hotel.image || "",
                    location: group.hotel.destination || "Singapore",
                    checkInTime: checkInTime,
                    checkOutTime: checkOutTime,
                    cancellation_charge: null
                },
                priceMode: "dmc",
                priceModeId: dmcId || "",
                rooms: group.rooms.map((room, idx) => ({
                    room_id: `room_${Date.now()}_${idx}`,
                    room_type: room.roomType || "",
                    number_of_rooms: room.rooms || 1,
                    beds: [{
                        bed_id: String(room.roomId || ""),
                        bed_type: room.bedType || "",
                        baby_cot: 0,
                        head_count: room.adultsPerRoom || 2,
                        max_occupancy: room.maxOccupancy || 2,
                        price: room.roomPrice || 0,
                        mealTypes: [room.mealPlan || "EP"],
                        selectedMeals: {
                            meal_1: {
                                type: room.mealPlan || "EP",
                                price: 0
                            }
                        }
                    }]
                })),
                totalPrice: group.rooms.reduce((sum, room) => sum + (room.roomPrice * room.rooms || 0), 0),
                price: group.rooms.reduce((sum, room) => sum + (room.roomPrice * room.rooms || 0), 0),
                transfer_options: linkedTransfer ? {
                    transfer_required: true,
                    type: linkedTransfer.type || "Private",
                    way: linkedTransfer.way || "One Way",
                    vehicle_id: linkedTransfer.vehicleId || "",
                    vehicle_details: linkedTransfer.vehicleId ? {
                        vehicle_id: linkedTransfer.vehicleId,
                        vehicle_name: linkedTransfer.vehicleName || "",
                        vehicle_type: linkedTransfer.vehicleType || "",
                        seating_capacity: linkedTransfer.seatingCapacity || "",
                        private_price: linkedTransfer.privatePrice || "0.00",
                        shared_price: linkedTransfer.sharedPrice || "0.00"
                    } : null,
                    cost: linkedTransfer.cost || 0,
                    destination_id: linkedTransfer.destinationId || "",
                    destination_name: linkedTransfer.destinationName || ""
                } : null,
                tour_id: ""
            };
        });
    }
    
    async function generateLocalTransferData() {
        const customerInfo = getCustomerInfo();
        const destination = document.getElementById('destinationSelect')?.value || 'Singapore';
        const dmcId = '{{ $dmc_id ?? "" }}';
        const standaloneTransfers = transferList.filter(transfer => transfer.isStandalone === true);
        const localTransferData = [];
        
        for (const transfer of standaloneTransfers) {
            // Extract date and time from dateTime
            let bookingDate = transfer.dateTime;
            let entrytime = "11:00 AM";
            
            if (bookingDate && bookingDate.includes('T')) {
                const parts = bookingDate.split('T');
                bookingDate = parts[0];
                if (parts[1]) {
                    // Convert 24h time to 12h format
                    const timeParts = parts[1].split(':');
                    let hours = parseInt(timeParts[0]);
                    const minutes = timeParts[1] || '00';
                    const ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12 || 12;
                    entrytime = `${hours}:${minutes} ${ampm}`;
                }
            }
            
            // Fetch vehicle details if vehicle_id exists
            const vehicleDetails = await fetchVehicleDetails(transfer.vehicleId, dmcId);
            
            // Determine pickup and dropoff based on transfer source type
            // For restaurant/hotel/attraction transfers: pickup is the service name, dropoff is the destination
            const pickupName = transfer.restaurantName || transfer.hotelName || transfer.attractionName || transfer.pickup || "";
            const dropoffName = transfer.destination || transfer.dropoff || "";
            
            localTransferData.push({
                id: `local-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
                Mode: "dmc",
                dmc_id: dmcId,
                fullName: customerInfo.fullName,
                email: customerInfo.email,
                phone: customerInfo.phone,
                country: destination,
                countryCode: customerInfo.countryCode,
                state: customerInfo.state,
                city: destination,
                zip: customerInfo.zip,
                address1: customerInfo.address1,
                address2: customerInfo.address2,
                bookingDate: bookingDate,
                pickupdate: bookingDate,
                entrytime: entrytime,
                vehicle_id: transfer.vehicleId || '',
                vehicles_name: vehicleDetails.vehicles_name,
                type: transfer.type || "Private",
                travel_type: "local_transfer",
                adults: transfer.adults || 0,
                children: transfer.child || 0,
                specialRequests: customerInfo.specialRequests,
                image: vehicleDetails.image,
                totalPrice: transfer.sell || 80,
                componentDayIndex: 0,
                entrypickup: pickupName,
                entrydropoff: dropoffName,
                PickupPlaceid: {
                    lat: "",
                    lng: ""
                },
                DropoffPlaceid: {
                    lat: "",
                    lng: ""
                },
                distance: 0,
                Tax: 0,
                Night_Start_Time: null,
                Night_End_Time: null,
                bookingType: "enquiry",
                vehicle_type: vehicleDetails.vehicle_type,
                vehicle_model: vehicleDetails.vehicle_model,
                model_year: vehicleDetails.model_year
            });
        }
        
        return localTransferData;
    }
    
    function copyJsonToClipboard() {
        const jsonText = document.getElementById('jsonOutput').textContent;
        navigator.clipboard.writeText(jsonText).then(() => {
            alert('JSON copied to clipboard!');
        }).catch(err => {
            console.error('Failed to copy:', err);
            alert('Failed to copy to clipboard');
        });
    }
    
    function downloadJson() {
        const jsonText = document.getElementById('jsonOutput').textContent;
        const blob = new Blob([jsonText], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `enquiry_data_${Date.now()}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
</script>
@endsection

