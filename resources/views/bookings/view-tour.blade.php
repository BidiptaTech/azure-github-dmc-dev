@extends('layouts.layout')
@section('title', 'Tour Details - ' . $tour->display_id)
@extends('layouts.datatablecss')

@section('content')
@php
    $adultCount  = (int) ($tour->adult ?? 0);
    $childCount  = (int) ($tour->child ?? 0);
    $infantCount = (int) ($tour->infant ?? 0);
    $totalGuests = $adultCount + $childCount + $infantCount;

    $statusTone = match ($tour->tour_status) {
        'Actual' => 'success',
        'Confirmed' => 'primary',
        'Definite' => 'info',
        'Prospect', 'Tentative' => 'warning',
        'Refunded', 'Refund - Pending' => 'olive',
        'Cancelled' => 'danger',
        default => 'secondary',
    };

    $checkIn = $tour->check_in_time ? \Carbon\Carbon::parse($tour->check_in_time) : null;
    $checkOut = $tour->check_out_time ? \Carbon\Carbon::parse($tour->check_out_time) : null;
    $durationDays = ($checkIn && $checkOut) ? ($checkIn->diffInDays($checkOut) + 1) : null;
    $daysUntilStart = $checkIn ? (int) floor($checkIn->floatDiffInDays(now(), false)) : null;

    $mainGuest = $tour->mainguest;
    if (is_string($mainGuest)) {
        $mainGuest = json_decode($mainGuest, true) ?: [];
    }
    if (! is_array($mainGuest)) {
        $mainGuest = [];
    }
    $leadGuestName = trim(implode(' ', array_filter([
        $mainGuest['title'] ?? null,
        $mainGuest['first_name'] ?? $mainGuest['firstname'] ?? $mainGuest['name'] ?? null,
        $mainGuest['last_name'] ?? $mainGuest['lastname'] ?? null,
    ])));
    if ($leadGuestName === '' && ! empty($tour->customer_name)) {
        $leadGuestName = $tour->customer_name;
    }

    $agentName = $tour->agent->name ?? $tour->agent_name ?? null;
    $agentCompany = $tour->agent->company_name ?? $tour->agent_company_name ?? null;

    $tourOrders = $tourOrders ?? collect();
    $activeServices = [];
    $removedServices = [];
    foreach ($tourOrders as $ord) {
        $raw = $ord->data;
        $item = is_array($raw) && isset($raw[0]) ? $raw[0] : (is_array($raw) ? $raw : []);
        if (! is_array($item)) {
            $item = [];
        }
        $type = (string) ($ord->type ?? '');
        $name = match ($type) {
            'hotel' => $item['hotelDetails']['hotel_name'] ?? $item['hotel_name'] ?? null,
            'attraction' => $item['AttractionName'] ?? $item['attraction_name'] ?? null,
            'restaurant' => $item['restaurantName'] ?? $item['restaurant_name'] ?? null,
            'guide' => $item['guide_name'] ?? $item['guideName'] ?? null,
            'entry_port', 'exit_port', 'travel_hourly', 'travel_point', 'local_transport'
                => $item['vehicles_name'] ?? $item['vehicle_name'] ?? $item['travel_type'] ?? $type,
            default => $item['name'] ?? $item['title'] ?? $item['service_name'] ?? ucfirst(str_replace('_', ' ', $type ?: 'service')),
        };
        $label = match ($type) {
            'hotel' => 'Hotel',
            'attraction' => 'Attraction',
            'restaurant' => 'Restaurant',
            'guide' => 'Guide',
            'entry_port' => 'Entry Transfer',
            'exit_port' => 'Exit Transfer',
            'travel_hourly' => 'Hourly Transport',
            'travel_point' => 'Point to Point',
            'local_transport' => 'Local Transfer',
            default => ucfirst(str_replace('_', ' ', $type ?: 'Service')),
        };
        $row = [
            'id' => $ord->id,
            'type' => $type,
            'label' => $label,
            'name' => $name ?: $label,
            'currency' => strtoupper(trim((string) ($ord->currency ?? ''))),
            'is_refund' => (int) ($ord->is_refund ?? 0) === 1,
            'is_verify' => (int) ($ord->is_verify ?? 0),
            'refunded' => (bool) ($ord->refunded ?? false),
            'refund_amount' => $ord->refund_amount,
            'deleted_at' => $ord->deleted_at,
            'cancel_reason' => $ord->cancel_reason ?? null,
        ];
        if ($ord->deleted_at) {
            $removedServices[] = $row;
        } else {
            $activeServices[] = $row;
        }
    }

    $trackDetails = [];
    if (! empty($tour->track_details)) {
        $decoded = is_array($tour->track_details)
            ? $tour->track_details
            : json_decode($tour->track_details, true);
        if (is_array($decoded)) {
            $trackDetails = $decoded;
        }
    }
    if (! empty($trackDetails)) {
        usort($trackDetails, function ($a, $b) {
            $ad = $a['date'] ?? null;
            $bd = $b['date'] ?? null;
            if ($ad === $bd) {
                return 0;
            }
            if (! $ad) {
                return -1;
            }
            if (! $bd) {
                return 1;
            }

            return strtotime($ad) <=> strtotime($bd);
        });
    }

    if (! function_exists('vtStatusColor')) {
        function vtStatusColor($status)
        {
            return match (true) {
                str_contains((string) $status, 'Actual') => 'success',
                str_contains((string) $status, 'Confirmed') => 'primary',
                str_contains((string) $status, 'Definite') => 'info',
                str_contains((string) $status, 'Prospect') || str_contains((string) $status, 'Tentative') => 'warning',
                str_contains((string) $status, 'Refund') => 'olive',
                str_contains((string) $status, 'Cancel') => 'danger',
                default => 'secondary',
            };
        }
    }
    if (! function_exists('vtStatusIcon')) {
        function vtStatusIcon($status)
        {
            return match (true) {
                str_contains((string) $status, 'Actual') => 'ri-check-double-line',
                str_contains((string) $status, 'Confirmed') => 'ri-checkbox-circle-line',
                str_contains((string) $status, 'Definite') => 'ri-checkbox-line',
                str_contains((string) $status, 'Prospect') || str_contains((string) $status, 'Tentative') => 'ri-question-line',
                str_contains((string) $status, 'Cancel') => 'ri-close-circle-line',
                str_contains((string) $status, 'Refund') => 'ri-refund-2-line',
                default => 'ri-record-circle-line',
            };
        }
    }

    $trackCreatedBy = $trackDetails[0]['changed_by_name'] ?? null;
    $trackLastUpdatedBy = ! empty($trackDetails)
        ? ($trackDetails[count($trackDetails) - 1]['changed_by_name'] ?? null)
        : null;
