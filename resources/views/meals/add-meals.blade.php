@extends('layouts.layout')
@section('content')
@extends('layouts.datatablecss')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single {
        height: 100% !important; /* Adjust as needed */
        line-height: 100% !important;
        padding: 8px 12px;
    }
    /* Increase the height of the dropdown items */
    .select2-container .select2-results__option {
        padding: 12px 10px;
    }
</style>

<!-- Start of the form -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
    
        <ul class="nav nav-pills mb-4 mt-4 d-flex justify-content-center" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ request()->routeIs('restaurant.create') ? 'active' : '' }}" 
                   href="{{ route('restaurant.create') }}" 
                   role="tab">
                    Restaurant
                </a>
            </li>
            
            <li class="nav-item" role="presentation">
                @if(count($restaurants) > 0)
                <a class="nav-link {{ request()->routeIs('meals.create') ? 'active' : '' }}" 
                   href="{{ route('meals.create') }}" 
                   role="tab">
                    Meals
                </a>
                @else
                <a class="nav-link disabled" 
                   href="javascript:void(0);" 
                   role="tab"
                   title="Add a restaurant first">
                    Meals
                </a>
                @endif
            </li>
        </ul>
        <x-alert />
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Add New Meal
                <a href="{{ route('meals.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form id="restaurantForm" method="POST" action="{{ route('meals.store') }}" enctype="multipart/form-data" class="card-body">
                @csrf
                <!-- Hidden Fields -->

                <fieldset>
                    <div id="restaurantDetailsContainer">
                        <div class="restaurant-form">
                            <div class="row">
                                <!-- Restaurant -->
                                <div class="col-md-3 mb-3">
                                    <label for="restaurant_id" class="form-label"><strong>Reastaurant</strong><span class="text-danger">*</span></label>
                                    <select name="restaurant_id" id="restaurantSelect" class="form-control">
                                        <option value="">Select a Restaurant</option>
                                        <!-- <option value="0">Third Party</option> -->
                                        @foreach($restaurants as $restaurant)
                                            <option value="{{ $restaurant->restaurant_id }}">{{ $restaurant->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('restaurant_id')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="meal_period" class="form-label"><strong>Meal Type</strong><span class="text-danger">*</span></label>
                                    <select class="form-select" name="meal_period" required>
                                        <option value="">Select an Option</option>
                                        <option value="1">Breakfast</option>
                                        <option value="2">Lunch</option>
                                        <option value="3">Dinner</option>
                                    </select>
                                    @error('meal_period')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label for="meal_category" class="form-label"><strong>Beverage</strong><span class="text-danger">*</span></label>
                                    <select type="text" class="form-select" name="meal_category" required>
                                        <option value="">Select an Option</option>
                                        <option value="1">Alcoholic</option>
                                        <option value="2">Non Alcoholic</option>
                                        <option value="3">No Beverage</option>
                                    </select>
                                    @error('meal_category')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Meal Type -->
                                <div class="col-md-3 mb-3">
                                    <label for="meal_type" class="form-label"><strong>Meals</strong><span class="text-danger">*</span></label>
                                    <select id="meal_type" class="form-select" name="meal_type" required onchange="toggleFields()">
                                        <option value="">Select an Option</option>
                                        <option value="1">Buffet</option>
                                        <option value="2">Set Menu</option>
                                        {{-- <option value="3">A-La-Carte</option> --}}
                                    </select>
                                    @error('meal_type')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Item Name -->
                                <div class="col-md-3 mb-3" id="item_name_container" style="display: none;">
                                    <label for="name" class="form-label"><strong>Item Name</strong><span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" placeholder="Enter Item Name">
                                    @error('name')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <!-- Item Price -->
                                <div class="col-md-3 mb-3" id="item_price_container" style="display: none;">
                                    <label for="price" class="form-label"><strong>Item Price</strong><span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="price" name="price" 
                                           placeholder="Enter Item Price" pattern="^[0-9]+(\.[0-9]{1,2})?$"
                                           oninput="validatePrice(this)">
                                    <small class="validation-message" id="price-validation-message"></small>
                                    @error('price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Item Cost Price -->
                                <div class="col-md-3 mb-3" id="item_cost_price_container" style="display: none;">
                                    <label for="item_cost_price" class="form-label"><strong>Item Cost Price</strong><span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="item_cost_price" name="item_cost_price"
                                           placeholder="Enter Item Cost Price" pattern="^[0-9]+(\.[0-9]{1,2})?$"
                                           oninput="validatePrice(this)">
                                    <small class="validation-message" id="item_cost_price-validation-message"></small>
                                    @error('item_cost_price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Adult Price -->
                                <div class="col-md-3 mb-3" id="adult_price_container" style="display: none;">
                                    <label for="adult_price" class="form-label"><strong>Adult Price</strong><span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="adult_price" name="adult_price" 
                                           placeholder="Enter Adult Price" pattern="^[0-9]+(\.[0-9]{1,2})?$"
                                           oninput="validatePrice(this)">
                                    <small class="validation-message" id="adult_price-validation-message"></small>
                                    @error('adult_price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Adult Cost Price -->
                                <div class="col-md-3 mb-3" id="adult_cost_price_container" style="display: none;">
                                    <label for="adult_cost_price" class="form-label"><strong>Adult Cost Price</strong><span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="adult_cost_price" name="adult_cost_price"
                                           placeholder="Enter Adult Cost Price" pattern="^[0-9]+(\.[0-9]{1,2})?$"
                                           oninput="validatePrice(this)">
                                    <small class="validation-message" id="adult_cost_price-validation-message"></small>
                                    @error('adult_cost_price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Child Price -->
                                <div class="col-md-3 mb-3" id="child_price_container" style="display: none;">
                                    <label for="child_price" class="form-label"><strong>Child Price</strong><span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="child_price" name="child_price" 
                                           placeholder="Enter Child Price" pattern="^[0-9]+(\.[0-9]{1,2})?$"
                                           oninput="validatePrice(this)">
                                    <small class="validation-message" id="child_price-validation-message"></small>
                                    @error('child_price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Child Cost Price -->
                                <div class="col-md-3 mb-3" id="child_cost_price_container" style="display: none;">
                                    <label for="child_cost_price" class="form-label"><strong>Child Cost Price</strong><span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="child_cost_price" name="child_cost_price"
                                           placeholder="Enter Child Cost Price" pattern="^[0-9]+(\.[0-9]{1,2})?$"
                                           oninput="validatePrice(this)">
                                    <small class="validation-message" id="child_cost_price-validation-message"></small>
                                    @error('child_cost_price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <!-- Veg/Nonveg Type -->
                                <div class="col-md-3 mb-3" id="veg_container" style="display: none;">
                                    <label for="item_type" class="form-label"><strong>Item Type</strong><span class="text-danger">*</span></label>
                                    <select id="veg_type" class="form-select" name="item_type" onchange="toggleFields()">
                                        <option value="">Select an Option</option>
                                        <option value="1">Vegetarian</option>
                                        <option value="2">Non Vegetarian</option>
                                    </select>
                                    @error('item_type')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Item File -->
                                <div class="col-md-3 mb-3" id="item_file_container" style="display: none;">
                                    <label for="item_file" class="form-label"><strong>Add Menu</strong></label>
                                    <input type="file" class="form-control" name="item_file">
                                    @error('item_file')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <!-- Item Description -->
                                <div class="col-md-6 mb-3">
                                    <label for="item_description" class="form-label"><strong>Item Description</strong><span class="text-danger">*</span></label>
                                    <textarea type="text" class="form-control" name="item_description" placeholder="Enter description..." required></textarea>
                                    @error('item_description')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="meal_status" value="0">
                                <input class="form-check-input" name="meal_status" type="checkbox" id="meal_status" value="1">
                                <label for="meal_status" class="form-check-label"><strong>Status</strong></label>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <!-- Submit Buttons -->
                <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

        <!-- Restaurant Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" Category="dialog" 
        aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" Category="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmation</h5>
            </div>
            <div class="modal-body">
                Are you sure want to delete?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <form id="deleteForm" action="" method="POST" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End Modal -->
<!-- End of the form -->
@endsection

@section('scripts') 
{{-- <script>
        document.addEventListener('DOMContentLoaded', () => {
        const toggleVisibility = (checkboxId, fieldId) => {
            document.getElementById(checkboxId).addEventListener('change', function () {
                document.getElementById(fieldId).classList.toggle('d-none', !this.checked);
            });
        };

        toggleVisibility('breakfastToggle', 'breakfastFields');
        toggleVisibility('lunchToggle', 'lunchFields');
        toggleVisibility('dinnerToggle', 'dinnerFields');
    });
</script> --}}

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggleVisibility = (checkboxId, fieldId) => {
            const checkbox = document.getElementById(checkboxId);
            const field = document.getElementById(fieldId);

            if (checkbox && field) {  // Check if both elements exist
                checkbox.addEventListener('change', function () {
                    field.classList.toggle('d-none', !this.checked);
                });
            }
        };

        toggleVisibility('breakfastToggle', 'breakfastFields');
        toggleVisibility('lunchToggle', 'lunchFields');
        toggleVisibility('dinnerToggle', 'dinnerFields');
    });
</script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#restaurantSelect').select2({
            placeholder: "Search and Select a Restaurant",
            allowClear: true,
            width: '100%'
        });
    });
</script>
<script>
    function toggleFields() {
        var mealType = document.getElementById("meal_type").value;
        var itemNameContainer = document.getElementById("item_name_container");
        var itemFileContainer = document.getElementById("item_file_container");
        var itemPriceContainer = document.getElementById("item_price_container");
        var itemCostPriceContainer = document.getElementById("item_cost_price_container");
        var adultPriceContainer = document.getElementById("adult_price_container");
        var adultCostPriceContainer = document.getElementById("adult_cost_price_container");
        var childPriceContainer = document.getElementById("child_price_container");
        var childCostPriceContainer = document.getElementById("child_cost_price_container");
        var vegContainer = document.getElementById("veg_container");
        var vegSelect = document.getElementById("veg_type");

        var itemNameInput = document.querySelector("input[name='name']");
        var itemFileInput = document.querySelector("input[name='item_file']");
        var itemPriceInput = document.querySelector("input[name='price']");
        var itemCostPriceInput = document.querySelector("input[name='item_cost_price']");
        var adultPriceInput = document.querySelector("input[name='adult_price']");
        var adultCostPriceInput = document.querySelector("input[name='adult_cost_price']");
        var childPriceInput = document.querySelector("input[name='child_price']");
        var childCostPriceInput = document.querySelector("input[name='child_cost_price']");

        if (mealType === "1" || mealType === "2") { // Buffet or Set Menu
            itemFileContainer.style.display = "block";
            itemNameContainer.style.display = "none";
            
            if(mealType === "1"){
                adultPriceContainer.style.display = "block";
                childPriceContainer.style.display = "block";
                if (adultCostPriceContainer) adultCostPriceContainer.style.display = "block";
                if (childCostPriceContainer) childCostPriceContainer.style.display = "block";
                vegContainer.style.display = "none";
                itemPriceContainer.style.display = "none";
                if (itemCostPriceContainer) itemCostPriceContainer.style.display = "none";
                vegSelect.removeAttribute("required");
                itemNameInput.removeAttribute("required");
                
                // Set validation for adult and child prices
                adultPriceInput.dataset.interacted = adultPriceInput.value.trim() === '' ? "true" : "false";
                childPriceInput.dataset.interacted = childPriceInput.value.trim() === '' ? "true" : "false";
                if (adultCostPriceInput) adultCostPriceInput.dataset.interacted = adultCostPriceInput.value.trim() === '' ? "true" : "false";
                if (childCostPriceInput) childCostPriceInput.dataset.interacted = childCostPriceInput.value.trim() === '' ? "true" : "false";
            }
            else{
                adultPriceContainer.style.display = "none";
                childPriceContainer.style.display = "none";
                itemPriceContainer.style.display = "block";
                if (adultCostPriceContainer) adultCostPriceContainer.style.display = "none";
                if (childCostPriceContainer) childCostPriceContainer.style.display = "none";
                if (itemCostPriceContainer) itemCostPriceContainer.style.display = "block";
                vegContainer.style.display = "block";
                
                // Set validation for item price
                itemPriceInput.dataset.interacted = itemPriceInput.value.trim() === '' ? "true" : "false";
                if (itemCostPriceInput) itemCostPriceInput.dataset.interacted = itemCostPriceInput.value.trim() === '' ? "true" : "false";
            }

            // Clear hidden fields
            itemNameInput.value = "";
            itemPriceInput.value = ""; // Clear price because it's hidden
            if (itemCostPriceInput && itemCostPriceContainer && itemCostPriceContainer.style.display === "none") itemCostPriceInput.value = "";
            if (adultCostPriceInput && adultCostPriceContainer && adultCostPriceContainer.style.display === "none") adultCostPriceInput.value = "";
            if (childCostPriceInput && childCostPriceContainer && childCostPriceContainer.style.display === "none") childCostPriceInput.value = "";

            // Set required attribute correctly
            itemFileInput.setAttribute("required", "required");
            itemNameInput.removeAttribute("required");
            itemPriceInput.removeAttribute("required");
            if (itemCostPriceInput && itemCostPriceContainer && itemCostPriceContainer.style.display === "none") itemCostPriceInput.removeAttribute("required");
        } else if (mealType === "3") { // A-La-Carte
            itemFileContainer.style.display = "none";
            itemNameContainer.style.display = "block";
            itemPriceContainer.style.display = "block";
            if (itemCostPriceContainer) itemCostPriceContainer.style.display = "block";
            adultPriceContainer.style.display = "none";
            childPriceContainer.style.display = "none";
            if (adultCostPriceContainer) adultCostPriceContainer.style.display = "none";
            if (childCostPriceContainer) childCostPriceContainer.style.display = "none";
            vegContainer.style.display = "block";
            // Clear hidden file input
            itemFileInput.value = "";

            // Set required attribute correctly
            itemNameInput.setAttribute("required", "required");
            itemPriceInput.setAttribute("required", "required");
            if (itemCostPriceInput) itemCostPriceInput.setAttribute("required", "required");
            itemFileInput.removeAttribute("required");
            
            // Set validation for item price
            itemPriceInput.dataset.interacted = itemPriceInput.value.trim() === '' ? "true" : "false";
            if (itemCostPriceInput) itemCostPriceInput.dataset.interacted = itemCostPriceInput.value.trim() === '' ? "true" : "false";
        } else { // Default case (no selection)
            itemFileContainer.style.display = "none";
            itemNameContainer.style.display = "none";
            itemPriceContainer.style.display = "none";
            if (itemCostPriceContainer) itemCostPriceContainer.style.display = "none";
            adultPriceContainer.style.display = "none";
            childPriceContainer.style.display = "none";
            if (adultCostPriceContainer) adultCostPriceContainer.style.display = "none";
            if (childCostPriceContainer) childCostPriceContainer.style.display = "none";

            // Clear all fields
            itemNameInput.value = "";
            itemFileInput.value = "";
            itemPriceInput.value = "";
            if (itemCostPriceInput) itemCostPriceInput.value = "";
            if (adultPriceInput) adultPriceInput.value = "";
            if (childPriceInput) childPriceInput.value = "";
            if (adultCostPriceInput) adultCostPriceInput.value = "";
            if (childCostPriceInput) childCostPriceInput.value = "";

            // Remove required attributes
            itemNameInput.removeAttribute("required");
            // itemFileInput.removeAttribute("required");
            itemPriceInput.removeAttribute("required");
            if (itemCostPriceInput) itemCostPriceInput.removeAttribute("required");
            if (adultCostPriceInput) adultCostPriceInput.removeAttribute("required");
            if (childCostPriceInput) childCostPriceInput.removeAttribute("required");
        }
    }
</script>

<!-- Add validation script -->
<script>
    function showValidationMessage(inputElement, isValid, message) {
        const messageElement = document.getElementById(`${inputElement.id}-validation-message`);
        
        if (!messageElement) return;
        
        // Only show validation message if the field has been interacted with
        if (!inputElement.dataset.interacted) return;
        
        if (isValid) {
            messageElement.innerHTML = `
                <div class="valid-feedback d-block">
                    <i class="fas fa-check-circle text-success"></i> 
                    Looks good!
                </div>`;
            inputElement.classList.remove('is-invalid');
            inputElement.classList.add('is-valid');
        } else {
            messageElement.innerHTML = `
                <div class="invalid-feedback d-block">
                    <i class="fas fa-exclamation-circle"></i> 
                    ${message}
                </div>`;
            inputElement.classList.remove('is-valid');
            inputElement.classList.add('is-invalid');
        }
    }

    function validatePrice(input) {
        // Mark the field as interacted with when the user makes changes
        input.dataset.interacted = "true";
        
        // Force numeric input and decimal point only
        input.value = input.value.replace(/[^0-9.]/g, '');
        
        // Ensure only one decimal point
        let value = input.value;
        const decimalCount = (value.match(/\./g) || []).length;
        if (decimalCount > 1) {
            const parts = value.split('.');
            value = parts[0] + '.' + parts.slice(1).join('');
            input.value = value;
        }
        
        // Validate with regex for proper price format (positive numbers with up to 2 decimal places)
        const priceRegex = /^[0-9]+(\.[0-9]{1,2})?$/;
        
        if (value === '') {
            showValidationMessage(input, false, 'Price is required');
        } else if (!priceRegex.test(value)) {
            showValidationMessage(input, false, `
                Please enter a valid price:
                <ul class="mt-1 mb-0">
                    <li>Must be a positive number</li>
                    <li>Can include up to 2 decimal places</li>
                    <li>Example: 99.95</li>
                </ul>
            `);
        } else {
            showValidationMessage(input, true, '');
        }
    }

    // Add CSS for validation messages and icons
    document.head.insertAdjacentHTML('beforeend', `
        <style>
            /* Base validation message styles */
            .validation-message {
                margin-top: 0.5rem;
                font-size: 0.85rem;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            }

            /* Error state styles */
            .validation-message .invalid-feedback {
                display: block;
                color: #e74c3c;
                background-color: #fef5f5;
                border-left: 3px solid #e74c3c;
                padding: 0.75rem 1rem;
                border-radius: 4px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                animation: slideIn 0.3s ease-in-out;
            }

            /* Success state styles */
            .validation-message .valid-feedback {
                display: block;
                color: #2ecc71;
                background-color: #f4fff6;
                border-left: 3px solid #2ecc71;
                padding: 0.75rem 1rem;
                border-radius: 4px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                animation: slideIn 0.3s ease-in-out;
            }

            /* List styles within validation messages */
            .validation-message ul {
                margin: 0.5rem 0 0 0;
                padding-left: 1.5rem;
                list-style-type: none;
            }

            .validation-message ul li {
                position: relative;
                padding: 0.2rem 0;
                color: #666;
            }

            .validation-message ul li::before {
                content: "•";
                color: #e74c3c;
                font-weight: bold;
                position: absolute;
                left: -1rem;
            }

            /* Icon styles */
            .validation-message i {
                margin-right: 0.5rem;
                font-size: 1rem;
            }

            /* Input field styles with validation icons */
            .form-control.is-valid {
                border-color: #2ecc71 !important;
                background-color: #fff !important;
                padding-right: calc(1.5em + 0.75rem);
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%232ecc71' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right calc(0.375em + 0.1875rem) center;
                background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
            }

            .form-control.is-invalid {
                border-color: #e74c3c !important;
                background-color: #fff !important;
                padding-right: calc(1.5em + 0.75rem);
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23e74c3c'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23e74c3c' stroke='none'/%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right calc(0.375em + 0.1875rem) center;
                background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
            }

            /* Animation for validation messages */
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Hover effect for validation messages */
            .validation-message .invalid-feedback:hover,
            .validation-message .valid-feedback:hover {
                transform: translateY(-1px);
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
                transition: all 0.3s ease;
            }

            /* Focus state styles */
            .form-control:focus {
                box-shadow: 0 0 0 0.2rem rgba(46, 204, 113, 0.25);
                border-color: #2ecc71;
            }

            .form-control.is-invalid:focus {
                box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.25);
                border-color: #e74c3c;
            }
        </style>
    `);

    // Set up validation on DOM load
    document.addEventListener('DOMContentLoaded', function() {
        // Add validation to price inputs
        const adultPriceInput = document.querySelector("input[name='adult_price']");
        const childPriceInput = document.querySelector("input[name='child_price']");
        const itemPriceInput = document.querySelector("input[name='price']");
        const itemCostPriceInput = document.querySelector("input[name='item_cost_price']");
        const adultCostPriceInput = document.querySelector("input[name='adult_cost_price']");
        const childCostPriceInput = document.querySelector("input[name='child_cost_price']");
        
        // Add ID and validation message elements for each price field
        if (adultPriceInput) {
            adultPriceInput.id = 'adult_price';
            // Add validation message container if it doesn't exist
            if (!document.getElementById('adult_price-validation-message')) {
                adultPriceInput.insertAdjacentHTML('afterend', 
                    '<small class="validation-message" id="adult_price-validation-message"></small>');
            }
            // Set initial state - don't show validation until interacted
            adultPriceInput.dataset.interacted = "false";
            // Add event listeners
            adultPriceInput.addEventListener('input', function() { validatePrice(this); });
            adultPriceInput.addEventListener('focus', function() { this.dataset.focused = "true"; });
            adultPriceInput.addEventListener('blur', function() {
                if (this.dataset.focused === "true") {
                    this.dataset.interacted = "true";
                    validatePrice(this);
                }
            });
            // Clear classes initially
            adultPriceInput.classList.remove('is-valid', 'is-invalid');
        }
        
        if (childPriceInput) {
            childPriceInput.id = 'child_price';
            // Add validation message container if it doesn't exist
            if (!document.getElementById('child_price-validation-message')) {
                childPriceInput.insertAdjacentHTML('afterend', 
                    '<small class="validation-message" id="child_price-validation-message"></small>');
            }
            // Set initial state - don't show validation until interacted
            childPriceInput.dataset.interacted = "false";
            // Add event listeners
            childPriceInput.addEventListener('input', function() { validatePrice(this); });
            childPriceInput.addEventListener('focus', function() { this.dataset.focused = "true"; });
            childPriceInput.addEventListener('blur', function() {
                if (this.dataset.focused === "true") {
                    this.dataset.interacted = "true";
                    validatePrice(this);
                }
            });
            // Clear classes initially
            childPriceInput.classList.remove('is-valid', 'is-invalid');
        }
        
        if (itemPriceInput) {
            itemPriceInput.id = 'price';
            // Add validation message container if it doesn't exist
            if (!document.getElementById('price-validation-message')) {
                itemPriceInput.insertAdjacentHTML('afterend', 
                    '<small class="validation-message" id="price-validation-message"></small>');
            }
            // Set initial state - don't show validation until interacted
            itemPriceInput.dataset.interacted = "false";
            // Add event listeners
            itemPriceInput.addEventListener('input', function() { validatePrice(this); });
            itemPriceInput.addEventListener('focus', function() { this.dataset.focused = "true"; });
            itemPriceInput.addEventListener('blur', function() {
                if (this.dataset.focused === "true") {
                    this.dataset.interacted = "true";
                    validatePrice(this);
                }
            });
            // Clear classes initially
            itemPriceInput.classList.remove('is-valid', 'is-invalid');
        }

        const setupCostValidation = (inputEl, id) => {
            if (!inputEl) return;
            inputEl.id = id;
            if (!document.getElementById(`${id}-validation-message`)) {
                inputEl.insertAdjacentHTML('afterend', `<small class="validation-message" id="${id}-validation-message"></small>`);
            }
            inputEl.dataset.interacted = "false";
            inputEl.addEventListener('input', function() { validatePrice(this); });
            inputEl.addEventListener('focus', function() { this.dataset.focused = "true"; });
            inputEl.addEventListener('blur', function() {
                if (this.dataset.focused === "true") {
                    this.dataset.interacted = "true";
                    validatePrice(this);
                }
            });
            inputEl.classList.remove('is-valid', 'is-invalid');
        };

        setupCostValidation(itemCostPriceInput, 'item_cost_price');
        setupCostValidation(adultCostPriceInput, 'adult_cost_price');
        setupCostValidation(childCostPriceInput, 'child_cost_price');
        
        // Call toggleFields to ensure proper initial state
        toggleFields();
    });
</script>
@endsection
