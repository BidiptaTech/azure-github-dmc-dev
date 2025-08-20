@extends('layouts.layout')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-gradient-primary text-white">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="ri-map-pin-line me-3 fs-4"></i>
                                <div>
                                    <h4 class="mb-1 text-white">Single Tour Packages</h4>
                                    <p class="mb-0 opacity-75">Manage your personalized tour experiences</p>
                                </div>
                            </div>
                            <a href="{{ route('single-tour-package.create') }}" class="btn btn-light">
                                <i class="ri-add-line me-2"></i>Create New Package
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-check-circle-line me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Packages List -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        @if($packages->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Package Details</th>
                                            <th>Destination</th>
                                            <th>Travel Dates</th>
                                            <th>Guests</th>
                                            <th>Agent</th>
                                            <th>Budget</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($packages as $index => $package)
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark">{{ $packages->firstItem() + $index }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <h6 class="mb-1 fw-bold">{{ $package->package_name }}</h6>
                                                    <small class="text-muted">
                                                        <i class="ri-time-line me-1"></i>{{ $package->formatted_duration }}
                                                        @if($package->is_premium)
                                                            <span class="badge bg-warning text-dark ms-2">
                                                                <i class="ri-vip-crown-line me-1"></i>Premium
                                                            </span>
                                                        @endif
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold">{{ $package->city->name ?? 'N/A' }}</span>
                                                    <small class="text-muted">{{ $package->country->name ?? 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold">{{ $package->check_in_time ? \Carbon\Carbon::parse($package->check_in_time)->format('d M Y') : 'N/A' }}</span>
                                                    <small class="text-muted">to {{ $package->check_out_time ? \Carbon\Carbon::parse($package->check_out_time)->format('d M Y') : 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="badge bg-primary me-2">{{ $package->total_guests }}</div>
                                                    <small class="text-muted">
                                                        {{ $package->adults }}A
                                                        @if($package->children > 0), {{ $package->children }}C @endif
                                                        @if($package->infants > 0), {{ $package->infants }}I @endif
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold">{{ $package->agent->name ?? 'N/A' }}</span>
                                                    <small class="text-muted">ID: {{ $package->agent_id }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success">{{ $package->formatted_budget }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $package->status_color }}">
                                                    {{ ucfirst($package->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="ri-more-2-line"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('single-tour-package.show', $package->id) }}">
                                                                <i class="ri-eye-line me-2"></i>View Details
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('single-tour-package.edit', $package->id) }}">
                                                                <i class="ri-edit-line me-2"></i>Edit Package
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <form action="{{ route('single-tour-package.destroy', $package->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this package?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <i class="ri-delete-bin-line me-2"></i>Delete Package
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-4">
                                {{ $packages->links() }}
                            </div>
                        @else
                            <!-- Empty State -->
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <i class="ri-map-pin-line text-muted" style="font-size: 4rem;"></i>
                                </div>
                                <h5 class="text-muted mb-3">No tour packages found</h5>
                                <p class="text-muted mb-4">Start creating personalized tour experiences for your clients.</p>
                                <a href="{{ route('single-tour-package.create') }}" class="btn btn-primary">
                                    <i class="ri-add-line me-2"></i>Create Your First Package
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('styles')
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.table th {
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
}

.table td {
    padding: 1rem 0.75rem;
}

.card {
    transition: all 0.3s ease;
}

.table-hover tbody tr:hover {
    background-color: rgba(99, 102, 241, 0.05);
}

.dropdown-item:hover {
    background-color: rgba(99, 102, 241, 0.1);
    color: #667eea;
}

.badge {
    font-size: 0.75rem;
    font-weight: 500;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.card {
    animation: fadeIn 0.6s ease-out;
}
</style>
@endsection 