{{-- Enquiry-selected services (from inquiry_form) — full-width strip so lite create layout is not squeezed --}}
@php
    $enq = $enquiry ?? null;
    $hasHotels = $enq && ($enq->hotel || ($hotels ?? collect())->count() > 0);
    $hasAttractions = $enq && ($enq->attraction || ($attractions ?? collect())->count() > 0);
    $hasGuides = $enq && ($enq->guide || ($guides ?? collect())->count() > 0);
    $hasVehicles = $enq && (($enq->local_transfer || $enq->pickup) || ($vehicles ?? collect())->count() > 0);
    $hasRestaurants = $enq && ($enq->restaurant || (isset($restaurant_data) && is_array($restaurant_data) && count($restaurant_data) > 0) || ($meals ?? collect())->count() > 0);
    $hasPorts = $enq && ($enq->entry_port || $enq->exit_port || $enq->pickup || $enq->local_transfer);
    $hasPackaged = $enq && ($enq->packaged_attractions || !empty($enq->packaged_attraction_ids));
    $showPanel = $enq && ($hasHotels || $hasAttractions || $hasGuides || $hasVehicles || $hasRestaurants || $hasPorts || $hasPackaged);
@endphp
@if($showPanel)
<section class="stp-lite-enq-strip mb-3" id="stpLiteEnquiryServices" aria-label="Enquiry selected services">
    <div class="stp-lite-enq-strip__bar" data-bs-toggle="collapse" data-bs-target="#stpLiteEnquiryServicesBody" role="button" aria-expanded="true" aria-controls="stpLiteEnquiryServicesBody">
        <div class="stp-lite-enq-strip__title">
            <i class="ri-file-list-3-line"></i>
            <div>
                <strong>Enquiry Selected Services</strong>
                <small>
                    {{ $enq->display_id ?? 'Enquiry' }}
                    @if($enq->multi_enq_id) · Multi {{ $enq->multi_enq_id }}@endif
                    @if($enq->city) · {{ $enq->city }}@endif
                    @if($enq->country) ({{ $enq->country }})@endif
                </small>
            </div>
        </div>
        <div class="stp-lite-enq-strip__hints">
            @if($hasHotels)<span class="stp-lite-enq-pill stp-lite-enq-pill--hotel"><i class="ri-hotel-line"></i> Hotels</span>@endif
            @if($hasAttractions)<span class="stp-lite-enq-pill stp-lite-enq-pill--attr"><i class="ri-map-pin-line"></i> Attractions</span>@endif
            @if($hasRestaurants)<span class="stp-lite-enq-pill stp-lite-enq-pill--rest"><i class="ri-restaurant-line"></i> Restaurants</span>@endif
            @if($hasGuides)<span class="stp-lite-enq-pill stp-lite-enq-pill--guide"><i class="ri-user-star-line"></i> Guides</span>@endif
            @if($hasVehicles || $hasPorts)<span class="stp-lite-enq-pill stp-lite-enq-pill--xfer"><i class="ri-car-line"></i> Transfer</span>@endif
            <i class="ri-arrow-down-s-line stp-lite-enq-strip__chevron"></i>
        </div>
    </div>

    <div id="stpLiteEnquiryServicesBody" class="collapse show">
        <div class="stp-lite-enq-strip__body">
            <div class="row g-2">
                @if($hasHotels)
                <div class="col-lg-4 col-md-6">
                    <div class="stp-lite-enq-block">
                        <div class="stp-lite-enq-block__head text-primary"><i class="ri-hotel-line me-1"></i>Hotels @if(($hotels ?? collect())->count()) ({{ $hotels->count() }})@endif</div>
                        @forelse(($hotels ?? collect()) as $hotel)
                            <div class="stp-lite-enq-block__item">
                                <strong>{{ $hotel->name ?? 'Hotel' }}</strong>
                                @if(!empty($hotel->city) || !empty($hotel->location))
                                    <small>{{ $hotel->city ?? $hotel->location }}</small>
                                @endif
                            </div>
                        @empty
                            <div class="stp-lite-enq-block__item"><small class="text-muted">Hotel requested on enquiry</small></div>
                        @endforelse
                        @if($enq->hotel_remarks)
                            <small class="text-muted d-block mt-1"><i class="ri-chat-quote-line me-1"></i>{{ $enq->hotel_remarks }}</small>
                        @endif
                    </div>
                </div>
                @endif

                @if($hasAttractions)
                <div class="col-lg-4 col-md-6">
                    <div class="stp-lite-enq-block">
                        <div class="stp-lite-enq-block__head text-success"><i class="ri-map-pin-line me-1"></i>Attractions @if(($attractions ?? collect())->count()) ({{ $attractions->count() }})@endif</div>
                        @forelse(($attractions ?? collect()) as $attraction)
                            <div class="stp-lite-enq-block__item">
                                <strong>{{ $attraction->name ?? 'Attraction' }}</strong>
                                @if(!empty($attraction->location))
                                    <small>{{ $attraction->location }}</small>
                                @endif
                            </div>
                        @empty
                            <div class="stp-lite-enq-block__item"><small class="text-muted">Attraction requested on enquiry</small></div>
                        @endforelse
                        @if($enq->attraction_remarks)
                            <small class="text-muted d-block mt-1"><i class="ri-chat-quote-line me-1"></i>{{ $enq->attraction_remarks }}</small>
                        @endif
                    </div>
                </div>
                @endif

                @if($hasRestaurants)
                <div class="col-lg-4 col-md-6">
                    <div class="stp-lite-enq-block">
                        <div class="stp-lite-enq-block__head text-warning"><i class="ri-restaurant-line me-1"></i>Restaurants</div>
                        @if(isset($restaurant_data) && is_array($restaurant_data) && count($restaurant_data) > 0)
                            @foreach($restaurant_data as $dateEntry)
                                @if(isset($dateEntry['date']) && isset($dateEntry['restaurants']))
                                    <div class="stp-lite-enq-block__item">
                                        <strong>{{ \Carbon\Carbon::parse($dateEntry['date'])->format('d M Y') }}</strong>
                                        @foreach($dateEntry['restaurants'] as $restaurantEntry)
                                            @php
                                                $restaurant = ($meals ?? collect())->firstWhere('restaurant_id', $restaurantEntry['restaurant_id'] ?? null);
                                            @endphp
                                            @if($restaurant)
                                                <small class="d-block">{{ $restaurant->name ?? 'Restaurant' }}</small>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        @elseif(($meals ?? collect())->count() > 0)
                            @foreach($meals as $restaurant)
                                <div class="stp-lite-enq-block__item"><strong>{{ $restaurant->name ?? 'Restaurant' }}</strong></div>
                            @endforeach
                        @else
                            <div class="stp-lite-enq-block__item"><small class="text-muted">Restaurant requested on enquiry</small></div>
                        @endif
                        @if($enq->restaurant_remarks)
                            <small class="text-muted d-block mt-1"><i class="ri-chat-quote-line me-1"></i>{{ $enq->restaurant_remarks }}</small>
                        @endif
                    </div>
                </div>
                @endif

                @if($hasGuides)
                <div class="col-lg-4 col-md-6">
                    <div class="stp-lite-enq-block">
                        <div class="stp-lite-enq-block__head" style="color:#7c3aed;"><i class="ri-user-star-line me-1"></i>Guides @if(($guides ?? collect())->count()) ({{ $guides->count() }})@endif</div>
                        @forelse(($guides ?? collect()) as $guide)
                            <div class="stp-lite-enq-block__item"><strong>{{ $guide->name ?? 'Guide' }}</strong></div>
                        @empty
                            <div class="stp-lite-enq-block__item"><small class="text-muted">Guide requested on enquiry</small></div>
                        @endforelse
                        @if($enq->guide_remarks)
                            <small class="text-muted d-block mt-1"><i class="ri-chat-quote-line me-1"></i>{{ $enq->guide_remarks }}</small>
                        @endif
                    </div>
                </div>
                @endif

                @if($hasVehicles && ($vehicles ?? collect())->count() > 0)
                <div class="col-lg-4 col-md-6">
                    <div class="stp-lite-enq-block">
                        <div class="stp-lite-enq-block__head text-info"><i class="ri-car-line me-1"></i>Vehicles ({{ $vehicles->count() }})</div>
                        @foreach($vehicles as $vehicle)
                            <div class="stp-lite-enq-block__item">
                                <strong>{{ $vehicle->vehicle_name ?? 'Vehicle' }}</strong>
                                @if(!empty($vehicle->seating_capacity))
                                    <small>{{ $vehicle->seating_capacity }} seats</small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($hasPorts)
                <div class="col-lg-4 col-md-6">
                    <div class="stp-lite-enq-block">
                        <div class="stp-lite-enq-block__head text-success"><i class="ri-flight-takeoff-line me-1"></i>Transfer &amp; Ports</div>
                        @if($enq->local_transfer)
                            <div class="stp-lite-enq-block__item"><strong>Local Transfer</strong></div>
                        @endif
                        @if($enq->entry_port)
                            <div class="stp-lite-enq-block__item">
                                <strong>Arrival</strong>
                                @if($enq->entry_port_address)<small class="d-block">Pickup: {{ $enq->entry_port_address }}</small>@endif
                                @if(!empty($entryDropoffLocation))<small class="d-block">Dropoff: {{ $entryDropoffLocation }}</small>@endif
                            </div>
                        @endif
                        @if($enq->exit_port)
                            <div class="stp-lite-enq-block__item">
                                <strong>Departure</strong>
                                @if($enq->exit_port_address)<small class="d-block">Pickup: {{ $enq->exit_port_address }}</small>@endif
                                @if(!empty($exitPickupLocation))<small class="d-block">From: {{ $exitPickupLocation }}</small>@endif
                            </div>
                        @endif
                        @if($enq->pickup_remarks)
                            <small class="text-muted d-block mt-1"><i class="ri-chat-quote-line me-1"></i>{{ $enq->pickup_remarks }}</small>
                        @endif
                    </div>
                </div>
                @endif

                @if($hasPackaged)
                <div class="col-lg-4 col-md-6">
                    <div class="stp-lite-enq-block">
                        <div class="stp-lite-enq-block__head text-primary"><i class="ri-stack-line me-1"></i>Packaged Attractions</div>
                        <div class="stp-lite-enq-block__item"><small class="text-muted">Requested on enquiry</small></div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
