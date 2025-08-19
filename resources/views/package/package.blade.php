@extends('layouts.layout')
@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">
                <i class="ri-gift-line me-2 text-primary"></i>Packages
            </h4>
            <a href="{{ route('packages.create') }}" class="btn btn-primary">
                <i class="ri-add-line me-1"></i>Create New Package
            </a>
        </div>

        <!-- Filter Section -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('packages.index') }}" method="GET" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Duration</label>
                            <select class="form-select" name="duration" onchange="this.form.submit()">
                                <option value="">All Days</option>
                                <option value="1-3" {{ request('duration') == '1-3' ? 'selected' : '' }}>1-3 Days</option>
                                <option value="4-6" {{ request('duration') == '4-6' ? 'selected' : '' }}>4-6 Days</option>
                                <option value="7-10" {{ request('duration') == '7-10' ? 'selected' : '' }}>7-10 Days</option>
                                <option value="10+" {{ request('duration') == '10+' ? 'selected' : '' }}>More than 10 Days</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Price Range</label>
                            <select class="form-select" name="price_range" onchange="this.form.submit()">
                                <option value="">All Prices</option>
                                <option value="0-100" {{ request('price_range') == '0-100' ? 'selected' : '' }}>$0 - $100</option>
                                <option value="101-300" {{ request('price_range') == '101-300' ? 'selected' : '' }}>$101 - $300</option>
                                <option value="301-500" {{ request('price_range') == '301-500' ? 'selected' : '' }}>$301 - $500</option>
                                <option value="501+" {{ request('price_range') == '501+' ? 'selected' : '' }}>$501+</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                <option value="Adventure" {{ request('category') == 'Adventure' ? 'selected' : '' }}>Adventure</option>
                                <option value="Cultural" {{ request('category') == 'Cultural' ? 'selected' : '' }}>Cultural</option>
                                <option value="City Tour" {{ request('category') == 'City Tour' ? 'selected' : '' }}>City Tour</option>
                                <option value="Beach" {{ request('category') == 'Beach' ? 'selected' : '' }}>Beach</option>
                                <option value="Heritage" {{ request('category') == 'Heritage' ? 'selected' : '' }}>Heritage</option>
                                <option value="Food & Culinary" {{ request('category') == 'Food & Culinary' ? 'selected' : '' }}>Food & Culinary</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sort By</label>
                            <select class="form-select" name="sort" onchange="this.form.submit()">
                                <option value="">Default</option>
                                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                                <option value="duration_low" {{ request('sort') == 'duration_low' ? 'selected' : '' }}>Duration: Short to Long</option>
                                <option value="duration_high" {{ request('sort') == 'duration_high' ? 'selected' : '' }}>Duration: Long to Short</option>
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3 text-end">
                        <a href="{{ route('packages.index') }}" class="btn btn-outline-secondary">
                            <i class="ri-refresh-line me-1"></i>Reset Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Packages Grid -->
        <div class="row g-4">
            @foreach($packages as $package)
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="position-relative">
                        @if($package->main_image)
                            <img src="{{ $package->main_image }}" class="card-img-top" alt="{{ $package->title }}" 
                                 style="height: 200px; object-fit: cover;">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="ri-image-line display-4 text-muted"></i>
                            </div>
                        @endif
                        <div class="position-absolute top-0 end-0 p-3">
                            <span class="badge bg-primary">{{ $package->duration_days }} Days</span>
                        </div>
                        <div class="position-absolute bottom-0 start-0 p-3 w-100" style="background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);">
                            <h5 class="card-title mb-0 text-white">{{ $package->title }}</h5>
                            <div class="text-white-50">
                                <i class="ri-map-pin-line me-1"></i>{{ $package->destination }} - {{ $package->city }}
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="text-primary">
                                <div class="small">From SGD</div>
                                <div class="h4 mb-0">${{ number_format($package->price_adult, 2) }}</div>
                            </div>
                            <span class="badge bg-{{ $package->status == '1' ? 'success' : ($package->status == '0' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($package->status == '1' ? 'Active' : ($package->status == '0' ? 'Draft' : 'Inactive')) }}
                            </span>
                        </div>
                        
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
                            }
                            
                            // Convert associative arrays to indexed arrays for display
                            $attractions = array_values($attractions);
                        @endphp

                        <!-- Itinerary Highlights -->
                        <div class="itinerary-highlights">
                            <!-- Attractions Preview -->
                            @if(count($attractions) > 0)
                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="flex-shrink-0">
                                            <span class="badge bg-primary-subtle text-primary rounded-circle p-2">
                                                <i class="ri-map-pin-line"></i>
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <h6 class="mb-0 fw-semibold">Top Attractions</h6>
                                        </div>
                                    </div>
                                    <div class="attraction-images d-flex gap-1 overflow-hidden">
                                        @foreach(array_slice($attractions, 0, 3) as $index => $attraction)
                                            <div class="position-relative" style="width: 60px; height: 60px;">
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
                                                     style="background: rgba(0,0,0,0.5); font-size: 8px; line-height: 1.2; color: white;">
                                                    {{ \Illuminate\Support\Str::limit($attraction['name'], 10) }}
                                                </div>
                                            </div>
                                        @endforeach
                                        @if(count($attractions) > 3)
                                            <div class="position-relative d-flex align-items-center justify-content-center bg-light rounded" 
                                                 style="width: 60px; height: 60px;">
                                                <span class="text-primary fw-bold">+{{ count($attractions) - 3 }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Hotels Preview -->
                            @if(count($hotels) > 0)
                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-2">
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
                                        @foreach(array_slice($hotels, 0, 2) as $hotel)
                                            <div class="d-flex align-items-center mb-1">
                                                <div class="flex-shrink-0" style="width: 30px; height: 30px;">
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
                                                <div class="flex-grow-1 ms-2">
                                                    <div class="text-truncate small fw-medium">{{ $hotel['name'] }}</div>
                                                    <div class="text-muted" style="font-size: 10px;">{{ $hotel['city'] }}</div>
                                                </div>
                                            </div>
                                        @endforeach
                                        @if(count($hotels) > 2)
                                            <div class="small text-primary">+{{ count($hotels) - 2 }} more hotels</div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Services Preview -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
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
                                            <i class="ri-taxi-line me-1 text-warning"></i>Transfers
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top-0 pt-0">
                        <div class="d-flex gap-2">
                            <a href="{{ route('packages.show', ['package_id' => Crypt::encrypt($package->package_id)]) }}" class="btn btn-primary btn-sm w-100">
                                <i class="ri-eye-line me-1"></i>Details
                            </a>
                            <form action="{{ route('packages.destroy', ['package_id' => Crypt::encrypt($package->package_id)]) }}" method="POST" class="w-100">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm w-100" 
                                        onclick="return confirm('Are you sure you want to delete this package?')">
                                    <i class="ri-delete-bin-line me-1"></i>Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $packages->links() }}
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s, box-shadow 0.2s;
    border: none;
    border-radius: 12px;
    overflow: hidden;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}
