@extends('layouts.layout')

@section('content')
<style>
    #currency {
    height: 38px;
    padding: 2px 8px !important;
    line-height: 1.2;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;

    padding-right: 18px; /* space for arrow */
    background: url("data:image/svg+xml;utf8,<svg fill='black' height='10' viewBox='0 0 20 20' width='10' xmlns='http://www.w3.org/2000/svg'><path d='M5 7l5 5 5-5z'/></svg>")
        no-repeat right 6px center;
    background-size: 10px;
 }
</style>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-md-6">
                <h4>Tour Quotation Preview</h4>
                <p class="text-muted mb-0">
                    Tour ID: {{ $tour->display_id ?? $tour->tour_id }} &mdash;
                    Destination: {{ $tour->destination ?? $tour->tour_destination ?? 'N/A' }}
                </p>
            </div>
            <div class="col-md-6 text-md-right mt-3 mt-md-0">
                <form method="GET" action="{{ route('tour.itinerary.preview', ['encryptedTourId' => Crypt::encrypt($tour->tour_id)]) }}" class="d-flex flex-nowrap align-items-center justify-content-md-end">
                    <label for="currency" class="mb-0 mr-2 font-weight-bold">Currency:</label>
                    <select name="currency" id="currency" class="form-control form-control-sm mr-2" style="max-width: 120px; height: 38px; line-height:1.2;" onchange="this.form.submit()">
                        @foreach($availableCurrencies as $currency)
                            <option value="{{ $currency }}" {{ $currency === $selectedCurrency ? 'selected' : '' }}>
                                {{ $currency }}
                            </option>
                        @endforeach
                    </select>
                    <button
                        type="button"
                        id="downloadQuotationBtn"
                        class="btn btn-primary flex-shrink-0"
                        data-bs-toggle="modal"
                        data-bs-target="#quotationInfoModal"
                    >
                        Download Quotation
                    </button>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body" style="padding: 0;">
                        <iframe
                            id="quotationIframe"
                            src="{{ route('tour.itinerary.pdf', ['tourId' => $tour->tour_id, 'currency' => $selectedCurrency, 'preview' => 1]) }}"
                            style="width: 100%; height: 900px; border: none;"
                        ></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quotation information modal -->
    <div class="modal fade" id="quotationInfoModal" tabindex="-1" aria-labelledby="quotationInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;">
                    <h6 class="modal-title mb-0" id="quotationInfoModalLabel" style="font-size: 0.95rem; font-weight: 700;">
                        Download Quotation (Editable Information)
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.95;"></button>
                </div>
                <div class="modal-body" style="padding: 1rem; background: #ffffff;">
                    <div id="quotationInfoModalError" class="alert alert-danger d-none" role="alert" style="margin-bottom: 1rem;"></div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="quotationCountrySelect" class="form-label fw-semibold mb-1" style="font-size: 0.85rem;">Country</label>
                            <select class="form-select" id="quotationCountrySelect" required style="height: 45px;">
                                <option value="">Select Country</option>
                                @foreach($countries ?? [] as $country)
                                    @php
                                        $tourCountry = (string) ($tour->destination ?? $tour->tour_destination ?? '');
                                    @endphp
                                    <option value="{{ $country->name }}" {{ $tourCountry === (string) $country->name ? 'selected' : '' }}>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="quotationCitySelect" class="form-label fw-semibold mb-1" style="font-size: 0.85rem;">City</label>
                            <select class="form-select" id="quotationCitySelect" required style="height: 45px;">
                                <option value="">Select City</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="quotation_information_modal" class="form-label fw-semibold mb-1" style="font-size: 0.85rem;">
                            Quotation Information
                        </label>
                        <textarea id="quotation_information_modal" class="form-control" style="min-height: 240px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8f9fa;">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-outline-primary" id="loadQuotationInfoBtn">Load Saved</button>
                    <button type="button" class="btn btn-primary" id="previewAndDownloadQuotationBtn">
                        Preview & Download
                    </button>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.js"></script>
    <script>
        (function () {
            const citiesByCountry = @json($citiesByCountry ?? []);

            const countrySelect = document.getElementById('quotationCountrySelect');
            const citySelect = document.getElementById('quotationCitySelect');
            const errorEl = document.getElementById('quotationInfoModalError');
            const loadBtn = document.getElementById('loadQuotationInfoBtn');
            const downloadBtn = document.getElementById('previewAndDownloadQuotationBtn');

            const textareaEl = document.getElementById('quotation_information_modal');
            if (!textareaEl) return;

            // Build city options based on selected country
            function setCityOptions(countryName) {
                const cities = citiesByCountry[countryName] || [];
                citySelect.innerHTML = '<option value="">Select City</option>';
                cities.forEach(function (name) {
                    const opt = document.createElement('option');
                    opt.value = name;
                    opt.textContent = name;
                    citySelect.appendChild(opt);
                });
            }

            function showError(message) {
                errorEl.textContent = message || 'Unable to load quotation information.';
                errorEl.classList.remove('d-none');
            }

            function clearError() {
                errorEl.textContent = '';
                errorEl.classList.add('d-none');
            }

            // Initialize summernote editor (footer jQuery is already loaded when this runs)
            try {
                if (window.jQuery && jQuery.fn && jQuery.fn.summernote) {
                    jQuery(textareaEl).summernote({
                        height: 320,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'clear']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['insert', ['link']],
                            ['view', ['codeview']]
                        ]
                    });
                }
            } catch (e) {
                // If editor fails, we still allow plain textarea updates.
            }

            // Populate initial city dropdown based on preselected country
            if (countrySelect && countrySelect.value) {
                setCityOptions(countrySelect.value);
            }

            countrySelect.addEventListener('change', function () {
                setCityOptions(this.value);
                // Reset content when switching context
                try {
                    if (window.jQuery && jQuery.fn && jQuery.fn.summernote) {
                        jQuery(textareaEl).summernote('code', '');
                    } else {
                        textareaEl.value = '';
                    }
                } catch (e) {
                    textareaEl.value = '';
                }
            });

            async function loadQuotationInformation(countryName, cityName) {
                clearError();
                if (!countryName || !cityName) return;

                loadBtn.disabled = true;
                loadBtn.textContent = 'Loading...';

                try {
                    const url = new URL('{{ route('quotation_settings.fetch') }}', window.location.origin);
                    url.searchParams.set('country', countryName);
                    url.searchParams.set('city', cityName);

                    const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                    const json = await res.json();

                    const html = (json && json.success && json.data && typeof json.data.quotation_information !== 'undefined')
                        ? (json.data.quotation_information || '')
                        : '';

                    // Prefer summernote if available, otherwise fall back to plain textarea value.
                    try {
                        if (window.jQuery && jQuery.fn && jQuery.fn.summernote) {
                            jQuery(textareaEl).summernote('code', html);
                        } else {
                            textareaEl.value = html;
                        }
                    } catch (e) {
                        textareaEl.value = html;
                    }
                } catch (e) {
                    showError('Failed to load quotation information.');
                } finally {
                    loadBtn.disabled = false;
                    loadBtn.textContent = 'Load Saved';
                }
            }

            // Debounced auto-load when both selectors have values
            let loadTimer = null;
            function maybeAutoLoad() {
                clearTimeout(loadTimer);
                const c = countrySelect.value;
                const city = citySelect.value;
                if (!c || !city) return;
                loadTimer = setTimeout(function () {
                    loadQuotationInformation(c, city);
                }, 300);
            }

            countrySelect.addEventListener('change', maybeAutoLoad);
            citySelect.addEventListener('change', maybeAutoLoad);

            loadBtn.addEventListener('click', function () {
                loadQuotationInformation(countrySelect.value, citySelect.value);
            });

            downloadBtn.addEventListener('click', async function () {
                clearError();
                const countryName = countrySelect.value;
                const cityName = citySelect.value;

                if (!countryName || !cityName) {
                    showError('Please select both Country and City.');
                    return;
                }

                downloadBtn.disabled = true;
                const originalText = downloadBtn.textContent;
                downloadBtn.textContent = 'Generating...';

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    let quotationHtml = '';

                    try {
                        if (window.jQuery && jQuery.fn && jQuery.fn.summernote) {
                            quotationHtml = jQuery(textareaEl).summernote('code');
                        } else {
                            quotationHtml = (textareaEl.value || '');
                        }
                    } catch (e) {
                        quotationHtml = (textareaEl.value || '');
                    }

                    const storeUrl = '{{ route('tour.quotation.info', ['tourId' => $tour->tour_id]) }}';
                    const res = await fetch(storeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            country: countryName,
                            city: cityName,
                            quotation_information: quotationHtml
                        })
                    });
                    const json = await res.json();

                    if (!json || !json.success || !json.quotation_info_key) {
                        showError('Unable to generate quotation preview PDF.');
                        return;
                    }

                    const key = json.quotation_info_key;
                    const previewBaseUrl = '{{ route('tour.itinerary.pdf', ['tourId' => $tour->tour_id, 'currency' => $selectedCurrency, 'preview' => 1]) }}';
                    const downloadBaseUrl = '{{ route('tour.itinerary.pdf', ['tourId' => $tour->tour_id, 'currency' => $selectedCurrency]) }}';

                    const previewUrl = previewBaseUrl + '&quotation_info_key=' + encodeURIComponent(key);
                    const downloadUrl = downloadBaseUrl + '&quotation_info_key=' + encodeURIComponent(key);

                    // Update iframe preview first, then trigger download in a new tab.
                    const iframe = document.getElementById('quotationIframe');
                    if (iframe) iframe.src = previewUrl;

                    window.open(downloadUrl, '_blank');
                    const bsModal = bootstrap.Modal.getInstance(document.getElementById('quotationInfoModal'));
                    if (bsModal) bsModal.hide();
                } catch (e) {
                    showError('Something went wrong while generating the quotation.');
                } finally {
                    downloadBtn.disabled = false;
                    downloadBtn.textContent = originalText;
                }
            });
        })();
    </script>
@endpush
@endsection

