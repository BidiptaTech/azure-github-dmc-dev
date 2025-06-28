@extends('layouts.layout')

@section('content')
<style>
    /* Modern Dashboard Styling */
    .dashboard-container {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        padding: 2rem;
    }

    .dashboard-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        color: white;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }

    .welcome-text {
        font-size: 2.5rem;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 0.5rem;
    }

    .subtitle {
        font-size: 1.2rem;
        opacity: 0.9;
        font-weight: 300;
    }

    .time-filter {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        padding: 0.5rem;
        margin-bottom: 1rem;
    }

    .time-filter .btn {
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        margin: 0 0.2rem;
        padding: 0.4rem 1rem;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .time-filter .btn.active,
    .time-filter .btn:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.5);
        transform: translateY(-2px);
    }

    .stats-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05);
        position: relative;
        overflow: hidden;
    }

    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }

    .stats-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--card-color);
    }

    .stats-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        margin-bottom: 1rem;
    }

    .stats-number {
        font-size: 2rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 0.5rem;
    }

    .stats-label {
        color: #718096;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .stats-detail {
        font-size: 0.8rem;
        color: #a0aec0;
        margin-top: 0.5rem;
    }

    .chart-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
    }

    .chart-header {
        font-size: 1.2rem;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .activity-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        border-radius: 10px;
        margin-bottom: 0.8rem;
        background: #f8fafc;
        transition: all 0.3s ease;
    }

    .activity-item:hover {
        background: #e2e8f0;
        transform: translateX(5px);
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        color: white;
        font-size: 1rem;
    }

    .activity-content h6 {
        margin: 0 0 0.2rem 0;
        color: #2d3748;
        font-weight: 600;
    }

    .activity-content small {
        color: #718096;
    }

    .quick-action-btn {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem;
        text-decoration: none;
        color: #4a5568;
        display: flex;
        flex-direction: column;
        align-items: center;
        transition: all 0.3s ease;
        margin-bottom: 1rem;
    }

    .quick-action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        color: #667eea;
        border-color: #667eea;
    }

    .quick-action-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.8rem;
        font-size: 1.3rem;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }

    .section-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
    }

    .section-title i {
        margin-right: 0.5rem;
        color: #667eea;
    }

    /* Loading Animation */
    .loading-spinner {
        width: 20px;
        height: 20px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Progress Bar */
    .progress-modern {
        height: 8px;
        border-radius: 4px;
        background: #e2e8f0;
        overflow: hidden;
        margin-top: 0.5rem;
    }

    .progress-bar-modern {
        height: 100%;
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 1rem;
        }
        
        .welcome-text {
            font-size: 1.8rem;
        }
        
        .stats-number {
            font-size: 1.5rem;
        }
    }

    /* Enhanced Chart specific styles */
    .chart-canvas {
        position: relative;
        height: 400px;
        width: 100%;
        border-radius: 15px;
        overflow: hidden;
    }
    
    .chart-loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 10;
        background: rgba(255, 255, 255, 0.9);
        padding: 1rem;
        border-radius: 10px;
    }

    /* Modern Chart Controls */
    .chart-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding: 0.75rem;
        background: rgba(99, 102, 241, 0.05);
        border-radius: 12px;
        border: 1px solid rgba(99, 102, 241, 0.1);
    }

    .chart-type-selector {
        display: flex;
        gap: 0.5rem;
    }

    .chart-type-btn {
        background: transparent;
        border: 1px solid rgba(99, 102, 241, 0.3);
        color: #6366f1;
        padding: 0.4rem 0.8rem;
        border-radius: 8px;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .chart-type-btn:hover {
        background: rgba(99, 102, 241, 0.1);
        transform: translateY(-2px);
    }

    .chart-type-btn.active {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .chart-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .chart-action-btn {
        background: transparent;
        border: 1px solid rgba(99, 102, 241, 0.3);
        color: #6366f1;
        padding: 0.4rem;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .chart-action-btn:hover {
        background: rgba(99, 102, 241, 0.1);
        transform: translateY(-2px);
    }

    /* Data Table Toggle */
    .data-table-container {
        margin-top: 1.5rem;
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        display: none;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th,
    .data-table td {
        padding: 0.75rem;
        text-align: left;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .data-table th {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        font-weight: 600;
    }

    .data-table tr:hover {
        background: rgba(99, 102, 241, 0.05);
    }

    /* Enhanced Tooltips */
    .custom-tooltip {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }

    /* Chart Insights Panel */
    .chart-insights {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.05), rgba(139, 92, 246, 0.05));
        border-radius: 15px;
        padding: 1.5rem;
        margin-top: 1rem;
        border: 1px solid rgba(99, 102, 241, 0.1);
    }

    .insight-item {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
    }

    .insight-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        color: white;
        font-size: 1.2rem;
    }

    .insight-content h6 {
        margin: 0 0 0.2rem 0;
        color: #2d3748;
        font-weight: 600;
    }

    .insight-content small {
        color: #718096;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .chart-controls {
            flex-direction: column;
            gap: 1rem;
        }
        
        .chart-type-selector {
            justify-content: center;
        }
    }

    #customLegend .legend-item:hover {
        background: #e2e8f0;
    }
</style>

