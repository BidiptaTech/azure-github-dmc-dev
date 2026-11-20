@extends('layouts.layout')

@section('title', 'Guide Languages')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">General Settings /</span> Guide Languages
    </h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Add Language</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('guide-languages.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label class="form-label">Language Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Model</label>
                    <input type="text" name="model" class="form-control" value="{{ old('model') }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i> Add
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Languages List</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Language</th>
                            <th>Model</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($languages as $idx => $language)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>{{ $language->name }}</td>
                                <td>{{ $language->model }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('guide-languages.edit', $language) }}"
                                           class="btn btn-primary btn-sm rounded-circle d-flex justify-content-center align-items-center"
                                           style="width: 28px; height: 28px; padding: 0;" title="Edit">
                                            <i class="ri-pencil-line" style="font-size: 16px;"></i>
                                        </a>

                                        <form action="{{ route('guide-languages.destroy', $language) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-danger btn-sm rounded-circle d-flex justify-content-center align-items-center"
                                                    style="width: 28px; height: 28px; padding: 0;"
                                                    title="Delete"
                                                    onclick="return confirm('Are you sure you want to delete this language?')">
                                                <i class="ri-delete-bin-line" style="font-size: 16px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No languages found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

