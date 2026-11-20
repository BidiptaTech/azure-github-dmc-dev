@extends('layouts.layout')

@section('title', 'Edit Guide Language')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">General Settings / Guide Languages /</span> Edit
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
            <h5 class="mb-0">Edit Language</h5>
            <a href="{{ route('guide-languages.index') }}" class="btn btn-outline-secondary btn-sm">
                Back
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('guide-languages.update', $language) }}" method="POST" class="row g-3">
                @csrf
                @method('PUT')

                <div class="col-md-6">
                    <label class="form-label">Language Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $language->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Model</label>
                    <input type="text" name="model" class="form-control" value="{{ old('model', $language->model) }}">
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