<!-- Add Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<div class="dashboard-container">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="welcome-text mb-2">Welcome back, {{ Auth::user()->first_name }}! 👋</h1>
                <p class="subtitle mb-0">Here's what's happening with your travel management system today.</p>
            </div>
            <div class="col-lg-4 text-end">
                <div class="text-white">
                    <i class="ri-calendar-line"></i> {{ date('M d, Y') }}
                    <br>
                    <small>{{ date('l') }}</small>
                </div>
            </div>
        </div>
        
        <!-- Time Filter -->
        <div class="time-filter mt-3">
            <div class="text-center">
                <button class="btn {{ $period == 'today' ? 'active' : '' }}" onclick="changeTimeFilter('today')">Today</button>
                <button class="btn {{ $period == 'month' ? 'active' : '' }}" onclick="changeTimeFilter('month')">This Month</button>
            </div>
        </div>
    </div>

    <!-- Primary Statistics Cards - Only show if user can view business metrics -->
    @if($userPermissions['canViewBusinessMetrics'] || $userPermissions['canViewEnquiries'])
    <div class="row">
        @if($userPermissions['canViewEnquiries'])
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="stats-card" style="--card-color: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="stats-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="ri-questionnaire-line"></i>
                </div>
                <div class="stats-number" id="enquiry-count">
                    {{ $counts['enquiries']['total'] ?? 0 }}
                </div>
                <div class="stats-label">Total Enquiries</div>
                <div class="progress-modern">
                    @php
                        $enquiryTotal = $counts['enquiries']['total'] ?? 0;
                        $enquiryProgress = min(($enquiryTotal / 500) * 100, 100); // Cap at 500 enquiries
                    @endphp
                    <div class="progress-bar-modern" id="enquiry-progress" style="width: {{ $enquiryProgress }}%; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);"></div>
                </div>
                <small class="text-muted mt-1 d-block">Progress: Volume (Max: 500)</small>
            </div>
        </div>
        @endif

        @if($userPermissions['canViewBusinessMetrics'])
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="stats-card" style="--card-color: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="stats-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="ri-bookmark-3-line"></i>
                </div>
                <div class="stats-number" id="booking-count">
                    {{ $counts['bookings']['total'] ?? 0 }}
                </div>
                <div class="stats-label">Total Bookings</div>
                <div class="progress-modern">
                    @php
                        $bookingTotal = $counts['bookings']['total'] ?? 0;
                        $bookingProgress = min(($bookingTotal / 500) * 100, 100); // Cap at 500 bookings
                    @endphp
                    <div class="progress-bar-modern" id="booking-progress" style="width: {{ $bookingProgress }}%; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);"></div>
                </div>
                <small class="text-muted mt-1 d-block">Progress: Volume (Max: 500)</small>
            </div>
        </div>

        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="stats-card" style="--card-color: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="stats-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <i class="ri-route-line"></i>
                </div>
                <div class="stats-number" id="tour-count">
                    {{ $counts['tours']['total'] ?? 0 }}
                </div>
                <div class="stats-label">Active Tours</div>
                <div class="progress-modern">
                    @php
                        $tourTotal = $counts['tours']['total'] ?? 0;
                        $tourProgress = min(($tourTotal / 500) * 100, 100); // Cap at 500 tours
                    @endphp
                    <div class="progress-bar-modern" id="tour-progress" style="width: {{ $tourProgress }}%; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);"></div>
                </div>
                <small class="text-muted mt-1 d-block">Progress: Volume (Max: 500)</small>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Product Statistics Cards - Show based on user permissions -->
    <div class="row">
        @if($userPermissions['canViewHotels'])
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="stats-card" style="--card-color: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <div class="stats-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <i class="ri-hotel-line"></i>
                </div>
                <div class="stats-number" id="hotel-count">
                    {{ $counts['hotels']['total'] ?? 0 }}
                </div>
                <div class="stats-label">Hotels</div>
                <div class="stats-detail">
                    Active: {{ $counts['hotels']['active'] ?? 0 }} | 
                    Recent: {{ $counts['hotels']['recent'] ?? 0 }}
                </div>
            </div>
        </div>
        @endif

        @if($userPermissions['canViewAttractions'])
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="stats-card" style="--card-color: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="stats-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="ri-landscape-line"></i>
                </div>
                <div class="stats-number">{{ $counts['attractions']['total'] ?? 0 }}</div>
                <div class="stats-label">Attractions</div>
                <div class="stats-detail">
                    Active: {{ $counts['attractions']['active'] ?? 0 }} | 
                    Recent: {{ $counts['attractions']['recent'] ?? 0 }}
                </div>
            </div>
        </div>
        @endif

        @if($userPermissions['canViewRestaurants'])
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="stats-card" style="--card-color: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="stats-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="ri-restaurant-2-line"></i>
                </div>
                <div class="stats-number">{{ $counts['restaurants']['total'] ?? 0 }}</div>
                <div class="stats-label">Restaurants</div>
                <div class="stats-detail">
                    Active: {{ $counts['restaurants']['active'] ?? 0 }} | 
                    Recent: {{ $counts['restaurants']['recent'] ?? 0 }}
                </div>
            </div>
        </div>
        @endif

        @if($userPermissions['canViewGuides'])
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="stats-card" style="--card-color: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="stats-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="ri-compass-3-line"></i>
                </div>
                <div class="stats-number">{{ $counts['guides']['total'] ?? 0 }}</div>
                <div class="stats-label">Guides</div>
                <div class="stats-detail">
                    Active: {{ $counts['guides']['available'] ?? 0 }} | 
                    Recent: {{ $counts['guides']['recent'] ?? 0 }}
                </div>
            </div>
        </div>
        @endif

        @if($userPermissions['canViewDrivers'])
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="stats-card" style="--card-color: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="stats-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <i class="ri-steering-2-line"></i>
                </div>
                <div class="stats-number">{{ $counts['drivers']['total'] ?? 0 }}</div>
                <div class="stats-label">Drivers</div>
                <div class="stats-detail">
                    Active: {{ $counts['drivers']['available'] ?? 0 }} | 
                    Recent: {{ $counts['drivers']['recent'] ?? 0 }}
                </div>
            </div>
        </div>
        @endif

        @if($userPermissions['canViewVehicles'])
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="stats-card" style="--card-color: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <div class="stats-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <i class="ri-car-line"></i>
                </div>
                <div class="stats-number">{{ $counts['vehicles']['total'] ?? 0 }}</div>
                <div class="stats-label">Vehicles</div>
                <div class="stats-detail">
                    Active: {{ $counts['vehicles']['available'] ?? 0 }} | 
                    Recent: {{ $counts['vehicles']['recent'] ?? 0 }}
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Agents Row (Separate) - Show only for specific roles -->
    @if($userPermissions['canViewAgents'])
    <div class="row">
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="stats-card" style="--card-color: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="stats-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="ri-user-line"></i>
                </div>
                <div class="stats-number">{{ $counts['agents']['total'] ?? 0 }}</div>
                <div class="stats-label">Agents</div>
                <div class="stats-detail">
                    Active: {{ $counts['agents']['active'] ?? 0 }} | 
                    Recent: {{ $counts['agents']['recent'] ?? 0 }}
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Enhanced Charts and Activity Section - Show for business metrics or product analytics -->
    @if($userPermissions['canViewBusinessMetrics'] || $userPermissions['canViewProductAnalytics'])
    <div class="row">
        <!-- Enhanced Chart Section -->
        <div class="col-lg-8">
            <div class="chart-card">
                <div class="chart-header">
                    <span><i class="ri-bar-chart-line"></i> 
                        @if($userPermissions['canViewProductAnalytics'] && !$userPermissions['canViewBusinessMetrics'])
                            Product Analytics - {{ ucfirst($period) }}
                        @else
                            Business Analytics - {{ ucfirst($period) }}
                        @endif
                    </span>
                    <small class="text-muted">Live data updates • Interactive Dashboard</small>
                </div>
                
                <!-- Enhanced Chart Controls -->
                <div class="chart-controls">
                    <div class="chart-type-selector">
                        <button class="chart-type-btn active" data-type="bar">
                            <i class="ri-bar-chart-line"></i> Bar
                        </button>
                        <button class="chart-type-btn" data-type="line">
                            <i class="ri-line-chart-line"></i> Line
                        </button>
                        <button class="chart-type-btn" data-type="doughnut">
                            <i class="ri-donut-chart-line"></i> Donut
                        </button>
                        <button class="chart-type-btn" data-type="radar">
                            <i class="ri-radar-line"></i> Radar
                        </button>
                    </div>
                    
                    <div class="chart-actions">
                        <button class="chart-action-btn" id="toggleDataTable" title="Toggle Data Table">
                            <i class="ri-table-line"></i>
                        </button>
                        <button class="chart-action-btn" id="exportChart" title="Export Chart">
                            <i class="ri-download-line"></i>
                        </button>
                        <button class="chart-action-btn" id="refreshChart" title="Refresh Data">
                            <i class="ri-refresh-line"></i>
                        </button>
                    </div>
                </div>
                
                <div class="chart-canvas">
                    <canvas id="businessAnalyticsChart"></canvas>
                    <div class="chart-loading" id="chartLoading" style="display: none;">
                        <div class="loading-spinner"></div>
                        <small style="margin-top: 0.5rem; display: block;">Loading analytics...</small>
                    </div>
                </div>
                
                <!-- Chart Insights -->
                <div class="chart-insights">
                    <h6 style="margin-bottom: 1rem; color: #6366f1;">
                        <i class="ri-lightbulb-line"></i> Key Insights
                    </h6>
                    <div id="chartInsights">
                        <!-- Dynamic insights will be populated here -->
                    </div>
                </div>
                
                <!-- Data Table Container -->
                <div class="data-table-container" id="dataTableContainer">
                    <h6 style="margin-bottom: 1rem; color: #6366f1;">
                        <i class="ri-table-line"></i> Detailed Data View
                    </h6>
                    <div style="overflow-x: auto;">
                        <table class="data-table" id="analyticsDataTable">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Total Count</th>
                                    <th>This Month</th>
                                    <th>Growth</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="dataTableBody">
                                <!-- Dynamic data will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Recent Activity -->
        <div class="col-lg-4">
            <div class="chart-card">
                <div class="chart-header">
                    <span><i class="ri-time-line"></i> System Overview</span>
                    <small class="text-muted">Real-time statistics</small>
                </div>
                
                @if(isset($counts['orders']))
                <div class="activity-item">
                    <div class="activity-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="ri-database-line"></i>
                    </div>
                    <div class="activity-content">
                        <h6>Total Orders</h6>
                        <small>{{ $counts['orders']['total'] ?? 0 }} ({{ $counts['orders']['recent'] ?? 0 }} recent)</small>
                    </div>
                </div>
                @endif

                @if(isset($counts['facilities']))
                <div class="activity-item">
                    <div class="activity-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                        <i class="ri-function-line"></i>
                    </div>
                    <div class="activity-content">
                        <h6>Facilities</h6>
                        <small>{{ $counts['facilities']['total'] ?? 0 }} active facilities</small>
                    </div>
                </div>
                @endif

                @if($userPermissions['canViewZones'] && isset($counts['zones']))
                <div class="activity-item">
                    <div class="activity-icon" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                        <i class="ri-map-pin-user-line"></i>
                    </div>
                    <div class="activity-content">
                        <h6>Zones</h6>
                        <small>{{ $counts['zones']['total'] ?? 0 }} operational zones</small>
                    </div>
                </div>
                @endif

                @if($userPermissions['canViewPorts'] && isset($counts['ports']))
                <div class="activity-item">
                    <div class="activity-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
                        <i class="ri-ship-line"></i>
                    </div>
                    <div class="activity-content">
                        <h6>Ports</h6>
                        <small>{{ $counts['ports']['total'] ?? 0 }} ports available</small>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <h3 class="section-title">
                <i class="ri-flashlight-line"></i>
                Quick Actions
            </h3>
        </div>

        @if($userPermissions['canViewEnquiries'])
        <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('enquirylist.index') }}" class="quick-action-btn">
                <div class="quick-action-icon">
                    <i class="ri-questionnaire-line"></i>
                </div>
                <span>Manage Negotiation</span>
            </a>
        </div>
        @endif

        @if($userPermissions['canViewBusinessMetrics'])
        <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('bookinglist.index') }}" class="quick-action-btn">
                <div class="quick-action-icon">
                    <i class="ri-bookmark-3-line"></i>
                </div>
                <span>View Bookings</span>
            </a>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('tours') }}" class="quick-action-btn">
                <div class="quick-action-icon">
                    <i class="ri-route-line"></i>
                </div>
                <span>Manage Tours</span>
            </a>
        </div>
        @endif

        @php
            $excludedRoles = [38, 81, 84, 87, 90, 93, 96, 99, 102, 105, 108, 111, 114, 117, 120, 123];
        @endphp

        @if(!in_array(Auth::user()->role_id, $excludedRoles))
        <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('users.index') }}" class="quick-action-btn">
                <div class="quick-action-icon">
                    <i class="ri-hotel-line"></i>
                </div>
                <span>Manage Users</span>
            </a>
        </div>
        @endif

        <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('mail.index') }}" class="quick-action-btn">
                <div class="quick-action-icon">
                    <i class="ri-mail-send-line"></i>
                </div>
                <span>Email Templates</span>
            </a>
        </div>

        @if(Auth::user()->role_id == 1)
        <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('master-setting') }}" class="quick-action-btn">
                <div class="quick-action-icon">
                    <i class="ri-settings-3-line"></i>
                </div>
                <span>Settings</span>
            </a>
        </div>
        @endif
    </div>

    <!-- Additional Stats Row -->
    {{-- <div class="row mt-4">
        <div class="col-12">
            <h3 class="section-title">
                <i class="ri-dashboard-line"></i>
                System Statistics - {{ ucfirst($period) }}
            </h3>
        </div>

        @if(Auth::user()->role_id != 38)
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stats-card" style="--card-color: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="stats-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="ri-user-line"></i>
                </div>
                <div class="stats-number">{{ $counts['users']['total'] ?? 0 }}</div>
                <div class="stats-label">System Users</div>
                <div class="stats-detail">
                    Active: {{ $counts['users']['active'] ?? 0 }} | 
                    Recent: {{ $counts['users']['recent'] ?? 0 }}
                </div>
            </div>
        </div>
        @endif

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stats-card" style="--card-color: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="stats-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="ri-grid-line"></i>
                </div>
                <div class="stats-number">{{ $counts['categories']['total'] ?? 0 }}</div>
                <div class="stats-label">Facility Categories</div>
                <div class="stats-detail">
                    Active: {{ $counts['categories']['active'] ?? 0 }} | 
                    Recent: {{ $counts['categories']['recent'] ?? 0 }}
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stats-card" style="--card-color: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="stats-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <i class="ri-function-line"></i>
                </div>
                <div class="stats-number">{{ $counts['facilities']['total'] ?? 0 }}</div>
                <div class="stats-label">Facilities</div>
                <div class="stats-detail">
                    Active: {{ $counts['facilities']['active'] ?? 0 }} | 
                    Recent: {{ $counts['facilities']['recent'] ?? 0 }}
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stats-card" style="--card-color: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="stats-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="ri-map-pin-user-line"></i>
                </div>
                <div class="stats-number">{{ $counts['zones']['total'] ?? 0 }}</div>
                <div class="stats-label">Operational Zones</div>
                <div class="stats-detail">
                    Active: {{ $counts['zones']['active'] ?? 0 }} | 
                    Recent: {{ $counts['zones']['recent'] ?? 0 }}
                </div>
            </div>
        </div>
    </div> --}}
