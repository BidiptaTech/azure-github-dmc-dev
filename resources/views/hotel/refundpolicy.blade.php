@extends('layouts.layout')
@section('content')
@include('hotel.tapview', ['hotel' => $hotel])
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.css" rel="stylesheet">
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
            Hotel Refund Policy
            <a href="javascript:history.back()" class="btn btn-sm btn-outline-danger">
                <i class="mdi mdi-arrow-left"></i> Back
            </a>
            </h5>
            <form action="{{ route('updaterefund.policy') }}" method="POST" enctype="multipart/form-data" class="card-body">
                @csrf 
                <input type="text" id="hotel_id" name="hotel_id" class="form-control" value="{{ $hotel->hotel_unique_id }}" hidden>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="policy" class="form-label"><strong>Refund Policy</strong><span style="color: red;">*</span></label>
                        <textarea id="summernote" name="refundpolicy" >{{ old('refundpolicy', $hotel->refundpolicy) }}</textarea>
                        @error('refundpolicy')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- File Upload -->
                    <div class="col-md-4 mb-3">
                        <label for="file" class="form-label"></label>
                        <input type="file" name="file" class="form-control" accept="application/pdf">
                        @error('file')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror

                        @if (!empty($hotel->refundpolicy_pdf))
                            <div class="mt-2">
                                <a href="{{ $hotel->refundpolicy_pdf }}" target="_blank">View Existing File</a>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" class="btn btn-primary px-4">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote.min.js"></script>
<script>
$(document).ready(function() {
  $('#summernote').summernote();
});
</script>

<script>
$(document).ready(function() {
    $('#summernote').summernote({
        height: 200,
        placeholder: "Enter refund policy...",
    });

    $("form").on("submit", function(e) {
        var content = $('#summernote').summernote('code').trim();
        
        // Remove HTML tags and check if content is empty
        if (content.replace(/<[^>]*>?/gm, '').trim() === '') {
            alert("Refund Policy is required.");
            e.preventDefault(); // Prevent form submission
        }
    });
});
</script>

@endsection
