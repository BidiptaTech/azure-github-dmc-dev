@extends('layouts.layout')
@section('title', 'Refunds')
@extends('layouts.datatablecss')

<!-- Date Range Picker CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-3 mb-2">
                <i class="ri-money-dollar-circle-line me-2 text-success"></i>
                <span class="text-muted fw-light">Bookings /</span> Refunds
            </h4>
            <p class="text-muted">Manage refunds for cancelled definite bookings</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-success fs-6">
                <i class="ri-money-dollar-circle-line me-1"></i>
                <span id="rangeCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }}</span>
                <span id="rangeLabel">{{ date('F') }}</span> Refunds
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
                            <h5 class="card-title mb-1" id="statRefundsCount">{{ $tours->where('updated_at', '>=', now()->startOfMonth())->where('updated_at', '<=', now()->endOfMonth())->count() }}</h5>
                            <p class="text-muted mb-0" id="statRefundsLabel">{{ date('F') }} Refunds</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-success rounded">
                                <i class="ri-money-dollar-circle-line ri-24px"></i>
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
                            <h5 class="card-title mb-1" id="statTotalCount">{{ $tours->count() }}</h5>
                            <p class="text-muted mb-0" id="statTotalLabel">Total Refunds</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-info rounded">
                                <i class="ri-funds-line ri-24px"></i>
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
                            <h5 class="card-title mb-1" id="statPendingCount">{{ $tours->where('tour_status', 'Refund - Pending')->count() }}</h5>
                            <p class="text-muted mb-0" id="statPendingLabel">Pending Refunds</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-danger rounded">
                                <i class="ri-time-line ri-24px"></i>
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
                            <h5 class="card-title mb-1" id="statCompletedCount">{{ $tours->where('tour_status', 'Refunded')->count() }}</h5>
                            <p class="text-muted mb-0" id="statCompletedLabel">Completed Refunds</p>
                        </div>
                        <div class="avatar">
                            <div class="avatar-initial bg-success rounded">
                                <i class="ri-check-circle-line ri-24px"></i>
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
                <div class="col-md-2">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Tour ID, Display ID...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Destination</label>
                    <select class="form-select" id="destinationFilter">
                        <option value="">All Destinations</option>
                        @foreach($tours->pluck('destination')->unique()->filter() as $destination)
                            <option value="{{ $destination }}">{{ $destination }}</option>
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
                <div class="col-md-2">
                    <label class="form-label">Refund Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="Refund - Pending">Pending</option>
                        <option value="Refunded">Refunded</option>
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
            <h5 class="mb-0">Refunds List</h5>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-success btn-sm dropdown-toggle" type="button" id="exportDropdown"
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
            </div>
        </div>
        <div class="card-body">
            @if($tours->count() > 0)
            <div class="table-responsive">
                <table class="datatables-basic table table-bordered" id="toursTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Tour Details</th>
                            <th>Destination</th>
                            <th>Guests</th>
                            <th>Agent</th>
                            <th>Travel Dates</th>
                            <th>Refund Status</th>
                            <th>Cancelled Date</th>
                            <th>Actions</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tours as $key => $tour)
                        <tr 
                            class="{{ $tour->tour_status === 'Refund - Pending' ? 'table-danger' : 'table-success' }}"
                            data-created-at="{{ optional($tour->created_at)->toDateString() }}"
                            data-updated-at="{{ optional($tour->updated_at)->toDateString() }}"
                            data-adult="{{ $tour->adult ?? 0 }}"
                            data-child="{{ $tour->child ?? 0 }}"
                            data-tour-status="{{ $tour->tour_status }}"
                            data-agent="{{ $tour->agent_name }}"
                            data-destination="{{ $tour->destination }}"
                        >
                            <td>{{ $key + 1 }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <strong class="text-success">{{ $tour->display_id }}</strong>
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
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ $tour->agent_name ?? 'N/A' }}</span>
                                    <small class="text-muted">ID: {{ $tour->agent_id ?? 'N/A' }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    @if($tour->check_in_time && $tour->check_out_time)
                                        <span class="fw-medium">{{ \Carbon\Carbon::parse($tour->check_in_time)->format('M d, Y') }}</span>
                                        <small class="text-muted">to {{ \Carbon\Carbon::parse($tour->check_out_time)->format('M d, Y') }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($tour->tour_status === 'Refund - Pending')
                                    <span class="badge bg-danger">
                                        <i class="ri-time-line me-1"></i>
                                        Pending
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="ri-check-circle-line me-1"></i>
                                        Refunded
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ optional($tour->updated_at)->format('M d, Y') }}</span>
                                    <small class="text-muted">{{ optional($tour->updated_at)->format('h:i A') }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('bookings.view-tour', ['tourId' => \Crypt::encrypt($tour->tour_id)]) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="View Details">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    @if($tour->tour_status === 'Refund - Pending')
                                        <button type="button" 
                                                class="btn btn-sm btn-success" 
                                                onclick="processRefund({{ $tour->tour_id }})"
                                                title="Process Refund">
                                            <i class="ri-money-dollar-circle-line"></i>
                                        </button>
                                    @else
                                        <span class="badge bg-success" title="Already Refunded">
                                            ✓ Refunded
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-medium">{{ optional($tour->created_at)->format('D, M d, Y') }}</span>
                                    <small class="text-muted">{{ optional($tour->created_at)->format('h:i A') }}</small>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="ri-money-dollar-circle-line ri-48px text-muted mb-3"></i>
                                    <h6 class="text-muted">No refunds found</h6>
                                    <p class="text-muted small mb-0">No tours with 'Refund - Pending' status found for refund processing.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            {{-- @if($tours->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $tours->links() }}
            </div>
            @endif --}} 
            @else
            <!-- Empty State -->
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="ri-money-dollar-circle-line ri-64px text-muted"></i>
                </div>
                <h4 class="text-muted mb-3 text-center fw-bold">No Refunds Available</h4>
                <p class="text-muted mb-4 small text-center">Currently, there are no tours with 'Refund - Pending' status that require refund processing.</p>
                <p class="text-muted small text-center">Refunds will appear here when tours are cancelled and marked as definite.</p>
            </div>
            @endif
        </div>
    </div>
