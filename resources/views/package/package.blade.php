@extends('layouts.layout')
@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">
                <i class="ri-gift-line me-2 text-primary"></i>Packages
            </h4>
            <div class="d-flex gap-2">
                {{-- <a href="{{ route('packages.create') }}" class="btn btn-primary">
                    <i class="ri-add-line me-1"></i>Create New Package
                </a> --}}
                <a href="{{ route('packages.definition.create') }}" class="btn btn-outline-primary">
                    <i class="ri-file-list-3-line me-1"></i>Package Definition
                </a>
            </div>
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
                            <label class="form-label">Package Status</label>
                            <select class="form-select" name="package_status" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="active" {{ request('package_status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="expired" {{ request('package_status') == 'expired' ? 'selected' : '' }}>Expired</option>
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
                    @php
                        $priceDataRaw = $package->price_data ?? null;
                        $priceDataArr = is_string($priceDataRaw) ? (json_decode($priceDataRaw, true) ?: []) : (is_array($priceDataRaw) ? $priceDataRaw : []);
                        $finalPrice = isset($priceDataArr['final_price']) && is_numeric($priceDataArr['final_price'])
                            ? (float) $priceDataArr['final_price']
                            : (is_numeric($package->price_adult) ? (float) $package->price_adult : 0);
                        $isBooked = (int) ($package->bookings_count ?? 0) > 0;
                        $isExpired = !empty($package->expire_date) && \Carbon\Carbon::parse($package->expire_date)->endOfDay()->lt(now());
                        $statusLabel = $isExpired
                            ? 'Expired'
                            : ($package->status == '1' ? 'Active' : ($package->status == '0' ? 'Draft' : 'Inactive'));
                        $statusClass = $isExpired
                            ? 'danger'
                            : ($package->status == '1' ? 'success' : ($package->status == '0' ? 'warning' : 'secondary'));
                    @endphp
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="text-primary">
                                <div class="small">From SGD</div>
                                <div class="h4 mb-0">${{ number_format($finalPrice, 2) }}</div>
                            </div>
                            <span class="badge bg-{{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </div>
                        
                        @php
                            // Parse selected_* JSON columns safely (new source of truth)
                            $selectedHotelsRaw = $package->selected_hotels ?? [];
                            $selectedAttractionsRaw = $package->selected_attractions ?? [];
                            $selectedGuidesRaw = $package->selected_guide ?? [];
                            $selectedRestaurantsRaw = $package->selected_restaurants ?? [];
                            $itineraryRaw = $package->itinerary ?? null;

                            $selectedHotels = is_string($selectedHotelsRaw) ? (json_decode($selectedHotelsRaw, true) ?: []) : (is_array($selectedHotelsRaw) ? $selectedHotelsRaw : []);
                            $selectedAttractions = is_string($selectedAttractionsRaw) ? (json_decode($selectedAttractionsRaw, true) ?: []) : (is_array($selectedAttractionsRaw) ? $selectedAttractionsRaw : []);
                            $selectedGuides = is_string($selectedGuidesRaw) ? (json_decode($selectedGuidesRaw, true) ?: []) : (is_array($selectedGuidesRaw) ? $selectedGuidesRaw : []);
                            $selectedRestaurants = is_string($selectedRestaurantsRaw) ? (json_decode($selectedRestaurantsRaw, true) ?: []) : (is_array($selectedRestaurantsRaw) ? $selectedRestaurantsRaw : []);
                            $itineraryData = is_string($itineraryRaw) ? (json_decode($itineraryRaw, true) ?: []) : (is_array($itineraryRaw) ? $itineraryRaw : []);

                            // Extract key information
                            $attractions = [];
                            $guides = [];
                            $hotels = [];
                            $hasArrivalPickup = false;
                            $hasDepartureService = false;
                            $hasLocalTransfers = false;
                            $dayWiseServices = [];

                            // Build attractions list (dedupe by attraction_id/id)
                            if (!empty($selectedAttractions) && is_array($selectedAttractions)) {
                                foreach ($selectedAttractions as $attraction) {
                                    if (!is_array($attraction)) continue;
                                    $attractionKey = $attraction['attraction_id'] ?? $attraction['id'] ?? null;
                                    if ($attractionKey !== null) {
                                        $attractions[$attractionKey] = $attraction;
                                    }
                                }
                            }

                            // Build hotels list (dedupe by hotel_id/id)
                            if (!empty($selectedHotels) && is_array($selectedHotels)) {
                                foreach ($selectedHotels as $hotel) {
                                    if (!is_array($hotel)) continue;
                                    $hotelKey = $hotel['hotel_id'] ?? $hotel['id'] ?? null;
                                    if ($hotelKey !== null) {
                                        $hotels[$hotelKey] = $hotel;
                                    }
                                }
                            }

                            // Build guides list (dedupe by id)
                            if (!empty($selectedGuides) && is_array($selectedGuides)) {
                                foreach ($selectedGuides as $guide) {
                                    if (!is_array($guide)) continue;
                                    $guideId = $guide['id'] ?? null;
                                    if ($guideId !== null) {
                                        $guides[$guideId] = $guide;
                                    }
                                }
                            }

                            // Services flags:
                            // Prefer explicit stored JSON (arrival_data/departure_data/transfer_data),
                            // fallback to day-wise itinerary flags if present.
                            $arrivalData = $package->arrival_data ?? [];
                            $departureData = $package->departure_data ?? [];
                            $transferData = $package->transfer_data ?? [];

                            $arrivalEnabled = is_array($arrivalData) ? (bool) ($arrivalData['enabled'] ?? false) : false;
                            $departureEnabled = is_array($departureData) ? (bool) ($departureData['enabled'] ?? false) : false;
                            $hasArrivalPickup = $arrivalEnabled;
                            $hasDepartureService = $departureEnabled;
                            $hasLocalTransfers = is_array($transferData) ? (count($transferData) > 0) : false;

                            // Predefined packages often store day-wise itinerary with arrival_pickup/departure_service on each day.
                            $dayRows = [];
                            if (isset($itineraryData['itinerary']) && is_array($itineraryData['itinerary'])) {
                                $dayRows = $itineraryData['itinerary'];
                            } elseif (isset($itineraryData['day_wise_itinerary']) && is_array($itineraryData['day_wise_itinerary'])) {
                                $dayRows = $itineraryData['day_wise_itinerary'];
                            }
                            foreach ($dayRows as $row) {
                                if (!is_array($row)) continue;
                                $dayNum = (int) ($row['day'] ?? 0);
                                if ($dayNum <= 0) continue;
                                if (!isset($dayWiseServices[$dayNum])) {
                                    $dayWiseServices[$dayNum] = [
                                        'arrival' => false,
                                        'departure' => false,
                                        'guide' => false,
                                        'attractions' => 0,
                                    ];
                                }
                                if ((int) ($row['arrival_pickup'] ?? 0) === 1 || !empty($row['arrival'])) {
                                    $dayWiseServices[$dayNum]['arrival'] = true;
                                    $hasArrivalPickup = true;
                                }
                                if ((int) ($row['departure_service'] ?? 0) === 1 || !empty($row['departure'])) {
                                    $dayWiseServices[$dayNum]['departure'] = true;
                                    $hasDepartureService = true;
                                }
                                if (!empty($row['guide'])) {
                                    $dayWiseServices[$dayNum]['guide'] = true;
                                }
                                if (isset($row['attractions']) && is_array($row['attractions'])) {
                                    $dayWiseServices[$dayNum]['attractions'] += count($row['attractions']);
                                }
                            }

                            // If no itinerary days were available, fall back to "day" fields in selected_* arrays.
                            if (empty($dayWiseServices)) {
                                foreach ($selectedAttractions as $a) {
                                    if (!is_array($a)) continue;
                                    $d = (int) ($a['day'] ?? 0);
                                    if ($d <= 0) continue;
                                    if (!isset($dayWiseServices[$d])) {
                                        $dayWiseServices[$d] = ['arrival' => false, 'departure' => false, 'guide' => false, 'attractions' => 0];
                                    }
                                    $dayWiseServices[$d]['attractions'] += 1;
                                }
                                foreach ($selectedGuides as $g) {
                                    if (!is_array($g)) continue;
                                    $d = (int) ($g['day'] ?? 0);
                                    if ($d <= 0) continue;
                                    if (!isset($dayWiseServices[$d])) {
                                        $dayWiseServices[$d] = ['arrival' => false, 'departure' => false, 'guide' => false, 'attractions' => 0];
                                    }
                                    $dayWiseServices[$d]['guide'] = true;
                                }
                            }

                            // Convert associative arrays to indexed arrays for display
                            $attractions = array_values($attractions);
                            $hotels = array_values($hotels);
                            $guides = array_values($guides);
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
                                                    <div class="text-muted" style="font-size: 10px;">{{ $hotel['city'] ?? '' }} {{ $hotel['country'] ?? '' }}</div>
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

                                    @if($hasLocalTransfers)
                                        <span class="badge bg-light text-dark">
                                            <i class="ri-taxi-line me-1 text-info"></i>Local Transfers
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

                            <!-- Day-wise Quick View -->
                            @if(!empty($dayWiseServices) && is_array($dayWiseServices))
                                @php
                                    ksort($dayWiseServices);
                                    $dayWisePreview = array_slice($dayWiseServices, 0, 2, true);
                                @endphp
                                <div class="small text-muted">
                                    <div class="fw-semibold text-dark mb-1">
                                        <i class="ri-calendar-check-line me-1 text-primary"></i>Day-wise (quick)
                                    </div>
                                    @foreach($dayWisePreview as $dayNum => $meta)
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span>Day {{ $dayNum }}</span>
                                            <span class="text-end">
                                                @if(!empty($meta['arrival'])) <span class="badge bg-info-subtle text-info">Arr</span> @endif
                                                @if(!empty($meta['departure'])) <span class="badge bg-warning-subtle text-warning">Dep</span> @endif
                                                @if(!empty($meta['guide'])) <span class="badge bg-primary-subtle text-primary">Guide</span> @endif
                                                @if(!empty($meta['attractions'])) <span class="badge bg-light text-dark">{{ (int) $meta['attractions'] }} Attr</span> @endif
                                            </span>
                                        </div>
                                    @endforeach
                                    @if(count($dayWiseServices) > 2)
                                        <div class="mt-1 text-primary">+{{ count($dayWiseServices) - 2 }} more day(s)</div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top-0 pt-0">
                        <div class="d-flex gap-2">
                            <a href="{{ route('packages.show', ['package_id' => Crypt::encrypt($package->package_id)]) }}" class="btn btn-primary btn-sm w-100">
                                <i class="ri-eye-line me-1"></i>Details
                            </a>
                            @php
                                $editDisabled = $isBooked || $isExpired;
                                $editTitle = $isBooked
                                    ? 'This package is already booked and cannot be edited.'
                                    : ($isExpired ? 'This package has expired and cannot be edited.' : '');
                                $deleteDisabled = $isBooked || $isExpired;
                                $deleteTitle = $isBooked
                                    ? 'This package is already booked and cannot be deleted.'
                                    : ($isExpired ? 'This package has expired and cannot be deleted.' : '');
                                $editHref = $package->package_type === 'definition'
                                    ? route('packages.definition.edit', ['package_id' => Crypt::encrypt($package->package_id)])
                                    : route('packages.edit', ['package_id' => Crypt::encrypt($package->package_id)]);
                            @endphp

                            @if($editDisabled)
                                <span class="d-inline-block w-100"
                                      tabindex="0"
                                      data-bs-toggle="tooltip"
                                      data-bs-placement="top"
                                      title="{{ $editTitle }}">
                                    <a href="#"
                                       class="btn btn-outline-primary btn-sm w-100 disabled"
                                       tabindex="-1"
                                       aria-disabled="true"
                                       onclick="return false;">
                                        <i class="ri-edit-line me-1"></i>Edit
                                    </a>
                                </span>
                            @else
                                <a href="{{ $editHref }}" class="btn btn-outline-primary btn-sm w-100">
                                    <i class="ri-edit-line me-1"></i>Edit
                                </a>
                            @endif
                            @if($deleteDisabled)
                                <span class="d-inline-block w-100"
                                      tabindex="0"
                                      data-bs-toggle="tooltip"
                                      data-bs-placement="top"
                                      title="{{ $deleteTitle }}">
                                    <button type="button"
                                            class="btn btn-outline-danger btn-sm w-100 disabled"
                                            tabindex="-1"
                                            disabled>
                                        <i class="ri-delete-bin-line me-1"></i>Delete
                                    </button>
                                </span>
                            @else
                                <form action="{{ route('packages.destroy', ['package_id' => Crypt::encrypt($package->package_id)]) }}" method="POST" class="w-100">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100" 
                                            onclick="return confirm('Are you sure you want to delete this package?')">
                                        <i class="ri-delete-bin-line me-1"></i>Delete
                                    </button>
                                </form>
                            @endif
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (!window.bootstrap || !bootstrap.Tooltip) return;
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
    new bootstrap.Tooltip(el);
  });
});
</script>
@endsection
@endsection
