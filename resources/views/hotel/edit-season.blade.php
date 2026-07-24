@extends('layouts.layout')
@section('title', 'Hotels')
@section('css')
<link href="{{ URL::asset('build/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
@endsection

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Edit Season
                <a href="javascript:history.back()" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form id="hotelForm" method="POST" action="{{ route('season.update') }}" enctype="multipart/form-data" class="card-body">
               @csrf
               <input value="{{$rate->rate_id}}" type="text" class="form-control" name="rate_id" hidden>
               <input value="{{$hotel->hotel_unique_id}}" type="text" class="form-control" name="hotel_id" hidden>
               <input name="event_type" type="hidden" value="Season">

               <hr>
               <div id="hotelRatesContainer">
                  <div class="hotel-rate-form">
                     <div class="row">
                        <!-- Event Name -->
                        <div class="col-md-3 mb-3">
                           <label for="event" class="form-label"><strong>Event Name</strong><span class="text-danger">*</span></label>
                           <input value="{{$rate->event}}" type="text" class="form-control" name="event" placeholder="Enter Event Name" required>
                        </div>

                        <div class="col-md-3 mb-3">
                           <label for="season_profit_margin" class="form-label"><strong>Profit (margin)</strong></label>
                           <select id="season_profit_margin" class="form-select js-season-profit-type">
                              <option value="percentage" selected>%</option>
                              <option value="flat">Flat</option>
                           </select>
                           <small class="text-muted">Helper only — not saved</small>
                        </div>
                        <div class="col-md-3 mb-3">
                           <label for="season_profit_amount" class="form-label"><strong>Profit amount</strong></label>
                           <input type="number" id="season_profit_amount" class="form-control js-season-profit-amount"
                                  value="0" min="0" step="0.01" placeholder="Enter profit amount">
                           <small class="text-muted">Auto-fills Sell from Cost</small>
                        </div>

                        <!-- Single Weekday: Cost then Sell -->
                        <div class="mb-3 col-md-3">
                           <label for="weekday_cost_price" class="form-label"><strong>Single Base Weekday Price(Cost)</strong><span class="text-danger">*</span></label>
                           <input value="{{ $rate->weekday_cost_price }}" type="number" id="weekday_cost_price" name="weekday_cost_price" class="form-control js-season-cost" data-sell-target="weekday_price" placeholder="Enter Cost Price" min="0" step="0.01">
                           @error('weekday_cost_price')
                              <div class="text-danger mt-1">{{ $message }}</div>
                           @enderror
                        </div>
                        <div class="mb-3 col-md-3" id="base_weekday_price">
                           <label for="weekday_price" class="form-label"><strong>Single Base Weekday Price(Sell)</strong><span class="text-danger">*</span></label>
                           <input value="{{ $rate->weekday_price }}" type="number" id="weekday_price" name="weekday_price" class="form-control js-season-sell" placeholder="Enter Sell Price" min="0" step="0.01">
                           @error('weekday_price')
                              <div class="text-danger mt-1">{{ $message }}</div>
                           @enderror
                        </div>

                        <!-- Single Weekend: Cost then Sell -->
                        <div class="mb-3 col-md-3">
                           <label for="weekend_cost_price" class="form-label"><strong>Single Base Weekend Price(Cost)</strong><span class="text-danger">*</span></label>
                           <input value="{{ $rate->weekend_cost_price }}" type="number" id="weekend_cost_price" name="weekend_cost_price" class="form-control js-season-cost" data-sell-target="weekend_price" placeholder="Enter Cost Price" min="0" step="0.01">
                           @error('weekend_cost_price')
                              <div class="text-danger mt-1">{{ $message }}</div>
                           @enderror
                        </div>
                        <div class="mb-3 col-md-3" id="base_weekend_price">
                              <label for="weekend_price" class="form-label"><strong>Single Base Weekend Price(Sell)</strong><span class="text-danger">*</span></label>
                              <input value="{{ $rate->weekend_price }}" type="number" id="weekend_price" name="weekend_price" class="form-control js-season-sell" placeholder="Enter Sell Price" min="0" step="0.01">
                              @error('weekend_price')
                                 <div class="text-danger mt-1">{{ $message }}</div>
                              @enderror
                        </div>

                        <!-- Double Weekday: Cost then Sell -->
                        <div class="mb-3 col-md-3">
                           <label for="double_weekday_cost_price" class="form-label"><strong>Double Base Weekday Price(Cost)</strong><span class="text-danger">*</span></label>
                           <input value="{{ $rate->double_weekday_cost_price }}" type="number" id="double_weekday_cost_price" name="double_weekday_cost_price" class="form-control js-season-cost" data-sell-target="double_weekday_price" placeholder="Enter Cost Price" min="0" step="0.01">
                           @error('double_weekday_cost_price')
                              <div class="text-danger mt-1">{{ $message }}</div>
                           @enderror
                        </div>
                        <div class="mb-3 col-md-3">
                           <label for="double_weekday_price" class="form-label"><strong>Double Base Weekday Price(Sell)</strong><span class="text-danger">*</span></label>
                           <input value="{{ $rate->double_weekday_price }}" type="number" id="double_weekday_price" name="double_weekday_price" class="form-control js-season-sell" placeholder="Enter Sell Price" min="0" step="0.01">
                           @error('double_weekday_price')
                              <div class="text-danger mt-1">{{ $message }}</div>
                           @enderror
                        </div>

                        <!-- Double Weekend: Cost then Sell -->
                        <div class="mb-3 col-md-3">
                           <label for="double_weekend_cost_price" class="form-label"><strong>Double Base Weekend Price(Cost)</strong><span class="text-danger">*</span></label>
                           <input value="{{ $rate->double_weekend_cost_price }}" type="number" id="double_weekend_cost_price" name="double_weekend_cost_price" class="form-control js-season-cost" data-sell-target="double_weekend_price" placeholder="Enter Cost Price" min="0" step="0.01">
                           @error('double_weekend_cost_price')
                              <div class="text-danger mt-1">{{ $message }}</div>
                           @enderror
                        </div>
                        <div class="mb-3 col-md-3">
                              <label for="double_weekend_price" class="form-label"><strong>Double Base Weekend Price(Sell)</strong><span class="text-danger">*</span></label>
                              <input value="{{ $rate->double_weekend_price }}" type="number" id="double_weekend_price" name="double_weekend_price" class="form-control js-season-sell" placeholder="Enter Sell Price" min="0" step="0.01">
                              @error('double_weekend_price')
                                 <div class="text-danger mt-1">{{ $message }}</div>
                              @enderror
                        </div>

                        <!-- Breakfast: Cost then Sell -->
                        <div class="mb-3 col-md-3">
                           <label for="breakfast_cost_price" class="form-label"><strong>Breakfast Price(Cost)</strong></label>
                           <input value="{{ $rate->breakfast_cost_price }}" type="number" step="0.01" min="0" id="breakfast_cost_price" name="breakfast_cost_price" class="form-control js-season-cost" data-sell-target="breakfast_price" placeholder="Enter Cost Price">
                           @error('breakfast_cost_price')
                              <div class="text-danger mt-1">{{ $message }}</div>
                           @enderror
                        </div>
                        <div class="mb-3 col-md-3">
                           <label for="breakfast_price" class="form-label"><strong>Breakfast Price(Sell)</strong></label>
                           <input value="{{ $rate->breakfast_price }}" type="number" step="0.01" min="0" id="breakfast_price" name="breakfast_price" class="form-control js-season-sell" placeholder="Enter Sell Price">
                           @error('breakfast_price')
                              <div class="text-danger mt-1">{{ $message }}</div>
                           @enderror
                        </div>

                        <!-- Lunch: Cost then Sell -->
                        <div class="mb-3 col-md-3">
                           <label for="lunch_cost_price" class="form-label"><strong>Lunch Price(Cost)</strong></label>
                           <input value="{{ $rate->lunch_cost_price }}" type="number" step="0.01" min="0" id="lunch_cost_price" name="lunch_cost_price" class="form-control js-season-cost" data-sell-target="lunch_price" placeholder="Enter Cost Price">
                           @error('lunch_cost_price')
                              <div class="text-danger mt-1">{{ $message }}</div>
                           @enderror
                        </div>
                        <div class="mb-3 col-md-3">
                           <label for="lunch_price" class="form-label"><strong>Lunch Price(Sell)</strong></label>
                           <input value="{{ $rate->lunch_price }}" type="number" step="0.01" min="0" id="lunch_price" name="lunch_price" class="form-control js-season-sell" placeholder="Enter Sell Price">
                           @error('lunch_price')
                              <div class="text-danger mt-1">{{ $message }}</div>
                           @enderror
                        </div>

                        <!-- Dinner: Cost then Sell -->
                        <div class="mb-3 col-md-3">
                           <label for="dinner_cost_price" class="form-label"><strong>Dinner Price(Cost)</strong></label>
                           <input value="{{ $rate->dinner_cost_price }}" type="number" step="0.01" min="0" id="dinner_cost_price" name="dinner_cost_price" class="form-control js-season-cost" data-sell-target="dinner_price" placeholder="Enter Cost Price">
                           @error('dinner_cost_price')
                              <div class="text-danger mt-1">{{ $message }}</div>
                           @enderror
                        </div>
                        <div class="mb-3 col-md-3">
                           <label for="dinner_price" class="form-label"><strong>Dinner Price(Sell)</strong></label>
                           <input value="{{ $rate->dinner_price }}" type="number" step="0.01" min="0" id="dinner_price" name="dinner_price" class="form-control js-season-sell" placeholder="Enter Sell Price">
                           @error('dinner_price')
                              <div class="text-danger mt-1">{{ $message }}</div>
                           @enderror
                        </div>

                        <!-- Start Date -->
                        <div class="col-md-3 mb-3">
                           <label for="start_date" class="form-label"><strong>Start Date</strong><span class="text-danger">*</span></label>
                           <input value="{{$rate->start_date}}" type="date" class="form-control" name="start_date" required>
                        </div>

                        <!-- End Date -->
                        <div class="col-md-3 mb-3">
                           <label for="end_date" class="form-label"><strong>End Date</strong><span class="text-danger">*</span></label>
                           <input value="{{$rate->end_date}}" type="date" class="form-control" name="end_date" required>
                        </div>
                     </div>
                  </div>
               </div>

               <!-- Status -->
               <div class="form-check form-switch">
                  <label for="season_status" class="form-label"><strong>Status</strong></label>
                  <span style="color: red; font-weight: bold;">*</span>
                  <input {{$rate->is_active == 1 ? 'checked' : ''}} class="form-check-input" name="season_status" type="checkbox" id="season_status" value="1">
                  <label class="form-check-label"></label>
                  @error('season_status')
                     <div class="text-danger mt-1">{{ $message }}</div>
                  @enderror
               </div>

               <!-- Submit Buttons -->
               <div class="d-flex gap-3">
                  <a href="{{ route('hotels.season', $hotel->hotel_unique_id) }}" class="btn btn-secondary px-4">Previous</a>
                  <button type="submit" class="btn btn-primary px-4">Update Seasons</button> 
               </div>
            </form>
      </div>
   </div>