</div>

<div id="customLegend" class="d-flex flex-wrap justify-content-center mt-4"></div>
<div id="serviceDetails" class="text-center mt-3" style="font-weight: 500; color: #6366f1;"></div>

<script>
// Enhanced Chart.js configuration and initialization
let businessChart = null;
let currentChartType = 'bar';
let currentData = null;
let userPermissions = @json($userPermissions);

// Chart color schemes
const colorSchemes = {
    gradient: [
        'rgba(102, 126, 234, 0.8)',  // Blue
        'rgba(118, 75, 162, 0.8)',   // Purple
        'rgba(67, 233, 123, 0.8)',   // Green
        'rgba(247, 112, 154, 0.8)',  // Pink
        'rgba(255, 159, 64, 0.8)',   // Orange
        'rgba(54, 162, 235, 0.8)',   // Light Blue
        'rgba(153, 102, 255, 0.8)',  // Light Purple
        'rgba(75, 192, 192, 0.8)',   // Teal
        'rgba(255, 99, 132, 0.8)',   // Red
        'rgba(255, 205, 86, 0.8)',   // Yellow
        'rgba(201, 203, 207, 0.8)',  // Gray
        'rgba(54, 162, 235, 0.8)'    // Blue
    ],
    border: [
        'rgba(102, 126, 234, 1)',
        'rgba(118, 75, 162, 1)',
        'rgba(67, 233, 123, 1)',
        'rgba(247, 112, 154, 1)',
        'rgba(255, 159, 64, 1)',
        'rgba(54, 162, 235, 1)',
        'rgba(153, 102, 255, 1)',
        'rgba(75, 192, 192, 1)',
        'rgba(255, 99, 132, 1)',
        'rgba(255, 205, 86, 1)',
        'rgba(201, 203, 207, 1)',
        'rgba(54, 162, 235, 1)'
    ]
};

