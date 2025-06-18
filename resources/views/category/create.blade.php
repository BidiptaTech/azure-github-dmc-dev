@extends('layouts.layout')
@section('content')

<!-- Start of the form -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Add New Category
                <a href="{{ route('category.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form action="{{ route('category.store') }}" method="POST" enctype="multipart/form-data" class="card-body">
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
                        <input type="file" name="icon" class="form-control" required>
                        @error('icon')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Separate Row for Status -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-check form-switch">
                            <input type="hidden" name="category_status" value="0">
                            <input class="form-check-input" name="category_status" type="checkbox" id="category_status"
                                value="1">
                            <label for="category_status" class="form-check-label"><strong>Status</strong></label>
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
@endsection