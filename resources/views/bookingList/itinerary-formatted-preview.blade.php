@extends('layouts.layout')

@section('content')
@php
    $previewBase = route('bookinglist.itinerary.formatted-preview', ['tourId' => $encryptedTourId]);
    $previewQuery = [
        'logo_type' => $logoType ?? 'dmc',
    ];
    $pdfBase = route('bookinglist.itinerary.pdf', ['tourId' => $encryptedTourId]);
@endphp
<style>
    .invoice-preview-toolbar {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        gap: 0.75rem 1rem;
        padding: 0.5rem 0.875rem;
        background: var(--bs-gray-50, #f8f9fa);
        border: 1px solid var(--bs-border-color, #dee2e6);
        border-radius: 0.375rem;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
    .invoice-preview-toolbar .toolbar-segment {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        flex-shrink: 0;
        white-space: nowrap;
    }
    .invoice-preview-toolbar .btn-group .btn {
        padding-top: 0.25rem;
        padding-bottom: 0.25rem;
        padding-left: 0.65rem;
        padding-right: 0.65rem;
        font-size: 0.8125rem;
    }
    .invoice-preview-toolbar .toolbar-label {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--bs-secondary-color, #697a8d);
        margin: 0;
        margin-right: 0.125rem;
    }
    .invoice-preview-actions .btn {
        min-height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding-left: 1rem;
        padding-right: 1rem;
        font-weight: 500;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
<div class="container-fluid">
    <div class="row mb-2 align-items-start">
        <div class="col-md-6">
            <h4 class="mb-1">Itinerary PDF Preview</h4>
            <p class="text-muted small mb-0">
                Tour ID: {{ $tour->display_id ?? $tour->tour_id }} &mdash;
                Destination: {{ $tour->destination ?? $tour->tour_destination ?? 'N/A' }}
            </p>
        </div>
        <div class="col-md-6 text-md-end mt-2 mt-md-0">
            <div class="invoice-preview-actions d-flex flex-wrap align-items-center justify-content-md-end gap-2 gap-md-3">
                <button
                    type="button"
                    id="itineraryPdfDownloadOpenModalBtn"
                    class="btn btn-primary shadow-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#itineraryFormattedPdfInfoModal"
                >
                    <i class="ri-download-line me-1"></i> Download PDF
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="history.back();">
                    <i class="ri-arrow-left-line me-1"></i> Back
                </button>
            </div>
        </div>
    </div>

    @if($hasAgency ?? false)
    <div class="row mb-3">
        <div class="col-12">
            <div class="invoice-preview-toolbar" role="toolbar" aria-label="Itinerary PDF branding">
                <div class="toolbar-segment">
                    <span class="toolbar-label">Company</span>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Company branding">
                        <a href="{{ $previewBase }}?{{ http_build_query(array_merge($previewQuery, ['logo_type' => 'dmc'])) }}"
                           class="btn {{ ($logoType ?? 'dmc') === 'dmc' ? 'btn-success' : 'btn-outline-secondary' }}">DMC</a>
                        <a href="{{ $previewBase }}?{{ http_build_query(array_merge($previewQuery, ['logo_type' => 'agency'])) }}"
                           class="btn {{ ($logoType ?? 'dmc') === 'agency' ? 'btn-success' : 'btn-outline-secondary' }}">Agency</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body" style="padding: 0;">
                    <iframe
                        id="itineraryFormattedPdfIframe"
                        src="{{ $pdfBase }}?preview=1&logo_type={{ urlencode($logoType ?? 'dmc') }}"
                        style="width: 100%; height: 900px; border: none;"
                        title="Itinerary PDF preview"
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="itineraryFormattedPdfInfoModal" tabindex="-1" aria-labelledby="itineraryFormattedPdfInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;">
                <h6 class="modal-title mb-0" id="itineraryFormattedPdfInfoModalLabel" style="font-size: 0.95rem; font-weight: 700;">
                    Download itinerary (country, city &amp; additional information)
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.95;"></button>
            </div>
            <div class="modal-body" style="padding: 1rem; background: #ffffff;">
                <div id="itineraryFormattedPdfModalError" class="alert alert-danger d-none" role="alert" style="margin-bottom: 1rem;"></div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="itineraryPdfCountrySelect" class="form-label fw-semibold mb-1" style="font-size: 0.85rem;">Country</label>
                        <select class="form-select" id="itineraryPdfCountrySelect" required style="height: 45px;">
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
                        <label for="itineraryPdfCitySelect" class="form-label fw-semibold mb-1" style="font-size: 0.85rem;">City</label>
                        <select class="form-select" id="itineraryPdfCitySelect" required style="height: 45px;">
                            <option value="">Select City</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <label for="itinerary_information_modal" class="form-label fw-semibold mb-1" style="font-size: 0.85rem;">
                        Itinerary information
                    </label>
                    <textarea id="itinerary_information_modal" class="form-control" style="min-height: 200px;"></textarea>
                </div>

                <div class="mt-3">
                    <label for="emergency_contact_modal" class="form-label fw-semibold mb-1" style="font-size: 0.85rem;">Emergency contact numbers</label>
                    <textarea id="emergency_contact_modal" class="form-control" rows="3" placeholder="Optional"></textarea>
                </div>
                <div class="mt-3">
                    <label for="sic_timing_modal" class="form-label fw-semibold mb-1" style="font-size: 0.85rem;">SIC tour pick up / drop timing</label>
                    <textarea id="sic_timing_modal" class="form-control" rows="3" placeholder="Optional"></textarea>
                </div>
                <div class="mt-3">
                    <label for="meeting_points_modal" class="form-label fw-semibold mb-1" style="font-size: 0.85rem;">Meeting points</label>
                    <textarea id="meeting_points_modal" class="form-control" rows="3" placeholder="Optional"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="background: #f8f9fa;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-outline-primary" id="loadItineraryPdfInfoBtn">Load saved</button>
                <button type="button" class="btn btn-primary" id="previewAndDownloadItineraryPdfBtn">
                    Preview &amp; download
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

            const countrySelect = document.getElementById('itineraryPdfCountrySelect');
            const citySelect = document.getElementById('itineraryPdfCitySelect');
            const errorEl = document.getElementById('itineraryFormattedPdfModalError');
            const loadBtn = document.getElementById('loadItineraryPdfInfoBtn');
            const downloadBtn = document.getElementById('previewAndDownloadItineraryPdfBtn');

            const textareaEl = document.getElementById('itinerary_information_modal');
            const emergencyEl = document.getElementById('emergency_contact_modal');
            const sicEl = document.getElementById('sic_timing_modal');
            const meetingEl = document.getElementById('meeting_points_modal');
            if (!textareaEl || !countrySelect || !citySelect) return;

            const defaultItineraryHtml = @json($defaultItineraryInformationHtml ?? '');
            const fetchSettingsUrl = @json(route('itinerary_settings.fetch'));

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
                errorEl.textContent = message || 'Unable to continue.';
                errorEl.classList.remove('d-none');
            }

            function clearError() {
                errorEl.textContent = '';
                errorEl.classList.add('d-none');
            }

            try {
                if (window.jQuery && jQuery.fn && jQuery.fn.summernote) {
                    jQuery(textareaEl).summernote({
                        height: 260,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'clear']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['insert', ['link']],
                            ['view', ['codeview']]
                        ]
                    });
                }
            } catch (e) {}

            if (countrySelect.value) {
                setCityOptions(countrySelect.value);
                const tourCity = @json(trim((string) ($tour->city ?? '')));
                if (tourCity) {
                    let matched = false;
                    for (let i = 0; i < citySelect.options.length; i++) {
                        if (String(citySelect.options[i].value).toLowerCase() === tourCity.toLowerCase()) {
                            citySelect.selectedIndex = i;
                            matched = true;
                            break;
                        }
                    }
                    if (!matched) {
                        const injected = document.createElement('option');
                        injected.value = tourCity;
                        injected.textContent = tourCity;
                        injected.selected = true;
                        citySelect.appendChild(injected);
                    }
                }
            }

            if (defaultItineraryHtml) {
                try {
                    if (window.jQuery && jQuery.fn && jQuery.fn.summernote) {
                        jQuery(textareaEl).summernote('code', defaultItineraryHtml);
                    } else {
                        textareaEl.value = defaultItineraryHtml;
                    }
                } catch (e) {
                    textareaEl.value = defaultItineraryHtml;
                }
            }

            countrySelect.addEventListener('change', function () {
                setCityOptions(this.value);
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

            async function loadItinerarySettings(countryName, cityName) {
                clearError();
                if (!countryName || !cityName) return;

                loadBtn.disabled = true;
                loadBtn.textContent = 'Loading...';

                try {
                    const url = new URL(fetchSettingsUrl, window.location.origin);
                    url.searchParams.set('country', countryName);
                    url.searchParams.set('city', cityName);

                    const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                    const json = await res.json();

                    const html = (json && json.success && json.data && typeof json.data.itinerary_information !== 'undefined')
                        ? (json.data.itinerary_information || '')
                        : '';

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
                    showError('Failed to load itinerary settings.');
                } finally {
                    loadBtn.disabled = false;
                    loadBtn.textContent = 'Load saved';
                }
            }

            let loadTimer = null;
            function maybeAutoLoad() {
                clearTimeout(loadTimer);
                const c = countrySelect.value;
                const city = citySelect.value;
                if (!c || !city) return;
                loadTimer = setTimeout(function () {
                    loadItinerarySettings(c, city);
                }, 300);
            }

            countrySelect.addEventListener('change', maybeAutoLoad);
            citySelect.addEventListener('change', maybeAutoLoad);

            loadBtn.addEventListener('click', function () {
                loadItinerarySettings(countrySelect.value, citySelect.value);
            });

            downloadBtn.addEventListener('click', async function () {
                clearError();
                const countryName = countrySelect.value;
                const cityName = citySelect.value;

                if (!countryName || !cityName) {
                    showError('Please select both country and city.');
                    return;
                }

                downloadBtn.disabled = true;
                const originalText = downloadBtn.textContent;
                downloadBtn.textContent = 'Generating...';

                try {
                    let itineraryHtml = '';
                    try {
                        if (window.jQuery && jQuery.fn && jQuery.fn.summernote) {
                            itineraryHtml = jQuery(textareaEl).summernote('code');
                        } else {
                            itineraryHtml = textareaEl.value || '';
                        }
                    } catch (e) {
                        itineraryHtml = textareaEl.value || '';
                    }

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const storeUrl = '{{ route('bookinglist.itinerary.pdf.store-info', ['tourId' => $encryptedTourId]) }}';
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
                            itinerary_information: itineraryHtml,
                            emergency_contact: (emergencyEl && emergencyEl.value) ? emergencyEl.value : '',
                            sic_timing: (sicEl && sicEl.value) ? sicEl.value : '',
                            meeting_points: (meetingEl && meetingEl.value) ? meetingEl.value : ''
                        })
                    });
                    const json = await res.json();

                    if (!json || !json.success || !json.itinerary_pdf_info_key) {
                        showError(json && json.message ? json.message : 'Unable to store itinerary PDF options.');
                        return;
                    }

                    const key = json.itinerary_pdf_info_key;
                    const logoType = @json($logoType ?? 'dmc');
                    const previewBaseUrl = '{{ $pdfBase }}?preview=1&logo_type=' + encodeURIComponent(logoType);
                    const downloadBaseUrl = '{{ $pdfBase }}?logo_type=' + encodeURIComponent(logoType);

                    const previewUrl = previewBaseUrl + '&itinerary_pdf_info_key=' + encodeURIComponent(key);
                    const downloadUrl = downloadBaseUrl + '&itinerary_pdf_info_key=' + encodeURIComponent(key);

                    const iframe = document.getElementById('itineraryFormattedPdfIframe');
                    if (iframe) iframe.src = previewUrl;

                    window.open(downloadUrl, '_blank');
                    const bsModal = bootstrap.Modal.getInstance(document.getElementById('itineraryFormattedPdfInfoModal'));
                    if (bsModal) bsModal.hide();
                } catch (e) {
                    showError('Something went wrong while generating the PDF.');
                } finally {
                    downloadBtn.disabled = false;
                    downloadBtn.textContent = originalText;
                }
            });
        })();
    </script>
@endpush
@endsection
