@extends('layouts.admin')

@section('title', 'Edit Tour Enquiry')

@php
use Illuminate\Support\Facades\Crypt;
@endphp

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
                                <tr data-order-id="{{ $order->id }}" data-booking-id="{{ $order->booking_id }}">
                                    <td>{{ ucfirst(str_replace('_', ' ', $order->type)) }}</td>
                                    <td>{{ $order->booking_id }}</td>
                                    <td>
                                        @php
                                            $orderData = is_array($order->data) ? $order->data : json_decode($order->data, true);
                                            $firstItem = $orderData[0] ?? [];
                                            $serviceDate = $firstItem['bookingDate'] ?? $firstItem['checkIn'] ?? $firstItem['pickupdate'] ?? 'N/A';
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
                                                echo ($firstItem['entrypickup'] ?? 'Pickup') . ' → ' . ($firstItem['entrydropoff'] ?? 'Dropoff');
                                            } elseif ($order->type == 'entry_port' || $order->type == 'exit_port') {
                                                echo ($firstItem['port_name'] ?? 'Port') . ' Transfer';
                                            } elseif ($order->type == 'miscellaneous') {
                                                echo $firstItem['item_name'] ?? 'Misc Item';
                                            } else {
                                                echo 'N/A';
                                            }
                                        @endphp
                                    </td>
                                    <td>
                                        @php
                                            $cost = $firstItem['cost'] ?? $firstItem['adultCost'] ?? $firstItem['totalPrice'] ?? 0;
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
                                        <button class="btn btn-sm btn-danger" onclick="markOrderForDeletion({{ $order->id }}, {{ $order->booking_id }})">
                                            <i class="ri-delete-bin-line"></i> Delete
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

