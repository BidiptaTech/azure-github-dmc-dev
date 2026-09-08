@extends('layouts.layout')
@section('title', 'Hotels')
@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
<link href="{{ URL::asset('build/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .room-occupancy-pricing-row > .col-md-6 {
        display: flex;
        flex-direction: column;
    }

    .room-occupancy-pricing-row fieldset {
        height: 100%;
        margin-bottom: 0;
    }

    .room-occupancy-pricing-row .row.g-2 > [class*="col-"] {
        min-width: 0;
    }

    .room-occupancy-pricing-row .form-text.base-cost-info,
    .room-occupancy-pricing-row .form-text.base-price-info {
        font-size: 0.8rem;
        line-height: 1.2;
        margin-top: 0.25rem;
        margin-bottom: 0;
        word-break: break-word;
    }

    .room-price-pair .form-text {
        min-height: 1.25rem;
        margin-top: 0.25rem;
        margin-bottom: 0;
    }

    #room-profit-helper-row {
        align-items: flex-start;
    }

    #room-profit-helper-row > [class*="col-"] {
        display: flex;
        flex-direction: column;
    }

    #room-profit-helper-row .form-label {
        min-height: 1.5rem;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: flex-end;
        line-height: 1.2;
    }

    #room-profit-helper-row .form-select,
    #room-profit-helper-row .form-control {
        height: 2.5rem;
        min-height: 2.5rem;
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
        line-height: 1.5;
        overflow: visible;
    }

    #room-profit-helper-row .form-select {
        padding-right: 2.25rem;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
    }

    #room-profit-helper-row .js-room-profit-amount-hint {
        min-height: 1.2rem;
        margin-top: 0.25rem;
        font-size: 0.8rem;
        line-height: 1.2;
    }

    .room-meal-row {
        align-items: flex-start;
    }

    .room-meal-toggle-box {
        min-height: 38px;
        display: flex;
        align-items: center;
        padding-top: 0.4rem;
        padding-bottom: 0.4rem;
    }
</style>
@endsection
@section('content')
@php
    $costOrSell = function ($cost, $sell) {
        $cost = (float) $cost;
        return $cost > 0 ? $cost : (float) $sell;
    };
    $fmtPrice = function ($value) {
        if ($value === null || $value === '') {
            return '';
        }
        return number_format((float) $value, 2, '.', '');
    };
    $baseRoomPricing = $baseRoomPricing ?? [
        'weekday_price' => (float) optional($baseRoom ?? null)->weekday_price,
        'weekend_price' => (float) optional($baseRoom ?? null)->weekend_price,
        'double_weekday_price' => (float) optional($baseRoom ?? null)->double_weekday_price,
        'double_weekend_price' => (float) optional($baseRoom ?? null)->double_weekend_price,
        'weekday_cost_price' => $costOrSell(optional($baseRoom ?? null)->weekday_cost_price, optional($baseRoom ?? null)->weekday_price),
        'weekend_cost_price' => $costOrSell(optional($baseRoom ?? null)->weekend_cost_price, optional($baseRoom ?? null)->weekend_price),
        'double_weekday_cost_price' => $costOrSell(optional($baseRoom ?? null)->double_weekday_cost_price, optional($baseRoom ?? null)->double_weekday_price),
        'double_weekend_cost_price' => $costOrSell(optional($baseRoom ?? null)->double_weekend_cost_price, optional($baseRoom ?? null)->double_weekend_price),
    ];
    $isThisBaseRoom = ((float) ($room->base_room ?? 0)) > 0;
    $inheritBaseProfit = (bool) ($inheritBaseProfit ?? false);
    $savedProfitType = $defaultProfitType ?? ($room->profit_type ?? null);
    $savedProfitAmount = $defaultProfitAmount ?? ($room->profit_amount ?? null);
    if ($inheritBaseProfit && !$isThisBaseRoom) {
        $savedProfitType = optional($baseRoom ?? null)->profit_type ?? $savedProfitType;
        $savedProfitAmount = optional($baseRoom ?? null)->profit_amount ?? $savedProfitAmount;
    }
    $defaultProfitType = strtolower((string) old('profit_type', $savedProfitType ?? 'percentage'));
    $defaultProfitType = in_array($defaultProfitType, ['percentage', 'flat'], true) ? $defaultProfitType : 'percentage';
    $defaultProfitAmount = old('profit_amount', $savedProfitAmount ?? 0);
    if (is_numeric($defaultProfitAmount)) {
        $defaultProfitAmount = number_format((float) $defaultProfitAmount, 2, '.', '');
    }
@endphp

