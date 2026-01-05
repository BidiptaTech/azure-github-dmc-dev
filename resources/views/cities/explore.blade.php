@extends('layouts.layout')
@section('title', 'Explore City - ' . $city->name)

@section('css')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .nav-pills .nav-link {
        border-radius: 10px;
        padding: 12px 20px;
        margin: 5px;
        transition: all 0.3s ease;
    }
    .nav-pills .nav-link.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    .tab-content {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        min-height: 500px;
    }
    .section-card {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }
    .section-card:hover {
        border-color: #667eea;
        box-shadow: 0 2px 10px rgba(102, 126, 234, 0.1);
    }
    .section-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-weight: 600;
    }
    .dynamic-item {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        position: relative;
    }
    .remove-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        color: white !important;
        font-size: 14px !important;
        font-weight: 600;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px !important;
        border-radius: 6px;
    }
    .remove-btn:hover {
        color: white !important;
        background-color: #c82333 !important;
        opacity: 1;
    }
    .hide-remove {
        display: none !important;
    }
    .add-more-btn {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: white !important;
        padding: 10px 25px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .add-more-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        color: white !important;
    }
    .image-preview {
        max-width: 200px;
        max-height: 200px;
        border-radius: 10px;
        margin-top: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    .image-wrapper {
        position: relative;
        display: inline-block;
        margin-top: 10px;
    }
    .image-remove-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #dc3545;
        color: white;
        border: 2px solid white;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
        line-height: 1;
        transition: all 0.3s ease;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
    .image-remove-btn:hover {
        background: #c82333;
        transform: scale(1.1);
    }
    .btn-save {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white !important;
        padding: 12px 40px;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        color: white !important;
    }
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }
    .required-star {
        color: #dc3545;
    }
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-4">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <i class="mdi mdi-city me-2"></i>
                    Explore City: {{ $city->name }}, {{ $city->country }}
                </span>
                <a href="{{ route('cities.index') }}" class="btn btn-secondary">
                    <i class="mdi mdi-arrow-left me-1"></i> Back to Cities
                </a>
            </h5>
            
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('cities.storeExploration', Crypt::encrypt($city->city_id)) }}" method="POST" enctype="multipart/form-data" id="explorationForm">
                    @csrf
                    
                    <!-- Navigation Pills -->
                    <ul class="nav nav-pills mb-4" id="explorationTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview" type="button" role="tab">
                                <i class="mdi mdi-information-outline me-1"></i> Overview
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="attractions-tab" data-bs-toggle="pill" data-bs-target="#attractions" type="button" role="tab">
                                <i class="mdi mdi-castle me-1"></i> Attractions
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="food-tab" data-bs-toggle="pill" data-bs-target="#food" type="button" role="tab">
                                <i class="mdi mdi-food me-1"></i> Food & Cuisine
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="stay-tab" data-bs-toggle="pill" data-bs-target="#stay" type="button" role="tab">
                                <i class="mdi mdi-hotel me-1"></i> Stay
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="transport-tab" data-bs-toggle="pill" data-bs-target="#transport" type="button" role="tab">
                                <i class="mdi mdi-train-car me-1"></i> Transportation
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="timing-tab" data-bs-toggle="pill" data-bs-target="#timing" type="button" role="tab">
                                <i class="mdi mdi-calendar-clock me-1"></i> Best Time
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="shopping-tab" data-bs-toggle="pill" data-bs-target="#shopping" type="button" role="tab">
                                <i class="mdi mdi-shopping me-1"></i> Shopping
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="emergency-tab" data-bs-toggle="pill" data-bs-target="#emergency" type="button" role="tab">
                                <i class="mdi mdi-hospital-box me-1"></i> Emergency
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="explorationTabContent">
                        
                        <!-- 1. OVERVIEW TAB -->
                        <div class="tab-pane fade show active" id="overview" role="tabpanel">
                            <div class="section-header">
                                <i class="mdi mdi-information-outline me-2"></i>City Overview
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">City Name</label>
                                    <input type="text" class="form-control" name="overview_city_name" 
                                        value="{{ $exploration->overview['city_name'] ?? $city->name }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Population</label>
                                    <input type="text" class="form-control" name="overview_population" 
                                        value="{{ $exploration->overview['population'] ?? '' }}" placeholder="e.g., 10 million">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Local Language</label>
                                    <input type="text" class="form-control" name="overview_language" 
                                        value="{{ $exploration->overview['local_language'] ?? '' }}" placeholder="e.g., English, Hindi">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Currency</label>
                                    <input type="text" class="form-control" name="overview_currency" 
                                        value="{{ $exploration->overview['currency'] ?? '' }}" placeholder="e.g., USD, INR">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Time Zone</label>
                                    <input type="text" class="form-control" name="overview_timezone" 
                                        value="{{ $exploration->overview['time_zone'] ?? '' }}" placeholder="e.g., GMT+5:30">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">City Image</label>
                                    <input type="file" class="form-control" name="overview_image" accept="image/*" onchange="previewImage(this, 'overview-preview', 'overview-wrapper')" id="overview-image-input">
                                    <input type="hidden" name="remove_overview_image" id="remove-overview-image" value="0">
                                    @if($exploration && isset($exploration->overview['image']))
                                        <input type="hidden" name="existing_overview_image" value="{{ $exploration->overview['image'] }}">
                                        <div class="image-wrapper" id="overview-wrapper">
                                            <img src="{{ $exploration->overview['image'] }}" class="image-preview" id="overview-preview">
                                            <span class="image-remove-btn" onclick="removeImage('overview-preview', 'overview-wrapper', 'overview-image-input', 'remove-overview-image')" title="Remove image">×</span>
                                        </div>
                                    @else
                                        <div class="image-wrapper d-none" id="overview-wrapper">
                                            <img src="#" class="image-preview" id="overview-preview">
                                            <span class="image-remove-btn" onclick="removeImage('overview-preview', 'overview-wrapper', 'overview-image-input', 'remove-overview-image')" title="Remove image">×</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Short Description</label>
                                <textarea class="form-control" name="overview_description" rows="3" 
                                    placeholder="Brief description of the city">{{ $exploration->overview['short_description'] ?? '' }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Best Known For</label>
                                <textarea class="form-control" name="overview_known_for" rows="2" 
                                    placeholder="What is this city famous for?">{{ $exploration->overview['best_known_for'] ?? '' }}</textarea>
                            </div>
                        </div>

                        <!-- 2. ATTRACTIONS TAB -->
                        <div class="tab-pane fade" id="attractions" role="tabpanel">
                            <div class="section-header">
                                <i class="mdi mdi-castle me-2"></i>Top Attractions
                            </div>

                            <div id="attractions-container">
                                @if($exploration && isset($exploration->attractions) && count($exploration->attractions) > 0)
                                    @foreach($exploration->attractions as $index => $attraction)
                                        <div class="dynamic-item attraction-item">
                                            <button type="button" class="btn btn-danger btn-sm remove-btn remove-attraction {{ $index == 0 ? 'hide-remove' : '' }}" title="Remove">
                                                Remove
                                            </button>
                                            <div class="row">
                                                <div class="col-md-8 mb-3">
                                                    <label class="form-label">Attraction Name</label>
                                                    <select class="form-select attraction-select select2" name="attraction_name[]" data-placeholder="Select or search attraction">
                                                        <option value="">Select attraction</option>
                                                        @foreach($attractions as $attr)
                                                            <option value="{{ $attr->name }}" {{ ($attraction['name'] ?? '') == $attr->name ? 'selected' : '' }}>
                                                                {{ $attr->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Image</label>
                                                    <input type="file" class="form-control" name="attraction_image[{{ $index }}]" accept="image/*" 
                                                           id="attraction-image-{{ $index }}" 
                                                           onchange="previewImage(this, 'attraction-preview-{{ $index }}', 'attraction-wrapper-{{ $index }}')">
                                                    <input type="hidden" name="remove_attraction_image[{{ $index }}]" id="remove-attraction-image-{{ $index }}" value="0">
                                                    @if(isset($attraction['image']))
                                                        <input type="hidden" name="existing_attraction_image[{{ $index }}]" value="{{ $attraction['image'] }}">
                                                        <div class="image-wrapper" id="attraction-wrapper-{{ $index }}">
                                                            <img src="{{ $attraction['image'] }}" class="image-preview" id="attraction-preview-{{ $index }}">
                                                            <span class="image-remove-btn" onclick="removeImage('attraction-preview-{{ $index }}', 'attraction-wrapper-{{ $index }}', 'attraction-image-{{ $index }}', 'remove-attraction-image-{{ $index }}')" title="Remove image">×</span>
                                                        </div>
                                                    @else
                                                        <div class="image-wrapper d-none" id="attraction-wrapper-{{ $index }}">
                                                            <img src="#" class="image-preview" id="attraction-preview-{{ $index }}">
                                                            <span class="image-remove-btn" onclick="removeImage('attraction-preview-{{ $index }}', 'attraction-wrapper-{{ $index }}', 'attraction-image-{{ $index }}', 'remove-attraction-image-{{ $index }}')" title="Remove image">×</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="dynamic-item attraction-item">
                                        <button type="button" class="btn btn-danger btn-sm remove-btn remove-attraction hide-remove" title="Remove">
                                            Remove
                                        </button>
                                        <div class="row">
                                            <div class="col-md-8 mb-3">
                                                <label class="form-label">Attraction Name</label>
                                                <select class="form-select attraction-select select2" name="attraction_name[]" data-placeholder="Select or search attraction">
                                                    <option value="">Select attraction</option>
                                                    @foreach($attractions as $attr)
                                                        <option value="{{ $attr->name }}">{{ $attr->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Image</label>
                                                <input type="file" class="form-control" name="attraction_image[0]" accept="image/*" 
                                                       id="attraction-image-0" 
                                                       onchange="previewImage(this, 'attraction-preview-0', 'attraction-wrapper-0')">
                                                <input type="hidden" name="remove_attraction_image[0]" id="remove-attraction-image-0" value="0">
                                                <div class="image-wrapper d-none" id="attraction-wrapper-0">
                                                    <img src="#" class="image-preview" id="attraction-preview-0">
                                                    <span class="image-remove-btn" onclick="removeImage('attraction-preview-0', 'attraction-wrapper-0', 'attraction-image-0', 'remove-attraction-image-0')" title="Remove image">×</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <button type="button" class="btn add-more-btn" onclick="addAttraction()">
                                <i class="mdi mdi-plus-circle me-1"></i> Add More Attraction
                            </button>
                        </div>

                        <!-- 3. FOOD & CUISINE TAB -->
                        <div class="tab-pane fade" id="food" role="tabpanel">
                            <div class="section-header">
                                <i class="mdi mdi-food me-2"></i>Food & Cuisine
                            </div>

                            <!-- Famous Dishes -->
                            <div class="section-card">
                                <h6 class="mb-3"><i class="mdi mdi-food-fork-drink me-2"></i>Famous Local Dishes</h6>
                                <div id="dishes-container">
                                    @if($exploration && isset($exploration->food_cuisine['famous_dishes']) && count($exploration->food_cuisine['famous_dishes']) > 0)
                                        @php $dishIndex = 0; @endphp
                                        @foreach($exploration->food_cuisine['famous_dishes'] as $name => $description)
                                            <div class="dynamic-item dish-item">
                                                <button type="button" class="btn btn-danger btn-sm remove-btn remove-dish {{ $dishIndex == 0 ? 'hide-remove' : '' }}" title="Remove">
                                                    Remove
                                                </button>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Dish Name</label>
                                                        <input type="text" class="form-control" name="food_dish_name[]" value="{{ $name }}">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Description</label>
                                                        <input type="text" class="form-control" name="food_dish_description[]" value="{{ $description }}">
                                                    </div>
                                                </div>
                                            </div>
                                            @php $dishIndex++; @endphp
                                        @endforeach
                                    @else
                                        <div class="dynamic-item dish-item">
                                            <button type="button" class="btn btn-danger btn-sm remove-btn remove-dish hide-remove" title="Remove">
                                                Remove
                                            </button>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Dish Name</label>
                                                    <input type="text" class="form-control" name="food_dish_name[]" placeholder="e.g., Biryani">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Description</label>
                                                    <input type="text" class="form-control" name="food_dish_description[]" placeholder="Brief description">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="addDish()">
                                    <i class="mdi mdi-plus me-1"></i> Add Dish
                                </button>
                            </div>

                            <!-- Top Restaurants -->
                            <div class="section-card">
                                <h6 class="mb-3"><i class="mdi mdi-silverware-fork-knife me-2"></i>Top Restaurants</h6>
                                <div id="restaurants-container">
                                    @if($exploration && isset($exploration->food_cuisine['top_restaurants']) && count($exploration->food_cuisine['top_restaurants']) > 0)
                                        @foreach($exploration->food_cuisine['top_restaurants'] as $index => $restaurant)
                                            <div class="dynamic-item restaurant-item">
                                                <button type="button" class="btn btn-danger btn-sm remove-btn remove-restaurant {{ $index == 0 ? 'hide-remove' : '' }}" title="Remove">
                                                    Remove
                                                </button>
                                                <div class="row">
                                                    <div class="col-md-8 mb-3">
                                                        <label class="form-label">Restaurant Name</label>
                                                        <select class="form-select restaurant-select select2" name="restaurant_name[]" data-placeholder="Select or search restaurant">
                                                            <option value="">Select restaurant</option>
                                                            @foreach($restaurants as $restaurantOption)
                                                                <option value="{{ $restaurantOption->name }}" {{ ($restaurant['name'] ?? '') == $restaurantOption->name ? 'selected' : '' }}>
                                                                    {{ $restaurantOption->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label">Image</label>
                                                        <input type="file" class="form-control" name="restaurant_image[{{ $index }}]" accept="image/*" 
                                                               id="restaurant-image-{{ $index }}" 
                                                               onchange="previewImage(this, 'restaurant-preview-{{ $index }}', 'restaurant-wrapper-{{ $index }}')">
                                                        <input type="hidden" name="remove_restaurant_image[{{ $index }}]" id="remove-restaurant-image-{{ $index }}" value="0">
                                                        @if(isset($restaurant['image']))
                                                            <input type="hidden" name="existing_restaurant_image[{{ $index }}]" value="{{ $restaurant['image'] }}">
                                                            <div class="image-wrapper" id="restaurant-wrapper-{{ $index }}">
                                                                <img src="{{ $restaurant['image'] }}" class="image-preview" id="restaurant-preview-{{ $index }}" style="max-width: 100px;">
                                                                <span class="image-remove-btn" onclick="removeImage('restaurant-preview-{{ $index }}', 'restaurant-wrapper-{{ $index }}', 'restaurant-image-{{ $index }}', 'remove-restaurant-image-{{ $index }}')" title="Remove image">×</span>
                                                            </div>
                                                        @else
                                                            <div class="image-wrapper d-none" id="restaurant-wrapper-{{ $index }}">
                                                                <img src="#" class="image-preview" id="restaurant-preview-{{ $index }}" style="max-width: 100px;">
                                                                <span class="image-remove-btn" onclick="removeImage('restaurant-preview-{{ $index }}', 'restaurant-wrapper-{{ $index }}', 'restaurant-image-{{ $index }}', 'remove-restaurant-image-{{ $index }}')" title="Remove image">×</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="dynamic-item restaurant-item">
                                            <button type="button" class="btn btn-danger btn-sm remove-btn remove-restaurant hide-remove" title="Remove">
                                                Remove
                                            </button>
                                            <div class="row">
                                                <div class="col-md-8 mb-3">
                                                    <label class="form-label">Restaurant Name</label>
                                                    <select class="form-select restaurant-select select2" name="restaurant_name[]" data-placeholder="Select or search restaurant">
                                                        <option value="">Select restaurant</option>
                                                        @foreach($restaurants as $restaurantOption)
                                                            <option value="{{ $restaurantOption->name }}">{{ $restaurantOption->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Image</label>
                                                    <input type="file" class="form-control" name="restaurant_image[0]" accept="image/*" 
                                                           id="restaurant-image-0" 
                                                           onchange="previewImage(this, 'restaurant-preview-0', 'restaurant-wrapper-0')">
                                                    <input type="hidden" name="remove_restaurant_image[0]" id="remove-restaurant-image-0" value="0">
                                                    <div class="image-wrapper d-none" id="restaurant-wrapper-0">
                                                        <img src="#" class="image-preview" id="restaurant-preview-0" style="max-width: 100px;">
                                                        <span class="image-remove-btn" onclick="removeImage('restaurant-preview-0', 'restaurant-wrapper-0', 'restaurant-image-0', 'remove-restaurant-image-0')" title="Remove image">×</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="addRestaurant()">
                                    <i class="mdi mdi-plus me-1"></i> Add Restaurant
                                </button>
                            </div>

                            <!-- Street Spots -->
                            <div class="section-card">
                                <h6 class="mb-3"><i class="mdi mdi-food-variant me-2"></i>Street Food Spots</h6>
                                <div id="street-spots-container">
                                    @if($exploration && isset($exploration->food_cuisine['street_spots']) && count($exploration->food_cuisine['street_spots']) > 0)
                                        @php $spotIndex = 0; @endphp
                                        @foreach($exploration->food_cuisine['street_spots'] as $spot)
                                            <div class="dynamic-item street-spot-item">
                                                <button type="button" class="btn btn-danger btn-sm remove-btn remove-street-spot {{ $spotIndex == 0 ? 'hide-remove' : '' }}" title="Remove">
                                                    Remove
                                                </button>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Spot Name</label>
                                                        <input type="text" class="form-control" name="street_spot_name[]" value="{{ $spot['name'] ?? '' }}">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Description</label>
                                                        <input type="text" class="form-control" name="street_spot_description[]" value="{{ $spot['description'] ?? '' }}">
                                                    </div>
                                                </div>
                                            </div>
                                            @php $spotIndex++; @endphp
                                        @endforeach
                                    @else
                                        <div class="dynamic-item street-spot-item">
                                            <button type="button" class="btn btn-danger btn-sm remove-btn remove-street-spot hide-remove" title="Remove">
                                                Remove
                                            </button>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Spot Name</label>
                                                    <input type="text" class="form-control" name="street_spot_name[]" placeholder="Street food location">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Description</label>
                                                    <input type="text" class="form-control" name="street_spot_description[]" placeholder="What's special here">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="addStreetSpot()">
                                    <i class="mdi mdi-plus me-1"></i> Add Street Spot
                                </button>
                            </div>

                            <!-- Food Image -->
                            <div class="section-card">
                                <label class="form-label">Representative Food Image</label>
                                <input type="file" class="form-control" name="food_image" accept="image/*" onchange="previewImage(this, 'food-preview', 'food-wrapper')" id="food-image-input">
                                <input type="hidden" name="remove_food_image" id="remove-food-image" value="0">
                                @if($exploration && isset($exploration->food_cuisine['image']))
                                    <input type="hidden" name="existing_food_image" value="{{ $exploration->food_cuisine['image'] }}">
                                    <div class="image-wrapper" id="food-wrapper">
                                        <img src="{{ $exploration->food_cuisine['image'] }}" class="image-preview" id="food-preview">
                                        <span class="image-remove-btn" onclick="removeImage('food-preview', 'food-wrapper', 'food-image-input', 'remove-food-image')" title="Remove image">×</span>
                                    </div>
                                @else
                                    <div class="image-wrapper d-none" id="food-wrapper">
                                        <img src="#" class="image-preview" id="food-preview">
                                        <span class="image-remove-btn" onclick="removeImage('food-preview', 'food-wrapper', 'food-image-input', 'remove-food-image')" title="Remove image">×</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- 4. STAY & ACCOMMODATION TAB -->
                        <div class="tab-pane fade" id="stay" role="tabpanel">
                            <div class="section-header">
                                <i class="mdi mdi-hotel me-2"></i>Stay & Accommodation
                            </div>

                            <div id="hotels-container">
                                @if($exploration && isset($exploration->accommodation) && count($exploration->accommodation) > 0)
                                    @foreach($exploration->accommodation as $index => $hotel)
                                        <div class="dynamic-item hotel-item">
                                            <button type="button" class="btn btn-danger btn-sm remove-btn remove-hotel {{ $index == 0 ? 'hide-remove' : '' }}" title="Remove">
                                                Remove
                                            </button>
                                            <div class="row">
                                                <div class="col-md-8 mb-3">
                                                    <label class="form-label">Hotel Name</label>
                                                    <select class="form-select hotel-select select2" name="hotel_name[]" data-placeholder="Select or search hotel">
                                                        <option value="">Select hotel</option>
                                                        @foreach($hotels as $hotelOption)
                                                            <option value="{{ $hotelOption->name }}" {{ ($hotel['name'] ?? '') == $hotelOption->name ? 'selected' : '' }}>
                                                                {{ $hotelOption->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Image</label>
                                                    <input type="file" class="form-control" name="hotel_image[{{ $index }}]" accept="image/*" 
                                                           id="hotel-image-{{ $index }}" 
                                                           onchange="previewImage(this, 'hotel-preview-{{ $index }}', 'hotel-wrapper-{{ $index }}')">
                                                    <input type="hidden" name="remove_hotel_image[{{ $index }}]" id="remove-hotel-image-{{ $index }}" value="0">
                                                    @if(isset($hotel['image']))
                                                        <input type="hidden" name="existing_hotel_image[{{ $index }}]" value="{{ $hotel['image'] }}">
                                                        <div class="image-wrapper" id="hotel-wrapper-{{ $index }}">
                                                            <img src="{{ $hotel['image'] }}" class="image-preview" id="hotel-preview-{{ $index }}" style="max-width: 100px;">
                                                            <span class="image-remove-btn" onclick="removeImage('hotel-preview-{{ $index }}', 'hotel-wrapper-{{ $index }}', 'hotel-image-{{ $index }}', 'remove-hotel-image-{{ $index }}')" title="Remove image">×</span>
                                                        </div>
                                                    @else
                                                        <div class="image-wrapper d-none" id="hotel-wrapper-{{ $index }}">
                                                            <img src="#" class="image-preview" id="hotel-preview-{{ $index }}" style="max-width: 100px;">
                                                            <span class="image-remove-btn" onclick="removeImage('hotel-preview-{{ $index }}', 'hotel-wrapper-{{ $index }}', 'hotel-image-{{ $index }}', 'remove-hotel-image-{{ $index }}')" title="Remove image">×</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="dynamic-item hotel-item">
                                        <button type="button" class="btn btn-danger btn-sm remove-btn remove-hotel hide-remove" title="Remove">
                                            Remove
                                        </button>
                                        <div class="row">
                                            <div class="col-md-8 mb-3">
                                                <label class="form-label">Hotel Name</label>
                                                <select class="form-select hotel-select select2" name="hotel_name[]" data-placeholder="Select or search hotel">
                                                    <option value="">Select hotel</option>
                                                    @foreach($hotels as $hotelOption)
                                                        <option value="{{ $hotelOption->name }}">{{ $hotelOption->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Image</label>
                                                <input type="file" class="form-control" name="hotel_image[0]" accept="image/*" 
                                                       id="hotel-image-0" 
                                                       onchange="previewImage(this, 'hotel-preview-0', 'hotel-wrapper-0')">
                                                <input type="hidden" name="remove_hotel_image[0]" id="remove-hotel-image-0" value="0">
                                                <div class="image-wrapper d-none" id="hotel-wrapper-0">
                                                    <img src="#" class="image-preview" id="hotel-preview-0" style="max-width: 100px;">
                                                    <span class="image-remove-btn" onclick="removeImage('hotel-preview-0', 'hotel-wrapper-0', 'hotel-image-0', 'remove-hotel-image-0')" title="Remove image">×</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <button type="button" class="btn add-more-btn" onclick="addHotel()">
                                <i class="mdi mdi-plus-circle me-1"></i> Add More Hotel
                            </button>
                        </div>

                        <!-- 5. TRANSPORTATION TAB -->
                        <div class="tab-pane fade" id="transport" role="tabpanel">
                            <div class="section-header">
                                <i class="mdi mdi-train-car me-2"></i>Transportation
                            </div>

                            <!-- Airports -->
                            <div class="section-card">
                                <h6 class="mb-3"><i class="mdi mdi-airplane me-2"></i>Nearest Airports</h6>
                                <div id="airports-container">
                                    @if($exploration && isset($exploration->transportation['airports']) && count($exploration->transportation['airports']) > 0)
                                        @php $airportIndex = 0; @endphp
                                        @foreach($exploration->transportation['airports'] as $airport)
                                            <div class="dynamic-item airport-item">
                                                <button type="button" class="btn btn-danger btn-sm remove-btn remove-airport {{ $airportIndex == 0 ? 'hide-remove' : '' }}" title="Remove">
                                                    Remove
                                                </button>
                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label">Airport Name</label>
                                                        <input type="text" class="form-control" name="airport_name[]" value="{{ $airport['name'] ?? '' }}">
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label">Airport Code</label>
                                                        <input type="text" class="form-control" name="airport_code[]" value="{{ $airport['code'] ?? '' }}" placeholder="e.g., JFK">
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label">Distance from City</label>
                                                        <input type="text" class="form-control" name="airport_distance[]" value="{{ $airport['distance'] ?? '' }}" placeholder="e.g., 15 km">
                                                    </div>
                                                </div>
                                            </div>
                                            @php $airportIndex++; @endphp
                                        @endforeach
                                    @else
                                        <div class="dynamic-item airport-item">
                                            <button type="button" class="btn btn-danger btn-sm remove-btn remove-airport hide-remove" title="Remove">
                                                Remove
                                            </button>
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Airport Name</label>
                                                    <input type="text" class="form-control" name="airport_name[]" placeholder="Airport name">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Airport Code</label>
                                                    <input type="text" class="form-control" name="airport_code[]" placeholder="e.g., JFK">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Distance from City</label>
                                                    <input type="text" class="form-control" name="airport_distance[]" placeholder="e.g., 15 km">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="addAirport()">
                                    <i class="mdi mdi-plus me-1"></i> Add Airport
                                </button>
                            </div>

                            <!-- Railway Stations -->
                            <div class="section-card">
                                <h6 class="mb-3"><i class="mdi mdi-train me-2"></i>Railway Stations</h6>
                                <div id="railways-container">
                                    @if($exploration && isset($exploration->transportation['railway_stations']) && count($exploration->transportation['railway_stations']) > 0)
                                        @php $railwayIndex = 0; @endphp
                                        @foreach($exploration->transportation['railway_stations'] as $railway)
                                            <div class="dynamic-item railway-item">
                                                <button type="button" class="btn btn-danger btn-sm remove-btn remove-railway {{ $railwayIndex == 0 ? 'hide-remove' : '' }}" title="Remove">
                                                    Remove
                                                </button>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Station Name</label>
                                                        <input type="text" class="form-control" name="railway_name[]" value="{{ $railway['name'] ?? '' }}">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Distance</label>
                                                        <input type="text" class="form-control" name="railway_distance[]" value="{{ $railway['distance'] ?? '' }}" placeholder="e.g., 5 km">
                                                    </div>
                                                </div>
                                            </div>
                                            @php $railwayIndex++; @endphp
                                        @endforeach
                                    @else
                                        <div class="dynamic-item railway-item">
                                            <button type="button" class="btn btn-danger btn-sm remove-btn remove-railway hide-remove" title="Remove">
                                                Remove
                                            </button>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Station Name</label>
                                                    <input type="text" class="form-control" name="railway_name[]" placeholder="Railway station name">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Distance</label>
                                                    <input type="text" class="form-control" name="railway_distance[]" placeholder="e.g., 5 km">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="addRailway()">
                                    <i class="mdi mdi-plus me-1"></i> Add Railway Station
                                </button>
                            </div>

                            <!-- Local Transport -->
                            <div class="section-card">
                                <h6 class="mb-3"><i class="mdi mdi-bus me-2"></i>Getting Around (Local Transport)</h6>
                                <div id="transport-options-container">
                                    @if($exploration && isset($exploration->transportation['local_transport']) && count($exploration->transportation['local_transport']) > 0)
                                        @php $transportIndex = 0; @endphp
                                        @foreach($exploration->transportation['local_transport'] as $type => $description)
                                            <div class="dynamic-item transport-item">
                                                <button type="button" class="btn btn-danger btn-sm remove-btn remove-transport {{ $transportIndex == 0 ? 'hide-remove' : '' }}" title="Remove">
                                                    Remove
                                                </button>
                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label">Transport Type</label>
                                                        <select class="form-select" name="transport_type[]">
                                                            <option value="">Select</option>
                                                            <option value="Bus" {{ $type == 'Bus' ? 'selected' : '' }}>Bus</option>
                                                            <option value="Metro" {{ $type == 'Metro' ? 'selected' : '' }}>Metro</option>
                                                            <option value="Taxi/Cab" {{ $type == 'Taxi/Cab' ? 'selected' : '' }}>Taxi/Cab</option>
                                                            <option value="Auto-rickshaw" {{ $type == 'Auto-rickshaw' ? 'selected' : '' }}>Auto-rickshaw</option>
                                                            <option value="Bike Rental" {{ $type == 'Bike Rental' ? 'selected' : '' }}>Bike Rental</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-8 mb-3">
                                                        <label class="form-label">Description</label>
                                                        <input type="text" class="form-control" name="transport_description[]" value="{{ $description }}" placeholder="Details about this transport">
                                                    </div>
                                                </div>
                                            </div>
                                            @php $transportIndex++; @endphp
                                        @endforeach
                                    @else
                                        <div class="dynamic-item transport-item">
                                            <button type="button" class="btn btn-danger btn-sm remove-btn remove-transport hide-remove" title="Remove">
                                                Remove
                                            </button>
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Transport Type</label>
                                                    <select class="form-select" name="transport_type[]">
                                                        <option value="">Select</option>
                                                        <option value="Bus">Bus</option>
                                                        <option value="Metro">Metro</option>
                                                        <option value="Taxi/Cab">Taxi/Cab</option>
                                                        <option value="Auto-rickshaw">Auto-rickshaw</option>
                                                        <option value="Bike Rental">Bike Rental</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-8 mb-3">
                                                    <label class="form-label">Description</label>
                                                    <input type="text" class="form-control" name="transport_description[]" placeholder="Details about this transport">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="addTransportOption()">
                                    <i class="mdi mdi-plus me-1"></i> Add Transport Option
                                </button>
                            </div>
                        </div>

                        <!-- 6. BEST TIME TO VISIT TAB -->
                        <div class="tab-pane fade" id="timing" role="tabpanel">
                            <div class="section-header">
                                <i class="mdi mdi-calendar-clock me-2"></i>Best Time to Visit
                            </div>

                            <!-- Seasonal Highlights -->
                            <div class="section-card">
                                <h6 class="mb-3"><i class="mdi mdi-weather-sunny me-2"></i>Seasonal Highlights</h6>
                                <div id="seasons-container">
                                    @if($exploration && isset($exploration->best_time_visit['seasonal_highlights']) && count($exploration->best_time_visit['seasonal_highlights']) > 0)
                                        @php $seasonIndex = 0; @endphp
                                        @foreach($exploration->best_time_visit['seasonal_highlights'] as $season)
                                            <div class="dynamic-item season-item">
                                                <button type="button" class="btn btn-danger btn-sm remove-btn remove-season {{ $seasonIndex == 0 ? 'hide-remove' : '' }}" title="Remove">
                                                    Remove
                                                </button>
                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label">Period</label>
                                                        <input type="text" class="form-control" name="season_period[]" value="{{ $season['period'] ?? '' }}" placeholder="e.g., November-February">
                                                    </div>
                                                    <div class="col-md-8 mb-3">
                                                        <label class="form-label">Description</label>
                                                        <input type="text" class="form-control" name="season_description[]" value="{{ $season['description'] ?? '' }}" placeholder="e.g., Pleasant winter weather">
                                                    </div>
                                                </div>
                                            </div>
                                            @php $seasonIndex++; @endphp
                                        @endforeach
                                    @else
                                        <div class="dynamic-item season-item">
                                            <button type="button" class="btn btn-danger btn-sm remove-btn remove-season hide-remove" title="Remove">
                                                Remove
                                            </button>
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Period</label>
                                                    <input type="text" class="form-control" name="season_period[]" placeholder="e.g., November-February">
                                                </div>
                                                <div class="col-md-8 mb-3">
                                                    <label class="form-label">Description</label>
                                                    <input type="text" class="form-control" name="season_description[]" placeholder="e.g., Pleasant winter weather">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="addSeason()">
                                    <i class="mdi mdi-plus me-1"></i> Add Season
                                </button>
                            </div>

                            <!-- Festival Periods -->
                            <div class="section-card">
                                <h6 class="mb-3"><i class="mdi mdi-party-popper me-2"></i>Festival Periods</h6>
                                <div id="festivals-container">
                                    @if($exploration && isset($exploration->best_time_visit['festival_periods']) && count($exploration->best_time_visit['festival_periods']) > 0)
                                        @php $festivalIndex = 0; @endphp
                                        @foreach($exploration->best_time_visit['festival_periods'] as $festival)
                                            <div class="dynamic-item festival-item">
                                                <button type="button" class="btn btn-danger btn-sm remove-btn remove-festival {{ $festivalIndex == 0 ? 'hide-remove' : '' }}" title="Remove">
                                                    Remove
                                                </button>
                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label">Festival Name</label>
                                                        <input type="text" class="form-control" name="festival_name[]" value="{{ $festival['name'] ?? '' }}">
                                                    </div>
                                                    <div class="col-md-3 mb-3">
                                                        <label class="form-label">Period</label>
                                                        <input type="text" class="form-control" name="festival_period[]" value="{{ $festival['period'] ?? '' }}" placeholder="e.g., October">
                                                    </div>
                                                    <div class="col-md-5 mb-3">
                                                        <label class="form-label">Description</label>
                                                        <input type="text" class="form-control" name="festival_description[]" value="{{ $festival['description'] ?? '' }}">
                                                    </div>
                                                </div>
                                            </div>
                                            @php $festivalIndex++; @endphp
                                        @endforeach
                                    @else
                                        <div class="dynamic-item festival-item">
                                            <button type="button" class="btn btn-danger btn-sm remove-btn remove-festival hide-remove" title="Remove">
                                                Remove
                                            </button>
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Festival Name</label>
                                                    <input type="text" class="form-control" name="festival_name[]" placeholder="Festival name">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Period</label>
                                                    <input type="text" class="form-control" name="festival_period[]" placeholder="e.g., October">
                                                </div>
                                                <div class="col-md-5 mb-3">
                                                    <label class="form-label">Description</label>
                                                    <input type="text" class="form-control" name="festival_description[]" placeholder="Cultural significance">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="addFestival()">
                                    <i class="mdi mdi-plus me-1"></i> Add Festival
                                </button>
                            </div>
                        </div>

                        <!-- 7. SHOPPING TAB -->
                        <div class="tab-pane fade" id="shopping" role="tabpanel">
                            <div class="section-header">
                                <i class="mdi mdi-shopping me-2"></i>Shopping
                            </div>

                            <div id="shopping-container">
                                @if($exploration && isset($exploration->shopping) && count($exploration->shopping) > 0)
                                    @foreach($exploration->shopping as $index => $shop)
                                        <div class="dynamic-item shopping-item">
                                            <button type="button" class="btn btn-danger btn-sm remove-btn remove-shopping {{ $index == 0 ? 'hide-remove' : '' }}" title="Remove">
                                                Remove
                                            </button>
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Name</label>
                                                    <input type="text" class="form-control" name="shopping_name[]" value="{{ $shop['name'] ?? '' }}">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Type</label>
                                                    <select class="form-select" name="shopping_type[]">
                                                        <option value="">Select</option>
                                                        <option value="Market" {{ ($shop['type'] ?? '') == 'Market' ? 'selected' : '' }}>Market</option>
                                                        <option value="Mall" {{ ($shop['type'] ?? '') == 'Mall' ? 'selected' : '' }}>Mall</option>
                                                        <option value="Bazaar" {{ ($shop['type'] ?? '') == 'Bazaar' ? 'selected' : '' }}>Bazaar</option>
                                                        <option value="Shopping Street" {{ ($shop['type'] ?? '') == 'Shopping Street' ? 'selected' : '' }}>Shopping Street</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Description</label>
                                                    <input type="text" class="form-control" name="shopping_description[]" value="{{ $shop['description'] ?? '' }}" placeholder="What to buy here">
                                                </div>
                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label">Image</label>
                                                    <input type="file" class="form-control" name="shopping_image[{{ $index }}]" accept="image/*" 
                                                           id="shopping-image-{{ $index }}" 
                                                           onchange="previewImage(this, 'shopping-preview-{{ $index }}', 'shopping-wrapper-{{ $index }}')">
                                                    <input type="hidden" name="remove_shopping_image[{{ $index }}]" id="remove-shopping-image-{{ $index }}" value="0">
                                                    @if(isset($shop['image']))
                                                        <input type="hidden" name="existing_shopping_image[{{ $index }}]" value="{{ $shop['image'] }}">
                                                        <div class="image-wrapper" id="shopping-wrapper-{{ $index }}">
                                                            <img src="{{ $shop['image'] }}" class="image-preview" id="shopping-preview-{{ $index }}" style="max-width: 100px;">
                                                            <span class="image-remove-btn" onclick="removeImage('shopping-preview-{{ $index }}', 'shopping-wrapper-{{ $index }}', 'shopping-image-{{ $index }}', 'remove-shopping-image-{{ $index }}')" title="Remove image">×</span>
                                                        </div>
                                                    @else
                                                        <div class="image-wrapper d-none" id="shopping-wrapper-{{ $index }}">
                                                            <img src="#" class="image-preview" id="shopping-preview-{{ $index }}" style="max-width: 100px;">
                                                            <span class="image-remove-btn" onclick="removeImage('shopping-preview-{{ $index }}', 'shopping-wrapper-{{ $index }}', 'shopping-image-{{ $index }}', 'remove-shopping-image-{{ $index }}')" title="Remove image">×</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="dynamic-item shopping-item">
                                        <button type="button" class="btn btn-danger btn-sm remove-btn remove-shopping hide-remove" title="Remove">
                                            Remove
                                        </button>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Name</label>
                                                <input type="text" class="form-control" name="shopping_name[]" placeholder="Market/Mall name">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Type</label>
                                                <select class="form-select" name="shopping_type[]">
                                                    <option value="">Select</option>
                                                    <option value="Market">Market</option>
                                                    <option value="Mall">Mall</option>
                                                    <option value="Bazaar">Bazaar</option>
                                                    <option value="Shopping Street">Shopping Street</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Description</label>
                                                <input type="text" class="form-control" name="shopping_description[]" placeholder="What to buy here">
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <label class="form-label">Image</label>
                                                <input type="file" class="form-control" name="shopping_image[0]" accept="image/*" 
                                                       id="shopping-image-0" 
                                                       onchange="previewImage(this, 'shopping-preview-0', 'shopping-wrapper-0')">
                                                <input type="hidden" name="remove_shopping_image[0]" id="remove-shopping-image-0" value="0">
                                                <div class="image-wrapper d-none" id="shopping-wrapper-0">
                                                    <img src="#" class="image-preview" id="shopping-preview-0" style="max-width: 100px;">
                                                    <span class="image-remove-btn" onclick="removeImage('shopping-preview-0', 'shopping-wrapper-0', 'shopping-image-0', 'remove-shopping-image-0')" title="Remove image">×</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <button type="button" class="btn add-more-btn" onclick="addShopping()">
                                <i class="mdi mdi-plus-circle me-1"></i> Add Shopping Location
                            </button>
                        </div>

                        <!-- 8. HOSPITALS & EMERGENCY TAB -->
                        <div class="tab-pane fade" id="emergency" role="tabpanel">
                            <div class="section-header">
                                <i class="mdi mdi-hospital-box me-2"></i>Hospitals & Emergency
                            </div>

                            <!-- Hospitals -->
                            <div class="section-card">
                                <h6 class="mb-3"><i class="mdi mdi-hospital-building me-2"></i>Top Nearby Hospitals</h6>
                                <div id="hospitals-container">
                                    @if($exploration && isset($exploration->hospitals_emergency['hospitals']) && count($exploration->hospitals_emergency['hospitals']) > 0)
                                        @php $hospitalIndex = 0; @endphp
                                        @foreach($exploration->hospitals_emergency['hospitals'] as $hospital)
                                            <div class="dynamic-item hospital-item">
                                                <button type="button" class="btn btn-danger btn-sm remove-btn remove-hospital {{ $hospitalIndex == 0 ? 'hide-remove' : '' }}" title="Remove">
                                                    Remove
                                                </button>
                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label">Hospital Name</label>
                                                        <input type="text" class="form-control" name="hospital_name[]" value="{{ $hospital['name'] ?? '' }}">
                                                    </div>
                                                    <div class="col-md-2 mb-3">
                                                        <label class="form-label">Type</label>
                                                        <select class="form-select" name="hospital_type[]">
                                                            <option value="">Select</option>
                                                            <option value="Government" {{ ($hospital['type'] ?? '') == 'Government' ? 'selected' : '' }}>Government</option>
                                                            <option value="Private" {{ ($hospital['type'] ?? '') == 'Private' ? 'selected' : '' }}>Private</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label">Address</label>
                                                        <input type="text" class="form-control" name="hospital_address[]" value="{{ $hospital['address'] ?? '' }}">
                                                    </div>
                                                    <div class="col-md-2 mb-3">
                                                        <label class="form-label">Contact</label>
                                                        <input type="text" class="form-control" name="hospital_contact[]" value="{{ $hospital['contact'] ?? '' }}">
                                                    </div>
                                                </div>
                                            </div>
                                            @php $hospitalIndex++; @endphp
                                        @endforeach
                                    @else
                                        <div class="dynamic-item hospital-item">
                                            <button type="button" class="btn btn-danger btn-sm remove-btn remove-hospital hide-remove" title="Remove">
                                                Remove
                                            </button>
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Hospital Name</label>
                                                    <input type="text" class="form-control" name="hospital_name[]" placeholder="Hospital name">
                                                </div>
                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label">Type</label>
                                                    <select class="form-select" name="hospital_type[]">
                                                        <option value="">Select</option>
                                                        <option value="Government">Government</option>
                                                        <option value="Private">Private</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Address</label>
                                                    <input type="text" class="form-control" name="hospital_address[]" placeholder="Location">
                                                </div>
                                                <div class="col-md-2 mb-3">
                                                    <label class="form-label">Contact</label>
                                                    <input type="text" class="form-control" name="hospital_contact[]" placeholder="Phone number">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="addHospital()">
                                    <i class="mdi mdi-plus me-1"></i> Add Hospital
                                </button>
                            </div>

                            <!-- Pharmacies -->
                            <div class="section-card">
                                <h6 class="mb-3"><i class="mdi mdi-pharmacy me-2"></i>Nearby Pharmacies</h6>
                                <div id="pharmacies-container">
                                    @if($exploration && isset($exploration->hospitals_emergency['pharmacies']) && count($exploration->hospitals_emergency['pharmacies']) > 0)
                                        @php $pharmacyIndex = 0; @endphp
                                        @foreach($exploration->hospitals_emergency['pharmacies'] as $pharmacy)
                                            <div class="dynamic-item pharmacy-item">
                                                <button type="button" class="btn btn-danger btn-sm remove-btn remove-pharmacy {{ $pharmacyIndex == 0 ? 'hide-remove' : '' }}" title="Remove">
                                                    Remove
                                                </button>
                                                <div class="mb-3">
                                                    <label class="form-label">Pharmacy Name</label>
                                                    <input type="text" class="form-control" name="pharmacy_name[]" value="{{ $pharmacy }}">
                                                </div>
                                            </div>
                                            @php $pharmacyIndex++; @endphp
                                        @endforeach
                                    @else
                                        <div class="dynamic-item pharmacy-item">
                                            <button type="button" class="btn btn-danger btn-sm remove-btn remove-pharmacy hide-remove" title="Remove">
                                                Remove
                                            </button>
                                            <div class="mb-3">
                                                <label class="form-label">Pharmacy Name</label>
                                                <input type="text" class="form-control" name="pharmacy_name[]" placeholder="Pharmacy name and location">
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="addPharmacy()">
                                    <i class="mdi mdi-plus me-1"></i> Add Pharmacy
                                </button>
                            </div>

                            <!-- Emergency Numbers -->
                            <div class="section-card">
                                <h6 class="mb-3"><i class="mdi mdi-phone-alert me-2"></i>Emergency Numbers</h6>
                                <div id="emergency-numbers-container">
                                    @if($exploration && isset($exploration->hospitals_emergency['emergency_numbers']) && count($exploration->hospitals_emergency['emergency_numbers']) > 0)
                                        @php $emergencyIndex = 0; @endphp
                                        @foreach($exploration->hospitals_emergency['emergency_numbers'] as $emergency)
                                            <div class="dynamic-item emergency-item">
                                                <button type="button" class="btn btn-danger btn-sm remove-btn remove-emergency {{ $emergencyIndex == 0 ? 'hide-remove' : '' }}" title="Remove">
                                                    Remove
                                                </button>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Service</label>
                                                        <input type="text" class="form-control" name="emergency_service[]" value="{{ $emergency['service'] ?? '' }}" placeholder="e.g., Police, Ambulance">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Number</label>
                                                        <input type="text" class="form-control" name="emergency_number[]" value="{{ $emergency['number'] ?? '' }}" placeholder="Emergency number">
                                                    </div>
                                                </div>
                                            </div>
                                            @php $emergencyIndex++; @endphp
                                        @endforeach
                                    @else
                                        <div class="dynamic-item emergency-item">
                                            <button type="button" class="btn btn-danger btn-sm remove-btn remove-emergency hide-remove" title="Remove">
                                                Remove
                                            </button>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Service</label>
                                                    <input type="text" class="form-control" name="emergency_service[]" placeholder="e.g., Police, Ambulance">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Number</label>
                                                    <input type="text" class="form-control" name="emergency_number[]" placeholder="Emergency number">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="addEmergency()">
                                    <i class="mdi mdi-plus me-1"></i> Add Emergency Number
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- Submit Button -->
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-save">
                            <i class="mdi mdi-content-save me-2"></i>Save Exploration Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Counter for dynamic field indexing
let attractionIndex = {{ $exploration && isset($exploration->attractions) ? count($exploration->attractions) : 1 }};
let hotelIndex = {{ $exploration && isset($exploration->accommodation) ? count($exploration->accommodation) : 1 }};

// Initialize Select2 on page load
$(document).ready(function() {
    $('.attraction-select').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Select or search attraction'
    });
    
    $('.hotel-select').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Select or search hotel'
    });
    
    $('.restaurant-select').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Select or search restaurant'
    });
});

// Image Preview Function
function previewImage(input, previewId, wrapperId) {
    const preview = document.getElementById(previewId);
    const wrapper = wrapperId ? document.getElementById(wrapperId) : null;
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            if (wrapper) {
                wrapper.classList.remove('d-none');
            } else {
                preview.classList.remove('d-none');
            }
            
            // Clear the removal flag when a new file is selected
            // This ensures that if user removed an image and then uploads a new one,
            // the new upload takes precedence
            if (input.id === 'overview-image-input') {
                document.getElementById('remove-overview-image').value = '0';
            } else if (input.id === 'food-image-input') {
                document.getElementById('remove-food-image').value = '0';
            } else if (input.id && input.id.startsWith('attraction-image-')) {
                const index = input.id.replace('attraction-image-', '');
                const removeInput = document.getElementById('remove-attraction-image-' + index);
                if (removeInput) removeInput.value = '0';
            } else if (input.id && input.id.startsWith('restaurant-image-')) {
                const index = input.id.replace('restaurant-image-', '');
                const removeInput = document.getElementById('remove-restaurant-image-' + index);
                if (removeInput) removeInput.value = '0';
            } else if (input.id && input.id.startsWith('hotel-image-')) {
                const index = input.id.replace('hotel-image-', '');
                const removeInput = document.getElementById('remove-hotel-image-' + index);
                if (removeInput) removeInput.value = '0';
            } else if (input.id && input.id.startsWith('shopping-image-')) {
                const index = input.id.replace('shopping-image-', '');
                const removeInput = document.getElementById('remove-shopping-image-' + index);
                if (removeInput) removeInput.value = '0';
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Remove Image Function
function removeImage(previewId, wrapperId, inputId, removeInputId) {
    const preview = document.getElementById(previewId);
    const wrapper = document.getElementById(wrapperId);
    const fileInput = document.getElementById(inputId);
    const removeInput = document.getElementById(removeInputId);
    
    // Clear the image preview
    preview.src = '#';
    
    // Hide the wrapper
    wrapper.classList.add('d-none');
    
    // Clear the file input
    fileInput.value = '';
    
    // Mark for removal
    if (removeInput) {
        removeInput.value = '1';
    }
}

// Remove button handlers using event delegation
document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-attraction')) {
        if (document.querySelectorAll('.attraction-item').length > 1) {
            e.target.closest('.attraction-item').remove();
        } else {
            alert('At least one attraction entry is required.');
        }
    }
    if (e.target.closest('.remove-dish')) {
        if (document.querySelectorAll('.dish-item').length > 1) {
            e.target.closest('.dish-item').remove();
        }
    }
    if (e.target.closest('.remove-restaurant')) {
        if (document.querySelectorAll('.restaurant-item').length > 1) {
            e.target.closest('.restaurant-item').remove();
        }
    }
    if (e.target.closest('.remove-street-spot')) {
        if (document.querySelectorAll('.street-spot-item').length > 1) {
            e.target.closest('.street-spot-item').remove();
        }
    }
    if (e.target.closest('.remove-hotel')) {
        if (document.querySelectorAll('.hotel-item').length > 1) {
            e.target.closest('.hotel-item').remove();
        } else {
            alert('At least one hotel entry is required.');
        }
    }
    if (e.target.closest('.remove-airport')) {
        if (document.querySelectorAll('.airport-item').length > 1) {
            e.target.closest('.airport-item').remove();
        }
    }
    if (e.target.closest('.remove-railway')) {
        if (document.querySelectorAll('.railway-item').length > 1) {
            e.target.closest('.railway-item').remove();
        }
    }
    if (e.target.closest('.remove-transport')) {
        if (document.querySelectorAll('.transport-item').length > 1) {
            e.target.closest('.transport-item').remove();
        }
    }
    if (e.target.closest('.remove-season')) {
        if (document.querySelectorAll('.season-item').length > 1) {
            e.target.closest('.season-item').remove();
        }
    }
    if (e.target.closest('.remove-festival')) {
        if (document.querySelectorAll('.festival-item').length > 1) {
            e.target.closest('.festival-item').remove();
        }
    }
    if (e.target.closest('.remove-shopping')) {
        if (document.querySelectorAll('.shopping-item').length > 1) {
            e.target.closest('.shopping-item').remove();
        }
    }
    if (e.target.closest('.remove-hospital')) {
        if (document.querySelectorAll('.hospital-item').length > 1) {
            e.target.closest('.hospital-item').remove();
        }
    }
    if (e.target.closest('.remove-pharmacy')) {
        if (document.querySelectorAll('.pharmacy-item').length > 1) {
            e.target.closest('.pharmacy-item').remove();
        }
    }
    if (e.target.closest('.remove-emergency')) {
        if (document.querySelectorAll('.emergency-item').length > 1) {
            e.target.closest('.emergency-item').remove();
        }
    }
});

// Add More Functions
function addAttraction() {
    const html = `
        <div class="dynamic-item attraction-item">
            <button type="button" class="btn btn-danger btn-sm remove-btn remove-attraction" title="Remove">
                Remove
            </button>
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label">Attraction Name</label>
                    <select class="form-select attraction-select select2" name="attraction_name[]" data-placeholder="Select or search attraction">
                        <option value="">Select attraction</option>
                        @foreach($attractions as $attr)
                            <option value="{{ $attr->name }}">{{ $attr->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Image</label>
                    <input type="file" class="form-control" name="attraction_image[${attractionIndex}]" accept="image/*">
                </div>
            </div>
        </div>
    `;
    document.getElementById('attractions-container').insertAdjacentHTML('beforeend', html);
    
    // Initialize Select2 for the newly added select element
    $('.attraction-select').last().select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Select or search attraction'
    });
    
    attractionIndex++;
}

function addDish() {
    const html = `
        <div class="dynamic-item dish-item">
            <button type="button" class="btn btn-danger btn-sm remove-btn remove-dish" title="Remove">
                Remove
            </button>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Dish Name</label>
                    <input type="text" class="form-control" name="food_dish_name[]" placeholder="e.g., Biryani">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Description</label>
                    <input type="text" class="form-control" name="food_dish_description[]" placeholder="Brief description">
                </div>
            </div>
        </div>
    `;
    document.getElementById('dishes-container').insertAdjacentHTML('beforeend', html);
}

let restaurantIndex = {{ $exploration && isset($exploration->food_cuisine['top_restaurants']) ? count($exploration->food_cuisine['top_restaurants']) : 1 }};

function addRestaurant() {
    const html = `
        <div class="dynamic-item restaurant-item">
            <button type="button" class="btn btn-danger btn-sm remove-btn remove-restaurant" title="Remove">
                Remove
            </button>
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label">Restaurant Name</label>
                    <select class="form-select restaurant-select select2" name="restaurant_name[]" data-placeholder="Select or search restaurant">
                        <option value="">Select restaurant</option>
                        @foreach($restaurants as $restaurantOption)
                            <option value="{{ $restaurantOption->name }}">{{ $restaurantOption->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Image</label>
                    <input type="file" class="form-control" name="restaurant_image[${restaurantIndex}]" accept="image/*">
                </div>
            </div>
        </div>
    `;
    document.getElementById('restaurants-container').insertAdjacentHTML('beforeend', html);
    
    // Initialize Select2 for the newly added select element
    $('.restaurant-select').last().select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Select or search restaurant'
    });
    
    restaurantIndex++;
}

function addStreetSpot() {
    const html = `
        <div class="dynamic-item street-spot-item">
            <button type="button" class="btn btn-danger btn-sm remove-btn remove-street-spot" title="Remove">
                                                Remove
            </button>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Spot Name</label>
                    <input type="text" class="form-control" name="street_spot_name[]" placeholder="Street food location">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Description</label>
                    <input type="text" class="form-control" name="street_spot_description[]" placeholder="What's special here">
                </div>
            </div>
        </div>
    `;
    document.getElementById('street-spots-container').insertAdjacentHTML('beforeend', html);
}

function addHotel() {
    const html = `
        <div class="dynamic-item hotel-item">
            <button type="button" class="btn btn-danger btn-sm remove-btn remove-hotel" title="Remove">
                                                Remove
            </button>
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label">Hotel Name</label>
                    <select class="form-select hotel-select select2" name="hotel_name[]" data-placeholder="Select or search hotel">
                        <option value="">Select hotel</option>
                        @foreach($hotels as $hotelOption)
                            <option value="{{ $hotelOption->name }}">{{ $hotelOption->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Image</label>
                    <input type="file" class="form-control" name="hotel_image[${hotelIndex}]" accept="image/*">
                </div>
            </div>
        </div>
    `;
    document.getElementById('hotels-container').insertAdjacentHTML('beforeend', html);
    
    // Initialize Select2 for the newly added select element
    $('.hotel-select').last().select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Select or search hotel'
    });
    
    hotelIndex++;
}

