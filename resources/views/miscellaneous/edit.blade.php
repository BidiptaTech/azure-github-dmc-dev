@extends('layouts.layout')
@section('title', 'Edit Miscellaneous Item')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Miscellaneous Item</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('miscellaneous.update', $item->mis_id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="item_name" class="form-label">Item Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('item_name') is-invalid @enderror" 
                                   id="item_name" 
                                   name="item_name" 
                                   value="{{ old('item_name', $item->item_name) }}" 
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
                                <option value="1" {{ old('status', $item->status) == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status', $item->status) == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" 
                              name="description" 
                              rows="3"
                              placeholder="Brief description of the miscellaneous item">{{ old('description', $item->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="image" class="form-label">Image</label>
                    @if($item->image)
                        <div class="mb-2">
                            <img src="{{ (str_starts_with($item->image, 'http') || str_starts_with($item->image, '/')) ? $item->image : asset('storage/' . $item->image) }}" 
                                 alt="{{ $item->item_name }}" 
                                 style="max-width: 200px; max-height: 200px; border-radius: 4px; border: 1px solid #ddd;">
                            <p class="text-muted small mt-1">Current image (upload new to replace)</p>
                        </div>
                    @endif
                    <input type="file" 
                           class="form-control @error('image') is-invalid @enderror" 
                           id="image" 
                           name="image" 
                           accept="image/*">
                    <small class="text-muted">Supported formats: JPEG, PNG, JPG, GIF, WEBP (Max: 2MB)</small>
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Update Item
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
