@extends('layouts.admin')

@section('title', 'Edit Tour Enquiry')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Edit Tour Enquiry - {{ $tour->display_id }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Edit Tour Enquiry</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tour Information</h5>
                </div>
                <div class="card-body">
                    <!-- Tour Basic Information -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Destination</label>
                            <input type="text" class="form-control" id="destinationInput" value="{{ $tour->destination }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="startDateInput" value="{{ $tour->check_in_time->format('Y-m-d') }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" id="endDateInput" value="{{ $tour->check_out_time->format('Y-m-d') }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <input type="text" class="form-control" value="{{ $tour->tour_status }}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Adults</label>
                            <input type="number" class="form-control" id="adultsInput" value="{{ $tour->adult }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Children</label>
                            <input type="number" class="form-control" id="childrenInput" value="{{ $tour->child }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Infants</label>
                            <input type="number" class="form-control" id="infantsInput" value="{{ $tour->infant }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Agent</label>
                            <input type="text" class="form-control" value="{{ $agent->name ?? 'N/A' }}" readonly>
                        </div>
                    </div>

                    <!-- Markup and Discount Controls -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <strong>Markup & Discount Settings</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Markup Type</label>
                            <select class="form-select" id="markupTypeEdit">
                                <option value="percentage" {{ $markupType == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="flat" {{ $markupType == 'flat' ? 'selected' : '' }}>Flat Amount</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Markup Value</label>
                            <input type="number" class="form-control" id="markupValueEdit" value="{{ $markupValue }}" step="0.01" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Discount Type</label>
                            <select class="form-select" id="discountTypeEdit">
                                <option value="">None</option>
                                <option value="percentage" {{ $discountType == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="flat" {{ $discountType == 'flat' ? 'selected' : '' }}>Flat Amount</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Discount Value</label>
                            <input type="number" class="form-control" id="discountValueEdit" value="{{ $discountValue }}" step="0.01" min="0">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tour Orders</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Order Type</th>
                                    <th>Booking ID</th>
                                    <th>Service Date</th>
                                    <th>Details</th>
                                    <th>Cost/Sell</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="ordersTableBody">
                                @forelse($orders as $order)
                                <tr data-order-id="{{ $order->id }}">
                                    <td>{{ ucfirst($order->type) }}</td>
                                    <td>{{ $order->booking_id }}</td>
                                    <td>
                                        @php
                                            $orderData = is_array($order->data) ? $order->data : json_decode($order->data, true);
                                            $firstItem = $orderData[0] ?? [];
                                            $serviceDate = $firstItem['bookingDate'] ?? $firstItem['checkIn'] ?? 'N/A';
                                        @endphp
                                        {{ $serviceDate }}
                                    </td>
                                    <td>
                                        @php
                                            // Display relevant details based on order type
                                            if ($order->type == 'hotel') {
                                                echo $firstItem['hotelDetails']['hotel_name'] ?? 'N/A';
                                            } elseif ($order->type == 'attraction') {
                                                echo $firstItem['AttractionName'] ?? 'N/A';
                                            } elseif ($order->type == 'restaurant') {
                                                echo $firstItem['restaurantName'] ?? 'N/A';
                                            } elseif ($order->type == 'guide') {
                                                echo $firstItem['guide_name'] ?? 'N/A';
                                            } elseif ($order->type == 'vehicle' || $order->type == 'local_transport') {
                                                echo $firstItem['vehicles_name'] ?? 'N/A';
                                            } else {
                                                echo 'N/A';
                                            }
                                        @endphp
                                    </td>
                                    <td>
                                        @php
                                            $cost = $firstItem['cost'] ?? $firstItem['adultCost'] ?? 0;
                                            $sell = $firstItem['sell'] ?? $firstItem['adultSell'] ?? $firstItem['price'] ?? $firstItem['totalPrice'] ?? 0;
                                        @endphp
                                        Cost: {{ number_format($cost, 2) }}<br>
                                        Sell: {{ number_format($sell, 2) }}
                                    </td>
                                    <td>
                                        @if($order->status == 1)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="editOrder({{ $order->id }})">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteOrder({{ $order->id }})">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No orders found for this tour</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-12 text-end">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                                <i class="ri-arrow-left-line me-1"></i> Back to Dashboard
                            </a>
                            <button type="button" class="btn btn-primary" onclick="saveChanges()">
                                <i class="ri-save-line me-1"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Edit Modal -->
<div class="modal fade" id="orderEditModal" tabindex="-1" aria-labelledby="orderEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderEditModalLabel">Edit Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="orderEditForm">
                    <!-- Order edit form will be dynamically loaded here -->
                    <p>Loading order details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveOrderChanges()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    const tourId = {{ $tour->tour_id }};
    const orders = @json($orders);
    
    function editOrder(orderId) {
        // Find the order in the orders array
        const order = orders.find(o => o.id === orderId);
        if (!order) {
            alert('Order not found');
            return;
        }
        
        // Parse order data
        const orderData = typeof order.data === 'string' ? JSON.parse(order.data) : order.data;
        const firstItem = orderData[0] || {};
        
        // Build edit form based on order type
        let formHTML = `<div class="row">`;
        
        // Display cost and sell fields for editing
        if (order.type === 'hotel') {
            formHTML += `
                <div class="col-md-6">
                    <label class="form-label">Hotel Name</label>
                    <input type="text" class="form-control" value="${firstItem.hotelDetails?.hotel_name || 'N/A'}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Cost</label>
                    <input type="number" class="form-control" id="editCost" value="${firstItem.cost || 0}" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sell</label>
                    <input type="number" class="form-control" id="editSell" value="${firstItem.sell || firstItem.price || 0}" step="0.01">
                </div>
            `;
        } else if (order.type === 'attraction') {
            formHTML += `
                <div class="col-md-6">
                    <label class="form-label">Attraction Name</label>
                    <input type="text" class="form-control" value="${firstItem.AttractionName || 'N/A'}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Adult Cost</label>
                    <input type="number" class="form-control" id="editAdultCost" value="${firstItem.ticket_details?.adult_cost || 0}" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Adult Sell</label>
                    <input type="number" class="form-control" id="editAdultSell" value="${firstItem.ticket_details?.adult_sell || 0}" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Child Cost</label>
                    <input type="number" class="form-control" id="editChildCost" value="${firstItem.ticket_details?.child_cost || 0}" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Child Sell</label>
                    <input type="number" class="form-control" id="editChildSell" value="${firstItem.ticket_details?.child_sell || 0}" step="0.01">
                </div>
            `;
        } else if (order.type === 'restaurant') {
            formHTML += `
                <div class="col-md-6">
                    <label class="form-label">Restaurant Name</label>
                    <input type="text" class="form-control" value="${firstItem.restaurantName || 'N/A'}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Adult Cost</label>
                    <input type="number" class="form-control" id="editAdultCost" value="${firstItem.adultCost || 0}" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Adult Sell</label>
                    <input type="number" class="form-control" id="editAdultSell" value="${firstItem.adultSell || 0}" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Child Cost</label>
                    <input type="number" class="form-control" id="editChildCost" value="${firstItem.childCost || 0}" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Child Sell</label>
                    <input type="number" class="form-control" id="editChildSell" value="${firstItem.childSell || 0}" step="0.01">
                </div>
            `;
        } else {
            formHTML += `
                <div class="col-12">
                    <p>Order type: ${order.type}</p>
                    <pre>${JSON.stringify(firstItem, null, 2)}</pre>
                </div>
            `;
        }
        
        formHTML += `</div>`;
        formHTML += `<input type="hidden" id="editOrderId" value="${orderId}">`;
        
        document.getElementById('orderEditForm').innerHTML = formHTML;
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('orderEditModal'));
        modal.show();
    }
    
    function saveOrderChanges() {
        const orderId = document.getElementById('editOrderId').value;
        
        // Get updated values
        // This is a simplified version - full implementation would handle all order types
        const updatedData = {
            order_id: orderId,
            // Add updated cost/sell values here
        };
        
        // Send update request
        alert('Order update functionality - to be implemented fully');
        
        // Close modal
        bootstrap.Modal.getInstance(document.getElementById('orderEditModal')).hide();
    }
    
    function deleteOrder(orderId) {
        if (!confirm('Are you sure you want to delete this order?')) {
            return;
        }
        
        alert('Order deletion functionality - to be implemented');
    }
    
    function saveChanges() {
        const markupValue = document.getElementById('markupValueEdit').value;
        const markupType = document.getElementById('markupTypeEdit').value;
        const discountValue = document.getElementById('discountValueEdit').value;
        const discountType = document.getElementById('discountTypeEdit').value;
        
        // Collect all order data
        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('destination', document.getElementById('destinationInput').value);
        formData.append('start_date', document.getElementById('startDateInput').value);
        formData.append('end_date', document.getElementById('endDateInput').value);
        formData.append('adults', document.getElementById('adultsInput').value);
        formData.append('children', document.getElementById('childrenInput').value);
        formData.append('infants', document.getElementById('infantsInput').value);
        formData.append('agent_id', {{ $tour->agent_id }});
        formData.append('agency_id', {{ $agent->agency_id ?? 0 }});
        formData.append('markup_value', markupValue);
        formData.append('markup_type', markupType);
        formData.append('discount_value', discountValue);
        formData.append('discount_type', discountType);
        
        // Send to server
        fetch('{{ route("enquiry-form-pro.update", $tour->tour_id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Tour enquiry updated successfully!');
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to update tour'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the tour');
        });
    }
</script>
@endsection