<div class="content-wrapper">
    <x-alert />
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                <span class="d-flex align-items-center flex-wrap gap-2">
                    Edit Room Category
                    @php
                        $editRoomHotel = is_iterable($hotel ?? null)
                            ? collect($hotel)->firstWhere('hotel_unique_id', $room->hotel_id)
                            : ($hotel ?? null);
                    @endphp
                    <x-currency-price-note :country="$editRoomHotel->country ?? null" />
                </span>
                <a href="javascript:history.back()" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form id="hotelForm" method="POST" action="{{ route('room.update') }}" enctype="multipart/form-data"
                class="card-body">
                @csrf
                <input type="hidden" value="{{ $room->room_id }}" name="room_id" class="form-control"
                    placeholder="Enter Room Category"></input>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="hotel_id" class="form-label">
                            <strong>Hotel</strong>
                            <span style="color: red; font-weight: bold;">*</span>
                        </label>
                        <!-- <select name="hotel_id" id="hotel_id" class="form-control" required readonly>
                            <option value="">Select a Hotel</option>
                            @foreach($hotel as $h)
                            <option value="{{ $h->hotel_unique_id }}"
                                {{ $room->hotel_id == $h->hotel_unique_id ? 'selected' : '' }}>
                                {{ $h->name }} 
                            </option>
                            @endforeach
                        </select> -->
                        <input type="hidden" name="hotel_id" value="{{ $room->hotel_id }}">
                        <input type="text" class="form-control" value="{{ $hotel->where('hotel_unique_id', $room->hotel_id)->first()->name ?? '' }}" readonly>
                    </div>

                    <!-- Base Room Category -->
                    <div class="col-md-3 mb-3" id="base_room_type" style="display: none;">
                        <label for="base_room_type_input" class="form-label"><strong>Base Room
                                Category</strong><span class="text-danger">*</span></label>
                        <input id="base_room_type_input" value="" name="base_room_type" class="form-control"
                            placeholder="Enter Room Category"
                            {{ !in_array($auth_user->role_id, [1, 20]) ? 'readonly' : '' }}
                            style="{{ !in_array($auth_user->role_id, [1, 20]) ? 'background-color:#f8f9fa;cursor:not-allowed;' : '' }}">
                        @if(!in_array($auth_user->role_id, [1, 20]))
                            <small class="text-muted">(Only admin can modify room category)</small>
                        @endif
                        @error('base_room_type')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Room Category -->
                    <div class="col-md-3 mb-3" id="room_type" style="display: none;">
                        <label for="room_type" class="form-label"><strong>Room Category</strong><span
                                class="text-danger">*</span></label>
                        <input value="" name="room_type" id="room_type_input" class="form-control"
                            placeholder="Enter Room Category"
                            {{ !in_array($auth_user->role_id, [1, 20]) ? 'readonly' : '' }}
                            style="{{ !in_array($auth_user->role_id, [1, 20]) ? 'background-color:#f8f9fa;cursor:not-allowed;' : '' }}">
                        @if(!in_array($auth_user->role_id, [1, 20]))
                            <small class="text-muted">(Only admin can modify room category)</small>
                        @endif
                        @error('room_type')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Variant Price -->
                    <div class="col-md-3 mb-3" id="varient_price" style="display: none;">
                        <label for="varient_price_input" class="form-label"><strong>Room Rate
                                Variant</strong><span class="text-danger">*</span></label>
                        <input name="varient_price" id="varient_price_input" class="form-control" type="number" step="0.01"
                            placeholder="Enter Variant Price">
                        @error('varient_price')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Number of Rooms -->
                    <div class="col-md-3 mb-3">
                        <label for="total_rooms" class="form-label"><strong>Total No of Rooms</strong><span
                                class="text-danger">*</span></label>
                        <input value="{{$room->no_of_room}}" type="text" class="form-control" name="total_no_of_room"
                               id="total_rooms" placeholder="Enter Number of Rooms"
                               oninput="validateTotalRooms(this)" required>
                        <small class="validation-message text-danger" id="total_rooms-validation-message"></small>
                        @error('base_no_of_room')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- dimension -->
                    <div class="mb-3 col-md-3" id="dimension">
                        <label for="dimension_input" class="form-label"><strong>Dimension</strong></label>
                        <input value="{{$room->dimension}}" type="number" name="dimension" id="dimension_input" class="form-control"
                               placeholder="Enter Dimension"
                               {{ !in_array($auth_user->role_id, [1, 20]) ? 'readonly' : '' }}
                               style="{{ !in_array($auth_user->role_id, [1, 20]) ? 'background-color:#f8f9fa;cursor:not-allowed;' : '' }}">
                        @if(!in_array($auth_user->role_id, [1, 20]))
                            <small class="text-muted">(Only admin can modify dimension)</small>
                        @endif
                        <small class="validation-message text-danger" id="dimension_input-validation-message"></small>
                    </div>
                </div>

                <div class="mb-3 row g-3" id="room-profit-helper-row">
                    <div class="col-md-3">
                        <label for="room_profit_margin" class="form-label"><strong>Profit (margin)</strong></label>
                        <select id="room_profit_margin" name="profit_type" class="form-select js-room-profit-type">
                            <option value="percentage" {{ $defaultProfitType === 'percentage' ? 'selected' : '' }}>%</option>
                            <option value="flat" {{ $defaultProfitType === 'flat' ? 'selected' : '' }}>Flat</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="room_profit_amount" class="form-label js-room-profit-amount-label"><strong>{{ $defaultProfitType === 'percentage' ? 'Profit percentage' : 'Profit amount' }}</strong></label>
                        <input type="number" id="room_profit_amount" name="profit_amount" class="form-control js-room-profit-amount"
                               value="{{ $defaultProfitAmount }}" min="0" step="0.01"
                               placeholder="{{ $defaultProfitType === 'percentage' ? 'Enter profit percentage' : 'Enter profit amount' }}">
                        <small class="text-muted js-room-profit-amount-hint">{{ $defaultProfitType === 'percentage' ? 'Sell = cost + (cost × percentage / 100)' : 'Sell = cost + flat profit amount' }}</small>
                    </div>
                </div>

                <div class="mb-3 row room-child-pricing-row g-2">
                    <!-- Children Price -->
                    <div class="col mb-3">
                        <label for="children_breakfast_price" class="form-label"><strong>Meal 
                                Children Price</strong></label>
                        <select name="children_price" id="children_breakfast_price" class="form-control">
                            <option value="">Please Select One</option>
                            <option {{$room->children_price == 0 ? 'selected' : ''}} value="0">Free
                            </option>
                            <option {{$room->children_price == 1 ? 'selected' : ''}} value="1">Half
                                Price</option>
                            <option {{$room->children_price == 2 ? 'selected' : ''}} value="2">Full
                                Price</option>
                        </select>
                    </div>
                    <!-- Child with bed: Cost then Sell -->
                    <div class="col mb-3">
                        <label for="child_with_bed_cost" class="form-label"><strong>Child with Bed Price(Cost)</strong></label>
                        <input type="number" name="child_with_bed_cost" id="child_with_bed_cost" class="form-control js-room-cost" data-sell-target="child_with_bed" placeholder="Enter Cost Price" min="0" step="0.01" value="{{ $room->child_with_bed_cost ?? '' }}">
                    </div>
                    <div class="col mb-3">
                        <label for="child_with_bed" class="form-label"><strong>Child with Bed Price(Sell)</strong></label>
                        <input type="number" name="child_with_bed" id="child_with_bed" class="form-control js-room-sell" placeholder="Enter Sell Price" min="0" step="0.01" value="{{ $room->child_with_bed ?? '' }}">
                    </div>
                    <!-- Child without bed: Cost then Sell -->
                    <div class="col mb-3">
                        <label for="child_without_bed_cost" class="form-label"><strong>Child without Bed Price(Cost)</strong></label>
                        <input type="number" name="child_without_bed_cost" id="child_without_bed_cost" class="form-control js-room-cost" data-sell-target="child_without_bed" placeholder="Enter Cost Price" min="0" step="0.01" value="{{ $room->child_without_bed_cost ?? '' }}">
                    </div>
                    <div class="col mb-3">
                        <label for="child_without_bed" class="form-label"><strong>Child without Bed Price(Sell)</strong></label>
                        <input type="number" name="child_without_bed" id="child_without_bed" class="form-control js-room-sell" placeholder="Enter Sell Price" min="0" step="0.01" value="{{ $room->child_without_bed ?? '' }}">
                    </div>
                </div>

                <div id="room-pricing-alert" class="mb-3"></div>

                <div class="mb-3 row room-occupancy-pricing-row" id="variant_pricing_row" style="display: none;"
                    data-weekday-price="{{ $baseRoomPricing['weekday_price'] }}"
                    data-weekend-price="{{ $baseRoomPricing['weekend_price'] }}"
                    data-double-weekday-price="{{ $baseRoomPricing['double_weekday_price'] }}"
                    data-double-weekend-price="{{ $baseRoomPricing['double_weekend_price'] }}"
                    data-weekday-cost="{{ $baseRoomPricing['weekday_cost_price'] }}"
                    data-weekend-cost="{{ $baseRoomPricing['weekend_cost_price'] }}"
                    data-double-weekday-cost="{{ $baseRoomPricing['double_weekday_cost_price'] }}"
                    data-double-weekend-cost="{{ $baseRoomPricing['double_weekend_cost_price'] }}">
                    <!-- Single weekday weekend price -->
                    <div class="col-md-6" id="single_price">
                        <div class="mb-3">
                            <fieldset class="border p-1 position-relative">
                                <legend>Single</legend>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                        <input type="text" id="singleWeekdayCostPrice" name="singleWeekdayCostPrice" class="form-control js-room-cost" data-sell-target="singleWeekdayPrice" placeholder=" " value="{{ $fmtPrice($room->weekday_cost_price) }}">
                                        <label for="singleWeekdayCostPrice">Weekday Price(Cost)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                        <input type="text" id="singleWeekdayPrice" name="singleWeekdayPrice" class="form-control js-room-sell" placeholder=" " value="{{ $fmtPrice($room->weekday_price) }}" onkeyup="calculatePrice()">
                                        <label for="singleWeekdayPrice">Weekday Price(Sell)</label>
                                        </div>
                                        @if($auth_user->user_type == 2)
                                        <span class="text-primary" style="font-size: 10px;">calculated price: <span id="totalSingleWeekdayPrice">{{ $single_weekday_price }}</span></span>
                                        @endif
                                        <div class="calculation-display text-primary small mt-1" id="single-weekday-calc" style="display: none;"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                        <input type="text" id="singleWeekendCostPrice" name="singleWeekendCostPrice" class="form-control js-room-cost" data-sell-target="singleWeekendPrice" placeholder=" " value="{{ $fmtPrice($room->weekend_cost_price) }}">
                                        <label for="singleWeekendCostPrice">Weekend Price(Cost)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                        <input type="text" id="singleWeekendPrice" name="singleWeekendPrice" class="form-control js-room-sell" placeholder=" " value="{{ $fmtPrice($room->weekend_price) }}" onkeyup="calculatePrice()">
                                        <label for="singleWeekendPrice">Weekend Price(Sell)</label>
                                        </div>
                                        @if($auth_user->user_type == 2)
                                        <span class="text-primary" style="font-size: 10px;">calculated price: <span id="totalSingleWeekendPrice">{{ $single_weekend_price }}</span></span>
                                        @endif
                                        <div class="calculation-display text-primary small mt-1" id="single-weekend-calc" style="display: none;"></div>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>

                    <!-- Double weekday weekend price -->
                    <div class="col-md-6" id="double_price">
                        <div class="mb-3">
                            <fieldset class="border p-1 position-relative">
                                <legend>Double</legend>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                        <input type="text" id="doubleWeekdayCostPrice" name="doubleWeekdayCostPrice" class="form-control js-room-cost" data-sell-target="doubleWeekdayPrice" placeholder=" " value="{{ $fmtPrice($room->double_weekday_cost_price) }}">
                                        <label for="doubleWeekdayCostPrice">Weekday Price(Cost)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                        <input type="text" id="doubleWeekdayPrice" name="doubleWeekdayPrice" class="form-control js-room-sell" placeholder=" " value="{{ $fmtPrice($room->double_weekday_price) }}" onkeyup="calculatePrice()">
                                        <label for="doubleWeekdayPrice">Weekday Price(Sell)</label>
                                        </div>
                                        @if($auth_user->user_type == 2)
                                        <span class="text-primary" style="font-size: 10px;">calculated price: <span id="totalDoubleWeekdayPrice">{{ $double_weekday_price }}</span></span>
                                        @endif
                                        <div class="calculation-display text-primary small mt-1" id="double-weekday-calc" style="display: none;"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                        <input type="text" id="doubleWeekendCostPrice" name="doubleWeekendCostPrice" class="form-control js-room-cost" data-sell-target="doubleWeekendPrice" placeholder=" " value="{{ $fmtPrice($room->double_weekend_cost_price) }}">
                                        <label for="doubleWeekendCostPrice">Weekend Price(Cost)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                        <input type="text" id="doubleWeekendPrice" name="doubleWeekendPrice" class="form-control js-room-sell" placeholder=" " value="{{ $fmtPrice($room->double_weekend_price) }}" onkeyup="calculatePrice()">
                                        <label for="doubleWeekendPrice">Weekend Price(Sell)</label>
                                        </div>
                                        @if($auth_user->user_type == 2)
                                        <span class="text-primary" style="font-size: 10px;">calculated price: <span id="totalDoubleWeekendPrice">{{ $double_weekend_price }}</span></span>
                                        @endif
                                        <div class="calculation-display text-primary small mt-1" id="double-weekend-calc" style="display: none;"></div>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </div>

                <div class="mb-3 row room-occupancy-pricing-row" id="base_pricing_row">
                    <!-- Base Single weekday weekend -->
                    <div class="col-md-6" id="base_single_price">
                        <div class="mb-3">
                            <fieldset class="border p-1 position-relative">
                                <legend>Single</legend>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                        <input type="text" id="baseSingleWeekdayCostPrice" name="baseSingleWeekdayCostPrice" class="form-control js-room-cost" data-sell-target="baseSingleWeekdayPrice" placeholder=" " value="{{ $fmtPrice($room->weekday_cost_price) }}">
                                        <label for="baseSingleWeekdayCostPrice">Base Weekday Price(Cost)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                        <input type="text" id="baseSingleWeekdayPrice" name="baseSingleWeekdayPrice" class="form-control js-room-sell" placeholder=" " value="{{ $fmtPrice($room->weekday_price) }}" onkeyup="calculatePrice()">
                                        <label for="baseSingleWeekdayPrice">Base Weekday Price(Sell)</label>
                                        </div>
                                        @if($auth_user->user_type == 2)
                                        <span class="text-primary" style="font-size: 10px;">calculated price: <span id="totalWeekdayPrice"> {{ $single_weekday_price }}</span></span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                        <input type="text" id="baseSingleWeekendCostPrice" name="baseSingleWeekendCostPrice" class="form-control js-room-cost" data-sell-target="baseSingleWeekendPrice" placeholder=" " value="{{ $fmtPrice($room->weekend_cost_price) }}">
                                        <label for="baseSingleWeekendCostPrice">Base Weekend Price(Cost)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                        <input type="text" id="baseSingleWeekendPrice" name="baseSingleWeekendPrice" class="form-control js-room-sell" placeholder=" " value="{{ $fmtPrice($room->weekend_price) }}" onkeyup="calculatePrice()">
                                        <label for="baseSingleWeekendPrice">Base Weekend Price(Sell)</label>
                                        </div>
                                        @if($auth_user->user_type == 2)
                                        <span class="text-primary" style="font-size: 10px;">calculated price: <span id="totalWeekendPrice">{{ $single_weekend_price }}</span></span>
                                        @endif
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>

                    <!-- Base Double weekday weekend -->
                    <div class="col-md-6" id="base_double_price">
                        <div class="mb-3">
                            <fieldset class="border p-1 position-relative">
                                <legend>Double</legend>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                        <input type="text" id="baseDoubleWeekdayCostPrice" name="baseDoubleWeekdayCostPrice" class="form-control js-room-cost" data-sell-target="baseDoubleWeekdayPrice" placeholder=" " value="{{ $fmtPrice($room->double_weekday_cost_price) }}">
                                        <label for="baseDoubleWeekdayCostPrice">Base Weekday Price(Cost)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                        <input type="text" id="baseDoubleWeekdayPrice" name="baseDoubleWeekdayPrice" class="form-control js-room-sell" placeholder=" " value="{{ $fmtPrice($room->double_weekday_price) }}" onkeyup="calculatePrice()">
                                        <label for="baseDoubleWeekdayPrice">Base Weekday Price(Sell)</label>
                                        </div>
                                        @if($auth_user->user_type == 2)
                                        <span class="text-primary" style="font-size: 10px;">calculated price: <span id="totalBaseDoubleWeekdayPrice">{{ $double_weekday_price }}</span></span>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                        <input type="text" id="baseDoubleWeekendCostPrice" name="baseDoubleWeekendCostPrice" class="form-control js-room-cost" data-sell-target="baseDoubleWeekendPrice" placeholder=" " value="{{ $fmtPrice($room->double_weekend_cost_price) }}">
                                        <label for="baseDoubleWeekendCostPrice">Base Weekend Price(Cost)</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                        <input type="text" id="baseDoubleWeekendPrice" name="baseDoubleWeekendPrice" class="form-control js-room-sell" placeholder=" " value="{{ $fmtPrice($room->double_weekend_price) }}" onkeyup="calculatePrice()">
                                        <label for="baseDoubleWeekendPrice">Base Weekend Price(Sell)</label>
                                        </div>
                                        @if($auth_user->user_type == 2)
                                        <span class="text-primary" style="font-size: 10px;">calculated price: <span id="totalBaseDoubleWeekendPrice">{{ $double_weekend_price }}</span></span>
                                        @endif
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>
                </div>

                <!-- Meal Options Section -->
                <div class="mb-3 row g-3 room-meal-row">
                        <div class="col-md-3">
                            <label for="breakfast_included" class="form-label"><strong>Breakfast Available</strong></label>
                            <select name="breakfast_included" id="breakfast_included" class="form-control" onchange="toggleMealOptions('breakfast')">
                                <option value="">Select One</option>
                                <option value="1" {{ $room->breakfast ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ !$room->breakfast ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="supplementary_breakfast" class="form-label"><strong>Comp. Breakfast</strong></label>
                            <div class="form-control room-meal-toggle-box">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="supplementary_breakfast"
                                           id="supplementary_breakfast" value="1"
                                           {{ $room->breakfast_included ? 'checked' : '' }}>
                                    <label class="form-check-label" for="supplementary_breakfast">Included</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 breakfast-options" style="display: none;">
                            <label for="breakfast_type" class="form-label"><strong>Type</strong><span class="text-danger">*</span></label>
                            <select name="breakfast_type" id="breakfast_type" class="form-control">
                                <option value="">Select Type</option>
                                <option value="Buffet" {{ $room->breakfast_type == 'Buffet' ? 'selected' : '' }}>Buffet</option>
                                <option value="Set Menu" {{ $room->breakfast_type == 'Set Menu' ? 'selected' : '' }}>Set Menu</option>
                            </select>
                        </div>
                        <div class="col-md-2 breakfast-options" style="display: none;">
                            <label for="breakfast_cost_price" class="form-label"><strong>Cost Price</strong></label>
                            <input type="number" name="breakfast_cost_price" id="breakfast_cost_price" class="form-control js-room-cost"
                                   data-sell-target="breakfast_price"
                                   placeholder="Enter Cost Price" min="0" step="0.01"
                                   value="{{ $room->breakfast_cost_price }}">
                        </div>
                        <div class="col-md-3 breakfast-options" style="display: none;">
                            <label for="breakfast_price" class="form-label"><strong>Sell Price</strong><span class="text-danger">*</span></label>
                            <input type="number" name="breakfast_price" id="breakfast_price" class="form-control js-room-sell"
                                   placeholder="Enter Sell Price" min="0" step="0.01"
                                   value="{{ $room->breakfast_price }}">
                        </div>
                </div>

                <div class="mb-3 row g-3 room-meal-row">
                        <div class="col-md-3">
                            <label for="lunch_included" class="form-label"><strong>Lunch Available</strong></label>
                            <select name="lunch_included" id="lunch_included" class="form-control" onchange="toggleMealOptions('lunch')">
                                <option value="">Select One</option>
                                <option value="1" {{ $room->lunch ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ !$room->lunch ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="col-md-3 lunch-options" style="display: none;">
                            <label for="lunch_type" class="form-label"><strong>Type</strong><span class="text-danger">*</span></label>
                            <select name="lunch_type" id="lunch_type" class="form-control">
                                <option value="">Select Type</option>
                                <option value="Buffet" {{ $room->lunch_type == 'Buffet' ? 'selected' : '' }}>Buffet</option>
                                <option value="Set Menu" {{ $room->lunch_type == 'Set Menu' ? 'selected' : '' }}>Set Menu</option>
                            </select>
                        </div>
                        <div class="col-md-3 lunch-options" style="display: none;">
                            <label for="lunch_cost_price" class="form-label"><strong>Cost Price</strong></label>
                            <input type="number" name="lunch_cost_price" id="lunch_cost_price" class="form-control js-room-cost"
                                   data-sell-target="lunch_price"
                                   placeholder="Enter Cost Price" min="0" step="0.01"
                                   value="{{ $room->lunch_cost_price }}">
                        </div>
                        <div class="col-md-3 lunch-options" style="display: none;">
                            <label for="lunch_price" class="form-label"><strong>Sell Price</strong><span class="text-danger">*</span></label>
                            <input type="number" name="lunch_price" id="lunch_price" class="form-control js-room-sell"
                                   placeholder="Enter Sell Price" min="0" step="0.01"
                                   value="{{ $room->lunch_price }}">
                        </div>
                </div>

                <div class="mb-3 row g-3 room-meal-row">
                        <div class="col-md-3">
                            <label for="dinner_included" class="form-label"><strong>Dinner Available</strong></label>
                            <select name="dinner_included" id="dinner_included" class="form-control" onchange="toggleMealOptions('dinner')">
                                <option value="">Select One</option>
                                <option value="1" {{ $room->dinner ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ !$room->dinner ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="col-md-3 dinner-options" style="display: none;">
                            <label for="dinner_type" class="form-label"><strong>Type</strong><span class="text-danger">*</span></label>
                            <select name="dinner_type" id="dinner_type" class="form-control">
                                <option value="">Select Type</option>
                                <option value="Buffet" {{ $room->dinner_type == 'Buffet' ? 'selected' : '' }}>Buffet</option>
                                <option value="Set Menu" {{ $room->dinner_type == 'Set Menu' ? 'selected' : '' }}>Set Menu</option>
                            </select>
                        </div>
                        <div class="col-md-3 dinner-options" style="display: none;">
                            <label for="dinner_cost_price" class="form-label"><strong>Cost Price</strong></label>
                            <input type="number" name="dinner_cost_price" id="dinner_cost_price" class="form-control js-room-cost"
                                   data-sell-target="dinner_price"
                                   placeholder="Enter Cost Price" min="0" step="0.01"
                                   value="{{ $room->dinner_cost_price }}">
                        </div>
                        <div class="col-md-3 dinner-options" style="display: none;">
                            <label for="dinner_price" class="form-label"><strong>Sell Price</strong><span class="text-danger">*</span></label>
                            <input type="number" name="dinner_price" id="dinner_price" class="form-control js-room-sell"
                                   placeholder="Enter Sell Price" min="0" step="0.01"
                                   value="{{ $room->dinner_price }}">
                        </div>
                </div>

                    @if(in_array($auth_user->role_id, [1, 20]))
                    <!-- Image sections - Only visible to admin users -->
                    <div class="row col-md-12">
                        <!-- Master image -->
                        <div class="mt-3 mb-3 col-md-4">
                            <div>
                                <label for="master_image" class="form-label"><strong>Master
                                        Image</strong><span style="color: red; font-weight: bold;">*</span></label>
                                <div id="master-drop-area" class="form-control"
                                    style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px;">
                                    Drag & Drop your files here or click to upload.
                                    <input type="file" id="master_image" name="master_image"
                                        accept="image/jpeg,image/png,image/webp,image/gif"
                                        style="display: none;">
                                </div>
                            </div>
                            <div id="master-preview-container" class="mb-3 mt-3 d-flex flex-wrap gap-2"
                                style="max-width: 30%; overflow-x: auto; white-space: nowrap;"></div>

                            @if($room->master_image)
                            <div class="existing-master-image-preview-container d-flex flex-wrap gap-2">
                                <div class="existing-master-image-preview-wrapper position-relative">
                                    <img src="{{$room->master_image}}" alt="Room Master Image"
                                        style="max-width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                                    <button class="delete-image-btn position-absolute top-0 end-0 btn btn-sm btn-danger"
                                        data-image="{{ $room->master_image }}"
                                        style="width: 20px; height: 20px; line-height: 18px; padding: 0; text-align: center; font-size: 14px; z-index: 1;">
                                        &times;
                                    </button>
                                </div>
                            </div>
                            @endif
                            @error('master_image')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Additional Image drop -->
                        <div class="mt-3 mb-3 col-md-8">
                            <div>
                                <label for="images" class="form-label"><strong>Additional
                                        Images</strong></label>
                                <div id="drop-area" class="form-control"
                                    style="padding: 20px; border: 2px dashed #007bff; text-align: center; height: 80px;">
                                    Drag & Drop your files here or click to upload.
                                    <input type="file" id="images" multiple accept="image/jpeg,image/png,image/webp,image/gif" style="display: none;">
                                </div>

                                <div id="preview-container" class="mb-3 mt-3 d-flex flex-wrap gap-2"
                                    style="max-width: 100%; overflow-x: auto; white-space: nowrap;"></div>
                            </div>

                            <!-- Existing Image Section -->
                            <div class="existing-image-preview-container d-flex flex-wrap gap-2">
                                @php
                                $images = json_decode($room->images, true);
                                if (!is_array($images)) {
                                    $images = [];
                                }
                                @endphp
                                @foreach($images as $img)
                                <!-- Hidden input to hold existing image path -->
                                <div class="existing-image-preview-wrapper position-relative">
                                    <input type="hidden" name="existing_images[]" value="{{ $img }}">
                                    <img src="{{ asset($img) }}" alt="Facility Image"
                                        style="max-width: 100px; height: 100px; object-fit: cover; border: 1px solid #ddd; border-radius: 5px;">
                                    <button class="delete-image-btn position-absolute top-0 end-0 btn btn-sm btn-danger"
                                        data-image="{{ $img }}"
                                        style="width: 20px; height: 20px; line-height: 18px; padding: 0; text-align: center; font-size: 14px; z-index: 1;">
                                        &times;
                                    </button>
                                </div>
                                @endforeach
                            </div>
                            <input type="file" name="all_images[]" id="all-images" multiple accept="image/jpeg,image/png,image/webp,image/gif" style="display: none;">

                            @error('images')
                            <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                            <div id="preview-container" class="mt-3 d-flex flex-wrap gap-2"
                                style="max-width: 100%; overflow-x: auto; white-space: nowrap;"></div>
                        </div>
                    </div>
                    @else
                    <!-- Hidden image inputs for DMC users -->
                    <input type="hidden" name="master_image" value="{{ $room->master_image }}">
                    <input type="hidden" name="existing_images[]" value="{{ json_encode($room->images) }}">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Image management is only available for admin users.
                    </div>
                    @endif

                <!-- Status -->
                <div class="form-check form-switch">
                    <label for="room_status" class="form-label"><strong>Status</strong></label>
                    <span style="color: red; font-weight: bold;">*</span>
                    <input {{$room->status == 1 ? 'checked' : ''}} class="form-check-input" name="room_status"
                        type="checkbox" id="room_status" value="1">
                    <label class="form-check-label"></label>
                    @error('room_status')
                    <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Submit Buttons -->
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary px-4">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prevent modification of readonly fields for non-admin users
    const isAdmin = {{ in_array($auth_user->role_id, [1, 20]) ? 'true' : 'false' }};
    
    if (!isAdmin) {
        // Prevent any modification attempts on readonly fields
        $('#base_room_type_input, #room_type_input, #dimension_input').on('keydown paste drop', function(e) {
            e.preventDefault();
            return false;
        });
        
        // Show tooltip when trying to modify readonly fields
        $('#base_room_type_input, #room_type_input, #dimension_input').on('click', function() {
            const $this = $(this);
            if (!$this.next('.tooltip').length) {
                $('<div class="tooltip">Only admin can modify this field</div>')
                    .insertAfter($this)
                    .fadeIn()
                    .delay(2000)
                    .fadeOut(function() { $(this).remove(); });
            }
        });
    }
    
    const room = @json($room);
    const baseRoom = @json($baseRoom);
    const serverBasePricing = @json($baseRoomPricing ?? null);
    const isEditingBaseRoom = Number(room.base_room) > 0;

    function syncRoomProfitAmountLabel() {
        const typeEl = document.querySelector('.js-room-profit-type');
        const labelEl = document.querySelector('.js-room-profit-amount-label');
        const amountEl = document.querySelector('.js-room-profit-amount');
        const hintEl = document.querySelector('.js-room-profit-amount-hint');
        const isPercent = !typeEl || typeEl.value !== 'flat';
        if (labelEl) {
            labelEl.innerHTML = '<strong>' + (isPercent ? 'Profit percentage' : 'Profit amount') + '</strong>';
        }
        if (amountEl) {
            amountEl.placeholder = isPercent ? 'Enter profit percentage' : 'Enter profit amount';
        }
        if (hintEl) {
            hintEl.textContent = isPercent
                ? 'Sell = cost + (cost × percentage / 100)'
                : 'Sell = cost + flat profit amount';
        }
    }
    window.syncRoomProfitAmountLabel = syncRoomProfitAmountLabel;
    syncRoomProfitAmountLabel();
    const standardPrices = {
        singleWeekday: 0,
        singleWeekend: 0,
        doubleWeekday: 0,
        doubleWeekend: 0
    };
    const standardCosts = {
        singleWeekday: 0,
        singleWeekend: 0,
        doubleWeekday: 0,
        doubleWeekend: 0
    };

    function pickNumeric(obj, keys) {
        if (!obj) return 0;
        for (let i = 0; i < keys.length; i++) {
            const raw = obj[keys[i]];
            if (raw === undefined || raw === null || raw === '') continue;
            const n = parseFloat(raw);
            if (!isNaN(n)) return n;
        }
        return 0;
    }

    function costOrSell(cost, sell) {
        const c = parseFloat(cost);
        if (!isNaN(c) && c > 0) return c;
        return parseFloat(sell) || 0;
    }

    function roundOccupancy(n) {
        return Math.round((Number(n) + Number.EPSILON) * 100) / 100;
    }

    function occupancyProfitSettings() {
        const typeEl = document.querySelector('.js-room-profit-type');
        const amountEl = document.querySelector('.js-room-profit-amount');
        return {
            type: typeEl ? typeEl.value : 'percentage',
            amount: parseFloat(amountEl ? amountEl.value : 0) || 0
        };
    }

    function applyProfitToSellBase(base, type, amount) {
        const b = parseFloat(base) || 0;
        const a = parseFloat(amount) || 0;
        if (a <= 0) return roundOccupancy(b);
        if (type === 'flat') return roundOccupancy(b + a);
        return roundOccupancy(b + (b * a / 100));
    }

    function occupancyInput(id, sellTarget) {
        return document.getElementById(id)
            || document.querySelector('[name="' + id + '"]')
            || (sellTarget ? document.querySelector('.js-room-cost[data-sell-target="' + sellTarget + '"]') : null);
    }

    function setOccupancyInput(id, value, makeReadonly, sellTarget) {
        const formatted = (parseFloat(value) || 0).toFixed(2);
        const seen = new Set();
        const add = function (el) {
            if (!el || seen.has(el)) return;
            seen.add(el);
            el.value = formatted;
            el.readOnly = false;
            el.classList.remove('bg-light');
        };
        add(document.getElementById(id));
        document.querySelectorAll('[name="' + id + '"]').forEach(add);
        if (sellTarget) {
            document.querySelectorAll('.js-room-cost[data-sell-target="' + sellTarget + '"]').forEach(add);
        }
    }

    function setOccupancyHint(inputId, className, html, dataBase, sellTarget) {
        const el = occupancyInput(inputId, sellTarget);
        if (!el) return;
        const col = el.closest('.col-md-6') || el.parentElement;
        const uniqueClass = className + '-' + inputId;
        let hint = col.querySelector('.' + uniqueClass);
        if (!hint) {
            hint = document.createElement('div');
            hint.className = 'form-text text-info ' + className + ' ' + uniqueClass;
            col.appendChild(hint);
        }
        hint.dataset.base = dataBase;
        hint.innerHTML = html;
    }

    function loadBaseOccupancyFrom(source) {
        if (!source) return false;
        standardPrices.singleWeekday = pickNumeric(source, ['weekday_price', 'weekdayPrice', 'singleWeekdayPrice']);
        standardPrices.singleWeekend = pickNumeric(source, ['weekend_price', 'weekendPrice', 'singleWeekendPrice']);
        standardPrices.doubleWeekday = pickNumeric(source, ['double_weekday_price', 'doubleWeekdayPrice']);
        standardPrices.doubleWeekend = pickNumeric(source, ['double_weekend_price', 'doubleWeekendPrice']);
        standardCosts.singleWeekday = costOrSell(
            pickNumeric(source, ['weekday_cost_price', 'weekdayCostPrice', 'singleWeekdayCostPrice']),
            standardPrices.singleWeekday
        );
        standardCosts.singleWeekend = costOrSell(
            pickNumeric(source, ['weekend_cost_price', 'weekendCostPrice', 'singleWeekendCostPrice']),
            standardPrices.singleWeekend
        );
        standardCosts.doubleWeekday = costOrSell(
            pickNumeric(source, ['double_weekday_cost_price', 'doubleWeekdayCostPrice']),
            standardPrices.doubleWeekday
        );
        standardCosts.doubleWeekend = costOrSell(
            pickNumeric(source, ['double_weekend_cost_price', 'doubleWeekendCostPrice']),
            standardPrices.doubleWeekend
        );
        return true;
    }

    function loadBaseOccupancyFromRow() {
        const row = document.getElementById('variant_pricing_row');
        if (!row || !row.dataset) return false;
        return loadBaseOccupancyFrom({
            weekday_price: row.dataset.weekdayPrice,
            weekend_price: row.dataset.weekendPrice,
            double_weekday_price: row.dataset.doubleWeekdayPrice,
            double_weekend_price: row.dataset.doubleWeekendPrice,
            weekday_cost_price: row.dataset.weekdayCost,
            weekend_cost_price: row.dataset.weekendCost,
            double_weekday_cost_price: row.dataset.doubleWeekdayCost,
            double_weekend_cost_price: row.dataset.doubleWeekendCost
        });
    }

    function applyOccupancyFromBase(variantPrice, makeReadonly, options) {
        variantPrice = parseFloat(variantPrice) || 0;
        options = options || {};
        const updateCost = options.updateCost !== false;
        const updateSell = options.updateSell !== false;
        const newPrices = {
            singleWeekday: standardPrices.singleWeekday + variantPrice,
            singleWeekend: standardPrices.singleWeekend + variantPrice,
            doubleWeekday: standardPrices.doubleWeekday + variantPrice,
            doubleWeekend: standardPrices.doubleWeekend + variantPrice
        };
        const newCosts = {
            singleWeekday: standardCosts.singleWeekday + variantPrice,
            singleWeekend: standardCosts.singleWeekend + variantPrice,
            doubleWeekday: standardCosts.doubleWeekday + variantPrice,
            doubleWeekend: standardCosts.doubleWeekend + variantPrice
        };
        const profit = occupancyProfitSettings();
        const sellPrices = {
            singleWeekday: applyProfitToSellBase(newCosts.singleWeekday, profit.type, profit.amount),
            singleWeekend: applyProfitToSellBase(newCosts.singleWeekend, profit.type, profit.amount),
            doubleWeekday: applyProfitToSellBase(newCosts.doubleWeekday, profit.type, profit.amount),
            doubleWeekend: applyProfitToSellBase(newCosts.doubleWeekend, profit.type, profit.amount)
        };

        if (updateSell) {
            setOccupancyInput('singleWeekdayPrice', sellPrices.singleWeekday, makeReadonly);
            setOccupancyInput('singleWeekendPrice', sellPrices.singleWeekend, makeReadonly);
            setOccupancyInput('doubleWeekdayPrice', sellPrices.doubleWeekday, makeReadonly);
            setOccupancyInput('doubleWeekendPrice', sellPrices.doubleWeekend, makeReadonly);
        }
        if (updateCost) {
            setOccupancyInput('singleWeekdayCostPrice', newCosts.singleWeekday, makeReadonly, 'singleWeekdayPrice');
            setOccupancyInput('singleWeekendCostPrice', newCosts.singleWeekend, makeReadonly, 'singleWeekendPrice');
            setOccupancyInput('doubleWeekdayCostPrice', newCosts.doubleWeekday, makeReadonly, 'doubleWeekdayPrice');
            setOccupancyInput('doubleWeekendCostPrice', newCosts.doubleWeekend, makeReadonly, 'doubleWeekendPrice');
        }

        const op = variantPrice >= 0 ? '+' : '';
        const sellHint = function (costTotal, sellTotal) {
            let text = 'Cost: ' + costTotal.toFixed(2);
            if (profit.amount > 0) {
                text += profit.type === 'flat'
                    ? (' + profit ' + profit.amount.toFixed(2) + ' = ' + sellTotal.toFixed(2))
                    : (' + profit ' + profit.amount.toFixed(2) + '% = ' + sellTotal.toFixed(2));
            }
            return text;
        };
        const costHint = function (base, total) {
            return variantPrice !== 0
                ? ('Base cost ' + base.toFixed(2) + ' ' + op + variantPrice.toFixed(2) + ' = ' + total.toFixed(2))
                : ('Base cost: ' + base.toFixed(2));
        };

        if (updateSell) {
            setOccupancyHint('singleWeekdayPrice', 'base-price-info', sellHint(newCosts.singleWeekday, sellPrices.singleWeekday), newCosts.singleWeekday.toFixed(2));
            setOccupancyHint('singleWeekendPrice', 'base-price-info', sellHint(newCosts.singleWeekend, sellPrices.singleWeekend), newCosts.singleWeekend.toFixed(2));
            setOccupancyHint('doubleWeekdayPrice', 'base-price-info', sellHint(newCosts.doubleWeekday, sellPrices.doubleWeekday), newCosts.doubleWeekday.toFixed(2));
            setOccupancyHint('doubleWeekendPrice', 'base-price-info', sellHint(newCosts.doubleWeekend, sellPrices.doubleWeekend), newCosts.doubleWeekend.toFixed(2));
        }
        if (updateCost) {
            setOccupancyHint('singleWeekdayCostPrice', 'base-cost-info', costHint(standardCosts.singleWeekday, newCosts.singleWeekday), standardCosts.singleWeekday.toFixed(2), 'singleWeekdayPrice');
            setOccupancyHint('singleWeekendCostPrice', 'base-cost-info', costHint(standardCosts.singleWeekend, newCosts.singleWeekend), standardCosts.singleWeekend.toFixed(2), 'singleWeekendPrice');
            setOccupancyHint('doubleWeekdayCostPrice', 'base-cost-info', costHint(standardCosts.doubleWeekday, newCosts.doubleWeekday), standardCosts.doubleWeekday.toFixed(2), 'doubleWeekdayPrice');
            setOccupancyHint('doubleWeekendCostPrice', 'base-cost-info', costHint(standardCosts.doubleWeekend, newCosts.doubleWeekend), standardCosts.doubleWeekend.toFixed(2), 'doubleWeekendPrice');
        }

        if (typeof calculatePrice === 'function') {
            try { calculatePrice(); } catch (e) {}
        }

        return { newPrices: newPrices, newCosts: newCosts, sellPrices: sellPrices };
    }

    window.refreshRoomOccupancySells = function () {
        const profit = occupancyProfitSettings();
        const pairs = [
            ['singleWeekdayCostPrice', 'singleWeekdayPrice'],
            ['singleWeekendCostPrice', 'singleWeekendPrice'],
            ['doubleWeekdayCostPrice', 'doubleWeekdayPrice'],
            ['doubleWeekendCostPrice', 'doubleWeekendPrice'],
            ['baseSingleWeekdayCostPrice', 'baseSingleWeekdayPrice'],
            ['baseSingleWeekendCostPrice', 'baseSingleWeekendPrice'],
            ['baseDoubleWeekdayCostPrice', 'baseDoubleWeekdayPrice'],
            ['baseDoubleWeekendCostPrice', 'baseDoubleWeekendPrice']
        ];
        pairs.forEach(function (pair) {
            const costEl = occupancyInput(pair[0], pair[1]);
            const sellEl = occupancyInput(pair[1]);
            if (!costEl || !sellEl || costEl.disabled) return;
            const section = costEl.closest('#variant_pricing_row, #base_pricing_row');
            if (section && section.offsetParent === null) return;
            const sellValue = applyProfitToSellBase(costEl.value, profit.type, profit.amount);
            setOccupancyInput(pair[1], sellValue, false);
            const uniqueClass = 'base-price-info-' + (sellEl.id || pair[1]);
            const col = sellEl.closest('.col-md-6') || sellEl.parentElement;
            if (col) {
                let hint = col.querySelector('.' + uniqueClass);
                if (!hint) {
                    hint = document.createElement('div');
                    hint.className = 'form-text text-primary base-price-info ' + uniqueClass;
                    col.appendChild(hint);
                }
                let text = 'Cost: ' + (parseFloat(costEl.value) || 0).toFixed(2);
                if (profit.amount > 0) {
                    text += profit.type === 'flat'
                        ? (' + profit ' + profit.amount.toFixed(2) + ' = ' + sellValue.toFixed(2))
                        : (' + profit ' + profit.amount.toFixed(2) + '% = ' + sellValue.toFixed(2));
                }
                hint.textContent = text;
            }
        });
        if (typeof calculatePrice === 'function') {
            try { calculatePrice(); } catch (e) {}
        }
    };

    function occupancyFieldEmpty(id) {
        const el = document.getElementById(id);
        const n = parseFloat(el ? el.value : '');
        return !el || el.value === '' || isNaN(n) || n <= 0;
    }

    loadBaseOccupancyFrom(baseRoom);
    loadBaseOccupancyFromRow();
    if (serverBasePricing) {
        loadBaseOccupancyFrom(serverBasePricing);
    }

    function setSectionInputsEnabled(sectionId, enabled) {
        const section = document.getElementById(sectionId);
        if (!section) return;
        section.querySelectorAll('input, select, textarea').forEach(function (el) {
            el.disabled = !enabled;
        });
    }

    if (!isEditingBaseRoom) {
        document.getElementById('base_room_type').style.display = "none";
        document.getElementById('room_type').style.display = "block";
        document.getElementById('room_type_input').value = room.room_type;

        document.getElementById('variant_pricing_row').style.display = "";
        document.getElementById('base_pricing_row').style.display = "none";
        setSectionInputsEnabled('variant_pricing_row', true);
        setSectionInputsEnabled('base_pricing_row', false);

        const varientPriceInput = document.getElementById('varient_price_input');
        document.getElementById('varient_price').style.display = "block";
        varientPriceInput.disabled = false;
        varientPriceInput.value = room.varient_price;

        const baseRoomTypeInput = document.getElementById('base_room_type_input');
        if (baseRoomTypeInput) baseRoomTypeInput.disabled = true;

        const currentVariant = parseFloat(room.varient_price) || 0;
        const costsEmpty = occupancyFieldEmpty('singleWeekdayCostPrice')
            && occupancyFieldEmpty('singleWeekendCostPrice')
            && occupancyFieldEmpty('doubleWeekdayCostPrice')
            && occupancyFieldEmpty('doubleWeekendCostPrice');
        if (costsEmpty) {
            applyOccupancyFromBase(currentVariant, false, { updateCost: true, updateSell: false });
        }

        varientPriceInput.addEventListener('input', function () {
            applyOccupancyFromBase(this.value, false);
        });
        varientPriceInput.addEventListener('change', function () {
            applyOccupancyFromBase(this.value, false);
        });
    } else {
        document.getElementById('variant_pricing_row').style.display = "none";
        document.getElementById('base_pricing_row').style.display = "";
        setSectionInputsEnabled('variant_pricing_row', false);
        setSectionInputsEnabled('base_pricing_row', true);

        document.getElementById('base_room_type').style.display = "block";
        document.getElementById('base_room_type_input').value = room.room_type;
        document.getElementById('base_room_type_input').disabled = false;
        document.getElementById('varient_price').style.display = "none";

        const roomTypeInput = document.getElementById('room_type_input');
        if (roomTypeInput) roomTypeInput.disabled = true;
        const varientPriceInput = document.getElementById('varient_price_input');
        if (varientPriceInput) {
            varientPriceInput.disabled = true;
            varientPriceInput.value = 0;
        }
    }

    if (!isEditingBaseRoom && @json((bool) ($inheritBaseProfit ?? false))) {
        if (typeof window.recalculateRoomProfits === 'function') {
            window.recalculateRoomProfits(true);
        }
    }

    const hotelForm = document.getElementById('hotelForm');
    if (hotelForm) {
        hotelForm.addEventListener('submit', function () {
            // Ensure only the visible pricing section posts values.
            if (isEditingBaseRoom) {
                setSectionInputsEnabled('variant_pricing_row', false);
                setSectionInputsEnabled('base_pricing_row', true);
                const roomTypeInput = document.getElementById('room_type_input');
                if (roomTypeInput) roomTypeInput.disabled = true;
                const varientPriceInput = document.getElementById('varient_price_input');
                if (varientPriceInput) varientPriceInput.disabled = true;
            } else {
                setSectionInputsEnabled('variant_pricing_row', true);
                setSectionInputsEnabled('base_pricing_row', false);
                const baseRoomTypeInput = document.getElementById('base_room_type_input');
                if (baseRoomTypeInput) baseRoomTypeInput.disabled = true;
            }
        });
    }
});
</script>

<!-- Additional Image drop down -->
<script>
(function () {
    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('images');
    const fileList = document.getElementById('preview-container');
    const allImagesInput = document.getElementById('all-images');
    if (!dropArea || !fileInput || !fileList || !allImagesInput) {
        return;
    }

    let files = [];
    const MAX_VISIBLE_IMAGES = 3;
    let showAllImages = false;

    dropArea.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', () => handleFiles(fileInput.files));

    dropArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropArea.style.borderColor = '#000';
    });

    dropArea.addEventListener('dragleave', () => {
        dropArea.style.borderColor = '#ccc';
    });

    dropArea.addEventListener('drop', (e) => {
        e.preventDefault();
        dropArea.style.borderColor = '#ccc';
        handleFiles(e.dataTransfer.files);
    });

    function handleFiles(newFiles) {
        // Append new files to the list
        files = [...files, ...Array.from(newFiles)];
        updateFileList();
    }

    function updateFileList() {
        // Clear file list display
        fileList.innerHTML = '';
        const dataTransfer = new DataTransfer();

        // Decide how many files to display based on `showAllImages`
        const visibleFiles = showAllImages ? files : files.slice(0, MAX_VISIBLE_IMAGES);

        visibleFiles.forEach((file, index) => {
            // Create a wrapper for the image and delete button
            const imageWrapper = document.createElement('div');
            imageWrapper.style.position = 'relative';
            imageWrapper.style.display = 'inline-block';
            imageWrapper.style.margin = '10px';
            imageWrapper.style.width = '100px';
            imageWrapper.style.height = '100px';

            // Create an image element for preview
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file); // Create an object URL for the file
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';

            // Create a delete button
            const deleteButton = document.createElement('button');
            deleteButton.textContent = '×';
            deleteButton.style.position = 'absolute';
            deleteButton.style.top = '2px';
            deleteButton.style.right = '2px';
            deleteButton.style.background = 'rgba(255, 0, 0, 0.8)';
            deleteButton.style.color = 'white';
            deleteButton.style.border = 'none';
            deleteButton.style.borderRadius = '50%';
            deleteButton.style.cursor = 'pointer';
            deleteButton.style.width = '20px';
            deleteButton.style.height = '20px';
            deleteButton.style.fontSize = '12px';
            deleteButton.style.lineHeight = '16px';

            // Remove file and update list on delete
            deleteButton.addEventListener('click', () => {
                const fileIndex = files.indexOf(file);
                if (fileIndex > -1) {
                    files.splice(fileIndex, 1);
                }
                updateFileList();
            });

            // Append image and delete button to the wrapper
            imageWrapper.appendChild(img);
            imageWrapper.appendChild(deleteButton);
            fileList.appendChild(imageWrapper);

            // Add the file to the DataTransfer object
            dataTransfer.items.add(file);
        });

        // Add all files to the hidden input `all-images`
        const hiddenDataTransfer = new DataTransfer();
        files.forEach(file => hiddenDataTransfer.items.add(file));
        allImagesInput.files = hiddenDataTransfer.files;

        // Add a "More Images" badge if there are more files and not showing all images
        if (!showAllImages && files.length > MAX_VISIBLE_IMAGES) {
            const moreBadge = document.createElement('div');
            moreBadge.textContent = `+${files.length - MAX_VISIBLE_IMAGES} more`;
            moreBadge.style.margin = '10px';
            moreBadge.style.padding = '20px';
            moreBadge.style.backgroundColor = '#007bff';
            moreBadge.style.color = 'white';
            moreBadge.style.borderRadius = '5px';
            moreBadge.style.textAlign = 'center';
            moreBadge.style.fontSize = '14px';
            moreBadge.style.cursor = 'pointer';

            // Add click event to show all images
            moreBadge.addEventListener('click', () => {
                showAllImages = true;
                updateFileList(); // Re-render with all images
            });

            fileList.appendChild(moreBadge);
        }
    }
})();
</script>

<!-- delete existing additional Image -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const existingContainer = document.querySelector('.existing-image-preview-container');
    if (!existingContainer) {
        return;
    }
    existingContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-image-btn')) {
            e.preventDefault(); // Prevent form submission
            e.stopPropagation(); // Stop event propagation
            const button = e.target;

            // Find the image preview wrapper
            const imageWrapper = button.closest('.existing-image-preview-wrapper');
            if (imageWrapper) {
                // Find and remove the associated hidden input field for the image
                const hiddenInput = imageWrapper.querySelector('input[type="hidden"]');
                if (hiddenInput) {
                    hiddenInput.remove(); // Remove the hidden input
                }

                // Remove the image wrapper (image and button)
                imageWrapper.remove();
            }
        }
    });
});
</script>

