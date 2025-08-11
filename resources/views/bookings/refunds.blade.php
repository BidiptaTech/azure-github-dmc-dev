@extends('layouts.layout')
@section('title', 'Refunds')
@extends('layouts.datatablecss')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-3 mb-2">
                <i class="ri-money-dollar-circle-line me-2 text-success"></i>
                <span class="text-muted fw-light">Bookings /</span> Refunds
            </h4>
            <p class="text-muted">Manage refunds and track refund status</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-success fs-6">
                <i class="ri-money-dollar-circle-line me-1"></i>
                0 Refunds
            </span>
        </div>
    </div>

    <!-- Coming Soon Message -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="ri-money-dollar-circle-line ri-64px text-muted"></i>
                    </div>
                    <h4 class="text-muted mb-3">Refunds Section</h4>
                    <p class="text-muted mb-4">This section is currently under development and will be available soon.</p>
                    <p class="text-muted">Refunds functionality will include:</p>
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <ul class="list-unstyled text-start">
                                <li class="mb-2"><i class="ri-check-line text-success me-2"></i>Refund request tracking</li>
                                <li class="mb-2"><i class="ri-check-line text-success me-2"></i>Refund status management</li>
                                <li class="mb-2"><i class="ri-check-line text-success me-2"></i>Payment reversal processing</li>
                                <li class="mb-2"><i class="ri-check-line text-success me-2"></i>Refund approval workflow</li>
                                <li class="mb-2"><i class="ri-check-line text-success me-2"></i>Refund history and reports</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@endsection

@extends('layouts.datatablejs')
