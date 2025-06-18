@extends('layouts.layout') 
@section('content')
@include('hotel.tapview', ['hotel' => $hotel])
@extends('layouts.datatablecss')
<style>
    .facility-card {
        transition: all 0.3s ease;
        border: 1px solid #dee2e6;
        height: 100%;
        cursor: pointer;
    }
    
    .facility-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .facility-card.selected {
        border-color: #007bff;
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    .facility-card .card-body {
        padding: 1rem;
        position: relative;
    }
    
    .category-pill {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 500;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
        transition: all 0.2s;
        cursor: pointer;
    }
    
    .category-pill:hover {
        filter: brightness(0.9);
    }
    
    .category-pill.active {
        background-color: #0d6efd !important;
        color: white !important;
    }
    
    .facility-counter {
        position: absolute;
        top: 8px;
        right: 8px;
        background: #0d6efd;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
    }
    
    .dollar-badge {
        position: absolute;
        top: 8px;
        right: 40px;
        background: #198754;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
    }
    
    .category-section {
        margin-bottom: 2rem;
    }
    
    .facility-checkbox {
        position: absolute;
        opacity: 0;
    }
    
    .facility-name {
        font-weight: 500;
        margin-bottom: 0;
    }
    
    .action-button {
        transition: all 0.2s;
    }
    
    .action-button:hover {
        transform: translateY(-2px);
    }
    
    .search-container {
        position: relative;
        margin-bottom: 1.5rem;
    }
    
    .search-container i {
        position: absolute;
        top: 12px;
        left: 12px;
        color: #adb5bd;
    }
    
    .search-container input {
        padding-left: 40px;
        border-radius: 50px;
    }
    
    #facilities-container {
        background-color: #f8f9fa;
    }
    
    .facility-checkbox {
        cursor: pointer;
    }
    
    .form-check {
        padding: 8px;
        border-radius: 4px;
        transition: background-color 0.2s;
    }
    
    .form-check:hover {
        background-color: #e9ecef;
    }
    
    .form-check-input:checked + .form-check-label {
        font-weight: bold;
        color: #007bff;
    }
    
    /* Added styles for facility indicators */
    .check-mark {
        margin-left: 5px;
        color: #198754;
        display: none;
    }
    
    .form-check-input:checked ~ .check-mark {
        display: inline-block;
    }
    
    .dollar-symbol {
        margin-left: 5px;
        color: #198754;
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Manage Hotel Facilities</h5>
                <span class="badge bg-primary p-2" id="selected-counter">0 Selected</span>
            </div>
            
            <div class="card-body">
                <form action="{{ route('store.facility.image') }}" method="POST" enctype="multipart/form-data" id="facility-form">
                    @csrf
                    <input type="hidden" id="hotel_id" name="hotel_id" value="{{ $hotel->hotel_unique_id }}">
                    <input type="hidden" name="selected_facilities" id="selected-facilities" value="{{ json_encode(collect($facilities)->pluck('facilityId')) }}">
                    
                    <!-- Search and actions -->
                    <div class="row mb-4">
                        <div class="col-lg-6">
                            <div class="search-container">
                                <i class="fas fa-search"></i>
                                <input type="text" class="form-control" id="facility-search" placeholder="Search facilities...">
                            </div>
                        </div>
                        <div class="col-lg-6 d-flex justify-content-end gap-2">
                            <button type="button" id="toggle-all" class="btn btn-outline-primary action-button">
                                <i class="fas fa-check-double me-2"></i>Select All
                            </button>
                            <button type="submit" class="btn btn-primary action-button">
                                <i class="fas fa-save me-2"></i>Save Facilities
                            </button>
                        </div>
                    </div>
                    
                    <!-- Category filter -->
                    <div class="mb-4 d-flex flex-wrap">
                        <div class="category-pill active" data-category="all" style="background-color: #e9ecef; color: #212529;">
                            All Categories
                        </div>
                        @foreach($category as $cat)
                            <div class="category-pill" data-category="{{ $cat->category_id }}" style="background-color: #e9ecef; color: #212529;">
                                {{ $cat->name }}
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Facility grid -->
                    <div id="facilities-container">
                        @foreach($category as $cat)
                        <div class="category-section" id="category-{{ $cat->category_id }}">
                            <h6 class="bg-light p-2 rounded mb-3 d-flex align-items-center">
                                <i class="fas fa-tag me-2"></i>{{ $cat->name }}
                                <span class="ms-auto badge bg-secondary category-count">0</span>
                            </h6>
                            <div class="row facility-items">
                                <!-- Facilities will be populated by JavaScript -->
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- No results message -->
                    <div id="no-facilities-message" class="text-center p-5" style="display: none;">
                        <i class="fas fa-search fa-3x mb-3 text-muted"></i>
                        <h6 class="text-muted">No facilities found</h6>
                    </div>
                </form>
            </div>
        </div>

@endsection

@section('scripts')  
<script src="{{ env('APP_URL') . '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js' }}"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        // Facility Category Filter Functionality
        const facilities = @json($facility);
        // Parse the existing selected facilities from the hidden input
        let selectedFacilities = JSON.parse($('#selected-facilities').val() || '[]');
        
        // Update the selected counter
        function updateSelectedCounter() {
            const selectedCount = selectedFacilities.length;
            $('#selected-counter').text(`${selectedCount} Selected`);
        }
        
        // Populate all facilities by category
        function populateAllFacilities() {
            // Clear all existing facility items
            $('.facility-items').empty();
            
            // Group facilities by category
            const facilitiesByCategory = {};
            
            // Process each facility
            facilities.forEach(function(facility) {
                if (facility.categories) {
                    const categoryId = facility.categories.categoryId || facility.categories.category_id;
                    
                    if (!facilitiesByCategory[categoryId]) {
                        facilitiesByCategory[categoryId] = [];
                    }
                    
                    facilitiesByCategory[categoryId].push(facility);
                }
            });
            
            // Populate each category section and update counts
            for (const categoryId in facilitiesByCategory) {
                const categoryFacilities = facilitiesByCategory[categoryId];
                const categoryContainer = $(`#category-${categoryId} .facility-items`);
                $(`#category-${categoryId} .category-count`).text(categoryFacilities.length);
                
                categoryFacilities.forEach(function(facility) {
                    const facilityId = parseInt(facility.facilityId);
                    const isChecked = selectedFacilities.includes(facilityId);
                    const isChargeable = facility.is_chargeable == 1;
                    const facilityCol = $('<div>').addClass('col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-3');
                    
                    const facilityCard = $(`
                        <div class="facility-card ${isChecked ? 'selected' : ''}" data-facility-id="${facilityId}">
                            <div class="card-body">
                                <input class="facility-checkbox" type="checkbox" 
                                    id="facility-${facilityId}" 
                                    name="facilities[]"
                                    value="${facilityId}" 
                                    ${isChecked ? 'checked' : ''}>
                                <p class="facility-name">
                                    ${facility.name}
                                </p>
                                ${isChecked ? '<span class="facility-counter"><i class="fas fa-check"></i></span>' : ''}
                                ${isChargeable ? '<span class="dollar-badge"><i class="fas fa-dollar-sign"></i></span>' : ''}
                            </div>
                        </div>
                    `);
                    
                    facilityCol.append(facilityCard);
                    categoryContainer.append(facilityCol);
                });
                
                // If no facilities in this category, show a message
                if (categoryFacilities.length === 0) {
                    categoryContainer.append('<div class="col-12"><p class="text-muted text-center py-3">No facilities in this category</p></div>');
                }
            }
            
            // Setup click handlers for all facility cards
            $('.facility-card').on('click', function() {
                const facilityId = parseInt($(this).data('facility-id'));
                const checkbox = $(this).find('.facility-checkbox');
                
                // Toggle checkbox state
                const isChecked = !checkbox.prop('checked');
                checkbox.prop('checked', isChecked);
                
                // Toggle selected class
                $(this).toggleClass('selected', isChecked);
                
                // Add or remove the counter badge
                if (isChecked) {
                    // Add tick mark if it doesn't exist already
                    if ($(this).find('.facility-counter').length === 0) {
                        $(this).find('.card-body').append('<span class="facility-counter"><i class="fas fa-check"></i></span>');
                    }
                    
                    // Add to selected facilities if not already there
                    if (!selectedFacilities.includes(facilityId)) {
                        selectedFacilities.push(facilityId);
                    }
                } else {
                    // Remove tick mark
                    $(this).find('.facility-counter').remove();
                    
                    // Remove from selected facilities
                    selectedFacilities = selectedFacilities.filter(id => id !== facilityId);
                }
                
                // Update the hidden input with the new selection
                updateSelectedFacilities();
                
                // Update counter
                updateSelectedCounter();
            });
            
            // Update the selected counter
            updateSelectedCounter();
            
            // If no facilities at all, show the no facilities message
            if (Object.keys(facilitiesByCategory).length === 0) {
                $('#no-facilities-message').show();
            } else {
                $('#no-facilities-message').hide();
            }
        }
        
        // Filter facilities by category
        function filterFacilitiesByCategory(categoryId) {
            if (categoryId === 'all') {
                // Show all categories
                $('.category-section').show();
            } else {
                // Hide all categories, then show only the selected one
                $('.category-section').hide();
                $(`#category-${categoryId}`).show();
            }
        }
        
        // Handle category pill clicks
        $('.category-pill').on('click', function() {
            // Remove active class from all pills
            $('.category-pill').removeClass('active');
            
            // Add active class to clicked pill
            $(this).addClass('active');
            
            const categoryId = $(this).data('category');
            filterFacilitiesByCategory(categoryId);
        });
        
        // Function to update the hidden input with selected facilities
        function updateSelectedFacilities() {
            $('#selected-facilities').val(JSON.stringify(selectedFacilities));
            
            // For debugging, uncomment this
            console.log('Updated selected facilities:', selectedFacilities);
        }
        
        // Initialize search functionality
        $('#facility-search').on('keyup', function() {
            const searchTerm = $(this).val().toLowerCase();
            
            if (searchTerm.length > 0) {
                // Show all categories when searching
                $('.category-section').show();
                $('.category-pill').removeClass('active');
                $('.category-pill[data-category="all"]').addClass('active');
                
                // Hide/show facilities based on search
                $('.facility-card').each(function() {
                    const facilityName = $(this).find('.facility-name').text().toLowerCase();
                    const matchesSearch = facilityName.includes(searchTerm);
                    
                    $(this).closest('.col-xl-3').toggle(matchesSearch);
                });
                
                // Check if any facilities are visible in each category
                $('.category-section').each(function() {
                    const visibleFacilities = $(this).find('.facility-card').closest('.col-xl-3:visible').length;
                    if (visibleFacilities === 0) {
                        $(this).hide();
                    }
                });
                
                // Show no results message if needed
                const visibleCategories = $('.category-section:visible').length;
                $('#no-facilities-message').toggle(visibleCategories === 0);
            } else {
                // If search is cleared, reset to active category
                const activeCategory = $('.category-pill.active').data('category');
                filterFacilitiesByCategory(activeCategory);
                
                // Reset facility visibility
                $('.facility-card').closest('.col-xl-3').show();
                $('#no-facilities-message').hide();
            }
        });
        
        // Initialize all facilities on page load
        populateAllFacilities();
        
        // Handle toggle all button
        $('#toggle-all').on('click', function() {
            const btnText = $(this).text().trim();
            // Only toggle checkboxes that are currently visible
            const visibleCards = $('.category-section:visible .facility-card');
            const visibleCheckboxes = $('.category-section:visible .facility-checkbox');
            
            if (btnText.includes('Select All')) {
                // Select all visible facilities
                visibleCheckboxes.prop('checked', true);
                visibleCards.addClass('selected');
                
                // Add check icons to all visible cards
                visibleCards.each(function() {
                    const facilityId = parseInt($(this).data('facility-id'));
                    
                    // Add tick mark if it doesn't exist already
                    if ($(this).find('.facility-counter').length === 0) {
                        $(this).find('.card-body').append('<span class="facility-counter"><i class="fas fa-check"></i></span>');
                    }
                    
                    // Add to selected facilities if not already there
                    if (!selectedFacilities.includes(facilityId)) {
                        selectedFacilities.push(facilityId);
                    }
                });
                
                $(this).html('<i class="fas fa-times-circle me-2"></i>Deselect All');
            } else {
                // Deselect all visible facilities
                visibleCheckboxes.prop('checked', false);
                visibleCards.removeClass('selected');
                
                // Remove tick marks from all visible cards
                visibleCards.find('.facility-counter').remove();
                
                // Remove all visible facilities from selected facilities
                visibleCards.each(function() {
                    const facilityId = parseInt($(this).data('facility-id'));
                    selectedFacilities = selectedFacilities.filter(id => id !== facilityId);
                });
                
                $(this).html('<i class="fas fa-check-double me-2"></i>Select All');
            }
            
            // Update the hidden input with the new selection
            updateSelectedFacilities();
            updateSelectedCounter();
        });
        
        // Form submission validation
        $('#facility-form').on('submit', function(e) {
            // Check if any facilities are selected
            if (selectedFacilities.length === 0) {
                e.preventDefault();
                alert('Please select at least one facility');
                return false;
            }
            
            // Update the hidden input one more time before submission to ensure latest selection
            updateSelectedFacilities();
            
            // Show loading state
            $(this).find('button[type="submit"]').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
            $(this).find('button[type="submit"]').prop('disabled', true);
            
            return true;
        });

        // Function to render facility checkboxes
        function renderFacilityCheckboxes(categoryId) {
            const facilitiesContainer = $('#facilities-checkboxes');
            const noFacilitiesMessage = $('#no-facilities-message');
            
            // Clear current facilities
            facilitiesContainer.empty();
            
            if (!categoryId) {
                noFacilitiesMessage.show();
                return;
            }
            
            // Filter facilities by selected category
            const filteredFacilities = filterFacilitiesByCategory(categoryId);
            
            if (filteredFacilities.length === 0) {
                noFacilitiesMessage.text('No facilities found for this category').show();
                return;
            }
            
            // Hide no facilities message
            noFacilitiesMessage.hide();
            
            // Render checkboxes for filtered facilities
            filteredFacilities.forEach(function(facility) {
                const isChecked = selectedFacilities.includes(parseInt(facility.facilityId));
                const isChargeable = facility.is_chargeable == 1;
                const facilityCol = $('<div>').addClass('col-md-3 mb-2');
                const facilityCheck = $(`
                    <div class="form-check">
                        <input class="form-check-input facility-checkbox" type="checkbox" 
                            id="facility-${facility.facilityId}" 
                            name="facilities[]"
                            value="${facility.facilityId}" 
                            ${isChecked ? 'checked' : ''}>
                        <label class="form-check-label" for="facility-${facility.facilityId}">
                            ${facility.name}
                        </label>
                        <i class="fas fa-check check-mark"></i>
                        ${isChargeable ? '<i class="fas fa-dollar-sign dollar-symbol"></i>' : ''}
                    </div>
                `);
                
                facilityCol.append(facilityCheck);
                facilitiesContainer.append(facilityCol);
            });
            
            // Update selected facilities when checkbox changes
            $('.facility-checkbox').on('change', updateSelectedFacilities);
        }
    });

    function setDeleteForm(action) {
        document.getElementById('deleteForm').action = action;
    }
</script>
@endsection