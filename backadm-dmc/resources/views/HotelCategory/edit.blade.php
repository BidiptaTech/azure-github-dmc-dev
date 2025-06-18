@extends('layouts.layout')
@section('content')

<!-- Start of the form -->
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Edit Hotel Category
                <a href="{{ route('hotel-category.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form action="{{ route('hotel-category.update', $category->id) }}" method="POST" enctype="multipart/form-data"
                class="card-body">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label"><strong>Name</strong>
                                <span style="color: red; font-weight: bold;">*</span>
                            </label>
                            <input value="{{ $category->name }}" type="text" name="name" placeholder="Enter Name"
                                class="form-control" required>
                            @error('name')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label"><strong>Icon</strong>
                            </label>
                            <input type="file" name="icon" class="form-control">
                            <img src="{{ $category->icon }}" alt="Category Icon" style="width: 50px; height: 32px;">
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div class="form-check form-switch">
                    <label for="status" class="form-label"><strong>Status</strong></label>
                    <input type="hidden" name="status" value="0">
                    <input class="form-check-input" name="status" @if($category->status == 1)
                    checked @endif
                    type="checkbox" id="status" value="1">
                    <label class="form-check-label"></label>
                </div>

                <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End of the form -->
@endsection