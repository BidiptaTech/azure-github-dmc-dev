  @extends('layouts.layout')
@section('title', 'Edit Invoice')
@php
use Illuminate\Support\Facades\Crypt;
@endphp

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Edit Proforma Invoice: {{ $invoice->proforma_number }}</h5>
                        <small class="text-muted">Tour ID: {{ $invoice->tour->display_id ?? $invoice->tour_id }}</small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('invoices.show', Crypt::encrypt($invoice->invoice_id)) }}" 
                           class="btn btn-outline-secondary btn-sm">
                            <i class="ri-arrow-left-line me-1"></i> Back to Invoice
                        </a>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('invoices.update', Crypt::encrypt($invoice->invoice_id)) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control" 
                                           name="invoice_date" 
                                           value="{{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('Y-m-d') : date('Y-m-d') }}"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Due Date</label>
                                    <input type="date" 
                                           class="form-control" 
                                           name="due_date" 
                                           value="{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('Y-m-d') : '' }}">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Validity Date</label>
                                    <input type="date" 
                                           class="form-control" 
                                           name="validity_date" 
                                           value="{{ $invoice->validity_date ? \Carbon\Carbon::parse($invoice->validity_date)->format('Y-m-d') : '' }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="draft" {{ $invoice->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="issued" {{ $invoice->status === 'issued' ? 'selected' : '' }}>Issued</option>
                                        <option value="pending" {{ $invoice->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Items -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Invoice Items</h6>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addItemRow()">
                                    <i class="ri-add-line me-1"></i> Add Item
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="itemsTable">
                                    <thead>
                                        <tr>
                                            <th width="30%">Description</th>
                                            <th width="15%">Type</th>
                                            <th width="10%">Adults</th>
                                            <th width="10%">Children</th>
                                            <th width="10%">Infants</th>
                                            <th width="12%">Unit Price</th>
                                            <th width="12%">Total Price</th>
                                            <th width="1%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTableBody">
                                        @foreach($invoice->items as $index => $item)
                                        <tr>
                                            <td>
                                                <input type="text" 
                                                       class="form-control form-control-sm" 
                                                       name="items[{{ $index }}][description]" 
                                                       value="{{ $item->description ?? '' }}" 
                                                       required>
                                            </td>
                                            <td>
                                                <input type="text" 
                                                       class="form-control form-control-sm" 
                                                       name="items[{{ $index }}][item_type]" 
                                                       value="{{ $item->item_type ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       class="form-control form-control-sm quantity-input" 
                                                       name="items[{{ $index }}][quantity_adults]" 
                                                       value="{{ $item->quantity_adults ?? 0 }}" 
                                                       min="0" 
                                                       step="1">
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       class="form-control form-control-sm quantity-input" 
                                                       name="items[{{ $index }}][quantity_children]" 
                                                       value="{{ $item->quantity_children ?? 0 }}" 
                                                       min="0" 
                                                       step="1">
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       class="form-control form-control-sm quantity-input" 
                                                       name="items[{{ $index }}][quantity_infants]" 
                                                       value="{{ $item->quantity_infants ?? 0 }}" 
                                                       min="0" 
                                                       step="1">
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       class="form-control form-control-sm unit-price-input" 
                                                       name="items[{{ $index }}][unit_price]" 
                                                       value="{{ $item->unit_price ?? 0 }}" 
                                                       min="0" 
                                                       step="0.01" 
                                                       required>
                                            </td>
                                            <td>
                                                <input type="number" 
                                                       class="form-control form-control-sm total-price-input" 
                                                       name="items[{{ $index }}][total_price]" 
                                                       value="{{ $item->total_price ?? 0 }}" 
                                                       min="0" 
                                                       step="0.01" 
                                                       readonly>
                                            </td>
                                            <td>
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger" 
                                                        onclick="removeItemRow(this)">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                        @if($invoice->items->count() === 0)
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">No items. Click "Add Item" to add one.</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6" class="text-end"><strong>Subtotal:</strong></td>
                                            <td class="text-end">
                                                <strong id="subtotalDisplay">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format($invoice->subtotal, 2) }}</strong>
                                                <input type="hidden" name="subtotal" id="subtotalInput" value="{{ $invoice->subtotal }}">
                                            </td>
                                            <td></td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td colspan="6" class="text-end"><strong>Total Amount:</strong></td>
                                            <td class="text-end">
                                                <strong id="totalDisplay">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format($invoice->total_amount, 2) }}</strong>
                                                <input type="hidden" name="total_amount" id="totalInput" value="{{ $invoice->total_amount }}">
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" 
                                      name="notes" 
                                      rows="4" 
                                      placeholder="Additional notes or terms...">{{ $invoice->notes ?? '' }}</textarea>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('invoices.show', Crypt::encrypt($invoice->invoice_id)) }}" class="btn btn-secondary">
                                <i class="ri-close-line me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i> Update Invoice
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let itemIndex = {{ $invoice->items->count() }};

