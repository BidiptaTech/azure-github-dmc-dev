@extends('layouts.layout')

@section('title', 'Zone List')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Zone /</span> Zone List
    </h4>

    <!-- Display flash message -->
    @if(session('success'))
     <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script>
        // Wait until DOM is loaded
        document.addEventListener('DOMContentLoaded', function () {
            // Automatically hide the alert after 5 seconds
            setTimeout(function () {
                var alert = document.getElementById('success-alert');
                if (alert) {
                    // Use Bootstrap's alert close method if available
                    var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }
            }, 3000);
        });
    </script>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Zones</h5>
            <a href="{{ route('zones.create') }}" class="btn btn-primary">Add New Zone</a>
        </div>
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Zone Name</th>
                            <th>Zone Type</th>
                            <th>City</th>
                            <th>Status</th>
                            <th>Zone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($zones as $key => $zone)
                        <tr>
                            <td>{{ ++$key }}</td>
                            <td style="max-width: 200px;">
                                <div style="word-wrap: break-word; white-space: normal;">
                                    {{ $zone->zone_name }}
                                </div>
                            </td>
                            <td>{{ $zone->zone_type }}</td>
                            <td>{{ $zone->cities->name }}</td>
                            <td>
                                <span class="badge bg-{{ $zone->status == 1 ? 'success' : 'danger' }}">
                                    {{ $zone->status == 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <!-- Settings Icon - Opens Checkbox Modal -->
                                <button type="button" 
                                        class="btn btn-{{ $zone->zone_type == 'Hotel' ? 'success' : ($zone->zone_type == 'Attraction' ? 'info' : ($zone->zone_type == 'Restaurant' ? 'warning' : 'secondary')) }} btn-sm rounded-pill d-flex justify-content-center align-items-center shadow-sm hover-lift"
                                        style="width: 36px; height: 36px; padding: 0; transition: all 0.2s ease;" title="{{ $zone->zone_type }} Settings"
                                        data-bs-toggle="modal" data-bs-target="#checkboxModal-{{ $zone->zone_id }}">
                                    @if($zone->zone_type == 'Hotel')
                                        <i class="ri-hotel-line" style="font-size: 18px;"></i>
                                    @elseif($zone->zone_type == 'Restaurant')
                                        <i class="ri-restaurant-2-line" style="font-size: 18px;"></i>
                                    @elseif($zone->zone_type == 'Attraction')
                                        <i class="ri-landscape-line" style="font-size: 18px;"></i>
                                    @else
                                        <i class="ri-settings-line" style="font-size: 18px;"></i>
                                    @endif
                                </button>
                                <!-- Checkbox Modal -->
                                <div class="modal fade" id="checkboxModal-{{ $zone->zone_id }}" tabindex="-1" aria-labelledby="checkboxModalLabel-{{ $zone->zone_id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" style="max-width: 600px; width: 95%;">
                                        <div class="modal-content border-0 shadow-lg" style="border-radius: 10px; overflow: hidden;">
                                            <div class="modal-header bg-primary p-4 position-relative" style="background: linear-gradient(135deg, #6f42c1, #007bff) !important;">
                                                <div class="d-flex align-items-center">
                                                    <div class="modal-icon me-3 bg-white text-primary d-flex justify-content-center align-items-center rounded-circle" 
                                                         style="width: 42px; height: 42px; box-shadow: 0 3px 8px rgba(0,0,0,0.2);">
                                                        @if($zone->zone_type == 'Hotel')
                                                            <i class="ri-hotel-line" style="font-size: 20px;"></i>
                                                        @elseif($zone->zone_type == 'Restaurant')
                                                            <i class="ri-restaurant-2-line" style="font-size: 20px;"></i>
                                                        @elseif($zone->zone_type == 'Attraction')
                                                            <i class="ri-landscape-line" style="font-size: 20px;"></i>
                                                        @else
                                                            <i class="ri-settings-line" style="font-size: 20px;"></i>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <h5 class="modal-title fw-bold text-white mb-1" id="checkboxModalLabel-{{ $zone->zone_id }}" style="word-wrap: break-word; max-width: 400px;">
                                                            {{ $zone->zone_name }}
                                                        </h5>
                                                        <p class="text-white-50 mb-0 small">
                                                            <i class="ri-map-pin-line me-1"></i> {{ $zone->cities->name }} - {{ $zone->zone_type }} Zone
                                                        </p>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" 
                                                        style="position: absolute; top: 15px; right: 15px;"></button>
                                            </div>
                                            <form action="{{ route('zones.settings', $zone->zone_id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body p-4" style="height: 60vh; overflow-y: auto;">
                                                    <div class="modal-body-content">
                                                        @if($zone->zone_type == 'Hotel')
                                                        <div class="mb-4">
                                                            <div class="section-header d-flex justify-content-between align-items-center mb-3">
                                                                <h6 class="fw-bold mb-0 text-success">Select Hotels for this Zone</h6>
                                                                <span class="badge bg-light text-success border border-success-subtle">
                                                                    <i class="ri-hotel-line me-1"></i> Hotels
                                                                </span>
                                                            </div>
                                                            <hr class="my-2 border-success-subtle">
                                                            <div class="row g-3 mt-3">
                                                                @php
                                                                    $user = auth()->user();
                                                                    $activeHotels = $hotels->filter(function ($hotel) use ($user) {
                                                                        return $hotel->status == 1 && in_array($user->userId, (array) $hotel->dmc_id);
                                                                    });
                                                                @endphp
                                                                @foreach($activeHotels as $hotel)
                                                                    @php
                                                                        $currentZoneForThisDmc = $hotel->getZoneForDmc($user->userId);
                                                                        $isAvailable = is_null($currentZoneForThisDmc) || $currentZoneForThisDmc == $zone->zone_id;
                                                                    @endphp
                                                                    @if($isAvailable)
                                                                    <div class="col-md-6 mb-2">
                                                                        <div class="form-check custom-checkbox">
                                                                            <input class="form-check-input border-success" type="checkbox" name="hotels[]" 
                                                                                value="{{ $hotel->hotel_unique_id }}" id="hotel-{{ $hotel->id }}-{{ $zone->zone_id }}"
                                                                                {{ $currentZoneForThisDmc == $zone->zone_id ? 'checked' : '' }}>
                                                                            <label class="form-check-label text-truncate ms-1" for="hotel-{{ $hotel->hotel_unique_id }}-{{ $zone->zone_id }}" 
                                                                                style="max-width: 100%; overflow: hidden; white-space: nowrap;" 
                                                                                title="{{ $hotel->name }}">
                                                                                {{ $hotel->name }}
                                                                                @if($currentZoneForThisDmc && $currentZoneForThisDmc == $zone->zone_id)
                                                                                    <span class="badge bg-success ms-1">Assigned</span>
                                                                                @endif
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    @endif
                                                                @endforeach
                                                                
                                                                @php
                                                                    $availableHotels = $activeHotels->filter(function($h) use ($zone, $user) { 
                                                                        $currentZone = $h->getZoneForDmc($user->userId);
                                                                        return is_null($currentZone) || $currentZone == $zone->zone_id; 
                                                                    });
                                                                @endphp
                                                                @if($availableHotels->count() == 0)
                                                                    <div class="col-12">
                                                                        <div class="alert bg-success-subtle border border-success-subtle text-success rounded-3 d-flex align-items-center" role="alert">
                                                                            <i class="ri-information-line me-2 fs-5"></i>
                                                                            <span>No active hotels available to assign to this zone.</span>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        
                                                        @elseif($zone->zone_type == 'Attraction')
                                                        <div class="mb-4">
                                                            <div class="section-header d-flex justify-content-between align-items-center mb-3">
                                                                <h6 class="fw-bold mb-0 text-info">Select Attractions for this Zone</h6>
                                                                <span class="badge bg-light text-info border border-info-subtle">
                                                                    <i class="ri-landscape-line me-1"></i> Attractions
                                                                </span>
                                                            </div>
                                                            <hr class="my-2">
                                                            <div class="row g-3 mt-3">
                                                                @php
                                                                    $user = auth()->user();
                                                                    $activeAttractions = $attractions->filter(function ($attraction) use ($user) {
                                                                        return $attraction->status == 1 && in_array($user->userId, (array) $attraction->dmc_id);
                                                                    });
                                                                @endphp
                                                                @foreach($activeAttractions as $attraction)
                                                                    @php
                                                                        $currentZoneForThisDmc = $attraction->getZoneForDmc($user->userId);
                                                                        $isAvailable = is_null($currentZoneForThisDmc) || $currentZoneForThisDmc == $zone->zone_id;
                                                                    @endphp
                                                                    @if($isAvailable)
                                                                    <div class="col-md-6 mb-2">
                                                                        <div class="form-check custom-checkbox">
                                                                            <input class="form-check-input border-info" type="checkbox" name="attractions[]" 
                                                                                value="{{ $attraction->attraction_id }}" id="attraction-{{ $attraction->id }}-{{ $zone->zone_id }}"
                                                                                {{ $currentZoneForThisDmc == $zone->zone_id ? 'checked' : '' }}>
                                                                            <label class="form-check-label text-truncate ms-1" for="attraction-{{ $attraction->attraction_id }}-{{ $zone->zone_id }}"
                                                                                style="max-width: 100%; overflow: hidden; white-space: nowrap;" 
                                                                                title="{{ $attraction->name }}">
                                                                                {{ $attraction->name }}
                                                                                @if($currentZoneForThisDmc && $currentZoneForThisDmc == $zone->zone_id)
                                                                                    <span class="badge bg-info ms-1">Assigned</span>
                                                                                @endif
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    @endif
                                                                @endforeach
                                                                
                                                                @php
                                                                    $availableAttractions = $activeAttractions->filter(function($a) use ($zone, $user) { 
                                                                        $currentZone = $a->getZoneForDmc($user->userId);
                                                                        return is_null($currentZone) || $currentZone == $zone->zone_id; 
                                                                    });
                                                                @endphp
                                                                @if($availableAttractions->count() == 0)
                                                                    <div class="col-12">
                                                                        <div class="alert alert-light border border-info-subtle text-info rounded-3 d-flex align-items-center" role="alert">
                                                                            <i class="ri-information-line me-2 fs-5"></i>
                                                                            <span>No active attractions available to assign to this zone.</span>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        
                                                        @elseif($zone->zone_type == 'Restaurant')
                                                        <div class="mb-4">
                                                            <div class="section-header d-flex justify-content-between align-items-center mb-3">
                                                                <h6 class="fw-bold mb-0 text-warning">Select Restaurants for this Zone</h6>
                                                                <span class="badge bg-light text-warning border border-warning-subtle">
                                                                    <i class="ri-restaurant-2-line me-1"></i> Restaurants
                                                                </span>
                                                            </div>
                                                            <hr class="my-2">
                                                            <div class="row g-3 mt-3">
                                                                @php
                                                                    $user = auth()->user();
                                                                    $activeRestaurants = $restaurants->filter(function ($restaurant) use ($user) {
                                                                        return $restaurant->status == 1 && in_array($user->userId, (array) $restaurant->dmc_id);
                                                                    });
                                                                @endphp
                                                                @foreach($activeRestaurants as $restaurant)
                                                                    @php
                                                                        $currentZoneForThisDmc = $restaurant->getZoneForDmc($user->userId);
                                                                        $isAvailable = is_null($currentZoneForThisDmc) || $currentZoneForThisDmc == $zone->zone_id;
                                                                    @endphp
                                                                    @if($isAvailable)
                                                                    <div class="col-md-6 mb-2">
                                                                        <div class="form-check custom-checkbox">
                                                                            <input class="form-check-input border-warning" type="checkbox" name="restaurants[]" 
                                                                                value="{{ $restaurant->restaurant_id }}" id="restaurant-{{ $restaurant->id }}-{{ $zone->zone_id }}"
                                                                                {{ $currentZoneForThisDmc == $zone->zone_id ? 'checked' : '' }}>
                                                                            <label class="form-check-label text-truncate ms-1" for="restaurant-{{ $restaurant->restaurant_id }}-{{ $zone->zone_id }}"
                                                                                style="max-width: 100%; overflow: hidden; white-space: nowrap;" 
                                                                                title="{{ $restaurant->name }}">
                                                                                {{ $restaurant->name }}
                                                                                @if($currentZoneForThisDmc && $currentZoneForThisDmc == $zone->zone_id)
                                                                                    <span class="badge bg-warning ms-1">Assigned</span>
                                                                                @endif
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                    @endif
                                                                @endforeach
                                                                
                                                                @php
                                                                    $availableRestaurants = $activeRestaurants->filter(function($r) use ($zone, $user) { 
                                                                        $currentZone = $r->getZoneForDmc($user->userId);
                                                                        return is_null($currentZone) || $currentZone == $zone->zone_id; 
                                                                    });
                                                                @endphp
                                                                @if($availableRestaurants->count() == 0)
                                                                    <div class="col-12">
                                                                        <div class="alert alert-light border border-warning-subtle text-warning rounded-3 d-flex align-items-center" role="alert">
                                                                            <i class="ri-information-line me-2 fs-5"></i>
                                                                            <span>No active restaurants available for your DMC to assign to this zone.</span>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        
                                                        @else
                                                        <div class="alert alert-light border border-primary-subtle rounded-3 p-3 d-flex" role="alert">
                                                            <i class="ri-information-line me-3 fs-3 text-primary"></i>
                                                            <div>
                                                                <h6 class="alert-heading mb-1 fw-bold">Information</h6>
                                                                <p class="mb-0">This zone type ({{ $zone->zone_type }}) doesn't support assigning entities.</p>
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top d-flex justify-content-between py-3 px-4 bg-light" style="position: sticky; bottom: 0;">
                                                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                                                        <i class="ri-close-line me-1"></i> Close
                                                    </button>
                                                    <button type="submit" class="btn btn-primary px-4">
                                                        <i class="ri-save-line me-1"></i> Save Changes
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <!-- View -->
                                    <a href="{{ route('zones.show', $zone->zone_id) }}" 
                                    class="btn btn-info btn-sm rounded-circle d-flex justify-content-center align-items-center"
                                    style="width: 28px; height: 28px; padding: 0;" title="View">
                                        <i class="ri-eye-line" style="font-size: 16px;"></i>
                                    </a>

                                    <!-- Edit -->
                                    <a href="{{ route('zones.edit', $zone->zone_id) }}" 
                                    class="btn btn-primary btn-sm rounded-circle d-flex justify-content-center align-items-center"
                                    style="width: 28px; height: 28px; padding: 0;" title="Edit">
                                        <i class="ri-pencil-line" style="font-size: 16px;"></i>
                                    </a>
                                    <!-- Delete -->
                                                                            <form action="{{ route('zones.destroy', $zone->zone_id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-danger btn-sm rounded-circle d-flex justify-content-center align-items-center"
                                                style="width: 28px; height: 28px; padding: 0;" title="Delete"
                                                onclick="return confirm('Are you sure you want to delete this port?')">
                                            <i class="ri-delete-bin-line" style="font-size: 16px;"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">No zones found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all modals
    const modalTriggers = document.querySelectorAll('[data-bs-toggle="modal"]');
    
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-bs-target');
            const targetModal = document.querySelector(targetId);
            
            if (targetModal) {
                const modal = new bootstrap.Modal(targetModal);
                modal.show();
            } else {
                console.error('Modal not found:', targetId);
            }
        });
    });
});
</script>
@endpush 