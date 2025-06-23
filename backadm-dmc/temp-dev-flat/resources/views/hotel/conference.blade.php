@extends('layouts.layout')

@section('content')
@include('hotel.tapview', ['hotel' => $hotel])
<link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Conference Room
                <a href="javascript:history.back()" class="btn btn-sm btn-outline-danger">
                <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form action="{{ route('update.conference') }}" method="POST" enctype="multipart/form-data"  class="card-body">
                @csrf 
                <input type="hidden" id="hotel_id" name="hotel_id" class="form-control" value="{{ $hotel->hotel_unique_id }}" >
                <div class="row">
                <div class="mb-3 col-md-4">
                    <label for="conference" class="form-label">
                        <strong>Conference</strong>
                        <span style="color: red; font-weight: bold;">*</span>
                    </label>
                    <select id="conference" name="conference" class="form-control">
                        <option value="" disabled selected>Select an option</option>
                        <option value="true" {{ $conference === true || $conference === 'true' ? 'selected' : '' }}>Yes</option>
                        <option value="false" {{ $conference === false || $conference === 'false' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" class="btn btn-primary px-4">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