function addItemRow() {
    const tbody = document.getElementById('itemsTableBody');
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <input type="text" 
                   class="form-control form-control-sm" 
                   name="items[${itemIndex}][description]" 
                   required>
        </td>
        <td>
            <input type="text" 
                   class="form-control form-control-sm" 
                   name="items[${itemIndex}][item_type]">
        </td>
        <td>
            <input type="number" 
                   class="form-control form-control-sm quantity-input" 
                   name="items[${itemIndex}][quantity_adults]" 
                   value="0" 
                   min="0" 
                   step="1">
        </td>
        <td>
            <input type="number" 
                   class="form-control form-control-sm quantity-input" 
                   name="items[${itemIndex}][quantity_children]" 
                   value="0" 
                   min="0" 
                   step="1">
        </td>
        <td>
            <input type="number" 
                   class="form-control form-control-sm quantity-input" 
                   name="items[${itemIndex}][quantity_infants]" 
                   value="0" 
                   min="0" 
                   step="1">
        </td>
        <td>
            <input type="number" 
                   class="form-control form-control-sm unit-price-input" 
                   name="items[${itemIndex}][unit_price]" 
                   value="0" 
                   min="0" 
                   step="0.01" 
                   required>
        </td>
        <td>
            <input type="number" 
                   class="form-control form-control-sm total-price-input" 
                   name="items[${itemIndex}][total_price]" 
                   value="0" 
                   min="0" 
                   step="0.01" 
                   readonly>
        </td>
        <td>
            <button type="button" 
                    class="btn btn-sm btn-danger" 
                    onclick="removeItemRow(this)">
                <i class="ri-delete-bin-line"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
    
    // Attach event listeners to new row
    attachItemEventListeners(row);
    
    itemIndex++;
    
    // Remove "No items" message if present
    const noItemsRow = tbody.querySelector('td[colspan]');
    if (noItemsRow && noItemsRow.textContent.includes('No items')) {
        noItemsRow.parentElement.remove();
    }
}

function removeItemRow(button) {
    const row = button.closest('tr');
    row.remove();
    calculateTotals();
}

function attachItemEventListeners(row) {
    const unitPriceInput = row.querySelector('.unit-price-input');
    const quantityInputs = row.querySelectorAll('.quantity-input');
    const totalPriceInput = row.querySelector('.total-price-input');
    
    function calculateRowTotal() {
        const unitPrice = parseFloat(unitPriceInput.value) || 0;
        const adults = parseFloat(quantityInputs[0].value) || 0;
        const children = parseFloat(quantityInputs[1].value) || 0;
        const infants = parseFloat(quantityInputs[2].value) || 0;
        const totalQuantity = adults + children + infants;
        const total = unitPrice * totalQuantity;
        totalPriceInput.value = total.toFixed(2);
        calculateTotals();
    }
    
    unitPriceInput.addEventListener('input', calculateRowTotal);
    quantityInputs.forEach(input => {
        input.addEventListener('input', calculateRowTotal);
    });
}

function calculateTotals() {
    const rows = document.querySelectorAll('#itemsTableBody tr');
    let subtotal = 0;
    
    rows.forEach(row => {
        const totalInput = row.querySelector('.total-price-input');
        if (totalInput) {
            subtotal += parseFloat(totalInput.value) || 0;
        }
    });
    
    // For proforma invoices, total = subtotal (no GST)
    const total = subtotal;
    
    document.getElementById('subtotalInput').value = subtotal.toFixed(2);
    document.getElementById('totalInput').value = total.toFixed(2);
    document.getElementById('subtotalDisplay').textContent = '{{ $invoice->base_currency ?? "SGD" }} ' + subtotal.toFixed(2);
    document.getElementById('totalDisplay').textContent = '{{ $invoice->base_currency ?? "SGD" }} ' + total.toFixed(2);
}

// Attach event listeners to existing rows
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('#itemsTableBody tr');
    rows.forEach(row => {
        if (row.querySelector('.unit-price-input')) {
            attachItemEventListeners(row);
        }
    });
});
</script>
@endsection

