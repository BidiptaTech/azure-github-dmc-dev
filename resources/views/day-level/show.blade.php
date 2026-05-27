@extends('layouts.layout')

@section('title', 'Day Level Details')

@push('css')
<style>
    :root {
        --dl-brand: #696cff;
        --dl-header-bg: linear-gradient(135deg, #696cff 0%, #845ef7 100%);
    }
    .dl-summary-card {
        border: 0;
        box-shadow: 0 2px 6px rgba(67, 89, 113, 0.08);
        border-radius: 0.75rem;
        overflow: hidden;
    }
    .dl-summary-head {
        background: var(--dl-header-bg);
        color: #fff !important;
        padding: 1rem 1.25rem;
    }
    .dl-summary-head .dl-summary-head-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: rgba(255, 255, 255, 0.82) !important;
        font-weight: 600;
        margin-bottom: 0.2rem;
    }
    .dl-summary-head .dl-summary-head-value {
        font-size: 0.95rem;
        font-weight: 600;
        color: #fff !important;
    }
    .dl-package-card {
        border: 1px solid rgba(105, 108, 255, 0.12);
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(67, 89, 113, 0.06);
        margin-bottom: 1.5rem;
    }
    .dl-package-head {
        background: var(--dl-header-bg);
        color: #fff;
        padding: 1rem 1.25rem;
    }
    .dl-package-head,
    .dl-package-head .package-title,
    .dl-package-head .package-meta,
    .dl-package-head h5 {
        color: #fff !important;
    }
    .dl-package-head .package-title {
        font-size: 1.05rem;
        font-weight: 700;
        margin: 0;
    }
    .dl-package-head .package-meta {
        font-size: 0.82rem;
        opacity: 0.92;
        margin-top: 0.35rem;
    }
    .dl-package-body {
        padding: 1.25rem;
        background: #fff;
    }
    .dl-section-title {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #8592a3;
        font-weight: 700;
        margin-bottom: 0.65rem;
    }
    .dl-city-card {
        border: 1px solid rgba(105, 108, 255, 0.15);
        border-radius: 0.65rem;
        overflow: hidden;
        margin-bottom: 1rem;
    }
    .dl-city-head {
        background: rgba(105, 108, 255, 0.08);
        color: #435971;
        padding: 0.65rem 1rem;
        font-weight: 600;
        border-bottom: 1px solid rgba(105, 108, 255, 0.12);
    }
    .dl-day-block {
        border-bottom: 1px solid rgba(67, 89, 113, 0.08);
        padding: 0.85rem 1rem;
    }
    .dl-day-block:last-child {
        border-bottom: 0;
    }
    .dl-day-title {
        font-weight: 700;
        color: var(--dl-brand);
        margin-bottom: 0.5rem;
        font-size: 0.92rem;
    }
    .dl-item-line {
        font-size: 0.85rem;
        color: #566a7f;
        margin-bottom: 0.35rem;
        padding-left: 0.5rem;
        border-left: 3px solid rgba(105, 108, 255, 0.25);
    }
    .dl-item-line strong {
        color: #435971;
    }
    .dl-empty {
        color: #8592a3;
        font-size: 0.85rem;
        font-style: italic;
    }
    .dl-badge-soft {
        background: rgba(255, 255, 255, 0.18);
        border: 1px solid rgba(255, 255, 255, 0.25);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
    }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h4 class="mb-0">Day Level Details</h4>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('day-level.index') }}" class="btn btn-outline-secondary">Back to list</a>
            </div>
        </div>

        <div class="card dl-summary-card mb-4">
            <div class="dl-summary-head">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="dl-summary-head-label">Master DMC</div>
                        <div class="dl-summary-head-value">
                            {{ optional($dayLevel->masterDmc)->company_name ?: 'Master DMC' }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dl-summary-head-label">DMC</div>
                        <div class="dl-summary-head-value">
                            {{ optional($dayLevel->dmc)->company_name ?: 'DMC' }}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="dl-summary-head-label">Country</div>
                        <div class="dl-summary-head-value">{{ $dayLevel->country ?: 'N/A' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="dl-summary-head-label">Packages</div>
                        <div class="dl-summary-head-value">{{ count($packageBlocks) }}</div>
                    </div>
                </div>
            </div>
        </div>

        @forelse($packageBlocks as $block)
            <div class="dl-package-card">
                <div class="dl-package-head d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h5 class="package-title">
                            {{ $block['cities'] ? implode(' · ', $block['cities']) : 'Package' }}
                        </h5>
                        <div class="package-meta">
                            {{ $block['total_days'] ?: $block['max_day'] }} day(s)
                        </div>
                    </div>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <span class="dl-badge-soft">{{ count($block['days'] ?? []) }} scheduled day(s)</span>
                        @if(!empty($block['has_stable_id']))
                            <a href="{{ route('day-level.edit', ['day_level' => $dayLevel->id, 'package_id' => $block['package_id']]) }}"
                               class="btn btn-sm btn-light">Edit package</a>
                        @endif
                    </div>
                </div>

                <div class="dl-package-body">
                    @if(!empty($block['city_plans']))
                        <div class="dl-section-title">Multi city itinerary</div>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>City</th>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($block['city_plans'] as $plan)
                                        <tr>
                                            <td>{{ $plan['city'] ?: 'N/A' }}</td>
                                            <td>Day {{ $plan['checkin'] }}</td>
                                            <td>Day {{ $plan['checkout'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="dl-section-title">Day by day</div>

                    @if(empty($block['days']))
                        <p class="dl-empty mb-0">No day services in this package.</p>
                    @else
                        @php
                            $cityGroups = [];
                            foreach ($block['days'] as $dayNode) {
                                $dayNum = (int) ($dayNode['day'] ?? 0);
                                $cityLabel = 'Itinerary';
                                foreach ($block['city_plans'] as $plan) {
                                    if ($dayNum >= ($plan['checkin'] ?? 0) && $dayNum <= ($plan['checkout'] ?? 0)) {
                                        $cityLabel = $plan['city'] ?: $cityLabel;
                                        break;
                                    }
                                }
                                $cityGroups[$cityLabel][] = $dayNode;
                            }
                        @endphp

                        @foreach($cityGroups as $cityLabel => $daysInCity)
                            <div class="dl-city-card">
                                <div class="dl-city-head">{{ $cityLabel }}</div>
                                @foreach($daysInCity as $dayNode)
                                    @php
                                        $dayNum = (int) ($dayNode['day'] ?? 0);
                                        $hotels = array_values((array) ($dayNode['hotels'] ?? []));
                                        $attractions = array_values((array) ($dayNode['attractions'] ?? []));
                                        $restaurants = array_values((array) ($dayNode['restaurants'] ?? []));
                                        $services = array_values((array) ($dayNode['services'] ?? []));
                                    @endphp
                                    <div class="dl-day-block">
                                        <div class="dl-day-title">Day {{ $dayNum ?: '?' }}</div>

                                        @if(empty($hotels) && empty($attractions) && empty($restaurants) && empty($services))
                                            <div class="dl-empty">No services scheduled</div>
                                        @endif

                                        @foreach($hotels as $hotel)
                                            <div class="dl-item-line">
                                                <strong>Hotel:</strong>
                                                {{ $hotel['hotel_name'] ?? 'N/A' }}
                                                · {{ $hotel['city'] ?? '' }}
                                                · {{ (int) ($hotel['night'] ?? 1) }} night(s)
                                                · {{ $hotel['meal_plan'] ?? 'No meal' }}
                                                @if(($hotel['guide_required'] ?? 'No') === 'Yes')
                                                    · Guide required
                                                @endif
                                            </div>
                                        @endforeach

                                        @foreach($attractions as $attraction)
                                            <div class="dl-item-line">
                                                <strong>Attraction:</strong>
                                                {{ $attraction['name'] ?? 'N/A' }}
                                                @if(!empty($attraction['ticket_name']))
                                                    · Ticket: {{ $attraction['ticket_name'] }}
                                                @endif
                                            </div>
                                            @if(!empty($attraction['transfer']['required']) && ($attraction['transfer']['required'] ?? '') === 'Yes')
                                                <div class="dl-item-line ms-3">
                                                    <strong>Transfer:</strong>
                                                    {{ $attraction['transfer']['pickup_location'] ?? '-' }}
                                                    → {{ $attraction['transfer']['drop_location'] ?? '-' }}
                                                </div>
                                            @endif
                                        @endforeach

                                        @foreach($restaurants as $restaurant)
                                            <div class="dl-item-line">
                                                <strong>Restaurant:</strong>
                                                {{ $restaurant['name'] ?? 'N/A' }}
                                            </div>
                                        @endforeach

                                        @foreach($services as $service)
                                            @php
                                                $meal = is_array($service['meal_configuration'] ?? null) ? $service['meal_configuration'] : null;
                                            @endphp
                                            <div class="dl-item-line">
                                                <strong>Service:</strong>
                                                {{ $service['restaurant_name'] ?? ($service['name'] ?? 'Service') }}
                                                @if($meal)
                                                    · {{ $meal['meal_type'] ?? '-' }} / {{ $meal['dish'] ?? '-' }} @ {{ $meal['time_slot'] ?? '-' }}
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-5">
                    No packages found for this record.
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
