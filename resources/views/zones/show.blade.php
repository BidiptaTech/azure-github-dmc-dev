@extends('layouts.layout')

@section('title', 'Zone Details')

@section('css')
<style>
    .card-3d {
        transition: all 0.3s;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border-radius: 15px;
        overflow: hidden;
        background: linear-gradient(145deg, #bedceb, #cba7cfee);
    }
    
    .card-header-3d {
        background: linear-gradient(145deg, #5293d3, #c5d5e6);
        border-bottom: none;
        padding: 1.5rem;
    }
    
    .info-group {
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        border: none;
        transform: translateZ(0);
        position: relative;
    }
    
    .info-group:hover {
        transform: translateY(-4px);
        box-shadow: 0 15px 20px rgba(0,0,0,0.1);
    }
    
    .info-group:before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 5px;
        background: linear-gradient(to bottom, #4e73df, #3a54c4);
        border-radius: 4px 0 0 4px;
    }
    
    .btn-3d {
        position: relative;
        box-shadow: 0 4px 6px rgba(0,0,0,0.15);
        transition: all 0.3s;
        transform: translateZ(0);
        border: none;
    }
    
    .btn-3d:hover {
        transform: translateY(-3px);
        box-shadow: 0 7px 14px rgba(0,0,0,0.2);
    }
    
    .btn-3d:active {
        transform: translateY(-1px);
    }
    
    .zone-title {
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }
    
    .badge-3d {
        box-shadow: 0 3px 5px rgba(0,0,0,0.1);
        border-radius: 30px;
        padding: 8px 15px;
        font-weight: 600;
    }
    
    .date-display {
        position: relative;
        padding-left: 10px;
    }
    
    .date-display:before {
        content: "";
        position: absolute;
        left: 0;
        top: 50%;
        height: 70%;
        width: 3px;
        background: linear-gradient(to bottom, #4e73df, #3a54c4);
        transform: translateY(-50%);
        border-radius: 10px;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4 zone-title">
        <span class="text-muted fw-light">Zone /</span> Zone Details
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-3d mb-4">
                <div class="card-header card-header-3d d-flex justify-content-between align-items-center">
                    <h5 class="m-0" style="color: #141313"><i class="bx bx-map-pin me-2"></i>Zone Information</h5>
                    <div>
                        <a href="{{ route('zones.edit', $zone->zone_id) }}" class="btn btn-primary btn-3d"><i class="bx bx-edit me-1"></i>Edit</a>
                        <a href="{{ route('zones.index') }}" class="btn btn-secondary btn-3d ms-2"><i class="bx bx-arrow-back me-1"></i>Back to List</a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-group mb-3 p-4 rounded bg-white">
                                <label class="text-muted mb-2 fs-6">Zone ID</label>
                                <p class="mb-0 fw-bold fs-5">{{ $zone->zone_id }}</p>
                            </div>
                            
                            <div class="info-group mb-3 p-4 rounded bg-white">
                                <label class="text-muted mb-2 fs-6">Zone Name</label>
                                <p class="mb-0 fw-bold fs-5">{{ $zone->zone_name }}</p>
                            </div>
                            
                            <div class="info-group mb-3 p-4 rounded bg-white">
                                <label class="text-muted mb-2 fs-6">Zone Type</label>
                                <p class="mb-0 fw-bold fs-5">{{ $zone->zone_type }}</p>
                            </div>
                            
                            <div class="info-group mb-3 p-4 rounded bg-white">
                                <label class="text-muted mb-2 fs-6">City</label>
                                <p class="mb-0 fw-bold fs-5">{{ $cityName }}</p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-group mb-3 p-4 rounded bg-white">
                                <label class="text-muted mb-2 fs-6">Description</label>
                                <p class="mb-0 fw-bold fs-5">{{ strip_tags($zone->description ?? 'N/A') }}</p>
                            </div>
                            
                            <div class="info-group mb-3 p-4 rounded bg-white">
                                <label class="text-muted mb-2 fs-6">Status</label>
                                <p class="mb-0">
                                    <span class="badge badge-3d bg-{{ $zone->status == 1 ? 'success' : 'danger' }}">
                                        <i class="bx {{ $zone->status == 1 ? 'bx-check-circle' : 'bx-x-circle' }} me-1"></i>
                                        {{ $zone->status == 1 ? 'Active' : 'Inactive' }}
                                    </span>
                                </p>
                            </div>
                            
                            <div class="info-group mb-3 p-4 rounded bg-white">
                                <label class="text-muted mb-2 fs-6">Created</label>
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-calendar fs-4 me-2"></i>
                                    <div class="date-display">
                                        <p class="mb-0 fw-bold">{{ $zone->created_at->format('l, F d, Y') }}</p>
                                        <p class="mb-0 text-muted">{{ $zone->created_at->format('h:i A') }} <span class="badge bg-light text-primary ms-2">{{ $zone->created_at->diffForHumans() }}</span></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="info-group mb-3 p-4 rounded bg-white">
                                <label class="text-muted mb-2 fs-6">Last Updated</label>
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-refresh fs-4 me-2"></i>
                                    <div class="date-display">
                                        <p class="mb-0 fw-bold">{{ $zone->updated_at->format('l, F d, Y') }}</p>
                                        <p class="mb-0 text-muted">{{ $zone->updated_at->format('h:i A') }} <span class="badge bg-light text-primary ms-2">{{ $zone->updated_at->diffForHumans() }}</span></p>
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
@endsection