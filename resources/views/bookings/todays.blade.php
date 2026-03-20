@extends('layouts.layout')
@section('title', "Trip Log")

@push('css')
<style>
    /* Override Bootstrap table cell styles */
   
    .trip-log-page { background: #f1f5f9; min-height: 100vh; padding: 0.5rem 0 1rem; }
    .trip-log-header {
        background: #fff;
        border-radius: 0.375rem;
        border: 1px solid #e2e8f0;
        padding: 0.4rem 0.75rem;
        margin-bottom: 0.5rem;
    }
    .trip-log-header .trip-log-title { font-size: 0.875rem; font-weight: 600; color: #334155; }
    .trip-log-header .form-label { font-size: 0.7rem; color: #64748b; margin-bottom: 0; margin-right: 0.25rem; }
    .trip-log-header .form-control { font-size: 0.75rem; height: 28px; width: 130px; padding: 0.2rem 0.4rem; }
    .trip-log-header .btn { font-size: 0.7rem; padding: 0.2rem 0.5rem; height: 28px; }
    .trip-log-tabs { margin-bottom: 0.35rem; border-bottom: 1px solid #e2e8f0; }
    .trip-log-tabs .nav-link {
        font-size: 0.8rem;
        font-weight: 600;
        color: #64748b;
        border: none;
        border-bottom: 2px solid transparent;
        padding: 0.35rem 0.65rem;
        margin-bottom: -1px;
    }
    .trip-log-tabs .nav-link:hover { color: #334155; }
    .trip-log-tabs .nav-link.active { color: #4f46e5; border-bottom-color: #4f46e5; background: transparent; }
    .trip-log-tab-pane { min-height: 50vh; }
    .trip-log-card { border: 1px solid #e2e8f0; border-radius: 0.375rem; overflow: hidden; }
    /* Force small table fonts - override any layout/Bootstrap */
    .trip-log-page .trip-log-table,
    .trip-log-page .trip-log-card .table { font-size: 10px !important; background: #fff; margin: 0; table-layout: auto; }
    .trip-log-page .trip-log-table thead th,
    .trip-log-page .trip-log-card .table thead th {
        background: #f1f5f9 !important;
        font-size: 14px !important;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        color: #475569;
        padding: 0.28rem 0.4rem;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .trip-log-page .trip-log-table tbody td,
    .trip-log-page .trip-log-card .table tbody td {
        font-size: 14px !important;
        padding: 0.25rem 0.4rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        line-height: 1.2;
    }
    .trip-log-table tbody tr:hover { background: #f8fafc; }
    .trip-log-table .trip-log-icon-cell { width: 24px; padding: 0.2rem; }
    .trip-log-page .trip-log-icon { width: 20px; height: 20px; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; background: #eef2ff; color: #4f46e5; font-size: 10px !important; }
    .trip-log-page .trip-log-footer { font-size: 9px !important; padding: 0.2rem 0.4rem; border-top: 1px solid #e2e8f0; background: #f8fafc; color: #64748b; }
    .trip-log-empty { font-size: 0.75rem; border: 1px dashed #cbd5e1; border-radius: 0.375rem; background: #f8fafc; padding: 1rem; text-align: center; color: #64748b; }

    .tab-content {
        padding: 0.5rem 0.05rem 0.05rem 0.05rem !important;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 trip-log-page">
<x-alert />
    <div class="trip-log-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        
        <span class="trip-log-title">Bookings / Trip Log</span>
        <form method="get" action="{{ route('bookings.today') }}" class="d-flex align-items-center">
            <label class="form-label">Trip Date</label>
            <input type="date" name="date" value="{{ $tripDate }}" class="form-control">
            <span class="text-danger mx-1">to</span>
            <input type="date" name="end_date" value="{{ $end_date??$tripDate }}" class="form-control">
            <button type="submit" class="btn btn-primary ms-1">Apply</button>
        </form>
    </div>

    <ul class="nav nav-tabs trip-log-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#all" role="tab">All</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#transfer-logs" role="tab">Transfer Logs</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#attractions" role="tab">Attractions</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#restaurants" role="tab">Restaurants</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#hotels" role="tab">Hotels</a>
        </li>
    </ul>

    <div class="tab-content">
        {{-- All (single table, sorted by date/time) --}}
        <div class="tab-pane fade show active trip-log-tab-pane" id="all" role="tabpanel">
            <div class="card trip-log-card">
                <div class="card-body p-0">
                    @if(count($allLogs ?? []) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover trip-log-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="trip-log-icon-cell"></th>
                                        <th>Type</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Reference No</th>
                                        <th>Guest</th>
                                        <th>Details</th>
                                        <th>Adults</th>
                                        <th>Child</th>
                                        <th>Other</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allLogs ?? [] as $row)
                                        <tr>
                                            <td><span class="trip-log-icon"><i class="{{ $row['icon'] ?? 'ri-car-line' }}"></i></span></td>
                                            <td>{{ $row['log_type'] ?? '—' }}</td>
                                            <td>{{ $row['date'] ?? '—' }}</td>
                                            <td>{{ $row['time'] ?? '—' }}</td>
                                            <td>{{ $row['reference_no'] ?? '—' }}</td>
                                            <td>{{ $row['guest'] ?? '—' }}</td>
                                            <td>
                                                @if(($row['log_type'] ?? '') === 'Transfer')
                                                    {{ \Str::limit(($row['from'] ?? '—') . ' → ' . ($row['to'] ?? '—'), 40) }}
                                                    @if(!empty($row['transfer_type'])) <span class="text-muted">({{ $row['transfer_type'] }})</span> @endif
                                                @elseif(($row['log_type'] ?? '') === 'Attraction')
                                                    {{ $row['name'] ?? '—' }} @if(!empty($row['ticket_type'])) <span class="text-muted">/ {{ $row['ticket_type'] }}</span> @endif
                                                @elseif(($row['log_type'] ?? '') === 'Restaurant')
                                                    {{ $row['name'] ?? '—' }} @if(!empty($row['meal_type'])) <span class="text-muted">/ {{ $row['meal_type'] }}</span> @endif
                                                @else
                                                    {{ $row['name'] ?? '—' }} — {{ $row['check_in'] ?? '—' }} to {{ $row['check_out'] ?? '—' }}
                                                @endif
                                            </td>
                                            <td>{{ $row['adults'] ?? '—' }}</td>
                                            <td>{{ $row['child'] ?? '—' }}</td>
                                            <td>
                                                @if(($row['log_type'] ?? '') === 'Transfer')
                                                    {{ $row['driver'] ?? '—' }}
                                                @elseif(($row['log_type'] ?? '') === 'Hotel')
                                                    {{ $row['rooms'] ?? '—' }} room(s)
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="trip-log-footer">Number of records: {{ count($allLogs ?? []) }}</div>
                    @else
                        <div class="trip-log-empty">No bookings for the selected date range.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Transfer Logs (single tab) --}}
        <div class="tab-pane fade trip-log-tab-pane" id="transfer-logs" role="tabpanel">
            <div class="card trip-log-card">
                <div class="card-body p-0">
                    @if(count($transferLogs) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover trip-log-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="trip-log-icon-cell"></th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Reference No</th>
                                        <th>Guest</th>
                                        <th>Transfer Type</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Type</th>
                                        <th>Adults</th>
                                        <th>Child</th>
                                        <th>Driver</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transferLogs as $row)
                                        <tr>
                                            <td><span class="trip-log-icon"><i class="{{ $row['icon'] ?? 'ri-car-line' }}"></i></span></td>
                                            <td>{{ $row['date'] ?? '—' }}</td>
                                            <td>{{ $row['time'] ?? '—' }}</td>
                                            <td>{{ $row['reference_no'] ?? '—' }}</td>
                                            <td>{{ $row['guest'] ?? '—' }}</td>
                                            <td>{{ $row['transfer_type'] ?? '—' }}</td>
                                            <td>{{ \Str::limit($row['from'] ?? '—', 30) }}</td>
                                            <td>{{ \Str::limit($row['to'] ?? '—', 30) }}</td>
                                            <td>{{ $row['type'] ?? '—' }}</td>
                                            <td>{{ $row['adults'] ?? 0 }}</td>
                                            <td>{{ $row['child'] ?? 0 }}</td>
                                            <td>{{ $row['driver'] ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="trip-log-footer">Number of records: {{ count($transferLogs) }}</div>
                    @else
                        <div class="trip-log-empty">No transfer logs for the selected date.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Attractions --}}
        <div class="tab-pane fade trip-log-tab-pane" id="attractions" role="tabpanel">
            <div class="card trip-log-card">
                <div class="card-body p-0">
                    @if(count($attractionLogs) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover trip-log-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="trip-log-icon-cell"></th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Reference No</th>
                                        <th>Guest</th>
                                        <th>Attraction Name</th>
                                        <th>Ticket / Type</th>
                                        <th>Adults</th>
                                        <th>Child</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attractionLogs as $row)
                                        <tr>
                                            <td><span class="trip-log-icon"><i class="ri-camera-line"></i></span></td>
                                            <td>{{ $row['date'] ?? '—' }}</td>
                                            <td>{{ $row['time'] ?? '—' }}</td>
                                            <td>{{ $row['reference_no'] ?? '—' }}</td>
                                            <td>{{ $row['guest'] ?? '—' }}</td>
                                            <td>{{ $row['name'] ?? '—' }}</td>
                                            <td>{{ $row['ticket_type'] ?? '—' }}</td>
                                            <td>{{ $row['adults'] ?? 0 }}</td>
                                            <td>{{ $row['child'] ?? 0 }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="trip-log-footer">Number of records: {{ count($attractionLogs) }}</div>
                    @else
                        <div class="trip-log-empty">No attraction bookings for the selected date.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Restaurants --}}
        <div class="tab-pane fade trip-log-tab-pane" id="restaurants" role="tabpanel">
            <div class="card trip-log-card">
                <div class="card-body p-0">
                    @if(count($restaurantLogs) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover trip-log-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="trip-log-icon-cell"></th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Reference No</th>
                                        <th>Guest</th>
                                        <th>Restaurant Name</th>
                                        <th>Meal Type</th>
                                        <th>Adults</th>
                                        <th>Child</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($restaurantLogs as $row)
                                        <tr>
                                            <td><span class="trip-log-icon"><i class="ri-restaurant-2-line"></i></span></td>
                                            <td>{{ $row['date'] ?? '—' }}</td>
                                            <td>{{ $row['time'] ?? '—' }}</td>
                                            <td>{{ $row['reference_no'] ?? '—' }}</td>
                                            <td>{{ $row['guest'] ?? '—' }}</td>
                                            <td>{{ $row['name'] ?? '—' }}</td>
                                            <td>{{ $row['meal_type'] ?? '—' }}</td>
                                            <td>{{ $row['adults'] ?? 0 }}</td>
                                            <td>{{ $row['child'] ?? 0 }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="trip-log-footer">Number of records: {{ count($restaurantLogs) }}</div>
                    @else
                        <div class="trip-log-empty">No restaurant bookings for the selected date.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Hotels --}}
        <div class="tab-pane fade trip-log-tab-pane" id="hotels" role="tabpanel">
            <div class="card trip-log-card">
                <div class="card-body p-0">
                    @if(count($hotelLogs) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover trip-log-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="trip-log-icon-cell"></th>
                                        <th>Reference No</th>
                                        <th>Guest</th>
                                        <th>Hotel Name</th>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                        <th>Rooms</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($hotelLogs as $row)
                                        <tr>
                                            <td><span class="trip-log-icon"><i class="ri-hotel-bed-line"></i></span></td>
                                            <td>{{ $row['reference_no'] ?? '—' }}</td>
                                            <td>{{ $row['guest'] ?? '—' }}</td>
                                            <td>{{ $row['name'] ?? '—' }}</td>
                                            <td>{{ $row['check_in'] ?? '—' }}</td>
                                            <td>{{ $row['check_out'] ?? '—' }}</td>
                                            <td>{{ $row['rooms'] ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="trip-log-footer">Number of records: {{ count($hotelLogs) }}</div>
                    @else
                        <div class="trip-log-empty">No hotel bookings for the selected date.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
