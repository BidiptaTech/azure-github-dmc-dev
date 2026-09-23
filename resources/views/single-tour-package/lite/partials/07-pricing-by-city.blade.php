{{-- === STP LITE: Pricing by city (hotel + other markup) → tours.currency_markups === --}}
<div id="submitSection" class="stp-lite-pricing-by-city mt-3 mb-2">
    <input type="hidden" id="discount_price" name="discount_price" value="{{ old('discount_price', isset($tour) ? (int) ceil((float) ($tour->discount_amount ?? 0)) : 0) }}">
    <input type="hidden" id="currency_markups" name="currency_markups" value="[]">

    <div id="enquiryProMarkupMultiWrap" class="enquiry-md-panel" style="display: none;">
        <div class="enquiry-md-panel__head" role="button" tabindex="0" aria-expanded="true"
             onclick="if(window.StpLiteCityMarkup){window.StpLiteCityMarkup.toggle(this);}"
             onkeydown="if((event.key==='Enter'||event.key===' ')&&window.StpLiteCityMarkup){event.preventDefault();window.StpLiteCityMarkup.toggle(this);}">
            <div class="enquiry-md-panel__head-left">
                <span class="enquiry-md-panel__chevron" aria-hidden="true">▼</span>
                <p class="enquiry-md-panel__title">Pricing by city</p>
                <span class="enquiry-md-panel__count" id="enquiryProMarkupCityCount">0</span>
            </div>
            <p class="enquiry-md-panel__hint">Markup added to rates · Discount on total</p>
        </div>
        <div class="enquiry-md-panel__body">
            <p class="enquiry-md-panel__note">
                Hotel markup and Other markup are applied per destination currency and included in quotation rates.
                Discount is shown on the quotation total only.
            </p>
            <div class="enquiry-md-table-wrap">
                <table class="enquiry-md-table">
                    <thead>
                        <tr>
                            <th scope="col">City</th>
                            <th scope="col" class="enquiry-md-th-markup">Markup type</th>
                            <th scope="col" class="enquiry-md-th-markup">Hotel markup</th>
                            <th scope="col" class="enquiry-md-th-markup">Other markup</th>
                            <th scope="col" class="enquiry-md-th-discount">Disc type</th>
                            <th scope="col" class="enquiry-md-th-discount">Disc value</th>
                        </tr>
                    </thead>
                    <tbody id="enquiryProCityMarkupBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
{{-- === END 07-pricing-by-city === --}}
