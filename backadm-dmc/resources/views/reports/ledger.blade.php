@extends('layouts.layout')
@extends('layouts.datatablecss')

@php
use Illuminate\Support\Facades\Auth;
@endphp

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
                                {{ is_array($results) || is_countable($results) ? count($results) : 0 }}
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
                                {{ is_array($results) ? count(array_unique(array_column($results, 'agent_name'))) : 0 }}
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
                                @php
                                    $selectedCurrency = request('currency', 'SGD');
                                    $totalAmount = is_array($results) ? array_sum(array_column($results, 'amount')) : 0;
                                    
                                    if ($selectedCurrency == 'INR') {
                                        $defaultRate = request('currency', 'SGD') == 'INR' ? '67.50' : '1.00';
                                        $exchangeRate = floatval(request('custom_exchange_rate', $defaultRate));
                                        $totalAmount *= $exchangeRate;
                                        $currencySymbol = '₹';
                                    } else {
                                        $currencySymbol = '$';
                                    }
                                @endphp
                                {{ $currencySymbol }}{{ number_format($totalAmount, 2) }}
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
                <!-- Enhanced Filter Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-gradient-primary text-white">
                        <h6 class="mb-0 fw-bold">
                            <i class="ri-filter-3-line me-2"></i>Advanced Filters & Currency Settings
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('reports.ledger') }}" id="ledgerFilterForm">
                            <!-- Hidden field for custom exchange rate -->
                            @php
                                $defaultCustomRate = request('currency', 'SGD') == 'INR' ? '67.50' : '1.00';
                            @endphp
                            <input type="hidden" id="customExchangeRateField" name="custom_exchange_rate" value="{{ request('custom_exchange_rate', $defaultCustomRate) }}">
                            <!-- First Row: Date Range & Currency -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-2">
                                    <label for="start_date" class="form-label fw-semibold">Start Date</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-calendar-line"></i></span>
                                        <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" class="form-control" required aria-label="Start Date" autocomplete="off" onchange="applyFilters()">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label for="end_date" class="form-label fw-semibold">End Date</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-calendar-line"></i></span>
                                        <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" class="form-control" required aria-label="End Date" autocomplete="off" onchange="applyFilters()">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label for="currency" class="form-label fw-semibold">Currency</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-money-dollar-circle-line"></i></span>
                                        <select name="currency" id="currency" class="form-select" aria-label="Currency" onchange="applyFilters()">
                                            <option value="SGD" {{ request('currency', 'SGD') == 'SGD' ? 'selected' : '' }}>SGD</option>
                                            <option value="INR" {{ request('currency') == 'INR' ? 'selected' : '' }}>INR</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Exchange Rate</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-info text-white">
                                            <i class="ri-exchange-line"></i>
                                        </span>
                                        @php
                                            $currentCurrency = request('currency', 'SGD');
                                            if ($currentCurrency == 'INR') {
                                                $currentRate = request('custom_exchange_rate', '67.50');
                                                $displayText = "1 SGD = {$currentRate} INR";
                                            } else {
                                                $displayText = "1 SGD = 1.00 SGD";
                                            }
                                        @endphp
                                        <input type="text" class="form-control bg-light" id="exchangeRate" readonly value="{{ $displayText }}" placeholder="Loading...">
                                        <button type="button" class="btn btn-outline-info btn-sm" id="editRateBtn" onclick="toggleRateEdit()" style="display: {{ request('currency', 'SGD') == 'INR' ? 'inline-block' : 'none' }};" title="Edit Exchange Rate">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                    </div>
                                    <div id="rateEditSection" style="display: none;" class="mt-2">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">1 SGD =</span>
                                            <input type="number" class="form-control" id="customRate" step="0.01" min="0" value="{{ request('custom_exchange_rate', request('currency', 'SGD') == 'INR' ? '67.50' : '1.00') }}" placeholder="Enter rate">
                                            <span class="input-group-text">INR</span>
                                            <button type="button" class="btn btn-success btn-sm" onclick="updateCustomRate()" title="Save Rate">
                                                <i class="ri-check-line"></i>
                                            </button>
                                            <button type="button" class="btn btn-secondary btn-sm" onclick="cancelRateEdit()" title="Cancel">
                                                <i class="ri-close-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="view_type" class="form-label fw-semibold">View Type</label>
                                    <select name="view_type" id="view_type" class="form-select" aria-label="View Type" onchange="applyFilters()">
                                        <option value="summary" {{ request('view_type', 'summary') == 'summary' ? 'selected' : '' }}>Summary View</option>
                                        <option value="detailed" {{ request('view_type') == 'detailed' ? 'selected' : '' }}>Detailed View</option>
                                        <option value="balance" {{ request('view_type') == 'balance' ? 'selected' : '' }}>Balance Sheet View</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Second Row: Hierarchy Selectors -->
                            <div class="row g-3 mb-3">
                                @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 2)
                                    {{-- Show Master DMC dropdown for Admin only --}}
                                    <div class="col-md-3">
                                        <label for="master_dmc_id" class="form-label fw-semibold">Master DMC</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-building-line"></i></span>
                                            <select name="master_dmc_id" id="master_dmc_id" class="form-select" aria-label="Master DMC">
                                                <option value="">All Master DMCs</option>
                                                @if(isset($masterDmcsForDropdown) && $masterDmcsForDropdown->isNotEmpty())
                                                    @foreach($masterDmcsForDropdown as $masterDmc)
                                                        <option value="{{ $masterDmc->userId }}" {{ ($masterDmcId ?? request('master_dmc_id')) == $masterDmc->userId ? 'selected' : '' }}>
                                                            {{ $masterDmc->company_name ?? $masterDmc->name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                @endif
                                
                                @if(Auth::user()->role_id <= 10)
                                    {{-- Show DMC dropdown for Admin and Master DMC --}}
                                    <div class="col-md-3">
                                        <label for="dmc_id" class="form-label fw-semibold">DMC</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="ri-store-line"></i></span>
                                            <select name="dmc_id" id="dmc_id" class="form-select" aria-label="DMC">
                                                @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 2)
                                                    <option value="">All DMCs</option>
                                                @else
                                                    <option value="">Select DMC</option>
                                                @endif
                                                @if(isset($dmcsForDropdown) && $dmcsForDropdown->isNotEmpty())
                                                    @foreach($dmcsForDropdown as $dmc)
                                                        <option value="{{ $dmc->userId }}" data-master="{{ $dmc->master_dmc_id ?? '' }}" {{ ($dmcId ?? request('dmc_id')) == $dmc->userId ? 'selected' : '' }}>
                                                            {{ $dmc->company_name ?? $dmc->name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-3">
                                    <label for="agency_id" class="form-label fw-semibold">Agency</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-building-2-line"></i></span>
                                        <select name="agency_id" id="agency_id" class="form-select" aria-label="Agency">
                                            <option value="">All Agencies</option>
                                            @if(isset($agenciesForDropdown) && $agenciesForDropdown->count() > 0)
                                                @foreach($agenciesForDropdown as $agency)
                                                    <option value="{{ $agency->agency_id }}" data-dmc="{{ json_encode($agency->dmc_id ?? []) }}" {{ (request('agency_id') == $agency->agency_id) ? 'selected' : '' }}>
                                                        {{ $agency->agency_name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="agent_id" class="form-label fw-semibold">Agent</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-user-line"></i></span>
                                        @if(isset($agentsForDropdown) && $agentsForDropdown->count() > 0)
                                            <select name="agent_id" id="agent_id" class="form-select" aria-label="Agent" onchange="applyFilters()">
                                                <option value="">All Agents</option>
                                                @php
                                                    $selectedAgencyId = request('agency_id');
                                                @endphp
                                                @foreach($agentsForDropdown as $agent)
                                                    @if(!$selectedAgencyId || $agent->agency_id == $selectedAgencyId)
                                                        <option value="{{ $agent->agent_id }}" data-agency="{{ $agent->agency_id ?? '' }}" {{ $agentId == $agent->agent_id ? 'selected' : '' }}>
                                                            {{ $agent->name }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="text" class="form-control bg-light text-muted" value="No agents available" readonly>
                                            <input type="hidden" name="agent_id" value="">
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="service_type" class="form-label fw-semibold">Service Type</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-service-line"></i></span>
                                        <select name="service_type" id="service_type" class="form-select" aria-label="Service Type" onchange="applyFilters()">
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
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                                            <i class="ri-refresh-line me-1"></i>Reset Filters
                                        </button>
                                        <button type="button" class="btn btn-info" onclick="refreshExchangeRate()">
                                            <i class="ri-exchange-line me-1"></i>Update Rate
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <!-- Export Dropdown Button -->
                                    <div class="dropdown w-100">
                                        <button class="btn btn-warning dropdown-toggle w-100" type="button" id="exportDropdown"
                                            data-bs-toggle="dropdown" aria-expanded="false" data-bs-toggle="tooltip" data-bs-placement="top" title="Export Table Data">
                                            <i class="ri-download-line me-1"></i>Export Data
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                            <li><a class="dropdown-item" href="javascript:void(0);" id="exportCopy">
                                                <i class="ri-file-copy-line me-2"></i>Copy to Clipboard</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);" id="exportCSV">
                                                <i class="ri-file-text-line me-2"></i>Export as CSV</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);" id="exportExcel">
                                                <i class="ri-file-excel-line me-2"></i>Export as Excel</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);" id="exportPDF">
                                                <i class="ri-file-pdf-line me-2"></i>Export as PDF</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0);" id="exportPrint">
                                                <i class="ri-printer-line me-2"></i>Print Table</a></li>
                                        </ul>
                                    </div>
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
                                        <th style="width: 50px; text-align: center;">#</th>
                                        <th>Date & Time</th>
                                        <th>Booking ID</th>
                                        <th>Agency Name</th>
                                        <th>Service Type</th>
                                        <th>Customer Details</th>
                                        <th>Opening Balance</th>
                                        <th>Transaction Amount</th>
                                        <th>Closing Balance</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0">
                                    @php 
                                        $runningBalance = 0;
                                        $selectedCurrency = request('currency', 'SGD');
                                        // Use custom exchange rate, default to 67.50 for INR, 1.00 for SGD
                                        $defaultRate = $selectedCurrency == 'INR' ? '67.50' : '1.00';
                                        $exchangeRate = floatval(request('custom_exchange_rate', $defaultRate));
                                    @endphp
                                    @if(isset($results) && (is_array($results) || is_countable($results)))
                                        @foreach($results as $index => $row)
                                        @php
                                            $openingBalance = $runningBalance;
                                            $transactionAmount = $row->amount ?? 0;
                                            $runningBalance += $transactionAmount;
                                            $closingBalance = $runningBalance;
                                            
                                            // Currency conversion
                                            if ($selectedCurrency == 'INR') {
                                                $openingBalance *= $exchangeRate;
                                                $transactionAmount *= $exchangeRate;
                                                $closingBalance *= $exchangeRate;
                                                $currencySymbol = '₹';
                                            } else {
                                                $currencySymbol = 'S$';
                                            }
                                        @endphp
                                        <tr>
                                            <td style="text-align: center;">
                                                <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold">{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y') }}</span>
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($row->created_at)->format('h:i A') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info text-white">{{ $row->booking_id }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold">{{ $row->company_name ?? 'N/A' }}</span>
                                                    <small class="text-muted"> {{ $row->agent_name ?? 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @if($row->service_type == 'entry_port')
                                                    <span class="badge bg-success">Arrival</span>
                                                @elseif($row->service_type == 'exit_port')
                                                    <span class="badge bg-warning">Departure</span>
                                                @elseif($row->service_type == 'travel_point')
                                                    <span class="badge bg-info">Travel Point</span>
                                                @elseif($row->service_type == 'travel_hourly')
                                                    <span class="badge bg-secondary">Travel Hourly</span>
                                                @elseif($row->service_type == 'guide')
                                                    <span class="badge bg-primary">Guide</span>
                                                @elseif($row->service_type == 'driver')
                                                    <span class="badge bg-dark">Driver</span>
                                                @elseif($row->service_type == 'attraction')
                                                    <span class="badge bg-danger">Attraction</span>
                                                @elseif($row->service_type == 'hotel')
                                                    <span class="badge bg-primary">Hotel</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($row->customer_name != 'N/A')
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-semibold">{{ $row->customer_name ?? 'N/A' }}</span>
                                                        <small class="text-muted">{{ $row->customer_email ?? 'N/A' }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td style="text-align: right;">
                                                <div class="d-flex flex-column align-items-end">
                                                    <span class="fw-bold text-secondary">{{ $currencySymbol }}{{ number_format($openingBalance, 2) }}</span>
                                                    @if($selectedCurrency == 'INR')
                                                        <small class="text-muted">S${{ number_format($openingBalance / $exchangeRate, 2) }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td style="text-align: right;">
                                                <div class="d-flex flex-column align-items-end">
                                                    <span class="fw-bold {{ $transactionAmount >= 0 ? 'text-success' : 'text-danger' }}">
                                                        {{ $transactionAmount >= 0 ? '+' : '' }}{{ $currencySymbol }}{{ number_format($transactionAmount, 2) }}
                                                    </span>
                                                    @if($selectedCurrency == 'INR')
                                                        <small class="text-muted">S${{ number_format($transactionAmount / $exchangeRate, 2) }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td style="text-align: right;">
                                                <div class="d-flex flex-column align-items-end">
                                                    <span class="fw-bold text-primary">{{ $currencySymbol }}{{ number_format($closingBalance, 2) }}</span>
                                                    @if($selectedCurrency == 'INR')
                                                        <small class="text-muted">S${{ number_format($closingBalance / $exchangeRate, 2) }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="ri-more-2-line"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="viewTransactionDetails('{{ $row->id }}')">
                                                            <i class="ri-eye-line me-2"></i>View Details
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="viewBalanceHistory('{{ $row->agent_id }}')">
                                                            <i class="ri-history-line me-2"></i>Balance History
                                                        </a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item" href="#" onclick="exportTransaction('{{ $row->id }}')">
                                                            <i class="ri-download-line me-2"></i>Export
                                                        </a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @endif 
                                    
                                    {{-- @if(!isset($results) || empty($results) || count($results) == 0)
                                        <tr>
                                            <td colspan="10" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="ri-file-list-3-line text-muted" style="font-size: 48px;"></i>
                                                    <span class="fw-semibold text-muted mt-2">No ledger entries found for the selected period.</span>
                                                    <small class="text-muted">Try adjusting your filters or date range.</small>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif --}}
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="6" class="text-end fw-bold">Total:</th>
                                        <th></th> <!-- Opening Balance column - empty -->
                                        <th class="text-end fw-bold">
                                            @php
                                                $totalAmount = isset($results) ? collect($results)->sum('amount') : 0;
                                                if ($selectedCurrency == 'INR') {
                                                    $totalAmount *= $exchangeRate;
                                                    $currencySymbol = '₹';
                                                } else {
                                                    $currencySymbol = 'S$';
                                                }
                                            @endphp
                                            {{ $currencySymbol }}{{ number_format($totalAmount, 2) }}
                                        </th>
                                        <th class="text-end fw-bold">{{ $currencySymbol }}{{ number_format((isset($runningBalance) ? $runningBalance : 0) * ($selectedCurrency == 'INR' ? $exchangeRate : 1), 2) }}</th>
                                        <th></th> <!-- Actions column - empty -->
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Details Modal -->
<div class="modal fade" id="transactionDetailsModal" tabindex="-1" aria-labelledby="transactionDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                <h5 class="modal-title fw-bold" id="transactionDetailsModalLabel">
                    <i class="ri-file-list-3-line me-2"></i>Transaction Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="transactionDetailsContent" style="background: #f8f9fa;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted fw-semibold">Loading transaction details...</p>
                    <small class="text-muted">Please wait while we fetch the information</small>
                </div>
            </div>
            <div class="modal-footer bg-white border-top">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Close
                </button>
                <button type="button" class="btn btn-primary" onclick="printTransactionDetails()">
                    <i class="ri-printer-line me-1"></i>Print Details
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Balance History Modal -->
<div class="modal fade" id="balanceHistoryModal" tabindex="-1" aria-labelledby="balanceHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; border: none;">
                <h5 class="modal-title fw-bold" id="balanceHistoryModalLabel">
                    <i class="ri-line-chart-line me-2"></i>Balance History & Transactions
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="balanceHistoryContent" style="background: #f8f9fa;">
                <div class="text-center py-5">
                    <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted fw-semibold">Loading balance history...</p>
                    <small class="text-muted">Fetching transaction records for this agent</small>
                </div>
            </div>
            <div class="modal-footer bg-white border-top">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Close
                </button>
                <button type="button" class="btn btn-success" onclick="exportBalanceHistory()">
                    <i class="ri-file-excel-line me-1"></i>Export to Excel
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<!-- DataTables Buttons for Export -->
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.colVis.min.js"></script>
<!-- DataTable JS -->
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<!-- DataTables Initialization Script -->
<script>
    $(document).ready(function() {
        // Add small delay to ensure DOM is fully rendered
        setTimeout(function() {
            // Debug: Check table structure before initialization
            console.log('Table rows:', $('#ledgerTable tbody tr').length);
            console.log('Header columns:', $('#ledgerTable thead th').length);
            console.log('Footer columns:', $('#ledgerTable tfoot th').length);
            
            // Debug: Check each row's column count
            $('#ledgerTable tbody tr').each(function(index, row) {
                console.log('Row ' + index + ' columns:', $(row).find('td').length);
            });
            
            // Debug: Check if any rows have different column counts
            var headerCols = $('#ledgerTable thead th').length;
            var mismatchFound = false;
            $('#ledgerTable tbody tr').each(function(index, row) {
                var rowCols = $(row).find('td').length;
                if (rowCols !== headerCols && rowCols > 0) {
                    console.error('Column mismatch in row ' + index + ': expected ' + headerCols + ', found ' + rowCols);
                    mismatchFound = true;
                }
            });
            
            if (!mismatchFound) {
                console.log('No column count mismatches found in table structure');
            }
            
            try {
                // Initialize DataTable without visible buttons (we'll use custom dropdown)
                var table = $('.datatables-basic').DataTable({
                    responsive: false, // Disable responsive to avoid column issues
                    autoWidth: false,
                    scrollX: true, // Add horizontal scroll instead of responsive
                    dom: 'frtip', // Remove 'B' to hide default buttons
                    buttons: [
                        'copy', 'csv', 'excel', 'pdf', 'print'
                    ],
                    columnDefs: [
                        { width: "50px", targets: 0, className: "text-center" }, // # column
                        { width: "120px", targets: 1 }, // Date & Time
                        { width: "100px", targets: 2 }, // Booking ID
                        { width: "150px", targets: 3 }, // Agent Name
                        { width: "120px", targets: 4 }, // Service Type
                        { width: "180px", targets: 5 }, // Customer Details
                        { width: "120px", targets: 6, className: "text-end" }, // Opening Balance
                        { width: "120px", targets: 7, className: "text-end" }, // Transaction Amount
                        { width: "120px", targets: 8, className: "text-end" }, // Closing Balance
                        { width: "100px", targets: 9, orderable: false, className: "text-center" } // Actions
                    ],
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search...",
                        emptyTable: "No ledger entries found for the selected period.",
                        zeroRecords: "No matching records found."
                    },
                    lengthMenu: [10, 25, 50, 100],
                    pageLength: 10,
                    order: [[1, 'desc']], // Sort by date descending
                    processing: true,
                    stateSave: false
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

                // Fallback export functionality if DataTables buttons don't work
                function exportTableData(format) {
                    try {
                        switch(format) {
                            case 'copy':
                                table.button('.buttons-copy').trigger();
                                break;
                            case 'csv':
                                table.button('.buttons-csv').trigger();
                                break;
                            case 'excel':
                                table.button('.buttons-excel').trigger();
                                break;
                            case 'pdf':
                                table.button('.buttons-pdf').trigger();
                                break;
                            case 'print':
                                table.button('.buttons-print').trigger();
                                break;
                            default:
                                console.error('Unknown export format:', format);
                        }
                    } catch (error) {
                        console.error('Export failed:', error);
                        // Show user-friendly error message
                        alert('Export functionality is not available at the moment. Please try again later.');
                    }
                }

                // Alternative export handlers with error handling
                $('#exportCopy').on('click', function(e) {
                    e.preventDefault();
                    exportTableData('copy');
                });

                $('#exportCSV').on('click', function(e) {
                    e.preventDefault();
                    exportTableData('csv');
                });

                $('#exportExcel').on('click', function(e) {
                    e.preventDefault();
                    exportTableData('excel');
                });

                $('#exportPDF').on('click', function(e) {
                    e.preventDefault();
                    exportTableData('pdf');
                });

                $('#exportPrint').on('click', function(e) {
                    e.preventDefault();
                    exportTableData('print');
                });

                // Enable Bootstrap tooltips
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });

                // Simple fallback export functionality (doesn't rely on DataTables buttons)
                function simpleExportTable(format) {
                    const table = document.getElementById('ledgerTable');
                    if (!table) {
                        alert('Table not found');
                        return;
                    }

                    let csvContent = '';
                    const rows = table.querySelectorAll('tr');
                    
                    // Get headers
                    const headers = [];
                    const headerRow = rows[0];
                    headerRow.querySelectorAll('th').forEach(th => {
                        headers.push(th.textContent.trim());
                    });
                    csvContent += headers.join(',') + '\n';

                    // Get data rows
                    for (let i = 1; i < rows.length; i++) {
                        const row = rows[i];
                        const cells = row.querySelectorAll('td');
                        const rowData = [];
                        cells.forEach(cell => {
                            // Clean up the cell content (remove HTML tags and extra spaces)
                            let cellText = cell.textContent.trim();
                            // Escape commas and quotes
                            if (cellText.includes(',') || cellText.includes('"')) {
                                cellText = '"' + cellText.replace(/"/g, '""') + '"';
                            }
                            rowData.push(cellText);
                        });
                        csvContent += rowData.join(',') + '\n';
                    }

                    if (format === 'csv') {
                        // Download CSV
                        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                        const link = document.createElement('a');
                        const url = URL.createObjectURL(blob);
                        link.setAttribute('href', url);
                        link.setAttribute('download', 'ledger_data.csv');
                        link.style.visibility = 'hidden';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } else if (format === 'copy') {
                        // Copy to clipboard
                        navigator.clipboard.writeText(csvContent).then(() => {
                            alert('Data copied to clipboard!');
                        }).catch(() => {
                            alert('Failed to copy to clipboard. Please try selecting and copying manually.');
                        });
                    } else if (format === 'print') {
                        // Print
                        const printWindow = window.open('', '_blank');
                        printWindow.document.write(`
                            <html>
                                <head>
                                    <title>Ledger Report</title>
                                    <style>
                                        table { border-collapse: collapse; width: 100%; }
                                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                                        th { background-color: #f2f2f2; }
                                    </style>
                                </head>
                                <body>
                                    <h2>Ledger Report</h2>
                                    ${table.outerHTML}
                                </body>
                            </html>
                        `);
                        printWindow.document.close();
                        printWindow.print();
                    }
                }

                // Add fallback export handlers
                $('#exportCopy').off('click').on('click', function(e) {
                    e.preventDefault();
                    try {
                        exportTableData('copy');
                    } catch (error) {
                        simpleExportTable('copy');
                    }
                });

                $('#exportCSV').off('click').on('click', function(e) {
                    e.preventDefault();
                    try {
                        exportTableData('csv');
                    } catch (error) {
                        simpleExportTable('csv');
                    }
                });

                $('#exportPrint').off('click').on('click', function(e) {
                    e.preventDefault();
                    try {
                        exportTableData('print');
                    } catch (error) {
                        simpleExportTable('print');
                    }
                });
                
            } catch (error) {
                console.error('DataTables initialization error:', error);
                console.log('Please check the table structure for column count mismatch');
                
                // Fallback: Initialize without complex features
                try {
                    var simpleTable = $('.datatables-basic').DataTable({
                        responsive: false,
                        paging: true,
                        searching: true,
                        ordering: true,
                        info: true,
                        autoWidth: false,
                        scrollX: true,
                        lengthMenu: [10, 25, 50, 100],
                        pageLength: 25,
                        order: [[1, 'desc']],
                        language: {
                            emptyTable: "No ledger entries found for the selected period.",
                            zeroRecords: "No matching records found.",
                            search: "_INPUT_",
                            searchPlaceholder: "Search..."
                        }
                    });
                    console.log('Simple DataTables initialization successful');
                } catch (fallbackError) {
                    console.error('Even simple DataTables initialization failed:', fallbackError);
                    
                    // Last resort: try without any options
                    try {
                        var basicTable = $('.datatables-basic').DataTable();
                        console.log('Basic DataTables initialization successful');
                    } catch (basicError) {
                        console.error('All DataTables initialization attempts failed:', basicError);
                    }
                }
            }
        }, 100); // 100ms delay
    });
</script>
<!-- End DataTable JS -->
<script>
    // Global variable to store custom exchange rate (must be outside DOMContentLoaded)
    // This always stores the INR rate (67.50 default), regardless of current currency
    @php
        $jsCustomRate = request('custom_exchange_rate', '67.50');
        // If current currency is SGD, still keep the INR rate for when user switches back
        if (request('currency', 'SGD') == 'SGD' && !request('custom_exchange_rate')) {
            $jsCustomRate = '67.50'; // Default INR rate
        }
    @endphp
    let customExchangeRate = {{ $jsCustomRate }};
    
    // Debug: Log the initial values for troubleshooting
    console.log('Initial customExchangeRate:', customExchangeRate);
    console.log('Current currency from URL:', '{{ request("currency", "SGD") }}');
    console.log('User role:', '{{ Auth::user()->role_id ?? "unknown" }}');
    
    // Immediate fix for exchange rate display based on URL parameters
    @if(request('currency') == 'INR')
        // Force set INR display immediately if INR is selected in URL
        setTimeout(function() {
            const exchangeRateInput = document.getElementById('exchangeRate');
            const editRateBtn = document.getElementById('editRateBtn');
            const customExchangeRateField = document.getElementById('customExchangeRateField');
            
            if (exchangeRateInput) {
                const currentRate = {{ request('custom_exchange_rate', '67.50') }};
                exchangeRateInput.value = `1 SGD = ${currentRate} INR`;
                console.log('Immediate INR fix applied:', exchangeRateInput.value);
            }
            
            if (editRateBtn) {
                editRateBtn.style.display = 'inline-block';
            }
            
            if (customExchangeRateField) {
                customExchangeRateField.value = '{{ request('custom_exchange_rate', '67.50') }}';
            }
        }, 100);
    @endif
    
    // Global function to update exchange rate (can be called from anywhere)
    function forceUpdateExchangeRate() {
        const currencyElement = document.getElementById('currency');
        const exchangeRateInput = document.getElementById('exchangeRate');
        const editRateBtn = document.getElementById('editRateBtn');
        
        if (!currencyElement || !exchangeRateInput) {
            console.error('Required elements not found for exchange rate update');
            return;
        }
        
        const currency = currencyElement.value;
        console.log('forceUpdateExchangeRate called with currency:', currency);
        
        if (currency === 'INR') {
            if (!customExchangeRate || customExchangeRate <= 0) {
                customExchangeRate = 67.50;
            }
            const displayValue = `1 SGD = ${customExchangeRate.toFixed(2)} INR`;
            exchangeRateInput.value = displayValue;
            
            if (editRateBtn) {
                editRateBtn.style.display = 'inline-block';
            }
            
            const customExchangeRateField = document.getElementById('customExchangeRateField');
            if (customExchangeRateField) {
                customExchangeRateField.value = customExchangeRate.toFixed(2);
            }
            
            console.log('Forced INR display update:', displayValue);
        } else {
            exchangeRateInput.value = '1 SGD = 1.00 SGD';
            
            if (editRateBtn) {
                editRateBtn.style.display = 'none';
            }
            
            const customExchangeRateField = document.getElementById('customExchangeRateField');
            if (customExchangeRateField) {
                customExchangeRateField.value = '1.00';
            }
            
            console.log('Forced SGD display update');
        }
    }

    // Enhanced functionality for the ledger
    document.addEventListener('DOMContentLoaded', function() {
        const startInput = document.getElementById('start_date');
        const endInput = document.getElementById('end_date');
        const masterDmcSelect = document.getElementById('master_dmc_id');
        const dmcSelect = document.getElementById('dmc_id');
        const agencySelect = document.getElementById('agency_id');
        const agentSelect = document.getElementById('agent_id');
        const currencySelect = document.getElementById('currency');

        // Date range logic
        function setEndDateLimits() {
            if (startInput.value) {
                endInput.min = startInput.value;
                const start = new Date(startInput.value);
                const maxEnd = new Date(start);
                maxEnd.setMonth(maxEnd.getMonth() + 3);
                if (maxEnd.getDate() !== start.getDate()) {
                    maxEnd.setDate(0);
                }
                endInput.max = maxEnd.toISOString().split('T')[0];
                if (endInput.value < startInput.value) {
                    endInput.value = startInput.value;
                }
                if (endInput.value > endInput.max) {
                    endInput.value = endInput.max;
                }
            }
        }

        // Cascade filtering for Master DMC -> DMC -> Agent
        function filterDmcsByMaster() {
            const selectedMaster = masterDmcSelect.value;
            const dmcOptions = dmcSelect.querySelectorAll('option');
            
            dmcOptions.forEach(option => {
                if (option.value === '') {
                    option.style.display = 'block';
                } else {
                    const masterDmcId = option.getAttribute('data-master');
                    option.style.display = (!selectedMaster || masterDmcId === selectedMaster) ? 'block' : 'none';
                }
            });
            
            // Reset DMC selection if current selection is not valid
            if (selectedMaster && dmcSelect.value) {
                const currentDmcOption = dmcSelect.querySelector(`option[value="${dmcSelect.value}"]`);
                if (currentDmcOption && currentDmcOption.getAttribute('data-master') !== selectedMaster) {
                    dmcSelect.value = '';
                    filterAgentsByDmc();
                }
            }
        }

        // Function to load agencies by DMC
        function loadAgenciesByDmc(dmcId, preSelectedAgencyId = null, preSelectedAgentId = null) {
            if (!agencySelect) return;
            
            if (!dmcId) {
                // Clear agencies if no DMC selected
                const currentValue = agencySelect.value;
                agencySelect.innerHTML = '<option value="">All Agencies</option>';
                agencySelect.value = '';
                
                // Clear agents if agency was cleared
                if (currentValue) {
                    clearAgents();
                }
                return;
            }

            // Store previous values or use pre-selected values
            const previousAgencyValue = preSelectedAgencyId || agencySelect.value;
            const previousAgentValue = preSelectedAgentId;
            console.log('Loading agencies for DMC, preserving agency:', previousAgencyValue, 'and agent:', previousAgentValue);
            
            // Show loading state
            agencySelect.innerHTML = '<option value="">Loading agencies...</option>';
            agencySelect.disabled = true;

            // Fetch agencies for the selected DMC
            fetch(`{{ url('/reports/fetch-agencies-by-dmc') }}?dmc_id=${dmcId}`)
                .then(response => response.json())
                .then(data => {
                    agencySelect.innerHTML = '<option value="">All Agencies</option>';
                    
                    if (data.success && data.agencies && data.agencies.length > 0) {
                        data.agencies.forEach(agency => {
                            const option = document.createElement('option');
                            option.value = agency.agency_id;
                            option.textContent = agency.agency_name;
                            option.setAttribute('data-dmc', JSON.stringify(agency.dmc_id || []));
                            agencySelect.appendChild(option);
                        });
                    }
                    
                    agencySelect.disabled = false;
                    
                    // If there was a previous agency selected and it still exists, keep it selected
                    // Otherwise, clear agents
                    if (previousAgencyValue && previousAgencyValue !== '' && previousAgencyValue !== 'Loading agencies...') {
                        const optionExists = Array.from(agencySelect.options).some(opt => opt.value === previousAgencyValue);
                        if (optionExists) {
                            agencySelect.value = previousAgencyValue;
                            console.log('Agency value restored to:', previousAgencyValue);
                            // Manually trigger agent loading for this agency with pre-selected agent
                            loadAgentsByAgency(previousAgencyValue, previousAgentValue);
                        } else {
                            agencySelect.value = '';
                            clearAgents();
                        }
                    } else {
                        clearAgents();
                    }
                })
                .catch(error => {
                    console.error('Error loading agencies:', error);
                    agencySelect.innerHTML = '<option value="">Error loading agencies</option>';
                    agencySelect.disabled = false;
                    clearAgents();
                });
        }
        
        // Helper function to clear agents dropdown - show all agents
        function clearAgents() {
            if (!agentSelect) return;
            
            // Show all agents - no filtering by DMC or agency
            const agentOptions = agentSelect.querySelectorAll('option');
            
            agentOptions.forEach(option => {
                option.style.display = 'block';
            });
            
            // Reset agent selection
            agentSelect.value = '';
        }

        function filterAgentsByDmc() {
            const selectedDmc = dmcSelect ? dmcSelect.value : '';
            
            // Load agencies for the selected DMC
            if (selectedDmc) {
                loadAgenciesByDmc(selectedDmc);
            } else {
                // Clear agencies if no DMC selected
                if (agencySelect) {
                    const currentAgencyValue = agencySelect.value;
                    agencySelect.innerHTML = '<option value="">All Agencies</option>';
                    agencySelect.value = '';
                    
                    // If agency was cleared, also clear agents
                    if (currentAgencyValue) {
                        clearAgents();
                    }
                }
            }
        }

        // Function to load agents by agency - ONLY filter by agency_id
        function loadAgentsByAgency(agencyId, preSelectedAgentId = null) {
            console.log('loadAgentsByAgency called with agencyId:', agencyId, 'preSelectedAgentId:', preSelectedAgentId);
            
            if (!agentSelect) {
                console.error('agentSelect is null');
                return;
            }
            
            if (!agencyId) {
                // If no agency selected, clear and show all agents
                clearAgents();
                return;
            }

            // Store the current agent value or use preSelectedAgentId
            const previousAgentValue = preSelectedAgentId || agentSelect.value;
            console.log('Preserving agent value:', previousAgentValue);
            
            // Show loading state
            agentSelect.innerHTML = '<option value="">Loading agents...</option>';
            agentSelect.disabled = true;

            // Build URL with ONLY agency_id - no DMC filtering
            let url = `{{ url('/reports/fetch-agents-by-agency') }}?agency_id=${agencyId}`;
            console.log('Fetching agents from URL:', url);

            // Fetch agents for the selected agency - ONLY by agency_id
            fetch(url)
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Agents data received:', data);
                    agentSelect.innerHTML = '<option value="">All Agents</option>';
                    
                    if (data.success && data.agents && data.agents.length > 0) {
                        console.log('Found', data.agents.length, 'agents');
                        data.agents.forEach(agent => {
                            const option = document.createElement('option');
                            option.value = agent.agent_id;
                            option.textContent = agent.name;
                            option.setAttribute('data-agency', agent.agency_id || '');
                            agentSelect.appendChild(option);
                        });
                        
                        // If there was a previous agent selected and it still exists, keep it selected
                        if (previousAgentValue && previousAgentValue !== '' && previousAgentValue !== 'Loading agents...') {
                            const optionExists = Array.from(agentSelect.options).some(opt => opt.value === previousAgentValue);
                            if (optionExists) {
                                agentSelect.value = previousAgentValue;
                                console.log('Agent value restored to:', previousAgentValue);
                            } else {
                                console.warn('Previous agent value not found in loaded agents:', previousAgentValue);
                            }
                        }
                    } else {
                        // No agents found for this agency
                        console.warn('No agents found or success is false:', data);
                        agentSelect.innerHTML = '<option value="">No agents found</option>';
                    }
                    
                    agentSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error loading agents:', error);
                    agentSelect.innerHTML = '<option value="">Error loading agents</option>';
                    agentSelect.disabled = false;
                });
        }

        function filterAgentsByAgency() {
            console.log('filterAgentsByAgency called');
            
            // Check if agentSelect exists (it may not if no agents are available)
            const agentSelectElement = agentSelect || document.getElementById('agent_id');
            if (!agentSelectElement) {
                console.log('Agent select not available (no agents in dropdown)');
                return;
            }
            
            // Get agency select element - try multiple ways
            let agencySelectElement = agencySelect;
            if (!agencySelectElement) {
                agencySelectElement = document.getElementById('agency_id');
            }
            
            const selectedAgency = agencySelectElement ? agencySelectElement.value : '';
            console.log('Selected agency:', selectedAgency);
            
            // If agency is selected, load agents for that agency
            if (selectedAgency) {
                console.log('Calling loadAgentsByAgency with:', selectedAgency);
                loadAgentsByAgency(selectedAgency);
            } else {
                // If no agency selected, show all agents (filtered by DMC if selected)
                console.log('No agency selected, clearing agents');
                clearAgents();
            }
        }

        // Currency change handler
        function updateExchangeRate() {
            // Get currency value - try multiple ways
            let currency;
            if (currencySelect && currencySelect.value) {
                currency = currencySelect.value;
            } else {
                const currencyElement = document.getElementById('currency');
                currency = currencyElement ? currencyElement.value : 'SGD';
            }
            
            const exchangeRateInput = document.getElementById('exchangeRate');
            const editRateBtn = document.getElementById('editRateBtn');
            
            // Debug logging
            console.log('updateExchangeRate called with currency:', currency);
            console.log('Current customExchangeRate:', customExchangeRate);
            console.log('exchangeRateInput element:', exchangeRateInput);
            console.log('editRateBtn element:', editRateBtn);
            
            if (!exchangeRateInput) {
                console.error('exchangeRate input element not found!');
                return;
            }
            
            if (currency === 'INR') {
                // Ensure we have a valid rate, default to 67.50 if not set
                if (!customExchangeRate || customExchangeRate <= 0) {
                    customExchangeRate = 67.50;
                    console.log('Reset customExchangeRate to default:', customExchangeRate);
                }
                const displayValue = `1 SGD = ${customExchangeRate.toFixed(2)} INR`;
                exchangeRateInput.value = displayValue;
                
                if (editRateBtn) {
                    editRateBtn.style.display = 'inline-block';
                }
                
                const customExchangeRateField = document.getElementById('customExchangeRateField');
                if (customExchangeRateField) {
                    customExchangeRateField.value = customExchangeRate.toFixed(2);
                }
                
                console.log('Set INR display:', displayValue);
            } else {
                exchangeRateInput.value = '1 SGD = 1.00 SGD';
                
                if (editRateBtn) {
                    editRateBtn.style.display = 'none';
                }
                
                // Hide edit section if visible
                const rateEditSection = document.getElementById('rateEditSection');
                if (rateEditSection) {
                    rateEditSection.style.display = 'none';
                }
                
                // For SGD, set exchange rate to 1.00 (no conversion needed)
                const customExchangeRateField = document.getElementById('customExchangeRateField');
                if (customExchangeRateField) {
                    customExchangeRateField.value = '1.00';
                }
                
                console.log('Set SGD display: 1 SGD = 1.00 SGD');
            }
        }

        // Event listeners
        startInput.addEventListener('change', setEndDateLimits);
        if (masterDmcSelect) {
            masterDmcSelect.addEventListener('change', filterDmcsByMaster);
        }
        if (dmcSelect) {
            dmcSelect.addEventListener('change', filterAgentsByDmc);
        }
        if (agencySelect) {
            agencySelect.addEventListener('change', filterAgentsByAgency);
        }
        currencySelect.addEventListener('change', updateExchangeRate);

        // Initialize on page load
        setEndDateLimits();
        if (masterDmcSelect) {
            filterDmcsByMaster();
        }
        
        // For DMC, Sales Head, Sales Manager, and Assistant Sales Manager, load their agencies on page load
        @php
            $user = Auth::user();
            $userDmcId = null;
            
            if ($user->role_id == 11) {
                // DMC - use their own ID
                $userDmcId = $user->userId;
            } elseif (in_array($user->role_id, [33, 128, 129, 130, 134, 135, 136, 138])) {
                // Sales Head - DMC is their creator
                $userDmcId = $user->created_by;
            } elseif ($user->role_id == 37) {
                // Sales Manager - get DMC through Sales Head
                $salesHead = \App\Models\User::where('userId', $user->created_by)->first();
                if ($salesHead) {
                    $userDmcId = $salesHead->created_by;
                }
            } elseif ($user->role_id == 38) {
                // Assistant Sales Manager - get DMC through Sales Manager -> Sales Head
                $salesManager = \App\Models\User::where('userId', $user->created_by)->first();
                if ($salesManager) {
                    $salesHead = \App\Models\User::where('userId', $salesManager->created_by)->first();
                    if ($salesHead) {
                        $userDmcId = $salesHead->created_by;
                    }
                }
            }
        @endphp
        
        // Get pre-selected values from request
        const preSelectedAgencyId = '{{ request('agency_id', '') }}';
        const preSelectedAgentId = '{{ request('agent_id', '') }}';
        
        @if($userDmcId)
            if (dmcSelect) {
                // Set DMC to user's DMC (only if not already set from request)
                if (!dmcSelect.value) {
                    dmcSelect.value = '{{ $userDmcId }}';
                    loadAgenciesByDmc('{{ $userDmcId }}', preSelectedAgencyId, preSelectedAgentId);
                } else if (dmcSelect.value === '{{ $userDmcId }}') {
                    // If already set to user's DMC, just load agencies
                    loadAgenciesByDmc('{{ $userDmcId }}', preSelectedAgencyId, preSelectedAgentId);
                }
            }
        @else
            // For other users, if DMC is pre-selected, load agencies
            if (dmcSelect && dmcSelect.value) {
                loadAgenciesByDmc(dmcSelect.value, preSelectedAgencyId, preSelectedAgentId);
            }
        @endif
        
        // If agency is pre-selected but DMC wasn't (admin without DMC selection), load agents directly
        @if(request('agency_id') && !request('dmc_id'))
            if (agencySelect && agencySelect.value && !dmcSelect) {
                console.log('Page load: Direct agency load, loading agents with pre-selected agent:', preSelectedAgentId);
                loadAgentsByAgency(agencySelect.value, preSelectedAgentId);
            }
        @endif
        
        // Debug: Check if elements exist before calling updateExchangeRate
        console.log('currencySelect element:', currencySelect);
        console.log('exchangeRate element:', document.getElementById('exchangeRate'));
        console.log('editRateBtn element:', document.getElementById('editRateBtn'));
        
        updateExchangeRate();
    });
    
    // Also call the force update after a short delay to ensure everything is loaded
    setTimeout(function() {
        console.log('Calling forceUpdateExchangeRate after page load');
        forceUpdateExchangeRate();
    }, 500);
    
    // Additional fallback - force update on window load
    window.addEventListener('load', function() {
        setTimeout(function() {
            console.log('Window loaded - calling forceUpdateExchangeRate again');
            forceUpdateExchangeRate();
        }, 1000);
    });
    
    // Add additional currency change listener for all users
    document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'currency') {
            console.log('Currency changed via document listener:', e.target.value);
            setTimeout(function() {
                forceUpdateExchangeRate();
            }, 100);
        }
    });
    
    // Manual trigger function for testing (can be called from browser console)
    window.fixExchangeRate = function() {
        console.log('Manual exchange rate fix triggered');
        forceUpdateExchangeRate();
    };

    // Filter functions
    function applyFilters() {
        // Ensure the custom exchange rate is set in the hidden field based on currency
        const currency = document.getElementById('currency').value;
        if (currency === 'INR') {
            // Use custom rate, default to 67.50 if not set
            const rateToUse = customExchangeRate || 67.50;
            document.getElementById('customExchangeRateField').value = rateToUse.toFixed(2);
        } else {
            document.getElementById('customExchangeRateField').value = '1.00';
        }
        
        // Debug: Log the form data before submission
        console.log('Submitting form with currency:', currency);
        console.log('Exchange rate:', document.getElementById('customExchangeRateField').value);
        
        document.getElementById('ledgerFilterForm').submit();
    }

    function resetFilters() {
        // Reset all form fields
        document.getElementById('start_date').value = '';
        document.getElementById('end_date').value = '';
        document.getElementById('master_dmc_id').value = '';
        document.getElementById('dmc_id').value = '';
        const agencySelect = document.getElementById('agency_id');
        if (agencySelect) {
            agencySelect.innerHTML = '<option value="">All Agencies</option>';
        }
        document.getElementById('agent_id').value = '';
        document.getElementById('service_type').value = '';
        document.getElementById('currency').value = 'SGD';
        document.getElementById('view_type').value = 'summary';
        
        // Reset custom exchange rate to default
        customExchangeRate = 67.50;
        document.getElementById('customExchangeRateField').value = '1.00'; // Default for SGD
        
        // Reset filters and submit
        setTimeout(() => {
            document.getElementById('ledgerFilterForm').submit();
        }, 100);
    }

    function refreshExchangeRate() {
        // Simulate API call to get live exchange rate
        const exchangeRateInput = document.getElementById('exchangeRate');
        const currency = document.getElementById('currency').value;
        
        exchangeRateInput.value = 'Loading...';
        
        // Simulate API delay
        setTimeout(() => {
            if (currency === 'INR') {
                // You can replace this with actual API call
                const rate = (67.50 + Math.random() * 0.1).toFixed(2);
                customExchangeRate = parseFloat(rate);
                exchangeRateInput.value = `1 SGD = ${customExchangeRate.toFixed(2)} INR`;
                document.getElementById('customExchangeRateField').value = customExchangeRate.toFixed(2);
                
                // Apply filters to refresh data with new rate
                showRateUpdateMessage('Exchange rate updated! Refreshing data...');
                setTimeout(() => {
                    applyFilters();
                }, 500);
            } else {
                exchangeRateInput.value = '1 SGD = 1.00 SGD';
            }
        }, 1000);
    }

    // Exchange rate editing functions
    function toggleRateEdit() {
        const rateEditSection = document.getElementById('rateEditSection');
        const customRateInput = document.getElementById('customRate');
        const editRateBtn = document.getElementById('editRateBtn');
        
        if (rateEditSection.style.display === 'none') {
            rateEditSection.style.display = 'block';
            customRateInput.value = customExchangeRate.toFixed(2);
            editRateBtn.innerHTML = '<i class="ri-eye-line"></i>';
            editRateBtn.classList.remove('btn-outline-info');
            editRateBtn.classList.add('btn-outline-secondary');
        } else {
            rateEditSection.style.display = 'none';
            editRateBtn.innerHTML = '<i class="ri-edit-line"></i>';
            editRateBtn.classList.remove('btn-outline-secondary');
            editRateBtn.classList.add('btn-outline-info');
        }
    }

    function updateCustomRate() {
        const customRateInput = document.getElementById('customRate');
        const newRate = parseFloat(customRateInput.value);
        
        if (isNaN(newRate) || newRate <= 0) {
            alert('Please enter a valid positive number for the exchange rate.');
            return;
        }
        
        customExchangeRate = newRate;
        document.getElementById('exchangeRate').value = `1 SGD = ${customExchangeRate.toFixed(2)} INR`;
        
        // Update the hidden field
        document.getElementById('customExchangeRateField').value = customExchangeRate.toFixed(2);
        
        // Hide the edit section
        document.getElementById('rateEditSection').style.display = 'none';
        const editRateBtn = document.getElementById('editRateBtn');
        editRateBtn.innerHTML = '<i class="ri-edit-line"></i>';
        editRateBtn.classList.remove('btn-outline-secondary');
        editRateBtn.classList.add('btn-outline-info');
        
        // Show success message and apply filters to refresh data
        showRateUpdateMessage('Exchange rate updated! Refreshing data...');
        
        // Apply filters to refresh the page with new exchange rate
        setTimeout(() => {
            applyFilters();
        }, 500);
    }

    function cancelRateEdit() {
        const rateEditSection = document.getElementById('rateEditSection');
        const editRateBtn = document.getElementById('editRateBtn');
        
        rateEditSection.style.display = 'none';
        editRateBtn.innerHTML = '<i class="ri-edit-line"></i>';
        editRateBtn.classList.remove('btn-outline-secondary');
        editRateBtn.classList.add('btn-outline-info');
    }

    function showRateUpdateMessage(message) {
        // Create a simple alert notification
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            <i class="ri-check-circle-line me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 3000);
    }

    // Transaction detail functions
    function viewTransactionDetails(transactionId) {
        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('transactionDetailsModal'));
        modal.show();
        
        // Reset modal content to loading state
        document.getElementById('transactionDetailsContent').innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading transaction details...</p>
            </div>
        `;
        
        // Fetch transaction details
        fetch(`{{ url('/reports/transaction-details') }}/${transactionId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayTransactionDetails(data.transaction);
                } else {
                    showError('transactionDetailsContent', 'Failed to load transaction details.');
                }
            })
            .catch(error => {
                console.error('Error fetching transaction details:', error);
                showError('transactionDetailsContent', 'Error loading transaction details.');
            });
    }

    function viewBalanceHistory(agentId) {
        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('balanceHistoryModal'));
        // Store agent ID for export function
        document.getElementById('balanceHistoryModal').setAttribute('data-agent-id', agentId);
        modal.show();
        
        // Reset modal content to loading state
        document.getElementById('balanceHistoryContent').innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading balance history...</p>
            </div>
        `;
        
        // Fetch balance history
        fetch(`{{ url('/reports/balance-history') }}/${agentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) { 
                    displayBalanceHistory(data.history, data.agent);
                } else {
                    showError('balanceHistoryContent', 'Failed to load balance history.');
                }
            })
            .catch(error => {
                console.error('Error fetching balance history:', error);
                showError('balanceHistoryContent', 'Error loading balance history.');
            });
    }

    function exportTransaction(transactionId) {
        // Show loading state
        const button = event.target.closest('.dropdown-item');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="ri-loader-line me-2"></i>Exporting...';
        button.disabled = true;
        
        // Create download link
        const link = document.createElement('a');
        link.href = `{{ url('/reports/export-transaction') }}/${transactionId}`;
        link.download = `transaction_${transactionId}.pdf`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Reset button state
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        }, 2000);
    }

    // Helper functions
    function displayTransactionDetails(transaction) {
        // Get current currency and exchange rate
        const currencyElement = document.getElementById('currency');
        const currency = currencyElement ? currencyElement.value : 'SGD';
        const exchangeRate = currency === 'INR' ? (customExchangeRate || 67.50) : 1.00;
        const currencySymbol = currency === 'INR' ? '₹' : 'S$';
        
        // Calculate amounts
        const amountSGD = parseFloat(transaction.amount || 0);
        const amountConverted = amountSGD * exchangeRate;
        
        // Format service type badge
        const serviceTypeBadges = {
            'hotel': '<span class="badge bg-primary"><i class="ri-hotel-line me-1"></i>Hotel</span>',
            'attraction': '<span class="badge bg-danger"><i class="ri-map-pin-line me-1"></i>Attraction</span>',
            'guide': '<span class="badge bg-info"><i class="ri-guide-line me-1"></i>Guide</span>',
            'driver': '<span class="badge bg-dark"><i class="ri-steering-line me-1"></i>Driver</span>',
            'entry_port': '<span class="badge bg-success"><i class="ri-plane-line me-1"></i>Arrival</span>',
            'exit_port': '<span class="badge bg-warning"><i class="ri-plane-line me-1"></i>Departure</span>',
            'travel_point': '<span class="badge bg-info"><i class="ri-map-line me-1"></i>Travel Point</span>',
            'travel_hourly': '<span class="badge bg-secondary"><i class="ri-time-line me-1"></i>Travel Hourly</span>'
        };
        
        const serviceBadge = serviceTypeBadges[transaction.service_type] || `<span class="badge bg-primary">${transaction.service_type}</span>`;
        
        // Format date
        const transactionDate = new Date(transaction.created_at);
        const formattedDate = transactionDate.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        const formattedTime = transactionDate.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        
        const content = `
            <!-- Alert Message -->
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="ri-information-line me-2"></i>
                <strong>Transaction Details</strong> - Viewing complete information for Booking ID: <strong>${transaction.booking_id}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            
            <div class="row g-3">
                <!-- Transaction Information Card -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <h6 class="mb-0"><i class="ri-file-text-line me-2"></i>Transaction Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3 pb-3 border-bottom">
                                <small class="text-muted d-block mb-1">Transaction ID</small>
                                <h5 class="mb-0 text-primary">#${transaction.id}</h5>
                            </div>
                            <div class="mb-3 pb-3 border-bottom">
                                <small class="text-muted d-block mb-1">Booking ID</small>
                                <h6 class="mb-0">${serviceBadge} <span class="text-dark ms-2">${transaction.booking_id}</span></h6>
                            </div>
                            <div class="mb-3 pb-3 border-bottom">
                                <small class="text-muted d-block mb-1">Transaction Amount</small>
                                <div class="d-flex flex-column">
                                    <h4 class="mb-1 text-success fw-bold">${currencySymbol}${amountConverted.toFixed(2)}</h4>
                                    ${currency === 'INR' ? `<small class="text-muted">Original: S$${amountSGD.toFixed(2)} (Rate: 1 SGD = ${exchangeRate.toFixed(2)} INR)</small>` : ''}
                                </div>
                            </div>
                            <div class="mb-0">
                                <small class="text-muted d-block mb-1">Date & Time</small>
                                <div>
                                    <i class="ri-calendar-line text-primary me-1"></i><strong>${formattedDate}</strong><br>
                                    <i class="ri-time-line text-primary me-1"></i><span>${formattedTime}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Customer & Agent Information Card -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="ri-team-line me-2"></i>Customer & Agent Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3 pb-3 border-bottom">
                                <small class="text-muted d-block mb-1"><i class="ri-user-star-line me-1"></i>Agent Details</small>
                                <h6 class="mb-1 text-dark">${transaction.agent_name || 'N/A'}</h6>
                            </div>
                            <div class="mb-3 pb-3 border-bottom">
                                <small class="text-muted d-block mb-1"><i class="ri-user-line me-1"></i>Customer Details</small>
                                ${transaction.customer_name && transaction.customer_name !== 'N/A' ? 
                                    `<h6 class="mb-1 text-dark">${transaction.customer_name}</h6>
                                    <small class="text-muted"><i class="ri-mail-line me-1"></i>${transaction.customer_email || 'No email provided'}</small>` : 
                                    '<p class="text-muted mb-0"><i class="ri-error-warning-line me-1"></i>No customer information available</p>'
                                }
                            </div>
                            <div class="mb-0">
                                <small class="text-muted d-block mb-1">Transaction Status</small>
                                <span class="badge ${transaction.status == 1 ? 'bg-success' : 'bg-danger'} px-3 py-2">
                                    <i class="ri-${transaction.status == 1 ? 'check' : 'close'}-circle-line me-1"></i>
                                    ${transaction.status == 1 ? 'Active' : 'Inactive'}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Currency Info Footer -->
            <div class="alert alert-light border mt-3 mb-0" role="alert">
                <div class="d-flex align-items-center">
                    <i class="ri-information-line text-info me-2" style="font-size: 1.2rem;"></i>
                    <small class="text-muted mb-0">
                        <strong>Note:</strong> Amounts are displayed in <strong>${currency === 'INR' ? 'Indian Rupees (INR)' : 'Singapore Dollars (SGD)'}</strong> based on your current currency selection.
                        ${currency === 'INR' ? ` Exchange rate: 1 SGD = ${exchangeRate.toFixed(2)} INR` : ''}
                    </small>
                </div>
            </div>
        `;
        document.getElementById('transactionDetailsContent').innerHTML = content;
    }

    function displayBalanceHistory(history, agent) {
        // Store the full history data globally for search and pagination
        window.fullBalanceHistory = history;
        window.currentAgent = agent;
        window.currentPage = 1;
        window.itemsPerPage = 10;
        window.searchTerm = '';
        
        renderBalanceHistoryTable();
    }

    function renderBalanceHistoryTable() {
        const history = window.fullBalanceHistory || [];
        const agent = window.currentAgent || {};
        const currentPage = window.currentPage || 1;
        const itemsPerPage = window.itemsPerPage || 10;
        const searchTerm = window.searchTerm || '';
        
        console.log('Rendering table with:', {
            historyCount: history.length,
            searchTerm: searchTerm,
            currentPage: currentPage,
            itemsPerPage: itemsPerPage
        });
        
        // Filter data based on search term
        let filteredHistory = history;
        if (searchTerm && searchTerm.trim() !== '') {
            console.log('Applying search filter for term:', searchTerm);
            const searchLower = searchTerm.toLowerCase().trim();
            
            filteredHistory = history.filter(item => {
                const bookingId = (item.booking_id || '').toString().toLowerCase();
                const serviceType = (item.service_type || '').toString().toLowerCase();
                
                // Try multiple date formats for better search
                const transactionDate = new Date(item.created_at);
                const dateStr1 = transactionDate.toLocaleDateString(); // e.g., 11/24/2025
                const dateStr2 = transactionDate.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: '2-digit' }); // e.g., Nov 24, 2025
                const dateStr3 = transactionDate.toLocaleDateString('en-US', { month: 'short' }); // e.g., Nov
                const dateStr4 = transactionDate.getFullYear().toString(); // e.g., 2025
                
                const matches = bookingId.includes(searchLower) ||
                               serviceType.includes(searchLower) ||
                               dateStr1.toLowerCase().includes(searchLower) ||
                               dateStr2.toLowerCase().includes(searchLower) ||
                               dateStr3.toLowerCase().includes(searchLower) ||
                               dateStr4.includes(searchLower);
                
                return matches;
            });
            console.log('Filtered results:', filteredHistory.length, 'items');
        }
        
        // Calculate pagination
        const totalItems = filteredHistory.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const paginatedHistory = filteredHistory.slice(startIndex, endIndex);
        
        // Calculate running balance for the entire filtered history
        let tableRows = '';
        let runningBalance = 0;
        
        // First, calculate the running balance up to the start of current page
        for (let i = 0; i < startIndex; i++) {
            runningBalance += parseFloat(filteredHistory[i].amount || 0);
        }
        
        // Get current currency and exchange rate
        const currencyElement = document.getElementById('currency');
        const currency = currencyElement ? currencyElement.value : 'SGD';
        const exchangeRate = currency === 'INR' ? (customExchangeRate || 67.50) : 1.00;
        const currencySymbol = currency === 'INR' ? '₹' : 'S$';
        
        // Service type badge mapping
        const serviceTypeBadges = {
            'hotel': '<span class="badge bg-primary"><i class="ri-hotel-line me-1"></i>Hotel</span>',
            'attraction': '<span class="badge bg-danger"><i class="ri-map-pin-line me-1"></i>Attraction</span>',
            'guide': '<span class="badge bg-info"><i class="ri-guide-line me-1"></i>Guide</span>',
            'driver': '<span class="badge bg-dark"><i class="ri-steering-line me-1"></i>Driver</span>',
            'entry_port': '<span class="badge bg-success"><i class="ri-plane-line me-1"></i>Arrival</span>',
            'exit_port': '<span class="badge bg-warning text-dark"><i class="ri-plane-line me-1"></i>Departure</span>',
            'travel_point': '<span class="badge bg-info"><i class="ri-map-line me-1"></i>Travel Point</span>',
            'travel_hourly': '<span class="badge bg-secondary"><i class="ri-time-line me-1"></i>Travel Hourly</span>'
        };
        
        // Now generate rows for current page
        paginatedHistory.forEach((item, index) => {
            const openingBalance = runningBalance;
            const transactionAmount = parseFloat(item.amount || 0);
            runningBalance += transactionAmount;
            
            // Convert amounts based on currency
            const openingConverted = openingBalance * exchangeRate;
            const transactionConverted = transactionAmount * exchangeRate;
            const closingConverted = runningBalance * exchangeRate;
            
            // Format date
            const transactionDate = new Date(item.created_at);
            const formattedDate = transactionDate.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: '2-digit' 
            });
            const formattedTime = transactionDate.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            
            const serviceBadge = serviceTypeBadges[item.service_type] || `<span class="badge bg-primary">${item.service_type}</span>`;
            
            tableRows += `
                <tr class="align-middle">
                    <td class="text-center"><span class="badge bg-light text-dark">${startIndex + index + 1}</span></td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold text-dark">${formattedDate}</span>
                            <small class="text-muted"><i class="ri-time-line me-1"></i>${formattedTime}</small>
                        </div>
                    </td>
                    <td><span class="badge bg-info text-white">${item.booking_id}</span></td>
                    <td>${serviceBadge}</td>
                    <td class="text-end">
                        <span class="text-muted">${currencySymbol}${openingConverted.toFixed(2)}</span>
                    </td>
                    <td class="text-end">
                        <span class="fw-bold ${transactionAmount >= 0 ? 'text-success' : 'text-danger'}">
                            ${transactionAmount >= 0 ? '+' : ''}${currencySymbol}${transactionConverted.toFixed(2)}
                        </span>
                    </td>
                    <td class="text-end">
                        <span class="fw-bold text-primary">${currencySymbol}${closingConverted.toFixed(2)}</span>
                    </td>
                </tr>
            `;
        });
        
        // Calculate final balance for the entire history
        let finalBalance = 0;
        history.forEach(item => {
            finalBalance += parseFloat(item.amount || 0);
        });
        
        // Convert final balance based on currency
        const finalBalanceConverted = finalBalance * exchangeRate;
        
        // Generate pagination controls
        const paginationHtml = generatePaginationControls(currentPage, totalPages, totalItems);
        
        const content = `
            <!-- Info Alert -->
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="ri-user-star-line me-2" style="font-size: 1.5rem;"></i>
                    <div>
                        <strong>Agent Transaction History</strong> - Viewing all transactions for <strong>${agent.name}</strong>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            
            <!-- Agent Summary Card -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                        <div class="card-body text-white">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-white bg-opacity-25 rounded-circle p-3 me-3">
                                            <i class="ri-user-line" style="font-size: 2rem;"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1 fw-bold">${agent.name}</h5>
                                            <small class="opacity-75"><i class="ri-shield-check-line me-1"></i>Agent ID: ${agent.agent_id}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                    <small class="opacity-75 d-block mb-1">Final Balance</small>
                                    <h3 class="mb-0 fw-bold">${currencySymbol}${finalBalanceConverted.toFixed(2)}</h3>
                                    ${currency === 'INR' ? `<small class="opacity-75">S$${finalBalance.toFixed(2)}</small>` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Search and Controls -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-1"><i class="ri-search-line me-1"></i>Search Transactions</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="ri-search-line text-success"></i></span>
                                <input type="text" id="balanceHistorySearch" class="form-control border-start-0 ${searchTerm ? 'border-end-0' : ''}" placeholder="Search by booking ID, service type, or date..." value="${searchTerm}" autocomplete="off">
                                ${searchTerm ? `
                                    <button class="btn btn-outline-secondary border-start-0" type="button" onclick="clearBalanceHistorySearch()" title="Clear search">
                                        <i class="ri-close-line"></i>
                                    </button>
                                ` : ''}
                            </div>
                            ${searchTerm ? `<small class="text-muted"><i class="ri-filter-line me-1"></i>Filtering results...</small>` : ''}
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1"><i class="ri-list-check me-1"></i>Items Per Page</label>
                            <select id="itemsPerPageSelect" class="form-select">
                                <option value="5" ${itemsPerPage == 5 ? 'selected' : ''}>5 per page</option>
                                <option value="10" ${itemsPerPage == 10 ? 'selected' : ''}>10 per page</option>
                                <option value="25" ${itemsPerPage == 25 ? 'selected' : ''}>25 per page</option>
                                <option value="50" ${itemsPerPage == 50 ? 'selected' : ''}>50 per page</option>
                                <option value="100" ${itemsPerPage == 100 ? 'selected' : ''}>100 per page</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1"><i class="ri-information-line me-1"></i>Showing</label>
                            <div class="alert alert-light border mb-0 py-2">
                                <small class="text-dark fw-semibold mb-0">
                                    ${startIndex + 1}-${Math.min(endIndex, totalItems)} of ${totalItems}
                                    ${searchTerm ? `<br><span class="text-muted">(filtered from ${history.length})</span>` : ''}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Transaction History Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 text-dark"><i class="ri-file-list-3-line me-2 text-success"></i>Transaction History</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">
                                <tr>
                                    <th class="text-center" style="width: 60px;">#</th>
                                    <th style="min-width: 130px;">Date & Time</th>
                                    <th style="min-width: 100px;">Booking ID</th>
                                    <th style="min-width: 130px;">Service Type</th>
                                    <th class="text-end" style="min-width: 120px;">Opening Balance</th>
                                    <th class="text-end" style="min-width: 150px;">Transaction</th>
                                    <th class="text-end" style="min-width: 120px;">Closing Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableRows || `
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="${searchTerm ? 'ri-search-line' : 'ri-inbox-line'}" style="font-size: 3rem; opacity: 0.3;"></i>
                                                <p class="mt-2 mb-0 fw-semibold">
                                                    ${searchTerm ? `No results found for "${searchTerm}"` : 'No transactions found'}
                                                </p>
                                                <small>${searchTerm ? 'Try a different search term' : 'No transaction history available for this agent'}</small>
                                                ${searchTerm ? '<br><button class="btn btn-sm btn-outline-success mt-2" onclick="clearBalanceHistorySearch()"><i class="ri-close-line me-1"></i>Clear Search</button>' : ''}
                                            </div>
                                        </td>
                                    </tr>
                                `}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Pagination -->
            ${paginationHtml}
            
            <!-- Currency Info Footer -->
            <div class="alert alert-light border mt-3 mb-0" role="alert">
                <div class="d-flex align-items-center">
                    <i class="ri-information-line text-success me-2" style="font-size: 1.2rem;"></i>
                    <small class="text-muted mb-0">
                        <strong>Currency:</strong> All amounts are displayed in <strong>${currency === 'INR' ? 'Indian Rupees (INR)' : 'Singapore Dollars (SGD)'}</strong>.
                        ${currency === 'INR' ? ` Exchange rate: 1 SGD = ${exchangeRate.toFixed(2)} INR` : ''}
                        <span class="ms-2">|</span> <strong class="ms-2">Total Transactions:</strong> ${totalItems}
                    </small>
                </div>
            </div>
        `;
        
        document.getElementById('balanceHistoryContent').innerHTML = content;
        
        // Attach event listeners
        attachBalanceHistoryEventListeners();
    }

    function generatePaginationControls(currentPage, totalPages, totalItems) {
        if (totalPages <= 1) return '';
        
        let paginationHtml = '<nav aria-label="Balance history pagination"><ul class="pagination justify-content-center">';
        
        // Previous button
        paginationHtml += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="changeBalanceHistoryPage(${currentPage - 1})" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        `;
        
        // Page numbers
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
        
        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }
        
        if (startPage > 1) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="changeBalanceHistoryPage(1)">1</a></li>`;
            if (startPage > 2) {
                paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            paginationHtml += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changeBalanceHistoryPage(${i})">${i}</a>
                </li>
            `;
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="changeBalanceHistoryPage(${totalPages})">${totalPages}</a></li>`;
        }
        
        // Next button
        paginationHtml += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="changeBalanceHistoryPage(${currentPage + 1})" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        `;
        
        paginationHtml += '</ul></nav>';
        
        return paginationHtml;
    }

    function attachBalanceHistoryEventListeners() {
        // Search input - instant search on every keystroke
        const searchInput = document.getElementById('balanceHistorySearch');
        if (searchInput) {
            // Use oninput for immediate search results
            searchInput.oninput = function(event) {
                const searchValue = event.target.value;
                console.log('Search input changed:', searchValue);
                
                // Update search term immediately
                window.searchTerm = searchValue;
                window.currentPage = 1; // Reset to first page when searching
                
                // Render table instantly - no delay
                renderBalanceHistoryTable();
            };
            
            console.log('Search input event listener attached - instant search enabled');
        } else {
            console.error('Search input not found');
        }
        
        // Items per page select
        const itemsPerPageSelect = document.getElementById('itemsPerPageSelect');
        if (itemsPerPageSelect) {
            itemsPerPageSelect.onchange = function() {
                window.itemsPerPage = parseInt(this.value);
                window.currentPage = 1; // Reset to first page when changing items per page
                console.log('Items per page changed to:', window.itemsPerPage);
                renderBalanceHistoryTable();
            };
        }
    }

    function changeBalanceHistoryPage(page) {
        const totalPages = Math.ceil((window.fullBalanceHistory || []).length / (window.itemsPerPage || 10));
        if (page >= 1 && page <= totalPages) {
            window.currentPage = page;
            renderBalanceHistoryTable();
        }
        return false; // Prevent default link behavior
    }
    
    function clearBalanceHistorySearch() {
        console.log('Clearing search');
        window.searchTerm = '';
        window.currentPage = 1;
        renderBalanceHistoryTable();
    }

    function showError(containerId, message) {
        document.getElementById(containerId).innerHTML = `
            <div class="text-center">
                <i class="ri-error-warning-line text-danger" style="font-size: 48px;"></i>
                <p class="mt-2 text-danger">${message}</p>
                <button class="btn btn-outline-primary" onclick="location.reload()">
                    <i class="ri-refresh-line me-1"></i>Retry
                </button>
            </div>
        `;
    }

    function printTransactionDetails() {
        const printContent = document.getElementById('transactionDetailsContent').innerHTML;
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Transaction Details</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .card { border: 1px solid #ddd; margin-bottom: 20px; }
                        .card-header { background: #f8f9fa; padding: 10px; font-weight: bold; }
                        .card-body { padding: 15px; }
                        .table { width: 100%; border-collapse: collapse; }
                        .table td { padding: 8px; border-bottom: 1px solid #eee; }
                        .fw-semibold { font-weight: 600; }
                        .badge { padding: 4px 8px; border-radius: 4px; color: white; }
                        .bg-primary { background-color: #0d6efd; }
                        .bg-info { background-color: #0dcaf0; }
                        .bg-success { background-color: #198754; }
                        .text-success { color: #198754; }
                    </style>
                </head>
                <body>
                    <h2>Transaction Details</h2>
                    ${printContent}
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }

    function exportBalanceHistory() {
        const agentId = document.getElementById('balanceHistoryModal').getAttribute('data-agent-id');
        if (agentId) {
            const link = document.createElement('a');
            link.href = `{{ url('/reports/export-balance-history') }}/${agentId}`;
            link.download = `balance_history_${agentId}.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }
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
    
    /* Enhanced Ledger Styles */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .bg-purple {
        background-color: #6f42c1;
    }
    
    .card-header {
        border-bottom: 2px solid rgba(0,0,0,0.05);
    }
    
    .form-label.fw-semibold {
        color: #495057;
        font-size: 0.9rem;
    }
    
    .input-group-text {
        background: #f8f9fa;
        border-color: #dee2e6;
        color: #6c757d;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
    }
    
    .btn-secondary {
        background: #6c757d;
        border: none;
    }
    
    .btn-info {
        background: #17a2b8;
        border: none;
    }
    
    .btn-warning {
        background: #ffc107;
        border: none;
        color: #212529;
    }
    
    .table thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.85rem;
    }
    
    .table tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
        transform: scale(1.01);
        transition: all 0.3s ease;
    }
    
    .table tfoot th {
        background: #f8f9fa;
        border-top: 2px solid #dee2e6;
        font-weight: 700;
    }
    
    .badge.bg-info {
        background-color: #0dcaf0 !important;
    }
    
    .badge.bg-light {
        background-color: #f8f9fa !important;
        color: #495057 !important;
        border: 1px solid #dee2e6;
    }
    
    /* Currency conversion styling */
    .currency-display {
        position: relative;
    }
    
    .currency-display small {
        opacity: 0.7;
        font-size: 0.7rem;
    }
    
    /* Loading animation */
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    
    .loading {
        animation: pulse 1.5s infinite;
    }
    
    /* Action buttons */
    .dropdown-toggle::after {
        margin-left: 0.5em;
    }
    
    .dropdown-item {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
    
    .dropdown-item:hover {
        background-color: rgba(102, 126, 234, 0.1);
        color: #667eea;
    }
    
    /* Exchange rate editing styles */
    #rateEditSection {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 0.5rem;
    }
    
    #customRate {
        text-align: center;
        font-weight: 600;
    }
    
    #editRateBtn {
        border-left: 1px solid #dee2e6;
    }
    
    /* Hide DataTables default buttons */
    .dt-buttons {
        display: none !important;
    }
    
    .dataTables_wrapper .dt-buttons {
        display: none !important;
    }
    
    /* Transaction Details Modal Styling */
    #transactionDetailsModal .modal-content {
        border-radius: 15px;
        overflow: hidden;
    }
    
    #transactionDetailsModal .modal-header {
        padding: 1.5rem;
        border-bottom: none;
    }
    
    #transactionDetailsModal .modal-body {
        padding: 1.5rem;
    }
    
    #transactionDetailsModal .card {
        border-radius: 10px;
        transition: transform 0.2s ease;
    }
    
    #transactionDetailsModal .card:hover {
        transform: translateY(-2px);
    }
    
    #transactionDetailsModal .card-header {
        padding: 1rem 1.25rem;
        border-bottom: none;
        font-weight: 600;
    }
    
    #transactionDetailsModal .border-bottom {
        border-color: #e9ecef !important;
    }
    
    #transactionDetailsModal .alert {
        border-radius: 10px;
        border-left: 4px solid #0dcaf0;
    }
    
    #transactionDetailsModal .badge {
        padding: 0.5em 0.75em;
        font-weight: 500;
    }
    
    /* Balance History Modal Styling */
    #balanceHistoryModal .modal-content {
        border-radius: 15px;
        overflow: hidden;
    }
    
    #balanceHistoryModal .modal-header {
        padding: 1.5rem;
        border-bottom: none;
    }
    
    #balanceHistoryModal .modal-body {
        padding: 1.5rem;
    }
    
    #balanceHistoryModal .table thead {
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    #balanceHistoryModal .table tbody tr {
        transition: all 0.2s ease;
    }
    
    #balanceHistoryModal .table tbody tr:hover {
        background-color: rgba(17, 153, 142, 0.05);
        transform: scale(1.01);
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    #balanceHistoryModal .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    #balanceHistoryModal .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
    }
    
    #balanceHistoryModal .pagination {
        margin-top: 1rem;
    }
    
    #balanceHistoryModal .pagination .page-link {
        color: #11998e;
        border-color: #dee2e6;
    }
    
    #balanceHistoryModal .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        border-color: #11998e;
    }
    
    #balanceHistoryModal .pagination .page-link:hover {
        background-color: rgba(17, 153, 142, 0.1);
        border-color: #11998e;
    }
    
    #balanceHistoryModal .input-group-text {
        border-right: 0;
    }
    
    #balanceHistoryModal .form-control:focus {
        border-color: #11998e;
        box-shadow: 0 0 0 0.2rem rgba(17, 153, 142, 0.25);
    }
    
    #balanceHistoryModal .form-select:focus {
        border-color: #11998e;
        box-shadow: 0 0 0 0.2rem rgba(17, 153, 142, 0.25);
    }
    
    #balanceHistoryModal .alert-success {
        background-color: rgba(17, 153, 142, 0.1);
        border-color: rgba(17, 153, 142, 0.2);
        color: #0d7566;
    }
    
    #balanceHistoryModal .badge {
        padding: 0.4em 0.65em;
        font-weight: 500;
    }
    
    /* Search input styling */
    #balanceHistorySearch {
        transition: all 0.3s ease;
    }
    
    #balanceHistorySearch:focus {
        border-color: #11998e !important;
        box-shadow: 0 0 0 0.2rem rgba(17, 153, 142, 0.25) !important;
    }
    
    /* Modal animation */
    .modal.fade .modal-dialog {
        transition: transform 0.3s ease-out;
    }
    
    .modal.show .modal-dialog {
        transform: none;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.85rem;
        }
        
        .d-flex.flex-column {
            text-align: center !important;
        }
        
        .card-header h6 {
            font-size: 0.9rem;
        }
        
        #transactionDetailsModal .modal-dialog {
            margin: 0.5rem;
        }
        
        #transactionDetailsModal .card-body {
            padding: 1rem;
        }
    }
</style>
@endsection 