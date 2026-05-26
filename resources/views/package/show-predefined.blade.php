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
                <!-- edit and delete button -->
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
                    $hasLocalTransfers = false;
                    $arrivalData = is_array($package->arrival_data ?? null) ? ($package->arrival_data ?? []) : [];
                    $departureData = is_array($package->departure_data ?? null) ? ($package->departure_data ?? []) : [];
                    $transferData = is_array($package->transfer_data ?? null) ? ($package->transfer_data ?? []) : [];
                    $durationDays = (int) ($package->duration_days ?? 0);

                    // Selected JSON columns (definition packages source of truth)
                    $selectedHotelsRaw = $package->selected_hotels ?? [];
                    $selectedAttractionsRaw = $package->selected_attractions ?? [];
                    $selectedRestaurantsRaw = $package->selected_restaurants ?? [];

                    $selectedHotels = is_string($selectedHotelsRaw) ? (json_decode($selectedHotelsRaw, true) ?: []) : (is_array($selectedHotelsRaw) ? $selectedHotelsRaw : []);
                    $selectedAttractions = is_string($selectedAttractionsRaw) ? (json_decode($selectedAttractionsRaw, true) ?: []) : (is_array($selectedAttractionsRaw) ? $selectedAttractionsRaw : []);
                    $selectedRestaurants = is_string($selectedRestaurantsRaw) ? (json_decode($selectedRestaurantsRaw, true) ?: []) : (is_array($selectedRestaurantsRaw) ? $selectedRestaurantsRaw : []);

                    // Build a day index for a rich day-wise UI
                    $dayIndex = [];
                    $initDay = function (int $day) use (&$dayIndex) {
                        if (!isset($dayIndex[$day])) {
                            $dayIndex[$day] = [
                                'city' => null,
                                'arrivals' => [],
                                'departures' => [],
                                'hotels' => [],
                                'attractions' => [],
                                'restaurants' => [],
                            ];
                        }
                    };
                    $setCity = function (int $day, $city) use (&$dayIndex) {
                        $cityStr = is_string($city) ? trim($city) : '';
                        if ($cityStr !== '' && empty($dayIndex[$day]['city'])) {
                            $dayIndex[$day]['city'] = $cityStr;
                        }
                    };

                    // Hotels: distribute across nights starting from start_day
                    if (!empty($selectedHotels) && is_array($selectedHotels)) {
                        foreach ($selectedHotels as $h) {
                            if (!is_array($h)) continue;
                            $start = (int) ($h['start_day'] ?? 0);
                            $nights = (int) ($h['nights'] ?? 1);
                            if ($start <= 0) continue;
                            $end = $start + max(1, $nights) - 1;
                            for ($d = $start; $d <= $end; $d++) {
                                $initDay($d);
                                $dayIndex[$d]['hotels'][] = $h;
                                $setCity($d, $h['city_plan_city'] ?? ($h['city'] ?? null));
                            }
                            // For highlights preview
                            $hotelKey = $h['hotel_id'] ?? ($h['id'] ?? null);
                            if ($hotelKey !== null) {
                                $hotels[$hotelKey] = [
                                    'id' => $hotelKey,
                                    'name' => $h['hotel_name'] ?? ($h['name'] ?? ''),
                                    'city' => $h['city_plan_city'] ?? ($h['city'] ?? ''),
                                    'main_image' => $h['main_image'] ?? null,
                                    'days' => range($start, $end),
                                ];
                            }
                        }
                    }

                    // Attractions (already have day + pickup/dropoff + guide + transfer)
                    if (!empty($selectedAttractions) && is_array($selectedAttractions)) {
                        foreach ($selectedAttractions as $a) {
                            if (!is_array($a)) continue;
                            $d = (int) ($a['day'] ?? 0);
                            if ($d <= 0) continue;
                            $initDay($d);
                            $dayIndex[$d]['attractions'][] = $a;
                            $setCity($d, $a['city_plan_city'] ?? ($a['location'] ?? null));

                            $atk = $a['attraction_id'] ?? ($a['id'] ?? null);
                            if ($atk !== null) {
                                $attractions[$atk] = $a;
                            }

                            if (!empty($a['guide']) && is_array($a['guide'])) {
                                $gid = $a['guide']['id'] ?? null;
                                if ($gid !== null) {
                                    $guides[$gid] = $a['guide'];
                                }
                            }
                        }
                    }

                    // Restaurants (already have day + meal_type + pickup/dropoff + transfer)
                    if (!empty($selectedRestaurants) && is_array($selectedRestaurants)) {
                        foreach ($selectedRestaurants as $r) {
                            if (!is_array($r)) continue;
                            $d = (int) ($r['day'] ?? 0);
                            if ($d <= 0) continue;
                            $initDay($d);
                            $dayIndex[$d]['restaurants'][] = $r;
                            $setCity($d, $r['city_plan_city'] ?? null);
                        }
                    }

                    // Arrival/Departure (explicit JSON columns)
                    $arrivalItems = (isset($arrivalData['items']) && is_array($arrivalData['items'])) ? $arrivalData['items'] : [];
                    foreach ($arrivalItems as $ai) {
                        if (!is_array($ai)) continue;
                        $d = (int) ($ai['day'] ?? 0);
                        if ($d <= 0) continue;
                        $initDay($d);
                        $dayIndex[$d]['arrivals'][] = $ai;
                        $setCity($d, $ai['city'] ?? null);
                    }

                    $departureItems = (isset($departureData['items']) && is_array($departureData['items'])) ? $departureData['items'] : [];
                    foreach ($departureItems as $di) {
                        if (!is_array($di)) continue;
                        $d = (int) ($di['day'] ?? 0);
                        if ($d <= 0) continue;
                        $initDay($d);
                        $dayIndex[$d]['departures'][] = $di;
                        $setCity($d, $di['city'] ?? null);
                    }

                    // Normalize day-wise rows so this template works for:
                    // - predefined packages: itineraryData['itinerary'] = [ {day, attractions[], guide{}, arrival_pickup, departure_service, ...}, ... ]
                    // - definition packages: itineraryData['day_wise_itinerary'] = [ {day, city, arrival{}, departure{}, ...}, ... ]
                    $dayRows = [];
                    if (isset($itineraryData['itinerary']) && is_array($itineraryData['itinerary'])) {
                        $dayRows = $itineraryData['itinerary'];
                    } elseif (isset($itineraryData['day_wise_itinerary']) && is_array($itineraryData['day_wise_itinerary'])) {
                        $dayRows = $itineraryData['day_wise_itinerary'];
                    } elseif (is_array($itineraryData) && array_is_list($itineraryData) && !empty($itineraryData)) {
                        // In case the itinerary field itself is already a list of day rows
                        $dayRows = $itineraryData;
                    }
                    // Ensure we have day buckets for Day 1..N even if empty (better UX)
                    if ($durationDays > 0) {
                        for ($d = 1; $d <= $durationDays; $d++) {
                            $initDay($d);
                        }
                    }
                    
                    // Process itinerary data if available
                    if(!empty($dayRows)) {
                            foreach($dayRows as $day) {
                                // Collect attractions - more efficient using associative array
                                if(isset($day['attractions']) && is_array($day['attractions'])) {
                                    foreach($day['attractions'] as $attraction) {
                                        if(is_array($attraction) && isset($attraction['attraction_id'])) {
                                            // Use attraction_id as key for efficient deduplication
                                            $attractions[$attraction['attraction_id']] = $attraction;
                                        } elseif (is_array($attraction) && isset($attraction['id'])) {
                                            $attractions[$attraction['id']] = $attraction;
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
                                if((isset($day['arrival_pickup']) && (int) $day['arrival_pickup'] == 1) || !empty($day['arrival'])) {
                                    $hasArrivalPickup = true;
                                }
                                
                                if((isset($day['departure_service']) && (int) $day['departure_service'] == 1) || !empty($day['departure'])) {
                                    $hasDepartureService = true;
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

                    // Explicit JSON columns for services (definition packages)
                    $hasArrivalPickup = $hasArrivalPickup || (bool) ($arrivalData['enabled'] ?? false);
                    $hasDepartureService = $hasDepartureService || (bool) ($departureData['enabled'] ?? false);
                    $hasLocalTransfers = !empty($transferData) && is_array($transferData) && count($transferData) > 0;
                    
                    // Convert associative arrays to indexed arrays for display
                    $attractions = array_values($attractions);
                    $hotels = array_values($hotels);
                    $guides = array_values($guides);
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
                                        <i class="ri-taxi-line me-1 text-info"></i>Transfer Services
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Day-wise Itinerary -->
                @if(!empty($dayIndex))
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-calendar-check-line me-2"></i>Detailed Itinerary
                        </h5>
                    </div>
                    <div class="card-body">
                        @php ksort($dayIndex); @endphp
                        <div class="accordion" id="itineraryAccordion">
                            @foreach($dayIndex as $dayNum => $meta)
                                @php
                                    $city = $meta['city'] ?? null;
                                    $isArrivalDay = ($dayNum === 1 && (!empty($meta['arrivals']) || $hasArrivalPickup));
                                    $isDepartureDay = ($durationDays > 0 && $dayNum === $durationDays && (!empty($meta['departures']) || $hasDepartureService));
                                @endphp
                                <div class="accordion-item mb-2 border-0">
                                    <h2 class="accordion-header" id="headingDay{{ $dayNum }}">
                                        <button class="accordion-button {{ $dayNum === 1 ? '' : 'collapsed' }}" type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#collapseDay{{ $dayNum }}"
                                                aria-expanded="{{ $dayNum === 1 ? 'true' : 'false' }}"
                                                aria-controls="collapseDay{{ $dayNum }}">
                                            <div class="d-flex align-items-center w-100 justify-content-between">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-primary">Day {{ $dayNum }}</span>
                                                    @if($city)
                                                        <span class="badge bg-secondary-subtle text-secondary">
                                                            <i class="ri-map-pin-line me-1"></i>{{ $city }}
                                                        </span>
                                                    @endif
                                                    @if($isArrivalDay)
                                                        <span class="badge bg-info-subtle text-info">Arrival</span>
                                                    @endif
                                                    @if($isDepartureDay)
                                                        <span class="badge bg-warning-subtle text-warning">Departure</span>
                                                    @endif
                                                </div>
                                                <div class="d-none d-md-flex gap-2">
                                                    @if(!empty($meta['hotels'])) <span class="badge bg-success-subtle text-success">{{ count($meta['hotels']) }} Hotel</span> @endif
                                                    @if(!empty($meta['attractions'])) <span class="badge bg-primary-subtle text-primary">{{ count($meta['attractions']) }} Attraction</span> @endif
                                                    @if(!empty($meta['restaurants'])) <span class="badge bg-danger-subtle text-danger">{{ count($meta['restaurants']) }} Meal</span> @endif
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapseDay{{ $dayNum }}" class="accordion-collapse collapse {{ $dayNum === 1 ? 'show' : '' }}"
                                         aria-labelledby="headingDay{{ $dayNum }}"
                                         data-bs-parent="#itineraryAccordion">
                                        <div class="accordion-body pt-2">

                                            {{-- Arrival --}}
                                            @if(!empty($meta['arrivals']))
                                                <div class="mb-3">
                                                    <h6 class="text-info mb-2">
                                                        <i class="ri-flight-land-line me-1"></i>Arrival Transfer
                                                    </h6>
                                                    @foreach($meta['arrivals'] as $ai)
                                                        @php $vehicles = (isset($ai['vehicles']) && is_array($ai['vehicles'])) ? $ai['vehicles'] : []; @endphp
                                                        <div class="border rounded p-2 mb-2">
                                                            <div class="d-flex flex-wrap gap-2 small">
                                                                <span class="badge bg-light text-dark">
                                                                    Pickup Port:
                                                                    {{ $ai['pickup_port_name'] ?? ($ai['pickup_port_id'] ?? '-') }}
                                                                </span>
                                                                <span class="badge bg-light text-dark">
                                                                    Dropoff Hotel:
                                                                    {{ $ai['dropoff_hotel_name'] ?? ($ai['dropoff_hotel_id'] ?? '-') }}
                                                                </span>
                                                                @if(count($vehicles) > 0)
                                                                    <span class="badge bg-light text-dark">{{ count($vehicles) }} vehicle(s)</span>
                                                                @endif
                                                            </div>
                                                            @if(count($vehicles) > 0)
                                                                <div class="mt-2 row g-2">
                                                                    @foreach($vehicles as $v)
                                                                        <div class="col-md-6">
                                                                            <div class="bg-light rounded p-2 small">
                                                                                <div class="fw-semibold">{{ $v['vehicle_name'] ?? 'Vehicle' }} <span class="text-muted">({{ $v['vehicle_type'] ?? '-' }})</span></div>
                                                                                <div class="text-muted">
                                                                                    Type: <span class="text-capitalize">{{ $v['selected_transfer_type'] ?? '-' }}</span>
                                                                                    @if(isset($v['seating_capacity'])) · Seats: {{ $v['seating_capacity'] }} @endif
                                                                                    @if(isset($v['qty'])) · Qty: {{ $v['qty'] }} @endif
                                                                                </div>
                                                                                @if(isset($v['selected_price']))
                                                                                    <div class="text-primary fw-semibold">SGD {{ number_format((float) $v['selected_price'], 2) }}</div>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            {{-- Departure --}}
                                            @if(!empty($meta['departures']))
                                                <div class="mb-3">
                                                    <h6 class="text-warning mb-2">
                                                        <i class="ri-flight-takeoff-line me-1"></i>Departure Transfer
                                                    </h6>
                                                    @foreach($meta['departures'] as $di)
                                                        @php $vehicles = (isset($di['vehicles']) && is_array($di['vehicles'])) ? $di['vehicles'] : []; @endphp
                                                        <div class="border rounded p-2 mb-2">
                                                            <div class="d-flex flex-wrap gap-2 small">
                                                                <span class="badge bg-light text-dark">
                                                                    Pickup Hotel:
                                                                    {{ $di['pickup_hotel_name'] ?? ($di['pickup_hotel_id'] ?? '-') }}
                                                                </span>
                                                                <span class="badge bg-light text-dark">
                                                                    Dropoff Port:
                                                                    {{ $di['dropoff_port_name'] ?? ($di['dropoff_port_id'] ?? '-') }}
                                                                </span>
                                                                @if(count($vehicles) > 0)
                                                                    <span class="badge bg-light text-dark">{{ count($vehicles) }} vehicle(s)</span>
                                                                @endif
                                                            </div>
                                                            @if(count($vehicles) > 0)
                                                                <div class="mt-2 row g-2">
                                                                    @foreach($vehicles as $v)
                                                                        <div class="col-md-6">
                                                                            <div class="bg-light rounded p-2 small">
                                                                                <div class="fw-semibold">{{ $v['vehicle_name'] ?? 'Vehicle' }} <span class="text-muted">({{ $v['vehicle_type'] ?? '-' }})</span></div>
                                                                                <div class="text-muted">
                                                                                    Type: <span class="text-capitalize">{{ $v['selected_transfer_type'] ?? '-' }}</span>
                                                                                    @if(isset($v['seating_capacity'])) · Seats: {{ $v['seating_capacity'] }} @endif
                                                                                    @if(isset($v['qty'])) · Qty: {{ $v['qty'] }} @endif
                                                                                </div>
                                                                                @if(isset($v['selected_price']))
                                                                                    <div class="text-primary fw-semibold">SGD {{ number_format((float) $v['selected_price'], 2) }}</div>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            {{-- Accommodation --}}
                                            @if(!empty($meta['hotels']))
                                                <div class="mb-3">
                                                    <h6 class="text-success mb-2">
                                                        <i class="ri-hotel-line me-1"></i>Accommodation
                                                    </h6>
                                                    <div class="row g-2">
                                                        @foreach($meta['hotels'] as $h)
                                                            @php
                                                                $hotelName = $h['hotel_name'] ?? ($h['name'] ?? 'Hotel');
                                                                $nights = (int) ($h['nights'] ?? 1);
                                                                $rooms = (isset($h['rooms']) && is_array($h['rooms'])) ? $h['rooms'] : [];
                                                            @endphp
                                                            <div class="col-md-6">
                                                                <div class="border rounded p-2 h-100">
                                                                    <div class="fw-semibold">{{ $hotelName }}</div>
                                                                    <div class="text-muted small">
                                                                        {{ $h['city_plan_city'] ?? ($h['city'] ?? '') }}
                                                                        · {{ $nights }} night{{ $nights === 1 ? '' : 's' }}
                                                                    </div>
                                                                    @if(count($rooms) > 0)
                                                                        <div class="mt-2 small">
                                                                            @foreach($rooms as $r)
                                                                                <div class="d-flex justify-content-between">
                                                                                    <span class="text-truncate">
                                                                                        {{ $r['room_type_name'] ?? 'Room' }}
                                                                                        @if(!empty($r['bed_type'])) · {{ $r['bed_type'] }} @endif
                                                                                        @if(isset($r['quantity'])) · Qty: {{ $r['quantity'] }} @endif
                                                                                    </span>
                                                                                    <span class="text-muted">
                                                                                        @if(isset($r['weekday_price'])) Wd: {{ (float) $r['weekday_price'] }} @endif
                                                                                        @if(isset($r['weekend_price'])) · We: {{ (float) $r['weekend_price'] }} @endif
                                                                                    </span>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Attractions --}}
                                            @if(!empty($meta['attractions']))
                                                <div class="mb-3">
                                                    <h6 class="text-primary mb-2">
                                                        <i class="ri-map-pin-line me-1"></i>Attractions
                                                    </h6>
                                                    <div class="row g-2">
                                                        @foreach($meta['attractions'] as $a)
                                                            @php
                                                                $guide = (isset($a['guide']) && is_array($a['guide'])) ? $a['guide'] : null;
                                                                $hasTransfer = !empty($a['transfer']) || ((int) ($a['transfer_available'] ?? 0) === 1);
                                                            @endphp
                                                            <div class="col-md-6">
                                                                <div class="border rounded p-2 h-100">
                                                                    <div class="d-flex align-items-start">
                                                                        <div class="me-2 flex-shrink-0" style="width: 44px; height: 44px;">
                                                                            @if(!empty($a['image']))
                                                                                <img src="{{ $a['image'] }}" class="rounded" style="width: 44px; height: 44px; object-fit: cover;">
                                                                            @else
                                                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                                                                    <i class="ri-image-line text-muted"></i>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                        <div class="flex-grow-1">
                                                                            <div class="fw-semibold">{{ $a['name'] ?? 'Attraction' }}</div>
                                                                            <div class="text-muted small">{{ $a['location'] ?? ($a['city_plan_city'] ?? '') }}</div>
                                                                            @if(!empty($a['ticket_name']))
                                                                                <div class="small">
                                                                                    <span class="badge bg-light text-dark"><i class="ri-ticket-2-line me-1"></i>{{ $a['ticket_name'] }}</span>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>

                                                                    <div class="mt-2 d-flex flex-wrap gap-2 small">
                                                                        @if($guide)
                                                                            <span class="badge bg-primary-subtle text-primary">
                                                                                <i class="ri-user-voice-line me-1"></i>{{ $guide['name'] ?? 'Guide' }}
                                                                                @if(!empty($guide['duration_label'])) · {{ $guide['duration_label'] }} @endif
                                                                            </span>
                                                                        @endif
                                                                        @if($hasTransfer)
                                                                            <span class="badge bg-info-subtle text-info">
                                                                                <i class="ri-taxi-line me-1"></i>{{ $a['vehicle_name'] ?? 'Transfer' }}
                                                                                @if(!empty($a['transfer_type'])) · {{ ucfirst((string) $a['transfer_type']) }} @endif
                                                                                @if(isset($a['transfer_price'])) · SGD {{ number_format((float) $a['transfer_price'], 2) }} @endif
                                                                            </span>
                                                                        @endif
                                                                    </div>

                                                                    @if(!empty($a['pickup_name']) || !empty($a['dropoff_name']))
                                                                        <div class="mt-2 small text-muted">
                                                                            @if(!empty($a['pickup_name'])) <div><i class="ri-map-pin-time-line me-1"></i>Pickup: {{ $a['pickup_name'] }}</div> @endif
                                                                            @if(!empty($a['dropoff_name'])) <div><i class="ri-flag-2-line me-1"></i>Dropoff: {{ $a['dropoff_name'] }}</div> @endif
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            {{-- Restaurants --}}
                                            @if(!empty($meta['restaurants']))
                                                <div class="mb-3">
                                                    <h6 class="text-danger mb-2">
                                                        <i class="ri-restaurant-line me-1"></i>Meals / Restaurants
                                                    </h6>
                                                    <div class="row g-2">
                                                        @foreach($meta['restaurants'] as $r)
                                                            @php
                                                                $hasTransfer = !empty($r['transfer']);
                                                                $mealLabel = $r['meal_type_label'] ?? null;
                                                            @endphp
                                                            <div class="col-md-6">
                                                                <div class="border rounded p-2 h-100">
                                                                    <div class="fw-semibold">{{ $r['restaurant_name'] ?? ($r['name'] ?? 'Restaurant') }}</div>
                                                                    <div class="text-muted small">
                                                                        @if($mealLabel) {{ $mealLabel }} @endif
                                                                        @if(isset($r['adult_price'])) · Adult: SGD {{ number_format((float) $r['adult_price'], 2) }} @endif
                                                                        @if(isset($r['child_price'])) · Child: SGD {{ number_format((float) $r['child_price'], 2) }} @endif
                                                                    </div>
                                                                    <div class="mt-2 d-flex flex-wrap gap-2 small">
                                                                        @if($hasTransfer)
                                                                            <span class="badge bg-info-subtle text-info">
                                                                                <i class="ri-taxi-line me-1"></i>{{ $r['vehicle_name'] ?? 'Transfer' }}
                                                                                @if(!empty($r['transfer_type'])) · {{ ucfirst((string) $r['transfer_type']) }} @endif
                                                                                @if(isset($r['transfer_price'])) · SGD {{ number_format((float) $r['transfer_price'], 2) }} @endif
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                    @if(!empty($r['pickup_name']) || !empty($r['dropoff_name']))
                                                                        <div class="mt-2 small text-muted">
                                                                            @if(!empty($r['pickup_name'])) <div><i class="ri-map-pin-time-line me-1"></i>Pickup: {{ $r['pickup_name'] }}</div> @endif
                                                                            @if(!empty($r['dropoff_name'])) <div><i class="ri-flag-2-line me-1"></i>Dropoff: {{ $r['dropoff_name'] }}</div> @endif
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            @if(empty($meta['arrivals']) && empty($meta['departures']) && empty($meta['hotels']) && empty($meta['attractions']) && empty($meta['restaurants']))
                                                <div class="text-muted small">No services booked for this day.</div>
                                            @endif
                                        </div>
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
                        @php
                            $priceDataRaw = $package->price_data ?? null;
                            $priceDataArr = is_string($priceDataRaw) ? (json_decode($priceDataRaw, true) ?: []) : (is_array($priceDataRaw) ? $priceDataRaw : []);
                            $pdTotal = isset($priceDataArr['total_price']) && is_numeric($priceDataArr['total_price']) ? (float) $priceDataArr['total_price'] : null;
                            $pdMarkupType = $priceDataArr['markup_type'] ?? null;
                            $pdMarkupAmount = isset($priceDataArr['markup_amount']) && is_numeric($priceDataArr['markup_amount']) ? (float) $priceDataArr['markup_amount'] : null;
                            $pdFinal = isset($priceDataArr['final_price']) && is_numeric($priceDataArr['final_price']) ? (float) $priceDataArr['final_price'] : null;
                        @endphp

                        @if($pdTotal !== null)
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Total Price:</span>
                                <span class="h6 mb-0">SGD {{ number_format($pdTotal, 2) }}</span>
                            </div>
                        </div>
                        @endif

                        @if($pdMarkupType && $pdMarkupAmount !== null)
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">
                                    Markup
                                    <small class="text-capitalize">({{ $pdMarkupType }})</small>:
                                </span>
                                <span class="h6 text-warning mb-0">
                                    @if($pdMarkupType === 'percentage')
                                        {{ rtrim(rtrim(number_format($pdMarkupAmount, 2), '0'), '.') }}%
                                    @else
                                        SGD {{ number_format($pdMarkupAmount, 2) }}
                                    @endif
                                </span>
                            </div>
                        </div>
                        @endif

                        <div class="mb-3 pt-2 border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Final Price:</span>
                                <span class="h5 text-primary mb-0">
                                    SGD {{ number_format($pdFinal !== null ? $pdFinal : (is_numeric($package->price_adult) ? (float) $package->price_adult : 0), 2) }}
                                </span>
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