@extends('layouts.layout')
@section('title', 'Create Miscellaneous Item')

@push('css')
<style>
    /* Match Select2 width/height to Bootstrap form-control / form-select */
    .misc-form-field .select2-container {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    .misc-form-field .select2-container .select2-selection--single {
        height: 38px !important;
        min-height: 38px !important;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        background-color: #fff;
        display: flex;
        align-items: center;
        padding: 0;
    }
    .misc-form-field .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 0.75rem;
        padding-right: 2rem;
        color: #697a8d;
        width: 100%;
    }
    .misc-form-field .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 0.5rem;
    }
    .misc-form-field .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #a1acb8;
    }
    .misc-form-field .select2-container--default.select2-container--focus .select2-selection--single,
    .misc-form-field .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }
    .misc-form-field .select2-container--default .select2-selection--single .select2-selection__clear {
        margin-right: 1.5rem;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Create Miscellaneous Item</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('miscellaneous.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="item_name" class="form-label">Item Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('item_name') is-invalid @enderror" 
                                   id="item_name" 
                                   name="item_name" 
                                   value="{{ old('item_name') }}" 
                                   placeholder="e.g., Airport Transfer, Travel Insurance"
                                   required>
                            @error('item_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status" 
                                    required>
                                <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3 misc-form-field">
                            <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                            <select class="form-select misc-search-select @error('country') is-invalid @enderror"
                                    id="country"
                                    name="country"
                                    required
                                    data-placeholder="Search country...">
                                <option value=""></option>
                                @foreach(($countryNames ?? []) as $cName)
                                    <option value="{{ $cName }}" {{ old('country') === $cName ? 'selected' : '' }}>{{ $cName }}</option>
                                @endforeach
                            </select>
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3 misc-form-field">
                            <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                            <select class="form-select misc-search-select @error('city') is-invalid @enderror"
                                    id="city"
                                    name="city"
                                    required
                                    data-placeholder="Search city...">
                                <option value=""></option>
                            </select>
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Pro Form shows this item’s DMC price when the tour city matches.</small>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" 
                              name="description" 
                              rows="3"
                              placeholder="Brief description of the miscellaneous item">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="image" class="form-label">Image</label>
                    <input type="file" 
                           class="form-control @error('image') is-invalid @enderror" 
                           id="image" 
                           name="image" 
                           accept="image/*">
                    <div id="image-preview" class="mt-2" style="display: none;">
                        <div class="position-relative d-inline-block">
                            <img id="image-preview-img" src="" alt="Preview" 
                                 style="width: 80px; height: 80px; object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6;">
                        </div>
                    </div>
                    <small class="text-muted d-block mt-1">Supported formats: JPEG, PNG, JPG, GIF, WEBP (Max: 2MB)</small>
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Create Item
                    </button>
                    <a href="{{ route('miscellaneous.index') }}" class="btn btn-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Footer reloads jQuery after head Select2, so load Select2 again here --}}
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
const MISC_CITIES_BY_COUNTRY = @json($citiesByCountry ?? []);
const MISC_OLD_CITY = @json(old('city', ''));

function initMiscSearchSelect($el, placeholder) {
    if (!$el.length || typeof jQuery === 'undefined' || typeof jQuery.fn.select2 !== 'function') {
        console.warn('Select2 not available for', $el.attr('id'));
        return;
    }
    if ($el.hasClass('select2-hidden-accessible')) {
        $el.select2('destroy');
    }
    $el.select2({
        width: '100%',
        placeholder: placeholder || ($el.data('placeholder') || 'Search...'),
        allowClear: true,
        dropdownParent: $(document.body)
    });
    $el.next('.select2-container').css({ width: '100%', display: 'block' });
}

function populateMiscCities(country, selectedCity) {
    const $city = $('#city');
    if (!$city.length) return;
    const cities = MISC_CITIES_BY_COUNTRY[country] || [];
    let html = '<option value=""></option>';
    cities.forEach(function (city) {
        const name = city.name || city;
        const sel = name === selectedCity ? ' selected' : '';
        html += '<option value="' + String(name).replace(/"/g, '&quot;') + '"' + sel + '>' + $('<div>').text(name).html() + '</option>';
    });
    if ($city.hasClass('select2-hidden-accessible')) {
        $city.select2('destroy');
    }
    $city.html(html);
    initMiscSearchSelect($city, 'Search city...');
    if (selectedCity) {
        $city.val(selectedCity).trigger('change');
    } else {
        $city.val(null).trigger('change');
    }
}

$(function () {
    // Empty first option helps Select2 placeholder
    if (!$('#country option[value=""]').length) {
        $('#country').prepend('<option value=""></option>');
    }

    initMiscSearchSelect($('#country'), 'Search country...');
    initMiscSearchSelect($('#city'), 'Search city...');

    const country = $('#country').val();
    if (country) {
        populateMiscCities(country, MISC_OLD_CITY);
    }

    $('#country').on('change', function () {
        populateMiscCities($(this).val() || '', '');
    });

    $('#image').on('change', function () {
        var preview = document.getElementById('image-preview');
        var previewImg = document.getElementById('image-preview-img');
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(this.files[0]);
        } else {
            preview.style.display = 'none';
        }
    });
});
</script>
@endpush
