@extends('layouts.layout')
@extends('layouts.datatablecss')
@section('content')
<style>
    .card .card-body .input-group-text {
        background: #f4f6fa;
        border: none;
    }
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 2;
    }
    .table-striped > tbody > tr:nth-of-type(odd) {
        --bs-table-accent-bg: #f8f9fa;
    }
    /* Simple 3D Period Display - More Reliable */
    .period-display-simple {
        font-size: 0.85rem;
        text-transform: capitalize;
        background: linear-gradient(145deg, #f0f2f5, #ffffff);
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        box-shadow: 
            0 2px 4px rgba(0,0,0,0.05),
            inset 0 1px 0 #fff;
        position: relative;
        transition: all 0.3s ease;
        text-align: left;
    }
    
    .period-display-simple:hover {
        transform: translateY(-1px);
        box-shadow: 
            0 4px 8px rgba(0,0,0,0.1),
            inset 0 1px 0 #fff;
    }

    .period-display-simple .main-date {
        display: block;
        font-weight: 700;
        color: #0d3f72;
        font-size: 0.9rem;
        text-shadow: 0 1px 1px #fff;
    }

    .period-display-simple .date-range {
        display: block;
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 2px;
    }
    
    /* Alternative: Even simpler version if the above doesn't work */
    .period-display-basic {
        font-weight: 700;
        color: #6c757d;
        font-size: 0.85rem;
        background: #f8f9fa;
        padding: 6px 10px;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    /* Enhanced card styling for better 3D effect */
    .sales-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 
            0 2px 12px rgba(99,102,241,0.06),
            0 4px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05);
        position: relative;
        overflow: hidden;
    }
    
    .sales-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #6366f1, #8b5cf6, #6366f1);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .sales-card:hover {
        transform: translateY(-5px);
        box-shadow: 
            0 8px 25px rgba(99,102,241,0.15),
            0 4px 20px rgba(0,0,0,0.1);
        border-color: #6366f1;
    }
    
    .sales-card:hover::before {
        opacity: 1;
    }

    #hiddenExportTable_wrapper {
        visibility: hidden !important;
    }
    #hiddenExportTable{
        display: none !important;
    }
