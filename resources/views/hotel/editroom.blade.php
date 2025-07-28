@extends('layouts.layout')
@section('title', 'Hotels')
@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<link href="{{ URL::asset('build/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
@endsection
@section('content')

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Edit Room Category
                <a href="javascript:history.back()" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form id="hotelForm" method="POST" action="{{ route('room.update') }}" enctype="multipart/form-data"
                class="card-body">
                @csrf
                <input type="hidden" value="{{ $room->room_id }}" name="room_id" class="form-control"
                    placeholder="Enter Room Category"></input>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="hotel_id" class="form-label">
                            <strong>Hotel</strong>
                            <span style="color: red; font-weight: bold;">*</span>
                        </label>
                        <!-- <select name="hotel_id" id="hotel_id" class="form-control" required readonly>
                            <option value="">Select a Hotel</option>
                            @foreach($hotel as $h)
                            <option value="{{ $h->hotel_unique_id }}"
                                {{ $room->hotel_id == $h->hotel_unique_id ? 'selected' : '' }}>
                                {{ $h->name }} 
                            </option>
                            @endforeach
                        </select> -->
                        <input type="hidden" name="hotel_id" value="{{ $room->hotel_id }}">
                        <input type="text" class="form-control" value="{{ $hotel->where('hotel_unique_id', $room->hotel_id)->first()->name ?? '' }}" readonly>
                    </div>

                    <!-- Base Room Category -->
                    <div class="col-md-3 mb-3" id="base_room_type" style="display: none;">
                        <label for="base_room_type_input" class="form-label"><strong>Base Room
                                Category</strong><span class="text-danger">*</span></label>
                        <input id="base_room_type_input" value="" name="base_room_type" class="form-control"
                            placeholder="Enter Room Category"
                            {{ !in_array($auth_user->role_id, [1, 20]) ? 'readonly' : '' }}
                            style="{{ !in_array($auth_user->role_id, [1, 20]) ? 'background-color:#f8f9fa;cursor:not-allowed;' : '' }}">
                        @if(!in_array($auth_user->role_id, [1, 20]))
                            <small class="text-muted">(Only admin can modify room category)</small>
                        @endif
                        @error('base_room_type')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Room Category -->
                    <div class="col-md-3 mb-3" id="room_type" style="display: none;">
                        <label for="room_type" class="form-label"><strong>Room Category</strong><span
                                class="text-danger">*</span></label>
                        <input value="" name="room_type" id="room_type_input" class="form-control"
                            placeholder="Enter Room Category"
                            {{ !in_array($auth_user->role_id, [1, 20]) ? 'readonly' : '' }}
                            style="{{ !in_array($auth_user->role_id, [1, 20]) ? 'background-color:#f8f9fa;cursor:not-allowed;' : '' }}">
                        @if(!in_array($auth_user->role_id, [1, 20]))
                            <small class="text-muted">(Only admin can modify room category)</small>
                        @endif
                        @error('room_type')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Variant Price -->
                    <div class="col-md-3 mb-3" id="varient_price" style="display: none;">
                        <label for="varient_price_input" class="form-label"><strong>Room Rate
                                Variant</strong><span class="text-danger">*</span></label>
                        <input name="varient_price" id="varient_price_input" class="form-control"
                            placeholder="Enter Variant Price">
                        @error('varient_price')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Number of Rooms -->
                    <div class="col-md-3 mb-3">
                        <label for="total_rooms" class="form-label"><strong>Total No of Rooms</strong><span
                                class="text-danger">*</span></label>
                        <input value="{{$room->no_of_room}}" type="text" class="form-control" name="total_no_of_room"
                               id="total_rooms" placeholder="Enter Number of Rooms"
                               oninput="validateTotalRooms(this)" required>
                        <small class="validation-message text-danger" id="total_rooms-validation-message"></small>
                        @error('base_no_of_room')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- dimension -->
                    <div class="mb-3 col-md-3" id="dimension">
                        <label for="dimension_input" class="form-label"><strong>Dimension</strong></label>
                        <input value="{{$room->dimension}}" type="number" name="dimension" id="dimension_input" class="form-control"
                               placeholder="Enter Dimension"
                               {{ !in_array($auth_user->role_id, [1, 20]) ? 'readonly' : '' }}
                               style="{{ !in_array($auth_user->role_id, [1, 20]) ? 'background-color:#f8f9fa;cursor:not-allowed;' : '' }}">
                        @if(!in_array($auth_user->role_id, [1, 20]))
                            <small class="text-muted">(Only admin can modify dimension)</small>
                        @endif
                        <small class="validation-message text-danger" id="dimension_input-validation-message"></small>
                    </div>

                    <!-- Children Price -->
                    <div class="mb-3 col-md-3">
                        <label for="children_breakfast_price" class="form-label"><strong>Meal
                                Children Price</strong></label>
                        <select name="children_price" id="children_breakfast_price" class="form-control">
                            <option value="">Please Select One</option>
                            <option {{$room->children_price == 0 ? 'selected' : ''}} value="0">Free
                            </option>
                            <option {{$room->children_price == 1 ? 'selected' : ''}} value="1">Half
                                Price</option>
                            <option {{$room->children_price == 2 ? 'selected' : ''}} value="2">Full
                                Price</option>
                        </select>
                    </div>

                    <!-- Single weekday weekend price -->
                    <div class="col-md-6" id="single_price" style="display: none;">
                        <div class="mb-3">
                            <fieldset class="border p-1 position-relative">
                                <legend>Single</legend>
                                <div class="row g-2">
                                    <div class="col-md-6 form-floating">
                                        <input type="text" id="singleWeekdayPrice" name="singleWeekdayPrice" class="form-control" placeholder=" ">
                                        <label for="singleWeekdayPrice">Weekday Price</label>
                                        @if($auth_user->user_type == 2)
                                        <span class="text-primary">Your calculated price: <span id="totalSingleWeekdayPrice">{{ $single_weekday_price }}</span></span>
                                        @endif
                                    </div>
                                    <div class="col-md-6 form-floating">
                                        <input type="text" id="singleWeekendPrice" name="singleWeekendPrice" class="form-control" placeholder=" ">
                                        <label for="singleWeekendPrice">Weekend Price</label>
                                        @if($auth_user->user_type == 2)
                                        <span class="text-primary">Your calculated price: <span id="totalSingleWeekendPrice">{{ $single_weekend_price }}</span></span>
                                        @endif
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>

                    <!-- Double weekday weekend price -->
                    <div class="col-md-6" id="double_price" style="display: none;">
                        <div class="mb-3">
                            <fieldset class="border p-1 position-relative">
                                <legend>Double</legend>
                                <div class="row g-2">
                                    <div class="col-md-6 form-floating">
                                        <input type="text" id="doubleWeekdayPrice" name="doubleWeekdayPrice" class="form-control" placeholder=" ">
                                        <label for="doubleWeekdayPrice">Weekday Price</label>
                                        @if($auth_user->user_type == 2)
                                        <span class="text-primary">Your calculated price: <span id="totalSingleWeekdayPrice">{{ $double_weekday_price }}</span></span>
                                        @endif
                                        
                                    </div>
                                    <div class="col-md-6 form-floating">
                                        <input type="text" id="doubleWeekendPrice" name="doubleWeekendPrice" class="form-control" placeholder=" ">
                                        <label for="doubleWeekendPrice">Weekend Price</label>
                                        @if($auth_user->user_type == 2)
                                        <span class="text-primary">Your calculated price: <span id="totalDoubleWeekendPrice">{{ $double_weekend_price }}</span></span>
                                        @endif
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>

                    <!-- Base Single weekday weekend -->
                    <div class="col-md-6" id="base_single_price" style="display: none;">
                        <div class="mb-3">
                            <fieldset class="border p-1 position-relative">
                                <legend>Single</legend>
                                <div class="row g-2">
                                    <div class="col-md-6 form-floating">
                                        <input type="text" id="baseSingleWeekdayPrice" name="baseSingleWeekdayPrice" class="form-control" placeholder=" ">
                                        <label for="baseSingleWeekdayPrice">Base Weekday Price</label>
                                        @if($auth_user->user_type == 2)
                                        <span class="text-primary">Your calculated price: <span id="totalWeekdayPrice"> {{ $single_weekday_price }}</span></span>
                                        @endif
                                        
                                    </div>
                                    <div class="col-md-6 form-floating">
                                        <input type="text" id="baseSingleWeekendPrice" name="baseSingleWeekendPrice" class="form-control" placeholder=" ">
                                        <label for="baseSingleWeekendPrice">Base Weekend Price</label>
                                        @if($auth_user->user_type == 2)
                                        <span class="text-primary">Your calculated price: <span id="totalWeekendPrice">{{ $single_weekend_price }}</span></span>
                                        @endif
                                      
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>

                    <!-- Base Double weekday weekend -->
                    <div class="col-md-6" id="base_double_price" style="display: none;">
                        <div class="mb-3">
                            <fieldset class="border p-1 position-relative">
                                <legend>Double</legend>
                                <div class="row g-2">
                                    <div class="col-md-6 form-floating">
                                        <input type="text" id="baseDoubleWeekdayPrice" name="baseDoubleWeekdayPrice" class="form-control" placeholder=" ">
                                        <label for="baseDoubleWeekdayPrice">Base Weekday Price</label>
                                        @if($auth_user->user_type == 2)
                                        <span class="text-primary">Your calculated price: <span id="totalBaseDoubleWeekdayPrice">{{ $double_weekday_price }}</span></span>
                                        @endif
                                    </div>
                                    <div class="col-md-6 form-floating">
                                        <input type="text" id="baseDoubleWeekendPrice" name="baseDoubleWeekendPrice" class="form-control" placeholder=" ">
                                        <label for="baseDoubleWeekendPrice">Base Weekend Price</label>
                                        @if($auth_user->user_type == 2)
                                        <span class="text-primary">Your calculated price: <span id="totalBaseDoubleWeekendPrice">{{ $double_weekend_price }}</span></span>
                                        @endif
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>

                    <!-- Meal Options Section -->
                    <div class="mb-3 row">
                        <!-- Breakfast Toggle -->
                        <div class="col-md-3 mb-3">
                            <label for="breakfast_included" class="form-label"><strong>Breakfast Included</strong></label>
                            <select name="breakfast_included" id="breakfast_included" class="form-control" onchange="toggleMealOptions('breakfast')">
                                <option value="">Select One</option>
                                <option value="1" {{ $room->breakfast ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ !$room->breakfast ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        
                        <!-- Breakfast Type - Shows when breakfast is included -->
                        <div class="col-md-3 mb-3 breakfast-options" style="display: none;">
                            <label for="breakfast_type" class="form-label"><strong>Breakfast Type</strong><span class="text-danger">*</span></label>
                            <select name="breakfast_type" id="breakfast_type" class="form-control">
                                <option value="">Select Type</option>
                                <option value="Buffet" {{ $room->breakfast_type == 'Buffet' ? 'selected' : '' }}>Buffet</option>
                                <option value="Set Menu" {{ $room->breakfast_type == 'Set Menu' ? 'selected' : '' }}>Set Menu</option>
                            </select>
                        </div>
                        
                        <!-- Breakfast Price - Shows when breakfast is included -->
                        <div class="col-md-3 mb-3 breakfast-options" style="display: none;">
                            <label for="breakfast_price" class="form-label"><strong>Breakfast Price</strong><span class="text-danger">*</span></label>
                            <input type="number" name="breakfast_price" id="breakfast_price" class="form-control" 
                                   placeholder="Enter Price" min="0" step="0.01" 
                                   value="{{ $room->breakfast_price }}">
                        </div>
                        
                        <!-- Lunch Toggle -->
                        <div class="col-md-3 mb-3">
                            <label for="lunch_included" class="form-label"><strong>Lunch Included</strong></label>
                            <select name="lunch_included" id="lunch_included" class="form-control" onchange="toggleMealOptions('lunch')">
                                <option value="">Select One</option>
                                <option value="1" {{ $room->lunch ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ !$room->lunch ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        
                        <!-- Lunch Type - Shows when lunch is included -->
                        <div class="col-md-3 mb-3 lunch-options" style="display: none;">
                            <label for="lunch_type" class="form-label"><strong>Lunch Type</strong><span class="text-danger">*</span></label>
                            <select name="lunch_type" id="lunch_type" class="form-control">
                                <option value="">Select Type</option>
                                <option value="Buffet" {{ $room->lunch_type == 'Buffet' ? 'selected' : '' }}>Buffet</option>
                                <option value="Set Menu" {{ $room->lunch_type == 'Set Menu' ? 'selected' : '' }}>Set Menu</option>
                            </select>
                        </div>
                        
                        <!-- Lunch Price - Shows when lunch is included -->
                        <div class="col-md-3 mb-3 lunch-options" style="display: none;">
                            <label for="lunch_price" class="form-label"><strong>Lunch Price</strong><span class="text-danger">*</span></label>
                            <input type="number" name="lunch_price" id="lunch_price" class="form-control" 
                                   placeholder="Enter Price" min="0" step="0.01" 
                                   value="{{ $room->lunch_price }}">
                        </div>
                        
                        <!-- Dinner Toggle -->
                        <div class="col-md-3 mb-3">
                            <label for="dinner_included" class="form-label"><strong>Dinner Included</strong></label>
                            <select name="dinner_included" id="dinner_included" class="form-control" onchange="toggleMealOptions('dinner')">
                                <option value="">Select One</option>
                                <option value="1" {{ $room->dinner ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ !$room->dinner ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        
                        <!-- Dinner Type - Shows when dinner is included -->
                        <div class="col-md-3 mb-3 dinner-options" style="display: none;">
                            <label for="dinner_type" class="form-label"><strong>Dinner Type</strong><span class="text-danger">*</span></label>
                            <select name="dinner_type" id="dinner_type" class="form-control">
                                <option value="">Select Type</option>
                                <option value="Buffet" {{ $room->dinner_type == 'Buffet' ? 'selected' : '' }}>Buffet</option>
                                <option value="Set Menu" {{ $room->dinner_type == 'Set Menu' ? 'selected' : '' }}>Set Menu</option>
                            </select>
                        </div>
                        
                        <!-- Dinner Price - Shows when dinner is included -->
                        <div class="col-md-3 mb-3 dinner-options" style="display: none;">
                            <label for="dinner_price" class="form-label"><strong>Dinner Price</strong><span class="text-danger">*</span></label>
                            <input type="number" name="dinner_price" id="dinner_price" class="form-control" 
                                   placeholder="Enter Price" min="0" step="0.01" 
                                   value="{{ $room->dinner_price }}">
                        </div>
                        
                        <!-- Supplementary Breakfast Toggle -->
                        <div class="col-md-3 mb-3">
                            <label for="supplementary_breakfast" class="form-label"><strong>Complementary Breakfast Included</strong></label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="supplementary_breakfast" 
                                       id="supplementary_breakfast" value="1" 
                                       {{ $room->breakfast_included ? 'checked' : '' }}>
                                <label class="form-check-label" for="supplementary_breakfast">Enable Complementary Breakfast</label>
                            </div>
                        </div>
                    </div>

                    @if(in_array($auth_user->role_id, [1, 20]))
                    <!-- Image sections - Only visible to admin users -->
                    <div class="row col-md-12">
                        <!-- Master image -->
                        <div class="mt-3 mb-3 col-md-4">
                            <div>
                                <label for="master_image" class="form-label"><strong>Master
                                        Image</strong><span style="color: red; font-weight: bold;">*</span></label>
                                <div id="master-drop-area" class="form-control"
                                    style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px;">
                                    Drag & Drop your files here or click to upload.
                                    <input type="file" id="master_image" name="master_image" multiple
                                        style="display: none;">
                                </div>
                            </div>
                            <div id="master-preview-container" class="mb-3 mt-3 d-flex flex-wrap gap-2"
                                style="max-width: 30%; overflow-x: auto; white-space: nowrap;"></div>

                            @if($room->master_image)
                            <div class="existing-master-image-preview-container d-flex flex-wrap gap-2">
                                <div class="existing-master-image-preview-wrapper position-relative">
                                    <img src="{{$room->master_image}}" alt="Room Master Image"
                                        style="max-width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                                    <button class="delete-image-btn position-absolute top-0 end-0 btn btn-sm btn-danger"
                                        data-image="{{ $room->master_image }}"
                                        style="width: 20px; height: 20px; line-height: 18px; padding: 0; text-align: center; font-size: 14px; z-index: 1;">
                                        &times;
                                    </button>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Additional Image drop -->
                        <div class="mt-3 mb-3 col-md-8">
                            <div>
                                <label for="images" class="form-label"><strong>Additional
                                        Images</strong></label>
                                <div id="drop-area" class="form-control"
                                    style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px;">
                                    Drag & Drop your files here or click to upload.
                                    <input type="file" id="images" name="images[]" multiple style="display: none;">
                                </div>

                                <div id="preview-container" class="mb-3 mt-3 d-flex flex-wrap gap-2"
                                    style="max-width: 100%; overflow-x: auto; white-space: nowrap;"></div>
                            </div>

                            <!-- Existing Image Section -->
                            <div class="existing-image-preview-container d-flex flex-wrap gap-2">
                                @php
                                $images = json_decode($room->images, true);
                                @endphp
                                @foreach($images as $img)
                                <!-- Hidden input to hold existing image path -->
                                <div class="existing-image-preview-wrapper position-relative">
                                    <input type="hidden" name="existing_images[]" value="{{ $img }}">
                                    <img src="{{ asset($img) }}" alt="Facility Image"
                                        style="max-width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                                    <button class="delete-image-btn position-absolute top-0 end-0 btn btn-sm btn-danger"
                                        data-image="{{ $img }}"
                                        style="width: 20px; height: 20px; line-height: 18px; padding: 0; text-align: center; font-size: 14px; z-index: 1;">
                                        &times;
                                    </button>
                                </div>
                                @endforeach
                            </div>
                            <input type="file" name="all_images[]" id="all-images" style="display: none;">

                            @error('images')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                            <div id="preview-container" class="mt-3 d-flex flex-wrap gap-2"
                                style="max-width: 100%; overflow-x: auto; white-space: nowrap;"></div>
                        </div>
                    </div>
                    @else
                    <!-- Hidden image inputs for DMC users -->
                    <input type="hidden" name="master_image" value="{{ $room->master_image }}">
                    <input type="hidden" name="existing_images[]" value="{{ json_encode($room->images) }}">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Image management is only available for admin users.
                    </div>
                    @endif
                </div>

                <!-- Status -->
                <div class="form-check form-switch">
                    <label for="room_status" class="form-label"><strong>Status</strong></label>
                    <span style="color: red; font-weight: bold;">*</span>
                    <input {{$room->status == 1 ? 'checked' : ''}} class="form-check-input" name="room_status"
                        type="checkbox" id="room_status" value="1">
                    <label class="form-check-label"></label>
                    @error('room_status')
                    <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Submit Buttons -->
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary px-4">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prevent modification of readonly fields for non-admin users
    const isAdmin = {{ in_array($auth_user->role_id, [1, 20]) ? 'true' : 'false' }};
    
    if (!isAdmin) {
        // Prevent any modification attempts on readonly fields
        $('#base_room_type_input, #room_type_input, #dimension_input').on('keydown paste drop', function(e) {
            e.preventDefault();
            return false;
        });
        
        // Show tooltip when trying to modify readonly fields
        $('#base_room_type_input, #room_type_input, #dimension_input').on('click', function() {
            const $this = $(this);
            if (!$this.next('.tooltip').length) {
                $('<div class="tooltip">Only admin can modify this field</div>')
                    .insertAfter($this)
                    .fadeIn()
                    .delay(2000)
                    .fadeOut(function() { $(this).remove(); });
            }
        });
    }
    
    // Get hotel data from server-rendered Blade variable
    const room = @json($room);
    const baseRoom = @json($baseRoom);

    if (room.room_type != "Standard") {
        // Hide the specified base divs
        document.getElementById('base_room_type').style.display = "none";
        document.getElementById('room_type').style.display = "block";
        document.getElementById('room_type_input').value = room.room_type;

        document.getElementById('base_single_price').style.display = "none";
        document.getElementById('base_double_price').style.display = "none";

        document.getElementById('single_price').style.display = "block";
        document.getElementById('double_price').style.display = "block";

        const singleWeekdayPrice = document.getElementById('singleWeekdayPrice');
        const singleWeekendPrice = document.getElementById('singleWeekendPrice');

        const doubleWeekdayPrice = document.getElementById('doubleWeekdayPrice');
        const doubleWeekendPrice = document.getElementById('doubleWeekendPrice');

        const varientPriceInput = document.getElementById('varient_price_input');
        const varientPriceDiv = document.getElementById('varient_price');
        varientPriceDiv.style.display = "block";
        varientPriceInput.value = room.varient_price;

        // room data population
        singleWeekdayPrice.value = room.weekday_price || 0;
        singleWeekendPrice.value = room.weekend_price || 0;
        doubleWeekendPrice.value = room.double_weekend_price || 0;
        doubleWeekdayPrice.value = room.double_weekday_price || 0;

        // Update prices dynamically based on variance price
        varientPriceInput.addEventListener('input', function() {
            const varientValue = parseFloat(varientPriceInput.value) ||
                0; // Default to 0 if input is invalid
            singleWeekdayPrice.value = (parseFloat(baseRoom?.weekday_price || 0) + varientValue)
                .toFixed(2);
            singleWeekendPrice.value = (parseFloat(baseRoom?.weekend_price || 0) + varientValue)
                .toFixed(2);
            doubleWeekdayPrice.value = (parseFloat(baseRoom?.double_weekday_price || 0) +
                varientValue).toFixed(2);
            doubleWeekendPrice.value = (parseFloat(baseRoom?.double_weekend_price || 0) +
                varientValue).toFixed(2);
        });
    } else {
        // Show base room divs if no room data is available
        document.getElementById('single_price').style.display = "none";
        document.getElementById('double_price').style.display = "none";

        const baseRoomType = document.getElementById('base_room_type');
        baseRoomType.style.display = "block";
        const baseRoomTypeInput = document.getElementById('base_room_type_input');
        baseRoomTypeInput.value = room.room_type;

        const baseSinglePrice = document.getElementById('base_single_price');
        baseSinglePrice.style.display = "block";
        const baseDoublePrice = document.getElementById('base_double_price').style.display = "block";

        document.getElementById('baseSingleWeekdayPrice').value = room.weekday_price;
        document.getElementById('baseSingleWeekendPrice').value = room.weekend_price;
        document.getElementById('baseDoubleWeekdayPrice').value = room.double_weekday_price;
        document.getElementById('baseDoubleWeekendPrice').value = room.double_weekend_price;

    }
});
</script>

<!-- Additional Image drop down -->
<script>
    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('images');
    const fileList = document.getElementById('preview-container');
    const allImagesInput = document.getElementById('all-images'); // Hidden input
    let files = []; // Store all files manually
    const MAX_VISIBLE_IMAGES = 3; // Maximum number of visible images
    let showAllImages = false; // Toggle for showing all images

    // Trigger file input on click
    dropArea.addEventListener('click', () => fileInput.click());

    // Handle file input change
    fileInput.addEventListener('change', () => handleFiles(fileInput.files));

    // Handle drag-and-drop events
    dropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropArea.style.borderColor = '#000';
    });

    dropArea.addEventListener('dragleave', () => {
        dropArea.style.borderColor = '#ccc';
    });

    dropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        dropArea.style.borderColor = '#ccc';
        handleFiles(e.dataTransfer.files);
    });

    function handleFiles(newFiles) {
        // Append new files to the list
        files = [...files, ...Array.from(newFiles)];
        updateFileList();
    }

    function updateFileList() {
        // Clear file list display
        fileList.innerHTML = '';
        const dataTransfer = new DataTransfer();

        // Decide how many files to display based on `showAllImages`
        const visibleFiles = showAllImages ? files : files.slice(0, MAX_VISIBLE_IMAGES);

        visibleFiles.forEach((file, index) => {
            // Create a wrapper for the image and delete button
            const imageWrapper = document.createElement('div');
            imageWrapper.style.position = 'relative';
            imageWrapper.style.display = 'inline-block';
            imageWrapper.style.margin = '10px';
            imageWrapper.style.width = '100px';
            imageWrapper.style.height = '100px';

            // Create an image element for preview
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file); // Create an object URL for the file
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';

            // Create a delete button
            const deleteButton = document.createElement('button');
            deleteButton.textContent = '×';
            deleteButton.style.position = 'absolute';
            deleteButton.style.top = '2px';
            deleteButton.style.right = '2px';
            deleteButton.style.background = 'rgba(255, 0, 0, 0.8)';
            deleteButton.style.color = 'white';
            deleteButton.style.border = 'none';
            deleteButton.style.borderRadius = '50%';
            deleteButton.style.cursor = 'pointer';
            deleteButton.style.width = '20px';
            deleteButton.style.height = '20px';
            deleteButton.style.fontSize = '12px';
            deleteButton.style.lineHeight = '16px';

            // Remove file and update list on delete
            deleteButton.addEventListener('click', () => {
                const fileIndex = files.indexOf(file);
                if (fileIndex > -1) {
                    files.splice(fileIndex, 1);
                }
                updateFileList();
            });

            // Append image and delete button to the wrapper
            imageWrapper.appendChild(img);
            imageWrapper.appendChild(deleteButton);
            fileList.appendChild(imageWrapper);

            // Add the file to the DataTransfer object
            dataTransfer.items.add(file);
        });

        // Add all files to the hidden input `all-images`
        const hiddenDataTransfer = new DataTransfer();
        files.forEach(file => hiddenDataTransfer.items.add(file));
        allImagesInput.files = hiddenDataTransfer.files;

        // Add a "More Images" badge if there are more files and not showing all images
        if (!showAllImages && files.length > MAX_VISIBLE_IMAGES) {
            const moreBadge = document.createElement('div');
            moreBadge.textContent = `+${files.length - MAX_VISIBLE_IMAGES} more`;
            moreBadge.style.margin = '10px';
            moreBadge.style.padding = '20px';
            moreBadge.style.backgroundColor = '#007bff';
            moreBadge.style.color = 'white';
            moreBadge.style.borderRadius = '5px';
            moreBadge.style.textAlign = 'center';
            moreBadge.style.fontSize = '14px';
            moreBadge.style.cursor = 'pointer';

            // Add click event to show all images
            moreBadge.addEventListener('click', () => {
                showAllImages = true;
                updateFileList(); // Re-render with all images
            });

            fileList.appendChild(moreBadge);
        }
    }
