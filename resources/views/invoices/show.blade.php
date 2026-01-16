@extends('layouts.layout')
@section('title', 'Invoice Details')
@php
use Illuminate\Support\Facades\Crypt;
@endphp

@section('content')
<style>
    .invoice-container {
        background: #f8f9fa;
        padding: 20px;
    }
    .invoice-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 10px 10px 0 0;
        margin-bottom: 0;
    }
    .invoice-header h2 {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
    }
    .invoice-header .invoice-number {
        font-size: 18px;
        opacity: 0.9;
        margin-top: 8px;
    }
    .invoice-body {
        background: white;
        padding: 30px;
        border-radius: 0 0 10px 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .info-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        border-left: 4px solid #667eea;
    }
    .info-section h5 {
        color: #667eea;
        font-weight: 600;
        margin-bottom: 15px;
        font-size: 16px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #e9ecef;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        font-weight: 600;
        color: #495057;
        min-width: 150px;
    }
    .info-value {
        color: #212529;
        text-align: right;
    }
    .service-section {
        margin-bottom: 30px;
    }
    .service-title {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 20px;
        border-radius: 6px 6px 0 0;
        font-weight: 600;
        font-size: 16px;
        margin-bottom: 0;
    }
    .invoice-table {
        border: none;
        margin-bottom: 0;
    }
    .invoice-table thead {
        background: #f8f9fa;
    }
    .invoice-table thead th {
        border: none;
        padding: 15px;
        font-weight: 600;
        color: #495057;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #dee2e6;
    }
    .invoice-table tbody td {
        border: none;
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }
    .invoice-table tbody tr:hover {
        background: #f8f9fa;
    }
    .price-cell {
        font-size: 15px;
        font-weight: 600;
        color: #28a745;
    }
    .price-cell.unit {
        color: #6c757d;
        font-weight: 500;
    }
    .summary-box {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 25px;
        border-radius: 8px;
        margin-top: 30px;
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #dee2e6;
        align-items: center;
    }
    .summary-row:last-child {
        border-bottom: none;
    }
    .summary-label {
        font-size: 15px;
        color: #495057;
        font-weight: 500;
    }
    .summary-value {
        font-size: 18px;
        font-weight: 700;
        color: #212529;
    }
    .summary-value.highlight {
        font-size: 24px;
        color: #28a745;
    }
    .summary-value.danger {
        color: #dc3545;
    }
    .summary-value.info {
        color: #17a2b8;
    }
    .currency-card {
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s;
    }
    .currency-card:hover {
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
    }
    .currency-label {
        font-size: 14px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 10px;
    }
    .currency-amount {
        font-size: 28px;
        font-weight: 700;
        color: #667eea;
    }
    .action-buttons {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .badge-custom {
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 600;
        border-radius: 20px;
    }
    .section-divider {
        height: 2px;
        background: linear-gradient(90deg, transparent, #667eea, transparent);
        margin: 30px 0;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y invoice-container">
    <div class="row">
        <div class="col-12">
            <!-- Action Buttons -->
            <div class="action-buttons mb-4">
                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">
                            @if($invoice->invoice_type === 'proforma')
                                <i class="ri-file-paper-line text-info"></i> Proforma Invoice
                            @else
                                <i class="ri-file-text-line text-success"></i> Tax Invoice
                            @endif
                        </h4>
                        <small class="text-muted">Booking ID: {{ $invoice->tour->display_id ?? $invoice->tour_id }}</small>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('invoices.view', Crypt::encrypt($invoice->invoice_id)) }}" 
                           class="btn btn-outline-primary btn-sm"
                           target="_blank">
                            <i class="ri-eye-line me-1"></i> View PDF
                        </a>
                        <a href="{{ route('invoices.download', Crypt::encrypt($invoice->invoice_id)) }}" 
                           class="btn btn-primary btn-sm">
                            <i class="ri-download-line me-1"></i> Download PDF with Services
                        </a>
                        <a href="{{ route('invoices.download-price-only', Crypt::encrypt($invoice->invoice_id)) }}" 
                           class="btn btn-info btn-sm">
                            <i class="ri-file-download-line me-1"></i> Download PDF only Price
                        </a>
                        <!-- @if($invoice->isEditable())
                        <a href="{{ route('invoices.edit', Crypt::encrypt($invoice->invoice_id)) }}" 
                           class="btn btn-outline-warning btn-sm">
                            <i class="ri-pencil-line me-1"></i> Edit
                        </a>
                        @endif -->
                        <a href="{{ route('bookings.view-tour', Crypt::encrypt($invoice->tour_id)) }}" 
                           class="btn btn-outline-secondary btn-sm">
                            <i class="ri-arrow-left-line me-1"></i> Back to Tour
                        </a>
                    </div>
                </div>
            </div>

            <!-- Invoice Header -->
            <div class="invoice-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2>
                            @if($invoice->invoice_type === 'proforma')
                                PROFORMA INVOICE
                            @else
                                TAX INVOICE
                            @endif
                        </h2>
                        <div class="invoice-number">
                            @if($invoice->invoice_type === 'proforma')
                                {{ $invoice->proforma_number }}
                            @else
                                {{ $invoice->invoice_number }}
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div style="background: rgba(255,255,255,0.2); padding: 15px; border-radius: 8px; display: inline-block;">
                            <div style="font-size: 14px; opacity: 0.9;">Status</div>
                            <div style="font-size: 20px; font-weight: 700; margin-top: 5px;">
                                <span class="badge-custom bg-{{ $invoice->status === 'issued' ? 'success' : ($invoice->status === 'paid' ? 'primary' : 'warning') }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Body -->
            <div class="invoice-body">
                <!-- Invoice Details & Financial Summary -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="info-section">
                            <h5><i class="ri-information-line me-2"></i>Invoice Details</h5>
                            <div class="info-row">
                                <span class="info-label">Invoice Type:</span>
                                <span class="info-value">
                                    @if($invoice->invoice_type === 'proforma')
                                        <span class="badge-custom bg-info">Proforma Invoice</span>
                                    @else
                                        <span class="badge-custom bg-success">Final Invoice (Tax Invoice)</span>
                                    @endif
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Invoice Date:</span>
                                <span class="info-value">{{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('jS M Y') : 'N/A' }}</span>
                            </div>
                            @if($invoice->due_date)
                            <div class="info-row">
                                <span class="info-label">Due Date:</span>
                                <span class="info-value">{{ \Carbon\Carbon::parse($invoice->due_date)->format('jS M Y') }}</span>
                            </div>
                            @endif
                            @if($invoice->validity_date && $invoice->invoice_type === 'proforma')
                            <div class="info-row">
                                <span class="info-label">Validity Date:</span>
                                <span class="info-value">{{ \Carbon\Carbon::parse($invoice->validity_date)->format('jS M Y') }}</span>
                            </div>
                            @endif
                            <div class="info-row">
                                <span class="info-label">Destination:</span>
                                <span class="info-value">{{ $invoice->destination ?? 'N/A' }}</span>
                            </div>
                            @if($invoice->agent)
                            <div class="info-row">
                                <span class="info-label">Agent:</span>
                                <span class="info-value">{{ $invoice->agent->name ?? 'N/A' }}</span>
                            </div>
                            @endif
                            @if($invoice->dmc)
                            <div class="info-row">
                                <span class="info-label">DMC:</span>
                                <span class="info-value">{{ $invoice->dmc->company_name ?? 'N/A' }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-section" style="border-left-color: #28a745;">
                            <h5 style="color: #28a745;"><i class="ri-money-dollar-circle-line me-2"></i>Financial Summary</h5>
                            @php
                                $tour = $invoice->tour;
                                $tourStatus = $tour->tour_status ?? '';
                                $statusesWithTax = ['Confirmed', 'Definite', 'Actual'];
                                $shouldShowTax = in_array($tourStatus, $statusesWithTax);
                                
                                $negotiatedAmountTop = $invoice->getNegotiatedAmount();
                                $actualAmountTop = $invoice->items->sum('total_price');
                                $baseAmountTop = $negotiatedAmountTop ?? $actualAmountTop;
                                $gstAmountTop = $invoice->gst_amount ?? 0;
                                $finalPriceTop = $shouldShowTax ? ($baseAmountTop + $gstAmountTop) : $baseAmountTop;
                                $paymentReceivedTop = $invoice->payment_received ?? 0;
                                $outstandingBalanceTop = $finalPriceTop - $paymentReceivedTop;
                            @endphp
                            <div class="info-row">
                                <span class="info-label">Total Amount:</span>
                                <span class="info-value">
                                    <strong style="color: #28a745; font-size: 18px;">
                                        {{ $invoice->base_currency ?? 'SGD' }} {{ number_format(round($invoice->total_amount)) }}
                                    </strong>
                                </span>
                            </div>
                            @if($invoice->invoice_type === 'final' && $gstAmountTop > 0)
                            <div class="info-row">
                                <span class="info-label">GST Amount:</span>
                                <span class="info-value">
                                    {{ $invoice->base_currency ?? 'SGD' }} {{ number_format(round($gstAmountTop)) }}
                                </span>
                            </div>
                            @endif
                            <div class="info-row">
                                <span class="info-label">Payment Received:</span>
                                <span class="info-value">
                                    <strong style="color: #17a2b8;">
                                        {{ $invoice->base_currency ?? 'SGD' }} {{ number_format(round($paymentReceivedTop)) }}
                                    </strong>
                                </span>
                            </div>
                            <div class="info-row" style="border-top: 2px solid #dee2e6; padding-top: 15px; margin-top: 10px;">
                                <span class="info-label" style="font-size: 16px; font-weight: 700;">Outstanding Balance:</span>
                                <span class="info-value">
                                    <strong style="color: {{ $outstandingBalanceTop > 0 ? '#dc3545' : '#28a745' }}; font-size: 22px;">
                                        {{ $invoice->base_currency ?? 'SGD' }} {{ number_format(round($outstandingBalanceTop)) }}
                                    </strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Client/Guest Information -->
                @php
                    $clientDetails = $invoice->client_details ?? [];
                @endphp
                @if(!empty($clientDetails))
                <div class="info-section" style="border-left-color: #17a2b8;">
                    <h5 style="color: #17a2b8;"><i class="ri-user-line me-2"></i>Client/Guest Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Lead Guest Name:</span>
                                <span class="info-value">{{ $clientDetails['lead_guest_name'] ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Email:</span>
                                <span class="info-value">{{ $clientDetails['email'] ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Phone:</span>
                                <span class="info-value">{{ $clientDetails['phone'] ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Booking ID:</span>
                                <span class="info-value"><strong>{{ $clientDetails['booking_id'] ?? ($invoice->tour->display_id ?? 'N/A') }}</strong></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Address:</span>
                                <span class="info-value">{{ $clientDetails['address'] ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">State:</span>
                                <span class="info-value">{{ $clientDetails['city'] ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Postal Code:</span>
                                <span class="info-value">{{ $clientDetails['postal_code'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Travel Company/Agency Information -->
                @php
                    $travelCompany = $invoice->travel_company_details ?? [];
                @endphp
                @if(!empty($travelCompany))
                <div class="info-section" style="border-left-color: #ffc107;">
                    <h5 style="color: #ffc107;"><i class="ri-building-line me-2"></i>Travel Company / Agency Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Agent Name:</span>
                                <span class="info-value">{{ $travelCompany['name'] ?? 'N/A' }}</span>
                            </div>
                            @if(!empty($travelCompany['company_name']))
                            <div class="info-row">
                                <span class="info-label">Travel Agency:</span>
                                <span class="info-value">{{ $travelCompany['company_name'] ?? 'N/A' }}</span>
                            </div>
                            @endif
                            <div class="info-row">
                                <span class="info-label">Contact Person:</span>
                                <span class="info-value">{{ $travelCompany['contact_person'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <span class="info-label">Address:</span>
                                <span class="info-value">{{ $travelCompany['address'] ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Phone:</span>
                                <span class="info-value">{{ $travelCompany['phone'] ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Email:</span>
                                <span class="info-value">{{ $travelCompany['email'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <div class="section-divider"></div>

                <!-- Invoice Items -->
                <div class="service-section">
                    <h4 class="mb-4" style="color: #495057; font-weight: 700;">
                        <i class="ri-list-check me-2"></i>Service Details
                    </h4>
                    
                    @php
                        $allItems = $invoice->items ?? collect([]);
                        $hotelItems = $allItems->where('item_type', 'hotel');
                        $entryPortItems = $allItems->where('item_type', 'entry_port');
                        $attractionItems = $allItems->where('item_type', 'attraction');
                        $restaurantItems = $allItems->where('item_type', 'restaurant');
                        $guideItems = $allItems->where('item_type', 'guide');
                        $travelPointItems = $allItems->where('item_type', 'travel_point');
                        $travelHourlyItems = $allItems->where('item_type', 'travel_hourly');
                        $localTransportItems = $allItems->where('item_type', 'local_transport');
                        $exitPortItems = $allItems->where('item_type', 'exit_port');
                        $otherItems = $allItems->whereNotIn('item_type', ['hotel', 'entry_port', 'attraction', 'restaurant', 'guide', 'travel_point', 'travel_hourly', 'local_transport', 'exit_port']);
                    @endphp

                    @if($hotelItems->count() > 0)
                    <!-- Hotel Services -->
                    <h6 class="service-title">Hotel Services</h6>
                    <div class="table-responsive">
                        <table class="table invoice-table">
                            <thead>
                                <tr>
                                    <th>Hotel Name</th>
                                    <th>Room Category</th>
                                    <th>Check in</th>
                                    <th>Check out</th>
                                    <th>No. of Nights</th>
                                    <th>Total Pax</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($hotelItems as $item)
                                @php
                                    $serviceDetails = $item->service_details ?? [];
                                    $checkInDate = $serviceDetails['check_in_date'] ?? '';
                                    $checkInTime = $serviceDetails['check_in_time'] ?? '';
                                    $checkOutDate = $serviceDetails['check_out_date'] ?? '';
                                    $checkOutTime = $serviceDetails['check_out_time'] ?? '';
                                    
                                    $checkInDisplay = 'N/A';
                                    if ($checkInDate) {
                                        try {
                                            $checkInCarbon = \Carbon\Carbon::parse($checkInDate);
                                            if ($checkInTime) {
                                                $timeParts = explode(':', $checkInTime);
                                                if (count($timeParts) >= 2) {
                                                    $checkInCarbon->setTime((int)$timeParts[0], (int)$timeParts[1]);
                                                    $checkInDisplay = $checkInCarbon->format('jS M Y, h:i A');
                                                } else {
                                                    $checkInDisplay = $checkInCarbon->format('jS M Y');
                                                }
                                            } else {
                                                $checkInDisplay = $checkInCarbon->format('jS M Y');
                                            }
                                        } catch (\Exception $e) {
                                            $checkInDisplay = 'N/A';
                                        }
                                    }
                                    
                                    $checkOutDisplay = 'N/A';
                                    if ($checkOutDate) {
                                        try {
                                            $checkOutCarbon = \Carbon\Carbon::parse($checkOutDate);
                                            if ($checkOutTime) {
                                                $timeParts = explode(':', $checkOutTime);
                                                if (count($timeParts) >= 2) {
                                                    $checkOutCarbon->setTime((int)$timeParts[0], (int)$timeParts[1]);
                                                    $checkOutDisplay = $checkOutCarbon->format('jS M Y, h:i A');
                                                } else {
                                                    $checkOutDisplay = $checkOutCarbon->format('jS M Y');
                                                }
                                            } else {
                                                $checkOutDisplay = $checkOutCarbon->format('jS M Y');
                                            }
                                        } catch (\Exception $e) {
                                            $checkOutDisplay = 'N/A';
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td><strong>{{ $serviceDetails['hotel_name'] ?? ($item->description ?? 'N/A') }}</strong></td>
                                    <td>{{ $serviceDetails['room_category'] ?? 'N/A' }}</td>
                                    <td>{{ $checkInDisplay }}</td>
                                    <td>{{ $checkOutDisplay }}</td>
                                    <td>{{ $serviceDetails['no_of_days'] ?? '' }}</td>
                                    <td>{{ $serviceDetails['total_pax'] ?? 0 }}</td>
                                    <td class="text-end price-cell unit">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format($item->unit_price ?? 0, 2) }}</td>
                                    <td class="text-end price-cell">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format($item->total_price ?? 0, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    @if($entryPortItems->count() > 0)
                    <!-- Arrival Services -->
                    <h6 class="service-title" style="margin-top: 30px;">Arrival Services</h6>
                    <div class="table-responsive">
                        <table class="table invoice-table">
                            <thead>
                                <tr>
                                    <th>Entry Pickup</th>
                                    <th>Entry Dropoff</th>
                                    <th>Vehicle Name</th>
                                    <th>Type</th>
                                    <th>Pickup Date</th>
                                    <th>Total Persons</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($entryPortItems as $item)
                                @php
                                    $serviceDetails = $item->service_details ?? [];
                                    $pickupDate = $serviceDetails['pickup_date'] ?? '';
                                    $entryTime = $serviceDetails['entrytime'] ?? '';
                                    $pickupDateDisplay = 'N/A';
                                    if ($pickupDate) {
                                        try {
                                            $pickupCarbon = \Carbon\Carbon::parse($pickupDate);
                                            if ($entryTime) {
                                                $timeStr = str_replace([' AM', ' PM', 'am', 'pm'], '', $entryTime);
                                                $timeParts = explode(':', $timeStr);
                                                if (count($timeParts) >= 2) {
                                                    $hour = (int)$timeParts[0];
                                                    $minute = (int)$timeParts[1];
                                                    if (stripos($entryTime, 'PM') !== false && $hour < 12) {
                                                        $hour += 12;
                                                    } elseif (stripos($entryTime, 'AM') !== false && $hour == 12) {
                                                        $hour = 0;
                                                    }
                                                    $pickupCarbon->setTime($hour, $minute);
                                                    $pickupDateDisplay = $pickupCarbon->format('jS M Y, h:i A');
                                                } else {
                                                    $pickupDateDisplay = $pickupCarbon->format('jS M Y');
                                                }
                                            } else {
                                                $pickupDateDisplay = $pickupCarbon->format('jS M Y');
                                            }
                                        } catch (\Exception $e) {
                                            $pickupDateDisplay = 'N/A';
                                        }
                                    }
                                    $totalPersons = $serviceDetails['total_persons'] ?? (($item->quantity_adults ?? 0) + ($item->quantity_children ?? 0) + ($item->quantity_infants ?? 0));
                                @endphp
                                <tr>
                                    <td>{{ $serviceDetails['entrypickup'] ?? 'N/A' }}</td>
                                    <td>{{ $serviceDetails['entrydropoff'] ?? 'N/A' }}</td>
                                    <td>{{ $serviceDetails['vehicle_name'] ?? 'N/A' }}</td>
                                    <td>{{ $serviceDetails['vehicle_type'] ?? 'N/A' }}</td>
                                    <td>{{ $pickupDateDisplay }}</td>
                                    <td>{{ $totalPersons }}</td>
                                    <td class="text-end price-cell unit">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format($item->unit_price ?? 0, 2) }}</td>
                                    <td class="text-end price-cell">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format($item->total_price ?? 0, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    @if($attractionItems->count() > 0)
                    <!-- Attraction Services -->
                    <h6 class="service-title" style="margin-top: 30px;">Attraction Services</h6>
                    <div class="table-responsive">
                        <table class="table invoice-table">
                            <thead>
                                <tr>
                                    <th>Attraction Name</th>
                                    <th>Ticket Details</th>
                                    <th>Visit Date</th>
                                    <th>Transfer</th>
                                    <th>Type</th>
                                    <th>Way</th>
                                    <th>Vehicle Details</th>
                                    <th>Adults</th>
                                    <th>Children</th>
                                    <th>Infants</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attractionItems as $item)
                                @php
                                    $serviceDetails = $item->service_details ?? [];
                                    $visitDate = $serviceDetails['booking_date'] ?? '';
                                    $visitDateDisplay = 'N/A';
                                    if ($visitDate) {
                                        try {
                                            $visitDateDisplay = \Carbon\Carbon::parse($visitDate)->format('jS M Y');
                                        } catch (\Exception $e) {
                                            $visitDateDisplay = 'N/A';
                                        }
                                    }
                                    $transferRequired = $serviceDetails['transfer_required'] ?? 'No';
                                    $transferType = $serviceDetails['transfer_type'] ?? '';
                                    $transferWay = $serviceDetails['transfer_way'] ?? '';
                                    $vehicleDetails = $serviceDetails['vehicle_details'] ?? '';
                                @endphp
                                <tr>
                                    <td><strong>{{ $serviceDetails['attraction_name'] ?? ($item->description ?? 'N/A') }}</strong></td>
                                    <td>{{ $serviceDetails['ticket_details'] ?? 'N/A' }}</td>
                                    <td>{{ $visitDateDisplay }}</td>
                                    <td>{{ $transferRequired }}</td>
                                    <td>{{ $transferType ?: 'N/A' }}</td>
                                    <td>{{ $transferWay ?: 'N/A' }}</td>
                                    <td>{{ $vehicleDetails ?: 'N/A' }}</td>
                                    <td>{{ $item->quantity_adults ?? 0 }}</td>
                                    <td>{{ $item->quantity_children ?? 0 }}</td>
                                    <td>{{ $item->quantity_infants ?? 0 }}</td>
                                    <td class="text-end price-cell unit">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format($item->unit_price ?? 0, 2) }}</td>
                                    <td class="text-end price-cell">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format($item->total_price ?? 0, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    @if($restaurantItems->count() > 0)
                    <!-- Restaurant Services -->
                    <h6 class="service-title" style="margin-top: 30px;">Restaurant Services</h6>
                    <div class="table-responsive">
                        <table class="table invoice-table">
                            <thead>
                                <tr>
                                    <th>Restaurant Name</th>
                                    <th>Meal Type</th>
                                    <th>Booking Date</th>
                                    <th>Transfer</th>
                                    <th>Type</th>
                                    <th>Way</th>
                                    <th>Vehicle Details</th>
                                    <th>Adults</th>
                                    <th>Children</th>
                                    <th>Infants</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($restaurantItems as $item)
                                @php
                                    $serviceDetails = $item->service_details ?? [];
                                    $visitDate = $serviceDetails['booking_date'] ?? '';
                                    $visitDateDisplay = 'N/A';
                                    if ($visitDate) {
                                        try {
                                            $visitDateDisplay = \Carbon\Carbon::parse($visitDate)->format('jS M Y');
                                        } catch (\Exception $e) {
                                            $visitDateDisplay = 'N/A';
                                        }
                                    }
                                    $transferRequired = $serviceDetails['transfer_required'] ?? 'No';
                                    $transferType = $serviceDetails['transfer_type'] ?? '';
                                    $transferWay = $serviceDetails['transfer_way'] ?? '';
                                    $vehicleDetails = $serviceDetails['vehicle_details'] ?? '';
                                @endphp
                                <tr>
                                    <td><strong>{{ $serviceDetails['restaurant_name'] ?? ($item->description ?? 'N/A') }}</strong></td>
                                    <td>{{ $serviceDetails['meal_type'] ?? 'N/A' }}</td>
                                    <td>{{ $visitDateDisplay }}</td>
                                    <td>{{ $transferRequired }}</td>
                                    <td>{{ $transferType ?: 'N/A' }}</td>
                                    <td>{{ $transferWay ?: 'N/A' }}</td>
                                    <td>{{ $vehicleDetails ?: 'N/A' }}</td>
                                    <td>{{ $item->quantity_adults ?? 0 }}</td>
                                    <td>{{ $item->quantity_children ?? 0 }}</td>
                                    <td>{{ $item->quantity_infants ?? 0 }}</td>
                                    <td class="text-end price-cell unit">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format($item->unit_price ?? 0, 2) }}</td>
                                    <td class="text-end price-cell">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format($item->total_price ?? 0, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    @if($guideItems->count() > 0)
                    <!-- Guide Services -->
                    <h6 class="service-title" style="margin-top: 30px;">Guide Services</h6>
                    <div class="table-responsive">
                        <table class="table invoice-table">
                            <thead>
                                <tr>
                                    <th>Guide Name</th>
                                    <th>Pick Up Date</th>
                                    <th>Hours</th>
                                    <th>Adults</th>
                                    <th>Children</th>
                                    <th>Infants</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($guideItems as $item)
                                @php
                                    $serviceDetails = $item->service_details ?? [];
                                    $pickupDate = $serviceDetails['pickup_date'] ?? '';
                                    $pickupDateDisplay = 'N/A';
                                    if ($pickupDate) {
                                        try {
                                            $pickupDateDisplay = \Carbon\Carbon::parse($pickupDate)->format('jS M Y');
                                        } catch (\Exception $e) {
                                            $pickupDateDisplay = 'N/A';
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td><strong>{{ $serviceDetails['guide_name'] ?? ($item->description ?? 'N/A') }}</strong></td>
                                    <td>{{ $pickupDateDisplay }}</td>
                                    <td>{{ $serviceDetails['hours'] ?? 0 }}</td>
                                    <td>{{ $item->quantity_adults ?? 0 }}</td>
                                    <td>{{ $item->quantity_children ?? 0 }}</td>
                                    <td>{{ $item->quantity_infants ?? 0 }}</td>
                                    <td class="text-end price-cell unit">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format($item->unit_price ?? 0, 2) }}</td>
                                    <td class="text-end price-cell">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format($item->total_price ?? 0, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    @if($travelPointItems->count() > 0)
                    <!-- Travel Point Services -->
                    <h6 class="service-title" style="margin-top: 30px;">Point to Point Transfer Services</h6>
                    <div class="table-responsive">
                        <table class="table invoice-table">
                            <thead>
                                <tr>
                                    <th>Pickup Location</th>
                                    <th>Dropoff Location</th>
                                    <th>Vehicle Name</th>
                                    <th>Pickup Date</th>
                                    <th>Total Persons</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($travelPointItems as $item)
                                @php
                                    $serviceDetails = $item->service_details ?? [];
                                    $pickupDate = $serviceDetails['pickup_date'] ?? '';
                                    $entryTime = $serviceDetails['entrytime'] ?? '';
                                    $pickupDateDisplay = 'N/A';
                                    if ($pickupDate) {
                                        try {
                                            $pickupCarbon = \Carbon\Carbon::parse($pickupDate);
                                            if ($entryTime) {
                                                $timeStr = str_replace([' AM', ' PM', 'am', 'pm'], '', $entryTime);
                                                $timeParts = explode(':', $timeStr);
                                                if (count($timeParts) >= 2) {
                                                    $hour = (int)$timeParts[0];
                                                    $minute = (int)$timeParts[1];
                                                    if (stripos($entryTime, 'PM') !== false && $hour < 12) {
                                                        $hour += 12;
                                                    } elseif (stripos($entryTime, 'AM') !== false && $hour == 12) {
                                                        $hour = 0;
                                                    }
                                                    $pickupCarbon->setTime($hour, $minute);
                                                    $pickupDateDisplay = $pickupCarbon->format('jS M Y, h:i A');
                                                } else {
                                                    $pickupDateDisplay = $pickupCarbon->format('jS M Y');
                                                }
                                            } else {
                                                $pickupDateDisplay = $pickupCarbon->format('jS M Y');
                                            }
                                        } catch (\Exception $e) {
                                            $pickupDateDisplay = 'N/A';
                                        }
                                    }
                                    $totalPersons = $serviceDetails['total_persons'] ?? (($item->quantity_adults ?? 0) + ($item->quantity_children ?? 0) + ($item->quantity_infants ?? 0));
                                @endphp
                                <tr>
                                    <td>{{ $serviceDetails['entrypickup'] ?? 'N/A' }}</td>
                                    <td>{{ $serviceDetails['entrydropoff'] ?? 'N/A' }}</td>
                                    <td>{{ $serviceDetails['vehicle_name'] ?? 'N/A' }}</td>
                                    <td>{{ $pickupDateDisplay }}</td>
                                    <td>{{ $totalPersons }}</td>
                                    <td class="text-end price-cell unit">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format(round($item->unit_price ?? 0), 2) }}</td>
                                    <td class="text-end price-cell">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format(round($item->total_price ?? 0), 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    @if($travelHourlyItems->count() > 0)
                    <!-- Travel Hourly Services -->
                    <h6 class="service-title" style="margin-top: 30px;">Hourly Tour Services</h6>
                    <div class="table-responsive">
                        <table class="table invoice-table">
                            <thead>
                                <tr>
                                    <th>Pickup Location</th>
                                    <th>Dropoff Location</th>
                                    <th>Vehicle Name</th>
                                    <th>Pickup Date</th>
                                    <th>Total Persons</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($travelHourlyItems as $item)
                                @php
                                    $serviceDetails = $item->service_details ?? [];
                                    $pickupDate = $serviceDetails['pickup_date'] ?? '';
                                    $entryTime = $serviceDetails['entrytime'] ?? '';
                                    $pickupDateDisplay = 'N/A';
                                    if ($pickupDate) {
                                        try {
                                            $pickupCarbon = \Carbon\Carbon::parse($pickupDate);
                                            if ($entryTime) {
                                                $timeStr = str_replace([' AM', ' PM', 'am', 'pm'], '', $entryTime);
                                                $timeParts = explode(':', $timeStr);
                                                if (count($timeParts) >= 2) {
                                                    $hour = (int)$timeParts[0];
                                                    $minute = (int)$timeParts[1];
                                                    if (stripos($entryTime, 'PM') !== false && $hour < 12) {
                                                        $hour += 12;
                                                    } elseif (stripos($entryTime, 'AM') !== false && $hour == 12) {
                                                        $hour = 0;
                                                    }
                                                    $pickupCarbon->setTime($hour, $minute);
                                                    $pickupDateDisplay = $pickupCarbon->format('jS M Y, h:i A');
                                                } else {
                                                    $pickupDateDisplay = $pickupCarbon->format('jS M Y');
                                                }
                                            } else {
                                                $pickupDateDisplay = $pickupCarbon->format('jS M Y');
                                            }
                                        } catch (\Exception $e) {
                                            $pickupDateDisplay = 'N/A';
                                        }
                                    }
                                    $totalPersons = $serviceDetails['total_persons'] ?? (($item->quantity_adults ?? 0) + ($item->quantity_children ?? 0) + ($item->quantity_infants ?? 0));
                                @endphp
                                <tr>
                                    <td>{{ $serviceDetails['entrypickup'] ?? 'N/A' }}</td>
                                    <td>{{ $serviceDetails['entrydropoff'] ?? 'N/A' }}</td>
                                    <td>{{ $serviceDetails['vehicle_name'] ?? 'N/A' }}</td>
                                    <td>{{ $pickupDateDisplay }}</td>
                                    <td>{{ $totalPersons }}</td>
                                    <td class="text-end price-cell unit">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format(round($item->unit_price ?? 0), 2) }}</td>
                                    <td class="text-end price-cell">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format(round($item->total_price ?? 0), 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    @if($localTransportItems->count() > 0)
                    <!-- Local Transport Services -->
                    <h6 class="service-title" style="margin-top: 30px;">Local Transport Services</h6>
                    <div class="table-responsive">
                        <table class="table invoice-table">
                            <thead>
                                <tr>
                                    <th>Pickup Location</th>
                                    <th>Dropoff Location</th>
                                    <th>Vehicle Name</th>
                                    <th>Pickup Date</th>
                                    <th>Total Persons</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($localTransportItems as $item)
                                @php
                                    $serviceDetails = $item->service_details ?? [];
                                    $pickupDate = $serviceDetails['pickup_date'] ?? '';
                                    $entryTime = $serviceDetails['entrytime'] ?? '';
                                    $pickupDateDisplay = 'N/A';
                                    if ($pickupDate) {
                                        try {
                                            $pickupCarbon = \Carbon\Carbon::parse($pickupDate);
                                            if ($entryTime) {
                                                $timeStr = str_replace([' AM', ' PM', 'am', 'pm'], '', $entryTime);
                                                $timeParts = explode(':', $timeStr);
                                                if (count($timeParts) >= 2) {
                                                    $hour = (int)$timeParts[0];
                                                    $minute = (int)$timeParts[1];
                                                    if (stripos($entryTime, 'PM') !== false && $hour < 12) {
                                                        $hour += 12;
                                                    } elseif (stripos($entryTime, 'AM') !== false && $hour == 12) {
                                                        $hour = 0;
                                                    }
                                                    $pickupCarbon->setTime($hour, $minute);
                                                    $pickupDateDisplay = $pickupCarbon->format('jS M Y, h:i A');
                                                } else {
                                                    $pickupDateDisplay = $pickupCarbon->format('jS M Y');
                                                }
                                            } else {
                                                $pickupDateDisplay = $pickupCarbon->format('jS M Y');
                                            }
                                        } catch (\Exception $e) {
                                            $pickupDateDisplay = 'N/A';
                                        }
                                    }
                                    $totalPersons = $serviceDetails['total_persons'] ?? (($item->quantity_adults ?? 0) + ($item->quantity_children ?? 0) + ($item->quantity_infants ?? 0));
                                @endphp
                                <tr>
                                    <td>{{ $serviceDetails['entrypickup'] ?? 'N/A' }}</td>
                                    <td>{{ $serviceDetails['entrydropoff'] ?? 'N/A' }}</td>
                                    <td>{{ $serviceDetails['vehicle_name'] ?? 'N/A' }}</td>
                                    <td>{{ $pickupDateDisplay }}</td>
                                    <td>{{ $totalPersons }}</td>
                                    <td class="text-end price-cell unit">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format(round($item->unit_price ?? 0), 2) }}</td>
                                    <td class="text-end price-cell">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format(round($item->total_price ?? 0), 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    @if($exitPortItems->count() > 0)
                    <!-- Departure Services -->
                    <h6 class="service-title" style="margin-top: 30px;">Departure Services</h6>
                    <div class="table-responsive">
                        <table class="table invoice-table">
                            <thead>
                                <tr>
                                    <th>Exit Pickup</th>
                                    <th>Exit Dropoff</th>
                                    <th>Vehicle Name</th>
                                    <th>Type</th>
                                    <th>Exit Pickup Date</th>
                                    <th>Total Persons</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($exitPortItems as $item)
                                @php
                                    $serviceDetails = $item->service_details ?? [];
                                    $exitPickupDate = $serviceDetails['exitpickupdate'] ?? '';
                                    $entryTime = $serviceDetails['entrytime'] ?? '';
                                    $exitPickupDateDisplay = 'N/A';
                                    if ($exitPickupDate) {
                                        try {
                                            $exitPickupCarbon = \Carbon\Carbon::parse($exitPickupDate);
                                            if ($entryTime) {
                                                $timeStr = str_replace([' AM', ' PM', 'am', 'pm'], '', $entryTime);
                                                $timeParts = explode(':', $timeStr);
                                                if (count($timeParts) >= 2) {
                                                    $hour = (int)$timeParts[0];
                                                    $minute = (int)$timeParts[1];
                                                    if (stripos($entryTime, 'PM') !== false && $hour < 12) {
                                                        $hour += 12;
                                                    } elseif (stripos($entryTime, 'AM') !== false && $hour == 12) {
                                                        $hour = 0;
                                                    }
                                                    $exitPickupCarbon->setTime($hour, $minute);
                                                    $exitPickupDateDisplay = $exitPickupCarbon->format('jS M Y, h:i A');
                                                } else {
                                                    $exitPickupDateDisplay = $exitPickupCarbon->format('jS M Y');
                                                }
                                            } else {
                                                $exitPickupDateDisplay = $exitPickupCarbon->format('jS M Y');
                                            }
                                        } catch (\Exception $e) {
                                            $exitPickupDateDisplay = 'N/A';
                                        }
                                    }
                                    $totalPersons = $serviceDetails['total_persons'] ?? (($item->quantity_adults ?? 0) + ($item->quantity_children ?? 0) + ($item->quantity_infants ?? 0));
                                @endphp
                                <tr>
                                    <td>{{ $serviceDetails['exitpickup'] ?? 'N/A' }}</td>
                                    <td>{{ $serviceDetails['exitdropoff'] ?? 'N/A' }}</td>
                                    <td>{{ $serviceDetails['vehicle_name'] ?? 'N/A' }}</td>
                                    <td>{{ $serviceDetails['vehicle_type'] ?? 'N/A' }}</td>
                                    <td>{{ $exitPickupDateDisplay }}</td>
                                    <td>{{ $totalPersons }}</td>
                                    <td class="text-end price-cell unit">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format($item->unit_price ?? 0, 2) }}</td>
                                    <td class="text-end price-cell">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format($item->total_price ?? 0, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    @if($otherItems->count() > 0)
                    <!-- Other Services -->
                    <h6 class="service-title" style="margin-top: 30px;">Other Services</h6>
                    <div class="table-responsive">
                        <table class="table invoice-table">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($otherItems as $item)
                                <tr>
                                    <td>{{ $item->description ?? 'N/A' }}</td>
                                    <td class="text-end price-cell unit">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format($item->unit_price ?? 0, 2) }}</td>
                                    <td class="text-end price-cell">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format($item->total_price ?? 0, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    @if($invoice->items->count() === 0)
                    <div class="alert alert-info" style="border-radius: 8px;">
                        <i class="ri-information-line me-2"></i>No items found in this invoice.
                    </div>
                    @endif

                    <!-- Price Summary -->
                    <div class="summary-box">
                        <h5 class="mb-4" style="color: #495057; font-weight: 700;">
                            <i class="ri-calculator-line me-2"></i>Price Summary
                        </h5>
                        @php
                            // Prefer values computed by service (includes newly added services)
                            $notes = is_string($invoice->notes) ? json_decode($invoice->notes, true) : ($invoice->notes ?? []);
                            $ordersTotal = $notes['orders_total'] ?? null;
                            $baseAmount = $notes['base_amount'] ?? null;
                            $actualAmount = $ordersTotal !== null ? $ordersTotal : $invoice->items->sum('total_price');
                            if ($baseAmount === null) {
                                $neg = $invoice->getNegotiatedAmount();
                                $baseAmount = $neg ?? $actualAmount;
                            }
                            $negotiatedAmount = $baseAmount; // negotiated amount adjusted with added services
                            $discount = $actualAmount - $baseAmount;
                            
                            $tour = $invoice->tour;
                            $tourStatus = $tour->tour_status ?? '';
                            $statusesWithTax = ['Confirmed', 'Definite', 'Actual'];
                            $shouldShowTax = in_array($tourStatus, $statusesWithTax);
                            
                            $taxBreakdown = $notes['tax_breakdown'] ?? [];
                            $gstAmount = $invoice->gst_amount ?? 0;
                            $finalPrice = $baseAmount + $gstAmount;
                            $paymentReceived = $invoice->payment_received ?? 0;
                            $outstandingBalance = $invoice->outstanding_balance ?? $finalPrice;
                        @endphp
                        <div class="summary-row">
                            <span class="summary-label">Total (Actual Amount):</span>
                            <span class="summary-value">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format(round($actualAmount)) }}</span>
                        </div>
                        @if($negotiatedAmount !== null)
                        <div class="summary-row" style="background: #e7f3ff; padding: 15px; border-radius: 6px; margin: 10px 0;">
                            <span class="summary-label">Last Negotiated Amount:</span>
                            <span class="summary-value" style="color: #0056b3;">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format(round($negotiatedAmount)) }}</span>
                        </div>
                        @if($discount > 0)
                        <div class="summary-row" style="background: #d4edda; padding: 15px; border-radius: 6px; margin: 10px 0;">
                            <span class="summary-label">Discount:</span>
                            <span class="summary-value" style="color: #155724;">-{{ $invoice->base_currency ?? 'SGD' }} {{ number_format(round($discount)) }}</span>
                        </div>
                        @elseif($discount < 0)
                        <div class="summary-row" style="background: #fff3cd; padding: 15px; border-radius: 6px; margin: 10px 0;">
                            <span class="summary-label">Additional Charges:</span>
                            <span class="summary-value" style="color: #856404;">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format(round(abs($discount))) }}</span>
                        </div>
                        @endif
                        @endif
                        
                        @if($shouldShowTax && $gstAmount > 0)
                        @if(!empty($taxBreakdown))
                            @foreach($taxBreakdown as $taxName => $taxValue)
                            <div class="summary-row" style="background: #fff3cd; padding: 15px; border-radius: 6px; margin: 10px 0;">
                                <span class="summary-label">{{ $taxName }}:</span>
                                <span class="summary-value" style="color: #856404;">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format(round($taxValue)) }}</span>
                            </div>
                            @endforeach
                        @else
                        <div class="summary-row" style="background: #fff3cd; padding: 15px; border-radius: 6px; margin: 10px 0;">
                            <span class="summary-label">Total Vat / GST Tax:</span>
                            <span class="summary-value" style="color: #856404;">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format(round($gstAmount)) }}</span>
                        </div>
                        @endif
                        @endif
                        
                        <div class="summary-row" style="background: #d4edda; padding: 20px; border-radius: 6px; margin-top: 15px; border-top: 3px solid #28a745;">
                            <span class="summary-label" style="font-size: 18px; font-weight: 700;">Final Price:</span>
                            <span class="summary-value highlight">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format(round($finalPrice)) }}</span>
                        </div>
                        
                        @if($shouldShowTax)
                        <div class="summary-row" style="background: #d1ecf1; padding: 15px; border-radius: 6px; margin: 10px 0;">
                            <span class="summary-label">Payment Received:</span>
                            <span class="summary-value info">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format(round($paymentReceived)) }}</span>
                        </div>
                        <div class="summary-row" style="background: #f8d7da; padding: 20px; border-radius: 6px; margin-top: 15px; border-top: 3px solid #dc3545;">
                            <span class="summary-label" style="font-size: 18px; font-weight: 700;">Outstanding Balance:</span>
                            <span class="summary-value danger" style="font-size: 24px;">{{ $invoice->base_currency ?? 'SGD' }} {{ number_format(round($outstandingBalance)) }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Currency Conversion -->
                @if($invoice->currency_conversion)
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="info-section" style="border-left-color: #6f42c1;">
                            <h5 style="color: #6f42c1;"><i class="ri-exchange-dollar-line me-2"></i>Currency Conversion</h5>
                            <div class="row">
                                @php
                                    $currencyConversion = $invoice->currency_conversion ?? [];
                                    $outstandingBalanceForCurrency = $invoice->outstanding_balance ?? 0;
                                @endphp
                                @if(isset($currencyConversion['USD']))
                                <div class="col-md-4 mb-3">
                                    <div class="currency-card">
                                        <div class="currency-label">USD</div>
                                        <div class="currency-amount">{{ number_format(round($currencyConversion['USD'] ?? 0)) }}</div>
                                    </div>
                                </div>
                                @endif
                                @if(isset($currencyConversion['SGD']))
                                <div class="col-md-4 mb-3">
                                    <div class="currency-card">
                                        <div class="currency-label">SGD</div>
                                        <div class="currency-amount">{{ number_format(round($currencyConversion['SGD'] ?? $outstandingBalanceForCurrency)) }}</div>
                                    </div>
                                </div>
                                @endif
                                @if(isset($currencyConversion['INR']))
                                <div class="col-md-4 mb-3">
                                    <div class="currency-card">
                                        <div class="currency-label">INR</div>
                                        <div class="currency-amount">{{ number_format(round($currencyConversion['INR'] ?? 0)) }}</div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Payment Terms and Bank Details -->
                @php
                    // Fetch bank details from database based on tour's dmc_id
                    $tour = $invoice->tour;
                    $dmcId = $tour->dmc_id ?? $invoice->dmc_id ?? null;
                    $bankDetail = null;
                    $paymentTerms = [];
                    $bankDetailsData = [];
                    
                    if ($dmcId) {
                        // Fetch active bank details for this DMC
                        $bankDetail = \App\Models\BankDetail::where('dmc_id', $dmcId)
                            ->where('is_active', 1)
                            ->whereNull('deleted_at')
                            ->first();
                    }
                    
                    // Use database bank details if found, otherwise fall back to invoice stored data
                    if ($bankDetail) {
                        $paymentTerms = $bankDetail->payment_terms ?? [];
                        $bankDetailsData = [
                            'account_name' => $bankDetail->account_name ?? '',
                            'account_number' => $bankDetail->account_number ?? '',
                            'bank_address' => $bankDetail->bank_address ?? '',
                            'ifsc_code' => $bankDetail->ifsc ?? null,
                            'swift_bic_iban' => $bankDetail->swift_bic_iban ?? null,
                            'bank_code' => $bankDetail->bank_code ?? null,
                            'branch_code' => $bankDetail->branch_code ?? null,
                            'aba_routing_number' => $bankDetail->aba_routing ?? null,
                        ];
                    } else {
                        // Fallback to invoice stored data
                        $paymentTerms = $invoice->payment_terms ?? [];
                        $bankDetailsData = $invoice->bank_details ?? [];
                    }
                    
                    // If no payment terms found, use default
                    if (empty($paymentTerms)) {
                        $dmcCompanyName = $invoice->dmc->company_name ?? 'DMC';
                        $dmcEmail = $invoice->dmc->email ?? 'dmc email';
                        $paymentTerms = [
                            'Please pay the amount before the payment due date to avoid auto release of booking. Bank details are mentioned below.',
                            'Payment/Remittance to be made in the currency as stated in the invoice.',
                            'Bank charges to be borne by remitter. Please ensure that ' . $dmcCompanyName . ' receive full payment as per Invoice.',
                            'To ensure prompt credit, please send payment details along with remittance advice at ' . $dmcEmail . '.',
                            'Interest @ 18% will be charged on all overdues.',
                            'This document is not a voucher. Voucher will be issued after the receipt of full monies & necessary documents.',
                        ];
                    }
                @endphp

                @if(!empty($paymentTerms))
                <div class="card mb-4" style="border-left: 4px solid #FFC0CB; margin-top: 20px;">
                    <div class="card-header" style="background-color: #FFC0CB;">
                        <h5 class="mb-0"><strong>Payment Terms :</strong></h5>
                    </div>
                    <div class="card-body" style="background-color: #FFC0CB;">
                        <ol style="margin-left: 20px; margin-top: 10px;">
                            @foreach($paymentTerms as $term)
                            <li style="margin-bottom: 8px;">{{ $term }}</li>
                            @endforeach
                        </ol>
                    </div>
                </div>
                @endif

                @if(!empty($bankDetailsData))
                <div class="card mb-4" style="border-left: 4px solid #FFC0CB;">
                    <div class="card-header" style="background-color: #FFC0CB;">
                        <h5 class="mb-0"><strong>Bank Details:</strong></h5>
                    </div>
                    <div class="card-body" style="background-color: #FFC0CB;">
                        <div class="table-responsive">
                            <table class="table table-bordered" style="background-color: white;">
                                <tbody>
                                    <tr>
                                        <td style="width: 40%; font-weight: 600;">Account Name</td>
                                        <td>{{ $bankDetailsData['account_name'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: 600;">Account Number</td>
                                        <td>{{ $bankDetailsData['account_number'] ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: 600;">Bank Address</td>
                                        <td>{{ $bankDetailsData['bank_address'] ?? '' }}</td>
                                    </tr>
                                    @if(!empty($bankDetailsData['ifsc_code']))
                                    <tr>
                                        <td style="font-weight: 600;">IFSC (For India only)</td>
                                        <td>{{ $bankDetailsData['ifsc_code'] }}</td>
                                    </tr>
                                    @endif
                                    @if(!empty($bankDetailsData['swift_bic_iban']))
                                    <tr>
                                        <td style="font-weight: 600;">SWIFT / BIC / IBAN Code (as applicable for international, Europe transfers)</td>
                                        <td>{{ $bankDetailsData['swift_bic_iban'] }}</td>
                                    </tr>
                                    @endif
                                    @if(!empty($bankDetailsData['bank_code']))
                                    <tr>
                                        <td style="font-weight: 600;">Bank Code (For Singapore)</td>
                                        <td>{{ $bankDetailsData['bank_code'] }}</td>
                                    </tr>
                                    @endif
                                    @if(!empty($bankDetailsData['branch_code']))
                                    <tr>
                                        <td style="font-weight: 600;">Branch Code (For Singapore)</td>
                                        <td>{{ $bankDetailsData['branch_code'] }}</td>
                                    </tr>
                                    @endif
                                    @if(!empty($bankDetailsData['aba_routing_number']))
                                    <tr>
                                        <td style="font-weight: 600;">ABA / Routing Number (For USA only)</td>
                                        <td>{{ $bankDetailsData['aba_routing_number'] }}</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Action Buttons for Definite Status -->
            @if($invoice->invoice_type === 'proforma' && $invoice->tour->tour_status === 'Definite')
            <div class="card mb-4" style="border-left: 4px solid #ffc107;">
                <div class="card-header bg-warning">
                    <h5 class="mb-0 text-white"><i class="ri-file-edit-line me-2"></i>Convert to Final Invoice</h5>
                </div>
                <div class="card-body">
                    <p>This tour is now in <strong>Definite</strong> status. You can convert this proforma invoice to a final invoice (Tax Invoice).</p>
                    <form action="{{ route('invoices.convert-to-final', Crypt::encrypt($invoice->invoice_id)) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" 
                                class="btn btn-warning btn-lg"
                                onclick="return confirm('Are you sure you want to convert this proforma invoice to final invoice? This action cannot be undone and GST will be applied.');">
                            <i class="ri-file-edit-line me-1"></i> Convert to Final Invoice
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