function addAirport() {
    const html = `
        <div class="dynamic-item airport-item">
            <button type="button" class="btn btn-danger btn-sm remove-btn remove-airport" title="Remove">
                                                Remove
            </button>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Airport Name</label>
                    <input type="text" class="form-control" name="airport_name[]" placeholder="Airport name">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Airport Code</label>
                    <input type="text" class="form-control" name="airport_code[]" placeholder="e.g., JFK">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Distance from City</label>
                    <input type="text" class="form-control" name="airport_distance[]" placeholder="e.g., 15 km">
                </div>
            </div>
        </div>
    `;
    document.getElementById('airports-container').insertAdjacentHTML('beforeend', html);
}

function addRailway() {
    const html = `
        <div class="dynamic-item railway-item">
            <button type="button" class="btn btn-danger btn-sm remove-btn remove-railway" title="Remove">
                                                Remove
            </button>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Station Name</label>
                    <input type="text" class="form-control" name="railway_name[]" placeholder="Railway station name">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Distance</label>
                    <input type="text" class="form-control" name="railway_distance[]" placeholder="e.g., 5 km">
                </div>
            </div>
        </div>
    `;
    document.getElementById('railways-container').insertAdjacentHTML('beforeend', html);
}

function addTransportOption() {
    const html = `
        <div class="dynamic-item transport-item">
            <button type="button" class="btn btn-danger btn-sm remove-btn remove-transport" title="Remove">
                                                Remove
            </button>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Transport Type</label>
                    <select class="form-select" name="transport_type[]">
                        <option value="">Select</option>
                        <option value="Bus">Bus</option>
                        <option value="Metro">Metro</option>
                        <option value="Taxi/Cab">Taxi/Cab</option>
                        <option value="Auto-rickshaw">Auto-rickshaw</option>
                        <option value="Bike Rental">Bike Rental</option>
                    </select>
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">Description</label>
                    <input type="text" class="form-control" name="transport_description[]" placeholder="Details about this transport">
                </div>
            </div>
        </div>
    `;
    document.getElementById('transport-options-container').insertAdjacentHTML('beforeend', html);
}