// Initialize enhanced chart on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeEnhancedChart();
    setupChartControls();
    updateDataTable();
    generateInsights();
});

function initializeEnhancedChart() {
    const ctx = document.getElementById('businessAnalyticsChart').getContext('2d');
    
    // Build chart data based on user permissions
    let chartLabels = [];
    let totalData = [];
    let monthData = [];
    
    // Add enquiries if user can view them (separate permission)
    if (userPermissions.canViewEnquiries && {!! json_encode(isset($counts['enquiries'])) !!}) {
        chartLabels.push('Enquiries');
        totalData.push({{ $counts['enquiries']['total'] ?? 0 }});
        monthData.push({{ $counts['enquiries']['new'] ?? 0 }});
    }
    
    // Add other business metrics if user can view them
    if (userPermissions.canViewBusinessMetrics) {
        if ({!! json_encode(isset($counts['bookings'])) !!}) {
            chartLabels.push('Bookings');
            totalData.push({{ $counts['bookings']['total'] ?? 0 }});
            monthData.push({{ $counts['bookings']['confirmed'] ?? 0 }});
        }
        
        if ({!! json_encode(isset($counts['tours'])) !!}) {
            chartLabels.push('Tours');
            totalData.push({{ $counts['tours']['total'] ?? 0 }});
            monthData.push({{ $counts['tours']['active'] ?? 0 }});
        }
    }
    
    // Add product data based on permissions
    if (userPermissions.canViewHotels && {!! json_encode(isset($counts['hotels'])) !!}) {
        chartLabels.push('Hotels');
        totalData.push({{ $counts['hotels']['total'] ?? 0 }});
        monthData.push({{ $counts['hotels']['active'] ?? 0 }});
    }
    
    if (userPermissions.canViewRestaurants && {!! json_encode(isset($counts['restaurants'])) !!}) {
        chartLabels.push('Restaurants');
        totalData.push({{ $counts['restaurants']['total'] ?? 0 }});
        monthData.push({{ $counts['restaurants']['active'] ?? 0 }});
    }
    
    if (userPermissions.canViewGuides && {!! json_encode(isset($counts['guides'])) !!}) {
        chartLabels.push('Guides');
        totalData.push({{ $counts['guides']['total'] ?? 0 }});
        monthData.push({{ $counts['guides']['available'] ?? 0 }});
    }
    
    if (userPermissions.canViewDrivers && {!! json_encode(isset($counts['drivers'])) !!}) {
        chartLabels.push('Drivers');
        totalData.push({{ $counts['drivers']['total'] ?? 0 }});
        monthData.push({{ $counts['drivers']['available'] ?? 0 }});
    }
    
    if (userPermissions.canViewVehicles && {!! json_encode(isset($counts['vehicles'])) !!}) {
        chartLabels.push('Vehicles');
        totalData.push({{ $counts['vehicles']['total'] ?? 0 }});
        monthData.push({{ $counts['vehicles']['available'] ?? 0 }});
    }
    
    if (userPermissions.canViewAttractions && {!! json_encode(isset($counts['attractions'])) !!}) {
        chartLabels.push('Attractions');
        totalData.push({{ $counts['attractions']['total'] ?? 0 }});
        monthData.push({{ $counts['attractions']['active'] ?? 0 }});
    }
    
    if (userPermissions.canViewAgents && {!! json_encode(isset($counts['agents'])) !!}) {
        chartLabels.push('Agents');
        totalData.push({{ $counts['agents']['total'] ?? 0 }});
        monthData.push({{ $counts['agents']['active'] ?? 0 }});
    }
    
    // Add zones only for DMC and upper levels
    if (userPermissions.canViewZones && {!! json_encode(isset($counts['zones'])) !!}) {
        chartLabels.push('Zones');
        totalData.push({{ $counts['zones']['total'] ?? 0 }});
        monthData.push({{ $counts['zones']['active'] ?? 0 }});
    }
    
    // Add ports only for admin users
    if (userPermissions.canViewPorts && {!! json_encode(isset($counts['ports'])) !!}) {
        chartLabels.push('Ports');
        totalData.push({{ $counts['ports']['total'] ?? 0 }});
        monthData.push({{ $counts['ports']['active'] ?? 0 }});
    }
    
    currentData = {
        labels: chartLabels,
        datasets: [{
            label: 'Total Count',
            data: totalData,
            backgroundColor: colorSchemes.gradient,
            borderColor: colorSchemes.border,
            borderWidth: 2,
            borderRadius: 8,
            borderSkipped: false,
            hoverBackgroundColor: colorSchemes.border,
            hoverBorderWidth: 3,
            tension: 0.4
        }, {
            label: 'This Month',
            data: monthData,
            backgroundColor: colorSchemes.gradient.map(color => color.replace('0.8', '0.4')),
            borderColor: colorSchemes.border.map(color => color.replace('1', '0.8')),
            borderWidth: 1,
            borderRadius: 6,
            borderSkipped: false,
            tension: 0.4
        }]
    };

    businessChart = new Chart(ctx, {
        type: currentChartType,
        data: currentData,
        options: getEnhancedChartOptions()
    });

    if (currentChartType === 'doughnut') {
        renderCustomLegend();
        document.getElementById('serviceDetails').innerHTML = '';
    } else {
        document.getElementById('customLegend').innerHTML = '';
        document.getElementById('serviceDetails').innerHTML = '';
    }
}