@endphp

<div class="container-xxl flex-grow-1 container-p-y vt-page">
    {{-- Page header --}}
    <div class="vt-header">
        <div class="vt-header-main">
            <nav class="vt-breadcrumb" aria-label="breadcrumb">
                <a href="javascript:history.back()">Bookings</a>
                <span>/</span>
                <span>Tour Details</span>
            </nav>
            <div class="vt-title-row">
                <h1 class="vt-title">{{ $tour->display_id }}</h1>
                <span class="vt-status vt-status-{{ $statusTone }}">{{ $tour->tour_status }}</span>
                @if(!empty($tour->is_pro))
                    <span class="vt-chip">Pro</span>
                @endif
            </div>
            <div class="vt-meta-row">
                <span><i class="ri-hashtag"></i> Tour #{{ $tour->tour_id }}</span>
                @if($tour->reference_id)
                    <span><i class="ri-bookmark-line"></i> Ref {{ $tour->reference_id }}</span>
                @endif
                @if($tour->multi_enq_id)
                    <span><i class="ri-links-line"></i> Multi {{ $tour->multi_enq_id }}</span>
                @endif
                <span><i class="ri-map-pin-line"></i> {{ $tour->destination ?? 'Destination N/A' }}{{ $tour->city ? ' · '.$tour->city : '' }}</span>
                @if($checkIn && $checkOut)
                    <span><i class="ri-calendar-line"></i> {{ $checkIn->format('d M Y') }} – {{ $checkOut->format('d M Y') }}</span>
                @endif
            </div>
        </div>
        <div class="vt-header-actions">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="history.back()">
                <i class="ri-arrow-left-line"></i> Back
            </button>
            <button type="button" class="btn btn-sm btn-dark" onclick="printTour()">
                <i class="ri-printer-line"></i> Print
            </button>
        </div>
    </div>

    {{-- KPI strip --}}
    <div class="vt-kpi-strip">
        <div class="vt-kpi">
            <span class="vt-kpi-label">Duration</span>
            <span class="vt-kpi-value">{{ $durationDays !== null ? $durationDays.' days' : '—' }}</span>
        </div>
        <div class="vt-kpi">
            <span class="vt-kpi-label">{{ ($daysUntilStart !== null && $daysUntilStart < 0) ? 'Starts in' : 'Since start' }}</span>
            <span class="vt-kpi-value">
                @if($daysUntilStart === null)
                    —
                @else
                    {{ abs($daysUntilStart) }} {{ abs($daysUntilStart) === 1 ? 'day' : 'days' }}
                @endif
            </span>
        </div>
        <div class="vt-kpi">
            <span class="vt-kpi-label">Guests</span>
            <span class="vt-kpi-value">{{ $totalGuests }}</span>
            <span class="vt-kpi-sub">A {{ $adultCount }} · C {{ $childCount }} · I {{ $infantCount }}</span>
        </div>
        <div class="vt-kpi">
            <span class="vt-kpi-label">Services</span>
            <span class="vt-kpi-value">{{ count($activeServices) }}</span>
            <span class="vt-kpi-sub">{{ count($removedServices) }} removed / refund</span>
        </div>
        <div class="vt-kpi">
            <span class="vt-kpi-label">Currency</span>
            <span class="vt-kpi-value">{{ strtoupper((string) ($tour->user_currency ?? '—')) }}</span>
        </div>
        <div class="vt-kpi">
            <span class="vt-kpi-label">Last updated</span>
            <span class="vt-kpi-value vt-kpi-value-sm">{{ $tour->updated_at?->format('d M Y, H:i') ?? '—' }}</span>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            {{-- Overview --}}
            <section class="vt-panel mb-3">
                <div class="vt-panel-head">
                    <h2>Tour overview</h2>
                </div>
                <div class="vt-panel-body">
                    <div class="row g-0 vt-fact-grid">
                        <div class="col-md-6">
                            <h3 class="vt-section-label">Booking</h3>
                            <dl class="vt-dl">
                                <div><dt>Display ID</dt><dd>{{ $tour->display_id }}</dd></div>
                                <div><dt>Tour ID</dt><dd>#{{ $tour->tour_id }}</dd></div>
                                @if($tour->reference_id)
                                    <div><dt>Reference</dt><dd>{{ $tour->reference_id }}</dd></div>
                                @endif
                                @if($tour->multi_enq_id)
                                    <div><dt>Multi enquiry</dt><dd>{{ $tour->multi_enq_id }}</dd></div>
                                @endif
                                <div><dt>Status</dt><dd><span class="vt-status vt-status-{{ $statusTone }}">{{ $tour->tour_status }}</span></dd></div>
                                <div><dt>Tour type</dt><dd>{{ $tour->tour_type ?: '—' }}</dd></div>
                                <div><dt>Created</dt><dd>{{ $tour->created_at?->format('d M Y, H:i') }}@if($trackCreatedBy) <span class="text-muted">· {{ $trackCreatedBy }}</span>@endif</dd></div>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <h3 class="vt-section-label">Destination & stay</h3>
                            <dl class="vt-dl">
                                <div><dt>Country</dt><dd>{{ $tour->destination ?? '—' }}</dd></div>
                                <div><dt>City</dt><dd>{{ $tour->city ?? '—' }}</dd></div>
                                <div><dt>Check-in</dt><dd>{{ $checkIn ? $checkIn->format('D, d M Y · h:i A') : '—' }}</dd></div>
                                <div><dt>Check-out</dt><dd>{{ $checkOut ? $checkOut->format('D, d M Y · h:i A') : '—' }}</dd></div>
                                <div><dt>Duration</dt><dd>{{ $durationDays !== null ? $durationDays.' days' : '—' }}</dd></div>
                            </dl>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Parties --}}
            <section class="vt-panel mb-3">
                <div class="vt-panel-head">
                    <h2>Parties & guests</h2>
                </div>
                <div class="vt-panel-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <h3 class="vt-section-label">Lead guest</h3>
                            <p class="vt-strong mb-1">{{ $leadGuestName !== '' ? $leadGuestName : 'Not provided' }}</p>
                            @if(!empty($mainGuest['email']))
                                <p class="vt-muted mb-0"><i class="ri-mail-line"></i> {{ $mainGuest['email'] }}</p>
                            @endif
                            @if(!empty($mainGuest['phone']) || !empty($mainGuest['mobile']))
                                <p class="vt-muted mb-0"><i class="ri-phone-line"></i> {{ $mainGuest['phone'] ?? $mainGuest['mobile'] }}</p>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <h3 class="vt-section-label">Agent</h3>
                            <p class="vt-strong mb-1">{{ $agentName ?: '—' }}</p>
                            @if($agentCompany)
                                <p class="vt-muted mb-0">{{ $agentCompany }}</p>
                            @endif
                            @if(!empty($tour->agent?->email))
                                <p class="vt-muted mb-0"><i class="ri-mail-line"></i> {{ $tour->agent->email }}</p>
                            @endif
                            @if(!empty($tour->agent?->phone))
                                <p class="vt-muted mb-0"><i class="ri-phone-line"></i> {{ $tour->agent->phone }}</p>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <h3 class="vt-section-label">Guest mix</h3>
                            <div class="vt-guest-bars">
                                <div class="vt-guest-bar">
                                    <span>Adults</span>
                                    <strong>{{ $adultCount }}</strong>
                                </div>
                                <div class="vt-guest-bar">
                                    <span>Children</span>
                                    <strong>{{ $childCount }}</strong>
                                </div>
                                <div class="vt-guest-bar">
                                    <span>Infants</span>
                                    <strong>{{ $infantCount }}</strong>
                                </div>
                                <div class="vt-guest-bar vt-guest-bar-total">
                                    <span>Total</span>
                                    <strong>{{ $totalGuests }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Active services --}}
            <section class="vt-panel mb-3">
                <div class="vt-panel-head">
                    <h2>Active services</h2>
                    <span class="vt-count">{{ count($activeServices) }}</span>
                </div>
                <div class="vt-panel-body p-0">
                    @if(count($activeServices) > 0)
                        <div class="table-responsive">
                            <table class="table vt-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Service</th>
                                        <th>Currency</th>
                                        <th>Order</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activeServices as $svc)
                                        <tr>
                                            <td><span class="vt-type">{{ $svc['label'] }}</span></td>
                                            <td class="fw-medium">{{ $svc['name'] }}</td>
                                            <td>{{ $svc['currency'] ?: '—' }}</td>
                                            <td class="text-muted">#{{ $svc['id'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="vt-empty">No active services on this tour.</div>
                    @endif
                </div>
            </section>

            {{-- Removed / refund services --}}
            @if(count($removedServices) > 0)
            <section class="vt-panel mb-3">
                <div class="vt-panel-head">
                    <h2>Removed & refund services</h2>
                    <span class="vt-count">{{ count($removedServices) }}</span>
                </div>
                <div class="vt-panel-body p-0">
                    <div class="table-responsive">
                        <table class="table vt-table mb-0">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Service</th>
                                    <th>Removed</th>
                                    <th>Refund status</th>
                                    <th class="text-end">Refund amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($removedServices as $svc)
                                    @php
                                        $refundLabel = 'Queued';
                                        $refundTone = 'secondary';
                                        if ($svc['refunded'] || $svc['is_verify'] === 1) {
                                            $refundLabel = 'Refunded';
                                            $refundTone = 'success';
                                        } elseif ($svc['is_verify'] === 2) {
                                            $refundLabel = 'Refund pending';
                                            $refundTone = 'warning';
                                        } elseif ($svc['is_refund']) {
                                            $refundLabel = 'Awaiting initiate';
                                            $refundTone = 'olive';
                                        }
                                    @endphp
                                    <tr>
                                        <td><span class="vt-type">{{ $svc['label'] }}</span></td>
                                        <td>
                                            <div class="fw-medium">{{ $svc['name'] }}</div>
                                            @if(!empty($svc['cancel_reason']))
                                                <div class="vt-muted small">{{ $svc['cancel_reason'] }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $svc['deleted_at'] ? \Carbon\Carbon::parse($svc['deleted_at'])->format('d M Y, H:i') : '—' }}</td>
                                        <td><span class="vt-status vt-status-{{ $refundTone }}">{{ $refundLabel }}</span></td>
                                        <td class="text-end">
                                            @if($svc['refund_amount'] !== null && $svc['refund_amount'] !== '')
                                                {{ number_format((float) $svc['refund_amount'], 2) }}
                                                {{ $svc['currency'] }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
            @endif

            {{-- Payments --}}
            @if(!empty($tour->parsed_payment_details) && is_array($tour->parsed_payment_details))
            <section class="vt-panel mb-3">
                <div class="vt-panel-head">
                    <h2>Payment details</h2>
                    <span class="vt-count">{{ count($tour->parsed_payment_details) }}</span>
                </div>
                <div class="vt-panel-body p-0">
                    @php $totalPaid = 0; @endphp
                    <div class="table-responsive">
                        <table class="table vt-table mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Transaction</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tour->parsed_payment_details as $index => $payment)
                                    @php
                                        $amt = floatval($payment['amount'] ?? 0);
                                        $totalPaid += $amt;
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $payment['payment_date'] ?? '—' }}</td>
                                        <td>{{ ucfirst($payment['payment_type'] ?? 'N/A') }}</td>
                                        <td>
                                            <span class="vt-status vt-status-{{ ($payment['status'] ?? 0) == 1 ? 'success' : 'warning' }}">
                                                {{ ($payment['status'] ?? 0) == 1 ? 'Confirmed' : 'Pending' }}
                                            </span>
                                        </td>
                                        <td class="text-muted">{{ $payment['transaction_id'] ?? '—' }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($amt, 2) }}</td>
                                    </tr>
                                    @if(!empty($payment['remarks']))
                                        <tr class="vt-subrow">
                                            <td></td>
                                            <td colspan="5" class="vt-muted">Remarks: {{ $payment['remarks'] }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-end fw-semibold">Total received</td>
                                    <td class="text-end fw-bold">{{ number_format($totalPaid, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </section>
            @endif

            {{-- Key dates --}}
            <section class="vt-panel mb-3">
                <div class="vt-panel-head">
                    <h2>Key dates</h2>
                </div>
                <div class="vt-panel-body">
                    <div class="vt-key-dates">
                        <div class="vt-key-date">
                            <span class="vt-key-dot"></span>
                            <div>
                                <strong>Tour created</strong>
                                <div class="vt-muted">{{ $tour->created_at->format('D, d M Y · h:i A') }} · {{ $tour->created_at->diffForHumans() }}</div>
                                @if($trackCreatedBy)
                                    <div class="vt-muted">By {{ $trackCreatedBy }}</div>
                                @endif
                            </div>
                        </div>
                        @if($tour->updated_at != $tour->created_at)
                        <div class="vt-key-date">
                            <span class="vt-key-dot"></span>
                            <div>
                                <strong>Last updated</strong>
                                <div class="vt-muted">{{ $tour->updated_at->format('D, d M Y · h:i A') }} · {{ $tour->updated_at->diffForHumans() }}</div>
                                @if($trackLastUpdatedBy)
                                    <div class="vt-muted">By {{ $trackLastUpdatedBy }}</div>
                                @endif
                            </div>
                        </div>
                        @endif
                        @if($checkIn)
                        <div class="vt-key-date">
                            <span class="vt-key-dot {{ $checkIn->isPast() ? 'is-done' : '' }}"></span>
                            <div>
                                <strong>{{ $checkIn->isPast() ? 'Tour started' : 'Tour start' }}</strong>
                                <div class="vt-muted">{{ $checkIn->format('D, d M Y · h:i A') }} · {{ $checkIn->diffForHumans() }}</div>
                            </div>
                        </div>
                        @endif
                        @if($checkOut)
                        <div class="vt-key-date">
                            <span class="vt-key-dot {{ $checkOut->isPast() ? 'is-done' : '' }}"></span>
                            <div>
                                <strong>{{ $checkOut->isPast() ? 'Tour completed' : 'Tour end' }}</strong>
                                <div class="vt-muted">{{ $checkOut->format('D, d M Y · h:i A') }} · {{ $checkOut->diffForHumans() }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </section>
        </div>

        {{-- Status history --}}
        <div class="col-xl-4">
            <section class="vt-panel vt-history-panel">
                <div class="vt-panel-head">
                    <div>
                        <h2>Status history</h2>
                        <p class="vt-panel-sub mb-0">Full lifecycle including service refunds</p>
                    </div>
                    <span class="vt-status vt-status-{{ $statusTone }}">{{ $tour->tour_status }}</span>
                </div>
                <div class="vt-panel-body vt-history-body">
                    <ol class="vt-audit">
                        <li class="vt-audit-item">
                            <div class="vt-audit-rail"><span class="vt-audit-node"></span></div>
                            <div class="vt-audit-content">
                                <div class="vt-audit-top">
                                    <strong>Tour created</strong>
                                    <time>{{ $tour->created_at->format('d M Y, H:i') }}</time>
                                </div>
                                <div class="vt-muted">{{ $tour->created_at->diffForHumans() }}</div>
                            </div>
                        </li>

                        @forelse($trackDetails as $item)
                            @php
                                $from = $item['from'] ?? null;
                                $to = $item['to'] ?? null;
                                $dateString = $item['date'] ?? null;
                                $date = $dateString ? \Carbon\Carbon::parse($dateString) : null;
                                $amount = $item['amount'] ?? null;
                                $comment = $item['comment'] ?? null;
                                $actualAmount = $item['actual_amount'] ?? null;
                                $offers = (isset($item['offers']) && is_array($item['offers'])) ? $item['offers'] : [];
                                $hasOffers = ! empty($offers);
                                $confirmCurrency = ! empty($item['confirm_currency']) ? strtoupper(trim((string) $item['confirm_currency'])) : null;
                                $changedByName = $item['changed_by_name'] ?? null;
                                $action = $item['action'] ?? null;
                                $Amount = $item['sgd_amount'] ?? null;
                                $selectedCurrency = $item['selected_currency'] ?? null;
                                $paymentDate = $item['payment_date'] ?? null;
                                $paymentType = $item['payment_type'] ?? null;
                                $serviceType = $item['service_type'] ?? (! empty($item['hotel_name']) || ! empty($item['hotel_id']) ? 'hotel' : null);
                                $serviceId = $item['service_id'] ?? $item['hotel_id'] ?? null;
                                $serviceName = $item['service_name'] ?? $item['hotel_name'] ?? null;
                                $statusLabel = $to ?? $from ?? 'Status Change';
                                $color = vtStatusColor($statusLabel);
                                $isConfirmed = $to === 'Confirmed';
                                $isRefundPending = $to === 'Refund - Pending';
                                $isRefundedStatus = $to === 'Refunded';
                                $isServiceRefund = ! empty($serviceType) || ! empty($serviceName)
                                    || in_array((string) $action, ['deleted', 'Service Removed', 'Refund Initiated', 'Refund Verified', 'TravClicks Refunded'], true);
                                $isCancelStatus = $to && str_starts_with((string) $to, 'Cancel -');
                                $isRefundMoneyAction = in_array((string) $action, ['Refund Initiated', 'Refund Verified', 'TravClicks Refunded'], true)
                                    || $isRefundPending
                                    || $isRefundedStatus;
                                $showDetails = $hasOffers
                                    || ($amount !== null && $amount !== '')
                                    || ($actualAmount !== null && $actualAmount !== '')
                                    || ! empty($comment)
                                    || $isRefundPending
                                    || $isRefundedStatus
                                    || $isCancelStatus
                                    || ($isServiceRefund && in_array((string) $action, ['deleted', 'Service Removed'], true));
                            @endphp
                            <li class="vt-audit-item">
                                <div class="vt-audit-rail"><span class="vt-audit-node vt-node-{{ $color }}"></span></div>
                                <div class="vt-audit-content">
                                    <div class="vt-audit-top">
                                        <div class="vt-audit-title">
                                            @if(empty($from))
                                                <span class="vt-status vt-status-{{ $color }}">{{ $to }}</span>
                                            @elseif((string) $from === (string) $to && in_array((string) $action, ['deleted', 'Service Removed'], true))
                                                <strong>Service removed</strong>
                                            @else
                                                <span class="text-muted">{{ $from }}</span>
                                                <i class="ri-arrow-right-s-line"></i>
                                                <span class="vt-status vt-status-{{ $color }}">{{ $to }}</span>
                                            @endif
                                            @if(!empty($action))
                                                <span class="vt-chip">{{ $action }}</span>
                                            @endif
                                        </div>
                                        @if($date)
                                            <time>{{ $date->format('d M Y, H:i') }}</time>
                                        @endif
                                    </div>

                                    @if($date)
                                        <div class="vt-muted mb-1">{{ $date->diffForHumans() }}</div>
                                    @endif

                                    @if(!empty($serviceType) || !empty($serviceName))
                                        <div class="vt-service-line">
                                            <span class="text-capitalize">{{ $serviceType ?? 'service' }}</span>
                                            @if(!empty($serviceName))
                                                — <strong>{{ $serviceName }}</strong>
                                            @endif
                                            @if(!empty($serviceId))
                                                <span class="text-muted">(ID: {{ $serviceId }})</span>
                                            @endif
                                        </div>
                                    @endif

                                    @if($showDetails)
                                        <div class="vt-detail-box">
                                            @if($isRefundPending && $isServiceRefund)
                                                <div>Service refund initiated · waiting for finance verification</div>
                                            @elseif($isRefundedStatus && $isServiceRefund)
                                                <div>Service refund completed and verified</div>
                                            @elseif($isRefundPending)
                                                <div>Agent cancelled the tour · refund pending</div>
                                            @elseif($isRefundedStatus)
                                                <div>Tour refund completed</div>
                                            @elseif($isCancelStatus)
                                                <div>Agent cancelled · status {{ $to }}</div>
                                            @elseif($hasOffers)
                                                @if($isConfirmed)
                                                    <div class="mb-1 fw-medium">Agent accepted country prices</div>
                                                @endif
                                                @foreach($offers as $offer)
                                                    @php
                                                        $offerCountry = trim((string) ($offer['country'] ?? ''));
                                                        $offerCurrency = strtoupper(trim((string) ($offer['currency'] ?? '')));
                                                        $offerAmount = $offer['amount'] ?? null;
                                                        $offerActualAmount = $offer['actual_amount'] ?? null;
                                                    @endphp
                                                    <div class="vt-offer-row">
                                                        <strong>{{ $offerCountry !== '' ? $offerCountry : 'Country' }}</strong>
                                                        @if($offerCurrency !== '') <span class="vt-chip">{{ $offerCurrency }}</span> @endif
                                                        <div>{{ $isConfirmed ? 'Accepted' : 'Negotiate' }}: {{ $offerAmount !== null && $offerAmount !== '' ? (is_numeric($offerAmount) ? number_format((float) $offerAmount, 2) : $offerAmount) : '—' }}</div>
                                                        <div>{{ $isConfirmed ? 'Original' : 'Offered' }}: {{ $offerActualAmount !== null && $offerActualAmount !== '' ? (is_numeric($offerActualAmount) ? number_format((float) $offerActualAmount, 2) : $offerActualAmount) : '—' }}</div>
                                                    </div>
                                                @endforeach
                                                @if($isConfirmed)
                                                    <div class="mt-1">
                                                        Total original: {{ $actualAmount !== null && $actualAmount !== '' ? (is_numeric($actualAmount) ? number_format((float) $actualAmount, 2) : $actualAmount) : '—' }}
                                                        @if(!empty($confirmCurrency)) {{ $confirmCurrency }} @endif
                                                        · Accepted: {{ $amount !== null && $amount !== '' ? (is_numeric($amount) ? number_format((float) $amount, 2) : $amount) : '—' }}
                                                        @if(!empty($confirmCurrency)) {{ $confirmCurrency }} @endif
                                                    </div>
                                                @endif
                                                @if(!empty($comment))
                                                    <div class="mt-1">Comment: {{ $comment }}</div>
                                                @endif
                                            @elseif($isConfirmed)
                                                <div>
                                                    Original: {{ $actualAmount !== null && $actualAmount !== '' ? (is_numeric($actualAmount) ? number_format((float) $actualAmount, 2) : $actualAmount) : '—' }}
                                                    @if(!empty($confirmCurrency)) {{ $confirmCurrency }} @endif
                                                </div>
                                                <div>
                                                    Accepted: {{ $amount !== null && $amount !== '' ? (is_numeric($amount) ? number_format((float) $amount, 2) : $amount) : '—' }}
                                                    @if(!empty($confirmCurrency)) {{ $confirmCurrency }} @endif
                                                </div>
                                            @else
                                                @if($amount !== null && $amount !== '')
                                                    <div>Negotiate amount: {{ is_numeric($amount) ? number_format((float) $amount, 2) : $amount }}</div>
                                                @endif
                                                @if($actualAmount !== null && $actualAmount !== '')
                                                    <div>Offered amount: {{ is_numeric($actualAmount) ? number_format((float) $actualAmount, 2) : $actualAmount }}</div>
                                                @endif
                                                @if(!empty($comment))
                                                    <div>Comment: {{ $comment }}</div>
                                                @endif
                                            @endif
                                        </div>
                                    @endif

                                    @if(!empty($Amount) || !empty($selectedCurrency) || (! $isRefundMoneyAction && (!empty($paymentDate) || !empty($paymentType))))
                                        <div class="vt-money-line">
                                            @if(!empty($Amount) || !empty($selectedCurrency))
                                                <span>
                                                    {{ $isRefundMoneyAction ? 'Refund' : 'Amount' }}:
                                                    <strong>{{ is_numeric($Amount) ? number_format((float) $Amount, 2) : ($Amount ?? '—') }}</strong>
                                                    @if(!empty($selectedCurrency)) {{ $selectedCurrency }} @endif
                                                </span>
                                            @endif
                                            @if(! $isRefundMoneyAction && (!empty($paymentDate) || !empty($paymentType)))
                                                <span>{{ $paymentDate ?: '—' }} · {{ $paymentType ?: '—' }}</span>
                                            @endif
                                        </div>
                                    @endif

                                    @if(!empty($changedByName))
                                        <div class="vt-muted mt-1">Updated by {{ $changedByName }}</div>
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li class="vt-audit-item">
                                <div class="vt-audit-rail"><span class="vt-audit-node vt-node-{{ $statusTone }}"></span></div>
                                <div class="vt-audit-content">
                                    <strong>Current status</strong>
                                    <div><span class="vt-status vt-status-{{ $statusTone }}">{{ $tour->tour_status }}</span></div>
                                    <div class="vt-muted">Since {{ $tour->updated_at->format('d M Y') }}</div>
                                </div>
                            </li>
                        @endforelse
                    </ol>
                </div>
            </section>
        </div>
    </div>
</div>

<style>
.vt-page {
    --vt-ink: #0f172a;
    --vt-muted: #64748b;
    --vt-line: #e2e8f0;
    --vt-soft: #f8fafc;
    --vt-panel: #ffffff;
    --vt-accent: #0f766e;
    color: var(--vt-ink);
}

.vt-header {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: flex-start;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--vt-line);
}
.vt-breadcrumb {
    display: flex;
    gap: 0.4rem;
    font-size: 0.78rem;
    color: var(--vt-muted);
    margin-bottom: 0.35rem;
}
.vt-breadcrumb a { color: var(--vt-muted); text-decoration: none; }
.vt-breadcrumb a:hover { color: var(--vt-ink); }
.vt-title-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.4rem;
}
.vt-title {
    font-size: 1.45rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    margin: 0;
    color: var(--vt-ink);
}
.vt-meta-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem 1.1rem;
    font-size: 0.82rem;
    color: var(--vt-muted);
}
.vt-meta-row i { margin-right: 0.2rem; }
.vt-header-actions { display: flex; gap: 0.5rem; flex-shrink: 0; }

.vt-kpi-strip {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 0.75rem;
    margin-bottom: 1rem;
}
.vt-kpi {
    background: var(--vt-panel);
    border: 1px solid var(--vt-line);
    border-radius: 8px;
    padding: 0.75rem 0.9rem;
}
.vt-kpi-label {
    display: block;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--vt-muted);
    margin-bottom: 0.2rem;
}
.vt-kpi-value {
    display: block;
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--vt-ink);
    line-height: 1.2;
}
.vt-kpi-value-sm { font-size: 0.95rem; }
.vt-kpi-sub {
    display: block;
    margin-top: 0.15rem;
    font-size: 0.75rem;
    color: var(--vt-muted);
}

