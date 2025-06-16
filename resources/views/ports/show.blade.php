@extends('layouts.layout')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Breadcrumb -->
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Ports /</span> View Port Details
        </h4>

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Port Information</h5>
                        <a href="{{ route('ports.index') }}" class="btn btn-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Back to List
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body">
                                            <h3 class="card-title text-white">{{ $port->port_name }}</h3>
                                            <p class="card-text mb-0">
                                                <i class="ri-map-pin-2-line me-1"></i> {{ $port->city->name ?? 'N/A' }}, {{ $port->country }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Basic Details</h5>
                                        <div class="mb-3">
                                            <label class="fw-bold">Port Type</label>
                                            <p class="mb-0">
                                                @php
                                                    $typeIcons = [
                                                        'Airport' => 'ri-plane-line',
                                                        'Seaport' => 'ri-ship-line',
                                                        'LandPort' => 'ri-bus-line',
                                                        'Railway' => 'ri-train-line',
                                                        'BusStand' => 'ri-bus-2-line'
                                                    ];
                                                    $typeColors = [
                                                        'Airport' => 'primary',
                                                        'Seaport' => 'info',
                                                        'LandPort' => 'success',
                                                        'Railway' => 'warning',
                                                        'BusStand' => 'danger'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $typeColors[$port->type] ?? 'secondary' }}">
                                                    <i class="{{ $typeIcons[$port->type] ?? 'ri-question-line' }} me-1"></i>
                                                    {{ $port->type }}
                                                </span>
                                            </p>
                                        </div>

                                        <div class="mb-3">
                                            <label class="fw-bold">Status</label>
                                            <p class="mb-0">
                                                <span class="badge bg-{{ $port->status ? 'success' : 'danger' }}">
                                                    <i class="ri-{{ $port->status ? 'checkbox-circle-line' : 'close-circle-line' }} me-1"></i>
                                                    {{ $port->status ? 'Active' : 'Inactive' }}
                                                </span>
                                            </p>
                                        </div>

                                        <div class="mb-3">
                                            <label class="fw-bold">Distance</label>
                                            <p class="mb-0">{{ $port->distance }} miles</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Location Details</h5>
                                        <div class="mb-3">
                                            <label class="fw-bold">Coordinates</label>
                                            <div class="d-flex align-items-center gap-3 mb-2">
                                                <div class="bg-light rounded p-2">
                                                    <small class="text-muted d-block">Latitude</small>
                                                    <span class="fw-semibold">{{ $port->latitude }}</span>
                                                </div>
                                                <div class="bg-light rounded p-2">
                                                    <small class="text-muted d-block">Longitude</small>
                                                    <span class="fw-semibold">{{ $port->longitude }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Map placeholder - You can integrate a real map here -->
                                        {{-- <div class="border rounded" style="height: 300px; background-color: #f8f9fa;">
                                            <div class="d-flex justify-content-center align-items-center h-100">
                                                <div class="text-center">
                                                    <i class="ri-map-2-line fs-1 text-muted"></i>
                                                    <p class="mb-0 mt-2">Map View</p>
                                                </div>
                                            </div>
                                        </div> --}}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-4">
                            <div class="d-flex gap-2">
                                <a href="{{ route('ports.edit', $port->port_id) }}" class="btn btn-primary">
                                    <i class="ri-pencil-line me-1"></i> Edit Port
                                </a>
                                <form action="{{ route('ports.destroy', $port->port_id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" 
                                            onclick="return confirm('Are you sure you want to delete this port?')">
                                        <i class="ri-delete-bin-line me-1"></i> Delete Port
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .card-title {
        margin-bottom: 1.5rem;
    }
    .badge {
        padding: 0.5rem 0.8rem;
        font-size: 0.875rem;
    }
    .bg-light {
        background-color: #f8f9fa;
    }
    .fw-semibold {
        font-weight: 600;
    }
</style>
@endsection 