.badge {
    font-weight: 500;
    padding: 0.5rem 0.75rem;
}
.badge.rounded-circle {
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.card-img-top {
    border-top-left-radius: calc(0.375rem - 1px);
    border-top-right-radius: calc(0.375rem - 1px);
    height: 220px;
    object-fit: cover;
}
.btn-sm {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 6px;
}
.card-footer {
    padding: 1rem;
}
.itinerary-highlights {
    border-top: 1px solid rgba(0,0,0,0.05);
    padding-top: 15px;
    margin-top: 10px;
}
.attraction-images img {
    transition: transform 0.3s;
}
.attraction-images img:hover {
    transform: scale(1.1);
}
.bg-primary-subtle {
    background-color: rgba(var(--bs-primary-rgb), 0.1);
}
.bg-success-subtle {
    background-color: rgba(var(--bs-success-rgb), 0.1);
}
.bg-warning-subtle {
    background-color: rgba(var(--bs-warning-rgb), 0.1);
}
.hotel-preview {
    max-height: 80px;
    overflow-y: auto;
}
.hotel-preview::-webkit-scrollbar {
    width: 4px;
}
.hotel-preview::-webkit-scrollbar-thumb {
    background-color: rgba(var(--bs-primary-rgb), 0.3);
    border-radius: 4px;
}
</style>
@endsection
