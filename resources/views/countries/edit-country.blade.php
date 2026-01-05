@extends('layouts.layout')
@section('title', 'Edit Country')
@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                Edit Country
                <a href="{{ route('countries.index') }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-arrow-left"></i> Back
                </a>
            </h5>
            <form action="{{ route('countries.update', $country->id) }}" method="POST" class="card-body" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Country Name (Readonly) -->
                    <div class="col-md-4 mb-3">
                        <label for="name" class="form-label"><strong>Country Name</strong><span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="{{ $country->name }}" readonly required>
                    </div>

                    <!-- Country Code (Dialing Code) - Readonly -->
                    <div class="col-md-4 mb-3">
                        <label for="country_code" class="form-label"><strong>Country Code (Dialing Code)</strong><span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="country_code" value="{{ $country->country_code }}" readonly required>
                    </div>

                    <!-- Card Name -->
                    <div class="col-md-4 mb-3">
                        <label for="card_type" class="form-label"><strong>Card Name</strong><span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('card_type') is-invalid @enderror"
                               name="card_type" value="{{ $country->card_type }}" placeholder="Enter Card Name" required>
                        @error('card_type')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="card_length" class="form-label"><strong>Card Length</strong><span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('card_length') is-invalid @enderror"
                               name="card_length" value="{{ $country->card_length }}" placeholder="Enter Card Length" required>
                        @error('card_length')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="min_legth" class="form-label"><strong>Min Dial Length</strong><span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('min_length') is-invalid @enderror"
                               name="min_length" value="{{ $country->min_length }}" placeholder="Enter Min Dial Length" required>
                        @error('min_length')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="max_length" class="form-label"><strong>Max Dial Length</strong><span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('max_length') is-invalid @enderror"
                               name="max_length" value="{{ $country->max_length }}" placeholder="Enter Max Dial Length" required>
                        @error('max_length')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Tax Percentage -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tax_percentage" class="form-label"><strong>Tax Percentage (%)</strong><span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control @error('tax_percentage') is-invalid @enderror"
                               name="tax_percentage" value="{{ $country->tax_percentage }}" required>
                        @error('tax_percentage')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Currency -->
                    <div class="col-md-6 mb-3">
                        <label for="currency" class="form-label"><strong>Currency</strong><span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('currency') is-invalid @enderror"
                               name="currency" value="{{ $country->currency }}" id="currency">
                        @error('currency')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Gateway Percentage -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="gateway_percentage" class="form-label"><strong>Gateway Percentage (%)</strong><span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control @error('gateway_percentage') is-invalid @enderror"
                               name="gateway_percentage" value="{{ $country->gateway_percentage }}" required>
                        @error('gateway_percentage')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Commission Percentage -->
                    <div class="col-md-6 mb-3">
                        <label for="commission_percentage" class="form-label"><strong>Commission Percentage (%)</strong><span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control @error('commission_percentage') is-invalid @enderror"
                               name="commission_percentage" value="{{ $country->commission_percentage }}" required>
                        @error('commission_percentage')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Country Image -->
                    <div class="col-md-6 mb-3">
                        <label for="country_image" class="form-label"><strong>Country Image</strong></label>
                        <input type="file" class="form-control @error('country_image') is-invalid @enderror" name="country_image" accept="image/*">
                        <input type="hidden" name="remove_country_image" id="remove_country_image" value="0">
                        @error('country_image')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        @if(!empty($country->country_image))
                        <div class="mt-2 border rounded p-2" id="countryImagePreviewWrap">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <img src="{{ asset($country->country_image) }}" alt="Country Image" class="rounded me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                    <span class="small">{{ Str::afterLast($country->country_image, '/') }}</span>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ asset($country->country_image) }}" target="_blank" class="btn btn-outline-primary">View</a>
                                    <button type="button" class="btn btn-outline-danger" id="removeCountryImageBtn">Remove</button>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                
                    <div class="col-md-6 mb-3">
                        <label for="header_pdf" class="form-label"><strong>Upload Header PDF</strong></label>
                        <input type="file" class="form-control @error('header_pdf') is-invalid @enderror" name="header_pdf" accept="application/pdf">
                        <input type="hidden" name="remove_header_pdf" id="remove_header_pdf" value="0">
                        @error('header_pdf')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        @if(!empty($country->header_pdf))
                        <div class="mt-2 border rounded p-2" id="headerPdfPreviewWrap">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="ri-file-pdf-2-line text-danger me-1"></i>
                                    <span class="small">{{ Str::afterLast($country->header_pdf, '/') }}</span>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ $country->header_pdf }}" target="_blank" class="btn btn-outline-primary">View</a>
                                    <button type="button" class="btn btn-outline-danger" id="removeHeaderBtn">Remove</button>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="footer_pdf" class="form-label"><strong>Upload Footer PDF</strong></label>
                        <input type="file" class="form-control @error('footer_pdf') is-invalid @enderror" name="footer_pdf" accept="application/pdf">
                        <input type="hidden" name="remove_footer_pdf" id="remove_footer_pdf" value="0">
                        @error('footer_pdf')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                        @if(!empty($country->footer_pdf))
                        <div class="mt-2 border rounded p-2" id="footerPdfPreviewWrap">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="ri-file-pdf-2-line text-danger me-1"></i>
                                    <span class="small">{{ Str::afterLast($country->footer_pdf, '/') }}</span>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ $country->footer_pdf }}" target="_blank" class="btn btn-outline-primary">View</a>
                                    <button type="button" class="btn btn-outline-danger" id="removeFooterBtn">Remove</button>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="row mt-4 text-center">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('countries.index') }}" class="btn btn-secondary px-4">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')