</div>
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
        // Initialize filter event handlers
        $('#searchInput').on('keyup', function() {
            filterTable();
        });

        $('#destinationFilter').on('change', function() {
            filterTable();
        });

        $('#agentFilter').on('change', function() {
            filterTable();
        });

        $('#statusFilter').on('change', function() {
            filterTable();
        });

        // Export functionality
        $('#exportCopy').on('click', function() {
            alert('Copy functionality would be implemented here');
        });

        $('#exportCSV').on('click', function() {
            alert('CSV export functionality would be implemented here');
        });

        $('#exportExcel').on('click', function() {
            alert('Excel export functionality would be implemented here');
        });

        $('#exportPDF').on('click', function() {
            alert('PDF export functionality would be implemented here');
        });

        $('#exportPrint').on('click', function() {
            window.print();
        });

        // Add data table initialization if needed
        console.log('Data table and filters initialized');
    }

    // Reset filters function (global)
    function resetFilters() {
        $('#searchInput').val('');
        $('#destinationFilter').val('');
        $('#agentFilter').val('');
        $('#statusFilter').val('');
        $('#dateRange').val('');
        $('#dateRangeStart').val('');
        $('#dateRangeEnd').val('');
        filterTable();
        updateStats();
    }

    // Filter table function
    function filterTable() {
        var searchTerm = $('#searchInput').val().toLowerCase();
        var destinationFilter = $('#destinationFilter').val();
        var agentFilter = $('#agentFilter').val();
        var statusFilter = $('#statusFilter').val();
        var startDate = $('#dateRangeStart').val();
        var endDate = $('#dateRangeEnd').val();

        var visibleRows = 0;

        $('#toursTable tbody tr').each(function() {
            var row = $(this);
            var show = true;

            // Search filter
            if (searchTerm) {
                var rowText = row.text().toLowerCase();
                if (rowText.indexOf(searchTerm) === -1) {
                    show = false;
                }
            }

            // Destination filter
            if (destinationFilter && show) {
                var destination = row.data('destination');
                if (destination !== destinationFilter) {
                    show = false;
                }
            }

            // Agent filter
            if (agentFilter && show) {
                var agent = row.data('agent');
                if (agent !== agentFilter) {
                    show = false;
                }
            }

            // Status filter
            if (statusFilter && show) {
                var status = row.data('tour-status');
                if (status !== statusFilter) {
                    show = false;
                }
            }

            // Date filter
            if (startDate && endDate && show) {
                var rowDate = row.data('updated-at');
                if (rowDate) {
                    show = rowDate >= startDate && rowDate <= endDate;
                }
            }

            if (show) {
                row.show();
                visibleRows++;
            } else {
                row.hide();
            }
        });

        // Update stats based on filtered results
        updateFilteredStats(visibleRows);
    }

    // Update filtered stats
    function updateFilteredStats(visibleRows) {
        // You can implement dynamic stats update based on filtered results here
        console.log('Visible rows:', visibleRows);
    }

    // Update stats
    function updateStats() {
        // Implement stats update logic if needed
    }

