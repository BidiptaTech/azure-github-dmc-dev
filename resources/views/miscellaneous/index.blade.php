@extends('layouts.layout')
@section('title', 'Miscellaneous Items')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Miscellaneous Items</h5>
            <a href="{{ route('miscellaneous.create') }}" class="btn btn-primary">
                <i class="ri-add-line me-1"></i> Add New Item
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if($items->isEmpty())
                <div class="alert alert-info">
                    No miscellaneous items found. <a href="{{ route('miscellaneous.create') }}">Create your first item</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Item Name</th>
                                <th>Description</th>
                                <th>DMCs Using</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr>
                                <td>{{ $item->mis_id }}</td>
                                <td>
                                    @if($item->image)
                                        <img src="{{ (str_starts_with($item->image, 'http') || str_starts_with($item->image, '/')) ? $item->image : asset('storage/' . $item->image) }}" 
                                             alt="{{ $item->item_name }}" 
                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                    @else
                                        <span class="text-muted small">No image</span>
                                    @endif
                                </td>
                                <td><strong>{{ $item->item_name }}</strong></td>
                                <td>{{ Str::limit($item->description ?? 'N/A', 50) }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $item->prices_count }} DMCs</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $item->status ? 'success' : 'danger' }}">
                                        {{ $item->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('miscellaneous.edit', $item->mis_id) }}" 
                                           class="btn btn-sm btn-primary"
                                           title="Edit">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <form action="{{ route('miscellaneous.destroy', $item->mis_id) }}" 
                                              method="POST" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Are you sure you want to delete this item? This will also remove it from all DMCs.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
