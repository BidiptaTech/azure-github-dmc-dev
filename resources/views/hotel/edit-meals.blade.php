@extends('layouts.layout')
@section('content')
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
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Update Meal Information
                <a href="{{ route('meals.restaurant_create', $meals->restaurant_id) }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form id="mealsForm" method="POST" action="{{ route('hotel-meal-update', ['id' => $meals->meal_id]) }}" enctype="multipart/form-data" class="card-body">
                @csrf
                <!-- Hidden Fields -->
                <input type="hidden" name="meal_id" value="{{$meals->meal_id}}">

                <fieldset>
                    <div id="mealsDetailsContainer">
                        <div class="meals-form">
                            <div class="row">
                                <!-- Restaurant -->
                                <div class="col-md-3 mb-3">
                                    <label for="restaurant_id" class="form-label"><strong>Reastaurant</strong><span class="text-danger">*</span></label>
                                    <select name="restaurant_id" id="restaurantSelect" class="form-control">
                                        <option value="">Select a Restaurant</option>
                                        <option value="0">Third Party</option>
                                        @foreach($restaurants as $restaurant)
                                            <option {{$meals->restaurant_id == $restaurant->restaurant_id ? 'selected' : ''}} value="{{ $restaurant->restaurant_id }}">{{ $restaurant->name }}</option>
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
                                        <option {{$meals->meal_period == 1 ? 'selected' : ''}} value="1">Breakfast</option>
                                        <option {{$meals->meal_period == 2 ? 'selected' : ''}} value="2">Lunch</option>
                                        <option {{$meals->meal_period == 3 ? 'selected' : ''}} value="3">Dinner</option>
                                    </select>
                                    @error('meal_period')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="category" class="form-label"><strong>Beverage</strong><span class="text-danger">*</span></label>
                                    <select class="form-select" name="category" required>
                                        <option value="">Select an Option</option>
                                        <option {{$meals->category == 1 ? 'selected' : ''}} value="1">Alcoholic</option>
                                        <option {{$meals->category == 2 ? 'selected' : ''}} value="2">Non Alcoholic</option>
                                        <option {{$meals->category == 3 ? 'selected' : ''}} value="3">No Beverage</option>
                                    </select>
                                    @error('category')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Meal Type -->
                                <div class="col-md-3 mb-3">
                                    <label for="meal_type" class="form-label"><strong>Meals</strong><span class="text-danger">*</span></label>
                                    <select class="form-control" name="meal_type" required>
                                        <option value="">Select</option>
                                        <option value="1" {{ $meals->type == "1" ? 'selected' : '' }}>Buffet</option>
                                        <option value="2" {{ $meals->type == "2" ? 'selected' : '' }}>Set Menu</option>
                                        {{-- <option value="3" {{ $meals->type == "3" ? 'selected' : '' }}>A-La-carte</option> --}}
                                    </select>
                                    @error('meal_type')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Item Name -->
                                <div class="col-md-3 mb-3">
                                    <label for="name" class="form-label"><strong>Item Name</strong><span class="text-danger">*</span></label>
                                    <input value="{{$meals->name}}" type="text" class="form-control" name="name" placeholder="Enter Meal Name (e.g. buffet)">
                                    @error('name')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Item Price -->
                                <div class="col-md-3 mb-3">
                                    <label for="price" class="form-label"><strong>Item Price</strong><span class="text-danger">*</span></label>
                                    <input value="{{$meals->price}}" type="text" class="form-control" id="price" name="price" 
                                           placeholder="Enter Item Price" pattern="^[0-9]+(\.[0-9]{1,2})?$"
                                           oninput="validatePrice(this)">
                                    <small class="validation-message" id="price-validation-message"></small>
                                    @error('price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Adult Price -->
                                <div class="col-md-3 mb-3" id="adult_price_container" style="display: none;">
                                    <label for="adult_price" class="form-label"><strong>Adult Price</strong><span class="text-danger">*</span></label>
                                    <input value="{{$meals->adult_price}}" type="text" class="form-control" id="adult_price" name="adult_price" 
                                           placeholder="Enter Adult Price" pattern="^[0-9]+(\.[0-9]{1,2})?$"
                                           oninput="validatePrice(this)">
                                    <small class="validation-message" id="adult_price-validation-message"></small>
                                    @error('adult_price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Child Price -->
                                <div class="col-md-3 mb-3" id="child_price_container" style="display: none;">
                                    <label for="child_price" class="form-label"><strong>Child Price</strong><span class="text-danger">*</span></label>
                                    <input value="{{$meals->child_price}}" type="text" class="form-control" id="child_price" name="child_price" 
                                           placeholder="Enter Child Price" pattern="^[0-9]+(\.[0-9]{1,2})?$"
                                           oninput="validatePrice(this)">
                                    <small class="validation-message" id="child_price-validation-message"></small>
                                    @error('child_price')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <!-- Veg/Nonveg Type -->
                                <div class="col-md-3 mb-3" id="veg_container" style="display: none;">
                                    <label for="item_type" class="form-label"><strong>Item Type</strong><span class="text-danger">*</span></label>
                                    <select id="veg_type" class="form-select" name="item_type" onchange="toggleFields()">
                                        <option value="">Select an Option</option>
                                        <option {{$meals->item_type == "1" ? 'selected' : ''}} value="1">Vegetarian</option>
                                        <option {{$meals->item_type == "2" ? 'selected' : ''}} value="2">Non Vegetarian</option>
                                    </select>
                                    @error('item_type')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- <div class="col-md-3 mb-3">
                                    <label for="files" class="form-label"><strong>Item File</strong><span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" name="item_file">
                                    <div class="mt-2">
                                        <img id="imagePreview" src="{{ asset($meal->files ?? '') }}" 
                                            alt="Preview Image" 
                                            style="display: {{ isset($meal->files) && $meal->files ? 'block' : 'none' }}; 
                                                    max-width: 100px; 
                                                    height: auto; 
                                                    border: 1px solid #ddd; 
                                                    padding: 5px;">
                                    </div>
                                    @error('item_file')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div> --}}

                                <div class="col-md-3 mb-3" id="item_file_container">
                                    <label for="files" class="form-label"><strong>Add Menu</strong></label>
                                    <input type="file" class="form-control" name="item_file">
                                    <div class="mt-2">
                                        @if($meals->files)
                                        <div class="image-preview-container d-flex flex-wrap gap-2">
                                            <div class="image-preview-wrapper position-relative">
                                                <img src="{{ $meals->files }}" alt="Meal Image" 
                                                    style="max-width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                                                <button type="button" class="delete-image-btn position-absolute top-0 end-0 btn btn-sm btn-danger"
                                                    data-image="{{ $meals->files }}"
                                                    style="width: 20px; height: 20px; line-height: 18px; padding: 0; text-align: center; font-size: 14px; z-index: 1;">
                                                    &times;
                                                </button>
                                                <small class="d-block mt-1 text-muted" style="max-width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    {{ basename($meals->files) }}
                                                </small>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    @error('item_file')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Item Description -->
                                <div class="col-md-6 mb-3">
                                    <label for="item_description" class="form-label"><strong>Item Description</strong><span class="text-danger">*</span></label>
                                    <textarea type="text" class="form-control" name="item_description" placeholder="Enter description..." required>{{old('item_description', $meals->item_description)}}</textarea>
                                    @error('item_description')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mt-2 form-check form-switch">
                        <label for="meal_status" class="form-label"><strong>Status</strong></label>
                        <span style="color: red; font-weight: bold;">*</span>
                        <input {{$meals->is_active == 1 ? 'checked' : ''}} class="form-check-input" name="meal_status" type="checkbox" id="meal_status" value="1">
                        <label class="form-check-label"></label>
                        @error('meal_status')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                </fieldset>


                <!-- Submit Buttons -->
                <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End of the form -->
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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
        var mealType = document.querySelector("select[name='meal_type']").value;
        var itemNameContainer = document.querySelector("input[name='name']").parentElement;
        var itemPriceContainer = document.querySelector("input[name='price']").parentElement;
        var itemFileContainer = document.querySelector("input[name='item_file']").parentElement;
        var itemNameInput = document.querySelector("input[name='name']");
        var itemPriceInput = document.querySelector("input[name='price']");
        var itemFileInput = document.querySelector("input[name='item_file']");
        var adultPriceInput = document.querySelector("input[name='adult_price']");
        var childPriceInput = document.querySelector("input[name='child_price']");
        var adultPriceContainer = document.getElementById("adult_price_container");
        var childPriceContainer = document.getElementById("child_price_container");
        var vegContainer = document.getElementById("veg_container");

        if (mealType === "1" || mealType === "2") { // Buffet or Set Menu
            itemFileContainer.style.display = "block";
            itemNameContainer.style.display = "none";
            if(mealType === "1"){
                adultPriceContainer.style.display = "block";
                childPriceContainer.style.display = "block";
                vegContainer.style.display = "none";
                itemPriceContainer.style.display = "none";
                itemFileInput.removeAttribute("required");
                itemPriceInput.value = "";
                itemPriceInput.removeAttribute("required");
                
                // Set validation for adult and child prices
                if (adultPriceInput) adultPriceInput.dataset.interacted = adultPriceInput.value.trim() === '' ? "true" : "false";
                if (childPriceInput) childPriceInput.dataset.interacted = childPriceInput.value.trim() === '' ? "true" : "false";
            }
            else{
                adultPriceContainer.style.display = "none";
                childPriceContainer.style.display = "none";
                itemPriceContainer.style.display = "block";
                vegContainer.style.display = "block";
                
                // Set validation for item price
                if (itemPriceInput) itemPriceInput.dataset.interacted = itemPriceInput.value.trim() === '' ? "true" : "false";
            }

            // Clear hidden fields
            itemNameInput.value = "";
            
            itemNameInput.removeAttribute("required");
        } else if (mealType === "3") { // A-La-Carte
            itemFileContainer.style.display = "none";
            itemNameContainer.style.display = "block";
            itemPriceContainer.style.display = "block";
            adultPriceContainer.style.display = "none";
            childPriceContainer.style.display = "none";
            vegContainer.style.display = "block";

            // Clear hidden file input
            itemFileInput.value = "";
            itemFileInput.removeAttribute("required");
            itemNameInput.setAttribute("required", "required");
            itemPriceInput.setAttribute("required", "required");
            
            // Set validation for item price
            if (itemPriceInput) itemPriceInput.dataset.interacted = itemPriceInput.value.trim() === '' ? "true" : "false";
        } else { // Default case (no selection)
            itemFileContainer.style.display = "none";
            itemNameContainer.style.display = "none";
            itemPriceContainer.style.display = "none";
            // Clear all fields
            itemNameInput.value = "";
            itemPriceInput.value = "";
            itemFileInput.value = "";
            itemNameInput.removeAttribute("required");
            itemPriceInput.removeAttribute("required");
            itemFileInput.removeAttribute("required");
        }
    }
    document.addEventListener("DOMContentLoaded", function () {
        toggleFields();
    });
    document.querySelector("select[name='meal_type']").addEventListener("change", toggleFields);

