@extends('layouts.layout')

@section('title', $package->title)

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Enhanced Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <span class="text-muted fw-light">Packages /</span> {{ $package->title }}
                </h4>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-{{ $package->status == '1' ? 'success' : ($package->status == '0' ? 'warning' : 'secondary') }} fs-6">
                        {{ $package->status == '1' ? 'Active' : ($package->status == '0' ? 'Draft' : 'Inactive') }}
                    </span>
                    @if($package->is_featured)
                        <span class="badge bg-danger fs-6">Featured</span>
                    @endif
                    <small class="text-muted">Created {{ $package->created_at->diffForHumans() }}</small>
                </div>
            </div>
            <div>
                <a href="{{ route('packages.index') }}" class="btn btn-outline-secondary me-2">
                    <i class="ri-arrow-left-line me-1"></i>Back to Packages
                </a>
                <a href="{{ route('packages.edit', $package->package_id) }}" class="btn btn-primary">
                    <i class="ri-edit-line me-1"></i>Edit Package
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Enhanced Package Images -->
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
                                    <img src="{{ $image }}" class="img-fluid rounded gallery-image" 
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

                <!-- Enhanced Itinerary Display -->
                @php
                    // Parse itinerary data safely
                    $itineraryData = [];
                    if (!empty($package->itinerary)) {
                        if (is_string($package->itinerary)) {
                            $itineraryData = json_decode($package->itinerary, true) ?: [];
                        } else if (is_array($package->itinerary)) {
                            $itineraryData = $package->itinerary;
                        }
                    }
                    
                    // Extract key information
                    $attractions = [];
                    $guides = [];
                    $hotels = [];
                    $hasArrivalPickup = false;
                    $hasDepartureService = false;
                    
                    // Process itinerary data if available
                    if(!empty($itineraryData)) {
                        // Check if we have itinerary key (from the JSON structure)
                        if(isset($itineraryData['itinerary']) && is_array($itineraryData['itinerary'])) {
                            foreach($itineraryData['itinerary'] as $day) {
                                // Collect attractions - more efficient using associative array
                                if(isset($day['attractions']) && is_array($day['attractions'])) {
                                    foreach($day['attractions'] as $attraction) {
                                        if(is_array($attraction) && isset($attraction['attraction_id'])) {
                                            // Use attraction_id as key for efficient deduplication
                                            $attractions[$attraction['attraction_id']] = $attraction;
                                        }
                                    }
                                }
                                
                                // Collect guides - more efficient using associative array
                                if(isset($day['guide']) && !empty($day['guide']) && is_array($day['guide'])) {
                                    $guideId = $day['guide']['id'] ?? null;
                                    if($guideId) {
                                        $guides[$guideId] = $day['guide'];
                                    }
                                }
                                
                                // Check for arrival/departure services
                                if(isset($day['arrival_pickup']) && $day['arrival_pickup'] == 1) {
                                    $hasArrivalPickup = true;
                                }
                                
                                if(isset($day['departure_service']) && $day['departure_service'] == 1) {
                                    $hasDepartureService = true;
                                }
                            }
                        }
                        
                        // Process hotels data if available
                        if(isset($itineraryData['hotels']) && is_array($itineraryData['hotels'])) {
                            foreach($itineraryData['hotels'] as $hotel) {
                                if(is_array($hotel) && isset($hotel['id'])) {
                                    $hotels[$hotel['id']] = $hotel;
                                }
                            }
                        }
                        
                        // Create a mapping of days to hotels for easy lookup
                        $dayToHotels = [];
                        if(isset($itineraryData['hotels']) && is_array($itineraryData['hotels'])) {
                            foreach($itineraryData['hotels'] as $hotel) {
                                if(is_array($hotel) && isset($hotel['days']) && is_array($hotel['days'])) {
                                    foreach($hotel['days'] as $day) {
                                        if(!isset($dayToHotels[$day])) {
                                            $dayToHotels[$day] = [];
                                        }
                                        $dayToHotels[$day][] = $hotel;
                                    }
                                }
                            }
                        }
                    }
                    
                    // Convert associative arrays to indexed arrays for display
                    $attractions = array_values($attractions);
                @endphp

                <!-- Itinerary Highlights -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-route-line me-2"></i>Itinerary Highlights
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Attractions Preview -->
                        @if(count($attractions) > 0)
                            <div class="mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="flex-shrink-0">
                                        <span class="badge bg-primary-subtle text-primary rounded-circle p-2">
                                            <i class="ri-map-pin-line"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 ms-2">
                                        <h6 class="mb-0 fw-semibold">Top Attractions</h6>
                                    </div>
                                </div>
                                <div class="attraction-images d-flex gap-2 overflow-hidden">
                                    @foreach(array_slice($attractions, 0, 4) as $index => $attraction)
                                        <div class="position-relative" style="width: 80px; height: 80px;">
                                            @if(isset($attraction['image']) && $attraction['image'])
                                                <img src="{{ $attraction['image'] }}" 
                                                     alt="{{ $attraction['name'] }}"
                                                     class="rounded" 
                                                     style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                     style="width: 100%; height: 100%;">
                                                    <i class="ri-image-line text-muted"></i>
                                                </div>
                                            @endif
                                            <div class="position-absolute bottom-0 start-0 w-100 p-1" 
                                                 style="background: rgba(0,0,0,0.7); font-size: 9px; line-height: 1.2; color: white;">
                                                {{ \Illuminate\Support\Str::limit($attraction['name'], 12) }}
                                            </div>
                                        </div>
                                    @endforeach
                                    @if(count($attractions) > 4)
                                        <div class="position-relative d-flex align-items-center justify-content-center bg-light rounded" 
                                             style="width: 80px; height: 80px;">
                                            <span class="text-primary fw-bold">+{{ count($attractions) - 4 }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Hotels Preview -->
                        @if(count($hotels) > 0)
                            <div class="mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="flex-shrink-0">
                                        <span class="badge bg-success-subtle text-success rounded-circle p-2">
                                            <i class="ri-hotel-line"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1 ms-2">
                                        <h6 class="mb-0 fw-semibold">Accommodations</h6>
                                    </div>
                                </div>
                                <div class="hotel-preview">
                                    @foreach(array_slice($hotels, 0, 3) as $hotel)
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="flex-shrink-0" style="width: 40px; height: 40px;">
                                                @if(isset($hotel['main_image']) && $hotel['main_image'])
                                                    <img src="{{ $hotel['main_image'] }}" 
                                                         class="rounded-circle" 
                                                         style="width: 100%; height: 100%; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" 
                                                         style="width: 100%; height: 100%;">
                                                        <i class="ri-building-line text-muted small"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <div class="text-truncate fw-medium">{{ $hotel['name'] }}</div>
                                                <div class="text-muted small">{{ $hotel['city'] }}</div>
                                                @if(isset($hotel['days']) && is_array($hotel['days']) && count($hotel['days']) > 0)
                                                    <div class="text-primary small">
                                                        <i class="ri-calendar-line me-1"></i>
                                                        Day{{ count($hotel['days']) > 1 ? 's' : '' }}: {{ implode(', ', $hotel['days']) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                    @if(count($hotels) > 3)
                                        <div class="small text-primary">+{{ count($hotels) - 3 }} more hotels</div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Services Preview -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <span class="badge bg-warning-subtle text-warning rounded-circle p-2">
                                        <i class="ri-service-line"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <h6 class="mb-0 fw-semibold">Services</h6>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                @if($hasArrivalPickup)
                                    <span class="badge bg-light text-dark">
                                        <i class="ri-flight-land-line me-1 text-success"></i>Airport Pickup
                                    </span>
                                @endif
                                
                                @if($hasDepartureService)
                                    <span class="badge bg-light text-dark">
                                        <i class="ri-flight-takeoff-line me-1 text-danger"></i>Airport Dropoff
                                    </span>
                                @endif
                                
                                @if(count($guides) > 0)
                                    <span class="badge bg-light text-dark">
                                        <i class="ri-user-voice-line me-1 text-primary"></i>Tour Guide
                                    </span>
                                @endif
                                
                                @php
                                    $hasTransfers = false;
                                    foreach($attractions as $attraction) {
                                        if(isset($attraction['transfer_available']) && $attraction['transfer_available'] == 1) {
                                            $hasTransfers = true;
                                            break;
                                        }
                                    }
                                @endphp
                                
                                @if($hasTransfers)
                                    <span class="badge bg-light text-dark">
                                        <i class="ri-taxi-line me-1 text-info"></i>Transfer Services
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Day-wise Itinerary -->
                @if(!empty($itineraryData))
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-calendar-check-line me-2"></i>Detailed Itinerary
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @if(isset($itineraryData['itinerary']) && is_array($itineraryData['itinerary']))
                                @foreach($itineraryData['itinerary'] as $day)
                                    <div class="timeline-item mb-4">
                                        <div class="timeline-marker">
                                            <span class="badge bg-primary rounded-circle">{{ $day['day'] ?? $loop->iteration }}</span>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="card">
                                                <div class="card-header bg-light">
                                                    <h6 class="mb-0">
                                                        Day {{ $day['day'] ?? $loop->iteration }}
                                                        @if($day['day'] == 1 && isset($day['arrival_pickup']) && $day['arrival_pickup'] == 1)
                                                            <span class="badge bg-info ms-2">Arrival Day</span>
                                                        @endif
                                                        @if($day['day'] == $package->duration_days && isset($day['departure_service']) && $day['departure_service'] == 1)
                                                            <span class="badge bg-warning ms-2">Departure Day</span>
                                                        @endif
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    @if(isset($day['attractions']) && count($day['attractions']) > 0)
                                                        <div class="mb-3">
                                                            <h6 class="text-primary mb-2">
                                                                <i class="ri-map-pin-line me-1"></i>Attractions
                                                            </h6>
                                                            <div class="row g-2">
                                                                @foreach($day['attractions'] as $attraction)
                                                                    <div class="col-md-6">
                                                                        <div class="border rounded p-2">
                                                                            <div class="d-flex align-items-center">
                                                                                @if(isset($attraction['image']) && $attraction['image'])
                                                                                    <img src="{{ $attraction['image'] }}" 
                                                                                         class="rounded me-2" 
                                                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                                                @else
                                                                                    <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" 
                                                                                         style="width: 40px; height: 40px;">
                                                                                        <i class="ri-image-line text-muted"></i>
                                                                                    </div>
                                                                                @endif
                                                                                <div class="flex-grow-1">
                                                                                    <div class="fw-medium">{{ $attraction['name'] }}</div>
                                                                                    <small class="text-muted">{{ $attraction['location'] ?? $attraction['city'] }}</small>
                                                                                </div>
                                                                                @if(isset($attraction['transfer_available']) && $attraction['transfer_available'] == 1)
                                                                                    <span class="badge bg-success-subtle text-success">
                                                                                        <i class="ri-taxi-line me-1"></i>Transfer
                                                                                    </span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif

                                                                                                         @php
                                                         $currentDay = $day['day'] ?? $loop->iteration;
                                                         $dayHotels = $dayToHotels[$currentDay] ?? [];
                                                     @endphp
                                                     
                                                     @if(count($dayHotels) > 0)
                                                         <div class="mb-3">
                                                             <h6 class="text-success mb-2">
                                                                 <i class="ri-hotel-line me-1"></i>Accommodation
                                                             </h6>
                                                             <div class="row g-2">
                                                                 @foreach($dayHotels as $hotel)
                                                                     <div class="col-md-6">
                                                                         <div class="border rounded p-2">
                                                                             <div class="d-flex align-items-center">
                                                                                 @if(isset($hotel['main_image']) && $hotel['main_image'])
                                                                                     <img src="{{ $hotel['main_image'] }}" 
                                                                                          class="rounded me-2" 
                                                                                          style="width: 40px; height: 40px; object-fit: cover;">
                                                                                 @else
                                                                                     <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" 
                                                                                          style="width: 40px; height: 40px;">
                                                                                         <i class="ri-building-line text-muted"></i>
                                                                                     </div>
                                                                                 @endif
                                                                                 <div class="flex-grow-1">
                                                                                     <div class="fw-medium">{{ $hotel['name'] }}</div>
                                                                                     <small class="text-muted">{{ $hotel['city'] }}</small>
                                                                                     @if(isset($hotel['star_rating']))
                                                                                         <div class="text-warning small">
                                                                                             @for($i = 1; $i <= 5; $i++)
                                                                                                 <i class="ri-star-fill {{ $i <= $hotel['star_rating'] ? '' : 'text-muted' }}"></i>
                                                                                             @endfor
                                                                                         </div>
                                                                                     @endif
                                                                                 </div>
                                                                                 @if(isset($hotel['room_type']))
                                                                                     <span class="badge bg-info-subtle text-info small">
                                                                                         {{ $hotel['room_type'] }}
                                                                                     </span>
                                                                                 @endif
                                                                             </div>
                                                                         </div>
                                                                     </div>
                                                                 @endforeach
                                                             </div>
                                                         </div>
                                                     @endif

                                                    @if(isset($day['guide']) && !empty($day['guide']))
                                                        <div class="mb-3">
                                                            <h6 class="text-info mb-2">
                                                                <i class="ri-user-voice-line me-1"></i>Guide
                                                            </h6>
                                                            <div class="border rounded p-2">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-grow-1">
                                                                        <div class="fw-medium">{{ $day['guide']['name'] }}</div>
                                                                        @if(isset($day['guide']['languages']))
                                                                            <small class="text-muted">
                                                                                <i class="ri-translate-2 me-1"></i>
                                                                                {{ is_array($day['guide']['languages']) ? implode(', ', $day['guide']['languages']) : $day['guide']['languages'] }}
                                                                            </small>
                                                                        @endif
                                                                        @if(isset($day['guide']['contact_no']))
                                                                            <small class="text-muted d-block">
                                                                                <i class="ri-phone-line me-1"></i>{{ $day['guide']['contact_no'] }}
                                                                            </small>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
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

            <!-- Enhanced Sidebar -->
            <div class="col-lg-4">
                <!-- Package Summary -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-information-line me-2"></i>Package Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
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

                <!-- Enhanced Pricing -->
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

/* Timeline Styling */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #696cff, #e7f1ff);
}

.timeline-item {
    position: relative;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 20px;
    z-index: 2;
}

.timeline-content {
    margin-left: 20px;
}

/* Enhanced Card Styling */
.card {
    border: none;
    box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 12px 0 rgba(67, 89, 113, 0.16);
}

.card-header {
    background-color: transparent;
    border-bottom: 1px solid #d9dee3;
    padding: 1.5rem;
}

.card-body {
    padding: 1.5rem;
}

/* Badge Styling */
.badge.bg-primary-subtle {
    background-color: rgba(105, 108, 255, 0.18) !important;
    color: #5659cc !important;
}

.badge.bg-success-subtle {
    background-color: rgba(32, 201, 151, 0.18) !important;
    color: #18a47c !important;
}

.badge.bg-warning-subtle {
    background-color: rgba(253, 126, 20, 0.18) !important;
    color: #cc6510 !important;
}

.badge.bg-info-subtle {
    background-color: rgba(13, 202, 240, 0.18) !important;
    color: #0aa2c0 !important;
}

/* Responsive Design */
@media (max-width: 768px) {
    .timeline {
        padding-left: 20px;
    }
    
    .timeline::before {
        left: 10px;
    }
    
    .timeline-marker {
        left: -17px;
    }
    
    .attraction-images {
        flex-wrap: wrap;
    }
    
    .attraction-images > div {
        width: 60px !important;
        height: 60px !important;
    }
}

/* Animation for timeline */
.timeline-item {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Enhanced hover effects */
.timeline-content .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

/* Image overlay effects */
.position-relative img {
    transition: filter 0.3s ease;
}

.position-relative:hover img {
    filter: brightness(1.1);
}

/* Custom scrollbar for gallery */
.attraction-images::-webkit-scrollbar {
    height: 4px;
}

.attraction-images::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

.attraction-images::-webkit-scrollbar-thumb {
    background: #696cff;
    border-radius: 2px;
}

.attraction-images::-webkit-scrollbar-thumb:hover {
    background: #5659cc;
}
</style>
@endsection 