</script>

<!-- delete existing additional Image -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Use event delegation for dynamically added elements
    document.querySelector('.existing-image-preview-container').addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-image-btn')) {
            e.preventDefault(); // Prevent form submission
            e.stopPropagation(); // Stop event propagation
            const button = e.target;

            // Find the image preview wrapper
            const imageWrapper = button.closest('.existing-image-preview-wrapper');
            if (imageWrapper) {
                // Find and remove the associated hidden input field for the image
                const hiddenInput = imageWrapper.querySelector('input[type="hidden"]');
                if (hiddenInput) {
                    hiddenInput.remove(); // Remove the hidden input
                }

                // Remove the image wrapper (image and button)
                imageWrapper.remove();
            }
        }
    });
});
</script>

<!-- delete existing master Image -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Use event delegation for dynamically added elements
    document.querySelector('.existing-master-image-preview-container').addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-image-btn')) {
            e.preventDefault(); // Prevent form submission
            e.stopPropagation(); // Stop event propagation
            const button = e.target;

            // Find the image preview wrapper
            const imageWrapper = button.closest('.existing-master-image-preview-wrapper');
            if (imageWrapper) {
                // Find and remove the associated hidden input field for the image
                const hiddenInput = imageWrapper.querySelector('input[type="hidden"]');
                if (hiddenInput) {
                    hiddenInput.remove(); // Remove the hidden input
                }

                // Remove the image wrapper (image and button)
                imageWrapper.remove();
            }
        }
    });
});
</script>

