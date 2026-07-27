@php
    use Illuminate\Support\Facades\Crypt;
    use App\Helpers\CommonHelper;

    $encryptedTourId = Crypt::encrypt($tour->tour_id);
    $quotationCountries = CommonHelper::getTourQuotationCountries($tour);
    $isMultiCountryQuotation = count($quotationCountries) > 1;
@endphp
<div class="quotation-actions-flyout__links">
    @if($isMultiCountryQuotation)
        @foreach($quotationCountries as $qCountry)
            <div class="quotation-country-group">
                <span class="quotation-country-label">{{ $qCountry }}</span>
                <div class="quotation-country-group__actions">
                    <a href="{{ route('tour.itinerary.preview', ['encryptedTourId' => $encryptedTourId, 'country' => $qCountry]) }}"
                       class="action-icon-badge" style="--action-color: #0f766e;" data-tooltip="Acco + Service ({{ $qCountry }})" target="_blank">
                        <i class="ri-file-list-3-line"></i>
                    </a>
                    <a href="{{ route('tour.detailed-quotation.preview', ['encryptedTourId' => $encryptedTourId, 'country' => $qCountry]) }}"
                       class="action-icon-badge" style="--action-color: #7c3aed;" data-tooltip="Packaged ({{ $qCountry }})" target="_blank">
                        <i class="ri-stack-line"></i>
                    </a>
                </div>
            </div>
        @endforeach
        <div class="quotation-country-group quotation-country-group--all">
            <span class="quotation-country-label">All</span>
            <div class="quotation-country-group__actions">
                <a href="{{ route('tour.itinerary.preview', ['encryptedTourId' => $encryptedTourId]) }}"
                   class="action-icon-badge" style="--action-color: #0f766e;" data-tooltip="Acco + Service (All Countries)" target="_blank">
                    <i class="ri-file-list-3-line"></i>
                </a>
                <a href="{{ route('tour.detailed-quotation.preview', ['encryptedTourId' => $encryptedTourId]) }}"
                   class="action-icon-badge" style="--action-color: #7c3aed;" data-tooltip="Packaged (All Countries)" target="_blank">
                    <i class="ri-stack-line"></i>
                </a>
            </div>
        </div>
    @else
        @php
            $singleCountry = $quotationCountries[0] ?? null;
            $itineraryParams = ['encryptedTourId' => $encryptedTourId];
            $detailedParams = ['encryptedTourId' => $encryptedTourId];
            if (!empty($singleCountry)) {
                $itineraryParams['country'] = $singleCountry;
                $detailedParams['country'] = $singleCountry;
            }
        @endphp
        <a href="{{ route('tour.itinerary.preview', $itineraryParams) }}"
           class="action-icon-badge" style="--action-color: #0f766e;" data-tooltip="Acco + Service Quotation" target="_blank">
            <i class="ri-file-list-3-line"></i>
        </a>
        <a href="{{ route('tour.detailed-quotation.preview', $detailedParams) }}"
           class="action-icon-badge" style="--action-color: #7c3aed;" data-tooltip="Packaged Quotation" target="_blank">
            <i class="ri-stack-line"></i>
        </a>
    @endif
</div>
