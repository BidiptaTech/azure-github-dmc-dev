@extends('layouts.layout')
@section('title', 'Quotation Settings')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header">Quotation Settings</h5>

            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('quotation_settings.save') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Country</strong><span class="text-danger">*</span></label>
                            <select class="form-control @error('country') is-invalid @enderror" name="country" id="countrySelect" required>
                                <option value="">Select Country</option>
                                @foreach($countries ?? [] as $country)
                                    <option value="{{ $country->name }}" @selected(($selectedCountry ?? '') === $country->name)>{{ $country->name }}</option>
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
                        <label class="form-label"><strong>Quotation Information</strong></label>
                        <textarea class="form-control @error('quotation_information') is-invalid @enderror" id="quotation_information" name="quotation_information">{{ old('quotation_information', $quotationInformationHtml ?? '') }}</textarea>
                        @error('quotation_information')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>

                <hr class="my-4">

                <h6 class="mb-3">Saved Quotation Infos</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40%;">Country</th>
                                <th style="width: 40%;">City</th>
                                <th style="width: 20%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($allSettings ?? []) as $s)
                                <tr>
                                    <td>{{ $s->country }}</td>
                                    <td>{{ $s->city }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a class="btn btn-sm btn-primary" href="{{ route('quotation_settings.edit', ['id' => \Illuminate\Support\Facades\Crypt::encryptString((string) $s->quotation_setting_id)]) }}">Edit</a>
                                            <form action="{{ route('quotation_settings.delete', ['id' => \Illuminate\Support\Facades\Crypt::encryptString((string) $s->quotation_setting_id)]) }}" method="POST" onsubmit="return confirm('Delete this quotation info?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No saved settings yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
        const el = document.getElementById('quotation_information');
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