// Process refund function
function processRefund(tourId) {
    // Create advanced confirmation modal
    showConfirmationModal(
        'Process Refund Confirmation',
        'Are you sure you want to process the refund for this tour?<br><small class="text-muted">This action cannot be undone.</small>',
        'warning',
        function() {
            // Show loading modal
            showLoadingModal('Processing Refund', 'Please wait while we process the refund...');
            
            const button = event.target.closest('button');
            const originalContent = button.innerHTML;
            button.innerHTML = '<i class="ri-loader-line spinner-border spinner-border-sm me-1"></i>';
            button.disabled = true;

            $.ajax({
                url: '{{ route("bookings.process-refund") }}',
                method: 'POST',
                data: {
                    tour_id: tourId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    hideModal();
                    if (response.success) {
                        showSuccessModal(
                            'Refund Processed Successfully!',
                            'The refund has been processed and the tour status has been updated.',
                            function() {
                                location.reload();
                            }
                        );
                    } else {
                        showErrorModal('Error Processing Refund', response.message || 'An error occurred while processing the refund.');
                        // Restore button state
                        button.innerHTML = originalContent;
                        button.disabled = false;
                    }
                },
                error: function(xhr) {
                    hideModal();
                    let errorMessage = 'Error processing refund. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showErrorModal('Error Processing Refund', errorMessage);
                    // Restore button state
                    button.innerHTML = originalContent;
                    button.disabled = false;
                }
            });
        }
    );
}

