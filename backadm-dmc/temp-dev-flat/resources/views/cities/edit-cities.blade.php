@extends('layouts.layout')
@section('title', 'Add Country')
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
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Add New Country
                <a href="{{ route('countries.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form action="{{ route('cities.store') }}" method="POST" class="card-body">
                @csrf

                <div class="row">
                    <!-- Select country Name -->
                    <div class="col-md-3 mb-3">
                        <label for="hotel_id" class="form-label"><strong>Country Name</strong><span class="text-danger">*</span></label>
                        <select name="name" id="name" class="form-control">
                            <option value="">Type to search country</option>
                            @foreach($countries as $country)
                                <option {{$country->id == $city->country_id ? 'selected' : ''}} value="{{$country->id}}">{{$country->name}}</option>
                            @endforeach
                        </select>
                        @error('hotel_id')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- City Name -->
                    <div class="col-md-6 mb-3">
                        <label for="city_name" class="form-label"><strong>City Time</strong><span class="text-danger">*</span></label>
                        <input value="{{$city->name}}" type="text" class="form-control" id="cityName" name="city_name" readonly required>
                        @error('city_name')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                </div>

                <div class="row mt-4 text-center">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <button type="reset" class="btn btn-secondary px-4">Reset</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('#name').select2({
            placeholder: "Search and Select a Hotel",
            allowClear: true,
            width: '100%'
        });
    });
</script>
<script>
    document.getElementById('countrySelect').addEventListener('change', function() {
        let selectedOption = this.options[this.selectedIndex];
        let countryCode = selectedOption.getAttribute('data-code');
        let currency = selectedOption.getAttribute('data-currency');

        document.getElementById('countryCode').value = countryCode || '';
        document.getElementById('currency').value = currency || '';
    });
</script>
@endsection
