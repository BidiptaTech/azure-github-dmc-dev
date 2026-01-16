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
                <a class="nav-link {{ request()->routeIs('restaurant.edit') ? 'active' : '' }}" 
                   href="{{ route('restaurant.edit', Crypt::encrypt($current_restaurant->restaurant_id)) }}" 
                   role="tab">
                    Restaurant
                </a>
            </li>
            
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ request()->routeIs('meals.restaurant_create') ? 'active' : '' }}" 
                   href="{{ route('meals.restaurant_create', Crypt::encrypt($current_restaurant->restaurant_id)) }}" 
                   role="tab">
                    Meals
                </a>
            </li>
        </ul>
        <x-alert />
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Add New Meal
                <div class="d-flex gap-2">
                    {{-- @if(auth()->user()->role_id == '11')
                        <a href="{{ route('meals.bulk_upload_for_restaurant', $current_restaurant->restaurant_id) }}" 
                           class="btn btn-warning btn-sm">
                            <i class="ri-upload-cloud-2-line me-1"></i>Bulk Upload Meals
                        </a>
                    @endif --}}
                    {{-- <a href="{{ route('meals.restaurant_create', $current_restaurant->restaurant_id) }}" class="btn btn-sm btn-outline-danger">
                        <i class="mdi mdi-arrow-left"></i> Back
                    </a> --}}
                </div>
            </h5>
            <form id="restaurantForm" method="POST" action="{{ route('meals.store') }}" enctype="multipart/form-data" class="card-body">
                @csrf
                <!-- Hidden Fields -->

                <fieldset>
                    <div id="restaurantDetailsContainer">
                        <div class="restaurant-form">
                            <div class="row">
                                @if($auth_user->role_id == 1 || $auth_user->role_id == 20)
                                <!-- DMC Selection (Required for Admin and Role 20) -->
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        @if($dmcUsers->count() > 0)
                                            <div class="alert alert-info">
                                                <strong>Note:</strong> As an admin/manager, you must select a DMC to add meals on their behalf. Only DMCs associated with this restaurant are shown.
                                            </div>
                                        @else
                                            <div class="alert alert-warning">
                                                <strong>Warning:</strong> This restaurant is not associated with any DMCs. You cannot add meals until DMCs are assigned to this restaurant.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="dmc_selection" class="form-label"><strong>Select DMC</strong><span class="text-danger">*</span></label>
                                        <select id="dmc_selection" class="form-control" name="dmc_id" required @if($dmcUsers->count() == 0) disabled @endif>
                                            @if($dmcUsers->count() > 0)
                                                <option value="">Select DMC</option>
                                                @foreach($dmcUsers as $dmc)
                                                    <option value="{{ $dmc->userId }}">{{ $dmc->company_name }} ({{ $dmc->name }})</option>
                                                @endforeach
                                            @else
                                                <option value="">No DMCs available for this restaurant</option>
                                            @endif
                                        </select>
                                        @if($dmcUsers->count() > 0)
                                            <small class="text-muted">You are adding meals on behalf of the selected DMC.</small>
                                        @else
                                            <small class="text-muted text-warning">Please contact administrator to assign DMCs to this restaurant first.</small>
                                        @endif
                                    </div>
                                </div>
                                @endif
                                <!-- Restaurant -->
                                <div class="col-md-3 mb-3" style="display: none;">
                                    <label for="restaurant_id" class="form-label"><strong>Reastaurant</strong><span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="restaurant_id" value="{{ $current_restaurant->restaurant_id }}" readonly>
                                    @error('restaurant_id')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="meal_period" class="form-label"><strong>Meal Type</strong><span class="text-danger">*</span></label>
                                    <select class="form-select" name="meal_period" required>
                                        <option value="">Select an Option</option>
                                        @if($current_restaurant->breakfast_available == 1)
                                        <option value="1">Breakfast</option>
                                        @endif
                                        @if($current_restaurant->lunch_available == 1)
                                        <option value="2">Lunch</option>
                                        @endif
                                        @if($current_restaurant->dinner_available == 1)
                                        <option value="3">Dinner</option>
                                        @endif
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
                        <button type="submit" class="btn btn-primary" id="submitBtn"
                            @if($auth_user->role_id == 1 || $auth_user->role_id == 20)
                                @if($dmcUsers->count() == 0) disabled title="Cannot submit: No DMCs available for this restaurant" @endif
                            @endif>
                            Submit
                        </button>
                        @if(($auth_user->role_id == 1 || $auth_user->role_id == 20) && $dmcUsers->count() == 0)
                            <div class="text-muted mt-2">
                                <small><i class="fas fa-exclamation-triangle text-warning"></i> Form submission disabled: Restaurant not associated with any DMCs</small>
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-datatable table-responsive pt-0">
                <div class="d-flex justify-content-between align-items-center" style="margin: 15px;">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0">Meals</h5>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        

                        <!-- Export Dropdown Button -->
                        <div class="dropdown">
                            <button class="btn btn-warning btn-sm dropdown-toggle" type="button" id="exportDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-download"></i> Export
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportCopy">Copy</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportCSV">CSV</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportExcel">Excel</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportPDF">PDF</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0);" id="exportPrint">Print</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                    <table class="datatables-basic table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Restaurant Name</th>
                                <th>Meal Type</th>
                                {{-- <th>Item Name</th> --}}
                                <th>Beverage</th>
                                <th>Type</th>
                                {{-- <th>Item Description</th> --}}
                                <th>Status</th>
                                @if(hasPermission('edit meal') || hasPermission('delete meal'))
                                <th>Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($meals as $key => $meal)
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td>
                                        @if($meal->restaurant)
                                            {{$meal->restaurant->name}}
                                        @else
                                            Unknown
                                        @endif
                                    </td>
                                    <td>
                                        @if($meal->meal_period == 1)
                                            Breakfast
                                        @elseif($meal->meal_period == 2)
                                            Lunch
                                        @elseif($meal->meal_period == 3)
                                            Dinner
                                        @else
                                            Unknown
                                        @endif
                                    </td>
                                    <td>
                                        @if($meal->category == 1)
                                            Alcoholic
                                        @elseif($meal->category == 2)
                                            Non Alcoholic
                                        @elseif($meal->category == 3)
                                            No Beverage
                                        @else
                                            Unknown
                                        @endif
                                    </td>
                                    {{-- <td class="category-name">
                                        @if($meal->name)
                                            {{ $meal->name }}
                                        @else
                                            {{ 'N/A' }}
                                        @endif
                                    </td> --}}
                                    <td>
                                        @if($meal->type == 1)
                                            Buffet
                                        @elseif($meal->type == 2)
                                            Set Menu
                                        @elseif($meal->type == 3)
                                            A-La-carte
                                        @else
                                            Unknown
                                        @endif
                                    </td>
                                    {{-- <td>
                                        {{$meal->item_description}}
                                    </td> --}}
                                    <td>
                                        @if($meal->is_active == 1)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    @if(hasPermission('edit meal') || hasPermission('delete meal'))
                                    <td style="white-space: nowrap;">
                                        <!-- Edit Button -->
                                        @if(hasPermission('edit meal'))
                                        <a href="{{ route('meals.edit', Crypt::encrypt($meal->meal_id)) }}" 
                                        class="btn btn-primary btn-sm rounded-circle" 
                                        style="width: 28px; height: 28px; padding: 0;">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                                                <path d="M200-200h57l391-391-57-57-391 391v57Zm-80 80v-170l528-527q12-11 26.5-17t30.5-6q16 0 31 6t26 18l55 56q12 11 17.5 26t5.5 30q0 16-5.5 30.5T817-647L290-120H120Zm640-584-56-56 56 56Zm-141 85-28-29 57 57-29-28Z"/>
                                            </svg>
                                        </a>
                                        @endif
    
                                        <!-- Delete Button -->
                                        @if(hasPermission('delete meal'))
                                        <button type="button" 
                                                class="btn btn-danger btn-sm rounded-circle" 
                                                style="width: 28px; height: 28px; padding: 0;" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal" 
                                                onclick="setDeleteForm('{{ route('meals.destroy', Crypt::encrypt($meal->meal_id)) }}')">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="16px" viewBox="0 -960 960 960" width="16px" fill="#ffffff">
                                                <path d="M280-120q-33 0-56.5-23.5T200-200v-520h-40v-80h200v-40h240v40h200v80h-40v520q0 33-23.5 56.5T680-120H280Zm400-600H280v520h400v-520ZM360-280h80v-360h-80v360Zm160 0h80v-360h-80v360ZM280-720v520-520Z"/>
                                            </svg>
                                        </button>
                                        @endif
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

