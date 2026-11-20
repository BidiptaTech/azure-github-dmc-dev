@extends('layouts.layout')

@section('title', 'Settings')
@section('css')

@endsection 
@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Update Settings
            </h5>
            <form action="{{ route('store-setting') }}" method="POST" enctype="multipart/form-data" class="card-body">
                @csrf
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <label for="input35" class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" placeholder="Enter Your Name" value="{{ $name }}" required>
                        @error('name') 
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-sm-4">
                        <label for="currency" class="form-label">Currency</label>
                        <select name="currency" class="form-control" required>
                            <option value="" disabled {{ empty($currentCurrency) ? 'selected' : '' }}>Select Currency</option>
                            @foreach(\App\Models\Setting::getCurrencyCodes() as $currency)
                                <option value="{{ $currency }}" {{ isset($currentCurrency) && $currentCurrency === $currency ? 'selected' : '' }}>
                                    {{ $currency }}
                                </option>
                            @endforeach
                        </select>
                        @error('currency') 
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-sm-4">
                        <label for="input35" class="form-label">Default Commission</label>
                        <input type="text" class="form-control" name="commission" placeholder="Enter Default Commission" value="{{ $Commission }}" required>
                        @error('commission') 
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-4">
                        <label for="inputLogo" class="form-label">Logo</label>
                        <input type="file" class="form-control" name="logo" accept="image/*" @if(empty($existingLogo)) required @endif>
                        @if(!empty($existingLogo))
                            <img src="{{ $existingLogo }}" alt="Current Logo" style="width: 55px; height: 55px; object-fit: cover;" class="mt-2">
                        @endif
                        @error('logo')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-sm-4">
                        <label for="inputFavicon" class="form-label">Favicon</label>
                        <input type="file" class="form-control" name="favicon" accept="image/*" @if(empty($existingFavicon)) required @endif>
                        @if(!empty($existingFavicon))
                            <img src="{{ $existingFavicon }}" alt="Current Favicon" style="width: 55px; height: 55px; object-fit: cover;" class="mt-2">
                        @endif
                        @error('favicon')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-4">
                        <label for="guide_default_pic" class="form-label">Guide Default Pic</label>
                        <input type="file" class="form-control" id="guide_default_pic" name="guide_default_pic" accept="image/*" @if(empty($existingGuideDefaultPic)) required @endif>
                        @if(!empty($existingGuideDefaultPic))
                            <img src="{{ $existingGuideDefaultPic }}" alt="Guide Default Pic" style="width: 55px; height: 55px; object-fit: cover;" class="mt-2">
                        @endif
                        @error('guide_default_pic')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-sm-4">
                        <label for="vehicle_default_pic" class="form-label">Vehicle Default Pic</label>
                        <input type="file" class="form-control" id="vehicle_default_pic" name="vehicle_default_pic" accept="image/*" @if(empty($existingVehicleDefaultPic)) required @endif>
                        @if(!empty($existingVehicleDefaultPic))
                            <img src="{{ $existingVehicleDefaultPic }}" alt="Vehicle Default Pic" style="width: 55px; height: 55px; object-fit: cover;" class="mt-2">
                        @endif
                        @error('vehicle_default_pic')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-sm-4">
                        <label for="driver_default_pic" class="form-label">Driver Default Pic</label>
                        <input type="file" class="form-control" id="driver_default_pic" name="driver_default_pic" accept="image/*" @if(empty($existingDriverDefaultPic)) required @endif>
                        @if(!empty($existingDriverDefaultPic))
                            <img src="{{ $existingDriverDefaultPic }}" alt="Driver Default Pic" style="width: 55px; height: 55px; object-fit: cover;" class="mt-2">
                        @endif
                        @error('driver_default_pic')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-4">
                        <label for="inputFileStorage" class="form-label">File Storage</label>
                        <select id="inputFileStorage" name="file_storage" class="form-select">
                            <option value="" disabled {{ old('file_storage', $file ?? '') == '' ? 'selected' : '' }}>Select an option</option>
                            <!-- <option value="local" {{ old('file_storage', $file ?? '') == 'local' ? 'selected' : '' }}>Local Storage</option> -->
                            <!-- <option value="s3" {{ old('file_storage', $file ?? '') == 's3' ? 'selected' : '' }}>Amazon S3</option> -->
                            <option value="azure" {{ old('file_storage', $file ?? '') == 'azure' ? 'selected' : '' }}>Azure Blob Storage</option>
                        </select>
                        @error('file_storage')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-sm-4">
                        <label for="input35" class="form-label">Tax Percentage</label>
                        <input type="number" class="form-control" name="tax_percentage" placeholder="Enter Tax Percentage" value="{{ $tax_percentage }}" required>
                        @error('tax') 
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-sm-4">
                        <label for="conversion" class="form-label">Conversion</label>
                        <input type="number" step="any" class="form-control" id="conversion" name="conversion" placeholder="Enter Conversion" value="{{ old('conversion', $conversion ?? '') }}">
                        @error('conversion')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                

                <div class="row mb-3">
                    <div class="col-sm-4">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

