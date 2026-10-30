@extends('layouts.layout')

@section('title', 'Edit DMC Func App')

@section('content')
@include('dmc-func-app._styles')

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">General Settings / DMC Func App /</span> Edit
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
            <h5 class="mb-0">Edit Method</h5>
            <a href="{{ route('dmc-func-app.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
        <div class="card-body">
            <form action="{{ route('dmc-func-app.update', $method) }}" method="POST" class="row g-3" id="dmcFuncAppForm">
                @csrf
                @method('PUT')
                <div class="col-md-4">
                    <label class="form-label">Function Name</label>
                    <input type="text" name="function_name" id="function_name" class="form-control" value="{{ old('function_name', $method->function_name) }}" required>
                    <div class="form-text">No spaces or special characters. Letters, numbers, and underscore only.</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Maximum DMC Limit</label>
                    <input type="number" name="maximum_limit" id="maximum_limit" class="form-control" min="1" value="{{ old('maximum_limit', $method->maximum_limit) }}" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Assigned DMCs</label>
                    @include('dmc-func-app._picker', [
                        'pickerOptions' => $dmcs,
                        'pickerSelected' => old('dmc_id', $selectedDmcIds),
                        'pickerHint' => 'DMCs already assigned to another method are hidden.',
                    ])
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

@section('scripts')
@include('dmc-func-app._select-script')
@endsection