function addSeason() {
    const html = `
        <div class="dynamic-item season-item">
            <button type="button" class="btn btn-danger btn-sm remove-btn remove-season" title="Remove">
                                                Remove
            </button>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Period</label>
                    <input type="text" class="form-control" name="season_period[]" placeholder="e.g., November-February">
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">Description</label>
                    <input type="text" class="form-control" name="season_description[]" placeholder="e.g., Pleasant winter weather">
                </div>
            </div>
        </div>
    `;
    document.getElementById('seasons-container').insertAdjacentHTML('beforeend', html);
}

function addFestival() {
    const html = `
        <div class="dynamic-item festival-item">
            <button type="button" class="btn btn-danger btn-sm remove-btn remove-festival" title="Remove">
                                                Remove
            </button>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Festival Name</label>
                    <input type="text" class="form-control" name="festival_name[]" placeholder="Festival name">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Period</label>
                    <input type="text" class="form-control" name="festival_period[]" placeholder="e.g., October">
                </div>
                <div class="col-md-5 mb-3">
                    <label class="form-label">Description</label>
                    <input type="text" class="form-control" name="festival_description[]" placeholder="Cultural significance">
                </div>
            </div>
        </div>
    `;
    document.getElementById('festivals-container').insertAdjacentHTML('beforeend', html);
}