function getEnhancedChartOptions() {
    const baseOptions = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        plugins: {
            title: {
                display: true,
                text: 'Business Analytics Overview',
                font: {
                    size: 18,
                    weight: 'bold',
                    family: 'Inter, sans-serif'
                },
                color: '#2d3748',
                padding: {
                    bottom: 20
                }
            },
            legend: {
                display: true,
                position: 'top',
                labels: {
                    usePointStyle: true,
                    padding: 25,
                    font: {
                        size: 13,
                        family: 'Inter, sans-serif'
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.9)',
                titleColor: '#fff',
                bodyColor: '#fff',
                borderColor: '#667eea',
                borderWidth: 2,
                cornerRadius: 12,
                displayColors: true,
                titleFont: {
                    size: 14,
                    weight: 'bold'
                },
                bodyFont: {
                    size: 13
                },
                padding: 12,
                callbacks: {
                    title: function(context) {
                        return context[0].label + ' Analytics';
                    },
                    label: function(context) {
                        return context.dataset.label + ': ' + context.parsed.y.toLocaleString();
                    },
                    afterLabel: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((context.parsed.y / total) * 100).toFixed(1);
                        return `${percentage}% of total ${context.dataset.label.toLowerCase()}`;
                    }
                }
            }
        }
    };

    // Chart type specific options
    if (currentChartType === 'bar' || currentChartType === 'line') {
        baseOptions.scales = {
            y: {
                beginAtZero: true,
                min: 0,
                max: 200, // Changed to 200
                ticks: {
                    stepSize: 20, // Changed to show steps of 20
                    color: '#718096',
                    font: {
                        size: 12,
                        family: 'Inter, sans-serif'
                    },
                    callback: function(value) {
                        return value.toLocaleString();
                    }
                },
                grid: {
                    color: 'rgba(99, 102, 241, 0.1)',
                    drawBorder: false,
                    lineWidth: 1
                },
                title: {
                    display: true,
                    text: 'Count (0-200)',
                    color: '#6366f1',
                    font: {
                        size: 14,
                        weight: 'bold'
                    }
                }
            },
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    color: '#718096',
                    font: {
                        size: 11,
                        family: 'Inter, sans-serif'
                    },
                    maxRotation: 45,
                    minRotation: 0
                }
            }
        };
    } else if (currentChartType === 'doughnut') {
        baseOptions.cutout = '60%';
        baseOptions.plugins.legend = { display: false }; // Hide default legend
        baseOptions.plugins.tooltip = {
            ...baseOptions.plugins.tooltip,
            callbacks: {
                label: function(context) {
                    const label = context.label || '';
                    const value = context.parsed || 0;
                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                    const percentage = ((value / total) * 100).toFixed(1);
                    return `${label}: ${value} (${percentage}%)`;
                }
            }
        };
    }

    return baseOptions;
}

// Enhanced chart controls setup
function setupChartControls() {
    // Chart type selector
    document.querySelectorAll('.chart-type-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.chart-type-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            currentChartType = this.dataset.type;
            updateChartType();
        });
    });

    // Action buttons
    document.getElementById('toggleDataTable').addEventListener('click', toggleDataTable);
    document.getElementById('exportChart').addEventListener('click', exportChart);
    document.getElementById('refreshChart').addEventListener('click', refreshChartData);
}

function updateChartType() {
    if (businessChart) {
        businessChart.destroy();
    }
    
    // Show loading
    document.getElementById('chartLoading').style.display = 'block';
    
    setTimeout(() => {
        const ctx = document.getElementById('businessAnalyticsChart').getContext('2d');
        businessChart = new Chart(ctx, {
            type: currentChartType,
            data: currentData,
            options: getEnhancedChartOptions()
        });
        
        document.getElementById('chartLoading').style.display = 'none';
        generateInsights();
    }, 500);
}