//     function toggleFields() {
//     var mealType = document.querySelector("select[name='meal_type']").value;
//     var itemNameContainer = document.querySelector("input[name='name']").parentElement;
//     var itemPriceContainer = document.querySelector("input[name='price']").parentElement;
//     var itemFileContainer = document.querySelector("input[name='item_file']").closest('.col-md-3');
//     var itemNameInput = document.querySelector("input[name='name']");
//     var itemPriceInput = document.querySelector("input[name='price']");
//     var itemFileInput = document.querySelector("input[name='item_file']");
//     var adultPriceInput = document.querySelector("input[name='adult_price']");
//     var childPriceInput = document.querySelector("input[name='child_price']");
//     var adultPriceContainer = document.getElementById("adult_price_container");
//     var childPriceContainer = document.getElementById("child_price_container");
//     var vegContainer = document.getElementById("veg_container");

//     if (mealType === "1" || mealType === "2") { // Buffet or Set Menu
//         itemFileContainer.style.display = "block";
//         itemNameContainer.style.display = "none";
//         if(mealType === "1"){
//             adultPriceContainer.style.display = "block";
//             childPriceContainer.style.display = "block";
//             vegContainer.style.display = "none";
//             itemPriceContainer.style.display = "none";
//             itemFileInput.removeAttribute("required");
//             itemPriceInput.value = "";
//             itemPriceInput.removeAttribute("required");
            
