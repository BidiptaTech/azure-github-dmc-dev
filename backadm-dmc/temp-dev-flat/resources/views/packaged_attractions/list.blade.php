@extends('layouts.layout')
@section('content')
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Packaged Attractions</h5>
                <a href="{{ route('packaged-attractions.create') }}" class="btn btn-primary">
                    <i class="mdi mdi-plus me-1"></i> Add New
                </a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="packaged-attractions-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Package Name</th>
                                <th>Image</th>
                                <th>Adult Price</th>
                                <th>Child Price</th>
                                <th>Senior Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($packagedAttractions as $attraction)
                                <tr>
                                    <td>{{ $attraction->id }}</td>
                                    <td>{{ $attraction->package_attraction_id }}</td>
                                    <td>
                                        @if($attraction->image)
                                            <img src="{{ asset($attraction->image) }}" alt="Package Image" class="img-thumbnail" width="50">
                                        @else
                                            <span class="badge bg-label-warning">No Image</span>
                                        @endif
                                    </td>
                                    <td>${{ number_format($attraction->adult_price, 2) }}</td>
                                    <td>${{ number_format($attraction->child_price, 2) }}</td>
                                    <td>${{ number_format($attraction->senior_citizen_price, 2) }}</td>
                                    <td>
                                        @if($attraction->status == 1)
                                            <span class="badge bg-label-success">Active</span>
                                        @else
                                            <span class="badge bg-label-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="mdi mdi-dots-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a class="dropdown-item" href="{{ route('packaged-attractions.show', $attraction->id) }}">
                                                    <i class="mdi mdi-eye me-1"></i> View
                                                </a>
                                                <a class="dropdown-item" href="{{ route('packaged-attractions.edit', $attraction->id) }}">
                                                    <i class="mdi mdi-pencil-outline me-1"></i> Edit
                                                </a>
                                                <form action="{{ route('packaged-attractions.destroy', $attraction->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item" onclick="return confirm('Are you sure you want to delete this packaged attraction?')">
                                                        <i class="mdi mdi-trash-can-outline me-1"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#packaged-attractions-table').DataTable({
            "order": [[0, "desc"]],
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]]
        });
    });
</script>
@endsection
