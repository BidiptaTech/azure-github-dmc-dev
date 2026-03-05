@extends('layouts.layout')
@section('title', 'Edit Miscellaneous Item')
@php use Illuminate\Support\Facades\Crypt; @endphp

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Miscellaneous Item</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('miscellaneous.update', Crypt::encrypt($item->mis_id)) }}" method="POST" enctype="multipart/form-data">
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
                    <input type="file" 
                           class="form-control @error('image') is-invalid @enderror" 
                           id="image" 
                           name="image" 
                           accept="image/*">
                    <input type="hidden" name="remove_image" id="remove_image" value="0">
                    <div id="image-preview" class="mt-2 {{ $item->image ? '' : 'd-none' }}">
                        <div class="d-flex align-items-start gap-2">
                            <div class="position-relative">
                                <img id="image-preview-img" 
                                     src="{{ $item->image ? ((str_starts_with($item->image, 'http') || str_starts_with($item->image, '/')) ? $item->image : asset('storage/' . $item->image)) : '' }}" 
                                     data-default-src="{{ $item->image ? ((str_starts_with($item->image, 'http') || str_starts_with($item->image, '/')) ? $item->image : asset('storage/' . $item->image)) : '' }}"
                                     alt="{{ $item->item_name }}" 
                                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 6px; border: 1px solid #dee2e6;">
                                <button type="button" id="image-remove" class="btn btn-sm btn-danger position-absolute rounded-circle p-0 d-flex align-items-center justify-content-center" 
                                        style="width: 24px; height: 24px; top: -8px; right: -8px; z-index: 10; cursor: pointer; border: 2px solid #fff;" title="Remove image">
                                    <i class="ri-close-line" style="font-size: 12px;"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-1">Supported formats: JPEG, PNG, JPG, GIF, WEBP (Max: 2MB)</small>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    var fileInput = document.getElementById('image');
    var removeImageInput = document.getElementById('remove_image');
    var preview = document.getElementById('image-preview');
    var previewImg = document.getElementById('image-preview-img');
    var removeBtn = document.getElementById('image-remove');
    var defaultSrc = previewImg.getAttribute('data-default-src') || '';

    fileInput.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            removeImageInput.value = '0';
            var reader = new FileReader();
            reader.onload = function(ev) {
                previewImg.src = ev.target.result;
                preview.classList.remove('d-none');
            };
            reader.readAsDataURL(this.files[0]);
        } else if (!defaultSrc || removeImageInput.value === '1') {
            preview.classList.add('d-none');
        }
    });

    if (removeBtn) {
        removeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            fileInput.value = '';
            removeImageInput.value = '1';
            preview.classList.add('d-none');
            previewImg.src = '';
        });
    }
});
</script>
@endsection

