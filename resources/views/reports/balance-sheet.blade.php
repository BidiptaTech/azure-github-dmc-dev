@extends('layouts.layout')
@extends('layouts.datatablecss')
@section('content')
<style>
    /* Balance Sheet Specific Styles */
    .balance-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 15px;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    }
    
    .balance-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
    }
    
    .profit-card {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        border: none;
        border-radius: 15px;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px rgba(17, 153, 142, 0.3);
    }
    
    .profit-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 35px rgba(17, 153, 142, 0.4);
    }
    
    .loss-card {
        background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
        border: none;
        border-radius: 15px;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px rgba(255, 65, 108, 0.3);
    }
    
    .loss-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 35px rgba(255, 65, 108, 0.4);
    }
    
    .revenue-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 15px;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    }
    
    .revenue-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
    }
    
    .cost-card {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border: none;
        border-radius: 15px;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px rgba(240, 147, 251, 0.3);
    }
    
    .cost-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 35px rgba(240, 147, 251, 0.4);
    }
    
    .margin-card {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        border: none;
        border-radius: 15px;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px rgba(79, 172, 254, 0.3);
    }
    
    .margin-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 35px rgba(79, 172, 254, 0.4);
    }
    
    .filter-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 15px;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
    }
    
    .balance-table {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
    
    .balance-table thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .balance-table thead th {
        border: none;
        padding: 15px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.85rem;
    }
    
    .balance-table tbody tr {
        transition: all 0.3s ease;
        border-bottom: 1px solid #f1f3f4;
    }
    
    .balance-table tbody tr:hover {
        background-color: #f8f9ff;
        transform: scale(1.01);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .balance-table tbody td {
        padding: 15px;
        vertical-align: middle;
        border: none;
    }
    
    .profit-indicator {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .profit-indicator.profitable {
        background: rgba(17, 153, 142, 0.1);
        color: #11998e;
        border: 1px solid rgba(17, 153, 142, 0.2);
    }
    
    .profit-indicator.loss {
        background: rgba(255, 65, 108, 0.1);
        color: #ff416c;
        border: 1px solid rgba(255, 65, 108, 0.2);
    }
    
    .profit-indicator.breakeven {
        background: rgba(255, 193, 7, 0.1);
        color: #ffc107;
        border: 1px solid rgba(255, 193, 7, 0.2);
    }
    
    .service-badge {
        display: inline-block;
        padding: 4px 8px;
        margin: 2px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 12px;
        font-size: 0.75rem;
        color: #495057;
    }
    
    .service-badge.hotel { background: rgba(255, 193, 7, 0.1); color: #ffc107; border-color: rgba(255, 193, 7, 0.3); }
    .service-badge.attraction { background: rgba(220, 53, 69, 0.1); color: #dc3545; border-color: rgba(220, 53, 69, 0.3); }
    .service-badge.guide { background: rgba(40, 167, 69, 0.1); color: #28a745; border-color: rgba(40, 167, 69, 0.3); }
    .service-badge.driver { background: rgba(0, 123, 255, 0.1); color: #007bff; border-color: rgba(0, 123, 255, 0.3); }
    
    .period-display {
        font-weight: 600;
        color: #495057;
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        padding: 8px 15px;
        border-radius: 25px;
        border: 1px solid #dee2e6;
        display: inline-block;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .chart-container {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }
    
    .export-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .export-btn {
        padding: 8px 16px;
        border-radius: 20px;
        border: none;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .export-btn.excel {
        background: #217346;
        color: white;
    }
    
    .export-btn.pdf {
        background: #dc3545;
        color: white;
    }
    
    .export-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    
    .financial-summary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 25px;
        border-radius: 15px;
        margin-bottom: 30px;
    }
    
    .summary-item {
        text-align: center;
        padding: 15px;
    }
    
    .summary-value {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .summary-label {
        font-size: 0.9rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .export-buttons {
            justify-content: center;
        }
        
        .balance-table {
            font-size: 0.85rem;
        }
        
        .balance-table thead th,
        .balance-table tbody td {
            padding: 10px 8px;
        }
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold py-3 mb-2">
                            <i class="ri-bar-chart-box-line me-2 text-primary"></i>
                            Balance Sheet & P&L Analysis
                        </h4>
                        <p class="text-muted mb-0">Comprehensive profit & loss analysis with day-wise financial breakdown</p>
                    </div>
                    <div class="export-buttons">
                        <button class="export-btn excel" onclick="exportToExcel()">
                            <i class="ri-file-excel-2-line me-1"></i> Excel
                        </button>
                        <button class="export-btn pdf" onclick="exportToPDF()">
                            <i class="ri-file-pdf-line me-1"></i> PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Summary Cards -->
        <div class="financial-summary">
            <div class="row">
                <div class="col-md-2 col-6">
                    <div class="summary-item">
                        <div class="summary-value">₹{{ number_format($summaryStats['total_revenue'], 0) }}</div>
                        <div class="summary-label">Total Revenue</div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="summary-item">
                        <div class="summary-value">₹{{ number_format($summaryStats['total_profit'], 0) }}</div>
                        <div class="summary-label">Net Profit</div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="summary-item">
                        <div class="summary-value">{{ number_format($summaryStats['profit_margin'], 1) }}%</div>
                        <div class="summary-label">Profit Margin</div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="summary-item">
                        <div class="summary-value">{{ $summaryStats['profitable_days'] }}</div>
                        <div class="summary-label">Profitable Days</div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="summary-item">
                        <div class="summary-value">{{ $summaryStats['total_transactions'] }}</div>
                        <div class="summary-label">Transactions</div>
                    </div>
                </div>
                <div class="col-md-2 col-6">
                    <div class="summary-item">
                        <div class="summary-value">₹{{ number_format($summaryStats['avg_daily_profit'], 0) }}</div>
                        <div class="summary-label">Avg Daily Profit</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('reports.balance-sheet') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Date Range</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ri-calendar-line"></i></span>
                            <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">To</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ri-calendar-line"></i></span>
                            <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">View Type</label>
                        <select class="form-select" name="view_type">
                            <option value="daily" {{ $viewType == 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly" {{ $viewType == 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ $viewType == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Agent</label>
                        <select class="form-select" name="agent_id">
                            <option value="">All Agents</option>
                            @foreach($agentsForDropdown as $agent)
                                <option value="{{ $agent->agent_id }}" {{ $agentId == $agent->agent_id ? 'selected' : '' }}>
                                    {{ $agent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-search-line"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Traditional Balance Sheet -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="chart-container">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">
                            <i class="ri-scales-3-line me-2 text-primary"></i>
                            Traditional Balance Sheet
                        </h5>
                        <div class="d-flex align-items-center">
                            @if($traditionalBalanceSheet['balance_check'])
                                <span class="badge bg-success">
                                    <i class="ri-check-line me-1"></i>Balanced
                                </span>
                            @else
                                <span class="badge bg-warning">
                                    <i class="ri-alert-line me-1"></i>Review Required
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Assets Column -->
                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <thead>
                                        <tr class="bg-primary text-white">
                                            <th colspan="2" class="text-center fw-bold">ASSETS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Current Assets -->
                                        <tr class="bg-light">
                                            <td colspan="2" class="fw-bold text-primary">Current Assets</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">- Cash & Equivalents</td>
                                            <td class="text-end">₹{{ number_format($traditionalBalanceSheet['current_assets']['cash_and_equivalents'], 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">- Accounts Receivable</td>
                                            <td class="text-end">₹{{ number_format($traditionalBalanceSheet['current_assets']['accounts_receivable'], 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">- Inventory (Bookings)</td>
                                            <td class="text-end">₹{{ number_format($traditionalBalanceSheet['current_assets']['inventory'], 0) }}</td>
                                        </tr>
                                        <tr class="border-top">
                                            <td class="fw-bold">Total Current Assets</td>
                                            <td class="text-end fw-bold">₹{{ number_format($traditionalBalanceSheet['current_assets']['total'], 0) }}</td>
                                        </tr>
                                        
                                        <!-- Non-Current Assets -->
                                        <tr class="bg-light">
                                            <td colspan="2" class="fw-bold text-primary pt-3">Non-Current Assets</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">- Guide Services</td>
                                            <td class="text-end">₹{{ number_format($traditionalBalanceSheet['non_current_assets']['equipment'], 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">- Driver Services</td>
                                            <td class="text-end">₹{{ number_format($traditionalBalanceSheet['non_current_assets']['vehicles'], 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">- Hotel Partnerships</td>
                                            <td class="text-end">₹{{ number_format($traditionalBalanceSheet['non_current_assets']['property_partnerships'], 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">- Attraction Services</td>
                                            <td class="text-end">₹{{ number_format($traditionalBalanceSheet['non_current_assets']['attraction_assets'], 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">- Transport Services</td>
                                            <td class="text-end">₹{{ number_format($traditionalBalanceSheet['non_current_assets']['transport_assets'], 0) }}</td>
                                        </tr>
                                        <tr class="border-top">
                                            <td class="fw-bold">Total Non-Current Assets</td>
                                            <td class="text-end fw-bold">₹{{ number_format($traditionalBalanceSheet['non_current_assets']['total'], 0) }}</td>
                                        </tr>
                                        
                                        <!-- Total Assets -->
                                        <tr class="border-top border-bottom border-2 bg-primary text-white">
                                            <td class="fw-bold">TOTAL ASSETS</td>
                                            <td class="text-end fw-bold">₹{{ number_format($traditionalBalanceSheet['total_assets'], 0) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Liabilities & Equity Column -->
                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <thead>
                                        <tr class="bg-success text-white">
                                            <th colspan="2" class="text-center fw-bold">LIABILITIES & EQUITY</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Current Liabilities -->
                                        <tr class="bg-light">
                                            <td colspan="2" class="fw-bold text-success">Current Liabilities</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">- Accounts Payable</td>
                                            <td class="text-end">₹{{ number_format($traditionalBalanceSheet['current_liabilities']['accounts_payable'], 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">- Tax Payable</td>
                                            <td class="text-end">₹{{ number_format($traditionalBalanceSheet['current_liabilities']['tax_payable'], 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">- Commission Payable</td>
                                            <td class="text-end">₹{{ number_format($traditionalBalanceSheet['current_liabilities']['commission_payable'], 0) }}</td>
                                        </tr>
                                        <tr class="border-top">
                                            <td class="fw-bold">Total Current Liabilities</td>
                                            <td class="text-end fw-bold">₹{{ number_format($traditionalBalanceSheet['current_liabilities']['total'], 0) }}</td>
                                        </tr>
                                        
                                        <!-- Non-Current Liabilities -->
                                        <tr class="bg-light">
                                            <td colspan="2" class="fw-bold text-success pt-3">Non-Current Liabilities</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">- Long-Term Loan</td>
                                            <td class="text-end">₹{{ number_format($traditionalBalanceSheet['non_current_liabilities']['long_term_loan'], 0) }}</td>
                                        </tr>
                                        <tr class="border-top">
                                            <td class="fw-bold">Total Liabilities</td>
                                            <td class="text-end fw-bold">₹{{ number_format($traditionalBalanceSheet['total_liabilities'], 0) }}</td>
                                        </tr>
                                        
                                        <!-- Owner's Equity -->
                                        <tr class="bg-light">
                                            <td colspan="2" class="fw-bold text-info pt-3">Owner's Equity</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">- Capital</td>
                                            <td class="text-end">₹{{ number_format($traditionalBalanceSheet['equity']['capital'], 0) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-4">- Retained Earnings</td>
                                            <td class="text-end {{ $traditionalBalanceSheet['equity']['retained_earnings'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                ₹{{ number_format($traditionalBalanceSheet['equity']['retained_earnings'], 0) }}
                                            </td>
                                        </tr>
                                        <tr class="border-top">
                                            <td class="fw-bold">Total Equity</td>
                                            <td class="text-end fw-bold">₹{{ number_format($traditionalBalanceSheet['equity']['total'], 0) }}</td>
                                        </tr>
                                        
                                        <!-- Total Liabilities + Equity -->
                                        <tr class="border-top border-bottom border-2 bg-success text-white">
                                            <td class="fw-bold">TOTAL LIABILITIES + EQUITY</td>
                                            <td class="text-end fw-bold">₹{{ number_format($traditionalBalanceSheet['total_liabilities_and_equity'], 0) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Balance Verification -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert {{ $traditionalBalanceSheet['balance_check'] ? 'alert-success' : 'alert-warning' }} d-flex align-items-center">
                                @if($traditionalBalanceSheet['balance_check'])
                                    <i class="ri-check-circle-line me-2"></i>
                                    <strong>Balance Sheet is Balanced!</strong> 
                                    Assets (₹{{ number_format($traditionalBalanceSheet['total_assets'], 0) }}) = 
                                    Liabilities + Equity (₹{{ number_format($traditionalBalanceSheet['total_liabilities_and_equity'], 0) }})
                                @else
                                    <i class="ri-alert-triangle-line me-2"></i>
                                    <strong>Balance Sheet Discrepancy Detected!</strong> 
                                    Assets: ₹{{ number_format($traditionalBalanceSheet['total_assets'], 0) }} | 
                                    Liabilities + Equity: ₹{{ number_format($traditionalBalanceSheet['total_liabilities_and_equity'], 0) }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Service Breakdown Chart -->
        @if(count($summaryStats['service_breakdown']) > 0)
        <div class="chart-container">
            <h5 class="fw-bold mb-3">
                <i class="ri-pie-chart-line me-2 text-primary"></i>
                Service-wise Performance
            </h5>
            <div class="row">
                @foreach($summaryStats['service_breakdown'] as $service)
                <div class="col-md-3 col-6 mb-3">
                    <div class="text-center p-3 border rounded">
                        <div class="service-badge {{ strtolower($service['service']) }}">
                            {{ ucfirst($service['service']) }}
                        </div>
                        <div class="mt-2">
                            <div class="fw-bold">₹{{ number_format($service['revenue'], 0) }}</div>
                            <small class="text-muted">Revenue</small>
                        </div>
                        <div class="mt-1">
                            <div class="fw-bold {{ $service['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                ₹{{ number_format($service['profit'], 0) }}
                            </div>
                            <small class="text-muted">Profit</small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Main Balance Sheet Table -->
        <div class="balance-table">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="balanceSheetTable">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Agent</th>
                            <th>Services</th>
                            <th>Transactions</th>
                            <th>Gross Revenue</th>
                            <th>Operational Costs</th>
                            <th>Taxes</th>
                            <th>Commission</th>
                            <th>Net Profit</th>
                            <th>Margin %</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($processedResults as $result)
                        <tr>
                            <td>
                                <div class="period-display">{{ $result['period'] }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $result['agent_name'] }}</div>
                                <small class="text-muted">ID: {{ $result['agent_id'] }}</small>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap">
                                    @foreach($result['services'] as $service)
                                        <span class="service-badge {{ strtolower($service['service_type']) }}" 
                                              title="Revenue: ₹{{ number_format($service['gross_revenue'], 0) }}">
                                            {{ ucfirst($service['service_type']) }}
                                            <small>({{ $service['transaction_count'] }})</small>
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $result['transaction_count'] }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-primary">₹{{ number_format($result['gross_revenue'], 2) }}</div>
                            </td>
                            <td>
                                <div class="text-danger">₹{{ number_format($result['operational_costs'], 2) }}</div>
                            </td>
                            <td>
                                <div class="text-warning">₹{{ number_format($result['taxes_paid'], 2) }}</div>
                            </td>
                            <td>
                                <div class="text-success">₹{{ number_format($result['commission_earned'], 2) }}</div>
                            </td>
                            <td>
                                <div class="fw-bold {{ $result['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    ₹{{ number_format($result['net_profit'], 2) }}
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold {{ $result['profit_margin_percentage'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($result['profit_margin_percentage'], 1) }}%
                                </div>
                            </td>
                            <td>
                                <span class="profit-indicator {{ $result['profitability_status'] }}">
                                    @if($result['profitability_status'] == 'profitable')
                                        <i class="ri-arrow-up-line"></i> Profit
                                    @elseif($result['profitability_status'] == 'loss')
                                        <i class="ri-arrow-down-line"></i> Loss
                                    @else
                                        <i class="ri-subtract-line"></i> Break Even
                                    @endif
                                </span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="ri-more-2-line"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="viewDetails('{{ $result['agent_id'] }}', '{{ $result['period'] }}')">
                                            <i class="ri-eye-line me-2"></i>View Details
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="exportRecord('{{ $result['agent_id'] }}', '{{ $result['period'] }}')">
                                            <i class="ri-download-line me-2"></i>Export
                                        </a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="ri-file-list-line" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <h6 class="mt-3">No balance sheet data found</h6>
                                    <p>Try adjusting your date range or filters</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Performance Insights -->
        @if(count($processedResults) > 0)
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent">
                        <h6 class="fw-bold mb-0">
                            <i class="ri-lightbulb-line me-2 text-warning"></i>
                            Performance Insights
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            @if($summaryStats['profit_margin'] > 20)
                                <li class="mb-2">
                                    <i class="ri-check-line text-success me-2"></i>
                                    Excellent profit margin of {{ number_format($summaryStats['profit_margin'], 1) }}%
                                </li>
                            @elseif($summaryStats['profit_margin'] > 10)
                                <li class="mb-2">
                                    <i class="ri-information-line text-info me-2"></i>
                                    Good profit margin of {{ number_format($summaryStats['profit_margin'], 1) }}%
                                </li>
                            @else
                                <li class="mb-2">
                                    <i class="ri-alert-line text-warning me-2"></i>
                                    Low profit margin of {{ number_format($summaryStats['profit_margin'], 1) }}% - Consider cost optimization
                                </li>
                            @endif
                            
                            @if($summaryStats['profitable_days'] > $summaryStats['loss_days'])
                                <li class="mb-2">
                                    <i class="ri-check-line text-success me-2"></i>
                                    More profitable days ({{ $summaryStats['profitable_days'] }}) than loss days ({{ $summaryStats['loss_days'] }})
                                </li>
                            @else
                                <li class="mb-2">
                                    <i class="ri-alert-line text-danger me-2"></i>
                                    More loss days ({{ $summaryStats['loss_days'] }}) than profitable days ({{ $summaryStats['profitable_days'] }})
                                </li>
                            @endif
                            
                            <li class="mb-2">
                                <i class="ri-user-line text-primary me-2"></i>
                                {{ $summaryStats['unique_agents'] }} agent(s) contributing to revenue
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent">
                        <h6 class="fw-bold mb-0">
                            <i class="ri-trophy-line me-2 text-warning"></i>
                            Top Performing Services
                        </h6>
                    </div>
                    <div class="card-body">
                        @php
                            $topServices = collect($summaryStats['service_breakdown'])->sortByDesc('profit')->take(3);
                        @endphp
                        @foreach($topServices as $index => $service)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="service-badge {{ strtolower($service['service']) }}">
                                    {{ ucfirst($service['service']) }}
                                </span>
                                <div class="small text-muted">{{ $service['transactions'] }} transactions</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-success">₹{{ number_format($service['profit'], 0) }}</div>
                                <div class="small text-muted">Profit</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Scripts -->
<script>
function exportToExcel() {
    const table = document.getElementById('balanceSheetTable');
    const wb = XLSX.utils.table_to_book(table, {sheet: "Balance Sheet"});
    XLSX.writeFile(wb, `balance_sheet_${new Date().toISOString().split('T')[0]}.xlsx`);
}

function exportToPDF() {
    window.print();
}

function viewDetails(agentId, period) {
    // Implementation for viewing detailed breakdown
    alert(`Viewing details for Agent ID: ${agentId}, Period: ${period}`);
}

function exportRecord(agentId, period) {
    // Implementation for exporting specific record
    alert(`Exporting record for Agent ID: ${agentId}, Period: ${period}`);
}

// Initialize DataTable with advanced features
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        $('#balanceSheetTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'desc']],
            columnDefs: [
                { orderable: false, targets: [11] }, // Actions column
                { className: 'text-center', targets: [3, 9, 10, 11] }
            ],
            dom: 'Blfrtip',
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="ri-file-excel-2-line"></i> Excel',
                    className: 'btn btn-success btn-sm'
                },
                {
                    extend: 'pdf',
                    text: '<i class="ri-file-pdf-line"></i> PDF',
                    className: 'btn btn-danger btn-sm'
                },
                {
                    extend: 'print',
                    text: '<i class="ri-printer-line"></i> Print',
                    className: 'btn btn-info btn-sm'
                }
            ],
            language: {
                search: "Search balance sheet:",
                lengthMenu: "Show _MENU_ entries per page",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    }
});
</script>

@endsection 