@extends('layouts.layout')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Edit Category
                <a href="{{ route('facility.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form action="{{ route('facility.update', $facility->id) }}" method="POST" enctype="multipart/form-data" class="card-body">
                @csrf 
                @method('PUT')

                <div class="row">
                <div class="col-md-6">
                <div class="mb-3">
                    <label for="name" class="form-label"><strong>Name</strong>
                        <span style="color: red; font-weight: bold;">*</span>
                    </label>
                    <input value="{{ old('name', $facility->name) }}" type="text" id="name" name="name" placeholder="Enter Facility Name" class="form-control" required>
                    @error('name')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                    <label for="icon" class="form-label"><strong>Icon</strong>
                    
                    </label>
                    <input type="file" name="icon" class="form-control">
                   
                    @if ($facility->icon)
                        <div class="mt-2">
                            <img src="{{ $facility->icon }}" alt="Facility Icon" style="width: 50px; height: 32px;">
                        </div>
                    @endif
                </div>
                </div>
                <div class="col-md-6">
                <div class="mb-3">
                    <label for="chargeable" class="form-label"><strong>Chargeable</strong>
                        <span style="color: red; font-weight: bold;">*</span>
                    </label>
                    <select name="chargeable" class="form-control" id="chargeable" required onchange="toggleCommentField()">
                        <option value="">Select One</option>
                        <option value="1" {{ old('is_chargeable', $facility->is_chargeable ?? '') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('is_chargeable', $facility->is_chargeable ?? '') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3" id="comment_section" style="display: {{ old('chargeable', $facility->chargeable) == '1' ? 'block' : 'none' }};">
                        <label for="comment" class="form-label"><strong>Comment</strong></label>
                        <input type="text" name="comment" id="comment" placeholder="Enter Comment Name" class="form-control" value="{{ old('comment', $facility->chargable_comment) }}">
                    </div>
                </div>

           
                <div class="col-md-6">
                <div class="mb-3">
                    <label for="category_type" class="form-label"><strong>Category</strong>
                        <span style="color: red; font-weight: bold;">*</span>
                    </label>
                    <select id="category_type" name="category_type" class="form-control" required>
                        <option value="">Select Category Type</option>
                        @forelse ($categories as $category)
                            <option value="{{ $category->category_id }}" {{ old('category_id', $facility->category_id) == $category->category_id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @empty
                            <option>No categories available</option>
                        @endforelse
                    </select>
                    @error('name')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
                </div>

                <div class="form-check form-switch">
                    <label for="facility_status" class="form-label"><strong>Status</strong>
                        
                    </label>
                    <input type="hidden" name="facility_status" value="0">
                    <input class="form-check-input" name="facility_status" 
                        @if($category->status == 1) checked @endif 
                        type="checkbox" id="facility_status" value="1">
                    <label class="form-check-label"></label>
                    
                </div>

                <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        function toggleCommentField() {
            var chargeable = document.getElementById('chargeable').value;
            var commentSection = document.getElementById('comment_section');
            if (chargeable == '1') {
                commentSection.style.display = 'block';
            } else {
                commentSection.style.display = 'none';
            }
        }
        window.onload = function() {
            toggleCommentField();
        }
    </script>
@endsection
