@extends('layouts.layout')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css" rel="stylesheet">

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Breadcrumb -->
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('packaged-attractions.index') }}">Packaged Attractions</a></li>
                        <li class="breadcrumb-item active">{{ $packagedAttraction->name }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Header Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-primary text-white shadow-lg" style="background-image: linear-gradient(135deg, #6B73FF 10%, #000DFF 100%);">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="fw-bold mb-1">{{ $packagedAttraction->name }}</h2>
                                <p class="mb-0">Package ID: {{ $packagedAttraction->package_attraction_id ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <a href="{{ route('packaged-attractions.index') }}" class="btn btn-light me-2">
                                    <i class="mdi mdi-arrow-left me-1"></i> Back to List
                                </a>
                                <a href="{{ route('packaged-attractions.edit', Crypt::encrypt($packagedAttraction->package_attraction_id)) }}" class="btn btn-warning">
                                    <i class="mdi mdi-pencil-outline me-1"></i> Edit Package
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Info Card -->
            <div class="col-md-8 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light py-3">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-information-outline text-primary me-2 fs-3"></i>
                            <h5 class="card-title mb-0">Package Information</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="p-3 border rounded bg-light-subtle">
                                    <h6 class="text-muted mb-2">Adult Price</h6>
                                    <h3 class="fw-bold text-success mb-0">${{ number_format($packagedAttraction->adult_price, 2) }}</h3>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="p-3 border rounded bg-light-subtle">
                                    <h6 class="text-muted mb-2">Child Price</h6>
                                    <h3 class="fw-bold text-info mb-0">${{ number_format($packagedAttraction->child_price, 2) }}</h3>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="p-3 border rounded bg-light-subtle">
                                    <h6 class="text-muted mb-2">Senior Citizen Price</h6>
                                    <h3 class="fw-bold text-warning mb-0">${{ number_format($packagedAttraction->senior_citizen_price, 2) }}</h3>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="p-3 border rounded bg-light-subtle">
                                    <h6 class="text-muted mb-2">Status</h6>
                                    <h3 class="mb-0">
                                        @if($packagedAttraction->status == 1)
                                            <span class="badge bg-success bg-glow px-3 py-2">Active</span>
                                        @else
                                            <span class="badge bg-danger bg-glow px-3 py-2">Inactive</span>
                                        @endif
                                    </h3>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h6 class="fw-semibold d-flex align-items-center">
                                <i class="mdi mdi-text-box-outline me-2"></i> Description
                            </h6>
                            <div class="p-3 border rounded">
                                <p class="mb-0">{{ $packagedAttraction->description ?? 'No description available' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Images Card -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-light py-3">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-image-multiple text-primary me-2 fs-3"></i>
                            <h5 class="card-title mb-0">Package Images</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($packagedAttraction->image)
                            <div class="mb-4 text-center">
                                <h6 class="fw-semibold mb-3">Featured Image</h6>
                                <img src="{{ $packagedAttraction->image }}" alt="{{ $packagedAttraction->name }}" class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                            </div>
                        @endif
                        
                        @if(!empty($packagedAttraction->getGalleryImagesArrayAttribute()))
                            <div class="mb-3">
                                <h6 class="fw-semibold mb-3">Gallery Images</h6>
                                <div class="row g-3">
                                    @foreach($packagedAttraction->getGalleryImagesArrayAttribute() as $image)
                                        <div class="col-6">
                                            <a href="{{ $image }}" target="_blank" class="d-block">
                                                <img src="{{ $image }}" alt="Gallery Image" class="img-fluid rounded shadow-sm hover-zoom">
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="mdi mdi-image-off text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mb-0">No gallery images available</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Attractions Card -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-light py-3">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-map-marker-multiple text-primary me-2 fs-3"></i>
                            <h5 class="card-title mb-0">Included Attractions</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(count($packagedAttraction->attractionsList()) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" width="80">#</th>
                                            <th>Attraction Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($packagedAttraction->attractionsList() as $key => $attraction)
                                            <tr>
                                                <td class="text-center">
                                                    <div class="avatar avatar-sm bg-primary rounded-circle">
                                                        <span class="avatar-initial">{{ $key + 1 }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="mdi mdi-map-marker me-2 text-danger"></i>
                                                        <div>
                                                            <h6 class="mb-0">{{ $attraction->name }}</h6>
                                                            <small class="text-muted">ID: {{ $attraction->id }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="mdi mdi-alert-circle-outline text-warning" style="font-size: 3rem;"></i>
                                <h6 class="mt-3">No attractions included in this package</h6>
                                <p class="text-muted">This package doesn't have any attractions assigned to it yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Card -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card shadow-sm bg-light-subtle">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="mdi mdi-calendar-clock text-primary me-2 fs-4"></i>
                                    <div>
                                        <h6 class="fw-semibold mb-1">Created</h6>
                                        <p class="mb-0">{{ $packagedAttraction->created_at->format('M d, Y h:i A') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="mdi mdi-update text-info me-2 fs-4"></i>
                                    <div>
                                        <h6 class="fw-semibold mb-1">Last Updated</h6>
                                        <p class="mb-0">{{ $packagedAttraction->updated_at->format('M d, Y h:i A') }}</p>
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
    .hover-zoom {
        transition: transform 0.3s ease;
    }
    .hover-zoom:hover {
        transform: scale(1.05);
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
        background-image: linear-gradient(135deg, #6B73FF 10%, #000DFF 100%) !important;
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
    .text-warning {
        color: #F57C00 !important;
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