<style>
.stp-lite-enq-strip {
    border: 1px solid #dbe3f0;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
    overflow: hidden;
}
.stp-lite-enq-strip__bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    flex-wrap: wrap;
    padding: .7rem 1rem;
    background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);
    border-bottom: 1px solid #e2e8f0;
    cursor: pointer;
    user-select: none;
}
.stp-lite-enq-strip__title {
    display: flex;
    align-items: center;
    gap: .65rem;
    min-width: 0;
}
.stp-lite-enq-strip__title > i {
    width: 32px; height: 32px; border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center;
    background: #2563eb; color: #fff; flex-shrink: 0;
}
.stp-lite-enq-strip__title strong {
    display: block; font-size: .88rem; color: #0f172a; line-height: 1.2;
}
.stp-lite-enq-strip__title small {
    display: block; color: #64748b; font-size: .72rem;
}
.stp-lite-enq-strip__hints {
    display: flex; align-items: center; gap: .35rem; flex-wrap: wrap;
}
.stp-lite-enq-pill {
    display: inline-flex; align-items: center; gap: .2rem;
    font-size: .68rem; font-weight: 700; padding: .15rem .45rem;
    border-radius: 999px; border: 1px solid transparent;
}
.stp-lite-enq-pill--hotel { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.stp-lite-enq-pill--attr { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
.stp-lite-enq-pill--rest { background: #fffbeb; color: #b45309; border-color: #fde68a; }
.stp-lite-enq-pill--guide { background: #f5f3ff; color: #6d28d9; border-color: #ddd6fe; }
.stp-lite-enq-pill--xfer { background: #ecfeff; color: #0e7490; border-color: #a5f3fc; }
.stp-lite-enq-strip__chevron { color: #64748b; font-size: 1.1rem; transition: transform .2s ease; }
.stp-lite-enq-strip__bar[aria-expanded="false"] .stp-lite-enq-strip__chevron { transform: rotate(-90deg); }
.stp-lite-enq-strip__body { padding: .85rem 1rem 1rem; }
.stp-lite-enq-block {
    border: 1px solid #eef2f7; border-radius: 10px; background: #f8fafc;
    padding: .55rem .65rem; height: 100%;
}
.stp-lite-enq-block__head { font-size: .78rem; font-weight: 700; margin-bottom: .35rem; }
.stp-lite-enq-block__item {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;
    padding: .35rem .5rem; margin-bottom: .3rem; font-size: .78rem;
}
.stp-lite-enq-block__item strong { display: block; color: #0f172a; font-size: .8rem; }
.stp-lite-enq-block__item small { color: #64748b; }
</style>
<script>
(function () {
    var bar = document.querySelector('#stpLiteEnquiryServices .stp-lite-enq-strip__bar');
    var body = document.getElementById('stpLiteEnquiryServicesBody');
    if (!bar || !body) return;
    body.addEventListener('shown.bs.collapse', function () { bar.setAttribute('aria-expanded', 'true'); });
    body.addEventListener('hidden.bs.collapse', function () { bar.setAttribute('aria-expanded', 'false'); });
})();
</script>
@endif