// Advanced Modal Functions
function showConfirmationModal(title, message, type, confirmCallback) {
    const modalHtml = `
        <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                    <div class="modal-body text-center py-5 px-4">
                        <!-- Refund Icon with Animation -->
                        <div class="refund-icon-wrapper mb-4">
                            <div class="refund-circle">
                                <i class="ri-money-dollar-circle-line ri-32px text-white"></i>
                            </div>
                        </div>
                        
                        <!-- Refund Title -->
                        <h4 class="fw-bold text-primary mb-3" id="confirmationModalLabel">Process Refund</h4>
                        
                        <!-- Refund Process Message -->
                        <div class="refund-message mb-4">
                            <p class="text-dark mb-3 fs-6">
                                <strong>Tour ID:</strong> <span class="text-primary">#${getTourIdFromButton()}</span>
                            </p>
                            <p class="text-muted mb-3">
                                You are about to process a refund for this tour booking. 
                                This will update the tour status and initiate the refund process.
                            </p>
                            <div class="alert alert-info border-0" style="background-color: #e3f2fd;">
                                <i class="ri-information-line me-2 text-info"></i>
                                <strong>Note:</strong> This action will mark the tour as "Refunded" and cannot be undone.
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="d-flex gap-3 justify-content-center">
                            <button type="button" class="btn btn-outline-secondary px-4 py-2" data-bs-dismiss="modal">
                                <i class="ri-close-line me-2"></i>Cancel
                            </button>
                            <button type="button" class="btn btn-primary px-4 py-2" id="confirmButton">
                                <i class="ri-check-line me-2"></i>Process Refund
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#confirmationModal').remove();
    
    // Add modal to body
    $('body').append(modalHtml);
    
    // Add custom CSS for enhanced styling
    if (!$('#confirmationModalStyles').length) {
        $('head').append(`
            <style id="confirmationModalStyles">
                .refund-icon-wrapper {
                    position: relative;
                    display: inline-block;
                }
                
                .refund-circle {
                    width: 80px;
                    height: 80px;
                    background: linear-gradient(135deg, #007bff, #0056b3);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    box-shadow: 0 8px 25px rgba(0, 123, 255, 0.3);
                    animation: refundPulse 0.6s ease-out;
                }
                
                @keyframes refundPulse {
                    0% { transform: scale(0.8); opacity: 0; }
                    50% { transform: scale(1.1); }
                    100% { transform: scale(1); opacity: 1; }
                }
                
                .refund-circle i {
                    font-size: 2.5rem;
                    animation: iconAppear 0.8s ease-out 0.3s both;
                }
                
                @keyframes iconAppear {
                    0% { transform: scale(0) rotate(-45deg); opacity: 0; }
                    100% { transform: scale(1) rotate(0deg); opacity: 1; }
                }
                
                .refund-message {
                    max-width: 400px;
                    margin: 0 auto;
                }
                
                #confirmationModal .modal-content {
                    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                }
                
                #confirmationModal .modal-dialog {
                    max-width: 500px;
                }
                
                #confirmationModal .btn {
                    border-radius: 8px;
                    font-weight: 500;
                    transition: all 0.3s ease;
                }
                
                #confirmationModal .btn:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                }
                
                #confirmationModal .alert {
                    border-radius: 8px;
                    font-size: 0.9rem;
                }
            </style>
        `);
    }
    
    // Show modal
    $('#confirmationModal').modal('show');
    
    // Handle confirm button click
    $('#confirmButton').off('click').on('click', function() {
        $('#confirmationModal').modal('hide');
        if (confirmCallback) {
            confirmCallback();
        }
    });
    
    // Clean up when modal is hidden
    $('#confirmationModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

// Helper function to get tour ID from the button
function getTourIdFromButton() {
    const button = event.target.closest('button');
    if (button && button.onclick) {
        const onclickStr = button.onclick.toString();
        const match = onclickStr.match(/processRefund\((\d+)\)/);
        return match ? match[1] : 'N/A';
    }
    return 'N/A';
}

function showLoadingModal(title, message) {
    const modalHtml = `
        <div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-body text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5 class="fw-bold mb-2">${title}</h5>
                        <p class="text-muted mb-0">${message}</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#loadingModal').remove();
    
    // Add modal to body
    $('body').append(modalHtml);
    
    // Show modal
    $('#loadingModal').modal('show');
}