<script>
    const tourId = {{ $tour->tour_id }};
    let orders = @json($orders);
    let ordersToDelete = []; // Track orders marked for deletion
    
    function markOrderForDeletion(orderId, bookingId) {
        if (!confirm('Are you sure you want to delete this order? This action cannot be undone.')) {
            return;
        }
        
        // Add to deletion list
        ordersToDelete.push(bookingId);
        
        // Remove from display
        const row = document.querySelector(`tr[data-order-id="${orderId}"]`);
        if (row) {
            row.style.backgroundColor = '#ffebee';
            row.style.textDecoration = 'line-through';
            row.querySelector('.btn-danger').disabled = true;
            row.querySelector('.btn-danger').textContent = 'Marked for deletion';
        }
        
        console.log('Orders to delete:', ordersToDelete);
    }
    
    function saveChanges() {
        const markupValue = document.getElementById('markupValueEdit').value;
        const markupType = document.getElementById('markupTypeEdit').value;
        const discountValue = document.getElementById('discountValueEdit').value;
        const discountType = document.getElementById('discountTypeEdit').value;
        
        // Prepare services data by grouping orders by type
        const servicesData = {
            entry_port: [],
            exit_port: [],
            accommodations: [],
            tours: [],
            meals: [],
            transfers: [],
            guides: [],
            miscellaneous: []
        };
        
        // Helper function to create unique key for deduplication
        function createUniqueKey(item, type) {
            let keyData = {};
            switch(type) {
                case 'entry_port':
                case 'exit_port':
                    keyData = {
                        port_id: item.port_id || '',
                        port_name: item.port_name || '',
                        bookingDate: item.bookingDate || '',
                        type: item.type || ''
                    };
                    break;
                case 'hotel':
                    keyData = {
                        hotel_id: item.hotel_unique_id || item.hotelDetails?.hotel_id || '',
                        checkIn: item.checkIn || '',
                        checkOut: item.checkOut || ''
                    };
                    break;
                case 'attraction':
                    keyData = {
                        attraction_id: item.attraction_id || '',
                        AttractionName: item.AttractionName || '',
                        bookingDate: item.bookingDate || ''
                    };
                    break;
                case 'restaurant':
                    keyData = {
                        restaurant_id: item.restaurant_id || '',
                        restaurantName: item.restaurantName || '',
                        bookingDate: item.bookingDate || ''
                    };
                    break;
                case 'local_transport':
                    keyData = {
                        vehicle_id: item.vehicle_id || '',
                        entrypickup: item.entrypickup || '',
                        entrydropoff: item.entrydropoff || '',
                        bookingDate: item.bookingDate || ''
                    };
                    break;
                case 'guide':
                    keyData = {
                        guide_id: item.guide_id || '',
                        guide_name: item.guide_name || '',
                        bookingDate: item.bookingDate || ''
                    };
                    break;
                case 'miscellaneous':
                    keyData = {
                        item_id: item.item_id || '',
                        item_name: item.item_name || '',
                        bookingDate: item.bookingDate || ''
                    };
                    break;
            }
            return JSON.stringify(keyData);
        }
        
        // Track seen items to prevent duplicates
        const seenKeys = {
            entry_port: new Set(),
            exit_port: new Set(),
            hotel: new Set(),
            attraction: new Set(),
            restaurant: new Set(),
            local_transport: new Set(),
            guide: new Set(),
            miscellaneous: new Set()
        };
        
        // Group orders by type (excluding those marked for deletion and duplicates)
        orders.forEach(order => {
            if (ordersToDelete.includes(order.booking_id)) {
                return; // Skip orders marked for deletion
            }
            
            const orderData = typeof order.data === 'string' ? JSON.parse(order.data) : order.data;
            const firstItem = orderData[0] || {};
            
            // Create unique key for this item
            const uniqueKey = createUniqueKey(firstItem, order.type);
            
            // Check if we've already seen this exact item
            if (seenKeys[order.type] && seenKeys[order.type].has(uniqueKey)) {
                console.log('Skipping duplicate order on frontend:', order.type, order.booking_id);
                return; // Skip duplicate
            }
            
            // Mark as seen
            if (seenKeys[order.type]) {
                seenKeys[order.type].add(uniqueKey);
            }
            
            // Add tour_id to the item
            firstItem.tour_id = tourId;
            firstItem.booking_id = order.booking_id;
            
            switch(order.type) {
                case 'entry_port':
                    servicesData.entry_port.push(firstItem);
                    break;
                case 'exit_port':
                    servicesData.exit_port.push(firstItem);
                    break;
                case 'hotel':
                    servicesData.accommodations.push(firstItem);
                    break;
                case 'attraction':
                    servicesData.tours.push(firstItem);
                    break;
                case 'restaurant':
                    servicesData.meals.push(firstItem);
                    break;
                case 'local_transport':
                    servicesData.transfers.push(firstItem);
                    break;
                case 'guide':
                    servicesData.guides.push(firstItem);
                    break;
                case 'miscellaneous':
                    servicesData.miscellaneous.push(firstItem);
                    break;
            }
        });
        
        // Log deduplicated counts
        console.log('Services after deduplication:', {
            entry_port: servicesData.entry_port.length,
            exit_port: servicesData.exit_port.length,
            accommodations: servicesData.accommodations.length,
            tours: servicesData.tours.length,
            meals: servicesData.meals.length,
            transfers: servicesData.transfers.length,
            guides: servicesData.guides.length,
            miscellaneous: servicesData.miscellaneous.length
        });
        
        // Prepare form data
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
        
        // Append services data
        if (servicesData.entry_port.length > 0) {
            formData.append('entry_port', JSON.stringify(servicesData.entry_port));
        }
        if (servicesData.exit_port.length > 0) {
            formData.append('exit_port', JSON.stringify(servicesData.exit_port));
        }
        if (servicesData.accommodations.length > 0) {
            formData.append('accommodations', JSON.stringify(servicesData.accommodations));
        }
        if (servicesData.tours.length > 0) {
            formData.append('tours', JSON.stringify(servicesData.tours));
        }
        if (servicesData.meals.length > 0) {
            formData.append('meals', JSON.stringify(servicesData.meals));
        }
        if (servicesData.transfers.length > 0) {
            formData.append('transfers', JSON.stringify(servicesData.transfers));
        }
        if (servicesData.guides.length > 0) {
            formData.append('guides', JSON.stringify(servicesData.guides));
        }
        if (servicesData.miscellaneous.length > 0) {
            formData.append('miscellaneous', JSON.stringify(servicesData.miscellaneous));
        }
        
        // Append orders to delete
        if (ordersToDelete.length > 0) {
            formData.append('orders_to_delete', JSON.stringify(ordersToDelete));
        }
        
        // Show loading state
        const saveBtn = document.querySelector('button[onclick="saveChanges()"]');
        const originalText = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';
        
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
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
            
            if (data.success) {
                alert('Tour enquiry updated successfully!');
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to update tour'));
            }
        })
        .catch(error => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
            console.error('Error:', error);
            alert('An error occurred while updating the tour');
        });
    }
</script>
@endsection