.vt-panel {
    background: var(--vt-panel);
    border: 1px solid var(--vt-line);
    border-radius: 8px;
    overflow: hidden;
}
.vt-panel-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    padding: 0.7rem 1rem;
    border-bottom: 1px solid var(--vt-line);
    background: var(--vt-soft);
}
.vt-panel-head h2 {
    margin: 0;
    font-size: 0.92rem;
    font-weight: 700;
    letter-spacing: 0.01em;
}
.vt-panel-sub {
    font-size: 0.75rem;
    color: var(--vt-muted);
    margin-top: 0.1rem;
}
.vt-panel-body { padding: 1rem; }
.vt-count {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--vt-muted);
    background: #fff;
    border: 1px solid var(--vt-line);
    border-radius: 999px;
    padding: 0.15rem 0.55rem;
}
.vt-section-label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--vt-muted);
    margin: 0 0 0.65rem;
    font-weight: 700;
}
.vt-fact-grid > div { padding: 0 1rem 0 0; }
.vt-fact-grid > div + div {
    padding-left: 1rem;
    border-left: 1px solid var(--vt-line);
}
.vt-dl { margin: 0; }
.vt-dl > div {
    display: grid;
    grid-template-columns: 120px 1fr;
    gap: 0.5rem;
    padding: 0.35rem 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.86rem;
}
.vt-dl dt { margin: 0; color: var(--vt-muted); font-weight: 500; }
.vt-dl dd { margin: 0; color: var(--vt-ink); }
.vt-strong { font-weight: 650; color: var(--vt-ink); }
.vt-muted { color: var(--vt-muted); font-size: 0.82rem; }
.vt-empty { padding: 1.25rem 1rem; color: var(--vt-muted); font-size: 0.88rem; }