function updateDataTable() {
    const tableBody = document.getElementById('dataTableBody');
    const services = currentData.labels;
    const totalData = currentData.datasets[0].data;
    const monthData = currentData.datasets[1].data;
    
    tableBody.innerHTML = '';
    
    services.forEach((service, index) => {
        const total = totalData[index];
        const month = monthData[index];
        const growth = total > 0 ? ((month / total) * 100).toFixed(1) : 0;
        const status = month > (total * 0.5) ? 'Excellent' : month > (total * 0.3) ? 'Good' : 'Needs Attention';
        const statusClass = month > (total * 0.5) ? 'success' : month > (total * 0.3) ? 'warning' : 'danger';
        
        const row = `
            <tr>
                <td><strong>${service}</strong></td>
                <td>${total.toLocaleString()}</td>
                <td>${month.toLocaleString()}</td>
                <td>${growth}%</td>
                <td><span class="badge bg-${statusClass}">${status}</span></td>
            </tr>
        `;
        tableBody.innerHTML += row;
    });
}

function generateInsights() {
    const insightsContainer = document.getElementById('chartInsights');
    const totalData = currentData.datasets[0].data;
    const monthData = currentData.datasets[1].data;
    
    // Calculate insights
    const maxTotal = Math.max(...totalData);
    const maxTotalIndex = totalData.indexOf(maxTotal);
    const maxMonth = Math.max(...monthData);
    const maxMonthIndex = monthData.indexOf(maxMonth);
    const totalSum = totalData.reduce((a, b) => a + b, 0);
    const monthSum = monthData.reduce((a, b) => a + b, 0);
    const efficiency = totalSum > 0 ? ((monthSum / totalSum) * 100).toFixed(1) : 0;
    
    insightsContainer.innerHTML = `
        <div class="insight-item">
            <div class="insight-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                <i class="ri-trophy-line"></i>
            </div>
            <div class="insight-content">
                <h6>Top Performing Service</h6>
                <small>${currentData.labels[maxTotalIndex]} leads with ${maxTotal} total records</small>
            </div>
        </div>
        
        <div class="insight-item">
            <div class="insight-icon" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                <i class="ri-trending-up-line"></i>
            </div>
            <div class="insight-content">
                <h6>Most Active This Month</h6>
                <small>${currentData.labels[maxMonthIndex]} with ${maxMonth} new entries</small>
            </div>
        </div>
        
        <div class="insight-item">
            <div class="insight-icon" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                <i class="ri-pie-chart-line"></i>
            </div>
            <div class="insight-content">
                <h6>Overall Efficiency</h6>
                <small>${efficiency}% of total records created this month</small>
            </div>
        </div>
    `;
}

function toggleDataTable() {
    const container = document.getElementById('dataTableContainer');
    const btn = document.getElementById('toggleDataTable');
    
    if (container.style.display === 'none' || !container.style.display) {
        container.style.display = 'block';
        btn.innerHTML = '<i class="ri-eye-off-line"></i>';
        updateDataTable();
    } else {
        container.style.display = 'none';
        btn.innerHTML = '<i class="ri-table-line"></i>';
    }
}

function exportChart() {
    const link = document.createElement('a');
    link.download = `business-analytics-${new Date().toISOString().slice(0, 10)}.png`;
    link.href = businessChart.toBase64Image('image/png', 1.0);
    link.click();
}

function refreshChartData() {
    const activeButton = document.querySelector('.time-filter .btn.active');
    if (activeButton) {
        const period = activeButton.textContent.toLowerCase().trim() === 'today' ? 'today' : 'month';
        changeTimeFilter(period);
    }
}

// Modified update chart function
function updateChart(counts, permissions) {
    if (businessChart) {
        document.getElementById('chartLoading').style.display = 'block';
        
        // Update user permissions
        userPermissions = permissions;
        
        // Build data arrays based on user permissions
        let totalData = [];
        let monthData = [];
        let chartLabels = [];
        
        // Add enquiries if user can view them (separate permission)
        if (userPermissions.canViewEnquiries && counts.enquiries) {
            chartLabels.push('Enquiries');
            totalData.push(counts.enquiries.total);
            monthData.push(counts.enquiries.new || 0);
        }
        
        // Add other business metrics if user can view them
        if (userPermissions.canViewBusinessMetrics) {
            if (counts.bookings) {
                chartLabels.push('Bookings');
                totalData.push(counts.bookings.total);
                monthData.push(counts.bookings.confirmed || 0);
            }
            
            if (counts.tours) {
                chartLabels.push('Tours');
                totalData.push(counts.tours.total);
                monthData.push(counts.tours.active);
            }
        }
        
        // Add product data based on permissions
        if (userPermissions.canViewHotels && counts.hotels) {
            chartLabels.push('Hotels');
            totalData.push(counts.hotels.total);
            monthData.push(counts.hotels.active);
        }
        
        if (userPermissions.canViewRestaurants && counts.restaurants) {
            chartLabels.push('Restaurants');
            totalData.push(counts.restaurants.total);
            monthData.push(counts.restaurants.active);
        }
        
        if (userPermissions.canViewGuides && counts.guides) {
            chartLabels.push('Guides');
            totalData.push(counts.guides.total);
            monthData.push(counts.guides.available);
        }
        
        if (userPermissions.canViewDrivers && counts.drivers) {
            chartLabels.push('Drivers');
            totalData.push(counts.drivers.total);
            monthData.push(counts.drivers.available);
        }
        
        if (userPermissions.canViewVehicles && counts.vehicles) {
            chartLabels.push('Vehicles');
            totalData.push(counts.vehicles.total);
            monthData.push(counts.vehicles.available);
        }
        
        if (userPermissions.canViewAttractions && counts.attractions) {
            chartLabels.push('Attractions');
            totalData.push(counts.attractions.total);
            monthData.push(counts.attractions.active);
        }
        
        if (userPermissions.canViewAgents && counts.agents) {
            chartLabels.push('Agents');
            totalData.push(counts.agents.total);
            monthData.push(counts.agents.active);
        }
        
        // Add zones only for DMC and upper levels
        if (userPermissions.canViewZones && counts.zones) {
            chartLabels.push('Zones');
            totalData.push(counts.zones.total);
            monthData.push(counts.zones.active);
        }
        
        // Add ports only for Admin and Super Admin users
        if (userPermissions.canViewPorts && counts.ports) {
            chartLabels.push('Ports');
            totalData.push(counts.ports.total);
            monthData.push(counts.ports.active);
        }
        
        // Update current data
        currentData.labels = chartLabels;
        currentData.datasets[0].data = totalData;
        currentData.datasets[1].data = monthData;
        
        businessChart.update('active');
        
        setTimeout(() => {
            document.getElementById('chartLoading').style.display = 'none';
            updateDataTable();
            generateInsights();
        }, 1000);
    }
}

