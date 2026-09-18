@extends('layouts.layout')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css" rel="stylesheet">

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Breadcrumb -->
        <div class="row mb-3">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('multiResturant.index') }}">Multi Restaurants</a></li>
                        <li class="breadcrumb-item active">{{ $multiRestaurant->package_name }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Header Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white" style="background-image: linear-gradient(135deg,rgb(86, 95, 255) 10%, #000DFF 100%);">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="fw-semibold text-white mb-1">{{ $multiRestaurant->package_name }}</h4>
                                <p class="mb-0 small">Package ID: {{ $multiRestaurant->package_id ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <a href="{{ route('multiResturant.index') }}" class="btn btn-light">
                                    <i class="mdi mdi-arrow-left me-1"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Info Card (Price & Status only) -->
        <div class="row">
            <div class="col-12 mb-3">
                <div class="card shadow-sm">
                    <div class="card-header bg-light py-2">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-information-outline text-primary me-2 fs-5"></i>
                            <h6 class="card-title mb-0">Package Information</h6>
                        </div>
                    </div>
                    <div class="card-body py-3">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="p-2 border rounded bg-light-subtle">
                                    <h6 class="text-muted mb-1">Adult Price</h6>
                                    <h5 class="fw-semibold text-success mb-0">${{ number_format($multiRestaurant->adult_price ?? 0, 2) }}</h5>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="p-2 border rounded bg-light-subtle">
                                    <h6 class="text-muted mb-1">Child Price</h6>
                                    <h5 class="fw-semibold text-success mb-0">
                                        @if(isset($multiRestaurant->child_price) && $multiRestaurant->child_price !== null)
                                            ${{ number_format($multiRestaurant->child_price, 2) }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </h5>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="p-2 border rounded bg-light-subtle">
                                    <h6 class="text-muted mb-1">Company</h6>
                                    <h6 class="fw-semibold mb-0">
                                        @if(!empty($multiRestaurant->dmc_id))
                                            @php
                                                $dmcUser = \App\Models\User::where('dmcId', $multiRestaurant->dmc_id)->first();
                                            @endphp
                                            {{ $dmcUser->company_name ?? 'DMC #' . $multiRestaurant->dmc_id }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </h6>
                                </div>
                            </div>
                            <div class="col-md-12 mb-0">
                                <div class="p-2 border rounded bg-light-subtle">
                                    <h6 class="text-muted mb-1">Status</h6>
                                    <h6 class="mb-0">
                                        @if($multiRestaurant->status == 1)
                                            <span class="badge bg-success bg-glow px-3 py-2">Active</span>
                                        @else
                                            <span class="badge bg-danger bg-glow px-3 py-2">Inactive</span>
                                        @endif
                                    </h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Restaurants Card -->
        <div class="row">
            <div class="col-12 mb-3">
                <div class="card shadow-sm">
                    <div class="card-header bg-light py-2">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-silverware-fork-knife text-primary me-2 fs-5"></i>
                            <h6 class="card-title mb-0">Included Restaurants</h6>
                        </div>
                    </div>
                    <div class="card-body py-3">
                        @if($multiRestaurant->getRestaurantsList()->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" width="80">Image</th>
                                            <th>Restaurant Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($multiRestaurant->getRestaurantsList() as $restaurant)
                                            @php
                                                $img = $restaurant->master_image ?? '';
                                                $imgSrc = $img ? (strpos($img, 'http') === 0 || strpos($img, '/') === 0 ? $img : '/'.$img) : null;
                                            @endphp
                                            <tr>
                                                <td class="text-center">
                                                    @if($imgSrc)
                                                        <img src="{{ $imgSrc }}" alt="{{ $restaurant->name ?? 'Restaurant' }}" class="rounded-circle object-fit-cover" style="width: 48px; height: 48px; object-fit: cover;">
                                                    @else
                                                        <div class="avatar avatar-sm bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                            <i class="mdi mdi-silverware-fork-knife text-white" style="font-size: 1.25rem;"></i>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="mdi mdi-silverware-fork-knife me-2 text-danger"></i>
                                                        <div>
                                                            <h6 class="mb-0">{{ $restaurant->name ?? 'N/A' }}</h6>
                                                            <small class="text-muted">ID: {{ $restaurant->restaurant_id ?? $restaurant->id }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="mdi mdi-alert-circle-outline text-warning" style="font-size: 2.5rem;"></i>
                                <h6 class="mt-2 mb-1">No restaurants included in this package</h6>
                                <p class="text-muted mb-0 small">This package doesn't have any restaurants assigned to it yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Card -->
        <div class="row">
            <div class="col-12 mb-3">
                <div class="card shadow-sm bg-light-subtle">
                    <div class="card-body py-3">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="mdi mdi-calendar-clock text-primary me-2 fs-4"></i>
                                    <div>
                                        <h6 class="fw-semibold mb-1">Created</h6>
                                        <p class="mb-0">{{ $multiRestaurant->created_at->format('M d, Y h:i A') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="mdi mdi-update text-info me-2 fs-4"></i>
                                    <div>
                                        <h6 class="fw-semibold mb-1">Last Updated</h6>
                                        <p class="mb-0">{{ $multiRestaurant->updated_at->format('M d, Y h:i A') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    $(document).ready(function() {
        // Add any specific scripts for the show page here
    });
</script>

<style>
    .bg-glow {
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        font-size: 0.85rem;
    }
    .avatar-initial {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }
    .bg-primary {
        background-image: linear-gradient(135deg,rgb(83, 91, 255) 10%, #000DFF 100%) !important;
        border: none;
    }
    .badge.bg-success {
        background-image: linear-gradient(135deg, #4CAF50 10%, #2E7D32 100%) !important;
    }
    .badge.bg-danger {
        background-image: linear-gradient(135deg, #FF5252 10%, #D32F2F 100%) !important;
    }
    .text-success {
        color: #2E7D32 !important;
    }
    .text-info {
        color: #0288D1 !important;
    }
    .card {
        border-radius: 0.75rem;
        overflow: hidden;
    }
    .card-header {
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        font-size: 1.2rem;
        line-height: 1;
    }
</style>
@endsection
@endsection
