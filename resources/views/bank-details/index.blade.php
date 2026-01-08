@extends('layouts.layout')

@section('title', 'Bank Details')

@section('content')
<style>
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
    }
    
    .action-btn {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        color: #333;
        background: #fff;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .table-container {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .table thead th {
        background-color: #667eea;
        color: white;
        border: none;
        font-weight: 600;
        padding: 1rem 0.75rem;
    }
    
    .table tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h2 class="mb-2"><i class="ri-bank-line me-2"></i>Bank Details</h2>
                    <p class="mb-0 opacity-90">Manage payment terms, bank details, and terms & conditions</p>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0">
                    <a href="{{ route('bank-details.create') }}" class="action-btn">
                        <i class="ri-add-line me-2"></i>Add New Bank Details
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Table Container -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Account Name</th>
                            <th>Account Number</th>
                            <th>Bank Address</th>
                            <th>Status</th>
                            <th>Created By</th>
                            @if(auth()->user()->role_id == 11)
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bankDetails as $bankDetail)
                            <tr>
                                <td>{{ $bankDetail->bank_detail_id }}</td>
                                <td>{{ $bankDetail->account_name }}</td>
                                <td>{{ $bankDetail->account_number }}</td>
                                <td>{{ Str::limit($bankDetail->bank_address, 50) }}</td>
                                <td>
                                    @if($bankDetail->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $bankDetail->creator->name ?? 'N/A' }}</td>
                                @if(auth()->user()->role_id == 11)
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('bank-details.edit', Crypt::encrypt($bankDetail->bank_detail_id)) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <form action="{{ route('bank-details.destroy', Crypt::encrypt($bankDetail->bank_detail_id)) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Are you sure you want to delete this bank detail?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="ri-inbox-line me-2"></i>No bank details found. 
                                    <a href="{{ route('bank-details.create') }}">Create one now</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