//             // Set validation for adult and child prices
//             if (adultPriceInput) adultPriceInput.dataset.interacted = adultPriceInput.value.trim() === '' ? "true" : "false";
//             if (childPriceInput) childPriceInput.dataset.interacted = childPriceInput.value.trim() === '' ? "true" : "false";
//         }
//         else{
//             adultPriceContainer.style.display = "none";
//             childPriceContainer.style.display = "none";
//             itemPriceContainer.style.display = "block";
//             vegContainer.style.display = "block";
            
//             // Set validation for item price
//             if (itemPriceInput) itemPriceInput.dataset.interacted = itemPriceInput.value.trim() === '' ? "true" : "false";
//         }

//         // Clear hidden fields
//         itemNameInput.value = "";
        
//         itemNameInput.removeAttribute("required");
//     } else if (mealType === "3") { // A-La-Carte
//         itemFileContainer.style.display = "none";
//         itemNameContainer.style.display = "block";
//         itemPriceContainer.style.display = "block";
//         adultPriceContainer.style.display = "none";
//         childPriceContainer.style.display = "none";
//         vegContainer.style.display = "block";

//         // Clear hidden file input
//         itemFileInput.value = "";
//         itemFileInput.removeAttribute("required");
//         itemNameInput.setAttribute("required", "required");
//         itemPriceInput.setAttribute("required", "required");
        
//         // Set validation for item price
//         if (itemPriceInput) itemPriceInput.dataset.interacted = itemPriceInput.value.trim() === '' ? "true" : "false";
//     } else { // Default case (no selection)
//         itemFileContainer.style.display = "none";
//         itemNameContainer.style.display = "none";
//         itemPriceContainer.style.display = "none";
//         // Clear all fields
//         itemNameInput.value = "";
//         itemPriceInput.value = "";
//         itemFileInput.value = "";
//         itemNameInput.removeAttribute("required");
//         itemPriceInput.removeAttribute("required");
//         itemFileInput.removeAttribute("required");
//     }
//  }
</script>

<script>
    // Handle delete image button click
    document.addEventListener('DOMContentLoaded', function() {
        const deleteImageButtons = document.querySelectorAll('.delete-image-btn');
        deleteImageButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Find the parent wrapper and hide it
                const imageWrapper = this.closest('.image-preview-wrapper');
                if (imageWrapper) {
                    imageWrapper.remove();
                    
                    // Create a hidden input to tell the server to remove the image
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'remove_image';
                    hiddenInput.value = '1';
                    document.getElementById('mealsForm').appendChild(hiddenInput);
                }
            });
        });
    });
</script>

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
    });
</script>
@endsection