.vt-guest-bars { display: grid; gap: 0.4rem; }
.vt-guest-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.45rem 0.65rem;
    border: 1px solid var(--vt-line);
    border-radius: 6px;
    background: var(--vt-soft);
    font-size: 0.86rem;
}
.vt-guest-bar-total {
    background: #0f172a;
    border-color: #0f172a;
    color: #fff;
}
.vt-guest-bar-total span { color: #cbd5e1; }

.vt-table { font-size: 0.84rem; }
.vt-table thead th {
    background: var(--vt-soft);
    border-bottom: 1px solid var(--vt-line);
    color: var(--vt-muted);
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 700;
    white-space: nowrap;
}
.vt-table td, .vt-table th { padding: 0.65rem 1rem; vertical-align: middle; }
.vt-table tbody tr + tr td { border-top: 1px solid #f1f5f9; }
.vt-table tfoot td {
    border-top: 1px solid var(--vt-line);
    background: var(--vt-soft);
}
.vt-type {
    display: inline-block;
    font-size: 0.72rem;
    font-weight: 650;
    color: #334155;
    background: #f1f5f9;
    border: 1px solid var(--vt-line);
    border-radius: 4px;
    padding: 0.12rem 0.4rem;
}
.vt-subrow td { background: #fcfcfd; }

.vt-status {
    display: inline-flex;
    align-items: center;
    font-size: 0.72rem;
    font-weight: 650;
    line-height: 1;
    padding: 0.28rem 0.5rem;
    border-radius: 4px;
    border: 1px solid transparent;
    white-space: nowrap;
}
.vt-status-success { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
.vt-status-primary { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.vt-status-info { background: #ecfeff; color: #0e7490; border-color: #a5f3fc; }
.vt-status-warning { background: #fffbeb; color: #b45309; border-color: #fde68a; }
.vt-status-danger { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
.vt-status-secondary { background: #f8fafc; color: #475569; border-color: #e2e8f0; }
.vt-status-olive { background: #f4f7ef; color: #4d5c2e; border-color: #c9d4a8; }

.vt-chip {
    display: inline-flex;
    align-items: center;
    font-size: 0.68rem;
    font-weight: 600;
    color: #475569;
    background: #f1f5f9;
    border: 1px solid var(--vt-line);
    border-radius: 4px;
    padding: 0.15rem 0.4rem;
}

.vt-key-dates { position: relative; padding-left: 0.25rem; }
.vt-key-date {
    display: flex;
    gap: 0.75rem;
    padding: 0.55rem 0;
    border-bottom: 1px solid #f1f5f9;
}
.vt-key-date:last-child { border-bottom: 0; }
.vt-key-dot {
    width: 10px;
    height: 10px;
    margin-top: 0.35rem;
    border-radius: 50%;
    background: #94a3b8;
    box-shadow: 0 0 0 3px #e2e8f0;
    flex-shrink: 0;
}
.vt-key-dot.is-done { background: var(--vt-accent); box-shadow: 0 0 0 3px #ccfbf1; }

.vt-history-panel {
    position: sticky;
    top: 1rem;
    max-height: calc(100vh - 2rem);
    display: flex;
    flex-direction: column;
}
.vt-history-body {
    overflow: auto;
    max-height: calc(100vh - 7rem);
    padding: 0.75rem 0.9rem 1rem;
}
.vt-audit {
    list-style: none;
    margin: 0;
    padding: 0;
}
.vt-audit-item {
    display: grid;
    grid-template-columns: 18px 1fr;
    gap: 0.65rem;
}
.vt-audit-rail {
    position: relative;
}
.vt-audit-rail::before {
    content: '';
    position: absolute;
    left: 7px;
    top: 14px;
    bottom: -8px;
    width: 1px;
    background: var(--vt-line);
}
.vt-audit-item:last-child .vt-audit-rail::before { display: none; }
.vt-audit-node {
    display: block;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #94a3b8;
    margin-top: 0.2rem;
}
.vt-node-success { border-color: #059669; }
.vt-node-primary { border-color: #2563eb; }
.vt-node-info { border-color: #0891b2; }
.vt-node-warning { border-color: #d97706; }
.vt-node-danger { border-color: #dc2626; }
.vt-node-olive { border-color: #556b2f; }
.vt-node-secondary { border-color: #64748b; }
.vt-audit-content {
    padding-bottom: 1rem;
    min-width: 0;
}
.vt-audit-top {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
    align-items: flex-start;
}
.vt-audit-top time {
    font-size: 0.72rem;
    color: var(--vt-muted);
    white-space: nowrap;
}
.vt-audit-title {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.84rem;
}
.vt-service-line {
    font-size: 0.8rem;
    margin: 0.25rem 0;
}
.vt-detail-box {
    margin-top: 0.4rem;
    padding: 0.55rem 0.65rem;
    background: var(--vt-soft);
    border: 1px solid var(--vt-line);
    border-radius: 6px;
    font-size: 0.78rem;
    color: #334155;
}
.vt-offer-row + .vt-offer-row {
    margin-top: 0.45rem;
    padding-top: 0.45rem;
    border-top: 1px dashed #e2e8f0;
}
.vt-money-line {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    margin-top: 0.35rem;
    font-size: 0.8rem;
}

.bg-olive { background-color: #556b2f !important; color: #fff !important; }
.bg-label-olive {
    background-color: rgba(85, 107, 47, 0.12) !important;
    color: #556b2f !important;
    border: 1px solid rgba(85, 107, 47, 0.35);
}

@media (max-width: 1199.98px) {
    .vt-kpi-strip { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .vt-history-panel { position: static; max-height: none; }
    .vt-history-body { max-height: 560px; }
}
@media (max-width: 767.98px) {
    .vt-header { flex-direction: column; }
    .vt-kpi-strip { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .vt-fact-grid > div + div {
        padding-left: 0;
        padding-top: 1rem;
        margin-top: 0.75rem;
        border-left: 0;
        border-top: 1px solid var(--vt-line);
    }
    .vt-dl > div { grid-template-columns: 100px 1fr; }
}
@media print {
    .vt-header-actions, .layout-menu, .layout-navbar { display: none !important; }
    .vt-history-panel { position: static; max-height: none; }
    .vt-history-body { max-height: none; overflow: visible; }
    .vt-panel { break-inside: avoid; }
}
</style>

<script>
function printTour() {
    window.print();
}
function exportTour() {
    window.open('/bookings/export-tour-pdf/' + {{ (int) $tour->tour_id }}, '_blank');
}
</script>
@endsection

@extends('layouts.datatablejs')
