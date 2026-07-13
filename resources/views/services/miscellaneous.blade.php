@extends('layouts.layout')

@section('title', 'Select Miscellaneous Items')

@push('css')
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('content')
<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-lg-12 mb-4 order-0">
                <div class="card">
                    <div class="d-flex align-items-end row">
                        <div class="col-sm-7">
                            <div class="card-body">
                                <h5 class="card-title text-primary">Select Miscellaneous Items & Set Prices</h5>
                                <p class="mb-4">
                                    Choose miscellaneous items you want to offer and set your pricing. Click on items to select them and enter prices.
                                </p>
                            </div>
                        </div>
                        <div class="col-sm-5 text-center text-sm-left">
                            <div class="card-body pb-0 px-0 px-md-4">
                                <i class="ri-add-box-line" style="font-size: 4rem; color: #7367f0;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selected Items Section -->
        <div class="card mb-4 {{ (!isset($selectedItems) || count($selectedItems) === 0) ? 'd-none' : '' }}" id="selectedItemsSection">
            <div class="card-header">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-2">
                    <h5 class="mb-0" id="selectedItemsTitle">Your Selected Items ({{ isset($selectedItems) ? count($selectedItems) : 0 }})</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-success" onclick="saveAllPrices()">
                            <i class="ri-save-line me-1"></i>Save All Prices
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form id="priceUpdateForm" action="{{ route('services.miscellaneous.update') }}" method="POST">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-hover mb-2">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">Remove</th>
                                    <th style="width: 35%;">Item Name</th>
                                    <th style="width: 15%;">Adult Price</th>
                                    <th style="width: 15%;">Child Price</th>
                                    <th style="width: 15%;">Infant Price</th>
                                    <th style="width: 15%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="selectedItemsBody">
                                @foreach(($selectedItems ?? []) as $item)
                                    <tr data-item-id="{{ $item->mis_id }}"
                                        data-display-name="{{ $item->item_name }}"
                                        data-description="{{ $item->description ?? '' }}"
                                        data-image-url="{{ $item->image ? ((str_starts_with($item->image, 'http') || str_starts_with($item->image, '/')) ? $item->image : asset('storage/' . $item->image)) : '' }}">
                                        <td>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger remove-item-btn" 
                                                    data-item-id="{{ $item->mis_id }}"
                                                    title="Remove Item">
                                                <i class="ri-close-line"></i>
                                            </button>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($item->image)
                                                    <img src="{{ (str_starts_with($item->image, 'http') || str_starts_with($item->image, '/')) ? $item->image : asset('storage/' . $item->image) }}" 
                                                         alt="{{ $item->item_name }}" 
                                                         class="rounded me-2" 
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="ri-add-box-line text-muted"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <strong>{{ $item->item_name }}</strong>
                                                    @if($item->description)
                                                        <br><small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                            <input type="hidden" name="selected_items[{{ $item->mis_id }}][selected]" value="1">
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   step="0.01" 
                                                   min="0"
                                                   class="form-control form-control-sm" 
                                                   name="selected_items[{{ $item->mis_id }}][adult_price]" 
                                                   value="{{ $item->adult_price ?? 0 }}"
                                                   placeholder="0.00">
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   step="0.01" 
                                                   min="0"
                                                   class="form-control form-control-sm" 
                                                   name="selected_items[{{ $item->mis_id }}][child_price]" 
                                                   value="{{ $item->child_price ?? 0 }}"
                                                   placeholder="0.00">
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   step="0.01" 
                                                   min="0"
                                                   class="form-control form-control-sm" 
                                                   name="selected_items[{{ $item->mis_id }}][infant_price]" 
                                                   value="{{ $item->infant_price ?? 0 }}"
                                                   placeholder="0.00">
                                        </td>
                                        <td>
                                            <button type="button" 
                                                    class="btn btn-sm btn-primary quick-save-btn" 
                                                    data-item-id="{{ $item->mis_id }}"
                                                    title="Quick Save">
                                                <i class="ri-save-line me-1"></i>Save
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>

        <!-- Available Items Section -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-2">
                    <h5 class="mb-0" id="availableItemsTitle">Available Items ({{ count($availableItems) }})</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-info" onclick="testJQuery()">
                            <i class="ri-bug-line me-1"></i>Test JS
                        </button>
                        <div class="input-group input-group-sm" style="width: 260px;">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input type="text" class="form-control" id="availableItemSearch" placeholder="Search items...">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="availableItemsTableWrapper" class="{{ count($availableItems) > 0 ? '' : 'd-none' }}">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="availableItemsBody">
                            @foreach($availableItems as $item)
                                @php
                                    $itemImageUrl = $item->image
                                        ? ((str_starts_with($item->image, 'http') || str_starts_with($item->image, '/')) ? $item->image : asset('storage/' . $item->image))
                                        : '';
                                @endphp
                                <tr class="available-item-row"
                                    data-item-id="{{ $item->mis_id }}"
                                    data-item-name="{{ strtolower($item->item_name) }}"
                                    data-display-name="{{ $item->item_name }}"
                                    data-description="{{ $item->description ?? '' }}"
                                    data-image-url="{{ $itemImageUrl }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($item->image)
                                                <img src="{{ $itemImageUrl }}" 
                                                     alt="{{ $item->item_name }}" 
                                                     class="rounded me-2" 
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="ri-add-box-line text-muted"></i>
                                                </div>
                                            @endif
                                            <strong>{{ $item->item_name }}</strong>
                                        </div>
                                    </td>
                                    <td>{{ Str::limit($item->description ?? 'N/A', 100) }}</td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-sm btn-success add-item-btn" 
                                                data-item-id="{{ $item->mis_id }}"
                                                data-item-name="{{ $item->item_name }}">
                                            <i class="ri-add-line me-1"></i>Add Item
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                </div>
                <div id="availableItemsEmpty" class="alert alert-info mb-0 {{ count($availableItems) > 0 ? 'd-none' : '' }}">
                    <i class="ri-information-line me-2"></i>All available items have been selected. Great job!
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    function updateSelectedItemCount() {
        const count = document.querySelectorAll('#selectedItemsBody tr[data-item-id]').length;
        const title = document.getElementById('selectedItemsTitle');
        const section = document.getElementById('selectedItemsSection');
        if (title) title.textContent = `Your Selected Items (${count})`;
        if (section) section.classList.toggle('d-none', count === 0);
    }

    function updateAvailableItemCount() {
        const count = document.querySelectorAll('#availableItemsBody .available-item-row').length;
        const title = document.getElementById('availableItemsTitle');
        const wrapper = document.getElementById('availableItemsTableWrapper');
        const empty = document.getElementById('availableItemsEmpty');
        if (title) title.textContent = `Available Items (${count})`;
        if (wrapper) wrapper.classList.toggle('d-none', count === 0);
        if (empty) empty.classList.toggle('d-none', count > 0);
    }

    function buildSelectedItemRow(itemRow, prices) {
        const itemId = itemRow.getAttribute('data-item-id');
        const name = itemRow.getAttribute('data-display-name') || '';
        const description = itemRow.getAttribute('data-description') || '';
        const imageUrl = itemRow.getAttribute('data-image-url') || '';
        const adult = prices?.adult_price ?? 0;
        const child = prices?.child_price ?? 0;
        const infant = prices?.infant_price ?? 0;
        const imageHtml = imageUrl
            ? `<img src="${escapeHtml(imageUrl)}" alt="${escapeHtml(name)}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">`
            : `<div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="ri-add-box-line text-muted"></i></div>`;
        const descHtml = description ? `<br><small class="text-muted">${escapeHtml(description.length > 50 ? description.substring(0, 50) + '...' : description)}</small>` : '';

        const row = document.createElement('tr');
        row.setAttribute('data-item-id', itemId);
        row.setAttribute('data-display-name', name);
        row.setAttribute('data-description', description);
        row.setAttribute('data-image-url', imageUrl);
        row.innerHTML = `
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" data-item-id="${escapeHtml(itemId)}" title="Remove Item">
                    <i class="ri-close-line"></i>
                </button>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    ${imageHtml}
                    <div><strong>${escapeHtml(name)}</strong>${descHtml}</div>
                </div>
                <input type="hidden" name="selected_items[${escapeHtml(itemId)}][selected]" value="1">
            </td>
            <td><input type="number" step="0.01" min="0" class="form-control form-control-sm" name="selected_items[${escapeHtml(itemId)}][adult_price]" value="${adult}" placeholder="0.00"></td>
            <td><input type="number" step="0.01" min="0" class="form-control form-control-sm" name="selected_items[${escapeHtml(itemId)}][child_price]" value="${child}" placeholder="0.00"></td>
            <td><input type="number" step="0.01" min="0" class="form-control form-control-sm" name="selected_items[${escapeHtml(itemId)}][infant_price]" value="${infant}" placeholder="0.00"></td>
            <td>
                <button type="button" class="btn btn-sm btn-primary quick-save-btn" data-item-id="${escapeHtml(itemId)}" title="Quick Save">
                    <i class="ri-save-line me-1"></i>Save
                </button>
            </td>`;
        return row;
    }

    function addItemToSelectedTable(itemRow, prices) {
        const itemId = itemRow.getAttribute('data-item-id');
        if (document.querySelector(`#selectedItemsBody tr[data-item-id="${itemId}"]`)) return;
        const tbody = document.getElementById('selectedItemsBody');
        if (!tbody) return;
        tbody.insertBefore(buildSelectedItemRow(itemRow, prices), tbody.firstChild);
        itemRow.remove();
        updateSelectedItemCount();
        updateAvailableItemCount();
    }

    function restoreItemToAvailableTable(itemRowData) {
        const tbody = document.getElementById('availableItemsBody');
        if (!tbody) return;

        const row = document.createElement('tr');
        row.className = 'available-item-row';
        row.setAttribute('data-item-id', itemRowData.itemId);
        row.setAttribute('data-item-name', (itemRowData.name || '').toLowerCase());
        row.setAttribute('data-display-name', itemRowData.name || '');
        row.setAttribute('data-description', itemRowData.description || '');
        row.setAttribute('data-image-url', itemRowData.imageUrl || '');
        const imageHtml = itemRowData.imageUrl
            ? `<img src="${escapeHtml(itemRowData.imageUrl)}" alt="${escapeHtml(itemRowData.name)}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">`
            : `<div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="ri-add-box-line text-muted"></i></div>`;
        const desc = itemRowData.description || 'N/A';
        const descDisplay = desc.length > 100 ? desc.substring(0, 100) + '...' : desc;

        row.innerHTML = `
            <td><div class="d-flex align-items-center">${imageHtml}<strong>${escapeHtml(itemRowData.name)}</strong></div></td>
            <td>${escapeHtml(descDisplay)}</td>
            <td>
                <button type="button" class="btn btn-sm btn-success add-item-btn" data-item-id="${escapeHtml(itemRowData.itemId)}" data-item-name="${escapeHtml(itemRowData.name)}">
                    <i class="ri-add-line me-1"></i>Add Item
                </button>
            </td>`;
        tbody.appendChild(row);
        updateAvailableItemCount();
    }

    function captureItemRowData(itemRow) {
        return {
            itemId: itemRow.getAttribute('data-item-id'),
            name: itemRow.getAttribute('data-display-name') || '',
            description: itemRow.getAttribute('data-description') || '',
            imageUrl: itemRow.getAttribute('data-image-url') || '',
        };
    }

    // Test function
    function testJQuery() {
        console.log('Test button clicked');
        console.log('jQuery version:', jQuery.fn.jquery);
        console.log('Swal available:', typeof Swal !== 'undefined');
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Test Successful!',
                text: 'jQuery and SweetAlert are working correctly.',
                timer: 2000
            });
        } else {
            alert('jQuery is working but SweetAlert is not loaded!');
        }
    }

    // Check if jQuery is loaded
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded!');
        alert('ERROR: jQuery is not loaded! The Add Item button will not work.');
    } else {
        console.log('jQuery loaded successfully, version:', jQuery.fn.jquery);
    }
    
    // Check if Swal is loaded
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 is not loaded!');
    } else {
        console.log('SweetAlert2 loaded successfully');
    }

    // Add Item
    $(document).on('click', '.add-item-btn', function(e) {
        e.preventDefault();
        console.log('Add Item button clicked');
        
        const button = $(this);
        const itemId = button.data('item-id');
        const itemName = button.data('item-name');
        
        console.log('Item ID:', itemId);
        console.log('Item Name:', itemName);
        
        button.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i>Adding...');
        
        $.ajax({
            url: '{{ route("services.miscellaneous.select") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                item_id: itemId,
                preserve_existing_prices: 1,
                adult_price: 0,
                child_price: 0,
                infant_price: 0
            },
            success: function(response) {
                console.log('Success response:', response);
                if (response.success) {
                    const itemRow = button.closest('.available-item-row').get(0);
                    if (itemRow) {
                        addItemToSelectedTable(itemRow, response.prices || { adult_price: 0, child_price: 0, infant_price: 0 });
                    }

                    if (typeof Swal !== 'undefined') {
                        const successMessage = response.action === 'restored'
                            ? itemName + ' has been restored with previous prices.'
                            : itemName + ' has been added. Please set prices and save.';

                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: successMessage,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(response.message || (itemName + ' has been added successfully!'));
                    }
                } else {
                    button.prop('disabled', false).html('<i class="ri-add-line me-1"></i>Add Item');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response:', xhr.responseText);
                
                button.prop('disabled', false).html('<i class="ri-add-line me-1"></i>Add Item');
                
                let errorMessage = 'Failed to add item. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });
                } else {
                    alert('Error: ' + errorMessage);
                }
            }
        });
    });

    // Remove Item
    $(document).on('click', '.remove-item-btn', function() {
        const button = $(this);
        const itemId = button.data('item-id');
        
        Swal.fire({
            title: 'Remove Item?',
            text: 'Are you sure you want to remove this item?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, remove it!'
        }).then((result) => {
            if (result.isConfirmed) {
                button.prop('disabled', true);
                
                $.ajax({
                    url: '{{ route("services.miscellaneous.remove") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        item_id: itemId
                    },
                    success: function(response) {
                        if (response.success) {
                            const selectedRow = document.querySelector(`#selectedItemsBody tr[data-item-id="${itemId}"]`);
                            let itemData = {
                                itemId: String(itemId),
                                name: '',
                                description: '',
                                imageUrl: '',
                            };

                            if (selectedRow) {
                                itemData = {
                                    itemId: String(itemId),
                                    name: selectedRow.getAttribute('data-display-name') || selectedRow.querySelector('strong')?.textContent.trim() || '',
                                    description: selectedRow.getAttribute('data-description') || '',
                                    imageUrl: selectedRow.getAttribute('data-image-url') || '',
                                };
                                selectedRow.remove();
                                updateSelectedItemCount();
                            }

                            restoreItemToAvailableTable(itemData);

                            Swal.fire({
                                icon: 'success',
                                title: 'Removed!',
                                text: response.message || 'Item has been removed.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            button.prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        button.prop('disabled', false);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to remove item. Please try again.'
                        });
                    }
                });
            }
        });
    });

    // Quick Save Single Item
    $(document).on('click', '.quick-save-btn', function() {
        const button = $(this);
        const row = button.closest('tr');
        const itemId = button.data('item-id');
        
        const data = {
            _token: '{{ csrf_token() }}',
            item_id: itemId,
            adult_price: row.find('input[name="selected_items[' + itemId + '][adult_price]"]').val() || 0,
            child_price: row.find('input[name="selected_items[' + itemId + '][child_price]"]').val() || 0,
            infant_price: row.find('input[name="selected_items[' + itemId + '][infant_price]"]').val() || 0
        };
        
        button.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i>Saving...');
        
        $.ajax({
            url: '{{ route("services.miscellaneous.select") }}',
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    button.html('<i class="ri-check-line me-1"></i>Saved!');
                    setTimeout(() => {
                        button.prop('disabled', false).html('<i class="ri-save-line me-1"></i>Save');
                    }, 2000);
                }
            },
            error: function(xhr) {
                button.prop('disabled', false).html('<i class="ri-save-line me-1"></i>Save');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to save prices. Please try again.'
                });
            }
        });
    });

    // Save All Prices
    function saveAllPrices() {
        Swal.fire({
            title: 'Saving...',
            text: 'Please wait while we save all prices.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $('#priceUpdateForm').submit();
    }

    $('#availableItemSearch').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        $('.available-item-row').each(function() {
            const itemName = $(this).data('item-name') || $(this).attr('data-item-name') || '';
            $(this).toggle(itemName.includes(searchTerm));
        });
    });
</script>
@endpush

@endsection