<script>
    // Guard in case element does not exist in edit form
    const countrySelectEl = document.getElementById('countrySelect');
    if (countrySelectEl) {
        countrySelectEl.addEventListener('change', function() {
            let selectedOption = this.options[this.selectedIndex];
            let countryCode = selectedOption.getAttribute('data-code');
            let currency = selectedOption.getAttribute('data-currency');
            const codeEl = document.getElementById('countryCode');
            const currencyEl = document.getElementById('currency');
            if (codeEl) codeEl.value = countryCode || '';
            if (currencyEl) currencyEl.value = currency || '';
        });
    }

    // Handle remove current PDFs/Images and live preview on replace
    document.addEventListener('DOMContentLoaded', function() {
        const headerInput = document.querySelector('input[name="header_pdf"]');
        const footerInput = document.querySelector('input[name="footer_pdf"]');
        const countryImageInput = document.querySelector('input[name="country_image"]');
        const removeHeader = document.getElementById('remove_header_pdf');
        const removeFooter = document.getElementById('remove_footer_pdf');
        const removeCountryImage = document.getElementById('remove_country_image');
        const headerWrap = document.getElementById('headerPdfPreviewWrap');
        const footerWrap = document.getElementById('footerPdfPreviewWrap');
        const countryImageWrap = document.getElementById('countryImagePreviewWrap');
        const removeHeaderBtn = document.getElementById('removeHeaderBtn');
        const removeFooterBtn = document.getElementById('removeFooterBtn');
        const removeCountryImageBtn = document.getElementById('removeCountryImageBtn');

        if (removeHeaderBtn) {
            removeHeaderBtn.addEventListener('click', function() {
                removeHeader.value = '1';
                if (headerWrap) headerWrap.style.display = 'none';
                // Clear the file input
                if (headerInput) headerInput.value = '';
            });
        }
        if (removeFooterBtn) {
            removeFooterBtn.addEventListener('click', function() {
                removeFooter.value = '1';
                if (footerWrap) footerWrap.style.display = 'none';
                // Clear the file input
                if (footerInput) footerInput.value = '';
            });
        }
        if (removeCountryImageBtn) {
            removeCountryImageBtn.addEventListener('click', function() {
                removeCountryImage.value = '1';
                if (countryImageWrap) countryImageWrap.style.display = 'none';
                // Clear the file input
                if (countryImageInput) countryImageInput.value = '';
            });
        }

        function showInlinePreview(file, targetWrapId) {
            if (!file || file.type !== 'application/pdf') return;
            const url = URL.createObjectURL(file);
            let wrap = document.getElementById(targetWrapId);
            if (!wrap) {
                wrap = document.createElement('div');
                wrap.id = targetWrapId;
                wrap.className = 'mt-2 border rounded p-2';
                const row = document.createElement('div');
                row.className = 'd-flex justify-content-between align-items-center';
                const left = document.createElement('div');
                left.innerHTML = '<i class="ri-file-pdf-2-line text-danger me-1"></i><span class="small">New PDF selected</span>';
                const right = document.createElement('div');
                right.className = 'btn-group btn-group-sm';
                const openBtn = document.createElement('a');
                openBtn.className = 'btn btn-outline-primary';
                openBtn.target = '_blank';
                openBtn.textContent = 'View';
                right.appendChild(openBtn);
                row.appendChild(left); row.appendChild(right);
                wrap.appendChild(row);
                const anchor = targetWrapId.includes('Header') ? headerInput : footerInput;
                anchor.parentElement.appendChild(wrap);
            }
            const openAnchor = wrap.querySelector('a.btn');
            openAnchor.href = url;
        }

        function showImagePreview(file, targetWrapId) {
            if (!file || !file.type.startsWith('image/')) return;
            const url = URL.createObjectURL(file);
            let wrap = document.getElementById(targetWrapId);
            if (!wrap) {
                wrap = document.createElement('div');
                wrap.id = targetWrapId;
                wrap.className = 'mt-2 border rounded p-2';
                const row = document.createElement('div');
                row.className = 'd-flex justify-content-between align-items-center';
                const left = document.createElement('div');
                left.className = 'd-flex align-items-center';
                const img = document.createElement('img');
                img.className = 'rounded me-2';
                img.style.width = '50px';
                img.style.height = '50px';
                img.style.objectFit = 'cover';
                img.src = url;
                const span = document.createElement('span');
                span.className = 'small';
                span.textContent = 'New image selected';
                left.appendChild(img);
                left.appendChild(span);
                const right = document.createElement('div');
                right.className = 'btn-group btn-group-sm';
                const openBtn = document.createElement('a');
                openBtn.className = 'btn btn-outline-primary';
                openBtn.target = '_blank';
                openBtn.textContent = 'View';
                openBtn.href = url;
                right.appendChild(openBtn);
                row.appendChild(left); row.appendChild(right);
                wrap.appendChild(row);
                countryImageInput.parentElement.appendChild(wrap);
            } else {
                const img = wrap.querySelector('img');
                const openAnchor = wrap.querySelector('a.btn');
                if (img) img.src = url;
                if (openAnchor) openAnchor.href = url;
            }
        }

        if (headerInput) {
            headerInput.addEventListener('change', function(e) {
                if (removeHeader) removeHeader.value = '0';
                const file = e.target.files && e.target.files[0];
                showInlinePreview(file, 'newHeaderPdfPreviewWrap');
            });
        }
        if (footerInput) {
            footerInput.addEventListener('change', function(e) {
                if (removeFooter) removeFooter.value = '0';
                const file = e.target.files && e.target.files[0];
                showInlinePreview(file, 'newFooterPdfPreviewWrap');
            });
        }
        if (countryImageInput) {
            countryImageInput.addEventListener('change', function(e) {
                if (removeCountryImage) removeCountryImage.value = '0';
                const file = e.target.files && e.target.files[0];
                showImagePreview(file, 'newCountryImagePreviewWrap');
            });
        }
    });
</script>
    
@endsection
