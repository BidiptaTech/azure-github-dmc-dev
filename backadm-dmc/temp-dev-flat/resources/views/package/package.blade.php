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
                    </div>
                    <div class="card-body">
                        <h5 class="card-title mb-2">{{ $package->title }}</h5>
                        
                        <div class="mb-3">
                            <i class="ri-map-pin-line me-2 text-primary"></i>
                            {{ $package->destination }} - {{ $package->city }}
                        </div>

                        <div class="text-primary mb-3">
                            <div class="small">From USD</div>
                            <div class="h4 mb-0">${{ number_format($package->price_adult, 2) }}</div>
                        </div>

                        <!-- Hotels -->
                        <div class="mb-2">
                            @php
                                $hotels = is_string($package->selected_hotels) ? 
                                    json_decode($package->selected_hotels, true) : 
                                    (is_array($package->selected_hotels) ? $package->selected_hotels : []);
                            @endphp
                            @if(!empty($hotels))
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($hotels as $hotel)
                                        @if(is_array($hotel) && isset($hotel['name']))
                                            <span class="badge bg-light text-dark">
                                                <i class="ri-hotel-line me-1"></i>{{ $hotel['name'] }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Attractions -->
                        <div class="mb-3">
                            @php
                                $attractions = is_string($package->selected_attractions) ? 
                                    json_decode($package->selected_attractions, true) : 
                                    (is_array($package->selected_attractions) ? $package->selected_attractions : []);
                            @endphp
                            @if(!empty($attractions))
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($attractions as $attraction)
                                        @if(is_array($attraction) && isset($attraction['name']))
                                            <span class="badge bg-light text-dark">
                                                <i class="ri-map-pin-line me-1"></i>{{ $attraction['name'] }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Restaurants -->
                        <div class="mb-3">
                            @php
                                $restaurants = is_string($package->selected_restaurants) ? 
                                    json_decode($package->selected_restaurants, true) : 
                                    (is_array($package->selected_restaurants) ? $package->selected_restaurants : []);
                            @endphp
                            @if(!empty($restaurants))
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($restaurants as $restaurant)
                                        @if(is_array($restaurant) && isset($restaurant['name']))
                                            <span class="badge bg-light text-dark">
                                                <i class="ri-restaurant-line me-1"></i>{{ $restaurant['name'] }}
                                                @if(isset($restaurant['cuisine']) && !empty($restaurant['cuisine']))
                                                    <small>({{ $restaurant['cuisine'] }})</small>
                                                @endif
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Guides -->
                        <div class="mb-3">
                            @php
                                $guides = is_string($package->selected_guide) ? 
                                    json_decode($package->selected_guide, true) : 
                                    (is_array($package->selected_guide) ? $package->selected_guide : []);
                            @endphp
                            @if(!empty($guides))
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($guides as $guide)
                                        @if(is_array($guide) && isset($guide['name']))
                                            <span class="badge bg-light text-dark">
                                                <i class="ri-user-line me-1"></i>{{ $guide['name'] }}
                                                @if(isset($guide['languages']) && !empty($guide['languages']))
                                                    <small>(
                                                    @if(is_array($guide['languages']))
                                                        @php
                                                            $languageStrings = [];
                                                            foreach($guide['languages'] as $lang) {
                                                                if(is_string($lang)) {
                                                                    $languageStrings[] = $lang;
                                                                } elseif(is_array($lang) && isset($lang['language'])) {
                                                                    $languageStrings[] = $lang['language'];
                                                                } elseif(is_object($lang) && isset($lang->language)) {
                                                                    $languageStrings[] = $lang->language;
                                                                }
                                                            }
                                                            echo implode(', ', $languageStrings);
                                                        @endphp
                                                    @else
                                                        {{ $guide['languages'] }}
                                                    @endif
                                                    )</small>
                                                @endif
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <span class="badge bg-{{ $package->status == '1' ? 'success' : ($package->status == '0' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($package->status == '1' ? 'Active' : ($package->status == '0' ? 'Draft' : 'Inactive')) }}
                            </span>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <div class="d-flex gap-2">
                            <a href="{{ route('packages.show', ['id' => $package->package_id]) }}" class="btn btn-primary btn-sm w-100">
                                <i class="ri-eye-line me-1"></i>View
                            </a>
                            <!-- edit field -->
                            <form action="{{ route('packages.destroy', ['id' => $package->package_id]) }}" method="POST" class="w-100">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm w-100" 
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
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.badge {
    font-weight: 500;
    padding: 0.5rem 0.75rem;
}
.card-img-top {
    border-top-left-radius: calc(0.375rem - 1px);
    border-top-right-radius: calc(0.375rem - 1px);
}
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
.card-footer {
    padding: 1rem;
}
</style>
@endsection