<!-- Restaurant Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" 
        aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmation</h5>
            </div>
            <div class="modal-body">
                Are you sure want to delete?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        var adultPriceContainer = document.getElementById("adult_price_container");
        var childPriceContainer = document.getElementById("child_price_container");
        var vegContainer = document.getElementById("veg_container");
        var vegSelect = document.getElementById("veg_type");

        var itemNameInput = document.querySelector("input[name='name']");
        var itemFileInput = document.querySelector("input[name='item_file']");
        var itemPriceInput = document.querySelector("input[name='price']");
        var adultPriceInput = document.querySelector("input[name='adult_price']");
        var childPriceInput = document.querySelector("input[name='child_price']");

        if (mealType === "1" || mealType === "2") { // Buffet or Set Menu
            itemFileContainer.style.display = "block";
            itemNameContainer.style.display = "none";
            
            if(mealType === "1"){
                adultPriceContainer.style.display = "block";
                childPriceContainer.style.display = "block";
                vegContainer.style.display = "none";
                itemPriceContainer.style.display = "none";
                vegSelect.removeAttribute("required");
                itemNameInput.removeAttribute("required");
                itemPriceInput.removeAttribute("required");
                // Set validation for adult and child prices
                adultPriceInput.dataset.interacted = adultPriceInput.value.trim() === '' ? "true" : "false";
                childPriceInput.dataset.interacted = childPriceInput.value.trim() === '' ? "true" : "false";
            }
            else{
                adultPriceContainer.style.display = "none";
                childPriceContainer.style.display = "none";
                itemPriceContainer.style.display = "block";
                vegContainer.style.display = "block";

                
                // Set validation for item price
                itemPriceInput.dataset.interacted = itemPriceInput.value.trim() === '' ? "true" : "false";
            }

            // Clear hidden fields
            itemNameInput.value = "";

            // Set required attribute correctly
            itemFileInput.removeAttribute("required");
            itemNameInput.removeAttribute("required");
            itemPriceInput.removeAttribute("required");
        } else if (mealType === "3") { // A-La-Carte
            itemFileContainer.style.display = "none";
            itemNameContainer.style.display = "block";
            itemPriceContainer.style.display = "block";
            adultPriceContainer.style.display = "none";
            childPriceContainer.style.display = "none";
            vegContainer.style.display = "block";
            // Clear hidden file input
            itemFileInput.value = "";

            // Set required attribute correctly
            itemNameInput.setAttribute("required", "required");
            itemPriceInput.setAttribute("required", "required");
            itemFileInput.removeAttribute("required");
            
            // Set validation for item price
            itemPriceInput.dataset.interacted = itemPriceInput.value.trim() === '' ? "true" : "false";
        } else { // Default case (no selection)
            itemFileContainer.style.display = "none";
            itemNameContainer.style.display = "none";
            itemPriceContainer.style.display = "none";

            // Clear all fields
            itemNameInput.value = "";
            itemFileInput.value = "";
            itemPriceInput.value = "";

            // Remove required attributes
            itemNameInput.removeAttribute("required");
            itemFileInput.removeAttribute("required");
            itemPriceInput.removeAttribute("required");
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
        // Form submission validation for DMC availability
        const form = document.getElementById('restaurantForm');
        const dmcSelect = document.getElementById('dmc_selection');
        const submitBtn = document.getElementById('submitBtn');
        
        @if(($auth_user->role_id == 1 || $auth_user->role_id == 20) && $dmcUsers->count() == 0)
        // Prevent form submission when no DMCs are available
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                alert('Cannot submit: This restaurant is not associated with any DMCs. Please contact administrator to assign DMCs first.');
                return false;
            });
        }
        @endif
        
        // Show/hide submit button based on DMC selection
        if (dmcSelect && submitBtn) {
            dmcSelect.addEventListener('change', function() {
                @if($auth_user->role_id == 1 || $auth_user->role_id == 20)
                if (this.value === '' && !this.disabled) {
                    submitBtn.disabled = true;
                    submitBtn.title = 'Please select a DMC first';
                } else if (!this.disabled) {
                    submitBtn.disabled = false;
                    submitBtn.title = '';
                }
                @endif
            });
        }
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
        
        // Call toggleFields to ensure proper initial state
        toggleFields();
    });
    
    // Function to set delete form action
    function setDeleteForm(action) {
        console.log('setDeleteForm called with action:', action);
        const form = document.getElementById('deleteForm');
        if (form) {
            form.action = action;
            console.log('Form action set to:', form.action);
        } else {
            console.error('Delete form not found!');
        }
    }
    
    // Test function to manually open modal
    function testModal() {
        console.log('Testing modal...');
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }
    
    // Add event listener when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, checking modal elements...');
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        console.log('Modal element:', modal);
        console.log('Form element:', form);
        
        // Test if Bootstrap Modal is available
        if (typeof bootstrap !== 'undefined') {
            console.log('Bootstrap is available');
        } else {
            console.error('Bootstrap is not available');
        }
    });
</script>
@endsection