<!-- delete existing master Image -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const existingMasterContainer = document.querySelector('.existing-master-image-preview-container');
    if (!existingMasterContainer) {
        return;
    }
    existingMasterContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-image-btn')) {
            e.preventDefault(); // Prevent form submission
            e.stopPropagation(); // Stop event propagation
            const button = e.target;

            // Find the image preview wrapper
            const imageWrapper = button.closest('.existing-master-image-preview-wrapper');
            if (imageWrapper) {
                // Find and remove the associated hidden input field for the image
                const hiddenInput = imageWrapper.querySelector('input[type="hidden"]');
                if (hiddenInput) {
                    hiddenInput.remove(); // Remove the hidden input
                }

                // Remove the image wrapper (image and button)
                imageWrapper.remove();
            }
        }
    });
});
</script>

<!-- Master Image drop down -->
<script>
(function () {
const masterDropArea = document.getElementById('master-drop-area');
const masterFileInput = document.getElementById('master_image');
const masterPreviewContainer = document.getElementById('master-preview-container');
if (!masterDropArea || !masterFileInput || !masterPreviewContainer) {
    return;
}
let masterFileCounter = 0; // Track total uploaded files
const MASTER_MAX_VISIBLE_IMAGES = 1; // Show only 1 image

// Open file picker on click
masterDropArea.addEventListener('click', () => masterFileInput.click());

// Handle drag events
masterDropArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    masterDropArea.style.backgroundColor = '#e3f2fd';
});

