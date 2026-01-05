@extends('layouts.layout')
@section('title', 'Agent Details')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-user-circle me-2"></i>Travel Agent Details
                </h5>
                <div class="d-flex gap-2">
                    @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 2 || auth()->user()->role_id == 3 || auth()->user()->role_id == 4 || auth()->user()->role_id == 19 || auth()->user()->role_id == 20)
                        <a href="{{ route('agents.edit', Crypt::encrypt($agent->agent_id)) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                    @endif
                    <a href="{{ route('agents.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Personal Information -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-user me-2"></i>Personal Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-4 fw-bold text-muted">Name:</div>
                            <div class="col-8">{{ $agent->salutation }} {{ $agent->name }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4 fw-bold text-muted">Email:</div>
                            <div class="col-8">
                                <a href="mailto:{{ $agent->email }}">{{ $agent->email }}</a>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4 fw-bold text-muted">Phone:</div>
                            <div class="col-8">
                                <a href="tel:{{ $agent->phone }}">{{ $agent->phone }}</a>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4 fw-bold text-muted">Designation:</div>
                            <div class="col-8">{{ $agent->designation ?? 'N/A' }}</div>
                        </div>
                        @if($agent->user_country || $agent->city)
                        <div class="row mb-3">
                            <div class="col-4 fw-bold text-muted">Location:</div>
                            <div class="col-8">
                                {{ $agent->city ?? 'N/A' }}@if($agent->city && $agent->user_country), @endif{{ $agent->user_country ?? '' }}
                            </div>
                        </div>
                        @endif
                        @if($agent->agent_address)
                        <div class="row mb-3">
                            <div class="col-4 fw-bold text-muted">Address:</div>
                            <div class="col-8">{{ $agent->agent_address }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Company Information -->
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-building me-2"></i>Company Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-4 fw-bold text-muted">Agency:</div>
                            <div class="col-8">{{ $agent->company_name ?? 'N/A' }}</div>
                        </div>
                        @if($agent->agency_id)
                        <div class="row mb-3">
                            <div class="col-4 fw-bold text-muted">Agency ID:</div>
                            <div class="col-8">{{ $agent->agency_id }}</div>
                        </div>
                        @endif
                        @php
                            $dmc = $agent->getDmc($agent->agent_id);
                            $agencyId = $agent->agency_id ?? null;
                            $agency = $agencyId ? App\Models\Agency::where('agency_id', $agencyId)->first() : null;
                            $dmcIds = [];
                            
                            if ($agency && !empty($agency->dmc_id)) {
                                $dmcIds = is_array($agency->dmc_id) 
                                    ? $agency->dmc_id 
                                    : json_decode($agency->dmc_id, true);
                                    
                                if (!is_array($dmcIds)) {
                                    $dmcIds = [];
                                }
                            }
                            
                            $dmcCompanies = [];
                            if (!empty($dmcIds)) {
                                $dmcCompanies = App\Models\User::whereIn('userId', $dmcIds)
                                    ->pluck('company_name')
                                    ->filter()
                                    ->values()
                                    ->toArray();
                            }
                        @endphp
                        @if(count($dmcCompanies) > 0)
                        <div class="row mb-3">
                            <div class="col-4 fw-bold text-muted">DMC Companies:</div>
                            <div class="col-8">
                                @foreach($dmcCompanies as $dmc)
                                    <span class="badge bg-primary me-1 mb-1">{{ $dmc }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        <div class="row mb-3">
                            <div class="col-4 fw-bold text-muted">Status:</div>
                            <div class="col-8">
                                @if($agent->status == 1)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Active
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle me-1"></i>Inactive
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4 fw-bold text-muted">Created:</div>
                            <div class="col-8">
                                {{ $agent->created_at ? $agent->created_at->format('M d, Y h:i A') : 'N/A' }}
                            </div>
                        </div>
                        @if($agent->updated_at)
                        <div class="row mb-3">
                            <div class="col-4 fw-bold text-muted">Last Updated:</div>
                            <div class="col-8">
                                {{ $agent->updated_at->format('M d, Y h:i A') }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        @if($agent->agent_image || $agent->image)
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-images me-2"></i>Documents & Images
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if($agent->agent_image)
                            <div class="col-md-6 mb-3">
                                <h6 class="fw-bold">Agent Photo:</h6>
                                <img src="{{ asset('storage/' . $agent->agent_image) }}" 
                                     alt="Agent Photo" 
                                     class="img-thumbnail" 
                                     style="max-width: 300px; max-height: 300px;">
                            </div>
                            @endif
                            @if($agent->image)
                            <div class="col-md-6 mb-3">
                                <h6 class="fw-bold">ID Proof:</h6>
                                <img src="{{ asset('storage/' . $agent->image) }}" 
                                     alt="ID Proof" 
                                     class="img-thumbnail" 
                                     style="max-width: 300px; max-height: 300px;">
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
.card {
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.card-header {
    border-radius: 0.5rem 0.5rem 0 0;
    padding: 1rem 1.25rem;
}

.row.mb-3:last-child {
    margin-bottom: 0 !important;
}

.badge {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}
</style>
@endsection

