@extends('layouts.layout')
@section('content')

<!-- Start of the form -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Add New Hotel Category
                <a href="{{ route('hotel-category.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form action="{{ route('hotel-category.store') }}" method="POST" enctype="multipart/form-data" class="card-body">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label"><strong>Name</strong>
                            <span style="color: red; font-weight: bold;">*</span>
                        </label>
                        <input type="text" id="name" name="name" placeholder="Enter Name" class="form-control" required>
                        @error('name')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="icon" class="form-label"><strong>Icon</strong>
                            <span style="color: red; font-weight: bold;">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i id="selectedIcon" class="bi bi-building"></i></span>
                            <select class="form-select select2" name="icon" id="icon" required>
                                <option value="">Search for an icon...</option>
                                <!-- Hotel & Accommodation -->
                                <option value="bi-building">Hotel Building</option>
                                <option value="bi-house-door">House Door</option>
                                <option value="bi-house-fill">House</option>
                                <option value="bi-building-fill">Building</option>
                                <option value="bi-door-open">Door Open</option>
                                <option value="bi-door-closed">Door Closed</option>
                                <option value="bi-key">Key</option>
                                <option value="bi-key-fill">Key Fill</option>
                                <option value="bi-bed">Bed</option>
                                <option value="bi-bed-fill">Bed Fill</option>
                                <option value="bi-pin-map">Location</option>
                                <option value="bi-pin-map-fill">Location Fill</option>
                                <option value="bi-geo-alt">Location Alt</option>
                                <option value="bi-geo-fill">Location Fill</option>
                                <option value="bi-star">Star</option>
                                <option value="bi-star-fill">Star Fill</option>
                                <option value="bi-stars">Stars</option>
                                <option value="bi-award">Award</option>
                                <option value="bi-award-fill">Award Fill</option>
                                <option value="bi-trophy">Trophy</option>
                                <option value="bi-trophy-fill">Trophy Fill</option>
                                <option value="bi-shield">Shield</option>
                                <option value="bi-shield-fill">Shield Fill</option>
                                <option value="bi-check-circle">Check Circle</option>
                                <option value="bi-check-circle-fill">Check Circle Fill</option>
                                <option value="bi-x-circle">X Circle</option>
                                <option value="bi-x-circle-fill">X Circle Fill</option>
                                <option value="bi-info-circle">Info Circle</option>
                                <option value="bi-info-circle-fill">Info Circle Fill</option>
                                <option value="bi-exclamation-circle">Exclamation Circle</option>
                                <option value="bi-exclamation-circle-fill">Exclamation Circle Fill</option>
                                <option value="bi-question-circle">Question Circle</option>
                                <option value="bi-question-circle-fill">Question Circle Fill</option>
                                <option value="bi-arrow-down-circle">Arrow Down Circle</option>
                                <option value="bi-arrow-down-circle-fill">Arrow Down Circle Fill</option>
                                <option value="bi-arrow-up-circle">Arrow Up Circle</option>
                                <option value="bi-arrow-up-circle-fill">Arrow Up Circle Fill</option>
                                <option value="bi-arrow-left-circle">Arrow Left Circle</option>
                                <option value="bi-arrow-left-circle-fill">Arrow Left Circle Fill</option>
                                <option value="bi-arrow-right-circle">Arrow Right Circle</option>
                                <option value="bi-arrow-right-circle-fill">Arrow Right Circle Fill</option>
                                <option value="bi-heart">Heart</option>
                                <option value="bi-heart-fill">Heart Fill</option>
                                <option value="bi-bookmark">Bookmark</option>
                                <option value="bi-bookmark-fill">Bookmark Fill</option>
                                <option value="bi-camera">Camera</option>
                                <option value="bi-camera-fill">Camera Fill</option>
                                <option value="bi-cart">Cart</option>
                                <option value="bi-cart-fill">Cart Fill</option>
                                <option value="bi-chat">Chat</option>
                                <option value="bi-chat-fill">Chat Fill</option>
                                <option value="bi-clock">Clock</option>
                                <option value="bi-clock-fill">Clock Fill</option>
                                <option value="bi-cloud">Cloud</option>
                                <option value="bi-cloud-fill">Cloud Fill</option>
                                <option value="bi-code">Code</option>
                                <option value="bi-code-slash">Code Slash</option>
                                <option value="bi-credit-card">Credit Card</option>
                                <option value="bi-credit-card-fill">Credit Card Fill</option>
                                <option value="bi-envelope">Envelope</option>
                                <option value="bi-envelope-fill">Envelope Fill</option>
                                <option value="bi-eye">Eye</option>
                                <option value="bi-eye-fill">Eye Fill</option>
                                <option value="bi-file">File</option>
                                <option value="bi-file-fill">File Fill</option>
                                <option value="bi-flag">Flag</option>
                                <option value="bi-flag-fill">Flag Fill</option>
                                <option value="bi-gear">Gear</option>
                                <option value="bi-gear-fill">Gear Fill</option>
                                <option value="bi-gift">Gift</option>
                                <option value="bi-gift-fill">Gift Fill</option>
                                <option value="bi-globe">Globe</option>
                                <option value="bi-globe2">Globe 2</option>
                                <option value="bi-graph-up">Graph Up</option>
                                <option value="bi-graph-down">Graph Down</option>
                                <option value="bi-image">Image</option>
                                <option value="bi-image-fill">Image Fill</option>
                                <option value="bi-link">Link</option>
                                <option value="bi-link-45deg">Link 45deg</option>
                                <option value="bi-list">List</option>
                                <option value="bi-list-ul">List UL</option>
                                <option value="bi-list-ol">List OL</option>
                                <option value="bi-lock">Lock</option>
                                <option value="bi-lock-fill">Lock Fill</option>
                                <option value="bi-map">Map</option>
                                <option value="bi-map-fill">Map Fill</option>
                                <option value="bi-music-note">Music Note</option>
                                <option value="bi-music-note-beamed">Music Note Beamed</option>
                                <option value="bi-person">Person</option>
                                <option value="bi-person-fill">Person Fill</option>
                                <option value="bi-phone">Phone</option>
                                <option value="bi-phone-fill">Phone Fill</option>
                                <option value="bi-pin">Pin</option>
                                <option value="bi-pin-fill">Pin Fill</option>
                                <option value="bi-play">Play</option>
                                <option value="bi-play-fill">Play Fill</option>
                                <option value="bi-printer">Printer</option>
                                <option value="bi-printer-fill">Printer Fill</option>
                                <option value="bi-search">Search</option>
                                <option value="bi-shop">Shop</option>
                                <option value="bi-shop-fill">Shop Fill</option>
                                <option value="bi-tag">Tag</option>
                                <option value="bi-tag-fill">Tag Fill</option>
                                <option value="bi-trash">Trash</option>
                                <option value="bi-trash-fill">Trash Fill</option>
                                <option value="bi-wifi">Wifi</option>
                                <option value="bi-wifi-1">Wifi 1</option>
                                <option value="bi-wifi-2">Wifi 2</option>
                                <option value="bi-wifi-off">Wifi Off</option>
                            </select>
                        </div>
                        @error('icon')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Separate Row for Status -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-check form-switch">
                            <input type="hidden" name="status" value="0">
                            <input class="form-check-input" name="status" type="checkbox" id="status"
                                value="1">
                            <label for="status" class="form-check-label"><strong>Status</strong></label>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="row mt-4">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End of the form -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2
    $('#icon').select2({
        dropdownParent: $('#icon').parent(),
        templateResult: formatIcon,
        templateSelection: formatIcon,
        escapeMarkup: function(m) { return m; }
    });
    
    // Format the icon options
    function formatIcon(icon) {
        if (!icon.id) return icon.text;
        return $('<span><i class="bi ' + icon.id + ' me-2"></i>' + icon.text + '</span>');
    }
    
    // Update the preview icon when selection changes
    $('#icon').on('change', function() {
        const selectedIcon = document.getElementById('selectedIcon');
        selectedIcon.className = 'bi ' + this.value;
    });

    // Add validation for required field
    $('#icon').on('select2:select', function() {
        if ($(this).val()) {
            $(this).removeClass('is-invalid');
        } else {
            $(this).addClass('is-invalid');
        }
    });
});
</script>
@endsection