masterDropArea.addEventListener('dragleave', () => {
    masterDropArea.style.backgroundColor = 'white';
});

masterDropArea.addEventListener('drop', (e) => {
    e.preventDefault();
    masterDropArea.style.backgroundColor = 'white';
    masterHandleFiles(e.dataTransfer.files, true);
});

// Handle file input change
masterFileInput.addEventListener('change', () => {
    masterHandleFiles(masterFileInput.files, false);
});

function isLikelyImageFile(file) {
    if (!file) return false;
    if (file.type && file.type.indexOf('image/') === 0) return true;
    return /\.(jpe?g|png|webp|gif|bmp)$/i.test(file.name || '');
}

// Process and display files
function masterHandleFiles(files, fromDrop) {
    if (!files || !files.length) {
        return;
    }

    const file = files[0];
    if (!isLikelyImageFile(file)) {
        alert('Please choose a JPEG, PNG, WEBP or GIF image.');
        return;
    }

    if (fromDrop && masterFileInput.files !== files) {
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        masterFileInput.files = dataTransfer.files;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
        masterPreviewContainer.innerHTML = '';
        masterFileCounter = 1;
        masterImagePreview(e.target.result);
    };
    reader.readAsDataURL(file);
}

// Add image preview with limited visibility and a "more" badge
function masterImagePreview(imageSrc) {

    const imageWrapper = document.createElement('div');
    imageWrapper.style.position = 'relative';
    imageWrapper.style.width = '70px';
    imageWrapper.style.height = '70px';
    imageWrapper.style.margin = '5px';
    imageWrapper.style.overflow = 'hidden';
    imageWrapper.style.borderRadius = '5px';

    const img = document.createElement('img');
    img.src = imageSrc;
    img.style.width = '100%';
    img.style.height = '100%';
    img.style.objectFit = 'cover';

    const deleteButton = document.createElement('button');
    deleteButton.textContent = '×';
    deleteButton.style.position = 'absolute';
    deleteButton.style.top = '2px';
    deleteButton.style.right = '2px';
    deleteButton.style.background = 'rgba(255, 0, 0, 0.8)';
    deleteButton.style.color = 'white';
    deleteButton.style.border = 'none';
    deleteButton.style.borderRadius = '50%';
    deleteButton.style.cursor = 'pointer';
    deleteButton.style.width = '20px';
    deleteButton.style.height = '20px';
    deleteButton.style.fontSize = '12px';
    deleteButton.style.lineHeight = '16px';
    deleteButton.addEventListener('click', () => {
        masterPreviewContainer.removeChild(imageWrapper);
        masterFileCounter--;
        updateMoreBadge();
    });

    imageWrapper.appendChild(img);
    imageWrapper.appendChild(deleteButton);
    masterPreviewContainer.appendChild(imageWrapper);

    updateMoreBadge();
}