<!-- Master Image drop down -->
<script>
const masterDropArea = document.getElementById('master-drop-area');
const masterFileInput = document.getElementById('master_image');
const masterPreviewContainer = document.getElementById('master-preview-container');
let masterFileCounter = 0; // Track total uploaded files
const MASTER_MAX_VISIBLE_IMAGES = 1; // Show only 1 image

// Open file picker on click
masterDropArea.addEventListener('click', () => masterFileInput.click());

// Handle drag events
masterDropArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    masterDropArea.style.backgroundColor = '#e3f2fd';
});

masterDropArea.addEventListener('dragleave', () => {
    masterDropArea.style.backgroundColor = 'white';
});

masterDropArea.addEventListener('drop', (e) => {
    e.preventDefault();
    masterDropArea.style.backgroundColor = 'white';
    masterHandleFiles(e.dataTransfer.files);
});

// Handle file input change
masterFileInput.addEventListener('change', () => {
    masterHandleFiles(masterFileInput.files);
});

// Process and display files
function masterHandleFiles(files) {
    Array.from(files).forEach(file => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                // If an image already exists, remove it before adding the new one
                if (masterFileCounter > 0) {
                    masterPreviewContainer.innerHTML = ''; // Clear the existing preview
                    masterFileCounter = 0; // Reset the file counter
                }
                masterFileCounter++;
                masterImagePreview(e.target.result);
            };
            reader.readAsDataURL(file);
        } else {
            alert(`${file.name} is not a valid image file.`);
        }
    });
}

