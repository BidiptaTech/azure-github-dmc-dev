@extends('layouts.layout')
@section('content')
@include('hotel.tapview', ['hotel' => $hotel])
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
            Hotel Cancellation Policy
            <a href="javascript:history.back()" class="btn btn-sm btn-outline-danger">
                <i class="mdi mdi-arrow-left"></i> Back
            </a>
            </h5>
            <form action="{{ route('updatecancellation.policy') }}" method="POST" enctype="multipart/form-data" class="card-body">
                @csrf 
                <input type="text" id="hotel_id" name="hotel_id" class="form-control" value="{{ $hotel->hotel_unique_id }}" hidden>
                <div class="row">
                    <div class="mb-3 col-md-4">
                        <label for="cancellation_type" class="form-label">
                            <strong>Cancellation Type</strong>
                            <span style="color: red; font-weight: bold;">*</span>
                        </label>
                        <select name="cancellation_type" id="cancellation_type" class="form-control" required>
                            <option value="">Select an option</option>
                            <option value="0" {{ old('cancellation_type', $hotel->cancellation_type) == 0 ? 'selected' : '' }}>Free</option>
                            <option value="1" {{ old('cancellation_type', $hotel->cancellation_type) == 1 ? 'selected' : '' }}>Chargeable</option>
                        </select>
                    </div>
                    <div id="cancellation-options" style="{{ old('cancellation_type', $hotel->cancellation_type) == 1 ? '' : 'display: none;' }}">
                    <div id="cancellation-fields">
                        @foreach($cancellation_data as $index => $rule)
                            <div class="row mb-3 cancellation-rule" id="cancellation-rule-{{ $index }}">
                                <div class="col-md-4">
                                    <label class="form-label"><strong>Duration</strong></label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        name="cancellation_duration[]" 
                                        placeholder="Enter Duration" 
                                        value="{{ old('cancellation_duration.' . $index, $rule['duration'] ?? '') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label"><strong>Type</strong></label>
                                    <select class="form-select" name="type[]">
                                        <option value="">Select Type</option>
                                        <option value="flat" {{ (old('type.' . $index, $rule['type'] ?? '') == 'flat') ? 'selected' : '' }}>Flat</option>
                                        <option value="percentage" {{ (old('type.' . $index, $rule['type'] ?? '') == 'percentage') ? 'selected' : '' }}>Percentage</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label"><strong>Price</strong></label>
                                    <input 
                                        type="number" 
                                        class="form-control" 
                                        name="cancellation_price[]" 
                                        placeholder="Enter Price" 
                                        value="{{ old('cancellation_price.' . $index, $rule['price'] ?? '') }}">
                                </div>
                                <div class="col-md-2" style="margin-top: 1.5rem">
                                    <button type="button" class="btn btn-danger" onclick="removeCancellationField({{ $index }})">Delete</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                        <div class="mb-3">
                            <button type="button" id="add-cancellation-field" class="btn btn-primary">Add More</button>
                        </div>
                    </div>
                        <!-- File Upload -->
                        <div class="mb-3 col-md-4">
                        <label for="file" class="form-label"></label>
                        <input type="file" name="file" class="form-control" accept="application/pdf">
                        @error('file')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror

                        @if (!empty($hotel->cancellation_pdf))
                            <div class="mt-2">
                                <a href="{{ $hotel->cancellation_pdf }}" target="_blank">View Existing File</a>
                            </div>
                        @endif
                        
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="policy" class="form-label"><strong>Cancellation Policy</strong><span style="color: red;">*</span></label>
                        <textarea id="summernote" name="policy">{{ old('policy', $hotel->policy) }}</textarea>
                        @error('policy')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" class="btn btn-primary px-4">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.js"></script>
<script>
$(document).ready(function() {
  $('#summernote').summernote();
});
</script>
<!-- Cancellation type -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cancellationType = document.getElementById('cancellation_type');
        const cancellationOptions = document.getElementById('cancellation-options');
        const fieldsContainer = document.getElementById('cancellation-fields');
        let index = fieldsContainer.querySelectorAll('.cancellation-rule').length;
        cancellationType.addEventListener('change', function () {
            cancellationOptions.style.display = this.value == '1' ? '' : 'none';
        });
        document.getElementById('add-cancellation-field').addEventListener('click', function () {
            const fieldHTML = `
                <div class="row mb-3 cancellation-rule" id="cancellation-rule-${index}">
                    <div class="col-md-4">
                        <label class="form-label"><strong>Duration</strong></label>
                        <input type="text" class="form-control" name="cancellation_duration[]" placeholder="Enter Duration">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><strong>Type</strong></label>
                        <select class="form-select" name="type[]">
                            <option value="">Select Type</option>
                            <option value="flat">Flat</option>
                            <option value="percentage">Percentage</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><strong>Price</strong></label>
                        <input type="number" class="form-control" name="cancellation_price[]" placeholder="Enter Price">
                    </div>
                    <div class="col-md-2" style="margin-top: 1.5rem">
                        <button type="button" class="btn btn-danger" onclick="removeCancellationField(${index})">Delete</button>
                    </div>
                </div>`;
            fieldsContainer.insertAdjacentHTML('beforeend', fieldHTML);
            index++; // Increment the index for the next field
        });
    });

    // Function to remove a cancellation field dynamically
    function removeCancellationField(index) {
        const field = document.getElementById('cancellation-rule-' + index);
        if (field) {
            field.remove(); // Remove the specific cancellation field
        }
    }
</script>
@endsection