// Create and update "+X more" badge
function updateMoreBadge() {
    // Remove any existing badge
    const existingBadge = document.getElementById('more-badge');
    if (existingBadge) existingBadge.remove();

    if (masterFileCounter > MASTER_MAX_VISIBLE_IMAGES) {
        const moreMasterBadge = document.createElement('div');
        moreMasterBadge.id = 'more-master-badge';
        moreMasterBadge.textContent = `+${masterFileCounter - MASTER_MAX_VISIBLE_IMAGES} more`;
        moreMasterBadge.style.margin = '5px';
        moreMasterBadge.style.padding = '5px 10px';
        moreMasterBadge.style.backgroundColor = '#007bff';
        moreMasterBadge.style.color = 'white';
        moreMasterBadge.style.borderRadius = '5px';
        moreMasterBadge.style.cursor = 'pointer';
        moreMasterBadge.style.fontSize = '12px';
        moreMasterBadge.style.textAlign = 'center';
        moreMasterBadge.addEventListener('click', () => {
            // Show all hidden images
            const hiddenImages = masterPreviewContainer.querySelectorAll('div[style*="display: none"]');
            hiddenImages.forEach(img => img.style.display = 'inline-block');
            moreMasterBadge.remove(); // Remove badge after revealing all
        });
        masterPreviewContainer.appendChild(moreMasterBadge);
    }
}
})();
</script>

