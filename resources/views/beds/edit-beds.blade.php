@extends('layouts.layout')
@section('content')
<!-- Start of the form -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Edit Bed Type
                <a href="javascript:history.back()" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form action="{{ route('beds.update') }}" method="post" class="card-body">
                @csrf
                <input value="{{ $bed->bedId }}" type="text" name="bed_id" hidden>
                <fieldset class="p-3 border rounded shadow-sm">
                        <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="hotel_id" class="form-label"><strong>Hotel</strong><span style="color: red; font-weight: bold;">*</span></label>
                            <select name="hotel_id" id="hotel_id" class="form-control" required>
                                <option value="">Select a Hotel</option>
                                @foreach($hotel as $h)
                                <option value="{{ $h->hotel_unique_id }}" {{ $bed->hotel_id == $h->hotel_unique_id ? 'selected' : '' }}>
                                    {{ $h->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label"><strong>Name</strong>
                                <span style="color: red; font-weight: bold;">*</span>
                            </label>
                            <input value="{{ $bed->name }}" type="text" id="name" name="bed_type" placeholder="Enter Bed Type" class="form-control" required>
                            @error('bed_type')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Number of Single Beds -->
                        <div class="col-md-3 mb-3">
                            <label for="single_bed" class="form-label"><strong>No. of Single Beds</strong></label>
                            <select id="single_bed" name="single_bed" class="form-control" required>
                                <option value="0" {{ $bed->no_of_single_bed == 0 ? 'selected' : '' }}>0</option>
                                <option value="1" {{ $bed->no_of_single_bed == 1 ? 'selected' : '' }}>1</option>
                                <option value="2" {{ $bed->no_of_single_bed == 2 ? 'selected' : '' }}>2</option>
                                <option value="3" {{ $bed->no_of_single_bed == 3 ? 'selected' : '' }}>3</option>
                                <option value="4" {{ $bed->no_of_single_bed == 4 ? 'selected' : '' }}>4</option>
                            </select>
                            @error('single_bed')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Number of King Beds -->
                        <div class="col-md-3 mb-3">
                            <label for="king_beds" class="form-label"><strong>No. of King Beds</strong></label>
                            <select id="king_beds" name="king_beds" class="form-control" required>
                                <option value="0" {{ $bed->no_of_king_bed == 0 ? 'selected' : '' }}>0</option>
                                <option value="1" {{ $bed->no_of_king_bed == 1 ? 'selected' : '' }}>1</option>
                                <option value="2" {{ $bed->no_of_king_bed == 2 ? 'selected' : '' }}>2</option>
                                <option value="3" {{ $bed->no_of_king_bed == 3 ? 'selected' : '' }}>3</option>
                                <option value="4" {{ $bed->no_of_king_bed == 4 ? 'selected' : '' }}>4</option>
                            </select>
                            @error('king_beds')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Number of Queen Beds -->
                        <div class="col-md-2 mb-3">
                            <label for="queen_beds" class="form-label"><strong>No. of Queen Beds</strong></label>
                            <select id="queen_beds" name="queen_beds" class="form-control" required>
                                <option value="0" {{ $bed->no_of_queen_bed == 0 ? 'selected' : '' }}>0</option>
                                <option value="1" {{ $bed->no_of_queen_bed == 1 ? 'selected' : '' }}>1</option>
                                <option value="2" {{ $bed->no_of_queen_bed == 2 ? 'selected' : '' }}>2</option>
                                <option value="3" {{ $bed->no_of_queen_bed == 3 ? 'selected' : '' }}>3</option>
                                <option value="4" {{ $bed->no_of_queen_bed == 4 ? 'selected' : '' }}>4</option>
                            </select>
                            @error('queen_beds')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Number of Twin Beds -->
                        <div class="col-md-2 mb-3">
                            <label for="twin_beds" class="form-label"><strong>No. of Twin Beds</strong></label>
                            <select id="twin_beds" name="twin_beds" class="form-control" required>
                                <option value="0" {{ $bed->no_of_twin_bed == 0 ? 'selected' : '' }}>0</option>
                                <option value="1" {{ $bed->no_of_twin_bed == 1 ? 'selected' : '' }}>1</option>
                                <option value="2" {{ $bed->no_of_twin_bed == 2 ? 'selected' : '' }}>2</option>
                                <option value="3" {{ $bed->no_of_twin_bed == 3 ? 'selected' : '' }}>3</option>
                                <option value="4" {{ $bed->no_of_twin_bed == 4 ? 'selected' : '' }}>4</option>
                            </select>
                            @error('twin_beds')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Number of Bunk Beds -->
                        <div class="col-md-2 mb-3">
                            <label for="bunk_beds" class="form-label"><strong>No. of Bunk Beds</strong></label>
                            <select id="bunk_beds" name="bunk_beds" class="form-control" required>
                                <option value="0" {{ $bed->no_of_bunk_bed == 0 ? 'selected' : '' }}>0</option>
                                <option value="1" {{ $bed->no_of_bunk_bed == 1 ? 'selected' : '' }}>1</option>
                                <option value="2" {{ $bed->no_of_bunk_bed == 2 ? 'selected' : '' }}>2</option>
                                <option value="3" {{ $bed->no_of_bunk_bed == 3 ? 'selected' : '' }}>3</option>
                                <option value="4" {{ $bed->no_of_bunk_bed == 4 ? 'selected' : '' }}>4</option>
                            </select>
                            @error('bunk_beds')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="form-check form-switch" style = "padding-left: 3.450em;">
                        <label for="bed_status" class="form-label"><strong>Status</strong><span style="color: red; font-weight: bold;">*</span></label>
                        <input type="hidden" name="bed_status" value="0">
                        <input {{$bed->is_active == 1 ? 'checked' : ''}} class="form-check-input" name="bed_status" type="checkbox" id="bed_status" value="1">
                        <label class="form-check-label"></label>
                        @error('bed_status')
                    <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                    </div>
                    <!-- Submit Button -->
                    <div class="row mt-4">
                        <div class="col-md-12 text-center">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</div>
<!-- End of the form -->
@endsection


