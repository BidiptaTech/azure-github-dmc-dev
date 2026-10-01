{{--
  Attraction modal transfer block (legacy card layout used by confirmed / actual / definite).
  Expects: $booking, $currency, optional $tour
  Pro: show cost + sell; header/total uses sell (vehicles[].lineSell / totalPrice / sell).
--}}
@php
    $tf = (isset($booking['transfer_options']) && is_array($booking['transfer_options']))
        ? $booking['transfer_options']
        : ((isset($booking['transferOptions']) && is_array($booking['transferOptions'])) ? $booking['transferOptions'] : null);
    $isPro = (int) ($tour->is_pro ?? 0) === 1;
    $vehList = ($tf && !empty($tf['vehicles']) && is_array($tf['vehicles']))
        ? array_values(array_filter($tf['vehicles'], 'is_array'))
        : [];

    $transferSell = 0.0;
    $transferCost = 0.0;
    if ($tf) {
        foreach ($vehList as $v) {
            $transferSell += (float) ($v['lineSell'] ?? $v['line_sell'] ?? $v['totalPrice'] ?? $v['sell'] ?? 0);
            $transferCost += (float) ($v['lineCost'] ?? $v['line_cost'] ?? $v['cost'] ?? 0);
        }
        if ($transferSell <= 0) {
            $transferSell = $isPro
                ? (float) ($tf['totalPrice'] ?? $tf['sell'] ?? 0)
                : (float) ($tf['cost'] ?? $tf['totalPrice'] ?? $tf['sell'] ?? 0);
        }
        if ($transferCost <= 0) {
            $transferCost = (float) ($tf['cost'] ?? $tf['lineCost'] ?? 0);
        }
    }
    $transferDisplay = $isPro ? $transferSell : (float) ($tf['cost'] ?? $tf['totalPrice'] ?? $transferSell ?? 0);

    $hasTransfer = $tf && (
        in_array($tf['transfer_required'] ?? null, [true, 'true', 'Yes', 1, '1'], true)
        || !empty($vehList)
        || !empty($tf['vehicle_details'])
        || !empty($tf['vehicle_id'])
        || $transferDisplay > 0
        || $transferCost > 0
    );
@endphp

@if($hasTransfer)
<div class="bg-light rounded p-2 mb-3">
    <div class="d-flex align-items-center mb-2">
        <div class="rounded-circle p-1 me-2" style="background: linear-gradient(135deg, #fd9853 0%, #fe7854 100%); width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
            <i class="ri-car-line text-white" style="font-size: 0.9rem;"></i>
        </div>
        <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.95rem;">Transfer Details</h6>
    </div>
    <div class="row g-2">
        <div class="col-md-6">
            <div class="bg-white rounded p-2">
                <div class="mb-1">
                    <small class="text-muted d-block" style="font-size: 0.7rem;">Transfer Type</small>
                    <span class="badge bg-primary" style="font-size: 0.7rem;">{{ $tf['type'] ?? 'N/A' }}</span>
                    <span class="badge bg-info" style="font-size: 0.7rem;">{{ $tf['way'] ?? 'N/A' }}</span>
                </div>
                @if(!empty($tf['pickup_location_name']))
                <div class="mt-1">
                    <small class="text-muted d-block" style="font-size: 0.7rem;">Pickup</small>
                    <div class="fw-medium text-primary" style="font-size: 0.8rem;">{{ $tf['pickup_location_name'] }}</div>
                </div>
                @endif
                @if(!empty($tf['destination_name']))
                <div class="mt-1">
                    <small class="text-muted d-block" style="font-size: 0.7rem;">Destination</small>
                    <div class="fw-medium text-primary" style="font-size: 0.8rem;">{{ $tf['destination_name'] }}</div>
                </div>
                @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="bg-white rounded p-2">
                @if(!empty($vehList))
                    @foreach($vehList as $vi => $v)
                        @php
                            $vName = $v['vehicle_name'] ?? $v['vehicleName'] ?? $v['name'] ?? ('Vehicle ' . ($vi + 1));
                            $vType = $v['type'] ?? $v['transferType'] ?? '';
                            $vQty = (int) ($v['qty'] ?? $v['quantity'] ?? 1);
                            $vLineSell = (float) ($v['lineSell'] ?? $v['line_sell'] ?? $v['totalPrice'] ?? $v['sell'] ?? 0);
                            $vLineCost = (float) ($v['lineCost'] ?? $v['line_cost'] ?? $v['cost'] ?? 0);
                        @endphp
                        <div class="{{ $vi > 0 ? 'mt-2 pt-2 border-top' : '' }}">
                            <small class="text-muted d-block" style="font-size: 0.7rem;">Vehicle {{ $vi + 1 }}</small>
                            <div class="fw-medium" style="font-size: 0.8rem;">
                                {{ $vName }}
                                @if($vType !== '') <span class="text-muted">({{ $vType }})</span> @endif
                                @if($vQty > 1) × {{ $vQty }} @endif
                            </div>
                            @if($isPro && $vLineCost > 0)
                            <small class="text-muted d-block" style="font-size: 0.65rem;">Cost: {{ $currency }} {{ number_format($vLineCost, 2) }}</small>
                            @endif
                            <div class="fw-bold text-success" style="font-size: 0.85rem;">
                                {{ $isPro ? 'Sell' : 'Price' }}: {{ $currency }} {{ number_format($vLineSell > 0 ? $vLineSell : $vLineCost, 2) }}
                            </div>
                        </div>
                    @endforeach
                @elseif(!empty($tf['vehicle_details']) && is_array($tf['vehicle_details']))
                    <small class="text-muted d-block" style="font-size: 0.7rem;">Vehicle</small>
                    <div class="fw-medium" style="font-size: 0.8rem;">{{ $tf['vehicle_details']['vehicle_name'] ?? 'N/A' }}</div>
                    @if(!empty($tf['vehicle_details']['seating_capacity']))
                    <small class="text-muted" style="font-size: 0.65rem;">Capacity: {{ $tf['vehicle_details']['seating_capacity'] }}</small>
                    @endif
                @elseif(!empty($tf['vehicle_id']))
                    <small class="text-muted d-block" style="font-size: 0.7rem;">Vehicle ID</small>
                    <div class="fw-medium" style="font-size: 0.8rem;">{{ $tf['vehicle_id'] }}</div>
                @endif

                @if($isPro && $transferCost > 0)
                <div class="mt-2">
                    <small class="text-muted d-block" style="font-size: 0.7rem;">Transfer Cost</small>
                    <div class="fw-bold text-muted" style="font-size: 0.85rem;">{{ $currency }} {{ number_format($transferCost, 2) }}</div>
                </div>
                @endif
                @if($transferDisplay > 0)
                <div class="mt-1">
                    <small class="text-muted d-block" style="font-size: 0.7rem;">{{ $isPro ? 'Transfer Sell' : 'Transfer Cost' }}</small>
                    <div class="fw-bold text-success" style="font-size: 0.9rem;">{{ $currency }} {{ number_format($transferDisplay, 2) }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