// Add image preview with limited visibility and a "more" badge
function masterImagePreview(imageSrc) {

    const imageWrapper = document.createElement('div');
    imageWrapper.style.position = 'relative';
    imageWrapper.style.width = '70px';
    imageWrapper.style.height = '70px';
    imageWrapper.style.margin = '5px';
    imageWrapper.style.overflow = 'hidden';
    imageWrapper.style.borderRadius = '5px';

    const img = document.createElement('img');
    img.src = imageSrc;
    img.style.width = '100%';
    img.style.height = '100%';
    img.style.objectFit = 'cover';

    const deleteButton = document.createElement('button');
    deleteButton.textContent = '×';
    deleteButton.style.position = 'absolute';
    deleteButton.style.top = '2px';
    deleteButton.style.right = '2px';
    deleteButton.style.background = 'rgba(255, 0, 0, 0.8)';
    deleteButton.style.color = 'white';
    deleteButton.style.border = 'none';
    deleteButton.style.borderRadius = '50%';
    deleteButton.style.cursor = 'pointer';
    deleteButton.style.width = '20px';
    deleteButton.style.height = '20px';
    deleteButton.style.fontSize = '12px';
    deleteButton.style.lineHeight = '16px';
    deleteButton.addEventListener('click', () => {
        masterPreviewContainer.removeChild(imageWrapper);
        masterFileCounter--;
        updateMoreBadge();
    });

    imageWrapper.appendChild(img);
    imageWrapper.appendChild(deleteButton);
    masterPreviewContainer.appendChild(imageWrapper);

    updateMoreBadge();
}