let shoppingIndex = {{ $exploration && isset($exploration->shopping) ? count($exploration->shopping) : 1 }};

function addShopping() {
    const html = `
        <div class="dynamic-item shopping-item">
            <button type="button" class="btn btn-danger btn-sm remove-btn remove-shopping" title="Remove">
                                                Remove
            </button>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" name="shopping_name[]" placeholder="Market/Mall name">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Type</label>
                    <select class="form-select" name="shopping_type[]">
                        <option value="">Select</option>
                        <option value="Market">Market</option>
                        <option value="Mall">Mall</option>
                        <option value="Bazaar">Bazaar</option>
                        <option value="Shopping Street">Shopping Street</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Description</label>
                    <input type="text" class="form-control" name="shopping_description[]" placeholder="What to buy here">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Image</label>
                    <input type="file" class="form-control" name="shopping_image[${shoppingIndex}]" accept="image/*">
                </div>
            </div>
        </div>
    `;
    document.getElementById('shopping-container').insertAdjacentHTML('beforeend', html);
    shoppingIndex++;
}

function addHospital() {
    const html = `
        <div class="dynamic-item hospital-item">
            <button type="button" class="btn btn-danger btn-sm remove-btn remove-hospital" title="Remove">
                                                Remove
            </button>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Hospital Name</label>
                    <input type="text" class="form-control" name="hospital_name[]" placeholder="Hospital name">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Type</label>
                    <select class="form-select" name="hospital_type[]">
                        <option value="">Select</option>
                        <option value="Government">Government</option>
                        <option value="Private">Private</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" class="form-control" name="hospital_address[]" placeholder="Location">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Contact</label>
                    <input type="text" class="form-control" name="hospital_contact[]" placeholder="Phone number">
                </div>
            </div>
        </div>
    `;
    document.getElementById('hospitals-container').insertAdjacentHTML('beforeend', html);
}

