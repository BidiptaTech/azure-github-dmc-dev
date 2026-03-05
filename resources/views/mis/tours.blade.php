@extends('layouts.layout')
@section('title', 'Tour MIS Report')
@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <h4 class="fw-bold mb-0">
                <i class="ri-file-list-3-line me-2 text-primary"></i>Tour MIS Report
            </h4>
            <a href="{{ route('mis.tours.export', request()->query()) }}" class="btn btn-success">
                <i class="ri-file-excel-2-line me-1"></i>Export to Excel
            </a>
        </div>

        {{-- Filters --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Filters</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('mis.tours') }}" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $filters['from_date'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $filters['to_date'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Agent</label>
                        <select name="agent" class="form-select">
                            <option value="">All Agents</option>
                            @foreach($agents as $a)
                                <option value="{{ $a->id }}" {{ ($filters['agent'] ?? '') == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">DMC</label>
                        <select name="dmc" class="form-select">
                            <option value="">All DMCs</option>
                            @foreach($dmcs as $d)
                                <option value="{{ $d->id }}" {{ ($filters['dmc'] ?? '') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Booking Status</label>
                        <select name="booking_status" class="form-select">
                            <option value="">All</option>
                            <option value="0" {{ ($filters['booking_status'] ?? '') === '0' ? 'selected' : '' }}>Not Started</option>
                            <option value="1" {{ ($filters['booking_status'] ?? '') === '1' ? 'selected' : '' }}>Confirmed</option>
                            <option value="2" {{ ($filters['booking_status'] ?? '') === '2' ? 'selected' : '' }}>In Progress</option>
                            <option value="3" {{ ($filters['booking_status'] ?? '') === '3' ? 'selected' : '' }}>Completed</option>
                            <option value="4" {{ ($filters['booking_status'] ?? '') === '4' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-filter-line me-1"></i>Apply
                        </button>
                        <a href="{{ route('mis.tours') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Report Table --}}
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Booking ID</th>
                                <th>Booking Date</th>
                                <th>Tour Name</th>
                                <th>Destination</th>
                                <th>Agent Name</th>
                                <th>DMC Name</th>
                                <th>PAX</th>
                                <th>Selling Price</th>
                                <th>Agent Commission</th>
                                <th>DMC Commission</th>
                                <th>Net Profit</th>
                                <th>Transaction Ref.</th>
                                <th>Payment Status</th>
                                <th>Booking Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($report as $row)
                                <tr>
                                    <td>{{ $row->booking_id }}</td>
                                    <td>{{ $row->booking_date ? \Carbon\Carbon::parse($row->booking_date)->format('d M Y') : '—' }}</td>
                                    <td>{{ $row->tour_name ?? '—' }}</td>
                                    <td>{{ $row->destination }}</td>
                                    <td>{{ $row->agent_name }}</td>
                                    <td>{{ $row->dmc_name }}</td>
                                    <td>{{ $row->pax }}</td>
                                    <td>{{ number_format($row->selling_price, 2) }}</td>
                                    <td>{{ number_format($row->agent_commission, 2) }}</td>
                                    <td>{{ number_format($row->dmc_commission, 2) }}</td>
                                    <td>{{ number_format($row->net_profit, 2) }}</td>
                                    <td>{{ $row->transaction_reference_number }}</td>
                                    <td><span class="badge bg-{{ $row->payment_status === 'paid' ? 'success' : ($row->payment_status === 'issued' ? 'warning' : 'secondary') }}">{{ $row->payment_status ?: '—' }}</span></td>
                                    <td><span class="badge bg-info">{{ $row->booking_status }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="15" class="text-center text-muted py-4">No records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
