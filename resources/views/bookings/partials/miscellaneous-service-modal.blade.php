{{--
  Professional Miscellaneous service modal.

  @include('bookings.partials.miscellaneous-service-modal', [
      'tour' => $tour,
      'serviceData' => $serviceData,
      'pageCurrency' => $pageCurrency ?? ($currency ?? 'SGD'),
  ])
--}}
@php
    $pageCurrency = $pageCurrency ?? ($currency ?? 'SGD');
    $ordersList = $serviceData['miscellaneous'] ?? [];
@endphp

@if(isset($svc['miscellaneous']) && $svc['miscellaneous'] > 0)
<div class="modal fade svc-modal" id="miscellaneousDetailsModal{{ $tour->tour_id }}" tabindex="-1" aria-labelledby="miscellaneousDetailsModalLabel{{ $tour->tour_id }}" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="miscellaneousDetailsModalLabel{{ $tour->tour_id }}">
                    <i class="ri-file-list-3-line me-1"></i>Miscellaneous — Tour #{{ $tour->tour_id }}
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="closeServiceModal('miscellaneous', {{ $tour->tour_id }})" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if(is_array($ordersList) && count($ordersList) > 0)
                    @foreach($ordersList as $index => $miscOrder)
                        @php
                            $currency = \App\Helpers\CommonHelper::resolveOrderDisplayCurrency($miscOrder, $pageCurrency);
                            $orderCountry = trim((string) ($miscOrder->resolved_service_country ?? $miscOrder->country ?? ''));
                            $payload = is_string($miscOrder->data) ? json_decode($miscOrder->data, true) : $miscOrder->data;
                            if (!is_array($payload)) {
                                $payload = [];
                            }
                            $miscItems = (isset($payload[0]) && is_array($payload[0])) ? $payload : [$payload];
                        @endphp

                        @foreach($miscItems as $booking)
                            @if(!is_array($booking) || empty($booking))
                                @continue
                            @endif
                            @php
                                $itemName = $booking['itemName'] ?? ($booking['item_name'] ?? 'Miscellaneous Item');
                                $adults = (int) ($booking['adultsQty'] ?? ($booking['adults_qty'] ?? 0));
                                $children = (int) ($booking['childQty'] ?? ($booking['child_qty'] ?? 0));
                                $infants = (int) ($booking['infantQty'] ?? ($booking['infant_qty'] ?? 0));
                                // Unit sells are BASE; marked units used for display after DMC other markup
                                $adultSell = (float) ($booking['adultSellBase'] ?? $booking['adultSell'] ?? $booking['adult_sell'] ?? 0);
                                $childSell = (float) ($booking['childSellBase'] ?? $booking['childSell'] ?? $booking['child_sell'] ?? 0);
                                $infantSell = (float) ($booking['infantSellBase'] ?? $booking['infantSell'] ?? $booking['infant_sell'] ?? 0);
                                $adultSellMarked = (float) ($booking['adultSellMarked'] ?? 0);
                                $childSellMarked = (float) ($booking['childSellMarked'] ?? 0);
                                $infantSellMarked = (float) ($booking['infantSellMarked'] ?? 0);
                                $hasUnitPrices = $adultSell > 0 || $childSell > 0 || $infantSell > 0
                                    || $adultSellMarked > 0 || $childSellMarked > 0 || $infantSellMarked > 0;

                                $baseTotal = (float) (
                                    ($booking['base_grand_total'] ?? null)
                                    ?? ($booking['dmc_profile_markup']['base_price'] ?? null)
                                    ?? 0
                                );
                                if ($baseTotal <= 0) {
                                    $baseTotal = ($adultSell * $adults) + ($childSell * $children) + ($infantSell * $infants);
                                }
                                $totalPrice = (float) (
                                    ($booking['dmc_profile_markup']['price'] ?? null)
                                    ?? ($booking['totalPrice'] ?? $booking['total_price'] ?? null)
                                    ?? 0
                                );
                                if ($totalPrice <= 0) {
                                    $totalPrice = $baseTotal;
                                }
                                $markupAmount = (float) ($booking['dmc_profile_markup']['markup_amount'] ?? max(0, $totalPrice - $baseTotal));

                                try {
                                    $dateLabel = !empty($booking['bookingDate'])
                                        ? \Carbon\Carbon::parse($booking['bookingDate'])->format('M d, Y')
                                        : 'N/A';
                                } catch (\Throwable $e) {
                                    $dateLabel = (string) ($booking['bookingDate'] ?? 'N/A');
                                }

                                $cityLabel = trim((string) ($booking['city'] ?? ''));
                                $countryLabel = $orderCountry !== ''
                                    ? $orderCountry
                                    : trim((string) ($booking['country'] ?? ''));
                                $hasLocation = $cityLabel !== '' || $countryLabel !== '';
                            @endphp

                            <div class="svc-panel svc-country-item" data-service-country="{{ $orderCountry !== '' ? $orderCountry : 'Other' }}">
                                <div class="svc-panel-head">
                                    <div class="svc-panel-head-main">
                                        <div class="svc-thumb svc-thumb-fallback"><i class="ri-file-list-3-line"></i></div>
                                        <div>
                                            <p class="svc-title">{{ $itemName }}</p>
                                            <p class="svc-subtitle">{{ $dateLabel }}@if($index > 0) • Order {{ $index + 1 }}@endif</p>
                                        </div>
                                    </div>
                                    <div class="svc-price">{{ $currency }} {{ number_format($totalPrice, 2) }}</div>
                                </div>

                                <div class="svc-section mb-0" style="border:0;border-radius:0;">
                                    <p class="svc-section-title">Item &amp; Date</p>
                                    <div class="svc-dl">
                                        <div class="svc-dl-row">
                                            <span class="svc-dl-label">Item</span>
                                            <span class="svc-dl-value">{{ $itemName }}</span>
                                        </div>
                                        <div class="svc-dl-row">
                                            <span class="svc-dl-label">Date</span>
                                            <span class="svc-dl-value">{{ $dateLabel }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
                                    <p class="svc-section-title">Pax</p>
                                    <div class="svc-guest-grid" style="grid-template-columns:1fr 1fr 1fr;">
                                        <div class="svc-guest-box">
                                            <div class="num">{{ $adults }}</div>
                                            <div class="lbl">Adults</div>
                                        </div>
                                        <div class="svc-guest-box">
                                            <div class="num">{{ $children }}</div>
                                            <div class="lbl">Child</div>
                                        </div>
                                        <div class="svc-guest-box">
                                            <div class="num">{{ $infants }}</div>
                                            <div class="lbl">Infant</div>
                                        </div>
                                    </div>
                                    <div class="svc-total-bar">Total: {{ $adults + $children + $infants }} Pax</div>
                                </div>

                                <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
                                    <p class="svc-section-title">Pricing</p>
                                    <div class="svc-dl">
                                        @if($hasUnitPrices)
                                        <div class="svc-dl-row full">
                                            <span class="svc-dl-label">Sell / Pax (base)</span>
                                            <span class="svc-dl-value">Adult: {{ number_format($adultSell, 2) }} / Child: {{ number_format($childSell, 2) }} / Infant: {{ number_format($infantSell, 2) }}</span>
                                        </div>
                                        @if($adultSellMarked > 0 || $childSellMarked > 0 || $infantSellMarked > 0)
                                        <div class="svc-dl-row full">
                                            <span class="svc-dl-label">Markup / Pax</span>
                                            <span class="svc-dl-value">Adult: {{ number_format($adultSellMarked ?: $adultSell, 2) }} / Child: {{ number_format($childSellMarked ?: $childSell, 2) }} / Infant: {{ number_format($infantSellMarked ?: $infantSell, 2) }}</span>
                                        </div>
                                        @endif
                                        @endif
                                        @if($baseTotal > 0 && abs($baseTotal - $totalPrice) > 0.01)
                                        <div class="svc-dl-row full">
                                            <span class="svc-dl-label">Sell total (base)</span>
                                            <span class="svc-dl-value">{{ $currency }} {{ number_format($baseTotal, 2) }}</span>
                                        </div>
                                        <div class="svc-dl-row full">
                                            <span class="svc-dl-label">Markup amount</span>
                                            <span class="svc-dl-value">{{ $currency }} {{ number_format($markupAmount, 2) }}</span>
                                        </div>
                                        @endif
                                        <div class="svc-dl-row full">
                                            <span class="svc-dl-label">Total (after markup)</span>
                                            <span class="svc-dl-value svc-amount" style="color:var(--svc-accent);">{{ $currency }} {{ number_format($totalPrice, 2) }}</span>
                                        </div>
                                    </div>
                                </div>

                                @if($hasLocation)
                                <div class="svc-section mb-0" style="border:0;border-radius:0;border-top:1px solid var(--svc-line);">
                                    <p class="svc-section-title">Location</p>
                                    <div class="svc-dl">
                                        @if($cityLabel !== '')
                                        <div class="svc-dl-row">
                                            <span class="svc-dl-label">City</span>
                                            <span class="svc-dl-value">{{ $cityLabel }}</span>
                                        </div>
                                        @endif
                                        <div class="svc-dl-row{{ $cityLabel === '' ? ' full' : '' }}">
                                            <span class="svc-dl-label">Country</span>
                                            <span class="svc-dl-value">{{ $countryLabel !== '' ? $countryLabel : 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        @endforeach
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <i class="ri-file-list-3-line text-muted" style="font-size:1.75rem;"></i>
                        <h6 class="text-dark mt-2 mb-1">No Miscellaneous Data Available</h6>
                        <p class="text-muted mb-0" style="font-size:0.85rem;">Miscellaneous services are booked but detailed information is not available.</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm svc-footer-btn" onclick="closeServiceModal('miscellaneous', {{ $tour->tour_id }})">
                    <i class="ri-close-line me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>
@endif