function showSuccessModal(title, message, callback) {
    const modalHtml = `
        <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                    <div class="modal-body text-center py-5 px-4">
                        <!-- Success Icon with Animation -->
                        <div class="success-icon-wrapper mb-4">
                            <div class="success-circle">
                                <i class="ri-check-line ri-32px text-white"></i>
                            </div>
                        </div>
                        
                        <!-- Success Title -->
                        <h4 class="fw-bold text-success mb-3" id="successModalLabel">${title}</h4>
                        
                        <!-- Success Message -->
                        <p class="text-muted mb-4 fs-6">${message}</p>
                        
                        <!-- Progress Bar -->
                        <div class="progress-wrapper mb-3">
                            <div class="progress" style="height: 6px; border-radius: 3px;">
                                <div class="progress-bar bg-success" id="successProgressBar" role="progressbar" style="width: 0%; transition: width 0.1s ease;"></div>
                            </div>
                        </div>
                        
                        <!-- Auto-close Timer -->
                        <p class="text-muted small mb-0">
                            <i class="ri-time-line me-1"></i>
                            Auto-closing in <span id="countdownTimer" class="fw-bold text-success">3</span> seconds
                        </p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#successModal').remove();
    
    // Add modal to body
    $('body').append(modalHtml);
    
    // Show modal
    $('#successModal').modal('show');
    
    // Add custom CSS for enhanced styling
    if (!$('#successModalStyles').length) {
        $('head').append(`
            <style id="successModalStyles">
                .success-icon-wrapper {
                    position: relative;
                    display: inline-block;
                }
                
                .success-circle {
                    width: 80px;
                    height: 80px;
                    background: linear-gradient(135deg, #28a745, #20c997);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
                    animation: successPulse 0.6s ease-out;
                }
                
                @keyframes successPulse {
                    0% { transform: scale(0.8); opacity: 0; }
                    50% { transform: scale(1.1); }
                    100% { transform: scale(1); opacity: 1; }
                }
                
                .success-circle i {
                    font-size: 2.5rem;
                    animation: checkmarkAppear 0.8s ease-out 0.3s both;
                }
                
                @keyframes checkmarkAppear {
                    0% { transform: scale(0) rotate(-45deg); opacity: 0; }
                    100% { transform: scale(1) rotate(0deg); opacity: 1; }
                }
                
                .progress-wrapper {
                    max-width: 300px;
                    margin: 0 auto;
                }
                
                .progress {
                    background-color: #e9ecef;
                    overflow: visible;
                }
                
                .progress-bar {
                    background: linear-gradient(90deg, #28a745, #20c997);
                    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
                }
                
                #successModal .modal-content {
                    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
                }
                
                #successModal .modal-dialog {
                    max-width: 450px;
                }
            </style>
        `);
    }
    
    // Start countdown timer and progress bar
    let countdown = 3;
    const countdownElement = document.getElementById('countdownTimer');
    const progressBar = document.getElementById('successProgressBar');
    
    const timer = setInterval(() => {
        countdown--;
        if (countdownElement) countdownElement.textContent = countdown;
        if (progressBar) progressBar.style.width = ((3 - countdown) / 3 * 100) + '%';
        
        if (countdown <= 0) {
            clearInterval(timer);
            // Auto-close modal
            $('#successModal').modal('hide');
            // Wait for modal to close, then refresh page
            setTimeout(() => {
                if (callback) {
                    callback();
                }
            }, 300);
        }
    }, 1000);
    
    // Clean up when modal is hidden
    $('#successModal').on('hidden.bs.modal', function() {
        clearInterval(timer);
        $(this).remove();
    });
}

function showErrorModal(title, message) {
    const modalHtml = `
        <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-0 pb-2">
                        <div class="d-flex align-items-center">
                            <div class="icon-wrapper me-3">
                                <i class="ri-error-warning-line ri-24px text-danger"></i>
                            </div>
                            <h5 class="modal-title fw-bold text-danger" id="errorModalLabel">${title}</h5>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center py-4">
                        <p class="mb-0">${message}</p>
                    </div>
                    <div class="modal-footer border-0 pt-2">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#errorModal').remove();
    
    // Add modal to body
    $('body').append(modalHtml);
    
    // Show modal
    $('#errorModal').modal('show');
    
    // Clean up when modal is hidden
    $('#errorModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

function hideModal() {
    // Hide all custom modals
    $('#loadingModal, #confirmationModal, #successModal, #errorModal').modal('hide');
}
</script>
@endsection

@extends('layouts.datatablejs')