// Modified time filter functionality to update chart
function changeTimeFilter(period) {
    // Show loading state
    showLoadingState();
    
    // Update active button
    document.querySelectorAll('.time-filter .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Find the button that was clicked and mark it active
    const targetButton = [...document.querySelectorAll('.time-filter .btn')].find(btn => 
        btn.textContent.toLowerCase().includes(period) || 
        (period === 'today' && btn.textContent.toLowerCase().includes('today')) ||
        (period === 'month' && btn.textContent.toLowerCase().includes('month'))
    );
    if (targetButton) {
        targetButton.classList.add('active');
    }
    
    // Fetch new data
    fetch(`{{ route('dashboard.counts') }}?period=${period}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCounts(data.counts);
            updateChart(data.counts, data.userPermissions);
            updateChartTitle(data.period);
        } else {
            console.error('Server error:', data);
        }
    })
    .catch(error => {
        console.error('Error fetching counts:', error);
    })
    .finally(() => {
        hideLoadingState();
    });
}

function showLoadingState() {
    // Add loading spinners to all count elements
    document.querySelectorAll('.stats-number').forEach(element => {
        if (!element.querySelector('.loading-spinner')) {
            const currentValue = element.textContent;
            element.innerHTML = '<div class="loading-spinner"></div>';
            element.dataset.originalValue = currentValue;
        }
    });
}

function hideLoadingState() {
    // Remove loading spinners
    document.querySelectorAll('.loading-spinner').forEach(spinner => {
        spinner.remove();
    });
}

function updateCounts(counts) {
    // Update primary stats - check if they exist first
    if (counts.enquiries) {
        updateElementCount('enquiry-count', counts.enquiries.total || 0);
    }
    if (counts.bookings) {
        updateElementCount('booking-count', counts.bookings.total || 0);
    }
    if (counts.tours) {
        updateElementCount('tour-count', counts.tours.total || 0);
    }
    if (counts.hotels) {
        updateElementCount('hotel-count', counts.hotels.total || 0);
    }
    
    // Update all other counts dynamically
    updateAllStatsCards(counts);
}

function updateElementCount(elementId, count) {
    const element = document.getElementById(elementId);
    if (element) {
        animateCount(element, parseInt(element.textContent) || 0, count);
    }
}

function updateAllStatsCards(counts) {
    const statsMapping = {
        // Secondary stats - update by finding stats-number elements and their labels
        'Attractions': counts.attractions ? counts.attractions.total : 0,
        'Restaurants': counts.restaurants ? counts.restaurants.total : 0,
        'Guides': counts.guides ? counts.guides.total : 0,
        'Drivers': counts.drivers ? counts.drivers.total : 0,
        'Vehicles': counts.vehicles ? counts.vehicles.total : 0,
        'Agents': counts.agents ? counts.agents.total : 0,
        'System Users': counts.users ? counts.users.total : 0,
        'Facility Categories': counts.categories ? counts.categories.total : 0,
        'Facilities': counts.facilities ? counts.facilities.total : 0,
        'Operational Zones': counts.zones ? counts.zones.total : 0
    };
    
    // Update each stat card
    Object.entries(statsMapping).forEach(([label, value]) => {
        const labelElement = [...document.querySelectorAll('.stats-label')].find(el => 
            el.textContent.trim() === label
        );
        
        if (labelElement) {
            const statsCard = labelElement.closest('.stats-card');
            const numberElement = statsCard.querySelector('.stats-number');
            if (numberElement) {
                animateCount(numberElement, parseInt(numberElement.textContent) || 0, value);
            }
        }
    });
    
    // Update progress bars for first three cards
    updateProgressBars(counts);
    
    // Update detail information
    updateStatsDetails(counts);
}

function updateProgressBars(counts) {
    // Update Enquiry Progress Bar
    if (counts.enquiries) {
        const enquiryTotal = counts.enquiries.total || 0;
        const enquiryProgress = Math.min((enquiryTotal / 500) * 100, 100); // Cap at 500 enquiries
        
        const enquiryProgressBar = document.getElementById('enquiry-progress');
        if (enquiryProgressBar) {
            animateProgressBar(enquiryProgressBar, enquiryProgress);
        }
    }
    
    // Update Booking Progress Bar
    if (counts.bookings) {
        const bookingTotal = counts.bookings.total || 0;
        const bookingProgress = Math.min((bookingTotal / 500) * 100, 100); // Cap at 500 bookings
        
        const bookingProgressBar = document.getElementById('booking-progress');
        if (bookingProgressBar) {
            animateProgressBar(bookingProgressBar, bookingProgress);
        }
    }
    
    // Update Tour Progress Bar
    if (counts.tours) {
        const tourTotal = counts.tours.total || 0;
        const tourProgress = Math.min((tourTotal / 500) * 100, 100); // Cap at 500 tours
        
        const tourProgressBar = document.getElementById('tour-progress');
        if (tourProgressBar) {
            animateProgressBar(tourProgressBar, tourProgress);
        }
    }
}

function animateProgressBar(element, targetPercent) {
    const currentWidth = parseFloat(element.style.width) || 0;
    const duration = 1000; // 1 second
    const startTime = performance.now();
    
    function updateProgress(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function for smooth animation
        const easeOutQuart = 1 - Math.pow(1 - progress, 4);
        const currentPercent = currentWidth + (targetPercent - currentWidth) * easeOutQuart;
        
        element.style.width = currentPercent + '%';
        
        if (progress < 1) {
            requestAnimationFrame(updateProgress);
        }
    }
    
    requestAnimationFrame(updateProgress);
}

function updateStatsDetails(counts) {
    // Removed the update details for the three main cards since they no longer have details
    
    // Update Hotels to Agents section details (no progress bars) - check if counts exist first
    if (counts.hotels) {
        updateDetailText('Hotels', `Active: ${counts.hotels.active || 0} | Recent: ${counts.hotels.recent || 0}`);
    }
    if (counts.attractions) {
        updateDetailText('Attractions', `Active: ${counts.attractions.active || 0} | Recent: ${counts.attractions.recent || 0}`);
    }
    if (counts.restaurants) {
        updateDetailText('Restaurants', `Active: ${counts.restaurants.active || 0} | Recent: ${counts.restaurants.recent || 0}`);
    }
    if (counts.guides) {
        updateDetailText('Guides', `Active: ${counts.guides.available || 0} | Recent: ${counts.guides.recent || 0}`);
    }
    if (counts.drivers) {
        updateDetailText('Drivers', `Active: ${counts.drivers.available || 0} | Recent: ${counts.drivers.recent || 0}`);
    }
    if (counts.vehicles) {
        updateDetailText('Vehicles', `Active: ${counts.vehicles.available || 0} | Recent: ${counts.vehicles.recent || 0}`);
    }
    if (counts.agents) {
        updateDetailText('Agents', `Active: ${counts.agents.active || 0} | Recent: ${counts.agents.recent || 0}`);
    }
    
    // Update system statistics details
    if (counts.users) {
        updateDetailText('System Users', `Active: ${counts.users.active || 0} | Recent: ${counts.users.recent || 0}`);
    }
    if (counts.categories) {
        updateDetailText('Facility Categories', `Active: ${counts.categories.active || 0} | Recent: ${counts.categories.recent || 0}`);
    }
    if (counts.facilities) {
        updateDetailText('Facilities', `Active: ${counts.facilities.active || 0} | Recent: ${counts.facilities.recent || 0}`);
    }
    if (counts.zones) {
        updateDetailText('Operational Zones', `Active: ${counts.zones.active || 0} | Recent: ${counts.zones.recent || 0}`);
    }
}

function updateDetailText(label, detailText) {
    const labelElement = [...document.querySelectorAll('.stats-label')].find(el => 
        el.textContent.trim() === label
    );
    
    if (labelElement) {
        const statsCard = labelElement.closest('.stats-card');
        const detailElement = statsCard.querySelector('.stats-detail');
        if (detailElement) {
            detailElement.textContent = detailText;
        }
    }
}

function animateCount(element, start, end) {
    const duration = 1000; // 1 second
    const startTime = performance.now();
    
    function updateCount(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function for smooth animation
        const easeOutQuart = 1 - Math.pow(1 - progress, 4);
        const current = Math.round(start + (end - start) * easeOutQuart);
        
        element.textContent = current.toLocaleString();
        
        if (progress < 1) {
            requestAnimationFrame(updateCount);
        }
    }
    
    requestAnimationFrame(updateCount);
}

function updateChartTitle(period) {
    const chartHeader = document.querySelector('.chart-header span');
    if (chartHeader) {
        let displayPeriod = period;
        if (period === 'today') displayPeriod = 'Today';
        if (period === 'month') displayPeriod = 'This Month';
        
        chartHeader.innerHTML = `<i class="ri-bar-chart-line"></i> Business Analytics - ${displayPeriod}`;
    }
    
    const chartSubtitle = document.querySelector('.chart-header + div small');
    if (chartSubtitle) {
        let displayPeriod = period;
        if (period === 'today') displayPeriod = 'today\'s';
        if (period === 'month') displayPeriod = 'this month\'s';
        
        chartSubtitle.textContent = `Showing ${displayPeriod} trends`;
    }
}

// Auto-refresh data every 5 minutes - only handle 'today' and 'month'
setInterval(() => {
    const activeButton = document.querySelector('.time-filter .btn.active');
    if (activeButton) {
        let period = activeButton.textContent.toLowerCase().trim();
        if (period === 'this month') period = 'month';
        if (period === 'today') period = 'today';
        
        changeTimeFilter(period);
    }
}, 300000); // 5 minutes

// Initial load notification
document.addEventListener('DOMContentLoaded', function() {
    // Ensure Today tab is active on page load
    const todayButton = document.querySelector('.time-filter .btn[onclick*="today"]');
    if (todayButton && !todayButton.classList.contains('active')) {
        document.querySelectorAll('.time-filter .btn').forEach(btn => btn.classList.remove('active'));
        todayButton.classList.add('active');
    }
    
    // Show a subtle notification that the dashboard is live
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 70px;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        z-index: 1000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    notification.innerHTML = `
        <div style="display: flex; align-items: center;">
            <i class="ri-check-line" style="margin-right: 0.5rem;"></i>
            Dashboard loaded with today's data
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Slide in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Slide out after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 1000);
    }, 1000);
});