</div>
@endsection
@section('scripts') 
<!-- DataTable Scripts -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>

<script>
    $(document).ready(function() {
        $('#example2').DataTable({
            "order": [[0, "asc"]],
            lengthChange: false,
            buttons: ['copy', 'excel', 'pdf', 'print']
        });

        $('#example2').DataTable().buttons().container().appendTo('#example2_wrapper .col-md-6:eq(0)');
    });

    function setDeleteForm(action) {
        document.getElementById('deleteForm').action = action;
    }
</script>
<script>
(function () {
    function round2(n) {
        return Math.round((Number(n) + Number.EPSILON) * 100) / 100;
    }

    function calcSellFromCost(cost, type, amount) {
        const c = parseFloat(cost);
        const a = parseFloat(amount);
        const costVal = isNaN(c) ? 0 : c;
        const amtVal = isNaN(a) ? 0 : a;
        if (costVal <= 0) return 0;
        if (type === 'flat') return round2(costVal + amtVal);
        return round2(costVal + (costVal * amtVal / 100));
    }

    function getProfitSettings() {
        const typeEl = document.querySelector('.js-season-profit-type');
        const amountEl = document.querySelector('.js-season-profit-amount');
        return {
            type: typeEl ? typeEl.value : 'percentage',
            amount: amountEl ? amountEl.value : 0
        };
    }

    function updateSellFromCost(costEl, force) {
        if (!costEl) return;
        const sellId = costEl.getAttribute('data-sell-target');
        if (!sellId) return;
        const sellEl = document.getElementById(sellId);
        if (!sellEl) return;
        if (!force && sellEl.dataset.userEdited === '1') return;
        const g = getProfitSettings();
        sellEl.value = calcSellFromCost(costEl.value, g.type, g.amount);
        sellEl.dataset.userEdited = '';
    }

    function recalculateAll(force) {
        document.querySelectorAll('.js-season-cost[data-sell-target]').forEach(function (costEl) {
            updateSellFromCost(costEl, force);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.js-season-cost[data-sell-target]').forEach(function (costEl) {
            costEl.addEventListener('input', function () {
                updateSellFromCost(costEl, true);
            });
        });
        document.querySelectorAll('.js-season-sell').forEach(function (sellEl) {
            sellEl.addEventListener('input', function () {
                sellEl.dataset.userEdited = '1';
            });
        });
        document.querySelectorAll('.js-season-profit-type, .js-season-profit-amount').forEach(function (el) {
            el.addEventListener('input', function () { recalculateAll(true); });
            el.addEventListener('change', function () { recalculateAll(true); });
        });
    });
})();
</script>
@endsection
