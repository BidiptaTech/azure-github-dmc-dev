@extends('layouts.layout')
@section('title', 'View Miscellaneous Item')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <!-- Item Details Card -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Item Details</h5>
                    <div>
                        <a href="{{ route('miscellaneous.edit', $item->mis_id) }}" class="btn btn-sm btn-primary">
                            <i class="ri-edit-line me-1"></i> Edit
                        </a>
                        <a href="{{ route('miscellaneous.index') }}" class="btn btn-sm btn-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($item->image)
                        <div class="mb-3 text-center">
                            <img src="{{ asset('storage/' . $item->image) }}" 
                                 alt="{{ $item->item_name }}" 
                                 style="max-width: 100%; max-height: 300px; border-radius: 8px; border: 1px solid #ddd;">
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Item ID:</label>
                        <p class="mb-0">{{ $item->mis_id }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Item Name:</label>
                        <p class="mb-0">{{ $item->item_name }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description:</label>
                        <p class="mb-0">{{ $item->description ?? 'N/A' }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status:</label>
                        <p class="mb-0">
                            <span class="badge bg-{{ $item->status ? 'success' : 'danger' }}">
                                {{ $item->status ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Created At:</label>
                        <p class="mb-0">{{ $item->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Last Updated:</label>
                        <p class="mb-0">{{ $item->updated_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- DMC Prices Card -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">DMC Pricing Information</h5>
                    <p class="text-muted small mb-0">{{ $item->prices->count() }} DMC(s) using this item</p>
                </div>
                <div class="card-body">
                    @if($item->prices->isEmpty())
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>
                            No DMCs have configured pricing for this item yet.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>DMC ID</th>
                                        <th>Adult Price</th>
                                        <th>Child Price</th>
                                        <th>Infant Price</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($item->prices as $price)
                                    <tr>
                                        <td><strong>{{ $price->dmc_id }}</strong></td>
                                        <td>{{ number_format($price->adult_price, 2) }}</td>
                                        <td>{{ number_format($price->child_price, 2) }}</td>
                                        <td>{{ number_format($price->infant_price, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $price->status ? 'success' : 'danger' }} badge-sm">
                                                {{ $price->status ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Actions Card -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex gap-2">
                        <a href="{{ route('miscellaneous.edit', $item->mis_id) }}" class="btn btn-primary">
                            <i class="ri-edit-line me-1"></i> Edit Item
                        </a>
                        <form action="{{ route('miscellaneous.destroy', $item->mis_id) }}" 
                              method="POST" 
                              style="display: inline;"
                              onsubmit="return confirm('Are you sure you want to delete this item? This will also remove it from all DMCs and cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="ri-delete-bin-line me-1"></i> Delete Item
                            </button>
                        </form>
                        <a href="{{ route('miscellaneous.index') }}" class="btn btn-secondary ms-auto">
                            <i class="ri-arrow-left-line me-1"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

