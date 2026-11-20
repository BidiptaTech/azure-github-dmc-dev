@extends('layouts.layout')

@section('title', 'DMC Func App')

@section('content')
@include('dmc-func-app._styles')

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">General Settings /</span> DMC Func App
    </h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
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
            <h5 class="mb-0">Add Method</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('dmc-func-app.store') }}" method="POST" class="row g-3" id="dmcFuncAppForm">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">Function Name</label>
                    <input type="text" name="function_name" id="function_name" class="form-control" value="{{ old('function_name') }}" placeholder="method1" required>
                    <div class="form-text">No spaces or special characters. Letters, numbers, and underscore only.</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Maximum DMC Limit</label>
                    <input type="number" name="maximum_limit" id="maximum_limit" class="form-control" min="1" value="{{ old('maximum_limit', 5) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Assign DMCs</label>
                    @include('dmc-func-app._picker', [
                        'pickerOptions' => $availableDmcs,
                        'pickerSelected' => old('dmc_id', []),
                        'pickerHint' => 'Only unassigned DMCs are listed.',
                    ])
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i> Add Method
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">DMC Func App Methods</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th style="width: 180px;">Function Name</th>
                            <th style="width: 120px;">Limit</th>
                            <th>Assigned DMCs</th>
                            <th style="width: 90px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($methods as $idx => $method)
                            @php $assignedIds = $method->dmc_ids; @endphp
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td><code>{{ $method->function_name }}</code></td>
                                <td>
                                    {{ count($assignedIds) }} / {{ $method->maximum_limit }}
                                    @if($method->hasReachedLimit())
                                        <span class="badge bg-warning text-dark ms-1">Full</span>
                                    @endif
                                </td>
                                <td>
                                    @if(empty($assignedIds))
                                        <span class="text-muted">None</span>
                                    @else
                                        <div class="dmc-badge-wrap">
                                            @foreach($assignedIds as $dmcId)
                                                @php
                                                    $style = \App\Models\DmcFuncApp::badgeStyle($dmcId);
                                                    $label = $dmcNames[$dmcId] ?? $dmcNames[(string) $dmcId] ?? ('DMC #' . $dmcId);
                                                @endphp
                                                <span class="dmc-color-badge" style="background: {{ $style['bg'] }}; color: {{ $style['color'] }};">
                                                    {{ $label }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('dmc-func-app.edit', $method) }}"
                                           class="btn btn-primary btn-sm rounded-circle d-flex justify-content-center align-items-center"
                                           style="width: 28px; height: 28px; padding: 0;" title="Edit">
                                            <i class="ri-pencil-line" style="font-size: 16px;"></i>
                                        </a>
                                        <form action="{{ route('dmc-func-app.destroy', $method) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-danger btn-sm rounded-circle d-flex justify-content-center align-items-center"
                                                    style="width: 28px; height: 28px; padding: 0;"
                                                    title="Delete"
                                                    onclick="return confirm('Delete this method and unassign its DMCs?')">
                                                <i class="ri-delete-bin-line" style="font-size: 16px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No DMC Func App methods found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@include('dmc-func-app._select-script')
@endsection
