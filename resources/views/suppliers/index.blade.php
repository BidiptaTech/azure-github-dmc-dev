@extends('layouts.layout')
@section('title', 'Supplier Master')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container { width: 100% !important; }
    .select2-container .select2-selection--single { height: 38px !important; display: flex !important; align-items: center !important; }
    .supplier-action-btns {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }
    .supplier-action-btns .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }
    .supplier-action-btns .btn-icon i {
        font-size: 16px;
        line-height: 1;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Settings /</span> Supplier Master
    </h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="alert alert-info mb-4">
        <strong>Step 1:</strong> Set API keys in the <strong>API Credentials</strong> cards below → click <strong>Save to .env</strong>.<br>
        <strong>Step 2:</strong> Map each country to a supplier in the table section.
    </div>

    {{-- API credentials per supplier → writes to .env --}}
    <div class="card mb-4" id="api-credentials-section">
        <div class="card-header"><h5 class="mb-0">Step 1 — API Credentials (saved to .env)</h5></div>
        <div class="card-body">
            @forelse($supplierDefinitions as $code => $definition)
                @php
                    $values = $credentialValues[$code] ?? [];
                    $configured = filled($values['base_url'] ?? null) && filled($values['api_key'] ?? null);
                @endphp
                <div class="card mb-3 border" id="cred-{{ $code }}">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <strong>{{ $definition['label'] ?? $code }}</strong>
                        <span class="badge {{ $configured ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ $configured ? 'Configured in .env' : 'Not set yet' }}
                        </span>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('suppliers.credentials.update', $code) }}" method="POST" class="row g-3">
                            @csrf
                            @foreach($definition['fields'] ?? [] as $fieldKey => $field)
                                <div class="col-md-6">
                                    <label class="form-label">
                                        {{ $field['label'] }}
                                        <small class="text-muted d-block">{{ $field['env'] }}</small>
                                    </label>
                                    <input
                                        type="{{ $field['type'] === 'password' ? 'password' : ($field['type'] === 'number' ? 'number' : 'text') }}"
                                        name="{{ $fieldKey }}"
                                        class="form-control"
                                        value="{{ $field['type'] === 'password' ? '' : ($values[$fieldKey] ?? $field['default'] ?? '') }}"
                                        placeholder="{{ $field['type'] === 'password' ? 'Leave blank to keep current value' : '' }}"
                                    >
                                </div>
                            @endforeach
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    Save {{ $definition['label'] ?? $code }} to .env
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="alert alert-warning mb-0">
                    Supplier credential forms not loaded. Ensure <code>config/suppliers.php</code> exists, then run:
                    <code>php artisan config:clear</code> and refresh this page.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Country mapping --}}
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Step 2 — Add Country → Supplier Mapping</h5></div>
        <div class="card-body">
            <form action="{{ route('suppliers.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-4 col-lg-2">
                    <label class="form-label">Country</label>
                    <select name="country_id" id="addCountrySelect" class="form-select country-select-search" required>
                        <option value="">Search country</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" @selected(old('country_id') == $country->id)>
                                {{ $country->name }} ({{ $country->country_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label">Supplier Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label">Supplier Code</label>
                    <select name="code" class="form-select" required>
                        <option value="">Select</option>
                        @foreach($supplierCodes as $code => $label)
                            <option value="{{ $code }}" @selected(old('code') === $code)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label">Service Type</label>
                    <select name="service_type" class="form-select" required>
                        <option value="">Select</option>
                        @foreach($serviceTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('service_type', 'hotels') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label">Markup Type</label>
                    <select name="markup_type" class="form-select" required>
                        @foreach($markupTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('markup_type', 'percentage') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label">Amount</label>
                    <input type="number" name="amount" class="form-control" value="{{ old('amount', '0') }}" min="0" step="1.0" required>
                </div>
                <div class="col-md-4 col-lg-1 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="status" value="1" checked>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Save Mapping</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">Country Supplier List</h5></div>
        <div class="card-body table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Country</th>
                        <th>Supplier</th>
                        <th>Code</th>
                        <th>Service Type</th>
                        <th>Markup Type</th>
                        <th>Amount</th>
                        <th>Credentials</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td>{{ $supplier->country?->name }}</td>
                            <td>{{ $supplier->name }}</td>
                            <td><code>{{ $supplier->code }}</code></td>
                            <td>{{ $supplier->serviceTypeLabel() }}</td>
                            <td>{{ $supplier->markupTypeLabel() }}</td>
                            <td>{{ number_format((float) ($supplier->amount ?? 0), 2) }}</td>
                            <td>
                                @if($supplier->credentialsConfigured())
                                    <span class="badge bg-success">In .env</span>
                                @else
                                    <a href="#cred-{{ $supplier->code }}" class="badge bg-warning text-dark text-decoration-none">
                                        Set credentials ↑
                                    </a>
                                @endif
                            </td>
                            <td>{{ $supplier->status ? 'Active' : 'Inactive' }}</td>
                            <td>
                                <div class="supplier-action-btns">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-icon" data-bs-toggle="modal" data-bs-target="#editSupplier{{ $supplier->id }}" title="Edit" aria-label="Edit">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this supplier mapping?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" title="Delete" aria-label="Delete">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <div class="modal fade" id="editSupplier{{ $supplier->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('suppliers.update', $supplier) }}" method="POST">
                                        @csrf @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit — {{ $supplier->country?->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body row g-3">
                                            <div class="col-12">
                                                <label class="form-label">Country</label>
                                                <select name="country_id" class="form-select country-select-search" required>
                                                    @foreach($countries as $country)
                                                        <option value="{{ $country->id }}" @selected($supplier->country_id == $country->id)>{{ $country->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Supplier Name</label>
                                                <input type="text" name="name" class="form-control" value="{{ $supplier->name }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Supplier Code</label>
                                                <select name="code" class="form-select" required>
                                                    @foreach($supplierCodes as $code => $label)
                                                        <option value="{{ $code }}" @selected($supplier->code === $code)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Service Type</label>
                                                <select name="service_type" class="form-select" required>
                                                    @foreach($serviceTypes as $value => $label)
                                                        <option value="{{ $value }}" @selected(($supplier->service_type ?? 'hotels') === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Markup Type</label>
                                                <select name="markup_type" class="form-select" required>
                                                    @foreach($markupTypes as $value => $label)
                                                        <option value="{{ $value }}" @selected(($supplier->markup_type ?? 'percentage') === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Amount</label>
                                                <input type="number" name="amount" class="form-control" value="{{ $supplier->amount ?? 0 }}" min="0" step="1.0" required>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="status" value="1" @checked($supplier->status)>
                                                    <label class="form-check-label">Active</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">Update</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted">No mappings yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    function initCountrySelect($el) {
        if (!$el.length || typeof $.fn.select2 === 'undefined' || $el.hasClass('select2-hidden-accessible')) return;
        const $parent = $el.closest('.modal');
        $el.select2({ placeholder: 'Search country', allowClear: true, width: '100%', dropdownParent: $parent.length ? $parent : $(document.body) });
    }
    $(function () {
        initCountrySelect($('#addCountrySelect'));
        $('.modal').on('shown.bs.modal', function () {
            $(this).find('.country-select-search').each(function () { initCountrySelect($(this)); });
        });
    });
</script>
@endsection