// Create and update "+X more" badge
function updateMoreBadge() {
    // Remove any existing badge
    const existingBadge = document.getElementById('more-badge');
    if (existingBadge) existingBadge.remove();

    if (masterFileCounter > MASTER_MAX_VISIBLE_IMAGES) {
        const moreMasterBadge = document.createElement('div');
        moreMasterBadge.id = 'more-master-badge';
        moreMasterBadge.textContent = `+${masterFileCounter - MASTER_MAX_VISIBLE_IMAGES} more`;
        moreMasterBadge.style.margin = '5px';
        moreMasterBadge.style.padding = '5px 10px';
        moreMasterBadge.style.backgroundColor = '#007bff';
        moreMasterBadge.style.color = 'white';
        moreMasterBadge.style.borderRadius = '5px';
        moreMasterBadge.style.cursor = 'pointer';
        moreMasterBadge.style.fontSize = '12px';
        moreMasterBadge.style.textAlign = 'center';
        moreMasterBadge.addEventListener('click', () => {
            // Show all hidden images
            const hiddenImages = masterPreviewContainer.querySelectorAll('div[style*="display: none"]');
            hiddenImages.forEach(img => img.style.display = 'inline-block');
            moreMasterBadge.remove(); // Remove badge after revealing all
        });
        masterPreviewContainer.appendChild(moreMasterBadge);
    }
}
</script>

