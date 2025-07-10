@extends('layouts.layout')

@section('content')
@include('hotel.tapview', ['hotel' => $hotel])
<link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" rel="stylesheet">
<style>
    /* Readonly styling for unauthorized users */
    .readonly-mode select[disabled] {
        background-color: #f8f9fa !important;
        opacity: 0.7;
        cursor: not-allowed;
        color: #6c757d;
    }
    
    .readonly-mode .btn[disabled] {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .alert-info {
        border-left: 4px solid #0dcaf0;
    }
</style>
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Conference Room
                <a href="javascript:history.back()" class="btn btn-sm btn-outline-danger">
                <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form action="{{ route('update.conference') }}" method="POST" enctype="multipart/form-data"  class="card-body">
                @csrf 
                <input type="hidden" id="hotel_id" name="hotel_id" class="form-control" value="{{ $hotel->hotel_unique_id }}" >
                
                @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20)
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-eye"></i> <strong>View Only Mode:</strong> Conference room editing is restricted for your role. Contact your administrator for editing permissions.
                    </div>
                @endif
                
                <div class="row">
                <div class="mb-3 col-md-4">
                    <label for="conference" class="form-label">
                        <strong>Conference</strong>
                        <span style="color: red; font-weight: bold;">*</span>
                    </label>
                    <select id="conference" name="conference" class="form-control"
                        @if(Auth::user()->role_id != 1 && Auth::user()->role_id != 20) disabled @endif>
                        <option value="" disabled selected>Select an option</option>
                        <option value="true" {{ $conference === true || $conference === 'true' ? 'selected' : '' }}>Yes</option>
                        <option value="false" {{ $conference === false || $conference === 'false' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <div class="d-flex gap-3 mt-4">
                    @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 20)
                        <button type="submit" class="btn btn-primary px-4">Save</button>
                    @else
                        <button type="button" class="btn btn-secondary px-4" disabled>
                            <i class="fas fa-lock"></i> Save Restricted
                        </button>
                        <small class="text-muted mt-2">
                            <i class="fas fa-info-circle"></i> You don't have permission to save conference data.
                        </small>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Check user role for protection
        var isUnauthorized = {{ Auth::user()->role_id != 1 && Auth::user()->role_id != 20 ? 'true' : 'false' }};
        
        // Add protection for unauthorized users
        if (isUnauthorized) {
            // Add readonly-mode class for styling
            $('body').addClass('readonly-mode');
            
            // Prevent form submissions
            $('form').on('submit', function(e) {
                e.preventDefault();
                alert('You do not have permission to save conference data. Contact your administrator.');
                return false;
            });
            
            // Add visual styling for readonly mode
            $('select[disabled]').css({
                'background-color': '#f8f9fa',
                'opacity': '0.7',
                'cursor': 'not-allowed'
            });
            
            // Disable select dropdown change events
            $('select[disabled]').on('change', function(e) {
                e.preventDefault();
                return false;
            });
            
            // Prevent any interaction with disabled elements
            $('select[disabled], button[disabled]').on('click focus', function(e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            });
        }
    });
</script>
@endsection

