@extends('layouts.layout')

@section('title', 'Default Values')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Default Values /</span> List
    </h4>

    <!-- Display flash messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                var alert = document.getElementById('success-alert');
                if (alert) {
                    var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }
            }, 3000);
        });
    </script>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                var alert = document.getElementById('error-alert');
                if (alert) {
                    var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }
            }, 3000);
        });
    </script>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Default Values Configuration</h5>
            @if(!empty($availableTypes))
            <a href="{{ route('default-values.create') }}" class="btn btn-primary">
                <i class="ri-add-line me-1"></i>Add Default Value
            </a>
            @endif
        </div>
        <div class="card-body">
            <div class="alert alert-info mb-4">
                <i class="ri-information-line me-2"></i>
                Configure default values for your services. Each DMC can set one default for each service type (Hotel, Restaurant, Attraction, Car Private, Car Shared, Port, Guide).
            </div>

            <div class="table-responsive text-nowrap">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Service Type</th>
                            <th>Service Name</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($defaultValues as $key => $defaultValue)
                        <tr>
                            <td>{{ ++$key }}</td>
                            <td>
                                @if($defaultValue->name == 'hotel')
                                    <span class="badge bg-success"><i class="ri-hotel-line me-1"></i>Hotel</span>
                                @elseif($defaultValue->name == 'restaurant')
                                    <span class="badge bg-warning"><i class="ri-restaurant-2-line me-1"></i>Restaurant</span>
                                @elseif($defaultValue->name == 'attraction')
                                    <span class="badge bg-info"><i class="ri-landscape-line me-1"></i>Attraction</span>
                                @elseif($defaultValue->name == 'car_private')
                                    <span class="badge bg-primary"><i class="ri-car-line me-1"></i>Car (Private)</span>
                                @elseif($defaultValue->name == 'car_shared')
                                    <span class="badge bg-secondary"><i class="ri-taxi-line me-1"></i>Car (Shared)</span>
                                @elseif($defaultValue->name == 'port')
                                    <span class="badge bg-dark"><i class="ri-ship-line me-1"></i>Port</span>
                                @elseif($defaultValue->name == 'guide')
                                    <span class="badge" style="background-color: #6f42c1; color: white;"><i class="ri-user-star-line me-1"></i>Guide</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $serviceName = 'N/A';
                                    if ($defaultValue->name == 'hotel' && $defaultValue->hotel) {
                                        $serviceName = $defaultValue->hotel->name ?? 'N/A';
                                    } elseif ($defaultValue->name == 'restaurant' && $defaultValue->restaurant) {
                                        $serviceName = $defaultValue->restaurant->name ?? 'N/A';
                                    } elseif ($defaultValue->name == 'attraction' && $defaultValue->attraction) {
                                        $serviceName = $defaultValue->attraction->name ?? 'N/A';
                                    } elseif (($defaultValue->name == 'car_private' || $defaultValue->name == 'car_shared') && $defaultValue->vehicle) {
                                        $serviceName = $defaultValue->vehicle->vehicle_name ?? 'N/A';
                                    } elseif ($defaultValue->name == 'port' && $defaultValue->port) {
                                        $serviceName = $defaultValue->port->port_name ?? 'N/A';
                                    } elseif ($defaultValue->name == 'guide' && $defaultValue->guide) {
                                        $serviceName = $defaultValue->guide->name ?? 'N/A';
                                    }
                                @endphp
                                <div style="max-width: 300px; word-wrap: break-word; white-space: normal;">
                                    {{ $serviceName }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $defaultValue->status == 1 ? 'success' : 'danger' }}">
                                    {{ $defaultValue->status == 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $defaultValue->updated_at->format('d M Y, h:i A') }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <!-- Edit -->
                                    <a href="{{ route('default-values.edit', Crypt::encrypt($defaultValue->id)) }}" 
                                       class="btn btn-primary btn-sm rounded-circle d-flex justify-content-center align-items-center"
                                       style="width: 28px; height: 28px; padding: 0;" title="Edit">
                                        <i class="ri-pencil-line" style="font-size: 16px;"></i>
                                    </a>
                                    
                                    <!-- Delete -->
                                    <form action="{{ route('default-values.destroy', Crypt::encrypt($defaultValue->id)) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-danger btn-sm rounded-circle d-flex justify-content-center align-items-center"
                                                style="width: 28px; height: 28px; padding: 0;" title="Delete"
                                                onclick="return confirm('Are you sure you want to delete this default value?')">
                                            <i class="ri-delete-bin-line" style="font-size: 16px;"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                <div class="py-4">
                                    <i class="ri-information-line" style="font-size: 48px; color: #ccc;"></i>
                                    <p class="mt-2 mb-0">No default values configured yet.</p>
                                    @if(!empty($availableTypes))
                                    <a href="{{ route('default-values.create') }}" class="btn btn-primary btn-sm mt-2">
                                        <i class="ri-add-line me-1"></i>Add Your First Default Value
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($defaultValues->count() > 0 && $defaultValues->count() < 7)
            <div class="mt-3">
                <div class="alert alert-secondary">
                    <strong>Available Types:</strong>
                    @foreach(['hotel' => 'Hotel', 'restaurant' => 'Restaurant', 'attraction' => 'Attraction', 'car_private' => 'Car (Private)', 'car_shared' => 'Car (Shared)', 'port' => 'Port', 'guide' => 'Guide'] as $key => $label)
                        @if(in_array($key, $availableTypes))
                            <span class="badge bg-light text-dark me-1">{{ $label }}</span>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

