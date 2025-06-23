@extends('layouts.layout')

@section('title', $package->title)

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <span class="text-muted fw-light">Packages /</span> {{ $package->title }}
                </h4>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-{{ $package->status == 'active' ? 'success' : ($package->status == 'draft' ? 'warning' : 'secondary') }}">
                        {{ ucfirst($package->status) }}
                    </span>
                    @if($package->is_featured)
                        <span class="badge bg-danger">Featured</span>
                    @endif
                    <small class="text-muted">Created {{ $package->created_at->diffForHumans() }}</small>
                </div>
            </div>
            <div>
                <a href="{{ route('packages.index') }}" class="btn btn-outline-secondary me-2">
                    <i class="ri-arrow-left-line me-1"></i>Back to Packages
                </a>
                <a href="{{ route('packages.edit', $package->id) }}" class="btn btn-primary">
                    <i class="ri-edit-line me-1"></i>Edit Package
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Package Images -->
                <div class="card mb-4">
                    <div class="card-body p-0">
                        <div class="position-relative">
                            @if($package->main_image)
                            <img src="{{ $package->main_image }}" 
                                 class="img-fluid w-100" alt="{{ $package->title }}" 
                                 style="height: 400px; object-fit: cover;">
                            @else
                            <div class="bg-light text-center py-5">
                                <i class="ri-image-line display-4 text-muted"></i>
                                <p class="text-muted mb-0">No main image available</p>
                            </div>
                            @endif
                            <div class="position-absolute top-0 start-0 p-3">
                                <span class="badge bg-primary fs-6">{{ $package->category }}</span>
                            </div>
                            <div class="position-absolute top-0 end-0 p-3">
                                <span class="badge bg-success fs-6">{{ $package->duration_days }} Days</span>
                            </div>
                        </div>
                        
                        @if($package->gallery_images && count($package->gallery_images) > 0)
                        <div class="p-3">
                            <h6 class="mb-3">Gallery Images</h6>
                            <div class="row g-2">
                                @foreach($package->gallery_images as $image)
                                <div class="col-md-3 col-6">
                                    <img src="{{ $image }}" class="img-fluid rounded" 
                                         style="height: 120px; width: 100%; object-fit: cover; cursor: pointer;"
                                         onclick="showImageModal('{{ $image }}')">
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Package Description -->
                @if($package->description)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-file-text-line me-2"></i>Description
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $package->description }}</p>
                    </div>
                </div>
                @endif

                <!-- Selected Hotels -->
                @if($package->selected_hotels && count($package->selected_hotels) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-hotel-line me-2"></i>Selected Hotels ({{ count($package->selected_hotels) }})
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($package->selected_hotels as $hotel)
                            @php
                                $hotelData = is_string($hotel) ? json_decode($hotel, true) : $hotel;
                                $hotelData = is_array($hotelData) ? $hotelData : [];
                                
                                $hotelId = $hotelData['id'] ?? '';
                                $hotelName = $hotelData['name'] ?? $hotelId ?? 'Unnamed Hotel';
                                $hotelCity = $hotelData['city'] ?? '';
                            @endphp
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-1">{{ $hotelName }}</h6>
                                        <div class="text-warning">
                                            @for($i = 1; $i <= 5; $i++)
                                                ★
                                            @endfor
                                        </div>
                                    </div>
                                    @if($hotelCity)
                                    <p class="text-muted mb-0">
                                        <i class="ri-map-pin-line me-1"></i>{{ $hotelCity }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Selected Attractions -->
                @if($package->selected_attractions && count($package->selected_attractions) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-map-pin-line me-2"></i>Selected Attractions ({{ count($package->selected_attractions) }})
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($package->selected_attractions as $attraction)
                            @php
                                $attractionData = is_string($attraction) ? json_decode($attraction, true) : $attraction;
                                $attractionData = is_array($attractionData) ? $attractionData : [];
                                
                                $attractionId = $attractionData['id'] ?? '';
                                $attractionName = $attractionData['name'] ?? $attractionId ?? 'Unnamed Attraction';
                                $attractionCity = $attractionData['city'] ?? '';
                            @endphp
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="mb-1">{{ $attractionName }}</h6>
                                    @if($attractionCity)
                                    <p class="text-muted mb-0">
                                        <i class="ri-map-pin-line me-1"></i>{{ $attractionCity }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Selected Guide -->
                @if($package->selected_guide && (is_array($package->selected_guide) && count($package->selected_guide) > 0 || !is_array($package->selected_guide)))
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-user-line me-2"></i>Selected Guide
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @php
                                $guides = is_array($package->selected_guide) ? $package->selected_guide : [$package->selected_guide];
                            @endphp
                            
                            @foreach($guides as $guide)
                            @php
                                $guideData = is_string($guide) ? json_decode($guide, true) : $guide;
                                $guideData = is_array($guideData) ? $guideData : [];
                                
                                $guideId = $guideData['id'] ?? '';
                                $guideName = $guideData['name'] ?? $guideId ?? 'Unnamed Guide';
                                $guideLanguages = $guideData['languages'] ?? [];
                                $guideContact = $guideData['contact_no'] ?? '';
                                
                                // Process languages
                                $languageList = '';
                                if (is_array($guideLanguages)) {
                                    $languageNames = [];
                                    foreach ($guideLanguages as $lang) {
                                        if (is_array($lang) || is_object($lang)) {
                                            $languageNames[] = $lang->language ?? $lang['language'] ?? '';
                                        } else {
                                            $languageNames[] = $lang;
                                        }
                                    }
                                    $languageList = implode(', ', array_filter($languageNames));
                                } elseif (is_string($guideLanguages)) {
                                    $languageList = $guideLanguages;
                                }
                            @endphp
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="mb-1">{{ $guideName }}</h6>
                                    @if($languageList)
                                    <p class="text-muted mb-1">
                                        <i class="ri-translate-2 me-1"></i>{{ $languageList }}
                                    </p>
                                    @endif
                                    @if($guideContact)
                                    <p class="text-muted mb-0">
                                        <i class="ri-phone-line me-1"></i>{{ $guideContact }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Selected Restaurants -->
                @if($package->selected_restaurants && count($package->selected_restaurants) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-restaurant-line me-2"></i>Selected Restaurants ({{ count($package->selected_restaurants) }})
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($package->selected_restaurants as $restaurant)
                            @php
                                $restaurantData = is_string($restaurant) ? json_decode($restaurant, true) : $restaurant;
                                $restaurantData = is_array($restaurantData) ? $restaurantData : [];
                                
                                $restaurantId = $restaurantData['id'] ?? '';
                                $restaurantName = $restaurantData['name'] ?? $restaurantId ?? 'Unnamed Restaurant';
                                $restaurantCuisine = $restaurantData['cuisine'] ?? '';
                                $restaurantCity = $restaurantData['city'] ?? '';
                            @endphp
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="mb-1">{{ $restaurantName }}</h6>
                                    @if($restaurantCuisine)
                                    <p class="text-muted mb-1">
                                        <i class="ri-restaurant-2-line me-1"></i>{{ $restaurantCuisine }}
                                    </p>
                                    @endif
                                    @if($restaurantCity)
                                    <p class="text-muted mb-0">
                                        <i class="ri-map-pin-line me-1"></i>{{ $restaurantCity }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Inclusions & Exclusions -->
                @if($package->inclusions || $package->exclusions)
                <div class="row">
                    @if($package->inclusions)
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0 text-success">
                                    <i class="ri-check-line me-2"></i>Inclusions
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="text-success">
                                    {!! nl2br(e($package->inclusions)) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($package->exclusions)
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0 text-danger">
                                    <i class="ri-close-line me-2"></i>Exclusions
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="text-danger">
                                    {!! nl2br(e($package->exclusions)) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Terms & Conditions -->
                @if($package->terms_conditions)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-file-list-line me-2"></i>Terms & Conditions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-muted">
                            {!! nl2br(e($package->terms_conditions)) !!}
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Package Summary -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-information-line me-2"></i>Package Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-center">
                                    <div class="h4 text-primary mb-1">{{ $package->duration_days }}</div>
                                    <small class="text-muted">Days</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <div class="h4 text-info mb-1">{{ $package->max_pax }}</div>
                                    <small class="text-muted">Max PAX</small>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Destination</label>
                            <p class="mb-0">{{ $package->destination }}</p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Category</label>
                            <p class="mb-0">{{ $package->category }}</p>
                        </div>

                        @if($package->start_date && $package->expire_date)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Validity Period</label>
                            <p class="mb-0">
                                {{ \Carbon\Carbon::parse($package->start_date)->format('M d, Y') }} - 
                                {{ \Carbon\Carbon::parse($package->expire_date)->format('M d, Y') }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Pricing -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-money-dollar-circle-line me-2"></i>Pricing
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Adult Price:</span>
                                <span class="h5 text-primary mb-0">SGD {{ number_format($package->price_adult, 2) }}</span>
                            </div>
                        </div>
                        
                        @if($package->price_senior)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Senior Price:</span>
                                <span class="h6 text-info mb-0">SGD {{ number_format($package->price_senior, 2) }}</span>
                            </div>
                        </div>
                        @endif
                        
                        @if($package->price_child)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Child Price:</span>
                                <span class="h6 text-success mb-0">SGD {{ number_format($package->price_child, 2) }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Package Meta -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-settings-line me-2"></i>Package Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted">Created:</small>
                            <div>{{ $package->created_at->format('M d, Y \a\t g:i A') }}</div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Last Updated:</small>
                            <div>{{ $package->updated_at->format('M d, Y \a\t g:i A') }}</div>
                        </div>
                        @if($package->creator)
                        <div class="mb-2">
                            <small class="text-muted">Created By:</small>
                            <div>{{ $package->creator->name ?? 'System' }}</div>
                        </div>
                        @endif
                        @if($package->updater && (!$package->creator || $package->updater->id !== $package->creator->id))
                        <div>
                            <small class="text-muted">Updated By:</small>
                            <div>{{ $package->updater->name ?? 'System' }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Package Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid" alt="Package Image">
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function showImageModal(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}
</script>
@endsection

@section('styles')
<style>
.card-img-top {
    transition: transform 0.3s ease;
}

.card-img-top:hover {
    transform: scale(1.02);
}

.badge {
    font-size: 0.875rem;
}

.border {
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.border:hover {
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.gallery-image {
    cursor: pointer;
    transition: transform 0.2s ease;
}

.gallery-image:hover {
    transform: scale(1.05);
}
</style>
@endsection 