function renderCustomLegend() {
    const legendContainer = document.getElementById('customLegend');
    legendContainer.innerHTML = '';
    if (!businessChart) return;

    const labels = businessChart.data.labels;
    const data = businessChart.data.datasets[0].data;
    const bgColors = businessChart.data.datasets[0].backgroundColor;

    labels.forEach((label, i) => {
        const color = bgColors[i];
        const value = data[i];
        const legendItem = document.createElement('div');
        legendItem.className = 'legend-item m-2 px-3 py-2 rounded d-flex align-items-center';
        legendItem.style.cursor = 'pointer';
        legendItem.style.background = '#f8fafc';
        legendItem.style.border = '1px solid #e2e8f0';
        legendItem.style.transition = 'background 0.2s';

        legendItem.innerHTML = `
            <span style="display:inline-block;width:18px;height:18px;border-radius:4px;background:${color};margin-right:10px;"></span>
            <span style="font-weight:600;">${label}</span>
            <span style="margin-left:8px;color:#888;">(${value})</span>
        `;

        legendItem.onclick = () => {
            highlightSegment(i);
        };

        legendContainer.appendChild(legendItem);
    });
}

function highlightSegment(index) {
    if (!businessChart) return;
    // Set all segments to default opacity
    businessChart.data.datasets[0].backgroundColor = businessChart.data.datasets[0].backgroundColor.map((color, i) =>
        i === index ? color : color.replace('0.8', '0.2')
    );
    businessChart.update();

    // Show details below the chart
    const label = businessChart.data.labels[index];
    const value = businessChart.data.datasets[0].data[index];
    const total = businessChart.data.datasets[0].data.reduce((a, b) => a + b, 0);
    const percentage = total ? ((value / total) * 100).toFixed(1) : 0;
    document.getElementById('serviceDetails').innerHTML =
        `<span style="font-size:1.1em;">${label}:</span> <b>${value}</b> (${percentage}%)`;
}
</script>

@endsection