<!-- Food availabity -->
{{-- <script>
 // Function to toggle visibility for a given meal's options
    function toggleVisibility(meal, optionsId) {
        const select = document.getElementById(meal);
        const optionsContainer = document.getElementById(optionsId);

        if (select) {
            select.addEventListener('change', function() {
                if (this.value === '1') {
                    // Show options if "Available" is selected
                    optionsContainer.classList.remove('d-none');
                } else {
                    // Hide options if "Not Available" is selected
                    optionsContainer.classList.add('d-none');
                }
            });
        }
    }

 // Initialize for breakfast, lunch, and dinner
    toggleVisibility('breakfast', 'breakfast-options');
    toggleVisibility('lunch', 'lunch-options');
    toggleVisibility('dinner', 'dinner-options');
    toggleVisibility('booking_available', '12_hours_booking_price');
</script> --}}

<!-- set food details -->
{{-- <script>
    const breakfast = document.getElementById('breakfast');
    const lunch = document.getElementById('lunch');
    const dinner = document.getElementById('dinner');
    const roomData = @json($room);
    const breakfastContainer = document.getElementById('breakfast-options');
    const lunchContainer = document.getElementById('lunch-options');
    const dinnerContainer = document.getElementById('dinner-options');

    //get breakfast fields
    const breakfastType = document.getElementById('breakfast_type');
    const breakfastPrice = document.getElementById('breakfast_price');
    const childrenBreakfastPrice = document.getElementById('children_breakfast_price');
    const breakfastRestaurant = document.getElementById('breakfast_restaurant');

    //get lunch fields
    const lunchType = document.getElementById('lunch_type');
    const lunchPrice = document.getElementById('lunch_price');
    const lunchRestaurant = document.getElementById('lunch_restaurant');
    const childrenLunchPrice = document.getElementById('children_lunch_price');

    //get dinner fields
    const dinnerType = document.getElementById('dinner_type');
    const dinnerPrice = document.getElementById('dinner_price');
    const dinnerRestaurant = document.getElementById('dinner_restaurant');
    const childrenDinnerPrice = document.getElementById('children_dinner_price');

    if (roomData.breakfast == 1) {

        breakfastContainer.classList.remove('d-none');
    } else {
        breakfast.value = '0';
        breakfastContainer.classList.add('d-none');;
    }

    if (roomData.lunch == 1) {
        lunchContainer.classList.remove('d-none');
    } else {
        lunch.value = '0';
        lunchContainer.classList.add('d-none');
    }

    if (roomData.dinner == 1) {
        dinnerContainer.classList.remove('d-none');
    } else {
        dinner.value = "0";
        dinnerContainer.classList.add('d-none');
    }
    </script>

    <!-- legend inputs animation -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.form-floating input');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.value) {
                    this.classList.add('has-value');
                } else {
                    this.classList.remove('has-value');
                }
            });
        });
    });