</style>
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4">
            <!-- Summary Cards -->
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="ri-money-dollar-circle-line ri-2x"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-muted small">Total Revenue</div>
                            <div class="fs-5 fw-semibold">
                                ₹{{ number_format($groupedResults->sum('total_revenue'), 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="ri-user-3-line ri-2x"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-muted small">Total Agents</div>
                            <div class="fs-5 fw-semibold">
                                {{ $groupedResults->pluck('agent_name')->unique()->count() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                @php
                    use Carbon\Carbon;
                    $start = Carbon::parse($startDate)->translatedFormat('l, F d, Y');
                    $end = Carbon::parse($endDate)->translatedFormat('l, F d, Y');
                @endphp

                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex flex-column gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="ri-calendar-line ri-xl"></i>
                            </div>
                            <div>
                                <small class="text-muted text-uppercase fw-semibold">Reporting Period</small>
                                <div class="text-dark fw-bold mt-1 small">  
                                    {{ $start }} <br class="d-sm-none">– {{ $end }}
                                </div>
                            </div>
                        </div>
                        <div class="border-top mt-3 pt-2 text-end">
                            <span class="badge bg-light text-dark small">
                                {{ \Str::ucfirst($groupBy) }} View
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="row mb-4">
            <div class="col-12">
                <!-- Filter Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('reports.sales-revenue') }}" class="row g-3 align-items-end" id="salesRevenueFilterForm">
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ri-calendar-line"></i></span>
                                    <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" class="form-control" required aria-label="Start Date" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ri-calendar-line"></i></span>
                                    <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" class="form-control" required aria-label="End Date" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="group_by" class="form-label">Group By</label>
                                <select name="group_by" id="group_by" class="form-select" aria-label="Group By">
                                    <option value="daily" {{ $groupBy == 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="monthly" {{ $groupBy == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="yearly" {{ $groupBy == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary w-100"><i class="ri-filter-3-line me-1"></i>Filter</button>
                                <!-- Export Dropdown Button -->
                                <div class="dropdown w-100">
                                    <button class="btn btn-warning btn-sm dropdown-toggle w-100" type="button" id="exportDropdown"
                                        data-bs-toggle="dropdown" aria-expanded="false" data-bs-toggle="tooltip" data-bs-placement="top" title="Export Table Data">
                                        <i class="fas fa-download"></i> Export
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportCopy" title="Copy to clipboard">Copy</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportCSV" title="Export as CSV">CSV</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportExcel" title="Export as Excel">Excel</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportPDF" title="Export as PDF">PDF</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0);" id="exportPrint" title="Print Table">Print</a></li>
                                    </ul>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <x-alert />
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="ri-bar-chart-line text-primary me-2"></i>
                                Sales & Revenue Details
                            </h5>
                            <div class="d-flex gap-2">
                                <span class="badge bg-primary-subtle text-primary">
                                    <i class="ri-database-2-line me-1"></i>
                                    {{ count($groupedResults) }} Records
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(count($groupedResults) > 0)
                            <div class="row g-4">
                                @foreach($groupedResults as $index => $group)
                                    <div class="col-lg-6 col-xl-4">
                                        <div class="sales-card h-100 p-3 rounded shadow-sm border position-relative">
                                            <!-- Card Header with Period -->
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="period-display-simple">
                                                    @php
                                                        try {
                                                            $period = $group['period'];
                                                            $displayDate = '';
                                                            $dateRange = '';

                                                            if ($groupBy == 'daily') {
                                                                $displayDate = \Carbon\Carbon::parse($period)->format('l, F j, Y');
                                                            } elseif ($groupBy == 'monthly') {
                                                                $month = \Carbon\Carbon::parse($period);
                                                                $displayDate = $month->format('F Y');
                                                                $dateRange = 'From ' . $month->startOfMonth()->format('M d') . ' to ' . $month->endOfMonth()->format('M d, Y');
                                                            } elseif ($groupBy == 'yearly') {
                                                                $year = \Carbon\Carbon::parse($period);
                                                                $displayDate = 'Year ' . $year->format('Y');
                                                                $dateRange = 'From ' . $year->startOfYear()->format('M d, Y') . ' to ' . $year->endOfYear()->format('M d, Y');
                                                            }
                                                        } catch (Exception $e) {
                                                            $displayDate = $group['period'] ?? 'Invalid Date';
                                                            $dateRange = '';
                                                        }
                                                    @endphp
                                                    
                                                    <span class="main-date">{{ $displayDate }}</span>
                                                    @if(!empty($dateRange))
                                                        <span class="date-range">{{ $dateRange }}</span>
                                                    @endif
                                                </div>
                                                <span class="text-muted small">#{{ $index + 1 }}</span>
                                            </div>
                                            
                                            <!-- Agent Information -->
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                                    <i class="ri-user-3-line"></i>
                                                </div>
                                                <div class="ms-3">
                                                    <div class="fw-semibold">{{ $group['agent_name'] }}</div>
                                                    <div class="text-muted small">Agent</div>
                                                </div>
                                            </div>
                                            
                                            <!-- Services List -->
                                            <div class="services-section mb-3">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="ri-list-check-2 text-primary me-2"></i>
                                                    <span class="fw-semibold small">Services ({{ $group['service_count'] }})</span>
                                                </div>
                                                <div class="services-list">
                                                    @foreach($group['services'] as $service)
                                                        <div class="service-item d-flex justify-content-between align-items-center py-1">
                                                            <div class="d-flex align-items-center">
                                                                <div class="service-icon me-2">
                                                                    @switch(strtolower($service['service_type']))
                                                                        @case('hotel')
                                                                            <i class="ri-hotel-line text-primary"></i>
                                                                            @break
                                                                        @case('tour')
                                                                            <i class="ri-route-line text-success"></i>
                                                                            @break
                                                                        @case('transport')
                                                                            <i class="ri-steering-2-line text-warning"></i>
                                                                            @break
                                                                        @case('restaurant')
                                                                            <i class="ri-restaurant-2-line text-info"></i>
                                                                            @break
                                                                        @case('attraction')
                                                                            <i class="ri-landscape-line text-danger"></i>
                                                                            @break
                                                                        @default
                                                                            <i class="ri-service-line text-secondary"></i>
                                                                    @endswitch
                                                                </div>
                                                                <span class="small">{{ ucfirst($service['service_type']) }}</span>
                                                            </div>
                                                            <span class="text-muted small">₹{{ number_format($service['revenue'], 2) }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            
                                            <!-- Total Revenue Section -->
                                            <div class="total-revenue-section border-top pt-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted small">Total Revenue</span>
                                                    <div class="fs-5 fw-bold text-success">
                                                        ₹{{ number_format($group['total_revenue'], 2) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="empty-state">
                                    <div class="empty-icon mb-2">
                                        <i class="ri-bar-chart-line" style="font-size:2rem;color:#ccc;"></i>
                                    </div>
                                    <h5 class="empty-title">No Data Available</h5>
                                    <p class="empty-description text-muted">
                                        No sales and revenue data found for the selected period.
                                    </p>
                                    <button class="btn btn-primary" onclick="resetFilters()">
                                        <i class="ri-refresh-line me-1"></i>Reset Filters
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
{{-- <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script> --}}
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<!-- DataTable JS -->
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<!-- DataTables Initialization Script -->
<script>
    $(document).ready(function() {
        // Create a hidden table for export purposes
        const exportTable = `
            <table class="table table-bordered table-striped align-middle" id="hiddenExportTable" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px; text-align: center;">#</th>
                        <th>Period</th>
                        <th>Agent Name</th>
                        <th>Service Type</th>
                        <th style="text-align: right;">Total Revenue</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach($groupedResults as $index => $group)
                        @foreach($group['services'] as $service)
                            <tr>
                                <td style="text-align: center;">{{ $index + 1 }}</td>
                                <td>{{ $group['period'] }}</td>
                                <td>{{ $group['agent_name'] }}</td>
                                <td>{{ $service['service_type'] }}</td>
                                <td style="text-align: right;">{{ number_format($service['revenue'], 2) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        `;
        
        // Append the hidden table to the body
        $('body').append(exportTable);
        
        // Initialize DataTable with export buttons on the hidden table
        var table = $('#hiddenExportTable').DataTable({
            paging: false,
            searching: false,
            info: false,
            ordering: false,
            dom: 'B', // Only show buttons, no search, no pagination, no table info
            buttons: [
                'copy',
                'csv',
                'excel',
                'pdf',
                'print'
            ]
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

        // Enable Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
<!-- End DataTable JS -->
<script>
    // Enhanced date logic for 3-month range and end date restriction
    document.addEventListener('DOMContentLoaded', function() {
        const startInput = document.getElementById('start_date');
        const endInput = document.getElementById('end_date');

        function setEndDateLimits() {
            if (startInput.value) {
                // Calculate the next day after start date
                const startDate = new Date(startInput.value);
                const nextDay = new Date(startDate);
                nextDay.setDate(nextDay.getDate() + 1);
                
                // Set min for end date to next day after start date
                endInput.min = nextDay.toISOString().split('T')[0];
                
                // Set max for end date to 3 months after start date
                const maxEnd = new Date(startDate);
                maxEnd.setMonth(maxEnd.getMonth() + 3);
                
                // Handle month overflow (e.g., Jan 31 + 3 months = Apr 30)
                if (maxEnd.getDate() !== startDate.getDate()) {
                    maxEnd.setDate(0); // Set to last day of previous month
                }
                
                endInput.max = maxEnd.toISOString().split('T')[0];
                
                // If end date is before or equal to start date, reset it to next day
                if (endInput.value <= startInput.value) {
                    endInput.value = nextDay.toISOString().split('T')[0];
                }
                
                // If end date is after max, reset it to max
                if (endInput.value > endInput.max) {
                    endInput.value = endInput.max;
                }
            }
        }

        // Event listeners
        startInput.addEventListener('change', function() {
            setEndDateLimits();
            // Clear end date when start date changes
            endInput.value = '';
        });
        
        // On page load
        setEndDateLimits();
        
        // Add helper function for reset
        window.resetFilters = function() {
            startInput.value = '';
            endInput.value = '';
            endInput.min = '';
            endInput.max = '';
        };
    });
</script>
@endsection

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
{{-- <style>
    .card .card-body .input-group-text {
        background: #f4f6fa;
        border: none;
    }
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 2;
    }
    .table-striped > tbody > tr:nth-of-type(odd) {
        --bs-table-accent-bg: #f8f9fa;
    }
    /* Simple 3D Period Display - More Reliable */
    .period-display-simple {
        font-weight: 700;
        color: #6c757d;
        font-size: 0.85rem;
        text-transform: capitalize;
        background: linear-gradient(145deg, #f8f9fa, #e9ecef);
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        box-shadow: 
            0 2px 4px rgba(0,0,0,0.1),
            0 1px 2px rgba(0,0,0,0.05),
            inset 0 1px 0 rgba(255,255,255,0.8);
        text-shadow: 
            0 1px 0 #fff,
            0 2px 0 #e9ecef;
        position: relative;
        transition: all 0.3s ease;
    }
    
    .period-display-simple:hover {
        transform: translateY(-1px);
        box-shadow: 
            0 4px 8px rgba(0,0,0,0.15),
            0 2px 4px rgba(0,0,0,0.1),
            inset 0 1px 0 rgba(255,255,255,0.9);
    }
    
    /* Alternative: Even simpler version if the above doesn't work */
    .period-display-basic {
        font-weight: 700;
        color: #6c757d;
        font-size: 0.85rem;
        background: #f8f9fa;
        padding: 6px 10px;
        border-radius: 6px;
        border: 1px solid #dee2e6;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    /* Enhanced card styling for better 3D effect */
    .sales-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 
            0 2px 12px rgba(99,102,241,0.06),
            0 4px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05);
        position: relative;
        overflow: hidden;
    }
    
    .sales-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #6366f1, #8b5cf6, #6366f1);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .sales-card:hover {
        transform: translateY(-5px);
        box-shadow: 
            0 8px 25px rgba(99,102,241,0.15),
            0 4px 20px rgba(0,0,0,0.1);
        border-color: #6366f1;
    }
    
    .sales-card:hover::before {
        opacity: 1;
    }
</style> --}}
@endsection 