<!-- Food availabity -->
{{-- <script>
 // Function to toggle visibility for a given meal's options
    function toggleVisibility(meal, optionsId) {
        const select = document.getElementById(meal);
        const optionsContainer = document.getElementById(optionsId);

        if (select) {
            select.addEventListener('change', function() {
                if (this.value === '1') {
                    // Show options if "Available" is selected
                    optionsContainer.classList.remove('d-none');
                } else {
                    // Hide options if "Not Available" is selected
                    optionsContainer.classList.add('d-none');
                }
            });
        }
    }

 // Initialize for breakfast, lunch, and dinner
    toggleVisibility('breakfast', 'breakfast-options');
    toggleVisibility('lunch', 'lunch-options');
    toggleVisibility('dinner', 'dinner-options');
    toggleVisibility('booking_available', '12_hours_booking_price');
</script> --}}

<!-- set food details -->
{{-- <script>
    const breakfast = document.getElementById('breakfast');
    const lunch = document.getElementById('lunch');
    const dinner = document.getElementById('dinner');
    const roomData = @json($room);
    const breakfastContainer = document.getElementById('breakfast-options');
    const lunchContainer = document.getElementById('lunch-options');
    const dinnerContainer = document.getElementById('dinner-options');

    //get breakfast fields
    const breakfastType = document.getElementById('breakfast_type');
    const breakfastPrice = document.getElementById('breakfast_price');
    const childrenBreakfastPrice = document.getElementById('children_breakfast_price');
    const breakfastRestaurant = document.getElementById('breakfast_restaurant');

    //get lunch fields
    const lunchType = document.getElementById('lunch_type');
    const lunchPrice = document.getElementById('lunch_price');
    const lunchRestaurant = document.getElementById('lunch_restaurant');
    const childrenLunchPrice = document.getElementById('children_lunch_price');

    //get dinner fields
    const dinnerType = document.getElementById('dinner_type');
    const dinnerPrice = document.getElementById('dinner_price');
    const dinnerRestaurant = document.getElementById('dinner_restaurant');
    const childrenDinnerPrice = document.getElementById('children_dinner_price');

    if (roomData.breakfast == 1) {

        breakfastContainer.classList.remove('d-none');
    } else {
        breakfast.value = '0';
        breakfastContainer.classList.add('d-none');;
    }

    if (roomData.lunch == 1) {
        lunchContainer.classList.remove('d-none');
    } else {
        lunch.value = '0';
        lunchContainer.classList.add('d-none');
    }

    if (roomData.dinner == 1) {
        dinnerContainer.classList.remove('d-none');
    } else {
        dinner.value = "0";
        dinnerContainer.classList.add('d-none');
    }
    </script>

    <!-- legend inputs animation -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.form-floating input');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.value) {
                    this.classList.add('has-value');
                } else {
                    this.classList.remove('has-value');
                }
            });
        });
    });