</script> --}}

<script>
    var commissionType = {{ $commission_type ?? 0 }}; // 0 = Flat, 1 = Percentage
    var commissionPrice = {{ $commission_price ?? 0 }}; // Commission value

    function calculateMarkupPrice(basePrice) {
        if (!basePrice) return 0;
        return commissionType == 1 
            ? basePrice + (basePrice * commissionPrice / 100)  // Percentage-based commission
            : basePrice + commissionPrice; // Flat commission
    }

    function calculatePrice() {
        var priceFields = [
            { input: 'singleWeekdayPrice', output: 'totalSingleWeekdayPrice' },
            { input: 'singleWeekendPrice', output: 'totalSingleWeekendPrice' },
            { input: 'doubleWeekdayPrice', output: 'totalDoubleWeekdayPrice' },
            { input: 'doubleWeekendPrice', output: 'totalDoubleWeekendPrice' },
            { input: 'baseDoubleWeekdayPrice', output: 'totalBaseDoubleWeekdayPrice' },
            { input: 'baseDoubleWeekendPrice', output: 'totalBaseDoubleWeekendPrice' },
            { input: 'baseSingleWeekdayPrice', output: 'totalWeekdayPrice' },
            { input: 'baseSingleWeekendPrice', output: 'totalWeekendPrice' }
        ];

        priceFields.forEach(field => {
            var inputValue = parseFloat(document.getElementById(field.input)?.value) || 0;
            var finalPrice = calculateMarkupPrice(inputValue);
            var outputElement = document.getElementById(field.output);
            if (outputElement) {
                outputElement.innerText = finalPrice.toFixed(2);
            }
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        var inputIds = [
            'singleWeekdayPrice', 'singleWeekendPrice', 
            'doubleWeekdayPrice', 'doubleWeekendPrice', 
            'baseDoubleWeekdayPrice', 'baseDoubleWeekendPrice', 
            'baseSingleWeekdayPrice', 'baseSingleWeekendPrice'
        ];

        inputIds.forEach(function (id) {
            var input = document.getElementById(id);
            if (input) {
                input.addEventListener('keyup', calculatePrice);
                input.addEventListener('change', calculatePrice);
            }
        });

        // Initial calculation
        calculatePrice();
    });
</script>

<script>
    function showValidationMessage(inputElement, isValid, message) {
        const messageElement = document.getElementById(`${inputElement.id}-validation-message`);
        
        if (!messageElement) return;
        
        if (isValid) {
            messageElement.innerHTML = `
                <div class="valid-feedback d-block">
                    <i class="fas fa-check-circle text-success"></i> 
                    Looks good!
                </div>`;
            inputElement.classList.remove('is-invalid');
            inputElement.classList.add('is-valid');
        } else {
            messageElement.innerHTML = `
                <div class="invalid-feedback d-block">
                    <i class="fas fa-exclamation-circle"></i> 
                    ${message}
                </div>`;
            inputElement.classList.remove('is-valid');
            inputElement.classList.add('is-invalid');
        }
    }

    function validateTotalRooms(input) {
        // Force numeric input by immediately replacing non-numeric characters
        input.value = input.value.replace(/[^0-9]/g, '');
        
        const value = input.value.trim();
        const roomsRegex = /^[1-9][0-9]{0,3}$/;  // 1-9999 rooms
        
        if (value === '') {
            showValidationMessage(input, false, 'Total number of rooms is required');
        } else if (!roomsRegex.test(value)) {
            showValidationMessage(input, false, `
                Please enter a valid number of rooms:
                <ul class="mt-1 mb-0">
                    <li>Must be a positive number (1-9999)</li>
                    <li>No decimal places allowed</li>
                    <li>No leading zeros</li>
                </ul>
            `);
        } else {
            showValidationMessage(input, true, '');
        }
    }

    // function validateDimension(input) {
    //     // Allow only digits, 'x', '*', and spaces
    //     input.value = input.value.replace(/[^0-9x*\s]/g, '');
        
    //     // Format to standard format: replace all * with x and normalize spacing
    //     let value = input.value.trim().replace(/\*/g, 'x');
        
    //     // Replace multiple spaces with a single space
    //     value = value.replace(/\s+/g, ' ');
        
    //     // Ensure only one 'x' separator
    //     if ((value.match(/x/g) || []).length > 1) {
    //         const parts = value.split('x');
    //         value = parts[0] + 'x' + parts.slice(1).join('');
    //     }
        
    //     // Update the input value with the formatted value
    //     input.value = value;
        
    //     // Validate the format: number x number
    //     const dimensionRegex = /^[1-9][0-9]{0,2}(\s*[x]\s*)[1-9][0-9]{0,2}$/;
        
    //     if (value === '') {
    //         // Since dimension is optional, don't show error if empty
    //         input.classList.remove('is-invalid');
    //         input.classList.remove('is-valid');
    //         const messageElement = document.getElementById(`${input.id}-validation-message`);
    //         if (messageElement) messageElement.innerHTML = '';
    //     } else if (!dimensionRegex.test(value)) {
    //         showValidationMessage(input, false, `
    //             Please enter a valid dimension:
    //             <ul class="mt-1 mb-0">
    //                 <li>Format: length x width (e.g., 12x10)</li>
    //                 <li>Use 'x' as separator</li>
    //                 <li>Both length and width must be positive numbers (1-999)</li>
    //             </ul>
    //         `);
    //     } else {
    //         showValidationMessage(input, true, '');
    //     }
    // }

    // Add CSS for validation messages if not already included
    document.head.insertAdjacentHTML('beforeend', `
        <style>
            /* Base validation message styles */
            .validation-message {
                margin-top: 0.5rem;
                font-size: 0.85rem;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            }

            /* Error state styles */
            .validation-message .invalid-feedback {
                display: block;
                color: #e74c3c;
                background-color: #fef5f5;
                border-left: 3px solid #e74c3c;
                padding: 0.75rem 1rem;
                border-radius: 4px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                animation: slideIn 0.3s ease-in-out;
            }

            /* Success state styles */
            .validation-message .valid-feedback {
                display: block;
                color: #2ecc71;
                background-color: #f4fff6;
                border-left: 3px solid #2ecc71;
                padding: 0.75rem 1rem;
                border-radius: 4px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                animation: slideIn 0.3s ease-in-out;
            }

            /* List styles within validation messages */
            .validation-message ul {
                margin: 0.5rem 0 0 0;
                padding-left: 1.5rem;
                list-style-type: none;
            }

            .validation-message ul li {
                position: relative;
                padding: 0.2rem 0;
                color: #666;
            }

            .validation-message ul li::before {
                content: "•";
                color: #e74c3c;
                font-weight: bold;
                position: absolute;
                left: -1rem;
            }

            /* Icon styles */
            .validation-message i {
                margin-right: 0.5rem;
                font-size: 1rem;
            }

            /* Input field styles */
            .is-invalid {
                border-color: #e74c3c !important;
                background-color: #fff !important;
            }

            .is-valid {
                border-color: #2ecc71 !important;
                background-color: #fff !important;
            }

            /* Animation for validation messages */
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Hover effect for validation messages */
            .validation-message .invalid-feedback:hover,
            .validation-message .valid-feedback:hover {
                transform: translateY(-1px);
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
                transition: all 0.3s ease;
            }

            /* Required field indicator */
            .required-field::after {
                content: "*";
                color: #e74c3c;
                margin-left: 4px;
            }

            /* Responsive adjustments */
            @media (max-width: 768px) {
                .validation-message {
                    font-size: 0.8rem;
                }
                
                .validation-message .invalid-feedback,
                .validation-message .valid-feedback {
                    padding: 0.5rem 0.75rem;
                }
            }

            /* Focus state styles */
            .form-control:focus {
                box-shadow: 0 0 0 0.2rem rgba(46, 204, 113, 0.25);
                border-color: #2ecc71;
            }

            .form-control.is-invalid:focus {
                box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.25);
                border-color: #e74c3c;
            }
        </style>
    `);
</script>

<script>
    // Function to toggle meal options visibility
    function toggleMealOptions(mealType) {
        const included = document.getElementById(`${mealType}_included`).value === '1';
        const options = document.querySelectorAll(`.${mealType}-options`);
        const typeInput = document.getElementById(`${mealType}_type`);
        const priceInput = document.getElementById(`${mealType}_price`);

        options.forEach(option => {
            option.style.display = included ? 'block' : 'none';
        });

        // Set required attribute based on visibility
        if (typeInput) typeInput.required = included;
        if (priceInput) priceInput.required = included;
    }

    // Initialize meal options on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize each meal type
        ['breakfast', 'lunch', 'dinner'].forEach(mealType => {
            // Set initial visibility based on current value
            const included = document.getElementById(`${mealType}_included`);
            if (included) {
                toggleMealOptions(mealType);
                
                // Add change event listener
                included.addEventListener('change', function() {
                    toggleMealOptions(mealType);
                });
            }
        });

        // Add event listeners for price inputs to prevent negative values
        ['breakfast_price', 'lunch_price', 'dinner_price'].forEach(priceId => {
            const priceInput = document.getElementById(priceId);
            if (priceInput) {
                priceInput.addEventListener('input', function() {
                    if (this.value < 0) {
                        this.value = 0;
                    }
                });
            }
        });
    });
