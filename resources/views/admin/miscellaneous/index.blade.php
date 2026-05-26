@extends('layouts.layout')
@section('title', 'Miscellaneous Items')
@php use Illuminate\Support\Facades\Crypt; @endphp

@section('css')
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<style>
    /* Premium Wrapper */
    .container-p-y > .card {
        border: 1px solid #d0d7e2;
        border-radius: 0.75rem;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06), 0 0 1px rgba(0, 0, 0, 0.08);
        background: linear-gradient(180deg, #ffffff 0%, #fbfcfe 100%);
        overflow: hidden;
    }

    /* Page Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.5rem;
        background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%);
        border-bottom: 1px solid #d0d7e2;
        margin-bottom: 0;
    }

    .page-title {
        font-size: 1.35rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
        letter-spacing: -0.01em;
    }

    .page-subtitle {
        font-size: 12.5px;
        color: #64748b;
        margin: 0.15rem 0 0;
        font-weight: 500;
    }

    .btn-premium {
        border-radius: 10px;
        font-weight: 700;
        font-size: 12.5px;
        padding: 0.45rem 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.06);
    }

    .btn-premium.btn-primary {
        background: linear-gradient(135deg, #6366f1, #4f46e5);
        border: none;
    }

    .btn-premium.btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(99, 102, 241, 0.25);
    }

    /* Table shell + premium table */
    .table-shell {
        padding: 1rem 1.25rem 1.25rem;
        background: #fff;
    }

    .table-premium {
        border-collapse: separate !important;
        border-spacing: 0;
        overflow: hidden;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
        background: #fff;
    }

    .table-premium thead th {
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        color: #334155;
        font-weight: 800;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 0.55rem 0.75rem !important;
        white-space: nowrap;
    }

    .table-premium tbody td {
        vertical-align: middle;
        font-size: 12.5px;
        color: #334155;
        padding: 0.55rem 0.75rem !important;
    }

    .table-premium tbody tr:nth-child(even) {
        background: #fbfdff;
    }

    .table-premium tbody tr:hover {
        background: #f4f7ff;
    }

    .table-premium > :not(caption) > * > * {
        border-bottom-color: #eef2f7;
    }

    .table-premium.table-hover > tbody > tr:hover > * {
        color: inherit;
    }

    /* Thumbnail */
    .item-thumb {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #dbe3ee;
        padding: 6px;
        object-fit: cover;
        box-shadow: 0 1px 6px rgba(0,0,0,0.06);
    }

    /* Badges */
    .badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.28rem 0.55rem;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 800;
        letter-spacing: 0.02em;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .badge-pill .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }

    .badge-pill.info {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .badge-pill.info .dot { background: #3b82f6; }

    .badge-pill.active {
        background: #ecfdf5;
        color: #065f46;
        border-color: #a7f3d0;
    }

    .badge-pill.active .dot { background: #10b981; }

    .badge-pill.inactive {
        background: #fef2f2;
        color: #7f1d1d;
        border-color: #fecaca;
    }

    .badge-pill.inactive .dot { background: #ef4444; }

    /* Action buttons */
    .btn-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid transparent;
        transition: all 0.2s ease;
    }

    .btn-icon:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.10);
    }

    .btn-icon-edit {
        background: #eef2ff;
        border-color: #c7d2fe;
        color: #4f46e5;
    }

    .btn-icon-delete {
        background: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
    }

    /* Pagination spacing */
    .pagination-wrap {
        padding: 0 1.25rem 1.25rem;
    }

    @media (max-width: 768px) {
        .page-header { padding: 0.9rem 1rem; }
        .page-title { font-size: 1.2rem; }
        .table-shell { padding: 0.85rem 1rem 1rem; }
        .pagination-wrap { padding: 0 1rem 1rem; }
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="page-header">
            <div>
                <h5 class="page-title">Miscellaneous Items</h5>
                <p class="page-subtitle">Manage optional items, images, descriptions, and status</p>
            </div>
            <a href="{{ route('miscellaneous.create') }}" class="btn btn-primary btn-premium">
                <i class="ri-add-line"></i> Add New Item
            </a>
        </div>
        <div class="card-body p-0">
            @if(session('success'))
                <div class="px-3 pt-3">
                    <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif
            
            @if($items->isEmpty())
                <div class="p-3">
                    <div class="alert alert-info mb-0">
                        No miscellaneous items found. <a href="{{ route('miscellaneous.create') }}">Create your first item</a>
                    </div>
                </div>
            @else
                <div class="table-shell">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-premium">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Item Name</th>
                                <th>Description</th>
                                <th>DMCs Using</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    @if($item->image)
                                        <img src="{{ (str_starts_with($item->image, 'http') || str_starts_with($item->image, '/')) ? $item->image : asset('storage/' . $item->image) }}" 
                                             alt="{{ $item->item_name }}" 
                                             class="item-thumb">
                                    @else
                                        <span class="text-muted small">No image</span>
                                    @endif
                                </td>
                                <td><strong>{{ $item->item_name }}</strong></td>
                                <td>{{ Str::limit($item->description ?? 'N/A', 55) }}</td>
                                <td>
                                    <span class="badge-pill info"><span class="dot"></span>{{ $item->prices_count }} DMCs</span>
                                </td>
                                <td>
                                    @if($item->status)
                                        <span class="badge-pill active"><span class="dot"></span>Active</span>
                                    @else
                                        <span class="badge-pill inactive"><span class="dot"></span>Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-inline-flex gap-2" role="group">
                                        <a href="{{ route('miscellaneous.edit', Crypt::encrypt($item->mis_id)) }}" 
                                           class="btn btn-icon btn-icon-edit"
                                           title="Edit">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-icon btn-icon-delete"
                                                title="Delete"
                                                onclick="deleteMiscItem('{{ route('miscellaneous.destroy', Crypt::encrypt($item->mis_id)) }}', '{{ addslashes($item->item_name) }}')">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                </div>
                
                <div class="pagination-wrap">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<script>
window.deleteMiscItem = function(deleteUrl, itemName) {
    Swal.fire({
        title: 'Delete Item?',
        text: `Are you sure you want to delete \"${itemName}\"? This will also remove it from all DMCs.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = deleteUrl;

            const csrfToken = document.querySelector('meta[name=\"csrf-token\"]');
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken.getAttribute('content');
                form.appendChild(csrfInput);
            }

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
};
</script>
@endsection