function addPharmacy() {
    const html = `
        <div class="dynamic-item pharmacy-item">
            <button type="button" class="btn btn-danger btn-sm remove-btn remove-pharmacy" title="Remove">
                                                Remove
            </button>
            <div class="mb-3">
                <label class="form-label">Pharmacy Name</label>
                <input type="text" class="form-control" name="pharmacy_name[]" placeholder="Pharmacy name and location">
            </div>
        </div>
    `;
    document.getElementById('pharmacies-container').insertAdjacentHTML('beforeend', html);
}

function addEmergency() {
    const html = `
        <div class="dynamic-item emergency-item">
            <button type="button" class="btn btn-danger btn-sm remove-btn remove-emergency" title="Remove">
                                                Remove
            </button>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Service</label>
                    <input type="text" class="form-control" name="emergency_service[]" placeholder="e.g., Police, Ambulance">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Number</label>
                    <input type="text" class="form-control" name="emergency_number[]" placeholder="Emergency number">
                </div>
            </div>
        </div>
    `;
    document.getElementById('emergency-numbers-container').insertAdjacentHTML('beforeend', html);
}

// Form Submission
document.getElementById('explorationForm').addEventListener('submit', function(e) {
    Swal.fire({
        title: 'Saving...',
        text: 'Please wait while we save the city exploration data.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
});

@if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '{{ session('success') }}',
        confirmButtonColor: '#667eea'
    });
@endif

@if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '{{ session('error') }}',
        confirmButtonColor: '#dc3545'
    });
@endif
</script>
@endsection

