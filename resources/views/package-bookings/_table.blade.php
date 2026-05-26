@php
    /** @var \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection $bookings */
    /** @var \Illuminate\Support\Collection $packageComments */
    $__showBookingStatus = !empty($showBookingStatusColumn);
    $__showPayments = !empty($showPackagePaymentColumn);
    $__showNegotiation = array_key_exists('showNegotiationColumn', get_defined_vars()) ? (bool) $showNegotiationColumn : true;
    $__hideEditAction = !empty($hideEditAction);
    $__pkgCurrency = \App\Helpers\CommonHelper::getDmcCurrencyByCountry();
@endphp

<div class="table-responsive">
    <table class="datatables-basic table table-bordered" id="packageBookingsTable">
        <colgroup>
            @if(!$__showNegotiation && !$__showPayments)
                {{-- Refunds / compact tables --}}
                <col style="width:3%">
                <col style="width:18%">
                <col style="width:20%">
                <col style="width:14%">
                <col style="width:12%">
                @if($__showBookingStatus)
                <col style="width:10%">
                @endif
                <col style="width:13%">
                <col style="width:10%">
            @else
                {{-- Wide tables --}}
                <col style="width:3%">
                <col style="width:16%">
                <col style="width:18%">
                <col style="width:16%">
                <col style="width:14%">
                @if($__showBookingStatus)
                <col style="width:10%">
                @endif
                <col style="width:12%">
                @if($__showNegotiation)
                <col style="width:12%">
                @endif
                @if($__showPayments)
                <col style="width:11%">
                @endif
                <col style="width:10%">
            @endif
        </colgroup>
        <thead class="table-light">
            <tr>
                <th class="th-tooltip" data-tooltip="#">#</th>
                <th class="th-tooltip" data-tooltip="Booking Details">Booking Details</th>
                <th class="th-tooltip" data-tooltip="Package Details">Package Details</th>
                <th class="th-tooltip" data-tooltip="Created">Created</th>
                <th class="th-tooltip" data-tooltip="Agent">Agent</th>
                @if($__showBookingStatus)
                <th class="th-tooltip" data-tooltip="Booking status">Booking status</th>
                @endif
                <th class="th-tooltip" data-tooltip="Services">Services</th>
                @if($__showNegotiation)
                <th class="th-tooltip" data-tooltip="Negotiation">Negotiation</th>
                @endif
                @if($__showPayments)
                <th class="th-tooltip" data-tooltip="Payments">Payments</th>
                @endif
                <th class="th-tooltip" data-tooltip="Actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $idx => $b)
                @php
                    $userInfo = is_array($b->user_info ?? null) ? ($b->user_info ?? []) : (is_string($b->user_info ?? null) ? (json_decode($b->user_info, true) ?: []) : []);
                    $customerName = $userInfo['name'] ?? ($b->bookedBy->name ?? '—');
                    $customerEmail = $userInfo['email'] ?? ($b->bookedBy->email ?? null);
                    $travelDates = is_array($b->travel_dates ?? null) ? ($b->travel_dates ?? []) : (is_string($b->travel_dates ?? null) ? (json_decode($b->travel_dates, true) ?: []) : []);
                    $startDate = $travelDates['start_date'] ?? null;
                    $endDate = $travelDates['end_date'] ?? null;
                    $statusValue = data_get($b, $statusColumn) ?? '—';
                    $bookingComments = ($packageComments ?? collect([]))
                        ->where('booking_id', $b->booking_id)
                        ->sortByDesc('created_at')
                        ->values();
                    $latestComment = $bookingComments->first();
                    $activeComment = $bookingComments->firstWhere('status', 1) ?? $latestComment;
                    // Match Tours flow: toggle buttons based on latest comment sender_type (agent <-> OM)
                    $latestSenderType = strtolower((string) ($latestComment?->sender_type ?? ''));
                    $hasAgentComment = $latestComment && $latestSenderType === 'agent';
                    $negLastAmount = $latestComment?->amount ?? '';
                    $negLastRemark = $latestComment?->comment ?? '';
                    $inquiryIdForUpdate = $activeComment?->package_inquiry_id ?? $latestComment?->package_inquiry_id ?? '';

                    $details = is_array($b->booking_details ?? null) ? ($b->booking_details ?? []) : (is_string($b->booking_details ?? null) ? (json_decode($b->booking_details, true) ?: []) : []);
                    $adultCount = (int) (data_get($details, 'adult_count') ?? data_get($details, 'adult') ?? 0);
                    $childCount = (int) (data_get($details, 'child_count') ?? data_get($details, 'child') ?? 0);
                    $priceAdult = (float) ($b->packageInfo?->price_adult ?? 0);
                    $priceChild = (float) ($b->packageInfo?->price_child ?? 0);
                    // Negotiation "Actual Amount" should be based on selected services totals (hotels/attractions/restaurants/transfers)
                    $approxActual = (float) \App\Helpers\CommonHelper::calculatePackageBookingActualAmount((string) $b->booking_id, $b->package_id ?? null, $b->dmc_id ?? null);
                    $priceData = is_array($b->total_price ?? null) ? ($b->total_price ?? []) : (is_string($b->total_price ?? null) ? (json_decode($b->total_price, true) ?: []) : []);
                    $storedTotal = (float) ($priceData['total_price'] ?? 0);
                    $storedFinal = (float) ($priceData['final_price'] ?? 0);
                    $storedBaseForUi = $storedFinal > 0 ? $storedFinal : ($storedTotal > 0 ? $storedTotal : $approxActual);

                    $latestOmComment = $bookingComments->first(function ($c) {
                        return strtolower((string) ($c->sender_type ?? '')) === 'om';
                    });
                    $latestAgentComment = $bookingComments->first(function ($c) {
                        return strtolower((string) ($c->sender_type ?? '')) === 'agent';
                    });
                    // DMC/OM counter-offer (or package total) = reference for agent modal & payment base excl. tax.
                    $agentNegotiationBaseline = \App\Helpers\CommonHelper::packageNegotiatedPriceExclTax($storedBaseForUi, $bookingComments);
                    $lastAgentOfferAmount = $latestAgentComment?->amount ?? '';
                    $lastAgentRemark = (string) ($latestAgentComment?->comment ?? '');

                    $services = \App\Helpers\CommonHelper::getPackageBookingServices((string) $b->booking_id, $b->package_id ?? null, $b->dmc_id ?? null);
                    $hasHotels = !empty($services['selected_hotels'] ?? []);
                    $hasAttractions = !empty($services['selected_attractions'] ?? []);
                    $hasRestaurants = !empty($services['selected_restaurants'] ?? []);
                    $hasArrival = !empty($services['arrival_data'] ?? []);
                    $hasDeparture = !empty($services['departure_data'] ?? []);

                    $isPipelineStatus = in_array((string) $statusValue, ['New Enquiry', 'Prospect', 'Tentative'], true);
                    $showNegotiationSummary = ! $isPipelineStatus;
                    if ($showNegotiationSummary || $__showPayments) {
                        $pkgTaxResult = \App\Helpers\CommonHelper::calculatePackageBookingTaxBreakdown(
                            (float) $agentNegotiationBaseline,
                            $b->taxes ?? [],
                            $details
                        );
                        $pkgTaxAmountRow = (float) ($pkgTaxResult['total_tax'] ?? 0);
                        $pkgFinalInclRow = (float) $agentNegotiationBaseline + $pkgTaxAmountRow;
                    } else {
                        $pkgTaxResult = ['breakdown' => [], 'total_tax' => 0.0];
                        $pkgTaxAmountRow = 0.0;
                        $pkgFinalInclRow = 0.0;
                    }
                    $paidAmountPkg = 0.0;
                    $hasPendingPaymentsPkg = false;
                    if ($__showPayments) {
                        $pdRows = \App\Helpers\CommonHelper::normalizeJsonArray($b->payment_details);
                        foreach ($pdRows as $p) {
                            if ((int) ($p['status'] ?? 0) === 1) {
                                $paidAmountPkg += (float) ($p['payment_amount'] ?? 0);
                            }
                            if ((int) ($p['status'] ?? 0) === 0) {
                                $hasPendingPaymentsPkg = true;
                            }
                        }
                    }
                    $dueAmountPkg = $__showPayments ? max(0, $pkgFinalInclRow - $paidAmountPkg) : 0.0;
                    $u = auth()->user();
                    $canAddPkgPayment = $u && (
                        (function_exists('hasPermission') && hasPermission('add payment'))
                        || in_array((int) ($u->role_id ?? 0), [11, 33, 37, 38, 128, 131, 132, 134, 135, 137, 138], true)
                    );
                    $canFinancePkgPayment = $u && in_array((int) ($u->role_id ?? 0), [36, 129, 131, 133, 134, 136, 137, 138, 126, 127], true);
                @endphp
                <tr data-created-at="{{ optional($b->created_at)->toDateString() }}" @if($__showBookingStatus)data-booking-status="{{ e((string) $statusValue) }}"@endif>
                    <td class="align-top">{{ $idx + 1 }}</td>
                    <td class="align-top">
                        <div class="d-flex flex-column gap-1">
                            <strong class="text-primary">{{ $b->booking_id ?? ('#' . ($b->id ?? '')) }}</strong>
                            <small class="text-muted">Package ID: {{'#' . ($b->package_id ?? '') ?? '—' }}</small>

                            {{-- @php
                                $dest = $b->packageInfo?->destination ?? null;
                                $cat = $b->packageInfo?->category ?? null;
                            @endphp
                            @if($dest || $cat)
                                <small class="text-muted">
                                    @if($dest) {{ $dest }} @endif
                                    @if($dest && $cat) • @endif
                                    @if($cat) {{ $cat }} @endif
                                </small>
                            @endif --}}

                            <div class="d-flex flex-column">
                                <small class="text-muted">
                                    <span class="fw-semibold">In:</span>
                                    {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('M d, Y') : '—' }}
                                </small>
                                <small class="text-muted">
                                    <span class="fw-semibold">Out:</span>
                                    {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('M d, Y') : '—' }}
                                </small>
                            </div>
                        </div>
                    </td>
                    <td class="align-top">
                        <div class="d-flex flex-column gap-1">
                            <strong>{{ $b->packageInfo->title ?? ('Package #' . ($b->package_id ?? '—')) }}</strong>
                            <div class="d-flex flex-wrap gap-1">
                                @if(!empty($b->packageInfo?->destination))
                                    <span class="badge bg-label-info">{{ $b->packageInfo->destination }}</span>
                                @endif
                                @if(!empty($b->packageInfo?->city))
                                    <span class="badge bg-label-primary">{{ $b->packageInfo->city }}</span>
                                @endif
                                @if(!empty($b->packageInfo?->category))
                                    <span class="badge bg-label-success">{{ $b->packageInfo->category }}</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="align-top">
                        <div class="d-flex flex-column gap-1">
                            <strong>{{ $customerName }}</strong>
                            @if($customerEmail)
                                <small class="text-muted">{{ $customerEmail }}</small>
                            @endif
                            <small class="text-muted">
                                {{ $b->created_at ? $b->created_at->format('M d, Y') : '—' }}
                            </small>
                        </div>
                    </td>
                    <td class="align-top">
                        @if($b->agent)
                            <div class="d-flex flex-column gap-1">
                                <strong>{{ $b->agent->name }}</strong>
                                @if(!empty($b->agent->company_name))
                                    <small class="text-muted">{{ $b->agent->company_name }}</small>
                                @endif
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    @if($__showBookingStatus)
                    <td class="align-top">
                        <span class="badge bg-label-primary text-wrap">{{ $statusValue }}</span>
                    </td>
                    @endif
                    <td class="align-top">
                        <div class="d-flex flex-wrap gap-2">
                            @if($hasHotels)
                                <a href="javascript:void(0);" class="badge bg-label-primary text-decoration-none" onclick="openPackageServiceModal('hotel', '{{ $b->booking_id }}')">
                                    <i class="ri-hotel-line me-1"></i> Hotels
                                </a>
                            @endif
                            @if($hasAttractions)
                                <a href="javascript:void(0);" class="badge bg-label-info text-decoration-none" onclick="openPackageServiceModal('attraction', '{{ $b->booking_id }}')">
                                    <i class="ri-building-2-line me-1"></i> Attractions
                                </a>
                            @endif
                            @if($hasRestaurants)
                                <a href="javascript:void(0);" class="badge bg-label-warning text-decoration-none" onclick="openPackageServiceModal('restaurant', '{{ $b->booking_id }}')">
                                    <i class="ri-restaurant-line me-1"></i> Restaurants
                                </a>
                            @endif
                            @if($hasArrival)
                                <a href="javascript:void(0);" class="badge bg-label-success text-decoration-none" onclick="openPackageServiceModal('arrival', '{{ $b->booking_id }}')">
                                    <i class="ri-flight-land-line me-1"></i> Arrival
                                </a>
                            @endif
                            @if($hasDeparture)
                                <a href="javascript:void(0);" class="badge bg-label-secondary text-decoration-none" onclick="openPackageServiceModal('departure', '{{ $b->booking_id }}')">
                                    <i class="ri-flight-takeoff-line me-1"></i> Departure
                                </a>
                            @endif
                            @if(!$hasHotels && !$hasAttractions && !$hasRestaurants && !$hasArrival && !$hasDeparture)
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                    </td>
                    @if($__showNegotiation)
                        <td class="align-top">
                            @if($isPipelineStatus)
                                <div class="d-flex flex-column gap-2">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary negotiation-btn negotiate-by-agent"
                                        data-booking-id="{{ $b->booking_id }}"
                                        data-display-id="{{ e($b->booking_id ?? '') }}"
                                        data-actual="{{ $storedBaseForUi }}"
                                        data-last-amount="{{ $lastAgentOfferAmount }}"
                                        data-last-comment="{{ e($lastAgentRemark) }}"
                                        data-booking-status="{{ e((string) $statusValue) }}"
                                        data-negotiation-locked="{{ $hasAgentComment ? '1' : '0' }}"
                                        onclick="openPackageAgentNegotiationModal(this)"
                                        {{ $hasAgentComment ? 'disabled' : '' }}
                                    >
                                        <span class="negotiate-by-agent-label">
                                            <i class="ri-handshake-line negotiate-by-agent-icon" aria-hidden="true"></i>
                                            <span class="d-block">Negotiate</span>
                                            <span class="d-block small text-muted">By Dmc</span>
                                        </span>
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-warning negotiation-btn"
                                        data-booking-id="{{ $b->booking_id }}"
                                        data-package-inquiry-id="{{ $inquiryIdForUpdate }}"
                                        data-price="{{ $negLastAmount !== '' ? $negLastAmount : $storedBaseForUi }}"
                                        data-actual="{{ $storedBaseForUi }}"
                                        data-discount="{{ max(0, $storedBaseForUi - (float)($negLastAmount !== '' ? $negLastAmount : $storedBaseForUi)) }}"
                                        data-comment="{{ e((string) $negLastRemark) }}"
                                        onclick="openPackageNegotiationUpdateModal(this)"
                                        {{ $hasAgentComment ? '' : 'disabled' }}
                                    >
                                        Negotiation
                                    </button>
                                    @if(!$hasAgentComment)
                                        <small class="text-muted" style="font-size: 0.7rem;">Awaiting agent</small>
                                    @endif
                                </div>
                            @else
                                <div class="d-flex flex-column gap-1 small">
                                    <div>
                                        <span class="text-muted">Agreed (excl. tax)</span>
                                        <div class="fw-semibold">{{ $__pkgCurrency }} {{ number_format((float) $agentNegotiationBaseline, 2) }}</div>
                                    </div>
                                    <div>
                                        <span class="text-muted">Tax</span>
                                        <div class="fw-semibold">{{ $__pkgCurrency }} {{ number_format($pkgTaxAmountRow, 2) }}</div>
                                    </div>
                                    <div>
                                        <span class="text-muted">Total (incl. tax)</span>
                                        <div class="fw-semibold text-success">{{ $__pkgCurrency }} {{ number_format($pkgFinalInclRow, 2) }}</div>
                                    </div>
                                    @if($latestComment)
                                        <div class="mt-1 text-muted" style="font-size: 0.7rem;">
                                            Last: {{ \Illuminate\Support\Str::limit((string) ($latestComment->comment ?? ''), 80) }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </td>
                    @endif
                    @if($__showPayments)
                    <td class="align-top">
                        <div class="d-flex flex-wrap gap-1 align-items-center">
                            @if(!empty($b->payment_details))
                                <button type="button" class="btn btn-sm btn-outline-primary" title="Payment history" data-bs-toggle="modal" data-bs-target="#pkgPaymentHistoryModal{{ $b->id }}">
                                    <i class="ri-wallet-3-line"></i>
                                </button>
                            @endif
                            @if($canAddPkgPayment && $dueAmountPkg > 0 && ! $hasPendingPaymentsPkg)
                                <button type="button" class="btn btn-sm btn-outline-success" title="Add payment" data-bs-toggle="modal" data-bs-target="#pkgAddPaymentModal{{ $b->id }}">
                                    <i class="ri-money-dollar-circle-line"></i>
                                </button>
                            @endif
                            @if(empty($b->payment_details) && ! ($canAddPkgPayment && $dueAmountPkg > 0 && ! $hasPendingPaymentsPkg))
                                <span class="text-muted small">—</span>
                            @endif
                        </div>
                    </td>
                    @endif
                    <td class="align-top col-actions">
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            @if(! $__hideEditAction)
                                <a href="{{ route('package.booking.edit', $b->booking_id) . '?return_url=' . urlencode(url()->full()) }}"
                                   class="action-icon-badge"
                                   style="--action-color: #7c3aed; background:#f5f3ff; border-color:#ddd6fe;"
                                   data-tooltip="Edit / add available add-ons">
                                    <i class="ri-edit-2-line"></i>
                                </a>
                            @endif
                            @if(strcasecmp((string) $statusValue, 'Refund - Pending') === 0)
                                <button type="button"
                                        class="action-icon-badge"
                                        style="--action-color: #16a34a; background:#ecfdf5; border-color:#bbf7d0;"
                                        data-tooltip="Process Refund"
                                        onclick="packageProcessRefund('{{ $b->booking_id }}')">
                                    <i class="ri-money-dollar-circle-line"></i>
                                </button>
                            @endif
                            @if(strcasecmp((string) $statusValue, 'Refunded') === 0)
                                <span class="badge d-inline-flex align-items-center gap-1"
                                      style="background:#ecfdf5;color:#166534;border:1px solid #bbf7d0;font-weight:600;padding:0.45rem 0.6rem;"
                                      data-tooltip="Refunded"
                                      aria-disabled="true">
                                    <i class="ri-check-line"></i>
                                    Refunded
                                </span>
                            @endif
                            @if(strcasecmp((string) $statusValue, 'Refund - Pending') !== 0 && strcasecmp((string) $statusValue, 'Refunded') !== 0)
                                <button type="button"
                                        class="action-icon-badge"
                                        style="--action-color: #dc2626; background:#fef2f2; border-color:#fecaca;"
                                        data-tooltip="Cancel Package"
                                        onclick="packageCancelBooking('{{ $b->booking_id }}')">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                
            @endforelse
        </tbody>
    </table>
</div>

