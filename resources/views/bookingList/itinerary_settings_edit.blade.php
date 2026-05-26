@extends('layouts.layout')
@section('title', 'Edit Itinerary Settings')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header">Edit Itinerary Settings</h5>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('itinerary_settings.update_route', ['id' => $encryptedId]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Country</strong><span class="text-danger">*</span></label>
                            <select class="form-control @error('country') is-invalid @enderror" name="country" id="countrySelect" required>
                                <option value="">Select Country</option>
                                @foreach($countries ?? [] as $country)
                                    <option value="{{ $country->name }}" @selected(old('country', $selectedCountry ?? '') === $country->name)>{{ $country->name }}</option>
                                @endforeach
                            </select>
                            @error('country')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>City</strong><span class="text-danger">*</span></label>
                            <select class="form-control @error('city') is-invalid @enderror" name="city" id="citySelect" required>
                                <option value="">Select City</option>
                            </select>
                            @error('city')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Itinerary Information</strong></label>
                        <textarea class="form-control @error('itinerary_information') is-invalid @enderror" id="itinerary_information" name="itinerary_information">{{ old('itinerary_information', $itineraryInformationHtml ?? '') }}</textarea>
                        @error('itinerary_information')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('itinerary_settings.pdf') }}" class="btn btn-secondary">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const citiesByCountry = @json($citiesByCountry ?? []);
        const selectedCity = @json(old('city', $selectedCity ?? ''));
        const countrySelect = document.getElementById('countrySelect');
        const citySelect = document.getElementById('citySelect');

        function setCityOptions(countryName) {
            const cities = citiesByCountry[countryName] || [];
            citySelect.innerHTML = '<option value="">Select City</option>';
            cities.forEach(function (name) {
                const opt = document.createElement('option');
                opt.value = name;
                opt.textContent = name;
                if (selectedCity && selectedCity === name) opt.selected = true;
                citySelect.appendChild(opt);
            });
        }

        countrySelect.addEventListener('change', function () {
            setCityOptions(this.value);
        });

        setCityOptions(countrySelect.value);
    })();
</script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>
<script>
    (function () {
        const el = document.getElementById('itinerary_information');
        if (!el || !window.jQuery || !jQuery.fn || !jQuery.fn.summernote) return;
        jQuery(el).summernote({
            height: 320,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link']],
                ['view', ['codeview']]
            ]
        });
    })();
</script>
@endsection