</script>

<script>
(function () {
    function round2(n) {
        return Math.round((Number(n) + Number.EPSILON) * 100) / 100;
    }

    function calcSellFromCost(cost, type, amount) {
        const costVal = parseFloat(cost);
        const amtVal = parseFloat(amount);
        const c = isNaN(costVal) ? 0 : costVal;
        const a = isNaN(amtVal) ? 0 : amtVal;
        if (c <= 0) return 0;
        if (a <= 0) return round2(c);
        if (type === 'flat') return round2(c + a);
        return round2(c + (c * a / 100));
    }

    function getProfitSettings() {
        const typeEl = document.querySelector('.js-room-profit-type');
        const amountEl = document.querySelector('.js-room-profit-amount');
        return {
            type: typeEl ? typeEl.value : 'percentage',
            amount: amountEl ? amountEl.value : 0
        };
    }

    function isOccupancyCostField(costEl) {
        return !!(costEl && costEl.closest && (
            costEl.closest('#variant_pricing_row') || costEl.closest('#base_pricing_row')
        ));
    }

    function updateOccupancySellHint(costEl, sellEl, sellValue) {
        if (!costEl || !sellEl || !isOccupancyCostField(costEl)) return;
        const g = getProfitSettings();
        const cost = parseFloat(costEl.value) || 0;
        const sell = parseFloat(sellValue);
        const sellNum = isNaN(sell) ? 0 : sell;
        const col = sellEl.closest('.col-md-6') || sellEl.parentElement;
        if (!col) return;
        const uniqueClass = 'base-price-info-' + (sellEl.id || costEl.getAttribute('data-sell-target') || 'sell');
        let hint = col.querySelector('.' + uniqueClass);
        if (!hint) {
            hint = document.createElement('div');
            hint.className = 'form-text text-primary base-price-info ' + uniqueClass;
            col.appendChild(hint);
        }
        const amt = parseFloat(g.amount) || 0;
        let text = 'Cost: ' + cost.toFixed(2);
        if (amt > 0) {
            text += g.type === 'flat'
                ? (' + profit ' + amt.toFixed(2) + ' = ' + sellNum.toFixed(2))
                : (' + profit ' + amt.toFixed(2) + '% = ' + sellNum.toFixed(2));
        }
        hint.textContent = text;
    }

    function updateSellFromCost(costEl, force) {
        if (!costEl) return;
        const sellId = costEl.getAttribute('data-sell-target');
        if (!sellId) return;
        const sellEl = document.getElementById(sellId) || document.querySelector('[name="' + sellId + '"]');
        if (!sellEl) return;
        if (!force && sellEl.dataset.userEdited === '1') return;
        const g = getProfitSettings();
        const sellValue = calcSellFromCost(costEl.value, g.type, g.amount);
        sellEl.value = sellValue;
        sellEl.dataset.userEdited = '';
        updateOccupancySellHint(costEl, sellEl, sellValue);
        if (typeof calculatePrice === 'function') {
            try { calculatePrice(); } catch (e) {}
        }
    }

    function recalculateAll(force) {
        if (typeof window.refreshRoomOccupancySells === 'function') {
            window.refreshRoomOccupancySells();
        }
        document.querySelectorAll('.js-room-cost[data-sell-target]').forEach(function (costEl) {
            updateSellFromCost(costEl, force);
        });
    }
    window.recalculateRoomProfits = recalculateAll;

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.js-room-cost[data-sell-target]').forEach(function (costEl) {
            costEl.addEventListener('input', function () { updateSellFromCost(costEl, true); });
            costEl.addEventListener('change', function () { updateSellFromCost(costEl, true); });
        });

        document.querySelectorAll('.js-room-sell').forEach(function (sellEl) {
            sellEl.addEventListener('input', function () {
                sellEl.dataset.userEdited = '1';
            });
        });

        document.querySelectorAll('.js-room-profit-type, .js-room-profit-amount').forEach(function (el) {
            el.addEventListener('input', function () {
                if (typeof window.syncRoomProfitAmountLabel === 'function') {
                    window.syncRoomProfitAmountLabel();
                }
                recalculateAll(true);
            });
            el.addEventListener('change', function () {
                if (typeof window.syncRoomProfitAmountLabel === 'function') {
                    window.syncRoomProfitAmountLabel();
                }
                recalculateAll(true);
            });
        });
        if (typeof window.syncRoomProfitAmountLabel === 'function') {
            window.syncRoomProfitAmountLabel();
        }
        if (@json((bool) ($inheritBaseProfit ?? false))) {
            setTimeout(function () { recalculateAll(true); }, 0);
        }
    });
})();
</script>

<script>
(function () {
    function sanitizePriceInputValue(value) {
        value = String(value || '').replace(/[^0-9.]/g, '');
        const firstDot = value.indexOf('.');
        if (firstDot !== -1) {
            value = value.slice(0, firstDot + 1) + value.slice(firstDot + 1).replace(/\./g, '');
        }
        return value;
    }

    function isRoomPriceInput(el) {
        if (!el || el.tagName !== 'INPUT') {
            return false;
        }
        if (el.type === 'checkbox' || el.type === 'radio' || el.type === 'hidden' || el.type === 'file') {
            return false;
        }
        if (el.classList.contains('js-room-cost')
            || el.classList.contains('js-room-sell')
            || el.classList.contains('js-room-profit-amount')) {
            return true;
        }
        if (el.id === 'varient_price_input' || el.name === 'varient_price') {
            return true;
        }
        const key = ((el.name || '') + ' ' + (el.id || '')).toLowerCase();
        if (!/(price|cost)/.test(key)) {
            return false;
        }
        // Exclude non-price controls that may include those words
        if (/(children_price|children_breakfast|total_rooms|no_of_room|dimension)/.test(key)) {
            return false;
        }
        return true;
    }

    function enforcePriceNumeric(el) {
        if (!isRoomPriceInput(el)) {
            return;
        }
        const sanitized = sanitizePriceInputValue(el.value);
        if (el.value !== sanitized) {
            const start = el.selectionStart;
            const end = el.selectionEnd;
            el.value = sanitized;
            if (typeof start === 'number' && typeof end === 'number' && el.setSelectionRange) {
                try {
                    el.setSelectionRange(Math.min(start, sanitized.length), Math.min(end, sanitized.length));
                } catch (e) {}
            }
        }
    }

    document.addEventListener('input', function (e) {
        enforcePriceNumeric(e.target);
    }, true);

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input').forEach(function (el) {
            if (isRoomPriceInput(el)) {
                el.setAttribute('inputmode', 'decimal');
                el.setAttribute('autocomplete', 'off');
                enforcePriceNumeric(el);
            }
        });
    });
})();
</script>
@endsection