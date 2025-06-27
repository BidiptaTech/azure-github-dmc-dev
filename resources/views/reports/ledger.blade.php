@extends('layouts.layout')
@extends('layouts.datatablecss')
@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row mb-4">
            <!-- Summary Cards -->
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="ri-money-dollar-circle-line ri-2x"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-muted small">Total Transactions</div>
                            <div class="fs-5 fw-semibold">
                                {{ count($results) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="ri-user-3-line ri-2x"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-muted small">Total Agents</div>
                            <div class="fs-5 fw-semibold">
                                {{ count(array_unique(array_column($results, 'agent_name'))) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="ri-exchange-dollar-line ri-2x"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-muted small">Total Amount</div>
                            <div class="fs-5 fw-semibold">
                                ₹{{ number_format(array_sum(array_column($results, 'amount')), 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
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
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <!-- Filter Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('reports.ledger') }}" class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label for="start_date" class="form-label">Start Date</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ri-calendar-line"></i></span>
                                    <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" class="form-control" required aria-label="Start Date" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="end_date" class="form-label">End Date</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ri-calendar-line"></i></span>
                                    <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" class="form-control" required aria-label="End Date" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="agent_id" class="form-label">Agent</label>
                                @if(count($agentsForDropdown) > 0)
                                    <select name="agent_id" id="agent_id" class="form-select" aria-label="Agent">
                                        <option value="">All Agents</option>
                                        @foreach($agentsForDropdown as $agent)
                                            <option value="{{ $agent->agent_id }}" {{ $agentId == $agent->agent_id ? 'selected' : '' }}>
                                                {{ $agent->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <div class="form-control bg-light text-muted d-flex align-items-center" style="height: 48px;">
                                        <i class="ri-user-forbid-line me-2"></i>
                                        <span>No agents available</span>
                                    </div>
                                    <input type="hidden" name="agent_id" value="">
                                @endif
                            </div>
                            <div class="col-md-3">
                                <label for="service_type" class="form-label">Service Type</label>
                                <select name="service_type" id="service_type" class="form-select" aria-label="Service Type">
                                    <option value="">All Services</option>
                                    <option value="hotel" {{ $serviceType == 'hotel' ? 'selected' : '' }}>Hotel</option>
                                    <option value="attraction" {{ $serviceType == 'attraction' ? 'selected' : '' }}>Attraction</option>
                                    <option value="guide" {{ $serviceType == 'guide' ? 'selected' : '' }}>Guide</option>
                                    <option value="driver" {{ $serviceType == 'driver' ? 'selected' : '' }}>Driver</option>
                                    <option value="entry_port" {{ $serviceType == 'entry_port' ? 'selected' : '' }}>Arrival</option>
                                    <option value="exit_port" {{ $serviceType == 'exit_port' ? 'selected' : '' }}>Departure</option>
                                    <option value="travel_point" {{ $serviceType == 'travel_point' ? 'selected' : '' }}>Travel Point</option>
                                    <option value="travel_hourly" {{ $serviceType == 'travel_hourly' ? 'selected' : '' }}>Travel Hourly</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end gap-2">
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
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="datatables-basic table table-bordered table-striped align-middle" id="ledgerTable" style="width:100%">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 60px; text-align: center;">#</th>
                                        <th>Date</th>
                                        <th>Booking ID</th>
                                        <th>Agent Name</th>
                                        <th>Service Type</th>
                                        <th>Customer Name</th>
                                        <th>Customer Email</th>
                                        <th style="text-align: right;">Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0">
                                    @foreach($results as $index => $row)
                                        <tr>
                                            <td style="text-align: center;">{{ $index + 1 }}</td>
                                            <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y, h:i A') }}</td>
                                            <td>{{ $row->booking_id }}</td>
                                            <td>{{ $row->agent_name }}</td>
                                            <td>
                                                @if($row->service_type == 'entry_port')
                                                    <span class="badge text-white bg-primary">Arrival</span>
                                                @elseif($row->service_type == 'exit_port')
                                                    <span class="badge text-white bg-primary">Departure</span>
                                                @elseif($row->service_type == 'travel_point')
                                                    <span class="badge text-white bg-primary">Travel Point</span>
                                                @elseif($row->service_type == 'travel_hourly')
                                                    <span class="badge text-white bg-primary">Travel Hourly</span>
                                                @elseif($row->service_type == 'guide')
                                                    <span class="badge text-white bg-primary">Guide</span>
                                                @elseif($row->service_type == 'driver')
                                                    <span class="badge text-white bg-primary">Driver</span>
                                                @elseif($row->service_type == 'attraction')
                                                    <span class="badge text-white bg-primary">Attraction</span>
                                                @elseif($row->service_type == 'hotel')
                                                    <span class="badge text-white bg-primary">Hotel</span>
                                                @endif
                                            </td>
                                            <td>{{ $row->customer_name ?? 'N/A' }}</td>
                                            <td>{{ $row->customer_email ?? 'N/A' }}</td>
                                            <td style="text-align: right;">
                                                <strong>₹{{ number_format($row->amount, 2) }}</strong>
                                            </td>
                                            <td>
                                                @if($row->status == 1)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                    {{-- @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="ri-file-list-3-line text-muted" style="font-size: 48px;"></i>
                                                    <span class="fw-semibold text-muted mt-2">No ledger entries found for the selected period.</span>
                                                </div>
                                            </td>
                                        </tr> --}}
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<!-- DataTable JS -->
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<!-- DataTables Initialization Script -->
<script>
    $(document).ready(function() {
        // Initialize DataTable with export buttons
        var table = $('.datatables-basic').DataTable({
            responsive: true,
            buttons: [
                'copy',
                'csv',
                'excel',
                'pdf',
                'print'
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
            },
            lengthMenu: [10, 25, 50, 100],
            stripeClasses: ['table-light', 'table-white'],
            order: [[1, 'desc']], // Sort by date descending
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
                // Set min for end date to start date
                endInput.min = startInput.value;
                // Set max for end date to 3 months after start date
                const start = new Date(startInput.value);
                const maxEnd = new Date(start);
                maxEnd.setMonth(maxEnd.getMonth() + 3);
                // If the day overflows (e.g., Feb 30), JS auto-corrects to next month, so fix:
                if (maxEnd.getDate() !== start.getDate()) {
                    maxEnd.setDate(0); // last day of previous month
                }
                endInput.max = maxEnd.toISOString().split('T')[0];
                // If end date is before start date, reset it
                if (endInput.value < startInput.value) {
                    endInput.value = startInput.value;
                }
                // If end date is after max, reset it
                if (endInput.value > endInput.max) {
                    endInput.value = endInput.max;
                }
            }
        }

        startInput.addEventListener('change', setEndDateLimits);
        // On page load
        setEndDateLimits();
    });
</script>
@endsection

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
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
    .badge {
        font-size: 0.75em;
    }
</style>
@endsection 