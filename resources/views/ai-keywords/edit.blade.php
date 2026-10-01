@extends('layouts.layout')

@section('title', 'Edit AI Keyword')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">AI Configuration / AI Keywords /</span> Edit
    </h4>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit AI Keyword</h5>
            <a href="{{ route('ai-key-words.index') }}" class="btn btn-outline-secondary btn-sm">
                Back
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('ai-key-words.update', $aiKeyword) }}" method="POST" class="row g-3">
                @csrf
                @method('PUT')

                <div class="col-md-4">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select" required>
                        <option value="">Select category</option>
                        @foreach($categories as $value => $label)
                            <option value="{{ $value }}" {{ old('category', $aiKeyword->category) === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="1" {{ old('status', $aiKeyword->status) == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('status', $aiKeyword->status) == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Keywords</label>
                    <textarea name="keywords" class="form-control" rows="8" required placeholder="Enter one keyword per line (or separate with commas)">{{ old('keywords', $keywordsText) }}</textarea>
                    <div class="form-text">Multiple keywords are saved together in one line, separated by commas.</div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