</script> --}}

<script>
    var commissionType = {{ $commission_type ?? 0 }}; // 0 = Flat, 1 = Percentage
    var commissionPrice = {{ $commission_price ?? 0 }}; // Commission value

    function calculateMarkupPrice(basePrice) {
        if (!basePrice) return 0;
        return commissionType == 1 
            ? basePrice + (basePrice * commissionPrice / 100)  // Percentage-based commission
            : basePrice + commissionPrice; // Flat commission
    }

    function calculatePrice() {
        var priceFields = [
            { input: 'singleWeekdayPrice', output: 'totalSingleWeekdayPrice' },
            { input: 'singleWeekendPrice', output: 'totalSingleWeekendPrice' },
            { input: 'doubleWeekdayPrice', output: 'totalDoubleWeekdayPrice' },
            { input: 'doubleWeekendPrice', output: 'totalDoubleWeekendPrice' },
            { input: 'baseDoubleWeekdayPrice', output: 'totalBaseDoubleWeekdayPrice' },
            { input: 'baseDoubleWeekendPrice', output: 'totalBaseDoubleWeekendPrice' },
            { input: 'baseSingleWeekdayPrice', output: 'totalWeekdayPrice' },
            { input: 'baseSingleWeekendPrice', output: 'totalWeekendPrice' }
        ];

        priceFields.forEach(field => {
            var inputValue = parseFloat(document.getElementById(field.input)?.value) || 0;
            var finalPrice = calculateMarkupPrice(inputValue);
            var outputElement = document.getElementById(field.output);
            if (outputElement) {
                outputElement.innerText = finalPrice.toFixed(2);
            }
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        var inputIds = [
            'singleWeekdayPrice', 'singleWeekendPrice', 
            'doubleWeekdayPrice', 'doubleWeekendPrice', 
            'baseDoubleWeekdayPrice', 'baseDoubleWeekendPrice', 
            'baseSingleWeekdayPrice', 'baseSingleWeekendPrice'
        ];

        inputIds.forEach(function (id) {
            var input = document.getElementById(id);
            if (input) {
                input.addEventListener('keyup', calculatePrice);
                input.addEventListener('change', calculatePrice);
            }
        });

        // Initial calculation
        calculatePrice();
    });
</script>

<script>
    function showValidationMessage(inputElement, isValid, message) {
        const messageElement = document.getElementById(`${inputElement.id}-validation-message`);
        
        if (!messageElement) return;
        
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

    function validateTotalRooms(input) {
        // Force numeric input by immediately replacing non-numeric characters
        input.value = input.value.replace(/[^0-9]/g, '');
        
        const value = input.value.trim();
        const roomsRegex = /^[1-9][0-9]{0,3}$/;  // 1-9999 rooms
        
        if (value === '') {
            showValidationMessage(input, false, 'Total number of rooms is required');
        } else if (!roomsRegex.test(value)) {
            showValidationMessage(input, false, `
                Please enter a valid number of rooms:
                <ul class="mt-1 mb-0">
                    <li>Must be a positive number (1-9999)</li>
                    <li>No decimal places allowed</li>
                    <li>No leading zeros</li>
                </ul>
            `);
        } else {
            showValidationMessage(input, true, '');
        }
    }

    // function validateDimension(input) {
    //     // Allow only digits, 'x', '*', and spaces
    //     input.value = input.value.replace(/[^0-9x*\s]/g, '');
        
    //     // Format to standard format: replace all * with x and normalize spacing
    //     let value = input.value.trim().replace(/\*/g, 'x');
        
    //     // Replace multiple spaces with a single space
    //     value = value.replace(/\s+/g, ' ');
        
    //     // Ensure only one 'x' separator
    //     if ((value.match(/x/g) || []).length > 1) {
    //         const parts = value.split('x');
    //         value = parts[0] + 'x' + parts.slice(1).join('');
    //     }
        
    //     // Update the input value with the formatted value
    //     input.value = value;
        
    //     // Validate the format: number x number
    //     const dimensionRegex = /^[1-9][0-9]{0,2}(\s*[x]\s*)[1-9][0-9]{0,2}$/;
        
    //     if (value === '') {
    //         // Since dimension is optional, don't show error if empty
    //         input.classList.remove('is-invalid');
    //         input.classList.remove('is-valid');
    //         const messageElement = document.getElementById(`${input.id}-validation-message`);
    //         if (messageElement) messageElement.innerHTML = '';
    //     } else if (!dimensionRegex.test(value)) {
    //         showValidationMessage(input, false, `
    //             Please enter a valid dimension:
    //             <ul class="mt-1 mb-0">
    //                 <li>Format: length x width (e.g., 12x10)</li>
    //                 <li>Use 'x' as separator</li>
    //                 <li>Both length and width must be positive numbers (1-999)</li>
    //             </ul>
    //         `);
    //     } else {
    //         showValidationMessage(input, true, '');
    //     }
    // }

    // Add CSS for validation messages if not already included
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

            /* Input field styles */
            .is-invalid {
                border-color: #e74c3c !important;
                background-color: #fff !important;
            }

            .is-valid {
                border-color: #2ecc71 !important;
                background-color: #fff !important;
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

            /* Required field indicator */
            .required-field::after {
                content: "*";
                color: #e74c3c;
                margin-left: 4px;
            }

            /* Responsive adjustments */
            @media (max-width: 768px) {
                .validation-message {
                    font-size: 0.8rem;
                }
                
                .validation-message .invalid-feedback,
                .validation-message .valid-feedback {
                    padding: 0.5rem 0.75rem;
                }
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
</script>

<script>
    // Function to toggle meal options visibility
    function toggleMealOptions(mealType) {
        const included = document.getElementById(`${mealType}_included`).value === '1';
        const options = document.querySelectorAll(`.${mealType}-options`);
        const typeInput = document.getElementById(`${mealType}_type`);
        const priceInput = document.getElementById(`${mealType}_price`);

        options.forEach(option => {
            option.style.display = included ? 'block' : 'none';
        });

        // Set required attribute based on visibility
        if (typeInput) typeInput.required = included;
        if (priceInput) priceInput.required = included;
    }

    // Initialize meal options on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize each meal type
        ['breakfast', 'lunch', 'dinner'].forEach(mealType => {
            // Set initial visibility based on current value
            const included = document.getElementById(`${mealType}_included`);
            if (included) {
                toggleMealOptions(mealType);
                
                // Add change event listener
                included.addEventListener('change', function() {
                    toggleMealOptions(mealType);
                });
            }
        });

        // Add event listeners for price inputs to prevent negative values
        ['breakfast_price', 'lunch_price', 'dinner_price'].forEach(priceId => {
            const priceInput = document.getElementById(priceId);
            if (priceInput) {
                priceInput.addEventListener('input', function() {
                    if (this.value < 0) {
                        this.value = 0;
                    }
                });
            }
        });
    });
</script>
@endsection